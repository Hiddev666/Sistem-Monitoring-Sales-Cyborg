<?php

namespace App\Http\Controllers\Admin;

use App\Models\Klien;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class KlienController
{
    /**
     * Display list of klien
     */
    public function index(): View
    {
        return view('admin.klien.index');
    }

    /**
     * Get klien for DataTables AJAX
     */
    public function getKlien(Request $request)
    {
        $query = Klien::with('wilayah');

        // Search
        if ($request->has('search') && $request->search['value']) {
            $search = $request->search['value'];
            $query->where(function ($q) use ($search) {
                $q->where('nama_klien', 'like', "%$search%")
                  ->orWhere('alamat', 'like', "%$search%")
                  ->orWhere('phone', 'like', "%$search%");
            });
        }

        // Filter by wilayah
        if ($request->has('wilayah_id') && $request->wilayah_id) {
            $query->where('wilayah_id', $request->wilayah_id);
        }

        // Filter by kategori
        if ($request->has('kategori') && $request->kategori) {
            $query->where('kategori', $request->kategori);
        }

        // Filter by aktif
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $recordsTotal = Klien::count();
        $recordsFiltered = $query->count();

        // Sorting
        $orderColumn = $request->input('order.0.column', 0);
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = ['id', 'nama_klien', 'kategori', 'wilayah_id', 'phone', 'is_active', 'created_at'];
        
        if ($orderColumn < count($columns)) {
            $query->orderBy($columns[$orderColumn], $orderDir);
        }

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $kliens = $query->offset($start)->limit($length)->get();

        $data = $kliens->map(function ($klien) {
            return [
                'id' => $klien->id,
                'nama_klien' => $klien->nama_klien,
                'kategori' => ucfirst(str_replace('_', ' ', $klien->kategori)),
                'alamat' => substr($klien->alamat, 0, 50) . '...',
                'wilayah' => $klien->wilayah?->nama_wilayah ?? '-',
                'gps' => '<a href="https://maps.google.com/?q=' . urlencode($klien->latitude . ',' . $klien->longitude) . '" target="_blank" class="badge bg-info">📍 ' . $klien->getGpsFormatted() . '</a>',
                'is_active' => $klien->is_active ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-danger">Tidak Aktif</span>',
                'created_at' => $klien->created_at->format('d/m/Y H:i'),
                'actions' => view('admin.klien.actions', ['klien' => $klien])->render(),
            ];
        });

        return response()->json([
            'draw' => $request->input('draw'),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Show create klien form
     */
    public function create(): View
    {
        return view('admin.klien.form', [
            'wilayahs' => Wilayah::all(),
            'kategoris' => $this->getKategoriOptions(),
        ]);
    }

    /**
     * Store new klien
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_klien' => ['required', 'string', 'max:100'],
            'kategori' => ['required', 'in:apotek,toko_obat,rs_klinik,lainnya'],
            'alamat' => ['required', 'string'],
            'wilayah_id' => ['required', 'exists:wilayah,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'numeric', 'digits_between:10,12'],
            'is_active' => ['boolean'],
        ]);

        Klien::create($validated);

        return redirect()->route('admin.klien.index')
            ->with('success', "Klien '{$validated['nama_klien']}' berhasil dibuat.");
    }

    /**
     * Show edit klien form
     */
    public function edit(Klien $klien): View
    {
        return view('admin.klien.form', [
            'klien' => $klien,
            'wilayahs' => Wilayah::all(),
            'kategoris' => $this->getKategoriOptions(),
        ]);
    }

    /**
     * Update klien
     */
    public function update(Klien $klien, Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_klien' => ['required', 'string', 'max:100'],
            'kategori' => ['required', 'in:apotek,toko_obat,rs_klinik,lainnya'],
            'alamat' => ['required', 'string'],
            'wilayah_id' => ['required', 'exists:wilayah,id'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'contact_person' => ['nullable', 'string', 'max:100'],
            'phone' => ['nullable', 'numeric', 'digits_between:10,12'],
            'is_active' => ['boolean'],
        ]);

        $klien->update($validated);

        return redirect()->route('admin.klien.index')
            ->with('success', "Klien '{$klien->nama_klien}' berhasil diperbarui.");
    }

    /**
     * Delete klien
     */
    public function destroy(Klien $klien): RedirectResponse
    {
        $nama = $klien->nama_klien;
        $klien->delete();

        return redirect()->route('admin.klien.index')
            ->with('success', "Klien '$nama' berhasil dihapus.");
    }

    /**
     * Get kategori options
     */
    private function getKategoriOptions()
    {
        return [
            'apotek' => 'Apotek',
            'toko_obat' => 'Toko Obat',
            'rs_klinik' => 'RS / Klinik',
            'lainnya' => 'Lainnya',
        ];
    }
}
