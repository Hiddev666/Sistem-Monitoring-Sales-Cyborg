@extends('layouts.app')

@section('title', 'Manager Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h2 class="mb-1">
            <i class="fas fa-gauge-high me-2"></i>Dashboard Manajer
        </h2>
        <p class="text-muted mb-0">Ringkasan monitoring dan laporan untuk wilayah kerja Anda</p>
    </div>
</div>

<div class="row">
    <div class="col-12 mb-3">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-chart-column me-2"></i>Ringkasan Hari Ini
                </h5>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-primary h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Sales Aktif</h6>
                        <h3 class="mb-0" id="active-sales-count">{{ $activeSales }}</h3>
                    </div>
                    <div class="stat-icon text-primary">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-success h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Total Kunjungan</h6>
                        <h3 class="mb-0" id="total-visits-count">{{ $totalVisits }}</h3>
                    </div>
                    <div class="stat-icon text-success">
                        <i class="fas fa-map-pin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-info h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Selesai</h6>
                        <h3 class="mb-0" id="completed-visits-count">{{ $completedVisits }}</h3>
                    </div>
                    <div class="stat-icon text-info">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-warning h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Alert</h6>
                        <h3 class="mb-0" id="alert-count">0</h3>
                    </div>
                    <div class="stat-icon text-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
                    <div class="col-md-4">
                        <a href="{{ route('manager.analytics.dashboard') }}" class="btn btn-outline-primary w-100 py-3">
                            <i class="fas fa-chart-pie mb-2 quick-action-icon"></i>
                            Ringkasan Analytics
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('manager.analytics.sales-performance') }}" class="btn btn-outline-success w-100 py-3">
                            <i class="fas fa-chart-bar mb-2 quick-action-icon"></i>
                            Performa Sales
                        </a>
                    </div>
                    <div class="col-md-4">
                        <a href="{{ route('manager.reports.export-sales-performance', ['start_date' => now()->subDays(30)->toDateString(), 'end_date' => now()->toDateString()]) }}" class="btn btn-outline-info w-100 py-3">
                            <i class="fas fa-file-export mb-2 quick-action-icon"></i>
                            Export Sales
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-satellite-dish me-2"></i>Monitoring Real-Time
                </h5>
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
