<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\BajaColaborador;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class BajaColaboradorPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:BajaColaborador');
    }

    public function view(AuthUser $authUser, BajaColaborador $bajaColaborador): bool
    {
        return $authUser->can('View:BajaColaborador');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:BajaColaborador');
    }

    public function update(AuthUser $authUser, BajaColaborador $bajaColaborador): bool
    {
        return $authUser->can('Update:BajaColaborador');
    }

    public function delete(AuthUser $authUser, BajaColaborador $bajaColaborador): bool
    {
        return $authUser->can('Delete:BajaColaborador');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:BajaColaborador');
    }

    public function restore(AuthUser $authUser, BajaColaborador $bajaColaborador): bool
    {
        return $authUser->can('Restore:BajaColaborador');
    }

    public function forceDelete(AuthUser $authUser, BajaColaborador $bajaColaborador): bool
    {
        return $authUser->can('ForceDelete:BajaColaborador');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:BajaColaborador');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:BajaColaborador');
    }

    public function replicate(AuthUser $authUser, BajaColaborador $bajaColaborador): bool
    {
        return $authUser->can('Replicate:BajaColaborador');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:BajaColaborador');
    }
}
