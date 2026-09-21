<?php
/**
 * migrar_galeria_productos.php — Migración de un solo uso.
 * Crea la tabla directorio_producto_fotos para que cada PRODUCTO (directorio_servicios)
 * pueda tener varias fotos (galería), replicando el patrón de directorio_fotos (negocios).
 * Ejecutar UNA VEZ: https://dechimbote.com/migrar_galeria_productos.php?key=...
 * Se autodestruye al terminar.
 */
require_once __DIR__ . '/config.php';

define('MIG_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== MIG_KEY) { http_response_code(403); exit('Acceso denegado.'); }
header('Content-Type: text/plain; charset=utf-8');
$pdo = db();

try {
    // Idempotente: si ya existe, no hacer nada
    $existe = $pdo->query("SHOW TABLES LIKE 'directorio_producto_fotos'")->fetchColumn();
    if ($existe) {
        echo "[OK] La tabla directorio_producto_fotos YA existe. Nada que hacer.\n";
    } else {
        $pdo->exec("CREATE TABLE directorio_producto_fotos (
            id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            producto_id INT(10) UNSIGNED NOT NULL,
            ruta VARCHAR(255) NOT NULL,
            descripcion VARCHAR(255) DEFAULT NULL,
            orden INT(11) NOT NULL DEFAULT 0,
            creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY idx_producto (producto_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "[OK] Tabla directorio_producto_fotos creada.\n";
    }

    // Columnas auxiliares opcionales para el asistente (físico/virtual, ubicación opcional)
    try {
        $c = $pdo->query("SHOW COLUMNS FROM directorio_servicios LIKE 'tipo_producto'")->fetchColumn();
        if (!$c) {
            $pdo->exec("ALTER TABLE directorio_servicios ADD COLUMN tipo_producto ENUM('fisico','virtual') NOT NULL DEFAULT 'fisico' AFTER titulo");
            echo "[OK] Columna tipo_producto agregada a directorio_servicios.\n";
        } else {
            echo "[OK] tipo_producto ya existe.\n";
        }
    } catch (PDOException $e) {
        echo "[AVISO] tipo_producto: " . $e->getMessage() . "\n";
    }
    echo "[OK] La unidad de medida se guarda en la columna 'unidad' ya existente.\n";

    echo "[LISTO] Migracion completa. Este archivo se autodestruye.\n";
    unlink(__FILE__);
    echo "[BORRADO] migrar_galeria_productos.php eliminado del servidor.\n";
} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    echo "No se borro el archivo; reporta este mensaje.\n";
}
