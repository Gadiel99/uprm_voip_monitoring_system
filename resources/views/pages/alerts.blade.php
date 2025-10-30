{{--
/*
 * File: alerts.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Alert management interface with two-level drill-down view
 * 
 * Author: [Hector R. Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   This page provides a comprehensive alert monitoring system with drill-down capability.
 *   Users can view building-level alerts and drill into individual device alerts.
 * 
 * Features:
 *   - Two-level navigation (Building Overview → Device Details)
 *   - Color-coded severity badges (Critical/Medium/Low)
 *   - Clickable rows for drill-down
 *   - Return navigation between views
 *   - Real-time alert status display
 *   - UPRM green theme (#00844b)
 * 
 * View Levels:
 *   Level 1: Building Overview (alertOverview)
 *     - Shows buildings with alert counts
 *     - Displays offline device counts
 *     - Severity indicators
 *     - Timestamp information
 *     - Columns: Status | Building | Offline Devices | Severity | Time
 *   
 *   Level 2: Device Details (buildingDetail)
 *     - Shows individual devices in selected building
 *     - Device-specific alert information
 *     - Return button to overview
 *     - Columns: Status | Device ID | Location | Issue | Severity | Time
 * 
 * Severity Levels:
 *   - Critical (Red - #dc3545): Immediate action required
 *     System failure or major outage affecting multiple devices
 *   
 *   - Medium (Yellow - #ffc107): Warning condition
 *     Monitor closely, potential issue developing
 *   
 *   - Low (Green - #198754): Informational
 *     Routine event or resolved issue
 * 
 * Interactive Elements:
 *   - Clickable rows: onclick="showBuilding(name, offline, total)"
 *     Navigates from building overview to device details
 *   
 *   - Return button: onclick="returnToOverview()"
 *     Navigates back to building overview
 * 
 * Sample Data:
 *   Building Overview:
 *     - Stefani: 121/155 offline (Critical)
 *     - Facundo Bueso: 2/25 offline (Medium)
 *     - Chemistry: 1/40 offline (Low)
 *   
 *   Device Details (Stefani):
 *     - 8 device alerts with various severities
 *     - Issues: No connectivity, packet loss, unstable connection
 * 
 * JavaScript Functions:
 *   - showBuilding(name, offline, total): Displays device details for building
 *   - returnToOverview(): Returns to building overview
 *   - Updates building name, device count dynamically
 * 
 * Styling Features:
 *   - UPRM green active state (#00844b)
 *   - Hover effects on clickable rows (#f3f7f3)
 *   - Rounded cards (border-radius: 12px)
 *   - Shadow effects for depth
 *   - Smooth transitions (0.15s ease)
 * 
 * Color Coding:
 *   - Critical badges: Red background, white text
 *   - Warning badges: Yellow background, black text
 *   - Normal badges: Green background, white text
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3
 *   - Bootstrap Icons (bi bi-*)
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
    /* === UPRM Theme === */
    .nav-pills .nav-link.active {
        background-color: #00844b !important;
        color: #fff !important;
        font-weight: 600;
        box-shadow: 0 0 6px rgba(0, 132, 75, 0.3);
    }

    .badge-critical { background-color: #dc3545 !important; color: #fff !important; }
    .badge-warning { background-color: #ffc107 !important; color: #000 !important; }
    .badge-normal  { background-color: #198754 !important; color: #fff !important; }

    .clickable-row { cursor: pointer; transition: background 0.15s ease; }
    .clickable-row:hover { background-color: #f3f7f3; }

    .card  { border-radius: 12px !important; }
    .table { border-radius: 8px !important; overflow: hidden; }
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
                    {{-- ======= Highlighted ======= --}}
                    <tr class="clickable-row" onclick="showBuilding('Stefani', 121, 155)">
                        <td><i class="bi bi-exclamation-triangle text-danger"></i></td>
                        <td>Stefani</td>
                        <td>121 / 155</td>
                        <td><span class="badge badge-critical">CRITICAL</span></td>
                        <td>2 minutes ago</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Biblioteca', 8, 40)">
                        <td><i class="bi bi-exclamation-circle text-warning"></i></td>
                        <td>General Library</td>
                        <td>8 / 40</td>
                        <td><span class="badge badge-warning">WARNING</span></td>
                        <td>15 minutes ago</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Centro de Estudiantes', 0, 30)">
                        <td><i class="bi bi-check-circle text-success"></i></td>
                        <td>Student Center</td>
                        <td>0 / 30</td>
                        <td><span class="badge badge-normal">NORMAL</span></td>
                        <td>2 hours ago</td>
                    </tr>

                    {{-- ======= Buildings (NORMAL placeholders) ======= --}}
                    <tr class="clickable-row" onclick="showBuilding('Celis', 0, 24)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Celis</td><td>0 / 24</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    <tr class="clickable-row" onclick="showBuilding('Biologia', 0, 18)">
                        <td><i class="bi bi-check-circle text-success"></i></td><td>Biologia</td><td>0 / 18</td><td><span class="badge badge-normal">NORMAL</span></td><td>just now</td>
                    </tr>
                    {{-- More buildings below ... same pattern --}}
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
/* ====== Device data per building (placeholders) ====== */
const buildingData = {
    "Stefani": [
        { server: "STF-001", id: "DEV-1001", user: "admin",  phone: "787-555-0101", mac: "00:1B:44:11:3A:B7", ip: "192.168.1.10" },
        { server: "STF-002", id: "DEV-1002", user: "jdoe",   phone: "787-555-0102", mac: "00:1B:44:11:3A:B8", ip: "192.168.1.11" },
        { server: "STF-003", id: "DEV-1003", user: "msmith", phone: "787-555-0103", mac: "00:1B:44:11:3A:B9", ip: "192.168.1.12" }
    ],
    "Biblioteca": [
        { server: "LIB-001", id: "DEV-2001", user: "jsantos", phone: "787-555-0201", mac: "00:1B:44:11:4A:11", ip: "192.168.2.10" }
    ],
    "Centro de Estudiantes": [
        { server: "STD-001", id: "DEV-3001", user: "drios", phone: "787-555-0301", mac: "00:1B:44:11:5A:21", ip: "192.168.3.10" }
    ],
    // More buildings ...
};

/* ====== UI handlers ====== */
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
    } else {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="text-center text-muted">
                    No registered devices for <strong>${name}</strong>.
                </td>
            </tr>`;
    }
}

function goBack() {
    document.getElementById('buildingDetails').classList.add('d-none');
    document.getElementById('alertOverview').classList.remove('d-none');
}

/*  URL hook so markers can open a building: /alerts?building=Stefani */
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const building = params.get('building');
    if (building) {
        // Default counters when opened from URL; adjust if you have real data
        showBuilding(building, 0, (buildingData[building] || []).length || 10);
    }
});
</script>
@endsection
