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
            <div class="input-group">
                <input type="password" id="settings_password" class="form-control" name="password">
                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('settings_password', this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
            <small class="text-muted">Leave blank to keep current password.</small>
          </div>
          <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirm New Password</label>
            <div class="input-group">
                <input type="password" id="settings_password_confirmation" class="form-control" name="password_confirmation">
                <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('settings_password_confirmation', this)">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Save changes</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
// Toggle password visibility
function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>
@endsection
