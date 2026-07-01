<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\RecepcionNacional;
use App\Models\RecepcionExportacion;
use Illuminate\Http\RedirectResponse;

class RecepcionController extends Controller
{

    /**
     * Muestra la pantalla principal con las tablas filtradas por semana.
     */
   public function index(Request $request)
{
    // El formato correcto para el navegador es 'Y-\WW' (ejemplo exacto para hoy: "2026-W27")
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

    return view('recepcion.index', [
        'recepcionesNacionales'    => $recepcionesNacionales,
        'recepcionesExportaciones' => $recepcionesExportaciones,
        'productores'              => $productores,
        'embarquesExportacion'     => $embarquesExportacion,
        'semanaActiva'             => $semanaInput // Ahora sí va con el formato correcto
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
            'recepcion_exportacion_id' => ['nullable', 'exists:recepcion_exportaciones,id'], // Validación de trazabilidad
        ]);

        // Los criterios de búsqueda ahora contemplan el operador, el sector y el embarque de origen
        $criteriosBusqueda = [
            'fecha_nacional'           => $request->fecha_nacional,
            'productor_id'             => $request->productor_id,
            'sector_registro'          => $request->sector_registro,
            'recepcion_exportacion_id' => $request->recepcion_exportacion_id, // Permite separar registros estándar de mermas de exportación
        ];

        $registroExistente = RecepcionNacional::where($criteriosBusqueda)->first();

        if ($registroExistente) {
            $cajasComercial = $request->filled('cajas_comercializar') && $request->cajas_comercializar > 0
                ? $request->cajas_comercializar
                : $registroExistente->cajas_comercializar;

            $pesoComercial = $request->filled('peso_comercializar') && $request->peso_comercializar > 0
                ? $request->peso_comercializar
                : $registroExistente->peso_comercializar;

            $cajasRechazo = $request->filled('cajas_rechazo_procesado') && $request->cajas_rechazo_procesado > 0
                ? $request->cajas_rechazo_procesado
                : $registroExistente->cajas_rechazo_procesado;

            $pesoRechazo = $request->filled('peso_rechazo_procesado') && $request->peso_rechazo_procesado > 0
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
            $criteriosBusqueda,
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
            ]
        );

        // 2. DISPARADOR DE TRAZABILIDAD AUTOMÁTICA
        // Si el registro está vinculado a un embarque de exportación, actualizamos los saldos de ese embarque
        if ($request->filled('recepcion_exportacion_id')) {
            $embarque = RecepcionExportacion::find($request->recepcion_exportacion_id);

            if ($embarque) {
                // Buscamos y sumamos todos los rechazos capturados para este embarque específico
                $totalCajasRechazoAsociadas = RecepcionNacional::where('recepcion_exportacion_id', $embarque->id)
                    ->sum('cajas_rechazo_procesado');

                // Las cajas restituidas (netas) serán las cajas exportadas originales menos las rechazadas
                $restituidasCalculadas = $embarque->cajas_exportacion - $totalCajasRechazoAsociadas;

                // El saldo pendiente o diferencia se actualiza con el total rechazado
                $embarque->update([
                    'restituidas' => $restituidasCalculadas >= 0 ? $restituidasCalculadas : 0,
                    'pendientes'  => $totalCajasRechazoAsociadas
                ]);
            }
        }

        return redirect()->route('recepcion.index')->with('status', 'La información de la recepción fue consolidada correctamente y los saldos de exportación fueron actualizados.');
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
            'cajas_restituidas'  => ['required', 'integer', 'min:0'],
            'cajas_pendientes'   => ['required', 'integer', 'min:0'],
        ]);

        RecepcionExportacion::create([
            'semana_exportacion' => $request->semana_exportacion,
            'fecha_exportacion'  => $request->fecha_exportacion,
            'productor_id'       => $request->productor_id,
            'sector_registro'    => $request->sector_registro,
            'cajas_exportacion'  => $request->cajas_exportadas,
            'peso_exportacion'   => $request->peso_exportacion,
            'restituidas'        => $request->cajas_restituidas,
            'pendientes'         => $request->cajas_pendientes,
            'capturado_por_id'   => auth()->id(),
        ]);

        return redirect()->route('recepcion.index')->with('status', 'Embarque de exportación registrado con éxito.');
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
            'peso_comercializar'     => ['required', 'numeric', 'min:0'],
            'peso_rechazo_procesado' => ['required', 'numeric', 'min:0'],
        ]);

        $registro = RecepcionNacional::findOrFail($id);

        $pesoComercialOriginal = $registro->peso_comercializar_original ?? $registro->getOriginal('peso_comercializar');
        $pesoRechazoOriginal   = $registro->peso_rechazo_procesado_original ?? $registro->getOriginal('peso_rechazo_procesado');

        $nuevoTotalKg = $request->peso_comercializar + $request->peso_rechazo_procesado;

        $registro->update([
            'peso_comercializar_original'     => $pesoComercialOriginal,
            'peso_comercializar'              => $request->peso_comercializar,
            'peso_rechazo_procesado_original' => $pesoRechazoOriginal,
            'peso_rechazo_procesado'          => $request->peso_rechazo_procesado,
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

    public function showNacional($id)
    {
        // Buscamos el registro nacional junto con su productor/operador asignado
        $registro = RecepcionNacional::with('productor')->findOrFail($id);

        // Declaramos la variable de control de tipo para la tarjeta de resumen
        $tipo = 'nacional';

        return view('recepcion.show', [
            'registro' => $registro,
            'tipo'     => $tipo
        ]);
    }
}
