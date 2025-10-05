<?php

namespace Ymsoft\FilamentTablePresets\Tests\Models;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Throwable;
use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;
use Ymsoft\FilamentTablePresets\Tests\TestCase;

class FilamentTablePresetTogglePublicTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        FilamentTablePreset::polymorphicUserRelationship(false);
    }

    /**
     * @throws Throwable
     */
    private function makePublicPreset(User $owner, string $resourceClass, string $name = 'public'): FilamentTablePreset
    {
        $preset = new FilamentTablePreset;
        $preset->fill([
            'name' => $name,
            'panel' => 'admin',
            'resource_class' => $resourceClass,
        ]);
        $preset->public = true;

        $owner->createTablePreset($preset);

        return $preset;
    }

    /**
     * @throws Throwable
     */
    public function test_owner_toggle_public_to_private_detaches_other_users(): void
    {
        $resourceClass = 'Workbenches\App\Filament\Resources\ProductResource';
        $owner = User::factory()->create();
        $other = User::factory()->create();

        $preset = $this->makePublicPreset($owner, $resourceClass, 'shared-preset');

        $other->attachTablePreset($preset);

        $this->assertCount(1, $owner->getResourceFilamentTablePresets($resourceClass));
        $this->assertCount(1, $other->getResourceFilamentTablePresets($resourceClass));

        $preset->togglePublic();
        $preset->refresh();

        $this->assertFalse($preset->public);

        $ownerPresets = $owner->getResourceFilamentTablePresets($resourceClass);
        $this->assertCount(1, $ownerPresets);
        $this->assertSame($preset->id, $ownerPresets->first()->id);

        $otherPresets = $other->getResourceFilamentTablePresets($resourceClass);
        $this->assertCount(0, $otherPresets);
    }
}
