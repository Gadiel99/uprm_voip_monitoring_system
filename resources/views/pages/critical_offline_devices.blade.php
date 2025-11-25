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
</style>

<div class="container-fluid py-4">
  <div class="card border-0 shadow-sm p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h5 class="fw-semibold mb-0">
          <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Critical Devices - Offline Only
        </h5>
        <small class="text-muted">{{ $devices->count() }} offline critical device(s) requiring attention</small>
      </div>
      <a href="{{ route('alerts') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Alerts
      </a>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>Subnet</th>
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
            <tr>
              <td class="fw-semibold">{{ $d->subnet ?? 'N/A' }}</td>
              <td class="fw-semibold">{{ $d->ip_address }}</td>
              <td>{{ $d->mac_address ?? 'N/A' }}</td>
              <td>
                @if($d->owner)
                  {{ $d->owner }}
                @elseif($exts->isNotEmpty())
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
              <td colspan="5" class="text-center text-success">
                <i class="bi bi-check-circle me-2"></i>All critical devices are currently online!
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

@endsection
