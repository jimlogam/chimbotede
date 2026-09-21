<?php
/**
 * migrar_tour.php — TOURS ESCOLARES: UN SOLO RUBRO DE VIAJES + MÍNIMO DE PERSONAS (2026-09-10).
 * =============================================================================
 * 1) RUBRO DUPLICADO: había DOS rubros de viajes ACTIVOS con las MISMAS palabras clave:
 *      · `agencias_de_viajes`            -> 5 fichas reales + 3 complementos   (SE QUEDA)
 *      · `turismo-y-agencias-de-viaje`   -> 0 fichas y 0 afinidades            (SE DESACTIVA)
 *    Una ficha que se registre en el rubro vacío nace SIN empresas al lado y SIN el carrusel 🤝.
 *    ⚠️ GUARDA: solo se desactiva si de verdad tiene 0 fichas (si alguien la usó, no se toca).
 *
 * 2) COLUMNA `directorio_negocios.minimo_personas` (SMALLINT, 0 = sin mínimo).
 *    "Cuánta gente mínimo para armar el paquete" es criterio de cada negocio (tours escolares,
 *    clases, shows): se muestra en su ficha como "👥 Mínimo 30 personas".
 *    ⚠️ La vista de la ficha lista columnas una por una: NO la expone (la ficha lo lee aparte).
 *
 * Rollback:
 *   UPDATE directorio_categorias SET activo = 1 WHERE slug = 'turismo-y-agencias-de-viaje';
 *   ALTER TABLE directorio_negocios DROP COLUMN minimo_personas;
 *
 * Uso: https://dechimbote.com/migrar_tour.php?key=<CLAVE>  (se autodestruye)
 */
require_once __DIR__ . '/config.php';
define('MIG_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== MIG_KEY) { http_response_code(403); exit('denegado'); }
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(120);
$pdo = db();

// ---------- 1) UN SOLO RUBRO DE VIAJES ----------
echo "=== RUBROS DE VIAJES ANTES ===\n";
foreach ($pdo->query("SELECT c.id, c.nombre, c.slug, c.activo,
        (SELECT COUNT(*) FROM directorio_negocios n WHERE n.categoria_id=c.id) fichas_totales,
        (SELECT COUNT(*) FROM directorio_negocios n WHERE n.categoria_id=c.id AND n.estado='activo') fichas_activas,
        (SELECT COUNT(*) FROM directorio_afinidades a WHERE a.categoria_origen_id=c.id AND a.activo=1) complementos
    FROM directorio_categorias c
    WHERE c.slug IN ('agencias_de_viajes','turismo-y-agencias-de-viaje') ORDER BY c.id")->fetchAll(PDO::FETCH_ASSOC) as $f) {
    echo sprintf("  id %-3s %-34s activo=%s fichas=%s (activas %s) complementos=%s\n",
        $f['id'], $f['nombre'], $f['activo'], $f['fichas_totales'], $f['fichas_activas'], $f['complementos']);
}

$st = $pdo->prepare("SELECT id, nombre, activo FROM directorio_categorias WHERE slug = 'turismo-y-agencias-de-viaje' LIMIT 1");
$st->execute();
$dup = $st->fetch(PDO::FETCH_ASSOC);
if (!$dup) {
    echo "[SALTADO] no existe el rubro duplicado\n";
} elseif ((int)$dup['activo'] === 0) {
    echo "[SALTADO] '{$dup['nombre']}' ya estaba desactivado\n";
} else {
    $n = (int)$pdo->query("SELECT COUNT(*) FROM directorio_negocios WHERE categoria_id = " . (int)$dup['id'])->fetchColumn();
    if ($n > 0) {
        echo "[ATENCION] '{$dup['nombre']}' tiene $n ficha(s): NO se desactiva (revisar a mano)\n";
    } else {
        $pdo->prepare("UPDATE directorio_categorias SET activo = 0 WHERE id = ?")->execute([(int)$dup['id']]);
        echo "[OK] '{$dup['nombre']}' (id {$dup['id']}) DESACTIVADO (estaba vacío)\n";
    }
}

// ¿Las palabras clave siguen llevando al rubro que queda?
echo "\nclaves del buscador que apuntan a 'agencias_de_viajes': ";
foreach ($pdo->query("SELECT k.clave FROM directorio_categoria_claves k
        JOIN directorio_categorias c ON c.id = k.categoria_id
        WHERE c.slug = 'agencias_de_viajes' ORDER BY k.clave")->fetchAll(PDO::FETCH_COLUMN) as $cl) { echo $cl . ' · '; }
echo "\n";

// ¿A quién complementa el rubro que queda? (con quién saldría en el carrusel 🤝)
echo "complementos de 'agencias_de_viajes': ";
foreach ($pdo->query("SELECT c2.nombre FROM directorio_afinidades a
        JOIN directorio_categorias c1 ON c1.id = a.categoria_origen_id
        JOIN directorio_categorias c2 ON c2.id = a.categoria_complementaria_id
        WHERE c1.slug = 'agencias_de_viajes' AND a.activo = 1 ORDER BY c2.nombre")->fetchAll(PDO::FETCH_COLUMN) as $nm) { echo $nm . ' · '; }
echo "\n\n";

// ---------- 2) COLUMNA minimo_personas ----------
$cols = $pdo->query("SHOW COLUMNS FROM directorio_negocios")->fetchAll(PDO::FETCH_ASSOC);
$tiene = [];
foreach ($cols as $c) { $tiene[$c['Field']] = $c['Type']; }
if (isset($tiene['minimo_personas'])) {
    echo "[SALTADO] `minimo_personas` ya existe (" . $tiene['minimo_personas'] . ")\n";
} else {
    $t0 = microtime(true);
    $pdo->exec("ALTER TABLE directorio_negocios ADD COLUMN minimo_personas SMALLINT UNSIGNED NOT NULL DEFAULT 0 AFTER descuento");
    printf("[OK] `minimo_personas` añadido en %.2f s\n", microtime(true) - $t0);
}
$cols = $pdo->query("SHOW COLUMNS FROM directorio_negocios")->fetchAll(PDO::FETCH_ASSOC);
echo "trato con el cliente: ";
foreach ($cols as $c) { if (in_array($c['Field'], ['delivery','recojo','anticipacion','descuento','minimo_personas'])) echo $c['Field'] . '  '; }
echo "\n";
$enVista = $pdo->query("SHOW COLUMNS FROM vista_negocio_ficha_completa LIKE 'minimo_personas'")->fetchAll();
echo "¿la vista de la ficha expone `minimo_personas`? " . ($enVista ? 'SÍ' : 'NO (la ficha lo lee con consulta propia)') . "\n\n";

// Prueba real + ROLLBACK
$ok = false;
$pdo->beginTransaction();
try {
    $pdo->prepare("INSERT INTO directorio_negocios (nombre, slug, ubicacion_tipo, minimo_personas, estado, plantilla_id, paleta_id)
                   VALUES (?,?, 'nacional', 30, 'pendiente', 1, 1)")
        ->execute(['__PRUEBA TOUR__ ' . time(), '__prueba-tour-' . time()]);
    $id = (int)$pdo->lastInsertId();
    $leido = $pdo->query("SELECT minimo_personas FROM directorio_negocios WHERE id = $id")->fetchColumn();
    $ok = ((int)$leido === 30);
    echo "PRUEBA INSERT: minimo_personas leido = {$leido} -> " . ($ok ? 'OK' : 'FALLA') . "\n";
    $pdo->rollBack();
    echo "PRUEBA deshecha (ROLLBACK)\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "PRUEBA ERROR: " . mb_substr($e->getMessage(), 0, 200) . "\n";
}

echo "\n=== RUBROS DE VIAJES DESPUES ===\n";
foreach ($pdo->query("SELECT c.id, c.nombre, c.slug, c.activo,
        (SELECT COUNT(*) FROM directorio_negocios n WHERE n.categoria_id=c.id AND n.estado='activo') fichas_activas
    FROM directorio_categorias c
    WHERE c.slug IN ('agencias_de_viajes','turismo-y-agencias-de-viaje') ORDER BY c.activo DESC, c.id")->fetchAll(PDO::FETCH_ASSOC) as $f) {
    echo sprintf("  id %-3s %-34s activo=%s fichas_activas=%s\n", $f['id'], $f['nombre'], $f['activo'], $f['fichas_activas']);
}

echo $ok ? "\n[LISTO] un solo rubro de viajes y campo de mínimo de personas.\n" : "\n[ATENCION] la prueba del campo falló.\n";
unlink(__FILE__);
echo "[BORRADO] el migrador se autodestruyo.\n";
