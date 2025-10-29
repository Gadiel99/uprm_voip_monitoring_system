@extends('components.layout.app')

@section('content')
<div class="container-fluid">
    <ul class="nav nav-tabs ps-3 pt-2 mb-4">
        <li class="nav-item">
            <a class="nav-link active" href="#">How To</a>
        </li>
    </ul>

    <div class="card border-0 shadow-sm p-4">
        <h4 class="fw-semibold mb-4">How to Use the Monitoring System</h4>

        {{-- ================= GETTING STARTED ================= --}}
        <h6 class="fw-bold">Getting Started</h6>
        <ol class="mb-4">
            <li><strong>Dashboard Overview:</strong> Start at the <strong>Home</strong> tab to view the interactive campus map and latest system reports. Each marker represents a building and is color-coded by alert severity. Click a building marker to open its detailed view in the <strong>Alerts</strong> tab.</li>
            <li><strong>Monitor Alerts:</strong> Go to the <strong>Alerts</strong> tab to see current notifications. Critical alerts require immediate attention and are highlighted in red. You can sort alerts by <em>severity</em> or <em>alphabetically</em> in <strong>Admin → Settings</strong>.</li>
            <li><strong>Device Management:</strong> Use the <strong>Devices</strong> tab to monitor all connected phones. Click on any device row to open its <strong>activity graph</strong> for the past month (green = active, red = inactive).</li>
            <li><strong>System Health:</strong> The <strong>Diagnostics</strong> tab provides metrics for devices and buildings across all monitored servers.</li>
        </ol>

        {{-- ================= ALERTS ================= --}}
        <h6 class="fw-bold">Working with Alerts</h6>

        <h6 class="fw-semibold mt-3">Understanding Severity Levels:</h6>
        <ul class="mb-3">
            <li><span class="text-danger fw-bold">Critical:</span> Immediate action required — system failure or major outage</li>
            <li><span class="text-warning fw-bold">Medium:</span> Warning condition — monitor closely</li>
            <li><span class="text-info fw-bold">Low:</span> Informational — routine or resolved system event</li>
        </ul>

        <p><strong>Alert Actions:</strong> Click any alert to view detailed information, acknowledge warnings, or mark issues as resolved. Building summaries show total affected devices and time since last update.</p>

        {{-- ================= DEVICES ================= --}}
        <h6 class="fw-bold mt-4">Device Monitoring</h6>
        <h6 class="fw-semibold">Status Indicators:</h6>
        <ul class="mb-3">
            <li><span class="text-success fw-bold">Online:</span> Device is connected and functioning normally</li>
            <li><span class="text-danger fw-bold">Offline:</span> Device is not responding or disconnected</li>
        </ul>
        <p>Use the <strong>Devices</strong> tab to filter devices by building and quickly identify issues. Each entry shows the device’s server, user, MAC, and IP address.</p>

        {{-- ================= SETTINGS ================= --}}
        <h6 class="fw-bold mt-4">Configuring Thresholds & Notifications</h6>
        <ul class="mb-3">
            <li><strong>Access Settings:</strong> Go to <strong>Admin → Settings</strong> to configure alert thresholds, notification rules, and sorting preferences.</li>
            <li><strong>Threshold Types:</strong> Set <em>Warning</em> and <em>Critical</em> levels for devices.</li>
            <li><strong>Alert Frequency:</strong> Define how often notifications are sent when an issue occurs and while it remains active.</li>
            <li><strong>Notification Preferences:</strong> Enable or disable <strong>Email</strong> and <strong>Push Notifications</strong> for alerts.</li>
            <li><strong>Save Changes:</strong> Always click <strong>“Save Configuration”</strong> after editing thresholds or alerts.</li>
        </ul>

        {{-- ================= DIAGNOSTICS ================= --}}
        <h6 class="fw-bold mt-4">Running Diagnostics</h6>
        <ul>
            <li><strong>Diagnostic Tests:</strong> Run automated tests to verify network connectivity, database performance, backup integrity, and system response time.</li>
            <li><strong>Health Summary:</strong> Results are color-coded for clarity — 
                <span class="text-success fw-bold">Green</span> (Normal), 
                <span class="text-warning fw-bold">Yellow</span> (Warning), 
                <span class="text-danger fw-bold">Red</span> (Critical).
            </li>
        </ul>

        {{-- ================= ADMIN ================= --}}
        <h6 class="fw-bold mt-4">Admin Management</h6>
        <ul>
            <li><strong>Access Control:</strong> Only administrators can view and modify the <strong>Admin</strong> tab.</li>
            <li><strong>Sub-Tabs:</strong></li>
            <ul>
                <li><strong>Backup:</strong> Create or restore system backups as ZIP files.</li>
                <li><strong>Logs:</strong> Review system activity and event history.</li>
                <li><strong>Settings:</strong> Adjust alert thresholds, notification options, and sorting behavior.</li>
                <li><strong>Servers:</strong> Manage connected system servers and monitor their status.</li>
                <li><strong>Users:</strong> Add, edit, or remove system users and manage their roles.</li>
            </ul>
        </ul>
    </div>
</div>
@endsection
