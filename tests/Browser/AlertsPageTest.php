<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AlertsPageTest extends DuskTestCase
{
    /**
     * Test the Alerts page displays correctly.
     *
     * @return void
     */
    public function testAlertsPageDisplaysCorrectly()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/alerts')
                ->assertSee('System Alerts')
                ->assertPresent('.nav-pills')
                ->assertSeeLink('All')
                ->assertSeeLink('Critical')
                ->assertSeeLink('High')
                ->assertSeeLink('Medium')
                ->assertSeeLink('Low');
        });
    }

    /**
     * Test critical buildings table displays.
     *
     * @return void
     */
    public function testCriticalBuildingsTableDisplays()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/alerts')
                ->assertSee('Critical Buildings')
                ->assertPresent('table')
                ->assertSee('Building')
                ->assertSee('Status')
                ->assertSee('Issues')
                ->assertSee('Last Updated');
        });
    }

    /**
     * Test alert severity badges display.
     *
     * @return void
     */
    public function testAlertSeverityBadgesDisplay()
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
}
