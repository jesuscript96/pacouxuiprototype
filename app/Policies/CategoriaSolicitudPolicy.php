<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CategoriaSolicitud;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CategoriaSolicitudPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CategoriaSolicitud');
    }

    public function view(AuthUser $authUser, CategoriaSolicitud $categoriaSolicitud): bool
    {
        return $authUser->can('View:CategoriaSolicitud');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CategoriaSolicitud');
    }

    public function update(AuthUser $authUser, CategoriaSolicitud $categoriaSolicitud): bool
    {
        return $authUser->can('Update:CategoriaSolicitud');
    }

    public function delete(AuthUser $authUser, CategoriaSolicitud $categoriaSolicitud): bool
    {
        return $authUser->can('Delete:CategoriaSolicitud');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CategoriaSolicitud');
    }

    public function restore(AuthUser $authUser, CategoriaSolicitud $categoriaSolicitud): bool
    {
        return $authUser->can('Restore:CategoriaSolicitud');
    }

    public function forceDelete(AuthUser $authUser, CategoriaSolicitud $categoriaSolicitud): bool
    {
        return $authUser->can('ForceDelete:CategoriaSolicitud');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CategoriaSolicitud');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CategoriaSolicitud');
    }

    public function replicate(AuthUser $authUser, CategoriaSolicitud $categoriaSolicitud): bool
    {
        return $authUser->can('Replicate:CategoriaSolicitud');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CategoriaSolicitud');
    }
}
