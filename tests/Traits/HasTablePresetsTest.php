<?php

namespace Ymsoft\FilamentTablePresets\Tests\Traits;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Throwable;
use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;
use Ymsoft\FilamentTablePresets\Tests\TestCase;

class HasTablePresetsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        FilamentTablePreset::polymorphicUserRelationship(false);
    }

    use DatabaseMigrations;

    /**
     * @throws Throwable
     */
    private function makePreset(User $user, string $resourceClass, string $name): FilamentTablePreset
    {
        $preset = new FilamentTablePreset;
        $preset->fill([
            'name' => $name,
            'panel' => 'admin',
            'resource_class' => $resourceClass,
        ]);

        $user->createTablePreset($preset);

        return $preset;
    }

    /**
     * @throws Throwable
     */
    public function test_create_table_preset_attaches_and_sets_owner(): void
    {
        $user = User::factory()->create();
        $resourceClass = 'Workbenches\App\Filament\Resources\ProductResource';

        $preset = $this->makePreset($user, $resourceClass, 'test');

        $this->assertEquals($user->getKey(), optional($preset->owner)->getKey());
        $this->assertCount(1, $preset->users);

        $presets = $user->getResourceFilamentTablePresets($resourceClass);
        $this->assertCount(1, $presets);
    }

    /**
     * @throws Throwable
     */
    public function test_toggle_visible_table_preset(): void
    {
        $user = User::factory()->create();
        $resourceClass = 'Workbenches\App\Filament\Resources\ProductResource';

        $preset = $this->makePreset($user, $resourceClass, 'test');

        $result = $user->getResourceFilamentTablePresets($resourceClass);
        $this->assertEquals(0, (int) $result[0]->pivot->visible);

        $user->toggleVisibleTablePreset($preset);

        $result = $user->getResourceFilamentTablePresets($resourceClass);
        $this->assertEquals(1, (int) $result[0]->pivot->visible);
    }

    /**
     * @throws Throwable
     */
    public function test_toggle_default_single_preset(): void
    {
        $user = User::factory()->create();
        $resourceClass = 'Workbenches\App\Filament\Resources\ProductResource';

        $preset = $this->makePreset($user, $resourceClass, 'test');

        $this->assertNull($user->findDefaultResourceFilamentTablePreset($resourceClass));

        $user->toggleDefaultTablePreset($preset);
        $this->assertEquals($preset->getKey(), $user->findDefaultResourceFilamentTablePreset($resourceClass)?->getKey());

        $user->toggleDefaultTablePreset($preset);
        $this->assertNull($user->findDefaultResourceFilamentTablePreset($resourceClass));
    }

    /**
     * @throws Throwable
     */
    public function test_toggle_default_only_one_default_per_resource(): void
    {
        $user = User::factory()->create();
        $resourceClass = 'Workbenches\App\Filament\Resources\ProductResource';

        $p1 = $this->makePreset($user, $resourceClass, 'p1');
        $p2 = $this->makePreset($user, $resourceClass, 'p2');

        // Enable default on the first preset
        $user->toggleDefaultTablePreset($p1);
        $this->assertEquals($p1->getKey(), $user->findDefaultResourceFilamentTablePreset($resourceClass)?->getKey());

        // Switch default to the second preset — the first should be cleared
        $user->toggleDefaultTablePreset($p2);
        $this->assertEquals($p2->getKey(), $user->findDefaultResourceFilamentTablePreset($resourceClass)?->getKey());

        // Disable default on the second — there should be no default left
        $user->toggleDefaultTablePreset($p2);
        $this->assertNull($user->findDefaultResourceFilamentTablePreset($resourceClass));
    }

    /**
     * @throws Throwable
     */
    public function test_attach_sets_incremental_sort(): void
    {
        $user = User::factory()->create();
        $resourceClass = 'Workbenches\App\Filament\Resources\ProductResource';

        $p1 = $this->makePreset($user, $resourceClass, 'p1');
        $p2 = $this->makePreset($user, $resourceClass, 'p2');
        $p3 = $this->makePreset($user, $resourceClass, 'p3');

        $result = $user->getResourceFilamentTablePresets($resourceClass);

        $this->assertSame([$p1->id, $p2->id, $p3->id], $result->pluck('id')->all());
        $this->assertSame([1, 2, 3], $result->map(fn ($p) => (int) $p->pivot->sort)->all());
    }

    /**
     * @throws Throwable
     */
    public function test_update_table_preset_sort_move_to_end(): void
    {
        $user = User::factory()->create();
        $resourceClass = 'Workbenches\App\Filament\Resources\ProductResource';

        $p1 = $this->makePreset($user, $resourceClass, 'p1'); // sort 1
        $p2 = $this->makePreset($user, $resourceClass, 'p2'); // sort 2
        $p3 = $this->makePreset($user, $resourceClass, 'p3'); // sort 3

        // Move the first preset to the end (position 3)
        $user->updateTablePresetSort($p1, 3);

        $result = $user->getResourceFilamentTablePresets($resourceClass);
        $this->assertSame([$p2->id, $p3->id, $p1->id], $result->pluck('id')->all());
        $this->assertSame([1, 2, 3], $result->map(fn ($p) => (int) $p->pivot->sort)->all());
    }

    /**
     * @throws Throwable
     */
    public function test_update_table_preset_sort_move_to_start(): void
    {
        $user = User::factory()->create();
        $resourceClass = 'Workbenches\App\Filament\Resources\ProductResource';

        $p1 = $this->makePreset($user, $resourceClass, 'p1'); // sort 1
        $p2 = $this->makePreset($user, $resourceClass, 'p2'); // sort 2
        $p3 = $this->makePreset($user, $resourceClass, 'p3'); // sort 3

        // Move the third preset to the start (position 1)
        $user->updateTablePresetSort($p3, 1);

        $result = $user->getResourceFilamentTablePresets($resourceClass);
        $this->assertSame([$p3->id, $p1->id, $p2->id], $result->pluck('id')->all());
        $this->assertSame([1, 2, 3], $result->map(fn ($p) => (int) $p->pivot->sort)->all());
    }

    /**
     * @throws Throwable
     */
    public function test_update_table_preset_sort_move_to_middle(): void
    {
        $user = User::factory()->create();
        $resourceClass = 'Workbenches\App\Filament\Resources\ProductResource';

        $p1 = $this->makePreset($user, $resourceClass, 'p1'); // sort 1
        $p2 = $this->makePreset($user, $resourceClass, 'p2'); // sort 2
        $p3 = $this->makePreset($user, $resourceClass, 'p3'); // sort 3

        // Move the third preset to position 2
        $user->updateTablePresetSort($p3, 2);

        $result = $user->getResourceFilamentTablePresets($resourceClass);
        $this->assertSame([$p1->id, $p3->id, $p2->id], $result->pluck('id')->all());
        $this->assertSame([1, 2, 3], $result->map(fn ($p) => (int) $p->pivot->sort)->all());
    }

    /**
     * @throws Throwable
     */
    public function test_detach_reindexes_sort(): void
    {
        $user = User::factory()->create();
        $resourceClass = 'Workbenches\App\Filament\Resources\ProductResource';

        $p1 = $this->makePreset($user, $resourceClass, 'p1'); // sort 1
        $p2 = $this->makePreset($user, $resourceClass, 'p2'); // sort 2
        $p3 = $this->makePreset($user, $resourceClass, 'p3'); // sort 3

        // Detach the middle preset and ensure indexes are re-indexed
        $user->detachTablePreset($p2);

        $result = $user->getResourceFilamentTablePresets($resourceClass);
        $this->assertSame([$p1->id, $p3->id], $result->pluck('id')->all());
        $this->assertSame([1, 2], $result->map(fn ($p) => (int) $p->pivot->sort)->all());
    }

    /**
     * @throws Throwable
     */
    public function test_multi_user_independent_visibility_and_default_settings_for_same_preset(): void
    {
        $resourceClass = 'Workbenches\App\Filament\Resources\ProductResource';
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Create preset (owned by user A) and attach to user B as well
        $preset = $this->makePreset($userA, $resourceClass, 'shared');
        $userB->attachTablePreset($preset, true); // ignore public flag

        // Initial state: not default, not visible for both
        $a = $userA->getResourceFilamentTablePresets($resourceClass)[0];
        $b = $userB->getResourceFilamentTablePresets($resourceClass)[0];

        $this->assertEquals(0, (int) $a->pivot->visible);
        $this->assertEquals(0, (int) $b->pivot->visible);
        $this->assertNull($userA->findDefaultResourceFilamentTablePreset($resourceClass));
        $this->assertNull($userB->findDefaultResourceFilamentTablePreset($resourceClass));

        // Change visibility for user A only
        $userA->toggleVisibleTablePreset($preset);

        $a = $userA->getResourceFilamentTablePresets($resourceClass)[0];
        $b = $userB->getResourceFilamentTablePresets($resourceClass)[0];
        $this->assertEquals(1, (int) $a->pivot->visible, 'User A visibility should be toggled');
        $this->assertEquals(0, (int) $b->pivot->visible, 'User B visibility should remain unchanged');

        // Set default for user A, should not affect user B
        $userA->toggleDefaultTablePreset($preset);
        $this->assertEquals($preset->getKey(), $userA->findDefaultResourceFilamentTablePreset($resourceClass)?->getKey());
        $this->assertNull($userB->findDefaultResourceFilamentTablePreset($resourceClass));

        // Now set and then unset default for user B, user A should remain default
        $userB->toggleDefaultTablePreset($preset);
        $this->assertEquals($preset->getKey(), $userB->findDefaultResourceFilamentTablePreset($resourceClass)?->getKey());

        $userB->toggleDefaultTablePreset($preset);
        $this->assertNull($userB->findDefaultResourceFilamentTablePreset($resourceClass));
        $this->assertEquals($preset->getKey(), $userA->findDefaultResourceFilamentTablePreset($resourceClass)?->getKey());
    }

    /**
     * @throws Throwable
     */
    public function test_multi_user_sort_independence_with_shared_presets(): void
    {
        $resourceClass = 'Workbenches\App\Filament\Resources\ProductResource';
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        // Create three presets owned by user A
        $p1 = $this->makePreset($userA, $resourceClass, 'p1'); // A sort: 1
        $p2 = $this->makePreset($userA, $resourceClass, 'p2'); // A sort: 2
        $p3 = $this->makePreset($userA, $resourceClass, 'p3'); // A sort: 3

        // Attach same presets to user B (independent pivot rows)
        $userB->attachTablePreset($p1, true);
        $userB->attachTablePreset($p2, true);
        $userB->attachTablePreset($p3, true);

        // Move p3 to the start for user A
        $userA->updateTablePresetSort($p3, 1);

        $orderA = $userA->getResourceFilamentTablePresets($resourceClass);
        $orderB = $userB->getResourceFilamentTablePresets($resourceClass);

        $this->assertSame([$p3->id, $p1->id, $p2->id], $orderA->pluck('id')->all());
        $this->assertSame([1, 2, 3], $orderA->map(fn ($p) => (int) $p->pivot->sort)->all(), 'User A should be re-ordered');

        $this->assertSame([$p1->id, $p2->id, $p3->id], $orderB->pluck('id')->all());
        $this->assertSame([1, 2, 3], $orderB->map(fn ($p) => (int) $p->pivot->sort)->all(), 'User B should be unchanged');

        // Now move p1 to the end for user B
        $userB->updateTablePresetSort($p1, 3);

        $orderA2 = $userA->getResourceFilamentTablePresets($resourceClass);
        $orderB2 = $userB->getResourceFilamentTablePresets($resourceClass);

        $this->assertSame([$p3->id, $p1->id, $p2->id], $orderA2->pluck('id')->all(), 'User A should remain unchanged');
        $this->assertSame([1, 2, 3], $orderA2->map(fn ($p) => (int) $p->pivot->sort)->all());

        $this->assertSame([$p2->id, $p3->id, $p1->id], $orderB2->pluck('id')->all(), 'User B should be re-ordered');
        $this->assertSame([1, 2, 3], $orderB2->map(fn ($p) => (int) $p->pivot->sort)->all());
    }

    /**
     * @throws Throwable
     */
    public function test_multi_user_detach_does_not_affect_other_user(): void
    {
        $resourceClass = 'Workbenches\App\Filament\Resources\ProductResource';
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $p1 = $this->makePreset($userA, $resourceClass, 'p1');
        $p2 = $this->makePreset($userA, $resourceClass, 'p2');
        $p3 = $this->makePreset($userA, $resourceClass, 'p3');

        // Attach user B to the same presets
        $userB->attachTablePreset($p1, true);
        $userB->attachTablePreset($p2, true);
        $userB->attachTablePreset($p3, true);

        // Detach p2 for user A only
        $userA->detachTablePreset($p2);

        // A should have p1, p3 with reindexed sort 1,2
        $resultA = $userA->getResourceFilamentTablePresets($resourceClass);
        $this->assertSame([$p1->id, $p3->id], $resultA->pluck('id')->all());
        $this->assertSame([1, 2], $resultA->map(fn ($p) => (int) $p->pivot->sort)->all());

        // B should still have p1, p2, p3 with sort 1,2,3
        $resultB = $userB->getResourceFilamentTablePresets($resourceClass);
        $this->assertSame([$p1->id, $p2->id, $p3->id], $resultB->pluck('id')->all());
        $this->assertSame([1, 2, 3], $resultB->map(fn ($p) => (int) $p->pivot->sort)->all());
    }
}
