<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Bitácora Sanidad y Nutrición - Sistema Control</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

    <!-- Navbar Institucional -->
    <nav class="bg-emerald-600 text-white shadow-md">
        <div class="max-w-[95%] mx-auto px-4 shadow-sm">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <i class="fa-solid fa-leaf text-2xl mr-2"></i>
                    <span class="font-bold text-xl tracking-wider">SISTEMA CONTROL</span>
                </div>
                <div class="flex items-center gap-4 text-sm font-medium">
                    <span class="bg-emerald-700 px-3 py-1 rounded text-xs flex items-center gap-1">
                        <i class="fa-solid fa-user"></i> {{ auth()->user()->name }}
                    </span>
                    <a href="{{ route('sanidad.index') }}" class="text-emerald-100 hover:text-white transition flex items-center gap-1">
                        <i class="fa-solid fa-arrow-left"></i> Volver Historial
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-[95%] mx-auto px-4 py-8 w-full flex-grow">
        
        @if($errors->has('error'))
        <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-900 rounded-r-lg shadow-sm">
            <span class="font-medium text-sm">{{ $errors->first('error') }}</span>
        </div>
        @endif

        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <h2 class="text-xl font-bold text-gray-800">Nueva Asignación de Sanidad y Nutrición</h2>
                <p class="text-xs text-gray-500 mt-1">Configure los datos generales de la orden de trabajo y asigne al operador responsable de la ejecución.</p>
            </div>

            <form action="{{ route('sanidad.store') }}" method="POST" class="p-6 space-y-8">
                @csrf

                <!-- SECCIÓN MAESTRA: DATOS GENERALES CON FILTRADO INTERACTIVO -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha Programada</label>
                        <input type="date" name="fecha" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-emerald-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 uppercase mb-1">1. Asignar a Operador:</label>
                        <select name="operador_id" id="operador_id" class="border border-gray-300 rounded-lg w-full p-2 text-sm focus:outline-emerald-500 bg-white" required onchange="filtrarSectoresPorOperador()">
                            <option value="">Seleccione el encargado...</option>
                            @foreach($operadores as $op)
                            <option value="{{ $op->id }}" data-sectores="{{ $op->sectores }}" {{ old('operador_id') == $op->id ? 'selected' : '' }}>{{ $op->name }}</option>
                            @endforeach
                        </select>
                        @error('operador_id')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 uppercase mb-1">2. Sector / Nave Autorizada:</label>
                        <select name="sector" id="sector" class="border border-gray-300 rounded-lg w-full p-2 text-sm focus:outline-emerald-500 bg-gray-100" required disabled onchange="cambiarDatosPorSector()">
                            <option value="">Primero elija un operador...</option>
                        </select>
                        @error('sector')
                        <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- SUBFORMULARIO 1: MANEJO DE AGROQUÍMICOS -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                        <h3 class="font-bold text-base text-gray-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-spray-can text-orange-500"></i>
                            1. Sección: Manejo de Agroquímicos
                        </h3>
                        <button type="button" onclick="agregarFilaAgroquimico()" class="bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold px-3 py-1.5 rounded transition shadow-sm cursor-pointer">
                            <i class="fa-solid fa-plus mr-1"></i> Añadir Producto
                        </button>
                    </div>

                    <!-- BLOQUE APARTE: Información Fija del Sector Seleccionado -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 bg-orange-50/40 p-4 rounded-xl border border-orange-200 text-xs shadow-2xs">
                        <div class="bg-white p-3 rounded-lg border border-orange-100 flex flex-col justify-center">
                            <span class="text-orange-900 font-bold uppercase tracking-wider text-[10px] mb-0.5">Variedad Cultivada:</span>
                            <span id="txt-variedad" class="text-gray-800 font-extrabold text-sm">—</span>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-orange-100 flex flex-col justify-center">
                            <span class="text-orange-900 font-bold uppercase tracking-wider text-[10px] mb-0.5">N° Plantas Total:</span>
                            <span id="txt-plantas" class="text-gray-800 font-mono font-extrabold text-sm">—</span>
                        </div>
                        <div class="bg-white p-3 rounded-lg border border-orange-100 flex flex-col justify-center">
                            <span class="text-orange-900 font-bold uppercase tracking-wider text-[10px] mb-0.5">Fecha de Trasplante:</span>
                            <span id="txt-trasplante" class="text-gray-800 font-extrabold text-sm">—</span>
                        </div>
                    </div>

                    <!-- 💡 NUEVO: Inputs ocultos para enviar estos tres datos fijos en el Request al Controlador -->
                    <input type="hidden" name="variedad_sector" id="hidden-variedad" value="">
                    <input type="hidden" name="numero_plantas_sector" id="hidden-plantas" value="">
                    <input type="hidden" name="fecha_trasplante_sector" id="hidden-trasplante" value="">

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-gray-200 text-sm min-w-[850px]" id="tabla-agroquimicos">
                            <thead>
                                <tr class="bg-gray-100 text-gray-700 font-semibold text-xs border-b border-gray-200">
                                    <th class="p-2 border border-gray-200 w-32">F. Aplicación</th>
                                    <th class="p-2 border border-gray-200 w-36">Tipo Aplicación</th>
                                    <th class="p-2 border border-gray-200">Producto</th>
                                    <th class="p-2 border border-gray-200 w-64">Dosis / Unidad</th>
                                    <th class="p-2 border border-gray-200 w-20">IS</th>
                                    <th class="p-2 border border-gray-200 w-40">Tipo Solución</th>
                                    <th class="p-2 border border-gray-200">Observaciones</th>
                                    <th class="p-2 border border-gray-200 text-center w-12">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200" id="body-agroquimicos">
                                <tr class="hover:bg-gray-50/50">
                                    <td class="p-1 border border-gray-200"><input type="date" name="fecha_aplicacion[]" value="{{ date('Y-m-d') }}" required class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                                    <td class="p-1 border border-gray-200">
                                        <select name="aplicacion[]" required class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500 bg-white">
                                            <option value="RIEGO">RIEGO</option>
                                            <option value="FOLIAR">FOLIAR</option>
                                            <option value="DRENCH">DRENCH</option>
                                        </select>
                                    </td>
                                    <td class="p-1 border border-gray-200"><input type="text" name="producto[]" placeholder="Ej: Confidor" required class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                                    
                                    <td class="p-1 border border-gray-200">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex gap-1">
                                                <input type="number" step="0.01" name="dosis[]" required placeholder="Cant." class="w-1/2 border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500">
                                                <select onchange="evaluarUnidadManual(this)" class="w-1/2 border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500 bg-white selector-unidad-base">
                                                    <option value="mL">mL</option>
                                                    <option value="L">L</option>
                                                    <option value="g">g</option>
                                                    <option value="kg">kg</option>
                                                    <option value="OTRO">Otro...</option>
                                                </select>
                                            </div>
                                            <input type="text" name="unidad_dosis[]" value="mL" placeholder="Escriba la unidad..." class="w-full border border-emerald-400 bg-emerald-50/50 rounded p-1 text-xs focus:outline-emerald-500 hidden campo-unidad-manual">
                                        </div>
                                    </td>

                                    <td class="p-1 border border-gray-200"><input type="number" name="is_intervalo_seguridad[]" placeholder="0" class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                                    
                                    <td class="p-1 border border-gray-200">
                                        <select name="tipo_solucion[]" required class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500 bg-white font-medium text-emerald-800">
                                            <option value="SOLUCION MADRE">Solución Madre</option>
                                            <option value="SOLUCION DIARIA">Solución Diaria</option>
                                        </select>
                                    </td>

                                    <td class="p-1 border border-gray-200"><input type="text" name="agroquimicos_observaciones[]" placeholder="..." class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                                    <td class="p-1 border border-gray-200 text-center">
                                        <button type="button" onclick="eliminarFila(this)" class="text-red-500 hover:text-red-700 text-sm cursor-pointer disabled:opacity-30" disabled>&times;</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SUBFORMULARIO 2: SECCIÓN FERTILIZANTES -->
                <div class="space-y-6 pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                        <h3 class="font-bold text-base text-gray-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-flask-vial text-emerald-600"></i>
                            2. Sección: Manejo de Fertilizantes
                        </h3>
                        <button type="button" onclick="agregarNuevoTanque()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-lg transition shadow-sm cursor-pointer">
                            <i class="fa-solid fa-folder-plus mr-1"></i> Agregar Tanque
                        </button>
                    </div>

                    <div id="raiz-tanques-fertilizantes" class="space-y-6">
                        <!-- Renderizado vía JS -->
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200 mt-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Labores Culturales Realizadas</label>
                            <input type="text" name="labores_culturales" placeholder="Ej: Poda, deshoje, limpieza de goteros..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Observaciones Generales de la Mezcla</label>
                            <input type="text" name="fertilizantes_observaciones" placeholder="Ej: Monitorear conductividad eléctrica..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-emerald-500 bg-white">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-gray-200">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-3 rounded-lg shadow-md transition cursor-pointer">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Crear y Asignar Bitácora
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        const hoy = "{{ date('Y-m-d') }}";
        const mapaSectoresData = @json($sectoresConVariedad);
        
        let contadorTanques = 0;

        function evaluarUnidadManual(elementoSelect) {
            const contenedor = elementoSelect.parentElement.parentElement;
            const inputManual = contenedor.querySelector('.campo-unidad-manual');

            if (elementoSelect.value === 'OTRO') {
                inputManual.value = '';
                inputManual.classList.remove('hidden');
                inputManual.required = true;
                inputManual.focus();
            } else {
                inputManual.value = elementoSelect.value;
                inputManual.classList.add('hidden');
                inputManual.required = false;
            }
        }

        function formatearNumero(numero) {
            if (!numero) return '—';
            return new Intl.NumberFormat('es-MX').format(numero);
        }

        function formatearFecha(fechaCadena) {
            if (!fechaCadena) return '—';
            const partes = fechaCadena.split('-');
            if (partes.length !== 3) return fechaCadena;
            return `${partes[2]}/${partes[1]}/${partes[0]}`;
        }

        function filtrarSectoresPorOperador() {
            const selectOperador = document.getElementById('operador_id');
            const selectSector = document.getElementById('sector');
            const opcionSeleccionada = selectOperador.options[selectOperador.selectedIndex];
            const cadenaSectores = opcionSeleccionada.getAttribute('data-sectores');

            selectSector.innerHTML = '';
            
            // Limpiar visualizadores globales fijos
            document.getElementById('txt-variedad').textContent = "—";
            document.getElementById('txt-plantas').textContent = "—";
            document.getElementById('txt-trasplante').textContent = "—";

            // 💡 Limpiar inputs ocultos
            document.getElementById('hidden-variedad').value = "";
            document.getElementById('hidden-plantas').value = "";
            document.getElementById('hidden-trasplante').value = "";

            if (!cadenaSectores || cadenaSectores.trim() === '') {
                selectSector.innerHTML = '<option value="">Este operador no tiene sectores asignados</option>';
                selectSector.disabled = true;
                selectSector.classList.add('bg-gray-100');
                return;
            }

            selectSector.disabled = false;
            selectSector.classList.remove('bg-gray-100');

            const opcionDefecto = document.createElement('option');
            opcionDefecto.value = '';
            opcionDefecto.textContent = 'Seleccione un sector...';
            selectSector.appendChild(opcionDefecto);

            const listaSectores = cadenaSectores.split(',').map(s => s.trim());

            listaSectores.forEach(sector => {
                if (sector !== '') {
                    const opt = document.createElement('option');
                    opt.value = sector;
                    opt.textContent = sector;
                    selectSector.appendChild(opt);
                }
            });
        }

        function cambiarDatosPorSector() {
            const sectorSeleccionado = document.getElementById('sector').value.trim();
            
            if (sectorSeleccionado === "" || !mapaSectoresData.hasOwnProperty(sectorSeleccionado)) {
                document.getElementById('txt-variedad').textContent = "—";
                document.getElementById('txt-plantas').textContent = "—";
                document.getElementById('txt-trasplante').textContent = "—";

                // 💡 Limpiar inputs ocultos si no hay selección válida
                document.getElementById('hidden-variedad').value = "";
                document.getElementById('hidden-plantas').value = "";
                document.getElementById('hidden-trasplante').value = "";
            } else {
                const data = mapaSectoresData[sectorSeleccionado];
                
                // Inyectamos la información en las tarjetas globales superiores fijas
                document.getElementById('txt-variedad').textContent = data.variedad || "—";
                document.getElementById('txt-plantas').textContent = formatearNumero(data.numero_plantas);
                document.getElementById('txt-trasplante').textContent = formatearFecha(data.fecha_trasplante);

                // 💡 INYECTAR LOS VALORES REALES EN LOS CAMPOS OCULTOS PARA EL ENVÍO POST
                document.getElementById('hidden-variedad').value = data.variedad || "";
                document.getElementById('hidden-plantas').value = data.numero_plantas || "";
                document.getElementById('hidden-trasplante').value = data.fecha_trasplante || "";
            }
        }

        function agregarFilaAgroquimico() {
            const tbody = document.getElementById('body-agroquimicos');
            const nuevaFila = document.createElement('tr');
            nuevaFila.className = "hover:bg-gray-50/50";
            nuevaFila.innerHTML = `
                <td class="p-1 border border-gray-200"><input type="date" name="fecha_aplicacion[]" value="${hoy}" required class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                <td class="p-1 border border-gray-200">
                    <select name="aplicacion[]" required class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500 bg-white">
                        <option value="RIEGO">RIEGO</option>
                        <option value="FOLIAR">FOLIAR</option>
                        <option value="DRENCH">DRENCH</option>
                    </select>
                </td>
                <td class="p-1 border border-gray-200"><input type="text" name="producto[]" placeholder="Ej: Confidor" required class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                
                <td class="p-1 border border-gray-200">
                    <div class="flex flex-col gap-1">
                        <div class="flex gap-1">
                            <input type="number" step="0.01" name="dosis[]" required placeholder="Cant." class="w-1/2 border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500">
                            <select onchange="evaluarUnidadManual(this)" class="w-1/2 border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500 bg-white selector-unidad-base">
                                <option value="mL">mL</option>
                                <option value="L">L</option>
                                <option value="g">g</option>
                                <option value="kg">kg</option>
                                <option value="OTRO">Otro...</option>
                            </select>
                        </div>
                        <input type="text" name="unidad_dosis[]" value="mL" placeholder="Escriba la unidad..." class="w-full border border-emerald-400 bg-emerald-50/50 rounded p-1 text-xs focus:outline-emerald-500 hidden campo-unidad-manual">
                    </div>
                </td>

                <td class="p-1 border border-gray-200"><input type="number" name="is_intervalo_seguridad[]" placeholder="0" class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                
                <td class="p-1 border border-gray-200">
                    <select name="tipo_solucion[]" required class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500 bg-white font-medium text-emerald-800">
                        <option value="SOLUCION MADRE">Solución Madre</option>
                        <option value="SOLUCION DIARIA">Solución Diaria</option>
                    </select>
                </td>

                <td class="p-1 border border-gray-200"><input type="text" name="agroquimicos_observaciones[]" placeholder="..." class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                <td class="p-1 border border-gray-200 text-center">
                    <button type="button" onclick="eliminarFila(this)" class="text-red-500 hover:text-red-700 text-sm cursor-pointer">&times;</button>
                </td>
            `;
            tbody.appendChild(nuevaFila);
            verificarBotonesEliminar('body-agroquimicos');
        }

        function eliminarFila(boton) {
            const fila = boton.closest('tr');
            const tbodyId = fila.parentElement.id;
            fila.remove();
            verificarBotonesEliminar(tbodyId);
        }

        function verificarBotonesEliminar(tbodyId) {
            const tbody = document.getElementById(tbodyId);
            const filas = tbody.getElementsByTagName('tr');
            if (filas.length === 1) {
                filas[0].querySelector('button[type="button"]').disabled = true;
            } else {
                for (let i = 0; i < filas.length; i++) {
                    filas[i].querySelector('button[type="button"]').disabled = false;
                }
            }
        }

        function agregarNuevoTanque() {
            contadorTanques++;
            const raiz = document.getElementById('raiz-tanques-fertilizantes');
            
            const bloqueTanque = document.createElement('div');
            bloqueTanque.className = "bg-stone-50 border border-stone-200 rounded-xl p-5 shadow-xs space-y-4 contenedor-bloque-tanque";
            bloqueTanque.id = `bloque_tanque_${contadorTanques}`;
            
            bloqueTanque.innerHTML = `
                <input type="hidden" name="tanques_indices[]" value="${contadorTanques}">

                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 bg-white p-3 rounded-lg border border-stone-100">
                    <div class="flex items-center gap-2 w-full sm:w-1/3">
                        <span class="text-xs font-bold text-gray-500 uppercase whitespace-nowrap">Identificador:</span>
                        <input type="text" name="tanque_${contadorTanques}" placeholder="Ej: Tanque A" required class="w-full border border-gray-300 rounded px-2 py-1 text-xs font-bold text-emerald-800 focus:outline-emerald-500 bg-white">
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" onclick="agregarAccionATanque(${contadorTanques})" class="bg-gray-800 hover:bg-gray-900 text-white text-[11px] font-bold px-3 py-1.5 rounded transition shadow-2xs cursor-pointer">
                            <i class="fa-solid fa-plus mr-1"></i> Añadir Acción
                        </button>
                        <button type="button" onclick="eliminarTanqueCompleto(${contadorTanques})" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-[11px] font-bold px-3 py-1.5 rounded transition shadow-2xs cursor-pointer btn-eliminar-tanque-global">
                            <i class="fa-solid fa-trash-can mr-1"></i> Eliminar Tanque
                        </button>
                    </div>
                </div>

                <div class="overflow-x-auto bg-white rounded-lg border border-stone-200">
                    <table class="w-full text-left text-xs text-gray-600 border-collapse min-w-[700px]">
                        <thead>
                            <tr class="bg-stone-100/80 text-stone-700 font-semibold border-b border-stone-200">
                                <th class="p-2 w-1/2">Acción / Instrucción Texto</th>
                                <th class="p-2 w-1/2">Cantidad / Unidad</th>
                                <th class="p-2 text-center w-12">Quitar</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpo_acciones_tanque_${contadorTanques}" class="divide-y divide-stone-100">
                        </tbody>
                    </table>
                </div>
            `;
            
            raiz.appendChild(bloqueTanque);
            agregarAccionATanque(contadorTanques);
            verLockeoTanques();
        }

        function agregarAccionATanque(idTanque) {
            const tbody = document.getElementById(`cuerpo_acciones_tanque_${idTanque}`);
            const nuevaFilaAccion = document.createElement('tr');
            nuevaFilaAccion.className = "hover:bg-stone-50/40 fila-accion-subdetalle";
            
            nuevaFilaAccion.innerHTML = `
                <td class="p-2 border border-stone-100">
                    <input type="text" name="accion_texto_${idTanque}[]" placeholder="Ej: Aplicar en el segundo riego..." required class="w-full border border-gray-300 rounded p-1.5 text-xs focus:outline-emerald-500">
                </td>
                <td class="p-2 border border-stone-100">
                    <div class="flex flex-col gap-1">
                        <div class="flex gap-1">
                            <input type="number" step="0.01" name="cantidad_${idTanque}[]" required placeholder="Cant." class="w-1/2 border border-gray-300 rounded p-1.5 text-xs focus:outline-emerald-500">
                            <select onchange="evaluarUnidadManual(this)" class="w-1/2 border border-gray-300 rounded p-1.5 text-xs focus:outline-emerald-500 bg-white selector-unidad-base">
                                <option value="g">g</option>
                                <option value="kg">kg</option>
                                <option value="L">L</option>
                                <option value="mL">mL</option>
                                <option value="OTRO">Otro...</option>
                            </select>
                        </div>
                        <input type="text" name="unidad_cantidad_${idTanque}[]" value="g" placeholder="Escriba la unidad..." class="w-full border border-emerald-400 bg-emerald-50/50 rounded p-1 text-xs focus:outline-emerald-500 hidden campo-unidad-manual">
                    </div>
                </td>
                <td class="p-2 border border-stone-100 text-center">
                    <button type="button" onclick="eliminarAccionFila(this, ${idTanque})" class="text-red-500 hover:text-red-700 font-bold text-base cursor-pointer btn-quitar-accion-fila">&times;</button>
                </td>
            `;
            
            tbody.appendChild(nuevaFilaAccion);
            verLockeoAcciones(idTanque);
        }

        function eliminarAccionFila(boton, idTanque) {
            const fila = boton.closest('tr');
            fila.remove();
            verLockeoAcciones(idTanque);
        }

        function eliminarTanqueCompleto(idTanque) {
            const bloque = document.getElementById(`bloque_tanque_${idTanque}`);
            bloque.remove();
            verLockeoTanques();
        }

        function verLockeoAcciones(idTanque) {
            const tbody = document.getElementById(`cuerpo_acciones_tanque_${idTanque}`);
            const filas = tbody.querySelectorAll('.fila-accion-subdetalle');
            if(filas.length === 1) {
                filas[0].querySelector('.btn-quitar-accion-fila').disabled = true;
                filas[0].querySelector('.btn-quitar-accion-fila').classList.add('opacity-30');
            } else {
                filas.forEach(f => {
                    f.querySelector('.btn-quitar-accion-fila').disabled = false;
                    f.querySelector('.btn-quitar-accion-fila').classList.remove('opacity-30');
                });
            }
        }

        function verLockeoTanques() {
            const bloques = document.querySelectorAll('.contenedor-bloque-tanque');
            if(bloques.length === 1) {
                bloques[0].querySelector('.btn-eliminar-tanque-global').disabled = true;
                bloques[0].querySelector('.btn-eliminar-tanque-global').classList.add('opacity-40');
            } else {
                bloques.forEach(b => {
                    b.querySelector('.btn-eliminar-tanque-global').disabled = false;
                    b.querySelector('.btn-eliminar-tanque-global').classList.remove('opacity-40');
                });
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            if(document.getElementById('operador_id').value !== "") {
                filtrarSectoresPorOperador();
            }
            agregarNuevoTanque();
        });
    </script>
</body>

</html>