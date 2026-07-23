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
        <div class="max-w-[95%] mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <i class="fa-solid fa-leaf text-2xl mr-2"></i>
                    <span class="font-bold text-xl tracking-wider">SISTEMA CONTROL</span>
                </div>

                <div class="flex items-center gap-4 text-sm font-medium">
                   
                    <span class="bg-emerald-700 px-3 py-1 rounded text-xs">
                        <i class="fa-solid fa-user"></i> {{ auth()->user()->name }}
                    </span>

                     <a href="{{ route('dashboard') }}" class="text-xs bg-emerald-700 hover:bg-emerald-800 px-3 py-1.5 rounded transition flex items-center gap-1">
                        <i class="fa-solid fa-circle-chevron-left"></i> Volver al Panel
                    </a>
                </div>
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
            <p class="text-gray-600 text-sm mt-1">Asigna roles a los usuarios registrados para controlar sus privilegios de lectura y escritura.</p>
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
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-gray-700 text-sm">
                        @foreach($usuarios as $user)
                        <tr class="hover:bg-gray-50 transition duration-150">
                            <td class="py-4 px-6 font-medium text-gray-900">{{ $user->name }}</td>
                            <td class="py-4 px-6 text-gray-600">{{ $user->email }}</td>
                            <td class="py-4 px-6 text-gray-500">{{ $user->created_at->format('d/m/Y H:i') }}</td>
                            <td class="py-4 px-6 text-center">
                                {{-- Colores dinámicos en base a cada uno de tus 5 roles actuales --}}
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if($user->rol === 'administrador') bg-purple-100 text-purple-800
                                    @elseif($user->rol === 'admin_general') bg-indigo-100 text-indigo-800
                                    @elseif($user->rol === 'operador') bg-blue-100 text-blue-800
                                    @elseif($user->rol === 'usuario_comercial') bg-amber-100 text-amber-800
                                    @elseif($user->rol === 'usuario_rechazo') bg-rose-100 text-rose-800
                                    @else bg-gray-100 text-gray-800 @endif">
                                    {{ strtoupper(str_replace('_', ' ', $user->rol)) }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-center">
                                @if(auth()->id() !== $user->id)
                                <form action="{{ route('usuarios.cambiarRol', $user->id) }}" method="POST" class="inline-flex gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <select name="rol" onchange="this.form.submit()" class="bg-gray-50 border border-gray-300 text-gray-700 text-xs rounded-lg focus:ring-emerald-500 focus:border-emerald-500 p-1.5 cursor-pointer">
                                        <option value="operador" {{ $user->rol === 'operador' ? 'selected' : '' }}>Operador</option>
                                        <option value="administrador" {{ $user->rol === 'administrador' ? 'selected' : '' }}>Administrador (Participativo)</option>
                                        <option value="admin_general" {{ $user->rol === 'admin_general' ? 'selected' : '' }}>Admin General (Supervisor)</option>
                                        <option value="usuario_comercial" {{ $user->rol === 'usuario_comercial' ? 'selected' : '' }}>Usuario Comercial</option>
                                        <option value="usuario_rechazo" {{ $user->rol === 'usuario_rechazo' ? 'selected' : '' }}>Usuario Rechazo</option>
                                    </select>
                                </form>
                                @else
                                <span class="text-xs text-gray-400 italic">Usuario Actual (Tú)</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500 w-full mt-auto">
        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.
    </footer>

</body>

</html>