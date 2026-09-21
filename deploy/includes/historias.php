<?php
/**
 * historias.php — ⭐ DESTACADOS: la tira de FLYERS de la portada (historias tipo Facebook)
 * =======================================================================================
 * Pedido del jefe (2026-09-15, textual): *«actualmente en el sitio web hay algunos folletos que están
 * muy bien hechos, algunos flyers… quiero que los crees en la parte de arriba de la portada así como
 * Facebook tiene sus historias, así similar… quiero que publiques algo que diga destacados y vas a
 * coger los productos que tengan foto vertical que yo te haya enviado y sean flyers… y lo pones en la
 * parte de arriba, así igualito.»*
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * QUÉ ES
 * ─────────────────────────────────────────────────────────────────────────────
 * Una **tira horizontal** arriba de todo en la portada: una **historia por TIENDA** (como Facebook,
 * que muestra un círculo por amigo), con el **flyer vertical** de esa tienda como portada y el nombre
 * debajo. Al tocar una historia se abre un **visor a pantalla completa** (igual que las historias):
 * barras de progreso arriba (una por flyer de esa tienda), avance solo cada `HISTORIAS_SEGUNDOS`,
 * toque a los lados para retroceder/avanzar, ✕ para cerrar y abajo el **título, el precio** y los
 * botones **«Ver la tienda»** y **WhatsApp** (con el mensaje con contexto de la casa, nunca en blanco).
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * DE DÓNDE SALEN LOS FLYERS (la decisión del jefe)
 * ─────────────────────────────────────────────────────────────────────────────
 * NO se adivina por el tamaño de la foto: se **marcan a mano** los productos cuyo flyer está bien
 * hecho. Viven en la tabla **`directorio_historias`** (`producto_id` único + `orden` + `activo`), así
 * que la lista se puede cambiar sin tocar una sola línea de código:
 *
 *     INSERT INTO directorio_historias (producto_id, orden) VALUES (9717, 1);   -- marca un flyer
 *     UPDATE directorio_historias SET activo = 0 WHERE producto_id = 9717;     -- lo saca de la tira
 *
 * ⚠️ **NO se marca por «tiene foto vertical»**: en el sitio hay 643 fotos verticales y la mayoría son
 * fotos normales (productos, locales, mascotas) y algunas hasta son capturas o dibujos que no pueden
 * salir nunca. La tira es de **flyers con letras, marca y precios**, revisados uno por uno.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * CÓMO SE USA (en `index.php`, ARRIBA, después del hero)
 * ─────────────────────────────────────────────────────────────────────────────
 *     <?php require_once __DIR__ . '/includes/historias.php'; ?>
 *     <?= historias_tira_html() ?>
 *
 * Las **historias salen AL AZAR en cada carga** (`shuffle()` de las tiendas; orden del jefe,
 * 2026-09-15) y la banda blanca que las envuelve va a **todo el ancho de la pantalla**.
 * Si la tabla no existe o no hay flyers marcados devuelve **''** → no se pinta nada (nunca un bloque
 * vacío). Ver la guía del módulo: **`GUIA_DISENO_DEL_INDEX.md`** (§6bis).
 */

if (!defined('HISTORIAS_TABLA'))       define('HISTORIAS_TABLA', 'directorio_historias');

/** 🎠 La tira de la FICHA repite sus productos hasta llegar a estas tarjetas, para que el paseo
 *  lento tenga siempre recorrido (si todo cabe en pantalla, `paseaTira()` no arranca). */
if (!defined('HZ_MIN_TARJETAS')) define('HZ_MIN_TARJETAS', 14);
if (!defined('HZ_MAX_VUELTAS'))  define('HZ_MAX_VUELTAS', 6);
if (!defined('HISTORIAS_SEGUNDOS'))    define('HISTORIAS_SEGUNDOS', 7);   // lo que dura cada flyer
// ⚖️ LA TIRA YA NO CARGA TODAS LAS TIENDAS (2026-09-16). Estaba en 0 = TODAS, y la tira es lo PRIMERO
//    que se ve debajo del hero: con 36 tiendas el navegador se bajaba **33 de las 36 fotos apenas
//    abrir** (medido en el navegador con un hueco de 360 px) = **512 KB en la primera pantalla**.
//    Una tira se pasea sola y en un celular se ven 3 o 4 historias a la vez: con 12 alcanza de sobra y
//    el visitante igual puede deslizarla. Las historias de una tienda concreta (su ficha) no usan este
//    tope: van con `historias_flyers($negocio_id)`, que es otra función.
if (!defined('HISTORIAS_TIENDAS_MAX')) define('HISTORIAS_TIENDAS_MAX', 12); // 12 historias en la tira

/** ¿Existe la tabla de la tira? (si no existe, la tira simplemente no se pinta). */
function historias_ok() {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        db()->query('SELECT 1 FROM ' . HISTORIAS_TABLA . ' LIMIT 1');
        $ok = true;
    } catch (Throwable $e) {
        $ok = false;
    }
    return $ok;
}

/**
 * Crea la tabla si no existe (instalación defensiva: los `migrar_*.php` están bloqueados por el
 * antivirus, así que cada módulo se instala solo la primera vez que se usa).
 * Devuelve true si quedó lista.
 */
function historias_instalar_tabla() {
    if (historias_ok()) return true;
    try {
        db()->exec("CREATE TABLE IF NOT EXISTS " . HISTORIAS_TABLA . " (
            id INT AUTO_INCREMENT PRIMARY KEY,
            producto_id INT NOT NULL,
            orden INT NOT NULL DEFAULT 0,
            activo TINYINT(1) NOT NULL DEFAULT 1,
            creado_en DATETIME NOT NULL,
            UNIQUE KEY uq_historia_producto (producto_id),
            KEY idx_historia_orden (activo, orden)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    } catch (Throwable $e) {
        return false;
    }
    // ⚠️ Se vuelve a comprobar con una consulta NUEVA: `historias_ok()` guarda su respuesta en una
    // variable estática, así que después de crear la tabla seguiría diciendo «no existe».
    try {
        db()->query('SELECT 1 FROM ' . HISTORIAS_TABLA . ' LIMIT 1');
    } catch (Throwable $e) {
        return false;
    }
    return true;
}

/**
 * Los flyers marcados, ya listos para pintar (con la tienda de cada uno).
 * @return array filas con producto_id, orden, titulo, precio, unidad, imagen, negocio, slug, whatsapp
 */
function historias_flyers($limite = 0, $negocio_id = 0) {
    if (!historias_ok()) return [];
    $filtro = ((int)$negocio_id > 0) ? (' AND n.id = ' . (int)$negocio_id) : '';
    $sql = "SELECT h.producto_id, h.orden,
                   s.titulo, s.precio, s.unidad, s.imagen,
                   n.id AS neg_id, n.nombre AS negocio, n.slug AS neg_slug, n.whatsapp
              FROM " . HISTORIAS_TABLA . " h
              JOIN directorio_servicios s ON s.id = h.producto_id
              JOIN directorio_negocios  n ON n.id = s.negocio_id
             WHERE h.activo = 1
               AND s.activo = 1
               AND s.imagen IS NOT NULL AND s.imagen <> ''
               AND n.estado = 'activo'" . $filtro . "
             ORDER BY h.orden ASC, s.id DESC";
    if ($limite > 0) $sql .= ' LIMIT ' . (int)$limite;
    try {
        return db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Los flyers AGRUPADOS POR TIENDA (una historia por tienda, como Facebook: un amigo = un círculo).
 * ⚠️ **El orden de las TIENDAS es AL AZAR en cada carga** (`shuffle()`, orden del jefe 2026-09-15).
 *    El `orden` guardado en la tabla sigue mandando **dentro** de cada tienda: el primer flyer de cada
 *    una es la portada de su historia.
 *
 * @return array lista de ['slug','nombre','neg_id','wa','url','flyers'=>[...]]
 */
function historias_tiendas($max = 0, $negocio_id = 0, array $excluir = []) {
    $filas = historias_flyers(0, (int)$negocio_id);
    if (!$filas) return [];
    $salta = array_flip(array_map('intval', $excluir));   // tiendas ya mostradas en otro ciclo
    $out = [];
    foreach ($filas as $f) {
        $slug = trim((string)$f['neg_slug']);
        if ($slug === '') continue;
        // 🧠 Nunca la misma tienda dos veces en la misma portada (memoria de la portada, en la sesión).
        if ($salta && isset($salta[(int)$f['neg_id']])) continue;
        $ruta = (string)$f['imagen'];
        if (!isset($out[$slug])) {
            $out[$slug] = [
                'slug'   => $slug,
                'neg_id' => (int)$f['neg_id'],
                'nombre' => (string)$f['negocio'],
                'wa'     => trim((string)($f['whatsapp'] ?? '')),
                'url'    => url_negocio($slug),
                'flyers' => [],
            ];
        }
        $out[$slug]['flyers'][] = [
            'id'     => (int)$f['producto_id'],
            'titulo' => (string)$f['titulo'],
            'precio' => formato_precio($f['precio']),
            'ruta'   => $ruta,
            // ⚠️ Cada uno con SU ancho: el visor mide 480 px (versión de 800), la tira 96-112 px
            // (versión de 300) y el avatar de la cabecera 34 px (versión de 160). Antes se usaba
            // la de 300 para todo y el avatar se bajaba 9 veces más de lo que necesitaba.
            'micro'  => img_url($ruta, IMG_ANCHO_MICRO),   // avatar de 34 px
            'mini'   => img_url($ruta, IMG_ANCHO_MINI),    // la tira de historias
            'grande' => img_url($ruta, IMG_ANCHO_MEDIO),   // el visor a pantalla completa
        ];
    }
    $lista = array_values($out);
    // 🎲 LAS HISTORIAS SALEN AL AZAR EN CADA CARGA (orden del jefe, 2026-09-15: *«las historias
    //    muéstralas al azar»*). Se sortean las TIENDAS, no los flyers de dentro: el orden guardado en
    //    `orden` sigue mandando DENTRO de cada tienda (el primero es la portada de su historia), así
    //    que la tira cambia entera de orden en cada carga pero cada historia se ve igual de bien.
    shuffle($lista);
    if ($max > 0 && count($lista) > $max) $lista = array_slice($lista, 0, $max);
    return $lista;
}

/**
 * Título corto para las historias de una tienda (máximo 4 palabras y sin la coletilla entre paréntesis).
 * ⚠️ Es propio de este archivo a propósito: `titulo_cinco_palabras()` vive en `includes/portada_ciclos.php`
 * (se mudó allí el 2026-09-15) y **la ficha de la tienda no carga ese módulo** → usarlo aquí reventaba la
 * ficha con «Call to undefined function» y la página se cortaba a la mitad. Este archivo se queda
 * autocontenido.
 */
if (!function_exists('historias_titulo_corto')) {
    function historias_titulo_corto($texto, $max = 4) {
        $texto = trim(preg_replace('/\s*\([^)]*\)\s*/u', ' ', (string)$texto));
        $palabras = preg_split('/\s+/u', $texto, -1, PREG_SPLIT_NO_EMPTY);
        if (!$palabras) return '';
        if (count($palabras) <= $max) return implode(' ', $palabras);
        return implode(' ', array_slice($palabras, 0, $max)) . '…';
    }
}

/** Marca productos como flyers de la tira (lo usa la sonda de sembrado). */
function historias_marcar(array $ids, $activo = 1) {
    if (!historias_instalar_tabla()) return 0;
    $n = 0;
    $orden = (int)db()->query('SELECT COALESCE(MAX(orden), 0) FROM ' . HISTORIAS_TABLA)->fetchColumn();
    $st = db()->prepare('INSERT INTO ' . HISTORIAS_TABLA . ' (producto_id, orden, activo, creado_en)
                         VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE orden = VALUES(orden), activo = VALUES(activo)');
    foreach ($ids as $id) {
        $id = (int)$id;
        if ($id <= 0) continue;
        $orden++;
        try {
            $st->execute([$id, $orden, $activo ? 1 : 0, date('Y-m-d H:i:s')]);
            $n++;
        } catch (Throwable $e) {
            // producto que ya no existe: se salta sin romper la siembra
        }
    }
    return $n;
}

/**
 * 🎬 LA TIRA DE HISTORIAS — **cada historia es un ENLACE a su tienda** (2026-09-16).
 *
 * Orden del jefe (textual): *«luego viene la sección de historias destacadas haciendo auto scroll…
 * cada una es un link para su tienda, solo eso, un link para su tienda»*.
 *
 * Antes cada historia abría un **VISOR** a pantalla completa (los flyers de la tienda, precios y botón
 * de WhatsApp). En la **portada** ya no: tocar una historia **lleva a la ficha de la tienda** y nada
 * más. De paso la página adelgaza, porque el visor traía un JSON con **todos** los flyers y sus enlaces
 * de WhatsApp (decenas de KB que ya no se mandan).
 *
 * ⚠️ El VISOR SIGUE VIVO en la ficha de la tienda (`historias_productos_html`): ahí sí tiene sentido
 * (son los productos de ESA tienda). Por eso `historias_visor_html()` y su parte del JS no se borraron.
 * ⚠️ La tira **se pasea sola SOLO EN CELULAR**: el paseo vive en `historias_js()` y ya no necesita el
 * visor. 🔴 **En PC (≥900 px) NO se pasea** (orden del jefe, 2026-09-19: *«en la versión escritorio…
 * elimínale su autoscroll, que se muestren simplemente estáticas pero sí que sean deslizables»*): la
 * guarda es `enPC()`, dentro de `escalon()`. Y en PC la banda ya no mide `100vw` sino lo que mide la
 * columna del sitio (el jefe: *«están desbordadas, están demasiado anchas»*).
 * @param int    $max    tiendas como máximo (0 = todas)
 * @param string $sufijo sufijo para los ids (`-2`, `-3`…) cuando la portada repite el bloque: los ids
 *                       de una página no se pueden repetir, y el JS inicializa cada tira con su
 *                       `data-suf`. Ver `includes/portada_ciclos.php`.
 */
function historias_tira_html($max = 0, $sufijo = '') {
    $max    = $max > 0 ? (int)$max : (int)HISTORIAS_TIENDAS_MAX;
    $sufijo = (string)$sufijo;
    // 🧠 Las tiendas que ya salieron en otro ciclo de esta portada NO vuelven a salir (memoria de la
    //    portada, en la sesión: ver `portada_memoria_*` en helpers.php). Se juntan las dos listas de
    //    tiendas (historias + fichas) para que una tienda no salga de las dos formas en la portada.
    $usados  = function_exists('portada_usados')
             ? array_merge(portada_usados('hist'), portada_usados('neg'))
             : [];
    $tiendas = historias_tiendas($max, 0, $usados);
    // 🛟 Si el pozo se agotó (solo hay 36 tiendas con flyers), mejor repetir que dejar la tira vacía.
    if (!$tiendas && $usados) $tiendas = historias_tiendas($max, 0, []);
    if (!$tiendas) return '';
    if (function_exists('portada_apunta')) portada_apunta('hist', array_column($tiendas, 'neg_id'));

    $h  = '<style>' . historias_estilos() . '</style>';
    $h .= '<section class="seccion hz-seccion" id="destacados' . e($sufijo) . '">';

    // 🏳️ LA BANDA BLANCA A TODO EL ANCHO DE LA PANTALLA (pedido del jefe, 2026-09-15: «el bloque
    //    blanco que va en la parte de atrás tenía la intención de que lo pongas ocupando todo el ancho
    //    de pantalla, todo 100 % del ancho de la pantalla»).
    //    ⚠️ SIN TÍTULO: el jefe mandó borrar la palabra «Destacados» el mismo día.
    $h .= '<div class="hz-blanco">';

    // ---- la tira (una historia por tienda) ----
    // `data-suf` = el sufijo de los ids de ESTA tira: la portada puede pintar varias (una por ciclo,
    // ver includes/portada_ciclos.php) y los ids no se pueden repetir.
    $h .= '<div class="hz-tira" id="hzTira' . e($sufijo) . '" data-suf="' . e($sufijo) . '" role="list">';
    foreach ($tiendas as $t) {
        // El nombre de la tienda, corto: la primera parte antes del guion largo (los nombres largos
        // traen la descripción: «Sra. Doris — Huevos, Frutas, Ropa y Arreglos»).
        $corto = trim(preg_split('/\s+[—–-]\s+/u', $t['nombre'])[0]);
        if ($corto === '') $corto = $t['nombre'];

        // 🔗 UN ENLACE A SU TIENDA Y NADA MÁS (lo que pidió el jefe). El `title` y el `aria-label`
        //    llevan el nombre completo, aunque en pantalla se vea el corto.
        $h .= '<a class="hz-item" role="listitem" href="' . e($t['url']) . '"'
            . ' title="' . e($t['nombre']) . '"'
            . ' aria-label="Ir a la tienda ' . e($t['nombre']) . '">';
        // 🖼️ La foto de la tira entra al MOTOR (`img_tag`): así lleva `srcset` y el celular baja la
        //    versión de 300 px. El hueco real es de 96 px (112 px desde 560 px de ancho).
        $h .= '<span class="hz-marco"><span class="hz-marco__foto">'
            . img_tag($t['flyers'][0]['ruta'], $t['flyers'][0]['titulo'], [
                'sizes' => '(min-width: 560px) 112px, 96px',
              ])
            . '</span>'
            . '<span class="hz-aro" aria-hidden="true"></span>'
            . '</span>';
        $h .= '<span class="hz-nombre">' . e($corto) . '</span>';
        $h .= '</a>';
    }
    $h .= '</div>';   // cierra la tira
    $h .= '</div>';   // cierra el rectángulo blanco (.hz-blanco)

    // 🎠 El paseo lento de la tira (ya no hay visor en la portada: ver la nota de arriba).
    $h .= historias_js();
    $h .= '</section>';
    return $h;
}

/**
 * 🎬 EL VISOR de una tira (marcado + datos + JS). Lo usan la portada (`historias_tira_html`) y la ficha
 * de la tienda (`historias_productos_html`): es el mismo visor con los ids sufijados, para que en una
 * misma página puedan convivir varios sin chocar.
 *
 * @param array  $datos  lista de tiendas: ['nom','url','av','f'=>[['img','tit','pre','url','wa'],…]]
 * @param string $sufijo sufijo de los ids (`-2`, `-t`…)
 */
function historias_visor_html(array $datos, $sufijo = '') {
    if (!$datos) return '';
    $h  = '<div class="hz-visor" id="hzVisor' . e($sufijo) . '" hidden>'
        . '<div class="hz-caja" role="dialog" aria-modal="true" aria-label="Flyers de la tienda">'
        . '<div class="hz-barras" id="hzBarras' . e($sufijo) . '" aria-hidden="true"></div>'
        . '<div class="hz-cabeza">'
        .   '<img class="hz-av" id="hzAv' . e($sufijo) . '" alt="" width="34" height="34">'
        // ⚠️ Aquí decía «Destacados»: el jefe mandó borrar esa palabra del sitio (2026-09-15).
        .   '<span class="hz-quien"><b id="hzNom' . e($sufijo) . '"></b></span>'
        .   '<button type="button" class="hz-x" id="hzX' . e($sufijo) . '" aria-label="Cerrar">✕</button>'
        . '</div>'
        . '<div class="hz-lienzo">'
        .   '<img class="hz-foto" id="hzFoto' . e($sufijo) . '" alt="" decoding="async">'
        . '</div>'
        . '<div class="hz-pie">'
        .   '<p class="hz-prod" id="hzTit' . e($sufijo) . '"></p>'
        .   '<p class="hz-precio" id="hzPre' . e($sufijo) . '"></p>'
        .   '<div class="hz-botones">'
        .     '<a class="hz-btn hz-btn--ver" id="hzVer' . e($sufijo) . '" href="#">Ver la tienda</a>'
        .     '<a class="hz-btn hz-btn--wa" id="hzWa' . e($sufijo) . '" href="#" target="_blank" rel="noopener" hidden>'
        .       wa_icono_svg() . '<span>WhatsApp</span></a>'
        .   '</div>'
        . '</div>'
        . '<button type="button" class="hz-zona hz-zona--izq" id="hzIzq' . e($sufijo) . '" aria-label="Anterior"></button>'
        . '<button type="button" class="hz-zona hz-zona--der" id="hzDer' . e($sufijo) . '" aria-label="Siguiente"></button>'
        . '</div></div>';

    $h .= '<script type="application/json" id="hzDatos' . e($sufijo) . '">'
        . json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
        . '</script>';
    $h .= historias_js();
    return $h;
}

/**
 * 🎬 LOS PRODUCTOS DE CUALQUIER TIENDA, como historias (para las tiendas SIN flyers marcados).
 *
 * ⚠️ Por qué existe (2026-09-16): el jefe aclaró que las historias de la ficha **no eran solo para la
 * tienda de la Sra. Cinthia**: *«me estaba refiriendo a que tenías que crearlas para todas las tiendas…
 * todas todas, siempre sus productos arriba con autoescrol»*. Antes, la ficha solo pintaba historias
 * para las **36 tiendas curadas** (las que tienen flyers en `directorio_historias`, que son los de la
 * portada). Ahora **cualquier** tienda muestra **sus productos** arriba.
 *
 * Reglas (las mismas del sitio): producto **activo y vigente**, con **foto que exista de verdad** en el
 * disco (`negocio_portada_ok()`: hay rutas del proveedor viejo cuyo archivo ya no está y la web las
 * muestra rotas) y **tope** `$max` para que la tira no se haga un rosario.
 *
 * @param int        $negocio_id la tienda
 * @param array|null $negocio    la fila de la tienda si la plantilla ya la tiene (ahorra una consulta)
 * @param array|null $productos  los productos ya cargados por la plantilla (ahorra OTRA consulta: la
 *                               ficha llama a `obtener_productos_negocio()` para su catálogo, sería
 *                               absurdo pedirlos dos veces en la misma página)
 * @param array|null $fotos      la galería ya cargada por la plantilla. Es el **respaldo**: un producto
 *                               sin foto propia usa una foto de la tienda (rotando), porque hay muchas
 *                               tiendas con fotos del local y sin fotos de producto, y el jefe quiere
 *                               sus productos arriba en todas
 * @param int        $max        tope de productos en la tira
 * @return array|null mismo formato que devuelve `historias_tiendas()`, o null si no hay nada que mostrar
 */
function historias_tienda_productos($negocio_id, $negocio = null, $productos = null, $fotos = null, $max = 20) {
    $negocio_id = (int)$negocio_id;
    if ($negocio_id <= 0 || !function_exists('obtener_productos_negocio')) return null;

    // La tienda (nombre, slug y WhatsApp): de la fila que ya tiene la plantilla o de la base.
    if (!is_array($negocio) || empty($negocio['slug'])) {
        try {
            $st = db()->prepare("SELECT id, nombre, slug, whatsapp FROM directorio_negocios
                                  WHERE id = ? AND estado = 'activo' LIMIT 1");
            $st->execute([$negocio_id]);
            $negocio = $st->fetch(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $negocio = null;
        }
    }
    $negocio = is_array($negocio) ? $negocio : [];
    $slug = trim((string)($negocio['slug'] ?? ''));
    if ($slug === '') return null;

    $filas = is_array($productos) ? $productos : obtener_productos_negocio($negocio_id);
    if (!$filas) return null;

    // 🖼️ El respaldo: las fotos de la TIENDA (la galería). La ficha ya las trae; si no, se piden.
    if (!is_array($fotos)) {
        $fotos = function_exists('obtener_fotos_negocio') ? obtener_fotos_negocio($negocio_id) : [];
    }
    $respaldo = [];
    foreach ((array)$fotos as $f) {
        $r = trim((string)($f['ruta'] ?? ''));
        if ($r !== '' && negocio_portada_ok($r)) $respaldo[] = $r;
    }

    $flyers = [];
    $k = 0;
    foreach ($filas as $p) {
        $ruta = trim((string)($p['imagen'] ?? ''));
        if ($ruta === '' || !negocio_portada_ok($ruta)) {
            // Sin foto propia: se usa una foto de la tienda, rotando (así no salen todas la misma).
            if (!$respaldo) continue;
            $ruta = $respaldo[$k % count($respaldo)];
            $k++;
        }
        $flyers[] = [
            'id'     => (int)($p['id'] ?? 0),
            'titulo' => (string)($p['titulo'] ?? ''),
            'precio' => formato_precio($p['precio'] ?? null),
            'ruta'   => $ruta,
            'micro'  => img_url($ruta, IMG_ANCHO_MICRO),
            'mini'   => img_url($ruta, IMG_ANCHO_MINI),
            'grande' => img_url($ruta, IMG_ANCHO_MEDIO),
        ];
        if ($max > 0 && count($flyers) >= (int)$max) break;
    }
    if (!$flyers) return null;

    return [
        'slug'   => $slug,
        'neg_id' => $negocio_id,
        'nombre' => (string)($negocio['nombre'] ?? ''),
        'wa'     => trim((string)($negocio['whatsapp'] ?? '')),
        'url'    => url_negocio($slug),
        'flyers' => $flyers,
    ];
}

/**
 * 🎬 LAS HISTORIAS DE UNA TIENDA (sus propios productos, arriba de su ficha) — **PARA TODAS**.
 * Pedido del jefe (2026-09-15, ficha de la Sra. Cinthia): *«coloca sus productos en la parte superior de
 * la tienda como historias destacadas»*, aclarado el 2026-09-16: *«me estaba refiriendo a que tenías que
 * crearlas para TODAS las tiendas… todas todas, siempre sus productos arriba con autoescrol»*.
 *
 * De dónde salen las historias:
 *   1.º si la tienda tiene **flyers marcados** en `directorio_historias` (las 36 curadas de la portada),
 *       se usan ESOS, en su orden;
 *   2.º si no, se usan **sus productos** con foto de verdad (`historias_tienda_productos()`).
 * Basta **1** para pintar la tira (antes exigía 2 y las tiendas de un solo producto se quedaban sin nada).
 * Una historia por **PRODUCTO** (no por tienda, como en la portada) y al tocar una se abre en ESE
 * producto, con el visor completo para pasear por los demás.
 *
 * 🎠 **LA TIRA REPITE LA LISTA HASTA LLENAR LA PANTALLA** (`$vueltas`): con 2 o 3 productos la tira
 * entraría entera en pantalla y el paseo lento **no tendría nada que pasear** (`paseaTira()` no arranca
 * si todo cabe). Repitiendo la lista —mínimo `HZ_MIN_TARJETAS`, tope `HZ_MAX_VUELTAS` vueltas— la tira
 * siempre desborda y **siempre se pasea**, en el celular y en la computadora. Las copias van
 * `aria-hidden` (para que un lector de pantalla no lea los productos dos veces) y llevan el mismo
 * `data-fi`, así que tocar una copia abre el producto que le toca.
 *
 * @param int        $negocio_id la tienda de la ficha
 * @param array|null $negocio    la fila de la tienda si la plantilla ya la tiene (ahorra una consulta)
 * @param array|null $productos  los productos ya cargados por la plantilla (ahorra la consulta del
 *                               catálogo, que la ficha ya hizo)
 * @param array|null $fotos      la galería ya cargada por la plantilla (respaldo de foto por producto)
 * @param string     $sufijo     sufijo de ids (por defecto `-t`, para no chocar con la portada)
 */
function historias_productos_html($negocio_id, $negocio = null, $productos = null, $fotos = null, $sufijo = '-t') {
    $negocio_id = (int)$negocio_id;
    if ($negocio_id <= 0) return '';
    $tiendas = historias_tiendas(1, $negocio_id);
    $t = $tiendas ? $tiendas[0] : historias_tienda_productos($negocio_id, $negocio, $productos, $fotos);
    if (!$t || empty($t['flyers'])) return '';
    $sufijo = (string)$sufijo;

    // Los datos del visor: UNA tienda con TODOS sus flyers (igual que en la portada).
    $flyers = [];
    foreach ($t['flyers'] as $fy) {
        $wa = '';
        if ($t['wa'] !== '') {
            $msg = wa_mensaje_con_enlace(
                '¡Hola! Vi «' . $fy['titulo'] . '» en su página de DeChimbote.com y quiero más información.',
                url_actual()
            );
            $wa = url_whatsapp($t['wa'], $msg);
        }
        $flyers[] = [
            'img' => $fy['grande'],
            'tit' => $fy['titulo'],
            'pre' => $fy['precio'],
            'url' => $t['url'],
            'wa'  => $wa,
        ];
    }
    $datos = [['nom' => $t['nombre'], 'url' => $t['url'], 'av' => $t['flyers'][0]['micro'], 'f' => $flyers]];

    // 🎠 Cuántas veces se repite la lista para que la tira SIEMPRE desborde y se pasee sola.
    $cuantos = count($t['flyers']);
    $vueltas = 1;
    if ($cuantos > 0 && $cuantos < HZ_MIN_TARJETAS) {
        $vueltas = (int)ceil(HZ_MIN_TARJETAS / $cuantos);
    }
    $vueltas = max(1, min($vueltas, HZ_MAX_VUELTAS));

    $h  = '<style>' . historias_estilos() . '</style>';
    $h .= '<section class="seccion hz-seccion hz-seccion--tienda" id="productos-historia' . e($sufijo) . '">';
    $h .= '<div class="hz-blanco">';
    $h .= '<div class="hz-tira" id="hzTira' . e($sufijo) . '" data-suf="' . e($sufijo) . '" role="list">';
    for ($v = 0; $v < $vueltas; $v++) {
        foreach ($t['flyers'] as $i => $fy) {
            $h .= '<button type="button" class="hz-item" role="listitem" data-hz="0" data-fi="' . (int)$i . '"'
                . ($v > 0 ? ' data-copia="1" aria-hidden="true" tabindex="-1"' : '')
                . ' aria-label="Ver ' . e($fy['titulo']) . '">';
            $h .= '<span class="hz-marco"><span class="hz-marco__foto">'
                . img_tag($fy['ruta'], $fy['titulo'], ['sizes' => '(min-width: 560px) 112px, 96px'])
                . '</span><span class="hz-aro" aria-hidden="true"></span></span>';
            // El nombre debajo es el del PRODUCTO (la tienda ya se llama así en toda la ficha)
            $h .= '<span class="hz-nombre">' . e(historias_titulo_corto($fy['titulo'])) . '</span>';
            $h .= '</button>';
        }
    }
    $h .= '</div>';        // cierra la tira
    $h .= '</div>';        // cierra la banda blanca
    $h .= historias_visor_html($datos, $sufijo);
    $h .= '</section>';
    return $h;
}

/** El JavaScript de las historias (una sola vez por página).
 *  ⚠️ Sirve para VARIAS tiras en la misma página: la portada repite el bloque (ver
 *  includes/portada_ciclos.php) y cada tira lleva su `data-suf` con el que se arman sus ids.
 *  Al cargar un ciclo nuevo por AJAX hay que llamar a `window.HZ_INIT(nodoNuevo)`. */
function historias_js() {
    static $hecho = false;
    if ($hecho) return '';
    $hecho = true;
    $seg = (int)HISTORIAS_SEGUNDOS * 1000;
    return <<<JS
<script>
/* 🎬 LAS HISTORIAS — el visor de flyers y el paseo lento de la tira. Sin dependencias.
   Una misma función inicializa TODAS las tiras de la página (cada una con su sufijo de ids). */
(function () {
  var SEG = $seg;

  function initTira(tira) {
    if (!tira || tira.__hzListo) return;
    tira.__hzListo = true;

    var suf  = tira.getAttribute('data-suf') || '';
    var caja = document.getElementById('hzDatos' + suf);
    var visor = document.getElementById('hzVisor' + suf);

    // 🎠 EL PASEO LENTO VA PRIMERO Y NO DEPENDE DEL VISOR: en la portada cada historia es un enlace
    //    a su tienda (no hay visor) y la tira se tiene que seguir paseando igual.
    paseaTira(tira, visor);

    // 🖱️ Y CON EL RATÓN SE ARRASTRA (2026-09-19): la tira se aprieta y se corre a mano. Va aquí, antes
    //    del `return` del visor, para que valga en la portada y en la ficha.
    arrastraConRaton(tira);

    // 🎬 EL VISOR: solo si la tira lo trae (la ficha de la tienda sí lo trae).
    if (!caja || !visor) return;
    var datos;
    try { datos = JSON.parse(caja.textContent); } catch (e) { return; }
    if (!datos || !datos.length) return;

    var elFoto = document.getElementById('hzFoto' + suf),
        elAv   = document.getElementById('hzAv' + suf),
        elNom  = document.getElementById('hzNom' + suf),
        elTit  = document.getElementById('hzTit' + suf),
        elPre  = document.getElementById('hzPre' + suf),
        elVer  = document.getElementById('hzVer' + suf),
        elWa   = document.getElementById('hzWa' + suf),
        elX    = document.getElementById('hzX' + suf),
        barras = document.getElementById('hzBarras' + suf);
    var ti = 0, fi = 0, timer = null;

    function parar() { if (timer) { clearTimeout(timer); timer = null; } }

    function pinta() {
      var t = datos[ti];
      if (!t) { cierra(); return; }
      var f = t.f[fi];
      if (!f) { cierra(); return; }

      elFoto.src = f.img;
      elFoto.alt = f.tit;
      elAv.src   = t.av;
      elNom.textContent = t.nom;
      elTit.textContent = f.tit;
      elPre.textContent = f.pre;
      elPre.hidden = !f.pre;
      elVer.href = f.url;
      if (f.wa) { elWa.href = f.wa; elWa.hidden = false; } else { elWa.hidden = true; }

      // Barras de progreso: una por flyer de esta tienda (como las historias)
      barras.innerHTML = '';
      for (var i = 0; i < t.f.length; i++) {
        var b = document.createElement('span');
        b.className = 'hz-barra';
        var r = document.createElement('i');
        if (i < fi) r.style.width = '100%';
        b.appendChild(r);
        barras.appendChild(b);
        if (i === fi) {
          var relleno = r;
          requestAnimationFrame(function () {
            relleno.style.transition = 'width ' + (SEG / 1000) + 's linear';
            relleno.style.width = '100%';
          });
        }
      }

      // Precarga del siguiente (para que el pase no se corte)
      var sig = t.f[fi + 1] || (datos[ti + 1] ? datos[ti + 1].f[0] : null);
      if (sig) { var im = new Image(); im.src = sig.img; }

      parar();
      timer = setTimeout(siguiente, SEG);
    }

    function siguiente() {
      var t = datos[ti];
      if (!t) { cierra(); return; }
      if (fi + 1 < t.f.length) { fi++; pinta(); return; }
      if (ti + 1 < datos.length) { ti++; fi = 0; pinta(); return; }
      cierra();
    }

    function anterior() {
      var t = datos[ti];
      if (!t) { cierra(); return; }
      if (fi > 0) { fi--; pinta(); return; }
      if (ti > 0) { ti--; fi = Math.max(0, datos[ti].f.length - 1); pinta(); return; }
      pinta();
    }

    function abre(i, f) {
      ti = i; fi = f || 0;
      visor.hidden = false;
      document.body.classList.add('hz-abierto');
      pinta();
    }

    function cierra() {
      parar();
      visor.hidden = true;
      document.body.classList.remove('hz-abierto');
      elFoto.removeAttribute('src');
      if (location.hash === '#destacados' + suf) {
        try { history.replaceState(null, '', location.pathname + location.search); } catch (e) {}
      }
    }

    // La tira: cada historia abre su visor
    tira.addEventListener('click', function (ev) {
      var b = ev.target.closest ? ev.target.closest('.hz-item') : null;
      if (!b) return;
      ev.preventDefault();
      // `data-fi` = el flyer exacto que se tocó (lo usa la ficha de la tienda, que pinta una historia
      // por PRODUCTO); si no viene, se abre por el primero de esa tienda (como en la portada).
      var i = parseInt(b.getAttribute('data-hz'), 10) || 0;
      var f = parseInt(b.getAttribute('data-fi'), 10);
      abre(i, isNaN(f) ? 0 : f);
    });

    var elDer = document.getElementById('hzDer' + suf), elIzq = document.getElementById('hzIzq' + suf);
    if (elDer) elDer.addEventListener('click', function (e) { e.stopPropagation(); siguiente(); });
    if (elIzq) elIzq.addEventListener('click', function (e) { e.stopPropagation(); anterior(); });
    if (elX)   elX.addEventListener('click', function (e) { e.stopPropagation(); cierra(); });
    visor.addEventListener('click', function (ev) { if (ev.target === visor) cierra(); });

    document.addEventListener('keydown', function (ev) {
      if (visor.hidden) return;
      if (ev.key === 'Escape')     { cierra(); }
      if (ev.key === 'ArrowRight') { siguiente(); }
      if (ev.key === 'ArrowLeft')  { anterior(); }
    });

    // Al cambiar de pestaña se pausa (no se pierde el flyer que estaba mirando)
    document.addEventListener('visibilitychange', function () {
      if (visor.hidden) return;
      if (document.hidden) parar(); else pinta();
    });
  }

  /* ========================================================================
     🎠 LA TIRA SE DESLIZA SOLA, DESPACIO, UN ESCALÓN POR VEZ
     Pedido del jefe (2026-09-15): *«las historias destacadas muéstralas en un scroll, un escalón
     lento»* → cada PASO avanza UNA historia (el ancho de una tarjeta + su hueco) con un
     deslizamiento suave de ~0,7 s y una pausa larga entre escalón y escalón.
     ⚠️ Vive FUERA de `initTira` a propósito (2026-09-16): en la portada **ya no hay visor** (cada
     historia es un enlace a su tienda) y el paseo tiene que funcionar igual. `visor` puede venir
     `null`: por eso todo se pregunta con `enVisor()`.
     ⚠️ El movimiento lo hace esta animación propia (`requestAnimationFrame` + `scrollLeft`), **no**
     `scrollBy({behavior:'smooth'})`: en la prueba de control el desplazamiento suave del navegador
     no avanzaba (la tira se quedaba en `scrollLeft=2` de 3 654 px).
     · Al llegar al final espera y vuelve al principio.
     · SE PAUSA solo si el visitante INTERACTÚA (dedo, arrastre, rueda o teclado), si la pestaña no
       se ve o si el visor está abierto. ⚠️ NO se pausa por tener el ratón encima (en PC el puntero
       se queda quieto sobre la tira y con esa regla no se movía nunca).
     · Si el aparato pide menos movimiento (`prefers-reduced-motion`), NO se mueve sola.
     ======================================================================== */
  /* ========================================================================
     🖱️ LA TIRA SE ARRASTRA CON EL RATÓN (orden del jefe, 2026-09-19, textual): *«en el modo PC, en el modo
     escritorio, las cabeceras de las destacadas no se pueden deslizar con el mouse… repito: no actives
     auto scroll, pero no se puede deslizar con el mouse»*.
     El problema era real y lo dejó el cambio de esa misma mañana: al quitarle el paseo automático en PC,
     la tira quedó QUIETA y su barra deslizadora está escondida a propósito (orden del 2026-09-16), así que
     con un ratón —que no tiene dedo ni trackpad— no había NINGUNA forma de correrla: quedaban 322 px de
     historias que nadie podía ver (medido el 2026-09-19 en la portada: `clientWidth` 1136 contra
     `scrollWidth` 1458).
     Ahora se aprieta el botón izquierdo y se arrastra: la tira acompaña al puntero y queda donde se soltó.
     ⛔ NO se toca el paseo automático (sigue APAGADO en PC, como el jefe mandó) ni el celular: ahí el dedo
     desliza de forma nativa, con su imán y su paseo.
     ======================================================================== */
  function arrastraConRaton(tira) {
    if (tira.__hzRaton) return;
    tira.__hzRaton = true;

    var UMBRAL = 4;                    // menos que esto es un clic normal (abrir la tienda)
    var activo = false, movido = false, x0 = 0, izq0 = 0;

    tira.addEventListener('pointerdown', function (ev) {
      if (ev.pointerType !== 'mouse' || ev.button !== 0) return;   // el dedo NO pasa por aquí
      activo = true; movido = false;
      x0 = ev.clientX; izq0 = tira.scrollLeft;
    });

    tira.addEventListener('pointermove', function (ev) {
      if (!activo) return;
      var d = ev.clientX - x0;
      if (!movido) {
        if (Math.abs(d) < UMBRAL) return;                 // todavía es un clic, no un arrastre
        movido = true;
        tira.classList.add('hz-tira--arrastrando');       // mano cerrada, sin imán y sin seleccionar texto
        try { tira.setPointerCapture(ev.pointerId); } catch (e) {}
      }
      tira.scrollLeft = izq0 - d;
      ev.preventDefault();
    });

    function suelta(ev) {
      if (!activo) return;
      activo = false;
      if (!movido) return;
      movido = false;
      tira.classList.remove('hz-tira--arrastrando');
      try { tira.releasePointerCapture(ev.pointerId); } catch (e) {}
      /* 🚫 EL ARRASTRE NO DEBE ABRIR LA TIENDA: el navegador manda un `clic` al soltar y ese clic
         abriría la historia (o el visor). Se traga ESE clic y solo ese: el que venga después pasa
         normal (por eso se desarma a los 350 ms). */
      var traga = function (e) { e.preventDefault(); e.stopPropagation(); };
      tira.addEventListener('click', traga, true);
      setTimeout(function () { tira.removeEventListener('click', traga, true); }, 350);
    }
    tira.addEventListener('pointerup', suelta);
    tira.addEventListener('pointercancel', suelta);
    window.addEventListener('pointerup', suelta);    // red de seguridad si el puntero suelta fuera

    // 🚫 Y tampoco se puede «arrastrar la foto»: el arrastre es para correr la tira, no para llevarse
    //    la imagen al escritorio (el fantasma del enlace cortaría el movimiento).
    tira.addEventListener('dragstart', function (ev) { ev.preventDefault(); });
  }

  function paseaTira(tira, visor) {
      if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
      if (tira.scrollWidth - tira.clientWidth < 40) return;   // todo cabe: nada que pasear

      // 💻 EN PC NO SE PASEA (orden del jefe, 2026-09-19): *«en la versión escritorio… elimínale su
      //    autoscroll, que no tenga autoscroll, que se muestren simplemente estáticas pero sí que sean
      //    deslizables»*. La tira queda QUIETA y se desliza a mano (dedo, rueda o trackpad).
      // ⚠️ Se pregunta DENTRO de `escalon()` y no aquí arriba: así, si alguien abre la página en el
      //    celular y luego la agranda hasta el tamaño de PC, el paseo se detiene en el acto.
      // ⚠️ El corte es 900 px: el MISMO «PC» del CSS (`.hz-blanco{width:auto}`) y el de los empleos.
      function enPC() { return !!(window.matchMedia && window.matchMedia('(min-width:900px)').matches); }

      var PASO_MS   = 3200;   // cada cuánto da un escalón (lento)
      var DESLIZ_MS = 700;    // lo que dura el deslizamiento
      var REPOSO_MS = 4000;   // lo que espera después de que el visitante suelta
      var tocando = false, animando = false;

      // `visor` puede NO existir (portada: la tira es de enlaces) → `enVisor` siempre es booleano.
      function enVisor() { return !!(visor && !visor.hidden); }

      function escalon_px() {
        var it = tira.querySelector('.hz-item');
        var w = it ? it.getBoundingClientRect().width : 106;
        return (w > 0 ? w : 106) + 10;
      }

      function desliza(destino, ms, alTerminar) {
        var ini = tira.scrollLeft, t0 = null, terminado = false;
        animando = true;
        tira.dataset.hzMov = (parseInt(tira.dataset.hzMov || '0', 10) + 1);   // ⚙️ contador para las pruebas

        function cierra() {
          if (terminado) return;
          terminado = true;
          animando = false;
          // 🛟 RED DE SEGURIDAD: si la animación no llegó (pestaña en segundo plano, aparato lento o
          //    fotogramas que no avanzan), se pone el destino de una vez y NO se queda trabada.
          if (Math.abs(tira.scrollLeft - destino) > 2) tira.scrollLeft = destino;
          if (alTerminar) alTerminar();
        }

        function cuadro(ts) {
          if (terminado) return;
          if (t0 === null) t0 = ts;
          var p = (ms > 0) ? (ts - t0) / ms : 1;
          if (!isFinite(p)) p = 1;
          p = Math.min(1, Math.max(0, p));
          var e = p < 0.5 ? 2 * p * p : 1 - Math.pow(-2 * p + 2, 2) / 2;   // easeInOutQuad
          tira.scrollLeft = ini + (destino - ini) * e;
          if (p < 1) { requestAnimationFrame(cuadro); } else { cierra(); }
        }

        requestAnimationFrame(cuadro);
        setTimeout(cierra, ms + 300);   // el vigilante
      }

      function escalon() {
        if (enPC()) return;   // 💻 en PC la tira no se mueve sola (2026-09-19): se desliza a mano
        tira.dataset.hzPasos = (parseInt(tira.dataset.hzPasos || '0', 10) + 1);   // ⚙️ contador (pruebas)
        tira.dataset.hzEstado = (tocando ? 'tocando' : '') + (animando ? ' animando' : '')
                              + (document.hidden ? ' oculta' : '') + (enVisor() ? ' visor' : '');
        if (tocando || animando || document.hidden || enVisor()) return;
        var fin = tira.scrollWidth - tira.clientWidth - 6;
        if (tira.scrollLeft >= fin) { desliza(0, 1200); return; }   // llegó al final: vuelve al principio
        desliza(Math.min(tira.scrollLeft + escalon_px(), fin), DESLIZ_MS);
      }

      setTimeout(function () { setInterval(escalon, PASO_MS); }, 2500);   // arranca a los 2,5 s

      // ⚠️ Solo la interacción de verdad: nada de «ratón encima» (ver la nota de arriba).
      ['touchstart', 'pointerdown', 'mousedown', 'wheel', 'focusin'].forEach(function (ev) {
        tira.addEventListener(ev, function () { tocando = true; }, { passive: true });
      });
      ['touchend', 'touchcancel', 'pointerup', 'mouseup', 'focusout'].forEach(function (ev) {
        tira.addEventListener(ev, function () {
          setTimeout(function () { tocando = false; }, REPOSO_MS);
        }, { passive: true });
      });
  }

  /** Inicializa las tiras que todavía no lo estén (dentro de `raiz` o en toda la página). */
  window.HZ_INIT = function (raiz) {
    var lista = (raiz || document).querySelectorAll('.hz-tira');
    for (var i = 0; i < lista.length; i++) initTira(lista[i]);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { window.HZ_INIT(); });
  } else {
    window.HZ_INIT();
  }
})();
</script>
JS;
}

/** El CSS del bloque (se inyecta una sola vez, dentro de un <style>). */
function historias_estilos() {
    return '
/* ============ LA TIRA DE FLYERS (historias tipo Facebook) ============ */
.hz-seccion{margin:0 0 10px}
/* 🏳️ BANDA BLANCA A TODO EL ANCHO (pedido del jefe, 2026-09-15). El truco del ancho completo:
   el bloque mide 100vw y se corre a la izquierda la mitad de lo que sobra del contenedor
   (`50% - 50vw`), así llega a los DOS bordes de la pantalla aunque viva dentro de `.main`
   (que tiene max-width 1200 px y 16 px de relleno).
   ⚠️ En PC la barra de scroll ocupa ~15 px y `100vw` la cuenta: por eso la portada lleva
   `body{overflow-x:clip}` (ver index.php) y así no aparece una barra horizontal. */
.hz-blanco{width:100vw;margin-left:calc(50% - 50vw);background:var(--color-fondo-tarjeta,#fff);
  border-top:1px solid var(--color-borde,#e6dbc8);border-bottom:1px solid var(--color-borde,#e6dbc8);
  padding:10px 12px 6px}
.hz-tira{display:flex;gap:10px;overflow-x:auto;scroll-snap-type:x proximity;
  -webkit-overflow-scrolling:touch;scrollbar-width:none;padding:4px 2px 8px;cursor:grab}
.hz-tira::-webkit-scrollbar{display:none}
/* 🖱️ MIENTRAS SE ARRASTRA CON EL RATÓN (2026-09-19): mano cerrada, SIN imán (el imán pelearía con el
   arrastre y la tira daría tirones) y sin seleccionar texto. Ver `arrastraConRaton()` en el JS. */
.hz-tira--arrastrando{cursor:grabbing;scroll-snap-type:none;user-select:none;-webkit-user-select:none}
.hz-tira--arrastrando .hz-item{cursor:grabbing}
.hz-tira .hz-item,.hz-tira img{-webkit-user-drag:none}
/* ⚠️ `.hz-item` es un ENLACE desde el 2026-09-16 (antes era un `<button>` que abría el visor): por eso
   lleva `text-decoration:none` y `color:inherit` — si no, el nombre de la tienda saldría azul y
   subrayado, como un enlace de texto. */
.hz-item{flex:0 0 96px;display:flex;flex-direction:column;align-items:center;gap:6px;
  background:none;border:0;padding:0;margin:0;cursor:pointer;font-family:inherit;
  text-decoration:none;color:inherit;
  scroll-snap-align:start;-webkit-tap-highlight-color:transparent}
.hz-marco{position:relative;display:block;width:96px;height:158px;padding:3px;border-radius:15px;
  background:linear-gradient(135deg,#6d071a 0%,#d0312a 45%,#f0861c 100%);
  box-shadow:0 4px 12px rgba(109,7,26,.18),0 0 0 2px #fff inset}
.hz-marco__foto{display:block;width:100%;height:100%;border-radius:12px;overflow:hidden;background:#fff}
.hz-marco__foto img{width:100%;height:100%;object-fit:cover;display:block}
.hz-aro{position:absolute;inset:0;border-radius:15px;pointer-events:none;
  box-shadow:inset 0 0 0 1px rgba(255,255,255,.35)}
.hz-nombre{font-size:12px;font-weight:700;line-height:1.2;color:var(--color-texto,#1f2937);
  text-align:center;max-width:96px;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
@media (min-width:560px){
  .hz-item{flex-basis:112px}
  .hz-marco{width:112px;height:184px}
  .hz-nombre{max-width:112px;font-size:12.5px}
}
/* 🚫 LA BARRA FINA DE ESCRITORIO SE QUITÓ (orden del jefe, 2026-09-16, textual: *«abajo de cada scroll
   has puesto como una especie de barra deslizadora que no es necesario que vaya»*). Antes, en PC, la
   tira mostraba una barra de 8 px «para que se supiera que hay más historias»; ahora no se dibuja en
   ningún navegador, como el resto de los carruseles de la portada. La tira se sigue deslizando con el
   dedo, la rueda y el trackpad, y además **se pasea sola** (ver `paseaTira`), así que la barra no
   hacía ninguna falta.
   ⚠️ **Y CON EL RATÓN SE ARRASTRA** desde el 2026-09-19 (`arrastraConRaton()` + `cursor:grab`): al
   quedar la tira quieta en PC (se le quitó el paseo esa misma mañana) y sin barra, el ratón se había
   quedado sin NINGUNA forma de correrla. */
@media (min-width:760px){
  .hz-blanco{padding:12px 16px 10px}
}
/* ============================================================================
   💻 EN PC: LA BANDA RESPETA LA MEDIDA DEL SITIO Y LA TIRA **NO SE PASEA**
   (orden del jefe, 2026-09-19, textual: *«en la versión escritorio… las historias destacadas elimínale
   su autoscroll, que no tenga autoscroll, que se muestren simplemente estáticas pero sí que sean
   deslizables… y fíjate en el ancho, están desbordadas, están demasiado anchas… corrige cualquier
   sección desbordada que se salga de la medida del sitio… solo modo PC»*).
   Desde 900 px (que es el «PC» de la casa: el mismo corte de los empleos y de la tira de la ficha):
     · la banda deja de medir `100vw` y toma **la medida de la columna del sitio** (`.main`, 1200 px),
       que es lo que el jefe veía «desbordado»: medido el 2026-09-19, la banda iba de −7 a 1257 px
       (1 264 px de ancho) mientras la columna va de 41 a 1209 (1 168 px útiles);
     · **el paseo automático NO arranca** (ver la guarda `enPC()` dentro de `paseaTira()`): la tira
       queda **quieta** y se desliza a mano con el dedo, la rueda o el trackpad.
   📱 En el celular NADA cambia: la banda sigue de borde a borde y la tira se sigue paseando sola. */
@media (min-width:900px){
  .hz-blanco{width:auto;margin-left:0}
}
/* ============================================================================
   💻 LA TIRA DE LA FICHA, SOLO EN PC (orden del jefe, 2026-09-18, textual):
   *«en el modo PC mostrar 11 veces la ficha de historia lo hace ver mal… lo mejor sería solo 8 y no
   ocupar todo el ancho de pantalla: usa el mismo espacio que las demás fichas, hablo del modo PC.»*
   Qué cambia (y SOLO dentro de la ficha: `hz-seccion--tienda`; la portada y los demás sitios siguen
   igual, con su banda de borde a borde):
     · La banda blanca deja de ser `100vw` y toma **el ancho de la ficha** (como sus otras tarjetas),
       con su borde y sus esquinas redondeadas.
     · **No se repiten las tarjetas** (fuera las copias `data-copia="1"`, que existen para que la tira
       desborde y se pasee sola en el celular).
     · **Como máximo 8 tarjetas**: de la 9.ª en adelante no se pintan.
   En el celular NADA cambia: la tira sigue igual, con sus repeticiones y su paseo automático.
   ============================================================================ */
@media (min-width:900px){
  .hz-seccion--tienda .hz-blanco{width:auto;margin-left:0;border:1px solid var(--color-borde,#e6dbc8);
    border-radius:14px;padding:12px 14px 8px}
  .hz-seccion--tienda .hz-tira > .hz-item[data-copia="1"]{display:none}
  .hz-seccion--tienda .hz-tira > .hz-item:nth-child(n+9){display:none}
}

/* ---------- EL VISOR (pantalla completa, como las historias) ---------- */
body.hz-abierto{overflow:hidden}
/* ⬛ El fondo es OPACO (como Facebook): antes era rgba(.96) y se entreveía la portada detrás,
   que distraía de la historia. */
.hz-visor{position:fixed;inset:0;z-index:4000;background:#0a0306;
  display:flex;align-items:center;justify-content:center;-webkit-tap-highlight-color:transparent}
.hz-visor[hidden]{display:none}
.hz-caja{position:relative;width:100%;max-width:480px;height:100%;max-height:100vh;
  display:flex;flex-direction:column;padding:calc(8px + env(safe-area-inset-top)) 0
  calc(8px + env(safe-area-inset-bottom))}
.hz-barras{display:flex;gap:3px;padding:0 12px 8px}
.hz-barra{flex:1;height:3px;border-radius:999px;background:rgba(255,255,255,.32);overflow:hidden}
.hz-barra > i{display:block;height:100%;width:0;background:#fff;border-radius:999px}
.hz-cabeza{display:flex;align-items:center;gap:9px;padding:0 12px 8px}
.hz-av{width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid #fff;background:#3a3a3a}
.hz-quien{display:flex;flex-direction:column;min-width:0;flex:1}
.hz-quien b{color:#fff;font-size:14px;font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.hz-quien i{color:rgba(255,255,255,.72);font-size:11.5px;font-style:normal}
.hz-x{background:rgba(255,255,255,.14);border:0;color:#fff;font-size:16px;font-weight:800;
  width:34px;height:34px;border-radius:50%;cursor:pointer;line-height:1;font-family:inherit}
.hz-lienzo{flex:1;min-height:0;display:flex;align-items:center;justify-content:center;padding:0 8px}
.hz-foto{max-width:100%;max-height:100%;width:auto;height:auto;object-fit:contain;
  border-radius:10px;background:#140a0d}
.hz-pie{padding:12px 14px 4px;display:flex;flex-direction:column;gap:8px}
.hz-prod{margin:0;color:#fff;font-size:16px;font-weight:800;line-height:1.25}
.hz-precio{margin:0;color:#ffd9a8;font-size:16px;font-weight:800}
.hz-botones{display:flex;gap:8px;flex-wrap:wrap}
.hz-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;flex:1 1 auto;
  min-height:46px;padding:11px 18px;border-radius:12px;font-size:15.5px;font-weight:800;
  text-decoration:none;text-align:center}
.hz-btn--ver{background:#fff;color:#6d071a}
.hz-btn--wa{background:#25d366;color:#fff}
.hz-btn--wa svg{width:19px;height:19px;flex:0 0 auto;color:#fff}
.hz-zona{position:absolute;top:60px;bottom:120px;width:34%;background:none;border:0;padding:0;cursor:pointer}
.hz-zona--izq{left:0}
.hz-zona--der{right:0}
@media (max-width:420px){ .hz-prod{font-size:15px} .hz-btn{font-size:14.5px} }
';
}
