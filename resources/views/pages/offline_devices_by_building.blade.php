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

    {{-- Success/Error Messages --}}
    @if(session('success'))
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    @endif

    @if($devices->isEmpty())
        <div class="alert alert-success">
            <i class="bi bi-check-circle me-2"></i>
            <strong>All devices are online!</strong> No offline devices found in this building.
        </div>
    @else
        <div class="alert alert-danger mb-3">
            <i class="bi bi-info-circle me-2"></i>
            <strong>{{ $devices->total() }} offline device(s)</strong> detected in this building.
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Subnet</th>
                <th>IP Address</th>
                <th>MAC Address</th>
                <th>Extension</th>
                <th style="width: 120px;">Actions</th>
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
                  <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="confirmRemoveDevice('{{ $device->device_id }}', '{{ $device->ip_address }}')">
                      <i class="bi bi-trash"></i> Remove
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>

        {{-- Pagination Links --}}
        @if($devices->hasPages())
          <div class="mt-4">
            {{ $devices->links('vendor.pagination.custom-pagination') }}
          </div>
        @endif
    @endif
  </div>
</div>

{{-- MODAL: CONFIRM DEVICE REMOVAL --}}
<div class="modal fade" id="deleteConfirmModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">
          <i class="bi bi-exclamation-triangle me-2"></i>voipmonitor.uprm.edu says
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="mb-2">Are you sure you want to remove device <strong id="deleteDeviceIp"></strong> from this network?</p>
        <p class="text-muted mb-0"><small>This action cannot be undone.</small></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
          <i class="bi bi-trash me-1"></i> OK
        </button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </div>
  </div>
</div>

<script>
// Confirm device removal
let pendingDeleteDeviceId = null;
let pendingDeleteDeviceIp = null;

function confirmRemoveDevice(deviceId, ipAddress) {
  // Store the device info
  pendingDeleteDeviceId = deviceId;
  pendingDeleteDeviceIp = ipAddress;
  
  // Update modal content
  document.getElementById('deleteDeviceIp').textContent = ipAddress;
  
  // Show modal
  const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
  modal.show();
}

// Handle actual deletion when OK is clicked
document.addEventListener('DOMContentLoaded', function() {
  const confirmBtn = document.getElementById('confirmDeleteBtn');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', function() {
      if (pendingDeleteDeviceId) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/devices/${pendingDeleteDeviceId}/remove`;
        
        // Add CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        // Add method spoofing for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
      }
    });
  }
});
</script>
@endsection
