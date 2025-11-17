<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;
use App\Models\AlertSettings;

class CheckAndNotify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check system status and send email notifications for critical conditions';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        try {
            $settings = AlertSettings::current();
            if (!($settings->is_active ?? true) || !($settings->email_notifications_enabled ?? true)) {
                $this->info('Email notifications disabled — skipping.');
                return Command::SUCCESS;
            }
        } catch (\Throwable $e) {
            $this->warn('Could not read AlertSettings; proceeding with default behavior.');
        }

        $this->info('Checking for critical conditions...');
        $notificationService->checkAndNotify();
        $this->info('Notification check completed.');
        
        return Command::SUCCESS;
    }
}
