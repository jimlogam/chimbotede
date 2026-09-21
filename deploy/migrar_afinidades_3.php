<?php
/**
 * migrar_afinidades_3.php — CIERRE de la ampliación del mapa de alianzas (2026-09-10).
 *
 * Deja a TODOS los rubros activos con 3 o más categorías complementarias. Añade pares en ambos
 * sentidos (INSERT IGNORE); no borra ni modifica nada. Se autodestruye al terminar.
 * Uso: https://dechimbote.com/migrar_afinidades_3.php?key=<CLAVE>
 */
require_once __DIR__ . '/config.php';
define('MIG_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== MIG_KEY) { http_response_code(403); exit('denegado'); }
header('Content-Type: text/plain; charset=utf-8');
$pdo = db();

// Abogados ⇄ Librerías / Útiles (papelería, trámites y sellos). Era el único rubro con 2 alianzas.
$pares = [
    [31, 22],
];

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
$total = (int)$pdo->query("SELECT COUNT(*) FROM directorio_afinidades")->fetchColumn();
echo "[OK] filas nuevas: $nuevos | total en tabla: $total\n\n";

$filas = $pdo->query("SELECT c.nombre,
        (SELECT COUNT(*) FROM directorio_afinidades a WHERE a.categoria_origen_id = c.id AND a.activo = 1) AS afinidades
      FROM directorio_categorias c WHERE c.activo = 1 ORDER BY afinidades ASC")->fetchAll();
echo "=== rubros con MENOS de 3 complementos ===\n";
$hay = false;
foreach ($filas as $f) {
    if ((int)$f['afinidades'] < 3) { $hay = true; echo "  {$f['afinidades']}  {$f['nombre']}\n"; }
}
if (!$hay) echo "  (ninguno: todos los rubros activos tienen 3 o más)\n";
echo "\n[LISTO]\n";
unlink(__FILE__);
echo "[BORRADO] el migrador se autodestruyo.\n";
