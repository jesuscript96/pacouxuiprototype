<?php

declare(strict_types=1);

namespace App\Services\Tableau;

use App\DTO\Tableau\TableauEmbedSession;
use App\Exceptions\Tableau\TableauReportAccessDeniedException;
use App\Models\Empresa;
use App\Models\User;
use Throwable;

final class TableauReportAccessService
{
    public function __construct(
        private TableauJwtService $jwtService,
        private TableauEmbedUrlBuilder $urlBuilder,
    ) {}

    /**
     * Usuario de Tableau (claim sub) según reglas legacy: analíticas por ubicación → usuario_tableau; si no, email de contacto de la empresa.
     */
    public function resolveTableauUsername(User $user, Empresa $empresa): string
    {
        if (! $user->ver_reportes) {
            throw new TableauReportAccessDeniedException('No tiene permiso para ver analíticos. Active «Ver reportes» en su usuario o consulte a su administrador.');
        }

        if ($empresa->tiene_analiticas_por_ubicacion) {
            $tableauUser = trim((string) $user->usuario_tableau);
            if ($tableauUser === '') {
                throw new TableauReportAccessDeniedException('No tiene un usuario de Tableau asignado para ver analíticos por ubicación. Consulte a su administrador.');
            }

            return $tableauUser;
        }

        $email = trim((string) $empresa->email_contacto);
        if ($email === '') {
            throw new TableauReportAccessDeniedException('La empresa no tiene correo de contacto configurado, necesario para acceder a este informe.');
        }

        return $email;
    }

    public function buildSession(User $user, Empresa $empresa, string $reportKey): TableauEmbedSession
    {
        try {
            $username = $this->resolveTableauUsername($user, $empresa);
            $embedSrc = $this->urlBuilder->embedSrcForReport($reportKey, $empresa);
            $token = $this->jwtService->createEmbedToken($username);

            return new TableauEmbedSession($embedSrc, $token);
        } catch (TableauReportAccessDeniedException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            throw new TableauReportAccessDeniedException('No se pudo generar el acceso al informe. Intente de nuevo más tarde.');
        }
    }

    /**
     * Panel Admin sin tenant: JWT con usuario global (TABLEAU_EMBED_ADMIN_USERNAME), vista sin override por empresa.
     * BL legacy v2: acceso tipo admin sin exigir ver_reportes.
     */
    public function buildSessionForAdminPanel(User $user, string $reportKey): TableauEmbedSession
    {
        if (! $user->puedeAccederAlPanelAdminPaco()) {
            throw new TableauReportAccessDeniedException('No tiene permisos para ver analíticos en el panel de administración.');
        }

        try {
            $username = trim((string) config('tableau.embed_admin_username'));
            if ($username === '') {
                throw new TableauReportAccessDeniedException('Falta configurar el usuario Tableau para administración (TABLEAU_EMBED_ADMIN_USERNAME).');
            }

            $embedSrc = $this->urlBuilder->embedSrcForReportAdmin($reportKey);
            $token = $this->jwtService->createEmbedToken($username);

            return new TableauEmbedSession($embedSrc, $token);
        } catch (TableauReportAccessDeniedException $e) {
            throw $e;
        } catch (Throwable $e) {
            report($e);

            throw new TableauReportAccessDeniedException('No se pudo generar el acceso al informe. Intente de nuevo más tarde.');
        }
    }
}
