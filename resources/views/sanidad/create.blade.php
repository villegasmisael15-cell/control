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
                        <select name="sector" id="sector" class="border border-gray-300 rounded-lg w-full p-2 text-sm focus:outline-emerald-500 bg-gray-100" required disabled>
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

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-gray-200 text-sm min-w-[1150px]" id="tabla-agroquimicos">
                            <thead>
                                <tr class="bg-gray-100 text-gray-700 font-semibold text-xs border-b border-gray-200">
                                    <th class="p-2 border border-gray-200 w-32">F. Aplicación</th>
                                    <th class="p-2 border border-gray-200 w-36">Tipo Aplicación</th>
                                    <th class="p-2 border border-gray-200">Producto</th>
                                    <th class="p-2 border border-gray-200 w-64">Dosis / Unidad</th>
                                    <th class="p-2 border border-gray-200 w-20">IS</th>
                                    <th class="p-2 border border-gray-200">Variedad</th>
                                    <th class="p-2 border border-gray-200 w-24">N° Plantas</th>
                                    <th class="p-2 border border-gray-200">Sol. Madre</th>
                                    <th class="p-2 border border-gray-200 w-32">F. Trasplante</th>
                                    <th class="p-2 border border-gray-200">Sol. Diaria</th>
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
                                    <td class="p-1 border border-gray-200"><input type="text" name="variedad[]" class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                                    <td class="p-1 border border-gray-200"><input type="number" name="numero_plantas[]" class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                                    <td class="p-1 border border-gray-200"><input type="text" name="solucion_madre[]" class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                                    <td class="p-1 border border-gray-200"><input type="date" name="fecha_trasplante[]" class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                                    <td class="p-1 border border-gray-200"><input type="text" name="solucion_diaria[]" class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                                    <td class="p-1 border border-gray-200"><input type="text" name="agroquimicos_observaciones[]" placeholder="..." class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                                    <td class="p-1 border border-gray-200 text-center">
                                        <button type="button" onclick="eliminarFila(this)" class="text-red-500 hover:text-red-700 text-sm cursor-pointer disabled:opacity-30" disabled>&times;</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- SUBFORMULARIO 2: MANEJO DE FERTILIZANTES -->
                <div class="space-y-4 pt-4 border-t border-gray-100">
                    <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                        <h3 class="font-bold text-base text-gray-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-flask-vial text-emerald-600"></i>
                            2. Sección: Manejo de Fertilizantes
                        </h3>
                        <button type="button" onclick="agregarFilaFertilizante()" class="bg-gray-800 hover:bg-gray-900 text-white text-xs font-bold px-3 py-1.5 rounded transition shadow-sm cursor-pointer">
                            <i class="fa-solid fa-plus mr-1"></i> Añadir Nutriente
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full border-collapse border border-gray-200 text-sm min-w-[600px]" id="tabla-fertilizantes">
                            <thead>
                                <tr class="bg-gray-100 text-gray-700 font-semibold text-xs border-b border-gray-200">
                                    <th class="p-2 border border-gray-200 w-1/2">Tanque</th>
                                    <!-- CORREGIDO: Encabezado Unificado Cantidad / Unidad -->
                                    <th class="p-2 border border-gray-200 w-1/2">Cantidad / Unidad</th>
                                    <th class="p-2 border border-gray-200 text-center w-12">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200" id="body-fertilizantes">
                                <tr class="hover:bg-gray-50/50">
                                    <td class="p-1.5 border border-gray-200">
                                        <input type="text" name="tanque[]" placeholder="Ej: Tanque A" required class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500">
                                    </td>
                                    <!-- CORREGIDO: Campos Juntos en la Misma Celda con Desglose Híbrido -->
                                    <td class="p-1.5 border border-gray-200">
                                        <div class="flex flex-col gap-1">
                                            <div class="flex gap-1">
                                                <input type="number" step="0.01" name="cantidad[]" required placeholder="Cant." class="w-1/2 border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500">
                                                <select onchange="evaluarUnidadManual(this)" class="w-1/2 border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500 bg-white selector-unidad-base">
                                                    <option value="g">g</option>
                                                    <option value="kg">kg</option>
                                                    <option value="L">L</option>
                                                    <option value="mL">mL</option>
                                                    <option value="OTRO">Otro...</option>
                                                </select>
                                            </div>
                                            <input type="text" name="unidad_cantidad[]" value="g" placeholder="Escriba la unidad..." class="w-full border border-emerald-400 bg-emerald-50/50 rounded p-1 text-xs focus:outline-emerald-500 hidden campo-unidad-manual">
                                        </div>
                                    </td>
                                    <td class="p-1.5 border border-gray-200 text-center">
                                        <button type="button" onclick="eliminarFila(this)" class="text-red-500 hover:text-red-700 text-sm cursor-pointer disabled:opacity-30" disabled>&times;</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-gray-50 p-4 rounded-xl border border-gray-200 mt-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Labores Culturales Relacionadas</label>
                            <input type="text" name="labores_culturales" placeholder="Ej: Poda, deshoje, limpieza de goteros..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-emerald-500 bg-white">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Observaciones Generales de la Mezcla</label>
                            <input type="text" name="fertilizantes_observaciones" placeholder="Ej: Aplicar en el segundo riego del día..." class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-emerald-500 bg-white">
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

        function filtrarSectoresPorOperador() {
            const selectOperador = document.getElementById('operador_id');
            const selectSector = document.getElementById('sector');
            const opcionSeleccionada = selectOperador.options[selectOperador.selectedIndex];
            const cadenaSectores = opcionSeleccionada.getAttribute('data-sectores');

            selectSector.innerHTML = '';

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
                <td class="p-1 border border-gray-200"><input type="text" name="variedad[]" class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                <td class="p-1 border border-gray-200"><input type="number" name="numero_plantas[]" class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                <td class="p-1 border border-gray-200"><input type="text" name="solucion_madre[]" class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                <td class="p-1 border border-gray-200"><input type="date" name="fecha_trasplante[]" class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                <td class="p-1 border border-gray-200"><input type="text" name="solucion_diaria[]" class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                <td class="p-1 border border-gray-200"><input type="text" name="agroquimicos_observaciones[]" placeholder="..." class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                <td class="p-1 border border-gray-200 text-center">
                    <button type="button" onclick="eliminarFila(this)" class="text-red-500 hover:text-red-700 text-sm cursor-pointer">&times;</button>
                </td>
            `;
            tbody.appendChild(nuevaFila);
            verificarBotonesEliminar('body-agroquimicos');
        }

        function agregarFilaFertilizante() {
            const tbody = document.getElementById('body-fertilizantes');
            const nuevaFila = document.createElement('tr');
            nuevaFila.className = "hover:bg-gray-50/50";
            nuevaFila.innerHTML = `
                <td class="p-1.5 border border-gray-200"><input type="text" name="tanque[]" placeholder="Ej: Tanque A" required class="w-full border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500"></td>
                
                <!-- CORREGIDO EN DINÁMICO FERTILIZANTES: Fusión de cantidad y select híbrido -->
                <td class="p-1.5 border border-gray-200">
                    <div class="flex flex-col gap-1">
                        <div class="flex gap-1">
                            <input type="number" step="0.01" name="cantidad[]" required placeholder="Cant." class="w-1/2 border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500">
                            <select onchange="evaluarUnidadManual(this)" class="w-1/2 border border-gray-300 rounded p-1 text-xs focus:outline-emerald-500 bg-white selector-unidad-base">
                                <option value="g">g</option>
                                <option value="kg">kg</option>
                                <option value="L">L</option>
                                <option value="mL">mL</option>
                                <option value="OTRO">Otro...</option>
                            </select>
                        </div>
                        <input type="text" name="unidad_cantidad[]" value="g" placeholder="Escriba la unidad..." class="w-full border border-emerald-400 bg-emerald-50/50 rounded p-1 text-xs focus:outline-emerald-500 hidden campo-unidad-manual">
                    </div>
                </td>
                <td class="p-1.5 border border-gray-200 text-center">
                    <button type="button" onclick="eliminarFila(this)" class="text-red-500 hover:text-red-700 text-sm cursor-pointer">&times;</button>
                </td>
            `;
            tbody.appendChild(nuevaFila);
            verificarBotonesEliminar('body-fertilizantes');
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

        document.addEventListener("DOMContentLoaded", function() {
            if(document.getElementById('operador_id').value !== "") {
                filtrarSectoresPorOperador();
            }
        });
    </script>
</body>

</html>