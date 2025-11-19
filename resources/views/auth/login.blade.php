<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UPRM VoIP Monitoring System</title>

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body { 
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://images.squarespace-cdn.com/content/v1/62b58bfd04a6df155f0b51ad/b614905f-e58f-42be-91cb-761d46ba944a/Portico-Simbolo-del-RUM.jpg?format=2500w');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: -2;
        }
        
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 103, 71, 0.85); /* UPRM green with 85% opacity */
            z-index: -1;
        }
        
        .login-container { 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            position: relative;
            z-index: 1;
        }
        
        .login-card { 
            background: rgba(255, 255, 255, 0.98);
            border-radius: 16px; 
            box-shadow: 0 8px 32px rgba(0,0,0,0.3); 
            padding: 2.5rem; 
            width: 100%; 
            max-width: 420px;
            backdrop-filter: blur(10px);
        }
        
        .btn-success { 
            background-color: #00844b; 
            border-color: #00844b; 
        }
        
        .btn-success:hover { 
            background-color: #006e3d; 
            border-color: #006e3d; 
        }
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
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility()">
                        <i class="bi bi-eye" id="toggleIcon"></i>
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
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('bi-eye');
            toggleIcon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('bi-eye-slash');
            toggleIcon.classList.add('bi-eye');
        }
    }
</script>
</body>
</html>
