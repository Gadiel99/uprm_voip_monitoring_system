<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class HomePageTest extends DuskTestCase
{
    /**
     * Test the home page displays the campus map and markers.
     *
     * @return void
     */
    public function testHomePageDisplaysCampusMap()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->waitForText('UPRM Campus Map')
                ->assertSee('UPRM Campus Map - System Status')
                ->assertSee('Map Legend')
                ->assertSee('Normal')
                ->assertSee('Warning')
                ->assertSee('Critical')
                ->waitFor('.map-wrapper')
                ->assertPresent('.map-image')
                ->assertPresent('.marker');
        });
    }

    /**
     * Test that markers are interactive (hover effect).
     *
     * @return void
     */
    public function testMarkersAreInteractive()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->waitFor('.marker')
                ->assertAttribute('.marker', 'title', 'Celis');
        });
    }
}
