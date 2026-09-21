<?php
/**
 * CRON — Reset de vistas cada 10 días (alternativa a evento MySQL)
 * ============================================================
 * El planificador de eventos MySQL está desactivado por defecto en
 * Hostinger compartido. Este script PHP hace lo mismo.
 *
 * CONFIGURAR EN CRON (cPanel/Hostinger):
 *   0 3 * * *  php /home/USUARIO/public_html/cron_reset_vistas.php
 *   (ejecuta todos los días a las 3:00 AM)
 *
 * Si el script no se ejecuta en CLI, llamarlo por web:
 *   https://chimbote-aldia.pe/cron_reset_vistas.php?key=CLAVE_SECRETA
 * Y protegerlo con .htaccess o un parámetro ?key=... secreto.
 * ============================================================
 */

// ----- CONFIGURACIÓN -----
// En producción, cargar desde un archivo de configuración separado (config.php)
// que NO esté en el repositorio público.
define('DB_HOST', 'localhost');
define('DB_NAME', 'u196269909_CHIMBOTEALDIA');
define('DB_USER', 'u196269909_ALDIACHIMBOTE');
define('DB_PASS', 'PON_AQUI_LA_CLAVE_DE_LA_BASE');

// Clave secreta si se llama por HTTP (no necesaria en CLI)
define('CRON_KEY', 'PON_AQUI_LA_CLAVE_INTERNA');

// Modo CLI (línea de comandos) o HTTP
$isCli = php_sapi_name() === 'cli';

// Verificar clave si es por HTTP
if (!$isCli) {
    $key = $_GET['key'] ?? '';
    if ($key !== CRON_KEY) {
        http_response_code(403);
        die('Acceso denegado.');
    }
    header('Content-Type: text/plain; charset=utf-8');
}

echo "[" . date('Y-m-d H:i:s') . "] Iniciando cron de reset de vistas...\n";

// ----- CONEXIÓN PDO -----
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "[OK] Conexión a BD establecida.\n";
} catch (PDOException $e) {
    echo "[ERROR] No se pudo conectar a la BD: " . $e->getMessage() . "\n";
    exit(1);
}

// ----- 1) RESET DE VISTAS (negocios con >= 10 días desde último reset) -----
try {
    $stmt = $pdo->prepare("
        UPDATE directorio_negocios
        SET vistas_count = 0,
            vistas_reset_fecha = CURRENT_DATE
        WHERE DATEDIFF(CURRENT_DATE, vistas_reset_fecha) >= 10
    ");
    $stmt->execute();
    $afectados = $stmt->rowCount();
    echo "[OK] Vistas reseteadas en {$afectados} negocio(s).\n";
} catch (PDOException $e) {
    echo "[ERROR] Falló reset de vistas: " . $e->getMessage() . "\n";
}

// ----- 2) LIMPIAR VISTAS ANTIGUAS (más de 30 días) -----
try {
    $stmt = $pdo->prepare("
        DELETE FROM directorio_vistas
        WHERE fecha < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    $afectados = $stmt->rowCount();
    echo "[OK] Vistas antiguas eliminadas: {$afectados} registro(s).\n";
} catch (PDOException $e) {
    echo "[ERROR] Falló limpieza de vistas: " . $e->getMessage() . "\n";
}

// ----- 3) LIMPIAR SESIONES EXPIRADAS -----
try {
    $stmt = $pdo->prepare("DELETE FROM directorio_sesiones WHERE expires_at < NOW()");
    $stmt->execute();
    $afectados = $stmt->rowCount();
    echo "[OK] Sesiones expiradas eliminadas: {$afectados}.\n";
} catch (PDOException $e) {
    echo "[ERROR] Falló limpieza de sesiones: " . $e->getMessage() . "\n";
}

// ----- 4) LIMPIAR HISTORIAL DE BÚSQUEDA ANTIGUO (>30 días) -----
try {
    $stmt = $pdo->prepare("
        DELETE FROM directorio_historial_busqueda
        WHERE fecha < DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute();
    $afectados = $stmt->rowCount();
    echo "[OK] Historial de búsqueda antiguo eliminado: {$afectados}.\n";
} catch (PDOException $e) {
    echo "[ERROR] Falló limpieza de historial: " . $e->getMessage() . "\n";
}

echo "[" . date('Y-m-d H:i:s') . "] Cron finalizado correctamente.\n";
exit(0);
