<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoreo Clima y Riego - Sistema Control</title>
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
                    <i class="fa-solid fa-cloud-sun-rain text-emerald-600"></i>
                    Bitácora de Monitoreo Climático y Riego
                </h1>
                <p class="text-gray-600 text-sm mt-1">Historial optimizado con balances hídricos, químicos y estados predictivos por sector.</p>
            </div>
            <div>
                <a href="{{ route('monitoreo.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded shadow">
                    <i class="fa-solid fa-plus mr-2"></i> Nuevo Registro
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

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" action="{{ route('monitoreo.index') }}" id="formFiltros" class="grid grid-cols-1 md:grid-cols-12 items-end gap-4">

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
                                class="w-full bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 p-2 pl-9 cursor-pointer shadow-sm outline-none">
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
                            <input type="text" name="buscar_termino" id="buscar_termino" value="{{ request('buscar_termino') }}" placeholder="Ej: Sector 1 o Nombre del trabajador..." class="w-full bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 p-2 pr-8">
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
                    <a href="{{ route('monitoreo.index') }}" class="text-xs text-red-600 hover:text-red-700 font-bold flex items-center justify-center gap-1 transition h-9 border border-red-200 rounded-lg bg-red-5/40 hover:bg-red-5">
                        <i class="fa-solid fa-filter-circle-xmark"></i> Limpiar
                    </a>
                </div>
                @endif
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
            <div class="p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <span class="font-semibold text-gray-700 text-sm">Resumen de Resultados Evaluados</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[950px]">
                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-200 text-gray-700 uppercase tracking-wider text-[11px] font-bold">
                            <th class="py-3 px-4">Fecha</th>
                            <th class="py-3 px-4">Sector</th>
                            <th class="py-3 px-4">Operador</th>
                            <th class="py-3 px-4">DPV</th>
                            <th class="py-3 px-4 bg-blue-50/50">% Drenaje</th>
                            <th class="py-3 px-4 bg-amber-50/50">Dif. CE</th>
                            <th class="py-3 px-4 bg-purple-50/50">Dif. pH</th>
                            <th class="py-3 px-4 bg-stone-50">% Caída Noct.</th>
                            <th class="py-3 px-4 bg-orange-50/50">Semáforo Rad.</th>
                            <th class="py-3 px-4 text-center">Estatus Clima</th>
                            <th class="py-3 px-4 text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700 text-sm">
                        @forelse($monitoreos as $row)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="py-3.5 px-4 font-medium">
                                <a href="{{ route('monitoreo.show', $row->id) }}" class="text-emerald-600 hover:text-emerald-800 hover:underline flex items-center gap-1.5">
                                    <i class="fa-solid fa-eye text-xs text-gray-400"></i> {{ \Carbon\Carbon::parse($row->fecha)->format('d/m/Y') }}
                                </a>
                            </td>
                            <td class="py-3.5 px-4"><span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded font-semibold">{{ $row->sector }}</span></td>
                            <td class="py-3.5 px-4">
                                <span class="text-xs text-gray-600 font-medium flex items-center gap-1">
                                    <i class="fa-solid fa-user-tie text-emerald-600 text-[10px]"></i>
                                    {{ $row->user ? $row->user->name : 'Sin operador asignado' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-xs font-semibold">{{ $row->dpv }}</td>
                            <td class="py-3.5 px-4 bg-blue-50/20 font-semibold text-blue-600">{{ $row->porcentaje_drenaje }}%</td>
                            <td class="py-3.5 px-4 bg-amber-50/20 font-medium {{ $row->diferencia_ce > 0.5 ? 'text-amber-600' : '' }}">{{ $row->diferencia_ce }}</td>
                            <td class="py-3.5 px-4 bg-purple-50/20 font-medium">{{ $row->diferencia_ph }}</td>
                            <td class="py-3.5 px-4 bg-stone-50/40 font-semibold text-stone-600">{{ $row->porcentaje_caida_nocturna }}%</td>

                            <td class="py-3.5 px-4 bg-orange-50/10">
                                @if(($row->radiacion_semaforo ?? 'VERDE') === 'VERDE')
                                <span class="px-2 py-0.5 inline-flex text-xs font-bold rounded bg-emerald-100 text-emerald-800"><i class="fa-solid fa-circle text-[8px] mr-1 mt-1"></i> Óptimo</span>
                                @elseif(($row->radiacion_semaforo ?? 'VERDE') === 'AMARILLO')
                                <span class="px-2 py-0.5 inline-flex text-xs font-bold rounded bg-amber-100 text-amber-800"><i class="fa-solid fa-circle text-[8px] mr-1 mt-1"></i> Alerta</span>
                                @else
                                <span class="px-2 py-0.5 inline-flex text-xs font-bold rounded bg-red-100 text-red-800"><i class="fa-solid fa-circle text-[8px] mr-1 mt-1"></i> Crítico</span>
                                @endif
                            </td>

                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $row->estatus_general === 'ÓPTIMO' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $row->estatus_general }}
                                </span>
                            </td>

                            <!-- 🛠️ BOTONES DE ACCIÓN -->
                            <td class="py-3.5 px-4 text-center">
                                <div class="inline-flex items-center justify-center gap-2">
                                    <a href="{{ route('monitoreo.show', $row->id) }}" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-700 p-2 rounded-lg transition shadow-2xs border border-emerald-200" title="Ver Detalle completo">
                                        <i class="fa-solid fa-magnifying-glass-chart text-sm"></i>
                                    </a>

                                    @can('es-administrador')
                                    <a href="{{ route('monitoreo.edit', $row->id) }}" class="bg-blue-50 hover:bg-blue-100 text-blue-700 p-2 rounded-lg transition shadow-2xs border border-blue-200" title="Editar">
                                        <i class="fa-solid fa-pen-to-square text-sm"></i>
                                    </a>

                                    <!-- Formulario con Modal Personalizado (Sin rastro de unitasrubra.com) -->
                                    <form action="{{ route('monitoreo.destroy', $row->id) }}" method="POST" id="delete-form-{{ $row->id }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" onclick="mostrarModalEliminar('{{ $row->id }}')" class="bg-red-50 hover:bg-red-100 text-red-700 p-2 rounded-lg transition shadow-2xs border border-red-200 cursor-pointer" title="Eliminar">
                                            <i class="fa-solid fa-trash text-sm"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="py-10 text-center text-gray-500">
                                <i class="fa-solid fa-folder-open text-4xl text-gray-300 mb-3 block"></i>
                                No hay registros almacenados coincidiendo con el filtro.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <!-- 🎨 MODAL PERSONALIZADO DE ELIMINACIÓN (Diseño Limpio) -->
    <div id="modalEliminar" class="fixed inset-0 bg-black/50 backdrop-blur-xs hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 text-center transform transition-all scale-95 opacity-0 duration-200" id="modalContenido">
            <div class="w-12 h-12 rounded-full bg-red-100 text-red-600 flex items-center justify-center mx-auto mb-4 text-xl">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-800 mb-1">¿Eliminar registro?</h3>
            <p class="text-xs text-gray-500 mb-6">Esta acción no se puede deshacer y borrará los datos seleccionados del sistema.</p>
            <div class="flex gap-3">
                <button type="button" onclick="cerrarModalEliminar()" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-xl transition text-xs cursor-pointer">
                    Cancelar
                </button>
                <button type="button" id="btnConfirmarEliminar" class="flex-1 bg-red-600 hover:bg-red-700 text-white font-semibold py-2.5 rounded-xl transition text-xs cursor-pointer shadow-md">
                    Sí, eliminar
                </button>
            </div>
        </div>
    </div>

    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500 w-full mt-auto">
        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.
    </footer>

    <!-- 🔌 SCRIPTS DE CONTEXTO Y FUNCIONAMIENTO FLATPICKR Y MODAL -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/weekSelect/weekSelect.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        let formIdParaEliminar = null;

        function mostrarModalEliminar(id) {
            formIdParaEliminar = id;
            const modal = document.getElementById('modalEliminar');
            const contenido = document.getElementById('modalContenido');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => {
                contenido.classList.remove('scale-95', 'opacity-0');
                contenido.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function cerrarModalEliminar() {
            const modal = document.getElementById('modalEliminar');
            const contenido = document.getElementById('modalContenido');
            contenido.classList.remove('scale-100', 'opacity-100');
            contenido.classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
                formIdParaEliminar = null;
            }, 200);
        }

        document.getElementById('btnConfirmarEliminar').addEventListener('click', function() {
            if (formIdParaEliminar) {
                document.getElementById('delete-form-' + formIdParaEliminar).submit();
            }
        });

        document.addEventListener("DOMContentLoaded", function() {
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
        });
    </script>
</body>

</html>