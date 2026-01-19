<?php

namespace PelicanAutoBackups\Console\Commands;

use Illuminate\Console\Command;
use PelicanAutoBackups\Services\AutoBackupService;

class ProcessAutoBackupsCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'p:autobackups:process
                            {--server= : Process backups for a specific server ID only}';

    /**
     * The console command description.
     */
    protected $description = 'Process automatic backups for all servers with auto-backup enabled.';

    /**
     * Execute the console command.
     */
    public function handle(AutoBackupService $service): int
    {
        $this->info('Processing automatic backups...');

        $serverId = $this->option('server');

        if ($serverId) {
            $setting = \PelicanAutoBackups\Models\AutoBackupSetting::where('server_id', $serverId)->first();
            
            if (!$setting) {
                $this->error("No auto-backup settings found for server {$serverId}");
                return self::FAILURE;
            }

            if (!$setting->server) {
                $this->error("Server {$serverId} not found");
                return self::FAILURE;
            }

            $this->info("Processing backups for server: {$setting->server->name}");
            
            try {
                $service->processServerBackups($setting);
                $this->info('Backup processing completed for server.');
            } catch (\Exception $e) {
                $this->error("Failed to process backups: {$e->getMessage()}");
                return self::FAILURE;
            }
        } else {
            try {
                $service->processAllBackups();
                $this->info('All automatic backups processed successfully.');
            } catch (\Exception $e) {
                $this->error("Failed to process backups: {$e->getMessage()}");
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }
}
