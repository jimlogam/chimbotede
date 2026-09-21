<?php
/**
 * api/empleos_json.php — Datos para el BUSCADOR FUZZY (Fuse.js) DE EMPLEOS
 * ============================================================
 * Devuelve un JSON PLANO (array de objetos) con los avisos de empleo VIGENTES, pensado
 * para que el navegador busque sin recargar la página ni golpear MySQL.
 * Es el hermano de `api/negocios_json.php`: mismo patrón, otro contenido.
 *
 *   GET /api/empleos_json.php            → JSON (con caché de 1 hora)
 *   GET /api/empleos_json.php?refresh=1  → fuerza reconstruir la caché
 *                                          (como máximo 1 vez por minuto)
 *
 * Salida (claves cortas para que el archivo pese poco en el celular):
 *   i = id · s = slug · t = titulo · e = entidad ("" si es NULL) · o = oficio_slug
 *   z = zona legible (distrito, o `ciudad_txt` si el aviso la trae) · c = categoria_id
 *   y = tipo ('ofrezco' | 'busco' | 'anuncio')
 *
 * ⚠️ Todos los avisos traen SIEMPRE las 8 claves (con "" o 0 cuando el dato no existe):
 *    así Fuse.js nunca tiene que defenderse de una clave que falta.
 * ⚠️ NO se incluyen descripcion, requisitos, sueldo, teléfono ni WhatsApp: el buscador
 *    solo filtra por título, entidad, oficio, zona y tipo; lo demás lo pinta la ficha
 *    (`empleo.php`), que lee el aviso completo de la BD.
 * ⚠️ El campo `entidad` es NULL cuando el aviso no puso empresa: va como "" (nunca null),
 *    porque Fuse.js revienta al normalizar un null.
 *
 * ⛔ REGLA DE FECHAS (trampa del proyecto): el MySQL del hosting va en **UTC** y el sitio
 *    en **America/Lima**. La vigencia se compara con la fecha de Lima que manda PHP
 *    (`date('Y-m-d')`) como PARÁMETRO del prepared statement (ver la línea comentada
 *    "fecha de Lima"). **PROHIBIDO `NOW()` / `CURDATE()`**: a las 19:00 de Lima `CURDATE()`
 *    ya sería "mañana" y los avisos que vencen hoy desaparecerían a media tarde.
 *    Mismo criterio que `sql_producto_vigente()` (includes/helpers.php:650).
 *
 * Caché de archivo: cache/empleos.json (TTL 3600 = 1 hora), igual que el resto del buscador.
 * Quien publica, aprueba o renueva un aviso llama a `fuzzy_olvidar_cache()`
 * (includes/fuzzy_cache.php) y el aviso nuevo aparece al instante, sin esperar la hora.
 * Si el disco no se puede escribir, el endpoint sigue funcionando consultando la BD.
 *
 * 🛡️ A PRUEBA DE LISTA VACÍA: `directorio_empleos` nace por auto-instalación defensiva
 *    (spec §1), así que todavía puede NO existir en el hosting. Si falta la tabla (o
 *    cualquier columna), este archivo responde `[]` con HTTP 200 — **jamás un 500** y
 *    jamás un mensaje de error a la vista: el buscador de empleos no encuentra nada y el
 *    resto del sitio sigue funcionando igual.
 */

require_once __DIR__ . '/../config.php';
// El motor común del buscador define la carpeta de caché y `fuzzy_olvidar_cache()`;
// de ahí se saca la ruta para que este JSON viva junto a negocios.json y productos.json.
require_once __DIR__ . '/../includes/fuzzy_cache.php';

$cache_dir  = function_exists('fuzzy_cache_dir') ? fuzzy_cache_dir() : __DIR__ . '/../cache';
$cache_file = $cache_dir . '/empleos.json';
$lock_file  = $cache_dir . '/empleos.lock';
$ttl        = 3600;   // 1 hora
$ahora      = time();

// ---------------------------------------------------------------- utilidades
/**
 * Manda el JSON al navegador (con gzip si el celular lo acepta) y termina.
 * (Mismos encabezados `X-Fuzzy-*` que negocios_json.php: el JS del buscador los lee igual.)
 */
function empleos_json_responder(string $json, string $origen, int $total): void
{
    header('Content-Type: application/json; charset=utf-8');
    // no-cache = el navegador puede guardarlo pero SIEMPRE revalida con ETag:
    // así un aviso recién publicado aparece al instante y si nada cambió solo
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
function empleos_json_preparar_cache(string $dir): bool
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
 * Consulta la BD y arma el array de avisos VIGENTES.
 * Devuelve [array avisos, string origen] — origen 'bd' o 'error' (error = tabla que
 * todavía no existe / BD caída → array vacío, sin 500 y sin basura en la caché).
 */
function empleos_json_consultar_bd(): array
{
    $hoy = date('Y-m-d');                         // ⬅️ fecha de Lima (config.php fija America/Lima)
    $hoy = preg_replace('/[^0-9\-]/', '', $hoy);  // por si acaso: solo dígitos y guiones

    // Vigencia = activo + sin caducidad o con caducidad de hoy en adelante.
    // El `?` lo llena el prepared statement con la fecha de Lima: NUNCA NOW()/CURDATE().
    $vig = "(e.disponible_hasta IS NULL OR e.disponible_hasta >= ?)";

    // Solo los campos que el buscador usa (+ el nombre del distrito para la zona legible).
    $campos = "e.id, e.slug, e.titulo, e.entidad, e.oficio_slug, e.categoria_id, e.tipo,
               e.ciudad_txt, d.nombre AS distrito";

    $sql_completo = "SELECT $campos
                       FROM directorio_empleos e
                       LEFT JOIN directorio_distritos d ON d.id = e.distrito_id
                      WHERE e.estado = 'activo' AND $vig
                      ORDER BY e.destacado DESC, e.publicado_en DESC";

    try {
        $st = db()->prepare($sql_completo);
        $st->execute([$hoy]);          // ⬅️ la vigencia viaja como PARÁMETRO (fecha de Lima)
        $filas = $st->fetchAll();
    } catch (Throwable $e) {
        // 🛡️ Lo normal mientras el módulo estrena: la tabla todavía no existe. No se
        // avisa de nada al visitante; se registra en el log y se intenta el plan B.
        error_log('empleos_json: sin tabla/datos -> ' . $e->getMessage());
        try {
            // Plan B: sin el JOIN a distritos (por si esa tabla tampoco responde); la
            // zona sale entonces de `ciudad_txt` o queda vacía.
            $sql_min = "SELECT e.id, e.slug, e.titulo, e.entidad, e.oficio_slug, e.categoria_id,
                               e.tipo, e.ciudad_txt, NULL AS distrito
                          FROM directorio_empleos e
                         WHERE e.estado = 'activo' AND $vig
                         ORDER BY e.destacado DESC, e.publicado_en DESC";
            $st = db()->prepare($sql_min);
            $st->execute([$hoy]);      // ⬅️ la vigencia viaja como PARÁMETRO (fecha de Lima)
            $filas = $st->fetchAll();
        } catch (Throwable $e2) {
            error_log('empleos_json: tabla no disponible -> ' . $e2->getMessage());
            return [[], 'error'];      // [] = el buscador no muestra nada (nada de 500)
        }
    }

    $avisos = [];
    foreach ($filas as $f) {
        $id   = (int)($f['id'] ?? 0);
        $slug = trim((string)($f['slug'] ?? ''));
        if ($id <= 0 || $slug === '') {
            continue;   // sin id ni slug no hay ficha que abrir: no sirve en el buscador
        }

        // Zona legible: el texto libre manda (un aviso de Casma no es un distrito del
        // Santa); si el aviso no lo trae, se usa el nombre del distrito.
        $zona = trim((string)($f['ciudad_txt'] ?? ''));
        if ($zona === '') {
            $zona = trim((string)($f['distrito'] ?? ''));
        }

        $avisos[] = [
            'i' => $id,
            's' => $slug,
            't' => trim((string)($f['titulo'] ?? '')),
            'e' => trim((string)($f['entidad'] ?? '')),   // NULL en la BD → "" acá
            'o' => trim((string)($f['oficio_slug'] ?? '')),
            'z' => $zona,
            'c' => (int)($f['categoria_id'] ?? 0),        // 0 = aviso sin categoría
            'y' => (string)($f['tipo'] ?? 'ofrezco'),     // 'ofrezco' | 'busco' | 'anuncio'
        ];
    }
    return [$avisos, 'bd'];
}

// ------------------------------------------------------- ¿servimos la caché?
$forzar = isset($_GET['refresh']) && $_GET['refresh'] === '1';
$cache_ok = empleos_json_preparar_cache($cache_dir);

if (!$forzar && $cache_ok && is_file($cache_file)) {
    $edad = $ahora - (int)@filemtime($cache_file);
    if ($edad >= 0 && $edad < $ttl) {
        $json = (string)@file_get_contents($cache_file);
        if ($json !== '' && $json[0] === '[') {
            empleos_json_responder($json, 'cache', substr_count($json, '"s":'));
        }
    }
}

// El refresh manual no puede repetirse antes de 60 segundos (protege la BD).
if ($forzar && $cache_ok && is_file($lock_file) && ($ahora - (int)@filemtime($lock_file)) < 60) {
    $json = is_file($cache_file) ? (string)@file_get_contents($cache_file) : '';
    if ($json !== '' && $json[0] === '[') {
        empleos_json_responder($json, 'cache-lock', substr_count($json, '"s":'));
    }
}

// ------------------------------------------------------------- consultar BD
try {
    [$avisos, $origen] = empleos_json_consultar_bd();
} catch (Throwable $e) {
    // Red de seguridad: pase lo que pase, el navegador recibe un JSON válido y vacío.
    // (A diferencia de negocios_json.php NO se manda 503: la lista de empleos puede
    //  estar recién instalado y el buscador tiene que seguir vivo, sin errores a la vista.)
    error_log('empleos_json: error de BD -> ' . $e->getMessage());
    $avisos = [];
    $origen = 'error';
}

$json = json_encode($avisos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if (!is_string($json) || $json === '') {
    $json = '[]';   // si json_encode falla con algún texto raro, el buscador recibe []
}

// ------------------------------------------------------------- guardar caché
// ⚠️ Solo se cachea si la BD respondió de verdad: un fallo momentáneo NO debe dejar
//    el [] congelado una hora entera. (La lista vacía de verdad sí se cachea: es correcto.)
if ($cache_ok && $origen === 'bd') {
    $tmp = $cache_file . '.tmp';
    if (@file_put_contents($tmp, $json, LOCK_EX) !== false) {
        @rename($tmp, $cache_file);
        @touch($lock_file);
    }
}

empleos_json_responder($json, $origen, count($avisos));
