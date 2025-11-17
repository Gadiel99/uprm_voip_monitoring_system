<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            padding: 14px 40px;
            background-color: #00844b;
            color: white !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #006e3d;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #00844b;
            padding: 15px;
            margin: 20px 0;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            text-align: center;
            color: #777;
            font-size: 12px;
            border-top: 1px solid #e0e0e0;
        }
        .footer p {
            margin: 5px 0;
        }
        .link-text {
            word-break: break-all;
            color: #00844b;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 Password Reset Request</h1>
            <p style="margin: 10px 0 0 0; font-size: 14px;">UPRM VoIP Monitoring System</p>
        </div>

        <div class="content">
            <h2>Hello, {{ $notifiable->name ?? 'User' }}!</h2>

            <p>
                You are receiving this email because we received a password reset request for your account.
            </p>

            <div class="button-container">
                <a href="{{ $url }}" class="button">Reset Password</a>
            </div>

            <div class="info-box">
                <strong>⏱️ Link Expiration:</strong> This password reset link will expire in {{ $count }} minutes.
            </div>

            <p>
                If you're having trouble clicking the "Reset Password" button, copy and paste the URL below into your web browser:
            </p>

            <p class="link-text">
                {{ $url }}
            </p>

            <p style="margin-top: 30px;">
                If you did not request a password reset, no further action is required. Your password will remain unchanged.
            </p>

            <p style="margin-top: 20px; color: #999; font-size: 13px;">
                <strong>Security Tip:</strong> Never share your password with anyone. UPRM IT staff will never ask for your password via email.
            </p>
        </div>

        <div class="footer">
            <p><strong>UPRM VoIP Monitoring System</strong></p>
            <p>University of Puerto Rico at Mayagüez</p>
            <p>© {{ date('Y') }} All rights reserved.</p>
            <p style="margin-top: 10px;">
                This is an automated message, please do not reply to this email.
            </p>
        </div>
    </div>
</body>
</html>
