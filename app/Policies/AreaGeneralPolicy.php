<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\AreaGeneral;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class AreaGeneralPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:AreaGeneral');
    }

    public function view(AuthUser $authUser, AreaGeneral $areaGeneral): bool
    {
        return $authUser->can('View:AreaGeneral');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:AreaGeneral');
    }

    public function update(AuthUser $authUser, AreaGeneral $areaGeneral): bool
    {
        return $authUser->can('Update:AreaGeneral');
    }

    public function delete(AuthUser $authUser, AreaGeneral $areaGeneral): bool
    {
        return $authUser->can('Delete:AreaGeneral');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:AreaGeneral');
    }

    public function restore(AuthUser $authUser, AreaGeneral $areaGeneral): bool
    {
        return $authUser->can('Restore:AreaGeneral');
    }

    public function forceDelete(AuthUser $authUser, AreaGeneral $areaGeneral): bool
    {
        return $authUser->can('ForceDelete:AreaGeneral');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:AreaGeneral');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:AreaGeneral');
    }

    public function replicate(AuthUser $authUser, AreaGeneral $areaGeneral): bool
    {
        return $authUser->can('Replicate:AreaGeneral');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:AreaGeneral');
    }
}
