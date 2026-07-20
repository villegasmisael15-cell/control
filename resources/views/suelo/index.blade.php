<!DOCTYPE html>

<html lang="es" class="h-full">


<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Monitoreo Suelo - Sistema Control</title>

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


        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-6">

            <form method="GET" action="{{ route('suelo.index') }}" class="grid grid-cols-1 md:grid-cols-12 items-end gap-4">



                <div class="col-span-1 md:col-span-3">

                    <label for="semana" class="block text-xs font-bold text-gray-600 uppercase mb-1.5 tracking-wider">Filtrar por Semana:</label>

                    <input type="week" name="semana" id="semana" value="{{ request('semana') }}" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-lg p-2 cursor-pointer">

                </div>


                <div class="col-span-1 md:col-span-3">

                    <label for="mes" class="block text-xs font-bold text-gray-600 uppercase mb-1.5 tracking-wider">Filtrar por Mes:</label>

                    <input type="month" name="mes" id="mes" value="{{ request('mes') }}" onchange="this.form.submit()" class="w-full bg-gray-50 border border-gray-300 text-gray-700 text-sm rounded-lg p-2 cursor-pointer">

                </div>


                @can('es-administrador')

                <div class="col-span-1 md:col-span-5 flex gap-2 items-end">

                    <div class="w-full">

                        <label for="buscar_termino" class="block text-xs font-bold text-gray-600 uppercase mb-1.5 tracking-wider">Buscar por Sector u Operador:</label>

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


                @if(request('semana') || request('mes') || request('buscar_termino'))

                <div class="col-span-1 md:col-span-1 pb-2">

                    <a href="{{ route('suelo.index') }}" class="text-xs text-red-600 hover:text-red-700 font-bold flex items-center justify-center gap-1 transition">

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

                        <tr class="bg-gray-100 border-b border-gray-200 text-gray-700 uppercase tracking-wider text tracking-wide text-[11px] font-bold">

                            <th class="py-3 px-4">Fecha</th>

                            <th class="py-3 px-4">Sector</th>

                            <th class="py-3 px-4">Operador</th>

                            <th class="py-3 px-4">DPV Clima</th>

                            <th class="py-3 px-4 bg-blue-50/50">Tensiómetro (cb) / Estado</th>

                            <th class="py-3 px-4 bg-purple-50/50">CE Suelo</th>

                            <th class="py-3 px-4 bg-purple-50/50">pH Suelo</th>

                            <th class="py-3 px-4 bg-orange-50/50">Radiación (Lux)</th>

                            <th class="py-3 px-4 text-center">Estatus Clima</th>

                        </tr>

                    </thead>

                    <tbody class="divide-y divide-gray-200 text-gray-700 text-sm">

                        @forelse($monitoreos as $row)

                        <tr class="hover:bg-gray-50 transition duration-150">

                            <td class="py-3.5 px-4 font-medium">

                                {{ \Carbon\Carbon::parse($row->fecha)->format('d/m/Y') }}

                            </td>

                            <td class="py-3.5 px-4">

                                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded font-semibold">{{ $row->sector }}</span>

                            </td>

                            <td class="py-3.5 px-4">

                                <span class="text-xs text-gray-600 font-medium flex items-center gap-1">

                                    <i class="fa-solid fa-user text-gray-400 text-[10px]"></i>

                                    {{ $row->user ? $row->user->name : 'Sistema / Automatizado' }}

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


                            <td class="py-3.5 px-4 bg-purple-50/20 font-medium">{{ $row->ce ?? '—' }}</td>

                            <td class="py-3.5 px-4 bg-purple-50/20 font-medium">{{ $row->ph ?? '—' }}</td>

                            <td class="py-3.5 px-4 bg-orange-50/10 font-medium">

                                {{ number_format($row->radiacion_lectura) }}

                                <span class="text-[10px] block font-bold {{ $row->radiacion_semaforo === 'VERDE' ? 'text-emerald-600' : ($row->radiacion_semaforo === 'AMARILLO' ? 'text-amber-600' : 'text-red-600') }}">

                                    {{ $row->radiacion_semaforo }}

                                </span>

                            </td>

                            <td class="py-3.5 px-4 text-center">

                                <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full {{ $row->estatus_general === 'ÓPTIMO' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">

                                    {{ $row->estatus_general }}

                                </span>

                            </td>

                        </tr>

                        @empty

                        <tr>

                            <td colspan="9" class="py-10 text-center text-gray-500">

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


    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500 w-full mt-auto">

        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.

    </footer>


</body>


</html>