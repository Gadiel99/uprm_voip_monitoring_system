# Test Cases Documentation

**Generated:** 2025-10-30 07:54:11

**Total Tests:** 48

## Summary

| Type | Count |
|------|-------|
| Unit Tests | 47 |
| Feature Tests | 1 |

## Database

| Test ID | Test Name | Type | Priority | Status |
|---------|-----------|------|----------|--------|
| TC-001 | buildings table exists | Unit | Low | Pending |
| TC-002 | buildings table has correct columns | Unit | Low | Pending |
| TC-003 | devices table exists | Unit | Low | Pending |
| TC-004 | devices table has correct columns | Unit | Low | Pending |
| TC-005 | extensions table exists | Unit | Low | Pending |
| TC-006 | extensions table has correct columns | Unit | Low | Pending |
| TC-007 | networks table exists | Unit | Low | Pending |
| TC-008 | networks table has correct columns | Unit | Low | Pending |
| TC-009 | building_networks pivot table exists | Unit | Low | Pending |
| TC-010 | building_networks pivot table has correct columns | Unit | Low | Pending |
| TC-011 | device_extensions pivot table exists | Unit | Low | Pending |
| TC-012 | device_extensions pivot table has correct columns | Unit | Low | Pending |
| TC-013 | buildings networks seeder creates building successfully | Unit | High | Pending |
| TC-014 | buildings networks seeder creates networks successfully | Unit | High | Pending |
| TC-015 | buildings networks seeder attaches networks to building | Unit | Low | Pending |
| TC-016 | seeded building has correct name | Unit | Low | Pending |
| TC-017 | seeded networks have correct subnets | Unit | Low | Pending |
| TC-018 | seeded networks have zero initial devices | Unit | Low | Pending |

## Models

| Test ID | Test Name | Type | Priority | Status |
|---------|-----------|------|----------|--------|
| TC-019 | building can be created with valid data | Unit | High | Pending |
| TC-020 | building has many networks relationship | Unit | High | Pending |
| TC-021 | building can retrieve its networks | Unit | High | Pending |
| TC-022 | building name is required | Unit | High | Pending |
| TC-023 | multiple buildings can exist | Unit | High | Pending |
| TC-024 | building can be updated | Unit | High | Pending |
| TC-025 | building can be deleted | Unit | High | Pending |
| TC-026 | device can be created with valid data | Unit | High | Pending |
| TC-027 | device belongs to a network | Unit | High | Pending |
| TC-028 | device has many extensions relationship | Unit | High | Pending |
| TC-029 | device status can be online or offline | Unit | High | Pending |
| TC-030 | device can be updated | Unit | High | Pending |
| TC-031 | device ip address is unique | Unit | High | Pending |
| TC-032 | device can be deleted | Unit | High | Pending |
| TC-033 | extension can be created with valid data | Unit | High | Pending |
| TC-034 | extension has many devices relationship | Unit | High | Pending |
| TC-035 | extension number is unique | Unit | High | Pending |
| TC-036 | extension can be updated | Unit | High | Pending |
| TC-037 | multiple extensions can exist | Unit | High | Pending |
| TC-038 | extension can be deleted | Unit | High | Pending |
| TC-039 | extension full name can be retrieved | Unit | High | Pending |
| TC-040 | network can be created with valid data | Unit | High | Pending |
| TC-041 | network has many devices relationship | Unit | High | Pending |
| TC-042 | network can update device counts | Unit | High | Pending |
| TC-043 | network subnet should be unique | Unit | High | Pending |
| TC-044 | network can be updated | Unit | High | Pending |
| TC-045 | network counts only offline devices correctly | Unit | High | Pending |
| TC-046 | network can be deleted | Unit | High | Pending |
| TC-047 | multiple networks can exist | Unit | High | Pending |

## General

| Test ID | Test Name | Type | Priority | Status |
|---------|-----------|------|----------|--------|
| TC-048 | the application returns a successful response | Feature | Low | Pending |

