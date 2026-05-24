@extends('layouts.sales')

@section('title', 'Jadwal Kunjungan Hari Ini')

@section('content')
<div class="container-fluid mt-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-3">
                <i class="fas fa-calendar-day"></i> Jadwal Kunjungan Hari Ini
                <span class="badge bg-primary float-end">{{ now()->format('d M Y') }}</span>
            </h2>
        </div>
    </div>

    <!-- Journey Status -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Status Perjalanan:</h6>
                            <p>
                                @if($jadwal->status === 'pending')
                                    <span class="badge bg-warning text-dark px-3 py-2">
                                        <i class="fas fa-hourglass-start"></i> Menunggu Dimulai
                                    </span>
                                @elseif($jadwal->status === 'aktif')
                                    <span class="badge bg-info px-3 py-2">
                                        <i class="fas fa-spinner fa-spin"></i> Perjalanan Berlangsung
                                    </span>
                                @else
                                    <span class="badge bg-success px-3 py-2">
                                        <i class="fas fa-check-circle"></i> Selesai
                                    </span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <h6>Progress Kunjungan:</h6>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-success" role="progressbar" 
                                     style="width: {{ $jadwal->getProgressPercentage() }}%; font-weight: bold; font-size: 12px;"
                                     aria-valuenow="{{ $jadwal->getProgressPercentage() }}" aria-valuemin="0" aria-valuemax="100">
                                    {{ $jadwal->getCompletedKlienCount() }}/{{ $jadwal->getTotalKlienCount() }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            @if($jadwal->status === 'pending')
                                <button class="btn btn-primary btn-lg" id="startJourneyBtn">
                                    <i class="fas fa-play-circle"></i> Mulai Perjalanan
                                </button>
                            @elseif($jadwal->status === 'aktif')
                                <button class="btn btn-danger btn-lg" id="endJourneyBtn">
                                    <i class="fas fa-stop-circle"></i> Selesaikan Perjalanan
                                </button>
                            @else
                                <button class="btn btn-secondary btn-lg" disabled>
                                    <i class="fas fa-check-circle"></i> Perjalanan Telah Selesai
                                </button>
                            @endif
                        </div>
                        <div class="col-md-6 text-end">
                            <a href="{{ route('sales.pjp.show', $jadwal->id) }}" class="btn btn-info btn-lg">
                                <i class="fas fa-mapmarked"></i> Lihat Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Klien List -->
    <div class="row">
        <div class="col-md-12">
            <h5 class="mb-3">
                <i class="fas fa-store"></i> Daftar Klien untuk Dikunjungi
            </h5>

            @if($klien->isEmpty())
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Tidak ada klien untuk dikunjungi hari ini.
                </div>
            @else
                <div class="row">
                    @foreach($klien as $item)
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h6 class="mb-0">
                                            <span class="badge bg-primary">{{ $item->urutan }}</span>
                                            {{ $item->klien->nama_klien }}
                                        </h6>
                                        @if($item->status === 'completed')
                                            <span class="badge bg-success">
                                                <i class="fas fa-check"></i> Selesai
                                            </span>
                                        @elseif($item->status === 'active')
                                            <span class="badge bg-info">
                                                <i class="fas fa-spinner fa-spin"></i> Aktif
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-clock"></i> Menunggu
                                            </span>
                                        @endif
                                    </div>

                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-map-pin"></i> {{ $item->klien->alamat }}
                                    </p>

                                    <p class="text-muted small mb-2">
                                        <i class="fas fa-phone"></i> {{ $item->klien->contact_person ?? '-' }}
                                        @if($item->klien->phone)
                                            ({{ $item->klien->phone }})
                                        @endif
                                    </p>

                                    @if($item->durasi_kunjungan)
                                        <p class="text-muted small">
                                            <i class="fas fa-clock"></i> Durasi: {{ $item->durasi_kunjungan }} menit
                                        </p>
                                    @endif

                                    @if($item->hasil_kunjungan)
                                        <p class="text-muted small">
                                            <i class="fas fa-note-sticky"></i> {{ $item->hasil_kunjungan }}
                                        </p>
                                    @endif

                                    <div class="mt-3 gap-2 d-flex flex-wrap">
                                        <a href="https://maps.google.com/?q={{ $item->klien->latitude }},{{ $item->klien->longitude }}" 
                                           target="_blank" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-directions"></i> Arah
                                        </a>

                                        @if($item->status !== 'completed' && $jadwal->status === 'aktif')
                                            @if($item->status === 'pending')
                                                <button class="btn btn-sm btn-primary checkin-btn" data-jadwal-klien-id="{{ $item->id }}">
                                                    <i class="fas fa-check-in"></i> Check-In
                                                </button>
                                            @else
                                                <button class="btn btn-sm btn-warning checkout-btn" data-jadwal-klien-id="{{ $item->id }}">
                                                    <i class="fas fa-check-out"></i> Check-Out
                                                </button>
                                            @endif
                                        @elseif($item->status === 'active' && !$item->isFormComplete())
                                            <a href="{{ route('sales.pjp.form', [$jadwal->id, $item->id]) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-clipboard-list"></i> Lengkapi Form
                                            </a>
                                        @elseif($item->isFormComplete())
                                            <span class="badge bg-success ms-2">
                                                <i class="fas fa-file-check"></i> Form Lengkap
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Start Journey
    document.getElementById('startJourneyBtn')?.addEventListener('click', function() {
        if (confirm('Mulai perjalanan kunjungan hari ini?')) {
            fetch('{{ route("sales.pjp.start", $jadwal->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => alert('Terjadi kesalahan: ' + error));
        }
    });

    // End Journey
    document.getElementById('endJourneyBtn')?.addEventListener('click', function() {
        if (confirm('Akhiri perjalanan kunjungan?')) {
            fetch('{{ route("sales.pjp.end", $jadwal->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    location.reload();
                } else {
                    alert('Error: ' + data.error);
                }
            })
            .catch(error => alert('Terjadi kesalahan: ' + error));
        }
    });

    // Check-In to Klien
    document.querySelectorAll('.checkin-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const jadwalKlienId = this.dataset.jadwalKlienId;
            
            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung GPS');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat...';

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    fetch(`{{ url('sales/pjp/klien') }}/${jadwalKlienId}/checkin`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude,
                            accuracy: Math.round(position.coords.accuracy),
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert(data.message);
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                            btn.disabled = false;
                            btn.innerHTML = '<i class="fas fa-check-in"></i> Check-In';
                        }
                    });
                },
                function(error) {
                    alert('Gagal mendapatkan GPS: ' + error.message);
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-check-in"></i> Check-In';
                }
            );
        });
    });

    // Check-Out from Klien
    document.querySelectorAll('.checkout-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const jadwalKlienId = this.dataset.jadwalKlienId;
            const hasil = prompt('Hasil kunjungan (opsional):');

            if (hasil !== null) {
                fetch(`{{ url('sales/pjp/klien') }}/${jadwalKlienId}/checkout`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        hasil_kunjungan: hasil,
                    }),
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + data.error);
                    }
                });
            }
        });
    });
</script>
@endpush

@endsection
