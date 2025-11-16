# File Cleanup Policy

## Overview

The VoIP Monitoring System implements an automated file cleanup policy to manage storage for ETL import files. This ensures efficient disk usage while maintaining an audit trail and troubleshooting capability.

## Retention Policy

### Archive Files (`storage/app/imports/archives/*.tar.gz`)
- **Retention Period**: 30 days (default)
- **Purpose**: Original import archives from sipXcom
- **Rationale**: Keep longer for audit trail and potential reprocessing

### Extracted Files (`storage/app/imports/extracted/*`)
- **Retention Period**: 7 days (default)
- **Purpose**: Extracted CSV/JSON files used for ETL processing
- **Rationale**: Shorter retention since data is already in MariaDB

## Automated Cleanup

The cleanup runs **daily at 2:00 AM** via Laravel's task scheduler:

```php
Schedule::command('imports:cleanup')
    ->dailyAt('02:00')
    ->withoutOverlapping();
```

### What Gets Deleted

Files/directories are deleted when:
1. They exceed their retention period (based on file modification time)
2. The scheduled task runs successfully
3. No other cleanup process is running (withoutOverlapping protection)

### What Gets Kept

- All files within the retention period
- The most recent imports (even if within minutes of cutoff)
- Files currently being processed (locked by ETL service)

## Manual Operations

### Check Storage Statistics

View current storage usage without cleaning:

```bash
php artisan imports:cleanup --stats
```

**Output:**
```
Import File Storage Statistics

Archives (storage/app/imports/archives):
  Files: 245
  Total Size: 1.2 GB
  Oldest: 2025-10-17 14:30:00
  Newest: 2025-11-16 10:15:00

Extracted Directories (storage/app/imports/extracted):
  Directories: 52
  Total Size: 850 MB
  Oldest: 2025-11-09 08:00:00
  Newest: 2025-11-16 10:15:00
```

### Run Manual Cleanup

Run cleanup immediately (doesn't wait for scheduled time):

```bash
php artisan imports:cleanup
```

### Custom Retention Periods

Override default retention periods:

```bash
# Keep archives for 60 days, extracted for 14 days
php artisan imports:cleanup --archive-days=60 --extracted-days=14

# Keep everything for just 1 day (aggressive cleanup)
php artisan imports:cleanup --archive-days=1 --extracted-days=1
```

## Configuration

### Changing Default Retention

Edit `app/Services/FileCleanupService.php`:

```php
private const ARCHIVE_RETENTION_DAYS = 30;    // Change this
private const EXTRACTED_RETENTION_DAYS = 7;   // Change this
```

### Changing Schedule Time

Edit `routes/console.php`:

```php
// Run at different time (e.g., 3:30 AM)
Schedule::command('imports:cleanup')
    ->dailyAt('03:30')
    ->withoutOverlapping();

// Or run twice daily
Schedule::command('imports:cleanup')
    ->twiceDaily(2, 14) // 2:00 AM and 2:00 PM
    ->withoutOverlapping();
```

### Disabling Automated Cleanup

Comment out the schedule in `routes/console.php`:

```php
// Schedule::command('imports:cleanup')
//     ->dailyAt('02:00')
//     ->withoutOverlapping();
```

## Monitoring

### Logs

Cleanup activities are logged in `storage/logs/laravel.log`:

```
[2025-11-16 02:00:01] production.INFO: Starting file cleanup
[2025-11-16 02:00:01] production.INFO: Deleted old archive {"file":"sipxcom-export-20251017143000.tar.gz","age_days":30,"size":"5.2 MB"}
[2025-11-16 02:00:02] production.INFO: File cleanup completed {"archives_deleted":8,"archives_size_freed":41943040,...}
[2025-11-16 02:00:02] production.INFO: Import file cleanup completed successfully
```

### Errors

If cleanup fails:
- Check file permissions (`storage/app/imports` should be writable)
- Verify disk space isn't full
- Check logs for specific error messages

## Best Practices

### 1. Regular Monitoring
```bash
# Add to weekly maintenance checklist
php artisan imports:cleanup --stats
```

### 2. Disk Space Alerts
Monitor available disk space. If storage grows unexpectedly:
```bash
# Check what's using space
du -h storage/app/imports/

# Run manual cleanup if needed
php artisan imports:cleanup
```

### 3. Before System Maintenance
```bash
# Verify cleanup is working before major updates
tail -f storage/logs/laravel.log | grep cleanup
```

### 4. Compliance Requirements
If your organization has data retention requirements:
- Adjust retention periods accordingly
- Document policy changes
- Consider backing up archives before deletion

## Troubleshooting

### "Permission denied" errors
```bash
sudo chown -R www-data:www-data storage/app/imports
sudo chmod -R 755 storage/app/imports
```

### Cleanup not running
```bash
# Verify cron is configured
crontab -l | grep schedule:run

# Should show:
# * * * * * cd /var/www/uprm_voip_monitoring_system && php artisan schedule:run >> /dev/null 2>&1

# Test scheduler
php artisan schedule:list
```

### Storage still growing
```bash
# Find largest files
find storage/app/imports -type f -exec du -h {} \; | sort -hr | head -20

# Check for stuck extraction processes
ps aux | grep extract
```

## Alternative Approaches

### Keep Only Latest Files (Not Recommended)

If you want to keep ONLY the most recent import:

```bash
# Manual script (use cautiously)
cd storage/app/imports/archives
ls -t sipxcom-export-*.tar.gz | tail -n +2 | xargs rm -f
```

**Why not recommended:**
- Loss of audit trail
- Can't troubleshoot historical issues
- No rollback capability
- Risk of data loss if current import is corrupt

### External Backup

For long-term archival:
```bash
# Weekly backup to external storage
0 3 * * 0 rsync -av /var/www/uprm_voip_monitoring_system/storage/app/imports/archives/ /backup/voip-imports/
```

## Related Documentation

- [ETL Process](./CRON_SETUP.md) - How ETL imports work
- [Storage Structure](../README.md) - Directory layout
- [Monitoring](./REPORTS_FUNCTIONALITY.md) - System health monitoring

## Support

For issues or questions:
1. Check logs: `storage/logs/laravel.log`
2. Run diagnostics: `php artisan imports:cleanup --stats`
3. Contact system administrator

---

**Last Updated**: November 16, 2025  
**Version**: 1.0  
**Author**: UPRM VoIP Monitoring System Team
