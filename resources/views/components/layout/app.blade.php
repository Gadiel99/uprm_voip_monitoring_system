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
    <title>UPRM VoIP Monitoring System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-uprm.png') }}">

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
            background-color: #00844b;
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
         * Component: Dashboard Tab Navigation
         * Purpose: Secondary navigation for main dashboard sections
         */
        .nav-tabs {
            border-bottom: 1px solid #dee2e6;
            background-color: rgba(255, 255, 255, 0.9);
        }

        /* Default tab color */
        .nav-tabs .nav-link {
            color: #333;
        }

        /* Active tab indicator with UPRM green underline */
        .nav-tabs .nav-link.active {
            border-bottom: 3px solid #00844b;
            color: #00844b !important;
            font-weight: 600;
        }

        /* Tab hover effect */
        .nav-tabs .nav-link:hover {
            background-color: rgba(0, 132, 75, 0.2);
            color: #00844b;
            font-weight: 600;
            transform: translateY(-2px);
            border-bottom: 3px solid #00844b !important;
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

        /* Nav pills hover effect */
        .nav-pills .nav-link:hover {
            background-color: rgba(0, 132, 75, 0.1);
            color: #00844b;
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

        /* Consistent extension tag styling across tables */
        .ext-badge {
            background-color: #f8f9fa; /* light */
            color: #212529;            /* dark */
            border: 1px solid #dee2e6; /* light border */
            border-radius: 10rem;      /* pill */
            font-weight: 500;
            font-size: 0.85rem;
            padding: 0.35em 0.6em;
            display: inline-block;
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
                <span class="fw-semibold text-dark">UPRM VoIP Monitoring System</span>
            </a>

            <div class="d-flex align-items-center gap-3">

                {{-- Help link --}}
                <a href="{{ route('help') }}" class="text-dark" title="Help">
                    <i class="bi bi-question-circle fs-5"></i>
                </a>

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
                                onclick="event.preventDefault(); logLogoutAndSubmit();"
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
                    @if (session('account_status'))
                        <div class="alert alert-success py-2 mb-3">
                            {{ ucfirst(str_replace('-', ' ', session('account_status'))) }}
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
                                <div class="input-group mb-2">
                                    <input type="password" id="acc_current_password" name="current_password" class="form-control" placeholder="Enter current password" autocomplete="current-password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('acc_current_password', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('current_password') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                                <label class="form-label fw-semibold">New Password</label>
                                <div class="input-group mb-2">
                                    <input
                                        type="password"
                                        id="acc_new_password"
                                        name="password"
                                        class="form-control"
                                        placeholder="Example: CampusNet#24!"
                                        minlength="8"
                                        maxlength="64"
                                        pattern=".{8,64}"
                                        title="Use 8-64 characters with upper- and lowercase letters, a number, and a symbol"
                                        autocomplete="new-password"
                                        required
                                    >
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('acc_new_password', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('password') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                                <label class="form-label fw-semibold">Confirm New Password</label>
                                <div class="input-group mb-2">
                                    <input type="password" id="acc_new_password_confirmation" name="password_confirmation" class="form-control" placeholder="Confirm new password" autocomplete="new-password" required>
                                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('acc_new_password_confirmation', this)">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </div>
                                @error('password_confirmation') <div class="text-danger small mb-2">{{ $message }}</div> @enderror

                                <div class="text-muted small d-block mb-2">
                                    Password requirements:
                                    <ul class="small mb-0" id="accPasswordReqs" style="list-style: none; padding-left: 0;">
                                        <li id="acc-req-length"><i class="bi bi-circle"></i> 8–64 characters</li>
                                        <li id="acc-req-case"><i class="bi bi-circle"></i> At least one uppercase and one lowercase letter</li>
                                        <li id="acc-req-number"><i class="bi bi-circle"></i> At least one number</li>
                                        <li id="acc-req-symbol"><i class="bi bi-circle"></i> At least one symbol (e.g., ! @ # $ %)</li>
                                    </ul>
                                    <p class="small text-muted mb-0 mt-1"><i class="bi bi-lightbulb"></i> <em>Suggestion: Use a different password from your current one</em></p>
                                </div>

                                <button class="btn btn-dark w-100" type="submit">Update Password</button>
                            </form>
                        </div>

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
    });
    </script>

{{-- Critical device notifications --}}
<script>
// Toggle password visibility
function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}

// Validate password requirements in real-time for account settings
function validatePasswordRequirements(inputId, reqPrefix) {
    const password = document.getElementById(inputId);
    if (!password) return;
    
    password.addEventListener('input', function() {
        const value = this.value;
        
        // Length check (8-64 characters)
        const lengthOk = value.length >= 8 && value.length <= 64;
        updateRequirement(`${reqPrefix}-req-length`, lengthOk);
        
        // Case check (uppercase and lowercase)
        const caseOk = /[a-z]/.test(value) && /[A-Z]/.test(value);
        updateRequirement(`${reqPrefix}-req-case`, caseOk);
        
        // Number check
        const numberOk = /\d/.test(value);
        updateRequirement(`${reqPrefix}-req-number`, numberOk);
        
        // Symbol check
        const symbolOk = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value);
        updateRequirement(`${reqPrefix}-req-symbol`, symbolOk);
        
        // Different from current (always show as pending since we can't check client-side)
        updateRequirement(`${reqPrefix}-req-different`, false);
    });
}

function updateRequirement(reqId, isValid) {
    const reqElement = document.getElementById(reqId);
    if (!reqElement) return;
    
    const icon = reqElement.querySelector('i');
    if (isValid) {
        reqElement.classList.remove('text-muted');
        reqElement.classList.add('text-success');
        icon.classList.remove('bi-circle');
        icon.classList.add('bi-check-circle-fill');
    } else {
        reqElement.classList.remove('text-success');
        reqElement.classList.add('text-muted');
        icon.classList.remove('bi-check-circle-fill');
        icon.classList.add('bi-circle');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    // Initialize password validation for account settings
    validatePasswordRequirements('acc_new_password', 'acc');
    
    const notifContent = document.getElementById('notif-content');
    const notifBadge = document.getElementById('notif-badge');

    // Function to check critical devices and update notifications from DATABASE
    async function updateCriticalDeviceNotifications() {
        try {
            const response = await fetch('/api/critical-devices/status', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error('Failed to fetch critical devices');
            
            const criticalDevices = await response.json();
            const notifications = [];
            
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
        } catch (error) {
            console.error('Error updating notifications:', error);
        }
    }

    // Initial update
    updateCriticalDeviceNotifications();

    // Update notifications every 15 seconds from database
    setInterval(updateCriticalDeviceNotifications, 15000);

});
</script>

{{-- Global logging for navigation and important actions --}}
<script>
// Helper function stub (logs now handled by backend SystemLogger)
if (typeof window.addLog === 'undefined') {
    window.addLog = function(type, message, user = 'Admin') {
        // Logs are now written to database by backend only
        console.log('Log (backend only):', type, message, user);
    }
}

// Page navigation is now tracked by backend middleware

// Log logout action and submit form
function logLogoutAndSubmit() {
    // Logout is now logged by backend controller
    // Just submit the form
    document.getElementById('logoutForm').submit();
}
</script>

{{-- Include custom notification modals --}}
@include('components.notification-modals')

</body>
</html>
