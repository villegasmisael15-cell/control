<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <title>Comprobante de Embarque</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 10px;
            color: #1f2937;
            line-height: 1.4;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #059669;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .titulo {
            color: #059669;
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }

        .subtitulo {
            margin: 4px 0 0 0;
            font-size: 13px;
            font-weight: bold;
            color: #4b5563;
        }

        .info-box {
            margin-bottom: 15px;
            font-size: 12px;
            background: #f9fafb;
            padding: 10px;
            border: 1px solid #e5e7eb;
        }

        .seccion-titulo {
            font-size: 13px;
            font-weight: bold;
            color: #111827;
            text-transform: uppercase;
            margin-top: 20px;
            margin-bottom: 8px;
            background: #f3f4f6;
            padding: 6px 10px;
            border-left: 3px solid #059669;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 15px;
        }

        th,
        td {
            padding: 8px;
            border: 1px solid #e5e7eb;
        }

        th {
            background: #f9fafb;
            text-align: left;
            color: #374151;
        }

        /* Colores específicos de Exportación */
        .total-recibido {
            color: #1d4ed8;
            font-weight: bold;
            background-color: #eff6ff;
        }

        .rechazado {
            color: #dc2626;
        }

        .rechazo-post-fila {
            color: #b91c1c;
            background-color: #fef2f2;
            font-weight: bold;
        }

        .aceptado {
            color: #16a34a;
            font-weight: bold;
            background-color: #f0fdf4;
        }

        .empacados {
            color: #059669;
            font-weight: bold;
        }

        .nacional {
            color: #000000;
            font-weight: bold;
        }

        /* Estilos específicos de Recepción Nacional */
        .texto-negro {
            color: #000000;
            font-weight: bold;
        }

        .observaciones {
            padding: 10px;
            border: 1px solid #e5e7eb;
            background-color: #fafafa;
            margin-bottom: 10px;
            border-radius: 6px;
        }

        .eficiencia-box {
            background: #f5f3ff;
            border: 1px solid #ddd6fe;
            padding: 12px;
            text-align: center;
            margin-top: 15px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

    <div class="header">
        <h2 class="titulo">SISTEMA CONTROL</h2>
        <p class="subtitulo">REPORTE INTEGRADO DE COMPROBANTES</p>
    </div>

    <div class="info-box">
        <table style="width: 100%; margin-bottom: 0; border: none; font-size: 12px;">
            <tr style="border: none;">
                <td style="width: 50%; padding: 0; border: none; vertical-align: top;">
                    <strong>Productor:</strong> {{ $reporte->nac_productor ?? 'N/A' }} <br>
                    <strong>Semana:</strong> 
                    {{ $reporte->fecha_exportacion ? date('W', strtotime($reporte->fecha_exportacion)) : date('W') }}<br>
                </td>
                <td style="width: 50%; padding: 0; border: none; text-align: right; vertical-align: top;">
                    <strong>Sector Principal:</strong> {{ $reporte->recepcion_sector }}<br>
                    <strong>Fecha de Exportación:</strong> 
                    {{ $reporte->fecha_exportacion ? date('d/m/Y', strtotime($reporte->fecha_exportacion)) : date('d/m/Y') }}
                </td>
            </tr>
        </table>
    </div>

    <div class="seccion-titulo">I. Reporte de Exportación</div>
    <table>
        <thead>
            <tr>
                <th>Concepto Exportación</th>
                <th style="text-align: right;">Cantidad (Kg)</th>
            </tr>
        </thead>
        <tbody>
            <tr class="total-recibido">
                <td>Total Recibido</td>
                <td style="text-align: right;">{{ number_format($reporte->total_kg ?? 0.00, 2) }}</td>
            </tr>
            <tr class="rechazado">
                <td>Total Rechazado </td>
                <td style="text-align: right;">{{ $reporte->rechazados_kg !== null ? number_format($reporte->rechazados_kg, 2) : '0.00' }}</td>
            </tr>
            
            @if(($reporte->rechazo_post ?? 0) > 0)
            <tr class="rechazo-post-fila">
                <td>Rechazo Posterior </td>
                <td style="text-align: right;">-{{ number_format($reporte->rechazo_post, 2) }}</td>
            </tr>
            @endif

            <tr class="aceptado">
                <td>Kilos Aceptados</td>
                <td style="text-align: right;">{{ number_format($reporte->aceptados_kg ?? $reporte->total_kg, 2) }}</td>
            </tr>
            <tr class="empacados">
                <td style="padding-left: 20px;">Kilos Empacados</td>
                <td style="text-align: right;">{{ $reporte->empacados !== null ? number_format($reporte->empacados, 2) : '0.00' }}</td>
            </tr>
            <tr class="nacional">
                <td style="padding-left: 20px;">Kilos Nacional </td>
                <td style="text-align: right;">{{ $reporte->nacional !== null ? number_format($reporte->nacional, 2) : '0.00' }}</td>
            </tr>
        </tbody>
    </table>

    @if($reporte->observaciones)
    <div class="observaciones">
        <span style="font-size: 10px; font-weight: bold; color: #4b5563; display: block; text-transform: uppercase;">Observaciones:</span>
        <p style="margin: 4px 0 0 0; font-size: 12px; color: #374151; font-style: italic;">"{{ $reporte->observaciones }}"</p>
    </div>
    @endif

    <div class="eficiencia-box">
        <span style="font-size: 11px; color: #6d28d9; text-transform: uppercase; font-weight: bold; display: block;"> Condensación global</span>
        <span style="font-size: 22px; font-weight: bold; color: #5b21b6;">
            @php
                $aceptadosReales = $reporte->aceptados_kg ?? $reporte->total_kg;
            @endphp

            @if($aceptadosReales > 0)
                @php
                    $numerador = (float)($reporte->total_kg ?? 0) - (float)($reporte->rechazados_kg ?? 0) - (float)($reporte->empacados ?? 0) - (float)($reporte->nacional ?? 0);
                    $porcentaje = ($numerador / $aceptadosReales) * 100;
                @endphp
                {{ number_format($porcentaje, 2) }}%
            @else
                0.00%
            @endif
        </span>
    </div>

    <div class="seccion-titulo">II. Reporte de Recepción Nacional</div>

    <div style="margin-bottom: 10px; font-size: 11px; background: #fafafa; padding: 8px; border: 1px solid #e5e7eb;">
        <strong>Productor Nacional:</strong> {{ $reporte->nac_productor ?? 'N/A' }} |
        <strong>Sector Origen:</strong> {{ $reporte->nac_sector ?? 'N/A' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Concepto Nacional</th>
                <th style="text-align: right;">Cantidad (Kg)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Kg Recepción y Rechazo</td>
                <td style="text-align: right; font-weight: bold;">{{ $reporte->nac_kg_recepcion !== null ? number_format($reporte->nac_kg_recepcion, 2) : '0.00' }} kg</td>
            </tr>
            <tr>
                <td>Kg Vendidos</td>
                <td style="text-align: right; font-weight: bold; color: #2563eb;">{{ $reporte->nac_kg_vendidos !== null ? number_format($reporte->nac_kg_vendidos, 2) : '0.00' }} kg</td>
            </tr>
            <tr class="texto-negro" style="background-color: #f9fafb;">
                <td>Total </td>
                <td style="text-align: right; font-size: 13px; color: #000000;">{{ $reporte->nac_kg_vendidos !== null ? number_format($reporte->nac_kg_vendidos, 2) : '0.00' }} kg</td>
            </tr>
        </tbody>
    </table>

</body>

</html>