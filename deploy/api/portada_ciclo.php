<?php
/**
 * api/portada_ciclo.php — 🔁 Devuelve UN CICLO de la portada (el HTML) para que la portada se repita
 * con carga diferida («Cargando más…»). Ver `includes/portada_ciclos.php`.
 *
 * Uso: /api/portada_ciclo.php?n=2   (n = 2..6; el ciclo 1 lo pinta index.php)
 * Devuelve HTML (text/html), ya con los ids sufijados para que no choquen con los del ciclo 1.
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/portada_ciclos.php';

$n = (int)($_GET['n'] ?? 2);
$n = max(2, min((int)PORTADA_CICLOS_MAX, $n));

header('Content-Type: text/html; charset=utf-8');
header('Cache-Control: no-store');   // cada carga trae contenido nuevo al azar: no se cachea

try {
    echo portada_ciclo_html($n);
} catch (Throwable $e) {
    // Nunca se rompe la portada del visitante: si algo falla, se devuelve vacío y el cargador se apaga.
    header('HTTP/1.1 500 Internal Server Error');
    echo '<!-- ciclo ' . (int)$n . ': ' . e($e->getMessage()) . ' -->';
}
