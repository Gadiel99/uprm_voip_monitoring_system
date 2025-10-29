@extends('components.layout.app')

@section('content')
@php
    // Tab activo por query o por variable enviada desde el controlador
    $activeTab = request('tab', $activeTab ?? 'users');
    $isSuper = in_array(strtolower(str_replace('_','', auth()->user()->role)), ['superadmin']);
@endphp

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-semibold m-0">Admin Panel</h4>
        <span class="text-muted small">Only admins can access. Only the super admin can promote/demote admins.</span>
    </div>

    {{-- Sub-navegación de Admin (tabs SIEMPRE visibles) --}}
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ $activeTab==='users' ? 'active' : '' }}"
               href="{{ route('admin') }}?tab=users">
                <i class="bi bi-people me-2"></i>Users
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab==='buildings' ? 'active' : '' }}"
               href="{{ route('admin') }}?tab=buildings">
               <i class="bi bi-building me-2"></i>Buildings
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab==='networks' ? 'active' : '' }}"
               href="{{ route('admin') }}?tab=networks">
               <i class="bi bi-diagram-3 me-2"></i>Networks
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $activeTab==='alerts' ? 'active' : '' }}"
               href="{{ route('admin') }}?tab=alerts">
               <i class="bi bi-bell me-2"></i>Alerts
            </a>
        </li>
        <li class="nav-item ms-auto">
            <a class="nav-link {{ $activeTab==='settings' ? 'active' : '' }}"
               href="{{ route('admin') }}?tab=settings">
               <i class="bi bi-gear me-2"></i>Settings
            </a>
        </li>
    </ul>

    {{-- Contenido de tabs --}}
    @if($activeTab === 'users')
        <div class="card border-0 shadow-sm p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div>
                    <h5 class="fw-semibold mb-1">User Management</h5>
                    <small class="text-muted">
                        Manage users, roles, and access. Super admin can promote/demote admins.
                    </small>
                </div>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="bi bi-person-plus me-2"></i>Add User
                </button>
            </div>

            {{-- Stats --}}
            @php
                $adminsCountCalc = isset($adminsCount) ? $adminsCount : ($users->where('role','admin')->count());
                $superAdminsCountCalc = isset($superAdminsCount)
                    ? $superAdminsCount
                    : ($users->filter(fn($u) => in_array(strtolower(str_replace('_','',$u->role)), ['superadmin']))->count());
            @endphp
            <div class="mb-3">
                <span class="badge bg-primary me-2">Total: {{ $users->count() }}</span>
                <span class="badge bg-warning text-dark me-2">Admins: {{ $adminsCountCalc }}</span>
                <span class="badge bg-dark">Super Admins: {{ $superAdminsCountCalc }}</span>
            </div>

            {{-- Users Table --}}
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 28%">Name</th>
                            <th style="width: 32%">Email</th>
                            <th style="width: 15%">Role</th>
                            <th class="text-end" style="width: 25%">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($users as $u)
                        @php
                            $roleNorm = strtolower(str_replace('_','',$u->role));
                            $roleLabel = $roleNorm === 'superadmin' ? 'Super Admin' : ucfirst($u->role);
                            $badge = $roleNorm === 'superadmin' ? 'bg-dark' : ($u->role === 'admin' ? 'bg-warning text-dark' : 'bg-secondary');
                            $isSelf = auth()->id() === $u->id;
                            $canDelete = $roleNorm !== 'superadmin' && !$isSelf && !(!$isSuper && $u->role === 'admin');
                        @endphp
                        <tr>
                            <td class="fw-semibold">{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td><span class="badge {{ $badge }}">{{ $roleLabel }}</span></td>
                            <td class="text-end">
                                {{-- Update role (solo superadmin; no superadmin; no self) --}}
                                @if($isSuper && $roleNorm !== 'superadmin' && !$isSelf)
                                    <form action="{{ route('admin.users.updateRole', $u->id) }}" method="POST" class="d-inline me-2">
                                        @csrf @method('PATCH')
                                        <select name="role" class="form-select d-inline w-auto me-1">
                                            <option value="user"  {{ $u->role === 'user'  ? 'selected' : '' }}>User</option>
                                            <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin</option>
                                        </select>
                                        <button class="btn btn-outline-primary btn-sm">Update</button>
                                    </form>
                                @else
                                    <button class="btn btn-sm btn-secondary me-2" disabled>Update</button>
                                @endif

                                {{-- Delete --}}
                                <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" {{ $canDelete ? '' : 'disabled' }}
                                            onclick="return {{ $canDelete ? 'confirm(\'Delete user '.$u->email.'?\')' : 'false' }}">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">No users found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Errors / Status --}}
            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <ul class="mb-0">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @if (session('status'))
                <div class="alert alert-success mt-3">{{ session('status') }}</div>
            @endif
        </div>

        {{-- Modal: Add User --}}
        <div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-semibold" id="addUserModalLabel">
                            <i class="bi bi-person-plus me-2 text-success"></i> Add New User
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('admin.users.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label">Name</label>
                                    <input type="text" name="name" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Role</label>
                                    <select name="role" class="form-select">
                                        <option value="user">User</option>
                                        @if($isSuper)
                                            <option value="admin">Admin</option>
                                        @endif
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-success">Create</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    @elseif($activeTab === 'buildings')
        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-semibold mb-1">Buildings (Admin)</h5>
            <small class="text-muted">Coming soon — hook your admin building tools here.</small>
        </div>

    @elseif($activeTab === 'networks')
        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-semibold mb-1">Networks (Admin)</h5>
            <small class="text-muted">Coming soon — admin network management here.</small>
        </div>

    @elseif($activeTab === 'alerts')
        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-semibold mb-1">Alerts (Admin)</h5>
            <small class="text-muted">Coming soon — admin alert rules/thresholds here.</small>
        </div>

    @elseif($activeTab === 'settings')
        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-semibold mb-1">Admin Settings</h5>
            <small class="text-muted">Coming soon — advanced settings for admins.</small>
        </div>
    @endif
</div>
@endsection
