<?php

namespace Ymsoft\FilamentTablePresets\Filament\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Ymsoft\FilamentTablePresets\Filament\Actions\DeleteTablePresetAction;
use Ymsoft\FilamentTablePresets\Filament\Actions\DetachTablePresetAction;
use Ymsoft\FilamentTablePresets\Filament\Actions\EditTablePresetAction;
use Ymsoft\FilamentTablePresets\Filament\Actions\SyncTablePresetAction;
use Ymsoft\FilamentTablePresets\Filament\Actions\ToggleTablePresetAction;
use Ymsoft\FilamentTablePresets\Livewire\FilamentTablePresets;
use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;

class FilamentTablePresetTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('active')
                    ->getStateUsing(function (FilamentTablePreset $record, FilamentTablePresets $livewire) {
                        return $livewire->isPresetSelected($record);
                    })
                    ->icon(function ($state) {
                        return $state ? 'heroicon-o-check' : '';
                    })
                    ->label(__('filament-table-presets::table-preset.active')),
                TextColumn::make('name')
                    ->label(__('filament-table-presets::table-preset.name'))
                    ->searchable(),
                TextColumn::make('description')
                    ->label(__('filament-table-presets::table-preset.description')),
                ToggleColumn::make('public')
                    ->label(__('filament-table-presets::table-preset.public'))
                    ->updateStateUsing(function ($record) {
                        auth()->user()->togglePublicTablePreset($record);
                    })
                    ->disabled(fn (FilamentTablePreset $record) => ! auth()->user()->can('update', $record)),
                ToggleColumn::make('visible')
                    ->label(__('filament-table-presets::table-preset.visible'))
                    ->updateStateUsing(function ($record) {
                        auth()->user()->toggleVisibleTablePreset($record);
                    }),
                ToggleColumn::make('default')
                    ->label(__('filament-table-presets::table-preset.default'))
                    ->updateStateUsing(function ($record) {
                        auth()->user()->toggleDefaultTablePreset($record);
                    }),
            ])
            ->reorderable(config('filament-table-presets.pivot_table_name').'.sort')
            ->filters([
                TernaryFilter::make('public')
                    ->label(__('filament-table-presets::table-preset.public')),
                TernaryFilter::make('visible')
                    ->label(__('filament-table-presets::table-preset.visible')),

            ])
            ->recordAction('toggle-table-preset')
            ->recordActions([
                ToggleTablePresetAction::make(),
                SyncTablePresetAction::make(),
                EditTablePresetAction::make(),
                DeleteTablePresetAction::make(),
                DetachTablePresetAction::make(),
            ])
            ->queryStringIdentifier('filament-table-presets');
    }
}
