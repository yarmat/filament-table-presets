<?php

namespace Ymsoft\FilamentTablePresets\Filament\Actions;

use Filament\Actions\DeleteAction;
use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;

class DeleteTablePresetAction extends DeleteAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->authorize('delete');

        $this->action(function () {
            $result = $this->process(static fn (FilamentTablePreset $record): ?bool => auth()->user()->deleteTablePreset($record));

            if (! $result) {
                $this->failure();

                return;
            }

            $this->success();
        });
    }
}
