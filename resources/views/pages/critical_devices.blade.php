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
        <h5 class="fw-semibold mb-0">
          <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Critical Devices
        </h5>
        <small class="text-muted">Click on any device to view its 30-day activity graph</small>
      </div>
      <a href="{{ route('devices') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Devices
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
          </tr>
        </thead>
        <tbody>
          @forelse($devices as $d)
            @php
              $exts = ($extByDevice ?? collect())->get($d->device_id) ?? collect();
            @endphp
            <tr onclick="showDeviceGraph('{{ $d->ip_address }}', '{{ $d->device_id }}', 'Critical Devices', 'N/A')" style="cursor: pointer;">
              <td class="fw-semibold">{{ $d->ip_address }}</td>
              <td>{{ $d->mac_address ?? 'N/A' }}</td>
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
                  @foreach($exts as $e)
                    {{ $e->extension_number }}@if(!$loop->last), @endif
                  @endforeach
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center text-muted">No critical devices found.</td>
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
          <canvas id="activityChart" width="400" height="200"></canvas>
        </div>
        <div class="mt-3 text-muted small">
          <i class="bi bi-info-circle me-1"></i>
          Showing device activity with 5-minute intervals (288 samples per day).
        </div>
      </div>
    </div>
  </div>
</div>

{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
let activityChart = null;
let activityDataSet = { today: null, yesterday: null };

// Show device activity graph in modal (same UX as devices_in_network)
async function showDeviceGraph(ip, deviceId, building, network) {
  document.getElementById('modalDeviceId').textContent = `${deviceId} (${ip}) - ${building}`;

  // Show loading spinner
  const chartContainer = document.getElementById('chartContainer');
  chartContainer.innerHTML = '<div class="text-center p-5"><div class="spinner-border text-success" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2">Loading activity data...</p></div>';

  // Show modal
  const modal = new bootstrap.Modal(document.getElementById('graphModal'));
  modal.show();

  try {
    const response = await fetch(`/api/device-activity/${deviceId}/both`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      },
      credentials: 'same-origin'
    });
    console.log('[DEBUG] critical_devices API fetch response:', response);
    let json = null;
    try { json = await response.json(); } catch (err) { console.error('[DEBUG] JSON parse error:', err); }
    console.log('[DEBUG] critical_devices API JSON:', json);
    if (!response.ok || !json) throw new Error('Failed to load activity data');

    activityDataSet = json;
    chartContainer.innerHTML = '<canvas id="activityChart" width="400" height="200"></canvas>';
    // Default to show today
    showDay(1);
  } catch (err) {
    console.error('[DEBUG] Error loading critical device activity:', err);
    chartContainer.innerHTML = `<div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2"></i>Unable to load activity data.</div>`;
  }
}

function showDay(dayNumber) {
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

  const data = dayNumber === 1 ? activityDataSet.today : activityDataSet.yesterday;
  if (!data || !Array.isArray(data.samples) || data.samples.length === 0) {
    document.getElementById('chartContainer').innerHTML = `<div class="alert alert-info"><i class="bi bi-info-circle me-2"></i>No data available for ${dayNumber === 1 ? 'today' : 'yesterday'}.</div>`;
    return;
  }

  // Ensure canvas exists
  if (!document.getElementById('activityChart')) {
    document.getElementById('chartContainer').innerHTML = '<canvas id="activityChart" width="400" height="200"></canvas>';
  }
  renderChart(data);
}

function renderChart(data) {
  if (activityChart) activityChart.destroy();

  const samples = data.samples;
  const isToday = data.day_number === 1;

  let currentSampleIndex = 287;
  if (isToday && data.current_sample_index !== undefined) currentSampleIndex = data.current_sample_index;

  const labels = [];
  for (let i = 0; i < 288; i++) {
    const hours = Math.floor(i * 5 / 60);
    const minutes = (i * 5) % 60;
    labels.push(`${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}`);
  }

  const displaySamples = samples.map((val, index) => {
    if (isToday) {
      // For today, only show samples up to current time
      if (index > currentSampleIndex) return null;
      return val;
    } else {
      // For yesterday, show all samples
      return val;
    }
  });

  const pointColors = displaySamples.map(v => v === null ? 'rgba(0,0,0,0)' : (v === 1 ? '#00844b' : '#dc3545'));

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
        spanGaps: false,
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      scales: {
        y: { beginAtZero: true, max: 1.1, min: -0.1, ticks: { stepSize: 1, callback: v => v === 1 ? 'Active' : v === 0 ? 'Inactive' : '' } },
        x: { title: { display: true, text: `Time (${data.activity_date})` }, ticks: { autoSkip: true, maxTicksLimit: 24 } }
      },
      plugins: { legend: { display: false }, tooltip: { callbacks: { title: ctx => `Time: ${ctx[0].label}`, label: ctx => 'Status: ' + (ctx.parsed.y === 1 ? 'Active' : 'Inactive') + (data.is_live ? ' (Live)' : ' (Historical)') } } },
      interaction: { intersect: false, mode: 'index' }
    }
  });
}
</script>
@endsection
