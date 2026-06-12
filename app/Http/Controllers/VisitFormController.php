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

        if (!$this->isEditableVisit($jadwalKlien)) {
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
            'type' => 'required|in:checkin,checkout',
            'capture_source' => 'required|in:camera',
        ], [
            'capture_source.required' => 'Foto harus diambil dari kamera.',
            'capture_source.in' => 'Foto harus diambil dari kamera handphone.',
        ]);

        if ($response = $this->authorizeEditableVisit($jadwalKlien)) {
            return $response;
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
        $jadwalKlien->refresh();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'photo' => [
                'path' => $result['path'],
                'url' => $request->input('type') === 'checkin'
                    ? $jadwalKlien->getFotoCheckinUrl()
                    : $jadwalKlien->getFotoCheckoutUrl(),
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

        if ($response = $this->authorizeEditableVisit($jadwalKlien)) {
            return $response;
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
        $jadwalKlien->refresh();

        return response()->json([
            'success' => true,
            'message' => $result['message'],
            'signature' => [
                'path' => $result['path'],
                'url' => $jadwalKlien->getTandaTanganUrl()
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
            'accuracy_checkout' => 'required|numeric|min:0|max:999999.99'
        ]);

        if ($response = $this->authorizeEditableVisit($jadwalKlien)) {
            return $response;
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
        if (!$this->canViewVisitPhoto($jadwalKlien)) {
            return abort(403, 'Unauthorized');
        }

        if (!in_array($type, ['checkin', 'checkout', 'signature'], true)) {
            return abort(404, 'Photo not found');
        }

        $fieldName = match ($type) {
            'checkin' => 'foto_checkin',
            'checkout' => 'foto_checkout',
            'signature' => 'tanda_tangan',
        };
        $photoPath = $jadwalKlien->{$fieldName};

        if (!$photoPath || !Storage::disk('local')->exists($photoPath)) {
            return abort(404, 'Photo not found');
        }

        return Storage::disk('local')->response($photoPath);
    }

    /**
     * Delete photo
     * DELETE /sales/pjp/klien/{jadwalKlien}/delete-photo
     */
    public function deletePhoto(Request $request, JadwalKlien $jadwalKlien)
    {
        $request->validate([
            'type' => 'required|in:checkin,checkout,signature'
        ]);

        if ($response = $this->authorizeEditableVisit($jadwalKlien)) {
            return $response;
        }

        $fieldName = match ($request->input('type')) {
            'checkin' => 'foto_checkin',
            'checkout' => 'foto_checkout',
            'signature' => 'tanda_tangan',
        };
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

    private function authorizeEditableVisit(JadwalKlien $jadwalKlien)
    {
        if ($jadwalKlien->jadwalKunjungan->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!$this->isEditableVisit($jadwalKlien)) {
            return response()->json([
                'success' => false,
                'message' => "Cannot update form for visit status: {$jadwalKlien->status}"
            ], 403);
        }

        return null;
    }

    private function isEditableVisit(JadwalKlien $jadwalKlien): bool
    {
        return $jadwalKlien->isEditableStatus();
    }

    private function canViewVisitPhoto(JadwalKlien $jadwalKlien): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        if ($jadwalKlien->jadwalKunjungan->user_id === $user->id) {
            return true;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isManager()
            && $user->wilayah_id
            && $jadwalKlien->jadwalKunjungan->user?->wilayah_id === $user->wilayah_id;
    }
}
