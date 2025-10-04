<?php

namespace Ymsoft\FilamentTablePresets\Tests;

use Orchestra\Testbench\Concerns\WithWorkbench;
use Orchestra\Testbench\TestCase as Orchestra;
use Ymsoft\FilamentTablePresets\FilamentTablePresetsServiceProvider;

class TestCase extends Orchestra
{
    use WithWorkbench;

    //    protected function setUp(): void
    //    {
    //        parent::setUp();
    //
    //        Factory::guessFactoryNamesUsing(
    //            fn (string $modelName) => 'Ymsoft\\FilamentTablePresets\\Database\\Factories\\'.class_basename($modelName).'Factory'
    //        );
    //    }
    //
    protected function getPackageProviders($app): array
    {
        return [
            FilamentTablePresetsServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__.'/../database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
        }
    }
}
