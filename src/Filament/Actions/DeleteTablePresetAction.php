<?php

namespace Ymsoft\FilamentTablePresets\Filament\Actions;

use Filament\Actions\DeleteAction;
use Ymsoft\FilamentTablePresets\Livewire\FilamentTablePresets;
use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;

class DeleteTablePresetAction extends DeleteAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->authorize('delete');

        $this->action(function () {
            $result = $this->process(static function (FilamentTablePreset $record, FilamentTablePresets $livewire): bool|null|string {
                if ($livewire->selectedPreset?->getKey() == $record->getKey()) {
                    return 'active';
                }

                return auth()->user()->deleteTablePreset($record);
            });

            if ($result === 'active') {
                $this->failureNotificationTitle(__('filament-table-presets::table-preset.active_preset_cannot_be_deleted'));
                $this->failure();

                return;
            }

            if (! $result) {
                $this->failure();

                return;
            }

            $this->success();
        });
    }
}
