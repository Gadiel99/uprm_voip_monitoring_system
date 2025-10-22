<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPRM Monitoring System</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }
        /* Navbar */
        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
        }

        /* Sidebar */
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background-color: #f9fafb;
            border-right: 1px solid #dee2e6;
            padding-top: 1rem;
        }
        .sidebar .nav-link {
            color: #333;
            font-weight: 500;
            border-radius: 8px;
            margin: 3px 0;
        }
        .sidebar .nav-link.active {
            background-color: #d7f5df;
            color: #198754 !important;
            font-weight: 600;
        }

        /* Tabs superiores */
        .nav-tabs {
            border-bottom: 1px solid #dee2e6;
            background-color: #fff;
        }
        .nav-tabs .nav-link.active {
            border-bottom: 3px solid #00844b;
            color: #00844b !important;
            font-weight: 600;
        }
        .nav-tabs .nav-link:hover {
            color: #00844b;
        }

        main {
            background-color: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
        }
    </style>
</head>

<body>
    {{-- Navbar superior --}}
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <img src="{{ asset('images/uprm_logo.png') }}" alt="UPRM Logo" height="36" class="me-2">
                <span class="fw-semibold text-dark">UPRM Monitoring System</span>
            </a>
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-bell text-dark"></i>
                <i class="bi bi-moon text-dark"></i>
                <div class="dropdown">
                    <a class="d-flex align-items-center text-decoration-none text-dark dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle me-1"></i> Admin
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="#">Profile</a></li>
                        <li><a class="dropdown-item" href="#">Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="d-flex">
        {{-- Sidebar --}}
        <div class="sidebar p-3">
            <ul class="nav flex-column">
                <li class="nav-item mb-2">
                    {{-- Activo solo si estás en las rutas del Dashboard (Home, Alerts, Devices, Reports, Admin) --}}
                    <a href="{{ url('/') }}"
                       class="nav-link {{ request()->is('/') || request()->is('alerts') || request()->is('devices') || request()->is('reports') || request()->is('admin') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item mb-2">
                    <a href="{{ url('/settings') }}" class="nav-link {{ request()->is('settings') ? 'active' : '' }}">
                        <i class="bi bi-gear me-2"></i> Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ url('/help') }}" class="nav-link {{ request()->is('help') ? 'active' : '' }}">
                        <i class="bi bi-question-circle me-2"></i> Help
                    </a>
                </li>
            </ul>
        </div>

        {{-- Contenido principal --}}
        <div class="flex-grow-1">
            {{-- Tabs visibles en todas las rutas del Dashboard --}}
            @if (
                request()->is('/') ||
                request()->is('alerts') ||
                request()->is('devices') ||
                request()->is('reports') ||
                request()->is('admin')
            )
                <ul class="nav nav-tabs ps-3 pt-2">
                    <li class="nav-item">
                        <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/alerts') }}" class="nav-link {{ request()->is('alerts') ? 'active' : '' }}">Alerts</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/devices') }}" class="nav-link {{ request()->is('devices') ? 'active' : '' }}">Devices</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/reports') }}" class="nav-link {{ request()->is('reports') ? 'active' : '' }}">Reports</a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/admin') }}" class="nav-link {{ request()->is('admin') ? 'active' : '' }}">Admin</a>
                    </li>
                </ul>
            @endif

            <main class="m-4">
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
