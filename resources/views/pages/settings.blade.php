@extends('components.layout.app')

@section('content')
<div class="container-fluid">

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
