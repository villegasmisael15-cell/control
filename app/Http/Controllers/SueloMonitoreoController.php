<?php

namespace App\Http\Controllers;

use App\Models\SueloMonitoreo;
use App\Models\SueloAnalisisRapido;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class SueloMonitoreoController extends Controller
{
    public function index(Request $request)
    {
        // 1. Consulta base ordenando de forma cronológica descendente
        $query = SueloMonitoreo::with('user')->orderBy('fecha', 'desc');

        // 2. Control de accesos por Rol
        if (auth()->user()->rol !== 'administrador') {
            $sectoresTexto = auth()->user()->sectores;
            $sectoresAsignados = $sectoresTexto ? array_map('trim', explode(',', $sectoresTexto)) : [];
            $query->whereIn('sector', $sectoresAsignados);
        } else {
            // Buscador unificado para el Administrator
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
            if ($request->session()->get('ultimo_filtro_suelo') === 'mes') {
                $mes = null;
                $request->merge(['mes' => null]);
            } else {
                $semana = null;
                $request->merge(['semana' => null]);
            }
        }

        if (!empty($semana)) {
            $request->session()->put('ultimo_filtro_suelo', 'semana');
            [$year, $week] = explode('-W', $semana);
            $inicioSemana = Carbon::now()->setISODate($year, $week)->startOfWeek();
            $finSemana = Carbon::now()->setISODate($year, $week)->endOfWeek();
            $query->whereBetween('fecha', [$inicioSemana, $finSemana]);
        }

        if (!empty($mes)) {
            $request->session()->put('ultimo_filtro_suelo', 'mes');
            $inicioMes = Carbon::parse($mes)->startOfMonth();
            $finMes = Carbon::parse($mes)->endOfMonth();
            $query->whereBetween('fecha', [$inicioMes, $finMes]);
        }

        if (empty($semana) && empty($mes)) {
            $request->session()->forget('ultimo_filtro_suelo');
        }

        // Mapeamos los resultados para buscar al dueño real del sector
        $monitoreos = $query->get()->map(function($monitoreo) {
            $dueno = User::where('sectores', 'LIKE', '%' . trim($monitoreo->sector) . '%')->first();
            $monitoreo->dueno_sector = $dueno ? $dueno->name : 'Sin asignar / General';
            return $monitoreo;
        });

        return view('suelo.index', compact('monitoreos'));
    }

    public function create()
    {
        $user = auth()->user();

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

        return view('suelo.create', compact('sectores'));
    }

    public function store(Request $request)
    {
        // 1. Identificamos dinámicamente quién es el verdadero dueño del sector enviado
        $sectorBuscado = trim($request->input('sector'));
        
        $duenoSector = User::where('sectores', 'LIKE', '%' . $sectorBuscado . '%')->first();
        
        // Si por alguna razón extraña no encontramos un dueño asignado, usamos el ID logueado como respaldo de seguridad
        $idDuenoReal = $duenoSector ? $duenoSector->id : auth()->id();

        // 2. Forzamos la hora actual y el ID del DUEÑO REAL del sector en el request antes de validar
        $request->merge([
            'radiacion_hora' => now()->format('H:i:s'),
            'user_id'        => $idDuenoReal
        ]);

        $request->validate([
            'fecha' => 'required|date',
            'sector' => 'required|string|max:255',
            'temperatura' => 'nullable|numeric',
            'humedad' => 'nullable|numeric',
            'lectura_tensiometro' => 'nullable|numeric',
            'tensiometro_estatus' => 'nullable|string|max:100',
            'ce' => 'nullable|numeric',
            'ph' => 'nullable|numeric',
            
            'radiacion_hora' => 'required',
            'radiacion_lectura' => 'required|integer|min:0',
            'radiacion_semaforo' => 'required|string|max:255',
            'radiacion_accion_tomada' => 'nullable|string',
            'user_id' => 'required|exists:users,id',

            // Alertas condicionales de CE
            'alerta_opcion'           => 'nullable|array',
            'alerta_opcion.*'         => 'string|in:EPS,ECP',

            // Estatus e Identificador de Tipo de Laboratorio
            'analisis_rapido_cumplio' => 'required|string|in:si,no',
            'tipo_analisis_lab'       => 'nullable|string|in:fertilidad,pasta_saturada',

            // Validación de los bloques dinámicos de Análisis Rápido (EPS)
            'eps_rapido_no3'          => 'nullable|string|max:50',
            'eps_rapido_k'            => 'nullable|string|max:50',
            'eps_rapido_ca'           => 'nullable|string|max:50',
            'eps_rapido_na'           => 'nullable|string|max:50',
            'eps_rapido_p'            => 'nullable|string|max:50',
            'eps_rapido_ph'           => 'nullable|string|max:50',
            'eps_rapido_ce'           => 'nullable|string|max:50',

            // Validación de los bloques dinámicos de Análisis Rápido (ECP)
            'ecp_rapido_no3'          => 'nullable|string|max:50',
            'ecp_rapido_k'            => 'nullable|string|max:50',
            'ecp_rapido_ca'           => 'nullable|string|max:50',
            'ecp_rapido_na'           => 'nullable|string|max:50',
            'ecp_rapido_p'            => 'nullable|string|max:50',
            'ecp_rapido_ph'           => 'nullable|string|max:50',
            'ecp_rapido_ce'           => 'nullable|string|max:50',

            // Análisis de Laboratorio
            'lab_mo'                  => 'nullable|string|max:50',
            'lab_p_bray'              => 'nullable|string|max:50',
            'lab_k'                   => 'nullable|string|max:50',
            'lab_mg'                  => 'nullable|string|max:50',
            'lab_na'                  => 'nullable|string|max:50',
            'lab_fe'                  => 'nullable|string|max:50',
            'lab_zn'                  => 'nullable|string|max:50',
            'lab_mn'                  => 'nullable|string|max:50',
            'lab_cu'                  => 'nullable|string|max:50',
            'lab_b'                   => 'nullable|string|max:50',
            'lab_s'                   => 'nullable|string|max:50',
            'lab_n_no3'               => 'nullable|string|max:50',
        ]);

        // --- PROCESAMIENTO BIOCLIMÁTICO AUTOMATIZADO ---
        $dpv = null;
        $estatus_general = 'SIN DATOS CLIMA';

        if ($request->filled('temperatura') && $request->filled('humedad')) {
            $temp = $request->temperatura;
            $hum = $request->humedad;
            $dpv = round((0.61078 * exp((17.27 * $temp) / ($temp + 237.3))) * (1 - $hum / 100), 2);
            $estatus_general = ($dpv >= 0.8 && $dpv <= 1.4) ? 'ÓPTIMO' : 'REVISAR CLIMA';
        }

        // --- PROCESAMIENTO DE ALERTAS DE CONDUCTIVIDAD ELÉCTRICA (CE) ---
        $alertaCeOpcion = null;
        if ($request->filled('ce') && (float)$request->ce > 3.0 && $request->has('alerta_opcion')) {
            $alertaCeOpcion = implode(', ', $request->alerta_opcion);
        }

        // --- INSERCIÓN EN TABLA MADRE (`suelo_monitoreos`) ---
        $datosAGuardar = array_merge($request->all(), [
            'dpv'              => $dpv,
            'estatus_general'  => $estatus_general,
            'alerta_ce_opcion' => $alertaCeOpcion,
        ]);

        $monitoreo = SueloMonitoreo::create($datosAGuardar);

        // --- INSERCIÓN EN TABLA HIJA RELACIONAL (`suelo_analisis_rapidos`) ---

        // Fila EPS
        if ($request->filled('eps_rapido_no3') || $request->filled('eps_rapido_ce')) {
            SueloAnalisisRapido::create([
                'suelo_monitoreo_id' => $monitoreo->id,
                'tipo_analisis'      => 'eps',
                'no3'                => $request->eps_rapido_no3,
                'k'                  => $request->eps_rapido_k,
                'ca'                 => $request->eps_rapido_ca,
                'na'                 => $request->eps_rapido_na,
                'p'                  => $request->eps_rapido_p,
                'ph'                 => $request->eps_rapido_ph,
                'ce'                 => $request->eps_rapido_ce,
            ]);
        }

        // Fila ECP
        if ($request->filled('ecp_rapido_no3') || $request->filled('ecp_rapido_ce')) {
            SueloAnalisisRapido::create([
                'suelo_monitoreo_id' => $monitoreo->id,
                'tipo_analisis'      => 'ecp',
                'no3'                => $request->ecp_rapido_no3,
                'k'                  => $request->ecp_rapido_k,
                'ca'                 => $request->ecp_rapido_ca,
                'na'                 => $request->ecp_rapido_na,
                'p'                  => $request->ecp_rapido_p,
                'ph'                 => $request->ecp_rapido_ph,
                'ce'                 => $request->ecp_rapido_ce,
            ]);
        }

        // --- EVALUAR ALERTA DE TENSIÓMETRO (Menor a 5 o Mayor a 25) ---
        if ($request->filled('lectura_tensiometro')) {
            $valTensiometro = $request->lectura_tensiometro;
            if ($valTensiometro < 5 || $valTensiometro > 25) {
                $this->enviarAlertaAdministradores($request->sector, $valTensiometro, 'tensiometro');
            }
        }

        return redirect()->route('suelo.index')->with('status', '¡Monitoreo de Suelo y Análisis guardados con éxito!');
    }

    public function destroy($id)
    {
        // 1. Buscar el monitoreo principal o lanzar error 404 si no existe
        $monitoreo = SueloMonitoreo::findOrFail($id);

        // 2. Eliminar primero los análisis rápidos asociados en la tabla hija
        SueloAnalisisRapido::where('suelo_monitoreo_id', $monitoreo->id)->delete();

        // 3. Eliminar el registro de monitoreo general
        $monitoreo->delete();

        // 4. Redireccionar de vuelta con un mensaje de estado
        return redirect()->route('suelo.index')->with('status', '¡El registro de monitoreo y sus análisis asociados fueron eliminados correctamente!');
    }

    /**
     * Función privada para disparar la notificación push a los Administradores
     */
    private function enviarAlertaAdministradores($sector, $valor, $tipo = 'tensiometro')
    {
        $admins = User::where('rol', 'administrador')
                      ->whereNotNull('fcm_token')
                      ->get();

        $projectId = "unitasrubraalertas";

        if ($tipo === 'tensiometro') {
            $titulo = '⚠️ Alerta de Tensiómetro en Suelo';
            $mensaje = "El sector " . $sector . " registró un nivel de tensiómetro crítico: " . $valor;
        } else {
            $titulo = '⚠️ Alerta en Suelo';
            $mensaje = "El sector " . $sector . " registró un valor crítico de: " . $valor;
        }

        foreach ($admins as $admin) {
            try {
                $jsonPath = storage_path('app/firebase-credentials.json');
                if (!file_exists($jsonPath)) {
                    continue;
                }

                $jsonKey = json_decode(file_get_contents($jsonPath), true);
                
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

                $fcmPayload = [
                    'message' => [
                        'token' => $admin->fcm_token,
                        'notification' => [
                            'title' => $titulo,
                            'body' => $mensaje
                        ],
                        'android' => [
                            'priority' => 'HIGH',
                            'notification' => [
                                'sound' => 'default',
                                'default_sound' => true,
                                'default_vibrate_timings' => true
                            ]
                        ]
                    ]
                ];

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