<?php

namespace App\Filament\Cliente\Widgets;

use App\Filament\Cliente\Pages\Storybook\BadgesPage;
use App\Filament\Cliente\Pages\Storybook\DegradadosPage;
use App\Filament\Cliente\Pages\Storybook\EnfasisPage;
use App\Filament\Cliente\Pages\Storybook\MarcaPage;
use App\Filament\Cliente\Pages\Storybook\NotificacionesPage;
use App\Filament\Cliente\Pages\Storybook\TablasEstiloNotionPage;
use App\Filament\Cliente\Pages\Storybook\TarjetasPage;
use Filament\Widgets\Widget;

class ExploraStorybookWidget extends Widget
{
    protected static bool $isDiscovered = false;

    protected string $view = 'filament.cliente.widgets.explora-storybook';

    protected int|string|array $columnSpan = 'full';

    /**
     * @return array<int, array{label: string, descripcion: string, url: string, icono: string, tono: string}>
     */
    public function getEnlaces(): array
    {
        return [
            [
                'label' => 'Tarjetas',
                'descripcion' => 'Hero cards, metric cards y accesos directos',
                'url' => TarjetasPage::getUrl(),
                'icono' => 'heroicon-o-credit-card',
                'tono' => 'indigo',
            ],
            [
                'label' => 'Degradados',
                'descripcion' => 'Gradientes institucionales y radial',
                'url' => DegradadosPage::getUrl(),
                'icono' => 'heroicon-o-swatch',
                'tono' => 'violet',
            ],
            [
                'label' => 'Énfasis',
                'descripcion' => 'Colores semánticos, badges y chips',
                'url' => EnfasisPage::getUrl(),
                'icono' => 'heroicon-o-sparkles',
                'tono' => 'emerald',
            ],
            [
                'label' => 'Notificaciones',
                'descripcion' => 'Toasts, banners y mensajes',
                'url' => NotificacionesPage::getUrl(),
                'icono' => 'heroicon-o-bell-alert',
                'tono' => 'amber',
            ],
            [
                'label' => 'Tablas Notion',
                'descripcion' => 'Tablas inline editables estilo Notion',
                'url' => TablasEstiloNotionPage::getUrl(),
                'icono' => 'heroicon-o-table-cells',
                'tono' => 'sky',
            ],
            [
                'label' => 'Badges',
                'descripcion' => 'Sistema de etiquetas y estados',
                'url' => BadgesPage::getUrl(),
                'icono' => 'heroicon-o-tag',
                'tono' => 'rose',
            ],
            [
                'label' => 'Marca',
                'descripcion' => 'Logo, colores y reglas de uso',
                'url' => MarcaPage::getUrl(),
                'icono' => 'heroicon-o-identification',
                'tono' => 'slate',
            ],
        ];
    }
}
