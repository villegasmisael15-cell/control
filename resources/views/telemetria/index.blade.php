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
                    <i class="fa-solid fa-microchip text-cyan-600"></i> Panel de Telemetría e Intervalos Inteligentes
                </h1>
                <p class="text-gray-600 text-sm mt-1">Monitoreo independiente y gestión de ciclos de descanso por zona ESP32.</p>
            </div>
        </div>

        <!-- ================= SECCIÓN 1: INVERNADERO 1 ================= -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-tower-broadcast text-emerald-600"></i> INVERNADERO 1
                </h3>

                @php
                $ultimoInv = \App\Models\Sensor::whereIn('esp32_id', ['INVERNADERO 1', 'INVERNADERO_1'])->latest()->first();
                $conectadoInv = false;
                if ($ultimoInv && $ultimoInv->created_at) {
                    $conectadoInv = \Carbon\Carbon::parse($ultimoInv->created_at)->diffInSeconds(now()) <= 60;
                }
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
                {{ $sensoresInvernadero->appends(['inv_page' => request('inv_page')])->links() }}
            </div>
            @endif
        </div>

        <!-- ================= SECCIÓN 2: INVERNADERO 2 ================= -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden mb-8">
            <div class="p-6 border-b border-gray-200 flex justify-between items-center bg-gray-50/50">
                <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                    <i class="fa-solid fa-tower-broadcast text-emerald-600"></i> INVERNADERO 2
                </h3>

                @php
                $ultimoInv2 = \App\Models\Sensor::whereIn('esp32_id', ['INVERNADERO 2', 'INVERNADERO_2'])->latest()->first();
                $conectadoInv2 = false;
                if ($ultimoInv2 && $ultimoInv2->created_at) {
                    $conectadoInv2 = \Carbon\Carbon::parse($ultimoInv2->created_at)->diffInSeconds(now()) <= 60;
                }
                @endphp

                @if($conectadoInv2)
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
                            <th class="py-3 px-3 font-semibold">eCO2</th>
                            <th class="py-3 px-3 font-semibold">Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700">
                        @forelse($sensoresInvernadero2 as $sensor)
                        <tr class="hover:bg-gray-50/80 transition">
                            <td class="py-3.5 px-3 font-bold text-cyan-700">{{ $sensor->peso_bascula_1 !== null ? $sensor->peso_bascula_1 . ' kg' : '--' }}</td>
                            <td class="py-3.5 px-3 font-bold text-cyan-700">{{ $sensor->peso_bascula_2 !== null ? $sensor->peso_bascula_2 . ' kg' : '--' }}</td>
                            <td class="py-3.5 px-3 font-semibold text-emerald-700">{{ $sensor->ph_valor !== null ? $sensor->ph_valor : '--' }}</td>
                            <td class="py-3.5 px-3 font-semibold text-amber-700">{{ $sensor->tds_valor !== null ? $sensor->tds_valor . ' mS/cm' : '--' }}</td>
                            <td class="py-3.5 px-3 font-medium text-blue-700">{{ $sensor->temp_ambiente !== null ? $sensor->temp_ambiente . ' °C' : '--' }}</td>
                            <td class="py-3.5 px-3 font-medium text-blue-600">{{ $sensor->humedad_ambiente !== null ? $sensor->humedad_ambiente . ' %' : '--' }}</td>
                            <td class="py-3.5 px-3 font-medium text-purple-700">{{ $sensor->temp_infrarrojo !== null ? $sensor->temp_infrarrojo . ' °C' : '--' }}</td>
                            <td class="py-3.5 px-3 font-medium text-amber-600">{{ $sensor->he390_valor !== null ? $sensor->he390_valor . ' %' : '--' }}</td>
                            <td class="py-3.5 px-3">{{ $sensor->calidad_aire_eco2 !== null ? $sensor->calidad_aire_eco2 . ' ppm' : '--' }}</td>
                            <td class="py-3.5 px-3 text-gray-500 text-[11px]">{{ $sensor->created_at ? \Carbon\Carbon::parse($sensor->created_at)->format('d/m/Y H:i:s') : 'Ahora' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="py-8 text-center text-gray-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-2"></i>
                                <p>No hay registros para Invernadero 2.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(isset($sensoresInvernadero2) && method_exists($sensoresInvernadero2, 'links'))
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $sensoresInvernadero2->appends(['inv2_page' => request('inv2_page')])->links() }}
            </div>
            @endif
        </div>

        <!-- ================= SECCIÓN 3: INVERNADERO 3 (CON FORMULARIO DE HORARIOS DINÁMICOS) ================= -->
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="p-6 border-b border-gray-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/50">
                <div class="flex items-center gap-2">
                    <h3 class="font-bold text-gray-800 text-base flex items-center gap-2">
                        <i class="fa-solid fa-tower-broadcast text-emerald-600"></i> INVERNADERO 3
                    </h3>
                    <span class="text-xs bg-emerald-100 text-emerald-800 px-2 py-0.5 rounded-md font-medium">Ciclo Dinámico Inteligente</span>
                </div>

                <div class="flex items-center gap-3">
                    @php
                    $ultimoInv3 = \App\Models\Sensor::whereIn('esp32_id', ['INVERNADERO 3', 'INVERNADERO_3'])->latest()->first();
                    $conectadoInv3 = false;
                    if ($ultimoInv3 && $ultimoInv3->created_at) {
                        $conectadoInv3 = \Carbon\Carbon::parse($ultimoInv3->created_at)->diffInSeconds(now()) <= 60;
                    }
                    @endphp

                    @if($conectadoInv3)
                    <span class="bg-cyan-100 text-cyan-800 text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-cyan-500 animate-pulse"></span> Conectado / En vivo
                    </span>
                    @else
                    <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-1 rounded-full flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span> Desconectado
                    </span>
                    @endif
                </div>
            </div>

            <!-- Formulario interactivo para cambiar horarios desde la vista -->
            <div class="bg-amber-50 border-b border-amber-200 p-4">
                <form action="{{ route('telemetria.configurar') }}" method="POST" class="flex flex-wrap items-center justify-between gap-4 text-xs">
                    @csrf
                    <input type="hidden" name="esp32_id" value="INVERNADERO 3">
                    
                    <div class="flex flex-wrap items-center gap-3">
                        <div class="flex items-center gap-1.5">
                            <span class="font-semibold text-gray-700"><i class="fa-solid fa-clock text-emerald-600"></i> Inicio Activo:</span>
                            <input type="time" name="hora_inicio" value="{{ isset($configInv3) && $configInv3->hora_inicio ? \Carbon\Carbon::parse($configInv3->hora_inicio)->format('H:i') : '08:00' }}" class="bg-white border border-gray-300 rounded px-2 py-1 font-medium">
                        </div>

                        <div class="flex items-center gap-1.5">
                            <span class="font-semibold text-gray-700">Fin Activo:</span>
                            <input type="time" name="hora_fin" value="{{ isset($configInv3) && $configInv3->hora_fin ? \Carbon\Carbon::parse($configInv3->hora_fin)->format('H:i') : '18:00' }}" class="bg-white border border-gray-300 rounded px-2 py-1 font-medium">
                        </div>

                        <div class="flex items-center gap-1.5">
                            <span class="font-semibold text-gray-700">Activo (seg):</span>
                            <input type="number" name="intervalo_activo_sec" value="{{ isset($configInv3) ? ($configInv3->intervalo_activo_ms / 1000) : 3 }}" class="bg-white border border-gray-300 rounded px-2 py-1 w-16 text-center font-medium">
                        </div>

                        <div class="flex items-center gap-1.5">
                            <span class="font-semibold text-gray-700">Descanso (min):</span>
                            <input type="number" name="intervalo_descanso_min" value="{{ isset($configInv3) ? ($configInv3->intervalo_descanso_ms / 60000) : 30 }}" class="bg-white border border-gray-300 rounded px-2 py-1 w-16 text-center font-medium">
                        </div>
                    </div>

                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-semibold px-4 py-1.5 rounded transition shadow-2xs whitespace-nowrap">
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
                    <tbody class="divide-y divide-gray-200 text-gray-700">
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

            @if(isset($sensoresInvernadero3) && method_exists($sensoresInvernadero3, 'links'))
            <div class="p-4 border-t border-gray-200 bg-gray-50">
                {{ $sensoresInvernadero3->appends(['inv3_page' => request('inv3_page')])->links() }}
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