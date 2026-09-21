<?php
/**
 * api/noticias_json.php — LAS NOTICIAS DE NUESTRA WEB, EN JSON
 * ===========================================================
 * Para qué existe: para que **el chatbot** (y cualquier otra parte del sitio) lea las noticias
 * **de dechimbote.com** en vez de ir a buscarlas a los medios de afuera. Es la puerta que pidió el
 * jefe (2026-09-13): *«ahora las noticias ya no las va a tomar así nomás de otras páginas de afuera,
 * sino que ahora va a buscar en nuestra página»*.
 *
 *   GET /api/noticias_json.php                 → las últimas noticias (máximo 10)
 *   GET /api/noticias_json.php?n=1             → solo la última (lo que usa el saludo del chat)
 *   GET /api/noticias_json.php?distrito=chimbote
 *   GET /api/noticias_json.php?dias=3          → ventana en días (por defecto 3: hoy, ayer, anteayer)
 *   GET /api/noticias_json.php?refresh=1       → ignora la caché (máximo 1 vez por minuto)
 *
 * Salida:
 *   {
 *     "ok": true, "generado": "2026-09-13 06:12", "ventana_dias": 3, "total": 10,
 *     "distritos": {"chimbote": 7, "nuevo-chimbote": 3, "santa": 0},
 *     "noticias": [{
 *        "id": 12, "slug": "…", "titulo": "…",
 *        "url": "https://dechimbote.com/noticia/…",      ← LO QUE HAY QUE ENLAZAR
 *        "entradilla": "…", "distrito": "Chimbote", "zona": "Cascajal",
 *        "fecha": "2026-09-13", "hora": "06:12:00",
 *        "fecha_corta": "13/09/2026 · 6:12 a. m.",     ← la que se usa en la web
 *        "fecha_texto": "domingo 13 de septiembre de 2026 · 6:12 a. m.",   ← para decir hablando
 *        "fuente": "Diario de Chimbote", "fuente_enlace": "https://…", "palabras": 303
 *     }, …]
 *   }
 *
 * ⛔ REGLAS PARA QUIEN LO CONSUMA (el chatbot):
 *    1. El enlace que se le da al visitante es **`url`**, y es NUESTRA página (`/noticia/<slug>`).
 *       **Nunca** se manda al visitante al medio de afuera: para eso existe este módulo.
 *    2. El **titular es el enlace** (nada de «clic aquí» ni de «leer la noticia»).
 *    3. `fuente` / `fuente_enlace` son para CITAR al pie si hace falta, no para enlazar.
 *    4. El texto completo vive en nuestra ficha: aquí solo va el titular y la entradilla.
 *
 * ⚠️ El cuerpo NO se devuelve a propósito: el contenido se lee en dechimbote.com (es el objetivo del
 *    módulo). Si algún día se necesita, se añade un `&cuerpo=1` con cuidado de no inflar el JSON.
 * 🛡️ A PRUEBA DE TABLA VACÍA: si la tabla todavía no existe (o está vacía), responde `"noticias": []`
 *    con HTTP 200 — **jamás un 500**.
 *
 * Motor: includes/noticias.php · Ajustes: includes/config_noticias.php · Guía: GUIA_NOTICIAS_DIARIAS.md
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/noticias.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Robots-Tag: noindex');            // es una puerta para el bot, no una página
header('Cache-Control: public, max-age=300');

/** Manda el JSON y termina (con gzip si el cliente lo acepta). */
function noti_api_responder(array $datos, $code = 200) {
    $json = json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    http_response_code($code);
    if (function_exists('gzencode') && !empty($_SERVER['HTTP_ACCEPT_ENCODING'])
        && stripos((string)$_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
        header('Content-Encoding: gzip');
        echo gzencode((string)$json, 6);
    } else {
        echo $json;
    }
    exit;
}

$n        = isset($_GET['n']) ? (int)$_GET['n'] : 10;
$n        = max(1, min($n, (int)NOTICIAS_MAX_DIA));
$distrito = trim((string)($_GET['distrito'] ?? ''));
if ($distrito !== '' && !isset(NOTICIAS_DISTRITOS[$distrito])) $distrito = '';
$dias     = isset($_GET['dias']) ? (int)$_GET['dias'] : (int)NOTICIAS_DIAS_VENTANA;
$dias     = max(1, min($dias, 30));
$refresh  = !empty($_GET['refresh']);

// ---------------------------------------------------------------- caché (5 min)
$base  = __DIR__ . '/../cache/noticias';
if (!is_dir($base)) @mkdir($base, 0755, true);
$clave = md5($n . '|' . $distrito . '|' . $dias);
$arch  = $base . '/api_' . $clave . '.json';
$ttl   = 300;
if (!$refresh && is_file($arch) && (time() - (int)@filemtime($arch)) < $ttl) {
    $guardado = json_decode((string)@file_get_contents($arch), true);
    if (is_array($guardado) && isset($guardado['noticias'])) {
        header('X-Noticias-Cache: hit');
        noti_api_responder($guardado);
    }
}

// ---------------------------------------------------------------- las noticias
$filtros = ['campos' => 'corta', 'por_pagina' => $n, 'dias' => $dias];
if ($distrito !== '') $filtros['distrito'] = $distrito;
$filas = noticias_listar($filtros);

$noticias = [];
$cuenta   = [];
foreach ((array)$filas as $f) {
    $clave_d = (string)$f['distrito'];
    $cuenta[$clave_d] = ($cuenta[$clave_d] ?? 0) + 1;
    $noticias[] = [
        'id'            => (int)$f['id'],
        'slug'          => (string)$f['slug'],
        'titulo'        => (string)$f['titulo'],
        'url'           => noticias_url((string)$f['slug']),
        'entradilla'    => (string)($f['entradilla'] ?? ''),
        'distrito'      => noticias_distrito_nombre($clave_d),
        'distrito_clave'=> $clave_d,
        'zona'          => (string)($f['zona'] ?? ''),
        'fecha'         => (string)$f['fecha'],
        'hora'          => (string)$f['hora'],
        // 📅 En la WEB se usa el formato corto (13/09/2026): el largo ocupaba mucho espacio (orden del
        // jefe, 2026-09-13). Para el chat, `fecha_texto` va en palabras porque se dice hablando.
        'fecha_corta'   => noticias_fecha_corta((string)$f['fecha'], (string)$f['hora']),
        'fecha_texto'   => noticias_fecha_larga((string)$f['fecha'], (string)$f['hora']),
        'fuente'        => (string)($f['fuente_nombre'] ?? ''),
        'fuente_enlace' => (string)($f['fuente_enlace'] ?? ''),
        'palabras'      => (int)($f['palabras'] ?? 0),
    ];
}

$datos = [
    'ok'           => true,
    'generado'     => date('Y-m-d H:i'),
    'ventana_dias' => $dias,
    'desde'        => date('Y-m-d', strtotime('-' . max(0, $dias - 1) . ' days')),
    'total'        => count($noticias),
    'distritos'    => $cuenta,
    'seccion'      => url('noticias'),
    'nota'         => 'Enlace SIEMPRE el campo "url" (es nuestra página). Nunca mandes al visitante al medio de afuera.',
    'noticias'     => $noticias,
];

@file_put_contents($arch, json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), LOCK_EX);
header('X-Noticias-Cache: miss');
noti_api_responder($datos);
