<?php

namespace App\Http\Controllers\Admin;

use App\Models\JadwalKunjungan;
use App\Models\JadwalKlien;
use App\Models\User;
use App\Models\Klien;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PJPController extends Controller
{
    /**
     * Display list of schedules (admin view)
     */
    public function index()
    {
        return view('admin.pjp.index');
    }

    /**
     * Get schedules data for DataTables
     */
    public function getData(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value', '');

        $query = JadwalKunjungan::with('user', 'creator');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('user', function ($u) use ($search) {
                    $u->where('name', 'like', "%$search%");
                })->orWhere('keterangan', 'like', "%$search%");
            });
        }

        $recordsTotal = JadwalKunjungan::count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy('tanggal', 'desc')
            ->offset($start)
            ->limit($length)
            ->get()
            ->map(function ($item) {
                $totalKlien = $item->getTotalKlienCount();
                $completedKlien = $item->getCompletedKlienCount();
                
                return [
                    'id' => $item->id,
                    'user_name' => $item->user->name,
                    'tanggal' => $item->tanggal->format('d/m/Y'),
                    'keterangan' => $item->keterangan ?? '-',
                    'status' => ucfirst($item->status),
                    'status_badge' => $this->getStatusBadge($item->status),
                    'progress' => $completedKlien . '/' . $totalKlien,
                    'percentage' => $item->getProgressPercentage(),
                    'created_by' => $item->creator->name,
                    'actions' => $item->id,
                ];
            });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Show create form
     */
    public function create()
    {
        $users = User::role('sales')->active()->get();
        $klien = Klien::active()->get();

        return view('admin.pjp.create', [
            'users' => $users,
            'klien' => $klien,
        ]);
    }

    /**
     * Store new schedule
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date|after:yesterday',
            'keterangan' => 'nullable|string|max:255',
            'klien' => 'nullable|array', // Made optional for debugging
            'klien.*' => 'exists:klien,id',
        ], [
            'klien.*.exists' => 'Klien yang dipilih tidak valid',
        ]);

        try {
            $jadwal = JadwalKunjungan::create([
                'user_id' => $validated['user_id'],
                'tanggal' => $validated['tanggal'],
                'keterangan' => $validated['keterangan'],
                'status' => 'pending',
                'created_by' => Auth::id(),
            ]);

            // Add klien to schedule with ordering
            if (!empty($validated['klien'])) {
                foreach ($validated['klien'] as $index => $klienId) {
                    JadwalKlien::create([
                        'jadwal_kunjungan_id' => $jadwal->id,
                        'klien_id' => $klienId,
                        'urutan' => $index + 1,
                        'status' => 'pending',
                    ]);
                }
            }

            return redirect()
                ->route('admin.pjp.index')
                ->with('success', 'Jadwal kunjungan berhasil dibuat!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Show edit form
     */
    public function edit(JadwalKunjungan $jadwal)
    {
        $users = User::role('sales')->active()->get();
        $klien = Klien::active()->get();
        $selectedKlien = $jadwal->jadwalKlien()->pluck('klien_id')->toArray();

        return view('admin.pjp.edit', [
            'jadwal' => $jadwal,
            'users' => $users,
            'klien' => $klien,
            'selectedKlien' => $selectedKlien,
        ]);
    }

    /**
     * Update schedule
     */
    public function update(Request $request, JadwalKunjungan $jadwal)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'keterangan' => 'nullable|string|max:255',
            'klien' => 'required|array|min:1',
            'klien.*' => 'exists:klien,id',
        ]);

        try {
            $jadwal->update([
                'user_id' => $validated['user_id'],
                'tanggal' => $validated['tanggal'],
                'keterangan' => $validated['keterangan'],
            ]);

            // Update klien list
            $jadwal->jadwalKlien()->delete();
            foreach ($validated['klien'] as $index => $klienId) {
                JadwalKlien::create([
                    'jadwal_kunjungan_id' => $jadwal->id,
                    'klien_id' => $klienId,
                    'urutan' => $index + 1,
                    'status' => 'pending',
                ]);
            }

            return redirect()
                ->route('admin.pjp.index')
                ->with('success', 'Jadwal kunjungan berhasil diperbarui!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Delete schedule
     */
    public function destroy(JadwalKunjungan $jadwal)
    {
        try {
            $jadwal->delete();
            return redirect()
                ->route('admin.pjp.index')
                ->with('success', 'Jadwal kunjungan berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Helper function to get status badge
     */
    private function getStatusBadge($status)
    {
        $badges = [
            'pending' => '<span class="badge bg-warning text-dark">Menunggu</span>',
            'aktif' => '<span class="badge bg-info">Aktif</span>',
            'selesai' => '<span class="badge bg-success">Selesai</span>',
        ];
        return $badges[$status] ?? '<span class="badge bg-secondary">Unknown</span>';
    }
}
