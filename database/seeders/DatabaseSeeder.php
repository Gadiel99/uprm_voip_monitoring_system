<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@voip.uprm.com',
            'password' => bcrypt('SuperAdmin2025!'),
            'role' => 'superadmin',
        ]);

        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@voip.uprm.com',
            'password' => bcrypt('Admin2025!'),
            'role' => 'admin',
        ]);

       $regularUsers = [
            ['name' => 'John Doe', 'email' => 'john.doe@voip.uprm.com'],
            ['name' => 'Jane Smith', 'email' => 'jane.smith@voip.uprm.com'],
            ['name' => 'Carlos Rodriguez', 'email' => 'carlos.rodriguez@voip.uprm.com'],
        ];

        foreach ($regularUsers as $userData) {
            User::factory()->create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => bcrypt('User2025!'),
                'role' => 'user',
            ]);
        }

        $this->call([
            // 1. Seed MariaDB (buildings & networks) - Add this line!
            BuildingsNetworksSeeder::class
        ]);
    }
}
