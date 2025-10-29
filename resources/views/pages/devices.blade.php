@extends('components.layout.app')

@section('content')
<style>
    .card {
        border-radius: 12px;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    .table-hover tbody tr:hover {
        background-color: #f1f3f4;
        cursor: pointer;
    }
    .badge-online {
        background-color: #e6f9ed;
        color: #00844b;
    }
    .badge-offline {
        background-color: #fdeaea;
        color: #c82333;
    }
    .click-hint {
        color: #00844b;
        font-weight: 600;
    }
</style>

<div class="container-fluid">
    <h4 class="fw-semibold mb-4">Device Management</h4>

    {{-- TABLE: BUILDINGS OVERVIEW --}}
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
    <div id="buildingDevices" class="d-none">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-semibold mb-0" id="buildingTitle">Building Devices</h5>
                    <small class="click-hint">(Click row to view graph)</small>
                </div>
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
                    {{-- Populated dynamically --}}
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- === MODAL: DEVICE GRAPH === --}}
<div class="modal fade" id="deviceGraphModal" tabindex="-1" aria-labelledby="deviceGraphModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deviceGraphModalLabel">Device Activity</h5>
      </div>
      <div class="modal-body">
        <canvas id="deviceActivityChart" height="100"></canvas>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="bi bi-arrow-left me-1"></i> Return
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
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

function showBuildingDevices(name) {
    document.getElementById('buildingOverview').classList.add('d-none');
    document.getElementById('buildingDevices').classList.remove('d-none');
    document.getElementById('buildingTitle').innerText = name + " — Devices";

    const tbody = document.getElementById('deviceTableBody');
    tbody.innerHTML = '';

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

function goBack() {
    document.getElementById('buildingDevices').classList.add('d-none');
    document.getElementById('buildingOverview').classList.remove('d-none');
}

function openDeviceGraph(deviceId) {
    const modal = new bootstrap.Modal(document.getElementById('deviceGraphModal'));
    const currentMonth = new Date().toLocaleString('default', { month: 'long', year: 'numeric' });
    document.getElementById('deviceGraphModalLabel').innerText = `Device Activity — ${deviceId} (${currentMonth})`;
    modal.show();

    const labels = Array.from({length: 30}, (_, i) => i + 1);
    const data = Array.from({length: 30}, () => 0); // All inactive by default
    const pointColors = data.map(v => v === 0 ? 'red' : '#00844b');

    if (chartInstance) chartInstance.destroy();

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
            animation: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    min: 0,
                    max: 1,
                    ticks: { stepSize: 1 }
                },
                x: {
                    title: { display: true, text: 'Days (1–30)' }
                }
            }
        }
    });
}
</script>
@endsection
