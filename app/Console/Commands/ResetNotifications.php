<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\NotificationService;

class ResetNotifications extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'notifications:reset {--send : Immediately re-check and send after reset}';

    /**
     * The console command description.
     */
    protected $description = 'Clear all cached notification states so next run treats everything as new; optional immediate resend.';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $this->info('Resetting notification cached states...');
        $summary = $notificationService->resetAllStates();
        $this->line("Cleared building states: {$summary['buildings']}");
        $this->line("Cleared device states:   {$summary['devices']}");

        if ($this->option('send')) {
            $this->newLine();
            $this->info('Triggering immediate consolidated notification check...');
            $notificationService->checkAndNotify();
            $this->info('Notification check finished.');
        }

        $this->newLine();
        $this->comment('Next scheduled ETL / notifications:check run will treat all states as fresh.');
        $this->comment('If using MAIL_ALERTS_TO, ensure distribution list includes every intended recipient.');

        return Command::SUCCESS;
    }
}
