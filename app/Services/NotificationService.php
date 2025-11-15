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
     * Return a unique list of recipient email addresses.
     * Pulls all users listed in the admin panel (users table).
     * Falls back to MAIL_ADMIN_ADDRESS if no users are found.
     *
     * @return array<string>
     */
    public function getRecipients(): array
    {
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
        
        if (!$alertSettings->is_active) {
            return;
        }

        // Gather all critical buildings
        $buildingsData = $this->getAllBuildingsWithStats();
        $criticalBuildings = collect();

        foreach ($buildingsData as $building) {
            $level = $alertSettings->getAlertLevel((float) $building->offline_percentage);
            $stateKey = "state_building_{$building->building_id}";
            
            // Track new critical buildings
            if ($level === 'red' && !Cache::has($stateKey)) {
                $criticalBuildings->push($building);
            }
            
            // Auto-reset: clear state when building exits critical state
            if ($this->autoReset && $level !== 'red' && Cache::has($stateKey)) {
                Cache::forget($stateKey);
                Log::info("Building state reset (auto)", [
                    'building_id' => $building->building_id,
                    'building_name' => $building->name,
                    'level' => $level,
                ]);
            }
        }

        // Gather all offline critical devices
        $offlineDevices = Devices::where('is_critical', true)
            ->where('status', 'offline')
            ->with(['network.buildings', 'extensions'])
            ->get();

        $newOfflineDevices = collect();
        foreach ($offlineDevices as $device) {
            $stateKey = "state_device_{$device->device_id}";
            if (!Cache::has($stateKey)) {
                $newOfflineDevices->push($device);
            }
        }

        // Auto-reset online critical devices
        if ($this->autoReset) {
            $onlineCritical = Devices::where('is_critical', true)
                ->where('status', 'online')
                ->get();
            
            foreach ($onlineCritical as $device) {
                $stateKey = "state_device_{$device->device_id}";
                if (Cache::has($stateKey)) {
                    Cache::forget($stateKey);
                    Log::info("Device state reset (auto)", [
                        'device_id' => $device->device_id,
                        'ip_address' => $device->ip_address,
                    ]);
                }
            }
        }

        // Send consolidated notification if there are any critical conditions
        if ($criticalBuildings->isNotEmpty() || $newOfflineDevices->isNotEmpty()) {
            $this->sendConsolidatedNotification($criticalBuildings, $newOfflineDevices, $alertSettings);
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

            Mail::send('emails.critical-alert', [
                'criticalBuildings' => $criticalBuildings,
                'offlineDevices' => $offlineDevices,
                'alertSettings' => $alertSettings,
            ], function ($message) use ($recipients, $criticalBuildings, $offlineDevices) {
                // Send to everyone listed in the admin panel (users table)
                if (count($recipients) > 1) {
                    $message->to($recipients[0])->bcc(array_slice($recipients, 1));
                } elseif (count($recipients) === 1) {
                    $message->to($recipients[0]);
                }
                
                $subject = "CRITICAL ALERT: ";
                $parts = [];
                if ($criticalBuildings->isNotEmpty()) {
                    $parts[] = $criticalBuildings->count() . " Building(s) Critical";
                }
                if ($offlineDevices->isNotEmpty()) {
                    $parts[] = $offlineDevices->count() . " Critical Device(s) Offline";
                }
                $message->subject($subject . implode(', ', $parts));
            });

            // Mark all buildings as notified
            foreach ($criticalBuildings as $building) {
                Cache::put("state_building_{$building->building_id}", 'red', now()->addDays(30));
            }

            // Mark all devices as notified
            foreach ($offlineDevices as $device) {
                Cache::put("state_device_{$device->device_id}", 'offline', now()->addDays(30));
            }

            Log::info("Consolidated critical alert sent", [
                'critical_buildings_count' => $criticalBuildings->count(),
                'offline_devices_count' => $offlineDevices->count(),
                'recipients_count' => count($recipients),
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send consolidated critical alert", [
                'error' => $e->getMessage(),
            ]);
        }
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
