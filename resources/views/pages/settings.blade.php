@extends('components.layout.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="fw-semibold">Threshold Settings</h4>
            <p class="text-muted mb-0">Configure alert thresholds for various system metrics</p>
        </div>
        <div>
            <button class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-clockwise me-1"></i> Reset
            </button>
            <button class="btn btn-success">
                <i class="bi bi-save2 me-1"></i> Save Changes
            </button>
        </div>
    </div>

    {{-- THRESHOLD CARDS --}}
    <div class="card border-0 shadow-sm p-4 mb-4">
        @php
            $thresholds = [
                ['title' => 'CPU Usage', 'desc' => 'CPU performance monitoring thresholds', 'warning' => 70, 'critical' => 90],
                ['title' => 'Memory Usage', 'desc' => 'Memory utilization thresholds', 'warning' => 80, 'critical' => 95],
                ['title' => 'Network Traffic', 'desc' => 'Network bandwidth monitoring thresholds', 'warning' => 75, 'critical' => 90],
                ['title' => 'Storage Usage', 'desc' => 'Storage capacity monitoring thresholds', 'warning' => 80, 'critical' => 95],
            ];
        @endphp

        @foreach ($thresholds as $item)
            <div class="mb-4">
                <h6 class="fw-semibold mb-1">{{ $item['title'] }}</h6>
                <p class="text-muted mb-2" style="font-size: 0.9rem;">{{ $item['desc'] }}</p>

                <div class="row align-items-center mb-2">
                    <div class="col-md-3">
                        <label class="form-label text-muted small mb-1">Warning Level</label>
                        <div class="input-group">
                            <input type="number" class="form-control form-control-sm text-center" value="{{ $item['warning'] }}" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Range: 0 - 100 %</small>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label text-muted small mb-1">Critical Level</label>
                        <div class="input-group">
                            <input type="number" class="form-control form-control-sm text-center" value="{{ $item['critical'] }}" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Range: 0 - 100 %</small>
                    </div>
                </div>
                @if (!$loop->last)
                    <hr>
                @endif
            </div>
        @endforeach
    </div>

    {{-- ALERT DISPLAY SETTINGS --}}
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h5 class="fw-semibold mb-3">Alert Display Settings</h5>
        <p class="text-muted" style="font-size: 0.9rem;">Choose how alerts are sorted in the Alerts tab</p>

        <div class="form-check mb-2">
            <input class="form-check-input" type="radio" name="sortOrder" id="bySeverity" checked>
            <label class="form-check-label fw-semibold" for="bySeverity">By Severity</label>
            <p class="text-muted small ms-4 mb-0">Critical alerts first, then warnings, then normal</p>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="radio" name="sortOrder" id="alphabetical">
            <label class="form-check-label fw-semibold" for="alphabetical">Alphabetically</label>
            <p class="text-muted small ms-4 mb-0">Sort alerts by building name (A–Z)</p>
        </div>
    </div>

    {{-- NOTIFICATION SETTINGS --}}
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h5 class="fw-semibold mb-3">Notification Settings</h5>

        <div class="form-check form-switch mb-2">
            <input class="form-check-input" type="checkbox" id="emailNotifications" checked>
            <label class="form-check-label fw-semibold" for="emailNotifications">Email Notifications</label>
            <p class="text-muted small ms-4 mb-0">Receive alerts via email</p>
        </div>

        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="pushNotifications">
            <label class="form-check-label fw-semibold" for="pushNotifications">Push Notifications</label>
            <p class="text-muted small ms-4 mb-0">Browser push notifications</p>
        </div>
    </div>
</div>
@endsection
