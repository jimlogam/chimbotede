<?php
/**
 * includes/ficha_posts.php — 🛍️ EL ÁREA DE PRODUCTOS DE LA FICHA (tarjetas del sitio)
 * ============================================================================
 * Pedido del jefe (2026-09-18), textual, en dos tandas:
 *   1.ª *«a partir de ahora los productos los muestres del mismo modo como ya aparecen en la ficha de
 *        explorer.php, como tarjetas de Facebook, así como se ve en explorer, así mostrarás el área de
 *        productos»*.
 *   2.ª *«para diferenciarnos de Facebook, en la ficha de productos seguiremos usando los colores del
 *        sitio; y primero pondremos la foto o fotos del producto, y abajo del producto la descripción
 *        corta con su botón de ver más, y la cantidad de vistas inicia en un número al azar entre 680 y
 *        900; el botón pedir información y el botón llamar deben ir en una sola fila; cambia el texto
 *        del botón "pedir información" y pon "Whatsapear"; no le pongas rating; después de 3 productos
 *        pon un banner mío de mi zona de banners y mi publicidad de mis rubros al final de los
 *        productos, dejando claro que somos la guía más completa de negocios en Chimbote.»*
 *
 * CÓMO QUEDA CADA TARJETA, DE ARRIBA A ABAJO (así lo pidió, en ese orden):
 *   1. 📷 LA FOTO (o las fotos) del producto.
 *   2. 🏷️ El título y el precio.
 *   3. 📝 La descripción CORTA con su botón «Ver más».
 *   4. 👁️ Las vistas (arrancan en un número al azar entre 680 y 900, estable por producto).
 *   5. 💬 Los DOS botones EN UNA SOLA FILA: «Whatsapear» (verde) y «Llamar».
 *   · Sin rating (lo pidió quitar) y con los COLORES DEL SITIO (nada del azul de Facebook).
 *   · 🖼️ Después del 3.er producto se cuela **un banner de la zona de banners**.
 *   · 📚 Al final de los productos va **la publicidad de los rubros** con el lema de la casa.
 *
 * 📐 2026-09-19 — TODAS LAS FOTOS MIDEN LO MISMO (en el celular y en PC). Orden del jefe, textual:
 *   *«en la ficha de productos todas las imágenes de los productos deben tener la misma altura, ya cuando
 *   hacen clic recién debe verse la imagen en su tamaño completo; por ahora todas deben tener la misma
 *   altura, es decir que parezcan parte de ellas ocultas, para que así se vea el diseño más organizado,
 *   mejor presentación, porque se trabaja con varios formatos de aspecto de imagen… siempre procurando que
 *   tengan un aspecto de panorámico para que no ocupe mucho espacio de altura la imagen»*.
 *   → La caja de fotos es **siempre 16:9** (`aspect-ratio` en `.fpc__fotos`) y las fotos se recortan con
 *   `object-fit:cover`; con 2, 3 o 4 fotos se reparten **dentro de la misma caja** (no la alargan). El
 *   recorte ya no depende del ancho de pantalla (antes vivía en un `@media (max-width:899px)` y por eso en
 *   PC cada tarjeta medía lo que medía su foto). **Al tocar la foto se abre `/producto/<id>`**, donde la
 *   imagen se ve completa. Y las tarjetas de una misma fila terminan a la misma altura (`.fpc` en columna
 *   con `.fpc__cuerpo` elástico y la rejilla sin `align-items:start`).
 *
 * 🖼️ 2026-09-19 — **EN PC, DOS BANNERS POR FILA** (misma fecha, tres órdenes, textual): *«corrige el ancho
 *   de los banners: deben verse 2 banners en modo PC; solo tocamos el modo PC, no el modo móvil»* · *«los
 *   banners siguen saliendo mal… hay un banner que está saliendo comprimido»* · *«si son solo imágenes con
 *   un click no es más… el problema está en que le pones CSS: las imágenes ya vienen listas en tamaño»*.
 *   → **Las dos causas, medidas con el navegador ese día:**
 *   1. La zona de banners pinta **3 columnas** y `includes/footer.php` las impone con **`!important`**
 *      (`.banners-fila{grid-template-columns:repeat(3,1fr)!important}`): la fila traía **un** banner y
 *      salía con **un tercio** del ancho. **Arreglo:** la fila se arma con las clases que ya existían
 *      —`banners-fila--dos` (dos iguales) y `banners-fila--uno`—, así que **no se agregó CSS** para esto.
 *   2. La fila medía **`100vw`** con márgenes negativos, pero `.fichap` tiene **`overflow-x:clip`**, y ese
 *      `clip` **recortaba la fila**: solo se veían los 1 010 px que caen dentro de la ficha y, como la fila
 *      iba pegada al borde izquierdo, lo que se veía era su **parte del medio** → el banner de la izquierda
 *      salía cortado por la izquierda y el de la derecha por la derecha (medido: cajas de 677 px de las que
 *      se veían 492 y 506). **Arreglo:** la fila mide lo que mide la ficha (fuera el `100vw` y los márgenes
 *      negativos): **los dos banners entran enteros**, con su proporción, sin recortes ni estirones.
 *   **En el celular no cambia nada**: la regla del pie deja una columna y aquí se esconde el 2.º banner, así
 *   que se ve **uno por fila**, como estaba.
 *
 * Funciones: fichap_css() · fichap_productos_html() · fichap_js() · fichap_vistas()
 */

/** 👁️ LAS VISTAS: arrancan entre 680 y 900, con un número FIJO por producto (no cambia en cada carga). */
function fichap_vistas($producto_id): int {
    $id = (int)$producto_id;
    if ($id <= 0) return 680;
    return 680 + (abs(crc32('fpc-vistas-' . $id)) % 221);   // 680 … 900
}

/** El CSS de las tarjetas (colores del SITIO: los mismos de la ficha). */
function fichap_css(): string {
    static $hecho = false;
    if ($hecho) return '';
    $hecho = true;
    return <<<CSS
<style>
/* 🖼️ PRODUCTOS DE LA FICHA — tarjetas con los COLORES DEL SITIO (2026-09-18)
   `overflow-x:clip` (2026-09-18): el banner de cada dos fichas se sale de esta caja para llegar a los
   bordes de la pantalla y, como `100vw` incluye el ancho de la barra de scroll (15 px en Windows), sin
   esto quedaban 8 px de scroll horizontal en PC. `clip` corta ese sobrante y, a diferencia de `hidden`,
   NO crea un contenedor de scroll: el header pegajoso y el resto del sitio siguen igual. */
.fichap{max-width:720px;margin:0 auto;overflow-x:clip}
.fichap__titulo{font-size:18px;font-weight:800;color:var(--color-primario,#6d071a);margin:0 0 12px;
  display:flex;align-items:center;gap:8px}
.fichap__n{background:var(--color-primario,#6d071a);color:#fff;border-radius:999px;font-size:13px;
  font-weight:800;padding:2px 9px}
.fichap__lista{display:grid;grid-template-columns:1fr;gap:14px}
.fpc{display:flex;flex-direction:column;background:var(--color-fondo-tarjeta,#fff);
  border:1px solid var(--color-borde,#e5e7eb);border-radius:14px;
  overflow:hidden;box-shadow:var(--sombra-tarjeta,0 1px 3px rgba(15,23,42,.08))}
/* El cuerpo se estira: así las tarjetas de una misma fila terminan a la misma altura y los botones
   quedan alineados abajo (la rejilla ya no deja huecos ni escalones). */
.fpc__cuerpo{flex:1 1 auto;padding:13px 14px 4px}
/* 1) LAS FOTOS, ARRIBA Y A TODO EL ANCHO · y son un ENLACE a la ficha del producto
   📐 TODAS LAS FOTOS MIDEN LO MISMO — EN CELULAR Y EN PC (orden del jefe, 2026-09-19, textual):
   *«en la ficha de productos todas las imágenes de los productos deben tener la misma altura… la idea es
   que las publicaciones tengan la misma altura, el mismo tamaño… siempre procurando que tengan un aspecto
   de panorámico para que no ocupe mucho espacio de altura la imagen… en la vista de la ficha no le pongas
   diferentes tamaños, todos con el mismo tamaño»*.
   Antes el recorte a lo horizontal vivía SOLO en el celular (`@media (max-width:899px)`), así que en PC
   cada tarjeta medía lo que medía su foto (una cuadrada ≈497 px de alto, una vertical ≈660 px) y la
   rejilla quedaba desordenada. Ahora **la caja de fotos mide SIEMPRE 16:9** (panorámica), tenga la foto
   el formato que tenga, y las fotos se recortan con `object-fit:cover`: lo que se sale de la caja queda
   oculto y **se ve completo al tocar la foto** (su enlace abre `/producto/<id>`, donde la imagen va
   entera, sin recorte). Con varias fotos la caja NO crece: se reparten DENTRO del mismo 16:9. */
.fpc__fotos{display:grid;gap:3px;background:var(--color-borde,#e5e7eb);aspect-ratio:16/9;overflow:hidden;
  flex:0 0 auto}
.fpc__fotos--1{grid-template-columns:1fr;grid-template-rows:1fr}
.fpc__fotos--2{grid-template-columns:1fr 1fr;grid-template-rows:1fr}
/* 3 y 4 fotos: 2 columnas × 2 filas dentro de la MISMA caja de 16:9 (ninguna alarga la tarjeta); con 3,
   la primera cruza las dos columnas y las otras dos van abajo. */
.fpc__fotos--3,.fpc__fotos--4{grid-template-columns:1fr 1fr;grid-template-rows:1fr 1fr}
.fpc__fotos--3 .fpc__foto:first-child{grid-column:1/-1}
/* La foto llena su hueco y se recorta; el mismo camino que ya funcionaba en el celular. */
.fpc__foto{display:block;min-width:0;min-height:0;background:#f1ece2;overflow:hidden}
.fpc__foto img{width:100%;height:100%;object-fit:cover;display:block;transition:transform .18s ease}
.fpc__foto:hover img{transform:scale(1.02)}
.fpc__nombre{font-size:17.5px;font-weight:800;color:var(--color-texto,#1f2937);margin:0 0 4px;line-height:1.3}
.fpc__precio{font-size:18px;font-weight:800;color:var(--color-primario,#6d071a);margin:0 0 8px}
.fpc__unidad{font-size:14px;font-weight:600;color:var(--color-texto-claro,#6b7280)}
.fpc__texto{font-size:15.5px;line-height:1.55;color:var(--color-texto,#1f2937);margin:0 0 4px;
  overflow-wrap:anywhere}
.fpc__texto[data-abierto="0"]{display:-webkit-box;-webkit-line-clamp:4;-webkit-box-orient:vertical;overflow:hidden}
.fpc__mas{margin:0 0 6px;padding:0;border:0;background:none;font-family:inherit;font-size:15px;
  font-weight:700;color:var(--color-primario,#6d071a);cursor:pointer}
.fpc__mas:hover{text-decoration:underline}
.fpc__vistas{display:flex;align-items:center;gap:6px;font-size:14px;color:var(--color-texto-claro,#6b7280);
  padding-bottom:10px}
/* 5) LOS DOS BOTONES, EN UNA SOLA FILA */
.fpc__acciones{display:flex;gap:9px;padding:0 14px 14px}
.fpc__btn{flex:1 1 0;min-width:0;display:inline-flex;align-items:center;justify-content:center;gap:7px;
  min-height:44px;padding:10px 12px;border:0;border-radius:11px;font-family:inherit;font-size:15.5px;
  font-weight:800;line-height:1;text-decoration:none;cursor:pointer;white-space:nowrap}
.fpc__btn svg{width:19px;height:19px;flex:0 0 auto}
.fpc__btn--wa{background:#25d366;color:#fff}
.fpc__btn--wa:hover{background:#1eb955;color:#fff}
.fpc__btn--tel{background:var(--color-primario,#6d071a);color:#fff}
.fpc__btn--tel:hover{filter:brightness(1.12);color:#fff}
/* 🖼️ EL BANNER ENTRE LAS FICHAS: **cada DOS fichas de producto** y **A TODO EL ANCHO DE LA PANTALLA**
   (orden del jefe, 2026-09-18: *«la regla fue que cada dos fichas de productos se ponía un banner, no cada
   tres; actualmente en el modo PC el banner carga cada tres fichas y no se quedó así: se quedó cada dos
   fichas, se presenta un banner a todo el ancho de la pantalla»*).
   Antes iba **solo después del 3.º** (`if ($i === 3)`), así que en una tienda con 6 productos se veía un
   único banner; ahora se cuela uno **después de cada par de tarjetas** (2, 4, 6…).
   Y para que cruce la pantalla entera —no solo las dos columnas de productos de 1010 px— se sale de la
   lista con el truco de los márgenes negativos: la tarjeta mide `100vw` y se corre a los bordes. */
/* 🖼️ LA FILA DE BANNERS: solo ocupa las DOS columnas de la ficha y nada más.
   🔴 HISTORIA DE UN ENGAÑO (2026-09-19, medido con el navegador): antes esta fila medía `100vw` y se salía
   de la ficha con márgenes negativos (el truco de «banner a todo el ancho de la pantalla» del 2026-09-18),
   pero `.fichap` tiene **`overflow-x:clip`** (se le puso ese mismo día para matar 8 px de scroll
   horizontal)… y ese `clip` **recortaba la fila**: de los 1 366 px de la fila solo se veían los 1 010 px
   que caen dentro de la ficha, y como la fila está pegada al borde izquierdo (-7), lo que se veía era su
   PARTE DEL MEDIO: el banner de la izquierda salía **cortado por su lado izquierdo** y el de la derecha
   **cortado por su lado derecho** (medido: cajas de 677 px de las que solo se veían 492 y 506). Eso es lo
   que el jefe veía como «banner comprimido». Ahora la fila mide lo que mide la ficha: **los dos banners
   entran completos**, uno al lado del otro, y no hace falta ninguna regla de ancho ni de `100vw`.
   ⚠️ Las COLUMNAS tampoco se declaran aquí: la fila lleva `banners-fila--dos` (dos iguales) o
   `banners-fila--uno` (una sola), que ya están en `includes/footer.php` con `!important`. Y el `img` se
   pinta con lo de siempre (`.banner-anuncio img{width:100%;height:auto}`, de `assets/css/banners-v2.css`):
   **la foto entra entera, con su proporción, sin recortes ni estirones**. */
.fpc__banner{grid-column:1/-1}
/* 📱 En el CELULAR se ve UN banner por fila (como estaba): el 2.º banner es para la fila de DOS de la PC. */
@media (max-width:768px){ .fpc__banner > .banner-anuncio:nth-child(n+2){display:none} }
/* 📚 LA PUBLICIDAD DE LOS RUBROS (al final de los productos) */
.fpc-rubros{margin:6px 0 18px;padding:16px 14px;border:1px solid var(--color-borde,#e5e7eb);border-radius:14px;
  background:linear-gradient(180deg,#fffdf8,#fff);box-shadow:var(--sombra-tarjeta,0 1px 3px rgba(15,23,42,.08))}
.fpc-rubros__titulo{font-size:16.5px;font-weight:800;color:var(--color-primario,#6d071a);margin:0 0 4px;line-height:1.35}
.fpc-rubros__sub{font-size:14.5px;color:var(--color-texto,#1f2937);margin:0 0 12px;line-height:1.5}
.fpc-rubros__grid{display:grid;grid-template-columns:1fr 1fr;gap:8px}
.fpc-rubro{display:flex;align-items:center;gap:8px;padding:10px 11px;border:1px solid var(--color-borde,#e5e7eb);
  border-radius:11px;background:#fff;text-decoration:none;color:var(--color-texto,#1f2937);font-size:14px;
  font-weight:700;line-height:1.25;min-width:0}
.fpc-rubro:hover{border-color:var(--color-primario,#6d071a);color:var(--color-primario,#6d071a)}
.fpc-rubro__ico{font-size:18px;line-height:1;flex:0 0 auto}
.fpc-rubro__nom{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.fpc-rubro__n{margin-left:auto;font-size:12px;font-weight:800;color:var(--color-texto-claro,#6b7280);flex:0 0 auto}
.fpc-rubros__todos{display:block;margin-top:12px;padding:11px;border-radius:11px;background:var(--color-primario,#6d071a);
  color:#fff;text-align:center;font-weight:800;font-size:15px;text-decoration:none}
@media (min-width:760px){
  .fpc-rubros__grid{grid-template-columns:repeat(3,1fr)}
}
/* 💻 SOLO EN PC (orden del jefe, 2026-09-18): las fichas de producto en **2 COLUMNAS** — la ficha se
   ensancha, los rubros pasan a 3 columnas y el banner cruza las dos columnas de productos. */
@media (min-width:900px){
  .fichap{max-width:1010px}
  .fichap__lista{grid-template-columns:1fr 1fr;gap:16px}
  .fichap__titulo{font-size:19px}
  .fpc-rubros{padding:18px 18px}
  .fpc-rubros__grid{grid-template-columns:repeat(3,1fr)}
}
@media (max-width:420px){
  .fpc__btn{font-size:14.5px;padding:10px 8px}
}
</style>
CSS;
}

/** La descripción corta de la tarjeta (unas 4 líneas) y si hay más texto. */
function fichap_texto_corto(string $texto, int $palabras = 28): array {
    $plano = trim((string)preg_replace('/\s+/u', ' ', strip_tags($texto)));
    if ($plano === '') return ['', '', false];
    $trozos = preg_split('/\s+/u', $plano) ?: [];
    if (count($trozos) <= $palabras) return [$plano, $plano, false];
    return [implode(' ', array_slice($trozos, 0, $palabras)) . '…', $plano, true];
}

/** 2) Una tarjeta: fotos arriba, textos, vistas y los dos botones en una fila. */
function fichap_producto_html(array $negocio, array $p, array $opts = []): string {
    $nombre = (string)($negocio['nombre'] ?? '');
    $titulo = (string)($p['titulo'] ?? '');
    $pid    = (int)($p['id'] ?? 0);
    $wa     = trim((string)($negocio['whatsapp'] ?? ''));
    $unidad = trim((string)($p['unidad'] ?? ''));
    $vistas = fichap_vistas($pid);

    /* 1) LAS FOTOS: la del producto y, si tiene galería, las demás (como máximo 4) */
    $fotos = [];
    foreach ((array)($opts['fotos'][$pid] ?? []) as $r) { $r = trim((string)$r); if ($r !== '') $fotos[] = $r; }
    if (!$fotos && !empty($p['imagen'])) $fotos[] = (string)$p['imagen'];
    $fotos = array_slice(array_values(array_unique($fotos)), 0, 4);

    $h = '<article class="fpc">';

    if ($fotos) {
        $n = count($fotos);
        $clase_n = $n >= 4 ? 4 : $n;
        /* 📐 EL ANCHO REAL DE CADA HUECO (para que el navegador baje la foto del tamaño justo):
           una sola foto ocupa todo el ancho; con 3, la PRIMERA cruza las dos columnas y las otras dos van
           a media caja; con 2 o 4, media caja cada una (ver `fichap_css()`). */
        $sizes_completo = '(max-width:900px) 100vw, 500px';
        $sizes_media    = '(max-width:900px) 50vw, 250px';
        $enlace_prod = url_producto($pid);
        $h .= '<div class="fpc__fotos fpc__fotos--' . $clase_n . '">';
        foreach ($fotos as $ix => $ruta) {
            $sizes = ($n === 1 || ($n === 3 && $ix === 0)) ? $sizes_completo : $sizes_media;
            /* 👆 LA FOTO ES UN ENLACE A LA FICHA DEL PRODUCTO (orden del jefe, 2026-09-18): *«si le dan
               clic se abre la ficha de producto»*. La foto se ve recortada en panorámico (16:9), en el
               celular y en PC; al tocarla se abre su ficha, donde la imagen va entera, sin recorte. */
            $h .= '<a class="fpc__foto" href="' . e($enlace_prod) . '" title="' . e($titulo) . '">'
                . img_tag($ruta, $titulo, ['sizes' => $sizes])
                . '</a>';
        }
        $h .= '</div>';
    }

    $h .= '<div class="fpc__cuerpo">';
    $h .= '<h3 class="fpc__nombre">' . e($titulo) . '</h3>';
    $h .= '<p class="fpc__precio">' . e(formato_precio($p['precio'] ?? 0));
    if ($unidad !== '' && $unidad !== 'unidad') $h .= ' <span class="fpc__unidad">· ' . e($unidad) . '</span>';
    $h .= '</p>';

    [$corto, $largo, $hay_mas] = fichap_texto_corto((string)($p['descripcion'] ?? ''));
    if ($corto !== '') {
        $h .= '<div class="fpc__texto" data-fichap-texto="0">' . e($corto) . '</div>';
        if ($hay_mas) {
            $h .= '<button type="button" class="fpc__mas" data-fichap-mas data-completo="' . e($largo) . '"'
                . ' data-corto="' . e($corto) . '">Ver más</button>';
        }
    }

    /* 4) LAS VISTAS */
    $h .= '<div class="fpc__vistas">👁️ <b>' . number_format($vistas) . '</b> vistas</div>';
    $h .= '</div>';

    /* 5) LOS DOS BOTONES EN UNA SOLA FILA */
    $h .= '<div class="fpc__acciones">';
    if ($wa !== '') {
        /* 💬 LA REGLA DE ORO DEL BOTÓN DE WHATSAPP (orden del jefe, 2026-09-18, textual): *«todos los
           botones de WhatsApp llevan la url de contexto; ejemplo: si es un producto, lleva información
           de ese producto, como link del producto, precio, etc. En un solo mensaje no debe haber 2
           url.»* → aquí el contexto es EL PRODUCTO: va **su ficha exacta** (`/producto/<id>`, que ya
           trae foto, nombre y precio) **y el precio escrito**, y **una sola URL** en todo el mensaje.
           Se manda por `api/lead.php` (el mismo camino de los botones de la ficha): así al jefe le
           llega el aviso de «pidieron precio» y `llamadas.js` le pega el `&c=1` del clic de verdad. */
        $unidad_msg = ($unidad !== '' && $unidad !== 'unidad') ? ' (' . $unidad . ')' : '';
        $texto_wa = 'Hola *' . wa_nombre_corto($nombre) . '* 👋, quiero consultar por este producto: *'
                  . $titulo . '* — ' . formato_precio($p['precio'] ?? 0) . $unidad_msg
                  . ' · ' . url_producto($pid);
        $href_wa = url('api/lead.php?n=' . (int)($negocio['id'] ?? 0) . '&p=' . $pid
                     . '&u=' . rawurlencode(url_producto($pid)) . '&t=' . rawurlencode($texto_wa));
        $h .= '<a class="fpc__btn fpc__btn--wa" href="' . e($href_wa) . '" target="_blank" rel="noopener">'
            . wa_icono_svg() . ' Whatsapear</a>';
    } else {
        $h .= '<a class="fpc__btn fpc__btn--tel" href="' . e(url_producto($pid)) . '">💬 Consultar</a>';
    }
    if (!empty($negocio['telefono'])) {
        $h .= '<a class="fpc__btn fpc__btn--tel" href="tel:' . e((string)$negocio['telefono'])
            . '" data-negocio="' . (int)($negocio['id'] ?? 0) . '">📞 Llamar</a>';
    }
    $h .= '</div>';

    $h .= '</article>';
    return $h;
}

/**
 * Los banners de la zona de banners que va a usar ESTA ficha (hasta 24, sin repetir: `banners_para()` va
 * marcando los que ya salieron en la misma página). Se piden de una vez para poder colocar una fila
 * **cada dos fichas de producto** —y en PC, **dos banners por fila** (orden del jefe, 2026-09-19)—.
 *
 * ⚠️ `banners_para()` entrega **12 como máximo en cada llamada** (está escrito ahí: `min($n, 12)`), así que
 * se le llama varias veces hasta juntar los 24; la 2.ª llamada ya no repite los de la 1.ª porque la
 * función va marcando los usados. Si la zona de banners tiene menos, devuelve los que haya.
 */
function fichap_banners_pool(?int $categoria_id = null, int $n = 24): array {
    if (!function_exists('banners_para')) {
        $f = __DIR__ . '/banners.php';
        if (is_file($f)) require_once $f;
    }
    if (!function_exists('banners_para')) return [];
    try {
        $out = [];
        for ($vuelta = 0; $vuelta < 4 && count($out) < $n; $vuelta++) {
            $g = banners_para(min(12, $n - count($out)), $categoria_id);
            if (!$g) break;                       // se acabaron los banners de la zona
            $out = array_merge($out, $g);
        }
        return $out;
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Un banner suelto de la zona de banners (el primero libre). Se sigue usando para huecos sueltos.
 */
function fichap_banner_html(?int $categoria_id = null): string {
    if (!function_exists('banners_render')) {
        $f = __DIR__ . '/banners.php';
        if (is_file($f)) require_once $f;
    }
    if (!function_exists('banners_render')) return '';
    $g = fichap_banners_pool($categoria_id, 1);
    if (!$g) return '';
    $html = banners_render($g, 'fpc__banner');
    return $html !== '' ? '<div class="fpc__banner">' . $html . '</div>' : '';
}

/**
 * 📚 LA PUBLICIDAD DE LOS RUBROS (al final de los productos): el lema de la casa y los rubros con
 * más tiendas, cada uno a su página. Es la parte que dice que somos la guía más completa de Chimbote.
 */
function fichap_rubros_html(int $limite = 12): string {
    try {
        $st = db()->prepare("SELECT c.nombre, c.slug, c.icono, COUNT(n.id) AS n
                             FROM directorio_categorias c
                             JOIN directorio_negocios n ON n.categoria_id = c.id AND n.estado = 'activo'
                             GROUP BY c.id, c.nombre, c.slug, c.icono
                             ORDER BY n DESC, c.nombre ASC
                             LIMIT " . max(1, min(24, $limite)));
        $st->execute();
        $rubros = $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        return '';
    }
    if (!$rubros) return '';

    $h  = '<section class="fpc-rubros">';
    $h .= '<h3 class="fpc-rubros__titulo">📚 DeChimbote.com es la guía más completa de negocios en Chimbote</h3>';
    $h .= '<p class="fpc-rubros__sub">Más de 1 700 tiendas, talleres, consultorios y servicios de la provincia '
        . 'del Santa, con sus productos, sus precios y su WhatsApp. Entra por el rubro que buscas:</p>';
    $h .= '<div class="fpc-rubros__grid">';
    foreach ($rubros as $r) {
        $h .= '<a class="fpc-rubro" href="' . e(url_categoria((string)$r['slug'])) . '" title="' . e((string)$r['nombre']) . '">'
            . '<span class="fpc-rubro__ico">' . e((string)($r['icono'] ?: '🏪')) . '</span>'
            . '<span class="fpc-rubro__nom">' . e((string)$r['nombre']) . '</span>'
            . '<span class="fpc-rubro__n">' . number_format((int)$r['n']) . '</span>'
            . '</a>';
    }
    $h .= '</div>';
    $h .= '<a class="fpc-rubros__todos" href="' . e(url('buscar.php')) . '">Ver todas las tiendas de Chimbote →</a>';
    $h .= '</section>';
    return $h;
}

/** La lista completa: las tarjetas, los banners CADA DOS productos y la publicidad de rubros al final. */
function fichap_productos_html(array $negocio, array $productos, array $opts = []): string {
    if (!$productos) return '';
    $h  = fichap_css();
    $h .= '<section class="fichap" id="productos">';
    $h .= '<h2 class="fichap__titulo">🛍️ Productos y servicios <span class="fichap__n">' . count($productos) . '</span></h2>';

    /* 🖼️ Los banners de la ficha: se piden juntos (hasta 24) y van **de a DOS por fila** (cada dos fichas
       de producto). La fila se arma con las clases que YA existen en el sitio —`banners-fila--dos` (dos
       columnas iguales) o `banners-fila--uno` (una sola)—, que están en `includes/footer.php` con
       `!important`: así no hace falta ninguna CSS nueva y no se pelea con la zona de banners, que pinta
       **3** columnas por su cuenta. En el celular manda su regla: **un banner por fila**. */
    $pool = fichap_banners_pool(isset($negocio['categoria_id']) ? (int)$negocio['categoria_id'] : null, 24);
    $np   = count($pool);
    $h .= '<div class="fichap__lista">';
    $i = 0;   // productos pintados
    $k = 0;   // filas de banners colocadas
    foreach ($productos as $p) {
        $i++;
        $h .= fichap_producto_html($negocio, $p, $opts);
        /* 🖼️ CADA DOS FICHAS, UNA FILA DE BANNERS (orden del jefe, 2026-09-18): **dos banners lado a lado
           en PC** (orden del jefe, 2026-09-19: *«corrige el ancho de los banners: deben verse 2 banners en
           modo PC»*) y **uno en el celular**. Si la tienda tiene más productos que banners, ROTAN; las
           repeticiones no suman impresión (la primera vuelta por el pozo es la que cuenta). */
        if ($pool && $i % 2 === 0) {
            $dos   = ($np > 1);                       // ¿hay con quién emparejarlo?
            $par   = $dos ? [$pool[($k * 2) % $np], $pool[($k * 2 + 1) % $np]] : [$pool[0]];
            $clase = $dos ? 'banners-fila--dos' : 'banners-fila--uno';
            $contar = $dos ? (($k * 2 + 1) < $np) : ($k === 0);
            /* El hueco real de cada banner: la mitad de la ficha en PC (unos 500 px) y todo el ancho en el
               celular. Se lo decimos a `img_tag()` para que el navegador baje la foto del tamaño justo y no
               salga borrosa al mostrarla. */
            $h .= banners_render($par, 'fpc__banner ' . $clase, $contar, '(max-width:900px) 100vw, 500px');
            $k++;
        }
    }
    $h .= '</div>';
    $h .= '</section>';

    // 📚 Al final de los productos, la publicidad de los rubros.
    $h .= fichap_rubros_html();
    return $h;
}

/** El JavaScript del «Ver más» de la descripción de cada tarjeta. */
function fichap_js(): string {
    static $hecho = false;
    if ($hecho) return '';
    $hecho = true;
    return <<<JS
<script>
/* 🛍️ «Ver más» de la descripción de cada tarjeta de producto (2026-09-18) */
(function () {
  document.addEventListener('click', function (ev) {
    var b = ev.target.closest ? ev.target.closest('[data-fichap-mas]') : null;
    if (!b) return;
    ev.preventDefault();
    var caja = b.parentNode.querySelector('[data-fichap-texto]');
    if (!caja) return;
    var abierto = caja.getAttribute('data-abierto') === '1';
    caja.textContent = abierto ? b.getAttribute('data-corto') : b.getAttribute('data-completo');
    caja.setAttribute('data-abierto', abierto ? '0' : '1');
    b.textContent = abierto ? 'Ver más' : 'Ver menos';
  });
})();
</script>
JS;
}
