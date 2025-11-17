#!/bin/bash

# Test script for Forgot Password Feature
# Usage: ./test-forgot-password.sh [email]

echo "=================================="
echo "Forgot Password Feature Test"
echo "=================================="
echo ""

# Check if email is provided
if [ -z "$1" ]; then
    echo "❌ Error: Please provide an email address"
    echo "Usage: ./test-forgot-password.sh [email]"
    echo "Example: ./test-forgot-password.sh admin@uprm.edu"
    exit 1
fi

EMAIL=$1
APP_DIR="/var/www/uprm_voip_monitoring_system"

cd $APP_DIR || exit

echo "📧 Testing with email: $EMAIL"
echo ""

# Check if user exists
echo "1️⃣  Checking if user exists in database..."
USER_EXISTS=$(mysql -u voip_app -p'VoipApp2024!' mariadb -se "SELECT COUNT(*) FROM users WHERE email='$EMAIL';")

if [ "$USER_EXISTS" -eq 0 ]; then
    echo "❌ User with email $EMAIL does not exist in database"
    echo ""
    echo "Available users:"
    mysql -u voip_app -p'VoipApp2024!' mariadb -e "SELECT id, name, email, role FROM users;"
    exit 1
else
    echo "✅ User exists"
    mysql -u voip_app -p'VoipApp2024!' mariadb -e "SELECT id, name, email, role FROM users WHERE email='$EMAIL';"
fi

echo ""

# Check routes
echo "2️⃣  Checking routes..."
php artisan route:list --name=password 2>/dev/null | grep -E "(password.request|password.email|password.reset|password.store)" && echo "✅ Password reset routes exist" || echo "❌ Routes not found"

echo ""

# Check views
echo "3️⃣  Checking views..."
[ -f "resources/views/auth/forgot-password.blade.php" ] && echo "✅ forgot-password.blade.php exists" || echo "❌ forgot-password.blade.php missing"
[ -f "resources/views/auth/reset-password.blade.php" ] && echo "✅ reset-password.blade.php exists" || echo "❌ reset-password.blade.php missing"
[ -f "resources/views/emails/reset-password.blade.php" ] && echo "✅ reset-password email template exists" || echo "❌ email template missing"

echo ""

# Check controllers
echo "4️⃣  Checking controllers..."
[ -f "app/Http/Controllers/Auth/PasswordResetLinkController.php" ] && echo "✅ PasswordResetLinkController exists" || echo "❌ PasswordResetLinkController missing"
[ -f "app/Http/Controllers/Auth/NewPasswordController.php" ] && echo "✅ NewPasswordController exists" || echo "❌ NewPasswordController missing"

echo ""

# Check notification
echo "5️⃣  Checking notification..."
[ -f "app/Notifications/ResetPasswordNotification.php" ] && echo "✅ ResetPasswordNotification exists" || echo "❌ ResetPasswordNotification missing"

echo ""

# Check database table
echo "6️⃣  Checking database table..."
TABLE_EXISTS=$(mysql -u voip_app -p'VoipApp2024!' mariadb -se "SHOW TABLES LIKE 'password_reset_tokens';" | wc -l)
if [ "$TABLE_EXISTS" -eq 1 ]; then
    echo "✅ password_reset_tokens table exists"
    echo ""
    echo "Table structure:"
    mysql -u voip_app -p'VoipApp2024!' mariadb -e "DESCRIBE password_reset_tokens;"
else
    echo "❌ password_reset_tokens table missing"
fi

echo ""

# Check mail configuration
echo "7️⃣  Checking mail configuration..."
echo "Current mail settings from .env:"
grep -E "^MAIL_" .env | head -6

echo ""

# Test sending (dry run)
echo "8️⃣  Testing password reset request..."
echo "To test manually, visit: http://$(grep APP_URL .env | cut -d'=' -f2 | tr -d '"')/forgot-password"
echo ""

# Simulate password reset request
echo "Would you like to test sending a reset email? (y/n)"
read -r RESPONSE

if [ "$RESPONSE" = "y" ] || [ "$RESPONSE" = "Y" ]; then
    echo ""
    echo "Sending password reset request for $EMAIL..."
    
    # Using artisan tinker to trigger password reset
    php artisan tinker --execute="
        \$user = App\Models\User::where('email', '$EMAIL')->first();
        if (\$user) {
            \$token = Illuminate\Support\Str::random(60);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => \$user->email],
                ['token' => Hash::make(\$token), 'created_at' => now()]
            );
            \$user->sendPasswordResetNotification(\$token);
            echo 'Password reset email sent!';
        } else {
            echo 'User not found';
        }
    "
    
    echo ""
    echo "✅ Check your email or logs for the reset link"
    echo ""
    echo "If using MAIL_MAILER=log, check: storage/logs/laravel.log"
    echo "If using sendmail, check system mail logs"
fi

echo ""
echo "=================================="
echo "Test Complete!"
echo "=================================="
echo ""
echo "Next steps:"
echo "1. Visit the login page and click 'Forgot your password?'"
echo "2. Enter email: $EMAIL"
echo "3. Check your email for the reset link"
echo "4. Follow the link and set a new password"
echo ""
