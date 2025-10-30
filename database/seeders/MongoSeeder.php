<?php
// filepath: database/seeders/MongoSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\UTCDateTime;
use Carbon\Carbon;

class MongoSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('📡 Seeding MongoDB registrar collection...');
        $this->command->newLine();
        
        // Clear existing test data
        DB::connection('mongodb')
            ->selectCollection('registrar')
            ->deleteMany([
                'identity' => ['$in' => ['4444@uprm.edu', '4445@uprm.edu', '5555@uprm.edu']]
            ]);

        $registrations = [
            // Device 1: 10.100.147.103 with extension 4444 - MAC: 4825674c55a1
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
            
            // Device 2: 10.100.100.11 with extension 4444 - MAC: aa11bb22cc33 (DIFFERENT MAC)
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
            
            // Device 2: 10.100.100.11 with extension 5555 (SAME device as above)
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
            
            // Device 3: 10.100.100.12 with extension 4445 - MAC: 4825674b686e
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
                'instrument' => '4825674b686e', // ✅ Device 3 MAC (already unique)
                'localAddress' => '136.145.71.54/RegDB::_bindingsNameSpace',
                'path' => '',
                'qvalue' => 0,
                'shardId' => 0,
                'timestamp' => new UTCDateTime(Carbon::now()->timestamp * 1000),
                'uri' => 'sip:4445@uprm.edu',
            ],
        ];

        $collection = DB::connection('mongodb')->selectCollection('registrar');

        foreach ($registrations as $registration) {
            $collection->insertOne($registration);
            
            $binding = $registration['binding'];
            $identity = $registration['identity'];
            $instrument = $registration['instrument'];
            
            $this->command->info("   ✅ {$identity}");
            $this->command->info("      @ {$binding}");
            $this->command->info("      MAC: {$instrument}");
        }

        $totalRegistrations = $collection->countDocuments();
        
        $this->command->newLine();
        $this->command->info("✅ MongoDB seeded: {$totalRegistrations} registrations");
        $this->command->newLine();
        $this->command->info("📊 Summary:");
        $this->command->info("   - Device 1: 10.100.147.103 (MAC: 4825674c55a1) → Extension 4444");
        $this->command->info("   - Device 2: 10.100.100.11 (MAC: aa11bb22cc33) → Extensions 4444, 5555");
        $this->command->info("   - Device 3: 10.100.100.12 (MAC: 4825674b686e) → Extension 4445");
    }
}