@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2><i class="fas fa-chart-bar"></i> Statistik Galeri Foto</h2>
            <p class="text-muted">Analisis komprehensif koleksi foto kunjungan</p>
            <span class="badge bg-secondary">
                Basis tanggal: {{ $dateBasis === 'upload_date' ? 'Tanggal Upload' : 'Tanggal Kunjungan' }}
            </span>
        </div>
    </div>

    <!-- Key Metrics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total dengan Foto</h5>
                    <h2 class="text-primary"> {{ $totalWithPhotos }}</h2>
                    <small class="text-muted">Kunjungan dengan dokumentasi foto</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">dengan Tanda Tangan</h5>
                    <h2 class="text-success">{{ $totalWithSignature }}</h2>
                    <small class="text-muted">Kunjungan ditandatangani klien</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Dokumentasi Lengkap</h5>
                    <h2 class="text-info">{{ $completeDocumentation }}</h2>
                    <small class="text-muted">Check-in + Check-out + Signature</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center">
                <div class="card-body">
                    <h5 class="card-title text-muted">Tingkat Kelengkapan</h5>
                    <h2 class="text-warning">{{ $totalWithPhotos > 0 ? round(($completeDocumentation / $totalWithPhotos) * 100, 1) : 0 }}%</h2>
                    <small class="text-muted">Dokumentasi lengkap / Total</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Hasil Tipe Breakdown -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <strong><i class="fas fa-pie-chart"></i> Distribusi Hasil Kunjungan</strong>
                </div>
                <div class="card-body">
                    <div id="hasilChart" style="position: relative; height: 300px;">
                        <canvas id="hasilChartCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Photo by Sales Rep -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <strong><i class="fas fa-user-tie"></i> Foto per Sales Representative</strong>
                </div>
                <div class="card-body">
                    <div id="repChart" style="position: relative; height: 300px;">
                        <canvas id="repChartCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Detailed Statistics Table -->
    <div class="row">
        <div class="col-md-12 mb-4">
            <div class="card">
                <div class="card-header">
                    <strong><i class="fas fa-table"></i> Rincian per Hasil Kunjungan</strong>
                </div>
                <div class="card-body">
                    <table class="table table-striped table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Hasil Kunjungan</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">dengan Foto</th>
                                <th class="text-center">dengan Signature</th>
                                <th class="text-center">Lengkap</th>
                                <th class="text-center">% Lengkap</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($photoByHasilTipe as $hasilTipe => $data)
                                <tr>
                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ $data['label'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">{{ $data['total'] }}</td>
                                    <td class="text-center">{{ $data['with_photos'] }}</td>
                                    <td class="text-center">{{ $data['with_signature'] }}</td>
                                    <td class="text-center">{{ $data['complete'] }}</td>
                                    <td class="text-center">
                                        @if($data['total'] > 0)
                                            {{ round(($data['complete'] / $data['total']) * 100, 1) }}%
                                        @else
                                            0%
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Sales Reps Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <strong><i class="fas fa-crown"></i> Top Sales Representatives (Dokumentasi Foto)</strong>
                </div>
                <div class="card-body">
                    <table class="table table-striped">
                        <thead class="table-light">
                            <tr>
                                <th>Peringkat</th>
                                <th>Nama Sales</th>
                                <th class="text-center">Total Kunjungan</th>
                                <th class="text-center">dengan Foto</th>
                                <th class="text-center">dengan Signature</th>
                                <th class="text-center">Lengkap</th>
                                <th class="text-center">% Dokumentasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($photoByRep as $index => $rep)
                                <tr>
                                    <td>
                                        @if($index == 0)
                                            <i class="fas fa-crown text-warning"></i> {{ $index + 1 }}
                                        @elseif($index == 1)
                                            <i class="fas fa-medal text-secondary"></i> {{ $index + 1 }}
                                        @elseif($index == 2)
                                            <i class="fas fa-medal text-danger"></i> {{ $index + 1 }}
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </td>
                                    <td>{{ $rep['name'] }}</td>
                                    <td class="text-center">{{ $rep['total_schedules'] }}</td>
                                    <td class="text-center">{{ $rep['with_photos'] }}</td>
                                    <td class="text-center">{{ $rep['with_signature'] }}</td>
                                    <td class="text-center">{{ $rep['complete'] }}</td>
                                    <td class="text-center">
                                        @if($rep['total_schedules'] > 0)
                                            {{ round(($rep['with_photos'] / $rep['total_schedules']) * 100, 1) }}%
                                        @else
                                            0%
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<link href="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Hasil Tipe Chart
    const hasilCtx = document.getElementById('hasilChartCanvas').getContext('2d');
    new Chart(hasilCtx, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($photoByHasilTipe->pluck('label')->values()) !!},
            datasets: [{
                data: {!! json_encode($photoByHasilTipe->pluck('with_photos')->values()) !!},
                backgroundColor: [
                    '#4472C4', '#70AD47', '#FFC000', '#FF6B6B', '#4ECDC4', '#95A5A6'
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Sales Rep Chart
    const repCtx = document.getElementById('repChartCanvas').getContext('2d');
    new Chart(repCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($photoByRep->pluck('name')->values()) !!},
            datasets: [{
                label: 'Foto',
                data: {!! json_encode($photoByRep->pluck('with_photos')->values()) !!},
                backgroundColor: '#4472C4',
                borderColor: '#2F5496',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>
@endsection
