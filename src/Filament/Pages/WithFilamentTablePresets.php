<?php

namespace Ymsoft\FilamentTablePresets\Filament\Pages;

use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Size;
use Livewire\Attributes\On;
use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;

/**
 * @mixin ListRecords
 */
trait WithFilamentTablePresets
{
    public ?FilamentTablePreset $selectedFilamentPreset = null;

    public function applyDefaultPreset(): void
    {
        $defaultPreset = auth()->user()
            ->findDefaultResourceFilamentTablePreset($this->getResourceClassName());

        if ($defaultPreset) {
            $this->applyFilamentTablePreset($defaultPreset);
        }
    }

    public function retrieveVisiblePresetActions(): array
    {
        $visiblePresets = auth()->user()
            ->getVisibleResourceFilamentTablePresets($this->getResourceClassName());

        $actions = [];

        /** @var FilamentTablePreset $preset */
        foreach ($visiblePresets as $preset) {
            $actions[] = Action::make($preset->getKey())
                ->label($preset->name)
                ->tooltip($preset->description)
                ->color(function (HasFilamentTablePresets $livewire) use ($preset) {
                    $activePreset = $livewire->getSelectedFilamentTablePreset();

                    return $activePreset?->getKey() == $preset->getKey() ? 'primary' : 'gray';
                })
                ->action(fn (HasFilamentTablePresets $livewire) => $livewire->handleFilamentTablePresetSelected($preset))
                ->size(Size::ExtraSmall);
        }

        return $actions;
    }

    public function getSelectedFilamentTablePreset(): ?FilamentTablePreset
    {
        return $this->selectedFilamentPreset;
    }

    public function getResourceClassName(): string
    {
        return self::class;
    }

    public function applyFilamentTablePreset(?FilamentTablePreset $preset = null): void
    {
        $this->selectedFilamentPreset = $preset;

        $this->tableSort = $preset?->sort;
        $this->tableSearch = $preset?->search;
        $this->tableFilters = $preset?->filters;
        $this->tableColumns = $preset?->columns ?? [];

        $this->bootedInteractsWithTable();
        $this->resetPage();
        $this->flushCachedTableRecords();
    }

    #[On('filament-table-preset-selected')]
    public function handleFilamentTablePresetSelected(FilamentTablePreset $preset): void
    {
        if ($this->selectedFilamentPreset?->getKey() === $preset->getKey()) {
            $this->applyFilamentTablePreset();
        } else {
            $this->applyFilamentTablePreset($preset);
        }
    }

    #[On('filament-table-preset-synced')]
    public function handleFilamentTablePresetSynced(FilamentTablePreset $preset): void
    {
        $this->setTablePresetValues($preset);
        $preset->save();
    }

    public function setTablePresetValues(FilamentTablePreset $preset): void
    {
        $preset->sort = $this->tableSort;
        $preset->filters = $this->tableFilters;
        $preset->search = $this->tableSearch;
        $preset->columns = $this->tableColumns;
    }
}
