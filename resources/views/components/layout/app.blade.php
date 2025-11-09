{{--
/*
 * File: app.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Main application layout template providing the overall structure
 *              for the monitoring system interface including navigation, sidebar,
 *              and content areas.
 * 
 * Author: [Hector R. sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   This file serves as the master layout for the entire application. It includes:
 *   - Top navigation bar with user menu and notifications
 *   - Left sidebar with main navigation links
 *   - Dashboard tabs for different system sections
 *   - Account settings modal
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3 (CSS framework)
 *   - Bootstrap Icons 1.11.3
 *   - Laravel Blade templating engine
 * 
 * Usage:
 *   @extends('components.layout.app')
 *   @section('content')
 *       <!-- Page content here -->
 *   @endsection
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 829 documentation standards
 *   - Adheres to IEEE 1016 software design description
 */
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Layout principal Bootstrap para toda la app autenticada.
         Incluye estilos de navbar, sidebar y pestañas superiores (Home/Alerts/Devices/Reports/Admin). -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPRM Monitoring System</title>

    {{-- External CSS and JavaScript Libraries --}}
    {{-- Bootstrap 5.3.3 - Frontend CSS framework --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Bootstrap Icons 1.11.3 - Icon library --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /*
         * Section: Custom CSS Styles
         * Description: Application-wide styling definitions following UPRM branding guidelines
         * Color Scheme:
         *   - Primary Green: #00844b (UPRM institutional color)
         *   - Background: #f8f9fa (Light gray)
         *   - Text: #333 (Dark gray)
         */

        /* Global body styling */
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }

        /* 
         * Component: Top Navigation Bar
         * Purpose: Main navigation container at top of page
         */
        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #dee2e6;
        }

        /* 
         * Component: Left Sidebar
         * Purpose: Main navigation menu container
         * Dimensions: 240px width, 100vh height
         */
        .sidebar {
            width: 240px;
            min-height: 100vh;
            background-color: #f9fafb;
            border-right: 1px solid #dee2e6;
            padding-top: 1rem;
        }

        /* Sidebar navigation links - default state */
        .sidebar .nav-link {
            color: #333;
            font-weight: 500;
            border-radius: 8px;
            margin: 3px 0;
        }

        /* Sidebar navigation links - active state */
        .sidebar .nav-link.active {
            background-color: #d7f5df;
            color: #198754 !important;
            font-weight: 600;
        }

        /* 
         * Component: Dashboard Tab Navigation
         * Purpose: Secondary navigation for main dashboard sections
         */
        .nav-tabs {
            border-bottom: 1px solid #dee2e6;
            background-color: #fff;
        }

        /* Active tab indicator with UPRM green underline */
        .nav-tabs .nav-link.active {
            border-bottom: 3px solid #00844b;
            color: #00844b !important;
            font-weight: 600;
        }

        /* Tab hover effect */
        .nav-tabs .nav-link:hover {
            background-color: #f1f3f4;
            color: #00844b;
            transition: all 0.2s ease;
        }

        /* 
         * Component: Main Content Area
         * Purpose: Container for page-specific content
         */
        main {
            background-color: #fff;
            border-radius: 8px;
            padding: 1.5rem;
            box-shadow: 0 0 8px rgba(0, 0, 0, 0.05);
        }

        /* 
         * Component: Account Settings Modal Pills
         * Purpose: Tab navigation within account settings modal
         */
        .nav-pills .nav-link {
            color: #000;
            border-radius: 8px;
            margin: 0 3px;
            font-weight: 500;
        }

        /* Active pill tab with UPRM green background */
        .nav-pills .nav-link.active {
            background-color: #00844b !important;
            color: #fff !important;
        }

        /* Modal dialog styling */
        .modal-content {
            border-radius: 16px;
        }

        /* Dark button variant */
        .btn-dark {
            background-color: #0b0b0b;
            border: none;
        }

        /* Notification dropdown */
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


    </style>
</head>

<body>
    @php
        // Determine if authenticated user is admin or super_admin
        $roleRaw = Auth::user()->role ?? null;
        $normalizedRole = $roleRaw ? strtolower(str_replace('_','', $roleRaw)) : null;
        $isAdminRole = in_array($normalizedRole, ['admin','superadmin']);
    @endphp

    {{-- Navbar: branding, notifications, and user menu --}}
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid">

            {{-- University logo and name --}}
            <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                <img src="{{ asset('images/logo-uprm.png') }}" alt="UPRM Logo" height="36" class="me-2">
                <span class="fw-semibold text-dark">UPRM Monitoring System</span>
            </a>

            <div class="d-flex align-items-center gap-3">

                {{-- Notifications dropdown --}}
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

                {{-- User account dropdown --}}
                <div class="dropdown">
                    <a
                        class="d-flex align-items-center text-decoration-none text-dark dropdown-toggle"
                        href="#"
                        data-bs-toggle="dropdown"
                    >
                       <i class="bi bi-person-circle me-1"></i>
                        <span id="userNameDisplay">{{ Auth::user()->name }}</span>
                    </a>

                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">

                        <li class="dropdown-header fw-semibold px-3">My Account</li>

                        {{-- Open account settings modal --}}
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

                        <li><hr class="dropdown-divider"></li>

                        {{-- Logout action --}}
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

    <div class="d-flex">
        {{-- Sidebar: navegación lateral simple (Dashboard/Help) --}}
        <div class="sidebar p-3">
            <ul class="nav flex-column">

                {{-- Dashboard link --}}
                <li class="nav-item mb-2">
                    <a
                        href="{{ url('/') }}"
                        class="nav-link {{ request()->is('/') || request()->is('alerts') || request()->is('devices') || request()->is('reports') || request()->is('admin') || request()->is('admin/*') ? 'active' : '' }}"
                    >
                        <i class="bi bi-speedometer2 me-2"></i>
                        Dashboard
                    </a>
                </li>

                {{-- Help link --}}
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

        {{-- Main content area --}}
        <div class="flex-grow-1">

            {{-- Dashboard top tabs (conditional render) --}}
            @if (
                request()->is('/') ||
                request()->is('alerts') ||
                request()->is('devices') ||
                request()->is('reports') || request()->is('reports/*') ||
                request()->is('admin') ||
                request()->is('admin/*')
            )
                <ul class="nav nav-tabs ps-3 pt-2">
                    <li class="nav-item">
                        <a href="{{ url('/') }}" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
                            Home
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/alerts') }}" class="nav-link {{ request()->is('alerts') ? 'active' : '' }}">
                            Alerts
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/devices') }}" class="nav-link {{ request()->is('devices') ? 'active' : '' }}">
                            Devices
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ url('/reports') }}" class="nav-link {{ request()->is('reports') ? 'active' : '' }}">
                            Reports
                        </a>
                    </li>

                    {{-- Admin tab only for admins --}}
                    @if ($isAdminRole)
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

            {{-- Page content injection --}}
            <main class="m-4">
                @yield('content')
            </main>
        </div>
    </div>

    {{-- Modal de Configuración de Cuenta: pestañas Perfil/Usuario/Email/Password con formularios --}}
    <div class="modal fade" id="accountSettingsModal" tabindex="-1" aria-labelledby="accountSettingsLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">

                {{-- Modal header --}}
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-semibold" id="accountSettingsLabel">Account Settings</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                {{-- Modal body --}}
                <div class="modal-body">
                    @if (session('status'))
                        <div class="alert alert-success py-2 mb-3">
                            {{ ucfirst(str_replace('-', ' ', session('status'))) }}
                        </div>
                    @endif

                    {{-- Account modal tabs --}}
                    <ul class="nav nav-pills mb-4 justify-content-center" id="accountTab" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#usernameTab" type="button">
                                Name
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

                        {{-- Name tab --}}
                        <div class="tab-pane fade show active" id="usernameTab" role="tabpanel">
                            <form method="POST" action="{{ route('profile.username') }}">
                                @csrf
                                @method('patch')
                                <input type="hidden" name="return_to" value="{{ url()->current() }}#accountSettingsModal">
                                <input type="hidden" name="tab" value="username">

                                <label class="form-label fw-semibold">Current Name</label>
                                <input type="text" class="form-control mb-3" value="{{ Auth::user()->name }}" readonly>

                                <label class="form-label fw-semibold">New Name</label>
                                <input
                                    type="text"
                                    name="name"
                                    class="form-control mb-2"
                                    value="{{ old('name') }}"
                                    placeholder="Enter new name"
                                    minlength="2"
                                    pattern=".*\S.*"
                                    title="Name cannot be blank or whitespace only"
                                    maxlength="255"
                                    required
                                >
                                @error('name') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                                <button class="btn btn-dark w-100" type="submit">Update Name</button>
                            </form>
                        </div>

                        {{-- Email tab --}}
                        <div class="tab-pane fade" id="emailTab" role="tabpanel">
                            <form method="POST" action="{{ route('profile.email') }}">
                                @csrf
                                @method('patch')
                                <input type="hidden" name="return_to" value="{{ url()->current() }}#accountSettingsModal">
                                <input type="hidden" name="tab" value="email">
                                
                                <label class="form-label fw-semibold">Current Email</label>
                                <input type="email" class="form-control mb-3" value="{{ Auth::user()->email }}" readonly>

                                <label class="form-label fw-semibold">New Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control mb-2"
                                    value="{{ old('email') }}"
                                    placeholder="Enter new email"
                                    autocomplete="email"
                                    required
                                >
                                @error('email') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                                <button class="btn btn-dark w-100" type="submit">Update Email</button>
                            </form>
                        </div>

                        {{-- Password tab --}}
                        <div class="tab-pane fade" id="passwordTab" role="tabpanel">
                            <form method="POST" action="{{ route('profile.password') }}">
                                @csrf
                                @method('patch')
                                <input type="hidden" name="return_to" value="{{ url()->current() }}#accountSettingsModal">
                                <input type="hidden" name="tab" value="password"><!-- NEW -->

                                <label class="form-label fw-semibold">Current Password</label>
                                <input type="password" id="acc_current_password" name="current_password" class="form-control mb-2" placeholder="Enter current password" autocomplete="current-password" required>
                                @error('current_password') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                                <label class="form-label fw-semibold">New Password</label>
                                <input
                                    type="password"
                                    id="acc_new_password"
                                    name="password"
                                    class="form-control mb-2"
                                    placeholder="Example: CampusNet#24!"
                                    minlength="8"
                                    maxlength="64"
                                    pattern=".{8,64}"
                                    title="Use 8-64 characters with upper- and lowercase letters, a number, and a symbol"
                                    autocomplete="new-password"
                                    required
                                >
                                @error('password') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                                <label class="form-label fw-semibold">Confirm New Password</label>
                                <input type="password" id="acc_new_password_confirmation" name="password_confirmation" class="form-control mb-2" placeholder="Confirm new password" autocomplete="new-password" required>
                                @error('password_confirmation') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                                <div class="text-muted small d-block mb-2">
                                    Password requirements:
                                    <ul class="small mb-0">
                                        <li>8–64 characters</li>
                                        <li>At least one uppercase and one lowercase letter</li>
                                        <li>At least one number</li>
                                        <li>At least one symbol (e.g., ! @ # $ %)</li>
                                        <li>Must be different from your current password</li>
                                    </ul>
                                </div>

                                <button class="btn btn-dark w-100" type="submit">Update Password</button>
                            </form>
                        </div>

                    </div>

                    <div class="text-end mt-4">
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- Removed demo JS that overwrote dynamic account data --}}
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        // Only auto-open the Account Settings modal when the server explicitly
        // requests a specific tab (e.g., after profile updates). Avoid opening
        // it for unrelated flash messages like 'Role updated' from Admin page.
        @if (session('account_tab'))
            const modalEl = document.getElementById('accountSettingsModal');
            if (modalEl) {
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
                const activeTab = @json(session('account_tab','username'));
                const trigger = document.querySelector(`[data-bs-target="#${activeTab}Tab"]`);
                if (trigger) {
                    const tab = new bootstrap.Tab(trigger);
                    tab.show();
                }
            }
        @endif

        // Password fields: show as text on focus, mask on blur
        function autoShowPassword(inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;
            input.addEventListener('focus', function() {
                input.type = 'text';
            });
            input.addEventListener('blur', function() {
                input.type = 'password';
            });
        }
        // Attach to all password fields in Account Settings modal
        function attachAutoShowAll() {
            autoShowPassword('acc_current_password');
            autoShowPassword('acc_new_password');
            autoShowPassword('acc_new_password_confirmation');
        }
        // Attach on page load and when modal is shown
        attachAutoShowAll();
        const modalEl2 = document.getElementById('accountSettingsModal');
        if (modalEl2) {
            modalEl2.addEventListener('shown.bs.modal', attachAutoShowAll);
        }
    });
    </script>

{{-- Critical device notifications --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const notifContent = document.getElementById('notif-content');
    const notifBadge = document.getElementById('notif-badge');

    // Function to check critical devices and update notifications
    function updateCriticalDeviceNotifications() {
        const notifications = [];
        
        // Get critical devices from localStorage (synced from admin panel)
        const criticalDevices = JSON.parse(localStorage.getItem('criticalDevices') || '[]');
        
        // Check each critical device for offline status
        criticalDevices.forEach(device => {
            if (device.status === 'Offline') {
                notifications.push({
                    title: device.owner || 'Critical Device',
                    desc: `Device ${device.ip} is offline`,
                    level: 'critical',
                    ip: device.ip,
                    mac: device.mac
                });
            }
        });

        // Populate notifications if any
        if (notifications.length > 0) {
            notifBadge.classList.remove('d-none');
            notifBadge.textContent = notifications.length;

            notifContent.innerHTML = notifications.map(n => `
                <li class="dropdown-item d-flex align-items-start gap-2 py-2">
                    <i class="bi bi-exclamation-octagon text-danger fs-5"></i>
                    <div>
                        <div class="fw-semibold text-danger">${n.title}</div>
                        <small class="text-muted">${n.desc}</small>
                    </div>
                </li>
            `).join('');
        } else {
            notifBadge.classList.add('d-none');
            notifContent.innerHTML = '<li class="text-center text-muted py-3">No new notifications</li>';
        }
    }

    // Initial update
    updateCriticalDeviceNotifications();

    // Update notifications every 15 seconds
    setInterval(updateCriticalDeviceNotifications, 15000);

    // Listen for storage changes (when admin updates critical devices)
    window.addEventListener('storage', (e) => {
        if (e.key === 'criticalDevices') {
            updateCriticalDeviceNotifications();
        }
    });
});
</script>

{{-- Global logging for navigation and important actions --}}
<script>
// Helper function to add log (same as in admin page)
if (typeof window.addLog === 'undefined') {
    window.addLog = function(type, message, user = 'Admin') {
        const logs = JSON.parse(localStorage.getItem('systemLogs') || '[]');
        const timestamp = new Date().toISOString().replace('T', ' ').substring(0, 19);
        
        logs.unshift({
            timestamp: timestamp,
            type: type,
            message: message,
            user: user,
            id: Date.now()
        });
        
        if (logs.length > 500) logs.pop();
        localStorage.setItem('systemLogs', JSON.stringify(logs));
    }
}

// Log page navigation
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop() || 'home';
    const pageNames = {
        '': 'Home',
        'alerts': 'Alerts',
        'devices': 'Devices',
        'reports': 'Reports',
        'admin': 'Admin',
        'settings': 'Settings',
        'help': 'Help'
    };
    
    const pageName = pageNames[currentPage] || currentPage;
    addLog('INFO', `Navigated to ${pageName} page`);
});

// Log logout
const logoutBtn = document.querySelector('form[action*="logout"] button');
if (logoutBtn) {
    logoutBtn.addEventListener('click', function() {
        addLog('INFO', 'User logged out from system');
    });
}
</script>
</body>
</html>
