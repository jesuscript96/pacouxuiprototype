<?php

declare(strict_types=1);

namespace App\Services\Tableau;

use App\Exceptions\Tableau\TableauReportAccessDeniedException;
use App\Models\Empresa;

final class TableauEmbedUrlBuilder
{
    /**
     * Panel Admin sin tenant: solo ruta por defecto del informe (sin overrides por empresa).
     */
    public function embedSrcForReportAdmin(string $reportKey): string
    {
        $baseUrl = $this->requireBaseUrl();
        $defaultPath = $this->defaultEmbedPathForReport($reportKey);

        return $this->normalizeUrl($baseUrl, $defaultPath);
    }

    /**
     * URL completa del viz: base_url + fragmento de ruta (o URL absoluta si el fragmento ya es http(s)).
     */
    public function embedSrcForReport(string $reportKey, Empresa $empresa): string
    {
        $baseUrl = $this->requireBaseUrl();
        $defaultPath = $this->defaultEmbedPathForReport($reportKey);

        /** @var array<int, string> $overrides */
        $overrides = config('tableau.report_path_overrides.'.$reportKey, []);
        $path = $overrides[$empresa->id] ?? $defaultPath;

        return $this->normalizeUrl($baseUrl, $path);
    }

    private function requireBaseUrl(): string
    {
        $baseUrl = rtrim((string) config('tableau.base_url'), '/');
        if ($baseUrl === '') {
            throw new TableauReportAccessDeniedException('Falta configurar la URL base de Tableau (TABLEAU_URL).');
        }

        return $baseUrl;
    }

    private function defaultEmbedPathForReport(string $reportKey): string
    {
        /** @var array<string, mixed>|null $definition */
        $definition = config('tableau_reports.'.$reportKey);
        if (! is_array($definition) || empty($definition['embed_path'])) {
            throw new TableauReportAccessDeniedException('Este informe no está disponible.');
        }

        return (string) $definition['embed_path'];
    }

    private function normalizeUrl(string $baseUrl, string $pathOrUrl): string
    {
        $pathOrUrl = trim($pathOrUrl);

        if (str_starts_with($pathOrUrl, 'http://') || str_starts_with($pathOrUrl, 'https://')) {
            return $pathOrUrl;
        }

        return $baseUrl.'/'.ltrim($pathOrUrl, '/');
    }
}
