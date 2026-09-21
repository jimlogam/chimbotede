<?php
require_once __DIR__ . '/config.php';

$usuario = usuario_actual();

// ⚠️ LOS DOS AYUDANTES DE LA PORTADA (`corazon_cz_svg()` y `titulo_cinco_palabras()`) SE MUDARON el
// 2026-09-15 a **`includes/portada_ciclos.php`**: ahora los usan los bloques de la portada, que ya no
// viven en este archivo sino en ese módulo (así valen igual para el ciclo 1 y para los ciclos 2..6 que
// los pide `api/portada_ciclo.php`, donde index.php no se carga). Aquí no se definen para no tener el
// mismo dibujo en dos sitios.

// 🔁 LOS BLOQUES DE LA PORTADA YA NO SE CARGAN AQUÍ: viven en `includes/portada_ciclos.php` (así el
// ciclo 1 y los ciclos 2..6 que se cargan por AJAX son exactamente lo mismo, y no se consultan dos
// veces los mismos datos). Ahí está todo: los productos AL AZAR y solo con foto, «Negocios para ti»
// (12 en PC en 6 × 2), los empleos (8 en PC / 5 en celular al azar) y «Más vistos» (4 × 2 en PC).

$categorias = obtener_categorias();

$titulo_pagina = 'Inicio';
$descripcion_pagina = 'El marketplace de negocios de Chimbote y la provincia del Santa, Áncash, Perú.';

// 📣 EL MENSAJE DE LA TARJETA AL COMPARTIR EL ENLACE (2026-09-15, pedido del jefe:
// «algo comercial, con emoticones, la guía más grande de negocios en Chimbote»).
// Es lo que se lee en WhatsApp/Facebook cuando alguien pega el enlace: va aparte del
// `<title>` y de la meta description de Google, que siguen como estaban.
$og_titulo = '🏪 La guía de negocios más grande de Chimbote';
$og_descripcion = '🔍 Entra y mira lo que más venden: tiendas, precios y WhatsApp directo 🛍️ '
                . 'Todo Chimbote, Nuevo Chimbote y Santa en un solo lugar 🇵🇪';

// 🔗 CANONICAL (2026-09-16, SEO): la portada se declara a sí misma, en una sola dirección. Sin
// esto, `dechimbote.com` y `dechimbote.com/index.php` podían contar como dos páginas distintas.
$canonical_url = url('');

include __DIR__ . '/includes/header.php';
?>

<?php // ============ 🎯 LA BANDA DE LOS TRES BOTONES · SU NUEVO SITIO (2026-09-19) ============
      // Pedido del jefe (textual): *«este bloque de crear tiendas que tiene 3 botones lo vas a bajar
      // 2 secciones, es decir debajo de las historias destacadas y debajo de los nuevos ingresos,
      // quedando exactamente encima de los banners»*.
      //
      // ANTES abría la portada (era lo primero debajo de la marquesina de rubros). AHORA va **dos bloques
      // más abajo**, dentro del ciclo 1 de la portada y en este orden:
      //     1) la tira de **HISTORIAS DESTACADAS**  → `historias_tira_html()`
      //     2) el bloque **«NUEVOS INGRESOS»**      → `#czVistos`, o sea `ofertas_bloque_html()` (12 productos)
      //     3) ⬅️ **ESTA BANDA** (Crear tienda · Ver tiendas cerca · Descubrir Nuevas Tiendas)
      //     4) los **BANNERS** separadores (3 en PC / 1 en celular) y, debajo, la rejilla al azar
      //
      // 🔧 CÓMO ESTÁ HECHO (y por qué así): el marcado de la banda **no se movió de sitio en el archivo**
      //    —sigue aquí abajo, entero y con sus mismos estilos, sin tocarle una letra— pero se **guarda en
      //    un búfer** (`ob_start()`) en vez de imprimirse, y se imprime mucho más abajo, **dentro de la
      //    llamada al ciclo 1** de la portada (es el 3.er argumento que se le pega a `portada_ciclo_html()`,
      //    ver la línea de más abajo). Así **el orden de la página lo manda una sola línea** y devolver la
      //    banda arriba (si el jefe lo pide) es mover esa línea, no este bloque de HTML.
      //
      // ⚠️ Lo que se llevó consigo, y tiene que viajar siempre con ella:
      //    · **📍 `#cercaIndex`** — el hueco donde el JS pinta los resultados de «Ver tiendas cerca»
      //      (justo debajo va aquí, unas líneas más abajo). El JS **baja hasta ese hueco** al pintar, así
      //      que si se quedara arriba el visitante tocaría el botón y los resultados le saldrían fuera de
      //      la vista. Va VACÍO hasta que se toca el botón: no ocupa espacio.
      //    · **el `<h1 class="sr-solo">`** de la portada (invisible: solo lo lee Google).
      // ⚠️ Y OJO: todo comentario de PHP tiene que ir DENTRO del bloque; si se escribe después de cerrarlo,
      //    el visitante ve el comentario escrito en la página (ver la trampa del 2026-09-16, más abajo).
      // ============================================================================================== ?>
<?php ob_start(); ?>

<!-- HERO (banda compacta: SOLO los dos botones, en una sola fila y centrados)
     Pedido del jefe (2026-09-10): fuera el titular "Negocios de Chimbote a un clic 📍" y la bajada
     "Encuentra restaurantes, bodegas…" — sobraban y ocupaban media pantalla. Se recupera ese espacio.
     El titular se deja INVISIBLE (clase .sr-solo) solo para Google: el visitante ya no lo ve.
     Botones: "Agregar" (naranja) + "Ver tiendas cerca" (verde), con el texto centrado.
     ⚠️ ESTE BLOQUE NO SE IMPRIME AQUÍ (2026-09-19): está dentro del búfer que abre la línea de arriba y
     lo imprime el ciclo 1 de la portada, después de «Nuevos ingresos» y antes de los banners. -->
<section class="hero hero--compacto">
    <h1 class="sr-solo">Negocios de Chimbote a un clic</h1>

    <div class="hero__ctas">
        <a href="<?= url('crear-tienda') ?>" class="hero__cta"
           title="Crear mi tienda con El maestro (Inteligencia Artificial): sube 8 fotos y la arma">Crear tienda</a>
        <a href="<?= url('buscar.php') ?>" class="hero__cta hero__cta--geo" id="btnHeroCerca"
           title="Ver las tiendas que están cerca de ti">
            <span class="hero__cta-t"><?= cerca_pin_svg('hero__cta-pin') ?>Ver tiendas cerca</span>
            <span class="hero__cta-s">comparte tu ubicación</span>
        </a>
    </div>

    <!-- 📘 EL BOTÓN DEL EXPLORER (2026-09-16, pedido del jefe, textual): *«debajo del botón “crear
         tienda” y “ver tiendas cerca”… agrega un tercer botón azul, delgado y ancho, que sirva para
         entrar al link https://dechimbote.com/explorer.php; ponle el icono de Facebook al botón; el
         botón de explorer es ancho y no muy alto.»*
         · Va **DEBAJO de los dos** botones del hero (no dentro de `.hero__ctas`, para que no se reparta
           el ancho con ellos: tiene que ser **ancho**, todo el ancho de la banda).
         · **Delgado**: 44 px de alto contra los 58 de los otros dos.
         · **Azul de Facebook** `#0866ff` — el MISMO que eligió el jefe para el Explorer después de
           decir que el `#1877f2` viejo se veía *«muy claro»* (ver `assets/css/explorer.css`, §Variables
           del muro).
         · **El icono es la «f» oficial** (trazo de Font Awesome, `facebook-f`), en blanco y con
           `currentColor` para que herede el color del texto. Se pinta en línea aquí mismo: no hace
           falta un ayudante porque este botón vive solo en la portada.
         · **El rótulo es «Descubrir Nuevas Tiendas»** (el jefe lo cambió el mismo día: al principio
           decía «Explorer · el muro de las tiendas», y pidió *«cambia el texto explorer del botón azul y
           pon “Descubrir Nuevas Tiendas”»*). Va en **una sola línea** con el icono delante.
         · ⚠️ La CSS va en el `<style>` del hero, unas líneas más abajo (`.hero__explorer`). -->
    <a href="<?= url('explorer.php') ?>" class="hero__explorer"
       title="Descubrir nuevas tiendas en el Explorer: el muro de las tiendas de Chimbote, con la forma del muro de Facebook">
        <span class="hero__explorer-f" aria-hidden="true"><svg viewBox="0 0 320 512" focusable="false"><path fill="currentColor" d="M80 299.3V512H196V299.3h86.5l18-97.8H196V166.9c0-51.7 20.3-71.5 72.7-71.5 16.3 0 29.4.4 37 1.2V7.9C291.4 4 256.4 0 236.2 0 129.3 0 80 50.5 80 154.2v47.3H16v97.8h64z"/></svg></span>
        <span class="hero__explorer-t">Descubrir Nuevas Tiendas</span>
    </a>

    <p class="hero__geo-aviso" id="heroGeoAviso" hidden style="color:#fff;margin-top:10px;font-size:14px"></p>

    <style>
        /* 🚫 NADA DE BARRAS LATERALES EN LA PORTADA (2026-09-16). Las bandas de la portada miden
           `100vw` (la tira de historias `.hz-blanco` y la rejilla al azar `.gz-marco`) y `100vw`
           CUENTA la barra de scroll: sin esto la página medía **8 px más que la pantalla** y se podía
           arrastrar de lado con el dedo. Las guías (`GUIA_DISENO_DEL_INDEX.md`, `grilla_azar.php`,
           `historias.php`) daban por hecho este recorte, pero no estaba en ninguna parte.
           ⚠️ Se usa `clip` (no `hidden`): recorta sin convertir el body en contenedor de scroll. */
        body{overflow-x:clip}

        /* Banda compacta: se recupera el espacio que ocupaban el titular y la bajada */
        .hero--compacto{padding:16px 16px 18px;margin-bottom:16px}
        /* Los dos botones en UNA fila, del mismo alto y con el texto centrado */
        .hero__ctas{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;align-items:stretch}
        .hero__cta{display:inline-flex;align-items:center;justify-content:center;text-align:center;
            min-height:58px;padding:10px 28px;font-size:17px}
        /* 🎨 El botón de ubicación va en NARANJA (2026-09-14, pedido del jefe): el verde se
           confundía con los botones de WhatsApp. Mismo diseño que includes/btn_cerca.php (§6.4). */
        .hero__cta--geo{position:relative;overflow:hidden;display:inline-flex;flex-direction:column;
            align-items:center;justify-content:center;gap:1px;border:0;cursor:pointer;line-height:1.2;
            text-align:center;color:#fff;border-radius:12px;
            background:linear-gradient(105deg,#a3123c 0%,#d0312a 48%,#f0861c 100%);
            box-shadow:0 0 0 3px #fbd7a4,0 0 18px rgba(240,134,28,.45),0 7px 16px rgba(109,7,26,.28),
                inset 0 2px 0 rgba(255,255,255,.42),inset 0 -3px 0 rgba(90,6,20,.30);
            min-height:58px;padding:10px 24px;
            transition:transform .12s ease,filter .12s ease,box-shadow .12s ease}
        .hero__cta--geo::after{content:'';position:absolute;inset:0;pointer-events:none;
            background:linear-gradient(112deg,rgba(255,255,255,.30) 0%,rgba(255,255,255,.08) 40%,rgba(255,255,255,0) 64%)}
        .hero__cta--geo:hover{filter:brightness(1.07);transform:translateY(-1px);
            box-shadow:0 0 0 3px #fde0b4,0 0 26px rgba(240,134,28,.60),0 9px 20px rgba(109,7,26,.32),
                inset 0 2px 0 rgba(255,255,255,.5),inset 0 -3px 0 rgba(90,6,20,.30)}
        .hero__cta--geo:active{transform:translateY(1px) scale(.995)}
        .hero__cta--geo.is-busy{opacity:.6;cursor:wait;pointer-events:none}
        .hero__cta--geo>span{position:relative;z-index:1}
        .hero__cta-t{display:inline-flex;align-items:center;gap:7px;font-size:17px;font-weight:800;color:#fff;
            text-shadow:0 2px 3px rgba(90,6,20,.45)}
        .hero__cta-s{font-size:12.5px;font-weight:500;color:rgba(255,255,255,.95)}
        .hero__cta-pin{flex:0 0 auto;width:19px;height:19px;color:#fff;filter:drop-shadow(0 1px 2px rgba(90,6,20,.5))}

        /* 📘 EL TERCER BOTÓN: EL EXPLORER (2026-09-16, pedido del jefe).
           Azul de Facebook, **ANCHO** (todo el ancho de la banda) y **DELGADO** (44 px, contra los 58 de
           los otros dos). Va debajo de «Crear tienda» y «Ver tiendas cerca», con la «f» de Facebook
           delante. El azul es `#0866ff`: el ACTUAL de Facebook y el mismo del Explorer (el jefe dijo que
           el `#1877f2` viejo se veía *«muy claro»*, ver `assets/css/explorer.css`). */
        .hero__explorer{display:flex;align-items:center;justify-content:center;gap:8px;
            width:100%;margin-top:10px;min-height:44px;padding:8px 14px;border-radius:12px;
            background:linear-gradient(180deg,#1a7cff 0%,#0866ff 55%,#0757d4 100%);color:#fff;
            font-size:16px;font-weight:700;text-decoration:none;line-height:1.15;letter-spacing:.01em;
            box-shadow:0 3px 10px rgba(3,20,60,.35),inset 0 1px 0 rgba(255,255,255,.35);
            transition:filter .12s ease,transform .12s ease}
        .hero__explorer:hover{filter:brightness(1.07)}
        .hero__explorer:active{transform:translateY(1px) scale(.997)}
        .hero__explorer-f{flex:0 0 auto;width:21px;height:21px}
        .hero__explorer-f svg{display:block;width:100%;height:100%}
        /* El rótulo va SIEMPRE en una sola línea (si envolviera, el botón crecería y el jefe lo quiere
           delgado). Medido: «Descubrir Nuevas Tiendas» en 16 px negrita + el icono ocupan ~235 px, y en
           el celular más angosto (320 px) hay ~296 px de ancho útil: entra con aire. */
        .hero__explorer,.hero__explorer-t{white-space:nowrap}
        /* 📱 CELULAR — «Crear tienda» CHICO y «Ver tiendas cerca» ANCHO, los dos en una sola fila
           (2026-09-16, orden del jefe, textual: *«el botón crear tienda hazlo más pequeño y la imagen
           ver tiendas cerca ponlo tal cual como la versión escritorio, más ancha»*).
           · **Crear tienda:** se encoge a lo que mide su texto (es el botón secundario).
           · **Ver tiendas cerca:** se lleva TODO el resto del ancho y recupera las medidas de
             escritorio (pin de 19 px, subtítulo de 12,5 px), así que su texto entra en **UNA línea**.
           ⚠️ Los dos siguen con texto de **16 px** (Regla de Oro n.º 1 del jefe: fuentes ≥16 px) y el
           contenedor los estira a la misma altura. La banda llega a los dos bordes de la pantalla. */
        @media (max-width: 640px){
            .hero--compacto{width:100vw;margin-left:calc(50% - 50vw);border-radius:0;
                border-left:0;border-right:0;padding:14px 12px 16px}
            .hero__ctas{flex-wrap:nowrap;gap:8px}
            /* el pequeño: mide justo su texto */
            .hero__cta{flex:0 1 auto;min-width:0;padding:10px 9px;font-size:16px}
            /* el ancho: se come lo que sobra y va como en escritorio */
            .hero__cta--geo{flex:1 1 auto;padding:10px 8px}
            .hero__cta-t{font-size:16px;gap:6px;line-height:1.15}
            .hero__cta-pin{width:19px;height:19px}
            .hero__cta-s{font-size:12.5px}
        }
    </style>
</section>

<?php // 📍 EL HUECO DE «VER TIENDAS CERCA» (lo rellena por JavaScript el módulo de geolocalización
      // cuando el visitante comparte su ubicación: guía GUIA_CERCA_DE_MI_Y_GEOBUSQUEDA.md).
      // ⚠️ Va JUSTO DEBAJO DE LA BANDA DE LOS TRES BOTONES, donde están «Crear tienda» y «Ver tiendas
      // cerca» (y muy por encima de donde estaba antes, que iba detrás de la rejilla al azar): al tocar
      // «Ver tiendas cerca» los resultados salen ahí mismo, a la vista, sin tener que bajar.
      // NO quitar este div: sin él el botón no pinta nada. Y si la banda se mueve de sitio otra vez,
      // **este hueco se mueve con ella** (el JS baja hasta aquí al pintar los resultados).
      // ⚠️ Va VACÍO hasta que el visitante toca el botón: no ocupa espacio, así que lo que venga debajo
      // (hoy: los banners) queda pegado a los botones. ?>
<div id="cercaIndex"></div>

<?php // 🔒 SE CIERRA EL BÚFER DE LA BANDA (2026-09-19): de aquí sale el HTML completo —la banda + el hueco
      // de «Ver tiendas cerca»— guardado en `$bloque_cta_portada`. **No se imprime aquí**: se imprime
      // dentro del ciclo 1 de la portada, después de «Nuevos ingresos» y antes de los banners. ?>
<?php $bloque_cta_portada = ob_get_clean(); ?>

<?php // ================== 🔁 LA PORTADA, AHORA EN CICLOS ==================
      // Pedido del jefe (2026-09-15): *«luego vuelve a repetir nuevamente todo empezando desde los
      // destacados y volviendo a repetir así hasta un máximo de cinco o seis veces, pero usando efecto
      // de loading o efecto de carga para que el sitio web no se vea muy pesado.»*
      //
      // Un CICLO es la vuelta completa: tira de historias → banners → rejilla al azar → banners →
      // línea separadora → productos → negocios para ti → empleos → banners → más vistos.
      // (En el ciclo 1, y SOLO en él, se cuelan dos bloques más justo debajo de las historias:
      //  «Nuevos ingresos» y la banda de los tres botones — ver la llamada de más abajo.)
      // El ciclo 1 se pinta aquí (para que Google lo vea y salga al instante) y los ciclos siguientes
      // (hoy el 2, el 3 y el 4) los trae `api/portada_ciclo.php` cuando el visitante se acerca al final,
      // con el aviso «Cargando más…». TODO el marcado de los bloques vive en `includes/portada_ciclos.php`.
      // ============================================================================== ?>
<?php require_once __DIR__ . '/includes/portada_ciclos.php'; ?>

<?php // 📍 (El hueco `#cercaIndex` ya NO va aquí: se mudó con la banda de los tres botones —2026-09-19—
      // y ahora sale dentro del ciclo 1, justo debajo de ella. Ver el comentario de arriba.) ?>

<?php // 👀 «NUEVOS INGRESOS» (antes «⚡ Ofertas de última hora») + 🎯 LA BANDA DE LOS TRES BOTONES
      // ============================================================================
      // El 2.º argumento de `portada_ciclo_html()` es lo que se cuela **justo debajo de la tira de
      // historias**, y ahí van DOS cosas pegadas y en este orden (2026-09-19):
      //     1) `#czVistos` → `ofertas_bloque_html()`: la línea separadora, la etiqueta «Nuevos ingresos»
      //        y los **12 productos** (6 y 6). ⚡ Lo pinta el SERVIDOR: antes lo llenaba el JavaScript con
      //        lo que el visitante ya había mirado —si había mirado 3, se veían 3— y ahora se ven 12
      //        siempre, con lo ya mirado delante.
      //     2) `$bloque_cta_portada` → **la banda de los tres botones** (Crear tienda · Ver tiendas cerca ·
      //        Descubrir Nuevas Tiendas) **con su hueco `#cercaIndex`**: es la banda que el jefe mandó
      //        bajar dos secciones, así que queda **exactamente encima de los banners**.
      //
      // 🔴 TRAMPA VIVIDA (2026-09-16): TODO este comentario tiene que ir DENTRO del bloque de PHP.
      //    Si se escribe DESPUÉS de cerrarlo, PHP lo manda tal cual al navegador y el visitante ve
      //    **el código escrito en la página** (pasó: 212 px de comentario a la vista entre los dos
      //    botones y las historias). Lo destapó la revisión con el navegador.
      //    ⚠️ Y OJO: NO se puede escribir la etiqueta de cierre de PHP ni siquiera DENTRO de un
      //    comentario —PHP cierra el bloque igual— ni la de apertura. Si hay que nombrarlas, se
      //    escriben como «la etiqueta de apertura/cierre». ?>
<?= portada_ciclo_html(
        1,
        '<div id="czVistos">' . ofertas_bloque_html() . '</div>'   // 1) «Nuevos ingresos» (12 productos)
        . $bloque_cta_portada                                      // 2) ⬅️ la banda de los tres botones
    ) ?>

<?php // 🌀 EL CARGADOR DE CICLOS (2..3): el aviso «Cargando más…» y el vigilante del final. ?>
<?= portada_cargador_html() ?>

<?php // ====== CIERRE DE LA PORTADA: CUATRO BANNERS APILADOS (uno debajo de otro) ======
      // Pedido del jefe (2026-09-10): "al final se cierra con tres o cuatro banners uno encima de
      // otro". La clase `banners-fila--uno` los deja en UNA sola columna (también en escritorio). ?>
<?= banners_fila_html(4, null, 'banners-fila--uno banners-fila--compacta') ?>

<style>
/* ====== VER NEGOCIOS CERCA (portada) ====== */
.cerca-bloque{margin:0 0 10px}
.cerca-chips{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin:0 0 12px}
.cerca-chip{border:1.5px solid var(--color-borde);background:#fff;color:var(--color-texto);border-radius:999px;
  padding:8px 14px;font-weight:700;font-size:13px;cursor:pointer;font-family:inherit}
.cerca-chip.is-on{background:var(--marca-granate);border-color:var(--marca-granate);color:#fff}
.card-negocio__dist{margin-top:6px;align-self:flex-start;display:inline-block;background:#ffedd5;color:#9a3412;
  border-radius:999px;padding:3px 10px;font-size:12px;font-weight:800}
.cerca-aviso{background:#fff;border:1px solid var(--color-borde);border-left:5px solid #f0861c;
  border-radius:12px;box-shadow:var(--sombra-tarjeta);padding:14px}
.cerca-aviso__t{font-weight:800;font-size:16px;margin:0 0 4px}
.cerca-aviso__p{font-size:14px;color:var(--color-texto-claro);margin:0 0 12px}
.cerca-acciones{display:flex;gap:8px;flex-wrap:wrap}
.cerca-btn{display:inline-flex;flex-direction:column;align-items:center;justify-content:center;gap:1px;border:0;
  border-radius:999px;padding:11px 18px;font-family:inherit;font-weight:800;font-size:15px;cursor:pointer;
  text-decoration:none;line-height:1.15;text-align:center}
/* 🎨 Los botones de "cerca" van en NARANJA (2026-09-14): antes eran verdes y se confundían
   con los de WhatsApp. Misma paleta que includes/btn_cerca.php (guía §6.4). */
.cerca-btn--cerca{background:linear-gradient(105deg,#a3123c 0%,#d0312a 48%,#f0861c 100%);color:#fff;
  box-shadow:0 0 0 2px #fbd7a4,inset 0 2px 0 rgba(255,255,255,.40)}
.cerca-btn--gris{background:#f1f1f1;color:var(--color-texto)}
.cerca-btn--linea{background:#fff;border:1.5px solid var(--color-borde);color:var(--color-texto)}
.cerca-btn__t{font-size:16px;font-weight:800}
.cerca-btn__s{font-size:12.5px;font-weight:500;opacity:.92}
/* pop-up de carga (máx. 4 segundos) */
.cerca-overlay{position:fixed;inset:0;background:rgba(23,23,23,.55);z-index:3000;display:flex;
  align-items:center;justify-content:center;padding:20px}
.cerca-overlay[hidden]{display:none}
.cerca-overlay__caja{background:#fff;border-radius:16px;padding:22px 20px 20px;max-width:320px;width:100%;
  text-align:center;box-shadow:0 12px 40px rgba(0,0,0,.3)}
.cerca-spin{width:44px;height:44px;margin:0 auto 12px;border:4px solid #ececec;border-top-color:#f0861c;
  border-radius:50%;animation:cercaSpin .8s linear infinite}
@keyframes cercaSpin{to{transform:rotate(360deg)}}
.cerca-overlay__t{font-weight:800;font-size:17px;margin:0 0 4px}
.cerca-overlay__p{font-size:13px;color:var(--color-texto-claro);margin:0}
.cerca-barra{height:5px;background:#eee;border-radius:999px;overflow:hidden;margin:14px 0 0}
.cerca-barra i{display:block;height:100%;width:0;background:#f0861c;border-radius:999px;
  animation:cercaBarra 4s linear forwards}
@keyframes cercaBarra{to{width:100%}}
/* alerta de PRIMERA VISITA (solo móvil) */
.geo-alerta{position:fixed;left:10px;right:10px;bottom:72px;z-index:2500;background:#fff;border-radius:16px;
  border:1px solid var(--color-borde);box-shadow:0 10px 34px rgba(0,0,0,.32);padding:14px;display:flex;
  flex-direction:column;gap:8px;animation:geoSube .35s ease-out}
.geo-alerta[hidden]{display:none}
@keyframes geoSube{from{transform:translateY(18px);opacity:0}to{transform:none;opacity:1}}
.geo-alerta__x{position:absolute;top:2px;right:4px;background:transparent;border:0;font-size:20px;line-height:1;
  color:#8a8a8a;cursor:pointer;padding:8px}
.geo-alerta__t{font-weight:800;font-size:17px;margin:0;padding-right:28px}
.geo-alerta__p{font-size:13px;color:var(--color-texto-claro);margin:0}
.geo-alerta__btn{background:linear-gradient(105deg,#a3123c 0%,#d0312a 48%,#f0861c 100%);color:#fff;border:0;
  border-radius:14px;padding:13px 16px;
  box-shadow:0 0 0 3px #fbd7a4,0 0 16px rgba(240,134,28,.45),inset 0 2px 0 rgba(255,255,255,.42),
    inset 0 -3px 0 rgba(90,6,20,.30);
  font-family:inherit;cursor:pointer;display:flex;flex-direction:column;align-items:center;gap:1px;width:100%}
.geo-alerta__btn:active{transform:scale(.98)}
.geo-alerta__btn-t{font-size:18px;font-weight:800;color:#fff;text-shadow:0 2px 3px rgba(90,6,20,.45)}
.geo-alerta__btn-s{font-size:12.5px;font-weight:500;color:rgba(255,255,255,.95)}
</style>

<!-- ====== POP-UP DE CARGA (no dura más de 4 segundos) ====== -->
<div class="cerca-overlay" id="cercaOverlay" hidden>
  <div class="cerca-overlay__caja" role="status" aria-live="polite">
    <div class="cerca-spin" aria-hidden="true"></div>
    <p class="cerca-overlay__t">Buscando negocios cerca de ti 📍</p>
    <p class="cerca-overlay__p" id="cercaOverlayTxt">Usando tu ubicación…</p>
    <div class="cerca-barra"><i></i></div>
  </div>
</div>

<!-- ====== ALERTA DE PRIMERA VISITA (solo celular) ====== -->
<div class="geo-alerta" id="geoAlerta" hidden>
  <button type="button" class="geo-alerta__x" id="geoAlertaX" aria-label="Cerrar">✕</button>
  <p class="geo-alerta__t">Mira lo que hay cerca de ti</p>
  <p class="geo-alerta__p">Restaurantes, bodegas, farmacias y más, a la vuelta de tu casa. Es gratis y no guardamos tu posición.</p>
  <button type="button" class="geo-alerta__btn" id="geoAlertaBtn">
    <span class="geo-alerta__btn-t">Ver negocios cerca</span>
    <span class="geo-alerta__btn-s">comparte tu ubicación</span>
  </button>
</div>

<script>
(function(){
  'use strict';

  var API    = '<?= url('api/cerca_de_mi.php') ?>';
  var BUSCAR = '<?= url('buscar.php') ?>';
  var SIN_FOTO = SITE_URL + '/assets/img/sin-foto.svg';
  var CENTRO = { lat: -9.0745, lng: -78.5936 };   // centro de Chimbote (plan B sin GPS)
  var CAP    = 4000;                              // tope DURO del pop-up de carga (4 s)
  var LS     = 'chimbote_cerca_v1';

  var caja    = document.getElementById('cercaIndex');
  var btnHero = document.getElementById('btnHeroCerca');
  var aviso   = document.getElementById('heroGeoAviso');
  var overlay = document.getElementById('cercaOverlay');
  var ovTxt   = document.getElementById('cercaOverlayTxt');
  var alerta  = document.getElementById('geoAlerta');

  var geo = { lat: null, lng: null, radio: 2, ocupado: false };
  var yaInteractuo = false;   // el visitante ya tocó "Ver negocios cerca"

  // ---------------- utilidades ----------------
  function esc(s){
    return String(s === null || s === undefined ? '' : s)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
  }
  function img(r){
    if (!r) return SIN_FOTO;
    return /^https?:\/\//.test(r) ? r : SITE_URL + '/' + String(r).replace(/^\/+/, '');
  }
  /* srcset para MÓVIL: el servidor manda las versiones que existen de verdad
     (300/800/1600 px). Si la foto todavía no tiene versiones, no se pone nada. */
  var SIZES_CARD_NEG = '(max-width: 640px) 92vw, 300px';
  var SIZES_CARD_PROD = '(max-width: 560px) 46vw, (max-width: 900px) 32vw, 240px';
  function srcsetAttr(elemento, sizes){
    if (!elemento || !elemento.srcset) return '';
    return ' srcset="' + esc(elemento.srcset) + '" sizes="' + sizes + '"';
  }
  function negUrl(slug){ return SITE_URL + '/neg/' + encodeURIComponent(slug); }
  function precio(n){ return 'S/ ' + Number(n || 0).toFixed(2); }
  function distTxt(m){
    m = Number(m || 0);
    if (m < 950) return 'a ' + Math.round(m) + ' m';
    var km = m / 1000, t;
    if (km < 10) { t = String(Math.round(km * 10) / 10).replace('.', ','); }
    else { t = String(Math.round(km)); }
    return 'a ' + t + ' km';
  }
  function closestDe(el, sel){
    while (el && el.nodeType === 1) {
      if (el.matches && el.matches(sel)) return el;
      el = el.parentNode;
    }
    return null;
  }
  function nota(txt){
    if (!aviso) return;
    if (txt) { aviso.hidden = false; aviso.textContent = txt; }
    else { aviso.hidden = true; aviso.textContent = ''; }
  }
  function cargando(txt){
    if (ovTxt) ovTxt.textContent = txt || 'Usando tu ubicación…';
    if (overlay) overlay.hidden = false;
    nota('Buscando negocios cerca de ti…');
  }
  function finCarga(){ if (overlay) overlay.hidden = true; nota(''); }
  function verLS(){ try { return localStorage.getItem(LS) === '1'; } catch(e){ return false; } }
  function marcarLS(){ try { localStorage.setItem(LS, '1'); } catch(e){} }
  function esMovil(){
    var ua = navigator.userAgent || '';
    if (/Android|iPhone|iPod|iPad|Opera Mini|IEMobile|Windows Phone|Mobile/i.test(ua)) return true;
    return ('ontouchstart' in window) && window.innerWidth <= 820;
  }
  function bajar(el){
    if (!el) return;
    try { el.scrollIntoView({behavior:'smooth', block:'start'}); }
    catch(e){ try { el.scrollIntoView(); } catch(e2){} }
  }

  // ---------------- pedir datos al sitio (con presupuesto de tiempo) ----------------
  function pedirJSON(lat, lng, radio, presupuesto, ok, mal){
    var ctrl = ('AbortController' in window) ? new AbortController() : null;
    var cortado = false, hecho = false;
    var t = setTimeout(function(){
      cortado = true;
      if (ctrl) { try { ctrl.abort(); } catch(e){} }
    }, Math.max(600, presupuesto));
    var url = API + '?lat=' + lat.toFixed(7) + '&lng=' + lng.toFixed(7)
            + '&radio=' + radio + '&neg=8&prod=8';
    fetch(url, ctrl ? {signal: ctrl.signal} : {})
      .then(function(r){ return r.json(); })
      .then(function(d){
        if (hecho) return; hecho = true; clearTimeout(t);
        if (d && d.ok === false) { mal(); return; }
        ok(d || {});
      })
      .catch(function(){
        if (hecho) return; hecho = true; clearTimeout(t);
        mal(cortado);
      });
  }

  // ---------------- tarjetas ----------------
  function cardNeg(n){
    var h = '<a href="' + negUrl(n.slug) + '" class="card-negocio">';
    h += '<div class="card-negocio__imagen"><img src="' + esc(img(n.imagen_portada)) + '"' + srcsetAttr(n, SIZES_CARD_NEG) + ' alt="' + esc(n.nombre) + '" loading="lazy" onerror="this.src=\'' + SIN_FOTO + '\'">';
    if (n.categoria_icono) h += '<span class="card-negocio__badge">' + esc(n.categoria_icono) + ' ' + esc(n.categoria_nombre || '') + '</span>';
    h += '</div><div class="card-negocio__body">';
    h += '<h3 class="card-negocio__titulo">' + esc(n.nombre) + '</h3>';
    if (n.distrito_nombre) h += '<div class="card-negocio__categoria">📍 ' + esc(n.distrito_nombre) + '</div>';
    h += '<span class="card-negocio__dist">⚡ ' + esc(distTxt(n.distancia_m)) + '</span>';
    h += '<div class="card-negocio__meta">';
    if (Number(n.rating) > 0) h += '<span class="card-negocio__rating">★ ' + Number(n.rating).toFixed(1) + '</span>';
    h += '</div></div></a>';
    return h;
  }
  function cardProd(p){
    var h = '<a href="' + negUrl(p.negocio_slug) + '" class="card-producto">';
    h += '<div class="card-producto__img"><img src="' + esc(img(p.imagen || p.foto)) + '"' + srcsetAttr(p, SIZES_CARD_PROD) + ' alt="' + esc(p.titulo) + '" loading="lazy" onerror="this.src=\'' + SIN_FOTO + '\'">';
    if (Number(p.destacado) > 0) h += '<span class="tag-top">★ Destacado</span>';
    h += '</div><div class="card-producto__body">';
    h += '<div class="card-producto__titulo">' + esc(p.titulo) + '</div>';
    h += '<div class="card-producto__negocio">' + esc(p.categoria_icono || '') + ' ' + esc(p.negocio_nombre) + '</div>';
    h += '<div class="card-producto__precio">' + precio(p.precio) + (p.unidad ? ' <span class="uni">/ ' + esc(p.unidad) + '</span>' : '') + '</div>';
    if (p.distancia_m !== undefined && p.distancia_m !== null) {
      h += '<span class="card-negocio__dist" style="font-size:11px">⚡ ' + esc(distTxt(p.distancia_m)) + '</span>';
    }
    h += '</div></a>';
    return h;
  }
  function chipsHtml(){
    var h = '<div class="cerca-chips"><span style="font-size:13px;font-weight:700;color:var(--color-texto-claro)">Radio:</span>';
    [2,5,10].forEach(function(v){
      h += '<button type="button" class="cerca-chip' + (geo.radio === v ? ' is-on' : '') + '" data-radio="' + v + '">' + v + ' km</button>';
    });
    h += '<button type="button" class="cerca-chip" data-cerca-quitar="1">✕ Quitar</button></div>';
    return h;
  }
  function urlBuscarCerca(radio){
    return BUSCAR + '?lat=' + geo.lat.toFixed(7) + '&lng=' + geo.lng.toFixed(7) + '&radio=' + radio;
  }

  // ---------------- pintar resultados ----------------
  function pintar(d){
    if (!caja) return;
    var negs  = (d && d.negocios)  ? d.negocios  : [];
    var prods = (d && d.productos) ? d.productos : [];
    if (!negs.length && !prods.length) { pintaAviso('vacio'); return; }

    var h = '<section class="seccion cerca-bloque">';
    h += '<div class="seccion__header"><div>';
    h += '<h2 class="seccion__titulo">📍 Negocios cerca de ti</h2>';
    h += '<small style="color:var(--color-texto-claro)">Del más cercano al más lejano · a menos de ' + geo.radio + ' km de donde estás</small>';
    h += '</div><a class="seccion__ver-todas" href="' + urlBuscarCerca(geo.radio) + '">Ver todos →</a></div>';
    h += chipsHtml();
    if (negs.length) {
      h += '<div class="grid-negocios">' + negs.map(cardNeg).join('') + '</div>';
    } else {
      h += '<p class="cerca-aviso__p">No hay tiendas con ubicación a menos de ' + geo.radio + ' km.</p>';
    }
    h += '</section>';

    if (prods.length) {
      h += '<section class="seccion cerca-bloque">';
      h += '<div class="seccion__header"><div>';
      h += '<h2 class="seccion__titulo">🛍️ Productos en tiendas cerca de ti</h2>';
      h += '<small style="color:var(--color-texto-claro)">Lo que venden los negocios que están a tu alrededor</small>';
      h += '</div><a class="seccion__ver-todas" href="' + urlBuscarCerca(geo.radio) + '">Ver más →</a></div>';
      h += '<div class="grid-productos">' + prods.map(cardProd).join('') + '</div>';
      h += '</section>';
    }

    caja.innerHTML = h;
    caja.hidden = false;
    bajar(caja);
  }

  function avisoHtml(motivo){
    var t = '', p = '';
    if (motivo === 'permiso') {
      t = 'No compartiste tu ubicación';
      p = 'No hay problema: abajo sigues viendo lo más buscado de Chimbote y puedes escribir lo que necesitas en el buscador. Si cambias de opinión, toca “Ver negocios cerca”.';
    } else if (motivo === 'gps') {
      t = 'No pudimos leer tu GPS';
      p = 'Revisa que la ubicación (GPS) esté activada en tu celular y vuelve a intentarlo. Mientras tanto, aquí tienes lo más buscado de Chimbote.';
    } else if (motivo === 'tarde') {
      t = 'Tu ubicación está tardando';
      p = 'El GPS no respondió a tiempo (máximo 4 segundos). Puedes reintentar o seguir con lo más buscado de Chimbote.';
    } else if (motivo === 'datos') {
      t = 'No pudimos cargar los negocios cercanos';
      p = 'Hubo un problema al leer las tiendas que están cerca de ti. Reintenta o usa el buscador por texto.';
    } else if (motivo === 'vacio') {
      t = 'No hay negocios registrados tan cerca';
      p = 'Prueba con un radio mayor o busca por texto: cada vez más tiendas de Chimbote se registran con su ubicación.';
    } else {
      t = 'Tu navegador no comparte la ubicación';
      p = 'Escribe lo que buscas o elige un distrito; igual verás lo mejor de Chimbote.';
    }
    var h = '<section class="seccion cerca-bloque"><div class="cerca-aviso">';
    h += '<p class="cerca-aviso__t">📍 ' + t + '</p><p class="cerca-aviso__p">' + p + '</p>';
    h += '<div class="cerca-acciones">';
    if (motivo === 'datos') {
      h += '<button type="button" class="cerca-btn cerca-btn--cerca" id="cercaReintentar"><span class="cerca-btn__t">Reintentar</span></button>';
    } else {
      h += '<button type="button" class="cerca-btn cerca-btn--cerca" id="cercaReintentar">'
         + '<span class="cerca-btn__t">Ver negocios cerca</span>'
         + '<span class="cerca-btn__s">comparte tu ubicación</span></button>';
    }
    if (motivo === 'vacio') {
      h += '<button type="button" class="cerca-btn cerca-btn--linea" data-radio-ir="10">Probar con 10 km</button>';
    } else {
      h += '<a class="cerca-btn cerca-btn--linea" href="' + BUSCAR + '?lat=' + CENTRO.lat + '&lng=' + CENTRO.lng + '&radio=2">Ver el centro de Chimbote</a>';
    }
    h += '<a class="cerca-btn cerca-btn--gris" href="' + BUSCAR + '">Buscar por texto</a>';
    h += '<a class="cerca-btn cerca-btn--gris" href="#populares">Ver lo más buscado 🔥</a>';
    h += '</div></div></section>';
    return h;
  }

  function pintaAviso(motivo){
    if (!caja) return;
    caja.innerHTML = avisoHtml(motivo);
    caja.hidden = false;
    bajar(caja);
  }

  // ---------------- flujo principal (con tope de 4 segundos) ----------------
  function flujoCerca(){
    if (geo.ocupado) return;
    yaInteractuo = true;
    marcarLS();
    if (alerta) alerta.hidden = true;

    if (!('geolocation' in navigator)) { pintaAviso('sin_soporte'); return; }

    geo.ocupado = true;
    if (btnHero) btnHero.classList.add('is-busy');
    cargando('Usando tu ubicación…');

    var t0 = Date.now();
    var cerrado = false;
    function cerrarPop(){
      if (cerrado) return false;
      cerrado = true;
      finCarga();
      if (btnHero) btnHero.classList.remove('is-busy');
      return true;
    }
    var tope = setTimeout(function(){
      if (!cerrarPop()) return;
      geo.ocupado = false;
      pintaAviso('tarde');
    }, CAP);

    navigator.geolocation.getCurrentPosition(function(pos){
      if (cerrado) return;
      geo.lat = pos.coords.latitude;
      geo.lng = pos.coords.longitude;

      pedirJSON(geo.lat, geo.lng, geo.radio, CAP - (Date.now() - t0), function(d){
        var negs  = (d && d.negocios)  ? d.negocios  : [];
        var prods = (d && d.productos) ? d.productos : [];
        // A 2 km no hay nada publicado y todavía sobra tiempo: ampliamos solos a 10 km.
        if (!negs.length && !prods.length && geo.radio === 2 && (Date.now() - t0) < 2200 && !cerrado) {
          if (ovTxt) ovTxt.textContent = 'Ampliando la búsqueda a 10 km…';
          geo.radio = 10;
          pedirJSON(geo.lat, geo.lng, 10, CAP - (Date.now() - t0), function(d2){
            if (!cerrarPop()) return;
            clearTimeout(tope);
            geo.ocupado = false;
            pintar(d2);
          }, function(){
            if (!cerrarPop()) return;
            clearTimeout(tope);
            geo.ocupado = false;
            pintaAviso('datos');
          });
          return;
        }
        if (!cerrarPop()) return;
        clearTimeout(tope);
        geo.ocupado = false;
        pintar(d);
      }, function(){
        if (!cerrarPop()) return;
        clearTimeout(tope);
        geo.ocupado = false;
        pintaAviso('datos');
      });
    }, function(err){
      if (!cerrarPop()) return;
      clearTimeout(tope);
      geo.ocupado = false;
      if (err && err.code === 1)      pintaAviso('permiso');
      else if (err && err.code === 2) pintaAviso('gps');
      else                            pintaAviso('tarde');
    }, {enableHighAccuracy: true, timeout: CAP - 600, maximumAge: 60000});
  }

  // Cambiar de radio (2 / 5 / 10 km) con la ubicación ya conocida.
  function cambiarRadio(r){
    if (!geo.lat || !geo.lng) { flujoCerca(); return; }
    geo.radio = r;
    Array.prototype.forEach.call(document.querySelectorAll('.cerca-chip[data-radio]'), function(c){
      c.classList.toggle('is-on', String(r) === c.getAttribute('data-radio'));
    });
    cargando('Buscando a menos de ' + r + ' km…');
    pedirJSON(geo.lat, geo.lng, r, 3500, function(d){
      finCarga();
      pintar(d);
    }, function(){
      finCarga();
      pintaAviso('datos');
    });
  }

  // ---------------- eventos ----------------
  if (btnHero) {
    btnHero.addEventListener('click', function(ev){
      ev.preventDefault();
      flujoCerca();
    });
  }

  if (caja) {
    caja.addEventListener('click', function(ev){
      var chip = closestDe(ev.target, '.cerca-chip');
      var reint = closestDe(ev.target, '#cercaReintentar');
      var ir = closestDe(ev.target, '[data-radio-ir]');
      if (chip) {
        if (chip.getAttribute('data-cerca-quitar')) {
          caja.innerHTML = '';
          caja.hidden = true;
          nota('');
          return;
        }
        var r = parseInt(chip.getAttribute('data-radio'), 10);
        if (r && r !== geo.radio) cambiarRadio(r);
        return;
      }
      if (ir) { cambiarRadio(parseInt(ir.getAttribute('data-radio-ir'), 10)); return; }
      if (reint) { flujoCerca(); }
    });
  }

  // Alerta de PRIMERA VISITA: solo en celular y solo una vez.
  if (alerta && esMovil() && !verLS()) {
    marcarLS();
    setTimeout(function(){
      if (yaInteractuo) return;                 // ya tocó el botón del hero
      if (overlay && !overlay.hidden) return;   // ya está buscando
      alerta.hidden = false;
    }, 1500);
    var x = document.getElementById('geoAlertaX');
    var b = document.getElementById('geoAlertaBtn');
    if (x) x.addEventListener('click', function(){ alerta.hidden = true; });
    if (b) b.addEventListener('click', function(){ alerta.hidden = true; flujoCerca(); });
  }
})();
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>
