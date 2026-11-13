<x-guest-layout>
    <div class="mb-4 text-center">
        <div class="inline-flex items-center justify-center bg-emerald-100 text-emerald-800 rounded-full p-3 mb-2 shadow-sm">
            <i class="fa-solid fa-plus text-xl"></i>
        </div>
        <h2 class="text-lg font-bold text-gray-900 uppercase tracking-wide">
            Agregar Nuevo Sector
        </h2>
        <p class="text-xs text-gray-600 mt-1">
            Registra un nuevo invernadero o agrega un sector a uno existente.
        </p>
    </div>

    <form method="POST" action="{{ route('sectores.almacenar') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="invernadero" value="Nombre del Invernadero" />
            <x-text-input id="invernadero" class="block mt-1 w-full text-sm" type="text" name="invernadero" required placeholder="Ej: Invernadero 1" value="{{ old('invernadero') }}" />
            <x-input-error :messages="$errors->get('invernadero')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="tipo" value="Tipo de Cultivo / Sistema" />
            <select name="tipo" id="tipo" class="block mt-1 w-full border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 rounded-md shadow-sm text-sm text-gray-800" required>
                <option value="hidroponia" {{ old('tipo') == 'hidroponia' ? 'selected' : '' }}>Hidroponía</option>
                <option value="suelo" {{ old('tipo') == 'suelo' ? 'selected' : '' }}>Suelo</option>
            </select>
            <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="sector" value="Nombre o Número del Sector" />
            <x-text-input id="sector" class="block mt-1 w-full text-sm" type="text" name="sector" required placeholder="Ej: Sector 1" value="{{ old('sector') }}" />
            <x-input-error :messages="$errors->get('sector')" class="mt-2" />
        </div>

        <div class="pt-4 flex items-center justify-between gap-4">
            <a href="{{ route('dashboard') }}" class="text-sm text-gray-600 hover:text-gray-900 underline font-semibold">
                Cancelar
            </a>
            <x-primary-button class="bg-emerald-600 hover:bg-emerald-700 font-bold py-2.5 px-6 shadow">
                Guardar Sector
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>