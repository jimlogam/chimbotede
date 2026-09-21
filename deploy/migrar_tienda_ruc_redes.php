<?php
/**
 * migrar_tienda_ruc_redes.php — 🪪 RUC + CORREO, 4 REDES MÁS Y LOS «USOS» DEL TELÉFONO
 * ==============================================================================================
 * Tercera tanda del editor del dueño (`mi-tienda.php`, pedido del jefe 2026-09-16 noche):
 *
 * 1) **`directorio_negocios`: 6 columnas nuevas** (todas NULL, opcionales):
 *      · `ruc`      → el RUC del negocio (11 dígitos). El jefe: *«los negocios con RUC son los que se
 *                     llevan los contratos más grandes, "sí funciona"»* → va en la ficha a la vista.
 *      · `email`    → correo de contacto del negocio (público, distinto del correo de la cuenta).
 *      · `youtube`  · `twitter` (X) · `telegram` · `linkedin` → las redes que faltaban para que el
 *                     dueño tenga «al menos 6 opciones» (con `facebook`, `instagram`, `tiktok` y `web`,
 *                     que ya existían, el modal ofrece **8**).
 *    ⚠️ `ruc` y `email` NO existían en ninguna forma (comprobado: ningún archivo del sitio lee
 *    `$negocio['email']`), así que añadirlos no cambia el comportamiento de nada.
 *
 * 2) **`directorio_negocio_telefonos.tipo`** pasa a
 *      ENUM('ambos','llamada','whatsapp','ventas','atencion','mayorista','pedidos','otros')
 *    porque el dueño ahora le pone un **uso** a cada número («solo ventas», «solo atención», «solo
 *    WhatsApp», «para otras tiendas / mayoristas»…). Los 3 valores viejos se conservan tal cual y las
 *    filas que ya existen NO se tocan. Solo `llamada` y `whatsapp` limitan los botones de la ficha:
 *    el resto de los usos dan **los dos botones** (llamar y WhatsApp) — lo decide
 *    `telefono_tipo_acciones()` en `includes/helpers.php`.
 *
 * Idempotente: se puede volver a correr sin daño.
 *
 * Rollback (si algún día se abandona):
 *   ALTER TABLE directorio_negocios DROP COLUMN ruc, DROP COLUMN email,
 *     DROP COLUMN youtube, DROP COLUMN twitter, DROP COLUMN telegram, DROP COLUMN linkedin;
 *   ALTER TABLE directorio_negocio_telefonos MODIFY tipo ENUM('ambos','llamada','whatsapp')
 *     NOT NULL DEFAULT 'ambos';
 *
 * Uso: https://dechimbote.com/migrar_tienda_ruc_redes.php?key=<CLAVE>  (se autodestruye)
 */
require_once __DIR__ . '/config.php';
define('MIG_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== MIG_KEY) { http_response_code(403); exit('denegado'); }
header('Content-Type: text/plain; charset=utf-8');
set_time_limit(120);
$pdo = db();

/* ============================ 1) Las 6 columnas nuevas ============================ */
echo "=== 1) COLUMNAS NUEVAS EN directorio_negocios ===\n\n";
$nuevas = [
    'ruc'      => "VARCHAR(20) NULL DEFAULT NULL COMMENT 'RUC del negocio (11 dígitos), opcional'",
    'email'    => "VARCHAR(120) NULL DEFAULT NULL COMMENT 'Correo público del negocio (no el de la cuenta)'",
    'youtube'  => "VARCHAR(255) NULL DEFAULT NULL COMMENT 'Canal de YouTube'",
    'twitter'  => "VARCHAR(255) NULL DEFAULT NULL COMMENT 'X (Twitter)'",
    'telegram' => "VARCHAR(255) NULL DEFAULT NULL COMMENT 'Telegram'",
    'linkedin' => "VARCHAR(255) NULL DEFAULT NULL COMMENT 'LinkedIn'",
];
$existentes = array_map(static function ($c) { return $c['Field']; },
    $pdo->query("SHOW COLUMNS FROM directorio_negocios")->fetchAll(PDO::FETCH_ASSOC));
foreach ($nuevas as $col => $def) {
    if (in_array($col, $existentes, true)) { echo "[SALTADO] {$col} ya existía\n"; continue; }
    $t0 = microtime(true);
    $pdo->exec("ALTER TABLE directorio_negocios ADD COLUMN `{$col}` {$def}");
    printf("+ %-9s en %.2f s\n", $col, microtime(true) - $t0);
}
$despues = array_map(static function ($c) { return $c['Field'] . '(' . $c['Type'] . ')'; },
    $pdo->query("SHOW COLUMNS FROM directorio_negocios")->fetchAll(PDO::FETCH_ASSOC));
echo "columnas de contacto/redes ahora: ";
foreach ($despues as $c) { if (preg_match('/^(ruc|email|web|facebook|instagram|tiktok|youtube|twitter|telegram|linkedin)\(/', $c)) echo $c . ' '; }
echo "\n\n";

/* ==================== 2) Los USOS del teléfono (ENUM ampliado) ==================== */
echo "=== 2) ENUM de directorio_negocio_telefonos.tipo ===\n\n";
$antes = $pdo->query("SHOW COLUMNS FROM directorio_negocio_telefonos LIKE 'tipo'")->fetch(PDO::FETCH_ASSOC);
echo "ANTES : " . ($antes['Type'] ?? '?') . "\n";
$quiero = "enum('ambos','llamada','whatsapp','ventas','atencion','mayorista','pedidos','otros')";
if (strpos((string)($antes['Type'] ?? ''), "'ventas'") !== false) {
    echo "[SALTADO] el ENUM ya tiene los usos nuevos\n";
} else {
    $t0 = microtime(true);
    $pdo->exec("ALTER TABLE directorio_negocio_telefonos MODIFY COLUMN tipo
                ENUM('ambos','llamada','whatsapp','ventas','atencion','mayorista','pedidos','otros')
                NOT NULL DEFAULT 'ambos'");
    printf("ALTER ejecutado en %.2f s\n", microtime(true) - $t0);
}
$despues2 = $pdo->query("SHOW COLUMNS FROM directorio_negocio_telefonos LIKE 'tipo'")->fetch(PDO::FETCH_ASSOC);
echo "DESPUES: " . ($despues2['Type'] ?? '?') . "\n";
echo "filas de teléfonos que ya existían (intactas): "
   . (int)$pdo->query("SELECT COUNT(*) FROM directorio_negocio_telefonos")->fetchColumn() . "\n\n";

/* ===================== 3) PRUEBA REAL (transacción, se deshace) ===================== */
echo "=== 3) PRUEBA REAL (se deshace con ROLLBACK) ===\n\n";
$okRuc = $okMail = $okRed = $okUso = $okViejo = false;
$usos = ['ambos', 'llamada', 'whatsapp', 'ventas', 'atencion', 'mayorista', 'pedidos', 'otros'];
$pdo->beginTransaction();
try {
    $pdo->prepare("INSERT INTO directorio_negocios
                     (nombre, slug, ubicacion_tipo, estado, plantilla_id, paleta_id, ruc, email,
                      youtube, twitter, telegram, linkedin)
                   VALUES (?,?, 'fisica', 'pendiente', 1, 1, ?,?,?,?,?,?)")
        ->execute(['__PRUEBA RUC REDES__ ' . time(), '__prueba-ruc-redes-' . time(),
                   '20601234567', 'ventas@tienda.com', 'youtube.com/@tienda', 'x.com/tienda',
                   't.me/tienda', 'linkedin.com/company/tienda']);
    $id = (int)$pdo->lastInsertId();

    $fila = $pdo->query("SELECT ruc, email, youtube, twitter, telegram, linkedin
                           FROM directorio_negocios WHERE id = " . $id)->fetch(PDO::FETCH_ASSOC);
    $okRuc  = ($fila['ruc'] === '20601234567');
    $okMail = ($fila['email'] === 'ventas@tienda.com');
    $okRed  = ($fila['youtube'] === 'youtube.com/@tienda' && $fila['twitter'] === 'x.com/tienda'
               && $fila['telegram'] === 't.me/tienda' && $fila['linkedin'] === 'linkedin.com/company/tienda');
    echo "PRUEBA RUC/correo/redes: " . ($okRuc && $okMail && $okRed ? 'OK' : 'FALLA') . "\n";
    echo "   ruc={$fila['ruc']} · email={$fila['email']} · youtube={$fila['youtube']}"
       . " · twitter={$fila['twitter']} · telegram={$fila['telegram']} · linkedin={$fila['linkedin']}\n";

    // Los 8 usos del teléfono, uno por uno (¿MySQL acepta cada valor sin dejarlo vacío?)
    $ins = $pdo->prepare("INSERT INTO directorio_negocio_telefonos (negocio_id, numero, tipo, orden) VALUES (?,?,?,?)");
    $guardados = [];
    foreach ($usos as $i => $uso) { $ins->execute([$id, '94300000' . $i, $uso, $i + 1]); }
    foreach ($pdo->query("SELECT tipo FROM directorio_negocio_telefonos WHERE negocio_id = " . $id . " ORDER BY orden")->fetchAll(PDO::FETCH_COLUMN) as $t) {
        $guardados[] = (string)$t;
    }
    $okUso = ($guardados === $usos);
    echo "PRUEBA de los 8 usos: " . ($okUso ? 'OK' : 'FALLA') . " → " . implode(', ', $guardados) . "\n";

    // Un uso inventado NO debe entrar (y menos quedar vacío, que es la trampa del ENUM)
    try {
        $ins->execute([$id, '943999999', 'inventado', 99]);
        $malo = (string)$pdo->query("SELECT tipo FROM directorio_negocio_telefonos WHERE negocio_id = " . $id
                                    . " AND numero = '943999999'")->fetchColumn();
        $okViejo = ($malo === '');
        echo "PRUEBA uso inventado: " . ($okViejo ? 'OK (MySQL lo rechaza)' : "OJO: quedó «{$malo}»") . "\n";
    } catch (Throwable $e) {
        $okViejo = true;
        echo "PRUEBA uso inventado: OK (rechazado: " . $e->getMessage() . ")\n";
    }

    $pdo->rollBack();
    echo "PRUEBAS deshechas (ROLLBACK): no quedó ningún dato de prueba\n";
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    echo "PRUEBA ERROR: " . $e->getMessage() . "\n";
}

echo "\nconteo final: tiendas=" . (int)$pdo->query("SELECT COUNT(*) FROM directorio_negocios")->fetchColumn()
   . " · con RUC=" . (int)$pdo->query("SELECT COUNT(*) FROM directorio_negocios WHERE ruc IS NOT NULL AND ruc <> ''")->fetchColumn()
   . " · teléfonos=" . (int)$pdo->query("SELECT COUNT(*) FROM directorio_negocio_telefonos")->fetchColumn() . "\n";

echo ($okRuc && $okMail && $okRed && $okUso && $okViejo)
    ? "\n[LISTO] el RUC, el correo, las 4 redes nuevas y los 8 usos del teléfono ya se pueden guardar.\n"
    : "\n[ATENCION] alguna prueba no pasó: revisar antes de usar el editor.\n";

unlink(__FILE__);
echo "[BORRADO] el migrador se autodestruyó.\n";
