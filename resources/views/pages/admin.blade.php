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
            {{-- Success/Error Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- Backup Statistics --}}
            <div class="card border-0 shadow-sm p-3 mb-3">
                <h6 class="fw-semibold mb-2">Backup Statistics</h6>
                <div class="row g-2">
                    <div class="col-md-3">
                        <div class="border rounded p-2 text-center">
                            <div class="text-muted" style="font-size: 0.75rem;">Total Backups</div>
                            <div class="h5 mb-0 text-primary">{{ $backupStats['total_backups'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-2 text-center">
                            <div class="text-muted" style="font-size: 0.75rem;">Total Size</div>
                            <div class="h5 mb-0 text-success">{{ $backupStats['total_size_formatted'] ?? 'N/A' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-2 text-center">
                            <div class="text-muted" style="font-size: 0.75rem;">Retention</div>
                            <div class="h5 mb-0 text-info">{{ ($backupStats['retention_weeks'] ?? 'N/A') }} {{ isset($backupStats['retention_weeks']) ? 'weeks' : '' }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-2 text-center">
                            <div class="text-muted" style="font-size: 0.75rem;">Schedule</div>
                            <div class="h5 mb-0 text-warning">Weekly</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Backup Actions --}}
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h6 class="fw-semibold mb-3">Backup Management</h6>
                
                @if($latestBackup)
                    <div class="mb-4 pb-3 border-bottom">
                        <div class="mb-2">
                            <strong>Latest Backup:</strong> 
                            <span class="font-monospace">{{ $latestBackup['filename'] }}</span>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <small class="text-muted">Size:</small> {{ $latestBackup['size_formatted'] }}
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Created:</small> {{ $latestBackup['created_at'] }}
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted">Age:</small> {{ $latestBackup['age'] }}
                            </div>
                        </div>
                    </div>
                @endif

                <div class="mb-3">
                    <label class="form-label fw-semibold">Storage Location</label>
                    <input type="text" class="form-control bg-light" value="{{ config('backup.path', storage_path('app/backups')) }}" readonly>
                    <small class="text-muted">Backups are automatically created weekly and stored in this directory.</small>
                </div>

                <div class="d-flex gap-2 mt-4">
                    @if($latestBackup)
                        <a href="{{ route('admin.backup.download') }}" class="btn btn-success">
                            <i class="bi bi-download me-2"></i>Download Latest Backup
                        </a>
                        <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#restoreModal">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Restore from Backup
                        </button>
                    @else
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-2"></i>No backups available. Weekly backups run automatically on Sundays at 3:00 AM.
                        </div>
                    @endif
                </div>
            </div>

            {{-- All Backups List --}}
            @if(!empty($allBackups))
                <div class="card border-0 shadow-sm p-4 mb-4">
                    <h6 class="fw-semibold mb-3">Available Backups</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Filename</th>
                                    <th>Size</th>
                                    <th>Created</th>
                                    <th>Age</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($allBackups as $backup)
                                    <tr>
                                        <td class="font-monospace small">{{ $backup['filename'] }}</td>
                                        <td>{{ $backup['size_formatted'] }}</td>
                                        <td>{{ $backup['created_at'] }}</td>
                                        <td>{{ $backup['age'] }}</td>
                                        <td>
                                            <a href="{{ route('admin.backup.download.file', ['filename' => $backup['filename']]) }}" 
                                               class="btn btn-sm btn-outline-success me-1">
                                                <i class="bi bi-download me-1"></i>Download
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-primary" 
                                                    onclick="restoreBackup('{{ $backup['filename'] }}')">
                                                <i class="bi bi-arrow-counterclockwise me-1"></i>Restore
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- Restore Confirmation Modal --}}
        <div class="modal fade" id="restoreModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">
                            <i class="bi bi-exclamation-triangle me-2"></i>Restore Database
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-danger">
                            <strong>Warning:</strong> This will replace ALL current database data with the backup data. This action cannot be undone!
                        </div>
                        <p>Are you sure you want to restore from: <strong id="restoreFilename">{{ $latestBackup['filename'] ?? '' }}</strong>?</p>
                        <form id="restoreForm" action="{{ route('admin.backup.restore') }}" method="POST">
                            @csrf
                            <input type="hidden" name="backup_file" id="restoreFileInput" value="{{ $latestBackup['filename'] ?? '' }}">
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-warning" onclick="document.getElementById('restoreForm').submit();">
                            <i class="bi bi-arrow-counterclockwise me-2"></i>Restore Database
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- LOGS --}}
        <div class="tab-pane fade" id="logs" role="tabpanel">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <div class="d-flex mb-3">
                    <input type="text" class="form-control form-control-sm bg-light" id="logSearchInput" placeholder="Search logs by timestamp, IP, action, or comment...">
                    <button class="btn btn-success btn-sm ms-2 px-3" onclick="filterLogs()"><i class="bi bi-search me-1"></i>Search</button>
                    <button class="btn btn-outline-secondary btn-sm ms-2 px-3" onclick="clearLogs()"><i class="bi bi-trash me-1"></i>Clear All</button>
                </div>
                
                <div class="mb-3">
                    <button class="btn btn-sm btn-outline-secondary me-2 py-1 px-2" onclick="filterByAction('all')">All</button>
                    <button class="btn btn-sm btn-outline-primary me-2 py-1 px-2" onclick="filterByAction('LOGIN')">Login</button>
                    <button class="btn btn-sm btn-outline-secondary me-2 py-1 px-2" onclick="filterByAction('LOGOUT')">Logout</button>
                    <button class="btn btn-sm btn-outline-success me-2 py-1 px-2" onclick="filterByAction('ADD')">Add</button>
                    <button class="btn btn-sm btn-outline-warning me-2 py-1 px-2" onclick="filterByAction('EDIT')">Edit</button>
                    <button class="btn btn-sm btn-outline-danger me-2 py-1 px-2" onclick="filterByAction('DELETE')">Delete</button>
                    <button class="btn btn-sm btn-outline-danger py-1 px-2" onclick="filterByAction('ERROR')">Error</button>
                </div>
                
                <small class="text-muted d-block mb-3">Showing <span id="logCount">0</span> logs. Only important system actions are recorded (add, delete, edit, login, logout, errors).</small>
                
                <div id="logsContainer" class="border rounded bg-white" style="max-height: 500px; overflow-y: auto;">
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

                @if(session('alert_settings_status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('alert_settings_status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <table class="table table-bordered table-hover align-middle" id="criticalTable">
                    <thead class="table-light">
                        <tr>
                            <th>IP Address</th>
                            <th>MAC Address</th>
                            <th>Owner</th>
                            <th>Status</th>
                            <th>Extensions</th>
                            <th style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($criticalDevices ?? [] as $device)
                        @php
                            $exts = ($extensionsByCriticalDevice ?? collect())->get($device->device_id) ?? collect();
                            $ownerName = $device->owner;
                            if (!$ownerName && $exts->isNotEmpty()) {
                                $ownerName = trim($exts->first()->user_first_name . ' ' . $exts->first()->user_last_name);
                            }
                        @endphp
                        <tr class="critical-device-row" data-device-id="{{ $device->device_id }}">
                            <td class="ip-cell">{{ $device->ip_address }}</td>
                            <td class="mac-cell">{{ $device->mac_address }}</td>
                            <td class="owner-cell">{{ $ownerName ?? 'N/A' }}</td>
                            <td class="status-cell">
                                @if($device->status === 'offline')
                                    <span class="text-danger fw-semibold">Offline</span>
                                @else
                                    <span class="text-success fw-semibold">Online</span>
                                @endif
                            </td>
                            <td class="extensions-cell">
                                @if($exts->isEmpty())
                                    <span class="text-muted">—</span>
                                @else
                                    @foreach($exts as $e)
                                        {{ $e->extension_number }}@if(!$loop->last), @endif
                                    @endforeach
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.critical-devices.destroy', $device->device_id) }}" method="POST" style="display:inline-block;" onsubmit="return handleFormSubmit(event, 'Remove this device from critical list?', 'Remove Device')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No critical devices configured.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                <hr class="my-4">

                {{-- Alert Thresholds Configuration --}}
                <h6 class="fw-semibold">Alert Thresholds</h6>
                <p class="text-muted small mb-3">Configure offline device percentage thresholds for building alerts.</p>

                <form method="POST" action="{{ route('admin.alert-settings.update') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Lower Threshold (%)</label>
                            <input type="number" class="form-control @error('lower_threshold') is-invalid @enderror" 
                                   name="lower_threshold" min="0" max="100" 
                                   value="{{ old('lower_threshold', $alertSettings->lower_threshold ?? 30) }}">
                            <small class="text-muted d-block mt-1">Below this is <span class="text-success fw-semibold">Normal</span></small>
                            @error('lower_threshold')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Upper Threshold (%)</label>
                            <input type="number" class="form-control @error('upper_threshold') is-invalid @enderror" 
                                   name="upper_threshold" min="0" max="100" 
                                   value="{{ old('upper_threshold', $alertSettings->upper_threshold ?? 70) }}">
                            <small class="text-muted d-block mt-1">Above this is <span class="text-danger fw-semibold">Critical</span></small>
                            @error('upper_threshold')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold d-block">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-check-circle me-2"></i>Save Thresholds
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="resetThresholdsToDefault()">
                                    <i class="bi bi-arrow-counterclockwise me-2"></i>Reset to Default
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <hr class="my-4">

                {{-- Notification Settings (System-wide for all admins) --}}
                <h6 class="fw-semibold mb-3">Notification Settings <small class="text-muted">(System-wide)</small></h6>
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" id="emailNotifications" {{ $alertSettings->email_notifications_enabled ? 'checked' : '' }}>
                    <label class="form-check-label fw-semibold" for="emailNotifications">Email Notifications</label>
                    <p class="text-muted small ms-4 mb-0">Enable email alerts for all users (system-wide setting)</p>
                </div>
            </div>
        </div>

        {{-- USERS --}}
        <div class="tab-pane fade" id="users" role="tabpanel">
            <div class="card border-0 shadow-sm p-4 mb-4">
                {{-- Flash messages (success only - errors shown in modal) --}}
                @if(session('user_status'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('user_status') }}
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
                                <span class="role-text">{{ $user->role === 'admin' ? 'Administrator' : 'Assistant' }}</span>
                                <form action="{{ route('admin.users.update', $user) }}" method="POST" class="role-form d-none" style="display:inline-block;">
                                    @csrf
                                    @method('PATCH')
                                    <select class="form-select form-select-sm" name="role" style="width: auto; display: inline-block;">
                                        <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>Assistant</option>
                                        <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Administrator</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-success ms-1"><i class="bi bi-check-lg"></i></button>
                                    <button type="button" class="btn btn-sm btn-secondary cancel-edit"><i class="bi bi-x-lg"></i></button>
                                </form>
                            </td>
                            <td>
                                @if($user->id === Auth::id())
                                    <button class="btn btn-sm btn-outline-secondary" disabled title="Cannot edit yourself"><i class="bi bi-pencil"></i></button>
                                    <button class="btn btn-sm btn-danger" disabled title="Cannot delete yourself"><i class="bi bi-trash"></i></button>
                                @else
                                    <button class="btn btn-sm btn-outline-secondary edit-role-btn"><i class="bi bi-pencil"></i></button>
                                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" style="display:inline-block;" onsubmit="return handleFormSubmit(event, 'Delete this user?', 'Delete User')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                    </form>
                                @endif
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
  <div class="modal-dialog modal-lg">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="addCriticalModalLabel"><i class="bi bi-ethernet me-2"></i>Add Critical Device</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('admin.critical-devices.store') }}">
        @csrf
        <div class="modal-body">
            {{-- Search Input --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Search Device</label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="deviceSearchInput" 
                    placeholder="Type IP, MAC (e.g. 4aba), or Owner name..."
                    autocomplete="off"
                >
                <small class="text-muted">No need for colons in MAC or dots in IP - just type the numbers/letters</small>
            </div>

            {{-- Device Select --}}
            <div class="mb-3">
                <label class="form-label fw-semibold">Select Device</label>
                <select class="form-select @error('device_id') is-invalid @enderror" name="device_id" id="device_select" required size="8" style="font-family: monospace; font-size: 0.9rem;">
                    <option value="">-- Start typing to search devices --</option>
                    @foreach($availableDevices ?? [] as $device)
                        <option value="{{ $device->device_id }}" data-searchable="{{ strtolower($device->ip_address . ' ' . $device->mac_address . ' ' . ($device->owner ?? '')) }}" style="display: none;" {{ old('device_id') == $device->device_id ? 'selected' : '' }}>
                            {{ $device->ip_address }} | {{ $device->mac_address }} @if($device->owner) | {{ $device->owner }} @endif
                        </option>
                    @endforeach
                </select>
                @error('device_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted d-block mt-2">
                    <span id="deviceCount">{{ count($availableDevices ?? []) }}</span> device(s) available. 
                    Only existing devices that are not already marked as critical can be selected.
                </small>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-success">Add to Critical List</button>
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
                <div class="input-group">
                    <input type="password" id="addUserPassword" class="form-control @error('password') is-invalid @enderror" 
                           name="password" placeholder="Example: CampusNet#24!" 
                           minlength="8" maxlength="64" 
                           title="Password must contain: 8-64 characters, at least one uppercase letter, one lowercase letter, one number, and one special character"
                           autocomplete="new-password" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('addUserPassword', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
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
                    <ul class="small mb-0" id="addUserPasswordReqs" style="list-style: none; padding-left: 0;">
                        <li id="req-length"><i class="bi bi-circle"></i> 8–64 characters</li>
                        <li id="req-case"><i class="bi bi-circle"></i> At least one uppercase and one lowercase letter</li>
                        <li id="req-number"><i class="bi bi-circle"></i> At least one number</li>
                        <li id="req-symbol"><i class="bi bi-circle"></i> At least one symbol (e.g., ! @ # $ %)</li>
                    </ul>
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Role</label>
                <select class="form-select @error('role') is-invalid @enderror" name="role" required>
                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>Assistant</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
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

// Validate password requirements in real-time for Add User
function validatePasswordRequirements(inputId, reqPrefix) {
    const password = document.getElementById(inputId);
    if (!password) return;
    validatePasswordRequirementsWithElement(password, reqPrefix);
}

function validatePasswordRequirementsWithElement(password, reqPrefix) {
    if (!password) return;
    
    // Check if already initialized
    if (password.dataset.validationInitialized === 'true') return;
    
    password.dataset.validationInitialized = 'true';
    
    password.addEventListener('input', function() {
        const value = this.value;
        
        // Length check (8-64 characters)
        const lengthOk = value.length >= 8 && value.length <= 64;
        updateRequirement(`${reqPrefix}-length`, lengthOk);
        
        // Case check (uppercase and lowercase)
        const caseOk = /[a-z]/.test(value) && /[A-Z]/.test(value);
        updateRequirement(`${reqPrefix}-case`, caseOk);
        
        // Number check
        const numberOk = /\d/.test(value);
        updateRequirement(`${reqPrefix}-number`, numberOk);
        
        // Symbol check
        const symbolOk = /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(value);
        updateRequirement(`${reqPrefix}-symbol`, symbolOk);
    });
}

function updateRequirement(reqId, isValid) {
    const reqElement = document.getElementById(reqId);
    if (!reqElement) return;
    
    const icon = reqElement.querySelector('i');
    if (!icon) return;
    
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

// Reset threshold values to default
function resetThresholdsToDefault() {
    const lowerThresholdInput = document.querySelector('input[name="lower_threshold"]');
    const upperThresholdInput = document.querySelector('input[name="upper_threshold"]');
    
    if (lowerThresholdInput) lowerThresholdInput.value = 10;
    if (upperThresholdInput) upperThresholdInput.value = 25;
}

document.addEventListener('DOMContentLoaded', () => {
    // Initialize password validation when Add User modal is shown
    const addUserModalEl = document.getElementById('addUserModal');
    
    if (addUserModalEl) {
        // Listen for Bootstrap modal shown event
        addUserModalEl.addEventListener('shown.bs.modal', function() {
            setTimeout(() => {
                const passwordInput = addUserModalEl.querySelector('#addUserPassword');
                if (passwordInput) {
                    validatePasswordRequirementsWithElement(passwordInput, 'req');
                }
            }, 200);
        });
        
        // If modal is reopened due to validation errors
        @if(old('_token') && ($errors->has('name') || $errors->has('email') || $errors->has('password') || $errors->has('role')))
            setTimeout(() => {
                const passwordInput = addUserModalEl.querySelector('#addUserPassword');
                if (passwordInput) {
                    validatePasswordRequirementsWithElement(passwordInput, 'req');
                }
            }, 500);
        @endif
    }
    
    // ===== Tab Activation =====
    const activeTab = @json($activeTab ?? request()->get('tab') ?? (($errors->has('lower_threshold') || $errors->has('upper_threshold')) ? 'settings' : null));
    if (activeTab) {
        const tabTrigger = document.querySelector(`[data-bs-target="#${activeTab}"]`);
        if (tabTrigger) {
            const tab = new bootstrap.Tab(tabTrigger);
            tab.show();
        }
    }
    
    // ===== Reopen Add User Modal only if user form has validation errors =====
    @if(old('_token') && ($errors->has('name') || $errors->has('email') || $errors->has('password') || $errors->has('role')))
        const addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
        addUserModal.show();
    @endif
    
    // ===== Reopen Add Critical Device Modal if validation errors =====
    @if($errors->has('device_id'))
        const addCriticalModal = new bootstrap.Modal(document.getElementById('addCriticalModal'));
        addCriticalModal.show();
    @endif
    
    // ===== Critical Device Search Functionality =====
    const deviceSearchInput = document.getElementById('deviceSearchInput');
    const deviceSelect = document.getElementById('device_select');
    const deviceCountSpan = document.getElementById('deviceCount');
    
    if (deviceSearchInput && deviceSelect) {
        deviceSearchInput.addEventListener('input', function() {
            const searchTermRaw = this.value.toLowerCase().trim();
            // Remove colons, dashes, and spaces from search term for flexible MAC search
            const searchTerm = searchTermRaw.replace(/[:.\-\s]/g, '');
            const options = deviceSelect.querySelectorAll('option');
            let visibleCount = 0;
            
            // If search is empty, hide all devices
            if (searchTermRaw === '') {
                options.forEach(option => {
                    if (option.value === '') {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                    }
                });
                if (deviceCountSpan) {
                    deviceCountSpan.textContent = '0';
                }
                deviceSelect.value = '';
                return;
            }
            
            options.forEach(option => {
                if (option.value === '') {
                    // Keep the placeholder option visible
                    option.style.display = '';
                    return;
                }
                
                const searchableText = option.getAttribute('data-searchable') || '';
                // Also remove colons, dashes, and spaces from searchable text for comparison
                const normalizedSearchableText = searchableText.replace(/[:.\-\s]/g, '');
                
                // Match either normalized text (for MAC) or original text (for IP/Owner)
                if (normalizedSearchableText.includes(searchTerm) || searchableText.includes(searchTermRaw)) {
                    option.style.display = '';
                    visibleCount++;
                } else {
                    option.style.display = 'none';
                }
            });
            
            // Update count
            if (deviceCountSpan) {
                deviceCountSpan.textContent = visibleCount;
            }
            
            // Auto-select first visible option if only one result
            if (visibleCount === 1 && searchTerm !== '') {
                const firstVisible = Array.from(options).find(opt => 
                    opt.value !== '' && opt.style.display !== 'none'
                );
                if (firstVisible) {
                    deviceSelect.value = firstVisible.value;
                }
            }
        });
        
        // Clear search when modal closes and hide all options
        document.getElementById('addCriticalModal').addEventListener('hidden.bs.modal', function() {
            deviceSearchInput.value = '';
            deviceSelect.querySelectorAll('option').forEach(opt => {
                if (opt.value === '') {
                    opt.style.display = '';
                } else {
                    opt.style.display = 'none';
                }
            });
            if (deviceCountSpan) {
                deviceCountSpan.textContent = '0';
            }
            deviceSelect.value = '';
        });
    }
    
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
    
    // ===== Notification Preferences (System-wide) =====
    const emailNotificationsToggle = document.getElementById('emailNotifications');
    
    if (emailNotificationsToggle) {
        emailNotificationsToggle.addEventListener('change', async function() {
            const isEnabled = this.checked;
            try {
                const response = await fetch('{{ route('admin.notification-preferences.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        email_notifications_enabled: isEnabled
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    addLog('INFO', `System-wide email notifications ${isEnabled ? 'enabled' : 'disabled'}`);
                } else {
                    throw new Error(data.message || 'Failed to update preferences');
                }
            } catch (error) {
                console.error('Error updating email notifications:', error);
                // Revert toggle on error
                this.checked = !isEnabled;
                customAlert('Failed to update email notification preferences. Please try again.', 'Error', 'error');
            }
        });
    }
    
    // ===== UTILITY FUNCTIONS =====
    
    /**
     * Sanitize user input to prevent issues with special characters
     */
    function sanitizeInput(input) {
        if (!input) return '';
        
        // Remove potentially problematic characters
        return input
            .replace(/[<>]/g, '') // Remove HTML tags
            .replace(/[;'"\\]/g, '') // Remove semicolons, quotes, backslashes
            .replace(/\r?\n|\r/g, ' ') // Replace newlines with spaces
            .trim();
    }
    
    // ===== Settings Tab - Critical Devices =====
    // Only enable table actions for serverTable (not criticalTable since it uses forms)
    ['serverTable'].forEach(enableTableActions);
    document.getElementById('formAddServer')?.addEventListener('submit', onAddServer);
    
    // Note: Critical devices are now fetched directly from database via API
    // No need to sync to localStorage anymore
    
    // ===== Logs Tab =====
    // Store backend logs directly (from database) - don't merge with localStorage
    const backendLogs = @json($systemLogs ?? []);
    if (backendLogs.length > 0) {
        localStorage.setItem('systemLogs', JSON.stringify(backendLogs));
    }
    
    updateLogsDisplay();
    
    loadAlertDisplaySettings();
});

function enableTableActions(tableId) {
    const tbody = document.querySelector('#' + tableId + ' tbody');
    if (!tbody) return;

    tbody.addEventListener('click', (e) => {
        const row = e.target.closest('tr');
        if (!row) return;

        // DELETE
        if (e.target.closest('.delete-btn')) {
            customConfirm('🗑️ Delete this entry?', 'Delete Entry').then(confirmed => {
                if (confirmed) {
                    // Get info before deleting
                    const cells = [...row.children];
                    const firstCell = cells[0]?.textContent.trim() || 'Unknown';
                    
                    row.remove();
                    
                    // Log deletion
                    addLog('DELETE', `Entry deleted from ${tableId}: ${firstCell}`);
                }
            });
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
                    // Sanitize input value before saving
                    const sanitizedValue = sanitizeInput(input.value);
                    cell.textContent = sanitizedValue;
                    newValues.push(sanitizedValue);
                } else {
                    newValues.push(cell.textContent.trim());
                }
            });
            
            // Log the edit
            addLog('EDIT', `Entry updated in ${tableId}: ${newValues[0]} - Changes saved`);

            actionCell.querySelector('.save-btn').outerHTML =
                '<button class="btn btn-sm btn-outline-secondary edit-btn"><i class="bi bi-pencil"></i></button>';
            return;
        }
    });
}

/* ADD FUNCTIONS  */
function onAddServer(ev) {
    ev.preventDefault();
    const nameRaw = document.getElementById('server_name').value.trim();
    const ipRaw = document.getElementById('server_ip').value.trim();
    const portRaw = document.getElementById('server_port').value.trim();
    const status = document.getElementById('server_status').value;
    
    // Sanitize inputs
    const name = sanitizeInput(nameRaw);
    const ip = sanitizeInput(ipRaw);
    const port = sanitizeInput(portRaw);
    
    // Validate
    if (!name || !ip || !port) {
        customAlert('❌ All fields are required', 'Validation Error', 'error');
        return;
    }

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

// Add a new log entry (deprecated - logs now handled by backend SystemLogger)
// This function is kept for backwards compatibility but does nothing
window.addLog = function(action, comment, user = 'Admin') {
    // Logs are now written to database by backend only
    // Frontend actions that need logging should make API calls or refresh the page
    console.log('Log action (backend only):', action, comment, user);
}

// Display logs in string format
function updateLogsDisplay(filteredLogs = null) {
    const container = document.getElementById('logsContainer');
    const logs = filteredLogs || getLogs();
    
    document.getElementById('logCount').textContent = logs.length;
    
    if (logs.length === 0) {
        container.innerHTML = '<div class="text-center text-muted py-4">No logs available. Actions will be logged here.</div>';
        return;
    }
    
    const actionColors = {
        'LOGIN': '#0d6efd',      // Blue
        'LOGOUT': '#6c757d',     // Gray
        'ADD': '#198754',        // Green
        'EDIT': '#ffc107',       // Yellow/Orange
        'DELETE': '#dc3545',     // Red
        'ERROR': '#dc3545'       // Red
    };
    
    container.innerHTML = logs.map(log => {
        const color = actionColors[log.action] || '#6c757d';
        return `
            <div class="log-entry border-bottom py-2" style="display: grid; grid-template-columns: 160px 90px 120px 1fr; gap: 12px; align-items: center; color: ${color};">
                <span class="small">${log.timestamp}</span>
                <span class="small fw-semibold">${log.action}</span>
                <span class="small font-monospace">${log.ip || 'N/A'}</span>
                <span class="small">${log.comment}</span>
            </div>`;
    }).join('');
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
        log.comment.toLowerCase().includes(searchTerm) ||
        log.action.toLowerCase().includes(searchTerm) ||
        log.timestamp.toLowerCase().includes(searchTerm) ||
        (log.ip && log.ip.toLowerCase().includes(searchTerm))
    );
    
    updateLogsDisplay(filtered);
}

// Filter by action type
function filterByAction(action) {
    const logs = getLogs();
    
    if (action === 'all') {
        updateLogsDisplay();
        return;
    }
    
    const filtered = logs.filter(log => log.action === action);
    updateLogsDisplay(filtered);
}

// Clear all logs
function clearLogs() {
    customConfirm('Are you sure you want to clear all logs?', 'Clear All Logs').then(confirmed => {
        if (confirmed) {
            localStorage.removeItem('systemLogs');
            addLog('DELETE', 'All system logs were cleared by admin', 'Admin');
            updateLogsDisplay();
        }
    });
}

/* ==================== FORM SUBMIT HANDLER ==================== */
function handleFormSubmit(event, message, title) {
    event.preventDefault();
    const form = event.target;
    customConfirm(message, title).then(confirmed => {
        if (confirmed) {
            form.submit();
        }
    });
    return false;
}

/* ==================== ALERT DISPLAY SETTINGS ==================== */

// Alerts are always sorted by severity first (Critical > Warning > Normal), 
// then alphabetically within each severity level
function loadAlertDisplaySettings() {
    // Set default sort order: severity + alphabetical
    localStorage.setItem('alertSortOrder', 'bySeverityThenAlpha');
}

/* ==================== BACKUP RESTORE FUNCTION ==================== */

function restoreBackup(filename) {
    // Update modal with selected backup filename
    document.getElementById('restoreFilename').textContent = filename;
    document.getElementById('restoreFileInput').value = filename;
    
    // Show the restore modal
    const modal = new bootstrap.Modal(document.getElementById('restoreModal'));
    modal.show();
}

</script>
@endsection
