@extends('components.layout.app')

@section('content')
<style>
    /* === UPRM Theme === */
    .nav-pills .nav-link.active {
        background-color: #00844b !important;
        color: #fff !important;
        font-weight: 600;
        box-shadow: 0 0 6px rgba(0, 132, 75, 0.3);
    }

    .badge-critical {
        background-color: #dc3545 !important;
        color: #fff !important;
    }
    .badge-warning {
        background-color: #ffc107 !important;
        color: #000 !important;
    }
    .badge-normal {
        background-color: #198754 !important;
        color: #fff !important;
    }

    .clickable-row {
        cursor: pointer;
        transition: background 0.15s ease;
    }
    .clickable-row:hover {
        background-color: #f3f7f3;
    }

    .card {
        border-radius: 12px !important;
    }
    .table {
        border-radius: 8px !important;
        overflow: hidden;
    }
</style>

<div class="container-fluid">
    <h4 class="fw-semibold mb-4">System Alerts</h4>

    {{-- ========== MAIN ALERT LIST VIEW ========== --}}
    <div id="alertOverview">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold">Critical Buildings</h6>
                <div>
                    <span class="badge bg-danger text-white me-2">1 Critical</span>
                </div>
            </div>

            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Status</th>
                        <th>Building</th>
                        <th>Offline Devices</th>
                        <th>Severity</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="clickable-row" onclick="showBuilding('Stefani', 121, 155)">
                        <td><i class="bi bi-exclamation-triangle text-danger"></i></td>
                        <td>Stefani</td>
                        <td>121 / 155</td>
                        <td><span class="badge badge-critical">CRITICAL</span></td>
                        <td>2 minutes ago</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('General Library', 8, 40)">
                        <td><i class="bi bi-exclamation-circle text-warning"></i></td>
                        <td>General Library</td>
                        <td>8 / 40</td>
                        <td><span class="badge badge-warning">WARNING</span></td>
                        <td>15 minutes ago</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Student Center', 0, 30)">
                        <td><i class="bi bi-check-circle text-success"></i></td>
                        <td>Student Center</td>
                        <td>0 / 30</td>
                        <td><span class="badge badge-normal">NORMAL</span></td>
                        <td>2 hours ago</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- ========== BUILDING DETAIL VIEW ========== --}}
    <div id="buildingDetails" class="d-none">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 id="buildingTitle" class="fw-semibold mb-0">Building Name</h5>
                    <small class="text-muted">Detailed device report</small>
                </div>
                <div class="d-flex align-items-center">
                    <span id="buildingCount" class="badge bg-secondary me-3">0 / 0</span>
                    <button class="btn btn-outline-secondary btn-sm" onclick="goBack()">
                        <i class="bi bi-arrow-left me-1"></i> Return
                    </button>
                </div>
            </div>

            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Server</th>
                        <th>ID</th>
                        <th>User</th>
                        <th>Phone</th>
                        <th>MAC Address</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody id="buildingTableBody">
                    {{-- Dynamic content --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    const buildingData = {
        "Stefani": [
            { server: "STF-001", id: "DEV-1001", user: "admin", phone: "787-555-0101", mac: "00:1B:44:11:3A:B7", ip: "192.168.1.10" },
            { server: "STF-002", id: "DEV-1002", user: "jdoe", phone: "787-555-0102", mac: "00:1B:44:11:3A:B8", ip: "192.168.1.11" },
            { server: "STF-003", id: "DEV-1003", user: "msmith", phone: "787-555-0103", mac: "00:1B:44:11:3A:B9", ip: "192.168.1.12" },
            { server: "STF-004", id: "DEV-1004", user: "rjohnson", phone: "787-555-0104", mac: "00:1B:44:11:3A:C0", ip: "192.168.1.13" },
            { server: "STF-005", id: "DEV-1005", user: "admin", phone: "787-555-0105", mac: "00:1B:44:11:3A:C1", ip: "192.168.1.14" }
        ],
        "General Library": [
            { server: "LIB-001", id: "DEV-2001", user: "jsantos", phone: "787-555-0201", mac: "00:1B:44:11:4A:11", ip: "192.168.2.10" },
            { server: "LIB-002", id: "DEV-2002", user: "acastro", phone: "787-555-0202", mac: "00:1B:44:11:4A:12", ip: "192.168.2.11" }
        ],
        "Student Center": [
            { server: "STD-001", id: "DEV-3001", user: "drios", phone: "787-555-0301", mac: "00:1B:44:11:5A:21", ip: "192.168.3.10" }
        ]
    };

    function showBuilding(name, notWorking, total) {
        document.getElementById('alertOverview').classList.add('d-none');
        document.getElementById('buildingDetails').classList.remove('d-none');

        document.getElementById('buildingTitle').innerText = name;
        const count = document.getElementById('buildingCount');
        count.innerText = `${notWorking} / ${total}`;

        let colorClass = 'bg-success';
        if (notWorking > 0 && notWorking < 10) colorClass = 'bg-warning text-dark';
        else if (notWorking >= 10) colorClass = 'bg-danger';
        count.className = `badge ${colorClass} me-3`;

        const tbody = document.getElementById('buildingTableBody');
        tbody.innerHTML = '';

        if (buildingData[name]) {
            buildingData[name].forEach(device => {
                tbody.innerHTML += `
                    <tr>
                        <td>${device.server}</td>
                        <td>${device.id}</td>
                        <td>${device.user}</td>
                        <td>${device.phone}</td>
                        <td>${device.mac}</td>
                        <td>${device.ip}</td>
                    </tr>`;
            });
        }
    }

    function goBack() {
        document.getElementById('buildingDetails').classList.add('d-none');
        document.getElementById('alertOverview').classList.remove('d-none');
    }
</script>
@endsection
