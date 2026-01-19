<?php

namespace PelicanAutoBackups\Filament\Server\Widgets;

use App\Models\Server;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\HtmlString;
use PelicanAutoBackups\Models\AutoBackupSetting;
use PelicanAutoBackups\Services\AutoBackupService;

class AutoBackupWidget extends Widget implements HasForms
{
    use InteractsWithForms;

    protected static string $view = 'pelican-autobackups::widgets.auto-backup-widget';

    protected int | string | array $columnSpan = 'full';

    public ?array $data = [];

    public ?Server $server = null;

    public ?AutoBackupSetting $setting = null;

    public int $totalSlots = 0;
    public int $usedSlots = 0;
    public int $autoBackupSlots = 0;

    public static function canView(): bool
    {
        // Only show on server panel and when table exists
        try {
            if (!Schema::hasTable('auto_backup_settings')) {
                return false;
            }
            
            $panel = filament();
            if (!$panel || $panel->getId() !== 'server') {
                return false;
            }
            
            return $panel->getTenant() !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function mount(): void
    {
        try {
            $this->server = filament()->getTenant();
            
            if (!$this->server) {
                return;
            }

            // Check if table exists before querying
            if (!Schema::hasTable('auto_backup_settings')) {
                return;
            }

            $this->setting = AutoBackupSetting::firstOrCreate(
                ['server_id' => $this->server->id],
                [
                    'daily_enabled' => false,
                    'weekly_enabled' => false,
                    'monthly_enabled' => false,
                    'backup_time' => '03:00:00',
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
            // Silently fail if there's any database issue
            return;
        }
    }

    protected function calculateSlotUsage(): void
    {
        $service = app(AutoBackupService::class);
        
        $this->totalSlots = $service->getTotalSlots($this->server);
        $this->usedSlots = \App\Models\Backup::where('server_id', $this->server->id)->count();
        $this->autoBackupSlots = $this->setting->required_slots;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(trans('pelican-autobackups::autobackups.title'))
                    ->description(trans('pelican-autobackups::autobackups.description'))
                    ->icon('tabler-clock-play')
                    ->schema([
                        Placeholder::make('slot_info')
                            ->label('')
                            ->content(fn () => $this->getSlotInfoContent()),

                        Toggle::make('daily_enabled')
                            ->label(trans('pelican-autobackups::autobackups.daily_backup'))
                            ->helperText(trans('pelican-autobackups::autobackups.daily_helper'))
                            ->inline(false)
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->validateSlots()),

                        Toggle::make('weekly_enabled')
                            ->label(trans('pelican-autobackups::autobackups.weekly_backup'))
                            ->helperText(trans('pelican-autobackups::autobackups.weekly_helper'))
                            ->inline(false)
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->validateSlots()),

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
                            ->inline(false)
                            ->reactive()
                            ->afterStateUpdated(fn () => $this->validateSlots()),

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
                    ->footerActions([
                        \Filament\Forms\Components\Actions\Action::make('save')
                            ->label(trans('pelican-autobackups::autobackups.save'))
                            ->action('save')
                            ->icon('tabler-device-floppy'),
                    ]),
            ])
            ->statePath('data');
    }

    protected function getSlotInfoContent(): HtmlString
    {
        $available = $this->totalSlots - $this->usedSlots;
        
        $statusClass = match(true) {
            $available <= 0 => 'text-danger-500',
            $available <= 3 => 'text-warning-500',
            default => 'text-success-500',
        };

        $html = '<div class="rounded-lg bg-gray-100 dark:bg-gray-800 p-4 mb-4">';
        $html .= '<div class="grid grid-cols-3 gap-4 text-center">';
        $html .= '<div>';
        $html .= '<div class="text-2xl font-bold">' . $this->totalSlots . '</div>';
        $html .= '<div class="text-sm text-gray-500">' . trans('pelican-autobackups::autobackups.total_slots') . '</div>';
        $html .= '</div>';
        $html .= '<div>';
        $html .= '<div class="text-2xl font-bold">' . $this->usedSlots . '</div>';
        $html .= '<div class="text-sm text-gray-500">' . trans('pelican-autobackups::autobackups.used_slots') . '</div>';
        $html .= '</div>';
        $html .= '<div>';
        $html .= '<div class="text-2xl font-bold ' . $statusClass . '">' . $available . '</div>';
        $html .= '<div class="text-sm text-gray-500">' . trans('pelican-autobackups::autobackups.available_slots') . '</div>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '</div>';

        // Warning if not enough slots
        if ($available < 3) {
            $html .= '<div class="rounded-lg bg-warning-100 dark:bg-warning-900/20 border border-warning-300 dark:border-warning-700 p-4 mb-4">';
            $html .= '<div class="flex items-center gap-2 text-warning-700 dark:text-warning-400">';
            $html .= '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 9v4m0 4h.01M5.07 19H19a2 2 0 0 0 1.75-2.75L13.75 4a2 2 0 0 0-3.5 0L3.25 16.25A2 2 0 0 0 5.07 19z"/></svg>';
            $html .= '<span class="font-medium">' . trans('pelican-autobackups::autobackups.low_slots_warning') . '</span>';
            $html .= '</div>';
            $html .= '<p class="text-sm mt-2 text-warning-600 dark:text-warning-500">' . trans('pelican-autobackups::autobackups.low_slots_description') . '</p>';
            $html .= '</div>';
        }

        return new HtmlString($html);
    }

    public function validateSlots(): void
    {
        $data = $this->form->getState();
        $service = app(AutoBackupService::class);
        
        $requiredSlots = $service->calculateRequiredSlots(
            $data['daily_enabled'] ?? false,
            $data['weekly_enabled'] ?? false,
            $data['monthly_enabled'] ?? false
        );

        if ($requiredSlots > $this->totalSlots) {
            Notification::make()
                ->title(trans('pelican-autobackups::autobackups.not_enough_slots_title'))
                ->body(trans('pelican-autobackups::autobackups.not_enough_slots_body', [
                    'required' => $requiredSlots,
                    'available' => $this->totalSlots,
                ]))
                ->danger()
                ->persistent()
                ->send();
        }
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $service = app(AutoBackupService::class);

        // Validate slot requirements
        $requiredSlots = $service->calculateRequiredSlots(
            $data['daily_enabled'] ?? false,
            $data['weekly_enabled'] ?? false,
            $data['monthly_enabled'] ?? false
        );

        if ($requiredSlots > $this->totalSlots) {
            Notification::make()
                ->title(trans('pelican-autobackups::autobackups.not_enough_slots_title'))
                ->body(trans('pelican-autobackups::autobackups.not_enough_slots_body', [
                    'required' => $requiredSlots,
                    'available' => $this->totalSlots,
                ]))
                ->danger()
                ->send();
            return;
        }

        $this->setting->update([
            'daily_enabled' => $data['daily_enabled'] ?? false,
            'weekly_enabled' => $data['weekly_enabled'] ?? false,
            'monthly_enabled' => $data['monthly_enabled'] ?? false,
            'backup_time' => $data['backup_time'] ?? '03:00:00',
            'weekly_day' => $data['weekly_day'] ?? 0,
            'monthly_day' => $data['monthly_day'] ?? 1,
        ]);

        $this->calculateSlotUsage();

        Notification::make()
            ->title(trans('pelican-autobackups::autobackups.saved_title'))
            ->body(trans('pelican-autobackups::autobackups.saved_body'))
            ->success()
            ->send();
    }
}
