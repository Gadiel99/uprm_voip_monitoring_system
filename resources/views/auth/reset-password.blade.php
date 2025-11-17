<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - UPRM VoIP Monitoring System</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { background-color: #f8f9fa; font-family: 'Segoe UI', sans-serif; }
        .login-container { min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #fff; border-radius: 16px; box-shadow: 0 0 15px rgba(0,0,0,0.1); padding: 2.5rem; width: 100%; max-width: 420px; }
        .btn-success { background-color: #00844b; border-color: #00844b; }
        .btn-success:hover { background-color: #006e3d; border-color: #006e3d; }
    </style>
</head>

<body>
<div class="login-container">
    <div class="login-card text-center">
        <img src="{{ asset('images/logo-uprm.png') }}" alt="UPRM Logo" width="80" class="mb-3">
        <h5 class="fw-semibold mb-2">Reset Password</h5>
        <p class="text-muted small mb-4">
            Enter your new password below.
        </p>

        {{-- Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger text-start">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Reset Password Form --}}
        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            {{-- Email --}}
            <div class="mb-3 text-start">
                <label for="email" class="form-label fw-semibold">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input id="email" type="email" name="email" class="form-control"
                           placeholder="admin@uprm.edu" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
                </div>
            </div>

            {{-- New Password --}}
            <div class="mb-3 text-start">
                <label for="password" class="form-label fw-semibold">New Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input id="password" type="password" name="password" class="form-control"
                           placeholder="Enter new password" required autocomplete="new-password">
                </div>
            </div>

            {{-- Confirm Password --}}
            <div class="mb-3 text-start">
                <label for="password_confirmation" class="form-label fw-semibold">Confirm Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock-fill"></i></span>
                    <input id="password_confirmation" type="password" name="password_confirmation" class="form-control"
                           placeholder="Confirm new password" required autocomplete="new-password">
                </div>
            </div>

            {{-- Password Requirements --}}
            <div class="mb-4 text-start">
                <p class="small text-muted mb-2 fw-semibold">Password requirements:</p>
                <ul class="small text-muted mb-0" style="line-height: 1.8;">
                    <li>8-64 characters</li>
                    <li>At least one uppercase and one lowercase letter</li>
                    <li>At least one number</li>
                    <li>At least one symbol (e.g., ! @ # $ %)</li>
                    <li>Must be different from your current password</li>
                </ul>
            </div>

            {{-- Submit button --}}
            <button type="submit" class="btn btn-success w-100 fw-semibold mb-3">
                <i class="bi bi-shield-check me-1"></i> Reset Password
            </button>

            {{-- Back to Login --}}
            <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100 fw-semibold">
                <i class="bi bi-arrow-left me-1"></i> Back to Login
            </a>
        </form>

        <hr class="my-4">
        <p class="small text-muted mb-0">© {{ date('Y') }} UPRM VoIP Monitoring System</p>
    </div>
</div>

<script>
    // Show password as text on focus, hide on blur
    document.addEventListener('DOMContentLoaded', function() {
        function autoShowPassword(inputId) {
            const input = document.getElementById(inputId);
            if (!input) return;
            input.addEventListener('focus', function() {
                this.type = 'text';
            });
            input.addEventListener('blur', function() {
                this.type = 'password';
            });
        }
        
        autoShowPassword('password');
        autoShowPassword('password_confirmation');
    });
</script>
</body>
</html>
