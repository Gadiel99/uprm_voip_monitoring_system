<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::factory()->create([
            'name' => 'superadmin',
            'email' => 'superadmin@uprm.edu',
            'password' => Hash::make('SuperAdmin2025!'),
            'role' => 'superadmin'
        ]);

        $this->call([
            // 1. Seed MariaDB (buildings & networks) - Add this line!
            BuildingsNetworksSeeder::class
        ]);
    }
}
