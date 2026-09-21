<?php
/**
 * telegram_subs.php — AVISOS POR TELEGRAM A LOS VENDEDORES (suscripciones a los encargos)
 * =====================================================================================
 * Pedido del jefe (2026-09-15): *«también tiene que tener la opción de recibir notificaciones
 * en mi Telegram… que las notificaciones le lleguen a las personas a su Telegram, si es posible
 * le pones una cajita ahí para que pongan su usuario de Telegram y reciba las notificaciones»*.
 *
 * ⚠️ LA VERDAD TÉCNICA (por qué la caja NO pide el @usuario):
 *   Un bot de Telegram **no puede escribirle a alguien por su @usuario**: la API solo deja mandar
 *   mensajes a quien YA inició el chat con el bot. Por eso la caja hace lo que sí funciona:
 *   genera un **código**, muestra un botón que abre el bot (`t.me/Jimmychimbote_bot?start=CODIGO`)
 *   y, cuando la persona toca **START**, el webhook (`api/telegram_bot.php`) guarda su `chat_id`
 *   enlazado a ese código. Desde ahí sí recibe los encargos de su rubro y su zona, y se da de baja
 *   con `/stop`.
 *
 * El interés se guarda en la tabla `directorio_telegram_subs`:
 *   · `rubro_id`    → el rubro que le interesa (NULL = todos).
 *   · `distrito_id` → su zona (NULL = toda la provincia).
 * Con eso, `tg_subs_avisar_encargo()` avisa SOLO de lo que le sirve (decisión del jefe: «solo
 * encargos de su rubro y su zona»). Tope: 10 avisos al día por persona y uno por encargo.
 *
 * ⚠️ Todo en try/catch: si la tabla no existe o Telegram falla, el sitio sigue igual.
 */

if (!defined('TG_SUBS_TABLA'))   define('TG_SUBS_TABLA', 'directorio_telegram_subs');
if (!defined('TG_BOT_USUARIO'))  define('TG_BOT_USUARIO', 'Jimmychimbote_bot');
if (!defined('TG_SUBS_MAX_DIA')) define('TG_SUBS_MAX_DIA', 10);   // avisos/día por persona

/** ¿Existe la tabla? (una comprobación por petición) */
function tg_subs_ok() {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        db()->query('SELECT 1 FROM ' . TG_SUBS_TABLA . ' LIMIT 1');
        $ok = true;
    } catch (Throwable $e) {
        $ok = false;
    }
    return $ok;
}

/** Crea la tabla (solo el admin, o el instalador temporal `TG_SUBS_INSTALAR`). */
function tg_subs_instalar() {
    if (tg_subs_ok()) return ['ok' => true, 'msg' => '✔ La tabla de suscriptores ya estaba lista.'];
    if ((!function_exists('es_admin') || !es_admin()) && !defined('TG_SUBS_INSTALAR')) {
        return ['ok' => false, 'msg' => 'Solo el administrador puede crear la tabla de suscriptores.'];
    }
    try {
        db()->exec("CREATE TABLE IF NOT EXISTS " . TG_SUBS_TABLA . " (
            id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
            codigo           CHAR(16)     NOT NULL,
            chat_id          VARCHAR(32)  NULL,
            nombre           VARCHAR(80)  NULL,
            usuario_id       BIGINT UNSIGNED NULL,
            negocio_id       INT UNSIGNED NULL,
            rubro_id         INT UNSIGNED NULL,
            distrito_id      INT UNSIGNED NULL,
            activo           TINYINT(1)   NOT NULL DEFAULT 0,
            avisos_n         SMALLINT UNSIGNED NOT NULL DEFAULT 0,
            avisos_fecha     DATE         NULL,
            ultimo_pedido_id INT UNSIGNED NULL,
            ip               VARCHAR(45)  NULL,
            creado_en        DATETIME     NOT NULL,
            activado_en      DATETIME     NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_tg_codigo (codigo),
            KEY idx_tg_chat (chat_id),
            KEY idx_tg_interes (activo, rubro_id, distrito_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        return ['ok' => true, 'msg' => '✔ Tabla de suscriptores de Telegram lista.'];
    } catch (Throwable $e) {
        error_log('tg_subs_instalar: ' . $e->getMessage());
        return ['ok' => false, 'msg' => 'Error al crear la tabla: ' . $e->getMessage()];
    }
}

/** Código corto y único (el que viaja en el enlace del bot). */
function tg_subs_codigo() {
    try { $c = bin2hex(random_bytes(5)); } catch (Throwable $e) { $c = substr(md5(uniqid('tg', true)), 0, 10); }
    // Se comprueba que no choque (con 10 hex es prácticamente imposible, pero mejor saberlo).
    try {
        for ($i = 0; $i < 5; $i++) {
            $st = db()->prepare('SELECT COUNT(*) FROM ' . TG_SUBS_TABLA . ' WHERE codigo = ?');
            $st->execute([$c]);
            if (!(int)$st->fetchColumn()) return $c;
            $c = bin2hex(random_bytes(5));
        }
    } catch (Throwable $e) {}
    return $c;
}

/** El enlace que abre el bot con el código puesto (la persona solo toca START). */
function tg_subs_url($codigo) {
    return 'https://t.me/' . TG_BOT_USUARIO . '?start=' . rawurlencode((string)$codigo);
}

/**
 * Deja apuntado el interés de alguien y devuelve su código + el enlace del bot.
 * Todavía NO está activo: se activa cuando la persona toca START en Telegram.
 *
 * @param array $datos rubro_id · distrito_id · usuario_id · negocio_id · nombre
 * @return array{codigo:string,url:string}|null
 */
function tg_subs_pedir(array $datos = []) {
    if (!tg_subs_ok()) return null;
    $codigo = tg_subs_codigo();
    try {
        db()->prepare('INSERT INTO ' . TG_SUBS_TABLA . '
            (codigo, nombre, usuario_id, negocio_id, rubro_id, distrito_id, activo, ip, creado_en)
            VALUES (?,?,?,?,?,?,0,?,?)')
            ->execute([
                $codigo,
                mb_substr(trim((string)($datos['nombre'] ?? '')), 0, 80),
                !empty($datos['usuario_id']) ? (int)$datos['usuario_id'] : null,
                !empty($datos['negocio_id']) ? (int)$datos['negocio_id'] : null,
                !empty($datos['rubro_id']) ? (int)$datos['rubro_id'] : null,
                !empty($datos['distrito_id']) ? (int)$datos['distrito_id'] : null,
                mb_substr((string)(ip_real()), 0, 45),
                date('Y-m-d H:i:s'),
            ]);
        return ['codigo' => $codigo, 'url' => tg_subs_url($codigo)];
    } catch (Throwable $e) {
        error_log('tg_subs_pedir: ' . $e->getMessage());
        return null;
    }
}

/**
 * Engancha un chat_id a un código (lo llama el webhook cuando la persona toca START).
 * Devuelve la fila (con su rubro/zona) o null si el código no sirve o ya caducó.
 * Caducidad: 7 días (un código viejo que alguien encuentre no debe enganchar a nadie).
 */
function tg_subs_activar($codigo, $chat_id, $nombre = '') {
    if (!tg_subs_ok()) return null;
    $codigo  = preg_replace('/[^a-f0-9]/i', '', (string)$codigo);
    $chat_id = preg_replace('/[^0-9\-]/', '', (string)$chat_id);
    if ($codigo === '' || $chat_id === '') return null;
    try {
        $st = db()->prepare('SELECT * FROM ' . TG_SUBS_TABLA . ' WHERE codigo = ? LIMIT 1');
        $st->execute([$codigo]);
        $f = $st->fetch(PDO::FETCH_ASSOC);
        if (!$f) return null;
        if (strtotime((string)$f['creado_en']) < time() - 7 * 86400) return null;

        // Un mismo chat no debe quedar en dos filas (se desactivan las viejas).
        db()->prepare('UPDATE ' . TG_SUBS_TABLA . ' SET activo = 0 WHERE chat_id = ? AND codigo <> ?')
            ->execute([$chat_id, $codigo]);

        // ⚠️ El nombre se decide en PHP, NO con `IF(? <> '', …)` en el SQL: MariaDB revienta con
        //    «Illegal mix of collations (utf8mb4_general_ci,COERCIBLE) and (utf8mb4_unicode_ci,…)»
        //    al comparar el parámetro (collation de la conexión) con el literal '' (de la tabla).
        //    Comprobado el 2026-09-15 con `__tg_diag.php`; la activación fallaba en silencio.
        $nombre = mb_substr(trim((string)$nombre), 0, 80);
        if ($nombre !== '') {
            db()->prepare('UPDATE ' . TG_SUBS_TABLA . '
                SET chat_id = ?, activo = 1, activado_en = ?, nombre = ? WHERE codigo = ?')
                ->execute([$chat_id, date('Y-m-d H:i:s'), $nombre, $codigo]);
        } else {
            db()->prepare('UPDATE ' . TG_SUBS_TABLA . '
                SET chat_id = ?, activo = 1, activado_en = ? WHERE codigo = ?')
                ->execute([$chat_id, date('Y-m-d H:i:s'), $codigo]);
        }
        $f['chat_id'] = $chat_id;
        $f['activo']  = 1;
        return $f;
    } catch (Throwable $e) {
        error_log('tg_subs_activar: ' . $e->getMessage());
        return null;
    }
}

/** Baja voluntaria (`/stop`): deja de recibir avisos. Devuelve true si había algo que dar de baja. */
function tg_subs_baja($chat_id) {
    if (!tg_subs_ok()) return false;
    $chat_id = preg_replace('/[^0-9\-]/', '', (string)$chat_id);
    if ($chat_id === '') return false;
    try {
        $st = db()->prepare('UPDATE ' . TG_SUBS_TABLA . ' SET activo = 0 WHERE chat_id = ? AND activo = 1');
        $st->execute([$chat_id]);
        return $st->rowCount() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

/** Cuántas personas están suscritas (y cuántas esperando el START). */
function tg_subs_contar() {
    if (!tg_subs_ok()) return ['activos' => 0, 'pendientes' => 0];
    try {
        $f = db()->query('SELECT SUM(activo = 1) a, SUM(activo = 0) p FROM ' . TG_SUBS_TABLA)->fetch(PDO::FETCH_ASSOC);
        return ['activos' => (int)($f['a'] ?? 0), 'pendientes' => (int)($f['p'] ?? 0)];
    } catch (Throwable $e) {
        return ['activos' => 0, 'pendientes' => 0];
    }
}

/** El nombre del rubro/distrito de un interés (para los textos). */
function tg_subs_interes_txt($fila) {
    $partes = [];
    if (!empty($fila['rubro_id'])) {
        try {
            $st = db()->prepare('SELECT nombre, icono FROM directorio_categorias WHERE id = ? LIMIT 1');
            $st->execute([(int)$fila['rubro_id']]);
            if ($r = $st->fetch(PDO::FETCH_ASSOC)) $partes[] = trim((string)$r['icono'] . ' ' . (string)$r['nombre']);
        } catch (Throwable $e) {}
    }
    if (!empty($fila['distrito_id'])) {
        try {
            $st = db()->prepare('SELECT nombre FROM directorio_distritos WHERE id = ? LIMIT 1');
            $st->execute([(int)$fila['distrito_id']]);
            if ($d = $st->fetchColumn()) $partes[] = '📍 ' . (string)$d;
        } catch (Throwable $e) {}
    }
    return $partes ? implode(' · ', $partes) : 'todo Chimbote y la provincia';
}

/**
 * 🛒 AVISA A LOS SUSCRIPTORES DEL ENCARGO NUEVO.
 * Solo a quien le interesa: mismo rubro (o «todos») y misma zona (o «toda la provincia»).
 * Un aviso por persona y encargo, y como máximo TG_SUBS_MAX_DIA al día.
 *
 * @return int cuántos avisos se mandaron
 */
function tg_subs_avisar_encargo($pedido) {
    if (!$pedido || !tg_subs_ok() || (string)($pedido['tipo'] ?? '') === 'oculto') return 0;
    if ((string)($pedido['estado'] ?? 'abierto') !== 'abierto') return 0;
    $pid    = (int)($pedido['id'] ?? 0);
    $rubro  = !empty($pedido['rubro_id']) ? (int)$pedido['rubro_id'] : 0;
    $dist   = !empty($pedido['distrito_id']) ? (int)$pedido['distrito_id'] : 0;
    if ($pid <= 0) return 0;

    try {
        // Candidatos: activos, del rubro (o todos) y de la zona (o toda la provincia).
        $sql = "SELECT * FROM " . TG_SUBS_TABLA . "
            WHERE activo = 1 AND chat_id IS NOT NULL AND CHAR_LENGTH(chat_id) > 3
              AND (rubro_id IS NULL" . ($rubro > 0 ? " OR rubro_id = ?" : "") . ")
              AND (distrito_id IS NULL" . ($dist > 0 ? " OR distrito_id = ?" : "") . ")
              AND (ultimo_pedido_id IS NULL OR ultimo_pedido_id <> ?)
              AND (avisos_fecha IS NULL OR avisos_fecha < CURDATE() OR avisos_n < " . (int)TG_SUBS_MAX_DIA . ")
            LIMIT 200";
        $par = [];
        if ($rubro > 0) $par[] = $rubro;
        if ($dist > 0)  $par[] = $dist;
        $par[] = $pid;
        $st = db()->prepare($sql);
        $st->execute($par);
        $filas = $st->fetchAll(PDO::FETCH_ASSOC);
        if (!$filas) return 0;

        $enviados = 0;
        foreach ($filas as $f) {
            $texto = tg_subs_texto_encargo($pedido, $f);
            if (!function_exists('telegram_enviar')) continue;
            if (telegram_enviar((string)$f['chat_id'], $texto)) {
                $enviados++;
                db()->prepare('UPDATE ' . TG_SUBS_TABLA . '
                    SET ultimo_pedido_id = ?,
                        avisos_n = IF(avisos_fecha = CURDATE(), avisos_n + 1, 1),
                        avisos_fecha = CURDATE()
                    WHERE id = ?')->execute([$pid, (int)$f['id']]);
            }
        }
        return $enviados;
    } catch (Throwable $e) {
        error_log('tg_subs_avisar_encargo: ' . $e->getMessage());
        return 0;
    }
}

/** El mensaje que recibe el vendedor. Sin datos personales de nadie: solo el encargo y su enlace. */
function tg_subs_texto_encargo($pedido, $fila) {
    $termino = (string)($pedido['termino'] ?? '');
    $n       = (int)($pedido['buscado_n'] ?? 1);
    $tipo    = ((string)($pedido['tipo'] ?? 'sin_vendedor') === 'pocos') ? 'pocos' : 'sin_vendedor';
    $dist    = trim((string)($pedido['distrito_nombre'] ?? ''));
    $rubro   = trim((string)($pedido['rubro_nombre'] ?? ''));

    $L = [];
    $L[] = $tipo === 'sin_vendedor' ? '🛒 NADIE LO VENDE EN CHIMBOTE' : '🛒 POQUITOS LO TIENEN EN CHIMBOTE';
    $L[] = '';
    $L[] = '🔎 «' . $termino . '»';
    if ($dist !== '' || $rubro !== '') {
        $L[] = trim(($dist !== '' ? '📍 ' . $dist : '') . ($rubro !== '' ? ' · 🏷️ ' . $rubro : ''));
    }
    if ($n > 1) $L[] = '👥 Ya lo buscaron ' . $n . ' veces';
    $L[] = '';
    $L[] = '¿Lo tienes en tu stock? Ofrécelo y te escriben:';
    if (function_exists('pedido_url')) $L[] = '👉 ' . pedido_url((string)($pedido['slug'] ?? ''));
    $L[] = '';
    $L[] = 'Recibes esto porque sigues los encargos de ' . tg_subs_interes_txt($fila) . '.';
    $L[] = 'Para dejar de recibirlos, escribe /stop';
    return implode("\n", $L);
}
