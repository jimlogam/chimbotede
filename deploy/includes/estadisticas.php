<?php
/**
 * estadisticas.php — Módulo de ESTADÍSTICAS / ANALYTICS de DeChimbote.com
 * ============================================================
 * Mide el tráfico real del sitio SIN depender de terceros (complementa GA4):
 *   - "En vivo": cuánta gente está ahora y en qué página.
 *   - Visitas, visitantes únicos, páginas vistas, tiempo de permanencia.
 *   - Páginas de entrada (por dónde llegan) y de salida (por dónde se van).
 *   - De qué enlace llegaron (referrer), dispositivo, horas pico, rutas.
 *   - Rankings por USUARIO: tiempo en línea, tiendas creadas, productos.
 *   - Última tienda creada y último producto agregado.
 *
 * Arquitectura (ligera, pensada para Hostinger compartido):
 *   - Cookie anónima `cz_stats` (32 hex, sin datos personales) creada por el
 *     servidor en cada página; identifica al "dispositivo/visitante".
 *   - El pageview se registra del lado del SERVIDOR (includes/header.php):
 *     1 fila en directorio_stats_eventos por página + 1 UPDATE en la sesión.
 *   - El tiempo de permanencia y el "ahora mismo" llegan por "latidos" (JS)
 *     a api/estadisticas.php cada 60 s y al cerrar/ocultar la pestaña.
 *   - Todas las fechas se generan en PHP (zona America/Lima, config.php) y se
 *     guardan/comparan como texto 'Y-m-d H:i:s': así el módulo funciona sin
 *     importar la zona horaria que tenga el servidor MySQL.
 *   - Ninguna función debe romper el sitio: todo va en try/catch.
 * ============================================================
 */

if (!defined('STATS_COOKIE'))         define('STATS_COOKIE', 'cz_stats');
if (!defined('STATS_VENTANA_MIN'))    define('STATS_VENTANA_MIN', 25);   // minutos de inactividad = sesión nueva
if (!defined('STATS_ONLINE_MIN'))     define('STATS_ONLINE_MIN', 3);     // "en línea ahora" (últimos 3 min)
if (!defined('STATS_MAX_DURACION'))   define('STATS_MAX_DURACION', 900); // tope segundos por latido (15 min)
if (!defined('STATS_DIAS_EVENTOS'))   define('STATS_DIAS_EVENTOS', 90);  // retención eventos (días)
if (!defined('STATS_DIAS_SESIONES'))  define('STATS_DIAS_SESIONES', 180);// retención sesiones (días)
if (!defined('STATS_EXCLUIR_ADMIN'))  define('STATS_EXCLUIR_ADMIN', true);// no contar al propio admin como tráfico

// ====== UTILIDADES ======

/** Ahora en hora local (America/Lima), formato MySQL DATETIME. */
function stats_ahora() {
    return date('Y-m-d H:i:s');
}

/** Límite de tiempo: hace N minutos en hora local. */
function stats_hace_min($min) {
    return date('Y-m-d H:i:s', time() - (int)$min * 60);
}

/** Respuesta JSON corta para el API (no rompe el JS). */
function stats_json($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function stats_tablas_ok() {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        $filas = db()->query("SHOW TABLES LIKE 'directorio_stats_%'")->fetchAll(PDO::FETCH_COLUMN);
        $ok = in_array('directorio_stats_sesiones', $filas, true)
           && in_array('directorio_stats_eventos', $filas, true);
    } catch (Throwable $e) {
        $ok = false;
    }
    return $ok;
}

function stats_es_bot($ua = null) {
    if ($ua === null) $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ua = mb_strtolower($ua);
    $bots = ['googlebot','bingbot','duckduckbot','baiduspider','yandex','semrush','ahrefsbot','dotbot',
             'petalbot','bytespider','gptbot','claudebot','ccbot','amazonbot','applebot','pingdom',
             'uptimerobot','facebookexternalhit','whatsapp','slackbot','discordbot','telegrambot',
             'linkedinbot','twitterbot','ia_archiver','mj12bot','sogou','exabot','crawler','spider','curl','python'];
    foreach ($bots as $b) {
        if (strpos($ua, $b) !== false) return true;
    }
    return false;
}

function stats_dispositivo($ua = null) {
    if ($ua === null) $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (preg_match('/iPad|Tablet|Kindle|Silk/i', $ua)) return 'tablet';
    if (preg_match('/Mobi|Android|iPhone|iPod|Opera Mini|IEMobile|BlackBerry/i', $ua)) return 'movil';
    return 'desktop';
}

/** Dominio "limpio" de un referrer (sin www / m.). '' = directo. */
function stats_ref_dominio($url) {
    if (!$url || !preg_match('#^https?://#i', $url)) return '';
    $host = strtolower((string)parse_url($url, PHP_URL_HOST));
    $host = preg_replace('/^www\.|^m\.|^l\.|^web\./', '', $host);
    return $host;
}

/** Nombre visible de la fuente para el dashboard. */
function stats_fuente_nombre($dominio) {
    $d = strtolower($dominio ?? '');
    if ($d === '') return 'Directo / escrito';
    if (strpos($d, 'google.') === 0 || $d === 'google.com') return 'Google';
    if (strpos($d, 'facebook') !== false || strpos($d, 'fb.com') !== false || strpos($d, 'm.me') !== false) return 'Facebook';
    if (strpos($d, 'whatsapp') !== false || strpos($d, 'wa.me') !== false) return 'WhatsApp';
    if (strpos($d, 'instagram') !== false) return 'Instagram';
    if (strpos($d, 'tiktok') !== false) return 'TikTok';
    if (strpos($d, 'youtube') !== false) return 'YouTube';
    if (strpos($d, 'bing') !== false || strpos($d, 'yahoo') !== false || strpos($d, 'duckduckgo') !== false || strpos($d, 'ecosia') !== false) return 'Otro buscador';
    if (strpos($d, 'dechimbote.com') !== false) return 'Interno';
    return $d;
}

/** Normaliza la ruta interna actual: devuelve [ruta_visible, tipo]. */
function stats_pagina_actual() {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $path = (string)parse_url($uri, PHP_URL_PATH);
    $path = rtrim($path, '/');
    if ($path === '' || $path === '/index.php' || $path === '/') {
        return ['/', 'inicio'];
    }
    if (preg_match('#^/neg/([^/]+)#', $path, $m)) return ['neg/' . $m[1], 'tienda'];
    if (preg_match('#^/negocio/([^/]+)#', $path, $m)) return ['neg/' . $m[1], 'tienda'];
    if (preg_match('#^/categoria/([^/]+)#', $path, $m)) return ['categoria/' . $m[1], 'categoria'];
    if (preg_match('#^/distrito/([^/]+)#', $path, $m)) return ['distrito/' . $m[1], 'distrito'];
    if ($path === '/buscar.php' || $path === '/buscar') return ['buscar.php', 'buscar'];
    if ($path === '/panel.php' || $path === '/perfil') return ['panel.php', 'panel'];
    if ($path === '/productos.php') return ['productos.php', 'productos'];
    if ($path === '/caminante' || strpos($path, '/caminante/') === 0) return ['caminante', 'caminante'];
    // 🗑️ 2026-09-13: aquí se clasificaban las páginas de mensajería entre tiendas (B2B) y el
    // chat público. Ese contenido se retiró del sitio, así que ya no se clasifica (si alguien
    // visita una URL vieja, ahora responde 404 y cae en «Otras»).
    if ($path === '/login.php') return ['login.php', 'login'];
    if ($path === '/registro.php') return ['registro.php', 'registro'];
    if ($path === '/crear_negocio.php' || $path === '/registrar_negocio.php') return ['registrar_tienda.php', 'registrar_tienda'];
    if ($path === '/reclamar_negocio.php') return ['reclamar_negocio.php', 'reclamar'];
    // /reclamar (y /reclamar.php) = la página "reclama tu negocio". Son las URLs que la
    // gente abre desde enlaces viejos: hasta el 2026-09-10 daban 404 y ahora se cuentan aquí.
    if ($path === '/reclamar' || $path === '/reclamar.php') return ['reclamar', 'reclamar'];
    if ($path === '/trabaja_con_nosotros.php') return ['trabaja_con_nosotros.php', 'trabaja'];
    $base = basename($path) ?: 'otro';
    return [$base, 'otro'];
}

/** Páginas que NUNCA se registran (el propio admin / migraciones / crons). */
function stats_pagina_excluida() {
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $path = (string)parse_url($uri, PHP_URL_PATH);
    // Cualquier página de error 404 (el ErrorDocument ejecuta /404.php aunque
    // REQUEST_URI siga siendo la URL rota original: no contarla como visita).
    if (basename($_SERVER['PHP_SELF'] ?? '') === '404.php') return true;
    if (preg_match('#/(superadmin\.php|migrar_|cron_|google_|registrar_vista|logout\.php|api/)#', $path)) return true;
    // Directorios internos (assets, includes) nunca deberían llegar aquí
    if (preg_match('#^/(assets|includes|img|video|audio|fotos)/#', $path)) return true;
    return false;
}

/** Lee la cookie anónima; si no existe la crea y la manda al navegador.
 *  Devuelve '' si no se pudo leer NI crear (p. ej. ya hay salida HTML). */
function stats_cookie() {
    $c = $_COOKIE[STATS_COOKIE] ?? '';
    if (preg_match('/^[a-f0-9]{32}$/', $c)) return $c;
    if (headers_sent()) return '';   // no se puede setear: no fabricar sesiones rotas
    $nueva = bin2hex(random_bytes(16));
    setcookie(STATS_COOKIE, $nueva, [
        'expires'  => time() + 60 * 60 * 24 * 400,
        'path'     => '/',
        'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => false,   // JS lo lee para los latidos
        'samesite' => 'Lax',
    ]);
    return $nueva;
}

/** Sesión "viva" de una cookie (actividad reciente). Trae ultimo_activo para
 *  poder sumar los segundos transcurridos desde PHP (no depende del reloj SQL). */
function stats_sesion_viva($cookie) {
    try {
        $stmt = db()->prepare("SELECT id, cookie, usuario_id, inicio, ultimo_activo, segundos
            FROM directorio_stats_sesiones
            WHERE cookie = ? AND ultimo_activo > ?
            ORDER BY id DESC LIMIT 1");
        stats_bind($stmt, [$cookie, stats_hace_min(STATS_VENTANA_MIN)]);
        $stmt->execute();
        $f = $stmt->fetch();
        return $f ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

/**
 * Registro del pageview (se llama en includes/header.php en cada página).
 * Inserta el evento y crea/actualiza la sesión de la cookie.
 */
function stats_registrar_visita() {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') return; // solo pageviews GET
    if (stats_es_bot()) return;
    if (stats_pagina_excluida()) return;
    // Solo navegadores reales: piden HTML (evita scripts/curl sin UA conocida)
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    if ($accept === '' || stripos($accept, 'text/html') === false) return;
    if (!stats_tablas_ok()) return;
    try {
        $usuario_actual = usuario_actual();
        if (STATS_EXCLUIR_ADMIN && !empty($usuario_actual['tipo']) && $usuario_actual['tipo'] === USUARIO_ADMIN) return;
        [$ruta, $tipo] = stats_pagina_actual();
        $cookie = stats_cookie();
        if ($cookie === '') return;
        $usuario_id = $usuario_actual['id'] ?? null;
        $disp = stats_dispositivo();
        $ref_url = $_SERVER['HTTP_REFERER'] ?? '';
        $ref_dom = '';
        if ($ref_url) {
            $rd = stats_ref_dominio($ref_url);
            // Referrer interno no cuenta como fuente externa
            if ($rd !== '' && strpos($rd, 'dechimbote.com') === false) {
                $ref_dom = mb_substr($rd, 0, 120);
                $ref_url = mb_substr($ref_url, 0, 500);
            } else {
                $ref_url = '';
            }
        }
        $pdo = db();
        $ahora = stats_ahora();
        $sesion = stats_sesion_viva($cookie);

        if ($sesion) {
            // El pageview NO suma segundos (eso lo hacen los latidos JS para
            // no duplicar). Solo refresca actividad y página actual.
            $upd = $pdo->prepare("UPDATE directorio_stats_sesiones
                SET ultimo_activo = ?, paginas = paginas + 1, salida = ?,
                    pagina_actual = ?, usuario_id = COALESCE(?, usuario_id)
                WHERE id = ?");
            stats_bind($upd, [$ahora, $ruta, $ruta, $usuario_id, $sesion['id']]);
            $upd->execute();
            $sesion_id = (int)$sesion['id'];
        } else {
            $ins = $pdo->prepare("INSERT INTO directorio_stats_sesiones
                (cookie, usuario_id, inicio, ultimo_activo, segundos, paginas, entrada, salida, pagina_actual, ref_dominio, ref_url, dispositivo)
                VALUES (?,?,?,?,0,1,?,?,?,?,?,?)");
            stats_bind($ins, [$cookie, $usuario_id, $ahora, $ahora, $ruta, $ruta, $ruta, $ref_dom, $ref_url, $disp]);
            $ins->execute();
            $sesion_id = (int)$pdo->lastInsertId();
        }

        $ev = $pdo->prepare("INSERT INTO directorio_stats_eventos (sesion_id, cookie, usuario_id, tipo, pagina, tipo_pagina, ref_dominio, fecha)
            VALUES (?,?,?, 'pv', ?, ?, ?, ?)");
        stats_bind($ev, [$sesion_id, $cookie, $usuario_id, $ruta, $tipo, $ref_dom, $ahora]);
        $ev->execute();
    } catch (Throwable $e) {
        // Nunca romper la página por un problema de estadísticas
        error_log('stats_registrar_visita: ' . $e->getMessage());
    }
}

/**
 * Latido / salida (desde api/estadisticas.php, vía JS).
 * Suma segundos reales de permanencia (el JS manda cuántos segundos visibles
 * transcurrieron) y refresca ultimo_activo para el "en vivo".
 * $pagina: página actual al momento del latido (para afinar "salida").
 */
function stats_latido($tipo, $dur, $pagina = '') {
    if (!stats_tablas_ok()) return false;
    $cookie = $_COOKIE[STATS_COOKIE] ?? '';
    if (!preg_match('/^[a-f0-9]{32}$/', $cookie)) return false;
    try {
        $sesion = stats_sesion_viva($cookie);
        if (!$sesion) return false;
        $dur = max(0, min((int)$dur, STATS_MAX_DURACION));
        $usuario_id = usuario_actual()['id'] ?? null;
        $ahora = stats_ahora();
        if ($tipo === 'salida' && $pagina !== '') {
            $pagina = mb_substr($pagina, 0, 190);
            $upd = db()->prepare("UPDATE directorio_stats_sesiones
                SET ultimo_activo = ?, segundos = segundos + ?, usuario_id = COALESCE(?, usuario_id), salida = ?, pagina_actual = ?
                WHERE id = ?");
            stats_bind($upd, [$ahora, $dur, $usuario_id, $pagina, $pagina, $sesion['id']]);
            $upd->execute();
        } else {
            $upd = db()->prepare("UPDATE directorio_stats_sesiones
                SET ultimo_activo = ?, segundos = segundos + ?, usuario_id = COALESCE(?, usuario_id)
                WHERE id = ?");
            stats_bind($upd, [$ahora, $dur, $usuario_id, $sesion['id']]);
            $upd->execute();
        }
        return true;
    } catch (Throwable $e) {
        error_log('stats_latido: ' . $e->getMessage());
        return false;
    }
}

/** Limpieza de datos viejos (se puede llamar desde el panel o cron). */
function stats_limpiar() {
    if (!stats_tablas_ok()) return [0, 0];
    try {
        $a = db()->prepare("DELETE FROM directorio_stats_eventos WHERE fecha < ?");
        stats_bind($a, [stats_hace_min(STATS_DIAS_EVENTOS * 24 * 60)]);
        $a->execute();
        $b = db()->prepare("DELETE FROM directorio_stats_sesiones WHERE ultimo_activo < ?");
        stats_bind($b, [stats_hace_min(STATS_DIAS_SESIONES * 24 * 60)]);
        $b->execute();
        return [$a->rowCount(), $b->rowCount()];
    } catch (Throwable $e) {
        error_log('stats_limpiar: ' . $e->getMessage());
        return [0, 0];
    }
}

/**
 * Crea las tablas de estadísticas (respaldo al migrar_estadisticas.php:
 * se puede ejecutar desde el Súper Admin sin subir archivos nuevos).
 * Devuelve array con mensajes [ok => bool, msg => string].
 */
function stats_instalar() {
    try {
        $pdo = db();
        $mensajes = [];
        $pdo->exec("CREATE TABLE IF NOT EXISTS directorio_stats_sesiones (
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
        $mensajes[] = 'Tabla directorio_stats_sesiones lista.';
        $pdo->exec("CREATE TABLE IF NOT EXISTS directorio_stats_eventos (
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
        $mensajes[] = 'Tabla directorio_stats_eventos lista.';
        return ['ok' => true, 'msg' => implode(' ', $mensajes)];
    } catch (Throwable $e) {
        error_log('stats_instalar: ' . $e->getMessage());
        return ['ok' => false, 'msg' => 'Error al crear tablas: ' . $e->getMessage()];
    }
}

// ======================================================================
// CONSULTAS PARA EL DASHBOARD (todas seguras: devuelven [] ante errores)
// ======================================================================

/**
 * Bind tipado de parámetros. Con PDO::ATTR_EMULATE_PREPARES=false, execute()
 * manda todo como string y los LIMIT ? / INTERVAL ? numéricos fallan en
 * MariaDB; por eso los enteros se bindean con PDO::PARAM_INT.
 */
function stats_bind($stmt, $params) {
    $i = 1;
    foreach ($params as $p) {
        if (is_int($p)) {
            $stmt->bindValue($i, $p, PDO::PARAM_INT);
        } elseif ($p === null) {
            $stmt->bindValue($i, null, PDO::PARAM_NULL);
        } else {
            $stmt->bindValue($i, (string)$p, PDO::PARAM_STR);
        }
        $i++;
    }
}

function stats_q($sql, $params = []) {
    try {
        $stmt = db()->prepare($sql);
        stats_bind($stmt, $params);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (Throwable $e) {
        error_log('stats_q: ' . $e->getMessage());
        return [];
    }
}

function stats_q1($sql, $params = []) {
    try {
        $stmt = db()->prepare($sql);
        stats_bind($stmt, $params);
        $stmt->execute();
        $v = $stmt->fetchColumn();
        return $v === false ? 0 : (int)$v;
    } catch (Throwable $e) {
        error_log('stats_q1: ' . $e->getMessage());
        return 0;
    }
}

/** KPIs de "hoy". */
function stats_kpis_hoy() {
    $desde = date('Y-m-d 00:00:00');
    return [
        'sesiones'   => stats_q1("SELECT COUNT(*) FROM directorio_stats_sesiones WHERE inicio >= ?", [$desde]),
        'visitantes' => stats_q1("SELECT COUNT(DISTINCT cookie) FROM directorio_stats_sesiones WHERE inicio >= ?", [$desde]),
        'pvs'        => stats_q1("SELECT COUNT(*) FROM directorio_stats_eventos WHERE tipo='pv' AND fecha >= ?", [$desde]),
        'segundos'   => stats_q1("SELECT COALESCE(SUM(segundos),0) FROM directorio_stats_sesiones WHERE inicio >= ?", [$desde]),
        'usuarios'   => stats_q1("SELECT COUNT(DISTINCT usuario_id) FROM directorio_stats_sesiones WHERE usuario_id IS NOT NULL AND inicio >= ?", [$desde]),
    ];
}

/** "En línea ahora": sesiones con actividad en los últimos N minutos. */
function stats_en_vivo($min = null) {
    $min = (int)($min ?: STATS_ONLINE_MIN);
    return stats_q("SELECT s.id, s.cookie, s.usuario_id, s.pagina_actual, s.dispositivo, s.ultimo_activo,
        u.nombre AS usuario_nombre
        FROM directorio_stats_sesiones s
        LEFT JOIN directorio_usuarios u ON u.id = s.usuario_id
        WHERE s.ultimo_activo > ?
        ORDER BY s.ultimo_activo DESC
        LIMIT ?", [stats_hace_min($min), 60]);
}

/** Serie por día para los últimos $dias días: sesiones, visitantes únicos, pvs. */
function stats_serie_dias($dias) {
    $desde = date('Y-m-d 00:00:00', strtotime("-" . ($dias - 1) . " days"));
    $filas = stats_q("SELECT DATE(fecha) AS d, COUNT(*) AS pvs FROM directorio_stats_eventos
        WHERE tipo='pv' AND fecha >= ? GROUP BY DATE(fecha) ORDER BY d ASC", [$desde]);
    $ses = stats_q("SELECT DATE(inicio) AS d, COUNT(*) AS sesiones, COUNT(DISTINCT cookie) AS visitantes
        FROM directorio_stats_sesiones WHERE inicio >= ? GROUP BY DATE(inicio)", [$desde]);
    $map = [];
    foreach ($filas as $f) $map[$f['d']] = ['pvs' => (int)$f['pvs'], 'sesiones' => 0, 'visitantes' => 0];
    foreach ($ses as $f) {
        if (!isset($map[$f['d']])) $map[$f['d']] = ['pvs' => 0];
        $map[$f['d']]['sesiones'] = (int)$f['sesiones'];
        $map[$f['d']]['visitantes'] = (int)$f['visitantes'];
    }
    $salida = [];
    for ($i = $dias - 1; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-$i days"));
        $r = $map[$d] ?? ['pvs' => 0, 'sesiones' => 0, 'visitantes' => 0];
        $salida[] = ['fecha' => date('d/m', strtotime($d)), 'pvs' => (int)$r['pvs'], 'sesiones' => (int)($r['sesiones'] ?? 0), 'visitantes' => (int)($r['visitantes'] ?? 0)];
    }
    return $salida;
}

/** Distribución por hora del día (0-23) en el rango. */
function stats_por_hora($dias) {
    $desde = date('Y-m-d 00:00:00', strtotime("-" . ($dias - 1) . " days"));
    $filas = stats_q("SELECT HOUR(fecha) AS h, COUNT(*) AS n FROM directorio_stats_eventos
        WHERE tipo='pv' AND fecha >= ? GROUP BY HOUR(fecha)", [$desde]);
    $out = array_fill(0, 24, 0);
    foreach ($filas as $f) $out[(int)$f['h']] = (int)$f['n'];
    return $out;
}

/** Top de páginas exactas (rutas) en el rango. */
function stats_top_rutas($dias, $top = 15) {
    $desde = date('Y-m-d 00:00:00', strtotime("-" . ($dias - 1) . " days"));
    $filas = stats_q("SELECT pagina, COUNT(*) AS n FROM directorio_stats_eventos
        WHERE tipo='pv' AND fecha >= ? GROUP BY pagina ORDER BY n DESC LIMIT ?", [$desde, (int)$top]);
    $total = stats_q1("SELECT COUNT(*) FROM directorio_stats_eventos WHERE tipo='pv' AND fecha >= ?", [$desde]);
    $out = [];
    foreach ($filas as $f) {
        $out[] = ['pagina' => $f['pagina'], 'n' => (int)$f['n'], 'pct' => $total ? round($f['n'] * 100 / $total, 1) : 0];
    }
    return $out;
}

/** Top por TIPO de página (inicio, tienda, buscar, login…). */
function stats_top_tipos($dias, $top = 12) {
    $desde = date('Y-m-d 00:00:00', strtotime("-" . ($dias - 1) . " days"));
    return stats_q("SELECT tipo_pagina, COUNT(*) AS n FROM directorio_stats_eventos
        WHERE tipo='pv' AND fecha >= ? GROUP BY tipo_pagina ORDER BY n DESC LIMIT ?", [$desde, (int)$top]);
}

/** Páginas por donde EMPIEZAN las sesiones (entrada). */
function stats_entradas($dias, $top = 12) {
    $desde = date('Y-m-d 00:00:00', strtotime("-" . ($dias - 1) . " days"));
    return stats_q("SELECT entrada, COUNT(*) AS n FROM directorio_stats_sesiones
        WHERE inicio >= ? AND entrada <> '' GROUP BY entrada ORDER BY n DESC LIMIT ?", [$desde, (int)$top]);
}

/** Última página conocida de cada sesión (aproximación de salida). */
function stats_salidas($dias, $top = 12) {
    $desde = date('Y-m-d 00:00:00', strtotime("-" . ($dias - 1) . " days"));
    return stats_q("SELECT salida, COUNT(*) AS n FROM directorio_stats_sesiones
        WHERE inicio >= ? AND salida <> '' GROUP BY salida ORDER BY n DESC LIMIT ?", [$desde, (int)$top]);
}

/** Fuentes externas (referrer) en el rango. */
function stats_fuentes($dias) {
    $desde = date('Y-m-d 00:00:00', strtotime("-" . ($dias - 1) . " days"));
    $filas = stats_q("SELECT ref_dominio, COUNT(*) AS n FROM directorio_stats_sesiones
        WHERE inicio >= ? GROUP BY ref_dominio ORDER BY n DESC", [$desde]);
    $agrup = [];
    foreach ($filas as $f) {
        $nom = stats_fuente_nombre($f['ref_dominio']);
        $agrup[$nom] = ($agrup[$nom] ?? 0) + (int)$f['n'];
    }
    arsort($agrup);
    $out = [];
    foreach ($agrup as $k => $v) $out[] = ['fuente' => $k, 'n' => $v];
    return $out;
}

/** Dispositivos en el rango. */
function stats_dispositivos($dias) {
    $desde = date('Y-m-d 00:00:00', strtotime("-" . ($dias - 1) . " days"));
    return stats_q("SELECT dispositivo, COUNT(*) AS n FROM directorio_stats_sesiones
        WHERE inicio >= ? GROUP BY dispositivo ORDER BY n DESC", [$desde]);
}

/** Transiciones de página dentro de cada sesión (muestra lo que más se navega). */
function stats_transiciones($dias, $top = 12) {
    $desde = date('Y-m-d 00:00:00', strtotime("-" . ($dias - 1) . " days"));
    // Tomamos los eventos más recientes del rango para no pesar el hosting
    $filas = stats_q("SELECT sesion_id, pagina FROM directorio_stats_eventos
        WHERE tipo='pv' AND sesion_id IS NOT NULL AND fecha >= ?
        ORDER BY id DESC LIMIT ?", [$desde, 8000]);
    $filas = array_reverse($filas);
    $pares = [];
    $prev = [];
    foreach ($filas as $f) {
        $sid = (int)$f['sesion_id'];
        if (isset($prev[$sid]) && $prev[$sid] !== $f['pagina']) {
            $clave = $prev[$sid] . ' → ' . $f['pagina'];
            $pares[$clave] = ($pares[$clave] ?? 0) + 1;
        }
        $prev[$sid] = $f['pagina'];
    }
    arsort($pares);
    $out = [];
    foreach (array_slice($pares, 0, $top, true) as $k => $v) {
        $out[] = ['ruta' => $k, 'n' => $v];
    }
    return $out;
}

// ====== RANKINGS POR USUARIO ======

/** Usuarios con más tiempo en línea (suma de segundos de sus sesiones). */
function stats_usuarios_tiempo($dias = 0, $top = 10) {
    $where = '';
    $params = [];
    if ($dias > 0) {
        $where = 'WHERE s.inicio >= ? AND s.usuario_id IS NOT NULL';
        $params[] = date('Y-m-d 00:00:00', strtotime("-" . ($dias - 1) . " days"));
    } else {
        $where = 'WHERE s.usuario_id IS NOT NULL';
    }
    return stats_q("SELECT u.id, u.nombre, u.email, u.tipo,
        SUM(s.segundos) AS segundos, COUNT(DISTINCT s.id) AS sesiones
        FROM directorio_stats_sesiones s
        INNER JOIN directorio_usuarios u ON u.id = s.usuario_id
        $where
        GROUP BY u.id ORDER BY segundos DESC LIMIT ?", array_merge($params, [(int)$top]));
}

/** Usuarios con más tiendas creadas (dueño de negocios). */
function stats_usuarios_tiendas($top = 10) {
    return stats_q("SELECT u.id, u.nombre, u.email, COUNT(n.id) AS tiendas
        FROM directorio_usuarios u
        INNER JOIN directorio_negocios n ON n.dueno_id = u.id
        GROUP BY u.id ORDER BY tiendas DESC, u.nombre ASC LIMIT ?", [(int)$top]);
}

/** Usuarios con más productos (productos de sus tiendas). */
function stats_usuarios_productos($top = 10) {
    return stats_q("SELECT u.id, u.nombre, u.email, COUNT(s.id) AS productos
        FROM directorio_usuarios u
        INNER JOIN directorio_negocios n ON n.dueno_id = u.id
        INNER JOIN directorio_servicios s ON s.negocio_id = n.id
        GROUP BY u.id ORDER BY productos DESC, u.nombre ASC LIMIT ?", [(int)$top]);
}

/** Últimas tiendas creadas (con dueño y link). */
function stats_ultimas_tiendas($n = 5) {
    return stats_q("SELECT n.id, n.nombre, n.slug, n.estado, n.creado_en, u.nombre AS dueno
        FROM directorio_negocios n LEFT JOIN directorio_usuarios u ON u.id = n.dueno_id
        ORDER BY n.id DESC LIMIT ?", [(int)$n]);
}

/** Últimos productos agregados (con su tienda). */
function stats_ultimos_productos($n = 5) {
    return stats_q("SELECT s.id, s.titulo, s.precio, s.activo, s.negocio_id, n.nombre AS tienda, n.slug AS tienda_slug
        FROM directorio_servicios s LEFT JOIN directorio_negocios n ON n.id = s.negocio_id
        ORDER BY s.id DESC LIMIT ?", [(int)$n]);
}

/** Búsquedas internas más usadas (historial de usuarios). */
function stats_busquedas($top = 12) {
    return stats_q("SELECT termino, COUNT(*) AS n FROM directorio_historial_busqueda
        WHERE termino IS NOT NULL AND termino <> ''
        GROUP BY termino ORDER BY n DESC LIMIT ?", [(int)$top]);
}

/** Formato corto de duración: 12m 5s / 3h 10m / 2d 4h. */
function stats_duracion_txt($seg) {
    $seg = (int)$seg;
    if ($seg < 60) return $seg . ' s';
    $min = intdiv($seg, 60);
    if ($min < 60) return $min . ' min';
    $h = intdiv($min, 60);
    $m = $min % 60;
    if ($h < 24) return $m ? "{$h} h {$m} min" : "{$h} h";
    $d = intdiv($h, 24);
    $hh = $h % 24;
    return $hh ? "{$d} d {$hh} h" : "{$d} d";
}

/** Etiqueta linda para tipos de página. */
function stats_tipo_etiqueta($t) {
    $map = [
        'inicio' => '🏠 Inicio', 'buscar' => '🔍 Buscar', 'tienda' => '🏬 Tienda',
        'categoria' => '📂 Categoría', 'distrito' => '📍 Distrito', 'login' => '🔑 Ingreso',
        'registro' => '📝 Registro', 'panel' => '🧑‍💼 Panel usuario', 'registrar_tienda' => '➕ Alta de tienda',
        'productos' => '📦 Mis productos', 'reclamar' => '🤝 Reclamar negocio', 'trabaja' => '💼 Trabaja con nosotros',
        'caminante' => '📸 Caminante',
        '404' => '⚠️ 404', 'otro' => '🧩 Otras',
    ];
    return $map[$t] ?? ucfirst($t);
}
