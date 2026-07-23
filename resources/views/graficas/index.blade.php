<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis Gráfico - Sistema Control</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="{{ asset('js/chart.js') }}"></script>
</head>

<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

        <nav class="bg-emerald-600 text-white shadow-md px-6 py-4 flex justify-between items-center w-full">
            <!-- Izquierda: Título alineado a la izquierda de forma natural -->
            <div class="text-xl font-bold tracking-wider flex items-center gap-2 text-left">
                <i class="fa-solid fa-leaf"></i> SISTEMA CONTROL
            </div>
            
            <!-- Derecha: Bloque de usuario y botón alineados juntos a la derecha -->
            <div class="flex items-center gap-4 text-right">
                <span class="bg-emerald-700/50 px-3 py-1.5 rounded-lg border border-emerald-500/30 flex items-center gap-1 text-xs font-medium whitespace-nowrap">
                    <i class="fa-solid fa-user"></i> {{ auth()->user()->name }}
                </span>
                   <a href="{{ route('dashboard') }}" class="text-xs bg-emerald-700 hover:bg-emerald-800 px-3 py-1.5 rounded transition flex items-center gap-1">
                        <i class="fa-solid fa-circle-chevron-left"></i> Volver al Panel
                    </a>
            </div>
        </nav>  
    <main class="max-w-[95%] mx-auto px-4 py-8 w-full flex-grow space-y-6">
        
        <!-- ENCABEZADO Y FILTROS INTEGRADOS Y ACOMODADOS CORRECTAMENTE -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-blue-600"></i> Panel de Análisis y Tendencias Bioclimáticas
                </h1>
                <p class="text-gray-500 text-xs sm:text-sm mt-1">Comportamiento histórico de las variables críticas computadas en el invernadero.</p>
            </div>

            <!-- Formulario de filtros limpio con Tailwind -->
            <form method="GET" action="{{ route('graficas.index') }}" class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                
                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="mes" class="text-xs font-bold text-gray-700 uppercase">Filtrar por Mes:</label>
                    <input type="month" name="mes" id="mes" value="{{ request('mes') }}" onchange="this.form.submit()" 
                        class="bg-gray-50 border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                </div>

                @can('es-administrador')
                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="buscar_sector" class="text-xs font-bold text-gray-700 uppercase">Seleccionar Sector:</label>
                    <select name="buscar_sector" id="buscar_sector" onchange="this.form.submit()" 
                        class="bg-gray-50 border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Todos los sectores</option>
                        @php
                            $sectoresFiltro = \App\Models\User::whereNotNull('sectores')->pluck('sectores')->toArray();
                            $sectoresUnicos = [];
                            foreach($sectoresFiltro as $cadena) {
                                foreach(explode(',', $cadena) as $sec) {
                                    $secLimpio = trim($sec);
                                    if(!empty($secLimpio)) $sectoresUnicos[] = $secLimpio;
                                }
                            }
                            $sectoresUnicos = array_unique($sectoresUnicos);
                            sort($sectoresUnicos);
                        @endphp
                        @foreach($sectoresUnicos as $sectorOpt)
                            <option value="{{ $sectorOpt }}" {{ request('buscar_sector') === $sectorOpt ? 'selected' : '' }}>{{ $sectorOpt }}</option>
                        @endforeach
                    </select>
                </div>
                @endcan

                @if(request('mes') || request('buscar_sector'))
                <div class="flex items-end h-full pt-4 sm:pt-0 w-full sm:w-auto">
                    <a href="{{ route('graficas.index') }}" class="text-xs font-bold text-red-600 hover:text-red-700 flex items-center gap-1 bg-red-50 border border-red-200 px-3 py-2 rounded-lg transition shadow-sm">
                        <i class="fa-solid fa-filter-circle-xmark"></i> Restablecer filtros
                    </a>
                </div>
                @endif
            </form>
        </div>

        <!-- CUADRÍCULA DE LAS GRÁFICAS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col">
                <div class="flex items-start gap-3 border-b border-gray-100 pb-3 mb-4">
                    <div class="p-2 bg-orange-50 rounded-lg text-orange-600">
                        <i class="fa-solid fa-temperature-three-quarters text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-800">Evolución del DPV (Déficit de Presión de Vapor)</h3>
                        <p class="text-xs text-gray-500">Rango óptimo vegetal: 0.8 a 1.4 kPa</p>
                    </div>
                </div>
                <div class="relative w-full h-[280px]">
                    <canvas id="chartDPV"></canvas>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col">
                <div class="flex items-start gap-3 border-b border-gray-100 pb-3 mb-4">
                    <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                        <i class="fa-solid fa-droplet text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-800">Eficiencia de Hidratación (% Drenaje)</h3>
                        <p class="text-xs text-gray-500">Relación porcentual diaria entre el volumen de riego y drenaje</p>
                    </div>
                </div>
                <div class="relative w-full h-[280px]">
                    <canvas id="chartDrenaje"></canvas>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col md:col-span-2">
                <div class="flex items-start gap-3 border-b border-gray-100 pb-3 mb-4">
                    <div class="p-2 bg-amber-50 rounded-lg text-amber-600">
                        <i class="fa-solid fa-sun text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-800">Historial de Radiación Solar (Lectura en Lux)</h3>
                        <p class="text-xs text-gray-500">Curva de intensidad lumínica registrada por fecha de monitoreo</p>
                    </div>
                </div>
                <div class="relative w-full h-[280px]">
                    <canvas id="chartLux"></canvas>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col md:col-span-2">
                <div class="flex items-start gap-3 border-b border-gray-100 pb-3 mb-4">
                    <div class="p-2 bg-purple-50 rounded-lg text-purple-600">
                        <i class="fa-solid fa-flask text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-800">Diferencial de Conductividad Eléctrica (Δ CE)</h3>
                        <p class="text-xs text-gray-500">Balance nutricional absorbido por el sustrato (Salida - Entrada)</p>
                    </div>
                </div>
                <div class="relative w-full h-[280px]">
                    <canvas id="chartCE"></canvas>
                </div>
            </div>

        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500 w-full mt-auto">
        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.
    </footer>

    <script>
    window.onload = function() {
        if (typeof Chart === 'undefined') {
            console.error("No se pudo cargar el archivo chart.js desde la carpeta public/js/");
            return;
        }

        const etiquetasFechas = {!! json_encode(array_values($fechas)) !!};
        const datosDPV       = {!! json_encode(array_values($dpv), JSON_NUMERIC_CHECK) !!};
        const datosDrenaje   = {!! json_encode(array_values($drenaje), JSON_NUMERIC_CHECK) !!};
        const datosCE        = {!! json_encode(array_values($difCe), JSON_NUMERIC_CHECK) !!};
        const datosLux       = {!! json_encode(array_values($lux), JSON_NUMERIC_CHECK) !!};

        // 1. Gráfica: DPV
        new Chart(document.getElementById('chartDPV'), {
            type: 'line',
            data: {
                labels: etiquetasFechas,
                datasets: [{
                    label: 'DPV',
                    data: datosDPV,
                    borderColor: '#ea580c',
                    backgroundColor: 'rgba(234, 88, 12, 0.1)',
                    borderWidth: 2,
                    tension: 0.2,
                    fill: true
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // 2. Gráfica: Drenaje
        new Chart(document.getElementById('chartDrenaje'), {
            type: 'bar',
            data: {
                labels: etiquetasFechas,
                datasets: [{
                    label: '% Drenaje',
                    data: datosDrenaje,
                    backgroundColor: '#2563eb'
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // 3. Gráfica: Lux
        new Chart(document.getElementById('chartLux'), {
            type: 'line',
            data: {
                labels: etiquetasFechas,
                datasets: [{
                    label: 'Lectura Lux',
                    data: datosLux,
                    borderColor: '#d97706',
                    backgroundColor: 'rgba(217, 119, 6, 0.05)',
                    borderWidth: 2.5,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });

        // 4. Gráfica: Balance CE
        new Chart(document.getElementById('chartCE'), {
            type: 'line',
            data: {
                labels: etiquetasFechas,
                datasets: [{
                    label: 'Diferencial CE',
                    data: datosCE,
                    borderColor: '#9333ea',
                    backgroundColor: 'rgba(147, 51, 234, 0.05)',
                    borderWidth: 2,
                    tension: 0.2,
                    fill: true
                }]
            },
            options: { responsive: true, maintainAspectRatio: false }
        });
    };
    </script>
</body>

</html>