<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Elimina la tabla usuarios tras la unificación en users.
     */
    public function up(): void
    {
        Schema::dropIfExists('usuarios');
    }

    /**
     * No se puede revertir sin pérdida de datos.
     */
    public function down(): void
    {
        // No-op
    }
};
