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
{{-- Error message display --}}
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        <strong>Access Denied:</strong> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- SYSTEM OVERVIEW --}}
<div class="card border-0 shadow-sm py-3 mb-4" style="max-width: min(1200px, 100%); margin: 0 auto;">
    <div class="d-flex justify-content-center align-items-center flex-wrap" style="gap: clamp(3rem, 6vw, 20rem);">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-hdd-network text-primary" style="font-size: 1.5rem;"></i>
            <div>
                <small class="text-muted d-block" style="font-size: 0.7rem;">Total Devices</small>
                <strong class="text-primary fs-4">{{ $stats['total_devices'] }}</strong>
            </div>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-check-circle text-success" style="font-size: 1.5rem;"></i>
            <div>
                <small class="text-muted d-block" style="font-size: 0.7rem;">Active Now</small>
                <strong class="text-success fs-4">{{ $stats['active_devices'] }}</strong>
            </div>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-x-circle text-danger" style="font-size: 1.5rem;"></i>
            <div>
                <small class="text-muted d-block" style="font-size: 0.7rem;">Inactive</small>
                <strong class="text-danger fs-4">{{ $stats['inactive_devices'] }}</strong>
            </div>
        </div>
        
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-building text-info" style="font-size: 1.5rem;"></i>
            <div>
                <small class="text-muted d-block" style="font-size: 0.7rem;">Buildings</small>
                <strong class="text-info fs-4">{{ $stats['total_buildings'] }}</strong>
            </div>
        </div>
    </div>
</div>

{{-- Control buttons for map management --}}
<div class="mb-3 d-flex justify-content-center align-items-center gap-3">
    {{-- Legend - inline with buttons --}}
    <div class="map-legend-inline">
        <strong class="text-dark">Map Legend:</strong>
        <span class="text-success ms-2">● Normal</span>
        <span class="text-warning ms-2">● Warning</span>
        <span class="text-danger ms-2">● Critical</span>
    </div>
    
    @php
        $isAdmin = strtolower(str_replace('_', '', auth()->user()->role ?? '')) === 'admin';
    @endphp
    
    {{-- Admin-only buttons for marker management --}}
    @if($isAdmin)
    <button id="addMarkerBtn" class="btn btn-primary">
        <i class="bi bi-plus-circle me-1"></i> Add Marker
    </button>
    <button id="editMarkerBtn" class="btn btn-warning">
        <i class="bi bi-pencil me-1"></i> Edit Marker
    </button>
    <button id="deleteMarkerBtn" class="btn btn-danger">
        <i class="bi bi-trash me-1"></i> Delete Marker
    </button>
    <button id="exitModeBtn" class="btn btn-secondary" style="display: none;">
        <i class="bi bi-x-circle me-1"></i> Exit Mode
    </button>
    @endif
    
    <button id="resetZoomBtn" class="btn btn-secondary">
        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset View
    </button>
</div>

{{-- Mode Indicator --}}
<div id="modeIndicator" class="alert alert-info align-items-center gap-2 mb-3" style="display: none;">
    <i class="bi bi-info-circle-fill fs-5"></i>
    <span id="modeIndicatorText" class="fw-semibold"></span>
</div>

{{-- Main card container for the map --}}
<div class="card border-0 shadow-sm p-4 mb-4">

    {{-- === RESPONSIVE MAP WRAPPER WITH ZOOM/PAN === --}}
    <div class="map-container" id="mapContainer">
        
        {{-- Zoom controls - positioned at left edge --}}
        <div class="zoom-controls">
            <button id="zoomInBtn" class="btn btn-sm btn-light mb-1" title="Zoom In">
                <i class="bi bi-plus-lg"></i>
            </button>
            <button id="zoomOutBtn" class="btn btn-sm btn-light" title="Zoom Out">
                <i class="bi bi-dash-lg"></i>
            </button>
        </div>
        
        {{-- Map wrapper that scales --}}
        <div class="map-wrapper" id="mapWrapper">

            {{-- Campus Map Image --}}
            <img src="{{ asset('images/MapaRUM.jpeg') }}" alt="UPRM Campus Map" class="map-image" id="mapImage">

            {{-- === INTERACTIVE MARKERS === --}}
            <div class="markers-layer" id="markersLayer">
                {{-- Markers will be added dynamically via JavaScript --}}
            </div>
        </div>
    </div>
</div>

{{-- MODAL: EDIT BUILDING --}}
<div class="modal fade" id="editBuildingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">Edit Building</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editBuildingForm">
                    <input type="hidden" id="editBuildingId">
                    
                    {{-- Building Name --}}
                    <div class="mb-3">
                        <label for="editBuildingName" class="form-label fw-semibold">Name:</label>
                        <input 
                            type="text" 
                            class="form-control" 
                            id="editBuildingName" 
                            placeholder="e.g. Stefani"
                            required
                        >
                    </div>

                    {{-- Position (optional to move marker) --}}
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-primary" id="moveMarkerCheck">
                            <i class="bi bi-pin-map me-1"></i> Move marker to a new position
                        </button>
                        <small class="text-muted d-block mt-2">Click this button, then click on the map to select a new position</small>
                    </div>

                    {{-- Networks Container --}}
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Current Networks:</label>
                        <div id="editNetworksContainer">
                            {{-- Networks will be loaded dynamically --}}
                        </div>
                        <small class="text-muted d-block mb-2">Click "Remove" to return a network to Action Required list</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Add Networks (Action Required):</label>
                        <div id="editNewNetworksContainer">
                            {{-- New networks to add --}}
                        </div>
                        <button type="button" class="btn btn-warning btn-sm" id="addEditNetworkBtn">
                            <i class="bi bi-plus-circle me-1"></i> Add Network
                        </button>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="updateBuildingBtn">
                    <i class="bi bi-check-circle me-1"></i> Update Building
                </button>
            </div>
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
                        <label class="form-label fw-semibold">Networks (Action Required):</label>
                        <div id="networksContainer">
                            <div class="input-group mb-2">
                                <select class="form-select network-select" required>
                                    <option value="">Select a network...</option>
                                </select>
                                <button type="button" class="btn btn-outline-danger remove-network-btn">
                                    <i class="bi bi-x-lg"></i>
                                </button>
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
    animation: pulse 0.8s infinite;
    cursor: pointer;
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

/* Zoom controls - positioned at left edge of map */
.zoom-controls {
    position: absolute;
    top: 10px;
    left: 10px;
    display: flex;
    flex-direction: column;
    gap: 5px;
    z-index: 1000;
}

.zoom-controls button {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    border: 1px solid #dee2e6;
}

.zoom-controls button:hover {
    background-color: #00844b;
    color: white;
    border-color: #00844b;
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
    
    // ===== BUILDING STATUS DATA (from controller - synced with alerts page) =====
    const buildingStatuses = {!! json_encode($buildings->pluck('alert_level', 'name')->toArray()) !!};
    const buildingStats = {!! json_encode($buildings->mapWithKeys(function($b) { 
        return [$b->name => [
            'online_devices' => $b->online_devices,
            'total_devices' => $b->total_devices,
            'offline_percentage' => $b->offline_percentage,
            'alert_level' => $b->alert_level
        ]]; 
    })->toArray()) !!};
    
    console.log('Building statuses loaded from controller:', buildingStatuses);
    console.log('Building stats loaded from controller:', buildingStats);

    // Function to get marker color based on status
    function getMarkerColor(buildingName) {
        const status = buildingStatuses[buildingName] || "green";
        switch(status) {
            case "red": return "#dc3545";    // Red - Critical
            case "yellow": return "#ffc107"; // Yellow - Warning
            case "green": return "#198754";  // Green - Normal
            default: return "#198754";
        }
    }
    
    // Function to get status label
    function getStatusLabel(alertLevel) {
        switch(alertLevel) {
            case "red": return "Critical";
            case "yellow": return "Warning";
            case "green": return "Normal";
            default: return "Normal";
        }
    }
    
    // Function to get status color class
    function getStatusColorClass(alertLevel) {
        switch(alertLevel) {
            case "red": return "text-danger";
            case "yellow": return "text-warning";
            case "green": return "text-success";
            default: return "text-success";
        }
    }

    // Markers will be loaded from database
    let markers = [];
    let buildingsData = []; // Store full building data including IDs
    let unassignedNetworks = []; // Store available networks for dropdowns
    window.unassignedNetworks = unassignedNetworks; // Make globally accessible
    const isAdmin = {{ $isAdmin ? 'true' : 'false' }};

    // ===== DOM ELEMENTS =====
    const mapContainer = document.getElementById('mapContainer');
    const mapWrapper = document.getElementById('mapWrapper');
    const markersLayer = document.getElementById('markersLayer');
    const addMarkerBtn = document.getElementById('addMarkerBtn');
    const editMarkerBtn = document.getElementById('editMarkerBtn');
    const deleteMarkerBtn = document.getElementById('deleteMarkerBtn');
    const exitModeBtn = document.getElementById('exitModeBtn');
    const resetZoomBtn = document.getElementById('resetZoomBtn');
    const zoomInBtn = document.getElementById('zoomInBtn');
    const zoomOutBtn = document.getElementById('zoomOutBtn');
    const modeIndicator = document.getElementById('modeIndicator');
    const modeIndicatorText = document.getElementById('modeIndicatorText');

    // ===== STATE VARIABLES =====
    let scale = 0.6; // Start with less zoom (was 1)
    let addMarkerMode = false;
    let editMarkerMode = false;
    let moveMarkerMode = false;
    let deleteMarkerMode = false;
    let isPanning = false;
    let startX, startY, scrollLeft, scrollTop;
    let editingMarkerId = null;
    let pendingEditPosition = null;

    // ===== MODE INDICATOR HELPER =====
    function showModeIndicator(mode) {
        if (modeIndicator && modeIndicatorText) {
            modeIndicatorText.textContent = `You are in ${mode} Mode`;
            
            // Change color based on mode
            modeIndicator.className = 'alert d-flex align-items-center gap-2 mb-3';
            if (mode === 'Add Marker') {
                modeIndicator.classList.add('alert-primary');
            } else if (mode === 'Edit Marker') {
                modeIndicator.classList.add('alert-warning');
            } else if (mode === 'Delete Marker') {
                modeIndicator.classList.add('alert-danger');
            }
            
            modeIndicator.style.display = 'flex';
        }
    }

    function hideModeIndicator() {
        if (modeIndicator) {
            modeIndicator.style.display = 'none';
            modeIndicator.classList.remove('d-flex');
        }
    }

    // ===== RENDER MARKERS =====
    function renderMarkers() {
        markersLayer.innerHTML = '';
        
        markers.forEach((markerData, index) => {
            const marker = document.createElement('div');
            marker.className = 'marker';
            marker.style.top = `${markerData.top}%`;
            marker.style.left = `${markerData.left}%`;
            marker.dataset.index = index;
            
            // Get building stats
            const stats = buildingStats[markerData.name] || { online_devices: 0, total_devices: 0, offline_percentage: 0, alert_level: 'green' };
            const onlinePercentage = stats.total_devices > 0 ? ((stats.online_devices / stats.total_devices) * 100).toFixed(1) : 0;
            const statusLabel = getStatusLabel(stats.alert_level);
            const statusColorClass = getStatusColorClass(stats.alert_level);
            
            // Create detailed tooltip with HTML
            marker.setAttribute('data-bs-toggle', 'tooltip');
            marker.setAttribute('data-bs-placement', 'top');
            marker.setAttribute('data-bs-html', 'true');
            marker.setAttribute('data-bs-title', `
                <div style="text-align: left; font-size: 0.875rem;">
                    <strong>${markerData.name}</strong><br>
                    <span class="${statusColorClass}">${statusLabel}</span><br>
                    <small>${onlinePercentage}% working</small>
                </div>
            `);
            
            // Set marker color based on building status (keep original color in all modes)
            marker.style.backgroundColor = getMarkerColor(markerData.name);

            // Only add delete-mode class for visual indicator (pulse animation)
            if (deleteMarkerMode) {
                marker.classList.add('delete-mode');
            }

            // Click handler
            marker.addEventListener('click', (e) => {
                e.stopPropagation();
                
                console.log('Marker clicked! Index:', index, 'Delete mode:', deleteMarkerMode, 'Edit mode:', editMarkerMode);
                
                if (isAdmin && deleteMarkerMode) {
                    deleteMarker(index);
                } else if (isAdmin && editMarkerMode) {
                    editMarker(index);
                } else {
                    // Redirect to building's offline devices page
                    window.location.href = `/alerts/building/${markerData.id}/offline`;
                }
            });

            markersLayer.appendChild(marker);

            // Initialize tooltip with HTML enabled
            new bootstrap.Tooltip(marker, { html: true });
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
    if (isAdmin && addMarkerBtn) {
        addMarkerBtn.addEventListener('click', async () => {
            addMarkerMode = !addMarkerMode;
            deleteMarkerMode = false;
            editMarkerMode = false;
            
            if (addMarkerMode) {
                // Load unassigned networks
                await loadUnassignedNetworks();
                
                addMarkerBtn.classList.add('active');
                if (editMarkerBtn) editMarkerBtn.classList.remove('active');
                if (deleteMarkerBtn) deleteMarkerBtn.classList.remove('active');
                if (exitModeBtn) exitModeBtn.style.display = 'inline-block';
                showModeIndicator('Add Marker');
                mapContainer.style.cursor = 'crosshair';
                customAlert('✅ Click on the map to place a new marker', 'Add Marker Mode', 'success');
            } else {
                addMarkerBtn.classList.remove('active');
                if (exitModeBtn) exitModeBtn.style.display = 'none';
                hideModeIndicator();
                mapContainer.style.cursor = 'grab';
            }
            
            renderMarkers();
        });
    }

    // Click on map to add marker OR move marker during edit
    mapWrapper.addEventListener('click', (e) => {
        console.log('🗺️ Map clicked! moveMarkerMode:', moveMarkerMode, 'editingMarkerId:', editingMarkerId, 'hasMoved:', hasMoved);
        
        // Handle add marker mode
        if (addMarkerMode) {
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

            // Populate the first network dropdown
            const firstSelect = document.querySelector('#networksContainer .network-select');
            if (firstSelect) {
                populateNetworkSelect(firstSelect, []);
            }
            
            // Update Add Network button state
            if (window.updateAddNetworkButtonState) {
                setTimeout(() => window.updateAddNetworkButtonState(), 100);
            }

            // Open the modal
            const modal = new bootstrap.Modal(document.getElementById('createBuildingModal'));
            modal.show();

            // Exit add mode (will be reactivated if cancelled)
            addMarkerMode = false;
            if (addMarkerBtn) addMarkerBtn.classList.remove('active');
            hideModeIndicator();
            mapContainer.style.cursor = 'grab';
            return;
        }
        
        // Handle move marker during edit mode
        if (moveMarkerMode && editingMarkerId) {
            console.log('✅ Move marker mode active - processing click');
            
            // Ignore if this was a drag operation
            if (hasMoved) {
                console.log('⚠️ Ignoring click - was a drag operation');
                hasMoved = false;
                return;
            }

            const rect = mapWrapper.getBoundingClientRect();
            
            // Calculate position relative to the map image at current scale
            const x = (e.clientX - rect.left) / scale;
            const y = (e.clientY - rect.top) / scale;
            
            console.log('📍 Click coordinates - clientX:', e.clientX, 'clientY:', e.clientY);
            console.log('📍 Rect - left:', rect.left, 'top:', rect.top);
            console.log('📍 Scale:', scale);
            console.log('📍 Calculated - x:', x, 'y:', y);
            
            const topPercent = (y / 1199) * 100;
            const leftPercent = (x / 2202) * 100;
            
            console.log('📍 Percentages - top:', topPercent, 'left:', leftPercent);

            // Store the new position for the marker being edited
            pendingEditPosition = {
                top: parseFloat(topPercent.toFixed(2)),
                left: parseFloat(leftPercent.toFixed(2))
            };
            
            console.log('✅ New marker position set:', pendingEditPosition);

            // Disable move marker mode
            moveMarkerMode = false;
            
            // Show temporary marker preview (optional)
            const previewMarker = document.createElement('div');
            previewMarker.id = 'previewMarker';
            previewMarker.className = 'marker';
            previewMarker.style.top = `${pendingEditPosition.top}%`;
            previewMarker.style.left = `${pendingEditPosition.left}%`;
            previewMarker.style.backgroundColor = '#ffc107';
            previewMarker.style.border = '3px solid #ff0000';
            previewMarker.style.zIndex = '999';
            
            // Remove any existing preview
            const existingPreview = document.getElementById('previewMarker');
            if (existingPreview) {
                existingPreview.remove();
            }
            
            markersLayer.appendChild(previewMarker);
            console.log('✅ Preview marker created at:', previewMarker.style.top, previewMarker.style.left);
            
            customAlert('✅ New position selected!\n\nYou can see a preview marker in yellow/red.\nClick "Update Building" to save the new position.', 'Position Selected', 'success');
            
            // Re-open the modal after position is selected
            const modal = new bootstrap.Modal(document.getElementById('editBuildingModal'));
            modal.show();
            
            return;
        }
        
        console.log('ℹ️ Click not processed - not in move marker mode');
    });

    // ===== EDIT MARKER FUNCTIONALITY =====
    if (isAdmin && editMarkerBtn) {
        editMarkerBtn.addEventListener('click', () => {
            editMarkerMode = !editMarkerMode;
            addMarkerMode = false;
            deleteMarkerMode = false;
            moveMarkerMode = false; // Reset move mode
            pendingEditPosition = null; // Reset pending position
            
            if (editMarkerMode) {
                editMarkerBtn.classList.add('active');
                if (addMarkerBtn) addMarkerBtn.classList.remove('active');
                if (deleteMarkerBtn) deleteMarkerBtn.classList.remove('active');
                if (exitModeBtn) exitModeBtn.style.display = 'inline-block';
                showModeIndicator('Edit Marker');
                mapContainer.style.cursor = 'crosshair'; // Changed from 'pointer' to 'crosshair'
                customAlert('✏️ Click on any marker to edit it', 'Edit Marker Mode', 'info');
            } else {
                editMarkerBtn.classList.remove('active');
                if (exitModeBtn) exitModeBtn.style.display = 'none';
                hideModeIndicator();
                mapContainer.style.cursor = 'grab';
                moveMarkerMode = false;
                editingMarkerId = null;
                pendingEditPosition = null;
            }
            
            renderMarkers();
        });
    }

    async function editMarker(index) {
        const marker = markers[index];
        
        console.log('📝 editMarker called - index:', index, 'marker:', marker);
        
        if (!marker.id) {
            customAlert('❌ Cannot edit this marker (no database ID)', 'Error', 'error');
            return;
        }
        
        editingMarkerId = marker.id;
        console.log('✅ editingMarkerId set to:', editingMarkerId);
        
        // Populate form with existing data FIRST (show modal immediately)
        document.getElementById('editBuildingId').value = marker.id;
        document.getElementById('editBuildingName').value = marker.name;
        
        // Show current networks (with remove buttons)
        const networksContainer = document.getElementById('editNetworksContainer');
        networksContainer.innerHTML = '';
        
        if (marker.networks && marker.networks.length > 0) {
            marker.networks.forEach(network => {
                const networkDiv = document.createElement('div');
                networkDiv.className = 'alert alert-info d-flex justify-content-between align-items-center mb-2';
                
                // Only show remove button if there's more than one network
                const removeButtonHtml = marker.networks.length > 1 
                    ? `<button type="button" class="btn btn-sm btn-danger remove-current-network" data-subnet="${network.subnet}">
                        <i class="bi bi-x-lg"></i> Remove
                    </button>`
                    : `<span class="badge bg-secondary">Last network - cannot remove</span>`;
                
                networkDiv.innerHTML = `
                    <span><i class="bi bi-hdd-network me-2"></i>${network.subnet}</span>
                    ${removeButtonHtml}
                `;
                networksContainer.appendChild(networkDiv);
            });
        } else {
            networksContainer.innerHTML = '<p class="text-muted">No networks assigned</p>';
        }
        
        // Clear new networks container
        const newNetworksContainer = document.getElementById('editNewNetworksContainer');
        newNetworksContainer.innerHTML = '';
        
        // Reset move marker mode
        moveMarkerMode = false;
        pendingEditPosition = null;
        
        // Show modal IMMEDIATELY (don't wait for network loading)
        const modal = new bootstrap.Modal(document.getElementById('editBuildingModal'));
        modal.show();
        
        // Load unassigned networks in background (for Add Network button)
        loadUnassignedNetworks().catch(err => {
            console.error('Failed to load unassigned networks:', err);
        });
    }

    function addEditNetworkField() {
        const container = document.getElementById('editNewNetworksContainer');
        const networkGroup = document.createElement('div');
        networkGroup.className = 'mb-2';
        
        const inputGroup = document.createElement('div');
        inputGroup.className = 'input-group';
        
        const select = document.createElement('select');
        select.className = 'form-select edit-network-select';
        select.required = true;
        
        // Get currently selected networks to exclude them
        const selectedNetworks = Array.from(container.querySelectorAll('.edit-network-select, .edit-network-manual-input'))
            .map(el => el.classList.contains('edit-network-select') ? el.value : el.value.trim())
            .filter(v => v && v !== '__manual__');
        
        // Populate with available networks AND manual option
        populateNetworkSelect(select, selectedNetworks, true);
        
        const removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'btn btn-danger';
        removeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
        removeBtn.onclick = () => {
            networkGroup.remove();
            refreshEditNetworkDropdowns();
        };
        
        // Manual input field (hidden by default)
        const manualInputGroup = document.createElement('div');
        manualInputGroup.className = 'input-group mt-2';
        manualInputGroup.style.display = 'none';
        
        const manualInput = document.createElement('input');
        manualInput.type = 'text';
        manualInput.className = 'form-control edit-network-manual-input';
        manualInput.placeholder = 'Enter network (e.g., 10.100.101.0)';
        
        const manualCancelBtn = document.createElement('button');
        manualCancelBtn.type = 'button';
        manualCancelBtn.className = 'btn btn-outline-secondary';
        manualCancelBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
        manualCancelBtn.onclick = () => {
            select.value = '';
            manualInput.value = '';
            manualInput.classList.remove('is-invalid', 'is-valid');
            manualInputGroup.style.display = 'none';
            select.style.display = 'block';
            feedback.style.display = 'none';
        };
        
        // Validation feedback
        const feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        feedback.style.display = 'none';
        
        // Network validation
        const networkRegex = /^(\d{1,3}\.){3}\d{1,3}$/;
        
        const validateManualNetwork = () => {
            const value = manualInput.value.trim();
            if (!value) {
                manualInput.classList.remove('is-invalid', 'is-valid');
                feedback.style.display = 'none';
                return false;
            }
            
            if (!networkRegex.test(value)) {
                manualInput.classList.add('is-invalid');
                manualInput.classList.remove('is-valid');
                feedback.style.display = 'block';
                feedback.textContent = 'Invalid network format. Use IPv4 format (e.g., 10.100.101.0)';
                return false;
            }
            
            const octets = value.split('.');
            const invalidOctet = octets.some(octet => {
                const num = parseInt(octet, 10);
                return num < 0 || num > 255;
            });
            
            if (invalidOctet) {
                manualInput.classList.add('is-invalid');
                manualInput.classList.remove('is-valid');
                feedback.style.display = 'block';
                feedback.textContent = 'Each octet must be between 0 and 255';
                return false;
            }
            
            manualInput.classList.remove('is-invalid');
            manualInput.classList.add('is-valid');
            feedback.style.display = 'none';
            return true;
        };
        
        manualInput.addEventListener('blur', validateManualNetwork);
        manualInput.addEventListener('input', validateManualNetwork);
        
        // Handle select change
        select.addEventListener('change', function() {
            if (this.value === '__manual__') {
                select.style.display = 'none';
                manualInputGroup.style.display = 'flex';
                manualInput.focus();
            } else {
                refreshEditNetworkDropdowns();
            }
        });
        
        manualInputGroup.appendChild(manualInput);
        manualInputGroup.appendChild(manualCancelBtn);
        
        inputGroup.appendChild(select);
        inputGroup.appendChild(removeBtn);
        
        networkGroup.appendChild(inputGroup);
        networkGroup.appendChild(manualInputGroup);
        networkGroup.appendChild(feedback);
        
        container.appendChild(networkGroup);
    }
    
    function refreshEditNetworkDropdowns() {
        const container = document.getElementById('editNewNetworksContainer');
        const allSelects = container.querySelectorAll('.edit-network-select');
        const allManualInputs = container.querySelectorAll('.edit-network-manual-input');
        
        const selectedValues = [
            ...Array.from(allSelects).map(s => s.value),
            ...Array.from(allManualInputs).map(i => i.value.trim())
        ].filter(v => v && v !== '__manual__');
        
        allSelects.forEach(select => {
            const currentValue = select.value;
            // Clear and repopulate with manual option
            populateNetworkSelect(select, selectedValues.filter(v => v !== currentValue), true);
            
            // Restore previous selection if it wasn't manual
            if (currentValue && currentValue !== '__manual__') {
                select.value = currentValue;
            }
        });
    }

    if (document.getElementById('addEditNetworkBtn')) {
        document.getElementById('addEditNetworkBtn').addEventListener('click', () => {
            addEditNetworkField();
        });
    }

    // Use event delegation for dynamically loaded button
    document.body.addEventListener('click', (e) => {
        if (e.target && e.target.id === 'moveMarkerCheck') {
            moveMarkerMode = true;
            console.log('🔄 Move marker button clicked - moveMarkerMode:', moveMarkerMode, 'editingMarkerId:', editingMarkerId);
            
            console.log('📍 Attempting to enable move marker mode...');
            
            // Close the modal so user can click on map
            const modal = bootstrap.Modal.getInstance(document.getElementById('editBuildingModal'));
            if (modal) {
                console.log('✅ Closing modal...');
                modal.hide();
            } else {
                console.log('⚠️ No modal instance found');
            }
            
            mapContainer.style.cursor = 'crosshair';
            console.log('✅ Move marker mode enabled - cursor set to crosshair, modal closed');
            console.log('📌 Current state - moveMarkerMode:', moveMarkerMode, 'editingMarkerId:', editingMarkerId);
            customAlert('📍 Click on the map to select a new position for this marker.\n\nThe modal will reopen after you select the position.', 'Move Marker', 'info');
        }
    });

    document.getElementById('updateBuildingBtn').addEventListener('click', async () => {
        const updateBtn = document.getElementById('updateBuildingBtn');
        const buildingId = document.getElementById('editBuildingId').value;
        const buildingNameRaw = document.getElementById('editBuildingName').value.trim();
        const buildingName = sanitizeInput(buildingNameRaw);
        
        if (!buildingName) {
            customAlert('❌ Please enter a building name', 'Validation Error', 'error');
            return;
        }
        
        if (buildingName.length > 50) {
            customAlert('❌ Building name is too long (max 50 characters)', 'Validation Error', 'error');
            return;
        }
        
        // Collect remaining current networks (ones not removed)
        const currentNetworkElements = document.querySelectorAll('#editNetworksContainer .alert');
        const currentNetworks = Array.from(currentNetworkElements).map(el => {
            const btn = el.querySelector('.remove-current-network');
            const badge = el.querySelector('.badge');
            // Get subnet from either button or badge (for last network case)
            if (btn) {
                return btn.dataset.subnet;
            } else if (badge) {
                // Extract subnet from the text content (before the badge)
                const textContent = el.querySelector('span:first-child').textContent;
                return textContent.trim();
            }
            return null;
        }).filter(n => n);
        
        // Collect newly added networks from dropdowns and manual inputs
        const newNetworkSelects = document.querySelectorAll('#editNewNetworksContainer .edit-network-select');
        const newNetworkManualInputs = document.querySelectorAll('#editNewNetworksContainer .edit-network-manual-input');
        const newNetworks = [];
        let hasInvalidNetwork = false;
        
        // Collect from dropdowns
        newNetworkSelects.forEach(select => {
            if (select.value && select.value !== '__manual__') {
                newNetworks.push(select.value);
            }
        });
        
        // Collect and validate from manual inputs
        newNetworkManualInputs.forEach(input => {
            const value = input.value.trim();
            if (value && input.parentElement.style.display !== 'none') {
                // Validate network format
                const networkRegex = /^(\d{1,3}\.){3}\d{1,3}$/;
                if (!networkRegex.test(value)) {
                    hasInvalidNetwork = true;
                    input.classList.add('is-invalid');
                    return;
                }
                
                // Validate octets
                const octets = value.split('.');
                const invalidOctet = octets.some(octet => {
                    const num = parseInt(octet, 10);
                    return num < 0 || num > 255;
                });
                
                if (invalidOctet) {
                    hasInvalidNetwork = true;
                    input.classList.add('is-invalid');
                    return;
                }
                
                newNetworks.push(value);
            }
        });
        
        if (hasInvalidNetwork) {
            customAlert('❌ Please enter valid network addresses in IPv4 format (e.g., 10.100.101.0)', 'Validation Error', 'error');
            return;
        }
        
        // Combine both lists (keeping existing + adding new)
        const networks = [...currentNetworks, ...newNetworks];
        
        if (networks.length === 0) {
            customAlert('❌ Building must have at least one network', 'Validation Error', 'error');
            return;
        }
        
        // Determine position - use pending position if marker was moved, otherwise keep current
        let position;
        if (pendingEditPosition) {
            position = pendingEditPosition;
            console.log('Using new position:', position);
        } else {
            // Find current marker position
            const currentMarker = markers.find(m => m.id == buildingId);
            if (currentMarker) {
                position = { top: currentMarker.top, left: currentMarker.left };
                console.log('Using current position:', position);
            } else {
                customAlert('❌ Could not find current marker position', 'Error', 'error');
                return;
            }
        }
        
        // Disable button to prevent double-clicks
        updateBtn.disabled = true;
        updateBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
        
        try {
            const payload = {
                name: buildingName,
                map_x: parseFloat(position.left),
                map_y: parseFloat(position.top),
                networks: networks
            };
            
            console.log('Sending update request:', payload);
            
            const response = await fetch(`/buildings/${buildingId}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify(payload)
            });
            
            console.log('Response status:', response.status);
            const result = await response.json();
            console.log('Response data:', result);
            
            if (result.success) {
                // Remove preview marker if exists
                const previewMarker = document.getElementById('previewMarker');
                if (previewMarker) {
                    previewMarker.remove();
                }
                
                // Close modal immediately
                const modal = bootstrap.Modal.getInstance(document.getElementById('editBuildingModal'));
                if (modal) {
                    modal.hide();
                }
                
                // Exit edit mode
                editMarkerMode = false;
                moveMarkerMode = false;
                editingMarkerId = null;
                pendingEditPosition = null;
                if (editMarkerBtn) editMarkerBtn.classList.remove('active');
                if (exitModeBtn) exitModeBtn.style.display = 'none';
                hideModeIndicator();
                mapContainer.style.cursor = 'grab';
                
                // Show success message immediately
                customAlert('✅ Building updated successfully!', 'Success', 'success');
                
                // Force reload data to get updated positions
                await loadBuildingsFromDB();
                if (isAdmin) {
                    await loadUnassignedNetworks();
                }
                
                // Force re-render markers
                renderMarkers();
            } else {
                customAlert('❌ Error: ' + (result.message || 'Failed to update building'), 'Error', 'error');
            }
        } catch (error) {
            console.error('Update error:', error);
            customAlert('❌ Failed to update building', 'Error', 'error');
        } finally {
            // Re-enable button
            updateBtn.disabled = false;
            updateBtn.innerHTML = '<i class="bi bi-check-circle me-1"></i> Update Building';
        }
    });
    
    // Handle remove current network button clicks (event delegation)
    document.getElementById('editNetworksContainer').addEventListener('click', (e) => {
        if (e.target.classList.contains('remove-current-network') || e.target.closest('.remove-current-network')) {
            const btn = e.target.classList.contains('remove-current-network') ? e.target : e.target.closest('.remove-current-network');
            const subnet = btn.dataset.subnet;
            
            // Check how many networks remain BEFORE removing
            const currentNetworkCount = document.querySelectorAll('#editNetworksContainer .alert').length;
            
            // Don't allow removal if it's the last network
            if (currentNetworkCount <= 1) {
                customAlert('❌ Cannot remove the last network!\n\nA building must have at least one network assigned.', 'Cannot Remove', 'warning');
                return;
            }
            
            customConfirm(`Remove network ${subnet} from this building?\n\nIt will be returned to the Action Required list.`, 'Remove Network').then(confirmed => {
                if (!confirmed) return;
                btn.closest('.alert').remove();
                
                // Check if only one network remains after removal
                const remainingNetworks = document.querySelectorAll('#editNetworksContainer .alert');
                
                if (remainingNetworks.length === 1) {
                    // Replace the remove button with a badge on the last remaining network
                    const lastNetworkAlert = remainingNetworks[0];
                    const removeBtn = lastNetworkAlert.querySelector('.remove-current-network');
                    if (removeBtn) {
                        removeBtn.outerHTML = '<span class="badge bg-secondary">Last network - cannot remove</span>';
                    }
                } else if (remainingNetworks.length === 0) {
                    document.getElementById('editNetworksContainer').innerHTML = '<p class="text-muted">No networks assigned</p>';
                }
            });
        }
    });

    // ===== DELETE MARKER FUNCTIONALITY =====
    if (isAdmin && deleteMarkerBtn) {
        deleteMarkerBtn.addEventListener('click', () => {
            deleteMarkerMode = !deleteMarkerMode;
            addMarkerMode = false;
            editMarkerMode = false;
            
            if (deleteMarkerMode) {
                deleteMarkerBtn.classList.add('active');
                if (addMarkerBtn) addMarkerBtn.classList.remove('active');
                if (editMarkerBtn) editMarkerBtn.classList.remove('active');
                if (exitModeBtn) exitModeBtn.style.display = 'inline-block';
                showModeIndicator('Delete Marker');
                mapContainer.style.cursor = 'pointer';
                customAlert('🗑️ Click on any marker to delete it. This will remove the building and all its networks from the database.', 'Delete Marker Mode', 'warning');
            } else {
                deleteMarkerBtn.classList.remove('active');
                if (exitModeBtn) exitModeBtn.style.display = 'none';
                hideModeIndicator();
                mapContainer.style.cursor = 'grab';
            }
            
            renderMarkers();
        });

        // Universal Exit Mode button handler
        if (exitModeBtn) {
            exitModeBtn.addEventListener('click', () => {
                // Exit all modes
                addMarkerMode = false;
                editMarkerMode = false;
                deleteMarkerMode = false;
                moveMarkerMode = false;
                editingMarkerId = null;
                pendingEditPosition = null;
                
                // Remove active state from all buttons
                if (addMarkerBtn) addMarkerBtn.classList.remove('active');
                if (editMarkerBtn) editMarkerBtn.classList.remove('active');
                if (deleteMarkerBtn) deleteMarkerBtn.classList.remove('active');
                
                // Hide exit button and mode indicator
                exitModeBtn.style.display = 'none';
                hideModeIndicator();
                
                // Reset cursor
                mapContainer.style.cursor = 'grab';
                
                // Re-render markers
                renderMarkers();
            });
        }
    }

    async function deleteMarker(index) {
        const marker = markers[index];
        
        if (!marker.id) {
            customAlert('❌ Cannot delete this marker (no database ID)', 'Error', 'error');
            return;
        }
        
        const networkCount = marker.networks?.length || 0;
        const confirmMsg = `⚠️ Delete building "${marker.name}"?\n\nThis will:\n- Remove the building from the map\n- Delete ${networkCount} network association(s)\n- Return networks to "Action Required" list\n\nThis action cannot be undone.`;
        
        const confirmed = await customConfirm(confirmMsg, 'Delete Building');
        if (confirmed) {
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
                    if (deleteMarkerBtn) deleteMarkerBtn.classList.remove('active');
                    if (exitModeBtn) exitModeBtn.style.display = 'none';
                    hideModeIndicator();
                    mapContainer.style.cursor = 'grab';
                    
                    customAlert(`✅ Building "${marker.name}" deleted successfully\n\n${networkCount} network(s) returned to Action Required list`, 'Success', 'success');
                    
                    // Reload buildings and unassigned networks
                    await loadBuildingsFromDB();
                    await loadUnassignedNetworks();
                } else {
                    customAlert(`❌ Failed to delete building: ${result.message}`, 'Error', 'error');
                }
            } catch (error) {
                console.error('Error deleting building:', error);
                customAlert('❌ Failed to delete building from database', 'Error', 'error');
            }
        }
    }

    // ===== LOAD UNASSIGNED NETWORKS =====
    async function loadUnassignedNetworks() {
        try {
            const response = await fetch('/networks/unassigned');
            if (!response.ok) throw new Error('Failed to load unassigned networks');
            
            unassignedNetworks = await response.json();
            window.unassignedNetworks = unassignedNetworks; // Update global reference
            console.log(`✅ Loaded ${unassignedNetworks.length} unassigned networks`);
        } catch (error) {
            console.error('Error loading unassigned networks:', error);
            unassignedNetworks = [];
            window.unassignedNetworks = [];
        }
    }
    
    // ===== POPULATE NETWORK SELECT DROPDOWN (GLOBAL) =====
    window.populateNetworkSelect = function(selectElement, excludeNetworkIds = [], includeManualOption = false) {
        selectElement.innerHTML = '<option value="">Select a network...</option>';
        
        const availableNetworks = unassignedNetworks.filter(net => 
            !excludeNetworkIds.includes(net.network_id)
        );
        
        selectElement.disabled = false;
        availableNetworks.forEach(network => {
            const option = document.createElement('option');
            option.value = network.subnet;
            option.textContent = `${network.subnet} (${network.total_devices || 0} devices)`;
            selectElement.appendChild(option);
        });
        
        // Add manual option if requested (for edit modal)
        if (includeManualOption) {
            const manualOption = document.createElement('option');
            manualOption.value = '__manual__';
            manualOption.textContent = '✏️ Enter manually...';
            selectElement.appendChild(manualOption);
        }
        
        // If no networks and no manual option, disable
        if (availableNetworks.length === 0 && !includeManualOption) {
            selectElement.innerHTML = '<option value="">No networks available</option>';
            selectElement.disabled = true;
        }
    };

    // ===== LOAD BUILDINGS FROM DATABASE =====
    async function loadBuildingsFromDB() {
        try {
            // Show loading message
            markersLayer.innerHTML = '<div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.2);"><div class="spinner-border text-primary me-2" role="status"></div><span>Loading buildings...</span></div>';
            
            const response = await fetch('/buildings');
            if (!response.ok) throw new Error('Failed to load buildings');
            
            buildingsData = await response.json();
            
            console.log('Raw buildings data from API:', buildingsData);
            
            // Convert to markers format
            markers = buildingsData.map(building => ({
                id: building.building_id,
                top: parseFloat(building.map_y) || 0,
                left: parseFloat(building.map_x) || 0,
                name: building.name,
                networks: building.networks || []
            }));
            
            console.log('Converted markers:', markers);
            
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
        // Don't allow panning in add, edit, or delete mode, or when clicking on markers
        if (addMarkerMode || editMarkerMode || deleteMarkerMode || e.target.classList.contains('marker')) return;
        
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
        if (!addMarkerMode && !editMarkerMode && !deleteMarkerMode) mapContainer.style.cursor = 'grab';
    });

    mapContainer.addEventListener('mouseup', () => {
        isPanning = false;
        // Don't reset hasMoved here - let the click handler check it first
        if (!addMarkerMode && !editMarkerMode && !deleteMarkerMode) mapContainer.style.cursor = 'grab';
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

    // ===== EDIT MODAL CLEANUP =====
    const editBuildingModal = document.getElementById('editBuildingModal');
    if (editBuildingModal) {
        editBuildingModal.addEventListener('hidden.bs.modal', function() {
            console.log('🚪 Modal hidden event - moveMarkerMode:', moveMarkerMode);
            
            // DON'T reset moveMarkerMode if we're in the middle of selecting a position
            // (modal was closed to allow map click, not by user canceling)
            if (!moveMarkerMode) {
                console.log('✅ Modal closed normally - cleaning up');
                
                // Remove preview marker when modal closes
                const previewMarker = document.getElementById('previewMarker');
                if (previewMarker) {
                    previewMarker.remove();
                }
                
                // Reset pending position only if not in move mode
                pendingEditPosition = null;
            } else {
                console.log('⏳ Modal closed for position selection - keeping moveMarkerMode active');
            }
            
            // Restore cursor if in edit mode
            if (editMarkerMode) {
                mapContainer.style.cursor = 'crosshair';
            }
        });
    }
    
    // ===== SAVE BUILDING BUTTON =====
    const saveBuildingBtn = document.getElementById('saveBuildingBtn');
    if (saveBuildingBtn) {
        saveBuildingBtn.addEventListener('click', async function() {
            const buildingNameRaw = document.getElementById('buildingName').value.trim();
            const buildingName = sanitizeInput(buildingNameRaw);
            const networkSelects = document.querySelectorAll('#networksContainer .network-select');
            const networks = [];
            
            // Collect all selected network values
            networkSelects.forEach(select => {
                const value = select.value.trim();
                if (value) {
                    networks.push(value);
                }
            });
            
            // Validate building name
            if (!buildingName) {
                customAlert('❌ Please enter a building name', 'Validation Error', 'error');
                return;
            }
            
            if (buildingName.length > 50) {
                customAlert('❌ Building name is too long (max 50 characters)', 'Validation Error', 'error');
                return;
            }
            
            // Validate networks
            if (networks.length === 0) {
                customAlert('❌ Please select at least one network from Action Required list', 'Validation Error', 'error');
                return;
            }
            
            // Check for duplicate building name
            const duplicate = markers.find(m => m.name.toLowerCase() === buildingName.toLowerCase());
            
            if (duplicate) {
                const confirmed = await customConfirm(`⚠️ A building named "${buildingName}" already exists. Add anyway?`, 'Duplicate Building');
                if (!confirmed) {
                    return;
                }
            }
            
            // Get position from pending marker
            const position = window.pendingMarkerPosition;
            
            if (!position) {
                customAlert('❌ No map position selected. Please click on the map first.', 'Error', 'error');
                return;
            }
            
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
                    customAlert(`✅ Building "${buildingName}" added successfully with ${networks.length} network(s)!`, 'Success', 'success');
                    // Reload buildings and unassigned networks
                    await loadBuildingsFromDB();
                    if (isAdmin) {
                        await loadUnassignedNetworks();
                    }
                } else {
                    customAlert(`❌ Failed to save building: ${result.message}`, 'Error', 'error');
                }
            } catch (error) {
                console.error('Error saving building:', error);
                customAlert('❌ Failed to save building to database', 'Error', 'error');
            } finally {
                if (document.body.contains(loadingMsg)) {
                    document.body.removeChild(loadingMsg);
                }
            }
        });
    }

    // ===== INITIAL RENDER =====
    updateZoom(0.6); // Apply initial zoom level
    
    // Load buildings and unassigned networks from database on page load
    loadBuildingsFromDB();
    if (isAdmin) {
        loadUnassignedNetworks();
    }
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
    coordBox.style.borderRadiu
onospace';
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add Network button
    document.getElementById('addNetworkBtn').addEventListener('click', function() {
        const container = document.getElementById('networksContainer');
        
        // Get currently selected networks to exclude them
        const selectedNetworks = Array.from(container.querySelectorAll('.network-select'))
            .map(s => s.value)
            .filter(v => v);
        
        // Check if there are available networks left
        const unassignedNetworks = window.unassignedNetworks || [];
        const availableCount = unassignedNetworks.length - selectedNetworks.length;
        
        if (availableCount <= 0) {
            customAlert('❌ No more networks available in Action Required list', 'No Networks Available', 'warning');
            return;
        }
        
        const inputGroup = document.createElement('div');
        inputGroup.className = 'input-group mb-2';
        
        const newSelect = document.createElement('select');
        newSelect.className = 'form-select network-select';
        newSelect.required = true;
        
        if (window.populateNetworkSelect) {
            window.populateNetworkSelect(newSelect, selectedNetworks);
        }
        
        const deleteBtn = document.createElement('button');
        deleteBtn.type = 'button';
        deleteBtn.className = 'btn btn-outline-danger remove-network-btn';
        deleteBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
        deleteBtn.onclick = function() {
            inputGroup.remove();
            refreshCreateNetworkDropdowns();
            updateAddNetworkButtonState();
        };
        
        // When a network is selected, refresh other dropdowns
        newSelect.addEventListener('change', function() {
            refreshCreateNetworkDropdowns();
            updateAddNetworkButtonState();
        });
        
        inputGroup.appendChild(newSelect);
        inputGroup.appendChild(deleteBtn);
        container.appendChild(inputGroup);
        
        // Update button state
        updateAddNetworkButtonState();
    });
    
    window.updateAddNetworkButtonState = function() {
        const container = document.getElementById('networksContainer');
        const addBtn = document.getElementById('addNetworkBtn');
        if (!container || !addBtn) return;
        
        const selectedNetworks = Array.from(container.querySelectorAll('.network-select'))
            .map(s => s.value)
            .filter(v => v);
        
        const unassignedNetworks = window.unassignedNetworks || [];
        const availableCount = unassignedNetworks.length - selectedNetworks.length;
        
        if (availableCount <= 0) {
            addBtn.disabled = true;
            addBtn.title = 'No more networks available';
        } else {
            addBtn.disabled = false;
            addBtn.title = `${availableCount} network(s) available`;
        }
    };
    
    function refreshCreateNetworkDropdowns() {
        const container = document.getElementById('networksContainer');
        const allSelects = container.querySelectorAll('.network-select');
        const selectedValues = Array.from(allSelects).map(s => s.value).filter(v => v);
        
        allSelects.forEach(select => {
            const currentValue = select.value;
            if (window.populateNetworkSelect) {
                window.populateNetworkSelect(select, selectedValues.filter(v => v !== currentValue));
                select.value = currentValue; // Restore selection
            }
        });
        
        // Update Add Network button state
        if (typeof updateAddNetworkButtonState === 'function') {
            updateAddNetworkButtonState();
        }
    }
    
    // Reset form when modal is closed
    document.getElementById('createBuildingModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('createBuildingForm').reset();
        const container = document.getElementById('networksContainer');
        container.innerHTML = `
            <div class="input-group mb-2">
                <select class="form-select network-select" required>
                    <option value="">Select a network...</option>
                </select>
                <button type="button" class="btn btn-outline-danger remove-network-btn">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
        `;
        
        // Re-populate the first dropdown
        const firstSelect = container.querySelector('.network-select');
        if (firstSelect && window.populateNetworkSelect) {
            window.populateNetworkSelect(firstSelect, []);
        }
        
        // Re-attach event listeners
        const removeBtn = container.querySelector('.remove-network-btn');
        if (removeBtn) {
            removeBtn.onclick = function() {
                // Can't remove the last network input
                const allInputs = container.querySelectorAll('.network-select');
                if (allInputs.length > 1) {
                    removeBtn.closest('.input-group').remove();
                    refreshCreateNetworkDropdowns();
                }
            };
        }
        
        // Update dropdown change listener
        if (firstSelect) {
            firstSelect.addEventListener('change', function() {
                refreshCreateNetworkDropdowns();
            });
        }
        
        // Update Add Network button state
        if (window.updateAddNetworkButtonState) {
            window.updateAddNetworkButtonState();
        }
    });
    
    function refreshCreateNetworkDropdowns() {
        const container = document.getElementById('networksContainer');
        const allSelects = container.querySelectorAll('.network-select');
        const selectedValues = Array.from(allSelects).map(s => s.value).filter(v => v);
        
        allSelects.forEach(select => {
            const currentValue = select.value;
            populateNetworkSelect(select, selectedValues.filter(v => v !== currentValue));
            select.value = currentValue; // Restore selection
        });
    }
});
</script>

@endsection

