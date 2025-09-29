<?php

namespace Ymsoft\FilamentTablePresets\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Ymsoft\FilamentTablePresets\FilamentTablePresets
 */
class FilamentTablePresets extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Ymsoft\FilamentTablePresets\FilamentTablePresets::class;
    }
}
