<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Telemetría - Sistema Control</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

    <!-- Navbar -->
    <nav class="bg-emerald-600 text-white shadow-md">
        <div class="max-w-[95%] mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center">
                <i class="fa-solid fa-leaf text-2xl mr-2 text-emerald-200"></i>
                <span class="font-bold text-xl tracking-wider">SISTEMA CONTROL - HISTORIAL</span>
            </div>
            <a href="{{ route('telemetria.index') }}" class="bg-emerald-700 hover:bg-emerald-800 px-3.5 py-1.5 rounded-md transition flex items-center gap-1 font-medium text-xs">
                <i class="fa-solid fa-circle-chevron-left text-[10px]"></i> Volver al Panel en Vivo
            </a>
        </div>
    </nav>

    <!-- Contenido -->
    <main class="max-w-[95%] mx-auto px-4 py-8 w-full flex-grow">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden p-6 mb-6">
            <h1 class="text-xl font-bold text-gray-800 uppercase mb-4 flex items-center gap-2">
                <i class="fa-solid fa-clock-rotate-left text-cyan-600"></i> Historial de Registros: {{ str_replace('_', ' ', $tipo) }}
            </h1>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse whitespace-nowrap text-xs">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 uppercase tracking-wider border-b border-gray-200">
                            <th class="py-3 px-3 font-semibold">ID</th>
                            <th class="py-3 px-3 font-semibold text-emerald-700">pH</th>
                            <th class="py-3 px-3 font-semibold text-amber-700">EC (mS/cm)</th>
                            <th class="py-3 px-3 font-semibold text-blue-700">Temp. Amb</th>
                            <th class="py-3 px-3 font-semibold text-blue-600">Hum. Amb</th>
                            <th class="py-3 px-3 font-semibold">Fecha y Hora</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700">
                        @forelse($registros as $sensor)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="py-3 px-3 font-bold">#{{ $sensor->id }}</td>
                            <td class="py-3 px-3 font-semibold text-emerald-700">{{ $sensor->ph_valor ?? '--' }}</td>
                            <td class="py-3 px-3 font-semibold text-amber-700">{{ $sensor->tds_valor ?? '--' }}</td>
                            <td class="py-3 px-3 text-blue-700">{{ $sensor->temp_ambiente ? $sensor->temp_ambiente . ' °C' : '--' }}</td>
                            <td class="py-3 px-3 text-blue-600">{{ $sensor->humedad_ambiente ? $sensor->humedad_ambiente . ' %' : '--' }}</td>
                            <td class="py-3 px-3 text-gray-500 text-[11px]">{{ $sensor->created_at ? \Carbon\Carbon::parse($sensor->created_at)->format('d/m/Y H:i:s') : '--' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-gray-400">
                                <i class="fa-solid fa-folder-open text-3xl mb-2"></i>
                                <p>No hay registros históricos almacenados para esta sección.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-4">
                {{ $registros->links() }}
            </div>
        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 py-4 text-center text-xs text-gray-500 mt-auto">
        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.
    </footer>
</body>
</html>