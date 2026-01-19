<?php

namespace PelicanAutoBackups\Services;

use App\Models\Backup;
use App\Models\Server;
use App\Services\Backups\InitiateBackupService;
use App\Services\Backups\DeleteBackupService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PelicanAutoBackups\Models\AutoBackupSetting;

class AutoBackupService
{
    protected const BACKUPS_TO_KEEP = 3;

    protected const BACKUP_NAME_PREFIXES = [
        'daily' => '[Auto] Daily',
        'weekly' => '[Auto] Weekly',
        'monthly' => '[Auto] Monthly',
    ];

    public function __construct(
        protected InitiateBackupService $initiateBackupService,
        protected DeleteBackupService $deleteBackupService
    ) {}

    /**
     * Process all servers that need automatic backups.
     */
    public function processAllBackups(): void
    {
        $settings = AutoBackupSetting::query()
            ->where(function ($query) {
                $query->where('daily_enabled', true)
                    ->orWhere('weekly_enabled', true)
                    ->orWhere('monthly_enabled', true);
            })
            ->with('server')
            ->get();

        foreach ($settings as $setting) {
            if (!$setting->server) {
                continue;
            }

            try {
                $this->processServerBackups($setting);
            } catch (\Exception $e) {
                Log::error('[AutoBackups] Failed to process backups for server ' . $setting->server_id, [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Process backups for a specific server.
     */
    public function processServerBackups(AutoBackupSetting $setting): void
    {
        $now = Carbon::now();
        $server = $setting->server;

        // Check if it's time to run backups (within the backup hour)
        $backupTime = Carbon::parse($setting->backup_time);
        if ($now->hour !== $backupTime->hour) {
            return;
        }

        // Process daily backups
        if ($setting->daily_enabled) {
            $this->processDailyBackup($setting, $server, $now);
        }

        // Process weekly backups
        if ($setting->weekly_enabled) {
            $this->processWeeklyBackup($setting, $server, $now);
        }

        // Process monthly backups
        if ($setting->monthly_enabled) {
            $this->processMonthlyBackup($setting, $server, $now);
        }
    }

    /**
     * Process daily backup for a server.
     */
    protected function processDailyBackup(AutoBackupSetting $setting, Server $server, Carbon $now): void
    {
        // Check if we already created a backup today
        if ($setting->last_daily_backup && $setting->last_daily_backup->isToday()) {
            return;
        }

        $this->createBackup($server, 'daily', $now);
        
        $setting->update(['last_daily_backup' => $now]);
        
        $this->cleanupOldBackups($server, 'daily');
    }

    /**
     * Process weekly backup for a server.
     */
    protected function processWeeklyBackup(AutoBackupSetting $setting, Server $server, Carbon $now): void
    {
        // Check if today is the scheduled day of the week
        if ($now->dayOfWeek !== $setting->weekly_day) {
            return;
        }

        // Check if we already created a backup this week
        if ($setting->last_weekly_backup && $setting->last_weekly_backup->isSameWeek($now)) {
            return;
        }

        $this->createBackup($server, 'weekly', $now);
        
        $setting->update(['last_weekly_backup' => $now]);
        
        $this->cleanupOldBackups($server, 'weekly');
    }

    /**
     * Process monthly backup for a server.
     */
    protected function processMonthlyBackup(AutoBackupSetting $setting, Server $server, Carbon $now): void
    {
        // Check if today is the scheduled day of the month
        if ($now->day !== $setting->monthly_day) {
            return;
        }

        // Check if we already created a backup this month
        if ($setting->last_monthly_backup && $setting->last_monthly_backup->isSameMonth($now)) {
            return;
        }

        $this->createBackup($server, 'monthly', $now);
        
        $setting->update(['last_monthly_backup' => $now]);
        
        $this->cleanupOldBackups($server, 'monthly');
    }

    /**
     * Create a backup with the appropriate naming.
     */
    protected function createBackup(Server $server, string $type, Carbon $date): void
    {
        $prefix = self::BACKUP_NAME_PREFIXES[$type];
        $dateFormat = match($type) {
            'daily' => $date->format('Y-m-d'),
            'weekly' => 'Week ' . $date->weekOfYear . ' ' . $date->format('Y'),
            'monthly' => $date->format('F Y'),
        };
        
        $backupName = "{$prefix} - {$dateFormat}";

        Log::info("[AutoBackups] Creating {$type} backup for server {$server->id}", [
            'name' => $backupName,
        ]);

        try {
            $this->initiateBackupService->handle(
                $server,
                $backupName,
                false // Don't lock the backup
            );
        } catch (\Exception $e) {
            Log::error("[AutoBackups] Failed to create {$type} backup for server {$server->id}", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Clean up old backups of a specific type, keeping only the last 3.
     */
    protected function cleanupOldBackups(Server $server, string $type): void
    {
        $prefix = self::BACKUP_NAME_PREFIXES[$type];
        
        $backups = Backup::query()
            ->where('server_id', $server->id)
            ->where('name', 'like', $prefix . '%')
            ->where('is_locked', false)
            ->orderBy('created_at', 'desc')
            ->get();

        // Keep the first 3 (newest), delete the rest
        $backupsToDelete = $backups->slice(self::BACKUPS_TO_KEEP);

        foreach ($backupsToDelete as $backup) {
            try {
                Log::info("[AutoBackups] Deleting old {$type} backup for server {$server->id}", [
                    'backup_id' => $backup->id,
                    'name' => $backup->name,
                ]);
                
                $this->deleteBackupService->handle($backup);
            } catch (\Exception $e) {
                Log::error("[AutoBackups] Failed to delete old backup {$backup->id}", [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get the count of auto backups for a server by type.
     */
    public function getAutoBackupCount(Server $server, string $type): int
    {
        $prefix = self::BACKUP_NAME_PREFIXES[$type];
        
        return Backup::query()
            ->where('server_id', $server->id)
            ->where('name', 'like', $prefix . '%')
            ->count();
    }

    /**
     * Calculate slots needed for the requested configuration.
     */
    public function calculateRequiredSlots(bool $daily, bool $weekly, bool $monthly): int
    {
        $slots = 0;
        
        if ($daily) $slots += 3;
        if ($weekly) $slots += 3;
        if ($monthly) $slots += 3;
        
        return $slots;
    }

    /**
     * Get available backup slots for a server.
     */
    public function getAvailableSlots(Server $server): int
    {
        $backupLimit = $server->backup_limit ?? 0;
        $currentBackups = Backup::where('server_id', $server->id)->count();
        
        return max(0, $backupLimit - $currentBackups);
    }

    /**
     * Get total backup slots for a server.
     */
    public function getTotalSlots(Server $server): int
    {
        return $server->backup_limit ?? 0;
    }
}
