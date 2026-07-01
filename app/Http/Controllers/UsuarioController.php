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
        // Si no es administrador, denegar el acceso de inmediato
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
}


    // Cambiar el rol de un usuario
   public function cambiarRol(Request $request, $id)
{
    // Validar que el rol recibido sea válido
    $request->validate([
        'rol' => 'required|string|in:administrador,operador',
    ]);

    $usuario = User::findOrFail($id);

    // Evitar que el administrador se cambie el rol a sí mismo por accidente
    if ($usuario->id === auth()->id()) {
        return redirect()->back()->with('error', 'No puedes cambiar tu propio rol.');
    }

    $usuario->update([
        'rol' => $request->rol
    ]);

    return redirect()->route('usuarios.index')->with('status', 'El rol del usuario se actualizó con éxito.');
}
}