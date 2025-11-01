<?php

/**
 * @file Buildings.php
 * @brief Eloquent model for the Buildings entity in the UPRM VoIP Monitoring System
 * @details This file defines the Buildings model which represents UPRM campus buildings
 *          that contain VoIP network infrastructure. Each building can have multiple
 *          networks associated with it through a many-to-many relationship.
 * @author Gadiel J. De Jesus Martinez - Triatek
 * @date October 30, 2025
 * @version 1.0
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Networks;

/**
 * @class Buildings
 * @brief Eloquent model representing UPRM campus buildings
 * @details This model handles the buildings table and provides relationships to other
 *          entities in the VoIP monitoring system. Buildings serve as geographical
 *          containers for network infrastructure and devices.
 *          
 *          Database Table: buildings
 *          Primary Key: building_id (auto-increment)
 *          
 *          Relationships:
 *          - Many-to-Many with Networks (building_networks pivot table)
 *          
 * @extends Illuminate\Database\Eloquent\Model
 * @author UPRM VoIP Monitoring System Team
 * @date October 30, 2025
 */
class Buildings extends Model
{
    /**
     * @brief Database table associated with this model
     * @details Specifies the table name in the database that this model represents
     * @var string $table The name of the buildings table
     */
    protected $table = 'buildings';

    /**
     * @brief Mass assignment protection configuration
     * @details Using guarded = [] allows all attributes to be mass assignable.
     *          This is appropriate for this model as input validation is handled
     *          at the controller/service layer.
     * @var array $guarded Empty array to allow mass assignment of all attributes
     */
    protected $guarded = [];

    /**
     * @brief Primary key column name for the buildings table
     * @details Specifies the custom primary key column name instead of Laravel's
     *          default 'id' column
     * @var string $primaryKey The primary key column name
     */
    protected $primaryKey = 'building_id';
    
    /**
     * @brief Defines many-to-many relationship with Networks model
     * @details Establishes the relationship between buildings and networks through
     *          the building_networks pivot table. Each building can have multiple
     *          networks, and each network can belong to multiple buildings.
     *          
     *          Relationship Details:
     *          - Type: Many-to-Many (belongsToMany)
     *          - Related Model: Networks
     *          - Pivot Table: building_networks
     *          - Foreign Key: building_id (this model's key in pivot)
     *          - Related Key: network_id (related model's key in pivot)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Networks, Buildings, \Illuminate\Database\Eloquent\Relations\Pivot>
     *         Collection of Networks associated with this building
     * 
     * @see App\Models\Networks
     * @author UPRM VoIP Monitoring System Team
     * @date October 30, 2025
     */
    public function networks()
    {
        /*
         * Many-to-Many relationship with Networks model
         * Uses building_networks as the intermediate pivot table
         * to establish the association between buildings and networks
         */
        return $this->belongsToMany(Networks::class, 'building_networks', 'building_id', 'network_id');
    }

   
}
