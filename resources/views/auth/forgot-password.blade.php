<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - UPRM VoIP Monitoring System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-uprm.png') }}">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-image: url('{{ asset('images/rum-background.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            font-family: 'Segoe UI', sans-serif;
        }
        
        /* Overlay for better card visibility */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 132, 75, 0.7);
            z-index: -1;
        }
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
        <h5 class="fw-semibold mb-2">Forgot Password?</h5>
        <p class="text-muted small mb-4">
            No problem. Enter your email address and we'll send you a password reset link.
        </p>

        {{-- Success Message --}}
        @if (session('status'))
            <div class="alert alert-success text-start">
                {{ session('status') }}
            </div>
        @endif

        {{-- Errors --}}
        @if ($errors->any())
            <div class="alert alert-danger text-start">
                <ul class="mb-0">
                    @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        {{-- Forgot Password Form --}}
        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            {{-- Email --}}
            <div class="mb-4 text-start">
                <label for="email" class="form-label fw-semibold">Email</label>
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input id="email" type="email" name="email" class="form-control"
                           placeholder="admin@uprm.edu" value="{{ old('email') }}" required autofocus>
                </div>
            </div>

            {{-- Submit button --}}
            <button type="submit" class="btn btn-success w-100 fw-semibold mb-3">
                <i class="bi bi-send me-1"></i> Email Password Reset Link
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
</body>
</html>
