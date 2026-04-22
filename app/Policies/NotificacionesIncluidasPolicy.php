<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\NotificacionesIncluidas;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class NotificacionesIncluidasPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:NotificacionesIncluidas');
    }

    public function view(AuthUser $authUser, NotificacionesIncluidas $notificacionesIncluidas): bool
    {
        return $authUser->can('View:NotificacionesIncluidas');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:NotificacionesIncluidas');
    }

    public function update(AuthUser $authUser, NotificacionesIncluidas $notificacionesIncluidas): bool
    {
        return $authUser->can('Update:NotificacionesIncluidas');
    }

    public function delete(AuthUser $authUser, NotificacionesIncluidas $notificacionesIncluidas): bool
    {
        return $authUser->can('Delete:NotificacionesIncluidas')
            && ! $notificacionesIncluidas->tieneEmpresasAsignadas();
    }

    public function restore(AuthUser $authUser, NotificacionesIncluidas $notificacionesIncluidas): bool
    {
        return $authUser->can('Restore:NotificacionesIncluidas');
    }

    public function forceDelete(AuthUser $authUser, NotificacionesIncluidas $notificacionesIncluidas): bool
    {
        return $authUser->can('ForceDelete:NotificacionesIncluidas')
            && ! $notificacionesIncluidas->tieneEmpresasAsignadas();
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:NotificacionesIncluidas');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:NotificacionesIncluidas');
    }

    public function replicate(AuthUser $authUser, NotificacionesIncluidas $notificacionesIncluidas): bool
    {
        return $authUser->can('Replicate:NotificacionesIncluidas');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:NotificacionesIncluidas');
    }
}
