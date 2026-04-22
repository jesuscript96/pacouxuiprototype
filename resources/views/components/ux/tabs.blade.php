@props([
    'tabs' => [],
    'alpineModel' => 'activeTab',
])

{{-- Tabs sticky estilo Analíticos (que el usuario dijo que está perfecto). --}}
<div class="ux-tabs sticky top-0 z-20 -mx-4 sm:-mx-6 lg:-mx-8">
    <div class="mx-4 rounded-2xl border border-slate-200 bg-white/80 p-2 shadow-sm backdrop-blur-md sm:mx-6 lg:mx-8">
        <nav class="flex flex-wrap items-stretch gap-1" role="tablist">
            @foreach ($tabs as $tab)
                <button
                    type="button"
                    x-on:click="{{ $alpineModel }} = '{{ $tab['id'] }}'"
                    role="tab"
                    x-bind:aria-selected="{{ $alpineModel }} === '{{ $tab['id'] }}' ? 'true' : 'false'"
                    class="group flex flex-1 min-w-[8rem] items-center gap-2.5 rounded-xl px-3.5 py-2.5 text-sm font-semibold transition-all duration-200"
                    x-bind:class="{{ $alpineModel }} === '{{ $tab['id'] }}'
                        ? 'bg-[#3148c8] text-white shadow-md ring-1 ring-[#3148c8]/25'
                        : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'"
                >
                    <span
                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg transition-colors"
                        x-bind:class="{{ $alpineModel }} === '{{ $tab['id'] }}' ? 'bg-white/20' : 'bg-slate-100 group-hover:bg-white'"
                    >
                        <x-filament::icon
                            :icon="$tab['icon'] ?? 'heroicon-o-rectangle-group'"
                            class="h-4 w-4"
                            x-bind:class="{{ $alpineModel }} === '{{ $tab['id'] }}' ? 'text-white' : 'text-slate-500'"
                        />
                    </span>
                    <span class="flex flex-col items-start leading-tight">
                        <span>{{ $tab['label'] }}</span>
                        @if (! empty($tab['description']))
                            <span
                                class="text-[10.5px] font-normal normal-case tracking-normal"
                                x-bind:class="{{ $alpineModel }} === '{{ $tab['id'] }}' ? 'text-white/75' : 'text-slate-400'"
                            >{{ $tab['description'] }}</span>
                        @endif
                    </span>
                </button>
            @endforeach
        </nav>
    </div>
</div>
