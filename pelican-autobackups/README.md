# Pelican Auto Backups Plugin

A Pelican Panel plugin that adds automatic backup scheduling functionality to your servers.

## Features

- **Daily Backups**: Automatically create backups every day
- **Weekly Backups**: Automatically create backups on a specific day of the week
- **Monthly Backups**: Automatically create backups on a specific day of the month
- **Smart Retention**: Keeps the last 3 backups for each type (daily, weekly, monthly)
- **Slot Management**: Warns users when they don't have enough backup slots for their desired configuration
- **Configurable Time**: Set the preferred time for backups to run
- **Automatic Cleanup**: Old backups are automatically deleted to stay within limits

## Requirements

- Pelican Panel v1.0.0 or higher
- PHP 8.1 or higher
- Cron job configured for Laravel scheduler

## Installation

1. Download the plugin zip file
2. Navigate to your Pelican Panel admin area
3. Go to **Plugins** section
4. Click **Import** and upload the zip file
5. Run database migrations:
   ```bash
   php artisan migrate
   ```

## Configuration

The plugin works out of the box with sensible defaults. Each backup type requires 3 backup slots to store the backup history.

### Backup Slot Requirements

| Configuration | Required Slots |
|--------------|----------------|
| Daily only | 3 |
| Weekly only | 3 |
| Monthly only | 3 |
| Daily + Weekly | 6 |
| Daily + Monthly | 6 |
| Weekly + Monthly | 6 |
| All three | 9 |

### Backup Naming

Backups are named automatically:
- **Daily**: `[Auto] Daily - 2026-01-19`
- **Weekly**: `[Auto] Weekly - Week 3 2026`
- **Monthly**: `[Auto] Monthly - January 2026`

## Usage

1. Navigate to your server in the Pelican Panel
2. Look for the **Automatic Backups** widget
3. Enable the backup types you want:
   - Toggle **Daily Backups** for daily automatic backups
   - Toggle **Weekly Backups** and select the day of week
   - Toggle **Monthly Backups** and select the day of month
4. Set your preferred backup time
5. Click **Save Settings**

## Cron Job

Make sure your Pelican Panel has the Laravel scheduler running. The plugin automatically schedules the backup check every minute.

The standard Pelican cron job should already handle this:
```bash
* * * * * php /var/www/pelican/artisan schedule:run >> /dev/null 2>&1
```

You can also manually run the backup processor:
```bash
php artisan p:autobackups:process
```

Or for a specific server:
```bash
php artisan p:autobackups:process --server=1
```

## File Structure

```
pelican-autobackups/
├── plugin.json                          # Plugin metadata
├── config/
│   └── autobackups.php                  # Configuration file
├── database/
│   └── migrations/
│       └── 2026_01_19_000000_create_auto_backup_settings_table.php
├── lang/
│   └── en/
│       └── autobackups.php              # English translations
├── resources/
│   └── views/
│       └── widgets/
│           └── auto-backup-widget.blade.php
└── src/
    ├── AutoBackupsPlugin.php            # Main plugin class
    ├── Console/
    │   └── Commands/
    │       └── ProcessAutoBackupsCommand.php
    ├── Filament/
    │   └── Server/
    │       └── Widgets/
    │           └── AutoBackupWidget.php
    ├── Models/
    │   └── AutoBackupSetting.php
    ├── Providers/
    │   └── AutoBackupsServiceProvider.php
    └── Services/
        └── AutoBackupService.php
```

## Support

If you need help with this plugin, visit the [Pelican Discord](https://discord.gg/pelican-panel).

## License

MIT License - feel free to use and modify as needed.

## Author

MissAnilka
