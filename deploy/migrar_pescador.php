<?php
/**
 * migrar_pescador.php — PRODUCTOS DE CORTA DURACIÓN + RUBRO DE PESCADERÍAS (2026-09-10).
 * =============================================================================
 * Caso: un PESCADOR que va al mar y vende lo que trae (no sabe qué tendrá). Necesita
 * publicar "hoy tengo bonito" y que el pescado desaparezca cuando ya no lo tiene.
 *
 * 1) COLUMNA `directorio_servicios.disponible_hasta` DATE NULL.
 *    NULL = sin caducidad (un producto normal). Con fecha = el producto se muestra como
 *    "⚡ Fresco de hoy" / "hasta el 12/09" y **deja de aparecer solo** cuando pasa la fecha
 *    (la condición se aplica en las consultas que ve el CLIENTE: ficha, portada, buscador y
 *    "cerca de mí". En el panel del dueño se sigue viendo todo, para poder reactivarlo).
 *    + índice `idx_disponible` para que el filtro no cueste nada.
 *    ⚠️ La comparación se hace con la fecha de LIMA que manda PHP (`date()`), NO con
 *    `CURDATE()`: el MySQL del hosting va en UTC y a las 19:00 de Lima ya sería "mañana"
 *    (el pescado desaparecería a media tarde).
 *
 * 2) DATA: afinidades del rubro **Pescaderías y Productos del Mar** (id 99), que estaba
 *    VACÍO de afinidades -> su ficha saldría sin el carrusel 🤝. Sus clientes naturales son
 *    los restaurantes y cevicherías (le compran por mayor), los mercados y las bodegas.
 *
 * Rollback:
 *   DELETE FROM directorio_afinidades WHERE id > <MAX_ANTES>;
 *   ALTER TABLE directorio_servicios DROP COLUMN disponible_hasta;
 *
 * Uso: https://dechimbote.com/migrar_pescador.php?key=<CLAVE>  (se autodestruye)
 */
require_once __DIR__ . '/config.php';
define('MIG_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== MIG_KEY) { http_response_code(403); exit('denegado'); }
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(120);
$pdo = db();

// ---------- 1) COLUMNA disponible_hasta ----------
$cols = $pdo->query("SHOW COLUMNS FROM directorio_servicios")->fetchAll(PDO::FETCH_ASSOC);
$tiene = [];
foreach ($cols as $c) { $tiene[$c['Field']] = $c['Type']; }
if (isset($tiene['disponible_hasta'])) {
    echo "[SALTADO] `disponible_hasta` ya existe (" . $tiene['disponible_hasta'] . ")\n";
} else {
    $t0 = microtime(true);
    $pdo->exec("ALTER TABLE directorio_servicios ADD COLUMN disponible_hasta DATE NULL DEFAULT NULL AFTER activo");
    printf("[OK] `disponible_hasta` añadido en %.2f s\n", microtime(true) - $t0);
}
$idx = $pdo->query("SHOW INDEX FROM directorio_servicios WHERE Key_name = 'idx_disponible'")->fetchAll();
if ($idx) {
    echo "[SALTADO] el índice idx_disponible ya existe\n";
} else {
    $t0 = microtime(true);
    $pdo->exec("ALTER TABLE directorio_servicios ADD INDEX idx_disponible (disponible_hasta)");
    printf("[OK] índice idx_disponible creado en %.2f s\n", microtime(true) - $t0);
}

// Prueba real con la MISMA condición que usan las consultas del cliente
$hoy = date('Y-m-d');                       // fecha de LIMA
$ayer = date('Y-m-d', strtotime('-1 day'));
$ok = false;
$pdo->beginTransaction();
try {
    $ins = $pdo->prepare("INSERT INTO directorio_servicios (negocio_id, titulo, precio, unidad, activo, disponible_hasta)
                          VALUES (0, ?, 1, 'por kg', 1, ?)");
    $ins->execute(['__PRUEBA VIGENTE__', $hoy]);
    $idHoy = (int)$pdo->lastInsertId();
    $ins->execute(['__PRUEBA CADUCADO__', $ayer]);
    $idAyer = (int)$pdo->lastInsertId();

    $sql = "SELECT COUNT(*) FROM directorio_servicios WHERE id IN ($idHoy, $idAyer)
              AND (disponible_hasta IS NULL OR disponible_hasta >= '$hoy')";
    $vigentes = (int)$pdo->query($sql)->fetchColumn();
    $ok = ($vigentes === 1);
    echo "PRUEBA: de 2 productos (uno de hoy y uno de ayer) salen $vigentes con la condición de vigencia -> "
       . ($ok ? 'OK (el de ayer ya no aparece)' : 'FALLA') . "\n";
    $pdo->rollBack();
    echo "PRUEBA deshecha (ROLLBACK)\n\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "PRUEBA ERROR: " . mb_substr($e->getMessage(), 0, 220) . "\n\n";
}

// ---------- 2) AFINIDADES DE PESCADERÍAS ----------
$maxAntes = (int)$pdo->query("SELECT COALESCE(MAX(id),0) FROM directorio_afinidades")->fetchColumn();
echo "afinidades: MAX(id) antes = $maxAntes  (rollback: DELETE FROM directorio_afinidades WHERE id > $maxAntes;)\n";

$rubro = function ($slug) use ($pdo) {
    $st = $pdo->prepare("SELECT id, nombre FROM directorio_categorias WHERE slug = ? LIMIT 1");
    $st->execute([$slug]);
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
};
$pescaderia = $rubro('pescaderias-y-productos-del-mar');
if (!$pescaderia) {
    echo "[ATENCION] no existe el rubro 'pescaderias-y-productos-del-mar'\n";
} else {
    echo "rubro: {$pescaderia['nombre']} (id {$pescaderia['id']})\n";
    // Quién le compra al pescador y quién está al lado: restaurantes y cevicherías (por mayor),
    // mercados/ferias y bodegas (reventa).
    $complementos = ['restaurantes', 'cevicherias', 'mercados-y-ferias', 'bodegas'];
    $ins = $pdo->prepare("INSERT IGNORE INTO directorio_afinidades (categoria_origen_id, categoria_complementaria_id, activo) VALUES (?,?,1)");
    $nuevas = 0; $ya = 0; $sinRubro = [];
    foreach ($complementos as $slug_c) {
        $c = $rubro($slug_c);
        if (!$c) { $sinRubro[] = $slug_c; continue; }
        $marca = [];
        foreach ([[$pescaderia['id'], $c['id']], [$c['id'], $pescaderia['id']]] as $par) {
            $ins->execute($par);
            if ($ins->rowCount() > 0) { $nuevas++; $marca[] = 'nueva'; } else { $ya++; $marca[] = 'ya estaba'; }
        }
        printf("   + %-28s (%s / %s)\n", $c['nombre'], $marca[0], $marca[1]);
    }
    if ($sinRubro) echo "   slugs que NO existen: " . implode(', ', $sinRubro) . "\n";
    echo "afinidades: $nuevas nuevas · $ya ya existían\n";
    echo "complementos del rubro: " . (int)$pdo->query("SELECT COUNT(*) FROM directorio_afinidades WHERE activo=1 AND categoria_origen_id = " . (int)$pescaderia['id'])->fetchColumn() . "\n";
}
echo "afinidades activas en total: " . (int)$pdo->query("SELECT COUNT(*) FROM directorio_afinidades WHERE activo=1")->fetchColumn() . "\n";

echo $ok ? "\n[LISTO] corta duración y rubro de pescaderías listos.\n" : "\n[ATENCION] la prueba de vigencia falló.\n";
unlink(__FILE__);
echo "[BORRADO] el migrador se autodestruyo.\n";
