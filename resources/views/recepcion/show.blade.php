<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Recepción</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

    <nav class="{{ $tipo === 'exportacion' ? 'bg-blue-600' : 'bg-emerald-600' }} text-white shadow-md">
        <div class="max-w-[95%] mx-auto px-4 flex items-center justify-between h-16">
            <div class="flex items-center">
                <i class="fa-solid {{ $tipo === 'exportacion' ? 'fa-plane-departure' : 'fa-leaf' }} text-2xl mr-2"></i>
                <span class="font-bold text-xl tracking-wider">SISTEMA CONTROL</span>
            </div>
            <a href="{{ route('recepcion.index') }}" class="{{ $tipo === 'exportacion' ? 'bg-blue-700 hover:bg-blue-800' : 'bg-emerald-700 hover:bg-emerald-800' }} text-white font-bold px-4 py-2 rounded-lg text-sm transition flex items-center gap-2 shadow-sm">
                <i class="fa-solid fa-arrow-left"></i> Volver a Recepción
            </a>
        </div>
    </nav>

    <main class="max-w-4xl mx-auto px-4 py-8 w-full flex-grow">

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fa-solid fa-file-invoice {{ $tipo === 'exportacion' ? 'text-blue-600' : 'text-emerald-600' }}"></i>
                Reporte de Recepción {{ $tipo === 'exportacion' ? 'Exportación' : 'Nacional' }}
            </h1>
            
        </div>

        @if (session('status'))
        <div class="mb-6 p-4 {{ $tipo === 'exportacion' ? 'bg-blue-50 border-l-4 border-blue-500 text-blue-800' : 'bg-emerald-50 border-l-4 border-emerald-500 text-emerald-800' }} text-sm font-semibold rounded-r-xl shadow-sm">
            {{ session('status') }}
        </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 md:col-span-2 space-y-4">
                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider border-b border-gray-100 pb-2">Datos de Origen</h3>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="text-gray-400 block text-xs font-semibold uppercase">Productor</span>
                        <span class="text-gray-800 font-bold text-base">{{ $registro->productor->name ?? 'N/A' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-xs font-semibold uppercase">Sector Utilizado</span>
                        <span class="text-gray-800 font-semibold">{{ $registro->sector_registro ?? 'No especificado' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-xs font-semibold uppercase">{{ $tipo === 'exportacion' ? 'Fecha de Envío' : 'Fecha de Recepción' }}</span>
                        <span class="text-gray-700 font-medium">
                            {{ \Carbon\Carbon::parse($tipo === 'exportacion' ? $registro->fecha_exportacion : $registro->fecha_nacional)->format('d/m/Y') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-xs font-semibold uppercase">Semana</span>
                        <span class="text-gray-900 font-bold bg-gray-100 px-2 py-0.5 rounded text-xs">
                            Semana {{ $tipo === 'exportacion' ? $registro->semana_exportacion : $registro->semana_nacional }}
                        </span>
                    </div>
                </div>

                <hr class="border-gray-100">

                <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider pb-1">
                    {{ $tipo === 'exportacion' ? 'Desglose de Cargas y Pesos' : 'Desglose de Pesos' }}
                </h3>

                <div class="grid grid-cols-2 gap-4 text-sm bg-gray-50 p-4 rounded-xl border border-gray-200">
                    @if($tipo === 'nacional')
                    <div>
                        <span class="text-gray-500 block text-xs">Cajas Recepcion</span>
                        <span class="text-lg font-bold text-gray-900">{{ number_format($registro->cajas_comercializar) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Peso Recepcion</span>
                        <span class="text-lg font-bold text-gray-900">{{ number_format($registro->peso_comercializar, 2) }} kg</span>

                        @if($registro->peso_comercializar_original !== null)
                        <span class="text-xs text-gray-400 line-through block mt-0.5">
                            Inicial: {{ number_format($registro->peso_comercializar_original, 2) }} kg
                        </span>
                        @endif
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Cajas de Rechazo (Procesado)</span>
                        <span class="text-lg font-bold text-red-600">{{ number_format($registro->cajas_rechazo_procesado) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Peso Rechazo (Procesado)</span>
                        <span class="text-lg font-bold text-red-600">{{ number_format($registro->peso_rechazo_procesado, 2) }} kg</span>

                        @if($registro->peso_rechazo_procesado_original !== null)
                        <span class="text-xs text-gray-400 line-through block mt-0.5">
                            Inicial: {{ number_format($registro->peso_rechazo_procesado_original, 2) }} kg
                        </span>
                        @endif
                    </div>
                    @else
                    <div>
                        <span class="text-gray-500 block text-xs">Cajas Exportadas</span>
                        <span class="text-lg font-bold text-gray-900">{{ number_format($registro->cajas_exportacion) }} uds</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Peso de Exportación Total</span>
                        <span class="text-lg font-bold text-gray-900">{{ number_format($registro->peso_exportacion, 2) }} kg</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Cajas Restituidas (Devolución)</span>
                        <span class="text-lg font-bold text-purple-600">uds</span>
                    </div>
                    <div>
                        <span class="text-gray-500 block text-xs">Saldo de Cajas Pendientes</span>
                        <span class="text-lg font-bold text-amber-600"> uds</span>
                    </div>
                    @endif
                </div>
            </div>

          <div class="bg-gray-900 text-white p-6 rounded-xl shadow-md flex flex-col justify-between border border-gray-800">
    <div>
        <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-4">
            {{ $tipo === 'exportacion' ? 'Resumen de Exportación' : 'Totales Calculados' }}
        </h3>
        <div class="space-y-4">
            <div>
                <span class="text-xs text-gray-400 block">Total Kilogramos Acumulados</span>
                <span class="text-3xl font-extrabold {{ $tipo === 'exportacion' ? 'text-blue-400' : 'text-amber-400' }} tracking-tight">
                    @if($tipo === 'exportacion')
                        @php
                            // Obtenemos el peso que se mandó a rechazo nacional
                            $pesoRechazoAcumulado = $registro->recepcionesNacionales ? $registro->recepcionesNacionales->sum('peso_rechazo_procesado') : 0;
                            // RESTAMOS el rechazo al peso de exportación para mostrar el dato neto real
                            $pesoNetoExportacion = $registro->peso_exportacion - $pesoRechazoAcumulado;
                        @endphp
                        {{ number_format($pesoNetoExportacion >= 0 ? $pesoNetoExportacion : 0, 2) }}
                    @else
                        {{ number_format($registro->total_kg, 2) }}
                    @endif
                    <span class="text-xs font-normal text-white">kg</span>
                </span>
            </div>
            <div>
                <span class="text-xs text-gray-400 block">{{ $tipo === 'exportacion' ? 'Cajas' : 'Total de Cajas Consolidadas' }}</span>
                <span class="text-2xl font-bold text-white tracking-tight">
                    @if($tipo === 'exportacion')
                        @php
                            // Obtenemos las cajas que se desviaron a rechazo nacional
                            $cajasRechazoAcumuladas = $registro->recepcionesNacionales ? $registro->recepcionesNacionales->sum('cajas_rechazo_procesado') : 0;
                            // RESTAMOS las cajas de rechazo para obtener el saldo neto real
                            $cajasNetasExportacion = $registro->cajas_exportacion - $cajasRechazoAcumuladas;
                        @endphp
                        {{ number_format($cajasNetasExportacion >= 0 ? $cajasNetasExportacion : 0) }}
                    @else
                        {{ number_format($registro->total_cajas) }}
                    @endif
                    <span class="text-xs font-normal text-gray-400">uds</span>
                </span>
            </div>

            @if($tipo === 'nacional' && $registro->cajas_vacias_totales > 0)
            <div class="border-t border-gray-800 pt-3">
                <span class="text-xs text-gray-400 block">Cajas Vacías Sueltas del Día</span>
                <span class="text-sm font-semibold text-gray-300">{{ number_format($registro->cajas_vacias_totales) }} vacías</span>
            </div>
            @endif

            @if($tipo === 'exportacion' && false)
            <div class="border-t border-gray-800 pt-3">
                <span class="text-xs text-gray-400 block">Estado del Saldo de Cajas</span>
                @if($registro->pendientes > 0)
                <span class="inline-flex items-center gap-1 mt-1 px-2 py-1 text-xs font-bold bg-amber-500/20 text-amber-400 rounded-md border border-amber-500/30">
                    <i class="fa-solid fa-triangle-exclamation"></i> Con Pendientes
                </span>
                @else
                <span class="inline-flex items-center gap-1 mt-1 px-2 py-1 text-xs font-bold bg-emerald-500/20 text-emerald-400 rounded-md border border-emerald-500/30">
                    <i class="fa-solid fa-circle-check"></i> Saldo Conciliado
                </span>
                @endif
            </div>
            @endif
        </div>
    </div>

    <div class="mt-6 text-[11px] text-gray-400 bg-gray-800/40 p-3 rounded-lg border border-gray-800">
        <span class="block"><i class="fa-solid fa-clock"></i> Creado: {{ $registro->created_at->format('d/m/Y H:i') }}</span>
    </div>
</div>
        </div>

        @if($tipo === 'nacional' && (auth()->user()->rol === 'administrador' || auth()->user()->rol === 'usuario_comercial'))
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="border-b border-gray-100 pb-3 mb-4">
                <h3 class="text-base font-bold text-gray-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-weight-scale text-emerald-600"></i>
                    Ajuste Operativo de Pesos en Báscula
                </h3>
                <p class="text-xs text-gray-500">
                    Se ingresan los pesos definitivos obtenidos tras el almacenamiento o reajuste en los procesos de selección.
                </p>
            </div>

            <form action="{{ route('recepcion.updateNacional', $registro->id) }}" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Peso Comercial Final (Kg)</label>
                        <input type="number" name="peso_comercializar" value="{{ $registro->peso_comercializar }}" step="0.01" min="0" required class="w-full border border-gray-300 rounded-lg p-2 text-sm outline-none font-semibold text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-red-700 uppercase mb-1">Peso de Rechazo Final (Kg)</label>
                        <input type="number" name="peso_rechazo_procesado" value="{{ $registro->peso_rechazo_procesado }}" step="0.01" min="0" required class="w-full border border-gray-300 rounded-lg p-2 text-sm outline-none font-semibold text-gray-800 focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                    </div>
                </div>

                <div class="flex justify-end pt-2">
                    <button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg text-sm transition shadow flex items-center justify-center gap-1.5 cursor-pointer h-[40px]">
                        <i class="fa-solid fa-floppy-disk"></i>
                        Actualizar Pesos de Salida
                    </button>
                </div>
            </form>
        </div>
        @endif

    </main>

    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500 w-full mt-auto">
        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.
    </footer>

</body>

</html>