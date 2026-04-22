<?php

namespace App\Providers;

use App\Contracts\ObtenerDestinatariosPushInterface;
use App\Filament\Auth\RedirigirLoginAlPanelClienteResponse;
use App\Models\AreaGeneral;
use App\Models\CandidatoReclutamiento;
use App\Models\CategoriaSolicitud;
use App\Models\DepartamentoGeneral;
use App\Models\PuestoGeneral;
use App\Models\SpatieRole;
use App\Models\TipoSolicitud;
use App\Models\User;
use App\Observers\CandidatoReclutamientoObserver;
use App\Policies\AreaGeneralPolicy;
use App\Policies\CategoriaSolicitudPolicy;
use App\Policies\DepartamentoGeneralPolicy;
use App\Policies\PuestoGeneralPolicy;
use App\Policies\SpatieRolePolicy;
use App\Policies\TipoSolicitudPolicy;
use App\Policies\UsuarioPolicy;
use App\Services\NotificacionesPush\ObtenerDestinatariosReal;
use App\Services\NotificacionesPush\ResolverDestinatariosService;
use App\Services\OneSignal\OneSignalService;
use Filament\Actions\Action;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as FilamentLoginResponseContract;
use Filament\Auth\Http\Responses\LoginResponse as FilamentLoginResponse;
use Filament\Notifications\Livewire\Notifications;
use Filament\Schemas\Components\Form;
use Filament\Support\Enums\Alignment;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\Table;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(OneSignalService::class, function (): OneSignalService {
            return new OneSignalService;
        });

        $this->app->bind(ObtenerDestinatariosPushInterface::class, ObtenerDestinatariosReal::class);

        $this->app->singleton(ResolverDestinatariosService::class);

        // BL: Filament resuelve la clase concreta LoginResponse en el flujo de login.
        $this->app->bind(FilamentLoginResponse::class, RedirigirLoginAlPanelClienteResponse::class);
        $this->app->bind(FilamentLoginResponseContract::class, RedirigirLoginAlPanelClienteResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UsuarioPolicy::class);
        Gate::policy(SpatieRole::class, SpatieRolePolicy::class);
        Gate::policy(CategoriaSolicitud::class, CategoriaSolicitudPolicy::class);
        Gate::policy(TipoSolicitud::class, TipoSolicitudPolicy::class);
        Gate::policy(AreaGeneral::class, AreaGeneralPolicy::class);
        Gate::policy(DepartamentoGeneral::class, DepartamentoGeneralPolicy::class);
        Gate::policy(PuestoGeneral::class, PuestoGeneralPolicy::class);

        CandidatoReclutamiento::observe(CandidatoReclutamientoObserver::class);

        // Solo HTTP: en CLI afectaría artisan serve, queue:work, etc. (bucles largos → fatal a 60s).
        if (! $this->app->runningInConsole()) {
            set_time_limit(60);
        }

        // Notificaciones tostadas: centradas (similar a un modal ligero) y por encima de modales/tablas.
        Notifications::alignment(Alignment::Center);

        // Listados (tablas): acciones por registro solo con icono; el texto va a tooltip y aria-label.
        Table::configureUsing(function (Table $table): void {
            $table->modifyUngroupedRecordActionsUsing(function (Action $action): void {
                $icon = $action->getIcon(default: $action->getTable() ? $action->getTableIcon() : null);

                if (! filled($icon)) {
                    return;
                }

                $action->iconButton()->tooltip($action->getLabel());
            });
        });

        // Formularios Filament (create/edit/wizard): sin validación HTML5 del navegador; los errores
        // se muestran con el mensaje rojo bajo el campo (Livewire + reglas Laravel).
        Form::configureUsing(function (Form $form): void {
            $form->extraAttributes(['novalidate' => true]);
        });

        // Filament v4: quita la línea vertical y los puntos junto a ítems agrupados en el sidebar
        // (clases definidas en filament-panels sidebar/item.blade.php).
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => <<<'HTML'
<style>
    .fi-sidebar-item-grouped-border {
        display: none !important;
    }

    /* Notificaciones Filament: más visibles (centradas vía PHP, tamaño, sombra, z-index sobre modales). */
    .fi-no {
        z-index: 110 !important;
    }

    .fi-no-notification:not(.fi-inline) {
        max-width: min(28rem, 92vw) !important;
        padding: 1.25rem 1.5rem !important;
        border: 2px solid #2b7fff !important;
        box-shadow: 0 25px 50px -12px rgb(0 0 0 / 0.2) !important;
    }

    .fi-no-notification .fi-no-notification-title {
        font-size: 1.125rem !important;
        line-height: 1.3 !important;
    }

    .fi-no-notification .fi-no-notification-body {
        font-size: 0.9375rem !important;
        line-height: 1.5 !important;
    }
</style>
HTML
        );
    }
}
