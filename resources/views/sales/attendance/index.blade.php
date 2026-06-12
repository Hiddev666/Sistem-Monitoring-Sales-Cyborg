@extends('layouts.sales')

@section('title', 'Absensi/Attendance')

@section('content')
<div class="container-fluid mt-4">
    @php
        $attendanceWindowOpen = $attendanceWindow['is_open'] ?? true;
        $attendanceWindowMessage = $attendanceWindow['message'] ?? 'Absensi hanya dapat dilakukan antara pukul 08:00 sampai 16:30.';
        $attendanceWindowLabel = ($attendanceWindow['window_start'] ?? '08:00') . ' - ' . ($attendanceWindow['window_end'] ?? '16:30');
    @endphp

    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-3"><i class="fas fa-clock"></i> Absensi Hari Ini</h2>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="alert {{ $attendanceWindowOpen ? 'alert-success' : 'alert-warning' }}">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2">
                    <div>
                        <strong>Jam operasional absensi:</strong> {{ $attendanceWindowLabel }}
                        <div class="small mt-1">
                            {{ $attendanceWindowOpen ? 'Absensi sedang tersedia saat ini.' : $attendanceWindowMessage }}
                        </div>
                    </div>
                    <span class="badge {{ $attendanceWindowOpen ? 'bg-success' : 'bg-warning text-dark' }}">
                        {{ $attendanceWindowOpen ? 'Aktif' : 'Di luar jam absensi' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Today's Attendance Status -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    @if($todayAbsensi && $todayAbsensi->waktu_masuk)
                        <h5 class="card-title">Check-In</h5>
                        <p class="text-success display-6">
                            <i class="fas fa-check-circle"></i>
                        </p>
                        <p class="text-muted">Waktu masuk: <strong>{{ $todayAbsensi->waktu_masuk }}</strong></p>
                        <button class="btn btn-success btn-sm" disabled>
                            <i class="fas fa-check"></i> Sudah Check-In
                        </button>
                    @elseif(!$attendanceWindowOpen)
                        <h5 class="card-title">Check-In</h5>
                        <p class="text-warning display-6">
                            <i class="fas fa-clock"></i>
                        </p>
                        <p class="text-muted">Tersedia pada jam {{ $attendanceWindowLabel }}</p>
                        <button class="btn btn-primary" disabled>
                            <i class="fas fa-door-open"></i> Check-In
                        </button>
                    @else
                        <h5 class="card-title">Check-In</h5>
                        <p class="text-danger display-6">
                            <i class="fas fa-times-circle"></i>
                        </p>
                        <button class="btn btn-primary" id="checkInBtn">
                            <i class="fas fa-door-open"></i> Check-In
                        </button>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-body text-center">
                    @if($todayAbsensi && $todayAbsensi->waktu_keluar)
                        <h5 class="card-title">Check-Out</h5>
                        <p class="text-success display-6">
                            <i class="fas fa-check-circle"></i>
                        </p>
                        <p class="text-muted">Waktu keluar: <strong>{{ $todayAbsensi->waktu_keluar }}</strong></p>
                        <p class="text-muted">Durasi: <strong>{{ floor($todayAbsensi->total_jam / 60) }}h {{ $todayAbsensi->total_jam % 60 }}m</strong></p>
                        <button class="btn btn-success btn-sm" disabled>
                            <i class="fas fa-check"></i> Sudah Check-Out
                        </button>
                    @elseif(!$attendanceWindowOpen)
                        <h5 class="card-title">Check-Out</h5>
                        <p class="text-warning display-6">
                            <i class="fas fa-clock"></i>
                        </p>
                        <p class="text-muted">Tersedia pada jam {{ $attendanceWindowLabel }}</p>
                        <button class="btn btn-warning" disabled>
                            <i class="fas fa-door-closed"></i> Check-Out
                        </button>
                    @elseif($todayAbsensi && $todayAbsensi->waktu_masuk)
                        <h5 class="card-title">Check-Out</h5>
                        <p class="text-warning display-6">
                            <i class="fas fa-clock"></i>
                        </p>
                        <p class="text-muted">Menunggu check-out...</p>
                        <button class="btn btn-warning" id="checkOutBtn">
                            <i class="fas fa-door-closed"></i> Check-Out
                        </button>
                    @else
                        <h5 class="card-title">Check-Out</h5>
                        <p class="text-secondary display-6">
                            <i class="fas fa-ban"></i>
                        </p>
                        <p class="text-muted">Lakukan check-in terlebih dahulu</p>
                        <button class="btn btn-secondary btn-sm" disabled>
                            <i class="fas fa-ban"></i> N/A
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- GPS Info Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt"></i> Informasi Lokasi</h5>
                </div>
                <div class="card-body">
                    <div id="gpsStatus" class="alert alert-info">
                        <i class="fas fa-spinner fa-spin"></i> Memuat informasi GPS...
                    </div>
                    <small class="text-muted">
                        GPS dibutuhkan untuk validasi lokasi check-in dan check-out. Pastikan GPS aktif dan akurat.
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Attendance History -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Riwayat Absensi (7 Hari Terakhir)</h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Masuk</th>
                                <th>Keluar</th>
                                <th>Durasi</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAbsensi as $item)
                                <tr>
                                    <td>{{ $item->tanggal->format('d M Y') }}</td>
                                    <td>{{ $item->waktu_masuk ?? '-' }}</td>
                                    <td>{{ $item->waktu_keluar ?? '-' }}</td>
                                    <td>
                                        @if($item->total_jam)
                                            {{ floor($item->total_jam / 60) }}h {{ $item->total_jam % 60 }}m
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if($item->status === 'completed')
                                            <span class="badge bg-success">Selesai</span>
                                        @elseif($item->status === 'pending')
                                            <span class="badge bg-warning text-dark">Berlangsung</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada riwayat absensi</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Include custom attendance JS -->
@push('scripts')
<script>
    // Get user's GPS location
    function getUserLocation() {
        if (!navigator.geolocation) {
            showGpsStatus('error', 'Browser Anda tidak mendukung GPS');
            return;
        }

        showGpsStatus('loading', 'Memuat lokasi GPS...');

        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = Math.round(position.coords.accuracy);
                
                sessionStorage.setItem('userLat', lat);
                sessionStorage.setItem('userLng', lng);
                sessionStorage.setItem('userAccuracy', accuracy);
                
                showGpsStatus('success', `
                    <strong>Lokasi terdeteksi:</strong><br>
                    Latitude: ${lat.toFixed(6)}<br>
                    Longitude: ${lng.toFixed(6)}<br>
                    Akurasi: ±${accuracy}m
                `);
            },
            function(error) {
                showGpsStatus('error', 'Gagal mendapatkan GPS: ' + error.message);
            }
        );
    }

    function showGpsStatus(type, message) {
        const gpsDiv = document.getElementById('gpsStatus');
        gpsDiv.className = 'alert alert-' + (type === 'success' ? 'success' : type === 'loading' ? 'info' : 'danger');
        gpsDiv.innerHTML = message;
    }

    // Check-In Handler
    document.getElementById('checkInBtn')?.addEventListener('click', function() {
        const lat = sessionStorage.getItem('userLat');
        const lng = sessionStorage.getItem('userLng');
        const accuracy = sessionStorage.getItem('userAccuracy');

        if (!lat || !lng) {
            alert('Tidak dapat mendapatkan lokasi GPS. Harap aktifkan GPS dan coba lagi.');
            return;
        }

        this.disabled = true;

        fetch('{{ route("sales.attendance.checkin") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                latitude: parseFloat(lat),
                longitude: parseFloat(lng),
                accuracy: parseFloat(accuracy),
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
                this.disabled = false;
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error);
            this.disabled = false;
        });
    });

    // Check-Out Handler
    document.getElementById('checkOutBtn')?.addEventListener('click', function() {
        const lat = sessionStorage.getItem('userLat');
        const lng = sessionStorage.getItem('userLng');
        const accuracy = sessionStorage.getItem('userAccuracy');

        if (!lat || !lng) {
            alert('Tidak dapat mendapatkan lokasi GPS. Harap aktifkan GPS dan coba lagi.');
            return;
        }

        this.disabled = true;

        fetch('{{ route("sales.attendance.checkout") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                latitude: parseFloat(lat),
                longitude: parseFloat(lng),
                accuracy: parseFloat(accuracy),
            }),
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('Error: ' + data.message);
                this.disabled = false;
            }
        })
        .catch(error => {
            alert('Terjadi kesalahan: ' + error);
            this.disabled = false;
        });
    });

    // Load GPS on page load
    document.addEventListener('DOMContentLoaded', function() {
        getUserLocation();
    });
</script>
@endpush

@endsection
