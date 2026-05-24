@extends('layouts.app')

@section('title', isset($klien) ? 'Edit Klien' : 'Tambah Klien')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.css" />
<style>
    #map {
        height: 400px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .map-controls {
        margin-bottom: 10px;
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-sm-6">
        <h1 class="h3">{{ isset($klien) ? 'Edit Klien' : 'Tambah Klien Baru' }}</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="{{ route('admin.klien.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

@include('components.alerts')

<div class="card">
    <div class="card-body">
        <form action="{{ isset($klien) ? route('admin.klien.update', $klien) : route('admin.klien.store') }}" method="POST">
            @csrf
            @if(isset($klien))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="nama_klien" class="form-label">Nama Klien <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('nama_klien') is-invalid @enderror" id="nama_klien" 
                           name="nama_klien" value="{{ old('nama_klien', $klien->nama_klien ?? '') }}" required>
                    @error('nama_klien')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="kategori" class="form-label">Kategori <span class="text-danger">*</span></label>
                    <select class="form-select @error('kategori') is-invalid @enderror" id="kategori" name="kategori" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($kategoris as $key => $value)
                            <option value="{{ $key }}" {{ old('kategori', $klien->kategori ?? '') == $key ? 'selected' : '' }}>
                                {{ $value }}
                            </option>
                        @endforeach
                    </select>
                    @error('kategori')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="mb-3">
                <label for="alamat" class="form-label">Alamat <span class="text-danger">*</span></label>
                <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" 
                          rows="2" required>{{ old('alamat', $klien->alamat ?? '') }}</textarea>
                @error('alamat')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="wilayah_id" class="form-label">Wilayah <span class="text-danger">*</span></label>
                    <select class="form-select @error('wilayah_id') is-invalid @enderror" id="wilayah_id" name="wilayah_id" required>
                        <option value="">-- Pilih Wilayah --</option>
                        @foreach($wilayahs as $wilayah)
                            <option value="{{ $wilayah->id }}" 
                                    {{ old('wilayah_id', $klien->wilayah_id ?? '') == $wilayah->id ? 'selected' : '' }}>
                                {{ $wilayah->nama_wilayah }}
                            </option>
                        @endforeach
                    </select>
                    @error('wilayah_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="contact_person" class="form-label">Kontak Person</label>
                    <input type="text" class="form-control" id="contact_person" name="contact_person" 
                           value="{{ old('contact_person', $klien->contact_person ?? '') }}">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">No. Telp</label>
                    <input type="tel" class="form-control" id="phone" name="phone" 
                           value="{{ old('phone', $klien->phone ?? '') }}" placeholder="081234567890">
                </div>

                <div class="col-md-6 mb-3">
                    <label for="is_active" class="form-label">Status</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', $klien->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>
            </div>

            {{-- GPS Input --}}
            <div class="card my-4">
                <div class="card-header bg-light">
                    <h5 class="mb-0">📍 Lokasi GPS</h5>
                </div>
                <div class="card-body">
                    <div class="map-controls">
                        <small class="text-muted">Klik pada peta untuk memilih lokasi, atau masukkan koordinat secara manual</small>
                    </div>
                    
                    <div id="map" class="mb-3"></div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="latitude" class="form-label">Latitude <span class="text-danger">*</span></label>
                            <input type="number" step="0.0000001" class="form-control @error('latitude') is-invalid @enderror" 
                                   id="latitude" name="latitude" value="{{ old('latitude', $klien->latitude ?? '-2.9760971') }}" required>
                            <small class="form-text text-muted">Range: -90 hingga 90</small>
                            @error('latitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="longitude" class="form-label">Longitude <span class="text-danger">*</span></label>
                            <input type="number" step="0.0000001" class="form-control @error('longitude') is-invalid @enderror" 
                                   id="longitude" name="longitude" value="{{ old('longitude', $klien->longitude ?? '104.7553750') }}" required>
                            <small class="form-text text-muted">Range: -180 hingga 180</small>
                            @error('longitude')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('admin.klien.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ isset($klien) ? 'Perbarui' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const map = L.map('map').setView([parseFloat(latInput.value), parseFloat(lngInput.value)], 13);
    let marker = null;

    // Add tile layer
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19,
    }).addTo(map);

    // Initialize marker
    function updateMarker() {
        const lat = parseFloat(latInput.value);
        const lng = parseFloat(lngInput.value);
        
        if (marker) {
            map.removeLayer(marker);
        }
        
        if (!isNaN(lat) && !isNaN(lng)) {
            marker = L.marker([lat, lng]).addTo(map);
            map.setView([lat, lng], 13);
        }
    }

    updateMarker();

    // Map click to update coordinates
    map.on('click', function(e) {
        latInput.value = e.latlng.lat.toFixed(7);
        lngInput.value = e.latlng.lng.toFixed(7);
        updateMarker();
    });

    // Input change to update marker
    latInput.addEventListener('change', updateMarker);
    lngInput.addEventListener('change', updateMarker);
});
</script>
@endpush
@endsection
