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
</style>

<div class="container-fluid">
    <h4 class="fw-semibold mb-4">Admin Panel</h4>

    {{-- SUB-TABS con Bootstrap (verde institucional) --}}
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

    {{-- CONTENIDO DE LOS SUB-TABS --}}
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
                    <button class="btn btn-success">
                        <i class="bi bi-archive me-2"></i> Create Backup & Download (ZIP)
                    </button>
                    <button class="btn btn-outline-secondary">
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
                <p class="text-muted mb-0">Recent activity and events:</p>
                <div class="bg-light rounded p-3 mt-3">
                    <code>[2025-10-21 19:42:11] INFO: System backup completed successfully.</code><br>
                    <code>[2025-10-21 19:40:05] WARNING: CPU usage exceeded 85% threshold.</code><br>
                    <code>[2025-10-21 19:32:47] INFO: New user “admin2” created.</code>
                </div>
            </div>
        </div>

        {{-- SETTINGS --}}
        <div class="tab-pane fade" id="settings" role="tabpanel" aria-labelledby="settings-tab">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-semibold mb-3">Admin Settings</h5>
                <p class="text-muted">Manage alert thresholds, critical phones, and notification frequency for system monitoring.</p>

                {{-- Critical Phones Section --}}
                <h6 class="fw-semibold mt-4">Critical Phones</h6>
                <p class="text-muted">These phones will trigger alerts when not responding, regardless of building trigger.</p>
                <button class="btn btn-success mb-3">
                    <i class="bi bi-plus-lg me-2"></i>Add Critical Phone
                </button>

                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Phone</th>
                            <th>MAC Address</th>
                            <th>Extension</th>
                            <th>Description</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>787-555-0100</td>
                            <td><code>00:1B:44:11:AA:00</code></td>
                            <td>1000</td>
                            <td>Emergency Services</td>
                            <td class="text-center">
                                <button class="btn btn-outline-secondary btn-sm me-1"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>787-555-0200</td>
                            <td><code>00:1B:44:11:AA:01</code></td>
                            <td>1001</td>
                            <td>Security Office</td>
                            <td class="text-center">
                                <button class="btn btn-outline-secondary btn-sm me-1"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>787-555-0300</td>
                            <td><code>00:1B:44:11:AA:02</code></td>
                            <td>1002</td>
                            <td>IT Operations Center</td>
                            <td class="text-center">
                                <button class="btn btn-outline-secondary btn-sm me-1"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <hr class="my-4">

                {{-- Alert Thresholds Section --}}
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
                <div class="bg-light rounded p-3 mb-3">
                    <p class="fw-semibold mb-1 text-dark">At Event Start:</p>
                    <p class="text-muted mb-0">1 alert every 5 minutes (3 times)</p>
                </div>
                <div class="bg-light rounded p-3 mb-3">
                    <p class="fw-semibold mb-1 text-dark">During Ongoing Issue:</p>
                    <p class="text-muted mb-0">1 alert every hour until resolution</p>
                </div>
                <div class="bg-light rounded p-3">
                    <p class="fw-semibold mb-1 text-dark">At Resolution:</p>
                    <p class="text-muted mb-0">1 alert at the moment of resolution</p>
                </div>
            </div>
        </div>

        {{-- SERVERS --}}
        <div class="tab-pane fade" id="servers" role="tabpanel" aria-labelledby="servers-tab">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-semibold mb-3">Registered Servers</h5>
                <table class="table table-bordered table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Server Name</th>
                            <th>Status</th>
                            <th>Last Check-In</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td>01</td><td>Database Node</td><td><span class="badge bg-success">Online</span></td><td>2 mins ago</td></tr>
                        <tr><td>02</td><td>Web API Node</td><td><span class="badge bg-warning text-dark">Warning</span></td><td>5 mins ago</td></tr>
                        <tr><td>03</td><td>Backup Node</td><td><span class="badge bg-danger">Offline</span></td><td>10 mins ago</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- USERS --}}
        <div class="tab-pane fade" id="users" role="tabpanel" aria-labelledby="users-tab">
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h5 class="fw-semibold mb-3">User Management</h5>
                <p class="text-muted">Manage user roles and access levels.</p>
                <button class="btn btn-success mb-3">
                    <i class="bi bi-person-plus me-2"></i> Add New User
                </button>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Admin</td>
                            <td>admin@uprm.edu</td>
                            <td>Superuser</td>
                            <td><button class="btn btn-sm btn-outline-secondary">Edit</button></td>
                        </tr>
                        <tr>
                            <td>Operator</td>
                            <td>operator@uprm.edu</td>
                            <td>Standard</td>
                            <td><button class="btn btn-sm btn-outline-secondary">Edit</button></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
