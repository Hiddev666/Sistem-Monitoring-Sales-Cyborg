@extends('layouts.app')

@section('title', 'Manajemen Jadwal Kunjungan (PJP)')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-calendar-alt"></i> Jadwal Kunjungan (PJP)</h2>
        </div>
        <div class="col-md-4 text-end">
            @can('create_pjp')
                <a href="{{ route('admin.pjp.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Buat Jadwal Baru
                </a>
            @endcan
        </div>
    </div>

    @include('components.alerts')

    <!-- DataTables -->
    <div class="card">
        <div class="table-responsive">
            <table id="pjpTable" class="table table-hover mb-0 dt-responsive">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Sales</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Dibuat Oleh</th>
                        <th>Action</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#pjpTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.pjp.data") }}',
            type: 'GET'
        },
        columns: [
            { data: 'id', name: 'id', width: '60px' },
            { data: 'user_name', name: 'user_name' },
            { data: 'tanggal', name: 'tanggal' },
            { data: 'keterangan', name: 'keterangan' },
            { 
                data: 'status_badge', 
                name: 'status',
                orderable: false,
                searchable: false
            },
            {
                data: null,
                name: 'progress',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `<div class="progress" style="height: 20px;">
                        <div class="progress-bar" style="width: ${row.percentage}%; font-size: 11px;">
                            ${row.progress}
                        </div>
                    </div>`;
                }
            },
            { data: 'created_by', name: 'created_by' },
            {
                data: null,
                name: 'actions',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm" role="group">
                            <a href="/admin/pjp/${row.id}/edit" class="btn btn-outline-primary" 
                               title="Edit"><i class="fas fa-edit"></i></a>
                            <button class="btn btn-outline-danger delete-btn" data-id="${row.id}" 
                                    title="Hapus"><i class="fas fa-trash"></i></button>
                        </div>
                    `;
                }
            }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        responsive: true,
        pageLength: 10
    });

    // Delete handler
    $(document).on('click', '.delete-btn', function() {
        const id = $(this).data('id');
        if (confirm('Apakah Anda yakin ingin menghapus jadwal ini?')) {
                    $.ajax({
                        url: `/admin/pjp/${id}`,
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: '{{ csrf_token() }}'
                        },
                        success: function() {
                            alert('Jadwal berhasil dihapus');
                            $('#pjpTable').DataTable().ajax.reload();
                        },
                        error: function() {
                            alert('Terjadi kesalahan saat menghapus');
                        }
                    });
        }
    });
});
</script>
@endpush

@endsection
