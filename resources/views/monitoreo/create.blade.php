<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Registro - Sistema Control</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

    <nav class="bg-emerald-600 text-white shadow-md">
        <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
            <span class="font-bold text-xl tracking-wider"><i class="fa-solid fa-leaf mr-2"></i>SISTEMA CONTROL</span>
            <a href="{{ route('monitoreo.index') }}" class="text-sm bg-emerald-700 hover:bg-emerald-800 px-3 py-2 rounded-md transition font-medium">
                <i class="fa-solid fa-arrow-left mr-1"></i> Volver
            </a>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-8 w-full flex-grow">
        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Captura de Monitoreo Climático y Riego</h2>
                <p class="text-xs text-gray-500 mt-1">Los campos en gris se calculan automáticamente en tiempo real.</p>
            </div>

            <form action="{{ route('monitoreo.store') }}" method="POST" class="p-6 space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha</label>
                        <input type="date" name="fecha" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-emerald-500">
                    </div>
                    <div class="mb-4">
                        <label for="sector" class="block text-sm font-bold text-gray-700 uppercase mb-2">Sector / Nave:</label>
                        <div class="relative">
                            <select name="sector" id="sector" class="form-select" required>
                                <option value="">Seleccione un sector</option>
                                @foreach($sectores as $sector)
                                <option value="{{ $sector }}" {{ old('sector') == $sector ? 'selected' : '' }}>{{ $sector }}</option>
                                @endforeach
                            </select>
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                <i class="fa-solid fa-chevron-down text-xs"></i>
                            </div>
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

                    <div class="bg-blue-50/50 p-4 rounded-xl border border-blue-200 space-y-3">
                        <h3 class="font-bold text-sm text-blue-700 border-b border-blue-200 pb-1"><i class="fa-solid fa-droplet text-blue-500 mr-1"></i> Riego y Drenaje</h3>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-0.5">Vol. Riego Entrada (mL)</label>
                            <input type="number" id="vol_riego_entrada" name="vol_riego_entrada" class="w-full bg-white border border-gray-300 rounded-lg px-2.5 py-1.5 text-sm focus:outline-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-0.5">Vol. Drenaje Salida (mL)</label>
                            <input type="number" id="vol_drenaje_salida" name="vol_drenaje_salida" class="w-full bg-white border border-gray-300 rounded-lg px-2.5 py-1.5 text-sm focus:outline-emerald-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-blue-600 mb-0.5">% Drenaje (Auto)</label>
                            <input type="text" id="porcentaje_drenaje_view" disabled class="w-full bg-blue-100/80 border border-blue-200 text-blue-700 rounded-lg px-2.5 py-1.5 text-sm font-bold">
                        </div>
                    </div>

                    <div class="bg-purple-50/50 p-4 rounded-xl border border-purple-200 space-y-3">
                        <h3 class="font-bold text-sm text-purple-700 border-b border-purple-200 pb-1"><i class="fa-solid fa-flask text-purple-500 mr-1"></i> Parámetros Químicos</h3>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-600 mb-0.5">CE Ent</label>
                                <input type="number" step="0.01" id="ce_entrada" name="ce_entrada" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-600 mb-0.5">CE Sal</label>
                                <input type="number" step="0.01" id="ce_calida" name="ce_salida" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-medium text-gray-600 mb-0.5">pH Ent</label>
                                <input type="number" step="0.1" id="ph_entrada" name="ph_entrada" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-gray-600 mb-0.5">pH Sal</label>
                                <input type="number" step="0.1" id="ph_salida" name="ph_salida" class="w-full bg-white border border-gray-300 rounded-lg px-2 py-1 text-sm">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs font-bold text-gray-500 pt-1">
                            <div>
                                <label class="mb-0.5 block">Dif. CE</label>
                                <input type="text" id="diferencia_ce_view" disabled class="w-full bg-gray-200 border rounded p-1 text-center font-mono">
                            </div>
                            <div>
                                <label class="mb-0.5 block">Dif. pH</label>
                                <input type="text" id="diferencia_ph_view" disabled class="w-full bg-gray-200 border rounded p-1 text-center font-mono">
                            </div>
                        </div>
                    </div>

                    <div class="bg-amber-50/30 p-4 rounded-xl border border-amber-200 space-y-4 md:col-span-2 flex flex-col justify-between">
                        <div>
                            <h3 class="font-bold text-sm text-amber-800 border-b border-amber-200 pb-1">
                                <i class="fa-solid fa-sun text-amber-500 mr-1"></i> Radiación Solar
                            </h3>
                            <div class="mt-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Lectura (Lux)</label>
                                    <input type="number" id="radiacion_lectura" name="radiacion_lectura" min="0" placeholder="Ej. 45000" class="w-full bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-emerald-500">
                                </div>
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

                    <div class="bg-amber-50/40 p-4 rounded-xl border border-amber-200 space-y-6 flex flex-col justify-between">
                        <div class="space-y-4">
                            <h3 class="font-bold text-sm text-amber-800 border-b border-amber-200 pb-1">
                                <i class="fa-solid fa-weight-scale text-amber-600 mr-1"></i> Balance de Peso en Sustrato
                            </h3>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Peso Tarde Anterior (kg)</label>
                                    <input type="number" step="0.01" id="peso_tarde_anterior" name="peso_tarde_anterior" class="w-full bg-white border border-gray-300 rounded-lg px-2.5 py-2 text-sm focus:outline-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 mb-1">Peso Mañana (kg)</label>
                                    <input type="number" step="0.01" id="peso_manana" name="peso_manana" class="w-full bg-white border border-gray-300 rounded-lg px-2.5 py-2 text-sm focus:outline-emerald-500">
                                </div>
                            </div>
                        </div>

                        <div class="pt-2">
                            <label class="block text-xs font-bold text-amber-800 mb-1">
                                <i class="fa-solid fa-chart-line mr-1"></i> % Caída Nocturna (Auto)
                            </label>
                            <input type="text" id="porcentaje_caida_nocturna_view" disabled class="w-full bg-amber-100 border border-amber-200 text-amber-800 rounded-lg px-2.5 py-2 text-sm font-black text-center tracking-wider shadow-inner">
                        </div>
                    </div>

                </div>

                <div class="w-full mt-6">
                    <div id="estatus_box" class="bg-gray-100 rounded-xl border border-gray-300 p-6 flex flex-col justify-center items-center transition duration-300">
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-500 mb-1">Estatus Predictivo General</span>
                        <div id="estatus_text" class="text-2xl font-black text-gray-400">—</div>
                        <p class="text-[11px] text-gray-400 mt-2 text-center">Depende del rango óptimo del DPV (0.8 a 1.4)</p>
                    </div>
                </div>

                <div class="flex justify-end pt-4">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-3 rounded-lg shadow-md transition cursor-pointer">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Guardar Registro
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        const inputs = [
            'temperatura', 'humedad', 'vol_riego_entrada', 'vol_drenaje_salida',
            'ce_entrada', 'ce_calida', 'ph_entrada', 'ph_salida',
            'peso_tarde_anterior', 'peso_manana', 'radiacion_lectura'
        ];

        inputs.forEach(id => {
            document.getElementById(id).addEventListener('input', calcularValores);
        });

        function calcularValores() {
            const temp = parseFloat(document.getElementById('temperatura').value);
            const hum = parseFloat(document.getElementById('humedad').value);
            const volEnt = parseFloat(document.getElementById('vol_riego_entrada').value);
            const volSal = parseFloat(document.getElementById('vol_drenaje_salida').value);
            const ceEnt = parseFloat(document.getElementById('ce_entrada').value);
            const ceSal = parseFloat(document.getElementById('ce_calida').value);
            const phEnt = parseFloat(document.getElementById('ph_entrada').value);
            const phSal = parseFloat(document.getElementById('ph_salida').value);
            const pTarde = parseFloat(document.getElementById('peso_tarde_anterior').value);
            const pManana = parseFloat(document.getElementById('peso_manana').value);
            const lux = parseFloat(document.getElementById('radiacion_lectura').value);

            // 1. Cálculo DPV y Caja Predictiva General
            const eBox = document.getElementById('estatus_box');
            const eText = document.getElementById('estatus_text');

            if (!isNaN(temp) && !isNaN(hum)) {
                const es = 0.61078 * Math.exp((17.27 * temp) / (temp + 237.3));
                const dpv = parseFloat((es * (1 - (hum / 100))).toFixed(2));
                document.getElementById('dpv_view').value = dpv;

                if (dpv >= 0.8 && dpv <= 1.4) {
                    eBox.className = "bg-emerald-100 rounded-xl border border-emerald-300 p-6 flex flex-col justify-center items-center transition duration-300";
                    eText.className = "text-2xl font-black text-emerald-800";
                    eText.innerText = "ÓPTIMO";
                } else {
                    eBox.className = "bg-red-100 rounded-xl border border-red-300 p-6 flex flex-col justify-center items-center transition duration-300";
                    eText.className = "text-2xl font-black text-red-800";
                    eText.innerText = "REVISAR CLIMA";
                }
            } else {
                document.getElementById('dpv_view').value = "";
                eBox.className = "bg-gray-100 rounded-xl border border-gray-300 p-6 flex flex-col justify-center items-center transition duration-300";
                eText.className = "text-2xl font-black text-gray-400";
                eText.innerText = "—";
            }

            // 2. % Drenaje
            if (!isNaN(volEnt) && !isNaN(volSal) && volEnt > 0) {
                document.getElementById('porcentaje_drenaje_view').value = ((volSal / volEnt) * 100).toFixed(1) + "%";
            } else {
                document.getElementById('porcentaje_drenaje_view').value = "";
            }

            // 3. Diferencias Químicas
            document.getElementById('diferencia_ce_view').value = (!isNaN(ceEnt) && !isNaN(ceSal)) ? (ceSal - ceEnt).toFixed(2) : "";
            document.getElementById('diferencia_ph_view').value = (!isNaN(phEnt) && !isNaN(phSal)) ? (phSal - phEnt).toFixed(2) : "";

            // 4. % Caída Nocturna
            if (!isNaN(pTarde) && !isNaN(pManana) && pTarde > 0) {
                document.getElementById('porcentaje_caida_nocturna_view').value = (((pTarde - pManana) / pTarde) * 100).toFixed(1) + "%";
            } else {
                document.getElementById('porcentaje_caida_nocturna_view').value = "";
            }

            // 5. Semáforo Radiación y Acciones
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
    </script>
</body>

</html>