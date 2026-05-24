@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-chart-line"></i> Dashboard Analytics</h2>
            <p class="text-muted">Ringkasan performa penjualan dan kunjungan</p>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-3">
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
                        <div class="col-md-3 d-flex align-items-end">
                            <a href="{{ route('admin.analytics.dashboard') }}" class="btn btn-secondary w-100">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Jadwal</h6>
                    <h2 class="mb-0">{{ $stats['total_schedules'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h6 class="card-title">Kunjungan Selesai</h6>
                    <h2 class="mb-0">{{ $stats['completed_visits'] }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h6 class="card-title">Total Revenue</h6>
                    <h2 class="mb-0">Rp {{ number_format($stats['total_revenue'], 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card bg-warning text-dark">
                <div class="card-body">
                    <h6 class="card-title">Rata-rata Durasi</h6>
                    <h2 class="mb-0">{{ $stats['avg_visit_duration'] }} min</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row mb-4">
        <!-- Results Breakdown Chart -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-pie-chart"></i> Hasil Kunjungan</h5>
                </div>
                <div class="card-body">
                    <canvas id="resultsChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Daily Trend Chart -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-line-chart"></i> Trend Harian</h5>
                </div>
                <div class="card-body">
                    <canvas id="trendChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="row mb-4">
        <!-- Top Sales Reps -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-star"></i> Top Sales Representative</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Kunjungan</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topSalesReps as $rep)
                                    <tr>
                                        <td {{ $loop->first ? 'class=text-primary font-weight-bold' : '' }}>
                                            {{ $rep->name }}
                                            @if($loop->first)
                                                <i class="fas fa-crown text-warning ms-1"></i>
                                            @endif
                                        </td>
                                        <td>{{ $rep->visits }}</td>
                                        <td>Rp {{ number_format($rep->revenue, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Belum ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.analytics.sales-performance') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-arrow-right"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Klien -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-trophy"></i> Top Klien</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Klien</th>
                                    <th>Kunjungan</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topKlien as $klien)
                                    <tr>
                                        <td {{ $loop->first ? 'class=text-success font-weight-bold' : '' }}>
                                            {{ $klien->nama_klien }}
                                            @if($loop->first)
                                                <i class="fas fa-crown text-warning ms-1"></i>
                                            @endif
                                        </td>
                                        <td>{{ $klien->visits }}</td>
                                        <td>Rp {{ number_format($klien->revenue, 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">Belum ada data</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="text-center mt-3">
                        <a href="{{ route('admin.analytics.klien-analysis') }}" class="btn btn-sm btn-outline-success">
                            <i class="fas fa-arrow-right"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Revenue Distribution -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-dollar-sign"></i> Distribusi Revenue Per Sales</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueChart" style="max-height: 300px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Links -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-link"></i> Akses Cepat</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('admin.analytics.sales-performance') }}" class="btn btn-outline-primary me-2 mb-2">
                        <i class="fas fa-users"></i> Performa Sales
                    </a>
                    <a href="{{ route('admin.analytics.klien-analysis') }}" class="btn btn-outline-success me-2 mb-2">
                        <i class="fas fa-store"></i> Analisis Klien
                    </a>
                    <a href="{{ route('admin.analytics.regional-performance') }}" class="btn btn-outline-warning me-2 mb-2">
                        <i class="fas fa-map"></i> Performa Regional
                    </a>
                    <a href="{{ route('admin.photo-gallery.index') }}" class="btn btn-outline-info me-2 mb-2">
                        <i class="fas fa-images"></i> Galeri Foto
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
    // Results Chart
    const resultsCtx = document.getElementById('resultsChart').getContext('2d');
    const resultsData = {!! json_encode($resultsBreakdown) !!};
    
    new Chart(resultsCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(resultsData),
            datasets: [{
                data: Object.values(resultsData),
                backgroundColor: [
                    '#28a745', '#dc3545', '#ffc107', '#17a2b8', '#6c757d', '#fd7e14'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Trend Chart
    const trendCtx = document.getElementById('trendChart').getContext('2d');
    const trendData = {!! json_encode($dailyTrend) !!};
    
    new Chart(trendCtx, {
        type: 'line',
        data: {
            labels: trendData.map(d => d.date),
            datasets: [{
                label: 'Kunjungan',
                data: trendData.map(d => d.visits),
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                tension: 0.4,
                fill: true
            }, {
                label: 'Selesai',
                data: trendData.map(d => d.completed),
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart').getContext('2d');
    const revenueData = {!! json_encode($revenueByRep) !!};
    
    new Chart(revenueCtx, {
        type: 'bar',
        data: {
            labels: revenueData.map(d => d.name),
            datasets: [{
                label: 'Revenue',
                data: revenueData.map(d => d.revenue),
                backgroundColor: '#007bff'
            }]
        },
        options: {
            responsive: true,
            indexAxis: 'y',
            plugins: { legend: { display: false } }
        }
    });
</script>
@endsection
