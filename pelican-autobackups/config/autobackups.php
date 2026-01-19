<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backups to Keep
    |--------------------------------------------------------------------------
    |
    | The number of backups to keep for each backup type (daily, weekly, monthly).
    | Older backups beyond this limit will be automatically deleted.
    |
    */
    'backups_to_keep' => 3,

    /*
    |--------------------------------------------------------------------------
    | Default Backup Time
    |--------------------------------------------------------------------------
    |
    | The default time of day when automatic backups will be created.
    | Format: H:i:s (24-hour format)
    |
    */
    'default_backup_time' => '03:00:00',

    /*
    |--------------------------------------------------------------------------
    | Default Weekly Day
    |--------------------------------------------------------------------------
    |
    | The default day of the week for weekly backups.
    | 0 = Sunday, 1 = Monday, ..., 6 = Saturday
    |
    */
    'default_weekly_day' => 0,

    /*
    |--------------------------------------------------------------------------
    | Default Monthly Day
    |--------------------------------------------------------------------------
    |
    | The default day of the month for monthly backups.
    | Valid values: 1-28 (to avoid issues with months having fewer days)
    |
    */
    'default_monthly_day' => 1,
];
