<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CentroPago;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CentroPagoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CentroPago');
    }

    public function view(AuthUser $authUser, CentroPago $centroPago): bool
    {
        return $authUser->can('View:CentroPago');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CentroPago');
    }

    public function update(AuthUser $authUser, CentroPago $centroPago): bool
    {
        return $authUser->can('Update:CentroPago');
    }

    public function delete(AuthUser $authUser, CentroPago $centroPago): bool
    {
        return $authUser->can('Delete:CentroPago');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CentroPago');
    }

    public function restore(AuthUser $authUser, CentroPago $centroPago): bool
    {
        return $authUser->can('Restore:CentroPago');
    }

    public function forceDelete(AuthUser $authUser, CentroPago $centroPago): bool
    {
        return $authUser->can('ForceDelete:CentroPago');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CentroPago');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CentroPago');
    }

    public function replicate(AuthUser $authUser, CentroPago $centroPago): bool
    {
        return $authUser->can('Replicate:CentroPago');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CentroPago');
    }
}
