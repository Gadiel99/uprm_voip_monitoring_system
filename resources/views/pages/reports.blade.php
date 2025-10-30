@extends('components.layout.app')

@section('content')
<div class="container-fluid">
    <h4 class="fw-semibold mb-4">Reports</h4>

    {{-- REPORTS TAB --}}
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h5 class="fw-semibold mb-3">Device Reports Search</h5>

        {{-- Search Filters --}}
        <form id="searchForm">
            <div class="row g-3 align-items-end mb-3">
                <div class="col-md-4">
                    <label class="form-label">User</label>
                    <input type="text" id="searchUser" class="form-control bg-light" placeholder="Search by user name...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">MAC Address</label>
                    <input type="text" id="searchMac" class="form-control bg-light" placeholder="Search by MAC address...">
                </div>
                <div class="col-md-4">
                    <label class="form-label">IP Address</label>
                    <input type="text" id="searchIp" class="form-control bg-light" placeholder="Search by IP address...">
                </div>
            </div>

            <div class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select id="searchStatus" class="form-select bg-light">
                        <option value="">All Status</option>
                        <option>Online</option>
                        <option>Offline</option>
                        <option>Critical</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Building</label>
                    <select id="searchBuilding" class="form-select bg-light">
                        <option value="">All Buildings</option>
                        <option>Stefani</option>
                        <option>General Library</option>
                        <option>Student Center</option>
                        <option>Engineering Complex</option>
                        <option>Computer Science Department</option>
                        <option>Administration</option>
                        <option>Physics</option>
                        <option>Chardon</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex justify-content-end align-items-center gap-3">
                    <button type="button" id="searchBtn" class="btn btn-success px-5 py-2">
                        <i class="bi bi-search me-2"></i> Search
                    </button>
                    <button type="reset" id="resetBtn" class="btn btn-outline-secondary px-4 py-2">
                        <i class="bi bi-arrow-counterclockwise me-2"></i> Reset
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- RESULTS TABLE --}}
    <div class="card border-0 shadow-sm p-4 mb-4">
        <h5 class="fw-semibold mb-3">Search Results</h5>
        <table class="table table-bordered table-hover align-middle" id="resultsTable">
            <thead class="table-light">
                <tr>
                    <th>User</th>
                    <th>MAC Address</th>
                    <th>IP Address</th>
                    <th>Status</th>
                    <th>Building</th>
                </tr>
            </thead>
            <tbody>
                {{-- Rows generated dynamically --}}
            </tbody>
        </table>
        <p id="noResults" class="text-muted fst-italic mt-2">No results to display.</p>
    </div>

    {{-- SYSTEM OVERVIEW --}}
    <div class="card border-0 shadow-sm p-4">
        <h5 class="fw-semibold mb-3">System Overview</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="border rounded-3 p-3 text-center" style="background-color: #f0f6ff;">
                    <h6 class="fw-semibold">Total Devices</h6>
                    <h2 class="fw-bold text-primary mb-1">18</h2>
                    <p class="text-primary small mb-0">Registered in system</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded-3 p-3 text-center" style="background-color: #ecfdf5;">
                    <h6 class="fw-semibold">Active Now</h6>
                    <h2 class="fw-bold text-success mb-1">15</h2>
                    <p class="text-success small mb-0">Currently online</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded-3 p-3 text-center" style="background-color: #fffbea;">
                    <h6 class="fw-semibold">Inactive</h6>
                    <h2 class="fw-bold text-warning mb-1">3</h2>
                    <p class="text-warning small mb-0">Offline devices</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="border rounded-3 p-3 text-center" style="background-color: #ecfdf5;">
                    <h6 class="fw-semibold">Buildings</h6>
                    <h2 class="fw-bold text-success mb-1">12</h2>
                    <p class="text-success small mb-0">Monitored locations</p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- JavaScript --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const data = [
        { user: 'admin', mac: '00:1B:44:11:3A:B7', ip: '192.168.1.10', status: 'Online', building: 'Stefani' },
        { user: 'jdoe', mac: '00:1B:44:11:3A:B8', ip: '192.168.1.11', status: 'Offline', building: 'Chardon' },
        { user: 'msmith', mac: '00:1B:44:11:3A:B9', ip: '192.168.1.12', status: 'Critical', building: 'Engineering Complex' },
        { user: 'jsantos', mac: '00:1B:44:11:4A:11', ip: '192.168.2.10', status: 'Online', building: 'General Library' },
        { user: 'acastro', mac: '00:1B:44:11:4A:12', ip: '192.168.2.11', status: 'Offline', building: 'Physics' },
        { user: 'drios', mac: '00:1B:44:11:5A:21', ip: '192.168.3.10', status: 'Online', building: 'Student Center' }
    ];

    const tbody = document.querySelector('#resultsTable tbody');
    const noResults = document.getElementById('noResults');

    function renderTable(rows) {
        tbody.innerHTML = '';
        if (rows.length === 0) {
            noResults.textContent = "No results to display.";
            noResults.classList.remove('d-none');
            return;
        }

        noResults.classList.add('d-none');
        rows.forEach(r => {
            let badgeClass = r.status === 'Online' ? 'bg-success' :
                             r.status === 'Offline' ? 'bg-danger' :
                             'bg-warning text-dark';
            tbody.insertAdjacentHTML('beforeend', `
                <tr>
                    <td>${r.user}</td>
                    <td>${r.mac}</td>
                    <td>${r.ip}</td>
                    <td><span class="badge ${badgeClass}">${r.status}</span></td>
                    <td>${r.building}</td>
                </tr>
            `);
        });
    }

    // Initially empty
    renderTable([]);

    // Search button
    document.getElementById('searchBtn').addEventListener('click', () => {
        const user = document.getElementById('searchUser').value.toLowerCase();
        const mac = document.getElementById('searchMac').value.toLowerCase();
        const ip = document.getElementById('searchIp').value.toLowerCase();
        const status = document.getElementById('searchStatus').value.toLowerCase();
        const building = document.getElementById('searchBuilding').value.toLowerCase();

        const filtered = data.filter(d =>
            (!user || d.user.toLowerCase().includes(user)) &&
            (!mac || d.mac.toLowerCase().includes(mac)) &&
            (!ip || d.ip.toLowerCase().includes(ip)) &&
            (!status || d.status.toLowerCase() === status) &&
            (!building || d.building.toLowerCase().includes(building))
        );

        renderTable(filtered);
    });

    // Reset button clears results
    document.getElementById('resetBtn').addEventListener('click', () => {
        document.getElementById('searchForm').reset();
        renderTable([]);
    });
});
</script>
@endsection
