<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Resources\VerDestinatariosDocumentos;

use App\Filament\Cliente\Resources\VerDestinatariosDocumentos\Pages\ListVerDestinatariosDocumentos;
use App\Filament\Cliente\Resources\VerDestinatariosDocumentos\Tables\VerDestinatariosDocumentosTable;
use App\Models\DocumentoCorporativo;
use App\Models\Empresa;
use App\Services\DocumentosCorporativosDestinatariosConsultaService;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

class VerDestinatariosDocumentosResource extends Resource
{
    protected static ?string $model = DocumentoCorporativo::class;

    /**
     * BL: Tenancy del panel (Empresa) no usa el scope global de Filament aquí: `DocumentoCorporativo`
     * no tiene `BelongsTo` directo a `Empresa`. El aislamiento por tenant activo se hace en
     * `getEloquentQuery()` vía `DocumentosCorporativosDestinatariosConsultaService::consultaBase()`:
     * carpeta de documentos corporativos de esa empresa + user de la misma empresa (una query con whereHas).
     */
    protected static bool $isScopedToTenant = false;

    protected static ?string $navigationLabel = 'Ver destinatarios';

    protected static ?string $modelLabel = 'Destinatario de documento';

    protected static ?string $pluralModelLabel = 'Destinatarios de documentos';

    protected static bool $shouldRegisterNavigation = false;

    protected static string|UnitEnum|null $navigationGroup = 'UX prototype';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'nombre_documento';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return VerDestinatariosDocumentosTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVerDestinatariosDocumentos::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $inquilino = Filament::getTenant();
        if (! $inquilino instanceof Empresa) {
            return parent::getEloquentQuery()->whereRaw('1 = 0');
        }

        return DocumentosCorporativosDestinatariosConsultaService::consultaBase($inquilino)
            ->with([
                'user.colaborador',
                'user.empresa',
                'carpeta',
            ]);
    }

    public static function canViewAny(): bool
    {
        return (bool) auth()->user()?->can('ViewAny:DocumentoCorporativo');
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canView(Model $record): bool
    {
        return (bool) auth()->user()?->can('View:DocumentoCorporativo');
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canForceDelete(Model $record): bool
    {
        return false;
    }

    public static function canForceDeleteAny(): bool
    {
        return false;
    }

    public static function canRestore(Model $record): bool
    {
        return false;
    }

    public static function canRestoreAny(): bool
    {
        return false;
    }

    public static function canReplicate(Model $record): bool
    {
        return false;
    }

    public static function canReorder(): bool
    {
        return false;
    }
}
