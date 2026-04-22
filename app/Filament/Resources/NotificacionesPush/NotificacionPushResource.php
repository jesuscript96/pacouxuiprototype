<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificacionesPush;

use App\Filament\Resources\NotificacionesPush\Schemas\NotificacionPushForm;
use App\Filament\Resources\NotificacionesPush\Schemas\NotificacionPushInfolist;
use App\Filament\Resources\NotificacionesPush\Tables\NotificacionesPushTable;
use App\Models\NotificacionPush;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class NotificacionPushResource extends Resource
{
    protected static ?string $model = NotificacionPush::class;

    protected static ?string $slug = 'notificaciones-push';

    protected static string|BackedEnum|null $navigationIcon = null;

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    protected static ?string $navigationLabel = 'Notificaciones Push';

    protected static ?string $modelLabel = 'Notificación Push';

    protected static ?string $pluralModelLabel = 'Notificaciones Push';

    protected static ?int $navigationSort = 10;

    protected static bool $isScopedToTenant = false;

    public static function form(Schema $schema): Schema
    {
        return NotificacionPushForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return NotificacionPushInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificacionesPushTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNotificacionesPush::route('/'),
            'create' => Pages\CreateNotificacionPush::route('/create'),
            'view' => Pages\ViewNotificacionPush::route('/{record}'),
            'edit' => Pages\EditNotificacionPush::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['empresa', 'creadoPor']);
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        if ($user?->hasRole('super_admin')) {
            return true;
        }

        return $user?->can('ViewAny:NotificacionPush') ?? false;
    }

    public static function canView(Model $record): bool
    {
        $user = auth()->user();

        if ($user?->hasRole('super_admin')) {
            return true;
        }

        return $user?->can('View:NotificacionPush') ?? false;
    }

    public static function canCreate(): bool
    {
        $user = auth()->user();

        if ($user?->hasRole('super_admin')) {
            return true;
        }

        return $user?->can('Create:NotificacionPush') ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        $user = auth()->user();

        if ($user?->hasRole('super_admin')) {
            return true;
        }

        if (! $record instanceof NotificacionPush) {
            return false;
        }

        if (! $record->esEditable()) {
            return false;
        }

        return $user?->can('Update:NotificacionPush') ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        $user = auth()->user();

        if ($user?->hasRole('super_admin')) {
            return true;
        }

        if (! $record instanceof NotificacionPush) {
            return false;
        }

        if (! $record->esCancelable()) {
            return false;
        }

        return $user?->can('Delete:NotificacionPush') ?? false;
    }

    public static function canDeleteAny(): bool
    {
        $user = auth()->user();

        if ($user?->hasRole('super_admin')) {
            return true;
        }

        return $user?->can('DeleteAny:NotificacionPush') ?? false;
    }

    public static function canForceDelete(Model $record): bool
    {
        $user = auth()->user();

        if ($user?->hasRole('super_admin')) {
            return true;
        }

        return $user?->can('ForceDelete:NotificacionPush') ?? false;
    }

    public static function canForceDeleteAny(): bool
    {
        $user = auth()->user();

        if ($user?->hasRole('super_admin')) {
            return true;
        }

        return $user?->can('ForceDeleteAny:NotificacionPush') ?? false;
    }

    public static function canRestore(Model $record): bool
    {
        $user = auth()->user();

        if ($user?->hasRole('super_admin')) {
            return true;
        }

        return $user?->can('Restore:NotificacionPush') ?? false;
    }

    public static function canRestoreAny(): bool
    {
        $user = auth()->user();

        if ($user?->hasRole('super_admin')) {
            return true;
        }

        return $user?->can('RestoreAny:NotificacionPush') ?? false;
    }

    public static function canReplicate(Model $record): bool
    {
        $user = auth()->user();

        if ($user?->hasRole('super_admin')) {
            return true;
        }

        return $user?->can('Replicate:NotificacionPush') ?? false;
    }

    public static function canReorder(): bool
    {
        $user = auth()->user();

        if ($user?->hasRole('super_admin')) {
            return true;
        }

        return $user?->can('Reorder:NotificacionPush') ?? false;
    }
}
