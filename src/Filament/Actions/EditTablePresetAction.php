<?php

namespace Ymsoft\FilamentTablePresets\Filament\Actions;

use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Width;

class EditTablePresetAction extends EditAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->modalWidth(Width::Small);

        $this->authorize('update');

        $this->schema([
            TextInput::make('name')
                ->label(__('filament-table-presets::table-preset.name'))
                ->required(),
            Textarea::make('description')
                ->label(__('filament-table-presets::table-preset.description')),
        ]);
    }
}
