<?php

namespace Ymsoft\FilamentTablePresets\Filament\Actions;

use Filament\Actions\DetachAction;
use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;

class DetachTablePresetAction extends DetachAction
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->authorize('detach');

        $this->action(function () {
            $result = $this->process(static fn (FilamentTablePreset $record): ?bool => auth()->user()->detachTablePreset($record));

            if (! $result) {
                $this->failure();

                return;
            }

            $this->success();
        });
    }
}
