<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use App\Models\AlertSettings;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send test consolidated critical alert email to verify email configuration';

    /**
     * Execute the console command.
     */
    public function handle(NotificationService $notificationService): int
    {
        $recipients = $notificationService->getRecipients();
        $this->info("Testing consolidated critical alert email with REAL database data...");
        $this->info("Recipients: " . (empty($recipients) ? 'none' : implode(', ', array_slice($recipients, 0, 3)) . (count($recipients) > 3 ? ' +' . (count($recipients)-3) . ' more' : '')));
        $this->newLine();

        try {
            $alertSettings = AlertSettings::current();
            
            // Get REAL critical buildings from database
            $buildingsData = \DB::table('buildings as b')
                ->leftJoin('building_networks as bn', 'bn.building_id', '=', 'b.building_id')
                ->leftJoin('networks as n', 'n.network_id', '=', 'bn.network_id')
                ->leftJoin('devices as d', 'd.network_id', '=', 'n.network_id')
                ->select(
                    'b.building_id',
                    'b.name',
                    \DB::raw('COUNT(DISTINCT d.device_id) as total_devices'),
                    \DB::raw("SUM(CASE WHEN d.status = 'offline' THEN 1 ELSE 0 END) as offline_devices"),
                    \DB::raw("CASE 
                        WHEN COUNT(DISTINCT d.device_id) > 0 
                        THEN ROUND((SUM(CASE WHEN d.status = 'offline' THEN 1 ELSE 0 END) * 100.0) / COUNT(DISTINCT d.device_id), 1)
                        ELSE 0
                    END as offline_percentage")
                )
                ->groupBy('b.building_id', 'b.name')
                ->get();

            $criticalBuildings = $buildingsData->filter(function ($building) use ($alertSettings) {
                return $building->offline_percentage > $alertSettings->upper_threshold;
            });

            // Get REAL offline critical devices from database
            $offlineDevices = \App\Models\Devices::where('is_critical', true)
                ->where('status', 'offline')
                ->with(['network.buildings', 'extensions'])
                ->get();

            $this->info('📧 Sending test with REAL data...');
            $this->line("  Critical buildings: {$criticalBuildings->count()}");
            $this->line("  Offline critical devices: {$offlineDevices->count()}");
            $this->newLine();

            if ($criticalBuildings->isEmpty() && $offlineDevices->isEmpty()) {
                $this->warn('⚠️  No critical conditions found in database!');
                $this->info('Try marking a device as critical and offline:');
                $this->line('  php artisan tinker --execute="App\\Models\\Devices::first()->update([\'is_critical\'=>true,\'status\'=>\'offline\'])"');
                return self::SUCCESS;
            }

            Mail::send('emails.critical-alert', [
                'criticalBuildings' => $criticalBuildings,
                'offlineDevices' => $offlineDevices,
                'alertSettings' => $alertSettings,
            ], function ($message) use ($recipients, $criticalBuildings, $offlineDevices) {
                if (count($recipients) > 1) {
                    $message->to($recipients[0])->bcc(array_slice($recipients, 1));
                } elseif (count($recipients) === 1) {
                    $message->to($recipients[0]);
                }
                
                $subject = "TEST: CRITICAL ALERT - ";
                $parts = [];
                if ($criticalBuildings->isNotEmpty()) {
                    $parts[] = $criticalBuildings->count() . " Building(s) Critical";
                }
                if ($offlineDevices->isNotEmpty()) {
                    $parts[] = $offlineDevices->count() . " Critical Device(s) Offline";
                }
                $message->subject($subject . implode(', ', $parts));
            });

            $this->info('✓ Test email sent with real database data');
            $this->newLine();
            $this->info('✅ Test notification sent successfully!');
            $this->info("Sent to " . count($recipients) . " recipient(s)");
            $this->newLine();
            $this->comment('Note: Check your email inbox. Data matches current database state.');

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Failed to send test notification');
            $this->error('Error: ' . $e->getMessage());
            $this->newLine();
            $this->comment('Troubleshooting tips:');
            $this->line('1. Check MAIL_ADMIN_ADDRESS in .env file');
            $this->line('2. Verify Postfix is running: sudo systemctl status postfix');
            $this->line('3. Check mail queue: sudo postqueue -p');
            $this->line('4. Test sendmail: echo "Test" | sendmail -v your@email.com');
            
            return self::FAILURE;
        }
    }
}
