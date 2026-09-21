<?php
/**
 * helpers_hubspot.php — Integración con HubSpot CRM (DeChimbote.com)
 * ============================================================
 * Sincroniza al dueño de un negocio con HubSpot y lleva su lead score.
 *
 * Reglas de oro de este archivo:
 *   1. NUNCA rompe la web: todo falla en silencio y solo se registra en el log.
 *   2. NUNCA degrada a nadie: sumar un clic toca SOLO el lead_score.
 *      El estado del plan se cambia únicamente con hubspot_actualizar_plan().
 *   3. Es idempotente de verdad: si el contacto existe lo actualiza (PATCH),
 *      si no existe lo crea (POST). Nunca duplica.
 *   4. Si en HubSpot falta una propiedad personalizada, se omite esa propiedad
 *      y el resto se guarda igual (no se pierde el lead por eso).
 *
 * Requiere: config_hubspot.php (token) y config.php (db(), notificar_jefe()).
 * ============================================================
 */

require_once __DIR__ . '/../config_hubspot.php';

// --- Constantes de la API (con guarda, por si ya vienen definidas) ---
if (!defined('HUBSPOT_API_BASE'))   define('HUBSPOT_API_BASE', 'https://api.hubapi.com');
if (!defined('HUBSPOT_TIMEOUT'))    define('HUBSPOT_TIMEOUT', 5);   // segundos máximos
if (!defined('HUBSPOT_LOG_ERRORS')) define('HUBSPOT_LOG_ERRORS', false);
if (!defined('HUBSPOT_LOG_FILE'))   define('HUBSPOT_LOG_FILE', __DIR__ . '/../logs/hubspot_errors.log');

/** Propiedades que el robot espera encontrar en el objeto Contacto. */
function hubspot_props_custom(): array {
    return [HUBSPOT_PROP_SCORE, HUBSPOT_PROP_PLAN, 'distrito', 'rubro'];
}

// ============================================================
// LOG (opcional)
// ============================================================

function hubspot_log(string $funcion, string $detalle): void {
    if (!HUBSPOT_LOG_ERRORS) return;
    try {
        $dir = dirname(HUBSPOT_LOG_FILE);
        if (!is_dir($dir)) @mkdir($dir, 0755, true);
        @file_put_contents(
            HUBSPOT_LOG_FILE,
            date('Y-m-d H:i:s') . " | {$funcion} | {$detalle}\n",
            FILE_APPEND
        );
    } catch (Throwable $e) {
        // el log nunca puede romper nada
    }
}

// ============================================================
// PROPIEDADES QUE HUBSPOT NO CONOCE (memoria de la petición)
// ============================================================

/** ¿Esta propiedad ya se descubrió como inexistente en HubSpot? */
function hubspot_prop_mala(string $prop): bool {
    return !empty($GLOBALS['__hubspot_props_malas'][$prop]);
}

/** Marca una propiedad como inexistente para no volver a enviarla en esta petición. */
function hubspot_prop_marcar_mala(string $prop): void {
    if (hubspot_prop_mala($prop)) return;
    $GLOBALS['__hubspot_props_malas'][$prop] = true;
    hubspot_log('propiedad', "La propiedad '{$prop}' no existe en HubSpot; se omite. Corre hubspot_instalar.php");
}

/** Si el error de HubSpot dice que una propiedad no existe, devuelve su nombre. */
function hubspot_prop_faltante(array $data): ?string {
    $msg = (string)($data['message'] ?? '');
    if (preg_match('/Property\s+"([^"]+)"\s+does not exist/i', $msg, $m)) return $m[1];
    if (preg_match("/Property\s+'([^']+)'\s+does not exist/i", $msg, $m)) return $m[1];
    return null;
}

// ============================================================
// CORTACIRCUITOS
// Si el token no tiene permisos, no tiene sentido llamar a HubSpot en cada
// registro: se apunta el fallo y se deja de intentar 10 minutos. Al arreglar
// los permisos, hubspot_instalar.php borra la marca y todo vuelve al instante.
// ============================================================

if (!defined('HUBSPOT_CIRCUITO_MIN')) define('HUBSPOT_CIRCUITO_MIN', 10);

function hubspot_circuito_archivo(): string {
    return __DIR__ . '/../logs/hubspot_circuito.txt';
}

/** ¿Está el circuito abierto (HubSpot en pausa por falta de permisos)? */
function hubspot_circuito_abierto(): bool {
    try {
        $f = hubspot_circuito_archivo();
        if (!is_file($f)) return false;
        $t = (int)@file_get_contents($f);
        return $t > 0 && (time() - $t) < (HUBSPOT_CIRCUITO_MIN * 60);
    } catch (Throwable $e) {
        return false;
    }
}

/** Abre o cierra el circuito. */
function hubspot_circuito_marcar(bool $abrir): void {
    try {
        $f = hubspot_circuito_archivo();
        if ($abrir) {
            $dir = dirname($f);
            if (!is_dir($dir)) @mkdir($dir, 0755, true);
            @file_put_contents($f, (string)time());
        } elseif (is_file($f)) {
            @unlink($f);
        }
    } catch (Throwable $e) {
        // nunca rompe nada
    }
}

// ============================================================
// LLAMADA CRUDA A LA API
// ============================================================

/**
 * Ejecuta una llamada a la API de HubSpot.
 *
 * @return array ['ok'=>bool, 'code'=>int, 'data'=>array, 'error'=>string]
 */
function hubspot_api(string $metodo, string $ruta, ?array $cuerpo = null): array {
    if (!function_exists('curl_init')) {
        return ['ok' => false, 'code' => 0, 'data' => [], 'error' => 'cURL no está disponible en el servidor'];
    }
    if (!hubspot_configurado()) {
        return ['ok' => false, 'code' => 0, 'data' => [], 'error' => 'HubSpot sin token configurado'];
    }
    if (hubspot_circuito_abierto()) {
        return ['ok' => false, 'code' => 0, 'data' => [], 'error' => 'HubSpot en pausa: faltan permisos en el token'];
    }

    $ch = curl_init(HUBSPOT_API_BASE . $ruta);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $metodo);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, HUBSPOT_TIMEOUT);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $cabeceras = [
        'Authorization: Bearer ' . HUBSPOT_TOKEN,
        'Accept: application/json',
    ];
    if ($cuerpo !== null) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($cuerpo, JSON_UNESCAPED_UNICODE));
        $cabeceras[] = 'Content-Type: application/json';
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $cabeceras);

    $resp = curl_exec($ch);
    $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = (string)curl_error($ch);
    curl_close($ch);

    if ($resp === false) {
        hubspot_log('api', "{$metodo} {$ruta} | conexión: {$err}");
        return ['ok' => false, 'code' => 0, 'data' => [], 'error' => 'Sin conexión con HubSpot: ' . $err];
    }

    $data = json_decode((string)$resp, true);
    if (!is_array($data)) $data = [];

    $ok = ($code >= 200 && $code < 300);
    if (!$ok) {
        hubspot_log('api', "{$metodo} {$ruta} | HTTP {$code} | " . substr((string)$resp, 0, 300));

        // Falta de permisos o token muerto: no insistir en cada visita
        $sin_permisos = ($code === 401)
            || ($code === 403 && (string)($data['category'] ?? '') === 'MISSING_SCOPES');
        if ($sin_permisos) hubspot_circuito_marcar(true);
    } else {
        hubspot_circuito_marcar(false);
    }

    return [
        'ok'    => $ok,
        'code'  => $code,
        'data'  => $data,
        'error' => $ok ? '' : hubspot_mensaje_error($data, $code),
    ];
}

/** Traduce el error de HubSpot a algo que se entienda sin ser programador. */
function hubspot_mensaje_error(array $data, int $code): string {
    $categoria = (string)($data['category'] ?? '');
    $msg       = (string)($data['message'] ?? '');

    if ($code === 401) {
        return 'Token de HubSpot inválido o revocado (401)';
    }
    if ($code === 403 && $categoria === 'MISSING_SCOPES') {
        return 'Al token le faltan permisos en HubSpot (MISSING_SCOPES): activa crm.objects.contacts.read y crm.objects.contacts.write en la app privada';
    }
    if ($code === 409) {
        return 'El contacto ya existía en HubSpot (409)';
    }
    if ($code === 429) {
        return 'HubSpot limitó las peticiones (429): se reintentará en el siguiente clic';
    }
    return $msg !== '' ? "HTTP {$code}: {$msg}" : "HTTP {$code}";
}

// ============================================================
// ESCRITURA A PRUEBA DE PROPIEDADES QUE NO EXISTEN
// ============================================================

/**
 * Envía propiedades a HubSpot quitando las que no existen en la cuenta.
 * Reintenta hasta 3 veces: cada 400 por propiedad desconocida elimina esa
 * propiedad y vuelve a enviar el resto, así nunca se pierde el lead entero.
 *
 * @return array ['ok'=>bool, 'code'=>int, 'data'=>array, 'error'=>string]
 */
function hubspot_escribir(string $metodo, string $ruta, array $properties, int $intentos = 3): array {
    $ultimo = ['ok' => false, 'code' => 0, 'data' => [], 'error' => 'Nada que enviar'];

    for ($i = 0; $i < $intentos; $i++) {
        $props = [];
        foreach ($properties as $k => $v) {
            if (hubspot_prop_mala((string)$k)) continue;
            $props[(string)$k] = $v;
        }
        if (empty($props)) {
            return ['ok' => false, 'code' => 0, 'data' => [], 'error' => 'No quedan propiedades válidas para enviar a HubSpot'];
        }

        $r = hubspot_api($metodo, $ruta, ['properties' => $props]);
        if ($r['ok']) return $r;

        $faltante = hubspot_prop_faltante($r['data']);
        if ($r['code'] === 400 && $faltante !== null && !hubspot_prop_mala($faltante)) {
            hubspot_prop_marcar_mala($faltante);
            $ultimo = $r;
            continue;   // reintenta sin esa propiedad
        }
        return $r;
    }

    return $ultimo;
}

// ============================================================
// LEER UN CONTACTO POR SU EMAIL  (el bug de la versión anterior:
// HubSpot v3 busca por ID interno; para el email hace falta idProperty=email)
// ============================================================

/**
 * Busca un contacto por email.
 *
 * @return array ['ok'=>bool, 'code'=>int, 'data'=>array, 'error'=>string]
 *               code 404 = no existe (resultado válido, no es un fallo)
 */
function hubspot_contacto_por_email(string $email): array {
    $estandar = ['email', 'firstname', 'lastname', 'phone', 'company'];

    $listas = [
        array_merge($estandar, hubspot_props_custom()),
        $estandar,
        ['email'],
    ];

    $ultimo = ['ok' => false, 'code' => 0, 'data' => [], 'error' => 'No se pudo consultar el contacto'];

    foreach ($listas as $lista) {
        $props = array_values(array_filter($lista, static fn($p) => !hubspot_prop_mala((string)$p)));
        if (empty($props)) continue;

        $ruta = '/crm/v3/objects/contacts/' . rawurlencode($email)
              . '?idProperty=email&properties=' . implode(',', $props);

        $r = hubspot_api('GET', $ruta);
        if ($r['ok'] || $r['code'] === 404) return $r;

        $faltante = hubspot_prop_faltante($r['data']);
        if ($r['code'] === 400 && $faltante !== null && !hubspot_prop_mala($faltante)) {
            hubspot_prop_marcar_mala($faltante);
            $ultimo = $r;
            continue;
        }
        return $r;
    }

    return $ultimo;
}

// ============================================================
// 1) SINCRONIZAR AL DUEÑO  (se llama al registrar un negocio)
// ============================================================

/**
 * Crea o actualiza el contacto del dueño en HubSpot.
 * Es idempotente: si ya existe lo actualiza, no lo duplica.
 *
 * @param string      $email          Email del dueño (obligatorio)
 * @param string      $nombre_negocio Nombre del negocio (va a la empresa y al nombre del contacto)
 * @param string      $whatsapp       WhatsApp del dueño
 * @param int|null    $lead_score     null = no tocar el score que ya tenga en HubSpot
 * @param string|null $estado_plan    'Gratis' | 'Premium' | 'Vencido'. null = no tocar el plan
 * @param string      $distrito       Distrito del negocio (si HubSpot lo tiene configurado)
 * @param string      $rubro          Rubro/categoría del negocio (idem)
 * @return array ['ok'=>bool, 'message'=>string, 'contact_id'=>string|null]
 */
function hubspot_sincronizar($email, $nombre_negocio = '', $whatsapp = '', $lead_score = null, $estado_plan = null, $distrito = '', $rubro = '') {
    $email = trim((string)$email);
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Email inválido o vacío', 'contact_id' => null];
    }
    if (!hubspot_configurado()) {
        return ['ok' => false, 'message' => 'HubSpot sin token configurado', 'contact_id' => null];
    }

    // --- Propiedades estándar ---
    $nombre_negocio = trim((string)$nombre_negocio);
    $partes = $nombre_negocio !== '' ? preg_split('/\s+/', $nombre_negocio, 2) : [''];

    $properties = [
        'email'     => $email,
        'firstname' => ($partes[0] ?? '') !== '' ? $partes[0] : $email,
        'lastname'  => (string)($partes[1] ?? ''),
        'phone'     => trim((string)$whatsapp),
        'company'   => $nombre_negocio,
        'website'   => defined('SITE_URL') && SITE_URL !== '' ? SITE_URL : 'https://dechimbote.com',
    ];

    // --- Propiedades personalizadas (solo si vienen con dato) ---
    if ($lead_score !== null && $lead_score !== '') {
        $properties[HUBSPOT_PROP_SCORE] = (string)(int)$lead_score;
    }
    if ($estado_plan !== null && $estado_plan !== '') {
        $properties[HUBSPOT_PROP_PLAN] = (string)$estado_plan;
    }
    if (trim((string)$distrito) !== '') {
        $properties['distrito'] = trim((string)$distrito);
    }
    if (trim((string)$rubro) !== '') {
        $properties['rubro'] = trim((string)$rubro);
    }

    // Fuera los vacíos (HubSpot no necesita que le mandemos cadenas vacías)
    $properties = array_filter($properties, static fn($v) => $v !== '' && $v !== null);

    // --- ¿Ya existe? ---
    $actual = hubspot_contacto_por_email($email);

    if ($actual['ok']) {
        $id = (string)($actual['data']['id'] ?? '');
        if ($id === '') {
            return ['ok' => false, 'message' => 'HubSpot no devolvió el ID del contacto', 'contact_id' => null];
        }
        $r = hubspot_escribir('PATCH', '/crm/v3/objects/contacts/' . rawurlencode($id), $properties);
        if (!$r['ok']) {
            return ['ok' => false, 'message' => 'No se pudo actualizar el contacto: ' . $r['error'], 'contact_id' => $id];
        }
        return ['ok' => true, 'message' => 'Contacto actualizado en HubSpot', 'contact_id' => $id];
    }

    if ($actual['code'] === 404) {
        $r = hubspot_escribir('POST', '/crm/v3/objects/contacts', $properties);
        if ($r['ok']) {
            return ['ok' => true, 'message' => 'Contacto creado en HubSpot', 'contact_id' => (string)($r['data']['id'] ?? '')];
        }
        // Carrera: lo creó otro proceso entre la consulta y el alta -> se actualiza
        if ($r['code'] === 409) {
            $otra = hubspot_contacto_por_email($email);
            if ($otra['ok'] && !empty($otra['data']['id'])) {
                $id = (string)$otra['data']['id'];
                $r2 = hubspot_escribir('PATCH', '/crm/v3/objects/contacts/' . rawurlencode($id), $properties);
                if ($r2['ok']) {
                    return ['ok' => true, 'message' => 'Contacto actualizado en HubSpot (ya existía)', 'contact_id' => $id];
                }
                return ['ok' => false, 'message' => 'No se pudo actualizar el contacto: ' . $r2['error'], 'contact_id' => $id];
            }
        }
        return ['ok' => false, 'message' => 'No se pudo crear el contacto: ' . $r['error'], 'contact_id' => null];
    }

    return ['ok' => false, 'message' => 'No se pudo consultar el contacto: ' . $actual['error'], 'contact_id' => null];
}

// ============================================================
// 2) SUMAR UN CLIC EN "PEDIR PRECIO"  (lead score)
// ============================================================

/**
 * Suma 1 al lead score del dueño en HubSpot.
 * IMPORTANTE: toca ÚNICAMENTE la propiedad del score. Jamás el plan.
 *
 * @return array ['ok'=>bool, 'message'=>string, 'new_score'=>int|null, 'prev_score'=>int|null]
 */
function hubspot_sumar_clic_solicitar($email) {
    $email = trim((string)$email);
    $fallo = static fn(string $m) => ['ok' => false, 'message' => $m, 'new_score' => null, 'prev_score' => null];

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) return $fallo('Email inválido');
    if (!hubspot_configurado())                                     return $fallo('HubSpot sin token configurado');
    if (hubspot_prop_mala(HUBSPOT_PROP_SCORE)) {
        return $fallo('La propiedad ' . HUBSPOT_PROP_SCORE . ' no existe en HubSpot: corre hubspot_instalar.php');
    }

    $actual = hubspot_contacto_por_email($email);

    if ($actual['ok']) {
        $id    = (string)($actual['data']['id'] ?? '');
        $props = (array)($actual['data']['properties'] ?? []);
        $prev  = ($props[HUBSPOT_PROP_SCORE] ?? '') !== '' ? (int)$props[HUBSPOT_PROP_SCORE] : 0;
        $nuevo = $prev + 1;

        if ($id === '') return $fallo('HubSpot no devolvió el ID del contacto');

        $r = hubspot_escribir('PATCH', '/crm/v3/objects/contacts/' . rawurlencode($id), [HUBSPOT_PROP_SCORE => (string)$nuevo]);
        if (!$r['ok']) return $fallo('No se pudo actualizar el score: ' . $r['error']);

        return ['ok' => true, 'message' => "Lead score actualizado a {$nuevo}", 'new_score' => $nuevo, 'prev_score' => $prev];
    }

    if ($actual['code'] === 404) {
        // Dueño registrado antes de la integración: se crea el contacto con score 1
        $r = hubspot_escribir('POST', '/crm/v3/objects/contacts', [
            'email'                 => $email,
            HUBSPOT_PROP_SCORE      => '1',
        ]);
        if ($r['ok']) {
            return ['ok' => true, 'message' => 'Contacto creado con lead score 1', 'new_score' => 1, 'prev_score' => 0];
        }
        return $fallo('No se pudo crear el contacto: ' . $r['error']);
    }

    return $fallo('No se pudo consultar el contacto: ' . $actual['error']);
}

// ============================================================
// 3) CAMBIAR EL ESTADO DEL PLAN (solo Premium / Gratis / Vencido)
// ============================================================

/**
 * Actualiza el estado del plan en HubSpot. Es la ÚNICA función que lo toca.
 *
 * @param string $estado_plan 'Gratis' | 'Premium' | 'Vencido'
 * @return array ['ok'=>bool, 'message'=>string]
 */
function hubspot_actualizar_plan($email, $estado_plan) {
    $email = trim((string)$email);
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'message' => 'Email inválido'];
    }
    if (!hubspot_configurado()) {
        return ['ok' => false, 'message' => 'HubSpot sin token configurado'];
    }
    if (!in_array($estado_plan, ['Gratis', 'Premium', 'Vencido'], true)) {
        return ['ok' => false, 'message' => 'Estado de plan inválido'];
    }

    $actual = hubspot_contacto_por_email($email);
    if (!$actual['ok']) {
        return [
            'ok'      => false,
            'message' => $actual['code'] === 404
                ? 'El contacto no existe todavía en HubSpot'
                : 'No se pudo consultar el contacto: ' . $actual['error'],
        ];
    }

    $id = (string)($actual['data']['id'] ?? '');
    if ($id === '') return ['ok' => false, 'message' => 'HubSpot no devolvió el ID del contacto'];

    $r = hubspot_escribir('PATCH', '/crm/v3/objects/contacts/' . rawurlencode($id), [HUBSPOT_PROP_PLAN => (string)$estado_plan]);
    if (!$r['ok']) {
        return ['ok' => false, 'message' => 'No se pudo actualizar el plan: ' . $r['error']];
    }
    return ['ok' => true, 'message' => "Plan actualizado a {$estado_plan} en HubSpot"];
}

// ============================================================
// 4) DATOS DEL NEGOCIO (para los ganchos)
// ============================================================

/**
 * Datos del negocio + email y plan de su dueño, en una sola consulta.
 * Devuelve [] si el negocio no existe o si algo falla (nunca lanza excepción).
 *
 * OJO: la columna `directorio_usuarios.plan` NO existe en todos los esquemas
 * (la creó en su día una migración que se ejecutó a mano). Si no existe, se consulta
 * sin ella en vez de fallar en silencio y dejar la sincronización muerta.
 */
function hubspot_datos_negocio($negocio_id): array {
    $negocio_id = (int)$negocio_id;
    if ($negocio_id <= 0) return [];

    static $con_plan = null;   // null = todavía no se sabe

    $campos = "n.id, n.nombre, n.whatsapp, n.telefono, u.email AS email_dueno, %s AS plan_usuario,
               c.nombre AS rubro, d.nombre AS distrito";
    $resto  = "FROM directorio_negocios n
               LEFT JOIN directorio_usuarios   u ON u.id = n.dueno_id
               LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
               LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
               WHERE n.id = ? LIMIT 1";

    $sql_plan = "SELECT " . sprintf($campos, 'u.plan') . ' ' . $resto;
    $sql_sin  = "SELECT " . sprintf($campos, 'NULL')   . ' ' . $resto;

    $intentos = $con_plan === false ? [$sql_sin]
              : ($con_plan === true  ? [$sql_plan]
              : [$sql_plan, $sql_sin]);

    foreach ($intentos as $sql) {
        try {
            $st = db()->prepare($sql);
            $st->execute([$negocio_id]);
            $con_plan = ($sql === $sql_plan);
            return $st->fetch() ?: [];
        } catch (Throwable $e) {
            $sin_columna = (stripos($e->getMessage(), 'Unknown column') !== false);
            if ($sql === $sql_plan && $sin_columna) {
                $con_plan = false;   // el esquema no tiene `plan`: se sigue sin ella
                continue;
            }
            hubspot_log('datos_negocio', $e->getMessage());
            return [];
        }
    }

    return [];
}

/** Email del dueño de un negocio ('' si no lo tiene). */
function hubspot_email_dueno_negocio($negocio_id): string {
    $d = hubspot_datos_negocio($negocio_id);
    return trim((string)($d['email_dueno'] ?? ''));
}

/** Convierte el plan interno ('gratis'/'premium') al texto de HubSpot. */
function hubspot_plan_texto($plan_usuario): string {
    $premium = defined('PLAN_PREMIUM') ? PLAN_PREMIUM : 'premium';
    return ((string)$plan_usuario === (string)$premium) ? 'Premium' : 'Gratis';
}

// ============================================================
// 5) GANCHOS (lo que llaman las páginas del sitio)
// ============================================================

/**
 * GANCHO 1 — Negocio recién registrado.
 * Sincroniza al dueño con HubSpot (crea o actualiza). Nunca rompe el registro.
 */
function hubspot_sync_negocio_nuevo($negocio_id): array {
    try {
        $d = hubspot_datos_negocio($negocio_id);
        if (!$d) return ['ok' => false, 'message' => 'Negocio no encontrado', 'contact_id' => null];

        $email = trim((string)($d['email_dueno'] ?? ''));
        if ($email === '') {
            return ['ok' => false, 'message' => 'El negocio no tiene dueño con email', 'contact_id' => null];
        }

        return hubspot_sincronizar(
            $email,
            (string)($d['nombre'] ?? ''),
            (string)($d['whatsapp'] ?? ($d['telefono'] ?? '')),
            0,                                  // lead score inicial
            hubspot_plan_texto($d['plan_usuario'] ?? ''),  // plan real del dueño
            (string)($d['distrito'] ?? ''),
            (string)($d['rubro'] ?? '')
        );
    } catch (Throwable $e) {
        hubspot_log('sync_negocio_nuevo', $e->getMessage());
        return ['ok' => false, 'message' => 'Error interno: ' . $e->getMessage(), 'contact_id' => null];
    }
}

/**
 * GANCHO 2 — Alguien pidió precio (clic en WhatsApp de una ficha).
 * Suma 1 al lead score y, al cruzar 3 por primera vez, avisa al jefe por Telegram.
 */
function hubspot_sync_clic_precio($negocio_id): array {
    try {
        $d = hubspot_datos_negocio($negocio_id);
        if (!$d) return ['ok' => false, 'message' => 'Negocio no encontrado', 'new_score' => null];

        $email = trim((string)($d['email_dueno'] ?? ''));
        if ($email === '') return ['ok' => false, 'message' => 'El negocio no tiene dueño con email', 'new_score' => null];

        if (hubspot_prop_mala(HUBSPOT_PROP_SCORE)) {
            return ['ok' => false, 'message' => 'La propiedad ' . HUBSPOT_PROP_SCORE . ' no existe en HubSpot', 'new_score' => null];
        }

        $r = hubspot_sumar_clic_solicitar($email);

        // Aviso solo la PRIMERA vez que cruza el umbral (no en cada clic posterior)
        if (!empty($r['ok'])
            && (int)($r['new_score'] ?? 0) >= 3
            && (int)($r['prev_score'] ?? 0) < 3) {
            hubspot_verificar_lead_caliente($email, (int)$r['new_score'], (string)($d['nombre'] ?? ''));
        }

        return $r;
    } catch (Throwable $e) {
        hubspot_log('sync_clic_precio', $e->getMessage());
        return ['ok' => false, 'message' => 'Error interno: ' . $e->getMessage(), 'new_score' => null];
    }
}

/**
 * Avisa al jefe por Telegram cuando un dueño se vuelve lead caliente.
 * Falla en silencio si el bot de Telegram no está disponible.
 */
function hubspot_verificar_lead_caliente($email, $nuevo_score, $negocio = '') {
    if ((int)$nuevo_score < 3) return false;
    if (!function_exists('notificar_jefe')) return false;

    $t  = "LEAD CALIENTE EN DECHIMBOTE.COM\n\n";
    $t .= "Negocio: " . ($negocio !== '' ? $negocio : '(sin nombre)') . "\n";
    $t .= "Email: {$email}\n";
    $t .= "Pedidos de precio: {$nuevo_score}\n";
    $t .= "Accion: contactarlo para venderle el plan Premium.";

    return notificar_jefe($t, 'urgente');
}
