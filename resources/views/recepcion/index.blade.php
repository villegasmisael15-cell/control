<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recepción - Sistema Control</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/weekSelect/style.css">
</head>

<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

    <!-- NAV TOTALMENTE RESPONSIVO -->
    <nav class="bg-emerald-600 text-white shadow-md">
        <div class="max-w-[95%] mx-auto px-3 sm:px-4 h-14 sm:h-16 flex items-center justify-between gap-2">
            <!-- Logotipo compacto -->
            <div class="flex items-center min-w-0">
                <i class="fa-solid fa-leaf text-lg sm:text-2xl mr-1.5 sm:mr-2 text-emerald-200"></i>
                <span class="font-bold text-sm sm:text-xl tracking-wider truncate">SISTEMA CONTROL</span>
            </div>

            <!-- Acciones con soporte para tus roles condicionales -->
            <div class="flex items-center gap-1.5 sm:gap-3 text-xs shrink-0">
                <span class="bg-emerald-700/80 px-2.5 py-1 rounded-md flex items-center gap-1 max-w-[110px] sm:max-w-none truncate" title="{{ auth()->user()->name }}">
                    <i class="fa-solid fa-user text-[10px]"></i>
                    <span class="truncate">{{ auth()->user()->name }}</span>
                </span>

                @if(auth()->user()->rol === 'administrador' || auth()->user()->rol === 'usuario_comercial')
                <a href="{{ route('dashboard') }}" class="bg-emerald-700 hover:bg-emerald-800 px-2.5 sm:px-3.5 py-1.5 rounded-md transition flex items-center gap-1 font-medium shadow-2xs whitespace-nowrap">
                    <i class="fa-solid fa-circle-chevron-left text-[10px]"></i>
                    <span class="hidden xs:inline">Volver al Panel</span>
                    <span class="inline xs:hidden">Panel</span>
                </a>
                @endif

                @if(auth()->user()->rol === 'usuario_rechazo')
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold px-2.5 py-1.5 rounded-md transition flex items-center gap-1 shadow-2xs cursor-pointer whitespace-nowrap">
                        <i class="fa-solid fa-right-from-bracket text-[10px]"></i> Salir
                    </button>
                </form>
                @endif
            </div>
        </div>
    </nav>

    <main class="max-w-[95%] mx-auto px-2 sm:px-4 py-6 sm:py-8 w-full flex-grow">
        @if (session('status'))
        <div class="mb-6 p-4 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl text-emerald-800 text-sm font-semibold shadow-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-base"></i>
            {{ session('status') }}
        </div>
        @endif

        <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex-grow">
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-boxes-packing text-amber-600"></i> Módulo de Recepción
                </h1>
                <p class="text-gray-500 text-xs sm:text-sm mt-0.5">Gestión e ingresos de huertas nacionales y exportación.</p>
            </div>

            <form method="GET" action="{{ route('recepcion.index') }}" id="formSemana" class="w-full sm:w-auto flex-shrink-0">
                <label for="semana_picker" class="block text-xs font-bold text-gray-600 uppercase mb-1 tracking-wider">Filtrar por Semana:</label>

                <div class="flex items-center gap-2">
                    <!-- Input real que se envía a Laravel -->
                    <input type="hidden" name="semana" id="semana_final_input" value="{{ $semanaActiva }}">

                    <!-- Input estético controlado por el calendario Flatpickr -->
                    <input type="text"
                        id="semana_picker"
                        placeholder="Seleccione un día..."
                        readonly
                        class="w-full bg-white border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 p-2 cursor-pointer shadow-sm outline-none">

                    {{-- Botón para limpiar filtro --}}
                    @if(request()->filled('semana'))
                    <a href="{{ route('recepcion.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-500 border border-gray-300 rounded-lg p-2 text-sm transition h-[38px] flex items-center justify-center" title="Limpiar Filtro">
                        <i class="fa-solid fa-filter-circle-xmark"></i>
                    </a>
                    @endif
                </div>
            </form>
        </div>

        <div class="mb-6 bg-white p-1.5 rounded-xl shadow-sm border border-gray-200 flex flex-col sm:flex-row gap-1.5">
            <button onclick="cambiarPestaña('nacional')" id="btn-tab-nacional" class="w-full sm:w-auto px-5 py-2.5 font-bold text-sm rounded-lg transition flex items-center justify-center gap-2 cursor-pointer bg-amber-600 text-white shadow-md">
                <i class="fa-solid fa-house-chimney"></i> Nacional/Rechazo
            </button>

            @if(auth()->user()->rol !== 'usuario_rechazo')
            <button onclick="cambiarPestaña('exportacion')" id="btn-tab-exportacion" class="w-full sm:w-auto px-5 py-2.5 font-bold text-sm rounded-lg transition flex items-center justify-center gap-2 cursor-pointer bg-gray-100 text-gray-600 hover:bg-gray-200">
                <i class="fa-solid fa-plane-departure"></i> Exportación
            </button>
            @endif
        </div>

        <!-- CONTENIDO NACIONAL -->
        <div id="contenido-nacional" class="block space-y-6">
            <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                <div class="p-4 sm:p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gray-50/50 rounded-xl">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-800">Panel de Acciones Nacional</h3>
                        <p class="text-xs text-gray-500">Registros y mermas operativas del día seleccionado.</p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        @if(auth()->user()->rol === 'administrador' || auth()->user()->rol === 'usuario_comercial')
                        <button onclick="abrirModalNacional('recepcion')" class="w-full sm:w-auto justify-center bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-lg text-xs sm:text-sm transition shadow flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid fa-plus"></i> Registrar Recepción
                        </button>
                        @endif

                        @if(auth()->user()->rol === 'administrador' || auth()->user()->rol === 'usuario_rechazo')
                        <button onclick="abrirModalNacional('rechazo')" class="w-full sm:w-auto justify-center bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-2 rounded-lg text-xs sm:text-sm transition shadow flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid fa-ban"></i> Capturar Kg de Rechazo
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            @php
            $nacionalesAgrupados = $recepcionesNacionales->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->fecha_nacional)->format('Y-m-d');
            });
            @endphp

            @forelse($nacionalesAgrupados as $fechaKey => $grupoNacional)
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 mb-6">
                <div class="p-4 sm:p-6 border-b border-gray-100 bg-gray-50/50 rounded-t-xl">
                    <h3 class="text-base sm:text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-day text-amber-600"></i>
                        Recepciones del Día: {{ \Carbon\Carbon::parse($fechaKey)->format('d/m/Y') }}
                    </h3>
                </div>

                <div class="overflow-x-auto w-full block">
                    <table class="w-full text-sm text-left text-gray-500 min-w-[900px]">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b border-gray-200 tracking-wider">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-center" rowspan="2">Semana #</th>
                                <th scope="col" class="px-4 py-3" rowspan="2">Fecha</th>
                                <th scope="col" class="px-4 py-3" rowspan="2">Productor</th>
                                <th scope="col" class="px-4 py-2 text-center bg-emerald-50 text-emerald-800 border-x border-gray-200" colspan="2">Nacional Recepcion</th>
                                <th scope="col" class="px-4 py-2 text-center bg-red-50 text-red-800 border-x border-gray-200" colspan="2">Nacional Rechazo</th>
                                <th scope="col" class="px-4 py-2 text-center bg-gray-200 text-gray-800" colspan="2">Totales Acumulados</th>
                                <th scope="col" class="px-4 py-3 text-center" rowspan="2">Acciones</th>
                            </tr>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-3 py-2 text-center border-x border-gray-200 text-xs font-semibold text-emerald-700">Cajas</th>
                                <th class="px-3 py-2 text-center border-x border-gray-200 text-xs font-semibold text-emerald-700">Peso (Kg)</th>
                                <th class="px-3 py-2 text-center border-x border-gray-200 text-xs font-semibold text-red-700">Cajas</th>
                                <th class="px-3 py-2 text-center border-x border-gray-200 text-xs font-semibold text-red-700">Peso (Kg)</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700">Total Cajas</th>
                                <th class="px-3 py-2 text-center text-xs font-semibold text-gray-700">Total Kg</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($grupoNacional as $nacional)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="px-4 py-3 font-bold text-gray-900 text-center">{{ $nacional->semana_nacional }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700">
                                    {{ \Carbon\Carbon::parse($nacional->fecha_nacional)->format('d/m/Y') }}
                                </td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $nacional->productor->name ?? 'N/A' }}</td>
                                <td class="px-3 py-3 text-center bg-emerald-50/20 font-semibold text-gray-900">
                                    <div>{{ number_format($nacional->cajas_comerciales_vigentes) }}</div>
                                    @if($nacional->fue_ajustado)
                                    <span class="text-[11px] text-gray-400 line-through block font-normal mt-0.5">Orig: {{ number_format($nacional->cajas_comercializar) }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center bg-emerald-50/20 text-gray-700">
                                    <div class="font-medium">{{ number_format($nacional->peso_comercial_vigente, 2) }} kg</div>
                                    @if($nacional->fue_ajustado)
                                    <span class="text-[11px] text-gray-400 line-through block mt-0.5">Orig: {{ number_format($nacional->peso_comercializar, 2) }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-3 text-center bg-red-50/20 font-semibold text-gray-900">{{ number_format($nacional->cajas_rechazo_procesado) }}</td>
                                <td class="px-3 py-3 text-center bg-red-50/20 text-gray-700">{{ number_format($nacional->peso_rechazo_procesado, 2) }}</td>
                                <td class="px-3 py-3 text-center bg-gray-50 font-bold text-gray-900">{{ number_format($nacional->total_cajas) }}</td>
                                <td class="px-3 py-3 text-center bg-gray-50 font-bold text-amber-700">{{ number_format($nacional->total_kg, 2) }}</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('recepcion.showNacional', $nacional->id) }}" class="text-emerald-600 hover:text-emerald-800 p-1" title="Ver Reporte">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @can('es-administrador')
                                        <form action="{{ route('recepcion.destroyNacional', $nacional->id) }}" method="POST" id="form-delete-nacional-{{ $nacional->id }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="mostrarModalGenerico('form-delete-nacional-{{ $nacional->id }}')" class="text-red-600 hover:text-red-800 p-1 transition cursor-pointer" title="Eliminar">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t-2 border-gray-300 bg-gray-100 font-bold text-gray-900">
                            <tr>
                                <td class="px-4 py-3 text-center text-xs uppercase text-gray-500 tracking-wider" colspan="3">Subtotal Día</td>
                                <td class="px-3 py-3 text-center bg-emerald-100/60 text-gray-950 font-extrabold">{{ number_format($grupoNacional->sum('cajas_comerciales_vigentes')) }}</td>
                                <td class="px-3 py-3 text-center bg-emerald-100/60 text-gray-950 font-extrabold">{{ number_format($grupoNacional->sum('peso_comercial_vigente'), 2) }} kg</td>
                                <td class="px-3 py-3 text-center bg-red-100/60 text-red-950 font-extrabold">{{ number_format($grupoNacional->sum('cajas_rechazo_procesado')) }}</td>
                                <td class="px-3 py-3 text-center bg-red-100/60 text-red-950 font-extrabold">{{ number_format($grupoNacional->sum('peso_rechazo_procesado'), 2) }} kg</td>
                                <td class="px-3 py-3 text-center bg-gray-200 text-gray-950 font-black">{{ number_format($grupoNacional->sum('total_cajas')) }}</td>
                                <td class="px-3 py-3 text-center bg-gray-200 text-amber-900 font-black">{{ number_format($grupoNacional->sum('total_kg'), 2) }} kg</td>
                                <td class="bg-gray-100"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            @empty
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 text-center text-sm text-gray-400 font-medium">
                No hay registros nacionales en esta semana.
            </div>
            @endforelse
        </div>

        <!-- CONTENIDO EXPORTACIÓN -->
        <div id="contenido-exportacion" class="hidden space-y-6">
            <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                <div class="p-4 sm:p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gray-50/50 rounded-xl">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-800">Panel de Acciones Exportación</h3>
                        <p class="text-xs text-gray-500">Bitácora de embarques y control de saldos semanal.</p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        @if(auth()->user()->rol === 'administrador' || auth()->user()->rol === 'usuario_comercial')
                        <button onclick="abrirModalExportacion()" class="w-full sm:w-auto justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded-lg text-xs sm:text-sm transition shadow flex items-center gap-1 cursor-pointer">
                            <i class="fa-solid fa-plus"></i> Registrar Exportación
                        </button>
                        @endif
                    </div>
                </div>
            </div>

            @php
            $exportacionesAgrupadas = $recepcionesExportaciones->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->fecha_exportacion)->format('Y-m-d');
            });
            @endphp

            @forelse($exportacionesAgrupadas as $fechaKey => $grupoExportacion)
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 mb-6 overflow-hidden">
                <div class="p-4 sm:p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gray-50/50">
                    <h3 class="text-base sm:text-lg font-bold text-gray-800 flex items-center gap-2">
                        <i class="fa-solid fa-calendar-day text-blue-600"></i>
                        Embarques del Día: {{ \Carbon\Carbon::parse($fechaKey)->format('d/m/Y') }}
                    </h3>

                    <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto justify-end">
                        @if(auth()->user()->rol === 'administrador' || auth()->user()->rol === 'usuario_comercial')
                        <button onclick="abrirModalRestituidasPorFecha('{{ $fechaKey }}')" class="w-full sm:w-auto justify-center bg-purple-600 hover:bg-purple-700 text-white font-bold px-4 py-2.5 rounded-lg text-xs sm:text-sm transition shadow flex items-center gap-1 cursor-pointer whitespace-nowrap">
                            <i class="fa-solid fa-boxes-packing"></i> Restituir Cajas
                        </button>

                        {{-- BOTÓN COMPAÑERO: CAJAS ENVIADAS --}}
                        <button onclick="abrirModalEnviadasPorFecha('{{ $fechaKey }}')" class="w-full sm:w-auto justify-center bg-cyan-600 hover:bg-cyan-700 text-white font-bold px-4 py-2.5 rounded-lg text-xs sm:text-sm transition shadow flex items-center gap-1 cursor-pointer whitespace-nowrap">
                            <i class="fa-solid fa-truck-ramp-box"></i> Cajas Enviadas
                        </button>
                        @endif

                        @if(auth()->user()->rol === 'administrador')
                        <button onclick="abrirModalCondensacion('{{ $fechaKey }}')" class="w-full sm:w-auto justify-center bg-red-600 hover:bg-red-700 text-white font-bold px-4 py-2.5 rounded-lg text-xs sm:text-sm transition shadow flex items-center gap-1.5 cursor-pointer whitespace-nowrap">
                            <i class="fa-solid fa-percentage"></i> Agropark
                        </button>
                        @endif
                    </div>
                </div>

                <div class="overflow-x-auto w-full block">
                    <table class="w-full text-sm text-left text-gray-500 min-w-[800px]">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-100 border-b border-gray-200 tracking-wider">
                            <tr>
                                <th scope="col" class="px-4 py-3 text-center">Semana #</th>
                                <th scope="col" class="px-4 py-3">Fecha Envío</th>
                                <th scope="col" class="px-4 py-3">Productor</th>
                                <th scope="col" class="px-4 py-3 text-center bg-blue-50 text-blue-800">Cajas Exportadas</th>
                                <th scope="col" class="px-4 py-3 text-center bg-blue-50 text-blue-800">Peso Total</th>
                                <th scope="col" class="px-4 py-3 text-center bg-purple-50 text-purple-800">Cajas Restituidas</th>
                                <th scope="col" class="px-4 py-3 text-center bg-amber-50 text-amber-800">Pendientes</th>
                                <th scope="col" class="px-4 py-3 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach($grupoExportacion as $exportacion)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="px-4 py-3 font-bold text-gray-900 text-center">{{ $exportacion->semana_exportacion }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700">{{ \Carbon\Carbon::parse($exportacion->fecha_exportacion)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $exportacion->productor->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-center bg-blue-50/10 font-semibold text-gray-900">{{ number_format($exportacion->cajas_exportacion) }}</td>
                                <td class="px-4 py-3 text-center bg-blue-50/10 text-gray-700 font-medium">
                                    <div>{{ number_format($exportacion->peso_exportacion, 3) }} kg</div>
                                    @if(auth()->user()->rol === 'administrador')
                                    <div class="text-[11px] font-bold text-red-600 mt-1 bg-red-50 rounded px-1.5 py-0.5 inline-block border border-red-100" title="Valor neto congelado en primer rechazo">
                                        <i class="fa-solid fa-lock text-[9px] mr-0.5"></i> Neto: {{ !is_null($exportacion->peso_neto_fijo) ? number_format($exportacion->peso_neto_fijo, 2) . ' kg' : 'Sin congelar' }}
                                    </div>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center bg-purple-50/10 font-medium text-purple-700">{{ number_format($exportacion->restituidas) }} uds</td>
                                <td class="px-4 py-3 text-center bg-amber-50/10 font-bold text-amber-700">{{ number_format($exportacion->pendientes) }} uds</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('recepcion.showExportacion', $exportacion->id) }}" class="text-emerald-600 hover:text-emerald-800 p-1" title="Ver Reporte">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @can('es-administrador')
                                        <form action="{{ route('recepcion.destroyExportacion', $exportacion->id) }}" method="POST" id="form-delete-exportacion-{{ $exportacion->id }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <!-- 💡 Cambiamos confirmarEliminacion por mostrarModalGenerico y ajustamos el ID del formulario -->
                                            <button type="button" onclick="mostrarModalGenerico('form-delete-exportacion-{{ $exportacion->id }}')" class="text-red-600 hover:text-red-800 p-1 cursor-pointer" title="Eliminar">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- RESUMEN DE CONDENSACIÓN INDEPENDIENTE POR TABLA DIARIA --}}
                @if(auth()->user()->rol === 'administrador')
                @php
                $sumaCajasExportadasDiarias = $grupoExportacion->sum('cajas_exportacion');

                $sumaPesosNetosFijosDiarios = (float) $grupoExportacion->sum(function($item) {
                return !is_null($item->peso_neto_fijo) ? (float)$item->peso_neto_fijo : (float)$item->peso_exportacion;
                });

                // Recuperamos el registro único de condensación de este día
                $condensacionDelDia = \App\Models\ControlCondensacion::where('fecha', $fechaKey)->first();
                $cantidadManualGuardada = $condensacionDelDia ? (float)$condensacionDelDia->agropark : 0.0;

                // 💡 AQUÍ LEEMOS EL NUEVO DATO DE LA BASE DE DATOS
                $cajasEnviadasGuardadas = $condensacionDelDia ? (int)$condensacionDelDia->cajas_enviadas : 0;

                $porcentajeCondensacionDiario = 0;

                if ($sumaPesosNetosFijosDiarios > 0 && $cantidadManualGuardada > 0) {
                $resultadoDivision = $cantidadManualGuardada / $sumaPesosNetosFijosDiarios;
                $porcentajeExacto = (1 - $resultadoDivision) * 100;
                $porcentajeCondensacionDiario = ceil($porcentajeExacto * 100) / 100;
                }
                @endphp
                <div class="bg-gray-900 text-white font-bold p-4 shadow border border-gray-700 border-t-0 rounded-b-xl">
                    {{-- Contenedor Principal: En móvil se vuelve columna con separación controlada --}}
                    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-4 text-sm w-full">

                        {{-- TÍTULO: Siempre arriba en móvil y ocupando su espacio --}}
                        <div class="uppercase tracking-wider text-xs font-extrabold text-amber-400 flex items-center gap-1 pb-2 border-b border-gray-800 xl:border-b-0 xl:pb-0">
                            <i class="fa-solid fa-chart-pie"></i> Resumen Condensación del Día
                        </div>

                        {{-- CONTENEDOR DE MÉTRICAS: Grid de 2 columnas en móvil, fila única en pantallas grandes --}}
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:flex md:flex-wrap md:items-center gap-4 md:gap-8 justify-between md:justify-end w-full xl:w-auto">

                            {{-- Total Cajas Exp. --}}
                            <div class="flex flex-col justify-center">
                                <span class="text-xs text-gray-400 block uppercase font-medium">Total Cajas Exp.</span>
                                <span class="text-base text-blue-400 font-extrabold whitespace-nowrap">
                                    <i class="fa-solid fa-box mr-1"></i> {{ number_format($sumaCajasExportadasDiarias) }} uds
                                </span>
                            </div>

                            {{-- Total Cajas Env. --}}
                            <div class="flex flex-col justify-center">
                                <span class="text-xs text-gray-400 block uppercase font-medium">Total Cajas Env.</span>
                                <span class="text-base text-cyan-400 font-extrabold whitespace-nowrap">
                                    <i class="fa-solid fa-truck mr-1"></i> {{ number_format($cajasEnviadasGuardadas) }} uds
                                </span>
                            </div>

                            {{-- Suma Pesos Netos Fijos --}}
                            <div class="flex flex-col justify-center">
                                <span class="text-xs text-gray-400 block uppercase font-medium">Pesos Netos Fijos</span>
                                <span class="text-base text-red-400 font-extrabold whitespace-nowrap">
                                    <i class="fa-solid fa-calculator mr-1"></i> {{ number_format($sumaPesosNetosFijosDiarios, 2) }} kg
                                </span>
                            </div>

                            {{-- Agropark --}}
                            <div class="flex flex-col justify-center">
                                <span class="text-xs text-gray-400 block uppercase font-medium">Agropark</span>
                                <span class="text-base text-blue-400 font-extrabold whitespace-nowrap">
                                    <i class="fa-solid fa-weight-hanging mr-1"></i> {{ $cantidadManualGuardada > 0 ? number_format($cantidadManualGuardada, 2) . ' kg' : 'Sin capturar' }}
                                </span>
                            </div>

                            {{-- Porcentaje de Condensación: Ocupa las 2 columnas en móviles muy chicos para resaltar --}}
                            <div class="col-span-2 sm:col-span-1 flex flex-col justify-center pt-2 sm:pt-0 border-t border-gray-800 sm:border-t-0">
                                <span class="text-xs text-amber-400 block uppercase font-medium">% Condensación</span>
                                <span class="text-lg text-emerald-400 font-black whitespace-nowrap">
                                    <i class="fa-solid fa-percent mr-1"></i> {{ number_format($porcentajeCondensacionDiario, 2) }} %
                                </span>
                            </div>

                        </div>
                    </div>
                </div>
                @endif
            </div>
            @empty
            <div class="bg-white shadow-sm rounded-xl border border-gray-200 p-6 text-center text-sm text-gray-400 font-medium">
                No hay embarques de exportación en esta semana.
            </div>
            @endforelse
        </div>
    </main>

    <!-- MODAL NACIONAL CORREGIDO -->
    <div id="modal-nacional" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-2xl my-auto flex flex-col overflow-hidden max-h-[95vh]">
            <div class="bg-amber-600 text-white px-6 py-4 flex justify-between items-center shrink-0">
                <h3 id="modal-nacional-titulo" class="text-lg font-bold flex items-center gap-2">
                    <i class="fa-solid fa-house-chimney"></i> Registrar Entrada Nacional
                </h3>
                <button type="button" onclick="cerrarModalNacional()" class="text-white/80 hover:text-white text-xl cursor-pointer transition">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <form action="{{ route('recepcion.storeNacional') }}" method="POST" id="form-modal-nacional" class="p-6 space-y-4 overflow-y-auto flex-grow max-h-[calc(95vh-120px)] scrollbar-thin scrollbar-thumb-gray-300">
                @csrf
                <input type="hidden" name="es_rechazo_operativo" id="es_rechazo_operativo" value="0">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Semana #</label>
                        <input type="number" name="semana_nacional" id="semana_nacional_input" readonly placeholder="Auto" class="w-full border border-gray-300 rounded-lg p-2 text-sm outline-none text-gray-500 bg-gray-100 font-bold cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha de Recepción</label>
                        <input type="text" name="fecha_nacional" id="fecha_nacional_input" placeholder="Seleccione la fecha..." required class="w-full border border-gray-300 rounded-lg p-2 text-sm outline-none text-gray-800 bg-gray-50 focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Productor / Dueño del Sector</label>
                    <select name="productor_id" id="productor_select" onchange="filtrarDatosPorOperador()" required class="w-full border border-gray-300 rounded-lg p-2.5 text-sm outline-none text-gray-800 bg-white focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                        <option value="" disabled selected>-- Selecciona un productor --</option>
                        @if(auth()->user()->rol === 'administrador')
                        <option value="{{ auth()->user()->id }}" data-sectores="{{ is_string(auth()->user()->sectores) ? auth()->user()->sectores : json_encode(auth()->user()->sectores) }}">
                            {{ auth()->user()->name }} (Tú - Administrador)
                        </option>
                        @endif
                        @foreach($productores as $productor)
                        @if($productor->id !== auth()->id())
                        <option value="{{ $productor->id }}" data-sectores="{{ is_string($productor->sectores) ? $productor->sectores : json_encode($productor->sectores) }}">
                            {{ $productor->name ?? $productor->nombre }}
                        </option>
                        @endif
                        @endforeach
                    </select>
                </div>

                <div id="contenedor-embarque-origen" class="hidden mt-3 drop-shadow-sm">
                    <label class="block text-xs font-bold text-blue-700 uppercase mb-1">Embarque de Exportación de Origen (Rastreo)</label>
                    <select name="recepcion_exportacion_id" id="recepcion_exportacion_select" class="w-full border border-blue-300 rounded-lg p-2.5 text-sm outline-none text-gray-800 bg-blue-50/40 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-medium">
                        <option value="" selected>-- Selecciona el embarque origen --</option>
                        @foreach($embarquesExportacion as $embarque)
                        <!-- 💡 SOLO SE AGREGA EL ATRIBUTO data-fecha AQUÍ ABAJO -->
                        <option class="opcion-embarque hidden" value="{{ $embarque->id }}" data-operador="{{ $embarque->productor_id }}" data-fecha="{{ $embarque->fecha_exportacion }}">
                            Fecha: {{ \Carbon\Carbon::parse($embarque->fecha_exportacion)->format('d/m/Y') }} — Cajas Env: {{ $embarque->cajas_exportacion }} (Semana #{{ $embarque->semana_exportacion }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <div id="contenedor-sector-registro" class="hidden mt-3">
                    <label class="block text-xs font-bold text-amber-700 uppercase mb-1">Sector Específico del Registro</label>
                    <select name="sector_registro" id="sector_registro_select" class="w-full border border-amber-300 rounded-lg p-2.5 text-sm outline-none text-gray-800 bg-amber-50/50 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 font-medium">
                    </select>
                </div>

                <hr class="border-gray-200 my-2">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="space-y-3 bg-emerald-50/40 p-4 rounded-xl border border-emerald-100">
                        <h4 class="text-xs font-bold text-emerald-800 uppercase tracking-wider flex items-center gap-1">
                            <i class="fa-solid fa-basket-shopping"></i> Comercializar
                        </h4>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Cajas Comerciales</label>
                            <input type="number" name="cajas_comercializar" id="cajas_com" placeholder="0" min="0" oninput="calcularTotalesNacional()" {{ auth()->user()->rol === 'usuario_rechazo' ? 'readonly' : '' }} class="w-full border border-gray-300 rounded-lg p-2 text-sm outline-none font-semibold text-gray-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 {{ auth()->user()->rol === 'usuario_rechazo' ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white' }}">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Peso Comercial (Kg)</label>
                            <input type="number" name="peso_comercializar" id="peso_com" placeholder="0.00" step="0.01" min="0" oninput="calcularTotalesNacional()" {{ auth()->user()->rol === 'usuario_rechazo' ? 'readonly' : '' }} class="w-full border border-gray-300 rounded-lg p-2 text-sm outline-none text-gray-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 {{ auth()->user()->rol === 'usuario_rechazo' ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white' }}">
                        </div>
                    </div>

                    <div class="space-y-3 bg-red-50/40 p-4 rounded-xl border border-red-100">
                        <h4 class="text-xs font-bold text-red-800 uppercase tracking-wider flex items-center gap-1">
                            <i class="fa-solid fa-ban"></i> Procesado (Rechazo)
                        </h4>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Cajas de Rechazo</label>
                            <input type="number" name="cajas_rechazo_procesado" id="cajas_rec" placeholder="0" min="0" oninput="calcularTotalesNacional()" {{ auth()->user()->rol === 'usuario_comercial' ? 'readonly' : '' }} class="w-full border border-gray-300 rounded-lg p-2 text-sm outline-none font-semibold text-gray-800 focus:border-red-500 focus:ring-1 focus:ring-red-500 {{ auth()->user()->rol === 'usuario_comercial' ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white' }}">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Peso Rechazo (Kg)</label>
                            <input type="number" name="peso_rechazo_procesado" id="peso_rec" placeholder="0.00" step="0.01" min="0" oninput="calcularTotalesNacional()" {{ auth()->user()->rol === 'usuario_comercial' ? 'readonly' : '' }} class="w-full border border-gray-300 rounded-lg p-2 text-sm outline-none text-gray-800 focus:border-red-500 focus:ring-1 focus:ring-red-500 {{ auth()->user()->rol === 'usuario_comercial' ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white' }}">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 sticky bottom-0 bg-white z-10">
                    <button type="button" onclick="cerrarModalNacional()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-lg transition shadow cursor-pointer">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EXPORTACIÓN LIMPIO -->
    <div id="modal-exportacion" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-xl border border-gray-200 w-full max-w-2xl mx-4 overflow-hidden">
            <div class="bg-blue-600 text-white px-6 py-4 flex justify-between items-center">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <i class="fa-solid fa-plane-departure"></i> Registrar Exportación
                </h3>
                <button type="button" onclick="cerrarModalExportacion()" class="text-white/80 hover:text-white text-xl cursor-pointer">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <form action="{{ route('recepcion.storeExportacion') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Semana #</label>
                        <input type="number" name="semana_exportacion" id="semana_exportacion_input" readonly placeholder="Auto" class="w-full border border-gray-300 rounded-lg p-2 text-sm outline-none text-gray-500 bg-gray-100 font-bold cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Fecha de Envío</label>
                        <input type="text" name="fecha_exportacion" id="fecha_exportacion_input" placeholder="Seleccione la fecha..." required class="w-full border border-gray-300 rounded-lg p-2 text-sm outline-none text-gray-800 bg-gray-50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Productor / Dueño del Sector</label>
                    <select name="productor_id" id="productor_exportacion_select" onchange="cargarSectoresDelProductorExportacion()" required class="w-full border border-gray-300 rounded-lg p-2.5 text-sm outline-none text-gray-800 bg-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                        <option value="" disabled selected>-- Selecciona un productor --</option>
                        @if(auth()->user()->rol === 'administrador')
                        <option value="{{ auth()->user()->id }}" data-sectores="{{ is_string(auth()->user()->sectores) ? auth()->user()->sectores : json_encode(auth()->user()->sectores) }}">
                            {{ auth()->user()->name }} (Tú - Administrador)
                        </option>
                        @endif
                        @if(isset($productores) && count($productores) > 0)
                        @foreach($productores as $productor)
                        @if($productor->id !== auth()->id())
                        <option value="{{ $productor->id }}" data-sectores="{{ is_string($productor->sectores) ? $productor->sectores : json_encode($productor->sectores) }}">
                            {{ $productor->name ?? $productor->nombre ?? 'Usuario sin nombre' }}
                        </option>
                        @endif
                        @endforeach
                        @else
                        <option value="" disabled>No se encontraron productores cargados</option>
                        @endif
                    </select>
                </div>

                <div id="contenedor-sector-exportacion" class="hidden mt-3 animate-fade-in">
                    <label class="block text-xs font-bold text-blue-700 uppercase mb-1">Sector Específico del Registro</label>
                    <select name="sector_registro" id="sector_exportacion_select" required class="w-full border border-blue-300 rounded-lg p-2.5 text-sm outline-none text-gray-800 bg-blue-50/50 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-medium">
                    </select>
                </div>

                <hr class="border-gray-200 my-2">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-blue-50/30 p-4 rounded-xl border border-blue-100">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Cajas Exportadas</label>
                        <input type="number" name="cajas_exportadas" id="cajas_exp" placeholder="0" min="0" oninput="calcularSaldosExportacion()" required class="w-full bg-white border border-gray-300 rounded-lg p-2 text-sm outline-none font-semibold text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Peso de Exportación (Kg)</label>
                        <input type="number" name="peso_exportacion" placeholder="0.000" step="any" min="0" required class="w-full bg-white border border-gray-300 rounded-lg p-2 text-sm outline-none text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <input type="hidden" name="cajas_pendientes" id="cajas_pen_input" value="0">

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="cerrarModalExportacion()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition shadow cursor-pointer">Guardar Embarque</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL RESTITUIDAS -->
    <div id="modalRestituidas" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl border border-gray-200 w-full max-w-md overflow-hidden transform transition-all">
            <div class="bg-purple-700 p-4 text-white flex justify-between items-center">
                <h3 class="font-bold text-sm sm:text-base uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-boxes-packing"></i> Registrar Cajas Restituidas
                </h3>
                <button onclick="cerrarModalRestituidas()" class="text-white/80 hover:text-white cursor-pointer outline-none">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <form action="{{ route('recepcion.storeRestituidas') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <div>
                    <label for="recepcion_exportacion_id" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Seleccionar Embarque Pendiente:</label>
                    <select name="recepcion_exportacion_id" id="recepcion_exportacion_id" required class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 p-2.5 outline-none cursor-pointer">
                        <option value="">-- Seleccione Fecha / Operador / Sector --</option>
                        @foreach($embarquesPendientesDeCajas as $emb)
                        <option value="{{ $emb->id }}" data-fecha="{{ \Carbon\Carbon::parse($emb->fecha_exportacion)->format('Y-m-d') }}">
                            {{ \Carbon\Carbon::parse($emb->fecha_exportacion)->format('d/m/Y') }} - {{ $emb->productor->name ?? 'N/A' }} ({{ $emb->sector_registro }}) - [Pendientes: {{ number_format($emb->pendientes) }} uds]
                        </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="cajas_a_restituir" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Cantidad de Cajas Devueltas:</label>
                    <input type="number" name="cajas_a_restituir" id="cajas_a_restituir" min="1" required placeholder="Ej. 50" class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg focus:ring-purple-500 focus:border-purple-500 p-2.5 outline-none">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="cerrarModalRestituidas()" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold px-4 py-2 rounded-lg text-xs sm:text-sm transition cursor-pointer">Cancelar</button>
                    <button type="submit" class="bg-purple-700 hover:bg-purple-800 text-white font-bold px-4 py-2 rounded-lg text-xs sm:text-sm transition shadow cursor-pointer">Guardar Devolución</button>
                </div>
            </form>
        </div>
    </div>

    {{-- NUEVO MODAL: CAPTURA DE CAJAS ENVIADAS EN CONDENSACIÓN --}}
    <div id="modalEnviadas" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl border border-gray-200 w-full max-w-md overflow-hidden transform transition-all">
            <div class="bg-cyan-700 p-4 text-white flex justify-between items-center">
                <h3 class="font-bold text-sm sm:text-base uppercase tracking-wider flex items-center gap-2">
                    <i class="fa-solid fa-truck-ramp-box"></i> Registrar Cajas Enviadas del Día
                </h3>
                <button onclick="cerrarModalEnviadas()" class="text-white/80 hover:text-white cursor-pointer outline-none">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            {{-- Apuntamos a la ruta global de condensación --}}
            <form action="{{ route('condensacion.guardar') }}" method="POST" class="p-6 space-y-4">
                @csrf

                {{-- Campos ocultos para saber qué día y semana estamos afectando --}}
                <input type="hidden" name="semana" value="{{ $semanaActual ?? ($recepcionesExportaciones->first()->semana_exportacion ?? 1) }}">
                <input type="hidden" name="fecha" id="enviadas_fecha_input" required>

                <div>
                    <label for="cajas_enviadas" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-1">Cantidad de Cajas Enviadas:</label>
                    <input type="number" name="cajas_enviadas" id="cajas_enviadas_input" min="1" required placeholder="Ej. 120" class="w-full bg-gray-50 border border-gray-300 text-gray-800 text-sm rounded-lg focus:ring-cyan-500 focus:border-cyan-500 p-2.5 outline-none font-medium">
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" onclick="cerrarModalEnviadas()" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-bold px-4 py-2 rounded-lg text-xs sm:text-sm transition cursor-pointer">
                        Cancelar
                    </button>
                    <button type="submit" class="bg-cyan-700 hover:bg-cyan-800 text-white font-bold px-4 py-2 rounded-lg text-xs sm:text-sm transition shadow cursor-pointer">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Cajas
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if(auth()->user()->rol === 'administrador')
    <div id="modalCondensacion" class="fixed inset-0 z-50 hidden overflow-y-auto bg-gray-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-xl border border-gray-200 w-full max-w-md transform transition-all overflow-hidden flex flex-col">
            <div class="p-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                <h3 class="text-base font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-percentage text-red-600"></i> Captura de Condensación
                </h3>
                <button onclick="cerrarModalCondensacion()" class="text-gray-400 hover:text-gray-600 text-lg cursor-pointer">&times;</button>
            </div>

            <form action="{{ route('condensacion.guardar') }}" method="POST" class="p-6 space-y-4">
                @csrf
                <input type="hidden" name="semana" value="{{ $semanaActual ?? ($recepcionesExportaciones->first()->semana_exportacion ?? 1) }}">
                <input type="hidden" name="fecha" id="condensacion_fecha_input" required>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cantidad Manual (Agropark Kg)</label>
                    <div class="relative">
                        <input type="number" step="any" name="agropark" id="condensacion_agropark_input" required min="0.01" placeholder="Ej. 45000.00" class="w-full bg-gray-50 border border-gray-300 rounded-lg pl-3 pr-10 py-2 text-sm focus:ring-2 focus:ring-red-500 focus:outline-none font-medium text-gray-900">
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <span class="text-xs text-gray-400 font-bold">kg</span>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end gap-2">
                    <button type="button" onclick="cerrarModalCondensacion()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs font-bold transition cursor-pointer">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg text-xs font-bold transition shadow flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-floppy-disk"></i> Guardar Fijo
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif

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

    <link class="hidden" id="flatpickr-css" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        let modoCaptura = 'recepcion';

        document.addEventListener("DOMContentLoaded", function() {
            flatpickr("#fecha_nacional_input", {
                locale: "es",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                onChange: function(selectedDates, dateStr, instance) {
                    calcularSemanaDesdeFecha('nacional');
                }
            });

            flatpickr("#fecha_exportacion_input", {
                locale: "es",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                onChange: function(selectedDates, dateStr, instance) {
                    calcularSemanaDesdeFecha('exportacion');
                }
            });

            flatpickr("#semana_picker", {
                locale: "es",
                disableMobile: "true",
                dateFormat: "Y-m-d",
                onReady: function(selectedDates, dateStr, instance) {
                    const valActual = document.getElementById('semana_final_input').value;
                    if (valActual) {
                        const partes = valActual.split('-W');
                        if (partes.length === 2) {
                            const año = parseInt(partes[0]);
                            const sem = parseInt(partes[1]);
                            const d = new Date(año, 0, 4);
                            d.setDate(d.getDate() + (sem - 1) * 7 - (d.getDay() + 6) % 7 + 3);
                            instance.setDate(d, false);
                            document.getElementById('semana_picker').value = `Semana ${sem} / ${año}`;
                        }
                    }
                },
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 0) return;
                    const fecha = selectedDates[0];
                    const target = new Date(fecha.valueOf());
                    const dayNr = (fecha.getDay() + 6) % 7;
                    target.setDate(target.getDate() - dayNr + 3);
                    const firstThursday = target.valueOf();
                    target.setMonth(0, 1);
                    if (target.getDay() !== 4) {
                        target.setMonth(0, 1 + ((4 - target.getDay()) + 7) % 7);
                    }
                    const numeroSemana = 1 + Math.ceil((firstThursday - target) / 604800000);
                    const añoSemana = new Date(firstThursday).getFullYear();
                    const formattedWeek = añoSemana + "-W" + String(numeroSemana).padStart(2, '0');
                    document.getElementById('semana_final_input').value = formattedWeek;
                    document.getElementById('formSemana').submit();
                }
            });
        });

        function cambiarPestaña(tab) {
            const contenidoNacional = document.getElementById('contenido-nacional');
            const contenidoExportacion = document.getElementById('contenido-exportacion');
            const btnNacional = document.getElementById('btn-tab-nacional');
            const btnExportacion = document.getElementById('btn-tab-exportacion');

            if (tab === 'nacional') {
                contenidoNacional.classList.remove('hidden');
                contenidoNacional.classList.add('block');
                contenidoExportacion.classList.remove('block');
                contenidoExportacion.classList.add('hidden');
                btnNacional.className = "w-full sm:w-auto px-5 py-2.5 font-bold text-sm rounded-lg transition flex items-center justify-center gap-2 cursor-pointer bg-amber-600 text-white shadow-md";
                btnExportacion.className = "w-full sm:w-auto px-5 py-2.5 font-bold text-sm rounded-lg transition flex items-center justify-center gap-2 cursor-pointer bg-gray-100 text-gray-600 hover:bg-gray-200";
            } else {
                contenidoNacional.classList.remove('block');
                contenidoNacional.classList.add('hidden');
                contenidoExportacion.classList.remove('hidden');
                contenidoExportacion.classList.add('block');
                btnNacional.className = "w-full sm:w-auto px-5 py-2.5 font-bold text-sm rounded-lg transition flex items-center justify-center gap-2 cursor-pointer bg-gray-100 text-gray-600 hover:bg-gray-200";
                btnExportacion.className = "w-full sm:w-auto px-5 py-2.5 font-bold text-sm rounded-lg transition flex items-center justify-center gap-2 cursor-pointer bg-blue-600 text-white shadow-md";
            }
        }

        function abrirModalNacional(tipo = 'recepcion') {
            const modal = document.getElementById('modal-nacional');
            const titulo = document.getElementById('modal-nacional-titulo');
            const esRechazoInput = document.getElementById('es_rechazo_operativo');
            const contenedorEmbarque = document.getElementById('contenedor-embarque-origen');
            const selectEmbarque = document.getElementById('recepcion_exportacion_select');

            if (!modal) return;
            modoCaptura = tipo;
            calcularTotalesNacional();

            if (tipo === 'rechazo') {
                if (titulo) titulo.innerHTML = '<i class="fa-solid fa-ban text-red-500"></i> Capturar Kg de Rechazo de Exportación';
                if (esRechazoInput) esRechazoInput.value = "1";
                if (selectEmbarque) selectEmbarque.required = true;
                if (contenedorEmbarque) contenedorEmbarque.classList.remove('hidden');
            } else {
                if (titulo) titulo.innerHTML = '<i class="fa-solid fa-house-chimney text-emerald-500"></i> Registrar Entrada Nacional';
                if (esRechazoInput) esRechazoInput.value = "0";
                if (selectEmbarque) {
                    selectEmbarque.value = "";
                    selectEmbarque.required = false;
                }
                if (contenedorEmbarque) contenedorEmbarque.classList.add('hidden');
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function cerrarModalNacional() {
            const modal = document.getElementById('modal-nacional');
            if (!modal) return;
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        function calcularTotalesNacional() {
            const inputCajasCom = document.getElementById('cajas_com');
            const inputPesoCom = document.getElementById('peso_com');
            const inputCajasRec = document.getElementById('cajas_rec');
            const inputPesoRec = document.getElementById('peso_rec');

            const cajasCom = inputCajasCom ? (parseInt(inputCajasCom.value) || 0) : 0;
            const pesoCom = inputPesoCom ? (parseFloat(inputPesoCom.value) || 0) : 0;
            const cajasRec = inputCajasRec ? (parseInt(inputCajasRec.value) || 0) : 0;
            const pesoRec = inputPesoRec ? (parseFloat(inputPesoRec.value) || 0) : 0;

            const totalCajasInput = document.getElementById('total_cajas_input');
            const totalKgInput = document.getElementById('total_kg_input');

            if (totalCajasInput) totalCajasInput.value = cajasCom + cajasRec;
            if (totalKgInput) totalKgInput.value = (pesoCom + pesoRec).toFixed(2);
        }

        function abrirModalExportacion() {
            const modal = document.getElementById('modal-exportacion');
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function cerrarModalExportacion() {
            const modal = document.getElementById('modal-exportacion');
            if (!modal) return;
            modal.classList.remove('flex');
            modal.classList.add('hidden');
        }

        function calcularSaldosExportacion() {
            const inputCajasExp = document.getElementById('cajas_exp');
            const inputCajasPen = document.getElementById('cajas_pen_input');
            const cajasExp = inputCajasExp ? (parseInt(inputCajasExp.value) || 0) : 0;
            if (inputCajasPen) {
                inputCajasPen.value = cajasExp >= 0 ? cajasExp : 0;
            }
        }

        function calcularSemanaDesdeFecha(tipo) {
            const fechaInput = document.getElementById(`fecha_${tipo}_input`);
            const semanaInput = document.getElementById(`semana_${tipo}_input`);
            if (!fechaInput || !fechaInput.value || !semanaInput) return;

            const fechaSeleccionada = new Date(fechaInput.value + 'T00:00:00');
            const target = new Date(fechaSeleccionada.valueOf());
            const dayNr = (fechaSeleccionada.getDay() + 6) % 7;
            target.setDate(target.getDate() - dayNr + 3);
            const firstThursday = target.valueOf();
            target.setMonth(0, 1);
            if (target.getDay() !== 4) {
                target.setMonth(0, 1 + ((4 - target.getDay()) + 7) % 7);
            }
            const numeroSemana = 1 + Math.ceil((firstThursday - target) / 604800000);
            semanaInput.value = numeroSemana;
        }

        function filtrarDatosPorOperador() {
            const selectProductor = document.getElementById('productor_select');
            const opcionesEmbarque = document.querySelectorAll('.opcion-embarque');
            const selectEmbarque = document.getElementById('recepcion_exportacion_select');
            const inputFechaNacional = document.getElementById('fecha_nacional_input');

            if (!selectProductor) return;
            const idOperador = selectProductor.value;

            cargarSectoresDelProductor();

            const divEmbarque = document.getElementById('contenedor-embarque-origen');
            if (modoCaptura === 'rechazo' && idOperador) {
                if (selectEmbarque) selectEmbarque.value = "";

                // Obtener el año y mes seleccionado en el input de fecha (Formato: YYYY-MM)
                let mesAñoFiltro = "";
                if (inputFechaNacional && inputFechaNacional.value) {
                    mesAñoFiltro = inputFechaNacional.value.substring(0, 7);
                }

                opcionesEmbarque.forEach(opcion => {
                    const idOpEmbarque = opcion.getAttribute('data-operador');
                    const fechaEmbarque = opcion.getAttribute('data-fecha'); // Formato: YYYY-MM-DD
                    const mesAñoEmbarque = fechaEmbarque ? fechaEmbarque.substring(0, 7) : "";

                    // Coincide con el operador Y con el mes de la fecha que se está capturando
                    if (idOpEmbarque == idOperador && (mesAñoFiltro === "" || mesAñoEmbarque === mesAñoFiltro)) {
                        opcion.classList.remove('hidden');
                    } else {
                        opcion.classList.add('hidden');
                    }
                });
                if (divEmbarque) divEmbarque.classList.remove('hidden');
            } else {
                if (divEmbarque) divEmbarque.classList.add('hidden');
            }
        }

        function cargarSectoresDelProductor() {
            const selectProductor = document.getElementById('productor_select');
            const selectSector = document.getElementById('sector_registro_select');
            const contenedorSector = document.getElementById('contenedor-sector-registro');

            if (!selectProductor || !selectSector || !contenedorSector) return;

            const opcionSeleccionada = selectProductor.options[selectProductor.selectedIndex];
            if (!opcionSeleccionada || opcionSeleccionada.value === "") {
                contenedorSector.classList.add('hidden');
                return;
            }

            let sectoresRaw = opcionSeleccionada.getAttribute('data-sectores');
            let sectores = [];

            try {
                sectores = JSON.parse(sectoresRaw);
            } catch (e) {
                if (sectoresRaw) {
                    sectores = sectoresRaw.split(',').map(s => s.trim().replace(/[\[\]"']/g, ''));
                }
            }

            selectSector.innerHTML = '<option value="" disabled selected>-- Selecciona el sector de origen --</option>';

            if (sectores && sectores.length > 0) {
                sectores.forEach(sector => {
                    if (sector) {
                        const option = document.createElement('option');
                        option.value = sector;
                        option.textContent = sector;
                        selectSector.appendChild(option);
                    }
                });
                contenedorSector.classList.remove('hidden');
            } else {
                const option = document.createElement('option');
                option.value = "General";
                option.textContent = "General (Sin sectores específicos)";
                selectSector.appendChild(option);
                contenedorSector.classList.remove('hidden');
            }
        }

        function cargarSectoresDelProductorExportacion() {
            const selectProductor = document.getElementById('productor_exportacion_select');
            const selectSector = document.getElementById('sector_exportacion_select');
            const contenedorSector = document.getElementById('contenedor-sector-exportacion');

            if (!selectProductor || !selectSector || !contenedorSector) return;

            const opcionSeleccionada = selectProductor.options[selectProductor.selectedIndex];
            if (!opcionSeleccionada || opcionSeleccionada.value === "") {
                contenedorSector.classList.add('hidden');
                return;
            }

            let sectoresRaw = opcionSeleccionada.getAttribute('data-sectores');
            let sectores = [];

            try {
                sectores = JSON.parse(sectoresRaw);
            } catch (e) {
                if (sectoresRaw) {
                    sectores = sectoresRaw.split(',').map(s => s.trim().replace(/[\[\]"']/g, ''));
                }
            }

            selectSector.innerHTML = '<option value="" disabled selected>-- Selecciona el sector de origen --</option>';

            if (sectores && sectores.length > 0) {
                sectores.forEach(sector => {
                    if (sector) {
                        const option = document.createElement('option');
                        option.value = sector;
                        option.textContent = sector;
                        selectSector.appendChild(option);
                    }
                });
                contenedorSector.classList.remove('hidden');
            } else {
                const option = document.createElement('option');
                option.value = "General";
                option.textContent = "General (Sin sectores específicos)";
                selectSector.appendChild(option);
                contenedorSector.classList.remove('hidden');
            }
        }

        function abrirModalRestituidas() {
            document.getElementById('modalRestituidas').classList.remove('hidden');
            document.getElementById('modalRestituidas').classList.add('flex');
        }

        function cerrarModalRestituidas() {
            document.getElementById('modalRestituidas').classList.remove('flex');
            document.getElementById('modalRestituidas').classList.add('hidden');
        }

        function abrirModalEnviadasPorFecha(fecha) {
            const modal = document.getElementById('modalEnviadas');
            const inputFecha = document.getElementById('enviadas_fecha_input');
            const inputCajas = document.getElementById('cajas_enviadas_input');

            if (!modal) return;

            if (inputFecha && fecha) {
                inputFecha.value = fecha;
            }

            // Buscamos la tabla del día para ver si ya hay una cantidad renderizada y precargarla
            const botonDisparador = document.querySelector(`button[onclick="abrirModalEnviadasPorFecha('${fecha}')"]`);
            if (botonDisparador) {
                const contenedorTabla = botonDisparador.closest('.bg-white');
                if (contenedorTabla && inputCajas) {
                    const celdaCajasTexto = contenedorTabla.querySelector('.text-cyan-400') ? contenedorTabla.querySelector('.text-cyan-400').innerText : '';
                    let valorActual = parseInt(celdaCajasTexto.replace(/[^0-9.-]+/g, ""));
                    inputCajas.value = (!isNaN(valorActual) && valorActual > 0) ? valorActual : '';
                }
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function cerrarModalEnviadas() {
            const modal = document.getElementById('modalEnviadas');
            const inputFecha = document.getElementById('enviadas_fecha_input');
            if (modal) {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }
            if (inputFecha) {
                inputFecha.value = "";
            }
        }


        function abrirModalCondensacion(fecha) {
            const modal = document.getElementById('modalCondensacion');
            const inputFecha = document.getElementById('condensacion_fecha_input');
            const inputAgropark = document.getElementById('condensacion_agropark_input');

            if (!modal) return;

            if (inputFecha && fecha) {
                inputFecha.value = fecha;
            }

            const botonDisparador = document.querySelector(`button[onclick="abrirModalCondensacion('${fecha}')"]`);
            if (!botonDisparador) return;

            const contenedorTabla = botonDisparador.closest('.bg-white');
            if (!contenedorTabla) return;

            // 💡 SOLUCIÓN: Forzamos a que siempre se limpie el campo al abrir el modal
            if (inputAgropark) {
                inputAgropark.value = ''; // <--- Cambia esto para que inicie vacío siempre
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            if (inputAgropark) {
                const textoSuma = contenedorTabla.querySelector('.text-red-400') ? contenedorTabla.querySelector('.text-red-400').innerText : '0';
                let sumaPesosFijos = parseFloat(textoSuma.replace(/[^0-9.-]+/g, ""));

                const nuevoInput = inputAgropark.cloneNode(true);
                inputAgropark.parentNode.replaceChild(nuevoInput, inputAgropark);

                nuevoInput.addEventListener('input', function() {
                    let cantidadManual = parseFloat(this.value);
                    let celdaPorcentaje = contenedorTabla.querySelector('.text-emerald-400');
                    let celdaAgropark = contenedorTabla.querySelector('.text-blue-400');

                    if (!isNaN(cantidadManual) && cantidadManual > 0 && sumaPesosFijos > 0) {
                        let division = cantidadManual / sumaPesosFijos;
                        let porcentaje = (1 - division) * 100;

                        if (celdaPorcentaje) celdaPorcentaje.innerText = porcentaje.toFixed(2) + ' %';
                        if (celdaAgropark) {
                            let formateado = cantidadManual.toLocaleString('en-US', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            }) + ' kg';
                            celdaAgropark.innerHTML = `<i class="fa-solid fa-pen-to-square text-xs mr-1"></i> ${formateado}`;
                        }
                    }
                });
            }
        }

        function cerrarModalCondensacion() {
            const modal = document.getElementById('modalCondensacion');
            const inputFecha = document.getElementById('condensacion_fecha_input');
            if (modal) {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
            }
            if (inputFecha) {
                inputFecha.value = "";
            }
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

        function abrirModalRestituidasPorFecha(fechaSeleccionada) {
            const modal = document.getElementById('modalRestituidas');
            const selectEmbarques = document.getElementById('recepcion_exportacion_id');

            if (!modal || !selectEmbarques) return;

            selectEmbarques.value = "";
            const opciones = selectEmbarques.querySelectorAll('option');

            opciones.forEach(opcion => {
                if (opcion.value === "") {
                    opcion.classList.remove('hidden');
                    return;
                }
                const fechaOpcion = opcion.getAttribute('data-fecha');
                if (fechaOpcion === fechaSeleccionada) {
                    opcion.classList.remove('hidden');
                } else {
                    opcion.classList.add('hidden');
                }
            });

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    </script>
</body>

</html>