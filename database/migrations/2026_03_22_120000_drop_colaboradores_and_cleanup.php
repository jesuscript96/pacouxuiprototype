<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * BL: La línea “unificar todo en users” se desestimó: se mantiene `colaboradores` como catálogo RH
     * y relación 1:1 vía `users.colaborador_id` → `colaboradores.id` (sin `colaboradores.user_id`).
     * La migración original eliminaba la tabla y columnas colaborador_id; ya no debe ejecutarse.
     */
    public function up(): void
    {
        // Intencionalmente vacío.
    }

    public function down(): void
    {
        //
    }
};
