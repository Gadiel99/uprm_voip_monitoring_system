# Reports Functionality Implementation

## Overview
The Reports page provides comprehensive device search, filtering, and reporting capabilities for the VoIP monitoring system. The implementation uses server-side data from the database rather than hardcoded JavaScript arrays.

## Architecture

### Controller: `ReportsController`
**Location:** `app/Http/Controllers/ReportsController.php`

#### Methods:

1. **`index()`** - Initial page load
   - Returns buildings list for dropdown
   - Calculates system statistics
   - Returns empty device list (populated after search)

2. **`search(Request $request)`** - Handle search with filters
   - Validates input (user, mac, ip, status, building_id)
   - Builds complex query with joins across:
     - `devices` table
     - `device_extensions` pivot
     - `extensions` (for user names)
     - `networks`
     - `building_networks` pivot
     - `buildings`
   - Applies filters dynamically
   - Returns grouped devices with multiple extensions
   - Maintains filter values in form

3. **`getSystemStats()`** - Private helper
   - Total devices count
   - Active devices (online status)
   - Inactive devices (offline status)
   - Total buildings count

4. **`groupExtensionsByDevice($devices)`** - Private helper
   - Groups multiple extensions per device
   - Handles devices without extensions
   - Returns clean collection with extension arrays

## Routes
**File:** `routes/web.php`

```php
// Reports: search and filtering
Route::get('/reports', [ReportsController::class, 'index'])->name('reports');
Route::get('/reports/search', [ReportsController::class, 'search'])->name('reports.search');
```

Both routes require authentication (`auth` middleware).

## View
**File:** `resources/views/pages/reports.blade.php`

### Structure:

1. **System Overview Cards** (4 cards)
   - Total Devices: `{{ $stats['total_devices'] }}`
   - Active Now: `{{ $stats['active_devices'] }}` (green)
   - Inactive: `{{ $stats['inactive_devices'] }}` (red)
   - Buildings: `{{ $stats['total_buildings'] }}` (blue)

2. **Search Filters Form**
   - User: Text input (searches first/last name)
   - MAC Address: Text input (partial match)
   - IP Address: Text input (partial match)
   - Status: Dropdown (online/offline)
   - Building: Dropdown (populated from `$buildings`)
   - Submit button → `reports.search` route
   - Reset button → clears and returns to `reports` route

3. **Search Results Table**
   - Displays when `$devices` collection has items
   - Shows result count badge
   - Columns:
     - User: Lists all extensions assigned to device (or "N/A")
     - MAC Address: `<code>` formatted
     - IP Address: `<code>` formatted
     - Status: Badge (green=online, red=offline, yellow=critical)
     - Building: Name or "Unassigned"
   - Empty state: Helpful message based on whether filters were applied

### Data Flow:

```
1. User visits /reports
   → ReportsController@index()
   → Returns: $buildings, $stats, $devices = []

2. User submits search form
   → GET /reports/search?user=john&status=online
   → ReportsController@search()
   → Validates input
   → Queries database with filters
   → Returns: $buildings, $stats, $devices, $filters

3. View renders with results
   → Stats cards show current database state
   → Form repopulates with filter values
   → Table displays matching devices
```

## Database Queries

### System Stats Query:
```php
Devices::count()                    // Total
Devices::where('status', 'online')  // Active
Devices::where('status', 'offline') // Inactive
Buildings::count()                   // Buildings
```

### Search Query Logic:
```sql
SELECT DISTINCT
    d.device_id,
    d.mac_address,
    d.ip_address,
    d.status,
    d.is_critical,
    b.name as building_name,
    CONCAT(e.user_first_name, ' ', e.user_last_name) as user_name,
    e.extension_number
FROM devices d
LEFT JOIN device_extensions de ON de.device_id = d.device_id
LEFT JOIN extensions e ON e.extension_id = de.extension_id
LEFT JOIN networks n ON n.network_id = d.network_id
LEFT JOIN building_networks bn ON bn.network_id = n.network_id
LEFT JOIN buildings b ON b.building_id = bn.building_id
WHERE 
    (e.user_first_name LIKE '%?%' OR e.user_last_name LIKE '%?%')
    AND d.mac_address LIKE '%?%'
    AND d.ip_address LIKE '%?%'
    AND d.status = ?
    AND b.building_id = ?
```

## Filter Behavior

- **User Filter**: Searches both first and last names (case-insensitive, partial match)
- **MAC Filter**: Partial match on MAC address field
- **IP Filter**: Partial match on IP address field
- **Status Filter**: Exact match (online/offline)
- **Building Filter**: Exact match by building_id
- **Empty Filter**: All devices returned (no WHERE clause)

## Extensions Handling

Since devices can have multiple extensions (many-to-many through `device_extensions`), the controller:

1. Retrieves all device-extension pairs from query
2. Groups by `device_id` in `groupExtensionsByDevice()`
3. Creates single device object with `extensions` array
4. Each extension has: `name`, `number`

The view loops through `$device->extensions` and displays each user on separate line.

## Features Implemented

✅ Server-side data from database  
✅ Real-time statistics calculation  
✅ Multi-field search with partial matching  
✅ Building dropdown from database  
✅ Filter persistence (form repopulation)  
✅ Empty state handling  
✅ Result count display  
✅ Status badges with colors  
✅ Critical device flag  
✅ Multiple extensions per device  
✅ Unassigned device handling  
✅ Reset functionality  

## Testing Checklist

- [ ] Visit `/reports` - should load with stats and empty results
- [ ] Search by user name - should filter devices
- [ ] Search by MAC address - should filter devices
- [ ] Search by IP address - should filter devices
- [ ] Filter by status (online) - should show only online
- [ ] Filter by building - should show only that building's devices
- [ ] Combine multiple filters - should apply all
- [ ] Click Reset - should clear form and return to initial state
- [ ] Verify stats cards show correct counts
- [ ] Check device with no extension shows "N/A"
- [ ] Check device with multiple extensions shows all users
- [ ] Verify critical badge appears when is_critical = true
- [ ] Test empty search (no results) shows appropriate message

## Related Files

- Controller: `app/Http/Controllers/ReportsController.php`
- Routes: `routes/web.php`
- View: `resources/views/pages/reports.blade.php`
- Models: `app/Models/Devices.php`, `Buildings.php`, `Extensions.php`

## Migration from Hardcoded JavaScript

### Before:
- Static data array in `<script>` tag
- Client-side filtering with JavaScript
- Fixed building list in options
- Stats calculated from static array
- No database integration

### After:
- Dynamic data from database queries
- Server-side filtering with Laravel
- Building dropdown from database
- Stats calculated from actual device records
- Full database integration with relationships

## Future Enhancements

Potential improvements:
- Pagination for large result sets
- Export to CSV/PDF
- Advanced filters (date range, network, critical only)
- Sorting by column
- Device detail modal on row click
- Real-time status updates (WebSocket)
- Saved search presets
- Chart/graph visualizations
