<?php
/**
 * api/estadisticas.php — Módulo de ESTADÍSTICAS: latidos del navegador
 * ============================================================
 * El JS (assets/js/estadisticas.js) manda aquí:
 *   - POST t=latido&dur=N   cada ~60 s mientras la pestaña está visible
 *   - POST t=salida&dur=N   al ocultar / cerrar la pestaña
 * Se suma la duración a la sesión de la cookie anónima y se refresca
 * ultimo_activo (para el "cuánta gente hay ahora mismo").
 *
 * GET  action=ahora  -> JSON con la gente en línea AHORA (solo admin).
 *
 * Respuestas siempre ligeras y sin error visible para no romper el JS.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/estadisticas.php';

// ====== En vivo (solo admin autenticado) ======
if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'ahora') {
    if (!stats_tablas_ok()) { stats_json(['ok' => false, 'error' => 'no_migrado']); }
    if (!es_admin()) { stats_json(['ok' => false, 'error' => 'no_admin'], 403); }
    $vivos = stats_en_vivo();
    $por_pagina = [];
    foreach ($vivos as $v) {
        $p = $v['pagina_actual'] !== '' ? $v['pagina_actual'] : '—';
        $por_pagina[$p] = ($por_pagina[$p] ?? 0) + 1;
    }
    arsort($por_pagina);
    $top_paginas = [];
    foreach (array_slice($por_pagina, 0, 10, true) as $p => $n) $top_paginas[] = ['pagina' => $p, 'n' => $n];
    stats_json([
        'ok'         => true,
        'total'      => count($vivos),
        'registrados'=> count(array_filter($vivos, fn($v) => !empty($v['usuario_id']))),
        'top_paginas'=> $top_paginas,
        'actualizado'=> date('H:i:s'),
    ]);
}

// ====== Solo POST para latidos ======
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    stats_json(['ok' => false, 'error' => 'metodo'], 405);
}

$t = $_POST['t'] ?? '';
if (!in_array($t, ['latido', 'salida'], true)) {
    stats_json(['ok' => false, 'error' => 'tipo']);
}
$dur = (int)($_POST['dur'] ?? 0);
$pg  = trim((string)($_POST['pg'] ?? ''));
$ok = stats_latido($t, $dur, $pg);
stats_json(['ok' => $ok, 't' => $t]);
