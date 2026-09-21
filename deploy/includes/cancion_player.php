<?php
/**
 * includes/cancion_player.php — 🎵 EL REPRODUCTOR DE LA CANCIÓN EN LA FICHA
 * =====================================================================
 * Qué pinta: la barrita NEGRA con los controles PLATEADOS de la canción de la tienda, **debajo del
 * número de teléfono** (orden del jefe, 2026-09-17). La canción es el jingle de esa tienda (la cantó
 * Treblo con su nombre y su rubro, o la puso el agente desde la carpeta de Descargas — ver
 * `includes/cancion.php` y el §16 de `GUIA_CONSTRUCTOR_DE_TIENDAS.md`).
 *
 * 🔴 LAS 4 ÓRDENES DEL JEFE DEL 2026-09-17 (las que mandan hoy):
 *   1. **«El reproductor ponlo debajo del número de teléfono»** → en la plantilla A va dentro de la
 *      tarjeta de datos, justo debajo de la fila 📞 Teléfono; en la B, debajo de la fila de datos; en
 *      la C, dentro del bloque 📞 Contacto, debajo del teléfono. (Antes iba debajo de los botones.)
 *   2. **«Un reproductor negro con controles plateados»** → fondo negro con degradado, borde gris
 *      plata, botón ▶️ y barra de avance en plata pulida. Aquí NO hay morado ni ningún otro color.
 *   3. **«La canción debe estar en autoplay»** 🔴 ORDEN DEL JEFE DEL **2026-09-18** (la que manda
 *      hoy) → el `<audio>` sale del HTML con **`autoplay`** y **`preload="auto"`** y la canción
 *      arranca sola **cada vez** que alguien entra a la ficha. Si el navegador bloquea el arranque
 *      (entrada directa, sin ningún clic), el **primer toque** del visitante la arranca igual y
 *      también se reintenta cuando el audio ya tiene datos (`canplay`).
 *      *(Del 2026-09-17 al 2026-09-18 valió lo contrario —«solo se reproduce 1 vez la primera vez que
 *      entran; no las próximas veces»—: eso quedó reemplazado. Con `CANCION_PLAYER_SOLO_UNA_VEZ = true`
 *      vuelve el comportamiento viejo, con su `localStorage` `dch_cancion_oida`.)*
 *   4. **«Volumen al 50%»** → `audio.volume = CANCION_PLAYER_VOLUMEN` (0.5), también al tocar ▶️.
 *
 * ⚠️ Detalles que importan:
 *   · El reproductor se pinta **solo en la plantilla activa** de la ficha (las otras dos viajan
 *     ocultas en el HTML): si se pintara en las tres, habría tres `<audio>` con la misma canción.
 *   · 📱 **Optimizado para celular Android («todo para Android», orden del jefe del 2026-09-17)**:
 *     la barra es **baja (54 px) y ancha (todo el ancho de su columna)**, con los textos en 11-12 px y
 *     un solo renglón; abajo de 420 px el reloj se esconde para que el nombre de la tienda no se corte.
 *     El audio se sirve en **OGG/Opus** (formato que Android y Chrome reproducen de sobra y que pesa
 *     ~4 veces menos que el MP3 original).
 *   · El CSS y el JavaScript van aquí dentro a propósito (es una barra): así no hay que tocar
 *     `includes/header.php` ni subir otra versión de los estilos del sitio.
 *
 * Guía: GUIA_CONSTRUCTOR_DE_TIENDAS.md §16.7.
 */

require_once __DIR__ . '/cancion.php';

/**
 * La barra de la canción, lista para pegar en la ficha. Devuelve '' si la tienda no tiene canción.
 *
 * @param array $negocio la fila de la tienda (necesita `id` y `nombre`)
 */
function cancion_player_html(array $negocio): string {
    $negocio_id = (int)($negocio['id'] ?? 0);
    if ($negocio_id <= 0) return '';
    $can = cancion_de_negocio($negocio_id);
    if (!$can || empty($can['ruta'])) return '';

    $src    = url((string)$can['ruta']);
    // 🔁 EL «?v=» QUE ROMPE LA CACHÉ (2026-09-17): el audio se sirve por Cloudflare y si algún día se
    //    rehace la canción de una tienda (misma ruta, audio nuevo), el visitante seguiría oyendo la
    //    vieja durante horas. Con la marca de tiempo del archivo, la dirección cambia y se oye la nueva.
    if (!empty($can['creado_en'])) {
        $src .= '?v=' . strtotime((string)$can['creado_en']);
    }
    $seg    = (float)($can['duracion'] ?? 0);
    $seg    = $seg > 0 ? $seg : 0;
    $reloj  = sprintf('%d:%02d', floor($seg / 60), (int)round(fmod($seg, 60)));
    $nombre = (string)($negocio['nombre'] ?? '');
    $corto  = mb_strlen($nombre) > 30 ? mb_substr($nombre, 0, 29) . '…' : $nombre;

    $vol = (float)CANCION_PLAYER_VOLUMEN;
    if ($vol <= 0 || $vol > 1) $vol = 0.5;

    // 🔴 AUTOPLAY (orden del jefe, 2026-09-18: «la canción debe estar en autoplay»). Con
    //    `CANCION_PLAYER_SOLO_UNA_VEZ = false` arranca sola SIEMPRE y el `autoplay` va en el HTML.
    //    Con `true` (comportamiento viejo) solo suena la primera visita y lo decide el JavaScript.
    $auto_siempre = (CANCION_PLAYER_AUTOPLAY && !CANCION_PLAYER_SOLO_UNA_VEZ);
    $preload  = (CANCION_PLAYER_AUTOPLAY && ($auto_siempre || CANCION_PLAYER_SOLO_UNA_VEZ)) ? 'auto' : 'none';
    $autoplay = $auto_siempre ? ' autoplay' : '';

    // 🎛️ EL CSS — MINIMALISTA, PLOMO Y BLANCO (orden del jefe, 2026-09-18: *«cambia el color negro por un
    //    color plomo y blanco, discreto, muy minimalista el reproductor»*). Antes era una barra NEGRA con
    //    controles plateados; ahora es blanca, con el borde y los textos en gris plomo y sin sombras
    //    fuertes ni brillos: solo el nombre de la tienda, la línea de avance y el tiempo.
    $html  = '<style>'
           . '.czc{display:flex;align-items:center;gap:10px;width:100%;box-sizing:border-box;'
           . 'margin:12px 0 2px;padding:9px 12px;border-radius:14px;'
           . 'background:#fff;border:1px solid #e5e7eb;'
           . 'box-shadow:0 1px 2px rgba(15,23,42,.05)}'
           . '.czc *{box-sizing:border-box}'
           . '.czc__play{flex:0 0 auto;width:38px;height:38px;border-radius:50%;cursor:pointer;'
           . 'border:1px solid #d1d5db;padding:0;font-size:13px;line-height:1;'
           . 'display:flex;align-items:center;justify-content:center;color:#6b7280;'
           . 'background:#fafafa;transition:background .12s,color .12s,transform .12s}'
           . '.czc__play:hover{background:#f3f4f6;color:#4b5563}'
           . '.czc__play:active{transform:scale(.94)}'
           . '.czc__play:focus-visible{outline:2px solid #9ca3af;outline-offset:2px}'
           . '.czc__play--suena{border-color:#c7cbd1;background:#f3f4f6;color:#4b5563}'
           . '.czc__cuerpo{flex:1 1 auto;min-width:0}'
           . '.czc__nombre{font-size:12.5px;font-weight:600;color:#6b7280;line-height:1.2;'
           . 'white-space:nowrap;overflow:hidden;text-overflow:ellipsis}'
           . '.czc__barra{height:3px;border-radius:99px;background:#eceef1;'
           . 'margin-top:7px;overflow:hidden}'
           . '.czc__avance{display:block;height:100%;width:0;border-radius:99px;'
           . 'background:#9ca3af;transition:width .18s linear}'
           . '.czc__reloj{flex:0 0 auto;font-size:11px;font-variant-numeric:tabular-nums;'
           . 'color:#9ca3af;letter-spacing:.02em}'
           . '@media(max-width:420px){.czc{gap:8px;padding:8px 10px;border-radius:12px}'
           . '.czc__play{width:34px;height:34px;font-size:12px}'
           . '.czc__reloj{display:none}.czc__nombre{font-size:12px}}'
           . '</style>';

    $html .= '<div class="czc" data-czc data-vol="' . e((string)$vol) . '"'
           . ' data-auto="' . (CANCION_PLAYER_AUTOPLAY ? '1' : '0') . '"'
           . ' data-una="' . (CANCION_PLAYER_SOLO_UNA_VEZ ? '1' : '0') . '">'
           . '<button type="button" class="czc__play" data-czc-play aria-label="Reproducir la música">▶</button>'
           . '<div class="czc__cuerpo">'
           // 🗑️ AQUÍ IBA `CANCION_PLAYER_TITULO` («La canción de esta tienda 🎵»). El jefe lo mandó BORRAR
           //    el 2026-09-18: el reproductor se explica solo y el texto sobraba. No volver a ponerlo.
           . '<div class="czc__nombre">' . e($corto) . ' · ' . e($reloj) . '</div>'
           . '<div class="czc__barra"><span class="czc__avance" data-czc-avance></span></div>'
           . '</div>'
           . '<div class="czc__reloj" data-czc-reloj>' . e($reloj) . '</div>'
           // 🔊 ARRANQUE: con «autoplay siempre» (orden del jefe, 2026-09-18) el `<audio>` sale del
           //    HTML ya con `autoplay` + `preload="auto"`: el navegador la pide y la arranca en cuanto
           //    puede, sin esperar al JavaScript. Con `CANCION_PLAYER_SOLO_UNA_VEZ = true` el que
           //    decide es el JavaScript (mira si ese celular ya la oyó), así que aquí no se pone nada.
           . '<audio data-czc-audio preload="' . $preload . '"' . $autoplay . ' src="' . e($src) . '"></audio>'
           . '</div>';

    // El JavaScript, una sola vez por página (la barra se pinta una sola vez, pero por si acaso).
    static $js_puesto = false;
    if (!$js_puesto) {
        $js_puesto = true;
        $html .= <<<'JS'
<script>
(function () {
  var caja = document.querySelector('[data-czc]'); if (!caja) return;
  var audio = caja.querySelector('[data-czc-audio]');
  var boton = caja.querySelector('[data-czc-play]');
  var barra = caja.querySelector('[data-czc-avance]');
  var reloj = caja.querySelector('[data-czc-reloj]');
  if (!audio || !boton) return;

  // 🎚️ VOLUMEN AL 50 % (orden del jefe). Se pone siempre, también al tocar ▶️.
  var VOL = parseFloat(caja.getAttribute('data-vol')) || 0.5;
  audio.volume = VOL;

  // 🎵 ¿ESTE CELULAR YA OYÓ LA CANCIÓN? (orden del jefe: «solo se reproduce 1 vez la primera vez
  //    que entran, no las próximas veces»). Se guarda en el navegador del visitante.
  var AUTO = caja.getAttribute('data-auto') === '1';   // ¿suena sola?
  var UNA  = caja.getAttribute('data-una') === '1';    // ¿solo la primera visita?
  var CLAVE = 'dch_cancion_oida';
  var yaOida = false;
  if (UNA) { try { yaOida = localStorage.getItem(CLAVE) === '1'; } catch (e) { yaOida = false; } }
  function marcarOida() { try { localStorage.setItem(CLAVE, '1'); } catch (e) {} }

  var total = parseFloat(audio.duration || 0) || 0;
  function relojes(t, d) {
    function mmss(s) { s = Math.max(0, Math.floor(s)); return Math.floor(s / 60) + ':' + ('0' + (s % 60)).slice(-2); }
    if (reloj) reloj.textContent = mmss(t) + (d > 0 ? ' / ' + mmss(d) : '');
  }
  function arrancar() {
    // 🧭 EL CHAT MANDA (orden del jefe, 2026-09-17): *«cuando el usuario da clic en el guía, la música
    //    debe parar, porque como el usuario va a grabar, la música estorba»*. Mientras el chat de la
    //    tienda esté abierto, `window.DCH_MUSICA_PARADA` vale true y aquí NO se arranca (ni sola ni
    //    con el primer gesto). Al cerrar el chat la música NO vuelve sola: el visitante le da al ▶.
    if (window.DCH_MUSICA_PARADA) return;
    audio.volume = VOL;
    var p = audio.play();
    if (p && p.catch) { p.catch(function () { /* el navegador no dejó sonar solo: queda el ▶️ */ }); }
  }

  boton.addEventListener('click', function () {
    if (audio.paused) { arrancar(); } else { audio.pause(); }
  });

  /* 🚪 SOLO SUENA CON LA PÁGINA DELANTE (orden del jefe, 2026-09-18, textual: *«solo se debe escuchar
     si el usuario está en la web; si bloquea su celular o se va a otra aplicación, como WhatsApp, ya no
     se debe seguir escuchando»*). En cuanto la página deja de estar a la vista, la canción SE PARA:
       · `visibilitychange` → es el aviso que mandan los navegadores al bloquear el celular, cambiar de
         aplicación o cambiar de pestaña (el caso de WhatsApp incluido);
       · `blur`, `pagehide` y `freeze` → el respaldo para el cambio de ventana en PC y para los móviles
         que no mandan el primer aviso.
     ⚠️ Al volver a la página la canción **NO arranca sola otra vez**: el visitante le da al ▶️ si quiere
     seguir oyéndola (así no lo sorprende la música al volver del chat). */
  function pararPorSalir() { if (!audio.paused) audio.pause(); }
  document.addEventListener('visibilitychange', function () { if (document.hidden) pararPorSalir(); });
  /* ⚠️ El `blur` de la ventana tiene una trampa: al tocar el **mapa de Google** de la ficha (que va en
     un `<iframe>`) el navegador le pasa el foco al iframe y la ventana se "desenfoca"… pero el visitante
     SIGUE en la página. Por eso, si el elemento con el foco es un iframe de la propia página, NO se para
     la música: solo se para cuando de verdad se fue a otra ventana o a otra aplicación. */
  window.addEventListener('blur', function () {
    var foco = document.activeElement;
    if (foco && foco.tagName === 'IFRAME') return;
    pararPorSalir();
  });
  window.addEventListener('pagehide', pararPorSalir);
  document.addEventListener('freeze', pararPorSalir);

  audio.addEventListener('play',  function () { boton.textContent = '❚❚'; boton.classList.add('czc__play--suena'); });
  audio.addEventListener('pause', function () { boton.textContent = '▶';  boton.classList.remove('czc__play--suena'); });
  audio.addEventListener('ended', function () { boton.textContent = '▶';  boton.classList.remove('czc__play--suena'); barra.style.width = '0'; });
  audio.addEventListener('loadedmetadata', function () { total = audio.duration || total; relojes(0, total); });
  audio.addEventListener('timeupdate', function () {
    if (!total) total = audio.duration || 0;
    barra.style.width = (total > 0 ? Math.min(100, (audio.currentTime / total) * 100) : 0) + '%';
    relojes(audio.currentTime, total);
  });
  relojes(0, total);

  if (!AUTO || yaOida) {
    // O el jefe apagó el autoarranque, o **este celular ya la oyó**: la barra se ve y el ▶️ funciona,
    // pero NO suena sola ni se descarga el audio hasta que él la pida (ahorro de datos).
    audio.preload = 'none';
    return;
  }

  // PRIMERA VISITA (o autoplay siempre): se intenta sonar apenas carga la página (el atributo
  // `autoplay` del HTML ya lo pide). Si el navegador lo bloquea (entrada directa, sin ningún clic),
  // NO se pierde: en el primer toque/clic/tecla del visitante en cualquier parte de la ficha se
  // vuelve a intentar —ese gesto ya es el permiso que pide el navegador—, y además se reintenta
  // cuando el audio ya tiene datos (`canplay`) por si el primer intento salió antes de tiempo.
  audio.preload = 'auto';
  var primeraVez = true;
  audio.addEventListener('play', function () {
    if (primeraVez) { primeraVez = false; if (UNA) marcarOida(); }   // ya sonó
  });
  arrancar();
  audio.addEventListener('canplay', function () { if (audio.paused && primeraVez) arrancar(); });

  var gestos = ['pointerdown', 'touchstart', 'click', 'keydown'];
  function primerGesto() {
    if (window.DCH_MUSICA_PARADA) { quitar(); return; }   // 🧭 el chat está abierto: no suena
    if (!audio.paused || !primeraVez) { quitar(); return; }
    arrancar();
    if (!audio.paused) quitar();
  }
  function quitar() { gestos.forEach(function (ev) { document.removeEventListener(ev, primerGesto); }); }
  gestos.forEach(function (ev) { document.addEventListener(ev, primerGesto, { passive: true }); });
})();
</script>
JS;
    }
    return $html;
}
