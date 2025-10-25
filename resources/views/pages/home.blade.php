@extends('components.layout.app')

@section('content')
<h4 class="fw-semibold mb-4">UPRM Campus Map - System Status</h4>

<div class="card border-0 shadow-sm p-4 mb-4">
    <div class="position-relative bg-white rounded-3 overflow-hidden" 
         style="border: 1px solid #dee2e6; height: 75vh;"> <!-- Reduced height -->

        <!-- Campus Map -->
        <img src="{{ asset('images/MapaRUM.jpeg') }}" 
             alt="UPRM Campus Map" 
             class="position-absolute top-0 start-0 w-100 h-100" 
             style="object-fit: contain;">

        <!-- Legend -->
        <div class="position-absolute top-0 start-0 bg-white bg-opacity-90 border rounded shadow-sm m-3 p-2 small text-start" 
             style="line-height: 1.3; border-left: 3px solid #00844b;">
            <strong class="text-dark">Map Legend</strong><br>
            <span class="text-success">● Normal</span><br>
            <span class="text-warning">● Warning</span><br>
            <span class="text-danger">● Critical</span><br>
            <small class="text-muted">Click markers for details</small>
        </div>

        <!-- Example marker (Celis) -->
        <div class="position-absolute" style="top: 43%; left: 57%;" data-bs-toggle="tooltip" title="Celis">
            <div class="rounded-circle bg-success" style="width: 18px; height: 18px; border: 2px solid white; cursor: pointer;"></div>
        </div>

    </div>
</div>

<script>
    // Enable tooltips for hover effect
    document.addEventListener('DOMContentLoaded', function () {
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        [...tooltipTriggerList].map(el => new bootstrap.Tooltip(el));
    });
</script>
@endsection
