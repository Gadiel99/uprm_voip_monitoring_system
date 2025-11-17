# Forgot Password Feature

## Overview
The forgot password feature allows users to reset their password via email when they can't remember their credentials.

## Features
✅ Password reset request page with email input
✅ Custom branded password reset email
✅ Secure token-based reset link (expires in 60 minutes)
✅ Password reset form with confirmation
✅ Link to forgot password on login page
✅ Consistent Bootstrap 5 design matching the login page

## How It Works

### 1. Request Password Reset
- User clicks "Forgot your password?" on the login page
- User enters their email address
- System sends a password reset email with a secure token

### 2. Receive Email
- User receives an email with a "Reset Password" button
- Email includes the UPRM branding and logo
- Link expires in 60 minutes for security

### 3. Reset Password
- User clicks the link in the email
- User enters new password and confirmation
- System validates and updates the password
- User is redirected to login page

## Routes

| Route | Method | Purpose |
|-------|--------|---------|
| `/forgot-password` | GET | Display forgot password form |
| `/forgot-password` | POST | Send password reset email |
| `/reset-password/{token}` | GET | Display reset password form |
| `/reset-password` | POST | Update password |

## Files Modified/Created

### Views
- ✅ `resources/views/auth/login.blade.php` - Added "Forgot your password?" link
- ✅ `resources/views/auth/forgot-password.blade.php` - Redesigned with Bootstrap 5
- ✅ `resources/views/auth/reset-password.blade.php` - Redesigned with Bootstrap 5
- ✅ `resources/views/emails/reset-password.blade.php` - Custom branded email template

### Controllers
- ✅ `app/Http/Controllers/Auth/PasswordResetLinkController.php` - Already exists
- ✅ `app/Http/Controllers/Auth/NewPasswordController.php` - Already exists

### Models & Notifications
- ✅ `app/Models/User.php` - Added custom password reset notification method
- ✅ `app/Notifications/ResetPasswordNotification.php` - Custom notification class

### Routes
- ✅ `routes/auth.php` - Password reset routes already defined

## Testing the Feature

### 1. Access the Forgot Password Page
```bash
# Visit in browser:
http://your-domain/forgot-password
```

### 2. Test with an Existing User
1. Go to login page: `http://your-domain/login`
2. Click "Forgot your password?" link
3. Enter a valid email address from the database
4. Submit the form

### 3. Check Email
Since you're using sendmail, check your mail logs or inbox:
```bash
# Check mail logs (varies by system)
sudo tail -f /var/log/mail.log
# or
sudo tail -f /var/log/maillog
```

### 4. Testing Locally
For local testing without a mail server, you can temporarily change the mail driver to `log`:
```env
MAIL_MAILER=log
```

Then check the Laravel logs:
```bash
tail -f storage/logs/laravel.log
```

### 5. Manual Testing Checklist
- [ ] Login page shows "Forgot your password?" link
- [ ] Forgot password page displays properly
- [ ] Form validation works (requires valid email)
- [ ] Success message appears after submitting
- [ ] Email is sent with reset link
- [ ] Reset link opens password reset form
- [ ] Password reset form validates new password
- [ ] Password successfully updates
- [ ] User can login with new password
- [ ] Old reset links expire after use
- [ ] Links expire after 60 minutes

## Email Configuration

Current configuration (from .env):
```env
MAIL_MAILER=sendmail
MAIL_SENDMAIL_PATH="/usr/sbin/sendmail -bs -i"
MAIL_FROM_ADDRESS="voip-monitoring@uprm.edu"
MAIL_FROM_NAME="UPRM VoIP Monitoring System"
```

### Alternative Email Configurations

#### For SMTP Server:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.uprm.edu
MAIL_PORT=587
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

#### For Testing (Mailtrap, Mailhog):
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
```

## Security Features

1. **Token Expiration**: Reset links expire after 60 minutes (configurable in `config/auth.php`)
2. **One-Time Use**: Tokens are invalidated after use
3. **Password Validation**: New passwords must meet minimum requirements
4. **Rate Limiting**: Throttled to prevent abuse
5. **Email Verification**: Only sends to registered email addresses

## Customization

### Change Token Expiration Time
Edit `config/auth.php`:
```php
'passwords' => [
    'users' => [
        'provider' => 'users',
        'table' => 'password_reset_tokens',
        'expire' => 60, // Change this value (in minutes)
        'throttle' => 60,
    ],
],
```

### Customize Email Content
Edit `resources/views/emails/reset-password.blade.php`

### Change Password Requirements
Edit `config/auth.php` or use custom validation rules in the controller.

## Troubleshooting

### Email Not Sending
1. Check mail configuration in `.env`
2. Verify sendmail is installed: `which sendmail`
3. Check Laravel logs: `storage/logs/laravel.log`
4. Test with `MAIL_MAILER=log` to see email content

### Token Expired Error
- Tokens expire after 60 minutes by default
- User needs to request a new reset link

### Link Not Working
1. Check if APP_URL in .env matches your domain
2. Verify password_reset_tokens table exists
3. Clear Laravel cache: `php artisan config:clear`

## Database Table

The feature uses the `password_reset_tokens` table:
```sql
CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);
```

To verify the table exists:
```bash
php artisan tinker
>>> DB::table('password_reset_tokens')->count();
```

## Queue Support (Optional)

For better performance with high-volume emails, configure queue:

1. Update `.env`:
```env
QUEUE_CONNECTION=database
```

2. Make notification queueable (already implemented)

3. Run queue worker:
```bash
php artisan queue:work
```

## Summary

The forgot password feature is now fully integrated into your UPRM VoIP Monitoring System with:
- Professional, branded email templates
- Consistent UI design matching your login page
- Secure token-based password reset
- Comprehensive error handling
- Ready for production use

Test the feature and verify emails are being sent correctly before deploying to production.
