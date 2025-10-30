<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LayoutComponentsTest extends DuskTestCase
{
    /**
     * Test the navbar displays correctly.
     *
     * @return void
     */
    public function testNavbarDisplaysCorrectly()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->assertPresent('.navbar')
                ->assertSee('UPRM Monitoring System')
                ->assertPresent('.navbar-brand img')
                // Check for notifications bell
                ->assertPresent('.bi-bell')
                // Check for user dropdown
                ->assertPresent('.bi-person-circle')
                ->assertSee('Admin');
        });
    }

    /**
     * Test the sidebar displays correctly.
     *
     * @return void
     */
    public function testSidebarDisplaysCorrectly()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->assertPresent('.sidebar')
                ->assertSee('Dashboard')
                ->assertSee('Help')
                ->assertPresent('.bi-speedometer2')
                ->assertPresent('.bi-question-circle');
        });
    }

    /**
     * Test the dashboard tabs display correctly.
     *
     * @return void
     */
    public function testDashboardTabsDisplayCorrectly()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->assertPresent('.nav-tabs')
                ->assertSeeLink('Home')
                ->assertSeeLink('Alerts')
                ->assertSeeLink('Devices')
                ->assertSeeLink('Reports')
                ->assertSeeLink('Admin');
        });
    }

    /**
     * Test the user dropdown menu.
     *
     * @return void
     */
    public function testUserDropdownMenu()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->click('.dropdown-toggle')
                ->waitFor('.dropdown-menu')
                ->assertSee('My Account')
                ->assertSee('Account Settings')
                ->assertSee('User Preview')
                ->assertSee('Logout');
        });
    }

    /**
     * Test the notifications dropdown.
     *
     * @return void
     */
    public function testNotificationsDropdown()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->click('.bi-bell')
                ->waitFor('.dropdown-menu')
                ->assertSee('Notifications')
                ->assertSee('No new notifications');
        });
    }
}
