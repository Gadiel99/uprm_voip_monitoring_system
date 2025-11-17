<?php

namespace App\Services;

use App\Models\Building;
use App\Models\Devices;
use App\Models\AlertSettings;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Notification Service
 * 
 * Handles email notifications for critical building states and offline critical devices.
 * Uses caching to prevent duplicate notifications within a cooldown period.
 */
class NotificationService
{
    /**
     * Cooldown period for notifications in minutes.
     * Prevents spam by not sending duplicate notifications for the same issue.
     */
    protected int $notificationCooldown = 30;

    /**
     * Whether to automatically reset notification states when conditions recover.
     * Set via NOTIFICATIONS_AUTO_RESET env variable (defaults to true).
     */
    protected bool $autoReset;

    public function __construct()
    {
        $this->autoReset = env('NOTIFICATIONS_AUTO_RESET', true);
    }

    /**
     * Return a list of recipient email addresses.
     * Priority:
     * 1) If MAIL_ALERTS_TO is set, use that single distribution address (recommended)
     * 2) Otherwise, send to all users with valid emails
     * 3) Fallback to MAIL_ADMIN_ADDRESS if none
     *
     * @return array<string>
     */
    public function getRecipients(): array
    {
        $dist = env('MAIL_ALERTS_TO');
        if ($dist && filter_var($dist, FILTER_VALIDATE_EMAIL)) {
            return [$dist];
        }

        $emails = User::query()
            ->whereNotNull('email')
            ->pluck('email')
            ->filter(function ($email) {
                return filter_var($email, FILTER_VALIDATE_EMAIL);
            })
            ->unique()
            ->values()
            ->all();

        if (empty($emails)) {
            $fallback = env('MAIL_ADMIN_ADDRESS');
            if ($fallback && filter_var($fallback, FILTER_VALIDATE_EMAIL)) {
                $emails = [$fallback];
            }
        }

        return $emails;
    }

    /**
     * Check and send consolidated notification for all critical conditions.
     * Sends a single email with all critical buildings and offline critical devices.
     * 
     * @return void
     */
    public function checkAndNotify(): void
    {
        $alertSettings = AlertSettings::current();
        
        // Don't send notifications if alerts are inactive or email notifications are disabled
        if (!$alertSettings->is_active || !$alertSettings->email_notifications_enabled) {
            return;
        }

        // Gather all critical buildings
        $buildingsData = $this->getAllBuildingsWithStats();
        $criticalBuildings = collect();

        foreach ($buildingsData as $building) {
            $level = $alertSettings->getAlertLevel((float) $building->offline_percentage);
            
            // Always include critical buildings (no state tracking)
            if ($level === 'red') {
                $criticalBuildings->push($building);
            }
        }

        // Gather all offline critical devices
        $offlineDevices = Devices::where('is_critical', true)
            ->where('status', 'offline')
            ->with(['network.buildings', 'extensions'])
            ->get()
            ->map(function($device) {
                // Populate owner from extensions if not set
                if (empty($device->owner) && $device->extensions->isNotEmpty()) {
                    $firstExt = $device->extensions->first();
                    $device->owner = trim(($firstExt->user_first_name ?? '') . ' ' . ($firstExt->user_last_name ?? ''));
                }
                return $device;
            });

        // Send consolidated notification every time if there are any critical conditions
        if ($criticalBuildings->isNotEmpty() || $offlineDevices->isNotEmpty()) {
            $this->sendConsolidatedNotification($criticalBuildings, $offlineDevices, $alertSettings);
        }
    }



    /**
     * Get buildings with device statistics.
     * 
     * @return \Illuminate\Support\Collection
     */
    protected function getAllBuildingsWithStats()
    {
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

        return $buildingsData;
    }

    /**
     * Send consolidated notification with all critical buildings and offline devices.
     * 
     * @param \Illuminate\Support\Collection $criticalBuildings
     * @param \Illuminate\Support\Collection $offlineDevices
     * @param AlertSettings $alertSettings
     * @return void
     */
    protected function sendConsolidatedNotification($criticalBuildings, $offlineDevices, $alertSettings): void
    {
        try {
            $recipients = $this->getRecipients();

            if (count($recipients) > 0) {
                // Build subject line
                $subject = "CRITICAL ALERT: ";
                $parts = [];
                if ($criticalBuildings->isNotEmpty()) {
                    $parts[] = $criticalBuildings->count() . " Building(s) Critical";
                }
                if ($offlineDevices->isNotEmpty()) {
                    $parts[] = $offlineDevices->count() . " Critical Device(s) Offline";
                }
                $subject .= implode(', ', $parts);

                // Send ONE email with all recipients in a single transaction
                Log::info('Sending consolidated alert to all recipients', ['recipients' => $recipients]);
                Mail::send('emails.critical-alert', [
                    'criticalBuildings' => $criticalBuildings,
                    'offlineDevices' => $offlineDevices,
                    'alertSettings' => $alertSettings,
                ], function ($message) use ($recipients, $subject) {
                    $message->to($recipients)->subject($subject);
                });
                
                Log::info("Consolidated critical alert sent", [
                    'critical_buildings_count' => $criticalBuildings->count(),
                    'offline_devices_count' => $offlineDevices->count(),
                    'recipients_count' => count($recipients),
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to send consolidated critical alert", [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Reset all cached notification states for buildings and devices.
     * Returns summary counts of cleared states.
     *
     * @return array{buildings:int,devices:int}
     */
    public function resetAllStates(): array
    {
        $buildingIds = Building::query()->pluck('building_id');
        $deviceIds   = Devices::query()->pluck('device_id');

        $clearedBuildings = 0;
        foreach ($buildingIds as $bid) {
            if (Cache::forget("state_building_{$bid}")) {
                $clearedBuildings++;
            }
            Cache::forget("notification_building_{$bid}");
        }

        $clearedDevices = 0;
        foreach ($deviceIds as $did) {
            if (Cache::forget("state_device_{$did}")) {
                $clearedDevices++;
            }
            Cache::forget("notification_device_{$did}");
        }

        Log::info('Notification states reset', [
            'buildings_cleared' => $clearedBuildings,
            'devices_cleared' => $clearedDevices,
        ]);

        return [
            'buildings' => $clearedBuildings,
            'devices' => $clearedDevices,
        ];
    }
    /**
     * Clear notification cache for a specific building.
     * Useful when a building recovers from critical state.
     * 
     * @param int $buildingId
     * @return void
     */
    public function clearBuildingNotification(int $buildingId): void
    {
        Cache::forget("notification_building_{$buildingId}");
        Cache::forget("state_building_{$buildingId}");
    }

    /**
     * Clear notification cache for a specific device.
     * Useful when a device comes back online.
     * 
     * @param int $deviceId
     * @return void
     */
    public function clearDeviceNotification(int $deviceId): void
    {
        Cache::forget("notification_device_{$deviceId}");
        Cache::forget("state_device_{$deviceId}");
    }
}
