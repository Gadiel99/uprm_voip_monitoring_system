<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * SystemLogger Helper
 * 
 * Comprehensive logging system for all user actions and system events.
 * Logs are stored both in Laravel logs and can be accessed via localStorage in frontend.
 */
class SystemLogger
{
    /**
     * Log types
     */
    const INFO = 'INFO';
    const SUCCESS = 'SUCCESS';
    const WARNING = 'WARNING';
    const ERROR = 'ERROR';

    /**
     * Add a system log entry
     * 
     * @param string $type Log type (INFO, SUCCESS, WARNING, ERROR)
     * @param string $message Log message
     * @param string|null $user User who performed the action
     * @param array $context Additional context data
     */
    public static function log(string $type, string $message, ?string $user = null, array $context = [])
    {
        // Get user if not provided
        if (!$user && Auth::check()) {
            $user = Auth::user()->email;
        } elseif (!$user) {
            $user = 'System';
        }

        // Prepare log entry
        $logEntry = [
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'type' => $type,
            'message' => $message,
            'user' => $user,
            'ip' => request()->ip(),
            'context' => $context
        ];

        // Log to Laravel log file
        $logMessage = sprintf(
            '[%s] %s: %s (User: %s, IP: %s)',
            $logEntry['timestamp'],
            $type,
            $message,
            $user,
            $logEntry['ip']
        );

        switch ($type) {
            case self::ERROR:
                Log::error($logMessage, $context);
                break;
            case self::WARNING:
                Log::warning($logMessage, $context);
                break;
            case self::SUCCESS:
            case self::INFO:
            default:
                Log::info($logMessage, $context);
                break;
        }

        // Store in session for frontend access
        $sessionLogs = session()->get('system_logs', []);
        array_unshift($sessionLogs, $logEntry);
        
        // Keep only last 500 logs
        if (count($sessionLogs) > 500) {
            array_pop($sessionLogs);
        }
        
        session()->put('system_logs', $sessionLogs);
    }

    /**
     * Log user login attempt
     */
    public static function logLoginAttempt(string $email, bool $success)
    {
        if ($success) {
            self::log(self::SUCCESS, "User logged in successfully", $email);
        } else {
            self::log(self::WARNING, "Failed login attempt", $email);
        }
    }

    /**
     * Log user logout
     */
    public static function logLogout(string $email)
    {
        self::log(self::INFO, "User logged out", $email);
    }

    /**
     * Log page access
     */
    public static function logPageAccess(string $page)
    {
        self::log(self::INFO, "Accessed {$page} page");
    }

    /**
     * Log data modification
     */
    public static function logDataChange(string $action, string $entity, $entityId = null)
    {
        $message = "{$action} {$entity}";
        if ($entityId) {
            $message .= " (ID: {$entityId})";
        }
        self::log(self::INFO, $message);
    }

    /**
     * Log admin action
     */
    public static function logAdminAction(string $action, array $context = [])
    {
        self::log(self::INFO, "Admin action: {$action}", null, $context);
    }

    /**
     * Log error
     */
    public static function logError(string $message, array $context = [])
    {
        self::log(self::ERROR, $message, null, $context);
    }

    /**
     * Log search action
     */
    public static function logSearch(string $searchTerm, string $page)
    {
        self::log(self::INFO, "Searched for '{$searchTerm}' on {$page} page");
    }

    /**
     * Log export action
     */
    public static function logExport(string $dataType)
    {
        self::log(self::INFO, "Exported {$dataType} data");
    }

    /**
     * Log configuration change
     */
    public static function logConfigChange(string $setting, $oldValue, $newValue)
    {
        self::log(
            self::WARNING,
            "Configuration changed: {$setting}",
            null,
            ['old_value' => $oldValue, 'new_value' => $newValue]
        );
    }
}
