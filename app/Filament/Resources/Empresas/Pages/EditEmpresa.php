<?php

namespace App\Filament\Resources\Empresas\Pages;

use App\Filament\Resources\Empresas\EmpresaResource;
use App\Models\FrecuenciaNotificaciones;
use App\Models\NotificacionesIncluidas;
use App\Models\QuincenasPersonalizadas;
use App\Models\RazonEncuestaSalida;
use App\Services\ArchivoService;
use App\Services\EmpresaService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;
use Illuminate\Database\Eloquent\Model;

class EditEmpresa extends EditRecord
{
    protected static string $resource = EmpresaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    // Ancho de pantalla
    public function getMaxContentWidth(): Width|string|null
    {
        return Width::MaxContent;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return '¡Empresa actualizada con éxito!';
    }

    /**
     * Obtiene las relaciones y data adicionales para rellenar el formulario de edición.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $record = $this->getRecord();
        $record->load([
            'razonesSociales',
            'productos',
            'comisionesRangos',
            'configuracionRetencionNominas',
            'centrosCostos',
            'aliasTipoTransacciones',
            'notificacionesIncluidas',
            'notificacionesEstadoAnimo',
            'documentos',
        ]);

        if ($record->nombre_app != null && $record->link_descarga_app != null) {
            $data['aplicacion_compilada'] = true;
        } else {
            $data['aplicacion_compilada'] = false;
        }

        // Razones sociales (repeater): id, nombre, rfc, cp, calle, numero_exterior, numero_interior, colonia, alcaldia, estado, registro_patronal + api_options_storage para el Select de colonia
        $data['razones_sociales'] = $record->razonesSociales
            ->map(fn ($r) => array_merge(
                $r->only(['id', 'nombre', 'rfc', 'cp', 'calle', 'numero_exterior', 'numero_interior', 'colonia', 'alcaldia', 'estado', 'registro_patronal']),
                ['api_options_storage' => $r->colonia ? json_encode([$r->colonia => $r->colonia]) : null]
            ))
            ->values()
            ->all();

        // Productos (repeater): desde el pivot, producto_id y desde (string para el Select)
        $data['productos'] = $record->productos
            ->map(fn ($p) => [
                'producto_id' => $p->pivot->producto_id ?? $p->id,
                'desde' => $p->pivot->desde,
            ])
            ->values()
            ->all();

        // Rango comisión (repeater, solo si tipo_comision = MIXED)
        $data['rango_comision'] = $record->comisionesRangos
            ->map(fn ($c) => [
                'rango_comision_precio_desde' => (string) $c->precio_desde,
                'rango_comision_precio_hasta' => (string) $c->precio_hasta,
                'rango_comision_monto_fijo' => (string) $c->cantidad_fija,
                'rango_comision_porcentaje' => (string) $c->porcentaje,
            ])
            ->values()
            ->all();

        // Retenciones: emails_retenciones (repeater) y días por periodicidad. En BD están como ["a@b.com", "b@b.com"]
        $configsRetencion = $record->configuracionRetencionNominas;
        $emailsRecopilados = $configsRetencion->flatMap(fn ($c) => is_array($c->emails) ? $c->emails : [])->unique()->values()->all();
        $data['emails_retenciones'] = array_map(fn ($email) => ['email_retencion' => $email], $emailsRecopilados);
        if ($data['emails_retenciones'] === []) {
            $data['emails_retenciones'] = [['email_retencion' => '']];
        }
        $configMensual = $configsRetencion->firstWhere('periodicidad_pago', 'MENSUAL');
        $configSemanal = $configsRetencion->firstWhere('periodicidad_pago', 'SEMANAL');
        $configCatorcenal = $configsRetencion->firstWhere('periodicidad_pago', 'CATORCENAL');
        $configQuincenal = $configsRetencion->firstWhere('periodicidad_pago', 'QUINCENAL');
        $data['dia_retencion_mensual'] = $configMensual && $configMensual->fecha ? $configMensual->fecha : null;
        $data['dia_retencion_semanal'] = $configSemanal && $configSemanal->dia_semana !== null ? (string) $configSemanal->dia_semana : null;
        $data['dia_retencion_catorcenal'] = $configCatorcenal && $configCatorcenal->dias !== null ? (string) $configCatorcenal->dias : null;
        $data['dia_retencion_quincenal'] = $configQuincenal && $configQuincenal->dias !== null ? (string) $configQuincenal->dias : null;

        // Quincena personalizada (una sola fila por empresa)
        $quincena = QuincenasPersonalizadas::where('empresa_id', $record->id)->first();
        $data['tiene_quincena_personalizada'] = $quincena !== null;
        $data['dia_inicio'] = $quincena ? (string) $quincena->dia_inicio : null;
        $data['dia_fin'] = $quincena ? (string) $quincena->dia_fin : null;

        // Razones de encuesta de salida (CheckboxList). Filtrar al catálogo: valores legacy/seeders no coinciden con las keys del CheckboxList y rompen la regla `in` al validar.
        $data['razones'] = RazonEncuestaSalida::soloRazonesDelCatalogo(
            RazonEncuestaSalida::where('empresa_id', $record->id)->pluck('razon')->all()
        );

        // Centros de costo por servicio (BELVO, EMIDA, STP)
        $centros = $record->centrosCostos->keyBy('servicio');
        $data['centro_costo_belvo_id'] = $centros->get('BELVO')?->id;
        $data['centro_costo_emida_id'] = $centros->get('EMIDA')?->id;
        $data['centro_costo_stp_id'] = $centros->get('STP')?->id;

        // Alias de transacciones por tipo
        $aliasMap = $record->aliasTipoTransacciones->keyBy('tipo_transaccion');
        $data['alias_transaccion_nomina'] = $aliasMap->get('ADELANTO DE NOMINA')?->alias;
        $data['alias_transaccion_servicio'] = $aliasMap->get('PAGO DE SERVICIO')?->alias;
        $data['alias_transaccion_recarga'] = $aliasMap->get('RECARGA')?->alias;

        // Notificaciones incluidas: array [id => true/false] para todos los tipos; true si la empresa lo tiene
        $idsIncluidos = $record->notificacionesIncluidas->pluck('id')->flip()->map(fn () => true)->all();
        $data['notificaciones_incluidas'] = NotificacionesIncluidas::all()->mapWithKeys(fn ($n) => [$n->id => $idsIncluidos[$n->id] ?? false])->all();

        // Notificaciones estado de ánimo: array [id => true/false] para todos los tipos; true si la empresa lo tiene
        $idsEstadoAnimo = $record->notificacionesEstadoAnimo->pluck('id')->flip()->map(fn () => true)->all();
        $data['frecuencia_notificaciones_estado_animo'] = FrecuenciaNotificaciones::all()->mapWithKeys(fn ($n) => [$n->id => $idsEstadoAnimo[$n->id] ?? false])->all();

        $data['documentos_contratos'] = $record->documentos()->pluck('ruta')->toArray();

        $archivoService = app(ArchivoService::class);

        if ($record->foto && $archivoService->existe($record->foto)) {
            $data['foto'] = $record->foto;
        } else {
            // BL: Retrocompatibilidad con la ruta legacy de fotos
            $fotoLegacy = "assets/companies/photos/{$record->id}.png";
            $data['foto'] = $archivoService->existe($fotoLegacy) ? $fotoLegacy : null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(EmpresaService::class)->update($record, $data);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

/*
 * MEJORAS OPCIONALES EN OTROS ARCHIVOS (realizar manualmente si se desea):
 *
 * 1. Modelo App\Models\Empresa
 *    Agregar relación hasMany con RazonEncuestaSalida para no usar el modelo directo aquí:
 *    public function razonesEncuestaSalida(): \Illuminate\Database\Eloquent\Relations\HasMany
 *    {
 *        return $this->hasMany(RazonEncuestaSalida::class, 'empresa_id');
 *    }
 *    Luego en mutateFormDataBeforeFill se puede hacer: $record->load('razonesEncuestaSalida')
 *    y usar $data['razones'] = $record->razonesEncuestaSalida->pluck('razon')->all();
 *
 * 2. App\Services\EmpresaService::update()
 *    Verificar que el método update() (y syncRelationsAfterUpdate/syncRazonesSociales) persista
 *    todas las relaciones que el formulario de edición envía: rango_comision (comisionesRangos),
 *    configuracionRetencionNominas (emails y periodicidades), quincenas_personalizadas,
 *    centrosCostos (sync), productos (sync con desde), aliasTipoTransacciones, notificacionesIncluidas.
 *    Si alguna no se actualiza al guardar, hay que añadir la lógica correspondiente en el servicio.
 */
