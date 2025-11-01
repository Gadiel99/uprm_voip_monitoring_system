<?php

/**
 * @file Devices.php
 * @brief Eloquent model for the Devices entity in the UPRM VoIP Monitoring System
 * @details This file defines the Devices model which represents VoIP devices
 *          (phones, switches, routers, etc.) within the UPRM network infrastructure.
 *          Devices are associated with networks and can have multiple extensions.
 * @author UPRM VoIP Monitoring System Team
 * @date October 30, 2025
 * @version 1.0
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Networks;

/**
 * @class Devices
 * @brief Eloquent model representing VoIP devices in the network infrastructure
 * @details This model handles the devices table and provides relationships to
 *          networks and extensions. Devices represent physical or virtual VoIP
 *          equipment including phones, switches, routers, and other network devices.
 *          
 *          Database Table: devices
 *          Primary Key: device_id (auto-increment)
 *          
 *          Key Attributes:
 *          - ip_address: Device IP address (unique)
 *          - mac_address: Device MAC address
 *          - network_id: Foreign key to networks table
 *          - status: Device status ('online', 'offline', 'unknown')
 *          - is_critical: Boolean flag for critical infrastructure devices
 *          
 *          Relationships:
 *          - Many-to-One with Networks
 *          - Many-to-Many with Extensions (device_extensions pivot table)
 *          
 * @extends Illuminate\Database\Eloquent\Model
 * @author UPRM VoIP Monitoring System Team
 * @date October 30, 2025
 */
class Devices extends Model
{
    /**
     * @brief Database table associated with this model
     * @details Specifies the table name in the database that this model represents
     * @var string $table The name of the devices table
     */
    protected $table = 'devices';

    /**
     * @brief Primary key column name for the devices table
     * @details Specifies the custom primary key column name instead of Laravel's
     *          default 'id' column
     * @var string $primaryKey The primary key column name
     */
    protected $primaryKey = 'device_id';

    /**
     * @brief Mass assignable attributes for the devices model
     * @details Specifies which attributes can be mass assigned using create() or fill()
     *          methods. This provides security by explicitly allowing only these fields
     *          to be mass assigned, preventing mass assignment vulnerabilities.
     * @var array $fillable Array of attribute names that can be mass assigned
     */
    protected $fillable = ['ip_address', 'mac_address', 'network_id', 'status', 'is_critical'];
    
    /**
     * @brief Defines many-to-one relationship with Networks model
     * @details Establishes the relationship where each device belongs to a network.
     *          This relationship is maintained through the network_id foreign key
     *          in the devices table.
     *          
     *          Relationship Details:
     *          - Type: Many-to-One (belongsTo)
     *          - Related Model: Networks
     *          - Foreign Key: network_id (in devices table)
     *          - Owner Key: network_id (in networks table)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Networks, Devices>
     *         The network that contains this device
     * 
     * @see App\Models\Networks
     * @author UPRM VoIP Monitoring System Team
     * @date October 30, 2025
     */
    public function network()
    {
        /*
         * Many-to-One relationship with Networks model
         * Each device belongs to exactly one network
         */
        return $this->belongsTo(Networks::class, 'network_id', 'network_id');
    
    }

    /**
     * @brief Defines many-to-many relationship with Extensions model
     * @details Establishes the relationship where devices can have multiple extensions
     *          and extensions can be associated with multiple devices. This relationship
     *          is maintained through the device_extensions pivot table.
     *          
     *          Relationship Details:
     *          - Type: Many-to-Many (belongsToMany)
     *          - Related Model: Extensions
     *          - Pivot Table: device_extensions
     *          - Foreign Key: device_id (this model's key in pivot)
     *          - Related Key: extension_id (related model's key in pivot)
     *          - Timestamps: Enabled (created_at, updated_at in pivot table)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Extensions, Devices, \Illuminate\Database\Eloquent\Relations\Pivot>
     *         Collection of extensions associated with this device
     * 
     * @see App\Models\Extensions
     * @author Gadiel J. De Jesus Martinez - Triatek
     * @date October 30, 2025
     */
    public function extensions()
    {
        /*
         * Many-to-Many relationship with Extensions model
         * Uses device_extensions as the intermediate pivot table
         * withTimestamps() enables automatic timestamp management in pivot table
         */
        return $this->belongsToMany(Extensions::class, 'device_extensions', 'device_id', 'extension_id')
        ->withTimestamps();
    }
}
