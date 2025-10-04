<?php

namespace Ymsoft\FilamentTablePresets\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LogicException;

/**
 * @property bool $public
 * @property mixed $user_id
 */
class FilamentTablePreset extends Model
{
    protected $fillable = [
        'name',
        'panel',
        'resource_class',
    ];

    protected $casts = [
        'columns' => 'array',
        'filters' => 'array',
        'public' => 'boolean',
    ];

    protected static bool $hasPolymorphicUserRelationship = false;

    public function getTable(): string
    {
        return config('filament-table-presets.table_name', 'filament_table_presets');
    }

    /**
     * @throws \Throwable
     */
    public function togglePublic(): void
    {
        \DB::transaction(function () {
            $wasPublic = $this->public;
            $this->public = ! $this->public;

            if ($wasPublic && ! $this->public) {
                $ownerId = $this->user_id;

                $this->users()->whereNotIn('user_id', [$ownerId])->detach();
            }

            $this->save();
        });
    }

    public function owner(): BelongsTo
    {
        if (static::hasPolymorphicUserRelationship()) {
            return $this->morphTo('user', 'user_type', 'user_id', 'id');
        }

        /** @var ?Authenticatable $authenticatable */
        $authenticatable = app(Authenticatable::class);

        if ($authenticatable) {
            /** @phpstan-ignore-next-line */
            return $this->belongsTo($authenticatable::class, 'user_id');
        }

        $userClass = app()->getNamespace().'Models\\User';

        if (! class_exists($userClass)) {
            throw new LogicException('No ['.$userClass.'] model found. Please bind an authenticatable model to the [Illuminate\\Contracts\\Auth\\Authenticatable] interface in a service provider\'s [register()] method.');
        }

        return $this->belongsTo($userClass, 'user_id');
    }

    public function users(): MorphToMany|BelongsToMany
    {
        $pivotTable = config('filament-table-presets.pivot_table_name', 'filament_table_preset_user');

        /** @var ?Authenticatable $authenticatable */
        $authenticatable = app(Authenticatable::class);

        if ($authenticatable) {
            $userClass = $authenticatable::class;
        } else {
            $userClass = app()->getNamespace().'Models\\User';

            if (! class_exists($userClass)) {
                throw new LogicException('No ['.$userClass.'] model found. Please bind an authenticatable model to the [Illuminate\\Contracts\\Auth\\Authenticatable] interface in a service provider\'s [register()] method.');
            }
        }

        if (static::hasPolymorphicUserRelationship()) {
            return $this->morphedByMany($userClass, 'user', $pivotTable, 'preset_id', 'user_id');
        }

        return $this->belongsToMany($userClass, $pivotTable, 'preset_id', 'user_id');
    }

    public static function hasPolymorphicUserRelationship(): bool
    {
        return static::$hasPolymorphicUserRelationship;
    }

    public static function polymorphicUserRelationship(bool $condition = true): void
    {
        static::$hasPolymorphicUserRelationship = $condition;
    }
}
