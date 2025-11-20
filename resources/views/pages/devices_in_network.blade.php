@extends('components.layout.app')

@section('content')
<style>
    .card {
        border-radius: 12px;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }

    .table-hover tbody tr:hover {
        background-color: #f1f3f4;
        cursor: pointer;
    }
</style>

<div class="container-fluid py-4">
  <div class="card border-0 shadow-sm p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h5 class="fw-semibold mb-0">Devices in Network: {{ $network }}</h5>
        <small class="text-muted">
          <i class="bi bi-building me-1"></i>{{ $building->name }} → {{ $network }}
        </small>
        <br>
        <small class="text-muted">Click on any device to view its daily activity graph (updates every 5 minutes)</small>
      </div>
      <a href="{{ route('devices.byBuilding', ['building' => $building->building_id]) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Networks
      </a>
    </div>

    {{-- Search bar for filtering devices --}}
    <div class="mb-3 d-flex gap-2">
      <input type="text" id="deviceSearch" class="form-control form-control-sm" placeholder="Search devices... (use commas: monzon, 4542ab)" style="max-width: 400px;">
      <button type="button" class="btn btn-outline-secondary btn-sm px-2" onclick="document.getElementById('deviceSearch').value=''; filterDevices();">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>IP Address</th>
            <th>MAC Address</th>
            <th>Owner</th>
            <th>Extensions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($devices as $d)
            @php
              $exts = ($extByDevice ?? collect())->get($d->device_id) ?? collect();
            @endphp
            <tr class="device-row" onclick="showDeviceGraph('{{ $d->ip_address }}', '{{ $d->device_id }}', '{{ $building->name }}', '{{ $network }}')" style="cursor: pointer;">
              <td class="fw-semibold device-ip">{{ $d->ip_address }}</td>
              <td class="device-mac">{{ $d->mac_address ?? 'N/A' }}</td>
              <td class="device-owner">
                @if($exts->isNotEmpty())
                  {{ $exts->first()->user_first_name }} {{ $exts->first()->user_last_name }}
                @else
                  <span class="text-muted">N/A</span>
                @endif
              </td>
              <td class="device-extensions">
                @if($exts->isEmpty())
                  <span class="text-muted">—</span>
                @else
                  @foreach($exts as $e)
                    {{ $e->extension_number }}@if(!$loop->last), @endif
                  @endforeach
                @endif
              </td>
            </tr>
          @empty
            <tr class="empty-devices-row">
              <td colspan="4" class="text-center text-muted">No devices found in this network.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- MODAL: DEVICE ACTIVITY GRAPH --}}
<div class="modal fade" id="graphModal" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Daily Activity: <span id="modalDeviceId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="btn-group mb-3" role="group">
                    <button type="button" class="btn btn-success active" id="btnToday" onclick="showDay(1)">
                        <i class="bi bi-calendar-check me-1"></i> Today
                    </button>
                    <button type="button" class="btn btn-outline-success" id="btnYesterday" onclick="showDay(2)">
                        <i class="bi bi-calendar me-1"></i> Yesterday
                    </button>
                </div>
                
                <div id="chartContainer">
                    <canvas id="activityChart" width="400" height="150"></canvas>
                </div>
                
                <div class="mt-3 text-muted small">
                    <i class="bi bi-info-circle me-1"></i>
                    Showing device activity with 5-minute intervals (288 samples per day).
                    Data updates every 5 minutes via ETL cron job.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
let activityChart = null;
let currentDeviceId = null;
let activityData = { today: null, yesterday: null };

// Filter devices based on search input
function filterDevices() {
    const searchInput = document.getElementById('deviceSearch').value.toLowerCase().trim();
    const rows = document.querySelectorAll('.device-row');
    let visibleCount = 0;

    // Split search terms by comma and normalize each term
    const searchTerms = searchInput.split(',').map(term => term.trim().replace(/[^a-z0-9]/g, '')).filter(term => term !== '');

    rows.forEach(row => {
        // Get all text content from the row and normalize it
        const ip = (row.querySelector('.device-ip')?.textContent || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        const mac = (row.querySelector('.device-mac')?.textContent || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        const owner = (row.querySelector('.device-owner')?.textContent || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        const extensions = (row.querySelector('.device-extensions')?.textContent || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        
        // Combine all fields into one searchable string
        const combinedText = ip + mac + owner + extensions;
        
        // Check if ALL search terms are found in the combined text
        const matches = searchTerms.length === 0 || searchTerms.every(term => combinedText.includes(term));

        if (matches) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Update empty message
    const emptyRow = document.querySelector('.empty-devices-row');
    if (emptyRow) {
        emptyRow.style.display = visibleCount === 0 ? '' : 'none';
    }
}

// Attach search event listener
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('deviceSearch');
    if (searchInput) {
        searchInput.addEventListener('input', filterDevices);
    }
});

// Show device activity graph in modal
async function showDeviceGraph(ip, deviceId, building, network) {
    // Set modal title
    document.getElementById('modalDeviceId').textContent = `${deviceId} (${ip}) - ${building} → ${network}`;
    
    // Store current device ID
    currentDeviceId = deviceId;
    
    // Reset button states
    document.getElementById('btnToday').classList.add('active', 'btn-success');
    document.getElementById('btnToday').classList.remove('btn-outline-success');
    document.getElementById('btnYesterday').classList.remove('active', 'btn-success');
    document.getElementById('btnYesterday').classList.add('btn-outline-success');
    
    // Show loading state
    const chartContainer = document.getElementById('chartContainer');
    chartContainer.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading activity data...</p></div>';
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('graphModal'));
    modal.show();
    
    try {
        // Fetch both days of activity data
        const response = await fetch(`/api/device-activity/${deviceId}/both`);
        console.log('[DEBUG] API fetch response:', response);
        let json = null;
        try {
            json = await response.json();
        } catch (jsonErr) {
            console.error('[DEBUG] Error parsing JSON:', jsonErr);
        }
        console.log('[DEBUG] API JSON recibido:', json);
        if (!response.ok || !json) {
            throw new Error('Failed to load activity data');
        }
        activityData = json;
        // Restore canvas
        chartContainer.innerHTML = '<canvas id="activityChart" width="400" height="150"></canvas>';
        // Show today's data by default
        showDay(1);
    } catch (error) {
        console.error('[DEBUG] Error loading activity data:', error);
        chartContainer.innerHTML = `
            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle me-2"></i>
                Unable to load activity data. The device may be new or data collection is still in progress.
            </div>
        `;
    }
}

// Show specific day's activity data
function showDay(dayNumber) {
    // Update button states
    if (dayNumber === 1) {
        document.getElementById('btnToday').classList.add('active', 'btn-success');
        document.getElementById('btnToday').classList.remove('btn-outline-success');
        document.getElementById('btnYesterday').classList.remove('active', 'btn-success');
        document.getElementById('btnYesterday').classList.add('btn-outline-success');
    } else {
        document.getElementById('btnYesterday').classList.add('active', 'btn-success');
        document.getElementById('btnYesterday').classList.remove('btn-outline-success');
        document.getElementById('btnToday').classList.remove('active', 'btn-success');
        document.getElementById('btnToday').classList.add('btn-outline-success');
    }
    
    const data = dayNumber === 1 ? activityData.today : activityData.yesterday;
    // Si no hay data o no hay samples, mostrar error. Si hay aunque sea un punto, mostrar la gráfica.
    if (!data || !Array.isArray(data.samples) || data.samples.length === 0) {
        document.getElementById('chartContainer').innerHTML = `
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                No data available for ${dayNumber === 1 ? 'today' : 'yesterday'}.
            </div>
        `;
        console.log('[DEBUG] No data for graph:', { dayNumber, data });
        return;
    }
    // Debug: mostrar el contenido de samples y el objeto completo
    console.log('[DEBUG] Mostrando gráfica:', {
        dayNumber,
        samples: data.samples,
        samplesLength: data.samples.length,
        first20: data.samples.slice(0, 20),
        allData: data
    });
    // Asegura que el canvas exista
    if (!document.getElementById('activityChart')) {
        document.getElementById('chartContainer').innerHTML = '<canvas id="activityChart" width="400" height="150"></canvas>';
    }
    renderChart(data);
}

// Render the activity chart
function renderChart(data) {
    // Destroy existing chart if any
    if (activityChart) {
        activityChart.destroy();
    }
    
    const samples = data.samples;
    const isLive = data.is_live === true; // Today uses live status
    const currentStatus = data.current_status; // 'online' or 'offline'
    const isToday = data.day_number === 1;
    
    // Use the current sample index provided by the server to avoid timezone issues
    // For yesterday, show all samples (0-287)
    let currentSampleIndex = 287; // Default to all samples
    if (isToday && data.current_sample_index !== undefined) {
        currentSampleIndex = data.current_sample_index;
    }
    
    // Create labels for 288 samples (00:00, 00:05, 00:10, ... 23:55)
    const labels = [];
    for (let i = 0; i < 288; i++) {
        const hours = Math.floor(i * 5 / 60);
        const minutes = (i * 5) % 60;
        labels.push(`${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`);
    }
    
    // Process samples for chart:
    // - Today: show only points where val === 1 (recorded), use current status
    // - Yesterday: show all points (1=online, 0=offline)
    const displaySamples = samples.map((val, index) => {
        if (isToday) {
            // Don't show future time slots
            if (index > currentSampleIndex) return null;
            // Only show points where val === 1 (recorded)
            if (val === 1) return currentStatus === 'online' ? 1 : 0;
            return null;
        } else {
            // Yesterday: show all points (historical)
            return val;
        }
    });
    
    // Debug output
    console.log('Chart Debug Info:', {
        isLive: isLive,
        currentStatus: currentStatus,
        currentSampleIndex: currentSampleIndex,
        totalSamples: samples.length,
        recordedSamples: samples.filter(v => v === 1).length,
        displaySamples: displaySamples.filter(v => v !== null).length,
        sampleValues: displaySamples.filter(v => v !== null)
    });
    
    // Point colors based on status
    const pointColors = displaySamples.map(val => {
        if (val === null) return 'rgba(0,0,0,0)'; // Transparent for null
        return val === 1 ? '#00844b' : '#dc3545'; // Green for online, red for offline
    });
    
    // Create new chart
    const ctx = document.getElementById('activityChart').getContext('2d');
    activityChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Device Status',
                data: displaySamples,
                borderColor: '#00844b',
                backgroundColor: 'rgba(0, 132, 75, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: pointColors,
                pointBorderColor: pointColors,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0,
                stepped: true,
                spanGaps: false, // Don't connect lines across null values
                segment: {
                    borderColor: ctx => {
                        // Color the line based on the value
                        const value = ctx.p0.parsed.y;
                        return value === 1 ? '#00844b' : '#dc3545';
                    }
                }
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 1.1,
                    min: -0.1,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return value === 1 ? 'Active' : value === 0 ? 'Inactive' : '';
                        }
                    },
                    grid: {
                        color: function(context) {
                            return context.tick.value === 0.5 ? 'transparent' : 'rgba(0, 0, 0, 0.1)';
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: `Time (${data.activity_date})`
                    },
                    ticks: {
                        maxRotation: 45,
                        minRotation: 45,
                        autoSkip: true,
                        maxTicksLimit: 24
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: function(context) {
                            return `Time: ${context[0].label}`;
                        },
                        label: function(context) {
                            const status = context.parsed.y === 1 ? 'Active' : 'Inactive';
                            const type = isLive ? ' (Live)' : ' (Historical)';
                            return 'Status: ' + status + type;
                        }
                    }
                }
            },
            interaction: {
                intersect: false,
                mode: 'index'
            }
        }
    });
}
</script>
@endsection