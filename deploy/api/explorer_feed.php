<?php
/**
 * explorer_feed.php — 📜 La tanda siguiente del muro (el «Ver más publicaciones» del Explorer)
 * ==========================================================================================
 * Es la API de `explorer.js`: recibe los MISMOS filtros que la dirección de la página (`p`,
 * `filtro`, `cat`, `zona`) y devuelve **el HTML ya pintado** de las siguientes publicaciones.
 *
 * ¿Por qué devuelve HTML y no datos? Porque quien pinta una publicación es `explorer_post_html()`
 * (el mismo que usa la página): así el muro y las tandas siguientes son **idénticos** y no hay dos
 * maneras de dibujar una publicación. El JSON solo lleva `html`, `hay_mas` y `pagina`.
 *
 * GET: /api/explorer_feed.php?p=2&filtro=producto&cat=boticas&zona=chimbote-nuevo
 * Devuelve: {"ok":true,"html":"…","hay_mas":true,"pagina":2}
 *
 * ⚠️ No hace falta sesión ni token: solo LEE contenido ya público del sitio (lo mismo que ve
 * cualquiera en la portada). No escribe nada.
 */

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/explorer.php';

// 🌐 El Explorer es una página PÚBLICA (aclaración del jefe, 2026-09-16): su API también.
// Solo lee contenido ya público del sitio (lo mismo que ve cualquiera en la portada) y no escribe nada.

$pagina = max(1, min(200, (int)($_GET['p'] ?? 1)));
$filtro = (string)($_GET['filtro'] ?? 'todo');
if (!in_array($filtro, ['todo', 'tienda', 'producto'], true)) $filtro = 'todo';
$cat  = preg_replace('/[^a-z0-9_\-]/', '', (string)($_GET['cat'] ?? ''));
$zona = preg_replace('/[^a-z0-9_\-]/', '', (string)($_GET['zona'] ?? ''));

// 🎲 La semilla del muro: la manda la página (window.EXP_SEMILLA) para que el scroll infinito pida
//    las siguientes publicaciones conservando EXACTAMENTE el mismo orden (si cambiara, se repetirían
//    unas y se saltarían otras).
$semilla = (int)($_GET['s'] ?? 0);

$feed = explorer_feed([
    'pagina'  => $pagina,
    'filtro'  => $filtro,
    'cat'     => $cat,
    'zona'    => $zona,
    'semilla' => $semilla,
]);

// 🧩 El muro se pinta con SU MISMO motor (`explorer_posts_html`), que además intercala la publicidad y
//    las historias. El `$desde` es cuántas publicaciones se pintaron antes de esta tanda, para que los
//    tramos (banner tras la 3 y luego cada 5, historias cada 10) sigan contando bien al bajar.
$html = explorer_posts_html($feed['posts'], ($pagina - 1) * (int)EXPLORER_POR_PAGINA);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
echo json_encode([
    'ok'      => !empty($feed['posts']) || $pagina === 1,
    'html'    => $html,
    'hay_mas' => (bool)($feed['hay_mas'] ?? false),
    'pagina'  => $pagina,
    'cuantas' => count($feed['posts']),
    'semilla' => (int)($feed['semilla'] ?? $semilla),
], JSON_UNESCAPED_UNICODE);
