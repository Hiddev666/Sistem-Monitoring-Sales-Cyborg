@extends('layouts.app')

@section('title', 'Manajemen Wilayah')

@section('content')
<div class="row mb-4">
    <div class="col-sm-6">
        <h1 class="h3 d-inline-block">Manajemen Wilayah</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="{{ route('admin.wilayah.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Wilayah
        </a>
    </div>
</div>

@include('components.alerts')

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped table-hover mb-0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama Wilayah</th>
                    <th>Keterangan</th>
                    <th>User</th>
                    <th>Klien</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($wilayahs as $wilayah)
                    <tr>
                        <td>{{ $wilayah->id }}</td>
                        <td><strong>{{ $wilayah->nama_wilayah }}</strong></td>
                        <td>{{ $wilayah->keterangan ?? '-' }}</td>
                        <td><span class="badge bg-info">{{ $wilayah->users_count }}</span></td>
                        <td><span class="badge bg-warning">{{ $wilayah->klien_count }}</span></td>
                        <td>
                            <a href="{{ route('admin.wilayah.edit', $wilayah) }}" class="btn btn-sm btn-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.wilayah.destroy', $wilayah) }}" method="POST" class="d-inline" 
                                  onsubmit="return confirm('Yakin ingin menghapus wilayah ini?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" 
                                        {{ $wilayah->users_count > 0 || $wilayah->klien_count > 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">Tidak ada data wilayah</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($wilayahs->hasPages())
        <div class="card-body">
            {{ $wilayahs->links() }}
        </div>
    @endif
</div>
@endsection
