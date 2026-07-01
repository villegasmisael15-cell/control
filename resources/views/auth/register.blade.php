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
            <select name="rol" id="rol" onchange="controlarBloqueSectores(this.value)" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 text-sm p-2.5 text-gray-800 bg-white" required>
                <option value="operador" {{ old('rol') === 'operador' ? 'selected' : '' }}>Operador (Manejo de Sectores e Hidroponía)</option>
                <option value="usuario_comercial" {{ old('rol') === 'usuario_comercial' ? 'selected' : '' }}>Recepción Comercial (Nacional Comercial y Exportación)</option>
                <option value="usuario_rechazo" {{ old('rol') === 'usuario_rechazo' ? 'selected' : '' }}>Recepción de Rechazo (Solo Nacional Procesado)</option>
            </select>
            <x-input-error :messages="$errors->get('rol')" class="mt-2" />
        </div>

        <div class="mt-4" id="seccion-completa-sectores">
            <label class="block font-bold text-sm text-gray-700 uppercase tracking-wider mb-2">
                Sectores Asignados:
            </label>

            <div id="contenedor-sectores" class="space-y-2">
                <div class="flex items-center gap-2 de-sector">
                    <div class="flex items-center bg-gray-100 border border-gray-300 rounded-lg px-3 py-2 w-full shadow-sm">
                        <span class="text-gray-500 font-medium select-none mr-1 text-sm">Sector</span>
                        <input type="number" name="sectores[]" id="primer_sector_input" min="1" placeholder="1" required class="w-full bg-transparent border-none outline-none text-sm p-0 focus:ring-0 text-gray-800">
                    </div>
                    <button type="button" onclick="agregarCampoSector()" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold h-10 w-12 rounded-lg flex items-center justify-center transition shadow cursor-pointer">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
            </div>

            <x-input-error :messages="$errors->get('sectores')" class="mt-2" />
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
    function controlarBloqueSectores(puesto) {
        const seccionSectores = document.getElementById('seccion-completa-sectores');
        const primerInput = document.getElementById('primer_sector_input');
        const contenedorSectores = document.getElementById('contenedor-sectores');

        if (puesto === 'operador') {
            seccionSectores.style.display = 'block';
            primerInput.required = true;
        } else {
            seccionSectores.style.display = 'none';
            primerInput.required = false;
            
            // Conservar únicamente el primer renglón limpio y eliminar dinámicos extras
            const renglones = contenedorSectores.querySelectorAll('.de-sector');
            renglones.forEach((renglon, indice) => {
                if (indice === 0) {
                    const input = renglon.querySelector('input');
                    if (input) input.value = '';
                } else {
                    renglon.remove();
                }
            });
        }
    }

    function agregarCampoSector() {
        const contenedor = document.getElementById('contenedor-sectores');
        
        const nuevoDiv = document.createElement('div');
        nuevoDiv.className = 'flex items-center gap-2 de-sector animate-fade-in';
        
        nuevoDiv.innerHTML = `
            <div class="flex items-center bg-gray-100 border border-gray-300 rounded-lg px-3 py-2 w-full shadow-sm">
                <span class="text-gray-500 font-medium select-none mr-1 text-sm">Sector</span>
                <input type="number" name="sectores[]" min="1" placeholder="Otro número" required class="w-full bg-transparent border-none outline-none text-sm p-0 focus:ring-0 text-gray-800">
            </div>
            <button type="button" onclick="eliminarCampoSector(this)" class="bg-red-500 hover:bg-red-600 text-white font-bold h-10 w-12 rounded-lg flex items-center justify-center transition shadow cursor-pointer">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        `;
        
        contenedor.appendChild(nuevoDiv);
    }

    function eliminarCampoSector(boton) {
        boton.closest('.de-sector').remove();
    }

    // Inicialización del estado del formulario al cargar
    document.addEventListener('DOMContentLoaded', function() {
        controlarBloqueSectores(document.getElementById('rol').value);
    });
    </script>
</x-guest-layout>