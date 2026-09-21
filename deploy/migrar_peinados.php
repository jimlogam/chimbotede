<?php
/**
 * migrar_peinados.php — CASO PEINADORA DE NIÑAS (2026-09-10). SOLO DATOS.
 * =============================================================================
 * 1) SUBRUBRO "Peinados" en `Salones de belleza` (id 13).
 *    Hoy ese rubro tiene Uñas, Pestañas y Maquillaje… y **Peinados no existía**
 *    (tampoco hay rubro ni subrubro de peinados/cabello/trenzas/niñas/fiestas).
 *
 * 2) AFINIDADES del ECOSISTEMA DE FIESTA para belleza/peluquerías, en AMBOS SENTIDOS.
 *    Antes, "Salones de belleza" solo se complementaba con Piercing, Joyas, Peluquerías
 *    y Ropa: la mamá que está mirando "Decoración / Eventos" o "Panaderías y Pastelerías"
 *    (las tortas) NO veía a la peinadora. Los ids se resuelven POR SLUG, nunca de memoria.
 *
 * Rollback (se imprime el MAX(id) exacto antes de insertar):
 *   DELETE FROM directorio_afinidades WHERE id > <MAX_ANTES>;
 *   DELETE FROM directorio_subcategorias WHERE slug = 'peinados';
 *
 * Uso: https://dechimbote.com/migrar_peinados.php?key=<CLAVE>  (se autodestruye)
 */
require_once __DIR__ . '/config.php';
define('MIG_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== MIG_KEY) { http_response_code(403); exit('denegado'); }
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(120);
$pdo = db();

/** Id de un rubro por slug (nunca de memoria). */
function rubro($pdo, string $slug) {
    $st = $pdo->prepare("SELECT id, nombre FROM directorio_categorias WHERE slug = ? LIMIT 1");
    $st->execute([$slug]);
    return $st->fetch(PDO::FETCH_ASSOC) ?: null;
}

// ---------- 1) SUBRUBRO "Peinados" ----------
$bel = rubro($pdo, 'belleza');
echo "rubro destino: " . ($bel ? $bel['nombre'] . ' (id ' . $bel['id'] . ')' : 'NO EXISTE') . "\n";
if (!$bel) { echo "[ATENCION] sin rubro de belleza no se hace nada\n"; unlink(__FILE__); exit; }

echo "subrubros de ese rubro antes: ";
foreach ($pdo->query("SELECT nombre FROM directorio_subcategorias WHERE categoria_id = " . (int)$bel['id'] . " ORDER BY id")->fetchAll(PDO::FETCH_COLUMN) as $n) { echo $n . ' · '; }
echo "\n";

$existe = (int)$pdo->query("SELECT COUNT(*) FROM directorio_subcategorias WHERE slug = 'peinados'")->fetchColumn();
if ($existe) {
    echo "[SALTADO] el subrubro 'peinados' ya existe\n";
} else {
    try {
        $pdo->prepare("INSERT INTO directorio_subcategorias (categoria_id, nombre, slug, activo) VALUES (?,?,?,1)")
            ->execute([(int)$bel['id'], 'Peinados', 'peinados']);
        echo "[OK] subrubro 'Peinados' creado (id " . $pdo->lastInsertId() . ")\n";
    } catch (Throwable $e) {
        // Si `slug` no existe como columna, se intenta sin slug (según el esquema real).
        try {
            $pdo->prepare("INSERT INTO directorio_subcategorias (categoria_id, nombre, activo) VALUES (?,?,1)")
                ->execute([(int)$bel['id'], 'Peinados']);
            echo "[OK] subrubro 'Peinados' creado SIN slug (id " . $pdo->lastInsertId() . ")\n";
        } catch (Throwable $e2) {
            echo "[ERROR] no se pudo crear el subrubro: " . mb_substr($e2->getMessage(), 0, 200) . "\n";
        }
    }
}
echo "subrubros de ese rubro despues: ";
foreach ($pdo->query("SELECT nombre FROM directorio_subcategorias WHERE categoria_id = " . (int)$bel['id'] . " ORDER BY id")->fetchAll(PDO::FETCH_COLUMN) as $n) { echo $n . ' · '; }
echo "\n\n";

// ---------- 2) AFINIDADES DEL ECOSISTEMA DE FIESTA ----------
$maxAntes = (int)$pdo->query("SELECT COALESCE(MAX(id),0) FROM directorio_afinidades")->fetchColumn();
echo "afinidades: MAX(id) antes = $maxAntes  (rollback: DELETE FROM directorio_afinidades WHERE id > $maxAntes;)\n";

// belleza = peinados y maquillaje para fiestas -> todo el ecosistema
// peluquerías = cortes y peinados -> eventos, fotos y ropa (sin tortas/música: sería ruido)
$plan = [
    'belleza'     => ['eventos', 'eventos_y_decoracion_tematica', 'fotografia', 'musica', 'panaderias-y-pastelerias', 'ropa'],
    'peluquerias' => ['eventos', 'fotografia', 'ropa'],
];

$ins = $pdo->prepare("INSERT IGNORE INTO directorio_afinidades (categoria_origen_id, categoria_complementaria_id, activo) VALUES (?,?,1)");
$nuevas = 0; $ya = 0; $sinRubro = [];
foreach ($plan as $slug_origen => $complementos) {
    $origen = rubro($pdo, $slug_origen);
    if (!$origen) { $sinRubro[] = $slug_origen; continue; }
    echo "\n{$origen['nombre']} (id {$origen['id']}):\n";
    foreach ($complementos as $slug_c) {
        $c = rubro($pdo, $slug_c);
        if (!$c) { $sinRubro[] = $slug_c; continue; }
        $marca = [];
        foreach ([[$origen['id'], $c['id']], [$c['id'], $origen['id']]] as $par) {
            $ins->execute($par);
            if ($ins->rowCount() > 0) { $nuevas++; $marca[] = 'nueva'; } else { $ya++; $marca[] = 'ya estaba'; }
        }
        printf("   + %-34s (%s / %s)\n", $c['nombre'], $marca[0], $marca[1]);
    }
    $n = (int)$pdo->query("SELECT COUNT(*) FROM directorio_afinidades WHERE activo=1 AND categoria_origen_id = " . (int)$origen['id'])->fetchColumn();
    echo "   -> ahora tiene $n complementos\n";
}
echo "\nafinidades insertadas: $nuevas nuevas · $ya ya existían\n";
if ($sinRubro) echo "slugs que NO existen (revisar): " . implode(', ', array_unique($sinRubro)) . "\n";
echo "afinidades activas en total: " . (int)$pdo->query("SELECT COUNT(*) FROM directorio_afinidades WHERE activo=1")->fetchColumn() . "\n";

unlink(__FILE__);
echo "\n[BORRADO] el migrador se autodestruyo.\n";
