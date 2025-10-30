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
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#servers"><i class="bi bi-server me-2"></i>Servers</button></li>
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
                    <input type="text" class="form-control bg-light" placeholder="Search logs by message, source, or user...">
                    <button class="btn btn-dark ms-2"><i class="bi bi-search me-1"></i>Search</button>
                </div>
                <small class="text-muted d-block mb-3">Logs are read-only and cannot be modified.</small>
                <div class="bg-light rounded p-3">
                    <code>[2025-10-21 19:42:11] INFO: System backup completed successfully.</code><br>
                    <code>[2025-10-21 19:20:14] ERROR: Database connection timeout.</code><br>
                    <code>[2025-10-21 19:15:08] INFO: Server reboot scheduled for maintenance.</code>
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

                <table class="table table-bordered align-middle" id="criticalTable">
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
                        <tr>
                            <td>192.168.1.10</td>
                            <td>00:1B:44:11:AA:00</td>
                            <td>Emergency Services</td>
                            <td><span class="badge badge-online">Online</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary edit-btn"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger delete-btn"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
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

        {{-- SERVERS --}}
        <div class="tab-pane fade" id="servers" role="tabpanel">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-semibold mb-3">Servers</h5>
                <p class="text-muted small">Manage system servers and ports.</p>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addServerModal">
                        <i class="bi bi-plus-lg me-2"></i>Add Server
                    </button>
                </div>

                <table class="table table-bordered align-middle" id="serverTable">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>IP Address</th>
                            <th>Port</th>
                            <th>Status</th>
                            <th style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Primary Server</td>
                            <td>192.168.1.10</td>
                            <td>22</td>
                            <td><span class="badge badge-online">Online</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary edit-btn"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger delete-btn"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>Database Server</td>
                            <td>192.168.1.12</td>
                            <td>3306</td>
                            <td><span class="badge badge-offline">Offline</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary edit-btn"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger delete-btn"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- USERS --}}
        <div class="tab-pane fade" id="users" role="tabpanel">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-semibold mb-3">User Management</h5>
                <p class="text-muted small">Manage system users, roles, and access permissions.</p>

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
                            <th>Status</th>
                            <th style="width:120px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Admin</td>
                            <td>admin@uprm.edu</td>
                            <td>Admin</td>
                            <td><span class="badge badge-online">Active</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary edit-btn"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger delete-btn"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>Operator</td>
                            <td>operator@uprm.edu</td>
                            <td>User</td>
                            <td><span class="badge badge-online">Active</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary edit-btn"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger delete-btn"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>Guest</td>
                            <td>guest@uprm.edu</td>
                            <td>User</td>
                            <td><span class="badge badge-offline">Inactive</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary edit-btn"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger delete-btn"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
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
      <form id="formAddUser">
        <div class="modal-body">
            <div class="mb-3">
                <label class="form-label fw-semibold">Name</label>
                <input type="text" class="form-control" id="user_name" placeholder="Full name" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" class="form-control" id="user_email" placeholder="example@uprm.edu" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Role</label>
                <select class="form-select" id="user_role">
                    <option>Admin</option>
                    <option selected>User</option>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Status</label>
                <select class="form-select" id="user_status">
                    <option selected>Active</option>
                    <option>Inactive</option>
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

{{-- ===================== UNIVERSAL JS ===================== --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    ['criticalTable','serverTable','userTable'].forEach(enableTableActions);

    document.getElementById('formAddCritical').addEventListener('submit', onAddCritical);
    document.getElementById('formAddServer').addEventListener('submit', onAddServer);
    document.getElementById('formAddUser').addEventListener('submit', onAddUser);
});

function enableTableActions(tableId) {
    const tbody = document.querySelector('#' + tableId + ' tbody');
    if (!tbody) return;

    tbody.addEventListener('click', (e) => {
        const row = e.target.closest('tr');
        if (!row) return;

        // DELETE
        if (e.target.closest('.delete-btn')) {
            if (confirm('🗑️ Delete this entry?')) row.remove();
            return;
        }

        // EDIT
        if (e.target.closest('.edit-btn')) {
            const actionCell = row.lastElementChild;
            const cells = [...row.children].slice(0, -1);
            cells.forEach((cell, index) => {
                const value = cell.textContent.trim();

                // Status column. Make it a select
                if (cell.innerText.includes('Online') || cell.innerText.includes('Offline')) {
                    const select = document.createElement('select');
                    select.className = 'form-select form-select-sm';
                    select.innerHTML = `
                        <option value="Online" ${value.includes('Online') ? 'selected' : ''}>Online</option>
                        <option value="Offline" ${value.includes('Offline') ? 'selected' : ''}>Offline</option>`;
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
            cells.forEach(cell => {
                const input = cell.querySelector('input');
                const select = cell.querySelector('select');

                if (select) {
                    const status = select.value;
                    const badgeClass = status === 'Online' ? 'badge-online' : 'badge-offline';
                    cell.innerHTML = `<span class="badge ${badgeClass}">${status}</span>`;
                } else if (input) {
                    cell.textContent = input.value;
                }
            });

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
        <tr>
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

function onAddUser(ev) {
    ev.preventDefault();
    const name = document.getElementById('user_name').value.trim();
    const email = document.getElementById('user_email').value.trim();
    const role = document.getElementById('user_role').value;
    const status = document.getElementById('user_status').value;

    const tbody = document.querySelector('#userTable tbody');
    const badge = (status === 'Active') ? 'badge-online' : 'badge-offline';
    tbody.insertAdjacentHTML('beforeend', `
        <tr>
            <td>${name}</td>
            <td>${email}</td>
            <td>${role}</td>
            <td><span class="badge ${badge}">${status}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-secondary edit-btn"><i class="bi bi-pencil"></i></button>
                <button class="btn btn-sm btn-danger delete-btn"><i class="bi bi-trash"></i></button>
            </td>
        </tr>
    `);
    bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
    ev.target.reset();
}
</script>
@endsection
