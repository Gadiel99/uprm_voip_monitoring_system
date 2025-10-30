<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>UPRM Monitoring System</title>

    {{-- Bootstrap 5 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap 5 JS Bundle (includes Popper) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* General page background */
        body {
            background-color: #f8f9fa;
        }

        /* Sidebar styling */
        .sidebar {
            width: 250px; /* fixed width */
            min-height: 100vh; /* full viewport height */
            background-color: #ffffff;
            border-right: 1px solid #dee2e6;
            padding-top: 1.5rem;
        }

        /* Active sidebar link */
        .sidebar .nav-link.active {
            background-color: #d1e7dd; /* light green */
            color: #0f5132 !important;
            border-radius: .375rem;
        }

        /* Map container styling */
        .map-container {
            background-color: #ffffff;
            border-radius: .5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
            padding: 1rem;
        }

        /* Navbar logo styling */
        .navbar-brand img {
            height: 40px;
            margin-right: 10px;
        }
    </style>
</head>

<body>
    {{-- Top navigation bar --}}
    <nav class="navbar navbar-expand-lg bg-white border-bottom">
        <div class="container-fluid">
            {{-- Brand logo and text --}}
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="{{ asset('images/uprm_logo.png') }}" alt="UPRM Logo">
                <span class="fw-semibold">UPRM Monitoring System</span>
            </a>

            {{-- Right side navbar icons and profile --}}
            <div class="d-flex align-items-center gap-3">
                <i class="bi bi-bell"></i> {{-- Notifications icon --}}
                <i class="bi bi-moon"></i> {{-- Theme toggle icon --}}
                <div class="dropdown">
                    <a class="d-flex align-items-center text-decoration-none dropdown-toggle" href="#" data-bs-toggle="dropdown">
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
        {{-- Sidebar navigation --}}
        <div class="sidebar d-flex flex-column p-3">
            <ul class="nav flex-column">
                <li class="nav-item mb-2">
                    {{-- Active dashboard link --}}
                    <a href="#" class="nav-link active d-flex align-items-center">
                        <i class="bi bi-speedometer2 me-2"></i> Dashboard
                    </a>
                </li>
                <li class="nav-item mb-2">
                    {{-- Settings link --}}
                    <a href="#" class="nav-link text-dark d-flex align-items-center">
                        <i class="bi bi-gear me-2"></i> Settings
                    </a>
                </li>
                <li class="nav-item">
                    {{-- Help link --}}
                    <a href="#" class="nav-link text-dark d-flex align-items-center">
                        <i class="bi bi-question-circle me-2"></i> Help
                    </a>
                </li>
            </ul>
        </div>

        {{-- Main content area --}}
        <main class="flex-grow-1 p-4">
            <h5 class="fw-semibold mb-3">UPRM Campus Map - System Status</h5>

            {{-- Map container --}}
            <div class="map-container">
                {{-- Map legend --}}
                <div class="mb-3">
                    <div class="border p-2 rounded d-inline-block">
                        <h6 class="fw-bold mb-1">Map Legend</h6>
                        <ul class="list-unstyled small mb-1">
                            <li><span class="text-success fw-bold">●</span> Normal</li>
                            <li><span class="text-warning fw-bold">●</span> Warning</li>
                            <li><span class="text-danger fw-bold">●</span> Critical</li>
                        </ul>
                        <small class="text-muted fst-italic">Click markers for details</small>
                    </div>
                </div>

                {{-- Campus map image --}}
                <img src="{{ asset('images/EDIFICIOS_DEL_RUM_1.png') }}" alt="UPRM Map" class="img-fluid rounded">

                {{-- Optional: overlay markers could be added here later --}}
            </div>
        </main>
    </div>
</body>
</html>
