<?php

namespace App\Http\Controllers;

use App\Models\MonitoreoClimaRiego;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Exports\ReporteMonitoreoExport;
use Maatwebsite\Excel\Facades\Excel;

class MonitoreoClimaRiegoController extends Controller
{
   public function index(Request $request)
{
    // 1. Inicializar la consulta base con Eager Loading
    $query = MonitoreoClimaRiego::with('user')->orderBy('fecha', 'desc');

    // 2. RESTRICCIÓN POR ROL / FILTROS DE BÚSQUEDA ADICIONALES
    if (auth()->user()->rol !== 'administrador') {
        $sectoresTexto = auth()->user()->sectores;
        $sectoresAsignados = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];
        $query->whereIn('sector', $sectoresAsignados);
    } else {
        // --- BLOQUE EXCLUSIVO DE ADMINISTRADOR: BUSCADOR UNIFICADO ---
        if ($request->filled('buscar_termino')) {
            $termino = $request->input('buscar_termino');
            
            // Agrupamos con una función callback para evitar romper otros filtros como fechas
            $query->where(function ($q) use ($termino) {
                // Coincidencia directa por el nombre del sector
                $q->where('sector', 'LIKE', '%' . $termino . '%')
                  // O coincidencia a través de la relación con el operador
                  ->orWhereHas('user', function ($subQuery) use ($termino) {
                      $subQuery->where('name', 'LIKE', '%' . $termino . '%');
                  });
            });
        }
    }

    // 3. PROCESAR FILTROS DINÁMICOS (Semana / Mes)
    $semana = $request->input('semana');
    $mes = $request->input('mes');

    if ($request->filled('semana') && $request->filled('mes')) {
        if ($request->session()->get('ultimo_filtro') === 'mes') {
            $mes = null;
            $request->merge(['mes' => null]);
        } else {
            $semana = null;
            $request->merge(['semana' => null]);
        }
    }

    if (!empty($semana)) {
        $request->session()->put('ultimo_filtro', 'semana');
        [$year, $week] = explode('-W', $semana);
        $inicioSemana = \Illuminate\Support\Carbon::now()->setISODate($year, $week)->startOfWeek();
        $finSemana = \Illuminate\Support\Carbon::now()->setISODate($year, $week)->endOfWeek();
        $query->whereBetween('fecha', [$inicioSemana, $finSemana]);
    }

    if (!empty($mes)) {
        $request->session()->put('ultimo_filtro', 'mes');
        $inicioMes = \Illuminate\Support\Carbon::parse($mes)->startOfMonth();
        $finMes = \Illuminate\Support\Carbon::parse($mes)->endOfMonth();
        $query->whereBetween('fecha', [$inicioMes, $finMes]);
    }

    if (empty($semana) && empty($mes)) {
        $request->session()->forget('ultimo_filtro');
    }

    // 4. Obtener los registros finales ya filtrados
    $monitoreos = $query->get();

    return view('monitoreo.index', compact('monitoreos'));
}

    public function create()
    {
        $user = auth()->user();

        if ($user->rol === 'administrador') {
            // OBTENCIÓN DINÁMICA DE SECTORES DESDE LA BD
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
            // Si es un operador, mantiene solo sus sectores asignados
            $sectoresTexto = $user->sectores;
            $sectores = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];
        }

        return view('monitoreo.create', compact('sectores'));
    }

   public function store(Request $request)
    {
        // 1. Identificamos al verdadero dueño del sector antes de validar
        $sectorBuscado = trim($request->input('sector'));
        
        $duenoSector = User::where('sectores', 'LIKE', '%' . $sectorBuscado . '%')->first();
        
        // Si no encuentra al operador, usa el usuario logueado como respaldo
        $idDuenoReal = $duenoSector ? $duenoSector->id : auth()->id();

        // 2. Inyectamos la hora y el user_id real al request antes de validar
        $request->merge([
            'radiacion_hora' => now()->format('H:i:s'),
            'user_id'        => $idDuenoReal
        ]);

        // 3. Validar los datos
        $request->validate([
            'fecha' => 'required|date',
            'sector' => 'required|string|max:255',
            'temperatura' => 'nullable|numeric',
            'humedad' => 'nullable|numeric',
            'vol_riego_entrada' => 'nullable|numeric',
            'vol_drenaje_salida' => 'nullable|numeric',
            'ce_entrada' => 'nullable|numeric',
            'ce_salida' => 'nullable|numeric',
            'ph_entrada' => 'nullable|numeric',
            'ph_salida' => 'nullable|numeric',
            'peso_tarde_anterior' => 'nullable|numeric',
            'peso_manana' => 'nullable|numeric',
            'radiacion_lectura' => 'nullable|integer|min:0',
            'radiacion_semaforo' => 'nullable|string|max:255',
            'radiacion_accion_tomada' => 'nullable|string',
            'user_id' => 'required|exists:users,id',
        ]);

        // Lógica de riego por macetas
        $volRiego = $request->vol_riego_entrada;
        if (!is_null($volRiego)) {
            $caracteristica = \App\Models\SectorCaracteristica::where('sector', $request->sector)->first();
            $macetas = $caracteristica ? $caracteristica->macetas_por_gotero : 1;
            if ($macetas > 0) {
                $volRiego = (int) round($volRiego / $macetas);
            }
        }

        // Cálculos automatizados
        $dpv = null;
        $estatus_general = 'SIN DATOS CLIMA';

        if ($request->filled('temperatura') && $request->filled('humedad')) {
            $temp = $request->temperatura;
            $hum = $request->humedad;
            $dpv = round((0.61078 * exp((17.27 * $temp) / ($temp + 237.3))) * (1 - $hum / 100), 2);
            $estatus_general = ($dpv >= 0.8 && $dpv <= 1.4) ? 'ÓPTIMO' : 'REVISAR CLIMA';
        }

        $porcentaje_drenaje = null;
        if (!is_null($volRiego) && $request->filled('vol_drenaje_salida') && $volRiego > 0) {
            $porcentaje_drenaje = round(($request->vol_drenaje_salida / $volRiego) * 100, 1);
        }

        $diferencia_ce = null;
        if ($request->filled('ce_entrada') && $request->filled('ce_salida')) {
            $diferencia_ce = round($request->ce_salida - $request->ce_entrada, 2);
        }

        $diferencia_ph = null;
        if ($request->filled('ph_entrada') && $request->filled('ph_salida')) {
            $diferencia_ph = round($request->ph_salida - $request->ph_entrada, 2);
        }

        $porcentaje_caida_nocturna = null;
        if ($request->filled('peso_tarde_anterior') && $request->filled('peso_manana') && $request->peso_tarde_anterior > 0) {
            $porcentaje_caida_nocturna = round((($request->peso_tarde_anterior - $request->peso_manana) / $request->peso_tarde_anterior) * 100, 1);
        }

        // 4. Guardar los datos del monitoreo
        $datosAGuardar = array_merge($request->all(), [
            'dpv' => $dpv,
            'porcentaje_drenaje' => $porcentaje_drenaje,
            'diferencia_ce' => $diferencia_ce,
            'diferencia_ph' => $diferencia_ph,
            'porcentaje_caida_nocturna' => $porcentaje_caida_nocturna,
            'estatus_general' => $estatus_general,
            'vol_riego_entrada' => $volRiego,
        ]);

        MonitoreoClimaRiego::create($datosAGuardar);

        // --- 5. EVALUAR ALERTA DE DRENAJE (Menor al 10% o Mayor al 35%) ---
        if (!is_null($porcentaje_drenaje) && ($porcentaje_drenaje < 10 || $porcentaje_drenaje > 35)) {
            $this->enviarAlertaAdministradores($request->sector, $porcentaje_drenaje);
        }

        return redirect()->route('monitoreo.index')->with('status', '¡Registro guardado con éxito!');
    }

    public function show($id)
{
    // 1. Buscar el registro técnico o lanzar 404 si no existe (con su operador precargado)
    $monitoreo = MonitoreoClimaRiego::with('user')->findOrFail($id);

    // 2. RESTRICCIÓN DE SEGURIDAD: Si es operador, verificar que el registro pertenezca a sus sectores
    if (auth()->user()->rol !== 'administrador') {
        $sectoresTexto = auth()->user()->sectores;
        $sectoresAsignados = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];

        if (!in_array($monitoreo->sector, $sectoresAsignados)) {
            abort(403, 'No tienes permiso para ver este registro.');
        }
    }

    // 3. IMPLEMENTACIÓN DE CARACTERÍSTICAS: Obtener los datos fijos del sector consultado
    $caracteristicas = \App\Models\SectorCaracteristica::where('sector', $monitoreo->sector)->first();

    // 4. Retornar la vista inyectando ambas variables de forma compacta
    return view('monitoreo.show', compact('monitoreo', 'caracteristicas'));
}

    public function edit($id)
{
    $monitoreo = MonitoreoClimaRiego::findOrFail($id);

    // Verificación de seguridad para operadores
    if (auth()->user()->rol !== 'administrador') {
        $sectoresTexto = auth()->user()->sectores;
        $sectoresAsignados = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];

        // Si el sector del registro no le pertenece al operador, bloqueamos el acceso
        if (!in_array($monitoreo->sector, $sectoresAsignados)) {
            abort(403, 'No tienes permiso para editar este registro.');
        }
        
        // Si es operador, solo le mostramos sus propios sectores asignados en el select
        $sectores = $sectoresAsignados;
    } else {
        // Si es administrador, obtiene todos los sectores como antes
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
    }

    $sectoresAsignados = $sectores;

    // Retornamos enviando ambas variables por seguridad
    return view('monitoreo.edit', compact('monitoreo', 'sectores', 'sectoresAsignados'));
}

   public function update(Request $request, $id)
    {
        $monitoreo = MonitoreoClimaRiego::findOrFail($id);

        // Verificación de seguridad: Si es operador, validar que el registro pertenezca a sus sectores
        if (auth()->user()->rol !== 'administrador') {
            $sectoresTexto = auth()->user()->sectores;
            $sectoresAsignados = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];

            if (!in_array($monitoreo->sector, $sectoresAsignados)) {
                abort(403, 'No tienes permiso para actualizar este registro.');
            }
        }

        // Validación adaptada para permitir decimales y nulos de forma segura
        $request->validate([
            'fecha' => 'required|date',
            'sector' => 'required|string|max:255',
            'temperatura' => 'nullable|numeric',
            'humedad' => 'nullable|numeric',
            'vol_riego_entrada' => 'nullable|numeric',
            'vol_drenaje_salida' => 'nullable|numeric',
            'ce_entrada' => 'nullable|numeric',
            'ce_salida' => 'nullable|numeric',
            'ph_entrada' => 'nullable|numeric',
            'ph_salida' => 'nullable|numeric',
            'peso_tarde_anterior' => 'nullable|numeric',
            'peso_manana' => 'nullable|numeric',
            'radiacion_lectura' => 'required|integer|min:0',
            'radiacion_semaforo' => 'required|string|max:255',
            'radiacion_accion_tomada' => 'nullable|string',
        ]);

        // Lógica de riego por macetas
        $volRiego = $request->vol_riego_entrada;
        if (!is_null($volRiego)) {
            $caracteristica = \App\Models\SectorCaracteristica::where('sector', $request->sector)->first();
            $macetas = $caracteristica ? $caracteristica->macetas_por_gotero : 1;
            if ($macetas > 0) {
                $volRiego = (int) round($volRiego / $macetas);
            }
        }

        // --- RE-CÁLCULOS AUTOMATIZADOS CON CONTROL DE NULOS ---
        $dpv = null;
        $estatus_general = 'SIN DATOS CLIMA';

        if ($request->filled('temperatura') && $request->filled('humedad')) {
            $temp = $request->temperatura;
            $hum = $request->humedad;
            $dpv = round((0.61078 * exp((17.27 * $temp) / ($temp + 237.3))) * (1 - $hum / 100), 2);
            $estatus_general = ($dpv >= 0.8 && $dpv <= 1.4) ? 'ÓPTIMO' : 'REVISAR CLIMA';
        }

        $porcentaje_drenaje = null;
        if (!is_null($volRiego) && $request->filled('vol_drenaje_salida') && $volRiego > 0) {
            $porcentaje_drenaje = round(($request->vol_drenaje_salida / $volRiego) * 100, 1);
        }

        $diferencia_ce = null;
        if ($request->filled('ce_entrada') && $request->filled('ce_salida')) {
            $diferencia_ce = round($request->ce_salida - $request->ce_entrada, 2);
        }

        $diferencia_ph = null;
        if ($request->filled('ph_entrada') && $request->filled('ph_salida')) {
            $diferencia_ph = round($request->ph_salida - $request->ph_entrada, 2);
        }

        $porcentaje_caida_nocturna = null;
        if ($request->filled('peso_tarde_anterior') && $request->filled('peso_manana') && $request->peso_tarde_anterior > 0) {
            $porcentaje_caida_nocturna = round((($request->peso_tarde_anterior - $request->peso_manana) / $request->peso_tarde_anterior) * 100, 1);
        }

        $monitoreo->update(array_merge($request->all(), [
            'dpv' => $dpv,
            'porcentaje_drenaje' => $porcentaje_drenaje,
            'diferencia_ce' => $diferencia_ce,
            'diferencia_ph' => $diferencia_ph,
            'porcentaje_caida_nocturna' => $porcentaje_caida_nocturna,
            'estatus_general' => $estatus_general,
            'vol_riego_entrada' => $volRiego,
        ]));

        // --- EVALUAR ALERTA DE DRENAJE TAMBIÉN AL ACTUALIZAR ---
        if (!is_null($porcentaje_drenaje) && ($porcentaje_drenaje < 10 || $porcentaje_drenaje > 35)) {
            $this->enviarAlertaAdministradores($request->sector, $porcentaje_drenaje);
        }

        return redirect()->route('monitoreo.index')->with('status', '¡Registro actualizado con éxito!');
    }

    public function destroy($id)
    {
        if (auth()->user()->rol !== 'administrador') {
            abort(403, 'Acción no autorizada.');
        }

        $monitoreo = MonitoreoClimaRiego::findOrFail($id);
        $monitoreo->delete();

        return redirect()->route('monitoreo.index')->with('status', 'El registro ha sido eliminado.');
    }

public function exportarExcel($id)
{
    if (auth()->user()->rol !== 'administrador') {
        abort(403, 'Acción no autorizada.');
    }

    $monitoreo = MonitoreoClimaRiego::findOrFail($id);
    $caracteristicas = \App\Models\SectorCaracteristica::where('sector', $monitoreo->sector)->first();

    $operador = \App\Models\User::where('sectores', 'LIKE', '%' . $monitoreo->sector . '%')
                                ->where('rol', '!=', 'administrador')
                                ->first();

    $operadorDueno = $operador ? $operador->name : 'Sin operador asignado';

    $nombreArchivo = "Reporte_Sector_" . str_replace(' ', '_', $monitoreo->sector) . "_ID_" . $monitoreo->id . ".xlsx";

    return Excel::download(new ReporteMonitoreoExport($monitoreo, $caracteristicas, $operadorDueno), $nombreArchivo);
}

public function graficas(Request $request)
{
    $query = MonitoreoClimaRiego::orderBy('fecha', 'desc');

    // 1. RESTRICCIÓN O FILTRADO POR SECTOR
    if (auth()->user()->rol !== 'administrador') {
        // El operador solo puede ver sus sectores asignados
        $sectoresTexto = auth()->user()->sectores;
        $sectoresAsignados = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];
        $query->whereIn('sector', $sectoresAsignados);
    } else {
        // El administrador filtra por el sector que elija en el select dinámico
        if ($request->filled('buscar_sector')) {
            $query->where('sector', $request->input('buscar_sector'));
        }
    }

    // 2. FILTRO POR MES
    $mes = $request->input('mes');
    if ($request->filled('mes')) {
        $inicioMes = \Illuminate\Support\Carbon::parse($mes)->startOfMonth();
        $finMes = \Illuminate\Support\Carbon::parse($mes)->endOfMonth();
        $query->whereBetween('fecha', [$inicioMes, $finMes]);
        
        $historicoReciente = $query->get();
    } else {
        // Comportamiento por defecto: Muestra los últimos 15 registros del sector seleccionado
        $historicoReciente = $query->take(15)->get();
    }

    // Invertir la colección para mantener el orden cronológico de izquierda a derecha
    $historico = $historicoReciente->reverse();

    // 3. Mapeo y formateo estricto a tipos primitivos de JavaScript
    $fechas   = $historico->pluck('fecha')->map(fn($f) => \Carbon\Carbon::parse($f)->format('d/m'))->toArray();
    $dpv      = $historico->pluck('dpv')->map(fn($val) => is_numeric($val) ? floatval($val) : 0)->toArray();
    $drenaje  = $historico->pluck('porcentaje_drenaje')->map(fn($val) => is_numeric($val) ? floatval($val) : 0)->toArray();
    $difCe    = $historico->pluck('diferencia_ce')->map(fn($val) => is_numeric($val) ? floatval($val) : 0)->toArray();
    $lux      = $historico->pluck('radiacion_lectura')->map(fn($val) => is_numeric($val) ? floatval($val) : 0)->toArray();

    return view('graficas.index', compact('fechas', 'dpv', 'drenaje', 'difCe', 'lux'));
}

    /**
     * Función privada para disparar la notificación push a los Administradores
     */
    private function enviarAlertaAdministradores($sector, $porcentajeDrenaje)
    {
        // 1. Buscar a los administradores y al usuario ID 19 que tengan token FCM registrado
        $admins = User::where(function($query) {
            $query->where('rol', 'administrador')
                  ->orWhere('id', 19);
        })->whereNotNull('fcm_token')->get();

        $projectId = "unitasrubraalertas";

        foreach ($admins as $admin) {
            try {
                // 2. Ruta física donde Laravel buscará el archivo de credenciales de Firebase
                $jsonPath = storage_path('app/firebase-credentials.json');
                if (!file_exists($jsonPath)) {
                    continue;
                }

                $jsonKey = json_decode(file_get_contents($jsonPath), true);
                
                // 3. Generar token de seguridad (JWT) para autenticarnos con Google
                $now = time();
                $header = json_encode(['alg' => 'RS256', 'typ' => 'JWT']);
                $payload = json_encode([
                    'iss' => $jsonKey['client_email'],
                    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                    'aud' => $jsonKey['token_uri'],
                    'iat' => $now,
                    'exp' => $now + 3600
                ]);

                $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
                $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
                
                $signature = '';
                openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $jsonKey['private_key'], OPENSSL_ALGO_SHA256);
                $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
                
                $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

                // 4. Obtener el Access Token temporal de Google
                $ch = curl_init($jsonKey['token_uri']);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                    'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                    'assertion' => $jwt
                ]));
                $response = curl_exec($ch);
                curl_close($ch);

                $tokenData = json_decode($response, true);
                if (!isset($tokenData['access_token'])) {
                    continue;
                }
                $accessToken = $tokenData['access_token'];

                // 5. Preparar la estructura de la notificación que llegará al celular
                $mensaje = "El sector " . $sector . " registró un drenaje crítico de: " . $porcentajeDrenaje . "%";

                $fcmPayload = [
                    'message' => [
                        'token' => $admin->fcm_token,
                        'notification' => [
                            'title' => '⚠️ Alerta de Drenaje en Hidroponía',
                            'body' => $mensaje
                        ]
                    ]
                ];

                // 6. Enviar la petición HTTP a los servidores de Firebase
                $ch = curl_init('https://fcm.googleapis.com/v1/projects/' . $projectId . '/messages:send');
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fcmPayload));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $accessToken
                ]);
                
                curl_exec($ch);
                curl_close($ch);

            } catch (\Exception $e) {
                continue;
            }
        }
    }
}