<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class UserCannotAccessAdminTest extends DuskTestCase
{
    /**
     * Test that a regular user cannot access the admin page.
     *
     * @return void
     */
    public function testUserCannotAccessAdminPage()
    {
        $this->browse(function (Browser $browser) {
            // Log in as a regular user (replace with a real user if needed)
            $browser->visit('/login')
                ->type('email', 'user@example.com')
                ->type('password', 'userpassword')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/admin')
                ->assertDontSee('Admin Panel')
                ->assertSee('Unauthorized|Forbidden|403|You do not have permission');
        });
    }
}
