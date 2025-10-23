<?php

namespace Ymsoft\FilamentTablePresets\Livewire;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Throwable;
use Ymsoft\FilamentTablePresets\Filament\Tables\FilamentTablePresetTable;
use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;

class FilamentTablePresets extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public string $resourceClass;

    public ?FilamentTablePreset $selectedPreset = null;

    public function table(Table $table): Table
    {
        return FilamentTablePresetTable::configure($table)
            ->query(function () {
                return auth()
                    ->user()
                    ->filamentTablePresets()
                    ->orderByPivot('sort')
                    ->where('resource_class', $this->resourceClass);
            });
    }

    public function isPresetSelected(FilamentTablePreset $preset): bool
    {
        return $this->selectedPreset?->getKey() === $preset->getKey();
    }

    /**
     * @throws Throwable
     */
    public function reorderTable(array $order, int|string|null $draggedRecordKey = null): void
    {
        if (! $this->getTable()->isReorderable()) {
            return;
        }

        $user = auth()->user();

        foreach ($order as $newSort => $presetId) {
            /** @var ?FilamentTablePreset $preset */
            $preset = $user->filamentTablePresets()->find($presetId);
            if ($preset) {
                $user->updateTablePresetSort($preset, $newSort + 1);
            }
        }
    }

    public function render(): View|Factory|\Illuminate\View\View
    {
        /** @phpstan-ignore-next-line  */
        return view('filament-table-presets::livewire.filament-table-presets');
    }
}
