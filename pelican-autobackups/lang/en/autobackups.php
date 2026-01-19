<?php

return [
    // Widget title and description
    'title' => 'Automatic Backups',
    'description' => 'Configure automatic backup schedules for your server. Each backup type keeps the last 3 backups.',

    // Backup types
    'daily_backup' => 'Daily Backups',
    'daily_helper' => 'Create a backup every day. Keeps the last 3 daily backups.',
    'weekly_backup' => 'Weekly Backups',
    'weekly_helper' => 'Create a backup once per week. Keeps the last 3 weekly backups.',
    'weekly_day' => 'Day of Week',
    'monthly_backup' => 'Monthly Backups',
    'monthly_helper' => 'Create a backup once per month. Keeps the last 3 monthly backups.',
    'monthly_day' => 'Day of Month',
    
    // Time settings
    'backup_time' => 'Backup Time',
    'backup_time_helper' => 'The time of day when backups will be created (server timezone).',

    // Days of week
    'days' => [
        'sunday' => 'Sunday',
        'monday' => 'Monday',
        'tuesday' => 'Tuesday',
        'wednesday' => 'Wednesday',
        'thursday' => 'Thursday',
        'friday' => 'Friday',
        'saturday' => 'Saturday',
    ],

    // Slot information
    'total_slots' => 'Total Slots',
    'used_slots' => 'Used Slots',
    'available_slots' => 'Available Slots',
    
    // Warnings
    'low_slots_warning' => 'Low backup slots available',
    'low_slots_description' => 'Each automatic backup type requires 3 slots to store the backup history. You may not be able to enable all backup types.',
    'not_enough_slots_title' => 'Not enough backup slots',
    'not_enough_slots_body' => 'You need :required backup slots for this configuration, but your server only has :available slots. Please disable some backup types or request more backup slots from your administrator.',

    // Actions
    'save' => 'Save Settings',
    'saved_title' => 'Settings Saved',
    'saved_body' => 'Your automatic backup settings have been saved successfully.',

    // Backup naming
    'backup_names' => [
        'daily' => '[Auto] Daily - :date',
        'weekly' => '[Auto] Weekly - Week :week :year',
        'monthly' => '[Auto] Monthly - :month :year',
    ],
];
