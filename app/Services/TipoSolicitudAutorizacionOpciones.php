<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Jefe;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

/**
 * Opciones de autorizadores alineadas al legacy PACO v2 (RequestsTypeController: getBosses, hasBosses, listados create/edit).
 */
final class TipoSolicitudAutorizacionOpciones
{
    /**
     * Usuarios cuya ficha tiene fila en `jefes` (misma noción que legacy `high_employees` ligados a `bosses`):
     * no todos los colaboradores con área/puesto, solo quienes tienen registro de jerarquía/códigos en BD.
     * Etiqueta: «Nombre Apellido - Área | Puesto» como en edit.blade.php del legacy.
     *
     * @return array<int, string>
     */
    public static function opcionesAutorizadoresPorNombre(int $empresaId): array
    {
        if ($empresaId <= 0) {
            return [];
        }

        $usuarios = User::query()
            ->pertenecenAEmpresa($empresaId)
            ->whereHas('colaborador', function (Builder $q) use ($empresaId): void {
                $q->where('empresa_id', $empresaId)
                    ->whereNotNull('area_id')
                    ->whereNotNull('puesto_id')
                    ->whereHas('jefe');
            })
            ->with(['colaborador.area', 'colaborador.puesto'])
            ->orderBy('name')
            ->orderBy('apellido_paterno')
            ->get();

        $opciones = [];
        foreach ($usuarios as $usuario) {
            $ficha = $usuario->colaborador;
            if ($ficha === null || $ficha->area === null || $ficha->puesto === null) {
                continue;
            }

            $nombre = trim((string) $usuario->name.' '.(string) ($usuario->apellido_paterno ?? ''));
            if ($nombre === '') {
                $nombre = (string) ($ficha->nombre_completo ?? $usuario->email ?? 'Usuario '.$usuario->id);
            }

            $etiqueta = $nombre.' - '.$ficha->area->nombre.' | '.$ficha->puesto->nombre;
            $opciones[(int) $usuario->id] = $etiqueta;
        }

        return $opciones;
    }

    /**
     * Solo niveles 1–4 para los que existe al menos un `jefe` de la empresa con `codigo_nivel_n` no vacío
     * (equivalente legacy `hasBosses` + `whereNotNull('code_'.$i)`).
     *
     * @return array<string, string>
     */
    public static function opcionesNivelesJerarquia(int $empresaId): array
    {
        if ($empresaId <= 0) {
            return [];
        }

        $opciones = [];
        for ($i = 1; $i <= 4; $i++) {
            $columna = self::columnaCodigoNivel($i);
            if ($columna === null) {
                continue;
            }
            if (self::empresaTieneCodigoJefeNivel($empresaId, $columna)) {
                $opciones[(string) $i] = 'Jefe nivel '.$i;
            }
        }

        return $opciones;
    }

    /**
     * @return list<string>
     */
    public static function nivelesJerarquiaPermitidosComoString(int $empresaId): array
    {
        // BL: array_keys puede devolver enteros (1,2) porque PHP normaliza claves numéricas; el formulario/Livewire suele mandar '1','2'. Sin normalizar, in_array(..., true) falla.
        return array_values(array_map(
            static fn (mixed $clave): string => (string) $clave,
            array_keys(self::opcionesNivelesJerarquia($empresaId))
        ));
    }

    /**
     * @return list<int>
     */
    public static function idsAutorizadoresPorNombrePermitidos(int $empresaId): array
    {
        return array_keys(self::opcionesAutorizadoresPorNombre($empresaId));
    }

    private static function columnaCodigoNivel(int $nivel): ?string
    {
        return match ($nivel) {
            1 => 'codigo_nivel_1',
            2 => 'codigo_nivel_2',
            3 => 'codigo_nivel_3',
            4 => 'codigo_nivel_4',
            default => null,
        };
    }

    private static function empresaTieneCodigoJefeNivel(int $empresaId, string $columna): bool
    {
        return Jefe::query()
            ->whereHas('colaborador', fn (Builder $q) => $q->where('empresa_id', $empresaId))
            ->whereNotNull($columna)
            ->where($columna, '!=', '')
            ->exists();
    }
}
