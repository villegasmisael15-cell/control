<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Bitácora Sanidad y Nutrición - Sistema Control</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

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
                <h2 class="text-xl font-bold text-gray-800">Editar Bitácora de Sanidad y Nutrición</h2>
                <p class="text-xs text-gray-500 mt-1">Modifique los datos generales, aplicaciones o fertirriego de esta orden de trabajo.</p>
            </div>

            <form action="{{ route('sanidad.update', $bitacora->id) }}" method="POST" class="p-6 space-y-8">
                @csrf
                @method('PUT')

                <!-- SECCIÓN MAESTRA -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Fecha Programada</label>
                        <input type="date" name="fecha" value="{{ old('fecha', $bitacora->fecha) }}" required class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-emerald-500 bg-white">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 uppercase mb-1">1. Asignar a Operador:</label>
                        <select name="operador_id" id="operador_id" class="border border-gray-300 rounded-lg w-full p-2 text-sm focus:outline-emerald-500 bg-white" required onchange="filtrarSectoresPorOperador()">
                            <option value="">Seleccione el encargado...</option>
                            @foreach($operadores as $op)
                            <option value="{{ $op->id }}" data-sectores="{{ $op->sectores }}" {{ old('operador_id', $bitacora->operador_id) == $op->id ? 'selected' : '' }}>{{ $op->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 uppercase mb-1">2. Sector / Nave Autorizada:</label>
                        <select name="sector" id="sector" class="border border-gray-300 rounded-lg w-full p-2 text-sm focus:outline-emerald-500 bg-gray-100" required disabled onchange="cambiarDatosPorSector()">
                            <option value="{{ $bitacora->sector }}">{{ $bitacora->sector }}</option>
                        </select>
                    </div>
                </div>

                <!-- SUBFORMULARIO 1: MANEJO DE AGROQUÍMICOS -->
                <div class="space-y-6">
                    <div class="border-b border-gray-200 pb-2 flex items-center justify-between">
                        <h3 class="font-bold text-base text-gray-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-spray-can text-orange-500"></i>
                            1. Sección: Manejo de Agroquímicos
                        </h3>
                        <button type="button" onclick="agregarNuevoBloqueAgroquimico()" class="bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold px-4 py-2 rounded-lg transition shadow-sm cursor-pointer">
                            <i class="fa-solid fa-folder-plus mr-1"></i> Añadir Registro de Aplicación
                        </button>
                    </div>

                    <!-- TARJETAS DE INFORMACIÓN FIJA -->
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

                    <input type="hidden" name="variedad_sector" id="hidden-variedad" value="">
                    <input type="hidden" name="numero_plantas_sector" id="hidden-plantas" value="">
                    <input type="hidden" name="fecha_trasplante_sector" id="hidden-trasplante" value="">

                    <div id="raiz-bloques-agroquimicos" class="space-y-6">
                        <!-- Renderizado por JS -->
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
                        <!-- Renderizado por JS -->
                    </div>

                    @php
                        $primerFert = $bitacora->fertilizantes->first();
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200 mt-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Labores Culturales Realizadas</label>
                            <input type="text" name="labores_culturales" value="{{ $primerFert->labores_culturales ?? '' }}" placeholder="Ej: Poda, deshoje..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Observaciones Generales de la Mezcla</label>
                            <input type="text" name="fertilizantes_observaciones" value="{{ $primerFert->observaciones ?? '' }}" placeholder="Ej: Monitorear conductividad..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-emerald-500 bg-white">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-gray-200">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-6 py-3 rounded-lg shadow-md transition cursor-pointer">
                        <i class="fa-solid fa-floppy-disk mr-2"></i> Actualizar Bitácora
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script>
        const mapaSectoresData = @json($sectoresConVariedad);
        const sectorActual = "{{ $bitacora->sector }}";
        const agroquimicosExistentes = @json($bitacora->agroquimicos);
        const fertilizantesExistentes = @json($bitacora->fertilizantes->groupBy('tanque'));

        let contadorTanques = 0;
        let contadorAgroquimicos = 0;

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

        function filtrarSectoresPorOperador(sectorSeleccionar = null) {
            const selectOperador = document.getElementById('operador_id');
            const selectSector = document.getElementById('sector');
            const opcionSeleccionada = selectOperador.options[selectOperador.selectedIndex];
            const cadenaSectores = opcionSeleccionada ? opcionSeleccionada.getAttribute('data-sectores') : '';

            selectSector.innerHTML = '';

            if (!cadenaSectores || cadenaSectores.trim() === '') {
                selectSector.innerHTML = '<option value="">Este operador no tiene sectores asignados</option>';
                selectSector.disabled = true;
                return;
            }

            selectSector.disabled = false;
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
                    if (sector === sectorSeleccionar) {
                        opt.selected = true;
                    }
                    selectSector.appendChild(opt);
                }
            });
            cambiarDatosPorSector();
        }

        function cambiarDatosPorSector() {
            const sectorSeleccionado = document.getElementById('sector').value.trim();
            if (sectorSeleccionado === "" || !mapaSectoresData.hasOwnProperty(sectorSeleccionado)) {
                document.getElementById('txt-variedad').textContent = "—";
                document.getElementById('txt-plantas').textContent = "—";
                document.getElementById('txt-trasplante').textContent = "—";
                document.getElementById('hidden-variedad').value = "";
                document.getElementById('hidden-plantas').value = "";
                document.getElementById('hidden-trasplante').value = "";
            } else {
                const data = mapaSectoresData[sectorSeleccionado];
                document.getElementById('txt-variedad').textContent = data.variedad || "—";
                document.getElementById('txt-plantas').textContent = formatearNumero(data.numero_plantas);
                document.getElementById('txt-trasplante').textContent = formatearFecha(data.fecha_trasplante);
                document.getElementById('hidden-variedad').value = data.variedad || "";
                document.getElementById('hidden-plantas').value = data.numero_plantas || "";
                document.getElementById('hidden-trasplante').value = data.fecha_trasplante || "";
            }
        }

        function agregarNuevoBloqueAgroquimico(datosFecha = '', datosAplicacion = '') {
            contadorAgroquimicos++;
            const raiz = document.getElementById('raiz-bloques-agroquimicos');
            
            const bloqueAgro = document.createElement('div');
            bloqueAgro.className = "bg-stone-50 border border-stone-200 rounded-xl p-5 shadow-xs space-y-4 contenedor-bloque-agro";
            bloqueAgro.id = `bloque_agro_${contadorAgroquimicos}`;
            
            bloqueAgro.innerHTML = `
                <input type="hidden" name="agro_indices[]" value="${contadorAgroquimicos}">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3 rounded-lg border border-stone-100">
                    <div class="flex flex-wrap items-center gap-3 w-full sm:w-2/3">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-gray-500 uppercase whitespace-nowrap">Fecha:</span>
                            <input type="date" name="fecha_aplicacion_${contadorAgroquimicos}" value="${datosFecha || '{{ date("Y-m-d") }}'}" required class="border border-gray-300 rounded px-2 py-1 text-xs focus:outline-emerald-500 bg-white">
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-bold text-gray-500 uppercase whitespace-nowrap">Tipo Aplicación:</span>
                            <select name="aplicacion_${contadorAgroquimicos}" required class="border border-gray-300 rounded px-2 py-1 text-xs focus:outline-emerald-500 bg-white font-semibold text-emerald-800">
                                <option value="RIEGO" ${datosAplicacion === 'RIEGO' ? 'selected' : ''}>RIEGO</option>
                                <option value="FOLIAR" ${datosAplicacion === 'FOLIAR' ? 'selected' : ''}>FOLIAR</option>
                                <option value="DRENCH" ${datosAplicacion === 'DRENCH' ? 'selected' : ''}>DRENCH</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex gap-2 justify-end">
                        <button type="button" onclick="agregarProductoToAgro(${contadorAgroquimicos})" class="bg-gray-800 hover:bg-gray-900 text-white text-[11px] font-bold px-3 py-1.5 rounded transition shadow-2xs cursor-pointer">
                            <i class="fa-solid fa-plus mr-1"></i> Añadir Producto
                        </button>
                        <button type="button" onclick="eliminarBloqueAgroCompleto(${contadorAgroquimicos})" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-[11px] font-bold px-3 py-1.5 rounded transition shadow-2xs cursor-pointer btn-eliminar-agro-global">
                            <i class="fa-solid fa-trash-can mr-1"></i> Eliminar Registro
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto bg-white rounded-lg border border-stone-200">
                    <table class="w-full text-left text-xs text-gray-600 border-collapse min-w-[750px]">
                        <thead>
                            <tr class="bg-stone-100/80 text-stone-700 font-semibold border-b border-stone-200">
                                <th class="p-2 w-1/3">Producto</th>
                                <th class="p-2 w-1/4">Dosis / Unidad</th>
                                <th class="p-2 w-20 text-center">IS</th>
                                <th class="p-2 w-1/3">Observaciones</th>
                                <th class="p-2 text-center w-12">Quitar</th>
                            </tr>
                        </thead>
                        <tbody id="cuerpo_productos_agro_${contadorAgroquimicos}" class="divide-y divide-stone-100"></tbody>
                    </table>
                </div>
            `;
            raiz.appendChild(bloqueAgro);
            verLockeoAgroBloques();
        }

        function agregarProductoToAgro(idAgro, prod = '', dosis = '', unidad = 'mL', is = '', obs = '') {
            const tbody = document.getElementById(`cuerpo_productos_agro_${idAgro}`);
            const nuevaFila = document.createElement('tr');
            nuevaFila.className = "hover:bg-stone-50/40 fila-producto-subdetalle";
            
            const esUnidadEstandar = ['mL', 'L', 'g', 'kg'].includes(unidad);
            const selectVal = esUnidadEstandar ? unidad : 'OTRO';
            const hiddenClass = esUnidadEstandar ? 'hidden' : '';

            nuevaFila.innerHTML = `
                <td class="p-2 border border-stone-100">
                    <input type="text" name="producto_${idAgro}[]" value="${prod}" placeholder="Ej: Confidor" required class="w-full border border-gray-300 rounded p-1.5 text-xs focus:outline-emerald-500">
                </td>
                <td class="p-2 border border-stone-100">
                    <div class="flex flex-col gap-1">
                        <div class="flex gap-1">
                            <input type="number" step="0.01" name="dosis_${idAgro}[]" value="${dosis}" required placeholder="Cant." class="w-1/2 border border-gray-300 rounded p-1.5 text-xs focus:outline-emerald-500">
                            <select onchange="evaluarUnidadManual(this)" class="w-1/2 border border-gray-300 rounded p-1.5 text-xs focus:outline-emerald-500 bg-white selector-unidad-base">
                                <option value="mL" ${selectVal === 'mL' ? 'selected' : ''}>mL</option>
                                <option value="L" ${selectVal === 'L' ? 'selected' : ''}>L</option>
                                <option value="g" ${selectVal === 'g' ? 'selected' : ''}>g</option>
                                <option value="kg" ${selectVal === 'kg' ? 'selected' : ''}>kg</option>
                                <option value="OTRO" ${selectVal === 'OTRO' ? 'selected' : ''}>Otro...</option>
                            </select>
                        </div>
                        <input type="text" name="unidad_dosis_${idAgro}[]" value="${unidad}" placeholder="Escriba la unidad..." class="w-full border border-emerald-400 bg-emerald-50/50 rounded p-1 text-xs focus:outline-emerald-500 ${hiddenClass} campo-unidad-manual">
                    </div>
                </td>
                <td class="p-2 border border-stone-100 text-center">
                    <input type="number" name="is_intervalo_seguridad_${idAgro}[]" value="${is}" placeholder="0" class="w-full border border-gray-300 rounded p-1.5 text-xs focus:outline-emerald-500 text-center">
                </td>
                <td class="p-2 border border-stone-100">
                    <input type="text" name="agroquimicos_observaciones_${idAgro}[]" value="${obs}" placeholder="..." class="w-full border border-gray-300 rounded p-1.5 text-xs focus:outline-emerald-500">
                </td>
                <td class="p-2 border border-stone-100 text-center">
                    <button type="button" onclick="eliminarProductoFila(this, ${idAgro})" class="text-red-500 hover:text-red-700 font-bold text-base cursor-pointer btn-quitar-producto-fila">&times;</button>
                </td>
            `;
            tbody.appendChild(nuevaFila);
            verLockeoProductosAgro(idAgro);
        }

        function eliminarProductoFila(boton, idAgro) {
            const fila = boton.closest('tr');
            fila.remove();
            verLockeoProductosAgro(idAgro);
        }

        function eliminarBloqueAgroCompleto(idAgro) {
            document.getElementById(`bloque_agro_${idAgro}`).remove();
            verLockeoAgroBloques();
        }

        function verLockeoProductosAgro(idAgro) {
            const tbody = document.getElementById(`cuerpo_productos_agro_${idAgro}`);
            const filas = tbody.querySelectorAll('.fila-producto-subdetalle');
            filas.forEach((f, idx) => {
                const btn = f.querySelector('.btn-quitar-producto-fila');
                btn.disabled = (filas.length === 1);
                btn.classList.toggle('opacity-30', filas.length === 1);
            });
        }

        function verLockeoAgroBloques() {
            const bloques = document.querySelectorAll('.contenedor-bloque-agro');
            bloques.forEach(b => {
                const btn = b.querySelector('.btn-eliminar-agro-global');
                btn.disabled = (bloques.length === 1);
                btn.classList.toggle('opacity-40', bloques.length === 1);
            });
        }

        function agregarNuevoTanque(nombreTanque = '', tipoSolucion = 'SOLUCION MADRE') {
            contadorTanques++;
            const raiz = document.getElementById('raiz-tanques-fertilizantes');
            
            const bloqueTanque = document.createElement('div');
            bloqueTanque.className = "bg-stone-50 border border-stone-200 rounded-xl p-5 shadow-xs space-y-4 contenedor-bloque-tanque";
            bloqueTanque.id = `bloque_tanque_${contadorTanques}`;
            
            bloqueTanque.innerHTML = `
                <input type="hidden" name="tanques_indices[]" value="${contadorTanques}">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white p-3 rounded-lg border border-stone-100">
                    <div class="flex flex-wrap items-center gap-3 w-full sm:w-2/3">
                        <div class="flex items-center gap-1.5 w-full sm:w-1/2">
                            <span class="text-xs font-bold text-gray-500 uppercase whitespace-nowrap">Identificador:</span>
                            <input type="text" name="tanque_${contadorTanques}" value="${nombreTanque}" placeholder="Ej: Tanque A" required class="w-full border border-gray-300 rounded px-2 py-1 text-xs font-bold text-emerald-800 focus:outline-emerald-500 bg-white">
                        </div>
                        <div class="flex items-center gap-1.5 w-full sm:w-1/2">
                            <span class="text-xs font-bold text-gray-500 uppercase whitespace-nowrap">Tipo Solución:</span>
                            <select name="tipo_solucion_${contadorTanques}" required class="w-full border border-gray-300 rounded px-2 py-1 text-xs focus:outline-emerald-500 bg-white font-medium text-emerald-800">
                                <option value="SOLUCION MADRE" ${tipoSolucion === 'SOLUCION MADRE' ? 'selected' : ''}>Solución Madre</option>
                                <option value="SOLUCION DIARIA" ${tipoSolucion === 'SOLUCION DIARIA' ? 'selected' : ''}>Solución Diaria</option>
                            </select>
                        </div>
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
                        <tbody id="cuerpo_acciones_tanque_${contadorTanques}" class="divide-y divide-stone-100"></tbody>
                    </table>
                </div>
            `;
            raiz.appendChild(bloqueTanque);
            verLockeoTanques();
        }

        function agregarAccionATanque(idTanque, accion = '', cantidad = '', unidad = 'g') {
            const tbody = document.getElementById(`cuerpo_acciones_tanque_${idTanque}`);
            const nuevaFilaAccion = document.createElement('tr');
            nuevaFilaAccion.className = "hover:bg-stone-50/40 fila-accion-subdetalle";
            
            const esUnidadEstandar = ['g', 'kg', 'L', 'mL'].includes(unidad);
            const selectVal = esUnidadEstandar ? unidad : 'OTRO';
            const hiddenClass = esUnidadEstandar ? 'hidden' : '';

            nuevaFilaAccion.innerHTML = `
                <td class="p-2 border border-stone-100">
                    <input type="text" name="accion_texto_${idTanque}[]" value="${accion}" placeholder="Ej: Aplicar en el segundo riego..." required class="w-full border border-gray-300 rounded p-1.5 text-xs focus:outline-emerald-500">
                </td>
                <td class="p-2 border border-stone-100">
                    <div class="flex flex-col gap-1">
                        <div class="flex gap-1">
                            <input type="number" step="0.01" name="cantidad_${idTanque}[]" value="${cantidad}" required placeholder="Cant." class="w-1/2 border border-gray-300 rounded p-1.5 text-xs focus:outline-emerald-500">
                            <select onchange="evaluarUnidadManual(this)" class="w-1/2 border border-gray-300 rounded p-1.5 text-xs focus:outline-emerald-500 bg-white selector-unidad-base">
                                <option value="g" ${selectVal === 'g' ? 'selected' : ''}>g</option>
                                <option value="kg" ${selectVal === 'kg' ? 'selected' : ''}>kg</option>
                                <option value="L" ${selectVal === 'L' ? 'selected' : ''}>L</option>
                                <option value="mL" ${selectVal === 'mL' ? 'selected' : ''}>mL</option>
                                <option value="OTRO" ${selectVal === 'OTRO' ? 'selected' : ''}>Otro...</option>
                            </select>
                        </div>
                        <input type="text" name="unidad_cantidad_${idTanque}[]" value="${unidad}" placeholder="Escriba la unidad..." class="w-full border border-emerald-400 bg-emerald-50/50 rounded p-1 text-xs focus:outline-emerald-500 ${hiddenClass} campo-unidad-manual">
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
            boton.closest('tr').remove();
            verLockeoAcciones(idTanque);
        }

        function eliminarTanqueCompleto(idTanque) {
            document.getElementById(`bloque_tanque_${idTanque}`).remove();
            verLockeoTanques();
        }

        function verLockeoAcciones(idTanque) {
            const tbody = document.getElementById(`cuerpo_acciones_tanque_${idTanque}`);
            const filas = tbody.querySelectorAll('.fila-accion-subdetalle');
            filas.forEach(f => {
                const btn = f.querySelector('.btn-quitar-accion-fila');
                btn.disabled = (filas.length === 1);
                btn.classList.toggle('opacity-30', filas.length === 1);
            });
        }

        function verLockeoTanques() {
            const bloques = document.querySelectorAll('.contenedor-bloque-tanque');
            bloques.forEach(b => {
                const btn = b.querySelector('.btn-eliminar-tanque-global');
                btn.disabled = (bloques.length === 1);
                btn.classList.toggle('opacity-40', bloques.length === 1);
            });
        }

        // Precarga de datos existentes al cargar la vista de edición
        document.addEventListener("DOMContentLoaded", function() {
            const operadorSelect = document.getElementById('operador_id');
            if(operadorSelect.value !== "") {
                filtrarSectoresPorOperador(sectorActual);
            }

            // Precargar Agroquímicos existentes agrupados
            if (agroquimicosExistentes.length > 0) {
                const gruposAgro = {};
                agroquimicosExistentes.forEach(item => {
                    const clave = item.fecha_aplicacion + '_' + item.aplicacion;
                    if (!gruposAgro[clave]) gruposAgro[clave] = [];
                    gruposAgro[clave].push(item);
                });

                Object.keys(gruposAgro).forEach(clave => {
                    const grupo = gruposAgro[clave];
                    agregarNuevoBloqueAgroquimico(grupo[0].fecha_aplicacion, grupo[0].aplicacion);
                    const idActual = contadorAgroquimicos;
                    grupo.forEach((prod, index) => {
                        if(index === 0) {
                            // Reemplazar la primera fila vacía que crea por defecto
                            const tbody = document.getElementById(`cuerpo_productos_agro_${idActual}`);
                            tbody.innerHTML = '';
                        }
                        agregarProductoToAgro(idActual, prod.producto, prod.dosis, prod.unidad_dosis, prod.is_intervalo_seguridad, prod.observaciones);
                    });
                });
            } else {
                agregarNuevoBloqueAgroquimico();
            }

            // Precargar Fertilizantes existentes por tanque
            const nombresTanques = Object.keys(fertilizantesExistentes);
            if (nombresTanques.length > 0) {
                nombresTanques.forEach(nombreTanque => {
                    const acciones = fertilizantesExistentes[nombreTanque];
                    const tipoSol = acciones[0].tipo_solucion || 'SOLUCION MADRE';
                    agregarNuevoTanque(nombreTanque, tipoSol);
                    const idTanqueActual = contadorTanques;
                    
                    // Limpiar la fila vacía inicial
                    const tbodyTanque = document.getElementById(`cuerpo_acciones_tanque_${idTanqueActual}`);
                    tbodyTanque.innerHTML = '';

                    acciones.forEach(acc => {
                        agregarAccionATanque(idTanqueActual, acc.accion, acc.cantidad, acc.unidad_cantidad);
                    });
                });
            } else {
                agregarNuevoTanque();
            }
        });
    </script>
</body>

</html>