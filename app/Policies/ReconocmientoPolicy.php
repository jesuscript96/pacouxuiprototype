<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Reconocmiento;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ReconocmientoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Reconocmiento');
    }

    public function view(AuthUser $authUser, Reconocmiento $reconocmiento): bool
    {
        return $authUser->can('View:Reconocmiento');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Reconocmiento');
    }

    public function update(AuthUser $authUser, Reconocmiento $reconocmiento): bool
    {
        return $authUser->can('Update:Reconocmiento');
    }

    public function delete(AuthUser $authUser, Reconocmiento $reconocmiento): bool
    {
        if (! $authUser->can('Delete:Reconocmiento')) {
            return false;
        }

        return ! $reconocmiento->tieneEmpresasAsignadas();
    }

    public function restore(AuthUser $authUser, Reconocmiento $reconocmiento): bool
    {
        return $authUser->can('Restore:Reconocmiento');
    }

    public function forceDelete(AuthUser $authUser, Reconocmiento $reconocmiento): bool
    {
        if (! $authUser->can('ForceDelete:Reconocmiento')) {
            return false;
        }

        return ! $reconocmiento->tieneEmpresasAsignadas();
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Reconocmiento');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Reconocmiento');
    }

    public function replicate(AuthUser $authUser, Reconocmiento $reconocmiento): bool
    {
        return $authUser->can('Replicate:Reconocmiento');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Reconocmiento');
    }
}
