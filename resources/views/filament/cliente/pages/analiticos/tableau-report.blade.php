<x-filament-panels::page>
    @if (filled($this->accessError))
        <x-filament::section>
            <x-slot name="heading">
                No se puede mostrar el informe
            </x-slot>
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ $this->accessError }}
            </p>
        </x-filament::section>
    @else
        <div
            id="tableau-embed-wrapper"
            class="space-y-4"
            wire:ignore
        >
            {{-- hidden + display:none: evita que role="alert" u otros estilos anulen .hidden de Tailwind --}}
            <div
                id="tableau-embed-error-banner"
                hidden
                class="hidden rounded-xl border border-red-300 bg-red-50 p-4 dark:border-red-800 dark:bg-red-950/40"
                style="display: none !important;"
                role="alert"
            >
                <p class="text-sm font-semibold text-red-900 dark:text-red-100">
                    Error al cargar el informe en Tableau
                </p>
                <p
                    class="mt-1 text-sm text-red-800 dark:text-red-200"
                    data-tableau-error-text
                ></p>
                <p class="mt-2 text-xs text-red-700/90 dark:text-red-300/90">
                    Suele deberse a que el usuario de Tableau no existe, no tiene permiso sobre la vista, el JWT o la Connected App no coinciden con el sitio, o la sesión devolvió 401 (no autorizado).
                </p>
            </div>

            <div
                class="w-full overflow-hidden rounded-2xl shadow-xl ring-1 ring-gray-950/5 dark:ring-white/10"
                style="min-height: 870px;"
            >
                <tableau-viz
                    id="tableau-viz"
                    src="{{ $this->embedSrc }}"
                    token="{{ $this->embedToken }}"
                    width="100%"
                    height="870"
                ></tableau-viz>
            </div>
        </div>
    @endif
</x-filament-panels::page>

@push('scripts')
    <script type="module" src="{{ config('tableau.embedding_script_url') }}"></script>
    <script type="module">
        import { TableauEventType } from {!! \Illuminate\Support\Js::from(config('tableau.embedding_script_url')) !!};

        const vizLoadErrorType = TableauEventType?.VizLoadError ?? 'vizloaderror';
        const firstInteractiveType = TableauEventType?.FirstInteractive ?? 'firstinteractive';
        const VIZ_ERROR_CONFIRM_MS = 1500;

        function parseTableauErrorMessage(raw) {
            if (raw == null || raw === '') {
                return null;
            }
            const str = String(raw);
            try {
                const parsed = JSON.parse(str);
                if (parsed && typeof parsed === 'object') {
                    return (
                        parsed.errorMessage ??
                        parsed.message ??
                        parsed.error ??
                        str
                    );
                }
            } catch {
                /* mensaje plano */
            }

            return str;
        }

        function buildErrorMessage(detail) {
            let text =
                parseTableauErrorMessage(detail?.message) ??
                'Tableau rechazó la carga del informe (por ejemplo 401 Unauthorized en sign-in). Revise usuario embebido, permisos y Connected App.';

            if (detail?.errorCode !== undefined && detail?.errorCode !== null && detail?.errorCode !== '') {
                text += ` [código Tableau: ${detail.errorCode}]`;
            }

            return text;
        }

        function revealErrorBanner(message) {
            const banner = document.getElementById('tableau-embed-error-banner');
            const textEl = banner?.querySelector('[data-tableau-error-text]');
            if (!banner || !textEl) {
                return;
            }
            textEl.textContent = message;
            banner.removeAttribute('hidden');
            banner.classList.remove('hidden');
            banner.style.removeProperty('display');
        }

        function hideErrorBanner() {
            const banner = document.getElementById('tableau-embed-error-banner');
            if (!banner) {
                return;
            }
            banner.setAttribute('hidden', '');
            banner.classList.add('hidden');
            banner.style.setProperty('display', 'none', 'important');
        }

        function showConfirmedError(detail, vizEl) {
            if (vizEl?.dataset.tableauEmbedErrorShown === '1') {
                return;
            }
            if (vizEl) {
                vizEl.dataset.tableauEmbedErrorShown = '1';
            }
            revealErrorBanner(buildErrorMessage(detail));
        }

        async function attachTableauVizListeners() {
            try {
                await customElements.whenDefined('tableau-viz');
            } catch {
                return;
            }

            const viz = document.getElementById('tableau-viz');
            if (!viz) {
                return;
            }

            let errorConfirmTimer = null;
            let pendingDetail = null;

            viz.addEventListener(firstInteractiveType, () => {
                viz.dataset.tableauVizInteractive = '1';
                delete viz.dataset.tableauEmbedErrorShown;
                if (errorConfirmTimer !== null) {
                    clearTimeout(errorConfirmTimer);
                    errorConfirmTimer = null;
                    pendingDetail = null;
                }
                hideErrorBanner();
            });

            viz.addEventListener(vizLoadErrorType, (event) => {
                if (viz.dataset.tableauVizInteractive === '1') {
                    return;
                }
                pendingDetail = event.detail;
                if (errorConfirmTimer !== null) {
                    clearTimeout(errorConfirmTimer);
                }
                errorConfirmTimer = window.setTimeout(() => {
                    errorConfirmTimer = null;
                    if (viz.dataset.tableauVizInteractive === '1') {
                        pendingDetail = null;

                        return;
                    }
                    const detail = pendingDetail;
                    pendingDetail = null;
                    if (detail !== undefined) {
                        showConfirmedError(detail, viz);
                    }
                }, VIZ_ERROR_CONFIRM_MS);
            });
        }

        attachTableauVizListeners();
    </script>
@endpush
