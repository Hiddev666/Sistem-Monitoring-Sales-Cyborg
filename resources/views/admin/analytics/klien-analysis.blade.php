@extends('layouts.app')

@section('content')
@php
    $isManager = auth()->user()?->isManager();
    $exportRoute = $isManager ? 'manager.reports.export-klien-analysis' : 'admin.reports.export-klien-analysis';
    $exportFilters = request()->only(['search', 'start_date', 'end_date']);
@endphp
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-users"></i> Analisis Klien</h2>
            <p class="text-muted">Analisis performa dan perilaku setiap klien</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Cari Klien</label>
                            <input type="text" name="search" class="form-control" placeholder="Nama klien" value="{{ $search }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Dari Tanggal</label>
                            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Sampai Tanggal</label>
                            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
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
            <a href="{{ route($exportRoute, $exportFilters) }}" class="btn btn-success">
                <i class="fas fa-download"></i> Download Excel
            </a>
            <a href="{{ route($exportRoute, array_merge($exportFilters, ['format' => 'pdf'])) }}" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
        </div>
    </div>

    <!-- Klien Analysis Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <strong><i class="fas fa-table"></i> Tabel Analisis Klien</strong>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>No</th>
                                    <th>Nama Klien</th>
                                    <th class="text-center">Kunjungan</th>
                                    <th class="text-center">Pembelian</th>
                                    <th class="text-center">Konversi (%)</th>
                                    <th class="text-center">Revenue</th>
                                    <th class="text-center">Rata-rata Transaksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($klienData as $index => $klien)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $klien['nama_klien'] }}</strong><br>
                                            <small class="text-muted">{{ $klien['alamat'] ?? 'N/A' }}</small>
                                        </td>
                                        <td class="text-center">{{ $klien['visits'] }}</td>
                                        <td class="text-center">{{ $klien['purchases'] }}</td>
                                        <td class="text-center">
                                            @php
                                                $conversionRate = $klien['visits'] > 0 ? ($klien['purchases'] / $klien['visits']) * 100 : 0;
                                                $badgeColor = $conversionRate >= 50 ? 'success' : ($conversionRate >= 25 ? 'warning' : 'danger');
                                            @endphp
                                            <span class="badge bg-{{ $badgeColor }}">
                                                {{ number_format($conversionRate, 1) }}%
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <strong class="text-success">
                                                Rp {{ number_format($klien['revenue'], 0, ',', '.') }}
                                            </strong>
                                        </td>
                                        <td class="text-center">
                                            Rp {{ number_format($klien['avg_transaction'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            <i class="fas fa-inbox"></i> Tidak ada data klien
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
                                                $totalVisits = collect($klienData)->sum('visits');
                                            @endphp
                                            {{ $totalVisits }}
                                        </strong>
                                    </th>
                                    <th class="text-center">
                                        <strong>
                                            @php
                                                $totalPurchases = collect($klienData)->sum('purchases');
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
                                                $totalRevenue = collect($klienData)->sum('revenue');
                                            @endphp
                                            Rp {{ number_format($totalRevenue, 0, ',', '.') }}
                                        </strong>
                                    </th>
                                    <th class="text-center">
                                        <strong>
                                            @php
                                                $totalTransactions = collect($klienData)->sum('purchases');
                                                $avgTransaction = $totalTransactions > 0 ? $totalRevenue / $totalTransactions : 0;
                                            @endphp
                                            Rp {{ number_format($avgTransaction, 0, ',', '.') }}
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

    <!-- Top Klien Insights -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <strong><i class="fas fa-star"></i> Top 5 Klien Paling Sering Dikunjungi</strong>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            @foreach(collect($klienData)->sortByDesc('visits')->take(5) as $klien)
                                <tr>
                                    <td>
                                        <strong>{{ $klien['nama_klien'] }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-primary">{{ $klien['visits'] }} kunjungan</span>
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
                <div class="card-header bg-warning text-dark">
                    <strong><i class="fas fa-money-bill"></i> Top 5 Klien Paling Menguntungkan</strong>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tbody>
                            @foreach(collect($klienData)->sortByDesc('revenue')->take(5) as $klien)
                                <tr>
                                    <td>
                                        <strong>{{ $klien['nama_klien'] }}</strong>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge bg-success">
                                            Rp {{ number_format($klien['revenue'], 0, ',', '.') }}
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
                    <h5 class="card-title text-muted">Total Klien</h5>
                    <h2 class="text-primary">{{ count($klienData) }}</h2>
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
                    <h5 class="card-title text-muted">Rata-rata Revenue/Klien</h5>
                    <h2 class="text-info">
                        @php
                            $avgRevenuePerKlien = count($klienData) > 0 ? ($totalRevenue ?? 0) / count($klienData) : 0;
                        @endphp
                        Rp {{ number_format($avgRevenuePerKlien, 0, ',', '.') }}
                    </h2>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
