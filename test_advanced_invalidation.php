<?php
/*
 * Comprehensive test for password reset token invalidation with actual tokens
 */

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use App\Models\User;

echo "=== Advanced Password Reset Token Invalidation Test ===\n\n";

$testEmail = 'sergio.melendez2@upr.edu';

// Ensure user exists
$user = User::where('email', $testEmail)->first();
if (!$user) {
    echo "❌ Test user doesn't exist\n";
    exit(1);
}

echo "1. Clearing any existing tokens...\n";
DB::table('password_reset_tokens')->where('email', $testEmail)->delete();

echo "\n2. Manually creating first token (simulating first email)...\n";
$firstPlainToken = Str::random(64);
$firstHashedToken = Hash::make($firstPlainToken);

DB::table('password_reset_tokens')->insert([
    'email' => $testEmail,
    'token' => $firstHashedToken,
    'created_at' => now()->subMinutes(5) // 5 minutes ago
]);

echo "First token (plain): " . substr($firstPlainToken, 0, 20) . "...\n";
echo "First token (hashed): " . substr($firstHashedToken, 0, 30) . "...\n";

// Verify first token exists
$tokensAfterFirst = DB::table('password_reset_tokens')->where('email', $testEmail)->get();
echo "Tokens after first: {$tokensAfterFirst->count()}\n";

sleep(2);

echo "\n3. Sending second password reset email via Laravel...\n";
$status = Password::sendResetLink(['email' => $testEmail]);
echo "Laravel sendResetLink status: $status\n";

echo "\n4. Checking token state after second email...\n";
$tokensAfterSecond = DB::table('password_reset_tokens')->where('email', $testEmail)->get();
echo "Tokens after second: {$tokensAfterSecond->count()}\n";

if ($tokensAfterSecond->count() > 1) {
    echo "❌ PROBLEM: Multiple tokens exist!\n";
    foreach ($tokensAfterSecond as $index => $token) {
        echo "  Token " . ($index + 1) . ": " . substr($token->token, 0, 30) . "... (created: {$token->created_at})\n";
    }
} else {
    $finalToken = $tokensAfterSecond->first();
    echo "✅ GOOD: Only one token exists\n";
    echo "  Final token: " . substr($finalToken->token, 0, 30) . "... (created: {$finalToken->created_at})\n";
    
    // Test if old token is invalid
    echo "\n5. Testing if old token is invalid...\n";
    $isOldTokenValid = Hash::check($firstPlainToken, $finalToken->token);
    if ($isOldTokenValid) {
        echo "❌ PROBLEM: Old token is still valid!\n";
    } else {
        echo "✅ GOOD: Old token is invalid (new token generated)\n";
    }
}

echo "\n6. Testing password reset with current token...\n";
if ($tokensAfterSecond->count() == 1) {
    $currentTokenRecord = $tokensAfterSecond->first();
    
    // We need to extract the plain token from what Laravel generated
    // Let's generate a fresh token for testing
    echo "Generating fresh token for reset test...\n";
    $freshPlainToken = Str::random(64);
    $freshHashedToken = Hash::make($freshPlainToken);
    
    DB::table('password_reset_tokens')->where('email', $testEmail)->update([
        'token' => $freshHashedToken,
        'created_at' => now()
    ]);
    
    echo "Fresh test token: " . substr($freshPlainToken, 0, 20) . "...\n";
    echo "Test URL: " . config('app.url') . "/reset-password/{$freshPlainToken}?email=" . urlencode($testEmail) . "\n";
}

echo "\n=== Final Test Summary ===\n";
$finalCount = DB::table('password_reset_tokens')->where('email', $testEmail)->count();
if ($finalCount == 1) {
    echo "✅ Token invalidation working: Only 1 token exists\n";
    echo "✅ Old tokens are automatically removed\n";
    echo "✅ Security requirement satisfied\n";
} else {
    echo "❌ Token invalidation NOT working: $finalCount tokens exist\n";
    echo "❌ Security vulnerability: Old links may still work\n";
}

echo "\nCleanup...\n";
DB::table('password_reset_tokens')->where('email', $testEmail)->delete();
echo "✅ Cleanup completed\n";