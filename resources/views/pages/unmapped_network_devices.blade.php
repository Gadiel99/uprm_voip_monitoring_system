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

    /* Pagination theme styling */
    .pagination .page-link {
        color: #00844b;
        border-color: #dee2e6;
    }

    .pagination .page-link:hover {
        color: #006f3f;
        background-color: #e6f9ed;
        border-color: #00844b;
    }

    .pagination .page-item.active .page-link {
        background-color: #00844b;
        border-color: #00844b;
        color: white;
    }

    .pagination .page-item.disabled .page-link {
        color: #6c757d;
    }
</style>

<div class="container-fluid py-4">
  <div class="card border-0 shadow-sm p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h5 class="fw-semibold mb-0">Devices in Network: {{ $network }}</h5>
        <small class="text-muted">
          <i class="bi bi-exclamation-circle me-1 text-warning"></i>{{ $building->name }} → {{ $network }}
        </small>
        <br>
        <small class="text-muted">Click on any device to view its 30-day activity graph</small>
      </div>
      <a href="{{ route('devices.unmapped') }}" class="btn btn-outline-secondary btn-sm">
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
                  <div class="d-flex flex-wrap gap-1">
                    @foreach($exts as $e)
                      <span class="badge bg-light text-dark border">
                        {{ $e->extension_number }}
                      </span>
                    @endforeach
                  </div>
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

    {{-- Pagination Links --}}
    @if($devices->hasPages())
      <div class="mt-4">
        {{ $devices->links('vendor.pagination.custom-bootstrap-5') }}
      </div>
    @endif
  </div>
</div>

{{-- MODAL: DEVICE ACTIVITY GRAPH --}}
<div class="modal fade" id="graphModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">30-Day Activity: <span id="modalDeviceId"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <canvas id="activityChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
let activityChart = null;

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
function showDeviceGraph(ip, deviceId, building, network) {
    // Set modal title
    document.getElementById('modalDeviceId').textContent = `${deviceId} (${ip}) - ${building} → ${network}`;
    
    // Generate random 30-day activity data (0 or 1)
    const days = Array.from({length: 30}, (_, i) => i + 1);
    const activityData = Array.from({length: 30}, () => Math.random() > 0.2 ? 1 : 0);
    
    // Point colors: green for active (1), red for inactive (0)
    const pointColors = activityData.map(val => val === 1 ? '#00844b' : '#dc3545');
    
    // Destroy existing chart if any
    if (activityChart) {
        activityChart.destroy();
    }
    
    // Create new chart
    const ctx = document.getElementById('activityChart').getContext('2d');
    activityChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: days,
            datasets: [{
                label: 'Device Status',
                data: activityData,
                borderColor: '#00844b',
                backgroundColor: 'rgba(0, 132, 75, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: pointColors,
                pointBorderColor: pointColors,
                pointRadius: 5,
                pointHoverRadius: 7,
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 1,
                    ticks: {
                        stepSize: 1,
                        callback: function(value) {
                            return value === 1 ? 'Active' : 'Inactive';
                        }
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Day of Month'
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y === 1 ? 'Active' : 'Inactive';
                        }
                    }
                }
            }
        }
    });
    
    // Show modal
    const modal = new bootstrap.Modal(document.getElementById('graphModal'));
    modal.show();
}
</script>
@endsection
