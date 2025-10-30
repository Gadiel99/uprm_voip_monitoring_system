<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AdminPageTest extends DuskTestCase
{
    /**
     * Test the Admin page displays correctly.
     *
     * @return void
     */
    public function testAdminPageDisplaysCorrectly()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/admin')
                ->assertSee('Admin Panel')
                ->assertPresent('.nav-pills');
        });
    }

    /**
     * Test admin panel tabs are present.
     *
     * @return void
     */
    public function testAdminPanelTabsPresent()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/admin')
                ->assertSee('Backup & Restore')
                ->assertSee('Logs')
                ->assertSee('Settings')
                ->assertSee('Servers')
                ->assertSee('Users');
        });
    }

    /**
     * Test switching between admin tabs.
     *
     * @return void
     */
    public function testSwitchingBetweenAdminTabs()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/admin')
                ->click('button[data-bs-target="#logs"]')
                ->waitForText('System Logs')
                ->assertSee('System Logs')
                ->click('button[data-bs-target="#settings"]')
                ->waitForText('Critical Devices Configuration')
                ->assertSee('Critical Devices Configuration')
                ->click('button[data-bs-target="#servers"]')
                ->waitForText('Server Configuration')
                ->assertSee('Server Configuration')
                ->click('button[data-bs-target="#users"]')
                ->waitForText('User Management')
                ->assertSee('User Management');
        });
    }

    /**
     * Test add buttons are present in admin tabs.
     *
     * @return void
     */
    public function testAddButtonsPresent()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/admin')
                ->click('button[data-bs-target="#settings"]')
                ->waitForText('Critical Devices Configuration')
                ->assertPresent('button[data-bs-target="#addCriticalModal"]')
                ->click('button[data-bs-target="#servers"]')
                ->waitForText('Server Configuration')
                ->assertPresent('button[data-bs-target="#addServerModal"]')
                ->click('button[data-bs-target="#users"]')
                ->waitForText('User Management')
                ->assertPresent('button[data-bs-target="#addUserModal"]');
        });
    }

    /**
     * Test modals can be opened.
     *
     * @return void
     */
    public function testModalsCanBeOpened()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/admin')
                ->click('button[data-bs-target="#settings"]')
                ->waitForText('Critical Devices Configuration')
                ->click('button[data-bs-target="#addCriticalModal"]')
                ->waitFor('#addCriticalModal')
                ->assertSee('Add Critical Device')
                ->assertPresent('#critical_ip')
                ->assertPresent('#critical_mac')
                ->assertPresent('#critical_owner');
        });
    }

    /**
     * Test tables display in admin tabs.
     *
     * @return void
     */
    public function testTablesDisplayInAdminTabs()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/admin')
                ->click('button[data-bs-target="#settings"]')
                ->waitForText('Critical Devices Configuration')
                ->assertPresent('#criticalTable')
                ->click('button[data-bs-target="#servers"]')
                ->waitForText('Server Configuration')
                ->assertPresent('#serverTable')
                ->click('button[data-bs-target="#users"]')
                ->waitForText('User Management')
                ->assertPresent('#userTable');
        });
    }
}
