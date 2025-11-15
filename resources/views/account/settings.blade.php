@extends('layouts.app')

@section('content')
<div class="modal show d-block" tabindex="-1">
  <div class="modal-dialog">
    <form method="POST" action="{{ route('account.settings.update') }}">
      @csrf
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Account Settings</h5>
        </div>
        <div class="modal-body">
          @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
          @endif
          <div class="mb-3">
            <label for="name" class="form-label">Username</label>
            <input type="text" class="form-control" name="name" value="{{ old('name', $user->name) }}" required>
          </div>
          <div class="mb-3">
            <label for="email" class="form-label">Email address</label>
            <input type="email" class="form-control" name="email" value="{{ old('email', $user->email) }}" required>
          </div>
          <div class="mb-3">
            <label for="password" class="form-label">New Password</label>
            <input type="password" class="form-control" name="password">
            <small class="text-muted">Leave blank to keep current password.</small>
          </div>
          <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" name="password_confirmation">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection
