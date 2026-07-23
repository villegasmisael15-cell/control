<!DOCTYPE html>
<html lang="es" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema Control</title>
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

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit"class="text-xs bg-emerald-700 hover:bg-emerald-800 px-3 py-1.5 rounded transition flex items-center gap-1">
                            <i class="fa-solid fa-right-from-bracket"></i> Salir
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="max-w-[95%] mx-auto px-4 py-8 w-full flex-grow">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-800">Panel Principal</h1>
            <p class="text-gray-600 text-sm mt-1">Bienvenido al sistema. Selecciona un módulo para comenzar a trabajar.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <!-- MÓDULO: HIDROPONÍA (Acceso: Administrador, Operador y Usuario Comercial) -->
            @if(auth()->user()->rol === 'administrador' || auth()->user()->rol === 'operador' || auth()->user()->rol === 'usuario_comercial')
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition duration-200 flex flex-col">
                <div class="p-6 flex-grow">
                    <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center text-emerald-600 text-xl mb-4">
                        <i class="fa-solid fa-cloud-sun-rain"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Hidroponía</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Bitácora automatizada para el registro de parámetros de temperatura, humedad, DPV, pH, conductividad eléctrica y balance hídrico.</p>
                </div>
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex justify-end">
                    <a href="{{ route('monitoreo.index') }}" class="text-sm text-emerald-600 hover:text-emerald-700 font-bold flex items-center gap-1">
                        Ingresar al módulo <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
            @endif

            <!-- MÓDULO: SUELO (Acceso: Administrador y Operador) -->
            @if(auth()->user()->rol === 'administrador' || auth()->user()->rol === 'operador')
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition duration-200 flex flex-col">
                <div class="p-6 flex-grow">
                    <div class="w-12 h-12 bg-stone-100 rounded-lg flex items-center justify-center text-stone-700 text-xl mb-4">
                        <i class="fa-solid fa-mound"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Suelo</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Monitoreo periódico y control de las condiciones directas del suelo, humedad de la tierra, nutrients y análisis edafológicos del cultivo.</p>
                </div>
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex justify-end">
                    <a href="{{ route('suelo.index') }}" class="text-sm text-stone-600 hover:text-stone-700 font-bold flex items-center gap-1">
                        Ingresar al módulo <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
            @endif

            <!-- MÓDULO: SANIDAD Y NUTRICIÓN (Acceso: Administrador y Operador) -->
            @if(auth()->user()->rol === 'administrador' || auth()->user()->rol === 'operador')
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition duration-200 flex flex-col">
                <div class="p-6 flex-grow">
                    <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center text-red-600 text-xl mb-4">
                        <i class="fa-solid fa-prescription-bottle-medical"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Sanidad y Nutrición</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Bitácora unificada para el manejo fitosanitario de agroquímicos y control nutricional de tanques de fertilizantes.</p>
                </div>
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex justify-end">
                    <a href="{{ route('sanidad.index') }}" class="text-sm text-red-600 hover:text-red-700 font-bold flex items-center gap-1">
                        Ingresar al módulo <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
            @endif

            <!-- MÓDULO: GRÁFICAS Y ANÁLISIS (Acceso: Solo Administrador) -->
            @can('es-administrador')
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition duration-200 flex flex-col">
                <div class="p-6 flex-grow">
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600 text-xl mb-4">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Gráficas y Análisis</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Visualización de tendencias bioclimáticas en tiempo real. Análisis comparativos del comportamiento de DPV, variaciones de pH y drenajes por sector.</p>
                </div>
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex justify-end">
                    <a href="{{ route('graficas.index') }}" class="text-sm text-blue-600 hover:text-blue-700 font-bold flex items-center gap-1">
                        Visualizar reportes <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
            @endcan

            <!-- MÓDULO: RECEPCIÓN (Acceso: Administrador, Usuario Comercial y Usuario Rechazo) -->
            @if(auth()->user()->rol === 'administrador' || auth()->user()->rol === 'usuario_comercial' || auth()->user()->rol === 'usuario_rechazo')
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition duration-200 flex flex-col">
                <div class="p-6 flex-grow">
                    <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600 text-xl mb-4">
                        <i class="fa-solid fa-boxes-packing"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Recepción</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Control de entrada de material vegetal e insumos al invernadero. Registro de lotes, procedencia, cantidad y auditoría de calidad en la entrega.</p>
                </div>
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex justify-end">
                    <a href="{{ route('recepcion.index') }}" class="text-sm text-amber-600 hover:text-amber-700 font-bold flex items-center gap-1">
                        Gestionar entradas <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
            @endif

            <!-- MÓDULO: REPORTES COMERCIALES (Acceso: Administrador, Operador) -->
            @if(auth()->user()->rol === 'administrador' || auth()->user()->rol === 'operador')
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition duration-200 flex flex-col">
                <div class="p-6 flex-grow">
                    <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center text-indigo-600 text-xl mb-4">
                        <i class="fa-solid fa-chart-simple"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Reportes</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Módulo de Reportes de la Bitácora de Embarques de Exportación. Consulta de estatus de contenedores, aduanas y seguimiento logístico.</p>
                </div>
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex justify-end">
                    <a href="{{ route('reportes.index') }}" class="text-sm text-indigo-600 hover:text-indigo-700 font-bold flex items-center gap-1">
                        Ver reportes <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
            @endif

            <!-- MÓDULO: CONTROL DE USUARIOS (Acceso: Solo Administrador) -->
            @can('es-administrador')
            <div class="bg-white rounded-xl shadow-md border border-gray-200 overflow-hidden hover:shadow-lg transition duration-200 flex flex-col">
                <div class="p-6 flex-grow">
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600 text-xl mb-4">
                        <i class="fa-solid fa-users-gear"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Control de Usuarios</h3>
                    <p class="text-gray-600 text-sm leading-relaxed">Administración de personal, asignación de roles y permisos específicos para limitar los accesos de escritura y lectura.</p>
                </div>
                <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex justify-end">
                    <a href="{{ route('usuarios.index') }}" class="text-sm text-purple-600 hover:text-purple-700 font-bold flex items-center gap-1">
                        Configurar accesos <i class="fa-solid fa-arrow-right text-xs"></i>
                    </a>
                </div>
            </div>
            @endcan

        </div>
    </main>

    <footer class="bg-white border-t border-gray-200 py-4 text-center text-sm text-gray-500 w-full mt-auto">
        &copy; {{ date('Y') }} Sistema Control. Todos los derechos reservados.
    </footer>

</body>

</html>