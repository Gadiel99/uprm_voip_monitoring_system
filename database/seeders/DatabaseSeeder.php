<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Database\Seeders\BuildingsNetworksSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
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

        $this->call([
            // 1. Seed MariaDB (buildings & networks)
            BuildingsNetworksSeeder::class
        ]);
    }
}
