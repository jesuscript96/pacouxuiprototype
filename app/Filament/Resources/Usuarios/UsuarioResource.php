<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios;

use App\Filament\Resources\Usuarios\Pages\CreateUsuario;
use App\Filament\Resources\Usuarios\Pages\EditUsuario;
use App\Filament\Resources\Usuarios\Pages\ListUsuarios;
use App\Filament\Resources\Usuarios\Schemas\UsuarioForm;
use App\Filament\Resources\Usuarios\Tables\UsuariosTable;
use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class UsuarioResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $recordTitleAttribute = 'email';

    protected static string|UnitEnum|null $navigationGroup = 'Catálogos Admin';

    protected static ?string $navigationLabel = 'Usuarios';

    protected static ?string $modelLabel = 'Usuario';

    protected static ?string $pluralModelLabel = 'Usuarios';

    public static function form(Schema $schema): Schema
    {
        return UsuarioForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UsuariosTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsuarios::route('/'),
            'create' => CreateUsuario::route('/create'),
            'edit' => EditUsuario::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();

        if (! $user instanceof User) {
            return $query;
        }

        if ($user->hasRole(Utils::getSuperAdminName())) {
            return $query;
        }

        $empresaIds = $user->empresa_ids;
        if (empty($empresaIds)) {
            return $query->whereNull('empresa_id');
        }

        return $query->where(function (Builder $q) use ($empresaIds): void {
            $q->whereIn('empresa_id', $empresaIds)
                ->orWhereNull('empresa_id')
                ->orWhereHas('empresas', fn (Builder $q2) => $q2->whereIn('empresas.id', $empresaIds));
        });
    }

    /**
     * @return list<string>
     */
    public static function normalizarTiposDesdeFormulario(mixed $tipo): array
    {
        if (! is_array($tipo)) {
            return [];
        }

        $out = [];
        foreach ($tipo as $value) {
            if ($value === null || $value === '') {
                continue;
            }
            if ($value instanceof \BackedEnum) {
                $value = $value->value;
            }
            $out[] = is_string($value) ? $value : (string) $value;
        }

        return array_values(array_unique($out));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mutateFormDataBeforeCreateForModal(array $data): array
    {
        $data['tipo'] = static::normalizarTiposDesdeFormulario($data['tipo'] ?? null);

        $auth = auth()->user();
        $tipo = $data['tipo'];
        if (
            $auth instanceof User
            && ! $auth->hasRole(Utils::getSuperAdminName())
            && $auth->empresa_id
            && (in_array('cliente', $tipo, true) || in_array('colaborador', $tipo, true))
            && (($data['empresas'] ?? []) === [] || $data['empresas'] === null)
        ) {
            $data['empresas'] = [(int) $auth->empresa_id];
            if (empty($data['empresa_id'])) {
                $data['empresa_id'] = (int) $auth->empresa_id;
            }
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mutateRecordDataBeforeFillForModal(User $record, array $data): array
    {
        $data['roles'] = $record->roles()->pluck('id')->toArray();
        $data['tipo'] = is_array($record->tipo) ? $record->tipo : [];
        $data['empresa_id'] = $record->empresa_id;
        $data['empresas'] = $record->empresas()->get()->pluck('id')->unique()->values()->all();

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function mutateFormDataBeforeSaveForModal(array $data): array
    {
        $data['tipo'] = static::normalizarTiposDesdeFormulario($data['tipo'] ?? null);

        return $data;
    }
}
