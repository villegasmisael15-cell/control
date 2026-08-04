<x-guest-layout>
    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label for="rol" class="block font-bold text-sm text-gray-700 uppercase tracking-wider mb-2">
                Puesto / Función:
            </label>
            <select name="rol" id="rol" onchange="controlarBloqueRol(this.value)" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5 text-gray-800 bg-white" required>
                <option value="" disabled selected>Selecciona tu puesto...</option>
                <option value="dueno" {{ old('rol') === 'dueno' ? 'selected' : '' }}>Dueño / Administrador de Invernaderos</option>
                <option value="operador" {{ old('rol') === 'operador' ? 'selected' : '' }}>Operador (Selección de Invernaderos y Sectores)</option>
                <option value="usuario_comercial" {{ old('rol') === 'usuario_comercial' ? 'selected' : '' }}>Recepción Comercial (Nacional Comercial y Exportación)</option>
                <option value="usuario_rechazo" {{ old('rol') === 'usuario_rechazo' ? 'selected' : '' }}>Recepción de Rechazo (Solo Nacional Procesado)</option>
            </select>
            <x-input-error :messages="$errors->get('rol')" class="mt-2" />
        </div>

        <!-- SECCIÓN PARA DUEÑO: SUS INVERNADEROS Y SECTORES AL REGISTRARSE -->
        <div class="mt-4 p-4 bg-gray-50 border border-gray-300 rounded-lg shadow-sm space-y-4" id="seccion-dueno" style="display: none;">
            <div class="flex justify-between items-center border-b pb-2">
                <h3 class="font-bold text-sm text-gray-700 uppercase tracking-wider">Registrar Mis Invernaderos y Sectores</h3>
                <button type="button" onclick="agregarInvernaderoDueno()" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs px-3 py-1.5 rounded-lg transition shadow cursor-pointer">
                    <i class="fa-solid fa-plus mr-1"></i> Agregar Invernadero
                </button>
            </div>

            <div id="contenedor-invernaderos-dueno" class="space-y-3">
                <div class="p-3 bg-white border border-gray-200 rounded-md shadow-sm bloque-inv-dueno space-y-3">
                    <div class="flex items-center justify-between gap-2">
                        <div class="w-full">
                            <label class="block text-xs font-semibold text-gray-600 uppercase">Nombre del Invernadero</label>
                            <input type="text" name="invernaderos_dueno[0][nombre]" placeholder="Ej: Invernadero 1" class="w-full mt-1 bg-white border border-gray-300 rounded-md text-sm p-2 text-gray-800">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Sectores (separados por comas)</label>
                        <input type="text" name="invernaderos_dueno[0][sectores]" placeholder="Ej: 1, 2, 3" class="w-full bg-white border border-gray-300 rounded-md text-sm p-2 text-gray-800">
                    </div>
                </div>
            </div>
            <x-input-error :messages="$errors->get('invernaderos_dueno')" class="mt-2" />
        </div>

        <!-- SECCIÓN PARA OPERADOR: SELECCIÓN DE DUEÑO Y SUS INVERNADEROS/SECTORES -->
        <div class="mt-4 p-4 bg-gray-50 border border-gray-300 rounded-lg shadow-sm space-y-4" id="seccion-operador" style="display: none;">
            <h3 class="font-bold text-sm text-gray-700 uppercase tracking-wider border-b pb-2">Asignación de Invernaderos y Sectores</h3>

            <div>
                <label for="dueno_id" class="block text-xs font-semibold text-gray-600 uppercase mb-1">Selecciona al Dueño:</label>
                <select name="dueno_id" id="dueno_id" onchange="filtrarSectoresPorDueno(this.value)" class="w-full bg-white border border-gray-300 rounded-md text-sm p-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-800">
                    <option value="">Seleccione un Dueño...</option>
                    @foreach($duenos as $dueno)
                        <option value="{{ $dueno->id }}" {{ old('dueno_id') == $dueno->id ? 'selected' : '' }}>
                            {{ $dueno->name }} ({{ $dueno->email }})
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('dueno_id')" class="mt-2" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-600 uppercase mb-2">Selecciona uno o más Sectores e Invernaderos:</label>
                <div id="lista-sectores-disponibles" class="max-h-48 overflow-y-auto space-y-2 border border-gray-200 p-3 bg-white rounded-md">
                    <p class="text-xs text-gray-500 italic">Primero selecciona un dueño para ver sus invernaderos y sectores.</p>
                </div>
                <x-input-error :messages="$errors->get('seleccion_sectores')" class="mt-2" />
            </div>
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Ya registrado?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Registrarse') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        const sectoresPorDueno = @json($sectoresAgrupadosPorDueno);
        let contadorInvDueno = 1;

        function controlarBloqueRol(puesto) {
            const seccionDueno = document.getElementById('seccion-dueno');
            const seccionOperador = document.getElementById('seccion-operador');
            const duenoIdInput = document.getElementById('dueno_id');
            
            seccionDueno.style.display = 'none';
            seccionOperador.style.display = 'none';
            duenoIdInput.required = false;

            if (puesto === 'dueno') {
                seccionDueno.style.display = 'block';
            } else if (puesto === 'operador') {
                seccionOperador.style.display = 'block';
                duenoIdInput.required = true;
            } else {
                duenoIdInput.value = '';
                document.getElementById('lista-sectores-disponibles').innerHTML = '<p class="text-xs text-gray-500 italic">Primero selecciona un dueño para ver sus invernaderos y sectores.</p>';
            }
        }

        function agregarInvernaderoDueno() {
            const contenedor = document.getElementById('contenedor-invernaderos-dueno');
            const nuevoDiv = document.createElement('div');
            nuevoDiv.className = 'p-3 bg-white border border-gray-200 rounded-md shadow-sm bloque-inv-dueno space-y-3';
            
            nuevoDiv.innerHTML = `
                <div class="flex items-center justify-between gap-2">
                    <div class="w-full">
                        <label class="block text-xs font-semibold text-gray-600 uppercase">Nombre del Invernadero</label>
                        <input type="text" name="invernaderos_dueno[${contadorInvDueno}][nombre]" placeholder="Ej: Invernadero ${contadorInvDueno + 1}" class="w-full mt-1 bg-white border border-gray-300 rounded-md text-sm p-2 text-gray-800">
                    </div>
                    <button type="button" onclick="this.closest('.bloque-inv-dueno').remove()" class="bg-red-500 hover:bg-red-600 text-white font-bold h-10 w-12 rounded-lg flex items-center justify-center cursor-pointer self-end">✕</button>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase mb-1">Sectores (separados por comas)</label>
                    <input type="text" name="invernaderos_dueno[${contadorInvDueno}][sectores]" placeholder="Ej: 1, 2" class="w-full bg-white border border-gray-300 rounded-md text-sm p-2 text-gray-800">
                </div>
            `;
            contenedor.appendChild(nuevoDiv);
            contadorInvDueno++; // Incrementa de forma segura para que cada bloque tenga su índice único
        }

        function filtrarSectoresPorDueno(duenoId) {
            const contenedor = document.getElementById('lista-sectores-disponibles');
            contenedor.innerHTML = '';

            if (!duenoId || !sectoresPorDueno[duenoId] || sectoresPorDueno[duenoId].length === 0) {
                contenedor.innerHTML = '<p class="text-xs text-gray-500 italic">Este dueño no tiene invernaderos ni sectores registrados aún.</p>';
                return;
            }

            sectoresPorDueno[duenoId].forEach(item => {
                const valorUnico = item.invernadero + '|' + item.sector;
                
                const div = document.createElement('label');
                div.className = 'flex items-center gap-2 p-1.5 hover:bg-gray-50 rounded cursor-pointer text-sm text-gray-700';
                div.innerHTML = `
                    <input type="checkbox" name="seleccion_sectores[]" value="${valorUnico}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <span class="font-medium"><i class="fa-solid fa-house-chimney text-emerald-600 mr-1"></i> ${item.invernadero}</span> — <span>${item.sector}</span>
                `;
                contenedor.appendChild(div);
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            const rolSelect = document.getElementById('rol');
            if (rolSelect.value) {
                controlarBloqueRol(rolSelect.value);
            }

            const duenoIdOld = document.getElementById('dueno_id').value;
            if (duenoIdOld && rolSelect.value === 'operador') {
                filtrarSectoresPorDueno(duenoIdOld);
            }
        });
    </script>
</x-guest-layout>