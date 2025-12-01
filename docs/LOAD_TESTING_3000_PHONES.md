# Load Testing 3,000 Phones - Documentation

## Overview
This documentation covers load testing the VoIP Monitoring System with 3,000 phones to validate the system can support the required capacity.

## Requirements
- **Minimum**: 3,000 phone devices distributed across buildings and networks
- **Test Environment**: Separate Ubuntu server (not production)
- **Database**: MySQL/MariaDB with sufficient storage
- **PHP Memory**: At least 512MB (recommended 1GB)

## Files Created

### 1. Seeder: `LoadTest3000PhonesSeeder.php`
**Location**: `database/seeders/LoadTest3000PhonesSeeder.php`

Creates 3,000 test phones with:
- Realistic MAC addresses (Polycom vendor prefix: 00:04:F2)
- IP addresses matching network subnets
- Extensions (10000+)
- 95% online, 5% offline status
- 10% marked as critical
- Device-extension relationships

**Preserves**: 
- Existing test data in Monzon (building_id: 99) and Prueba (building_id: 211)
- All devices marked as **critical** from the admin panel
- Networks containing critical devices are automatically skipped

### 2. Test Suite: `LoadTest3000PhonesTest.php`
**Location**: `tests/Feature/LoadTest3000PhonesTest.php`

Tests include:
- Database has 3,000+ phones
- Dashboard load performance (<5s)
- Phones list load performance (<3s)
- Database query performance (<1s each)
- ETL process performance (<5 minutes)
- Activity rotation performance (<60s)
- Network count updates performance
- Concurrent requests simulation

### 3. Cleanup Seeder: `CleanupLoadTestDataSeeder.php`
**Location**: `database/seeders/CleanupLoadTestDataSeeder.php`

Safely removes all LoadTest data while preserving:
- Real test data in Monzon and Prueba
- All critical devices (double-checked before deletion)

---

## Setup Instructions

### Step 1: Prepare New Test Server

```bash
# SSH into new Ubuntu server
ssh user@test-server-ip

# Clone repository
cd /var/www
git clone <repository-url> voip_mon_test
cd voip_mon_test

# Install dependencies
composer install
npm install

# Copy environment file
cp .env.example .env

# Edit .env with database credentials
nano .env
```

### Step 2: Configure Environment

Edit `.env`:

```env
APP_NAME="VoIP Monitoring (Load Test)"
APP_ENV=testing
APP_DEBUG=true

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=voip_mon_test
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Increase memory limit
PHP_MEMORY_LIMIT=1024M
```

Update `php.ini` or `.env`:
```ini
memory_limit = 1024M
max_execution_time = 300
```

### Step 3: Database Setup

```bash
# Create database
mysql -u root -p
```

```sql
CREATE DATABASE voip_mon_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON voip_mon_test.* TO 'your_username'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

```bash
# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed initial data (buildings, networks, etc.)
php artisan db:seed
```

### Step 4: Load Test Data

```bash
# Seed 3,000 phones (takes 2-5 minutes depending on server)
php artisan db:seed --class=LoadTest3000PhonesSeeder
```

Expected output:
```
🚀 Starting Load Test: Creating 3,000 phones...
🔒 Found 4 critical devices in 3 networks
📊 Found 30 buildings with 75 networks
🔒 Preserving test data: Monzon, Prueba, and 4 critical devices
⚠ Skipping networks containing critical devices...
📱 Creating 40 phones for Biblioteca - Network 10.100.62.0...
  ✓ Progress: 100/3000 (3.3%)
  ...
✅ Load Test Seeding Complete!
============================================================
📊 Total Phones Created: 3000
🏢 Buildings Used: 30
🌐 Networks Used: 75
⏱️  Execution Time: 123.45s
🔒 Preserved: Monzon, Prueba, and 4 critical devices

📈 Database Statistics:
  - Total Devices: 3013
  - Total Extensions: 3014
  - Online: 2862
  - Offline: 151
============================================================
```

---

## Running Performance Tests

### Option 1: Automated Script (Recommended)

```bash
# Use the interactive load test script
./scripts/load-test.sh

# Then select option 2 for testing
# Or option 4 for full suite (setup + test + cleanup)
```

### Option 2: Manual Performance Testing via Tinker

```bash
# Test database queries
php artisan tinker
```

```php
// Count queries
$start = microtime(true);
$count = \App\Models\Devices::count();
echo "Time: " . ((microtime(true) - $start) * 1000) . "ms\n";

// Complex query
$start = microtime(true);
$buildings = \App\Models\Building::with('networks.devices')->get();
echo "Time: " . ((microtime(true) - $start) * 1000) . "ms\n";
```

**Note**: The Pest test suite (`tests/Feature/LoadTest3000PhonesTest.php`) is provided for reference but requires proper Pest configuration to run. Use the automated script or manual testing instead.

### Option 2: Manual Performance Testing

#### Test Dashboard Load Time
```bash
# Using curl with timing
time curl -w "\nTotal Time: %{time_total}s\n" http://localhost/dashboard
```

#### Test Database Query Performance
```bash
php artisan tinker
```

```php
// Count queries
$start = microtime(true);
\App\Models\Devices::count();
echo "Time: " . ((microtime(true) - $start) * 1000) . "ms\n";

// Complex query
$start = microtime(true);
\App\Models\Building::with('networks.devices')->get();
echo "Time: " . ((microtime(true) - $start) * 1000) . "ms\n";
```

#### Test ETL Performance
```bash
# Run ETL and measure time
time php artisan etl:run --since="1 hour ago"
```

### Option 3: Apache Bench Load Testing

```bash
# Install Apache Bench
sudo apt install apache2-utils

# Test with 50 concurrent users, 1000 requests
ab -n 1000 -c 50 http://localhost/dashboard

# Test with POST requests (if you have API endpoints)
ab -n 500 -c 25 -p tests/sample-request.json -T application/json http://localhost/api/devices
```

### Option 4: Artillery (Advanced)

```bash
# Install Artillery
npm install -g artillery

# Create load test config
cat > artillery-test.yml << 'EOF'
config:
  target: "http://localhost"
  phases:
    - duration: 60
      arrivalRate: 10
    - duration: 120
      arrivalRate: 50
  
scenarios:
  - flow:
      - get:
          url: "/dashboard"
      - think: 2
      - get:
          url: "/phones"
      - think: 3
      - get:
          url: "/buildings"
EOF

# Run load test
artillery run artillery-test.yml
```

---

## Performance Benchmarks

### Expected Results (3,000 phones)

| Metric | Target | Acceptable |
|--------|--------|-----------|
| Dashboard Load | < 2s | < 5s |
| Phones List | < 1s | < 3s |
| Database Query | < 500ms | < 1s |
| ETL Process | < 3min | < 5min |
| Activity Rotation | < 30s | < 60s |
| Memory Usage | < 256MB | < 512MB |

### What to Monitor

1. **Response Times**
   - Dashboard page load
   - API endpoints
   - Database queries

2. **Resource Usage**
   - PHP memory consumption
   - Database connections
   - CPU usage

3. **Database Performance**
   - Query execution time
   - Index usage
   - Connection pool

4. **Concurrent Users**
   - 10-50 simultaneous users
   - No timeouts
   - Consistent response times

---

## Optimizations (if needed)

### 1. Database Indexing
```sql
-- Add indexes if queries are slow
CREATE INDEX idx_devices_status ON devices(status);
CREATE INDEX idx_devices_network ON devices(network_id);
CREATE INDEX idx_extensions_number ON extensions(extension_number);
CREATE INDEX idx_device_extensions_device ON device_extensions(device_id);
```

### 2. Query Optimization
```php
// Use pagination
$devices = Devices::paginate(50);

// Eager loading
$networks = Network::with('devices')->get();

// Select specific columns
$devices = Devices::select('device_id', 'mac_address', 'status')->get();
```

### 3. Caching
```php
// Cache expensive queries
$stats = Cache::remember('dashboard_stats', 300, function() {
    return [
        'total' => Devices::count(),
        'online' => Devices::where('status', 'online')->count(),
        'offline' => Devices::where('status', 'offline')->count(),
    ];
});
```

### 4. PHP Configuration
```ini
; php.ini
memory_limit = 1024M
max_execution_time = 300
opcache.enable = 1
opcache.memory_consumption = 256
```

---

## Cleanup After Testing

### Remove Load Test Data

```bash
# Run cleanup seeder
php artisan db:seed --class=CleanupLoadTestDataSeeder
```

This will:
- Delete all LoadTest devices
- Delete all LoadTest extensions
- Remove relationships
- Update network counts
- **Preserve** Monzon and Prueba test data

### Verify Cleanup

```bash
php artisan tinker
```

```php
// Should return 0
\App\Models\Extensions::where('user_first_name', 'LoadTest')->count();

// Should show only original test data
\App\Models\Devices::count();
```

---

## Troubleshooting

### Issue: Out of Memory

**Solution**:
```bash
# Increase PHP memory limit
php -d memory_limit=2048M artisan db:seed --class=LoadTest3000PhonesSeeder
```

### Issue: Slow Seeding

**Reason**: Batch inserts are optimized, but large datasets take time.

**Expected Time**: 2-5 minutes for 3,000 phones

### Issue: Database Connection Timeout

**Solution**: Increase `wait_timeout` in MySQL:
```sql
SET GLOBAL wait_timeout = 600;
SET GLOBAL max_allowed_packet = 67108864; -- 64MB
```

### Issue: Duplicate MAC Addresses

**Solution**: The seeder generates random MACs. Very unlikely, but if it happens:
```bash
# Clean and re-run
php artisan db:seed --class=CleanupLoadTestDataSeeder
php artisan db:seed --class=LoadTest3000PhonesSeeder
```

---

## Reporting Results

### Generate Test Report

```bash
# Run tests and save output
php artisan test --filter=LoadTest3000Phones > load_test_results.txt 2>&1

# View results
cat load_test_results.txt
```

### Key Metrics to Document

1. ✅ **Total Phones**: 3,000+
2. ✅ **Dashboard Load**: < 5 seconds
3. ✅ **Database Queries**: < 1 second
4. ✅ **ETL Process**: < 5 minutes
5. ✅ **Memory Usage**: < 512MB
6. ✅ **Concurrent Users**: 50+ simultaneous

### Example Report

```
Load Test Report - VoIP Monitoring System
==========================================
Date: 2025-11-29
Server: Ubuntu 24.04, 4GB RAM, 2 CPU
Database: MySQL 8.0

Test Configuration:
- Total Phones: 3,013
- Buildings: 30
- Networks: 75
- Load Test Users: 50 concurrent

Performance Results:
✓ Dashboard Load: 1.2s (target: <5s)
✓ Phones List: 0.8s (target: <3s)
✓ Database Queries: 250ms avg (target: <1s)
✓ ETL Process: 2m 15s (target: <5m)
✓ Memory Usage: 384MB (target: <512MB)
✓ Concurrent Users: 50 OK

Status: ✅ PASSED - System supports 3,000 phones
```

---

## Next Steps

After successful load testing:

1. ✅ Document results
2. ✅ Run cleanup seeder
3. ✅ Implement any necessary optimizations
4. ✅ Update system documentation
5. ✅ Present findings to stakeholders

---

## Support

For issues or questions:
- Check Laravel logs: `storage/logs/laravel.log`
- Check database slow query log
- Monitor system resources: `htop`, `mysql status`
