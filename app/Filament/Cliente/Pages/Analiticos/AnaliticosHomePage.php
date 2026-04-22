<?php

declare(strict_types=1);

namespace App\Filament\Cliente\Pages\Analiticos;

use App\Filament\Cliente\Navigation\UxPrototypeParentNavigationItems;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Support\Enums\Width;
use UnitEnum;

class AnaliticosHomePage extends Page
{
    protected string $view = 'filament.cliente.pages.analiticos.analiticos-home';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'Analíticos · Showroom';

    protected static ?string $title = 'Analíticos';

    protected Width|string|null $maxContentWidth = Width::Full;

    public static function getSlug(?Panel $panel = null): string
    {
        return 'analiticos';
    }

    public string $seccion = 'resumen';

    /**
     * @return array<int, array{id: string, label: string, icono: string, descripcion: string}>
     */
    public array $secciones = [];

    public function mount(): void
    {
        $this->secciones = [
            ['id' => 'resumen', 'label' => 'Resumen', 'icono' => 'heroicon-o-squares-2x2', 'descripcion' => 'KPIs principales y visión ejecutiva'],
            ['id' => 'rotacion', 'label' => 'Rotación', 'icono' => 'heroicon-o-arrow-path-rounded-square', 'descripcion' => 'Altas, bajas y motivos'],
            ['id' => 'engagement', 'label' => 'Engagement', 'icono' => 'heroicon-o-heart', 'descripcion' => 'Actividad, adopción y uso'],
            ['id' => 'demograficos', 'label' => 'Demográficos', 'icono' => 'heroicon-o-user-group', 'descripcion' => 'Composición de la plantilla'],
            ['id' => 'encuestas', 'label' => 'Encuestas', 'icono' => 'heroicon-o-clipboard-document-check', 'descripcion' => 'eNPS, clima y satisfacción'],
        ];
    }

    public function cambiarSeccion(string $id): void
    {
        $valid = array_column($this->secciones, 'id');

        if (in_array($id, $valid, true)) {
            $this->seccion = $id;
        }
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'UX prototype';
    }

    public static function getNavigationParentItem(): ?string
    {
        return UxPrototypeParentNavigationItems::ANALITICOS;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
