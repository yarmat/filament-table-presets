<?php

namespace Ymsoft\FilamentTablePresets\Models;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use LogicException;
use Throwable;

/**
 * @property string $name
 * @property ?string $description
 * @property ?string $sort
 * @property ?string $search
 * @property ?array $columns
 * @property ?array $filters
 * @property string $panel
 * @property string $resource_class
 * @property bool $public
 * @property mixed $owner_id
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
     * @throws Throwable
     */
    public function togglePublic(): void
    {
        \DB::transaction(function () {
            $wasPublic = $this->public;
            $this->public = ! $this->public;

            if ($wasPublic && ! $this->public) {
                $relation = $this->users();

                $relation->newPivotStatement()
                    ->when(method_exists($relation, 'getMorphType'), function ($q) use ($relation) {
                        /** @phpstan-ignore-next-line */
                        $q->where($relation->getMorphType(), $relation->getMorphClass());
                    })
                    ->where($relation->getForeignPivotKeyName(), $this->getKey())
                    ->where($relation->getRelatedPivotKeyName(), '!=', $this->owner_id)
                    ->delete();

                $this->unsetRelation('users');
            }

            $this->save();
        });
    }

    public function owner(): BelongsTo
    {
        if (static::hasPolymorphicUserRelationship()) {
            return $this->morphTo();
        }

        /** @var ?Authenticatable $authenticatable */
        $authenticatable = app(Authenticatable::class);

        if ($authenticatable) {
            /** @phpstan-ignore-next-line */
            return $this->belongsTo($authenticatable::class);
        }

        $userClass = app()->getNamespace().'Models\\User';

        if (! class_exists($userClass)) {
            throw new LogicException('No ['.$userClass.'] model found. Please bind an authenticatable model to the [Illuminate\\Contracts\\Auth\\Authenticatable] interface in a service provider\'s [register()] method.');
        }

        return $this->belongsTo($userClass);
    }

    public function users(): MorphToMany|BelongsToMany
    {
        $pivotTable = config('filament-table-presets.pivot_table_name', 'filament_table_preset_user');

        /** @var ?Model $authenticatable */
        $authenticatable = app(Authenticatable::class);

        if ($authenticatable) {
            $userClass = $authenticatable::class;
        } else {
            $userClass = app()->getNamespace().'Models\\User';
            if (! class_exists($userClass)) {
                throw new \LogicException(
                    'No ['.$userClass.'] model found. Please bind an authenticatable model to the [Illuminate\\Contracts\\Auth\\Authenticatable] interface.'
                );
            }
        }

        if (static::hasPolymorphicUserRelationship()) {
            return $this->morphedByMany(
                $userClass,
                'user',
                $pivotTable,
                'preset_id',
                'user_id'
            )->withPivot(['sort', 'default', 'visible']);
        }

        return $this->belongsToMany(
            $userClass,
            $pivotTable,
            'preset_id',
            'user_id'
        )->withPivot(['sort', 'default', 'visible']);
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
