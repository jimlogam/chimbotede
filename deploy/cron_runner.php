<?php
/**
 * ============================================================
 *  cron_runner.php — Framework de tareas programadas
 *  Directorio de Negocios de Chimbote (Hostinger, shared hosting)
 *
 *  Propósito: un ÚNICO punto de entrada para TODAS las tareas
 *  programadas. El "reloj" lo pone el Cron Job de hPanel, que
 *  llama a este archivo (por CLI o por URL).
 *
 *  PROTEJIDO por clave secreta: nadie puede disparar una tarea
 *  desde fuera sin conocer CRON_KEY.
 *
 *  USO (hPanel, Cron Job):
 *    A) Por CLI (recomendado):
 *       /usr/bin/php /home/u196269909/domains/dechimbote.com/public_html/cron_runner.php backup "MI_CLAVE"
 *    B) Por URL (si el panel no deja CLI):
 *       https://dechimbote.com/cron_runner.php?tarea=backup&k=MI_CLAVE
 *
 *  Para añadir una tarea nueva:
 *    1) Escribe un archivo en cron/tasks/<nombre>.php que defina
 *       una función  tarea_<nombre>($pdo, $logger)  que devuelva
 *       un string resumen.
 *    2) Regístrala en el array $TAREAS de abajo.
 *    3) Añade un Cron Job en hPanel que la llame.
 * ============================================================
 */

declare(strict_types=1);

/* ── CLAVE SECRETA (CÁMBIALA y no la subas a otro lado) ───── */
const CRON_KEY = 'PON_AQUI_LA_CLAVE';

/* ── Tareas registradas: 'nombre' => archivo dentro de cron/tasks/ ── */
$TAREAS = [
    'backup' => 'backup.php',
    // 'reporte' => 'reporte.php',  // futuro
    // 'pendientes' => 'pendientes.php', // futuro
];

/* ── Resolución de credenciales / entrada ─────────────────── */
$es_cli = (PHP_SAPI === 'cli');
$logger = function (string $msg): void {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
};

function obtener_entrada(bool $es_cli): array {
    if ($es_cli) {
        // argv: [0]=archivo, [1]=tarea, [2]=clave
        return ['tarea' => $GLOBALS['argv'][1] ?? '', 'clave' => $GLOBALS['argv'][2] ?? ''];
    }
    return ['tarea' => $_GET['tarea'] ?? '', 'clave' => $_GET['k'] ?? ''];
}

/* ── 1) Validar clave (segura contra timing) ─────────────── */
$entrada = obtener_entrada($es_cli);
if (!hash_equals(CRON_KEY, $entrada['clave'] ?? '')) {
    http_response_code(403);
    echo 'Acceso denegado.';
    exit(1);
}

/* ── 2) Validar tarea ────────────────────────────────────── */
$tarea = $entrada['tarea'] ?? '';
if ($tarea === '' || !isset($TAREAS[$tarea])) {
    http_response_code(400);
    echo 'Tarea no válida. Disponibles: ' . implode(', ', array_keys($TAREAS)) . "\n";
    exit(1);
}

/* ── 3) Conectar a la BD (usa config.php del proyecto) ───── */
require_once __DIR__ . '/config.php';  // define las credenciales y la función db()
$pdo = db();                           // ⚠️ config.php NO crea $pdo: hay que pedirlo a db().
                                       //    Sin esta línea las tareas morían con HTTP 500
                                       //    (el backup de la BD estuvo caído desde el 2026-09-03).

/* ── 4) Ejecutar la tarea ────────────────────────────────── */
$archivo = __DIR__ . '/cron/tasks/' . $TAREAS[$tarea];
if (!is_file($archivo)) {
    http_response_code(500);
    echo "No existe cron/tasks/{$TAREAS[$tarea]}\n";
    exit(1);
}

header('Content-Type: text/plain; charset=utf-8');
$modo = $es_cli ? 'CLI' : 'WEB';
$logger("▶ Tarea '{$tarea}' iniciada en {$modo}.");

$resultado = require $archivo;  // ejecuta tarea_<nombre>($pdo, $logger)

$logger('✔ Tarea finalizada.');
$logger((string)$resultado);
exit(0);
