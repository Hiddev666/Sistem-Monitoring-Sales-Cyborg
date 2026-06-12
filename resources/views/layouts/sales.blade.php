<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sales Force Monitor')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @stack('styles')

    <style>
        * {
            scroll-behavior: smooth;
        }

        html,
        body {
            min-height: 100%;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fb;
        }

        body {
            padding-bottom: 76px;
        }

        .sales-topbar {
            position: sticky;
            top: 0;
            z-index: 1030;
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            box-shadow: 0 1px 8px rgba(15, 23, 42, 0.06);
        }

        .sales-topbar-inner {
            min-height: 64px;
            padding: 0.75rem 1rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
        }

        .sales-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
        }

        .sales-brand-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            background: #2563eb;
            flex: 0 0 auto;
        }

        .sales-brand-title {
            margin: 0;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .sales-brand-subtitle {
            display: block;
            max-width: 48vw;
            color: #6b7280;
            font-size: 0.8rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sales-user-actions {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex: 0 0 auto;
        }

        .tracking-status {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            min-height: 38px;
            max-width: 260px;
            padding: 0.4rem 0.75rem;
            border-radius: 999px;
            border: 1px solid transparent;
            font-size: 0.78rem;
            line-height: 1.2;
            background: #f8fafc;
            color: #334155;
        }

        .tracking-status i {
            font-size: 0.9rem;
        }

        .tracking-status__text {
            display: inline-block;
            max-width: 180px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .tracking-status__meta {
            display: block;
            font-size: 0.68rem;
            opacity: 0.82;
            line-height: 1.1;
        }

        .tracking-status--active {
            background: #ecfdf5;
            color: #047857;
            border-color: #a7f3d0;
        }

        .tracking-status--inactive {
            background: #f8fafc;
            color: #475569;
            border-color: #cbd5e1;
        }

        .tracking-status--warning {
            background: #fffbeb;
            color: #b45309;
            border-color: #fde68a;
        }

        .tracking-status--error {
            background: #fef2f2;
            color: #b91c1c;
            border-color: #fecaca;
        }

        .sales-content {
            padding: 1rem;
            max-width: 1180px;
            margin: 0 auto;
        }

        .sales-content .container,
        .sales-content .container-fluid {
            padding-left: 0;
            padding-right: 0;
            margin-top: 0 !important;
        }

        .card {
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.05);
            margin-bottom: 1rem;
        }

        .card-header {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
        }

        .btn,
        .form-control,
        .form-select {
            border-radius: 0.375rem;
        }

        .btn {
            min-height: 42px;
        }

        input,
        select,
        textarea {
            font-size: 16px;
        }

        .sales-nav {
            position: fixed;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 1040;
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            background: #ffffff;
            border-top: 1px solid #e5e7eb;
            box-shadow: 0 -2px 12px rgba(15, 23, 42, 0.08);
        }

        .sales-nav-link {
            min-height: 64px;
            padding: 0.5rem 0.25rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            color: #64748b;
            text-decoration: none;
            font-size: 0.76rem;
            border-top: 3px solid transparent;
        }

        .sales-nav-link i {
            font-size: 1.15rem;
        }

        .sales-nav-link.active {
            color: #2563eb;
            border-top-color: #2563eb;
            background: #eff6ff;
        }

        .sales-nav-link:hover {
            color: #2563eb;
            background: #f8fafc;
        }

        .badge-role {
            border-radius: 0.375rem;
            background: #f59e0b;
            color: #111827;
            font-size: 0.75rem;
            padding: 0.35rem 0.55rem;
        }

        @media (min-width: 992px) {
            body {
                padding-bottom: 0;
                padding-left: 240px;
            }

            .sales-topbar {
                left: 240px;
            }

            .sales-topbar-inner {
                padding-left: 2rem;
                padding-right: 2rem;
            }

            .sales-content {
                padding: 2rem;
            }

            .sales-nav {
                top: 0;
                right: auto;
                bottom: 0;
                width: 240px;
                display: flex;
                flex-direction: column;
                align-items: stretch;
                padding: 1rem 0.75rem;
                border-top: 0;
                border-right: 1px solid #e5e7eb;
                box-shadow: 2px 0 12px rgba(15, 23, 42, 0.06);
            }

            .sales-nav::before {
                content: 'Sales Force';
                display: block;
                padding: 0.75rem 0.75rem 1.25rem;
                color: #111827;
                font-weight: 700;
                font-size: 1.05rem;
            }

            .sales-nav-link {
                min-height: 46px;
                flex-direction: row;
                justify-content: flex-start;
                gap: 0.75rem;
                padding: 0.65rem 0.75rem;
                border-top: 0;
                border-left: 3px solid transparent;
                border-radius: 0.375rem;
                font-size: 0.94rem;
            }

            .sales-nav-link.active {
                border-left-color: #2563eb;
            }

        .sales-brand-subtitle {
            max-width: none;
        }

            .tracking-status {
                max-width: 360px;
            }

            .tracking-status__text {
                max-width: 260px;
            }
        }
    </style>

    @yield('css')
</head>
<body>
    @auth
        <header class="sales-topbar">
            <div class="sales-topbar-inner">
                <div class="sales-brand">
                    <span class="sales-brand-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </span>
                    <div>
                        <h1 class="sales-brand-title">@yield('title', 'Sales Force Monitor')</h1>
                        <span class="sales-brand-subtitle">{{ auth()->user()->name }}</span>
                    </div>
                </div>

                <div class="sales-user-actions">
                    <div id="trackingStatus" class="tracking-status tracking-status--inactive" role="status" aria-live="polite">
                        <i class="fas fa-location-crosshairs"></i>
                        <div>
                            <span class="tracking-status__text">Tracking nonaktif</span>
                            <span class="tracking-status__meta">Menunggu check-in absensi</span>
                        </div>
                    </div>
                    <span class="badge-role d-none d-sm-inline-flex">{{ auth()->user()->getRoleLabel() }}</span>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Logout">
                            <i class="fas fa-sign-out-alt"></i>
                            <span class="d-none d-sm-inline ms-1">Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="sales-content">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <nav class="sales-nav">
            <a href="{{ route('sales.dashboard') }}" class="sales-nav-link {{ request()->routeIs('sales.dashboard') ? 'active' : '' }}">
                <i class="fas fa-house"></i>
                <span>Beranda</span>
            </a>
            <a href="{{ route('sales.pjp.today') }}" class="sales-nav-link {{ request()->routeIs('sales.pjp.*') ? 'active' : '' }}">
                <i class="fas fa-calendar-day"></i>
                <span>Jadwal</span>
            </a>
            <a href="{{ route('sales.attendance.index') }}" class="sales-nav-link {{ request()->routeIs('sales.attendance.*') ? 'active' : '' }}">
                <i class="fas fa-clock"></i>
                <span>Absensi</span>
            </a>
            <a href="{{ route('password.change') }}" class="sales-nav-link {{ request()->routeIs('password.*') ? 'active' : '' }}">
                <i class="fas fa-user"></i>
                <span>Akun</span>
            </a>
        </nav>
    @endauth

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            const now = Date.now();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    </script>

    @auth
        @if(auth()->user()->isSales())
            <script>
                (function() {
                    const locationUpdateUrl = @json(route('api.location.update', [], false));
                    const attendanceStatusUrl = @json(route('sales.attendance.status', [], false));
                    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    const csrfToken = csrfMeta ? csrfMeta.content : null;
                    const updateIntervalMs = 60000;
                    let trackingTimer = null;
                    let isSendingLocation = false;
                    let lastUpdateText = 'Belum ada update';
                    let currentTrackingState = 'inactive';

                    function updateTrackingStatus(state, text, meta) {
                        currentTrackingState = state;

                        const statusEl = document.getElementById('trackingStatus');
                        const textEl = statusEl?.querySelector('.tracking-status__text');
                        const metaEl = statusEl?.querySelector('.tracking-status__meta');

                        if (!statusEl || !textEl || !metaEl) {
                            return;
                        }

                        statusEl.className = `tracking-status tracking-status--${state}`;
                        textEl.textContent = text;
                        metaEl.textContent = meta || lastUpdateText;
                    }

                    function setLastUpdateText(text) {
                        lastUpdateText = text;

                        const statusEl = document.getElementById('trackingStatus');
                        const metaEl = statusEl?.querySelector('.tracking-status__meta');

                        if (metaEl) {
                            metaEl.textContent = text;
                        }
                    }

                    function formatTime(date = new Date()) {
                        return date.toLocaleTimeString('id-ID', {
                            hour: '2-digit',
                            minute: '2-digit'
                        });
                    }

                    function handleLocationError(error) {
                        if (!error) {
                            updateTrackingStatus('error', 'Tracking gagal', 'Gagal membaca GPS');
                            return;
                        }

                        if (error.code === 1) {
                            updateTrackingStatus('warning', 'GPS ditolak', 'Izinkan akses lokasi untuk tracking');
                            return;
                        }

                        if (error.code === 2) {
                            updateTrackingStatus('warning', 'GPS tidak tersedia', 'Perangkat belum memberikan lokasi');
                            return;
                        }

                        if (error.code === 3) {
                            updateTrackingStatus('warning', 'GPS timeout', 'Lokasi tidak didapat tepat waktu');
                            return;
                        }

                        updateTrackingStatus('error', 'Tracking gagal', error.message || 'Terjadi kesalahan GPS');
                    }

                    function shouldTrackLocation() {
                        return fetch(attendanceStatusUrl, {
                            headers: {
                                'Accept': 'application/json'
                            },
                            credentials: 'include'
                        })
                            .then(response => response.ok ? response.json() : null)
                            .then(data => {
                                if (!data) {
                                    updateTrackingStatus('error', 'Status tracking tidak terbaca', 'Coba muat ulang halaman');
                                    return false;
                                }

                                if (!data.checked_in) {
                                    updateTrackingStatus('inactive', 'Tracking nonaktif', 'Belum check-in absensi');
                                    return false;
                                }

                                if (data.checked_out) {
                                    updateTrackingStatus('inactive', 'Tracking nonaktif', 'Sudah checkout absensi');
                                    return false;
                                }

                                updateTrackingStatus('active', 'Tracking aktif', `Update terakhir: ${lastUpdateText}`);
                                return true;
                            })
                            .catch(() => {
                                updateTrackingStatus('error', 'Status tracking gagal dimuat', 'Periksa koneksi jaringan');
                                return false;
                            });
                    }

                    function sendCurrentLocation() {
                        if (isSendingLocation || !navigator.geolocation || !csrfToken) {
                            return;
                        }

                        isSendingLocation = true;
                        updateTrackingStatus('warning', 'Mengirim lokasi...', lastUpdateText);

                        navigator.geolocation.getCurrentPosition(
                            function(position) {
                                fetch(locationUpdateUrl, {
                                    method: 'POST',
                                    headers: {
                                        'Accept': 'application/json',
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': csrfToken
                                    },
                                    credentials: 'same-origin',
                                    body: JSON.stringify({
                                        latitude: position.coords.latitude,
                                        longitude: position.coords.longitude,
                                        accuracy: position.coords.accuracy
                                    })
                                })
                                    .then(async function(response) {
                                        const data = await response.json().catch(() => ({}));

                                        if (!response.ok || !data.success) {
                                            const message = data.message || 'Gagal mengirim lokasi';

                                            if (response.status === 400 && /tracking is only active after attendance check-in/i.test(message)) {
                                                updateTrackingStatus('inactive', 'Tracking nonaktif', 'Belum check-in absensi');
                                                return;
                                            }

                                            updateTrackingStatus('error', 'Lokasi gagal dikirim', message);
                                            setLastUpdateText(`Gagal ${formatTime()}`);
                                            return;
                                        }

                                        setLastUpdateText(`Update terakhir: ${formatTime()}`);
                                        updateTrackingStatus('active', 'Tracking aktif', lastUpdateText);
                                    })
                                    .catch(function() {
                                        setLastUpdateText(`Gagal ${formatTime()}`);
                                        updateTrackingStatus('error', 'Lokasi gagal dikirim', 'Periksa koneksi jaringan');
                                    })
                                    .finally(function() {
                                        isSendingLocation = false;
                                    });
                            },
                            function(error) {
                                handleLocationError(error);
                                isSendingLocation = false;
                            },
                            {
                                enableHighAccuracy: true,
                                timeout: 15000,
                                maximumAge: 30000
                            }
                        );
                    }

                    function refreshTrackingState() {
                        shouldTrackLocation().then(function(canTrack) {
                            if (!canTrack) {
                                if (trackingTimer) {
                                    clearInterval(trackingTimer);
                                    trackingTimer = null;
                                }
                                return;
                            }

                            sendCurrentLocation();

                            if (!trackingTimer) {
                                trackingTimer = setInterval(sendCurrentLocation, updateIntervalMs);
                            }
                        });
                    }

                    updateTrackingStatus('inactive', 'Tracking nonaktif', 'Memeriksa status absensi...');
                    document.addEventListener('DOMContentLoaded', refreshTrackingState);
                    document.addEventListener('visibilitychange', function() {
                        if (!document.hidden) {
                            refreshTrackingState();
                        }
                    });
                    window.addEventListener('focus', refreshTrackingState);
                })();
            </script>
        @endif
    @endauth

    @yield('js')
    @stack('scripts')
</body>
</html>
