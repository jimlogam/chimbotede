<?php
/**
 * migrar_entrega.php — "CÓMO ENTREGA" Y "A PEDIDO" (caso tortas, 2026-09-10).
 * =============================================================================
 * Dos campos nuevos en `directorio_negocios`:
 *   - `recojo`       TINYINT(1)   -> "también puedes recoger en mi casa" (el cliente pasa)
 *                      (`delivery` = "yo llevo a domicilio", ya existía)
 *   - `anticipacion` VARCHAR(60)  -> "a pedido, con 2 días de anticipación"
 *
 * ⚠️ La vista `vista_negocio_ficha_completa` lista sus columnas UNA POR UNA, así que NO
 *    expone los campos nuevos: la ficha los lee con una consulta propia (ver negocio.php).
 *
 * Y DATA: el rubro **Panaderías y Pastelerías** (id 88) tenía **0 afinidades** -> su ficha
 * saldría SIN el carrusel 🤝 ("Negocios que complementan"). Se le dan pares EN AMBOS
 * SENTIDOS, y se resuelven los ids POR SLUG (nunca de memoria).
 *
 * Rollback:
 *   DELETE FROM directorio_afinidades WHERE id > <MAX_ID_ANTES>;   (se imprime abajo)
 *   ALTER TABLE directorio_negocios DROP COLUMN recojo, DROP COLUMN anticipacion;
 *
 * Uso: https://dechimbote.com/migrar_entrega.php?key=<CLAVE>  (se autodestruye)
 */
require_once __DIR__ . '/config.php';
define('MIG_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== MIG_KEY) { http_response_code(403); exit('denegado'); }
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(120);
$pdo = db();

// ---------- 1) COLUMNAS NUEVAS ----------
$cols = $pdo->query("SHOW COLUMNS FROM directorio_negocios")->fetchAll(PDO::FETCH_ASSOC);
$tiene = [];
foreach ($cols as $c) { $tiene[$c['Field']] = $c['Type']; }

foreach ([
    'recojo'       => "ADD COLUMN recojo TINYINT(1) NOT NULL DEFAULT 0 AFTER delivery",
    'anticipacion' => "ADD COLUMN anticipacion VARCHAR(60) DEFAULT NULL AFTER recojo",
] as $campo => $clausula) {
    if (isset($tiene[$campo])) {
        echo "[SALTADO] `$campo` ya existe (" . $tiene[$campo] . ")\n";
    } else {
        $t0 = microtime(true);
        $pdo->exec("ALTER TABLE directorio_negocios $clausula");
        printf("[OK] `%s` añadido en %.2f s\n", $campo, microtime(true) - $t0);
    }
}
$cols = $pdo->query("SHOW COLUMNS FROM directorio_negocios")->fetchAll(PDO::FETCH_ASSOC);
echo "columnas de entrega ahora: ";
foreach ($cols as $c) { if (in_array($c['Field'], ['delivery','recojo','anticipacion'])) echo $c['Field'] . '=' . $c['Type'] . '  '; }
echo "\n";

// La vista NO los expone (documentado a propósito)
$enVista = $pdo->query("SHOW COLUMNS FROM vista_negocio_ficha_completa LIKE 'recojo'")->fetchAll();
echo "¿la vista de la ficha expone `recojo`? " . ($enVista ? 'SÍ' : 'NO (la ficha lo lee con consulta propia)') . "\n\n";

// ---------- 2) PRUEBA REAL (transacción + ROLLBACK) ----------
$ok = false;
$pdo->beginTransaction();
try {
    $pdo->prepare("INSERT INTO directorio_negocios
        (nombre, slug, ubicacion_tipo, delivery, recojo, anticipacion, estado, plantilla_id, paleta_id)
        VALUES (?,?, 'fisica', 1, 1, ?, 'pendiente', 1, 1)")
        ->execute(['__PRUEBA ENTREGA__ ' . time(), '__prueba-entrega-' . time(), '2 días']);
    $id = (int)$pdo->lastInsertId();
    $f = $pdo->query("SELECT delivery, recojo, anticipacion FROM directorio_negocios WHERE id = $id")->fetch(PDO::FETCH_ASSOC);
    $ok = ((int)$f['delivery'] === 1 && (int)$f['recojo'] === 1 && $f['anticipacion'] === '2 días');
    echo "PRUEBA: delivery={$f['delivery']} recojo={$f['recojo']} anticipacion={$f['anticipacion']} -> " . ($ok ? 'OK' : 'FALLA') . "\n";
    $pdo->rollBack();
    echo "PRUEBA deshecha (ROLLBACK)\n\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "PRUEBA ERROR: " . $e->getMessage() . "\n\n";
}

// ---------- 3) AFINIDADES DEL RUBRO DE PASTELERÍA ----------
$maxAntes = (int)$pdo->query("SELECT COALESCE(MAX(id),0) FROM directorio_afinidades")->fetchColumn();
echo "afinidades: MAX(id) antes = $maxAntes  (rollback: DELETE FROM directorio_afinidades WHERE id > $maxAntes;)\n";

$origen = $pdo->query("SELECT id, nombre FROM directorio_categorias WHERE slug = 'panaderias-y-pastelerias' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if (!$origen) {
    echo "[ATENCION] no existe el rubro 'panaderias-y-pastelerias': afinidades SALTADAS\n";
} else {
    echo "rubro: {$origen['nombre']} (id {$origen['id']})\n";
    $complementos = ['restaurantes','eventos','eventos-y-decoracion-tematica','bodegas','supermercados',
                     'heladerias-y-juguerias','menus-y-bodegones','dark_kitchens','hoteles','fotografia','musica'];
    $ins = $pdo->prepare("INSERT IGNORE INTO directorio_afinidades (categoria_origen_id, categoria_complementaria_id, activo) VALUES (?,?,1)");
    $pares = 0; $ya = 0; $sinRubro = [];
    foreach ($complementos as $slug) {
        $st = $pdo->prepare("SELECT id, nombre FROM directorio_categorias WHERE slug = ? LIMIT 1");
        $st->execute([$slug]);
        $c = $st->fetch(PDO::FETCH_ASSOC);
        if (!$c) { $sinRubro[] = $slug; continue; }
        foreach ([[$origen['id'], $c['id']], [$c['id'], $origen['id']]] as $par) {   // ambos sentidos
            $ins->execute($par);
            if ($ins->rowCount() > 0) $pares++; else $ya++;
        }
    }
    echo "afinidades insertadas: $pares nuevas · $ya ya existían\n";
    if ($sinRubro) echo "rubros que NO existen (revisar slug): " . implode(', ', $sinRubro) . "\n";
    $n = (int)$pdo->query("SELECT COUNT(*) FROM directorio_afinidades WHERE activo=1 AND categoria_origen_id = " . (int)$origen['id'])->fetchColumn();
    echo "el rubro de pastelería tiene ahora $n complementos\n";
    echo "quién complementa a la pastelería: ";
    foreach ($pdo->query("SELECT c2.nombre FROM directorio_afinidades a JOIN directorio_categorias c2 ON c2.id=a.categoria_complementaria_id
                          WHERE a.categoria_origen_id = " . (int)$origen['id'] . " AND a.activo=1 ORDER BY c2.nombre")->fetchAll(PDO::FETCH_COLUMN) as $nom) {
        echo $nom . ' · ';
    }
    echo "\n";
}
$total = (int)$pdo->query("SELECT COUNT(*) FROM directorio_afinidades WHERE activo=1")->fetchColumn();
echo "afinidades activas en total: $total\n";

echo $ok ? "\n[LISTO] entrega/recojo/anticipación y afinidades de pastelería quedaron listos.\n"
         : "\n[ATENCION] la prueba de los campos nuevos falló: revisar antes de usar el alta.\n";
unlink(__FILE__);
echo "[BORRADO] el migrador se autodestruyo.\n";
