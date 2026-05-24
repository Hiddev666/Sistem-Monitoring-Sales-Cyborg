@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-th"></i> Galeri Grid Foto</h2>
            <p class="text-muted">Tampilan grid foto kunjungan</p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Cari Klien</label>
                            <input type="text" name="search" class="form-control" placeholder="Nama atau alamat" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sales</label>
                            <select name="user_id" class="form-select">
                                <option value="">-- Semua --</option>
                                @foreach($salesReps as $rep)
                                    <option value="{{ $rep->id }}" {{ request('user_id') == $rep->id ? 'selected' : '' }}>
                                        {{ $rep->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Hasil</label>
                            <select name="hasil_tipe" class="form-select">
                                <option value="">-- Semua --</option>
                                @foreach($hasilTipeOptions as $value => $label)
                                    <option value="{{ $value }}" {{ request('hasil_tipe') == $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-1 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route('admin.photo-gallery.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-list"></i> Tampilan List
            </a>
            <a href="{{ route('admin.photo-gallery.statistics') }}" class="btn btn-outline-info me-2">
                <i class="fas fa-chart-bar"></i> Statistik
            </a>
        </div>
    </div>

    <!-- Photo Grid -->
    <div class="row g-3">
        @forelse($photos as $photo)
            <div class="col-md-4 col-lg-3">
                <div class="card h-100 position-relative overflow-hidden gallery-card" style="cursor: pointer;" 
                     onclick="openLightbox({{ $photo->id }})">
                    <!-- Overlay -->
                    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center opacity-0 gallery-overlay" 
                         style="transition: opacity 0.3s;">
                        <span class="text-white">
                            <i class="fas fa-expand fa-2x"></i>
                        </span>
                    </div>

                    <!-- Main Photo -->
                    @if($photo->foto_checkin)
                        <img src="{{ $photo->getFotoCheckinUrl() }}" alt="Check-in" class="card-img-top" style="height: 200px; object-fit: cover;">
                    @elseif($photo->foto_checkout)
                        <img src="{{ $photo->getFotoCheckoutUrl() }}" alt="Check-out" class="card-img-top" style="height: 200px; object-fit: cover;">
                    @else
                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                            <i class="fas fa-image text-muted fa-3x"></i>
                        </div>
                    @endif

                    <!-- Photo Badges -->
                    <div class="position-absolute top-2 end-2">
                        @if($photo->foto_checkin)
                            <span class="badge bg-success me-1">
                                <i class="fas fa-check-circle"></i> In
                            </span>
                        @endif
                        @if($photo->foto_checkout)
                            <span class="badge bg-info">
                                <i class="fas fa-times-circle"></i> Out
                            </span>
                        @endif
                    </div>

                    <!-- Card Body -->
                    <div class="card-body p-2">
                        <h6 class="card-title mb-1" style="font-size: 0.85rem;">{{ $photo->klien->nama_klien }}</h6>
                        <p class="card-text mb-2" style="font-size: 0.75rem; color: #666;">
                            {{ $photo->jadwalKunjungan->user->name }}<br/>
                            {{ $photo->created_at->format('d M Y') }}
                        </p>
                        <span class="badge bg-info" style="font-size: 0.7rem;">
                            {{ $photo->getHasilTipeLabel() }}
                        </span>
                    </div>

                    <!-- Hover Footer -->
                    <div class="card-footer bg-white d-flex gap-2 p-2 gallery-footer" style="display: none;">
                        <a href="{{ route('admin.photo-gallery.lightbox', $photo->id) }}" class="btn btn-sm btn-primary flex-grow-1" 
                           onclick="event.stopPropagation();">
                            <i class="fas fa-eye"></i>
                        </a>
                        @if($photo->foto_checkin)
                            <a href="{{ route('admin.photo-gallery.download', [$photo->id, 'checkin']) }}" class="btn btn-sm btn-outline-secondary" 
                               onclick="event.stopPropagation();">
                                <i class="fas fa-download"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-md-12">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Tidak ada foto ditemukan
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($photos->hasPages())
        <div class="row mt-4">
            <div class="col-md-12">
                {{ $photos->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Lightbox Modal -->
<div class="modal fade" id="lightboxModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Foto Detail</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="lightboxContent">
                <!-- Loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<style>
.gallery-card:hover .gallery-overlay {
    opacity: 1 !important;
}

.gallery-card:hover .gallery-footer {
    display: flex !important;
}
</style>

<script>
function openLightbox(photoId) {
    const url = `{{ route('admin.photo-gallery.lightbox', '__ID__') }}`.replace('__ID__', photoId);
    
    fetch(url)
        .then(response => response.text())
        .then(html => {
            document.getElementById('lightboxContent').innerHTML = html;
            new bootstrap.Modal(document.getElementById('lightboxModal')).show();
        })
        .catch(error => console.error('Error:', error));
}
</script>
@endsection
