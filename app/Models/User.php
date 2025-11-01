<?php

/**
 * @file User.php
 * @brief Eloquent model for the User entity in the UPRM VoIP Monitoring System
 * @details This file defines the User model which represents system users who have
 *          access to the VoIP monitoring dashboard. Users can have different roles
 *          and permissions within the system.
 * @author UPRM VoIP Monitoring System Team
 * @date October 30, 2025
 * @version 1.0
 */

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @class User
 * @brief Eloquent model representing system users
 * @details This model handles the users table and provides authentication
 *          functionality for the VoIP monitoring system. Users can have different
 *          roles and access levels to monitor and manage the VoIP infrastructure.
 *          
 *          Database Table: users
 *          Primary Key: id (auto-increment)
 *          
 *          Key Attributes:
 *          - name: User's full name
 *          - email: User's email address (unique, used for authentication)
 *          - password: Hashed password for authentication
 *          - role: User role (admin, operator, viewer, etc.)
 *          - email_verified_at: Timestamp of email verification
 *          - remember_token: Token for "remember me" functionality
 *          
 *          Features:
 *          - Authentication (login/logout)
 *          - Email verification (optional)
 *          - Password hashing
 *          - Role-based access control
 *          - Factory support for testing
 *          - Notification system integration
 *          
 * @extends Illuminate\Foundation\Auth\User
 * @uses HasFactory<\Database\Factories\UserFactory>
 * @uses Notifiable
 * @author Gadiel J. De Jesus Martinez - Triatek
 * @date October 30, 2025
 */

class User extends Authenticatable
{
    /**
     * @brief Enables factory support and notification features
     * @details Uses Laravel's HasFactory trait for model factories (testing/seeding)
     *          and Notifiable trait for sending notifications to users
     */
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * @brief Mass assignable attributes for the users model
     * @details Specifies which attributes can be mass assigned using create() or fill()
     *          methods. This provides security by explicitly allowing only these fields
     *          to be mass assigned, preventing mass assignment vulnerabilities.
     *          
     *          Allowed attributes:
     *          - name: User's full name
     *          - email: User's email address
     *          - password: User's password (will be hashed automatically)
     *          - role: User's role in the system
     *
     * @var list<string> $fillable Array of attribute names that can be mass assigned
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role'
    ];

    /**
     * @brief Attributes hidden from serialization
     * @details Specifies which attributes should be hidden when the model is
     *          converted to arrays or JSON. This is crucial for security to
     *          prevent sensitive information from being exposed in API responses.
     *          
     *          Hidden attributes:
     *          - password: User's hashed password
     *          - remember_token: Token used for "remember me" functionality
     *
     * @var list<string> $hidden Array of attribute names to hide from serialization
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @brief Defines attribute casting for type conversion
     * @details Specifies how attributes should be automatically cast when accessed
     *          or mutated. This ensures proper data types and automatic conversions.
     *          
     *          Casting rules:
     *          - email_verified_at: Converted to DateTime object for easy manipulation
     *          - password: Automatically hashed when set, never returns plain text
     *
     * @return array<string, string> Array mapping attribute names to cast types
     * 
     * @author Gadiel J. De Jesus Martinez - Triatek
     * @date October 30, 2025
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
