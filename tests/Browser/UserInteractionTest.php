<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UserInteractionTest extends DuskTestCase
{
    /**
     * Test sidebar navigation works.
     *
     * @return void
     */
    public function testSidebarNavigation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->waitFor('.sidebar')
                ->assertPresent('.sidebar')
                ->click('.sidebar a[href*="help"]')
                ->waitForLocation('/help')
                ->assertSee('How to Use the Monitoring System')
                ->click('.sidebar a[href="/"]')
                ->waitForLocation('/')
                ->assertSee('UPRM Campus Map');
        });
    }

    /**
     * Test dashboard tab navigation.
     *
     * @return void
     */
    public function testDashboardTabNavigation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->waitFor('.nav-tabs')
                ->click('a[href*="alerts"]')
                ->waitForLocation('/alerts')
                ->assertSee('System Alerts')
                ->waitFor('.nav-tabs .nav-link.active')
                ->assertPresent('.nav-tabs .nav-link.active')
                ->click('a[href*="devices"]')
                ->waitForLocation('/devices')
                ->assertSee('Buildings Overview')
                ->click('a[href*="reports"]')
                ->waitForLocation('/reports')
                ->assertSee('Device Reports')
                ->click('a[href*="admin"]')
                ->waitForLocation('/admin')
                ->assertSee('Admin Panel');
        });
    }

    /**
     * Test clicking on active navigation items.
     *
     * @return void
     */
    public function testActiveNavigationItems()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->waitFor('.sidebar .nav-link.active')
                ->assertPresent('.sidebar .nav-link.active')
                ->waitFor('.nav-tabs .nav-link.active')
                ->assertPresent('.nav-tabs .nav-link.active');
        });
    }

    /**
     * Test form inputs accept user input.
     *
     * @return void
     */
    public function testFormInputsAcceptInput()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/reports')
                ->waitForText('Device Reports')
                ->waitFor('input[type="text"]')
                ->assertPresent('input[type="text"]')
                ->type('input[type="text"]', 'test')
                ->assertInputValue('input[type="text"]', 'test');
        });
    }

    /**
     * Test buttons are clickable.
     *
     * @return void
     */
    public function testButtonsAreClickable()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/admin')
                ->waitForText('Admin Panel')
                ->click('button[data-bs-target="#settings"]')
                ->waitForText('Critical Devices Configuration')
                ->click('button[data-bs-target="#addCriticalModal"]')
                ->waitFor('#addCriticalModal')
                ->assertVisible('#addCriticalModal');
        });
    }
}
