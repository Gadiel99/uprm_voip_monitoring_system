@extends('components.layout.app')

@section('content')
<style>
    .card {
        border-radius: 12px;
        box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }

    .table-hover tbody tr:hover {
        background-color: #f1f3f4;
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
        <h5 class="fw-semibold mb-0">
            <i class="bi bi-exclamation-triangle text-danger me-2"></i>
            Offline Devices — {{ $building->name }}
        </h5>
        <small class="text-muted">Showing only offline devices from alerts</small>
      </div>
      <a href="{{ route('alerts') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Alerts
      </a>
    </div>

    @if($devices->isEmpty())
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            <strong>All devices are online!</strong> No offline devices found in this building.
        </div>
    @else
        <div class="alert alert-danger mb-3">
            <i class="bi bi-info-circle me-2"></i>
            <strong>{{ $devices->count() }} offline device(s)</strong> detected in this building.
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Subnet</th>
                <th>IP Address</th>
                <th>MAC Address</th>
                <th>Extension</th>
              </tr>
            </thead>
            <tbody>
              @foreach($devices as $device)
                @php
                  $extensions = $extByDevice[$device->device_id] ?? collect();
                  $extList = $extensions->map(function($ext) {
                      $name = trim($ext->user_first_name . ' ' . $ext->user_last_name);
                      return $ext->extension_number . ($name ? " ({$name})" : '');
                  })->join(', ');
                @endphp
                <tr>
                  <td>
                    <i class="bi bi-diagram-3 me-2 text-primary"></i>
                    {{ $device->subnet }}
                  </td>
                  <td>{{ $device->ip_address }}</td>
                  <td>{{ $device->mac_address ?: 'N/A' }}</td>
                  <td>{{ $extList ?: 'No extension' }}</td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
    @endif
  </div>
</div>
@endsection
