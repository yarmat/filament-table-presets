<?php

namespace Ymsoft\FilamentTablePresets\Tests\Models;

use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;

class FilamentTablePresetTogglePublicMorphTest extends FilamentTablePresetTogglePublicTest
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
