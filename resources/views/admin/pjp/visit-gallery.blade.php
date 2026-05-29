@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-images"></i> Galeri Kunjungan - {{ $jadwalKlien->klien->nama_klien }}</h2>
            <p class="text-muted">
                <strong>Jadwal:</strong> {{ $jadwalKlien->jadwalKunjungan->tanggal->format('d M Y') }} |
                <strong>Sales:</strong> {{ $jadwalKlien->jadwalKunjungan->user->name }} |
                <strong>Status:</strong> 
                @if($jadwalKlien->isFormComplete())
                    <span class="badge bg-success">Form Lengkap</span>
                @else
                    <span class="badge bg-warning">Belum Lengkap</span>
                @endif
            </p>
        </div>
    </div>

    <!-- Visit Details -->
    <div class="row mb-4">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Detail Kunjungan</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nama Klien:</strong> {{ $jadwalKlien->klien->nama_klien }}</p>
                            <p><strong>Alamat:</strong> {{ $jadwalKlien->klien->alamat }}</p>
                            <p><strong>Kontak:</strong> {{ $jadwalKlien->klien->phone ?? '-' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Check-In:</strong> {{ $jadwalKlien->waktu_checkin ?? '-' }}</p>
                            <p><strong>Check-Out:</strong> {{ $jadwalKlien->waktu_checkout ?? '-' }}</p>
                            <p><strong>Durasi:</strong> {{ $jadwalKlien->durasi_kunjungan ?? 0 }} menit</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Photos -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-camera"></i> Dokumentasi Foto</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Check-in Photo -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Foto Check-in</h6>
                                </div>
                                <div class="card-body text-center">
                                    @if($jadwalKlien->foto_checkin)
                                        <img src="{{ $jadwalKlien->getFotoCheckinUrl() }}" alt="Check-in" class="img-fluid rounded mb-2" style="max-height: 300px;">
                                        <div>
                                            <small class="text-muted">{{ $jadwalKlien->waktu_checkin }}</small>
                                        </div>
                                        <a href="{{ $jadwalKlien->getFotoCheckinUrl() }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-expand"></i> Perbesar
                                        </a>
                                    @else
                                        <p class="text-muted my-5">Belum ada foto check-in</p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Check-out Photo -->
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">Foto Check-out</h6>
                                </div>
                                <div class="card-body text-center">
                                    @if($jadwalKlien->foto_checkout)
                                        <img src="{{ $jadwalKlien->getFotoCheckoutUrl() }}" alt="Check-out" class="img-fluid rounded mb-2" style="max-height: 300px;">
                                        <div>
                                            <small class="text-muted">{{ $jadwalKlien->waktu_checkout }}</small>
                                        </div>
                                        <a href="{{ $jadwalKlien->getFotoCheckoutUrl() }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-expand"></i> Perbesar
                                        </a>
                                    @else
                                        <p class="text-muted my-5">Belum ada foto check-out</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visit Results & Notes -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-list-check"></i> Hasil & Catatan Kunjungan</h5>
                </div>
                <div class="card-body">
                    @if($jadwalKlien->hasil_tipe)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label"><strong>Hasil Kunjungan:</strong></label>
                                <p class="badge bg-info px-3 py-2">{{ $jadwalKlien->getHasilTipeLabel() }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label"><strong>Nominal Transaksi:</strong></label>
                                <p>Rp {{ number_format($jadwalKlien->nominal_transaksi ?? 0, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label"><strong>Catatan Kunjungan:</strong></label>
                            <div class="alert alert-light border">
                                {{ $jadwalKlien->catatan_kunjungan }}
                            </div>
                        </div>

                        @if($jadwalKlien->lat_checkout && $jadwalKlien->lng_checkout)
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label"><strong>Lokasi Check-out:</strong></label>
                                    <p>
                                        {{ $jadwalKlien->lat_checkout }}, {{ $jadwalKlien->lng_checkout }}
                                        <br>
                                        <small class="text-muted">Akurasi: {{ $jadwalKlien->accuracy_checkout }} meter</small>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <a href="https://maps.google.com/?q={{ $jadwalKlien->lat_checkout }},{{ $jadwalKlien->lng_checkout }}" target="_blank" class="btn btn-sm btn-outline-info">
                                        <i class="fas fa-map"></i> Lihat di Maps
                                    </a>
                                </div>
                            </div>
                        @endif
                    @else
                        <p class="text-muted">Hasil kunjungan belum diisi</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Signature -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-pen"></i> Tanda Tangan Digital</h5>
                </div>
                <div class="card-body text-center">
                    @if($jadwalKlien->tanda_tangan)
                        <img src="{{ $jadwalKlien->getTandaTanganUrl() }}" alt="Signature" class="img-fluid rounded" style="max-height: 150px; border: 1px solid #ddd; padding: 10px;">
                        <div>
                            <small class="text-muted d-block mt-2">Ditandatangani: {{ $jadwalKlien->waktu_form_selesai?->format('d M Y H:i') ?? '-' }}</small>
                        </div>
                    @else
                        <p class="text-muted my-5">Belum ada tanda tangan</p>
                    @endif
                </div>
            </div>

            <!-- Completion Status -->
            <div class="card">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0"><i class="fas fa-check-circle"></i> Status Kelengkapan</h5>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="{{ $jadwalKlien->foto_checkin ? 'text-success' : 'text-danger' }}">
                            <i class="fas {{ $jadwalKlien->foto_checkin ? 'fa-check-circle' : 'fa-times-circle' }}"></i> Foto Check-in
                        </span>
                    </div>
                    <div class="mb-2">
                        <span class="{{ $jadwalKlien->foto_checkout ? 'text-success' : 'text-danger' }}">
                            <i class="fas {{ $jadwalKlien->foto_checkout ? 'fa-check-circle' : 'fa-times-circle' }}"></i> Foto Check-out
                        </span>
                    </div>
                    <div class="mb-2">
                        <span class="{{ $jadwalKlien->catatan_kunjungan ? 'text-success' : 'text-danger' }}">
                            <i class="fas {{ $jadwalKlien->catatan_kunjungan ? 'fa-check-circle' : 'fa-times-circle' }}"></i> Catatan Kunjungan
                        </span>
                    </div>
                    <div class="mb-2">
                        <span class="{{ $jadwalKlien->tanda_tangan ? 'text-success' : 'text-danger' }}">
                            <i class="fas {{ $jadwalKlien->tanda_tangan ? 'fa-check-circle' : 'fa-times-circle' }}"></i> Tanda Tangan
                        </span>
                    </div>
                    <div class="mb-2">
                        <span class="{{ $jadwalKlien->hasil_tipe ? 'text-success' : 'text-danger' }}">
                            <i class="fas {{ $jadwalKlien->hasil_tipe ? 'fa-check-circle' : 'fa-times-circle' }}"></i> Hasil Tipe
                        </span>
                    </div>
                    <div class="mb-2">
                        <span class="{{ $jadwalKlien->lat_checkout && $jadwalKlien->lng_checkout ? 'text-success' : 'text-danger' }}">
                            <i class="fas {{ ($jadwalKlien->lat_checkout && $jadwalKlien->lng_checkout) ? 'fa-check-circle' : 'fa-times-circle' }}"></i> GPS Check-out
                        </span>
                    </div>

                    <hr>

                    <div class="text-center">
                        @if($jadwalKlien->isFormComplete())
                            <span class="badge bg-success p-2">
                                <i class="fas fa-check-double"></i> FORM LENGKAP
                            </span>
                        @else
                            <span class="badge bg-warning text-dark p-2">
                                <i class="fas fa-exclamation-triangle"></i> BELUM LENGKAP
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Back Button -->
    <div class="row">
        <div class="col-md-12">
            <a href="{{ route('admin.pjp.edit', $jadwalKlien->jadwalKunjungan->id) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </div>
    </div>
</div>
@endsection
