<?php
/**
 * migrar_tiendatelefonos.php — 📞 LOS HASTA 4 TELÉFONOS DEL DUEÑO + 🌎 «TODOS LOS DISTRITOS»
 * ==========================================================================================
 * Pedido del jefe (2026-09-16) para el editor del dueño (`mi-tienda.php`):
 *
 * 1) **Tabla NUEVA `directorio_negocio_telefonos`**: el dueño puede tener hasta 4 teléfonos. El
 *    PRINCIPAL sigue viviendo en `directorio_negocios.whatsapp` (así NO se rompe nada: el botón de
 *    WhatsApp de la ficha, `api/lead.php`, el copy de la IA y el chatbot lo leen de ahí) y los
 *    **secundarios** viven aquí, cada uno con su `tipo`:
 *      · `ambos`    → ese número recibe llamadas y WhatsApp
 *      · `llamada`  → SOLO llamadas
 *      · `whatsapp` → SOLO WhatsApp
 *    `orden` = el orden en que los escribió el dueño (1, 2, 3).
 *
 * 2) **Columna `es_todos` en `directorio_negocio_cobertura`**: en «TU ZONA» el dueño puede elegir
 *    **«🌎 Todos los distritos»**. Eso se guarda como `distrito_id = NULL` (toda la provincia, igual
 *    que el interés de Telegram) **y** como cobertura de todos los distritos visibles marcada con
 *    `es_todos = 1`. Así la tienda **sale en la búsqueda de CUALQUIER distrito** (el buscador ya
 *    cruza la cobertura: `buscar.php`), y si el dueño después elige UN distrito, el editor borra
 *    exactamente esas filas (`es_todos = 1`) sin tocar la cobertura real de un servicio a domicilio.
 *
 * Idempotente: se puede volver a correr sin daño.
 *
 * Rollback (si algún día se abandona la idea):
 *   DROP TABLE directorio_negocio_telefonos;
 *   ALTER TABLE directorio_negocio_cobertura DROP COLUMN es_todos;
 *
 * Uso: https://dechimbote.com/migrar_tiendatelefonos.php?key=<CLAVE>  (se autodestruye)
 */
require_once __DIR__ . '/config.php';
define('MIG_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== MIG_KEY) { http_response_code(403); exit('denegado'); }
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(120);
$pdo = db();

echo "=== 1) TABLA directorio_negocio_telefonos ===\n\n";

$existe = (bool)$pdo->query("SHOW TABLES LIKE 'directorio_negocio_telefonos'")->fetchColumn();
if ($existe) {
    echo "[SALTADO] la tabla ya existía\n";
} else {
    $t0 = microtime(true);
    $pdo->exec("CREATE TABLE directorio_negocio_telefonos (
        id         INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        negocio_id INT(10) UNSIGNED NOT NULL,
        numero     VARCHAR(40) NOT NULL,
        tipo       ENUM('ambos','llamada','whatsapp') NOT NULL DEFAULT 'ambos',
        orden      TINYINT(3) UNSIGNED NOT NULL DEFAULT 1,
        creado_en  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        KEY idx_tel_negocio (negocio_id, orden),
        CONSTRAINT fk_tel_negocio FOREIGN KEY (negocio_id) REFERENCES directorio_negocios (id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    printf("CREATE TABLE en %.2f s\n", microtime(true) - $t0);
}
$cols = $pdo->query("SHOW COLUMNS FROM directorio_negocio_telefonos")->fetchAll(PDO::FETCH_ASSOC);
echo "columnas: ";
foreach ($cols as $c) { echo $c['Field'] . '(' . $c['Type'] . ') '; }
echo "\nfilas actuales: " . (int)$pdo->query("SELECT COUNT(*) FROM directorio_negocio_telefonos")->fetchColumn() . "\n\n";

echo "=== 2) COLUMNA es_todos en directorio_negocio_cobertura ===\n\n";
$tiene = false;
foreach ($pdo->query("SHOW COLUMNS FROM directorio_negocio_cobertura")->fetchAll(PDO::FETCH_ASSOC) as $c) {
    if ($c['Field'] === 'es_todos') $tiene = true;
}
if ($tiene) {
    echo "[SALTADO] la columna es_todos ya existía\n";
} else {
    $t0 = microtime(true);
    $pdo->exec("ALTER TABLE directorio_negocio_cobertura
                ADD COLUMN es_todos TINYINT(1) NOT NULL DEFAULT 0 AFTER distrito_id");
    printf("ALTER TABLE en %.2f s\n", microtime(true) - $t0);
}
$cols2 = $pdo->query("SHOW COLUMNS FROM directorio_negocio_cobertura")->fetchAll(PDO::FETCH_ASSOC);
echo "columnas: ";
foreach ($cols2 as $c) { echo $c['Field'] . ' '; }
echo "\nfilas con es_todos=1: " . (int)$pdo->query("SELECT COUNT(*) FROM directorio_negocio_cobertura WHERE es_todos = 1")->fetchColumn() . "\n\n";

echo "=== 3) PRUEBA REAL (transacción, se deshace) ===\n\n";
$okTel = false; $okNum = false; $okTipo = false; $okOrd = false; $okZona = false;
$distritos = array_map('intval', $pdo->query("SELECT id FROM directorio_distritos WHERE visible = 1 ORDER BY id")->fetchAll(PDO::FETCH_COLUMN));
$pdo->beginTransaction();
try {
    $pdo->prepare("INSERT INTO directorio_negocios (nombre, slug, ubicacion_tipo, estado, plantilla_id, paleta_id)
                   VALUES (?,?, 'fisica', 'pendiente', 1, 1)")
        ->execute(['__PRUEBA TELEFONOS__ ' . time(), '__prueba-telefonos-' . time()]);
    $id = (int)$pdo->lastInsertId();

    // Dos secundarios: uno solo para llamadas y otro solo para WhatsApp.
    $ins = $pdo->prepare("INSERT INTO directorio_negocio_telefonos (negocio_id, numero, tipo, orden) VALUES (?,?,?,?)");
    $ins->execute([$id, '943111222', 'llamada', 1]);
    $ins->execute([$id, '943333444', 'whatsapp', 2]);
    $filas = $pdo->query("SELECT numero, tipo, orden FROM directorio_negocio_telefonos WHERE negocio_id = " . $id . " ORDER BY orden")->fetchAll(PDO::FETCH_ASSOC);
    $okTel  = (count($filas) === 2);
    $okNum  = ($okTel && $filas[0]['numero'] === '943111222');
    $okTipo = ($okTel && $filas[0]['tipo'] === 'llamada' && $filas[1]['tipo'] === 'whatsapp');
    $okOrd  = ($okTel && (int)$filas[1]['orden'] === 2);
    echo "PRUEBA teléfonos: " . count($filas) . " filas guardadas -> " . ($okTel && $okNum && $okTipo && $okOrd ? 'OK' : 'FALLA') . "\n";
    foreach ($filas as $f) { echo "   · {$f['numero']} · {$f['tipo']} · orden {$f['orden']}\n"; }

    // Zona «todos los distritos»: distrito_id NULL + cobertura marcada.
    $pdo->prepare("UPDATE directorio_negocios SET distrito_id = NULL WHERE id = ?")->execute([$id]);
    $cins = $pdo->prepare("INSERT INTO directorio_negocio_cobertura (negocio_id, distrito_id, es_todos) VALUES (?,?,1)");
    foreach ($distritos as $d) { $cins->execute([$id, $d]); }
    $cob = (int)$pdo->query("SELECT COUNT(*) FROM directorio_negocio_cobertura WHERE negocio_id = " . $id . " AND es_todos = 1")->fetchColumn();
    $okZona = ($cob === count($distritos));
    echo "PRUEBA zona «todos»: cobertura marcada = {$cob} de " . count($distritos) . " distritos visibles -> " . ($okZona ? 'OK' : 'FALLA') . "\n";

    // Y la prueba de fuego: ¿la búsqueda de UN distrito la encuentra por cobertura?
    if ($distritos) {
        $uno = $distritos[0];
        $st = $pdo->prepare("SELECT COUNT(*) FROM directorio_negocios n
                              WHERE n.id = ? AND (n.distrito_id IN (" . $uno . ")
                                 OR EXISTS (SELECT 1 FROM directorio_negocio_cobertura cc
                                             WHERE cc.negocio_id = n.id AND cc.distrito_id IN (" . $uno . ")))");
        $st->execute([$id]);
        $sale = ((int)$st->fetchColumn() > 0);
        echo "PRUEBA buscador (distrito {$uno}): " . ($sale ? 'LA ENCUENTRA ✔' : 'NO LA ENCUENTRA ✖') . "\n";
        $okZona = $okZona && $sale;
    }

    $pdo->rollBack();
    echo "PRUEBAS deshechas (ROLLBACK): no quedó ningún dato de prueba\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "PRUEBA ERROR: " . $e->getMessage() . "\n";
}

// La FK borra los teléfonos cuando se borra la tienda: se comprueba que la FK existe.
$fk = $pdo->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
                    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'directorio_negocio_telefonos'
                      AND REFERENCED_TABLE_NAME = 'directorio_negocios'")->fetchAll(PDO::FETCH_COLUMN);
echo "\nFK hacia directorio_negocios: " . ($fk ? implode(', ', $fk) : 'NO HAY (revisar)') . "\n";

echo "\nconteo final: teléfonos=" . (int)$pdo->query("SELECT COUNT(*) FROM directorio_negocio_telefonos")->fetchColumn()
   . " · cobertura=" . (int)$pdo->query("SELECT COUNT(*) FROM directorio_negocio_cobertura")->fetchColumn()
   . " · tiendas con distrito NULL=" . (int)$pdo->query("SELECT COUNT(*) FROM directorio_negocios WHERE distrito_id IS NULL")->fetchColumn() . "\n";

echo ($okTel && $okNum && $okTipo && $okOrd && $okZona && $fk)
    ? "\n[LISTO] los 4 teléfonos y «todos los distritos» ya se pueden guardar.\n"
    : "\n[ATENCION] alguna prueba no pasó: revisar antes de usar el editor.\n";

unlink(__FILE__);
echo "[BORRADO] el migrador se autodestruyó.\n";
