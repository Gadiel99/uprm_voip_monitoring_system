<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Storage Path
    |--------------------------------------------------------------------------
    |
    | Path where database backups will be stored.
    | Default: /var/backups/monitoring
    |
    */
    'path' => env('BACKUP_PATH', '/var/backups/monitoring'),

    /*
    |--------------------------------------------------------------------------
    | Backup Retention
    |--------------------------------------------------------------------------
    |
    | Number of weeks to retain old backups before deletion.
    |
    */
    'retention_weeks' => env('BACKUP_RETENTION_WEEKS', 4),
];
