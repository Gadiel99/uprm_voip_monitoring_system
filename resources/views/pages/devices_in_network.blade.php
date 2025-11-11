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

    .badge-online {
        background-color: #e6f9ed;
        color: #00844b;
    }

    .badge-offline {
        background-color: #fdeaea;
        color: #c82333;
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
      </div>
      <a href="{{ route('devices.byBuilding', ['building' => $building->building_id]) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Networks
      </a>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>IP Address</th>
            <th>MAC Address</th>
            <th>Owner</th>
            <th>Extensions</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($devices as $d)
            @php
              $exts = ($extByDevice ?? collect())->get($d->device_id) ?? collect();
              $badgeClass = $d->status === 'online' ? 'badge-online' : 'badge-offline';
            @endphp
            <tr onclick="showDeviceGraph('{{ $d->ip_address }}', '{{ $d->device_id }}', '{{ $building->name }}', '{{ $network }}')" style="cursor: pointer;">
              <td class="fw-semibold">{{ $d->ip_address }}</td>
              <td><code>{{ $d->mac_address ?? 'N/A' }}</code></td>
              <td>
                @if($exts->isNotEmpty())
                  {{ $exts->first()->user_first_name }} {{ $exts->first()->user_last_name }}
                @else
                  <span class="text-muted">N/A</span>
                @endif
              </td>
              <td>
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
              <td><span class="badge {{ $badgeClass }}">{{ ucfirst($d->status ?? 'unknown') }}</span></td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted">No devices found in this network.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
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