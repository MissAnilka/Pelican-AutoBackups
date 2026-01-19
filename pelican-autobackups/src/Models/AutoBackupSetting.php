<?php

namespace PelicanAutoBackups\Models;

use App\Models\Server;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoBackupSetting extends Model
{
    protected $table = 'auto_backup_settings';

    protected $fillable = [
        'server_id',
        'daily_enabled',
        'weekly_enabled',
        'monthly_enabled',
        'backup_time',
        'weekly_day',
        'monthly_day',
        'last_daily_backup',
        'last_weekly_backup',
        'last_monthly_backup',
    ];

    protected $casts = [
        'daily_enabled' => 'boolean',
        'weekly_enabled' => 'boolean',
        'monthly_enabled' => 'boolean',
        'weekly_day' => 'integer',
        'monthly_day' => 'integer',
        'last_daily_backup' => 'datetime',
        'last_weekly_backup' => 'datetime',
        'last_monthly_backup' => 'datetime',
    ];

    /**
     * Get the server that owns the auto backup settings.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Calculate the number of backup slots required based on enabled backup types.
     */
    public function getRequiredSlotsAttribute(): int
    {
        $slots = 0;
        
        if ($this->daily_enabled) {
            $slots += 3; // Keep last 3 daily backups
        }
        
        if ($this->weekly_enabled) {
            $slots += 3; // Keep last 3 weekly backups
        }
        
        if ($this->monthly_enabled) {
            $slots += 3; // Keep last 3 monthly backups
        }
        
        return $slots;
    }

    /**
     * Get the count of enabled backup types.
     */
    public function getEnabledTypesCountAttribute(): int
    {
        $count = 0;
        
        if ($this->daily_enabled) $count++;
        if ($this->weekly_enabled) $count++;
        if ($this->monthly_enabled) $count++;
        
        return $count;
    }

    /**
     * Check if a specific backup type can be enabled based on available slots.
     */
    public function canEnableType(string $type, int $availableSlots): bool
    {
        $currentRequired = $this->required_slots;
        $additionalRequired = 3; // Each type needs 3 slots
        
        // If this type is already enabled, no additional slots needed
        $typeEnabled = match($type) {
            'daily' => $this->daily_enabled,
            'weekly' => $this->weekly_enabled,
            'monthly' => $this->monthly_enabled,
            default => false,
        };
        
        if ($typeEnabled) {
            return true;
        }
        
        return ($currentRequired + $additionalRequired) <= $availableSlots;
    }
}
