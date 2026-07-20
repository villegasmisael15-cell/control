<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Registro #{{ $monitoreo->id }} - Sistema Control</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

    <nav class="bg-emerald-600 text-white shadow-md">
        <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
            <span class="font-bold text-xl tracking-wider"><i class="fa-solid fa-leaf mr-2"></i>SISTEMA CONTROL</span>
            <a href="{{ route('monitoreo.index') }}" class="text-sm bg-emerald-700 hover:bg-emerald-800 px-3 py-2 rounded-md transition font-medium">
                <i class="fa-solid fa-arrow-left mr-1"></i> Volver a la Bitácora
            </a>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-8 w-full flex-grow">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">

            <div class="bg-gray-50 px-6 py-5 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                        <i class="fa-solid fa-file-invoice text-emerald-600"></i>
                        Inspección del Registro Diario
                    </h2>
                </div>
                <div class="flex items-center gap-3">
                    <span class="text-xs font-semibold px-3 py-1.5 bg-gray-100 text-gray-800 rounded-lg border border-gray-200">
                        <i class="fa-solid fa-calendar mr-1"></i> {{ \Carbon\Carbon::parse($monitoreo->fecha)->format('d/m/Y') }}
                    </span>
                    <span class="text-xs font-bold px-3 py-1.5 bg-emerald-100 text-emerald-800 rounded-lg border border-emerald-200">
                        <i class="fa-solid fa-layer-group mr-1"></i> {{ $monitoreo->sector }}
                    </span>
                </div>
            </div>

            <div class="p-6 space-y-6">

                <div class="bg-gray-50 rounded-xl border border-gray-300 p-5 shadow-sm">
                    <h3 class="font-bold text-xs text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-1.5 border-b border-gray-200 pb-2">
                        <i class="fa-solid fa-circle-info text-emerald-600"></i> Características Iniciales del Área
                    </h3>
                    @if($caracteristicas)
                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                        <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm flex items-center gap-3">
                            <div class="p-2 bg-emerald-50 rounded-md text-emerald-600"><i class="fa-solid fa-maximize"></i></div>
                            <div>
                                <span class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide">Superficie</span>
                                <span class="text-sm font-bold text-gray-800">{{ number_format($caracteristicas->superficie_m2) }} m²</span>
                            </div>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm flex items-center gap-3">
                            <div class="p-2 bg-emerald-50 rounded-md text-emerald-600"><i class="fa-solid fa-seedling"></i></div>
                            <div>
                                <span class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide">Variedad</span>
                                <span class="text-sm font-bold text-gray-800">{{ $caracteristicas->variedad }}</span>
                            </div>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm flex items-center gap-3">
                            <div class="p-2 bg-emerald-50 rounded-md text-emerald-600"><i class="fa-solid fa-提出 text-xs font-bold">M</i></div>
                            <div>
                                <span class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide">Macetas / Gotero</span>
                                <span class="text-sm font-bold text-gray-800">{{ $caracteristicas->macetas_por_gotero ?? 1 }}</span>
                            </div>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-gray-200 shadow-sm flex items-center gap-3">
                            <div class="p-2 bg-emerald-50 rounded-md text-emerald-600"><i class="fa-solid fa-calendar-check"></i></div>
                            <div>
                                <span class="block text-[11px] font-bold text-gray-500 uppercase tracking-wide">Fecha Trasplante</span>
                                <span class="text-sm font-bold text-gray-800">{{ \Carbon\Carbon::parse($caracteristicas->fecha_trasplante)->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                    @else
                    <p class="text-xs text-gray-500 italic">No hay características registradas para este sector.</p>
                    @endif
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div class="bg-stone-50 p-5 rounded-xl border border-stone-200 space-y-3 shadow-sm">
                        <h3 class="font-bold text-sm text-stone-700 border-b border-stone-200 pb-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-temperature-half text-orange-500"></i> Monitoreo Climático
                        </h3>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Temperatura:</span>
                            <span class="font-semibold text-gray-800">{{ $monitoreo->temperatura }} °C</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Humedad:</span>
                            <span class="font-semibold text-gray-800">{{ $monitoreo->humedad }} %</span>
                        </div>
                        <div class="flex justify-between items-center text-sm pt-1 border-t border-dashed border-stone-200">
                            <span class="text-gray-600 font-medium">DPV Calculado:</span>
                            <span class="font-mono font-bold text-gray-900">{{ $monitoreo->dpv }}</span>
                        </div>
                    </div>

                    <div class="bg-blue-50/40 p-5 rounded-xl border border-blue-200 space-y-3 shadow-sm">
                        <h3 class="font-bold text-sm text-blue-700 border-b border-blue-200 pb-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-droplet text-blue-500"></i> Riego y Hidratación
                        </h3>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Vol. Riego por Maceta:</span>
                            <span class="font-semibold text-emerald-700 font-bold">{{ number_format($monitoreo->vol_riego_entrada) }} mL</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-gray-500">Vol. Drenaje Salida:</span>
                            <span class="font-semibold text-gray-800">{{ number_format($monitoreo->vol_drenaje_salida) }} mL</span>
                        </div>
                        <div class="flex justify-between items-center text-sm pt-1 border-t border-dashed border-blue-200">
                            <span class="text-blue-700 font-semibold">% Drenaje:</span>
                            <span class="font-bold text-blue-600">{{ $monitoreo->porcentaje_drenaje }}%</span>
                        </div>
                    </div>

                    <div class="bg-purple-50/40 p-5 rounded-xl border border-purple-200 space-y-3 shadow-sm">
                        <h3 class="font-bold text-sm text-purple-700 border-b border-purple-200 pb-2 flex items-center gap-1.5">
                            <i class="fa-solid fa-flask text-purple-500"></i> Conductividad y pH
                        </h3>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
                            <div class="flex justify-between"><span class="text-gray-500">CE Ent:</span> <span class="font-semibold text-gray-800">{{ $monitoreo->ce_entrada }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">CE Sal:</span> <span class="font-semibold text-gray-800">{{ $monitoreo->ce_salida }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">pH Ent:</span> <span class="font-semibold text-gray-800">{{ $monitoreo->ph_entrada }}</span></div>
                            <div class="flex justify-between"><span class="text-gray-500">pH Sal:</span> <span class="font-semibold text-gray-800">{{ $monitoreo->ph_salida }}</span></div>
                        </div>
                        <div class="pt-2 border-t border-dashed border-purple-200 grid grid-cols-2 gap-2 text-center text-xs font-bold text-gray-600">
                            <div class="bg-white/80 p-1 rounded border border-purple-100">Dif. CE: <span class="text-purple-700 block text-sm font-bold">{{ $monitoreo->diferencia_ce }}</span></div>
                            <div class="bg-white/80 p-1 rounded border border-purple-100">Dif. pH: <span class="text-purple-700 block text-sm font-bold">{{ $monitoreo->diferencia_ph }}</span></div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div class="bg-amber-50/30 p-5 rounded-xl border border-amber-200 md:col-span-2 flex flex-col justify-between shadow-sm">
                        <div>
                            <h3 class="font-bold text-sm text-amber-800 border-b border-amber-200 pb-2 flex items-center gap-1.5">
                                <i class="fa-solid fa-sun text-amber-500"></i> Radiación Solar Registrada
                            </h3>
                            <div class="grid grid-cols-2 gap-4 mt-3 text-sm">
                                <div class="flex justify-between items-center bg-white/60 p-2 rounded border border-amber-100">
                                    <span class="text-gray-500">Hora de Captura:</span>
                                    <span class="font-mono font-bold text-gray-800">{{ \Carbon\Carbon::parse($monitoreo->radiacion_hora)->format('g:i a') }}</span>
                                </div>
                                <div class="flex justify-between items-center bg-white/60 p-2 rounded border border-amber-100">
                                    <span class="text-gray-500">Lectura Tomada:</span>
                                    <span class="font-bold text-gray-800">{{ number_format($monitoreo->radiacion_lectura) }} Lux</span>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4 items-center">
                            <div class="text-center sm:text-left">
                                <span class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Semáforo Evaluado:</span>
                                @if($monitoreo->radiacion_semaforo === 'VERDE')
                                <span class="mt-1 px-3 py-1 inline-flex text-xs font-black rounded bg-emerald-100 text-emerald-800 border border-emerald-200">VERDE (Óptima)</span>
                                @elseif($monitoreo->radiacion_semaforo === 'AMARILLO')
                                <span class="mt-1 px-3 py-1 inline-flex text-xs font-black rounded bg-amber-100 text-amber-800 border border-amber-200">AMARILLO</span>
                                @else
                                <span class="mt-1 px-3 py-1 inline-flex text-xs font-black rounded bg-red-100 text-red-800 border border-red-200">ROJO</span>
                                @endif
                            </div>
                            <div class="sm:col-span-2 bg-white/80 p-3 rounded-lg border border-amber-100">
                                <span class="block text-[11px] font-bold text-amber-900 uppercase mb-0.5">Acción Ejecutada en Invernadero:</span>
                                <p class="text-xs text-gray-700 italic font-medium">{{ $monitoreo->radiacion_accion_tomada ?? 'Ninguna acción requerida.' }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-amber-50/40 p-5 rounded-xl border border-amber-200 space-y-4 flex flex-col justify-between shadow-sm">
                        <div class="space-y-3">
                            <h3 class="font-bold text-sm text-amber-800 border-b border-amber-200 pb-2 flex items-center gap-1.5">
                                <i class="fa-solid fa-weight-scale text-amber-600"></i> Balance de Sustrato
                            </h3>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Peso Tarde Anterior:</span>
                                <span class="font-semibold text-gray-800">{{ $monitoreo->peso_tarde_anterior }} kg</span>
                            </div>
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-gray-500">Peso Mañana Siguiente:</span>
                                <span class="font-semibold text-gray-800">{{ $monitoreo->peso_manana }} kg</span>
                            </div>
                        </div>
                        <div class="pt-2 border-t border-dashed border-amber-200">
                            <span class="text-xs font-bold text-amber-800 block mb-1">% Caída Nocturna:</span>
                            <span class="block w-full bg-amber-100 text-amber-800 rounded-lg py-2 text-base font-black text-center tracking-wider border border-amber-200">{{ $monitoreo->porcentaje_caida_nocturna }}%</span>
                        </div>
                    </div>
                </div>

                <div class="w-full">
                    @php
                    $isOptimo = $monitoreo->estatus_general === 'ÓPTIMO';
                    @endphp
                    <div class="{{ $isOptimo ? 'bg-emerald-100 border-emerald-300' : 'bg-red-100 border-red-300' }} rounded-xl border p-6 flex flex-col justify-center items-center shadow-sm transition duration-300">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Diagnóstico Automático del Sistema</span>
                        <div class="text-3xl font-black {{ $isOptimo ? 'text-emerald-800' : 'text-red-800' }}">
                            {{ $monitoreo->estatus_general }}
                        </div>
                        <p class="text-[11px] text-gray-400 mt-2 text-center max-w-sm">Análisis computado a partir del balance bioclimático y los rangos de transpiración vegetal ideales del DPV (0.8 a 1.4).</p>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-4 border-t border-gray-100">
                    <span class="text-xs text-gray-400">Inspección de parámetros históricos en modo de solo lectura.</span>

                    @can('es-administrador')
                    <div class="flex gap-2">
                        <a href="{{ route('monitoreo.excel', $monitoreo->id) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-lg text-sm transition shadow flex items-center gap-1.5">
                            <i class="fa-solid fa-file-excel"></i> Descargar Excel
                        </a>

                        <a href="{{ route('monitoreo.edit', $monitoreo->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded-lg text-sm transition shadow flex items-center gap-1.5">
                            <i class="fa-solid fa-pen-to-square"></i> Editar Formulario
                        </a>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500 w-full mt-auto">
        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.
    </footer>

</body>

</html>