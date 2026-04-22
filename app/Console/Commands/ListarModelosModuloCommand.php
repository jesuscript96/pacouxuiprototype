<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ListarModelosModuloCommand extends Command
{
    protected $signature = 'listar:modelos-modulo
                            {modulo : Nombre del módulo: chat, voz, financiero, documentos, adicionales}';

    protected $description = 'Lista los modelos de un módulo (pospuestos) y los archivos que los referencian, para desacoplar de forma granular.';

    /**
     * Módulos y modelos cuyas tablas están en migraciones pospuestas.
     * Clase => tabla (solo informativo).
     *
     * @var array<string, array<string, string>>
     */
    private const MODULOS = [
        'chat' => [
            'ChatRoom' => 'chat_rooms',
            'ChatRoomEmployee' => 'chat_room_employees',
            'ChatMessage' => 'chat_messages',
            'ChatMessageStatus' => 'chat_message_status',
            'ChatMessageMention' => 'chat_message_mentions',
            'ChatMessageReaction' => 'chat_message_reactions',
        ],
        'voz' => [
            'VozEmpleado' => 'voces_empleado',
            'UsuarioTemaVoz' => 'usuario_tema_voz',
            'ReiteracionVoz' => 'reiteraciones_voz',
            'TokenPushUser' => 'tokens_push_user',
            'Testigo' => 'testigos',
            'OneSignalToken' => 'one_signal_tokens',
            'DirectDebitBelvo' => 'direct_debit_belvos',
        ],
        'financiero' => [
            'CuentaEmpleado' => 'cuentas_empleado',
            'EstadoCuenta' => 'estados_cuenta',
            'Transaccion' => 'transacciones',
            'CuentaPorCobrarEmpleado' => 'cuentas_por_cobrar_empleado',
            'ReciboNominaEmpleado' => 'recibos_nomina_empleado',
            'AdelantoNominaEmpleado' => 'adelantos_nomina_empleado',
            'PayrollWithholdingConfig' => 'payroll_withholding_configs',
        ],
        'documentos' => [
            'Folder' => 'folders',
            'DigitalDocument' => 'digital_documents',
            'EmploymentContractsToken' => 'employment_contracts_tokens',
        ],
        'adicionales' => [
            'DepartamentoGeneral' => 'departamentos_generales',
        ],
    ];

    public function handle(): int
    {
        $modulo = strtolower($this->argument('modulo'));

        if (! array_key_exists($modulo, self::MODULOS)) {
            $this->error('Módulo no reconocido. Usar: '.implode(', ', array_keys(self::MODULOS)));

            return self::FAILURE;
        }

        $modelos = self::MODULOS[$modulo];
        $this->info("Módulo: {$modulo}");
        $this->newLine();
        $this->line('<comment>Modelos (tabla en pospuestos):</comment>');
        foreach ($modelos as $clase => $tabla) {
            $path = app_path('Models/'.$clase.'.php');
            $existe = File::exists($path) ? '✓' : '—';
            $this->line("  {$existe} {$clase}  →  {$tabla}");
        }
        $this->newLine();
        $this->line('<comment>Referencias en app/:</comment>');

        $referencias = $this->buscarReferencias(array_keys($modelos));
        if ($referencias === []) {
            $this->line('  (ninguna)');
        } else {
            foreach ($referencias as $archivo => $clases) {
                $this->line('  '.str_replace(base_path().DIRECTORY_SEPARATOR, '', $archivo));
                foreach ($clases as $clase) {
                    $this->line('    → '.$clase);
                }
            }
        }
        $this->newLine();
        $this->line('Ver docs/modelos_por_modulo_granular.md para el proceso de desacoplar.');

        return self::SUCCESS;
    }

    /**
     * Busca en app/ archivos que referencien alguna de las clases dadas.
     *
     * @param  array<int, string>  $clases
     * @return array<string, array<int, string>>
     */
    private function buscarReferencias(array $clases): array
    {
        $resultado = [];
        $files = File::allFiles(app_path());
        $esteArchivo = (new \ReflectionClass($this))->getFileName();
        foreach ($files as $file) {
            $path = $file->getPathname();
            if ($path === $esteArchivo || $file->getExtension() !== 'php') {
                continue;
            }
            $content = File::get($path);
            $encontradas = [];
            foreach ($clases as $clase) {
                if (str_contains($content, $clase)) {
                    $encontradas[] = $clase;
                }
            }
            if ($encontradas !== []) {
                $resultado[$path] = $encontradas;
            }
        }

        return $resultado;
    }
}
