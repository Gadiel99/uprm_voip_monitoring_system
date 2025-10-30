<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class NavigationTest extends DuskTestCase
{
    /**
     * Test navigation between main pages.
     *
     * @return void
     */
    public function testMainNavigation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/reports')
                ->assertSee('Reports')
                ->visit('/devices')
                ->assertSee('Devices')
                ->visit('/alerts')
                ->assertSee('Alerts')
                ->visit('/help')
                ->assertSee('How to Use the Monitoring System');
        });
    }
}
