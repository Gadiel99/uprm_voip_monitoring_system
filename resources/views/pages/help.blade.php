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

        <h6 class="fw-bold">Getting Started</h6>
        <ol class="mb-4">
            <li><strong>Dashboard Overview:</strong> Start at the Home tab to view the interactive map and latest system reports. The map shows real-time device status with color-coded markers.</li>
            <li><strong>Monitor Alerts:</strong> Click the Alerts tab to view system notifications. Critical alerts require immediate attention and are highlighted in red.</li>
            <li><strong>Device Management:</strong> Use the Devices tab to monitor all connected equipment and view device status.</li>
            <li><strong>System Health:</strong> The Diagnostics tab provides detailed system performance metrics and health checks.</li>
        </ol>

        <h6 class="fw-bold">Working with Alerts</h6>
        <h6 class="fw-semibold mt-3">Understanding Severity Levels:</h6>
        <ul class="mb-3">
            <li><span class="text-danger fw-bold">Critical:</span> Immediate action required - system failure or security breach</li>
            <li><span class="text-warning fw-bold">High:</span> Urgent attention needed - performance degradation</li>
            <li><span class="text-warning fw-bold" style="color: #f0ad4e;">Medium:</span> Warning condition - monitor closely</li>
            <li><span class="text-info fw-bold">Low:</span> Informational - routine system events</li>
        </ul>

        <p><strong>Alert Actions:</strong> Click on any alert to view detailed information, acknowledge warnings, or mark issues as resolved.</p>

        <h6 class="fw-bold mt-4">Device Monitoring</h6>
        <h6 class="fw-semibold">Status Indicators:</h6>
        <ul class="mb-3">
            <li><span class="text-success fw-bold">Online:</span> Device is connected and functioning normally</li>
            <li><span class="text-danger fw-bold">Offline:</span> Device is not responding or disconnected</li>
            <li><span class="text-warning fw-bold">Warning:</span> Device has issues but is still operational</li>
        </ul>

        <h6 class="fw-bold mt-4">Configuring Thresholds</h6>
        <ul class="mb-3">
            <li><strong>Access Settings:</strong> Click “Settings” in the sidebar to configure alert thresholds for different system metrics.</li>
            <li><strong>Threshold Types:</strong> Set warning and critical levels for CPU usage, memory, network traffic, and storage usage.</li>
            <li><strong>Save Changes:</strong> Remember to click “Save Changes” after modifying any threshold values. Unsaved changes will be highlighted.</li>
        </ul>

        <h6 class="fw-bold mt-4">Running Diagnostics</h6>
        <ul>
            <li><strong>Performance Metrics:</strong> View real-time system performance including CPU usage, memory consumption, storage utilization, network traffic, and power consumption.</li>
            <li><strong>Diagnostic Tests:</strong> Run automated tests to check network connectivity, database performance, security scans, backup verification, and load balancer health.</li>
            <li><strong>System Health:</strong> Monitor the overall system health summary showing healthy systems percentage, active warnings, and critical issues.</li>
        </ul>
    </div>
</div>
@endsection
