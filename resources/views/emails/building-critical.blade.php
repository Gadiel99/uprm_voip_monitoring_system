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
        .alert-box {
            background-color: #fee2e2;
            border-left: 4px solid #dc2626;
            padding: 15px;
            margin: 20px 0;
        }
        .stats {
            display: table;
            width: 100%;
            margin: 20px 0;
        }
        .stat-row {
            display: table-row;
        }
        .stat-label {
            display: table-cell;
            padding: 8px;
            font-weight: bold;
            background-color: #e5e7eb;
        }
        .stat-value {
            display: table-cell;
            padding: 8px;
            background-color: white;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>⚠️ CRITICAL ALERT</h1>
    </div>
    <div class="content">
        <h2>Building in Critical State</h2>
        
        <div class="alert-box">
            <strong>{{ $building->name }}</strong> has exceeded the critical threshold for device failures.
        </div>

        <p>The following metrics have been recorded for this building:</p>

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
                <div class="stat-value" style="color: #dc2626; font-weight: bold;">{{ $building->offline_devices }}</div>
            </div>
            <div class="stat-row">
                <div class="stat-label">Offline Percentage</div>
                <div class="stat-value" style="color: #dc2626; font-weight: bold;">{{ $building->offline_percentage }}%</div>
            </div>
            <div class="stat-row">
                <div class="stat-label">Critical Threshold</div>
                <div class="stat-value">{{ $alertSettings->upper_threshold }}%</div>
            </div>
        </div>

        <p><strong>Immediate action is required to investigate and restore service to the affected devices.</strong></p>

        <div style="text-align: center;">
            <a href="{{ env('APP_URL') }}/buildings" class="button">View Building Details</a>
        </div>

        <div class="footer">
            <p>This is an automated alert from the UPRM VoIP Monitoring System.</p>
            <p>Timestamp: {{ now()->format('F j, Y, g:i a') }}</p>
        </div>
    </div>
</body>
</html>
