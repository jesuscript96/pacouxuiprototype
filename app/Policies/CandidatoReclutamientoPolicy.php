<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CandidatoReclutamiento;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CandidatoReclutamientoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CandidatoReclutamiento');
    }

    public function view(AuthUser $authUser, CandidatoReclutamiento $candidatoReclutamiento): bool
    {
        return $authUser->can('View:CandidatoReclutamiento');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:CandidatoReclutamiento');
    }

    public function update(AuthUser $authUser, CandidatoReclutamiento $candidatoReclutamiento): bool
    {
        return $authUser->can('Update:CandidatoReclutamiento');
    }

    public function delete(AuthUser $authUser, CandidatoReclutamiento $candidatoReclutamiento): bool
    {
        return $authUser->can('Delete:CandidatoReclutamiento');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:CandidatoReclutamiento');
    }

    public function restore(AuthUser $authUser, CandidatoReclutamiento $candidatoReclutamiento): bool
    {
        return $authUser->can('Restore:CandidatoReclutamiento');
    }

    public function forceDelete(AuthUser $authUser, CandidatoReclutamiento $candidatoReclutamiento): bool
    {
        return $authUser->can('ForceDelete:CandidatoReclutamiento');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:CandidatoReclutamiento');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:CandidatoReclutamiento');
    }

    public function replicate(AuthUser $authUser, CandidatoReclutamiento $candidatoReclutamiento): bool
    {
        return $authUser->can('Replicate:CandidatoReclutamiento');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:CandidatoReclutamiento');
    }
}
