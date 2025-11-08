{{--
/*
 * File: reports.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Device search and reporting interface with filtering capabilities
 * 
 * Author: [Hector R. Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   This page provides advanced search and filtering capabilities for device reports.
 *   Users can search by multiple criteria and view aggregated system statistics.
 * 
 * Features:
 *   - Multi-criteria search interface
 *   - Dynamic results table with color-coded status badges
 *   - System overview statistics cards
 *   - Real-time client-side filtering
 *   - Reset functionality to clear all filters
 * 
 * Search Filters:
 *   - User: Text search by assigned user name
 *   - MAC Address: Filter by network MAC address
 *   - IP Address: Filter by IP address
 *   - Status: Dropdown (Online/Offline/Critical)
 *   - Building: Dropdown selection from 8 buildings
 * 
 * Results Table Columns:
 *   - User (assigned user name)
 *   - MAC Address (network identifier)
 *   - IP Address (device IP)
 *   - Status (color-coded badge)
 *   - Building (location)
 * 
 * System Overview Cards:
 *   - Total Devices: Count of registered devices
 *   - Active Now: Currently online devices
 *   - Inactive: Offline devices  
 *   - Buildings: Total monitored locations
 * 
 * Status Badge Colors:
 *   - Online: Green (bg-success)
 *   - Offline: Red (bg-danger)
 *   - Critical: Yellow (bg-warning)
 * 
 * Data Handling:
 *   - Static demo dataset (6 sample devices)
 *   - Client-side JavaScript filtering
 *   - No backend API calls
 *   - Case-insensitive search matching
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3
 *   - Vanilla JavaScript (no external libraries)
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 1016 software design description
 *   - Adheres to IEEE 829 test documentation standards
 *   - Implements IEEE 730 quality assurance practices
 */
--}}
@extends('components.layout.app')

@section('content')
<div class="container-fluid">
    <h4 class="fw-semibold mb-4">Reports</h4>

    {{-- ================= REPORTS SEARCH FORM ================= --}}
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h5 class="fw-semibold mb-3">Device Reports Search</h5>

        {{-- Search Filters Form --}}
        <form id="searchForm">
            <div class="row g-3 align-items-end mb-3">
                {{-- User filter --}}
                <div class="col-md-4">
                    <label class="form-label">User</label>
                    <input type="text" id="searchUser" class="form-control bg-light" placeholder="Search by user name...">
                </div>
                {{-- MAC filter --}}
                <div class="col-md-4">
                    <label class="form-label">MAC Address</label>
                    <input type="text" id="searchMac" class="form-control bg-light" placeholder="Search by MAC address...">
                </div>
                {{-- IP filter --}}
                <div class="col-md-4">
                    <label class="form-label">IP Address</label>
                    <input type="text" id="searchIp" class="form-control bg-light" placeholder="Search by IP address...">
                </div>
            </div>

            <div class="row g-3 align-items-end">
                {{-- Status filter --}}
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select id="searchStatus" class="form-select bg-light">
                        <option value="">All Status</option>
                        <option>Online</option>
                        <option>Offline</option>
                        <option>Critical</option>
                    </select>
                </div>
                {{-- Building filter --}}
                <div class="col-md-4">
                    <label class="form-label">Building</label>
                    <select id="searchBuilding" class="form-select bg-light">
                        <option value="">All Buildings</option>
                        <option>Stefani</option>
                        <option>General Library</option>
                        <option>Student Center</option>
                        <option>Engineering Complex</option>
                        <option>Computer Science Department</option>
                        <option>Administration</option>
                        <option>Physics</option>
                        <option>Chardon</option>
                    </select>
                </div>
                {{-- Search and Reset buttons --}}
                <div class="col-md-4 d-flex justify-content-end align-items-center gap-3">
                    <button type="button" id="searchBtn" class="btn btn-success px-5 py-2">
                        <i class="bi bi-search me-2"></i> Search
                    </button>
                    <button type="reset" id="resetBtn" class="btn btn-outline-secondary px-4 py-2">
                        <i class="bi bi-arrow-counterclockwise me-2"></i> Reset
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- ================= SEARCH RESULTS TABLE ================= --}}
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h5 class="fw-semibold mb-3">Search Results</h5>
        <table class="table table-bordered table-hover align-middle" id="resultsTable">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>MAC Address</th>
                    <th>IP Address</th>
                    <th>Status</th>
                    <th>Building</th>
                </tr>
            </thead>
            <tbody>
                {{-- Rows populated dynamically via JS --}}
            </tbody>
        </table>
        <p id="noResults" class="text-muted fst-italic mt-2">No results to display.</p>
    </div>

    {{-- ================= SYSTEM OVERVIEW CARDS ================= --}}
    <div class="card border-0 shadow-sm p-4">
        <h5 class="fw-semibold mb-3">System Overview</h5>
        <div class="row g-3">
            {{-- Total devices --}}
            <div class="col-md-3">
                <div class="border rounded-3 p-3 text-center" style="background-color: #f0f6ff;">
                    <h6 class="fw-semibold">Total Devices</h6>
                    <h2 class="fw-bold text-primary mb-1" id="totalDevices">0</h2>
                    <p class="text-primary small mb-0">Registered in system</p>
                </div>
            </div>
            {{-- Active devices --}}
            <div class="col-md-3">
                <div class="border rounded-3 p-3 text-center" style="background-color: #ecfdf5;">
                    <h6 class="fw-semibold">Active Now</h6>
                    <h2 class="fw-bold text-success mb-1" id="activeDevices">0</h2>
                    <p class="text-success small mb-0">Currently online</p>
                </div>
            </div>
            {{-- Inactive devices --}}
            <div class="col-md-3">
                <div class="border rounded-3 p-3 text-center" style="background-color: #fffbea;">
                    <h6 class="fw-semibold">Inactive</h6>
                    <h2 class="fw-bold text-warning mb-1" id="inactiveDevices">0</h2>
                    <p class="text-warning small mb-0">Offline devices</p>
                </div>
            </div>
            {{-- Total buildings monitored --}}
            <div class="col-md-3">
                <div class="border rounded-3 p-3 text-center" style="background-color: #ecfdf5;">
                    <h6 class="fw-semibold">Buildings</h6>
                    <h2 class="fw-bold text-success mb-1" id="totalBuildings">0</h2>
                    <p class="text-success small mb-0">Monitored locations</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ================= JAVASCRIPT SEARCH LOGIC ================= --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    // Sample device data - expanded with all buildings from map
    const data = [
        // Stefani Building
        { user: 'admin', mac: '00:1B:44:11:3A:B7', ip: '192.168.1.10', status: 'Online', building: 'Stefani' },
        { user: 'jdoe', mac: '00:1B:44:11:3A:B8', ip: '192.168.1.11', status: 'Online', building: 'Stefani' },
        { user: 'msmith', mac: '00:1B:44:11:3A:B9', ip: '192.168.1.12', status: 'Offline', building: 'Stefani' },
        
        // Biblioteca
        { user: 'jsantos', mac: '00:1B:44:11:4A:11', ip: '192.168.2.10', status: 'Online', building: 'Biblioteca' },
        { user: 'acastro', mac: '00:1B:44:11:4A:12', ip: '192.168.2.11', status: 'Online', building: 'Biblioteca' },
        
        // Centro de Estudiantes
        { user: 'drios', mac: '00:1B:44:11:5A:21', ip: '192.168.3.10', status: 'Online', building: 'Centro de Estudiantes' },
        { user: 'clopez', mac: '00:1B:44:11:5A:22', ip: '192.168.3.11', status: 'Offline', building: 'Centro de Estudiantes' },
        
        // Chardon Building
        { user: 'rperez', mac: '00:1B:44:11:6A:31', ip: '192.168.4.10', status: 'Online', building: 'Chardon' },
        { user: 'lgarcia', mac: '00:1B:44:11:6A:32', ip: '192.168.4.11', status: 'Offline', building: 'Chardon' },
        
        // Fisica
        { user: 'jfernandez', mac: '00:1B:44:11:8A:51', ip: '192.168.6.10', status: 'Online', building: 'Fisica' },
        { user: 'sgonzalez', mac: '00:1B:44:11:8A:52', ip: '192.168.6.11', status: 'Offline', building: 'Fisica' },
        
        // Quimica
        { user: 'druiz', mac: '00:1B:44:11:9A:61', ip: '192.168.7.10', status: 'Online', building: 'Quimica' },
        
        // Biologia
        { user: 'mdiaz', mac: '00:1B:44:11:AA:71', ip: '192.168.8.10', status: 'Online', building: 'Biologia' },
        { user: 'ctorres', mac: '00:1B:44:11:AA:72', ip: '192.168.8.11', status: 'Online', building: 'Biologia' },
        
        // Ing.Civil
        { user: 'erodriguez', mac: '00:1B:44:11:7A:41', ip: '192.168.5.10', status: 'Online', building: 'Ing.Civil' },
        
        // Ing.Industrial
        { user: 'mmartinez', mac: '00:1B:44:11:7A:42', ip: '192.168.5.11', status: 'Online', building: 'Ing.Industrial' },
        
        // Celis
        { user: 'alopez', mac: '00:1B:44:11:6A:33', ip: '192.168.4.12', status: 'Online', building: 'Celis' },
        
        // Musa
        { user: 'rmendez', mac: '00:1B:44:11:BA:81', ip: '192.168.9.10', status: 'Online', building: 'Musa' }
    ];

    const tbody = document.querySelector('#resultsTable tbody');
    const noResults = document.getElementById('noResults');

    // Calculate and update System Overview statistics
    function updateSystemOverview() {
        const totalDevices = data.length;
        const activeDevices = data.filter(d => d.status === 'Online').length;
        const inactiveDevices = data.filter(d => d.status === 'Offline' || d.status === 'Critical').length;
        const uniqueBuildings = [...new Set(data.map(d => d.building))].length;
        
        document.getElementById('totalDevices').textContent = totalDevices;
        document.getElementById('activeDevices').textContent = activeDevices;
        document.getElementById('inactiveDevices').textContent = inactiveDevices;
        document.getElementById('totalBuildings').textContent = uniqueBuildings;
    }
    
    // Update overview on page load
    updateSystemOverview();

    // Function to render table rows dynamically
    function renderTable(rows) {
        tbody.innerHTML = '';
        if (rows.length === 0) {
            noResults.textContent = "No results to display.";
            noResults.classList.remove('d-none');
            return;
        }

        noResults.classList.add('d-none');

        rows.forEach(r => {
            // Badge color based on device status
            let badgeClass = r.status === 'Online' ? 'bg-success' :
                             r.status === 'Offline' ? 'bg-danger' :
                             'bg-warning text-dark';

            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${r.user}</td>
                    <td>${r.mac}</td>
                    <td>${r.ip}</td>
                    <td><span class="badge ${badgeClass}">${r.status}</span></td>
                    <td>${r.building}</td>
                </tr>
            `);
        });
    }

    // Initially show no results
    renderTable([]);

    // Search button click event
    document.getElementById('searchBtn').addEventListener('click', () => {
        const user = document.getElementById('searchUser').value.toLowerCase();
        const mac = document.getElementById('searchMac').value.toLowerCase();
        const ip = document.getElementById('searchIp').value.toLowerCase();
        const status = document.getElementById('searchStatus').value.toLowerCase();
        const building = document.getElementById('searchBuilding').value.toLowerCase();

        // Filter data based on input values
        const filtered = data.filter(d =>
            (!user || d.user.toLowerCase().includes(user)) &&
            (!mac || d.mac.toLowerCase().includes(mac)) &&
            (!ip || d.ip.toLowerCase().includes(ip)) &&
            (!status || d.status.toLowerCase() === status) &&
            (!building || d.building.toLowerCase().includes(building))
        );

        renderTable(filtered);
    });

    // Reset button clears search and results
    document.getElementById('resetBtn').addEventListener('click', () => {
        document.getElementById('searchForm').reset();
        renderTable([]);
    });
});
</script>
@endsection
