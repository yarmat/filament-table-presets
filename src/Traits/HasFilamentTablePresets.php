<?php

namespace Ymsoft\FilamentTablePresets\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\DB;
use LogicException;
use Throwable;
use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;

/**
 * @mixin Model
 */
trait HasFilamentTablePresets
{
    public function filamentTablePresets(): BelongsToMany|MorphToMany
    {
        $pivotTable = config('filament-table-presets.pivot_table_name', 'filament_table_preset_user');

        if (FilamentTablePreset::hasPolymorphicUserRelationship()) {
            return $this->morphToMany(FilamentTablePreset::class, 'user', $pivotTable, 'user_id', 'preset_id')
                ->withPivot(['sort', 'default', 'visible']);
        }

        return $this->belongsToMany(FilamentTablePreset::class, $pivotTable, 'user_id', 'preset_id')
            ->withPivot(['sort', 'default', 'visible']);
    }

    public function getResourceFilamentTablePresets(string $resourceClass)
    {
        return $this->filamentTablePresets()
            ->where('resource_class', $resourceClass)
            ->orderByPivot('sort')
            ->get();
    }

    public function findDefaultResourceFilamentTablePreset(string $resourceClass): ?FilamentTablePreset
    {
        /** @var ?FilamentTablePreset $defaultPreset */
        $defaultPreset = $this->filamentTablePresets()
            ->where('resource_class', $resourceClass)
            ->wherePivot('default', true)
            ->first();

        return $defaultPreset;
    }

    /**
     * @throws Throwable
     */
    public function createTablePreset(FilamentTablePreset $preset): void
    {
        DB::transaction(function () use ($preset) {
            $preset->owner()->associate($this);
            $preset->save();

            $this->attachTablePreset($preset, true);

            return $preset;
        });
    }

    /**
     * @throws Throwable
     */
    public function deleteTablePreset(FilamentTablePreset $preset): void
    {
        DB::transaction(function () use ($preset) {
            $this->detachTablePreset($preset);
            $preset->delete();
        });
    }

    /**
     * @throws Throwable
     */
    public function attachTablePreset(FilamentTablePreset $preset, bool $ignorePublic = false): void
    {
        if (! $ignorePublic && ! $preset->public) {
            throw new LogicException('You can only attach public table presets.');
        }

        DB::transaction(function () use ($preset) {
            $last = $this->filamentTablePresets()
                ->where('resource_class', $preset->resource_class)
                ->orderByPivot('sort', 'desc')
                ->first();

            $nextSort = $last ? ((int) $last->pivot->sort + 1) : 1;

            $this->filamentTablePresets()->syncWithoutDetaching([
                $preset->getKey() => [
                    'sort' => $nextSort,
                ],
            ]);
        });

    }

    /**
     * @throws Throwable
     */
    public function detachTablePreset(FilamentTablePreset $preset): void
    {
        DB::transaction(function () use ($preset) {
            $relation = $this->filamentTablePresets();

            $relation->detach($preset->getKey());

            $presets = $this->getResourceFilamentTablePresets($preset->resource_class);

            $sort = 1;
            /** @var FilamentTablePreset $existingPreset */
            foreach ($presets as $existingPreset) {
                $relation->updateExistingPivot($existingPreset->id, ['sort' => $sort]);
                $sort++;
            }
        });
    }

    /**
     * @throws Throwable
     */
    public function toggleDefaultTablePreset(FilamentTablePreset $preset): void
    {
        DB::transaction(function () use ($preset) {
            $relation = $this->filamentTablePresets();

            $current = $relation
                ->wherePivot('preset_id', $preset->getKey())
                ->first();

            $isDefault = (bool) optional($current->pivot)->default;

            $resourcePresetIds = FilamentTablePreset::query()
                ->where('resource_class', $preset->resource_class)
                ->pluck('id');

            $relation->newPivotStatement()
                ->when($relation instanceof MorphToMany, function ($q) use ($relation) {
                    $q->where($relation->getMorphType(), $relation->getMorphClass());
                })
                ->where($relation->getForeignPivotKeyName(), $this->getKey())
                ->whereIn($relation->getRelatedPivotKeyName(), $resourcePresetIds)
                ->update(['default' => false]);

            $relation->updateExistingPivot($preset->getKey(), ['default' => ! $isDefault]);
        });
    }

    /**
     * @throws Throwable
     */
    public function toggleVisibleTablePreset(FilamentTablePreset $preset): void
    {
        DB::transaction(function () use ($preset) {
            $relation = $this->filamentTablePresets();

            $current = $relation
                ->wherePivot('preset_id', $preset->getKey())
                ->first();

            $visible = (bool) optional($current->pivot)->visible;

            $relation->updateExistingPivot($preset->getKey(), ['visible' => ! $visible]);
        });
    }

    /**
     * @throws Throwable
     */
    public function updateTablePresetSort(FilamentTablePreset $preset, int $newSort): void
    {
        DB::transaction(function () use ($preset, $newSort) {
            $relation = $this->filamentTablePresets();

            $presets = $relation
                ->where('resource_class', $preset->resource_class)
                ->orderByPivot('sort')
                ->get();

            $sort = 1;
            /** @var FilamentTablePreset $existingPreset */
            foreach ($presets as $existingPreset) {
                if ($existingPreset->id === $preset->id) {
                    continue;
                }
                if ($sort === $newSort) {
                    $sort++;
                }
                $relation->updateExistingPivot($existingPreset->id, ['sort' => $sort]);
                $sort++;
            }

            $relation->updateExistingPivot($preset->getKey(), ['sort' => $newSort]);
        });
    }
}
