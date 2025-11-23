<?php

return [
    // Directory where backup ZIP files will be stored
    'path' => env('BACKUP_PATH', storage_path('app/backups')),
];
