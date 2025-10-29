@extends('components.layout.app')

@section('content')
<style>
    /* Estilo verde institucional */
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
    .nav-pills .nav-link:hover {
        background-color: #d7f5df;
    }
    .badge-online {
        background-color: #e6f9ed;
        color: #00844b;
    }
    .badge-offline {
        background-color: #fdeaea;
        color: #c82333;
    }

    /* === Consistent Button Styles === */
    .btn {
        transition: all 0.2s ease-in-out;
    }
    .btn-success {
        background-color: #00844b;
        border-color: #00844b;
    }
    .btn-success:hover {
        background-color: #006f3f;
        border-color: #006f3f;
    }
    .btn-outline-secondary:hover {
        background-color: #f1f3f4;
        color: #000;
    }
    .btn-danger:hover {
        background-color: #c82333;
        border-color: #c82333;
        color: #fff;
    }
    .btn-secondary:hover {
        background-color: #6c757d;
        color: #fff;
    }
</style>

<div class="container-fluid">
    <h4 class="fw-semibold mb-4">Admin Panel</h4>

    {{-- SUB-TABS --}}
    <ul class="nav nav-pills bg-light p-2 rounded mb-4" id="adminTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active d-flex align-items-center" id="backup-tab"
                data-bs-toggle="tab" data-bs-target="#backup" type="button" role="tab">
                <i class="bi bi-hdd-stack me-2"></i> Backup
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center" id="logs-tab"
                data-bs-toggle="tab" data-bs-target="#logs" type="button" role="tab">
                <i class="bi bi-file-earmark-text me-2"></i> Logs
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center" id="settings-tab"
                data-bs-toggle="tab" data-bs-target="#settings" type="button" role="tab">
                <i class="bi bi-gear me-2"></i> Settings
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center" id="servers-tab"
                data-bs-toggle="tab" data-bs-target="#servers" type="button" role="tab">
                <i class="bi bi-server me-2"></i> Servers
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link d-flex align-items-center" id="users-tab"
                data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">
                <i class="bi bi-people me-2"></i> Users
            </button>
        </li>
    </ul>

    {{-- TAB CONTENT --}}
    <div class="tab-content" id="adminTabContent">

        {{-- BACKUP --}}
        <div class="tab-pane fade show active" id="backup" role="tabpanel" aria-labelledby="backup-tab">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-semibold mb-3">Backup Configuration</h5>
                <div class="mb-3">
                    <label class="form-label fw-semibold">Local Backup Path</label>
                    <input type="text" class="form-control bg-light" value="/var/backups/monitoring" readonly>
                    <small class="text-muted">Configure where backups are stored locally</small>
                </div>
                <h5 class="fw-semibold mt-4 mb-3">Backup Operations</h5>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-success" aria-label="Create and Download Backup">
                        <i class="bi bi-archive me-2"></i> Create Backup & Download (ZIP)
                    </button>
                    <button class="btn btn-outline-secondary" aria-label="Restore Backup">
                        <i class="bi bi-upload me-2"></i> Restore from Backup
                    </button>
                </div>
                <small class="text-muted mt-2 d-block">
                    Backups are downloaded as ZIP files. Restoration will overwrite current system data.
                </small>
            </div>
        </div>

        {{-- LOGS --}}
        <div class="tab-pane fade" id="logs" role="tabpanel" aria-labelledby="logs-tab">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-semibold mb-3">System Logs</h5>

                {{-- Log Search --}}
                <div class="d-flex mb-3">
                    <input type="text" class="form-control bg-light" placeholder="Search logs by message, source, or user...">
                    <button class="btn btn-dark ms-2" aria-label="Search logs">
                        <i class="bi bi-search me-1"></i> Search
                    </button>
                </div>
                <small class="text-muted d-block mb-3">Note: Logs are read-only and cannot be altered</small>

                {{-- Example log entries --}}
                <div class="bg-light rounded p-3">
                    <code>[2025-10-21 19:42:11] INFO: System backup completed successfully.</code><br>
                    <code>[2025-10-21 19:40:05] WARNING: CPU usage exceeded 85% threshold.</code><br>
                    <code>[2025-10-21 19:32:47] INFO: New user “admin2” created.</code><br>
                    <code>[2025-10-21 19:20:14] ERROR: Database connection timeout on backup node.</code><br>
                    <code>[2025-10-21 19:15:08] INFO: Server reboot scheduled for maintenance.</code>
                </div>
            </div>
        </div>

        {{-- SETTINGS --}}
        <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-semibold mb-3">Admin Settings</h5>
                <p class="text-muted">Manage critical phones, alert thresholds, and alert frequency configuration.</p>

                {{-- Critical Phones --}}
                <h6 class="fw-semibold mt-3">Critical Phones</h6>
                <p class="text-muted">Phones that trigger alerts when not responding, regardless of building trigger.</p>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button class="btn btn-success px-3" data-bs-toggle="modal" data-bs-target="#addCriticalPhoneModal" aria-label="Add Critical Phone">
                        <i class="bi bi-plus-lg me-2"></i> Add Critical Phone
                    </button>
                    <div class="text-muted small">
                        <i class="bi bi-telephone-inbound me-1 text-success"></i> Only critical phones trigger bell alerts
                    </div>
                </div>

                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Phone</th>
                            <th>MAC Address</th>
                            <th>Extension</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>787-555-0100</td>
                            <td class="text-danger">00:1B:44:11:AA:00</td>
                            <td>1000</td>
                            <td>Emergency Services</td>
                            <td><span class="badge bg-success">Online</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary me-1" title="Edit" aria-label="Edit phone">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" title="Delete" aria-label="Delete phone">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr>
                            <td>787-555-0200</td>
                            <td class="text-danger">00:1B:44:11:AA:01</td>
                            <td>1001</td>
                            <td>Security Office</td>
                            <td><span class="badge bg-danger">Offline</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary me-1" title="Edit" aria-label="Edit phone">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-danger" title="Delete" aria-label="Delete phone">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>


                <hr class="my-4">

                {{-- Alert Thresholds --}}
                <h6 class="fw-semibold">Alert Thresholds</h6>
                <div class="row g-4 mt-2">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Green Status (Below)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" value="10">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Yellow Status (Between)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" value="10">
                            <span class="input-group-text">%</span>
                            <input type="number" class="form-control" value="25">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Red Status (Above)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" value="25">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- Alert Frequency Configuration --}}
                <h6 class="fw-semibold mb-3">Alert Frequency Configuration</h6>
                <form class="bg-light rounded p-4">
                    <div class="mb-4">
                        <label class="form-label fw-semibold">At Event Start</label>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Send every (minutes)</label>
                                <input type="number" class="form-control" value="5">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Repeat</label>
                                <select class="form-select">
                                    <option selected>3 times</option>
                                    <option>5 times</option>
                                    <option>Until resolved</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">During Ongoing Issue</label>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Send every (hours)</label>
                                <input type="number" class="form-control" value="1">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small text-muted">Until</label>
                                <select class="form-select">
                                    <option selected>Resolution</option>
                                    <option>Manual Stop</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">At Resolution</label>
                        <div class="row g-3 align-items-end">
                            <div class="col-md-6">
                                <label class="form-label small text-muted">Send alert</label>
                                <select class="form-select">
                                    <option selected>Immediately upon resolution</option>
                                    <option>After confirmation</option>
                                    <option>Disabled</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" class="btn btn-success">
                            <i class="bi bi-save me-2"></i>Save Configuration
                        </button>
                    </div>
                </form>
            </div>

         {{-- ALERT DISPLAY SETTINGS --}}
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h5 class="fw-semibold mb-3">Alert Display Settings</h5>
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
    </div>

    {{-- NOTIFICATION SETTINGS --}}
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h5 class="fw-semibold mb-3">Notification Settings</h5>

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
        <div class="tab-pane fade" id="servers" role="tabpanel" aria-labelledby="servers-tab">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-semibold mb-3">Server Management</h5>
                <p class="text-muted">Manage system servers, monitor their connection status, and configure ports.</p>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addServerModal">
                        <i class="bi bi-plus-lg me-2"></i> Add Server
                    </button>
                    <div class="text-muted small">
                        <i class="bi bi-hdd-network me-1 text-success"></i> Online servers are operational
                    </div>
                </div>

                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>IP Address</th>
                            <th>Port</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Primary Server</td>
                            <td>192.168.1.10</td>
                            <td>22</td>
                            <td><span class="badge bg-success-subtle text-success">Online</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary me-1" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>Backup Server</td>
                            <td>192.168.1.11</td>
                            <td>22</td>
                            <td><span class="badge bg-success-subtle text-success">Online</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary me-1" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>Database Server</td>
                            <td>192.168.1.12</td>
                            <td>3306</td>
                            <td><span class="badge bg-danger-subtle text-danger">Offline</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary me-1" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- USERS --}}
        <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-semibold mb-3">User Management</h5>
                <p class="text-muted">Manage system users, roles, and permissions.</p>

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-person-plus me-2"></i> Add New User
                    </button>
                    <div class="text-muted small">
                        <i class="bi bi-shield-lock me-1 text-success"></i> Admins: full access ·
                        <i class="bi bi-eye me-1 text-secondary"></i> Users: view only
                    </div>
                </div>

                <table class="table table-bordered align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><i class="bi bi-person-circle me-2 text-success"></i> Admin</td>
                            <td>admin@uprm.edu</td>
                            <td><span class="badge bg-success">Admin</span></td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary me-1" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-person-fill me-2 text-secondary"></i> Operator</td>
                            <td>operator@uprm.edu</td>
                            <td><span class="badge bg-secondary">User</span></td>
                            <td><span class="badge bg-success">Active</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary me-1" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td><i class="bi bi-person-fill me-2 text-muted"></i> Guest</td>
                            <td>guest@uprm.edu</td>
                            <td><span class="badge bg-secondary">User</span></td>
                            <td><span class="badge bg-danger">Inactive</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-secondary me-1" title="Edit"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-sm btn-danger" title="Delete"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Add Server --}}
<div class="modal fade" id="addServerModal" tabindex="-1" aria-labelledby="addServerModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="addServerModalLabel">
          <i class="bi bi-hdd-stack me-2"></i>Add Server
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="addServerForm">
          <div class="mb-3">
            <label class="form-label fw-semibold">Server Name</label>
            <input type="text" class="form-control" placeholder="Primary Server" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">IP Address</label>
            <input type="text" class="form-control" placeholder="192.168.1.10" required>
          </div>

          <div class="text-end mt-3">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-success" id="saveServerBtn">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Modal: Add User --}}
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="addUserModalLabel">
          <i class="bi bi-person-plus me-2"></i>Add New User
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="addUserForm">
          <div class="mb-3">
            <label class="form-label fw-semibold">Full Name</label>
            <input type="text" class="form-control" placeholder="Enter full name" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" class="form-control" placeholder="example@uprm.edu" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">Role</label>
            <select class="form-select">
              <option>Admin</option>
              <option>User</option>
            </select>
          </div>

          <div class="text-end mt-3">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-success" id="saveUserBtn">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Modal: Add Critical Phone --}}
<div class="modal fade" id="addCriticalPhoneModal" tabindex="-1" aria-labelledby="addCriticalPhoneModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="addCriticalPhoneModalLabel">
          <i class="bi bi-telephone-plus me-2"></i>Add Critical Phone
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="addCriticalPhoneForm">
          <div class="mb-3">
            <label class="form-label fw-semibold">Phone Number</label>
            <input type="text" class="form-control" placeholder="787-555-XXXX" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-semibold">MAC Address</label>
            <input type="text" class="form-control" placeholder="00:1B:44:11:AA:XX" required>
          </div>

          <div class="text-end mt-3">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-success" id="saveCriticalPhoneBtn">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Edit Modal --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title" id="editModalLabel">
          <i class="bi bi-pencil-square me-2"></i>Edit Entry
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <form>
          <div class="mb-3">
            <label class="form-label fw-semibold">Name</label>
            <input type="text" class="form-control" placeholder="Enter name">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" class="form-control" placeholder="Enter email">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Role</label>
            <select class="form-select">
              <option>Admin</option>
              <option>User</option>
            </select>
          </div>
          <div class="text-end">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-success" id="saveEditBtn">Save</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

{{-- Delete Confirmation Modal --}}
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content border-0 shadow-sm">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteModalLabel">
          <i class="bi bi-trash me-2"></i>Delete Confirmation
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <p class="fw-semibold mb-3">Are you sure you want to delete this entry?</p>
        <div class="text-end">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" id="confirmDeleteBtn">Delete</button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Scripts for modal logic --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
  // === Edit buttons ===
  document.querySelectorAll('.btn-outline-secondary').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      new bootstrap.Modal(document.getElementById('editModal')).show();
    });
  });

  // === Delete buttons ===
  document.querySelectorAll('.btn-danger').forEach(btn => {
    if (!btn.closest('#deleteModal')) { // avoid recursive trigger
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        new bootstrap.Modal(document.getElementById('deleteModal')).show();
      });
    }
  });

  // === Save edit (simulated) ===
  document.getElementById('saveEditBtn').addEventListener('click', () => {
    alert('✅ Changes saved (simulated)');
    bootstrap.Modal.getInstance(document.getElementById('editModal')).hide();
  });

  // === Confirm delete (simulated) ===
  document.getElementById('confirmDeleteBtn').addEventListener('click', () => {
    alert('🗑️ Entry deleted (simulated)');
    bootstrap.Modal.getInstance(document.getElementById('deleteModal')).hide();
  });

  // === Add modals (simulated save) ===
  document.getElementById('saveServerBtn').addEventListener('click', () => {
    alert('🖥️ New server added (simulated)');
    bootstrap.Modal.getInstance(document.getElementById('addServerModal')).hide();
    document.getElementById('addServerForm').reset();
  });

  document.getElementById('saveUserBtn').addEventListener('click', () => {
    alert('👤 New user added (simulated)');
    bootstrap.Modal.getInstance(document.getElementById('addUserModal')).hide();
    document.getElementById('addUserForm').reset();
  });

  document.getElementById('saveCriticalPhoneBtn').addEventListener('click', () => {
    alert('📞 New critical phone added (simulated)');
    bootstrap.Modal.getInstance(document.getElementById('addCriticalPhoneModal')).hide();
    document.getElementById('addCriticalPhoneForm').reset();
  });
});
</script>


@endsection
