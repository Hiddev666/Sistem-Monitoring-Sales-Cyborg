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
        $manager = auth()->user();
        $wilayahId = $this->managerWilayahId($manager);

        // Get dashboard statistics
        $activeSales = User::role('sales')
            ->when($wilayahId !== null, function ($query) use ($wilayahId) {
                $query->where('wilayah_id', $wilayahId);
            })
            ->whereHas('absensi', function ($query) {
                $query->whereDate('tanggal', today())
                    ->whereNotNull('waktu_masuk')
                    ->whereNull('waktu_keluar');
            })
            ->count();

        $visitsQuery = JadwalKlien::whereHas('jadwalKunjungan', function ($query) use ($wilayahId) {
            $query->whereDate('tanggal', today());

            if ($wilayahId !== null) {
                $query->whereHas('user', function ($query) use ($wilayahId) {
                    $query->where('wilayah_id', $wilayahId);
                });
            }
        });

        $totalVisits = (clone $visitsQuery)->count();
        $completedVisits = (clone $visitsQuery)
            ->where('status', 'completed')
            ->count();

        return view('manager.dashboard', compact(
            'activeSales',
            'totalVisits',
            'completedVisits'
        ));
    }

    private function managerWilayahId(?\App\Models\User $manager): ?int
    {
        if (!$manager?->isManager()) {
            return null;
        }

        return (int) ($manager->wilayah_id ?? 0);
    }
}
