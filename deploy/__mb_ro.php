<?php
/**
 * __mb_ro.php — TEMPORAL: intento de crear el usuario MySQL de solo lectura para Metabase
 * + volcado de esquema real de las tablas. Se ejecuta UNA vez y se BORRA del servidor.
 * Protegido por key. No contiene credenciales: usa db() del config.php del servidor.
 */
header('Content-Type: application/json; charset=utf-8');

const MB_RO_KEY = 'PON_AQUI_LA_CLAVE';
const MB_RO_USER = 'u196269909_metabase';
const MB_RO_HOST = '%';
const MB_RO_PASS = 'PON_AQUI_LA_CLAVE';

if (($_GET['key'] ?? '') !== MB_RO_KEY) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'clave invalida']);
    exit;
}

require_once __DIR__ . '/config.php';
$pdo = db();

$accion = $_GET['accion'] ?? 'info';
$res = ['ok' => true, 'accion' => $accion, 'pasos' => []];

/** Ejecuta una sentencia y devuelve [ok, detalle]. */
function paso(PDO $pdo, array &$res, string $titulo, string $sql): array {
    try {
        $stmt = $pdo->query($sql);
        $filas = $stmt->fetchAll(PDO::FETCH_NUM);
        return ['ok' => true, 'detalle' => array_map(fn($f) => implode(' | ', $f), $filas)];
    } catch (Throwable $e) {
        return ['ok' => false, 'detalle' => $e->getCode() . ': ' . mb_substr($e->getMessage(), 0, 300)];
    }
}

if ($accion === 'info') {
    // 1) Qué privilegios tiene el usuario del sitio (dice si CREATE USER/GRANT es viable)
    $res['pasos']['grants_actuales'] = paso($pdo, $res, 'grants', 'SHOW GRANTS FOR CURRENT_USER()');

    // 2) Tablas existentes
    $res['pasos']['tablas'] = paso($pdo, $res, 'tablas', "SHOW TABLES");

    // 3) Esquema de las tablas objetivo y las que usan los dashboards
    foreach (['directorio_negocios', 'directorio_usuarios', 'directorio_servicios',
              'directorio_producto_fotos', 'directorio_stats_eventos', 'directorio_stats_sesiones',
              'directorio_distritos', 'directorio_categorias', 'directorio_subcategorias',
              'directorio_vistas', 'directorio_negocio_pagos', 'directorio_pagos',
              'directorio_opiniones', 'directorio_reclamos', 'directorio_zonas'] as $t) {
        $res['pasos']['columnas_' . $t] = paso($pdo, $res, 'col', "SHOW FULL COLUMNS FROM `{$t}`");
        $res['pasos']['count_' . $t] = paso($pdo, $res, 'count', "SELECT COUNT(*) FROM `{$t}`");
    }

    // 4) Definición de las vistas útiles (para saber qué columnas exponen)
    foreach (['vista_negocios_con_pagos', 'vista_negocios_populares', 'vista_negocio_ficha_completa'] as $v) {
        $res['pasos']['crea_' . $v] = paso($pdo, $res, 'view', "SHOW CREATE VIEW `{$v}`");
    }

    // 5) Muestras: pagos por estado y negocios con dueno (para el dashboard de ventas)
    $res['pasos']['pagos_por_estado'] = paso($pdo, $res, 'pp',
        "SELECT estado, COUNT(*) FROM directorio_negocio_pagos GROUP BY estado");
    $res['pasos']['negocios_con_dueno'] = paso($pdo, $res, 'nd',
        "SELECT COUNT(*) FROM directorio_negocios WHERE dueno_id IS NOT NULL");
} elseif ($accion === 'crear') {
    $u = MB_RO_USER;
    $res['pasos']['create_user'] = paso($pdo, $res, 'create',
        "CREATE USER '{$u}'@'" . MB_RO_HOST . "' IDENTIFIED BY '" . MB_RO_PASS . "'");

    // Si ya existía, reintentar solo la contraseña
    if (!$res['pasos']['create_user']['ok']) {
        $res['pasos']['alter_user'] = paso($pdo, $res, 'alter',
            "ALTER USER '{$u}'@'" . MB_RO_HOST . "' IDENTIFIED BY '" . MB_RO_PASS . "'");
    }

    foreach (['directorio_negocios', 'directorio_usuarios', 'directorio_servicios',
              'directorio_producto_fotos', 'directorio_stats_eventos',
              'directorio_stats_sesiones'] as $t) {
        $res['pasos']['grant_' . $t] = paso($pdo, $res, 'grant',
            "GRANT SELECT ON `u196269909_CHIMBOTEALDIA`.`{$t}` TO '{$u}'@'" . MB_RO_HOST . "'");
    }

    $res['pasos']['flush'] = paso($pdo, $res, 'flush', 'FLUSH PRIVILEGES');
    $res['pasos']['verifica'] = paso($pdo, $res, 'verifica', "SHOW GRANTS FOR '{$u}'@'" . MB_RO_HOST . "'");
} else {
    $res = ['ok' => false, 'error' => 'accion desconocida'];
}

echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
