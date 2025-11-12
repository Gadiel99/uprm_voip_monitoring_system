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
    <h4 class="fw-semibold mb-4">Device Management</h4>

    {{-- TABLE: CRITICAL DEVICES --}}
    {{-- Shows critical devices as a separate table --}}
    <div id="buildingOverview">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-semibold mb-3">Critical Devices</h5>
            <p class="text-muted mb-3">High-priority devices requiring special monitoring.</p>

            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Building</th>
                        <th>Total Networks</th>
                        <th>Total Devices</th>
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

        {{-- TABLE: BUILDINGS OVERVIEW --}}
        {{-- Shows a summary of all buildings and their device counts --}}
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-semibold mb-3">Buildings Overview</h5>
            <p class="text-muted mb-3">Select a building to view all connected devices and their graphs.</p>

            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Building</th>
                        <th>Total Networks</th>
                        <th>Total Devices</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- All buildings from database --}}
                    @foreach($overview as $building)
                        <tr onclick="window.location.href='{{ route('devices.byBuilding', $building->building_id) }}'">
                            <td><i class="bi bi-building me-2 text-success"></i> {{ $building->name }}</td>
                            <td>{{ $building->total_networks }}</td>
                            <td>{{ $building->total_devices }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>


@endsection
