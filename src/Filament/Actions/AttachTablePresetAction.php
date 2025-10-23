<?php

namespace Ymsoft\FilamentTablePresets\Filament\Actions;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\Width;
use Ymsoft\FilamentTablePresets\Filament\Pages\HasFilamentTablePresets;
use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;

class AttachTablePresetAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'attach-table-preset';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->modalWidth(Width::Small)
            ->label(__('filament-table-presets::table-preset.attach_preset'))
            ->icon('heroicon-o-link')
            ->schema([
                Select::make('preset_id')
                    ->label(__('filament-table-presets::table-preset.select_preset'))
                    ->options(function (HasFilamentTablePresets $livewire) {
                        $user = auth()->user();
                        $panel = Filament::getCurrentPanel()->getId();
                        $resourceClass = $livewire->getResourceClassName();

                        return FilamentTablePreset::query()
                            ->where('public', true)
                            ->where('panel', $panel)
                            ->where('resource_class', $resourceClass)
                            ->where('owner_id', '!=', $user->getKey())
                            ->whereDoesntHave('users', function ($query) use ($user) {
                                $query->where('user_id', $user->getKey());
                            })
                            ->pluck('name', 'id');
                    })
                    ->searchable()
                    ->required()
                    ->helperText(__('filament-table-presets::table-preset.attach_preset_helper')),
            ])
            ->action(function (HasFilamentTablePresets $livewire, array $data) {
                $user = auth()->user();
                $preset = FilamentTablePreset::query()->findOrFail($data['preset_id']);

                if (! $preset->public) {
                    return;
                }

                $user->attachTablePreset($preset);
            });
    }
}
