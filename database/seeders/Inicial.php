<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class Inicial extends Seeder
{
    /**
     * Run the database seeds.
     * Idempotente: no inserta filas si el ID ya existe (seguro para entorno de Rafa).
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@paco.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
                'tipo' => ['administrador'],
            ]
        );

        $productos = [
            ['id' => 6, 'nombre' => 'Adelanto de Nómina', 'descripcion' => ''],
            ['id' => 7, 'nombre' => 'Descuentos', 'descripcion' => ''],
            ['id' => 8, 'nombre' => 'Doctor en Línea Individual', 'descripcion' => 'Doctor en Línea Individual'],
            ['id' => 10, 'nombre' => 'Seguros Individuales', 'descripcion' => 'Esta es la descripcion de este producto'],
            ['id' => 11, 'nombre' => 'Seguros Familiares', 'descripcion' => ''],
            ['id' => 12, 'nombre' => 'Doctor en Linea Freemium', 'descripcion' => 'Doctor en Linea Freemium'],
            ['id' => 13, 'nombre' => 'Doctor en Línea Familiar', 'descripcion' => 'Doctor en Línea Familiar'],
            ['id' => 14, 'nombre' => 'Recargas', 'descripcion' => 'Recargas'],
            ['id' => 15, 'nombre' => 'Pago de Servicios', 'descripcion' => 'Pago de Servicios'],
            ['id' => 16, 'nombre' => 'Comunicación', 'descripcion' => 'Comunicación'],
            ['id' => 17, 'nombre' => 'Encuestas', 'descripcion' => 'Encuestas'],
            ['id' => 18, 'nombre' => 'Reconocimientos', 'descripcion' => 'Reconocimientos'],
            ['id' => 19, 'nombre' => 'Voz del Empleado', 'descripcion' => 'Voz del empleado'],
            ['id' => 20, 'nombre' => 'Solicitudes', 'descripcion' => 'Modulo para que los empleados ingresar solicitudes de vacaciones, enfermedades, incapacidad, etc..'],
            ['id' => 21, 'nombre' => 'Recibos de nomina', 'descripcion' => 'Modulo para que los empleados puedan ver sus recibos de nomina'],
            ['id' => 22, 'nombre' => 'Documentos corporativos', 'descripcion' => 'Modulo para que los empleados vean los documentos corporativos de la empresa como manuales y politicas de la empresa'],
            ['id' => 23, 'nombre' => 'Seguros por suscripción', 'descripcion' => 'Asignación del producto mediante el pago de una suscripción'],
            ['id' => 24, 'nombre' => 'Descuentos por suscripción', 'descripcion' => 'Asignación del producto mediante el pago de una suscripción'],
            ['id' => 26, 'nombre' => 'Asistencias PH', 'descripcion' => 'Asistencias Palacio de Hierro'],
            ['id' => 27, 'nombre' => 'RH Conecta', 'descripcion' => 'RH Conecta Webview'],
            ['id' => 28, 'nombre' => 'Bienestar en línea', 'descripcion' => 'Bienestar en línea'],
            ['id' => 29, 'nombre' => 'Solicitud de Documentos', 'descripcion' => 'Solicitud de Documentos'],
            ['id' => 30, 'nombre' => 'Estados de ánimo', 'descripcion' => 'Sirve para registrar tus estados de ánimo a llevar un mejor seguimiento sobre ellos'],
            ['id' => 31, 'nombre' => 'Cartas SUA', 'descripcion' => 'Permite al colaborador ver sus cartas SUA y firmarlas'],
            ['id' => 32, 'nombre' => 'Contrato Laboral', 'descripcion' => 'Permite que los colaboradores de la empresa un firmen un contrato laboral'],
            ['id' => 33, 'nombre' => 'QR Access', 'descripcion' => 'Modulo para consultar QR de acceso para empleados en la plataforma'],
            ['id' => 34, 'nombre' => 'Carrusel de imagenes', 'descripcion' => 'Permite mostrar en la APP una serie de imagenes configuradas desde el panel a los empleados pertenecientes a la empresa'],
            ['id' => 35, 'nombre' => 'Capacitaciones', 'descripcion' => 'Permite ver los cursos creados de la empresa a los colaboradores'],
        ];
        $this->insertOrIgnore('productos', $productos, 'id');

        $centrosCostos = [
            ['id' => 89, 'servicio' => 'STP', 'nombre' => 'PACOINVES', 'cuenta_bancaria' => '646180243502000005'],
            ['id' => 90, 'servicio' => 'BELVO', 'nombre' => 'PACOINVES', 'key_id' => '4f189b5f-a8ed-4bf1-ad78-89e97dca5152', 'secret_key' => '771bc26001d08cb19759d0900c5460d08cbcea42'],
            ['id' => 91, 'servicio' => 'EMIDA', 'nombre' => 'INVESTAJ MÉXICO', 'terminal_id_tae' => '8602678', 'terminal_id_ps' => '4129556', 'clerk_id_tae' => '2VRD25', 'clerk_id_ps' => '2VRD25'],
            ['id' => 92, 'servicio' => 'EMIDA', 'nombre' => 'PACO', 'terminal_id_tae' => '3298739', 'terminal_id_ps' => '3816940', 'clerk_id_tae' => '24197', 'clerk_id_ps' => 'dEd1s59'],
            ['id' => 93, 'servicio' => 'BELVO', 'nombre' => 'PACO', 'key_id' => '796ff675-7964-4905-a994-3757f87ad3c1', 'secret_key' => '41467a43ea2c298b29a61ac9fe1737cf62eb44d1'],
            ['id' => 94, 'servicio' => 'STP', 'nombre' => 'TECNOLOGÍA EN BENEFICIOS MÉXICO SA DE CV', 'cuenta_bancaria' => '646180243500000007'],
        ];
        $this->insertOrIgnoreCentroCostos($centrosCostos);

        $industrias = [
            ['id' => 2, 'nombre' => 'Tecnología'],
            ['id' => 3, 'nombre' => 'Agricultura'],
            ['id' => 4, 'nombre' => 'Restaurantera'],
            ['id' => 5, 'nombre' => 'Turismo'],
            ['id' => 6, 'nombre' => 'Alimentos'],
            ['id' => 7, 'nombre' => 'Servicios Profesionales'],
            ['id' => 8, 'nombre' => 'Industrial'],
            ['id' => 9, 'nombre' => 'Retail'],
            ['id' => 10, 'nombre' => 'Financiera'],
            ['id' => 11, 'nombre' => 'Energética'],
            ['id' => 12, 'nombre' => 'Construcción'],
            ['id' => 13, 'nombre' => 'Educación'],
            ['id' => 14, 'nombre' => 'Salud'],
            ['id' => 15, 'nombre' => 'Logística'],
        ];
        $this->insertOrIgnore('industrias', $industrias, 'id');

        $subindustrias = [
            ['id' => 2, 'nombre' => 'Programación', 'industria_id' => 2],
            ['id' => 4, 'nombre' => 'Restaurantes', 'industria_id' => 4],
            ['id' => 5, 'nombre' => 'Big Data', 'industria_id' => 2],
            ['id' => 6, 'nombre' => 'Hotelería', 'industria_id' => 5],
            ['id' => 7, 'nombre' => 'Lacteos', 'industria_id' => 6],
            ['id' => 8, 'nombre' => 'Outsourcing', 'industria_id' => 7],
            ['id' => 9, 'nombre' => 'Publicidad', 'industria_id' => 7],
            ['id' => 10, 'nombre' => 'Financiera', 'industria_id' => 7],
            ['id' => 11, 'nombre' => 'Plástico', 'industria_id' => 8],
            ['id' => 12, 'nombre' => 'Belleza', 'industria_id' => 9],
            ['id' => 13, 'nombre' => 'Servicios Legales', 'industria_id' => 7],
            ['id' => 14, 'nombre' => 'Seguridad Privada', 'industria_id' => 7],
            ['id' => 15, 'nombre' => 'Vinos', 'industria_id' => 6],
            ['id' => 16, 'nombre' => 'Textil', 'industria_id' => 8],
            ['id' => 17, 'nombre' => 'Control de Calidad', 'industria_id' => 7],
            ['id' => 18, 'nombre' => 'Farmacéutica', 'industria_id' => 8],
            ['id' => 19, 'nombre' => 'Comedores Industriales', 'industria_id' => 6],
            ['id' => 20, 'nombre' => 'Maquinaria', 'industria_id' => 8],
            ['id' => 21, 'nombre' => 'Logística', 'industria_id' => 7],
            ['id' => 22, 'nombre' => 'Limpieza', 'industria_id' => 7],
            ['id' => 23, 'nombre' => 'Energía', 'industria_id' => 8],
            ['id' => 24, 'nombre' => 'Fintech', 'industria_id' => 10],
            ['id' => 25, 'nombre' => 'Muebles', 'industria_id' => 8],
            ['id' => 26, 'nombre' => 'Gasolinera', 'industria_id' => 11],
            ['id' => 27, 'nombre' => 'Vivienda', 'industria_id' => 12],
            ['id' => 28, 'nombre' => 'Colegios', 'industria_id' => 13],
            ['id' => 29, 'nombre' => 'Health Tech', 'industria_id' => 14],
            ['id' => 30, 'nombre' => 'Carne', 'industria_id' => 6],
            ['id' => 31, 'nombre' => 'Servicios Quiroprácticos', 'industria_id' => 14],
            ['id' => 32, 'nombre' => 'Estacionamientos', 'industria_id' => 7],
            ['id' => 33, 'nombre' => 'Transporte', 'industria_id' => 15],
        ];
        $this->insertOrIgnore('sub_industrias', $subindustrias, 'id');

        $notificaciones = [
            ['id' => 1, 'nombre' => 'Adelanto de nómina disponible'],
            ['id' => 2, 'nombre' => 'Confirmación en validación de cuenta'],
            ['id' => 3, 'nombre' => 'Rechazó en validación de cuenta'],
            ['id' => 4, 'nombre' => 'Registro Exitoso'],
            ['id' => 5, 'nombre' => 'Bienvenida a la Empresa'],
            ['id' => 6, 'nombre' => 'Recordatorio para estado de ánimo'],
        ];
        if (Schema::hasTable('notificaciones_incluidas')) {
            $this->insertOrIgnore('notificaciones_incluidas', $notificaciones, 'id');
        }
    }

    /**
     * Inserta filas solo si el ID no existe (idempotente).
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function insertOrIgnore(string $table, array $rows, string $idKey = 'id'): void
    {
        $existingIds = DB::table($table)->whereIn($idKey, array_column($rows, $idKey))->pluck($idKey)->all();
        $toInsert = array_values(array_filter($rows, fn (array $row) => ! in_array($row[$idKey], $existingIds, true)));
        if ($toInsert !== []) {
            DB::table($table)->insert($toInsert);
        }
    }

    /**
     * Inserta centros de costo (cada fila puede tener columnas distintas) solo si el ID no existe.
     *
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function insertOrIgnoreCentroCostos(array $rows): void
    {
        $existingIds = DB::table('centro_de_costos')->whereIn('id', array_column($rows, 'id'))->pluck('id')->all();
        $now = now();
        foreach ($rows as $row) {
            if (in_array($row['id'], $existingIds, true)) {
                continue;
            }
            $full = array_merge([
                'servicio' => null,
                'nombre' => null,
                'cuenta_bancaria' => null,
                'terminal_id_tae' => null,
                'terminal_id_ps' => null,
                'clerk_id_tae' => null,
                'clerk_id_ps' => null,
                'key_id' => null,
                'secret_key' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ], $row);
            unset($full['id']);
            DB::table('centro_de_costos')->insert(array_merge(['id' => $row['id']], $full));
        }
    }
}
