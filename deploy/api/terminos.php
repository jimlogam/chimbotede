<?php
/**
 * api/terminos.php — Sugerencias de TÍTULO de producto mientras se escribe (2026-09-10)
 * =============================================================================
 * Lo usa `assets/js/terminos_sugerir.js` en el alta de productos (panel del dueño y
 * asistente). El diccionario NO viaja al navegador: se consulta aquí.
 *
 *   GET /api/terminos.php?q=canci              → títulos que empiezan/palabra por "canci"
 *   GET /api/terminos.php?q=canci&limite=6     → tope de sugerencias (por defecto 8, máx 12)
 *   GET /api/terminos.php?campo=unidad&q=por c → unidades reales ya usadas en el sitio
 *
 * Respuesta: { ok, q, campo, total, terminos: [{t, n}] }   (n = en cuántos productos se usa)
 * Solo lectura. Con caché del diccionario en `cache/terminos.json` (1 hora).
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/terminos_sugerir.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: public, max-age=300');

$q      = trim((string)($_GET['q'] ?? ''));
$campo  = ($_GET['campo'] ?? 'titulo') === 'unidad' ? 'unidad' : 'titulo';
$limite = (int)($_GET['limite'] ?? 8);
$limite = max(1, min(12, $limite ?: 8));

if (mb_strlen($q) < 2) {
    json_response(['ok' => true, 'q' => $q, 'campo' => $campo, 'total' => 0, 'terminos' => []]);
}

try {
    if ($campo === 'unidad') {
        // Unidades REALES que ya usa el sitio ("por torta", "por m2", "por servicio"…):
        // así todos escriben igual y el carrito calcula bien los totales.
        $nq = terminos_normalizar($q);
        $filas = db()->query("SELECT unidad AS t, COUNT(*) AS n
                                FROM directorio_servicios
                               WHERE activo = 1 AND unidad IS NOT NULL AND unidad <> ''
                               GROUP BY unidad ORDER BY n DESC LIMIT 400")->fetchAll(PDO::FETCH_ASSOC);
        $sug = [];
        foreach ($filas as $f) {
            $k = terminos_normalizar($f['t']);
            if ($k === '' || $k === $nq) continue;
            if (strncmp($k, $nq, strlen($nq)) === 0 || strpos(' ' . $k, ' ' . $nq) !== false) {
                $sug[] = ['t' => (string)$f['t'], 'n' => (int)$f['n']];
            }
            if (count($sug) >= $limite) break;
        }
        json_response(['ok' => true, 'q' => $q, 'campo' => 'unidad', 'total' => count($sug), 'terminos' => $sug]);
    }

    $sug = terminos_sugerir($q, $limite);
    json_response(['ok' => true, 'q' => $q, 'campo' => 'titulo', 'total' => count($sug), 'terminos' => $sug]);
} catch (Throwable $e) {
    // Nunca romper el formulario por una sugerencia: se responde vacío.
    json_response(['ok' => true, 'q' => $q, 'campo' => $campo, 'total' => 0, 'terminos' => [], 'aviso' => 'sin sugerencias']);
}
