/* llamadas.js — 📞 MIDE EL BOTÓN «LLAMAR» (y los WhatsApp directos del copy)
 * ==========================================================================================
 * Pedido del jefe (2026-09-14): quiere saber «las tiendas que están recibiendo más clics en el
 * botón de llamada». Hasta entonces ese botón era un `<a href="tel:…">` puro: el navegador abría
 * el marcador y el clic NO pasaba por el servidor, así que el dato no existía en ninguna tabla.
 *
 * Qué hace este archivo (y qué NO hace):
 *   · Escucha el TOQUE DE VERDAD en cualquier enlace `tel:` (los de la ficha y los que el copy de
 *     una tienda pinta como botón) y en los enlaces directos a `wa.me` que NO pasan por
 *     `api/lead.php` (los del copy de la descripción).
 *   · Manda un «beacon» a `/api/llamada.php` (asíncrono, sin bloquear nada) y DEJA SEGUIR el
 *     enlace: el teléfono se marca igual y el WhatsApp se abre igual. El visitante no nota nada.
 *   · No toca los botones que ya se cuentan solos (`api/lead.php`, el carrito 🛒): esos los
 *     registra el servidor y contarlos también aquí sería contarlos dos veces. Lo que SÍ hace con
 *     ellos es pegarles el marcador **`&c=1`** en el momento del clic — la prueba de que hubo un
 *     clic de verdad que `api/lead.php` exige desde el 2026-09-16 (ver allí el porqué).
 *
 * 🐛 ARREGLO DE LOS FALSOS POSITIVOS (2026-09-16) — LO QUE ESTABA MAL:
 *   Antes este archivo contaba el **`pointerdown`**: bastaba con que el dedo TOCARA el botón (para
 *   empezar a deslizar la pantalla, o para mirar, o en un toque que el navegador acabó cancelando)
 *   y ya salía «📞 TOCARON LLAMAR EN UNA FICHA» o «🔥 PIDIERON PRECIO» sin que nadie hubiera
 *   llamado ni escrito. Eso es exactamente lo que el jefe reportó como falso positivo.
 *   Ahora solo cuentan:
 *     · el **`click`** — un toque/ pulsación de verdad, con el dedo levantado encima del enlace; y
 *     · un **`pointerup` de respaldo** (para los navegadores que no sueltan `click` en los enlaces
 *       `tel:`, que abren app externa) **y solo si** el dedo no se movió más de `PX_MAX` px y
 *       estuvo pulsado menos de `MS_MAX` ms.
 *   Resultado: un ARRASTRE (scroll) o una PULSACIÓN LARGA (menú de copiar/abrir en otra pestaña) ya
 *   NO cuentan como clic. Si el `click` llega después del `pointerup`, el dedupe de abajo lo para.
 *
 * El id de la tienda sale de `data-negocio` en el enlace o, si no está, del bloque
 * `<div data-negocio-id="…">` que pinta negocio.php. Sin id no se manda nada (nunca un dato falso).
 */
(function () {
  'use strict';

  var yaEnviados = {};
  var PX_MAX = 12;     // px: moverse más que esto es un arrastre (scroll), no un toque
  var MS_MAX = 1500;   // ms: estar más que esto pulsando es una pulsación larga, no un toque
  var toque = null;    // {x, y, t} del pointerdown en curso

  function idNegocio(a) {
    if (a && a.getAttribute) {
      var propio = a.getAttribute('data-negocio');
      if (propio) return propio;
    }
    var bloque = document.querySelector('[data-negocio-id]');
    return bloque ? (bloque.getAttribute('data-negocio-id') || '') : '';
  }

  function avisar(a, tipo) {
    var n = idNegocio(a);
    if (!n) return;

    var datos = 'n=' + encodeURIComponent(n)
              + '&tipo=' + encodeURIComponent(tipo)
              + '&u=' + encodeURIComponent(window.location.href);
    var producto = a.getAttribute('data-producto');
    if (producto) datos += '&p=' + encodeURIComponent(producto);

    // Una sola vez por enlace y por carga de página (el toque en móvil puede disparar
    // pointerup y click: sin esto se contarían dos clics de un solo dedo).
    var clave = tipo + '|' + n + '|' + (a.getAttribute('href') || '');
    if (yaEnviados[clave]) return;
    yaEnviados[clave] = 1;

    var url = '/api/llamada.php';
    try {
      if (navigator.sendBeacon) {
        navigator.sendBeacon(url, new Blob([datos], { type: 'application/x-www-form-urlencoded' }));
        return;
      }
    } catch (e) { /* sin sendBeacon: se intenta por imagen (abajo) */ }

    try {
      var img = new Image();
      img.src = url + '?' + datos + '&_=' + Date.now();
    } catch (e) { /* si ni eso: el clic sigue funcionando, solo no se mide */ }
  }

  /* 🖱️ Deja la marca `&c=1` en el enlace del contador de pedidos: es la prueba de clic que exige
     `api/lead.php`. Se pega SOLO aquí, dentro del clic de verdad. */
  function marcarClic(a) {
    var h = a.getAttribute('href') || '';
    if (h.indexOf('api/lead.php') === -1) return;
    if (/([?&])c=1(&|$)/.test(h)) return;
    a.setAttribute('href', h + (h.indexOf('?') === -1 ? '?' : '&') + 'c=1');
  }

  function revisar(ev) {
    var a = ev.target;
    // Subir hasta el enlace (el toque cae en el <span> de dentro del botón).
    while (a && a !== document && a.tagName !== 'A') a = a.parentNode;
    if (!a || a.tagName !== 'A') return;

    var href = a.getAttribute('href') || '';

    // 📞 Llamar (ficha, ficha-C, empleos y los botones del copy).
    if (href.indexOf('tel:') === 0) { avisar(a, 'llamada'); return; }

    // 💬 WhatsApp del botón de la ficha: lo cuenta el SERVIDOR (`api/lead.php`). Aquí solo se le
    // pega la marca del clic de verdad; sin ella el servidor no lo cuenta (y así los robots que
    // siguen el enlace dejan de inventar pedidos).
    if (href.indexOf('api/lead.php') !== -1) { marcarClic(a); return; }
    if (href.indexOf('/carrito') === 0) return;
    if (href.indexOf('wa.me') !== -1 || href.indexOf('api.whatsapp.com') !== -1
        || href.indexOf('web.whatsapp.com') !== -1) {
      avisar(a, 'whatsapp');
    }
  }

  // Se guarda dónde empezó el toque: sirve para saber si fue un toque o un arrastre.
  document.addEventListener('pointerdown', function (ev) {
    toque = { x: ev.clientX, y: ev.clientY, t: Date.now() };
  }, true);

  // Respaldo para los navegadores que, al abrir el marcador o WhatsApp, no sueltan `click`.
  document.addEventListener('pointerup', function (ev) {
    var t = toque;
    toque = null;
    if (!t) return;
    var dx = ev.clientX - t.x, dy = ev.clientY - t.y;
    if (Math.sqrt(dx * dx + dy * dy) > PX_MAX) return;   // se movió: era scroll
    if (Date.now() - t.t > MS_MAX) return;               // demasiado rato: pulsación larga
    revisar(ev);
  }, true);

  // El clic de verdad, en captura: se entera ANTES de que el navegador abra el marcador.
  document.addEventListener('click', revisar, true);
})();
