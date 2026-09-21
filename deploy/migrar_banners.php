<?php
/**
 * migrar_banners.php — Instala el módulo de publicidad (banners rotativos programados).
 * =====================================================================================
 * 1) Crea las tablas: directorio_banners y directorio_banner_stats.
 * 2) Con ?importar=1 registra los 69 banners de la campaña de necesidades
 *    (imágenes locales subidas a assets/uploads/banners/) — idempotente (no duplica).
 *
 * Uso (una sola vez, y borra este archivo del servidor al terminar):
 *   https://dechimbote.com/migrar_banners.php?key=chimbotealdia-banners-2026
 *   https://dechimbote.com/migrar_banners.php?key=chimbotealdia-banners-2026&importar=1
 */
require_once __DIR__ . '/config.php';
if (($_GET['key'] ?? '') !== 'chimbotealdia-banners-2026') { http_response_code(403); die('Acceso denegado.'); }

header('Content-Type: text/html; charset=utf-8');
$pdo = db();

echo '<!doctype html><meta charset="utf-8"><body style="font-family:sans-serif;padding:2rem;line-height:1.6">';
echo '<h3>📢 Migración del módulo de banners</h3><pre>';

/* ---------- 1) Tablas ---------- */
$pdo->exec("CREATE TABLE IF NOT EXISTS directorio_banners (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(190) NOT NULL,
    texto VARCHAR(255) NULL,
    imagen VARCHAR(255) NULL,
    tema VARCHAR(120) NULL COMMENT 'necesidad -> negocios del modal',
    rubros VARCHAR(255) NULL COMMENT 'csv ids categoria permitidas; NULL=libre',
    franjas VARCHAR(60) NOT NULL DEFAULT '24' COMMENT 'csv manana,tarde,noche | 24',
    fecha_inicio DATE NULL,
    fecha_fin DATE NULL COMMENT 'NULL = infinito',
    enlace VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    orden INT NOT NULL DEFAULT 0,
    impresiones INT UNSIGNED NOT NULL DEFAULT 0,
    clics INT UNSIGNED NOT NULL DEFAULT 0,
    creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_act (activo),
    KEY idx_vigencia (fecha_inicio, fecha_fin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "OK  tabla directorio_banners\n";

$pdo->exec("CREATE TABLE IF NOT EXISTS directorio_banner_stats (
    banner_id INT UNSIGNED NOT NULL,
    fecha DATE NOT NULL,
    impresiones INT UNSIGNED NOT NULL DEFAULT 0,
    clics INT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (banner_id, fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
echo "OK  tabla directorio_banner_stats\n";

/* ---------- 2) Importar los 69 banners (opcional) ---------- */
if (isset($_GET['importar'])) {
    $filas = require __DIR__ . '/includes/datos_banners_69.php';
    $ok = 0; $dup = 0;
    foreach ($filas as $f) {
        [$imagen, $titulo, $tema, $franjas, $activo] = $f;
        // idempotente por imagen
        $s = $pdo->prepare("SELECT id FROM directorio_banners WHERE imagen=? LIMIT 1");
        $s->execute([$imagen]);
        if ($s->fetchColumn()) { $dup++; continue; }

        $s = $pdo->prepare("INSERT INTO directorio_banners
            (titulo, imagen, tema, franjas, activo, orden, creado_en)
            VALUES (?,?,?,?,?,0,NOW())");
        $s->execute([$titulo, $imagen, ($tema !== '' ? $tema : null), $franjas, (int)$activo]);
        $ok++;
    }
    echo "OK  importados $ok banners nuevos ($dup ya existían, sin duplicar)\n";
    echo "    Los 7 sociales + 4 'DECIDIR' entraron inactivos (activo=0).\n";
} else {
    echo "--  Para registrar los 69 banners añade &importar=1\n";
}

echo "</pre>";
echo '<p>✅ Módulo instalado. Gestiona los banners en <a href="' . url('superadmin.php?seccion=banners') . '">superadmin → 📢 Banners</a>.</p>';
echo '<p style="color:#b91c1c"><strong>⚠️ Borra este archivo del servidor al terminar.</strong></p>';
echo '</body>';
