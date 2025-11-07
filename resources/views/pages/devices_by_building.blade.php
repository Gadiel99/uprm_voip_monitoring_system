@extends('components.layout.app') {{-- Ajusta si tu layout es distinto --}}

@section('content')
<div class="container-fluid py-4">
  <div class="card border-0 shadow-sm p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h5 class="fw-semibold mb-0">Devices — {{ $building->name }}</h5>
        <small class="text-muted">Full device list and connection status</small>
      </div>
      <a href="{{ route('devices') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Return
      </a>
    </div>

    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-light">
          <tr>
            <th>IP</th>
            <th>Status</th>
            <th>Extensions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($devices as $d)
            @php
              $exts = ($extByDevice ?? collect())->get($d->device_id) ?? collect();
              $badge = 'badge bg-light text-muted';
              if ($d->status === 'online') $badge = 'badge bg-success';
              elseif ($d->status === 'offline') $badge = 'badge bg-secondary';
              elseif ($d->status === 'warning') $badge = 'badge bg-warning text-dark';
            @endphp
            <tr>
              <td class="fw-semibold">{{ $d->ip_address }}</td>
              <td><span class="{{ $badge }}">{{ ucfirst($d->status ?? 'unknown') }}</span></td>
              <td>
                @if($exts->isEmpty())
                  <span class="text-muted">—</span>
                @else
                  <div class="d-flex flex-wrap gap-2">
                    @foreach($exts as $e)
                      <span class="badge bg-light text-dark border">
                        {{ $e->extension_number }}
                        <small class="text-muted">— {{ $e->user_first_name }} {{ $e->user_last_name }}</small>
                      </span>
                    @endforeach
                  </div>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center text-muted">No devices in this building.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection
