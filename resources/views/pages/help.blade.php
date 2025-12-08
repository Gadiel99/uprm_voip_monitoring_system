{{--
/*
 * File: help.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Comprehensive user guide and system documentation
 * 
 * Author: [Hector R. Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   This page provides complete documentation for system users, covering all
 *   features, workflows, and configuration options. It serves as the primary
 *   reference guide for the monitoring system.
 * 
 * Documentation Sections:
 *   1. Getting Started
 *      - Dashboard overview
 *      - Monitor alerts
 *      - Device management
 *      - System health diagnostics
 *   
 *   2. Working with Alerts
 *      - Severity level definitions (Critical/Medium/Low)
 *      - Alert actions and acknowledgment
 *      - Building summaries
 *   
 *   3. Device Monitoring
 *      - Status indicators (Online/Offline)
 *      - Filtering by building
 *      - Device information fields
 *   
 *   4. Configuring Thresholds & Notifications
 *      - Access settings navigation
 *      - Threshold types (Warning/Critical)
 *      - Alert frequency configuration
 *      - Notification preferences (Email/Push)
 *   
 *   5. Running Diagnostics
 *      - Automated diagnostic tests
 *      - Health summary color codes
 *      - Network/Database/Backup checks
 *   
 *   6. Admin Management
 *      - Access control
 *      - Admin panel sub-tabs (Backup/Logs/Settings/Servers/Users)
 *      - User role management
 * 
 * Severity Level Definitions:
 *   - Critical (Red): Immediate action required, system failure/major outage
 *   - Medium (Yellow): Warning condition, monitor closely
 *   - Low (Blue): Informational, routine or resolved event
 * 
 * Target Audience:
 *   - System administrators
 *   - Network operators
 *   - Technical support staff
 *   - New users requiring onboarding
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3 for styling
 *   - Bootstrap Icons for visual indicators
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 1063 software user documentation
 *   - Adheres to IEEE 1016 design description standards
 *   - Implements IEEE 730 quality assurance documentation
 */
--}}
@extends('components.layout.app')

@section('content')
<div class="container-fluid">
    {{-- Navigation tabs at the top --}}
    <ul class="nav nav-tabs ps-3 pt-2 mb-4">
        <li class="nav-item">
            {{-- Active tab for How To guide --}}
            <a class="nav-link active" href="#">How To</a>
        </li>
    </ul>

    {{-- Main card container for the How To guide --}}
    <div class="card border-0 shadow-sm p-4">
        <h4 class="fw-semibold mb-4">How to Use the Monitoring System</h4>

        {{-- ================= GETTING STARTED ================= --}}
        {{-- Overview instructions for first-time users --}}
        <h6 class="fw-bold">Getting Started</h6>
        <ol class="mb-4">
            <li>
                <strong>Dashboard Overview:</strong> Start at the <strong>Home</strong> tab to view the interactive campus map and building markers. 
                Each marker represents a building and is color-coded by alert severity (Green = Normal, Yellow = Warning, Red = Critical). 
                Click a building marker to view its detailed alerts in the <strong>Alerts</strong> tab.
            </li>
            <li>
                <strong>Monitor Alerts:</strong> Go to the <strong>Alerts</strong> tab to see current notifications. 
                Critical alerts require immediate attention and are highlighted in red. 
            </li>
            <li>
                <strong>Device Management:</strong> Use the <strong>Devices</strong> tab to monitor all connected devices. 
                Click on any building to view its devices, then click on any device row to open its <strong>30-day activity graph</strong> 
                (green points = active, red points = inactive).
            </li>
            <li>
                <strong>Reports:</strong> The <strong>Reports</strong> tab allows you to search and filter devices by user, MAC address, IP address, status, and building.
            </li>
        </ol>

        {{-- ================= ALERTS ================= --}}
        {{-- Instructions on how to work with system alerts --}}
        <h6 class="fw-bold">Working with Alerts</h6>

        <h6 class="fw-semibold mt-3">Understanding Severity Levels:</h6>
        <ul class="mb-3">
            <li><span class="text-danger fw-bold">Critical:</span> Immediate action required — system failure or major outage (>25% devices offline)</li>
            <li><span class="text-warning fw-bold">Warning:</span> Warning condition — monitor closely (10-25% devices offline)</li>
            <li><span class="text-success fw-bold">Normal:</span> System operating normally — all devices functioning properly (<10% devices offline)</li>
        </ul>

        {{-- Instructions for interacting with alerts --}}
        <p>
            <strong>Alert Badges:</strong> The Alerts page displays count badges showing the number of Critical, Warning, and Normal alerts. 
            Click on any building to view detailed device-level information and affected devices. 
            Building summaries show total offline devices out of total devices.
        </p>

        {{-- ================= DEVICES ================= --}}
        {{-- How to monitor and interpret device status --}}
        <h6 class="fw-bold mt-4">Device Monitoring</h6>
        <h6 class="fw-semibold">Status Indicators:</h6>
        <ul class="mb-3">
            <li><span class="text-success fw-bold">Online:</span> Device is connected and functioning normally</li>
            <li><span class="text-danger fw-bold">Offline:</span> Device is not responding or disconnected</li>
        </ul>
        <p>
            Use the <strong>Devices</strong> tab to view all buildings and their device statistics. 
            Click on any building to see detailed device information including: 
            <strong>IP Address, MAC Address, Owner, Extensions,</strong> and <strong>Status</strong>. 
            Click on any device row to view its 30-day activity graph showing connection history.
        </p>
        <p>
            <strong>Critical Devices:</strong> High-priority devices are displayed in a separate table at the top of the Devices page 
            and can be managed in the <strong>Admin → Settings</strong> panel.
        </p>

        {{-- ================= SETTINGS ================= --}}
        {{-- How to configure thresholds and notifications --}}
        <h6 class="fw-bold mt-4">Configuring Settings</h6>
        <ul class="mb-3">
            <li><strong>Access Settings:</strong> Go to <strong>Admin → Settings</strong> to manage critical devices and alert display preferences.</li>
            <li><strong>Critical Devices:</strong> Add or remove high-priority devices that require special monitoring. Enter the device's IP Address, MAC Address, and Owner information.</li>
            <li><strong>Alert Thresholds:</strong> Configure offline device percentage thresholds for building alerts:
                <ul>
                    <li><strong>Lower Threshold:</strong> Buildings with offline percentage below this value are considered <span class="badge bg-success">Normal</span> (default: 10%)</li>
                    <li><strong>Upper Threshold:</strong> Buildings with offline percentage above this value are considered <span class="badge bg-danger">Critical</span> (default: 25%)</li>
                    <li>Buildings with offline percentage between the two thresholds are <span class="badge bg-warning text-dark">Warning</span></li>
                    <li>Use the "Reset to Default" button to restore thresholds to 10% (lower) and 25% (upper)</li>
                </ul>
            </li>
            <li><strong>Save Changes:</strong> All changes are saved automatically or require confirmation depending on the setting type.</li>
        </ul>

        {{-- ================= ADMIN ================= --}}
        {{-- Information for system administrators --}}
        <h6 class="fw-bold mt-4">Admin Management</h6>
        <ul>
            <li><strong>Access Control:</strong> Only administrators can view and modify the <strong>Admin</strong> tab.</li>
            <li><strong>Admin Tabs:</strong></li>
            <ul>
                <li><strong>Backup:</strong> Configure backup settings and download system backups as ZIP files.</li>
                <li><strong>Logs:</strong> Review comprehensive system activity logs including user logins, logouts, page access, and all system actions. Filter logs by type (All, Info, Success, Warning, Error).</li>
                <li><strong>Settings:</strong> Manage critical devices list, configure alert threshold, and disable/enable email notifications.</li>
                <li><strong>Users:</strong> View system users, their roles (Admin/Assistant), email addresses, and online/offline status.</li>
            </ul>
            <li><strong>Map Markers:</strong> Administrators can add, edit and delete building markers on the campus map using the <strong>Add Marker</strong>, <strong>Edit Marker</strong> and <strong>Delete Marker</strong> buttons on the Home page.</li>
        </ul>
    </div>
</div>
@endsection
