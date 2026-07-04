<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis Gráfico - Sistema Control</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="{{ asset('js/chart.js') }}"></script>
    
</head>

<body>

    <nav>
        <div class="nav-title">
            <i class="fa-solid fa-leaf"></i> SISTEMA CONTROL
        </div>
        <a href="{{ route('dashboard') }}" class="btn-volver">
            <i class="fa-solid fa-house"></i> Volver al Panel
        </a>
    </nav>

    <main>
        <div class="header-section">
            <h1><i class="fa-solid fa-chart-line" style="color: #2563eb;"></i> Panel de Análisis y Tendencias Bioclimáticas</h1>
            <p>Comportamiento histórico de las variables críticas computadas en el invernadero.</p>
        </div>

        <div class="contenedor-filtro" style="margin-top: 24px;">
            <form method="GET" action="{{ route('graficas.index') }}" style="display: flex; align-items: center; gap: 16px; margin: 0; flex-wrap: wrap;">
                
                <div class="grupo-campo">
                    <label for="mes" class="label-filtro">Filtrar por Mes:</label>
                    <input type="month" name="mes" id="mes" value="{{ request('mes') }}" onchange="this.form.submit()" class="input-filtro">
                </div>

                @can('es-administrador')
                <div class="grupo-campo">
                    <label for="buscar_sector" class="label-filtro">Seleccionar Sector:</label>
                    <select name="buscar_sector" id="buscar_sector" onchange="this.form.submit()" class="input-filtro">
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
                    <a href="{{ route('graficas.index') }}" class="link-limpiar">
                        <i class="fa-solid fa-filter-circle-xmark"></i> Restablecer filtros
                    </a>
                @endif
            </form>
        </div>

        <div class="grid-graficas">

            <div class="card-grafica">
                <div class="card-header">
                    <i class="fa-solid fa-temperature-three-quarters" style="color: #ea580c; font-size: 20px;"></i>
                    <div>
                        <h3 class="card-title">Evolución del DPV (Déficit de Presión de Vapor)</h3>
                        <p class="card-subtitle">Rango óptimo vegetal: 0.8 a 1.4 kPa</p>
                    </div>
                </div>
                <div class="contenedor-canvas" style="height: 280px; position: relative; width: 100%;">
                    <!-- Se removió width y height fijos -->
                    <canvas id="chartDPV"></canvas>
                </div>
            </div>

            <div class="card-grafica">
                <div class="card-header">
                    <i class="fa-solid fa-droplet" style="color: #2563eb; font-size: 20px;"></i>
                    <div>
                        <h3 class="card-title">Eficiencia de Hidratación (% Drenaje)</h3>
                        <p class="card-subtitle">Relación porcentual diaria entre el volumen de riego y drenaje</p>
                    </div>
                </div>
                <div class="contenedor-canvas" style="height: 280px; position: relative; width: 100%;">
                    <!-- Se removió width y height fijos -->
                    <canvas id="chartDrenaje"></canvas>
                </div>
            </div>

            <div class="card-grafica col-span-2">
                <div class="card-header">
                    <i class="fa-solid fa-sun" style="color: #d97706; font-size: 20px;"></i>
                    <div>
                        <h3 class="card-title">Historial de Radiación Solar (Lectura en Lux)</h3>
                        <p class="card-subtitle">Curva de intensidad lumínica registrada por fecha de monitoreo</p>
                    </div>
                </div>
                <!-- Para móvil bajamos un poco la altura de las gráficas originalmente anchas para que no se corten visualmente -->
                <div class="contenedor-canvas" style="height: 280px; position: relative; width: 100%;">
                    <!-- Se removió width y height fijos -->
                    <canvas id="chartLux"></canvas>
                </div>
            </div>

            <div class="card-grafica col-span-2">
                <div class="card-header">
                    <i class="fa-solid fa-flask" style="color: #9333ea; font-size: 20px;"></i>
                    <div>
                        <h3 class="card-title">Diferencial de Conductividad Eléctrica (Δ CE)</h3>
                        <p class="card-subtitle">Balance nutricional absorbido por el sustrato (Salida - Entrada)</p>
                    </div>
                </div>
                <div class="contenedor-canvas" style="height: 280px; position: relative; width: 100%;">
                    <!-- Se removió width y height fijos -->
                    <canvas id="chartCE"></canvas>
                </div>
            </div>

        </div>
    </main>

    <footer>
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