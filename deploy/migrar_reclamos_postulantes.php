<?php
/**
 * migrar_reclamos_postulantes.php — Migración de un solo uso.
 * Crea:
 *  1) directorio_reclamos   : solicitudes "quiero reclamar mi negocio".
 *  2) directorio_postulantes: postulaciones a "Trabaja con nosotros".
 * Se autodestruye al terminar.
 */
require_once __DIR__ . '/config.php';

define('MIG_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== MIG_KEY) { http_response_code(403); exit('Acceso denegado.'); }
header('Content-Type: text/plain; charset=utf-8');
$pdo = db();

function existe_tabla($pdo, $t) { return $pdo->query("SHOW TABLES LIKE '$t'")->fetchColumn() ? true : false; }

try {
    if (existe_tabla($pdo, 'directorio_reclamos')) {
        echo "[OK] directorio_reclamos ya existe.\n";
    } else {
        $pdo->exec("CREATE TABLE directorio_reclamos (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            negocio_id INT UNSIGNED NOT NULL,
            usuario_id BIGINT UNSIGNED NULL,
            nombre VARCHAR(120) NOT NULL,
            email VARCHAR(150) NOT NULL,
            telefono VARCHAR(40) NULL,
            explicacion TEXT NULL,
            estado ENUM('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
            atendido_por BIGINT UNSIGNED NULL,
            creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            atendido_en DATETIME NULL,
            PRIMARY KEY (id),
            KEY idx_negocio (negocio_id),
            KEY idx_estado (estado)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "[OK] directorio_reclamos creada.\n";
    }

    if (existe_tabla($pdo, 'directorio_postulantes')) {
        echo "[OK] directorio_postulantes ya existe.\n";
    } else {
        $pdo->exec("CREATE TABLE directorio_postulantes (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT,
            nombre_real VARCHAR(120) NOT NULL,
            email VARCHAR(150) NOT NULL,
            telefono VARCHAR(40) NULL,
            redes VARCHAR(255) NULL,
            info VARCHAR(500) NULL,
            sueldo_solicitado VARCHAR(20) NULL,
            jornada VARCHAR(40) NULL,
            movilidad ENUM('si','no') NULL,
            segundo_idioma VARCHAR(120) NULL,
            formacion VARCHAR(255) NULL,
            estado ENUM('nuevo','en_revision','contactado','descartado') NOT NULL DEFAULT 'nuevo',
            creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "[OK] directorio_postulantes creada.\n";
    }

    echo "[LISTO] Migracion completa. Autodestruccion...\n";
    unlink(__FILE__);
    echo "[BORRADO] migrar_reclamos_postulantes.php eliminado del servidor.\n";
} catch (PDOException $e) {
    echo "[ERROR] " . $e->getMessage() . "\n";
    echo "No se borro el archivo.\n";
}
