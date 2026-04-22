@extends('postulacion.layout')

@section('title', 'Postulación enviada')

@section('content')
<div class="bg-white shadow rounded-lg overflow-hidden text-center py-12 px-6">
    <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100">
        <svg class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
    </div>

    <h1 class="mt-6 text-2xl font-bold text-gray-900">Postulación enviada</h1>

    <p class="mt-4 text-gray-600">
        Tu postulación para <strong>{{ $vacante }}</strong> en <strong>{{ $empresa }}</strong>
        ha sido recibida correctamente.
    </p>

    <p class="mt-2 text-sm text-gray-500">
        El equipo de recursos humanos revisará tu información y se pondrá en contacto contigo.
    </p>

    <div class="mt-8">
        <a
            href="/"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
            Volver al inicio
        </a>
    </div>
</div>
@endsection
