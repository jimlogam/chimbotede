<?php
/**
 * migrar_mayorista.php — 4.º TIPO DE VENDEDOR: 🏭 Mayorista / Proveedor (2026-09-10).
 * =============================================================================
 * `directorio_negocios.ubicacion_tipo` es un ENUM('fisica','ambulante','nacional').
 * Para que el alta pueda guardar 'mayorista' hay que AÑADIRLO AL FINAL del ENUM
 * (MySQL 8 lo hace en modo instantáneo, sin reescribir la tabla y sin tocar datos).
 *
 * Rollback (solo si se abandona el 4.º tipo):
 *   UPDATE directorio_negocios SET ubicacion_tipo='fisica' WHERE ubicacion_tipo='mayorista';
 *   ALTER TABLE directorio_negocios MODIFY COLUMN ubicacion_tipo
 *     ENUM('fisica','ambulante','nacional') NOT NULL DEFAULT 'fisica';
 *
 * Uso: https://dechimbote.com/migrar_mayorista.php?key=<CLAVE>  (se autodestruye)
 */
require_once __DIR__ . '/config.php';
define('MIG_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== MIG_KEY) { http_response_code(403); exit('denegado'); }
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(120);
$pdo = db();

$antes = $pdo->query("SHOW COLUMNS FROM directorio_negocios LIKE 'ubicacion_tipo'")->fetch(PDO::FETCH_ASSOC);
echo "ANTES  : " . ($antes['Type'] ?? '?') . "\n";
echo "conteo por tipo antes: ";
foreach ($pdo->query("SELECT ubicacion_tipo, COUNT(*) c FROM directorio_negocios GROUP BY ubicacion_tipo")->fetchAll(PDO::FETCH_ASSOC) as $f) {
    echo $f['ubicacion_tipo'] . '=' . $f['c'] . '  ';
}
echo "\n\n";

$t0 = microtime(true);
$pdo->exec("ALTER TABLE directorio_negocios MODIFY COLUMN ubicacion_tipo
            ENUM('fisica','ambulante','nacional','mayorista') NOT NULL DEFAULT 'fisica'");
printf("ALTER ejecutado en %.2f s\n", microtime(true) - $t0);

$despues = $pdo->query("SHOW COLUMNS FROM directorio_negocios LIKE 'ubicacion_tipo'")->fetch(PDO::FETCH_ASSOC);
echo "DESPUES: " . ($despues['Type'] ?? '?') . "\n\n";

// Prueba real: insertar un mayorista dentro de una transacción y deshacerla (no deja basura).
$ok = false;
$pdo->beginTransaction();
try {
    $pdo->prepare("INSERT INTO directorio_negocios (nombre, slug, ubicacion_tipo, estado, plantilla_id, paleta_id)
                   VALUES (?,?,?, 'pendiente', 1, 1)")
        ->execute(['__PRUEBA MAYORISTA__ ' . time(), '__prueba-mayorista-' . time(), 'mayorista']);
    $id = (int)$pdo->lastInsertId();
    $leido = $pdo->query("SELECT ubicacion_tipo FROM directorio_negocios WHERE id = " . $id)->fetchColumn();
    $ok = ($leido === 'mayorista');
    echo "PRUEBA INSERT: ubicacion_tipo leido = '{$leido}' -> " . ($ok ? 'ACEPTA mayorista' : 'FALLA') . "\n";
    $pdo->rollBack();
    echo "PRUEBA deshecha (ROLLBACK): la tabla queda igual\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "PRUEBA ERROR: " . $e->getMessage() . "\n";
}

echo "\nconteo por tipo despues: ";
foreach ($pdo->query("SELECT ubicacion_tipo, COUNT(*) c FROM directorio_negocios GROUP BY ubicacion_tipo")->fetchAll(PDO::FETCH_ASSOC) as $f) {
    echo $f['ubicacion_tipo'] . '=' . $f['c'] . '  ';
}
echo "\n";
echo $ok ? "[LISTO] el 4.º tipo (mayorista) ya se puede guardar.\n" : "[ATENCION] la prueba no paso: revisar antes de usar el alta.\n";
unlink(__FILE__);
echo "[BORRADO] el migrador se autodestruyo.\n";
