# Email Notifications System

## Overview

The VoIP Monitoring System includes automated email notifications for critical conditions. When one or more critical buildings or offline critical devices are detected, a **single consolidated email** is sent containing:
- All buildings in critical state (offline device % > upper threshold)
- All critical devices that are currently offline

This consolidated approach provides a complete snapshot of all critical issues in one message.

### Progressive Notification Frequency

The system uses **progressive notification frequency** to balance rapid response with notification fatigue:

- **First 3 emails**: Sent every **5 minutes** (15 minutes total)
  - Ensures immediate awareness of critical conditions
  
- **After 3rd email**: Frequency reduces to **1 hour**
  - Maintains ongoing monitoring without overwhelming recipients
  - Continues hourly as long as conditions remain critical

- **Automatic Reset**: When all critical conditions are resolved, tracking resets and the next critical event starts fresh with 5-minute frequency

📖 **For detailed information**, see [PROGRESSIVE_NOTIFICATION_FREQUENCY.md](PROGRESSIVE_NOTIFICATION_FREQUENCY.md)

## Configuration

### 1. Mail Settings (.env)

```env
MAIL_MAILER=sendmail
MAIL_SENDMAIL_PATH="/usr/sbin/sendmail -bs -i"
MAIL_HOST=localhost
MAIL_PORT=25
MAIL_FROM_ADDRESS="voip-monitoring@uprm.edu"
MAIL_FROM_NAME="UPRM VoIP Monitoring System"
MAIL_ADMIN_ADDRESS="admin@uprm.edu"

# Auto-reset notification states when devices come online or buildings exit critical (true/false)
NOTIFICATIONS_AUTO_RESET=true
```

**Important**: 
- Recipients are pulled from the **users table** (admin panel). Only users with **email notifications enabled** receive alerts.
- Users can toggle their email notification preferences in the Admin Settings panel.
- `MAIL_ADMIN_ADDRESS` is only used as a fallback if no users with notifications enabled exist.
- Set `NOTIFICATIONS_AUTO_RESET=false` to manually control when notification states reset.

### 2. Postfix MTA

Postfix is configured as the Mail Transfer Agent (MTA):
- Listening on localhost only (loopback-only)
- Configured for local delivery
- Service: `sudo systemctl status postfix`

## Features

### Notification Service

**Location**: `app/Services/NotificationService.php`

Key features:
- **State-based Sending**: Emails sent only when devices go offline or buildings enter critical state
- **Auto-reset**: When `NOTIFICATIONS_AUTO_RESET=true` (default), states reset automatically when:
  - Critical devices come back online
  - Buildings exit critical state (drop below upper threshold)
- **Cache-based Tracking**: Uses Laravel cache to track notification states
- **Manual Control**: Set `NOTIFICATIONS_AUTO_RESET=false` to manually control state resets
- **Multi-recipient**: Emails all users from the admin panel (users table)

### Email Template

**Consolidated Critical Alert** (`resources/views/emails/critical-alert.blade.php`)
- Single email containing all critical conditions
- Sections for critical buildings (if any) with full statistics
- Sections for offline critical devices (if any) with device details
- Action buttons for dashboard, buildings, and devices views
- Professional HTML layout with color-coded alerts

## Usage

### Artisan Commands

#### 1. Check and Send Notifications

```bash
# Check all critical conditions and send consolidated email if needed
php artisan notifications:check
```

#### 2. Test Notification

```bash
# Send a test consolidated alert with sample data
php artisan notifications:test
```

### Automatic Scheduling

Notifications are checked automatically:
- **ETL Process**: After each ETL run (every 5 minutes), notifications are checked and auto-reset runs
- **Scheduled Task**: Independent notification check runs via cron

See `routes/console.php` for scheduler configuration.

### How It Works

**State Tracking & Auto-Reset** (when `NOTIFICATIONS_AUTO_RESET=true`):

1. **One or more conditions become critical**:
   - System gathers all NEW critical buildings (not previously notified)
   - System gathers all NEW offline critical devices (not previously notified)
   - If any NEW conditions exist, ONE consolidated email is sent with everything
   - All items in the email are marked as notified (cached)

2. **Auto-reset on recovery**:
   - When a building exits critical state → state cleared automatically
   - When a critical device comes back online → state cleared automatically
   - Next time they become critical/offline again → included in the next alert

3. **Consolidation benefits**:
   - Receive ONE email per check cycle, not multiple separate emails
   - Complete overview of all critical issues in one place
   - Less inbox clutter while maintaining full awareness

**Manual Control** (when `NOTIFICATIONS_AUTO_RESET=false`):
- States never auto-reset
- Use `clearBuildingNotification()` or `clearDeviceNotification()` to manually reset
- Useful for testing or custom workflows

## Critical Devices

### Marking Devices as Critical

Devices can be marked as critical in the database:

```sql
-- Mark a device as critical
UPDATE devices SET is_critical = 1 WHERE device_id = 123;

-- Mark all devices in a specific network as critical
UPDATE devices SET is_critical = 1 WHERE network_id = 5;
```

Or via Laravel:

```php
$device = Devices::find(123);
$device->is_critical = true;
$device->save();
```

### When to Mark a Device as Critical

Mark devices as critical if they are:
- Essential infrastructure (core routers, switches)
- Emergency communication systems
- Key administrative devices
- Critical departmental services

## Alert Thresholds

Alert thresholds are configured in the `alert_settings` table:
- **Lower Threshold**: Below this = Green (healthy)
- **Upper Threshold**: Above this = Red (critical)
- **Between**: Yellow (warning)

Critical building notifications are only sent when the offline percentage exceeds the **upper threshold**.

## Troubleshooting

### Check Email Delivery

```bash
# Check Postfix status
sudo systemctl status postfix

# View Postfix logs
sudo journalctl -u postfix -n 50

# Check mail queue
sudo postqueue -p

# Flush mail queue (force delivery)
sudo postqueue -f
```

### Test Sendmail Directly

```bash
# Send a test email
echo "Subject: Test Email
This is a test message." | sendmail -v your@email.com
```

### Common Issues

1. **No emails received**
   - Verify `MAIL_ADMIN_ADDRESS` in `.env`
   - Check spam/junk folders
   - Verify Postfix is running
   - Check firewall settings if sending to external addresses

2. **Permission errors**
   - Ensure web server user (www-data) can access sendmail
   - Check Laravel log files: `storage/logs/laravel.log`

3. **Duplicate notifications**
   - This is prevented by the 30-minute cooldown
   - Clear cache if needed: `php artisan cache:clear`

### Logs

Check application logs for notification details:

```bash
tail -f storage/logs/laravel.log | grep -i notification
```

## Integration with ETL

The ETL process (`app/Console/Commands/RunETL.php`) automatically checks for critical conditions after each run. This ensures notifications are sent shortly after devices go offline or buildings enter critical state.

## API for Manual Clearing

If you need to manually clear notification cooldowns:

```php
use App\Services\NotificationService;

$notificationService = app(NotificationService::class);

// Clear building notification cooldown
$notificationService->clearBuildingNotification($buildingId);

// Clear device notification cooldown
$notificationService->clearDeviceNotification($deviceId);
```

## Security Considerations

1. **Email Authentication**: Consider adding SPF/DKIM records for your domain
2. **Rate Limiting**: The 30-minute cooldown prevents email flooding
3. **Sensitive Data**: Email templates do not include passwords or sensitive credentials
4. **Access Control**: Only administrators should receive critical notifications

## System-Wide Notification Settings

Administrators can control notification settings for the entire system through the Admin panel:

1. Navigate to **Admin** → **Settings** tab
2. Find the **Notification Settings (System-wide)** section
3. Toggle preferences:
   - **Email Notifications**: Enable/disable email alerts for critical conditions (applies to all users)
   - **Push Notifications**: Browser notifications (coming soon)

**Important:**
- These settings are **system-wide** and shared across all administrators
- When an admin toggles email notifications OFF, no users will receive email alerts
- When toggled back ON, all users in the system will receive notifications again
- Changes are saved automatically and apply immediately
- System logs record all preference updates with the admin's name

## Future Enhancements

Potential improvements:
- Browser push notifications (UI already in place)
- SMS/Slack integration
- Notification history in database
- Custom notification templates per building
- Notification priority levels
- Per-building or per-device notification preferences
