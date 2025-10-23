@extends('components.layout.app')

@section('content')
<div class="container-fluid">
    <h4 class="fw-semibold mb-4">Reports</h4>

    {{-- REPORTS TAB --}}
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h5 class="fw-semibold mb-3">Device Reports Search</h5>

        {{-- Search Filters --}}
        <div class="row g-3 align-items-end mb-3">
            <div class="col-md-4">
                <label class="form-label">User</label>
                <input type="text" class="form-control bg-light" placeholder="Search by user name...">
            </div>
            <div class="col-md-4">
                <label class="form-label">MAC Address</label>
                <input type="text" class="form-control bg-light" placeholder="Search by MAC address...">
            </div>
            <div class="col-md-4">
                <label class="form-label">Building</label>
                <select class="form-select bg-light">
                    <option selected>All Buildings</option>
                    <option>Engineering Complex</option>
                    <option>Computer Science Department</option>
                    <option>Library</option>
                    <option>Student Center</option>
                </select>
            </div>
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-dark">
                <i class="bi bi-search me-2"></i> Search
            </button>
            <button class="btn btn-outline-secondary">
                <i class="bi bi-arrow-counterclockwise me-2"></i> Reset
            </button>
        </div>
    </div>

    {{-- SYSTEM OVERVIEW --}}
    <div class="card border-0 shadow-sm p-4">
        <h5 class="fw-semibold mb-3">System Overview</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="border rounded-3 p-3 text-center" style="background-color: #f0f6ff;">
                    <h6 class="fw-semibold">Total Devices</h6>
                    <h2 class="fw-bold text-primary mb-1">18</h2>
                    <p class="text-primary small mb-0">Registered in system</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded-3 p-3 text-center" style="background-color: #ecfdf5;">
                    <h6 class="fw-semibold">Active Now</h6>
                    <h2 class="fw-bold text-success mb-1">15</h2>
                    <p class="text-success small mb-0">Currently online</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded-3 p-3 text-center" style="background-color: #fffbea;">
                    <h6 class="fw-semibold">Inactive</h6>
                    <h2 class="fw-bold text-warning mb-1">3</h2>
                    <p class="text-warning small mb-0">Offline devices</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded-3 p-3 text-center" style="background-color: #ecfdf5;">
                    <h6 class="fw-semibold">Buildings</h6>
                    <h2 class="fw-bold text-success mb-1">12</h2>
                    <p class="text-success small mb-0">Monitored locations</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
