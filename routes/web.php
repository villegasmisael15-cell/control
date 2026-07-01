<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\MonitoreoClimaRiegoController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RecepcionController;
use App\Http\Controllers\ProfileController;

// Redireccionar la raíz al login si no está autenticado, o al dashboard si ya inició sesión
Route::get('/', function () {
    return Auth::check() ? redirect('/dashboard') : redirect('/register');
});

// Todas las rutas dentro de este grupo requerirán LOGIN obligatorio
Route::middleware(['auth', 'verified'])->group(function () {

    // Vista del Dashboard Principal
    Route::get('/dashboard', function () {
        return view('dashboard');
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

    // 1. RUTAS PÚBLICAS (Para Administradores y Operadores)
    // Ambos pueden ver la tabla (cada quien indexado a su sector) y subir nuevos registros
    Route::get('/monitoreo', [MonitoreoClimaRiegoController::class, 'index'])->name('monitoreo.index');
    Route::get('/monitoreo/ver/{id}', [MonitoreoClimaRiegoController::class, 'show'])->name('monitoreo.show');
    Route::get('/monitoreo/nuevo', [MonitoreoClimaRiegoController::class, 'create'])->name('monitoreo.create');
    Route::post('/monitoreo/guardar', [MonitoreoClimaRiegoController::class, 'store'])->name('monitoreo.store');


    // 2. RUTAS PRIVADAS (Exclusivas de Administrador)
    // Solo el administrador puede entrar a los formularios de edición y ejecutar bajas
    Route::middleware('can:es-administrador')->group(function () {
        Route::get('/monitoreo/{id}/editar', [MonitoreoClimaRiegoController::class, 'edit'])->name('monitoreo.edit');
        Route::put('/monitoreo/{id}/actualizar', [MonitoreoClimaRiegoController::class, 'update'])->name('monitoreo.update');
        Route::delete('/monitoreo/{id}/eliminar', [MonitoreoClimaRiegoController::class, 'destroy'])->name('monitoreo.destroy');
        Route::get('/monitoreo/{id}/exportar-excel', [MonitoreoClimaRiegoController::class, 'exportarExcel'])->name('monitoreo.excel');
        Route::patch('/usuarios/{id}/cambiar-rol', [UsuarioController::class, 'cambiarRol'])->name('usuarios.cambiarRol');
        Route::resource('usuarios', UsuarioController::class);
        Route::delete('/recepcion/nacional/eliminar/{id}', [RecepcionController::class, 'destroyNacional'])->name('recepcion.destroyNacional');
    });


    // 3. IMPLEMENTACIÓN: REGISTRO OBLIGATORIO DE CARACTERÍSTICAS DE SECTOR
    // Estas rutas manejan la pantalla de bloqueo técnico para operadores sin datos base
    Route::get('/sectores/configurar-inicial', function () {
        $sectoresTexto = auth()->user()->sectores;
        $primerSector = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto))[0] : null;

        $sector = session('sector_pendiente') ?? $primerSector;

        if (!$sector) {
            return redirect('/dashboard');
        }
        return view('sectores.configurar_inicial', compact('sector'));
    })->name('sectores.configurar');

    Route::post('/sectores/configurar-inicial', function (Request $request) {
        $request->validate([
            'sector' => 'required|string',
            'superficie_m2' => 'required|integer|min:1',
            'variedad' => 'required|string|max:255',
            'fecha_trasplante' => 'required|date',
        ]);

        \App\Models\SectorCaracteristica::updateOrCreate(
            ['sector' => $request->sector],
            [
                'superficie_m2' => $request->superficie_m2,
                'variedad' => $request->variedad,
                'fecha_trasplante' => $request->fecha_trasplante
            ]
        );

        return redirect('/dashboard')->with('status', 'Sector configurado correctamente.');
    })->name('sectores.guardar_inicial');
});

// Las rutas de autenticación de Breeze (Login, Registro, etc.) se cargan aquí:
require __DIR__ . '/auth.php';
