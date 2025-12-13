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
        <h5 class="fw-semibold mb-0">Devices with Unknown IP Addresses</h5>
        <small class="text-muted">
          <i class="bi bi-question-circle me-1 text-danger"></i>{{ $building->name }} → Unknown IP
        </small>
        <br>
        <small class="text-muted">These devices are defined in the system but have never been registered</small>
      </div>
      <a href="{{ route('devices.unmapped') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Back to Networks
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

    {{-- Info Alert --}}
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
      <i class="bi bi-info-circle me-2"></i>
      <strong>What does this mean?</strong> These devices exist in the phone system database but have never registered with the VoIP server. 
      They may be:
      <ul class="mb-0 mt-2">
        <li>Devices that were provisioned but never deployed</li>
        <li>Devices that are powered off or disconnected</li>
        <li>Devices with incorrect network configuration</li>
        <li>Devices that have been decommissioned but not removed from the database</li>
      </ul>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
            <th>MAC Address</th>
            <th>Owner</th>
            <th>Extensions</th>
            <th style="width: 120px;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($devices as $d)
            @php
              $exts = ($extByDevice ?? collect())->get($d->device_id) ?? collect();
            @endphp
            <tr class="device-row">
              <td class="device-mac">{{ $d->mac_address ?? 'N/A' }}</td>
              <td class="device-owner">
                @if($exts->isNotEmpty())
                  {{ $exts->first()->user_first_name }} {{ $exts->first()->user_last_name }}
                @else
                  <span class="text-muted">Unknown</span>
                @endif
              </td>
              <td class="device-extensions">
                @if($exts->isEmpty())
                  <span class="text-muted">—</span>
                @else
                  {{ $exts->pluck('extension_number')->join(', ') }}
                @endif
              </td>
              <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm" onclick="confirmRemoveDevice('{{ $d->device_id }}', '{{ $d->mac_address }}')">
                  <i class="bi bi-trash"></i> Remove
                </button>
              </td>
            </tr>
          @empty
            <tr class="empty-devices-row">
              <td colspan="4" class="text-center text-muted">No devices with unknown IP addresses found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    {{-- Pagination Links --}}
    @if($devices->hasPages())
      <div class="mt-4">
        {{ $devices->links('vendor.pagination.custom-pagination') }}
      </div>
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
                <p class="mb-2">Are you sure you want to remove device with MAC address <strong id="deleteDeviceMac"></strong>?</p>
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
// Filter devices based on search input
function filterDevices() {
    const searchInput = document.getElementById('deviceSearch').value.toLowerCase().trim();
    const rows = document.querySelectorAll('.device-row');
    let visibleCount = 0;

    // Split search terms by comma and normalize each term
    const searchTerms = searchInput.split(',').map(term => term.trim().replace(/[^a-z0-9]/g, '')).filter(term => term !== '');

    rows.forEach(row => {
        // Get all text content from the row and normalize it
        const mac = (row.querySelector('.device-mac')?.textContent || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        const owner = (row.querySelector('.device-owner')?.textContent || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        const extensions = (row.querySelector('.device-extensions')?.textContent || '').toLowerCase().replace(/[^a-z0-9]/g, '');
        
        // Check if any search term matches
        let matchFound = false;
        if (searchTerms.length === 0) {
            matchFound = true; // Show all if no search terms
        } else {
            for (const term of searchTerms) {
                if (mac.includes(term) || owner.includes(term) || extensions.includes(term)) {
                    matchFound = true;
                    break;
                }
            }
        }
        
        if (matchFound) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    // Update empty message
    const emptyRow = document.querySelector('.empty-devices-row');
    if (emptyRow) {
        emptyRow.style.display = visibleCount === 0 && searchInput ? '' : 'none';
        if (visibleCount === 0 && searchInput) {
            emptyRow.querySelector('td').textContent = 'No devices match your search.';
        }
    }
}

// Real-time search
document.getElementById('deviceSearch')?.addEventListener('input', filterDevices);

// Device removal
let pendingDeleteDeviceId = null;

function confirmRemoveDevice(deviceId, deviceMac) {
    pendingDeleteDeviceId = deviceId;
    document.getElementById('deleteDeviceMac').textContent = deviceMac;
    
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    modal.show();
}

// Confirm delete button
document.getElementById('confirmDeleteBtn')?.addEventListener('click', function() {
    if (pendingDeleteDeviceId) {
        // Create and submit form
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/devices/${pendingDeleteDeviceId}/remove`;
        
        // CSRF token
        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = '{{ csrf_token() }}';
        form.appendChild(csrfInput);
        
        // Method spoofing for DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);
        
        document.body.appendChild(form);
        form.submit();
    }
});
</script>
@endsection
