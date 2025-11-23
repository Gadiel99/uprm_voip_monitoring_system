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
     * @param string|null $user User who performed the action (email for login, or null for auto-detect)
     * @param array $context Additional context data
     */
    public static function log(string $action, string $comment, ?string $user = null, array $context = [])
    {
        // Get user display name if not provided
        $userName = 'System';
        if (!$user && Auth::check()) {
            // Use authenticated user's name
            $userName = Auth::user()->name;
        } elseif ($user) {
            // If email provided, try to find user's name
            $userModel = \App\Models\User::where('email', $user)->first();
            $userName = $userModel ? $userModel->name : $user;
        }

        // Prepare log entry
        $logEntry = [
            'timestamp' => now()->format('Y-m-d H:i:s'),
            'action' => $action,
            'comment' => $comment,
            'user' => $userName,
            'ip' => request()->ip(),
            'context' => $context
        ];

        // Log to Laravel log file
        $logMessage = sprintf(
            '[%s] %s: %s (User: %s, IP: %s)',
            $logEntry['timestamp'],
            $action,
            $comment,
            $userName,
            $logEntry['ip']
        );

        // Choose appropriate log level based on action
        if ($action === self::ERROR) {
            Log::error($logMessage, $context);
        } else {
            Log::info($logMessage, $context);
        }

        // Store in database table
        try {
            \Illuminate\Support\Facades\DB::table('system_logs')->insert([
                'created_at' => now(),
                'action' => $action,
                'comment' => $comment,
                'user' => $userName,
                'ip' => request()->ip(),
                'context' => json_encode($context)
            ]);
        } catch (\Exception $e) {
            // If database insert fails, log error but don't break execution
            Log::error('Failed to insert system log to database: ' . $e->getMessage());
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
        // Get user's name for display
        $userModel = \App\Models\User::where('email', $email)->first();
        $userName = $userModel ? $userModel->name : $email;
        
        if ($success) {
            self::log(self::LOGIN, "{$userName} logged in successfully", $email, $context);
        } else {
            // Failed login includes reason if available
            $reason = $context['reason'] ?? 'Invalid credentials';
            self::log(self::ERROR, "Failed login attempt by {$userName}: {$reason}", $email, $context);
        }
    }

    /**
     * Log user logout
     */
    public static function logLogout(string $email)
    {
        // Get user's name for display
        $userModel = \App\Models\User::where('email', $email)->first();
        $userName = $userModel ? $userModel->name : $email;
        
        self::log(self::LOGOUT, "{$userName} logged out", $email);
    }

    /**
     * Log data addition
     */
    public static function logAdd(string $entity, $entityId = null, array $context = [])
    {
        $comment = "Added {$entity}";
        
        // Try to add entity name if available in context
        $name = $context['name'] ?? null;
        if ($name) {
            $comment .= " '{$name}'";
        }
        
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
        
        // Try to add entity name if available in context
        $name = null;
        if (isset($context['new']['name'])) {
            $name = $context['new']['name'];
        } elseif (isset($context['name'])) {
            $name = $context['name'];
        }
        
        if ($name) {
            $comment .= " '{$name}'";
        }
        
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
        
        // Try to add entity name if available in context
        $name = $context['name'] ?? null;
        if ($name) {
            $comment .= " '{$name}'";
        }
        
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
