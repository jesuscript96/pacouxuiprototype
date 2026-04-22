<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ConfiguracionApp extends Model
{
    protected $table = 'configuracion_app';

    protected $fillable = [
        'nombre_app',
        'android_app_id',
        'ios_app_id',
        'one_signal_app_id',
        'one_signal_rest_api_key',
        'link_descarga',
        'android_channel_id',
        'version_ios',
        'version_android',
        'requiere_validacion',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'requiere_validacion' => 'boolean',
        ];
    }

    /**
     * Empresas que usan esta configuración de app.
     */
    public function empresas(): HasMany
    {
        return $this->hasMany(Empresa::class, 'configuracion_app_id');
    }

    public function tieneOneSignalConfigurado(): bool
    {
        return filled($this->one_signal_app_id) && filled($this->one_signal_rest_api_key);
    }
}
