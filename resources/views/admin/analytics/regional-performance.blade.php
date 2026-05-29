@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-map-marked-alt"></i> Performa Regional</h2>
            <p class="text-muted">Analisis performa penjualan per wilayah</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
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
            <a href="{{ route(auth()->user()?->isManager() ? 'manager.reports.export-regional-performance' : 'admin.reports.export-regional-performance', request()->only(['start_date', 'end_date'])) }}" class="btn btn-success">
                <i class="fas fa-download"></i> Download Excel
            </a>
            <a href="{{ route(auth()->user()?->isManager() ? 'manager.reports.export-regional-performance' : 'admin.reports.export-regional-performance', array_merge(request()->only(['start_date', 'end_date']), ['format' => 'pdf'])) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
        </div>
    </div>

    <!-- Regional Performance Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <strong><i class="fas fa-table"></i> Tabel Performa Regional</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Wilayah</th>
                                    <th class="text-center">Jumlah Sales</th>
                                    <th class="text-center">Total Kunjungan</th>
                                    <th class="text-center">Pembelian</th>
                                    <th class="text-center">Konversi (%)</th>
                                    <th class="text-center">Total Revenue</th>
                                    <th class="text-center">Avg Revenue/Rep</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($regions as $index => $region)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $region['nama_wilayah'] }}</strong>
                                        </td>
                                        <td class="text-center">{{ $region['sales_count'] }}</td>
                                        <td class="text-center">{{ $region['visits'] }}</td>
                                        <td class="text-center">{{ $region['purchases'] }}</td>
                                        <td class="text-center">
                                            @php
                                                $conversionRate = $region['visits'] > 0 ? ($region['purchases'] / $region['visits']) * 100 : 0;
                                                $badgeColor = $conversionRate >= 40 ? 'success' : ($conversionRate >= 20 ? 'warning' : 'danger');
                                            @endphp
                                            <span class="badge bg-{{ $badgeColor }}">
                                                {{ number_format($conversionRate, 1) }}%
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <strong class="text-success">
                                                Rp {{ number_format($region['revenue'], 0, ',', '.') }}
                                            </strong>
                                        </td>
                                        <td class="text-center">
                                            Rp {{ number_format($region['avg_revenue_per_rep'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted">
                                            <i class="fas fa-inbox"></i> Tidak ada data regional
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="2"><strong>TOTAL</strong></th>
                                    <th class="text-center">
                                        <strong>
                                            @php
                                                $totalSalesCount = collect($regions)->sum('sales_count');
                                            @endphp
                                            {{ $totalSalesCount }}
                                        </strong>
                                    </th>
                                    <th class="text-center">
                                        <strong>
                                            @php
                                                $totalVisits = collect($regions)->sum('visits');
                                            @endphp
                                            {{ $totalVisits }}
                                        </strong>
                                    </th>
                                    <th class="text-center">
                                        <strong>
                                            @php
                                                $totalPurchases = collect($regions)->sum('purchases');
                                            @endphp
                                            {{ $totalPurchases }}
                                        </strong>
                                    </th>
                                    <th class="text-center">
                                        <strong>
                                            @php
                                                $totalConversionRate = $totalVisits > 0 ? ($totalPurchases / $totalVisits) * 100 : 0;
                                            @endphp
                                            {{ number_format($totalConversionRate, 1) }}%
                                        </strong>
                                    </th>
                                    <th class="text-center">
                                        <strong class="text-success">
                                            @php
                                                $totalRevenue = collect($regions)->sum('revenue');
                                            @endphp
                                            Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                                        </strong>
                                    </th>
                                    <th class="text-center">
                                        <strong>
                                            @php
                                                $avgRevenuePerRepAll = $totalSalesCount > 0 ? $totalRevenue / $totalSalesCount : 0;
                                            @endphp
                                            Rp {{ number_format($avgRevenuePerRepAll, 0, ',', '.') }}
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

    <!-- Top Regions -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <strong><i class="fas fa-trophy"></i> Wilayah dengan Revenue Tertinggi</strong>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            @foreach(collect($regions)->sortByDesc('revenue')->take(5) as $region)
                                <tr>
                                    <td>
                                        <strong>{{ $region['nama_wilayah'] }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-success">
                                            Rp {{ number_format($region['revenue'], 0, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <strong><i class="fas fa-rocket"></i> Wilayah dengan Konversi Tertinggi</strong>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            @foreach(collect($regions)->map(function($r) { $r['conversion_rate'] = $r['visits'] > 0 ? ($r['purchases'] / $r['visits']) * 100 : 0; return $r; })->sortByDesc('conversion_rate')->take(5) as $region)
                                <tr>
                                    <td>
                                        <strong>{{ $region['nama_wilayah'] }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-info">
                                            {{ number_format($region['conversion_rate'], 1) }}%
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Wilayah</h5>
                    <h2 class="text-primary">{{ count($regions) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Revenue</h5>
                    <h2 class="text-success">Rp {{ number_format($totalRevenue ?? 0, 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Rata-rata Revenue/Wilayah</h5>
                    <h2 class="text-info">
                        @php
                            $avgRevenuePerRegion = count($regions) > 0 ? ($totalRevenue ?? 0) / count($regions) : 0;
                        @endphp
                        Rp {{ number_format($avgRevenuePerRegion, 0, ',', '.') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
