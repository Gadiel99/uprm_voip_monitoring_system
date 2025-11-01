<?php

/**
 * @file PostgresSeeder.php
 * @brief PostgreSQL seeder for VoIP user data in the UPRM monitoring system
 * @details This seeder populates the PostgreSQL users table with VoIP user account
 *          data for testing the monitoring system. It creates user profiles with
 *          SIP credentials, extension numbers, and authentication tokens that
 *          correspond to the MongoDB registrar data for complete test scenarios.
 * @author UPRM VoIP Monitoring System Team
 * @date October 30, 2025
 * @version 1.0
 * @filepath database/seeders/PostgresSeeder.php
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * @class PostgresSeeder
 * @brief Seeder class for PostgreSQL VoIP user data
 * @details This seeder creates VoIP user accounts in the PostgreSQL database
 *          to support testing and development of the VoIP monitoring system.
 *          It provides user authentication data that corresponds to SIP
 *          registrations in the MongoDB registrar collection.
 *          
 *          User Data Structure:
 *          - User Profile: First name, last name for identification
 *          - SIP Credentials: Extension numbers (user_name) and passwords
 *          - Authentication: PIN tokens for secure access
 *          
 *          Test User Configuration:
 *          - Extension 4444: Primary test user (matches MongoDB registration)
 *          - Extension 4445: Secondary test user for device testing
 *          - Extension 5555: Multi-extension test scenario
 *          
 *          Integration Points:
 *          - User names correspond to MongoDB registrar identities
 *          - SIP passwords enable device authentication
 *          - PIN tokens provide secure user access control
 *          
 *          Data Consistency:
 *          - Extension numbers match MongoDB registration data
 *          - User profiles support realistic testing scenarios
 *          - Credential format follows sipXecs standards
 * 
 * @extends Illuminate\Database\Seeder
 * @author UPRM VoIP Monitoring System Team
 * @date October 30, 2025
 */
class PostgresSeeder extends Seeder
{
    /**
     * @brief Executes the PostgreSQL seeding process for VoIP user data
     * @details This method performs comprehensive seeding of the PostgreSQL users
     *          table with VoIP user account data. The process includes data cleanup,
     *          user creation, and validation reporting to ensure proper integration
     *          with the MongoDB registrar data.
     *          
     *          Seeding Process:
     *          1. Data Cleanup: Removes existing test user accounts
     *          2. User Creation: Inserts VoIP user profiles with credentials
     *          3. Progress Reporting: Provides detailed console feedback
     *          4. Validation Summary: Reports final statistics and configuration
     *          
     *          User Account Structure:
     *          - Profile Information: Human-readable names for identification
     *          - Extension Mapping: User names as SIP extension numbers
     *          - Authentication Data: SIP passwords and PIN tokens
     *          - Integration Keys: Consistent with MongoDB registration data
     *          
     *          Security Features:
     *          - Secure SIP password generation following standards
     *          - PIN token implementation for user authentication
     *          - Data isolation through proper cleanup procedures
     * 
     * @return void
     * 
     * @throws \Illuminate\Database\QueryException If PostgreSQL operations fail
     * @throws \Exception If user creation encounters errors
     * 
     * @see MongoSeeder For corresponding MongoDB registration data
     * @author UPRM VoIP Monitoring System Team
     * @date October 30, 2025
     */
    public function run(): void
    {
        // Display initial seeding message to console
        if ($this->command) {
            $this->command->info('📞 Seeding PostgreSQL users...');
        }
        
        /*
         * VoIP User Data Structure
         * 
         * This array contains VoIP user account data that corresponds to
         * the SIP registrations created in the MongoDB seeder. Each user
         * includes complete profile and authentication information.
         * 
         * Data Fields:
         * - first_name, last_name: Human-readable user identification
         * - user_name: SIP extension number (matches MongoDB identity)
         * - pintoken: Numeric PIN for user authentication
         * - sip_password: Secure password for SIP device authentication
         * 
         * Integration Notes:
         * - user_name values match MongoDB registrar identities
         * - SIP passwords enable device registration authentication
         * - PIN tokens provide secure user access control
         */
        $users = [
            /*
             * Primary Test User
             * Extension: 4444 (corresponds to MongoDB registrations)
             * Scenario: Standard user with device registration conflicts
             */
            [
                'first_name' => 'Prueba',
                'last_name' => 'Capstone2025',
                'user_name' => '4444',
                'pintoken' => '123456789',
                'sip_password' => 'o5yXMP2ouOm2',
            ],
            /*
             * Secondary Test User
             * Extension: 4445 (single device registration)
             * Scenario: Standard user for baseline testing
             */
            [
                'first_name' => 'Prueba 2',
                'last_name' => 'Prueba2',
                'user_name' => '4445',
                'pintoken' => '123456789',
                'sip_password' => 'LzkxXFh8G6Ke2',
            ],
            /*
             * Multi-Extension Test User
             * Extension: 5555 (multi-extension device scenario)
             * Scenario: User for multi-extension device testing
             */
            [
                'first_name' => 'Prueba 3',
                'last_name' => 'Capstone 2025',
                'user_name' => '5555',
                'pintoken' => '321654987',
                'sip_password' => 'MmusDr4Q6Xa2',
            ],
        ];

        /*
         * Data Cleanup Process
         * 
         * Removes existing test users to ensure clean seeding environment.
         * This prevents duplicate data and ensures consistent test scenarios.
         */
        DB::connection('pgsql')
            ->table('users')
            ->whereIn('user_name', ['4444', '4445', '5555'])
            ->delete();

        /*
         * User Account Creation Loop
         * 
         * Iterates through user definitions to create PostgreSQL user records
         * with complete profile and authentication information
         */
        foreach ($users as $user) {
            // Insert user record into PostgreSQL users table
            DB::connection('pgsql')->table('users')->insert($user);
            
            // Display detailed progress information
            if ($this->command) {
                $this->command->info("   ✅ Created user: {$user['user_name']} ({$user['first_name']} {$user['last_name']})");
            }
        }

        /*
         * Final Statistics and Summary Report
         * 
         * Provides comprehensive feedback about the seeding operation
         * including total user count and completion confirmation
         */
        $totalUsers = DB::connection('pgsql')->table('users')->count();
        
        if ($this->command) {
            $this->command->newLine();
            $this->command->info("✅ PostgreSQL users seeded successfully!");
            $this->command->info("   Total users in database: {$totalUsers}");
        }
    }
}