@extends('layouts.sales')

@section('title', 'Sales Dashboard')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-1"><i class="fas fa-gauge-high"></i> Dashboard Sales</h2>
            <p class="text-muted mb-0">Ringkasan operasional hari ini</p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Absensi</h6>
                            @if($todayAbsensi?->waktu_keluar)
                                <h5 class="mb-0 text-success">Sudah Check-Out</h5>
                                <small>{{ $todayAbsensi->waktu_masuk }} - {{ $todayAbsensi->waktu_keluar }}</small>
                            @elseif($todayAbsensi?->waktu_masuk)
                                <h5 class="mb-0 text-warning">Sedang Bekerja</h5>
                                <small>Masuk {{ $todayAbsensi->waktu_masuk }}</small>
                            @else
                                <h5 class="mb-0 text-danger">Belum Check-In</h5>
                                <small>Mulai dari menu absensi</small>
                            @endif
                        </div>
                        <i class="fas fa-clock fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Kunjungan</h6>
                            <h5 class="mb-0">{{ $completedVisits }}/{{ $totalVisits }} Selesai</h5>
                            <small>{{ number_format($progressPercentage, 1) }}% progress</small>
                        </div>
                        <i class="fas fa-map-pin fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-muted mb-1">Jam Kerja</h6>
                            @if($todayAbsensi?->total_jam)
                                <h5 class="mb-0">{{ floor($todayAbsensi->total_jam / 60) }}h {{ $todayAbsensi->total_jam % 60 }}m</h5>
                                <small>Total hari ini</small>
                            @elseif($todayAbsensi?->waktu_masuk)
                                <h5 class="mb-0">Berlangsung</h5>
                                <small>Durasi dihitung saat check-out</small>
                            @else
                                <h5 class="mb-0">-</h5>
                                <small>Belum ada absensi</small>
                            @endif
                        </div>
                        <i class="fas fa-hourglass-half fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-calendar-check"></i> Jadwal Hari Ini</h5>
                </div>
                <div class="card-body">
                    @if($jadwal)
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <div class="mb-2">
                                    @if($jadwal->status === \App\Models\JadwalKunjungan::STATUS_PENDING)
                                        <span class="badge bg-warning text-dark">Menunggu Dimulai</span>
                                    @elseif($jadwal->status === \App\Models\JadwalKunjungan::STATUS_ACTIVE)
                                        <span class="badge bg-info">Perjalanan Berlangsung</span>
                                    @else
                                        <span class="badge bg-success">Selesai</span>
                                    @endif
                                </div>
                                <p class="text-muted mb-0">{{ $totalVisits }} klien dijadwalkan untuk hari ini.</p>
                            </div>
                            <a href="{{ route('sales.pjp.show', $jadwal) }}" class="btn btn-outline-primary">
                                <i class="fas fa-list"></i> Detail PJP
                            </a>
                        </div>

                        <div class="progress mb-3" style="height: 24px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $progressPercentage }}%;">
                                {{ $completedVisits }}/{{ $totalVisits }}
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm align-middle">
                                <thead>
                                    <tr>
                                        <th>Urutan</th>
                                        <th>Klien</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($jadwalKlien->take(5) as $item)
                                        <tr>
                                            <td>{{ $item->urutan }}</td>
                                            <td>{{ $item->klien->nama_klien }}</td>
                                            <td>
                                                @if($item->status === \App\Models\JadwalKlien::STATUS_COMPLETED)
                                                    <span class="badge bg-success">Selesai</span>
                                                @elseif($item->status === \App\Models\JadwalKlien::STATUS_ACTIVE)
                                                    <span class="badge bg-info">Aktif</span>
                                                @else
                                                    <span class="badge bg-secondary">Menunggu</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5>Belum Ada Jadwal Hari Ini</h5>
                            <p class="text-muted mb-0">Anda tetap dapat melakukan absensi harian.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-bolt"></i> Aksi Cepat</h5>
                </div>
                <div class="card-body d-grid gap-2">
                    <a href="{{ route('sales.attendance.index') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-clock"></i> Buka Absensi
                    </a>
                    <a href="{{ route('sales.pjp.today') }}" class="btn btn-success btn-lg">
                        <i class="fas fa-calendar-day"></i> Jadwal Hari Ini
                    </a>
                    @if($jadwal)
                        <a href="{{ route('sales.pjp.show', $jadwal) }}" class="btn btn-outline-info btn-lg">
                            <i class="fas fa-route"></i> Detail Kunjungan
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-history"></i> Kunjungan Terakhir</h5>
                </div>
                <div class="card-body">
                    @if($recentVisits->isEmpty())
                        <p class="text-muted text-center mb-0">Belum ada riwayat kunjungan selesai.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Tanggal</th>
                                        <th>Klien</th>
                                        <th>Hasil</th>
                                        <th>Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentVisits as $visit)
                                        <tr>
                                            <td>{{ $visit->jadwalKunjungan?->tanggal?->format('d M Y') ?? '-' }}</td>
                                            <td>{{ $visit->klien?->nama_klien ?? '-' }}</td>
                                            <td>{{ $visit->getHasilTipeLabel() }}</td>
                                            <td>Rp {{ number_format($visit->nominal_transaksi ?? 0, 0, ',', '.') }}</td>
                                        </tr>
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
@endsection
