<?php

declare(strict_types=1);

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum EstadoVerificacionCuenta: string implements HasColor, HasIcon, HasLabel
{
    case SIN_VERIFICAR = 'sin_verificar';
    case VERIFICADA = 'verificada';
    case RECHAZADA = 'rechazada';

    public function getLabel(): string
    {
        return match ($this) {
            self::SIN_VERIFICAR => 'Sin verificar',
            self::VERIFICADA => 'Verificada',
            self::RECHAZADA => 'Rechazada',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SIN_VERIFICAR => 'warning',
            self::VERIFICADA => 'success',
            self::RECHAZADA => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::SIN_VERIFICAR => 'heroicon-o-clock',
            self::VERIFICADA => 'heroicon-o-check-circle',
            self::RECHAZADA => 'heroicon-o-x-circle',
        };
    }

    public function estaVerificada(): bool
    {
        return $this === self::VERIFICADA;
    }

    public function estaSinVerificar(): bool
    {
        return $this === self::SIN_VERIFICAR;
    }

    public function estaRechazada(): bool
    {
        return $this === self::RECHAZADA;
    }

    public function puedeVerificarse(): bool
    {
        return $this === self::SIN_VERIFICAR;
    }

    public function puedeReenviarse(): bool
    {
        return $this === self::RECHAZADA;
    }
}
