# Duplicate Email Notifications Fix

## Problem
The system was sending duplicate email notifications every 5 minutes.

## Root Causes Identified

### 1. Dual ETL Triggers (Primary Issue)
- **Laravel Scheduler**: Running `schedule:run` every minute via cron, which triggered `etl:run --since="5 minutes ago"` every 5 minutes
- **Auto-Import Script**: Running `/var/www/auto-import-voip-cron.sh` every 5 minutes via separate cron entry
- **Result**: ETL and notifications were being triggered twice, once by each mechanism

### 2. ETL Command Signature Mismatch (Secondary Issue)
- The scheduler was calling `etl:run --since="5 minutes ago"` but the command only supported `--import=`
- This caused ETL failures every 5 minutes (visible in logs as "ETL command failed")
- Even though it failed, it was still attempting to run

### 3. Missing Deduplication Logic
- No cache-based deduplication in `NotificationService::sendConsolidatedNotification()`
- If same critical state persisted and multiple checks ran, multiple emails would be sent

## Solutions Applied

### 1. Removed Duplicate Scheduler (✅ Critical Fix)
**Action**: Removed `schedule:run` from www-data crontab
```bash
# Before: Two cron entries
* * * * * cd /var/www/uprm_voip_monitoring_system && php artisan schedule:run
*/5 * * * * /var/www/auto-import-voip-cron.sh

# After: Only auto-import script
*/5 * * * * /var/www/auto-import-voip-cron.sh
```

**Files Changed**: 
- `/var/cron/www-data` (crontab entry removed)
- `routes/console.php` (commented out ETL schedule)

### 2. Fixed ETL Command Signature (✅ Important)
**Action**: Added `--since` option support to `RunETL` command

**File**: `app/Console/Commands/RunETL.php`

**Changes**:
- Added `--since=` option to command signature
- Implemented logic to find recent imports within time window
- Command now works with both `--import=` (explicit path) and `--since=` (auto-find)

### 3. Removed Cooldown Logic (✅ Updated Requirement)
**Action**: Removed cache-based deduplication cooldown to allow continuous alerts

**File**: `app/Services/NotificationService.php`

**Reason**: User requirement changed - emails should be sent **every 5 minutes** while critical conditions persist, not just on state changes.

**Changes**:
- Removed signature-based cooldown check
- Removed cache persistence of last sent state
- Emails now sent on every check cycle (every 5 minutes) as long as critical conditions exist

### 4. Cleaned Up Auto-Import Script (✅ Minor)
**Action**: Removed duplicate `notifications:check` call from cron script

**File**: `/var/www/auto-import-voip-cron.sh`

**Reason**: ETL command (`etl:run`) already calls `notifications:check` internally, so the external call was redundant

## Current Architecture

### Single ETL Pipeline
```
Every 5 minutes:
  auto-import-voip-cron.sh
    ↓
  1. Download latest archive via SCP
    ↓
  2. Extract archive (data:import)
    ↓
  3. Run ETL (etl:run --import=path)
    ↓
  4. Record device activity
    ↓
  5. Check and send notifications (notifications:check)
```

### Notification Flow
```
notifications:check (via ETL)
  ↓
NotificationService::checkAndNotify()
  ↓
Gather critical buildings + offline critical devices
  ↓
sendConsolidatedNotification()
  ↓
Check cache signature (deduplication)
  ↓
Send email if:
  - Signature changed (new critical state)
  - OR > 30 minutes since last email with same signature
```

## Testing & Verification

### Test Commands
```bash
# Test ETL with --since option (now works)
php artisan etl:run --since="5 minutes ago"

# Test notification check
php artisan notifications:check

# Monitor logs for duplicates
tail -f storage/logs/laravel.log | grep "consolidated\|notification"

# Check cron is running correctly
tail -f storage/logs/auto-import.log
```

### Expected Behavior
- ✅ One email every 5 minutes while critical conditions persist
- ✅ No duplicate emails from dual cron triggers (fixed by removing scheduler)
- ✅ ETL runs successfully every 5 minutes (via cron script only)
- ✅ Continuous alerts keep administrators informed of ongoing issues

## Monitoring

### Log Locations
- **Laravel logs**: `/var/www/uprm_voip_monitoring_system/storage/logs/laravel.log`
- **Auto-import logs**: `/var/www/uprm_voip_monitoring_system/storage/logs/auto-import.log`
- **Cron logs**: `/var/www/uprm_voip_monitoring_system/storage/logs/cron.log`
- **System cron**: `/var/log/syslog` (grep for CRON)

### Key Log Messages
```bash
# Successful notification send
"Consolidated critical alert sent"

# Email being sent
"Sending consolidated alert to all recipients"

# ETL completion
"ETL process completed successfully"
```

## Rollback Instructions (If Needed)

If you need to revert to dual-trigger system:

1. **Re-enable scheduler in crontab**:
   ```bash
   (sudo crontab -u www-data -l; echo "* * * * * cd /var/www/uprm_voip_monitoring_system && php artisan schedule:run >> /dev/null 2>&1") | sudo crontab -u www-data -
   ```

2. **Uncomment ETL schedule in routes/console.php**:
   ```php
   Schedule::command('etl:run --since="5 minutes ago"')
       ->everyFiveMinutes()
       ->withoutOverlapping();
   ```

3. **Clear config cache**:
   ```bash
   php artisan config:clear
   ```

## Date Fixed
November 17, 2025

## Author
System Administrator / GitHub Copilot
