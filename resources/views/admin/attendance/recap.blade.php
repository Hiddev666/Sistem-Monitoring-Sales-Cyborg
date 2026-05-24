@extends('layouts.app')

@section('title', 'Rekap Absensi')

@section('content')
<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-chart-line"></i> Rekap Absensi</h2>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label for="wilayah_id" class="form-label">Wilayah</label>
                    <select name="wilayah_id" id="wilayah_id" class="form-select">
                        <option value="">Semua Wilayah</option>
                        @foreach(\App\Models\Wilayah::all() as $w)
                            <option value="{{ $w->id }}">{{ $w->nama_wilayah }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label for="user_id" class="form-label">Karyawan</label>
                    <select name="user_id" id="user_id" class="form-select">
                        <option value="">Semua Karyawan</option>
                        @foreach(\App\Models\User::active()->orderBy('name')->get() as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="tanggal_mulai" class="form-label">Dari Tanggal</label>
                    <input type="date" name="tanggal_mulai" id="tanggal_mulai" class="form-control"
                           value="{{ now()->subDays(7)->toDateString() }}">
                </div>

                <div class="col-md-2">
                    <label for="tanggal_akhir" class="form-label">Hingga Tanggal</label>
                    <input type="date" name="tanggal_akhir" id="tanggal_akhir" class="form-control"
                           value="{{ now()->toDateString() }}">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-primary w-100" id="applyFilterBtn">
                        <i class="fas fa-filter"></i> Terapkan Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DataTables -->
    <div class="card">
        <div class="table-responsive">
            <table id="attendanceTable" class="table table-hover mb-0 dt-responsive">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Wilayah</th>
                        <th>Masuk</th>
                        <th>Keluar</th>
                        <th>Durasi</th>
                        <th>Lokasi Masuk</th>
                        <th>Lokasi Keluar</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const table = $('#attendanceTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.attendance.data") }}',
            type: 'GET',
            data: function(d) {
                console.log(d)
                d.wilayah_id = $('#wilayah_id').val();
                d.user_id = $('#user_id').val();
                d.tanggal_mulai = $('#tanggal_mulai').val();
                d.tanggal_akhir = $('#tanggal_akhir').val();
            }
        },
        columns: [
            { data: 'tanggal', name: 'tanggal' },
            { data: 'name', name: 'name' },
            { data: 'wilayah', name: 'wilayah' },
            { data: 'waktu_masuk', name: 'waktu_masuk' },
            { data: 'waktu_keluar', name: 'waktu_keluar' },
            { data: 'durasi', name: 'durasi' },
            {
                data: null,
                name: 'lat_masuk',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return `<a href="https://maps.google.com/?q=${data.lat_masuk},${data.lng_masuk}"
                            target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-map-marker-alt"></i>
                            </a>`;
                }
            },
            {
                data: null,
                name: 'lat_keluar',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return data.lat_keluar ?
                        `<a href="https://maps.google.com/?q=${data.lat_keluar},${data.lng_keluar}"
                            target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-map-marker-alt"></i>
                            </a>` : '-';
                }
            }
        ],
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json'
        },
        responsive: true,
        pageLength: 25
    });

    $('#applyFilterBtn').click(function() {
        table.draw();
    });
});
</script>
@endpush

@endsection
