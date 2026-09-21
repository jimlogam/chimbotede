<?php
/**
 * banners.php — MÓDULO DE PUBLICIDAD (banners rotativos programados)
 * ================================================================
 * Chimbote al Día · Se carga automáticamente desde helpers.php.
 *
 * Funcionalidades:
 *  - Cada banner tiene VIGENCIA (fecha_inicio / fecha_fin; sin fecha_fin = infinito).
 *    Al cumplirse la fecha_fin el banner se DESACTIVA SOLO (sin intervención manual).
 *  - Cada banner tiene FRANJAS HORARIAS preferidas (mañana / tarde / noche) o 24 h.
 *    Fuera de su franja NO se muestra.
 *  - Rotación ALEATORIA sin repetir banners dentro de la misma página (carga).
 *  - Restricción opcional por rubros (categorías): vacío = libre en todo el sitio.
 *  - Estadísticas de impresiones y clics (totales + por día en directorio_banner_stats).
 *  - "Temas" de necesidad -> negocios que la resuelven (modal con filtro por distrito).
 *  - 🔎 BÚSQUEDA del sitio (2026-09-11, pedido del jefe): un banner puede llevar a los
 *    RESULTADOS DE BÚSQUEDA con el término que él escriba en el panel (`buscar.php?q=…`).
 *    Prioridad del clic: busqueda > enlace externo > tema (modal).
 *
 * Franjas horarias (decisión de marketing, editables aquí):
 *   🌅 mañana 06:00–11:59 | ☀️ tarde 12:00–18:59 | 🌙 noche 19:00–23:59
 *   '24' (o sin franjas) = todo el día.
 */

if (!defined('SITE_URL')) { require_once __DIR__ . '/../config.php'; }

/* ================= FRANJAS HORARIAS ================= */

function banners_franjas_def(): array {
    return [
        ['k' => 'manana', 'label' => 'Mañana', 'icono' => '🌅', 'desde' => 6,  'hasta' => 12],
        ['k' => 'tarde',  'label' => 'Tarde',  'icono' => '☀️', 'desde' => 12, 'hasta' => 19],
        ['k' => 'noche',  'label' => 'Noche',  'icono' => '🌙', 'desde' => 19, 'hasta' => 24],
    ];
}

/** Franja(s) actual(es) según la hora del servidor (America/Lima). */
function banners_franja_actual(): string {
    $h = (int)date('G');
    foreach (banners_franjas_def() as $f) {
        if ($h >= $f['desde'] && $h < $f['hasta']) return $f['k'];
    }
    return ''; // 00:00–05:59: solo banners 24 h
}

/** Etiquetas legibles de una columna franjas (csv) para el panel. */
function banners_franjas_etiquetas(?string $csv): string {
    if (!$csv || $csv === '24' || $csv === '') return 'Todo el día';
    $out = [];
    foreach (explode(',', $csv) as $k) {
        foreach (banners_franjas_def() as $f) { if ($f['k'] === $k) { $out[] = $f['icono'] . ' ' . $f['label']; break; } }
    }
    return $out ? implode(' · ', $out) : 'Todo el día';
}

/** ¿El banner puede mostrarse en esta hora? (csv de franjas en $b['franjas']) */
function banner_en_franja(array $b): bool {
    $csv = trim((string)($b['franjas'] ?? ''));
    if ($csv === '' || $csv === '24' || $csv === '24h') return true;          // todo el día
    $actual = banners_franja_actual();
    if ($actual === '') return false;                                          // madrugada sin 24 h
    $set = array_flip(array_map('trim', explode(',', $csv)));
    return isset($set[$actual]);
}

/** ¿El banner está dentro de su vigencia de fechas? */
function banner_en_vigencia(array $b, string $hoy = ''): bool {
    $hoy = $hoy ?: date('Y-m-d');
    if (!empty($b['fecha_inicio']) && $b['fecha_inicio'] > $hoy) return false;
    if (!empty($b['fecha_fin'])     && $b['fecha_fin']     < $hoy) return false;
    return true;
}

/** Desactiva SOLO los banners cuya fecha_fin ya pasó (ejecutar en cada request). */
function banners_auto_expirar(): void {
    static $hecho = false;
    if ($hecho) return;
    $hecho = true;
    try {
        db()->exec("UPDATE directorio_banners SET activo=0 WHERE activo=1 AND fecha_fin IS NOT NULL AND fecha_fin < CURDATE()");
    } catch (Throwable $e) { /* la tabla aún no existe: silencioso */ }
}

/* ================= ELECCIÓN / ROTACIÓN ================= */

/** Filtro de bots para no inflar impresiones. */
function banner_es_bot(): bool {
    static $bot = null;
    if ($bot !== null) return $bot;
    $ua = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');
    $bot = (strpos($ua, 'bot') !== false || strpos($ua, 'spider') !== false || strpos($ua, 'crawl') !== false
            || strpos($ua, 'curl') !== false || strpos($ua, 'python') !== false || $ua === '');
    return $bot;
}

/**
 * Pool de banners ELEGIBLES ahora mismo (vigencia + franja + imagen + activo).
 * $categoria_id: si se pasa (contexto de página), respeta rubros restringidos
 * del banner (rubros vacío = libre en cualquier rubro).
 */
function banners_elegibles(?int $categoria_id = null): array {
    banners_auto_expirar();
    try {
        $rows = db()->query("SELECT * FROM directorio_banners WHERE activo=1 ORDER BY orden ASC, id ASC")->fetchAll();
    } catch (Throwable $e) {
        return []; // módulo aún sin migrar
    }
    $out = [];
    foreach ($rows as $b) {
        if (empty($b['imagen']) && empty($b['imagen_h'])) continue;   // banner sin imagen
        if (!banner_en_vigencia($b)) continue;
        if (!banner_en_franja($b)) continue;
        if ($categoria_id !== null) {
            $rubros = array_filter(array_map('intval', explode(',', (string)($b['rubros'] ?? ''))));
            if ($rubros && !in_array((int)$categoria_id, $rubros, true)) continue;  // restringido a otros rubros
        }
        $out[] = $b;
    }
    shuffle($out); // rotación aleatoria
    return $out;
}

/**
 * Entrega hasta $n banners únicos (nunca se repite uno en la misma página/carga).
 * La memoria estática es compartida entre todos los huecos de la página.
 */
function banners_para(int $n = 1, ?int $categoria_id = null, array $excluir = []): array {
    static $usados = [];
    $n = max(1, min((int)$n, 12));
    $pool = banners_elegibles($categoria_id);
    $out = [];
    foreach ($pool as $b) {
        $id = (int)$b['id'];
        if (isset($usados[$id]) || in_array($id, $excluir, true)) continue;
        $usados[$id] = 1;
        $out[] = $b;
        if (count($out) >= $n) break;
    }
    return $out;
}

/** Un banner único (para un hueco suelto). */
function banner_aleatorio(?int $categoria_id = null): ?array {
    $g = banners_para(1, $categoria_id);
    return $g[0] ?? null;
}

/* ================= ESTADÍSTICAS ================= */

function banner_registrar_impresion(int $banner_id): void {
    if (!$banner_id || banner_es_bot()) return;
    $pdo = db(); $f = date('Y-m-d');
    try {
        $pdo->prepare("UPDATE directorio_banners SET impresiones=impresiones+1 WHERE id=?")->execute([$banner_id]);
        $pdo->prepare("INSERT INTO directorio_banner_stats (banner_id, fecha, impresiones, clics) VALUES (?,?,1,0)
                       ON DUPLICATE KEY UPDATE impresiones=impresiones+1")->execute([$banner_id, $f]);
    } catch (Throwable $e) { /* tabla sin migrar: silencioso */ }
}

function banner_registrar_clic(int $banner_id): void {
    if (!$banner_id || banner_es_bot()) return;
    $pdo = db(); $f = date('Y-m-d');
    try {
        $pdo->prepare("UPDATE directorio_banners SET clics=clics+1 WHERE id=?")->execute([$banner_id]);
        $pdo->prepare("INSERT INTO directorio_banner_stats (banner_id, fecha, impresiones, clics) VALUES (?,?,0,1)
                       ON DUPLICATE KEY UPDATE clics=clics+1")->execute([$banner_id, $f]);
    } catch (Throwable $e) { /* silencioso */ }

    // 🔔 Aviso al jefe (apagado por defecto: los banners rotan mucho)
    if (function_exists('aviso')) {
        aviso('banner_clic', [
            'banner_id'  => (int)$banner_id,
            'clave'      => 'bclic:' . (int)$banner_id . ':' . (ip_real()) . ':' . date('YmdH'),
            'dedupe_min' => 60,
            'resumen'    => 'clic en banner #' . (int)$banner_id,
        ]);
    }
}

/* ================= 🔎 BÚSQUEDA DEL SITIO (banner que lleva a buscar.php) =================
 * Pedido del jefe (2026-09-11): un banner puede llevar a los RESULTADOS DE BÚSQUEDA del
 * sitio con el término que él escriba en el panel. Ejemplo suyo: si escribe «Perros», el
 * clic busca «Perros»; si escribe «Desayuno», busca eso y muestra los resultados en
 * https://dechimbote.com/buscar.php?q=Desayunos
 *
 * POR QUÉ ASÍ y no reusando `enlace`:
 *  · El campo `enlace` es para irse FUERA del sitio (abre en pestaña nueva). Aquí la
 *    navegación es INTERNA (misma pestaña), se escribe una palabra y no una URL, y el
 *    panel comprueba en vivo que el término tenga resultados antes de guardarlo.
 *  · Orden de prioridad al hacer clic:  busqueda  >  enlace  >  tema (modal).
 *
 * La columna `busqueda` la crea sola el panel (`banners_asegurar_busqueda()`), igual que otras
 * pestañas auto-instalan las suyas: así no hace falta subir un migrador que el
 * escáner del hosting pueda poner en cuarentena.
 */

/** ¿La tabla de banners ya tiene la columna `busqueda`? (memorizado por request) */
function banners_col_busqueda(bool $recalcular = false): bool {
    static $hay = null;
    if ($hay === null || $recalcular) {
        try { $hay = (bool)db()->query("SHOW COLUMNS FROM directorio_banners LIKE 'busqueda'")->fetch(); }
        catch (Throwable $e) { $hay = false; }   // tabla sin migrar: el módulo no rompe
    }
    return (bool)$hay;
}

/**
 * Crea la columna `busqueda` si falta (idempotente y silenciosa).
 * Se llama al abrir la pestaña 📢 Banners y antes de guardar un banner.
 * ⚠️ Nunca encadenar el ALTER a otra columna (ej. `AFTER plan_hasta`): si esa columna no
 * existe en producción, el ALTER revienta (GUIA_DESPLIEGUE_Y_ENTORNO.md §8).
 */
function banners_asegurar_busqueda(): bool {
    if (banners_col_busqueda()) return true;
    try {
        db()->exec("ALTER TABLE directorio_banners ADD COLUMN busqueda VARCHAR(190) NULL AFTER tema");
    } catch (Throwable $e) {
        return false;
    }
    return banners_col_busqueda(true);
}

/** Término limpio listo para guardar (sin espacios de sobra, tope 190) o null si vacío. */
function banners_busqueda_limpiar($termino): ?string {
    $t = trim(preg_replace('/\s+/u', ' ', (string)$termino));
    if ($t === '') return null;
    return mb_substr($t, 0, 190);
}

/** URL de la búsqueda del sitio para un término ('' si no hay término). */
function banners_busqueda_url($termino): string {
    $termino = trim((string)$termino);
    return $termino === '' ? '' : url('buscar.php?q=' . rawurlencode($termino));
}

/* ================= RENDER (un slot = una imagen del mismo tamaño) ================= */

/**
 * HTML de una fila/grilla de banners ya elegidos (mismo tamaño uniforme).
 * $banners: resultado de banners_para()/banner_aleatorio().
 * Las impresiones se registran al renderizar, SALVO que se pase `$contar = false`.
 *
 * 🖼️ `$contar` (2026-09-18): en la ficha de una tienda el banner se cuela **cada dos fichas de producto**
 * (orden del jefe), así que una tienda con 226 productos pinta el mismo banner varias veces: la PRIMERA
 * vuelta cuenta como impresión y las repeticiones no, para no inflar la estadística de la zona de banners.
 *
 * 📐 `$sizes` (2026-09-19): el `sizes` del `<img>` dice cuánto mide el hueco de verdad, y con eso el
 * navegador elige la foto del tamaño justo. El valor de siempre (1 banner por fila) sigue siendo el de
 * defecto: **sin este parámetro no cambia nada** en la portada ni en el resto del sitio. Lo usa la ficha
 * cuando pone **dos banners por fila** (ahí cada hueco es la mitad del ancho, no un tercio).
 */
function banners_render(array $banners, string $clase_extra = '', bool $contar = true, string $sizes = ''): string {
    if (!$banners) return '';
    $html = '<div class="banners-fila' . ($clase_extra ? ' ' . e($clase_extra) : '') . '">';
    foreach ($banners as $b) {
        $id    = (int)$b['id'];
        // Ruta RELATIVA: img_tag() necesita la ruta para buscar las versiones de
        // 300/800 px (el motor las tiene desde el 2026-09-10) y decidir con srcset.
        $ruta_img = (string)($b['imagen'] ?? ($b['imagen_h'] ?? ''));
        $tema  = trim((string)($b['tema'] ?? ''));
        $enlace= trim((string)($b['enlace'] ?? ''));
        $busq  = trim((string)($b['busqueda'] ?? ''));
        if ($contar) banner_registrar_impresion($id); // estadística: impresión (una por banner y página)

        $datos = ' data-banner="' . $id . '"';
        $datos .= ' data-tema="' . e($tema) . '"';
        $datos .= ' data-nombre="' . e($b['titulo'] ?? '') . '"';
        if ($busq !== '') $datos .= ' data-busqueda="' . e($busq) . '"';

        /* Destino del clic, por orden de prioridad:
           1) 🔎 BÚSQUEDA del sitio (buscar.php?q=…) — navegación INTERNA, misma pestaña.
           2) 🔗 Enlace externo — se abre en pestaña nueva (como siempre).
           3) Nada (#): si hay tema, el JS abre el modal de negocios. */
        if ($busq !== '') {
            $href  = e(banners_busqueda_url($busq));
            $extra = '';
        } elseif ($enlace !== '') {
            $href  = e($enlace);
            $extra = ' target="_blank" rel="noopener"';
        } else {
            $href  = '#';
            $extra = '';
        }
        $html .= '<a class="banner-anuncio" href="' . $href . '"' . $extra . $datos . '>';
        // Móvil: 1 banner por fila a todo el ancho · escritorio: 3 columnas (o las que diga la fila).
        $html .= img_tag($ruta_img, $b['titulo'] ?? 'Publicidad', [
            'sizes' => $sizes !== '' ? $sizes : '(max-width: 768px) 100vw, (max-width: 1200px) 33vw, 400px',
        ]);
        $html .= '</a>';
    }
    $html .= '</div>';
    return $html;
}

/** Atajo: HTML de $n banners únicos para un hueco (contexto opcional de categoría). */
function banners_fila_html(int $n = 3, ?int $categoria_id = null, string $clase_extra = ''): string {
    return banners_render(banners_para($n, $categoria_id), $clase_extra);
}

/* ================= TEMAS DE NECESIDAD -> NEGOCIOS (modal) ================= */

/**
 * Catálogo de temas: clave (lo que guarda banner.tema) -> búsqueda.
 * 'cat'  = slug de categoría exacta   |  'kw' = palabra clave extra (nombre/descripción/productos)
 */
function banners_temas(): array {
    $g = static function (string $slug, string $nombre): array {
        return ['cat' => $slug, 'kw' => '', 'nombre' => $nombre,
            'frase' => 'Resuelve tu necesidad ahora. Estos negocios de ' . $nombre . ' te pueden ayudar 👇',
            'frase_res' => 'Estos son los negocios de ' . $nombre];
    };
    return [
        'farmacias'     => $g('farmacias', 'farmacias y boticas'),
        'grifos'        => $g('grifos', 'grifos y gasolineras'),
        'gas'           => ['cat' => '', 'kw' => 'gas', 'nombre' => 'reparto de gas doméstico',
            'frase' => '¿Se te acabó el gas a media cocina? Te lo llevamos a domicilio 🍳.',
            'frase_res' => 'Estos son proveedores de gas doméstico'],
        'mecanicos'     => $g('mecanicos', 'talleres mecánicos'),
        'restaurantes'  => $g('restaurantes', 'restaurantes y comidas'),
        'veterinarias'  => $g('veterinarias', 'veterinarias'),
        'supermercados' => $g('supermercados', 'supermercados y bodegas'),
        'informatica'   => $g('informatica', 'informática y celulares'),
        'abogados'      => $g('abogados', 'abogados y estudios jurídicos'),
        'deportes'      => $g('deportes', 'deportes'),
        'belleza'       => $g('belleza', 'salones de belleza y barberías'),
        'dentistas'     => $g('dentistas', 'dentistas y clínicas dentales'),
        'educacion'     => $g('educacion', 'educación y academias'),
        'ropa'          => $g('ropa', 'tiendas de ropa'),
        'joyas'         => $g('joyas', 'joyerías'),
        'gimnasios'     => $g('gimnasios', 'gimnasios'),
        'gasfiteros'    => $g('gasfiteros', 'gasfiteros'),
        'manejo'        => $g('manejo', 'escuelas de manejo'),
        'inmobiliarias' => $g('inmobiliarias', 'inmobiliarias'),
        'hoteles'       => $g('hoteles', 'hoteles y hospedajes'),
    ];
}

/** Resuelve un tema a su configuración (cat/kw/nombre/frases). Si no existe, lo usa como slug. */
function banner_tema_cfg(string $tema): array {
    $tema = strtolower(trim($tema));
    $t = banners_temas();
    if ($tema !== '' && isset($t[$tema])) return $t[$tema];
    return ['cat' => $tema, 'kw' => '', 'nombre' => ($tema !== '' ? $tema : 'negocios'),
            'frase' => 'Encontraste justo lo que buscabas. Estos negocios te pueden ayudar 👇',
            'frase_res' => 'Estos son los mejores resultados'];
}

/**
 * Negocios que resuelven la necesidad de un tema, opcionalmente filtrados por distrito.
 * Coincide por categoría exacta y/o palabra clave en nombre/descripción/productos.
 */
function banners_resultados_tema(string $tema, string $distrito = ''): array {
    $cfg = banner_tema_cfg($tema);
    $catSlug = $cfg['cat'] ?? '';
    $kw      = $cfg['kw'] ?? '';
    if ($catSlug === '' && $kw === '') return [];

    $sql = "SELECT n.id, n.nombre, n.slug, n.whatsapp, n.telefono, n.vistas_count,
                   c.nombre AS categoria, c.slug AS c_slug, c.icono AS c_icono,
                   d.nombre AS distrito, d.slug AS d_slug,
                   (SELECT f.ruta FROM directorio_fotos f WHERE f.negocio_id=n.id ORDER BY f.orden ASC, f.id ASC LIMIT 1) AS foto
            FROM directorio_negocios n
            LEFT JOIN directorio_categorias c ON c.id=n.categoria_id
            LEFT JOIN directorio_distritos  d ON d.id=n.distrito_id
            WHERE n.estado='activo' AND (1=1)";
    $cond = []; $params = [];

    if ($catSlug !== '') { $cond[] = 'c.slug = ?'; $params[] = $catSlug; }
    if ($kw !== '') {
        $like = '%' . $kw . '%';
        $cond[] = "(n.nombre LIKE ? OR n.descripcion LIKE ?
                    OR EXISTS (SELECT 1 FROM directorio_servicios s WHERE s.negocio_id=n.id AND s.activo=1 AND s.titulo LIKE ?))";
        array_push($params, $like, $like, $like);
    }
    if ($distrito !== '') { $cond[] = 'd.slug = ?'; $params[] = $distrito; }

    if ($cond) $sql .= ' AND ' . implode(' AND ', $cond);
    $sql .= ' ORDER BY n.vistas_count DESC, n.nombre ASC LIMIT 24';

    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll();

    foreach ($rows as &$r) { $r = banner_fila_preparar($r); }
    unset($r);
    return $rows;
}

/**
 * Deja una fila de negocio lista para el modal (enlaces armados).
 * La usan la lista normal y la lista por cercanía: las dos pintan igual.
 * Si la fila trae 'distancia_m' (búsqueda por cercanía), añade 'distancia_txt'
 * ("a 850 m" / "a 3,5 km") para que el modal la muestre sin calcular nada.
 */
function banner_fila_preparar(array $r): array {
    $r['url']          = url_negocio($r['slug']);
    $r['whatsapp_url'] = !empty($r['whatsapp']) ? url_whatsapp($r['whatsapp']) : '';
    $r['tel_url']      = !empty($r['telefono']) ? ('tel:' . e($r['telefono'])) : '';
    if (isset($r['distancia_m'])) {
        $r['distancia_txt'] = distancia_txt($r['distancia_m']);
    }
    /* 🖼️ La foto del modal mide 62 px (`.bn-item__img`), así que al navegador se le
       manda la versión de 160 px, no la foto entera (antes bajaba la completa: el
       pendiente que quedaba anotado en GUIA_IMAGENES_Y_OPTIMIZACION.md §10).
       `img_url()` devuelve una URL completa; `banners.js` la usa tal cual. */
    if (!empty($r['foto']) && function_exists('img_url')) {
        $r['foto_chica'] = img_url((string)$r['foto'], IMG_ANCHO_MICRO);
    }
    return $r;
}

/* ====== TEMA + CERCA DE MÍ (la cercanía se aplica ENCIMA del criterio) ======
 * Pedido del jefe Jimmy (2026-09-10): el visitante que entra por un banner de
 * "pollo a la brasa" no puede terminar viendo ferreterías solo porque están a 300 m.
 * Por eso la cercanía NO reemplaza al tema del banner: se usa EXACTAMENTE el mismo
 * criterio de banners_resultados_tema() (categoría y/o palabra clave) y encima se
 * filtra por radio y se ordena del más cercano al más lejano, con los metros visibles.
 *
 * La escalera de radios (2 → 5 → 10 km hasta juntar 20) es la misma de buscar.php,
 * para que el sitio hable un solo idioma (GUIA_CERCA_DE_MI_Y_GEOBUSQUEDA.md).
 *
 * OJO: los negocios SIN lat/lng no pueden salir aquí (no se sabe dónde están); el
 * modal lo avisa al visitante para que no parezca que "desaparecieron" negocios.
 */

/** Radios que prueba sola la búsqueda por cercanía del modal, en este orden. */
function banners_cerca_escalera(): array { return [2, 5, 10]; }

/** Resultados que intenta juntar la búsqueda por cercanía del modal. */
function banners_cerca_tope(): int { return 20; }

/**
 * Negocios del tema ordenados por distancia a un punto, con la escalera 2 → 5 → 10 km.
 * $radio_fijo: null = escalera automática; un número = ese radio exacto (km).
 * Devuelve ['data' => filas, 'radio_km' => float, 'completo' => bool, 'tope' => int].
 */
function banners_resultados_tema_cerca(string $tema, $lat, $lng, string $distrito = '', ?float $radio_fijo = null): array {
    $cfg      = banner_tema_cfg($tema);
    $catSlug  = $cfg['cat'] ?? '';
    $kw       = $cfg['kw'] ?? '';
    $escalera = banners_cerca_escalera();
    $tope     = banners_cerca_tope();
    $radio_max = (float)end($escalera);

    $base = ['data' => [], 'radio_km' => $radio_fijo !== null ? $radio_fijo : $radio_max,
             'completo' => false, 'tope' => $tope];
    if ($catSlug === '' && $kw === '') return $base;

    $lat = (float)$lat; $lng = (float)$lng;

    /** Una consulta al radio pedido, con el MISMO criterio del tema. */
    $consulta = function (float $radio_km) use ($catSlug, $kw, $distrito, $lat, $lng, $tope): array {
        $sql = "SELECT n.id, n.nombre, n.slug, n.whatsapp, n.telefono, n.vistas_count,
                       c.nombre AS categoria, c.slug AS c_slug, c.icono AS c_icono,
                       d.nombre AS distrito, d.slug AS d_slug,
                       (SELECT f.ruta FROM directorio_fotos f WHERE f.negocio_id=n.id ORDER BY f.orden ASC, f.id ASC LIMIT 1) AS foto,
                       ST_Distance_Sphere(POINT(n.lng, n.lat), POINT(?, ?)) AS distancia_m
                FROM directorio_negocios n
                LEFT JOIN directorio_categorias c ON c.id=n.categoria_id
                LEFT JOIN directorio_distritos  d ON d.id=n.distrito_id
                WHERE n.estado='activo' AND n.lat IS NOT NULL AND n.lng IS NOT NULL";
        $params = [$lng, $lat];
        $cond   = [];

        if ($catSlug !== '') { $cond[] = 'c.slug = ?'; $params[] = $catSlug; }
        if ($kw !== '') {
            $like = '%' . $kw . '%';
            $cond[] = "(n.nombre LIKE ? OR n.descripcion LIKE ?
                        OR EXISTS (SELECT 1 FROM directorio_servicios s WHERE s.negocio_id=n.id AND s.activo=1 AND s.titulo LIKE ?))";
            array_push($params, $like, $like, $like);
        }
        if ($distrito !== '') { $cond[] = 'd.slug = ?'; $params[] = $distrito; }
        if ($cond) $sql .= ' AND ' . implode(' AND ', $cond);

        if ($radio_km > 0) {
            $sql .= ' AND ST_Distance_Sphere(POINT(n.lng, n.lat), POINT(?, ?)) <= ?';
            array_push($params, $lng, $lat, $radio_km * 1000);
        }
        $sql .= ' ORDER BY distancia_m ASC LIMIT ' . (int)$tope;

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) { $r = banner_fila_preparar($r); }
        unset($r);
        return $rows;
    };

    if ($radio_fijo !== null) {
        $rows = $consulta($radio_fijo);
        return ['data' => $rows, 'radio_km' => $radio_fijo,
                'completo' => count($rows) >= $tope, 'tope' => $tope];
    }

    // UNA sola consulta al radio mayor: los 20 más cercanos dentro de 10 km son
    // EXACTAMENTE los mismos que saldrían probando 2, luego 5 y luego 10 km.
    $rows     = $consulta($radio_max);
    $completo = (count($rows) >= $tope);
    $radio    = $radio_max;
    if ($completo) {
        $dist_max = 0.0;
        foreach ($rows as $r) { $dist_max = max($dist_max, (float)($r['distancia_m'] ?? 0)); }
        foreach ($escalera as $rk) {
            if ($dist_max <= ($rk * 1000) + 1) { $radio = (float)$rk; break; }
        }
    }
    return ['data' => $rows, 'radio_km' => $radio, 'completo' => $completo, 'tope' => $tope];
}

/* ================= ADMIN (CRUD) ================= */

function banners_listar(string $estado = ''): array {
    $sql = "SELECT b.*,
                   (SELECT SUM(impresiones) FROM directorio_banner_stats s WHERE s.banner_id=b.id) AS stat_imp,
                   (SELECT SUM(clics) FROM directorio_banner_stats s WHERE s.banner_id=b.id) AS stat_clic
            FROM directorio_banners b";
    if ($estado === 'activos')   { $sql .= ' WHERE b.activo=1'; }
    elseif ($estado === 'ocultos'){ $sql .= ' WHERE b.activo=0'; }
    $sql .= ' ORDER BY b.activo DESC, b.orden ASC, b.id ASC';
    try { return db()->query($sql)->fetchAll(); } catch (Throwable $e) { return []; }
}

function banner_por_id(int $id): ?array {
    $stmt = db()->prepare("SELECT * FROM directorio_banners WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

/** Guarda/actualiza un banner. $rubros = array de ids de categoría ([] = todas). */
function banner_guardar(array $d): int {
    $titulo  = mb_substr(trim($d['titulo'] ?? ''), 0, 190);
    $texto   = $d['texto'] !== '' ? mb_substr(trim((string)$d['texto']), 0, 255) : null;
    $imagen  = $d['imagen'] !== '' ? trim($d['imagen']) : null;
    $tema    = $d['tema'] !== '' ? mb_substr(trim($d['tema']), 0, 120) : null;
    $busq    = banners_busqueda_limpiar($d['busqueda'] ?? '');
    $enlace  = $d['enlace'] !== '' ? mb_substr(trim($d['enlace']), 0, 255) : null;
    $inicio  = !empty($d['fecha_inicio']) ? trim($d['fecha_inicio']) : null;
    $fin     = !empty($d['fecha_fin']) ? trim($d['fecha_fin']) : null;   // null = infinito
    $franjas = banners_normalizar_franjas($d['franjas'] ?? []);
    $rubros  = array_values(array_filter(array_map('intval', (array)($d['rubros'] ?? []))));
    $rubrosTxt = $rubros ? implode(',', $rubros) : null;                  // null = libre en todo el sitio
    $activo  = !empty($d['activo']) ? 1 : 0;
    $orden   = (int)($d['orden'] ?? 0);
    $pdo = db();

    // La columna `busqueda` puede no existir todavía (migración pendiente): en ese caso se
    // guarda el resto del banner sin romper nada. El panel la crea sola al abrirse.
    $conBusq = banners_col_busqueda();

    if (!empty($d['id'])) {
        if ($conBusq) {
            $pdo->prepare("UPDATE directorio_banners SET titulo=?, texto=?, imagen=?, tema=?, busqueda=?, enlace=?,
                           fecha_inicio=?, fecha_fin=?, franjas=?, rubros=?, activo=?, orden=?
                           WHERE id=?")
                ->execute([$titulo, $texto, $imagen, $tema, $busq, $enlace, $inicio, $fin, $franjas, $rubrosTxt, $activo, $orden, (int)$d['id']]);
        } else {
            $pdo->prepare("UPDATE directorio_banners SET titulo=?, texto=?, imagen=?, tema=?, enlace=?,
                           fecha_inicio=?, fecha_fin=?, franjas=?, rubros=?, activo=?, orden=?
                           WHERE id=?")
                ->execute([$titulo, $texto, $imagen, $tema, $enlace, $inicio, $fin, $franjas, $rubrosTxt, $activo, $orden, (int)$d['id']]);
        }
        return (int)$d['id'];
    }
    if ($conBusq) {
        $pdo->prepare("INSERT INTO directorio_banners
                       (titulo, texto, imagen, tema, busqueda, enlace, fecha_inicio, fecha_fin, franjas, rubros, activo, orden)
                       VALUES (?,?,?,?,?,?,?,?,?,?,?,?)")
            ->execute([$titulo, $texto, $imagen, $tema, $busq, $enlace, $inicio, $fin, $franjas, $rubrosTxt, $activo, $orden]);
    } else {
        $pdo->prepare("INSERT INTO directorio_banners
                       (titulo, texto, imagen, tema, enlace, fecha_inicio, fecha_fin, franjas, rubros, activo, orden)
                       VALUES (?,?,?,?,?,?,?,?,?,?,?)")
            ->execute([$titulo, $texto, $imagen, $tema, $enlace, $inicio, $fin, $franjas, $rubrosTxt, $activo, $orden]);
    }
    return (int)$pdo->lastInsertId();
}

function banner_eliminar(int $id): void {
    $pdo = db();
    $pdo->prepare("DELETE FROM directorio_banner_stats WHERE banner_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM directorio_banners WHERE id=?")->execute([$id]);
}

/** Normaliza franjas enviadas del panel: lista de claves o '24'/vacío => todo el día. */
function banners_normalizar_franjas(array $sel): string {
    $val = [];
    foreach ($sel as $v) {
        $v = trim((string)$v);
        if ($v === '24' || $v === '24h') return '24';
        foreach (banners_franjas_def() as $f) { if ($f['k'] === $v) { $val[$v] = 1; break; } }
    }
    return $val ? implode(',', array_keys($val)) : '24';
}

/* Stats para el panel: últimos 7 días (por banner o globales) */
function banners_stats_dias(int $limite = 7): array {
    $sql = "SELECT s.fecha,
                   SUM(s.impresiones) AS impresiones, SUM(s.clics) AS clics
            FROM directorio_banner_stats s
            WHERE s.fecha >= DATE_SUB(CURDATE(), INTERVAL " . max(1, min(90, $limite)) . " DAY)
            GROUP BY s.fecha ORDER BY s.fecha ASC";
    try { return db()->query($sql)->fetchAll(); } catch (Throwable $e) { return []; }
}
