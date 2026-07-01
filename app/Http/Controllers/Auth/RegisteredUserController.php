<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Rules\SectorUnico; 

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
{
    // 1. Validación inicial de campos requeridos y formatos nativos
   $request->validate([
    'name' => ['required', 'string', 'max:255'],
    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
    'password' => ['required', 'confirmed', Rules\Password::defaults()],
    'rol' => ['required', 'string', 'in:operador,usuario_comercial,usuario_rechazo'], // Actualizado
    'sectores' => ['required_if:rol,operador', 'array'],
]);

    // Inicializamos variables de control para los sectores
    $sectoresCadena = null;

    // 2. Procesamiento de sectores: SOLO si el rol es operador
    if ($request->rol === 'operador') {
        // Limpiamos duplicados por si el usuario repite el mismo número por error
        $numerosUnicos = array_unique($request->sectores);

        // Mapeamos el array de números [1, 2] para transformarlo en ["Sector 1", "Sector 2"]
        $sectoresFormateados = array_map(function($numero) {
            return 'Sector ' . trim($numero);
        }, $numerosUnicos);

        // Convertimos a cadena final separada por comas: "Sector 1, Sector 2"
        $sectoresCadena = implode(', ', $sectoresFormateados);

        // 3. BLINDAJE DE SEGURIDAD: Validar la cadena final contra sectores ya ocupados
        $validadorSector = validator(['sectores' => $sectoresCadena], [
            'sectores' => [new SectorUnico()]
        ]);

        if ($validadorSector->fails()) {
            throw ValidationException::withMessages([
                'sectores' => $validadorSector->errors()->first('sectores')
            ]);
        }
    }

    // 4. Creación del usuario guardando su rol correspondiente
    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'rol' => $request->rol, // Guarda 'operador' o 'usuario' dinámicamente
        'sectores' => $sectoresCadena, // Si es usuario, guardará null automáticamente
    ]);

    event(new Registered($user));

    Auth::login($user);

    // 5. Redirección condicional según el rol creado
    if ($user->rol === 'usuario_comercial' || $user->rol === 'usuario_rechazo') {
    return redirect()->route('recepcion.index');
}

    return redirect(route('dashboard', absolute: false));
}
}