<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Candidato - {{ $candidato->nombre_completo ?? 'Sin nombre' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
        }
        .header {
            background-color: #1e40af;
            color: white;
            padding: 20px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 12px;
            opacity: 0.9;
        }
        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #f1f5f9;
            padding: 8px 12px;
            font-weight: bold;
            font-size: 12px;
            border-left: 3px solid #1e40af;
            margin-bottom: 10px;
        }
        .section-content {
            padding: 0 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 6px 8px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        table th {
            background-color: #f8fafc;
            font-weight: 600;
            width: 35%;
        }
        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-gray { background-color: #e2e8f0; color: #475569; }
        .badge-warning { background-color: #fef3c7; color: #92400e; }
        .badge-success { background-color: #d1fae5; color: #065f46; }
        .badge-danger { background-color: #fee2e2; color: #991b1b; }
        .badge-info { background-color: #dbeafe; color: #1e40af; }
        .timeline-item {
            padding: 8px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .timeline-item:last-child {
            border-bottom: none;
        }
        .comment {
            background-color: #f8fafc;
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 4px;
        }
        .comment-meta {
            font-size: 10px;
            color: #64748b;
            margin-top: 5px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 10px 20px;
            font-size: 9px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $vacante->puesto }}</h1>
        <p>{{ $empresa->nombre }} | Candidato: {{ $candidato->nombre_completo ?? 'Sin nombre' }}</p>
    </div>

    <div class="section">
        <div class="section-title">Información General</div>
        <div class="section-content">
            <table>
                <tr>
                    <th>Estatus actual</th>
                    <td>
                        @php
                            $colorClass = match($candidato->estatus) {
                                'Sin atender' => 'badge-gray',
                                'En proceso' => 'badge-warning',
                                'Contratado' => 'badge-success',
                                'Rechazado' => 'badge-danger',
                                'No se presentó' => 'badge-info',
                                default => 'badge-gray',
                            };
                        @endphp
                        <span class="badge {{ $colorClass }}">{{ $candidato->estatus }}</span>
                    </td>
                </tr>
                <tr>
                    <th>Fecha de postulación</th>
                    <td>{{ $candidato->created_at->format('d/m/Y H:i') }}</td>
                </tr>
                @if($candidato->curp)
                <tr>
                    <th>CURP</th>
                    <td>{{ $candidato->curp }}</td>
                </tr>
                @endif
                @if($candidato->email)
                <tr>
                    <th>Email</th>
                    <td>{{ $candidato->email }}</td>
                </tr>
                @endif
                @if($candidato->telefono)
                <tr>
                    <th>Teléfono</th>
                    <td>{{ $candidato->telefono }}</td>
                </tr>
                @endif
                @if($candidato->evaluacion_cv)
                <tr>
                    <th>Evaluación CV</th>
                    <td>{{ $candidato->evaluacion_cv }}/10</td>
                </tr>
                @endif
            </table>
        </div>
    </div>

    <div class="section">
        <div class="section-title">Datos del Formulario</div>
        <div class="section-content">
            <table>
                @foreach($camposFormulario as $campo)
                    @php
                        $valor = $valoresFormulario[$campo->nombre] ?? null;
                        if (is_array($valor)) {
                            $valor = implode(', ', $valor);
                        }
                    @endphp
                    <tr>
                        <th>{{ $campo->etiqueta }}</th>
                        <td>{{ $valor ?: '—' }}</td>
                    </tr>
                @endforeach
            </table>
        </div>
    </div>

    @if($candidato->historialEstatus->count() > 0)
    <div class="section">
        <div class="section-title">Historial de Estatus</div>
        <div class="section-content">
            @foreach($candidato->historialEstatus as $historial)
                <div class="timeline-item">
                    <strong>{{ $historial->estatus }}</strong>
                    <br>
                    <small>
                        {{ $historial->fecha_inicio->format('d/m/Y H:i') }}
                        @if($historial->fecha_fin)
                            → {{ $historial->fecha_fin->format('d/m/Y H:i') }}
                            ({{ $historial->duracion }})
                        @else
                            → Actual
                        @endif
                        @if($historial->creadoPor)
                            | Por: {{ $historial->creadoPor->name }}
                        @endif
                    </small>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    @if($candidato->mensajes->count() > 0)
    <div class="section">
        <div class="section-title">Comentarios ({{ $candidato->mensajes->count() }})</div>
        <div class="section-content">
            @foreach($candidato->mensajes as $mensaje)
                <div class="comment">
                    {{ $mensaje->comentario }}
                    <div class="comment-meta">
                        {{ $mensaje->usuario->name ?? 'Usuario' }} — {{ $mensaje->created_at->format('d/m/Y H:i') }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i') }} | {{ config('app.name') }}
    </div>
</body>
</html>
