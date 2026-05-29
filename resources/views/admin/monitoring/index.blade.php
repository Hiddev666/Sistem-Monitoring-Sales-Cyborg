@extends('layouts.app')

@section('title', 'Monitoring Admin')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h2>
            <i class="fas fa-satellite-dish me-2"></i>Monitoring Real-Time Administrator
        </h2>
        <p class="text-muted">Pantau pergerakan sales, kunjungan, dan status absensi secara langsung.</p>
    </div>
</div>

<div class="row">
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-primary h-100">
            <div class="card-body">
                <h6 class="text-uppercase text-muted mb-1">Pengguna Aktif</h6>
                <h3 class="mb-0">{{ $totalUsers }}</h3>
                <small class="text-muted">Sales aktif: {{ $activeSales }}</small>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-success h-100">
            <div class="card-body">
                <h6 class="text-uppercase text-muted mb-1">Jadwal Hari Ini</h6>
                <h3 class="mb-0">{{ $todaySchedules }}</h3>
                <small class="text-muted">Total kunjungan: {{ $todayVisits }}</small>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-info h-100">
            <div class="card-body">
                <h6 class="text-uppercase text-muted mb-1">Kunjungan Selesai</h6>
                <h3 class="mb-0">{{ $completedVisits }}</h3>
                <small class="text-muted">Status operasional terkini</small>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-warning h-100">
            <div class="card-body">
                <h6 class="text-uppercase text-muted mb-1">Absensi Aktif</h6>
                <h3 class="mb-0">{{ $activeAttendance }}</h3>
                <small class="text-muted">Belum checkout hari ini</small>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Kembali ke Dashboard
            </a>
            <a href="{{ route('admin.reports.export-sales-performance') }}" class="btn btn-outline-info">
                <i class="fas fa-file-export me-1"></i>Export Laporan Sales
            </a>
            <a href="{{ route('admin.analytics.dashboard') }}" class="btn btn-outline-primary">
                <i class="fas fa-chart-pie me-1"></i>Ringkasan Analytics
            </a>
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
</style>
@endsection
