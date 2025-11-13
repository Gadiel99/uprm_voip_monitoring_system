<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Seed default admin users
     */
    public function run(): void
    {
        // Create or update superadmin user
        User::updateOrCreate(
            ['email' => 'superadmin@uprm.edu'],
            [
                'name' => 'superadmin',
                'password' => Hash::make('SuperAdmin2025!'),
                'role' => 'superadmin'
            ]
        );
        
        // Create or update Sergio Melendez user
        User::updateOrCreate(
            ['email' => 'sergio.melendez@uprm.edu'],
            [
                'name' => 'Sergio Melendez',
                'password' => Hash::make('SergioMelendez2025!'),
                'role' => 'user'
            ]
        );

        $this->command->info('✅ Seeded admin users');
    }
}
