<?php

/**
 * @file Networks.php
 * @brief Eloquent model for the Networks entity in the UPRM VoIP Monitoring System
 * @details This file defines the Networks model which represents network subnets
 *          within UPRM campus buildings. Networks contain VoIP devices and track
 *          device statistics including total and offline device counts.
 * @author UPRM VoIP Monitoring System Team
 * @date October 30, 2025
 * @version 1.0
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @class Networks
 * @brief Eloquent model representing network subnets in the VoIP infrastructure
 * @details This model handles the networks table and provides relationships to
 *          buildings and devices. Networks represent IP subnets that contain
 *          VoIP devices and maintain statistics about device status.
 *          
 *          Database Table: networks
 *          Primary Key: network_id (auto-increment)
 *          
 *          Key Attributes:
 *          - subnet: IP subnet in CIDR notation (e.g., "192.168.1.0/24")
 *          - total_devices: Count of all devices in this network
 *          - offline_devices: Count of offline devices in this network
 *          
 *          Relationships:
 *          - Many-to-One with Buildings
 *          - One-to-Many with Devices
 *          
 * @extends Illuminate\Database\Eloquent\Model
 * @author Gadiel J. De Jesus Martinez - Triatek
 * @date October 30, 2025
 */

class Networks extends Model
{
    /**
     * @brief Primary key column name for the networks table
     * @details Specifies the custom primary key column name instead of Laravel's
     *          default 'id' column
     * @var string $primaryKey The primary key column name
     */
    protected $primaryKey = 'network_id';

    /**
     * @brief Mass assignment protection configuration
     * @details Using guarded = [] allows all attributes to be mass assignable.
     *          This is appropriate for this model as input validation is handled
     *          at the controller/service layer.
     * @var array $guarded Empty array to allow mass assignment of all attributes
     */
    protected $guarded = [];

    /**
     * @brief Defines many-to-one relationship with Buildings model
     * @details Establishes the relationship where each network belongs to a building.
     *          This relationship is maintained through the building_id foreign key
     *          in the networks table.
     *          
     *          Relationship Details:
     *          - Type: Many-to-One (belongsTo)
     *          - Related Model: Buildings
     *          - Foreign Key: building_id (in networks table)
     *          - Owner Key: building_id (in buildings table)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Buildings, Networks>
     *         The building that contains this network
     * 
     * @see App\Models\Buildings
     * @author UPRM VoIP Monitoring System Team
     * @date October 30, 2025
     */
    public function buildings()
    {
        /*
         * Many-to-One relationship with Buildings model
         * Each network belongs to exactly one building
         */
        return $this->belongsTo(Buildings::class, 'building_id', 'building_id');
    
    }

    /**
     * @brief Defines one-to-many relationship with Devices model
     * @details Establishes the relationship where each network can contain multiple
     *          devices. This relationship is maintained through the network_id
     *          foreign key in the devices table.
     *          
     *          Relationship Details:
     *          - Type: One-to-Many (hasMany)
     *          - Related Model: Devices
     *          - Foreign Key: network_id (in devices table)
     *          - Local Key: network_id (in networks table)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Devices, Networks>
     *         Collection of devices within this network
     * 
     * @see App\Models\Devices
     * @author UPRM VoIP Monitoring System Team
     * @date October 30, 2025
     */
    public function devices()
    {
        /*
         * One-to-Many relationship with Devices model
         * Each network can contain multiple devices
         */
        return $this->hasMany(Devices::class, 'network_id', 'network_id');
    
    }

 
    /**
     * @brief Updates device count statistics for this network
     * @details Recalculates and updates the total_devices and offline_devices
     *          counters based on the current state of devices in this network.
     *          This method should be called whenever device status changes or
     *          devices are added/removed from the network.
     *          
     *          Operations performed:
     *          1. Count total devices associated with this network
     *          2. Count devices with 'offline' status in this network
     *          3. Update the model's total_devices and offline_devices attributes
     *          4. Save the changes to the database
     * 
     * @return void
     * 
     * @throws \Illuminate\Database\QueryException If database update fails
     * 
     * @see App\Models\Devices
     * @author UPRM VoIP Monitoring System Team
     * @date October 30, 2025
     */
    public function updateDeviceCounts()
    {
        /*
         * Calculate total number of devices in this network
         * Uses the devices() relationship to count associated devices
         */
        $this->total_devices = $this->devices()->count();
        
        /*
         * Calculate number of offline devices in this network
         * Filters devices by 'offline' status and counts them
         */
        $this->offline_devices = $this->devices()->where('status', 'offline')->count();
        
        /*
         * Persist the updated counts to the database
         * This triggers an UPDATE SQL statement
         */
        $this->save();
    
    }
}
