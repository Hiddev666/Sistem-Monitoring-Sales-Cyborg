@extends('layouts.app')

@section('title', 'Manager Dashboard')

@section('content')
<div class="row mb-4">
    <div class="col-md-12">
        <h2>
            <i class="fas fa-gauge-high me-2"></i>Dashboard Manajer
        </h2>
        <p class="text-muted">Monitoring real-time aktivitas dan kinerja sales force Anda</p>
    </div>
</div>

<div class="row">
    <!-- Sales Aktif -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Sales Aktif</h6>
                        <h3 class="mb-0">{{ $activeSales }}</h3>
                    </div>
                    <div style="font-size: 2rem; color: #0d6efd; opacity: 0.2;">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Kunjungan Hari Ini -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Total Kunjungan</h6>
                        <h3 class="mb-0">{{ $totalVisits }}</h3>
                    </div>
                    <div style="font-size: 2rem; color: #198754; opacity: 0.2;">
                        <i class="fas fa-map-pin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kunjungan Selesai -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-info">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Selesai</h6>
                        <h3 class="mb-0">{{ $completedVisits }}</h3>
                    </div>
                    <div style="font-size: 2rem; color: #0dcaf0; opacity: 0.2;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert -->
    <div class="col-md-6 col-lg-3 mb-4">
        <div class="card border-left-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-uppercase text-muted mb-1">Alert</h6>
                        <h3 class="mb-0" id="alert-count">0</h3>
                    </div>
                    <div style="font-size: 2rem; color: #ffc107; opacity: 0.3;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-map me-2"></i>Peta Monitoring Real-Time
                </h5>
            </div>
            <div class="card-body p-0">
                <div id="map" style="height: 500px; width: 100%;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Alerts Section -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-bell me-2"></i>Notifikasi & Alert
                </h5>
            </div>
            <div class="card-body">
                <div id="alerts-container">
                    <p class="text-muted text-center">Memuat alert...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .border-left-primary {
        border-left: 4px solid #0d6efd !important;
    }

    .border-left-success {
        border-left: 4px solid #198754 !important;
    }

    .border-left-warning {
        border-left: 4px solid #ffc107 !important;
    }

    .border-left-info {
        border-left: 4px solid #0dcaf0 !important;
    }
</style>

@section('js')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    // Initialize map
    const map = L.map('map').setView([-2.9796, 104.7557], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap'
    }).addTo(map);

    // Define marker colors
    const markerColors = {
        'idle': 'gray',
        'active': 'yellow',
        'completed': 'green',
        'paused': 'orange'
    };

    // Store markers for updates
    const markers = {};

    // Fetch and display sales locations
    function updateMap() {
        fetch('/api/dashboard/sales-locations')
            .then(response => response.json())
            .then(data => {
                // Update alert count
                document.getElementById('alert-count').textContent = data.notMoving;

                // Update alerts panel
                updateAlerts(data.sales);

                // Clear existing markers
                map.eachLayer(layer => {
                    if (layer instanceof L.Marker) {
                        layer.remove();
                    }
                });

                // Add new markers
                data.sales.forEach(sales => {
                    const icon = L.divIcon({
                        className: 'custom-marker',
                        html: `<div style="
                            background-color: ${getMarkerColor(sales.status)};
                            width: 30px;
                            height: 30px;
                            border-radius: 50%;
                            border: 3px solid white;
                            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                        "></div>`,
                        iconSize: [30, 30],
                        iconAnchor: [15, 15],
                        popupAnchor: [0, -15]
                    });

                    const marker = L.marker([sales.latitude, sales.longitude], {icon})
                        .addTo(map)
                        .bindPopup(`
                            <strong>${sales.name}</strong><br>
                            Status: ${sales.status}<br>
                            Kunjungan hari ini: ${sales.visitCount}<br>
                            Selesai: ${sales.completedCount}<br>
                            Update: ${sales.lastUpdate}
                        `);

                    markers[sales.id] = marker;
                });

                // Fit bounds if there are markers
                if (data.sales.length > 0) {
                    const group = new L.featureGroup(Object.values(markers));
                    map.fitBounds(group.getBounds().pad(0.1));
                }
            })
            .catch(error => {
                console.error('Error fetching sales locations:', error);
            });
    }

    function getMarkerColor(status) {
        const colors = {
            'idle': '#6c757d',
            'active': '#ffc107',
            'completed': '#198754',
            'paused': '#fd7e14'
        };
        return colors[status] || '#6c757d';
    }

    function updateAlerts(salesData) {
        const alertsContainer = document.getElementById('alerts-container');
        const alerts = salesData.filter(sales => sales.noMovementMinutes > 60);

        if (alerts.length === 0) {
            alertsContainer.innerHTML = '<p class="text-muted text-center">Tidak ada alert saat ini</p>';
            return;
        }

        let alertsHtml = '';
        alerts.forEach(sales => {
            alertsHtml += `
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <strong>${sales.name}</strong> tidak bergerak selama ${sales.noMovementMinutes} menit
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            `;
        });

        alertsContainer.innerHTML = alertsHtml;
    }

    // Auto-update every 30 seconds
    updateMap();
    setInterval(updateMap, 30000);
</script>
@endsection
@endsection
