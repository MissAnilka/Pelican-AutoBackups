# Pelican Auto Backups

A Pelican Panel plugin that adds automatic backup scheduling with daily, weekly, and monthly options.

## Features

- 📅 **Daily Backups** - Automatic daily backup creation
- 📆 **Weekly Backups** - Choose which day of the week
- 🗓️ **Monthly Backups** - Choose which day of the month
- 🔄 **Smart Retention** - Keeps the last 3 backups per type
- ⚠️ **Slot Warnings** - Alerts when insufficient backup slots
- ⏰ **Configurable Time** - Set your preferred backup time

## Installation

See the [plugin README](pelican-autobackups/README.md) for detailed installation instructions.

## Quick Start

1. Download and import the plugin via Pelican Panel admin
2. Run `php artisan migrate`
3. Navigate to any server and configure automatic backups

## Requirements

- Pelican Panel v1.0.0+
- PHP 8.1+
- Laravel scheduler (cron) configured

## License

MIT License

