<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\LokasiRealtime;
use App\Models\User;
use App\Services\GpsValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    protected $gpsService;

    public function __construct(GpsValidationService $gpsService)
    {
        $this->gpsService = $gpsService;
    }

    /**
     * Update real-time location from sales device
     * POST /api/location/update
     */
    public function updateLocation(Request $request)
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0'
        ]);

        LokasiRealtime::create([
            'user_id' => Auth::id(),
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'akurasi_meter' => $validated['accuracy'] ?? null,
            'recorded_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Location updated successfully'
        ]);
    }

    /**
     * Get sales locations for manager dashboard
     * GET /api/dashboard/sales-locations
     */
    public function salesLocations()
    {
        $salesLocations = LokasiRealtime::with('user')
            ->whereHas('user', function ($query) {
                $query->role('sales');
            })
            ->latestPerUser()
            ->get();

        $salesData = $salesLocations->map(function ($location) {
            $noMovementMinutes = $this->calculateNoMovement($location->user_id);
            $status = $this->determineStatus($location->user_id);

            return [
                'id' => $location->user_id,
                'name' => $location->user->name,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'accuracy' => $location->akurasi_meter,
                'status' => $status,
                'visitCount' => $this->getTodayVisitCount($location->user_id),
                'completedCount' => $this->getTodayCompletedCount($location->user_id),
                'noMovementMinutes' => $noMovementMinutes,
                'lastUpdate' => $location->recorded_at->diffForHumans(),
            ];
        });

        $todayVisits = JadwalKlien::whereDate('created_at', today())->count();
        $todayCompleted = JadwalKlien::whereDate('created_at', today())
            ->where('status', 'completed')
            ->count();
        $notMovingCount = $salesData->filter(function ($sales) {
            return $sales['noMovementMinutes'] > 60;
        })->count();

        return response()->json([
            'sales' => $salesData,
            'activeSales' => $salesData->count(),
            'totalVisits' => $todayVisits,
            'completedVisits' => $todayCompleted,
            'notMoving' => $notMovingCount,
        ]);
    }

    /**
     * Get dashboard statistics
     * GET /api/dashboard/statistics
     */
    public function dashboardStatistics()
    {
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

        $notMoving = LokasiRealtime::with('user')
            ->whereHas('user', function ($query) {
                $query->role('sales');
            })
            ->latestPerUser()
            ->get()
            ->filter(function ($location) {
                return $this->calculateNoMovement($location->user_id) > 60;
            })
            ->count();

        return response()->json([
            'activeSales' => $activeSales,
            'totalVisits' => $totalVisits,
            'completedVisits' => $completedVisits,
            'notMoving' => $notMoving,
        ]);
    }

    /**
     * Calculate how long a sales has not moved (in minutes)
     */
    private function calculateNoMovement($userId)
    {
        $locations = LokasiRealtime::where('user_id', $userId)
            ->orderBy('recorded_at', 'desc')
            ->limit(2)
            ->get();

        if ($locations->count() < 2) {
            return 0;
        }

        $lastLoc = $locations->first();
        $prevLoc = $locations->last();

        // Check if coordinates are same (within 10m tolerance)
        $distance = $this->gpsService->calculateDistance(
            $lastLoc->latitude,
            $lastLoc->longitude,
            $prevLoc->latitude,
            $prevLoc->longitude
        );

        if ($distance < 10) {
            return (int) abs($lastLoc->recorded_at->diffInMinutes($prevLoc->recorded_at));
        }

        return 0;
    }

    /**
     * Determine sales status based on activity
     */
    private function determineStatus($userId)
    {
        $noMovement = $this->calculateNoMovement($userId);

        if ($noMovement > 60) {
            return 'idle'; // Not moving for more than 60 minutes
        }

        // Check if sales has active journey
        $activeJourney = JadwalKunjungan::where('user_id', $userId)
            ->whereDate('tanggal', today())
            ->where('status', 'aktif')
            ->exists();

        if ($activeJourney) {
            return 'active'; // Currently on journey
        }

        // Check if sales completed all visits today
        $totalVisits = $this->getTodayVisitCount($userId);
        $completedVisits = $this->getTodayCompletedCount($userId);

        if ($totalVisits > 0 && $totalVisits === $completedVisits) {
            return 'completed'; // All visits completed
        }

        return 'paused'; // Has schedule but not started
    }

    /**
     * Get today's visit count for a sales
     */
    private function getTodayVisitCount($userId)
    {
        return JadwalKlien::whereHas('jadwalKunjungan', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->whereDate('tanggal', today());
        })->count();
    }

    /**
     * Get today's completed visit count for a sales
     */
    private function getTodayCompletedCount($userId)
    {
        return JadwalKlien::whereHas('jadwalKunjungan', function ($query) use ($userId) {
            $query->where('user_id', $userId)
                ->whereDate('tanggal', today());
        })->where('status', 'completed')->count();
    }
}
