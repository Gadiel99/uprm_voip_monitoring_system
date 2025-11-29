<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class ShowNotificationStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display current notification tracking status and frequency information';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $tracking = Cache::get('notification_tracking');

        if (!$tracking) {
            $this->info('📭 No active notification tracking found.');
            $this->info('This means either:');
            $this->info('  - No critical conditions have been detected yet');
            $this->info('  - Critical conditions were resolved and tracking was reset');
            return 0;
        }

        $this->info('📊 Notification Tracking Status');
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');

        // Basic tracking info
        $this->table(
            ['Metric', 'Value'],
            [
                ['Notifications Sent', $tracking['count'] ?? 0],
                ['First Sent', $tracking['first_sent'] ? Carbon::parse($tracking['first_sent'])->format('Y-m-d H:i:s') : 'N/A'],
                ['Last Sent', $tracking['last_sent'] ? Carbon::parse($tracking['last_sent'])->format('Y-m-d H:i:s') : 'N/A'],
            ]
        );

        // Frequency information
        $count = $tracking['count'] ?? 0;
        if ($count < 3) {
            $currentFrequency = '5 minutes';
            $nextTransition = 3 - $count;
            $this->info("📧 Current Frequency: {$currentFrequency}");
            $this->info("⏭️  Next {$nextTransition} email(s) will be sent every 5 minutes");
            $this->info("⏰ After that, frequency will reduce to 1 hour");
        } else {
            $currentFrequency = '1 hour (60 minutes)';
            $this->info("📧 Current Frequency: {$currentFrequency}");
            $this->info("✅ Progressive frequency active - emails sent hourly");
        }

        // Time until next notification
        if ($tracking['last_sent']) {
            $lastSent = Carbon::parse($tracking['last_sent']);
            $now = Carbon::now();
            $minutesSinceLast = $now->diffInMinutes($lastSent);
            
            $requiredMinutes = $count < 3 ? 5 : 60;
            $minutesUntilNext = max(0, $requiredMinutes - $minutesSinceLast);
            
            $this->line('');
            if ($minutesUntilNext > 0) {
                $this->info("⏳ Next notification eligible in: {$minutesUntilNext} minute(s)");
            } else {
                $this->warn("✓ Next notification can be sent now (if conditions still critical)");
            }
        }

        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->comment('💡 Tip: Run "php artisan notifications:check" to manually check for alerts');

        return 0;
    }
}
