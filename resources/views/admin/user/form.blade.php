@extends('layouts.app')

@section('title', isset($user) ? 'Edit User' : 'Tambah User')

@section('content')
@php
    $isSuperAdmin = auth()->user()?->isSuperAdmin();
    $isAdmin = auth()->user()?->isAdmin() && !$isSuperAdmin;
@endphp
<div class="row mb-4">
    <div class="col-sm-6">
        <h1 class="h3">{{ isset($user) ? 'Edit User' : 'Tambah User Baru' }}</h1>
        <div class="text-muted mt-1">
            @if($isSuperAdmin)
                Super Admin dapat menetapkan semua role, termasuk admin dan manager.
            @elseif($isAdmin)
                Admin hanya dapat membuat atau memperbarui user role sales.
            @else
                Role dikunci untuk user sales agar struktur akses tetap aman.
            @endif
        </div>
    </div>
    <div class="col-sm-6 text-end">
        <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

@include('components.alerts')

<div class="card">
    <div class="card-body">
        <form action="{{ isset($user) ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST">
            @csrf
            @if(isset($user))
                @method('PUT')
            @endif

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" 
                           value="{{ old('name', $user->name ?? '') }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" 
                           value="{{ old('email', $user->email ?? '') }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label">No. Telp</label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" 
                           value="{{ old('phone', $user->phone ?? '') }}" placeholder="081234567890">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="wilayah_id" class="form-label">Wilayah <span class="text-danger">*</span></label>
                    <select class="form-select @error('wilayah_id') is-invalid @enderror" id="wilayah_id" name="wilayah_id" required>
                        <option value="">-- Pilih Wilayah --</option>
                        @foreach($wilayahs as $wilayah)
                            <option value="{{ $wilayah->id }}" 
                                    {{ old('wilayah_id', $user->wilayah_id ?? '') == $wilayah->id ? 'selected' : '' }}>
                                {{ $wilayah->nama_wilayah }}
                            </option>
                        @endforeach
                    </select>
                    @error('wilayah_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    @if($isSuperAdmin)
                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                        <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                            <option value="">-- Pilih Role --</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" 
                                        {{ old('role', isset($user) && $user->roles->isNotEmpty() ? $user->roles->first()->id : '') == $role->id ? 'selected' : '' }}>
                                    {{ ucfirst($role->name) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted d-block mt-1">Super Admin dapat memilih role apa pun, termasuk admin, manager, dan sales.</small>
                        @error('role')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    @elseif($isAdmin)
                        <label class="form-label">Role Terbatas</label>
                        <div class="form-control bg-light">
                            Sales
                        </div>
                        <input type="hidden" name="role" value="{{ old('role', $defaultRoleId ?? ($user->roles->first()->id ?? '')) }}">
                        <small class="text-muted d-block mt-1">Admin hanya dapat membuat atau memperbarui user dengan role sales.</small>
                    @else
                        <label class="form-label">Role</label>
                        <div class="form-control bg-light">
                            Sales
                        </div>
                        <input type="hidden" name="role" value="{{ old('role', $defaultRoleId ?? ($user->roles->first()->id ?? '')) }}">
                        <small class="text-muted d-block mt-1">Role dikunci untuk user sales.</small>
                    @endif
                </div>

                <div class="col-md-6 mb-3">
                    <label for="is_active" class="form-label">Status</label>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" 
                               {{ old('is_active', isset($user) ? $user->is_active : false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_active">Aktif</label>
                    </div>
                </div>
            </div>

            @if(!isset($user))
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" 
                               required minlength="8">
                        <small class="form-text text-muted">Minimum 8 karakter</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                               id="password_confirmation" name="password_confirmation" required minlength="8">
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @else
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> Kosongkan field password jika tidak ingin mengubahnya
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label">Password Baru (Opsional)</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" 
                               minlength="8">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                               id="password_confirmation" name="password_confirmation" minlength="8">
                        @error('password_confirmation')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            @endif

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> {{ isset($user) ? 'Perbarui' : 'Simpan' }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
