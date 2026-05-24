@extends('layouts.app')

@section('title', isset($wilayah) ? 'Edit Wilayah' : 'Tambah Wilayah')

@section('content')
<div class="row mb-4">
    <div class="col-sm-6">
        <h1 class="h3">{{ isset($wilayah) ? 'Edit Wilayah' : 'Tambah Wilayah Baru' }}</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="{{ route('admin.wilayah.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

@include('components.alerts')

<div class="card">
    <div class="card-body">
        <form action="{{ isset($wilayah) ? route('admin.wilayah.update', $wilayah) : route('admin.wilayah.store') }}" method="POST">
            @csrf
            @if(isset($wilayah))
                @method('PUT')
            @endif

            <div class="mb-3">
                <label for="nama_wilayah" class="form-label">Nama Wilayah <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('nama_wilayah') is-invalid @enderror" id="nama_wilayah" 
                       name="nama_wilayah" value="{{ old('nama_wilayah', $wilayah->nama_wilayah ?? '') }}" required>
                @error('nama_wilayah')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="keterangan" class="form-label">Keterangan</label>
                <textarea class="form-control @error('keterangan') is-invalid @enderror" id="keterangan" 
                          name="keterangan" rows="3">{{ old('keterangan', $wilayah->keterangan ?? '') }}</textarea>
                @error('keterangan')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('admin.wilayah.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ isset($wilayah) ? 'Perbarui' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
