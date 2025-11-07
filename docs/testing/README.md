# Database Testing Suite

This directory contains all database-related tests for the UPRM VoIP Monitoring System.

## 📁 File Structure

```
tests/
├── Unit/
│   ├── Models/
│   │   ├── BuildingTest.php      - Building model tests
│   │   ├── DeviceTest.php        - Device model tests
│   │   ├── ExtensionTest.php     - Extension model tests
│   │   └── NetworkTest.php       - Network model tests
│   └── Database/
│       ├── MigrationTest.php     - Database schema tests
│       └── SeederTest.php        - Seeder tests
└── Feature/
    └── Services/
        └── ETLServiceTest.php    - ETL process tests (coming soon)
```

## 🚀 Quick Start

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
# Run unit tests only
php artisan test --testsuite=Unit

# Run feature tests only
php artisan test --testsuite=Feature
```

### Run Specific Test File
```bash
php artisan test tests/Unit/Models/BuildingTest.php
php artisan test tests/Unit/Models/DeviceTest.php
```

### Run with Coverage
```bash
./vendor/bin/pest --coverage
./vendor/bin/pest --coverage-html=docs/testing/coverage
```

## 📊 Automated Test Reporting

### Generate Test Case List (Before Running Tests)
```bash
# Generate CSV spreadsheet
php artisan test:report --format=csv

# Generate Markdown documentation
php artisan test:report --format=markdown

# Generate both formats
php artisan test:report --format=both
```

**Output Files:**
- `docs/testing/test-cases.csv` - Excel-compatible spreadsheet
- `docs/testing/TEST_CASES.md` - Markdown documentation

### Run Tests and Generate Results Report
```bash
# Run tests and generate CSV results
php artisan test:run-parse --format=csv

# Run tests and generate Markdown results
php artisan test:run-parse --format=markdown

# Generate both formats
php artisan test:run-parse --format=both
```

**Output Files:**
- `docs/testing/test-results.csv` - Test execution results spreadsheet
- `docs/testing/TEST_RESULTS.md` - Test execution results documentation

## 📈 Test Coverage

Current test coverage by module:

| Module | Tests | Status |
|--------|-------|--------|
| Buildings Model | 7 | ✅ Complete |
| Devices Model | 7 | ✅ Complete |
| Extensions Model | 7 | ✅ Complete |
| Networks Model | 8 | ✅ Complete |
| Database Migrations | 12 | ✅ Complete |
| Database Seeders | 6 | ✅ Complete |
| Feature Tests | 1 | ✅ Complete |
| ETL Service | 0 | ⏳ Planned |

**Total Tests:** 48  
**Passing:** 48 (100%)  
**Total Assertions:** 171

## 📝 Writing New Tests

### Using Pest Syntax

```php
<?php

use App\Models\YourModel;

test('your test description', function () {
    // Arrange
    $model = YourModel::create([...]);
    
    // Act
    $result = $model->someMethod();
    
    // Assert
    expect($result)->toBe(expected_value);
});
```

### Test Best Practices

1. **Use descriptive test names** - Test names should clearly describe what is being tested
2. **Follow AAA pattern** - Arrange, Act, Assert
3. **One assertion per test** - Keep tests focused
4. **Use database refresh** - Already configured in `Pest.php`
5. **Clean up after tests** - Use transactions or database refresh

## 🔍 Debugging Tests

### Run Single Test
```bash
php artisan test --filter="building can be created with valid data"
```

### Verbose Output
```bash
./vendor/bin/pest --verbose
```

### Show All Output (including dumps)
```bash
./vendor/bin/pest --display-errors
```

## 📤 Submitting Test Reports to Professor

### Step 1: Generate Test Case List
```bash
php artisan test:report --format=both
```
This creates a CSV file you can open in Excel.

### Step 2: Run Tests and Generate Results
```bash
php artisan test:run-parse --format=both
```
This runs all tests and creates result reports.

### Step 3: Open in Excel
1. Navigate to `docs/testing/`
2. Open `test-cases.csv` in Excel
3. Open `test-results.csv` in Excel
4. Format as needed and save

### Alternative: Use Markdown
- Open `docs/testing/TEST_CASES.md` for test case list
- Open `docs/testing/TEST_RESULTS.md` for test results
- Open `docs/testing/TEST_PLAN.md` for complete test plan

## 🛠️ Troubleshooting

### Issue: Tests fail with database errors
**Solution:** Make sure you're using in-memory SQLite for testing
```bash
# Check phpunit.xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

### Issue: Commands not found
**Solution:** Register commands in `app/Console/Kernel.php` or they will be auto-discovered in Laravel 11+

### Issue: Tests are slow
**Solution:** Run tests in parallel
```bash
php artisan test --parallel
```

## 📚 Additional Resources

- [Pest Documentation](https://pestphp.com/)
- [Laravel Testing Guide](https://laravel.com/docs/testing)
- [Test Plan](./TEST_PLAN.md)

## ✅ Pre-Commit Checklist

Before committing code:
- [ ] All tests pass
- [ ] New features have tests
- [ ] Test coverage maintained/improved
- [ ] Test documentation updated
- [ ] No commented-out tests

## 🎯 Testing Goals

- **Coverage:** Maintain ≥ 80% code coverage
- **Quality:** Zero failing tests in main branch
- **Speed:** Tests complete in < 30 seconds
- **Reliability:** Tests are deterministic and repeatable

---

**Last Updated:** October 30, 2025  
**Test Status:** ✅ All 48 tests passing (100% success rate)  
**Maintained By:** Database Team
