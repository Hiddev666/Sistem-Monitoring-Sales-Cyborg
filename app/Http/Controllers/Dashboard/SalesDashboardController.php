<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use Illuminate\View\View;

class SalesDashboardController extends Controller
{
    /**
     * Show sales dashboard
     */
    public function index(): View
    {
        $user = auth()->user();
        $todayAbsensi = Absensi::todayFor($user->id);
        $jadwal = JadwalKunjungan::todayFor($user->id);
        $jadwalKlien = collect();

        if ($jadwal) {
            $jadwalKlien = $jadwal->jadwalKlien()
                ->with('klien')
                ->ordered()
                ->get();
        }

        $totalVisits = $jadwalKlien->count();
        $completedVisits = $jadwalKlien->where('status', JadwalKlien::STATUS_COMPLETED)->count();
        $progressPercentage = $totalVisits > 0 ? round(($completedVisits / $totalVisits) * 100, 2) : 0;

        $recentVisits = JadwalKlien::with(['klien', 'jadwalKunjungan'])
            ->whereHas('jadwalKunjungan', fn ($query) => $query->where('user_id', $user->id))
            ->where('status', JadwalKlien::STATUS_COMPLETED)
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return view('sales.dashboard', [
            'todayAbsensi' => $todayAbsensi,
            'jadwal' => $jadwal,
            'jadwalKlien' => $jadwalKlien,
            'totalVisits' => $totalVisits,
            'completedVisits' => $completedVisits,
            'progressPercentage' => $progressPercentage,
            'recentVisits' => $recentVisits,
        ]);
    }
}
