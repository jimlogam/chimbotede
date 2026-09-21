<?php
/**
 * migrar_domicilio.php — 5.º TIPO DE VENDEDOR: 🧰 SERVICIO A DOMICILIO (2026-09-10).
 * =============================================================================
 * Dos cambios de estructura, idempotentes:
 *
 * 1) `directorio_negocios.ubicacion_tipo` pasa a
 *    ENUM('fisica','ambulante','nacional','mayorista','domicilio')   <-- 'domicilio' AL FINAL
 *    (MySQL 8 lo hace en modo instantáneo: sin reescribir la tabla y sin tocar datos).
 *
 * 2) Tabla NUEVA `directorio_negocio_cobertura` (un negocio -> varios distritos):
 *    un servicio a domicilio no tiene UNA ubicación, atiende en VARIAS zonas.
 *    `distrito_id` (la columna de siempre) sigue siendo su base/zona principal.
 *
 * Rollback (solo si se abandona el 5.º tipo):
 *   UPDATE directorio_negocios SET ubicacion_tipo='fisica' WHERE ubicacion_tipo='domicilio';
 *   ALTER TABLE directorio_negocios MODIFY COLUMN ubicacion_tipo
 *     ENUM('fisica','ambulante','nacional','mayorista') NOT NULL DEFAULT 'fisica';
 *   DROP TABLE directorio_negocio_cobertura;
 *
 * Uso: https://dechimbote.com/migrar_domicilio.php?key=<CLAVE>  (se autodestruye)
 */
require_once __DIR__ . '/config.php';
define('MIG_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== MIG_KEY) { http_response_code(403); exit('denegado'); }
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(120);
$pdo = db();

// ---------- 1) ENUM ----------
$antes = $pdo->query("SHOW COLUMNS FROM directorio_negocios LIKE 'ubicacion_tipo'")->fetch(PDO::FETCH_ASSOC);
echo "ANTES  : " . ($antes['Type'] ?? '?') . "\n";
echo "conteo por tipo antes: ";
foreach ($pdo->query("SELECT ubicacion_tipo, COUNT(*) c FROM directorio_negocios GROUP BY ubicacion_tipo")->fetchAll(PDO::FETCH_ASSOC) as $f) {
    echo $f['ubicacion_tipo'] . '=' . $f['c'] . '  ';
}
echo "\n\n";

if (strpos((string)$antes['Type'], "'domicilio'") !== false) {
    echo "[SALTADO] el ENUM ya incluye 'domicilio'\n";
} else {
    $t0 = microtime(true);
    $pdo->exec("ALTER TABLE directorio_negocios MODIFY COLUMN ubicacion_tipo
                ENUM('fisica','ambulante','nacional','mayorista','domicilio') NOT NULL DEFAULT 'fisica'");
    printf("ALTER ejecutado en %.2f s\n", microtime(true) - $t0);
}
$despues = $pdo->query("SHOW COLUMNS FROM directorio_negocios LIKE 'ubicacion_tipo'")->fetch(PDO::FETCH_ASSOC);
echo "DESPUES: " . ($despues['Type'] ?? '?') . "\n\n";

// ---------- 2) TABLA DE COBERTURA ----------
$t0 = microtime(true);
$pdo->exec("CREATE TABLE IF NOT EXISTS directorio_negocio_cobertura (
    negocio_id  INT(10) UNSIGNED NOT NULL,
    distrito_id INT(10) UNSIGNED NOT NULL,
    creado_en   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (negocio_id, distrito_id),
    KEY idx_cobertura_distrito (distrito_id),
    CONSTRAINT fk_cob_negocio  FOREIGN KEY (negocio_id)  REFERENCES directorio_negocios (id)  ON DELETE CASCADE,
    CONSTRAINT fk_cob_distrito FOREIGN KEY (distrito_id) REFERENCES directorio_distritos (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
printf("CREATE TABLE cobertura en %.2f s\n", microtime(true) - $t0);
$cols = $pdo->query("SHOW COLUMNS FROM directorio_negocio_cobertura")->fetchAll(PDO::FETCH_ASSOC);
echo "columnas de directorio_negocio_cobertura: ";
foreach ($cols as $c) { echo $c['Field'] . ' '; }
echo "\nfilas actuales: " . (int)$pdo->query("SELECT COUNT(*) FROM directorio_negocio_cobertura")->fetchColumn() . "\n\n";

// ---------- 3) PRUEBA REAL (dentro de una transacción, se deshace) ----------
$okTipo = false; $okCob = false;
$distrito = (int)$pdo->query("SELECT id FROM directorio_distritos WHERE visible=1 ORDER BY id LIMIT 1")->fetchColumn();
$pdo->beginTransaction();
try {
    $pdo->prepare("INSERT INTO directorio_negocios (nombre, slug, ubicacion_tipo, estado, plantilla_id, paleta_id)
                   VALUES (?,?,?, 'pendiente', 1, 1)")
        ->execute(['__PRUEBA DOMICILIO__ ' . time(), '__prueba-domicilio-' . time(), 'domicilio']);
    $id = (int)$pdo->lastInsertId();
    $leido = $pdo->query("SELECT ubicacion_tipo FROM directorio_negocios WHERE id = " . $id)->fetchColumn();
    $okTipo = ($leido === 'domicilio');
    echo "PRUEBA INSERT negocio: ubicacion_tipo leido = '{$leido}' -> " . ($okTipo ? 'ACEPTA domicilio' : 'FALLA') . "\n";

    if ($distrito) {
        $pdo->prepare("INSERT IGNORE INTO directorio_negocio_cobertura (negocio_id, distrito_id) VALUES (?,?)")
            ->execute([$id, $distrito]);
        $n = (int)$pdo->query("SELECT COUNT(*) FROM directorio_negocio_cobertura WHERE negocio_id = " . $id)->fetchColumn();
        $okCob = ($n === 1);
        echo "PRUEBA cobertura: filas del negocio de prueba = {$n} (distrito {$distrito}) -> " . ($okCob ? 'OK' : 'FALLA') . "\n";
    } else {
        echo "PRUEBA cobertura: no hay distritos visibles para probar\n";
    }
    $pdo->rollBack();
    echo "PRUEBAS deshechas (ROLLBACK): ninguna tabla queda con basura\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "PRUEBA ERROR: " . $e->getMessage() . "\n";
}

echo "\nconteo por tipo despues: ";
foreach ($pdo->query("SELECT ubicacion_tipo, COUNT(*) c FROM directorio_negocios GROUP BY ubicacion_tipo")->fetchAll(PDO::FETCH_ASSOC) as $f) {
    echo $f['ubicacion_tipo'] . '=' . $f['c'] . '  ';
}
echo "\n";
echo ($okTipo && $okCob) ? "[LISTO] el 5.º tipo (domicilio) y la cobertura ya se pueden guardar.\n"
                         : "[ATENCION] alguna prueba no paso: revisar antes de usar el alta.\n";
unlink(__FILE__);
echo "[BORRADO] el migrador se autodestruyo.\n";
