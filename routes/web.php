<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\MonitoreoClimaRiegoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RecepcionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\SueloMonitoreoController;
use App\Http\Controllers\SanidadNutricionBitacoraController;


// Redireccionar la raíz al login si no está autenticado, o al dashboard si ya inició sesión
Route::get('/', function () {
    return Auth::check() ? redirect('/dashboard') : redirect('/register');
});

// Todas las rutas dentro de este grupo requerirán LOGIN obligatorio
Route::middleware(['auth', 'verified'])->group(function () {

    // Vista del Dashboard Principal (MODIFICADA: Resuelve el tablero vacío del admin_general)
    Route::get('/dashboard', function () {
        $user = auth()->user();

        // Si es el administrador que no participa, le mandamos TODOS los sectores del sistema
        if ($user->rol === 'admin_general') {
            $sectores = \App\Models\SectorCaracteristica::pluck('sector')->toArray();
        } else {
            // Para operadores u otros usuarios, procesamos sus sectores asignados como siempre
            $sectoresTexto = $user->sectores;
            $sectores = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];
        }

        // Retornamos la vista compartiendo la variable $sectores para que las gráficas la usen
        return view('dashboard', compact('sectores'));
    })->name('dashboard');

    Route::get('/graficas', [MonitoreoClimaRiegoController::class, 'graficas'])->name('graficas.index');
    Route::get('/recepcion', [RecepcionController::class, 'index'])->name('recepcion.index');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/recepcion/nacional/guardar', [RecepcionController::class, 'storeNacional'])->name('recepcion.storeNacional');
    Route::post('/recepcion/exportacion/guardar', [RecepcionController::class, 'storeExportacion'])->name('recepcion.storeExportacion');
    Route::put('/recepcion/nacional/actualizar/{id}', [RecepcionController::class, 'updateNacional'])->name('recepcion.updateNacional');
    Route::get('/recepcion/nacional/reporte/{id}', [RecepcionController::class, 'showNacional'])->name('recepcion.showNacional');
    Route::get('/recepcion/exportacion/{id}', [RecepcionController::class, 'showExportacion'])->name('recepcion.showExportacion');
    Route::put('/recepcion/exportacion/actualizar/{id}', [RecepcionController::class, 'updateExportacion'])->name('recepcion.updateExportacion');
    Route::post('/recepcion/exportacion/restituir', [RecepcionController::class, 'storeRestituidas'])->name('recepcion.storeRestituidas');
    Route::get('/reportes/{id}/descargar-pdf', [ReporteController::class, 'descargarPDF'])->name('reportes.pdf');
    Route::get('/sanidad/pdf/{id}', [SanidadNutricionBitacoraController::class, 'pdf'])->name('sanidad.pdf');
    // UBICACIÓN CORREGIDA: Permite procesar el envío del formulario de Condensación de forma segura
    Route::post('/condensacion/guardar', [RecepcionController::class, 'guardarCondensacion'])->name('condensacion.guardar');

    // 1. RUTAS PÚBLICAS (Para Administradores y Operadores)
    Route::get('/monitoreo', [MonitoreoClimaRiegoController::class, 'index'])->name('monitoreo.index');
    Route::get('/monitoreo/ver/{id}', [MonitoreoClimaRiegoController::class, 'show'])->name('monitoreo.show');
    Route::get('/monitoreo/nuevo', [MonitoreoClimaRiegoController::class, 'create'])->name('monitoreo.create');
    Route::post('/monitoreo/guardar', [MonitoreoClimaRiegoController::class, 'store'])->name('monitoreo.store');

    // 3. IMPLEMENTACIÓN: REGISTRO OBLIGATORIO DE CARACTERÍSTICAS DE SECTOR
    // Estas rutas manejan la pantalla de bloqueo técnico para operadores sin datos base
    Route::get('/sectores/configurar-inicial', function () {
        $user = auth()->user();

        // BYPASS: Si el usuario es 'admin_general', salta el bloqueo directo al dashboard
        if ($user->rol === 'admin_general') {
            return redirect('/dashboard');
        }

        $sectoresTexto = $user->sectores;
        $primerSector = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto))[0] : null;

        $sector = session('sector_pendiente') ?? $primerSector;

        if (!$sector) {
            return redirect('/dashboard');
        }
        return view('sectores.configurar_inicial', compact('sector'));
    })->name('sectores.configurar');

   Route::post('/sectores/configurar-inicial', function (Request $request) {
    $request->validate([
        'sector'             => 'required|string',
        'superficie_m2'      => 'required|integer|min:1',
        'variedad'           => 'required|string|max:255',
        'numero_plantas'     => 'required|integer|min:1', // 💡 NUEVO CAMPO VALIDADO
        'macetas_por_gotero' => 'required|integer|min:1', 
        'fecha_trasplante'   => 'required|date',
    ]);

    \App\Models\SectorCaracteristica::updateOrCreate(
        ['sector' => $request->sector],
        [
            'superficie_m2'      => $request->superficie_m2,
            'variedad'           => $request->variedad,
            'numero_plantas'     => $request->numero_plantas, // 💡 NUEVO CAMPO GUARDADO
            'macetas_por_gotero' => $request->macetas_por_gotero, 
            'fecha_trasplante'   => $request->fecha_trasplante
        ]
    );
        return redirect('/dashboard')->with('status', 'Sector configurado correctamente.');
    })->name('sectores.guardar_inicial');

    Route::get('/suelo', [SueloMonitoreoController::class, 'index'])->name('suelo.index');
    Route::get('/suelo/nuevo', [SueloMonitoreoController::class, 'create'])->name('suelo.create');
    Route::post('/suelo/guardar', [SueloMonitoreoController::class, 'store'])->name('suelo.store');
    
    Route::get('/reportes', [ReporteController::class, 'index'])->name('reportes.index');
    Route::put('/reportes/{id}/actualizar', [ReporteController::class, 'update'])->name('reportes.update');
    Route::put('/reportes/{recepcion_id}', [ReporteController::class, 'update'])->name('reportes.update_recepcion');

    Route::get('/sanidad-nutricion', [SanidadNutricionBitacoraController::class, 'index'])->name('sanidad.index');
    Route::get('/sanidad-nutricion/nuevo', [SanidadNutricionBitacoraController::class, 'create'])->name('sanidad.create');
    Route::post('/sanidad-nutricion/guardar', [SanidadNutricionBitacoraController::class, 'store'])->name('sanidad.store');
    // =========================================================================

    // 2. RUTAS PRIVADAS (Exclusivas de Administrador)
    Route::middleware('can:es-administrador')->group(function () {
        Route::get('/monitoreo/{id}/editar', [MonitoreoClimaRiegoController::class, 'edit'])->name('monitoreo.edit');
        Route::put('/monitoreo/{id}/actualizar', [MonitoreoClimaRiegoController::class, 'update'])->name('monitoreo.update');
        Route::delete('/monitoreo/{id}/eliminar', [MonitoreoClimaRiegoController::class, 'destroy'])->name('monitoreo.destroy');
        Route::get('/monitoreo/{id}/exportar-excel', [MonitoreoClimaRiegoController::class, 'exportarExcel'])->name('monitoreo.excel');
        Route::patch('/usuarios/{id}/cambiar-rol', [UsuarioController::class, 'cambiarRol'])->name('usuarios.cambiarRol');
        Route::resource('usuarios', UsuarioController::class);
        Route::delete('/recepcion/nacional/eliminar/{id}', [RecepcionController::class, 'destroyNacional'])->name('recepcion.destroyNacional');
        Route::delete('/recepcion/exportacion/{id}', [RecepcionController::class, 'destroyExportacion'])->name('recepcion.destroyExportacion');
        Route::delete('/suelo/{id}', [SueloMonitoreoController::class, 'destroy'])->name('suelo.destroy');
        Route::delete('/sanidad/{id}', [SanidadNutricionBitacoraController::class, 'destroy'])->name('sanidad.destroy');
    });

}); // Cierre correcto del middleware global group

// Las rutas de autenticación de Breeze (Login, Registro, etc.) se cargan aquí:
require __DIR__ . '/auth.php';