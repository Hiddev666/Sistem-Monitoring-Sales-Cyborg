<?php

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Services\GpsValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbsensiController extends Controller
{
    protected $gpsService;

    public function __construct(GpsValidationService $gpsService)
    {
        $this->gpsService = $gpsService;
    }

    /**
     * Display sales attendance page
     */
    public function index()
    {
        $user = auth()->user();
        
        // Get today's attendance
        $todayAbsensi = Absensi::todayFor($user->id);
        
        // Get recent attendance history (last 7 days)
        $recentAbsensi = Absensi::byUser($user->id)
            ->orderBy('tanggal', 'desc')
            ->limit(7)
            ->get();

        return view('sales.attendance.index', [
            'todayAbsensi' => $todayAbsensi,
            'recentAbsensi' => $recentAbsensi,
        ]);
    }

    /**
     * Check-in: Capture current location and time
     * POST /sales/attendance/checkin
     */
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
        ]);

        try {
            $user = auth()->user();
            
            // Check if already checked in today
            $existingAbsensi = Absensi::todayFor($user->id);
            
            if ($existingAbsensi && $existingAbsensi->waktu_masuk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah check-in hari ini pada ' . $existingAbsensi->waktu_masuk,
                ], 400);
            }

            // Create or update today's attendance
            if ($existingAbsensi) {
                $absensi = $existingAbsensi;
            } else {
                $absensi = new Absensi([
                    'user_id' => $user->id,
                    'tanggal' => now()->toDateString(),
                ]);
            }

            // Record check-in
            $absensi->waktu_masuk = now()->format('H:i:s');
            $absensi->lat_masuk = $validated['latitude'];
            $absensi->lng_masuk = $validated['longitude'];
            $absensi->accuracy_masuk = $validated['accuracy'] ?? null;
            $absensi->status = 'pending';
            $absensi->save();

            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil! Waktu: ' . $absensi->waktu_masuk,
                'data' => [
                    'id' => $absensi->id,
                    'waktu_masuk' => $absensi->waktu_masuk,
                    'lat_masuk' => $absensi->lat_masuk,
                    'lng_masuk' => $absensi->lng_masuk,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat check-in: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check-out: Record end of day location and time
     * POST /sales/attendance/checkout
     */
    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0',
        ]);

        try {
            $user = auth()->user();
            
            // Get today's attendance
            $absensi = Absensi::todayFor($user->id);
            
            if (!$absensi) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda belum check-in hari ini. Silahkan check-in terlebih dahulu.',
                ], 400);
            }

            if ($absensi->waktu_keluar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah check-out hari ini pada ' . $absensi->waktu_keluar,
                ], 400);
            }

            // Record check-out
            $absensi->waktu_keluar = now()->format('H:i:s');
            $absensi->lat_keluar = $validated['latitude'];
            $absensi->lng_keluar = $validated['longitude'];
            $absensi->accuracy_keluar = $validated['accuracy'] ?? null;
            $absensi->status = 'completed';
            
            // Calculate total hours (in minutes)
            $absensi->calculateDuration();

            return response()->json([
                'success' => true,
                'message' => 'Check-out berhasil! Durasi kerja: ' . $this->formatDuration($absensi->total_jam),
                'data' => [
                    'id' => $absensi->id,
                    'waktu_masuk' => $absensi->waktu_masuk,
                    'waktu_keluar' => $absensi->waktu_keluar,
                    'total_jam' => $absensi->total_jam,
                    'durasi_formatted' => $this->formatDuration($absensi->total_jam),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat check-out: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get today's attendance status
     * GET /sales/attendance/status
     */
    public function getStatus()
    {
        $user = auth()->user();
        $absensi = Absensi::todayFor($user->id);

        if (!$absensi) {
            return response()->json([
                'checked_in' => false,
                'checked_out' => false,
                'message' => 'Belum ada absensi hari ini',
            ]);
        }

        return response()->json([
            'checked_in' => (bool) $absensi->waktu_masuk,
            'checked_out' => (bool) $absensi->waktu_keluar,
            'waktu_masuk' => $absensi->waktu_masuk,
            'waktu_keluar' => $absensi->waktu_keluar,
            'lat_masuk' => $absensi->lat_masuk,
            'lng_masuk' => $absensi->lng_masuk,
            'lap_keluar' => $absensi->lat_keluar,
            'lng_keluar' => $absensi->lng_keluar,
            'total_jam' => $absensi->total_jam,
            'status' => $absensi->status,
        ]);
    }

    /**
     * View attendance history (recap for admin)
     */
    public function recap(Request $request)
    {
        $validated = $request->validate([
            'wilayah_id' => 'nullable|exists:wilayah,id',
            'tanggal_mulai' => 'nullable|date',
            'tanggal_akhir' => 'nullable|date|after_or_equal:tanggal_mulai',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $query = Absensi::with('user.wilayah')
            ->where('status', 'completed');

        if ($validated['user_id'] ?? null) {
            $query->where('user_id', $validated['user_id']);
        }

        if ($validated['tanggal_mulai'] ?? null) {
            $query->where('tanggal', '>=', $validated['tanggal_mulai']);
        }

        if ($validated['tanggal_akhir'] ?? null) {
            $query->where('tanggal', '<=', $validated['tanggal_akhir']);
        }

        if ($validated['wilayah_id'] ?? null) {
            $query->whereHas('user', function ($q) use ($validated) {
                $q->where('wilayah_id', $validated['wilayah_id']);
            });
        }

        $absensi = $query->orderBy('tanggal', 'desc')
            ->paginate(50);

        return view('admin.attendance.recap', [
            'absensi' => $absensi,
        ]);
    }

    /**
     * Get attendance data for DataTables (admin view)
     */
    public function getData(Request $request)
    {
        $draw = $request->input('draw');
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value', '');

        $query = Absensi::with('user.wilayah')
            ->where('status', 'completed');

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        $recordsTotal = Absensi::where('status', 'completed')->count();
        $recordsFiltered = $query->count();

        $data = $query->orderBy('tanggal', 'desc')
            ->offset($start)
            ->limit($length)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->user->name,
                    'tanggal' => $item->tanggal->format('d/m/Y'),
                    'waktu_masuk' => $item->waktu_masuk,
                    'waktu_keluar' => $item->waktu_keluar,
                    'durasi' => $this->formatDuration($item->total_jam),
                    'wilayah' => $item->user->wilayah?->nama_wilayah ?? '-',
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
     * Format duration from minutes to HH:MM format
     */
    private function formatDuration($minutes)
    {
        if (!$minutes) return '-';
        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }
}
