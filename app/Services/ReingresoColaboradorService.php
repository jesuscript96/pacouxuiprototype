<?php

namespace App\Services;

use App\Models\BajaColaborador;
use App\Models\Colaborador;
use App\Models\Empresa;
use App\Models\ReingresoColaborador;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReingresoColaboradorService
{
    public function __construct(
        protected ColaboradorService $colaboradorService
    ) {}

    /**
     * Procesa el reingreso de un colaborador dado de baja (nueva ficha y, opcionalmente, nuevo usuario).
     *
     * @param  array<string, mixed>  $data
     */
    public function reingresar(BajaColaborador $baja, array $data): ReingresoColaborador
    {
        $this->validarPuedeReingresar($baja);
        $this->validarFechaIngresoVsBaja($baja, $data);

        return DB::transaction(function () use ($baja, $data) {
            $baja->loadMissing(['colaborador', 'empresa']);
            $colaboradorAnterior = $baja->colaborador;
            if ($colaboradorAnterior === null) {
                throw ValidationException::withMessages([
                    'baja' => ['No se encontró la ficha del colaborador asociada a esta baja.'],
                ]);
            }

            $empresa = $baja->empresa ?? Empresa::query()->find($baja->empresa_id);
            if ($empresa === null) {
                throw ValidationException::withMessages([
                    'baja' => ['No se encontró la empresa de la baja.'],
                ]);
            }

            $userAnterior = $colaboradorAnterior->user()->withTrashed()->first();
            $crearUsuario = (bool) ($data['crear_usuario'] ?? true);

            if ($crearUsuario) {
                $payload = $this->construirPayloadCrearColaborador($colaboradorAnterior, $data);
                $nuevoUser = $this->colaboradorService->crearColaborador($payload, $empresa);
                $nuevoUser->loadMissing('colaborador');
                $nuevoColaborador = $nuevoUser->colaborador;
                if ($nuevoColaborador === null) {
                    throw ValidationException::withMessages([
                        'baja' => ['No se pudo crear la ficha del colaborador.'],
                    ]);
                }
                $nuevoUserModel = $nuevoUser;
            } else {
                $nuevoColaborador = $this->crearColaboradorSinUsuario($colaboradorAnterior, $data);
                $nuevoUserModel = null;
            }

            $reingreso = ReingresoColaborador::query()->create([
                'baja_colaborador_id' => $baja->id,
                'colaborador_anterior_id' => $colaboradorAnterior->id,
                'colaborador_nuevo_id' => $nuevoColaborador->id,
                'user_anterior_id' => $userAnterior?->id,
                'user_nuevo_id' => $nuevoUserModel?->id,
                'empresa_id' => $baja->empresa_id,
                'fecha_ingreso_anterior' => $colaboradorAnterior->fecha_ingreso,
                'fecha_ingreso_nuevo' => Carbon::parse($data['fecha_ingreso'])->toDateString(),
                'motivo_reingreso' => $data['motivo_reingreso'] ?? null,
                'comentarios' => $data['comentarios'] ?? null,
                'registrado_por' => auth()->id(),
            ]);

            // BL: la baja deja de listarse como pendiente; el reingreso conserva la trazabilidad.
            $baja->delete();

            return $reingreso->fresh(['colaboradorNuevo']);
        });
    }

    /**
     * @return array<string, mixed>
     */
    public function obtenerDatosParaReingreso(BajaColaborador $baja): array
    {
        $baja->loadMissing('colaborador');
        $colaborador = $baja->colaborador;
        if ($colaborador === null) {
            return [];
        }

        return [
            'nombre' => $colaborador->nombre,
            'apellido_paterno' => $colaborador->apellido_paterno,
            'apellido_materno' => $colaborador->apellido_materno,
            'email' => $colaborador->email,
            'telefono_movil' => $colaborador->telefono_movil,
            'fecha_nacimiento' => $colaborador->fecha_nacimiento?->format('Y-m-d'),
            'genero' => $colaborador->genero,
            'curp' => $colaborador->curp,
            'rfc' => $colaborador->rfc,
            'nss' => $colaborador->nss,
            'estado_civil' => $colaborador->estado_civil,
            'nacionalidad' => $colaborador->nacionalidad,
            'direccion' => $colaborador->direccion,
            'empresa_id' => $colaborador->empresa_id,
            'ubicacion_id' => $colaborador->ubicacion_id,
            'departamento_id' => $colaborador->departamento_id,
            'area_id' => $colaborador->area_id,
            'puesto_id' => $colaborador->puesto_id,
            'region_id' => $colaborador->region_id,
            'centro_pago_id' => $colaborador->centro_pago_id,
            'razon_social_id' => $colaborador->razon_social_id,
            'salario_bruto' => $colaborador->salario_bruto,
            'salario_neto' => $colaborador->salario_neto,
            'salario_diario' => $colaborador->salario_diario,
            'salario_diario_integrado' => $colaborador->salario_diario_integrado,
            'monto_maximo' => $colaborador->monto_maximo,
            'periodicidad_pago' => $colaborador->periodicidad_pago,
            'dia_periodicidad' => $colaborador->dia_periodicidad,
            'fecha_ingreso' => now()->format('Y-m-d'),
            'crear_usuario' => true,
        ];
    }

    protected function validarPuedeReingresar(BajaColaborador $baja): void
    {
        if ($baja->trashed()) {
            throw ValidationException::withMessages([
                'baja' => ['Esta baja ya no está disponible.'],
            ]);
        }

        if (! $baja->estaEjecutada()) {
            throw ValidationException::withMessages([
                'baja' => ['Solo se pueden reingresar colaboradores con baja ejecutada.'],
            ]);
        }

        if ($baja->tieneReingreso()) {
            throw ValidationException::withMessages([
                'baja' => ['Este colaborador ya fue reingresado anteriormente.'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function validarFechaIngresoVsBaja(BajaColaborador $baja, array $data): void
    {
        if (empty($data['fecha_ingreso'])) {
            throw ValidationException::withMessages([
                'fecha_ingreso' => ['La fecha de ingreso es obligatoria.'],
            ]);
        }

        $fechaIngreso = Carbon::parse($data['fecha_ingreso'])->startOfDay();
        $fechaBaja = $baja->fecha_baja->copy()->startOfDay();

        if ($fechaIngreso->lte($fechaBaja)) {
            throw ValidationException::withMessages([
                'fecha_ingreso' => ['La fecha de reingreso debe ser posterior a la fecha de baja.'],
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function construirPayloadCrearColaborador(Colaborador $anterior, array $data): array
    {
        $anterior->loadMissing(['user' => fn ($q) => $q->withTrashed()]);
        $userAnterior = $anterior->user;

        $payload = [];
        if ($userAnterior !== null) {
            $omit = ['id', 'password', 'remember_token', 'workos_id', 'colaborador_id', 'email_verified_at', 'tipo'];
            foreach ((new User)->getFillable() as $field) {
                if (in_array($field, $omit, true)) {
                    continue;
                }
                $v = $userAnterior->getAttribute($field);
                if ($v !== null) {
                    $payload[$field] = $v;
                }
            }
        }

        $pick = function (string $key) use ($anterior, $data): mixed {
            if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                return $data[$key];
            }

            return $anterior->getAttribute($key);
        };

        $numero = $this->generarNumeroColaboradorUnico((int) $anterior->empresa_id);

        $payload = array_merge($payload, [
            'nombre' => $pick('nombre'),
            'apellido_paterno' => $pick('apellido_paterno'),
            'apellido_materno' => $pick('apellido_materno'),
            'email' => $pick('email'),
            'telefono_movil' => $pick('telefono_movil'),
            'fecha_nacimiento' => $pick('fecha_nacimiento'),
            'genero' => $pick('genero'),
            'curp' => $pick('curp'),
            'rfc' => $pick('rfc'),
            'nss' => $pick('nss'),
            'estado_civil' => $pick('estado_civil'),
            'nacionalidad' => $pick('nacionalidad'),
            'direccion' => $pick('direccion'),
            'fecha_ingreso' => $data['fecha_ingreso'],
            'fecha_registro_imss' => $anterior->fecha_registro_imss,
            'numero_colaborador' => $numero,
            'ubicacion_id' => $data['ubicacion_id'] ?? $anterior->ubicacion_id,
            'departamento_id' => $data['departamento_id'] ?? $anterior->departamento_id,
            'area_id' => $data['area_id'] ?? $anterior->area_id,
            'puesto_id' => $data['puesto_id'] ?? $anterior->puesto_id,
            'region_id' => $data['region_id'] ?? $anterior->region_id,
            'centro_pago_id' => $data['centro_pago_id'] ?? $anterior->centro_pago_id,
            'razon_social_id' => $data['razon_social_id'] ?? $anterior->razon_social_id,
            'salario_bruto' => $data['salario_bruto'] ?? $anterior->salario_bruto,
            'salario_neto' => $data['salario_neto'] ?? $anterior->salario_neto,
            'salario_diario' => $data['salario_diario'] ?? $anterior->salario_diario,
            'salario_diario_integrado' => $data['salario_diario_integrado'] ?? $anterior->salario_diario_integrado,
            'monto_maximo' => $data['monto_maximo'] ?? $anterior->monto_maximo,
            'periodicidad_pago' => $data['periodicidad_pago'] ?? $anterior->periodicidad_pago,
            'dia_periodicidad' => $data['dia_periodicidad'] ?? $anterior->dia_periodicidad,
            'dias_vacaciones_legales' => $anterior->dias_vacaciones_anuales,
            'dias_vacaciones_empresa' => $anterior->dias_vacaciones_restantes,
            'hora_entrada' => $anterior->hora_entrada,
            'hora_salida' => $anterior->hora_salida,
            'hora_entrada_comida' => $anterior->hora_entrada_comida,
            'hora_salida_comida' => $anterior->hora_salida_comida,
            'hora_inicio_horas_extra' => $anterior->hora_entrada_extra,
            'hora_fin_horas_extra' => $anterior->hora_salida_extra,
            'comentario_adicional' => $anterior->comentario_adicional,
            'nombre_empresa_pago' => $anterior->nombre_empresa_pago,
            'verificado' => false,
            'verificacion_carga_masiva' => false,
            'tiene_identificacion' => $anterior->tiene_identificacion,
        ]);

        if (! empty($data['password'])) {
            $payload['password'] = $data['password'];
        }

        return $payload;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function crearColaboradorSinUsuario(Colaborador $anterior, array $data): Colaborador
    {
        $fillable = (new Colaborador)->getFillable();
        $attrs = $anterior->only($fillable);

        $overrides = [
            'nombre', 'apellido_paterno', 'apellido_materno', 'email', 'telefono_movil',
            'fecha_nacimiento', 'genero', 'curp', 'rfc', 'nss', 'estado_civil', 'nacionalidad', 'direccion',
            'ubicacion_id', 'departamento_id', 'area_id', 'puesto_id', 'region_id', 'centro_pago_id', 'razon_social_id',
            'salario_bruto', 'salario_neto', 'salario_diario', 'salario_diario_integrado', 'monto_maximo',
            'periodicidad_pago', 'dia_periodicidad',
        ];

        foreach ($overrides as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                $attrs[$key] = $data[$key];
            }
        }

        $attrs['numero_colaborador'] = $this->generarNumeroColaboradorUnico((int) $anterior->empresa_id);
        $attrs['fecha_ingreso'] = $data['fecha_ingreso'];
        $attrs['verificado'] = false;
        $attrs['verificacion_carga_masiva'] = false;

        return Colaborador::query()->create($attrs);
    }

    protected function generarNumeroColaboradorUnico(int $empresaId): string
    {
        do {
            $numero = 'RE-'.strtoupper(Str::random(6));
            $existeColab = Colaborador::query()
                ->withTrashed()
                ->where('empresa_id', $empresaId)
                ->where('numero_colaborador', $numero)
                ->exists();
            $existeUser = User::query()
                ->withTrashed()
                ->where('empresa_id', $empresaId)
                ->where('numero_colaborador', $numero)
                ->exists();
        } while ($existeColab || $existeUser);

        return $numero;
    }
}
