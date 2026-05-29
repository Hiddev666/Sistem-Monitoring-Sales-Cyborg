<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use App\Models\Wilayah;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    /**
     * Show admin dashboard
     */
    public function index(): View
    {
        $todayVisits = JadwalKlien::whereHas('jadwalKunjungan', function ($query) {
            $query->whereDate('tanggal', today());
        });

        return view('admin.dashboard', [
            'totalUsers' => User::active()->count(),
            'activeSales' => User::role('sales')->active()->count(),
            'totalKlien' => Klien::active()->count(),
            'totalWilayah' => Wilayah::count(),
            'todaySchedules' => JadwalKunjungan::whereDate('tanggal', today())->count(),
            'todayVisits' => (clone $todayVisits)->count(),
            'completedVisits' => (clone $todayVisits)->where('status', JadwalKlien::STATUS_COMPLETED)->count(),
            'activeAttendance' => Absensi::whereDate('tanggal', today())
                ->whereNotNull('waktu_masuk')
                ->whereNull('waktu_keluar')
                ->count(),
        ]);
    }
}
