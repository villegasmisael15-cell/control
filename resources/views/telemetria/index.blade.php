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
                <span class="bg-emerald-700/80 px-2.5 py-1 rounded-md flex items-center gap-1 max-w-[120px] sm:max-w-none truncate" title="{{ auth()->user()->name ?? 'Usuario' }}">
                    <i class="fa-solid fa-user text-[10px]"></i>
                    <span class="truncate">{{ auth()->user()->name ?? 'Usuario' }}</span>
                </span>
                <a href="{{ route('dashboard') }}" class="bg-emerald-700 hover:bg-emerald-800 px-2.5 sm:px-3.5 py-1.5 rounded-md transition flex items-center gap-1 font-medium shadow-2xs whitespace-nowrap">
                    <i class="fa-solid fa-circle-chevron-left text-[10px]"></i>
                    <span>Volver al Panel</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Contenido Principal -->
    <main class="max-w-[95%] mx-auto px-4 py-8 w-full flex-grow space-y-10">
        
        <!-- Cabecera general -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-network-wired text-cyan-600"></i> Panel General de Telemetría
                </h1>
                <p class="text-gray-600 text-sm mt-1">Monitoreo independiente por zonas e infraestructura del cultivo.</p>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 1. SECCIÓN: ENTRADA ELECTROVÁLVULAS -->
        <!-- ========================================== -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="p-5 border-b border-gray-200 flex justify-between items-center bg-blue-50/40">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-faucet-drip text-blue-600 text-lg"></i>
                    <h3 class="font-bold text-gray-800 text-base">Entrada Electroválvulas (Agua)</h3>
                </div>
                @php
                    $ultimoElectro = \App\Models\Sensor::where('esp32_id', 'ENTRADA ELECTROVALVULAS')->latest()->first();
                    $conectadoElectro = $ultimoElectro && $ultimoElectro->created_at && \Carbon\Carbon::parse($ultimoElectro->created_at)->diffInSeconds(now()) <= 60;
                @endphp
                @if($conectadoElectro)
                    <span class="bg-cyan-100 text-cyan-800 text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span> En línea
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
                            <th class="py-3 px-4 font-semibold text-emerald-700">pH del Agua</th>
                            <th class="py-3 px-4 font-semibold text-blue-700">Conductividad (TDS / EC)</th>
                            <th class="py-3 px-4 font-semibold">Última Actualización</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700">
                        @forelse($registrosElectrovolvulas ?? [] as $reg)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-4 font-bold text-emerald-700 text-sm">{{ $reg->ph_valor !== null ? $reg->ph_valor : '--' }}</td>
                            <td class="py-3 px-4 font-bold text-blue-700 text-sm">{{ $reg->tds_valor !== null ? $reg->tds_valor . ' mS/cm' : '--' }}</td>
                            <td class="py-3 px-4 text-gray-500">{{ $reg->created_at ? \Carbon\Carbon::parse($reg->created_at)->format('d/m/Y H:i:s') : '--' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-6 text-center text-gray-400">No hay registros de electroválvulas todavía.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>


        <!-- ========================================== -->
        <!-- 2. SECCIÓN: INVERNADERO 1 -->
        <!-- ========================================== -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="p-5 border-b border-gray-200 flex justify-between items-center bg-emerald-50/40">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-seedling text-emerald-600 text-lg"></i>
                    <h3 class="font-bold text-gray-800 text-base">Invernadero 1 (Sensores y Básculas 1 y 2)</h3>
                </div>
                @php
                    $ultimoInv1 = \App\Models\Sensor::where('esp32_id', 'INVERNADERO 1')->latest()->first();
                    $conectadoInv1 = $ultimoInv1 && $ultimoInv1->created_at && \Carbon\Carbon::parse($ultimoInv1->created_at)->diffInSeconds(now()) <= 60;
                @endphp
                @if($conectadoInv1)
                    <span class="bg-cyan-100 text-cyan-800 text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span> En línea
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
                            <th class="py-3 px-4 font-semibold text-cyan-700">Báscula 1</th>
                            <th class="py-3 px-4 font-semibold text-cyan-700">Báscula 2</th>
                            <th class="py-3 px-4 font-semibold">Temp. Amb (°C)</th>
                            <th class="py-3 px-4 font-semibold">Hum. Amb (%)</th>
                            <th class="py-3 px-4 font-semibold">eCO2 / TVOC</th>
                            <th class="py-3 px-4 font-semibold">Luz (Lux)</th>
                            <th class="py-3 px-4 font-semibold">Temp. Infra</th>
                            <th class="py-3 px-4 font-semibold">Humedad Suelo</th>
                            <th class="py-3 px-4 font-semibold text-emerald-700">pH / TDS</th>
                            <th class="py-3 px-4 font-semibold">Temp. Suelo (DS18B20)</th>
                            <th class="py-3 px-4 font-semibold">Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700">
                        @forelse($registrosInvernadero1 ?? [] as $reg)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3.5 px-4 font-bold text-cyan-700">{{ $reg->peso_bascula_1 !== null ? number_format($reg->peso_bascula_1, 2) . ' g' : ($reg->peso_hx711 !== null ? number_format($reg->peso_hx711, 2) . ' g' : '--') }}</td>
                            <td class="py-3.5 px-4 font-bold text-cyan-700">{{ $reg->peso_bascula_2 !== null ? number_format($reg->peso_bascula_2, 2) . ' g' : '--' }}</td>
                            <td class="py-3.5 px-4">{{ $reg->temp_ambiente !== null ? $reg->temp_ambiente . ' °C' : '--' }}</td>
                            <td class="py-3.5 px-4">{{ $reg->humedad_ambiente !== null ? $reg->humedad_ambiente . ' %' : '--' }}</td>
                            <td class="py-3.5 px-4">{{ $reg->calidad_aire_eco2 !== null ? $reg->calidad_aire_eco2 . ' ppm' : '--' }}</td>
                            <td class="py-3.5 px-4">{{ $reg->luz_lux !== null ? $reg->luz_lux . ' lx' : '--' }}</td>
                            <td class="py-3.5 px-4">{{ $reg->temp_infrarrojo !== null ? $reg->temp_infrarrojo . ' °C' : '--' }}</td>
                            <td class="py-3.5 px-4">{{ $reg->he390_valor !== null ? $reg->he390_valor . ' %' : '--' }}</td>
                            <td class="py-3.5 px-4 font-semibold text-emerald-700">pH: {{ $reg->ph_valor ?? '--' }} | TDS: {{ $reg->tds_valor ?? '--' }}</td>
                            <td class="py-3.5 px-4">{{ $reg->temp_ds18b20 !== null ? $reg->temp_ds18b20 . ' °C' : '--' }}</td>
                            <td class="py-3.5 px-4 text-gray-500">{{ $reg->created_at ? \Carbon\Carbon::parse($reg->created_at)->format('d/m/Y H:i:s') : '--' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="11" class="py-6 text-center text-gray-400">No hay registros del Invernadero 1 todavía.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- 3. SECCIÓN: INVERNADERO 2 Y 3 (Próximamente) -->
        <!-- ========================================== -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden p-6 text-center">
            <div class="flex flex-col items-center justify-center py-6">
                <i class="fa-solid fa-screwdriver-wrench text-amber-500 text-3xl mb-3"></i>
                <h3 class="font-bold text-gray-800 text-base">Invernadero 2 y 3</h3>
                <p class="text-gray-500 text-sm mt-1">Este módulo se encuentra actualmente en fase de armado y configuración de hardware (incluirá básculas 3 y 4).</p>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500 w-full mt-auto">
        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.
    </footer>

</body>

</html>