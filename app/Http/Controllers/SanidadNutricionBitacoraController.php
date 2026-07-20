<?php

namespace App\Http\Controllers;

use App\Models\SanidadNutricionBitacora;
use App\Models\ManejoAgroquimico;
use App\Models\ManejoFertilizante;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

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
    $user = auth()->user();

    // 1. NUEVO: Obtener todos los operadores con sus sectores para el mapeo dinámico en JS
    $operadores = User::where('rol', 'operador')
        ->select('id', 'name', 'sectores')
        ->orderBy('name', 'asc')
        ->get();

    // 2. Mantenemos tu lógica original intacta para la obtención de sectores según el rol
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
        $sectores = array_unique($sectoresUnicos);
        sort($sectores);
    } else {
        $sectoresTexto = $user->sectores;
        $sectores = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];
    }

    // 3. Pasamos tanto tus sectores como la nueva colección de operadores a la vista
    return view('sanidad.create', compact('sectores', 'operadores'));
}

    public function store(Request $request)
    {
        // Validación general de los datos entrantes (Maestro + Subformularios)
        $request->validate([
            // Datos Maestro
            'fecha' => 'required|date',
            'sector' => 'required|string|max:255',

            // Arreglos de Manejo de Agroquímicos
            'fecha_aplicacion' => 'required|array',
            'fecha_aplicacion.*' => 'required|date',
            'aplicacion' => 'required|array',
            'aplicacion.*' => 'required|string|in:RIEGO,FOLIAR,DRENCH',
            'producto' => 'required|array',
            'producto.*' => 'required|string|max:255',
            'dosis' => 'required|array',
            'dosis.*' => 'required|numeric|min:0',
            'unidad_dosis' => 'required|array',
            'unidad_dosis.*' => 'required|string',
            'is_intervalo_seguridad' => 'nullable|array',
            'variedad' => 'nullable|array',
            'numero_plantas' => 'nullable|array',
            'solucion_madre' => 'nullable|array',
            'fecha_trasplante' => 'nullable|array',
            'solucion_diaria' => 'nullable|array',
            'agroquimicos_observaciones' => 'nullable|array',

            // Arreglos de Manejo de Fertilizantes
            'tanque' => 'required|array',
            'tanque.*' => 'required|string|max:100',
            'cantidad' => 'required|array',
            'cantidad.*' => 'required|numeric|min:0',
            'unidad_cantidad' => 'required|array',
            'unidad_cantidad.*' => 'required|string',
            'labores_culturales' => 'nullable|string|max:255',
            'fertilizantes_observaciones' => 'nullable|string|max:255',
        ]);

        // Iniciamos la transacción segura
        DB::beginTransaction();

        try {
            // 1. Crear el Registro Maestro
            $bitacora = SanidadNutricionBitacora::create([
                'fecha' => $request->fecha,
                'sector' => $request->sector,
                'operador_id' => $request->operador_id,
            ]);

            // 2. Procesar e insertar bloque: Manejo de Agroquímicos
            foreach ($request->aplicacion as $index => $val) {
                ManejoAgroquimico::create([
                    'bitacora_id'            => $bitacora->id,
                    'fecha_aplicacion'       => $request->fecha_aplicacion[$index],
                    'aplicacion'             => $request->aplicacion[$index],
                    'productor'              => $request->productor[$index] ?? null,
                    'producto'               => $request->producto[$index],
                    'dosis'                  => $request->dosis[$index],
                    'unidad_dosis'           => $request->unidad_dosis[$index],
                    'is_intervalo_seguridad' => $request->is_intervalo_seguridad[$index] ?? null,
                    'variedad'               => $request->variedad[$index] ?? null,
                    'numero_plantas'         => $request->numero_plantas[$index] ?? null,
                    'solucion_madre'         => $request->solucion_madre[$index] ?? null,
                    'fecha_trasplante'       => $request->fecha_trasplante[$index] ?? null,
                    'solucion_diaria'        => $request->solucion_diaria[$index] ?? null,
                    'observaciones'          => $request->agroquimicos_observaciones[$index] ?? null,
                ]);
            }

            // 3. Procesar e insertar bloque: Manejo de Fertilizantes
            foreach ($request->tanque as $index => $val) {
                ManejoFertilizante::create([
                    'bitacora_id'        => $bitacora->id,
                    'tanque'             => $request->tanque[$index],
                    'cantidad'           => $request->cantidad[$index],
                    'unidad_cantidad'    => $request->unidad_cantidad[$index],
                    'labores_culturales' => $request->labores_culturales[$index] ?? null,
                    'observaciones'      => $request->fertilizantes_observaciones[$index] ?? null,
                ]);
            }

            // Si todo salió bien, confirmamos los cambios permanentes
            DB::commit();

            return redirect()->route('sanidad.index')->with('status', '¡Bitácora de Sanidad y Nutrición guardada con éxito!');

        } catch (\Exception $e) {
            // Si algo falla, cancelamos todo el proceso para evitar datos corruptos
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors(['error' => 'Error al guardar el registro: ' . $e->getMessage()]);
        }
    }
}