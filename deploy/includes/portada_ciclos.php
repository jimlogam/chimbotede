<?php
/**
 * portada_ciclos.php — 🔁 LA PORTADA QUE SE REPITE (con carga diferida)
 * ====================================================================
 * Pedido del jefe (2026-09-15, textual): *«luego vuelve a repetir nuevamente todo empezando desde los
 * destacados y volviendo a repetir así hasta un máximo de cinco o seis veces, pero usando efecto de
 * loading o efecto de carga para que el sitio web no se vea muy pesado.»*
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * CÓMO FUNCIONA
 * ─────────────────────────────────────────────────────────────────────────────
 * · **Un «ciclo»** es la vuelta completa de la portada, de arriba abajo:
 *      1) la tira de historias (flyers, al azar)     6) los productos (18 en PC / 36 en celular)
 *      2) los banners separadores                     7) «Negocios para ti» (12 en PC / 8 en celular)
 *      3) la rejilla de productos al azar             8) los empleos y anuncios (8 en PC / 5 en celular)
 *      4) la línea separadora                         9) los banners
 *      5) los banners                                10) «Más vistos esta semana» (8)
 * · El **primer ciclo lo pinta `index.php`** (así lo ve Google y se ve al instante) y **todo lo demás
 *   que va debajo del hero se pinta con estas funciones**, para que el ciclo 1 y los siguientes sean
 *   exactamente lo mismo.
 * · Los ciclos **siguientes (hoy el 2 y el 3) se cargan cuando el visitante se acerca al final**
 *   (`portada_cargador_html()`): aparece el aviso **«Cargando más…»** y `api/portada_ciclo.php?n=N`
 *   devuelve el ciclo siguiente, con **contenido nuevo al azar** (otras historias, otros productos,
 *   otros avisos). Así la portada se repite sin que el sitio se vuelva pesado: el HTML y las fotos del
 *   ciclo 2 no se piden hasta que hacen falta.
 *   ⚖️ **Cuántas vueltas: 3 desde el 2026-09-16** (eran 6). El porqué, con los números medidos, está en
 *   `PORTADA_CICLOS_MAX`, aquí abajo: cada ciclo son 133 fotos y 264 KB de HTML.
 * · ⚠️ **Ids únicos por ciclo**: cada ciclo lleva su sufijo (`-2`, `-3`…) en los ids (`hzTira-2`,
 *   `hzVisor-2`, `al-azar-2`, `empleos-2`, `populares-2`…) porque en una página los ids no se pueden
 *   repetir. El JS de las historias inicializa cada tira con su `data-suf` (`window.HZ_INIT`).
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * LO QUE PIDIÓ EL JEFE, BLOQUE POR BLOQUE (todo esto ya está aplicado)
 * ─────────────────────────────────────────────────────────────────────────────
 * | Bloque | Celular (SIN TOCAR) | PC |
 * |---|---|---|
 * | Historias | 1 tira que se pasea sola | igual, a todo el ancho |
 * | Rejilla al azar | 3 × 3 = 9 | **3 filas de 12 con scroll lateral** |
 * | Productos | 6 filas de 6 | **3 filas** de 6 |
 * | Negocios para ti | 8 en 2 columnas | **6 columnas × 2 filas = 12** |
 * | Empleos | **1 fila que se desliza con 5 al azar** | **2 filas de 4** (eran 8 en una fila; el jefe lo cambió el 2026-09-19) |
 * | Más vistos | 8 en 2 columnas | **4 columnas × 2 filas** |
 * | Fotos | **nada sin foto** (el único que puede ir sin foto es Empleos: un aviso es texto) | igual |
 */

// ⚖️ EL PESO EN CELULAR (medido el 2026-09-16, orden del jefe: «en móvil lo siento muy pesado, tardan
//    las imágenes en cargar»). El jefe pidió «cinco o seis vueltas», pero **cada ciclo pesa lo suyo**:
//    133 fotos y 264 KB de HTML. Con 6 ciclos, un visitante que se entretiene bajando se llevaba
//    **802 fotos · 21.2 MB de fotos · 1.66 MB de HTML = 23.2 MB y 822 peticiones** (medido archivo por
//    archivo). Se bajó a 3 y el **2026-09-16 el jefe pidió volver a subirlo a CUATRO** (*«hasta un
//    máximo de cuatro veces se puede duplicar todo el bloque del sitio»*), eso sí: con la condición de
//    que **no se repitan** ni las fotos, ni los negocios, ni las historias (ver la memoria de la
//    portada, `portada_memoria_*` en `helpers.php`).
if (!defined('PORTADA_CICLOS_MAX')) define('PORTADA_CICLOS_MAX', 4);   // 1 pintado + 3 cargados al bajar

/* ============================================================================
   LOS DOS AYUDANTES DE LA PORTADA (vivían en `index.php`; se mudaron aquí el 2026-09-15)
   Están aquí porque los usan los bloques de la portada —que ahora viven en este módulo— y el
   `api/portada_ciclo.php` NO carga `index.php`: si siguieran allí, los ciclos 2..6 reventarían con
   «Call to undefined function corazon_cz_svg()» (pasó en la primera prueba del endpoint).
   ============================================================================ */

// Dibujo del corazón «me interesa»: el MISMO que usa assets/js/carrito.js (si se cambia uno, cambiar
// el otro). Lleva dos caminos: el VACÍO (gris, apagado, esperando el clic) y el LLENO (blanco sobre el
// círculo rojo, cuando ya está en el pedido). Los muestra/oculta carrito.css.
// El jefe pidió que NO parezca ya elegido: antes era el emoji ❤️ rojo y lleno.
if (!function_exists('corazon_cz_svg')) {
    function corazon_cz_svg() {
        return '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
            . '<path class="cz-add__vacio" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round" '
            . 'd="M16.5 3.2c-1.74 0-3.4.8-4.5 2.08C10.9 4 9.24 3.2 7.5 3.2 4.5 3.2 2 5.6 2 8.6c0 3.75 3.4 6.82 8.55 11.5L12 21.4l1.45-1.3C18.6 15.42 22 12.35 22 8.6c0-3-2.5-5.4-5.5-5.4z"/>'
            . '<path class="cz-add__lleno" fill="currentColor" '
            . 'd="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>'
            . '</svg>';
    }
}

// Título de las tarjetas de producto: como MÁXIMO 5 palabras (pedido del jefe 2026-09-10).
if (!function_exists('titulo_cinco_palabras')) {
    function titulo_cinco_palabras($texto, $max = 5) {
        $palabras = preg_split('/\s+/u', trim((string)$texto), -1, PREG_SPLIT_NO_EMPTY);
        if (!$palabras) return '';
        if (count($palabras) <= $max) return implode(' ', $palabras);
        return implode(' ', array_slice($palabras, 0, $max)) . '…';
    }
}

/** El sufijo de ids de un ciclo (el 1 no lleva sufijo, para no cambiar la portada de siempre). */
function portada_sufijo($ciclo) {
    $ciclo = max(1, (int)$ciclo);
    return $ciclo > 1 ? '-' . $ciclo : '';
}

/**
 * 🧱 LA LÍNEA SEPARADORA entre las dos áreas (la que al jefe le gusta: «lo más notoria»).
 * El CSS se inyecta una sola vez por página.
 */
function portada_linea_html() {
    static $css = false;
    $h = '';
    if (!$css) {
        $css = true;
        $h .= '<style>
/* 🧱 La línea separadora: marcada en el centro y desvanecida en las puntas, para que se lea como un
   separador entre dos áreas y no como el borde de una tarjeta.
   ⚠️ El primer intento fue con .30 de opacidad y salía CASI INVISIBLE (medido en el render:
   (210,177,173) sobre el fondo crema (247,239,226)). El jefe pidió que se note MÁS (2026-09-15:
   «me encanta ahí, lo más notoria») → 4 px, centro a .85 y un brillo granate alrededor. */
.seccion-sep{height:4px;margin:26px 0 20px;border-radius:999px;
    background:linear-gradient(90deg,rgba(109,7,26,0) 0%,rgba(109,7,26,.22) 15%,
        rgba(109,7,26,.85) 50%,rgba(109,7,26,.22) 85%,rgba(109,7,26,0) 100%);
    box-shadow:0 1px 6px rgba(109,7,26,.18)}
@media (max-width:640px){ .seccion-sep{height:3px;margin:20px 0 16px} }
</style>';
    }
    return $h . '<div class="seccion-sep" role="separator" aria-hidden="true"></div>';
}

/**
 * 🛍️ LOS PRODUCTOS DE LA PORTADA (6 filas de 6 → en PC solo se ven 3).
 * Solo productos CON FOTO, y se comprueba que el archivo exista (la regla del jefe).
 */
function portada_productos_datos($total = 36) {
    $usuario = function_exists('usuario_actual') ? usuario_actual() : null;
    if (!function_exists('img_ruta_fisica')) {
        $img = __DIR__ . '/imagenes.php';
        if (is_file($img)) require_once $img;
    }

    $filas = [];
    if ($usuario) {
        $filas = obtener_productos_recomendados($usuario['id'], $total * 2);
    } else {
        // 🧠 Sin repetir lo que ya salió en otro ciclo de esta misma portada (memoria de la portada).
        $usados = function_exists('portada_usados') ? portada_usados('prod') : [];
        $lista  = obtener_productos_azar_con_foto($total, $usados);
        // 🛟 Si no queda material nuevo, se repite antes que dejar el bloque vacío.
        if (!$lista && $usados) $lista = obtener_productos_azar_con_foto($total, []);
        return $lista;
    }

    // Las recomendaciones y su relleno NO filtran por foto: aquí se descartan las que no la tienen
    // (y las que apuntan a un archivo que ya no está) para que en la portada no salga ni una sin foto.
    $out = [];
    foreach ($filas as $p) {
        $ruta = function_exists('imagen_producto') ? imagen_producto($p) : trim((string)($p['imagen'] ?? ''));
        if ($ruta === '') continue;
        if (function_exists('img_ruta_fisica') && !is_file(img_ruta_fisica($ruta))) continue;
        $out[] = $p;
        if (count($out) >= (int)$total) break;
    }
    return $out;
}

/** 🛍️ El bloque de productos (6 filas de 6 con sus banners; en PC solo se ven las 3 primeras). */
function portada_productos_html($total = 36, $sufijo = '') {
    $productos = portada_productos_datos($total);
    if ($productos && function_exists('portada_apunta')) portada_apunta('prod', array_column($productos, 'id'));
    static $css = false;
    $h = '';
    if (!$css) {
        $css = true;
        $h .= '<style>
/* 📐 LAYOUT DE LA PORTADA (pedido del jefe, 2026-09-15). En celular NO cambia nada: estas reglas solo
   actúan desde 760 px. */
@media (min-width:760px){
    /* PRODUCTOS: solo tres filas de 6 (el jefe: «esto se repite seis veces en PC, es mucho:
       solamente tres») */
    .prod-fila[data-fila="4"], .prod-fila[data-fila="5"], .prod-fila[data-fila="6"]{display:none}
    .prod-banner[data-banner-fila="4"], .prod-banner[data-banner-fila="6"]{display:none}
    /* NEGOCIOS PARA TI: 6 columnas × 2 filas = 12 */
    .grid-negocios.grid-negocios--seispc{grid-template-columns:repeat(6, minmax(0,1fr));gap:12px}
    /* MÁS VISTOS: 4 columnas × 2 filas = 8 */
    .grid-negocios.grid-negocios--cuatropc{grid-template-columns:repeat(4, minmax(0,1fr));gap:12px}
}
/* En celular, de las 12 fichas de «Negocios para ti» se siguen viendo 8 (2 columnas × 4). */
@media (max-width:759px){
    .grid-negocios--seispc > *:nth-child(n+9){display:none}
}
</style>';
    }

    if (empty($productos)) {
        return $h . '<section class="seccion"><div class="empty-state">'
            . '<div class="empty-state__icono">🛍️</div>'
            . '<div class="empty-state__titulo">Aún no hay productos</div>'
            . '<p>Pronto las tiendas publicarán sus productos aquí.</p>'
            . '</div></section>';
    }

    $filas = array_chunk(array_slice($productos, 0, 36), 6);
    $total_filas = count($filas);
    foreach ($filas as $iFila => $filaProductos) {
        $h .= '<section class="seccion seccion--compacta prod-fila" data-fila="' . ($iFila + 1) . '">';
        $h .= '<div class="carrusel-productos">';
        foreach ($filaProductos as $p) {
            $imagenP = imagen_producto($p);
            $h .= '<a href="' . e(url_negocio($p['negocio_slug'])) . '" class="card-producto cz-prod"'
                . ' data-cz-prod data-id="' . (int)$p['id'] . '"'
                . ' data-titulo="' . e($p['titulo']) . '" data-precio="' . (float)$p['precio'] . '"'
                . ' data-unidad="' . e($p['unidad'] ?? '') . '"'
                . ' data-img="' . e(img_url($imagenP, 300)) . '" data-img800="' . e(img_url($imagenP, 800)) . '"'
                . ' data-neg-id="' . (int)$p['negocio_id'] . '" data-neg-nombre="' . e($p['negocio_nombre']) . '"'
                . ' data-neg-slug="' . e($p['negocio_slug']) . '">';
            $h .= '<div class="card-producto__img">'
                . img_tag($imagenP, $p['titulo'], [
                    // 📐 EL HUECO VERDADERO DE LA TARJETA EN CELULAR (medido el 2026-09-16).
                    //    La tarjeta mide `flex: 0 0 42%` del carrusel (components.css), y el carrusel
                    //    vive dentro de `.main` (360 px de pantalla − 32 px de relleno = 328 px):
                    //    **42 % de 328 = 137.8 px = 38.3vw**, NO 42vw (que son 151 px). Con el dato
                    //    viejo, el celular necesitaba 151 × 2 = 302 px y la escalera salta de 300 a 480:
                    //    por **2 píxeles** se bajaba la foto de 480 (**35 KB de promedio**) en vez de la
                    //    de 300 (**15.5 KB**). Las 19 tarjetas que se ven al abrir pesaban 667 KB en vez
                    //    de ~295 KB. Con el ancho verdadero (39vw = 140 px → 281 px a 2x) el navegador
                    //    elige la de 300 y la foto se ve igual (300 px para un hueco de 138 px = 2.2x).
                    'sizes'   => '(max-width: 560px) 39vw, (max-width: 900px) 31vw, 190px',
                    'onerror' => "this.src='" . url('assets/img/sin-foto.svg') . "'",
                  ]);
            // ⚠️ La etiqueta «★ Destacado» NO se pinta: el jefe mandó borrar esa palabra de la portada
            //    (2026-09-15). Si algún día la quiere, es volver a poner este bloque:
            //    if (!empty($p['destacado'])) $h .= '<span class="tag-top">★ Destacado</span>';
            $h .= '<span class="cz-add cz-add--flotante" role="button" tabindex="0" data-cz-add'
                . ' aria-pressed="false" title="Me interesa">' . corazon_cz_svg() . '</span>'
                . '<span class="cz-vcard__hecho" data-cz-hecho hidden>En tu pedido</span>'
                . '</div>';
            $h .= '<div class="card-producto__body">'
                . '<div class="card-producto__titulo">' . e(titulo_cinco_palabras($p['titulo'])) . '</div>'
                . '<div class="card-producto__precio">' . formato_precio($p['precio']) . '</div>'
                . vigencia_chip_html($p)
                . '</div></a>';
        }
        $h .= '</div></section>';
        // Banners de publicidad: después de cada tanda de dos filas (2.ª, 4.ª y 6.ª)
        if (in_array($iFila, [1, 3], true) || $iFila === $total_filas - 1) {
            $h .= '<div class="prod-banner" data-banner-fila="' . ($iFila + 1) . '">'
                . banners_fila_html(3, null, 'banners-fila--compacta') . '</div>';
        }
    }
    return $h;
}

/** 🏪 «NEGOCIOS PARA TI» / «NEGOCIOS DESTACADOS» (12 en PC en 6×2; 8 en celular en 2 columnas). */
function portada_destacados_html($sufijo = '') {
    $usuario = function_exists('usuario_actual') ? usuario_actual() : null;
    $TOTAL   = 12;
    // 🧠 Sin repetir tiendas de otro ciclo de esta misma portada (memoria de la portada).
    //    Se juntan las dos listas de tiendas: las que salieron como **historia** y las que salieron
    //    como **ficha** — así una tienda no sale de historia y de destacada en la misma portada.
    $usados  = function_exists('portada_usados')
             ? array_merge(portada_usados('neg'), portada_usados('hist'))
             : [];

    if ($usuario) {
        $destacados = obtener_recomendaciones_usuario($usuario['id'], $TOTAL);
        if (count($destacados) < $TOTAL) {
            $destacados = array_merge($destacados, obtener_destacados_aleatorios($TOTAL - count($destacados), $usados));
        }
        $titulo   = 'Negocios para ti';
        $subtitulo = 'Basado en tu historial de búsqueda';
    } else {
        $destacados = obtener_destacados_aleatorios($TOTAL, $usados);
        // 🛟 Si no quedan destacadas nuevas, se repiten antes que dejar el bloque vacío.
        if (!$destacados && $usados) $destacados = obtener_destacados_aleatorios($TOTAL, []);
        $titulo    = 'Negocios destacados';
        $subtitulo = 'Descubre lo mejor de Chimbote';
    }
    if ($destacados && function_exists('portada_apunta')) portada_apunta('neg', array_column($destacados, 'id'));

    $h  = '<section class="seccion">';
    $h .= '<div class="seccion__header"><div>'
        . '<h2 class="seccion__titulo">' . e($titulo) . '</h2>'
        . '<small style="color:var(--color-texto-claro)">' . e($subtitulo) . '</small>'
        . '</div><a href="' . url('buscar.php') . '" class="seccion__ver-todas">Ver todos →</a></div>';

    if (empty($destacados)) {
        $h .= '<div class="empty-state"><div class="empty-state__icono">🏪</div>'
            . '<div class="empty-state__titulo">Aún no hay negocios destacados</div>'
            . '<p>Sé el primero en registrar tu negocio.</p></div></section>';
        return $h;
    }

    $h .= '<div class="grid-negocios grid-negocios--dos grid-negocios--seispc">';
    foreach ($destacados as $n) {
        $h .= '<a href="' . e(url_negocio($n['slug'])) . '" class="card-negocio">';
        $h .= '<div class="card-negocio__imagen">'
            . img_tag($n['imagen_portada'] ?? '', $n['nombre'], [
                // 📐 2 columnas en celular y 6 en PC (~190 px): el `sizes` tiene que decir
                //    la verdad o el celular se baja una foto del doble de tamaño (GUIA_IMAGENES §4).
                //    ⚠️ El número verdadero en celular es **159 px** (2 columnas de 328 px con 10 px de
                //    hueco = 44vw), no los 46vw de antes. Con 41vw el hueco que se anuncia (148 px) es un
                //    7 % más chico que el real a propósito: así el celular de 2x pide 296 px y elige la
                //    versión de **300 (15.5 KB)** en vez de la de **480 (35 KB)**, y la foto igual se ve
                //    nítida (300 px en un hueco de 159 px = 1.9x). Medido el 2026-09-16: este bloque
                //    bajaba 699 KB al abrir y ahora baja ~310 KB.
                'sizes'   => '(max-width: 759px) 41vw, 190px',
                'onerror' => "this.src='" . url('assets/img/sin-foto.svg') . "'",
              ]);
        if (!empty($n['categoria_icono'])) {
            $h .= '<span class="card-negocio__badge">' . e($n['categoria_icono']) . ' ' . e($n['categoria_nombre']) . '</span>';
        }
        if (!empty($n['destacado'])) {
            $h .= '<span class="card-negocio__destacado">★ TOP</span>';
        }
        $h .= '</div><div class="card-negocio__body">'
            . '<h3 class="card-negocio__titulo">' . e($n['nombre']) . '</h3>';
        if (!empty($n['distrito_nombre'])) {
            $h .= '<div class="card-negocio__categoria">📍 ' . e($n['distrito_nombre']) . '</div>';
        }
        $h .= '<div class="card-negocio__meta">';
        if (!empty($n['rating']) && $n['rating'] > 0) {
            $h .= '<span class="card-negocio__rating">★ ' . number_format($n['rating'], 1) . '</span>';
        }
        if (!empty($n['vistas_count'])) {
            $h .= '<span>👁 ' . number_format($n['vistas_count']) . '</span>';
        }
        $h .= '</div></div></a>';
    }
    $h .= '</div></section>';
    return $h;
}

/**
 * 🔥 «MÁS VISTOS ESTA SEMANA» — **8 en PC y 6 en CELULAR** (el jefe lo limitó a 6 el 2026-09-16:
 * *«más vistos esta semana me encanta, limítalo solamente a seis, actualmente tienes ocho»*).
 * Se pintan las 8 (Google las ve y en PC se ven todas) y el CSS esconde de la 7.ª en adelante en
 * celular: así el cambio es **solo de móvil**, como pidió.
 */
function portada_populares_html($sufijo = '') {
    // 🧠 Sin repetir tiendas de otro ciclo de la portada (y tampoco las que ya salieron de historia).
    $usados    = function_exists('portada_usados')
               ? array_merge(portada_usados('neg'), portada_usados('hist'))
               : [];
    $populares = obtener_populares(pop_limite_total(), $usados);
    // 🛟 Si el ranking se queda sin fichas nuevas, se repiten antes que dejar el bloque vacío.
    if (!$populares && $usados) $populares = obtener_populares(pop_limite_total(), []);
    if (!$populares) return '';
    if (function_exists('portada_apunta')) portada_apunta('neg', array_column($populares, 'id'));

    $h  = '<section class="seccion" id="populares' . e($sufijo) . '">';
    $h .= '<style>
/* 📱 En celular, de «Más vistos» solo se ven SEIS (2 columnas × 3 filas) — orden del jefe 2026-09-16 */
@media (max-width:759px){
    #populares' . e($sufijo) . ' .grid-negocios > *:nth-child(n+' . (pop_visibles_movil() + 1) . '){display:none}
}
</style>';
    $h .= '<div class="seccion__header">'
        . '<h2 class="seccion__titulo">🔥 Más vistos esta semana</h2>'
        . '<small style="color:var(--color-texto-claro)">Ranking resetea cada 10 días</small>'
        . '</div>';
    $h .= '<div class="grid-negocios grid-negocios--dos grid-negocios--cuatropc">';
    foreach ($populares as $n) {
        $h .= '<a href="' . e(url_negocio($n['slug'])) . '" class="card-negocio">';
        $h .= '<div class="card-negocio__imagen">'
            . img_tag($n['imagen_portada'] ?? '', $n['nombre'], [
                // 📐 2 columnas en celular y 4 en PC (~280 px). Mismo arreglo que en «Negocios para ti»:
                //    el hueco real es 159 px, no 46vw, y con 41vw el celular elige la versión de 300.
                'sizes'   => '(max-width: 759px) 41vw, 280px',
                'onerror' => "this.src='" . url('assets/img/sin-foto.svg') . "'",
              ]);
        if (!empty($n['categoria_icono'])) {
            $h .= '<span class="card-negocio__badge">' . e($n['categoria_icono']) . '</span>';
        }
        $h .= '</div><div class="card-negocio__body">'
            . '<h3 class="card-negocio__titulo">' . e($n['nombre']) . '</h3>'
            . '<div class="card-negocio__categoria">' . e($n['categoria_icono']) . ' ' . e($n['categoria_nombre']) . '</div>'
            . '<div class="card-negocio__meta">';
        if (!empty($n['rating']) && $n['rating'] > 0) {
            $h .= '<span class="card-negocio__rating">★ ' . number_format($n['rating'], 1) . '</span>';
        }
        if (!empty($n['vistas_count'])) {
            $h .= '<span>👁 ' . number_format($n['vistas_count']) . ' vistas</span>';
        }
        $h .= '</div></div></a>';
    }
    $h .= '</div></section>';
    return $h;
}

/**
 * 🏷️ LA ETIQUETA «Nuevos ingresos» (2026-09-16).
 *
 * Pedido del jefe (textual): *«el letrero que dice ofertas de última hora y el cronómetro te pedí que lo
 * borres, retíralo; y en su lugar pon una línea de separación, una línea que marque claramente que ahí
 * acaba algo, y pon un texto de nuevos ingresos»* · y más abajo: *«después de la cuadrícula de 3×3
 * vienen dos banners, después de esos dos banners hay una línea de separación; debajo de esa línea de
 * separación escribe un texto pequeño: “nuevos ingresos”»*.
 *
 * Así que la etiqueta va en los DOS sitios donde empieza una tanda de productos: debajo de la línea
 * separadora del bloque de arriba (el que antes se llamaba «⚡ Ofertas de última hora») y debajo de la
 * línea separadora que ya había antes de las filas de productos. Es **chica y discreta**, como pidió.
 * El CSS se inyecta una sola vez por página.
 */
function portada_etiqueta_html($texto = 'Nuevos ingresos') {
    static $css = false;
    $h = '';
    if (!$css) {
        $css = true;
        $h .= '<style>
/* 🏷️ «Nuevos ingresos»: la etiqueta que abre una tanda de productos (orden del jefe, 2026-09-16).
   Chica, en el color suave del sitio y con la primera letra en mayúscula. No es un título de sección:
   es una marca de agua para que se entienda que ahí empieza algo nuevo. */
.ni-titulo{font-size:13px;font-weight:700;letter-spacing:.03em;color:var(--color-texto-claro,#7a6a5f);
    margin:0 0 8px 2px}
</style>';
    }
    return $h . '<div class="ni-titulo">' . e($texto) . '</div>';
}

/**
 * 🎠 LAS DOCE FICHAS DE UNA FILA DE «NUEVOS INGRESOS» (2026-09-19).
 *
 * Las pinta tal cual las pide `ofertas_bloque_html()`: **cada fila es UNA VUELTA de la marquesina**, así
 * que la fila lleva **12 fichas** y se repite **dos veces** (el juego de verdad y su copia) para que el
 * bucle no tenga costura. Aquí solo se dibuja un juego.
 *
 * @param array $fila  los productos de esa fila (12)
 * @param bool  $copia true = juego de cierre del bucle: sus enlaces NO se enfocan con el teclado
 *                     (`tabindex="-1"`), porque son los mismos 12 enlaces repetidos (y el juego va
 *                     marcado `aria-hidden` en el HTML, que es cosa de `ofertas_bloque_html()`).
 */
function ofertas_fichas_html(array $fila, $copia = false) {
    $h = '';
    foreach ($fila as $p) {
        $foto = function_exists('imagen_producto') ? imagen_producto($p) : trim((string)($p['imagen'] ?? ''));
        $h .= '<a class="card-producto cz-vcard" href="' . e(url_negocio((string)$p['negocio_slug'])) . '"'
            . ($copia ? ' tabindex="-1"' : '')
            . ' data-cz-prod data-id="' . (int)$p['id'] . '"'
            . ' data-titulo="' . e($p['titulo']) . '" data-precio="' . (float)$p['precio'] . '"'
            . ' data-unidad="' . e($p['unidad'] ?? '') . '" data-img="' . e(img_url($foto, 300)) . '"'
            . ' data-img800="' . e(img_url($foto, 800)) . '"'
            . ' data-neg-id="' . (int)$p['negocio_id'] . '" data-neg-nombre="' . e($p['negocio_nombre']) . '"'
            . ' data-neg-slug="' . e($p['negocio_slug']) . '">';
        $h .= '<div class="card-producto__img">'
            . img_tag($foto, (string)$p['titulo'], [
                'sizes'   => '146px',
                'onerror' => "this.src='" . url('assets/img/sin-foto.svg') . "'",
              ])
            . '<span class="cz-add cz-add--flotante" role="button" tabindex="0" data-cz-add'
            . ' aria-pressed="false" title="Me interesa">' . corazon_cz_svg() . '</span>'
            . '<span class="cz-vcard__hecho" data-cz-hecho hidden>En tu pedido</span>'
            . '</div>';
        $h .= '<div class="card-producto__body">'
            . '<div class="cz-vcard__tienda">🏪 ' . e(mb_substr((string)$p['negocio_nombre'], 0, 30)) . '</div>'
            . '<div class="cz-vcard__prod">' . e(mb_substr((string)$p['titulo'], 0, 42)) . '</div>'
            . '</div></a>';
    }
    return $h;
}

/**
 * 🎠 EL BLOQUE DE PRODUCTOS QUE VA PEGADO DEBAJO DE LAS HISTORIAS — **«Nuevos ingresos»**.
 *
 * ⚠️ **ESTE BLOQUE ANTES SE LLAMABA «⚡ Ofertas de última hora»** y llevaba su letrero y un cronómetro
 * de cuenta atrás. El jefe mandó **quitarlos** (2026-09-16) y en su lugar va **una línea de separación
 * y la etiqueta «Nuevos ingresos»**.
 *
 * ⚠️ Y **el 2026-09-19 el jefe mandó convertirlo en MARQUESINA** (textual): *«los nuevos ingresos son un
 * auto scroll de 12 tiendas por fila en el modo pc, uno gira de derecha a izquierda y otros de izquierda
 * a derecha, con efecto de marquesina lenta»*. Así que:
 *
 * | | Cómo queda |
 * |---|---|
 * | **Cuántas fichas** | **24** = **2 filas de 12** (antes eran 12: 6 y 6) |
 * | **PC (≥760 px)** | Cada fila **gira sola**, **despacio** y **para siempre**: la **1.ª de derecha a izquierda** y la **2.ª al revés** (`.cz-mq--vuelta`), con `translateX(0 → -50%)` a **75 s** por vuelta (~25 px/s). Al pasar el ratón **se para** (si no, no habría forma de tocar una ficha) |
 * | **Celular (<760 px)** | **Igual que estaba**: 2 filas de 6 que **se deslizan con el dedo** (la copia del bucle no se pinta y las fichas 7..12 de cada fila tampoco, así el celular no carga ni 12 fotos de más) |
 * | **Sin costura** | Cada fila lleva **el juego de 12 fichas + su COPIA** (`aria-hidden`, enlaces no enfocables). Al llegar a -50 % la copia está justo donde empezó el juego → el bucle no salta |
 * | **Accesibilidad** | Con `prefers-reduced-motion: reduce` **no hay animación** y la fila vuelve a deslizarse a mano |
 *
 * El JS (`carrito.js` → `pintarVistos()`) sigue **poniendo delante** las fichas de lo que el visitante ya
 * miró, pero ahora en **el juego de verdad** de la 1.ª fila (y después rehace la copia para que las dos
 * sigan siendo idénticas). Antes lo pintaba SOLO el JavaScript: si el visitante había mirado 3 productos,
 * se veían 3, y a un visitante nuevo no le salía nada; por eso el bloque lo pinta el **servidor** y el JS
 * solo adelanta lo ya mirado.
 *
 * @param int $total fichas con foto a traer (por defecto **24** = 2 filas de 12).
 */
function ofertas_bloque_html($total = 0) {
    $por_fila = 12;
    $total    = $total > 0 ? (int)$total : $por_fila * 2;

    // 🧠 Sin repetir lo que ya salió en la rejilla o en los productos de esta misma portada.
    $usados = function_exists('portada_usados') ? portada_usados('prod') : [];
    $prods  = obtener_productos_azar_con_foto($total, $usados);
    if (!$prods && $usados) $prods = obtener_productos_azar_con_foto($total, []);   // 🛟 nunca vacío
    if (!$prods) return '';
    if (function_exists('portada_apunta')) portada_apunta('prod', array_column($prods, 'id'));

    $h  = '<section class="seccion cz-vistos-sec" id="czVistosSec">';
    $h .= portada_linea_html();          // 🧱 la línea que marca que ahí acaba algo
    $h .= portada_etiqueta_html();       // 🏷️ «Nuevos ingresos»

    static $css = false;
    if (!$css) {
        $css = true;
        $h .= '<style>
/* 🎠 LA MARQUESINA DE «NUEVOS INGRESOS» (orden del jefe, 2026-09-19: «un auto scroll de 12 tiendas por
   fila en el modo pc, uno gira de derecha a izquierda y otros de izquierda a derecha, con efecto de
   marquesina lenta»).
   · La ESTRUCTURA es: .cz-mq (la ventana, que recorta) > .cz-mq__pista (lo que se mueve) > los DOS
     juegos de 12 fichas (.cz-mq__set: el de verdad y su copia).
   · El ancho de UNA vuelta es exactamente el de un juego, así que el `-50%` de la animación cae justo
     donde empezó la copia → **bucle sin costura**. Por eso el `padding-right:12px` de cada juego (el
     hueco entre la última ficha y la primera de la vuelta siguiente es un hueco más, como los de
     adentro) y el `gap:0` de la pista.
   · ⚠️ Se mide en píxeles reales: 12 fichas de 146 px + 11 huecos de 12 px + el hueco de la vuelta =
     **1 896 px** por vuelta, más ancho que la ventana de PC (~1 168 px), que es lo que hace falta para
     que la marquesina se vea moverse. */
.cz-mq__pista{display:flex;flex:0 0 auto}   /* ⚠️ `flex:0 0 auto`: la pista NO se puede encoger (si se
                                                encogiera, las fichas se aplastarían y no habría marquesina) */
.cz-mq__set{display:flex;gap:12px;padding-right:12px}
.cz-mq__set--copia{display:none}          /* 📱 en celular la copia NO se pinta: la fila se desliza a mano */
@media (min-width:760px){
  .cz-mq{overflow:hidden}                 /* la ventana: lo que sobra, recortado */
  .cz-mq__set--copia{display:flex}
  .cz-mq__pista{width:max-content;animation:czMarquesina 75s linear infinite}
  .cz-mq--vuelta .cz-mq__pista{animation-direction:reverse}
  /* Al pasar el ratón por encima se PARA: si no, es imposible tocar una ficha que va pasando. */
  .cz-mq:hover .cz-mq__pista{animation-play-state:paused}
  /* 🎨 El desvanecido de las dos puntas, para que las fichas no salgan cortadas a cuchillo. */
  .cz-mq{-webkit-mask-image:linear-gradient(90deg,transparent 0,#000 26px,#000 calc(100% - 26px),transparent 100%);
     mask-image:linear-gradient(90deg,transparent 0,#000 26px,#000 calc(100% - 26px),transparent 100%)}
}
@keyframes czMarquesina{from{transform:translateX(0)}to{transform:translateX(-50%)}}
@media (max-width:759px){
  /* 📱 CELULAR: se queda como estaba — **6 fichas por fila** (las otras 6 no se pintan, así el celular
     tampoco se baja sus fotos) y la fila se desliza con el dedo, la rueda o el trackpad. */
  .cz-mq__set>.cz-vcard:nth-child(n+7){display:none}
}
@media (prefers-reduced-motion:reduce){
  /* Quien pidió en su sistema «menos movimiento» ve la fila quieta y deslizable. */
  .cz-mq__pista{animation:none}
  .cz-mq{overflow-x:auto;-webkit-mask-image:none;mask-image:none}
}
</style>';
    }

    // 12 y 12: dos filas-marquesina, la segunda al revés (una gira hacia un lado y la otra hacia el otro).
    $filas = array_chunk(array_slice($prods, 0, $por_fila * 2), $por_fila);
    foreach ($filas as $i => $fila) {
        $h .= '<div class="carrusel-tiendas cz-mq' . ($i % 2 ? ' cz-mq--vuelta' : '') . '">';
        $h .= '<div class="cz-mq__pista">';
        $h .= '<div class="cz-mq__set">' . ofertas_fichas_html($fila) . '</div>';
        // La COPIA que cierra el bucle: mismas fichas, marcadas `aria-hidden` (no se leen dos veces).
        $h .= '<div class="cz-mq__set cz-mq__set--copia" aria-hidden="true">'
            . ofertas_fichas_html($fila, true) . '</div>';
        $h .= '</div></div>';
    }
    $h .= '</section>';
    return $h;
}

/**
 * 🔁 UN CICLO COMPLETO DE LA PORTADA (de la tira de historias a «Más vistos»).
 * @param int    $ciclo 1 = el de siempre (sin sufijo de ids); 2..6 = los que se cargan al bajar.
 * @param string $tras_historias HTML que se cuela **justo debajo de la tira de historias** (antes iba
 *              en `index.php`, entre el hero y la tira). Lo usa la portada para el hueco de
 *              «⚡ Ofertas de última hora» (`#czVistos`): el jefe pidió (2026-09-16) que **las
 *              historias queden pegadas a los dos botones del hero**, y ese bloque se metía en medio.
 *              ⚠️ Solo se pasa en el ciclo 1 (los demás no llevan ese hueco).
 */
function portada_ciclo_html($ciclo = 1, $tras_historias = '') {
    $suf = portada_sufijo($ciclo);

    // 🔑 LA SESIÓN, ANTES QUE NADA (2026-09-16). Aquí se apoya la memoria de la portada (abajo) y
    //    **tiene que estar arrancada ANTES del primer bloque**: el sitio arranca la sesión dentro de
    //    `usuario_actual()`, que en la portada se llama **después** de las historias y de la rejilla…
    //    y en `api/portada_ciclo.php` (los ciclos 2..4) la primera llamada a `usuario_actual()` es la de
    //    los PRODUCTOS. Resultado medido el 2026-09-16: las historias y la rejilla de los ciclos 2..4
    //    **repetían** las del ciclo 1, porque leían la memoria cuando la sesión todavía no existía.
    //    `iniciar_sesion()` es idempotente (solo arranca si no está arrancada) y aquí no se ha impreso
    //    nada todavía, así que no hay riesgo de «headers already sent».
    if (function_exists('iniciar_sesion') && session_status() !== PHP_SESSION_ACTIVE) iniciar_sesion();

    // 🧠 LA MEMORIA DE LA PORTADA: el ciclo 1 la deja en blanco (cada carga de la portada empieza
    //    limpia) y los ciclos siguientes van apuntando lo que muestran, para no repetirlo.
    //    Ver `portada_memoria_*` en `includes/helpers.php`.
    if ((int)$ciclo <= 1 && function_exists('portada_memoria_arranque')) portada_memoria_arranque();

    if (!function_exists('historias_tira_html')) {
        $f = __DIR__ . '/historias.php';
        if (is_file($f)) require_once $f;
    }
    if (!function_exists('grilla_azar_html')) {
        $f = __DIR__ . '/grilla_azar.php';
        if (is_file($f)) require_once $f;
    }

    $h = '';

    // 1) La tira de historias (flyers, al azar, con su paseo lento)
    if (function_exists('historias_tira_html')) $h .= historias_tira_html(0, $suf);

    // 1bis) Lo que tenga que ir pegado debajo de las historias (hoy: «Ofertas de última hora»)
    $h .= (string)$tras_historias;

    // 2) El separador de banners (1 en celular / 3 en PC) y 3) la rejilla al azar
    if (function_exists('grilla_azar_separador_html')) $h .= grilla_azar_separador_html();
    if (function_exists('grilla_azar_html'))           $h .= grilla_azar_html(0, $suf);

    // 4) Banners + 5) la línea separadora
    $h .= banners_fila_html(3, null, 'banners-fila--compacta');
    $h .= portada_linea_html();
    // 🏷️ Debajo de la línea va la etiqueta «Nuevos ingresos» (pedido del jefe, 2026-09-16): así se
    //    entiende que lo que viene son las filas de productos nuevos.
    $h .= portada_etiqueta_html();

    // 6) Los productos
    $h .= portada_productos_html(36, $suf);

    // 7) Negocios para ti · 8) Empleos y anuncios · 9) Banners · 10) Más vistos
    $h .= portada_destacados_html($suf);
    $h .= empleos_bloque_html(8, true, $suf);
    $h .= banners_fila_html(3, null, 'banners-fila--compacta');
    $h .= portada_populares_html($suf);

    return $h;
}

/**
 * 🌀 EL CARGADOR: el aviso «Cargando más…» y el vigilante que pide los ciclos siguientes cuando el
 * visitante se acerca al final. Se pinta UNA sola vez, al final de la portada (antes del cierre).
 *
 * @param int $max ciclos totales (1 + los cargados). El jefe pidió «un máximo de cinco o seis veces».
 */
function portada_cargador_html($max = 0) {
    $max = $max > 0 ? (int)$max : (int)PORTADA_CICLOS_MAX;
    if ($max < 2) return '';

    $h  = '<div id="pdcCola"></div>';
    $h .= '<div class="pdc-aviso" id="pdcAviso" hidden>'
        . '<span class="pdc-spinner" aria-hidden="true"></span>'
        . '<span>Cargando más negocios…</span></div>';
    $h .= '<div class="pdc-sentina" id="pdcSentina" aria-hidden="true"></div>';
    $h .= '<style>
/* 🌀 El aviso de carga y el vigilante del final de la portada */
.pdc-aviso{display:flex;align-items:center;justify-content:center;gap:10px;margin:18px 0 6px;
    color:var(--marca-granate,#6d071a);font-weight:700;font-size:15px}
.pdc-aviso[hidden]{display:none}
.pdc-spinner{width:20px;height:20px;border-radius:50%;flex:0 0 auto;
    border:3px solid rgba(109,7,26,.22);border-top-color:var(--marca-granate,#6d071a);
    animation:pdcGira .8s linear infinite}
@keyframes pdcGira{to{transform:rotate(360deg)}}
.pdc-sentina{height:1px}
</style>';
    $h .= '<script>
/* 🔁 Trae los ciclos siguientes de la portada cuando el visitante se acerca al final.
   · El HTML llega por `fetch` y se inserta tal cual (los <script> que vinieran NO se ejecutan: el JS
     de las historias se inicializa a mano con window.HZ_INIT, que es lo único que hace falta).
   · El aviso «Cargando más…» se deja ver un mínimo de 700 ms para que no sea un parpadeo.
   · Si falla la red, se vuelve a intentar la próxima vez que el vigilante entre en pantalla. */
(function () {
  var MAX = ' . (int)$max . ';
  var n = 1, cargando = false;
  var cola   = document.getElementById("pdcCola");
  var aviso  = document.getElementById("pdcAviso");
  var sentina = document.getElementById("pdcSentina");
  if (!cola || !sentina) return;

  function trae() {
    if (cargando || n >= MAX) return;
    cargando = true;
    var t0 = Date.now();
    aviso.hidden = false;
    n++;

    fetch(window.SITE_URL + "/api/portada_ciclo.php?n=" + n, { credentials: "same-origin" })
      .then(function (r) { if (!r.ok) throw new Error("HTTP " + r.status); return r.text(); })
      .then(function (html) {
        var resto = 700 - (Date.now() - t0);            // el aviso se ve un momento, sin parpadeo
        setTimeout(function () {
          if (html && html.length > 50) {
            var d = document.createElement("div");
            d.innerHTML = html;
            while (d.firstChild) cola.appendChild(d.firstChild);
            if (window.HZ_INIT) window.HZ_INIT(cola);
          } else {
            n = MAX;                                     // no hay más: se apaga el cargador
          }
          aviso.hidden = (n >= MAX);
          cargando = false;
        }, Math.max(0, resto));
      })
      .catch(function () {
        n--;                                             // no se pierde el turno: se reintenta
        aviso.hidden = true;
        cargando = false;
      });
  }

  if ("IntersectionObserver" in window) {
    var obs = new IntersectionObserver(function (entradas) {
      for (var i = 0; i < entradas.length; i++) {
        if (entradas[i].isIntersecting) trae();
      }
    }, { rootMargin: "900px 0px" });                     // empieza a cargar antes de llegar al final
    obs.observe(sentina);
  } else {
    window.addEventListener("scroll", function () {
      var y = window.scrollY + window.innerHeight;
      if (y > document.documentElement.scrollHeight - 1200) trae();
    }, { passive: true });
  }
})();
</script>';
    return $h;
}
