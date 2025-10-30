<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class VisualElementsTest extends DuskTestCase
{
    /**
     * Test UPRM logo displays on all pages.
     *
     * @return void
     */
    public function testLogoDisplaysOnAllPages()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->waitFor('.navbar-brand img')
                ->assertPresent('.navbar-brand img')
                ->visit('/alerts')
                ->waitFor('.navbar-brand img')
                ->assertPresent('.navbar-brand img')
                ->visit('/devices')
                ->waitFor('.navbar-brand img')
                ->assertPresent('.navbar-brand img')
                ->visit('/reports')
                ->waitFor('.navbar-brand img')
                ->assertPresent('.navbar-brand img')
                ->visit('/admin')
                ->waitFor('.navbar-brand img')
                ->assertPresent('.navbar-brand img')
                ->visit('/help')
                ->waitFor('.navbar-brand img')
                ->assertPresent('.navbar-brand img');
        });
    }

    /**
     * Test Bootstrap icons display correctly.
     *
     * @return void
     */
    public function testBootstrapIconsDisplay()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->waitFor('.bi-bell')
                ->assertPresent('.bi-bell')
                ->assertPresent('.bi-person-circle')
                ->assertPresent('.bi-speedometer2')
                ->assertPresent('.bi-question-circle');
        });
    }

    /**
     * Test color scheme consistency.
     *
     * @return void
     */
    public function testColorSchemeConsistency()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->waitFor('.sidebar')
                // Check for UPRM green color classes
                ->assertPresent('.text-success')
                ->assertPresent('.btn-success')
                ->visit('/alerts')
                ->waitForText('System Alerts')
                ->assertPresent('.badge-danger')
                ->visit('/devices')
                ->waitForText('Buildings Overview')
                ->assertPresent('.badge-success');
        });
    }

    /**
     * Test tables display correctly on all pages.
     *
     * @return void
     */
    public function testTablesDisplay()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/alerts')
                ->waitForText('System Alerts')
                ->waitFor('table')
                ->assertPresent('table')
                ->visit('/devices')
                ->waitForText('Buildings Overview')
                ->waitFor('table')
                ->assertPresent('table')
                ->visit('/reports')
                ->waitForText('Device Reports')
                ->waitFor('table')
                ->assertPresent('table')
                ->visit('/admin')
                ->waitForText('Admin Panel')
                ->click('button[data-bs-target="#settings"]')
                ->waitForText('Critical Devices Configuration')
                ->waitFor('table')
                ->assertPresent('table');
        });
    }
}
