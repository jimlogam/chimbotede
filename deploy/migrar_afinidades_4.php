<?php
/**
 * migrar_afinidades_4.php — Últimos pares del mapa de alianzas (2026-09-10).
 * Veterinarias / Mascotas y Veterinarias 24 Horas ⇄ Supermercados (alimento y accesorios):
 * les da un tercer complemento real, porque su afinidad mutua se descarta por "rubro gemelo".
 * INSERT IGNORE en ambos sentidos; se autodestruye al terminar.
 * Uso: https://dechimbote.com/migrar_afinidades_4.php?key=<CLAVE>
 */
require_once __DIR__ . '/config.php';
define('MIG_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== MIG_KEY) { http_response_code(403); exit('denegado'); }
header('Content-Type: text/plain; charset=utf-8');
$pdo = db();

$pares = [[9, 24], [58, 24]];

$max_antes = (int)$pdo->query("SELECT COALESCE(MAX(id),0) FROM directorio_afinidades")->fetchColumn();
echo "Max id antes: $max_antes (rollback: DELETE FROM directorio_afinidades WHERE id > $max_antes)\n\n";
$ins = $pdo->prepare("INSERT IGNORE INTO directorio_afinidades (categoria_origen_id, categoria_complementaria_id) VALUES (?,?)");
$nuevos = 0;
foreach ($pares as $par) {
    foreach ([$par, array_reverse($par)] as $d) {
        $ins->execute([$d[0], $d[1]]);
        if ($ins->rowCount() > 0) $nuevos++;
    }
}
echo "[OK] filas nuevas: $nuevos | total en tabla: " . (int)$pdo->query("SELECT COUNT(*) FROM directorio_afinidades")->fetchColumn() . "\n";
echo "[LISTO]\n";
unlink(__FILE__);
echo "[BORRADO]\n";
