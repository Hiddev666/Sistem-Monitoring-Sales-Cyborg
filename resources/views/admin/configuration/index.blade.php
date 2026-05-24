@extends('layouts.app')

@section('title', 'Konfigurasi Sistem')

@section('content')
<div class="row mb-4">
    <div class="col-sm-6">
        <h1 class="h3 d-inline-block">⚙️ Konfigurasi Sistem</h1>
    </div>
</div>

@include('components.alerts')

<div class="card">
    <div class="card-body">
        <form action="{{ route('admin.configuration.update') }}" method="POST">
            @csrf
            @method('PUT')

            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Pengaturan ini mempengaruhi seluruh sistem
            </div>

            <h5 class="mb-3 border-bottom pb-2">GPS & Validasi</h5>

            <div class="mb-3">
                <label for="gps_radius_tolerance" class="form-label">
                    Toleransi Radius GPS <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="number" class="form-control @error('gps_radius_tolerance') is-invalid @enderror" 
                           id="gps_radius_tolerance" name="gps_radius_tolerance" value="{{ $gpsRadius }}" 
                           min="10" max="1000" required>
                    <span class="input-group-text">meter</span>
                </div>
                <small class="form-text text-muted">
                    Jarak maksimal (meter) penerimaan check-in dan check-out. Default: 100m
                </small>
                @error('gps_radius_tolerance')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <h5 class="mb-3 border-bottom pb-2 mt-4">Sesi & Keamanan</h5>

            <div class="mb-3">
                <label for="session_timeout_minutes" class="form-label">
                    Timeout Sesi <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="number" class="form-control @error('session_timeout_minutes') is-invalid @enderror" 
                           id="session_timeout_minutes" name="session_timeout_minutes" value="{{ $sessionTimeout }}" 
                           min="15" max="480" required>
                    <span class="input-group-text">menit</span>
                </div>
                <small class="form-text text-muted">
                    Waktu inaktif sebelum sesi berakhir otomatis. Rentang: 15-480 menit
                </small>
                @error('session_timeout_minutes')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <h5 class="mb-3 border-bottom pb-2 mt-4">Laporan & Export</h5>

            <div class="mb-3">
                <label for="export_format" class="form-label">
                    Format Export Default <span class="text-danger">*</span>
                </label>
                <select class="form-select @error('export_format') is-invalid @enderror" id="export_format" 
                        name="export_format" required>
                    <option value="pdf" {{ $exportFormat == 'pdf' ? 'selected' : '' }}>PDF (Portable Document)</option>
                    <option value="excel" {{ $exportFormat == 'excel' ? 'selected' : '' }}>Excel (XLSX)</option>
                    <option value="csv" {{ $exportFormat == 'csv' ? 'selected' : '' }}>CSV (Comma Separated)</option>
                </select>
                <small class="form-text text-muted">
                    Format default saat memicu download laporan
                </small>
                @error('export_format')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                <form action="{{ route('admin.configuration.reset') }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning" 
                            onclick="return confirm('Reset semua pengaturan ke nilai default?');">
                        <i class="fas fa-undo"></i> Reset ke Default
                    </button>
                </form>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-light">
        <h5 class="mb-0">📋 Informasi Sistem</h5>
    </div>
    <div class="card-body">
        <table class="table table-sm mb-0">
            <tr>
                <td><strong>Nama Aplikasi:</strong></td>
                <td>Monitoring Sales Force</td>
            </tr>
            <tr>
                <td><strong>Versi:</strong></td>
                <td>2.0.0</td>
            </tr>
            <tr>
                <td><strong>Framework:</strong></td>
                <td>Laravel 12</td>
            </tr>
            <tr>
                <td><strong>Database:</strong></td>
                <td>MySQL 8.0+</td>
            </tr>
        </table>
    </div>
</div>
@endsection
