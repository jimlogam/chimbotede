<?php
/**
 * config.php — Configuración general del marketplace DeChimbote.com
 * ============================================================
 * ESTE ARCHIVO DEBE EDITARSE CON TUS CREDENCIALES REALES DE HOSTINGER.
 * No subas este archivo con credenciales reales a un repositorio git público.
 * ============================================================
 */

// ====== BASE DE DATOS (EDITAR CON TUS DATOS DE HOSTINGER) ======
define('DB_HOST', 'localhost');
define('DB_NAME', 'u196269909_CHIMBOTEALDIA');   // ⚠️ Cambiar
define('DB_USER', 'u196269909_ALDIACHIMBOTE');      // ⚠️ Cambiar
define('DB_PASS', 'PON_AQUI_LA_CLAVE_DE_LA_BASE');      // ⚠️ Cambiar
define('DB_CHARSET', 'utf8mb4');

// ====== CONFIGURACIÓN DEL SITIO ======
define('SITE_NAME', 'DeChimbote.com');
define('SITE_URL', 'https://dechimbote.com');  // ⚠️ Cambiar al dominio real
define('SITE_DESCRIPTION', 'El marketplace de Chimbote y la provincia del Santa');

// Zona horaria de Perú
date_default_timezone_set('America/Lima');

// ====== SESIONES ======
define('SESSION_LIFETIME', 60 * 60 * 24 * 30);  // 30 días (recuérdame)
define('SESSION_NAME', 'CHIMBOTE_SID');

// ====== SEGURIDAD ======
define('HASH_COST', 10);  // coste del bcrypt
define('CSRF_TOKEN_NAME', '_csrf');

// ====== PAGINACIÓN ======
define('POR_PAGINA_NEGOCIOS', 12);
define('POR_PAGINA_BUSCADOR', 24);
define('POR_PAGINA_PRODUCTOS', 50);

// ====== ALGORITMO DE VISIBILIDAD (del informe) ======
define('DIAS_RESET_VISTAS', 10);  // reset cada 10 días

// ====== ESTADOS ======
define('ESTADO_NEGOCIO_PENDIENTE', 'pendiente');
define('ESTADO_NEGOCIO_ACTIVO', 'activo');
define('ESTADO_NEGOCIO_INACTIVO', 'inactivo');
define('ESTADO_NEGOCIO_RECHAZADO', 'rechazado');

// ====== TIPOS DE USUARIO ======
define('USUARIO_CLIENTE', 'cliente');
define('USUARIO_DUENO', 'dueno');
define('USUARIO_ADMIN', 'admin');

// ====== DEBUG (DESHABILITAR EN PRODUCCIÓN) ======
error_reporting(E_ALL);
ini_set('display_errors', '0');  // ⚠️ Cambiar a '0' en producción
ini_set('log_errors', '1');

// ====== CONEXIÓN PDO (singleton) ======
function db() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,  // para LIMIT con ?
            ]);
        } catch (PDOException $e) {
            error_log('Error de conexión BD: ' . $e->getMessage());
            // 🔔 Aviso al jefe: la web se quedó sin base de datos (no depende de la BD para enviarse)
            // ⚠️ LAZO INFINITO (encontrado y corregido el 2026-09-13 al probar el módulo de noticias):
            //    `aviso()` consulta la base (config de avisos, dedupe, registro), así que si la base no
            //    responde, el aviso vuelve a entrar aquí y se llama otra vez… hasta colgar la petición
            //    (en la prueba local se quedó 7 minutos dando vueltas). Con esta marca se avisa UNA sola
            //    vez por petición: la segunda entrada ya no llama a `aviso()` y la web responde el error.
            static $aviso_bd_hecho = false;
            if (function_exists('aviso') && !$aviso_bd_hecho) {
                $aviso_bd_hecho = true;
                aviso('error_sitio', [
                    'mensaje'    => 'LA BASE DE DATOS NO RESPONDE: ' . mb_substr($e->getMessage(), 0, 140),
                    'archivo'    => 'config.php (conexión)',
                    'ruta'       => (string)($_SERVER['REQUEST_URI'] ?? ''),
                    'clave'      => 'err:bd:' . md5($e->getMessage()),
                    'dedupe_min' => 60,
                    'resumen'    => 'BD caída: ' . mb_substr($e->getMessage(), 0, 80),
                ]);
            }
            http_response_code(500);
            die('Lo sentimos, no se pudo conectar a la base de datos. Intenta más tarde.');
        }
    }
    return $pdo;
}

// ====== INICIAR SESIÓN ======
function iniciar_sesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_name(SESSION_NAME);
        session_start();
    }
}

// ====== IP REAL DEL VISITANTE (necesario con Cloudflare/CDN delante) ======
// Va ANTES que los helpers: todo el sitio (avisos, informes, antispam, visitas) la usa.
// 🛡️ CARGA DEFENSIVA (2026-09-16): si el archivo no estuviera disponible (subida a medias o
//    cuarentena del antivirus del hosting, que ya pasó una vez con PHP nuevos), el sitio NO se
//    cae: queda una red de seguridad que devuelve lo mismo que devolvía el código antes.
$__ip_real_motor = __DIR__ . '/includes/ip_real.php';
if (is_file($__ip_real_motor)) {
    require_once $__ip_real_motor;
}
unset($__ip_real_motor);
if (!function_exists('ip_real')) {
    /** Red de seguridad: sin el motor, se usa la IP de la conexión (como antes de Cloudflare). */
    function ip_real($bytes = 0) {
        $ip = trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
        return $bytes > 0 ? mb_substr($ip, 0, (int)$bytes) : $ip;
    }
}

// ====== AUTOLOAD DE HELPERS ======
require_once __DIR__ . '/includes/helpers.php';
