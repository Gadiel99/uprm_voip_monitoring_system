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

    /* Table value styling to match screenshot */
    .table td, .table th { font-size: 0.98rem; color: #212529; }
</style>

<div class="container-fluid">
    {{-- TABLE: CRITICAL DEVICES --}}
    {{-- Shows critical devices as a separate table --}}
    <div id="buildingOverview">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-semibold mb-3">Critical Devices</h5>
            <p class="text-muted mb-3">High-priority devices requiring special monitoring.</p>

            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50%;">Important Devices</th>
                        <th style="width: 25%;">Total Networks</th>
                        <th style="width: 25%;">Total Devices</th>
                    </tr>
                </thead>
                <tbody>
                    <tr onclick="window.location.href='{{ route('devices.critical') }}'" style="cursor: pointer;">
                        <td><i class="bi bi-exclamation-triangle me-2 text-danger"></i> Critical Devices</td>
                        <td>{{ $criticalDevices->total_networks ?? 0 }}</td>
                        <td>{{ $criticalDevices->total_devices ?? 0 }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- TABLE: ACTION REQUIRED (Unmapped Networks) --}}
        {{-- Shows networks not assigned to any building that need configuration --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-semibold mb-3">Action Required</h5>
            <p class="text-muted mb-3">Networks and devices not assigned to any building. Click to view and assign them.</p>

            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50%;">Connection</th>
                        <th style="width: 25%;">Total Networks</th>
                        <th style="width: 25%;">Total Devices</th>
                    </tr>
                </thead>
                <tbody>
                    <tr onclick="window.location.href='{{ route('devices.unmapped') }}'" style="cursor: pointer;">
                        <td><i class="bi bi-exclamation-circle me-2 text-warning"></i> Need Connection</td>
                        <td>{{ $unmappedStats->total_networks }}</td>
                        <td>{{ $unmappedStats->total_devices }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- TABLE: BUILDINGS OVERVIEW --}}
        {{-- Shows a summary of all buildings and their device counts --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-semibold mb-3">Buildings Overview</h5>
            <p class="text-muted mb-3">Select a building to view all connected devices and their graphs.</p>

            {{-- Search bar for filtering buildings --}}
            <div class="mb-3 d-flex gap-2">
                <input type="text" id="buildingSearch" class="form-control form-control-sm" placeholder="Search buildings by name..." style="max-width: 400px;">
                <button type="button" class="btn btn-outline-secondary btn-sm px-2" onclick="document.getElementById('buildingSearch').value=''; filterBuildings();">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 50%;">Building</th>
                        <th style="width: 25%;">Total Networks</th>
                        <th style="width: 25%;">Total Devices</th>
                    </tr>
                </thead>
                <tbody id="buildingsTableBody">
                    {{-- All buildings from database --}}
                    @foreach($overview as $building)
                        <tr class="building-row" data-building-name="{{ strtolower($building->name) }}" onclick="window.location.href='{{ route('devices.byBuilding', $building->building_id) }}'">
                            <td class="building-name"><i class="bi bi-building me-2 text-success"></i> {{ $building->name }}</td>
                            <td>{{ $building->total_networks }}</td>
                            <td>{{ $building->total_devices }}</td>
                        </tr>
                    @endforeach
                    <tr class="no-results-row" style="display: none;">
                        <td colspan="3" class="text-center text-muted">No buildings found matching your search.</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
// Filter buildings based on search input
function filterBuildings() {
    const searchInput = document.getElementById('buildingSearch').value.toLowerCase().trim();
    const rows = document.querySelectorAll('.building-row');
    const noResultsRow = document.querySelector('.no-results-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const buildingName = row.getAttribute('data-building-name');
        
        if (buildingName.includes(searchInput)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Show/hide no results message
    if (noResultsRow) {
        noResultsRow.style.display = visibleCount === 0 && searchInput !== '' ? '' : 'none';
    }
}

// Attach search event listener
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('buildingSearch');
    if (searchInput) {
        searchInput.addEventListener('input', filterBuildings);
    }
});
</script>

@endsection
