<?php

declare(strict_types=1);

namespace App\DTO\Tableau;

final readonly class TableauEmbedSession
{
    public function __construct(
        public string $embedSrc,
        public string $token,
    ) {}
}
