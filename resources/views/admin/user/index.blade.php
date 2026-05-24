@extends('layouts.app')

@section('title', 'Manajemen User')

@section('content')
<div class="row mb-4">
    <div class="col-sm-6">
        <h1 class="h3 d-inline-block">Manajemen User</h1>
    </div>
    <div class="col-sm-6 text-end">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah User
        </a>
    </div>
</div>

@include('components.alerts')

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table id="usersTable" class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No. Telp</th>
                        <th>Wilayah</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Dibuat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#usersTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('admin.users.data') }}",
            type: 'GET'
        },
        columns: [
            { data: 'id', width: '5%' },
            { data: 'name' },
            { data: 'email' },
            { data: 'phone' },
            { data: 'wilayah' },
            { data: 'role' },
            { data: 'is_active', orderable: false },
            { data: 'created_at' },
            { data: 'actions', orderable: false, searchable: false, width: '10%' }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        }
    });
});
</script>
@endpush
@endsection
