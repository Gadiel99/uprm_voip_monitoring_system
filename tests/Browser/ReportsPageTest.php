<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ReportsPageTest extends DuskTestCase
{
    /**
     * Test the Reports page displays correctly.
     *
     * @return void
     */
    public function testReportsPageDisplaysCorrectly()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/reports')
                ->waitForText('Device Reports')
                ->assertSee('Device Reports')
                ->assertSee('Search Filters');
        });
    }

    /**
     * Test search filter fields are present.
     *
     * @return void
     */
    public function testSearchFilterFieldsPresent()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/reports')
                ->waitForText('Device Reports')
                ->waitFor('input[type="text"]')
                ->assertPresent('input[type="text"]')
                ->assertPresent('select')
                ->assertPresent('button');
        });
    }

    /**
     * Test reports table displays.
     *
     * @return void
     */
    public function testReportsTableDisplays()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/reports')
                ->waitForText('Device Reports')
                ->waitFor('table')
                ->assertSee('User')
                ->assertSee('MAC Address')
                ->assertSee('IP Address')
                ->assertSee('Status')
                ->assertSee('Building');
        });
    }
}
