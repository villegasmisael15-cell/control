<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Reporte;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $mes = $request->get('mes', date('n'));
        $semana = $request->get('semana');
        $anioActual = date('Y');

        // 1. Consulta base uniendo tus tablas originales
        $query = DB::table('recepcion_exportaciones')
            ->leftJoin('reportes', 'recepcion_exportaciones.id', '=', 'reportes.recepcion_exportacion_id')
            ->leftJoin('recepcion_nacionales', 'recepcion_exportaciones.id', '=', 'recepcion_nacionales.recepcion_exportacion_id')
            ->select(
                'recepcion_exportaciones.id as recepcion_id', 
                'recepcion_exportaciones.sector_registro as recepcion_sector', 
                'recepcion_exportaciones.peso_exportacion as total_kg',  
                'recepcion_exportaciones.peso_neto_fijo as aceptados_kg',
                'recepcion_nacionales.peso_rechazo_procesado_original as rechazados_kg',
                'reportes.id as reporte_id',
                'reportes.empacados',
                'reportes.nacional',
                'reportes.rechazo_post', 
                'reportes.observaciones',
                'reportes.aprobado',
                'recepcion_exportaciones.fecha_exportacion',
                'recepcion_exportaciones.productor_id'
            );

        // 2. FILTRO DE SEGURIDAD POR ROL (Estricto del negocio)
        if ($user->rol === 'operador') {
            $query->where('reportes.aprobado', true)
                  ->where(function($q) use ($user) {
                      $q->where('recepcion_exportaciones.productor_id', $user->id)
                        ->orWhere('recepcion_exportaciones.sector_registro', $user->sector);
                  });
        } elseif ($user->rol !== 'administrador') {
            abort(403, 'No autorizado.');
        }

        // 3. FILTRO DE FECHAS OPERATIVAS (fecha_exportacion)
        if ($semana) {
            switch ($semana) {
                case '1': $inicio = "$anioActual-$mes-01"; $fin = "$anioActual-$mes-07"; break;
                case '2': $inicio = "$anioActual-$mes-08"; $fin = "$anioActual-$mes-14"; break;
                case '3': $inicio = "$anioActual-$mes-15"; $fin = "$anioActual-$mes-21"; break;
                case '4': 
                    $inicio = "$anioActual-$mes-22"; 
                    $query->whereBetween('recepcion_exportaciones.fecha_exportacion', [$inicio, DB::raw("LAST_DAY('$inicio')")]);
                    break;
            }
            if ($semana !== '4') {
                $query->whereBetween('recepcion_exportaciones.fecha_exportacion', ["$inicio 00:00:00", "$fin 23:59:59"]);
            }
        } else {
            $query->whereMonth('recepcion_exportaciones.fecha_exportacion', $mes)
                  ->whereYear('recepcion_exportaciones.fecha_exportacion', $anioActual);
        }

        $registrosRaw = $query->orderBy('recepcion_exportaciones.fecha_exportacion', 'desc')->get();

        // 4. MAPEO Y CÁLCULO DINÁMICO
        $reportes = $registrosRaw->map(function($item) {
            $fechaFiltroDiario = date('Y-m-d', strtotime($item->fecha_exportacion));
            
            // Peso Neto Fijo o calculado inicial
            if (!is_null($item->aceptados_kg)) {
                $pesoNeto = (float)$item->aceptados_kg;
            } else {
                $pesoRechazo = $item->rechazados_kg !== null ? (float)$item->rechazados_kg : 0;
                $pesoNeto = (float)$item->total_kg - $pesoRechazo;
            }
            
            // LÓGICA DE RECHAZO POST: Se le resta al aceptado neto del operador
            $rechazoPostObtenido = $item->rechazo_post !== null ? (float)$item->rechazo_post : 0.00;
            $kilosAceptadosFinales = $pesoNeto - $rechazoPostObtenido;
            $kilosAceptadosFinales = $kilosAceptadosFinales >= 0 ? $kilosAceptadosFinales : 0.00;

            // Consultar el valor diario de Agropark ingresado globalmente
            $controlCondensacion = DB::table('control_condensaciones')->whereDate('fecha', $fechaFiltroDiario)->first();
            $agroparkDelDia = $controlCondensacion ? (float)$controlCondensacion->agropark : 0.0;

            // Suma de pesos totales del mismo día
            $sumaPesosDiarios = DB::table('recepcion_exportaciones')
                ->whereDate('fecha_exportacion', $fechaFiltroDiario)
                ->get()
                ->sum(function($reg) {
                    return !is_null($reg->peso_neto_fijo) ? (float)$reg->peso_neto_fijo : (float)$reg->peso_exportacion;
                });

            // Fórmulas automáticas de visualización
            $destinoCalculado = 0;
            if ($kilosAceptadosFinales > 0 && $agroparkDelDia > 0 && $sumaPesosDiarios > 0) {
                $destinoCalculado = $kilosAceptadosFinales * ($agroparkDelDia / $sumaPesosDiarios);
            }

            $participacionCalculada = 0;
            if ($destinoCalculado > 0 && $agroparkDelDia > 0) {
                $participacionCalculada = ($destinoCalculado * 100) / $agroparkDelDia;
            }

            $operadorName = DB::table('users')->where('id', $item->productor_id)->value('name');

            return (object)[
                'recepcion_id'      => $item->recepcion_id,
                'recepcion_sector'  => $item->recepcion_sector,
                'total_kg'          => $item->total_kg,
                'rechazados_kg'     => $item->rechazados_kg,
                'aceptados_kg'      => $kilosAceptadosFinales,
                'rechazo_post'      => $rechazoPostObtenido,
                'reporte_id'        => $item->reporte_id,
                'observaciones'     => $item->observaciones,
                'aprobado'          => $item->aprobado, 
                'operador_name'     => $operadorName ?? 'Desconocido',
                'fecha_registro'    => $fechaFiltroDiario,
                'destino'           => $destinoCalculado > 0 ? $destinoCalculado : null,
                'participacion'     => $participacionCalculada > 0 ? number_format($participacionCalculada, 2) . '%' : '—',
                'nacional'          => $item->nacional,
                'empacados'         => $item->empacados
            ];
        });

        return view('reportes.index', compact('reportes'));
    }

 

      public function update(Request $request, $recepcionId)
    {
        if (auth()->user()->rol !== 'administrador') {
            abort(403, 'No autorizado.');
        }

        $request->validate([
            'empacados'     => 'nullable|numeric|min:0',
            'nacional'      => 'nullable|numeric|min:0', 
            'rechazo_post'  => 'nullable|numeric|min:0',
            'kg_vendidos'   => 'nullable|numeric|min:0',
            'observaciones' => 'nullable|string',
        ]);

        try {
            $registro = \App\Models\RecepcionExportacion::find($recepcionId);

            if (!$registro) {
                return redirect()->back()->withErrors(['error' => 'No se encontró la recepción origen']);
            }

            $reporteExistente = DB::table('reportes')->where('recepcion_exportacion_id', $recepcionId)->first();

            // 1. Mantener o actualizar el estatus de aprobación
            $estatusAprobado = $request->has('aprobado') 
                ? (bool)$request->input('aprobado') 
                : ($reporteExistente ? (bool)$reporteExistente->aprobado : false);

            // 2. Calcular la base original (Peso Neto de báscula inicial)
            if (!is_null($registro->peso_neto_fijo)) {
                $pesoNetoInicial = (float)$registro->peso_neto_fijo;
            } else {
                $pesoRechazoAcumulado = $registro->receccionesNacionales ? $registro->receccionesNacionales->sum('peso_rechazo_procesado') : 0;
                $pesoNetoInicial = (float)$registro->peso_exportacion - $pesoRechazoAcumulado;
            }
            $pesoNetoInicial = $pesoNetoInicial >= 0 ? $pesoNetoInicial : 0.00;

            // 3. Recuperar los datos de Condensación/Agropark guardados para este día específico
            $fechaFiltroDiario = \Carbon\Carbon::parse($registro->fecha_exportacion)->format('Y-m-d');
            $controlCondensacion = DB::table('control_condensaciones')->whereDate('fecha', $fechaFiltroDiario)->first();
            
            $agroparkDiario = $controlCondensacion ? (float)$controlCondensacion->agropark : 0.00;
            $factorMultiplicador = $controlCondensacion ? (float)$controlCondensacion->factor_multiplicador : 0.00;
            $nacionalGlobalDiario = $controlCondensacion ? (float)$controlCondensacion->total_nacional_global : 0.00;

            // Obtener la suma total de los pesos netos iniciales del día para la fórmula proporcional
            $sumaPesosDiarios = \App\Models\RecepcionExportacion::whereDate('fecha_exportacion', $fechaFiltroDiario)
                ->get()
                ->sum(function($item) {
                    return !is_null($item->peso_neto_fijo) ? (float)$item->peso_neto_fijo : (float)$item->peso_exportacion;
                });

            // 4. PROCESAR SEGÚN EL MODAL QUE ESTÁ ENVIANDO LOS DATOS
            if ($request->has('rechazo_post') && !$request->has('kg_vendidos') && !$request->has('observaciones')) {
                
                // --- ACCIÓN: MODAL DE RECHAZO POSTERIOR (DESCARTE) ---
                $rechazoPostFinal = (float)$request->rechazo_post;
                $aceptadosKgFinal = $pesoNetoInicial - $rechazoPostFinal;
                $aceptadosKgFinal = $aceptadosKgFinal >= 0 ? $aceptadosKgFinal : 0.00;

                // ¡RECALCULO EN CALIENTE DE LAS FÓRMULAS CON EL NUEVO DATO ACEPTADO!
                $destinoFinal = 0.00;
                $participacionCalculada = 0.00;
                $nacionalFinal = 0.00;
                $empacadosFinal = 0.00;

                if ($sumaPesosDiarios > 0 && $agroparkDiario > 0) {
                    // FÓRMULA B: Destino Condensación recalculado en base a los kilos aceptados corregidos
                    $divisionDiaria = $agroparkDiario / $sumaPesosDiarios;
                    $destinoFinal = $aceptadosKgFinal * $divisionDiaria;

                    // FÓRMULA C: Nueva participación
                    if ($destinoFinal > 0) {
                        $participacionCalculada = ($destinoFinal * 100) / $agroparkDiario;
                    }

                    // FÓRMULA 3: Nuevo cálculo proporcional de Fruta Nacional
                    $nacionalFinal = ($nacionalGlobalDiario * $participacionCalculada) / 100;

                    // FÓRMULA 4: Nuevo cálculo final de Empacados
                    if ($factorMultiplicador > 0) {
                        $empacadosFinal = ($destinoFinal - $nacionalFinal) * $factorMultiplicador;
                        if ($empacadosFinal < 0) $empacadosFinal = 0.00;
                    }
                }

            } else {
                
                // --- ACCIÓN: MODAL DE CAPTURA GENERAL / DATOS MANUALES ---
                $estatusAprobado = true; // Forzar visibilidad al operador al guardar datos manuales

                $rechazoPostFinal = $reporteExistente ? (float)$reporteExistente->rechazo_post : 0.00;
                $aceptadosKgFinal = $pesoNetoInicial - $rechazoPostFinal;
                $aceptadosKgFinal = $aceptadosKgFinal >= 0 ? $aceptadosKgFinal : 0.00;
                
                $empacadosFinal = $request->filled('empacados') ? (float)$request->empacados : ($reporteExistente ? (float)$reporteExistente->empacados : 0.00);
                $nacionalFinal  = $request->filled('nacional') ? (float)$request->nacional : ($reporteExistente ? (float)$reporteExistente->nacional : 0.00);

                $destinoFinal = 0.00;
                if ($aceptadosKgFinal > 0 && $agroparkDiario > 0 && $sumaPesosDiarios > 0) {
                    $divisionDiaria = $agroparkDiario / $sumaPesosDiarios;
                    $destinoFinal = $aceptadosKgFinal * $divisionDiaria;
                }
            }

            // 5. GUARDAR O ACTUALIZAR DE FORMA PERSISTENTE EN LA TABLA reportes
            if ($reporteExistente) {
                DB::table('reportes')
                    ->where('id', $reporteExistente->id)
                    ->update([
                        'user_id'       => $registro->productor_id, 
                        'sector'        => $registro->sector_registro, 
                        'empacados'     => $empacadosFinal,
                        'nacional'      => $nacionalFinal,
                        'rechazo_post'  => $rechazoPostFinal,
                        'aceptados_kg'  => $aceptadosKgFinal,
                        'destino'       => $destinoFinal, 
                        'observaciones' => $request->has('observaciones') ? $request->observaciones : $reporteExistente->observaciones,
                        'aprobado'      => $estatusAprobado,
                        'updated_at'    => now(),
                    ]);
            } else {
                DB::table('reportes')->insert([
                    'recepcion_exportacion_id' => $recepcionId,
                    'user_id'                  => $registro->productor_id, 
                    'sector'                   => $registro->sector_registro, 
                    'empacados'                => $empacadosFinal,
                    'nacional'                 => $nacionalFinal, 
                    'rechazo_post'             => $rechazoPostFinal,
                    'aceptados_kg'             => $aceptadosKgFinal,
                    'destino'                  => $destinoFinal,
                    'observaciones'            => $request->observaciones,
                    'aprobado'                 => $estatusAprobado,
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ]);
            }

            // 6. ACTUALIZAR EN LA TABLA reportes_nacionales (Kg Vendidos)
            $nombreProductor = DB::table('users')->where('id', $registro->productor_id)->value('name');
            $recepcionNacionalFisica = DB::table('recepcion_nacionales')->where('recepcion_exportacion_id', $recepcionId)->first();
            $kgRecepcionNacional = $recepcionNacionalFisica->total_kg ?? $registro->peso_exportacion ?? 0.00;
            
            $reporteNacExistente = DB::table('reportes_nacionales')->where('recepcion_exportacion_id', $recepcionId)->first();

            if ($reporteNacExistente) {
                DB::table('reportes_nacionales')->where('id', $reporteNacExistente->id)->update([
                    'fecha'       => $registro->created_at ? date('Y-m-d', strtotime($registro->created_at)) : now()->toDateString(),
                    'productor'   => $nombreProductor ?? 'Desconocido',
                    'sector'      => $registro->sector_registro,
                    'total_kg'    => $kgRecepcionNacional,
                    'kg_vendidos' => $request->has('kg_vendidos') ? $request->kg_vendidos : ($reporteNacExistente->kg_vendidos ?? 0.00),
                    'updated_at'  => now(),
                ]);
            } else {
                DB::table('reportes_nacionales')->insert([
                    'recepcion_exportacion_id' => $recepcionId,
                    'fecha'                    => $registro->created_at ? date('Y-m-d', strtotime($registro->created_at)) : now()->toDateString(),
                    'productor'                => $nombreProductor ?? 'Desconocido',
                    'sector'                   => $registro->sector_registro,
                    'total_kg'                 => $kgRecepcionNacional,
                    'kg_vendidos'              => $request->kg_vendidos ?? 0.00,
                    'total_semana'             => 0.00,
                    'created_at'               => now(),
                    'updated_at'               => now(),
                ]);
            }

            return redirect()->back()->with('success', '¡Datos recalculados y guardados con éxito en la base de datos!');

        } catch (\Exception $e) {
            return redirect()->back()->withErrors(['error' => 'Error crítico al procesar el recálculo: ' . $e->getMessage()]);
        }
    }




public function descargarPDF($id)
    {
        $reporte = DB::table('recepcion_exportaciones')
            ->leftJoin('reportes', 'recepcion_exportaciones.id', '=', 'reportes.recepcion_exportacion_id')
            ->leftJoin('recepcion_nacionales', 'recepcion_exportaciones.id', '=', 'recepcion_nacionales.recepcion_exportacion_id')
            ->leftJoin('reportes_nacionales', 'recepcion_exportaciones.id', '=', 'reportes_nacionales.recepcion_exportacion_id')
            ->select(
                'recepcion_exportaciones.id as recepcion_id', 
                'recepcion_exportaciones.sector_registro as recepcion_sector', 
                'recepcion_exportaciones.peso_exportacion as total_kg',  
                // CORRECCIÓN: Usar el valor calculado con descarte de la tabla de reportes
                'reportes.aceptados_kg as aceptados_kg',
                // CORRECCIÓN: Traer el rechazo procesado real de la báscula
                'recepcion_nacionales.peso_rechazo_procesado as rechazados_kg',
                'reportes.id as reporte_id',
                'reportes.empacados',
                'reportes.nacional',
                'reportes.rechazo_post',
                'reportes.observaciones',
                'reportes.destino',
                'recepcion_exportaciones.fecha_exportacion as fecha_exportacion',
                'reportes_nacionales.fecha as nac_fecha',
                'reportes_nacionales.productor as nac_productor',
                'reportes_nacionales.sector as nac_sector',
                'reportes_nacionales.total_kg as nac_kg_recepcion', 
                'reportes_nacionales.kg_vendidos as nac_kg_vendidos',
                'reportes_nacionales.total_semana as nac_total_semana'
            )
            ->where('recepcion_exportaciones.id', $id)
            ->first();

        if (!$reporte) {
            abort(404, 'El reporte solicitado no existe.');
        }

        $pdf = Pdf::loadView('reportes.ticket_pdf', compact('reporte'));
        return $pdf->download('Comprobante_' . $reporte->recepcion_sector . '.pdf');
    }
}