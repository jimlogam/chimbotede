<?php
/**
 * crear_tienda_ia.php — 🛠️ «EL MAESTRO»: LA PÁGINA PARA ARMAR TU TIENDA
 * =====================================================================
 * URL amigable: /crear-tienda (regla en el `.htaccess`).
 * Para agregar un producto a una tienda que ya existe: /crear-tienda?modo=producto.
 *
 * 🖥️ ES UNA PÁGINA SOLA DE PANTALLA COMPLETA (decisión del jefe, 2026-09-15).
 * Antes esta página incluía `includes/header.php` y `includes/footer.php` y la barra de escribir
 * iba `position: fixed` sobre el fondo: al bajar, el PIE DEL SITIO se colaba dentro del área de
 * chat y el jefe lo vio (textual: *«se pudo ver en el área de llenado del módulo el footer de la
 * página, cosa que se supone que es una página; hazlo como página, una página normal»*).
 * Ahora **no se incluye ninguna cabecera ni pie del sitio**: la página es suya de arriba a abajo
 * (`100dvh`): cabecera propia con el asistente, la conversación en medio (con su propio scroll) y
 * la barra de escribir pegada abajo, dentro del mismo bloque. **Debajo de la barra no hay nada**,
 * así que ningún pie puede volver a aparecer ahí.
 *
 * ⚠️ Trampa de PHP que mordió al escribir este archivo: dentro de un comentario de bloque, la
 *    secuencia asterisco-barra CIERRA el comentario. Por eso aquí las rutas se escriben sin el
 *    asterisco doble de markdown.
 *
 * Guía: GUIA_CONSTRUCTOR_DE_TIENDAS.md
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/tienda_ia.php';

// Página privada: que Google no la guarde (no tiene nada útil sin sesión)
header('X-Robots-Tag: noindex, nofollow');

$modo     = (isset($_GET['modo']) && $_GET['modo'] === 'producto') ? 'producto' : 'nueva';
$usuario  = usuario_actual();
$logueado = !empty($usuario);

// 🛒🆕 EL PRODUCTO YA VIENE DICHO (pedido del jefe, 2026-09-16). El botón **«Agregar mi tienda»** de
// los resultados de búsqueda (`buscar.php`, debajo del botón «Ver … cerca de mí») llega aquí con el
// término que se buscó en `prod`: `/crear-tienda?modo=producto&prod=cumpleaños`. Con eso el asistente
// **no pregunta cómo se llama el producto**: nace con ese nombre y lo primero que pide son sus fotos
// («📷 Mándame la foto de cumpleaños») para dejarlo publicado en la tienda del dueño.
// Si el visitante entra a mano a `/crear-tienda` (sin `prod`), todo sigue igual que siempre.
$prod = '';
if ($modo === 'producto' && isset($_GET['prod'])) {
    // Se limpia con el mismo motor del asistente (nada de etiquetas ni de líneas raras): máx. 60 como
    // el nombre de un producto escrito por el dueño en el paso `producto_nombre`.
    $prod = tienda_ia_limpiar((string)$_GET['prod'], 60);
}
// La URL del modo «agregar un producto» conservando el producto ya dicho (la usan la puerta y el menú).
$url_modo_producto = 'crear-tienda?modo=producto' . ($prod !== '' ? '&prod=' . rawurlencode($prod) : '');

$titulo_pagina      = $modo === 'producto' ? 'Agrega un producto a tu tienda · El maestro' : 'Arma tu tienda · El maestro';
$descripcion_pagina = 'Cuéntame cómo es tu tienda y yo la armo contigo: nombre, fotos, tu WhatsApp y tus primeros productos.';

// 📈 La visita también cuenta para las estadísticas del sitio (es lo único que hacía `header.php`
// que de verdad nos sirve aquí). Va aislado: si el módulo falla, la página se muestra igual.
if (is_file(__DIR__ . '/includes/estadisticas.php')) {
    require_once __DIR__ . '/includes/estadisticas.php';
    try { stats_registrar_visita(); } catch (Throwable $e) { /* nunca romper la página */ }
}

/** El <head> de la página sola (sin las CSS ni los scripts del sitio: aquí no hacen falta). */
function tia_pagina_head($titulo, $descripcion) { ?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
  <title><?= e($titulo) ?> · <?= e(SITE_NAME) ?></title>
  <meta name="description" content="<?= e($descripcion) ?>">
  <meta name="robots" content="noindex, nofollow">
  <meta name="theme-color" content="#6d071a">
  <link rel="icon" href="<?= url('assets/img/favicon.ico') ?>">
  <link rel="stylesheet" href="<?= url('assets/css/tienda_ia.css') ?>?v=12">
</head>
<?php }

/** El pie común de la página sola (aquí NO hay pie del sitio: solo cierra el documento). */
function tia_pagina_scripts() { ?>
</body>
</html>
<?php }

/* ============================ LA PUERTA (solo si está cerrada en config) ============================ */
// Por defecto `TIENDA_IA_SOLO_REGISTRADOS` es `false`: el asistente crea la cuenta al publicar.
// Si el jefe la pone en `true`, esto vuelve a ser la pantalla de «crea tu cuenta / entra».
if (TIENDA_IA_SOLO_REGISTRADOS && !$logueado) {
    tia_pagina_head($titulo_pagina, $descripcion_pagina);
    ?>
    <body class="tia-body">
    <main class="tia-puerta">
      <div class="tia-puerta__caja">
        <div class="tia-puerta__emoji"><?= TIENDA_IA_EMOJI ?></div>
        <h1 class="tia-puerta__tit"><?= e(TIENDA_IA_NOMBRE) ?> te arma tu tienda</h1>
        <p class="tia-puerta__sub">
          Te pregunto una cosa a la vez —cómo se llama tu tienda, de qué trata, tus fotos— y al final
          <strong>tu tienda queda publicada</strong> con tu WhatsApp.
        </p>
        <ul class="tia-puerta__lista">
          <li>📷 <strong>Mándame 8 fotos de tu negocio</strong> (afuera, adentro, tus productos, tus máquinas, tu personal, tu tarjeta o tu folleto) y saco de ahí lo principal.</li>
          <li>✅ Antes de publicar <strong>te confirmo el nombre y tu número</strong>: nada sale mal.</li>
          <li>🎙️ <strong>Puedes hablarme</strong> en vez de escribir: lo escribo yo.</li>
          <li>⏱️ Son <strong>5 minutos</strong> y puedes dejarlo a medias y seguir después.</li>
          <li>🔑 Entra con tu número de teléfono: <strong>ese es tu usuario</strong>.</li>
        </ul>
        <div class="tia-puerta__botones">
          <a class="tia-btn tia-btn--principal" href="<?= e(url('registro.php?redirect=' . rawurlencode($modo === 'producto' ? $url_modo_producto : 'crear-tienda'))) ?>">
            Crear mi cuenta gratis
          </a>
          <a class="tia-btn" href="<?= e(url('login.php?redirect=' . rawurlencode($modo === 'producto' ? $url_modo_producto : 'crear-tienda'))) ?>">
            Ya tengo cuenta, entrar
          </a>
        </div>
        <p class="tia-puerta__pie">
          ¿Solo querías mirar? <a href="<?= e(url('index.php')) ?>">Vuelve al inicio</a>
        </p>
      </div>
    </main>
    </body>
    </html>
    <?php
    exit;
}

/* ============================ LA PÁGINA (la conversación) ============================ */
$uid     = $logueado ? (int)$usuario['id'] : 0;
$tiendas = ($modo === 'producto') ? tienda_ia_tiendas_de($uid) : [];
$tablas  = tienda_ia_tablas_ok();

// Para el modo «ya tengo una tienda»: si no tiene ninguna, se le manda a crear la primera.
if ($modo === 'producto' && !$tiendas && $tablas) {
    $modo = 'nueva';
}

$primer_nombre = $logueado ? explode(' ', trim((string)($usuario['nombre'] ?? '')))[0] : '';

tia_pagina_head($titulo_pagina, $descripcion_pagina);
?>
<body class="tia-body">

<main class="tia-app" id="tia"
      data-modo="<?= e($modo) ?>"
      data-api="<?= e(url('api/tienda_ia.php')) ?>"
      data-csrf="<?= e(csrf_token()) ?>"
      data-panel="<?= e(url('panel.php')) ?>">

  <!-- ===== Cabecera PROPIA de la página: quién soy, en qué paso vamos y la barrita ===== -->
  <header class="tia-top">
    <a class="tia-top__atras" href="<?= e(url('index.php')) ?>" title="Volver al inicio" aria-label="Volver al inicio">←</a>
    <div class="tia-top__quien">
      <span class="tia-top__emoji" aria-hidden="true"><?= TIENDA_IA_EMOJI ?></span>
      <span class="tia-top__txt">
        <strong><?= e(TIENDA_IA_NOMBRE) ?></strong>
        <small id="tiaPaso">Vamos a empezar</small>
      </span>
    </div>
    <button type="button" class="tia-top__menu" id="tiaMenuBtn" aria-expanded="false" aria-label="Más opciones">⋯</button>

    <!-- El menú de la cabecera: lo que antes eran dos botones sueltos, ahora guardado y a un toque.
         🆕 2026-09-16 — DOS OPCIONES NUEVAS, las dos en ventana emergente (orden del jefe: *«un usuario
         puede tener varias tiendas… un mismo usuario puede tener varios negocios»* y *«pon submenús en
         popup escondidos»*): «🏪 Mis tiendas» (la lista de todos sus negocios, con su enlace) y
         «🆕 Crear otra tienda» (arranca otro negocio sin perder el que ya publicó). -->
    <div class="tia-menu" id="tiaMenu" hidden>
      <button type="button" class="tia-menu__item" id="tiaMisTiendas">🏪 Mis tiendas</button>
      <button type="button" class="tia-menu__item" id="tiaOtraTienda">🆕 Crear otra tienda</button>
      <button type="button" class="tia-menu__item" id="tiaGuardar">💾 Guardar y seguir después</button>
      <button type="button" class="tia-menu__item" id="tiaReiniciar">🔄 Empezar de nuevo</button>
      <?php if ($logueado): ?>
        <a class="tia-menu__item" href="<?= e(url('panel.php')) ?>">🏪 Ir a mi panel</a>
      <?php else: ?>
        <a class="tia-menu__item" href="<?= e(url('login.php?redirect=' . rawurlencode('crear-tienda'))) ?>">🔑 Entrar con mi cuenta</a>
      <?php endif; ?>
      <a class="tia-menu__item" href="<?= e(url('index.php')) ?>">🏠 Volver al inicio</a>
    </div>

    <div class="tia-barra" aria-hidden="true"><span id="tiaBarra"></span></div>
  </header>

  <?php if (!$tablas): ?>
    <div class="tia-aviso tia-aviso--error">
      El constructor todavía no está instalado (faltan sus dos tablas en la base de datos).
      Avisa al administrador: <code>directorio_ia_tiendas</code> y <code>directorio_ia_tiendas_log</code>.
    </div>
  <?php endif; ?>

  <!-- ===== LA CONVERSACIÓN (tiene su propio scroll: es la única zona que se mueve) ===== -->
  <section class="tia-chat" id="tiaChat" aria-live="polite"></section>

  <!-- ===== Lo que el dueño puede tocar (las opciones del paso) ===== -->
  <div class="tia-opciones" id="tiaOpciones"></div>

  <!-- ===== La barra de escribir: pegada abajo DENTRO de esta página (nada queda debajo) ===== -->
  <footer class="tia-composer" id="tiaPie">
    <div class="tia-composer__caja">

      <label class="tia-sr" for="tiaTexto">Tu respuesta</label>
      <!-- 🎯🎨 EL COMPOSITOR DE LA GUÍA, TAL CUAL (orden del jefe, 2026-09-20): *«quiero llevar este
           mismo estilo de los botones —el botón de cámara (el que abre el modal de "Abrir cámara" o
           "Abrir galería"), el botón de grabar, el input y el botón de enviar— tal cual como lo tenemos
           ahorita, tal cual»*. Son CUATRO piezas y en este orden:
               [📷 cámara]  [🎤 micrófono]  [ Escribe tu respuesta… ]  [ ➤ enviar ]
           · 📷 abre un **menú chiquito ENCIMA del botón** (como el ☰ del sitio, con icono de cámara):
             **Abrir cámara** y **Abrir galería** (la galería deja marcar VARIAS fotos, como antes).
           · 🎤 es el **micrófono del buscador copiado tal cual** (`buscador_voz.js`): mismo icono, mismo
             naranja, misma forma y mismo motor (`es-PE` → `es-ES` → `es-MX`, reconocedor nuevo por
             dictado, sus avisos). Lo que dicta **cae en el cuadro** y, si el micrófono se apaga solo
             porque escuchó silencio, **el mensaje se manda al segundo** (igual que en la guía).
           · ➤ **solo envía**.
           ⛔ Se fueron el 😊 de emojis, el 📎 de adjuntar y los dos botones grandes de foto.
           🔴 OJO CON LA CLASE DE ESTA FILA: es **`tia-composer__linea`** y NO se puede volver a llamar
           `tia-barra`, porque **`tia-barra` es la barrita del progreso de la cabecera** (`#tiaBarra`,
           arriba): al compartir la clase las dos barras se pisaban y la fila quedaba de 12 px, blanca y
           con los botones saliéndose (el ➤ salía como un óvalo). Una clase, un uso. -->
      <div class="tia-composer__linea">
        <button type="button" class="tia-cam" id="tiaCam" aria-label="Mandar una foto" title="Mandar una foto" aria-haspopup="menu">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9.4 3.5h5.2l1.3 1.9h3.3A2.4 2.4 0 0 1 21.6 7.8v9.3a2.4 2.4 0 0 1-2.4 2.4H4.8a2.4 2.4 0 0 1-2.4-2.4V7.8a2.4 2.4 0 0 1 2.4-2.4h3.3zM12 8.6a4.1 4.1 0 1 0 0 8.2 4.1 4.1 0 0 0 0-8.2zm0 1.7a2.4 2.4 0 1 1 0 4.8 2.4 2.4 0 0 1 0-4.8z"/></svg>
        </button>
        <button type="button" class="tia-voz" id="tiaVoz" hidden aria-pressed="false"
                title="Hablar: tu voz se escribe en el cuadro" aria-label="Hablar: tu voz se escribe en el cuadro">
          <span class="tia-voz__icono" aria-hidden="true"><?php // el MISMO dibujo del buscador (micrófono relleno) ?>
            <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true" focusable="false"><path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/><path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/></svg>
          </span>
        </button>
        <textarea id="tiaTexto" class="tia-campo" rows="1" placeholder="Escribe tu respuesta…" aria-label="Tu respuesta"></textarea>
        <button type="button" class="tia-enviar" id="tiaEnviar" aria-label="Enviar la respuesta" title="Enviar">
          <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3.3 20.5 21 12.6a.65.65 0 0 0 0-1.2L3.3 3.5a.65.65 0 0 0-.9.75l1.95 6.6c.05.2.22.35.43.37l8.5.78-8.5.78c-.21.02-.38.17-.43.37L2.4 19.75a.65.65 0 0 0 .9.75z"/></svg>
        </button>
      </div>

      <?php // 📷 Dos entradas de archivo: la primera abre la CÁMARA (capture), la segunda la GALERÍA
            // (sin capture y con `multiple`: el jefe quiere poder elegir varias fotos de una vez). ?>
      <input type="file" id="tiaArchivo" accept="image/*" capture="environment" hidden>
      <input type="file" id="tiaArchivoGaleria" accept="image/*" multiple hidden>
    </div>

    <!-- 📷 El menú chiquito de la cámara: sale ENCIMA del botón (como el ☰ del sitio) -->
    <div class="tia-cam-menu" id="tiaCamMenu" role="menu" aria-label="Mandar una foto" hidden>
      <button type="button" class="tia-cam-op" id="tiaCamFoto" role="menuitem"><span aria-hidden="true">📷</span> Abrir cámara</button>
      <button type="button" class="tia-cam-op" id="tiaCamGaleria" role="menuitem"><span aria-hidden="true">🖼️</span> Abrir galería</button>
    </div>
    <?php // 🔔 El aviso del micrófono (el mismo del buscador: permiso bloqueado, no te escuché, sin conexión…) ?>
    <div class="tia-voz-aviso" id="tiaVozAviso" role="status"></div>

    <p class="tia-ayuda" id="tiaAyuda"></p>
  </footer>

  <!-- ==================================================================================
       🪟 LAS VENTANAS EMERGENTES (2026-09-16) — orden del jefe: *«pon submenús en popup
       escondidos… y asimismo mete otros modales donde lo creas conveniente»*.
       Las dos nacen ESCONDIDAS (`hidden`) y las abre `tienda_ia.js`:
         · 🏪 `#tiaSheetTiendas` → todas las tiendas del dueño (puede tener varios negocios).
         · 🆕 `#tiaSheetOtra`    → confirmación para armar otra tienda.
       Se cierran con la ✕/Cancelar, tocando el fondo (el velo) o con Escape.
       ⚠️ 2026-09-20: la ventana de la FOTO (`#tiaSheetFoto`) se retiró — ahora la cámara tiene su
       **menú chiquito** encima del botón (ver el compositor), como en la guía.
       ================================================================================== -->

  <!-- 🏪 Mis tiendas: todos sus negocios, con su enlace (los pinta `tienda_ia.js`) -->
  <div class="tia-sheet" id="tiaSheetTiendas" hidden>
    <div class="tia-sheet__velo" data-cerrar="1"></div>
    <div class="tia-sheet__caja" role="dialog" aria-modal="true" aria-labelledby="tiaSheetTiendasTit">
      <p class="tia-sheet__tit" id="tiaSheetTiendasTit">🏪 Mis tiendas</p>
      <div class="tia-sheet__lista" id="tiaSheetTiendasLista"></div>
      <button type="button" class="tia-sheet__op tia-sheet__op--nuevo" id="tiaSheetTiendasOtra">
        <span class="tia-sheet__ico">🆕</span>
        <span class="tia-sheet__txt"><strong>Crear otra tienda</strong><small>Para otro negocio tuyo</small></span>
      </button>
      <button type="button" class="tia-sheet__cerrar" data-cerrar="1">Cerrar</button>
    </div>
  </div>

  <!-- 🆕 Otra tienda: la confirmación (el texto lo arma `tienda_ia.js` según el paso) -->
  <div class="tia-sheet" id="tiaSheetOtra" hidden>
    <div class="tia-sheet__velo" data-cerrar="1"></div>
    <div class="tia-sheet__caja" role="dialog" aria-modal="true" aria-labelledby="tiaSheetOtraTit">
      <p class="tia-sheet__tit" id="tiaSheetOtraTit">🆕 ¿Armamos otra tienda?</p>
      <p class="tia-sheet__nota" id="tiaSheetOtraTxt"></p>
      <button type="button" class="tia-sheet__op tia-sheet__op--nuevo" id="tiaSheetOtraSi">
        <span class="tia-sheet__ico">🆕</span>
        <span class="tia-sheet__txt"><strong>Sí, crear otra tienda</strong><small>Empezamos con las fotos del otro negocio</small></span>
      </button>
      <button type="button" class="tia-sheet__cerrar" data-cerrar="1">No, seguir aquí</button>
    </div>
  </div>
</main>

<script>window.TIA_CFG = {
  modo: <?= json_encode($modo) ?>,
  // 🛒🆕 El producto que ya viene dicho por el botón «Agregar mi tienda» del buscador (vacío si no):
  // el navegador lo manda en cada petición (`prod`) y el motor lo usa como nombre del producto.
  producto: <?= json_encode($prod) ?>,
  api: <?= json_encode(url('api/tienda_ia.php')) ?>,
  csrf: <?= json_encode(csrf_token()) ?>,
  usuario: <?= json_encode($primer_nombre) ?>,
  entro: <?= json_encode($logueado) ?>,
  emoji: <?= json_encode(TIENDA_IA_EMOJI) ?>,
  bot: <?= json_encode(TIENDA_IA_NOMBRE) ?>,
  sitio: <?= json_encode(SITE_URL) ?>,
  sitio_nombre: <?= json_encode(SITE_NAME) ?>,
  panel: <?= json_encode(url('panel.php')) ?>,
  casa: <?= json_encode(url('index.php')) ?>,
  // 🆕 La página limpia (modo «tienda nueva»): la usa el botón «crear otra tienda» cuando el dueño
  // está en el modo «agregar un producto» (`/crear-tienda?modo=producto`), porque esa conversación
  // no es la misma y hay que cambiar de modo de verdad.
  nueva: <?= json_encode(url('crear-tienda')) ?>,
  max_fotos: <?= json_encode((int)TIENDA_IA_FOTOS_TIENDA_MAX) ?>,
  min_fotos: <?= json_encode((int)TIENDA_IA_FOTOS_TIENDA_MIN) ?>,
  // 📷🆕 El arranque con fotos: mínimo 5 (orden del jefe, 2026-09-15) y sin botón de cámara.
  min_fotos_arranque: <?= json_encode((int)TIENDA_IA_FOTOS_ARRANQUE_MIN) ?>,
  max_fotos_arranque: <?= json_encode((int)TIENDA_IA_FOTOS_ARRANQUE_MAX) ?>,
  wa_share: <?= json_encode('https://wa.me/?text=') ?>,
  wa_icono: <?= json_encode(function_exists('wa_icono_svg') ? wa_icono_svg() : '') ?>
};</script>
<?php // 🎯 2026-09-20: al compositor de la guía (📷 · 🎤 · cuadro · ➤) le toca **v=13** en el JS.
      // ⚠️ `dictado_voz.js` sigue cargado pero ya no pinta nada aquí: el cuadro no lleva `data-dictado`
      // (el micrófono del maestro es el del buscador, copiado dentro de `tienda_ia.js`). ?>
<script src="<?= url('assets/js/imagen_optimizar.js') ?>?v=2" defer></script>
<script src="<?= url('assets/js/dictado_voz.js') ?>?v=3" defer></script>
<script src="<?= url('assets/js/tienda_ia.js') ?>?v=13" defer></script>
<?php tia_pagina_scripts(); ?>
