{{--
/*
 * File: home.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: Interactive campus map interface displaying building status markers
 * 
 * Author: [Hector R.Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   This page displays an interactive map of the UPRM campus with clickable building
 *   markers. Each marker represents a monitored building and displays its current
 *   device status through color coding.
 * 
 * Features:
 *   - Interactive UPRM campus map (2202x1199 px)
 *   - 37 building markers with positioning
 *   - Color-coded status indicators (green/yellow/red)
 *   - Click-to-navigate functionality to building details
 *   - Responsive design with fixed aspect ratio
 *   - Bootstrap tooltip integration
 * 
 * Marker Color Codes:
 *   - Green (#198754): Normal operation (<10% devices offline)
 *   - Yellow/Warning: Warning status (10-25% devices offline)
 *   - Red/Critical: Critical status (>25% devices offline)
 * 
 * Interaction:
 *   - Clicking a marker redirects to: /alerts?building={buildingName}
 *   - Hover displays building name via Bootstrap tooltip
 * 
 * Technical Notes:
 *   - Marker positions use percentage-based coordinates (top%, left%)
 *   - Map maintains 2202:1199 aspect ratio for accuracy
 *   - JavaScript event listeners enable marker interactivity
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3 (tooltips)
 *   - Campus map image: public/images/MapaRUM.jpeg
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 1016 software design description
 *   - Adheres to IEEE 730 software quality assurance
 */
--}}
@extends('components.layout.app')

@section('content')
<h4 class="fw-semibold mb-4">UPRM Campus Map - System Status</h4>

{{-- Control buttons for map management --}}
<div class="mb-3 d-flex align-items-center gap-3">
    {{-- Legend - inline with buttons --}}
    <div class="map-legend-inline">
        <strong class="text-dark">Map Legend:</strong>
        <span class="text-success ms-2">● Normal</span>
        <span class="text-warning ms-2">● Warning</span>
        <span class="text-danger ms-2">● Critical</span>
    </div>
    
    {{-- Admin buttons for marker management --}}
    <button id="addMarkerBtn" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add Marker
    </button>
    <button id="deleteMarkerBtn" class="btn btn-danger">
        <i class="bi bi-trash me-1"></i> Delete Marker
    </button>
    
    <button id="resetZoomBtn" class="btn btn-secondary">
        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset View
    </button>
</div>

{{-- Main card container for the map --}}
<div class="card border-0 shadow-sm p-4 mb-4">

    {{-- === RESPONSIVE MAP WRAPPER WITH ZOOM/PAN === --}}
    <div class="map-container" id="mapContainer">
        
        {{-- Map wrapper that scales --}}
        <div class="map-wrapper" id="mapWrapper">

            {{-- Campus Map Image --}}
            <img src="{{ asset('images/MapaRUM.jpeg') }}" alt="UPRM Campus Map" class="map-image" id="mapImage">

            {{-- === INTERACTIVE MARKERS === --}}
            <div class="markers-layer" id="markersLayer">
                {{-- Markers will be added dynamically via JavaScript --}}
            </div>
        </div>

        {{-- Zoom controls --}}
        <div class="zoom-controls">
            <button id="zoomInBtn" class="btn btn-sm btn-light mb-1" title="Zoom In">
                <i class="bi bi-plus-lg"></i>
            </button>
            <button id="zoomOutBtn" class="btn btn-sm btn-light" title="Zoom Out">
                <i class="bi bi-dash-lg"></i>
            </button>
        </div>
    </div>
</div>

{{-- MODAL: CREATE BUILDING --}}
<div class="modal fade" id="createBuildingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Create Building</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createBuildingForm">
                    {{-- Building Name --}}
                    <div class="mb-3">
                        <label for="buildingName" class="form-label fw-semibold">Name:</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="buildingName" 
                            placeholder="e.g. Stefani"
                            required
                        >
                    </div>

                    {{-- Networks Container --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Networks:</label>
                        <div id="networksContainer">
                            <div class="input-group mb-2">
                                <input 
                                    type="text" 
                                    class="form-control network-input" 
                                    placeholder="e.g. 10.100.100.0"
                                    required
                                >
                            </div>
                        </div>
                        <button type="button" class="btn btn-success btn-sm" id="addNetworkBtn">
                            <i class="bi bi-plus-circle me-1"></i> Add Network
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="saveBuildingBtn">
                    <i class="bi bi-check-circle me-1"></i> Save Building
                </button>
            </div>
        </div>
    </div>
</div>

{{-- === MAP CSS STYLING === --}}
<style>
/* Container for the map with overflow for panning */
.map-container {
    position: relative;
    width: 100%;
    max-width: 1200px; /* Reduced from 1600px to make map less wide */
    height: 500px;
    margin: 0 auto;
    overflow: auto;
    border: 1px solid #dee2e6;
    border-radius: 8px;
    background-color: #f8f9fa;
    cursor: grab;
}

.map-container:active {
    cursor: grabbing;
}

/* Wrapper to maintain aspect ratio */
.map-wrapper {
    position: relative;
    min-width: 100%;
    min-height: 100%;
    width: 2202px;
    height: 1199px;
    transform-origin: top left;
    transition: transform 0.1s ease-out;
}

/* Map image */
.map-image {
    width: 100%;
    height: 100%;
    object-fit: contain;
    user-select: none;
    pointer-events: none;
}

/* Markers layer */
.markers-layer {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

/* Individual markers */
.marker {
    position: absolute;
    width: 24px; /* Increased from 18px */
    height: 24px; /* Increased from 18px */
    background-color: #198754;
    border: 3px solid white; /* Increased from 2px */
    border-radius: 50%;
    cursor: pointer;
    transform: translate(-50%, -50%);
    transition: transform 0.15s ease, box-shadow 0.2s ease;
    pointer-events: all;
    z-index: 100;
}

.marker:hover {
    transform: translate(-50%, -50%) scale(1.4);
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
    z-index: 200;
}

/* Delete mode styling */
.marker.delete-mode {
    background-color: #dc3545 !important;
    animation: pulse 0.8s infinite;
}

@keyframes pulse {
    0%, 100% { transform: translate(-50%, -50%) scale(1); }
    50% { transform: translate(-50%, -50%) scale(1.2); }
}

/* Legend - inline with buttons */
.map-legend-inline {
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid #dee2e6;
    border-left: 3px solid #00844b;
    border-radius: 6px;
    padding: 8px 16px;
    font-size: 0.9rem;
    white-space: nowrap;
}

/* Zoom controls - FIXED position */
.zoom-controls {
    position: fixed;
    top: 180px;
    right: 20px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    z-index: 9999;
}

.zoom-controls button {
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
}

/* Responsive adjustments */
@media (max-width: 992px) {
    .d-flex.gap-3 {
        flex-wrap: wrap;
    }
    
    .map-legend-inline {
        font-size: 0.8rem;
        padding: 6px 12px;
    }
    
    .map-container {
        height: 450px;
    }
}

@media (max-width: 768px) {
    .map-container {
        height: 400px;
    }
    
    .map-legend-inline {
        width: 100%;
        text-align: center;
        margin-bottom: 0.5rem;
    }
    
    .zoom-controls {
        top: 160px;
        right: 10px;
    }
}

@media (max-width: 480px) {
    .map-container {
        height: 300px;
    }
    
    .marker {
        width: 28px; /* Increased from 24px for mobile */
        height: 28px;
        border: 3px solid white;
    }
    
    .map-legend-inline {
        font-size: 0.75rem;
        padding: 6px 10px;
    }
}
</style>

{{-- === INTERACTIVE MAP SCRIPT === --}}
<script>
// ===== GLOBAL VARIABLES =====
window.currentEditIndex = null;

document.addEventListener('DOMContentLoaded', function () {
    
    // ===== BUILDING STATUS DATA (synced with alerts page) =====
    const buildingStatuses = {
        "Stefani": "critical",
        "Biblioteca": "warning",
        "General Library": "warning",
        "Centro de Estudiantes": "normal",
        "Student Center": "normal",
        "Celis": "normal",
        "Biologia": "normal",
        "DeDiego": "normal",
        "Luchetti": "normal",
        "ROTC": "normal",
        "Adm.Empresas": "normal",
        "Musa": "normal",
        "Chardon": "normal",
        "Monzon": "normal",
        "Sanchez Hidalgo": "normal",
        "Fisica": "normal",
        "Geologia": "normal",
        "Ciencias Marinas": "normal",
        "Quimica": "normal",
        "Piñero": "normal",
        "Enfermeria": "normal",
        "Vagones": "normal",
        "Natatorio": "normal",
        "Centro Nuclear": "normal",
        "Coliseo": "normal",
        "Gimnacio": "normal",
        "Servicios Medicos": "normal",
        "Decanato de Estudiantes": "normal",
        "Oficina de Facultad": "normal",
        "Adm.Finca Alzamora": "normal",
        "Centro de Estudiantes": "normal",
        "Terrats": "normal",
        "Ing.Civil": "normal",
        "Ing.Industrial": "normal",
        "Ing.Quimica": "normal",
        "Ing.Agricola": "normal",
        "Edificio A (Hotel Colegial)": "normal",
        "Edificio B (Adm.Peq.Negocios y Oficina Adm)": "normal",
        "Edificio C (Oficina de Extension Agricola)": "normal",
        "Edificio D": "normal"
    };

    // Load statuses from localStorage if available
    const savedStatuses = localStorage.getItem('buildingStatuses');
    if (savedStatuses) {
        Object.assign(buildingStatuses, JSON.parse(savedStatuses));
    }

    // Function to get marker color based on status
    function getMarkerColor(buildingName) {
        const status = buildingStatuses[buildingName] || "normal";
        switch(status) {
            case "critical": return "#dc3545"; // Red
            case "warning": return "#ffc107";  // Yellow
            case "normal": return "#198754";   // Green
            default: return "#198754";
        }
    }
    
    // ===== DEFAULT MARKERS DATA =====
    const defaultMarkers = [
        { top: 71.3, left: 78.3, name: "Celis" },
        { top: 56.5, left: 82.5, name: "Stefani" },
        { top: 18.5, left: 72, name: "Biologia" },
        { top: 78, left: 76.7, name: "DeDiego" },
        { top: 70, left: 86.4, name: "Luchetti" },
        { top: 63.5, left: 85.4, name: "ROTC" },
        { top: 13, left: 33, name: "Adm.Empresas" },
        { top: 78.5, left: 67, name: "Musa" },
        { top: 58.3, left: 75.9, name: "Chardon" },
        { top: 75.8, left: 72.5, name: "Monzon" },
        { top: 46.5, left: 70.1, name: "Sanchez Hidalgo" },
        { top: 41, left: 76, name: "Fisica" },
        { top: 40, left: 78, name: "Geologia" },
        { top: 39, left: 80, name: "Ciencias Marinas" },
        { top: 40, left: 63, name: "Quimica" },
        { top: 85, left: 60.5, name: "Piñero" },
        { top: 51.5, left: 59, name: "Enfermeria" },
        { top: 48, left: 53, name: "Vagones" },
        { top: 32.6, left: 30.5, name: "Natatorio" },
        { top: 18.2, left: 86.5, name: "Centro Nuclear" },
        { top: 64, left: 46, name: "Coliseo" },
        { top: 66.7, left: 54.1, name: "Gimnacio" },
        { top: 71, left: 67, name: "Servicios Medicos" },
        { top: 79, left: 80.5, name: "Decanato de Estudiantes" },
        { top: 49.7, left: 66, name: "Oficina de Facultad" },
        { top: 62, left: 8, name: "Adm.Finca Alzamora" },
        { top: 62.5, left: 65.8, name: "Biblioteca" },
        { top: 64.8, left: 72.6, name: "Centro de Estudiantes" },
        { top: 48, left: 81, name: "Terrats" },
        { top: 7.1, left: 59.8, name: "Ing.Civil" },
        { top: 49, left: 78, name: "Ing.Industrial" },
        { top: 17.7, left: 55.7, name: "Ing.Quimica" },
        { top: 38.1, left: 50.9, name: "Ing.Agricola" },
        { top: 26.9, left: 18.2, name: "Edificio A (Hotel Colegial)" },
        { top: 33, left: 17.6, name: "Edificio B (Adm.Peq.Negocios y Oficina Adm)" },
        { top: 26.8, left: 21.8, name: "Edificio C (Oficina de Extension Agricola)" },
        { top: 29.3, left: 20, name: "Edificio D" }
    ];

    // Markers will be loaded from database
    let markers = [];
    let buildingsData = []; // Store full building data including IDs

    // ===== DOM ELEMENTS =====
    const mapContainer = document.getElementById('mapContainer');
    const mapWrapper = document.getElementById('mapWrapper');
    const markersLayer = document.getElementById('markersLayer');
    const addMarkerBtn = document.getElementById('addMarkerBtn');
    const deleteMarkerBtn = document.getElementById('deleteMarkerBtn');
    const resetZoomBtn = document.getElementById('resetZoomBtn');
    const zoomInBtn = document.getElementById('zoomInBtn');
    const zoomOutBtn = document.getElementById('zoomOutBtn');

    // ===== STATE VARIABLES =====
    let scale = 0.6; // Start with less zoom (was 1)
    let addMarkerMode = false;
    let deleteMarkerMode = false;
    let isPanning = false;
    let startX, startY, scrollLeft, scrollTop;

    // ===== RENDER MARKERS =====
    function renderMarkers() {
        markersLayer.innerHTML = '';
        
        markers.forEach((markerData, index) => {
            const marker = document.createElement('div');
            marker.className = 'marker';
            marker.style.top = `${markerData.top}%`;
            marker.style.left = `${markerData.left}%`;
            marker.title = markerData.name;
            marker.dataset.index = index;
            
            // Set marker color based on building status
            marker.style.backgroundColor = getMarkerColor(markerData.name);

            if (deleteMarkerMode) {
                marker.classList.add('delete-mode');
            }

            // Click handler
            marker.addEventListener('click', (e) => {
                e.stopPropagation();
                
                if (deleteMarkerMode) {
                    deleteMarker(index);
                } else {
                    window.location.href = `/alerts?building=${encodeURIComponent(markerData.name)}`;
                }
            });

            markersLayer.appendChild(marker);

            // Initialize tooltip
            new bootstrap.Tooltip(marker);
        });
    }

    // ===== UTILITY FUNCTIONS =====
    
    /**
     * Sanitize user input to prevent issues with special characters
     * Removes or escapes characters that could break JavaScript/HTML/SQL
     */
    function sanitizeInput(input) {
        if (!input) return '';
        
        // Remove potentially problematic characters
        return input
            .replace(/[<>]/g, '') // Remove HTML tags
            .replace(/[;'"\\]/g, '') // Remove semicolons, quotes, backslashes
            .replace(/\r?\n|\r/g, ' ') // Replace newlines with spaces
            .trim();
    }
    
    /**
     * Validate network address format (basic check)
     */
    function isValidNetwork(network) {
        // Basic IPv4 network format: XXX.XXX.XXX.XXX
        const ipPattern = /^(\d{1,3}\.){3}\d{1,3}$/;
        return ipPattern.test(network);
    }
    
    // ===== ADD MARKER FUNCTIONALITY =====
    addMarkerBtn.addEventListener('click', () => {
        addMarkerMode = !addMarkerMode;
        deleteMarkerMode = false;
        
        if (addMarkerMode) {
            addMarkerBtn.classList.add('active');
            deleteMarkerBtn.classList.remove('active');
            mapContainer.style.cursor = 'crosshair';
            alert('✅ Click on the map to place a new marker');
        } else {
            addMarkerBtn.classList.remove('active');
            mapContainer.style.cursor = 'grab';
        }
        
        renderMarkers();
    });

    // Click on map to add marker
    mapWrapper.addEventListener('click', (e) => {
        if (!addMarkerMode) return;
        
        // Ignore if this was a drag operation
        if (hasMoved) {
            hasMoved = false;
            return;
        }

        const rect = mapWrapper.getBoundingClientRect();
        
        // Calculate position relative to the map image at current scale
        const x = (e.clientX - rect.left) / scale;
        const y = (e.clientY - rect.top) / scale;
        
        const topPercent = (y / 1199) * 100;
        const leftPercent = (x / 2202) * 100;

        // Store the position temporarily
        window.pendingMarkerPosition = {
            top: parseFloat(topPercent.toFixed(1)),
            left: parseFloat(leftPercent.toFixed(1))
        };

        // Open the modal
        const modal = new bootstrap.Modal(document.getElementById('createBuildingModal'));
        modal.show();

        // Exit add mode (will be reactivated if cancelled)
        addMarkerMode = false;
        addMarkerBtn.classList.remove('active');
        mapContainer.style.cursor = 'grab';
    });

    // ===== DELETE MARKER FUNCTIONALITY =====
    deleteMarkerBtn.addEventListener('click', () => {
        deleteMarkerMode = !deleteMarkerMode;
        addMarkerMode = false;
        
        if (deleteMarkerMode) {
            deleteMarkerBtn.classList.add('active');
            addMarkerBtn.classList.remove('active');
            mapContainer.style.cursor = 'pointer';
            alert('🗑️ Click on any marker to delete it');
        } else {
            deleteMarkerBtn.classList.remove('active');
            mapContainer.style.cursor = 'grab';
        }
        
        renderMarkers();
    });

    async function deleteMarker(index) {
        const marker = markers[index];
        
        if (!marker.id) {
            alert('❌ Cannot delete this marker (no database ID)');
            return;
        }
        
        if (confirm(`Delete building "${marker.name}"?`)) {
            try {
                const response = await fetch(`/buildings/${marker.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Exit delete mode
                    deleteMarkerMode = false;
                    deleteMarkerBtn.classList.remove('active');
                    mapContainer.style.cursor = 'grab';
                    
                    alert(`✅ Building "${marker.name}" deleted`);
                    
                    // Reload buildings from database
                    await loadBuildingsFromDB();
                } else {
                    alert(`❌ Failed to delete building: ${result.message}`);
                }
            } catch (error) {
                console.error('Error deleting building:', error);
                alert('❌ Failed to delete building from database');
            }
        }
    }

    // ===== LOAD BUILDINGS FROM DATABASE =====
    async function loadBuildingsFromDB() {
        try {
            // Show loading message
            markersLayer.innerHTML = '<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.2);"><div class="spinner-border text-primary me-2" role="status"></div><span>Loading buildings...</span></div>';
            
            const response = await fetch('/buildings');
            if (!response.ok) throw new Error('Failed to load buildings');
            
            buildingsData = await response.json();
            
            // Convert to markers format
            markers = buildingsData.map(building => ({
                id: building.building_id,
                top: building.map_y,
                left: building.map_x,
                name: building.name,
                networks: building.networks ? building.networks.map(n => n.subnet) : []
            }));
            
            renderMarkers();
            console.log(`✅ Loaded ${markers.length} buildings from database`);
        } catch (error) {
            console.error('Error loading buildings:', error);
            markersLayer.innerHTML = '<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:#dc3545;color:white;padding:20px;border-radius:8px;">❌ Failed to load buildings</div>';
        }
    }

    // ===== ZOOM FUNCTIONALITY =====
    function updateZoom(newScale) {
        scale = Math.min(Math.max(newScale, 0.5), 3);
        mapWrapper.style.transform = `scale(${scale})`;
    }

    zoomInBtn.addEventListener('click', () => updateZoom(scale + 0.2));
    zoomOutBtn.addEventListener('click', () => updateZoom(scale - 0.2));

    // Mouse wheel zoom
    mapContainer.addEventListener('wheel', (e) => {
        e.preventDefault();
        const delta = e.deltaY > 0 ? -0.1 : 0.1;
        updateZoom(scale + delta);
    });

    resetZoomBtn.addEventListener('click', () => {
        updateZoom(0.6); // Reduced from 1 to 0.6 for less zoom
        mapContainer.scrollLeft = 0;
        mapContainer.scrollTop = 0;
    });

    // ===== PAN FUNCTIONALITY =====
    let clickStartX, clickStartY;
    let hasMoved = false;
    
    mapContainer.addEventListener('mousedown', (e) => {
        // Don't allow panning in add or delete mode, or when clicking on markers
        if (addMarkerMode || deleteMarkerMode || e.target.classList.contains('marker')) return;
        
        isPanning = true;
        hasMoved = false;
        mapContainer.style.cursor = 'grabbing';
        startX = e.pageX - mapContainer.offsetLeft;
        startY = e.pageY - mapContainer.offsetTop;
        clickStartX = e.pageX;
        clickStartY = e.pageY;
        scrollLeft = mapContainer.scrollLeft;
        scrollTop = mapContainer.scrollTop;
    });

    mapContainer.addEventListener('mouseleave', () => {
        isPanning = false;
        hasMoved = false; // Reset on mouse leave
        if (!addMarkerMode) mapContainer.style.cursor = 'grab';
    });

    mapContainer.addEventListener('mouseup', () => {
        isPanning = false;
        // Don't reset hasMoved here - let the click handler check it first
        if (!addMarkerMode) mapContainer.style.cursor = 'grab';
    });

    mapContainer.addEventListener('mousemove', (e) => {
        if (!isPanning) return;
        
        // Detect if mouse has moved significantly (more than 20px = it's a drag, not a click)
        // Increased threshold to 20px to be less sensitive and allow easier marker clicks
        const deltaX = Math.abs(e.pageX - clickStartX);
        const deltaY = Math.abs(e.pageY - clickStartY);
        if (deltaX > 20 || deltaY > 20) {
            hasMoved = true;
        }
        
        e.preventDefault();
        
        const x = e.pageX - mapContainer.offsetLeft;
        const y = e.pageY - mapContainer.offsetTop;
        const walkX = (x - startX) * 1.5;
        const walkY = (y - startY) * 1.5;
        
        mapContainer.scrollLeft = scrollLeft - walkX;
        mapContainer.scrollTop = scrollTop - walkY;
    });

    // ===== TOUCH SUPPORT FOR MOBILE =====
    let touchStartX, touchStartY;

    mapContainer.addEventListener('touchstart', (e) => {
        if (addMarkerMode) return;
        touchStartX = e.touches[0].pageX - mapContainer.scrollLeft;
        touchStartY = e.touches[0].pageY - mapContainer.scrollTop;
    });

    mapContainer.addEventListener('touchmove', (e) => {
        if (addMarkerMode) return;
        e.preventDefault();
        
        const x = e.touches[0].pageX;
        const y = e.touches[0].pageY;
        
        mapContainer.scrollLeft = touchStartX - x;
        mapContainer.scrollTop = touchStartY - y;
    });

    // ===== INITIAL RENDER =====
    updateZoom(0.6); // Apply initial zoom level
    
    // Load buildings from database on page load
    loadBuildingsFromDB();
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

// ===== CREATE BUILDING MODAL HANDLERS =====
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add Network button
    document.getElementById('addNetworkBtn').addEventListener('click', function() {
        const container = document.getElementById('networksContainer');
        
        const inputGroup = document.createElement('div');
        inputGroup.className = 'input-group mb-2';
        
        const newInput = document.createElement('input');
        newInput.type = 'text';
        newInput.className = 'form-control network-input';
        newInput.placeholder = 'e.g. 10.100.101.0';
        
        const deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.className = 'btn btn-outline-danger';
        deleteBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
        deleteBtn.onclick = function() {
            if (confirm('Remove this network input?')) {
                inputGroup.remove();
            }
        };
        
        inputGroup.appendChild(newInput);
        inputGroup.appendChild(deleteBtn);
        container.appendChild(inputGroup);
    });
    
    // Save Building button
    document.getElementById('saveBuildingBtn').addEventListener('click', async function() {
        const buildingNameRaw = document.getElementById('buildingName').value.trim();
        const buildingName = sanitizeInput(buildingNameRaw);
        const networkInputs = document.querySelectorAll('.network-input');
        const networks = [];
        const invalidNetworks = [];
        
        // Collect and validate all network values
        networkInputs.forEach(input => {
            const valueRaw = input.value.trim();
            const value = sanitizeInput(valueRaw);
            
            if (value) {
                // Validate network format
                if (isValidNetwork(value)) {
                    networks.push(value);
                } else {
                    invalidNetworks.push(value);
                }
            }
        });
        
        // Validate building name
        if (!buildingName) {
            alert('❌ Please enter a building name');
            return;
        }
        
        if (buildingName.length > 50) {
            alert('❌ Building name is too long (max 50 characters)');
            return;
        }
        
        // Validate networks
        if (networks.length === 0 && invalidNetworks.length === 0) {
            alert('❌ Please enter at least one network');
            return;
        }
        
        if (invalidNetworks.length > 0) {
            alert(`❌ Invalid network format(s): ${invalidNetworks.join(', ')}\n\nPlease use format: XXX.XXX.XXX.XXX`);
            return;
        }
        
        // Check for duplicate building name
        const duplicate = markers.find(m => m.name.toLowerCase() === buildingName.toLowerCase());
        
        if (duplicate) {
            if (!confirm(`⚠️ A building named "${buildingName}" already exists. Add anyway?`)) {
                return;
            }
        }
        
        // Get position from pending marker
        const position = window.pendingMarkerPosition;
        
        // Save to database via API
        const buildingData = {
            name: buildingName,
            map_x: position.left,
            map_y: position.top,
            networks: networks
        };
        
        // Close modal first
        const modal = bootstrap.Modal.getInstance(document.getElementById('createBuildingModal'));
        modal.hide();
        
        // Show loading
        const loadingMsg = document.createElement('div');
        loadingMsg.className = 'alert alert-info position-fixed top-0 start-50 translate-middle-x mt-3';
        loadingMsg.style.zIndex = '9999';
        loadingMsg.textContent = '⏳ Saving building...';
        document.body.appendChild(loadingMsg);
        
        try {
            const response = await fetch('/buildings', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(buildingData)
            });
            
            const result = await response.json();
            
            if (result.success) {
                alert(`✅ Building "${buildingName}" added successfully with ${networks.length} network(s)!`);
                // Reload buildings from database
                await loadBuildingsFromDB();
            } else {
                alert(`❌ Failed to save building: ${result.message}`);
            }
        } catch (error) {
            console.error('Error saving building:', error);
            alert('❌ Failed to save building to database');
        } finally {
            document.body.removeChild(loadingMsg);
        }
    });
    
    // Reset form when modal is closed
    document.getElementById('createBuildingModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('createBuildingForm').reset();
        const container = document.getElementById('networksContainer');
        container.innerHTML = `
            <input 
                type="text" 
                class="form-control mb-2 network-input" 
                placeholder="e.g. 10.100.100.0"
                required
            >
        `;
    });
});
</script>

@endsection

