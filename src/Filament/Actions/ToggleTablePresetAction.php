<?php

namespace Ymsoft\FilamentTablePresets\Filament\Actions;

use Filament\Actions\Action;
use Ymsoft\FilamentTablePresets\Livewire\FilamentTablePresets;
use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;

class ToggleTablePresetAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'toggle-table-preset';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->icon(fn (FilamentTablePresets $livewire, FilamentTablePreset $record) => $livewire->isPresetSelected($record) ? 'heroicon-o-minus' : 'heroicon-o-check');

        $this->label(fn (FilamentTablePresets $livewire, FilamentTablePreset $record) => $livewire->isPresetSelected($record) ? __('filament-table-presets::table-preset.disable') : __('filament-table-presets::table-preset.enable'));

        $this->action(function (FilamentTablePreset $record) {
            $this->getLivewire()->dispatch('filament-table-preset-selected', $record);
        });
    }
}
