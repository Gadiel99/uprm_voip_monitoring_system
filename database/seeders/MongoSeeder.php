<?php
// filepath: database/seeders/MongoRegistrarSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\UTCDateTime;
use Carbon\Carbon;

class MongoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('📡 Seeding MongoDB registrar collection...');
        
        $registrations = [
            // Device 1: 10.100.147.103 with extension 4444
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
                'instanceId' => '4825674c55a1',
                'instrument' => null,
                'localAddress' => '136.145.71.54/RegDB::_bindingsNameSpace',
                'path' => '',
                'qvalue' => 0,
                'shardId' => 0,
                'timestamp' => new UTCDateTime(Carbon::now()->timestamp * 1000),
                'uri' => 'sip:4444@uprm.edu',
            ],
            
            // Device 2: 10.100.100.11 with extension 4444
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
                'instanceId' => '4825674c55a1',
                'instrument' => null,
                'localAddress' => '136.145.71.54/RegDB::_bindingsNameSpace',
                'path' => '',
                'qvalue' => 0,
                'shardId' => 0,
                'timestamp' => new UTCDateTime(Carbon::now()->timestamp * 1000),
                'uri' => 'sip:4444@uprm.edu',
            ],
            
            // Device 2: 10.100.100.11 with extension 5555 (same device, different extension)
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
                'instanceId' => '4825674c55a1',
                'instrument' => null,
                'localAddress' => '136.145.71.54/RegDB::_bindingsNameSpace',
                'path' => '',
                'qvalue' => 0,
                'shardId' => 0,
                'timestamp' => new UTCDateTime(Carbon::now()->timestamp * 1000),
                'uri' => 'sip:5555@uprm.edu',
            ],
            
            // Device 3: 10.100.100.12 with extension 4445
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
                'instanceId' => '4825674b686e',
                'instrument' => null,
                'localAddress' => '136.145.71.54/RegDB::_bindingsNameSpace',
                'path' => '',
                'qvalue' => 0,
                'shardId' => 0,
                'timestamp' => new UTCDateTime(Carbon::now()->timestamp * 1000),
                'uri' => 'sip:4445@uprm.edu',
            ],
        ];

        // Clear existing test registrations
        DB::connection('mongodb')
            ->getDatabase()
            ->selectCollection('registrar')
            ->deleteMany([
                'identity' => ['$in' => ['4444@uprm.edu', '4445@uprm.edu', '5555@uprm.edu']]
            ]);

        // Insert registrations
        $collection = DB::connection('mongodb')
            ->getDatabase()
            ->selectCollection('registrar');

        foreach ($registrations as $registration) {
            $collection->insertOne($registration);
            $binding = $registration['binding'];
            $identity = $registration['identity'];
            $this->command->info("   ✅ Created registration: {$binding} → {$identity}");
        }

        $totalRegistrations = $collection->countDocuments();
        
        $this->command->newLine();
        $this->command->info("✅ MongoDB registrar seeded successfully!");
        $this->command->info("   Total registrations: {$totalRegistrations}");
        $this->command->newLine();
        $this->command->info("📊 Expected ETL results:");
        $this->command->info("   Devices: 3 (10.100.147.103, 10.100.100.11, 10.100.100.12)");
        $this->command->info("   Extensions: 3 (4444, 4445, 5555)");
        $this->command->info("   Networks: 2 (10.100.147.0/24, 10.100.100.0/24)");
    }
}