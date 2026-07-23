<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Sanidad y Nutrición</title>
    <style>
        body { font-family: Helvetica, Arial, sans-serif; color: #333; font-size: 11px; line-height: 1.4; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #059669; padding-bottom: 10px; margin-bottom: 20px; display: table; width: 100%; }
        .header div { display: table-cell; vertical-align: middle; }
        .title { font-size: 16px; font-weight: bold; color: #065f46; text-transform: uppercase; }
        .subtitle { font-size: 10px; color: #6b7280; }
        .info-box { background-color: #f3f4f6; border: 1px solid #e5e7eb; padding: 10px; margin-bottom: 15px; border-radius: 4px; }
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        .info-table td { padding: 5px; font-size: 11px; }
        h3 { font-size: 12px; color: #1f2937; border-bottom: 1px solid #d1d5db; padding-bottom: 4px; margin-top: 20px; text-transform: uppercase; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 5px; margin-bottom: 15px; }
        table.data-table th, table.data-table td { border: 1px solid #d1d5db; padding: 6px; text-align: left; font-size: 10px; }
        table.data-table th { background-color: #059669; color: #ffffff; text-transform: uppercase; }
        
        /* Estilos específicos para la tabla de observaciones en 2 columnas */
        table.obs-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
        table.obs-table th { background-color: #f3f4f6; color: #374151; border: 1px solid #d1d5db; padding: 6px; font-size: 10px; text-transform: uppercase; text-align: left; width: 50%; }
        table.obs-table td { border: 1px solid #d1d5db; padding: 8px; font-size: 10px; vertical-align: top; width: 50%; background-color: #ffffff; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; text-align: center; font-size: 9px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 5px; }
    </style>
</head>
<body>

    <div class="header">
        <div>
            <span class="title">Sistema Control - Bitácora </span><br>
            <span class="subtitle">Reporte Oficial de Aplicaciones y Nutrición</span>
        </div>
        
    </div>

    <!-- DATOS GENERALES -->
    <div class="info-box">
        <table class="info-table">
            <tr>
                <td><strong>Fecha de Registro:</strong> {{ \Carbon\Carbon::parse($bitacora->fecha)->format('d/m/Y') }}</td>
                <td><strong>Sector / Nave:</strong> {{ $bitacora->sector }}</td>
            </tr>
            <tr>
                <td colspan="2"><strong>Operador Responsable:</strong> {{ $bitacora->operador ? $bitacora->operador->name : 'No asignado' }}</td>
            </tr>
        </table>
    </div>

    <!-- 1. AGROQUÍMICOS -->
    <h3>1. Manejo de Agroquímicos</h3>
    @if($bitacora->agroquimicos->isNotEmpty())
        @php $primerArq = $bitacora->agroquimicos->first(); @endphp
        <table class="info-table" style="background-color: #fff7ed; border: 1px solid #ffedd5;">
            <tr>
                <td><strong>Variedad:</strong> {{ $primerArq->variedad ?? '—' }}</td>
                <td><strong>N° Plantas:</strong> {{ $primerArq->numero_plantas ? number_format($primerArq->numero_plantas) : '—' }}</td>
                <td><strong>Trasplante:</strong> {{ $primerArq->fecha_trasplante ? \Carbon\Carbon::parse($primerArq->fecha_trasplante)->format('d/m/Y') : '—' }}</td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th>F. Aplicación</th>
                    <th>Tipo</th>
                    <th>Producto / Ingrediente</th>
                    <th>Dosis</th>
                    <th>Solución</th>
                    <th>Observaciones</th>
                    <th style="text-align: center;">IS</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bitacora->agroquimicos as $arq)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($arq->fecha_aplicacion)->format('d/m/Y') }}</td>
                    <td>{{ $arq->aplicacion }}</td>
                    <td><strong>{{ $arq->producto }}</strong></td>
                    <td>{{ $arq->dosis }} {{ $arq->unidad_dosis }}</td>
                    <td>{{ $arq->solucion_madre == 'SÍ' ? 'Solución Madre' : ($arq->solucion_diaria == 'SÍ' ? 'Solución Diaria' : 'Estándar') }}</td>
                    <td>{{ $arq->observaciones ?? '—' }}</td>
                    <td style="text-align: center;">{{ $arq->is_intervalo_seguridad ?? ($arq->intervalo_seguridad ?? ($arq->is ?? '—')) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p style="color: #6b7280; font-style: italic;">Sin aplicaciones de agroquímicos registradas.</p>
    @endif

    <!-- 2. FERTILIZANTES -->
    <h3>2. Manejo de Fertilizantes</h3>
    @if($bitacora->fertilizantes->isNotEmpty())
        @foreach($bitacora->fertilizantes->groupBy('tanque') as $nombreTanque => $accionesTanque)
            <strong style="font-size: 11px; color: #059669; display: block; margin-top: 8px;">Tanque: {{ $nombreTanque }}</strong>
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 70%;">Acción / Instrucción</th>
                        <th style="width: 30%; text-align: right;">Dosificación</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accionesTanque as $acc)
                    <tr>
                        <td style="font-style: italic;">{{ $acc->accion ?? 'Aplicación estándar.' }}</td>
                        <td style="text-align: right; font-weight: bold;">{{ $acc->cantidad }} {{ $acc->unidad_cantidad }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endforeach

        @php $primerFert = $bitacora->fertilizantes->first(); @endphp
        @if($primerFert && ($primerFert->labores_culturales || $primerFert->observaciones))
            <!-- TABLA DE 2 COLUMNAS PARA OBSERVACIONES Y LABORES -->
            <table class="obs-table">
                <thead>
                    <tr>
                        <th>Labores Culturales Realizadas</th>
                        <th>Observaciones Generales de la Mezcla</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $primerFert->labores_culturales ?? 'Ninguna registrada.' }}</td>
                        <td>{{ $primerFert->observaciones ?? 'Sin observaciones generales.' }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
    @else
        <p style="color: #6b7280; font-style: italic;">Sin nutrientes añadidos en esta orden.</p>
    @endif

    <div class="footer">
        Sistema de Control Agrícola — Reporte generado digitalmente.
    </div>

</body>
</html>