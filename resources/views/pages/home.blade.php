@extends('components.layout.app')

@section('content')
<h4 class="fw-semibold mb-4">UPRM Campus Map - System Status</h4>

{{-- Main card container for the map --}}
<div class="card border-0 shadow-sm p-4 mb-4">

    {{-- === MAP WRAPPER WITH FIXED ASPECT RATIO === --}}
    {{-- Ensures the map maintains correct proportions across screen sizes --}}
    <div class="map-wrapper mx-auto position-relative rounded-3 overflow-hidden border">

        {{-- Campus Map Image --}}
        <img src="{{ asset('images/MapaRUM.jpeg') }}" alt="UPRM Campus Map" class="map-image">

        {{-- Legend overlay at top-left --}}
        <div class="position-absolute top-0 start-0 bg-white bg-opacity-90 border rounded shadow-sm m-3 p-2 small text-start" 
             style="line-height: 1.3; border-left: 3px solid #00844b;"> 
            <strong class="text-dark">Map Legend</strong><br>
            {{-- Color-coded status indicators --}}
            <span class="text-success">● Normal</span><br>
            <span class="text-warning">● Warning</span><br>
            <span class="text-danger">● Critical</span><br>
            <small class="text-muted">Click markers for details</small>
        </div>

        {{-- === INTERACTIVE MARKERS === --}}
        {{-- Each marker is positioned absolutely using percentage coordinates --}}
        {{-- To reposition markers, adjust the 'top' and 'left' percentages --}}
        <div class="marker" style="top: 71.3%; left: 78.3%;" title="Celis"></div> 
        <div class="marker" style="top: 56.5%; left: 82.5%;" title="Stefani"></div>
        <div class="marker" style="top: 18.5%; left: 72%;" title="Biologia"></div>
        <div class="marker" style="top: 78%; left: 76.7%;" title="DeDiego"></div>
        <div class="marker" style="top: 70%; left: 86.4%;" title="Luchetti"></div>
        <div class="marker" style="top: 63.5%; left: 85.4%;" title="ROTC"></div>
        <div class="marker" style="top: 13%; left: 33%;" title="Adm.Empresas"></div>
        <div class="marker" style="top: 78.5%; left: 67%;" title="Musa"></div>
        <div class="marker" style="top: 58.3%; left: 75.9%;" title="Chardon"></div>
        <div class="marker" style="top: 75.8%; left: 72.5%;" title="Monzon"></div>
        <div class="marker" style="top: 46.5%; left: 70.1%;" title="Sanchez Hidalgo"></div>
        <div class="marker" style="top: 41%; left: 76%;" title="Fisica"></div>
        <div class="marker" style="top: 40%; left: 78%;" title="Geologia"></div>
        <div class="marker" style="top: 39%; left: 80%;" title="Ciencias Marinas"></div>
        <div class="marker" style="top: 40%; left: 63%;" title="Quimica"></div>
        <div class="marker" style="top: 85%; left: 60.5%;" title="Piñero"></div>
        <div class="marker" style="top: 51.5%; left: 59%;" title="Enfermeria"></div>
        <div class="marker" style="top: 48%; left: 53%;" title="Vagones"></div>
        <div class="marker" style="top: 32.6%; left: 30.5%;" title="Natatorio"></div>
        <div class="marker" style="top: 18.2%; left: 86.5%;" title="Centro Nuclear"></div>
        <div class="marker" style="top: 64%; left: 46%;" title="Coliseo"></div>
        <div class="marker" style="top: 66.7%; left: 54.1%;" title="Gimnacio"></div>
        <div class="marker" style="top: 71%; left: 67%;" title="Servicios Medicos"></div>
        <div class="marker" style="top: 79%; left: 80.5%;" title="Decanato de Estudiantes"></div>
        <div class="marker" style="top: 49.7%; left: 66%;" title="Oficina de Facultad"></div>
        <div class="marker" style="top: 62%; left: 8%;" title="Adm.Finca Alzamora"></div>
        <div class="marker" style="top: 62.5%; left: 65.8%;" title="Biblioteca"></div>
        <div class="marker" style="top: 64.8%; left: 72.6%;" title="Centro de Estudiantes"></div>
        <div class="marker" style="top: 48%; left: 81%;" title="Terrats"></div>
        <div class="marker" style="top: 7.1%; left: 59.8%;" title="Ing.Civil"></div>
        <div class="marker" style="top: 49%; left: 78%;" title="Ing.Industrial"></div>
        <div class="marker" style="top: 17.7%; left: 55.7%;" title="Ing.Quimica"></div>
        <div class="marker" style="top: 38.1%; left: 50.9%;" title="Ing.Agricola"></div>
        <div class="marker" style="top: 26.9%; left: 18.2%;" title="Edificio A (Hotel Colegial)"></div>
        <div class="marker" style="top: 33%; left: 17.6%;" title="Edificio B (Adm.Peq.Negocios y Oficina Adm)"></div>
        <div class="marker" style="top: 26.8%; left: 21.8%;" title="Edificio C (Oficina de Extension Agricola)"></div>
        <div class="marker" style="top: 29.3%; left: 20%;" title="Edificio D"></div>
    </div>
</div>

{{-- === MAP CSS STYLING === --}}
<style>
/* Wrapper to maintain aspect ratio and map boundaries */
.map-wrapper {
    width: 100%;
    max-width: 1600px;
    aspect-ratio: 2202 / 1199; /* Original map dimensions */
    position: relative;
    overflow: hidden;
    border: 1px solid #dee2e6;
}

/* Map image should fully fit the wrapper */
.map-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
}

/* Markers are circular interactive elements */
.marker {
    position: absolute;
    width: 18px;
    height: 18px;
    background-color: #198754;
    border: 2px solid white;
    border-radius: 50%;
    cursor: pointer;
    transform: translate(-50%, -50%);
    transition: transform 0.15s ease, box-shadow 0.2s ease;
}

/* Hover effect for markers */
.marker:hover {
    transform: translate(-50%, -50%) scale(1.25);
    box-shadow: 0 0 6px rgba(0, 0, 0, 0.2);
    z-index: 10;
}
</style>

{{-- === ENABLE TOOLTIP ON MARKERS === --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Select all elements with a 'title' attribute to enable Bootstrap tooltip
    const tooltipTriggerList = document.querySelectorAll('[title]');
    tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
});
</script>

{{-- === MARKER CLICK REDIRECTION SCRIPT === --}}
<script>
window.addEventListener('load', () => {
    const markers = document.querySelectorAll('.marker');
    console.log(`✅ Found ${markers.length} markers`);

    markers.forEach(marker => {
        marker.style.cursor = 'pointer';
        marker.addEventListener('click', () => {
            // Use the title attribute to identify building name
            const building =
                marker.getAttribute('title') ||
                marker.getAttribute('aria-label') ||
                marker.getAttribute('data-bs-original-title');

            if (building) {
                console.log(`➡ Redirecting to: /alerts?building=${building}`);
                // Redirect to alerts page for selected building
                window.location.href = `/alerts?building=${encodeURIComponent(building)}`;
            } else {
                console.warn("⚠️ Marker without title:", marker);
            }
        });
    });
});
</script>

{{-- === TEMPORARY COORDINATE HELPER (COMMENTED OUT) === --}}
{{-- 
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapWrapper = document.querySelector('.map-wrapper');

    // Floating coordinate display for marker positioning
    const coordBox = document.createElement('div');
    coordBox.style.position = 'fixed';
    coordBox.style.bottom = '10px';
    coordBox.style.right = '10px';
    coordBox.style.background = 'rgba(0, 0, 0, 0.8)';
    coordBox.style.color = '#fff';
    coordBox.style.padding = '8px 12px';
    coordBox.style.borderRadius = '8px';
    coordBox.style.fontFamily = 'monospace';
    coordBox.style.fontSize = '0.9rem';
    coordBox.style.zIndex = '9999';
    coordBox.textContent = 'Move cursor over map...';
    document.body.appendChild(coordBox);

    // Update box with cursor position as percentages
    mapWrapper.addEventListener('mousemove', function (e) {
        const rect = mapWrapper.getBoundingClientRect();
        const top = ((e.clientY - rect.top) / rect.height) * 100;
        const left = ((e.clientX - rect.left) / rect.width) * 100;
        coordBox.textContent = `top: ${top.toFixed(1)}%; left: ${left.toFixed(1)}%;`;
    });

    mapWrapper.addEventListener('mouseleave', () => {
        coordBox.textContent = 'Move cursor over map...';
    });
});
</script>
--}}

@endsection
