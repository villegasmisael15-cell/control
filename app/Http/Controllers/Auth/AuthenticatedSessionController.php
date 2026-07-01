<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
   public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();

    $request->session()->regenerate();

    // 1. Obtenemos el usuario que acaba de loguearse
    $user = auth()->user();

    // 2. Si es Administrador u Operador, va al Dashboard normal
    if ($user->rol === 'administrador' || $user->rol === 'operador') {
        return redirect()->intended(route('dashboard'));
    } 

    // 3. Si es el nuevo rol "usuario", va directo a la Recepción sin pasar por el dashboard
   if ($user->rol === 'usuario_comercial' || $user->rol === 'usuario_rechazo') {
    return redirect()->route('recepcion.index');
}

    return redirect()->intended(route('dashboard'));
}   

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
