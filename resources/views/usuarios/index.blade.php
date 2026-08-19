<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administración de Usuarios - Sistema Control</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans antialiased min-h-full flex flex-col">

    <nav class="bg-emerald-600 text-white shadow-md">
        <div class="max-w-[95%] mx-auto px-3 sm:px-4 h-14 sm:h-16 flex items-center justify-between gap-2">
            <!-- Logotipo compacto -->
            <div class="flex items-center min-w-0">
                <i class="fa-solid fa-leaf text-lg sm:text-2xl mr-1.5 sm:mr-2 text-emerald-200"></i>
                <span class="font-bold text-sm sm:text-xl tracking-wider truncate">SISTEMA CONTROL</span>
            </div>

            <!-- Acciones adaptadas con truncamiento de texto -->
            <div class="flex items-center gap-1.5 sm:gap-3 text-xs shrink-0">
                <span class="bg-emerald-700/80 px-2.5 py-1 rounded-md flex items-center gap-1 max-w-[120px] sm:max-w-none truncate" title="{{ auth()->user()->name }}">
                    <i class="fa-solid fa-user text-[10px]"></i>
                    <span class="truncate">{{ auth()->user()->name }}</span>
                </span>
                <a href="{{ route('dashboard') }}" class="bg-emerald-700 hover:bg-emerald-800 px-2.5 sm:px-3.5 py-1.5 rounded-md transition flex items-center gap-1 font-medium shadow-2xs whitespace-nowrap">
                    <i class="fa-solid fa-circle-chevron-left text-[10px]"></i>
                    <span class="hidden xs:inline">Volver al Panel</span>
                    <span class="inline xs:hidden">Panel</span>
                </a>
            </div>
        </div>
    </nav>

    <main class="max-w-[95%] mx-auto px-4 py-8 w-full flex-grow">

        @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-100 border-l-4 border-emerald-500 text-emerald-800 rounded shadow-sm text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-check"></i> {{ session('success') }}
        </div>
        @endif

        @if(session('error'))
        <div class="mb-6 p-4 bg-red-100 border-l-4 border-red-500 text-red-800 rounded shadow-sm text-sm flex items-center gap-2">
            <i class="fa-solid fa-circle-exclamation"></i> {{ session('error') }}
        </div>
        @endif

        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Control de Usuarios y Accesos</h1>
            <p class="text-gray-600 text-sm mt-1">Asigna roles o elimina cuentas de usuarios para controlar los privilegios en la plataforma.</p>
        </div>

        <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 border-b border-gray-200 text-gray-600 text-xs font-bold uppercase tracking-wider">
                            <th class="py-4 px-6">Nombre</th>
                            <th class="py-4 px-6">Correo Electrónico</th>
                            <th class="py-4 px-6">Fecha de Registro</th>
                            <th class="py-4 px-6 text-center">Rol Actual</th>
                            <th class="py-4 px-6 text-center">Cambiar Permisos</th>
                            @if(str_contains(auth()->user()->rol, 'admin'))
                            <th class="py-4 px-6 text-center">Acción</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700 text-sm">
                        @foreach($usuarios as $user)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="py-4 px-6 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="py-4 px-6 text-gray-600">{{ $user->email }}</td>
                            <td class="py-4 px-6 text-gray-500">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-4 px-6 text-center">
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if(str_contains($user->rol, 'administrador')) bg-purple-100 text-purple-800
                                    @elseif(str_contains($user->rol, 'admin_general')) bg-indigo-100 text-indigo-800
                                    @elseif(str_contains($user->rol, 'dueno')) bg-emerald-100 text-emerald-800
                                    @elseif(str_contains($user->rol, 'operador') && str_contains($user->rol, 'usuario_comercial')) bg-teal-100 text-teal-800
                                    @elseif(str_contains($user->rol, 'operador')) bg-blue-100 text-blue-800
                                    @elseif(str_contains($user->rol, 'usuario_comercial')) bg-amber-100 text-amber-800
                                    @elseif(str_contains($user->rol, 'usuario_rechazo')) bg-rose-100 text-rose-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ strtoupper(str_replace('_', ' ', $user->rol)) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if(auth()->id() !== $user->id)
                                <form action="{{ route('usuarios.cambiarRol', $user->id) }}" method="POST" class="inline-flex">
                                    @csrf
                                    @method('PATCH')

                                    <select name="rol" onchange="this.form.submit()" class="bg-gray-50 border border-gray-300 text-gray-700 text-xs rounded-lg p-1.5 cursor-pointer">
                                        <option value="operador" {{ trim($user->rol) === 'operador' ? 'selected' : '' }}>Operador</option>
                                        <option value="operador,usuario_comercial" {{ trim($user->rol) === 'operador,usuario_comercial' ? 'selected' : '' }}>Operador, Usuario Comercial</option>
                                        <option value="usuario_comercial" {{ trim($user->rol) === 'usuario_comercial' ? 'selected' : '' }}>Usuario Comercial</option>
                                        <option value="dueno" {{ trim($user->rol) === 'dueno' ? 'selected' : '' }}>Dueño</option>
                                        <option value="administrador" {{ trim($user->rol) === 'administrador' ? 'selected' : '' }}>Admin Participativo</option>
                                        <option value="admin_general" {{ trim($user->rol) === 'admin_general' ? 'selected' : '' }}>Admin General</option>
                                        <option value="usuario_rechazo" {{ trim($user->rol) === 'usuario_rechazo' ? 'selected' : '' }}>Rechazo</option>
                                    </select>
                                </form>
                                @else
                                <span class="text-xs text-gray-400 italic">Usuario Actual (Tú)</span>
                                @endif
                            </td>

                            {{-- Botón de Eliminar que abre el Modal Personalizado --}}
                            @if(str_contains(auth()->user()->rol, 'admin'))
                            <td class="py-4 px-6 text-center">
                                @if(auth()->id() !== $user->id)
                                <form id="form-delete-{{ $user->id }}" action="{{ route('usuarios.destroy', $user->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" onclick="abrirModalEliminar('form-delete-{{ $user->id }}', '{{ addslashes($user->name) }}')" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-2 rounded-lg transition cursor-pointer" title="Eliminar Usuario">
                                        <i class="fa-solid fa-trash-can text-sm"></i>
                                    </button>
                                </form>
                                @else
                                <span class="text-xs text-gray-400">-</span>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <!-- MODAL PERSONALIZADO DE CONFIRMACIÓN -->
    <div id="modalConfirmarEliminar" class="fixed inset-0 bg-gray-900/60 backdrop-blur-xs hidden items-center justify-center z-50 p-4 transition-opacity">
        <div id="modalContenidoEliminar" class="bg-white rounded-2xl shadow-2xl border border-gray-100 w-full max-w-md p-6 transform scale-95 opacity-0 transition-all duration-200 flex flex-col items-center text-center">
            
            <div class="w-14 h-14 bg-red-100 text-red-600 rounded-full flex items-center justify-center mb-4 text-2xl shadow-inner">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>

            <h3 class="text-lg font-bold text-gray-900">¿Eliminar usuario?</h3>
            <p class="text-gray-500 text-sm mt-2">
                ¿Estás seguro de que deseas eliminar permanentemente a <span id="nombreUsuarioEliminar" class="font-bold text-gray-800"></span>? Esta acción no se puede deshacer.
            </p>

            <div class="flex items-center justify-center gap-3 w-full mt-6">
                <button type="button" onclick="cerrarModalEliminar()" class="w-1/2 py-2.5 px-4 border border-gray-300 text-gray-700 text-sm font-semibold rounded-xl hover:bg-gray-50 transition cursor-pointer">
                    Cancelar
                </button>
                <button type="button" id="btnConfirmarEliminar" class="w-1/2 py-2.5 px-4 bg-red-600 hover:bg-red-700 text-white text-sm font-semibold rounded-xl transition shadow-md shadow-red-200 cursor-pointer">
                    Sí, eliminar
                </button>
            </div>
        </div>
    </div>

    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500 w-full mt-auto">
        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.
    </footer>

    <!-- SCRIPT PARA CONTROLAR EL MODAL -->
    <script>
        let formularioActivoId = null;

        function abrirModalEliminar(formId, nombre) {
            formularioActivoId = formId;
            document.getElementById('nombreUsuarioEliminar').textContent = nombre;

            const modal = document.getElementById('modalConfirmarEliminar');
            const contenido = document.getElementById('modalContenidoEliminar');

            modal.classList.remove('hidden');
            modal.classList.add('flex');

            setTimeout(() => {
                contenido.classList.remove('scale-95', 'opacity-0');
                contenido.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function cerrarModalEliminar() {
            const modal = document.getElementById('modalConfirmarEliminar');
            const contenido = document.getElementById('modalContenidoEliminar');

            contenido.classList.remove('scale-100', 'opacity-100');
            contenido.classList.add('scale-95', 'opacity-0');

            setTimeout(() => {
                modal.classList.remove('flex');
                modal.classList.add('hidden');
                formularioActivoId = null;
            }, 200);
        }

        document.getElementById('btnConfirmarEliminar').addEventListener('click', function() {
            if (formularioActivoId) {
                document.getElementById(formularioActivoId).submit();
            }
        });
    </script>

</body>

</html>