
@php
    $mensaje = $get('mensaje');
    $tipo = $get('tipo');
    $bg = ($tipo == 'CUMPLEAÑOS') ? asset('img/felicitaciones/cumpleanos.png') : asset('img/felicitaciones/aniversario.png');

    $logoPath = $get('logo');
    if ($logoPath && is_string($logoPath)) {
        $archivoService = app(\App\Services\ArchivoService::class);
        $discoNombre = $archivoService->nombreDisco();
        $logo = $discoNombre === 's3'
            ? $archivoService->disco()->temporaryUrl($logoPath, now()->addMinutes(60))
            : asset($logoPath);
    } else {
        $logo = asset('img/felicitaciones/logo.png');
    }
@endphp

<div class="py-6 px-7 mx-auto" style="max-width: 400px; background-image: url('{{ $bg }}'); background-size: cover; background-position: top center; min-height: 700px;">

        {{-- Logo de la empresa --}}
        <div class="mx-auto text-center w-full {{ $tipo == 'CUMPLEAÑOS' ? 'mb-4' : 'mb-14' }}">
            <img src="{{ $logo }}" alt="Logo" style="max-width: 100px; display: block; margin: 0 auto;">
        </div>

        {{-- Mensaje del editor de texto --}}
        <div class="text-center text-lg font-bold mb-4">
            {!! $mensaje !!}
        </div>

        @if($tipo == 'CUMPLEAÑOS')
        <div class="text-center text-2xl font-bold" style="color: #3148C8; line-height: 1.2">
                ¡Muy feliz cumpleaños <div class="text-4xl">35!</div>
            </div>
        @endif
</div>
