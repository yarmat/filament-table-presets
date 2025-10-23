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

    protected function getPackageProviders($app): array
    {
        return [
            \Livewire\LivewireServiceProvider::class,
            FilamentTablePresetsServiceProvider::class,
        ];
    }

    protected function getTestSchema(): string
    {
        return 'single';
    }

    public function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        $testSchema = $this->getTestSchema();

        if ($testSchema == 'morph') {
            foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__.'/database/migrations') as $migration) {
                (include $migration->getRealPath())->up();
            }
        } else {
            foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__.'/../database/migrations') as $migration) {
                (include $migration->getRealPath())->up();
            }
        }
    }
}
