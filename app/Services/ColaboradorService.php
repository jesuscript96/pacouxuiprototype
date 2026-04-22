<?php

namespace App\Services;

use App\Models\BeneficiarioColaborador;
use App\Models\Colaborador;
use App\Models\CuentaNomina;
use App\Models\Empresa;
use App\Models\User;
use App\Services\Correos\CorreoBienvenidaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ColaboradorService
{
    /**
     * FK de catálogos RH persistidos en `colaboradores` (no en `users`).
     *
     * @var list<string>
     */
    private const CATALOGO_RH_KEYS = [
        'ubicacion_id',
        'departamento_id',
        'area_id',
        'puesto_id',
        'region_id',
        'centro_pago_id',
        'razon_social_id',
    ];

    public function __construct(
        protected HistorialService $historialService,
        protected AsignacionProductosService $asignacionProductosService,
        protected CorreoBienvenidaService $correoBienvenidaService
    ) {}

    /**
     * Crea un usuario colaborador con historiales, beneficiarios, cuenta nómina y productos.
     *
     * @param  array<string, mixed>  $data  Acepta `name` o `nombre` (legacy); `telefono_movil` / `celular`.
     */
    public function crearColaborador(array $data, Empresa $empresa): User
    {
        $this->normalizarClavesNombre($data);
        $email = $data['email'] ?? null;
        $telefonoMovil = $data['telefono_movil'] ?? $data['celular'] ?? null;
        if (($email === null || $email === '') && ($telefonoMovil === null || $telefonoMovil === '')) {
            throw ValidationException::withMessages([
                'email' => ['Debe proporcionar al menos email o teléfono móvil.'],
            ]);
        }
        if ($email === null || $email === '') {
            $email = $this->generarEmailPlaceholder($empresa->id);
            $data['email'] = $email;
        }
        $this->validarUnicidadEmailCrear($email);
        $this->validarUnicidadMovilCrear($telefonoMovil, $empresa->id);
        $this->validarPeriodicidadCatorcenal($data['periodicidad_pago'] ?? null, $empresa);

        $beneficiarios = $data['beneficiarios'] ?? [];
        $cuentaNomina = $data['cuenta_nomina'] ?? null;
        $accesoPanel = (bool) ($data['acceso_panel'] ?? false);
        unset($data['beneficiarios'], $data['cuenta_nomina'], $data['acceso_panel']);

        $catalogoRhDesdePayload = $this->extraerCatalogoRhDesdePayload($data);
        $dataUsuario = $this->extraerAtributosUser($data);
        $dataUsuario['empresa_id'] = $empresa->id;
        $dataUsuario['tipo'] = ['colaborador'];
        $dataUsuario['password'] = $dataUsuario['password'] ?? Hash::make(Str::random(32));

        DB::beginTransaction();
        try {
            $user = User::create($dataUsuario);

            $colaborador = Colaborador::query()->create(
                $this->construirAtributosFichaColaborador($user, $empresa, $catalogoRhDesdePayload)
            );

            $user->forceFill(['colaborador_id' => $colaborador->id])->save();
            $user->refresh();
            $user->load('colaborador');

            $codigoJefe = $this->generarCodigoJefe($user);
            $user->forceFill(['codigo_jefe' => $codigoJefe])->save();
            $colaborador->forceFill(['codigo_jefe' => $codigoJefe])->save();

            $this->historialService->crearHistorialesIniciales($user);

            if (! empty($beneficiarios)) {
                $this->validarPorcentajesBeneficiarios($beneficiarios);
                foreach ($beneficiarios as $b) {
                    BeneficiarioColaborador::create([
                        'user_id' => $user->id,
                        'colaborador_id' => $colaborador->id,
                        'nombre_completo' => $b['nombre_completo'],
                        'parentesco' => $b['parentesco'],
                        'porcentaje' => $b['porcentaje'] ?? null,
                    ]);
                }
            }

            if ($cuentaNomina !== null) {
                $this->validarUnicidadNumeroCuenta($cuentaNomina['numero_cuenta'] ?? '', $empresa->id, null);
                CuentaNomina::create([
                    'user_id' => $user->id,
                    'colaborador_id' => $colaborador->id,
                    'banco_id' => $cuentaNomina['banco_id'],
                    'numero_cuenta' => $cuentaNomina['numero_cuenta'],
                    'tipo_cuenta' => $cuentaNomina['tipo_cuenta'] ?? 'CLABE',
                    'estado' => $cuentaNomina['estado'] ?? 'ACTIVA',
                ]);
            }

            $this->asignacionProductosService->asignarProductosEmpresa($user, $empresa);

            if ($accesoPanel) {
                $user->empresas()->syncWithoutDetaching([$empresa->id]);
                $user->agregarRol('cliente');
            }

            DB::commit();

            $user = $user->fresh();

            try {
                $this->correoBienvenidaService->enviar($colaborador->fresh());
            } catch (\Throwable $e) {
                Log::warning('Error despachando correo de bienvenida', [
                    'colaborador_id' => $colaborador->id,
                    'error' => $e->getMessage(),
                ]);
            }

            return $user;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function actualizarColaborador(User $user, array $data): User
    {
        $this->normalizarClavesNombre($data);
        $this->validarUnicidadEmailActualizar($data['email'] ?? null, $user);
        $telefono = $data['telefono_movil'] ?? $data['celular'] ?? null;
        $this->validarUnicidadMovilActualizar($telefono, $user);

        $beneficiarios = $data['beneficiarios'] ?? null;
        $cuentaNomina = $data['cuenta_nomina'] ?? null;
        unset($data['beneficiarios'], $data['cuenta_nomina']);

        $empresaId = (int) $user->empresa_id;
        $catalogoRhDesdePayload = $this->extraerCatalogoRhDesdePayload($data);
        $user->loadMissing('colaborador');
        $ficha = $user->colaborador;
        $catalogosAnteriores = [
            'ubicacion_id' => $ficha?->ubicacion_id,
            'departamento_id' => $ficha?->departamento_id,
            'area_id' => $ficha?->area_id,
            'puesto_id' => $ficha?->puesto_id,
            'region_id' => $ficha?->region_id,
            'razon_social_id' => $ficha?->razon_social_id,
            'centro_pago_id' => $ficha?->centro_pago_id,
            'periodicidad_pago' => $ficha?->periodicidad_pago ?? $user->periodicidad_pago,
        ];

        $mapTipoCatalogo = [
            'ubicacion_id' => 'ubicacion',
            'departamento_id' => 'departamento',
            'area_id' => 'area',
            'puesto_id' => 'puesto',
            'region_id' => 'region',
            'razon_social_id' => 'razon_social',
            'periodicidad_pago' => 'periodicidad_pago',
        ];

        DB::beginTransaction();
        try {
            foreach ($mapTipoCatalogo as $campo => $tipoCatalogo) {
                if (! array_key_exists($campo, $data)) {
                    continue;
                }
                $nuevo = $data[$campo];
                if ($nuevo != $catalogosAnteriores[$campo]) {
                    $this->historialService->registrarCambio(
                        $user,
                        $tipoCatalogo,
                        $nuevo
                    );
                }
            }

            $dataUsuario = $this->extraerAtributosUser($data);
            unset($dataUsuario['empresa_id'], $dataUsuario['password'], $dataUsuario['tipo']);
            $user->update($dataUsuario);

            $empresaModel = Empresa::query()->find($empresaId);
            if ($user->colaborador_id !== null && $empresaModel !== null) {
                $attrsFicha = $this->construirAtributosFichaColaborador(
                    $user->fresh(),
                    $empresaModel,
                    $catalogoRhDesdePayload
                );
                $attrsFicha = array_intersect_key($attrsFicha, array_flip((new Colaborador)->getFillable()));
                Colaborador::query()->whereKey($user->colaborador_id)->update($attrsFicha);
            }

            if (! empty($beneficiarios)) {
                $this->validarPorcentajesBeneficiarios($beneficiarios);
                $user->beneficiarios()->delete();
                foreach ($beneficiarios as $b) {
                    BeneficiarioColaborador::create([
                        'user_id' => $user->id,
                        'colaborador_id' => $user->colaborador_id,
                        'nombre_completo' => $b['nombre_completo'],
                        'parentesco' => $b['parentesco'],
                        'porcentaje' => $b['porcentaje'] ?? null,
                    ]);
                }
            } elseif ($beneficiarios === []) {
                $user->beneficiarios()->delete();
            }

            if ($cuentaNomina !== null) {
                $this->validarUnicidadNumeroCuenta(
                    $cuentaNomina['numero_cuenta'] ?? '',
                    $empresaId,
                    $user->id
                );
                $user->cuentasNomina()->delete();
                CuentaNomina::create([
                    'user_id' => $user->id,
                    'colaborador_id' => $user->colaborador_id,
                    'banco_id' => $cuentaNomina['banco_id'],
                    'numero_cuenta' => $cuentaNomina['numero_cuenta'],
                    'tipo_cuenta' => $cuentaNomina['tipo_cuenta'] ?? 'CLABE',
                    'estado' => $cuentaNomina['estado'] ?? 'ACTIVA',
                ]);
            }

            $catalogosCambiaron = false;
            foreach (array_keys($catalogosAnteriores) as $campo) {
                if (isset($data[$campo]) && ($data[$campo] ?? null) != $catalogosAnteriores[$campo]) {
                    $catalogosCambiaron = true;
                    break;
                }
            }

            if ($catalogosCambiaron) {
                $user->refresh();
                $user->codigo_jefe = $this->generarCodigoJefe($user);
                $user->save();
                $this->asignacionProductosService->reevaluarProductos($user);
            }

            DB::commit();

            return $user->fresh();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function extraerAtributosUser(array $data): array
    {
        return array_intersect_key($data, array_flip((new User)->getFillable()));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function normalizarClavesNombre(array &$data): void
    {
        if (isset($data['nombre']) && ! isset($data['name'])) {
            $data['name'] = $data['nombre'];
        }
        if (isset($data['celular']) && ! isset($data['telefono_movil'])) {
            $data['telefono_movil'] = $data['celular'];
        }
    }

    private function generarEmailPlaceholder(int $empresaId): string
    {
        do {
            $email = 'colaborador.'.$empresaId.'.'.Str::lower(Str::random(16)).'@sin-email.tecben.local';
        } while (User::query()->where('email', $email)->exists());

        return $email;
    }

    private function validarUnicidadEmailCrear(?string $email): void
    {
        if ($email === null || $email === '') {
            return;
        }
        // BL: users.email es único a nivel tabla; no solo por empresa.
        if (User::query()->where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => ['El email ya está registrado.']]);
        }
    }

    private function validarUnicidadMovilCrear(?string $telefonoMovil, int $empresaId): void
    {
        if ($telefonoMovil === null || $telefonoMovil === '') {
            return;
        }
        if (User::query()->colaboradoresDeEmpresa($empresaId)->where('telefono_movil', $telefonoMovil)->exists()) {
            throw ValidationException::withMessages(['telefono_movil' => ['El teléfono móvil ya está registrado para un colaborador de esta empresa.']]);
        }
    }

    private function validarUnicidadEmailActualizar(?string $email, User $user): void
    {
        if ($email === null || $email === '') {
            return;
        }
        if (User::query()->where('email', $email)->where('id', '!=', $user->id)->exists()) {
            throw ValidationException::withMessages(['email' => ['El email ya está registrado.']]);
        }
    }

    private function validarUnicidadMovilActualizar(?string $telefonoMovil, User $user): void
    {
        if ($telefonoMovil === null || $telefonoMovil === '') {
            return;
        }
        $empresaId = (int) $user->empresa_id;
        if (User::query()->colaboradoresDeEmpresa($empresaId)->where('telefono_movil', $telefonoMovil)->where('id', '!=', $user->id)->exists()) {
            throw ValidationException::withMessages(['telefono_movil' => ['El teléfono móvil ya está registrado para otro colaborador de esta empresa.']]);
        }
    }

    private function validarPeriodicidadCatorcenal(?string $periodicidadPago, Empresa $empresa): void
    {
        if ($periodicidadPago !== 'CATORCENAL') {
            return;
        }
        if (! $empresa->fecha_proximo_pago_catorcenal) {
            throw ValidationException::withMessages([
                'periodicidad_pago' => ['La empresa debe tener configurada la fecha de próximo pago catorcenal para usar periodicidad CATORCENAL.'],
            ]);
        }
    }

    /**
     * @param  array<int, array{nombre_completo: string, parentesco: string, porcentaje?: float|null}>  $beneficiarios
     */
    private function validarPorcentajesBeneficiarios(array $beneficiarios): void
    {
        $suma = 0.0;
        foreach ($beneficiarios as $b) {
            $suma += (float) ($b['porcentaje'] ?? 0);
        }
        if (abs($suma - 100.0) > 0.01) {
            throw ValidationException::withMessages([
                'beneficiarios' => ['La suma de porcentajes de beneficiarios debe ser 100.'],
            ]);
        }
    }

    private function validarUnicidadNumeroCuenta(string $numeroCuenta, int $empresaId, ?int $excluirUserId): void
    {
        $userIds = User::query()->colaboradoresDeEmpresa($empresaId)->pluck('id');
        $query = CuentaNomina::query()
            ->where('numero_cuenta', $numeroCuenta)
            ->whereIn('user_id', $userIds);

        if ($excluirUserId !== null) {
            $query->where('user_id', '!=', $excluirUserId);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages(['cuenta_nomina.numero_cuenta' => ['El número de cuenta ya está en uso por otro colaborador activo de esta empresa.']]);
        }
    }

    private function generarCodigoJefe(User $user): string
    {
        $user->loadMissing('colaborador');
        $ficha = $user->colaborador;
        $partes = array_filter([
            $ficha?->ubicacion_id,
            $ficha?->departamento_id,
            $ficha?->area_id,
            $ficha?->puesto_id,
        ]);

        return implode('.', $partes) ?: '';
    }

    /**
     * @param  array<string, mixed>  $catalogoRhOverrides  FK de catálogo desde el payload; si falta una clave, se usa la ficha actual.
     * @return array<string, mixed>
     */
    private function construirAtributosFichaColaborador(User $user, Empresa $empresa, array $catalogoRhOverrides = []): array
    {
        $user->loadMissing('colaborador');
        $ficha = $user->colaborador;
        $catalogoRh = $this->resolverCatalogoRhParaFicha($ficha, $catalogoRhOverrides);

        return [
            'empresa_id' => $empresa->id,
            'nombre' => $user->name ?? '',
            'apellido_paterno' => $user->apellido_paterno ?? '',
            'apellido_materno' => $user->apellido_materno ?? '',
            'email' => $user->email,
            'telefono_movil' => $user->telefono_movil,
            'numero_colaborador' => $user->numero_colaborador,
            'fecha_nacimiento' => $user->fecha_nacimiento,
            'genero' => $user->genero,
            'curp' => $user->curp,
            'rfc' => $user->rfc,
            'nss' => $user->nss,
            'fecha_ingreso' => $user->fecha_ingreso,
            'fecha_registro_imss' => $user->fecha_registro_imss,
            'estado_civil' => $user->estado_civil,
            'nacionalidad' => $user->nacionalidad,
            'direccion' => $user->direccion,
            'salario_bruto' => $user->salario_bruto,
            'salario_neto' => $user->salario_neto,
            'salario_diario' => $user->salario_diario,
            'salario_diario_integrado' => $user->salario_diario_integrado,
            'salario_variable' => null,
            'monto_maximo' => $user->monto_maximo,
            'periodicidad_pago' => $user->periodicidad_pago ?? 'MENSUAL',
            'dia_periodicidad' => $user->dia_periodicidad,
            'dias_vacaciones_anuales' => $user->dias_vacaciones_legales ?? 0,
            'dias_vacaciones_restantes' => $user->dias_vacaciones_empresa ?? 0,
            'hora_entrada' => $user->hora_entrada,
            'hora_salida' => $user->hora_salida,
            'hora_entrada_comida' => $user->hora_entrada_comida,
            'hora_salida_comida' => $user->hora_salida_comida,
            'hora_entrada_extra' => $user->hora_inicio_horas_extra,
            'hora_salida_extra' => $user->hora_fin_horas_extra,
            'comentario_adicional' => $user->comentario_adicional,
            'codigo_jefe' => $user->codigo_jefe,
            'verificado' => $user->verificado ?? false,
            'verificacion_carga_masiva' => $user->verificacion_carga_masiva ?? false,
            'tiene_identificacion' => $user->tiene_identificacion ?? false,
            'fecha_verificacion_movil' => $user->fecha_verificacion_movil,
            'ubicacion_id' => $catalogoRh['ubicacion_id'],
            'departamento_id' => $catalogoRh['departamento_id'],
            'area_id' => $catalogoRh['area_id'],
            'puesto_id' => $catalogoRh['puesto_id'],
            'region_id' => $catalogoRh['region_id'],
            'centro_pago_id' => $catalogoRh['centro_pago_id'],
            'razon_social_id' => $catalogoRh['razon_social_id'],
            'nombre_empresa_pago' => $user->nombre_empresa_pago,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function extraerCatalogoRhDesdePayload(array $data): array
    {
        return array_intersect_key($data, array_flip(self::CATALOGO_RH_KEYS));
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function resolverCatalogoRhParaFicha(?Colaborador $ficha, array $overrides = []): array
    {
        $out = [];
        foreach (self::CATALOGO_RH_KEYS as $key) {
            $out[$key] = array_key_exists($key, $overrides)
                ? $overrides[$key]
                : $ficha?->getAttribute($key);
        }

        return $out;
    }
}
