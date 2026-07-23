<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Registro Suelo - Sistema Control</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

    <nav class="bg-emerald-600 text-white shadow-md">
        <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
            <span class="font-bold text-xl tracking-wider"><i class="fa-solid fa-leaf mr-2"></i>SISTEMA CONTROL</span>
            <a href="{{ route('suelo.index') }}" class="text-sm bg-emerald-700 hover:bg-emerald-800 px-3 py-2 rounded-md transition font-medium">
                <i class="fa-solid fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-8 w-full flex-grow">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Captura de Monitoreo en Suelo</h2>
                <p class="text-xs text-gray-500 mt-1">Los campos en gris se calculan automáticamente en tiempo real.</p>
            </div>

            <form action="{{ route('suelo.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha</label>
                        <input type="date" name="fecha" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-emerald-500">
                    </div>
                    <div class="mb-4">
                        <label class="block text-sm font-bold text-gray-700 uppercase mb-2">Sector / Nave:</label>
                        <div class="relative">
                            <select name="sector" id="sector" class="form-select border border-gray-300 rounded w-full p-2" required>
                                <option value=" ">Seleccione un sector</option>
                                @foreach($sectores as $sector)
                                <option value="{{ $sector }}" {{ old('sector') == $sector ? 'selected' : '' }}>{{ $sector }}</option>
                                @endforeach
                            </select>
                        </div>
                        @error('sector')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <hr class="border-gray-200">

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                    <div class="bg-stone-50 p-4 rounded-xl border border-stone-200 space-y-3">
                        <h3 class="font-bold text-sm text-stone-700 border-b border-stone-200 pb-1"><i class="fa-solid fa-temperature-half text-orange-500 mr-1"></i> Monitoreo Clima</h3>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-0.5">Temperatura (°C)</label>
                            <input type="number" step="0.01" id="temperatura" name="temperatura" class="w-full bg-white border border-gray-300 rounded-lg px-2.5 py-1.5 text-sm focus:outline-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-0.5">Humedad (%)</label>
                            <input type="number" step="0.01" id="humedad" name="humedad" class="w-full bg-white border border-gray-300 rounded-lg px-2.5 py-1.5 text-sm focus:outline-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 mb-0.5">DPV (Auto)</label>
                            <input type="text" id="dpv_view" disabled class="w-full bg-gray-200 border border-gray-300 text-gray-600 rounded-lg px-2.5 py-1.5 text-sm font-mono font-bold">
                        </div>
                    </div>

                    <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-200 space-y-3 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-sm text-blue-700 border-b border-blue-200 pb-1">
                                <i class="fa-solid fa-gauge text-blue-500 mr-1"></i> Humedad del Suelo
                            </h3>
                            <div class="mt-3">
                                <label class="block text-xs font-medium text-gray-600 mb-0.5">Lectura Tensiómetro (cb / kPa)</label>
                                <input type="number" step="0.01" id="lectura_tensiometro" name="lectura_tensiometro" placeholder="Ej: 15.40" class="w-full bg-white border border-gray-300 rounded-lg px-2.5 py-1.5 text-sm focus:outline-emerald-500">
                            </div>
                        </div>

                        <div class="mt-2">
                            <label class="block text-[11px] font-bold text-gray-500 uppercase mb-1">Estado del Suelo:</label>
                            <input type="text" id="tensiometro_alerta_view" value="Esperando lectura..." disabled class="w-full bg-gray-200 border border-gray-300 text-gray-600 rounded-lg px-2.5 py-1.5 text-xs font-bold text-center">
                            <input type="hidden" id="tensiometro_estatus" name="tensiometro_estatus" value="">
                        </div>
                    </div>

                    <div class="bg-purple-50/50 p-4 rounded-xl border border-purple-200 space-y-3">
                        <h3 class="font-bold text-sm text-purple-700 border-b border-purple-200 pb-1"><i class="fa-solid fa-flask text-purple-500 mr-1"></i> Parámetros Químicos</h3>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-0.5">Conductividad Eléctrica (CE)</label>
                            <input type="number" step="0.01" id="ce" name="ce" placeholder="Ej: 1.8" class="w-full bg-white border border-gray-300 rounded-lg px-2.5 py-1.5 text-sm focus:outline-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-0.5">Potencial de Hidrógeno (pH)</label>
                            <input type="number" step="0.1" id="ph" name="ph" placeholder="Ej: 6.2" class="w-full bg-white border border-gray-300 rounded-lg px-2.5 py-1.5 text-sm focus:outline-emerald-500">
                        </div>

                        {{-- ALERTA CONDICIONAL PARA CE > 3.0 --}}
                        <div id="alerta_ce" class="hidden p-3 bg-red-50 border border-red-200 rounded-lg space-y-2">
                            <span class="text-[11px] font-bold text-red-700 uppercase block"><i class="fa-solid fa-triangle-exclamation mr-1"></i> Alerta: CE Excesiva (> 3.0)</span>
                            <div class="flex flex-wrap gap-3 text-xs font-semibold text-gray-700">
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="checkbox" name="alerta_opcion[]" id="chk_eps" value="EPS" class="rounded text-red-600 focus:ring-red-500"> EPS
                                </label>
                                <label class="flex items-center gap-1 cursor-pointer">
                                    <input type="checkbox" name="alerta_opcion[]" id="chk_ecp" value="ECP" class="rounded text-red-600 focus:ring-red-500"> ECP
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="bg-amber-50/30 p-4 rounded-xl border border-amber-200 space-y-4 md:col-span-2 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-sm text-amber-880 border-b border-amber-200 pb-1">
                                <i class="fa-solid fa-sun text-amber-500 mr-1"></i> Radiación Solar
                            </h3>
                            <div class="mt-3">
                                <label class="block text-xs font-medium text-gray-600 mb-1">Lectura (Lux)</label>
                                <input type="number" id="radiacion_lectura" name="radiacion_lectura" min="0" placeholder="Ej. 45000" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-emerald-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-500 mb-1">Semáforo Alerta (Auto)</label>
                                <input type="text" id="radiacion_semaforo_view" value="Esperando datos..." disabled class="w-full bg-gray-200 border border-gray-300 text-gray-600 rounded-lg px-2.5 py-2 text-sm font-bold text-center">
                                <input type="hidden" id="radiacion_semaforo" name="radiacion_semaforo" value="">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Acción Tomada</label>
                                <textarea name="radiacion_accion_tomada" rows="1" placeholder="Describa la acción correctiva..." class="w-full bg-white border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:outline-emerald-500 resize-none"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="w-full md:col-span-1">
                        <div id="estatus_box" class="h-full bg-gray-100 rounded-xl border border-gray-300 p-6 flex flex-col justify-center items-center transition duration-300">
                            <span class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Estatus Predictivo General</span>
                            <div id="estatus_text" class="text-2xl font-black text-gray-400">—</div>
                            <p class="text-[11px] text-gray-400 mt-2 text-center">Depende del rango óptimo del DPV (0.8 a 1.4)</p>
                        </div>
                    </div>

                </div>

                {{-- APARTADO: ANÁLISIS RÁPIDO --}}
                <div id="seccion_analisis_rapido" class="hidden bg-cyan-50/40 p-5 rounded-xl border border-cyan-200 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between border-b border-cyan-200 pb-2 gap-2">
                        <h3 class="font-bold text-base text-cyan-900 flex items-center gap-1.5">
                            <i class="fa-solid fa-bolt-lightning text-cyan-600"></i> Análisis Rápido
                        </h3>
                        <div class="flex items-center gap-2">
                            <label class="text-xs font-bold text-gray-700 uppercase tracking-wider">¿Se cumplió?</label>
                            <select name="analisis_rapido_cumplio" id="analisis_rapido_cumplio" onchange="evaluarCumplimiento()" class="bg-white border border-gray-300 rounded-lg p-1.5 text-xs font-bold outline-none text-gray-800 focus:border-cyan-500 focus:ring-1 focus:ring-cyan-500">
                                <option value="si" selected>SÍ</option>
                                <option value="no">NO</option>
                            </select>
                        </div>
                    </div>

                    <!-- BLOQUE REGISTRO: EPS -->
                    <div id="fila_analisis_eps" class="hidden space-y-2">
                        <span class="text-xs font-bold text-cyan-800 uppercase tracking-wider block bg-cyan-100/50 px-2 py-1 rounded border border-cyan-200/40 w-fit">
                            • Análisis EPS
                        </span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-0.5">No3 (EPS)</label>
                                <input type="text" name="eps_rapido_no3" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-cyan-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-0.5">K (EPS)</label>
                                <input type="text" name="eps_rapido_k" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-cyan-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Ca (EPS)</label>
                                <input type="text" name="eps_rapido_ca" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-cyan-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Na (EPS)</label>
                                <input type="text" name="eps_rapido_na" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-cyan-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-0.5">P (EPS)</label>
                                <input type="text" name="eps_rapido_p" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-cyan-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-0.5">PH (EPS)</label>
                                <input type="text" name="eps_rapido_ph" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-cyan-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Ce (EPS)</label>
                                <input type="text" name="eps_rapido_ce" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-cyan-500">
                            </div>
                        </div>
                    </div>

                    <!-- BLOQUE REGISTRO: ECP -->
                    <div id="fila_analisis_ecp" class="hidden space-y-2">
                        <span class="text-xs font-bold text-cyan-800 uppercase tracking-wider block bg-cyan-100/50 px-2 py-1 rounded border border-cyan-200/40 w-fit">
                            • Análisis ECP
                        </span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-7 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-0.5">No3 (ECP)</label>
                                <input type="text" name="ecp_rapido_no3" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-cyan-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-0.5">K (ECP)</label>
                                <input type="text" name="ecp_rapido_k" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-cyan-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Ca (ECP)</label>
                                <input type="text" name="ecp_rapido_ca" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-cyan-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Na (ECP)</label>
                                <input type="text" name="ecp_rapido_na" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-cyan-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-0.5">P (ECP)</label>
                                <input type="text" name="ecp_rapido_p" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-cyan-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-0.5">PH (ECP)</label>
                                <input type="text" name="ecp_rapido_ph" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-cyan-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Ce (ECP)</label>
                                <input type="text" name="ecp_rapido_ce" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-cyan-500">
                            </div>
                        </div>
                    </div>

                </div>

                {{-- APARTADO: ANÁLISIS DE LABORATORIO --}}
                <div id="seccion_laboratorio" class="hidden bg-emerald-50/30 p-5 rounded-xl border border-emerald-200 space-y-5">
                    <h3 class="font-bold text-base text-emerald-900 border-b border-emerald-200 pb-2 flex items-center gap-1.5">
                        <i class="fa-solid fa-microscope text-emerald-600"></i> Análisis de Laboratorio
                    </h3>
                    
                    <!-- NUEVO: TIPO DE ANÁLISIS -->
                    <div>
                        <label class="block text-xs font-bold text-emerald-800 uppercase tracking-wider mb-2">Tipo de Análisis:</label>
                        <div class="flex flex-wrap gap-4">
                            <label class="flex items-center gap-1.5 cursor-pointer text-sm text-gray-700 font-medium">
                                <input type="radio" name="tipo_analisis_lab" value="fertilidad" class="text-emerald-600 focus:ring-emerald-500" checked> 
                                Fertilidad
                            </label>
                            <label class="flex items-center gap-1.5 cursor-pointer text-sm text-gray-700 font-medium">
                                <input type="radio" name="tipo_analisis_lab" value="pasta_saturada" class="text-emerald-600 focus:ring-emerald-500"> 
                                Pasta Saturada
                            </label>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-0.5">MO</label>
                            <input type="text" name="lab_mo" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-0.5">P-Bray</label>
                            <input type="text" name="lab_p_bray" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-0.5">K</label>
                            <input type="text" name="lab_k" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Mg</label>
                            <input type="text" name="lab_mg" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Na</label>
                            <input type="text" name="lab_na" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Fe</label>
                            <input type="text" name="lab_fe" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Zn</label>
                            <input type="text" name="lab_zn" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Mn</label>
                            <input type="text" name="lab_mn" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-0.5">Cu</label>
                            <input type="text" name="lab_cu" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-0.5">B</label>
                            <input type="text" name="lab_b" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-0.5">S</label>
                            <input type="text" name="lab_s" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-emerald-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-gray-600 mb-0.5">N-NO3</label>
                            <input type="text" name="lab_n_no3" placeholder="Valor" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm outline-none focus:border-emerald-500">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-3 rounded-lg shadow-md transition cursor-pointer">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Guardar Registro Suelo
                    </button>
                </div>
            </form>
        </div>
    </main>

  <script>
    // Inputs generales del clima y suelo
    const inputs = ['temperatura', 'humedad', 'radiacion_lectura', 'lectura_tensiometro', 'ce'];

    inputs.forEach(id => {
        document.getElementById(id).addEventListener('input', calcularValores);
    });

    document.getElementById('chk_eps').addEventListener('change', alternarFilasAnalisis);
    document.getElementById('chk_ecp').addEventListener('change', alternarFilasAnalisis);

    // Inputs de Análisis Rápido (EPS y ECP) y Laboratorio para activar los semáforos
    const inputsSemaforos = [
        // EPS Rápido
        'eps_rapido_no3', 'eps_rapido_k', 'eps_rapido_ca', 'eps_rapido_na', 'eps_rapido_p', 'eps_rapido_ph', 'eps_rapido_ce',
        // ECP Rápido
        'ecp_rapido_no3', 'ecp_rapido_k', 'ecp_rapido_ca', 'ecp_rapido_na', 'ecp_rapido_p', 'ecp_rapido_ph', 'ecp_rapido_ce',
        // Laboratorio
        'lab_mo', 'lab_p_bray', 'lab_k', 'lab_mg', 'lab_na', 'lab_fe', 'lab_zn', 'lab_mn', 'lab_cu', 'lab_b', 'lab_s', 'lab_n_no3'
    ];

    inputsSemaforos.forEach(name => {
        const el = document.getElementsByName(name)[0];
        if(el) {
            el.addEventListener('input', evaluarSemaforosDinamicos);
        }
    });

    // También escuchar cuando cambie el tipo de análisis de laboratorio
    document.querySelectorAll('input[name="tipo_analisis_lab"]').forEach(radio => {
        radio.addEventListener('change', evaluarSemaforosDinamicos);
    });

    function calcularValores() {
        const temp = parseFloat(document.getElementById('temperatura').value);
        const hum = parseFloat(document.getElementById('humedad').value);
        const lux = parseFloat(document.getElementById('radiacion_lectura').value);
        const tensio = parseFloat(document.getElementById('lectura_tensiometro').value);
        const ceValor = parseFloat(document.getElementById('ce').value);

        const divAlertaCe = document.getElementById('alerta_ce');
        const divAnalisisRapido = document.getElementById('seccion_analisis_rapido');
        const divSeccionLab = document.getElementById('seccion_laboratorio');
        const selectCumplio = document.getElementById('analisis_rapido_cumplio');

        if (!isNaN(ceValor) && ceValor > 3.0) {
            divAlertaCe.classList.remove('hidden');
            divAnalisisRapido.classList.remove('hidden');
            alternarFilasAnalisis();
            if (selectCumplio.value === 'no') {
                divSeccionLab.classList.remove('hidden');
            }
        } else {
            divAlertaCe.classList.add('hidden');
            divAnalisisRapido.classList.add('hidden');
            divSeccionLab.classList.add('hidden');
        }

        // 1. Cálculo DPV
        const eBox = document.getElementById('estatus_box');
        const eText = document.getElementById('estatus_text');

        if (!isNaN(temp) && !isNaN(hum)) {
            const es = 0.61078 * Math.exp((17.27 * temp) / (temp + 237.3));
            const dpv = parseFloat((es * (1 - (hum / 100))).toFixed(2));
            document.getElementById('dpv_view').value = dpv;

            if (dpv >= 0.8 && dpv <= 1.4) {
                eBox.className = "bg-emerald-100 rounded-xl border border-emerald-300 p-6 flex flex-col justify-center items-center h-full transition duration-300";
                eText.className = "text-2xl font-black text-emerald-800";
                eText.innerText = "ÓPTIMO";
            } else {
                eBox.className = "bg-red-100 rounded-xl border border-red-300 p-6 flex flex-col justify-center items-center h-full transition duration-300";
                eText.className = "text-2xl font-black text-red-800";
                eText.innerText = "REVISAR CLIMA";
            }
        } else {
            document.getElementById('dpv_view').value = "";
            eBox.className = "bg-gray-100 rounded-xl border border-gray-300 p-6 flex flex-col justify-center items-center h-full transition duration-300";
            eText.className = "text-2xl font-black text-gray-400";
            eText.innerText = "—";
        }

        // 2. Tensiómetro
        const tAlertaView = document.getElementById('tensiometro_alerta_view');
        const tEstatusHidden = document.getElementById('tensiometro_estatus');
        
        if (!isNaN(tensio)) {
            if (tensio < 5) {
                tAlertaView.className = "w-full bg-blue-100 border border-blue-300 text-blue-800 rounded-lg px-2.5 py-1.5 text-xs font-bold text-center shadow-sm";
                tAlertaView.value = "💧 SUELO SATURADO (NO REGAR)";
                tEstatusHidden.value = "SUELO SATURADO";
            } 
            else if (tensio >= 5 && tensio <= 20) {
                tAlertaView.className = "w-full bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-lg px-2.5 py-1.5 text-xs font-bold text-center shadow-sm";
                tAlertaView.value = "✅ HUMEDAD ADECUADA";
                tEstatusHidden.value = "HUMEDAD ADECUADA";
            } 
            else if (tensio > 20 && tensio <= 30) {
                tAlertaView.className = "w-full bg-amber-100 border border-amber-300 text-amber-800 rounded-lg px-2.5 py-1.5 text-xs font-bold text-center shadow-sm";
                tAlertaView.value = "⚠️ SOLICITAR RIEGO";
                tEstatusHidden.value = "SOLICITAR RIEGO";
            } 
            else if (tensio > 30) {
                tAlertaView.className = "w-full bg-red-100 border border-red-300 text-red-800 rounded-lg px-2.5 py-1.5 text-xs font-black text-center animate-pulse shadow-sm";
                tAlertaView.value = "🚨 CRÍTICO: SUELO SECO (URGENTE)";
                tEstatusHidden.value = "SUELO SECO CRÍTICO";
            }
        } else {
            tAlertaView.className = "w-full bg-gray-200 border border-gray-300 text-gray-600 rounded-lg px-2.5 py-1.5 text-xs font-bold text-center";
            tAlertaView.value = "Esperando lectura...";
            tEstatusHidden.value = "";
        }

        // 3. Radiación
        const rSemaforoView = document.getElementById('radiacion_semaforo_view');
        const rSemaforoHidden = document.getElementById('radiacion_semaforo');
        const rAccionTomada = document.getElementsByName('radiacion_accion_tomada')[0];

        if (!isNaN(lux)) {
            if (lux < 15000) {
                rSemaforoView.className = "w-full bg-red-100 border border-red-300 text-red-800 rounded-lg px-2.5 py-2 text-sm font-bold text-center";
                rSemaforoView.value = "ROJO (Insuficiente)";
                rSemaforoHidden.value = "ROJO";
                rAccionTomada.value = "Espaciar riego, evitar saturación";
            } else if (lux >= 15000 && lux <= 34000) {
                rSemaforoView.className = "w-full bg-amber-100 border border-amber-300 text-amber-800 rounded-lg px-2.5 py-2 text-sm font-bold text-center";
                rSemaforoView.value = "AMARILLO (Baja/Moderada)";
                rSemaforoHidden.value = "AMARILLO";
                rAccionTomada.value = "Mantenimiento vegetativo";
            } else if (lux >= 35000 && lux <= 65000) {
                rSemaforoView.className = "w-full bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-lg px-2.5 py-2 text-sm font-bold text-center";
                rSemaforoView.value = "VERDE (Óptima)";
                rSemaforoHidden.value = "VERDE";
                rAccionTomada.value = "Riego normal, máxima fotosíntesis";
            } else if (lux >= 66000 && lux <= 80000) {
                rSemaforoView.className = "w-full bg-amber-100 border border-amber-300 text-amber-800 rounded-lg px-2.5 py-2 text-sm font-bold text-center";
                rSemaforoView.value = "AMARILLO (Alta/Alerta)";
                rSemaforoHidden.value = "AMARILLO";
                rAccionTomada.value = "Monitorear T/VPD";
            } else {
                rSemaforoView.className = "w-full bg-red-100 border border-red-300 text-red-800 rounded-lg px-2.5 py-2 text-sm font-bold text-center";
                rSemaforoView.value = "ROJO (Excesiva/Crítica)";
                rSemaforoHidden.value = "ROJO";
                rAccionTomada.value = "Activar malla sombra, nebulización";
            }
        } else {
            rSemaforoView.className = "w-full bg-gray-200 border border-gray-300 text-gray-600 rounded-lg px-2.5 py-2 text-sm font-bold text-center";
            rSemaforoView.value = "Esperando datos...";
            rSemaforoHidden.value = "";
            rAccionTomada.value = "";
        }
    }

    function evaluarCumplimiento() {
        const seleccion = document.getElementById('analisis_rapido_cumplio').value;
        const seccionLab = document.getElementById('seccion_laboratorio');
        
        if (seleccion === 'no') {
            seccionLab.classList.remove('hidden');
        } else {
            seccionLab.classList.add('hidden');
        }
    }

    function alternarFilasAnalisis() {
        const epsChecked = document.getElementById('chk_eps').checked;
        const ecpChecked = document.getElementById('chk_ecp').checked;

        const filaEps = document.getElementById('fila_analisis_eps');
        const filaEcp = document.getElementById('fila_analisis_ecp');

        if (epsChecked) {
            filaEps.classList.remove('hidden');
        } else {
            filaEps.classList.add('hidden');
        }

        if (ecpChecked) {
            filaEcp.classList.remove('hidden');
        } else {
            filaEcp.classList.add('hidden');
        }
    }

    // Función encargada de evaluar y renderizar los semáforos dinámicos en los campos
    function evaluarSemaforosDinamicos() {
        const bajo = ['bg-red-50', 'border-red-400', 'focus:ring-red-500', 'text-red-900'];
        const optimo = ['bg-green-50', 'border-green-400', 'focus:ring-green-500', 'text-green-900'];
        const alto = ['bg-amber-50', 'border-amber-400', 'focus:ring-amber-500', 'text-amber-900'];
        const limpiar = ['bg-white', 'border-gray-300', 'focus:border-cyan-500', 'focus:border-emerald-500', 'bg-red-50', 'border-red-400', 'bg-green-50', 'border-green-400', 'bg-amber-50', 'border-amber-400', 'text-red-900', 'text-green-900', 'text-amber-900'];

        function aplicarColor(inputName, valor, minOptimo, maxOptimo, forzarGris = false) {
            const el = document.getElementsByName(inputName)[0];
            if (!el || el.value === "") {
                if(el) el.classList.remove(...limpiar);
                return;
            }
            
            el.classList.remove(...limpiar);

            // 💡 SI SE INDICA FORZAR GRIS (Caso Sodio en Fertilidad), NO COMPARA RANGOS
            if (forzarGris) {
                return; 
            }

            if (valor < minOptimo) {
                el.classList.add(...bajo);
            } else if (valor >= minOptimo && valor <= maxOptimo) {
                el.classList.add(...optimo);
            } else {
                el.classList.add(...alto);
            }
        }

        // ==========================================
        // 1. EVALUAR REGISTRO SECCIÓN EPS (RÁPIDO)
        // ==========================================
        const epsNo3 = parseFloat(document.getElementsByName('eps_rapido_no3')[0]?.value);
        aplicarColor('eps_rapido_no3', epsNo3, 150, 250); 

        const epsK = parseFloat(document.getElementsByName('eps_rapido_k')[0]?.value);
        aplicarColor('eps_rapido_k', epsK, 117, 234); 

        const epsCa = parseFloat(document.getElementsByName('eps_rapido_ca')[0]?.value);
        aplicarColor('eps_rapido_ca', epsCa, 120, 200); 

        const epsNa = parseFloat(document.getElementsByName('eps_rapido_na')[0]?.value);
        aplicarColor('eps_rapido_na', epsNa, 0, 60); 

        const epsP = parseFloat(document.getElementsByName('eps_rapido_p')[0]?.value);
        aplicarColor('eps_rapido_p', epsP, 15, 30); 

        const epsPh = parseFloat(document.getElementsByName('eps_rapido_ph')[0]?.value);
        aplicarColor('eps_rapido_ph', epsPh, 5.5, 6.5); 

        const epsCe = parseFloat(document.getElementsByName('eps_rapido_ce')[0]?.value);
        aplicarColor('eps_rapido_ce', epsCe, 2.0, 3.5); 

        // ==========================================
        // 2. EVALUAR REGISTRO SECCIÓN ECP (RÁPIDO)
        // ==========================================
        const ecpNo3 = parseFloat(document.getElementsByName('ecp_rapido_no3')[0]?.value);
        aplicarColor('ecp_rapido_no3', ecpNo3, 500, 800); 

        const ecpK = parseFloat(document.getElementsByName('ecp_rapido_k')[0]?.value);
        aplicarColor('ecp_rapido_k', ecpK, 3500, 5000); 

        const ecpCa = parseFloat(document.getElementsByName('ecp_rapido_ca')[0]?.value);
        aplicarColor('ecp_rapido_ca', ecpCa, 200, 450); 

        const ecpNa = parseFloat(document.getElementsByName('ecp_rapido_na')[0]?.value);
        aplicarColor('ecp_rapido_na', ecpNa, 0, 100); 

        const ecpP = parseFloat(document.getElementsByName('ecp_rapido_p')[0]?.value);
        aplicarColor('ecp_rapido_p', ecpP, 200, 400); 

        const ecpPh = parseFloat(document.getElementsByName('ecp_rapido_ph')[0]?.value);
        aplicarColor('ecp_rapido_ph', ecpPh, 5.5, 6.2); 

        const ecpCe = parseFloat(document.getElementsByName('ecp_rapido_ce')[0]?.value);
        aplicarColor('ecp_rapido_ce', ecpCe, 8.0, 12.0); 

        // ==========================================
        // 3. EVALUAR SECCIÓN LABORATORIO (DINÁMICO)
        // ==========================================
        const tipoLab = document.querySelector('input[name="tipo_analisis_lab"]:checked')?.value;

        if (tipoLab === 'fertilidad') {
            const labNo3 = parseFloat(document.getElementsByName('lab_n_no3')[0]?.value);
            aplicarColor('lab_n_no3', labNo3, 25, 45); 

            const labP = parseFloat(document.getElementsByName('lab_p_bray')[0]?.value);
            aplicarColor('lab_p_bray', labP, 25, 45); 

            const labK = parseFloat(document.getElementsByName('lab_k')[0]?.value);
            aplicarColor('lab_k', labK, 180, 300); 

            const labMg = parseFloat(document.getElementsByName('lab_mg')[0]?.value);
            aplicarColor('lab_mg', labMg, 250, 450); 

            // 💡 SOLUCIÓN OPERATIVA: Forzamos el estado neutral gris para el Na en Fertilidad
            const labNa = parseFloat(document.getElementsByName('lab_na')[0]?.value);
            aplicarColor('lab_na', labNa, 0, 0, true); 

            const labFe = parseFloat(document.getElementsByName('lab_fe')[0]?.value);
            aplicarColor('lab_fe', labFe, 5.0, 15.0); 

            const labZn = parseFloat(document.getElementsByName('lab_zn')[0]?.value);
            aplicarColor('lab_zn', labZn, 1.5, 3.5); 

            const labMn = parseFloat(document.getElementsByName('lab_mn')[0]?.value);
            aplicarColor('lab_mn', labMn, 2.0, 10.0); 

            const labCu = parseFloat(document.getElementsByName('lab_cu')[0]?.value);
            aplicarColor('lab_cu', labCu, 0.4, 1.5); 

            const labB = parseFloat(document.getElementsByName('lab_b')[0]?.value);
            aplicarColor('lab_b', labB, 0.6, 1.2); 

            const labS = parseFloat(document.getElementsByName('lab_s')[0]?.value);
            aplicarColor('lab_s', labS, 15, 35); 

        } else if (tipoLab === 'pasta_saturada') {
            const labNo3 = parseFloat(document.getElementsByName('lab_n_no3')[0]?.value);
            aplicarColor('lab_n_no3', labNo3, 150, 250); 

            const labP = parseFloat(document.getElementsByName('lab_p_bray')[0]?.value);
            aplicarColor('lab_p_bray', labP, 15, 30); 

            const labK = parseFloat(document.getElementsByName('lab_k')[0]?.value);
            aplicarColor('lab_k', labK, 150, 250); 

            const labMg = parseFloat(document.getElementsByName('lab_mg')[0]?.value);
            aplicarColor('lab_mg', labMg, 36, 60); 

            const labNa = parseFloat(document.getElementsByName('lab_na')[0]?.value);
            aplicarColor('lab_na', labNa, 0, 60); 

            const labFe = parseFloat(document.getElementsByName('lab_fe')[0]?.value);
            aplicarColor('lab_fe', labFe, 0, 0, true); 

            const labZn = parseFloat(document.getElementsByName('lab_zn')[0]?.value);
            aplicarColor('lab_zn', labZn, 0, 0, true);

            const labMn = parseFloat(document.getElementsByName('lab_mn')[0]?.value);
            aplicarColor('lab_mn', labMn, 0, 0, true);

            const labCu = parseFloat(document.getElementsByName('lab_cu')[0]?.value);
            aplicarColor('lab_cu', labCu, 0, 0, true);

            const labB = parseFloat(document.getElementsByName('lab_b')[0]?.value);
            aplicarColor('lab_b', labB, 0, 0, true);

            const labS = parseFloat(document.getElementsByName('lab_s')[0]?.value);
            aplicarColor('lab_s', labS, 192, 480); 
        }
    }
</script>
</body>

</html>