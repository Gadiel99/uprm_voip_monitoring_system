# Device Activity Tracking System - Daily Graphs

## Overview
The system now tracks device activity with **288 samples per day** (5-minute intervals) and maintains a **2-day rolling queue** of data.

## Key Features
- ✅ **288 samples per day** - One sample every 5 minutes (00:00, 00:05, 00:10, ... 23:55)
- ✅ **2-day data retention** - Today (day 1) and yesterday (day 2)
- ✅ **Automatic rotation** - At midnight, day 1 becomes day 2, old day 2 is deleted
- ✅ **Real-time updates** - Activity recorded every time ETL runs (every 5 minutes)
- ✅ **Interactive graphs** - Toggle between today and yesterday views
- ✅ **API endpoints** - Fetch activity data via REST API

## Database Schema

### `device_activity` Table
```sql
- activity_id (PK)
- device_id (FK to devices)
- activity_date (DATE)
- day_number (1=today, 2=yesterday)
- samples (JSON array of 288 integers: 0 or 1)
- created_at
- updated_at

Indexes:
- device_id
- activity_date
- (device_id, day_number) UNIQUE
```

## How It Works

### 1. Data Collection (Every 5 Minutes)
When the ETL cron runs (every 5 minutes):
1. ETL processes device data and updates status
2. `DeviceActivityService::recordActivity()` is called
3. For each device, the current status (online/offline) is recorded
4. The appropriate sample index (0-287) is calculated based on current time
5. The sample is updated in the day_number=1 record

### 2. Midnight Rotation
At 12:01 AM daily:
1. All `day_number=2` records are deleted (old yesterday data)
2. All `day_number=1` records become `day_number=2` (today becomes yesterday)
3. New `day_number=1` records are created for all devices with zeros

### 3. Data Visualization
When user clicks a device:
1. API fetches both day 1 and day 2 data
2. Chart displays today's data by default
3. User can toggle to view yesterday's data
4. Graph shows 288 points as a stepped line chart

## Files Created/Modified

### New Files
```
database/migrations/2025_11_17_030000_create_device_activity_table.php
app/Models/DeviceActivity.php
app/Services/DeviceActivityService.php
app/Http/Controllers/DeviceActivityController.php
app/Console/Commands/RotateActivityData.php
```

### Modified Files
```
app/Console/Commands/RunETL.php - Added activity recording
routes/web.php - Added API routes
routes/console.php - Added midnight rotation schedule
resources/views/pages/devices_in_network.blade.php - Updated graph modal
```

## API Endpoints

### Get Today's Activity
```http
GET /api/device-activity/{deviceId}?day=1
```

### Get Yesterday's Activity
```http
GET /api/device-activity/{deviceId}?day=2
```

### Get Both Days
```http
GET /api/device-activity/{deviceId}/both
```

Response format:
```json
{
  "today": {
    "activity_date": "2025-11-17",
    "day_number": 1,
    "samples": [0, 1, 1, 1, ..., 1]  // 288 values
  },
  "yesterday": {
    "activity_date": "2025-11-16",
    "day_number": 2,
    "samples": [1, 1, 0, 1, ..., 1]  // 288 values
  }
}
```

## Scheduled Tasks

### ETL with Activity Recording (Every 5 Minutes)
```bash
php artisan etl:run --import=/path/to/import
```
- Processes device data
- Records activity sample for current time slot

### Activity Data Rotation (Daily at 12:01 AM)
```bash
php artisan activity:rotate
```
- Deletes old day 2 data
- Moves day 1 to day 2
- Creates new day 1 records

## Cron Configuration

The scheduler is already configured in `routes/console.php`:

```php
// ETL runs every 5 minutes (records activity automatically)
Schedule::command('etl:run --since="5 minutes ago"')->everyFiveMinutes();

// Rotation runs daily at midnight
Schedule::command('activity:rotate')->dailyAt('00:01');
```

Make sure the Laravel scheduler is running:
```bash
* * * * * cd /var/www/uprm_voip_monitoring_system && php artisan schedule:run >> /dev/null 2>&1
```

## Sample Index Calculation

Sample index is calculated based on minutes since midnight:
```
sample_index = floor(minutes_since_midnight / 5)
```

Examples:
- 00:00 → sample 0
- 00:05 → sample 1
- 12:00 → sample 144
- 23:55 → sample 287

## Graph Visualization

The chart shows:
- **X-axis**: Time labels (00:00, 00:05, ..., 23:55)
- **Y-axis**: Status (Active/Inactive)
- **Line**: Stepped line (no interpolation)
- **Points**: Green dots (active), red dots (inactive)
- **Interaction**: Hover to see exact time and status

## Testing

### 1. Check Activity Recording
```bash
# Run ETL manually
php artisan etl:run --import=/path/to/import

# Check if activity was recorded
mysql -u voip_app -p'VoipApp2024!' mariadb -e "SELECT * FROM device_activity LIMIT 5;"
```

### 2. Test Rotation
```bash
# Run rotation manually
php artisan activity:rotate

# Verify day numbers changed
mysql -u voip_app -p'VoipApp2024!' mariadb -e "SELECT device_id, day_number, activity_date FROM device_activity ORDER BY device_id, day_number;"
```

### 3. Test API
```bash
# Get activity for device ID 1
curl http://voipmonitor.uprm.edu/api/device-activity/1/both
```

### 4. View in Browser
1. Go to Devices page
2. Navigate to a building → network
3. Click on any device
4. Modal should show today's activity graph
5. Click "Yesterday" button to see previous day

## Troubleshooting

### No Data Showing
- Check if ETL has run at least once today
- Verify device_activity table has records:
  ```sql
  SELECT COUNT(*) FROM device_activity;
  ```

### Graph Shows All Zeros
- Device may be new (no history yet)
- Wait for next ETL run to record activity

### Yesterday Button Shows No Data
- System may have just started (no yesterday data yet)
- Rotation runs at midnight, so yesterday data available after 12:01 AM

### Activity Not Recording
- Check ETL is running every 5 minutes
- Check Laravel logs: `tail -f storage/logs/laravel.log`
- Verify DeviceActivityService is being called

## Performance

### Storage Estimate
- Each device = 2 records (today + yesterday)
- Each record = ~2KB (288 integers in JSON)
- 1000 devices = 2000 records ≈ 4MB
- Very efficient and scales well

### Query Performance
- Indexed on device_id and day_number
- Queries are very fast (typically <10ms)
- JSON column efficiently stores 288 samples

## Migration from Old System

If you had the old 30-day system:
1. Old data is ignored (new system starts fresh)
2. After first midnight rotation, you'll have 2 days of data
3. System is fully operational after 24 hours

## Future Enhancements

Possible improvements:
- [ ] Add weekly/monthly aggregated views
- [ ] Export activity data to CSV
- [ ] Show downtime percentage
- [ ] Alert on unusual activity patterns
- [ ] Historical data archive (beyond 2 days)

## Summary

The new system provides:
- ✅ High-resolution activity tracking (5-minute intervals)
- ✅ Minimal storage footprint (2 days only)
- ✅ Fast, efficient queries
- ✅ Real-time updates
- ✅ Clean automatic rotation
- ✅ RESTful API access
- ✅ Beautiful interactive graphs

All integrated with your existing ETL cron job!
