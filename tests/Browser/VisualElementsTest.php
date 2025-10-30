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
                ->assertPresent('.navbar-brand img')
                ->visit('/alerts')
                ->assertPresent('.navbar-brand img')
                ->visit('/devices')
                ->assertPresent('.navbar-brand img')
                ->visit('/reports')
                ->assertPresent('.navbar-brand img')
                ->visit('/admin')
                ->assertPresent('.navbar-brand img')
                ->visit('/help')
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
                // Check for UPRM green color classes
                ->assertPresent('.text-success')
                ->assertPresent('.btn-success')
                ->visit('/alerts')
                ->assertPresent('.badge-danger')
                ->visit('/devices')
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
                ->assertPresent('table')
                ->visit('/devices')
                ->assertPresent('table')
                ->visit('/reports')
                ->assertPresent('table')
                ->visit('/admin')
                ->click('button[data-bs-target="#settings"]')
                ->waitForText('Critical Devices Configuration')
                ->assertPresent('table');
        });
    }
}
