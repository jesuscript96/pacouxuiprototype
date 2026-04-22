@extends('postulacion.layout')

@section('title', $vacante->puesto)

@section('content')
<div class="bg-white shadow rounded-lg overflow-hidden">
    <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-8 text-white">
        <h1 class="text-2xl font-bold">{{ $vacante->puesto }}</h1>
        <p class="mt-1 text-blue-100">{{ $empresa->nombre }}</p>
    </div>

    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <details class="group">
            <summary class="cursor-pointer text-sm font-medium text-gray-700 hover:text-gray-900">
                Ver detalles de la vacante
            </summary>
            <div class="mt-4 space-y-4 text-sm text-gray-600">
                <div>
                    <h3 class="font-semibold text-gray-900">Requisitos</h3>
                    <div class="prose prose-sm max-w-none">{!! $vacante->requisitos !!}</div>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Aptitudes</h3>
                    <div class="prose prose-sm max-w-none">{!! $vacante->aptitudes !!}</div>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-900">Prestaciones</h3>
                    <div class="prose prose-sm max-w-none">{!! $vacante->prestaciones !!}</div>
                </div>
            </div>
        </details>
    </div>

    @if ($errors->any())
        <div class="px-6 py-4 bg-red-50 border-b border-red-200">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Por favor corrige los siguientes errores:</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form
        method="POST"
        action="{{ route('postulacion.enviar', [$empresa, $vacante]) }}"
        enctype="multipart/form-data"
        x-data="formularioPostulacion()"
        class="px-6 py-6 space-y-6"
    >
        @csrf

        @foreach ($vacante->camposFormulario as $campo)
            @php
                $inputName = "campos[{$campo->nombre}]";
                $inputId = "campo_{$campo->nombre}";
                $oldValue = old("campos.{$campo->nombre}");
                $hasError = $errors->has("campos.{$campo->nombre}");
                $inputClass = 'mt-1 block w-full rounded-md shadow-sm sm:text-sm '
                    . ($hasError
                        ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                        : 'border-gray-300 focus:border-blue-500 focus:ring-blue-500');
            @endphp

            <div
                @if ($campo->es_dependiente)
                    x-show="campos['{{ $campo->campo_padre }}'] === '{{ $campo->valor_activador }}'"
                    x-cloak
                @endif
                class="space-y-1"
            >
                <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700">
                    {{ $campo->etiqueta }}
                    @if ($campo->requerido)
                        <span class="text-red-500">*</span>
                    @endif
                </label>

                @switch($campo->tipo)
                    @case('text')
                    @case('email')
                    @case('phone')
                    @case('number')
                        <input
                            type="{{ $campo->tipo === 'phone' ? 'tel' : $campo->tipo }}"
                            name="{{ $inputName }}"
                            id="{{ $inputId }}"
                            value="{{ $oldValue }}"
                            placeholder="{{ $campo->placeholder }}"
                            x-model="campos['{{ $campo->nombre }}']"
                            @if ($campo->longitud_minima) minlength="{{ $campo->longitud_minima }}" @endif
                            @if ($campo->longitud_maxima) maxlength="{{ $campo->longitud_maxima }}" @endif
                            @if ($campo->requerido && !$campo->es_dependiente) required @endif
                            class="{{ $inputClass }}"
                        >
                        @break

                    @case('textarea')
                        <textarea
                            name="{{ $inputName }}"
                            id="{{ $inputId }}"
                            placeholder="{{ $campo->placeholder }}"
                            x-model="campos['{{ $campo->nombre }}']"
                            rows="4"
                            @if ($campo->longitud_minima) minlength="{{ $campo->longitud_minima }}" @endif
                            @if ($campo->longitud_maxima) maxlength="{{ $campo->longitud_maxima }}" @endif
                            @if ($campo->requerido && !$campo->es_dependiente) required @endif
                            class="{{ $inputClass }}"
                        >{{ $oldValue }}</textarea>
                        @break

                    @case('date')
                        <input
                            type="date"
                            name="{{ $inputName }}"
                            id="{{ $inputId }}"
                            value="{{ $oldValue }}"
                            x-model="campos['{{ $campo->nombre }}']"
                            @if ($campo->requerido && !$campo->es_dependiente) required @endif
                            class="{{ $inputClass }}"
                        >
                        @break

                    @case('select')
                        <select
                            name="{{ $inputName }}"
                            id="{{ $inputId }}"
                            x-model="campos['{{ $campo->nombre }}']"
                            @if ($campo->requerido && !$campo->es_dependiente) required @endif
                            class="{{ $inputClass }}"
                        >
                            <option value="">{{ $campo->placeholder ?: 'Selecciona una opción' }}</option>
                            @foreach ($campo->opciones ?? [] as $opcion)
                                <option value="{{ $opcion }}" {{ $oldValue === $opcion ? 'selected' : '' }}>
                                    {{ $opcion }}
                                </option>
                            @endforeach
                        </select>
                        @break

                    @case('file')
                        <div class="mt-1">
                            <input
                                type="file"
                                name="archivos[{{ $campo->nombre }}]"
                                id="{{ $inputId }}"
                                @if ($campo->tipos_archivo) accept="{{ $campo->tipos_archivo }}" @endif
                                @if ($campo->requerido && !$campo->es_dependiente) required @endif
                                class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-md file:border-0
                                    file:text-sm file:font-medium
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100
                                    {{ $hasError ? 'border-red-300' : 'border-gray-300' }}"
                            >
                            @if ($campo->tipos_archivo)
                                <p class="mt-1 text-xs text-gray-500">
                                    Formatos permitidos: {{ str_replace(['image/', 'application/'], '', $campo->tipos_archivo) }}
                                </p>
                            @endif
                        </div>
                        @break
                @endswitch

                @error("campos.{$campo->nombre}")
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
                @error("archivos.{$campo->nombre}")
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        <div class="pt-4">
            <button
                type="submit"
                class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
            >
                Enviar postulación
            </button>
        </div>
    </form>
</div>

<script>
    function formularioPostulacion() {
        return {
            campos: {
                @foreach ($vacante->camposFormulario as $campo)
                    '{{ $campo->nombre }}': '{{ old("campos.{$campo->nombre}", "") }}',
                @endforeach
            }
        }
    }
</script>
@endsection
