# Critical Device Protection - Load Testing

## 🔒 Protection Strategy

The load testing system uses **multiple layers of protection** to ensure critical devices are never affected:

### Layer 1: Building Exclusion
- **Monzon** (building_id: 99) - Completely excluded
- **Prueba** (building_id: 211) - Completely excluded
- All devices in these buildings are preserved

### Layer 2: Critical Device Detection
- Automatically detects all devices with `is_critical = 1`
- Identifies their networks
- **Excludes entire networks** that contain critical devices
- Works regardless of which building the device is in

### Layer 3: Cleanup Double-Check
- Before deleting any device, checks `is_critical` status
- Removes critical device IDs from deletion list
- Provides explicit confirmation of protected devices

## 📊 Current Protected Devices

As of now, **4 critical devices** are protected:

| Device ID | MAC Address | IP Address | Network | Building | Protected By |
|-----------|-------------|------------|---------|----------|--------------|
| 5 | 48:25:67:4C:55:A1 | 10.100.100.11 | 10.100.100.0 | Monzon | Layer 1 + 2 + 3 |
| 6 | 48:25:67:4D:62:D8 | 10.100.100.15 | 10.100.100.0 | Monzon | Layer 1 + 2 + 3 |
| 11 | 48:25:67:4D:61:5C | 10.100.101.16 | 10.100.101.0 | Prueba | Layer 1 + 2 + 3 |
| 13 | 48:25:67:4C:EE:5C | 10.100.102.12 | 10.100.102.0 | Prueba | Layer 1 + 2 + 3 |

## 🛡️ How It Works

### During Seeding (`LoadTest3000PhonesSeeder`)

```php
// 1. Find all critical devices
$criticalNetworkIds = Devices::where('is_critical', 1)
    ->pluck('network_id')
    ->unique()
    ->toArray();

// 2. Exclude Monzon and Prueba buildings
$buildings = Building::with('networks')
    ->whereNotIn('building_id', [99, 211])
    ->get();

// 3. Skip networks with critical devices
foreach ($buildings as $building) {
    foreach ($building->networks as $network) {
        if (in_array($network->network_id, $criticalNetworkIds)) {
            // Skip this network entirely
            continue;
        }
        // Safe to use this network
    }
}
```

### During Cleanup (`CleanupLoadTestDataSeeder`)

```php
// 1. Get LoadTest device IDs
$loadTestDeviceIds = DB::table('device_extensions')
    ->whereIn('extension_id', $loadTestExtensionIds)
    ->pluck('device_id');

// 2. Remove critical devices from deletion list (double-check)
$criticalDeviceIds = Devices::where('is_critical', 1)->pluck('device_id');
$loadTestDeviceIds = $loadTestDeviceIds->diff($criticalDeviceIds);

// 3. Now safe to delete
Devices::whereIn('device_id', $loadTestDeviceIds)->delete();
```

## ✅ Testing Protection

### Verify Critical Devices
```bash
php artisan tinker
```

```php
// Check all critical devices
\App\Models\Devices::where('is_critical', 1)
    ->with('network.buildings')
    ->get()
    ->map(function($d) {
        return [
            'id' => $d->device_id,
            'ip' => $d->ip_address,
            'building' => $d->network->buildings->first()->name
        ];
    });
```

### Verify Networks Are Skipped
```bash
php artisan db:seed --class=LoadTest3000PhonesSeeder
```

Look for output:
```
🔒 Found 4 critical devices in 3 networks
⚠ Skipping networks containing critical devices...
```

### Verify Cleanup Protection
```bash
php artisan db:seed --class=CleanupLoadTestDataSeeder
```

Look for output:
```
🔒 Protected 4 critical devices from deletion
```

## 🎯 Future-Proof Protection

This protection works even if:
- ✅ New devices are marked critical in **any building**
- ✅ Critical devices are moved to different networks
- ✅ More buildings are added to the exclusion list
- ✅ Networks are reorganized

### Example: Marking a Device Critical

If an admin marks a device in "Biblioteca" as critical:

1. **Load Test Seeder**: Will detect it, skip that network, and report:
   ```
   ⚠ Skipping Biblioteca - 10.100.62.0 (contains critical devices)
   ```

2. **Cleanup Seeder**: Will exclude it from deletion:
   ```
   🔒 Protected 5 critical devices from deletion
   ```

## 📝 Admin Panel Integration

Critical devices can be marked from the admin panel at `/admin/devices`:

1. Select device
2. Click "Mark as Critical"
3. Device is now protected during load testing
4. Its entire network is excluded from load test seeding

## 🔍 Audit Trail

To see what would be protected before running tests:

```bash
php artisan tinker
```

```php
// Check critical devices
$critical = \App\Models\Devices::where('is_critical', 1)->count();
echo "Critical devices: {$critical}\n";

// Check excluded networks
$criticalNets = \App\Models\Devices::where('is_critical', 1)
    ->with('network')
    ->get()
    ->pluck('network.subnet')
    ->unique();
echo "Protected networks: " . $criticalNets->implode(', ') . "\n";

// Check excluded buildings
$buildings = ['Monzon', 'Prueba'];
echo "Protected buildings: " . implode(', ', $buildings) . "\n";
```

## 🚨 Important Notes

1. **Never delete critical devices manually** - Use the cleanup seeder
2. **Critical flag takes precedence** - Even LoadTest devices won't be deleted if marked critical
3. **Network-wide protection** - One critical device protects entire network
4. **Building-wide protection** - Monzon and Prueba are always safe
5. **Triple redundancy** - Three layers ensure nothing is accidentally deleted

## 📖 Related Documentation

- [Load Testing Guide](LOAD_TESTING_3000_PHONES.md)
- [Quick Reference](../LOAD_TEST_QUICKREF.md)
- [Admin Panel Usage](ADMIN_PANEL.md)
