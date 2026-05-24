@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-chart-line"></i> Laporan Performa Penjualan</h2>
            <p class="text-muted">Analisis detail performa setiap sales representative</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Wilayah</label>
                            <select name="wilayah_id" class="form-select">
                                <option value="">-- Semua Wilayah --</option>
                                @foreach($wilayah as $item)
                                    <option value="{{ $item->id }}" {{ request('wilayah_id') == $item->id ? 'selected' : '' }}>
                                        {{ $item->nama_wilayah }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Export Button -->
    <div class="row mb-3">
        <div class="col-md-12">
            <a href="{{ route('admin.reports.export-sales-performance', request()->only(['start_date', 'end_date', 'wilayah_id'])) }}" class="btn btn-success">
                <i class="fas fa-download"></i> Download Excel
            </a>
            <a href="{{ route('admin.reports.export-sales-performance', array_merge(request()->only(['start_date', 'end_date', 'wilayah_id']), ['format' => 'pdf'])) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
        </div>
    </div>

    <!-- Performance Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <strong><i class="fas fa-table"></i> Tabel Performa Sales</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Sales</th>
                                    <th>Wilayah</th>
                                    <th class="text-center">Jadwal</th>
                                    <th class="text-center">Kunjungan</th>
                                    <th class="text-center">Selesai</th>
                                    <th class="text-center">Revenue</th>
                                    <th class="text-center">Rata-rata Durasi</th>
                                    <th class="text-center">Konversi (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salesReps as $index => $rep)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $rep['name'] }}</strong>
                                        </td>
                                        <td>
                                            @if(isset($rep['wilayah']))
                                                <span class="badge bg-info">{{ $rep['wilayah'] }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $rep['schedules'] }}</td>
                                        <td class="text-center">{{ $rep['visits'] }}</td>
                                        <td class="text-center">{{ $rep['completed_visits'] }}</td>
                                        <td class="text-center">
                                            <strong class="text-success">
                                                Rp {{ number_format($rep['revenue'], 0, ',', '.') }}
                                            </strong>
                                        </td>
                                        <td class="text-center">
                                            {{ number_format($rep['avg_duration'], 1) }} menit
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $conversionRate = $rep['visits'] > 0 ? ($rep['completed_visits'] / $rep['visits']) * 100 : 0;
                                                $badgeColor = $conversionRate >= 80 ? 'success' : ($conversionRate >= 60 ? 'warning' : 'danger');
                                            @endphp
                                            <span class="badge bg-{{ $badgeColor }}">
                                                {{ number_format($conversionRate, 1) }}%
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">
                                            <i class="fas fa-inbox"></i> Tidak ada data
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2"><strong>TOTAL</strong></th>
                                    <th></th>
                                    <th class="text-center">
                                        <strong>
                                            @php
                                                $totalSchedules = collect($salesReps)->sum('schedules');
                                            @endphp
                                            {{ $totalSchedules }}
                                        </strong>
                                    </th>
                                    <th class="text-center">
                                        <strong>
                                            @php
                                                $totalVisits = collect($salesReps)->sum('visits');
                                            @endphp
                                            {{ $totalVisits }}
                                        </strong>
                                    </th>
                                    <th class="text-center">
                                        <strong>
                                            @php
                                                $totalCompleted = collect($salesReps)->sum('completed_visits');
                                            @endphp
                                            {{ $totalCompleted }}
                                        </strong>
                                    </th>
                                    <th class="text-center">
                                        <strong class="text-success">
                                            @php
                                                $totalRevenue = collect($salesReps)->sum('revenue');
                                            @endphp
                                            Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                                        </strong>
                                    </th>
                                    <th class="text-center">
                                        <strong>
                                            @php
                                                $avgDurationAll = collect($salesReps)->avg('avg_duration');
                                            @endphp
                                            {{ number_format($avgDurationAll, 1) }} menit
                                        </strong>
                                    </th>
                                    <th class="text-center">
                                        <strong>
                                            @php
                                                $totalConversionRate = $totalVisits > 0 ? ($totalCompleted / $totalVisits) * 100 : 0;
                                            @endphp
                                            {{ number_format($totalConversionRate, 1) }}%
                                        </strong>
                                    </th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Penjualan</h5>
                    <h2 class="text-success">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Rata-rata Revenue/Rep</h5>
                    <h2 class="text-info">
                        @php
                            $avgRevenuePerRep = count($salesReps) > 0 ? $totalRevenue / count($salesReps) : 0;
                        @endphp
                        Rp {{ number_format($avgRevenuePerRep, 0, ',', '.') }}
                    </h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Sales Rep</h5>
                    <h2 class="text-primary">{{ count($salesReps) }}</h2>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
