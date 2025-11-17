<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Alert Settings Model
 * 
 * Manages system-wide alert threshold configuration.
 * - lower_threshold: Below this percentage is considered green (healthy)
 * - upper_threshold: Above this percentage is considered red (critical)
 * - Between thresholds is considered yellow (warning)
 * - is_active: Whether alert monitoring is enabled
 */
class AlertSettings extends Model
{
    protected $table = 'alert_settings';

    protected $fillable = [
        'lower_threshold',
        'upper_threshold',
        'is_active',
        'email_notifications_enabled',
        'push_notifications_enabled',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'lower_threshold' => 'integer',
        'upper_threshold' => 'integer',
        'email_notifications_enabled' => 'boolean',
        'push_notifications_enabled' => 'boolean',
    ];

    /**
     * Get the current alert settings (singleton pattern).
     * Creates default if none exist.
     */
    public static function current(): self
    {
        $settings = self::first();
        
        if (!$settings) {
            $settings = self::create([
                'lower_threshold' => 30,
                'upper_threshold' => 70,
                'is_active' => true,
                'email_notifications_enabled' => true,
                'push_notifications_enabled' => false,
            ]);
        }
        
        return $settings;
    }

    /**
     * Determine alert level based on offline percentage.
     * 
     * @param float $offlinePercentage
     * @return string 'green'|'yellow'|'red'
     */
    public function getAlertLevel(float $offlinePercentage): string
    {
        if (!$this->is_active) {
            return 'green'; // No alerts when disabled
        }

        if ($offlinePercentage < $this->lower_threshold) {
            return 'green';
        } elseif ($offlinePercentage > $this->upper_threshold) {
            return 'red';
        } else {
            return 'yellow';
        }
    }

    /**
     * Validation: Ensure lower < upper and both are 0-100.
     */
    public static function rules(): array
    {
        return [
            'lower_threshold' => ['required', 'integer', 'min:0', 'max:100'],
            'upper_threshold' => ['required', 'integer', 'min:0', 'max:100', 'gt:lower_threshold'],
            'is_active' => ['boolean'],
        ];
    }
}
