<?php
/**
 * migrar_estadisticas.php — Módulo de ESTADÍSTICAS del Súper Admin
 * ============================================================
 * Crea las tablas de tracking anónimo de visitas (sin datos personales):
 *
 *   directorio_stats_sesiones  — una fila por "visita continua" (cookie anónima
 *                                + ventana de inactividad). Guarda duración en
 *                                segundos, páginas vistas, entrada/salida,
 *                                referrer (de dónde llegó) y dispositivo.
 *   directorio_stats_eventos   — cada pageview / búsqueda / clic externo con
 *                                timestamp (permite top de páginas, rutas,
 *                                entradas/salidas y "a qué página cambiaron").
 *
 * Se autodestruye al terminar.
 * Uso: migrar_estadisticas.php?key=PON_AQUI_LA_CLAVE_INTERNA
 */
require_once __DIR__ . '/config.php';
define('MIG_KEY_STATS', 'PON_AQUI_LA_CLAVE_INTERNA');
if (($_GET['key'] ?? '') !== MIG_KEY_STATS) { http_response_code(403); exit('denegado'); }
header('Content-Type: text/plain; charset=utf-8');
$pdo = db();

// ===== 1) Tabla de sesiones (una visita continua por cookie) =====
$existe = $pdo->query("SHOW TABLES LIKE 'directorio_stats_sesiones'")->fetchColumn();
if (!$existe) {
    $pdo->exec("CREATE TABLE directorio_stats_sesiones (
        id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        cookie        CHAR(32) NOT NULL,
        usuario_id    INT UNSIGNED NULL,
        inicio        DATETIME NOT NULL,
        ultimo_activo DATETIME NOT NULL,
        segundos      INT UNSIGNED NOT NULL DEFAULT 0,
        paginas       INT UNSIGNED NOT NULL DEFAULT 0,
        entrada       VARCHAR(190) NOT NULL DEFAULT '',
        salida        VARCHAR(190) NOT NULL DEFAULT '',
        pagina_actual VARCHAR(190) NOT NULL DEFAULT '',
        ref_dominio   VARCHAR(120) NOT NULL DEFAULT '',
        ref_url       VARCHAR(500) NOT NULL DEFAULT '',
        dispositivo   VARCHAR(12) NOT NULL DEFAULT 'desktop',
        es_bot        TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        KEY idx_stats_cookie (cookie),
        KEY idx_stats_usr (usuario_id),
        KEY idx_stats_ultimo (ultimo_activo),
        KEY idx_stats_inicio (inicio),
        KEY idx_stats_ref (ref_dominio)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "[OK] tabla directorio_stats_sesiones creada.\n";
} else {
    echo "[OK] directorio_stats_sesiones ya existia.\n";
}

// ===== 2) Tabla de eventos (pageviews / busquedas / clics) =====
$existe = $pdo->query("SHOW TABLES LIKE 'directorio_stats_eventos'")->fetchColumn();
if (!$existe) {
    $pdo->exec("CREATE TABLE directorio_stats_eventos (
        id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        sesion_id     BIGINT UNSIGNED NULL,
        cookie        CHAR(32) NOT NULL,
        usuario_id    INT UNSIGNED NULL,
        tipo          VARCHAR(10) NOT NULL DEFAULT 'pv',
        pagina        VARCHAR(190) NOT NULL DEFAULT '',
        tipo_pagina   VARCHAR(24) NOT NULL DEFAULT 'otro',
        ref_dominio   VARCHAR(120) NOT NULL DEFAULT '',
        fecha         DATETIME NOT NULL,
        PRIMARY KEY (id),
        KEY idx_ev_fecha (fecha),
        KEY idx_ev_sesion (sesion_id),
        KEY idx_ev_tipo_pagina (tipo_pagina, fecha),
        KEY idx_ev_pagina (pagina(64)),
        KEY idx_ev_busqueda (tipo, fecha)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "[OK] tabla directorio_stats_eventos creada.\n";
} else {
    echo "[OK] directorio_stats_eventos ya existia.\n";
}

echo "\n[LISTO] migracion estadisticas completa.\n";
echo "Sube ahora (o despues): api/estadisticas.php, includes/estadisticas.php,\n";
echo "includes/vista_estadisticas_admin.php, assets/js/estadisticas.js y la\n";
echo "seccion 'estadisticas' en superadmin.php + script en includes/footer.php.\n";
unlink(__FILE__);
echo "[BORRADO]\n";
