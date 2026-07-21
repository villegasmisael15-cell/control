<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bitácora Sanidad y Nutrición - Sistema Control</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

    <!-- Navbar Institucional -->
    <nav class="bg-emerald-600 text-white shadow-md">
        <div class="max-w-[95%] mx-auto px-4 shadow-sm">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <i class="fa-solid fa-leaf text-2xl mr-2"></i>
                    <span class="font-bold text-xl tracking-wider">SISTEMA CONTROL</span>
                </div>
                <div class="flex items-center gap-4 text-sm font-medium">
                    <span class="bg-emerald-700 px-3 py-1 rounded text-xs flex items-center gap-1">
                        <i class="fa-solid fa-user"></i> {{ auth()->user()->name }}
                    </span>
                    <a href="{{ url('/') }}" class="text-emerald-100 hover:text-white transition flex items-center gap-1">
                        <i class="fa-solid fa-house"></i> Panel Principal
                    </a>
                </div>
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
            <div>
                <a href="{{ route('sanidad.create') }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded shadow transition">
                    <i class="fa-solid fa-plus mr-2"></i> Nueva Bitácora Combinada
                </a>
            </div>
        </div>

        <!-- BLOQUE DE FILTROS -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">
            <form method="GET" action="{{ route('sanidad.index') }}" class="grid grid-cols-1 md:grid-cols-12 items-end gap-4">
                
                <div class="col-span-1 md:col-span-3">
                    <label for="semana" class="block text-xs font-bold text-gray-600 uppercase mb-1.5 tracking-wider">Filtrar por Semana:</label>
                    <input type="week" name="semana" id="semana" value="{{ request('semana') }}" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-lg p-2 cursor-pointer focus:outline-emerald-500">
                </div>

                <div class="col-span-1 md:col-span-3">
                    <label for="mes" class="block text-xs font-bold text-gray-600 uppercase mb-1.5 tracking-wider">Filtrar por Mes:</label>
                    <input type="month" name="mes" id="mes" value="{{ request('mes') }}" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-lg p-2 cursor-pointer focus:outline-emerald-500">
                </div>

                @can('es-administrador')
                <div class="col-span-1 md:col-span-5 flex gap-2 items-end">
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

                @if(request('semana') || request('mes') || request('buscar_termino'))
                <div class="col-span-1 md:col-span-1 pb-2">
                    <a href="{{ route('sanidad.index') }}" class="text-xs text-red-600 hover:text-red-700 font-bold flex items-center justify-center gap-1 transition">
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
                <div class="bg-gray-50 px-5 py-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="text-sm font-bold text-gray-700 flex items-center gap-1">
                            <i class="fa-solid fa-calendar text-gray-400"></i>
                            {{ \Carbon\Carbon::parse($bitacora->fecha)->format('d/m/Y') }}
                        </span>
                        <span class="bg-emerald-50 text-emerald-800 text-xs px-2.5 py-0.5 rounded-full font-bold border border-emerald-200">
                            Sector: {{ $bitacora->sector }}
                        </span>
                    </div>
                    <span class="text-xs text-gray-500 font-medium flex items-center gap-1">
                        <i class="fa-solid fa-user-gear text-emerald-600"></i>
                        Operador Encargado: <strong class="text-emerald-700">{{ $bitacora->operador ? $bitacora->operador->name : 'No asignado' }}</strong>
                    </span>
                </div>

                <!-- DETALLES ACOMODADOS VERTICALMENTE -->
                <div class="p-6 space-y-8 divide-y divide-gray-100">
                    
                    <!-- 1. MANEJO DE AGROQUÍMICOS -->
                    <div class="space-y-3">
                        <h4 class="text-sm font-bold text-orange-600 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-spray-can text-lg"></i>
                            1. Sección: Manejo de Agroquímicos
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs text-gray-600 border border-gray-200 min-w-[1100px]">
                                <thead>
                                    <tr class="bg-orange-50/50 text-orange-800 border-b border-gray-200 font-semibold text-center">
                                        <th class="p-2 text-left">F. Aplicación</th>
                                        <th class="p-2 text-left">Tipo Aplicación</th>
                                        <th class="p-2 text-left">Producto</th>
                                        <th class="p-2">Dosis / Unidad</th>
                                        <th class="p-2">IS</th>
                                        <th class="p-2">Variedad</th>
                                        <th class="p-2">N° Plantas</th>
                                        <th class="p-2">Sol. Madre</th>
                                        <th class="p-2">F. Trasplante</th>
                                        <th class="p-2">Sol. Diaria</th>
                                        <th class="p-2 text-left">Observaciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($bitacora->agroquimicos as $arq)
                                    <tr class="hover:bg-gray-50/50 text-center">
                                        <td class="p-2 text-left whitespace-nowrap">{{ \Carbon\Carbon::parse($arq->fecha_aplicacion)->format('d/m/Y') }}</td>
                                        <td class="p-2 text-left font-medium text-stone-700">{{ $arq->aplicacion }}</td>
                                        <td class="p-2 text-left font-bold text-gray-900">{{ $arq->producto }}</td>
                                        <td class="p-2 font-mono font-semibold text-orange-700 bg-orange-50/20 rounded">{{ $arq->dosis }} {{ $arq->unidad_dosis }}</td>
                                        <td class="p-2">
                                            @if($arq->is_intervalo_seguridad)
                                            <span class="bg-amber-100 text-amber-800 font-bold px-1.5 py-0.5 rounded text-[10px]">
                                                {{ $arq->is_intervalo_seguridad }} 
                                            </span>
                                            @else
                                            <span class="text-gray-400">—</span>
                                            @endif
                                        </td>
                                        <td class="p-2 text-gray-700">{{ $arq->variedad ?? '—' }}</td>
                                        <td class="p-2 font-mono">{{ $arq->numero_plantas ? number_format($arq->numero_plantas) : '—' }}</td>
                                        <td class="p-2 italic text-stone-600">{{ $arq->solucion_madre ?? '—' }}</td>
                                        <td class="p-2 whitespace-nowrap">{{ $arq->fecha_trasplante ? \Carbon\Carbon::parse($arq->fecha_trasplante)->format('d/m/Y') : '—' }}</td>
                                        <td class="p-2 font-medium text-stone-700">{{ $arq->solucion_diaria ?? '—' }}</td>
                                        <td class="p-2 text-left text-gray-500 max-w-xs truncate" title="{{ $arq->observaciones }}">{{ $arq->observaciones ?? '—' }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="11" class="p-4 text-center text-gray-400 italic bg-gray-50/50">Sin aplicaciones de agroquímicos registradas en esta orden.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 2. MANEJO DE FERTILIZANTES -->
                    <div class="space-y-3 pt-6">
                        <h4 class="text-sm font-bold text-emerald-600 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fa-solid fa-flask-vial text-lg"></i>
                            2. Sección: Manejo de Fertilizantes
                        </h4>
                        <!-- CORREGIDO: Contenedor flex con 'justify-center' para centrar la tabla completa en la bitácora -->
                        <div class="flex justify-center w-full overflow-x-auto">
                            <table class="w-full text-xs text-gray-600 border border-gray-200 max-w-xl">
                                <thead>
                                    <tr class="bg-emerald-50/50 text-emerald-800 border-b border-gray-200 font-semibold text-center">
                                        <th class="p-2 w-1/2">Tanque</th>
                                        <th class="p-2 w-1/2">Cantidad / Unidad</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($bitacora->fertilizantes as $fer)
                                    <tr class="hover:bg-gray-50/50 text-center">
                                        <td class="p-2 font-medium text-gray-900">
                                            {{ $fer->tanque }}
                                        </td>
                                        <td class="p-2 font-mono font-bold text-emerald-700 bg-emerald-50/10 rounded">
                                            {{ $fer->cantidad }} {{ $fer->unidad_cantidad }}
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="p-3 text-center text-gray-400 italic bg-gray-50/50">Sin nutrientes añadidos en esta orden.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- 3. CAMPOS COMPLEMENTARIOS GLOBALES -->
                    @if(($bitacora->fertilizantes->first() && $bitacora->fertilizantes->first()->labores_culturales) || ($bitacora->fertilizantes->first() && $bitacora->fertilizantes->first()->observaciones))
                    <div class="pt-4 grid grid-cols-1 md:grid-cols-2 gap-4 text-xs text-gray-600">
                        <div class="bg-stone-50 p-3 rounded-lg border border-gray-150">
                            <span class="font-bold text-stone-700 block uppercase tracking-wider text-[10px] mb-1">Labores Culturales Realizadas:</span>
                            <p class="italic text-stone-600">{{ $bitacora->fertilizantes->first()->labores_culturales ?? 'Ninguna registrada.' }}</p>
                        </div>
                        <div class="bg-stone-50 p-3 rounded-lg border border-gray-150">
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

    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500 w-full mt-auto">
        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.
    </footer>

</body>

</html>