<x-filament-panels::page>
    <x-filament.cliente.storybook-shell>
        <div class="space-y-10 sm:space-y-12">

        @php
            $pacoColors = [
                ['label' => 'Azul marca (trazo)', 'token' => '--primary / paco-blue', 'hex' => '#3148c8'],
                ['label' => 'Azul suave (relleno)', 'token' => 'paco-blue-soft', 'hex' => '#cad6fb'],
                ['label' => 'Azul profundo', 'token' => 'paco-blue-deep', 'hex' => '#2436a3'],
                ['label' => 'Coral / acento', 'token' => 'paco-accent', 'hex' => '#fb4f33'],
            ];
            $grayColors = [
                ['label' => 'Gris Niebla', 'token' => 'gray-niebla', 'hex' => '#eef1fc'],
                ['label' => 'Gris Humo', 'token' => 'gray-humo', 'hex' => '#dde3f0'],
                ['label' => 'Gris Pizarra', 'token' => 'gray-pizarra', 'hex' => '#5c6488'],
                ['label' => 'Carbón', 'token' => 'gray-carbon', 'hex' => '#1a1f2e'],
            ];
            $emphasisColors = [
                ['label' => 'Rojo', 'token' => 'emphasis-red', 'hex' => '#E53935'],
                ['label' => 'Amarillo', 'token' => 'emphasis-yellow', 'hex' => '#F9A825'],
                ['label' => 'Verde', 'token' => 'emphasis-green', 'hex' => '#2E7D32'],
                ['label' => 'Violeta', 'token' => 'emphasis-violet', 'hex' => '#6A1B9A'],
                ['label' => 'Mora', 'token' => 'emphasis-mora', 'hex' => '#4A148C'],
            ];
            $sidebarColors = [
                ['label' => 'Sidebar fondo', 'token' => '--sidebar-bg', 'hex' => '#eef1f8'],
                ['label' => 'Sidebar borde', 'token' => '--sidebar-border', 'hex' => '#d8dde8'],
                ['label' => 'Sidebar hover', 'token' => '--sidebar-hover-bg', 'hex' => '#e0e6f4'],
                ['label' => 'Sidebar activo', 'token' => '--sidebar-active-bg', 'hex' => 'rgba(49,72,200,0.10)'],
                ['label' => 'Texto muted', 'token' => '--sidebar-text-muted', 'hex' => '#94a3b8'],
                ['label' => 'Texto base', 'token' => '--sidebar-text', 'hex' => '#64748b'],
                ['label' => 'Texto hover', 'token' => '--sidebar-text-hover', 'hex' => '#1e293b'],
                ['label' => 'Texto activo', 'token' => '--sidebar-text-active', 'hex' => '#3148c8'],
            ];
            $contentColors = [
                ['label' => 'Fondo contenido', 'token' => '--content-bg', 'hex' => '#f7f9fc'],
                ['label' => 'Card fondo', 'token' => '--card-bg', 'hex' => '#ffffff'],
                ['label' => 'Card borde', 'token' => '--card-border', 'hex' => '#e4e8f0'],
                ['label' => 'Topbar fondo', 'token' => '--topbar-bg', 'hex' => '#ffffff'],
            ];
        @endphp

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Colores principales</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Azules de marca y acento coral</p>
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ($pacoColors as $c)
                    <div>
                        <div class="sb-swatch h-20 w-full rounded-xl border border-slate-200 shadow-sm" style="background-color: {{ $c['hex'] }}"></div>
                        <p class="mt-2 text-sm font-semibold text-slate-800">{{ $c['label'] }}</p>
                        <p class="font-mono text-xs text-slate-500">{{ $c['hex'] }}</p>
                        <p class="font-mono text-xs text-slate-400 italic">{{ $c['token'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Escala de grises</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Grises del design system: niebla, humo, pizarra, carbón</p>
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ($grayColors as $c)
                    <div>
                        <div class="sb-swatch h-20 w-full rounded-xl border border-slate-200 shadow-sm" style="background-color: {{ $c['hex'] }}"></div>
                        <p class="mt-2 text-sm font-semibold text-slate-800">{{ $c['label'] }}</p>
                        <p class="font-mono text-xs text-slate-500">{{ $c['hex'] }}</p>
                        <p class="font-mono text-xs text-slate-400 italic">{{ $c['token'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Colores de énfasis</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Para alertas, estados y acciones especiales</p>
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-5">
                @foreach ($emphasisColors as $c)
                    <div>
                        <div class="sb-swatch h-20 w-full rounded-xl border border-slate-200 shadow-sm" style="background-color: {{ $c['hex'] }}"></div>
                        <p class="mt-2 text-sm font-semibold text-slate-800">{{ $c['label'] }}</p>
                        <p class="font-mono text-xs text-slate-500">{{ $c['hex'] }}</p>
                        <p class="font-mono text-xs text-slate-400 italic">{{ $c['token'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Sidebar</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Variables CSS definidas en filament-sidebar-overrides.css</p>
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ($sidebarColors as $c)
                    <div>
                        <div class="sb-swatch h-20 w-full rounded-xl border border-slate-200 shadow-sm" style="background-color: {{ $c['hex'] }}"></div>
                        <p class="mt-2 text-sm font-semibold text-slate-800">{{ $c['label'] }}</p>
                        <p class="font-mono text-xs text-slate-500">{{ $c['hex'] }}</p>
                        <p class="font-mono text-xs text-slate-400 italic">{{ $c['token'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 sb-panel sb-fade-up">
            <h3 class="text-lg font-semibold tracking-tight text-slate-900">Contenido y tarjetas</h3>
            <p class="mt-1.5 text-sm leading-relaxed text-slate-500">Fondos y bordes del área principal</p>
            <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
                @foreach ($contentColors as $c)
                    <div>
                        <div class="sb-swatch h-20 w-full rounded-xl border border-slate-200 shadow-sm" style="background-color: {{ $c['hex'] }}"></div>
                        <p class="mt-2 text-sm font-semibold text-slate-800">{{ $c['label'] }}</p>
                        <p class="font-mono text-xs text-slate-500">{{ $c['hex'] }}</p>
                        <p class="font-mono text-xs text-slate-400 italic">{{ $c['token'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        </div>
    </x-filament.cliente.storybook-shell>
</x-filament-panels::page>
