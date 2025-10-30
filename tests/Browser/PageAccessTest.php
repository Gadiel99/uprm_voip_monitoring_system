<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class PageAccessTest extends DuskTestCase
{
    /**
     * Test that admin can access all main pages.
     */
    public function testAdminCanAccessAllPages()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->visit('/')
                ->assertSee('Dashboard')
                ->visit('/reports')
                ->assertSee('Reports')
                ->visit('/devices')
                ->assertSee('Device Management')
                ->visit('/alerts')
                ->assertSee('System Alerts')
                ->visit('/help')
                ->assertSee('How to Use the Monitoring System')
                ->visit('/admin')
                ->assertSee('Admin Panel');
        });
    }
}
