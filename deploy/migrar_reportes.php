<?php
/**
 * migrar_reportes.php — Crea la tabla directorio_reportes
 * ============================================================
 * USO (una sola vez, desde el navegador):
 *   https://dechimbote.com/migrar_reportes.php?key=PON_AQUI_LA_CLAVE_INTERNA
 *
 * Se AUTODESTRUYE al terminar (borra este archivo del servidor), así nadie
 * puede volver a ejecutarlo. Si algo falla, NO se borra y muestra el error.
 *
 * Nota: en esta base de datos los productos viven en `directorio_servicios`
 * (no existe ninguna tabla `directorio_productos`), por eso producto_id
 * apunta a directorio_servicios.id.
 */

$CLAVE = 'PON_AQUI_LA_CLAVE_INTERNA';

header('Content-Type: text/plain; charset=utf-8');

if (!hash_equals($CLAVE, (string)($_GET['key'] ?? ''))) {
    http_response_code(403);
    exit('Acceso denegado.');
}

require_once __DIR__ . '/config.php';

$pdo = db();
$log = [];

try {
    $existe = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.tables
        WHERE table_schema = DATABASE() AND table_name = 'directorio_reportes'")->fetchColumn();

    if ($existe) {
        $log[] = 'La tabla directorio_reportes ya existía: no se cambió nada.';
    } else {
        $pdo->exec("CREATE TABLE directorio_reportes (
            id            INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            usuario_id    BIGINT(20) UNSIGNED NULL,
            negocio_id    INT(10) UNSIGNED NOT NULL,
            producto_id   INT(10) UNSIGNED NULL,
            motivo        VARCHAR(50) NOT NULL,
            descripcion   TEXT NULL,
            ip            VARCHAR(45) NULL,
            estado        ENUM('pendiente','revisado','resuelto','ignorado') NOT NULL DEFAULT 'pendiente',
            atendido_por  BIGINT(20) UNSIGNED NULL,
            atendido_en   DATETIME NULL,
            fecha         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_negocio (negocio_id),
            KEY idx_estado (estado),
            KEY idx_fecha (fecha),
            KEY idx_usuario (usuario_id),
            KEY idx_ip (ip)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $log[] = '✔ Tabla directorio_reportes creada correctamente.';
    }

    // Comprobación final: la tabla responde
    $cols = $pdo->query("SHOW COLUMNS FROM directorio_reportes")->fetchAll();
    $log[] = 'Columnas (' . count($cols) . '): ' . implode(', ', array_column($cols, 'Field'));
    $log[] = 'Filas actuales: ' . (int)$pdo->query("SELECT COUNT(*) FROM directorio_reportes")->fetchColumn();

    $todo_ok = true;
} catch (Throwable $e) {
    $todo_ok = false;
    $log[] = '✖ ERROR: ' . $e->getMessage();
}

echo "MIGRACIÓN DE REPORTES — dechimbote.com\n";
echo str_repeat('=', 44) . "\n";
echo implode("\n", $log) . "\n";
echo str_repeat('=', 44) . "\n";

if ($todo_ok) {
    if (@unlink(__FILE__)) {
        echo "Este archivo se borró solo del servidor (ya no existe migrar_reportes.php).\n";
    } else {
        echo "⚠️ No se pudo borrar el archivo: BÓRRALO TÚ desde el Administrador de archivos de hPanel.\n";
    }
    echo "Listo: ya puedes usar el botón 🚩 Reportar en las fichas de negocio.\n";
} else {
    echo "El archivo NO se borró para que puedas revisar el error y reintentar.\n";
}
