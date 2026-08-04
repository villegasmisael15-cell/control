<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SectorCaracteristica;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Rules\SectorUnico;

class UsuarioController extends Controller
{
    // Mostrar la lista de usuarios
    public function index()
    {
        // Si no es administrador ni admin_general (manejado por el Gate), denegar el acceso
        if (Gate::denies('es-administrador')) {
            abort(403, 'No tienes permisos para administrar usuarios.');
        }

        // Obtener todos los usuarios ordenados por nombre
        $usuarios = User::orderBy('name', 'asc')->get();

        return view('usuarios.index', compact('usuarios'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'              => ['required', 'string', 'min:8'],
            'rol'                   => ['required', 'string', 'in:dueno,operador,usuario_comercial,usuario_rechazo,admin_general'],
            'dueno_id'              => ['required_if:rol,operador', 'nullable', 'exists:users,id'],
            'seleccion_sectores'    => ['required_if:rol,operador', 'nullable', 'array'],
            'invernaderos_dueno'    => ['required_if:rol,dueno', 'nullable', 'array'],
        ]);

        DB::beginTransaction();

        try {
            // 1. Crear el usuario con su rol correspondiente
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'rol'      => $request->rol,
            ]);

            // 2. Si el rol es DUEÑO, registramos sus invernaderos y sectores en sector_caracteristica
            if ($request->rol === 'dueno' && $request->has('invernaderos_dueno')) {
                foreach ($request->invernaderos_dueno as $inv) {
                    $nombreInvernadero = trim($inv['nombre'] ?? '');
                    $sectoresTexto = trim($inv['sectores'] ?? '');

                    if (!empty($nombreInvernadero) && !empty($sectoresTexto)) {
                        $listaSectores = explode(',', $sectoresTexto);

                        foreach ($listaSectores as $num) {
                            $limpio = trim($num);
                            if (!empty($limpio)) {
                                $sectorFormateado = (stripos($limpio, 'Sector') === false) ? 'Sector ' . $limpio : $limpio;

                                SectorCaracteristica::create([
                                    'user_id'            => $user->id,
                                    'invernadero'        => $nombreInvernadero,
                                    'sector'             => $sectorFormateado,
                                    'superficie_m2'      => 1,
                                    'numero_plantas'     => 1,
                                    'macetas_por_gotero' => 1,
                                ]);
                            }
                        }
                    }
                }
            }

            // 3. Si el rol es OPERADOR, guardamos las selecciones múltiples en operador_sectores
            if ($request->rol === 'operador' && $request->has('seleccion_sectores')) {
                foreach ($request->seleccion_sectores as $item) {
                    $partes = explode('|', $item);
                    if (count($partes) === 2) {
                        \App\Models\OperadorSector::create([
                            'user_id'     => $user->id,
                            'dueno_id'    => $request->dueno_id,
                            'invernadero' => trim($partes[0]),
                            'sector'      => trim($partes[1]),
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('usuarios.index')->with('success', 'Usuario creado correctamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al crear el usuario: ' . $e->getMessage())->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'rol' => ['required', 'string', 'in:dueno,operador,usuario_comercial,usuario_rechazo,admin_general'],
            'sectores' => ['nullable', 'string', new SectorUnico($id)], 
        ]);

        $usuario->update([
            'name' => $request->name,
            'email' => $request->email,
            'rol' => $request->rol,
        ]);

        return redirect()->route('usuarios.index')->with('success', 'Usuario actualizado con éxito.');
    }

    // Cambiar el rol de un usuario
    public function cambiarRol(Request $request, $id)
    {
        // 1. Validar que el rol recibido sea uno de los oficiales del sistema
        $request->validate([
            'rol' => 'required|string|in:dueno,administrador,operador,admin_general,usuario_comercial,usuario_rechazo',
        ]);

        $usuario = User::findOrFail($id);

        // Evitar que el administrador se cambie el rol a sí mismo por accidente
        if ($usuario->id === auth()->id()) {
            return redirect()->back()->with('error', 'No puedes cambiar tu propio rol.');
        }

        // Datos a actualizar
        $datosActualizar = [
            'rol' => $request->rol
        ];

        $usuario->update($datosActualizar);

        return redirect()->route('usuarios.index')->with('success', 'El rol del usuario se actualizó con éxito.');
    }
}