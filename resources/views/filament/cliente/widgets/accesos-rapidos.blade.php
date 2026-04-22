<x-filament-widgets::widget>
    <div class="dash-showroom space-y-5">
        <div class="dash-section-title px-1">
            <span class="dash-section-eyebrow">Accesos directos</span>
            <span class="dash-section-rule"></span>
        </div>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-7 sm:gap-4">
            @foreach ($this->getLinks() as $link)
                <a
                    href="{{ $link['url'] }}"
                    @class([
                        'group flex flex-col items-center gap-2 rounded-2xl border p-4 text-center transition-all duration-300 hover:shadow-lg hover:-translate-y-0.5 sm:gap-3 sm:p-5',
                        'border-primary-200/60 bg-white hover:border-primary-300 hover:shadow-primary-500/10' => $link['color'] === 'primary',
                        'border-red-200/60 bg-white hover:border-red-300 hover:shadow-red-500/10' => $link['color'] === 'danger',
                        'border-green-200/60 bg-white hover:border-green-300 hover:shadow-green-500/10' => $link['color'] === 'success',
                        'border-amber-200/60 bg-white hover:border-amber-300 hover:shadow-amber-500/10' => $link['color'] === 'warning',
                        'border-sky-200/60 bg-white hover:border-sky-300 hover:shadow-sky-500/10' => $link['color'] === 'info',
                        'border-slate-200/60 bg-white hover:border-slate-300 hover:shadow-slate-500/10' => $link['color'] === 'gray',
                    ])
                >
                    <div
                        @class([
                            'flex h-11 w-11 items-center justify-center rounded-xl transition-all duration-300 group-hover:scale-110 group-hover:shadow-md sm:h-12 sm:w-12',
                            'bg-primary-100 group-hover:shadow-primary-500/20' => $link['color'] === 'primary',
                            'bg-red-100 group-hover:shadow-red-500/20' => $link['color'] === 'danger',
                            'bg-green-100 group-hover:shadow-green-500/20' => $link['color'] === 'success',
                            'bg-amber-100 group-hover:shadow-amber-500/20' => $link['color'] === 'warning',
                            'bg-sky-100 group-hover:shadow-sky-500/20' => $link['color'] === 'info',
                            'bg-slate-200 group-hover:shadow-slate-500/20' => $link['color'] === 'gray',
                        ])
                    >
                        <x-filament::icon
                            :icon="$link['icon']"
                            @class([
                                'h-5 w-5',
                                'text-primary-600' => $link['color'] === 'primary',
                                'text-red-600' => $link['color'] === 'danger',
                                'text-green-600' => $link['color'] === 'success',
                                'text-amber-600' => $link['color'] === 'warning',
                                'text-sky-600' => $link['color'] === 'info',
                                'text-slate-500' => $link['color'] === 'gray',
                            ])
                        />
                    </div>

                    <div class="min-w-0 w-full">
                        <p
                            @class([
                                'text-sm font-semibold leading-tight',
                                'text-primary-800' => $link['color'] === 'primary',
                                'text-red-800' => $link['color'] === 'danger',
                                'text-green-800' => $link['color'] === 'success',
                                'text-amber-800' => $link['color'] === 'warning',
                                'text-sky-800' => $link['color'] === 'info',
                                'text-slate-700' => $link['color'] === 'gray',
                            ])
                        >{{ $link['label'] }}</p>
                        <p class="mt-0.5 hidden text-xs leading-tight text-slate-400 sm:block">{{ $link['description'] }}</p>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
