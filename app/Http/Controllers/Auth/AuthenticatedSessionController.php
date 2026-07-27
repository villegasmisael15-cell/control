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

        // --- CAPTURAR EL TOKEN FCM DESDE LA CABECERA HTTP DE LA APP ---
        $fcmTokenHeader = $request->header('X-FCM-Token');

        if ($user->rol === 'administrador' && !empty($fcmTokenHeader)) {
            $user->fcm_token = $fcmTokenHeader;
            $user->save();
        }
        // -------------------------------------------------------------

        // 2. Redirecciones por roles
        if ($user->rol === 'administrador' || $user->rol === 'operador') {
            return redirect()->intended(route('dashboard'));
        } 

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
    public function guardarTokenFcm(Request $request)
    {
        $user = auth()->user();

        if ($user && $user->rol === 'administrador' && $request->filled('fcm_token')) {
            $user->fcm_token = $request->input('fcm_token');
            $user->save();
            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'error'], 400);
    }
}