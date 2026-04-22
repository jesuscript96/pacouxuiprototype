<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\TemaVozColaborador;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class TemaVozColaboradorPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:TemaVozColaborador');
    }

    public function view(AuthUser $authUser, TemaVozColaborador $temaVozColaborador): bool
    {
        return $authUser->can('View:TemaVozColaborador');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:TemaVozColaborador');
    }

    public function update(AuthUser $authUser, TemaVozColaborador $temaVozColaborador): bool
    {
        return $authUser->can('Update:TemaVozColaborador');
    }

    public function delete(AuthUser $authUser, TemaVozColaborador $temaVozColaborador): bool
    {
        return $authUser->can('Delete:TemaVozColaborador')
            && ! $temaVozColaborador->tieneEmpresasAsignadas();
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:TemaVozColaborador');
    }

    public function restore(AuthUser $authUser, TemaVozColaborador $temaVozColaborador): bool
    {
        return $authUser->can('Restore:TemaVozColaborador');
    }

    public function forceDelete(AuthUser $authUser, TemaVozColaborador $temaVozColaborador): bool
    {
        return $authUser->can('ForceDelete:TemaVozColaborador')
            && ! $temaVozColaborador->tieneEmpresasAsignadas();
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:TemaVozColaborador');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:TemaVozColaborador');
    }

    public function replicate(AuthUser $authUser, TemaVozColaborador $temaVozColaborador): bool
    {
        return $authUser->can('Replicate:TemaVozColaborador');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:TemaVozColaborador');
    }
}
