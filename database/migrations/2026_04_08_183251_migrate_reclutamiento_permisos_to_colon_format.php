<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /** @var array<string, string> */
    private array $mapping = [
        'ViewCandidato' => 'ViewAny:Candidato',
        'UpdateCandidato' => 'Update:Candidato',
        'DeleteCandidato' => 'Delete:Candidato',
        'DeleteComentarioCandidato' => 'Delete:MensajeCandidato',
    ];

    public function up(): void
    {
        foreach ($this->mapping as $old => $new) {
            DB::table('permissions')
                ->where('name', $old)
                ->update(['name' => $new]);
        }
    }

    public function down(): void
    {
        foreach ($this->mapping as $old => $new) {
            DB::table('permissions')
                ->where('name', $new)
                ->update(['name' => $old]);
        }
    }
};
