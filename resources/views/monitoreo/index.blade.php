<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoreo Clima y Riego - Sistema Control</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

    <nav class="bg-emerald-600 text-white shadow-md">
        <div class="max-w-[95%] mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <i class="fa-solid fa-leaf text-2xl mr-2"></i>
                    <span class="font-bold text-xl tracking-wider">SISTEMA CONTROL</span>
                </div>
                <a href="{{ url('/') }}" class="text-sm bg-emerald-700 hover:bg-emerald-800 px-3 py-2 rounded-md transition font-medium">
                    <i class="fa-solid fa-house mr-1"></i> Dashboard
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
            <form method="GET" action="{{ route('monitoreo.index') }}" class="grid grid-cols-1 md:grid-cols-12 items-end gap-4">
                
                <div class="col-span-1 md:col-span-3">
                    <label for="semana" class="block text-xs font-bold text-gray-600 uppercase mb-1.5 tracking-wider">Filtrar por Semana:</label>
                    <input type="week" name="semana" id="semana" value="{{ request('semana') }}" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 p-2 cursor-pointer">
                </div>

                <div class="col-span-1 md:col-span-3">
                    <label for="mes" class="block text-xs font-bold text-gray-600 uppercase mb-1.5 tracking-wider">Filtrar por Mes:</label>
                    <input type="month" name="mes" id="mes" value="{{ request('mes') }}" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 p-2 cursor-pointer">
                </div>

                @can('es-administrador')
                <div class="col-span-1 md:col-span-5 flex gap-2 items-end">
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

                @if(request('semana') || request('mes') || request('buscar_termino'))
                <div class="col-span-1 md:col-span-1 pb-2">
                    <a href="{{ route('monitoreo.index') }}" class="text-xs text-red-600 hover:text-red-700 font-bold flex items-center justify-center gap-1 transition">
                        <i class="fa-solid fa-filter-circle-xmark"></i> Limpiar
                    </a>
                </div>
                @endif
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-md overflow-hidden border border-gray-200">
            <div class="p-5 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <span class="font-semibold text-gray-700 text-sm">Resumen de Resultados Evaluados</span>
                <span class="text-xs text-gray-500">Haga clic en la fecha o en el icono de auditoría para ver la inspection completa</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
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
                                    <i class="fa-solid fa-user text-gray-400 text-[10px]"></i>
                                    {{ $row->user ? $row->user->name : 'Sistema / Automatizado' }}
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

                            <td class="py-3 px-4 text-center flex justify-center gap-3">
                                <a href="{{ route('monitoreo.show', $row->id) }}" class="text-emerald-600 hover:text-emerald-800" title="Ver Detalle completo">
                                    <i class="fa-solid fa-magnifying-glass-chart"></i>
                                </a>

                                @can('es-administrador')
                                <a href="{{ route('monitoreo.edit', $row->id) }}" class="text-blue-600 hover:text-blue-800" title="Editar">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                                <form action="{{ route('monitoreo.destroy', $row->id) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 cursor-pointer" onclick="return confirm('¿Seguro que deseas eliminar este registro?')" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endcan
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

    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500 w-full mt-auto">
        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.
    </footer>

</body>

</html>