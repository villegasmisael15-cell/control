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
    <main class="max-w-[95%] mx-auto px-4 py-8 w-full flex-grow space-y-10">

        <!-- Cabecera de la sección -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                    <i class="fa-solid fa-microchip text-cyan-600"></i> Panel de Telemetría Independiente
                </h1>
                <p class="text-gray-600 text-sm mt-1">Monitoreo separado por zonas de dispositivos ESP32.</p>
            </div>
        </div>

        <!-- ================= SECCIÓN 1: INVERNADERO 1 ================= -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-tower-broadcast text-emerald-600"></i> INVERNADERO 1
                </h3>

                @php
                @php
                $ultimoInv = \App\Models\Sensor::whereIn('esp32_id', ['INVERNADERO 1', 'INVERNADERO_1'])->latest()->first();
                // Si hay un registro reciente, lo marcamos en línea (ampliamos el margen a 10 minutos o validamos que exista)
                $conectadoInv = $ultimoInv && $ultimoInv->created_at;
                @endphp

                @if($conectadoInv)
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
                            <th class="py-3 px-3 font-semibold text-cyan-700">Báscula 1</th>
                            <th class="py-3 px-3 font-semibold text-cyan-700">Báscula 2</th>
                            <th class="py-3 px-3 font-semibold text-emerald-700">pH</th>
                            <th class="py-3 px-3 font-semibold text-amber-700">TDS / EC</th>
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
                    <tbody class="divide-y divide-gray-200 text-gray-700">
                        @forelse($sensoresInvernadero as $sensor)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="py-3.5 px-3 font-bold text-cyan-700">{{ $sensor->peso_bascula_1 !== null ? $sensor->peso_bascula_1 . ' kg' : '--' }}</td>
                            <td class="py-3.5 px-3 font-bold text-cyan-700">{{ $sensor->peso_bascula_2 !== null ? $sensor->peso_bascula_2 . ' kg' : '--' }}</td>
                            <td class="py-3.5 px-3 font-semibold text-emerald-700">{{ $sensor->ph_valor !== null ? $sensor->ph_valor : '--' }}</td>
                            <td class="py-3.5 px-3 font-semibold text-amber-700">{{ $sensor->tds_valor !== null ? $sensor->tds_valor . ' mS/cm' : '--' }}</td>
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
                            <td colspan="13" class="py-8 text-center text-gray-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-2"></i>
                                <p>No hay registros para Invernadero 1.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($sensoresInvernadero) && method_exists($sensoresInvernadero, 'links'))
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $sensoresInvernadero->appends(['elec_page' => request('elec_page')])->links() }}
            </div>
            @endif
        </div>

        <!-- ================= SECCIÓN 2: ENTRADA ELECTROVALVULAS ================= -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid id-card text-emerald-600"></i> ENTRADA ELECTROVALVULAS
                </h3>

                @php
                $ultimoElec = \App\Models\Sensor::where('esp32_id', 'ENTRADA ELECTROVALVULAS')->latest()->first();
                $conectadoElec = false;
                if ($ultimoElec && $ultimoElec->created_at) {
                $conectadoElec = \Carbon\Carbon::parse($ultimoElec->created_at)->diffInSeconds(now()) <= 60;
                    }
                    @endphp

                    @if($conectadoElec)
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
                            <th class="py-3 px-6 font-semibold text-emerald-700">pH</th>
                            <th class="py-3 px-6 font-semibold text-amber-700">TDS / Conductividad</th>
                            <th class="py-3 px-6 font-semibold">Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700">
                        @forelse($sensoresElectrovalvulas as $sensor)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="py-4 px-6 font-bold text-emerald-700 text-sm">{{ $sensor->ph_valor !== null ? $sensor->ph_valor : '--' }}</td>
                            <td class="py-4 px-6 font-bold text-amber-700 text-sm">{{ $sensor->tds_valor !== null ? $sensor->tds_valor . ' mS/cm' : '--' }}</td>
                            <td class="py-4 px-6 text-gray-500 text-xs">{{ $sensor->created_at ? \Carbon\Carbon::parse($sensor->created_at)->format('d/m/Y H:i:s') : 'Ahora' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="py-8 text-center text-gray-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-2"></i>
                                <p>No hay registros para Entrada Electroválvulas.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($sensoresElectrovalvulas) && method_exists($sensoresElectrovalvulas, 'links'))
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $sensoresElectrovalvulas->appends(['inv_page' => request('inv_page')])->links() }}
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