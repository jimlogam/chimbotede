<?php
/**
 * migrar_avisos.php — Crea las tablas del sistema de avisos (🔔)
 * ============================================================
 * USO (una sola vez):
 *   https://dechimbote.com/migrar_avisos.php?key=PON_AQUI_LA_CLAVE_INTERNA
 *
 * Crea:
 *   directorio_avisos_config  -> encendido/apagado de cada aviso (panel 🔔 Avisos)
 *   directorio_avisos_log     -> registro de todo lo avisado (dedupe, topes, resúmenes)
 *
 * Se AUTODESTRUYE al terminar. Es seguro volver a crearlas: usa IF NOT EXISTS.
 */

$CLAVE = 'PON_AQUI_LA_CLAVE_INTERNA';
header('Content-Type: text/plain; charset=utf-8');
if (!hash_equals($CLAVE, (string)($_GET['key'] ?? ''))) { http_response_code(403); exit('Acceso denegado.'); }

require_once __DIR__ . '/config.php';
$pdo = db();
$log = [];

try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS directorio_avisos_config (
        tipo            VARCHAR(30) NOT NULL,
        activo          TINYINT(1) NOT NULL DEFAULT 1,
        actualizado_en  DATETIME NULL,
        PRIMARY KEY (tipo)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = '✔ directorio_avisos_config lista.';

    $pdo->exec("CREATE TABLE IF NOT EXISTS directorio_avisos_log (
        id           BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        tipo         VARCHAR(30) NOT NULL,
        estado       ENUM('enviado','agrupado','duplicado','silencio','apagado','robot') NOT NULL DEFAULT 'enviado',
        clave        VARCHAR(120) NULL,
        resumen      VARCHAR(255) NULL,
        ip           VARCHAR(45) NULL,
        user_agent   VARCHAR(255) NULL,
        es_bot       TINYINT(1) NOT NULL DEFAULT 0,
        bot          VARCHAR(40) NULL,
        negocio_id   INT(10) UNSIGNED NULL,
        usuario_id   BIGINT(20) UNSIGNED NULL,
        creado_en    DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_estado_fecha (estado, creado_en),
        KEY idx_clave (clave, estado, creado_en),
        KEY idx_tipo_fecha (tipo, creado_en),
        KEY idx_bot (es_bot, creado_en),
        KEY idx_negocio (negocio_id, creado_en)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $log[] = '✔ directorio_avisos_log lista.';

    // Sembrar el catálogo con sus valores por defecto (no pisa lo que ya exista)
    $ins = $pdo->prepare("INSERT IGNORE INTO directorio_avisos_config (tipo, activo, actualizado_en) VALUES (?,?,?)");
    $n = 0;
    foreach (avisos_catalogo() as $tipo => $info) {
        $ins->execute([$tipo, (int)$info['defecto'], date('Y-m-d H:i:s')]);
        $n += $ins->rowCount();
    }
    $log[] = "✔ Catálogo de avisos sembrado ($n nuevos).";
    $log[] = '  Tipos: ' . implode(', ', array_keys(avisos_catalogo()));
    $log[] = 'Registros en el log: ' . (int)$pdo->query('SELECT COUNT(*) FROM ' . AVISOS_TABLA_LOG)->fetchColumn();
    $ok = true;
} catch (Throwable $e) {
    $ok = false;
    $log[] = '✖ ERROR: ' . $e->getMessage();
}

echo "MIGRACIÓN DEL SISTEMA DE AVISOS — dechimbote.com\n" . str_repeat('=', 52) . "\n";
echo implode("\n", $log) . "\n" . str_repeat('=', 52) . "\n";

if ($ok) {
    echo @unlink(__FILE__)
        ? "Este archivo se borró solo del servidor.\n"
        : "⚠️ Bórralo tú desde el Administrador de archivos de hPanel.\n";
    echo "Listo: revisa el panel en https://dechimbote.com/superadmin.php?seccion=avisos\n";
} else {
    echo "El archivo NO se borró para que puedas reintentar.\n";
}
