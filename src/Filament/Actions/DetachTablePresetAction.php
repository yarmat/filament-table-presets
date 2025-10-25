<?php

namespace Ymsoft\FilamentTablePresets\Filament\Actions;

use Filament\Actions\DetachAction;
use Ymsoft\FilamentTablePresets\Livewire\FilamentTablePresets;
use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;

class DetachTablePresetAction extends DetachAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->authorize('detach');

        $this->action(function () {
            $result = $this->process(static function (FilamentTablePreset $record, FilamentTablePresets $livewire): ?string {
                if ($livewire->selectedPreset?->getKey() == $record->getKey()) {
                    return 'active';
                }

                auth()->user()->detachTablePreset($record);

                return null;
            });

            if ($result === 'active') {
                $this->failureNotificationTitle(__('filament-table-presets::table-preset.active_preset_cannot_be_detached'));
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
