<?php
/**
 * api/negocios_json.php — Datos para el BUSCADOR FUZZY (Fuse.js)
 * ============================================================
 * Devuelve un JSON PLANO (array de objetos) con los negocios activos, pensado
 * para que el navegador busque sin recargar la página ni golpear MySQL.
 *
 *   GET /api/negocios_json.php            → JSON (con caché de 1 hora)
 *   GET /api/negocios_json.php?refresh=1  → fuerza reconstruir la caché
 *                                           (como máximo 1 vez por minuto)
 *
 * Salida (claves cortas para que el archivo pese poco en el celular):
 *   n = nombre · s = slug · r = rubro (categoría) · i = icono del rubro
 *   d = distrito · p = 1 si el negocio está destacado (⭐ TOP)
 *
 * ⚠️ NO se incluyen descripciones ni campos largos: solo lo que el buscador usa.
 * ⚠️ El proyecto NO tiene columnas `rubro`, `distrito`, `es_premium` ni `activo`
 *    en `directorio_negocios`: el rubro y el distrito son tablas aparte
 *    (`categoria_id`, `distrito_id`) y el estado activo es `estado = 'activo'`.
 *
 * Caché de archivo: cache/negocios.json (1 hora). Si el disco no se puede
 * escribir, el endpoint sigue funcionando consultando la BD directamente.
 */

require_once __DIR__ . '/../config.php';

$cache_dir  = __DIR__ . '/../cache';
$cache_file = $cache_dir . '/negocios.json';
$lock_file  = $cache_dir . '/negocios.lock';
$ttl        = 3600;   // 1 hora
$ahora      = time();

// ---------------------------------------------------------------- utilidades
/**
 * Manda el JSON al navegador (con gzip si el celular lo acepta) y termina.
 */
function fuzzy_responder(string $json, string $origen, int $total): void
{
    header('Content-Type: application/json; charset=utf-8');
    // no-cache = el navegador puede guardarlo pero SIEMPRE revalida con ETag:
    // así un negocio recién creado aparece al instante y si nada cambió solo
    // viajan unas pocas decenas de bytes (304).
    header('Cache-Control: no-cache');
    header('X-Fuzzy-Origen: ' . $origen);
    header('X-Fuzzy-Total: ' . $total);

    $etag = '"' . md5($json) . '"';
    header('ETag: ' . $etag);

    // Si el navegador ya tiene esta misma versión, no le mandamos nada.
    $si_none = trim((string)($_SERVER['HTTP_IF_NONE_MATCH'] ?? ''));
    if ($si_none !== '' && $si_none === $etag) {
        http_response_code(304);
        exit;
    }

    // gzip: el JSON pesa mucho menos y en móvil se nota.
    $acepta_gzip = stripos((string)($_SERVER['HTTP_ACCEPT_ENCODING'] ?? ''), 'gzip') !== false;
    if ($acepta_gzip && function_exists('gzencode')) {
        $comprimido = gzencode($json, 6);
        if ($comprimido !== false) {
            header('Content-Encoding: gzip');
            header('Vary: Accept-Encoding');
            header('Content-Length: ' . strlen($comprimido));
            echo $comprimido;
            exit;
        }
    }

    header('Content-Length: ' . strlen($json));
    echo $json;
    exit;
}

/**
 * Deja la carpeta cache/ lista para escribir (si se puede).
 */
function fuzzy_preparar_cache(string $dir): bool
{
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
    if (!is_dir($dir)) {
        return false;
    }
    // Que nadie liste ni descargue la caché por HTTP (el sitio la lee del disco).
    $ht = $dir . '/.htaccess';
    if (!file_exists($ht)) {
        @file_put_contents($ht, "Options -Indexes\n<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nOrder allow,deny\nDeny from all\n</IfModule>\n");
    }
    return is_writable($dir);
}

/**
 * Consulta la BD y arma el array de negocios activos.
 * Devuelve [array negocios, string origen].
 */
function fuzzy_consultar_bd(): array
{
    // 🏷️ RUBROS MÚLTIPLES (2026-09-12): una tienda puede vivir en hasta 4 rubros.
    // En el JSON va el principal + los extras separados por coma, así el buscador
    // fuzzy la encuentra escribiendo CUALQUIERA de sus rubros.
    $extra_sql = rubros_multi_ok()
        ? "(SELECT GROUP_CONCAT(rc.nombre ORDER BY rr.orden ASC SEPARATOR ', ')
              FROM directorio_negocio_rubros rr
              JOIN directorio_categorias rc ON rc.id = rr.categoria_id
             WHERE rr.negocio_id = n.id)"
        : "NULL";

    $sql_completo = "SELECT n.id, n.nombre, n.slug,
                            n.destacado,
                            c.nombre AS rubro, c.icono AS icono,
                            d.nombre AS distrito,
                            $extra_sql AS rubros_extra
                     FROM directorio_negocios n
                     LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                     LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
                     WHERE n.estado = 'activo'
                     ORDER BY n.nombre ASC";

    try {
        $filas = db()->query($sql_completo)->fetchAll();
    } catch (Throwable $e) {
        // Plan B: si alguna columna no existe en el hosting, devolvemos lo mínimo
        // (así el buscador fuzzy nunca se queda sin datos).
        error_log('negocios_json: fallback -> ' . $e->getMessage());
        $filas = db()->query("SELECT id, nombre, slug FROM directorio_negocios
                              WHERE estado = 'activo' ORDER BY nombre ASC")->fetchAll();
    }

    $negocios = [];
    foreach ($filas as $f) {
        $item = [
            'n' => (string)($f['nombre'] ?? ''),
            's' => (string)($f['slug'] ?? ''),
        ];
        if ($item['n'] === '' || $item['s'] === '') {
            continue;
        }
        $rubro = trim((string)($f['rubro'] ?? ''));
        $extra = trim((string)($f['rubros_extra'] ?? ''));
        if ($rubro !== '' && $extra !== '') $rubro .= ', ' . $extra;   // 🏷️ principal + extras
        elseif ($rubro === '' && $extra !== '') $rubro = $extra;
        if ($rubro !== '')   $item['r'] = $rubro;
        $icono = trim((string)($f['icono'] ?? ''));
        if ($icono !== '')   $item['i'] = $icono;
        $dist  = trim((string)($f['distrito'] ?? ''));
        if ($dist !== '')    $item['d'] = $dist;
        if (!empty($f['destacado'])) $item['p'] = 1;
        $negocios[] = $item;
    }
    return [$negocios, 'bd'];
}

// ------------------------------------------------------- ¿servimos la caché?
$forzar = isset($_GET['refresh']) && $_GET['refresh'] === '1';
$cache_ok = fuzzy_preparar_cache($cache_dir);

if (!$forzar && $cache_ok && is_file($cache_file)) {
    $edad = $ahora - (int)@filemtime($cache_file);
    if ($edad >= 0 && $edad < $ttl) {
        $json = (string)@file_get_contents($cache_file);
        if ($json !== '' && $json[0] === '[') {
            fuzzy_responder($json, 'cache', substr_count($json, '"s":'));
        }
    }
}

// El refresh manual no puede repetirse antes de 60 segundos (protege la BD).
if ($forzar && $cache_ok && is_file($lock_file) && ($ahora - (int)@filemtime($lock_file)) < 60) {
    $json = is_file($cache_file) ? (string)@file_get_contents($cache_file) : '';
    if ($json !== '' && $json[0] === '[') {
        fuzzy_responder($json, 'cache-lock', substr_count($json, '"s":'));
    }
}

// ------------------------------------------------------------- consultar BD
try {
    [$negocios, $origen] = fuzzy_consultar_bd();
} catch (Throwable $e) {
    error_log('negocios_json: error de BD -> ' . $e->getMessage());
    http_response_code(503);
    header('Content-Type: application/json; charset=utf-8');
    echo '[]';
    exit;
}

$json = json_encode($negocios, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

// ------------------------------------------------------------- guardar caché
if ($cache_ok && is_string($json) && $json !== '') {
    $tmp = $cache_file . '.tmp';
    if (@file_put_contents($tmp, $json, LOCK_EX) !== false) {
        @rename($tmp, $cache_file);
        @touch($lock_file);
    }
}

fuzzy_responder((string)$json, $origen, count($negocios));
