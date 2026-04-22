<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class UsuarioPolicy
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

        return $authUser->can('ViewAny:User') || $authUser->can('ViewAny:Usuario');
    }

    public function view(AuthUser $authUser, User $record): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('View:User') || $authUser->can('View:Usuario');
    }

    public function create(AuthUser $authUser): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('Create:User') || $authUser->can('Create:Usuario');
    }

    public function update(AuthUser $authUser, User $record): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('Update:User') || $authUser->can('Update:Usuario');
    }

    public function delete(AuthUser $authUser, User $record): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('Delete:User') || $authUser->can('Delete:Usuario');
    }

    public function restore(AuthUser $authUser, User $record): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('Restore:User') || $authUser->can('Restore:Usuario');
    }

    public function forceDelete(AuthUser $authUser, User $record): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('ForceDelete:User') || $authUser->can('ForceDelete:Usuario');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('ForceDeleteAny:User') || $authUser->can('ForceDeleteAny:Usuario');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('RestoreAny:User') || $authUser->can('RestoreAny:Usuario');
    }

    public function replicate(AuthUser $authUser, User $record): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('Replicate:User') || $authUser->can('Replicate:Usuario');
    }

    public function reorder(AuthUser $authUser): bool
    {
        if ($this->isSuperAdmin($authUser)) {
            return true;
        }

        return $authUser->can('Reorder:User') || $authUser->can('Reorder:Usuario');
    }
}
