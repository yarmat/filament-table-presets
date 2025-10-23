<?php

namespace Ymsoft\FilamentTablePresets;

use Filament\Contracts\Plugin;
use Filament\Facades\Filament;
use Filament\Panel;
use Gate;
use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;
use Ymsoft\FilamentTablePresets\Policies\FilamentTablePresetPolicy;

class FilamentTablePresetPlugin implements Plugin
{
    protected bool $hasPolymorphicUserRelationship = false;

    public function getId(): string
    {
        return 'filament-table-presets';
    }

    public function hasPolymorphicUserRelationship(bool $condition = true): void
    {
        $this->hasPolymorphicUserRelationship = $condition;
    }

    public function register(Panel $panel): void {}

    public function boot(Panel $panel): void
    {
        Gate::policy(FilamentTablePreset::class, FilamentTablePresetPolicy::class);

        FilamentTablePreset::polymorphicUserRelationship($this->hasPolymorphicUserRelationship);

        FilamentTablePreset::addGlobalScope('panel', function ($query) {
            $query->where(function ($q) {
                $q->where('panel', Filament::getCurrentPanel()->getId());
            });
        });
    }

    /**
     * Create a new plugin instance.
     */
    public static function make(): static
    {
        return app(static::class);
    }
}
