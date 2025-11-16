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
          <i class="bi bi-exclamation-circle me-2 text-warning"></i>
          Networks — {{ $building->name }}
        </h5>
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
          </tr>
        </thead>
        <tbody>
          @forelse($networks as $network)
            @php
              $networkDevices = $devicesByNetwork[$network] ?? collect();
              $totalDevices = $networkDevices->count();
            @endphp
            <tr onclick="window.location.href='{{ route('devices.unmappedNetwork', ['network' => urlencode($network)]) }}'" style="cursor: pointer;">
              <td class="fw-semibold">
                <i class="bi bi-diagram-3 me-2 text-warning"></i>
                {{ $network }}
              </td>
              <td>{{ $totalDevices }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="2" class="text-center text-success">
                <i class="bi bi-check-circle me-2"></i>All networks are assigned to buildings.
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
