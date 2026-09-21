<?php
/**
 * ============================================================
 *  cron/tasks/backup.php — TAREA: Backup automático de la BD
 *
 *  Hace un dump SQL de TODAS las tablas de la base conectada
 *  ($pdo) usando PDO puro (no depende de mysqldump), lo
 *  comprime en .sql.gz y lo guarda en la carpeta __backups/
 *  (protegida contra acceso web por su .htaccess).
 *
 *  Rotación: conserva las últimas BACKUP_KEEP copias.
 *
 *  Uso: cron_runner.php backup "CLAVE"
 * ============================================================
 */

/* ── Config ─────────────────────────────────────────────── */
const BACKUP_DIR   = __DIR__ . '/../../__backups'; // public_html/__backups
const BACKUP_KEEP  = 14;   // nº de copias a conservar
const BACKUP_LIMIT = 500000; // filas por tabla por dump (seguridad memoria)

/**
 * Devuelve un string resumen (requerido por cron_runner).
 * Firma: function() use ($pdo, $logger) { ... return $resumen; }
 */
return (function ($pdo, $logger) {
    $lines = [];
    $lines[] = '-- ===========================================================';
    $lines[] = '--  Backup Directorio de Negocios de Chimbote';
    $lines[] = '--  Fecha: ' . date('Y-m-d H:i:s');
    $lines[] = '--  BD:    ' . (defined('DB_NAME') ? DB_NAME : '?');
    $lines[] = '-- ===========================================================';
    $lines[] = 'SET NAMES utf8mb4;';
    $lines[] = 'SET FOREIGN_KEY_CHECKS = 0;';
    $lines[] = '';

    // Tablas de la base actual (las que "ve" la conexión)
    $tablas = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);

    if (empty($tablas)) {
        $logger('⚠ No se encontraron tablas.');
        return 'Sin tablas para respaldar.';
    }

    foreach ($tablas as $tabla) {
        // 1) Estructura
        $create = $pdo->query("SHOW CREATE TABLE `$tabla`")->fetch(PDO::FETCH_NUM);
        $lines[] = "-- ------------------------------------------------";
        $lines[] = "-- Estructura: $tabla";
        $lines[] = "-- ------------------------------------------------";
        $lines[] = "DROP TABLE IF EXISTS `$tabla`;";
        $lines[] = ($create[1] ?? '') . ';';
        $lines[] = '';

        // 2) Datos (todas las filas)
        $lines[] = "-- Datos: $tabla";
        $stmt = $pdo->query("SELECT * FROM `$tabla`");
        $rows = 0;
        while (($fila = $stmt->fetch(PDO::FETCH_NUM)) !== false) {
            $filaSql = array_map([$pdo, 'quote'], $fila);
            $lines[] = "INSERT INTO `$tabla` VALUES (" . implode(',', $filaSql) . ');';
            $rows++;
            if ($rows >= BACKUP_LIMIT) { $logger("  $tabla: límite ($rows filas)."); break; }
        }
        $logger("  ✓ $tabla: $rows filas");
        $lines[] = '';
    }

    $lines[] = 'SET FOREIGN_KEY_CHECKS = 1;';
    $sql = implode("\n", $lines);

    // Crear carpeta de backups si no existe
    if (!is_dir(BACKUP_DIR)) { mkdir(BACKUP_DIR, 0755, true); }

    $archivo = BACKUP_DIR . '/backup_' . date('Ymd_His') . '.sql.gz';
    $gz = @gzopen($archivo, 'wb6');
    if ($gz === false) {
        return 'ERROR: no se pudo crear ' . $archivo . ' (revisa permisos de ' . BACKUP_DIR . ').';
    }
    gzwrite($gz, $sql);
    gzclose($gz);
    $tam = round(filesize($archivo) / 1024, 1);
    $logger("💾 Backup guardado: $archivo ({$tam} KB)");

    // Rotación: borrar las más antiguas dejando BACKUP_KEEP
    $patron = BACKUP_DIR . '/backup_*.sql.gz';
    $copias = glob($patron);
    sort($copias); // ascendente = más antiguas primero
    if (count($copias) > BACKUP_KEEP) {
        $aBorrar = array_slice($copias, 0, count($copias) - BACKUP_KEEP);
        foreach ($aBorrar as $vieja) {
            @unlink($vieja);
            $logger("  🗑 rotación: borrado $vieja");
        }
    }

    return "OK: $tam KB | copias actuales: " . count(glob($patron));
})($pdo, $logger);
