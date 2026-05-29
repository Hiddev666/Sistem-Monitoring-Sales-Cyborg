@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-images"></i> Galeri Foto Kunjungan</h2>
            <p class="text-muted">Kelola dan lihat foto dari setiap kunjungan</p>
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
                            <label class="form-label">Wilayah</label>
                            <select name="wilayah_id" class="form-select">
                                <option value="">-- Semua --</option>
                                @foreach($wilayah as $item)
                                    <option value="{{ $item->id }}" {{ request('wilayah_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->nama_wilayah }}
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
                            <label class="form-label">Basis Tanggal</label>
                            <select name="date_basis" class="form-select">
                                <option value="visit_date" {{ $dateBasis === 'visit_date' ? 'selected' : '' }}>Tanggal Kunjungan</option>
                                <option value="upload_date" {{ $dateBasis === 'upload_date' ? 'selected' : '' }}>Tanggal Upload</option>
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
            <a href="{{ route('admin.photo-gallery.grid') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-th"></i> Tampilan Grid
            </a>
            <a href="{{ route('admin.photo-gallery.statistics') }}" class="btn btn-outline-info me-2">
                <i class="fas fa-chart-bar"></i> Statistik
            </a>
            <form method="POST" action="{{ route('admin.photo-gallery.export-zip') }}" style="display:inline;">
                @csrf
                <input type="hidden" name="start_date" value="{{ request('start_date') }}">
                <input type="hidden" name="end_date" value="{{ request('end_date') }}">
                <input type="hidden" name="user_id" value="{{ request('user_id') }}">
                <input type="hidden" name="wilayah_id" value="{{ request('wilayah_id') }}">
                <input type="hidden" name="hasil_tipe" value="{{ request('hasil_tipe') }}">
                <input type="hidden" name="date_basis" value="{{ request('date_basis', $dateBasis ?? 'visit_date') }}">
                <button type="submit" class="btn btn-outline-success">
                    <i class="fas fa-download"></i> Download ZIP
                </button>
            </form>
        </div>
    </div>

    <!-- Photo List -->
    <div class="row">
        @forelse($photos as $photo)
            <div class="col-md-6 mb-3">
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <!-- Photos -->
                            <div class="col-md-6">
                                @if($photo->foto_checkin)
                                    <div class="mb-2">
                                        <small class="text-muted d-block">Check-in</small>
                                        <img src="{{ $photo->getFotoCheckinUrl() }}" alt="Check-in" class="img-fluid rounded" style="max-height: 150px;">
                                    </div>
                                @endif
                                @if($photo->foto_checkout)
                                    <div>
                                        <small class="text-muted d-block">Check-out</small>
                                        <img src="{{ $photo->getFotoCheckoutUrl() }}" alt="Check-out" class="img-fluid rounded" style="max-height: 150px;">
                                    </div>
                                @endif
                            </div>

                            <!-- Details -->
                            <div class="col-md-6">
                                @php
                                    $displayDate = $dateBasis === 'upload_date'
                                        ? $photo->created_at
                                        : ($photo->jadwalKunjungan->tanggal ?? $photo->created_at);
                                @endphp
                                <p>
                                    <strong>{{ $photo->klien->nama_klien }}</strong><br>
                                    <small class="text-muted">{{ $photo->klien->alamat }}</small>
                                </p>
                                <p>
                                    <strong>Sales:</strong> {{ $photo->jadwalKunjungan->user->name }}<br>
                                    <strong>{{ $dateBasis === 'upload_date' ? 'Tanggal Upload' : 'Tanggal Kunjungan' }}:</strong> {{ $displayDate->format('d M Y') }}<br>
                                    <strong>Hasil:</strong> 
                                    <span class="badge bg-info">{{ $photo->getHasilTipeLabel() }}</span>
                                </p>
                                @if($photo->nominal_transaksi)
                                    <p>
                                        <strong>Revenue:</strong> Rp {{ number_format($photo->nominal_transaksi, 0, ',', '.') }}
                                    </p>
                                @endif
                                <div>
                                    <a href="{{ route('admin.photo-gallery.lightbox', ['jadwalKlien' => $photo->id, 'date_basis' => request('date_basis', $dateBasis ?? 'visit_date')]) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-expand"></i> Lihat Detail
                                    </a>
                                    @if($photo->foto_checkin)
                                        <a href="{{ route('admin.photo-gallery.download', ['jadwalKlien' => $photo->id, 'type' => 'checkin', 'date_basis' => request('date_basis', $dateBasis ?? 'visit_date')]) }}" class="btn btn-sm btn-outline-secondary">
                                            <i class="fas fa-download"></i> DL Check-in
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
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
@endsection
