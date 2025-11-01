<?php

/**
 * @file Extensions.php
 * @brief Eloquent model for the Extensions entity in the UPRM VoIP Monitoring System
 * @details This file defines the Extensions model which represents VoIP phone extensions
 *          for users within the UPRM campus. Extensions represent user phone numbers
 *          and can be associated with multiple devices (phone, softphone, etc.).
 * @author UPRM VoIP Monitoring System Team
 * @date October 30, 2025
 * @version 1.0
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @class Extensions
 * @brief Eloquent model representing VoIP phone extensions
 * @details This model handles the extensions table and provides relationships to
 *          devices. Extensions represent user phone numbers and contact information
 *          within the UPRM VoIP system.
 *          
 *          Database Table: extensions
 *          Primary Key: extension_id (auto-increment)
 *          
 *          Key Attributes:
 *          - extension_number: Phone extension number (unique)
 *          - first_name: User's first name
 *          - last_name: User's last name
 *          - email: User's email address (optional)
 *          - department: User's department (optional)
 *          
 *          Relationships:
 *          - Many-to-Many with Devices (device_extensions pivot table)
 *          
 * @extends Illuminate\Database\Eloquent\Model
 * @author Gadiel J. De Jesus Martinez - Triatek
 * @date October 30, 2025
 */

class Extensions extends Model
{
    /**
     * @brief Database table associated with this model
     * @details Specifies the table name in the database that this model represents
     * @var string $table The name of the extensions table
     */
    protected $table = 'extensions';

    /**
     * @brief Primary key column name for the extensions table
     * @details Specifies the custom primary key column name instead of Laravel's
     *          default 'id' column
     * @var string $primaryKey The primary key column name
     */
    protected $primaryKey = 'extension_id';

    /**
     * @brief Mass assignment protection configuration
     * @details Using guarded = [] allows all attributes to be mass assignable.
     *          This is appropriate for this model as input validation is handled
     *          at the controller/service layer.
     * @var array $guarded Empty array to allow mass assignment of all attributes
     */
    protected $guarded = [];

    /**
     * @brief Defines many-to-many relationship with Devices model
     * @details Establishes the relationship where extensions can be associated with
     *          multiple devices and devices can have multiple extensions. This allows
     *          for scenarios where a user has multiple devices (desk phone, softphone)
     *          or shared devices have multiple extensions.
     *          
     *          Relationship Details:
     *          - Type: Many-to-Many (belongsToMany)
     *          - Related Model: Devices
     *          - Pivot Table: device_extensions
     *          - Foreign Key: extension_id (this model's key in pivot)
     *          - Related Key: device_id (related model's key in pivot)
     *          - Timestamps: Enabled (created_at, updated_at in pivot table)
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Devices, Extensions, \Illuminate\Database\Eloquent\Relations\Pivot>
     *         Collection of devices associated with this extension
     * 
     * @see App\Models\Devices
     * @author Gadiel J. De Jesus Martinez - Triatek
     * @date October 30, 2025
     */
    public function devices()
    {
        /*
         * Many-to-Many relationship with Devices model
         * Uses device_extensions as the intermediate pivot table
         * withTimestamps() enables automatic timestamp management in pivot table
         */
        return $this->belongsToMany(Devices::class, 'device_extensions', 'extension_id', 'device_id')
        ->withTimestamps();
    }
}
