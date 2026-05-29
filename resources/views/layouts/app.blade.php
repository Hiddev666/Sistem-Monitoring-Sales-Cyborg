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
            min-height: 100vh;
            position: fixed;
            width: 250px;
            left: 0;
            top: 0;
            color: white;
            overflow-y: auto;
            z-index: 1000;
            padding-top: 0;
        }

        .sidebar .brand {
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

        .badge-role.super_admin {
            background-color: #dc3545;
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
    <!-- Sidebar Navigation -->
    <div class="sidebar" id="sidebar">
        <div class="brand">
            <h5>
                <i class="fas fa-map-marker-alt me-2"></i>Sales Force
            </h5>
            <small>Monitoring Kinerja</small>
        </div>

        <nav class="nav flex-column">
            @auth
                <!-- Dashboard -->
                <a class="nav-link @if(Route::is('*.dashboard')) active @endif" href="{{ auth()->user()->isSales() ? route('sales.dashboard') : (auth()->user()->isManager() ? route('manager.dashboard') : route('admin.dashboard')) }}">
                    <i class="fas fa-gauge-high"></i> Dashboard
                </a>

                @if(auth()->user()->isAdmin())
                    <!-- Master Data -->
                    <hr class="text-white-50">
                    <span class="nav-link text-uppercase" style="cursor: default; font-size: 0.75rem; color: rgba(255,255,255,0.5);">Data Master</span>
                    
                    <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                        <i class="fas fa-users"></i> Pengguna
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.klien.*') ? 'active' : '' }}" href="{{ route('admin.klien.index') }}">
                        <i class="fas fa-building"></i> Klien/Toko
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.wilayah.*') ? 'active' : '' }}" href="{{ route('admin.wilayah.index') }}">
                        <i class="fas fa-map"></i> Wilayah
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.configuration.*') ? 'active' : '' }}" href="{{ route('admin.configuration.index') }}">
                        <i class="fas fa-cog"></i> Konfigurasi
                    </a>
                    
                    <!-- Scheduling -->
                    <hr class="text-white-50">
                    <span class="nav-link text-uppercase" style="cursor: default; font-size: 0.75rem; color: rgba(255,255,255,0.5);">Penjadwalan & Absensi</span>
                    
                    <a class="nav-link {{ request()->routeIs('admin.pjp.*') ? 'active' : '' }}" href="{{ route('admin.pjp.index') }}">
                        <i class="fas fa-calendar-check"></i> PJP (Jadwal)
                    </a>
                    <a class="nav-link {{ request()->routeIs('admin.attendance.*') ? 'active' : '' }}" href="{{ route('admin.attendance.recap') }}">
                        <i class="fas fa-clock"></i> Absensi
                    </a>
                @endif

                @if(auth()->user()->isSales())
                    <!-- Sales Menu -->
                    <hr class="text-white-50">
                    <span class="nav-link text-uppercase" style="cursor: default; font-size: 0.75rem; color: rgba(255,255,255,0.5);">Operasional</span>
                    
                    <a class="nav-link {{ request()->routeIs('sales.pjp.*') ? 'active' : '' }}" href="{{ route('sales.pjp.today') }}">
                        <i class="fas fa-calendar-alt"></i> Jadwal Hari Ini
                    </a>
                    <a class="nav-link {{ request()->routeIs('sales.attendance.*') ? 'active' : '' }}" href="{{ route('sales.attendance.index') }}">
                        <i class="fas fa-clock"></i> Absensi
                    </a>
                @endif

                @if(auth()->user()->isManager())
                    <!-- Manager Menu -->
                    <hr class="text-white-50">
                    <span class="nav-link text-uppercase" style="cursor: default; font-size: 0.75rem; color: rgba(255,255,255,0.5);">Laporan</span>
                    
                    @php($analyticsDashboardRoute = auth()->user()->isManager() ? 'manager.analytics.dashboard' : 'admin.analytics.dashboard')
                    @php($salesPerformanceRoute = auth()->user()->isManager() ? 'manager.analytics.sales-performance' : 'admin.analytics.sales-performance')
                    @php($regionalPerformanceRoute = auth()->user()->isManager() ? 'manager.analytics.regional-performance' : 'admin.analytics.regional-performance')
                    @php($klienAnalysisRoute = auth()->user()->isManager() ? 'manager.analytics.klien-analysis' : 'admin.analytics.klien-analysis')

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

                <!-- Account -->
                <hr class="text-white-50">
                <span class="nav-link text-uppercase" style="cursor: default; font-size: 0.75rem; color: rgba(255,255,255,0.5);">Akun</span>
                
                <a class="nav-link" href="{{ route('password.change') }}">
                    <i class="fas fa-lock"></i> Ubah Password
                </a>
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
                        <strong>{{ auth()->user()->name }}</strong>
                    </div>
                    <span class="badge badge-role {{ auth()->user()->roles->first()->name ?? 'unknown' }}">
                        {{ auth()->user()->getRoleLabel() }}
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
