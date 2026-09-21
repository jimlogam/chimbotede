<?php
/**
 * includes/vista_tiendas_admin.php — 🏬 TIENDAS del Súper Admin
 * ============================================================================
 * VISTA PREVIA (tarjetas con FOTO) + FILTROS + ORDEN, pedido del jefe (2026-09-12):
 *
 *   "necesito tener una vista previa de las tiendas y filtros para ordenarlas por:
 *    con foto, sin foto, con whatsapp, sin whatsapp, con productos, sin productos,
 *    etc. — filtros activos"
 *
 * Qué hace:
 *  1) 🖼️ VISTA PREVIA: cada tienda es una tarjeta con su FOTO (la primera de la
 *     galería), nombre, rubro, distrito, slug, estado y los datos que importan
 *     para trabajar: 📦 productos · 📷 fotos · 📱 WhatsApp · 📞 teléfono ·
 *     📍 GPS · 👁️ vistas · 👤 dueño. Lo que FALTA se pinta en ROJO.
 *  2) 🔎 FILTROS en píldoras, con el NÚMERO REAL de tiendas de cada uno
 *     (con/sin foto, WhatsApp, productos, teléfono, GPS, dueño, redes, destacadas)
 *     + estado (pendientes / activas / ocultas / rechazadas). Se combinan entre sí
 *     y con el buscador. Los filtros puestos se ven en la barra "Filtros activos"
 *     y se quitan con un clic (✕) o todos de golpe con 🧹.
 *  2.bis) 🆕 📍 DISTRITO y 🛵 CÓMO ATIENDE (pedido del jefe, 2026-09-20): *«en el filtro de tiendas
 *     también falta que filtres por distritos… quiero ver esto pero solo para el distrito de Santa…
 *     o de vendedores que venden desde internet… o ambulantes»*. Un bloque con los 9 distritos
 *     (Chimbote, Nuevo Chimbote, Santa, Coishco, Samanco, Nepeña, Moro, Macate y Cáceres del Perú)
 *     y otro con el tipo de vendedor (`ubicacion_tipo`: 🏪 local fijo · 🛵 ambulante · 🏠 a domicilio ·
 *     🚚 todo el país (internet) · 📦 al por mayor · ❔ sin dato). El distrito usa la MISMA regla que
 *     la búsqueda del sitio: la tienda entra si ESTÁ ahí o si ATIENDE ahí (cobertura).
 *  2.ter) 🆕 🏷️ RUBRO (2026-09-20): un desplegable en la barra con los 44 rubros y sus números
 *     (la tienda cuenta por su rubro principal y por sus rubros extra). Se combina con todo.
 *  3) ↕️ ORDEN: recientes, antiguas, pendientes primero, nombre A–Z, más/menos
 *     fotos, más/menos productos, más vistas, actualizadas hace poco.
 *  4) ⚡ BUSCADOR PREDICTIVO: filtra al instante las tarjetas de la página mientras
 *     escribes y, si dejas de escribir, busca en TODAS las tiendas del sitio.
 *  5) 📋 VISTA TABLA (como antes) con los mismos filtros, para quien la prefiera.
 *
 * Contexto: se incluye desde superadmin.php (sección 'tiendas'), ya con
 * config.php cargado (db(), url(), e(), img_tag(), csrf_campo()).
 * Todas las funciones van con el prefijo `sat_` para no chocar con nada.
 * ============================================================================
 */

// ===== 1) CATÁLOGO DE FILTROS ================================================

/** Filtros "con / sin" (clave GET => [título, etiqueta con, etiqueta sin, clave del contador]) */
function sat_pares(): array {
    return [
        'foto'  => ['📷 Foto',       'Con foto',            'Sin foto',            'foto'],
        'prod'  => ['📦 Productos',  'Con productos',       'Sin productos',       'prod'],
        'wa'    => ['📱 WhatsApp',   'Con WhatsApp',        'Sin WhatsApp',        'wa'],
        // 🎵 CON MÚSICA (pedido del jefe, 2026-09-18): las tiendas que ya tienen su canción puesta
        // (la que suena en su ficha). Cuenta solo las canciones que de verdad suenan: `estado = 'listo'`
        // y con archivo (`ruta`), que es exactamente lo que mira el reproductor de la ficha.
        'mus'   => ['🎵 Música',     'Con música',          'Sin música',          'mus'],
        // ✅ REVISADOS (pedido del jefe, 2026-09-18): las tiendas por las que YA se pasó la revisión
        // completa (catálogo, imágenes de producto, rubro y canción). La marca vive en
        // `directorio_negocios.revisado_en` (fecha en que se revisó; NULL = todavía sin revisar) y se
        // pone con el botón «✅ Marcar revisada» de cada tarjeta o al terminar la revisión de una tienda.
        'rev'   => ['✅ Revisados',  '✅ Revisados',        '⬜ Sin revisar',      'rev'],
        'tel'   => ['📞 Teléfono',   'Con teléfono',        'Sin teléfono',        'tel'],
        'ubi'   => ['📍 Ubicación',  'Con ubicación GPS',   'Sin ubicación GPS',   'ubi'],
        'dueno' => ['👤 Dueño',      'Con dueño',           'Sin dueño',           'dueno'],
        'redes' => ['🌐 Redes',      'Con redes sociales',  'Sin redes sociales',  'redes'],
        'dest'  => ['⭐ Destacadas', 'Destacadas',          'No destacadas',       'dest'],
    ];
}

/** Filtros que se ven siempre (los demás van en "⚙️ Más filtros") */
function sat_pares_rapidos(): array { return ['foto', 'prod', 'rev', 'wa', 'mus']; }

/**
 * ¿La base ya tiene la columna `revisado_en`? (la creó la migración del 2026-09-18).
 * Si no estuviera, el filtro «Revisados» no se ofrece y el listado se pinta igual: nunca se cae.
 */
function sat_revisados_ok(): bool {
    static $ok = null;
    if ($ok === null) {
        try { db()->query('SELECT revisado_en FROM directorio_negocios LIMIT 1'); $ok = true; }
        catch (Throwable $e) { $ok = false; }
    }
    return $ok;
}

/**
 * ¿Existe la tabla de COBERTURA (`directorio_negocio_cobertura`)? La creó el 5.º tipo de vendedor
 * (🧰 servicio a domicilio, 2026-09-10) y es la que dice en qué distritos ATIENDE una tienda.
 * Si no estuviera, el filtro de distrito funciona igual, solo con el distrito base (`distrito_id`).
 */
function sat_cobertura_ok(): bool {
    static $ok = null;
    if ($ok === null) {
        try { db()->query('SELECT 1 FROM directorio_negocio_cobertura LIMIT 1'); $ok = true; }
        catch (Throwable $e) { $ok = false; }
    }
    return $ok;
}

/**
 * 📍 LOS DISTRITOS CON SUS NÚMEROS (pedido del jefe, 2026-09-20): *«en el filtro de tiendas también
 * falta que filtres por distritos… quiero ver esto pero solo para el distrito de santa»*.
 *
 * Devuelve id => ['nombre','n','visible'] de TODOS los distritos (los 9), en orden alfabético.
 * El número usa la MISMA regla que la búsqueda del sitio (`buscar.php`): la tienda entra en un
 * distrito si **está** ahí (`distrito_id`) **o si ATIENDE ahí** (cobertura). Por eso la suma de los
 * distritos puede pasar del total del sitio: una tienda que atiende en 4 distritos cuenta en los 4.
 * Todo en UNA sola consulta (la lista y los conteos), y a prueba de fallos: sin cobertura, se cuenta
 * solo el distrito base.
 */
function sat_distritos(): array {
    static $res = null;
    if ($res !== null) return $res;
    $res = [];
    $inner = sat_cobertura_ok()
        ? "(SELECT n.distrito_id AS did, n.id AS nid FROM directorio_negocios n WHERE n.distrito_id IS NOT NULL
            UNION ALL
            SELECT cc.distrito_id AS did, cc.negocio_id AS nid FROM directorio_negocio_cobertura cc)"
        : "(SELECT n.distrito_id AS did, n.id AS nid FROM directorio_negocios n WHERE n.distrito_id IS NOT NULL)";
    $sql = "SELECT d.id, d.nombre, d.visible, COALESCE(x.n, 0) AS n
              FROM directorio_distritos d
              LEFT JOIN (SELECT c.did, COUNT(DISTINCT c.nid) AS n FROM $inner c GROUP BY c.did) x ON x.did = d.id
             ORDER BY d.nombre ASC";
    try {
        foreach (db()->query($sql) as $f) {
            $res[(int)$f['id']] = ['nombre' => (string)$f['nombre'], 'n' => (int)$f['n'], 'visible' => (int)$f['visible']];
        }
    } catch (Throwable $e) {
        // Plan B (sin UNION): solo el distrito base. El filtro nunca se queda sin opciones.
        try {
            foreach (db()->query("SELECT d.id, d.nombre, d.visible,
                                        (SELECT COUNT(*) FROM directorio_negocios n WHERE n.distrito_id = d.id) AS n
                                   FROM directorio_distritos d ORDER BY d.nombre ASC") as $f) {
                $res[(int)$f['id']] = ['nombre' => (string)$f['nombre'], 'n' => (int)$f['n'], 'visible' => (int)$f['visible']];
            }
        } catch (Throwable $e2) { $res = []; }
    }
    return $res;
}

/**
 * 🛵 CÓMO ATIENDE (el `ubicacion_tipo` de la tienda) — pedido del jefe, 2026-09-20:
 * *«…o de vendedores que venden desde internet… o ambulantes»*.
 * Clave => [etiqueta larga (píldora), etiqueta corta (tarjeta/tabla)].
 * `sin_dato` es una clave de mentira: agrupa las tiendas que traen el campo vacío (para poder
 * encontrarlas y arreglarlas), no es un valor del ENUM.
 */
function sat_vende(): array {
    return [
        'fisica'    => ['🏪 Tiene local y ahí atiende', '🏪 Local fijo'],
        'ambulante' => ['🛵 Ambulante (por las calles)', '🛵 Ambulante'],
        'domicilio' => ['🏠 Lo lleva a la casa del cliente', '🏠 A domicilio'],
        'nacional'  => ['🚚 Vende a todo el país (internet)', '🚚 Todo el país'],
        'mayorista' => ['📦 Vende al por mayor', '📦 Al por mayor'],
        'sin_dato'  => ['❔ Sin dato', '❔ Sin dato'],
    ];
}

/** Cuántas tiendas hay de cada tipo de vendedor (totales del sitio). */
function sat_contar_vende(): array {
    static $res = null;
    if ($res !== null) return $res;
    $res = array_fill_keys(array_keys(sat_vende()), 0);
    try {
        $sql = "SELECT COALESCE(NULLIF(TRIM(ubicacion_tipo), ''), 'sin_dato') AS t, COUNT(*) AS n
                  FROM directorio_negocios GROUP BY t";
        foreach (db()->query($sql) as $f) {
            $t = (string)$f['t'];
            if (!array_key_exists($t, $res)) $res[$t] = 0;   // un valor raro del ENUM no rompe nada
            $res[$t] = (int)$f['n'];
        }
    } catch (Throwable $e) { /* sin números, las píldoras salen en 0 y el filtro sigue sirviendo */ }
    return $res;
}

/** La etiqueta corta de cómo atiende una tienda (para la tarjeta y la tabla). */
function sat_vende_corto($tipo): string {
    $mapa = sat_vende();
    $k = trim((string)$tipo);
    return $mapa[$k][1] ?? $mapa['sin_dato'][1];
}

/**
 * 🏷️ LOS RUBROS CON SUS NÚMEROS (pedido del jefe, 2026-09-20, después de los distritos):
 * alimenta el desplegable «🏷️ Rubro» de la barra de arriba.
 *
 * Devuelve id => ['nombre','n'] de TODOS los rubros, en orden alfabético (el `<select>` nativo
 * salta al rubro al escribir sus primeras letras: UX predictiva sin listas eternas a la vista).
 * El número usa la MISMA regla que el sitio (`rubro_filtro_id()`): la tienda cuenta en su rubro
 * principal **y** en cada rubro extra (`directorio_negocio_rubros`, 2026-09-12).
 * A prueba de fallos: si la tabla de rubros múltiples no estuviera, se cuenta solo el principal.
 */
function sat_rubros(): array {
    static $res = null;
    if ($res !== null) return $res;
    $res = [];
    $inner = rubros_multi_ok()
        ? "(SELECT n.categoria_id AS cid, n.id AS nid FROM directorio_negocios n WHERE n.categoria_id IS NOT NULL
            UNION ALL
            SELECT rr.categoria_id AS cid, rr.negocio_id AS nid FROM directorio_negocio_rubros rr)"
        : "(SELECT n.categoria_id AS cid, n.id AS nid FROM directorio_negocios n WHERE n.categoria_id IS NOT NULL)";
    $sql = "SELECT c.id, c.nombre, c.activo, COALESCE(x.n, 0) AS n
              FROM directorio_categorias c
              LEFT JOIN (SELECT y.cid, COUNT(DISTINCT y.nid) AS n FROM $inner y GROUP BY y.cid) x ON x.cid = c.id
             ORDER BY c.nombre ASC";
    try {
        foreach (db()->query($sql) as $f) {
            $n = (int)$f['n'];
            // Un rubro APAGADO y sin ninguna tienda no se ofrece (no hay nada que mirar ahí).
            if ($n === 0 && (int)($f['activo'] ?? 1) === 0) continue;
            $res[(int)$f['id']] = ['nombre' => (string)$f['nombre'], 'n' => $n];
        }
    } catch (Throwable $e) {
        try {
            foreach (db()->query("SELECT id, nombre FROM directorio_categorias ORDER BY nombre ASC") as $f) {
                $res[(int)$f['id']] = ['nombre' => (string)$f['nombre'], 'n' => 0];
            }
        } catch (Throwable $e2) { $res = []; }
    }
    return $res;
}

/** La tabla de las canciones (el nombre vive en el motor: `includes/config_cancion.php`). */
function sat_tabla_canciones(): string {
    return defined('CANCION_TABLA') ? (string)CANCION_TABLA : 'directorio_canciones';
}

/**
 * ¿Existe la tabla de canciones? Si el módulo no estuviera instalado, el filtro de música no se
 * ofrece (y el listado de tiendas se sigue pintando igual: nunca se rompe por un filtro).
 */
function sat_canciones_ok(): bool {
    static $ok = null;
    if ($ok === null) {
        try { db()->query('SELECT 1 FROM ' . sat_tabla_canciones() . ' LIMIT 1'); $ok = true; }
        catch (Throwable $e) { $ok = false; }
    }
    return $ok;
}

/** La canción que SUENA en la ficha de esa tienda (misma condición que el reproductor). */
function sat_cancion_sql(string $alias = 'n'): string {
    return "SELECT 1 FROM " . sat_tabla_canciones() . " k
             WHERE k.negocio_id = " . $alias . ".id AND k.estado = 'listo' AND k.ruta IS NOT NULL";
}

/** Estados (clave GET => [etiqueta, clase del badge]) */
function sat_estados(): array {
    return [
        ''          => ['🗂️ Todas',     'b-oculto'],
        'pendiente' => ['⏳ Pendientes', 'b-pendiente'],
        'activo'    => ['🟢 Activas',    'b-aprobado'],
        'inactivo'  => ['🙈 Ocultas',    'b-oculto'],
        'rechazado' => ['⛔ Rechazadas', 'b-rechazado'],
    ];
}

/** Orden del listado (clave GET => etiqueta) */
function sat_ordenes(): array {
    return [
        'recientes'    => '🕒 Más recientes',
        'antiguas'     => '🕰️ Más antiguas',
        'pendientes'   => '⏳ Pendientes primero',
        'nombre'       => '🔤 Nombre (A–Z)',
        'vistas'       => '👁️ Más vistas',
        'fotos'        => '📷 Más fotos',
        'sin_fotos'    => '📷 Menos fotos primero',
        'productos'    => '📦 Más productos',
        'sin_prod'     => '📦 Menos productos primero',
        'actualizadas' => '🔄 Actualizadas hace poco',
    ];
}

/** ORDER BY real de cada opción (los alias del SELECT funcionan en MySQL) */
function sat_orden_sql(string $orden): string {
    switch ($orden) {
        case 'antiguas':     return 'n.id ASC';
        case 'pendientes':   return "FIELD(n.estado,'pendiente','inactivo','rechazado','activo'), n.id DESC";
        case 'nombre':       return 'n.nombre ASC';
        case 'vistas':       return 'n.vistas_count DESC, n.id DESC';
        case 'fotos':        return 'nfotos DESC, n.id DESC';
        case 'sin_fotos':    return 'nfotos ASC, n.id DESC';
        case 'productos':    return 'nproductos DESC, n.id DESC';
        case 'sin_prod':     return 'nproductos ASC, n.id DESC';
        case 'actualizadas': return 'n.actualizado_en DESC, n.id DESC';
        case 'recientes':
        default:             return 'n.id DESC';
    }
}

// ===== 2) ESTADO DE LOS FILTROS (lo que trae la URL) =========================

/** Lee y normaliza los filtros de la URL. Solo valores válidos entran. */
function sat_leer(): array {
    $f = [];
    foreach (array_keys(sat_pares()) as $k) {
        $v = isset($_GET[$k]) ? (string)$_GET[$k] : '';
        $f[$k] = in_array($v, ['con', 'sin'], true) ? $v : '';
    }
    $est = (string)($_GET['estado'] ?? '');
    $f['estado'] = array_key_exists($est, sat_estados()) ? $est : '';

    // 📍 DISTRITO y 🛵 CÓMO ATIENDE (2026-09-20): entran solo ids/valores que existen.
    //    Si la URL trae un distrito que no está en la tabla (o basura), se ignora: nunca al SQL.
    $dist = (int)($_GET['dist'] ?? 0);
    $f['dist'] = array_key_exists($dist, sat_distritos()) ? $dist : 0;
    $vta  = (string)($_GET['vent'] ?? '');
    $f['vent'] = array_key_exists($vta, sat_vende()) ? $vta : '';

    // 🏷️ RUBRO (2026-09-20): solo ids de rubros que existen.
    $rub = (int)($_GET['rub'] ?? 0);
    $f['rub'] = array_key_exists($rub, sat_rubros()) ? $rub : 0;

    $f['q'] = trim((string)($_GET['q'] ?? ''));
    if (mb_strlen($f['q']) > 60) $f['q'] = mb_substr($f['q'], 0, 60);

    $ord = (string)($_GET['orden'] ?? '');
    $f['orden'] = array_key_exists($ord, sat_ordenes()) ? $ord : 'recientes';

    $f['vista'] = (($_GET['vista'] ?? '') === 'tabla') ? 'tabla' : 'previa';
    $f['p']     = max(1, (int)($_GET['p'] ?? 1));

    // ¿Hay algún filtro puesto? (sirve para abrir "Más filtros" solo)
    $f['hay'] = ($f['q'] !== '' || $f['estado'] !== '' || $f['dist'] > 0 || $f['vent'] !== '' || $f['rub'] > 0);
    foreach (array_keys(sat_pares()) as $k) if ($f[$k] !== '') $f['hay'] = true;

    return $f;
}

/** URL de la sección respetando los filtros (con cambios opcionales). */
function sat_url(array $f, array $cambios = []): string {
    $f = array_merge($f, $cambios);
    $q = ['seccion' => 'tiendas'];
    foreach (array_keys(sat_pares()) as $k) if (!empty($f[$k])) $q[$k] = $f[$k];
    if (!empty($f['estado'])) $q['estado'] = $f['estado'];
    if (!empty($f['dist']))   $q['dist']   = (int)$f['dist'];
    if (!empty($f['vent']))   $q['vent']   = $f['vent'];
    if (!empty($f['rub']))    $q['rub']    = (int)$f['rub'];
    if (!empty($f['q']))      $q['q']      = $f['q'];
    if (!empty($f['orden']) && $f['orden'] !== 'recientes') $q['orden'] = $f['orden'];
    if (!empty($f['vista']) && $f['vista'] !== 'previa')    $q['vista']  = $f['vista'];
    if (!empty($f['p']) && (int)$f['p'] > 1)                $q['p']      = (int)$f['p'];
    return url('superadmin.php?' . http_build_query($q));
}

/** FROM + JOIN comunes a todas las consultas de tiendas. */
function sat_from(): string {
    return " FROM directorio_negocios n
             LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
             LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
             LEFT JOIN directorio_usuarios   u ON u.id = n.dueno_id ";
}

/** Condiciones SQL del WHERE según los filtros ($par se llena con los valores). */
function sat_where(array $f, array &$par): string {
    $c = [];
    if (($f['q'] ?? '') !== '') {
        $like = '%' . $f['q'] . '%';
        $c[] = "(n.nombre LIKE ? OR n.slug LIKE ? OR c.nombre LIKE ? OR d.nombre LIKE ? OR u.email LIKE ?)";
        array_push($par, $like, $like, $like, $like, $like);
    }
    if (($f['estado'] ?? '') !== '') { $c[] = 'n.estado = ?'; $par[] = $f['estado']; }

    // 📍 DISTRITO (2026-09-20): la MISMA regla que la búsqueda del sitio (`buscar.php`): la tienda
    // sale si ESTÁ en ese distrito o si ATIENDE ahí (cobertura de un 🧰 servicio a domicilio o de un
    // negocio que marcó «🌎 Todos los distritos»). Así el número del panel coincide con lo que ve el
    // cliente que busca en ese distrito.
    if (!empty($f['dist'])) {
        $d = (int)$f['dist'];
        if (sat_cobertura_ok()) {
            $c[] = '(n.distrito_id = ? OR EXISTS (SELECT 1 FROM directorio_negocio_cobertura cc
                                                   WHERE cc.negocio_id = n.id AND cc.distrito_id = ?))';
            array_push($par, $d, $d);
        } else {
            $c[] = 'n.distrito_id = ?';
            $par[] = $d;
        }
    }

    // 🛵 CÓMO ATIENDE (2026-09-20): local fijo · ambulante · a domicilio · a todo el país · mayorista.
    if (!empty($f['vent'])) {
        if ($f['vent'] === 'sin_dato') {
            $c[] = "(n.ubicacion_tipo IS NULL OR TRIM(n.ubicacion_tipo) = '')";
        } else {
            $c[] = 'n.ubicacion_tipo = ?';
            $par[] = $f['vent'];
        }
    }

    // 🏷️ RUBRO (2026-09-20): la misma condición que usa la búsqueda del sitio (`rubro_filtro_id()`):
    // la tienda sale por su rubro principal y también por sus rubros extra (2026-09-12).
    if (!empty($f['rub'])) {
        [$sql_rub, $par_rub] = rubro_filtro_id((int)$f['rub'], 'n');
        $c[] = $sql_rub;
        foreach ($par_rub as $pv) $par[] = $pv;
    }

    $mapa = [
        'foto'  => ["EXISTS (SELECT 1 FROM directorio_fotos f WHERE f.negocio_id = n.id)",
                    "NOT EXISTS (SELECT 1 FROM directorio_fotos f WHERE f.negocio_id = n.id)"],
        'prod'  => ["EXISTS (SELECT 1 FROM directorio_servicios s WHERE s.negocio_id = n.id)",
                    "NOT EXISTS (SELECT 1 FROM directorio_servicios s WHERE s.negocio_id = n.id)"],
        'wa'    => ["COALESCE(NULLIF(TRIM(n.whatsapp),''),'') <> ''",
                    "COALESCE(NULLIF(TRIM(n.whatsapp),''),'') = ''"],
        'tel'   => ["COALESCE(NULLIF(TRIM(n.telefono),''),'') <> ''",
                    "COALESCE(NULLIF(TRIM(n.telefono),''),'') = ''"],
        'ubi'   => ["(n.lat IS NOT NULL AND n.lng IS NOT NULL)",
                    "(n.lat IS NULL OR n.lng IS NULL)"],
        'dueno' => ["n.dueno_id IS NOT NULL", "n.dueno_id IS NULL"],
        'redes' => ["(COALESCE(NULLIF(TRIM(n.facebook),''),'') <> ''
                     OR COALESCE(NULLIF(TRIM(n.instagram),''),'') <> ''
                     OR COALESCE(NULLIF(TRIM(n.tiktok),''),'') <> '')",
                    "(COALESCE(NULLIF(TRIM(n.facebook),''),'') = ''
                     AND COALESCE(NULLIF(TRIM(n.instagram),''),'') = ''
                     AND COALESCE(NULLIF(TRIM(n.tiktok),''),'') = '')"],
        'dest'  => ['n.destacado = 1', 'n.destacado = 0'],
    ];
    // 🎵 Música: solo si el módulo de las canciones está instalado.
    if (sat_canciones_ok()) {
        $mapa['mus'] = ['EXISTS (' . sat_cancion_sql() . ')', 'NOT EXISTS (' . sat_cancion_sql() . ')'];
    }
    // ✅ Revisados: solo si la columna de la marca existe.
    if (sat_revisados_ok()) {
        $mapa['rev'] = ['n.revisado_en IS NOT NULL', 'n.revisado_en IS NULL'];
    }
    foreach ($mapa as $k => $opciones) {
        if (!empty($f[$k])) $c[] = ($f[$k] === 'con') ? $opciones[0] : $opciones[1];
    }
    return $c ? (' WHERE ' . implode(' AND ', $c)) : '';
}

// ===== 3) CONSULTAS ==========================================================

/**
 * Números REALES de todo el sitio (una sola pasada) para pintar los filtros.
 * Son totales del universo: sirven como "lista de trabajo" (p. ej. hay 296 sin foto).
 */
function sat_contar_global(): array {
    static $res = null;
    if ($res !== null) return $res;
    // 🎵 La música se cuenta solo si la tabla existe (si no, el filtro no se ofrece y aquí no se
    //    pide nada: así el listado del Súper Admin nunca se cae por un módulo que no está).
    $mus_ok = sat_canciones_ok();
    $col_mus = $mus_ok ? ", (SELECT COUNT(*) FROM " . sat_tabla_canciones() . " k
                 WHERE k.negocio_id = n.id AND k.estado = 'listo' AND k.ruta IS NOT NULL) AS nmus"
             : ", 0 AS nmus";
    $sum_mus = $mus_ok ? ", SUM(f.nmus > 0) AS con_mus,   SUM(f.nmus = 0) AS sin_mus" : '';
    // ✅ Revisados: el contador solo se pide si la columna existe.
    $rev_ok  = sat_revisados_ok();
    $col_rev = $rev_ok ? ", (n.revisado_en IS NOT NULL) AS nrev" : '';
    $sum_rev = $rev_ok ? ", SUM(f.nrev > 0) AS con_rev,   SUM(f.nrev = 0) AS sin_rev" : '';
    $sql = "SELECT COUNT(*) AS total,
        SUM(f.nfotos > 0) AS con_foto,      SUM(f.nfotos = 0) AS sin_foto,
        SUM(f.nprod  > 0) AS con_prod,      SUM(f.nprod  = 0) AS sin_prod,
        SUM(f.nwa    > 0) AS con_wa,        SUM(f.nwa    = 0) AS sin_wa,
        SUM(f.ntel   > 0) AS con_tel,       SUM(f.ntel   = 0) AS sin_tel,
        SUM(f.nubi   > 0) AS con_ubi,       SUM(f.nubi   = 0) AS sin_ubi,
        SUM(f.ndueno > 0) AS con_dueno,     SUM(f.ndueno = 0) AS sin_dueno,
        SUM(f.nredes > 0) AS con_redes,     SUM(f.nredes = 0) AS sin_redes,
        SUM(f.ndest  > 0) AS con_dest,      SUM(f.ndest  = 0) AS sin_dest,
        SUM(f.nestado = 'pendiente') AS est_pendiente,
        SUM(f.nestado = 'activo')    AS est_activo,
        SUM(f.nestado = 'inactivo')  AS est_inactivo,
        SUM(f.nestado = 'rechazado') AS est_rechazado" . $sum_mus . $sum_rev . "
        FROM (
            SELECT n.id, n.estado AS nestado, n.destacado AS ndest,
                (SELECT COUNT(*) FROM directorio_fotos     x WHERE x.negocio_id = n.id) AS nfotos,
                (SELECT COUNT(*) FROM directorio_servicios s WHERE s.negocio_id = n.id) AS nprod,
                (COALESCE(NULLIF(TRIM(n.whatsapp),''),'') <> '') AS nwa,
                (COALESCE(NULLIF(TRIM(n.telefono),''),'') <> '') AS ntel,
                (n.lat IS NOT NULL AND n.lng IS NOT NULL) AS nubi,
                (n.dueno_id IS NOT NULL) AS ndueno,
                ((COALESCE(NULLIF(TRIM(n.facebook),''),'')  <> '')
              OR (COALESCE(NULLIF(TRIM(n.instagram),''),'') <> '')
              OR (COALESCE(NULLIF(TRIM(n.tiktok),''),'')    <> '')) AS nredes" . $col_mus . $col_rev . "
            FROM directorio_negocios n
        ) f";
    try {
        $res = db()->query($sql)->fetch(PDO::FETCH_ASSOC) ?: [];
    } catch (Throwable $e) {
        $res = [];
    }
    foreach ($res as $k => $v) $res[$k] = (int)$v;
    return $res;
}

/** Cuántas tiendas cumplen los filtros puestos. */
function sat_total(array $f): int {
    $par = [];
    try {
        $st = db()->prepare('SELECT COUNT(*)' . sat_from() . sat_where($f, $par));
        $st->execute($par);
        return (int)$st->fetchColumn();
    } catch (Throwable $e) {
        return 0;
    }
}

/** Las tiendas de la página actual, ya con foto de portada y contadores. */
function sat_listar(array $f, int $por, int $offset): array {
    $par = [];
    // ✅ La fecha de revisión solo se pide si la columna existe (así el listado nunca se cae).
    $col_rev = sat_revisados_ok() ? ', n.revisado_en' : '';
    $sql = "SELECT n.id, n.nombre, n.slug, n.estado, n.creado_en, n.actualizado_en,
                   n.whatsapp, n.telefono, n.direccion, n.referencia, n.ubicacion_tipo,
                   n.lat, n.lng, n.vistas_count, n.rating, n.destacado, n.dueno_id" . $col_rev . ",
                   c.nombre AS categoria_nombre, c.icono AS categoria_icono,
                   d.nombre AS distrito_nombre,
                   u.email  AS dueno_email,
                   (SELECT COUNT(*) FROM directorio_servicios s WHERE s.negocio_id = n.id) AS nproductos,
                   (SELECT COUNT(*) FROM directorio_fotos     x WHERE x.negocio_id = n.id) AS nfotos,
                   (SELECT x.ruta FROM directorio_fotos x WHERE x.negocio_id = n.id
                     ORDER BY x.orden ASC, x.id ASC LIMIT 1) AS portada"
         . sat_from() . sat_where($f, $par)
         . ' ORDER BY ' . sat_orden_sql($f['orden'])
         . ' LIMIT ' . (int)$por . ' OFFSET ' . (int)$offset;
    try {
        $st = db()->prepare($sql);
        $st->execute($par);
        return $st->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

// ===== 4) PIEZAS DE LA INTERFAZ =============================================

/** Píldora de filtro (enlace que pone/quita el filtro). */
function sat_pildora(array $f, string $clave, string $valor, string $texto, int $n, string $extra = ''): string {
    $puesto = (($f[$clave] ?? '') === $valor);
    $nuevo  = $puesto ? '' : $valor;      // tocar la píldora puesta = quitarla
    $clase  = 'sati-chip' . ($puesto ? ' sati-chip--on' : '') . ($extra !== '' ? ' ' . $extra : '');
    return '<a class="' . $clase . '" href="' . e(sat_url($f, [$clave => $nuevo, 'p' => 1])) . '">'
         . $texto
         . ' <b>' . number_format($n) . '</b>'
         . ($puesto ? ' <span class="sati-chip__x">✕</span>' : '')
         . '</a>';
}

/**
 * Píldora de VALOR ÚNICO (📍 distrito · 🛵 cómo atiende): a diferencia de las de "con/sin", aquí
 * se elige UN valor y, al tocar la píldora puesta, se quita. Mismo aspecto que las demás.
 */
function sat_pildora_valor(array $f, string $clave, string $valor, string $texto, int $n, string $extra = ''): string {
    $puesto = ((string)($f[$clave] ?? '') === (string)$valor);
    $clase  = 'sati-chip' . ($puesto ? ' sati-chip--on' : '') . ($extra !== '' ? ' ' . $extra : '');
    return '<a class="' . $clase . '" href="' . e(sat_url($f, [$clave => $puesto ? '' : $valor, 'p' => 1])) . '">'
         . $texto
         . ' <b>' . number_format($n) . '</b>'
         . ($puesto ? ' <span class="sati-chip__x">✕</span>' : '')
         . '</a>';
}

/** Píldora de estado (Todas / Pendientes / Activas / …). */
function sat_pildora_estado(array $f, string $valor, string $texto, int $n): string {
    $puesto = (($f['estado'] ?? '') === $valor);
    $clase  = 'sati-chip' . ($puesto ? ' sati-chip--on' : '');
    // En "Todas" no se pinta la ✕: no es un filtro puesto, es la ausencia de filtro.
    $equis  = ($puesto && $valor !== '') ? ' <span class="sati-chip__x">✕</span>' : '';
    return '<a class="' . $clase . '" href="' . e(sat_url($f, ['estado' => $valor, 'p' => 1])) . '">'
         . $texto . ' <b>' . number_format($n) . '</b>' . $equis
         . '</a>';
}

/** Botón de acción POST (aprobar / rechazar / ocultar / publicar / eliminar).
 *  Con `$confirmar` vacío NO pregunta nada (para los clics que se repiten mucho, p. ej. marcar revisada). */
function sat_boton_post(string $accion, int $id, string $estado, string $texto, string $clase, string $confirmar): string {
    return '<form method="post" style="display:inline">' . csrf_campo()
         . '<input type="hidden" name="accion" value="' . e($accion) . '">'
         . '<input type="hidden" name="id" value="' . (int)$id . '">'
         . ($estado !== '' ? '<input type="hidden" name="estado" value="' . e($estado) . '">' : '')
         . '<button class="btn-mini ' . $clase . '"'
         . ($confirmar !== '' ? ' onclick="return confirm(' . "'" . e($confirmar) . "'" . ')"' : '')
         . '>' . $texto . '</button>'
         . '</form>';
}

/**
 * 📨 EL BOTÓN DE INVITACIÓN de una tienda (pedido del jefe, 2026-09-17).
 * Va DEBAJO de la tienda, con los 4 colores según cuántas invitaciones lleva:
 *   ⚫ 0 negro · 🟠 1 naranja · 🟢 2 verde · 🔵 3+ azul
 * El mensaje que abre lleva el usuario y la contraseña de la tienda (§2.b de
 * includes/invitaciones.php): si las credenciales YA están preparadas, el enlace se pinta
 * aquí mismo (clic instantáneo); si es la PRIMERA vez, el JavaScript abre WhatsApp en una
 * pestaña y el servidor responde con el enlace ya armado (hay que crear la cuenta).
 * Al costado, el ↺ deshace un clic de más. Si la tienda no tiene ni WhatsApp ni teléfono,
 * el botón sale apagado en rojo: nunca se abre un chat en blanco (regla del sitio).
 *
 * @param array $inv  ['n'=>int,'ultima'=>string]
 * @param array $cred ['usuario'=>string,'clave'=>string] ya guardadas ('' si no hay)
 */
function sat_boton_invitacion(array $t, array $inv, array $cred = []): string {
    $id  = (int)$t['id'];
    $n   = (int)($inv['n'] ?? 0);
    $tel = invitacion_contacto($t);

    $como = invitacion_etiqueta($n);
    if (!empty($inv['ultima'])) {
        $ts = strtotime((string)$inv['ultima']);
        // ⚠️ «a las» va ESCAPADO: en `date()` la `a` es am/pm, la `l` el día de la semana y la `s`
        // los segundos — sin las barras el `title` decía «17/09/2026 am Thursdayam26 09:07».
        if ($ts) $como .= ' · último envío el ' . date('d/m/Y \a \l\a\s H:i', $ts);
    }

    if ($tel === '') {
        return '<span class="sati-inv sati-inv--sin" title="Esta tienda no tiene WhatsApp ni teléfono: no se puede invitar">📨 Sin número</span>';
    }

    // ¿Ya tiene usuario y contraseña? Entonces el enlace se puede armar aquí y no hay espera.
    $listo = (!empty($cred['usuario']) && !empty($cred['clave']));
    $href  = $listo ? invitacion_url($t, $cred) : '#';
    $creds = $listo ? ('usuario ' . (string)$cred['usuario'] . ' · contraseña ' . (string)$cred['clave']) : '';
    if ($creds !== '') $como .= ' · ' . $creds;

    return '<a class="sati-inv sati-inv--n' . invitacion_nivel($n) . '" data-inv-id="' . $id . '"'
         . ' data-listo="' . ($listo ? '1' : '0') . '"'
         . ($creds !== '' ? ' data-creds="' . e($creds) . '"' : '')
         . ' href="' . e($href) . '"' . ($listo ? ' target="_blank" rel="noopener"' : '')
         . ' title="Invitación por WhatsApp con el usuario y la contraseña · ' . e($como) . '"'
         . ' onclick="return satInvitar(this)">📨 Invitar <b class="sati-inv__n">' . $n . '</b></a>'
         . '<button type="button" class="sati-inv__undo" data-inv-id="' . $id . '"'
         . ' title="Corregir: quitar UNA invitación del conteo"' . ($n < 1 ? ' disabled' : '')
         . ' onclick="return satInvDeshacer(this)">↺</button>';
}

// ===== 5) LA VISTA ==========================================================

// 📨 INVITACIONES (2026-09-17): el motor vive en includes/invitaciones.php.
require_once __DIR__ . '/invitaciones.php';

$sf    = sat_leer();
$sg    = sat_contar_global();
$por   = ($sf['vista'] === 'tabla') ? 60 : 30;
$total = sat_total($sf);
$paginas = max(1, (int)ceil($total / $por));
if ($sf['p'] > $paginas) $sf['p'] = $paginas;
$offset = ($sf['p'] - 1) * $por;
$filas  = sat_listar($sf, $por, $offset);

// Cuántas invitaciones lleva cada tienda de ESTA página (⚫🟠🟢🔵) y qué credenciales se le
// mandaron (usuario + contraseña, para poder armar el enlace sin esperar).
// Son consultas aparte y a prueba de fallos: si las tablas faltaran, el listado se pinta igual.
$sati_ids  = array_map(function ($t) { return (int)$t['id']; }, $filas);
$sati_inv  = invitaciones_mapa($sati_ids);
$sati_cred = invitaciones_claves_mapa($sati_ids);
$sati_inv_de = function ($id) use ($sati_inv): array {
    $id = (int)$id;
    return $sati_inv[$id] ?? ['n' => 0, 'ultima' => ''];
};
$sati_cred_de = function ($id) use ($sati_cred): array {
    $id = (int)$id;
    return $sati_cred[$id] ?? ['usuario' => '', 'clave' => ''];
};

$pares   = sat_pares();
$estados = sat_estados();
// 📍 Distritos y 🛵 tipos de vendedor (2026-09-20): la lista con sus números reales.
$distritos = sat_distritos();
$vende_lbl = sat_vende();
$vende_num = sat_contar_vende();
// 🏷️ Los rubros (con sus números) para el desplegable de la barra.
$rubros    = sat_rubros();
$badge_estado = ['pendiente' => 'b-pendiente', 'activo' => 'b-aprobado', 'inactivo' => 'b-oculto', 'rechazado' => 'b-rechazado'];

/** Contador global de un par (con/sin) */
$num = function (string $clave, string $lado) use ($sg): int {
    $k = ($lado === 'con' ? 'con_' : 'sin_') . $clave;
    return (int)($sg[$k] ?? 0);
};
$num_estado = function (string $valor) use ($sg): int {
    if ($valor === '') return (int)($sg['total'] ?? 0);
    $k = 'est_' . $valor;
    return (int)($sg[$k] ?? 0);
};

// Filtros puestos (para la barra "Filtros activos")
$puestos = [];
if ($sf['q'] !== '')       $puestos[] = ['q', '🔎 «' . $sf['q'] . '»'];
if ($sf['estado'] !== '')  $puestos[] = ['estado', $estados[$sf['estado']][0]];
if (!empty($sf['dist']))   $puestos[] = ['dist', '📍 ' . ($distritos[$sf['dist']]['nombre'] ?? '')];
if (!empty($sf['vent']))   $puestos[] = ['vent', $vende_lbl[$sf['vent']][0]];
if (!empty($sf['rub']))    $puestos[] = ['rub', '🏷️ ' . ($rubros[$sf['rub']]['nombre'] ?? '')];
foreach ($pares as $k => $info) {
    if (!empty($sf[$k])) $puestos[] = [$k, ($sf[$k] === 'con' ? $info[1] : $info[2])];
}
$hay_extra = false;
foreach (['tel', 'ubi', 'dueno', 'redes', 'dest'] as $k) if (!empty($sf[$k])) $hay_extra = true;
?>
<style>
/* 🏬 TIENDAS: vista previa + filtros (Súper Admin) */
/* 📐 `--sati-foto` = la ALTURA de TODAS las fotos (corta y siempre igual, pedido del jefe). */
.sati{--sati-foto:152px;font-size:14px}
.sati-barra{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:10px}
.sati-busca{display:flex;gap:6px;flex:1;min-width:240px}
.sati-busca input{flex:1;min-width:150px;padding:11px 13px;border:1px solid var(--color-borde);border-radius:10px;font-size:16px}
.sati-barra select{padding:11px 10px;border:1px solid var(--color-borde);border-radius:10px;font-size:16px;background:#fff;max-width:100%}
.sati-caja{background:#fff;border:1px solid var(--color-borde);border-radius:12px;box-shadow:var(--sombra-tarjeta);padding:10px 12px;margin-bottom:10px}
.sati-caja__titulo{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.04em;color:var(--color-texto-claro);margin-bottom:6px}
/* Segundo título dentro de la misma caja (📍 Distrito + 🛵 Cómo atiende): separa los dos bloques. */
.sati-caja__titulo--sep{margin-top:12px;padding-top:10px;border-top:1px solid var(--color-borde)}
.sati-chips{display:flex;flex-wrap:wrap;gap:6px}
.sati-chips + .sati-chips{margin-top:8px}
.sati-chip{display:inline-flex;align-items:center;gap:5px;padding:8px 12px;border-radius:999px;background:#f1f5f9;border:1px solid var(--color-borde);color:#334155;font-size:13.5px;font-weight:600;text-decoration:none;line-height:1;min-height:36px}
.sati-chip b{font-weight:800;color:#0f172a}
.sati-chip:hover{border-color:var(--color-primario);color:var(--color-primario)}
.sati-chip--on{background:var(--color-primario);border-color:transparent;color:#fff}
.sati-chip--on b,.sati-chip--on:hover{color:#fff}
.sati-chip__x{font-weight:800;opacity:.85}
.sati-chip--alerta b{color:#b91c1c}
.sati-chip--on.sati-chip--alerta b{color:#fff}
.sati-mas{margin-top:8px}
.sati-mas summary{cursor:pointer;font-size:13px;font-weight:700;color:var(--color-primario);padding:4px 0;list-style:none}
.sati-mas summary::-webkit-details-marker{display:none}
.sati-mas[open] summary{margin-bottom:6px}
.sati-activos{display:flex;flex-wrap:wrap;gap:6px;align-items:center;background:#fffbeb;border:1px solid #fcd34d;border-radius:12px;padding:8px 10px;margin-bottom:10px}
.sati-activos__lbl{font-size:12px;font-weight:800;color:#92400e;text-transform:uppercase;letter-spacing:.03em}
.sati-quitar{display:inline-flex;align-items:center;gap:5px;background:#fff;border:1px solid #fcd34d;color:#92400e;border-radius:999px;padding:6px 10px;font-size:12.5px;font-weight:700;text-decoration:none}
.sati-quitar:hover{background:#fef3c7}
.sati-resumen{font-size:13px;color:var(--color-texto-claro);margin:0 0 10px}
.sati-resumen b{color:var(--color-texto)}
.sati-resumen--vivo{color:#1d4ed8;font-weight:700}
/* 🖼️ LA REJILLA (pedido del jefe, 2026-09-17): 1 columna en el celular y 5 COLUMNAS EN PC.
   El ancho de cada tarjeta lo decide el ancho de la pantalla, no el contenido. */
.sati-grid{display:grid;grid-template-columns:minmax(0,1fr);gap:12px}
.sati-card{background:#fff;border:1px solid var(--color-borde);border-radius:14px;box-shadow:var(--sombra-tarjeta);overflow:hidden;display:flex;flex-direction:column}
/* 🖼️ La foto: MISMA ALTURA para todas (corta). `object-fit:cover` recorta parejo, así una foto
   apaisada y una vertical ocupan exactamente el mismo alto y las tarjetas quedan alineadas. */
.sati-card__foto{position:relative;display:block;height:var(--sati-foto);background:#f1f5f9;text-decoration:none;overflow:hidden}
.sati-card__foto img{width:100%;height:100%;object-fit:cover;object-position:center;display:block}
.sati-card__sinfoto{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:2px;font-size:26px;color:#b91c1c;background:repeating-linear-gradient(45deg,#fff1f2,#fff1f2 12px,#ffe4e6 12px,#ffe4e6 24px);border-bottom:2px dashed #fca5a5}
.sati-card__sinfoto b{font-size:12px;font-weight:800;letter-spacing:.06em}
.sati-card__estado{position:absolute;top:8px;left:8px;background:#fff;box-shadow:0 1px 4px rgba(0,0,0,.18)}
.sati-card__cuerpo{padding:10px 12px 12px;display:flex;flex-direction:column;gap:7px;flex:1}
/* El nombre ocupa 2 líneas como máximo: así todas las tarjetas de una fila miden lo mismo. */
.sati-card__nombre{font-size:15px;font-weight:800;line-height:1.2;margin:0;overflow-wrap:break-word;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;min-height:2.4em}
.sati-card__meta{font-size:12px;color:var(--color-texto-claro);line-height:1.35;overflow-wrap:break-word}
.sati-card__datos{display:flex;flex-wrap:wrap;gap:4px}
.sati-dato{font-size:11.5px;font-weight:700;padding:3px 8px;border-radius:999px;background:#f1f5f9;color:#334155;max-width:100%;overflow-wrap:break-word}
.sati-dato--ok{background:#dcfce7;color:#15803d}
.sati-dato--mal{background:#fee2e2;color:#b91c1c}
.sati-dato--info{background:#eff6ff;color:#1e40af}
.sati-acciones{display:flex;gap:6px;flex-wrap:wrap;margin-top:auto;padding-top:8px}
.sati-acciones .btn-mini{text-decoration:none;display:inline-block;line-height:1.5}
/* ===== 📨 BOTÓN DE INVITACIÓN (debajo de cada tienda) ==========================
   Los 4 colores del jefe (2026-09-17):
     ⚫ negro   n0 → sin invitar · 🟠 naranja n1 → 1 vez · 🟢 verde n2 → 2 veces · 🔵 azul n3 → 3+ */
.sati-inv-fila{display:flex;align-items:center;gap:5px;margin-top:9px;padding-top:9px;border-top:1px dashed var(--color-borde)}
.sati-inv{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:36px;padding:8px 10px;
  border:0;border-radius:10px;font-size:13px;font-weight:800;line-height:1;color:#fff;text-decoration:none;cursor:pointer;
  background:#111827;box-shadow:0 1px 3px rgba(15,23,42,.25)}
.sati-inv:hover{filter:brightness(1.12);color:#fff}
.sati-inv b{font-weight:800;background:rgba(255,255,255,.24);border-radius:999px;padding:1px 7px;font-size:12px}
.sati-inv--n0{background:#111827}   /* ⚫ sin invitar */
.sati-inv--n1{background:#ea580c}   /* 🟠 1 vez */
.sati-inv--n2{background:#15803d}   /* 🟢 2 veces */
.sati-inv--n3{background:#1d4ed8}   /* 🔵 3 veces o más */
.sati-inv--sin{background:#e5e7eb;color:#b91c1c;cursor:not-allowed;box-shadow:none}
.sati-inv--sin:hover{filter:none}
.sati-inv--sin b{background:rgba(185,28,28,.12);color:#b91c1c}
.sati-inv__undo{flex:0 0 auto;width:36px;min-height:36px;border:1px solid var(--color-borde);background:#f8fafc;color:#475569;
  border-radius:10px;font-size:15px;font-weight:800;line-height:1;cursor:pointer}
.sati-inv__undo:hover:not(:disabled){border-color:var(--color-primario);color:var(--color-primario)}
.sati-inv__undo:disabled{opacity:.35;cursor:default}
/* ✅ REVISADOS (2026-09-18): la fila de la marca de revisión, debajo de la invitación */
.sati-rev-fila{display:flex;align-items:center;gap:6px;margin-top:8px;flex-wrap:wrap}
.sati-rev{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:5px;min-height:34px;padding:7px 10px;
  border-radius:10px;font-size:12.5px;font-weight:800;line-height:1;background:#f1f5f9;color:#475569;border:1px dashed var(--color-borde)}
.sati-rev--on{background:#dcfce7;color:#15803d;border:1px solid #86efac}
/* Leyenda de los 4 colores (para que no haya que adivinarlos) */
.sati-inv-leyenda{display:flex;flex-wrap:wrap;align-items:center;gap:10px;background:#fff;border:1px solid var(--color-borde);
  border-radius:12px;box-shadow:var(--sombra-tarjeta);padding:8px 12px;margin:0 0 10px;font-size:12.5px;color:var(--color-texto-claro)}
.sati-inv-leyenda b{color:var(--color-texto)}
.sati-inv-leyenda span.punto{display:inline-block;width:12px;height:12px;border-radius:999px;vertical-align:-1px;margin-right:4px}
.sati-inv-leyenda .p0{background:#111827}
.sati-inv-leyenda .p1{background:#ea580c}
.sati-inv-leyenda .p2{background:#15803d}
.sati-inv-leyenda .p3{background:#1d4ed8}
.sati-pag{display:flex;gap:8px;align-items:center;justify-content:center;flex-wrap:wrap;margin:16px 0 4px}
.sati-pag a,.sati-pag span.pag{padding:9px 14px;border-radius:10px;border:1px solid var(--color-borde);background:#fff;text-decoration:none;color:var(--color-texto);font-weight:700;font-size:13.5px}
.sati-pag a:hover{border-color:var(--color-primario);color:var(--color-primario)}
.sati-pag .pag-off{opacity:.45}
.sati-tabla-mini{width:44px;height:44px;object-fit:cover;border-radius:8px;display:block}
.sati-tabla-sinfoto{width:44px;height:44px;border-radius:8px;background:#fee2e2;color:#b91c1c;display:flex;align-items:center;justify-content:center;font-size:18px}
/* 📐 COLUMNAS SEGÚN EL ANCHO (pedido del jefe: 5 columnas en PC).
   ⚠️ El ancho útil del panel tiene TOPE: `<main>` mide 1200 px y le quedan 1168 px al `.sa-wrap`
   (16 px de relleno por lado, `--max-ancho`), así que en cualquier PC ancha la rejilla recibe
   SIEMPRE 1168 px → 5 columnas de ~226 px. El corte se pone en 980 px (no en 1024) para que
   también salgan 5 columnas con la ventana no maximizada. */
@media(min-width:470px){.sati-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(min-width:700px){.sati-grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(min-width:860px){.sati-grid{grid-template-columns:repeat(4,minmax(0,1fr))}}
@media(min-width:980px){
  /* 🖥️ PC: 5 columnas y la tarjeta un poco más apretada para que quepan los botones */
  .sati-grid{grid-template-columns:repeat(5,minmax(0,1fr));gap:10px}
  .sati{--sati-foto:130px}
  .sati-card__cuerpo{padding:8px 9px 10px;gap:6px}
  .sati-card__nombre{font-size:13.5px}
  .sati-card__meta{font-size:11px}
  .sati-dato{font-size:10.5px;padding:2px 7px}
  .sati-acciones{gap:4px;padding-top:6px}
  .sati-acciones .btn-mini{padding:6px 8px;font-size:11.5px}
  .sati-inv{font-size:12px;padding:7px 8px;min-height:34px}
  .sati-inv__undo{width:34px;min-height:34px;font-size:14px}
}
@media(max-width:640px){
  .sati-chip{font-size:13px;padding:9px 12px;min-height:42px}
  .sati-busca{min-width:100%}
  .sati-barra select{flex:1}
  .sati-acciones .btn-mini{padding:10px 12px;font-size:13px}
  .sati-inv{min-height:44px;font-size:14px}
  .sati-inv__undo{width:44px;min-height:44px}
}
</style>

<div class="sati">

  <!-- 1) BUSCADOR + ORDEN + VISTA (todo en un mismo formulario GET) -->
  <form method="get" class="sati-barra" id="sati-form">
    <input type="hidden" name="seccion" value="tiendas">
    <?php foreach (array_keys($pares) as $k): ?>
      <?php if (!empty($sf[$k])): ?><input type="hidden" name="<?= e($k) ?>" value="<?= e($sf[$k]) ?>"><?php endif; ?>
    <?php endforeach; ?>
    <?php if ($sf['estado'] !== ''): ?><input type="hidden" name="estado" value="<?= e($sf['estado']) ?>"><?php endif; ?>
    <?php // 📍 Distrito y 🛵 cómo atiende: viajan con el buscador y con los desplegables de orden y vista. ?>
    <?php if (!empty($sf['dist'])): ?><input type="hidden" name="dist" value="<?= (int)$sf['dist'] ?>"><?php endif; ?>
    <?php if ($sf['vent'] !== ''): ?><input type="hidden" name="vent" value="<?= e($sf['vent']) ?>"><?php endif; ?>

    <div class="sati-busca">
      <input type="search" name="q" id="sati-q" value="<?= e($sf['q']) ?>" autocomplete="off"
             placeholder="🔎 Nombre, rubro, distrito o correo del dueño…">
      <button class="btn-mini b-ok" style="padding:11px 16px;font-size:14px">Buscar</button>
    </div>

    <select name="rub" onchange="this.form.submit()" aria-label="Filtrar por rubro">
      <option value="">🏷️ Todos los rubros (<?= number_format((int)($sg['total'] ?? 0)) ?>)</option>
      <?php foreach ($rubros as $rid => $rr): ?>
        <option value="<?= (int)$rid ?>" <?= (int)$sf['rub'] === (int)$rid ? 'selected' : '' ?>><?= e($rr['nombre']) ?> (<?= number_format($rr['n']) ?>)</option>
      <?php endforeach; ?>
    </select>

    <select name="orden" onchange="this.form.submit()" aria-label="Ordenar por">
      <?php foreach (sat_ordenes() as $k => $lbl): ?>
        <option value="<?= e($k) ?>" <?= $sf['orden'] === $k ? 'selected' : '' ?>><?= e($lbl) ?></option>
      <?php endforeach; ?>
    </select>

    <select name="vista" onchange="this.form.submit()" aria-label="Cómo ver las tiendas">
      <option value="previa" <?= $sf['vista'] === 'previa' ? 'selected' : '' ?>>🖼️ Vista previa</option>
      <option value="tabla"  <?= $sf['vista'] === 'tabla'  ? 'selected' : '' ?>>📋 Tabla</option>
    </select>
  </form>

  <!-- 2) FILTROS (con el número real de tiendas de cada uno) -->
  <div class="sati-caja">
    <div class="sati-caja__titulo">Filtros rápidos · toca para filtrar (el número es el total del sitio)</div>
    <div class="sati-chips">
      <?php foreach (sat_pares_rapidos() as $k): $info = $pares[$k]; ?>
        <?= sat_pildora($sf, $k, 'con', $info[1], $num($k, 'con'), ($k !== 'foto' ? '' : '')) ?>
        <?= sat_pildora($sf, $k, 'sin', $info[2], $num($k, 'sin'), 'sati-chip--alerta') ?>
      <?php endforeach; ?>
    </div>

    <details class="sati-mas" <?= $hay_extra ? 'open' : '' ?>>
      <summary>⚙️ Más filtros ▾</summary>
      <div class="sati-chips">
        <?php foreach (['tel', 'ubi', 'dueno', 'redes', 'dest'] as $k): $info = $pares[$k]; ?>
          <?= sat_pildora($sf, $k, 'con', $info[1], $num($k, 'con')) ?>
          <?= sat_pildora($sf, $k, 'sin', $info[2], $num($k, 'sin'), 'sati-chip--alerta') ?>
        <?php endforeach; ?>
      </div>
    </details>
  </div>

  <!-- 2.b) 📍 DISTRITO Y 🛵 CÓMO ATIENDE (pedido del jefe, 2026-09-20):
       «en el filtro de tiendas falta que filtres por distritos… quiero ver esto pero solo para el
        distrito de Santa… o de vendedores que venden desde internet… o ambulantes».
       Se combinan con todo lo demás: 📍 Santa + 🛵 Ambulante = los ambulantes de Santa. -->
  <?php if ($distritos || $vende_num): ?>
  <div class="sati-caja">
    <?php if ($distritos): ?>
      <div class="sati-caja__titulo">📍 Distrito · toca uno para ver SOLO ese distrito (entra la tienda si está ahí o si ATIENDE ahí)</div>
      <div class="sati-chips">
        <?php foreach ($distritos as $did => $dd): ?>
          <?= sat_pildora_valor($sf, 'dist', (string)$did, '📍 ' . $dd['nombre'], $dd['n']) ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <?php if ($vende_num): ?>
      <div class="sati-caja__titulo sati-caja__titulo--sep">🛵 Cómo atiende (tipo de vendedor)</div>
      <div class="sati-chips">
        <?php foreach ($vende_lbl as $vk => $vtxt): ?>
          <?php if (!isset($vende_num[$vk])) continue; ?>
          <?= sat_pildora_valor($sf, 'vent', $vk, $vtxt[0], $vende_num[$vk],
                $vk === 'sin_dato' ? 'sati-chip--alerta' : '') ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>

  <!-- 3) ESTADO -->
  <div class="sati-caja">
    <div class="sati-caja__titulo">Estado de la tienda</div>
    <div class="sati-chips">
      <?php foreach ($estados as $k => $info): ?>
        <?= sat_pildora_estado($sf, $k, $info[0], $num_estado($k)) ?>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- 4) FILTROS ACTIVOS -->
  <?php if ($puestos): ?>
    <div class="sati-activos">
      <span class="sati-activos__lbl">Filtros activos:</span>
      <?php foreach ($puestos as [$clave, $texto]): ?>
        <a class="sati-quitar" href="<?= e(sat_url($sf, [$clave => '', 'p' => 1])) ?>" title="Quitar este filtro"><?= e($texto) ?> ✕</a>
      <?php endforeach; ?>
      <a class="sati-quitar" style="background:var(--color-primario);border-color:transparent;color:#fff" href="<?= e(sat_url($sf, array_merge(['q' => '', 'estado' => '', 'dist' => '', 'vent' => '', 'rub' => '', 'p' => 1], array_fill_keys(array_keys($pares), '')))) ?>">🧹 Quitar todos</a>
    </div>
  <?php endif; ?>

  <!-- 5) RESULTADO -->
  <p class="sati-resumen" id="sati-resumen">
    <?php if ($total === 0): ?>
      <b>Ninguna tienda</b> cumple estos filtros. Prueba quitando alguno.
    <?php else: ?>
      Mostrando <b><?= number_format($offset + 1) ?>–<?= number_format(min($offset + $por, $total)) ?></b>
      de <b><?= number_format($total) ?></b> tienda(s)
      <?php if ($sf['q'] !== ''): ?> que coinciden con <b>«<?= e($sf['q']) ?>»</b><?php endif; ?>
      · <b><?= number_format((int)($sg['total'] ?? 0)) ?></b> en total en el sitio
      · página <?= (int)$sf['p'] ?> de <?= (int)$paginas ?>
    <?php endif; ?>
  </p>

  <!-- 5.b) LEYENDA DEL BOTÓN DE INVITACIÓN (los 4 colores del jefe) -->
  <?php if ($filas): ?>
    <div class="sati-inv-leyenda">
      <b>📨 Invitación por WhatsApp:</b>
      <span><span class="punto p0"></span>sin enviar</span>
      <span><span class="punto p1"></span>1 vez</span>
      <span><span class="punto p2"></span>2 veces</span>
      <span><span class="punto p3"></span>3 veces o más</span>
      <span style="margin-left:auto">Un clic abre WhatsApp con el mensaje escrito y queda apuntado · <b>↺</b> corrige si te equivocas</span>
    </div>
  <?php endif; ?>

  <?php if (!$filas): ?>
    <div class="empty">No se encontraron tiendas con esos filtros.</div>
  <?php elseif ($sf['vista'] === 'tabla'): ?>

    <div class="sa-table-wrap"><table class="sa-table">
      <thead><tr><th>Foto</th><th>Tienda</th><th>Categoría</th><th>Estado</th><th>Datos</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($filas as $t):
        $wa = trim((string)$t['whatsapp']) !== ''; $te = trim((string)$t['telefono']) !== ''; ?>
        <tr>
          <td>
            <?php if (!empty($t['portada'])): ?>
              <a href="<?= e(url_negocio($t['slug'])) ?>" target="_blank" rel="noopener"><?= img_tag($t['portada'], $t['nombre'], ['sizes' => '44px', 'class' => 'sati-tabla-mini']) ?></a>
            <?php else: ?>
              <span class="sati-tabla-sinfoto" title="Sin foto">📷</span>
            <?php endif; ?>
          </td>
          <td><b><?= e($t['nombre']) ?></b><div class="mini">/<?= e($t['slug']) ?></div></td>
          <td><?= e($t['categoria_nombre'] ?? '—') ?><?php if (!empty($t['distrito_nombre'])): ?><div class="mini">📍 <?= e($t['distrito_nombre']) ?></div><?php endif; ?>
            <?php // 🛵 Cómo atiende (2026-09-20): se ve siempre en la tabla, para no tener que adivinar. ?>
            <div class="mini"><?= e(sat_vende_corto($t['ubicacion_tipo'] ?? '')) ?></div></td>
          <td><span class="badge-est <?= $badge_estado[$t['estado']] ?? 'b-oculto' ?>"><?= e($t['estado']) ?></span></td>
          <td>
            <span class="mini">📦 <?= (int)$t['nproductos'] ?> prod · 📷 <?= (int)$t['nfotos'] ?> fotos · 👁️ <?= (int)$t['vistas_count'] ?></span>
            <div class="mini"><?= $wa ? '📱 ' . e($t['whatsapp']) : '<span class="danger">📱 sin WhatsApp</span>' ?><?= $te ? ' · 📞 ' . e($t['telefono']) : ' · <span class="danger">📞 sin teléfono</span>' ?></div>
            <?php if ($t['dueno_email']): ?><div class="mini">👤 <?= e($t['dueno_email']) ?></div><?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap;margin-bottom:6px">
              <?php if ($t['estado'] === 'pendiente'): ?>
                <?= sat_boton_post('tienda_estado', (int)$t['id'], 'activo', '✓ Aprobar', 'b-ok', '¿Aprobar y publicar esta tienda?') ?>
                <?= sat_boton_post('tienda_estado', (int)$t['id'], 'rechazado', '⛔ Rechazar', 'b-no', '¿Rechazar esta tienda?') ?>
              <?php elseif ($t['estado'] === 'activo'): ?>
                <?= sat_boton_post('tienda_estado', (int)$t['id'], 'inactivo', '🙈 Ocultar', 'b-ghost', '¿Ocultar esta tienda? Dejará de verse en el sitio.') ?>
              <?php else: ?>
                <?= sat_boton_post('tienda_estado', (int)$t['id'], 'activo', '🟢 Publicar', 'b-ok', '¿Publicar esta tienda otra vez?') ?>
              <?php endif; ?>
            </div>
            <a class="btn-mini b-ghost" href="<?= e(url_negocio($t['slug'])) ?>" target="_blank" rel="noopener">Ver</a>
            <?= sat_boton_post('tienda_eliminar', (int)$t['id'], '', '🗑 Eliminar', 'b-no', '⚠️ ¿ELIMINAR esta tienda y TODOS sus productos/fotos? Esta acción no se puede deshacer.') ?>
            <!-- 📨 INVITACIÓN (pedido del jefe, 2026-09-17) -->
            <div class="sati-inv-fila" style="border-top:0;padding-top:4px"><?= sat_boton_invitacion($t, $sati_inv_de($t['id']), $sati_cred_de($t['id'])) ?></div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>

  <?php else: ?>

    <div class="sati-grid">
      <?php foreach ($filas as $t):
        $nprod  = (int)$t['nproductos'];
        $nfotos = (int)$t['nfotos'];
        $wa     = trim((string)$t['whatsapp']) !== '';
        $te     = trim((string)$t['telefono']) !== '';
        $ubi    = ($t['lat'] !== null && $t['lng'] !== null);
        // 🛵 CÓMO ATIENDE (2026-09-20): el 🏪 local es lo normal (4 286 de 4 395), así que solo se
        // pinta la píldora cuando la tienda NO es un local: ambulante, a domicilio, a todo el país,
        // mayorista o sin dato. Así los casos que buscan el jefe saltan a la vista en la rejilla.
        $vtipo  = trim((string)($t['ubicacion_tipo'] ?? ''));
        $vesp   = ($vtipo === 'fisica') ? '' : sat_vende_corto($vtipo);
        $busca  = mb_strtolower($t['nombre'] . ' ' . $t['slug'] . ' ' . ($t['categoria_nombre'] ?? '') . ' ' . ($t['distrito_nombre'] ?? '') . ' ' . ($t['dueno_email'] ?? ''));
      ?>
      <article class="sati-card" data-busca="<?= e($busca) ?>">
        <a class="sati-card__foto" href="<?= e(url_negocio($t['slug'])) ?>" target="_blank" rel="noopener" title="Abrir la ficha de la tienda">
          <?php if (!empty($t['portada'])): ?>
            <?= img_tag($t['portada'], $t['nombre'], ['sizes' => '(max-width:640px) 92vw, 320px', 'class' => 'sati-card__img']) ?>
          <?php else: ?>
            <span class="sati-card__sinfoto">📷<b>SIN FOTO</b></span>
          <?php endif; ?>
          <span class="badge-est sa-tienda-badge <?= $badge_estado[$t['estado']] ?? 'b-oculto' ?> sati-card__estado"><?= e($t['estado']) ?></span>
        </a>
        <div class="sati-card__cuerpo">
          <h3 class="sati-card__nombre"><?= e($t['nombre']) ?></h3>
          <div class="sati-card__meta">
            <?= e($t['categoria_nombre'] ?? 'Sin rubro') ?><?= !empty($t['distrito_nombre']) ? ' · 📍 ' . e($t['distrito_nombre']) : '' ?>
            <br>/<?= e($t['slug']) ?>
          </div>
          <div class="sati-card__datos">
            <?php if ($vesp !== ''): ?>
              <span class="sati-dato <?= $vtipo === '' ? 'sati-dato--mal' : 'sati-dato--info' ?>"><?= e($vesp) ?></span>
            <?php endif; ?>
            <span class="sati-dato <?= $nprod > 0 ? 'sati-dato--ok' : 'sati-dato--mal' ?>">📦 <?= $nprod ?> prod</span>
            <span class="sati-dato <?= $nfotos > 0 ? 'sati-dato--ok' : 'sati-dato--mal' ?>">📷 <?= $nfotos ?> fotos</span>
            <span class="sati-dato <?= $wa ? 'sati-dato--ok' : 'sati-dato--mal' ?>"><?= $wa ? '📱 ' . e($t['whatsapp']) : '📱 sin WhatsApp' ?></span>
            <span class="sati-dato <?= $te ? '' : 'sati-dato--mal' ?>"><?= $te ? '📞 ' . e($t['telefono']) : '📞 sin teléfono' ?></span>
            <span class="sati-dato <?= $ubi ? '' : 'sati-dato--mal' ?>"><?= $ubi ? '📍 con GPS' : '📍 sin GPS' ?></span>
            <span class="sati-dato">👁️ <?= (int)$t['vistas_count'] ?></span>
            <?php if ((int)$t['destacado'] === 1): ?><span class="sati-dato sati-dato--info">⭐ destacada</span><?php endif; ?>
            <span class="sati-dato <?= $t['dueno_email'] ? '' : 'sati-dato--mal' ?>">👤 <?= $t['dueno_email'] ? e($t['dueno_email']) : 'sin dueño' ?></span>
          </div>
          <div class="sati-acciones">
            <?php if ($t['estado'] === 'pendiente'): ?>
              <?= sat_boton_post('tienda_estado', (int)$t['id'], 'activo', '✓ Aprobar', 'b-ok', '¿Aprobar y publicar esta tienda?') ?>
              <?= sat_boton_post('tienda_estado', (int)$t['id'], 'rechazado', '⛔ Rechazar', 'b-no', '¿Rechazar esta tienda?') ?>
            <?php elseif ($t['estado'] === 'activo'): ?>
              <?= sat_boton_post('tienda_estado', (int)$t['id'], 'inactivo', '🙈 Ocultar', 'b-ghost', '¿Ocultar esta tienda? Dejará de verse en el sitio.') ?>
            <?php else: ?>
              <?= sat_boton_post('tienda_estado', (int)$t['id'], 'activo', '🟢 Publicar', 'b-ok', '¿Publicar esta tienda otra vez?') ?>
            <?php endif; ?>
            <a class="btn-mini b-ghost" href="<?= e(url_negocio($t['slug'])) ?>" target="_blank" rel="noopener">👁 Ver ficha</a>
            <?= sat_boton_post('tienda_eliminar', (int)$t['id'], '', '🗑', 'b-no', '⚠️ ¿ELIMINAR esta tienda y TODOS sus productos/fotos? Esta acción no se puede deshacer.') ?>
          </div>
          <!-- 📨 INVITACIÓN (pedido del jefe, 2026-09-17): va DEBAJO de la tienda -->
          <div class="sati-inv-fila"><?= sat_boton_invitacion($t, $sati_inv_de($t['id']), $sati_cred_de($t['id'])) ?></div>
          <!-- ✅ REVISADOS (pedido del jefe, 2026-09-18): la marca de la revisión de la tienda.
               Es lo que alimenta el filtro «✅ Revisados» de arriba. -->
          <?php if (sat_revisados_ok()): ?>
            <div class="sati-rev-fila">
              <?php if (!empty($t['revisado_en'])): ?>
                <span class="sati-rev sati-rev--on" title="Revisada el <?= e(date('d/m/Y H:i', strtotime((string)$t['revisado_en']))) ?>">✅ Revisada · <?= e(date('d/m/Y', strtotime((string)$t['revisado_en']))) ?></span>
                <?= sat_boton_post('tienda_revisar_quitar', (int)$t['id'], '', '↩️ Quitar', 'b-ghost', '¿Quitar la marca de REVISADA de esta tienda?') ?>
              <?php else: ?>
                <?= sat_boton_post('tienda_revisar', (int)$t['id'], '', '✅ Marcar revisada', 'b-ok', '') ?>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

  <?php endif; ?>

  <!-- 6) PAGINACIÓN -->
  <?php if ($paginas > 1): ?>
    <div class="sati-pag">
      <?php if ($sf['p'] > 1): ?>
        <a href="<?= e(sat_url($sf, ['p' => $sf['p'] - 1])) ?>">← Anterior</a>
      <?php else: ?>
        <span class="pag pag-off">← Anterior</span>
      <?php endif; ?>
      <span class="pag">Página <?= (int)$sf['p'] ?> de <?= (int)$paginas ?></span>
      <?php if ($sf['p'] < $paginas): ?>
        <a href="<?= e(sat_url($sf, ['p' => $sf['p'] + 1])) ?>">Siguiente →</a>
      <?php else: ?>
        <span class="pag pag-off">Siguiente →</span>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <!-- 7) FORMULARIO OCULTO de la invitación: lo manda el `fetch` del botón 📨.
       (Sin `action`, igual que los demás botones del panel: así el POST conserva los filtros.) -->
  <form method="post" id="sati-inv-form" style="display:none">
    <?= csrf_campo() ?>
    <input type="hidden" name="accion" value="tienda_invitar">
    <input type="hidden" name="id" value="">
  </form>

</div>

<script>
/* ⚡ BUSCADOR PREDICTIVO de la vista de tiendas
   1) Mientras escribes, se filtran AL INSTANTE las tarjetas de esta página.
   2) Si dejas de escribir (0,8 s), se busca en TODAS las tiendas del sitio. */
(function () {
  var inp = document.getElementById('sati-q');
  if (!inp || !inp.form) return;
  var tarjetas = Array.prototype.slice.call(document.querySelectorAll('.sati-card'));
  var resumen  = document.getElementById('sati-resumen');
  var textoOriginal = resumen ? resumen.innerHTML : '';
  var temporizador = null;

  function sinTildes(s) {
    s = (s || '').toLowerCase();
    return s.normalize ? s.normalize('NFD').replace(/[\u0300-\u036f]/g, '') : s;
  }
  function filtrar() {
    var q = sinTildes(inp.value.trim());
    var n = 0;
    tarjetas.forEach(function (c) {
      var ok = (q === '') || (sinTildes(c.getAttribute('data-busca') || '').indexOf(q) !== -1);
      c.style.display = ok ? '' : 'none';
      if (ok) n++;
    });
    if (!resumen) return;
    if (q === '') {
      resumen.innerHTML = textoOriginal;
      resumen.classList.remove('sati-resumen--vivo');
    } else {
      resumen.className = 'sati-resumen sati-resumen--vivo';
      resumen.textContent = '🔎 ' + n + ' de ' + tarjetas.length + ' tiendas de esta página coinciden con «' + inp.value.trim() + '» · se buscará en todas en un instante…';
    }
  }
  inp.addEventListener('input', function () {
    filtrar();
    clearTimeout(temporizador);
    temporizador = setTimeout(function () {
      if (inp.value.trim() === (inp.defaultValue || '').trim()) return; // no cambió: no recargar
      inp.form.submit();
    }, 800);
  });
  inp.addEventListener('keydown', function (ev) { if (ev.key === 'Enter') clearTimeout(temporizador); });

  // Si la página se cargó con una búsqueda, el cursor vuelve al buscador para seguir escribiendo.
  if (inp.value.trim() !== '') {
    inp.focus();
    try { inp.setSelectionRange(inp.value.length, inp.value.length); } catch (e) {}
  }
})();

/* 📨 BOTÓN DE INVITACIÓN (pedido del jefe, 2026-09-17)
   El mensaje que abre WhatsApp lleva la invitación + el USUARIO y la CONTRASEÑA de la tienda,
   así que hay dos caminos:
     · data-listo="1" → el enlace ya viene armado desde el servidor: se abre AL INSTANTE (enlace
       normal) y el envío se apunta de fondo para que el COLOR quede guardado.
     · data-listo="0" → es la PRIMERA invitación de esa tienda: hay que crearle la cuenta y su
       contraseña, así que se abre una pestaña en blanco (dentro del clic, para que el navegador
       no la bloquee) y, cuando el servidor responde con el enlace, esa pestaña se va a WhatsApp.
   El ↺ borra la última invitación apuntada (con confirmación) y repinta. */
(function () {
  var form = document.getElementById('sati-inv-form');

  function pintar(id, n) {
    n = Math.max(0, parseInt(n, 10) || 0);
    var nivel = Math.min(3, n);
    var boton = document.querySelector('.sati-inv[data-inv-id="' + id + '"]');
    if (boton) {
      boton.className = 'sati-inv sati-inv--n' + nivel;
      var num = boton.querySelector('.sati-inv__n');
      if (num) num.textContent = n;
      var creds = boton.getAttribute('data-creds') || '';
      boton.setAttribute('title', 'Invitación por WhatsApp'
        + (creds !== '' ? ' con ' + creds : ' con el usuario y la contraseña')
        + ' · ' + (n <= 0 ? 'sin invitación enviada'
                          : (n === 1 ? 'invitación enviada 1 vez' : 'invitaciones enviadas ' + n + ' veces')));
    }
    var undo = document.querySelector('.sati-inv__undo[data-inv-id="' + id + '"]');
    if (undo) undo.disabled = (n < 1);
  }

  function apuntar(accion, id, ok, error) {
    if (!form) return;
    form.querySelector('[name=accion]').value = accion;
    form.querySelector('[name=id]').value     = id;
    fetch(window.location.pathname + window.location.search, {
      method: 'POST',
      body: new FormData(form),
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (r) { return r.json(); }).then(function (d) {
      if (d && d.ok) ok(d);
      else if (d && d.msg) { if (error) error(); alert(d.msg); }
    }).catch(function () {
      /* Sin conexión no se pudo apuntar: se avisa sin romper nada. */
      if (error) error();
      alert('⚠️ No pude preparar la invitación. Revisa tu conexión y recarga la página.');
    });
  }

  window.satInvitar = function (a) {
    var id = parseInt(a.getAttribute('data-inv-id'), 10) || 0;
    if (id <= 0) return false;

    // ¿Ya tiene usuario y contraseña armados? Enlace directo, sin esperas.
    if (a.getAttribute('data-listo') === '1') {
      apuntar('tienda_invitar', id, function (d) { pintar(id, d.n); if (d.url) a.href = d.url; }, null);
      return true;   // ← deja que el navegador abra WhatsApp con el mensaje escrito
    }

    // Primera vez: hay que crear la cuenta y la contraseña, así que la pestaña se abre YA
    // (dentro del clic) y se manda a WhatsApp cuando el servidor contesta.
    var w = window.open('about:blank', '_blank');
    apuntar('tienda_invitar', id, function (d) {
      pintar(id, d.n);
      if (d.usuario && d.clave) a.setAttribute('data-creds', 'usuario ' + d.usuario + ' · contraseña ' + d.clave);
      if (d.url) {
        a.href = d.url;
        a.setAttribute('data-listo', '1');
        a.setAttribute('target', '_blank');
        a.setAttribute('rel', 'noopener');
        if (w) w.location.href = d.url; else window.location.href = d.url;
      } else if (w) { w.close(); }
      if (d.aviso) alert('📨 ' + d.aviso);
    }, function () { if (w) w.close(); });
    return false;   // el clic ya está manejado
  };

  window.satInvDeshacer = function (b) {
    var id = parseInt(b.getAttribute('data-inv-id'), 10) || 0;
    if (id > 0 && confirm('¿Quitar UNA invitación del conteo de esta tienda?')) {
      apuntar('tienda_invitar_deshacer', id, function (d) { pintar(id, d.n); }, null);
    }
    return false;
  };
})();
</script>
