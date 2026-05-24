@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h2>
            <i class="fas fa-gauge-high me-2"></i>Dashboard Administrator
        </h2>
        <p class="text-muted">Selamat datang di panel administrasi sistem monitoring sales force</p>
    </div>
</div>

<div class="row">
    <!-- Total Users -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Total Pengguna</h6>
                        <h3 class="mb-0">0</h3>
                    </div>
                    <div style="font-size: 2rem; color: #0d6efd; opacity: 0.2;">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <hr>
                <a href="#" class="btn btn-sm btn-primary">
                    <i class="fas fa-arrow-right me-1"></i>Kelola Pengguna
                </a>
            </div>
        </div>
    </div>

    <!-- Total Klien -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Total Klien</h6>
                        <h3 class="mb-0">0</h3>
                    </div>
                    <div style="font-size: 2rem; color: #198754; opacity: 0.2;">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
                <hr>
                <a href="#" class="btn btn-sm btn-success">
                    <i class="fas fa-arrow-right me-1"></i>Kelola Klien
                </a>
            </div>
        </div>
    </div>

    <!-- Total Wilayah -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Total Wilayah</h6>
                        <h3 class="mb-0">0</h3>
                    </div>
                    <div style="font-size: 2rem; color: #ffc107; opacity: 0.3;">
                        <i class="fas fa-map"></i>
                    </div>
                </div>
                <hr>
                <a href="#" class="btn btn-sm btn-warning">
                    <i class="fas fa-arrow-right me-1"></i>Kelola Wilayah
                </a>
            </div>
        </div>
    </div>

    <!-- Total Jadwal -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">PJP Hari Ini</h6>
                        <h3 class="mb-0">0</h3>
                    </div>
                    <div style="font-size: 2rem; color: #0dcaf0; opacity: 0.2;">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
                <hr>
                <a href="#" class="btn btn-sm btn-info">
                    <i class="fas fa-arrow-right me-1"></i>Buat PJP
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-lightning-bolt me-2"></i>Aksi Cepat
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <a href="#" class="btn btn-outline-primary w-100 py-3">
                            <i class="fas fa-user-plus mb-2" style="font-size: 1.5rem; display: block;"></i>
                            Tambah Pengguna
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-outline-success w-100 py-3">
                            <i class="fas fa-plus-circle mb-2" style="font-size: 1.5rem; display: block;"></i>
                            Tambah Klien
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-outline-warning w-100 py-3">
                            <i class="fas fa-calendar-plus mb-2" style="font-size: 1.5rem; display: block;"></i>
                            Buat Jadwal
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="#" class="btn btn-outline-secondary w-100 py-3">
                            <i class="fas fa-cog mb-2" style="font-size: 1.5rem; display: block;"></i>
                            Pengaturan
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
