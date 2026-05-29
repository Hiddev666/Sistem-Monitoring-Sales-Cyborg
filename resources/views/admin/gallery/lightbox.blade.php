<div class="row">
    <!-- Navigation -->
    @if($current > 0)
        <div class="col-12 mb-3">
                <a href="#" onclick="openLightbox({{ $allPhotos[$current-1]->id }}); return false;" class="btn btn-outline-secondary float-start">
                <i class="fas fa-chevron-left"></i> Sebelumnya
            </a>
        </div>
    @endif
    @if($current < count($allPhotos) - 1)
        <div class="col-12 mb-3">
            <a href="#" onclick="openLightbox({{ $allPhotos[$current+1]->id }}); return false;" class="btn btn-outline-secondary float-end">
                Berikutnya <i class="fas fa-chevron-right"></i>
            </a>
        </div>
    @endif

    <!-- Photo Display -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <strong>Foto</strong>
                <span class="float-end text-muted">{{ $current + 1 }} dari {{ count($allPhotos) }}</span>
            </div>
            <div class="card-body bg-light text-center">
                @if($jadwalKlien->foto_checkin)
                    <ul class="nav nav-tabs mb-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#tab-checkin" role="tab">
                                <i class="fas fa-check-circle"></i> Check-in
                            </a>
                        </li>
                        @if($jadwalKlien->foto_checkout)
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-checkout" role="tab">
                                    <i class="fas fa-times-circle"></i> Check-out
                                </a>
                            </li>
                        @endif
                        @if($jadwalKlien->tanda_tangan)
                            <li class="nav-item">
                                <a class="nav-link" data-bs-toggle="tab" href="#tab-signature" role="tab">
                                    <i class="fas fa-pen"></i> Tanda Tangan
                                </a>
                            </li>
                        @endif
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="tab-checkin" role="tabpanel">
                            <img src="{{ $jadwalKlien->getFotoCheckinUrl() }}" alt="Check-in" class="img-fluid rounded" style="max-height: 400px;">
                            <p class="text-muted mt-2">
                            <small>
                                {{ ($dateBasis === 'upload_date' ? $jadwalKlien->created_at : ($jadwalKlien->jadwalKunjungan->tanggal ?? $jadwalKlien->created_at))->format('d M Y') }}
                            </small>
                        </p>
                        </div>
                        @if($jadwalKlien->foto_checkout)
                            <div class="tab-pane fade" id="tab-checkout" role="tabpanel">
                                <img src="{{ $jadwalKlien->getFotoCheckoutUrl() }}" alt="Check-out" class="img-fluid rounded" style="max-height: 400px;">
                                <p class="text-muted mt-2">
                                <small>{{ $jadwalKlien->waktu_checkout ? $jadwalKlien->waktu_checkout->format('d M Y H:i') : 'N/A' }}</small>
                                </p>
                            </div>
                        @endif
                        @if($jadwalKlien->tanda_tangan)
                            <div class="tab-pane fade" id="tab-signature" role="tabpanel">
                                <img src="{{ $jadwalKlien->getTandaTanganUrl() }}" alt="Signature" class="img-fluid rounded" style="max-height: 400px;">
                                <p class="text-muted mt-2">
                                    <small>Tanda tangan klien</small>
                                </p>
                            </div>
                        @endif
                    </div>
                @else
                    <p class="text-muted">Tidak ada foto tersedia</p>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-3 d-flex gap-2">
            @if($jadwalKlien->foto_checkin)
                <a href="{{ route('admin.photo-gallery.download', ['jadwalKlien' => $jadwalKlien->id, 'type' => 'checkin', 'date_basis' => $dateBasis ?? 'visit_date']) }}" class="btn btn-outline-primary flex-grow-1">
                    <i class="fas fa-download"></i> Download Check-in
                </a>
            @endif
            @if($jadwalKlien->foto_checkout)
                <a href="{{ route('admin.photo-gallery.download', ['jadwalKlien' => $jadwalKlien->id, 'type' => 'checkout', 'date_basis' => $dateBasis ?? 'visit_date']) }}" class="btn btn-outline-primary flex-grow-1">
                    <i class="fas fa-download"></i> Download Check-out
                </a>
            @endif
            <form method="POST" action="{{ route('admin.photo-gallery.delete', $jadwalKlien->id) }}" style="display:inline;" 
                  onsubmit="return confirm('Hapus foto ini?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger flex-grow-1">
                    <i class="fas fa-trash"></i> Hapus
                </button>
            </form>
        </div>
    </div>

    <!-- Metadata -->
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <strong>Informasi Kunjungan</strong>
            </div>
            <div class="card-body" style="font-size: 0.9rem;">
                <p>
                    <strong>Klien:</strong><br>
                    {{ $jadwalKlien->klien->nama_klien }}<br>
                    <small class="text-muted">{{ $jadwalKlien->klien->alamat }}</small>
                </p>

                <p>
                    <strong>Sales:</strong><br>
                    {{ $jadwalKlien->jadwalKunjungan->user->name }}
                </p>

                <p>
                    <strong>Tanggal:</strong><br>
                    {{ ($dateBasis === 'upload_date' ? $jadwalKlien->created_at : ($jadwalKlien->jadwalKunjungan->tanggal ?? $jadwalKlien->created_at))->format('d M Y') }}
                </p>

                <p>
                    <strong>Hasil Kunjungan:</strong><br>
                    <span class="badge bg-info">
                        {{ $jadwalKlien->getHasilTipeLabel() }}
                    </span>
                </p>

                @if($jadwalKlien->nominal_transaksi)
                    <p>
                        <strong>Nominal:</strong><br>
                        Rp {{ number_format($jadwalKlien->nominal_transaksi, 0, ',', '.') }}
                    </p>
                @endif

                @if($jadwalKlien->catatan_kunjungan)
                    <p>
                        <strong>Catatan:</strong><br>
                        <small>{{ $jadwalKlien->catatan_kunjungan }}</small>
                    </p>
                @endif
            </div>
        </div>

        @if($jadwalKlien->lat_checkin)
            <div class="card">
                <div class="card-header">
                    <strong>Lokasi GPS</strong>
                </div>
                <div class="card-body" style="font-size: 0.85rem;">
                    <p class="mb-2">
                        <strong>Check-in:</strong><br>
                        <code>{{ number_format($jadwalKlien->lat_checkin, 6) }}, {{ number_format($jadwalKlien->lng_checkin, 6) }}</code>
                    </p>
                    @if($jadwalKlien->lat_checkout)
                        <p>
                            <strong>Check-out:</strong><br>
                            <code>{{ number_format($jadwalKlien->lat_checkout, 6) }}, {{ number_format($jadwalKlien->lng_checkout, 6) }}</code>
                        </p>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
