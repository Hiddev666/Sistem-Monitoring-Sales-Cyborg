@extends('layouts.sales')

@section('title', 'Detail Jadwal Kunjungan')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="fas fa-route"></i> Detail Jadwal Kunjungan
            </h2>
            <p class="text-muted mb-0">
                {{ $jadwal->tanggal->format('d M Y') }}
                @if($jadwal->keterangan)
                    <span class="mx-2">|</span>{{ $jadwal->keterangan }}
                @endif
            </p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="{{ route('sales.pjp.today') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted small mb-1">Status</div>
                    @if($jadwal->isPendingStatus())
                        <span class="badge bg-warning text-dark">Menunggu</span>
                    @elseif($jadwal->isActiveStatus())
                        <span class="badge bg-info">Berlangsung</span>
                    @else
                        <span class="badge bg-success">Selesai</span>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted small mb-1">Total Klien</div>
                    <h4 class="mb-0">{{ $jadwal->getTotalKlienCount() }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted small mb-1">Selesai</div>
                    <h4 class="mb-0">{{ $jadwal->getCompletedKlienCount() }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <div class="text-muted small mb-1">Progress</div>
                    <h4 class="mb-0">{{ $jadwal->getProgressPercentage() }}%</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <div class="progress" style="height: 24px;">
                        <div class="progress-bar bg-success"
                             role="progressbar"
                             style="width: {{ $jadwal->getProgressPercentage() }}%;"
                             aria-valuenow="{{ $jadwal->getProgressPercentage() }}"
                             aria-valuemin="0"
                             aria-valuemax="100">
                            {{ $jadwal->getCompletedKlienCount() }}/{{ $jadwal->getTotalKlienCount() }}
                        </div>
                    </div>

                    <div class="mt-3">
                        @if($jadwal->isPendingStatus())
                            <button class="btn btn-primary" id="startJourneyBtn">
                                <i class="fas fa-play-circle"></i> Mulai Perjalanan
                            </button>
                        @elseif($jadwal->isActiveStatus())
                            <button class="btn btn-danger" id="endJourneyBtn">
                                <i class="fas fa-stop-circle"></i> Selesaikan Perjalanan
                            </button>
                        @else
                            <button class="btn btn-secondary" disabled>
                                <i class="fas fa-check-circle"></i> Perjalanan Telah Selesai
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <strong><i class="fas fa-store"></i> Daftar Klien</strong>
                </div>
                <div class="card-body">
                    @if($klien->isEmpty())
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle"></i> Tidak ada klien pada jadwal ini.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th style="width: 70px;">Urutan</th>
                                        <th>Klien</th>
                                        <th>Kontak</th>
                                        <th>Status</th>
                                        <th>Check-in</th>
                                        <th>Check-out</th>
                                        <th>Dokumentasi</th>
                                        <th class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($klien as $item)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary">{{ $item->urutan }}</span>
                                            </td>
                                            <td>
                                                <strong>{{ $item->klien->nama_klien }}</strong><br>
                                                <small class="text-muted">{{ $item->klien->alamat }}</small>
                                            </td>
                                            <td>
                                                {{ $item->klien->contact_person ?? '-' }}<br>
                                                <small class="text-muted">{{ $item->klien->phone ?? '-' }}</small>
                                            </td>
                                            <td>
                                                @if($item->isCompletedStatus())
                                                    <span class="badge bg-success">Selesai</span>
                                                @elseif($item->isActiveStatus())
                                                    <span class="badge bg-info">Aktif</span>
                                                @elseif($item->isSkippedStatus())
                                                    <span class="badge bg-dark">Dilewati</span>
                                                @else
                                                    <span class="badge bg-secondary">Menunggu</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $item->waktu_checkin ?? '-' }}
                                                @if($item->lat_checkin && $item->lng_checkin)
                                                    <br><small class="text-muted">{{ $item->getGpsFormatted() }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $item->waktu_checkout ?? '-' }}
                                                @if($item->lat_checkout && $item->lng_checkout)
                                                    <br><small class="text-muted">{{ $item->getGpsCheckoutFormatted() }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column gap-1">
                                                    <small>
                                                        <i class="fas {{ $item->foto_checkin ? 'fa-check text-success' : 'fa-minus text-muted' }}"></i>
                                                        Foto check-in
                                                    </small>
                                                    <small>
                                                        <i class="fas {{ $item->foto_checkout ? 'fa-check text-success' : 'fa-minus text-muted' }}"></i>
                                                        Foto check-out
                                                    </small>
                                                    <small>
                                                        <i class="fas {{ $item->tanda_tangan ? 'fa-check text-success' : 'fa-minus text-muted' }}"></i>
                                                        Tanda tangan
                                                    </small>
                                                </div>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <a href="https://maps.google.com/?q={{ $item->klien->latitude }},{{ $item->klien->longitude }}"
                                                       target="_blank"
                                                       class="btn btn-outline-primary">
                                                        <i class="fas fa-directions"></i>
                                                    </a>
                                                    @if($currentVisit && $currentVisit->id === $item->id && ($item->isActiveStatus() || $item->isCheckingOutStatus()) && !$item->isFormComplete())
                                                        <a href="{{ route('sales.pjp.form', [$jadwal->id, $item->id]) }}"
                                                           class="btn btn-outline-success">
                                                            <i class="fas fa-clipboard-list"></i>
                                                        </a>
                                                    @elseif($currentVisit && $currentVisit->id === $item->id && $item->isPendingStatus())
                                                        <button class="btn btn-outline-primary checkin-btn"
                                                                data-jadwal-klien-id="{{ $item->id }}">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                        @if($item->hasil_kunjungan || $item->catatan_kunjungan || $item->hasil_tipe || $item->nominal_transaksi)
                                            <tr>
                                                <td></td>
                                                <td colspan="7">
                                                    <div class="small text-muted">
                                                        @if($item->hasil_tipe)
                                                            <strong>Hasil:</strong> {{ $item->getHasilTipeLabel() }}
                                                        @endif
                                                        @if($item->nominal_transaksi)
                                                            <span class="ms-3"><strong>Nominal:</strong> Rp {{ number_format($item->nominal_transaksi, 0, ',', '.') }}</span>
                                                        @endif
                                                        @if($item->durasi_kunjungan)
                                                            <span class="ms-3"><strong>Durasi:</strong> {{ $item->durasi_kunjungan }} menit</span>
                                                        @endif
                                                        @if($item->hasil_kunjungan)
                                                            <div><strong>Hasil kunjungan:</strong> {{ $item->hasil_kunjungan }}</div>
                                                        @endif
                                                        @if($item->catatan_kunjungan)
                                                            <div><strong>Catatan:</strong> {{ $item->catatan_kunjungan }}</div>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('startJourneyBtn')?.addEventListener('click', function() {
        if (!confirm('Mulai perjalanan kunjungan ini?')) {
            return;
        }

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
                location.reload();
                return;
            }

            alert(data.error || 'Perjalanan gagal dimulai.');
        })
        .catch(error => alert('Terjadi kesalahan: ' + error));
    });

    document.getElementById('endJourneyBtn')?.addEventListener('click', function() {
        if (!confirm('Selesaikan perjalanan kunjungan ini?')) {
            return;
        }

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
                location.reload();
                return;
            }

            alert(data.error || 'Perjalanan gagal diselesaikan.');
        })
        .catch(error => alert('Terjadi kesalahan: ' + error));
    });

    document.querySelectorAll('.checkin-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const jadwalKlienId = this.dataset.jadwalKlienId;
            const checkinUrlTemplate = @json(route('sales.pjp.checkin-klien', ['jadwalKlien' => '__JADWAL_KLIEN__']));
            const checkinUrl = checkinUrlTemplate.replace('__JADWAL_KLIEN__', jadwalKlienId);

            if (!navigator.geolocation) {
                alert('Browser Anda tidak mendukung GPS');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memuat...';

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    fetch(checkinUrl, {
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
</script>
@endpush
@endsection
