<?php

namespace Ymsoft\FilamentTablePresets;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Ymsoft\FilamentTablePresets\Commands\FilamentTablePresetsCommand;

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
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_filament_table_presets_table')
            ->hasCommand(FilamentTablePresetsCommand::class);
    }
}
