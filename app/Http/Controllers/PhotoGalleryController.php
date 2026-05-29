<?php

namespace App\Http\Controllers;

use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PhotoGalleryController extends Controller
{
    /**
     * Photo Gallery Index
     * GET /admin/photo-gallery
     */
    public function index(Request $request)
    {
        $dateBasis = $request->get('date_basis', 'visit_date');
        $query = $this->buildGalleryQuery($request);
        $this->applyDateFilter($query, $request->get('start_date'), $request->get('end_date'), $dateBasis);

        $photos = $query->paginate(12);

        $salesReps = User::role('sales')->get();
        $wilayah = \App\Models\Wilayah::all();
        
        $hasilTipeOptions = [
            'pembelian' => 'Pembelian',
            'tidak_ada_uang' => 'Tidak Ada Uang',
            'tidak_ada_orang' => 'Tidak Ada Orang',
            'tidak_ada_minat' => 'Tidak Ada Minat',
            'dilanjutkan' => 'Dilanjutkan',
            'lainnya' => 'Lainnya'
        ];

        return view('admin.gallery.index', compact(
            'photos',
            'salesReps',
            'wilayah',
            'hasilTipeOptions',
            'dateBasis'
        ));
    }

    /**
     * Gallery Grid View
     * GET /admin/photo-gallery/grid
     */
    public function grid(Request $request)
    {
        $dateBasis = $request->get('date_basis', 'visit_date');
        $query = $this->buildGalleryQuery($request);
        $this->applyDateFilter($query, $request->get('start_date'), $request->get('end_date'), $dateBasis);

        $photos = $query->paginate(24);
        $salesReps = User::role('sales')->get();
        $wilayah = \App\Models\Wilayah::all();
        $hasilTipeOptions = $this->hasilTipeOptions();

        return view('admin.gallery.grid', compact(
            'photos',
            'salesReps',
            'wilayah',
            'hasilTipeOptions',
            'dateBasis'
        ));
    }

    /**
     * Photo Lightbox View
     * GET /admin/photo-gallery/{jadwalKlien}/lightbox
     */
    public function lightbox(Request $request, JadwalKlien $jadwalKlien)
    {
        $dateBasis = $request->get('date_basis', 'visit_date');

        // Get adjacent photos for navigation
        $current = JadwalKlien::where('status', 'completed')
            ->where(function ($q) {
                $q->whereNotNull('foto_checkin')
                  ->orWhereNotNull('foto_checkout');
            })
            ->orderBy('id')
            ->pluck('id')
            ->search($jadwalKlien->id);

        $allPhotos = JadwalKlien::where('status', 'completed')
            ->where(function ($q) {
                $q->whereNotNull('foto_checkin')
                  ->orWhereNotNull('foto_checkout');
            })
            ->orderBy('id')
            ->get();

        return view('admin.gallery.lightbox', compact(
            'jadwalKlien',
            'allPhotos',
            'current',
            'dateBasis'
        ));
    }

    /**
     * Download photo
     * GET /admin/photo-gallery/{jadwalKlien}/download/{type}
     */
    public function downloadPhoto(Request $request, JadwalKlien $jadwalKlien, string $type)
    {
        $fieldName = $type === 'checkin' ? 'foto_checkin' : 'foto_checkout';
        $photoPath = $jadwalKlien->{$fieldName};

        if (!$photoPath || !\Illuminate\Support\Facades\Storage::disk('local')->exists($photoPath)) {
            return response()->json(['error' => 'Photo not found'], 404);
        }

        $dateBasis = $request->get('date_basis', 'visit_date');
        $filename = $this->buildGalleryFilename($jadwalKlien, $type, $dateBasis);
        $extension = pathinfo($photoPath, PATHINFO_EXTENSION);

        return \Illuminate\Support\Facades\Storage::disk('local')->download($photoPath, $filename . '.' . $extension);
    }

    /**
     * Delete photo from gallery
     * DELETE /admin/photo-gallery/{jadwalKlien}/delete-photo
     */
    public function deletePhoto(Request $request, JadwalKlien $jadwalKlien)
    {
        $request->validate([
            'type' => 'required|in:checkin,checkout'
        ]);

        $fieldName = $request->input('type') === 'checkin' ? 'foto_checkin' : 'foto_checkout';
        $photoPath = $jadwalKlien->{$fieldName};

        if ($photoPath && \Illuminate\Support\Facades\Storage::disk('local')->exists($photoPath)) {
            \Illuminate\Support\Facades\Storage::disk('local')->delete($photoPath);
        }

        $jadwalKlien->update([$fieldName => null]);

        return response()->json([
            'success' => true,
            'message' => 'Photo deleted successfully'
        ]);
    }

    /**
     * Export photos as ZIP
     * POST /admin/photo-gallery/export-zip
     */
    public function exportZip(Request $request)
    {
        $query = $this->buildGalleryQuery($request);
        $dateBasis = $request->get('date_basis', 'visit_date');

        $this->applyDateFilter(
            $query,
            $request->get('start_date'),
            $request->get('end_date'),
            $dateBasis
        );

        $jadwalKlien = $query->get();

        $zip = new \ZipArchive();
        $zipPath = storage_path('exports/photos_' . time() . '.zip');
        
        if (!is_dir(storage_path('exports'))) {
            mkdir(storage_path('exports'), 0755, true);
        }

        if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
            foreach ($jadwalKlien as $item) {
                $folder = $this->buildGalleryFolderName($item, $dateBasis);

                if ($item->foto_checkin && \Illuminate\Support\Facades\Storage::disk('local')->exists($item->foto_checkin)) {
                    $content = \Illuminate\Support\Facades\Storage::disk('local')->get($item->foto_checkin);
                    $zip->addFromString($folder . '/checkin.jpg', $content);
                }

                if ($item->foto_checkout && \Illuminate\Support\Facades\Storage::disk('local')->exists($item->foto_checkout)) {
                    $content = \Illuminate\Support\Facades\Storage::disk('local')->get($item->foto_checkout);
                    $zip->addFromString($folder . '/checkout.jpg', $content);
                }

                if ($item->tanda_tangan && \Illuminate\Support\Facades\Storage::disk('local')->exists($item->tanda_tangan)) {
                    $content = \Illuminate\Support\Facades\Storage::disk('local')->get($item->tanda_tangan);
                    $zip->addFromString($folder . '/signature.png', $content);
                }
            }
            $zip->close();

            return response()->download($zipPath)->deleteFileAfterSend(true);
        }

        return redirect()->back()->with('error', 'Failed to create ZIP file');
    }

    /**
     * Photo Statistics
     * GET /admin/photo-gallery/statistics
     */
    public function statistics(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->subDays(30)->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->toDateString());
        $dateBasis = $request->get('date_basis', 'visit_date');
        $query = $this->buildGalleryQuery($request);
        $this->applyDateFilter($query, $startDate, $endDate, $dateBasis);

        $totalWithPhotos = (clone $query)
            ->where(function ($q) {
                $q->whereNotNull('foto_checkin')
                    ->orWhereNotNull('foto_checkout');
            })
            ->count();

        $totalWithSignature = (clone $query)
            ->whereNotNull('tanda_tangan')
            ->count();

        $completeDocumentation = (clone $query)
            ->whereNotNull('foto_checkin')
            ->whereNotNull('foto_checkout')
            ->whereNotNull('tanda_tangan')
            ->count();

        $hasilTipeOptions = $this->hasilTipeOptions();

        $photoByHasilTipe = (clone $query)
            ->selectRaw('hasil_tipe, COUNT(*) as total, SUM(CASE WHEN foto_checkin IS NOT NULL OR foto_checkout IS NOT NULL THEN 1 ELSE 0 END) as with_photos, SUM(CASE WHEN tanda_tangan IS NOT NULL THEN 1 ELSE 0 END) as with_signature, SUM(CASE WHEN foto_checkin IS NOT NULL AND foto_checkout IS NOT NULL AND tanda_tangan IS NOT NULL THEN 1 ELSE 0 END) as complete')
            ->groupBy('hasil_tipe')
            ->orderByDesc('total')
            ->get()
            ->mapWithKeys(function ($item) use ($hasilTipeOptions) {
                $key = $item->hasil_tipe ?? 'unknown';

                return [
                    $key => [
                        'label' => $hasilTipeOptions[$key] ?? ucfirst(str_replace('_', ' ', $key)),
                        'total' => (int) $item->total,
                        'with_photos' => (int) $item->with_photos,
                        'with_signature' => (int) $item->with_signature,
                        'complete' => (int) $item->complete,
                    ],
                ];
            });

        $photoByRep = (clone $query)
            ->join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
            ->join('users', 'jadwal_kunjungan.user_id', '=', 'users.id')
            ->selectRaw('users.id, users.name, COUNT(*) as total_schedules, SUM(CASE WHEN jadwal_klien.foto_checkin IS NOT NULL OR jadwal_klien.foto_checkout IS NOT NULL THEN 1 ELSE 0 END) as with_photos, SUM(CASE WHEN jadwal_klien.tanda_tangan IS NOT NULL THEN 1 ELSE 0 END) as with_signature, SUM(CASE WHEN jadwal_klien.foto_checkin IS NOT NULL AND jadwal_klien.foto_checkout IS NOT NULL AND jadwal_klien.tanda_tangan IS NOT NULL THEN 1 ELSE 0 END) as complete')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('with_photos')
            ->get();

        return view('admin.gallery.statistics', compact(
            'totalWithPhotos',
            'totalWithSignature',
            'completeDocumentation',
            'photoByHasilTipe',
            'photoByRep',
            'startDate',
            'endDate',
            'dateBasis'
        ));
    }

    private function applyDateFilter($query, ?string $startDate, ?string $endDate, string $dateBasis = 'visit_date')
    {
        if (!$startDate && !$endDate) {
            return $query;
        }

        if ($dateBasis === 'upload_date') {
            if ($startDate) {
                $query->whereDate('created_at', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('created_at', '<=', $endDate);
            }

            return $query;
        }

        return $query->whereHas('jadwalKunjungan', function ($query) use ($startDate, $endDate) {
            if ($startDate) {
                $query->whereDate('tanggal', '>=', $startDate);
            }

            if ($endDate) {
                $query->whereDate('tanggal', '<=', $endDate);
            }
        });
    }

    private function buildGalleryQuery(Request $request)
    {
        $query = JadwalKlien::where('status', 'completed')
            ->where(function ($q) {
                $q->whereNotNull('foto_checkin')
                    ->orWhereNotNull('foto_checkout')
                    ->orWhereNotNull('tanda_tangan');
            })
            ->with(['klien', 'jadwalKunjungan.user']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('klien', function ($q) use ($search) {
                $q->where('nama_klien', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->whereHas('jadwalKunjungan', function ($q) use ($request) {
                $q->where('user_id', $request->get('user_id'));
            });
        }

        if ($request->filled('wilayah_id')) {
            $query->whereHas('jadwalKunjungan.user', function ($q) use ($request) {
                $q->where('wilayah_id', $request->get('wilayah_id'));
            });
        }

        if ($request->filled('hasil_tipe')) {
            $query->where('hasil_tipe', $request->get('hasil_tipe'));
        }

        return $query;
    }

    private function hasilTipeOptions(): array
    {
        return [
            'pembelian' => 'Pembelian',
            'tidak_ada_uang' => 'Tidak Ada Uang',
            'tidak_ada_orang' => 'Tidak Ada Orang',
            'tidak_ada_minat' => 'Tidak Ada Minat',
            'dilanjutkan' => 'Dilanjutkan',
            'lainnya' => 'Lainnya',
        ];
    }

    private function buildGalleryFilename(JadwalKlien $jadwalKlien, string $type, string $dateBasis = 'visit_date'): string
    {
        return $this->buildGalleryNameBase($jadwalKlien, $dateBasis) . '_' . $type;
    }

    private function buildGalleryFolderName(JadwalKlien $jadwalKlien, string $dateBasis = 'visit_date'): string
    {
        return $this->buildGalleryNameBase($jadwalKlien, $dateBasis);
    }

    private function buildGalleryNameBase(JadwalKlien $jadwalKlien, string $dateBasis = 'visit_date'): string
    {
        $clientName = Str::slug($jadwalKlien->klien->nama_klien);
        $dateLabel = $dateBasis === 'upload_date'
            ? $jadwalKlien->created_at->format('Ymd')
            : ($jadwalKlien->jadwalKunjungan?->tanggal?->format('Ymd') ?? $jadwalKlien->created_at->format('Ymd'));

        return trim($clientName . '_' . $dateLabel, '_');
    }
}
