<?php
/**
 * google_instalar.php — INSTALADOR DE UN SOLO USO
 * Agrega la columna google_id a directorio_usuarios y se borra solo.
 * Ejecutar UNA VEZ: https://dechimbote.com/google_instalar.php?key=...
 */
require_once __DIR__ . '/config.php';

define('INSTALAR_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== INSTALAR_KEY) {
    http_response_code(403);
    exit('Acceso denegado.');
}
header('Content-Type: text/plain; charset=utf-8');
$pdo = db();

try {
    $existe = $pdo->query("SHOW COLUMNS FROM directorio_usuarios LIKE 'google_id'")->fetch();
    if ($existe) {
        echo "[OK] La columna google_id YA existe. Nada que hacer.\n";
    } else {
        $pdo->exec("ALTER TABLE directorio_usuarios
            ADD COLUMN google_id VARCHAR(255) NULL DEFAULT NULL AFTER password_hash,
            ADD UNIQUE KEY uniq_google_id (google_id)");
        echo "[OK] Columna google_id agregada con indice unico.\n";
    }
    $n = $pdo->query("SELECT COUNT(*) FROM directorio_usuarios")->fetchColumn();
    echo "[OK] Tabla directorio_usuarios operativa. Usuarios actuales: {$n}\n";
    echo "[LISTO] Instalacion completa. Este archivo se autodestruye.\n";
    unlink(__FILE__);
    echo "[BORRADO] google_instalar.php eliminado del servidor.\n";
} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    echo "No se borro el archivo; reporta este mensaje.\n";
}
