<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido/a a {{ $nombreApp }}</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f4f7; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color: #f4f4f7;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">

                    {{-- Header con logo --}}
                    <tr>
                        <td align="center" style="padding: 32px 40px 24px; background-color: #1a1a2e;">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="{{ $empresaNombre }}" style="max-height: 60px; max-width: 200px;">
                            @else
                                <span style="font-size: 28px; font-weight: 700; color: #ffffff; letter-spacing: -0.5px;">{{ $nombreApp }}</span>
                            @endif
                        </td>
                    </tr>

                    {{-- Cuerpo --}}
                    <tr>
                        <td style="padding: 40px;">
                            <h1 style="margin: 0 0 16px; font-size: 24px; font-weight: 700; color: #1a1a2e; line-height: 1.3;">
                                ¡Bienvenido/a, {{ $nombreCompleto }}!
                            </h1>

                            <p style="margin: 0 0 16px; font-size: 16px; color: #4a4a68; line-height: 1.6;">
                                <strong>{{ $empresaNombre }}</strong> te da la bienvenida a <strong>{{ $nombreApp }}</strong>.
                                Ya tienes acceso a la aplicación.
                            </p>

                            <p style="margin: 0 0 32px; font-size: 16px; color: #4a4a68; line-height: 1.6;">
                                Descarga la app e inicia sesión con tu cuenta empresarial para comenzar a disfrutar de todos los beneficios que tu empresa tiene para ti.
                            </p>

                            {{-- Botón CTA --}}
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin: 0 auto;">
                                <tr>
                                    <td align="center" style="border-radius: 8px; background-color: #4f46e5;">
                                        <a href="{{ $linkDescarga }}" target="_blank" style="display: inline-block; padding: 14px 40px; font-size: 16px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 8px;">
                                            Descargar la App
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- Separador --}}
                    <tr>
                        <td style="padding: 0 40px;">
                            <hr style="border: none; border-top: 1px solid #e8e8ed; margin: 0;">
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td align="center" style="padding: 24px 40px 32px;">
                            <p style="margin: 0 0 8px; font-size: 14px; font-weight: 600; color: #1a1a2e;">
                                {{ $nombreApp }}
                            </p>
                            <p style="margin: 0; font-size: 13px; color: #8e8ea0;">
                                ¿Necesitas ayuda?
                                <a href="mailto:{{ $supportEmail }}" style="color: #4f46e5; text-decoration: none;">{{ $supportEmail }}</a>
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
