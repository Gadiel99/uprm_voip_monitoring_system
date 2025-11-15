<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class FreshResetCommand extends Command
{
    protected $signature = 'fresh:reset';
    protected $description = 'Reset all databases and reseed with fresh data';

    public function handle(): int
    {
        $this->info('🔄 Starting fresh reset...');
        $this->newLine();

        // Step 1: Clear MariaDB
        $this->info('1️⃣  Clearing MariaDB (Laravel main database)...');
        DB::table('device_extensions')->delete();
        DB::table('extensions')->delete();
        DB::table('devices')->delete();
        DB::table('networks')->delete();
        DB::table('buildings')->delete();
        $this->info('   ✅ MariaDB cleared');
        $this->newLine();

        // Step 2: Clear PostgreSQL
        $this->info('2️⃣  Clearing PostgreSQL (users database)...');
        $deleted = DB::connection('pgsql')->table('users')->delete();
        $this->info("   ✅ Deleted {$deleted} PostgreSQL users");
        $this->newLine();

        // Step 3: Clear MongoDB
        $this->info('3️⃣  Clearing MongoDB (registrar collection)...');
        $result = DB::connection('mongodb')
            ->selectCollection('registrar')
            ->deleteMany([]);
        $this->info("   ✅ Deleted {$result->getDeletedCount()} MongoDB registrations");
        $this->newLine();

        // Step 4: Reseed everything
        $this->info('4️⃣  Reseeding databases...');
        Artisan::call('db:seed', ['--class' => 'BuildingsNetworksSeeder']);
        $this->info('   ✅ Buildings & Networks seeded');
        
        Artisan::call('db:seed', ['--class' => 'PostgresSeeder']);
        $this->info('   ✅ PostgreSQL users seeded');
        
        Artisan::call('db:seed', ['--class' => 'MongoSeeder']);
        $this->info('   ✅ MongoDB registrations seeded');
        
        $this->newLine();

        // Step 5: Verify data
        $this->info('5️⃣  Verifying data...');
        $buildingsCount = DB::table('buildings')->count();
        $networksCount = DB::table('networks')->count();
        $postgresCount = DB::connection('pgsql')->table('users')->count();
        $mongoCount = DB::connection('mongodb')->selectCollection('registrar')->countDocuments();
        
        $this->info("   🏢 Buildings: {$buildingsCount}");
        $this->info("   🌐 Networks: {$networksCount}");
        $this->info("   👥 PostgreSQL Users: {$postgresCount}");
        $this->info("   📡 MongoDB Registrations: {$mongoCount}");
        
        $this->newLine();
        $this->info('✅ Fresh reset complete!');
        $this->newLine();
        $this->info('💡 Now run: php artisan etl:run');

        return self::SUCCESS;
    }
}