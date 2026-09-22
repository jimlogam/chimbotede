<?php
/**
 * muro.php — 💬 EL CHAT EN VIVO DEL SITIO («lo que está pasando ahora»)
 * ====================================================================
 * Pedido del jefe (2026-09-15, al ver la primera versión): *«yo lo que quiero encontrar es un chat
 * activo con mensajes que van llegando cada dos segundos, con búsquedas no encontradas, con clic
 * realizado en tiendas, con negocios que están publicando nuevos productos… lo mismo que me llega a
 * mi Telegram… Yo quiero un chat que esté vivo, que se mantenga fluido, que el que lo esté mirando
 * en ese momento se lleve la oportunidad.»*
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * CÓMO FUNCIONA
 * ─────────────────────────────────────────────────────────────────────────────
 * · **De dónde sale:** de `directorio_avisos_log`, que es *exactamente* «lo que le llega al Telegram
 *   del jefe»: búsquedas, clics de precio, botones de llamar, visitas, opiniones, encargos, tiendas y
 *   productos nuevos, empleos, reportes… Solo de **usuarios** (`es_bot = 0`) y sin los resúmenes.
 * · **El ritmo (2 s):** el navegador tiene una COLA. Primero suelta los mensajes nuevos de verdad
 *   (los pide al servidor cada 20 s). Cuando la cola se vacía, **recicla** los últimos movimientos
 *   —el jefe lo pidió así: *«si es necesario repitiendo las publicaciones de abajo arriba… nunca
 *   estático»*—, siempre con **su hora real** (nunca se hace pasar un mensaje viejo por nuevo).
 * · **El tope:** el chat mantiene **150** mensajes (el de siempre): al entrar uno nuevo, el primero
 *   sale de la lista. Nada se borra de la base: de ese registro salen los Récords y los informes.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * 🔴 REGLA DE ORO DEL JEFE (2026-09-15, textual)
 * ─────────────────────────────────────────────────────────────────────────────
 * *«No se usa la palabra Bot ni robot ni araña ni nada que tenga que ver con automatizaciones de
 * spiders o navegadores: para eso usamos la palabra OPERADORES o la palabra USUARIOS.»*
 * → En este archivo **no existe** ninguna de esas palabras en lo que se ve: el que busca, el que
 *   toca «Llamar», el que pide precio es **«Un usuario»**; el que publica es **«Un negocio»**.
 *   (Al que no es persona ni se le nombra: la consulta ya filtra `es_bot = 0`.)
 *
 * 🔒 Y NUNCA se publica el `resumen` del registro tal cual: trae *usuario y CONTRASEÑA* de las
 *    tiendas nuevas (lo cazó `__muro_prueba.php`). El texto lo construye `muro_evento_datos()`.
 */

if (!defined('MURO_TABLA_LOG')) define('MURO_TABLA_LOG', 'directorio_avisos_log');
if (!defined('MURO_TOPE'))      define('MURO_TOPE', 150);   // el tope que pidió el jefe
if (!defined('MURO_LOTE'))      define('MURO_LOTE', 2000);  // filas que se miran por consulta
if (!defined('MURO_RITMO_MS'))  define('MURO_RITMO_MS', 2000);  // un mensaje cada 2 segundos
// 🟢 EL CONTADOR DE GENTE EN LÍNEA (fórmula que pidió el jefe, 2026-09-15): *«el número mínimo va a
//    ser 27… si hay uno, 27 + 4; si hay dos, 27 + 8»* → **27 + 4 × (personas reales en el sitio)**.
if (!defined('MURO_EN_LINEA_BASE'))   define('MURO_EN_LINEA_BASE', 27);
if (!defined('MURO_EN_LINEA_FACTOR')) define('MURO_EN_LINEA_FACTOR', 4);
if (!defined('MURO_EN_LINEA_MIN'))    define('MURO_EN_LINEA_MIN', 3);   // minutos sin latido = se fue
// ⚠️ LA IP: el jefe la quiere COMPLETA (igual que en su Telegram). Poniendo esto en `true` vuelve a
//    enmascararse (`181.176.•••.•••`) en un segundo. En el Telegram del jefe llega SIEMPRE completa.
if (!defined('MURO_IP_ENMASCARADA'))  define('MURO_IP_ENMASCARADA', false);

/**
 * Los tipos de aviso que son movimiento del público, con su icono, su TÍTULO (en mayúsculas, igual
 * que el mensaje de Telegram), quién lo hace y qué dice.
 * Lo que no está aquí no entra al chat (resúmenes del sistema, 404, errores, alertas del servidor).
 * ⚠️ Prohibido escribir «robot», «bot», «araña», «spider» o «navegador»: se dice **usuario**.
 */
function muro_tipos() {
    return [
        'busqueda'            => ['icono' => '🔍', 'titulo' => 'BÚSQUEDA EN EL BUSCADOR',              'quien' => 'Un usuario',    'txt' => 'buscó'],
        'pedido_sin_vendedor' => ['icono' => '🛒', 'titulo' => 'NADIE LO VENDE: ENCARGO PUBLICADO',    'quien' => 'El directorio', 'txt' => 'publicó un encargo nuevo'],
        'lead_precio'         => ['icono' => '💬', 'titulo' => 'PIDIERON PRECIO (WHATSAPP)',          'quien' => 'Un usuario',    'txt' => 'pidió precio en'],
        'llamada_tienda'      => ['icono' => '📞', 'titulo' => 'TOCARON «LLAMAR» EN UNA FICHA',       'quien' => 'Un usuario',    'txt' => 'tocó «Llamar» en'],
        'visita_tienda'       => ['icono' => '👁️', 'titulo' => 'VISITA A UNA TIENDA',                 'quien' => 'Un usuario',    'txt' => 'está viendo'],
        'visita_producto'     => ['icono' => '📦', 'titulo' => 'VISITA A UN PRODUCTO',                'quien' => 'Un usuario',    'txt' => 'está mirando un producto de'],
        'opinion_nueva'       => ['icono' => '⭐', 'titulo' => 'OPINIÓN NUEVA EN UNA FICHA',          'quien' => 'Un usuario',    'txt' => 'dejó una opinión en'],
        'reclamo'             => ['icono' => '🙋', 'titulo' => 'RECLAMO DE NEGOCIO',                  'quien' => 'Un usuario',    'txt' => 'dice que una ficha es suya'],
        'tienda_nueva'        => ['icono' => '🏪', 'titulo' => 'TIENDA NUEVA CREADA',                 'quien' => 'Un negocio',    'txt' => 'se acaba de publicar'],
        'producto_nuevo'      => ['icono' => '📥', 'titulo' => 'PRODUCTO NUEVO O EDITADO',            'quien' => 'Un negocio',    'txt' => 'publicó un producto nuevo en'],
        'foto_nueva'          => ['icono' => '📷', 'titulo' => 'FOTOS NUEVAS EN UNA FICHA',           'quien' => 'Un negocio',    'txt' => 'subió fotos nuevas en'],
        'producto_borrado'    => ['icono' => '🗑️', 'titulo' => 'PRODUCTO ELIMINADO',                  'quien' => 'Un negocio',    'txt' => 'quitó un producto de su ficha'],
        'usuario_nuevo'       => ['icono' => '👤', 'titulo' => 'USUARIO NUEVO REGISTRADO',            'quien' => 'Un usuario',    'txt' => 'acaba de crear su cuenta'],
        'empleo'              => ['icono' => '💼', 'titulo' => 'AVISO DE EMPLEO NUEVO',               'quien' => 'El directorio', 'txt' => 'publicó un aviso de empleo'],
        'postulante'          => ['icono' => '📨', 'titulo' => 'POSTULACIÓN (TRABAJA CON NOSOTROS)',  'quien' => 'Un usuario',    'txt' => 'postuló a un aviso de empleo'],
        'reporte_contenido'   => ['icono' => '🚩', 'titulo' => 'REPORTE DE CONTENIDO',                'quien' => 'Un usuario',    'txt' => 'reportó contenido'],
        'banner_clic'         => ['icono' => '📢', 'titulo' => 'CLIC EN UN BANNER',                   'quien' => 'Un usuario',    'txt' => 'tocó un anuncio'],
    ];
}

/**
 * 🟢 CUÁNTAS PERSONAS ESTÁN EN EL SITIO (para el contador del encabezado).
 * Se cuentan las sesiones con **latido reciente** (`ultimo_activo`), que es lo que el propio sitio
 * ya mide con `stats_latido()`.
 *
 * ⚠️ **La cifra que se MUESTRA es la que pidió el jefe**: `27 + 4 × reales` (mínimo 27 con el sitio
 *    vacío). No es el conteo crudo: es una decisión suya de escaparate («que el usuario vea que
 *    nuestro sitio web sí tiene poder»). El número real se guarda en `reales` por si algún día
 *    quiere volver al dato exacto: basta con cambiar `MURO_EN_LINEA_BASE` a 0 y el factor a 1.
 *
 * @return array{reales:int,mostrado:int}
 */
function muro_en_linea() {
    $n = 0;
    try {
        if (!function_exists('stats_hace_min')) {
            $est = __DIR__ . '/estadisticas.php';
            if (is_file($est)) require_once $est;
        }
        $desde = function_exists('stats_hace_min')
            ? stats_hace_min((int)MURO_EN_LINEA_MIN)
            : date('Y-m-d H:i:s', time() - (int)MURO_EN_LINEA_MIN * 60);
        $st = db()->prepare("SELECT COUNT(*) FROM directorio_stats_sesiones WHERE ultimo_activo >= ?");
        $st->execute([$desde]);
        $n = (int)$st->fetchColumn();
    } catch (Throwable $e) {
        $n = 0;   // sin datos: queda el mínimo
    }
    return [
        'reales'   => $n,
        'mostrado' => (int)MURO_EN_LINEA_BASE + (int)MURO_EN_LINEA_FACTOR * $n,
    ];
}

/** ¿Se puede leer el chat? */
function muro_ok() {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        db()->query('SELECT 1 FROM ' . MURO_TABLA_LOG . ' LIMIT 1');
        $ok = true;
    } catch (Throwable $e) {
        $ok = false;
    }
    return $ok;
}

/**
 * Los movimientos del público, del más nuevo al más viejo.
 *
 * ⚠️ DOS TRAMPAS QUE YA SE PAGARON AQUÍ (2026-09-15), no volver a caer:
 *  1) **El filtro de tipos va DENTRO de la subconsulta.** Cuando iba fuera (con un lote de 300), el
 *     chat se quedaba **VACÍO**: las últimas 300 filas de usuarios eran casi todas `pagina_404`
 *     (los 404 son la mitad del movimiento), así que al filtrar después no quedaba ni una. Ahora el
 *     `LIMIT` del lote se aplica a las filas que SÍ interesan, y el lote es de 2 000.
 *  2) **El orden de los parámetros:** el `?` del `id > ?` va ANTES que los del `tipo IN (…)` en el
 *     SQL, así que también en el array. Y dentro de la subconsulta la tabla no tiene alias (`l`).
 */
function muro_listar($limite = 25, $desde_id = 0) {
    if (!muro_ok()) return [];
    $limite   = max(1, min((int)MURO_TOPE, (int)$limite));
    $desde_id = max(0, (int)$desde_id);
    $tipos    = array_keys(muro_tipos());
    $ph       = implode(',', array_fill(0, count($tipos), '?'));
    $par = [];
    $w_desde = '';
    if ($desde_id > 0) {
        $w_desde = ' AND id > ?';
        $par[] = $desde_id;
    }
    foreach ($tipos as $t) $par[] = $t;
    try {
        $sql = "SELECT l.id, l.tipo, l.resumen, l.negocio_id, l.creado_en, l.ip, l.user_agent,
                       n.nombre AS tienda, n.slug AS tienda_slug,
                       c.nombre AS rubro, c.icono AS rubro_icono,
                       d.nombre AS distrito
                FROM (SELECT id, tipo, resumen, negocio_id, creado_en, ip, user_agent
                        FROM " . MURO_TABLA_LOG . "
                       WHERE es_bot = 0 $w_desde
                         AND estado IN ('enviado','agrupado')
                         AND tipo IN ($ph)
                       ORDER BY id DESC LIMIT " . (int)MURO_LOTE . ") l
                LEFT JOIN directorio_negocios   n ON n.id = l.negocio_id
                LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
                ORDER BY l.id DESC
                LIMIT " . (int)$limite;
        $st = db()->prepare($sql);
        $st->execute($par);
        return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        error_log('muro_listar: ' . $e->getMessage());
        return [];
    }
}

/** Solo los mensajes NUEVOS (id mayor que $desde) — lo que pide el AJAX cada 20 segundos. */
function muro_listar_desde($desde_id, $limite = 25) {
    return muro_listar($limite, (int)$desde_id);
}

/** El término de una búsqueda, sacado del resumen («cerveza (18 res.)» → «cerveza»). */
function muro_termino_de($resumen) {
    $t = trim((string)$resumen);
    $t = preg_replace('/\s*\(\d+\s*res\.?\)\s*$/u', '', $t);
    $t = preg_replace('/\s*\(\d+\s*búsq\..*$/u', '', (string)$t);
    $t = preg_replace('/\s*\(dejó su WhatsApp\)\s*$/u', '', (string)$t);
    return trim(mb_substr((string)$t, 0, 60));
}

if (!function_exists('muro_tiempo_txt')) {
    /** «hace 5 min», «hoy», «ayer»… para que el chat se sienta vivo. */
    function muro_tiempo_txt($fecha) {
        $t = strtotime((string)$fecha);
        if (!$t) return '';
        $min = (int)floor((time() - $t) / 60);
        if ($min < 1)  return 'ahora mismo';
        if ($min < 60) return 'hace ' . $min . ' min';
        $h = (int)floor($min / 60);
        if ($h < 24 && date('Y-m-d', $t) === date('Y-m-d')) return 'hace ' . $h . ($h === 1 ? ' hora' : ' horas');
        $dias = (int)floor((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', $t))) / 86400);
        if ($dias <= 0) return 'hoy';
        if ($dias === 1) return 'ayer';
        if ($dias < 30) return 'hace ' . $dias . ' días';
        return 'el ' . date('d/m/Y', $t);
    }
}

if (!function_exists('muro_ip_mostrar')) {
    /**
     * La IP tal como se enseña. **El jefe la quiere COMPLETA**, igual que en su Telegram (que es un
     * canal privado): con `MURO_IP_ENMASCARADA` en `true` vuelve a `181.176.•••.•••` en un segundo.
     *
     * 🔒 OJO (queda escrito aquí y en la guía): en el Telegram del jefe la IP completa es un dato
     * privado suyo; publicada en una web abierta, una IP completa **identifica a una persona** (se
     * puede geolocalizar y hasta atacar su conexión) y es un dato personal protegido (Ley 29733). El
     * jefe decidió mostrarla; si algún día llega una queja o quiere revertirlo, se cambia la
     * constante a `true` y queda enmascarada otra vez, sin tocar nada más.
     */
    function muro_ip_mostrar($ip) {
        $ip = trim((string)$ip);
        if ($ip === '' || defined('MURO_IP_ENMASCARADA') && MURO_IP_ENMASCARADA) {
            return muro_ip_parcial($ip);
        }
        return $ip;
    }
}

if (!function_exists('muro_ip_parcial')) {
    /**
     * La IP como se puede mostrar en una página ABIERTA: se conserva la red y se tapan los dos
     * últimos grupos (`190.117.•••.•••`).
     *
     * 🔒 POR QUÉ NO SE PUBLICA ENTERA (aunque en el Telegram del jefe sí salga completa): una IP
     * completa **identifica a una persona** (y con ella se puede geolocalizar y hasta atacarla), así
     * que es un dato personal; publicarla en una web abierta es un problema legal (Ley 29733) y de
     * confianza. Enmascarada da la misma información útil —de qué red viene, si repite, si es de la
     * zona— sin exponer a nadie. **En el Telegram del jefe sigue llegando completa: ese canal es
     * privado y suyo.**
     */
    function muro_ip_parcial($ip) {
        $ip = trim((string)$ip);
        if ($ip === '') return '';
        if (strpos($ip, ':') !== false) {                 // IPv6: se deja solo el primer bloque
            $p = explode(':', $ip);
            return $p[0] . ':••••';
        }
        $p = explode('.', $ip);
        if (count($p) !== 4) return '•••';
        return $p[0] . '.' . $p[1] . '.•••.•••';
    }
}

if (!function_exists('muro_dispositivo')) {
    /** 📱 móvil o 💻 computadora, sin depender de ningún otro archivo. */
    function muro_dispositivo($ua) {
        $ua = mb_strtolower((string)$ua);
        if ($ua === '') return '';
        if (preg_match('/android|iphone|ipad|ipod|mobile|windows phone|opera mini/', $ua)) return '📱 móvil';
        return '💻 computadora';
    }
}

/**
 * 🔒 EL MENSAJE SEGURO (nunca con el `resumen` crudo) — **con el mismo detalle que el Telegram**:
 * quién, qué hizo, cuántos resultados, la IP (enmascarada), la hora exacta, el dispositivo y la URL.
 * Devuelve ['id','tipo','icono','quien','texto','url','meta','detalles','hora','hora_larga','hace','n'].
 */
function muro_evento_datos($e) {
    $tipo    = (string)($e['tipo'] ?? '');
    $tienda  = trim((string)($e['tienda'] ?? ''));
    $slug    = trim((string)($e['tienda_slug'] ?? ''));
    $rubro   = trim((string)($e['rubro'] ?? ''));
    $icono_r = trim((string)($e['rubro_icono'] ?? ''));
    $dist    = trim((string)($e['distrito'] ?? ''));
    $t       = muro_tipos()[$tipo] ?? null;
    if (!$t) return null;

    $url = '';
    $quien = $t['quien'];
    $texto = '';

    switch ($tipo) {
        case 'busqueda':
            $term = muro_termino_de($e['resumen'] ?? '');
            if ($term === '') return null;
            // El título ya dice «BÚSQUEDA»: aquí va solo el término, como en el Telegram.
            $texto = '«' . $term . '»';
            $url   = url('buscar.php?q=' . rawurlencode($term));
            break;

        case 'pedido_sin_vendedor':
            $term = muro_termino_de($e['resumen'] ?? '');
            if ($term === '') return null;
            $texto = '«' . $term . '»';
            $url   = url('en-vivo');
            break;

        case 'usuario_nuevo':
            $texto = 'acaba de crear su cuenta en el sitio';
            break;

        case 'reclamo':
            $texto = 'dice que una ficha del directorio es suya';
            break;

        case 'reporte_contenido':
            $texto = 'reportó contenido en una ficha';
            $url   = url('en-vivo');
            break;

        case 'postulante':
            $texto = 'postuló a un aviso de empleo';
            $url   = url('empleos');
            break;

        case 'empleo':
            $texto = 'hay un aviso de empleo nuevo en Chimbote';
            $url   = url('empleos');
            break;

        case 'banner_clic':
            $texto = 'tocó un anuncio del sitio';
            break;

        case 'producto_borrado':
            $texto = 'quitó un producto de su ficha';
            break;

        case 'tienda_nueva':
            // ⛔ El resumen de este tipo trae usuario y clave: se usa SOLO el nombre de la tienda.
            $quien = 'Un negocio';
            $texto = ($tienda === '' ? 'Un negocio de Chimbote se acaba de publicar' : '');
            if ($slug !== '') $url = url_negocio($slug);
            break;

        default:
            // Todo lo que tiene tienda: el nombre va en la línea «🏪 …» (como el Telegram), así que
            // el cuerpo no repite nada.
            if ($tienda === '') {
                $texto = $t['txt'];
            } else {
                $texto = '';
                if ($slug !== '') $url = url_negocio($slug);
            }
            break;
    }

    $creado = (string)($e['creado_en'] ?? '');
    $term   = (in_array($tipo, ['busqueda', 'busqueda_vacia', 'pedido_sin_vendedor'], true) && !empty($term))
                ? (string)$term : '';

    // ----------------------------------------------------------------------------------
    // 🧾 LAS LÍNEAS DE DETALLE, CALCADAS DEL MENSAJE DE TELEGRAM DEL JEFE:
    //    🏪 la tienda · 👤 Anónimo · 📱 Móvil · ✅ por qué es persona · 🌐 IP · 🔗 URL · 🕐 fecha y hora
    // ----------------------------------------------------------------------------------
    $detalles = [];
    $es_visita = in_array($tipo, ['visita_tienda', 'visita_producto'], true);
    if ($tienda !== '') $detalles[] = '🏪 ' . $tienda;

    // 👤 Cómo se le identifica (igual que el Telegram: «👤 Anónimo · 📱 Móvil»)
    $quien_mira = !empty($e['usuario_id']) ? '👤 Usuario registrado' : '👤 Anónimo';
    $dispo = muro_dispositivo((string)($e['user_agent'] ?? ''));
    $detalles[] = $quien_mira . ($dispo !== '' ? ' · ' . $dispo : '');

    // ✅ POR QUÉ SE SABE QUE ES UNA PERSONA: el propio registro ya lo explica (solo en las visitas).
    if ($es_visita) {
        $porq = preg_replace('/^\s*·?\s*persona porque\s*/u', '', (string)($e['resumen'] ?? ''));
        $porq = trim(preg_replace('/\s+/u', ' ', (string)$porq));
        if ($porq !== '' && mb_strlen($porq) > 5) {
            $detalles[] = '✅ Persona real: ' . mb_substr($porq, 0, 150);
        }
    }

    // 🌐 La IP (el jefe la quiere completa, como en su Telegram: ver MURO_IP_ENMASCARADA).
    $ip_mostrar = muro_ip_mostrar((string)($e['ip'] ?? ''));
    if ($ip_mostrar !== '') {
        $detalles[] = '🌐 ' . $ip_mostrar . ($es_visita ? ' · navegando en el sitio' : '');
    }

    // 📄 Cuántos resultados le salieron (búsquedas)
    if ($tipo === 'busqueda' || $tipo === 'busqueda_vacia') {
        if (preg_match('/\((\d+)\s*res\.?\)/u', (string)($e['resumen'] ?? ''), $m)) {
            $n_res = (int)$m[1];
            $detalles[] = '📄 ' . $n_res . ' resultado' . ($n_res === 1 ? '' : 's')
                        . ($n_res === 0 ? ' · nadie lo tiene todavía' : '');
        }
    }

    // 🔗 La URL (como en el Telegram: la ruta visible)
    $ruta = '';
    if ($tipo === 'busqueda' && $term !== '')      $ruta = '/buscar.php?q=' . rawurlencode($term);
    elseif ($tipo === 'pedido_sin_vendedor')       $ruta = '/encargos';
    elseif ($slug !== '')                          $ruta = '/neg/' . $slug;
    elseif ($tipo === 'empleo' || $tipo === 'postulante') $ruta = '/empleos';
    if ($ruta !== '') $detalles[] = '🔗 https://dechimbote.com' . $ruta;

    // 🕐 La fecha y la hora (como el Telegram: «15/09 10:47»)
    if ($creado !== '') $detalles[] = '🕐 ' . date('d/m H:i', strtotime($creado));

    return [
        'id'         => (int)($e['id'] ?? 0),
        'tipo'       => $tipo,
        'icono'      => $t['icono'],
        'titulo'     => $t['titulo'],
        'quien'      => $quien,
        'texto'      => $texto,
        'url'        => $url,
        'meta'       => trim(($rubro !== '' ? $icono_r . ' ' . $rubro : '') . ($dist !== '' ? ' · ' . $dist : ''), ' ·'),
        'detalles'   => $detalles,
        'hora'       => $creado !== '' ? date('H:i', strtotime($creado)) : '',
        'hora_larga' => $creado !== '' ? date('d/m/Y H:i:s', strtotime($creado)) : '',
        'hace'       => muro_tiempo_txt($creado),
        // El término buscado: lo usa la acción del clic («yo lo vendo» → el encargo de ese término).
        'termino'    => $term,
        'n'          => 1,
    ];
}

/**
 * Convierte filas en mensajes listos para pintar (agrupando los iguales seguidos) + el id mayor.
 * @return array{items:array,ultimo:int}
 */
function muro_items($filas) {
    $items  = [];
    $ultimo = 0;
    foreach ($filas as $e) {
        $id = (int)($e['id'] ?? 0);
        if ($id > $ultimo) $ultimo = $id;
        $d = muro_evento_datos($e);
        if (!$d) continue;
        $ult = $items ? $items[count($items) - 1] : null;
        if ($ult && $ult['texto'] === $d['texto'] && $ult['meta'] === $d['meta']) {
            $items[count($items) - 1]['n'] = (int)$ult['n'] + 1;
            continue;
        }
        $items[] = $d;
    }
    return ['items' => $items, 'ultimo' => $ultimo];
}

/** Un mensaje del chat, en HTML (burbuja con avatar, quién, texto y hora). Es CLICABLE. */
function muro_msg_html($d, $nuevo = false) {
    $term = (string)($d['termino'] ?? '');
    $h  = '<div class="mchat__msg mchat__msg--toca' . ($nuevo ? ' mchat__msg--nuevo' : '') . '"'
        . ' data-id="' . (int)$d['id'] . '"'
        . ($term !== '' ? ' data-termino="' . e($term) . '"' : '')
        . ' role="button" tabindex="0" title="Toca para ofrecer lo tuyo">';
    $h .= '<span class="mchat__av">' . $d['icono'] . '</span>';
    $h .= '<div class="mchat__burbuja">';
    // 🧾 Igual que el mensaje de Telegram: TÍTULO en mayúsculas, el dato, y las líneas de detalle.
    if (!empty($d['titulo'])) $h .= '<span class="mchat__titulo">' . e((string)$d['titulo']) . '</span>';
    $h .= '<span class="mchat__quien">' . e((string)$d['quien']) . '</span>';
    if (trim((string)$d['texto']) !== '') {
        $h .= '<span class="mchat__texto">';
        $h .= ($d['url'] !== '')
            ? '<a href="' . e($d['url']) . '">' . e((string)$d['texto']) . '</a>'
            : e((string)$d['texto']);
        if ((int)($d['n'] ?? 1) > 1) $h .= ' <b class="mchat__x">×' . (int)$d['n'] . '</b>';
        $h .= '</span>';
    }
    if (!empty($d['meta'])) $h .= '<span class="mchat__meta">' . e((string)$d['meta']) . '</span>';
    // 📋 Las líneas de detalle: igual que el mensaje de Telegram (resultados, IP, hora, dispositivo, URL).
    if (!empty($d['detalles'])) {
        $h .= '<span class="mchat__det">';
        foreach ($d['detalles'] as $lin) {
            $h .= '<span>' . e((string)$lin) . '</span>';
        }
        $h .= '</span>';
    }
    $h .= '<span class="mchat__hora">' . e((string)($d['hora'] ?? '')) . '</span>';
    // El «Yo lo vendo» solo se ofrece donde tiene sentido: cuando el mensaje trae algo que alguien
    // está buscando (búsqueda o encargo). En el resto, el clic sigue abriendo el panel, pero sin
    // prometer lo que no hay.
    if ($term !== '') $h .= '<span class="mchat__yo">🛠️ Yo lo vendo ›</span>';
    $h .= '</div></div>';
    return $h;
}

/**
 * 💬 EL CHAT. Lo usan la página de encargos (protagonista) y la portada (versión corta).
 *
 * @param int  $n     cuántos mensajes se pintan de entrada
 * @param int  $alto  alto del cuerpo en px (0 = el de por defecto)
 * @param bool $pie   pintar la barra de abajo con el botón «Publicar lo mío»
 */
function muro_chat_html($n = 12, $alto = 0, $pie = true) {
    $filas = muro_listar(max($n, 40));            // se traen más de los que se pintan: son los que
    $pack  = muro_items($filas);                  // el navegador RECICLA para no quedarse quieto
    if (!$pack['items']) return '';               // sin movimiento no se pinta nada (nunca un chat vacío)

    $visibles = array_slice($pack['items'], 0, (int)$n);
    $cola     = array_slice($pack['items'], 0, 40);
    // Los movimientos de la última hora (dato real, para el rótulo del encabezado).
    $ultima_hora = 0;
    foreach ($pack['items'] as $d) {
        $min = 0;
        if (!empty($d['hace'])) {
            if ($d['hace'] === 'ahora mismo') $min = 0;
            elseif (preg_match('/hace (\d+) min/u', (string)$d['hace'], $m)) $min = (int)$m[1];
            else $min = 999;
        }
        if ($min < 60) $ultima_hora++;
    }

    $h  = muro_estilos();
    $h .= '<div class="mchat" id="mchat"' . ($alto > 0 ? ' style="--mchat-alto:' . (int)$alto . 'px"' : '') . '>';
    $h .= '<div class="mchat__cab">';
    $h .= '<span class="mchat__punto"></span>';
    // 📛 EL NOMBRE QUE ELIGIÓ EL JEFE (2026-09-15): «Información en vivo» (antes «Chimbote en vivo»).
    $h .= '<b>Información en vivo</b>';
    // 🟢 EL CONTADOR DE GENTE EN LÍNEA (con destello verde): la cifra que pidió el jefe,
    //    `27 + 4 × personas reales`. Se refresca sola con el AJAX del chat.
    $linea = muro_en_linea();
    $h .= '<span class="mchat__enlinea" id="mchatEnLinea" data-base="' . (int)MURO_EN_LINEA_BASE
        . '" data-factor="' . (int)MURO_EN_LINEA_FACTOR . '">'
        . '<span class="mchat__destello">✦</span> <b>' . (int)$linea['mostrado'] . '</b> en línea</span>';
    if ($ultima_hora > 0) $h .= '<span class="mchat__badge">' . (int)$ultima_hora . ' movimientos en la última hora</span>';
    $h .= '</div>';
    // data-logueado / data-csrf / data-accion: los usa el clic («yo lo vendo» → api/muro_accion.php).
    $logueado = function_exists('usuario_actual') && usuario_actual();
    $h .= '<div class="mchat__cuerpo" id="mchatCuerpo" data-fuente="' . e(url('api/muro_json.php'))
        . '" data-ultimo="' . (int)$pack['ultimo'] . '" data-tope="' . (int)MURO_TOPE
        . '" data-ritmo="' . (int)MURO_RITMO_MS . '"'
        . ' data-accion="' . e(url('api/muro_accion.php')) . '"'
        . ' data-csrf="' . e(function_exists('csrf_token') ? csrf_token() : '') . '"'
        . ' data-logueado="' . ($logueado ? '1' : '0') . '">';
    foreach ($visibles as $d) $h .= muro_msg_html($d);
    $h .= '</div>';

    // 💬 EL PANEL DEL CLIC: aparece cuando alguien toca un mensaje (y el chat se detiene).
    $h .= '<div class="mchat__panel" id="mchatPanel" hidden>';
    $h .= '<button type="button" class="mchat__cerrar" id="mchatCerrar" aria-label="Cerrar y seguir el chat">✕</button>';
    $h .= '<div class="mchat__panel-cuerpo" id="mchatPanelCuerpo"></div>';
    $h .= '</div>';

    if ($pie) {
        $h .= '<div class="mchat__pie">';
        // 📲 El jefe lo pidió así: *«ofrece ahí la opción de recibir estos mensajes en mi Telegram…
        //    el usuario que quiera que le envíen va a poner ahí su Telegram y le va a llegar todo»*.
        // ⛔ SIN ANCLAS (orden del jefe, 2026-09-21): los dos botones llevaban a `en-vivo#telegram` y
        //    `en-vivo#pedir` (saltos dentro de la página de En vivo). Ahora llevan a la página, que es
        //    donde están las dos cosas.
        $h .= '<a class="mchat__cta mchat__cta--tg" href="' . e(url('en-vivo')) . '">'
            . '📲 Recibir estos mensajes en mi Telegram</a>';
        $h .= '<a class="mchat__cta" href="' . e(url('en-vivo')) . '">🛠️ Publicar lo mío</a>';
        $h .= '<a class="mchat__cta mchat__cta--suave" href="' . e(url('crear-tienda?modo=producto')) . '">➕ Subir un producto</a>';
        $h .= '</div>';
    }
    $h .= '</div>';
    // La cola de reciclado: lo que el navegador repetirá (con su hora real) cuando no haya novedades.
    $h .= '<script type="application/json" id="mchatCola">'
        . json_encode($cola, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
        . '</script>';
    $h .= muro_chat_js();
    return $h;
}

/**
 * 💬 EL MOTOR DEL CHAT (JavaScript): un mensaje cada 2 segundos + el CLIC que lo detiene.
 *   1) Primero suelta los NUEVOS de verdad (los pide al servidor cada 20 s).
 *   2) Cuando no hay nuevos, RECICLA los últimos (el jefe: *«si es necesario repitiendo las
 *      publicaciones de abajo arriba… nunca estático»*), respetando su hora original.
 *   3) Mantiene 150 mensajes: al entrar uno, el primero sale.
 *   4) 🔴 **El CLIC detiene el chat** y abre el panel: «yo lo vendo» → se pide el WhatsApp del
 *      vendedor → y solo entonces se le entrega el del cliente (o se le invita a registrarse).
 *      Al cerrar el panel, el chat sigue donde estaba.
 */
function muro_chat_js() {
    static $hecho = false;
    if ($hecho) return '';
    $hecho = true;
    return <<<'JS'
<script>
(function () {
  'use strict';
  var cuerpo = document.getElementById('mchatCuerpo');
  if (!cuerpo || !window.fetch) return;
  var fuente   = cuerpo.getAttribute('data-fuente') || '';
  var accion   = cuerpo.getAttribute('data-accion') || '';
  var csrf     = cuerpo.getAttribute('data-csrf') || '';
  var logueado = cuerpo.getAttribute('data-logueado') === '1';
  var ultimo   = parseInt(cuerpo.getAttribute('data-ultimo') || '0', 10) || 0;
  var tope     = parseInt(cuerpo.getAttribute('data-tope') || '150', 10) || 150;
  var ritmo    = parseInt(cuerpo.getAttribute('data-ritmo') || '2000', 10) || 2000;
  var panel    = document.getElementById('mchatPanel');
  var panelC   = document.getElementById('mchatPanelCuerpo');
  var cerrar   = document.getElementById('mchatCerrar');

  var cola = [];
  try {
    var bruto = document.getElementById('mchatCola');
    if (bruto) cola = JSON.parse(bruto.textContent || '[]') || [];
  } catch (e) { cola = []; }
  var reciclado = [];
  var pausado = false;

  function alFinal() { return (cuerpo.scrollHeight - cuerpo.scrollTop - cuerpo.clientHeight) < 60; }

  function burbuja(d) {
    var msg = document.createElement('div');
    msg.className = 'mchat__msg mchat__msg--toca mchat__msg--nuevo';
    msg.setAttribute('data-id', d.id || 0);
    if (d.termino) msg.setAttribute('data-termino', d.termino);
    msg.setAttribute('role', 'button');
    msg.setAttribute('tabindex', '0');
    var av = document.createElement('span');
    av.className = 'mchat__av';
    av.textContent = d.icono || '•';
    var b = document.createElement('div');
    b.className = 'mchat__burbuja';
    if (d.titulo) {
      var ti = document.createElement('span');
      ti.className = 'mchat__titulo';
      ti.textContent = d.titulo;
      b.appendChild(ti);
    }
    var q = document.createElement('span');
    q.className = 'mchat__quien';
    q.textContent = d.quien || 'Un usuario';
    var t = document.createElement('span');
    t.className = 'mchat__texto';
    if (d.url) {
      var a = document.createElement('a');
      a.href = d.url;
      a.textContent = d.texto || '';
      t.appendChild(a);
    } else { t.textContent = d.texto || ''; }
    if (d.n && d.n > 1) {
      var x = document.createElement('b');
      x.className = 'mchat__x';
      x.textContent = ' ×' + d.n;
      t.appendChild(x);
    }
    b.appendChild(q);
    if (d.texto && String(d.texto).trim() !== '') b.appendChild(t);
    if (d.meta) {
      var m = document.createElement('span');
      m.className = 'mchat__meta';
      m.textContent = d.meta;
      b.appendChild(m);
    }
    if (d.detalles && d.detalles.length) {
      var det = document.createElement('span');
      det.className = 'mchat__det';
      d.detalles.forEach(function (lin) {
        var s = document.createElement('span');
        s.textContent = lin;
        det.appendChild(s);
      });
      b.appendChild(det);
    }
    var ho = document.createElement('span');
    ho.className = 'mchat__hora';
    ho.textContent = d.hora || '';
    b.appendChild(ho);
    var yo = document.createElement('span');
    yo.className = 'mchat__yo';
    yo.textContent = '🛠️ Yo lo vendo ›';
    if (d.termino) b.appendChild(yo);
    msg.appendChild(av); msg.appendChild(b);
    return msg;
  }

  function soltar() {
    if (pausado) return;
    var d = null;
    if (cola.length) { d = cola.shift(); }
    else if (reciclado.length) { d = reciclado[0]; reciclado.push(reciclado.shift()); }
    if (!d) return;
    var pegado = alFinal();
    cuerpo.appendChild(burbuja(d));
    while (cuerpo.children.length > tope) cuerpo.removeChild(cuerpo.firstChild);
    if (pegado) cuerpo.scrollTop = cuerpo.scrollHeight;
  }

  function traer() {
    if (pausado || document.hidden || !fuente) return;
    fetch(fuente + '?desde=' + ultimo, { cache: 'no-store' })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (!d || !d.ok) return;
        if (d.ultimo) { ultimo = d.ultimo; cuerpo.setAttribute('data-ultimo', ultimo); }
        // 🟢 El contador de gente en línea se refresca solo (base + factor × reales).
        if (d.en_linea) {
          var el = document.getElementById('mchatEnLinea');
          if (el && el.querySelector('b')) el.querySelector('b').textContent = d.en_linea;
        }
        if (d.items && d.items.length) {
          for (var i = d.items.length - 1; i >= 0; i--) cola.push(d.items[i]);
          reciclado = d.items.slice().reverse().concat(reciclado).slice(0, 40);
        }
      })
      .catch(function () {});
  }

  /* ============ 🔴 EL CLIC: se DETIENE el chat y aparecen las opciones ============ */
  function pausar()   { pausado = true;  cuerpo.classList.add('mchat__cuerpo--pausa'); }
  function reanudar() {
    pausado = false;
    cuerpo.classList.remove('mchat__cuerpo--pausa');
    if (panel) panel.hidden = true;
    var act = cuerpo.querySelector('.mchat__msg--activo');
    if (act) act.classList.remove('mchat__msg--activo');
    cuerpo.scrollTop = cuerpo.scrollHeight;
  }

  function parrafo(txt, clase) {
    var p = document.createElement('p');
    p.className = 'mchat__panel-txt ' + (clase || '');
    p.textContent = txt;
    return p;
  }

  function boton(texto, url, clase) {
    var a = document.createElement('a');
    a.className = 'mchat__opcion ' + (clase || '');
    a.href = url;
    a.textContent = texto;
    return a;
  }

  function pintarAcciones(lista) {
    if (!lista || !lista.length) return;
    var caja = document.createElement('div');
    caja.className = 'mchat__opciones';
    lista.forEach(function (a) {
      if (!a || !a.url) return;
      caja.appendChild(boton(a.texto || 'Ver', a.url, a.tipo === 'wa' ? 'mchat__opcion--wa' : ''));
    });
    if (caja.children.length) panelC.appendChild(caja);
  }

  /* 🔴 Se le PIDE su WhatsApp (y hasta que no lo deja, no se le da el del cliente). */
  function formularioWa() {
    var f = document.createElement('div');
    f.className = 'mchat__wa';
    var l = document.createElement('label');
    l.setAttribute('for', 'mchatWaInput');
    l.textContent = 'Tu WhatsApp (te lo pido para pasarte el dato del cliente):';
    var inp = document.createElement('input');
    inp.type = 'tel';
    inp.id = 'mchatWaInput';
    inp.inputMode = 'numeric';
    inp.maxLength = 15;
    inp.placeholder = '9XX XXX XXX';
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'mchat__opcion mchat__opcion--principal';
    b.textContent = '✅ Continuar';
    b.addEventListener('click', function () {
      var v = (inp.value || '').replace(/\D+/g, '');
      if (v.length < 9) { inp.focus(); inp.classList.add('mchat__wa--mal'); return; }
      preguntar(v);
    });
    inp.addEventListener('keydown', function (ev) { if (ev.key === 'Enter') b.click(); });
    f.appendChild(l); f.appendChild(inp); f.appendChild(b);
    return f;
  }

  function preguntar(wa) {
    var cab = panel.getAttribute('data-msg') || '';
    panelC.innerHTML = '';
    if (cab) panelC.appendChild(parrafo(cab, 'mchat__panel-mensaje'));
    panelC.appendChild(parrafo('Un momento…', 'mchat__panel-cargando'));
    var datos = new URLSearchParams();
    datos.append('id', panel.getAttribute('data-id') || '0');
    datos.append('wa', wa || '');
    datos.append('_csrf', csrf);
    fetch(accion, { method: 'POST', body: datos, credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); })
      .then(function (d) { pintar(d); })
      .catch(function () {
        var cab = panel.getAttribute('data-msg') || '';
        panelC.innerHTML = '';
        if (cab) panelC.appendChild(parrafo(cab, 'mchat__panel-mensaje'));
        panelC.appendChild(parrafo('No pudimos conectar. Revisa tu internet e inténtalo otra vez.', 'mchat__panel-mal'));
      });
  }

  function pintar(d) {
    var cab = panel.getAttribute('data-msg') || '';
    panelC.innerHTML = '';
    if (cab) panelC.appendChild(parrafo(cab, 'mchat__panel-mensaje'));
    if (!d) { panelC.appendChild(parrafo('No pudimos leer la respuesta.', 'mchat__panel-mal')); return; }

    if (d.estado === 'pide_wa') {
      panelC.appendChild(parrafo(d.mensaje || 'Déjanos tu WhatsApp.'));
      panelC.appendChild(formularioWa());
      return;
    }
    if (d.estado === 'wa_invalido') {
      panelC.appendChild(parrafo('⚠️ ' + (d.mensaje || 'Ese número no parece un WhatsApp.'), 'mchat__panel-mal'));
      panelC.appendChild(formularioWa());
      return;
    }
    if (d.estado === 'tope' || d.estado === 'no_existe' || d.estado === 'sin_id') {
      panelC.appendChild(parrafo('⚠️ ' + (d.mensaje || 'No pudimos continuar.'), 'mchat__panel-mal'));
      return;
    }
    if (d.estado === 'no_registrado') {
      panelC.appendChild(parrafo('🚫 ' + (d.mensaje || 'Ese número no está registrado en esta tienda.'), 'mchat__panel-mal'));
      if (d.registro && d.registro.url) {
        var caja = document.createElement('div');
        caja.className = 'mchat__opciones';
        caja.appendChild(boton(d.registro.texto || '📝 Registrarme gratis', d.registro.url, 'mchat__opcion--principal'));
        panelC.appendChild(caja);
      }
      pintarAcciones(d.acciones);
      return;
    }
    if (d.estado === 'sin_comprador') {
      panelC.appendChild(parrafo('👋 ' + (d.mensaje || 'Este cliente no dejó su número.')));
      pintarAcciones(d.acciones);
      return;
    }
    if (d.estado === 'ok' && d.comprador) {
      panelC.appendChild(parrafo('✅ ' + (d.mensaje || 'Aquí tienes el dato del cliente.')));
      var caja2 = document.createElement('div');
      caja2.className = 'mchat__cliente';
      var t = document.createElement('span');
      t.className = 'mchat__cliente-t';
      t.textContent = '📱 ' + (d.comprador.enmascara || d.comprador.enmascarado || d.comprador.wa || '');
      caja2.appendChild(t);
      if (d.termino) {
        var tm = document.createElement('span');
        tm.className = 'mchat__cliente-n';
        tm.textContent = '🔎 Busca: ' + d.termino;
        caja2.appendChild(tm);
      }
      if (d.comprador.nota) {
        var n = document.createElement('span');
        n.className = 'mchat__cliente-n';
        n.textContent = '📝 «' + d.comprador.nota + '»';
        caja2.appendChild(n);
      }
      panelC.appendChild(caja2);
      pintarAcciones(d.acciones);
      return;
    }
    panelC.appendChild(parrafo(d.mensaje || 'Listo.'));
    pintarAcciones(d.acciones);
  }

  function abrir(el) {
    if (!panel || !panelC) return;
    pausar();
    var previo = cuerpo.querySelector('.mchat__msg--activo');
    if (previo) previo.classList.remove('mchat__msg--activo');
    el.classList.add('mchat__msg--activo');
    panel.setAttribute('data-id', el.getAttribute('data-id') || '0');
    var txt = el.querySelector('.mchat__texto');
    var tit = el.querySelector('.mchat__titulo');
    // En el panel se muestra el mensaje tocado: su texto o, si el mensaje solo lleva detalle, su título.
    var cab = txt ? txt.textContent : (tit ? tit.textContent : '');
    panel.setAttribute('data-msg', cab);
    panel.hidden = false;

    panelC.innerHTML = '';
    panelC.appendChild(parrafo(cab, 'mchat__panel-mensaje'));

    if (logueado) {
      // Ya sabemos quién es: directo a «yo lo vendo» y al WhatsApp del cliente si lo dejó.
      preguntar('');
    } else {
      panelC.appendChild(parrafo('¿Lo tienes? Déjanos tu WhatsApp y te pasamos el dato del cliente.'));
      panelC.appendChild(formularioWa());
    }
  }

  cuerpo.addEventListener('click', function (ev) {
    if (ev.target.closest('a')) return;          // los enlaces de dentro siguen funcionando
    var m = ev.target.closest('.mchat__msg');
    if (m) abrir(m);
  });
  cuerpo.addEventListener('keydown', function (ev) {
    if (ev.key !== 'Enter' && ev.key !== ' ') return;
    var m = ev.target.closest('.mchat__msg');
    if (m) { ev.preventDefault(); abrir(m); }
  });
  if (cerrar) cerrar.addEventListener('click', reanudar);

  // La ronda de reciclado arranca con lo que ya está pintado.
  (function () {
    var vistos = [];
    var nodos = cuerpo.querySelectorAll('.mchat__msg');
    for (var i = nodos.length - 1; i >= 0 && vistos.length < 40; i--) {
      var a = nodos[i].querySelector('.mchat__texto a');
      vistos.push({ id: nodos[i].getAttribute('data-id') || 0,
                    termino: nodos[i].getAttribute('data-termino') || '',
                    icono: (nodos[i].querySelector('.mchat__av') || {}).textContent || '•',
                    quien: (nodos[i].querySelector('.mchat__quien') || {}).textContent || 'Un usuario',
                    texto: (nodos[i].querySelector('.mchat__texto') || {}).textContent || '',
                    url: a ? a.getAttribute('href') : '',
                    meta: (nodos[i].querySelector('.mchat__meta') || {}).textContent || '',
                    hora: (nodos[i].querySelector('.mchat__hora') || {}).textContent || '',
                    n: 1 });
    }
    reciclado = vistos;
  })();

  cuerpo.scrollTop = cuerpo.scrollHeight;
  setInterval(soltar, ritmo);       // 💬 un mensaje cada 2 segundos, siempre
  setInterval(traer, 20000);        // 🔄 y cada 20 s se pregunta si hay nuevos de verdad
  traer();
})();
</script>
JS;
}

/**
 * 🏠 EL BLOQUE DE LA PORTADA (pedido del jefe): título «🛒 Nuevos pedidos sin vendedor», los encargos
 * abiertos y el chat en vivo debajo. Si no hay nada, devuelve '' (nunca un bloque vacío).
 */
function muro_portada_html($encargos = 3, $n = 6) {
    if (!function_exists('pedidos_listar')) {
        $motor = __DIR__ . '/pedidos_sin_vendedor.php';
        if (is_file($motor)) require_once $motor;
    }
    $abiertos = [];
    if (function_exists('pedidos_listar')) $abiertos = pedidos_listar('abierto', (int)$encargos, 0);
    $chat = muro_chat_html($n, 300, true);   // con su pie: incluye «📲 Recibir estos mensajes en mi Telegram»
    if ($chat === '' && !$abiertos) return '';

    $h  = '<section class="seccion muro-portada" id="en-vivo">';
    $h .= '<div class="muro-portada__cab">';
    // El nombre del módulo es «Información en vivo» (el jefe, 2026-09-15); su frase de la portada
    // («nuevos pedidos sin vendedor») vive en el subtítulo.
    $h .= '<h2 class="seccion__titulo" style="margin:0">🔴 Información en vivo</h2>';
    $h .= '<p class="muro-portada__sub">Lo que está pasando en Chimbote ahora mismo — y los '
        . '<b>nuevos pedidos sin vendedor</b>: lo que la gente busca y nadie tiene. Se actualiza solo, '
        . 'sin recargar.</p>';
    $h .= '</div>';

    if ($abiertos) {
        $h .= '<div class="muro-portada__pedidos">';
        foreach ($abiertos as $p) {
            $n2     = (int)($p['buscado_n'] ?? 1);
            $vistos = ($p['resultados'] === null) ? null : (int)$p['resultados'];
            $tipo_p = ((string)($p['tipo'] ?? 'sin_vendedor') === 'pocos') ? 'pocos' : 'sin_vendedor';
            $h .= '<a class="muro-portada__pedido" href="' . e(function_exists('pedido_url')
                    ? pedido_url((string)$p['slug']) : url('en-vivo')) . '">';
            $h .= '<span class="muro-portada__pide">🔎 Buscan <b>' . e((string)$p['termino']) . '</b></span>';
            $h .= '<span class="muro-portada__estado">' . ($tipo_p === 'pocos'
                    ? ($vistos !== null && $vistos > 0 ? 'Solo ' . $vistos . ' tienda' . ($vistos === 1 ? '' : 's') . ' lo tiene' . ($vistos === 1 ? '' : 'n') : 'Muy pocos lo tienen')
                    : 'Nadie lo vende') . '</span>';
            if ($n2 > 1) $h .= '<span class="muro-portada__fuego">🔥 ' . $n2 . ' personas lo buscan</span>';
            $h .= '</a>';
        }
        $h .= '</div>';
    }

    if ($chat !== '') $h .= $chat;

    $h .= '<p style="text-align:center;margin:14px 0 0">'
        . '<a class="mchat__cta" style="display:inline-flex;min-height:46px;padding:11px 20px" href="'
        . e(url('en-vivo')) . '">Ver toda la información en vivo y ofrecer lo tuyo →</a></p>';
    $h .= '</section>';
    return $h;
}

/** Los estilos del chat y del bloque de portada (una sola vez por página). */
function muro_estilos() {
    static $hecho = false;
    if ($hecho) return '';
    $hecho = true;
    return '<style>
/* ============ 💬 EL CHAT EN VIVO ============ */
/* ⬛ EL CHAT VA A TODO EL ANCHO (pedido del jefe: *«procura ocupar todo el ancho de la página»*):
   sin límite de ancho, solo un margen pequeño para que no toque los bordes. */
.mchat-ancho{width:100%;max-width:100%;margin:0 0 18px;padding:0 clamp(8px,2vw,26px);box-sizing:border-box}
.mchat{background:linear-gradient(180deg,#fbf7f1 0%,#fff 100%);border:1px solid var(--color-borde,#e8ddd0);
    border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.06);width:100%;max-width:100%}
.mchat__cab{display:flex;align-items:center;gap:7px;padding:11px 13px;background:#fff;
    border-bottom:1px solid var(--color-borde,#e8ddd0);font-size:15px;color:var(--marca-granate,#6d071a);flex-wrap:wrap}
.mchat__cab b{font-weight:800}
.mchat__sub{font-size:12.5px;color:#8a8a8a;font-weight:600}
/* 🟢 GENTE EN LÍNEA (con destello verde delante del número) */
.mchat__enlinea{display:inline-flex;align-items:center;gap:5px;font-size:12.5px;font-weight:700;color:#15803d;
    background:#dcfce7;border-radius:999px;padding:3px 10px;white-space:nowrap}
.mchat__enlinea b{font-size:14px;font-weight:900}
.mchat__destello{color:#22c55e;font-size:12px;animation:mchatDestello 1.6s infinite}
@keyframes mchatDestello{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.35;transform:scale(1.35)}}
.mchat__badge{margin-left:auto;font-size:11.5px;font-weight:800;color:#166534;background:#f0fdf4;
    border-radius:999px;padding:3px 9px;white-space:nowrap}
.mchat__punto{width:9px;height:9px;border-radius:50%;background:#16a34a;flex:0 0 auto;
    animation:mchatPulso 1.8s infinite}
@keyframes mchatPulso{0%{box-shadow:0 0 0 0 rgba(22,163,74,.55)}70%{box-shadow:0 0 0 9px rgba(22,163,74,0)}100%{box-shadow:0 0 0 0 rgba(22,163,74,0)}}
.mchat__cuerpo{height:var(--mchat-alto,340px);overflow-y:auto;overscroll-behavior:contain;
    padding:12px 11px;display:flex;flex-direction:column;gap:9px;scroll-behavior:smooth}
.mchat__msg{display:flex;gap:8px;align-items:flex-start;max-width:100%}
.mchat__av{width:32px;height:32px;flex:0 0 auto;border-radius:50%;background:#fff;border:1px solid var(--color-borde,#e8ddd0);
    display:flex;align-items:center;justify-content:center;font-size:15px;box-shadow:0 1px 3px rgba(0,0,0,.06)}
.mchat__burbuja{position:relative;background:#fff;border:1px solid #eee5da;border-radius:4px 14px 14px 14px;
    padding:9px 12px;max-width:100%;box-shadow:0 1px 3px rgba(0,0,0,.05);display:flex;flex-direction:column;gap:2px}
/* 🧾 EL TÍTULO del mensaje, igual que el Telegram (mayúsculas y negrita) */
.mchat__titulo{font-size:12px;font-weight:900;letter-spacing:.4px;color:#1f2937;
    text-transform:uppercase;margin-bottom:3px}
.mchat__quien{font-size:12px;font-weight:800;color:var(--marca-granate,#6d071a)}
.mchat__texto{font-size:14.5px;line-height:1.45;color:#2f2f2f}
.mchat__texto a{color:var(--marca-granate,#6d071a);font-weight:700;text-decoration:none}
.mchat__meta{font-size:11.5px;color:#8a8a8a;font-weight:600}
.mchat__hora{font-size:10.5px;color:#b0aaa2;align-self:flex-end}
.mchat__x{color:var(--marca-naranja,#e07a1f);font-weight:800}
/* 👆 El mensaje se puede TOCAR: al pasar por encima (o al tocarlo) se ve «Yo lo vendo». */
.mchat__msg--toca{cursor:pointer}
.mchat__msg--toca .mchat__burbuja{transition:border-color .15s,box-shadow .15s}
.mchat__msg--toca:hover .mchat__burbuja{border-color:var(--marca-naranja,#e07a1f);
    box-shadow:0 2px 8px rgba(224,122,31,.18)}
.mchat__msg--activo .mchat__burbuja{border-color:var(--marca-naranja,#e07a1f);background:#fff8ed}
.mchat__yo{display:none;font-size:11.5px;font-weight:800;color:var(--marca-naranja,#e07a1f);
    border-top:1px dashed #f1e5d6;margin-top:5px;padding-top:4px;text-align:right}
.mchat__msg--toca:hover .mchat__yo,.mchat__msg--activo .mchat__yo{display:block}
.mchat__cuerpo--pausa{box-shadow:inset 0 0 0 2px rgba(224,122,31,.35)}
/* 💬 EL PANEL DEL CLIC (el chat se detiene mientras está abierto) */
.mchat__panel{border-top:2px solid var(--marca-naranja,#e07a1f);background:#fffdf8;padding:13px;
    position:relative;animation:mchatPanel .25s ease both}
@keyframes mchatPanel{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:none}}
.mchat__cerrar{position:absolute;top:9px;right:9px;width:32px;height:32px;border-radius:50%;
    border:1px solid var(--color-borde,#e8ddd0);background:#fff;font-size:14px;font-weight:800;
    cursor:pointer;color:#666;line-height:1;font-family:inherit}
.mchat__panel-txt{margin:0 0 9px;font-size:14.5px;line-height:1.5;color:#333}
.mchat__panel-mensaje{font-weight:700;color:var(--marca-granate,#6d071a);padding-right:34px;margin-bottom:6px}
.mchat__panel-mal{color:#b91c1c;font-weight:700}
.mchat__panel-cargando{color:#8a8a8a;margin:2px 0}
.mchat__opciones{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
.mchat__opcion{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:44px;
    padding:10px 15px;border-radius:11px;background:#fff;border:1.5px solid var(--color-borde,#e8ddd0);
    color:var(--marca-granate,#6d071a);font-size:14.5px;font-weight:800;text-decoration:none;
    cursor:pointer;font-family:inherit;flex:1 1 auto;text-align:center}
.mchat__opcion--principal{background:var(--marca-naranja,#e07a1f);border-color:var(--marca-naranja,#e07a1f);color:#fff}
.mchat__opcion--wa{background:#25d366;border-color:#25d366;color:#063a1d}
.mchat__wa{display:flex;flex-direction:column;gap:8px;margin-top:4px}
.mchat__wa label{font-size:13.5px;font-weight:700;color:#555}
.mchat__wa input{width:100%;box-sizing:border-box;font-size:16px;padding:11px 12px;
    border:1.5px solid var(--color-borde,#e8ddd0);border-radius:10px;font-family:inherit}
.mchat__wa--mal{border-color:#dc2626!important;background:#fef2f2}
.mchat__cliente{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:11px;padding:10px 12px;
    margin:0 0 8px;display:flex;flex-direction:column;gap:3px}
.mchat__cliente-t{font-size:17px;font-weight:800;color:#166534;letter-spacing:.5px}
.mchat__cliente-n{font-size:13px;color:#3f6b51}
/* 🧾 Las líneas de detalle de cada mensaje (igual que el mensaje de Telegram) */
.mchat__det{display:flex;flex-direction:column;gap:1px;margin-top:5px;padding-top:5px;
    border-top:1px dashed #efe6da}
.mchat__det > span{font-size:11.5px;line-height:1.45;color:#7a7368;font-family:ui-monospace,Menlo,Consolas,monospace;
    word-break:break-word}
/* 📲 El botón para recibir estos mensajes en Telegram */
.mchat__cta--tg{background:#229ed9;color:#fff;flex:1 1 100%}
/* ✨ El mensaje que acaba de entrar: aparece deslizándose desde abajo, como en un chat. */
.mchat__msg--nuevo{animation:mchatEntra .45s ease both}
@keyframes mchatEntra{0%{opacity:0;transform:translateY(10px)}100%{opacity:1;transform:translateY(0)}}
.mchat__pie{display:flex;gap:8px;padding:10px 12px;background:#fff;border-top:1px solid var(--color-borde,#e8ddd0);flex-wrap:wrap}
.mchat__cta{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:44px;
    padding:10px 16px;border-radius:11px;background:var(--marca-naranja,#e07a1f);color:#fff;
    font-size:15px;font-weight:800;text-decoration:none;flex:1 1 auto}
.mchat__cta--suave{background:#fff;color:var(--marca-granate,#6d071a);border:1.5px solid var(--color-borde,#e8ddd0)}
/* ============ 🏠 EL BLOQUE DE LA PORTADA ============ */
.muro-portada{margin:22px 0}
.muro-portada__cab{margin-bottom:10px}
.muro-portada__sub{margin:5px 0 0;font-size:14.5px;line-height:1.5;color:#666}
.muro-portada__pedidos{display:grid;grid-template-columns:1fr;gap:9px;margin-bottom:12px}
@media (min-width:760px){.muro-portada__pedidos{grid-template-columns:repeat(3,1fr)}}
.muro-portada__pedido{display:flex;flex-direction:column;gap:3px;padding:12px 13px;border-radius:13px;
    background:#fff8ed;border:1.5px solid var(--marca-naranja,#e07a1f);text-decoration:none}
.muro-portada__pide{font-size:16px;color:#222}
.muro-portada__pide b{color:var(--marca-granate,#6d071a)}
.muro-portada__estado{font-size:12.5px;font-weight:800;text-transform:uppercase;letter-spacing:.3px;color:#c2410c}
.muro-portada__fuego{font-size:13px;font-weight:800;color:var(--marca-granate,#6d071a)}
</style>';
}
