@props([
    'title' => '',
    'description' => '',
    'cols' => 1,
])

<div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up transition-shadow duration-300 hover:shadow-lg hover:shadow-indigo-500/10">
    @if ($title)
        <div class="mb-6">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">{{ $title }}</h3>
            @if ($description)
                <p class="mt-1.5 text-sm leading-relaxed text-slate-500">{{ $description }}</p>
            @endif
        </div>
    @endif

    <div @class([
        'grid gap-4 sm:gap-5',
        'grid-cols-1' => $cols == 1,
        'grid-cols-1 sm:grid-cols-2' => $cols == 2,
        'grid-cols-1 sm:grid-cols-2 lg:grid-cols-3' => $cols == 3,
        'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4' => $cols == 4,
        'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-6' => $cols == 6,
    ])>
        {{ $slot }}
    </div>
</div>
