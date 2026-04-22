<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jefes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('colaborador_id')
                ->nullable()
                ->constrained('colaboradores')
                ->nullOnDelete();
            $table->string('codigo_nivel_1');
            $table->string('codigo_nivel_2')->nullable();
            $table->string('codigo_nivel_3')->nullable();
            $table->string('codigo_nivel_4')->nullable();
            $table->timestamps();

            $table->unique('colaborador_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jefes');
    }
};
