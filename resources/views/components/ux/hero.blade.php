@props([
    'eyebrow' => null,
    'title',
    'description' => null,
    'icon' => 'heroicon-o-sparkles',
    'stat' => null,
])

{{-- Hero sobrio tipo dash-glass-hero del Storybook: translúcido, vidrio, sin degradado radial fuerte. --}}
<div class="ux-hero dash-glass-hero relative overflow-hidden rounded-3xl p-6 text-slate-800 sm:p-8">
    <div class="pointer-events-none absolute -right-28 -top-28 h-80 w-80 rounded-full bg-indigo-400/[0.06] blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-32 -left-20 h-96 w-96 rounded-full bg-slate-300/[0.12] blur-3xl"></div>

    <div class="relative grid grid-cols-1 gap-6 lg:grid-cols-[1fr_auto] lg:items-center">
        <div>
            @if ($eyebrow)
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                    <span class="inline-flex h-2 w-2 rounded-full bg-emerald-500 ring-4 ring-emerald-500/15"></span>
                    {{ $eyebrow }}
                </div>
            @endif

            <h1 class="mt-3 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl lg:text-[2rem]">{{ $title }}</h1>

            @if ($description)
                <p class="mt-2 max-w-2xl text-sm leading-relaxed text-slate-600 sm:text-base">{{ $description }}</p>
            @endif
        </div>

        @if ($stat)
            <div class="flex items-center gap-3 rounded-2xl border border-white/60 bg-white/45 p-4 shadow-sm ring-1 ring-slate-200/50 backdrop-blur-xl">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl border border-slate-200/70 bg-white/70 text-[#3148c8] shadow-sm">
                    <x-filament::icon :icon="$icon" class="h-6 w-6" />
                </div>
                <div>
                    @if (! empty($stat['label']))
                        <p class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $stat['label'] }}</p>
                    @endif
                    <p class="text-2xl font-extrabold tabular-nums text-slate-900">{{ $stat['value'] ?? '' }}</p>
                    @if (! empty($stat['hint']))
                        <p class="text-[11px] text-slate-600">{{ $stat['hint'] }}</p>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{ $slot }}
</div>
