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

        /* Top tabs */
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
            background-color: #f1f3f4;
            color: #00844b;
            transition: all 0.2s ease;
        }

        main {
            background-color: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
        }

        /* Account Settings Modal Styles */
        .nav-pills .nav-link {
            color: #000;
            border-radius: 8px;
            margin: 0 3px;
            font-weight: 500;
        }

        .nav-pills .nav-link.active {
            background-color: #00844b !important;
            color: #fff !important;
        }

        .modal-content {
            border-radius: 16px;
        }

        .btn-dark {
            background-color: #0b0b0b;
            border: none;
        }

        /* Notifications dropdown */
        .dropdown-menu {
            border-radius: 10px;
            border: none;
        }

        .dropdown-header {
            font-size: 0.9rem;
            color: #333;
        }

        #notif-content li {
            transition: background-color 0.2s ease;
        }

        #notif-content li:hover {
            background-color: #f5f6f7;
            border-radius: 6px;
        }

        /* User Preview Banner */
        .preview-banner {
            background-color: #e7f0ff;
            color: #004085;
            font-size: 0.9rem;
            border-bottom: 1px solid #b8daff;
            text-align: center;
            padding: 0.5rem 1rem;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body>
    @php
        $isUserPreview = session('user_preview', false);
    @endphp

    {{-- Navbar --}}
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid">

            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <img src="{{ asset('images/logo-uprm.png') }}" alt="UPRM Logo" height="36" class="me-2">
                <span class="fw-semibold text-dark">UPRM Monitoring System</span>
            </a>

            <div class="d-flex align-items-center gap-3">

                {{-- Notifications (frontend only) --}}
                <div class="dropdown">
                    <a href="#" class="text-dark position-relative" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-bell fs-5"></i>
                        <span
                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none"
                            id="notif-badge">
                            0
                        </span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm p-2" style="width: 320px;">
                        <li class="dropdown-header fw-semibold">Notifications</li>
                        <li class="dropdown-divider"></li>
                        <div id="notif-content">
                            <li class="text-center text-muted py-3">No new notifications</li>
                        </div>
                    </ul>
                </div>

                

                {{-- User Dropdown --}}
                <div class="dropdown">
                    <a
                        class="d-flex align-items-center text-decoration-none text-dark dropdown-toggle"
                        href="#"
                        data-bs-toggle="dropdown"
                    >
                        <i class="bi bi-person-circle me-1"></i>
                        Admin
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">

                        <li class="dropdown-header fw-semibold px-3">My Account</li>

                        <li>
                            <a
                                class="dropdown-item"
                                href="#"
                                data-bs-toggle="modal"
                                data-bs-target="#accountSettingsModal"
                            >
                                <i class="bi bi-gear me-2 text-secondary"></i>
                                Account Settings
                            </a>
                        </li>

                        {{-- Role switch --}}
                        @if ($isUserPreview)
                            <li>
                                <form action="{{ url('/exit-user-preview') }}" method="POST">
                                    @csrf
                                   <button type="submit" class="dropdown-item fw-semibold" style="color: #007bff !important;">
    <i class="bi bi-eye-slash me-2" style="color: #007bff !important;"></i>Exit User Preview
</button>


                                </form>
                            </li>
                        @else
                            <li>
                                <form action="{{ url('/enter-user-preview') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-eye me-2 text-secondary"></i>
                                        User Preview
                                    </button>
                                </form>
                            </li>
                        @endif

                        <li><hr class="dropdown-divider"></li>

                        {{-- Logout (submits POST /logout) --}}
                        <li>
                            <form id="logoutForm" action="{{ url('/logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>

                            <a
                                class="dropdown-item text-danger"
                                href="#"
                                onclick="event.preventDefault(); document.getElementById('logoutForm').submit();"
                            >
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Logout
                            </a>
                        </li>

                    </ul>
                </div>
            </div>
        </div>
    </nav>

    {{-- User Preview Banner --}}
    @if ($isUserPreview)
        <div class="preview-banner">
            👁 Viewing as <strong>User Role</strong> — Click <strong>"Exit User Preview"</strong> in the menu to return to Admin.
        </div>
    @endif

    <div class="d-flex">

        {{-- Sidebar --}}
        <div class="sidebar p-3">
            <ul class="nav flex-column">

                <li class="nav-item mb-2">
                    <a
                        href="{{ url('/') }}"
                        class="nav-link {{ request()->is('/') || request()->is('alerts') || request()->is('devices') || request()->is('reports') || request()->is('admin') ? 'active' : '' }}"
                    >
                        <i class="bi bi-speedometer2 me-2"></i>
                        Dashboard
                    </a>
                </li>

                @if (! $isUserPreview)
                    <li class="nav-item mb-2">
                        <a
                            href="{{ url('/settings') }}"
                            class="nav-link {{ request()->is('settings') ? 'active' : '' }}"
                        >
                            <i class="bi bi-gear me-2"></i>
                            Settings
                        </a>
                    </li>
                @endif

                <li class="nav-item">
                    <a
                        href="{{ url('/help') }}"
                        class="nav-link {{ request()->is('help') ? 'active' : '' }}"
                    >
                        <i class="bi bi-question-circle me-2"></i>
                        Help
                    </a>
                </li>

            </ul>
        </div>

        {{-- Main content --}}
        <div class="flex-grow-1">

            {{-- Dashboard tabs (only on dashboard routes) --}}
            @if (
                request()->is('/') ||
                request()->is('alerts') ||
                request()->is('devices') ||
                request()->is('reports') ||
                request()->is('admin')
            )
                <ul class="nav nav-tabs ps-3 pt-2">

                    <li class="nav-item">
                        <a
                            href="{{ url('/') }}"
                            class="nav-link {{ request()->is('/') ? 'active' : '' }}"
                        >
                            Home
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            href="{{ url('/alerts') }}"
                            class="nav-link {{ request()->is('alerts') ? 'active' : '' }}"
                        >
                            Alerts
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            href="{{ url('/devices') }}"
                            class="nav-link {{ request()->is('devices') ? 'active' : '' }}"
                        >
                            Devices
                        </a>
                    </li>

                    <li class="nav-item">
                        <a
                            href="{{ url('/reports') }}"
                            class="nav-link {{ request()->is('reports') ? 'active' : '' }}"
                        >
                            Reports
                        </a>
                    </li>

                    @if (! $isUserPreview)
                        <li class="nav-item">
                            <a
                                href="{{ url('/admin') }}"
                                class="nav-link {{ request()->is('admin') ? 'active' : '' }}"
                            >
                                Admin
                            </a>
                        </li>
                    @endif

                </ul>
            @endif

            <main class="m-4">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Account Settings Modal --}}
    <div
        class="modal fade"
        id="accountSettingsModal"
        tabindex="-1"
        aria-labelledby="accountSettingsLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold" id="accountSettingsLabel">Account Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    {{-- Modal Tabs --}}
                    <ul class="nav nav-pills mb-4 justify-content-center" id="accountTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profileTab" type="button">
                                Profile Picture
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#usernameTab" type="button">
                                Username
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#emailTab" type="button">
                                Email
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#passwordTab" type="button">
                                Password
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">

                        {{-- Profile Picture --}}
                        <div class="tab-pane fade show active text-center" id="profileTab" role="tabpanel">
                            <div class="mb-3">
                                <img
                                    src="{{ asset('images/default-avatar.png') }}"
                                    alt="User Avatar"
                                    class="rounded-circle border mb-3"
                                    width="120"
                                    height="120"
                                >
                                <h6 class="fw-semibold mb-0">Admin User</h6>
                                <small class="text-muted">admin@uprm.edu</small>
                            </div>

                            <button class="btn btn-dark mb-2">Upload New Picture</button>
                            <p class="text-muted small">Recommended: Square image, at least 400x400px</p>
                        </div>

                        {{-- Username --}}
                        <div class="tab-pane fade" id="usernameTab" role="tabpanel">
                            <label class="form-label fw-semibold">Current Username</label>
                            <input type="text" class="form-control mb-3" value="Admin User" readonly>

                            <label class="form-label fw-semibold">New Username</label>
                            <input type="text" class="form-control mb-3" placeholder="Enter new username">

                            <button class="btn btn-dark w-100">Update Username</button>
                        </div>

                        {{-- Email --}}
                        <div class="tab-pane fade" id="emailTab" role="tabpanel">
                            <label class="form-label fw-semibold">Current Email</label>
                            <input type="email" class="form-control mb-3" value="admin@uprm.edu" readonly>

                            <label class="form-label fw-semibold">New Email</label>
                            <input type="email" class="form-control mb-3" placeholder="Enter new email">

                            <button class="btn btn-dark w-100">Update Email</button>
                        </div>

                        {{-- Password --}}
                        <div class="tab-pane fade" id="passwordTab" role="tabpanel">
                            <label class="form-label fw-semibold">Current Password</label>
                            <input type="password" class="form-control mb-3" placeholder="Enter current password">

                            <label class="form-label fw-semibold">New Password</label>
                            <input type="password" class="form-control mb-3" placeholder="Enter new password">

                            <label class="form-label fw-semibold">Confirm New Password</label>
                            <input type="password" class="form-control mb-3" placeholder="Confirm new password">

                            <small class="text-muted d-block mb-2">Password must be at least 6 characters long</small>

                            <button class="btn btn-dark w-100">Update Password</button>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Simulated notifications --}}
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const notifContent = document.getElementById('notif-content');
            const notifBadge = document.getElementById('notif-badge');

            // Static demo data
            const notifications = [
                { title: 'Emergency Services', desc: 'Phone 787-555-0100 is offline', level: 'critical' },
                { title: 'Security Office', desc: 'Phone 787-555-0200 is offline', level: 'warning' }
            ];

            if (notifications.length > 0) {
                notifBadge.classList.remove('d-none');
                notifBadge.textContent = notifications.length;

                notifContent.innerHTML = notifications.map(n => `
                    <li class="dropdown-item d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-octagon text-danger fs-5"></i>
                        <div>
                            <div class="fw-semibold text-danger">${n.title}</div>
                            <small class="text-muted">${n.desc}</small>
                        </div>
                    </li>
                `).join('');
            }
        });
    </script>
</body>
</html>
