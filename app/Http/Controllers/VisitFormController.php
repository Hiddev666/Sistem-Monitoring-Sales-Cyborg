<?php

namespace App\Http\Controllers;

use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Services\PhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class VisitFormController extends Controller
{
    protected PhotoService $photoService;

    public function __construct(PhotoService $photoService)
    {
        $this->photoService = $photoService;
    }

    /**
     * Show visit form for a klien
     * GET /sales/pjp/{jadwalKunjungan}/klien/{jadwalKlien}/form
     */
    public function show(JadwalKunjungan $jadwalKunjungan, JadwalKlien $jadwalKlien)
    {
        // Verify user owns this schedule
        if ($jadwalKunjungan->user_id !== Auth::id()) {
            return abort(403, 'Unauthorized');
        }

        // Verify klien belongs to this schedule
        if ($jadwalKlien->jadwal_kunjungan_id !== $jadwalKunjungan->id) {
            return abort(404, 'Klien not found');
        }

        // Verify status is appropriate for form
        if (!in_array($jadwalKlien->status, ['active', 'checking_out'])) {
            return abort(403, 'Cannot complete form for this visit');
        }

        $hasilTipeOptions = [
            'pembelian' => 'Pembelian',
            'tidak_ada_uang' => 'Tidak Ada Uang',
            'tidak_ada_orang' => 'Tidak Ada Orang',
            'tidak_ada_minat' => 'Tidak Ada Minat',
            'dilanjutkan' => 'Dilanjutkan',
            'lainnya' => 'Lainnya'
        ];

        return view('sales.pjp.visit-form', compact(
            'jadwalKunjungan',
            'jadwalKlien',
            'hasilTipeOptions'
        ));
    }

    /**
     * Upload check-in or check-out photo
     * POST /sales/pjp/klien/{jadwalKlien}/upload-photo
     */
    public function uploadPhoto(Request $request, JadwalKlien $jadwalKlien)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'type' => 'required|in:checkin,checkout'
        ]);

        // Verify user owns the schedule
        if ($jadwalKlien->jadwalKunjungan->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $result = $this->photoService->storeVisitPhoto(
            $request->file('photo'),
            $jadwalKlien->id,
            $request->input('type'),
            Auth::id()
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        // Update model
        $fieldName = $request->input('type') === 'checkin' ? 'foto_checkin' : 'foto_checkout';
        $jadwalKlien->update([$fieldName => $result['path']]);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'photo' => [
                'path' => $result['path'],
                'url' => $result['url'],
                'type' => $request->input('type')
            ]
        ]);
    }

    /**
     * Upload digital signature
     * POST /sales/pjp/klien/{jadwalKlien}/upload-signature
     */
    public function uploadSignature(Request $request, JadwalKlien $jadwalKlien)
    {
        $request->validate([
            'signature' => 'required|string' // Base64 data URL from canvas
        ]);

        // Verify user owns the schedule
        if ($jadwalKlien->jadwalKunjungan->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $result = $this->photoService->storeSignature(
            $request->input('signature'),
            $jadwalKlien->id,
            Auth::id()
        );

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message']
            ], 400);
        }

        $jadwalKlien->update(['tanda_tangan' => $result['path']]);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'signature' => [
                'path' => $result['path'],
                'url' => $result['url']
            ]
        ]);
    }

    /**
     * Submit visit form
     * POST /sales/pjp/klien/{jadwalKlien}/submit-form
     */
    public function submitForm(Request $request, JadwalKlien $jadwalKlien)
    {
        $request->validate([
            'catatan_kunjungan' => 'required|string|min:5|max:1000',
            'hasil_tipe' => 'required|in:pembelian,tidak_ada_uang,tidak_ada_orang,tidak_ada_minat,dilanjutkan,lainnya',
            'nominal_transaksi' => 'nullable|numeric|min:0',
            'lat_checkout' => 'required|numeric|between:-90,90',
            'lng_checkout' => 'required|numeric|between:-180,180',
            'accuracy_checkout' => 'required|numeric|min:0'
        ]);

        // Verify user owns the schedule
        if ($jadwalKlien->jadwalKunjungan->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        // Verify required documentation exists
        if (!$jadwalKlien->foto_checkin || !$jadwalKlien->foto_checkout) {
            return response()->json([
                'success' => false,
                'message' => 'Both check-in and check-out photos are required'
            ], 400);
        }

        if (!$jadwalKlien->tanda_tangan) {
            return response()->json([
                'success' => false,
                'message' => 'Digital signature is required'
            ], 400);
        }

        // Complete the form
        $formData = array_merge(
            $request->only(['catatan_kunjungan', 'hasil_tipe', 'nominal_transaksi']),
            $request->only(['lat_checkout', 'lng_checkout', 'accuracy_checkout']),
            [
                'foto_checkin' => $jadwalKlien->foto_checkin,
                'foto_checkout' => $jadwalKlien->foto_checkout,
                'tanda_tangan' => $jadwalKlien->tanda_tangan,
            ]
        );

        try {
            $jadwalKlien->completeForm($formData);

            return response()->json([
                'success' => true,
                'message' => 'Visit form submitted successfully',
                'redirect' => route('sales.pjp.today')
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to submit visit form', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit form: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get photo preview
     * GET /sales/pjp/klien/{jadwalKlien}/photo/{type}
     */
    public function getPhotoPreview(JadwalKlien $jadwalKlien, string $type)
    {
        // Verify user owns the schedule
        if ($jadwalKlien->jadwalKunjungan->user_id !== Auth::id()) {
            return abort(403, 'Unauthorized');
        }

        $fieldName = $type === 'checkin' ? 'foto_checkin' : 'foto_checkout';
        $photoPath = $jadwalKlien->{$fieldName};

        if (!$photoPath || !Storage::exists($photoPath)) {
            return abort(404, 'Photo not found');
        }

        return Storage::response($photoPath);
    }

    /**
     * Delete photo
     * DELETE /sales/pjp/klien/{jadwalKlien}/delete-photo
     */
    public function deletePhoto(Request $request, JadwalKlien $jadwalKlien)
    {
        $request->validate([
            'type' => 'required|in:checkin,checkout'
        ]);

        // Verify user owns the schedule
        if ($jadwalKlien->jadwalKunjungan->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $fieldName = $request->input('type') === 'checkin' ? 'foto_checkin' : 'foto_checkout';
        $photoPath = $jadwalKlien->{$fieldName};

        if ($photoPath) {
            $this->photoService->deletePhoto($photoPath);
            $jadwalKlien->update([$fieldName => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Photo deleted successfully'
        ]);
    }
}
