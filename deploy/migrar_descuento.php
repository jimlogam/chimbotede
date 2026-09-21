<?php
/**
 * migrar_descuento.php — DESCUENTO POR DECHIMBOTE.COM + DATOS DEL DJ (2026-09-10).
 * =============================================================================
 * 1) COLUMNA `directorio_negocios.descuento` (TINYINT, % de 0 a 50).
 *    El negocio DECLARA una vez si da descuento a quien lo contacta por la web:
 *      · se ve en su ficha           -> "🎁 10 % de descuento por DeChimbote.com"
 *      · viaja en el mensaje de WhatsApp del pedido (api/lead.php)
 *    ⚠️ La vista `vista_negocio_ficha_completa` lista sus columnas UNA POR UNA, así que
 *       NO expone el campo nuevo: la ficha lo lee con su consulta propia (igual que
 *       `recojo` y `anticipacion`).
 *
 * 2) DATA del DJ: enlace **Música / Shows ⇄ Decoración / Eventos** en AMBOS SENTIDOS.
 *    Un DJ vive de las fiestas: sus clientes son los organizadores de eventos (8 fichas)
 *    y hasta ahora esa relación NO existía (Música solo se complementaba con Eventos
 *    TEMÁTICOS, Fotografía, Pastelerías, Belleza, Esports y Escape Room).
 *
 * Rollback:
 *   DELETE FROM directorio_afinidades WHERE id > <MAX_ANTES>;
 *   ALTER TABLE directorio_negocios DROP COLUMN descuento;
 *
 * Uso: https://dechimbote.com/migrar_descuento.php?key=<CLAVE>  (se autodestruye)
 */
require_once __DIR__ . '/config.php';
define('MIG_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== MIG_KEY) { http_response_code(403); exit('denegado'); }
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(120);
$pdo = db();

// ---------- 1) COLUMNA del descuento ----------
$cols = $pdo->query("SHOW COLUMNS FROM directorio_negocios")->fetchAll(PDO::FETCH_ASSOC);
$tiene = [];
foreach ($cols as $c) { $tiene[$c['Field']] = $c['Type']; }
if (isset($tiene['descuento'])) {
    echo "[SALTADO] `descuento` ya existe (" . $tiene['descuento'] . ")\n";
} else {
    $t0 = microtime(true);
    $pdo->exec("ALTER TABLE directorio_negocios ADD COLUMN descuento TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER anticipacion");
    printf("[OK] `descuento` añadido en %.2f s\n", microtime(true) - $t0);
}
$cols = $pdo->query("SHOW COLUMNS FROM directorio_negocios")->fetchAll(PDO::FETCH_ASSOC);
echo "columnas de trato con el cliente: ";
foreach ($cols as $c) { if (in_array($c['Field'], ['delivery','recojo','anticipacion','descuento'])) echo $c['Field'] . '=' . $c['Type'] . '  '; }
echo "\n";
$enVista = $pdo->query("SHOW COLUMNS FROM vista_negocio_ficha_completa LIKE 'descuento'")->fetchAll();
echo "¿la vista de la ficha expone `descuento`? " . ($enVista ? 'SÍ' : 'NO (la ficha lo lee con consulta propia)') . "\n\n";

// Prueba real en transacción + ROLLBACK
$ok = false;
$pdo->beginTransaction();
try {
    $pdo->prepare("INSERT INTO directorio_negocios (nombre, slug, ubicacion_tipo, descuento, estado, plantilla_id, paleta_id)
                   VALUES (?,?, 'fisica', 15, 'pendiente', 1, 1)")
        ->execute(['__PRUEBA DESC__ ' . time(), '__prueba-desc-' . time()]);
    $id = (int)$pdo->lastInsertId();
    $leido = $pdo->query("SELECT descuento FROM directorio_negocios WHERE id = $id")->fetchColumn();
    $ok = ((int)$leido === 15);
    echo "PRUEBA INSERT: descuento leido = {$leido} -> " . ($ok ? 'OK' : 'FALLA') . "\n";
    $pdo->rollBack();
    echo "PRUEBA deshecha (ROLLBACK)\n\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "PRUEBA ERROR: " . mb_substr($e->getMessage(), 0, 200) . "\n\n";
}

// ---------- 2) DATA: Música / Shows ⇄ Decoración / Eventos ----------
$maxAntes = (int)$pdo->query("SELECT COALESCE(MAX(id),0) FROM directorio_afinidades")->fetchColumn();
echo "afinidades: MAX(id) antes = $maxAntes  (rollback: DELETE FROM directorio_afinidades WHERE id > $maxAntes;)\n";

$slug = function ($s) use ($pdo) {
    $st = $pdo->prepare("SELECT id, nombre FROM directorio_categorias WHERE slug = ? LIMIT 1");
    $st->execute([$s]);
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
};
$musica = $slug('musica');
$eventos = $slug('eventos');
if (!$musica || !$eventos) {
    echo "[ATENCION] falta el rubro musica o eventos: no se enlazan\n";
} else {
    echo "enlazando {$musica['nombre']} (id {$musica['id']}) ⇄ {$eventos['nombre']} (id {$eventos['id']})\n";
    $ins = $pdo->prepare("INSERT IGNORE INTO directorio_afinidades (categoria_origen_id, categoria_complementaria_id, activo) VALUES (?,?,1)");
    $nuevas = 0; $ya = 0;
    foreach ([[$musica['id'], $eventos['id']], [$eventos['id'], $musica['id']]] as $par) {
        $ins->execute($par);
        if ($ins->rowCount() > 0) $nuevas++; else $ya++;
    }
    echo "afinidades: $nuevas nuevas · $ya ya existían\n";
    foreach (['musica' => $musica, 'eventos' => $eventos] as $etiqueta => $r) {
        $n = (int)$pdo->query("SELECT COUNT(*) FROM directorio_afinidades WHERE activo=1 AND categoria_origen_id = " . (int)$r['id'])->fetchColumn();
        echo "  {$r['nombre']}: $n complementos ahora -> ";
        foreach ($pdo->query("SELECT c2.nombre FROM directorio_afinidades a JOIN directorio_categorias c2 ON c2.id=a.categoria_complementaria_id
                              WHERE a.categoria_origen_id = " . (int)$r['id'] . " AND a.activo=1 ORDER BY c2.nombre")->fetchAll(PDO::FETCH_COLUMN) as $nom) { echo $nom . ' · '; }
        echo "\n";
    }
}
echo "afinidades activas en total: " . (int)$pdo->query("SELECT COUNT(*) FROM directorio_afinidades WHERE activo=1")->fetchColumn() . "\n";

echo $ok ? "\n[LISTO] descuento y enlace del DJ quedaron listos.\n" : "\n[ATENCION] la prueba del descuento falló.\n";
unlink(__FILE__);
echo "[BORRADO] el migrador se autodestruyo.\n";
