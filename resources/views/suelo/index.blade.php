<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoreo Suelo - Sistema Control</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- 🔌 CDNs Obligatorias para Flatpickr (Estilos y Plugin de Semanas) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/weekSelect/weekSelect.css">
</head>

<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

   <nav class="bg-emerald-600 text-white shadow-md">
    <div class="max-w-[95%] mx-auto px-3 sm:px-4 h-14 sm:h-16 flex items-center justify-between gap-2">
        <!-- Logotipo compacto -->
        <div class="flex items-center min-w-0">
            <i class="fa-solid fa-leaf text-lg sm:text-2xl mr-1.5 sm:mr-2 text-emerald-200"></i>
            <span class="font-bold text-sm sm:text-xl tracking-wider truncate">SISTEMA CONTROL</span>
        </div>

        <!-- Acciones adaptadas con truncamiento de texto -->
        <div class="flex items-center gap-1.5 sm:gap-3 text-xs shrink-0">
            <span class="bg-emerald-700/80 px-2.5 py-1 rounded-md flex items-center gap-1 max-w-[120px] sm:max-w-none truncate" title="{{ auth()->user()->name }}">
                <i class="fa-solid fa-user text-[10px]"></i> 
                <span class="truncate">{{ auth()->user()->name }}</span>
            </span>
            <a href="{{ route('dashboard') }}" class="bg-emerald-700 hover:bg-emerald-800 px-2.5 sm:px-3.5 py-1.5 rounded-md transition flex items-center gap-1 font-medium shadow-2xs whitespace-nowrap">
                <i class="fa-solid fa-circle-chevron-left text-[10px]"></i> 
                <span class="hidden xs:inline">Volver al Panel</span>
                <span class="inline xs:hidden">Panel</span>
            </a>
        </div>
    </div>
</nav>
    <main class="max-w-[95%] mx-auto px-4 py-8 w-full flex-grow">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-mound text-emerald-600"></i>
                    Bitácora de Monitoreo en Suelo
                </h1>
                <p class="text-gray-600 text-sm mt-1">Historial operativo de humedad del suelo (tensiómetro), balances químicos directos y estados bioclimáticos.</p>
            </div>
            <div>
                <a href="{{ route('suelo.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded shadow">
                    <i class="fa-solid fa-plus mr-2"></i> Nuevo Registro Suelo
                </a>
            </div>
        </div>

        @if(session('status'))
        <div class="mb-6 p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-900 rounded-r-lg shadow-sm flex items-center justify-between">
            <div class="flex items-center">
                <i class="fa-solid fa-circle-check text-xl mr-3 text-emerald-600"></i>
                <span class="font-medium text-sm">{{ session('status') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700 font-bold text-lg cursor-pointer">&times;</button>
        </div>
        @endif

        <!-- BLOQUE DE FILTROS OPTIMIZADO -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" action="{{ route('suelo.index') }}" id="formFiltros" class="grid grid-cols-1 md:grid-cols-12 items-end gap-4">

                {{-- 📅 FILTRADO POR SEMANA (CON FLATPICKR) --}}
                <div class="col-span-1 md:col-span-4">
                    <label for="semana_picker" class="block text-xs font-bold text-gray-600 uppercase mb-1.5 tracking-wider">Filtrar por Semana:</label>
                    <div class="flex items-center gap-2">
                        <input type="hidden" name="semana" id="semana_final_input" value="{{ request('semana') }}">
                        <div class="relative w-full">
                            <input type="text"
                                id="semana_picker"
                                placeholder="Seleccione un día..."
                                readonly
                                class="w-full bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-lg focus:outline-emerald-500 p-2 pl-9 cursor-pointer shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 text-xs">
                                <i class="fa-solid fa-calendar-days"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 🔍 BUSCADOR POR SECTOR O TRABAJADOR --}}
                @can('es-administrador')
                <div class="col-span-1 md:col-span-7 flex gap-2 items-end">
                    <div class="w-full">
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1.5 tracking-wider">Buscar por Sector u Operador:</label>
                        <div class="relative">
                            <input type="text" name="buscar_termino" id="buscar_termino" value="{{ request('buscar_termino') }}" placeholder="Ej: Sector 1 o Nombre del trabajador..." class="w-full bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-lg p-2 pr-8">
                            @if(request('buscar_termino'))
                            <button type="button" onclick="document.getElementById('buscar_termino').value=''; this.form.submit();" class="absolute right-2.5 top-2.5 text-gray-400 hover:text-gray-600 text-xs">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                            @endif
                        </div>
                    </div>
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white font-bold h-9 px-3 rounded-lg flex items-center justify-center text-sm shadow transition cursor-pointer">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
                @endcan

                {{-- 🧹 BOTÓN PARA LIMPIAR FILTROS --}}
                @if(request('semana') || request('buscar_termino'))
                <div class="col-span-1 md:col-span-1 pb-2">
                    <a href="{{ route('suelo.index') }}" class="text-xs text-red-600 hover:text-red-700 font-bold flex items-center justify-center gap-1 transition h-9 border border-red-200 rounded-lg bg-red-5/40 hover:bg-red-5">
                        <i class="fa-solid fa-filter-circle-xmark"></i> Limpiar
                    </a>
                </div>
                @endif
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
            <div class="p-5 border-b border-gray-100 bg-gray-50">
                <span class="font-semibold text-gray-700 text-sm">Resumen de Evaluaciones en Suelo</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-200 text-gray-700 uppercase tracking-wider text-[11px] font-bold">
                            <th class="py-3 px-4">Fecha</th>
                            <th class="py-3 px-4">Sector</th>
                            <th class="py-3 px-4">Dueño del Sector</th>
                            <th class="py-3 px-4">DPV Clima</th>
                            <th class="py-3 px-4 bg-blue-50/50">Tensiómetro / Estado</th>
                            <th class="py-3 px-4 bg-orange-50/50">Radiación (Lux)</th>
                            <th class="py-3 px-4 text-center">Estatus Clima</th>
                            <th class="py-3 px-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700 text-sm">
                        @forelse($monitoreos as $row)
                        @php
                            $colorRadiacion = $row->radiacion_semaforo === 'VERDE' ? 'text-emerald-600 font-bold' : ($row->radiacion_semaforo === 'AMARILLO' ? 'text-amber-600 font-bold' : 'text-red-600 font-bold');
                        @endphp
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="py-3.5 px-4 font-medium">
                                {{ \Carbon\Carbon::parse($row->fecha)->format('d/m/Y') }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded font-semibold">{{ $row->sector }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="text-xs text-gray-600 font-medium flex items-center gap-1">
                                    <i class="fa-solid fa-user-tie text-emerald-600 text-[10px]"></i>
                                    {{ $row->dueno_sector }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold">{{ $row->dpv ?? '—' }}</td>

                            <td class="py-3.5 px-4 bg-blue-50/20">
                                <span class="font-bold text-blue-600 block text-sm">{{ $row->lectura_tensiometro ?? '—' }}</span>
                                @if($row->tensiometro_estatus)
                                @if($row->tensiometro_estatus === 'SUELO SATURADO')
                                <span class="text-[10px] px-1.5 py-0.5 inline-block font-bold rounded bg-blue-100 text-blue-800 mt-1">Saturado</span>
                                @elseif($row->tensiometro_estatus === 'HUMEDAD ADECUADA')
                                <span class="text-[10px] px-1.5 py-0.5 inline-block font-bold rounded bg-emerald-100 text-emerald-800 mt-1">Óptimo</span>
                                @elseif($row->tensiometro_estatus === 'SOLICITAR RIEGO')
                                <span class="text-[10px] px-1.5 py-0.5 inline-block font-bold rounded bg-amber-100 text-amber-800 mt-1">Falta Riego</span>
                                @elseif($row->tensiometro_estatus === 'SUELO SECO CRÍTICO')
                                <span class="text-[10px] px-1.5 py-0.5 inline-block font-black rounded bg-red-100 text-red-800 mt-1 animate-pulse">¡Seco Crítico!</span>
                                @endif
                                @else
                                <span class="text-[10px] text-gray-400 italic">Sin diagnóstico</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 bg-orange-50/10 font-medium">
                                <span class="{{ $colorRadiacion }}">{{ number_format($row->radiacion_lectura) }}</span>
                                <span class="text-[10px] block font-semibold text-gray-500">
                                    {{ $row->radiacion_semaforo }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $row->estatus_general === 'ÓPTIMO' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $row->estatus_general }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    <button type="button"
                                        onclick="mostrarDetalleSuelo(this)"
                                        data-fecha="{{ \Carbon\Carbon::parse($row->fecha)->format('d/m/Y') }}"
                                        data-sector="{{ $row->sector }}"
                                        data-operador="{{ $row->dueno_sector }}"
                                        data-temperatura="{{ $row->temperatura ?? '—' }}"
                                        data-humedad="{{ $row->humedad ?? '—' }}"
                                        data-dpv="{{ $row->dpv ?? '—' }}"
                                        data-estatus_clima="{{ $row->estatus_general }}"
                                        data-tensiometro="{{ $row->lectura_tensiometro ?? '—' }}"
                                        data-tensiometro_estatus="{{ $row->tensiometro_estatus ?? 'Sin diagnostico' }}"
                                        data-ce="{{ $row->ce ?? '—' }}"
                                        data-ph="{{ $row->ph ?? '—' }}"
                                        data-alerta_ce="{{ $row->alerta_ce_opcion ?? 'Ninguna' }}"
                                        data-radiacion_num="{{ number_format($row->radiacion_lectura) }}"
                                        data-radiacion_semaforo="{{ $row->radiacion_semaforo }}"
                                        data-radiacion_accion="{{ $row->radiacion_accion_tomada ?? 'Ninguna' }}"
                                        data-rapido_cumplio="{{ strtoupper($row->analisis_rapido_cumplio) }}"
                                        data-tipo_lab="{{ $row->tipo_analisis_lab ?? 'ninguno' }}"
                                        data-analisis_rapidos="{{ json_encode($row->analisisRapidos) }}"
                                        data-l_mo="{{ $row->lab_mo ?? '—' }}" data-l_pbray="{{ $row->lab_p_bray ?? '—' }}"
                                        data-l_k="{{ $row->lab_k ?? '—' }}" data-l_mg="{{ $row->lab_mg ?? '—' }}"
                                        data-l_na="{{ $row->lab_na ?? '—' }}" data-l_fe="{{ $row->lab_fe ?? '—' }}"
                                        data-l_zn="{{ $row->lab_zn ?? '—' }}" data-l_mn="{{ $row->lab_mn ?? '—' }}"
                                        data-l_cu="{{ $row->lab_cu ?? '—' }}" data-l_b="{{ $row->lab_b ?? '—' }}"
                                        data-l_s="{{ $row->lab_s ?? '—' }}" data-l_nno3="{{ $row->lab_n_no3 ?? '—' }}"
                                        class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-3 py-1.5 rounded-md transition shadow flex items-center gap-1 cursor-pointer">
                                        <i class="fa-solid fa-eye"></i> Ver Detalle
                                    </button>

                                    @if(auth()->user()->rol === 'administrador')
                                    <form action="{{ route('suelo.destroy', $row->id) }}" method="POST" id="form-delete-suelo-{{ $row->id }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="mostrarModalGenerico('form-delete-suelo-{{ $row->id }}')" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-3 py-1.5 rounded-md transition shadow flex items-center gap-1 cursor-pointer">
                                            <i class="fa-solid fa-trash-can"></i> Eliminar
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-gray-500">
                                <i class="fa-solid fa-folder-open text-4xl text-gray-300 mb-3 block"></i>
                                No hay registros almacenados en suelo.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    {{-- MODAL INTERACTIVO DE DETALLES --}}
    <div id="modal_detalle_suelo" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4 animate-fade-in">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-4xl my-auto flex flex-col overflow-hidden max-h-[90vh]">
            <div class="bg-emerald-600 text-white px-6 py-4 flex justify-between items-center shrink-0">
                <h3 class="text-base font-bold uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-file-invoice text-xl"></i> Reporte Detallado de Inspección de Suelo
                </h3>
                <button type="button" onclick="cerrarModalDetalle()" class="text-white/80 hover:text-white text-2xl font-bold cursor-pointer outline-none">&times;</button>
            </div>

            <div class="p-6 space-y-6 overflow-y-auto flex-grow scrollbar-thin text-xs overscroll-behavior-contain">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <div><span class="text-gray-400 block uppercase font-bold">Fecha</span><span id="md_fecha" class="font-bold text-gray-800 text-sm"></span></div>
                    <div><span class="text-gray-400 block uppercase font-bold">Sector / Nave</span><span id="md_sector" class="font-bold text-gray-800 text-sm"></span></div>
                    <div><span class="text-gray-400 block uppercase font-bold">Dueño del Sector</span><span id="md_operador" class="font-bold text-gray-800 text-sm"></span></div>
                    <div><span class="text-gray-400 block uppercase font-bold">Estatus General</span><span id="md_estatus_clima" class="font-black"></span></div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <div class="bg-gray-100 p-2 font-bold text-gray-700 border-b border-gray-200">Conditions Ambientales</div>
                        <table class="w-full text-left font-medium divide-y divide-gray-100">
                            <tr class="divide-x divide-gray-100">
                                <td class="p-2 text-gray-500">Temperatura</td>
                                <td id="md_temp" class="p-2 font-bold text-gray-800"></td>
                            </tr>
                            <tr class="divide-x divide-gray-100">
                                <td class="p-2 text-gray-500">Humedad Clima</td>
                                <td id="md_hum" class="p-2 font-bold text-gray-800"></td>
                            </tr>
                            <tr class="divide-x divide-gray-100">
                                <td class="p-2 text-gray-500">DPV Calculado</td>
                                <td id="md_dpv" class="p-2 font-mono font-bold text-gray-800"></td>
                            </tr>
                            <tr class="divide-x divide-gray-100">
                                <td class="p-2 text-gray-500">Radiación Registrada</td>
                                <td class="p-2 font-bold text-gray-800">
                                    <span id="md_radiacion_num"></span> <span id="md_radiacion_semaforo" class="text-[10px] ml-1"></span>
                                </td>
                            </tr>
                            <tr class="divide-x divide-gray-100">
                                <td class="p-2 text-gray-500">Acción Radiación</td>
                                <td id="md_radiacion_accion" class="p-2 text-gray-600"></td>
                            </tr>
                        </table>
                    </div>

                    <div class="border border-gray-200 rounded-xl overflow-hidden">
                        <div class="bg-gray-100 p-2 font-bold text-gray-700 border-b border-gray-200">Física y Química Base</div>
                        <table class="w-full text-left font-medium divide-y divide-gray-100">
                            <tr class="divide-x divide-gray-100">
                                <td class="p-2 text-gray-500">Tensiómetro</td>
                                <td id="md_tensiometro" class="p-2 font-bold text-gray-800"></td>
                            </tr>
                            <tr class="divide-x divide-gray-100">
                                <td class="p-2 text-gray-500">Estatus del Suelo</td>
                                <td id="md_tensiometro_estatus" class="p-2 font-bold text-gray-800"></td>
                            </tr>
                            <tr class="divide-x divide-gray-100">
                                <td class="p-2 text-gray-500">Conductividad (CE)</td>
                                <td id="md_ce" class="p-2 font-bold text-gray-800"></td>
                            </tr>
                            <tr class="divide-x divide-gray-100">
                                <td class="p-2 text-gray-500">Potencial pH</td>
                                <td id="md_ph" class="p-2 font-bold text-gray-800"></td>
                            </tr>
                            <tr class="divide-x divide-gray-100">
                                <td class="p-2 text-gray-500">Acción Alerta CE</td>
                                <td id="md_alerta_ce" class="p-2 font-bold text-red-700"></td>
                            </tr>
                        </table>
                    </div>
                </div>

                {{-- CONTAINER MÚLTIPLE PARA ANÁLISIS RÁPIDOS (EPS Y ECP DINÁMICOS DESDE TABLA RELACIONAL) --}}
                <div class="border border-cyan-200 rounded-xl overflow-hidden">
                    <div class="bg-cyan-600 text-white px-3 py-2.5 font-bold flex justify-between items-center">
                        <span>Resultados: Análisis Rápidos Realizados en Campo</span>
                        <span id="md_rapido_cumplio" class="bg-white text-cyan-800 px-2 py-0.5 rounded font-black text-[10px]"></span>
                    </div>
                    <div id="contenedor_tablas_rapidas" class="p-4 space-y-4 bg-gray-50/50">
                        <!-- Generado por JS -->
                    </div>
                </div>

                {{-- DETALLE DE LABORATORIO --}}
                <div id="md_box_laboratorio" class="border border-emerald-200 rounded-xl overflow-hidden hidden">
                    <div class="bg-emerald-600 text-white p-2.5 font-bold flex justify-between items-center">
                        <span><i class="fa-solid fa-microscope mr-1"></i> Desglose Completo: Análisis de Laboratorio</span>
                        <span id="md_tipo_lab_badge" class="bg-white text-emerald-800 px-2 py-0.5 rounded font-bold text-[10px] uppercase"></span>
                    </div>
                    <div class="grid grid-cols-3 sm:grid-cols-6 text-center divide-x divide-y divide-gray-200 font-semibold bg-white" id="grid_laboratorio_elementos">
                        <div class="p-2" id="box_l_mo"><span class="text-gray-400 block text-[10px] uppercase">MO</span><span id="md_l_mo">—</span></div>
                        <div class="p-2" id="box_l_pbray"><span class="text-gray-400 block text-[10px] uppercase">P-Bray</span><span id="md_l_pbray">—</span></div>
                        <div class="p-2" id="box_l_k"><span class="text-gray-400 block text-[10px] uppercase">K</span><span id="md_l_k">—</span></div>
                        <div class="p-2" id="box_l_mg"><span class="text-gray-400 block text-[10px] uppercase">Mg</span><span id="md_l_mg">—</span></div>
                        <div class="p-2" id="box_l_na"><span class="text-gray-400 block text-[10px] uppercase">Na</span><span id="md_l_na">—</span></div>
                        <div class="p-2" id="box_l_fe"><span class="text-gray-400 block text-[10px] uppercase">Fe</span><span id="md_l_fe">—</span></div>
                        <div class="p-2" id="box_l_zn"><span class="text-gray-400 block text-[10px] uppercase">Zn</span><span id="md_l_zn">—</span></div>
                        <div class="p-2" id="box_l_mn"><span class="text-gray-400 block text-[10px] uppercase">Mn</span><span id="md_l_mn">—</span></div>
                        <div class="p-2" id="box_l_cu"><span class="text-gray-400 block text-[10px] uppercase">Cu</span><span id="md_l_cu">—</span></div>
                        <div class="p-2" id="box_l_b"><span class="text-gray-400 block text-[10px] uppercase">B</span><span id="md_l_b">—</span></div>
                        <div class="p-2" id="box_l_s"><span class="text-gray-400 block text-[10px] uppercase">S</span><span id="md_l_s">—</span></div>
                        <div class="p-2" id="box_l_nno3"><span class="text-gray-400 block text-[10px] uppercase">N-NO3</span><span id="md_l_nno3">—</span></div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-100 p-4 border-t border-gray-200 flex justify-end shrink-0">
                <button type="button" onclick="cerrarModalDetalle()" class="px-5 py-2 bg-gray-700 hover:bg-gray-800 text-white font-bold rounded-lg text-xs transition cursor-pointer">Cerrar Inspección</button>
            </div>
        </div>
    </div>

    <!-- MODAL DE ELIMINACIÓN UNIFICADO -->
<div id="modalGenericoEliminar" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 text-center transform transition-all scale-95 opacity-0 duration-200 border border-gray-100" id="modalContenidoGenerico">
        <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4 text-xl">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-800 mb-1">¿Eliminar registro?</h3>
        <p class="text-xs text-gray-500 mb-6">Esta acción no se puede deshacer y borrará los datos seleccionados del sistema.</p>
        <div class="flex gap-3">
            <button type="button" onclick="cerrarModalGenerico()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-xl transition text-xs cursor-pointer">
                Cancelar
            </button>
            <button type="button" id="btnConfirmarEliminarGenerico" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-xl transition text-xs cursor-pointer shadow-md">
                Sí, eliminar
            </button>
        </div>
    </div>
</div>

    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500 w-full mt-auto">
        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.
    </footer>

    <!-- 🔌 SCRIPTS DE CONTEXTO Y FUNCIONAMIENTO FLATPICKR -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/weekSelect/weekSelect.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            flatpickr("#semana_picker", {
                locale: "es",
                firstDayOfWeek: 1, // Iniciar en Lunes
                defaultDate: "{{ request('semana') }}" ? null : null,
                plugins: [new weekSelect({})],
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length > 0) {
                        const numeroSemana = instance.config.getWeek(selectedDates[0]);
                        const anio = selectedDates[0].getFullYear();
                        const stringSemanaFinal = anio + "-W" + String(numeroSemana).padStart(2, '0');

                        document.getElementById("semana_final_input").value = stringSemanaFinal;
                        document.getElementById("formFiltros").submit();
                    }
                }
            });

            const semanaActual = "{{ request('semana') }}";
            if (semanaActual) {
                document.getElementById("semana_picker").value = "Semana " + semanaActual.split("-W")[1] + ", " + semanaActual.split("-W")[0];
            }
        });

        // LÓGICA DE DIÁGNOSTICO DE SEMÁFOROS DINÁMICOS EN BASE A REQUISITOS WORD
        function obtenerClaseSemaforo(tipo, elemento, valorStr) {
            const valor = parseFloat(valorStr);
            if (isNaN(valor)) return 'text-gray-500 font-semibold';

            const clases = {
                bajo: 'text-red-600 bg-red-50 border border-red-200 px-1.5 py-0.5 rounded font-bold',
                optimo: 'text-green-600 bg-green-50 border border-green-200 px-1.5 py-0.5 rounded font-bold',
                alto: 'text-amber-600 bg-amber-50 border border-amber-200 px-1.5 py-0.5 rounded font-bold'
            };

            // 1. ANÁLISIS RÁPIDO: EXTRACTO DE PASTA SATURADA (EPS)
            if (tipo === 'eps') {
                switch (elemento) {
                    case 'no3':
                        return (valor < 150) ? clases.bajo : (valor <= 250) ? clases.optimo : clases.alto;
                    case 'p':
                        return (valor < 15) ? clases.bajo : (valor <= 30) ? clases.optimo : clases.alto;
                    case 'k':
                        return (valor < 117) ? clases.bajo : (valor <= 234) ? clases.optimo : clases.alto;
                    case 'ca':
                        return (valor < 120) ? clases.bajo : (valor <= 200) ? clases.optimo : clases.alto;
                    case 'na':
                        return (valor <= 30) ? clases.optimo : (valor <= 60) ? clases.optimo : clases.bajo;
                    case 'ph':
                        return (valor < 5.5) ? clases.bajo : (valor <= 6.5) ? clases.optimo : clases.alto;
                    case 'ce':
                        return (valor < 2.0) ? clases.bajo : (valor <= 3.5) ? clases.optimo : clases.alto;
                }
            }
            // 2. ANÁLISIS RÁPIDO: EXTRACTO CELULAR (ECP - SAVIA)
            if (tipo === 'ecp') {
                switch (elemento) {
                    case 'no3':
                        return (valor < 500) ? clases.bajo : (valor <= 800) ? clases.optimo : clases.alto;
                    case 'k':
                        return (valor < 3000) ? clases.bajo : (valor <= 5000) ? clases.optimo : clases.alto;
                    case 'ca':
                        return (valor < 200) ? clases.bajo : (valor <= 450) ? clases.optimo : clases.alto;
                    case 'na':
                        return (valor <= 40) ? clases.optimo : (valor <= 100) ? clases.optimo : clases.bajo;
                    case 'p':
                        return (valor < 200) ? clases.bajo : (valor <= 400) ? clases.optimo : clases.alto;
                    case 'ph':
                        return (valor < 5.5) ? clases.bajo : (valor <= 6.2) ? clases.optimo : clases.alto;
                    case 'ce':
                        return (valor < 8.0) ? clases.bajo : (valor <= 12.0) ? clases.optimo : clases.alto;
                }
            }
            // 3. LABORATORIO: FERTILIDAD
            if (tipo === 'fertilidad') {
                switch (elemento) {
                    case 'n_no3':
                        return (valor < 25) ? clases.bajo : (valor <= 45) ? clases.optimo : clases.alto;
                    case 'p_bray':
                        return (valor < 25) ? clases.bajo : (valor <= 45) ? clases.optimo : clases.alto;
                    case 'k':
                        return (valor < 180) ? clases.bajo : (valor <= 300) ? clases.optimo : clases.alto;
                    case 'mg':
                        return (valor < 250) ? clases.bajo : (valor <= 450) ? clases.optimo : clases.alto;
                    case 's':
                        return (valor < 15) ? clases.bajo : (valor <= 35) ? clases.optimo : clases.alto;
                    case 'fe':
                        return (valor < 5.0) ? clases.bajo : (valor <= 15.0) ? clases.optimo : clases.alto;
                    case 'mn':
                        return (valor < 2.0) ? clases.bajo : (valor <= 10.0) ? clases.optimo : clases.alto;
                    case 'zn':
                        return (valor < 1.5) ? clases.bajo : (valor <= 3.5) ? clases.optimo : clases.alto;
                    case 'cu':
                        return (valor < 0.4) ? clases.bajo : (valor <= 1.5) ? clases.optimo : clases.alto;
                    case 'b':
                        return (valor < 0.6) ? clases.bajo : (valor <= 1.2) ? clases.optimo : clases.alto;
                }
            }
            // 4. LABORATORIO: PASTA SATURADA
            if (tipo === 'pasta_saturada') {
                switch (elemento) {
                    case 'n_no3':
                        return (valor < 150) ? clases.bajo : (valor <= 250) ? clases.optimo : clases.alto;
                    case 'p_bray':
                        return (valor < 15) ? clases.bajo : (valor <= 30) ? clases.optimo : clases.alto;
                    case 'k':
                        return (valor < 150) ? clases.bajo : (valor <= 250) ? clases.optimo : clases.alto;
                    case 'mg':
                        return (valor < 36) ? clases.bajo : (valor <= 60) ? clases.optimo : clases.alto;
                    case 'na':
                        return (valor <= 60) ? clases.optimo : clases.alto;
                    case 's':
                        return (valor < 192) ? clases.bajo : (valor <= 480) ? clases.optimo : clases.alto;
                }
            }
            return 'text-gray-800 font-bold';
        }

        function mostrarDetalleSuelo(boton) {
            document.getElementById('md_fecha').innerText = boton.getAttribute('data-fecha');
            document.getElementById('md_sector').innerText = boton.getAttribute('data-sector');
            document.getElementById('md_operador').innerText = boton.getAttribute('data-operador');

            const estClima = boton.getAttribute('data-estatus_clima');
            const mdEstClima = document.getElementById('md_estatus_clima');
            mdEstClima.innerText = estClima;
            mdEstClima.className = estClima === 'ÓPTIMO' ? 'font-black text-emerald-600 text-sm' : 'font-black text-red-600 text-sm';

            document.getElementById('md_temp').innerText = boton.getAttribute('data-temperatura') + ' °C';
            document.getElementById('md_hum').innerText = boton.getAttribute('data-humedad') + ' %';
            document.getElementById('md_dpv').innerText = boton.getAttribute('data-dpv');
            
            // Radiación detallada con color en el número
            const radNum = boton.getAttribute('data-radiacion_num');
            const radSem = boton.getAttribute('data-radiacion_semaforo');
            const spanRadNum = document.getElementById('md_radiacion_num');
            const spanRadSem = document.getElementById('md_radiacion_semaforo');
            
            spanRadNum.innerText = radNum + ' Lux';
            spanRadNum.className = radSem === 'VERDE' ? 'text-emerald-600 font-bold' : (radSem === 'AMARILLO' ? 'text-amber-600 font-bold' : 'text-red-600 font-bold');
            spanRadSem.innerText = '(' + radSem + ')';
            spanRadSem.className = 'text-[10px] ml-1 font-semibold text-gray-500';

            document.getElementById('md_radiacion_accion').innerText = boton.getAttribute('data-radiacion_accion');
            document.getElementById('md_tensiometro').innerText = boton.getAttribute('data-tensiometro') + ' cb';
            document.getElementById('md_tensiometro_estatus').innerText = boton.getAttribute('data-tensiometro_estatus');
            document.getElementById('md_ce').innerText = boton.getAttribute('data-ce');
            document.getElementById('md_ph').innerText = boton.getAttribute('data-ph');
            document.getElementById('md_alerta_ce').innerText = boton.getAttribute('data-alerta_ce');

            const cumplio = boton.getAttribute('data-rapido_cumplio');
            document.getElementById('md_rapido_cumplio').innerText = 'CUMPLIÓ: ' + cumplio;

            // Renderizado e interpretación dinámica de las tablas relacionales EPS y ECP
            const analisisRapidosJson = JSON.parse(boton.getAttribute('data-analisis_rapidos') || '[]');
            const contenedorTablas = document.getElementById('contenedor_tablas_rapidas');
            contenedorTablas.innerHTML = '';

            if (analisisRapidosJson.length === 0) {
                contenedorTablas.innerHTML = `<div class="text-center py-2 text-gray-400 italic">No se adjuntaron registros de campo EPS/ECP.</div>`;
            } else {
                analisisRapidosJson.forEach(ana => {
                    const tipoNombre = ana.tipo_analisis.toUpperCase();
                    const divFila = document.createElement('div');
                    divFila.className = "bg-white p-3 rounded-lg border border-gray-200 shadow-sm";
                    divFila.innerHTML = `
                        <div class="font-bold text-cyan-700 border-b border-gray-100 pb-1 mb-2">Método: Renglón ${tipoNombre}</div>
                        <div class="grid grid-cols-4 sm:grid-cols-7 text-center divide-x divide-gray-100">
                            <div class="p-1"><span class="text-gray-400 block text-[9px] uppercase">No3</span><span class="${obtenerClaseSemaforo(ana.tipo_analisis, 'no3', ana.no3)}">${ana.no3 ?? '—'}</span></div>
                            <div class="p-1"><span class="text-gray-400 block text-[9px] uppercase">K</span><span class="${obtenerClaseSemaforo(ana.tipo_analisis, 'k', ana.k)}">${ana.k ?? '—'}</span></div>
                            <div class="p-1"><span class="text-gray-400 block text-[9px] uppercase">Ca</span><span class="${obtenerClaseSemaforo(ana.tipo_analisis, 'ca', ana.ca)}">${ana.ca ?? '—'}</span></div>
                            <div class="p-1"><span class="text-gray-400 block text-[9px] uppercase">Na</span><span class="${obtenerClaseSemaforo(ana.tipo_analisis, 'na', ana.na)}">${ana.na ?? '—'}</span></div>
                            <div class="p-1"><span class="text-gray-400 block text-[9px] uppercase">P</span><span class="${obtenerClaseSemaforo(ana.tipo_analisis, 'p', ana.p)}">${ana.p ?? '—'}</span></div>
                            <div class="p-1"><span class="text-gray-400 block text-[9px] uppercase">pH</span><span class="${obtenerClaseSemaforo(ana.tipo_analisis, 'ph', ana.ph)}">${ana.ph ?? '—'}</span></div>
                            <div class="p-1"><span class="text-gray-400 block text-[9px] uppercase">CE</span><span class="${obtenerClaseSemaforo(ana.tipo_analisis, 'ce', ana.ce)}">${ana.ce ?? '—'}</span></div>
                        </div>
                    `;
                    contenedorTablas.appendChild(divFila);
                });
            }

            // ========================================================
            // FORZADO DE VISUALIZACIÓN DEL REPORTE DE LABORATORIO
            // ========================================================
            const tipoLab = boton.getAttribute('data-tipo_lab');
            const boxLab = document.getElementById('md_box_laboratorio');

            // Formateamos el título del badge
            if (tipoLab && tipoLab !== 'ninguno' && tipoLab !== 'null') {
                document.getElementById('md_tipo_lab_badge').innerText = 'Tipo: ' + tipoLab.replace('_', ' ');
            } else {
                document.getElementById('md_tipo_lab_badge').innerText = 'Tipo: No especificado';
            }

            const elementosLab = ['mo', 'pbray', 'k', 'mg', 'na', 'fe', 'zn', 'mn', 'cu', 'b', 's', 'nno3'];
            elementosLab.forEach(el => {
                const val = boton.getAttribute(`data-l_${el}`);
                const spanVal = document.getElementById(`md_l_${el}`);
                const boxEl = document.getElementById(`box_l_${el}`);

                if (spanVal) {
                    // Si el valor no existe o es un guion plano, dejamos '—'
                    spanVal.innerText = (val && val !== '—') ? val : '—';

                    let DB_Elemento = el === 'pbray' ? 'p_bray' : el === 'nno3' ? 'n_no3' : el;
                    spanVal.className = obtenerClaseSemaforo(tipoLab, DB_Elemento, val);
                }

                // Ocultar dinámicamente los campos de micronutrientes solo si es pasta saturada
                if (boxEl) {
                    if (tipoLab === 'pasta_saturada' && ['mo', 'fe', 'zn', 'mn', 'cu', 'b'].includes(el)) {
                        boxEl.classList.add('hidden');
                    } else {
                        boxEl.classList.remove('hidden');
                    }
                }
            });

            // 🔥 OBLIGAMOS A LA CAJA A MOSTRARSE SIEMPRE REMOVIENDO EL HIDDEN
            boxLab.classList.remove('hidden');

            const modal = document.getElementById('modal_detalle_suelo');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function cerrarModalDetalle() {
            const modal = document.getElementById('modal_detalle_suelo');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        let formIdParaEliminar = null;

function mostrarModalGenerico(formId) {
    formIdParaEliminar = formId;
    const modal = document.getElementById('modalGenericoEliminar');
    const contenido = document.getElementById('modalContenidoGenerico');
    if (!modal) return;
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        contenido.classList.remove('scale-95', 'opacity-0');
        contenido.classList.add('scale-100', 'opacity-100');
    }, 10);
}

function cerrarModalGenerico() {
    const modal = document.getElementById('modalGenericoEliminar');
    const contenido = document.getElementById('modalContenidoGenerico');
    if (!modal) return;
    contenido.classList.remove('scale-100', 'opacity-100');
    contenido.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
        formIdParaEliminar = null;
    }, 200);
}

document.addEventListener("DOMContentLoaded", function() {
    const btnConfirmar = document.getElementById('btnConfirmarEliminarGenerico');
    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', function() {
            if (formIdParaEliminar) {
                document.getElementById(formIdParaEliminar).submit();
            }
        });
    }
});
    </script>
</body>

</html>