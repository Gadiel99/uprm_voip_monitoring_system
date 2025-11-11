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
        <h5 class="fw-semibold mb-0">Networks — {{ $building->name }}</h5>
        <small class="text-muted">Select a network to view its devices</small>
      </div>
      <a href="{{ route('devices') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Return
      </a>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Network</th>
            <th>Total Devices</th>
            <th>Online</th>
            <th>Offline</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @forelse($networks as $network)
            @php
              $networkDevices = $devicesByNetwork[$network] ?? collect();
              $onlineDevices = $networkDevices->where('status', 'online')->count();
              $totalDevices = $networkDevices->count();
              $offlineDevices = $totalDevices - $onlineDevices;
              
              $badgeClass = 'badge-online';
              $statusText = 'All Online';
              if ($offlineDevices > $onlineDevices) {
                  $badgeClass = 'badge-offline';
                  $statusText = 'Critical';
              } elseif ($offlineDevices > 0) {
                  $badgeClass = 'badge bg-warning text-dark';
                  $statusText = 'Warning';
              }
            @endphp
            <tr onclick="window.location.href='{{ route('devices.byNetwork', ['building' => $building->building_id, 'network' => urlencode($network)]) }}'" style="cursor: pointer;">
              <td class="fw-semibold">
                <i class="bi bi-diagram-3 me-2 text-primary"></i>
                {{ $network }}
              </td>
              <td>{{ $totalDevices }}</td>
              <td>{{ $onlineDevices }}</td>
              <td>{{ $offlineDevices }}</td>
              <td>
                <span class="badge {{ $badgeClass }}">{{ $statusText }}</span>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-center text-muted">No networks configured for this building.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
