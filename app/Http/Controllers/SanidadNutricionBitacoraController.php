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
        // Validación general de los datos entrantes (Maestro + Subformularios)
        $request->validate([
            // Datos Maestro
            'fecha' => 'required|date',
            'sector' => 'required|string|max:255',
            'operador_id' => 'required|exists:users,id',

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
            
            // 💡 RECTIFICADO: Validación adaptada a los nuevos inputs ocultos del sector
            'variedad_sector' => 'nullable|string|max:255',
            'numero_plantas_sector' => 'nullable|integer',
            'fecha_trasplante_sector' => 'nullable|date',
            
            // Selector dual para solución
            'tipo_solucion' => 'required|array',
            'tipo_solucion.*' => 'required|string|in:SOLUCION MADRE,SOLUCION DIARIA',

            'agroquimicos_observaciones' => 'nullable|array',

            // VALIDACIÓN DEL SUBDETALLE: Control de índices de los bloques de tanques
            'tanques_indices' => 'required|array',
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
                
                $opcionSolucion = $request->tipo_solucion[$index];
                $valSolucionMadre = ($opcionSolucion === 'SOLUCION MADRE') ? 'SÍ' : null; 
                $valSolucionDiaria = ($opcionSolucion === 'SOLUCION DIARIA') ? 'SÍ' : null;

                ManejoAgroquimico::create([
                    'bitacora_id'            => $bitacora->id,
                    'fecha_aplicacion'       => $request->fecha_aplicacion[$index],
                    'aplicacion'             => $request->aplicacion[$index],
                    'productor'              => $request->productor[$index] ?? null,
                    'producto'               => $request->producto[$index],
                    'dosis'                  => $request->dosis[$index],
                    'unidad_dosis'           => $request->unidad_dosis[$index],
                    'is_intervalo_seguridad' => $request->is_intervalo_seguridad[$index] ?? null,
                    
                    // 💡 CAMBIO CLAVE: Asignación global directa desde los campos ocultos del sector
                    'variedad'               => $request->variedad_sector,
                    'numero_plantas'         => $request->numero_plantas_sector,
                    'fecha_trasplante'       => $request->fecha_trasplante_sector,
                    
                    'solucion_madre'         => $valSolucionMadre,
                    'solucion_diaria'        => $valSolucionDiaria,
                    'observaciones'          => $request->agroquimicos_observaciones[$index] ?? null,
                ]);
            }

            // 3. PROCESAR SUBDETALLE ANIDADO: Tanque -> Múltiples Acciones con Dosis individuales
            foreach ($request->tanques_indices as $tIdx) {
                // Obtenemos el nombre específico asignado a este bloque de tanque
                $nombreTanque = $request->input("tanque_{$tIdx}");
                
                // Extraemos las listas de acciones y dosis vinculadas a este ID de tanque
                $acciones   = $request->input("accion_texto_{$tIdx}", []);
                $cantidades = $request->input("cantidad_{$tIdx}", []);
                $unidades   = $request->input("unidad_cantidad_{$tIdx}", []);

                // Iteramos sobre cada una de las acciones agregadas en el Tanque actual
                foreach ($acciones as $aIdx => $accionTexto) {
                    ManejoFertilizante::create([
                        'bitacora_id'        => $bitacora->id,
                        'tanque'             => $nombreTanque,
                        'accion'             => $accionTexto,
                        'cantidad'           => $cantidades[$aIdx] ?? 0,
                        'unidad_cantidad'    => $unidades[$aIdx] ?? 'g',
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
}