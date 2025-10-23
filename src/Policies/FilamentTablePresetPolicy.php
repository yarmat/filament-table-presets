<?php

namespace Ymsoft\FilamentTablePresets\Policies;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Ymsoft\FilamentTablePresets\Models\FilamentTablePreset;

class FilamentTablePresetPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    public function viewAny(Authenticatable $user): bool
    {
        return true;
    }

    public function view(Authenticatable $user, FilamentTablePreset $preset): bool
    {
        return true;
    }

    public function create(Authenticatable $user): true
    {
        return true;
    }

    public function detach(Authenticatable|Model $user, FilamentTablePreset $preset): bool
    {
        return $preset->owner->isNot($user);
    }

    public function update(Authenticatable|Model $user, FilamentTablePreset $preset): bool
    {
        return $preset->owner->is($user);
    }

    public function delete(Authenticatable|Model $user, FilamentTablePreset $preset): bool
    {
        return $preset->owner->is($user);
    }
}
