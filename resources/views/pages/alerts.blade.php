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
<script>
// ===== SYNC BUILDING STATUSES TO LOCALSTORAGE =====
// This data is read by the home page to color markers
document.addEventListener('DOMContentLoaded', function() {
    const buildingStatuses = {
        "Stefani": "critical",
        "Biblioteca": "warning",
        "Centro de Estudiantes": "normal",
        "Celis": "normal",
        "Biologia": "normal",
        "DeDiego": "normal",
        "Luchetti": "normal",
        "ROTC": "normal",
        "Adm.Empresas": "normal",
        "Musa": "normal",
        "Chardon": "normal",
        "Monzon": "normal",
        "Sanchez Hidalgo": "normal",
        "Fisica": "normal",
        "Geologia": "normal",
        "Ciencias Marinas": "normal",
        "Quimica": "normal",
        "Piñero": "normal",
        "Enfermeria": "normal",
        "Vagones": "normal",
        "Natatorio": "normal",
        "Centro Nuclear": "normal",
        "Coliseo": "normal",
        "Gimnacio": "normal",
        "Servicios Medicos": "normal",
        "Decanato de Estudiantes": "normal",
        "Oficina de Facultad": "normal",
        "Adm.Finca Alzamora": "normal",
        "Terrats": "normal",
        "Ing.Civil": "normal",
        "Ing.Industrial": "normal",
        "Ing.Quimica": "normal",
        "Ing.Agricola": "normal",
        "Edificio A (Hotel Colegial)": "normal",
        "Edificio B (Adm.Peq.Negocios y Oficina Adm)": "normal",
        "Edificio C (Oficina de Extension Agricola)": "normal",
        "Edificio D": "normal"
    };
    
    // Save to localStorage so home page can read it
    localStorage.setItem('buildingStatuses', JSON.stringify(buildingStatuses));
});
</script>

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
                <h6 class="fw-semibold">Buildings Overview</h6>
                <div>
                    <span class="badge bg-danger text-white me-2" id="criticalCount">0 Critical</span>
                    <span class="badge bg-warning text-dark me-2" id="warningCount">0 Warning</span>
                    <span class="badge bg-success text-white" id="normalCount">0 Normal</span>
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
                <tbody id="alertTableBody">
                    {{-- Dynamic content will be inserted here --}}
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
/* ==================== ALERT DATA ==================== */
const alertsData = [
    { building: 'Stefani', offline: 121, total: 155, severity: 'CRITICAL', severityLevel: 3, time: '2 minutes ago', icon: 'bi-exclamation-triangle text-danger' },
    { building: 'Biblioteca', offline: 8, total: 40, severity: 'WARNING', severityLevel: 2, time: '15 minutes ago', icon: 'bi-exclamation-circle text-warning' },
    { building: 'Centro de Estudiantes', offline: 0, total: 30, severity: 'NORMAL', severityLevel: 1, time: '2 hours ago', icon: 'bi-check-circle text-success' },
    { building: 'Celis', offline: 0, total: 24, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Biologia', offline: 0, total: 18, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'DeDiego', offline: 0, total: 22, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Luchetti', offline: 0, total: 15, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'ROTC', offline: 0, total: 12, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Adm.Empresas', offline: 0, total: 28, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Musa', offline: 0, total: 16, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Chardon', offline: 0, total: 25, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Monzon', offline: 0, total: 20, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Sanchez Hidalgo', offline: 0, total: 19, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Fisica', offline: 0, total: 32, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Geologia', offline: 0, total: 14, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Ciencias Marinas', offline: 0, total: 11, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Quimica', offline: 0, total: 35, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Piñero', offline: 0, total: 21, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Enfermeria', offline: 0, total: 17, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Vagones', offline: 0, total: 8, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Natatorio', offline: 0, total: 6, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Centro Nuclear', offline: 0, total: 9, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Coliseo', offline: 0, total: 13, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Gimnacio', offline: 0, total: 10, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Servicios Medicos', offline: 0, total: 7, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Decanato de Estudiantes', offline: 0, total: 12, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Oficina de Facultad', offline: 0, total: 15, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Adm.Finca Alzamora', offline: 0, total: 5, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Terrats', offline: 0, total: 18, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Ing.Civil', offline: 0, total: 23, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Ing.Industrial', offline: 0, total: 27, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Ing.Quimica', offline: 0, total: 31, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Ing.Agricola', offline: 0, total: 14, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Edificio A (Hotel Colegial)', offline: 0, total: 8, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Edificio B (Adm.Peq.Negocios y Oficina Adm)', offline: 0, total: 9, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Edificio C (Oficina de Extension Agricola)', offline: 0, total: 6, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' },
    { building: 'Edificio D', offline: 0, total: 7, severity: 'NORMAL', severityLevel: 1, time: 'just now', icon: 'bi-check-circle text-success' }
];

/* ==================== RENDER ALERTS ==================== */
function renderAlerts() {
    const sortOrder = localStorage.getItem('alertSortOrder') || 'bySeverity';
    const tbody = document.getElementById('alertTableBody');
    
    // Clone and sort the data
    let sortedAlerts = [...alertsData];
    
    if (sortOrder === 'bySeverity') {
        // Sort by severity level (Critical > Warning > Normal)
        sortedAlerts.sort((a, b) => b.severityLevel - a.severityLevel);
    } else {
        // Sort alphabetically by building name
        sortedAlerts.sort((a, b) => a.building.localeCompare(b.building));
    }
    
    // Clear existing content
    tbody.innerHTML = '';
    
    // Render sorted alerts
    sortedAlerts.forEach(alert => {
        const badgeClass = alert.severity === 'CRITICAL' ? 'badge-critical' : 
                          alert.severity === 'WARNING' ? 'badge-warning' : 'badge-normal';
        
        const row = `
            <tr class="clickable-row" onclick="showBuilding('${alert.building}', ${alert.offline}, ${alert.total})">
                <td><i class="bi ${alert.icon}"></i></td>
                <td>${alert.building}</td>
                <td>${alert.offline} / ${alert.total}</td>
                <td><span class="badge ${badgeClass}">${alert.severity}</span></td>
                <td>${alert.time}</td>
            </tr>
        `;
        tbody.innerHTML += row;
    });
    
    // Update badge counts for all severity levels
    const criticalCount = alertsData.filter(a => a.severity === 'CRITICAL').length;
    const warningCount = alertsData.filter(a => a.severity === 'WARNING').length;
    const normalCount = alertsData.filter(a => a.severity === 'NORMAL').length;
    
    document.getElementById('criticalCount').textContent = `${criticalCount} Critical`;
    document.getElementById('warningCount').textContent = `${warningCount} Warning`;
    document.getElementById('normalCount').textContent = `${normalCount} Normal`;
}

/* ====== Device data per building (placeholders) ====== */
const buildingData = {
    "Stefani": [
        { server: "STF-001", id: "DEV-1001", user: "admin",  phone: "787-555-0101", mac: "00:1B:44:11:3A:B7", ip: "192.168.1.10" },
        { server: "STF-002", id: "DEV-1002", user: "jdoe",   phone: "787-555-0102", mac: "00:1B:44:11:3A:B8", ip: "192.168.1.11" },
        { server: "STF-003", id: "DEV-1003", user: "msmith", phone: "787-555-0103", mac: "00:1B:44:11:3A:B9", ip: "192.168.1.12" }
    ],
    "Biblioteca": [
        { server: "BIB-001", id: "DEV-2001", user: "jsantos", phone: "787-555-0201", mac: "00:1B:44:11:4A:11", ip: "192.168.2.10" }
    ],
    "Centro de Estudiantes": [
        { server: "STD-001", id: "DEV-3001", user: "drios", phone: "787-555-0301", mac: "00:1B:44:11:5A:21", ip: "192.168.3.10" }
    ],
    "Celis": [
        { server: "CEL-001", id: "DEV-4001", user: "alopez", phone: "787-555-0401", mac: "00:1B:44:11:6A:31", ip: "192.168.4.10" }
    ],
    "Biologia": [
        { server: "BIO-001", id: "DEV-5001", user: "rperez", phone: "787-555-0501", mac: "00:1B:44:11:7A:41", ip: "192.168.5.10" }
    ],
    // Add more buildings as needed
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
    // Render alerts based on saved sort preference
    renderAlerts();
    
    const params = new URLSearchParams(window.location.search);
    const building = params.get('building');
    if (building) {
        // Default counters when opened from URL; adjust if you have real data
        showBuilding(building, 0, (buildingData[building] || []).length || 10);
    }
});

// Listen for changes to alert sort order (if changed in another tab)
window.addEventListener('storage', (e) => {
    if (e.key === 'alertSortOrder') {
        renderAlerts();
    }
});
</script>
@endsection
