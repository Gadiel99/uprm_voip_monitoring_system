{{--
/*
 * File: devices.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Device management interface with building overview and device details
 * 
 * Author: [Hector R. Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   This page provides a two-level interface for device management:
 *   Level 1: Building overview with device statistics
 *   Level 2: Detailed device listing per building
 *   Level 3: Individual device activity graphs (modal)
 * 
 * Features:
 *   - Building overview table showing device counts (total/online/offline)
 *   - Click-through to view all devices in a building
 *   - Device information: ID, User, Phone, MAC, IP
 *   - 30-day activity graph using Chart.js
 *   - Inline editing capabilities
 *   - Return navigation between views
 * 
 * Data Display:
 *   Buildings Table:
 *     - Building name
 *     - Total device count
 *     - Online device count
 *     - Offline device count
 *   
 *   Devices Table:
 *     - Device ID
 *     - Assigned user
 *     - Phone number
 *     - MAC address
 *     - IP address
 * 
 * Graphs:
 *   - Type: Line chart
 *   - X-axis: Days 1-30 (current month)
 *   - Y-axis: Binary (0=Inactive, 1=Active)
 *   - Point colors: Green (active), Red (inactive)
 *   - Library: Chart.js 3.x
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3
 *   - Chart.js (CDN)
 *   - Bootstrap Icons
 * 
 * Frontend Data:
 *   - Static demo data stored in JavaScript object
 *   - No backend database connection
 *   - Client-side navigation and filtering
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 1016 software design description
 *   - Adheres to IEEE 829 test documentation standards
 */
--}}
@extends('components.layout.app')

@section('content')
<style>
    /* Card styling with rounded corners and soft shadow */
    .card {
        border-radius: 12px;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }

    /* Highlight table rows on hover */
    .table-hover tbody tr:hover {
        background-color: #f1f3f4;
        cursor: pointer;
    }

    /* Online badge styling */
    .badge-online {
        background-color: #e6f9ed;
        color: #00844b;
    }

    /* Offline badge styling */
    .badge-offline {
        background-color: #fdeaea;
        color: #c82333;
    }

    /* Small hint text for clickable rows */
    .click-hint {
        color: #00844b;
        font-weight: 600;
    }
</style>

<div class="container-fluid">
    <h4 class="fw-semibold mb-4">Device Management</h4>

    {{-- TABLE: BUILDINGS OVERVIEW --}}
    {{-- Shows a summary of all buildings and their device counts --}}
    <div id="buildingOverview">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-semibold mb-3">Buildings Overview</h5>
            <p class="text-muted mb-3">Select a building to view all connected devices and their graphs.</p>

            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Building</th>
                        <th>Total Devices</th>
                        <th>Online</th>
                        <th>Offline</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Example buildings with sample data --}}
                    <tr onclick="showBuildingDevices('Stefani')">
                        <td><i class="bi bi-building me-2 text-success"></i> Stefani</td>
                        <td>155</td>
                        <td>140</td>
                        <td>15</td>
                    </tr>
                    <tr onclick="showBuildingDevices('General Library')">
                        <td><i class="bi bi-building me-2 text-warning"></i> General Library</td>
                        <td>40</td>
                        <td>35</td>
                        <td>5</td>
                    </tr>
                    <tr onclick="showBuildingDevices('Student Center')">
                        <td><i class="bi bi-building me-2 text-success"></i> Student Center</td>
                        <td>30</td>
                        <td>30</td>
                        <td>0</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    {{-- TABLE: DEVICES PER BUILDING --}}
    {{-- Displays all devices for the selected building --}}
    <div id="buildingDevices" class="d-none">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    {{-- Title will dynamically show building name --}}
                    <h5 class="fw-semibold mb-0" id="buildingTitle">Building Devices</h5>
                    <small class="click-hint">(Click row to view graph)</small>
                </div>
                {{-- Button to go back to overview --}}
                <button class="btn btn-outline-secondary btn-sm" onclick="goBack()">
                    <i class="bi bi-arrow-left me-1"></i> Return
                </button>
            </div>

            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Device ID</th>
                        <th>Assigned User</th>
                        <th>Phone Number</th>
                        <th>MAC Address</th>
                        <th>IP Address</th>
                    </tr>
                </thead>
                <tbody id="deviceTableBody">
                    {{-- Device rows will be populated dynamically via JS --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- MODAL: DEVICE GRAPH --}}
{{-- Modal displays activity chart for selected device --}}
<div class="modal fade" id="deviceGraphModal" tabindex="-1" aria-labelledby="deviceGraphModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        {{-- Device title dynamically updated --}}
        <h5 class="modal-title" id="deviceGraphModalLabel">Device Activity</h5>
      </div>
      <div class="modal-body">
        {{-- Chart.js canvas --}}
        <canvas id="deviceActivityChart" height="100"></canvas>
      </div>
      <div class="modal-footer">
        {{-- Close modal --}}
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-arrow-left me-1"></i> Return
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Chart.js library --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
/* Sample device data for each building */
const buildingDevicesData = {
    "Stefani": [
        { id: "DEV-1001", user: "admin", phone: "787-555-0101", mac: "00:1B:44:11:3A:B7", ip: "192.168.1.10" },
        { id: "DEV-1002", user: "jdoe", phone: "787-555-0102", mac: "00:1B:44:11:3A:B8", ip: "192.168.1.11" },
        { id: "DEV-1003", user: "msmith", phone: "787-555-0103", mac: "00:1B:44:11:3A:B9", ip: "192.168.1.12" }
    ],
    "General Library": [
        { id: "DEV-2001", user: "jsantos", phone: "787-555-0201", mac: "00:1B:44:11:4A:11", ip: "192.168.2.10" },
        { id: "DEV-2002", user: "acastro", phone: "787-555-0202", mac: "00:1B:44:11:4A:12", ip: "192.168.2.11" }
    ],
    "Student Center": [
        { id: "DEV-3001", user: "drios", phone: "787-555-0301", mac: "00:1B:44:11:5A:21", ip: "192.168.3.10" }
    ]
};

let chartInstance = null;

/* Show all devices for the selected building */
function showBuildingDevices(name) {
    // Hide overview and show devices table
    document.getElementById('buildingOverview').classList.add('d-none');
    document.getElementById('buildingDevices').classList.remove('d-none');

    // Update building title
    document.getElementById('buildingTitle').innerText = name + " — Devices";

    // Clear previous device rows
    const tbody = document.getElementById('deviceTableBody');
    tbody.innerHTML = '';

    // Add device rows dynamically
    buildingDevicesData[name].forEach(device => {
        tbody.innerHTML += `
            <tr onclick="openDeviceGraph('${device.id}')">
                <td>${device.id}</td>
                <td>${device.user}</td>
                <td>${device.phone}</td>
                <td>${device.mac}</td>
                <td>${device.ip}</td>
            </tr>`;
    });
}

/* Go back to the buildings overview */
function goBack() {
    document.getElementById('buildingDevices').classList.add('d-none');
    document.getElementById('buildingOverview').classList.remove('d-none');
}

/* Open modal and display device activity chart */
function openDeviceGraph(deviceId) {
    const modal = new bootstrap.Modal(document.getElementById('deviceGraphModal'));

    // Display current month in modal title
    const currentMonth = new Date().toLocaleString('default', { month: 'long', year: 'numeric' });
    document.getElementById('deviceGraphModalLabel').innerText = `Device Activity — ${deviceId} (${currentMonth})`;
    modal.show();

    // Generate sample labels (days 1–30) and sample data (all inactive)
    const labels = Array.from({length: 30}, (_, i) => i + 1);
    const data = Array.from({length: 30}, () => 0); // All inactive
    const pointColors = data.map(v => v === 0 ? 'red' : '#00844b');

    // Destroy previous chart instance to avoid duplication
    if (chartInstance) chartInstance.destroy();

    // Initialize new Chart.js line chart
    const ctx = document.getElementById('deviceActivityChart').getContext('2d');
    chartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Active (1) / Inactive (0)',
                data: data,
                borderColor: '#00844b',
                borderWidth: 2,
                pointBackgroundColor: pointColors,
                fill: false,
                tension: 0
            }]
        },
        options: {
            animation: false, // Disable animations for faster rendering
            plugins: {
                legend: { display: false } // Hide legend
            },
            scales: {
                y: {
                    min: 0,
                    max: 1,
                    ticks: { stepSize: 1 } // Only show 0 or 1
                },
                x: {
                    title: { display: true, text: 'Days (1–30)' } // X-axis label
                }
            }
        }
    });
}
</script>
@endsection
