<?php
/**
 * supremo.php — 👑 «EL SUPREMO»: LA PÁGINA DE LA VENTA EN VIVO
 * =====================================================================
 * URL amigable: la barra y la palabra supremo (regla en el `.htaccess`).
 *
 * 🔒 **SOLO EL SÚPER ADMINISTRADOR.** No hay enlace a esta página en ninguna parte pública del
 * sitio: el único camino es el botón de la pestaña **👑 El Supremo** del panel del administrador
 * (`superadmin.php?seccion=supremo`). Aquí se comprueba `es_admin()` y su puerta
 * (`api/supremo.php`) lo vuelve a comprobar en cada petición.
 *
 * 🖥️ Es una PÁGINA SOLA de pantalla completa, igual que El maestro (mismos estilos:
 * `assets/css/tienda_ia.css`), y le suma lo suyo (`assets/css/supremo.css`):
 *
 *   · ⏱️ **El cronómetro de la venta** en la cabecera (la meta son 4 a 5 minutos).
 *   · 👑 **El panel de los códigos**: las 4 tarjetas con su botón de copiar (cabecera, música,
 *     imágenes de los productos y el mensaje para el cliente). Se abre solo al llegar a ese paso
 *     y también con el botón de la cabecera, para tenerlo a mano delante del cliente.
 *   · 🛍️ **La rejilla de productos** que se pueden incluir, con la foto donde la IA los vio.
 *   · 📥 **La sala de espera de imágenes**: se suben todas de una vez y el servidor las reparte.
 *
 * Guía: GUIA_EL_SUPREMO.md
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/supremo.php';

// 🔒 La puerta del administrador (la de verdad; la de la API es la que impide cualquier POST).
$admin = usuario_actual();
if (!$admin) {
    redirect('login.php?redirect=' . rawurlencode('supremo'));
}
if (!es_admin()) {
    http_response_code(403);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><title>Acceso denegado</title></head>'
       . '<body style="font-family:system-ui;padding:40px;text-align:center">'
       . '<h1 style="font-size:22px">👑 El Supremo es privado</h1>'
       . '<p>Esta página es del Súper Administrador de ' . e(SITE_NAME) . '.</p>'
       . '<p><a href="' . e(url('index.php')) . '">Volver al inicio</a></p></body></html>';
    exit;
}

// Página privada: que ningún buscador la guarde. Y ⚠️ **nada de caché del HTML**: si el navegador o
// el hosting se guardan esta página, el jefe sigue viendo la versión vieja después de cada mejora
// (pasó el 2026-09-18 con el JS y el CSS: por HTTP seguían sirviendo los bytes anteriores).
header('X-Robots-Tag: noindex, nofollow');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$titulo_pagina      = SUPREMO_TITULO;
$descripcion_pagina = SUPREMO_SUBTITULO;

// 📈 La visita cuenta para las estadísticas del sitio (igual que en El maestro).
if (is_file(__DIR__ . '/includes/estadisticas.php')) {
    require_once __DIR__ . '/includes/estadisticas.php';
    try { stats_registrar_visita(); } catch (Throwable $e) { /* nunca romper la página */ }
}

$tablas        = tienda_ia_tablas_ok();
$primer_nombre = explode(' ', trim((string)($admin['nombre'] ?? '')))[0];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
  <title><?= e($titulo_pagina) ?> · <?= e(SITE_NAME) ?></title>
  <meta name="description" content="<?= e($descripcion_pagina) ?>">
  <meta name="robots" content="noindex, nofollow">
  <meta name="theme-color" content="<?= e(SUPREMO_COLOR) ?>">
  <link rel="icon" href="<?= url('assets/img/favicon.ico') ?>">
  <!-- Los estilos de la conversación son los de El maestro (misma casa) + los propios del Supremo.
       ⚠️ El `?v=N` NO es decorativo: el hosting cachea los estáticos por URL, así que al cambiar el
       JS o el CSS hay que SUBIR ESTE NÚMERO o el jefe seguirá viendo los archivos viejos. -->
  <link rel="stylesheet" href="<?= url('assets/css/tienda_ia.css') ?>?v=12">
  <link rel="stylesheet" href="<?= url('assets/css/supremo.css') ?>?v=8">
</head>
<body class="tia-body sup-body">

<main class="tia-app" id="sup"
      data-api="<?= e(url('api/supremo.php')) ?>"
      data-csrf="<?= e(csrf_token()) ?>"
      data-panel="<?= e(url('panel.php')) ?>"
      data-admin="<?= e(url('superadmin.php?seccion=supremo')) ?>">

  <!-- ===== Cabecera: quién soy, el paso, el cronómetro de la venta y los códigos a mano ===== -->
  <header class="tia-top sup-top">
    <a class="tia-top__atras" href="<?= e(url('superadmin.php?seccion=supremo')) ?>"
       title="Volver al panel" aria-label="Volver al panel">←</a>
    <div class="tia-top__quien">
      <span class="tia-top__emoji" aria-hidden="true"><?= SUPREMO_EMOJI ?></span>
      <span class="tia-top__txt">
        <strong><?= e(SUPREMO_NOMBRE) ?></strong>
        <small id="supPaso">Modo vendedor</small>
      </span>
    </div>

    <!-- ⏱️ El cronómetro de la venta: la meta son 4 o 5 minutos delante del cliente -->
    <div class="sup-reloj" id="supReloj" title="Tiempo de la venta (la meta son <?= (int)SUPREMO_MINUTOS_OBJETIVO ?> minutos)">
      <span class="sup-reloj__ico" aria-hidden="true">⏱️</span>
      <span class="sup-reloj__t" id="supRelojT">0:00</span>
    </div>

    <!-- 👑 A los códigos, de un toque (es lo que se pega en Flow delante del cliente) -->
    <button type="button" class="sup-btn-oro" id="supBtnCodigos" hidden>👑 Códigos</button>

    <!-- 💰 LA OFERTA: la cuenta que cierra la venta (precio × 50 ventas contra el 10 %). -->
    <button type="button" class="sup-btn-oro sup-btn-oro--plata" id="supBtnOferta" title="La cuenta de la oferta">💰 Oferta</button>

    <!-- 🧠 PIENSA MEJOR: repasa lo publicado, reestructura los datos y pide lo que faltó. -->
    <button type="button" class="sup-btn-oro sup-btn-oro--piensa" id="supBtnPiensa" title="Piensa mejor: revisa la tienda y completa lo que falte">🧠</button>

    <!-- 🎵 LA CANCIÓN: subir el audio que bajó de Flow y dejarlo sonando en la ficha. -->
    <button type="button" class="sup-btn-oro sup-btn-oro--musica" id="supBtnCancion" title="Subir la canción de la tienda" hidden>🎵</button>

    <button type="button" class="tia-top__menu" id="supMenuBtn" aria-expanded="false" aria-label="Más opciones">⋯</button>

    <div class="tia-menu" id="supMenu" hidden>
      <button type="button" class="tia-menu__item" id="supVerUbicacion">📍 Compartir ubicación</button>
      <button type="button" class="tia-menu__item" id="supVerPiensa">🧠 Piensa mejor</button>
      <button type="button" class="tia-menu__item" id="supVerCodigos">👑 Los códigos de la venta</button>
      <button type="button" class="tia-menu__item" id="supVerOferta">💰 La oferta y el guion de venta</button>
      <button type="button" class="tia-menu__item" id="supVerCancion">🎵 Subir la canción de la tienda</button>
      <button type="button" class="tia-menu__item" id="supIrImagenes">📥 Subir las imágenes</button>
      <button type="button" class="tia-menu__item" id="supVerTienda">🏬 Ver la tienda</button>
      <button type="button" class="tia-menu__item" id="supGuardar">💾 Guardar y seguir después</button>
      <button type="button" class="tia-menu__item" id="supReiniciar">🔄 Empezar otra venta</button>
      <a class="tia-menu__item" href="<?= e(url('superadmin.php?seccion=supremo')) ?>">👑 Ir a mi panel del Supremo</a>
      <a class="tia-menu__item" href="<?= e(url('crear-tienda')) ?>" target="_blank" rel="noopener">🛠️ Abrir El maestro</a>
    </div>

    <div class="tia-barra" aria-hidden="true"><span id="supBarra"></span></div>
  </header>

  <?php if (!$tablas): ?>
    <div class="tia-aviso tia-aviso--error">
      El Supremo todavía no puede guardar nada: faltan sus tablas en la base de datos
      (<code>directorio_ia_tiendas</code> y <code>directorio_ia_tiendas_log</code>, las mismas de El maestro).
      Se crean solas al abrir <a href="<?= e(url('crear-tienda')) ?>" target="_blank" rel="noopener">/crear-tienda</a>.
    </div>
  <?php endif; ?>

  <!-- ===== LA CONVERSACIÓN (es la única zona que scrollea) ===== -->
  <section class="tia-chat" id="supChat" aria-live="polite"></section>

  <!-- ===== 🛍️ La rejilla de productos que se pueden incluir (solo en su paso) ===== -->
  <div class="sup-multi" id="supMulti" hidden></div>

  <!-- ===== 📥 Lo que ya llegó a la sala de espera (solo en su paso) ===== -->
  <div class="sup-llegaron" id="supLlegaron" hidden></div>

  <!-- ===== 🎵 La canción de la tienda (se pinta cuando ya la tiene, con su reproductor) ===== -->
  <div class="sup-cancion" id="supCancion" hidden></div>

  <!-- ===== Los botones del paso ===== -->
  <div class="tia-opciones" id="supOpciones"></div>

  <!-- ===== La barra de escribir (pegada abajo, dentro de la página) ===== -->
  <footer class="tia-composer" id="supPie">
    <div class="tia-composer__caja">

      <div class="tia-foto" id="supFotoBotones" hidden>
        <button type="button" class="tia-btn tia-btn--camara" id="supCamara"><?= e(TIENDA_IA_TEXTO_CAMARA) ?></button>
        <button type="button" class="tia-btn tia-btn--galeria" id="supGaleria"><?= e(TIENDA_IA_TEXTO_GALERIA) ?></button>
      </div>

      <label class="tia-sr" for="supTexto">Escribe aquí</label>
      <!-- El compositor es el de WhatsApp, igual que en El maestro: [😊] [ Mensaje ] [📎] [📷] [🎤] -->
      <div class="tia-wa">
        <button type="button" class="tia-wa__ico" id="supEmoji" aria-label="Emojis" title="Emojis">
          <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round">
            <circle cx="12" cy="12" r="9.2"/>
            <path d="M8.4 14.2c.9 1.5 2.2 2.2 3.6 2.2s2.7-.7 3.6-2.2"/>
            <circle cx="9" cy="9.6" r="1.15" fill="currentColor" stroke="none"/>
            <circle cx="15" cy="9.6" r="1.15" fill="currentColor" stroke="none"/>
          </svg>
        </button>
        <textarea id="supTexto" class="tia-wa__campo" rows="1" placeholder="Escribe o habla 🎙️" aria-label="Mensaje"></textarea>
        <div class="tia-wa__icos">
          <button type="button" class="tia-wa__ico" id="supClip" aria-label="Adjuntar fotos" title="Adjuntar fotos" hidden>
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20.4 11.6l-7.7 7.7a5 5 0 0 1-7.1-7.1l8-8a3.4 3.4 0 0 1 4.8 4.8l-8 8a1.8 1.8 0 0 1-2.5-2.5l7.3-7.3"/>
            </svg>
          </button>
          <button type="button" class="tia-wa__ico" id="supCam" aria-label="Tomar una foto" title="Tomar una foto" hidden>
            <svg viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3.5 8.5h3l1.6-2.2h7.8l1.6 2.2h3v10h-17z"/><circle cx="12" cy="13.2" r="3.4"/>
            </svg>
          </button>
          <button type="button" class="tia-wa__mic" id="supEnviar" aria-label="Grabar un mensaje de voz" title="Toca para hablar">
            <span class="tia-wa__mic-ico" aria-hidden="true">
              <svg viewBox="0 0 24 24" width="24" height="24" fill="currentColor">
                <path d="M12 15a3.2 3.2 0 0 0 3.2-3.2V6.2a3.2 3.2 0 1 0-6.4 0v5.6A3.2 3.2 0 0 0 12 15z"/>
                <path d="M18.6 11.6a6.6 6.6 0 0 1-13.2 0H3.6a8.4 8.4 0 0 0 7.4 8.3V22h2v-2.1a8.4 8.4 0 0 0 7.4-8.3h-1.8z"/>
              </svg>
            </span>
            <span class="tia-wa__mic-enviar" aria-hidden="true" hidden>
              <svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor"><path d="M3.4 20.4l17.5-8.4L3.4 3.6l.1 6.6 12 1.8-12 1.8z"/></svg>
            </span>
          </button>
        </div>
      </div>

      <input type="file" id="supArchivo" accept="image/*" capture="environment" hidden>
      <input type="file" id="supArchivoGaleria" accept="image/*" multiple hidden>
      <?php // 🎵 El audio de la canción (lo que el jefe bajó de Flow). No se comprime ni se toca. ?>
      <input type="file" id="supAudio" accept="audio/*,.mp3,.ogg,.m4a,.opus,.wav,.aac,.flac" hidden>
    </div>

    <div class="tia-emoji" id="supEmojiPanel" hidden aria-label="Emojis"></div>
    <p class="tia-ayuda" id="supAyuda"></p>
  </footer>

  <!-- ==================================================================================
       👑 EL PANEL DE LOS CÓDIGOS — lo que hace único al Supremo.
       Se abre solo al llegar al paso `sup_codigos` y con el botón dorado de la cabecera.
       Cada tarjeta: su título, su «cómo se usa» y el texto con su botón de COPIAR.
       (Regla inviolable n.º 1 del proyecto: todo lo que el jefe tenga que llevar a otro sitio
       se le entrega listo para copiar de un clic; nunca se le pide seleccionar texto.)
       ================================================================================== -->
  <div class="sup-sheet" id="supSheetCodigos" hidden>
    <div class="sup-sheet__velo" data-cerrar="1"></div>
    <div class="sup-sheet__caja" role="dialog" aria-modal="true" aria-labelledby="supCodigosTit">
      <div class="sup-sheet__cabeza">
        <p class="sup-sheet__tit" id="supCodigosTit">👑 Los códigos de esta venta</p>
        <button type="button" class="sup-sheet__x" data-cerrar="1" aria-label="Cerrar">✕</button>
      </div>
      <p class="sup-sheet__nota" id="supCodigosNota">
        Copia cada uno con su botón y pégalo donde dice. El de la cabecera necesita que le adjuntes
        una <b>imagen de referencia</b> (de esa imagen se copia solo el estilo, nunca su contenido).
      </p>
      <div class="sup-codigos" id="supCodigosLista"></div>
      <button type="button" class="tia-btn" data-cerrar="1">Cerrar y seguir con la venta</button>
    </div>
  </div>

  <!-- ==================================================================================
       💰 EL PANEL DE LA OFERTA — la cuenta que cierra la venta.
       Se abre con el botón 💰 de la cabecera (en cualquier momento de la venta) y también
       cuando el vendedor toca «Ver la oferta y el guion».
       · LA CALCULADORA va en vivo (sin ir al servidor): se escribe el precio y la tabla se
         recalcula al instante, con el cliente mirando.
       · Los 4 textos (la oferta, el guion, las condiciones y la frase) vienen del servidor
         ya armados con los datos de ESTA tienda, y cada uno tiene su botón de COPIAR.
       ================================================================================== -->
  <div class="sup-sheet" id="supSheetOferta" hidden>
    <div class="sup-sheet__velo" data-cerrar="1"></div>
    <div class="sup-sheet__caja" role="dialog" aria-modal="true" aria-labelledby="supOfertaTit">
      <div class="sup-sheet__cabeza">
        <p class="sup-sheet__tit" id="supOfertaTit">💰 La oferta — la cuenta que cierra la venta</p>
        <button type="button" class="sup-sheet__x" data-cerrar="1" aria-label="Cerrar">✕</button>
      </div>

      <div class="sup-calc">
        <label class="sup-calc__et" for="supCalcPrecio">Precio de UN producto del local (<?= e(SUPREMO_MONEDA) ?>)</label>
        <div class="sup-calc__fila">
          <input type="number" id="supCalcPrecio" class="sup-calc__campo" inputmode="decimal"
                 step="0.5" min="0" placeholder="80" aria-label="Precio del producto">
          <button type="button" class="sup-calc__usar" id="supCalcUsar">✅ Usar este precio</button>
        </div>
        <div class="sup-calc__chips" id="supCalcChips"></div>

        <table class="sup-calc__tabla" id="supCalcTabla">
          <tbody>
            <tr><th>Precio unitario</th><td id="supCalcPrecioV">—</td></tr>
            <tr><th>Ventas prometidas</th><td><?= (int)SUPREMO_OFERTA_VENTAS ?> en <?= (int)SUPREMO_OFERTA_DIAS ?> días</td></tr>
            <tr><th>Ingreso para la tienda</th><td id="supCalcIngreso" class="sup-calc__fuerte">—</td></tr>
            <tr><th>Comisión <?= rtrim(rtrim(number_format((float)SUPREMO_OFERTA_COMISION, 2, '.', ''), '0'), '.') ?> % (como otros)</th><td id="supCalcComision">—</td></tr>
            <tr><th>Mi tarifa al mes</th><td id="supCalcTarifa"><?= e(supremo_soles((float)SUPREMO_OFERTA_TARIFA)) ?></td></tr>
            <tr><th>Ahorro para la tienda</th><td id="supCalcAhorro" class="sup-calc__fuerte">—</td></tr>
            <tr><th>Mi comisión real</th><td id="supCalcEfectiva">—</td></tr>
            <tr><th>Ventas que cubren mi tarifa</th><td id="supCalcCubre">—</td></tr>
          </tbody>
        </table>

        <p class="sup-calc__frase" id="supCalcFrase"></p>
      </div>

      <div class="sup-codigos" id="supOfertaTextos"></div>
      <button type="button" class="tia-btn" data-cerrar="1">Cerrar y seguir con la venta</button>
    </div>
  </div>

  <!-- ==================================================================================
       🎵 EL PANEL DE LA CANCIÓN — subir el audio que el jefe bajó de Google Flow.
       Orden del jefe (2026-09-18): «no hay modo de subir la canción descargada al Supremo».
       Se pega el archivo tal cual llega (no se recorta ni se reedito) y **no gasta créditos**:
       el motor de canciones del sitio la deja sonando en la ficha (directorio_canciones).
       ================================================================================== -->
  <div class="sup-sheet" id="supSheetCancion" hidden>
    <div class="sup-sheet__velo" data-cerrar="1"></div>
    <div class="sup-sheet__caja" role="dialog" aria-modal="true" aria-labelledby="supCancionTit">
      <div class="sup-sheet__cabeza">
        <p class="sup-sheet__tit" id="supCancionTit">🎵 La canción de la tienda</p>
        <button type="button" class="sup-sheet__x" data-cerrar="1" aria-label="Cerrar">✕</button>
      </div>
      <p class="sup-sheet__nota">
        En Google Flow ya la tienes hecha y <b>descargada</b>: mándamela aquí y la dejo
        <b>sonando sola en la ficha del cliente</b>. Se guarda tal cual llega (no la recorto ni la
        reedito) y <b>no gasta créditos</b>.
      </p>
      <div id="supCancionEstado"></div>
      <button type="button" class="sup-cancion__subir" id="supCancionSubir">
        <span class="sup-cancion__ico" aria-hidden="true">🎵</span>
        <span class="sup-cancion__txt"><strong>Subir el audio que bajaste</strong>
          <small id="supCancionNota"></small></span>
      </button>
      <button type="button" class="tia-btn" data-cerrar="1">Cerrar</button>
    </div>
  </div>

</main>

<script>window.SUP_CFG = {
  nombre_bot: <?= json_encode(SUPREMO_NOMBRE) ?>,
  emoji: <?= json_encode(SUPREMO_EMOJI) ?>,
  api: <?= json_encode(url('api/supremo.php')) ?>,
  csrf: <?= json_encode(csrf_token()) ?>,
  usuario: <?= json_encode($primer_nombre) ?>,
  sitio: <?= json_encode(SITE_URL) ?>,
  sitio_nombre: <?= json_encode(SITE_NAME) ?>,
  panel: <?= json_encode(url('panel.php')) ?>,
  casa: <?= json_encode(url('index.php')) ?>,
  objetivo_seg: <?= json_encode((int)SUPREMO_MINUTOS_OBJETIVO * 60) ?>,
  // 🎵 El tope REAL de subida de audio de este servidor (lo calcula el motor según PHP).
  audio_max_mb: <?= json_encode((int)supremo_audio_max_mb()) ?>,
  // 💰 Los tres números de la oferta (salen de config_supremo.php: se cambian en UN solo sitio).
  oferta: <?= json_encode(['tarifa' => (float)SUPREMO_OFERTA_TARIFA,
                           'ventas' => (int)SUPREMO_OFERTA_VENTAS,
                           'comision' => (float)SUPREMO_OFERTA_COMISION,
                           'dias' => (int)SUPREMO_OFERTA_DIAS,
                           'ideal' => (float)SUPREMO_OFERTA_EFECTIVA_IDEAL,
                           'moneda' => SUPREMO_MONEDA]) ?>,
  min_fotos: <?= json_encode((int)SUPREMO_FOTOS_MIN) ?>,
  max_fotos: <?= json_encode((int)SUPREMO_FOTOS_MAX) ?>,
  // 📷📉 El tamaño máximo (lado mayor) y la calidad con que se comprimen las fotos EN EL CELULAR
  //      antes de subirlas: a 800 px una foto de 4 MB sube en ~190 KB (medido, ver config_supremo.php).
  foto_lado: <?= json_encode((int)SUPREMO_FOTO_LADO) ?>,
  foto_calidad: <?= json_encode((float)SUPREMO_FOTO_CALIDAD) ?>,
  lote_max: <?= json_encode((int)SUPREMO_IMAGENES_LOTE) ?>,
  gemini: 'https://gemini.google.com/app',
  wa_share: 'https://wa.me/?text=',
  // 📲 El ícono OFICIAL de WhatsApp (el mismo del sitio) para el botón que le manda los datos al cliente.
  wa_icono: <?= json_encode(function_exists('wa_icono_svg') ? wa_icono_svg() : '') ?>
};</script>
<script src="<?= url('assets/js/imagen_optimizar.js') ?>?v=2" defer></script>
<script src="<?= url('assets/js/supremo.js') ?>?v=11" defer></script>
</body>
</html>
