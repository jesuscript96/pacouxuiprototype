@php
    use App\Livewire\NotificacionesPush\ListaDestinatarios;

    $empresaId = $empresaId ?? null;
    $filtrosSegmentacion = $filtros ?? [];
    $destinatariosEstado = $destinatariosEstado ?? null;

    $livewireKey = 'np-lista-'.($empresaId ?? '0').'-'.md5((string) json_encode($filtrosSegmentacion));
@endphp

@if ($empresaId)
    {{-- BL: block + width 100% evita shrink-to-fit del ViewField/Livewire en el grid del formulario. --}}
    <div class="block w-full min-w-0 max-w-full" style="width: 100%;">
        @livewire(ListaDestinatarios::class, [
            'empresaId' => (int) $empresaId,
            'filtros' => $filtrosSegmentacion,
            'destinatariosEstado' => is_array($destinatariosEstado) ? $destinatariosEstado : null,
        ], key($livewireKey))
    </div>
@else
    <div class="w-full rounded-lg border border-dashed border-gray-300 p-4 text-center text-gray-500 dark:border-gray-600 dark:text-gray-400">
        Selecciona una empresa para ver los destinatarios.
    </div>
@endif
