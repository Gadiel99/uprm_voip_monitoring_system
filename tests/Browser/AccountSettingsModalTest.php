<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class AccountSettingsModalTest extends DuskTestCase
{
    /**
     * Test the Account Settings modal can be opened.
     *
     * @return void
     */
    public function testAccountSettingsModalOpens()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->click('.dropdown-toggle')
                ->waitFor('.dropdown-menu')
                ->click('a[data-bs-target="#accountSettingsModal"]')
                ->waitFor('#accountSettingsModal')
                ->assertSee('Account Settings');
        });
    }

    /**
     * Test the Account Settings modal has correct tabs.
     *
     * @return void
     */
    public function testAccountSettingsModalTabs()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->click('.dropdown-toggle')
                ->waitFor('.dropdown-menu')
                ->click('a[data-bs-target="#accountSettingsModal"]')
                ->waitFor('#accountSettingsModal')
                ->assertSee('Profile')
                ->assertSee('Password')
                ->assertSee('Preferences');
        });
    }

    /**
     * Test the Account Settings modal form fields.
     *
     * @return void
     */
    public function testAccountSettingsFormFields()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->click('.dropdown-toggle')
                ->waitFor('.dropdown-menu')
                ->click('a[data-bs-target="#accountSettingsModal"]')
                ->waitFor('#accountSettingsModal')
                ->assertPresent('input[type="text"]')
                ->assertPresent('input[type="email"]')
                ->assertPresent('button[type="submit"]');
        });
    }
}
