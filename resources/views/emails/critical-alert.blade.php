<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #dc2626;
            color: white;
            padding: 20px;
            border-radius: 5px 5px 0 0;
            text-align: center;
        }
        .content {
            background-color: #f9fafb;
            padding: 30px;
            border: 1px solid #e5e7eb;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .section {
            margin: 30px 0;
        }
        .section-title {
            font-size: 20px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #dc2626;
        }
        .alert-box {
            background-color: #fee2e2;
            border-left: 4px solid #dc2626;
            padding: 15px;
            margin: 15px 0;
        }
        .building-item, .device-item {
            background-color: white;
            border: 1px solid #e5e7eb;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .building-name, .device-title {
            font-size: 16px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .stats {
            display: table;
            width: 100%;
            margin: 10px 0;
        }
        .stat-row {
            display: table-row;
        }
        .stat-label {
            display: table-cell;
            padding: 6px 10px;
            font-weight: bold;
            background-color: #f3f4f6;
            width: 40%;
        }
        .stat-value {
            display: table-cell;
            padding: 6px 10px;
            background-color: white;
        }
        .critical {
            color: #dc2626;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            background-color: #dc2626;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .summary {
            background-color: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            font-size: 12px;
            color: #6b7280;
            text-align: center;
        }
        .button {
            display: inline-block;
            background-color: #2563eb;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 5px;
        }
        .no-items {
            color: #6b7280;
            font-style: italic;
            padding: 15px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>⚠️ CRITICAL ALERT</h1>
    </div>
    <div class="content">
        <div class="summary">
            @if($criticalBuildings->isNotEmpty() && $offlineDevices->isNotEmpty())
                {{ $criticalBuildings->count() }} Building(s) Critical &amp; {{ $offlineDevices->count() }} Critical Device(s) Offline
            @elseif($criticalBuildings->isNotEmpty())
                {{ $criticalBuildings->count() }} Building(s) in Critical State
            @else
                {{ $offlineDevices->count() }} Critical Device(s) Offline
            @endif
        </div>

        @if($criticalBuildings->isNotEmpty())
        <div class="section">
            <div class="section-title">🏢 Critical Buildings</div>
            <div class="alert-box">
                The following buildings have exceeded the critical threshold ({{ $alertSettings->upper_threshold }}%) for device failures.
            </div>

            @foreach($criticalBuildings as $building)
            <div class="building-item">
                <div class="building-name">{{ $building->name }}</div>
                <div class="stats">
                    <div class="stat-row">
                        <div class="stat-label">Total Devices</div>
                        <div class="stat-value">{{ $building->total_devices }}</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-label">Online Devices</div>
                        <div class="stat-value" style="color: #059669;">{{ $building->total_devices - $building->offline_devices }}</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-label">Offline Devices</div>
                        <div class="stat-value critical">{{ $building->offline_devices }}</div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-label">Offline Percentage</div>
                        <div class="stat-value critical">{{ $building->offline_percentage }}%</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif

        @if($offlineDevices->isNotEmpty())
        <div class="section">
            <div class="section-title">📵 Offline Critical Devices</div>
            <div class="alert-box">
                The following critical devices are currently offline and require immediate attention.
            </div>

            @foreach($offlineDevices as $device)
            <div class="device-item">
                <div class="device-title">
                    <span class="badge">CRITICAL</span>
                    <span class="critical">OFFLINE</span>
                </div>
                <div class="stats">
                    <div class="stat-row">
                        <div class="stat-label">IP Address</div>
                        <div class="stat-value"><code>{{ $device->ip_address }}</code></div>
                    </div>
                    <div class="stat-row">
                        <div class="stat-label">MAC Address</div>
                        <div class="stat-value"><code>{{ $device->mac_address }}</code></div>
                    </div>
                    @if($device->owner)
                    <div class="stat-row">
                        <div class="stat-label">Owner</div>
                        <div class="stat-value">{{ $device->owner }}</div>
                    </div>
                    @endif
                    @if($device->network && $device->network->buildings->isNotEmpty())
                    <div class="stat-row">
                        <div class="stat-label">Building(s)</div>
                        <div class="stat-value">{{ $device->network->buildings->pluck('name')->implode(', ') }}</div>
                    </div>
                    @endif
                    <div class="stat-row">
                        <div class="stat-label">Network</div>
                        <div class="stat-value">{{ $device->network->name ?? 'Unknown' }}</div>
                    </div>
                    @if($device->extensions && $device->extensions->count() > 0)
                    <div class="stat-row">
                        <div class="stat-label">Extensions</div>
                        <div class="stat-value">{{ $device->extensions->pluck('extension')->implode(', ') }}</div>
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @endif

        <div class="section">
            <p><strong>⚡ Immediate Action Required:</strong></p>
            <ul>
                @if($criticalBuildings->isNotEmpty())
                <li>Investigate buildings with high device failure rates</li>
                <li>Check network infrastructure (switches, patch panels)</li>
                @endif
                @if($offlineDevices->isNotEmpty())
                <li>Verify physical connectivity and power for critical devices</li>
                <li>Contact device owners if necessary</li>
                <li>Check for recent maintenance or configuration changes</li>
                @endif
                <li>Review system logs for additional details</li>
            </ul>
        </div>

        <div style="text-align: center;">
            @if($criticalBuildings->isNotEmpty())
            <a href="{{ env('APP_URL') }}/buildings" class="button">View Buildings</a>
            @endif
            @if($offlineDevices->isNotEmpty())
            <a href="{{ env('APP_URL') }}/devices" class="button">View Devices</a>
            @endif
            <a href="{{ env('APP_URL') }}" class="button">Dashboard</a>
        </div>

        <div class="footer">
            <p>This is an automated alert from the UPRM VoIP Monitoring System.</p>
            <p>Timestamp: {{ now()->format('F j, Y, g:i a') }}</p>
            <p>Alert Threshold: {{ $alertSettings->upper_threshold }}%</p>
        </div>
    </div>
</body>
</html>
