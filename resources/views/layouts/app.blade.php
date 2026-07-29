<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'SINDORY - Indo Express Laundry System')</title>
    
    <!-- Google Fonts & Bootstrap 5 & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --sidebar-bg: #0f172a;
            --sidebar-hover: #1e293b;
            --sidebar-accent: #38bdf8;
            --topbar-height: 65px;
            --bg-canvas: #f8fafc;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-canvas);
            color: #334155;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        #sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: var(--sidebar-bg);
            color: #94a3b8;
            z-index: 1040;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.08);
            display: flex;
            flex-direction: column;
        }

        #sidebar.collapsed {
            margin-left: calc(-1 * var(--sidebar-width));
        }

        .sidebar-brand {
            height: var(--topbar-height);
            display: flex;
            align-items: center;
            padding: 0 1.25rem;
            background: rgba(255, 255, 255, 0.03);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .sidebar-brand a {
            color: #fff;
            font-weight: 800;
            font-size: 1.15rem;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            letter-spacing: -0.5px;
        }

        .sidebar-menu {
            padding: 1.25rem 0.85rem;
            flex: 1;
            overflow-y: auto;
        }

        .menu-header {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #64748b;
            padding: 0.75rem 0.85rem 0.35rem;
            margin-top: 0.5rem;
        }

        .nav-link-custom {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 0.7rem 0.9rem;
            color: #94a3b8;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            margin-bottom: 2px;
        }

        .nav-link-custom i {
            font-size: 1.15rem;
            transition: transform 0.2s ease;
        }

        .nav-link-custom:hover {
            color: #f8fafc;
            background-color: var(--sidebar-hover);
        }

        .nav-link-custom:hover i {
            transform: translateX(3px);
            color: var(--sidebar-accent);
        }

        .nav-link-custom.active {
            color: #ffffff;
            background: linear-gradient(135deg, #0284c7, #0369a1);
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.25);
        }

        .nav-link-custom.active i {
            color: #ffffff;
        }

        .sidebar-user {
            padding: 1rem;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(0, 0, 0, 0.15);
        }

        .user-card {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0.5rem;
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.04);
        }

        .avatar-circle {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: linear-gradient(135deg, #38bdf8, #818cf8);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }

        /* Content Area */
        #content-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
        }

        #content-wrapper.expanded {
            margin-left: 0;
        }

        /* Topbar */
        .topbar {
            height: var(--topbar-height);
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.75rem;
            position: sticky;
            top: 0;
            z-index: 1030;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        }

        .toggle-btn {
            background: transparent;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 6px 10px;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }

        .toggle-btn:hover {
            background: #f1f5f9;
            color: #0f172a;
        }

        .main-container {
            padding: 2rem 1.75rem;
            flex: 1;
        }

        /* Cards & Components */
        .card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.02);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card-interactive:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
        }

        .step-progress {
            display: flex;
            justify-content: space-between;
            position: relative;
            margin: 25px 0;
        }

        .step-progress::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 4px;
            background: #e2e8f0;
            z-index: 1;
            transform: translateY(-50%);
            border-radius: 4px;
        }

        .step-item {
            position: relative;
            z-index: 2;
            background: #ffffff;
            padding: 8px 16px;
            border-radius: 30px;
            border: 2px solid #cbd5e1;
            font-size: 0.85rem;
            font-weight: 600;
            color: #64748b;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
            transition: all 0.3s ease;
        }

        .step-item.active {
            border-color: #0284c7;
            background: #0284c7;
            color: #ffffff;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.3);
        }

        .step-item.completed {
            border-color: #10b981;
            background: #10b981;
            color: #ffffff;
        }

        .timer-box {
            background: #0f172a;
            color: #38bdf8;
            font-family: 'JetBrains Mono', monospace, sans-serif;
            font-size: 2rem;
            font-weight: 800;
            letter-spacing: 3px;
            padding: 12px 24px;
            border-radius: 12px;
            display: inline-block;
            box-shadow: 0 4px 14px rgba(15, 23, 42, 0.25);
            border: 1px solid rgba(56, 189, 248, 0.2);
        }

        @media (max-width: 991.98px) {
            #sidebar {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            #sidebar.active {
                margin-left: 0;
            }
            #content-wrapper {
                margin-left: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>

    @php
        $isAuthPage = request()->routeIs('login') || request()->routeIs('register');
    @endphp

    @if(!$isAuthPage)
        <!-- Sidebar Navigation (Sembunyi di Halaman Login & Register) -->
        <aside id="sidebar">
            <div class="sidebar-brand">
                <a href="{{ route('monitoring') }}" class="d-flex align-items-center gap-2">
                    <img src="{{ asset('images/logo.png') }}" alt="SINDORY Logo" height="38" class="rounded border bg-white p-1">
                    <div>
                        <span class="fw-extrabold text-white fs-5 d-block leading-none">SINDORY</span>
                        <small class="text-white-50 extra-small d-block" style="font-size: 0.65rem;">Indo Express Laundry</small>
                    </div>
                </a>
            </div>

            <div class="sidebar-menu">
                <div class="menu-header">Layanan & Monitoring</div>
                <a href="{{ route('monitoring') }}" class="nav-link-custom {{ request()->routeIs('monitoring') || request()->routeIs('pelanggan.monitoring') ? 'active' : '' }}">
                    <i class="bi bi-tv"></i>
                    <span>Monitoring Real-Time</span>
                </a>

                @auth
                    @if(auth()->user()->role === 'pelanggan')
                        <a href="{{ route('pelanggan.riwayat') }}" class="nav-link-custom {{ request()->routeIs('pelanggan.riwayat') ? 'active' : '' }}">
                            <i class="bi bi-clock-history"></i>
                            <span>Riwayat Pesanan</span>
                        </a>
                    @endif

                    @if(auth()->user()->role === 'kasir')
                        <div class="menu-header">Operasional Kasir</div>
                        <a href="{{ route('kasir.transaksi.index') }}" class="nav-link-custom {{ request()->routeIs('kasir.transaksi.index') || request()->routeIs('kasir.transaksi.show') ? 'active' : '' }}">
                            <i class="bi bi-receipt"></i>
                            <span>Daftar Transaksi</span>
                        </a>
                        <a href="{{ route('kasir.transaksi.create') }}" class="nav-link-custom {{ request()->routeIs('kasir.transaksi.create') ? 'active' : '' }}">
                            <i class="bi bi-plus-circle"></i>
                            <span>Input Pesanan Baru</span>
                        </a>
                        <a href="{{ route('kasir.pelanggan.index') }}" class="nav-link-custom {{ request()->routeIs('kasir.pelanggan.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i>
                            <span>Kelola Pelanggan</span>
                        </a>
                        <a href="{{ route('kasir.user.index') }}" class="nav-link-custom {{ request()->routeIs('kasir.user.*') ? 'active' : '' }}">
                            <i class="bi bi-person-gear"></i>
                            <span>Kelola User System</span>
                        </a>
                    @endif

                    @if(auth()->user()->role === 'pemilik')
                        <div class="menu-header">Eksekutif & Analitik</div>
                        <a href="{{ route('pemilik.dashboard') }}" class="nav-link-custom {{ request()->routeIs('pemilik.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard Analytics</span>
                        </a>
                        <a href="{{ route('pemilik.monitoring') }}" class="nav-link-custom {{ request()->routeIs('pemilik.monitoring') ? 'active' : '' }}">
                            <i class="bi bi-display"></i>
                            <span>Monitoring Transaksi</span>
                        </a>
                        <a href="{{ route('pemilik.laporan') }}" class="nav-link-custom {{ request()->routeIs('pemilik.laporan') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-bar-graph"></i>
                            <span>Laporan Laba/Omset</span>
                        </a>
                    @endif
                @endauth
            </div>

            @auth
                <div class="sidebar-user">
                    <div class="user-card mb-2">
                        <div class="avatar-circle">
                            {{ strtoupper(substr(auth()->user()->nama, 0, 1)) }}
                        </div>
                        <div class="overflow-hidden">
                            <div class="text-white font-semibold text-truncate small" style="max-width: 140px;">
                                {{ auth()->user()->nama }}
                            </div>
                            <span class="badge bg-info text-dark extra-small" style="font-size: 0.68rem;">
                                {{ strtoupper(auth()->user()->role) }}
                            </span>
                        </div>
                    </div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100 fw-semibold">
                            <i class="bi bi-box-arrow-right me-1"></i> Logout
                        </button>
                    </form>
                </div>
            @else
                <div class="sidebar-user">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-sm w-100 mb-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Login Sistem
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline-light btn-sm w-100 fw-semibold">
                        Daftar Pelanggan
                    </a>
                </div>
            @endauth
        </aside>
    @endif

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="{{ $isAuthPage ? 'expanded' : '' }}">
        <!-- Topbar Header -->
        <header class="topbar">
            <div class="d-flex align-items-center gap-3">
                @if(!$isAuthPage)
                    <button class="toggle-btn" id="sidebarToggle" title="Toggle Sidebar">
                        <i class="bi bi-list fs-5"></i>
                    </button>
                @else
                    <a href="{{ route('monitoring') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                        <img src="{{ asset('images/logo.png') }}" alt="SINDORY Logo" height="36" class="rounded border p-1">
                        <div>
                            <span class="fw-bold text-dark fs-5 leading-none d-block">SINDORY</span>
                            <small class="text-muted extra-small d-block" style="font-size: 0.65rem;">Indo Express Laundry</small>
                        </div>
                    </a>
                @endif
                <div class="d-none d-md-flex align-items-center text-muted small fw-semibold">
                    <i class="bi bi-calendar3 me-2"></i> {{ date('l, d F Y') }}
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('monitoring') }}" class="btn btn-sm btn-light border text-secondary">
                    <i class="bi bi-search me-1"></i> Lacak Nota
                </a>
                @auth
                    <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle fs-6"></i>
                            <span class="fw-semibold">{{ auth()->user()->nama }}</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                            <li><span class="dropdown-header">Akses: {{ strtoupper(auth()->user()->role) }}</span></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bi bi-box-arrow-right me-2"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    @if(!$isAuthPage)
                        <a href="{{ route('login') }}" class="btn btn-sm btn-primary fw-semibold">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Login
                        </a>
                    @endif
                @endauth
            </div>
        </header>

        <!-- Main Content -->
        <main class="main-container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </main>

        <footer class="bg-white border-top py-3 text-center text-muted mt-auto">
            <div class="container-fluid px-4">
                <small>&copy; {{ date('Y') }} SINDORY &bull; Indo Express Laundry System (Random Forest Regressor Engine)</small>
            </div>
        </footer>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const contentWrapper = document.getElementById('content-wrapper');
            const toggleBtn = document.getElementById('sidebarToggle');

            if (toggleBtn && sidebar && contentWrapper) {
                toggleBtn.addEventListener('click', function() {
                    if (window.innerWidth < 992) {
                        sidebar.classList.toggle('active');
                    } else {
                        sidebar.classList.toggle('collapsed');
                        contentWrapper.classList.toggle('expanded');
                    }
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
