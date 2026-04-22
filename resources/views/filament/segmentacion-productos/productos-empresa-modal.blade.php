@php use App\Filament\Resources\SegmentacionProductos\SegmentacionProductosResource; @endphp
<div class="overflow-x-auto">
    @if($record->productos->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400">Sin productos asignados.</p>
    @else
        <table class="fi-ta-table w-full table-auto divide-y divide-gray-200 dark:divide-white/5">
            <thead class="divide-y divide-gray-200 dark:divide-white/5">
                <tr class="bg-gray-50 dark:bg-white/5">
                    <th class="fi-ta-header-cell px-3 py-3.5 text-start text-sm font-semibold text-gray-950 dark:text-white">ID</th>
                    <th class="fi-ta-header-cell px-3 py-3.5 text-start text-sm font-semibold text-gray-950 dark:text-white">Nombre</th>
                    <th class="fi-ta-header-cell px-3 py-3.5 text-start text-sm font-semibold text-gray-950 dark:text-white">Descripción</th>
                    <th class="fi-ta-header-cell px-3 py-3.5 text-end text-sm font-semibold text-gray-950 dark:text-white">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                @foreach($record->productos as $producto)
                    <tr class="fi-ta-row transition duration-75 hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="fi-ta-cell px-3 py-4 text-sm text-gray-950 dark:text-white">{{ $producto->id }}</td>
                        <td class="fi-ta-cell px-3 py-4 text-sm text-gray-950 dark:text-white">{{ $producto->nombre }}</td>
                        <td class="fi-ta-cell px-3 py-4 text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($producto->descripcion ?? '', 40) }}</td>
                        <td class="fi-ta-cell px-3 py-4 text-end">
                            <a
                                href="{{ SegmentacionProductosResource::getUrl('editar-producto', ['record' => $record->getKey(), 'producto' => $producto->getKey()]) }}"
                                class="text-primary-600 hover:text-primary-500 hover:underline dark:text-primary-400 dark:hover:text-primary-300 text-sm font-medium"
                            >
                                Editar
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
