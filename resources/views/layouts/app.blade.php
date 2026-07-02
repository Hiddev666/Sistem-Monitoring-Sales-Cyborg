<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sales Force Monitor')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

    <!-- Yajra DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

    @stack('styles')

    <style>
        * {
            scroll-behavior: smooth;
        }

        body {
            background-color: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            position: fixed;
            width: 250px;
            left: 0;
            top: 0;
            color: white;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 1000;
            padding-top: 0;
        }

        .sidebar .brand {
            flex: 0 0 auto;
            background-color: rgba(0, 0, 0, 0.1);
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar .brand h5 {
            font-size: 1.1rem;
            margin: 0;
            font-weight: 600;
        }

        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 0.75rem 1.5rem;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            border-left-color: white;
        }

        .sidebar .nav-link.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border-left-color: white;
        }

        .sidebar .nav-link i {
            width: 20px;
            margin-right: 0.5rem;
        }

        .sidebar-section {
            margin-bottom: 0.5rem;
        }

        .sidebar-section-toggle {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            background: transparent;
            border: 0;
            color: rgba(255, 255, 255, 0.88);
            padding: 0.75rem 1.5rem;
            border-left: 3px solid transparent;
            transition: all 0.3s ease;
            text-align: left;
        }

        .sidebar-section-toggle:hover,
        .sidebar-section-toggle:focus {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            border-left-color: white;
            outline: none;
        }

        .sidebar-section-toggle.active {
            background-color: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            border-left-color: white;
        }

        .sidebar-section-title {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            min-width: 0;
        }

        .sidebar-section-title span {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-section-caret {
            transition: transform 0.2s ease;
            opacity: 0.9;
            flex: 0 0 auto;
        }

        .sidebar-section-toggle:not(.collapsed) .sidebar-section-caret {
            transform: rotate(180deg);
        }

        .sidebar-section-body {
            padding: 0.25rem 0 0.5rem;
        }

        .sidebar-section-body .nav-link {
            padding-left: 2.25rem;
        }

        .sidebar-nav {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            overscroll-behavior: contain;
            scrollbar-gutter: stable;
            padding-bottom: 1rem;
        }

        .main-content {
            margin-left: 250px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            background: white;
            border-bottom: 1px solid #e3e6f0;
            padding: 1rem 2rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .topbar-user .user-menu {
            position: relative;
        }

        .page-content {
            flex: 1;
            padding: 2rem;
        }

        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e3e6f0;
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .badge-role {
            font-size: 0.75rem;
            padding: 0.35rem 0.65rem;
            border-radius: 0.375rem;
        }

        .badge-role.sales {
            background-color: #ffc107;
            color: #000;
        }

        .badge-role.manager {
            background-color: #0dcaf0;
            color: #000;
        }

        .badge-role.admin {
            background-color: #0d6efd;
            color: #fff;
        }

        .alert {
            border-radius: 0.5rem;
        }

        .btn {
            border-radius: 0.375rem;
        }

        .form-control, .form-select {
            border-radius: 0.375rem;
        }

        @media (max-width: 768px) {
            .sidebar {
                margin-left: -250px;
            }

            .sidebar.show {
                margin-left: 0;
            }

            .main-content {
                margin-left: 0;
            }
        }
    </style>

    @yield('css')
</head>
<body>
    @php
        $authUser = auth()->user();
        $isSales = $authUser?->isSales();
        $isManager = $authUser?->isManager();
        $isAdmin = $authUser?->isAdmin();
        $canManageUsers = $authUser?->can('manage_users');
        $canManageConfig = $authUser?->can('manage_config');
        $canViewReports = $authUser?->can('view_reports');
        $canExportReports = $authUser?->can('export_reports');
        $canViewKunjungan = $authUser?->can('view_kunjungan');

        $dashboardRoute = $isSales ? 'sales.dashboard' : ($isManager ? 'manager.dashboard' : 'admin.dashboard');
        $analyticsDashboardRoute = $isManager ? 'manager.analytics.dashboard' : 'admin.analytics.dashboard';
        $salesPerformanceRoute = $isManager ? 'manager.analytics.sales-performance' : 'admin.analytics.sales-performance';
        $regionalPerformanceRoute = $isManager ? 'manager.analytics.regional-performance' : 'admin.analytics.regional-performance';
        $klienAnalysisRoute = $isManager ? 'manager.analytics.klien-analysis' : 'admin.analytics.klien-analysis';
        $roleName = $authUser?->roles->first()?->name ?? 'unknown';
        $managementActive = request()->routeIs('admin.users.*');
        $dataMasterActive = request()->routeIs('admin.klien.*', 'admin.wilayah.*');
        $monitoringActive = request()->routeIs('admin.monitoring.index', 'admin.analytics.*', 'manager.analytics.*', 'admin.photo-gallery.*');
        $scheduleAttendanceActive = request()->routeIs('admin.pjp.*', 'admin.attendance.*');
        $operationalActive = request()->routeIs('sales.pjp.*', 'sales.attendance.*');
        $configurationActive = request()->routeIs('admin.configuration.*');
        $accountActive = request()->routeIs('password.*');
    @endphp

    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <div class="brand">
            <h5>
                <i class="fas fa-map-marker-alt me-2"></i>Sales Force
            </h5>
            <small>Monitoring Kinerja</small>
        </div>

        <nav class="nav flex-column sidebar-nav">
            @auth
                <a class="nav-link @if(Route::is('*.dashboard')) active @endif" href="{{ route($dashboardRoute) }}">
                    <i class="fas fa-gauge-high"></i> Dashboard
                </a>

                @if($canManageUsers)
                    <div class="sidebar-section">
                        <button class="sidebar-section-toggle {{ $managementActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarManagement" aria-expanded="{{ $managementActive ? 'true' : 'false' }}" aria-controls="sidebarManagement">
                            <span class="sidebar-section-title">
                                <i class="fas fa-users"></i>
                                <span>Manajemen User</span>
                            </span>
                            <i class="fas fa-chevron-down sidebar-section-caret"></i>
                        </button>
                        <div class="collapse {{ $managementActive ? 'show' : '' }} sidebar-section-body" id="sidebarManagement">
                            <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="fas fa-users"></i> Pengguna
                            </a>
                        </div>
                    </div>
                @endif

                @if($isAdmin)
                    <div class="sidebar-section">
                        <button class="sidebar-section-toggle {{ $dataMasterActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarDataMaster" aria-expanded="{{ $dataMasterActive ? 'true' : 'false' }}" aria-controls="sidebarDataMaster">
                            <span class="sidebar-section-title">
                                <i class="fas fa-boxes-stacked"></i>
                                <span>Data Master</span>
                            </span>
                            <i class="fas fa-chevron-down sidebar-section-caret"></i>
                        </button>
                        <div class="collapse {{ $dataMasterActive ? 'show' : '' }} sidebar-section-body" id="sidebarDataMaster">
                            <a class="nav-link {{ request()->routeIs('admin.klien.*') ? 'active' : '' }}" href="{{ route('admin.klien.index') }}">
                                <i class="fas fa-building"></i> Klien/Toko
                            </a>
                            <a class="nav-link {{ request()->routeIs('admin.wilayah.*') ? 'active' : '' }}" href="{{ route('admin.wilayah.index') }}">
                                <i class="fas fa-map"></i> Wilayah
                            </a>
                        </div>
                    </div>

                    <div class="sidebar-section">
                        <button class="sidebar-section-toggle {{ $monitoringActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMonitoring" aria-expanded="{{ $monitoringActive ? 'true' : 'false' }}" aria-controls="sidebarMonitoring">
                            <span class="sidebar-section-title">
                                <i class="fas fa-chart-line"></i>
                                <span>Monitoring & Laporan</span>
                            </span>
                            <i class="fas fa-chevron-down sidebar-section-caret"></i>
                        </button>
                        <div class="collapse {{ $monitoringActive ? 'show' : '' }} sidebar-section-body" id="sidebarMonitoring">
                            <a class="nav-link {{ request()->routeIs('admin.monitoring.index') ? 'active' : '' }}" href="{{ route('admin.monitoring.index') }}">
                                <i class="fas fa-satellite-dish"></i> Monitoring Real-Time
                            </a>
                            @if($canViewReports)
                                <a class="nav-link {{ request()->routeIs('admin.analytics.dashboard', 'manager.analytics.dashboard') ? 'active' : '' }}" href="{{ route($analyticsDashboardRoute) }}">
                                    <i class="fas fa-chart-pie"></i> Ringkasan Analytics
                                </a>
                                <a class="nav-link {{ request()->routeIs('admin.analytics.sales-performance', 'manager.analytics.sales-performance') ? 'active' : '' }}" href="{{ route($salesPerformanceRoute) }}">
                                    <i class="fas fa-chart-bar"></i> Performa Sales
                                </a>
                                <a class="nav-link {{ request()->routeIs('admin.analytics.regional-performance', 'manager.analytics.regional-performance') ? 'active' : '' }}" href="{{ route($regionalPerformanceRoute) }}">
                                    <i class="fas fa-map-marked-alt"></i> Performa Regional
                                </a>
                                <a class="nav-link {{ request()->routeIs('admin.analytics.klien-analysis', 'manager.analytics.klien-analysis') ? 'active' : '' }}" href="{{ route($klienAnalysisRoute) }}">
                                    <i class="fas fa-users"></i> Analisis Klien
                                </a>
                            @endif
                            @if($canViewKunjungan)
                                <a class="nav-link {{ request()->routeIs('admin.photo-gallery.*') ? 'active' : '' }}" href="{{ route('admin.photo-gallery.index') }}">
                                    <i class="fas fa-images"></i> Galeri Kunjungan
                                </a>
                            @endif
                        </div>
                    </div>

                    @can('create_pjp')
                        <div class="sidebar-section">
                            <button class="sidebar-section-toggle {{ $scheduleAttendanceActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarSchedule" aria-expanded="{{ $scheduleAttendanceActive ? 'true' : 'false' }}" aria-controls="sidebarSchedule">
                                <span class="sidebar-section-title">
                                    <i class="fas fa-calendar-check"></i>
                                    <span>Penjadwalan & Absensi</span>
                                </span>
                                <i class="fas fa-chevron-down sidebar-section-caret"></i>
                            </button>
                            <div class="collapse {{ $scheduleAttendanceActive ? 'show' : '' }} sidebar-section-body" id="sidebarSchedule">
                                <a class="nav-link {{ request()->routeIs('admin.pjp.*') ? 'active' : '' }}" href="{{ route('admin.pjp.index') }}">
                                    <i class="fas fa-calendar-check"></i> PJP (Jadwal)
                                </a>
                                <a class="nav-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}" href="{{ route('admin.attendance.recap') }}">
                                    <i class="fas fa-clock"></i> Absensi
                                </a>
                            </div>
                        </div>
                    @endcan
                @endif

                @if($authUser?->isSales())
                    <div class="sidebar-section">
                        <button class="sidebar-section-toggle {{ $operationalActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarOperational" aria-expanded="{{ $operationalActive ? 'true' : 'false' }}" aria-controls="sidebarOperational">
                            <span class="sidebar-section-title">
                                <i class="fas fa-briefcase"></i>
                                <span>Operasional</span>
                            </span>
                            <i class="fas fa-chevron-down sidebar-section-caret"></i>
                        </button>
                        <div class="collapse {{ $operationalActive ? 'show' : '' }} sidebar-section-body" id="sidebarOperational">
                            <a class="nav-link {{ request()->routeIs('sales.pjp.*') ? 'active' : '' }}" href="{{ route('sales.pjp.today') }}">
                                <i class="fas fa-calendar-alt"></i> Jadwal Hari Ini
                            </a>
                            <a class="nav-link {{ request()->routeIs('sales.attendance.*') ? 'active' : '' }}" href="{{ route('sales.attendance.index') }}">
                                <i class="fas fa-clock"></i> Absensi
                            </a>
                        </div>
                    </div>
                @endif

                @if($canManageConfig)
                    <div class="sidebar-section">
                        <button class="sidebar-section-toggle {{ $configurationActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarConfiguration" aria-expanded="{{ $configurationActive ? 'true' : 'false' }}" aria-controls="sidebarConfiguration">
                            <span class="sidebar-section-title">
                                <i class="fas fa-cog"></i>
                                <span>Konfigurasi Sistem</span>
                            </span>
                            <i class="fas fa-chevron-down sidebar-section-caret"></i>
                        </button>
                        <div class="collapse {{ $configurationActive ? 'show' : '' }} sidebar-section-body" id="sidebarConfiguration">
                            <a class="nav-link {{ request()->routeIs('admin.configuration.*') ? 'active' : '' }}" href="{{ route('admin.configuration.index') }}">
                                <i class="fas fa-cog"></i> Konfigurasi Sistem
                            </a>
                        </div>
                    </div>
                @endif

                <div class="sidebar-section">
                    <button class="sidebar-section-toggle {{ $accountActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarAccount" aria-expanded="{{ $accountActive ? 'true' : 'false' }}" aria-controls="sidebarAccount">
                        <span class="sidebar-section-title">
                            <i class="fas fa-lock"></i>
                            <span>Akun</span>
                        </span>
                        <i class="fas fa-chevron-down sidebar-section-caret"></i>
                    </button>
                    <div class="collapse {{ $accountActive ? 'show' : '' }} sidebar-section-body" id="sidebarAccount">
                        <a class="nav-link {{ request()->routeIs('password.*') ? 'active' : '' }}" href="{{ route('password.change') }}">
                            <i class="fas fa-lock"></i> Ubah Password
                        </a>
                    </div>
                </div>
            @endauth
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Top Bar -->
        <div class="topbar">
            <div class="d-flex align-items-center">
                <button class="btn btn-light me-2" id="sidebar-toggle" style="display: none;">
                    <i class="fas fa-bars"></i>
                </button>
                <span class="navbar-brand mb-0 h5 me-3">Sales Force Monitor</span>
            </div>

            <div class="topbar-user">
                @auth
                    <div>
                        <small class="text-muted d-block">Logged in as</small>
                        <strong>{{ $authUser?->name }}</strong>
                    </div>
                    <span class="badge badge-role {{ $roleName }}">
                        {{ $authUser?->getRoleLabel() }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm" title="Logout">
                            <i class="fas fa-sign-out-alt me-1"></i>Logout
                        </button>
                    </form>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <a class="dropdown-item" href="{{ route('password.change') }}">
                                    <i class="fas fa-key me-2"></i>Ubah Password
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @endauth
            </div>
        </div>

        <!-- Page Content -->
        <div class="page-content">
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
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- jQuery (for DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>

    <script>
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('sidebar');

        if (window.innerWidth <= 768) {
            sidebarToggle.style.display = 'block';
        }

        window.addEventListener('resize', function() {
            if (window.innerWidth <= 768) {
                sidebarToggle.style.display = 'block';
            } else {
                sidebarToggle.style.display = 'none';
                sidebar.classList.remove('show');
            }
        });

        sidebarToggle?.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
    </script>

    @yield('js')
    @stack('scripts')
</body>
</html>
