<?php

use App\Http\Controllers\AbsensiController;
use App\Http\Controllers\Admin\ConfigurationController;
use App\Http\Controllers\Admin\KlienController;
use App\Http\Controllers\Admin\PJPController;
use App\Http\Controllers\Admin\ReportExportController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\WilayahController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Dashboard\AdminDashboardController;
use App\Http\Controllers\Dashboard\AdminMonitoringController;
use App\Http\Controllers\Dashboard\AnalyticsController;
use App\Http\Controllers\Dashboard\ManagerDashboardController;
use App\Http\Controllers\Dashboard\SalesDashboardController;
use App\Http\Controllers\PhotoGalleryController;
use App\Http\Controllers\SalesPJPController;
use App\Http\Controllers\VisitFormController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root to login or dashboard
Route::get('/', function () {
    if (Auth::check()) {
        $user = Auth::user();

        if ($user->isSales()) {
            return redirect()->route('sales.dashboard');
        } elseif ($user->isManager()) {
            return redirect()->route('manager.dashboard');
        } elseif ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        // User has no valid role - logout and redirect to login
        Auth::logout();

        return redirect()->route('login')->with('error', 'User has no assigned role.');
    }

    return redirect()->route('login');
});

// ===========================
// Authentication Routes
// ===========================
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Protected routes
Route::middleware(['auth', 'session.timeout'])->group(function () {
    // Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // Password Management
    Route::get('/password/change', [PasswordController::class, 'showChangeForm'])->name('password.change');
    Route::post('/password/update', [PasswordController::class, 'update'])->name('password.update');
    Route::get('/visit-photo/{jadwalKlien}/{type}', [VisitFormController::class, 'getPhotoPreview'])
        ->name('visit-photo.preview');

    // ===========================
    // SALES ROUTES (Mobile Web)
    // ===========================
    Route::middleware('role:sales')->prefix('sales')->name('sales.')->group(function () {
        Route::get('/dashboard', [SalesDashboardController::class, 'index'])->name('dashboard');

        // ===========================
        // PHASE 3: ATTENDANCE & SCHEDULING
        // ===========================

        // Attendance (F-05, F-06, F-07)
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/', [AbsensiController::class, 'index'])->name('index');
            Route::get('/status', [AbsensiController::class, 'getStatus'])->name('status');
            Route::post('/checkin', [AbsensiController::class, 'checkIn'])->name('checkin');
            Route::post('/checkout', [AbsensiController::class, 'checkOut'])->name('checkout');
        });

        // PJP (Jadwal Kunjungan) - Sales View (F-09, F-10, F-11)
        Route::prefix('pjp')->name('pjp.')->group(function () {
            Route::get('/today', [SalesPJPController::class, 'today'])->name('today');
            Route::get('/{jadwal}', [SalesPJPController::class, 'show'])->name('show');
            Route::post('/{jadwal}/mulai-perjalanan', [SalesPJPController::class, 'startJourney'])->name('start');
            Route::post('/{jadwal}/selesai-perjalanan', [SalesPJPController::class, 'endJourney'])->name('end');
            Route::get('/{jadwal}/next-klien', [SalesPJPController::class, 'getNextKlien'])->name('next-klien');
            Route::get('/{jadwal}/progress', [SalesPJPController::class, 'getProgress'])->name('progress');
            Route::post('/klien/{jadwalKlien}/checkin', [SalesPJPController::class, 'checkInKlien'])->name('checkin-klien');
            Route::post('/klien/{jadwalKlien}/checkout', [SalesPJPController::class, 'checkOutKlien'])->name('checkout-klien');

            // ===========================
            // PHASE 4: VISIT FORM & PHOTOS
            // ===========================
            Route::get('/{jadwalKunjungan}/klien/{jadwalKlien}/form', [VisitFormController::class, 'show'])->name('form');
            Route::post('/klien/{jadwalKlien}/upload-photo', [VisitFormController::class, 'uploadPhoto'])->name('upload-photo');
            Route::post('/klien/{jadwalKlien}/upload-signature', [VisitFormController::class, 'uploadSignature'])->name('upload-signature');
            Route::post('/klien/{jadwalKlien}/submit-form', [VisitFormController::class, 'submitForm'])->name('submit-form');
            Route::delete('/klien/{jadwalKlien}/delete-photo', [VisitFormController::class, 'deletePhoto'])->name('delete-photo');
            Route::get('/klien/{jadwalKlien}/photo/{type}', [VisitFormController::class, 'getPhotoPreview'])->name('photo-preview');
        });
    });

    // ===========================
    // MANAGER ROUTES (Desktop)
    // ===========================
    Route::middleware('role:manager')->prefix('manager')->name('manager.')->group(function () {
        Route::get('/dashboard', [ManagerDashboardController::class, 'index'])->name('dashboard');
    });

    Route::middleware('role:admin,super_admin,manager')->prefix('manager')->name('manager.')->group(function () {
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/dashboard', [AnalyticsController::class, 'adminDashboard'])->name('dashboard');
            Route::get('/sales-performance', [AnalyticsController::class, 'salesPerformance'])->name('sales-performance');
            Route::get('/klien-analysis', [AnalyticsController::class, 'klienAnalysis'])->name('klien-analysis');
            Route::get('/regional-performance', [AnalyticsController::class, 'regionalPerformance'])->name('regional-performance');
        });

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/sales-performance/export', [ReportExportController::class, 'salesPerformance'])->name('export-sales-performance');
            Route::get('/regional-performance/export', [ReportExportController::class, 'regionalPerformance'])->name('export-regional-performance');
            Route::get('/klien-analysis/export', [ReportExportController::class, 'klienAnalysis'])->name('export-klien-analysis');
        });
    });

    // ===========================
    // ADMIN ROUTES (Desktop)
    // ===========================
    Route::middleware('role:admin,super_admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/monitoring', [AdminMonitoringController::class, 'index'])->name('monitoring.index');

        // ===========================
        // PHASE 2: MASTER DATA MANAGEMENT
        // ===========================

        // User Management (F-30)
        Route::prefix('users')->name('users.')->group(function () {
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/data', [UserController::class, 'getUsers'])->name('data');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        });

        // Klien/Toko Management (F-31)
        Route::prefix('klien')->name('klien.')->group(function () {
            Route::get('/', [KlienController::class, 'index'])->name('index');
            Route::get('/data', [KlienController::class, 'getKlien'])->name('data');
            Route::get('/create', [KlienController::class, 'create'])->name('create');
            Route::post('/', [KlienController::class, 'store'])->name('store');
            Route::get('/{klien}/edit', [KlienController::class, 'edit'])->name('edit');
            Route::put('/{klien}', [KlienController::class, 'update'])->name('update');
            Route::delete('/{klien}', [KlienController::class, 'destroy'])->name('destroy');
        });

        // Wilayah Management (F-32)
        Route::prefix('wilayah')->name('wilayah.')->group(function () {
            Route::get('/', [WilayahController::class, 'index'])->name('index');
            Route::get('/create', [WilayahController::class, 'create'])->name('create');
            Route::post('/', [WilayahController::class, 'store'])->name('store');
            Route::get('/{wilayah}/edit', [WilayahController::class, 'edit'])->name('edit');
            Route::put('/{wilayah}', [WilayahController::class, 'update'])->name('update');
            Route::delete('/{wilayah}', [WilayahController::class, 'destroy'])->name('destroy');
        });

        // Configuration (F-33)
        Route::prefix('configuration')->name('configuration.')->group(function () {
            Route::get('/', [ConfigurationController::class, 'index'])->name('index');
            Route::put('/', [ConfigurationController::class, 'update'])->name('update');
            Route::post('/reset', [ConfigurationController::class, 'reset'])->name('reset');
        });

        // ===========================
        // PHASE 3: ATTENDANCE & SCHEDULING
        // ===========================

        // PJP (Jadwal Kunjungan) Management (F-08, F-09, F-10, F-11)
        Route::prefix('pjp')->name('pjp.')->group(function () {
            Route::get('/', [PJPController::class, 'index'])->name('index');
            Route::get('/data', [PJPController::class, 'getData'])->name('data');
            Route::get('/create', [PJPController::class, 'create'])->name('create');
            Route::post('/', [PJPController::class, 'store'])->name('store');
            Route::get('/{jadwal}/edit', [PJPController::class, 'edit'])->name('edit');
            Route::put('/{jadwal}', [PJPController::class, 'update'])->name('update');
            Route::delete('/{jadwal}', [PJPController::class, 'destroy'])->name('destroy');
        });

        // Attendance Recap (F-05, F-06, F-07)
        Route::prefix('attendance')->name('attendance.')->group(function () {
            Route::get('/recap', [AbsensiController::class, 'recap'])->name('recap');
            Route::get('/data', [AbsensiController::class, 'getData'])->name('data');
        });

        // Photo Gallery
        Route::prefix('photo-gallery')->name('photo-gallery.')->group(function () {
            Route::get('/', [PhotoGalleryController::class, 'index'])->name('index');
            Route::get('/grid', [PhotoGalleryController::class, 'grid'])->name('grid');
            Route::get('/statistics', [PhotoGalleryController::class, 'statistics'])->name('statistics');
            Route::get('/{jadwalKlien}/lightbox', [PhotoGalleryController::class, 'lightbox'])->name('lightbox');
            Route::get('/{jadwalKlien}/download/{type}', [PhotoGalleryController::class, 'downloadPhoto'])->name('download');
            Route::delete('/{jadwalKlien}/delete-photo', [PhotoGalleryController::class, 'deletePhoto'])->name('delete');
            Route::post('/export-zip', [PhotoGalleryController::class, 'exportZip'])->name('export-zip');
        });
    });

    // ===========================
    // PHASE 5/8: DASHBOARD & REPORTING
    // ===========================
    Route::middleware('role:admin,super_admin,manager')->prefix('admin')->name('admin.')->group(function () {
        Route::prefix('analytics')->name('analytics.')->group(function () {
            Route::get('/dashboard', [AnalyticsController::class, 'adminDashboard'])->name('dashboard');
            Route::get('/sales-performance', [AnalyticsController::class, 'salesPerformance'])->name('sales-performance');
            Route::get('/klien-analysis', [AnalyticsController::class, 'klienAnalysis'])->name('klien-analysis');
            Route::get('/regional-performance', [AnalyticsController::class, 'regionalPerformance'])->name('regional-performance');
        });

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/sales-performance/export', [ReportExportController::class, 'salesPerformance'])->name('export-sales-performance');
            Route::get('/regional-performance/export', [ReportExportController::class, 'regionalPerformance'])->name('export-regional-performance');
            Route::get('/klien-analysis/export', [ReportExportController::class, 'klienAnalysis'])->name('export-klien-analysis');
        });
    });

    // ===========================
    // PHASE 5: API ROUTES
    // ===========================

    // Location Tracking & Dashboard API
    Route::prefix('api')->name('api.')->group(function () {
        Route::middleware('role:sales')->post('/location/update', [LocationController::class, 'updateLocation'])->name('location.update');
        Route::middleware('role:manager,admin,super_admin')->group(function () {
            Route::get('/dashboard/sales-locations', [LocationController::class, 'salesLocations'])->name('dashboard.sales-locations');
            Route::get('/dashboard/statistics', [LocationController::class, 'dashboardStatistics'])->name('dashboard.statistics');
        });
    });
});
