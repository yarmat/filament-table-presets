<?php

namespace Ymsoft\FilamentTablePresets\Filament\Actions;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Width;
use Ymsoft\FilamentTablePresets\Filament\Pages\HasFilamentTablePresets;
use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;

class CreateTablePresetAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'create-table-preset';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->modalWidth(Width::Small)
            ->label(__('filament-table-presets::table-preset.create_preset'))
            ->icon('heroicon-o-plus')
            ->schema([
                TextInput::make('name')
                    ->label(__('filament-table-presets::table-preset.name'))
                    ->required(),
                Textarea::make('description')
                    ->label(__('filament-table-presets::table-preset.description')),
            ])
            ->action(function (HasFilamentTablePresets $livewire, array $data) {
                $user = auth()->user();
                $preset = new FilamentTablePreset;
                $preset->name = $data['name'];
                $preset->description = $data['description'];
                $preset->resource_class = self::class;
                $preset->public = false;
                $preset->panel = Filament::getCurrentPanel()->getId();
                $livewire->setTablePresetValues($preset);

                $user->createTablePreset($preset);
            });
    }
}
