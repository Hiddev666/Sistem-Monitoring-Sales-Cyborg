@extends('layouts.sales')

@section('title', 'Jadwal Kunjungan')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-lg">
                <div class="card-body text-center p-5">
                    <div class="mb-4">
                        <i class="fas fa-calendar-times text-muted" style="font-size: 80px; opacity: 0.5;"></i>
                    </div>
                    
                    <h3 class="mb-3">Tidak Ada Jadwal</h3>
                    <p class="text-muted mb-4">
                        Anda belum memiliki jadwal kunjungan untuk hari ini. 
                        Silahkan hubungi supervisor atau kembali nanti.
                    </p>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i><br>
                        <small>
                            Namun Anda dapat tetap melakukan <strong>Check-In / Check-Out</strong> 
                            untuk mencatat kehadiran Anda.
                        </small>
                    </div>

                    <div class="d-grid gap-2">
                        @can('create_pjp_self')
                            <a href="{{ route('sales.pjp.create') }}" class="btn btn-success">
                                <i class="fas fa-plus-circle"></i> Buat Jadwal Sendiri
                            </a>
                        @endcan
                        <a href="{{ route('sales.attendance.index') }}" class="btn btn-primary">
                            <i class="fas fa-clock"></i> Buka Absensi
                        </a>
                        <a href="{{ route('sales.dashboard') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Info Card -->
            <div class="card mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-lightbulb"></i> Tips</h6>
                </div>
                <div class="card-body small">
                    <ul class="mb-0">
                        <li>Jadwal kunjungan biasanya dibuat oleh supervisor setiap hari</li>
                        <li>Pastikan hubungan Anda dengan supervisor lancar</li>
                        <li>Gunakan fitur Absensi untuk mencatat kehadiran harian</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
