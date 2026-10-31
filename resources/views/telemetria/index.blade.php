<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Telemetría e IoT - Sistema Control</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

    <!-- Navbar superior -->
    <nav class="bg-emerald-600 text-white shadow-md">
        <div class="max-w-[98%] sm:max-w-[95%] mx-auto px-2 sm:px-4 h-14 sm:h-16 flex items-center justify-between gap-2">
            <div class="flex items-center min-w-0">
                <i class="fa-solid fa-leaf text-base sm:text-2xl mr-1.5 sm:mr-2 text-emerald-200"></i>
                <span class="font-bold text-xs sm:text-xl tracking-wider truncate">SISTEMA CONTROL</span>
            </div>

            <div class="flex items-center gap-1.5 sm:gap-3 text-xs shrink-0">
                <span class="bg-emerald-700/80 px-2 py-1 rounded-md hidden md:flex items-center gap-1 max-w-[150px] truncate" title="{{ auth()->user()->name }}">
                    <i class="fa-solid fa-user text-[10px]"></i>
                    <span class="truncate">{{ auth()->user()->name }}</span>
                </span>
                <a href="{{ route('dashboard') }}" class="bg-emerald-700 hover:bg-emerald-800 px-2.5 sm:px-3.5 py-1.5 rounded-md transition flex items-center gap-1 font-medium shadow-xs whitespace-nowrap">
                    <i class="fa-solid fa-circle-chevron-left text-[10px]"></i>
                    <span class="inline">Volver al Panel</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="max-w-[98%] sm:max-w-[95%] mx-auto px-2 sm:px-4 py-4 sm:py-8 w-full flex-grow space-y-6 sm:space-y-10">

        <!-- Cabecera de la sección -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-microchip text-cyan-600"></i> Panel de Telemetría en Vivo
                </h1>
                <p class="text-gray-600 text-xs sm:text-sm mt-0.5">Monitoreo independiente y actualización inteligente por zona ESP32.</p>
            </div>
        </div>

        <!-- ================= SECCIÓN 1: INVERNADERO 1 ================= -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-6">
            <div class="p-4 sm:p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gray-50/50">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="font-bold text-gray-800 text-sm sm:text-base flex items-center gap-2">
                        <i class="fa-solid fa-tower-broadcast text-emerald-600"></i> INVERNADERO 1
                    </h3>
                    <span class="text-[10px] sm:text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md font-medium">Ciclo Dinámico Inteligente</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('telemetria.historial', 'invernadero1') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-clock-rotate-left"></i> Ver Registros Anteriores
                    </a>
                    <div id="status-inv1">
                        <span class="bg-gray-100 text-gray-600 text-[11px] sm:text-xs font-semibold px-2.5 py-1 rounded-full">Verificando...</span>
                    </div>
                </div>
            </div>

            <!-- Formulario de configuración de tiempos para Invernadero 1 -->
            <div class="bg-amber-50 border-b border-amber-200 p-3 sm:p-4">
                <form action="{{ route('telemetria.configurar') }}" method="POST" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                    @csrf
                    <input type="hidden" name="esp32_id" value="INVERNADERO 1">

                    <div class="grid grid-cols-2 xs:grid-cols-4 sm:flex sm:flex-wrap items-center gap-2 sm:gap-3">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1">
                            <span class="font-semibold text-gray-700 whitespace-nowrap"><i class="fa-solid fa-clock text-emerald-600"></i> Inicio:</span>
                            <input type="time" name="hora_inicio" value="{{ isset($configInv1) && $configInv1->hora_inicio ? \Carbon\Carbon::parse($configInv1->hora_inicio)->format('H:i') : '08:00' }}" class="bg-white border border-gray-300 rounded px-2 py-1 font-medium w-full sm:w-auto">
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1">
                            <span class="font-semibold text-gray-700 whitespace-nowrap">Fin:</span>
                            <input type="time" name="hora_fin" value="{{ isset($configInv1) && $configInv1->hora_fin ? \Carbon\Carbon::parse($configInv1->hora_fin)->format('H:i') : '18:00' }}" class="bg-white border border-gray-300 rounded px-2 py-1 font-medium w-full sm:w-auto">
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1">
                            <span class="font-semibold text-gray-700 whitespace-nowrap">Activo(s):</span>
                            <input type="number" name="intervalo_activo_sec" value="{{ isset($configInv1) ? ($configInv1->intervalo_activo_ms / 1000) : 3 }}" class="bg-white border border-gray-300 rounded px-2 py-1 w-full sm:w-16 text-center font-medium">
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1">
                            <span class="font-semibold text-gray-700 whitespace-nowrap">Descanso(m):</span>
                            <input type="number" name="intervalo_descanso_min" value="{{ isset($configInv1) ? ($configInv1->intervalo_descanso_ms / 60000) : 30 }}" class="bg-white border border-gray-300 rounded px-2 py-1 w-full sm:w-16 text-center font-medium">
                        </div>
                    </div>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 sm:py-1.5 rounded transition shadow-xs w-full sm:w-auto text-center whitespace-nowrap mt-1 sm:mt-0">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Guardar Cambios
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase tracking-wider border-b border-gray-200">
                            <th class="py-3 px-3 font-semibold text-cyan-700">Báscula 1</th>
                            <th class="py-3 px-3 font-semibold text-cyan-700">Báscula 2</th>
                            <th class="py-3 px-3 font-semibold text-emerald-700">pH</th>
                            <th class="py-3 px-3 font-semibold text-amber-700">EC (mS/cm)</th>
                            <th class="py-3 px-3 font-semibold text-amber-700">TDS (uS/cm)</th>
                            <th class="py-3 px-3 font-semibold text-blue-700">Temp. Amb</th>
                            <th class="py-3 px-3 font-semibold text-blue-600">Hum. Amb</th>
                            <th class="py-3 px-3 font-semibold text-purple-700">Temp. IR</th>
                            <th class="py-3 px-3 font-semibold text-amber-600">Suelo (HW-390)</th>
                            <th class="py-3 px-3 font-semibold text-green-700">Temp. Suelo</th>
                            <th class="py-3 px-3 font-semibold">eCO2</th>
                            <th class="py-3 px-3 font-semibold">TVOC</th>
                            <th class="py-3 px-3 font-semibold">Luz</th>
                            <th class="py-3 px-3 font-semibold">Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-inv1" class="divide-y divide-gray-200 text-gray-700">
                        @forelse($sensoresInvernadero as $sensor)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="py-3.5 px-3 font-bold text-cyan-700">{{ $sensor->peso_bascula_1 !== null ? $sensor->peso_bascula_1 . ' kg' : '--' }}</td>
                            <td class="py-3.5 px-3 font-bold text-cyan-700">{{ $sensor->peso_bascula_2 !== null ? $sensor->peso_bascula_2 . ' kg' : '--' }}</td>
                            <td class="py-3.5 px-3 font-semibold text-emerald-700">{{ $sensor->ph_valor !== null ? $sensor->ph_valor : '--' }}</td>
                            <td class="py-3.5 px-3 font-semibold text-amber-700">{{ $sensor->tds_valor !== null ? $sensor->tds_valor . ' mS/cm' : '--' }}</td>
                            <td class="py-3.5 px-3 font-semibold text-amber-700">{{ $sensor->tds_valor !== null ? ($sensor->tds_valor * 1000) . ' uS/cm' : '--' }}</td>
                            <td class="py-3.5 px-3 font-medium text-blue-700">{{ $sensor->temp_ambiente !== null ? $sensor->temp_ambiente . ' °C' : '--' }}</td>
                            <td class="py-3.5 px-3 font-medium text-blue-600">{{ $sensor->humedad_ambiente !== null ? $sensor->humedad_ambiente . ' %' : '--' }}</td>
                            <td class="py-3.5 px-3 font-medium text-purple-700">{{ $sensor->temp_infrarrojo !== null ? $sensor->temp_infrarrojo . ' °C' : '--' }}</td>
                            <td class="py-3.5 px-3 font-medium text-amber-600">{{ $sensor->he390_valor !== null ? $sensor->he390_valor . ' %' : '--' }}</td>
                            <td class="py-3.5 px-3 font-medium text-green-700">{{ $sensor->temp_ds18b20 !== null ? $sensor->temp_ds18b20 . ' °C' : '--' }}</td>
                            <td class="py-3.5 px-3">{{ $sensor->calidad_aire_eco2 !== null ? $sensor->calidad_aire_eco2 . ' ppm' : '--' }}</td>
                            <td class="py-3.5 px-3">{{ $sensor->calidad_aire_tvoc !== null ? $sensor->calidad_aire_tvoc . ' ppb' : '--' }}</td>
                            <td class="py-3.5 px-3">{{ $sensor->luz_lux !== null ? $sensor->luz_lux . ' lx' : '--' }}</td>
                            <td class="py-3.5 px-3 text-gray-500 text-[11px]">{{ $sensor->created_at ? \Carbon\Carbon::parse($sensor->created_at)->format('d/m/Y H:i:s') : 'Ahora' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="14" class="py-8 text-center text-gray-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-2"></i>
                                <p>No hay registros para Invernadero 1.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= SECCIÓN 2: INVERNADERO 2 ================= -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-6">
            <div class="p-4 sm:p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gray-50/50">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="font-bold text-gray-800 text-sm sm:text-base flex items-center gap-2">
                        <i class="fa-solid fa-tower-broadcast text-emerald-600"></i> INVERNADERO 2
                    </h3>
                    <span class="text-[10px] sm:text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md font-medium">Ciclo Dinámico Inteligente</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('telemetria.historial', 'invernadero2') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-clock-rotate-left"></i> Ver Registros Anteriores
                    </a>
                    <div id="status-inv2">
                        <span class="bg-gray-100 text-gray-600 text-[11px] sm:text-xs font-semibold px-2.5 py-1 rounded-full">Verificando...</span>
                    </div>
                </div>
            </div>

            <!-- Formulario de configuración de tiempos para Invernadero 2 -->
            <div class="bg-amber-50 border-b border-amber-200 p-3 sm:p-4">
                <form action="{{ route('telemetria.configurar') }}" method="POST" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                    @csrf
                    <input type="hidden" name="esp32_id" value="INVERNADERO 2">

                    <div class="grid grid-cols-2 xs:grid-cols-4 sm:flex sm:flex-wrap items-center gap-2 sm:gap-3">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1">
                            <span class="font-semibold text-gray-700 whitespace-nowrap"><i class="fa-solid fa-clock text-emerald-600"></i> Inicio:</span>
                            <input type="time" name="hora_inicio" value="{{ isset($configInv2) && $configInv2->hora_inicio ? \Carbon\Carbon::parse($configInv2->hora_inicio)->format('H:i') : '08:00' }}" class="bg-white border border-gray-300 rounded px-2 py-1 font-medium w-full sm:w-auto">
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1">
                            <span class="font-semibold text-gray-700 whitespace-nowrap">Fin:</span>
                            <input type="time" name="hora_fin" value="{{ isset($configInv2) && $configInv2->hora_fin ? \Carbon\Carbon::parse($configInv2->hora_fin)->format('H:i') : '18:00' }}" class="bg-white border border-gray-300 rounded px-2 py-1 font-medium w-full sm:w-auto">
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1">
                            <span class="font-semibold text-gray-700 whitespace-nowrap">Activo(s):</span>
                            <input type="number" name="intervalo_activo_sec" value="{{ isset($configInv2) ? ($configInv2->intervalo_activo_ms / 1000) : 3 }}" class="bg-white border border-gray-300 rounded px-2 py-1 w-full sm:w-16 text-center font-medium">
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1">
                            <span class="font-semibold text-gray-700 whitespace-nowrap">Descanso(m):</span>
                            <input type="number" name="intervalo_descanso_min" value="{{ isset($configInv2) ? ($configInv2->intervalo_descanso_ms / 60000) : 30 }}" class="bg-white border border-gray-300 rounded px-2 py-1 w-full sm:w-16 text-center font-medium">
                        </div>
                    </div>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 sm:py-1.5 rounded transition shadow-xs w-full sm:w-auto text-center whitespace-nowrap mt-1 sm:mt-0">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Guardar Cambios
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase tracking-wider border-b border-gray-200">
                            <th class="py-3 px-3 font-semibold text-cyan-700">Báscula 1</th>
                            <th class="py-3 px-3 font-semibold text-cyan-700">Báscula 2</th>
                            <th class="py-3 px-3 font-semibold text-amber-600">Suelo (HW-390)</th>
                            <th class="py-3 px-3 font-semibold text-emerald-700">pH</th>
                            <th class="py-3 px-3 font-semibold text-amber-700">EC (mS/cm)</th>
                            <th class="py-3 px-3 font-semibold text-amber-700">TDS (uS/cm)</th>
                            <th class="py-3 px-3 font-semibold text-blue-700">Temp. Amb</th>
                            <th class="py-3 px-3 font-semibold text-blue-600">Hum. Amb</th>
                            <th class="py-3 px-3 font-semibold text-purple-700">Temp. IR</th>
                            <th class="py-3 px-3 font-semibold">eCO2</th>
                            <th class="py-3 px-3 font-semibold">Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-inv2" class="divide-y divide-gray-200 text-gray-700">
                        @forelse($sensoresInvernadero2 as $sensor)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="py-3.5 px-3 font-bold text-cyan-700">{{ $sensor->peso_bascula_1 !== null ? $sensor->peso_bascula_1 . ' kg' : '--' }}</td>
                            <td class="py-3.5 px-3 font-bold text-cyan-700">{{ $sensor->peso_bascula_2 !== null ? $sensor->peso_bascula_2 . ' kg' : '--' }}</td>
                            <td class="py-3.5 px-3 font-medium text-amber-600">{{ $sensor->he390_valor !== null ? $sensor->he390_valor . ' %' : '--' }}</td>
                            <td class="py-3.5 px-3 font-semibold text-emerald-700">{{ $sensor->ph_valor !== null ? $sensor->ph_valor : '--' }}</td>
                            <td class="py-3.5 px-3 font-semibold text-amber-700">{{ $sensor->tds_valor !== null ? $sensor->tds_valor . ' mS/cm' : '--' }}</td>
                            <td class="py-3.5 px-3 font-semibold text-amber-700">{{ $sensor->tds_valor !== null ? ($sensor->tds_valor * 1000) . ' uS/cm' : '--' }}</td>
                            <td class="py-3.5 px-3 font-medium text-blue-700">{{ $sensor->temp_ambiente !== null ? $sensor->temp_ambiente . ' °C' : '--' }}</td>
                            <td class="py-3.5 px-3 font-medium text-blue-600">{{ $sensor->humedad_ambiente !== null ? $sensor->humedad_ambiente . ' %' : '--' }}</td>
                            <td class="py-3.5 px-3 font-medium text-purple-700">{{ $sensor->temp_infrarrojo !== null ? $sensor->temp_infrarrojo . ' °C' : '--' }}</td>
                            <td class="py-3.5 px-3">{{ $sensor->calidad_aire_eco2 !== null ? $sensor->calidad_aire_eco2 . ' ppm' : '--' }}</td>
                            <td class="py-3.5 px-3 text-gray-500 text-[11px]">{{ $sensor->created_at ? \Carbon\Carbon::parse($sensor->created_at)->format('d/m/Y H:i:s') : 'Ahora' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="py-8 text-center text-gray-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-2"></i>
                                <p>No hay registros para Invernadero 2.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ================= SECCIÓN 3: INVERNADERO 3 ================= -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-6">
            <div class="p-4 sm:p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gray-50/50">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="font-bold text-gray-800 text-sm sm:text-base flex items-center gap-2">
                        <i class="fa-solid fa-tower-broadcast text-emerald-600"></i> INVERNADERO 3
                    </h3>
                    <span class="text-[10px] sm:text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md font-medium">Ciclo Dinámico Inteligente</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('telemetria.historial', 'invernadero3') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-clock-rotate-left"></i> Ver Registros Anteriores
                    </a>
                    <div id="status-inv3">
                        <span class="bg-gray-100 text-gray-600 text-[11px] sm:text-xs font-semibold px-2.5 py-1 rounded-full">Verificando...</span>
                    </div>
                </div>
            </div>

            <!-- Formulario interactivo (NO SE PIERDE NADA AL ACTUALIZAR LA TABLA) -->
            <div class="bg-amber-50 border-b border-amber-200 p-3 sm:p-4">
                <form action="{{ route('telemetria.configurar') }}" method="POST" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                    @csrf
                    <input type="hidden" name="esp32_id" value="INVERNADERO 3">

                    <div class="grid grid-cols-2 xs:grid-cols-4 sm:flex sm:flex-wrap items-center gap-2 sm:gap-3">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1">
                            <span class="font-semibold text-gray-700 whitespace-nowrap"><i class="fa-solid fa-clock text-emerald-600"></i> Inicio:</span>
                            <input type="time" name="hora_inicio" value="{{ isset($configInv3) && $configInv3->hora_inicio ? \Carbon\Carbon::parse($configInv3->hora_inicio)->format('H:i') : '08:00' }}" class="bg-white border border-gray-300 rounded px-2 py-1 font-medium w-full sm:w-auto">
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1">
                            <span class="font-semibold text-gray-700 whitespace-nowrap">Fin:</span>
                            <input type="time" name="hora_fin" value="{{ isset($configInv3) && $configInv3->hora_fin ? \Carbon\Carbon::parse($configInv3->hora_fin)->format('H:i') : '18:00' }}" class="bg-white border border-gray-300 rounded px-2 py-1 font-medium w-full sm:w-auto">
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1">
                            <span class="font-semibold text-gray-700 whitespace-nowrap">Activo(s):</span>
                            <input type="number" name="intervalo_activo_sec" value="{{ isset($configInv3) ? ($configInv3->intervalo_activo_ms / 1000) : 3 }}" class="bg-white border border-gray-300 rounded px-2 py-1 w-full sm:w-16 text-center font-medium">
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1">
                            <span class="font-semibold text-gray-700 whitespace-nowrap">Descanso(m):</span>
                            <input type="number" name="intervalo_descanso_min" value="{{ isset($configInv3) ? ($configInv3->intervalo_descanso_ms / 60000) : 30 }}" class="bg-white border border-gray-300 rounded px-2 py-1 w-full sm:w-16 text-center font-medium">
                        </div>
                    </div>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 sm:py-1.5 rounded transition shadow-xs w-full sm:w-auto text-center whitespace-nowrap mt-1 sm:mt-0">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Guardar Cambios
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase tracking-wider border-b border-gray-200">
                            <th class="py-3 px-3 font-semibold text-cyan-700">Báscula 1</th>
                            <th class="py-3 px-3 font-semibold text-cyan-700">Báscula 2</th>
                            <th class="py-3 px-3 font-semibold text-amber-600">Suelo (HW-390)</th>
                            <th class="py-3 px-3 font-semibold text-emerald-700">pH</th>
                            <th class="py-3 px-3 font-semibold text-amber-700">EC (mS/cm)</th>
                            <th class="py-3 px-3 font-semibold text-amber-700">TDS (uS/cm)</th>
                            <th class="py-3 px-3 font-semibold text-purple-700">Temp. IR Objeto</th>
                            <th class="py-3 px-3 font-semibold">Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-inv3" class="divide-y divide-gray-200 text-gray-700">
                        @forelse($sensoresInvernadero3 as $sensor)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="py-3.5 px-3 font-bold text-cyan-700">{{ $sensor->peso_bascula_1 !== null ? $sensor->peso_bascula_1 . ' kg' : '--' }}</td>
                            <td class="py-3.5 px-3 font-bold text-cyan-700">{{ $sensor->peso_bascula_2 !== null ? $sensor->peso_bascula_2 . ' kg' : '--' }}</td>
                            <td class="py-3.5 px-3 font-medium text-amber-600">{{ $sensor->he390_valor !== null ? $sensor->he390_valor . ' %' : '--' }}</td>
                            <td class="py-3.5 px-3 font-semibold text-emerald-700">{{ $sensor->ph_valor !== null ? $sensor->ph_valor : '--' }}</td>
                            <td class="py-3.5 px-3 font-semibold text-amber-700">{{ $sensor->tds_valor !== null ? $sensor->tds_valor . ' mS/cm' : '--' }}</td>
                            <td class="py-3.5 px-3 font-semibold text-amber-700">{{ $sensor->tds_valor !== null ? ($sensor->tds_valor * 1000) . ' uS/cm' : '--' }}</td>
                            <td class="py-3.5 px-3 font-medium text-purple-700">{{ $sensor->temp_infrarrojo !== null ? $sensor->temp_infrarrojo . ' °C' : '--' }}</td>
                            <td class="py-3.5 px-3 text-gray-500 text-[11px]">{{ $sensor->created_at ? \Carbon\Carbon::parse($sensor->created_at)->format('d/m/Y H:i:s') : 'Ahora' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="py-8 text-center text-gray-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-2"></i>
                                <p>No hay registros para Invernadero 3.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
      <!-- ================= SECCIÓN 4: ELECTROVÁLVULAS (pH y TDS) ================= -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-6">
            <div class="p-4 sm:p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 bg-gray-50/50">
                <div class="flex flex-wrap items-center gap-2">
                    <h3 class="font-bold text-gray-800 text-sm sm:text-base flex items-center gap-2">
                        <i class="fa-solid fa-faucet-drip text-cyan-600"></i> ELECTROVÁLVULAS (pH y TDS)
                    </h3>
                    <span class="text-[10px] sm:text-xs bg-cyan-100 text-cyan-800 px-2 py-0.5 rounded-md font-medium">Control Nutricional / Riego</span>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('telemetria.historial', 'electrovalvulas') }}" class="bg-cyan-600 hover:bg-cyan-700 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition flex items-center gap-1.5 shadow-xs">
                        <i class="fa-solid fa-clock-rotate-left"></i> Ver Registros Anteriores
                    </a>
                    <div id="status-electrovalvulas">
                        <span class="bg-gray-100 text-gray-600 text-[11px] sm:text-xs font-semibold px-2.5 py-1 rounded-full">Verificando...</span>
                    </div>
                </div>
            </div>

            <!-- Formulario de configuración de tiempos para Electroválvulas -->
            <div class="bg-amber-50 border-b border-amber-200 p-3 sm:p-4">
                <form action="{{ route('telemetria.configurar') }}" method="POST" class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                    @csrf
                    <input type="hidden" name="esp32_id" value="ELECTROVALBULAS">

                    <div class="grid grid-cols-2 xs:grid-cols-4 sm:flex sm:flex-wrap items-center gap-2 sm:gap-3">
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1">
                            <span class="font-semibold text-gray-700 whitespace-nowrap"><i class="fa-solid fa-clock text-emerald-600"></i> Inicio:</span>
                            <input type="time" name="hora_inicio" value="{{ isset($configElectro) && $configElectro->hora_inicio ? \Carbon\Carbon::parse($configElectro->hora_inicio)->format('H:i') : '08:00' }}" class="bg-white border border-gray-300 rounded px-2 py-1 font-medium w-full sm:w-auto">
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1">
                            <span class="font-semibold text-gray-700 whitespace-nowrap">Fin:</span>
                            <input type="time" name="hora_fin" value="{{ isset($configElectro) && $configElectro->hora_fin ? \Carbon\Carbon::parse($configElectro->hora_fin)->format('H:i') : '18:00' }}" class="bg-white border border-gray-300 rounded px-2 py-1 font-medium w-full sm:w-auto">
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1">
                            <span class="font-semibold text-gray-700 whitespace-nowrap">Activo(s):</span>
                            <input type="number" name="intervalo_activo_sec" value="{{ isset($configElectro) ? ($configElectro->intervalo_activo_ms / 1000) : 3 }}" class="bg-white border border-gray-300 rounded px-2 py-1 w-full sm:w-16 text-center font-medium">
                        </div>

                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-1">
                            <span class="font-semibold text-gray-700 whitespace-nowrap">Descanso(m):</span>
                            <input type="number" name="intervalo_descanso_min" value="{{ isset($configElectro) ? ($configElectro->intervalo_descanso_ms / 60000) : 30 }}" class="bg-white border border-gray-300 rounded px-2 py-1 w-full sm:w-16 text-center font-medium">
                        </div>
                    </div>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-2 sm:py-1.5 rounded transition shadow-xs w-full sm:w-auto text-center whitespace-nowrap mt-1 sm:mt-0">
                        <i class="fa-solid fa-floppy-disk mr-1"></i> Guardar Cambios
                    </button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase tracking-wider border-b border-gray-200">
                            <th class="py-3 px-3 font-semibold text-emerald-700">pH</th>
                            <th class="py-3 px-3 font-semibold text-amber-700">EC (mS/cm)</th>
                            <th class="py-3 px-3 font-semibold text-amber-700">TDS (uS/cm)</th>
                            <th class="py-3 px-3 font-semibold">Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-electrovalvulas" class="divide-y divide-gray-200 text-gray-700">
                        @forelse($sensoresElectrovalvulas ?? [] as $sensor)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="py-3.5 px-3 font-semibold text-emerald-700">{{ $sensor->ph_valor !== null ? $sensor->ph_valor : '--' }}</td>
                            <td class="py-3.5 px-3 font-semibold text-amber-700">{{ $sensor->tds_valor !== null ? number_format($sensor->tds_valor, 2) . ' mS/cm' : '--' }}</td>
                            <td class="py-3.5 px-3 font-semibold text-amber-700">{{ $sensor->tds_valor !== null ? round($sensor->tds_valor * 1000) . ' uS/cm' : '--' }}</td>
                            <td class="py-3.5 px-3 text-gray-500 text-[11px]">{{ $sensor->created_at ? \Carbon\Carbon::parse($sensor->created_at)->format('d/m/Y H:i:s') : 'Ahora' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-gray-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-2"></i>
                                <p>No hay registros para Electroválvulas.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-3 sm:py-4 text-center text-xs sm:text-sm text-gray-500 w-full mt-auto">
        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.
    </footer>

    <!-- SCRIPT DE ACTUALIZACIÓN INDEPENDIENTE POR ID NUEVO -->
    <script>
        let ultimoIdInv1 = null;
        let ultimoIdInv2 = null;
        let ultimoIdInv3 = null;
        let ultimoIdElectro = null;

        function verificarYActualizarTelemetria() {
            fetch("{{ route('telemetria.envivo') }}")
                .then(response => response.json())
                .then(data => {
                    if (data.invernadero1.ultimo_id !== ultimoIdInv1) {
                        ultimoIdInv1 = data.invernadero1.ultimo_id;
                        actualizarTablaInv1(data.invernadero1.datos);
                    }
                    if (data.invernadero2.ultimo_id !== ultimoIdInv2) {
                        ultimoIdInv2 = data.invernadero2.ultimo_id;
                        actualizarTablaInv2(data.invernadero2.datos);
                    }
                    if (data.invernadero3.ultimo_id !== ultimoIdInv3) {
                        ultimoIdInv3 = data.invernadero3.ultimo_id;
                        actualizarTablaInv3(data.invernadero3.datos);
                    }
                    if (data.electrovalvulas && data.electrovalvulas.ultimo_id !== ultimoIdElectro) {
                        ultimoIdElectro = data.electrovalvulas.ultimo_id;
                        actualizarTablaElectrovalvulas(data.electrovalvulas.datos);
                    }
                })
                .catch(error => console.error('Error al verificar datos en vivo:', error));
        }

        function formatearFecha(fechaStr) {
            if (!fechaStr) return '--';
            const fecha = new Date(fechaStr);
            return fecha.toLocaleString('es-MX', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        }

        function actualizarTablaInv1(registros) {
            const tbody = document.getElementById('tabla-inv1');
            const statusDiv = document.getElementById('status-inv1');
            if (registros.length === 0) return;

            const ultimo = new Date(registros[0].created_at);
            const diffSegundos = (new Date() - ultimo) / 1000;
            if (diffSegundos <= 60) {
                statusDiv.innerHTML = `<span class="bg-cyan-100 text-cyan-800 text-[11px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span> Conectado / En vivo</span>`;
            } else {
                statusDiv.innerHTML = `<span class="bg-red-100 text-red-800 text-[11px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> Desconectado</span>`;
            }

            let html = '';
            registros.forEach(s => {
                let ecVal = s.tds_valor !== null ? parseFloat(s.tds_valor) : 0;
                let tdsUs = s.tds_valor !== null ? (ecVal * 1000).toFixed(0) : '--';
                html += `<tr class="hover:bg-gray-50/80 transition">
                    <td class="py-3.5 px-3 font-bold text-cyan-700">${s.peso_bascula_1 !== null ? s.peso_bascula_1 + ' kg' : '--'}</td>
                    <td class="py-3.5 px-3 font-bold text-cyan-700">${s.peso_bascula_2 !== null ? s.peso_bascula_2 + ' kg' : '--'}</td>
                    <td class="py-3.5 px-3 font-semibold text-emerald-700">${s.ph_valor !== null ? s.ph_valor : '--'}</td>
                    <td class="py-3.5 px-3 font-semibold text-amber-700">${s.tds_valor !== null ? s.tds_valor + ' mS/cm' : '--'}</td>
                    <td class="py-3.5 px-3 font-semibold text-amber-700">${tdsUs !== '--' ? tdsUs + ' uS/cm' : '--'}</td>
                    <td class="py-3.5 px-3 font-medium text-blue-700">${s.temp_ambiente !== null ? s.temp_ambiente + ' °C' : '--'}</td>
                    <td class="py-3.5 px-3 font-medium text-blue-600">${s.humedad_ambiente !== null ? s.humedad_ambiente + ' %' : '--'}</td>
                    <td class="py-3.5 px-3 font-medium text-purple-700">${s.temp_infrarrojo !== null ? s.temp_infrarrojo + ' °C' : '--'}</td>
                    <td class="py-3.5 px-3 font-medium text-amber-600">${s.he390_valor !== null ? s.he390_valor + ' %' : '--'}</td>
                    <td class="py-3.5 px-3 font-medium text-green-700">${s.temp_ds18b20 !== null ? s.temp_ds18b20 + ' °C' : '--'}</td>
                    <td class="py-3.5 px-3">${s.calidad_aire_eco2 !== null ? s.calidad_aire_eco2 + ' ppm' : '--'}</td>
                    <td class="py-3.5 px-3">${s.calidad_aire_tvoc !== null ? s.calidad_aire_tvoc + ' ppb' : '--'}</td>
                    <td class="py-3.5 px-3">${s.luz_lux !== null ? s.luz_lux + ' lx' : '--'}</td>
                    <td class="py-3.5 px-3 text-gray-500 text-[11px]">${formatearFecha(s.created_at)}</td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }

        function actualizarTablaInv2(registros) {
            const tbody = document.getElementById('tabla-inv2');
            const statusDiv = document.getElementById('status-inv2');
            if (registros.length === 0) return;

            const ultimo = new Date(registros[0].created_at);
            const diffSegundos = (new Date() - ultimo) / 1000;
            if (diffSegundos <= 60) {
                statusDiv.innerHTML = `<span class="bg-cyan-100 text-cyan-800 text-[11px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span> Conectado / En vivo</span>`;
            } else {
                statusDiv.innerHTML = `<span class="bg-red-100 text-red-800 text-[11px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> Desconectado</span>`;
            }

            let html = '';
            registros.forEach(s => {
                let ecVal = s.tds_valor !== null ? parseFloat(s.tds_valor) : 0;
                let tdsUs = s.tds_valor !== null ? (ecVal * 1000).toFixed(0) : '--';
                html += `<tr class="hover:bg-gray-50/80 transition">
                    <td class="py-3.5 px-3 font-bold text-cyan-700">${s.peso_bascula_1 !== null ? s.peso_bascula_1 + ' kg' : '--'}</td>
                    <td class="py-3.5 px-3 font-bold text-cyan-700">${s.peso_bascula_2 !== null ? s.peso_bascula_2 + ' kg' : '--'}</td>
                    <td class="py-3.5 px-3 font-medium text-amber-600">${s.he390_valor !== null ? s.he390_valor + ' %' : '--'}</td>
                    <td class="py-3.5 px-3 font-semibold text-emerald-700">${s.ph_valor !== null ? s.ph_valor : '--'}</td>
                    <td class="py-3.5 px-3 font-semibold text-amber-700">${s.tds_valor !== null ? s.tds_valor + ' mS/cm' : '--'}</td>
                    <td class="py-3.5 px-3 font-semibold text-amber-700">${tdsUs !== '--' ? tdsUs + ' uS/cm' : '--'}</td>
                    <td class="py-3.5 px-3 font-medium text-blue-700">${s.temp_ambiente !== null ? s.temp_ambiente + ' °C' : '--'}</td>
                    <td class="py-3.5 px-3 font-medium text-blue-600">${s.humedad_ambiente !== null ? s.humedad_ambiente + ' %' : '--'}</td>
                    <td class="py-3.5 px-3 font-medium text-purple-700">${s.temp_infrarrojo !== null ? s.temp_infrarrojo + ' °C' : '--'}</td>
                    <td class="py-3.5 px-3">${s.calidad_aire_eco2 !== null ? s.calidad_aire_eco2 + ' ppm' : '--'}</td>
                    <td class="py-3.5 px-3 text-gray-500 text-[11px]">${formatearFecha(s.created_at)}</td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }

        function actualizarTablaInv3(registros) {
            const tbody = document.getElementById('tabla-inv3');
            const statusDiv = document.getElementById('status-inv3');
            if (registros.length === 0) return;

            const ultimo = new Date(registros[0].created_at);
            const diffSegundos = (new Date() - ultimo) / 1000;
            if (diffSegundos <= 60) {
                statusDiv.innerHTML = `<span class="bg-cyan-100 text-cyan-800 text-[11px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span> Conectado / En vivo</span>`;
            } else {
                statusDiv.innerHTML = `<span class="bg-red-100 text-red-800 text-[11px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> Desconectado</span>`;
            }

            let html = '';
            registros.forEach(s => {
                let ecVal = s.tds_valor !== null ? parseFloat(s.tds_valor) : 0;
                let tdsUs = s.tds_valor !== null ? (ecVal * 1000).toFixed(0) : '--';
                html += `<tr class="hover:bg-gray-50/80 transition">
                    <td class="py-3.5 px-3 font-bold text-cyan-700">${s.peso_bascula_1 !== null ? s.peso_bascula_1 + ' kg' : '--'}</td>
                    <td class="py-3.5 px-3 font-bold text-cyan-700">${s.peso_bascula_2 !== null ? s.peso_bascula_2 + ' kg' : '--'}</td>
                    <td class="py-3.5 px-3 font-medium text-amber-600">${s.he390_valor !== null ? s.he390_valor + ' %' : '--'}</td>
                    <td class="py-3.5 px-3 font-semibold text-emerald-700">${s.ph_valor !== null ? s.ph_valor : '--'}</td>
                    <td class="py-3.5 px-3 font-semibold text-amber-700">${s.tds_valor !== null ? s.tds_valor + ' mS/cm' : '--'}</td>
                    <td class="py-3.5 px-3 font-semibold text-amber-700">${tdsUs !== '--' ? tdsUs + ' uS/cm' : '--'}</td>
                    <td class="py-3.5 px-3 font-medium text-purple-700">${s.temp_infrarrojo !== null ? s.temp_infrarrojo + ' °C' : '--'}</td>
                    <td class="py-3.5 px-3 text-gray-500 text-[11px]">${formatearFecha(s.created_at)}</td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }

       function actualizarTablaElectrovalvulas(registros) {
            const tbody = document.getElementById('tabla-electrovalvulas');
            const statusDiv = document.getElementById('status-electrovalvulas');
            if (!tbody || registros.length === 0) return;

            const ultimo = new Date(registros[0].created_at);
            const diffSegundos = (new Date() - ultimo) / 1000;
            if (diffSegundos <= 60) {
                statusDiv.innerHTML = `<span class="bg-cyan-100 text-cyan-800 text-[11px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span> Conectado / En vivo</span>`;
            } else {
                statusDiv.innerHTML = `<span class="bg-red-100 text-red-800 text-[11px] sm:text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-500"></span> Desconectado</span>`;
            }

            let html = '';
            registros.forEach(s => {
                let ecVal = s.tds_valor !== null ? parseFloat(s.tds_valor) : 0;
                let tdsVal = s.tds_valor !== null ? ecVal.toFixed(2) : '--';
                let tdsUs = s.tds_valor !== null ? Math.round(ecVal * 1000) : '--';
                
                html += `<tr class="hover:bg-gray-50/80 transition">
                    <td class="py-3.5 px-3 font-semibold text-emerald-700">${s.ph_valor !== null ? s.ph_valor : '--'}</td>
                    <td class="py-3.5 px-3 font-semibold text-amber-700">${tdsVal !== '--' ? tdsVal + ' mS/cm' : '--'}</td>
                    <td class="py-3.5 px-3 font-semibold text-amber-700">${tdsUs !== '--' ? tdsUs + ' uS/cm' : '--'}</td>
                    <td class="py-3.5 px-3 text-gray-500 text-[11px]">${formatearFecha(s.created_at)}</td>
                </tr>`;
            });
            tbody.innerHTML = html;
        }

        // Consultar cada 5 segundos de forma independiente
        setInterval(verificarYActualizarTelemetria, 5000);
    </script>

</body>

</html>