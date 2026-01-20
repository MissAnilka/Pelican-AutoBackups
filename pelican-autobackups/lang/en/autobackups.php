<?php

return [
    // Navigation
    'navigation' => 'Auto Backups',
    
    // Page title and description
    'title' => 'Auto Backups',
    'settings_title' => 'Automatic Backup Settings',
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
    'available_slots' => 'Available',
    'auto_backup_slots' => 'Auto-Backup Needs',
    'no_backup_slots' => 'This server has no backup slots configured.',
    
    // Warnings and errors
    'low_slots_warning' => 'Low backup slots available',
    'low_slots_description' => 'Each automatic backup type requires 3 slots to store the backup history.',
    'not_enough_slots' => 'Not Enough Slots',
    'not_enough_slots_title' => 'Not Enough Backup Slots',
    'not_enough_slots_body' => 'You need :required backup slots for this configuration, but your server only has :available slots.',
    'slots_warning' => 'You need :required slots for auto-backups, but only :available are available.',
    'error' => 'Error',
    'cannot_save' => 'Cannot Save',
    'slots_exceeded' => 'The required backup slots exceed your total available slots.',

    // Actions
    'save' => 'Save Settings',
    'saved' => 'Settings saved successfully!',
    'saved_title' => 'Settings Saved',
    'saved_body' => 'Your automatic backup settings have been saved successfully.',

    // Backup naming
    'backup_names' => [
        'daily' => 'Auto-Daily',
        'weekly' => 'Auto-Weekly', 
        'monthly' => 'Auto-Monthly',
    ],
];
