@extends('components.layout.app')

@section('content')
<style>
    .alert-green { background-color: #e6f9ed !important; color: #006f3f !important; border-color: #00844b !important; }
    .alert-yellow { background-color: #fff7e6 !important; color: #b38300 !important; border-color: #ffc107 !important; }
    .alert-red { background-color: #fdeaea !important; color: #c82333 !important; border-color: #dc3545 !important; }
    
    .badge-green { background-color: #198754 !important; color: #fff !important; }
    .badge-yellow { background-color: #ffc107 !important; color: #000 !important; }
    .badge-red { background-color: #dc3545 !important; color: #fff !important; }
    
    .clickable-row { cursor: pointer; transition: background 0.15s ease; }
    .clickable-row:hover { background-color: #f3f7f3; }
    
    .card { border-radius: 12px !important; }
</style>

<div class="container-fluid">
    <h4 class="fw-semibold mb-4">System Alerts</h4>
    
    @if(!$alertSettings->is_active)
        <div class="alert alert-warning mb-4">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Alert monitoring is currently disabled.</strong> 
            Enable it in <a href="{{ route('admin', ['tab' => 'settings']) }}" class="alert-link">Admin  Settings</a> to see color-coded statuses.
        </div>
    @endif
    
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 fw-semibold">System Summary</h6>
                        <small class="text-muted">Overall device status</small>
                    </div>
                    <div>
                        @if($systemSummary)
                            <span class="badge badge-{{ $systemSummary->alert_level }} me-2">
                                {{ $systemSummary->total_devices }} Total
                            </span>
                            <span class="badge bg-success me-2">
                                {{ $systemSummary->online_devices }} Online
                            </span>
                            <span class="badge bg-danger">
                                {{ $systemSummary->offline_devices }} Offline ({{ $systemSummary->offline_percentage }}%)
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($criticalDevices && $criticalDevices->total_devices > 0)
        <div class="card border-0 shadow-sm p-4 mb-4 alert-{{ $criticalDevices->alert_level }}">
            <h6 class="fw-semibold mb-3">
                <i class="bi bi-exclamation-triangle me-2"></i>Critical Devices
            </h6>
            <div class="row">
                <div class="col-md-3">
                    <strong>Total:</strong> {{ $criticalDevices->total_devices }}
                </div>
                <div class="col-md-3">
                    <strong>Online:</strong> <span class="text-success">{{ $criticalDevices->online_devices }}</span>
                </div>
                <div class="col-md-3">
                    <strong>Offline:</strong> <span class="text-danger">{{ $criticalDevices->offline_devices }}</span>
                </div>
                <div class="col-md-3">
                    <strong>Status:</strong> 
                    <span class="badge badge-{{ $criticalDevices->alert_level }}">
                        {{ ucfirst($criticalDevices->alert_level) }} ({{ $criticalDevices->offline_percentage }}%)
                    </span>
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('alerts.criticalOffline') }}" class="btn btn-sm btn-outline-dark">
                    <i class="bi bi-arrow-right me-1"></i>View Critical Devices (Offline Only)
                </a>
            </div>
        </div>
    @endif

    <div class="card border-0 shadow-sm p-4 mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-semibold">Buildings Overview</h6>
            <div>
                @php
                    $greenCount = $buildings->where('alert_level', 'green')->count();
                    $yellowCount = $buildings->where('alert_level', 'yellow')->count();
                    $redCount = $buildings->where('alert_level', 'red')->count();
                @endphp
                <span class="badge badge-red me-2">{{ $redCount }} Critical</span>
                <span class="badge badge-yellow me-2">{{ $yellowCount }} Warning</span>
                <span class="badge badge-green">{{ $greenCount }} Normal</span>
            </div>
        </div>

        <table class="table table-bordered table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Building</th>
                    <th>Total Devices</th>
                    <th>Online</th>
                    <th>Offline</th>
                    <th>Offline %</th>
                    <th>Alert Level</th>
                </tr>
            </thead>
            <tbody>
                @forelse($buildings->sortByDesc('offline_percentage') as $building)
                    <tr class="clickable-row" onclick="window.location.href='{{ route('alerts.offlineDevices', $building->building_id) }}'">
                        <td>
                            <i class="bi bi-building me-2 text-{{ $building->alert_level === 'red' ? 'danger' : ($building->alert_level === 'yellow' ? 'warning' : 'success') }}"></i>
                            {{ $building->name }}
                        </td>
                        <td>{{ $building->total_devices }}</td>
                        <td><span class="text-success">{{ $building->online_devices }}</span></td>
                        <td><span class="text-danger">{{ $building->offline_devices }}</span></td>
                        <td>{{ $building->offline_percentage }}%</td>
                        <td>
                            <span class="badge badge-{{ $building->alert_level }}">
                                @if($building->alert_level === 'red')
                                    Critical
                                @elseif($building->alert_level === 'yellow')
                                    Warning
                                @else
                                    Normal
                                @endif
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No buildings found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        
        <div class="mt-3">
            <small class="text-muted">
                <i class="bi bi-info-circle me-1"></i>
                <strong>Thresholds:</strong>
                Green &lt; {{ $alertSettings->lower_threshold }}% | 
                Yellow {{ $alertSettings->lower_threshold }}%-{{ $alertSettings->upper_threshold }}% | 
                Red &gt; {{ $alertSettings->upper_threshold }}%
            </small>
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buildingStatuses = {
        @foreach($buildings as $building)
            "{{ $building->name }}": "{{ $building->alert_level === 'red' ? 'critical' : ($building->alert_level === 'yellow' ? 'warning' : 'normal') }}",
        @endforeach
    };
    localStorage.setItem('buildingStatuses', JSON.stringify(buildingStatuses));
});
</script>

@endsection