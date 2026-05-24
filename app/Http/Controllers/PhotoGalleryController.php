<?php

namespace App\Http\Controllers;

use App\Models\JadwalKlien;
use App\Models\JadwalKunjungan;
use App\Models\Klien;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PhotoGalleryController extends Controller
{
    /**
     * Photo Gallery Index
     * GET /admin/photo-gallery
     */
    public function index(Request $request)
    {
        $query = JadwalKlien::where('status', 'completed')
            ->where(function ($q) {
                $q->whereNotNull('foto_checkin')
                  ->orWhereNotNull('foto_checkout')
                  ->orWhereNotNull('tanda_tangan');
            })
            ->with(['klien', 'jadwalKunjungan.user']);

        // Filters
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

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->get('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->get('end_date'));
        }

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
            'hasilTipeOptions'
        ));
    }

    /**
     * Gallery Grid View
     * GET /admin/photo-gallery/grid
     */
    public function grid(Request $request)
    {
        $query = JadwalKlien::where('status', 'completed')
            ->where(function ($q) {
                $q->whereNotNull('foto_checkin')
                  ->orWhereNotNull('foto_checkout');
            })
            ->with(['klien', 'jadwalKunjungan.user']);

        // Apply same filters as index
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->whereHas('klien', function ($q) use ($search) {
                $q->where('nama_klien', 'like', "%{$search}%");
            });
        }

        if ($request->filled('user_id')) {
            $query->whereHas('jadwalKunjungan', function ($q) use ($request) {
                $q->where('user_id', $request->get('user_id'));
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->get('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->get('end_date'));
        }

        $photos = $query->paginate(24);

        return view('admin.gallery.grid', compact('photos'));
    }

    /**
     * Photo Lightbox View
     * GET /admin/photo-gallery/{jadwalKlien}/lightbox
     */
    public function lightbox(JadwalKlien $jadwalKlien)
    {
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
            'current'
        ));
    }

    /**
     * Download photo
     * GET /admin/photo-gallery/{jadwalKlien}/download/{type}
     */
    public function downloadPhoto(JadwalKlien $jadwalKlien, string $type)
    {
        $fieldName = $type === 'checkin' ? 'foto_checkin' : 'foto_checkout';
        $photoPath = $jadwalKlien->{$fieldName};

        if (!$photoPath || !\Illuminate\Support\Facades\Storage::exists($photoPath)) {
            return response()->json(['error' => 'Photo not found'], 404);
        }

        $filename = $jadwalKlien->klien->nama_klien . '_' . $type . '_' . $jadwalKlien->created_at->format('Ymd_His');
        $extension = pathinfo($photoPath, PATHINFO_EXTENSION);

        return \Illuminate\Support\Facades\Storage::download($photoPath, $filename . '.' . $extension);
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

        if ($photoPath && \Illuminate\Support\Facades\Storage::exists($photoPath)) {
            \Illuminate\Support\Facades\Storage::delete($photoPath);
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
        $query = JadwalKlien::where('status', 'completed')
            ->where(function ($q) {
                $q->whereNotNull('foto_checkin')
                  ->orWhereNotNull('foto_checkout');
            });

        if ($request->filled('user_id')) {
            $query->whereHas('jadwalKunjungan', function ($q) use ($request) {
                $q->where('user_id', $request->get('user_id'));
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->get('start_date'));
        }

        $jadwalKlien = $query->get();

        $zip = new \ZipArchive();
        $zipPath = storage_path('exports/photos_' . time() . '.zip');
        
        if (!is_dir(storage_path('exports'))) {
            mkdir(storage_path('exports'), 0755, true);
        }

        if ($zip->open($zipPath, \ZipArchive::CREATE) === true) {
            foreach ($jadwalKlien as $item) {
                $folder = $item->klien->nama_klien . '_' . $item->created_at->format('Ymd');

                if ($item->foto_checkin && \Illuminate\Support\Facades\Storage::exists($item->foto_checkin)) {
                    $content = \Illuminate\Support\Facades\Storage::get($item->foto_checkin);
                    $zip->addFromString($folder . '/checkin.jpg', $content);
                }

                if ($item->foto_checkout && \Illuminate\Support\Facades\Storage::exists($item->foto_checkout)) {
                    $content = \Illuminate\Support\Facades\Storage::get($item->foto_checkout);
                    $zip->addFromString($folder . '/checkout.jpg', $content);
                }

                if ($item->tanda_tangan && \Illuminate\Support\Facades\Storage::exists($item->tanda_tangan)) {
                    $content = \Illuminate\Support\Facades\Storage::get($item->tanda_tangan);
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

        $totalWithPhotos = JadwalKlien::whereBetween('created_at', [$startDate, $endDate])
            ->where(function ($q) {
                $q->whereNotNull('foto_checkin')
                  ->orWhereNotNull('foto_checkout');
            })
            ->count();

        $totalWithSignature = JadwalKlien::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('tanda_tangan')
            ->count();

        $completeDocumentation = JadwalKlien::whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('foto_checkin')
            ->whereNotNull('foto_checkout')
            ->whereNotNull('tanda_tangan')
            ->count();

        $photoByHasilTipe = JadwalKlien::whereBetween('created_at', [$startDate, $endDate])
            ->where(function ($q) {
                $q->whereNotNull('foto_checkin')
                  ->orWhereNotNull('foto_checkout');
            })
            ->groupBy('hasil_tipe')
            ->selectRaw('hasil_tipe, COUNT(*) as count')
            ->get()
            ->mapWithKeys(fn ($item) => [
                $item->hasil_tipe => $item->count
            ])
            ->toArray();

        $photoByRep = JadwalKlien::join('jadwal_kunjungan', 'jadwal_klien.jadwal_kunjungan_id', '=', 'jadwal_kunjungan.id')
            ->join('users', 'jadwal_kunjungan.user_id', '=', 'users.id')
            ->whereBetween('jadwal_klien.created_at', [$startDate, $endDate])
            ->where(function ($q) {
                $q->whereNotNull('jadwal_klien.foto_checkin')
                  ->orWhereNotNull('jadwal_klien.foto_checkout');
            })
            ->groupBy('users.id', 'users.name')
            ->selectRaw('users.name, COUNT(*) as count')
            ->orderByDesc('count')
            ->get();

        return view('admin.gallery.statistics', compact(
            'totalWithPhotos',
            'totalWithSignature',
            'completeDocumentation',
            'photoByHasilTipe',
            'photoByRep',
            'startDate',
            'endDate'
        ));
    }
}
