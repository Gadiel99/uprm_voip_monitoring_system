# UPRM VoIP Monitoring System - Database Test Plan

## Project Information
- **Project Name:** UPRM VoIP Monitoring System
- **Module:** Database & Models
- **Testing Framework:** Pest PHP
- **Date Created:** October 28, 2025
- **Last Updated:** October 30, 2025

## 1. Introduction

### 1.1 Purpose
This document describes the comprehensive testing strategy for the UPRM VoIP Monitoring System database components, including models, relationships, migrations, seeders, and the ETL service.

### 1.2 Scope
Testing covers:
- **Database Models:** Buildings, Devices, Extensions, Networks, Users
- **ETL Service:** Data extraction, transformation, and loading functionality
- **Database Migrations:** Table structure and constraints
- **Database Seeders:** Initial data population
- **Model Relationships:** belongsTo, hasMany, belongsToMany relationships

### 1.3 Out of Scope
- API endpoint testing (handled by API team)
- Frontend functionality (handled by frontend team)
- Integration with external systems (future phase)

## 2. Test Strategy

### 2.1 Testing Framework
- **Framework:** Pest PHP (v2.x)
- **Type:** Unit and Feature Tests
- **Database:** SQLite (in-memory for testing)
- **CI/CD:** GitHub Actions (planned)

### 2.2 Test Types

#### Unit Tests
- Model creation, updates, and deletion
- Model relationships and associations
- Business logic methods
- Data validation rules

#### Feature Tests
- Complete workflows
- ETL process end-to-end
- Database transactions

#### Integration Tests
- Multi-model interactions
- Complex relationship queries
- Performance testing

### 2.3 Test Coverage Goals
- **Models:** 90% code coverage
- **Services:** 85% code coverage
- **Overall:** 80% minimum coverage

## 3. Test Environment

### 3.1 Setup
- **OS:** Windows
- **PHP Version:** 8.x
- **Laravel Version:** 12.x
- **Database (Testing):** MariaDB (local)
- **Database (Production):** MariaDB/PostgreSQL/MongoDB

### 3.2 Configuration
```php
// phpunit.xml configuration
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

## 4. Test Categories

### 4.1 Model Tests

#### Buildings Model
- ✅ Create building with valid data
- ✅ Building has many networks relationship
- ✅ Building can retrieve its networks
- ✅ Building name validation
- ✅ Multiple buildings can exist
- ✅ Building can be updated
- ✅ Building can be deleted

#### Devices Model
- ✅ Create device with valid data
- ✅ Device belongs to network
- ✅ Device has many extensions relationship
- ✅ Device status (online/offline)
- ✅ Device IP address uniqueness
- ✅ Device can be updated
- ✅ Device can be deleted

#### Extensions Model
- ✅ Create extension with valid data
- ✅ Extension has many devices relationship
- ✅ Extension number uniqueness
- ✅ Extension can be updated
- ✅ Multiple extensions can exist
- ✅ Extension can be deleted
- ✅ Extension full name retrieval

#### Networks Model
- ✅ Create network with valid data
- ✅ Network has many devices relationship
- ✅ Network can update device counts
- ✅ Network subnet uniqueness
- ✅ Network can be updated
- ✅ Network counts offline devices correctly
- ✅ Network can be deleted
- ✅ Multiple networks can exist

### 4.2 Database Tests

#### Migration Tests
- ✅ Buildings table exists with correct columns
- ✅ Devices table exists with correct columns
- ✅ Extensions table exists with correct columns
- ✅ Networks table exists with correct columns
- ✅ Building_networks pivot table exists
- ✅ Device_extensions pivot table exists

#### Seeder Tests
- ✅ BuildingsNetworksSeeder creates building
- ✅ BuildingsNetworksSeeder creates networks
- ✅ BuildingsNetworksSeeder attaches relationships
- ✅ Seeded data has correct values
- ✅ Seeded networks have zero initial devices

## 5. Test Execution

### 5.1 Running Tests

#### Run All Tests
```bash
php artisan test
# or
./vendor/bin/pest
```

#### Run Specific Test Suite
```bash
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature
```

#### Run Specific Test File
```bash
php artisan test tests/Unit/Models/BuildingTest.php
```


### 5.2 Automated Test Reporting

#### Generate Test Case Report
```bash
php artisan test:report --format=csv
php artisan test:report --format=markdown
php artisan test:report --format=both
```

#### Run Tests and Generate Results
```bash
php artisan test:run-parse --format=csv
php artisan test:run-parse --format=markdown
php artisan test:run-parse --format=both
```

## 6. Success Criteria

### 6.1 Test Metrics
- All unit tests pass: ✅ **48/48 PASSING** (October 30, 2025)
- No critical bugs: ✅ **0 Critical Bugs**
- Performance tests pass: 📅 Planned

### 6.2 Quality Gates
- Zero failing tests before merge
- All new code has corresponding tests
- Code review approval required
- Documentation updated

## 7. Defect Management

### 7.1 Severity Levels
- **Critical:** System crash, data loss, security breach
- **High:** Major feature not working, incorrect data
- **Medium:** Minor feature issue, workaround available
- **Low:** Cosmetic issue, documentation error

### 7.2 Reporting
- Use GitHub Issues for bug tracking
- Include test case ID in bug report
- Attach test output and logs
- Assign priority based on severity

## 8. Test Schedule

### 8.1 Timeline
- **Week 1:** Model unit tests (Complete ✅)
- **Week 2:** Database tests (Complete ✅)
- **Week 3:** Service tests (Planned 📅)
- **Week 4:** Integration tests (Planned 📅)
- **Week 5:** Performance tests (Planned 📅)

### 8.2 Milestones
- ✅ Test framework setup (October 30, 2025)
- ✅ Model tests complete (48 tests passing)
- ✅ Database migration tests complete
- ✅ Database seeder tests complete
- 📅 ETL service tests


## 9. Resources

### 9.1 Documentation
- Pest PHP: https://pestphp.com/
- Laravel Testing: https://laravel.com/docs/testing
- GitHub Repository: https://github.com/Gadiel99/uprm_voip_monitoring_system

## 10. Appendix

### 10.1 Test File Structure
```
tests/
├── Unit/
│   ├── Models/
│   │   ├── BuildingTest.php
│   │   ├── DeviceTest.php
│   │   ├── ExtensionTest.php
│   │   └── NetworkTest.php
│   └── Database/
│       ├── MigrationTest.php
│       └── SeederTest.php
└── Feature/
    └── Services/
        └── ETLServiceTest.php
```

### 10.2 Commands Reference
```bash
# Generate test reports
php artisan test:report --format=csv
php artisan test:report --format=markdown
php artisan test:report --format=both

# Run tests with results parsing
php artisan test:run-parse --format=csv
php artisan test:run-parse --format=markdown
php artisan test:run-parse --format=both

# Standard test commands
php artisan test
php artisan test --parallel
php artisan test --coverage
./vendor/bin/pest --verbose
```

---

**Document Version:** 1.1  
**Last Review Date:** October 30, 2025  
**Next Review Date:** November 6, 2025  
**Test Status:** ✅ All 48 tests passing (100% success rate)
