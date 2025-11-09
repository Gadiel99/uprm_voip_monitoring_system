<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Buildings;
use App\Models\Networks;

class BuildingsNetworksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = storage_path('app/seed/building_networks.csv');
        if (!file_exists($path)) {
            $this->command->error("CSV file not found at path: $path");
            return;
        }

        $rows = $this->readCsv($path);
        
        $this->command->info("Total rows read from CSV: " . count($rows));
        
        // Debug: Show first row structure
        if (!empty($rows)) {
            $this->command->info("First row keys: " . implode(', ', array_keys($rows[0])));
            $this->command->info("First row: " . json_encode($rows[0]));
        }

        $rows = array_filter($rows, function ($r) {
            if (!isset($r['buildings']) || !isset($r['subnet'])) return false;
            $b = trim((string)$r['buildings']);
            return $b !== '' && !in_array(Str::lower($b), ['unknown', 'unkown', 'desconocido']);
        });
        
        $this->command->info("Rows after filtering: " . count($rows));

        // Índices para no repetir INSERTs
        $buildingCache = [];
        $networkCache  = [];

        DB::transaction(function () use ($rows, &$buildingCache, &$networkCache) {
            foreach ($rows as $r) {
                $buildingName = trim($r['buildings']);
                $subnet       = trim($r['subnet']);

                // 1) upsert building
                if (!isset($buildingCache[$buildingName])) {
                    $bid = DB::table('buildings')->where('name', $buildingName)->value('building_id');
                    if (!$bid) {
                        $bid = DB::table('buildings')->insertGetId([
                            'name'       => $buildingName,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ], 'building_id');
                    }
                    $buildingCache[$buildingName] = $bid;
                }
                $buildingId = $buildingCache[$buildingName];

                // 2) upsert network (clave por subnet)
                if (!isset($networkCache[$subnet])) {
                    $nid = DB::table('networks')->where('subnet', $subnet)->value('network_id');
                    if (!$nid) {
                        $nid = DB::table('networks')->insertGetId([
                            'subnet'      => $subnet,
                            'created_at'  => now(),
                            'updated_at'  => now(),
                        ], 'network_id');
                    }
                    $networkCache[$subnet] = $nid;
                }
                $networkId = $networkCache[$subnet];

                // 3) upsert pivot (sin duplicar)
                $exists = DB::table('building_networks')
                    ->where('building_id', $buildingId)
                    ->where('network_id',  $networkId)
                    ->exists();

                if (!$exists) {
                    DB::table('building_networks')->insert([
                        'building_id' => $buildingId,
                        'network_id'  => $networkId,
                    ]);
                }
            }
        });

        $this->command->info('Building ↔ Network mapping imported successfully (Unknown skipped).');
    }

    private function readCsv(string $path): array
    {
        $out = [];
        if (($h = fopen($path, "r")) !== false) {
            $headers = null;
            
            while (($data = fgetcsv($h)) !== false) {
                // First row is headers
                if ($headers === null) {
                    // Remove BOM if present and normalize headers
                    $headers = array_map(function($x) {
                        $x = trim($x);
                        // Remove UTF-8 BOM
                        $x = preg_replace('/^\xEF\xBB\xBF/', '', $x);
                        return Str::lower($x);
                    }, $data);
                    continue;
                } 
                
                // Build associative array from data row
                $row = [];
                foreach ($data as $i => $value) {
                    $key = $headers[$i] ?? $i; // Use header name or index as fallback
                    $row[$key] = $value;
                }
                $out[] = $row;
            }
            fclose($h);
        }

        return $out;
    }
}