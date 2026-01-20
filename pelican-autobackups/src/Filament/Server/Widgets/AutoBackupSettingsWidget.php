<?php

namespace PelicanAutoBackups\Filament\Server\Widgets;

use App\Models\Backup;
use App\Models\Server;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Schema as DBSchema;
use PelicanAutoBackups\Models\AutoBackupSetting;
use PelicanAutoBackups\Services\AutoBackupService;

class AutoBackupSettingsWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static bool $isLazy = false;

    protected string $view = 'filament.admin.widgets.form-widget';

    protected int|string|array $columnSpan = 'full';

    public ?array $data = [];

    public ?Server $server = null;

    public ?AutoBackupSetting $setting = null;

    public int $totalSlots = 0;
    public int $usedSlots = 0;

    public function mount(): void
    {
        try {
            $this->server = Filament::getTenant();
            
            if (!$this->server || !DBSchema::hasTable('auto_backup_settings')) {
                return;
            }

            $this->setting = AutoBackupSetting::firstOrCreate(
                ['server_id' => $this->server->id],
                [
                    'daily_enabled' => false,
                    'weekly_enabled' => false,
                    'monthly_enabled' => false,
                    'backup_time' => '03:00',
                    'weekly_day' => 0,
                    'monthly_day' => 1,
                ]
            );

            $this->calculateSlotUsage();

            $this->form->fill([
                'daily_enabled' => $this->setting->daily_enabled,
                'weekly_enabled' => $this->setting->weekly_enabled,
                'monthly_enabled' => $this->setting->monthly_enabled,
                'backup_time' => $this->setting->backup_time,
                'weekly_day' => $this->setting->weekly_day,
                'monthly_day' => $this->setting->monthly_day,
            ]);
        } catch (\Throwable $e) {
            // Silently fail
        }
    }

    protected function calculateSlotUsage(): void
    {
        if (!$this->server) {
            return;
        }
        
        $service = app(AutoBackupService::class);
        $this->totalSlots = $service->getTotalSlots($this->server);
        $this->usedSlots = Backup::where('server_id', $this->server->id)->count();
    }

    public function form(Schema $schema): Schema
    {
        $available = $this->totalSlots - $this->usedSlots;
        
        $statusColor = match(true) {
            $available <= 0 => 'danger',
            $available <= 3 => 'warning',
            default => 'success',
        };

        $slotMessage = trans('pelican-autobackups::autobackups.slot_usage', [
            'used' => $this->usedSlots,
            'total' => $this->totalSlots,
            'available' => $available,
        ]);

        if ($available <= 0) {
            $slotMessage .= ' ' . trans('pelican-autobackups::autobackups.no_slots_warning');
        }

        return $schema
            ->statePath('data')
            ->components([
                Section::make(trans('pelican-autobackups::autobackups.title'))
                    ->description(trans('pelican-autobackups::autobackups.description'))
                    ->icon('tabler-clock-play')
                    ->iconColor('primary')
                    ->collapsible()
                    ->collapsed()
                    ->persistCollapsed()
                    ->schema([
                        TextEntry::make('slot_info')
                            ->hiddenLabel()
                            ->state($slotMessage)
                            ->color($statusColor),

                        Toggle::make('daily_enabled')
                            ->label(trans('pelican-autobackups::autobackups.daily_backup'))
                            ->helperText(trans('pelican-autobackups::autobackups.daily_helper'))
                            ->live(),

                        Toggle::make('weekly_enabled')
                            ->label(trans('pelican-autobackups::autobackups.weekly_backup'))
                            ->helperText(trans('pelican-autobackups::autobackups.weekly_helper'))
                            ->live(),

                        Select::make('weekly_day')
                            ->label(trans('pelican-autobackups::autobackups.weekly_day'))
                            ->options([
                                0 => trans('pelican-autobackups::autobackups.days.sunday'),
                                1 => trans('pelican-autobackups::autobackups.days.monday'),
                                2 => trans('pelican-autobackups::autobackups.days.tuesday'),
                                3 => trans('pelican-autobackups::autobackups.days.wednesday'),
                                4 => trans('pelican-autobackups::autobackups.days.thursday'),
                                5 => trans('pelican-autobackups::autobackups.days.friday'),
                                6 => trans('pelican-autobackups::autobackups.days.saturday'),
                            ])
                            ->visible(fn ($get) => $get('weekly_enabled'))
                            ->default(0),

                        Toggle::make('monthly_enabled')
                            ->label(trans('pelican-autobackups::autobackups.monthly_backup'))
                            ->helperText(trans('pelican-autobackups::autobackups.monthly_helper'))
                            ->live(),

                        Select::make('monthly_day')
                            ->label(trans('pelican-autobackups::autobackups.monthly_day'))
                            ->options(array_combine(range(1, 28), range(1, 28)))
                            ->visible(fn ($get) => $get('monthly_enabled'))
                            ->default(1),

                        TimePicker::make('backup_time')
                            ->label(trans('pelican-autobackups::autobackups.backup_time'))
                            ->helperText(trans('pelican-autobackups::autobackups.backup_time_helper'))
                            ->seconds(false)
                            ->default('03:00'),
                    ])
                    ->headerActions([
                        Action::make('save')
                            ->label(trans('pelican-autobackups::autobackups.save'))
                            ->action('save')
                            ->icon('tabler-device-floppy'),
                    ]),
            ]);
    }

    public function save(): void
    {
        try {
            if (!$this->setting) {
                return;
            }

            $data = $this->form->getState();

            $this->setting->update([
                'daily_enabled' => $data['daily_enabled'] ?? false,
                'weekly_enabled' => $data['weekly_enabled'] ?? false,
                'monthly_enabled' => $data['monthly_enabled'] ?? false,
                'backup_time' => $data['backup_time'] ?? '03:00',
                'weekly_day' => $data['weekly_day'] ?? 0,
                'monthly_day' => $data['monthly_day'] ?? 1,
            ]);

            Notification::make()
                ->title(trans('pelican-autobackups::autobackups.saved'))
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title(trans('pelican-autobackups::autobackups.save_error'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public static function canView(): bool
    {
        return DBSchema::hasTable('auto_backup_settings');
    }
}
