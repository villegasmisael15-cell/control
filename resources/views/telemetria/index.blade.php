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
        <div class="max-w-[95%] mx-auto px-3 sm:px-4 h-14 sm:h-16 flex items-center justify-between gap-2">
            <div class="flex items-center min-w-0">
                <i class="fa-solid fa-leaf text-lg sm:text-2xl mr-1.5 sm:mr-2 text-emerald-200"></i>
                <span class="font-bold text-sm sm:text-xl tracking-wider truncate">SISTEMA CONTROL</span>
            </div>

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

    <!-- Contenido Principal -->
    <main class="max-w-[95%] mx-auto px-4 py-8 w-full flex-grow">
        
        <!-- Cabecera de la sección -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-microchip text-cyan-600"></i> Telemetría y Sensores del Invernadero
                </h1>
                <p class="text-gray-600 text-sm mt-1">Monitoreo de variables ambientales, calidad de aire, agua y peso.</p>
            </div>
        </div>

        <!-- Tabla de Datos Recibidos -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-800 text-base">Registros Históricos</h3>

                @php
                    $ultimoRegistro = \App\Models\Sensor::latest()->first();
                    $estaConectado = false;
                    if ($ultimoRegistro && $ultimoRegistro->created_at) {
                        $tiempoTranscurrido = \Carbon\Carbon::parse($ultimoRegistro->created_at)->diffInSeconds(now());
                        $estaConectado = $tiempoTranscurrido <= 60;
                    }
                @endphp

                @if($estaConectado)
                    <span class="bg-cyan-100 text-cyan-800 text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span> Conectado / En vivo
                    </span>
                @else
                    <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span> Desconectado
                    </span>
                @endif
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase tracking-wider border-b border-gray-200">
                            <th class="py-3 px-4 font-semibold">Dispositivo</th>
                            <th class="py-3 px-4 font-semibold text-cyan-700">Peso (kg)</th>
                            <th class="py-3 px-4 font-semibold">Temp. Amb (°C)</th>
                            <th class="py-3 px-4 font-semibold">Hum. Amb (%)</th>
                            <th class="py-3 px-4 font-semibold">eCO2 (ppm)</th>
                            <th class="py-3 px-4 font-semibold">TVOC (ppb)</th>
                            <th class="py-3 px-4 font-semibold">Luz (Lux)</th>
                            <th class="py-3 px-4 font-semibold">Temp. Infra (°C)</th>
                            <th class="py-3 px-4 font-semibold">TDS</th>
                            <th class="py-3 px-4 font-semibold">HE390</th>
                            <th class="py-3 px-4 font-semibold text-emerald-700">pH</th>
                            <th class="py-3 px-4 font-semibold">Temp. Suelo (°C)</th>
                            <th class="py-3 px-4 font-semibold">Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700">
                        @forelse($sensores as $sensor)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="py-3.5 px-4 font-medium text-gray-900 flex items-center gap-1.5">
                                <i class="fa-solid fa-wifi text-emerald-600 text-xs"></i> {{ $sensor->esp32_id ?? 'ESP32_1' }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-cyan-700 text-sm">
                                {{ $sensor->peso_hx711 !== null ? number_format($sensor->peso_hx711, 2) : '0.00' }} kg
                            </td>
                            <td class="py-3.5 px-4">{{ $sensor->temp_ambiente !== null ? $sensor->temp_ambiente . ' °C' : '--' }}</td>
                            <td class="py-3.5 px-4">{{ $sensor->humedad_ambiente !== null ? $sensor->humedad_ambiente . ' %' : '--' }}</td>
                            <td class="py-3.5 px-4">{{ $sensor->calidad_aire_eco2 !== null ? $sensor->calidad_aire_eco2 : '--' }}</td>
                            <td class="py-3.5 px-4">{{ $sensor->calidad_aire_tvoc !== null ? $sensor->calidad_aire_tvoc : '--' }}</td>
                            <td class="py-3.5 px-4">{{ $sensor->luz_lux !== null ? $sensor->luz_lux . ' lx' : '--' }}</td>
                            <td class="py-3.5 px-4">{{ $sensor->temp_infrarrojo !== null ? $sensor->temp_infrarrojo . ' °C' : '--' }}</td>
                            <td class="py-3.5 px-4">{{ $sensor->tds_valor !== null ? $sensor->tds_valor : '--' }}</td>
                            <td class="py-3.5 px-4">{{ $sensor->he390_valor !== null ? $sensor->he390_valor : '--' }}</td>
                            <td class="py-3.5 px-4 font-semibold text-emerald-700">{{ $sensor->ph_valor !== null ? $sensor->ph_valor : '--' }}</td>
                            <td class="py-3.5 px-4">{{ $sensor->temp_ds18b20 !== null ? $sensor->temp_ds18b20 . ' °C' : '--' }}</td>
                            <td class="py-3.5 px-4 text-gray-500 text-[11px]">
                                {{ $sensor->created_at ? \Carbon\Carbon::parse($sensor->created_at)->format('d/m/Y H:i:s') : 'Ahora' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="13" class="py-8 text-center text-gray-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-2"></i>
                                <p>No hay registros de sensores guardados todavía.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            @if(isset($sensores) && method_exists($sensores, 'links'))
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $sensores->links() }}
            </div>
            @endif
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500 w-full mt-auto">
        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.
    </footer>

</body>

</html>