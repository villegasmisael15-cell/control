<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SectorCaracteristica;
use App\Models\OperadorSector;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        // 1. Obtenemos los IDs de los usuarios que han creado sectores (los Dueños)
        $idsDuenos = SectorCaracteristica::select('user_id')->distinct()->pluck('user_id');
        
        // 2. Buscamos esos usuarios en la tabla users
        $duenos = User::whereIn('id', $idsDuenos)->get();

        // 3. Obtenemos todos los sectores y los agrupamos por el id del dueño
        $sectoresAgrupadosPorDueno = SectorCaracteristica::select('user_id', 'invernadero', 'sector')
            ->get()
            ->groupBy('user_id');

        return view('auth.register', compact('duenos', 'sectoresAgrupadosPorDueno'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // 1. Validación adaptada a los roles y requerimientos
        $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'email'               => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password'            => ['required', 'confirmed', Rules\Password::defaults()],
            'rol'                 => ['required', 'string', 'in:dueno,operador,usuario_comercial,usuario_rechazo'],
            'dueno_id'            => ['required_if:rol,operador', 'nullable', 'exists:users,id'],
            'seleccion_sectores'  => ['required_if:rol,operador', 'nullable', 'array'],
            'invernaderos_dueno'  => ['required_if:rol,dueno', 'nullable', 'array'],
        ]);

        // 2. Creación del usuario con su respectivo rol
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'rol'      => $request->rol,
        ]);

        // 3. Si el usuario es DUEÑO, guardamos sus invernaderos y sectores de forma segura
        if ($request->rol === 'dueno' && $request->has('invernaderos_dueno')) {
            foreach ($request->invernaderos_dueno as $inv) {
                $nombreInvernadero = trim($inv['nombre'] ?? '');
                $sectoresTexto = trim($inv['sectores'] ?? '');

                if (!empty($nombreInvernadero) && !empty($sectoresTexto)) {
                    // Separamos por comas (ej: "1, 2, 3")
                    $listaSectores = explode(',', $sectoresTexto);

                    foreach ($listaSectores as $num) {
                        $limpio = trim($num);
                        if (!empty($limpio)) {
                            $sectorFormateado = (stripos($limpio, 'Sector') === false) ? 'Sector ' . $limpio : $limpio;

                            // firstOrCreate evita duplicados y respeta la combinación (user_id, invernadero, sector)
                            SectorCaracteristica::firstOrCreate(
                                [
                                    'user_id'     => $user->id,
                                    'invernadero' => $nombreInvernadero,
                                    'sector'      => $sectorFormateado,
                                ],
                                [
                                    'variedad'    => null,
                                ]
                            );
                        }
                    }
                }
            }
        }

        // 4. Si es OPERADOR, guardamos sus selecciones múltiples en operador_sectores
        if ($request->rol === 'operador' && $request->has('seleccion_sectores')) {
            foreach ($request->seleccion_sectores as $item) {
                $partes = explode('|', $item);
                if (count($partes) === 2) {
                    $invernadero = trim($partes[0]);
                    $sector = trim($partes[1]);

                    OperadorSector::firstOrCreate([
                        'user_id'     => $user->id,
                        'dueno_id'    => $request->dueno_id,
                        'invernadero' => $invernadero,
                        'sector'      => $sector,
                    ]);
                }
            }
        }

        event(new Registered($user));

        Auth::login($user);

        // 5. Redirecciones condicionales
        if ($user->rol === 'usuario_comercial' || $user->rol === 'usuario_rechazo') {
            return redirect()->route('recepcion.index');
        }

        return redirect(route('dashboard', absolute: false));
    }
}