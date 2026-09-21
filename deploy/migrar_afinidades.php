<?php
/**
 * migrar_afinidades.php — Crea la tabla directorio_afinidades y la siembra
 * con pares de alianzas estratégicas (categorías complementarias) usando los
 * ids reales de directorio_categorias. Se autodestruye al terminar.
 */
require_once __DIR__ . '/config.php';
define('MIG_KEY','PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key']??'')!==MIG_KEY){http_response_code(403);exit('denegado');}
header('Content-Type: text/plain; charset=utf-8');
$pdo=db();

$existe = $pdo->query("SHOW TABLES LIKE 'directorio_afinidades'")->fetchColumn();
if (!$existe) {
    $pdo->exec("CREATE TABLE directorio_afinidades (
        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        categoria_origen_id INT UNSIGNED NOT NULL,
        categoria_complementaria_id INT UNSIGNED NOT NULL,
        activo TINYINT(1) NOT NULL DEFAULT 1,
        creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uniq_par (categoria_origen_id, categoria_complementaria_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "[OK] tabla directorio_afinidades creada.\n";
} else {
    echo "[OK] tabla ya existia.\n";
}

// Pares de alianzas estratégicas (bidireccionales) por ID de categoría.
// Cada par se inserta en AMBOS sentidos (a->b y b->a).
$pares = [
    // Mascotas / veterinarias
    [9,50],  [9,4],   // Veterinarias -> Spa Mascotas, Bodegas(comida para mascotas por mayoreo)
    [58,50], [58,9],
    [50,4],
    // Salud
    [16,3], [16,10], [32,3], [32,16], [10,3], [10,32],
    // Estética
    [12,13], [12,51], [13,53], [51,53], [12,53],
    // Comida
    [1,4], [1,43], [43,27], [14,4], [45,36], [45,49],
    // Bienestar / deportes
    [36,37], [36,49], [37,17],
    // Ropa y accesorios
    [17,18], [17,19], [18,19],
    // Hogar / construcción
    [2,5], [2,6], [2,29], [2,40], [5,34], [34,30],
    // Eventos / decoración
    [30,42], [42,21], [42,35], [21,35], [55,42], [55,30],
    // Viajes / hospedaje
    [15,61], [61,27], [15,1], [27,23],
    // Vehículos
    [8,33], [8,2],
    // Educación / tecnología
    [22,38], [38,26], [26,56], [56,55], [48,26],
    // Finanzas
    [59,60], [54,60],
    // Inmobiliaria / legal
    [28,31], [28,40], [62,40], [62,31],
    // Drones / fotografía
    [57,21], [57,42],
    // Varios
    [47,37], [52,4],
];

$insertados = 0;
$existentes = 0;
$ins = $pdo->prepare("INSERT IGNORE INTO directorio_afinidades (categoria_origen_id, categoria_complementaria_id) VALUES (?,?)");
foreach ($pares as $par) {
    // ambos sentidos
    foreach ([$par, array_reverse($par)] as $d) {
        $ins->execute([$d[0], $d[1]]);
        if ($ins->rowCount() > 0) $insertados++;
    }
}
$total = $pdo->query("SELECT COUNT(*) FROM directorio_afinidades")->fetchColumn();
echo "[OK] Insertados nuevos: $insertados | total en tabla: $total\n";

// Verificación: categorías sin afinidad (para futura ampliación)
echo "\n=== categorias SIN afinidad todavia ===\n";
$rows=$pdo->query("SELECT c.id,c.nombre FROM directorio_categorias c WHERE c.activo=1 AND NOT EXISTS (SELECT 1 FROM directorio_afinidades a WHERE a.categoria_origen_id=c.id)")->fetchAll();
foreach($rows as $r) echo "  {$r['id']}|{$r['nombre']}\n";
echo "\n[LISTO] migracion afinidades completa.\n";
unlink(__FILE__);
echo "[BORRADO]\n";
