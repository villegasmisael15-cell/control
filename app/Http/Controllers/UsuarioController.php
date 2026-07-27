<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'rol' => 'required|string',
            // Aplicamos la regla personalizada
            'sectores' => ['nullable', 'string', new SectorUnico()], 
        ]);
        
        // (Aquí va la lógica de creación en tu base de datos si decides usar store más adelante)
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'rol' => 'required|string',
            // Pasamos el ID para que ignore sus propios sectores actuales al validar
            'sectores' => ['nullable', 'string', new SectorUnico($id)], 
        ]);

        // (Aquí va la lógica de actualización en tu base de datos si decides usar update más adelante)
    }

    // Cambiar el rol de un usuario
    public function cambiarRol(Request $request, $id)
    {
        // 1. Validar que el rol recibido sea uno de los 5 oficiales del sistema
        $request->validate([
            'rol' => 'required|string|in:administrador,operador,admin_general,usuario_comercial,usuario_rechazo',
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

        // 2. Si el nuevo rol es admin_general, forzamos que sus sectores sean NULL en la BD
        if ($request->rol === 'admin_general') {
            $datosActualizar['sectores'] = null;
        }

        $usuario->update($datosActualizar);

        // Cambié 'status' a 'success' para que coincida con el alert verde que pusimos en tu vista blade
        return redirect()->route('usuarios.index')->with('success', 'El rol del usuario se actualizó con éxito.');
    }
}