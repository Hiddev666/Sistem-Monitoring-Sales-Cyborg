@extends('layouts.app')

@section('title', 'Manager Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h2>
            <i class="fas fa-gauge-high me-2"></i>Dashboard Manajer
        </h2>
        <p class="text-muted">Monitoring real-time aktivitas dan kinerja sales force Anda</p>
    </div>
</div>

<div class="row">
    <!-- Sales Aktif -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Sales Aktif</h6>
                        <h3 class="mb-0" id="active-sales-count">{{ $activeSales }}</h3>
                    </div>
                    <div style="font-size: 2rem; color: #0d6efd; opacity: 0.2;">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Kunjungan Hari Ini -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Total Kunjungan</h6>
                        <h3 class="mb-0" id="total-visits-count">{{ $totalVisits }}</h3>
                    </div>
                    <div style="font-size: 2rem; color: #198754; opacity: 0.2;">
                        <i class="fas fa-map-pin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kunjungan Selesai -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Selesai</h6>
                        <h3 class="mb-0" id="completed-visits-count">{{ $completedVisits }}</h3>
                    </div>
                    <div style="font-size: 2rem; color: #0dcaf0; opacity: 0.2;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Alert</h6>
                        <h3 class="mb-0" id="alert-count">0</h3>
                    </div>
                    <div style="font-size: 2rem; color: #ffc107; opacity: 0.3;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2 mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bolt me-2"></i>Aksi Cepat
                </h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('manager.analytics.dashboard') }}" class="btn btn-outline-primary">
                        <i class="fas fa-chart-pie me-1"></i>Ringkasan Analytics
                    </a>
                    <a href="{{ route('manager.analytics.sales-performance') }}" class="btn btn-outline-success">
                        <i class="fas fa-chart-bar me-1"></i>Performa Sales
                    </a>
                    <a href="{{ route('manager.reports.export-sales-performance', ['start_date' => now()->subDays(30)->toDateString(), 'end_date' => now()->toDateString()]) }}" class="btn btn-outline-info">
                        <i class="fas fa-file-export me-1"></i>Export Sales
                    </a>
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
</style>

@endsection
