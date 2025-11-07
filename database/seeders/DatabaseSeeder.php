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
        User::create([
            'name' => 'Joe',
            'email' => 'john.doe@voip.uprm.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::factory(10)->create();

        User::factory()->create([
            'name' => 'superadmin',
            'email' => 'superadmin@viop.uprm.com',
            'role' => 'superadmin'
        ]);

        $this->call([
            // 1. Seed MariaDB (buildings & networks) - Add this line!
            BuildingsNetworksSeeder::class
        ]);
    }
}
