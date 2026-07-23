<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitácora Sanidad y Nutrición - Sistema Control</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- 🔌 CDNs Obligatorias para Flatpickr (Estilos y Plugin de Semanas) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/weekSelect/weekSelect.css">
</head>

<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

    <!-- Navbar Institucional -->
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
                    <i class="fa-solid fa-notes-medical text-emerald-600"></i>
                    Historial de Sanidad y Nutrición
                </h1>
                <p class="text-gray-600 text-sm mt-1">Gestión integrada de aplicaciones fitosanitarias, dosificación en tanques de fertirriego y monitoreo de labores culturales.</p>
            </div>
            @if(auth()->user()->rol === 'administrador')
            <div>
                <a href="{{ route('sanidad.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded shadow transition">
                    <i class="fa-solid fa-plus mr-2"></i> Nueva Bitácora
                </a>
            </div>
            @endif
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
            <form method="GET" action="{{ route('sanidad.index') }}" id="formFiltros" class="grid grid-cols-1 md:grid-cols-12 items-end gap-4">

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
                        <label for="buscar_termino" class="block text-xs font-bold text-gray-600 uppercase mb-1.5 tracking-wider">Buscar por Sector u Operador:</label>
                        <div class="relative">
                            <input type="text" name="buscar_termino" id="buscar_termino" value="{{ request('buscar_termino') }}" placeholder="Ej: Sector 2 o Nombre del aplicador..." class="w-full bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-lg p-2 pr-8 focus:outline-emerald-500">
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
                    <a href="{{ route('sanidad.index') }}" class="text-xs text-red-600 hover:text-red-700 font-bold flex items-center justify-center gap-1 transition h-9 border border-red-200 rounded-lg bg-red-5/40 hover:bg-red-5">
                        <i class="fa-solid fa-filter-circle-xmark"></i> Limpiar
                    </a>
                </div>
                @endif
            </form>
        </div>

        <!-- LISTADO DE BITÁCORAS PRINCIPALES -->
        <div class="space-y-8">
            @forelse($bitacoras as $bitacora)
            <div class="bg-white rounded-xl shadow border border-gray-200 overflow-hidden">

                <!-- ENCABEZADO REGISTRO MAESTRO -->
                <div class="bg-gray-50 px-5 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="text-sm font-bold text-gray-700 flex items-center gap-1">
                            <i class="fa-solid fa-calendar text-gray-400"></i>
                            {{ \Carbon\Carbon::parse($bitacora->fecha)->format('d/m/Y') }}
                        </span>
                        <span class="bg-emerald-50 text-emerald-800 text-xs px-2.5 py-0.5 rounded-full font-bold border border-emerald-200">
                            Sector: {{ $bitacora->sector }}
                        </span>
                        <span class="text-xs text-gray-500 font-medium flex items-center gap-1">
                            <i class="fa-solid fa-user-gear text-emerald-600"></i>
                            Operador: <strong class="text-emerald-700">{{ $bitacora->operador ? $bitacora->operador->name : 'No asignado' }}</strong>
                        </span>
                    </div>

                    <!-- BOTONES: PDF Y ELIMINAR -->
                    <div class="flex items-center gap-2">
                        <a href="{{ route('sanidad.pdf', $bitacora->id) }}" target="_blank" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold px-3 py-1.5 rounded-md transition shadow flex items-center gap-1">
                            <i class="fa-solid fa-file-pdf"></i> PDF
                        </a>

                        @if(auth()->user()->rol === 'administrador')
                        <form action="{{ route('sanidad.destroy', $bitacora->id) }}" method="POST" id="form-delete-sanidad-{{ $bitacora->id }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="button" onclick="mostrarModalGenerico('form-delete-sanidad-{{ $bitacora->id }}')" class="bg-red-600 hover:bg-red-700 text-white text-xs font-bold px-3 py-1.5 rounded-md transition shadow flex items-center gap-1 cursor-pointer">
                                <i class="fa-solid fa-trash-can"></i> Eliminar
                            </button>
                        </form>
                        @endif
                    </div>
                </div>

                <!-- CONTENIDO INTERNO DE LA BITÁCORA -->
                <div class="p-6 space-y-8 divide-y divide-gray-100">

                    <!-- 1. MANEJO DE AGROQUÍMICOS -->
                    <div class="space-y-4">
                        <h4 class="text-sm font-bold text-orange-600 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-spray-can text-lg"></i>
                            1. Sección: Manejo de Agroquímicos
                        </h4>

                        @if($bitacora->agroquimicos->isNotEmpty())
                        @php $primerArq = $bitacora->agroquimicos->first(); @endphp

                        <!-- BARRA DE DATOS GENERALES -->
                        <div class="bg-orange-50/60 border border-orange-200/80 rounded-lg px-3 py-2.5 grid grid-cols-3 gap-1 text-xs items-center">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:gap-2 text-left">
                                <span class="text-orange-900 font-semibold text-[9px] sm:text-[10px]">Variedad:</span>
                                <span class="text-orange-950 font-black tracking-wide text-xs sm:text-sm">{{ $primerArq->variedad ?? '—' }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:gap-2 text-center items-center justify-center">
                                <span class="text-orange-900 font-semibold text-[9px] sm:text-[10px] block">N° Plantas:</span>
                                <span class="text-gray-900 font-mono font-bold tracking-tight text-xs sm:text-sm">{{ $primerArq->numero_plantas ? number_format($primerArq->numero_plantas) : '—' }}</span>
                            </div>
                            <div class="flex flex-col sm:flex-row sm:items-center sm:gap-2 text-right justify-end">
                                <span class="text-orange-900 font-semibold text-[9px] sm:text-[10px]">Trasplante:</span>
                                <span class="text-emerald-800 font-bold text-xs sm:text-sm">{{ $primerArq->fecha_trasplante ? \Carbon\Carbon::parse($primerArq->fecha_trasplante)->format('d/m/Y') : '—' }}</span>
                            </div>
                        </div>

                        <!-- TABLA DE APLICACIONES DE AGROQUÍMICOS -->
                        <div class="overflow-x-auto rounded-xl border border-gray-200 shadow-2xs mt-4">
                            <table class="w-full text-left text-xs text-gray-600 border-collapse min-w-[800px]">
                                <thead>
                                    <tr class="bg-gray-50 text-gray-700 font-semibold border-b border-gray-200">
                                        <th class="p-3 w-28">F. Aplicación</th>
                                        <th class="p-3 w-28">Tipo Aplicación</th>
                                        <th class="p-3">Producto / Ingrediente</th>
                                        <th class="p-3 w-40">Dosis / Unidad</th>
                                        <th class="p-3 w-40">Tipo de Solución</th>
                                        <th class="p-3">Observaciones de la Aplicación</th>
                                        <th class="p-3 text-center w-16">IS</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @foreach($bitacora->agroquimicos as $arq)
                                    <tr class="hover:bg-gray-50/50">
                                        <td class="p-3 font-medium text-gray-700">
                                            {{ \Carbon\Carbon::parse($arq->fecha_aplicacion)->format('d/m/Y') }}
                                        </td>
                                        <td class="p-3">
                                            <span class="font-bold text-gray-700 bg-gray-100 px-2 py-0.5 rounded text-[10px]">{{ $arq->aplicacion }}</span>
                                        </td>
                                        <td class="p-3 font-bold text-gray-900">
                                            {{ $arq->producto }}
                                        </td>
                                        <td class="p-3 font-mono font-bold text-orange-700">
                                            {{ $arq->dosis }} {{ $arq->unidad_dosis }}
                                        </td>
                                        <td class="p-3">
                                            <span class="font-bold text-xs {{ $arq->solucion_madre == 'SÍ' ? 'text-emerald-700' : 'text-blue-700' }}">
                                                {{ $arq->solucion_madre == 'SÍ' ? 'Solución Madre' : ($arq->solucion_diaria == 'SÍ' ? 'Solución Diaria' : 'Estándar') }}
                                            </span>
                                        </td>
                                        <td class="p-3 text-gray-500 italic max-w-xs truncate" title="{{ $arq->observaciones }}">
                                            {{ $arq->observaciones ?? '—' }}
                                        </td>
                                        <td class="p-3 text-center">
                                            @php
                                            $atributosCrudos = $arq->getAttributes();
                                            $isValor = $atributosCrudos['is_intervalo_seguridad'] ?? ($atributosCrudos['intervalo_seguridad'] ?? ($atributosCrudos['is'] ?? null));
                                            @endphp

                                            @if($isValor !== null && $isValor !== '')
                                            <span class="bg-amber-100 text-amber-800 font-bold px-2 py-0.5 rounded text-[10px]">
                                                {{ $isValor }}
                                            </span>
                                            @else
                                            <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                        <div class="p-4 text-center text-gray-400 italic bg-gray-50/50 rounded-xl border border-gray-200">
                            Sin aplicaciones de agroquímicos registradas en esta orden.
                        </div>
                        @endif
                    </div>

                    <!-- 2. MANEJO DE FERTILIZANTES -->
                    <div class="space-y-4 pt-6">
                        <h4 class="text-sm font-bold text-emerald-600 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-flask-vial text-lg"></i>
                            2. Sección: Manejo de Fertilizantes
                        </h4>

                        @if($bitacora->fertilizantes->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach($bitacora->fertilizantes->groupBy('tanque') as $nombreTanque => $accionesTanque)
                            <div class="bg-stone-20 border-stone-200 rounded-lg p-4 shadow-2xs space-y-2">
                                <div class="bg-emerald-600 text-white text-xs font-bold px-3 py-1.5 rounded-lg inline-flex items-center gap-1.5 uppercase tracking-wide">
                                    <i class="fa-solid fa-prescription-bottle-droplet"></i>
                                    Tanque: {{ $nombreTanque }}
                                </div>

                                <div class="overflow-hidden rounded-lg border border-stone-200 bg-white">
                                    <table class="w-full text-left text-xs text-gray-600 border-collapse">
                                        <thead>
                                            <tr class="bg-stone-100 font-semibold text-stone-700 border-b border-stone-200">
                                                <th class="p-2 w-3/5">Acción / Instrucción Texto</th>
                                                <th class="p-2 w-2/5 text-right">Dosificación</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-stone-100">
                                            @foreach($accionesTanque as $acc)
                                            <tr class="hover:bg-stone-50/50">
                                                <td class="p-2 text-gray-700 italic">
                                                    {{ $acc->accion ?? 'Aplicación estándar de nutriente.' }}
                                                </td>
                                                <td class="p-2 font-mono font-bold text-emerald-700 text-right">
                                                    {{ $acc->cantidad }} {{ $acc->unidad_cantidad }}
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="p-4 text-center text-gray-400 italic bg-gray-50/50 rounded-xl border border-gray-200">
                            Sin nutrientes añadidos en esta orden.
                        </div>
                        @endif
                    </div>

                    <!-- 3. CAMPOS COMPLEMENTARIOS GLOBALES -->
                    @if($bitacora->fertilizantes->isNotEmpty() && ($bitacora->fertilizantes->first()->labores_culturales || $bitacora->fertilizantes->first()->observaciones))
                    <div class="pt-6 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-600">
                        <div class="bg-stone-50 p-3 rounded-lg border border-stone-200 shadow-2xs">
                            <span class="font-bold text-stone-700 block uppercase tracking-wider text-[10px] mb-1">Labores Culturales Realizadas:</span>
                            <p class="italic text-stone-600">{{ $bitacora->fertilizantes->first()->labores_culturales ?? 'Ninguna registrada.' }}</p>
                        </div>
                        <div class="bg-stone-50 p-3 rounded-lg border border-stone-200 shadow-2xs">
                            <span class="font-bold text-stone-700 block uppercase tracking-wider text-[10px] mb-1">Observaciones Generales de la Mezcla:</span>
                            <p class="italic text-stone-600">{{ $bitacora->fertilizantes->first()->observaciones ?? 'Sin observaciones generales.' }}</p>
                        </div>
                    </div>
                    @endif

                </div>

            </div>
            @empty
            <div class="bg-white rounded-xl shadow border border-gray-200 py-12 text-center text-gray-500">
                <i class="fa-solid fa-notes-medical text-5xl text-gray-300 mb-4 block"></i>
                No se han encontrado bitácoras de sanidad y nutrición en el rango seleccionado.
            </div>
            @endforelse
        </div>

    </main>

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
            // Inicializar Flatpickr
            flatpickr("#semana_picker", {
                locale: "es",
                firstDayOfWeek: 1,
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

            // Manejador del botón de confirmación del modal de eliminación
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