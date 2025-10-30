<?php
// filepath: database/seeders/PostgresUserSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PostgresSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📞 Seeding PostgreSQL users...');
        
        $users = [
            [
                'first_name' => 'Prueba',
                'last_name' => 'Capstone2025',
                'user_name' => '4444',
                'pintoken' => '123456789',
                'sip_password' => 'o5yXMP2ouOm2',
            ],
            [
                'first_name' => 'Prueba 2',
                'last_name' => 'Prueba2',
                'user_name' => '4445',
                'pintoken' => '123456789',
                'sip_password' => 'LzkxXFh8G6Ke2',
            ],
            [
                'first_name' => 'Prueba 3',
                'last_name' => 'Capstone 2025',
                'user_name' => '5555',
                'pintoken' => '321654987',
                'sip_password' => 'MmusDr4Q6Xa2',
            ],
        ];

        // Clear existing test users (optional)
        DB::connection('pgsql')
            ->table('users')
            ->whereIn('user_name', ['4444', '4445', '5555'])
            ->delete();

        // Insert users
        foreach ($users as $user) {
            DB::connection('pgsql')->table('users')->insert($user);
            $this->command->info("   ✅ Created user: {$user['user_name']} ({$user['first_name']} {$user['last_name']})");
        }

        $totalUsers = DB::connection('pgsql')->table('users')->count();
        
        $this->command->newLine();
        $this->command->info("✅ PostgreSQL users seeded successfully!");
        $this->command->info("   Total users in database: {$totalUsers}");
    }
}