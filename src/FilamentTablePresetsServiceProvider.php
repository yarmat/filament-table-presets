<?php

namespace Ymsoft\FilamentTablePresets;

use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Ymsoft\FilamentTablePresets\Livewire\FilamentTablePresets;

class FilamentTablePresetsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('filament-table-presets')
            ->hasViews()
            ->hasTranslations()
            ->hasConfigFile()
            ->hasMigration('create_filament_table_presets_table');
    }

    public function bootingPackage(): void
    {
        Livewire::component('filament-table-presets::livewire.filament-table-presets', FilamentTablePresets::class);
    }
}
