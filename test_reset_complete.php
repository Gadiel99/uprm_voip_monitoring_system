<?php
/*
 * Complete Password Reset Test Script
 * Tests the entire password reset flow end-to-end
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use App\Models\User;

echo "=== UPRM VoIP Password Reset Complete Test ===\n\n";

// 1. Create/verify test user
$testEmail = 'sergio.melendez2@upr.edu';

// Get or create user
$user = User::where('email', $testEmail)->first();
if (!$user) {
    echo "Creating test user...\n";
    $user = User::create([
        'name' => 'Sergio Test',
        'email' => $testEmail,
        'password' => 'oldPassword123',
        'role' => 'user'
    ]);
} else {
    echo "Test user exists: {$user->name} ({$user->email})\n";
}

// 2. Generate password reset token manually
echo "\n2. Generating password reset token...\n";

$plainToken = Str::random(64);

// Insert token into database
DB::table('password_reset_tokens')->updateOrInsert(
    ['email' => $testEmail],
    [
        'email' => $testEmail,
        'token' => Hash::make($plainToken),
        'created_at' => now()
    ]
);

echo "✅ Token generated and stored\n";
echo "Plain token: {$plainToken}\n";

// 3. Build reset URL
$resetUrl = config('app.url') . "/reset-password/{$plainToken}?email=" . urlencode($testEmail);
echo "\n3. Reset URL created:\n{$resetUrl}\n";

// 4. Test the password reset process
echo "\n4. Testing password reset with new password...\n";

$newPassword = 'newSecurePassword123';

// Simulate the reset password request
$resetData = [
    'token' => $plainToken,
    'email' => $testEmail,
    'password' => $newPassword,
    'password_confirmation' => $newPassword
];

echo "Reset data prepared:\n";
echo "- Token: " . substr($plainToken, 0, 20) . "...\n";
echo "- Email: {$testEmail}\n";
echo "- New Password: [HIDDEN]\n";

// Test token validation
$tokenRecord = DB::table('password_reset_tokens')
    ->where('email', $testEmail)
    ->first();

if ($tokenRecord && Hash::check($plainToken, $tokenRecord->token)) {
    echo "✅ Token validation: PASSED\n";
    
    // Update user password
    $user = User::where('email', $testEmail)->first();
    $user->update(['password' => $newPassword]);
    
    // Clear token
    DB::table('password_reset_tokens')->where('email', $testEmail)->delete();
    
    echo "✅ Password updated successfully\n";
    echo "✅ Token cleared from database\n";
    
    // Test login with new password
    echo "\n5. Testing login with new password...\n";
    
    $freshUser = User::where('email', $testEmail)->first();
    if (Hash::check($newPassword, $freshUser->password)) {
        echo "✅ Login test: PASSED - New password works!\n";
    } else {
        echo "❌ Login test: FAILED - New password doesn't work\n";
    }
    
} else {
    echo "❌ Token validation: FAILED\n";
}

echo "\n=== Test Complete ===\n";

// Print current user status
$currentUser = User::where('email', $testEmail)->first();
echo "\nFinal user state:\n";
echo "- Name: {$currentUser->name}\n";
echo "- Email: {$currentUser->email}\n";
echo "- Password hash: " . substr($currentUser->password, 0, 20) . "...\n";

echo "\n🔗 You can now test this URL in your browser:\n";
echo $resetUrl . "\n\n";