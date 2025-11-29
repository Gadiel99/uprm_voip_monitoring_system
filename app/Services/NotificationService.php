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
 * Progressive notification frequency:
 * - First 3 emails: Every 5 minutes (15 minutes total)
 * - After 3rd email: Every 1 hour
 */
class NotificationService
{
    /**
     * Initial notification frequency (first 3 emails) in minutes.
     */
    protected int $initialFrequency = 5;

    /**
     * Reduced notification frequency (after 3rd email) in minutes.
     */
    protected int $reducedFrequency = 60;

    /**
     * Number of initial frequent notifications before reducing frequency.
     */
    protected int $initialNotificationCount = 3;

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
     * Uses progressive frequency: 5 minutes for first 3 emails, then hourly.
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

        // Send consolidated notification if there are any critical conditions
        // and if enough time has passed based on progressive frequency
        if ($criticalBuildings->isNotEmpty() || $offlineDevices->isNotEmpty()) {
            if ($this->shouldSendNotification()) {
                $this->sendConsolidatedNotification($criticalBuildings, $offlineDevices, $alertSettings);
            }
        } else {
            // Reset notification tracking when no critical conditions exist
            $this->resetNotificationTracking();
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
     * Determine if notification should be sent based on progressive frequency.
     * - First 3 emails: Every 5 minutes
     * - After 3rd email: Every 1 hour
     * 
     * @return bool
     */
    protected function shouldSendNotification(): bool
    {
        $cacheKey = 'notification_tracking';
        $tracking = Cache::get($cacheKey, [
            'count' => 0,
            'last_sent' => null,
            'first_sent' => null,
        ]);

        $now = now();
        
        // First notification - always send
        if ($tracking['count'] === 0 || $tracking['last_sent'] === null) {
            return true;
        }

        $lastSent = \Carbon\Carbon::parse($tracking['last_sent']);
        // Use absolute difference to avoid timezone issues
        $minutesSinceLastSent = abs($now->diffInMinutes($lastSent, false));

        // Determine required frequency based on count
        if ($tracking['count'] < $this->initialNotificationCount) {
            // First 3 emails: check if 5 minutes have passed
            $requiredFrequency = $this->initialFrequency;
        } else {
            // After 3rd email: check if 1 hour has passed
            $requiredFrequency = $this->reducedFrequency;
        }

        return $minutesSinceLastSent >= $requiredFrequency;
    }

    /**
     * Update notification tracking after sending an email.
     * 
     * @return void
     */
    protected function updateNotificationTracking(): void
    {
        $cacheKey = 'notification_tracking';
        $tracking = Cache::get($cacheKey, [
            'count' => 0,
            'last_sent' => null,
            'first_sent' => null,
        ]);

        $now = now();
        
        $tracking['count']++;
        $tracking['last_sent'] = $now->toDateTimeString();
        
        if ($tracking['first_sent'] === null) {
            $tracking['first_sent'] = $now->toDateTimeString();
        }

        // Store indefinitely until conditions clear
        Cache::forever($cacheKey, $tracking);
        
        Log::info('Notification tracking updated', [
            'count' => $tracking['count'],
            'last_sent' => $tracking['last_sent'],
            'next_frequency' => $tracking['count'] >= $this->initialNotificationCount 
                ? "{$this->reducedFrequency} minutes" 
                : "{$this->initialFrequency} minutes",
        ]);
    }

    /**
     * Reset notification tracking when conditions are no longer critical.
     * 
     * @return void
     */
    protected function resetNotificationTracking(): void
    {
        $cacheKey = 'notification_tracking';
        
        if (Cache::has($cacheKey)) {
            Cache::forget($cacheKey);
            Log::info('Notification tracking reset - no critical conditions');
        }
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

                // Get notification tracking info for logging
                $tracking = Cache::get('notification_tracking', ['count' => 0]);
                $notificationNumber = $tracking['count'] + 1;

                // Send ONE email with all recipients in a single transaction
                Log::info('Sending consolidated alert to all recipients', [
                    'recipients' => $recipients,
                    'notification_number' => $notificationNumber,
                ]);
                
                Mail::send('emails.critical-alert', [
                    'criticalBuildings' => $criticalBuildings,
                    'offlineDevices' => $offlineDevices,
                    'alertSettings' => $alertSettings,
                ], function ($message) use ($recipients, $subject) {
                    $message->to($recipients)->subject($subject);
                });
                
                // Update tracking after successful send
                $this->updateNotificationTracking();
                
                Log::info("Consolidated critical alert sent", [
                    'critical_buildings_count' => $criticalBuildings->count(),
                    'offline_devices_count' => $offlineDevices->count(),
                    'recipients_count' => count($recipients),
                    'notification_number' => $notificationNumber,
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
     * Also resets the progressive notification tracking.
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

        // Reset progressive notification tracking
        $this->resetNotificationTracking();

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
