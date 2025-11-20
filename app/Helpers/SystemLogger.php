<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * SystemLogger Helper
 * 
 * Focused logging system for important actions only:
 * - LOGIN/LOGOUT: User authentication
 * - ADD/EDIT/DELETE: Data modifications
 * - ERROR: System errors
 */
class SystemLogger
{
    /**
     * Log types - Only important actions
     */
    const LOGIN = 'LOGIN';
    const LOGOUT = 'LOGOUT';
    const ADD = 'ADD';
    const EDIT = 'EDIT';
    const DELETE = 'DELETE';
    const ERROR = 'ERROR';

    /**
     * Add a system log entry
     * 
     * @param string $action Action type (LOGIN, LOGOUT, ADD, EDIT, DELETE, ERROR)
     * @param string $comment Description of the action
     * @param string|null $user User who performed the action
     * @param array $context Additional context data
     */
    public static function log(string $action, string $comment, ?string $user = null, array $context = [])
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
            'action' => $action,
            'comment' => $comment,
            'user' => $user,
            'ip' => request()->ip(),
            'context' => $context
        ];

        // Log to Laravel log file
        $logMessage = sprintf(
            '[%s] %s: %s (User: %s, IP: %s)',
            $logEntry['timestamp'],
            $action,
            $comment,
            $user,
            $logEntry['ip']
        );

        // Choose appropriate log level based on action
        if ($action === self::ERROR) {
            Log::error($logMessage, $context);
        } else {
            Log::info($logMessage, $context);
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
    public static function logLoginAttempt(string $email, bool $success, array $context = [])
    {
        if ($success) {
            self::log(self::LOGIN, "User logged in successfully", $email, $context);
        } else {
            // Failed login includes reason if available
            $reason = $context['reason'] ?? 'Invalid credentials';
            self::log(self::ERROR, "Failed login attempt: {$reason}", $email, $context);
        }
    }

    /**
     * Log user logout
     */
    public static function logLogout(string $email)
    {
        self::log(self::LOGOUT, "User logged out", $email);
    }

    /**
     * Log data addition
     */
    public static function logAdd(string $entity, $entityId = null, array $context = [])
    {
        $comment = "Added {$entity}";
        if ($entityId) {
            $comment .= " (ID: {$entityId})";
        }
        self::log(self::ADD, $comment, null, $context);
    }

    /**
     * Log data modification
     */
    public static function logEdit(string $entity, $entityId = null, array $context = [])
    {
        $comment = "Edited {$entity}";
        if ($entityId) {
            $comment .= " (ID: {$entityId})";
        }
        self::log(self::EDIT, $comment, null, $context);
    }

    /**
     * Log data deletion
     */
    public static function logDelete(string $entity, $entityId = null, array $context = [])
    {
        $comment = "Deleted {$entity}";
        if ($entityId) {
            $comment .= " (ID: {$entityId})";
        }
        self::log(self::DELETE, $comment, null, $context);
    }

    /**
     * Log error
     */
    public static function logError(string $message, array $context = [])
    {
        self::log(self::ERROR, $message, null, $context);
    }

    /**
     * Log informational message
     */
    public static function logInfo(string $message, array $context = [])
    {
        // Use general log method for info messages
        self::log('INFO', $message, null, $context);
    }

    /**
     * Log warning message
     */
    public static function logWarning(string $message, array $context = [])
    {
        // Use general log method for warning messages
        self::log('WARNING', $message, null, $context);
    }
}
