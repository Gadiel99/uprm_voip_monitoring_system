<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class LoginLogoutTest extends DuskTestCase
{
    /**
     * Test login and logout process.
     *
     * @return void
     */
    public function testLoginAndLogout()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->assertSee('Dashboard')
                ->click('@logout-button')
                ->assertPathIs('/login');
        });
    }
}
