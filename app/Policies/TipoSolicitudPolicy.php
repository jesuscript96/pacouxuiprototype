<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TipoSolicitud;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TipoSolicitudPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TipoSolicitud');
    }

    public function view(AuthUser $authUser, TipoSolicitud $tipoSolicitud): bool
    {
        return $authUser->can('View:TipoSolicitud');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TipoSolicitud');
    }

    public function update(AuthUser $authUser, TipoSolicitud $tipoSolicitud): bool
    {
        return $authUser->can('Update:TipoSolicitud');
    }

    public function delete(AuthUser $authUser, TipoSolicitud $tipoSolicitud): bool
    {
        return $authUser->can('Delete:TipoSolicitud');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TipoSolicitud');
    }

    public function restore(AuthUser $authUser, TipoSolicitud $tipoSolicitud): bool
    {
        return $authUser->can('Restore:TipoSolicitud');
    }

    public function forceDelete(AuthUser $authUser, TipoSolicitud $tipoSolicitud): bool
    {
        return $authUser->can('ForceDelete:TipoSolicitud');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TipoSolicitud');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TipoSolicitud');
    }

    public function replicate(AuthUser $authUser, TipoSolicitud $tipoSolicitud): bool
    {
        return $authUser->can('Replicate:TipoSolicitud');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TipoSolicitud');
    }
}
