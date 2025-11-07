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
    .badge-warning {
        background-color: #fff3cd;
        color: #856404;
    }
    .badge-critical {
        background-color: #f8d7da;
        color: #721c24;
    }
</style>

<div class="container-fluid">
    <h4 class="fw-semibold mb-4">Device Management</h4>

    {{-- TABLE: BUILDINGS OVERVIEW --}}
    <div id="buildingOverview">
        <div class="card border-0 shadow-sm p-4 mb-4">
            <h5 class="fw-semibold mb-3">Buildings Overview</h5>
            <p class="text-muted mb-3">Select a building to view all connected devices and their statuses.</p>

            <table class="table table-bordered table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Building</th>
                        <th>Phones (Extensions)</th>
                        <th>Total Devices</th>
                        <th>Online</th>
                        <th>Offline</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($overview as $row)
                        @php
                            $exts = $extensionsByBuilding->get($row->building_id) ?? collect();
                            $statusBadge = 'badge-online';
                            $label = 'Normal';
                            if (($row->offline_devices ?? 0) > 0) {
                                $statusBadge = 'badge-warning';
                                $label = 'Warning';
                            }
                            if (($row->total_devices ?? 0) > 0 && ($row->offline_devices ?? 0) / max(1,$row->total_devices) > 0.3) {
                                $statusBadge = 'badge-critical';
                                $label = 'Critical';
                            }
                        @endphp
                        <tr onclick="window.location='{{ route('devices.byBuilding', $row->building_id) }}'">
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-building me-2 text-success"></i>
                                    <strong>{{ $row->name }}</strong>
                                </div>
                            </td>
                            <td style="max-width: 400px;">
                                @if($exts->isEmpty())
                                    <span class="text-muted">—</span>
                                @else
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach($exts->take(4) as $e)
                                            <span class="badge bg-light text-dark border">
                                                {{ $e->extension_number }}
                                                <small class="text-muted">— {{ $e->user_first_name }} {{ $e->user_last_name }}</small>
                                            </span>
                                        @endforeach
                                        @if($exts->count() > 4)
                                            <span class="badge bg-secondary">+{{ $exts->count() - 4 }} more</span>
                                        @endif
                                    </div>
                                @endif
                            </td>
                            <td><span class="badge bg-primary">{{ $row->total_devices ?? 0 }}</span></td>
                            <td><span class="badge bg-success">{{ $row->online_devices ?? 0 }}</span></td>
                            <td><span class="badge bg-secondary">{{ $row->offline_devices ?? 0 }}</span></td>
                            <td><span class="badge {{ $statusBadge }}">{{ $label }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">No buildings found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
