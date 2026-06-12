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
                <div id="map-empty-state" class="p-4 text-center text-muted d-none">
                    Belum ada lokasi sales yang aktif hari ini.
                </div>
            </div>
        </div>
    </div>
</div>

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

@push('scripts')
<script>
    const salesLocationsUrl = '{{ route('api.dashboard.sales-locations', [], false) }}';

    const map = L.map('map').setView([-2.9796, 104.7557], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    }).addTo(map);

    let markerGroup = L.featureGroup().addTo(map);

    function updateMap() {
        fetch(salesLocationsUrl, {
            headers: {
                'Accept': 'application/json'
            },
            credentials: 'same-origin'
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Gagal memuat lokasi sales');
                }

                return response.json();
            })
            .then(data => {
                setTextContent('active-sales-count', data.activeSales);
                setTextContent('total-visits-count', data.totalVisits);
                setTextContent('completed-visits-count', data.completedVisits);
                setTextContent('alert-count', data.notMoving);

                updateAlerts(data.sales);
                markerGroup.clearLayers();

                const emptyState = document.getElementById('map-empty-state');
                if (emptyState) {
                    emptyState.classList.toggle('d-none', data.sales.length > 0);
                }

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
                        .bindPopup(`
                            <strong>${sales.name}</strong><br>
                            Status: ${sales.status}<br>
                            Akurasi: ${sales.accuracy ? sales.accuracy + 'm' : '-'}<br>
                            Kunjungan hari ini: ${sales.visitCount}<br>
                            Selesai: ${sales.completedCount}<br>
                            Update: ${sales.lastUpdate}
                        `);

                    markerGroup.addLayer(marker);
                });

                if (data.sales.length > 0) {
                    map.fitBounds(markerGroup.getBounds().pad(0.1));
                }
            })
            .catch(error => {
                console.error('Error fetching sales locations:', error);
                const alertsContainer = document.getElementById('alerts-container');
                if (alertsContainer) {
                    alertsContainer.innerHTML = `
                        <div class="alert alert-danger mb-0">
                            ${error.message}
                        </div>
                    `;
                }
            });
    }

    function setTextContent(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
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
        if (!alertsContainer) {
            return;
        }

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

    updateMap();
    setInterval(updateMap, 30000);
</script>
@endpush
