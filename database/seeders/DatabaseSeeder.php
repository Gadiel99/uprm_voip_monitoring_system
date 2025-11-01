<?php

/**
 * @file DatabaseSeeder.php
 * @brief Main database seeder for the UPRM VoIP Monitoring System
 * @details This seeder orchestrates the complete database seeding process for both
 *          MariaDB and MongoDB components. It creates user accounts with different
 *          roles and calls specialized seeders for buildings, networks, and MongoDB data.
 * @author Gadiel J. De Jesus Martinez - Triatek
 * @date October 30, 2025
 * @version 1.0
 * @filepath database/seeders/DatabaseSeeder.php
 */

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * @class DatabaseSeeder
 * @brief Primary seeder class for the VoIP monitoring system database
 * @details This seeder serves as the entry point for all database seeding operations.
 *          It creates a hierarchical user system with multiple roles and coordinates
 *          the execution of specialized seeders for different database components.
 *          
 *          User Roles Created:
 *          - Super Admin: Full system access with elevated privileges
 *          - Admin: Administrative access with limited system control
 *          - Regular Users: Standard monitoring and viewing permissions
 *          
 *          Seeding Order:
 *          1. User accounts (super admin, admin, regular users)
 *          2. MariaDB data (buildings, networks, relationships)
 *          3. MongoDB data (called conditionally via specialized seeders)
 *          
 *          Security Features:
 *          - Bcrypt password hashing for all user accounts
 *          - Role-based access control implementation
 *          - Secure default passwords following institutional standards
 * 
 * @extends Illuminate\Database\Seeder
 * @author UPRM VoIP Monitoring System Team
 * @date October 30, 2025
 */

class DatabaseSeeder extends Seeder
{
    /**
     * @brief Seeds the entire application database with initial data
     * @details This method executes the complete database seeding process for the
     *          VoIP monitoring system. The seeding operation follows a specific
     *          sequence to ensure proper data relationships and dependencies.
     *          
     *          Seeding Process:
     *          1. Administrative Users: Creates super admin and admin accounts
     *          2. Regular Users: Creates standard user accounts for testing
     *          3. Specialized Seeders: Calls dedicated seeders for complex data
     *          
     *          User Account Structure:
     *          - Super Admin: Full system privileges, secure credentials
     *          - Admin: Administrative access, departmental management
     *          - Regular Users: Standard monitoring access, role-based permissions
     *          
     *          Security Implementation:
     *          - All passwords use bcrypt hashing with strong defaults
     *          - Role assignment follows least privilege principle
     *          - Email addresses follow institutional domain standards
     * 
     * @return void
     * 
     * @throws \Illuminate\Database\QueryException If user creation fails
     * @throws \Exception If password hashing encounters errors
     * 
     * @see App\Models\User
     * @see BuildingsNetworksSeeder
     * @author Gadiel J. De Jesus Martinez - Triatek
     * @date October 30, 2025
     */
    public function run(): void
    {
        /*
         * Administrative User Creation
         * 
         * Creates high-privilege user accounts for system administration
         * and management operations. These accounts have elevated access
         * to all system functions and data.
         */
        
        /*
         * Super Administrator Account
         * 
         * Highest privilege level with full system access including:
         * - User management and role assignment
         * - System configuration and settings
         * - Database administration capabilities
         * - Complete monitoring data access
         */
        User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@voip.uprm.com',
            'password' => bcrypt('SuperAdmin2025!'),
            'role' => 'superadmin',
        ]);

        /*
         * Administrative User Account
         * 
         * Administrative privilege level with limited system access including:
         * - Department-level user management
         * - Monitoring configuration within scope
         * - Data analysis and reporting capabilities
         * - Building and network management
         */
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@voip.uprm.com',
            'password' => bcrypt('Admin2025!'),
            'role' => 'admin',
        ]);

        /*
         * Regular User Account Creation
         * 
         * Creates standard user accounts for testing and demonstration
         * purposes. These accounts have basic monitoring access with
         * read-only permissions for most system data.
         */
        $regularUsers = [
            ['name' => 'John Doe', 'email' => 'john.doe@voip.uprm.com'],
            ['name' => 'Jane Smith', 'email' => 'jane.smith@voip.uprm.com'],
            ['name' => 'Carlos Rodriguez', 'email' => 'carlos.rodriguez@voip.uprm.com'],
        ];

        /*
         * Regular User Processing Loop
         * 
         * Iterates through the regular user definitions to create
         * standard user accounts with consistent security settings
         */
        foreach ($regularUsers as $userData) {
            User::factory()->create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => bcrypt('User2025!'),  // Standard password for testing
                'role' => 'user',                   // Basic user role
            ]);
        }

        /*
         * Specialized Seeder Execution
         * 
         * Calls dedicated seeders for complex data structures that require
         * specialized logic or external data sources. This approach maintains
         * separation of concerns and allows for modular seeding operations.
         */
        $this->call([
            // MariaDB Infrastructure Data Seeding
            BuildingsNetworksSeeder::class  // Seeds UPRM buildings and network infrastructure
        ]);
    }
}
