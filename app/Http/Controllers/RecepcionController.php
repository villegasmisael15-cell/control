<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RecepcionNacional;
use App\Models\RecepcionExportacion;
use Illuminate\Http\RedirectResponse;
use App\Models\ControlCondensacion;

class RecepcionController extends Controller
{

    /**
     * Muestra la pantalla principal con las tablas filtradas por semana.
     */
    public function index(Request $request)
    {
        // El formato correcto para el navegador/Flatpickr es 'Y-\WW' (ejemplo exacto para hoy: "2026-W27")
        $semanaInput = $request->filled('semana') ? $request->semana : date('Y-\WW');

        // Descomponemos el año y el número de semana para los queries
        $partes = explode('-W', $semanaInput);
        $semanaFiltrar = isset($partes[1]) ? (int)$partes[1] : null;

        // 1. Cargamos los operadores
        $productores = User::where('rol', 'operador')->orderBy('name', 'asc')->get();

        // 2. IDs de embarques ya usados para el modal
        $idsEmbarquesConRechazo = RecepcionNacional::whereNotNull('recepcion_exportacion_id')
            ->pluck('recepcion_exportacion_id')
            ->toArray();

        $embarquesExportacion = RecepcionExportacion::whereNotIn('id', $idsEmbarquesConRechazo)
            ->orderBy('fecha_exportacion', 'desc')
            ->get();

        // 3. Recepciones Nacionales filtradas por la semana activa
        $recepcionesNacionales = RecepcionNacional::with(['productor'])
            ->when($semanaFiltrar, function ($query) use ($semanaFiltrar) {
                return $query->where('semana_nacional', $semanaFiltrar);
            })
            ->orderBy('fecha_nacional', 'desc')
            ->get();

        // 4. Recepciones Exportaciones filtradas por la semana activa
        $recepcionesExportaciones = RecepcionExportacion::with(['productor'])
            ->when($semanaFiltrar, function ($query) use ($semanaFiltrar) {
                return $query->where('semana_exportacion', $semanaFiltrar);
            })
            ->orderBy('fecha_exportacion', 'desc')
            ->get();

        // 5. Embarques que aún tienen cajas pendientes de restituir
        $embarquesPendientesDeCajas = RecepcionExportacion::with(['productor'])
            ->where('pendientes', '>', 0)
            ->orderBy('fecha_exportacion', 'desc')
            ->get();

        // =========================================================================
        // NUEVO: Buscamos si ya existe el dato fijo de Agropark guardado para esta semana específica
        // =========================================================================
        $controlCondensacion = null;
        if ($semanaFiltrar) {
            $controlCondensacion = \App\Models\ControlCondensacion::where('semana', $semanaFiltrar)->first();
        }
        // =========================================================================

        return view('recepcion.index', [
            'recepcionesNacionales'      => $recepcionesNacionales,
            'recepcionesExportaciones'   => $recepcionesExportaciones,
            'productores'                => $productores,
            'embarquesExportacion'       => $embarquesExportacion,
            'embarquesPendientesDeCajas' => $embarquesPendientesDeCajas,
            'semanaActiva'               => $semanaInput,
            // Mandamos las nuevas variables necesarias para que el HTML y el Modal funcionen
            'controlCondensacion'        => $controlCondensacion,
            'semanaActual'               => $semanaFiltrar
        ]);
    }

    /**
     * Almacena o consolida una recepción nacional.
     */
    public function storeNacional(Request $request): RedirectResponse
    {
        $request->validate([
            'fecha_nacional'           => ['required', 'date'],
            'productor_id'             => ['required', 'exists:users,id'],
            'sector_registro'          => ['required', 'string'],
            'semana_nacional'          => ['required', 'integer'],
            'cajas_comercializar'      => ['nullable', 'integer', 'min:0'],
            'peso_comercializar'       => ['nullable', 'numeric', 'min:0'],
            'cajas_rechazo_procesado'  => ['nullable', 'integer', 'min:0'],
            'peso_rechazo_procesado'   => ['nullable', 'numeric', 'min:0'],
            'cajas_vacias_totales'     => ['nullable', 'integer', 'min:0'],
            'recepcion_exportacion_id' => ['nullable', 'exists:recepcion_exportaciones,id'],
        ]);

        // CORRECCIÓN CRÍTICA: Buscamos el registro existente usando estrictamente Fecha, Operador y Sector
        // Quitamos 'recepcion_exportacion_id' de aquí para que encuentre el registro comercial original que tiene null
        $criteriosBusqueda = [
            'fecha_nacional'  => $request->fecha_nacional,
            'productor_id'    => $request->productor_id,
            'sector_registro' => $request->sector_registro,
        ];

        $registroExistente = RecepcionNacional::where($criteriosBusqueda)->first();

        if ($registroExistente) {
            $cajasComercial = $request->filled('cajas_comercializar') && $request->cajas_comercializar > 0
                ? $request->cajas_comercializar
                : $registroExistente->cajas_comercializar;

            $pesoComercial = ($request->filled('peso_comercializar') && $request->peso_comercializar !== null)
                ? $request->peso_comercializar
                : $registroExistente->peso_comercializar;

            $cajasRechazo = $request->filled('cajas_rechazo_procesado') && $request->cajas_rechazo_procesado > 0
                ? $request->cajas_rechazo_procesado
                : $registroExistente->cajas_rechazo_procesado;

            $pesoRechazo = ($request->filled('peso_rechazo_procesado') && $request->peso_rechazo_procesado !== null)
                ? $request->peso_rechazo_procesado
                : $registroExistente->peso_rechazo_procesado;

            $cajasVacias = $request->filled('cajas_vacias_totales') && $request->cajas_vacias_totales > 0
                ? $request->cajas_vacias_totales
                : $registroExistente->cajas_vacias_totales;

            $pesoComercialOriginal = $registroExistente->peso_comercializar_original;
            $pesoRechazoOriginal   = $registroExistente->peso_rechazo_procesado_original;
        } else {
            $cajasComercial = $request->cajas_comercializar ?? 0;
            $pesoComercial  = $request->peso_comercializar ?? 0;
            $cajasRechazo   = $request->cajas_rechazo_procesado ?? 0;
            $pesoRechazo    = $request->peso_rechazo_procesado ?? 0;
            $cajasVacias    = $request->cajas_vacias_totales ?? 0;

            $pesoComercialOriginal = $pesoComercial > 0 ? $pesoComercial : null;
            $pesoRechazoOriginal   = $pesoRechazo > 0 ? $pesoRechazo : null;
        }

        $totalCajas = $cajasComercial + $cajasRechazo;
        $totalKg    = $pesoComercial + $pesoRechazo;

        // 1. Consolidar la información en la tabla nacional
        $registroNacional = RecepcionNacional::updateOrCreate(
            $criteriosBusqueda, // Usa la llave de búsqueda limpia (Fecha, Operador, Sector)
            [
                'semana_nacional'                 => $request->semana_nacional,
                'cajas_comercializar'             => $cajasComercial,
                'peso_comercializar'              => $pesoComercial,
                'peso_comercializar_original'     => $pesoComercialOriginal,
                'cajas_rechazo_procesado'         => $cajasRechazo,
                'peso_rechazo_procesado'          => $pesoRechazo,
                'peso_rechazo_procesado_original' => $pesoRechazoOriginal,
                'cajas_vacias_totales'            => $cajasVacias,
                'total_cajas'                     => $totalCajas,
                'total_kg'                        => $totalKg,
                'capturado_por_id'                => auth()->id(),
                // Se añade o mantiene el id de exportación sin usarlo como restricción de búsqueda
                'recepcion_exportacion_id'        => $request->recepcion_exportacion_id ?: ($registroExistente ? $registroExistente->recepcion_exportacion_id : null),
            ]
        );

        // 2. DISPARADOR DE TRAZABILIDAD AUTOMÁTICA CON CANDADO DE PRIMERA VEZ (MANTENIDO INTACTO)
        if ($request->filled('recepcion_exportacion_id')) {
            $embarque = RecepcionExportacion::find($request->recepcion_exportacion_id);

            if ($embarque) {
                // Sumamos únicamente el peso de rechazo de fruta acumulado para este embarque
                $totalPesoRechazoAsociado = RecepcionNacional::where('recepcion_exportacion_id', $embarque->id)
                    ->sum('peso_rechazo_procesado');

                $pesoNetoCalculado = $embarque->peso_exportacion - $totalPesoRechazoAsociado;

                $pesoNetoFijo = is_null($embarque->peso_neto_fijo)
                    ? ($pesoNetoCalculado >= 0 ? $pesoNetoCalculado : 0)
                    : $embarque->peso_neto_fijo;

                // ACTUALIZACIÓN DE EMBARQUE: Ya no modificamos 'restituidas' ni 'pendientes' desde aquí
                $embarque->update([
                    'peso_neto_fijo' => $pesoNetoFijo
                ]);
            }
        }

        return redirect()->route('recepcion.index')->with('status', 'La información de la recepción fue consolidada correctamente y los saldos de exportación fueron actualizados de forma segura.');
    }

    /**
     * Guarda un nuevo embarque en la Bitácora de Exportación.
     */
    public function storeExportacion(Request $request): RedirectResponse
    {
        if (auth()->user()->rol === 'usuario_rechazo') {
            abort(403, 'No se tiene autorización para registrar embarques de exportación.');
        }

        $request->validate([
            'semana_exportacion' => ['required', 'integer', 'min:1', 'max:53'],
            'fecha_exportacion'  => ['required', 'date'],
            'productor_id'       => ['required', 'exists:users,id'],
            'sector_registro'    => ['required', 'string'],
            'cajas_exportadas'   => ['required', 'integer', 'min:0'],
            'peso_exportacion'   => ['required', 'numeric', 'min:0'],
        ]);

        RecepcionExportacion::create([
            'semana_exportacion' => $request->semana_exportacion,
            'fecha_exportacion'  => $request->fecha_exportacion,
            'productor_id'       => $request->productor_id,
            'sector_registro'    => $request->sector_registro,
            'cajas_exportacion'  => $request->cajas_exportadas,
            'peso_exportacion'   => $request->peso_exportacion,

            // REGLA 1: Las restituidas inician limpias y las pendientes adoptan el total de lo exportado
            'restituidas'        => 0,
            'pendientes'         => $request->cajas_exportadas,

            'capturado_por_id'   => auth()->id(),
        ]);

        return redirect()->route('recepcion.index')->with('status', 'Embarque de exportación registrado con éxito.');
    }

    /**
     * Almacena las cajas vacías devueltas.
     */
    public function storeRestituidas(Request $request): RedirectResponse
    {
        $request->validate([
            'recepcion_exportacion_id' => ['required', 'exists:recepcion_exportaciones,id'],
            'cajas_a_restituir'        => ['required', 'integer', 'min:1'],
        ]);

        $exportacion = RecepcionExportacion::findOrFail($request->recepcion_exportacion_id);

        // Validación estricta basada en existencias físicas reales
        if ($request->cajas_a_restituir > $exportacion->pendientes) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['cajas_a_restituir' => 'La cantidad ingresada supera las cajas pendientes actuales (' . $exportacion->pendientes . ' restantes).']);
        }

        // El cálculo ahora es una resta y suma limpia sobre el inventario de empaques
        $nuevoPendiente = $exportacion->pendientes - $request->cajas_a_restituir;
        $nuevoRestituidas = $exportacion->restituidas + $request->cajas_a_restituir;

        $exportacion->update([
            'restituidas' => $nuevoRestituidas,
            'pendientes'  => $nuevoPendiente >= 0 ? $nuevoPendiente : 0
        ]);

        return redirect()->route('recepcion.index')
            ->with('status', 'Devolución de cajas vacías registrada con éxito.');
    }

    /**
     * Registra las mermas o reajustes operativos de báscula nacional.
     */
    public function updateNacional(Request $request, $id): RedirectResponse
    {
        if (auth()->user()->rol === 'usuario_rechazo') {
            abort(403, 'No se tiene autorización para modificar valores comerciales.');
        }

        $request->validate([
            'peso_comercializar'     => ['nullable', 'numeric', 'min:0'],
            'peso_rechazo_procesado' => ['nullable', 'numeric', 'min:0'],
        ]);

        $registro = RecepcionNacional::findOrFail($id);

        $pesoComercialOriginal = $registro->peso_comercializar_original ?? $registro->getOriginal('peso_comercializar');
        $pesoRechazoOriginal   = $registro->peso_rechazo_procesado_original ?? $registro->getOriginal('peso_rechazo_procesado');

        $nuevoPesoComercial = ($request->filled('peso_comercializar') && $request->peso_comercializar !== null)
            ? $request->peso_comercializar
            : $registro->peso_comercializar;

        $nuevoPesoRechazo = ($request->filled('peso_rechazo_procesado') && $request->peso_rechazo_procesado !== null)
            ? $request->peso_rechazo_procesado
            : $registro->peso_rechazo_procesado;

        $nuevoTotalKg = $nuevoPesoComercial + $nuevoPesoRechazo;

        $registro->update([
            'weight_comercializar_original'     => $pesoComercialOriginal,
            'peso_comercializar'              => $nuevoPesoComercial,
            'peso_rechazo_procesado_original' => $pesoRechazoOriginal,
            'peso_rechazo_procesado'          => $nuevoPesoRechazo,
            'total_kg'                        => $nuevoTotalKg,
        ]);

        return redirect()
            ->route('recepcion.showNacional', $id)
            ->with('status', 'El registro nacional fue actualizado correctamente.');
    }

    /**
     * Elimina un registro nacional de la base de datos.
     */
    public function destroyNacional($id): RedirectResponse
    {
        if (auth()->user()->rol === 'usuario_rechazo') {
            abort(403, 'No se tiene autorización para eliminar registros de la bitácora.');
        }

        $registro = RecepcionNacional::findOrFail($id);
        $registro->delete();

        return redirect()->route('recepcion.index')->with('status', 'El registro de recepción fue eliminado correctamente.');
    }

    /**
     * Elimina un registro de exportación.
     */
    public function destroyExportacion($id): RedirectResponse
    {
        if (auth()->user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        $exportacion = RecepcionExportacion::findOrFail($id);
        $exportacion->delete();

        return redirect()->route('recepcion.index')
            ->with('status', 'El embarque de exportación ha sido eliminado correctamente.');
    }

    /**
     * Muestra el detalle de un embarque de exportación específico.
     */
    public function showExportacion($id)
    {
        $registro = RecepcionExportacion::with('productor')->findOrFail($id);

        return view('recepcion.show', [
            'registro' => $registro,
            'tipo'     => 'exportacion'
        ]);
    }

    /**
     * Actualiza los datos de exportación.
     */
    public function updateExportacion(Request $request, $id): RedirectResponse
    {
        if (auth()->user()->rol === 'usuario_rechazo') {
            abort(403, 'No se tiene autorización para modificar valores de exportación.');
        }

        $request->validate([
            'peso_exportacion' => ['required', 'numeric', 'min:0'],
        ]);

        $registro = RecepcionExportacion::findOrFail($id);

        $registro->update([
            'peso_exportacion' => $request->peso_exportacion,
        ]);

        return redirect()->route('recepcion.showExportacion', $id)->with('status', 'El peso del embarque de exportación fue actualizado exitosamente.');
    }

    /**
     * Muestra el detalle de un registro nacional específico.
     */
    public function showNacional($id)
    {
        $registro = RecepcionNacional::with('productor')->findOrFail($id);
        $tipo = 'nacional';

        return view('recepcion.show', [
            'registro' => $registro,
            'tipo'     => $tipo
        ]);
    }
    /**
     * Almacena o modifica la condensación diaria (Agropark) de forma segura.
     */
   public function guardarCondensacion(Request $request): RedirectResponse
    {
        // Validación: Agregamos las cajas_enviadas como opcionales a la lista existente
        $request->validate([
            'fecha'                  => ['required', 'date'],
            'total_empacados_global' => ['nullable', 'numeric', 'min:0'],
            'total_nacional_global'  => ['nullable', 'numeric', 'min:0'],
            'agropark'               => ['nullable', 'numeric', 'min:0'],
            'cajas_enviadas'         => ['nullable', 'integer', 'min:1'], // <-- Agregado
        ]);

        try {
            $fechaFormateada = \Carbon\Carbon::parse($request->fecha)->format('Y-m-d');

            // Buscamos el registro actual de condensación para este día
            $controlCondensacion = \DB::table('control_condensaciones')->whereDate('fecha', $fechaFormateada)->first();

            // Si se envía un nuevo valor de Agropark lo tomamos; si no, conservamos el actual de la BD
            $agroparkExistente = $request->has('agropark') && $request->agropark !== null
                ? (float)$request->agropark
                : ($controlCondensacion->agropark ?? 0.0);

            // 💡 NUEVO: Si se envía un nuevo valor de Cajas Enviadas lo tomamos; si no, conservamos el de la BD
            $cajasEnviadasExistentes = $request->has('cajas_enviadas') && $request->cajas_enviadas !== null
                ? (int)$request->cajas_enviadas
                : ($controlCondensacion->cajas_enviadas ?? 0);

            // Rescatamos los totales globales del formulario o mantenemos los actuales (0 por defecto)
            $empacadosGlobal = $request->filled('total_empacados_global') ? (float)$request->total_empacados_global : ($controlCondensacion->total_empacados_global ?? 0.00);
            $nacionalGlobal = $request->filled('total_nacional_global') ? (float)$request->total_nacional_global : ($controlCondensacion->total_nacional_global ?? 0.00);

            // Calculamos el factor multiplicador únicamente si existen los valores globales obligatorios de la fórmula
            $resultado1 = $agroparkExistente - $nacionalGlobal;
            $resultado2_factor = 0.00;

            if ($empacadosGlobal > 0 && $resultado1 > 0) {
                $resultado2_factor = $empacadosGlobal / $resultado1;
            }

            // Obtener el número de semana ISO automáticamente
            $semanaCalculada = $request->filled('semana')
                ? (int)$request->semana
                : (int)\Carbon\Carbon::parse($fechaFormateada)->weekOfYear;

            // Guardamos o actualizamos incluyendo la nueva columna sin pisar datos anteriores
            \DB::table('control_condensaciones')->updateOrInsert(
                ['fecha' => $fechaFormateada],
                [
                    'semana'                 => $semanaCalculada,
                    'agropark'               => $agroparkExistente,
                    'cajas_enviadas'         => $cajasEnviadasExistentes, // <-- CAMPO EN BASE DE DATOS INYECTADO
                    'total_empacados_global' => $empacadosGlobal,
                    'total_nacional_global'  => $nacionalGlobal,
                    'factor_multiplicador'   => $resultado2_factor,
                    'created_at'             => $controlCondensacion->created_at ?? now(),
                    'updated_at'             => now()
                ]
            );

            // 2. Traer los embarques del día para la distribución por sectores
            $recepcionesDelDia = \App\Models\RecepcionExportacion::whereDate('fecha_exportacion', $fechaFormateada)->get();

            $sumaPesosDiarios = $recepcionesDelDia->sum(function ($item) {
                return !is_null($item->peso_neto_fijo) ? (float)$item->peso_neto_fijo : (float)$item->peso_exportacion;
            });

            if ($sumaPesosDiarios > 0 && $agroparkExistente > 0) {
                foreach ($recepcionesDelDia as $registro) {

                    // A) Peso Neto Inicial del Sector (Se usará como base para aceptados_kg)
                    if (!is_null($registro->peso_neto_fijo)) {
                        $pesoNeto = (float)$registro->peso_neto_fijo;
                    } else {
                        $pesoRechazo = $registro->receccionesNacionales ? $registro->receccionesNacionales->sum('peso_rechazo_procesado') : 0;
                        $pesoNeto = (float)$registro->peso_exportacion - $pesoRechazo;
                    }
                    $pesoNeto = $pesoNeto >= 0 ? $pesoNeto : 0;

                    // B) Calcular dato destino (Condensación)
                    $divisionDiaria = $agroparkExistente / $sumaPesosDiarios;
                    $destinoCalculado = $pesoNeto * $divisionDiaria;

                    // C) Calcular Participación
                    $participacionCalculada = 0;
                    if ($destinoCalculado > 0 && $agroparkExistente > 0) {
                        $participacionCalculada = ($destinoCalculado * 100) / $agroparkExistente;
                    }

                    $resultado3_nacional = ($nacionalGlobal * $participacionCalculada) / 100;
                    $empacadosFinalCalculado = 0.00;

                    if ($resultado2_factor > 0) {
                        $empacadosFinalCalculado = ($destinoCalculado - $resultado3_nacional) * $resultado2_factor;
                        if ($empacadosFinalCalculado < 0) $empacadosFinalCalculado = 0;
                    }

                    // OBTENEMOS EL RECHAZO POSTERIOR EXISTENTE
                    $rechazoPostExistente = \DB::table('reportes')
                        ->where('recepcion_exportacion_id', $registro->id)
                        ->value('rechazo_post') ?? 0.00;

                    // Los kilos aceptados finales que se guardan en caliente
                    $aceptadosKgGuardar = $pesoNeto - (float)$rechazoPostExistente;
                    $aceptadosKgGuardar = $aceptadosKgGuardar >= 0 ? $aceptadosKgGuardar : 0.00;

                    // GUARDAR EN LA TABLA reportes INCLUYENDO EL DATO DE ACEPTADOS_KG
                    \DB::table('reportes')
                        ->updateOrInsert(
                            ['recepcion_exportacion_id' => $registro->id],
                            [
                                'user_id'       => $registro->productor_id,
                                'sector'        => $registro->sector_registro,
                                'destino'       => $destinoCalculado,
                                'participacion' => $participacionCalculada,
                                'nacional'      => $resultado3_nacional,
                                'empacados'     => $empacadosFinalCalculado,
                                'aceptados_kg'  => $aceptadosKgGuardar,
                                'aprobado'      => \DB::table('reportes')->where('recepcion_exportacion_id', $registro->id)->value('aprobado') ?? false,
                                'created_at'    => \DB::table('reportes')->where('recepcion_exportacion_id', $registro->id)->value('created_at') ?? now(),
                                'updated_at'    => now()
                            ]
                        );
                }
            }

            return redirect()->back()->with('success', '¡Parámetros y cajas del día guardados con éxito!');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Error crítico al guardar los datos: ' . $e->getMessage()]);
        }
    }

    
}
