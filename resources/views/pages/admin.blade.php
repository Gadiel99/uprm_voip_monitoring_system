{{--
/*
 * File: admin.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Administrative control panel with system configuration and management tools
 * 
 * Author: [Hector R. Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   This page provides administrators with centralized access to system configuration,
 *   maintenance, monitoring logs, server management, and user administration.
 * 
 * Access Control:
 *   - Restricted to users with administrative privileges
 *   - Visible only in left sidebar for authorized users
 *   - Requires authentication and admin role
 * 
 * Main Features (5 Tabs):
 *   1. Backup
 *      - Manual backup trigger
 *      - Automated backup schedule display
 *      - Backup completion confirmation
 *   
 *   2. Logs
 *      - Real-time system log viewer
 *      - Log filtering by severity
 *      - Sample log entries with timestamps
 *   
 *   3. Settings
 *      - Threshold configurations (Warning/Critical)
 *      - Notification preferences (Email/Push)
 *      - Alert frequency settings
 *      - Save functionality with confirmation
 *   
 *   4. Servers
 *      - Server status dashboard
 *      - Health check interface
 *      - Color-coded status indicators (Operational/Warning)
 *   
 *   5. Users
 *      - User management table
 *      - Role assignment
 *      - User information display (Name/Email/Role)
 * 
 * Tab Navigation:
 *   - Bootstrap nav-tabs for tabbed interface
 *   - Active tab highlighting
 *   - Click-to-switch functionality
 *   - Persistent state during page session
 * 
 * Configuration Options:
 *   Settings Tab:
 *     - Warning Threshold: Numeric input (default: 75)
 *     - Critical Threshold: Numeric input (default: 90)
 *     - Alert Frequency: Dropdown (Instant/Every 5/15/30 minutes)
 *     - Notification Type: Checkboxes (Email/Push)
 * 
 * User Management:
 *   Table Columns:
 *     - Name (user full name)
 *     - Email (contact address)
 *     - Role (Admin/User)
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3 for tab navigation and styling
 *   - Bootstrap Icons for visual indicators
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 1016 software design description
 *   - Adheres to IEEE 829 test documentation standards
 *   - Implements IEEE 730 quality assurance practices
 */
--}}
@extends('components.layout.app')

@section('content')
<style>
    /* === Institutional Green Theme === */
    .nav-pills .nav-link.active {
        background-color: #00844b !important;
        color: #fff !important;
        font-weight: 600;
        box-shadow: 0 0 6px rgba(0, 132, 75, 0.3);
    }
    .nav-pills .nav-link {
        color: #198754;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    .nav-pills .nav-link:hover { background-color: #d7f5df; }

    .badge-online  { background-color: #e6f9ed; color: #00844b; }
    .badge-offline { background-color: #fdeaea; color: #c82333; }

    .btn { transition: all 0.2s ease-in-out; }
    .btn-success { background-color: #00844b; border-color: #00844b; }
    .btn-success:hover { background-color: #006f3f; border-color: #006f3f; }
    .btn-outline-secondary:hover { background-color: #f1f3f4; color: #000; }
    .btn-danger:hover { background-color: #c82333; border-color: #c82333; color: #fff; }
</style>

<div class="container-fluid">
    <h4 class="fw-semibold mb-4">Admin Panel</h4>

    {{-- NAV PILLS --}}
    <ul class="nav nav-pills bg-light p-2 rounded mb-4" id="adminTab" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#backup"><i class="bi bi-hdd-stack me-2"></i>Backup</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#logs"><i class="bi bi-file-earmark-text me-2"></i>Logs</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#settings"><i class="bi bi-gear me-2"></i>Settings</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#users"><i class="bi bi-people me-2"></i>Users</button></li>
    </ul>

    <div class="tab-content" id="adminTabContent">

        {{-- BACKUP --}}
        <div class="tab-pane fade show active" id="backup" role="tabpanel">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-semibold mb-3">Backup Configuration</h5>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Local Backup Path</label>
                    <input type="text" class="form-control bg-light" value="/var/backups/monitoring" readonly>
                    <small class="text-muted">Backups are stored locally in this directory.</small>
                </div>

                <h5 class="fw-semibold mt-4 mb-3">Backup Operations</h5>
                <div class="d-flex gap-2">
                    <button class="btn btn-success"><i class="bi bi-archive me-2"></i>Create Backup & Download (ZIP)</button>
                    <button class="btn btn-outline-secondary"><i class="bi bi-upload me-2"></i>Restore from Backup</button>
                </div>
            </div>
        </div>

        {{-- LOGS --}}
        <div class="tab-pane fade" id="logs" role="tabpanel">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-semibold mb-3">System Logs</h5>
                <div class="d-flex mb-3">
                    <input type="text" class="form-control bg-light" id="logSearchInput" placeholder="Search logs by message, action, or user...">
                    <button class="btn btn-dark ms-2" onclick="filterLogs()"><i class="bi bi-search me-1"></i>Search</button>
                    <button class="btn btn-outline-secondary ms-2" onclick="clearLogs()"><i class="bi bi-trash me-1"></i>Clear All</button>
                </div>
                
                <div class="mb-3">
                    <button class="btn btn-sm btn-outline-primary me-2" onclick="filterByType('all')">All</button>
                    <button class="btn btn-sm btn-outline-info me-2" onclick="filterByType('INFO')">Info</button>
                    <button class="btn btn-sm btn-outline-success me-2" onclick="filterByType('SUCCESS')">Success</button>
                    <button class="btn btn-sm btn-outline-warning me-2" onclick="filterByType('WARNING')">Warning</button>
                    <button class="btn btn-sm btn-outline-danger" onclick="filterByType('ERROR')">Error</button>
                </div>
                
                <small class="text-muted d-block mb-3">Showing <span id="logCount">0</span> logs. Logs are automatically recorded for all system actions.</small>
                
                <div class="bg-light rounded p-3" style="max-height: 500px; overflow-y: auto;" id="logsContainer">
                    <div class="text-center text-muted py-4">No logs available. Actions will be logged here.</div>
                </div>
            </div>
        </div>

        {{-- SETTINGS (Critical Devices + Alert config) --}}
        <div class="tab-pane fade" id="settings" role="tabpanel">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-semibold mb-2">Critical Devices</h5>
                <p class="text-muted small">Devices that trigger bell alerts when inactive.</p>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addCriticalModal">
                        <i class="bi bi-plus-lg me-2"></i>Add Device
                    </button>
                </div>

                <table class="table table-bordered table-hover align-middle" id="criticalTable">
                    <thead class="table-light">
                        <tr>
                            <th>IP Address</th>
                            <th>MAC Address</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="critical-device-row" style="cursor: pointer;">
                            <td>192.168.1.10</td>
                            <td>00:1B:44:11:AA:00</td>
                            <td>Emergency Services</td>
                            <td><span class="badge badge-online">Online</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary edit-btn"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger delete-btn"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr class="critical-device-row" style="cursor: pointer;">
                            <td>192.168.1.11</td>
                            <td>00:1B:44:11:AA:01</td>
                            <td>Security Office</td>
                            <td><span class="badge badge-offline">Offline</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary edit-btn"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger delete-btn"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <hr class="my-4">

                {{-- Alert Thresholds (only Yellow range, as requested) --}}
                <h6 class="fw-semibold">Alert Thresholds</h6>
                <p class="text-muted small mb-3">Set the percentage range that defines warning conditions.</p>
                <div class="row g-4 mt-2">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Yellow Warning Status (Between)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" value="10">
                            <span class="input-group-text">%</span>
                            <input type="number" class="form-control" value="25">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- Alert Display Settings (moved into Settings tab) --}}
                <h6 class="fw-semibold mb-2">Alert Display Settings</h6>
                <p class="text-muted" style="font-size: 0.9rem;">Choose how alerts are sorted in the Alerts tab</p>
                <div class="form-check mb-2">
                    <input class="form-check-input" type="radio" name="sortOrder" id="bySeverity" checked>
                    <label class="form-check-label fw-semibold" for="bySeverity">By Severity</label>
                    <p class="text-muted small ms-4 mb-0">Critical alerts first, then warnings, then normal</p>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="sortOrder" id="alphabetical">
                    <label class="form-check-label fw-semibold" for="alphabetical">Alphabetically</label>
                    <p class="text-muted small ms-4 mb-0">Sort alerts by building name (A–Z)</p>
                </div>

                <hr class="my-4">

                {{-- Notification Settings (also inside Settings tab) --}}
                <h6 class="fw-semibold mb-3">Notification Settings</h6>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
                    <label class="form-check-label fw-semibold" for="emailNotifications">Email Notifications</label>
                    <p class="text-muted small ms-4 mb-0">Receive alerts via email</p>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="pushNotifications">
                    <label class="form-check-label fw-semibold" for="pushNotifications">Push Notifications</label>
                    <p class="text-muted small ms-4 mb-0">Browser push notifications</p>
                </div>
            </div>
        </div>

        {{-- USERS --}}
        <div class="tab-pane fade" id="users" role="tabpanel">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-semibold mb-3">User Management</h5>
                <p class="text-muted small">Manage system users, roles, and access permissions.</p>

                {{-- Flash messages (success only - errors shown in modal) --}}
                @if(session('status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-person-plus me-2"></i>Add User
                    </button>
                </div>

                <table class="table table-bordered align-middle" id="userTable">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users ?? [] as $user)
                        <tr data-user-id="{{ $user->id }}">
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td class="role-cell">
                                <span class="role-text">{{ ucfirst($user->role) }}</span>
                                <form action="{{ route('admin.users.update', $user) }}" method="POST" class="role-form d-none" style="display:inline-block;">
                                    @csrf
                                    @method('PATCH')
                                    <select class="form-select form-select-sm" name="role" style="width: auto; display: inline-block;">
                                        <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>User</option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-success ms-1"><i class="bi bi-check-lg"></i></button>
                                    <button type="button" class="btn btn-sm btn-secondary cancel-edit"><i class="bi bi-x-lg"></i></button>
                                </form>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary edit-role-btn"><i class="bi bi-pencil"></i></button>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline-block;">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger" onclick="return confirm('Delete this user?')"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

{{-- ===================== MODALS ===================== --}}

{{-- Add Critical Device --}}
<div class="modal fade" id="addCriticalModal" tabindex="-1" aria-labelledby="addCriticalModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="addCriticalModalLabel"><i class="bi bi-ethernet me-2"></i>Add Critical Device</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="formAddCritical">
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label fw-semibold">IP Address</label>
                <input type="text" class="form-control" id="critical_ip" placeholder="192.168.x.x" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">MAC Address</label>
                <input type="text" class="form-control" id="critical_mac" placeholder="00:1B:44:11:AA:XX" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Owner</label>
                <input type="text" class="form-control" id="critical_owner" placeholder="Emergency Services" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Status</label>
                <select class="form-select" id="critical_status">
                    <option value="Online">Online</option>
                    <option value="Offline" selected>Offline</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Add Server --}}
<div class="modal fade" id="addServerModal" tabindex="-1" aria-labelledby="addServerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="addServerModalLabel"><i class="bi bi-hdd-stack me-2"></i>Add Server</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form id="formAddServer">
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label fw-semibold">Name</label>
                <input type="text" class="form-control" id="server_name" placeholder="Primary Server" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">IP Address</label>
                <input type="text" class="form-control" id="server_ip" placeholder="192.168.1.10" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Port</label>
                <input type="number" class="form-control" id="server_port" placeholder="22" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Status</label>
                <select class="form-select" id="server_status">
                    <option value="Online" selected>Online</option>
                    <option value="Offline">Offline</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Add User --}}
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="addUserModalLabel"><i class="bi bi-person-plus me-2"></i>Add User</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="modal-body">
            {{-- Removed top alert; errors now appear inline below fields --}}
            @php($passwordErrors = $errors->get('password'))
            @php($emailErrors = $errors->get('email'))
            
            <div class="mb-3">
                <label class="form-label fw-semibold">Name</label>
                <input type="text" class="form-control @error('name') is-invalid @enderror" 
                       name="name" placeholder="Full name" value="{{ old('name') }}"
                       maxlength="255" autocomplete="name">
                @error('name')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="text" class="form-control @error('email') is-invalid @enderror" 
                       name="email" placeholder="example@uprm.edu" value="{{ old('email') }}"
                       maxlength="255" autocomplete="email" required>
                @if($emailErrors)
                    <div class="invalid-feedback d-block">
                        <ul class="mb-0 ps-3">
                            @foreach($emailErrors as $eErr)
                                <li>{{ $eErr }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" id="addUserPassword" class="form-control @error('password') is-invalid @enderror" 
                       name="password" placeholder="Example: CampusNet#24!" 
                       minlength="8" maxlength="64" 
                       pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()_+\-=\[\]{};':&quot;\\|,.<>\/?]).{8,64}$"
                       title="Password must contain: 8-64 characters, at least one uppercase letter, one lowercase letter, one number, and one special character"
                       autocomplete="new-password" required>
                @if($passwordErrors)
                    <div class="invalid-feedback d-block">
                        <ul class="mb-0 ps-3">
                            @foreach($passwordErrors as $pErr)
                                <li>{{ $pErr }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="text-muted small mt-2">
                    Password requirements:
                    <ul class="small mb-0">
                        <li>8–64 characters</li>
                        <li>At least one uppercase and one lowercase letter</li>
                        <li>At least one number</li>
                        <li>At least one symbol (e.g., ! @ # $ %)</li>
                    </ul>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Role</label>
                <select class="form-select @error('role') is-invalid @enderror" name="role" required>
                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
                @error('role')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Save</button>
        </div>
      </form>
    </div>
  </div>
</div>{{-- ===================== UNIVERSAL JS ===================== --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    // ===== Tab Activation =====
    const activeTab = @json($activeTab ?? null);
    if (activeTab) {
        const tabTrigger = document.querySelector(`[data-bs-target="#${activeTab}"]`);
        if (tabTrigger) {
            const tab = new bootstrap.Tab(tabTrigger);
            tab.show();
        }
    }
    
    // ===== Reopen Add User Modal if validation errors exist =====
    @if($errors->any() && old('_token'))
        const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
        addUserModal.show();
    @endif
    
    // ===== Users Tab Functionality =====
    // User table: Edit role functionality
    document.querySelectorAll('.edit-role-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const roleCell = row.querySelector('.role-cell');
            const roleText = roleCell.querySelector('.role-text');
            const roleForm = roleCell.querySelector('.role-form');
            
            roleText.classList.add('d-none');
            roleForm.classList.remove('d-none');
            this.disabled = true;
        });
    });
    
    // Cancel edit role
    document.querySelectorAll('.cancel-edit').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const roleCell = row.querySelector('.role-cell');
            const roleText = roleCell.querySelector('.role-text');
            const roleForm = roleCell.querySelector('.role-form');
            const editBtn = row.querySelector('.edit-role-btn');
            
            roleText.classList.remove('d-none');
            roleForm.classList.add('d-none');
            editBtn.disabled = false;
        });
    });
    
    // Password field: show as plain text on focus, mask on blur
    const passwordField = document.getElementById('addUserPassword');
    if (passwordField) {
        passwordField.addEventListener('focus', function() {
            this.type = 'text';
        });
        passwordField.addEventListener('blur', function() {
            this.type = 'password';
        });
    }
    
    // ===== Settings Tab - Critical Devices =====
    ['criticalTable','serverTable'].forEach(enableTableActions);
    document.getElementById('formAddCritical')?.addEventListener('submit', onAddCritical);
    document.getElementById('formAddServer')?.addEventListener('submit', onAddServer);
    
    syncCriticalDevicesToLocalStorage();
    const criticalTable = document.querySelector('#criticalTable');
    if (criticalTable) {
        const observer = new MutationObserver(() => {
            syncCriticalDevicesToLocalStorage();
        });
        observer.observe(criticalTable, {
            childList: true,
            subtree: true,
            characterData: true
        });
    }
    
    // Handle clicks on critical device rows
    document.addEventListener('click', (e) => {
        const row = e.target.closest('.critical-device-row');
        if (!row) return;
        if (e.target.closest('td:last-child')) return;
        if (e.target.closest('.edit-btn') || e.target.closest('.delete-btn')) return;
        
        const cells = row.querySelectorAll('td');
        const deviceIP = cells[0].textContent.trim();
        window.location.href = `/devices?ip=${encodeURIComponent(deviceIP)}&building=Critical+Devices`;
    });
    
    // ===== Logs Tab =====
    const backendLogs = @json($systemLogs ?? []);
    if (backendLogs.length > 0) {
        let frontendLogs = getLogs();
        const mergedLogs = [...backendLogs, ...frontendLogs];
        const uniqueLogs = mergedLogs.reduce((acc, log) => {
            const key = `${log.timestamp}-${log.message}`;
            if (!acc.some(l => `${l.timestamp}-${l.message}` === key)) {
                acc.push(log);
            }
            return acc;
        }, []);
        uniqueLogs.sort((a, b) => new Date(b.timestamp) - new Date(a.timestamp));
        if (uniqueLogs.length > 500) uniqueLogs.length = 500;
        localStorage.setItem('systemLogs', JSON.stringify(uniqueLogs));
    }
    
    updateLogsDisplay();
    const logs = getLogs();
    if (logs.length === 0) {
        addLog('INFO', 'System initialized - Logging started', 'System');
    }
    
    loadAlertDisplaySettings();
    
    const tabButtons = document.querySelectorAll('#adminTab button[data-bs-toggle="tab"]');
    tabButtons.forEach(button => {
        button.addEventListener('shown.bs.tab', (e) => {
            const tabName = e.target.textContent.trim();
            addLog('INFO', `Admin accessed ${tabName} tab`, 'Admin');
        });
    });
    
    // Alert display settings radio buttons
    const radioButtons = document.querySelectorAll('input[name="sortOrder"]');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', saveAlertDisplaySettings);
    });
});

function enableTableActions(tableId) {
    const tbody = document.querySelector('#' + tableId + ' tbody');
    if (!tbody) return;

    tbody.addEventListener('click', (e) => {
        const row = e.target.closest('tr');
        if (!row) return;

        // DELETE
        if (e.target.closest('.delete-btn')) {
            if (confirm('🗑️ Delete this entry?')) {
                // Get info before deleting
                const cells = [...row.children];
                const firstCell = cells[0]?.textContent.trim() || 'Unknown';
                
                row.remove();
                
                // Log deletion
                addLog('WARNING', `Entry deleted from ${tableId}: ${firstCell}`);
            }
            return;
        }

        // EDIT
        if (e.target.closest('.edit-btn')) {
            const actionCell = row.lastElementChild;
            const cells = [...row.children].slice(0, -1);
            
            // Store original values for logging
            const originalValues = cells.map(c => c.textContent.trim());
            
            cells.forEach((cell, index) => {
                const value = cell.textContent.trim();

                // Status column. Make it a select
                if (cell.innerText.includes('Online') || cell.innerText.includes('Offline') || 
                    cell.innerText.includes('Active') || cell.innerText.includes('Inactive')) {
                    const select = document.createElement('select');
                    select.className = 'form-select form-select-sm';
                    
                    // Determine if this is Online/Offline or Active/Inactive
                    const isActiveInactive = cell.innerText.includes('Active') || cell.innerText.includes('Inactive');
                    
                    if (isActiveInactive) {
                        select.innerHTML = `
                            <option value="Active" ${value.includes('Active') ? 'selected' : ''}>Active</option>
                            <option value="Inactive" ${value.includes('Inactive') ? 'selected' : ''}>Inactive</option>`;
                    } else {
                        select.innerHTML = `
                            <option value="Online" ${value.includes('Online') ? 'selected' : ''}>Online</option>
                            <option value="Offline" ${value.includes('Offline') ? 'selected' : ''}>Offline</option>`;
                    }
                    cell.textContent = '';
                    cell.appendChild(select);
                } else {
                    const input = document.createElement('input');
                    input.type = 'text';
                    input.className = 'form-control form-control-sm';
                    input.value = value;
                    cell.textContent = '';
                    cell.appendChild(input);
                }
            });
            actionCell.querySelector('.edit-btn').outerHTML =
                '<button class="btn btn-sm btn-success save-btn"><i class="bi bi-check-lg"></i></button>';
            return;
        }

        // SAVE
        if (e.target.closest('.save-btn')) {
            const actionCell = row.lastElementChild;
            const cells = [...row.children].slice(0, -1);
            
            // Collect new values for logging
            const newValues = [];
            
            cells.forEach(cell => {
                const input = cell.querySelector('input');
                const select = cell.querySelector('select');

                if (select) {
                    const status = select.value;
                    let badgeClass;
                    
                    // Determine badge class based on status value
                    if (status === 'Online' || status === 'Active') {
                        badgeClass = 'badge-online';
                    } else if (status === 'Offline' || status === 'Inactive') {
                        badgeClass = 'badge-offline';
                    }
                    
                    cell.innerHTML = `<span class="badge ${badgeClass}">${status}</span>`;
                    newValues.push(status);
                } else if (input) {
                    cell.textContent = input.value;
                    newValues.push(input.value);
                } else {
                    newValues.push(cell.textContent.trim());
                }
            });
            
            // Log the edit
            addLog('INFO', `Entry updated in ${tableId}: ${newValues[0]} - Changes saved`);

            actionCell.querySelector('.save-btn').outerHTML =
                '<button class="btn btn-sm btn-outline-secondary edit-btn"><i class="bi bi-pencil"></i></button>';
            return;
        }
    });
}

/* ADD FUNCTIONS  */
function onAddCritical(ev) {
    ev.preventDefault();
    const ip = document.getElementById('critical_ip').value.trim();
    const mac = document.getElementById('critical_mac').value.trim();
    const owner = document.getElementById('critical_owner').value.trim();
    const status = document.getElementById('critical_status').value;

    const tbody = document.querySelector('#criticalTable tbody');
    const badge = status === 'Online' ? 'badge-online' : 'badge-offline';
    tbody.insertAdjacentHTML('beforeend', `
        <tr class="critical-device-row" style="cursor: pointer;">
            <td>${ip}</td>
            <td>${mac}</td>
            <td>${owner}</td>
            <td><span class="badge ${badge}">${status}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-secondary edit-btn"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-danger delete-btn"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    `);
    
    // Log the action
    addLog('SUCCESS', `Critical device added: ${owner} (${ip}) - Status: ${status}`);
    
    bootstrap.Modal.getInstance(document.getElementById('addCriticalModal')).hide();
    ev.target.reset();
}

function onAddServer(ev) {
    ev.preventDefault();
    const name = document.getElementById('server_name').value.trim();
    const ip = document.getElementById('server_ip').value.trim();
    const port = document.getElementById('server_port').value.trim();
    const status = document.getElementById('server_status').value;

    const tbody = document.querySelector('#serverTable tbody');
    const badge = status === 'Online' ? 'badge-online' : 'badge-offline';
    tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <td>${name}</td>
            <td>${ip}</td>
            <td>${port}</td>
            <td><span class="badge ${badge}">${status}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-secondary edit-btn"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-danger delete-btn"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    `);
    bootstrap.Modal.getInstance(document.getElementById('addServerModal')).hide();
    ev.target.reset();
}

// function onAddUser(ev) {
//     ...removed, now handled by backend...
// }

/* SYNC CRITICAL DEVICES TO LOCALSTORAGE FOR NOTIFICATIONS */
function syncCriticalDevicesToLocalStorage() {
    const criticalTable = document.querySelector('#criticalTable tbody');
    if (!criticalTable) return;
    
    const devices = [];
    const rows = criticalTable.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cells = row.querySelectorAll('td');
        if (cells.length >= 4) {
            const ip = cells[0].textContent.trim();
            const mac = cells[1].textContent.trim();
            const owner = cells[2].textContent.trim();
            const statusBadge = cells[3].querySelector('.badge');
            const status = statusBadge ? statusBadge.textContent.trim() : 'Unknown';
            
            devices.push({
                ip: ip,
                mac: mac,
                owner: owner,
                status: status
            });
        }
    });
    
    localStorage.setItem('criticalDevices', JSON.stringify(devices));
}

/* ==================== LOGGING SYSTEM ==================== */

// Get logs from localStorage or initialize
function getLogs() {
    return JSON.parse(localStorage.getItem('systemLogs') || '[]');
}

// Save logs to localStorage
function saveLogs(logs) {
    localStorage.setItem('systemLogs', JSON.stringify(logs));
    updateLogsDisplay();
}

// Add a new log entry
window.addLog = function(type, message, user = 'Admin') {
    const logs = getLogs();
    const timestamp = new Date().toISOString().replace('T', ' ').substring(0, 19);
    
    const newLog = {
        timestamp: timestamp,
        type: type, // INFO, SUCCESS, WARNING, ERROR
        message: message,
        user: user,
        id: Date.now()
    };
    
    logs.unshift(newLog); // Add to beginning
    
    // Keep only last 500 logs
    if (logs.length > 500) {
        logs.pop();
    }
    
    saveLogs(logs);
}

// Display logs in the container
function updateLogsDisplay(filteredLogs = null) {
    const container = document.getElementById('logsContainer');
    const logs = filteredLogs || getLogs();
    
    document.getElementById('logCount').textContent = logs.length;
    
    if (logs.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-4">No logs available. Actions will be logged here.</div>';
        return;
    }
    
    const logColors = {
        'INFO': 'text-primary',
        'SUCCESS': 'text-success',
        'WARNING': 'text-warning',
        'ERROR': 'text-danger'
    };
    
    container.innerHTML = logs.map(log => `
        <div class="log-entry mb-2 pb-2 border-bottom">
            <code class="${logColors[log.type] || 'text-secondary'}">
                [${log.timestamp}] <strong>${log.type}</strong>: ${log.message}
                ${log.user !== 'System' ? `<span class="text-muted">(${log.user})</span>` : ''}
            </code>
        </div>
    `).join('');
}

// Filter logs by search term
function filterLogs() {
    const searchTerm = document.getElementById('logSearchInput').value.toLowerCase();
    const logs = getLogs();
    
    if (!searchTerm) {
        updateLogsDisplay();
        return;
    }
    
    const filtered = logs.filter(log => 
        log.message.toLowerCase().includes(searchTerm) ||
        log.type.toLowerCase().includes(searchTerm) ||
        log.user.toLowerCase().includes(searchTerm)
    );
    
    updateLogsDisplay(filtered);
}

// Filter by log type
function filterByType(type) {
    const logs = getLogs();
    
    if (type === 'all') {
        updateLogsDisplay();
        return;
    }
    
    const filtered = logs.filter(log => log.type === type);
    updateLogsDisplay(filtered);
}

// Clear all logs
function clearLogs() {
    if (confirm('Are you sure you want to clear all logs?')) {
        localStorage.removeItem('systemLogs');
        addLog('WARNING', 'All system logs were cleared by admin', 'Admin');
        updateLogsDisplay();
    }
}

/* ==================== ALERT DISPLAY SETTINGS ==================== */

// Load saved alert display settings from localStorage
function loadAlertDisplaySettings() {
    const sortOrder = localStorage.getItem('alertSortOrder') || 'bySeverity';
    
    // Set the radio button based on saved preference
    if (sortOrder === 'bySeverity') {
        document.getElementById('bySeverity').checked = true;
    } else {
        document.getElementById('alphabetical').checked = true;
    }
}

// Save alert display settings to localStorage
function saveAlertDisplaySettings() {
    const sortOrder = document.querySelector('input[name="sortOrder"]:checked').id;
    localStorage.setItem('alertSortOrder', sortOrder);
    addLog('INFO', `Alert display settings changed to: ${sortOrder === 'bySeverity' ? 'By Severity' : 'Alphabetically'}`, 'Admin');
    alert('Alert display settings saved successfully!');
}

// Note: Radio button listeners are attached in the main DOMContentLoaded above
</script>
@endsection
