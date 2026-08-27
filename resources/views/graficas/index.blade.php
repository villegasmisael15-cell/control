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

        <!-- PESTAÑAS PARA CAMBIAR DE MÓDULO -->
        <div class="flex items-center gap-2 border-b border-gray-200 pb-3">
            <a href="{{ route('graficas.index', ['modulo' => 'hidroponia']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-bold transition {{ ($modulo ?? 'hidroponia') === 'hidroponia' ? 'bg-emerald-600 text-white shadow' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
               <i class="fa-solid fa-droplet mr-1.5"></i> Hidroponía (Clima y Riego)
            </a>
            <a href="{{ route('graficas.index', ['modulo' => 'suelo']) }}" 
               class="px-4 py-2 rounded-lg text-sm font-bold transition {{ ($modulo ?? '') === 'suelo' ? 'bg-emerald-600 text-white shadow' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50' }}">
               <i class="fa-solid fa-seedling mr-1.5"></i> Suelo
            </a>
        </div>

        <!-- ENCABEZADO Y FILTROS EN CASCADA -->
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-chart-line text-blue-600"></i> Panel de Análisis y Tendencias ({{ ucfirst($modulo ?? 'hidroponia') }})
                </h1>
                <p class="text-gray-500 text-xs sm:text-sm mt-1">Comportamiento histórico de las variables críticas computadas.</p>
            </div>

            <!-- Formulario de filtros en cascada -->
            <form method="GET" action="{{ route('graficas.index') }}" class="flex flex-wrap items-center gap-4 w-full lg:w-auto">
                <input type="hidden" name="modulo" value="{{ $modulo ?? 'hidroponia' }}">

                @if(($modulo ?? 'hidroponia') === 'hidroponia')
                    @can('es-administrador')
                    <!-- 1. Seleccionar Dueño -->
                    <div class="flex flex-col gap-1 w-full sm:w-auto">
                        <label for="dueno_id" class="text-xs font-bold text-gray-700 uppercase">Dueño / Operador:</label>
                        <select name="dueno_id" id="dueno_id" onchange="document.getElementById('invernadero').value=''; document.getElementById('buscar_sector').value=''; this.form.submit()"
                            class="bg-gray-50 border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="">Seleccione un dueño</option>
                            @foreach($dueños ?? [] as $d)
                            <option value="{{ $d->id }}" {{ request('dueno_id') == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Seleccionar Invernadero -->
                    <div class="flex flex-col gap-1 w-full sm:w-auto">
                        <label for="invernadero" class="text-xs font-bold text-gray-700 uppercase">Invernadero:</label>
                        <select name="invernadero" id="invernadero" onchange="document.getElementById('buscar_sector').value=''; this.form.submit()"
                            class="bg-gray-50 border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            {{ !request('dueno_id') ? 'disabled' : '' }}>
                            <option value="">Seleccione invernadero</option>
                            @foreach($invernaderos ?? [] as $inv)
                            <option value="{{ $inv }}" {{ request('invernadero') === $inv ? 'selected' : '' }}>{{ $inv }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Seleccionar Sector -->
                    <div class="flex flex-col gap-1 w-full sm:w-auto">
                        <label for="buscar_sector" class="text-xs font-bold text-gray-700 uppercase">Sector:</label>
                        <select name="buscar_sector" id="buscar_sector" onchange="this.form.submit()"
                            class="bg-gray-50 border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            {{ !request('invernadero') ? 'disabled' : '' }}>
                            <option value="">Seleccione sector</option>
                            @foreach($sectores ?? [] as $sec)
                            <option value="{{ $sec }}" {{ request('buscar_sector') === $sec ? 'selected' : '' }}>{{ $sec }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endcan
                @endif

                <!-- 4. Filtrar por Mes con Desplegable en Español -->
                <div class="flex flex-col gap-1 w-full sm:w-auto">
                    <label for="mes" class="text-xs font-bold text-gray-700 uppercase">Filtrar por Mes:</label>
                    <select name="mes" id="mes" onchange="this.form.submit()"
                        class="bg-gray-50 border border-gray-300 rounded-lg px-3 py-1.5 text-sm font-medium text-gray-800 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="">Todos los meses</option>
                        @foreach($mesesDisponibles ?? [] as $m)
                            @php
                                $carbonFecha = \Carbon\Carbon::createFromFormat('Y-m', $m)->locale('es');
                                $nombreMes = ucfirst($carbonFecha->translatedFormat('F Y'));
                            @endphp
                            <option value="{{ $m }}" {{ request('mes') === $m ? 'selected' : '' }}>{{ $nombreMes }}</option>
                        @endforeach
                    </select>
                </div>

                @if(request('dueno_id') || request('mes') || request('buscar_sector') || request('invernadero'))
                <div class="flex items-end h-full pt-4 sm:pt-0 w-full sm:w-auto">
                    <a href="{{ route('graficas.index', ['modulo' => $modulo]) }}" class="text-xs font-bold text-red-600 hover:text-red-700 flex items-center gap-1 bg-red-50 border border-red-200 px-3 py-2 rounded-lg transition shadow-sm">
                        <i class="fa-solid fa-filter-circle-xmark"></i> Restablecer filtros
                    </a>
                </div>
                @endif
            </form>
        </div>

        <!-- CUADRÍCULA DE LAS GRÁFICAS SEGÚN EL MÓDULO SELECCIONADO -->
        @if(($modulo ?? 'hidroponia') === 'suelo')
            <!-- GRÁFICAS DE SUELO -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col">
                    <h3 class="text-base font-bold text-gray-800 mb-1">Evolución del DPV (Suelo)</h3>
                    <div class="relative w-full h-[280px]"><canvas id="chartDPV"></canvas></div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col">
                    <h3 class="text-base font-bold text-gray-800 mb-1">Lectura de Tensiómetro</h3>
                    <div class="relative w-full h-[280px]"><canvas id="chartTensiometro"></canvas></div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col">
                    <h3 class="text-base font-bold text-gray-800 mb-1">Radiación Solar (Lux)</h3>
                    <div class="relative w-full h-[280px]"><canvas id="chartRadiacion"></canvas></div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col">
                    <h3 class="text-base font-bold text-gray-800 mb-1">Conductividad Eléctrica (CE Suelo)</h3>
                    <div class="relative w-full h-[280px]"><canvas id="chartCE"></canvas></div>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col md:col-span-2">
                    <h3 class="text-base font-bold text-gray-800 mb-1">Nivel de pH en Suelo</h3>
                    <div class="relative w-full h-[280px]"><canvas id="chartPH"></canvas></div>
                </div>
            </div>
        @else
            <!-- GRÁFICAS DE HIDROPONÍA -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col">
                    <div class="flex items-start gap-3 border-b border-gray-100 pb-3 mb-4">
                        <div class="p-2 bg-orange-50 rounded-lg text-orange-600"><i class="fa-solid fa-temperature-three-quarters text-xl"></i></div>
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Evolución del DPV (Déficit de Presión de Vapor)</h3>
                            <p class="text-xs text-gray-500">Rango óptimo vegetal: 0.8 a 1.4 kPa</p>
                        </div>
                    </div>
                    <div class="relative w-full h-[280px]"><canvas id="chartDPV"></canvas></div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col">
                    <div class="flex items-start gap-3 border-b border-gray-100 pb-3 mb-4">
                        <div class="p-2 bg-blue-50 rounded-lg text-blue-600"><i class="fa-solid fa-droplet text-xl"></i></div>
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Eficiencia de Hidratación (% Drenaje)</h3>
                            <p class="text-xs text-gray-500">Relación porcentual diaria entre el volumen de riego y drenaje</p>
                        </div>
                    </div>
                    <div class="relative w-full h-[280px]"><canvas id="chartDrenaje"></canvas></div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col md:col-span-2">
                    <div class="flex items-start gap-3 border-b border-gray-100 pb-3 mb-4">
                        <div class="p-2 bg-amber-50 rounded-lg text-amber-600"><i class="fa-solid fa-sun text-xl"></i></div>
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Historial de Radiación Solar (Lectura en Lux)</h3>
                            <p class="text-xs text-gray-500">Curva de intensidad lumínica registrada por fecha de monitoreo</p>
                        </div>
                    </div>
                    <div class="relative w-full h-[280px]"><canvas id="chartLux"></canvas></div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col md:col-span-2">
                    <div class="flex items-start gap-3 border-b border-gray-100 pb-3 mb-4">
                        <div class="p-2 bg-teal-50 rounded-lg text-teal-600"><i class="fa-solid fa-water text-xl"></i></div>
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Comparativa de Conductividad Eléctrica (CE Entrada vs CE Salida)</h3>
                            <p class="text-xs text-gray-500">Comportamiento simultáneo de los niveles de sales en el riego y en el drenaje</p>
                        </div>
                    </div>
                    <div class="relative w-full h-[280px]"><canvas id="chartCEComparativa"></canvas></div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col md:col-span-2">
                    <div class="flex items-start gap-3 border-b border-gray-100 pb-3 mb-4">
                        <div class="p-2 bg-purple-50 rounded-lg text-purple-600"><i class="fa-solid fa-flask text-xl"></i></div>
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Diferencial de Conductividad Eléctrica (Δ CE)</h3>
                            <p class="text-xs text-gray-500">Balance nutricional absorbido por el sustrato (Salida - Entrada)</p>
                        </div>
                    </div>
                    <div class="relative w-full h-[280px]"><canvas id="chartCE"></canvas></div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 flex flex-col md:col-span-2">
                    <div class="flex items-start gap-3 border-b border-gray-100 pb-3 mb-4">
                        <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600"><i class="fa-solid fa-vials text-xl"></i></div>
                        <div>
                            <h3 class="text-base font-bold text-gray-800">Comportamiento del pH (pH Entrada, pH Salida y Diferencial)</h3>
                            <p class="text-xs text-gray-500">Monitoreo simultáneo de la acidez/alcalinidad en el riego, drenaje y su variación</p>
                        </div>
                    </div>
                    <div class="relative w-full h-[280px]"><canvas id="chartPHComparativa"></canvas></div>
                </div>
            </div>
        @endif
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

            const moduloActivo = "{{ $modulo ?? 'hidroponia' }}";
            const etiquetasFechas = {!! json_encode(array_values($fechas ?? [])) !!};

            if (moduloActivo === 'suelo') {
                // --- INICIALIZAR GRÁFICAS DE SUELO ---
                const datosDPV = {!! json_encode(array_values($dpv ?? []), JSON_NUMERIC_CHECK) !!};
                const datosTensiometro = {!! json_encode(array_values($tensiometro ?? []), JSON_NUMERIC_CHECK) !!};
                const datosRadiacion = {!! json_encode(array_values($radiacion ?? []), JSON_NUMERIC_CHECK) !!};
                const datosCE = {!! json_encode(array_values($ce ?? []), JSON_NUMERIC_CHECK) !!};
                const datosPH = {!! json_encode(array_values($ph ?? []), JSON_NUMERIC_CHECK) !!};

                new Chart(document.getElementById('chartDPV'), { type: 'line', data: { labels: etiquetasFechas, datasets: [{ label: 'DPV Suelo', data: datosDPV, borderColor: '#ea580c', backgroundColor: 'rgba(234, 88, 12, 0.1)', fill: true, tension: 0.2 }] }, options: { responsive: true, maintainAspectRatio: false } });
                new Chart(document.getElementById('chartTensiometro'), { type: 'line', data: { labels: etiquetasFechas, datasets: [{ label: 'Tensiómetro', data: datosTensiometro, borderColor: '#0284c7', backgroundColor: 'rgba(2, 132, 199, 0.1)', fill: true, tension: 0.2 }] }, options: { responsive: true, maintainAspectRatio: false } });
                new Chart(document.getElementById('chartRadiacion'), { type: 'line', data: { labels: etiquetasFechas, datasets: [{ label: 'Radiación (Lux)', data: datosRadiacion, borderColor: '#d97706', backgroundColor: 'rgba(217, 119, 6, 0.1)', fill: true, tension: 0.2 }] }, options: { responsive: true, maintainAspectRatio: false } });
                new Chart(document.getElementById('chartCE'), { type: 'line', data: { labels: etiquetasFechas, datasets: [{ label: 'CE', data: datosCE, borderColor: '#9333ea', backgroundColor: 'rgba(147, 51, 234, 0.1)', fill: true, tension: 0.2 }] }, options: { responsive: true, maintainAspectRatio: false } });
                new Chart(document.getElementById('chartPH'), { type: 'line', data: { labels: etiquetasFechas, datasets: [{ label: 'pH', data: datosPH, borderColor: '#059669', backgroundColor: 'rgba(5, 150, 105, 0.1)', fill: true, tension: 0.2 }] }, options: { responsive: true, maintainAspectRatio: false } });

            } else {
                // --- INICIALIZAR GRÁFICAS DE HIDROPONÍA ---
                const datosDPV = {!! json_encode(array_values($dpv ?? []), JSON_NUMERIC_CHECK) !!};
                const datosDrenaje = {!! json_encode(array_values($drenaje ?? []), JSON_NUMERIC_CHECK) !!};
                const datosCE = {!! json_encode(array_values($difCe ?? []), JSON_NUMERIC_CHECK) !!};
                const datosLux = {!! json_encode(array_values($lux ?? []), JSON_NUMERIC_CHECK) !!};
                const datosCEEntrada = {!! json_encode(array_values($ceEntrada ?? []), JSON_NUMERIC_CHECK) !!};
                const datosCESalida = {!! json_encode(array_values($ceSalida ?? []), JSON_NUMERIC_CHECK) !!};
                const datosPHEntrada = {!! json_encode(array_values($phEntrada ?? []), JSON_NUMERIC_CHECK) !!};
                const datosPHSalida = {!! json_encode(array_values($phSalida ?? []), JSON_NUMERIC_CHECK) !!};
                const datosDifPH = {!! json_encode(array_values($difPh ?? []), JSON_NUMERIC_CHECK) !!};

                new Chart(document.getElementById('chartDPV'), {
                    type: 'line',
                    data: { labels: etiquetasFechas, datasets: [{ label: 'DPV', data: datosDPV, borderColor: '#ea580c', backgroundColor: 'rgba(234, 88, 12, 0.1)', borderWidth: 2, tension: 0.2, fill: true }] },
                    options: { responsive: true, maintainAspectRatio: false }
                });

                new Chart(document.getElementById('chartDrenaje'), {
                    type: 'bar',
                    data: { labels: etiquetasFechas, datasets: [{ label: '% Drenaje', data: datosDrenaje, backgroundColor: '#2563eb' }] },
                    options: { responsive: true, maintainAspectRatio: false }
                });

                new Chart(document.getElementById('chartLux'), {
                    type: 'line',
                    data: { labels: etiquetasFechas, datasets: [{ label: 'Lectura Lux', data: datosLux, borderColor: '#d97706', backgroundColor: 'rgba(217, 119, 6, 0.05)', borderWidth: 2.5, tension: 0.3, fill: true }] },
                    options: { responsive: true, maintainAspectRatio: false }
                });

                new Chart(document.getElementById('chartCEComparativa'), {
                    type: 'line',
                    data: {
                        labels: etiquetasFechas,
                        datasets: [
                            { label: 'CE Entrada', data: datosCEEntrada, borderColor: '#0d9488', backgroundColor: 'rgba(13, 148, 136, 0.05)', borderWidth: 2, tension: 0.2, fill: true },
                            { label: 'CE Salida', data: datosCESalida, borderColor: '#f59e0b', backgroundColor: 'rgba(245, 158, 11, 0.05)', borderWidth: 2, tension: 0.2, fill: true }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });

                new Chart(document.getElementById('chartCE'), {
                    type: 'line',
                    data: { labels: etiquetasFechas, datasets: [{ label: 'Diferencial CE', data: datosCE, borderColor: '#9333ea', backgroundColor: 'rgba(147, 51, 234, 0.05)', borderWidth: 2, tension: 0.2, fill: true }] },
                    options: { responsive: true, maintainAspectRatio: false }
                });

                new Chart(document.getElementById('chartPHComparativa'), {
                    type: 'line',
                    data: {
                        labels: etiquetasFechas,
                        datasets: [
                            { label: 'pH Entrada', data: datosPHEntrada, borderColor: '#6366f1', backgroundColor: 'rgba(99, 102, 241, 0.05)', borderWidth: 2, tension: 0.2, fill: false },
                            { label: 'pH Salida', data: datosPHSalida, borderColor: '#ec4899', backgroundColor: 'rgba(236, 72, 153, 0.05)', borderWidth: 2, tension: 0.2, fill: false },
                            { label: 'Dif. pH (Salida - Entrada)', data: datosDifPH, borderColor: '#8b5cf6', backgroundColor: 'rgba(139, 92, 246, 0.05)', borderWidth: 2, borderDash: [4, 4], tension: 0.2, fill: false }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }
        };
    </script>
</body>

</html>