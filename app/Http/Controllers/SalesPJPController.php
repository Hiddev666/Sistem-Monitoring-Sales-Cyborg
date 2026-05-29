<?php

namespace App\Http\Controllers;

use App\Models\Configuration;
use App\Models\JadwalKunjungan;
use App\Models\JadwalKlien;
use App\Services\GpsValidationService;
use Illuminate\Http\Request;

class SalesPJPController extends Controller
{
    protected $gpsService;

    public function __construct(GpsValidationService $gpsService)
    {
        $this->gpsService = $gpsService;
    }

    /**
     * Display today's schedule for sales
     * GET /sales/pjp/today
     */
    public function today()
    {
        $user = auth()->user();
        $jadwal = JadwalKunjungan::todayFor($user->id);

        if (!$jadwal) {
            return view('sales.pjp.no-schedule');
        }

        $klien = $jadwal->jadwalKlien()
            ->ordered()
            ->with('klien')
            ->get();
        $currentVisit = $jadwal->getCurrentVisit();

        return view('sales.pjp.today', [
            'jadwal' => $jadwal,
            'klien' => $klien,
            'currentVisit' => $currentVisit,
        ]);
    }

    /**
     * View schedule details
     * GET /sales/pjp/{jadwal}
     */
    public function show(JadwalKunjungan $jadwal)
    {
        $user = auth()->user();

        // Ensure user can only view their own schedule
        if ($jadwal->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }

        $klien = $jadwal->jadwalKlien()
            ->ordered()
            ->with('klien')
            ->get();
        $currentVisit = $jadwal->getCurrentVisit();

        return view('sales.pjp.show', [
            'jadwal' => $jadwal,
            'klien' => $klien,
            'currentVisit' => $currentVisit,
        ]);
    }

    /**
     * Start journey: Change status from pending to aktif
     * POST /sales/pjp/{jadwal}/mulai-perjalanan
     */
    public function startJourney(JadwalKunjungan $jadwal)
    {
        $user = auth()->user();

        if ($jadwal->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$jadwal->isPendingStatus()) {
            return response()->json([
                'error' => 'Jadwal sudah dimulai atau selesai'
            ], 400);
        }

        try {
            $jadwal->mulaiPerjalanan();

            return response()->json([
                'success' => true,
                'message' => 'Perjalanan dimulai!',
                'jadwal' => [
                    'id' => $jadwal->id,
                    'status' => $jadwal->status,
                    'waktu_mulai' => $jadwal->waktu_mulai,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * End journey: Change status to selesai
     * POST /sales/pjp/{jadwal}/selesai-perjalanan
     */
    public function endJourney(JadwalKunjungan $jadwal)
    {
        $user = auth()->user();

        if ($jadwal->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$jadwal->isActiveStatus()) {
            return response()->json([
                'error' => 'Jadwal harus dalam status aktif'
            ], 400);
        }

        $hasUnfinishedVisits = $jadwal->jadwalKlien()
            ->whereNotIn('status', [
                JadwalKlien::STATUS_COMPLETED,
                JadwalKlien::STATUS_SKIPPED,
            ])
            ->exists();

        if ($hasUnfinishedVisits) {
            return response()->json([
                'error' => 'Selesaikan semua form kunjungan sebelum mengakhiri perjalanan'
            ], 400);
        }

        try {
            $jadwal->selesaiPerjalanan();

            return response()->json([
                'success' => true,
                'message' => 'Perjalanan selesai!',
                'jadwal' => [
                    'id' => $jadwal->id,
                    'status' => $jadwal->status,
                    'waktu_selesai' => $jadwal->waktu_selesai,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check-in to a specific klien
     * POST /sales/pjp/klien/{jadwalKlien}/checkin
     */
    public function checkInKlien(Request $request, JadwalKlien $jadwalKlien)
    {
        $user = auth()->user();
        $jadwal = $jadwalKlien->jadwalKunjungan;

        // Security check
        if ($jadwal->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!$jadwal->isActiveStatus()) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal harus aktif sebelum check-in.',
            ], 400);
        }

        if ($jadwalKlien->isCompletedStatus() || $jadwalKlien->isSkippedStatus()) {
            return response()->json([
                'success' => false,
                'message' => 'Klien ini sudah selesai atau dilewati.',
            ], 400);
        }

        $currentVisit = $jadwal->getCurrentVisit();

        if (!$currentVisit) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada klien yang dapat dikunjungi saat ini.',
            ], 400);
        }

        if ($currentVisit->id !== $jadwalKlien->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ikuti urutan PJP. Klien yang bisa di-check-in saat ini adalah klien urutan ' . $currentVisit->urutan . '.',
            ], 400);
        }

        $validated = $request->validate([
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'accuracy' => 'nullable|numeric|min:0|max:999999.99',
        ]);

        // Validate GPS proximity
        $klien = $jadwalKlien->klien;
        $gpsValidation = $this->gpsService->validateCheckIn(
            $validated['latitude'],
            $validated['longitude'],
            $klien->latitude,
            $klien->longitude,
            Configuration::getGpsRadiusTolerance()
        );

        if (!$gpsValidation['valid']) {
            return response()->json([
                'success' => false,
                'message' => $gpsValidation['message'],
                'distance' => $gpsValidation['distance'],
            ], 400);
        }

        try {
            // Update jadwal_klien with check-in info
            $jadwalKlien->status = JadwalKlien::STATUS_ACTIVE;
            $jadwalKlien->waktu_checkin = now()->format('H:i:s');
            $jadwalKlien->lat_checkin = $validated['latitude'];
            $jadwalKlien->lng_checkin = $validated['longitude'];
            $jadwalKlien->accuracy_checkin = $validated['accuracy'] ?? null;
            $jadwalKlien->save();

            return response()->json([
                'success' => true,
                'message' => 'Check-in berhasil! Jarak: ' . $gpsValidation['distance'] . 'm',
                'data' => [
                    'id' => $jadwalKlien->id,
                    'status' => $jadwalKlien->status,
                    'waktu_checkin' => $jadwalKlien->waktu_checkin,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check-out and record visit information
     * POST /sales/pjp/klien/{jadwalKlien}/checkout
     */
    public function checkOutKlien(Request $request, JadwalKlien $jadwalKlien)
    {
        $user = auth()->user();
        $jadwal = $jadwalKlien->jadwalKunjungan;

        if ($jadwal->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($jadwalKlien->isCompletedStatus()) {
            return response()->json([
                'success' => true,
                'message' => 'Kunjungan sudah selesai.',
            ]);
        }

        $currentVisit = $jadwal->getCurrentVisit();

        if (!$currentVisit || $currentVisit->id !== $jadwalKlien->id) {
            return response()->json([
                'success' => false,
                'message' => 'Ikuti urutan PJP. Klien yang bisa di-check-out saat ini adalah klien urutan ' . ($currentVisit?->urutan ?? '-'),
            ], 400);
        }

        if (!($jadwalKlien->isActiveStatus() || $jadwalKlien->isCheckingOutStatus())) {
            return response()->json([
                'success' => false,
                'message' => 'Klien harus sudah check-in sebelum checkout',
            ], 400);
        }

        $jadwalKlien->status = JadwalKlien::STATUS_CHECKING_OUT;
        $jadwalKlien->save();

        return response()->json([
            'success' => true,
            'message' => 'Lanjutkan checkout melalui form kunjungan.',
            'redirect' => route('sales.pjp.form', [$jadwal->id, $jadwalKlien->id]),
        ]);
    }

    /**
     * Get next klien to visit
     * GET /sales/pjp/{jadwal}/next-klien
     */
    public function getNextKlien(JadwalKunjungan $jadwal)
    {
        $user = auth()->user();

        if ($jadwal->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $currentKlien = $jadwal->getCurrentVisit();

        if (!$currentKlien) {
            return response()->json([
                'message' => 'Tidak ada klien lagi untuk dikunjungi',
            ]);
        }

        return response()->json([
            'id' => $currentKlien->id,
            'klien' => [
                'id' => $currentKlien->klien->id,
                'nama' => $currentKlien->klien->nama_klien,
                'alamat' => $currentKlien->klien->alamat,
                'latitude' => $currentKlien->klien->latitude,
                'longitude' => $currentKlien->klien->longitude,
                'contact_person' => $currentKlien->klien->contact_person,
                'phone' => $currentKlien->klien->phone,
            ],
            'urutan' => $currentKlien->urutan,
        ]);
    }

    /**
     * Get schedule progress/summary
     * GET /sales/pjp/{jadwal}/progress
     */
    public function getProgress(JadwalKunjungan $jadwal)
    {
        $user = auth()->user();

        if ($jadwal->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'total' => $jadwal->getTotalKlienCount(),
            'completed' => $jadwal->getCompletedKlienCount(),
            'percentage' => $jadwal->getProgressPercentage(),
            'status' => $jadwal->status,
        ]);
    }
}
