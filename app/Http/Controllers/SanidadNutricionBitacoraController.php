<?php

namespace App\Http\Controllers;

use App\Models\SanidadNutricionBitacora;
use App\Models\ManejoAgroquimico;
use App\Models\ManejoFertilizante;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class SanidadNutricionBitacoraController extends Controller
{
    public function index(Request $request)
    {
        // 1. Carga la bitácora con sus relaciones y ordena de forma cronológica descendente
       $query = SanidadNutricionBitacora::with(['operador', 'agroquimicos', 'fertilizantes'])
        ->orderBy('fecha', 'desc');

        // 2. Control de accesos por Rol
        if (auth()->user()->rol !== 'administrador') {
            $sectoresTexto = auth()->user()->sectores;
            $sectoresAsignados = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];
            $query->whereIn('sector', $sectoresAsignados);
        } else {
            // Buscador unificado para el Administrador
            if ($request->filled('buscar_termino')) {
                $termino = $request->input('buscar_termino');
                $query->where(function ($q) use ($termino) {
                    $q->where('sector', 'LIKE', '%' . $termino . '%')
                      ->orWhereHas('user', function ($subQuery) use ($termino) {
                          $subQuery->where('name', 'LIKE', '%' . $termino . '%');
                      });
                });
            }
        }

        // 3. Filtros temporales (Semana / Mes)
        $semana = $request->input('semana');
        $mes = $request->input('mes');

        if ($request->filled('semana') && $request->filled('mes')) {
            if ($request->session()->get('ultimo_filtro_sanidad') === 'mes') {
                $mes = null;
                $request->merge(['mes' => null]);
            } else {
                $semana = null;
                $request->merge(['semana' => null]);
            }
        }

        if (!empty($semana)) {
            $request->session()->put('ultimo_filtro_sanidad', 'semana');
            [$year, $week] = explode('-W', $semana);
            $inicioSemana = Carbon::now()->setISODate($year, $week)->startOfWeek();
            $finSemana = Carbon::now()->setISODate($year, $week)->endOfWeek();
            $query->whereBetween('fecha', [$inicioSemana, $finSemana]);
        }

        if (!empty($mes)) {
            $request->session()->put('ultimo_filtro_sanidad', 'mes');
            $inicioMes = Carbon::parse($mes)->startOfMonth();
            $finMes = Carbon::parse($mes)->endOfMonth();
            $query->whereBetween('fecha', [$inicioMes, $finMes]);
        }

        if (empty($semana) && empty($mes)) {
            $request->session()->forget('ultimo_filtro_sanidad');
        }

        $bitacoras = $query->get();

        return view('sanidad.index', compact('bitacoras'));
    }

    public function create()
    {
        if (auth()->user()->rol !== 'administrador') {
            return redirect()->route('sanidad.index')
                ->withErrors(['error' => 'Acceso denegado. Solo el administrador puede asignar bitácoras.']);
        }
        $user = auth()->user();

        // 1. Obtener todos los operadores para el mapeo en el selector inicial
        $operadores = User::where('rol', 'operador')
            ->select('id', 'name', 'sectores')
            ->orderBy('name', 'asc')
            ->get();

        // 2. Obtener lista de sectores crudos según los permisos del rol
        if ($user->rol === 'administrador') {
            $todosLosSectoresTexto = User::whereNotNull('sectores')->pluck('sectores')->toArray();
            $sectoresUnicos = [];
            foreach ($todosLosSectoresTexto as $cadena) {
                $partes = explode(',', $cadena);
                foreach ($partes as $sector) {
                    $sectorLimpio = trim($sector);
                    if (!empty($sectorLimpio)) {
                        $sectoresUnicos[] = $sectorLimpio;
                    }
                }
            }
            $listaSectores = array_unique($sectoresUnicos);
        } else {
            $sectoresTexto = $user->sectores;
            $listaSectores = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];
        }

        // 3. ESTRUCTURA COMPLETA: Mapea variedad, fecha de trasplante y número de plantas por sector
        $sectoresConVariedad = [];
        foreach ($listaSectores as $sectorName) {
            $caracteristica = DB::table('sector_caracteristicas')
                ->where('sector', $sectorName)
                ->first();

            $sectoresConVariedad[$sectorName] = [
                'variedad'         => $caracteristica ? $caracteristica->variedad : '',
                'fecha_trasplante' => $caracteristica ? $caracteristica->fecha_trasplante : '',
                'numero_plantas'   => $caracteristica ? $caracteristica->numero_plantas : ''
            ];
        }

        // Ordenamos alfabéticamente por sector
        ksort($sectoresConVariedad);

        // 4. Mandamos las colecciones completas a la vista
        return view('sanidad.create', compact('operadores', 'sectoresConVariedad'));
    }

    public function store(Request $request)
    {
        if (auth()->user()->rol !== 'administrador') {
            return redirect()->route('sanidad.index')
                ->withErrors(['error' => 'Acceso denegado. No tiene permisos para guardar registros.']);
        }

        // 1. Validación general adaptada a la nueva estructura de bloques
        $request->validate([
            'fecha' => 'required|date',
            'sector' => 'required|string|max:255',
            'operador_id' => 'required|exists:users,id',
            'agro_indices' => 'required|array',
            'tanques_indices' => 'required|array',
            'variedad_sector' => 'nullable|string|max:255',
            'numero_plantas_sector' => 'nullable|integer',
            'fecha_trasplante_sector' => 'nullable|date',
            'labores_culturales' => 'nullable|string|max:255',
            'fertilizantes_observaciones' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // 2. Crear el Registro Maestro
            $bitacora = SanidadNutricionBitacora::create([
                'fecha' => $request->fecha,
                'sector' => $request->sector,
                'operador_id' => $request->operador_id,
            ]);

            // 3. Procesar bloques dinámicos de Agroquímicos
            foreach ($request->agro_indices as $agId) {
                $fAplicacion = $request->input("fecha_aplicacion_{$agId}");
                $tAplicacion = $request->input("aplicacion_{$agId}");
                $productos   = $request->input("producto_{$agId}", []);
                $dosis       = $request->input("dosis_{$agId}", []);
                $unidades    = $request->input("unidad_dosis_{$agId}", []);
                $isValores   = $request->input("is_intervalo_seguridad_{$agId}", []);
                $obsValores  = $request->input("agroquimicos_observaciones_{$agId}", []);

                foreach ($productos as $pIdx => $nombreProducto) {
                    ManejoAgroquimico::create([
                        'bitacora_id'            => $bitacora->id,
                        'fecha_aplicacion'       => $fAplicacion,
                        'aplicacion'             => $tAplicacion,
                        'producto'               => $nombreProducto,
                        'dosis'                  => $dosis[$pIdx] ?? 0,
                        'unidad_dosis'           => $unidades[$pIdx] ?? 'mL',
                        'is_intervalo_seguridad' => $isValores[$pIdx] ?? null,
                        'variedad'               => $request->variedad_sector,
                        'numero_plantas'         => $request->numero_plantas_sector,
                        'fecha_trasplante'       => $request->fecha_trasplante_sector,
                        'solucion_madre'         => null, // Ya se maneja en fertilizantes
                        'solucion_diaria'        => null,
                        'observaciones'          => $obsValores[$pIdx] ?? null,
                    ]);
                }
            }

            // 4. Procesar bloques de Tanques y Fertilizantes (con Tipo de Solución por tanque)
            foreach ($request->tanques_indices as $tIdx) {
                $nombreTanque  = $request->input("tanque_{$tIdx}");
                $tipoSolucion  = $request->input("tipo_solucion_{$tIdx}"); // SOLUCION MADRE o SOLUCION DIARIA
                $acciones      = $request->input("accion_texto_{$tIdx}", []);
                $cantidades    = $request->input("cantidad_{$tIdx}", []);
                $unidadesFilt  = $request->input("unidad_cantidad_{$tIdx}", []);

                foreach ($acciones as $aIdx => $accionTexto) {
                    ManejoFertilizante::create([
                        'bitacora_id'        => $bitacora->id,
                        'tanque'             => $nombreTanque,
                        'tipo_solucion'      => $tipoSolucion, // Guardamos el tipo de solución por tanque
                        'accion'             => $accionTexto,
                        'cantidad'           => $cantidades[$aIdx] ?? 0,
                        'unidad_cantidad'    => $unidadesFilt[$aIdx] ?? 'g',
                        'labores_culturales' => $request->labores_culturales ?? null,
                        'observaciones'      => $request->fertilizantes_observaciones ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('sanidad.index')->with('status', '¡Bitácora de Sanidad y Nutrición guardada con éxito!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Error al guardar el registro: ' . $e->getMessage()]);
        }
    }


    public function edit($id)
    {
        if (auth()->user()->rol !== 'administrador') {
            return redirect()->route('sanidad.index')
                ->withErrors(['error' => 'Acceso denegado. Solo el administrador puede editar bitácoras.']);
        }

        $bitacora = SanidadNutricionBitacora::with(['agroquimicos', 'fertilizantes'])->findOrFail($id);
        $user = auth()->user();

        // 1. Obtener operadores
        $operadores = User::where('rol', 'operador')
            ->select('id', 'name', 'sectores')
            ->orderBy('name', 'asc')
            ->get();

        // 2. Obtener lista de sectores
        $todosLosSectoresTexto = User::whereNotNull('sectores')->pluck('sectores')->toArray();
        $sectoresUnicos = [];
        foreach ($todosLosSectoresTexto as $cadena) {
            $partes = explode(',', $cadena);
            foreach ($partes as $sector) {
                $sectorLimpio = trim($sector);
                if (!empty($sectorLimpio)) {
                    $sectoresUnicos[] = $sectorLimpio;
                }
            }
        }
        $listaSectores = array_unique($sectoresUnicos);

        // 3. Mapear características por sector
        $sectoresConVariedad = [];
        foreach ($listaSectores as $sectorName) {
            $caracteristica = DB::table('sector_caracteristicas')
                ->where('sector', $sectorName)
                ->first();

            $sectoresConVariedad[$sectorName] = [
                'variedad'         => $caracteristica ? $caracteristica->variedad : '',
                'fecha_trasplante' => $caracteristica ? $caracteristica->fecha_trasplante : '',
                'numero_plantas'   => $caracteristica ? $caracteristica->numero_plantas : ''
            ];
        }
        ksort($sectoresConVariedad);

        return view('sanidad.edit', compact('bitacora', 'operadores', 'sectoresConVariedad'));
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->rol !== 'administrador') {
            return redirect()->route('sanidad.index')
                ->withErrors(['error' => 'Acceso denegado. No tiene permisos para modificar registros.']);
        }

        $request->validate([
            'fecha' => 'required|date',
            'sector' => 'required|string|max:255',
            'operador_id' => 'required|exists:users,id',
            'agro_indices' => 'required|array',
            'tanques_indices' => 'required|array',
            'variedad_sector' => 'nullable|string|max:255',
            'numero_plantas_sector' => 'nullable|integer',
            'fecha_trasplante_sector' => 'nullable|date',
            'labores_culturales' => 'nullable|string|max:255',
            'fertilizantes_observaciones' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            $bitacora = SanidadNutricionBitacora::findOrFail($id);

            // 1. Actualizar Maestro
            $bitacora->update([
                'fecha' => $request->fecha,
                'sector' => $request->sector,
                'operador_id' => $request->operador_id,
            ]);

            // 2. Limpiar detalles anteriores para reemplazar con los nuevos editados
            $bitacora->agroquimicos()->delete();
            $bitacora->fertilizantes()->delete();

            // 3. Reinsertar bloques de Agroquímicos
            foreach ($request->agro_indices as $agId) {
                $fAplicacion = $request->input("fecha_aplicacion_{$agId}");
                $tAplicacion = $request->input("aplicacion_{$agId}");
                $productos   = $request->input("producto_{$agId}", []);
                $dosis       = $request->input("dosis_{$agId}", []);
                $unidades    = $request->input("unidad_dosis_{$agId}", []);
                $isValores   = $request->input("is_intervalo_seguridad_{$agId}", []);
                $obsValores  = $request->input("agroquimicos_observaciones_{$agId}", []);

                foreach ($productos as $pIdx => $nombreProducto) {
                    ManejoAgroquimico::create([
                        'bitacora_id'            => $bitacora->id,
                        'fecha_aplicacion'       => $fAplicacion,
                        'aplicacion'             => $tAplicacion,
                        'producto'               => $nombreProducto,
                        'dosis'                  => $dosis[$pIdx] ?? 0,
                        'unidad_dosis'           => $unidades[$pIdx] ?? 'mL',
                        'is_intervalo_seguridad' => $isValores[$pIdx] ?? null,
                        'variedad'               => $request->variedad_sector,
                        'numero_plantas'         => $request->numero_plantas_sector,
                        'fecha_trasplante'       => $request->fecha_trasplante_sector,
                        'observaciones'          => $obsValores[$pIdx] ?? null,
                    ]);
                }
            }

            // 4. Reinsertar bloques de Tanques y Fertilizantes
            foreach ($request->tanques_indices as $tIdx) {
                $nombreTanque  = $request->input("tanque_{$tIdx}");
                $tipoSolucion  = $request->input("tipo_solucion_{$tIdx}");
                $acciones      = $request->input("accion_texto_{$tIdx}", []);
                $cantidades    = $request->input("cantidad_{$tIdx}", []);
                $unidadesFilt  = $request->input("unidad_cantidad_{$tIdx}", []);

                foreach ($acciones as $aIdx => $accionTexto) {
                    ManejoFertilizante::create([
                        'bitacora_id'        => $bitacora->id,
                        'tanque'             => $nombreTanque,
                        'tipo_solucion'      => $tipoSolucion,
                        'accion'             => $accionTexto,
                        'cantidad'           => $cantidades[$aIdx] ?? 0,
                        'unidad_cantidad'    => $unidadesFilt[$aIdx] ?? 'g',
                        'labores_culturales' => $request->labores_culturales ?? null,
                        'observaciones'      => $request->fertilizantes_observaciones ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('sanidad.index')->with('status', '¡Bitácora actualizada con éxito!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Error al actualizar el registro: ' . $e->getMessage()]);
        }
    }
    

    public function destroy($id)
{
    // 1. Buscamos la bitácora principal
    $bitacora = SanidadNutricionBitacora::findOrFail($id);

    // 2. Si tus relaciones en el modelo maestro (sanidad_nutricion_bitacoras) 
    // están configuradas con onDelete('cascade'), puedes borrar directamente:
    $bitacora->agroquimicos()->delete();
    $bitacora->fertilizantes()->delete();

    // 3. Eliminamos el registro maestro
    $bitacora->delete();

    // 4. Redireccionamos con mensaje de éxito
    return redirect()->route('sanidad.index')->with('status', '¡La bitácora de sanidad y nutrición fue eliminada permanentemente!');
}

  public function pdf($id)
    {
        // Usamos tu modelo real: SanidadNutricionBitacora
        $bitacora = SanidadNutricionBitacora::with(['operador', 'agroquimicos', 'fertilizantes'])->findOrFail($id);

        // Cargamos la vista de PDF
        $pdf = Pdf::loadView('sanidad.pdf', compact('bitacora'));

        // Formato de hoja Carta vertical
        $pdf->setPaper('letter', 'portrait');

        // Retornamos el PDF para visualizarse en el navegador
        return $pdf->stream('Bitacora-Sanidad-Sector-' . $bitacora->sector . '-' . $bitacora->fecha . '.pdf');
    }
}