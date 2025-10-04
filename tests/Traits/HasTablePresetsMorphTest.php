<?php

namespace Ymsoft\FilamentTablePresets\Tests\Traits;

use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;

class HasTablePresetsMorphTest extends HasTablePresetsTest
{
    protected function getTestSchema(): string
    {
        return 'morph';
    }

    protected function setUp(): void
    {
        parent::setUp();

        FilamentTablePreset::polymorphicUserRelationship();
    }
}
