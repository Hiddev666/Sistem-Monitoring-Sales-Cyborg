<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\JadwalKlien;
use App\Models\User;
use Illuminate\View\View;

class ManagerDashboardController extends Controller
{
    /**
     * Show manager dashboard
     */
    public function index(): View
    {
        // Get dashboard statistics
        $activeSales = User::role('sales')
            ->whereHas('absensi', function ($query) {
                $query->whereDate('tanggal', today())
                    ->whereNotNull('waktu_masuk')
                    ->whereNull('waktu_keluar');
            })
            ->count();

        $totalVisits = JadwalKlien::whereDate('created_at', today())->count();
        $completedVisits = JadwalKlien::whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();

        return view('manager.dashboard', compact(
            'activeSales',
            'totalVisits',
            'completedVisits'
        ));
    }
}
