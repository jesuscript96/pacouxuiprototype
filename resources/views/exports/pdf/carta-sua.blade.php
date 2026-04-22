<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Carta SUA - {{ $nombre_empleado }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
            padding: 40px 50px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 20px;
        }
        .header h1 {
            font-size: 16px;
            color: #1e40af;
            margin-bottom: 5px;
        }
        .header .subtitulo {
            font-size: 10px;
            color: #64748b;
        }
        .destinatario {
            margin-bottom: 25px;
        }
        .destinatario p {
            margin-bottom: 3px;
        }
        .destinatario .label {
            font-weight: bold;
            color: #475569;
        }
        .contenido {
            text-align: justify;
            margin-bottom: 20px;
        }
        .contenido p {
            margin-bottom: 15px;
        }
        .tabla-montos {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
        }
        .tabla-montos th,
        .tabla-montos td {
            padding: 10px 15px;
            text-align: left;
            border: 1px solid #e2e8f0;
        }
        .tabla-montos th {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
            width: 60%;
        }
        .tabla-montos td {
            text-align: right;
            font-family: 'DejaVu Sans Mono', monospace;
        }
        .tabla-montos tr.total {
            background-color: #1e40af;
            color: white;
        }
        .tabla-montos tr.total th,
        .tabla-montos tr.total td {
            border-color: #1e40af;
            font-weight: bold;
        }
        .legal {
            font-size: 9px;
            color: #64748b;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e2e8f0;
        }
        .legal p {
            margin-bottom: 10px;
        }
        .firma-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }
        .firma-linea {
            width: 250px;
            border-top: 1px solid #333;
            margin: 40px auto 5px auto;
        }
        .firma-label {
            text-align: center;
            font-size: 10px;
            color: #475569;
        }
        .footer {
            position: fixed;
            bottom: 30px;
            left: 50px;
            right: 50px;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        .footer .fecha {
            float: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>CARTA DE APORTACIONES SUA</h1>
        <div class="subtitulo">Artículo 180 de la Ley del Seguro Social</div>
    </div>

    <div class="destinatario">
        <p><span class="label">At'n:</span> {{ $nombre_empleado }}</p>
        <p><span class="label">RFC:</span> {{ $rfc }}</p>
        <p><span class="label">CURP:</span> {{ $curp }}</p>
    </div>

    <div class="contenido">
        <p>
            Por medio de la presente, <strong>{{ $razon_social }}</strong> le informa que,
            de conformidad con lo establecido en el artículo 180 de la Ley del Seguro Social,
            durante el bimestre <strong>{{ $bimestre }}</strong> se realizaron las siguientes
            aportaciones a su cuenta individual del Sistema de Ahorro para el Retiro (AFORE):
        </p>
    </div>

    <table class="tabla-montos">
        <tr>
            <th>Retiro</th>
            <td>$ {{ $retiro }}</td>
        </tr>
        <tr>
            <th>Cesantía en Edad Avanzada y Vejez (C.V.)</th>
            <td>$ {{ $cesantia_vejez }}</td>
        </tr>
        <tr>
            <th>Aportación Infonavit</th>
            <td>$ {{ $infonavit }}</td>
        </tr>
        <tr class="total">
            <th>TOTAL DECLARADO</th>
            <td>$ {{ $total }}</td>
        </tr>
    </table>

    <div class="contenido">
        <p>
            Las aportaciones antes mencionadas han sido enteradas al Instituto Mexicano del
            Seguro Social (IMSS) y al Instituto del Fondo Nacional de la Vivienda para los
            Trabajadores (INFONAVIT), según corresponda, para ser acreditadas en su cuenta
            individual.
        </p>
        <p>
            Se extiende la presente para los fines legales que al interesado convengan.
        </p>
    </div>

    <div class="firma-section">
        <div class="firma-linea"></div>
        <div class="firma-label">Firma del trabajador</div>
    </div>

    <div class="legal">
        <p>
            <strong>Aviso de privacidad:</strong> Los datos personales contenidos en este
            documento son tratados de conformidad con la Ley Federal de Protección de Datos
            Personales en Posesión de los Particulares.
        </p>
    </div>

    <div class="footer">
        <span>{{ $empresa_nombre }}</span>
        <span class="fecha">Generado el {{ $fecha_generacion }}</span>
    </div>
</body>
</html>
