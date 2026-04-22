<?php

declare(strict_types=1);

namespace App\Services\Tableau;

use App\Exceptions\Tableau\TableauReportAccessDeniedException;
use Firebase\JWT\JWT;

final class TableauJwtService
{
    /**
     * Genera un JWT para incrustar vistas Tableau (Connected App, HS256).
     *
     * @see https://help.tableau.com/current/online/en-us/connected_apps_eas.htm
     */
    public function createEmbedToken(string $tableauUsername): string
    {
        $clientId = (string) config('tableau.connected_app.client_id');
        $secretId = (string) config('tableau.connected_app.secret_id');
        $secretKey = (string) config('tableau.connected_app.secret_key');

        if ($clientId === '' || $secretId === '' || $secretKey === '') {
            throw new TableauReportAccessDeniedException('La integración con Tableau no está configurada correctamente en el servidor.');
        }

        $ttlMinutes = max(1, (int) config('tableau.token_ttl_minutes', 10));
        $now = time();

        $payload = [
            'iss' => $clientId,
            'exp' => $now + ($ttlMinutes * 60),
            'jti' => uniqid('', true),
            'aud' => 'tableau',
            'sub' => $tableauUsername,
            'scp' => ['tableau:views:embed'],
        ];

        return JWT::encode(
            $payload,
            $secretKey,
            'HS256',
            $secretId,
            ['iss' => $clientId],
        );
    }
}
