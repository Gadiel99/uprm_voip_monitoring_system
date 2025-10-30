<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class DevicesPageTest extends DuskTestCase
{
    /**
     * Test the Devices page displays correctly.
     *
     * @return void
     */
    public function testDevicesPageDisplaysCorrectly()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/devices')
                ->waitForText('Buildings Overview')
                ->assertSee('Buildings Overview')
                ->waitFor('table')
                ->assertSee('Building Name')
                ->assertSee('Total Devices')
                ->assertSee('Online')
                ->assertSee('Offline')
                ->assertSee('Status');
        });
    }

    /**
     * Test status badges display correctly.
     *
     * @return void
     */
    public function testStatusBadgesDisplay()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/devices')
                ->waitForText('Buildings Overview')
                ->waitFor('.badge')
                ->assertPresent('.badge');
        });
    }

    /**
     * Test view devices buttons are present.
     *
     * @return void
     */
    public function testViewDevicesButtonsPresent()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/devices')
                ->waitForText('Buildings Overview')
                ->waitFor('.btn-success')
                ->assertPresent('.btn-success');
        });
    }
}
