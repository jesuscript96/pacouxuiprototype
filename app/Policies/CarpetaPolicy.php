<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Carpeta;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CarpetaPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Carpeta');
    }

    public function view(AuthUser $authUser, Carpeta $carpeta): bool
    {
        return $authUser->can('View:Carpeta');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Carpeta');
    }

    public function update(AuthUser $authUser, Carpeta $carpeta): bool
    {
        return $authUser->can('Update:Carpeta');
    }

    public function delete(AuthUser $authUser, Carpeta $carpeta): bool
    {
        return $authUser->can('Delete:Carpeta');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:Carpeta');
    }

    public function restore(AuthUser $authUser, Carpeta $carpeta): bool
    {
        return $authUser->can('Restore:Carpeta');
    }

    public function forceDelete(AuthUser $authUser, Carpeta $carpeta): bool
    {
        return $authUser->can('ForceDelete:Carpeta');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Carpeta');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Carpeta');
    }

    public function replicate(AuthUser $authUser, Carpeta $carpeta): bool
    {
        return $authUser->can('Replicate:Carpeta');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Carpeta');
    }
}
