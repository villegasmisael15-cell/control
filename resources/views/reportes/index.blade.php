<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Bitácora de Embarques</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

    <nav class="bg-emerald-600 text-white shadow-md">
        <div class="max-w-[95%] mx-auto px-4 shadow-sm">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <i class="fa-solid fa-leaf text-2xl mr-2"></i>
                    <span class="font-bold text-xl tracking-wider">SISTEMA CONTROL</span>
                </div>
                <div class="flex items-center gap-4 text-sm font-medium">
                    <span class="bg-emerald-700 px-3 py-1 rounded text-xs">
                        <i class="fa-solid fa-user"></i> {{ auth()->user()->name }}
                    </span>
                    <a href="{{ route('dashboard') }}" class="text-xs bg-emerald-700 hover:bg-emerald-800 px-3 py-1.5 rounded transition flex items-center gap-1">
                        <i class="fa-solid fa-circle-chevron-left"></i> Volver al Panel
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-[95%] mx-auto px-2 sm:px-4 py-6 w-full flex-grow">
        <div class="mb-6">
            <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-chart-simple text-indigo-600"></i> Módulo de Reportes
            </h1>
            <p class="text-gray-600 text-xs sm:text-sm mt-1">Bitácora de Embarques de Exportación asignados por fecha.</p>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 p-4 mb-6">
            <form action="{{ route('reportes.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 items-end gap-4">
                <div>
                    <label for="mes" class="block text-xs font-bold text-gray-500 uppercase mb-1">
                        <i class="fa-solid fa-calendar text-indigo-600 mr-1"></i> Seleccionar Mes
                    </label>
                    <select name="mes" id="mes" class="w-full text-sm bg-gray-50 border border-gray-300 rounded-lg px-3 py-2">
                        @php
                        $mesActual = date('n');
                        $mesSeleccionado = request('mes', $mesActual);
                        $meses = [
                        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                        ];
                        @endphp
                        @foreach($meses as $num => $nombre)
                        <option value="{{ $num }}" {{ $mesSeleccionado == $num ? 'selected' : '' }}>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="semana" class="block text-xs font-bold text-gray-500 uppercase mb-1">
                        <i class="fa-solid fa-calendar-weeks text-indigo-600 mr-1"></i> Semana
                    </label>
                    <select name="semana" id="semana" class="w-full text-sm bg-gray-50 border border-gray-300 rounded-lg px-3 py-2">
                        <option value="">— Todo el mes —</option>
                        <option value="1" {{ request('semana') == '1' ? 'selected' : '' }}>Semana 1 (Días 1 al 7)</option>
                        <option value="2" {{ request('semana') == '2' ? 'selected' : '' }}>Semana 2 (Días 8 al 14)</option>
                        <option value="3" {{ request('semana') == '3' ? 'selected' : '' }}>Semana 3 (Días 15 al 21)</option>
                        <option value="4" {{ request('semana') == '4' ? 'selected' : '' }}>Semana 4 / Fin (Días 22+)</option>
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="w-full bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium py-2 px-4 rounded-lg transition flex items-center justify-center gap-2 cursor-pointer">
                        <i class="fa-solid fa-filter"></i> Filtrar
                    </button>
                    @if(request('semana') || request('mes'))
                    <a href="{{ route('reportes.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium py-2 px-4 rounded-lg transition flex items-center justify-center gap-1">Limpiar</a>
                    @endif
                </div>
            </form>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-xl mb-6 text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-100 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6 text-sm">
            <ul class="list-disc pl-5">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @php
        $reportesPorFecha = $reportes->groupBy('fecha_registro')->sortKeysDesc();
        @endphp

        @forelse($reportesPorFecha as $fechaKey => $grupoReportes)
        @php
        $fechaFormateada = \Carbon\Carbon::parse($fechaKey)->format('d/m/Y');
        @endphp

        <div class="mb-8 bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">

            <div class="bg-gray-800 text-white px-4 py-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="font-bold text-sm uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-calendar-day text-amber-400"></i> Reportes del día: {{ $fechaFormateada }}
                </div>

                @if(auth()->user()->rol === 'administrador')
                <div>
                    <button onclick="openGlobalModal('{{ $fechaKey }}')" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold px-4 py-2 rounded-lg transition shadow-sm flex items-center gap-1.5 cursor-pointer">
                        <i class="fa-solid fa-calculator"></i> Capturar Parámetros del Día
                    </button>
                </div>
                @endif
            </div>

            <!-- VISTA MÓVIL (CORREGIDA) -->
            <div class="block md:hidden space-y-4 p-4 bg-gray-50/50">
                @foreach($grupoReportes as $reporte)
                <div class="bg-white rounded-xl p-4 border border-gray-200 space-y-3 shadow-xs">
                    <div class="flex justify-between items-start border-b border-gray-100 pb-2">
                        <div>
                            <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">
                                Sector {{ $reporte->recepcion_sector }}
                            </span>
                            <h2 class="text-sm font-bold text-gray-900 mt-1">
                                <i class="fa-solid fa-user text-gray-400 mr-1 text-xs"></i>
                                {{ $reporte->operador_name ?? auth()->user()->name }}
                            </h2>
                        </div>
                        @if(auth()->user()->rol === 'administrador')
                        <div class="text-right">
                            @if($reporte->aprobado)
                            <span class="bg-green-100 text-green-800 text-[10px] px-2 py-0.5 rounded-sm font-medium">Visible</span>
                            @else
                            <span class="bg-amber-100 text-amber-800 text-[10px] px-2 py-0.5 rounded-sm font-medium">Pendiente</span>
                            @endif
                        </div>
                        @endif
                    </div>

                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="bg-gray-50 p-2 rounded-lg border border-blue-50">
                            <span class="block text-[10px] uppercase font-semibold text-blue-400">Total</span>
                            <span class="font-bold text-blue-800">{{ number_format($reporte->total_kg, 2) }}</span>
                        </div>
                        <div class="bg-red-50/50 p-2 rounded-lg border border-red-100/30">
                            <span class="block text-[10px] uppercase font-semibold text-red-400">Rechazo</span>
                            <span class="font-bold text-red-600">{{ $reporte->rechazados_kg !== null ? number_format($reporte->rechazados_kg, 2) : '0.00' }}</span>
                        </div>
                        <div class="bg-green-50/50 p-2 rounded-lg border border-green-100/30">
                            <span class="block text-[10px] uppercase font-semibold text-green-400">Aceptado</span>
                            <span class="font-bold text-green-600">{{ number_format($reporte->aceptados_kg ?? $reporte->total_kg, 2) }}</span>
                        </div>
                    </div>

                    <div class="bg-indigo-50/30 rounded-lg p-2.5 text-xs space-y-1 text-gray-600 border border-indigo-100/30">
                        <div class="flex justify-between text-red-600 font-medium"><span>Rechazo Post:</span> <span class="font-bold">{{ $reporte->rechazo_post !== null ? number_format($reporte->rechazo_post, 2) . ' kg' : '0.00 kg' }}</span></div>
                        <hr class="border-indigo-100/50 my-1">

                        <div class="flex justify-between text-emerald-700 font-medium"><span>Destino:</span> <span class="font-bold">{{ $reporte->destino !== null ? number_format($reporte->destino, 3) . ' kg' : '—' }}</span></div>
                        <div class="flex justify-between text-gray-700"><span>Participación:</span> <span class="font-semibold">{{ $reporte->participacion !== null ? $reporte->participacion : '—' }}</span></div>

                        @if(auth()->user()->rol === 'administrador')
                        <div class="flex justify-between"><span>Nacional (Auto):</span> <span class="font-semibold text-gray-900">{{ $reporte->nacional !== null ? number_format($reporte->nacional, 2) . ' kg' : '—' }}</span></div>
                        <div class="flex justify-between"><span>Empacados (Auto):</span> <span class="font-semibold text-gray-900">{{ $reporte->empacados !== null ? number_format($reporte->empacados, 2) . ' kg' : '—' }}</span></div>

                        <div class="flex justify-between text-indigo-700 font-bold border-t border-indigo-100/50 pt-1 mt-1">
                            <span>% Condensación:</span>
                            <span>
                                @php $aceptadosReales = $reporte->aceptados_kg ?? $reporte->total_kg; @endphp
                                @if($aceptadosReales > 0)
                                @php
                                $numerador = $reporte->total_kg - ($reporte->rechazados_kg ?? 0) - ($reporte->empacados ?? 0) - ($reporte->nacional ?? 0);
                                $porcentaje = ($numerador / $aceptadosReales) * 100;
                                @endphp
                                {{ number_format($porcentaje, 2) }}%
                                @else
                                0.00%
                                @endif
                            </span>
                        </div>

                        @if($reporte->observaciones)
                        <p class="text-[11px] text-gray-500 italic pt-1 border-t border-indigo-100/50 mt-1">"{{ $reporte->observaciones }}"</p>
                        @endif
                        @endif
                    </div>

                    <!-- BOTONES EN VISTA MÓVIL CORREGIDOS -->
                    <div class="pt-2 border-t border-gray-100 flex flex-col sm:flex-row gap-2 justify-end">
                        @if(auth()->user()->rol === 'operador')
                        <a href="{{ route('reportes.pdf', $reporte->recepcion_id) }}" class="w-full bg-red-600 hover:bg-red-700 text-white text-xs px-4 py-2 rounded-lg font-medium transition text-center flex items-center justify-center gap-1">
                            <i class="fa-solid fa-file-pdf"></i> PDF
                        </a>
                        @endif

                        @if(auth()->user()->rol === 'administrador')
                        <!-- NUEVO: Botón Capturar Post en Móvil -->
                        <button type="button" onclick="toggleModal('modal-rechazo-{{ $reporte->recepcion_id }}')" class="w-full bg-red-600 hover:bg-red-700 text-white text-xs px-4 py-2 rounded-lg font-medium transition text-center flex items-center justify-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            {{ ($reporte->rechazo_post ?? 0) > 0 ? 'Post: ' . number_format($reporte->rechazo_post, 2) . ' kg' : 'Capturar Post' }}
                        </button>
                        
                        <button type="button" onclick="toggleModal('modal-{{ $reporte->recepcion_id }}')" class="w-full bg-gray-800 hover:bg-gray-950 text-white text-xs px-4 py-2 rounded-lg font-medium transition text-center flex items-center justify-center gap-1">
                            <i class="fa-solid fa-pen-to-square mr-1"></i> Observaciones / Ventas
                        </button>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>

            <!-- VISTA ESCRITORIO -->
            <div class="hidden md:block overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse whitespace-nowrap">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200 text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <th class="px-6 py-4">Operador</th>
                                <th class="px-6 py-4">Sector</th>
                                <th class="px-6 py-4 bg-gray-100/50">Total Kg</th>
                                <th class="px-6 py-4 bg-gray-100/50">Rechazado Kg</th>
                                <th class="px-6 py-4 bg-red-50/50 text-red-700">Rechazo Post</th>
                                <th class="px-6 py-4 bg-gray-100/50">Aceptados Kg</th>
                                @if(auth()->user()->rol === 'administrador')
                                <th class="px-6 py-4 bg-emerald-50/30 text-emerald-800 font-bold">Destino</th>
                                <th class="px-6 py-4 bg-emerald-50/30 text-emerald-800 font-bold">Participación</th>
                                <th class="px-6 py-4 bg-indigo-50/30">Nacional</th>
                                <th class="px-6 py-4 bg-indigo-50/30">Empacados</th>
                                <th class="px-6 py-4 text-center font-bold text-indigo-700">% Condensación</th>
                                <th class="px-6 py-4">Observaciones</th>
                                <th class="px-6 py-4">Estatus</th>
                                @endif
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm text-gray-700">
                            @foreach($grupoReportes as $reporte)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $reporte->operador_name ?? auth()->user()->name }}</td>
                                <td class="px-6 py-4 font-semibold text-gray-600"> {{ $reporte->recepcion_sector }}</td>
                                <td class="px-6 py-4 bg-gray-100/30 text-blue-600 font-medium">{{ $reporte->total_kg }}</td>
                                <td class="px-6 py-4 bg-gray-100/30 text-red-600 font-medium">{{ $reporte->rechazados_kg !== null ? number_format($reporte->rechazados_kg, 2) : '0.00' }}</td>
                                <td class="px-6 py-4 bg-red-50/10 text-red-600 font-medium">{{ number_format($reporte->rechazo_post ?? 0, 2) }}</td>
                                <td class="px-6 py-4 bg-gray-100/30 text-green-600 font-medium">{{ number_format($reporte->aceptados_kg ?? $reporte->total_kg, 2) }}</td>
                                @if(auth()->user()->rol === 'administrador')
                                <td class="px-6 py-4 bg-emerald-50/10 text-emerald-700 font-bold">{{ $reporte->destino !== null ? number_format($reporte->destino, 3) : '—' }}</td>
                                <td class="px-6 py-4 bg-emerald-50/10 font-medium">{{ $reporte->participacion !== null ? $reporte->participacion : '—' }}</td>
                                <td class="px-6 py-4 bg-indigo-50/10 font-bold text-blue-600">{{ $reporte->nacional !== null ? number_format($reporte->nacional, 2) : '—' }}</td>
                                <td class="px-6 py-4 bg-indigo-50/10 text-emerald-700 font-bold">{{ $reporte->empacados !== null ? number_format($reporte->empacados, 2) : '—' }}</td>
                                <td class="px-6 py-4 text-center font-bold text-indigo-600 bg-indigo-50/30">
                                    @php $aceptadosReales = $reporte->aceptados_kg ?? $reporte->total_kg; @endphp
                                    @if($reporte->reporte_id && $aceptadosReales > 0)
                                    @php
                                    $numerador = $reporte->total_kg - ($reporte->rechazados_kg ?? 0) - ($reporte->empacados ?? 0) - ($reporte->nacional ?? 0);
                                    $porcentaje = ($numerador / $aceptadosReales) * 100;
                                    @endphp
                                    {{ number_format($porcentaje, 2) }}%
                                    @else
                                    <span class="text-xs text-gray-400 font-normal">Sin calcular</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 max-w-xs truncate text-gray-500 italic" title="{{ $reporte->observaciones }}">{{ $reporte->observaciones ?? 'Sin comentarios' }}</td>
                                <td class="px-6 py-4">
                                    @if($reporte->aprobado)
                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-sm font-medium inline-flex items-center gap-1"><i class="fa-solid fa-eye"></i> Visible</span>
                                    @else
                                    <span class="bg-amber-100 text-amber-800 text-xs px-2 py-1 rounded-sm font-medium inline-flex items-center gap-1"><i class="fa-solid fa-eye-slash"></i> Pendiente</span>
                                    @endif
                                </td>
                                @endif

                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if(auth()->user()->rol === 'administrador')
                                        <button type="button" onclick="toggleModal('modal-rechazo-{{ $reporte->recepcion_id }}')" class="bg-red-600 hover:bg-red-700 text-white text-xs px-3 py-1.5 rounded-lg font-medium transition cursor-pointer inline-flex items-center gap-1">
                                            <i class="fa-solid fa-circle-exclamation"></i>
                                            {{ ($reporte->rechazo_post ?? 0) > 0 ? 'Post: ' . number_format($reporte->rechazo_post, 2) . ' kg' : 'Capturar Post' }}
                                        </button>
                                        @endif

                                        @if(auth()->user()->rol === 'operador')
                                        <a href="{{ route('reportes.pdf', $reporte->recepcion_id) }}" class="inline-flex bg-red-600 hover:bg-red-700 text-white text-xs px-4 py-2 rounded-lg font-medium transition text-center items-center justify-center gap-1">
                                            <i class="fa-solid fa-file-pdf"></i> PDF
                                        </a>
                                        @endif

                                        @if(auth()->user()->rol === 'administrador')
                                        <button type="button" onclick="toggleModal('modal-{{ $reporte->recepcion_id }}')" class="bg-gray-800 hover:bg-gray-950 text-white text-xs px-3 py-1.5 rounded-lg font-medium transition cursor-pointer inline-flex items-center gap-1">
                                            <i class="fa-solid fa-pen-to-square"></i> Capturar
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white text-center py-8 text-gray-400 rounded-xl border border-gray-200">
            <i class="fa-solid fa-inbox text-2xl mb-1 block text-gray-300"></i> No hay registros este mes.
        </div>
        @endforelse

        <!-- SECCIÓN DE MODALES (Mantiene la misma lógica funcional) -->
        @if(auth()->user()->rol === 'administrador')
        @foreach($reportes as $reporte)
        <div id="modal-{{ $reporte->recepcion_id }}" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4 transition-opacity">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 max-w-md w-full overflow-hidden whitespace-normal">
                <div class="bg-indigo-600 text-white px-6 py-4 flex justify-between items-center">
                    <h3 class="font-bold text-lg">Capturar Datos Manuales</h3>
                    <button type="button" onclick="toggleModal('modal-{{ $reporte->recepcion_id }}')" class="text-indigo-200 hover:text-white cursor-pointer text-xl">&times;</button>
                </div>
                <form action="{{ route('reportes.update', $reporte->recepcion_id) }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-3 gap-3 text-xs bg-gray-50 p-3 rounded-lg border border-gray-200 text-gray-600 text-center">
                        <div><span class="block font-bold">Total</span> {{ number_format($reporte->total_kg, 2) }} kg</div>
                        <div><span class="block font-bold text-red-600">Rechazo</span> {{ $reporte->rechazados_kg !== null ? number_format($reporte->rechazados_kg, 2) : '0.00' }} kg</div>
                        <div><span class="block font-bold text-green-600">Aceptado</span> {{ number_format($reporte->aceptados_kg ?? $reporte->total_kg, 2) }} kg</div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kg Vendidos (Nacional)</label>
                        <input type="number" step="0.01" min="0" name="kg_vendidos" value="{{ $reporte->nac_kg_vendidos ?? '' }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:outline-hidden transition" placeholder="Cantidad vendida...">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Observaciones</label>
                        <textarea name="observaciones" rows="3" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:outline-hidden transition resize-none" placeholder="Notas de la bitácora...">{{ $reporte->observaciones }}</textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                        <button type="button" onclick="toggleModal('modal-{{ $reporte->recepcion_id }}')" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg font-medium cursor-pointer transition">Cancelar</button>
                        <button type="submit" class="px-4 py-2 text-sm bg-indigo-600 text-white hover:bg-indigo-700 rounded-lg font-medium cursor-pointer transition">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="modal-rechazo-{{ $reporte->recepcion_id }}" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4 transition-opacity">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 max-w-sm w-full overflow-hidden whitespace-normal">
                <div class="bg-red-600 text-white px-6 py-4 flex justify-between items-center">
                    <h3 class="font-bold text-sm uppercase tracking-wider"><i class="fa-solid fa-circle-exclamation mr-1"></i> Registrar Rechazo Post</h3>
                    <button type="button" onclick="toggleModal('modal-rechazo-{{ $reporte->recepcion_id }}')" class="text-red-200 hover:text-white cursor-pointer text-xl">&times;</button>
                </div>
                <form action="{{ route('reportes.update', $reporte->recepcion_id) }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    @method('PUT')

                    <input type="hidden" name="aprobado" value="0">

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cantidad Descarte Posterior (Kg)</label>
                        <input type="number" step="0.01" min="0" name="rechazo_post" value="{{ ($reporte->rechazo_post ?? 0) > 0 ? $reporte->rechazo_post : '' }}" class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:border-red-500 focus:outline-hidden transition" placeholder="0.00">
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                        <button type="button" onclick="toggleModal('modal-rechazo-{{ $reporte->recepcion_id }}')" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg font-medium cursor-pointer transition">Cancelar</button>
                        <button type="submit" class="px-4 py-2 text-sm bg-red-600 text-white hover:bg-red-700 rounded-lg font-medium cursor-pointer transition">Guardar Kilos</button>
                    </div>
                </form>
            </div>
        </div>
        @endforeach
        @endif

        <div id="global-modal" class="fixed inset-0 bg-black/50 hidden flex items-center justify-center z-50 p-4">
            <div class="bg-white rounded-xl shadow-xl border border-gray-200 max-w-sm w-full overflow-hidden">
                <div class="bg-gray-900 text-white px-6 py-4 flex justify-between items-center">
                    <h3 class="font-bold text-sm uppercase tracking-wider"><i class="fa-solid fa-gears text-amber-400 mr-1"></i> Parámetros Operativos</h3>
                    <button type="button" onclick="closeGlobalModal()" class="text-gray-400 hover:text-white text-xl cursor-pointer">&times;</button>
                </div>
                <form action="{{ route('condensacion.guardar') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    <input type="hidden" name="fecha" id="global-modal-fecha">

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Total Nacional Global</label>
                        <input type="number" step="0.01" min="0" name="total_nacional_global" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:outline-hidden transition" placeholder="Introduce el total nacional diario...">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Total Empacados Global</label>
                        <input type="number" step="0.01" min="0" name="total_empacados_global" required class="w-full text-sm border border-gray-300 rounded-lg px-3 py-2 bg-gray-50 focus:bg-white focus:border-indigo-500 focus:outline-hidden transition" placeholder="Introduce el total empacados diario...">
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                        <button type="button" onclick="closeGlobalModal()" class="px-4 py-2 text-sm bg-gray-100 text-gray-700 hover:bg-gray-200 rounded-lg transition">Cancelar</button>
                        <button type="submit" class="px-4 py-2 text-sm bg-gray-800 text-white hover:bg-gray-900 rounded-lg transition font-medium">Calcular y Distribuir</button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500 w-full mt-auto">
        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.
    </footer>

    <script>
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.toggle('hidden');
            }
        }

        function openGlobalModal(fecha) {
            document.getElementById('global-modal-fecha').value = fecha;
            document.getElementById('global-modal').classList.remove('hidden');
        }

        function closeGlobalModal() {
            document.getElementById('global-modal').classList.add('hidden');
        }
    </script>
</body>

</html>