<?php

namespace Ymsoft\FilamentTablePresets\Filament\Actions;

use Filament\Actions\Action;
use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;

class SyncTablePresetAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'sync-table-preset';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon('heroicon-o-arrow-path');

        $this->label(__('filament-table-presets::table-preset.sync'));

        $this->authorize('update');

        $this->modalDescription(__('filament-table-presets::table-preset.table_preset_sync_description'));

        $this->requiresConfirmation();

        $this->successNotificationTitle(__('filament-table-presets::table-preset.table_preset_synced'));

        $this->action(function (FilamentTablePreset $record) {
            $this->getLivewire()->dispatch('filament-table-preset-synced', $record);

            $this->success();
        });
    }
}
