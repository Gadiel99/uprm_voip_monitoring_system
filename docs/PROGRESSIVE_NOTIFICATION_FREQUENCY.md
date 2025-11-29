# Progressive Notification Frequency System

## Overview

The notification system now uses a progressive frequency approach to balance timely alerts with notification fatigue. When critical conditions are detected (critical buildings or offline critical devices), the system automatically adjusts notification frequency based on how long the condition persists.

## Frequency Schedule

### Initial Phase (First 15 minutes)
- **Frequency**: Every 5 minutes
- **Count**: 3 emails
- **Duration**: 0-15 minutes
- **Purpose**: Rapid notification for immediate response

### Sustained Phase (After 15 minutes)
- **Frequency**: Every 1 hour
- **Purpose**: Continued monitoring without overwhelming recipients

## How It Works

### 1. Critical Condition Detection
The system checks every 5 minutes (via cron) for:
- Buildings with offline percentage above the red threshold
- Critical devices that are offline

### 2. Notification Tracking
Using Laravel's cache system, the service tracks:
- `count`: Number of notifications sent for current critical period
- `last_sent`: Timestamp of last notification sent
- `first_sent`: Timestamp of first notification in current critical period

### 3. Frequency Logic
```
if (count < 3) {
    Send notification if 5 minutes have passed since last notification
} else {
    Send notification if 60 minutes have passed since last notification
}
```

### 4. Automatic Reset
When all critical conditions are resolved (no critical buildings and no offline critical devices), the tracking resets automatically. The next critical event will start fresh from the 5-minute frequency.

## Implementation Details

### NotificationService Methods

#### `shouldSendNotification(): bool`
Determines if enough time has passed based on current notification count:
- Returns `true` if this is the first notification
- Returns `true` if sufficient time has passed based on frequency tier
- Returns `false` if still within cooldown period

#### `updateNotificationTracking(): void`
Updates cache after sending a notification:
- Increments notification count
- Records current timestamp as `last_sent`
- Sets `first_sent` on initial notification
- Logs tracking information for monitoring

#### `resetNotificationTracking(): void`
Clears all tracking when conditions return to normal:
- Removes cache entry
- Logs reset event
- Next critical event starts fresh

### Cache Key
`notification_tracking` - Stores the tracking object with count and timestamps

## Example Timeline

### Scenario: Building becomes critical at 10:00 AM

| Time     | Event                      | Action                          | Count |
|----------|----------------------------|---------------------------------|-------|
| 10:00 AM | Critical detected          | **Email #1** sent immediately   | 1     |
| 10:05 AM | Still critical             | **Email #2** sent (5 min)       | 2     |
| 10:10 AM | Still critical             | **Email #3** sent (5 min)       | 3     |
| 10:15 AM | Still critical             | Skipped (need 60 min now)       | 3     |
| 10:20 AM | Still critical             | Skipped                         | 3     |
| 11:10 AM | Still critical             | **Email #4** sent (60 min)      | 4     |
| 12:10 PM | Still critical             | **Email #5** sent (60 min)      | 5     |
| 12:30 PM | **Condition resolved**     | Tracking reset                  | -     |
| 2:00 PM  | Critical again (new issue) | **Email #1** sent (fresh start) | 1     |

## Configuration

### Environment Variables
All configuration is handled in the `NotificationService` class:

```php
protected int $initialFrequency = 5;          // 5 minutes
protected int $reducedFrequency = 60;         // 60 minutes (1 hour)
protected int $initialNotificationCount = 3;  // First 3 emails
```

To customize these values, modify the class properties in:
`app/Services/NotificationService.php`

### Cron Schedule
The notification check runs every 5 minutes via:
```bash
/var/www/voip_mon/scripts/auto-import-voip-cron.sh
```

This script:
1. Downloads latest VoIP data
2. Runs ETL process
3. Executes `php artisan notifications:check`

## Monitoring

### Log Messages
The system logs detailed information about notification decisions:

```
[INFO] Notification tracking updated
    - count: 3
    - last_sent: 2025-11-29 10:10:00
    - next_frequency: 60 minutes

[INFO] Consolidated critical alert sent
    - notification_number: 3
    - critical_buildings_count: 2
    - offline_devices_count: 5
```

### Cache Inspection
To view current tracking status:
```bash
php artisan tinker
>>> Cache::get('notification_tracking')
```

### Manual Reset
To manually reset notification tracking:
```bash
php artisan notifications:reset
```

This also resets the progressive frequency tracking.

## Benefits

1. **Immediate Awareness**: First 3 notifications arrive quickly (within 15 minutes)
2. **Reduced Fatigue**: After initial burst, hourly notifications prevent overwhelming recipients
3. **Automatic Recovery**: System resets when issues are resolved
4. **Persistent Monitoring**: Continues hourly notifications for ongoing issues
5. **Simple Configuration**: Easy to adjust frequencies in code

## Testing

### Manual Test
1. Set alert thresholds to trigger easily
2. Force devices offline or adjust data to create critical building
3. Monitor log file: `storage/logs/laravel.log`
4. Observe notification timing

### Verify Tracking
```bash
# Check current tracking state
php artisan tinker
>>> Cache::get('notification_tracking')

# Manual notification check
php artisan notifications:check
```

## Troubleshooting

### Notifications Not Sending
1. Check alert settings are active: `is_active = true`
2. Verify email notifications enabled: `email_notifications_enabled = true`
3. Check cron is running every 5 minutes
4. Review logs: `tail -f storage/logs/laravel.log`

### Too Frequent or Infrequent
1. Check cache tracking: `Cache::get('notification_tracking')`
2. Verify cron timing in crontab
3. Adjust frequency settings in NotificationService if needed

### Tracking Not Resetting
1. Ensure all critical conditions are actually resolved
2. Check that `checkAndNotify()` is running successfully
3. Manually reset if needed: `php artisan notifications:reset`

## Migration Notes

This feature does **not** require database migrations. All tracking is stored in Laravel's cache system, which persists data efficiently without database overhead.

Previous behavior: Notifications sent every 5 minutes continuously while conditions were critical.

New behavior: Progressive frequency (5min → hourly) for sustained critical conditions.
