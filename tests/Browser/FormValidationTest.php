<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class FormValidationTest extends DuskTestCase
{
    /**
     * Test form validation and error messages.
     *
     * @return void
     */
    public function testFormValidation()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/reports/create')
                ->press('Submit')
                ->assertSee('The title field is required')
                ->assertSee('The description field is required');
        });
    }
}
