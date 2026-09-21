<?php
/**
 * header.php — Cabecera común del sitio
 * ============================================================
 * Uso: en cada página pública:
 *   $titulo_pagina = 'Mi página';
 *   $descripcion_pagina = 'Meta description';
 *   include 'includes/header.php';
 */

if (!defined('SITE_URL')) {
    require_once __DIR__ . '/../config.php';
}

intentar_session_cookie();

// 📈 ESTADÍSTICAS PROPIAS (analytics sin depender de Google).
// Registra el pageview con una cookie anónima (cz_stats) y alimenta el panel
// Súper Admin → 📈 Estadísticas. Va aislado en try/catch: si el módulo falla o
// las tablas aún no existen, la página se muestra exactamente igual.
if (is_file(__DIR__ . '/estadisticas.php')) {
    require_once __DIR__ . '/estadisticas.php';
    try { stats_registrar_visita(); } catch (Throwable $e) { /* nunca romper la página */ }
}

$titulo_pagina = $titulo_pagina ?? SITE_NAME;
$descripcion_pagina = $descripcion_pagina ?? SITE_DESCRIPTION;

// 📣 EL MENSAJE DE LA TARJETA (2026-09-15, pedido del jefe: «algo comercial, con emoticones»):
// el título y el texto que se leen en WhatsApp son los que venden. La portada define su propio
// mensaje comercial en `$og_titulo` / `$og_descripcion` (y así el `<title>` de la pestaña y la
// meta description de Google siguen diciendo lo suyo, sin mezclarse con el copy de la tarjeta).
$og_titulo = trim((string)($og_titulo ?? '')) !== '' ? (string)$og_titulo : $titulo_pagina;
$og_descripcion = trim((string)($og_descripcion ?? '')) !== '' ? (string)$og_descripcion : $descripcion_pagina;

// 🖼️ LA IMAGEN AL COMPARTIR EL ENLACE (og:image) — 2026-09-15, pedido del jefe.
// Es la tarjeta que se ve cuando alguien pega un enlace del sitio en WhatsApp, Facebook o
// Telegram: el título, el texto y UNA IMAGEN. Así funciona:
//   · por defecto va NUESTRA imagen del sitio (assets/img/og-dechimbote.jpg, **1200 × 1200**,
//     cuadrada): la portada, los rubros/categorías, el buscador, las noticias, los empleos…
//     ⚠️ Es CUADRADA a propósito: la tarjeta chica de WhatsApp recorta al centro en cuadradito
//     y con una panorámica cortaba el logo (orden del jefe). Cuadrada entra siempre completa.
//   · la página que quiera otra imagen define `$og_imagen` (y si quiere `$og_imagen_alt`)
//     ANTES de incluir este header: la ficha de una tienda pone la CABECERA de esa tienda
//     y, si el enlace trae un producto (`?p=<id>`), la foto de ese producto.
//   ⚠️ El `?v=` de la imagen del sitio es para saltarse la caché de WhatsApp/Facebook cuando
//     se cambia el dibujo: si se rehace, hay que SUBIRLO.
$og_imagen = trim((string)($og_imagen ?? '')) !== '' ? (string)$og_imagen : url('assets/img/og-dechimbote.jpg') . '?v=2';
$og_imagen_alt = $og_imagen_alt ?? ($titulo_pagina . ' · ' . SITE_NAME);

// Formato y medida real de la imagen: los robots de WhatsApp/Facebook lo agradecen (con la
// medida saben cómo recortar la tarjeta). Se mira el archivo SOLO si es de nuestro hosting.
$og_imagen_tipo = preg_match('/\.jpe?g([?#]|$)/i', $og_imagen) ? 'image/jpeg' : 'image/webp';
$og_ancho = $og_alto = 0;
$og_ruta = preg_replace('/[?#].*$/', '', $og_imagen);
if (strpos($og_ruta, SITE_URL . '/') === 0) {
    $og_local = dirname(__DIR__) . '/' . ltrim(substr($og_ruta, strlen(SITE_URL)), '/');
    if (is_file($og_local)) {
        $og_info = @getimagesize($og_local);
        if ($og_info) { $og_ancho = (int)$og_info[0]; $og_alto = (int)$og_info[1]; }
    }
}

// 🔗 Canonical (2026-09-12): el sitio NO tenía ninguno y las páginas con filtros por URL
// (la página de empleos) se duplican solas ante Google. La página que quiera declararlo
// define `$canonical_url` antes de incluir este header; si no, no se pinta nada.
$canonical_url = $canonical_url ?? '';
$usuario = usuario_actual();
$categorias = $categorias ?? obtener_categorias();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0">
    <title><?= e($titulo_pagina) ?> · <?= e(SITE_NAME) ?></title>
    <meta name="description" content="<?= e($descripcion_pagina) ?>">
    <meta name="theme-color" content="#6d071a">

    <?php // 📈 GOOGLE ANALYTICS (GA4) — la etiqueta gtag.js que dio el jefe el 2026-09-20.
          // Va aquí, en el <head> de la cabecera COMÚN: así la llevan TODAS las páginas del sitio
          // (portada, rubros, fichas de tienda, noticias, empleos, buscador…). Es la etiqueta
          // oficial de Google tal cual, sin cambiarle una letra; el `async` deja que la página se
          // pinte sin esperar al script.
          // ⚠️ El ID «G-5L3BK3WLFB» es la propiedad de dechimbote.com: si algún día se cambia de
          // propiedad, se cambia SOLO aquí (una línea) y no hay más archivos que tocar.
          // Ojo: esto NO tiene nada que ver con las estadísticas propias del sitio
          // (includes/estadisticas.php, la cookie cz_stats y el panel 📈 Estadísticas): son dos
          // medidas distintas y las dos conviven sin pisarse. ?>
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=G-5L3BK3WLFB"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());

      gtag('config', 'G-5L3BK3WLFB');
    </script>

    <!-- Open Graph -->
    <meta property="og:title" content="<?= e($og_titulo) ?>">
    <meta property="og:description" content="<?= e($og_descripcion) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
    <meta property="og:locale" content="es_PE">
    <?php // 🖼️ La tarjeta del enlace (og:image). Va SIEMPRE: sin ella WhatsApp muestra el
          // enlace pelado. ?>
    <meta property="og:image" content="<?= e($og_imagen) ?>">
    <meta property="og:image:secure_url" content="<?= e($og_imagen) ?>">
    <meta property="og:image:type" content="<?= e($og_imagen_tipo) ?>">
    <?php if ($og_ancho > 0): ?>
        <meta property="og:image:width" content="<?= $og_ancho ?>">
        <meta property="og:image:height" content="<?= $og_alto ?>">
    <?php endif; ?>
    <meta property="og:image:alt" content="<?= e($og_imagen_alt) ?>">
    <?php // X (Twitter) no lee og:* para la tarjeta grande: quiere las suyas. ?>
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($og_titulo) ?>">
    <meta name="twitter:description" content="<?= e($og_descripcion) ?>">
    <meta name="twitter:image" content="<?= e($og_imagen) ?>">
    <?php if ($canonical_url !== ''): ?>
        <link rel="canonical" href="<?= e($canonical_url) ?>">
        <meta property="og:url" content="<?= e($canonical_url) ?>">
    <?php endif; ?>

    <!-- CSS -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= url('assets/css/base.css') ?>?v=6">
    <link rel="stylesheet" href="<?= url('assets/css/components.css') ?>?v=27">
    <link rel="stylesheet" href="<?= url('assets/css/plantilla-a.css') ?>?v=4">
    <link rel="stylesheet" href="<?= url('assets/css/plantilla-b.css') ?>?v=3">
    <link rel="stylesheet" href="<?= url('assets/css/plantilla-c.css') ?>?v=3">
    <link rel="stylesheet" href="<?= url('assets/css/banners-v2.css') ?>?v=4">
    <?php // 🛒 Carrito "Me interesa" por tienda (botón flotante, cajón del pedido,
          // ficha rápida del producto y fila "Vistos recientemente").
          // Motor: assets/js/carrito.js (se carga en includes/footer.php). ?>
    <link rel="stylesheet" href="<?= url('assets/css/carrito.css') ?>?v=6">
    <?php /* 🧩 CSS EXTRA de la página que lo pida (2026-09-18): la página lo deja en `$css_extra`
             ANTES de incluir este header. Lo estrena la ficha de tienda, que carga el CSS del
             Explorer para pintar sus productos como las tarjetas de Facebook del muro
             (todas las reglas de `explorer.css` van con el prefijo `exp-`: no pueden chocar). */ ?>
    <?= $css_extra ?? '' ?>

    <!-- Favicon: el símbolo de BÚSQUEDA (la lupa) en los colores de la marca. El .ico no existía
         (el navegador mostraba el icono en blanco) y lo dibuja __favicon_armar.py.
         ⚠️ El ?v= va a propósito: los navegadores se guardan el favicon en caché y sin la marca
         de versión el jefe seguiría viendo el icono viejo en su pestaña. -->
    <link rel="icon" href="<?= url('assets/img/favicon.ico') ?>?v=2" sizes="any">
    <link rel="icon" type="image/png" sizes="512x512" href="<?= url('assets/img/favicon.png') ?>?v=2">
    <link rel="apple-touch-icon" href="<?= url('assets/img/apple-touch-icon.png') ?>?v=2">

    <!-- 🧠 Buscador fuzzy (tolerante a errores de tipeo): librería local, no CDN -->
    <script src="<?= url('assets/js/fuse.min.js') ?>?v=7" defer></script>
    <script>
        window.SITE_URL = <?= json_encode(SITE_URL) ?>;
        window.CSRF_TOKEN = '<?= csrf_token() ?>';
    </script>
</head>
<body data-plantilla="A" data-color="granate">

<!-- ====== LA CABECERA: LA MARCA, EL BUSCADOR Y EL MENÚ ☰ ======
     · CELULAR (dos filas, como siempre): la banda de la marca (.marca-banda) lleva el logo y el
       ☰ Menú y NO es fija —se va sola con el scroll—; debajo, la cabecera fija (`header.topbar`)
       mantiene el buscador y la marquesina de rubros. Pedido del jefe (2026-09-10): al bajar,
       arriba solo deben quedar el buscador y los rubros.
     · PC (UNA SOLA FILA — orden del jefe, 2026-09-20): *«reduce el ancho del buscador en el modo
       PC… debe verse el menú hamburguesa y debe verse el logo al costado, ocupando los tres una
       sola fila»*. En escritorio la banda de arriba se apaga y el logo, el buscador (MÁS ANGOSTO:
       560 px) y el ☰ viven juntos, en esa fila, dentro de la cabecera fija.
     ⚠️ Por eso hay DOS copias del logo y DOS botones ☰: la de celular (banda) y la de PC (fila).
     Los dos botones llevan la clase **.js-menu-btn** — el script del final de este archivo maneja
     la lista completa (NO volver a `getElementById`: hay dos y solo tomaría el primero). -->
<div class="marca-banda">
    <div class="topbar__barra">
        <?php // 🖼️ LA MARCA ES EL LOGO (2026-09-15, orden del jefe): aquí decía el texto
              // "📍 Chimbote.xyz" y se anuló. Ahora va la imagen del logo (el pin con la tienda
              // + "de chimbote.com"), recortada del original y convertida a WebP BLANCO CON
              // TRANSPARENCIA: el fondo granate del recorte se volvió transparente, así entra
              // en esta banda (que es un degradado granate) sin que se note el rectángulo.
              // El archivo lo arma __logo_armar.py desde Downloads.
              // ⚠️ Esta copia es la del CELULAR: en PC se pinta la de la fila de abajo. ?>
        <a href="<?= url('') ?>" class="topbar__logo" title="Inicio de DeChimbote.com">
            <img src="<?= url('assets/img/logo-dechimbote.webp') ?>" alt="DeChimbote.com"
                 class="topbar__logo-img" width="593" height="67">
        </a>

        <button type="button" class="topbar__burger js-menu-btn"
                aria-label="Abrir el menú" aria-expanded="false" aria-controls="menuPrincipal">
            <span class="topbar__burger-lineas" aria-hidden="true">
                <span></span><span></span><span></span>
            </span>
            <span class="topbar__burger-txt">Menú</span>
        </button>
    </div>
</div>

<!-- ====== TOP BAR (FIJA/sticky): en PC la marca + el buscador + el ☰; en celular solo el buscador -->
<header class="topbar">
    <div class="topbar__inner">

        <?php // 🖥️ LA MARCA AL COSTADO DEL BUSCADOR (2026-09-20, orden del jefe). En celular NO se
              // pinta (ahí manda la de la banda de arriba): lo decide el CSS, con la clase --pc. ?>
        <a href="<?= url('') ?>" class="topbar__logo topbar__logo--pc" title="Inicio de DeChimbote.com">
            <img src="<?= url('assets/img/logo-dechimbote.webp') ?>" alt="DeChimbote.com"
                 class="topbar__logo-img" width="593" height="67">
        </a>

        <!-- Buscador. SIN lupa visible (el jefe la quitó el 2026-09-10: el que escribe usa ENTER y
             el que no quiere escribir, el micrófono). Va centrado entre el logo y el ☰, y en PC es
             MÁS ANGOSTO que antes (560 px en vez de 760): orden del jefe (2026-09-20).
             El botón de abajo va invisible solo para que ENTER siga enviando el formulario. -->
        <form action="<?= url('buscar.php') ?>" method="get" class="topbar__search" role="search">
            <input type="search" id="buscador-fuzzy" name="q" data-fuzzy placeholder="¿Qué buscas hoy? Escribe o habla" value="<?= e($_GET['q'] ?? '') ?>" aria-label="Buscar" autocomplete="off">
            <button type="submit" class="sr-solo" aria-label="Buscar" tabindex="-1">Buscar</button>
        </form>

        <?php // ☰ EL MENÚ, AL EXTREMO DERECHO DE LA FILA (2026-09-20). En celular no se pinta. ?>
        <button type="button" class="topbar__burger topbar__burger--pc js-menu-btn"
                aria-label="Abrir el menú" aria-expanded="false" aria-controls="menuPrincipal">
            <span class="topbar__burger-lineas" aria-hidden="true">
                <span></span><span></span><span></span>
            </span>
            <span class="topbar__burger-txt">Menú</span>
        </button>
    </div>

    <!-- Categorías en marquesina continua (scroll automático derecha → izquierda, orden aleatorio) -->
    <style>
        .topbar__cats{display:block;overflow:hidden;max-width:var(--max-ancho);margin:0 auto;padding:0 10px 8px}
        .topbar__cats-track{display:inline-flex;white-space:nowrap;width:max-content;will-change:transform;animation:catsScroll var(--cats-duracion,55s) linear infinite}
        .topbar__cats-track a{white-space:nowrap;margin-right:6px;padding:6px 12px;border-radius:999px;font-size:13px;font-weight:500;background:rgba(255,255,255,.1);color:#fff;text-decoration:none;border:1px solid #e6c37a}
        .topbar__cats-track a:hover{background:rgba(255,255,255,.2)}
        .topbar__cats-track a.is-active{background:var(--marca-crema);color:var(--marca-granate);font-weight:600}
        @keyframes catsScroll{from{transform:translateX(0)}to{transform:translateX(-50%)}}
    </style>
    <div class="topbar__cats">
        <div class="topbar__cats-track" style="--cats-duracion:<?= max(140, round(count($categorias)*3.5)) ?>s">
            <?php $catsTop = $categorias; shuffle($catsTop); ?>
            <?php for ($k = 0; $k < 2; $k++): ?>
                <?php foreach ($catsTop as $c): ?>
                    <a href="<?= url('categoria/' . $c['slug']) ?>" class="<?= (($_GET['cat'] ?? '') === $c['slug']) ? 'is-active' : '' ?>">
                        <?= $c['icono'] ?> <?= e($c['nombre']) ?>
                    </a>
                <?php endforeach; ?>
            <?php endfor; ?>
        </div>
    </div>

</header>

<?php // 🗑️ 2026-09-13 (orden del jefe): aquí vivía la BANDA DEL CHAT COMUNITARIO (el último
      // mensaje del chat público, que pintaba includes/chat_mini.php con chat_mini_fila_html()).
      // Se retiró el chat comunitario COMPLETO: su página, su API y su include ya no existen.
      // NO volver a pintar esa banda ni a enlazar ese chat. ?>

<!-- ====== MENÚ PRINCIPAL (hamburguesa) ======
     Pedido del jefe (2026-09-10): la barra ya no lleva botones con iconos — en móvil no cabían
     y "Salir" ni se veía. Aquí van TODAS las opciones con texto claro, separadas por grupos.
     El mismo menú sirve para el visitante sin cuenta y para el usuario logueado (cambia su grupo
     "Tu cuenta"). Lo abre/cierra el script del final del archivo. -->
<div class="menu-principal" id="menuPrincipal" hidden>
    <div class="menu-principal__fondo" data-menu-cerrar></div>

    <aside class="menu-principal__caja" role="dialog" aria-modal="true" aria-label="Menú de <?= e(SITE_NAME) ?>">
        <div class="menu-principal__head">
            <?php // Misma marca que la banda de arriba: el logo en imagen (antes "Chimbote.xyz"). ?>
            <a href="<?= url('') ?>" class="menu-principal__marca" title="Inicio de DeChimbote.com">
                <img src="<?= url('assets/img/logo-dechimbote.webp') ?>" alt="DeChimbote.com"
                     class="menu-principal__marca-img" width="593" height="67">
            </a>
            <button type="button" class="menu-principal__x" data-menu-cerrar aria-label="Cerrar el menú">✕</button>
        </div>

        <div class="menu-principal__cuerpo">

            <?php if ($usuario): ?>
                <p class="menu-principal__grupo">Tu cuenta</p>
                <a class="menu-principal__item" href="<?= url('panel.php') ?>">
                    <span class="menu-principal__ico">👤</span>
                    <span class="menu-principal__txt">
                        <span class="menu-principal__t">Mi panel, <?= e(explode(' ', $usuario['nombre'])[0]) ?></span>
                        <span class="menu-principal__s">Tus avisos, pedidos y búsquedas</span>
                    </span>
                </a>
                <?php if (es_dueno()): ?>
                    <a class="menu-principal__item" href="<?= url('panel.php') ?>">
                        <span class="menu-principal__ico">🏪</span>
                        <span class="menu-principal__txt">
                            <span class="menu-principal__t">Mis tiendas</span>
                            <span class="menu-principal__s">Edita tus datos, fotos y productos</span>
                        </span>
                    </a>
                <?php endif; ?>
                <?php if (es_admin()): ?>
                    <a class="menu-principal__item" href="<?= url('superadmin.php') ?>">
                        <span class="menu-principal__ico">🛡️</span>
                        <span class="menu-principal__txt">
                            <span class="menu-principal__t">Súper Admin</span>
                            <span class="menu-principal__s">Control total del sitio, avisos y monitoreo</span>
                        </span>
                    </a>
                <?php endif; ?>
                <a class="menu-principal__item" href="<?= url('logout.php') ?>">
                    <span class="menu-principal__ico">⏻</span>
                    <span class="menu-principal__txt">
                        <span class="menu-principal__t">Salir</span>
                        <span class="menu-principal__s">Cerrar mi sesión en este aparato</span>
                    </span>
                </a>
            <?php else: ?>
                <p class="menu-principal__grupo">Tu cuenta</p>
                <a class="menu-principal__item menu-principal__item--destacado" href="<?= url('registro.php') ?>">
                    <span class="menu-principal__ico">✍️</span>
                    <span class="menu-principal__txt">
                        <span class="menu-principal__t">Crear mi cuenta gratis</span>
                        <span class="menu-principal__s">Guarda tus favoritos y lo que buscas</span>
                    </span>
                </a>
                <a class="menu-principal__item" href="<?= url('login.php') ?>">
                    <span class="menu-principal__ico">👤</span>
                    <span class="menu-principal__txt">
                        <span class="menu-principal__t">Ingresar</span>
                        <span class="menu-principal__s">Ya tengo cuenta en DeChimbote.com</span>
                    </span>
                </a>
            <?php endif; ?>

            <p class="menu-principal__grupo">Tu negocio</p>
            <a class="menu-principal__item" href="<?= url('crear-tienda') ?>">
                <span class="menu-principal__ico">🛠️</span>
                <span class="menu-principal__txt">
                    <span class="menu-principal__t">Crear mi tienda</span>
                    <span class="menu-principal__s">Con Inteligencia Artificial: sube 8 fotos y ella la arma</span>
                </span>
            </a>
            <a class="menu-principal__item" href="<?= url('caminante/') ?>">
                <span class="menu-principal__ico">📸</span>
                <span class="menu-principal__txt">
                    <span class="menu-principal__t">Agregar tienda con la cámara</span>
                    <span class="menu-principal__s">Caminante: fotos en 2 minutos, sin complicarte</span>
                </span>
            </a>
            <?php // Este menú va al BUSCADOR de reclamos (/reclamar), no al formulario directo:
                  // sin slug el formulario no sabe qué negocio es y quedaba en un aviso de error.
                  // Desde /reclamar el dueño busca su tienda y de ahí pasa al formulario. ?>
            <a class="menu-principal__item" href="<?= url('reclamar') ?>">
                <span class="menu-principal__ico">🏪</span>
                <span class="menu-principal__txt">
                    <span class="menu-principal__t">Reclamar mi negocio</span>
                    <span class="menu-principal__s">Ya está publicado y es mío</span>
                </span>
            </a>

            <p class="menu-principal__grupo">Descubrir en Chimbote</p>
            <a class="menu-principal__item" href="<?= url('buscar.php') ?>">
                <span class="menu-principal__ico">🔎</span>
                <span class="menu-principal__txt">
                    <span class="menu-principal__t">Buscar negocios</span>
                    <span class="menu-principal__s">Por nombre, rubro o producto</span>
                </span>
            </a>
            <?php // 📰 NOTICIAS (pedido del jefe, 2026-09-13): el menú hamburguesa ofrece la sección
                  // de noticias locales del sitio. El titular es el enlace en todas partes. ?>
            <a class="menu-principal__item" href="<?= url('noticias') ?>">
                <span class="menu-principal__ico">📰</span>
                <span class="menu-principal__txt">
                    <span class="menu-principal__t">Noticias de Chimbote</span>
                    <span class="menu-principal__s">Lo último de la zona: hoy, ayer y antes de ayer</span>
                </span>
            </a>
            <?php // 🔴 INFORMACIÓN EN VIVO (módulo del 2026-09-15; el jefe lo renombró ese mismo día:
                  // antes «Encargos», antes «Pedidos sin vendedor»). Es el chat de lo que pasa ahora
                  // mismo en el sitio + lo que la gente busca y nadie vende. La palabra «tablón» sigue
                  // retirada del sitio: no usarla en ningún texto visible. ?>
            <a class="menu-principal__item" href="<?= url('en-vivo') ?>">
                <span class="menu-principal__ico">🔴</span>
                <span class="menu-principal__txt">
                    <span class="menu-principal__t">Información en vivo</span>
                    <span class="menu-principal__s">Lo que está pasando ahora en Chimbote: búsquedas, visitas y lo que nadie vende</span>
                </span>
            </a>
            <?php // 🗑️ 2026-09-13 (orden del jefe): aquí estaban DOS opciones de menú que ya NO
                  // existen — el CHAT COMUNITARIO público y, antes, la bandeja de mensajería
                  // entre tiendas (B2B), que ni siquiera estaba publicada. Todo eso se retiró
                  // del sitio: no volver a poner estos enlaces. ?>
            <a class="menu-principal__item" href="<?= url('trabaja_con_nosotros.php') ?>">
                <span class="menu-principal__ico">💼</span>
                <span class="menu-principal__txt">
                    <span class="menu-principal__t">Trabaja con nosotros</span>
                    <span class="menu-principal__s">Repartidores, vendedores y oficios</span>
                </span>
            </a>

            <?php // 🏪 NOSOTROS (3.ª orden del jefe, 2026-09-21 — LA QUE MANDA HOY): el área «Nosotros»
                  // son CUATRO PÁGINAS, cada una con su URL, y aquí van las cuatro puertas.
                  // ⛔ SIN ANCLAS: textual del jefe, *«no quiero que trabajes con anclas dentro del
                  // sitio… creo que sean páginas separadas, cada una con su área respectiva»* (antes
                  // esto eran tres enlaces a la misma página con `#`). Cada sección tiene, además, su
                  // **menú interno** para pasar de una a otra: `includes/nosotros_area.php`. ?>
            <p class="menu-principal__grupo">Nosotros</p>
            <a class="menu-principal__item" href="<?= url('nosotros') ?>">
                <span class="menu-principal__ico">🏪</span>
                <span class="menu-principal__txt">
                    <span class="menu-principal__t">Nosotros</span>
                    <span class="menu-principal__s">Qué es el sitio y qué ofrece, en corto</span>
                </span>
            </a>
            <a class="menu-principal__item" href="<?= url('como-se-usa') ?>">
                <span class="menu-principal__ico">📖</span>
                <span class="menu-principal__txt">
                    <span class="menu-principal__t">Cómo se usa este sitio</span>
                    <span class="menu-principal__s">La guía en 2 minutos: buscar, pedir y vender</span>
                </span>
            </a>
            <a class="menu-principal__item" href="<?= url('novedades') ?>">
                <span class="menu-principal__ico">✨</span>
                <span class="menu-principal__txt">
                    <span class="menu-principal__t">Novedades</span>
                    <span class="menu-principal__s">Lo que nos diferencia: IA, GPS, estadísticas en vivo y tus fotos cuando quieras</span>
                </span>
            </a>
            <a class="menu-principal__item menu-principal__item--destacado" href="<?= url('precios') ?>">
                <span class="menu-principal__ico">💰</span>
                <span class="menu-principal__txt">
                    <span class="menu-principal__t">Precios</span>
                    <span class="menu-principal__s">Nuestros 5 planes, desde gratis</span>
                </span>
            </a>

            <p class="menu-principal__grupo">Cerca de mí</p>
            <?php require_once __DIR__ . '/btn_cerca.php'; ?>
            <?= btn_tiendas_cerca_html('Negocios cerca de mí', 'toca y comparte tu ubicación') ?>

            <p class="menu-principal__grupo">Rubros de Chimbote</p>
            <div class="menu-principal__rubros">
                <?php foreach (array_slice($categorias, 0, 18) as $c): ?>
                    <a href="<?= url('categoria/' . $c['slug']) ?>"><?= $c['icono'] ?> <?= e($c['nombre']) ?></a>
                <?php endforeach; ?>
            </div>
            <?php // 🏷️ TODOS LOS RUBROS (2026-09-19, pedido del jefe: *«¿existe alguna página donde yo
                  // pueda ver los rubros?… una página donde se vean TODOS los rubros»*). Los 18 chips de
                  // arriba son solo un pedazo: esta opción abre la LISTA COMPLETA (`rubros.php`, o sea
                  // /rubros), en orden alfabético y con un campo que filtra al instante. ?>
            <a class="menu-principal__item" href="<?= url('rubros') ?>">
                <span class="menu-principal__ico">🏷️</span>
                <span class="menu-principal__txt">
                    <span class="menu-principal__t">Ver todos los rubros (<?= count($categorias) ?>)</span>
                    <span class="menu-principal__s">La lista completa de rubros del directorio, en orden alfabético</span>
                </span>
            </a>
        </div>

        <div class="menu-principal__pie">
            <?= e(SITE_NAME) ?> · El marketplace de Chimbote y la provincia del Santa, Áncash
        </div>
    </aside>
</div>

<script>
/* Abrir/cerrar el menú hamburguesa. Sin dependencias: se cierra con la ✕, tocando el fondo
   oscuro, con la tecla Escape y al tocar cualquier enlace del menú.
   ⚠️ HAY DOS BOTONES ☰ (el de la banda de la marca, que se ve en celular, y el de la fila del PC,
   al costado del buscador — 2026-09-20): por eso se trabaja con la LISTA `.js-menu-btn` y no con
   un id. Los dos abren el mismo menú y los dos se marcan como abiertos. */
(function () {
    'use strict';
    var btns = document.querySelectorAll('.js-menu-btn');
    var menu = document.getElementById('menuPrincipal');
    if (!btns.length || !menu) return;

    function marcar(abierto) {
        for (var i = 0; i < btns.length; i++) {
            btns[i].classList.toggle('is-abierto', abierto);
            btns[i].setAttribute('aria-expanded', abierto ? 'true' : 'false');
            btns[i].setAttribute('aria-label', abierto ? 'Cerrar el menú' : 'Abrir el menú');
        }
    }
    function abrir() {
        menu.hidden = false;
        document.documentElement.style.overflow = 'hidden';   // el fondo no se mueve
        marcar(true);
        var x = menu.querySelector('.menu-principal__x');
        if (x) x.focus({ preventScroll: true });
    }
    function cerrar() {
        menu.hidden = true;
        document.documentElement.style.overflow = '';
        marcar(false);
    }

    for (var i = 0; i < btns.length; i++) {
        btns[i].addEventListener('click', function () { menu.hidden ? abrir() : cerrar(); });
    }

    menu.addEventListener('click', function (ev) {
        var t = ev.target;
        while (t && t.nodeType === 1) {
            if (t.hasAttribute && t.hasAttribute('data-menu-cerrar')) { cerrar(); return; }
            // Al elegir una opción se cierra… salvo el botón de ubicación, que necesita
            // quedarse a la vista para avisar si el visitante no comparte su ubicación.
            if (t.tagName === 'A') {
                if (!(t.classList && t.classList.contains('btc-cerca__btn'))) cerrar();
                return;
            }
            t = t.parentNode;
        }
    });

    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && !menu.hidden) cerrar();
    });
})();
</script>

<?= mostrar_flash() ?>

<main class="main">
