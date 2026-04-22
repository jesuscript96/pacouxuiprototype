<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EstadoAnimoCaracteristica;
use Illuminate\Auth\Access\HandlesAuthorization;

class EstadoAnimoCaracteristicaPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EstadoAnimoCaracteristica');
    }

    public function view(AuthUser $authUser, EstadoAnimoCaracteristica $estadoAnimoCaracteristica): bool
    {
        return $authUser->can('View:EstadoAnimoCaracteristica');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EstadoAnimoCaracteristica');
    }

    public function update(AuthUser $authUser, EstadoAnimoCaracteristica $estadoAnimoCaracteristica): bool
    {
        return $authUser->can('Update:EstadoAnimoCaracteristica');
    }

    public function delete(AuthUser $authUser, EstadoAnimoCaracteristica $estadoAnimoCaracteristica): bool
    {
        return $authUser->can('Delete:EstadoAnimoCaracteristica');
    }

    public function restore(AuthUser $authUser, EstadoAnimoCaracteristica $estadoAnimoCaracteristica): bool
    {
        return $authUser->can('Restore:EstadoAnimoCaracteristica');
    }

    public function forceDelete(AuthUser $authUser, EstadoAnimoCaracteristica $estadoAnimoCaracteristica): bool
    {
        return $authUser->can('ForceDelete:EstadoAnimoCaracteristica');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EstadoAnimoCaracteristica');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EstadoAnimoCaracteristica');
    }

    public function replicate(AuthUser $authUser, EstadoAnimoCaracteristica $estadoAnimoCaracteristica): bool
    {
        return $authUser->can('Replicate:EstadoAnimoCaracteristica');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EstadoAnimoCaracteristica');
    }

}