<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\ReporteInterno;
use App\Policies\Concerns\HasShieldPolicyHelpers;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class ReporteInternoPolicy
{
    use HandlesAuthorization;
    use HasShieldPolicyHelpers;

    public function viewAny(AuthUser $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('ViewAny:ReporteInterno');
    }

    public function view(AuthUser $user, ReporteInterno $reporteInterno): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('View:ReporteInterno');
    }

    public function create(AuthUser $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('Create:ReporteInterno');
    }

    public function update(AuthUser $user, ReporteInterno $reporteInterno): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('Update:ReporteInterno');
    }

    public function delete(AuthUser $user, ReporteInterno $reporteInterno): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('Delete:ReporteInterno');
    }

    public function restore(AuthUser $user, ReporteInterno $reporteInterno): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('Restore:ReporteInterno');
    }

    public function forceDelete(AuthUser $user, ReporteInterno $reporteInterno): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('ForceDelete:ReporteInterno');
    }

    public function forceDeleteAny(AuthUser $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('ForceDeleteAny:ReporteInterno');
    }

    public function restoreAny(AuthUser $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('RestoreAny:ReporteInterno');
    }

    public function replicate(AuthUser $user, ReporteInterno $reporteInterno): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('Replicate:ReporteInterno');
    }

    public function reorder(AuthUser $user): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        return $user->can('Reorder:ReporteInterno');
    }
}
