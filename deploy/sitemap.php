<?php
/**
 * sitemap.php — Sitemap XML del sitio (lo que Google lee)
 * ============================================================
 * Se sirve en /sitemap.xml gracias a la regla de .htaccess:
 *     RewriteRule ^sitemap\.xml$ sitemap.php [L]
 *
 * ⚠️ POR QUÉ EXISTE: robots.txt declara "Sitemap: https://dechimbote.com/sitemap.xml",
 * pero ese archivo NO existía (daba 404) y el viejo sitemap.php era de la arquitectura
 * anterior (includes/funciones.php) y devolvía ERROR 500. Es decir: el propio robots.txt
 * mandaba a Google a un enlace roto. Ahora se genera aquí, al día, desde la BD.
 *
 * Incluye: la portada, los rubros, los distritos con negocios y TODAS las fichas activas.
 * Guía: GUIA_DESPLIEGUE_Y_ENTORNO.md / GUIA_ESTADISTICAS_DEL_SITIO.md
 *
 * ============================================================================
 * 🆕 2026-09-16 — DOS ARREGLOS DE SEO (pedido del jefe: «que Google nos encuentre lo más pronto»)
 * ============================================================================
 * 1) **EL `lastmod` AHORA ES EL REAL DE CADA PÁGINA.** Antes las 6.916 direcciones decían TODAS
 *    «hoy». Una fecha que siempre es hoy para todo Google la acaba ignorando (no le sirve para
 *    saber qué cambió). Ahora cada tienda lleva la fecha de su última modificación de verdad
 *    (`actualizado_en`), cada producto la suya, cada noticia la de su publicación y cada empleo
 *    la del aviso. Si una dirección NO tiene fecha conocida, **no se declara el `<lastmod>`**
 *    (una etiqueta de menos es mejor que una mentira; es válido en el estándar de sitemaps).
 * 2) **LAS NOTICIAS YA ESTÁN DENTRO**: `/noticias` y cada `/noticia/<slug>`. Era el contenido que
 *    cambia TODOS LOS DÍAS y no se le estaba ofreciendo a Google por ninguna parte: se estaba
 *    perdiendo lo más fresco del sitio.
 *
 * ⚠️ Lo que Google de verdad usa de aquí: el `<loc>` (la dirección) y el `<lastmod>` (si es
 *    creíble). `<priority>` y `<changefreq>` los ignora desde hace años, pero se dejan porque no
 *    hacen daño y otros buscadores (Bing) todavía los miran.
 */

require_once __DIR__ . '/config.php';

header('Content-Type: application/xml; charset=utf-8');

$urls = [];

/**
 * Agrega una dirección al sitemap.
 * `$lastmod` puede venir vacío o en cualquier formato de fecha/hora: si no se puede leer,
 * NO se declara la etiqueta `<lastmod>`.
 */
$agregar = function (string $loc, string $lastmod = '', string $prio = '0.6', string $freq = 'weekly') use (&$urls): void {
    $lm = '';
    if ($lastmod !== '') {
        $t = strtotime($lastmod);
        if ($t !== false) $lm = date('Y-m-d', $t);
    }
    $urls[] = ['loc' => $loc, 'lm' => $lm, 'prio' => $prio, 'freq' => $freq];
};

/** La fecha más nueva de todo el sitio: se la lleva la PORTADA (es lo que más cambia). */
$ultimo_sitio = '';
$recordar = function (string $fecha) use (&$ultimo_sitio): void {
    if ($fecha === '') return;
    if ($ultimo_sitio === '' || strtotime($fecha) > strtotime($ultimo_sitio)) $ultimo_sitio = $fecha;
};

/** El máximo de dos fechas (para saber cuándo cambió un rubro o un distrito). */
$mayor = function (string $a, string $b): string {
    if ($a === '') return $b;
    if ($b === '') return $a;
    return strtotime($b) > strtotime($a) ? $b : $a;
};

// ============================================================================
// 0) DE QUÉ FECHA ES CADA RUBRO Y CADA DISTRITO (según su tienda más reciente)
//    Así las páginas de rubro/distrito también llevan una fecha de verdad.
// ============================================================================
$fecha_rubro    = [];
$fecha_distrito = [];

// ============================================================================
// 1) TODAS LAS FICHAS ACTIVAS (y de paso, la fecha de su rubro y su distrito)
//    Se recorren por lotes para no cargar ~1.700 filas de golpe.
// ============================================================================
try {
    $ultimo_id = 0;
    while (true) {
        $st = db()->prepare("SELECT n.id, n.slug, n.categoria_id, n.distrito_id,
                                    COALESCE(n.actualizado_en, n.creado_en) AS modificado
                               FROM directorio_negocios n
                              WHERE n.estado = 'activo' AND n.slug <> '' AND n.id > ?
                              ORDER BY n.id ASC LIMIT 500");
        $st->execute([$ultimo_id]);
        $filas = $st->fetchAll();
        if (!$filas) break;
        foreach ($filas as $f) {
            $ultimo_id = (int)$f['id'];
            $mod = (string)($f['modificado'] ?? '');
            $agregar(url_negocio((string)$f['slug']), $mod, '0.8', 'weekly');
            $recordar($mod);
            if (!empty($f['categoria_id'])) $fecha_rubro[(int)$f['categoria_id']]    = $mayor((string)($fecha_rubro[(int)$f['categoria_id']] ?? ''), $mod);
            if (!empty($f['distrito_id']))  $fecha_distrito[(int)$f['distrito_id']]  = $mayor((string)($fecha_distrito[(int)$f['distrito_id']] ?? ''), $mod);
        }
        if (count($filas) < 500) break;
    }
} catch (Throwable $e) { error_log('sitemap negocios: ' . $e->getMessage()); }

// 1.b) Los RUBROS MÚLTIPLES: una tienda que además vive en otro rubro también lo mantiene fresco.
try {
    $rs = db()->query("SELECT nr.categoria_id, COALESCE(n.actualizado_en, n.creado_en) AS modificado
                         FROM directorio_negocio_rubros nr
                         JOIN directorio_negocios n ON n.id = nr.negocio_id
                        WHERE n.estado = 'activo'");
    foreach ($rs->fetchAll() as $f) {
        $cid = (int)$f['categoria_id'];
        if ($cid <= 0) continue;
        $fecha_rubro[$cid] = $mayor((string)($fecha_rubro[$cid] ?? ''), (string)($f['modificado'] ?? ''));
    }
} catch (Throwable $e) { error_log('sitemap rubros multiples: ' . $e->getMessage()); }

// ============================================================================
// 2) RUBROS Y DISTRITOS (los que se pueden visitar)
// ============================================================================
try {
    foreach (obtener_categorias() as $c) {
        $agregar(url_categoria((string)$c['slug']), (string)($fecha_rubro[(int)$c['id']] ?? ''), '0.7', 'weekly');
    }
} catch (Throwable $e) { error_log('sitemap categorias: ' . $e->getMessage()); }

try {
    foreach (obtener_distritos_visibles() as $d) {
        $agregar(url('distrito/' . urlencode((string)$d['slug'])), (string)($fecha_distrito[(int)$d['id']] ?? ''), '0.6', 'weekly');
    }
} catch (Throwable $e) { error_log('sitemap distritos: ' . $e->getMessage()); }

// ============================================================================
// 3) PÁGINAS FIJAS ÚTILES (sin fecha: no son contenido que se actualice solo)
// ============================================================================
$agregar(url('buscar.php'), '', '0.6', 'weekly');
$agregar(url('reclamar'),   '', '0.6', 'monthly');
$agregar(url('crear-tienda'), '', '0.5', 'monthly');
// 🏷️ EL ÍNDICE DE RUBROS (2026-09-19, pedido del jefe): una sola página con TODOS los rubros del
// directorio. Es una puerta de entrada al sitio (lleva a las 60 páginas de rubro), así que va con la
// misma prioridad que un rubro.
$agregar(url('rubros'), '', '0.7', 'weekly');
// 🏪 NOSOTROS (2026-09-21): la guía de uso del sitio, las novedades y los 5 planes. Es la página
// que el menú ☰ ofrece en su sección «Nosotros» y la que se comparte cuando alguien pregunta
// «¿cuánto cuesta?»: para Google es una puerta de entrada más del dominio.
$agregar(url('nosotros'), '', '0.6', 'monthly');
// 🏪 LAS TRES LANDING DEL ÁREA NOSOTROS (3.ª orden del jefe, 2026-09-21): cada sección tiene su propia
// página (nada de anclas). Precios es la que más se consulta y la que más se comparte: prioridad alta.
$agregar(url('como-se-usa'), '', '0.6', 'monthly');
$agregar(url('novedades'),   '', '0.6', 'monthly');
$agregar(url('precios'),     '', '0.7', 'weekly');
// 🔒 POLÍTICAS DE PRIVACIDAD Y CONDICIONES DE USO (2026-09-21): el botón que el jefe pidió dentro de
// «Nosotros». Es una página fija que cambia poco (va en `yearly` y con prioridad baja, como los
// términos de cualquier sitio). De paso arregló los DOS enlaces del pie que daban 404.
$agregar(url('privacidad'), '', '0.3', 'yearly');
// ❓📘 Y LOS OTROS DOS DOCUMENTOS OFICIALES: las Preguntas Frecuentes y el Manual de Uso, que acompañan
// a las Políticas en la colección documental del sitio, **con una página por capítulo** (sin anclas).
$agregar(url('preguntas-frecuentes'), '', '0.4', 'yearly');
$agregar(url('manual-de-uso'), '', '0.4', 'yearly');
try {
    if (is_file(__DIR__ . '/includes/doc_legal.php')) {
        require_once __DIR__ . '/includes/doc_legal.php';
        if (function_exists('doc_legal') && function_exists('doc_legal_mapa')) {
            foreach (['privacidad', 'faq', 'manual'] as $doc_id) {
                $doc_d = doc_legal($doc_id);
                if (!$doc_d) continue;
                foreach (doc_legal_mapa($doc_d) as $cap_slug => $cap_i) {
                    $agregar(doc_legal_url($doc_id, $cap_slug), '', '0.3', 'yearly');
                }
            }
        }
    }
} catch (Throwable $e) { error_log('sitemap documentacion: ' . $e->getMessage()); }

// ============================================================================
// 4) 💼 LA PÁGINA DE EMPLEOS Y CADA AVISO VIGENTE
//    (contenido que cambia todos los días: prioridad alta y frecuencia diaria)
// ============================================================================
try {
    if (function_exists('empleos_instalar_tabla') && empleos_instalar_tabla()) {
        [$vig, $vp] = empleo_vigente_sql('e');
        $max_emp = '';
        $st = db()->prepare("SELECT e.slug,
                                    COALESCE(e.actualizado_en, e.publicado_en, e.creado_en) AS modificado
                               FROM directorio_empleos e
                              WHERE $vig
                              ORDER BY e.publicado_en DESC LIMIT 2000");
        $st->execute($vp);
        foreach ($st->fetchAll() as $f) {
            $mod = (string)($f['modificado'] ?? '');
            $agregar(empleo_url((string)$f['slug']), $mod, '0.8', 'daily');
            $max_emp = $mayor($max_emp, $mod);
            $recordar($mod);
        }
        $agregar(url('empleos'), $max_emp, '0.9', 'daily');
    }
} catch (Throwable $e) { error_log('sitemap empleos: ' . $e->getMessage()); }

// ============================================================================
// 5) 📰 LAS NOTICIAS (2026-09-16): el listado y cada noticia publicada.
//    Es lo MÁS fresco del sitio y hasta hoy no estaba aquí.
// ============================================================================
try {
    if (is_file(__DIR__ . '/includes/noticias.php')) require_once __DIR__ . '/includes/noticias.php';
    if (function_exists('noticias_instalar_tabla') && noticias_instalar_tabla()) {
        $max_noti = '';
        $st = db()->query("SELECT slug, creada_en FROM directorio_noticias
                            WHERE estado = 'publicado' AND slug <> ''
                            ORDER BY fecha DESC, hora DESC, id DESC LIMIT 3000");
        foreach ($st->fetchAll() as $f) {
            $mod = (string)($f['creada_en'] ?? '');
            $agregar(noticias_url((string)$f['slug']), $mod, '0.8', 'daily');
            $max_noti = $mayor($max_noti, $mod);
            $recordar($mod);
        }
        $agregar(noticias_url(), $max_noti, '0.9', 'daily');
    }
} catch (Throwable $e) { error_log('sitemap noticias: ' . $e->getMessage()); }

// ============================================================================
// 6) 🛒 LAS PÁGINAS DE LOS PRODUCTOS (`/producto/<id>`, 2026-09-15 — pedido del jefe:
//    «que el producto se comparta con su enlace y su foto»). Solo los que se pueden ver:
//    tienda ACTIVA, producto ACTIVO y VIGENTE (los de corta duración caducan solos).
//    La fecha es la más nueva entre la del producto y la de su tienda (si la tienda cambió,
//    la página del producto también cambió: ahí se ven su teléfono, sus fotos y su horario).
// ============================================================================
try {
    $ultimo_id = 0;
    while (true) {
        $st = db()->prepare("SELECT s.id,
                                    GREATEST(s.creado_en, COALESCE(n.actualizado_en, n.creado_en)) AS modificado
                               FROM directorio_servicios s
                               JOIN directorio_negocios n ON n.id = s.negocio_id AND n.estado = 'activo'
                              WHERE s.activo = 1 AND " . sql_producto_vigente('s') . " AND s.id > ?
                              ORDER BY s.id ASC LIMIT 500");
        $st->execute([$ultimo_id]);
        $filas = $st->fetchAll();
        if (!$filas) break;
        foreach ($filas as $f) {
            $ultimo_id = (int)$f['id'];
            $mod = (string)($f['modificado'] ?? '');
            $agregar(url_producto($ultimo_id), $mod, '0.6', 'weekly');
            $recordar($mod);
        }
        if (count($filas) < 500) break;
    }
} catch (Throwable $e) { error_log('sitemap productos: ' . $e->getMessage()); }

// ============================================================================
// 7) LA PORTADA, AL FINAL porque su fecha es la más nueva de todo el sitio.
// ============================================================================
$agregar(url(''), $ultimo_sitio !== '' ? $ultimo_sitio : date('Y-m-d'), '1.0', 'daily');

// ============================================================================
// SALIDA
// ============================================================================
echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";
foreach ($urls as $u) {
    echo "  <url>\n";
    echo '    <loc>' . htmlspecialchars((string)$u['loc'], ENT_XML1 | ENT_QUOTES, 'UTF-8') . "</loc>\n";
    if ($u['lm'] !== '') echo '    <lastmod>' . $u['lm'] . "</lastmod>\n";
    echo '    <changefreq>' . $u['freq'] . "</changefreq>\n";
    echo '    <priority>' . $u['prio'] . "</priority>\n";
    echo "  </url>\n";
}
echo '</urlset>' . "\n";
