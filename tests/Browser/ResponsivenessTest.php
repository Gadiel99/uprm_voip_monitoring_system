<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ResponsivenessTest extends DuskTestCase
{
    /**
     * Test the layout is responsive on mobile viewport.
     *
     * @return void
     */
    public function testMobileViewport()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->resize(375, 667) // iPhone SE size
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->waitFor('.navbar')
                ->assertPresent('.navbar')
                ->waitFor('.sidebar')
                ->assertPresent('.sidebar');
        });
    }

    /**
     * Test the layout is responsive on tablet viewport.
     *
     * @return void
     */
    public function testTabletViewport()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->resize(768, 1024) // iPad size
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->waitFor('.navbar')
                ->assertPresent('.navbar')
                ->waitFor('.sidebar')
                ->assertPresent('.sidebar')
                ->waitFor('.nav-tabs')
                ->assertPresent('.nav-tabs');
        });
    }

    /**
     * Test the layout is responsive on desktop viewport.
     *
     * @return void
     */
    public function testDesktopViewport()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->resize(1920, 1080) // Full HD
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/')
                ->waitFor('.navbar')
                ->assertPresent('.navbar')
                ->waitFor('.sidebar')
                ->assertPresent('.sidebar')
                ->waitFor('.nav-tabs')
                ->assertPresent('.nav-tabs')
                ->waitFor('.map-wrapper')
                ->assertPresent('.map-wrapper');
        });
    }
}
