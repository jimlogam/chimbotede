<?php
/**
 * api/cancion_worker.php — 🎵 EL OBRERO DE LAS CANCIONES (interno)
 * ==============================================================
 * Qué hace: mueve la cola de las canciones de las tiendas — pide la que falta a Treblo (Sonauto),
 * pregunta por la que se está haciendo, la baja, la deja en **40,000 segundos exactos** y la guarda
 * en el hosting con su ruta en la base de datos.
 *
 * ⚠️ NO lo llama el navegador ni nadie de fuera: lo llama **el propio servidor** (la publicación de
 * una tienda y el «latido» de la ficha) con la clave de `CANCION_WORKER_TOKEN`. Sin esa clave
 * responde 403 y no hace nada.
 *
 * Por qué existe (y por qué no se hace dentro de la publicación): generar una canción tarda entre
 * **30 y 120 segundos**. Si eso pasara dentro de la petición del dueño, el dueño se quedaría mirando
 * la pantalla. Así el dueño recibe su tienda al instante y su canción aparece un minuto después.
 *
 * Y por qué NO depende de un cron: el hosting no tiene uno nuestro. El obrero se dispara al publicar
 * la tienda y, si eso se pierde (se cortó la conexión, el hosting reinició), **el latido del sitio**
 * (`cancion_latido()`, que se llama al abrir cualquier ficha) lo vuelve a arrancar solo.
 *
 * Uso:  /api/cancion_worker.php?t=<CANCION_WORKER_TOKEN>&n=2
 *       (`n` = cuántas canciones intenta cerrar en esta pasada; tope 3)
 *
 * Guía: GUIA_CONSTRUCTOR_DE_TIENDAS.md §16.
 */

@ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/cancion.php';

// 🔌 El obrero trabaja aunque el que lo disparó ya se haya ido: es un «avisale al de atrás» y seguir.
@ignore_user_abort(true);
@set_time_limit((int)CANCION_ESPERA_MAX + 60);

$token = (string)($_GET['t'] ?? '');
if ($token === '' || !hash_equals((string)CANCION_WORKER_TOKEN, $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'sin permiso']);
    exit;
}

$n = max(1, min(3, (int)($_GET['n'] ?? 2)));
$r = cancion_procesar($n, (int)CANCION_ESPERA_MAX);

echo json_encode(['ok' => true, 'hechas' => $r['hechas'], 'notas' => $r['notas'],
                  'segundos' => $r['segundos'] ?? null], JSON_UNESCAPED_UNICODE);
