<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class BootstrapComponentsTest extends DuskTestCase
{
    /**
     * Test Bootstrap modals work correctly.
     *
     * @return void
     */
    public function testBootstrapModalsWork()
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
                ->assertVisible('#addCriticalModal')
                ->click('.modal-header .btn-close')
                ->waitUntilMissing('#addCriticalModal');
        });
    }

    /**
     * Test Bootstrap tabs work correctly.
     *
     * @return void
     */
    public function testBootstrapTabsWork()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->assertSeeLink('Home')
                ->click('a[href*="alerts"]')
                ->waitForLocation('/alerts')
                ->assertSee('System Alerts')
                ->click('a[href*="devices"]')
                ->waitForLocation('/devices')
                ->assertSee('Buildings Overview')
                ->click('a[href*="reports"]')
                ->waitForLocation('/reports')
                ->assertSee('Device Reports');
        });
    }

    /**
     * Test Bootstrap dropdowns work correctly.
     *
     * @return void
     */
    public function testBootstrapDropdownsWork()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->assertPresent('.dropdown')
                ->click('.dropdown-toggle')
                ->waitFor('.dropdown-menu')
                ->assertVisible('.dropdown-menu');
        });
    }

    /**
     * Test Bootstrap badges display correctly.
     *
     * @return void
     */
    public function testBootstrapBadgesDisplay()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/alerts')
                ->assertPresent('.badge');
        });
    }

    /**
     * Test Bootstrap buttons display correctly.
     *
     * @return void
     */
    public function testBootstrapButtonsDisplay()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/devices')
                ->assertPresent('.btn-success')
                ->visit('/reports')
                ->assertPresent('.btn');
        });
    }

    /**
     * Test Bootstrap cards display correctly.
     *
     * @return void
     */
    public function testBootstrapCardsDisplay()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->assertPresent('.card');
        });
    }
}
