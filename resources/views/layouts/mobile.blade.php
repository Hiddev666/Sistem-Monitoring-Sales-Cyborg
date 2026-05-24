<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>@yield('title', 'Sales Force Monitor')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * {
            scroll-behavior: smooth;
        }

        html, body {
            height: 100%;
            width: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            padding-bottom: 80px; /* Space for bottom nav */
        }

        .mobile-topbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }

        .mobile-topbar h4 {
            margin: 0;
            font-size: 1.25rem;
        }

        .mobile-topbar .user-info {
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .mobile-navbar {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: white;
            border-top: 1px solid #e3e6f0;
            display: flex;
            justify-content: space-around;
            z-index: 1040;
            box-shadow: 0 -1px 3px rgba(0, 0, 0, 0.1);
        }

        .mobile-navbar a {
            flex: 1;
            padding: 0.75rem 0.5rem;
            text-align: center;
            text-decoration: none;
            color: #6c757d;
            font-size: 0.75rem;
            border-top: 3px solid transparent;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.25rem;
            transition: all 0.3s ease;
        }

        .mobile-navbar a i {
            font-size: 1.25rem;
        }

        .mobile-navbar a.active {
            color: #667eea;
            background-color: #f5f5f5;
            border-top-color: #667eea;
        }

        .mobile-navbar a:hover {
            background-color: #f5f5f5;
        }

        .page-content {
            padding: 1.5rem 1rem;
        }

        .card {
            border: none;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1rem;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e3e6f0;
        }

        .btn {
            border-radius: 0.375rem;
            min-height: 44px; /* Touch-friendly minimum */
            min-width: 44px;
        }

        .form-control, .form-select {
            border-radius: 0.375rem;
            min-height: 44px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"],
        input[type="date"],
        input[type="number"],
        select,
        textarea {
            font-size: 16px; /* Prevent zoom on iOS */
        }

        .btn-floating {
            position: fixed;
            bottom: 100px;
            right: 1rem;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            z-index: 1035;
            font-size: 1.5rem;
            text-decoration: none;
            border: none;
        }

        .badge-role {
            font-size: 0.65rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
        }

        .badge-role.sales {
            background-color: #ffc107;
            color: #000;
        }

        .alert {
            border-radius: 0.5rem;
            margin-bottom: 1rem;
        }

        @media (orientation: landscape) {
            body {
                padding-bottom: 60px;
            }
        }
    </style>

    @yield('css')
</head>
<body>
    @auth
        <!-- Mobile Top Bar -->
        <div class="mobile-topbar d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-0">
                    <i class="fas fa-map-marker-alt me-2"></i>Sales Monitor
                </h4>
                <div class="user-info">
                    {{ auth()->user()->name }}
                    <span class="badge badge-role sales ms-2">{{ auth()->user()->getRoleLabel() }}</span>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-light btn-sm" title="Logout">
                    <i class="fas fa-sign-out-alt"></i>
                </button>
            </form>
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

        <!-- Mobile Bottom Navigation -->
        <div class="mobile-navbar">
            <a href="{{ route('sales.dashboard') }}" class="@if(Route::is('sales.dashboard')) active @endif">
                <i class="fas fa-house"></i>
                <span>Beranda</span>
            </a>
            <a href="{{ route('sales.pjp.today') }}" class="@if(Route::is('sales.pjp.*')) active @endif">
                <i class="fas fa-calendar"></i>
                <span>Jadwal</span>
            </a>
            <a href="{{ route('sales.attendance.index') }}" class="@if(Route::is('sales.attendance.*')) active @endif">
                <i class="fas fa-clock"></i>
                <span>Absensi</span>
            </a>
            <a href="{{ route('sales.pjp.today') }}">
                <i class="fas fa-history"></i>
                <span>Kunjungan</span>
            </a>
            <a href="{{ route('password.change') }}" class="@if(Route::is('password.*')) active @endif">
                <i class="fas fa-user"></i>
                <span>Akun</span>
            </a>
        </div>
    @endauth

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Prevent zoom on double tap
        let lastTouchEnd = 0;
        document.addEventListener('touchend', function(event) {
            let now = Date.now();
            if (now - lastTouchEnd <= 300) {
                event.preventDefault();
            }
            lastTouchEnd = now;
        }, false);
    </script>

    @yield('js')
</body>
</html>
