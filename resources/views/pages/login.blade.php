{{--
/*
 * File: login.blade.php
 * Project: UPRM VoIP Monitoring System
 * Description: User authentication page providing login functionality
 * 
 * Author: [Hector R. Sepulveda]
 * Date Created: October 2025
 * Last Modified: October 30, 2025
 * 
 * Purpose:
 *   This page provides the user interface for system authentication. It includes:
 *   - Email and password input fields
 *   - UPRM branding and logo
 * 
 * Form Details:
 *   - Action: POST /login
 *   - Method: POST
 *   - CSRF Protection: Enabled
 *   - Validation: Client-side HTML5 validation
 * 
 * Security:
 *   - CSRF token required for form submission
 *   - Session-based authentication
 *   - Frontend mockup (accepts any credentials)
 * 
 * Dependencies:
 *   - Bootstrap 5.3.3
 *   - Bootstrap Icons 1.11.3
 * 
 * IEEE Standards Compliance:
 *   - Follows IEEE 829 test documentation
 *   - Adheres to IEEE 1016 design standards
 */
--}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - UPRM VoIP Monitoring System</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo-uprm.png') }}">

    {{-- === Bootstrap 5 CSS & JS === --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- === Bootstrap Icons === --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    {{-- === Custom Styles for Login Page === --}}
    <style>
        /* Body background and font */
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', sans-serif;
        }

        /* Center the login container vertically & horizontally */
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Login card styling */
        .login-card {
            background: #fff; /* White card background */
            border-radius: 16px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1); /* Soft shadow */
            padding: 2.5rem;
            width: 100%;
            max-width: 420px; /* Restrict maximum width */
        }

        /* Custom green buttons */
        .btn-success {
            background-color: #00844b;
            border-color: #00844b;
        }

        /* Hover effect for buttons */
        .btn-success:hover {
            background-color: #006e3d;
            border-color: #006e3d;
        }
    </style>
</head>

<body>
<div class="login-container">
    {{-- === Login Card === --}}
    <div class="login-card text-center">
        {{-- Logo --}}
        <img src="{{ asset('images/logo-uprm.png') }}" alt="UPRM Logo" width="80" class="mb-3">

        {{-- Page title --}}
        <h5 class="fw-semibold mb-4">UPRM VoIP Monitoring System</h5>

        {{-- === Login Form === --}}
        <form action="{{ url('/login') }}" method="POST">
            @csrf  {{-- CSRF token for security --}}

            {{-- Email input field --}}
            <div class="mb-3 text-start">
                <label class="form-label fw-semibold">Email</label>
                <div class="input-group">
                    {{-- Envelope icon --}}
                    <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                    <input type="email" name="email" class="form-control" placeholder="admin@uprm.edu" required>
                </div>
            </div>

            {{-- Password input field --}}
            <div class="mb-3 text-start">
                <label class="form-label fw-semibold">Password</label>
                <div class="input-group">
                    {{-- Lock icon --}}
                    <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                    <input type="password" name="password" id="login_password" class="form-control" placeholder="Enter your password" required>
                    <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('login_password', this)">
                        <i class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            {{-- Login button --}}
            <button type="submit" class="btn btn-success w-100 fw-semibold">Log In</button>
        </form>

        {{-- Footer separator --}}
        <hr class="my-4">

        {{-- Footer copyright --}}
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

// Log login attempt
document.querySelector('form').addEventListener('submit', function(e) {
    const email = document.querySelector('input[name="email"]').value;
    
    // Add log entry
    const logs = JSON.parse(localStorage.getItem('systemLogs') || '[]');
    const timestamp = new Date().toISOString().replace('T', ' ').substring(0, 19);
    
    logs.unshift({
        timestamp: timestamp,
        type: 'INFO',
        message: `User login attempt with email: ${email}`,
        user: email,
        id: Date.now()
    });
    
    // Keep only last 500 logs
    if (logs.length > 500) logs.pop();
    
    localStorage.setItem('systemLogs', JSON.stringify(logs));
});
</script>
</body>
</html>
