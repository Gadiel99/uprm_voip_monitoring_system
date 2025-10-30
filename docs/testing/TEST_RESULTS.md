# Test Execution Results

**Test Run Date:** 2025-10-30
**Test Run Time:** 08:03:09

## Summary

| Metric | Value |
|--------|-------|
| Total Tests | 48 |
| Passed | 48 |
| Failed | 0 |
| Success Rate | 100% |

## Test Details

### Database\MigrationTest

| Test ID | Test Name | Status |
|---------|-----------|--------|
| TC-001 | buildings table exists | Pass |
| TC-002 | buildings table has correct columns | Pass |
| TC-003 | devices table exists | Pass |
| TC-004 | devices table has correct columns | Pass |
| TC-005 | extensions table exists | Pass |
| TC-006 | extensions table has correct columns | Pass |
| TC-007 | networks table exists | Pass |
| TC-008 | networks table has correct columns | Pass |
| TC-009 | building_networks pivot table exists | Pass |
| TC-010 | building_networks pivot table has correct columns | Pass |
| TC-011 | device_extensions pivot table exists | Pass |
| TC-012 | device_extensions pivot table has correct columns | Pass |

### Database\SeederTest

| Test ID | Test Name | Status |
|---------|-----------|--------|
| TC-013 | buildings networks seeder creates building successfully | Pass |
| TC-014 | buildings networks seeder creates networks successfully | Pass |
| TC-015 | buildings networks seeder attaches networks to building | Pass |
| TC-016 | seeded building has correct name | Pass |
| TC-017 | seeded networks have correct subnets | Pass |
| TC-018 | seeded networks have zero initial devices | Pass |

### Models\BuildingTest

| Test ID | Test Name | Status |
|---------|-----------|--------|
| TC-019 | building can be created with valid data | Pass |
| TC-020 | building has many networks relationship | Pass |
| TC-021 | building can retrieve its networks | Pass |
| TC-022 | building name is required | Pass |
| TC-023 | multiple buildings can exist | Pass |
| TC-024 | building can be updated | Pass |
| TC-025 | building can be deleted | Pass |

### Models\DeviceTest

| Test ID | Test Name | Status |
|---------|-----------|--------|
| TC-026 | device can be created with valid data | Pass |
| TC-027 | device belongs to a network | Pass |
| TC-028 | device has many extensions relationship | Pass |
| TC-029 | device status can be online or offline | Pass |
| TC-030 | device can be updated | Pass |
| TC-031 | device ip address is unique | Pass |
| TC-032 | device can be deleted | Pass |

### Models\ExtensionTest

| Test ID | Test Name | Status |
|---------|-----------|--------|
| TC-033 | extension can be created with valid data | Pass |
| TC-034 | extension has many devices relationship | Pass |
| TC-035 | extension number is unique | Pass |
| TC-036 | extension can be updated | Pass |
| TC-037 | multiple extensions can exist | Pass |
| TC-038 | extension can be deleted | Pass |
| TC-039 | extension full name can be retrieved | Pass |

### Models\NetworkTest

| Test ID | Test Name | Status |
|---------|-----------|--------|
| TC-040 | network can be created with valid data | Pass |
| TC-041 | network has many devices relationship | Pass |
| TC-042 | network can update device counts | Pass |
| TC-043 | network subnet should be unique | Pass |
| TC-044 | network can be updated | Pass |
| TC-045 | network counts only offline devices correctly | Pass |
| TC-046 | network can be deleted | Pass |
| TC-047 | multiple networks can exist | Pass |

### ExampleTest

| Test ID | Test Name | Status |
|---------|-----------|--------|
| TC-048 | the application returns a successful response | Pass |

