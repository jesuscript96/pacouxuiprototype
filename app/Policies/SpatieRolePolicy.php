<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\SpatieRole;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class SpatieRolePolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:SpatieRole');
    }

    public function view(AuthUser $authUser, SpatieRole $spatieRole): bool
    {
        return $authUser->can('View:SpatieRole');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:SpatieRole');
    }

    public function update(AuthUser $authUser, SpatieRole $spatieRole): bool
    {
        return $authUser->can('Update:SpatieRole');
    }

    public function delete(AuthUser $authUser, SpatieRole $spatieRole): bool
    {
        return $authUser->can('Delete:SpatieRole');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:SpatieRole');
    }

    public function restore(AuthUser $authUser, SpatieRole $spatieRole): bool
    {
        return $authUser->can('Restore:SpatieRole');
    }

    public function forceDelete(AuthUser $authUser, SpatieRole $spatieRole): bool
    {
        return $authUser->can('ForceDelete:SpatieRole');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:SpatieRole');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:SpatieRole');
    }

    public function replicate(AuthUser $authUser, SpatieRole $spatieRole): bool
    {
        return $authUser->can('Replicate:SpatieRole');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:SpatieRole');
    }
}
