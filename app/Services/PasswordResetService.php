<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;
use App\Helpers\SystemLogger;

/**
 * Enhanced Password Reset Service
 * 
 * Provides explicit token invalidation and comprehensive logging
 * for password reset operations.
 */
class PasswordResetService
{
    /**
     * Send a password reset link with explicit old token invalidation.
     *
     * @param string $email
     * @return string Password reset status
     */
    public function sendResetLinkWithInvalidation(string $email): string
    {
        // Check for existing tokens
        $existingTokens = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->get();

        if ($existingTokens->isNotEmpty()) {
            SystemLogger::logInfo(
                'Invalidating previous password reset tokens',
                [
                    'email' => $email,
                    'previous_tokens_count' => $existingTokens->count(),
                    'tokens_created_at' => $existingTokens->pluck('created_at')->toArray()
                ]
            );

            // Explicitly delete old tokens before creating new one
            DB::table('password_reset_tokens')->where('email', $email)->delete();
        }

        // Send the reset link (Laravel will create new token)
        $status = Password::sendResetLink(['email' => $email]);

        if ($status === Password::RESET_LINK_SENT) {
            SystemLogger::logInfo(
                'Password reset link sent with token invalidation',
                ['email' => $email]
            );
        } else {
            SystemLogger::logWarning(
                'Password reset link failed after token invalidation',
                ['email' => $email, 'status' => $status]
            );
        }

        return $status;
    }

    /**
     * Validate that only one token exists for an email.
     *
     * @param string $email
     * @return array Validation result
     */
    public function validateTokenUniqueness(string $email): array
    {
        $tokens = DB::table('password_reset_tokens')
            ->where('email', $email)
            ->get();

        return [
            'is_unique' => $tokens->count() <= 1,
            'token_count' => $tokens->count(),
            'tokens' => $tokens->map(function ($token) {
                return [
                    'created_at' => $token->created_at,
                    'token_prefix' => substr($token->token, 0, 20) . '...'
                ];
            })->toArray()
        ];
    }

    /**
     * Clean up expired tokens (older than configured expiry time).
     *
     * @return int Number of tokens cleaned up
     */
    public function cleanupExpiredTokens(): int
    {
        $expireMinutes = config('auth.passwords.users.expire', 60);
        $cutoffTime = now()->subMinutes($expireMinutes);

        $expiredCount = DB::table('password_reset_tokens')
            ->where('created_at', '<', $cutoffTime)
            ->count();

        if ($expiredCount > 0) {
            DB::table('password_reset_tokens')
                ->where('created_at', '<', $cutoffTime)
                ->delete();

            SystemLogger::logInfo(
                'Cleaned up expired password reset tokens',
                ['expired_tokens_removed' => $expiredCount]
            );
        }

        return $expiredCount;
    }

    /**
     * Get token statistics for monitoring.
     *
     * @return array Token statistics
     */
    public function getTokenStatistics(): array
    {
        $total = DB::table('password_reset_tokens')->count();
        $last24Hours = DB::table('password_reset_tokens')
            ->where('created_at', '>=', now()->subHours(24))
            ->count();

        $duplicateEmails = DB::table('password_reset_tokens')
            ->select('email', DB::raw('COUNT(*) as count'))
            ->groupBy('email')
            ->having('count', '>', 1)
            ->get();

        return [
            'total_tokens' => $total,
            'tokens_last_24h' => $last24Hours,
            'duplicate_email_count' => $duplicateEmails->count(),
            'duplicate_emails' => $duplicateEmails->pluck('email')->toArray()
        ];
    }
}