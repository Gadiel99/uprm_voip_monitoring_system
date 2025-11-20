<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\PasswordResetService;

class PasswordResetTokens extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'password-reset:manage {action} {--email=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage password reset tokens (stats, cleanup, validate)';

    /**
     * Password Reset Service
     */
    protected $passwordResetService;

    /**
     * Create a new command instance.
     */
    public function __construct(PasswordResetService $passwordResetService)
    {
        parent::__construct();
        $this->passwordResetService = $passwordResetService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $action = $this->argument('action');

        switch ($action) {
            case 'stats':
                $this->showStatistics();
                break;
            case 'cleanup':
                $this->cleanupExpiredTokens();
                break;
            case 'validate':
                $this->validateTokens();
                break;
            case 'check-email':
                $this->checkSpecificEmail();
                break;
            default:
                $this->error('Invalid action. Available actions: stats, cleanup, validate, check-email');
                return self::FAILURE;
        }

        return self::SUCCESS;
    }

    /**
     * Show password reset token statistics.
     */
    protected function showStatistics()
    {
        $this->info('📊 Password Reset Token Statistics');
        $this->line('');

        $stats = $this->passwordResetService->getTokenStatistics();

        $this->line("Total tokens in system: {$stats['total_tokens']}");
        $this->line("Tokens created in last 24h: {$stats['tokens_last_24h']}");
        $this->line("Emails with duplicate tokens: {$stats['duplicate_email_count']}");

        if ($stats['duplicate_email_count'] > 0) {
            $this->warn('⚠️  Emails with multiple tokens:');
            foreach ($stats['duplicate_emails'] as $email) {
                $this->line("  • $email");
            }
        } else {
            $this->info('✅ No duplicate tokens found');
        }
    }

    /**
     * Clean up expired tokens.
     */
    protected function cleanupExpiredTokens()
    {
        $this->info('🧹 Cleaning up expired password reset tokens...');
        
        $cleanedUp = $this->passwordResetService->cleanupExpiredTokens();
        
        if ($cleanedUp > 0) {
            $this->info("✅ Cleaned up {$cleanedUp} expired tokens");
        } else {
            $this->info('✅ No expired tokens found');
        }
    }

    /**
     * Validate all tokens for uniqueness.
     */
    protected function validateTokens()
    {
        $this->info('🔍 Validating token uniqueness...');
        
        $stats = $this->passwordResetService->getTokenStatistics();
        
        if ($stats['duplicate_email_count'] > 0) {
            $this->error("❌ Found {$stats['duplicate_email_count']} emails with multiple tokens");
            foreach ($stats['duplicate_emails'] as $email) {
                $validation = $this->passwordResetService->validateTokenUniqueness($email);
                $this->line("  • $email: {$validation['token_count']} tokens");
            }
        } else {
            $this->info('✅ All tokens are unique');
        }
    }

    /**
     * Check tokens for a specific email.
     */
    protected function checkSpecificEmail()
    {
        $email = $this->option('email');
        
        if (!$email) {
            $this->error('❌ Please specify an email with --email=example@domain.com');
            return;
        }

        $this->info("🔍 Checking tokens for: $email");
        
        $validation = $this->passwordResetService->validateTokenUniqueness($email);
        
        $this->line("Token count: {$validation['token_count']}");
        $this->line("Is unique: " . ($validation['is_unique'] ? 'YES' : 'NO'));
        
        if ($validation['token_count'] > 0) {
            $this->line('Tokens:');
            foreach ($validation['tokens'] as $index => $token) {
                $this->line("  " . ($index + 1) . ". Created: {$token['created_at']} (Token: {$token['token_prefix']})");
            }
        } else {
            $this->info('✅ No tokens found for this email');
        }
    }
}
