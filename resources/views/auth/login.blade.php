<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UPRM VoIP Monitoring System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-uprm.png') }}">

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
        <h5 class="fw-semibold mb-4">UPRM VoIP Monitoring System</h5>

        {{-- Database Restore Notification --}}
        @if (Cache::has('database_restored'))
            @php
                $restoreInfo = Cache::get('database_restored');
            @endphp
            <div class="alert alert-warning text-start">
                <div class="d-flex align-items-start">
                    <i class="bi bi-exclamation-triangle-fill me-2 mt-1"></i>
                    <div>
                        <strong>Database Restore Notice</strong>
                        <p class="mb-2 small">A database restore was performed on {{ $restoreInfo['timestamp'] }}.</p>
                        <p class="mb-2 small">If you're having trouble logging in:</p>
                        <ul class="small mb-0">
                            <li>Use the <a href="{{ route('password.request') }}" class="alert-link">Forgot Password</a> link below</li>
                            <li>Or contact an administrator for assistance</li>
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Success Message --}}
        @if (session('status'))
            <div class="alert alert-success text-start">
                <i class="bi bi-check-circle me-2"></i>{{ session('status') }}
            </div>
        @endif

        {{-- Errores --}}
        @if ($errors->any())
            <div class="alert alert-danger text-start">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Login Form --}}
        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-3 text-start">
                <label for="email" class="form-label fw-semibold">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input id="email" type="email" name="email" class="form-control"
                           placeholder="admin@uprm.edu" value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            {{-- Password --}}
            <div class="mb-3 text-start">
                <label for="password" class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input id="password" type="password" name="password" class="form-control"
                           placeholder="Enter your password" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            {{-- Forgot Password Link --}}
            <div class="mb-4 text-end">
                <a href="{{ route('password.request') }}" class="text-decoration-none small">Forgot your password?</a>
            </div>

            {{-- Login button --}}
            <button type="submit" class="btn btn-success w-100 fw-semibold">Log In</button>
        </form>

        <hr class="my-4">
        <p class="small text-muted mb-0">© {{ date('Y') }} UPRM VoIP Monitoring System</p>
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
</body>
</html>
