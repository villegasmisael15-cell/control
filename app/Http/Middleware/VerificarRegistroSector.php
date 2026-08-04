<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\SectorCaracteristica;
use Symfony\Component\HttpFoundation\Response;

class VerificarRegistroSector
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return $next($request);
        }

        if ($request->is('sectores/configurar-inicial*')) {
            return $next($request);
        }

        $user = auth()->user();

        if ($user->rol === 'admin_general') {
            return $next($request);
        }

        // Buscamos si el operador tiene algún sector registrado cuya variedad esté pendiente (null o vacía)
        $pendiente = SectorCaracteristica::where('user_id', $user->id)
            ->where(function($query) {
                $query->whereNull('variedad')->orWhere('variedad', '');
            })
            ->first();

        // Si encuentra un registro pendiente, lo mandamos obligatoriamente a configurar
        if ($pendiente) {
            return redirect('/sectores/configurar-inicial')
                ->with('sector_pendiente', $pendiente->sector);
        }

        return $next($request);
    }
}