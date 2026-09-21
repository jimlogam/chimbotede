<?php
/**
 * __bi_diag.php — TEMPORAL (se borra al terminar): comprueba que el tracking de
 * estadísticas quedó funcionando en producción. Solo lee (SELECT/SHOW).
 * Uso: __bi_diag.php?key=...
 */
header('Content-Type: application/json; charset=utf-8');
if (($_GET['key'] ?? '') !== 'kM8wqR7vTz3nYbX2') { http_response_code(403); echo json_encode(['ok' => false]); exit; }

require_once __DIR__ . '/config.php';

$out = ['ok' => true, 'php' => PHP_VERSION];
try {
    $pdo = db();
    foreach (['directorio_stats_sesiones', 'directorio_stats_eventos'] as $t) {
        $ex = (bool)$pdo->query("SHOW TABLES LIKE '{$t}'")->fetchColumn();
        $out['tablas'][$t] = $ex ? (int)$pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn() : 'NO EXISTE';
    }
    $out['ultimos_eventos'] = $pdo->query("SELECT id, tipo, pagina, tipo_pagina, fecha FROM directorio_stats_eventos ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
    $out['ultimas_sesiones'] = $pdo->query("SELECT id, LEFT(cookie,8) AS cookie8, dispositivo, paginas, segundos, entrada, salida, ref_dominio, ultimo_activo FROM directorio_stats_sesiones ORDER BY id DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $out['ok'] = false;
    $out['error'] = $e->getMessage();
}
echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
