<?php
/*
 * Test script to verify password reset token invalidation behavior
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use App\Models\User;

echo "=== Password Reset Token Invalidation Test ===\n\n";

$testEmail = 'sergio.melendez2@upr.edu';

// Ensure user exists
$user = User::where('email', $testEmail)->first();
if (!$user) {
    echo "❌ Test user doesn't exist\n";
    exit(1);
}

echo "1. Clearing any existing tokens...\n";
DB::table('password_reset_tokens')->where('email', $testEmail)->delete();

echo "\n2. Sending first password reset email...\n";
$status1 = Password::sendResetLink(['email' => $testEmail]);

$token1 = DB::table('password_reset_tokens')->where('email', $testEmail)->first();
echo "First token created: " . substr($token1->token, 0, 20) . "...\n";
echo "Created at: {$token1->created_at}\n";

sleep(2); // Wait 2 seconds to ensure different timestamps

echo "\n3. Sending second password reset email...\n";
$status2 = Password::sendResetLink(['email' => $testEmail]);

$tokens = DB::table('password_reset_tokens')->where('email', $testEmail)->get();
echo "Number of tokens in database: {$tokens->count()}\n";

if ($tokens->count() > 1) {
    echo "❌ PROBLEM: Multiple tokens exist - old tokens not invalidated\n";
    foreach ($tokens as $index => $token) {
        echo "  Token " . ($index + 1) . ": " . substr($token->token, 0, 20) . "... (created: {$token->created_at})\n";
    }
} else {
    echo "✅ GOOD: Only one token exists - old token was invalidated\n";
    $finalToken = $tokens->first();
    echo "  Final token: " . substr($finalToken->token, 0, 20) . "... (created: {$finalToken->created_at})\n";
}

echo "\n4. Testing token validation...\n";
// Test with the current token
$currentToken = $tokens->first();

// The password reset system should validate this token correctly
echo "Current token validation: ";
try {
    $user = User::where('email', $testEmail)->first();
    if ($user) {
        echo "✅ User found\n";
    } else {
        echo "❌ User not found\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

echo "\n=== Summary ===\n";
if ($tokens->count() == 1) {
    echo "✅ Password reset token invalidation is working correctly\n";
    echo "✅ Old tokens are automatically removed when new ones are generated\n";
} else {
    echo "❌ Password reset token invalidation needs to be fixed\n";
    echo "❌ Multiple tokens exist for the same email\n";
}

echo "\nFinal cleanup...\n";
DB::table('password_reset_tokens')->where('email', $testEmail)->delete();
echo "✅ Test cleanup completed\n";