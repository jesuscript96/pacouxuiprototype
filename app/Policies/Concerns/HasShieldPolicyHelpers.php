<?php

namespace App\Policies\Concerns;

use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Foundation\Auth\User as AuthUser;

trait HasShieldPolicyHelpers
{
    protected function isSuperAdmin(?AuthUser $user): bool
    {
        return $user instanceof User && $user->hasRole(Utils::getSuperAdminName());
    }

    protected function usuario(AuthUser $user): ?User
    {
        return $user instanceof User ? $user : null;
    }

    /**
     * Solo su propia empresa (o super_admin). Considera empresa_id y membresía en empresa_user.
     */
    protected function canAccessEmpresa(AuthUser $user, int $empresaId): bool
    {
        if ($this->isSuperAdmin($user)) {
            return true;
        }

        $u = $this->usuario($user);

        return $u && $u->perteneceAEmpresa($empresaId);
    }
}
