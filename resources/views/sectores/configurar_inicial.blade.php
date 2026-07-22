<x-guest-layout>
    <div class="mb-4 text-center">
        <div class="inline-flex items-center justify-center bg-amber-100 text-amber-800 rounded-full p-3 mb-2 shadow-sm">
            <i class="fa-solid fa-triangle-exclamation text-xl"></i>
        </div>
        <h2 class="text-lg font-bold text-gray-900 uppercase tracking-wide">
            Registro Obligatorio Requerido
        </h2>
        <p class="text-xs text-gray-600 mt-1">
            Se requiere ingresar las características iniciales del área antes de proceder con las bitácoras diarias.
        </p>
    </div>

    <form method="POST" action="{{ route('sectores.guardar_inicial') }}" class="space-y-4">
        @csrf
        
        <div class="bg-gray-100 border border-gray-300 rounded-lg p-3 text-center font-mono text-sm text-gray-700 font-bold shadow-inner">
            CONFIGURANDO: {{ $sector }}
            <input type="hidden" name="sector" value="{{ $sector }}">
        </div>

        <div>
            <x-input-label for="superficie_m2" value="Superficie en Metros Cuadrados (m²)" />
            <x-text-input id="superficie_m2" class="block mt-1 w-full" type="number" name="superficie_m2" min="1" required placeholder="Ej: 2500" />
            <x-input-error :messages="$errors->get('superficie_m2')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="variedad" value="Variedad del Cultivo" />
            <x-text-input id="variedad" class="block mt-1 w-full" type="text" name="variedad" required placeholder="Ej: Tomate Saladette" />
            <x-input-error :messages="$errors->get('variedad')" class="mt-2" />
        </div>
        
        <div>
            <x-input-label for="numero_plantas" value="Número Total de Plantas" />
            <x-text-input id="numero_plantas" class="block mt-1 w-full" type="number" name="numero_plantas" min="1" step="1" required placeholder="Ej: 5000" />
            <x-input-error :messages="$errors->get('numero_plantas')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="macetas_por_gotero" value="Número de Macetas por Gotero" />
            <x-text-input id="macetas_por_gotero" class="block mt-1 w-full" type="number" name="macetas_por_gotero" min="1" step="1" required placeholder="Ej: 2" />
            <x-input-error :messages="$errors->get('macetas_por_gotero')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="fecha_trasplante" value="Fecha de Trasplante" />
            <x-text-input id="fecha_trasplante" class="block mt-1 w-full" type="date" name="fecha_trasplante" required />
            <x-input-error :messages="$errors->get('fecha_trasplante')" class="mt-2" />
        </div>

        <div class="pt-2">
            <x-primary-button class="w-full justify-center bg-emerald-600 hover:bg-emerald-700 font-bold py-2.5 shadow">
                Guardar Características e Iniciar
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>