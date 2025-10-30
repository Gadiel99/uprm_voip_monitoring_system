# Frontend Test Suite Documentation
## UPRM VoIP Monitoring System

### Test Coverage Overview

This test suite provides comprehensive coverage of the frontend application, testing all visual elements, user interactions, and Bootstrap components.

---

## Test Files and Coverage

### 1. **LoginLogoutTest.php**
**Purpose:** Test authentication functionality
- ✓ User can log in with valid credentials (asd@d.com / 123)
- ✓ User is redirected after successful login
- ✓ Dashboard is visible after login
- ✓ Logout functionality works correctly

### 2. **HomePageTest.php**
**Purpose:** Test the home/dashboard page
- ✓ Campus map displays correctly
- ✓ Map legend shows status indicators (Normal, Warning, Critical)
- ✓ Interactive markers are present on the map
- ✓ Markers have title attributes for building names
- ✓ Map wrapper and image elements render properly

### 3. **NavigationTest.php**
**Purpose:** Test navigation between pages
- ✓ Can navigate to Reports page
- ✓ Can navigate to Devices page
- ✓ Can navigate to Alerts page
- ✓ Can navigate to Help page
- ✓ All pages display correct content

### 4. **PageAccessTest.php**
**Purpose:** Test page accessibility for admin users
- ✓ Admin can access all dashboard pages
- ✓ Authentication is required for protected pages
- ✓ Correct content displays on each page

### 5. **HelpPageTest.php**
**Purpose:** Test help documentation page
- ✓ Help page displays comprehensive content
- ✓ All sections are present (Getting Started, Alerts, Device Monitoring, etc.)
- ✓ Severity levels are documented
- ✓ Status indicators are explained
- ✓ Configuration instructions are provided

### 6. **AlertsPageTest.php**
**Purpose:** Test alerts management page
- ✓ Alerts page displays correctly
- ✓ Alert severity filter tabs present (All, Critical, High, Medium, Low)
- ✓ Critical buildings table displays
- ✓ Table columns are correct (Building, Status, Issues, Last Updated)
- ✓ Alert severity badges display properly

### 7. **DevicesPageTest.php**
**Purpose:** Test device management page
- ✓ Buildings overview table displays
- ✓ Table shows Building Name, Total Devices, Online, Offline, Status columns
- ✓ Status badges display correctly
- ✓ View Devices buttons are present and functional

### 8. **ReportsPageTest.php**
**Purpose:** Test reports and search functionality
- ✓ Reports page displays correctly
- ✓ Search filter fields are present
- ✓ Reports table displays with correct columns
- ✓ Filter inputs accept user input

### 9. **AdminPageTest.php**
**Purpose:** Test admin panel functionality
- ✓ Admin panel displays correctly
- ✓ All tabs present (Backup & Restore, Logs, Settings, Servers, Users)
- ✓ Tab switching works correctly
- ✓ Add buttons present in each tab
- ✓ Modals can be opened (Add Critical Device, Add Server, Add User)
- ✓ Tables display in admin tabs
- ✓ Form fields are present in modals

### 10. **LayoutComponentsTest.php**
**Purpose:** Test layout and UI components
- ✓ Navbar displays correctly with logo and branding
- ✓ Notifications bell icon present
- ✓ User dropdown displays with username
- ✓ Sidebar displays with correct navigation items
- ✓ Dashboard tabs display correctly
- ✓ User dropdown menu works
- ✓ Notifications dropdown works

### 11. **AccountSettingsModalTest.php**
**Purpose:** Test account settings modal
- ✓ Account Settings modal can be opened
- ✓ Modal tabs present (Profile, Password, Preferences)
- ✓ Form fields display correctly
- ✓ Submit buttons present

### 12. **BootstrapComponentsTest.php**
**Purpose:** Test Bootstrap framework components
- ✓ Modals open and close correctly
- ✓ Tabs work and switch content
- ✓ Dropdowns display on click
- ✓ Badges display correctly
- ✓ Buttons render properly
- ✓ Cards display correctly

### 13. **VisualElementsTest.php**
**Purpose:** Test visual consistency across pages
- ✓ UPRM logo displays on all pages
- ✓ Bootstrap icons display correctly
- ✓ Color scheme consistent (UPRM green theme)
- ✓ Tables display on all relevant pages

### 14. **UserInteractionTest.php**
**Purpose:** Test user interaction flows
- ✓ Sidebar navigation works
- ✓ Dashboard tab navigation functions correctly
- ✓ Active navigation items highlighted
- ✓ Form inputs accept user input
- ✓ Buttons are clickable and responsive

### 15. **ResponsivenessTest.php**
**Purpose:** Test responsive design
- ✓ Layout works on mobile viewport (375x667)
- ✓ Layout works on tablet viewport (768x1024)
- ✓ Layout works on desktop viewport (1920x1080)
- ✓ All key elements visible on different screen sizes

### 16. **FormValidationTest.php**
**Purpose:** Test form validation
- ✓ Form validation messages display
- ✓ Required field validation works

### 17. **UserCannotAccessAdminTest.php**
**Purpose:** Test authorization controls
- ✓ Regular users cannot access admin-only pages
- ✓ Appropriate error messages display

---

## Test Execution

### Running All Tests
```bash
php artisan dusk
```

### Running Specific Test File
```bash
php artisan dusk tests/Browser/HomePageTest.php
```

### Running with Output to File
```bash
php artisan dusk > dusk-testing-sheet.txt
```

---

## Test Credentials

**Admin Account:**
- Email: `asd@d.com`
- Password: `123`

**Regular User Account:**
- Email: `user@example.com`
- Password: `userpassword`

---

## Pages Tested

1. **Login Page** (`/login`)
   - Login form
   - Email/password inputs
   - Remember me checkbox
   - Forgot password link

2. **Home/Dashboard** (`/`)
   - Campus map with markers
   - Interactive building markers
   - Map legend
   - Status indicators

3. **Alerts Page** (`/alerts`)
   - Alert severity filters
   - Critical buildings table
   - Alert badges

4. **Devices Page** (`/devices`)
   - Buildings overview table
   - Device status badges
   - View devices buttons

5. **Reports Page** (`/reports`)
   - Search filters
   - Reports table
   - Filter inputs

6. **Admin Panel** (`/admin`)
   - Multiple tabs (Backup, Logs, Settings, Servers, Users)
   - Add modals for critical devices, servers, and users
   - Configuration tables
   - Edit/delete functionality

7. **Help Page** (`/help`)
   - Comprehensive documentation
   - Sections for different features
   - Usage instructions

---

## Bootstrap Components Tested

- ✓ Navigation Bar (Navbar)
- ✓ Sidebar Navigation
- ✓ Tabs (nav-tabs and nav-pills)
- ✓ Modals
- ✓ Dropdowns
- ✓ Badges
- ✓ Buttons
- ✓ Cards
- ✓ Tables
- ✓ Forms
- ✓ Input Groups

---

## Visual Elements Tested

- ✓ UPRM Logo
- ✓ Bootstrap Icons (bell, person-circle, speedometer, question-circle, etc.)
- ✓ Color scheme (UPRM green #00844b)
- ✓ Responsive layouts
- ✓ Interactive hover effects
- ✓ Active state indicators

---

## Browser Compatibility

Tests run using ChromeDriver with headless Chrome browser.

**Recommended Browsers:**
- Chrome (version 142+)
- Edge (Chromium-based)
- Firefox
- Safari

---

## Test Environment

- **Framework:** Laravel with Laravel Dusk
- **Testing Tool:** Laravel Dusk 8.3.3
- **Browser:** Chrome 142
- **ChromeDriver:** 142.0.7444.59
- **PHP Version:** 8.4
- **Frontend:** Blade Templates + Bootstrap 5.3.3

---

## Known Limitations

These tests focus on **frontend-only** functionality:
- Tests verify UI elements are present and visible
- Tests verify user interactions work (clicks, navigation, modal opening)
- Tests verify form fields accept input
- Tests do NOT verify backend CRUD operations (add, edit, delete records)
- Tests do NOT verify data persistence
- Tests do NOT verify API calls or database operations

**Backend functionality** (when implemented) will require additional integration tests.

---

## Future Test Coverage

When backend is implemented, add tests for:
- [ ] Adding critical devices through admin panel
- [ ] Adding servers through admin panel
- [ ] Adding users through admin panel
- [ ] Editing configuration settings
- [ ] Deleting records
- [ ] Search functionality with actual data
- [ ] Filter functionality with actual data
- [ ] Alert generation and display
- [ ] Device status updates
- [ ] Report generation
- [ ] User role permissions
- [ ] Data persistence across sessions

---

## Test Maintenance

**Update tests when:**
- New pages are added
- UI components are modified
- Navigation structure changes
- Forms are updated
- Modals are added/removed
- New features are implemented

**Keep credentials updated** if authentication changes.

---

## Troubleshooting

### Common Issues:

1. **ChromeDriver version mismatch:**
   ```bash
   php artisan dusk:chrome-driver
   ```

2. **Tests timing out:**
   - Increase `waitFor()` timeout values
   - Check if page elements are loading slowly

3. **Elements not found:**
   - Verify selectors match actual HTML
   - Check if elements are hidden or dynamically loaded
   - Use `waitFor()` for dynamic content

4. **Login failures:**
   - Verify test credentials are correct
   - Check login button text matches ("Log In")
   - Ensure authentication routes are working

---

## Test Results Format

Tests output in this format:
```
PASS  Tests\Browser\HomePageTest
✓ home page displays campus map
✓ markers are interactive

PASS  Tests\Browser\NavigationTest
✓ main navigation

Tests:    17 passed
Duration: 45.23s
```

Failed tests show:
```
FAIL  Tests\Browser\ExampleTest
✗ example test failed
  
  Expected element not found: .missing-class
  at tests/Browser/ExampleTest.php:25
```

---

## Contact & Support

For issues with tests or to report bugs, contact the development team.

**Test Suite Version:** 1.0
**Last Updated:** October 30, 2025
