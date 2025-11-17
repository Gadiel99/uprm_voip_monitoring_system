# Database Backup System

## Overview

The VoIP Monitoring System includes an automated MariaDB database backup system that creates weekly compressed backups and manages retention automatically.

## Features

✅ **Automated Weekly Backups** - Runs every Sunday at 3:00 AM  
✅ **Compressed ZIP Format** - All backups are compressed to save space  
✅ **12-Week Retention** - Automatically keeps 3 months of backups  
✅ **One-Click Restore** - Easy restoration from any backup  
✅ **Manual Backup** - Create backups on-demand via admin panel  
✅ **Download Support** - Download any backup as ZIP file  

## Backup Schedule

**Weekly Schedule:** Every Sunday at 3:00 AM

The backup runs automatically via Laravel's task scheduler. No manual intervention required.

## Storage Location

Backups are stored in:
```
/var/backups/monitoring/
```

Backup files are named with timestamps:
```
backup_2025-11-17_061612.zip
```

## Retention Policy

- **Retention Period**: 4 weeks (28 days / 1 month)
- **Automatic Cleanup**: Old backups are deleted automatically after the retention period
- **Space Management**: Keeps approximately 4 backup files at any time

## Using the Admin Panel

### View Backup Status

1. Navigate to **Admin → Backup** tab
2. View backup statistics:
   - Total number of backups
   - Total storage used
   - Retention policy
   - Scheduled frequency

### Create Manual Backup

1. Go to **Admin → Backup** tab
2. Click **"Create Backup & Download (ZIP)"**
3. Wait for backup to complete (may take 10-30 seconds)
4. Backup will automatically download as ZIP file
5. New backup appears in "Available Backups" list

### Download Latest Backup

1. Go to **Admin → Backup** tab
2. In "Latest Backup" section, click **"Download Latest Backup"**
3. ZIP file downloads to your computer

### Restore from Backup

**⚠️ WARNING:** Restore will replace ALL current database data!

1. Go to **Admin → Backup** tab
2. In "Latest Backup" section, click **"Restore from Backup"**
   - OR select a specific backup from the list and click its **"Restore"** button
3. Confirm the restoration in the warning dialog
4. Wait for restore to complete (may take 10-30 seconds)
5. System will show success/error message

## Command Line Usage

### Create a New Backup

```bash
php artisan backup:database
```

**Output:**
```
Creating database backup...

✓ Backup created successfully
  File: backup_2025-11-17_061612.zip
  Size: 12.56 KB
```

### List All Backups

```bash
php artisan backup:database --list
```

**Output:**
```
+------------------------------+----------+---------------------+---------------+
| Filename                     | Size     | Created             | Age           |
+------------------------------+----------+---------------------+---------------+
| backup_2025-11-17_061612.zip | 12.56 KB | 2025-11-17 10:16:12 | 5 minutes ago |
| backup_2025-11-10_030015.zip | 11.98 KB | 2025-11-10 03:00:15 | 7 days ago    |
+------------------------------+----------+---------------------+---------------+
```

### Show Backup Statistics

```bash
php artisan backup:database --stats
```

**Output:**
```
Backup Statistics:

  Total Backups: 2
  Total Size: 24.54 KB
  Retention: 12 weeks
  Oldest: 2025-11-10 03:00:15
  Newest: 2025-11-17 10:16:12
```

## Manual Restore from Command Line

If you need to manually restore a backup from the command line:

```bash
# Extract the backup
cd /var/backups/monitoring
unzip backup_2025-11-17_061612.zip

# Restore to database
mysql -h localhost -u your_username -p your_database < backup_2025-11-17_061612.sql
```

## Monitoring Backups

### Check Scheduled Tasks

Verify backup is scheduled:

```bash
php artisan schedule:list | grep backup
```

Expected output:
```
0 3 * * 0  php artisan backup:database ... Next Due: 5 days from now
```

### Check Logs

Backup operations are logged in `storage/logs/laravel.log`:

```bash
tail -f storage/logs/laravel.log | grep -i backup
```

**Successful backup:**
```
[2025-11-17 10:16:12] production.INFO: Database backup created successfully
{"file":"backup_2025-11-17_061612.zip","size":"12.56 KB"}
```

**Failed backup:**
```
[2025-11-17 10:16:12] production.ERROR: Backup creation failed
{"error":"Database dump failed: ..."}
```

## Storage Requirements

### Estimated Sizes

- **Empty database**: ~10 KB
- **Small database** (< 1000 devices): ~50 KB
- **Medium database** (1000-10000 devices): ~500 KB
- **Large database** (10000+ devices): 1-5 MB

### Total Storage for 12 Weeks

With 12 weekly backups:
- Small system: ~600 KB
- Medium system: ~6 MB
- Large system: 12-60 MB

## Backup Contents

Each backup includes complete dumps of all tables:

- `users` - User accounts and authentication
- `buildings` - Building information
- `networks` - Network configurations
- `devices` - Device records
- `extensions` - Extension assignments
- `building_networks` - Building-network relationships
- `device_extensions` - Device-extension relationships
- `alert_settings` - Alert configuration
- `device_activity_today` - Current day activity
- `device_activity_yesterday` - Previous day activity
- All other system tables

## Disaster Recovery

### Regular Backup Download

**Best Practice:** Download and store backups offsite regularly

1. Weekly: Download latest backup to external storage
2. Monthly: Archive backup to cloud storage
3. Keep at least 3 months of external backups

### System Recovery Procedure

If database is corrupted or lost:

1. Stop the application
2. Reinstall MariaDB if needed
3. Create fresh database
4. Restore from latest backup (via admin panel or CLI)
5. Verify data integrity
6. Restart application

## Troubleshooting

### Backup Creation Fails

**Error:** "Database dump failed"

**Solution:**
```bash
# Check mysqldump is installed
which mysqldump

# Check database credentials
php artisan tinker
>>> config('database.connections.mysql')

# Check disk space
df -h /var/backups/monitoring
```

### Restore Fails

**Error:** "Database restore failed"

**Solution:**
```bash
# Check mysql client is installed
which mysql

# Verify backup file integrity
unzip -t /var/backups/monitoring/backup_*.zip

# Check database permissions
mysql -u root -p -e "SHOW GRANTS FOR 'your_user'@'localhost';"
```

### Backup Not Running Automatically

**Solution:**
```bash
# Verify cron is running scheduler
crontab -l | grep schedule:run

# Should show:
# * * * * * cd /var/www/uprm_voip_monitoring_system && php artisan schedule:run

# Test scheduler manually
php artisan schedule:run

# Check schedule list
php artisan schedule:list
```

## Configuration

### Change Retention Period

Edit `app/Services/BackupService.php`:

```php
private const RETENTION_WEEKS = 12; // Change to desired weeks
```

### Change Backup Schedule

Edit `routes/console.php`:

```php
// Daily at 2 AM
Schedule::command('backup:database')
    ->dailyAt('02:00')
    ->withoutOverlapping();

// Or twice weekly (Sunday and Wednesday)
Schedule::command('backup:database')
    ->twiceWeekly(0, 3) // 0=Sunday, 3=Wednesday
    ->at('03:00')
    ->withoutOverlapping();
```

## Security Considerations

1. **File Permissions**: Backup directory should be writable by web server
   ```bash
   sudo chown -R www-data:www-data /var/backups/monitoring
   sudo chmod -R 775 /var/backups/monitoring
   ```

2. **Database Credentials**: Stored in `.env` file (not in version control)

3. **Backup Files**: Contain sensitive data - do not expose publicly

4. **Access Control**: Only admin users can create/restore backups

5. **Audit Trail**: All backup operations are logged with timestamps

## API Endpoints

Admin panel uses these routes:

- `POST /admin/backup/create` - Create new backup and download
- `GET /admin/backup/download` - Download latest backup
- `POST /admin/backup/restore` - Restore from backup

All routes require admin authentication.

## Related Documentation

- [File Cleanup Policy](./FILE_CLEANUP_POLICY.md) - Import file retention
- [CRON Setup](./CRON_SETUP.md) - Task scheduling
- [Email Notifications](./EMAIL_NOTIFICATIONS.md) - Alert system

---

**Last Updated**: November 17, 2025  
**Version**: 1.0  
**Author**: UPRM VoIP Monitoring System Team
