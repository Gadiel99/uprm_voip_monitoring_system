<?php

/**
 * @file MongoSeeder.php
 * @brief MongoDB seeder for VoIP registrar data in the UPRM monitoring system
 * @details This seeder populates the MongoDB registrar collection with realistic
 *          SIP registration data for testing the VoIP monitoring system. It creates
 *          device registrations with extensions, IP addresses, and MAC addresses
 *          that represent actual VoIP phone deployments across UPRM campus.
 * @author Gadiel J. De Jesus Martinez - Triatek
 * @date October 30, 2025
 * @version 1.0
 * @filepath database/seeders/MongoSeeder.php
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\UTCDateTime;
use Carbon\Carbon;

/**
 * @class MongoSeeder
 * @brief Seeder class for MongoDB VoIP registrar data
 * @details This seeder creates realistic SIP registration data in the MongoDB registrar
 *          collection to support testing and development of the VoIP monitoring system.
 *          It simulates actual device registrations with proper SIP protocol data
 *          including extensions, IP addresses, MAC addresses, and registration metadata.
 *          
 *          Test Scenario Configuration:
 *          - Device 1: Single extension registration (4444) at 10.100.147.103
 *          - Device 2: Multi-extension device (4444, 5555) at 10.100.100.11
 *          - Device 3: Single extension registration (4445) at 10.100.100.12
 *         
 * 
 * @extends Illuminate\Database\Seeder
 * @author Gadiel J. De Jesus Martinez - Triatek
 * @date October 30, 2025
 */
class MongoSeeder extends Seeder
{
    /**
     * @brief Executes the MongoDB seeding process for VoIP registrar data
     * @details This method performs comprehensive seeding of the MongoDB registrar
     *          collection with realistic SIP registration documents. The process
     *          includes data cleanup, document creation, and validation reporting.
     *          
     *          Seeding Process:
     *          1. Data Cleanup: Removes existing test registrations
     *          2. Document Creation: Inserts SIP registration documents
     *          3. Progress Reporting: Provides detailed console feedback
     *          4. Validation Summary: Reports final statistics and configuration
     *          
     *          Registration Documents Include:
     *          - SIP binding information (extension@IP:port)
     *          - Contact headers with transport protocol details
     *          - Call-ID and sequence numbers for SIP session tracking
     *          - Expiration timestamps using MongoDB UTCDateTime
     *          - Device identification via MAC address (instrument field)
     *          - Network routing information (localAddress, path)
     *          
     *          
     * 
     * @throws \MongoDB\Driver\Exception\Exception If MongoDB operations fail
     * @throws \Exception If document creation encounters errors
     * 
     * @see MongoDB\BSON\UTCDateTime
     * @see Carbon\Carbon
     * @author UPRM VoIP Monitoring System Team
     * @date October 30, 2025
     */
    public function run(): void
    {
        // Display initial seeding message to console
        if ($this->command) {
            $this->command->info('📡 Seeding MongoDB registrar collection...');
            $this->command->newLine();
        }
        
        /*
         * Data Cleanup Process
         * 
         * Removes existing test registrations to ensure clean seeding environment.
         * This prevents duplicate data and ensures consistent test scenarios.
         */
        DB::connection('mongodb')
            ->selectCollection('registrar')
            ->deleteMany([
                'identity' => ['$in' => ['4444@uprm.edu', '4445@uprm.edu', '5555@uprm.edu']]
            ]);

        /*
         * SIP Registration Data Structure
         * 
         * This array contains realistic SIP registration documents that simulate
         * actual VoIP phone registrations in the UPRM campus environment.
         * Each registration includes complete SIP protocol metadata.
         * 
         * Document Structure:
         * - _id: Unique MongoDB document identifier
         * - binding: SIP binding URI (extension@IP:port)
         * - contact: SIP contact header with transport details
         * - identity: SIP URI for the registered extension
         * - instrument: Device MAC address for hardware identification
         * - expirationTime: Registration expiration timestamp
         * - timestamp: Registration creation timestamp
         * - Additional SIP protocol metadata (callid, cseq, etc.)
         */
        $registrations = [
            /*
             * Device 1: Single Extension Registration
             * Location: Stefani Building (10.100.147.103)
             * Extension: 4444
             * MAC Address: 4825674c55a1
             * Scenario: Standard single-extension device registration
             */
            [
                '_id' => '68f00e90cb49cb4bd61a9ec',
                'binding' => 'sip:4444@10.100.147.103',
                'callid' => '7dea27e278740a336af356fcb34d62ef',
                'contact' => '<sip:4444@10.100.147.103;transport=tcp;x-sipX-nonat>',
                'cseq' => 112,
                'expirationTime' => new UTCDateTime(Carbon::now()->addHours(1)->timestamp * 1000),
                'expired' => false,
                'gruu' => null,
                'identity' => '4444@uprm.edu',
                'instanceId' => null,
                'instrument' => '4825574c55a2', 
                'localAddress' => '136.145.71.54/RegDB::_bindingsNameSpace',
                'path' => '',
                'qvalue' => 0,
                'shardId' => 0,
                'timestamp' => new UTCDateTime(Carbon::now()->timestamp * 1000),
                'uri' => 'sip:4444@uprm.edu',
            ],
            
            /*
             * Device 2: Multi-Extension Registration (First Extension)
             * Location: Stefani Building (10.100.100.11)
             * Extension: 4444 (conflicts with Device 1 for testing)
             * MAC Address: aa11bb22cc33
             * Scenario: Extension conflict detection testing
             */
            [
                '_id' => '68f92475b6cb49cb6bd70cf58',
                'binding' => 'sip:4444@10.100.100.11',
                'callid' => '5c8ec1c242848603d651d74474c55a1',
                'contact' => '<sip:4444@10.100.100.11;transport=tcp;x-sipX-nonat>',
                'cseq' => 57,
                'expirationTime' => new UTCDateTime(Carbon::now()->addHours(2)->timestamp * 1000),
                'expired' => false,
                'gruu' => null,
                'identity' => '4444@uprm.edu',
                'instanceId' => null,
                'instrument' => 'aa11bb22cc33', 
                'localAddress' => '136.145.71.54/RegDB::_bindingsNameSpace',
                'path' => '',
                'qvalue' => 0,
                'shardId' => 0,
                'timestamp' => new UTCDateTime(Carbon::now()->timestamp * 1000),
                'uri' => 'sip:4444@uprm.edu',
            ],
            
            /*
             * Device 2: Multi-Extension Registration (Second Extension)
             * Location: Stefani Building (10.100.100.11) - Same device as above
             * Extension: 5555
             * MAC Address: aa11bb22cc33 (same as previous registration)
             * Scenario: Multi-extension device testing
             */
            [
                '_id' => '68f92475b6cb49cb6bd70cf5a',
                'binding' => 'sip:5555@10.100.100.11',
                'callid' => '32f09d012e2507e28dddc5328e4c55a1',
                'contact' => '<sip:5555@10.100.100.11;transport=tcp;x-sipX-nonat>',
                'cseq' => 59,
                'expirationTime' => new UTCDateTime(Carbon::now()->addHours(1)->addMinutes(30)->timestamp * 1000),
                'expired' => false,
                'gruu' => null,
                'identity' => '5555@uprm.edu',
                'instanceId' => null,
                'instrument' => 'aa11bb22cc33', 
                'localAddress' => '136.145.71.54/RegDB::_bindingsNameSpace',
                'path' => '',
                'qvalue' => 0,
                'shardId' => 0,
                'timestamp' => new UTCDateTime(Carbon::now()->timestamp * 1000),
                'uri' => 'sip:5555@uprm.edu',
            ],
            
            /*
             * Device 3: Single Extension Registration
             * Location: Stefani Building (10.100.100.12)
             * Extension: 4445
             * MAC Address: 4825674b686e
             * Scenario: Standard single-extension device registration
             */
            [
                '_id' => '68f93cc10cb49cb4bd71013a',
                'binding' => 'sip:4445@10.100.100.12',
                'callid' => '685c59b15db36a34fb268efe14b686e',
                'contact' => '<sip:4445@10.100.100.12;transport=tcp;x-sipX-nonat>',
                'cseq' => 54,
                'expirationTime' => new UTCDateTime(Carbon::now()->addHours(1)->addMinutes(45)->timestamp * 1000),
                'expired' => false,
                'gruu' => null,
                'identity' => '4445@uprm.edu',
                'instanceId' => null,
                'instrument' => '4825674b686e', // Device 3 MAC address
                'localAddress' => '136.145.71.54/RegDB::_bindingsNameSpace',
                'path' => '',
                'qvalue' => 0,
                'shardId' => 0,
                'timestamp' => new UTCDateTime(Carbon::now()->timestamp * 1000),
                'uri' => 'sip:4445@uprm.edu',
            ],
        ];

        /*
         * MongoDB Collection Access
         * 
         * Establishes connection to the MongoDB registrar collection
         * for document insertion operations
         */
        $collection = DB::connection('mongodb')->selectCollection('registrar');

        /*
         * Registration Document Insertion Loop
         * 
         * Iterates through each registration document to insert into MongoDB
         * and provide detailed progress feedback to the console
         */

        foreach ($registrations as $registration) {
            // Insert registration document into MongoDB collection
            $collection->insertOne($registration);
            
            // Extract key information for progress reporting
            $binding = $registration['binding'];
            $identity = $registration['identity'];
            $instrument = $registration['instrument'];
            
            // Display detailed progress information
            if ($this->command) {
                $this->command->info("   ✅ {$identity}");
                $this->command->info("      @ {$binding}");
                $this->command->info("      MAC: {$instrument}");
            }
        }

        /*
         * Final Statistics and Summary Report
         * 
         * Provides comprehensive feedback about the seeding operation
         * including total document count and detailed device configuration
         */
        $totalRegistrations = $collection->countDocuments();
        
        if ($this->command) {
            $this->command->newLine();
            $this->command->info("✅ MongoDB seeded: {$totalRegistrations} registrations");
            $this->command->newLine();
            $this->command->info("📊 Summary:");
            $this->command->info("   - Device 1: 10.100.147.103 (MAC: 4825674c55a1) → Extension 4444");
            $this->command->info("   - Device 2: 10.100.100.11 (MAC: aa11bb22cc33) → Extensions 4444, 5555");
            $this->command->info("   - Device 3: 10.100.100.12 (MAC: 4825674b686e) → Extension 4445");
        }
    }
}