<?php
/**
 * explorer.php — 🧭 EL MOTOR DEL ÁREA «EXPLORER» (muro tipo Facebook con lo nuestro)
 * ==================================================================================
 * Pedido del jefe (2026-09-16, textual): *«clona esta página de Facebook y llámala a explorar,
 * usa los mismos colores, los mismos diseños, las mismas formas, pero usa mis tiendas y mis
 * productos para rellenar contenido por mis categorías, todos mis rubros, pon mis enlaces a mis
 * herramientas… quiero que mi sitio web en esta nueva área llamada Explorer sea lo más parecido a
 * la página de Facebook, colores, formas, buscador, y también en formato móvil.»*
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * QUÉ ES
 * ─────────────────────────────────────────────────────────────────────────────
 * `/explorer` es un **muro**: una columna central de **publicaciones** (como el muro de Facebook),
 * con la **tira de historias** arriba, el **compositor** («¿Qué estás pensando?»), la **columna
 * izquierda** con los accesos directos del sitio y la **columna derecha** con Publicidad y tiendas
 * sugeridas. En el celular se ve como la app: cabecera arriba, barra de iconos abajo.
 *
 * **Lo que se publica en el muro sale de la base de datos del sitio, no de un archivo de ejemplo:**
 *   · **Publicación de TIENDA** — cada tienda activa con foto (su portada + hasta 3 fotos de sus
 *     productos), su rubro, su distrito, las primeras palabras de su descripción y sus números
 *     reales (⭐ rating, 💬 opiniones, 👁 vistas, 🛒 pedidos).
 *   · **Publicación de PRODUCTO** — cada producto activo con foto, con su precio, su tienda y el
 *     botón **❤️ Me interesa** que es EL DEL CARRITO DEL SITIO (`assets/js/carrito.js`): el pedido
 *     sale por WhatsApp a la tienda, igual que en la portada (nada de un botón de mentira).
 *
 * **Los enlaces son los del sitio** (`GUIA_MAESTRA` §4): 🛠️ El maestro (`/crear-tienda`), 📸 El
 * caminante (`/caminante/`), 🥷 El ninja (el chat de ayuda), 🔎 el buscador, 🔴 En vivo, 📰 Noticias,
 * 💼 Empleos, 📍 Cerca de mí, 👤 el panel y 🛡️ el Súper Admin (solo si es el jefe).
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * LO QUE **NO** HACE (reglas del proyecto que se respetan aquí)
 * ─────────────────────────────────────────────────────────────────────────────
 *   · **No inventa números.** Los contadores que se ven son de la base (`directorio_opiniones`,
 *     `directorio_pedidos`, `vistas_count`, `rating`). Si una tabla no existe, el número simplemente
 *     no se pinta (nunca un dato falso).
 *   · **No inventa un «Me gusta» falso**: el botón 👍 Me gusta se recuerda **en el navegador del
 *     visitante** (localStorage, como el carrito) y no se presume de un total que no tenemos. Lo que
 *     sí es real y medible es ❤️ Me interesa (carrito → WhatsApp → `directorio_pedidos`) y 💬
 *     comentar, que **graba una OPINIÓN DE VERDAD** en la tienda (`api/reportar.php?que=opinar`),
 *     la misma que sale en la pestaña 💬 Opiniones de su ficha y que el jefe modera en el Súper Admin.
 *   · **Nunca un WhatsApp en blanco**: todos los botones pasan por `api/lead.php` con `c=1`, que
 *     arma el mensaje con contexto y el enlace del producto (regla del 2026-09-11 y del 2026-09-16).
 *   · **Nada de capturas**: aquí solo entran fotos y flyers del negocio (regla del 2026-09-12).
 *
 * Guía del módulo: `GUIA_EXPLORER.md` (raíz de `D:\RELAX`).
 */

if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config.php';
}

/** Publicaciones por tanda del muro. */
if (!defined('EXPLORER_POR_PAGINA')) define('EXPLORER_POR_PAGINA', 8);
/** Historias de la tira de arriba. */
if (!defined('EXPLORER_HISTORIAS'))   define('EXPLORER_HISTORIAS', 12);

// ============================================================================
// 0) 👍 ME GUSTA Y 💬 COMENTARIOS DE LAS PUBLICACIONES (2026-09-16, pedido del jefe)
// ============================================================================
/**
 * ⭐ LOS ME GUSTA CON LOS QUE ARRANCA CADA PUBLICACIÓN (orden del jefe, 2026-09-16, textual:
 * *«todas inician con me gusta al azar, entre 13 y 27 likes cada publicación al azar»*).
 *
 * Es un **número de arranque** (semilla) para que el muro no se vea vacío: sale **al azar entre 13 y
 * 27** y es **distinto en cada publicación**. Se calcula con el `crc32` de su clave, así que:
 *   · es **SIEMPRE EL MISMO** para esa publicación (no baila al recargar ni al bajar con el scroll),
 *   · **no ocupa nada en la base** (no hay miles de filas de relleno),
 *   · y a ese número se le **SUMAN los me gusta de verdad** (`directorio_explorer_likes`), que son los
 *     que sirven para saber qué le gusta a la gente.
 * El día que el jefe quiera solo números reales, se pone `EXPLORER_LIKES_SEMILLA` en `false`.
 */
if (!defined('EXPLORER_LIKES_SEMILLA')) define('EXPLORER_LIKES_SEMILLA', true);
if (!defined('EXPLORER_LIKES_MIN'))     define('EXPLORER_LIKES_MIN', 13);
if (!defined('EXPLORER_LIKES_MAX'))     define('EXPLORER_LIKES_MAX', 27);

/** Los me gusta «de arranque» de una publicación (13 a 27, estable y distinto en cada una). */
function explorer_likes_base($clave) {
    if (!EXPLORER_LIKES_SEMILLA) return 0;
    $min   = (int)EXPLORER_LIKES_MIN;
    $max   = max($min, (int)EXPLORER_LIKES_MAX);
    $rango = $max - $min + 1;
    return $min + (abs((int)crc32('explorer-likes-' . (string)$clave)) % $rango);
}

/**
 * El muro tiene **sus propias reacciones y sus propios comentarios**, separados de las OPINIONES de
 * la ficha de la tienda (que son otra cosa y siguen igual, con sus ⭐).
 *
 * Orden del jefe (textual): *«activa el botón me gusta pero no es lo mismo que el botón me interesa…
 * el botón me interesa nosotros lo usamos como un carrito de compras y esta página es más como una
 * página de exploración: si una persona le da me gusta es simplemente para ir conociendo qué tipo de
 * productos son los que le gustan, tal cual como lo hace Facebook, no los agregues a producto… en la
 * parte de comentarios activa un bloque de comentarios para que se pueda comentar las publicaciones
 * tal cual como hace Facebook y no le pongas estrellas».*
 *
 *   · 👍 **Me gusta** → se guarda en la base (tabla `directorio_explorer_likes`). **No agrega nada al
 *     pedido y no toca la opinión de la tienda**: es la señal de «esto me gusta», que es lo que sirve
 *     para saber qué busca la gente. Un visitante = **un me gusta por publicación** (clave única), así
 *     el número es de personas y no de clics.
 *   · 💬 **Comentar** → un comentario de la publicación (tabla `directorio_explorer_comentarios`),
 *     **sin estrellas**. El nombre sale de la sesión; si no hay sesión, se pone un apodo amable.
 *
 * Las dos tablas se **instalan solas** la primera vez que se usan: los `migrar_*.php` están bloqueados
 * por el antivirus del hosting, así que este es el patrón del sitio (`empleos_instalar_tabla()`).
 */
if (!defined('EXPLORER_LIKES_TABLA'))  define('EXPLORER_LIKES_TABLA', 'directorio_explorer_likes');
if (!defined('EXPLORER_COMEN_TABLA'))  define('EXPLORER_COMEN_TABLA', 'directorio_explorer_comentarios');

/** ¿Están las tablas del muro? (si no, el botón y los comentarios se pintan igual, pero sin números). */
function explorer_social_ok() {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        db()->query('SELECT 1 FROM ' . EXPLORER_LIKES_TABLA . ' LIMIT 1');
        db()->query('SELECT 1 FROM ' . EXPLORER_COMEN_TABLA . ' LIMIT 1');
        return $ok = true;
    } catch (Throwable $e) {
        return $ok = false;
    }
}

/** Instalación defensiva de las dos tablas (devuelve true si quedaron listas). */
function explorer_instalar_social() {
    if (explorer_social_ok()) return true;
    try {
        db()->exec("CREATE TABLE IF NOT EXISTS " . EXPLORER_LIKES_TABLA . " (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            post_tipo   CHAR(1)     NOT NULL DEFAULT 't',
            post_id     INT UNSIGNED NOT NULL,
            negocio_id  INT UNSIGNED NOT NULL,
            producto_id INT UNSIGNED NULL,
            visitante   VARCHAR(64) NOT NULL,
            usuario_id  BIGINT UNSIGNED NULL,
            ip          VARCHAR(45) NULL,
            fecha       DATETIME    NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY uq_exp_like (post_tipo, post_id, visitante),
            KEY idx_exp_like_post (post_tipo, post_id),
            KEY idx_exp_like_neg (negocio_id, fecha)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        db()->exec("CREATE TABLE IF NOT EXISTS " . EXPLORER_COMEN_TABLA . " (
            id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            post_tipo   CHAR(1)     NOT NULL DEFAULT 't',
            post_id     INT UNSIGNED NOT NULL,
            negocio_id  INT UNSIGNED NOT NULL,
            producto_id INT UNSIGNED NULL,
            autor       VARCHAR(60) NOT NULL DEFAULT 'Visitante',
            texto       TEXT        NOT NULL,
            visitante   VARCHAR(64) NOT NULL DEFAULT '',
            usuario_id  BIGINT UNSIGNED NULL,
            ip          VARCHAR(45) NULL,
            estado      VARCHAR(12) NOT NULL DEFAULT 'aprobado',
            fecha       DATETIME    NOT NULL,
            PRIMARY KEY (id),
            KEY idx_exp_com_post (post_tipo, post_id, id),
            KEY idx_exp_com_neg (negocio_id, fecha)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e) {
        error_log('explorer_instalar_social: ' . $e->getMessage());
        return false;
    }
    return explorer_social_ok();
}

/**
 * La identidad del visitante para el muro: **su cuenta** si tiene sesión, y si no una cookie propia
 * (`exp_vis`, un año). Con esto un me gusta es de UNA persona, no de un clic, y no hace falta que
 * nadie se registre para opinar (el sitio es de visitantes anónimos).
 */
function explorer_visitante_id() {
    $u = usuario_actual();
    if ($u) return 'u' . (int)$u['id'];

    $c = (string)($_COOKIE['exp_vis'] ?? '');
    if (!preg_match('/^[a-f0-9]{32}$/', $c)) {
        try { $c = bin2hex(random_bytes(16)); } catch (Throwable $e) { $c = md5(uniqid('', true)); }
        if (!headers_sent()) {
            setcookie('exp_vis', $c, [
                'expires'  => time() + 31536000,
                'path'     => '/',
                'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }
        $_COOKIE['exp_vis'] = $c;
    }
    return $c;
}

/** El apodo de quien comenta (su nombre si tiene sesión; si no, un apodo amable y sin datos). */
function explorer_autor_comentario() {
    $u = usuario_actual();
    if ($u) {
        $n = trim((string)($u['nombre'] ?? ''));
        if ($n !== '') {
            $p = preg_split('/\s+/u', $n);
            return mb_substr((string)($p[0] ?? $n), 0, 24);
        }
    }
    $apodos = ['Visitante', 'Vecino de Chimbote', 'Un cliente', 'Alguien del barrio', 'Un curioso'];
    return $apodos[abs(crc32(explorer_visitante_id())) % count($apodos)];
}

/** Parte una clave de publicación («t-1769» / «p-1234») en [tipo, id]. */
function explorer_clave_partir($clave) {
    if (!preg_match('/^([tp])-(\d+)$/', (string)$clave, $m)) return ['', 0];
    return [$m[1] === 'p' ? 'p' : 't', (int)$m[2]];
}

/**
 * Los me gusta y los comentarios de TODA la página, en dos consultas (nunca una por tarjeta).
 * @param  array $posts publicaciones ya preparadas (con su `clave`)
 * @return array [clave => ['n'=>int,'mio'=>bool,'n_com'=>int,'com'=>[...]]]
 */
function explorer_social_lote(array $posts) {
    $out = [];
    foreach ($posts as $p) {
        $out[$p['clave']] = ['n' => 0, 'mio' => false, 'n_com' => 0, 'com' => []];
    }
    if (!$posts || !explorer_social_ok()) return $out;

    $por_t = [];
    $por_p = [];
    foreach ($posts as $p) {
        [$tipo, $id] = explorer_clave_partir($p['clave']);
        if ($tipo === 't') $por_t[] = $id;
        elseif ($tipo === 'p') $por_p[] = $id;
    }
    $donde = [];
    if ($por_t) $donde[] = "(post_tipo = 't' AND post_id IN (" . implode(',', array_map('intval', $por_t)) . "))";
    if ($por_p) $donde[] = "(post_tipo = 'p' AND post_id IN (" . implode(',', array_map('intval', $por_p)) . "))";
    if (!$donde) return $out;
    $where = implode(' OR ', $donde);
    $yo = explorer_visitante_id();

    // ---- 1) los me gusta (contados y, de paso, si el mío está)
    try {
        $rs = db()->query("SELECT post_tipo, post_id, COUNT(*) AS n,
                                  SUM(visitante = " . db()->quote($yo) . ") AS mio
                             FROM " . EXPLORER_LIKES_TABLA . "
                            WHERE $where
                         GROUP BY post_tipo, post_id")->fetchAll();
        foreach ($rs as $r) {
            $k = $r['post_tipo'] . '-' . (int)$r['post_id'];
            if (!isset($out[$k])) continue;
            $out[$k]['n']   = (int)$r['n'];
            $out[$k]['mio'] = ((int)$r['mio'] > 0);
        }
    } catch (Throwable $e) {}

    // ---- 2) los comentarios (el total y los 2 últimos, que son los que se ven al abrir la caja)
    try {
        $rs = db()->query("SELECT id, post_tipo, post_id, autor, texto, fecha, estado
                             FROM " . EXPLORER_COMEN_TABLA . "
                            WHERE ($where) AND COALESCE(estado, 'aprobado') <> 'oculto'
                         ORDER BY id DESC")->fetchAll();
        foreach ($rs as $r) {
            $k = $r['post_tipo'] . '-' . (int)$r['post_id'];
            if (!isset($out[$k])) continue;
            $out[$k]['n_com']++;
            if (count($out[$k]['com']) < 2) $out[$k]['com'][] = $r;
        }
    } catch (Throwable $e) {}

    return $out;
}

/** Todos los comentarios de una publicación (lo pide el «Ver los N comentarios»). */
function explorer_comentarios_listar($tipo, $post_id, $limite = 60) {
    $tipo = ($tipo === 'p') ? 'p' : 't';
    $limite = max(1, min(200, (int)$limite));
    if (!explorer_social_ok()) return [];
    try {
        $st = db()->prepare("SELECT id, autor, texto, fecha FROM " . EXPLORER_COMEN_TABLA . "
                              WHERE post_tipo = ? AND post_id = ? AND COALESCE(estado, 'aprobado') <> 'oculto'
                           ORDER BY id ASC LIMIT " . $limite);
        $st->execute([$tipo, (int)$post_id]);
        return $st->fetchAll() ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * 👍 PRENDER O APAGAR EL ME GUSTA de una publicación: **solo eso** (nada de carrito, nada de opinión).
 * @return array{ok:bool,error?:string,n?:int,mio?:bool}
 */
function explorer_like_alternar($tipo, $post_id, $negocio_id = 0, $producto_id = 0) {
    $tipo = ($tipo === 'p') ? 'p' : 't';
    $post_id = (int)$post_id;
    if ($post_id <= 0) return ['ok' => false, 'error' => 'Publicación no válida.'];
    if (!explorer_instalar_social()) {
        return ['ok' => false, 'error' => 'Los me gusta todavía no están listos. Prueba en un momento.'];
    }
    $yo = explorer_visitante_id();
    $u  = usuario_actual();
    try {
        $st = db()->prepare('SELECT id FROM ' . EXPLORER_LIKES_TABLA . '
                              WHERE post_tipo = ? AND post_id = ? AND visitante = ? LIMIT 1');
        $st->execute([$tipo, $post_id, $yo]);
        $id = (int)$st->fetchColumn();

        if ($id > 0) {
            db()->prepare('DELETE FROM ' . EXPLORER_LIKES_TABLA . ' WHERE id = ?')->execute([$id]);
            $mio = false;
        } else {
            db()->prepare('INSERT INTO ' . EXPLORER_LIKES_TABLA . '
                           (post_tipo, post_id, negocio_id, producto_id, visitante, usuario_id, ip, fecha)
                           VALUES (?,?,?,?,?,?,?,?)')
                ->execute([$tipo, $post_id, (int)$negocio_id, ((int)$producto_id ?: null), $yo,
                           ($u ? (int)$u['id'] : null), mb_substr((string)(ip_real()), 0, 45),
                           date('Y-m-d H:i:s')]);
            $mio = true;
        }
        $st = db()->prepare('SELECT COUNT(*) FROM ' . EXPLORER_LIKES_TABLA . '
                              WHERE post_tipo = ? AND post_id = ?');
        $st->execute([$tipo, $post_id]);
        return ['ok' => true, 'n' => (int)$st->fetchColumn(), 'mio' => $mio];
    } catch (Throwable $e) {
        error_log('explorer_like_alternar: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'No se pudo guardar tu me gusta. Inténtalo otra vez.'];
    }
}

/**
 * 💬 ESCRIBIR UN COMENTARIO en una publicación (**sin estrellas**: las ⭐ son de las opiniones de la
 * ficha, que son otro módulo). Devuelve el comentario listo para pintar.
 */
function explorer_comentario_crear($tipo, $post_id, $texto, $negocio_id = 0, $producto_id = 0) {
    $tipo = ($tipo === 'p') ? 'p' : 't';
    $post_id = (int)$post_id;
    $texto = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$texto)));
    if ($post_id <= 0) return ['ok' => false, 'error' => 'Publicación no válida.'];
    if (mb_strlen($texto) < 2) return ['ok' => false, 'error' => 'Escribe tu comentario, por favor.'];
    if (mb_strlen($texto) > 800) $texto = mb_substr($texto, 0, 800);
    if (!explorer_instalar_social()) {
        return ['ok' => false, 'error' => 'Los comentarios todavía no están listos. Prueba en un momento.'];
    }

    $ip = mb_substr((string)(ip_real()), 0, 45);
    try {
        // 🚦 Un tope por IP al día, como las opiniones: así un robot no llena el muro.
        $st = db()->prepare('SELECT COUNT(*) FROM ' . EXPLORER_COMEN_TABLA . '
                              WHERE ip = ? AND fecha >= ?');
        $st->execute([$ip, date('Y-m-d 00:00:00')]);
        if ((int)$st->fetchColumn() >= 20) {
            return ['ok' => false, 'error' => 'Ya comentaste bastante hoy. Mañana puedes seguir.'];
        }
    } catch (Throwable $e) {}

    $autor = explorer_autor_comentario();
    $u = usuario_actual();
    try {
        db()->prepare('INSERT INTO ' . EXPLORER_COMEN_TABLA . '
                       (post_tipo, post_id, negocio_id, producto_id, autor, texto, visitante, usuario_id, ip, estado, fecha)
                       VALUES (?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([$tipo, $post_id, (int)$negocio_id, ((int)$producto_id ?: null), $autor, $texto,
                       explorer_visitante_id(), ($u ? (int)$u['id'] : null), $ip, 'aprobado', date('Y-m-d H:i:s')]);
        $id = (int)db()->lastInsertId();
    } catch (Throwable $e) {
        error_log('explorer_comentario_crear: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'No se pudo publicar tu comentario. Inténtalo otra vez.'];
    }

    // 🔔 El jefe se entera (mismo motor de avisos que todo el sitio; si el tipo no existe, no pasa nada)
    try {
        if (function_exists('aviso')) {
            $neg = '';
            if ((int)$negocio_id > 0) {
                $st = db()->prepare('SELECT nombre FROM directorio_negocios WHERE id = ? LIMIT 1');
                $st->execute([(int)$negocio_id]);
                $neg = (string)$st->fetchColumn();
            }
            aviso('explorer_comentario', [
                'negocio_id' => (int)$negocio_id,
                'resumen'    => $autor . ' comentó en el Explorer: «' . mb_substr($texto, 0, 70) . '»',
                'detalle'    => 'Tienda: ' . ($neg !== '' ? $neg : '—') . "\n" . $texto,
                'clave'      => 'expcom:' . $id,
                'dedupe_min' => 1,
            ]);
        }
    } catch (Throwable $e) {}

    return ['ok' => true, 'comentario' => [
        'id'     => $id,
        'autor'  => $autor,
        'texto'  => $texto,
        'fecha'  => 'Ahora mismo',
        'inicial'=> explorer_avatar_inicial($autor),
        'color'  => explorer_color_apodo($autor),
    ]];
}

/** El color del circulito del apodo (igual que en las opiniones: el mismo apodo, el mismo color). */
function explorer_color_apodo($autor) {
    $colores = ['#0866ff', '#f3425f', '#45bd62', '#f7b928', '#8b5cf6', '#0f766e', '#ea6a12'];
    return $colores[abs(crc32((string)$autor)) % count($colores)];
}

// ============================================================================
// 1) UTILIDADES PEQUEÑAS
// ============================================================================

/** ¿Existe una tabla? (para los contadores opcionales: si no está, no se pinta el número). */
function explorer_tabla_ok(string $tabla) {
    static $cache = [];
    $tabla = preg_replace('/[^a-z0-9_]/i', '', $tabla);
    if (isset($cache[$tabla])) return $cache[$tabla];
    try {
        db()->query('SELECT 1 FROM ' . $tabla . ' LIMIT 1');
        return $cache[$tabla] = true;
    } catch (Throwable $e) {
        return $cache[$tabla] = false;
    }
}

/** La dirección del Explorer (el archivo es `explorer.php`; el jefe lo llama así) con los filtros
 *  que se estén usando. */
function explorer_url(array $q = []) {
    $base = [
        'filtro' => $_GET['filtro'] ?? '',
        'cat'    => $_GET['cat'] ?? '',
        'zona'   => $_GET['zona'] ?? '',
        'q'      => $_GET['q'] ?? '',
    ];
    foreach ($q as $k => $v) { if ($v === null || $v === '') unset($base[$k]); else $base[$k] = $v; }
    $base = array_filter($base, static function ($v) { return $v !== '' && $v !== null; });
    return url('explorer.php') . ($base ? '?' . http_build_query($base) : '');
}

/** «hace 5 min» · «hace 3 h» · «hace 2 d» · «17/09» (SIEMPRE corto, para que entre en una sola fila).
 *
 *  ⚠️ LA FECHA ES CORTA A PROPÓSITO (orden del jefe, 2026-09-17): *«los formatos de fecha ponlos tipo
 *  "17/09" y nunca "17 de septiembre", para que todo entre en una sola fila»*. Antes, lo más viejo de
 *  una semana salía como «12 de septiembre» y partía la línea del muro en dos. */
function explorer_hace($fecha) {
    $t = strtotime((string)$fecha);
    if (!$t) return '';
    $seg = time() - $t;
    if ($seg < 0)        return 'Ahora mismo';
    if ($seg < 60)       return 'Ahora mismo';
    if ($seg < 3600)     return 'hace ' . floor($seg / 60) . ' min';
    if ($seg < 86400)    return 'hace ' . floor($seg / 3600) . ' h';
    if ($seg < 604800)   return 'hace ' . floor($seg / 86400) . ' d';
    return date('d/m', $t);          // «17/09» — nunca «17 de septiembre»
}

/** Recorte por PALABRAS (nunca corta a mitad de palabra). */
function explorer_recorte($texto, $palabras = 40) {
    $texto = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$texto)));
    if ($texto === '') return '';
    $p = preg_split('/\s+/u', $texto, -1, PREG_SPLIT_NO_EMPTY);
    if (count($p) <= $palabras) return $texto;
    return implode(' ', array_slice($p, 0, $palabras)) . '…';
}

/** Un avatar con la inicial (igual que los avisos de empleo del sitio). */
function explorer_avatar_inicial($nombre) {
    $nombre = trim((string)$nombre);
    if ($nombre === '') $nombre = 'D';
    return mb_strtoupper(mb_substr($nombre, 0, 1));
}

/** El nombre de quien está navegando ('' si es un visitante sin cuenta). */
function explorer_yo() {
    $u = usuario_actual();
    return $u ? trim((string)($u['nombre'] ?? '')) : '';
}

/** El nombre de pila, para saludar como en el muro («¿Qué estás pensando, Jimmy?»). */
function explorer_yo_corto() {
    $n = explorer_yo();
    if ($n === '') return '';
    $p = preg_split('/\s+/u', $n);
    return (string)($p[0] ?? $n);
}

/**
 * El nombre corto de una tienda: la primera parte antes del guion largo.
 * (Los nombres largos del directorio traen la descripción pegada: «Sra. Doris — Huevos, Frutas…».)
 */
function explorer_nombre_corto($nombre) {
    $partes = preg_split('/\s+[—–]\s+/u', (string)$nombre);
    $corto  = trim((string)($partes[0] ?? $nombre));
    return $corto !== '' ? $corto : (string)$nombre;
}

/** El enlace de WhatsApp de un botón del muro: SIEMPRE por api/lead.php (mide y da contexto). */
function explorer_wa_url($negocio_id, $producto_id = 0, $contexto = '') {
    $q = ['n' => (int)$negocio_id, 'c' => 1];
    if ((int)$producto_id > 0) $q['p'] = (int)$producto_id;
    if ($contexto !== '') $q['u'] = $contexto;
    return url('api/lead.php') . '?' . http_build_query($q);
}

/** La dirección del mensaje de una publicación (lo que se comparte por WhatsApp). */
function explorer_post_url($tipo, $id) {
    return url('explorer.php') . '?post=' . ($tipo === 'producto' ? 'p' : 't') . '-' . (int)$id;
}

// ============================================================================
// 2) LOS ICONOS (trazos propios, en el mismo espíritu del muro de Facebook)
// ============================================================================
/**
 * Los dibujos van en SVG y con `currentColor`, así el color lo pone el CSS (azul activo, gris
 * apagado) sin duplicar archivos. Son trazos sencillos dibujados para este módulo: ni un archivo
 * de imagen más que bajar.
 */
function explorer_icono($nombre, $clase = '') {
    $trazo = static function ($d, $extra = '') {
        return '<path d="' . $d . '"' . $extra . '/>';
    };
    $svg = static function ($contenido, $relleno = true) use ($clase) {
        return '<svg class="exi ' . e($clase) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false" '
             . ($relleno ? 'fill="currentColor"' : 'fill="none" stroke="currentColor" stroke-width="2" '
                . 'stroke-linecap="round" stroke-linejoin="round"') . '>' . $contenido . '</svg>';
    };

    switch ($nombre) {
        case 'inicio':      // 🏠
            return $svg($trazo('M12 2.6 1.8 11.3a1 1 0 0 0 .6 1.7h1.9V21a1 1 0 0 0 1 1h4.2v-5.7h3v5.7h4.2a1 1 0 0 0 1-1v-8h1.9a1 1 0 0 0 .6-1.7z'));
        case 'tiendas':     // 👥 dos personas (como «Amigos»)
            return $svg($trazo('M9 11.2a4.1 4.1 0 1 0 0-8.2 4.1 4.1 0 0 0 0 8.2zm0 2c-3.3 0-8.9 1.7-8.9 5v3.1h17.8v-3.1c0-3.3-5.6-5-8.9-5zm8.4-2.6a3.4 3.4 0 1 0 0-6.8 5.6 5.6 0 0 1 0 6.8zm2.2 2.9c1.9.6 3.9 1.8 3.9 3.6v3.2h2.5v-3.2c0-2.2-3.4-3.4-6.4-3.6z'));
        case 'rubros':      // 🗂️ grupo (tres personas)
            return $svg($trazo('M8 10.5a3.6 3.6 0 1 0 0-7.2 3.6 3.6 0 0 0 0 7.2zm8 0a3.1 3.1 0 1 0 0-6.2 3.1 3.1 0 0 0 0 6.2zm-8 1.9c-3 0-7.6 1.5-7.6 4.4v2.7h15.2v-2.7c0-2.9-4.6-4.4-7.6-4.4zm9.5.4c-.9 0-1.9.1-2.8.4 1.5 1 2.3 2.4 2.3 4v2.3H23v-2.3c0-2.6-3.5-4-6.5-4.4z'));
        case 'productos':   // 🛍️ tienda de Marketplace
            return $svg($trazo('M3.6 3.2h16.8l1.6 5.1a3.6 3.6 0 0 1-7 1.3 3.6 3.6 0 0 1-6 0 3.6 3.6 0 0 1-7-1.3zm1.9 8.9v7.5a1.2 1.2 0 0 0 1.2 1.2h12.6a1.2 1.2 0 0 0 1.2-1.2v-7.5a5.4 5.4 0 0 1-3.4-.3 5.7 5.7 0 0 1-6 0 5.4 5.4 0 0 1-3.4.3zm4.1 1.6h6.8v5.4H9.6z'));
        case 'historias':   // 🎬 video / historias
            return $svg($trazo('M3.2 4.6h17.6a1.6 1.6 0 0 1 1.6 1.6v11.6a1.6 1.6 0 0 1-1.6 1.6H3.2a1.6 1.6 0 0 1-1.6-1.6V6.2a1.6 1.6 0 0 1 1.6-1.6zm6.4 3.2v8.4l7.4-4.2z'));
        case 'campana':     // 🔔 avisos
            return $svg($trazo('M12 1.8a6.4 6.4 0 0 0-6.4 6.4v4.3L3.4 16a1 1 0 0 0 .9 1.5h15.4A1 1 0 0 0 20.6 16l-2.2-3.5V8.2A6.4 6.4 0 0 0 12 1.8zM9.3 19a2.7 2.7 0 0 0 5.4 0z'));
        case 'mensaje':     // 💬 Messenger
            return $svg($trazo('M12 2.1C6.4 2.1 2 6.3 2 11.5c0 2.9 1.4 5.4 3.7 7.1v3.3l3.4-1.9c.9.2 1.9.4 2.9.4 5.6 0 10-4.2 10-9.4S17.6 2.1 12 2.1zm1 12.4-2.6-2.7-5 2.7 5.5-5.8 2.6 2.7 4.9-2.7z'));
        case 'buscar':      // 🔎
            return $svg($trazo('M10.5 3a7.5 7.5 0 1 0 4.6 13.4l4.2 4.2 1.4-1.4-4.2-4.2A7.5 7.5 0 0 0 10.5 3zm0 2a5.5 5.5 0 1 1 0 11 5.5 5.5 0 0 1 0-11z'), false);
        case 'rejilla':     // ⠿ los nueve puntos
            $c = '';
            foreach ([4, 12, 20] as $x) { foreach ([4, 12, 20] as $y) { $c .= '<circle cx="' . $x . '" cy="' . $y . '" r="2.3"/>'; } }
            return $svg($c);
        case 'mas':         // ➕
            return $svg($trazo('M11 4h2v7h7v2h-7v7h-2v-7H4v-2h7z'));
        case 'me-gusta':    // 👍
            return $svg($trazo('M16.4 3.1c-.5-.5-1.4-.4-1.8.2l-.7 1c-1.1 1.6-1.7 3.5-1.7 5.4v.4H4.9c-1.1 0-1.9.9-1.9 1.9 0 .9.6 1.7 1.5 1.9l1.2 5.4c.2.9 1 1.5 1.9 1.5h7.6V7.3c0-.6-.3-1.2-.7-1.6zM18.1 10.6v10.4h2.6c.7 0 1.3-.6 1.3-1.3v-7.8c0-.7-.6-1.3-1.3-1.3z'));
        case 'corazon':     // ❤️
            return $svg($trazo('M12 21s-8.6-5.2-8.6-11A4.9 4.9 0 0 1 12 6.6 4.9 4.9 0 0 1 20.6 10c0 5.8-8.6 11-8.6 11z'));
        case 'comentar':    // 💬
            return $svg($trazo('M12 2.4C6.4 2.4 2 6.3 2 11.2c0 2.7 1.3 5.1 3.4 6.7l-1 3.3a.5.5 0 0 0 .6.6l3.7-1.3c1 .3 2.1.4 3.3.4 5.6 0 10-3.9 10-8.8s-4.4-9.7-10-9.7z'));
        case 'compartir':   // ➦
            return $svg($trazo('M13.8 2.6 22 10.8l-8.2 8.2v-5.2C7.4 13.6 4.2 16 2.2 20c0-7.4 4.4-11.4 11.6-12.2z'));
        case 'whatsapp':
            return $svg($trazo('M12 2a9.9 9.9 0 0 0-8.5 15L2 22l5.2-1.4A9.9 9.9 0 1 0 12 2zm5.3 14c-.2.6-1.3 1.2-1.8 1.2-.5 0-2.4-.2-4.6-2.1a9 9 0 0 1-2.5-3.3c-.2-.5-.6-1.6-.4-2.3.2-.7 1-1.4 1.4-1.4.2 0 .7-.1.9.5l.7 1.7c.1.2 0 .5-.1.6l-.4.5c-.1.2-.3.3-.1.6.7 1.2 1.6 1.9 2.7 2.4.3.1.4.1.6-.1l.6-.7c.2-.2.4-.2.6-.1l1.7.8c.3.1.4.5.4.7 0 .3 0 1.1-.1 1.4z'));
        case 'telefono':    // 📞
            return $svg($trazo('M6.6 2.9a2 2 0 0 0-2.7.3L2.6 4.9c-.9 1.1-.7 2.9.5 5.1a24 24 0 0 0 4.4 5.5 24 24 0 0 0 5.5 4.4c2.2 1.2 4 1.4 5.1.5l1.7-1.3a2 2 0 0 0 .3-2.7l-2.3-2.6a2 2 0 0 0-2.4-.5l-1.2.6a1 1 0 0 1-1.1-.1 15 15 0 0 1-3.6-3.6 1 1 0 0 1-.1-1.1l.6-1.2a2 2 0 0 0-.5-2.4z'));
        case 'ojo':         // 👁
            return $svg($trazo('M12 5c-5 0-9.3 3.2-11 7 1.7 3.8 6 7 11 7s9.3-3.2 11-7c-1.7-3.8-6-7-11-7zm0 3.4a3.6 3.6 0 1 1 0 7.2 3.6 3.6 0 0 1 0-7.2z'));
        case 'estrella':    // ★
            return $svg($trazo('M12 2.6l2.9 5.9 6.5.9-4.7 4.6 1.1 6.5-5.8-3-5.8 3 1.1-6.5L2.6 9.4l6.5-.9z'));
        case 'carrito':     // 🛒
            return $svg($trazo('M2 3h3.1l.7 2.6h15.6a1 1 0 0 1 1 1.3l-2.3 7.5a2 2 0 0 1-1.9 1.4H8.2a2 2 0 0 1-1.9-1.4L3.4 4.6A.5.5 0 0 0 2.9 4H2zm6.4 15.5a1.9 1.9 0 1 1 0 3.8 1.9 1.9 0 0 1 0-3.8zm9.2 0a1.9 1.9 0 1 1 0 3.8 1.9 1.9 0 0 1 0-3.8z'));
        case 'mundo':       // 🌐 (el «público» de las publicaciones)
            return $svg($trazo('M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm0 1.8c1.4 1.5 2.3 3.4 2.6 5.5H9.4A13 13 0 0 1 12 3.8zM3.9 9.3h3.4a14 14 0 0 0 .6 2.7H4.6a8 8 0 0 1-.7-2.7zm1.3 4.4h3.3c-.3 1-.5 1.9-.6 2.7H4.6a8 8 0 0 1 .6-2.7zM12 20.2a13 13 0 0 1-2.6-5.5h5.2A13 13 0 0 1 12 20.2zm3.1-9.4c.3-.9.5-1.8.6-2.7h3.4a8 8 0 0 1-.7 2.7zm.7 4.4c-.1-.8-.3-1.7-.6-2.7h3.3a8 8 0 0 1 .6 2.7z'));
        case 'noticias':    // 📰
            return $svg($trazo('M4 3.4h13.6a1.4 1.4 0 0 1 1.4 1.4v11.4h1.6v3a1.8 1.8 0 0 0 1.8-1.8V8h-2V4.8A1.4 1.4 0 0 0 19 3.4zm1.6 2.8h4.2v3.4H5.6zm0 5.2h9.4v1.6H5.6zm0 3h9.4v1.6H5.6z'));
        case 'empleo':      // 💼
            return $svg($trazo('M9.4 3.4h5.2a2 2 0 0 1 2 2v1.2h3.2a1.6 1.6 0 0 1 1.6 1.6v3H2.6v-3A1.6 1.6 0 0 1 4.2 6.6h3.2V5.4a2 2 0 0 1 2-2zm0 3.2h5.2V5.4H9.4zM2.6 13.4h6.2v1.4h6.4v-1.4h6.2v5.6a1.6 1.6 0 0 1-1.6 1.6H4.2a1.6 1.6 0 0 1-1.6-1.6z'));
        case 'vivo':        // 🔴 punto en vivo
            return $svg('<circle cx="12" cy="12" r="9"/>');
        case 'ubicacion':   // 📍
            return $svg($trazo('M12 2a7.4 7.4 0 0 0-7.4 7.4c0 5.5 7.4 12.6 7.4 12.6s7.4-7.1 7.4-12.6A7.4 7.4 0 0 0 12 2zm0 10a2.6 2.6 0 1 1 0-5.2 2.6 2.6 0 0 1 0 5.2z'));
        case 'ninja':
            return $svg($trazo('M12 2.2c-4.6 0-8.2 3.5-8.2 8 0 1.5.4 2.8 1.1 4l-1.5 5.2a.5.5 0 0 0 .6.6l5.3-1.4c.9.3 1.8.4 2.7.4 4.6 0 8.2-3.5 8.2-8.1s-3.6-8.7-8.2-8.7zM8.6 10.6a1.4 1.4 0 1 1 0 2.8 1.4 1.4 0 0 1 0-2.8zm6.8 0a1.4 1.4 0 1 1 0 2.8 1.4 1.4 0 0 1 0-2.8z'));
        case 'maestro':     // 🛠️
            return $svg($trazo('M21.7 5.3a5.6 5.6 0 0 1-7.3 7.3l-7.2 7.2a1.9 1.9 0 0 1-2.7-2.7l7.2-7.2a5.6 5.6 0 0 1 7.3-7.3L15.6 6l2.4 2.4z'));
        case 'camara':      // 📸
            return $svg($trazo('M9.2 3.4h5.6l1.3 1.9h3.1a2.2 2.2 0 0 1 2.2 2.2v10a2.2 2.2 0 0 1-2.2 2.2H4.8a2.2 2.2 0 0 1-2.2-2.2v-10a2.2 2.2 0 0 1 2.2-2.2h3.1zM12 8.4a4 4 0 1 0 0 8 4 4 0 0 0 0-8zm0 1.8a2.2 2.2 0 1 1 0 4.4 2.2 2.2 0 0 1 0-4.4z'));
        case 'panel':       // 👤
            return $svg($trazo('M12 3.4a4.3 4.3 0 1 1 0 8.6 4.3 4.3 0 0 1 0-8.6zm0 10.2c4.4 0 8 2.2 8 4.9v2.1H4v-2.1c0-2.7 3.6-4.9 8-4.9z'));
        case 'escudo':      // 🛡️
            return $svg($trazo('M12 2 4 5v7c0 4.6 3.4 8.6 8 10 4.6-1.4 8-5.4 8-10V5zm-1 13-3-3 1.4-1.4L11 12.2l4.6-4.6L17 9z'));
        case 'guardar':     // 🔖
            return $svg($trazo('M6 2.6h12a1.4 1.4 0 0 1 1.4 1.4v17.4L12 16.9l-7.4 4.5V4A1.4 1.4 0 0 1 6 2.6z'));
        case 'menu':        // ☰
            return $svg($trazo('M3 5h18v2.2H3zm0 6h18v2.2H3zm0 6h18v2.2H3z'));
        case 'cerrar':      // ✕
            return $svg($trazo('M5.6 4 12 10.4 18.4 4 20 5.6 13.6 12 20 18.4 18.4 20 12 13.6 5.6 20 4 18.4 10.4 12 4 5.6z'));
        case 'puntos':      // ⋯
            return $svg('<circle cx="5" cy="12" r="2.1"/><circle cx="12" cy="12" r="2.1"/><circle cx="19" cy="12" r="2.1"/>');
        case 'sonrisa':     // 🙂
            return $svg('<circle cx="12" cy="12" r="9.4" fill="none" stroke="currentColor" stroke-width="2"/>'
                      . '<circle cx="8.9" cy="10" r="1.4"/><circle cx="15.1" cy="10" r="1.4"/>'
                      . '<path d="M7.6 14.2a4.7 4.7 0 0 0 8.8 0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>');
        case 'flecha-mas':  // ➜ para «Ver más»
            return $svg($trazo('M11.3 4.6 12.7 6l-6 6h14.7v2H6.7l6 6-1.4 1.4L3.9 13z'));
        case 'enviar':      // ✈️ el avioncito de papel del «enviar comentario» (hace algo: publica)
            return $svg($trazo('M2.4 11.2 21 3.1c.6-.3 1.2.3 1 .9l-5.6 17.2c-.2.7-1.2.8-1.6.2l-3.3-4.6-4.4 3.2c-.6.4-1.4 0-1.3-.7l.4-4.7L2.2 12.4c-.6-.3-.5-1 .2-1.2z'));
        default:
            return $svg('<circle cx="12" cy="12" r="9"/>');
    }
}

// ============================================================================
// 3) LAS HERRAMIENTAS DEL SITIO (lo que se enlaza desde el Explorer)
// ============================================================================
/**
 * Cada opción es una herramienta REAL del sitio (no un adorno): su enlace está en la
 * `GUIA_MAESTRA` §4. `color` es el cuadrito de color del icono (como los accesos de Facebook).
 */
function explorer_herramientas() {
    $u = usuario_actual();
    $t = [
        ['k' => 'buscar',  'ico' => 'buscar',   'txt' => 'Explorar tiendas',      'sub' => 'Todas las tiendas de Chimbote y la provincia del Santa', 'url' => url('buscar.php'),        'color' => '#1877f2'],
        ['k' => 'prods',   'ico' => 'productos','txt' => 'Productos y ofertas',   'sub' => 'Lo que venden las tiendas, con su precio',               'url' => explorer_url(['filtro' => 'producto', 'post' => null]), 'color' => '#f7b928'],
        ['k' => 'rubros',  'ico' => 'rubros',   'txt' => 'Todos los rubros',      'sub' => 'Los rubros del directorio, de la A a la Z',              'url' => url('explorer') . '?panel=rubros', 'color' => '#45bd62'],
        ['k' => 'historias','ico'=> 'historias','txt' => 'Historias y flyers',    'sub' => 'Los flyers que subieron las tiendas',                    'url' => url('explorer') . '#historias',   'color' => '#f3425f'],
        ['k' => 'noticias','ico' => 'noticias', 'txt' => 'Noticias de Chimbote',  'sub' => 'Lo último de la zona, contado por nosotros',              'url' => url('noticias'),          'color' => '#0f766e'],
        ['k' => 'empleos', 'ico' => 'empleo',   'txt' => 'Empleos y anuncios',    'sub' => 'Quién está contratando hoy en Chimbote',                  'url' => url('empleos'),           'color' => '#6d071a'],
        ['k' => 'vivo',    'ico' => 'vivo',     'txt' => 'Información en vivo',   'sub' => 'Lo que se busca y nadie vende, ahora mismo',             'url' => url('en-vivo'),           'color' => '#e41e3f'],
        ['k' => 'cerca',   'ico' => 'ubicacion','txt' => 'Tiendas cerca de mí',   'sub' => 'Toca y comparte tu ubicación',                           'url' => url('buscar.php') . '?lat=&lng=&radio=auto', 'color' => '#f5533d'],
        ['k' => 'carrito', 'ico' => 'carrito',  'txt' => 'Mi pedido',             'sub' => 'Lo que marcaste con ❤️ Me interesa',                     'url' => '#', 'accion' => 'carrito',        'color' => '#00a400'],
        ['k' => 'maestro', 'ico' => 'maestro',  'txt' => 'Crear mi tienda con IA', 'sub' => 'El maestro: sube 8 fotos y arma tu tienda',              'url' => url('crear-tienda'),      'color' => '#ea6a12'],
        ['k' => 'caminante','ico'=> 'camara',   'txt' => 'Agregar tienda con la cámara', 'sub' => 'El caminante: 2 minutos, sin complicarte',        'url' => url('caminante/'),        'color' => '#7a5230'],
        // 🗑️ 2026-09-17: aquí iba «El ninja (chat de ayuda)». El chat ya no vive en todo el sitio
        // (es 🧭 El guía, el anfitrión de cada tienda, y solo se pinta en las fichas), así que su
        // entrada en «Tus herramientas» se retiró: no puede quedar un botón que no abra nada.
    ];
    if ($u) {
        $t[] = ['k' => 'panel', 'ico' => 'panel', 'txt' => 'Mi panel', 'sub' => 'Tus avisos, tus tiendas y lo que buscaste', 'url' => url('panel.php'), 'color' => '#123c6b'];
    } else {
        $t[] = ['k' => 'cuenta', 'ico' => 'panel', 'txt' => 'Crear mi cuenta gratis', 'sub' => 'Guarda tus favoritos y lo que buscas', 'url' => url('registro.php'), 'color' => '#123c6b'];
    }
    if (es_admin()) {
        $t[] = ['k' => 'admin', 'ico' => 'escudo', 'txt' => 'Súper Admin', 'sub' => 'Control total del sitio, avisos y monitoreo', 'url' => url('superadmin.php'), 'color' => '#4a0512'];
    }
    return $t;
}

/** Las herramientas que caben en la barra izquierda (las primeras) y las demás tras «Ver más». */
function explorer_herramientas_visibles($cuantas = 7) {
    $t = explorer_herramientas();
    return [array_slice($t, 0, $cuantas), array_slice($t, $cuantas)];
}

// ============================================================================
// 4) EL MURO: consultas
// ============================================================================
/**
 * Las publicaciones del muro, ya mezcladas (tiendas y productos por fecha) y paginadas.
 *
 * Se hace **en una sola consulta** (`UNION ALL` + `ORDER BY fecha DESC`) y no en PHP: la tabla de
 * productos tiene miles de filas y traerlas todas para ordenarlas en memoria sería tirar el servidor.
 *
 * @param array $o  pagina, filtro ('todo'|'tienda'|'producto'), cat (slug de rubro), zona (slug de distrito)
 * @return array{posts:array, hay_mas:bool}
 */
function explorer_feed(array $o = []) {
    $pagina   = max(1, (int)($o['pagina'] ?? 1));
    $filtro   = (string)($o['filtro'] ?? 'todo');
    $cat      = trim((string)($o['cat'] ?? ''));
    $zona     = trim((string)($o['zona'] ?? ''));
    $por      = max(1, (int)($o['por_pagina'] ?? EXPLORER_POR_PAGINA));
    $off      = ($pagina - 1) * $por;

    // ---- filtro por rubro (respeta los rubros MÚLTIPLES de una tienda, como el resto del sitio)
    $cat_id = 0;
    if ($cat !== '') {
        try {
            $st = db()->prepare('SELECT id FROM directorio_categorias WHERE slug = ? LIMIT 1');
            $st->execute([$cat]);
            $cat_id = (int)$st->fetchColumn();
        } catch (Throwable $e) { $cat_id = 0; }
    }
    $rubro_tie = $rubro_pro = '';
    if ($cat_id > 0) {
        if (rubros_multi_ok()) {
            $rubro_tie = ' AND (n.categoria_id = :c1 OR EXISTS (SELECT 1 FROM directorio_negocio_rubros rr
                           WHERE rr.negocio_id = n.id AND rr.categoria_id = :c2))';
            $rubro_pro = ' AND (n.categoria_id = :c3 OR EXISTS (SELECT 1 FROM directorio_negocio_rubros rr
                           WHERE rr.negocio_id = n.id AND rr.categoria_id = :c4))';
        } else {
            $rubro_tie = ' AND n.categoria_id = :c1';
            $rubro_pro = ' AND n.categoria_id = :c3';
        }
    }

    // ---- filtro por distrito
    $dist_id = 0;
    if ($zona !== '') {
        try {
            $st = db()->prepare('SELECT id FROM directorio_distritos WHERE slug = ? LIMIT 1');
            $st->execute([$zona]);
            $dist_id = (int)$st->fetchColumn();
        } catch (Throwable $e) { $dist_id = 0; }
    }
    $zona_tie = $zona_pro = '';
    if ($dist_id > 0) {
        $zona_tie = ' AND n.distrito_id = :z1';
        $zona_pro = ' AND n.distrito_id = :z3';
    }

    $cols = "n.id AS neg_id, n.nombre AS neg_nombre, n.slug AS neg_slug,
             n.rating, n.vistas_count, n.whatsapp, n.telefono, n.direccion,
             c.nombre AS cat_nombre, c.icono AS cat_icono, c.slug AS cat_slug,
             d.nombre AS distrito";

    $tie = "SELECT 'tienda' AS tipo, n.id AS pid, n.creado_en AS fecha,
                   n.nombre AS titulo, n.descripcion AS texto,
                   0 AS precio, '' AS unidad, $cols
              FROM directorio_negocios n
              LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
              LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
             WHERE n.estado = 'activo'
               AND EXISTS (SELECT 1 FROM directorio_fotos f WHERE f.negocio_id = n.id)"
             . $rubro_tie . $zona_tie;

    $pro = "SELECT 'producto' AS tipo, s.id AS pid, s.creado_en AS fecha,
                   s.titulo AS titulo, s.descripcion AS texto,
                   s.precio AS precio, s.unidad AS unidad, $cols
              FROM directorio_servicios s
              JOIN directorio_negocios n ON n.id = s.negocio_id
              LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
              LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
             WHERE s.activo = 1 AND n.estado = 'activo' AND " . sql_producto_vigente('s') . "
               AND ((s.imagen IS NOT NULL AND s.imagen <> '')
                    OR EXISTS (SELECT 1 FROM directorio_producto_fotos pf WHERE pf.producto_id = s.id))"
             . $rubro_pro . $zona_pro;

    if ($filtro === 'tienda')   $pro = '';
    if ($filtro === 'producto') $tie = '';

    if ($tie !== '' && $pro !== '') $sql = "($tie) UNION ALL ($pro)";
    elseif ($tie !== '')            $sql = $tie;
    else                            $sql = $pro;

    // ⚠️ Los marcadores se atan SOLO de las ramas que van en la consulta: si se colara el `:c1` de
    //    la rama de tiendas en una consulta que solo lleva productos, el PDO se queja
    //    («number of bound variables does not match number of tokens») y el muro saldría vacío.
    $par = [];
    if ($cat_id > 0) {
        if ($tie !== '') { $par[':c1'] = $cat_id; if ($rubros_multi_ok()) $par[':c2'] = $cat_id; }
        if ($pro !== '') { $par[':c3'] = $cat_id; if ($rubros_multi_ok()) $par[':c4'] = $cat_id; }
    }
    if ($dist_id > 0) {
        if ($tie !== '') $par[':z1'] = $dist_id;
        if ($pro !== '') $par[':z3'] = $dist_id;
    }

    // 🎲 EL ORDEN DEL MURO (pedido del jefe, 2026-09-16: *«cambia el orden para que no se vea siempre
    //    de la misma tienda»*). Antes iba por fecha, y como las tiendas y sus productos se cargan en
    //    tandas, salían pegadas 3 o 4 publicaciones de la MISMA tienda. Ahora el orden es **al azar
    //    pero estable dentro de la visita**: la semilla viaja aparte (la página se la pasa a la API
    //    del «ver más»), así el scroll sigue pidiendo las siguientes sin repetir ni saltarse ninguna,
    //    y cada visita ve un muro distinto. Se ordena por el MD5 de (tipo, id, semilla): es barato y
    //    NO se usa `RAND(semilla)` porque en MySQL ese `RAND` devuelve el mismo valor en todas las
    //    filas y el orden quedaría congelado.
    $semilla = (int)($o['semilla'] ?? 0);
    if ($semilla <= 0) {
        try { $semilla = random_int(1, 2000000000); } catch (Throwable $e) { $semilla = 20260916; }
    }
    $sql = "SELECT * FROM ($sql) AS m
             ORDER BY MD5(CONCAT(m.tipo, '-', m.pid, '-', " . $semilla . "))
             LIMIT " . ($por + 1) . " OFFSET " . $off;

    try {
        $st = db()->prepare($sql);
        $st->execute($par);
        $filas = $st->fetchAll() ?: [];
    } catch (Throwable $e) {
        error_log('explorer_feed: ' . $e->getMessage());
        return ['posts' => [], 'hay_mas' => false, 'error' => true, 'semilla' => $semilla];
    }

    $hay_mas = count($filas) > $por;
    if ($hay_mas) $filas = array_slice($filas, 0, $por);

    // 🔀 Y aunque el azar ya reparte, se remata el orden para que **no haya dos seguidas de la misma
    //    tienda** (el azar solo no lo garantiza): se va eligiendo la primera publicación cuya tienda
    //    no sea la que se acaba de pintar.
    $filas = explorer_mezclar_tiendas($filas);

    return ['posts' => explorer_preparar_posts($filas), 'hay_mas' => $hay_mas, 'semilla' => $semilla];
}

/**
 * 🔀 Separa las publicaciones de la misma tienda: devuelve la lista en el mismo conjunto pero sin dos
 * seguidas del mismo negocio (si al final solo quedan de la misma tienda, se acepta: es preferible
 * repetir a perder publicaciones).
 */
function explorer_mezclar_tiendas(array $filas) {
    $n = count($filas);
    if ($n < 3) return $filas;
    $resto = array_values($filas);
    $out   = [];
    $ultimo = -1;
    while ($resto) {
        $elegido = 0;
        foreach ($resto as $i => $f) {
            if ((int)$f['neg_id'] !== $ultimo) { $elegido = $i; break; }
        }
        $f = $resto[$elegido];
        array_splice($resto, $elegido, 1);
        $ultimo = (int)$f['neg_id'];
        $out[] = $f;
    }
    return $out;
}

/** Una sola publicación (para el enlace compartido: /explorer?post=p-1234). */
function explorer_post_uno($tipo, $id) {
    $tipo = ($tipo === 'producto') ? 'producto' : 'tienda';
    $id   = (int)$id;
    if ($id <= 0) return null;

    $cols = "n.id AS neg_id, n.nombre AS neg_nombre, n.slug AS neg_slug,
             n.rating, n.vistas_count, n.whatsapp, n.telefono, n.direccion,
             c.nombre AS cat_nombre, c.icono AS cat_icono, c.slug AS cat_slug,
             d.nombre AS distrito";

    if ($tipo === 'tienda') {
        $sql = "SELECT 'tienda' AS tipo, n.id AS pid, n.creado_en AS fecha, n.nombre AS titulo,
                       n.descripcion AS texto, 0 AS precio, '' AS unidad, $cols
                  FROM directorio_negocios n
                  LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                  LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
                 WHERE n.id = ? AND n.estado = 'activo' LIMIT 1";
    } else {
        $sql = "SELECT 'producto' AS tipo, s.id AS pid, s.creado_en AS fecha, s.titulo AS titulo,
                       s.descripcion AS texto, s.precio AS precio, s.unidad AS unidad, $cols
                  FROM directorio_servicios s
                  JOIN directorio_negocios n ON n.id = s.negocio_id
                  LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                  LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
                 WHERE s.id = ? AND s.activo = 1 AND n.estado = 'activo' LIMIT 1";
    }
    try {
        $st = db()->prepare($sql);
        $st->execute([$id]);
        $f = $st->fetch();
    } catch (Throwable $e) {
        return null;
    }
    if (!$f) return null;
    $posts = explorer_preparar_posts([$f]);
    return $posts[0] ?? null;
}

/**
 * Completa las publicaciones con lo que hace falta para pintarlas: fotos, opiniones y pedidos.
 * ⚠️ Todo va **en tandas** (una consulta por tabla para toda la página), nunca una consulta por
 * tarjeta: con 8 publicaciones eso sería 24 consultas por carga.
 */
function explorer_preparar_posts(array $filas) {
    if (!$filas) return [];

    $neg_ids  = [];
    $prod_ids = [];
    foreach ($filas as $f) {
        $neg_ids[(int)$f['neg_id']] = true;
        if ($f['tipo'] === 'producto') $prod_ids[(int)$f['pid']] = true;
    }
    $neg_ids  = array_keys($neg_ids);
    $prod_ids = array_keys($prod_ids);
    $in_neg   = $neg_ids ? implode(',', array_map('intval', $neg_ids)) : '0';
    $in_pro   = $prod_ids ? implode(',', array_map('intval', $prod_ids)) : '0';

    // ---- 1) Fotos de portada de cada tienda + fotos de los productos de esa tienda (la rejilla)
    $fotos_neg = [];
    try {
        $rs = db()->query("SELECT negocio_id, ruta FROM directorio_fotos
                            WHERE negocio_id IN ($in_neg) ORDER BY negocio_id ASC, orden ASC, id ASC")->fetchAll();
        foreach ($rs as $r) { $fotos_neg[(int)$r['negocio_id']][] = (string)$r['ruta']; }
    } catch (Throwable $e) {}

    $fotos_prod_neg = [];
    try {
        $rs = db()->query("SELECT s.negocio_id,
                                  COALESCE(NULLIF(s.imagen, ''),
                                           (SELECT pf.ruta FROM directorio_producto_fotos pf
                                             WHERE pf.producto_id = s.id ORDER BY pf.orden ASC LIMIT 1)) AS ruta
                             FROM directorio_servicios s
                            WHERE s.negocio_id IN ($in_neg) AND s.activo = 1
                            ORDER BY s.negocio_id ASC, s.destacado DESC, s.id DESC")->fetchAll();
        foreach ($rs as $r) {
            $ruta = (string)($r['ruta'] ?? '');
            if ($ruta === '') continue;
            $fotos_prod_neg[(int)$r['negocio_id']][] = $ruta;
        }
    } catch (Throwable $e) {}

    // ---- 2) La foto (y la galería) de cada producto publicado
    $fotos_prod = [];
    try {
        $rs = db()->query("SELECT id,
                                  NULLIF(imagen, '') AS imagen,
                                  (SELECT pf.ruta FROM directorio_producto_fotos pf
                                    WHERE pf.producto_id = directorio_servicios.id
                                    ORDER BY pf.orden ASC LIMIT 1) AS galeria
                             FROM directorio_servicios WHERE id IN ($in_pro)")->fetchAll();
        foreach ($rs as $r) {
            $id = (int)$r['id'];
            $fotos_prod[$id] = [];
            foreach ([$r['imagen'], $r['galeria']] as $ruta) {
                $ruta = (string)$ruta;
                if ($ruta !== '' && !in_array($ruta, $fotos_prod[$id], true)) $fotos_prod[$id][] = $ruta;
            }
        }
        // Las otras fotos de la galería (hasta 3 más), para que una publicación de producto pueda
        // salir con varias fotos como las de Facebook.
        if ($prod_ids) {
            $rs = db()->query("SELECT producto_id, ruta FROM directorio_producto_fotos
                                WHERE producto_id IN ($in_pro) ORDER BY producto_id ASC, orden ASC")->fetchAll();
            foreach ($rs as $r) {
                $id = (int)$r['producto_id']; $ruta = (string)$r['ruta'];
                if ($ruta === '' || in_array($ruta, $fotos_prod[$id] ?? [], true)) continue;
                if (count($fotos_prod[$id]) >= 4) continue;
                $fotos_prod[$id][] = $ruta;
            }
        }
    } catch (Throwable $e) {}

    // ---- 3) Los números REALES: opiniones y pedidos
    $n_opi = [];
    if (explorer_tabla_ok('directorio_opiniones')) {
        try {
            $rs = db()->query("SELECT negocio_id, COUNT(*) AS n FROM directorio_opiniones
                                WHERE negocio_id IN ($in_neg) GROUP BY negocio_id")->fetchAll();
            foreach ($rs as $r) { $n_opi[(int)$r['negocio_id']] = (int)$r['n']; }
        } catch (Throwable $e) {}
    }
    $n_ped = [];
    $n_lla = [];
    if (explorer_tabla_ok('directorio_pedidos')) {
        try {
            $rs = db()->query("SELECT negocio_id, COUNT(*) AS n FROM directorio_pedidos
                                WHERE negocio_id IN ($in_neg) GROUP BY negocio_id")->fetchAll();
            foreach ($rs as $r) { $n_ped[(int)$r['negocio_id']] = (int)$r['n']; }
        } catch (Throwable $e) {}
        try {
            $rs = db()->query("SELECT negocio_id, COUNT(*) AS n FROM directorio_pedidos
                                WHERE negocio_id IN ($in_neg) AND tipo = 'llamada' GROUP BY negocio_id")->fetchAll();
            foreach ($rs as $r) { $n_lla[(int)$r['negocio_id']] = (int)$r['n']; }
        } catch (Throwable $e) {}
    }

    // ---- 4) Armar la publicación
    $out = [];
    foreach ($filas as $f) {
        $neg_id  = (int)$f['neg_id'];
        $prod_id = ($f['tipo'] === 'producto') ? (int)$f['pid'] : 0;

        if ($f['tipo'] === 'producto') {
            $fotos = $fotos_prod[$prod_id] ?? [];
        } else {
            // La publicación de una tienda: su portada + las fotos de sus productos (máx. 4), que es
            // lo que la hace parecer una publicación con varias fotos (como las del muro de Facebook).
            $fotos = [];
            $portada = $fotos_neg[$neg_id][0] ?? '';
            if ($portada !== '') $fotos[] = $portada;
            foreach (array_slice($fotos_neg[$neg_id] ?? [], 1, 3) as $r) $fotos[] = $r;
            foreach ($fotos_prod_neg[$neg_id] ?? [] as $r) {
                if (count($fotos) >= 4) break;
                if (!in_array($r, $fotos, true)) $fotos[] = $r;
            }
        }

        $f['fotos']      = array_slice($fotos, 0, 4);
        $f['avatar']     = ($f['tipo'] === 'producto')
                            ? (($fotos_neg[$neg_id][0] ?? '') ?: ($f['fotos'][0] ?? ''))
                            : ($f['fotos'][0] ?? '');
        $f['n_opi']      = $n_opi[$neg_id] ?? 0;
        $f['n_ped']      = $n_ped[$neg_id] ?? 0;
        $f['n_llamadas'] = $n_lla[$neg_id] ?? 0;
        $f['clave']      = ($f['tipo'] === 'producto' ? 'p' : 't') . '-' . (int)$f['pid'];
        $f['permalink']  = explorer_post_url($f['tipo'], (int)$f['pid']);
        $out[] = $f;
    }

    // ---- 5) 👍 Los me gusta y 💬 los comentarios de TODA la página (dos consultas, no una por tarjeta)
    $social = explorer_social_lote($out);
    foreach ($out as $i => $p) {
        $s = $social[$p['clave']] ?? ['n' => 0, 'mio' => false, 'n_com' => 0, 'com' => []];
        // ⭐ Los de arranque (13 a 27, al azar por publicación, ver `explorer_likes_base()`) + los de
        //    VERDAD que ya tiene en la base. Así el muro no arranca vacío y los reales se siguen sumando.
        $base = explorer_likes_base($p['clave']);
        $out[$i]['like_base']   = $base;
        $out[$i]['like_real']   = (int)$s['n'];
        $out[$i]['n_likes']     = $base + (int)$s['n'];
        $out[$i]['like_mio']    = (bool)$s['mio'];
        $out[$i]['n_coment']    = (int)$s['n_com'];
        $out[$i]['comentarios'] = $s['com'];
    }
    return $out;
}

// ============================================================================
// 5) EL MURO: pintado de una publicación
// ============================================================================

/** Un avatar: la foto de la tienda o, si no hay, su inicial en un círculo de color. */
function explorer_avatar_html($ruta, $nombre, $clase = '') {
    if (trim((string)$ruta) !== '') {
        return img_tag($ruta, $nombre, ['class' => 'exp-av__img ' . $clase, 'sizes' => '48px']);
    }
    $colores = ['#1877f2', '#f3425f', '#45bd62', '#f7b928', '#8b5cf6', '#0f766e'];
    $c = $colores[abs(crc32((string)$nombre)) % count($colores)];
    return '<span class="exp-av__txt ' . e($clase) . '" style="background:' . $c . '">'
         . e(explorer_avatar_inicial($nombre)) . '</span>';
}

/**
 * 👤 El avatar de QUIEN NAVEGA (o el «entra a tu cuenta»): si no hay sesión NO se pone una letra —
 * una letra suelta parecería el perfil de alguien—, se pone el muñequito gris con el que Facebook
 * dice «aquí entras tú». Con sesión, la inicial de su nombre.
 */
function explorer_mi_avatar_html() {
    $yo = explorer_yo();
    if ($yo !== '') return explorer_avatar_html('', $yo);
    return '<span class="exp-av__vacio">' . explorer_icono('panel') . '</span>';
}

/**
 * La fila de acciones de una publicación: **👍 Me gusta · 💬 Comentar · ➦ Compartir** (las tres de
 * Facebook, con el significado de Facebook).
 *
 * ⚠️ **AQUÍ NO HAY CARRITO** (orden del jefe, 2026-09-16): el ❤️ «Me interesa» es del **carrito de
 * compras** del sitio y se queda en la ficha y en la portada; el Explorer es una página para
 * **explorar**, así que su botón es un **me gusta de verdad** (queda en `directorio_explorer_likes`
 * para saber qué le gusta a la gente) y **no agrega nada al pedido**.
 */
function explorer_acciones_html(array $p) {
    $clave = (string)$p['clave'];
    $likes = (int)($p['n_likes'] ?? 0);
    $mio   = !empty($p['like_mio']);
    $n_com = (int)($p['n_coment'] ?? 0);

    $h  = '<div class="exp-acciones">';

    // ---- 👍 ME GUSTA
    $h .= '<button type="button" class="exp-accion' . ($mio ? ' is-on' : '') . '"'
        . ' data-ex-like="' . e($clave) . '" aria-pressed="' . ($mio ? 'true' : 'false') . '"'
        . ' title="' . ($mio ? 'Quitar mi me gusta' : 'Me gusta') . '">'
        . explorer_icono('me-gusta')
        . '<span data-ex-like-txt>' . ($mio ? 'Te gusta' : 'Me gusta') . '</span>'
        . '<b class="exp-accion__n" data-ex-like-n' . ($likes > 0 ? '' : ' hidden') . '>'
        . ($likes > 0 ? number_format($likes) : '') . '</b>'
        . '</button>';

    // ---- 💬 COMENTAR
    $h .= '<button type="button" class="exp-accion" data-ex-comentar="' . e($clave) . '">'
        . explorer_icono('comentar') . '<span>Comentar</span>'
        . '<b class="exp-accion__n"' . ($n_com > 0 ? '' : ' hidden') . ' data-ex-com-n>'
        . ($n_com > 0 ? number_format($n_com) : '') . '</b>'
        . '</button>';

    // ---- ➦ COMPARTIR (el menú del celular y, si no lo hay, WhatsApp con el enlace del post)
    $h .= '<button type="button" class="exp-accion" data-ex-compartir="' . e($p['permalink']) . '" '
        . 'data-ex-titulo="' . e(explorer_nombre_corto($p['neg_nombre'])) . '">'
        . explorer_icono('compartir') . '<span>Compartir</span></button>';

    $h .= '</div>';
    return $h;
}

/** El bloque de comentarios de una publicación (lo que se abre al tocar 💬 Comentar). */
function explorer_comentarios_bloque_html(array $p, $nombre_tienda) {
    $clave = (string)$p['clave'];
    $n     = (int)($p['n_coment'] ?? 0);
    $hay   = count($p['comentarios'] ?? []);

    $h  = '<div class="exp-coment" data-ex-coment data-post="' . e($clave) . '" hidden>';
    $h .= '<div class="exp-coment__lista" data-ex-coment-lista>';

    // Los 2 últimos, del más viejo al más nuevo (como el muro)
    foreach (array_reverse($p['comentarios'] ?? []) as $c) {
        $h .= explorer_comentario_html($c['autor'] ?? 'Visitante', $c['texto'] ?? '', $c['fecha'] ?? '');
    }
    $h .= '</div>';

    if ($n > $hay) {
        $h .= '<button type="button" class="exp-coment__ver" data-ex-ver-coment="' . e($clave) . '">'
            . 'Ver los ' . number_format($n) . ' comentarios</button>';
    }

    // La caja de escribir: avatar + campo + el botón de enviar (el avioncito de papel, que SÍ hace algo)
    $h .= '<form class="exp-coment__form" data-ex-comentar-form data-post="' . e($clave) . '">'
        . '<div class="exp-av exp-av--chico">' . explorer_mi_avatar_html() . '</div>'
        . '<input type="text" class="exp-coment__campo" name="texto" maxlength="800" autocomplete="off" '
        . 'placeholder="Escribe un comentario…" aria-label="Escribe un comentario">'
        . '<button type="submit" class="exp-coment__enviar" aria-label="Publicar comentario" title="Publicar comentario">'
        . explorer_icono('enviar') . '</button>'
        . '</form>'
        . '<p class="exp-coment__nota">Tu comentario se publica al instante. Si no tienes sesión, sale con un '
        . 'apodo (nunca tu nombre ni tu teléfono).</p>';

    if ((int)$p['n_opi'] > 0) {
        $h .= '<a class="exp-coment__ver" href="' . e(url_negocio($p['neg_slug'])) . '">'
            . '⭐ Ver las ' . number_format((int)$p['n_opi']) . ' opiniones de la tienda en su ficha</a>';
    }

    $h .= '</div>';
    return $h;
}

/** Una publicación del muro (tienda o producto). Es el corazón del Explorer. */
function explorer_post_html(array $p, $destacada = false) {
    $neg_id  = (int)$p['neg_id'];
    $prod_id = ($p['tipo'] === 'producto') ? (int)$p['pid'] : 0;
    $nombre  = explorer_nombre_corto($p['neg_nombre']);
    $wa      = explorer_wa_url($neg_id, $prod_id, $p['permalink']);

    // El texto de la publicación (los primeros ~48 caracteres de la descripción, con «Ver más»).
    if ($p['tipo'] === 'producto') {
        $texto  = trim((string)$p['texto']);
        $corto  = explorer_recorte($texto, 32);
    } else {
        $texto  = trim((string)$p['texto']);
        $corto  = explorer_recorte($texto, 44);
    }
    $largo  = mb_strlen($corto) < mb_strlen(trim(preg_replace('/\s+/u', ' ', strip_tags($texto))));

    $clave = ($p['tipo'] === 'producto' ? 'p' : 't') . '-' . (int)$p['pid'];
    $h  = '<article class="exp-post' . ($destacada ? ' exp-post--sola' : '') . '" id="post-' . e($p['clave']) . '"'
        . ' data-ex-post="' . e($p['clave']) . '"'
        . ' data-neg="' . $neg_id . '"'
        . ' data-prod="' . $prod_id . '">';

    // ---------- cabecera ----------
    $h .= '<header class="exp-post__cabeza">';
    $h .= '<a class="exp-av" href="' . e(url_negocio($p['neg_slug'])) . '" title="' . e($p['neg_nombre']) . '">'
        . explorer_avatar_html($p['avatar'], $nombre) . '</a>';
    $h .= '<div class="exp-post__quien">';
    $h .= '<a class="exp-post__nombre" href="' . e(url_negocio($p['neg_slug'])) . '">' . e($nombre) . '</a>';
    $h .= '<p class="exp-post__meta">';
    if ($p['tipo'] === 'producto') {
        $h .= '<span class="exp-post__tipo">🛍️ Publicó un producto</span>';
    } else {
        $h .= '<span class="exp-post__tipo">' . e($p['cat_icono'] ?? '🏪') . ' '
            . e($p['cat_nombre'] ?? 'Tienda') . '</span>';
    }
    $h .= ' · ' . e(explorer_hace($p['fecha']));
    $h .= ' · ' . explorer_icono('mundo', 'exp-post__mundo');
    $h .= '</p>';
    if (!empty($p['distrito'])) {
        $h .= '<p class="exp-post__zona">📍 ' . e($p['distrito']) . '</p>';
    }
    $h .= '</div>';

    // ---------- el menú ⋯ (acciones reales, no decorativas) ----------
    $h .= '<div class="exp-menu" data-ex-menu>'
        . '<button type="button" class="exp-menu__btn" data-ex-menu-btn aria-label="Más opciones">'
        . explorer_icono('puntos') . '</button>'
        . '<div class="exp-menu__caja" hidden>'
        .   '<a class="exp-menu__it" href="' . e(url_negocio($p['neg_slug'])) . '">' . explorer_icono('productos')
        .     '<span>Ver la tienda</span></a>'
        .   '<a class="exp-menu__it" href="' . e($wa) . '" target="_blank" rel="noopener">' . explorer_icono('whatsapp')
        .     '<span>Escribirle por WhatsApp</span></a>'
        .   '<button type="button" class="exp-menu__it" data-ex-guardar="' . $neg_id . '" data-ex-neg-slug="' . e($p['neg_slug']) . '">'
        .     explorer_icono('guardar') . '<span>Guardar en mi lista</span></button>'
        .   '<button type="button" class="exp-menu__it exp-menu__it--x" data-ex-ocultar="' . e($p['clave']) . '">'
        .     explorer_icono('cerrar') . '<span>No me interesa</span></button>'
        . '</div></div>';
    $h .= '</header>';

    // ---------- texto ----------
    $h .= '<div class="exp-post__cuerpo">';
    if ($p['tipo'] === 'producto') {
        $h .= '<h3 class="exp-post__titulo">' . e($p['titulo']) . '</h3>';
        $h .= '<p class="exp-post__precio">' . e(formato_precio($p['precio']));
        if (!empty($p['unidad']) && $p['unidad'] !== 'unidad') $h .= ' <span>· ' . e($p['unidad']) . '</span>';
        $h .= '</p>';
    }
    if ($corto !== '') {
        $h .= '<p class="exp-post__texto" data-ex-texto>' . e($corto) . '</p>';
        if ($largo) {
            $h .= '<button type="button" class="exp-post__mas" data-ex-mas '
                . 'data-ex-completo="' . e(explorer_recorte($texto, 400)) . '">Ver más</button>';
        }
    }

    // ---------- fotos ----------
    if ($p['fotos']) {
        $n = count($p['fotos']);
        // ⚖️ EL ANCHO QUE SE PIDE A CADA FOTO: con UNA foto el hueco es toda la columna (660 px de
        //    escritorio, la pantalla entera en el celular) y con VARIAS cada hueco es la mitad. Si se
        //    pidiera siempre el ancho grande, el navegador se bajaría la versión de 800 px para
        //    cuadros de 330 px (medido: casi el triple de bytes por foto). `img_tag` elige la versión
        //    con esto, así que la rejilla baja la de 480 y la foto sola la de 800.
        $sizes = ($n === 1) ? '(max-width: 860px) 100vw, 660px' : '(max-width: 860px) 50vw, 330px';
        $h .= '<div class="exp-fotos exp-fotos--' . $n . '">';
        $i = 0;
        foreach ($p['fotos'] as $ruta) {
            $i++;
            $clase = 'exp-fotos__f exp-fotos__f--' . $i;
            $enlace = ($p['tipo'] === 'producto')
                    ? url_producto($prod_id)
                    : url_negocio($p['neg_slug']);
            $h .= '<a class="' . $clase . '" href="' . e($enlace) . '" title="' . e($p['titulo']) . '">'
                . img_tag($ruta, $p['titulo'], ['sizes' => $sizes, 'class' => 'exp-fotos__img'])
                . '</a>';
        }
        $h .= '</div>';
    }

    // ---------- los números REALES ----------
    $likes = (int)$p['n_likes'];
    $mio   = !empty($p['like_mio']);
    $h .= '<div class="exp-post__numeros">';
    // 👍 A la izquierda, como en el muro: quién le dio me gusta (el número es de PERSONAS: una por
    //    visitante, así nadie infla el contador a clics).
    $h .= '<span class="exp-post__reac">'
        . '<span class="exp-post__globito">' . explorer_icono('me-gusta') . '</span>'
        . '<span data-ex-resumen>'
        . ($likes === 0 ? 'Sé el primero en dar me gusta'
            : ($mio
                ? ($likes === 1 ? 'Te gusta a ti' : 'Tú y ' . number_format($likes - 1) . ' personas más')
                : number_format($likes) . ($likes === 1 ? ' persona' : ' personas') . ' les gusta'))
        . '</span></span>';
    $h .= '<span class="exp-post__derecha">';
    if ((int)$p['n_coment'] > 0) {
        $h .= '<button type="button" class="exp-post__enlace" data-ex-comentar="' . e($p['clave']) . '">💬 '
            . number_format((int)$p['n_coment']) . ' ' . ((int)$p['n_coment'] === 1 ? 'comentario' : 'comentarios') . '</button>';
    }
    if ((int)$p['vistas_count'] > 0) {
        $h .= '<span class="exp-post__vistas">' . explorer_icono('ojo') . ' ' . number_format((int)$p['vistas_count']) . '</span>';
    }
    if (!empty($p['rating']) && (float)$p['rating'] > 0) {
        $h .= '<a class="exp-post__rating" href="' . e(url_negocio($p['neg_slug'])) . '" title="Calificación de la tienda">★ '
            . number_format((float)$p['rating'], 1) . '</a>';
    }
    if ((int)$p['n_ped'] > 0) {
        $h .= '<span class="exp-post__vistas" title="Personas que pidieron información de esta tienda">🛒 '
            . number_format((int)$p['n_ped']) . '</span>';
    }
    $h .= '</span>';
    $h .= '</div>';

    // ---------- las acciones ----------
    $h .= explorer_acciones_html($p);

    // ---------- el bloque de comentarios (💬 Comentar) ----------
    $h .= explorer_comentarios_bloque_html($p, $nombre);

    $h .= '</article>';
    return $h;
}

/** Un comentario del muro (con su circulito de apodo, como los comentarios de Facebook). */
function explorer_comentario_html($autor, $texto, $fecha) {
    $autor = trim((string)$autor) !== '' ? (string)$autor : 'Visitante';
    $h  = '<div class="exp-coment__fila">';
    $h .= '<span class="exp-av__txt exp-av__txt--chico" style="background:' . e(explorer_color_apodo($autor)) . '">'
        . e(explorer_avatar_inicial($autor)) . '</span>';
    $h .= '<div class="exp-coment__globo">';
    $h .= '<p class="exp-coment__autor">' . e($autor) . '</p>';
    $h .= '<p class="exp-coment__texto">' . e($texto) . '</p>';
    if ($fecha !== '') $h .= '<p class="exp-coment__fecha">' . e(explorer_hace($fecha)) . '</p>';
    $h .= '</div></div>';
    return $h;
}

// ============================================================================
// 5.bis) LOS INTERCALADOS DEL MURO: PUBLICIDAD Y BLOQUES DE CASA
// ============================================================================
/**
 * Pedido del jefe (2026-09-16, textual): *«metele banners activos y metele publicidad a nuestras
 * secciones, a nuestros rubros, a nuestras noticias y ofertas de empleo… al menos debe haber un
 * anuncio cada 3 bloques de tiendas. Publicidad NO repetitiva, siempre distinta… diversas formas de
 * invitarlo a crear su tienda, o mostrarle su tienda y decirle que edite sus productos»*.
 *
 * CÓMO QUEDA EL MURO (posiciones contadas sobre las publicaciones de la visita, no de la tanda):
 *
 *   · **PUBLICIDAD cada 3**  → tras la 3, la 6, la 9, la 12… (el «al menos un anuncio cada 3 bloques»).
 *     Siempre **distinta**: los banners activos del sitio salen sin repetir (memoria de
 *     `banners_para()`) y, cuando se acaban (o no hay ninguno cargado), entra el **catálogo de avisos
 *     de casa**, que rota por número de hueco y trae **14 mensajes distintos** + los personalizados del
 *     dueño (su tienda, sus visitas, editar sus productos).
 *   · **RUBROS**    cada 12, en el hueco 4  → una tarjeta con 6 rubros (rotan) para explorar.
 *   · **NOTICIAS**  cada 12, en el hueco 5  → las últimas noticias de la zona (módulo de noticias).
 *   · **EMPLEOS**   cada 12, en el hueco 7  → ofertas de empleo vigentes (módulo de empleos).
 *   · **HISTORIAS** cada 10, en el hueco 10 → la tira de historias otra vez, como repite Facebook.
 *
 * Los huecos 4, 5 y 7 se eligieron porque **no caen en múltiplos de 3** (donde va la publicidad): así
 * nunca se apilan dos intercalados seguidos y el muro se lee como el de Facebook.
 *
 * Nada de esto se guarda en la base: son adornos del muro y se calculan al vuelo. Todo lo pinta
 * `explorer_posts_html()`, que es el MISMO motor para la página y para el scroll infinito, así que el
 * patrón sigue contando bien al bajar.
 */
if (!defined('EXPLORER_CADA_BANNER_A'))  define('EXPLORER_CADA_BANNER_A', 3);   // publicidad cada 3
if (!defined('EXPLORER_CADA_BANNER_B'))  define('EXPLORER_CADA_BANNER_B', 3);   // (los dos tramos: cada 3)
if (!defined('EXPLORER_CADA_HISTORIAS')) define('EXPLORER_CADA_HISTORIAS', 10); // historias cada 10
if (!defined('EXPLORER_CADA_RUBROS'))    define('EXPLORER_CADA_RUBROS', 12);    // rubros cada 12 (hueco 4)
if (!defined('EXPLORER_CADA_NOTICIAS'))  define('EXPLORER_CADA_NOTICIAS', 12);  // noticias cada 12 (hueco 5)
if (!defined('EXPLORER_CADA_EMPLEOS'))   define('EXPLORER_CADA_EMPLEOS', 12);   // empleos cada 12 (hueco 7)

/** ¿Después de la publicación $n toca publicidad? (cada 3: 3 · 6 · 9 · 12…) */
function explorer_toca_banner($n) {
    $n = (int)$n;
    if ($n <= 0) return false;
    $a = max(1, (int)EXPLORER_CADA_BANNER_A);
    $b = max(1, (int)EXPLORER_CADA_BANNER_B);
    if ($n < $a) return false;
    return (($n - $a) % $b) === 0;
}

/** ¿Después de la publicación $n toca repetir la tira de historias? (cada 10) */
function explorer_toca_historias($n) {
    $n = (int)$n;
    $c = max(2, (int)EXPLORER_CADA_HISTORIAS);
    return $n > 0 && ($n % $c) === 0;
}

/** ¿Toca la tarjeta de rubros? (cada 12, en el hueco 4 — nunca cae donde va la publicidad) */
function explorer_toca_rubros($n) {
    $n = (int)$n; $c = max(4, (int)EXPLORER_CADA_RUBROS);
    return $n > 0 && ($n % $c) === 4;
}

/** ¿Toca la tarjeta de noticias? (cada 12, en el hueco 5) */
function explorer_toca_noticias($n) {
    $n = (int)$n; $c = max(4, (int)EXPLORER_CADA_NOTICIAS);
    return $n > 0 && ($n % $c) === 5;
}

/** ¿Toca la tarjeta de empleos y anuncios? (cada 12, en el hueco 7) */
function explorer_toca_empleos($n) {
    $n = (int)$n; $c = max(4, (int)EXPLORER_CADA_EMPLEOS);
    return $n > 0 && ($n % $c) === 7;
}

// ---------------------------------------------------------------------------
// 📣 LA PUBLICIDAD DEL MURO
// ---------------------------------------------------------------------------

/**
 * EL CATÁLOGO DE AVISOS DE CASA: las **invitaciones distintas** que pidió el jefe. Se usan cuando no
 * hay banners activos que mostrar (o cuando ya salieron todos en esta carga). Cada aviso tiene su
 * icono, su color y su enlace a una herramienta REAL del sitio; y si el visitante tiene sesión con
 * tiendas, delante van **sus propios avisos** (su tienda, sus visitas, editar sus productos).
 *
 * @return array lista de avisos: icono, color, txt, sub, url, boton, accion
 */
function explorer_anuncios_casa() {
    static $cache = null;
    if ($cache !== null) return $cache;

    $avisos = [];

    // ---- 0) Los del DUEÑO: «muéstrale su tienda y dile que edite sus productos» (lo pidió el jefe)
    $u = usuario_actual();
    if ($u) {
        try {
            $st = db()->prepare("SELECT id, nombre, slug, vistas_count, estado,
                                        (SELECT COUNT(*) FROM directorio_servicios s
                                          WHERE s.negocio_id = n.id AND s.activo = 1) AS n_prod,
                                        (SELECT COUNT(*) FROM directorio_fotos f WHERE f.negocio_id = n.id) AS n_fotos
                                   FROM directorio_negocios n
                                  WHERE n.dueno_id = ? AND n.estado = 'activo'
                               ORDER BY n.id DESC LIMIT 3");
            $st->execute([(int)$u['id']]);
            foreach ($st->fetchAll() as $t) {
                $nom = explorer_nombre_corto($t['nombre']);
                $avisos[] = [
                    'ico' => 'panel', 'color' => '#0866ff',
                    'txt' => 'Tu tienda «' . $nom . '» ya está publicada',
                    'sub' => 'Lleva ' . number_format((int)$t['vistas_count']) . ' visitas y '
                           . (int)$t['n_prod'] . ' producto(s). Mírala y compártela con tus clientes.',
                    'url' => url_negocio((string)$t['slug']), 'boton' => 'Ver mi tienda', 'accion' => '',
                ];
                $avisos[] = [
                    'ico' => 'productos', 'color' => '#45bd62',
                    'txt' => 'Súbele más productos a «' . $nom . '»',
                    'sub' => ((int)$t['n_prod'] < 3
                        ? 'Con 2 o 3 productos vendes mucho más: agrega los tuyos en 1 minuto.'
                        : 'Edita precios, cambia fotos y agrega lo nuevo de esta semana.'),
                    'url' => url('productos.php') . '?n=' . (int)$t['id'], 'boton' => 'Editar mis productos', 'accion' => '',
                ];
            }
        } catch (Throwable $e) { /* sin tiendas: se sigue con el catálogo general */ }
    }

    // ---- 1) El catálogo de invitaciones (todas llevan a una herramienta de verdad)
    $avisos = array_merge($avisos, [
        ['ico' => 'maestro', 'color' => '#0866ff',
         'txt' => '🛠️ Crea tu tienda con Inteligencia Artificial',
         'sub' => 'Sube 8 fotos y «El maestro» te arma la tienda, la descripción y tus primeros productos. Gratis.',
         'url' => url('crear-tienda'), 'boton' => 'Crear mi tienda', 'accion' => ''],

        ['ico' => 'camara', 'color' => '#0f766e',
         'txt' => '📸 Agrega tu tienda con la cámara del celular',
         'sub' => 'El caminante: sacas las fotos y en 2 minutos tu negocio queda publicado en Chimbote.',
         'url' => url('caminante/'), 'boton' => 'Probar El caminante', 'accion' => ''],

        ['ico' => 'productos', 'color' => '#45bd62',
         'txt' => '🛍️ Publica un producto y véndelo por WhatsApp',
         'sub' => 'Con foto, precio y un toque, el cliente te escribe directo. Sin comisiones.',
         'url' => url('crear-tienda') . '?modo=producto', 'boton' => 'Publicar un producto', 'accion' => ''],

        ['ico' => 'tiendas', 'color' => '#7a5230',
         'txt' => '🏪 ¿Tu negocio ya está en DeChimbote.com?',
         'sub' => 'Búscalo y reclámalo gratis: así puedes editar sus fotos, sus precios y sus productos.',
         'url' => url('reclamar'), 'boton' => 'Reclamar mi negocio', 'accion' => ''],

        ['ico' => 'panel', 'color' => '#123c6b',
         'txt' => '📈 Mira cuánta gente está viendo tu tienda',
         'sub' => 'Tu panel te dice las visitas, quién te buscó y quién pidió tu WhatsApp.',
         'url' => url('panel.php'), 'boton' => 'Ver mi panel', 'accion' => ''],

        ['ico' => 'empleo', 'color' => '#6d071a',
         'txt' => '💼 Publica un aviso de empleo gratis',
         'sub' => 'Busca personal para tu negocio: tu aviso sale en la página de empleos y dura 30 días.',
         'url' => url('empleos') . '#publicar', 'boton' => 'Publicar un aviso', 'accion' => ''],

        ['ico' => 'vivo', 'color' => '#e41e3f',
         'txt' => '🔴 Mira lo que la gente busca y nadie vende',
         'sub' => 'Información en vivo: si tienes eso que buscan, escríbeles y vendes hoy mismo.',
         'url' => url('en-vivo'), 'boton' => 'Ver la información en vivo', 'accion' => ''],

        // 🗑️ 2026-09-17: aquí iba la historia «🥷 ¿Dudas para vender por internet?» (que abría el
        // chat). El chat de ayuda ya no vive en todo el sitio: es 🧭 El guía, el anfitrión de cada
        // tienda, y solo se pinta en las fichas. Se retiró para no dejar un botón muerto.

        ['ico' => 'estrella', 'color' => '#b45309',
         'txt' => '⭐ Haz que tu tienda salga primero',
         'sub' => 'El plan Premium te da más visitas, tu tienda entre las primeras y sin límites en el chat de ayuda.',
         'url' => url('crear-tienda'), 'boton' => 'Quiero más visitas', 'accion' => ''],

        ['ico' => 'camara', 'color' => '#0ea5e9',
         'txt' => '📷 Sube tus fotos con la cámara y dicta los precios',
         'sub' => 'La captura rápida del panel: tomas la foto, dictas el nombre y el precio, y queda publicado.',
         'url' => url('panel.php'), 'boton' => 'Cargar mi inventario', 'accion' => ''],

        ['ico' => 'noticias', 'color' => '#0f766e',
         'txt' => '📰 ¿Pasó algo en tu negocio? Cuéntalo',
         'sub' => 'Inauguraciones, promociones, aniversarios: las noticias de la zona se leen todos los días.',
         'url' => url('noticias'), 'boton' => 'Ver las noticias', 'accion' => ''],

        ['ico' => 'whatsapp', 'color' => '#25d366',
         'txt' => '💬 Vende por WhatsApp con tu catálogo en línea',
         'sub' => 'Tu tienda aquí es tu catálogo: el cliente ve tus productos y te escribe con un toque.',
         'url' => url('crear-tienda'), 'boton' => 'Armar mi catálogo', 'accion' => ''],

        ['ico' => 'ubicacion', 'color' => '#f5533d',
         'txt' => '📍 Que te encuentren cerca de tu negocio',
         'sub' => 'Pon tu dirección y tus clientes te verán en «tiendas cerca de mí», con el mapa y tu WhatsApp.',
         'url' => url('panel.php'), 'boton' => 'Poner mi ubicación', 'accion' => ''],

        ['ico' => 'guardar', 'color' => '#64748b',
         'txt' => '🔖 Guarda lo que te gusta y vuelve después',
         'sub' => 'Marca tus tiendas favoritas y las tendrás a mano la próxima vez.',
         'url' => url('registro.php'), 'boton' => 'Crear mi cuenta gratis', 'accion' => ''],
    ]);

    return $cache = $avisos;
}

/** Un aviso de casa pintado con la forma de las publicaciones del muro. */
function explorer_casa_feed_html($n = 0) {
    $avisos = explorer_anuncios_casa();
    if (!$avisos) return '';
    $total = count($avisos);
    // 🔁 La rotación va por NÚMERO DE HUECO (no al azar y sin memoria): así el aviso 1, el 2, el 3…
    //    salen siempre distintos y, al bajar con el scroll, siguen la misma cuenta (el hueco se sabe
    //    por la posición de la publicación), sin repetirse nunca en toda la visita.
    $hueco = max(0, intdiv((int)$n, max(1, (int)EXPLORER_CADA_BANNER_A)) - 1);
    $a = $avisos[$hueco % $total];

    $extra = (!empty($a['accion'])) ? ' data-ex-accion="' . e($a['accion']) . '"' : '';
    return '<article class="exp-post exp-post--publi exp-post--casa" aria-label="Publicidad">'
        . '<header class="exp-publi__cabeza"><span class="exp-publi__etiqueta">Publicidad</span>'
        . '<span class="exp-publi__dominio">dechimbote.com</span></header>'
        . '<a class="exp-publi__enlace exp-publi__enlace--casa" href="' . e($a['url']) . '"' . $extra . '>'
        . '<span class="exp-publi__casa" style="background:' . e($a['color']) . '">' . explorer_icono($a['ico']) . '</span>'
        . '<span class="exp-publi__cta">Ver más</span></a>'
        . '<div class="exp-publi__pie"><a class="exp-publi__titulo" href="' . e($a['url']) . '"' . $extra . '>'
        . e($a['txt']) . '</a>'
        . '<p class="exp-publi__sub">' . e($a['sub']) . '</p>'
        . '<a class="exp-btn exp-btn--azul exp-publi__btn" href="' . e($a['url']) . '"' . $extra . '>'
        . e($a['boton']) . '</a>'
        . '<span class="exp-publi__dominio">dechimbote.com</span></div></article>';
}

/**
 * LA PUBLICIDAD DENTRO DEL MURO: primero un **banner activo de verdad** (`directorio_banners`, los
 * mismos que rotan en la portada y que NO se repiten en la misma carga, con su impresión contada); si
 * ya no queda ninguno, un **aviso de casa** del catálogo (siempre distinto).
 */
function explorer_banner_feed_html($n = 0) {
    $motor = __DIR__ . '/banners.php';
    if (is_file($motor)) {
        require_once $motor;
        if (function_exists('banners_para')) {
            foreach (banners_para(1) as $b) {
                $id     = (int)$b['id'];
                $ruta   = (string)($b['imagen'] ?? ($b['imagen_h'] ?? ''));
                $titulo = trim((string)($b['titulo'] ?? ''));
                $busq   = trim((string)($b['busqueda'] ?? ''));
                $enlace = trim((string)($b['enlace'] ?? ''));
                $tema   = trim((string)($b['tema'] ?? ''));

                if ($busq !== '')      { $href = e(banners_busqueda_url($busq)); $extra = ''; }
                elseif ($enlace !== ''){ $href = e($enlace); $extra = ' target="_blank" rel="noopener"'; }
                else                   { $href = '#'; $extra = ''; }
                if ($href === '#' && $tema !== '' && function_exists('banners_temas')) {
                    $temas = banners_temas();
                    $cfg   = $temas[$tema] ?? null;
                    if (!empty($cfg['cat'])) $href = e(url_categoria((string)$cfg['cat']));
                }
                // 🛟 Un banner sin destino: antes que perder al anunciante, el clic va al buscador.
                if ($href === '#') $href = e(url('buscar.php'));

                if (function_exists('banner_registrar_impresion')) banner_registrar_impresion($id);

                $datos = ' data-banner="' . $id . '" data-tema="' . e($tema) . '" data-nombre="' . e($titulo) . '"';
                if ($busq !== '') $datos .= ' data-busqueda="' . e($busq) . '"';

                return '<article class="exp-post exp-post--publi" aria-label="Publicidad">'
                    . '<header class="exp-publi__cabeza"><span class="exp-publi__etiqueta">Publicidad</span>'
                    . '<span class="exp-publi__dominio">dechimbote.com</span></header>'
                    . '<a class="exp-publi__enlace" href="' . $href . '"' . $extra . $datos . '>'
                    . ($ruta !== '' ? img_tag($ruta, $titulo !== '' ? $titulo : 'Publicidad',
                        ['sizes' => '(max-width: 860px) 100vw, 660px']) : '')
                    . '<span class="exp-publi__cta">Ver más</span></a>'
                    . '<div class="exp-publi__pie">'
                    . '<a class="exp-publi__titulo" href="' . $href . '"' . $extra . '>'
                    . e($titulo !== '' ? $titulo : 'Publicidad de DeChimbote.com') . '</a>'
                    . '<span class="exp-publi__dominio">dechimbote.com</span>'
                    . '</div></article>';
            }
        }
    }
    return explorer_casa_feed_html($n);
}

// ---------------------------------------------------------------------------
// 🗂️ 📰 💼 LOS BLOQUES DE CASA (rubros, noticias y empleos)
// ---------------------------------------------------------------------------

/** La cabeza de un bloque de casa (icono con color + título + subtítulo). */
function explorer_bloque_cabeza_html($ico, $color, $tit, $sub) {
    return '<header class="exp-bloque__cabeza">'
        . '<span class="exp-bloque__ico" style="background:' . e($color) . '">' . explorer_icono($ico) . '</span>'
        . '<span class="exp-bloque__txt"><b>' . e($tit) . '</b><i>' . e($sub) . '</i></span>'
        . '</header>';
}

/**
 * 🗂️ LA TARJETA DE RUBROS: «explora por rubro» con 6 rubros (rotan para no repetirse) y el botón que
 * abre el cajón con los 122. Es la invitación a pasearse por el directorio.
 */
function explorer_rubros_feed_html($n = 0) {
    $rubros = explorer_rubros_directos(24);
    if (!$rubros) return '';
    $total = count($rubros);
    $hueco = max(0, intdiv((int)$n, max(4, (int)EXPLORER_CADA_RUBROS)));
    $inicio = ($hueco * 6) % $total;
    $seis = [];
    for ($i = 0; $i < 6 && $i < $total; $i++) $seis[] = $rubros[($inicio + $i) % $total];

    $h  = '<article class="exp-post exp-bloque exp-bloque--rubros" aria-label="Rubros del directorio">';
    $h .= explorer_bloque_cabeza_html('rubros', '#45bd62', 'Explora por rubro',
        'Las tiendas de Chimbote, ordenadas por lo que venden');
    $h .= '<div class="exp-rubros">';
    foreach ($seis as $r) {
        $h .= '<a class="exp-rubro" href="' . e(explorer_url(['cat' => (string)$r['slug'], 'post' => null])) . '">'
            . '<span class="exp-rubro__ico">' . e((string)$r['icono']) . '</span>'
            . '<span class="exp-rubro__nom">' . e((string)$r['nombre']) . '</span>'
            . '<span class="exp-rubro__n">' . number_format((int)$r['n']) . '</span>'
            . '</a>';
    }
    $h .= '</div>';
    $h .= '<div class="exp-bloque__pie">'
        . '<button type="button" class="exp-btn exp-btn--gris exp-btn--ancho" data-ex-abrir-rubros>Ver todos los rubros</button>'
        . '</div></article>';
    return $h;
}

/**
 * 📰 LA TARJETA DE NOTICIAS: **solo las noticias de HOY** (orden del jefe, 2026-09-17: *«solo publica
 * noticias del día… nunca anteriores»*). Si hoy no hay ninguna, la tarjeta **NO se pinta** (nunca un
 * bloque vacío ni una noticia de ayer).
 *
 * Las noticias las escribe el robot todas las mañanas (`cron/noticias_diarias.php`, 06:00 de Chimbote),
 * así que durante el día esta tarjeta va con lo del día. La fecha va corta, «17/09», para que el pie
 * entre en una sola fila.
 */
function explorer_noticias_feed_html($n = 0) {
    $motor = __DIR__ . '/noticias.php';
    if (!is_file($motor)) return '';
    require_once $motor;
    if (!function_exists('noticias_por_dias')) return '';

    $hoy   = date('Y-m-d');   // fecha de LIMA (el sitio la fija al arrancar)
    $notas = [];
    try {
        $grupos = noticias_por_dias([], [$hoy], 'corta');
        if (!empty($grupos[$hoy])) {
            $notas = $grupos[$hoy];
        } elseif (function_exists('noticias_listar')) {
            // 🛟 Respaldo (por si el agrupado cambia de forma): se piden las últimas y se deja SOLO
            //    lo de hoy; cualquier cosa de ayer o más vieja se descarta.
            foreach (noticias_listar(['campos' => 'corta', 'por_pagina' => 12]) as $nt) {
                if ((string)($nt['fecha'] ?? '') !== $hoy) continue;
                $notas[] = $nt;
                if (count($notas) >= 3) break;
            }
        }
    } catch (Throwable $e) { return ''; }

    $notas = array_slice($notas, 0, 3);
    if (!$notas) return '';   // sin noticias de HOY: la tarjeta no se pinta

    $h  = '<article class="exp-post exp-bloque exp-bloque--noticias" aria-label="Noticias de Chimbote">';
    $h .= explorer_bloque_cabeza_html('noticias', '#0f766e', 'Noticias de hoy en Chimbote',
        'Solo lo que se publicó hoy, contado por nosotros');
    foreach ($notas as $nt) {
        $slug = (string)($nt['slug'] ?? '');
        if ($slug === '') continue;
        $h .= '<a class="exp-nota" href="' . e(url('noticia/' . $slug)) . '">'
            . '<b class="exp-nota__tit">' . e((string)($nt['titulo'] ?? '')) . '</b>';
        $entrada = trim((string)($nt['entradilla'] ?? ''));
        if ($entrada !== '') $h .= '<span class="exp-nota__ent">' . e(explorer_recorte($entrada, 26)) . '</span>';
        $pie = [];
        // ⏰ La hora del día (corta) y la fecha en «17/09»: nunca «17 de septiembre» (partía la fila).
        $hora = trim((string)($nt['hora'] ?? ''));
        if ($hora !== '') $pie[] = '🕒 ' . e(substr($hora, 0, 5));
        $pie[] = '📅 ' . e(date('d/m', strtotime($hoy)));
        if (!empty($nt['fuente_nombre'])) $pie[] = '📰 ' . e((string)$nt['fuente_nombre']);
        $h .= '<span class="exp-nota__pie">' . implode(' · ', $pie) . '</span>';
        $h .= '</a>';
    }
    $h .= '<div class="exp-bloque__pie">'
        . '<a class="exp-btn exp-btn--gris exp-btn--ancho" href="' . e(url('noticias')) . '">Ver todas las noticias</a>'
        . '</div></article>';
    return $h;
}

/**
 * 💼 LA TARJETA DE EMPLEOS: ofertas vigentes (módulo de empleos). Si no hay avisos, NO se pinta.
 */
function explorer_empleos_feed_html($n = 0) {
    if (!function_exists('empleos_destacados')) return '';
    $avisos = [];
    try { $avisos = empleos_destacados(3, true); } catch (Throwable $e) { return ''; }
    if (!$avisos) return '';

    $cuantos = function_exists('empleos_contar_activos') ? (int)empleos_contar_activos() : count($avisos);
    $h  = '<article class="exp-post exp-bloque exp-bloque--empleos" aria-label="Empleos y anuncios">';
    $h .= explorer_bloque_cabeza_html('empleo', '#6d071a', 'Empleos y anuncios',
        $cuantos > 0 ? ('Hay ' . number_format($cuantos) . ' aviso(s) vigente(s) en Chimbote')
                     : 'Quién está contratando hoy');
    foreach ($avisos as $e) {
        $slug = (string)($e['slug'] ?? '');
        if ($slug === '') continue;
        $meta = [];
        if (!empty($e['categoria_icono']) || !empty($e['categoria_nombre'])) {
            $meta[] = trim((string)($e['categoria_icono'] ?? '') . ' ' . (string)($e['categoria_nombre'] ?? ''));
        }
        if (!empty($e['distrito_nombre'])) $meta[] = '📍 ' . (string)$e['distrito_nombre'];
        $sueldo = function_exists('empleo_sueldo_txt') ? trim((string)empleo_sueldo_txt($e)) : '';
        if ($sueldo !== '') $meta[] = '💰 ' . $sueldo;
        $h .= '<a class="exp-aviso" href="' . e(url('empleo/' . $slug)) . '">'
            . '<b class="exp-aviso__tit">' . e((string)($e['titulo'] ?? '')) . '</b>';
        if ($meta) $h .= '<span class="exp-aviso__meta">' . e(implode(' · ', $meta)) . '</span>';
        $h .= '</a>';
    }
    $h .= '<div class="exp-bloque__pie">'
        . '<a class="exp-btn exp-btn--gris exp-btn--ancho" href="' . e(url('empleos')) . '">Ver todos los empleos y anuncios</a>'
        . '</div></article>';
    return $h;
}

// ---------------------------------------------------------------------------
// 📜 EL MURO COMPLETO
// ---------------------------------------------------------------------------

/**
 * EL MURO: las publicaciones con TODOS sus intercalados, en su orden. Lo usan la página (tanda 1) y la
 * API del scroll infinito, así que el muro sale igual por los dos lados y los tramos siguen contando
 * bien al bajar (`$desde` = cuántas publicaciones se pintaron antes).
 *
 * @param array $posts publicaciones ya preparadas
 * @param int   $desde cuántas publicaciones van pintadas antes de esta tanda (0 en la primera)
 */
function explorer_posts_html(array $posts, $desde = 0) {
    $h = '';
    $i = max(0, (int)$desde);
    foreach ($posts as $p) {
        $i++;
        $h .= explorer_post_html($p);
        if (explorer_toca_banner($i))    $h .= explorer_banner_feed_html($i);
        if (explorer_toca_rubros($i))    $h .= explorer_rubros_feed_html($i);
        if (explorer_toca_noticias($i))  $h .= explorer_noticias_feed_html($i);
        if (explorer_toca_empleos($i))   $h .= explorer_empleos_feed_html($i);
        if (explorer_toca_historias($i)) $h .= explorer_stories_html('-n' . $i, true);
    }
    return $h;
}

// ============================================================================
// 6) LA TIRA DE HISTORIAS (flyers de las tiendas, como las historias de Facebook)
// ============================================================================
/**
 * Los datos de la tira (una historia por tienda con flyers), **una sola vez por petición**: el muro
 * repite la tira más abajo y no tiene sentido volver a consultar la base ni volver a sortearla.
 */
function explorer_stories_datos() {
    static $cache = null;
    if ($cache !== null) return $cache;
    $items = [];
    $motor = __DIR__ . '/historias.php';
    if (is_file($motor)) {
        require_once $motor;
        if (function_exists('historias_tiendas')) {
            foreach (historias_tiendas((int)EXPLORER_HISTORIAS) as $t) {
                $flyer = $t['flyers'][0] ?? null;
                if (!$flyer) continue;
                $items[] = [
                    'url'    => (string)$t['url'],
                    'nombre' => explorer_nombre_corto($t['nombre']),
                    'largo'  => (string)$t['nombre'],
                    'titulo' => (string)$flyer['titulo'],
                    'ruta'   => (string)$flyer['ruta'],
                ];
            }
        }
    }
    return $cache = $items;
}

/**
 * LA TIRA DE HISTORIAS. Reutiliza el módulo de historias del sitio (`includes/historias.php`: la tabla
 * `directorio_historias` marca qué flyers salen) pero se pinta con las formas del muro: tarjetas
 * redondeadas de 112 × 190 con el aro azul del avatar y el nombre abajo, y la primera tarjeta es
 * «Crear historia», que aquí lleva a El maestro (`/crear-tienda`).
 *
 * @param string $sufijo  '' para la tira de arriba; '-n10', '-n20'… para las que repite el muro (los
 *                        `id` de una página no se pueden repetir).
 * @param bool   $en_muro true = la versión que va DENTRO del muro (con su tarjeta blanca y su título
 *                        «Historias destacadas»), como las filas que repite Facebook.
 */
function explorer_stories_html($sufijo = '', $en_muro = false) {
    $items = explorer_stories_datos();

    $h  = '<section class="exp-historias' . ($en_muro ? ' exp-historias--en-muro' : '') . '"'
        . ' id="historias' . e($sufijo) . '" aria-label="Historias de las tiendas">';
    if ($en_muro) {
        $h .= '<p class="exp-historias__titulo">Historias destacadas de las tiendas</p>';
    }
    $h .= '<div class="exp-historias__tira">';
    $h .= '<a class="exp-hist exp-hist--crear" href="' . e(url('crear-tienda')) . '">'
        . '<span class="exp-hist__crear-foto">' . explorer_mi_avatar_html() . '</span>'
        . '<span class="exp-hist__crear-mas">' . explorer_icono('mas') . '</span>'
        . '<span class="exp-hist__crear-txt">Crear historia</span>'
        . '</a>';
    foreach ($items as $t) {
        $h .= '<a class="exp-hist" href="' . e($t['url']) . '" title="' . e($t['largo']) . '">'
            . '<span class="exp-hist__foto">' . img_tag($t['ruta'], $t['titulo'], ['sizes' => '112px']) . '</span>'
            . '<span class="exp-hist__velo" aria-hidden="true"></span>'
            . '<span class="exp-av exp-av--hist">' . explorer_avatar_html($t['ruta'], $t['nombre']) . '</span>'
            . '<span class="exp-hist__nombre">' . e($t['nombre']) . '</span>'
            . '</a>';
    }
    $h .= '</div></section>';
    return $h;
}
// 7) LA COLUMNA DERECHA: publicidad, tiendas sugeridas y herramientas
// ============================================================================
/**
 * 📣 Publicidad de la columna derecha.
 *
 * Primero van los **banners REALES del sitio** (`directorio_banners`: los mismos que rotan en la
 * portada, con su conteo de impresiones y de clics). Si el jefe no tiene banners cargados, el hueco
 * NO se queda vacío: se completa con **publicidad de casa** (nuestras propias herramientas), que es
 * lo que hace cualquier muro mientras un anunciante no paga. Nunca se inventa un anuncio de un
 * negocio que no existe.
 */
function explorer_publicidad_html($n = 2) {
    $n = max(1, (int)$n);
    $html_banners = [];

    $motor = __DIR__ . '/banners.php';
    if (is_file($motor)) {
        require_once $motor;
        if (function_exists('banners_para')) {
            foreach (banners_para($n) as $b) {
                $id     = (int)$b['id'];
                $ruta   = (string)($b['imagen'] ?? ($b['imagen_h'] ?? ''));
                $titulo = trim((string)($b['titulo'] ?? ''));
                $busq   = trim((string)($b['busqueda'] ?? ''));
                $enlace = trim((string)($b['enlace'] ?? ''));
                $tema   = trim((string)($b['tema'] ?? ''));

                if ($busq !== '')      { $href = e(banners_busqueda_url($busq)); $extra = ''; }
                elseif ($enlace !== ''){ $href = e($enlace); $extra = ' target="_blank" rel="noopener"'; }
                else                   { $href = '#'; $extra = ''; }

                // 🛟 Un banner «de necesidad» (sin enlace ni búsqueda) llevaba al modal de la portada,
                //    que aquí no existe: se le da destino REAL a su RUBRO para que el clic no quede muerto.
                if ($href === '#' && $tema !== '' && function_exists('banners_temas')) {
                    $temas = banners_temas();
                    $cfg   = $temas[$tema] ?? null;
                    if (!empty($cfg['cat'])) $href = e(url_categoria((string)$cfg['cat']));
                }

                if (function_exists('banner_registrar_impresion')) banner_registrar_impresion($id);

                $datos = ' data-banner="' . $id . '" data-tema="' . e($tema) . '" data-nombre="' . e($titulo) . '"';
                if ($busq !== '') $datos .= ' data-busqueda="' . e($busq) . '"';

                $h = '<a class="exp-ad" href="' . $href . '"' . $extra . $datos . '>';
                if ($ruta !== '') {
                    $h .= '<span class="exp-ad__foto">' . img_tag($ruta, $titulo !== '' ? $titulo : 'Publicidad',
                            ['sizes' => '(max-width: 1000px) 100vw, 320px']) . '</span>';
                }
                $h .= '<span class="exp-ad__pie">'
                    . '<span class="exp-ad__txt">' . e($titulo !== '' ? $titulo : 'Publicidad de DeChimbote.com') . '</span>'
                    . '<span class="exp-ad__dominio">dechimbote.com</span></span></a>';
                $html_banners[] = $h;
            }
        }
    }

    // Publicidad de casa (solo lo que falte)
    $casa = [
        [
            'ico' => 'maestro', 'color' => '#ea6a12',
            'titulo' => '🛠️ Crea tu tienda con Inteligencia Artificial',
            'texto'  => 'Sube 8 fotos y «El maestro» te arma la tienda, la descripción y 2 productos. Gratis.',
            'url'    => url('crear-tienda'), 'boton' => 'Crear mi tienda',
        ],
        [
            'ico' => 'camara', 'color' => '#0f766e',
            'titulo' => '📸 Agrega una tienda con la cámara del celular',
            'texto'  => 'El caminante: sacas las fotos y en 2 minutos tu negocio está publicado en Chimbote.',
            'url'    => url('caminante/'), 'boton' => 'Probar El caminante',
        ],
    ];

    $h = '<section class="exp-rail__bloque" aria-label="Publicidad">';
    $h .= '<h2 class="exp-rail__titulo">Publicidad</h2>';
    foreach ($html_banners as $b) $h .= $b;
    $faltan = $n - count($html_banners);
    for ($i = 0; $i < $faltan && $i < count($casa); $i++) {
        $c = $casa[$i];
        $h .= '<div class="exp-ad exp-ad--casa">'
            . '<span class="exp-ad__foto exp-ad__foto--casa" style="background:' . e($c['color']) . '">'
            . explorer_icono($c['ico']) . '</span>'
            . '<span class="exp-ad__pie">'
            . '<span class="exp-ad__txt">' . e($c['titulo']) . '</span>'
            . '<span class="exp-ad__sub">' . e($c['texto']) . '</span>'
            . '<span class="exp-ad__dominio">dechimbote.com</span>'
            . '<a class="exp-btn exp-btn--azul exp-ad__btn" href="' . e($c['url']) . '">' . e($c['boton']) . '</a>'
            . '</span></div>';
    }
    $h .= '</section>';
    return $h;
}

/** 🏪 «Tiendas que te pueden interesar» — el bloque con la forma de «Solicitudes de amistad». */
function explorer_sugerencias_html($n = 4) {
    $sql = "SELECT n.id, n.nombre, n.slug, n.rating, n.vistas_count,
                   c.nombre AS cat_nombre, c.icono AS cat_icono, d.nombre AS distrito,
                   (SELECT f.ruta FROM directorio_fotos f WHERE f.negocio_id = n.id
                     ORDER BY f.orden ASC, f.id ASC LIMIT 1) AS portada
              FROM directorio_negocios n
              LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
              LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
             WHERE n.estado = 'activo'
               AND EXISTS (SELECT 1 FROM directorio_fotos f2 WHERE f2.negocio_id = n.id)
             ORDER BY RAND() LIMIT " . (int)($n * 2);
    try {
        $filas = db()->query($sql)->fetchAll() ?: [];
    } catch (Throwable $e) {
        return '';
    }
    $out = [];
    foreach ($filas as $f) {
        if (!negocio_portada_ok($f['portada'] ?? '')) continue;
        $out[] = $f;
        if (count($out) >= $n) break;
    }
    if (!$out) return '';

    $h = '<section class="exp-rail__bloque" aria-label="Tiendas que te pueden interesar">';
    $h .= '<h2 class="exp-rail__titulo">Tiendas que te pueden interesar</h2>';
    foreach ($out as $t) {
        $h .= '<div class="exp-sug" data-ex-sug="' . (int)$t['id'] . '">';
        $h .= '<a class="exp-sug__foto" href="' . e(url_negocio($t['slug'])) . '">'
            . explorer_avatar_html($t['portada'], $t['nombre'], 'exp-av__img--sug') . '</a>';
        $h .= '<div class="exp-sug__txt">';
        $h .= '<a class="exp-sug__nombre" href="' . e(url_negocio($t['slug'])) . '">'
            . e(explorer_nombre_corto($t['nombre'])) . '</a>';
        $h .= '<p class="exp-sug__meta">' . e($t['cat_icono'] ?? '🏪') . ' ' . e($t['cat_nombre'] ?? '')
            . (!empty($t['distrito']) ? ' · ' . e($t['distrito']) : '') . '</p>';
        $h .= '</div>';
        $h .= '<div class="exp-sug__botones">'
            . '<a class="exp-btn exp-btn--azul" href="' . e(url_negocio($t['slug'])) . '">Ver tienda</a>'
            . '<button type="button" class="exp-btn exp-btn--gris" data-ex-seguir="' . (int)$t['id'] . '">Guardar</button>'
            . '</div>';
        $h .= '</div>';
    }
    $h .= '</section>';
    return $h;
}

/** 🛠️ «Tus herramientas» — el bloque que lleva a todo lo que el jefe puede hacer en el sitio. */
function explorer_rail_herramientas_html() {
    $t = explorer_herramientas();
    // Se enseñan las de trabajo (crear/publicar) para no repetir la barra izquierda entera.
    $claves = ['maestro', 'caminante', 'empleos', 'vivo'];   // (2026-09-17: se quitó 'ninja')
    $h = '<section class="exp-rail__bloque" aria-label="Tus herramientas">';
    $h .= '<h2 class="exp-rail__titulo">Tus herramientas</h2>';
    foreach ($t as $it) {
        if (!in_array($it['k'], $claves, true)) continue;
        $h .= '<a class="exp-rail__it" href="' . e($it['url']) . '"' . (!empty($it['accion']) ? ' data-ex-accion="' . e($it['accion']) . '"' : '') . '>'
            . '<span class="exp-cuadro" style="background:' . e($it['color']) . '">' . explorer_icono($it['ico']) . '</span>'
            . '<span class="exp-rail__ittxt"><b>' . e($it['txt']) . '</b><i>' . e($it['sub']) . '</i></span>'
            . '</a>';
    }
    $h .= '</section>';
    return $h;
}

// ============================================================================
// 8) LA COLUMNA IZQUIERDA (accesos directos del sitio)
// ============================================================================
/** Los rubros con más tiendas, para los «accesos directos» (como los grupos de la barra de Facebook). */
function explorer_rubros_directos($limite = 10) {
    try {
        $rs = db()->query("SELECT c.id, c.nombre, c.slug, c.icono,
                                  (SELECT COUNT(*) FROM directorio_negocios n
                                    WHERE n.categoria_id = c.id AND n.estado = 'activo') AS n
                             FROM directorio_categorias c
                            WHERE c.activo = 1
                            ORDER BY n DESC, c.nombre ASC
                            LIMIT " . (int)$limite)->fetchAll();
        return $rs ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

/** Todos los rubros (para el cajón «Todos los rubros»). */
function explorer_rubros_todos() {
    try {
        return db()->query("SELECT id, nombre, slug, icono FROM directorio_categorias
                             WHERE activo = 1 ORDER BY nombre ASC")->fetchAll() ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

/** Cuántos avisos de empleo hay vigentes (para el globito rojo de la campana). */
function explorer_avisos_n() {
    if (!function_exists('empleos_contar_activos')) return 0;
    try {
        return (int)empleos_contar_activos();
    } catch (Throwable $e) {
        return 0;
    }
}

/**
 * 📊 El resumen REAL del sitio (lo que se ve al tocar la campana y en el panel de la izquierda).
 * Son tres COUNT de la base: nada inventado, y si una consulta falla el número sale en 0.
 */
function explorer_resumen() {
    $r = ['tiendas' => 0, 'productos' => 0, 'rubros' => 0, 'opiniones' => 0, 'empleos' => explorer_avisos_n()];
    try {
        $r['tiendas'] = (int)db()->query("SELECT COUNT(*) FROM directorio_negocios WHERE estado = 'activo'")->fetchColumn();
    } catch (Throwable $e) {}
    try {
        $r['productos'] = (int)db()->query("SELECT COUNT(*) FROM directorio_servicios s
                                              JOIN directorio_negocios n ON n.id = s.negocio_id
                                             WHERE s.activo = 1 AND n.estado = 'activo'")->fetchColumn();
    } catch (Throwable $e) {}
    try {
        $r['rubros'] = (int)db()->query("SELECT COUNT(*) FROM directorio_categorias WHERE activo = 1")->fetchColumn();
    } catch (Throwable $e) {}
    if (explorer_tabla_ok('directorio_opiniones')) {
        try { $r['opiniones'] = (int)db()->query("SELECT COUNT(*) FROM directorio_opiniones")->fetchColumn(); } catch (Throwable $e) {}
    }
    return $r;
}
