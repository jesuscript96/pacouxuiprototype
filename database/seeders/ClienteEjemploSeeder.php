<?php

namespace Database\Seeders;

use App\Helpers\SeederRoleNaming;
use App\Models\Colaborador;
use App\Models\Empresa;
use App\Models\User;
use Illuminate\Database\Seeder;

class ClienteEjemploSeeder extends Seeder
{
    /**
     * Crea ficha en `colaboradores` y usuario cliente@tecben.com (panel Cliente, empresa id=1, rol admin_empresa).
     * BL: usuarios con acceso al panel Cliente deben tener colaborador_id → ficha RH.
     * Idempotente. Password en texto plano (cast `hashed` en User).
     */
    public function run(): void
    {
        $empresaId = 1;
        $empresa = Empresa::find($empresaId);
        if ($empresa === null) {
            $this->command->warn('No existe empresa id 1. Ejecuta EmpresaEjemploSeeder antes.');

            return;
        }

        $email = 'cliente@tecben.com';

        $colaborador = Colaborador::withoutEvents(function () use ($email, $empresaId): Colaborador {
            return Colaborador::query()->firstOrCreate(
                [
                    'email' => $email,
                    'empresa_id' => $empresaId,
                ],
                [
                    'nombre' => 'Cliente',
                    'apellido_paterno' => 'Ejemplo',
                    'apellido_materno' => 'Panel',
                    'numero_colaborador' => 'FCH-CLIENTE-TECBEN-DEMO',
                    'fecha_nacimiento' => '1990-01-15',
                    'fecha_ingreso' => now()->format('Y-m-d'),
                    'periodicidad_pago' => 'QUINCENAL',
                    'verificado' => false,
                    'verificacion_carga_masiva' => false,
                ]
            );
        });

        $user = User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Cliente',
                'apellido_paterno' => 'Ejemplo',
                'apellido_materno' => 'Panel',
                'password' => 'password',
                'tipo' => ['cliente'],
                'empresa_id' => $empresaId,
                'colaborador_id' => $colaborador->id,
                'numero_colaborador' => $colaborador->numero_colaborador,
            ]
        );

        if ((int) $user->colaborador_id !== (int) $colaborador->id || (int) $user->empresa_id !== $empresaId) {
            $user->update([
                'colaborador_id' => $colaborador->id,
                'empresa_id' => $empresaId,
                'numero_colaborador' => $colaborador->numero_colaborador,
            ]);
        }

        $user->empresas()->syncWithoutDetaching([$empresaId]);

        $rolAdmin = SeederRoleNaming::findForCompany($empresaId, 'admin_empresa');

        if ($rolAdmin && ! $user->hasRole($rolAdmin->name)) {
            $user->assignRole($rolAdmin);
        }

        $this->command->info('Usuario cliente@tecben.com listo (ficha colaborador id='.$colaborador->id.', rol admin_empresa, empresa '.$empresaId.'). Contraseña: password');
    }
}
