<?php

/**
 * @file ETLService.php
 * @brief Service class for ETL (Extract, Transform, Load) processing
 * @details Handles extraction from imported files, transforms data, and loads into MariaDB
 * @author UPRM VoIP Monitoring System Team
 * @date November 6, 2025
 * @version 4.0
 */

namespace App\Services;

use App\Models\Devices;
use App\Models\Extensions;
use App\Models\Network;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * @class ETLService
 * @brief Service for ETL processing of VoIP device and extension data
 * @details Processes imported files (phones.csv, users.csv, registrar.json) and loads into MariaDB
 * @author UPRM VoIP Monitoring System Team
 * @date November 6, 2025
 */
class ETLService
{   
    /**
     * @brief Metrics to track during ETL process
     * @var array
     */
    private $metrics = [
        'devices_created' => 0,
        'devices_updated' => 0,
        'extensions_created' => 0,
        'extensions_updated' => 0,
        'devices_online' => 0,
        'devices_offline' => 0,
    ];
    
    /**
     * @brief Run the ETL process
     * @details Extracts data from imported files, transforms, and loads into MariaDB
     * 
     * @param string $importPath Path to extracted import directory
     * @return array The metrics of the ETL process
     * 
     * @throws Exception If data extraction or processing fails
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 6, 2025
     */
    public function run(string $importPath): array
    {
        try {
            Log::info('Starting ETL process', [
                'import_path' => $importPath,
                'mode' => 'import'
            ]);
            
            // Reset metrics
            $this->metrics = [
                'devices_created' => 0,
                'devices_updated' => 0,
                'extensions_created' => 0,
                'extensions_updated' => 0,
                'devices_online' => 0,
                'devices_offline' => 0,
            ];
            
            // Extract data from files
            Log::info('Extracting data from imported files');
            $phones = $this->getPhonesFromFile($importPath);
            $registrations = $this->getRegistrationsFromFile($importPath);
            $users = $this->getUsersFromFile($importPath);
            
            Log::info('Data extracted', [
                'phones_count' => $phones->count(),
                'registrations_count' => $registrations->count(),
                'users_count' => $users->count()
            ]);
            
            // Validate extracted data
            if ($users->isEmpty()) {
                throw new Exception('No user data found');
            }
            
            if ($phones->isEmpty()) {
                throw new Exception('No phone data found');
            }
            
            // Step 1: Create ALL extensions from users CSV
            Log::info('Creating extensions from users');
            foreach ($users as $user) {
                /** @var object $user */
                $extensionExists = Extensions::where('extension_number', $user->user_name)->exists();
                
                Extensions::updateOrCreate(
                    ['extension_number' => (string)$user->user_name],
                    [
                        'user_first_name' => (string)$user->first_name,
                        'user_last_name' => (string)$user->last_name,
                        'devices_registered' => 0, // Will be updated when processing devices
                    ]
                );
                
                if ($extensionExists) {
                    $this->metrics['extensions_updated']++;
                } else {
                    $this->metrics['extensions_created']++;
                }
            }
            
            Log::info('Extensions created', [
                'created' => $this->metrics['extensions_created'],
                'updated' => $this->metrics['extensions_updated']
            ]);
            
            // Step 2: Process devices from phone table and match with registrations
            Log::info('Processing devices from phones table');
            $this->processDevicesAndRegistrations($phones, $registrations);
            
            // Update network counts
            $this->updateNetworkCounts();
            
            Log::info('ETL process completed successfully', $this->metrics);
            
            return $this->metrics;
            
        } catch (Exception $e) {
            Log::error('ETL process failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /* Extract Phase - File-based */

    /**
     * @brief Extract devices/phones from imported CSV file
     * @details Reads phones.csv from extracted import directory (PostgreSQL phone table)
     * 
     * @param string $importPath Path to extracted import directory
     * @return Collection Collection of phone/device records
     * 
     * @throws Exception If file not found or invalid CSV
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 6, 2025
     */
    private function getPhonesFromFile(string $importPath): Collection
    {
        $csvFile = $this->findFileInDirectory($importPath, 'phone.csv');
        
        if (!$csvFile) {
            throw new Exception("phone.csv not found in import directory: {$importPath}");
        }
        
        Log::info('Reading phones from file', ['file' => $csvFile]);
        
        $phones = [];
        $handle = fopen($csvFile, 'r');
        
        if ($handle === false) {
            throw new Exception("Failed to open phone.csv: {$csvFile}");
        }
        
        try {
            // Read header row
            $headers = fgetcsv($handle);
            
            if ($headers === false) {
                throw new Exception("Failed to read CSV headers from phone.csv");
            }
            
            // Normalize headers (trim and lowercase)
            $headers = array_map(function($h) {
                return strtolower(trim($h));
            }, $headers);
            
            // Find column indices - we only need phone_id and serial_number
            $phoneIdIdx = array_search('phone_id', $headers);
            $serialNumberIdx = array_search('serial_number', $headers);
            
            if ($serialNumberIdx === false) {
                throw new Exception("Required column 'serial_number' not found in phone.csv. Found columns: " . implode(', ', $headers));
            }
            
            // Read data rows
            $rowNum = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                
                // Skip rows with missing serial number
                if (!isset($row[$serialNumberIdx]) || empty(trim($row[$serialNumberIdx]))) {
                    Log::warning("Skipping row with no serial_number in phone.csv", ['row' => $rowNum]);
                    continue;
                }
                
                $serialNumber = trim($row[$serialNumberIdx]);
                $phoneId = isset($row[$phoneIdIdx]) ? trim($row[$phoneIdIdx]) : null;
                
                $phones[] = (object)[
                    'phone_id' => $phoneId,
                    'serial_number' => $serialNumber,
                ];
            }
            
            Log::info('Phones loaded from file', ['count' => count($phones)]);
            
            return collect($phones);
            
        } finally {
            fclose($handle);
        }
    }

    /**
     * @brief Extract registrations from imported JSON file
     * @details Reads registrar.json from extracted import directory
     * 
     * @param string $importPath Path to extracted import directory
     * @return Collection Collection of registration records
     * 
     * @throws Exception If file not found or invalid JSON
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 6, 2025
     */
    private function getRegistrationsFromFile(string $importPath): Collection
    {
        $jsonFile = $this->findFileInDirectory($importPath, 'registrar.json');
        
        if (!$jsonFile) {
            throw new Exception("registrar.json not found in import directory: {$importPath}");
        }
        
        Log::info('Reading registrations from file', ['file' => $jsonFile]);
        
        $jsonContent = file_get_contents($jsonFile);
        
        if ($jsonContent === false) {
            throw new Exception("Failed to read registrar.json: {$jsonFile}");
        }
        
        $data = json_decode($jsonContent, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Invalid JSON in registrar.json: " . json_last_error_msg());
        }
        
        if (!is_array($data)) {
            throw new Exception("Expected JSON array in registrar.json");
        }
        
        Log::info('Registrations loaded from file', ['count' => count($data)]);
        
        // Convert array items to objects for compatibility with existing code
        return collect($data)->map(function($item) {
            return (object)$item;
        });
    }

    /**
     * @brief Extract users from imported CSV file
     * @details Reads users.csv from extracted import directory
     * 
     * @param string $importPath Path to extracted import directory
     * @return Collection Collection of user records
     * 
     * @throws Exception If file not found or invalid CSV
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 6, 2025
     */
    private function getUsersFromFile(string $importPath): Collection
    {
        $csvFile = $this->findFileInDirectory($importPath, 'users.csv');
        
        if (!$csvFile) {
            throw new Exception("users.csv not found in import directory: {$importPath}");
        }
        
        Log::info('Reading users from file', ['file' => $csvFile]);
        
        $users = [];
        $handle = fopen($csvFile, 'r');
        
        if ($handle === false) {
            throw new Exception("Failed to open users.csv: {$csvFile}");
        }
        
        try {
            // Read header row
            $headers = fgetcsv($handle);
            
            if ($headers === false) {
                throw new Exception("Failed to read CSV headers from users.csv");
            }
            
            // Normalize headers (trim and lowercase)
            $headers = array_map(function($h) {
                return strtolower(trim($h));
            }, $headers);
            
            // Find column indices
            $firstNameIdx = array_search('first_name', $headers);
            $lastNameIdx = array_search('last_name', $headers);
            $userNameIdx = array_search('user_name', $headers);
            
            if ($userNameIdx === false) {
                throw new Exception("Required column 'user_name' not found in users.csv. Found columns: " . implode(', ', $headers));
            }
            
            if ($firstNameIdx === false) {
                Log::warning("Column 'first_name' not found in users.csv");
            }
            
            if ($lastNameIdx === false) {
                Log::warning("Column 'last_name' not found in users.csv");
            }
            
            // Read data rows
            $rowNum = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNum++;
                
                // Skip rows with missing user_name (required)
                if (!isset($row[$userNameIdx]) || empty(trim($row[$userNameIdx]))) {
                    Log::warning("Skipping row with missing user_name in users.csv", ['row' => $rowNum]);
                    continue;
                }
                
                $userName = trim($row[$userNameIdx]);
                $firstName = ($firstNameIdx !== false && isset($row[$firstNameIdx])) ? trim($row[$firstNameIdx]) : '';
                $lastName = ($lastNameIdx !== false && isset($row[$lastNameIdx])) ? trim($row[$lastNameIdx]) : '';
                
                // Skip superadmin users or rows with no user_name
                if (strtolower($userName) === 'superadmin' || empty($userName)) {
                    Log::info("Skipping admin/superadmin user", [
                        'row' => $rowNum,
                        'user_name' => $userName
                    ]);
                    continue;
                }
                
                // Allow users even with empty first_name or last_name
                // The important part is having a valid user_name (extension number)
                $users[] = (object)[
                    'first_name' => $firstName ?: 'Unknown',
                    'last_name' => $lastName ?: 'User',
                    'user_name' => $userName,
                ];
            }
            
            Log::info('Users loaded from file', ['count' => count($users)]);
            
            return collect($users);
            
        } finally {
            fclose($handle);
        }
    }

    /**
     * @brief Find file in directory recursively
     * @details Searches for a file by name in directory tree
     * 
     * @param string $directory Directory to search
     * @param string $filename Filename to find
     * @return string|null Full path to file or null if not found
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 6, 2025
     */
    private function findFileInDirectory(string $directory, string $filename): ?string
    {
        if (!is_dir($directory)) {
            return null;
        }
        
        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS)
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getFilename() === $filename) {
                    return $file->getPathname();
                }
            }
        } catch (Exception $e) {
            Log::warning('Error searching for file', [
                'directory' => $directory,
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }


    /* Transform and Load Phase */

    /**
     * @brief Extract IP address from binding string
     * @details Parses SIP binding URI to extract IPv4 address
     * 
     * @param string $binding SIP binding URI (e.g., sip:user@192.168.1.100:5060)
     * @return string|null Extracted IP address or null if not found
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 6, 2025
     */
    private function extractIpFromBinding(string $binding): ?string
    {
        if (preg_match('/(\d+\.\d+\.\d+\.\d+)/', $binding, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * @brief Format MAC address with colons
     * @details Normalizes MAC address to uppercase with colon separators
     * 
     * @param string $mac Raw MAC address (with or without separators)
     * @return string Formatted MAC address (e.g., AA:BB:CC:DD:EE:FF)
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 6, 2025
     */
    private function formatMacAddress(string $mac): string
    {
        $mac = strtoupper(preg_replace('/[^a-fA-F0-9]/', '', $mac));
        
        if (strlen($mac) === 12) {
            return implode(':', str_split($mac, 2));
        }
        
        return $mac;
    }

    /**
     * @brief Find or create network based on IP address
     * @details Determines /24 subnet from IP and creates network if needed
     * 
     * @param string $ipAddress Device IP address
     * @return Network|null Network model or null if IP invalid
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 6, 2025
     */
    private function findOrCreateNetwork(string $ipAddress): ?Network
    {
        $ipParts = explode('.', $ipAddress);
        
        if (count($ipParts) !== 4) {
            return null;
        }
        
        // Match format from building_networks.csv (without /24)
        $subnet = "{$ipParts[0]}.{$ipParts[1]}.{$ipParts[2]}.0";
        
        return Network::firstOrCreate(
            ['subnet' => $subnet],
            [
                'total_devices' => 0,
                'offline_devices' => 0,
            ]
        );
    }

    /**
     * @brief Update network device counts
     * @details Recalculates total and offline device counts for all networks
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 6, 2025
     */
    private function updateNetworkCounts(): void
    {
        $networks = Network::all();
        
        foreach ($networks as $network) {
            $network->updateDeviceCounts();
            
            Log::info('Updated network counts', [
                'network_id' => $network->network_id,
                'total_devices' => $network->total_devices,
                'offline_devices' => $network->offline_devices
            ]);
        }
    }
    
    /**
     * @brief Get ETL metrics
     * @details Returns current metrics from ETL process
     * 
     * @return array Metrics array with counts
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 6, 2025
     */
    public function getMetrics(): array
    {
        return $this->metrics;
    }


    /**
     * @brief Process devices from phone table and match with registrations
     * @details Creates all devices from phone table, then updates status based on registrations
     * 
     * @param Collection $phones Collection of phone records from PostgreSQL
     * @param Collection $registrations Collection of registration records from MongoDB
     * 
     * @author UPRM VoIP Monitoring System Team
     * @date November 6, 2025
     */
    private function processDevicesAndRegistrations(Collection $phones, Collection $registrations): void
    {
        // Build a lookup map of registrations by serial number (instrument/MAC)
        $registrationMap = [];
        foreach ($registrations as $registration) {
            $instrument = $registration->instrument ?? null;
            if ($instrument) {
                // Normalize MAC address for comparison (remove colons, lowercase)
                $normalizedMac = strtolower(preg_replace('/[^a-f0-9]/', '', $instrument));
                
                $registrationMap[$normalizedMac] = [
                    'identity' => $registration->identity ?? null,
                    'binding' => $registration->binding ?? null,
                    'expired' => $registration->expired ?? true,
                ];
            }
        }
        
        Log::info('Built registration lookup map', ['count' => count($registrationMap)]);
        
        $processedExtensions = []; // Track extensions and their device counts
        
        // Process each phone from the phone table
        foreach ($phones as $phone) {
            try {
                /** @var object $phone */
                $serialNumber = (string)$phone->serial_number;
                
                // Normalize MAC address
                $normalizedMac = strtolower(preg_replace('/[^a-f0-9]/', '', $serialNumber));
                $formattedMac = $this->formatMacAddress($serialNumber);
                
                // Check if this device is currently registered
                $registration = $registrationMap[$normalizedMac] ?? null;
                
                // Determine status and IP address
                if ($registration && !$registration['expired']) {
                    // Device is online and registered
                    $status = 'online';
                    $ipAddress = $this->extractIpFromBinding($registration['binding']);
                    $identity = $registration['identity'];
                    $extensionNumber = explode('@', $identity)[0];
                    $this->metrics['devices_online']++;
                } else {
                    // Device is defined but not registered (offline)
                    $status = 'offline';
                    $ipAddress = null; // No IP address available for offline devices
                    $extensionNumber = null; // Can't determine extension if not registered
                    $this->metrics['devices_offline']++;
                }
                
                // Determine network (only if we have an IP)
                $network = null;
                if ($ipAddress) {
                    $network = $this->findOrCreateNetwork($ipAddress);
                }
                
                // Check if device exists
                $existingDevice = Devices::where('mac_address', $formattedMac)->first();
                $deviceExists = $existingDevice !== null;
                
                // Create or update device - build the data array
                $deviceData = [
                    'status' => $status,
                ];
                
                // Preserve is_critical flag if device exists, otherwise set to false for new devices
                if (!$deviceExists) {
                    $deviceData['is_critical'] = false;
                }
                
                // Only set IP and network if available
                if ($ipAddress) {
                    $deviceData['ip_address'] = $ipAddress;
                }
                if ($network) {
                    $deviceData['network_id'] = $network->network_id;
                }
                
                $device = Devices::updateOrCreate(
                    ['mac_address' => $formattedMac], // WHERE clause
                    $deviceData // Data to insert/update
                );
                
                // Update metrics
                if ($deviceExists) {
                    $this->metrics['devices_updated']++;
                } else {
                    $this->metrics['devices_created']++;
                }
                
                Log::info('Device processed', [
                    'device_id' => $device->device_id,
                    'mac' => $formattedMac,
                    'ip' => $ipAddress ?? 'N/A',
                    'status' => $status,
                    'extension' => $extensionNumber ?? 'N/A',
                    'action' => $deviceExists ? 'updated' : 'created'
                ]);
                
                // Link device to extension only if we know the extension
                if ($extensionNumber) {
                    $extension = Extensions::where('extension_number', $extensionNumber)->first();
                    
                    if ($extension) {
                        // Track this extension's device registrations
                        if (!isset($processedExtensions[$extensionNumber])) {
                            $processedExtensions[$extensionNumber] = [
                                'extension' => $extension,
                                'devices' => []
                            ];
                        }
                        
                        // Add device to this extension's list (avoid duplicates)
                        if (!in_array($device->device_id, $processedExtensions[$extensionNumber]['devices'])) {
                            $processedExtensions[$extensionNumber]['devices'][] = $device->device_id;
                        }
                        
                        // Create device-extension relationship
                        DB::table('device_extensions')->updateOrInsert(
                            [
                                'device_id' => $device->device_id,
                                'extension_id' => $extension->extension_id,
                            ],
                            [
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]
                        );
                        
                        Log::info('Extension linked to device', [
                            'extension' => $extensionNumber,
                            'device_id' => $device->device_id
                        ]);
                    } else {
                        Log::warning('Extension not found for device', [
                            'extension' => $extensionNumber,
                            'device_id' => $device->device_id
                        ]);
                    }
                }
                
            } catch (Exception $e) {
                Log::error('Error processing phone', [
                    'phone' => $phone,
                    'error' => $e->getMessage()
                ]);
                continue;
            }
        }
        
        // Update devices_registered count for each extension
        foreach ($processedExtensions as $extensionNumber => $data) {
            $extension = $data['extension'];
            $deviceCount = count($data['devices']);
            
            $extension->devices_registered = $deviceCount;
            $extension->save();
            
            Log::info('Updated extension device count', [
                'extension' => $extensionNumber,
                'devices_registered' => $deviceCount
            ]);
        }
    }
}
