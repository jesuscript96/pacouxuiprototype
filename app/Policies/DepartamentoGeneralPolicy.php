<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DepartamentoGeneral;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class DepartamentoGeneralPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DepartamentoGeneral');
    }

    public function view(AuthUser $authUser, DepartamentoGeneral $departamentoGeneral): bool
    {
        return $authUser->can('View:DepartamentoGeneral');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DepartamentoGeneral');
    }

    public function update(AuthUser $authUser, DepartamentoGeneral $departamentoGeneral): bool
    {
        return $authUser->can('Update:DepartamentoGeneral');
    }

    public function delete(AuthUser $authUser, DepartamentoGeneral $departamentoGeneral): bool
    {
        return $authUser->can('Delete:DepartamentoGeneral');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DepartamentoGeneral');
    }

    public function restore(AuthUser $authUser, DepartamentoGeneral $departamentoGeneral): bool
    {
        return $authUser->can('Restore:DepartamentoGeneral');
    }

    public function forceDelete(AuthUser $authUser, DepartamentoGeneral $departamentoGeneral): bool
    {
        return $authUser->can('ForceDelete:DepartamentoGeneral');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DepartamentoGeneral');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DepartamentoGeneral');
    }

    public function replicate(AuthUser $authUser, DepartamentoGeneral $departamentoGeneral): bool
    {
        return $authUser->can('Replicate:DepartamentoGeneral');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DepartamentoGeneral');
    }
}
