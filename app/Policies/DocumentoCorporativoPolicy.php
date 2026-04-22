<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\DocumentoCorporativo;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class DocumentoCorporativoPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:DocumentoCorporativo');
    }

    public function view(AuthUser $authUser, DocumentoCorporativo $documentoCorporativo): bool
    {
        return $authUser->can('View:DocumentoCorporativo');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:DocumentoCorporativo');
    }

    public function update(AuthUser $authUser, DocumentoCorporativo $documentoCorporativo): bool
    {
        return $authUser->can('Update:DocumentoCorporativo');
    }

    public function delete(AuthUser $authUser, DocumentoCorporativo $documentoCorporativo): bool
    {
        return $authUser->can('Delete:DocumentoCorporativo');
    }

    public function deleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('DeleteAny:DocumentoCorporativo');
    }

    public function restore(AuthUser $authUser, DocumentoCorporativo $documentoCorporativo): bool
    {
        return $authUser->can('Restore:DocumentoCorporativo');
    }

    public function forceDelete(AuthUser $authUser, DocumentoCorporativo $documentoCorporativo): bool
    {
        return $authUser->can('ForceDelete:DocumentoCorporativo');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:DocumentoCorporativo');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:DocumentoCorporativo');
    }

    public function replicate(AuthUser $authUser, DocumentoCorporativo $documentoCorporativo): bool
    {
        return $authUser->can('Replicate:DocumentoCorporativo');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:DocumentoCorporativo');
    }
}
