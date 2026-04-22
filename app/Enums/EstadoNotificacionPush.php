<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoNotificacionPush: string implements HasColor, HasIcon, HasLabel
{
    case BORRADOR = 'borrador';
    case PROGRAMADA = 'programada';
    case ENVIANDO = 'enviando';
    case ENVIADA = 'enviada';
    case FALLIDA = 'fallida';
    case CANCELADA = 'cancelada';

    public function getLabel(): string
    {
        return match ($this) {
            self::BORRADOR => 'Borrador',
            self::PROGRAMADA => 'Programada',
            self::ENVIANDO => 'Enviando...',
            self::ENVIADA => 'Enviada',
            self::FALLIDA => 'Fallida',
            self::CANCELADA => 'Cancelada',
        };
    }

    /**
     * @return string | array<string> | null
     */
    public function getColor(): string|array|null
    {
        return match ($this) {
            self::BORRADOR => 'gray',
            self::PROGRAMADA => 'info',
            self::ENVIANDO => 'warning',
            self::ENVIADA => 'success',
            self::FALLIDA => 'danger',
            self::CANCELADA => 'gray',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::BORRADOR => 'heroicon-o-pencil',
            self::PROGRAMADA => 'heroicon-o-clock',
            self::ENVIANDO => 'heroicon-o-arrow-path',
            self::ENVIADA => 'heroicon-o-check-circle',
            self::FALLIDA => 'heroicon-o-x-circle',
            self::CANCELADA => 'heroicon-o-no-symbol',
        };
    }

    public function esEditable(): bool
    {
        return in_array($this, [self::BORRADOR, self::PROGRAMADA], true);
    }

    public function esCancelable(): bool
    {
        return in_array($this, [self::BORRADOR, self::PROGRAMADA], true);
    }

    public function puedeEnviarse(): bool
    {
        return in_array($this, [self::BORRADOR, self::PROGRAMADA], true);
    }
}
