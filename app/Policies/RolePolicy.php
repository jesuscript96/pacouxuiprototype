<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SpatieRole;
use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class RolePolicy
{
    use HandlesAuthorization;

    private function isSuperAdmin(?AuthUser $user): bool
    {
        return $user instanceof User && $user->hasRole(Utils::getSuperAdminName());
    }

    public function viewAny(AuthUser $authUser): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('ViewAny:Role');
    }

    public function view(AuthUser $authUser, SpatieRole $role): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('View:Role');
    }

    public function create(AuthUser $authUser): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('Create:Role');
    }

    public function update(AuthUser $authUser, SpatieRole $role): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('Update:Role');
    }

    public function delete(AuthUser $authUser, SpatieRole $role): bool
    {
        if ($role->tieneUsuariosAsignados()) {
            return false;
        }

        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('Delete:Role');
    }

    public function restore(AuthUser $authUser, SpatieRole $role): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('Restore:Role');
    }

    public function forceDelete(AuthUser $authUser, SpatieRole $role): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('ForceDelete:Role');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('ForceDeleteAny:Role');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('RestoreAny:Role');
    }

    public function replicate(AuthUser $authUser, SpatieRole $role): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('Replicate:Role');
    }

    public function reorder(AuthUser $authUser): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('Reorder:Role');
    }
}
