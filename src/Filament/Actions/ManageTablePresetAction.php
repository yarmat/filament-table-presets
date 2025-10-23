<?php

namespace Ymsoft\FilamentTablePresets\Filament\Actions;

use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Ymsoft\FilamentTablePresets\Filament\Pages\HasFilamentTablePresets;

class ManageTablePresetAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'manage-table-preset';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-table-presets::table-preset.preset_management'))
            ->icon('heroicon-o-adjustments-horizontal')
            ->modalWidth(Width::SevenExtraLarge)
            /** @phpstan-ignore-next-line  */
            ->modalContent(fn (HasFilamentTablePresets $livewire) => view('filament-table-presets::filament.table-presets-modal', [
                'selectedPreset' => $livewire->getSelectedFilamentTablePreset(),
                'resourceClass' => $livewire->getResourceClassName(),
            ]))
            ->slideOver()
            ->modalSubmitAction(false)
            ->extraModalFooterActions([
                CreateTablePresetAction::make(),
                AttachTablePresetAction::make(),
            ]);
    }
}
