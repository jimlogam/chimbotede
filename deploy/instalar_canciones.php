<?php
/**
 * instalar_canciones.php — Crea las tablas de la CANCIÓN DE CADA TIENDA 🎵
 * ======================================================================
 * USO (una sola vez, desde el navegador):
 *   https://dechimbote.com/instalar_canciones.php?key=PON_AQUI_LA_CLAVE_INTERNA
 *
 * Crea (si no existen) las dos tablas del módulo:
 *   · `directorio_canciones`        → la cola y el archivo de cada canción (una fila por tienda):
 *                                     el pedido, el task_id de Treblo, la cuenta usada, la ruta del
 *                                     MP3, su duración exacta y los errores.
 *   · `directorio_cancion_claves`   → el registro del consumo de las 4 cuentas de Treblo (saldo,
 *                                     cuántas canciones lleva cada una, y el latido/aviso del módulo).
 *
 * ⚠️ NO toca `directorio_negocios` ni la vista de la ficha: la ruta del audio vive en
 *    `directorio_canciones.ruta` (una sola verdad, y la ficha la busca por `negocio_id`).
 *
 * Se AUTODESTRUYE al terminar. Si algo falla, NO se borra y muestra el error.
 *
 * ⚠️ ¿POR QUÉ NO SE LLAMA `migrar_canciones.php`? Porque el hosting (Hostinger) responde **403** a
 *    cualquier archivo que empiece con `migrar_` — comprobado el 2026-09-17 con `migrar_reportes.php`
 *    (que existió de verdad): el navegador no lo abre. Los instaladores del sitio (`google_instalar.php`,
 *    `hubspot_instalar.php`) sí funcionan: de ahí el nombre.
 *
 * Guía: GUIA_CONSTRUCTOR_DE_TIENDAS.md §16.
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

$tablas = [
    'directorio_canciones' => "CREATE TABLE directorio_canciones (
        id             INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        negocio_id     INT(10) UNSIGNED NOT NULL,
        nombre         VARCHAR(160) NOT NULL DEFAULT '',
        rubro          VARCHAR(120) NOT NULL DEFAULT '',
        distrito       VARCHAR(80)  NOT NULL DEFAULT '',
        estado         ENUM('pendiente','generando','listo','error') NOT NULL DEFAULT 'pendiente',
        clave_n        TINYINT(3) UNSIGNED NULL,
        task_id        VARCHAR(64)  NULL,
        modelo         VARCHAR(32)  NULL,
        prompt         TEXT NULL,
        letra          TEXT NULL,
        ruta           VARCHAR(255) NULL,
        duracion       DECIMAL(6,3) NULL,
        bytes          INT(10) UNSIGNED NULL,
        error          VARCHAR(255) NULL,
        intentos       TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
        creado_en      DATETIME NOT NULL,
        actualizado_en DATETIME NULL,
        PRIMARY KEY (id),
        KEY idx_negocio (negocio_id),
        KEY idx_estado (estado),
        KEY idx_creado (creado_en)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    'directorio_cancion_claves' => "CREATE TABLE directorio_cancion_claves (
        n             TINYINT(3) UNSIGNED NOT NULL,
        etiqueta      VARCHAR(40) NULL,
        saldo         INT(11) NULL,
        agotada       TINYINT(1) NOT NULL DEFAULT 0,
        usadas        INT(10) UNSIGNED NOT NULL DEFAULT 0,
        comprobado_en DATETIME NULL,
        aviso_en      DATETIME NULL,
        latido_en     DATETIME NULL,
        PRIMARY KEY (n)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
];

try {
    foreach ($tablas as $nombre => $sql) {
        $existe = (int)$pdo->query("SELECT COUNT(*) FROM information_schema.tables
            WHERE table_schema = DATABASE() AND table_name = " . $pdo->quote($nombre))->fetchColumn();
        if ($existe) {
            $log[] = '· ' . $nombre . ': ya existía (no se tocó).';
        } else {
            $pdo->exec($sql);
            $log[] = '✔ ' . $nombre . ': creada.';
        }
    }

    // La fila 0 de las cuentas es el «estado general» (latido y aviso al jefe).
    $pdo->exec("INSERT IGNORE INTO directorio_cancion_claves (n, etiqueta) VALUES (0, 'TOTAL')");
    foreach ([1, 2, 3, 4] as $n) {
        $pdo->exec("INSERT IGNORE INTO directorio_cancion_claves (n, etiqueta) VALUES ($n, 'cuenta $n')");
    }

    foreach (array_keys($tablas) as $nombre) {
        $cols = $pdo->query("SHOW COLUMNS FROM " . $nombre)->fetchAll();
        $log[] = 'Columnas de ' . $nombre . ' (' . count($cols) . '): ' . implode(', ', array_column($cols, 'Field'));
    }
    $log[] = 'Canciones apuntadas: ' . (int)$pdo->query("SELECT COUNT(*) FROM directorio_canciones")->fetchColumn();
    $log[] = 'Cuentas registradas: ' . (int)$pdo->query("SELECT COUNT(*) FROM directorio_cancion_claves")->fetchColumn();
    $todo_ok = true;
} catch (Throwable $e) {
    $todo_ok = false;
    $log[] = '✖ ERROR: ' . $e->getMessage();
}

echo "INSTALACIÓN DE LAS CANCIONES DE LAS TIENDAS — dechimbote.com\n";
echo str_repeat('=', 60) . "\n";
echo implode("\n", $log) . "\n";
echo str_repeat('=', 60) . "\n";

if ($todo_ok) {
    if (@unlink(__FILE__)) {
        echo "Este archivo se borró solo del servidor (ya no existe instalar_canciones.php).\n";
    } else {
        echo "⚠️ No se pudo borrar el archivo: BÓRRALO TÚ desde el Administrador de archivos de hPanel.\n";
    }
    echo "Listo: el módulo 🎵 ya puede guardar la canción de cada tienda.\n";
} else {
    echo "El archivo NO se borró para que puedas revisar el error y reintentar.\n";
}
