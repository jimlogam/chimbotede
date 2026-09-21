/* ============================================================================
 * imagen_zoom.js — dechimbote.com
 * ============================================================================
 * QUÉ HACE: en las galerías, la foto se muestra PEQUEÑA (liviana, para móvil) y
 * SOLO se abre en tamaño completo cuando el usuario la toca. Así nadie baja
 * megabytes por gusto.
 *
 * 🔁 ES UNA GALERÍA, NO UNA FOTO SUELTA — arreglo del 2026-09-14 (lo reportó el
 * jefe: *«cuando haces clic en una imagen no puedes deslizar a la derecha o a la
 * izquierda para ver la siguiente imagen; se supone que son un solo bloque de
 * imágenes, una sola galería»*). Al abrir una foto se abre TODA la galería del
 * negocio y se pasa de una a otra:
 *     · deslizando el dedo a la izquierda/derecha  (móvil, lo normal)
 *     · con los botones ‹ ›  (escritorio y móvil)
 *     · con las flechas ← → del teclado
 * Arriba a la izquierda se ve **«3 / 8»** para saber por dónde vas.
 *
 * CÓMO SE USA: cualquier <img> con la clase `cz-zoom` (o con el atributo
 * `data-full`) se vuelve ampliable. El tamaño grande sale de `data-full`; si no
 * está, se usa el `src`.
 *   En PHP:  <?= img_tag($ruta, 'Foto', ['zoom' => true, 'sizes' => '...',
 *                                       'extra' => ['data-galeria' => 'tienda']]) ?>
 * Las fotos que comparten el MISMO valor de `data-galeria` forman UNA sola
 * galería. Se saltan las que están ocultas (las otras plantillas de la ficha
 * viajan en el mismo HTML pero con `display:none`), SALVO las marcadas con
 * `data-galeria-oculta`: esas son las que la grilla RECORTA por diseño (3 × 3 en
 * móvil y 5 × 5 en PC, orden del jefe del 2026-09-18: «para no llenar la página
 * con muchas fotos») y **sí salen en el visor**, porque al tocar una foto se
 * tienen que ver TODAS las de la tienda. Sin `data-galeria`, la foto se abre
 * sola: es el comportamiento de siempre y no se rompe nada.
 *
 * MÓVIL PRIMERO: se cierra con ✕, tocando el fondo, con la tecla Esc o
 * deslizando hacia abajo. Permite el pellizco (pinch) para acercar.
 * ==========================================================================*/
(function () {
  'use strict';

  var overlay = null, imgGrande = null, btnCerrar = null, cargando = null,
      btnAnt = null, btnSig = null, contador = null, pista = null;

  var activo = false;      // ¿está abierto el visor?
  var lista = [];          // los <img> de la galería abierta, en su orden real
  var indice = 0;          // cuál de ellos se está viendo
  var gesto = null;        // el dedo: { x, y, dx, dy, eje }
  var pendiente = null;    // el temporizador del cambio de foto
  var T = 150;             // ms que dura la transición al pasar de foto

  // ==========================================================================
  // Utilidades
  // ==========================================================================

  /** La versión grande de una foto: `data-full` (o el src si no lo trae). */
  function urlCompleta(img) {
    return img.getAttribute('data-full') || img.currentSrc || img.src || '';
  }

  /** ¿Se está viendo de verdad? (descarta las otras plantillas, en display:none) */
  function visible(el) {
    return el.getClientRects().length > 0;
  }

  /**
   * Las fotos que forman la galería de la foto tocada. Es lo que hace que el
   * visor sea UNA galería y no una foto suelta.
   *
   * ✂️ `data-galeria-oculta` (2026-09-18): la grilla de la ficha se RECORTA a 3 × 3 en móvil y 5 × 5
   * en PC (orden del jefe: «para no llenar la página con muchas fotos»), así que las fotos que
   * sobresalen quedan en `display: none`. Esas SÍ entran al visor: el jefe quiere que al tocar
   * cualquiera «ahí sí se vean todas las fotos que podía tener» la tienda. Por eso la regla de saltar
   * las ocultas tiene una excepción: la foto marcada con `data-galeria-oculta` cuenta siempre.
   */
  function fotosDeLaGaleria(img) {
    var grupo = img.getAttribute('data-galeria') || '';
    var candidatas = [];
    var i;

    if (grupo !== '') {
      // ⚠️ El valor del grupo lo pone PHP (negocio.php): es un nombre corto y fijo
      // ('tienda'), nunca algo que venga del visitante.
      var todos = document.querySelectorAll('img[data-galeria="' + grupo + '"]');
      for (i = 0; i < todos.length; i++) {
        if (todos[i] === img || visible(todos[i]) || todos[i].hasAttribute('data-galeria-oculta')) {
          candidatas.push(todos[i]);
        }
      }
    }
    if (candidatas.length === 0) candidatas.push(img);

    // Sin repetidas: una tienda puede tener la misma foto dos veces cargada.
    var limpias = [], urls = [];
    for (i = 0; i < candidatas.length; i++) {
      var u = urlCompleta(candidatas[i]);
      if (u === '' || urls.indexOf(u) !== -1) continue;
      urls.push(u);
      limpias.push(candidatas[i]);
    }
    return limpias.length ? limpias : [img];
  }

  // ==========================================================================
  // El visor (se construye una sola vez, la primera vez que hace falta)
  // ==========================================================================

  function construir() {
    if (overlay) return;

    overlay = document.createElement('div');
    overlay.className = 'cz-zoom-overlay';
    overlay.setAttribute('role', 'dialog');
    overlay.setAttribute('aria-modal', 'true');
    overlay.setAttribute('aria-label', 'Galería de fotos');
    overlay.innerHTML =
      '<button type="button" class="cz-zoom-cerrar" aria-label="Cerrar">✕</button>' +
      '<div class="cz-zoom-contador" aria-live="polite"></div>' +
      '<div class="cz-zoom-cargando" aria-hidden="true">Cargando…</div>' +
      '<button type="button" class="cz-zoom-nav cz-zoom-nav--ant" aria-label="Foto anterior">‹</button>' +
      '<img class="cz-zoom-img" alt="">' +
      '<button type="button" class="cz-zoom-nav cz-zoom-nav--sig" aria-label="Foto siguiente">›</button>' +
      '<div class="cz-zoom-pista"></div>';
    document.body.appendChild(overlay);

    imgGrande = overlay.querySelector('.cz-zoom-img');
    btnCerrar = overlay.querySelector('.cz-zoom-cerrar');
    cargando  = overlay.querySelector('.cz-zoom-cargando');
    btnAnt    = overlay.querySelector('.cz-zoom-nav--ant');
    btnSig    = overlay.querySelector('.cz-zoom-nav--sig');
    contador  = overlay.querySelector('.cz-zoom-contador');
    pista     = overlay.querySelector('.cz-zoom-pista');

    // ---- ✕ y los botones ‹ › (no cierran el visor: paran el clic) ----
    btnCerrar.addEventListener('click', function (e) { e.stopPropagation(); cerrar(); });
    btnAnt.addEventListener('click', function (e) { e.stopPropagation(); ir(indice - 1, -1); });
    btnSig.addEventListener('click', function (e) { e.stopPropagation(); ir(indice + 1, 1); });

    // ---- Tocar el fondo cierra (pero NO la foto ni los botones) ----
    overlay.addEventListener('click', function (e) {
      var t = e.target;
      if (t === overlay || (t.classList && t.classList.contains('cz-zoom-pista'))) cerrar();
    });

    // ---- Gestos: deslizar ← → pasa de foto · deslizar ↓ cierra ----
    overlay.addEventListener('touchstart', function (e) {
      if (e.touches.length !== 1) { gesto = null; return; }
      gesto = { x: e.touches[0].clientX, y: e.touches[0].clientY, dx: 0, dy: 0, eje: '' };
    }, { passive: true });

    overlay.addEventListener('touchmove', function (e) {
      if (!gesto || e.touches.length !== 1) return;
      gesto.dx = e.touches[0].clientX - gesto.x;
      gesto.dy = e.touches[0].clientY - gesto.y;

      // ¿El dedo va de lado o de arriba abajo? Se decide una sola vez por gesto:
      // si no, un deslizamiento diagonal haría las dos cosas a la vez.
      if (gesto.eje === '' && (Math.abs(gesto.dx) > 10 || Math.abs(gesto.dy) > 10)) {
        gesto.eje = Math.abs(gesto.dx) > Math.abs(gesto.dy) ? 'x' : 'y';
      }

      imgGrande.style.transition = 'none';
      if (gesto.eje === 'x') {
        imgGrande.style.transform = 'translateX(' + gesto.dx + 'px)';
        imgGrande.style.opacity = String(Math.max(0.45, 1 - Math.abs(gesto.dx) / 520));
      } else if (gesto.eje === 'y' && gesto.dy > 0) {
        imgGrande.style.transform = 'translateY(' + gesto.dy + 'px)';
        imgGrande.style.opacity = String(Math.max(0.35, 1 - gesto.dy / 420));
      }
    }, { passive: true });

    overlay.addEventListener('touchend', function (e) {
      if (!gesto) return;
      var dx = gesto.dx, dy = gesto.dy, eje = gesto.eje;
      gesto = null;
      soltarFoto();

      if (eje === 'x' && Math.abs(dx) > 45) {
        ir(indice + (dx < 0 ? 1 : -1), dx < 0 ? 1 : -1);   // ← siguiente · → anterior
      } else if (eje === 'y' && dy > 90) {
        cerrar();
      }
    });

    overlay.addEventListener('touchcancel', function () {
      gesto = null;
      soltarFoto();
    });

    // ---- Teclado: Esc cierra · ← → pasan de foto ----
    document.addEventListener('keydown', function (e) {
      if (!activo) return;
      if (e.key === 'Escape' || e.key === 'Esc') { cerrar(); return; }
      if (e.key === 'ArrowLeft')  { e.preventDefault(); ir(indice - 1, -1); return; }
      if (e.key === 'ArrowRight') { e.preventDefault(); ir(indice + 1, 1); }
    });

    // ---- La foto terminó de cargar ----
    imgGrande.addEventListener('load', listo);
    imgGrande.addEventListener('error', function () {
      cargando.style.display = 'block';
      cargando.textContent = 'No se pudo abrir la imagen';
    });
  }

  /** Quita el desplazamiento del dedo (se usa al soltar y antes de cada cambio). */
  function soltarFoto() {
    if (!imgGrande) return;
    imgGrande.style.transition = '';
    imgGrande.style.transform = '';
    imgGrande.style.opacity = '';
  }

  function listo() {
    cargando.style.display = 'none';
    imgGrande.classList.add('cz-zoom-img--lista');
  }

  /** El contador «3 / 8» y la pista de abajo. */
  function pintarPie() {
    var n = lista.length;
    overlay.classList.toggle('cz-zoom-overlay--multiple', n > 1);
    contador.textContent = n > 1 ? (indice + 1) + ' / ' + n : '';
    pista.textContent = n > 1
      ? 'Desliza ← → para ver las demás · ✕ para cerrar'
      : 'Toca el fondo o desliza ↓ para cerrar';
  }

  /** Deja lista la foto de al lado para que el cambio se sienta instantáneo. */
  function precargarVecinas() {
    if (lista.length < 2) return;
    [indice - 1, indice + 1].forEach(function (n) {
      var el = lista[(n + lista.length) % lista.length];
      if (!el) return;
      var u = urlCompleta(el);
      if (u) { var pre = new Image(); pre.src = u; }
    });
  }

  /**
   * Pinta una foto en el visor.
   * @param string url    la versión grande
   * @param string alt    el texto alternativo
   * @param number desdeX de qué lado entra (0 = sin desplazamiento)
   */
  function pintarFoto(url, alt, desdeX) {
    cargando.style.display = 'block';
    cargando.textContent = 'Cargando…';
    imgGrande.classList.remove('cz-zoom-img--lista');
    imgGrande.alt = alt || '';

    imgGrande.style.transition = 'none';
    imgGrande.style.transform = desdeX ? 'translateX(' + desdeX + 'px)' : '';
    imgGrande.style.opacity = '';
    imgGrande.src = url;
    void imgGrande.offsetWidth;              // reflujo: así la transición corre de verdad
    imgGrande.style.transition = '';

    pintarPie();

    // Si ya estaba en la caché del navegador, el «load» no vuelve a dispararse
    if (imgGrande.complete && imgGrande.naturalWidth > 0) listo();
    precargarVecinas();
  }

  /** Pasa a otra foto de la galería (da la vuelta: de la última a la primera). */
  function ir(n, dir) {
    if (lista.length < 2) return;
    var d = dir < 0 ? -1 : 1;
    indice = ((n % lista.length) + lista.length) % lista.length;

    if (pendiente) { clearTimeout(pendiente); pendiente = null; }
    // La que estaba se va hacia el lado contrario del que llega
    imgGrande.classList.remove('cz-zoom-img--lista');
    imgGrande.style.transform = 'translateX(' + (d > 0 ? -34 : 34) + 'px)';

    pendiente = setTimeout(function () {
      pendiente = null;
      var el = lista[indice];
      if (!el) return;
      pintarFoto(urlCompleta(el), el.getAttribute('alt') || '', d > 0 ? 34 : -34);
    }, T);
  }

  /** Abre el visor con la galería a la que pertenece esa foto. */
  function abrirDesdeImg(img) {
    lista = fotosDeLaGaleria(img);

    // En qué puesto de la galería está la foto que tocó
    var url = urlCompleta(img);
    indice = 0;
    for (var i = 0; i < lista.length; i++) {
      if (urlCompleta(lista[i]) === url) { indice = i; break; }
    }

    construir();
    activo = true;
    document.body.classList.add('cz-zoom-abierto');
    overlay.classList.add('cz-zoom-overlay--visible');
    pintarFoto(url, img.getAttribute('alt') || '', 0);
  }

  /** Abre una sola foto por su URL (compatibilidad con window.CZZoom.abrir). */
  function abrirSuelta(url, alt) {
    lista = [];
    indice = 0;
    construir();
    activo = true;
    document.body.classList.add('cz-zoom-abierto');
    overlay.classList.add('cz-zoom-overlay--visible');
    pintarFoto(String(url || ''), alt || '', 0);
  }

  /** Se puede llamar con la foto (<img>) o con su URL. */
  function abrir(algo, alt) {
    if (algo && algo.tagName === 'IMG') { abrirDesdeImg(algo); return; }
    abrirSuelta(algo, alt);
  }

  function cerrar() {
    if (!overlay) return;
    if (pendiente) { clearTimeout(pendiente); pendiente = null; }
    gesto = null;
    overlay.classList.remove('cz-zoom-overlay--visible');
    overlay.classList.remove('cz-zoom-overlay--multiple');
    document.body.classList.remove('cz-zoom-abierto');
    activo = false;
    setTimeout(function () {
      if (!activo && imgGrande) {
        imgGrande.removeAttribute('src');
        imgGrande.classList.remove('cz-zoom-img--lista');
      }
    }, 250);
  }

  // ==========================================================================
  // Enganche: cualquier img.cz-zoom o [data-full] de la página
  // ==========================================================================

  document.addEventListener('click', function (e) {
    var t = e.target;
    if (!t || t.tagName !== 'IMG') return;
    if (!t.classList.contains('cz-zoom') && !t.hasAttribute('data-full')) return;
    if (!urlCompleta(t)) return;
    e.preventDefault();
    abrirDesdeImg(t);
  });

  // Las fotos se abren también con el teclado (Tab + Enter): el visor se usa sin ratón.
  function hacerAccesibles() {
    var fotos = document.querySelectorAll('img.cz-zoom, img[data-full]');
    for (var i = 0; i < fotos.length; i++) {
      var img = fotos[i];
      if (img.getAttribute('tabindex') === null) {
        img.setAttribute('tabindex', '0');
        img.setAttribute('role', 'button');
      }
      img.addEventListener('keydown', function (ev) {
        if (ev.key !== 'Enter' && ev.key !== ' ') return;
        ev.preventDefault();
        abrirDesdeImg(ev.currentTarget);
      });
    }
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', hacerAccesibles);
  } else {
    hacerAccesibles();
  }

  window.CZZoom = { abrir: abrir, cerrar: cerrar, ir: ir };
})();
