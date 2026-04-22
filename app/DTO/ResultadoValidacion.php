<?php

namespace App\DTO;

final readonly class ResultadoValidacion
{
    /**
     * @param  array<string, mixed>|null  $datosExtraidos
     */
    public function __construct(
        public bool $isValid,
        public bool $dataIsValid,
        public ?array $datosExtraidos = null,
        public ?string $error = null,
        public ?float $score = null,
    ) {}

    /**
     * @param  array<string, mixed>  $datosExtraidos
     */
    public static function exitoso(array $datosExtraidos = [], ?float $score = null): self
    {
        return new self(
            isValid: true,
            dataIsValid: true,
            datosExtraidos: $datosExtraidos,
            score: $score,
        );
    }

    /**
     * @param  array<string, mixed>  $datosExtraidos
     */
    public static function documentoValido(bool $datosCoinciden, array $datosExtraidos = []): self
    {
        return new self(
            isValid: true,
            dataIsValid: $datosCoinciden,
            datosExtraidos: $datosExtraidos,
        );
    }

    public static function fallido(string $error): self
    {
        return new self(
            isValid: false,
            dataIsValid: false,
            error: $error,
        );
    }

    public static function sinValidar(): self
    {
        return new self(
            isValid: false,
            dataIsValid: false,
            error: 'Validación no implementada',
        );
    }

    /**
     * @return array{is_valid: bool, data_is_valid: bool, datos_extraidos: array<string, mixed>|null, error: string|null, score: float|null, validated_at: string}
     */
    public function toArray(): array
    {
        return [
            'is_valid' => $this->isValid,
            'data_is_valid' => $this->dataIsValid,
            'datos_extraidos' => $this->datosExtraidos,
            'error' => $this->error,
            'score' => $this->score,
            'validated_at' => now()->toIso8601String(),
        ];
    }
}
