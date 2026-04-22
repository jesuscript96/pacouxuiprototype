<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\FiltroColaborador;
use App\Policies\Concerns\HasShieldPolicyHelpers;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class FiltroColaboradorPolicy
{
    use HandlesAuthorization;
    use HasShieldPolicyHelpers;

    public function viewAny(AuthUser $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('ViewAny:FiltroColaborador');
    }

    public function view(AuthUser $user, FiltroColaborador $filtroColaborador): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('View:FiltroColaborador');
    }

    public function create(AuthUser $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('Create:FiltroColaborador');
    }

    public function update(AuthUser $user, FiltroColaborador $filtroColaborador): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('Update:FiltroColaborador');
    }

    public function delete(AuthUser $user, FiltroColaborador $filtroColaborador): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('Delete:FiltroColaborador');
    }

    public function restore(AuthUser $user, FiltroColaborador $filtroColaborador): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('Restore:FiltroColaborador');
    }

    public function forceDelete(AuthUser $user, FiltroColaborador $filtroColaborador): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('ForceDelete:FiltroColaborador');
    }

    public function forceDeleteAny(AuthUser $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('ForceDeleteAny:FiltroColaborador');
    }

    public function restoreAny(AuthUser $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('RestoreAny:FiltroColaborador');
    }

    public function replicate(AuthUser $user, FiltroColaborador $filtroColaborador): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('Replicate:FiltroColaborador');
    }

    public function reorder(AuthUser $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('Reorder:FiltroColaborador');
    }
}
