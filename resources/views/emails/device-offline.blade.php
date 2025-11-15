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
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #ea580c;
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
        .alert-box {
            background-color: #ffedd5;
            border-left: 4px solid #ea580c;
            padding: 15px;
            margin: 20px 0;
        }
        .device-info {
            display: table;
            width: 100%;
            margin: 20px 0;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            padding: 8px;
            font-weight: bold;
            background-color: #e5e7eb;
            width: 40%;
        }
        .info-value {
            display: table-cell;
            padding: 8px;
            background-color: white;
        }
        .critical-badge {
            display: inline-block;
            background-color: #dc2626;
            color: white;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
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
            margin-top: 20px;
        }
        .warning {
            background-color: #fef3c7;
            border: 1px solid #f59e0b;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🚨 CRITICAL DEVICE OFFLINE</h1>
    </div>
    <div class="content">
        <div class="alert-box">
            <strong>A critical device has gone offline and requires immediate attention.</strong>
        </div>

        <div class="device-info">
            <div class="info-row">
                <div class="info-label">Status</div>
                <div class="info-value">
                    <span class="critical-badge">CRITICAL DEVICE</span>
                    <span style="color: #dc2626; font-weight: bold; margin-left: 10px;">OFFLINE</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">IP Address</div>
                <div class="info-value"><code>{{ $device->ip_address }}</code></div>
            </div>
            <div class="info-row">
                <div class="info-label">MAC Address</div>
                <div class="info-value"><code>{{ $device->mac_address }}</code></div>
            </div>
            @if($device->owner)
            <div class="info-row">
                <div class="info-label">Owner</div>
                <div class="info-value">{{ $device->owner }}</div>
            </div>
            @endif
            @if($buildings)
            <div class="info-row">
                <div class="info-label">Building(s)</div>
                <div class="info-value">{{ $buildings }}</div>
            </div>
            @endif
            <div class="info-row">
                <div class="info-label">Network</div>
                <div class="info-value">{{ $device->network->name ?? 'Unknown' }}</div>
            </div>
            @if($device->extensions && $device->extensions->count() > 0)
            <div class="info-row">
                <div class="info-label">Extensions</div>
                <div class="info-value">
                    {{ $device->extensions->pluck('extension')->implode(', ') }}
                </div>
            </div>
            @endif
        </div>

        <div class="warning">
            <strong>⚠️ Impact:</strong> This device has been marked as critical to operations. 
            Its offline status may affect essential services or communications.
        </div>

        <p><strong>Recommended Actions:</strong></p>
        <ul>
            <li>Verify physical connectivity and power status</li>
            <li>Check network infrastructure (switches, routers)</li>
            <li>Attempt to ping the device from the network</li>
            <li>Review recent maintenance or configuration changes</li>
            <li>Contact the device owner if necessary</li>
        </ul>

        <div style="text-align: center;">
            <a href="{{ env('APP_URL') }}/devices" class="button">View Device Dashboard</a>
        </div>

        <div class="footer">
            <p>This is an automated alert from the UPRM VoIP Monitoring System.</p>
            <p>Device Last Checked: {{ now()->format('F j, Y, g:i a') }}</p>
        </div>
    </div>
</body>
</html>
