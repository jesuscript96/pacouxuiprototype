<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Area;
use App\Models\CentroPago;
use App\Models\Colaborador;
use App\Models\Departamento;
use App\Models\Empresa;
use App\Models\Puesto;
use App\Models\Region;
use App\Models\Ubicacion;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * 100 usuarios con ficha en `colaboradores` (50 por cada una de las 2 primeras empresas por id).
 * 90% tipo colaborador, 10% tipo cliente (5 clientes por empresa). Contraseña: "password".
 * Los clientes se enlazan en `empresa_user`. Catálogos: mismos criterios que EmpresaCatalogosDemostrativosSeeder.
 *
 * Requisitos: 2 empresas; conviene ejecutar antes EmpresaCatalogosDemostrativosSeeder (u otros catálogos por empresa).
 */
class ColaboradoresYUsuariosDemostrativosSeeder extends Seeder
{
    use WithoutModelEvents;

    private const string PASSWORD_PLAINTEXT = 'password';

    private const int TOTAL_POR_EMPRESA = 50;

    private const int CLIENTES_POR_EMPRESA = 5;

    public function run(): void
    {
        $empresas = Empresa::query()->orderBy('id')->limit(2)->get();
        if ($empresas->count() < 2) {
            $this->command?->error('ColaboradoresYUsuariosDemostrativosSeeder: se requieren al menos 2 empresas.');

            return;
        }

        $passwordHash = Hash::make(self::PASSWORD_PLAINTEXT);

        /** @var array<int, array<string, list<int>>> */
        $catalogosPorEid = [];
        foreach ($empresas as $empresa) {
            $eid = (int) $empresa->getKey();
            $catalogosPorEid[$eid] = $this->catalogosParaEmpresa($eid);
        }

        DB::transaction(function () use ($empresas, $passwordHash, $catalogosPorEid): void {
            foreach ($empresas as $empresa) {
                $eid = (int) $empresa->getKey();
                $cats = $catalogosPorEid[$eid];
                for ($i = 0; $i < self::TOTAL_POR_EMPRESA; $i++) {
                    $esCliente = $i < self::CLIENTES_POR_EMPRESA;
                    $this->crearUsuarioYFicha($empresa, $eid, $i, $esCliente, $cats, $passwordHash);
                }
            }
        });

        $this->command?->info('ColaboradoresYUsuariosDemostrativosSeeder: 100 usuarios y 100 colaboradores creados (contraseña: password).');
    }

    /**
     * @return array{
     *     departamento_ids: list<int>,
     *     area_ids: list<int>,
     *     puesto_ids: list<int>,
     *     region_ids: list<int>,
     *     ubicacion_ids: list<int>,
     *     centro_pago_ids: list<int>,
     *     razon_social_ids: list<int>,
     * }
     */
    private function catalogosParaEmpresa(int $empresaId): array
    {
        $empresa = Empresa::query()->find($empresaId);
        $razonIds = $empresa !== null
            ? $empresa->razonesSociales()->orderBy('razones_sociales.id')->pluck('razones_sociales.id')->all()
            : [];

        return [
            'departamento_ids' => Departamento::query()->where('empresa_id', $empresaId)->orderBy('id')->pluck('id')->all(),
            'area_ids' => Area::query()->where('empresa_id', $empresaId)->orderBy('id')->pluck('id')->all(),
            'puesto_ids' => Puesto::query()->where('empresa_id', $empresaId)->orderBy('id')->pluck('id')->all(),
            'region_ids' => Region::query()->where('empresa_id', $empresaId)->orderBy('id')->pluck('id')->all(),
            'ubicacion_ids' => Ubicacion::query()->where('empresa_id', $empresaId)->orderBy('id')->pluck('id')->all(),
            'centro_pago_ids' => CentroPago::query()->where('empresa_id', $empresaId)->orderBy('id')->pluck('id')->all(),
            'razon_social_ids' => $razonIds,
        ];
    }

    /**
     * @param  array<string, list<int>>  $cats
     */
    private function crearUsuarioYFicha(Empresa $empresa, int $empresaId, int $indice, bool $esCliente, array $cats, string $passwordHash): void
    {
        $ubicacionId = $this->elegirId($cats['ubicacion_ids'], $indice);
        $departamentoId = $this->elegirId($cats['departamento_ids'], $indice);
        $areaId = $this->elegirId($cats['area_ids'], $indice);
        $puestoId = $this->elegirId($cats['puesto_ids'], $indice);
        $regionId = $this->elegirId($cats['region_ids'], $indice);
        $centroPagoId = $this->elegirId($cats['centro_pago_ids'], $indice);
        $razonSocialId = $this->elegirId($cats['razon_social_ids'], $indice);

        $periodicidades = ['MENSUAL', 'QUINCENAL', 'SEMANAL'];
        $periodicidad = $periodicidades[$indice % count($periodicidades)];
        $generos = ['M', 'F', 'OTRO'];
        $genero = $generos[$indice % count($generos)];

        $nombre = fake()->firstName();
        $apellidoPaterno = fake()->lastName();
        $apellidoMaterno = fake()->lastName();
        $email = "demo.u{$empresaId}.{$indice}@colaboradores-demostrativos.test";
        $telefonoMovil = $this->telefonoUnico($empresaId, $indice);
        $numeroColaborador = sprintf('DEMO-%d-%04d', $empresaId, $indice);
        $fechaNacimiento = fake()->dateTimeBetween('-55 years', '-20 years')->format('Y-m-d');
        $fechaIngreso = fake()->dateTimeBetween('-8 years', 'now')->format('Y-m-d');
        $curp = strtoupper(Str::random(4).fake()->numerify('######').Str::random(6).fake()->numerify('##'));
        $curp = substr($curp, 0, 18);
        $nss = fake()->numerify('###########');

        $tipo = $esCliente ? ['cliente'] : ['colaborador'];

        $codigoJefe = implode('.', array_filter([
            $ubicacionId,
            $departamentoId,
            $areaId,
            $puestoId,
        ], static fn (mixed $v): bool => $v !== null && $v !== ''));

        $rfc = strtoupper(substr(Str::random(4).fake()->numerify('######').Str::random(3), 0, 13));

        // BL: Tras migración remove_rh_catalog_columns, las FK de catálogo viven en `colaboradores`;
        // en `users` se replica lo laboral/personal común y `codigo_jefe` derivado del catálogo.
        $attrsUsuario = [
            'name' => $nombre,
            'apellido_paterno' => $apellidoPaterno,
            'apellido_materno' => $apellidoMaterno,
            'email' => $email,
            'password' => $passwordHash,
            'telefono_movil' => $telefonoMovil,
            'numero_colaborador' => $numeroColaborador,
            'fecha_nacimiento' => $fechaNacimiento,
            'fecha_ingreso' => $fechaIngreso,
            'fecha_registro_imss' => fake()->optional(0.6)->dateTimeBetween('-8 years', 'now')?->format('Y-m-d'),
            'genero' => $genero,
            'curp' => $curp,
            'rfc' => $rfc,
            'nss' => $nss,
            'estado_civil' => fake()->randomElement(['Soltero', 'Casado', 'Unión libre']),
            'nacionalidad' => 'Mexicana',
            'direccion' => fake()->optional(0.85)->streetAddress(),
            'salario_bruto' => fake()->randomFloat(2, 12000, 65000),
            'salario_neto' => null,
            'salario_diario' => null,
            'salario_diario_integrado' => null,
            'monto_maximo' => null,
            'periodicidad_pago' => $periodicidad,
            'dia_periodicidad' => ($indice % 28) + 1,
            'dias_vacaciones_legales' => 12,
            'dias_vacaciones_empresa' => 12,
            'empresa_id' => $empresaId,
            'tipo' => $tipo,
            'nombre_empresa_pago' => fake()->optional(0.3)->company(),
            'verificado' => fake()->boolean(35),
            'verificacion_carga_masiva' => false,
            'tiene_identificacion' => fake()->boolean(40),
            'codigo_jefe' => $codigoJefe,
        ];

        if (Schema::hasColumn('users', 'ubicacion_id')) {
            $attrsUsuario['ubicacion_id'] = $ubicacionId;
            $attrsUsuario['departamento_id'] = $departamentoId;
            $attrsUsuario['area_id'] = $areaId;
            $attrsUsuario['puesto_id'] = $puestoId;
            $attrsUsuario['region_id'] = $regionId;
            $attrsUsuario['centro_pago_id'] = $centroPagoId;
            $attrsUsuario['razon_social_id'] = $razonSocialId;
        }

        /** @var User $user */
        $user = User::query()->forceCreate($attrsUsuario);

        $attrsFicha = [
            'empresa_id' => $empresaId,
            // 'user_id' => $user->id,
            'nombre' => $nombre,
            'apellido_paterno' => $apellidoPaterno,
            'apellido_materno' => $apellidoMaterno,
            'email' => $email,
            'telefono_movil' => $telefonoMovil,
            'numero_colaborador' => $numeroColaborador,
            'fecha_nacimiento' => $fechaNacimiento,
            'genero' => $genero,
            'curp' => $curp,
            'rfc' => $rfc,
            'nss' => $nss,
            'fecha_ingreso' => $fechaIngreso,
            'fecha_registro_imss' => $attrsUsuario['fecha_registro_imss'],
            'estado_civil' => $attrsUsuario['estado_civil'],
            'nacionalidad' => $attrsUsuario['nacionalidad'],
            'direccion' => $attrsUsuario['direccion'],
            'salario_bruto' => $attrsUsuario['salario_bruto'],
            'salario_neto' => null,
            'salario_diario' => null,
            'salario_diario_integrado' => null,
            'salario_variable' => null,
            'monto_maximo' => null,
            'periodicidad_pago' => $periodicidad,
            'dia_periodicidad' => $attrsUsuario['dia_periodicidad'],
            'dias_vacaciones_anuales' => 12,
            'dias_vacaciones_restantes' => 12,
            'hora_entrada' => null,
            'hora_salida' => null,
            'hora_entrada_comida' => null,
            'hora_salida_comida' => null,
            'hora_entrada_extra' => null,
            'hora_salida_extra' => null,
            'comentario_adicional' => null,
            'codigo_jefe' => $codigoJefe,
            'verificado' => $attrsUsuario['verificado'],
            'verificacion_carga_masiva' => false,
            'tiene_identificacion' => $attrsUsuario['tiene_identificacion'],
            'fecha_verificacion_movil' => null,
            'ubicacion_id' => $ubicacionId,
            'departamento_id' => $departamentoId,
            'area_id' => $areaId,
            'puesto_id' => $puestoId,
            'region_id' => $regionId,
            'centro_pago_id' => $centroPagoId,
            'razon_social_id' => $razonSocialId,
            'nombre_empresa_pago' => $attrsUsuario['nombre_empresa_pago'],
        ];

        $colaborador = Colaborador::query()->create($attrsFicha);

        $user->forceFill(['colaborador_id' => $colaborador->id])->save();

        if ($esCliente) {
            $user->empresas()->syncWithoutDetaching([$empresaId]);
        }
    }

    /**
     * @param  list<int>  $ids
     */
    private function elegirId(array $ids, int $indice): ?int
    {
        if ($ids === []) {
            return null;
        }

        return (int) $ids[$indice % count($ids)];
    }

    private function telefonoUnico(int $empresaId, int $indice): string
    {
        $suffix = str_pad((string) (($empresaId * 100 + $indice) % 100_000_000), 8, '0', STR_PAD_LEFT);

        return '55'.$suffix;
    }
}
