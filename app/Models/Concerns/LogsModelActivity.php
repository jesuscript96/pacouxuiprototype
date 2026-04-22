<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity as LogsActivityTrait;

/**
 * Auditoría Spatie para modelos de negocio (panel Admin / Cliente).
 * Sobrescribe {@see attributesExcludedFromActivityLog()} para excluir secretos.
 */
trait LogsModelActivity
{
    use LogsActivityTrait;

    public function getActivitylogOptions(): LogOptions
    {
        // BL: sin logFillable()/logOnly() Spatie no tiene atributos que registrar → array vacío → dontSubmitEmptyLogs no persiste nada.
        return LogOptions::defaults()
            ->logFillable()
            ->logExcept(array_values(array_unique(array_merge(
                ['created_at', 'updated_at'],
                $this->attributesExcludedFromActivityLog(),
            ))))
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Atributos que no deben aparecer en activity_log (secretos, tokens, etc.).
     *
     * @return list<string>
     */
    protected function attributesExcludedFromActivityLog(): array
    {
        return [];
    }
}
