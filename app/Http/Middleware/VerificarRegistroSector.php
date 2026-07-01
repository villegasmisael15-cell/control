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
    // Si no está logueado, permitir flujo normal (para el login/registro)
    if (!auth()->check()) {
        return $next($request);
    }

    // Si ya va directo a guardar los datos del sector, dejarlo pasar para evitar bucle infinito
    if ($request->is('sectores/configurar-inicial')) {
        return $next($request);
    }

    $sectoresTexto = auth()->user()->sectores;
    
    // Si el usuario (sea operador o administrador) NO tiene sectores asignados en su perfil, no se le bloquea
    if (empty($sectoresTexto)) {
        return $next($request);
    }

    $sectoresAsignados = array_map('trim', explode(',', $sectoresTexto));

    foreach ($sectoresAsignados as $sector) {
        // Verificar si el sector actual ya cuenta con sus datos base en la tabla
        $existe = SectorCaracteristica::where('sector', $sector)->exists();
        
        if (!$existe) {
            // Si falta un solo sector de su perfil, redirigir forzosamente
            return redirect('/sectores/configurar-inicial')
                ->with('sector_pendiente', $sector);
        }
    }

    return $next($request);
}
}