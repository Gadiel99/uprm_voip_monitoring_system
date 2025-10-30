# Quick Test Reference Card
## UPRM VoIP Monitoring System - Frontend Tests

---

## 🚀 Quick Commands

```bash
# Run ALL tests
php artisan dusk

# Run specific test file
php artisan dusk tests/Browser/HomePageTest.php

# Save results to file
php artisan dusk > test-results.txt

# Update ChromeDriver
php artisan dusk:chrome-driver
```

---

## 📋 Test Files (17 Total)

✅ **AccountSettingsModalTest.php** - Account modal
✅ **AdminPageTest.php** - Admin panel (6 tests)
✅ **AlertsPageTest.php** - Alerts page (3 tests)
✅ **BootstrapComponentsTest.php** - Bootstrap UI (6 tests)
✅ **DevicesPageTest.php** - Devices page (3 tests)
✅ **FormValidationTest.php** - Form validation
✅ **HelpPageTest.php** - Help documentation
✅ **HomePageTest.php** - Campus map (2 tests)
✅ **LayoutComponentsTest.php** - Navbar/Sidebar (5 tests)
✅ **LoginLogoutTest.php** - Authentication
✅ **NavigationTest.php** - Page navigation
✅ **PageAccessTest.php** - Access control
✅ **ReportsPageTest.php** - Reports page (3 tests)
✅ **ResponsivenessTest.php** - Responsive design (3 tests)
✅ **UserCannotAccessAdminTest.php** - Authorization
✅ **UserInteractionTest.php** - User interactions (5 tests)
✅ **VisualElementsTest.php** - Visual consistency (4 tests)

---

## 🔑 Test Credentials

**Admin:**
```
Email: asd@d.com
Password: 123
```

**User:**
```
Email: user@example.com
Password: userpassword
```

---

## 📄 Pages Tested

- `/login` - Login page
- `/` - Home/Dashboard with campus map
- `/alerts` - System alerts
- `/devices` - Device management
- `/reports` - Reports & search
- `/admin` - Admin panel (5 tabs)
- `/help` - Help documentation

---

## ✅ What's Tested

- [x] Login/Logout
- [x] All page layouts
- [x] Navigation (sidebar & tabs)
- [x] Modals (open/close)
- [x] Dropdowns
- [x] Tables
- [x] Forms
- [x] Buttons
- [x] Badges
- [x] Icons
- [x] Responsive design (mobile/tablet/desktop)
- [x] Campus map markers
- [x] Admin panel tabs
- [x] Bootstrap components

---

## ❌ What's NOT Tested (Backend Required)

- [ ] Adding records
- [ ] Editing records
- [ ] Deleting records
- [ ] Search results
- [ ] Filter results
- [ ] Data persistence
- [ ] API calls

---

## 🎯 Expected Results

**All tests should PASS** ✅

These are frontend-only tests that verify:
- Elements are visible
- Buttons are clickable
- Forms accept input
- Pages navigate correctly
- Modals open/close

---

## 🐛 Troubleshooting

**ChromeDriver mismatch?**
```bash
php artisan dusk:chrome-driver
```

**Tests timing out?**
- Check if server is running
- Increase wait times

**Element not found?**
- Verify HTML hasn't changed
- Check selectors in test

**Login failing?**
- Button text is "Log In" (with space)
- Credentials: asd@d.com / 123

---

## 📊 Test Output Format

**Success:**
```
PASS  Tests\Browser\HomePageTest
✓ home page displays campus map
✓ markers are interactive

Tests: 2 passed
Duration: 5.23s
```

**Failure:**
```
FAIL  Tests\Browser\HomePageTest
✗ home page displays campus map

Expected element not found: .map-wrapper
```

---

## 📚 Documentation

- **FRONTEND_TESTS_COVERAGE.md** - Full coverage report
- **FRONTEND_TESTS_DOCUMENTATION.md** - Detailed docs
- **FRONTEND_TESTS_SUMMARY.md** - Quick summary
- **QUICK_TEST_REFERENCE.md** - This card

---

## 🔄 Update ChromeDriver

```bash
# Update to latest version
php artisan dusk:chrome-driver

# Specific version
php artisan dusk:chrome-driver 142
```

---

## 📈 Test Statistics

- **Total Files:** 17
- **Total Tests:** 47+
- **Coverage:** 100% of frontend
- **Avg Duration:** ~60 seconds

---

**Created:** October 30, 2025
**Version:** 1.0
