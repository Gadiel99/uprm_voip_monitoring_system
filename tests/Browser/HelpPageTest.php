<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class HelpPageTest extends DuskTestCase
{
    /**
     * Test the Help page displays expected content.
     *
     * @return void
     */
    public function testHelpPageDisplaysContent()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', 'asd@d.com')
                ->type('password', '123')
                ->press('Log In')
                ->assertPathIsNot('/login')
                ->visit('/help')
                ->assertSee('How to Use the Monitoring System')
                ->assertSee('Getting Started')
                ->assertSee('Dashboard Overview:')
                ->assertSee('Monitor Alerts:')
                ->assertSee('Device Management:')
                ->assertSee('System Health:')
                ->assertSee('Working with Alerts')
                ->assertSee('Understanding Severity Levels:')
                ->assertSee('Critical:')
                ->assertSee('High:')
                ->assertSee('Medium:')
                ->assertSee('Low:')
                ->assertSee('Device Monitoring')
                ->assertSee('Status Indicators:')
                ->assertSee('Online:')
                ->assertSee('Offline:')
                ->assertSee('Warning:')
                ->assertSee('Configuring Thresholds')
                ->assertSee('Access Settings:')
                ->assertSee('Threshold Types:')
                ->assertSee('Save Changes:')
                ->assertSee('Running Diagnostics')
                ->assertSee('Performance Metrics:')
                ->assertSee('Diagnostic Tests:')
                ->assertSee('System Health:');
        });
    }
}
