<?php

namespace Ymsoft\FilamentTablePresets\Filament\Pages;

use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;

interface HasFilamentTablePresets
{
    public function applyDefaultPreset(): void;

    public function retrieveVisiblePresetActions(): array;

    public function getSelectedFilamentTablePreset(): ?FilamentTablePreset;

    public function applyFilamentTablePreset(?FilamentTablePreset $preset = null): void;

    public function getResourceClassName(): string;

    public function handleFilamentTablePresetSelected(FilamentTablePreset $preset): void;

    public function handleFilamentTablePresetSynced(FilamentTablePreset $preset): void;

    public function setTablePresetValues(FilamentTablePreset $preset): void;
}
