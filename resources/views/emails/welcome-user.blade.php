<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to UPRM VoIP Monitoring System</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #00844b;
            color: white;
            padding: 30px;
            text-align: center;
        }
        .header img {
            max-width: 80px;
            margin-bottom: 15px;
            background-color: white;
            border-radius: 50%;
            padding: 15px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .content {
            padding: 40px 30px;
        }
        .content h2 {
            color: #00844b;
            margin-top: 0;
            font-size: 20px;
        }
        .content p {
            margin: 15px 0;
            color: #555;
        }
        .credentials-box {
            background-color: #f8f9fa;
            border-left: 4px solid #00844b;
            padding: 20px;
            margin: 25px 0;
            border-radius: 4px;
        }
        .credentials-box p {
            margin: 10px 0;
            font-size: 14px;
        }
        .credentials-box strong {
            color: #00844b;
            display: inline-block;
            min-width: 100px;
        }
        .credentials-box .password {
            font-family: 'Courier New', monospace;
            background-color: #fff;
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #dee2e6;
            display: inline-block;
            margin-top: 5px;
            color: #333;
            font-weight: 600;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            margin: 20px 0;
            background-color: #00844b;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            text-align: center;
        }
        .button:hover {
            background-color: #006e3d;
        }
        .info-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box p {
            margin: 5px 0;
            color: #856404;
            font-size: 14px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            color: #6c757d;
            font-size: 12px;
            border-top: 1px solid #dee2e6;
        }
        .footer p {
            margin: 5px 0;
        }
        .security-tips {
            background-color: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .security-tips h3 {
            color: #2e7d32;
            font-size: 16px;
            margin-top: 0;
            margin-bottom: 10px;
        }
        .security-tips ul {
            margin: 10px 0;
            padding-left: 20px;
            color: #2e7d32;
        }
        .security-tips li {
            margin: 5px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('images/logo-uprm.png') }}" alt="UPRM Logo">
            <h1>UPRM VoIP Monitoring System</h1>
        </div>

        <div class="content">
            <h2>Hello {{ $user->name }},</h2>
            
            <p>
                Your account has been created successfully by an administrator. You now have access to the UPRM VoIP Monitoring System.
            </p>

            <div class="credentials-box">
                <p style="font-weight: 600; color: #00844b; margin-bottom: 15px;">Your Login Credentials:</p>
                <p><strong>Email:</strong> {{ $email }}</p>
                <p><strong>Password:</strong> <span class="password">{{ $temporaryPassword }}</span></p>
            </div>

            <div class="info-box">
                <p><strong>⚠️ Important Security Notice:</strong></p>
                <p>This is a temporary password. For security reasons, we strongly recommend that you change your password immediately after your first login.</p>
            </div>

            <div style="text-align: center;">
                <a href="{{ $loginUrl }}" class="button">Login to Your Account</a>
            </div>

            <div class="security-tips">
                <h3>🔒 Password Security Tips:</h3>
                <ul>
                    <li>Change your password after first login via Account Settings</li>
                    <li>Use a strong password with 8-64 characters</li>
                    <li>Include uppercase and lowercase letters, numbers, and symbols</li>
                    <li>Never share your password with anyone</li>
                    <li>Don't reuse passwords from other accounts</li>
                </ul>
            </div>

            <p style="margin-top: 30px;">
                If you have any questions or didn't request this account, please contact the Director of the Department of Auxiliary Services.
            </p>

            {{-- Director Signature Section --}}
            <div style="margin-top: 40px; padding-top: 20px; border-top: 2px solid #e9ecef;">
                <p style="color: #6c757d; font-size: 14px; margin-bottom: 5px;">
                    Best regards,
                </p>
                <p style="margin: 10px 0 5px 0; font-weight: 600; color: #00844b; font-size: 16px;">
                    Carlos Olivencia Rodríguez
                </p>
                <p style="margin: 5px 0; color: #6c757d; font-size: 14px;">
                    Director<br>
                    Department of Auxiliary Services<br>
                    Universidad de Puerto Rico - Mayagüez
                </p>
                <p style="margin: 15px 0 5px 0; color: #6c757d; font-size: 13px;">
                    <i class="bi bi-envelope"></i> 📧 <a href="mailto:carlos.olivencia1@upr.edu" style="color: #00844b; text-decoration: none;">carlos.olivencia1@upr.edu</a><br>
                    <i class="bi bi-telephone"></i> 📞 (787) 832-4040 ext. 6401<br>
                    <i class="bi bi-building"></i> 🏢 UPRM Edificio Central Telefónica
                </p>
            </div>
        </div>

        <div class="footer">
            <p>&copy; {{ date('Y') }} Universidad de Puerto Rico - Recinto Universitario de Mayagüez</p>
            <p>This is an automated message. Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
