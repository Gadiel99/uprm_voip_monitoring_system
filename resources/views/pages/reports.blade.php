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
 *   - Extension (phone extension number)
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
{{--
/**
 * Reports Page View
 * 
 * Purpose:
 *   Provides device search, filtering, and reporting interface for VoIP system.
 *   Enables users to search devices by user, MAC, IP, status, and building.
 *   Displays system overview statistics and detailed search results.
 * 
 * Controller:
 *   ReportsController@index - Initial page load with stats
 *   ReportsController@search - Filtered search results
 * 
 * Features:
 *   - Real-time device search with multiple filters
 *   - System overview with statistics cards
 *   - Results table with device details
 *   - Server-side data from database
 *   - Reset functionality to clear all filters
 * 
 * Search Filters:
 *   - User: Text search by assigned user name
 *   - MAC Address: Filter by network MAC address
 *   - IP Address: Filter by IP address
 *   - Status: Dropdown (Online/Offline)
 *   - Building: Dropdown selection from database
 * 
 * Results Table Columns:
 *   - User (assigned user name or N/A)
 *   - Extension (phone extension number or N/A)
 *   - MAC Address (network identifier)
 *   - IP Address (device IP)
 *   - Status (color-coded badge)
 *   - Building (location or Unassigned)
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
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3
 *   - Laravel Blade templating
 *   - Server-side data from ReportsController
 */
--}}
@extends('components.layout.app')

@section('content')
<style>
    /* Match existing design language from devices/admin pages */
    .reports-section-heading { font-weight:600; }
    .stat-tile { border-radius:14px; padding:1.25rem 1rem; min-height:120px; display:flex; flex-direction:column; justify-content:center; }
    .stat-tile h3 { font-weight:700; margin:0; }
    .tile-total { background:#f4f7ff; }
    .tile-active { background:#e6f9ed; }
    .tile-inactive { background:#fff7e6; }
    .tile-buildings { background:#e6f9ed; }
    .stat-desc { font-size:.75rem; margin-top:.25rem; color:#11618b; }
    .stat-desc.inactive { color:#b38300; }
    .stat-desc.active { color:#006f3f; }
    .search-label { font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:#555; }
    .results-empty i { font-size:3rem; opacity:.25; }
    .btn-success { background:#00844b; border-color:#00844b; }
    .btn-success:hover { background:#006f3f; border-color:#006f3f; }
    .btn-outline-secondary:hover { background:#f1f3f4; }
    .reports-wrapper-card { border-radius:14px; }
    /* Remove monospace shrinking for code; we'll stop using <code> for MAC/IP to keep uniform font */
    .results-table code { font-size:inherit; font-family:inherit; }
</style>

<div class="container-fluid">
    <div class="card border-0 shadow-sm p-4 reports-wrapper-card">

        {{-- DEVICE REPORTS SEARCH --}}
        <div class="mb-4" id="reportsSearchCard">
            <h6 class="fw-semibold mb-3">Device Reports Search</h6>
            <form method="GET" action="{{ route('reports') }}" id="searchForm">
                <div class="input-group input-group-lg">
                    <input type="text" id="searchQuery" name="query" class="form-control" placeholder="Search devices... (use commas for multiple terms: monzon, 4542ab)" value="{{ $filters['query'] ?? '' }}">
                    <button type="submit" class="btn btn-success px-4" id="searchBtn"><i class="bi bi-search me-1"></i>Search</button>
                    <button type="button" class="btn btn-outline-secondary px-3" id="resetBtn"><i class="bi bi-arrow-clockwise"></i></button>
                </div>
                <small class="text-muted mt-2 d-block">Search by user, MAC, IP, status, building, or extension. Use commas to search multiple terms (all must match).</small>
            </form>
        </div>

        {{-- SEARCH RESULTS --}}
        <div class="mb-4" id="reportsResultsCard">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold mb-0">Search Results</h6>
                @if(isset($devices) && count($devices) > 0)
                    <a href="{{ route('reports.export.csv', ['query' => $filters['query'] ?? '']) }}" class="btn btn-success btn-sm">
                        <i class="bi bi-download me-1"></i>Download CSV
                    </a>
                @else
                    <button class="btn btn-secondary btn-sm" disabled>
                        <i class="bi bi-download me-1"></i>Download CSV
                    </button>
                @endif
            </div>
            @if(isset($devices) && count($devices) > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle results-table">
                        <thead class="table-light">
                            <tr>
                                <th>User</th>
                                <th>Extension</th>
                                <th>MAC Address</th>
                                <th>IP Address</th>
                                <th>Status</th>
                                <th>Building</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($devices as $device)
                                <tr>
                                    <td>
                                        @if(count($device->extensions) > 0)
                                            @foreach($device->extensions as $ext)
                                                <div>{{ $ext['name'] }}</div>
                                            @endforeach
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if(count($device->extensions) > 0)
                                            @foreach($device->extensions as $ext)
                                                {{ $ext['number'] ?? 'N/A' }}@if(!$loop->last), @endif
                                            @endforeach
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $device->mac_address }}</td>
                                    <td>{{ $device->ip_address }}</td>
                                    <td>
                                        @if($device->status == 'online')
                                            <span class="text-success">Online</span>
                                        @else
                                            <span class="text-danger">Offline</span>
                                        @endif
                                    </td>
                                    <td>{{ $device->building_name }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="results-empty text-center py-4 text-muted">
                    <i class="bi bi-search"></i>
                    <p class="mt-3 mb-0">
                        @if(isset($filters) && (isset($filters['user']) || isset($filters['mac']) || isset($filters['ip']) || isset($filters['status']) || isset($filters['building_id'])))
                            No devices found matching your search criteria.
                        @else
                            No results to display.
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
// Reset button: clear form inputs and navigate back to base reports route without query params
document.getElementById('resetBtn').addEventListener('click', function() {
    const form = document.getElementById('searchForm');
    form.querySelectorAll('input').forEach(i => i.value='');
    form.querySelectorAll('select').forEach(s => s.selectedIndex = 0);
    // Use replace so back button does not return to filtered state
    window.location.replace("{{ route('reports') }}");
});
</script>
@endsection
