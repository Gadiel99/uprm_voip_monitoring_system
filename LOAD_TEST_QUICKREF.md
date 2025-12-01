# Load Testing Quick Reference

## 🚀 Quick Commands

### Setup (2-5 minutes)
```bash
# Interactive menu
./scripts/load-test.sh

# Direct seeding
php artisan db:seed --class=LoadTest3000PhonesSeeder
```

### Testing
```bash
# Run all performance tests
php artisan test --filter=LoadTest3000Phones

# Run specific test
php artisan test --filter=test_dashboard_load_performance

# Manual testing with curl
time curl http://localhost/dashboard

# Apache Bench (50 concurrent users)
ab -n 1000 -c 50 http://localhost/dashboard
```

### Cleanup
```bash
# Remove all load test data
php artisan db:seed --class=CleanupLoadTestDataSeeder
```

## 📊 What Gets Created

- **3,000 devices** with realistic MAC addresses and IPs
- **3,000 extensions** (numbered 10000+)
- Distributed across all buildings/networks (except Monzon & Prueba)
- 95% online, 5% offline status
- 10% marked as critical

## ✅ Success Criteria

| Metric | Target |
|--------|--------|
| Dashboard Load | < 5s |
| Database Queries | < 1s |
| ETL Process | < 5 min |
| Memory Usage | < 512MB |

## 🔒 Safety Features

- **Preserves** existing test data in:
  - Monzon (building_id: 99)
  - Prueba (building_id: 211)
  - All devices marked as **critical** (from admin panel)
- All LoadTest data is clearly labeled
- Safe cleanup removes only LoadTest data
- Networks containing critical devices are automatically skipped

## 📁 Files Created

```
database/seeders/
  ├── LoadTest3000PhonesSeeder.php      # Creates 3,000 phones
  └── CleanupLoadTestDataSeeder.php     # Removes load test data

tests/Feature/
  └── LoadTest3000PhonesTest.php        # Performance tests

docs/
  └── LOAD_TESTING_3000_PHONES.md       # Full documentation

scripts/
  └── load-test.sh                       # Interactive setup script
```

## 🐛 Troubleshooting

### Out of Memory
```bash
php -d memory_limit=2048M artisan db:seed --class=LoadTest3000PhonesSeeder
```

### Check Current Data
```bash
php artisan tinker
\App\Models\Devices::count();
\App\Models\Extensions::where('user_first_name', 'LoadTest')->count();
```

### Database Connection Issues
```sql
-- In MySQL
SET GLOBAL wait_timeout = 600;
SET GLOBAL max_allowed_packet = 67108864;
```

## 📖 Full Documentation

See **[docs/LOAD_TESTING_3000_PHONES.md](../docs/LOAD_TESTING_3000_PHONES.md)** for:
- Complete setup instructions
- Performance benchmarks
- Optimization strategies
- Reporting guidelines
