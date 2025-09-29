<?php

namespace Ymsoft\FilamentTablePresets\Commands;

use Illuminate\Console\Command;

class FilamentTablePresetsCommand extends Command
{
    public $signature = 'filament-table-presets';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
