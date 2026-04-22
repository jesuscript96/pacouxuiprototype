<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\EstadoAnimoAfeccion;
use Illuminate\Auth\Access\HandlesAuthorization;

class EstadoAnimoAfeccionPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:EstadoAnimoAfeccion');
    }

    public function view(AuthUser $authUser, EstadoAnimoAfeccion $estadoAnimoAfeccion): bool
    {
        return $authUser->can('View:EstadoAnimoAfeccion');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:EstadoAnimoAfeccion');
    }

    public function update(AuthUser $authUser, EstadoAnimoAfeccion $estadoAnimoAfeccion): bool
    {
        return $authUser->can('Update:EstadoAnimoAfeccion');
    }

    public function delete(AuthUser $authUser, EstadoAnimoAfeccion $estadoAnimoAfeccion): bool
    {
        return $authUser->can('Delete:EstadoAnimoAfeccion');
    }

    public function restore(AuthUser $authUser, EstadoAnimoAfeccion $estadoAnimoAfeccion): bool
    {
        return $authUser->can('Restore:EstadoAnimoAfeccion');
    }

    public function forceDelete(AuthUser $authUser, EstadoAnimoAfeccion $estadoAnimoAfeccion): bool
    {
        return $authUser->can('ForceDelete:EstadoAnimoAfeccion');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:EstadoAnimoAfeccion');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:EstadoAnimoAfeccion');
    }

    public function replicate(AuthUser $authUser, EstadoAnimoAfeccion $estadoAnimoAfeccion): bool
    {
        return $authUser->can('Replicate:EstadoAnimoAfeccion');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:EstadoAnimoAfeccion');
    }

}