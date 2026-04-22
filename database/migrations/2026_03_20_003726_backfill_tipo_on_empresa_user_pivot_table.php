<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Datos: rellena empresa_user.tipo según users.tipo (JSON) para filas con tipo NULL.
     * Prioridad: cliente → administrador → colaborador.
     */
    public function up(): void
    {
        if (! Schema::hasTable('empresa_user') || ! Schema::hasColumn('empresa_user', 'tipo')) {
            return;
        }

        DB::table('empresa_user')
            ->whereNull('tipo')
            ->orderBy('id')
            ->chunkById(500, function ($rows): void {
                foreach ($rows as $row) {
                    $user = User::query()->find($row->user_id);
                    if ($user === null) {
                        continue;
                    }
                    $pivotTipo = $this->inferirTipoPivot($user->tipo ?? []);
                    if ($pivotTipo === null) {
                        continue;
                    }
                    DB::table('empresa_user')
                        ->where('id', $row->id)
                        ->update([
                            'tipo' => $pivotTipo,
                            'updated_at' => now(),
                        ]);
                }
            });
    }

    /**
     * @param  list<string>  $tiposUsuario
     */
    private function inferirTipoPivot(array $tiposUsuario): ?string
    {
        if (in_array('cliente', $tiposUsuario, true)) {
            return 'cliente';
        }
        if (in_array('administrador', $tiposUsuario, true)) {
            return 'administrador';
        }
        if (in_array('colaborador', $tiposUsuario, true)) {
            return 'colaborador';
        }

        return null;
    }

    /**
     * No reversible de forma segura (no sabemos qué filas eran NULL antes).
     */
    public function down(): void
    {
        //
    }
};
