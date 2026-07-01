<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recepción - Sistema Control</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

    <!-- NAV TOTALMENTE RESPONSIVO -->
    <nav class="bg-emerald-600 text-white shadow-md">
        <div class="max-w-[95%] mx-auto px-4 flex flex-col sm:flex-row items-center justify-between py-3 sm:h-16 gap-3">
            <div class="flex items-center self-start sm:self-auto">
                <i class="fa-solid fa-leaf text-2xl mr-2"></i>
                <span class="font-bold text-xl tracking-wider">SISTEMA CONTROL</span>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 w-full sm:w-auto text-xs sm:text-sm">
                @if(auth()->user()->rol === 'administrador')
                <a href="{{ route('dashboard') }}" class="bg-emerald-700 hover:bg-emerald-800 text-white font-bold px-3 py-2 rounded-lg transition flex items-center gap-1.5 shadow-sm cursor-pointer">
                    <i class="fa-solid fa-house"></i> <span class="hidden xs:inline">Panel</span>
                </a>
                @endif

                <span class="bg-emerald-700/50 px-3 py-2 rounded-lg border border-emerald-500/30 flex items-center gap-1.5 font-medium whitespace-nowrap">
                    <i class="fa-solid fa-user text-xs"></i> {{ auth()->user()->name }}
                </span>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold px-3 py-2 rounded-lg transition flex items-center gap-1.5 shadow-sm cursor-pointer border border-red-500/20 whitespace-nowrap">
                        <i class="fa-solid fa-right-from-bracket"></i> Salir
                    </button>
                </form>
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

           <form method="GET" action="{{ route('recepcion.index') }}" class="w-full sm:w-auto sm:max-w-xs flex-shrink-0">
    <label for="semana" class="block text-xs font-bold text-gray-600 uppercase mb-1 tracking-wider">Filtrar por Semana:</label>
    <div class="relative flex items-center gap-1.5">
<input type="week" name="semana" id="semana" value="{{ $semanaActiva }}" onchange="this.form.submit()" class="w-full bg-white border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 p-2 cursor-pointer shadow-sm outline-none">
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
        <div class="p-4 sm:p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gray-50/50">
            <h3 class="text-base sm:text-lg font-bold text-gray-800">Recepción Nacional</h3>

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
                    @forelse($recepcionesNacionales as $nacional)
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
                                <form action="{{ route('recepcion.destroyNacional', $nacional->id) }}" method="POST" onsubmit="return confirm('¿Seguro de eliminar?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 p-1 transition cursor-pointer">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-6 py-10 text-center text-sm text-gray-400 font-medium">No hay registros nacionales.</td>
                    </tr>
                    @endforelse
                </tbody>

                @if($recepcionesNacionales->count() > 0)
                <tfoot class="border-t-2 border-gray-300 bg-gray-100 font-bold text-gray-900">
                    <tr>
                        <td class="px-4 py-3 text-center text-xs uppercase text-gray-500 tracking-wider" colspan="3">
                            Nacional Procesado
                        </td>
                        <td class="px-3 py-3 text-center bg-emerald-100/60 text-gray-950 font-extrabold">
                            {{ number_format($recepcionesNacionales->sum('cajas_comerciales_vigentes')) }}
                        </td>
                        <td class="px-3 py-3 text-center bg-emerald-100/60 text-gray-950 font-extrabold">
                            {{ number_format($recepcionesNacionales->sum('peso_comercial_vigente'), 2) }} kg
                        </td>
                        <td class="px-3 py-3 text-center bg-red-100/60 text-red-950 font-extrabold">
                            {{ number_format($recepcionesNacionales->sum('cajas_rechazo_procesado')) }}
                        </td>
                        <td class="px-3 py-3 text-center bg-red-100/60 text-red-950 font-extrabold">
                            {{ number_format($recepcionesNacionales->sum('peso_rechazo_procesado'), 2) }} kg
                        </td>
                        <td class="px-3 py-3 text-center bg-gray-200 text-gray-950 font-black">
                            {{ number_format($recepcionesNacionales->sum('total_cajas')) }}
                        </td>
                        <td class="px-3 py-3 text-center bg-gray-200 text-amber-900 font-black">
                            {{ number_format($recepcionesNacionales->sum('total_kg'), 2) }} kg
                        </td>
                        <td class="bg-gray-100"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
        <!-- CONTENIDO EXPORTACIÓN -->
        <div id="contenido-exportacion" class="hidden space-y-6">
            <div class="bg-white shadow-sm rounded-xl border border-gray-200">
                <div class="p-4 sm:p-6 border-b border-gray-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gray-50/50">
                    <div>
                        <h3 class="text-base sm:text-lg font-bold text-gray-800">Bitácora de Embarques de Exportación</h3>
                    </div>
                    @if(auth()->user()->rol === 'administrador' || auth()->user()->rol === 'usuario_comercial')
                    <button onclick="abrirModalExportacion()" class="w-full sm:w-auto justify-center bg-blue-600 hover:bg-blue-700 text-white font-bold px-4 py-2 rounded-lg text-xs sm:text-sm transition shadow flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-plus"></i> Registrar Exportación
                    </button>
                    @endif
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
                            @forelse($recepcionesExportaciones as $exportacion)
                            <tr class="hover:bg-gray-50/70 transition">
                                <td class="px-4 py-3 font-bold text-gray-900 text-center">{{ $exportacion->semana_exportacion }}</td>
                                <td class="px-4 py-3 whitespace-nowrap text-gray-700">{{ \Carbon\Carbon::parse($exportacion->fecha_exportacion)->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $exportacion->productor->name ?? 'N/A' }}</td>
                                <td class="px-4 py-3 text-center bg-blue-50/10 font-semibold text-gray-900">{{ number_format($exportacion->cajas_exportacion) }}</td>
                                <td class="px-4 py-3 text-center bg-blue-50/10 text-gray-700 font-medium">{{ number_format($exportacion->peso_exportacion, 2) }}</td>
                                <td class="px-4 py-3 text-center bg-purple-50/10 font-medium text-purple-700">-</td>
                                <td class="px-4 py-3 text-center bg-amber-50/10 font-bold text-amber-700">-</td>
                                <td class="px-4 py-3 text-center whitespace-nowrap">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="{{ route('recepcion.showExportacion', $exportacion->id) }}" class="text-emerald-600 hover:text-emerald-800 p-1" title="Ver Reporte">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        @can('es-administrador')
                                        <button class="text-red-600 hover:text-red-800 p-1" title="Eliminar"><i class="fa-solid fa-trash-can"></i></button>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-sm text-gray-400 font-medium">No hay embarques de exportación.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <!-- MODAL NACIONAL CORREGIDO (DIVS COMPLETAS Y MAPEO BLINDADO) -->
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

            <form action="{{ route('recepcion.storeNacional') }}" method="POST" class="p-6 space-y-4 overflow-y-auto flex-grow max-h-[calc(95vh-120px)] scrollbar-thin scrollbar-thumb-gray-300">
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
                        @foreach($productores as $productor)
                        <option value="{{ $productor->id }}" data-sectores="{{ is_string($productor->sectores) ? $productor->sectores : json_encode($productor->sectores) }}">
                            {{ $productor->name ?? $productor->nombre }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <div id="contenedor-embarque-origen" class="hidden mt-3 drop-shadow-sm">
                    <label class="block text-xs font-bold text-blue-700 uppercase mb-1">Embarque de Exportación de Origen (Rastreo)</label>
                    <select name="recepcion_exportacion_id" id="recepcion_exportacion_select" class="w-full border border-blue-300 rounded-lg p-2.5 text-sm outline-none text-gray-800 bg-blue-50/40 focus:border-blue-500 focus:ring-1 focus:ring-blue-500 font-medium">
                        <option value="" selected>-- Selecciona el embarque origen --</option>
                        @foreach($embarquesExportacion as $embarque)
                        <option class="opcion-embarque hidden" value="{{ $embarque->id }}" data-operador="{{ $embarque->productor_id }}">
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
                            <input type="number" name="cajas_comercializar" id="cajas_com" value="0" min="0" oninput="calcularTotalesNacional()" {{ auth()->user()->rol === 'usuario_rechazo' ? 'readonly' : '' }} class="w-full border border-gray-300 rounded-lg p-2 text-sm outline-none font-semibold text-gray-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 {{ auth()->user()->rol === 'usuario_rechazo' ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white' }}">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Peso Comercial (Kg)</label>
                            <input type="number" name="peso_comercializar" id="peso_com" value="0.00" step="0.01" min="0" oninput="calcularTotalesNacional()" {{ auth()->user()->rol === 'usuario_rechazo' ? 'readonly' : '' }} class="w-full border border-gray-300 rounded-lg p-2 text-sm outline-none text-gray-800 focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 {{ auth()->user()->rol === 'usuario_rechazo' ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white' }}">
                        </div>
                    </div>
                    <div class="space-y-3 bg-red-50/40 p-4 rounded-xl border border-red-100">
                        <h4 class="text-xs font-bold text-red-800 uppercase tracking-wider flex items-center gap-1">
                            <i class="fa-solid fa-ban"></i> Procesado (Rechazo)
                        </h4>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Cajas de Rechazo</label>
                            <input type="number" name="cajas_rechazo_procesado" id="cajas_rec" value="0" min="0" oninput="calcularTotalesNacional()" {{ auth()->user()->rol === 'usuario_comercial' ? 'readonly' : '' }} class="w-full border border-gray-300 rounded-lg p-2 text-sm outline-none font-semibold text-gray-800 focus:border-red-500 focus:ring-1 focus:ring-red-500 {{ auth()->user()->rol === 'usuario_comercial' ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white' }}">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Peso Rechazo (Kg)</label>
                            <input type="number" name="peso_rechazo_procesado" id="peso_rec" value="0.00" step="0.01" min="0" oninput="calcularTotalesNacional()" {{ auth()->user()->rol === 'usuario_comercial' ? 'readonly' : '' }} class="w-full border border-gray-300 rounded-lg p-2 text-sm outline-none text-gray-800 focus:border-red-500 focus:ring-1 focus:ring-red-500 {{ auth()->user()->rol === 'usuario_comercial' ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white' }}">
                        </div>
                    </div>
                </div>

                <div class="bg-gray-100 p-4 rounded-xl grid grid-cols-2 gap-4 border border-gray-200">
                    @if(auth()->user()->rol === 'administrador' || auth()->user()->rol === 'usuario_rechazo')
                    <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300 col-span-2">
                        <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider flex items-center gap-1 mb-2">
                            <i class="fa-solid fa-boxes-stacked text-amber-600"></i> Registro Aparte (Sin Productor)
                        </h4>
                        <div>
                            <label class="block text-xs text-gray-600 mb-1">Cajas Vacías Totales del Día</label>
                            <input type="number" name="cajas_vacias_totales" value="0" min="0" class="w-full bg-white border border-gray-300 rounded-lg p-2 text-sm outline-none font-semibold text-gray-800 focus:border-amber-500 focus:ring-1 focus:ring-amber-500">
                            <p class="text-[11px] text-gray-400 mt-1">*Nota: Si vas a registrar solo cajas vacías, selecciona un productor genérico o de uso interno arriba.</p>
                        </div>
                    </div>
                    @endif
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Total Cajas</label>
                        <input type="number" name="total_cajas" id="total_cajas_input" value="0" readonly class="w-full bg-transparent border-none text-lg font-bold text-gray-900 p-0 focus:ring-0 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Total Kilogramos</label>
                        <input type="number" name="total_kg" id="total_kg_input" value="0.00" step="0.01" readonly class="w-full bg-transparent border-none text-lg font-bold text-amber-700 p-0 focus:ring-0 outline-none">
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 sticky bottom-0 bg-white z-10">
                    <button type="button" onclick="cerrarModalNacional()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold rounded-lg transition shadow cursor-pointer">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EXPORTACIÓN CORREGIDO -->
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
                        @if(isset($productores) && count($productores) > 0)
                        @foreach($productores as $productor)
                        <option value="{{ $productor->id }}" data-sectores="{{ is_string($productor->sectores) ? $productor->sectores : json_encode($productor->sectores) }}">
                            {{ $productor->name ?? $productor->nombre ?? 'Usuario sin nombre' }}
                        </option>
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
                        <input type="number" name="cajas_exportadas" id="cajas_exp" value="0" min="0" oninput="calcularSaldosExportacion()" required class="w-full bg-white border border-gray-300 rounded-lg p-2 text-sm outline-none font-semibold text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Peso de Exportación (Kg)</label>
                        <input type="number" name="peso_exportacion" value="0.00" step="0.01" min="0" required class="w-full bg-white border border-gray-300 rounded-lg p-2 text-sm outline-none text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-purple-50/30 p-4 rounded-xl border border-purple-100">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Cajas Restituidas (Devolución)</label>
                        <input type="number" name="cajas_restituidas" id="cajas_res" value="0" min="0" oninput="calcularSaldosExportacion()" required class="w-full bg-white border border-gray-300 rounded-lg p-2 text-sm outline-none text-gray-800 focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase mb-1">Saldo Pendiente (Diferencia)</label>
                        <input type="number" name="cajas_pendientes" id="cajas_pen_input" value="0" readonly class="w-full bg-transparent border-none text-lg font-bold text-amber-700 p-0 focus:ring-0 outline-none">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="cerrarModalExportacion()" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-100 transition cursor-pointer">Cancelar</button>
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-lg transition shadow cursor-pointer">Guardar Embarque</button>
                </div>
            </form>
        </div>
    </div>

    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500 w-full mt-auto">
        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.
    </footer>

    <link class="hidden" id="flatpickr-css" rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        // Variable global para controlar si el formulario nacional actúa como entrada estándar o captura de rechazo
        let modoCaptura = 'recepcion';

        document.addEventListener("DOMContentLoaded", function() {
            // Inicialización de Flatpickr para el formulario Nacional
            flatpickr("#fecha_nacional_input", {
                locale: "es",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                onChange: function(selectedDates, dateStr, instance) {
                    calcularSemanaDesdeFecha('nacional');
                }
            });

            // Inicialización de Flatpickr para el formulario de Exportación
            flatpickr("#fecha_exportacion_input", {
                locale: "es",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                onChange: function(selectedDates, dateStr, instance) {
                    calcularSemanaDesdeFecha('exportacion');
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

        // ACEPTA PARÁMETRO PARA DETERMINAR SI ES CAPTURA DE RECHAZO O RECEPCIÓN VIGENTE
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
            const inputCajasRes = document.getElementById('cajas_res');
            const inputCajasPen = document.getElementById('cajas_pen_input');

            const cajasExp = inputCajasExp ? (parseInt(inputCajasExp.value) || 0) : 0;
            const cajasRes = inputCajasRes ? (parseInt(inputCajasRes.value) || 0) : 0;

            const diferencia = cajasExp - cajasRes;
            if (inputCajasPen) inputCajasPen.value = diferencia >= 0 ? diferencia : 0;
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

        // FUNCIÓN MAESTRA: FILTRA SECTORES Y EMBARQUES DE EXPORTACIÓN BASADO EN EL OPERADOR SELECCIONADO
        function filtrarDatosPorOperador() {
            const selectProductor = document.getElementById('productor_select');
            const contenedorEmbarque = document.getElementById('contenedor-sector-exportacion'); // Contenedor del select de rastreo
            const opcionesEmbarque = document.querySelectorAll('.opcion-embarque');
            const selectEmbarque = document.getElementById('recepcion_exportacion_select');

            if (!selectProductor) return;
            const idOperador = selectProductor.value;

            // 1. Ejecuta la carga dinámica de sectores original
            cargarSectoresDelProductor();

            // 2. Filtra el universo de embarques de exportación para este operador específico
            const divEmbarque = document.getElementById('contenedor-embarque-origen');
            if (modoCaptura === 'rechazo' && idOperador) {
                if (selectEmbarque) selectEmbarque.value = ""; // Resetea selecciones previas para evitar contaminación

                opcionesEmbarque.forEach(opcion => {
                    if (opcion.getAttribute('data-operador') == idOperador) {
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
    </script>
</body>

</html>