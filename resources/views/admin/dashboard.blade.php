@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
@php
    $authUser = auth()->user();
    $canManageConfig = $authUser?->can('manage_config');
    $canViewReports = $authUser?->can('view_reports');
    $canExportReports = $authUser?->can('export_reports');
    $canViewKunjungan = $authUser?->can('view_kunjungan');
@endphp
<div class="row mb-4">
    <div class="col-md-12">
        <h2>
            <i class="fas fa-gauge-high me-2"></i>Dashboard Administrator
        </h2>
        <p class="text-muted">
            Ringkasan operasional, monitoring, dan konfigurasi sistem
        </p>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-layer-group me-2"></i>Operasional
                </h5>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card border-left-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Pengguna Aktif</h6>
                        <h3 class="mb-0">{{ $totalUsers }}</h3>
                        <small class="text-muted">Sales aktif: <span id="active-sales-count">{{ $activeSales }}</span></small>
                    </div>
                    <div class="stat-icon text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <hr>
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-primary">
                    <i class="fas fa-arrow-right me-1"></i>Kelola Pengguna
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card border-left-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Klien Aktif</h6>
                        <h3 class="mb-0">{{ $totalKlien }}</h3>
                        <small class="text-muted">Wilayah: {{ $totalWilayah }}</small>
                    </div>
                    <div class="stat-icon text-success">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <hr>
                <a href="{{ route('admin.klien.index') }}" class="btn btn-sm btn-success">
                    <i class="fas fa-arrow-right me-1"></i>Kelola Klien
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card border-left-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">PJP Hari Ini</h6>
                        <h3 class="mb-0">{{ $todaySchedules }}</h3>
                        <small class="text-muted">Kunjungan: <span id="total-visits-count">{{ $todayVisits }}</span></small>
                    </div>
                    <div class="stat-icon text-warning">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <hr>
                <a href="{{ route('admin.pjp.create') }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-arrow-right me-1"></i>Buat PJP
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card border-left-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Aktivitas Hari Ini</h6>
                        <h3 class="mb-0" id="completed-visits-count">{{ $completedVisits }}</h3>
                        <small class="text-muted">Absensi aktif: {{ $activeAttendance }}</small>
                    </div>
                    <div class="stat-icon text-info">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <hr>
                <a href="{{ route('admin.attendance.recap') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-arrow-right me-1"></i>Rekap Absensi
                </a>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-pie me-2"></i>Monitoring & Laporan
                </h5>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card border-left-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Monitoring Real-Time</h6>
                        <p class="text-muted mb-0">Pantau lokasi, aktivitas, dan pergerakan sales.</p>
                    </div>
                    <div class="stat-icon text-info">
                        <i class="fas fa-satellite-dish"></i>
                    </div>
                </div>
                <hr>
                <a href="{{ route('admin.monitoring.index') }}" class="btn btn-sm btn-info">
                    <i class="fas fa-arrow-right me-1"></i>Buka Monitoring
                </a>
            </div>
        </div>
    </div>

    @if($canViewKunjungan)
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card border-left-success h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase text-muted mb-1">Galeri Kunjungan</h6>
                            <p class="text-muted mb-0">Cek dokumentasi foto dari kunjungan lapangan.</p>
                        </div>
                        <div class="stat-icon text-success">
                            <i class="fas fa-images"></i>
                        </div>
                    </div>
                    <hr>
                    <a href="{{ route('admin.photo-gallery.index') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-arrow-right me-1"></i>Lihat Galeri
                    </a>
                </div>
            </div>
        </div>
    @endif

    @if($canViewReports || $canExportReports)
        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card border-left-primary h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase text-muted mb-1">Analytics</h6>
                            <p class="text-muted mb-0">Ringkasan performa, tren, dan distribusi hasil.</p>
                        </div>
                        <div class="stat-icon text-primary">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                    <hr>
                    <a href="{{ route('admin.analytics.dashboard') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-right me-1"></i>Buka Analytics
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3 mb-4">
            <div class="card border-left-warning h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-uppercase text-muted mb-1">Export Laporan</h6>
                            <p class="text-muted mb-0">Unduh laporan sales performance untuk kebutuhan review.</p>
                        </div>
                        <div class="stat-icon text-warning">
                            <i class="fas fa-file-export"></i>
                        </div>
                    </div>
                    <hr>
                    <a href="{{ route('admin.reports.export-sales-performance') }}" class="btn btn-sm btn-warning">
                        <i class="fas fa-arrow-right me-1"></i>Export Sales
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>

@if($canManageConfig)
<div class="row mt-2">
    <div class="col-12 mb-3">
        <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                    <i class="fas fa-shield-halved me-2"></i>Konfigurasi Sistem
                    </h5>
                </div>
            </div>
    </div>

    <div class="col-md-6 col-xl-3 mb-4">
        <div class="card border-left-dark h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Konfigurasi Sistem</h6>
                        <p class="text-muted mb-0">Kelola pengaturan inti aplikasi dan batas operasional.</p>
                    </div>
                    <div class="stat-icon text-dark">
                        <i class="fas fa-cog"></i>
                    </div>
                </div>
                <hr>
                <a href="{{ route('admin.configuration.index') }}" class="btn btn-sm btn-dark">
                    <i class="fas fa-arrow-right me-1"></i>Buka Konfigurasi
                </a>
            </div>
        </div>
    </div>
</div>
@endif

<div class="row mt-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>Aksi Cepat
                </h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <a href="{{ route('admin.users.create') }}" class="btn btn-outline-primary w-100 py-3">
                            <i class="fas fa-user-plus mb-2 quick-action-icon"></i>
                            Tambah Pengguna
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.klien.create') }}" class="btn btn-outline-success w-100 py-3">
                            <i class="fas fa-plus-circle mb-2 quick-action-icon"></i>
                            Tambah Klien
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.pjp.create') }}" class="btn btn-outline-warning w-100 py-3">
                            <i class="fas fa-calendar-plus mb-2 quick-action-icon"></i>
                            Buat Jadwal
                        </a>
                    </div>
                    @if($canViewReports || $canExportReports)
                        <div class="col-md-3">
                            <a href="{{ route('admin.analytics.dashboard') }}" class="btn btn-outline-secondary w-100 py-3">
                                <i class="fas fa-chart-pie mb-2 quick-action-icon"></i>
                                Analytics
                            </a>
                        </div>
                    @endif
                    <div class="col-md-3">
                        <a href="{{ route('admin.wilayah.index') }}" class="btn btn-outline-primary w-100 py-3">
                            <i class="fas fa-map mb-2 quick-action-icon"></i>
                            Kelola Wilayah
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('admin.photo-gallery.index') }}" class="btn btn-outline-success w-100 py-3">
                            <i class="fas fa-images mb-2 quick-action-icon"></i>
                            Galeri Kunjungan
                        </a>
                    </div>
                    @if($canViewReports || $canExportReports)
                        <div class="col-md-3">
                            <a href="{{ route('admin.reports.export-sales-performance') }}" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-file-export mb-2 quick-action-icon"></i>
                                Export Sales
                            </a>
                        </div>
                    @endif
                    @if($canManageConfig)
                        <div class="col-md-3">
                            <a href="{{ route('admin.configuration.index') }}" class="btn btn-outline-dark w-100 py-3">
                                <i class="fas fa-cog mb-2 quick-action-icon"></i>
                                Konfigurasi Sistem
                            </a>
                        </div>
                    @endif
                    <div class="col-md-3">
                        <a href="{{ route('admin.monitoring.index') }}" class="btn btn-outline-secondary w-100 py-3">
                            <i class="fas fa-satellite-dish mb-2 quick-action-icon"></i>
                            Monitoring Real-Time
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('dashboard.partials.realtime-monitoring')

<style>
    .border-left-primary {
        border-left: 4px solid #0d6efd !important;
    }

    .border-left-success {
        border-left: 4px solid #198754 !important;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }

    .border-left-info {
        border-left: 4px solid #0dcaf0 !important;
    }

    .border-left-dark {
        border-left: 4px solid #212529 !important;
    }

    .stat-icon {
        font-size: 2rem;
        opacity: 0.2;
    }

    .quick-action-icon {
        display: block;
        font-size: 1.5rem;
    }
</style>
@endsection
