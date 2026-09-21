<?php
/**
 * grilla_azar.php — 🎲 EL RECTÁNGULO DE PRODUCTOS AL AZAR (va DEBAJO de «⭐ Destacados»)
 * =====================================================================================
 * Pedido del jefe (2026-09-15, textual): *«…dibuja un rectángulo y dentro de ese rectángulo crea una
 * grilla con tres columnas por tres filas en formato móvil mostrando imágenes de productos, y en
 * formato PC 6 columnas por tres filas también mostrando productos. Recuerda que todo esto siempre se
 * muestra al azar para que no haya suspicacias. Único requisito: tener una foto tanto en la portada de
 * las tiendas como también en los productos. Los productos no es necesario que lleven nombre. Entre
 * tienda y la grilla trata de poner alguna línea separadora o un banner — si un banner estaría bien:
 * en formato móvil un banner y en formato PC una fila de tres banners—.»*
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * QUÉ HACE
 * ─────────────────────────────────────────────────────────────────────────────
 * · **El separador** (`grilla_azar_separador_html()`): una **fila de 3 banners**, que en el celular se
 *   ve **solo el primero** (en PC, los tres en fila). Si no hay banners, no pinta nada.
 * · **El rectángulo** (`grilla_azar_html()`): un **marco blanco con borde** y, dentro, la **rejilla de
 *   productos**:
 *     · **celular: 3 columnas × 3 filas = 9 productos**
 *     · **PC (≥760 px): 6 columnas × 3 filas = 18 productos**
 *   Se pintan los **18 en el HTML** y el CSS esconde del 10.º en adelante en el celular (así Google
 *   los ve todos, sin JavaScript). **Solo la foto** (sin nombre); el toque lleva a la ficha de la
 *   tienda, como hacen todas las tarjetas de producto del sitio.
 * · **SIEMPRE AL AZAR** (regla del jefe: *«para que no haya suspicacias»*): la consulta lleva
 *   `ORDER BY RAND()` y **corre en cada carga** de la portada — no hay caché ni lista fija, así que
 *   todos los negocios tienen las mismas posibilidades y no se puede decir que alguien esté siempre.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * EL ÚNICO REQUISITO (lo pidió así el jefe)
 * ─────────────────────────────────────────────────────────────────────────────
 * **Foto en los dos lados**: el producto tiene que tener **su foto** (`directorio_servicios.imagen`) y
 * su tienda tiene que tener **portada** (la 1.ª foto de `directorio_fotos`). Además se comprueba que
 * **los archivos existan de verdad en el disco** (`is_file`) — en el sitio hay fotos del proveedor
 * viejo que ya no están (dan 404) y aquí no puede salir un hueco roto. Por eso se piden **más
 * candidatos al azar** de los necesarios y se van tomando los que pasan la prueba.
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * CÓMO SE USA (en `index.php`, justo después de `historias_tira_html()`)
 * ─────────────────────────────────────────────────────────────────────────────
 *     <?php require_once __DIR__ . '/includes/grilla_azar.php'; ?>
 *     <?= grilla_azar_separador_html() ?>
 *     <?= grilla_azar_html() ?>
 *
 * Si no hay productos que cumplan el requisito, devuelve **''** → no se pinta nada.
 */

if (!defined('GRILLA_AZAR_CELDAS'))     define('GRILLA_AZAR_CELDAS', 36);       // PC: 3 filas de 12 (celular: 9)
if (!defined('GRILLA_AZAR_CANDIDATOS')) define('GRILLA_AZAR_CANDIDATOS', 220);  // al azar, de más, y se filtran
if (!defined('GRILLA_AZAR_COLS_PC'))    define('GRILLA_AZAR_COLS_PC', 12);      // columnas por fila en escritorio
if (!defined('GRILLA_AZAR_FILAS_PC'))   define('GRILLA_AZAR_FILAS_PC', 3);      // filas en escritorio
if (!defined('GRILLA_AZAR_PX_PC'))      define('GRILLA_AZAR_PX_PC', 184);       // ancho de cada celda en PC
if (!defined('GRILLA_AZAR_MOVIL_VISIBLES')) define('GRILLA_AZAR_MOVIL_VISIBLES', 9); // 3 × 3 en celular

/**
 * Los productos al azar que cumplen el requisito (foto propia + portada de su tienda, ambas existentes).
 * ⚠️ **Cada llamada sortea de nuevo** (`ORDER BY RAND()`): es lo que pidió el jefe. No se cachea.
 *
 * @return array filas con id, titulo, imagen, neg, neg_slug, portada
 */
function grilla_azar_productos($cuantas = 0, $candidatos = 0, array $excluir = []) {
    $cuantas    = $cuantas > 0 ? (int)$cuantas : (int)GRILLA_AZAR_CELDAS;
    $candidatos = $candidatos > 0 ? (int)$candidatos : (int)GRILLA_AZAR_CANDIDATOS;

    // El motor de imágenes es el que sabe dónde viven los archivos en el disco
    if (!function_exists('img_ruta_fisica')) {
        $img = __DIR__ . '/imagenes.php';
        if (is_file($img)) require_once $img;
    }

    // 🧠 `$excluir`: los productos ya vistos en los ciclos anteriores de esta misma portada
    //    (memoria de la portada, en la sesión: ver `portada_memoria_*` en helpers.php).
    // Foto del producto + portada de la tienda (la 1.ª foto de su galería), todo en una consulta
    $sql = "SELECT s.id, s.titulo, s.imagen, s.precio, s.unidad,
                   n.id AS neg_id, n.nombre AS neg, n.slug AS neg_slug,
                   (SELECT f.ruta FROM directorio_fotos f
                     WHERE f.negocio_id = n.id
                     ORDER BY f.orden ASC, f.id ASC LIMIT 1) AS portada
              FROM directorio_servicios s
              JOIN directorio_negocios n ON n.id = s.negocio_id
             WHERE s.activo = 1
               AND s.imagen IS NOT NULL AND s.imagen <> ''
               AND n.estado = 'activo'
               AND EXISTS (SELECT 1 FROM directorio_fotos f WHERE f.negocio_id = n.id)"
             . (function_exists('portada_sql_no_in') ? portada_sql_no_in($excluir, 's.id') : '') . "
             ORDER BY RAND()
             LIMIT " . (int)$candidatos;

    try {
        $filas = db()->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return [];
    }

    $out = [];
    foreach ($filas as $f) {
        $foto   = trim((string)$f['imagen']);
        $port   = trim((string)($f['portada'] ?? ''));
        if ($foto === '' || $port === '') continue;
        // Las dos fotos tienen que EXISTIR en el disco (si no, saldría un hueco roto)
        if (function_exists('img_ruta_fisica')) {
            if (!is_file(img_ruta_fisica($foto)) || !is_file(img_ruta_fisica($port))) continue;
        }
        $out[] = $f;
        if (count($out) >= $cuantas) break;
    }
    return $out;
}

/**
 * 🎲 El rectángulo con la rejilla de productos al azar. Devuelve '' si no hay ninguno que cumpla.
 * @param int    $cuantas celdas a pintar (por defecto 36 = 12 × 3 en PC; en celular se ven 9)
 * @param string $sufijo  sufijo del id cuando la portada repite el bloque (ver portada_ciclos.php)
 */
function grilla_azar_html($cuantas = 0, $sufijo = '') {
    // 🧠 Se salta lo que ya salió en los ciclos anteriores de esta misma portada (memoria de la portada).
    $usados    = function_exists('portada_usados') ? portada_usados('prod') : [];
    $productos = grilla_azar_productos($cuantas, 0, $usados);
    // 🛟 Si no queda material nuevo, se repite antes que dejar la rejilla vacía.
    if (!$productos && $usados) $productos = grilla_azar_productos($cuantas, 0, []);
    if (!$productos) return '';
    if (function_exists('portada_apunta')) portada_apunta('prod', array_column($productos, 'id'));

    $h  = '<style>' . grilla_azar_estilos() . '</style>';
    $h .= '<section class="seccion gz-seccion" id="al-azar' . e((string)$sufijo) . '" aria-label="Productos destacados">';
    // 🏷️ TÍTULO «Más destacados» (pedido del jefe, 2026-09-16: *«a esa cuadrícula de 3×3 hay que
    //    ponerle un título… que diga más destacados, algo chico nada más, no muy notorio pero que
    //    funcione ahí»*). Va **solo en celular** (el jefe dijo que esta tanda es de móvil): una línea
    //    pequeña, en cursiva suave y del color del texto, pegada a la rejilla.
    $h .= '<div class="gz-titulo">Más destacados</div>';
    $h .= '<div class="gz-marco"><div class="gz-grilla">';
    foreach ($productos as $p) {
        $foto = trim((string)$p['imagen']);
        // El nombre del producto va SOLO en el alt/title (el jefe pidió la rejilla sin nombres:
        // en la pantalla no se lee ninguno) — así Google y el lector de pantalla sí lo saben.
        $h .= '<a class="gz-celda" href="' . e(url_negocio((string)$p['neg_slug'])) . '"'
            . ' title="' . e((string)$p['titulo']) . ' — ' . e((string)$p['neg']) . '">'
            . img_tag($foto, (string)$p['titulo'], [
                'sizes' => '(max-width: 759px) 31vw, ' . (int)round(1200 / GRILLA_AZAR_COLS_PC) . 'px',
            ])
            . '</a>';
    }
    $h .= '</div></div></section>';
    return $h;
}

/**
 * El separador entre «⭐ Destacados» y la rejilla: **3 banners en una fila**, y en el celular
 * **solo el primero** (regla del jefe). Se pinta con el mismo motor de publicidad del sitio, así que
 * respeta temas, enlaces, búsquedas, impresiones y clics. Devuelve '' si no hay banners.
 */
function grilla_azar_separador_html() {
    if (!function_exists('banners_fila_html')) {
        $b = __DIR__ . '/banners.php';
        if (is_file($b)) require_once $b;
    }
    if (!function_exists('banners_fila_html')) return '';
    $fila = banners_fila_html(3, null, 'banners-fila--compacta gz-sep');
    if (trim((string)$fila) === '') return '';

    // ⚠️ En móvil se esconde del 2.º banner en adelante SOLO en esta fila (clase `gz-sep`): las demás
    // filas de la portada siguen mostrando los tres apilados, que es lo que el jefe pidió el 2026-09-10.
    return '<style>@media (max-width:768px){.banners-fila.gz-sep>.banner-anuncio:nth-child(n+2){display:none}}</style>'
         . $fila;
}

/** El CSS del rectángulo y su rejilla (se inyecta una sola vez por página). */
function grilla_azar_estilos() {
    $filas_pc = (int)GRILLA_AZAR_FILAS_PC;                // 3 filas en PC
    $px_pc    = (int)GRILLA_AZAR_PX_PC;                   // 184 px por celda en PC
    $movil    = (int)GRILLA_AZAR_MOVIL_VISIBLES;          // 9 → del 10.º en adelante se esconden
    return '
/* ============ 🎲 LA REJILLA DE PRODUCTOS AL AZAR (debajo de los banners) ============ */
.gz-seccion{margin:0 0 10px}
/* 🏷️ «Más destacados»: el titulito que pidió el jefe (2026-09-16). Chico, discreto y **solo en
   celular**: en PC la rejilla va sin título, como estaba. No es un título de sección (no lleva el
   tamaño de `.seccion__titulo`): es una etiqueta suave pegada a la rejilla. */
/* 🆕 MÁS VISIBLE (2026-09-16, orden del jefe, textual): *«el letrero “más destacados” hazlo un poquito
   más llamativo, está muy oculto, muy escondido»*. Antes era una línea de 12,5 px en cursiva y del
   color suave del texto: se perdía. Ahora va **en el rojo de la marca, en negrita, con una barrita
   naranja a la izquierda y una línea que se desvanece a la derecha** — se ve sin gritar (el jefe
   pidió «un poquito», no un titular). Los colores son los de la casa: la barra usa el mismo degradado
   naranja→rojo del marco de las historias (`.hz-marco`) y de los botones del hero. */
.gz-titulo{display:none}
@media (max-width:759px){
  .gz-titulo{display:flex;align-items:center;gap:8px;font-size:15px;font-weight:800;
    letter-spacing:.01em;color:var(--color-primario,#a3123c);margin:0 0 8px 2px}
  .gz-titulo::before{content:"";flex:0 0 auto;width:4px;height:18px;border-radius:999px;
    background:linear-gradient(180deg,#f0861c 0%,#a3123c 100%)}
  .gz-titulo::after{content:"";flex:1 1 auto;height:1px;
    background:linear-gradient(90deg,rgba(240,134,28,.45) 0%,rgba(240,134,28,0) 100%)}
}
/* 📏 A TODO EL ANCHO DE LA PANTALLA (pedido del jefe, 2026-09-15): el bloque mide 100vw y se corre a
   la izquierda la mitad de lo que sobra del contenedor (`50% - 50vw`), así llega a los DOS bordes
   aunque viva dentro de `.main` (max-width 1200 px + 16 px de relleno). Y como `100vw` cuenta la barra
   de scroll de PC (~15 px), la portada lleva `body{overflow-x:clip}` (ver index.php) para que no
   aparezca una barra horizontal. */
.gz-marco{width:100vw;margin-left:calc(50% - 50vw);background:var(--color-fondo-tarjeta,#fff);
  border-top:1px solid var(--color-borde,#e6dbc8);border-bottom:1px solid var(--color-borde,#e6dbc8);
  padding:10px}
/* 📱 CELULAR (SIN TOCAR, como lo mandó el jefe): 3 columnas × 3 filas = 9 productos */
.gz-grilla{display:grid;grid-template-columns:repeat(3,1fr);gap:7px}
.gz-celda{display:block;aspect-ratio:1/1;border-radius:10px;overflow:hidden;background:#f1ece2;
  border:1px solid rgba(230,219,200,.9);transition:transform .12s ease,box-shadow .12s ease}
.gz-celda img{width:100%;height:100%;object-fit:cover;display:block}
.gz-celda:hover{transform:translateY(-2px);box-shadow:0 6px 14px rgba(109,7,26,.16)}
.gz-celda:active{transform:scale(.985)}
.gz-grilla>.gz-celda:nth-child(n+' . ($movil + 1) . '){display:none}
/* 💻 PC (pedido del jefe, 2026-09-15): **3 FILAS DE 12 CON SCROLL LATERAL**, igual que el carrusel
   del celular. Se arma con `grid-auto-flow:column` (llena de arriba abajo y sigue en la columna
   siguiente) + `grid-template-rows:repeat(3,…)`: salen 12 columnas de 3 y, como no caben en el
   ancho, la fila se desliza de lado (se ven ~6 y media y el resto se corre). */
@media (min-width:760px){
  .gz-marco{padding:12px 14px}
  /* 🚫 SIN BARRA DESLIZADORA tampoco en PC (orden del jefe, 2026-09-16: *«abajo de cada scroll has
     puesto como una especie de barra deslizadora que no es necesario que vaya»*). Antes esta fila
     llevaba barra fina de 8 px pintada a mano; ahora se desliza con la rueda, el trackpad o el dedo y
     no se dibuja ninguna barra — igual que el carrusel de productos. */
  .gz-grilla{grid-template-rows:repeat(' . $filas_pc . ',auto);grid-auto-flow:column;
    grid-auto-columns:' . $px_pc . 'px;gap:10px;overflow-x:auto;overscroll-behavior-x:contain;
    scroll-snap-type:x proximity;scrollbar-width:none;padding-bottom:2px}
  .gz-grilla::-webkit-scrollbar{display:none}
  .gz-celda{width:' . $px_pc . 'px;height:' . $px_pc . 'px;scroll-snap-align:start}
  .gz-grilla>.gz-celda:nth-child(n+' . ($movil + 1) . '){display:block}
}
/* 💻 EN PC LA BANDA RESPETA LA MEDIDA DEL SITIO (orden del jefe, 2026-09-19): *«fíjate en el ancho…
   están desbordadas, están demasiado anchas… corrige cualquier sección desbordada que se salga de la
   medida del sitio… solo modo PC»*. Medido el 2026-09-19: esta banda iba de −7 a 1257 px (1 264 px)
   mientras la columna del sitio tiene 1 168 px útiles. Desde **900 px** (el «PC» de la casa, el mismo
   corte que la tira de historias y que los empleos) deja de medir `100vw`.
   ⚠️ La rejilla de dentro NO cambia: sigue siendo **3 filas de 12 con scroll lateral** (se ven ~6 y
   media y el resto se corre) — solo se acorta la ventana por la que se mira.
   📱 En el celular NADA cambia: la banda sigue de borde a borde. */
@media (min-width:900px){
  .gz-marco{width:auto;margin-left:0}
}
';
}
