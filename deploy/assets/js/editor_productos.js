/* ============================================================================
 * assets/js/editor_productos.js — MOTOR DEL EDITOR MASIVO DE PRODUCTOS
 * ----------------------------------------------------------------------------
 * Página: editaproductos.php (solo el jefe/admin · herramienta de ESCRITORIO).
 *
 * Qué resuelve (pedido del jefe, 2026-09-11):
 *   1) ⛔ **NADA RECARGA LA PÁGINA**: guardar el precio, la unidad, el prompt o
 *      publicar una imagen se hace con fetch/XHR (JSON). El scroll se queda
 *      donde estaba (antes, trabajando el producto 22 o 27, cada cambio te
 *      devolvía al principio de la página).
 *   2) 🖼️ **Arrastrar y soltar la imagen ENCIMA de la foto**: al soltarla se
 *      publica AL INSTANTE (auto-guardado), con vista previa inmediata. También
 *      sirve **Ctrl+V** con una imagen copiada (p. ej. desde Gemini) y el
 *      respaldo "elige el archivo".
 *   3) 🏷️ **Select de UNIDAD predictivo con FUZZY** (Fuse.js, el mismo motor del
 *      buscador del sitio): escribe "kil" y aparecen «por kilo» / «por
 *      kilómetro»; tolera tildes y errores de tipeo. Sin Fuse.js cae a una
 *      búsqueda por trozos (nunca se queda sin sugerencias).
 *   4) 💾 **Botón Guardar por producto** (precio + unidad). Si hay cambios sin
 *      guardar, el botón se pone ámbar y, si intentas cerrar, el navegador avisa.
 *   5) ↩️ **Deshacer** el último cambio de imagen (por si el arrastre fue al
 *      producto equivocado).
 *
 * Reglas de la casa que respeta:
 *   · La imagen SIEMPRE se comprime antes de subir (CZImg.optimizar de
 *     imagen_optimizar.js) y el servidor la vuelve a validar (WebP + 300/800).
 *   · UX predictiva (Regla de Oro n.º 2): lista completa al abrir, filtra
 *     mientras escribes, Enter elige la primera, Esc cierra, píldora verde
 *     confirma lo elegido.
 *   · Si el guardado silencioso falla, el formulario clásico sigue ahí como
 *     respaldo (nunca se pierde un cambio).
 * ========================================================================== */
(function () {
  'use strict';

  var OPCIONES   = Array.isArray(window.EP_UNIDADES) ? window.EP_UNIDADES : [];   // compatibilidad
  /** El input de la UNIDAD del producto (en el editor de tiendas no existe). */
  function inputUnidad(card) {
    return card ? qs('.ep-combo[data-campo="unidad"] input', card) : null;
  }
  var URL_ACCION = window.EP_URL_ACCION || 'editaproductos.php';
  var MAX_SUG    = 40;      // tope de sugerencias visibles

  // ------------------------------------------------------------------ utils
  function qs(s, r) { return (r || document).querySelector(s); }
  function qsa(s, r) { return Array.prototype.slice.call((r || document).querySelectorAll(s)); }

  function esc(t) {
    return String(t == null ? '' : t).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  /** Igual que la normalización del servidor: minúsculas y sin tildes (1 carácter → 1 carácter). */
  function normaliza(t) {
    return String(t || '').toLowerCase()
      .replace(/[áàäâã]/g, 'a').replace(/[éèëê]/g, 'e').replace(/[íìïî]/g, 'i')
      .replace(/[óòöô]/g, 'o').replace(/[úùüû]/g, 'u').replace(/ñ/g, 'n');
  }

  function miles(n) {
    n = parseInt(n, 10);
    if (isNaN(n)) return '0';
    return n.toLocaleString('en-US');
  }

  function csrf() {
    var i = qs('input[name="_csrf"]');
    return i ? i.value : '';
  }

  function aviso(txt) {
    if (window.CZImg && window.CZImg.aviso) window.CZImg.aviso(txt);
  }

  /** POST que contesta JSON (fetch). `datos` puede ser un objeto o un FormData. */
  function peticion(datos, cb) {
    var fd;
    if (datos instanceof FormData) { fd = datos; }
    else {
      fd = new FormData();
      Object.keys(datos || {}).forEach(function (k) { fd.append(k, datos[k]); });
    }
    fd.set('ajax', '1');
    fetch(URL_ACCION, {
      method: 'POST', body: fd, credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
      .then(function (r) { return r.json(); })
      .then(function (j) { cb(j, null); })
      .catch(function (e) { cb(null, e); });
  }

  /** Mensaje de estado dentro de la tarjeta (nunca un alert). */
  function estado(card, texto, tipo, ms) {
    var el = qs('[data-estado]', card);
    if (!el) return;
    el.hidden = false;
    el.className = 'ep-estado ep-estado--' + (tipo === 'error' ? 'error' : (tipo === 'curso' ? 'curso' : 'ok'));
    el.textContent = texto;
    if (el.__t) clearTimeout(el.__t);
    if (tipo !== 'curso') {
      el.__t = setTimeout(function () { el.hidden = true; }, ms || 7000);
    }
  }

  function pintarContadores(c) {
    if (!c) return;
    if (typeof c.con_prompt === 'number') {
      var cp = qs('[data-cont="con_prompt"]');
      if (cp) cp.textContent = miles(c.con_prompt);
      var tp = qs('[data-cont="total"]');
      var sp = qs('[data-cont="sin_prompt"]');
      if (tp && sp) sp.textContent = miles(parseInt(String(tp.textContent).replace(/[^0-9]/g, ''), 10) - c.con_prompt);
    }
    if (typeof c.con_imagen === 'number') {
      var ci = qs('[data-cont="con_imagen"]');
      if (ci) ci.textContent = miles(c.con_imagen);
    }
  }

  /* ======================================================== 1) COMBOBOX FUZZY
   * Sirve para VARIAS listas: la página declara sus listas en `window.EP_LISTAS`
   * (p. ej. { unidades: [...], rubros: [...] }) y cada input elige la suya con
   * `data-ep-lista="rubros"`. Sin atributo se usa la lista por defecto
   * (window.EP_UNIDADES, la del editor de productos).
   */
  var LISTAS = (window.EP_LISTAS && typeof window.EP_LISTAS === 'object') ? window.EP_LISTAS : {};
  var LISTA_DEFECTO = Array.isArray(window.EP_UNIDADES)
      ? window.EP_UNIDADES
      : (Array.isArray(LISTAS.unidades) ? LISTAS.unidades : []);
  var fuses = {};   // un índice por lista

  /** ¿Qué lista usa este input? */
  function listaDe(input) {
    var clave = input ? String(input.getAttribute('data-ep-lista') || '') : '';
    if (clave !== '' && Array.isArray(LISTAS[clave])) return { clave: clave, opciones: LISTAS[clave] };
    return { clave: 'defecto', opciones: LISTA_DEFECTO };
  }

  function prepararFuse(clave, opciones) {
    clave = clave || 'defecto';
    opciones = opciones || LISTA_DEFECTO;
    if (fuses[clave]) return fuses[clave];
    if (typeof window.Fuse !== 'function' || !opciones.length) return null;
    fuses[clave] = new window.Fuse(opciones, {
      keys: ['n'],
      threshold: 0.35,          // punto dulce para español (igual que el buscador del sitio)
      minMatchCharLength: 1,
      includeScore: true,
      includeMatches: true,
      ignoreLocation: true,     // que encuentre la palabra en cualquier parte
      ignoreAccents: true,
      ignoreFieldNorm: true
    });
    return fuses[clave];
  }

  /** Respaldo sin Fuse.js: coincidencia por trozos sobre el texto normalizado. */
  function filtrarSimple(q, opciones) {
    opciones = opciones || LISTA_DEFECTO;
    var nq = normaliza(q);
    var out = [];
    for (var i = 0; i < opciones.length && out.length < MAX_SUG; i++) {
      var pos = opciones[i].n.indexOf(nq);
      if (pos !== -1) out.push({ o: opciones[i], marcas: [[pos, pos + nq.length - 1]] });
    }
    return out;
  }

  function resaltar(texto, marcas) {
    if (!marcas || !marcas.length) return esc(texto);
    var tramos = marcas.slice().sort(function (a, b) { return a[0] - b[0]; });
    var salida = '', pos = 0;
    tramos.forEach(function (t) {
      var ini = t[0], fin = t[1];
      if (ini < pos || fin < ini) return;
      salida += esc(texto.slice(pos, ini)) + '<em>' + esc(texto.slice(ini, fin + 1)) + '</em>';
      pos = fin + 1;
    });
    salida += esc(texto.slice(pos));
    return salida;
  }

  /** Arma la lista de filas: sin texto = TODAS agrupadas y alfabéticas; con texto = fuzzy. */
  function filas(q, clave, opciones) {
    var lista = [];
    opciones = opciones || LISTA_DEFECTO;
    q = String(q || '').trim();
    if (q === '') {
      var grupos = {}, orden = [];
      opciones.forEach(function (o) {
        if (!grupos[o.g]) { grupos[o.g] = []; orden.push(o.g); }
        grupos[o.g].push(o);
      });
      orden.forEach(function (g) {
        grupos[g].sort(function (a, b) { return a.u.localeCompare(b.u, 'es'); });
        lista.push({ grupo: g });
        grupos[g].forEach(function (o) { lista.push({ o: o }); });
      });
      return lista;
    }
    var f = prepararFuse(clave, opciones);
    if (f) {
      f.search(q, { limit: MAX_SUG }).forEach(function (r) {
        var marcas = (r.matches && r.matches[0] && r.matches[0].indices) ? r.matches[0].indices : [];
        lista.push({ o: r.item, marcas: marcas });
      });
    } else {
      lista = filtrarSimple(q, opciones);
    }
    return lista;
  }

  function pintarLista(c, q) {
    var lista = filas(q, c.clave, c.opciones);
    if (!lista.length) {
      c.lista.innerHTML = '<div class="ep-combo__vacio">'
        + (c.clave === 'rubros'
            ? 'Ningún rubro coincide. Revisa cómo se llama el rubro en el directorio.'
            : 'Ninguna unidad coincide. Puedes escribirla tal cual y guardarla.')
        + '</div>';
      c.lista.hidden = false;
      c.items = [];
      return;
    }
    var html = '';
    lista.forEach(function (fila, i) {
      if (fila.grupo) { html += '<div class="ep-combo__grupo">' + esc(fila.grupo) + '</div>'; return; }
      html += '<button type="button" class="ep-combo__item" data-i="' + i + '" data-u="' + esc(fila.o.u) + '">'
            + resaltar(fila.o.u, fila.marcas) + '</button>';
    });
    c.lista.innerHTML = html;
    c.lista.hidden = false;
    c.sel = -1;
  }

  function cerrarCombo(c) { c.lista.hidden = true; c.sel = -1; }

  function elegirUnidad(c, valor) {
    c.input.value = valor;
    c.input.dispatchEvent(new Event('input', { bubbles: true }));
    c.input.dispatchEvent(new Event('change', { bubbles: true }));
    cerrarCombo(c);
    var card = c.input.closest('.ep-card');
    if (card) {
      refrescarSucio(card);
      // 🏷️ Los combos de RUBRO guardan los rubros (acción propia);
      // el resto son la unidad del producto (precio + unidad).
      if (esComboRubro(c.input)) guardarRubros(card);
      else autoguardar(card);            // ⚡ la unidad se guarda sola
    }
  }

  /** ¿Este input es un bloque de rubro? (lleva data-campo="rubro") */
  function esComboRubro(input) {
    return !!input && input.getAttribute('data-campo') === 'rubro';
  }

  /** Guarda los rubros de una tienda (hasta 4) — en vivo, sin recargar. */
  function guardarRubros(card) {
    if (!card) return;
    var inputs = qsa('.ep-rubro', card);
    if (!inputs.length) return;

    // Si nada cambió desde el último guardado, no se llama al servidor.
    var firma = inputs.map(function (i) { return String(i.value || '').trim(); }).join('|');
    if (card.getAttribute('data-rubros-firma') === firma) return;
    // Un guardado a la vez; si mientras tanto hubo otro cambio, se repite al terminar.
    if (card.__epRubrosOcupado) { card.__epRubrosPend = true; return; }
    card.__epRubrosOcupado = true;

    var fd = new FormData();
    fd.set('accion', 'guardar_rubros');
    fd.set('producto_id', card.getAttribute('data-producto'));
    fd.set('_csrf', csrf());
    inputs.forEach(function (inp, i) {
      var slot = inp.getAttribute('data-slot') || String(i + 1);
      fd.set('rubro' + slot, String(inp.value || '').trim());
    });

    var b = qs('[data-guardar-rubros]', card);
    if (b) { b.disabled = true; b.textContent = '⏳ Guardando…'; }
    estado(card, '⏳ Guardando los rubros…', 'curso');

    peticion(fd, function (j) {
      card.__epRubrosOcupado = false;
      if (b) { b.disabled = false; b.textContent = '💾 Guardar rubros'; }
      if (!j || !j.ok) {
        estado(card, (j && j.mensaje) || 'No se pudieron guardar los rubros.', 'error');
        return;
      }
      card.setAttribute('data-rubros-firma', firma);
      if (j.rubros_txt) {
        var pill = qs('[data-pill-rubros]', card);
        if (pill) { pill.hidden = false; pill.textContent = '🏷️ ' + j.rubros_txt; }
        var chip = qs('[data-vista-rubro]', card);
        if (chip) {
          var ic = (j.rubros && j.rubros[0] && j.rubros[0].icono) ? j.rubros[0].icono + ' ' : '';
          chip.textContent = '🏷️ ' + ic + j.rubros_txt;
        }
      }
      estado(card, j.mensaje, 'ok');
      aviso(j.mensaje);
      if (card.__epRubrosPend) { card.__epRubrosPend = false; guardarRubros(card); }
    });
  }

  function moverSel(c, paso) {
    var nodos = qsa('.ep-combo__item', c.lista);
    if (!nodos.length) return false;
    c.sel = (c.sel + paso + nodos.length + 1) % (nodos.length + 1);
    nodos.forEach(function (n, i) { n.classList.toggle('ep-combo__item--sel', i === c.sel); });
    if (c.sel >= 0 && nodos[c.sel]) {
      var y = nodos[c.sel].offsetTop, h = c.lista.clientHeight;
      if (y < c.lista.scrollTop) c.lista.scrollTop = y;
      else if (y + nodos[c.sel].offsetHeight > c.lista.scrollTop + h) c.lista.scrollTop = y + nodos[c.sel].offsetHeight - h;
    }
    return true;
  }

  function engancharCombo(input) {
    if (!input || input.__epCombo) return;
    input.__epCombo = true;
    var cont = input.closest('.ep-combo');
    if (!cont) return;
    var lista = qs('.ep-combo__lista', cont);
    if (!lista) return;
    var base = listaDe(input);
    var c = { input: input, lista: lista, sel: -1, timer: null, clave: base.clave, opciones: base.opciones };

    input.setAttribute('autocomplete', 'off');
    input.addEventListener('focus', function () { pintarLista(c, ''); });
    input.addEventListener('click', function () { if (lista.hidden) pintarLista(c, input.value === input.defaultValue ? '' : input.value); });
    input.addEventListener('input', function () {
      if (c.timer) clearTimeout(c.timer);
      var q = input.value;
      c.timer = setTimeout(function () { pintarLista(c, q); }, 90);
    });
    input.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowDown') { if (lista.hidden) pintarLista(c, ''); if (moverSel(c, 1)) e.preventDefault(); }
      else if (e.key === 'ArrowUp') { if (moverSel(c, -1)) e.preventDefault(); }
      else if (e.key === 'Escape') { cerrarCombo(c); }
      else if (e.key === 'Enter') {
        var nodos = qsa('.ep-combo__item', lista);
        if (!lista.hidden && nodos.length) {
          e.preventDefault();
          var idx = (c.sel >= 0) ? c.sel : 0;
          if (nodos[idx]) elegirUnidad(c, nodos[idx].getAttribute('data-u'));
        }
      } else if (e.key === 'Tab') { cerrarCombo(c); }
    });
    input.addEventListener('blur', function () { setTimeout(function () { cerrarCombo(c); }, 180); });

    lista.addEventListener('mousedown', function (e) {
      var b = e.target.closest ? e.target.closest('.ep-combo__item') : null;
      if (!b) return;
      e.preventDefault();
      elegirUnidad(c, b.getAttribute('data-u'));
    });
    document.addEventListener('mousedown', function (e) {
      if (!cont.contains(e.target)) cerrarCombo(c);
    });
  }

  /* ================================================ 2) PRECIO + UNIDAD + GUARDAR */
  function precioDe(card) {
    var pi = qs('input[type="number"]', card);
    var v = parseFloat(String(pi ? pi.value : '0').replace(',', '.'));
    if (isNaN(v) || v < 0) v = 0;
    return Math.round(v * 100) / 100;
  }

  function estaSucio(card) {
    var ui = inputUnidad(card);
    var guardadoP = parseFloat(card.getAttribute('data-precio') || '0');
    var guardadoU = String(card.getAttribute('data-unidad') || '').trim();
    var ahoraU = String(ui ? ui.value : '').trim();
    return (Math.abs(precioDe(card) - guardadoP) > 0.0001) || (ahoraU !== guardadoU);
  }

  function refrescarSucio(card) {
    var sucio = estaSucio(card);
    card.classList.toggle('ep-card--sucio', sucio);

    var b = qs('[data-guardar]', card);
    if (b && !b.dataset.ocupado) {
      b.textContent = sucio ? '💾 Guardar · cambios sin guardar' : '💾 Guardar';
      b.classList.toggle('ep-btn--pide', sucio);
    }

    var pill = qs('[data-pill]', card);
    if (pill) {
      var guardadoU = String(card.getAttribute('data-unidad') || '').trim();
      if (guardadoU === '') { pill.hidden = true; pill.innerHTML = ''; }
      else {
        pill.hidden = false;
        pill.innerHTML = '🏷️ Guardado como: <b>' + esc(guardadoU) + '</b>'
                       + (sucio ? ' <button type="button" data-reset-unidad="1" title="Volver a la unidad guardada">✕</button>' : '');
      }
    }
    return sucio;
  }

  /**
   * Guarda precio + unidad de un producto.
   * @param {boolean} esAuto  true = lo disparó el guardado automático (al salir
   *   del campo del precio o al elegir la unidad). Entonces NO se toca el botón
   *   (no parpadea) y el aviso es cortito; si falla, el botón queda ámbar para
   *   que el jefe lo vea y lo pueda forzar a mano.
   */
  function guardarDatos(card, esAuto) {
    var pid = card.getAttribute('data-producto');
    var ui = inputUnidad(card);
    var b = qs('[data-guardar]', card);

    // Si ya hay un guardado en vuelo, se anota que hay que volver a guardar al
    // terminar (así el último cambio del jefe nunca se pierde).
    if (card.__epGuardando) { if (esAuto) card.__epPendiente = true; return; }
    // Nada cambió: no se llama al servidor (el botón manual solo avisa).
    if (!estaSucio(card)) {
      if (!esAuto) estado(card, 'No hay cambios que guardar.', 'ok', 2500);
      return;
    }
    card.__epGuardando = true;
    if (b && !esAuto) { b.dataset.ocupado = '1'; b.disabled = true; b.textContent = '⏳ Guardando…'; }

    peticion({
      accion: 'guardar_datos', producto_id: pid, _csrf: csrf(),
      precio: String(precioDe(card)), unidad: String(ui ? ui.value : '')
    }, function (j) {
      card.__epGuardando = false;
      if (b && !esAuto) { delete b.dataset.ocupado; b.disabled = false; }
      if (!j || !j.ok) {
        if (b) { delete b.dataset.ocupado; b.disabled = false; b.textContent = '💾 Guardar · cambios sin guardar'; b.classList.add('ep-btn--pide'); }
        estado(card, (j && j.mensaje) || 'No se pudo guardar (revisa la conexión).', 'error');
        return;
      }
      card.setAttribute('data-precio', j.precio_vista);
      card.setAttribute('data-unidad', j.unidad || '');
      var pi = qs('input[type="number"]', card);
      if (pi) pi.value = j.precio_vista;
      if (ui) ui.defaultValue = ui.value;
      var chip = qs('[data-vista-precio]', card);
      if (chip) chip.textContent = 'S/ ' + j.precio_vista + (j.unidad ? ' ' + j.unidad : '');
      refrescarSucio(card);
      if (b && !esAuto) {
        b.textContent = '✅ Guardado';
        setTimeout(function () { refrescarSucio(card); }, 1500);
        estado(card, j.mensaje, 'ok');
      } else {
        estado(card, '💾 Guardado solo · S/ ' + j.precio_vista + (j.unidad ? ' ' + j.unidad : ''), 'ok', 2600);
      }
      // Si mientras guardaba hubo otro cambio, se guarda otra vez
      if (card.__epPendiente) { card.__epPendiente = false; guardarDatos(card, true); }
    });
  }

  /** Guardado automático: solo si de verdad hay algo distinto de lo guardado. */
  function autoguardar(card) {
    if (!card || !estaSucio(card)) return;
    guardarDatos(card, true);
  }

  /* ============================================ 3) LA IMAGEN: ARRASTRAR / PEGAR */
  function ponerBotonDeshacer(card) {
    if (qs('[data-deshacer]', card)) return;
    var ref = qs('[data-estado]', card);
    if (!ref) return;
    var b = document.createElement('button');
    b.type = 'button';
    b.className = 'ep-btn ep-btn--suave ep-btn--mini';
    b.setAttribute('data-deshacer', card.getAttribute('data-producto'));
    b.style.marginTop = '8px';
    b.textContent = '↩️ Deshacer el último cambio de imagen';
    b.addEventListener('click', function () { deshacerImagen(card); });
    ref.parentNode.insertBefore(b, ref.nextSibling);
  }

  function subirImagen(card, archivo, urlPrevia) {
    var pid    = card.getAttribute('data-producto');
    var marco  = qs('[data-marco]', card);
    var img    = qs('img[data-ruta]', card);
    var sello  = qs('[data-sello]', card);
    var prog   = qs('[data-progreso]', card);
    var barra  = prog ? qs('i', prog) : null;
    var form   = qs('.ep-form-foto', card);

    var fd = form ? new FormData(form) : new FormData();
    fd.set('accion', 'cambiar_imagen');
    fd.set('producto_id', pid);
    fd.set('_csrf', csrf());
    fd.set('ajax', '1');
    fd.set('foto', archivo, archivo.name || 'imagen.webp');

    var xhr = new XMLHttpRequest();
    xhr.open('POST', URL_ACCION, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.withCredentials = true;

    if (xhr.upload && barra) {
      xhr.upload.onprogress = function (e) {
        if (e.lengthComputable) barra.style.width = Math.round((e.loaded / e.total) * 100) + '%';
      };
    }
    xhr.onload = function () {
      var j = null;
      try { j = JSON.parse(xhr.responseText); } catch (e) { j = null; }
      if (sello) sello.hidden = true;
      if (prog) { prog.classList.remove('ep-progreso--on'); if (barra) barra.style.width = '0'; }
      if (!j || !j.ok) {
        // Se devuelve la vista previa a la foto que había
        if (img && urlPrevia !== null) { img.src = urlPrevia; }
        estado(card, (j && j.mensaje) || ('No se pudo publicar la imagen (HTTP ' + xhr.status + ').'), 'error');
        return;
      }
      if (img) {
        if (!img.getAttribute('data-ruta')) {
          // Estaba "sin imagen": ya se creó el <img> en la vista previa
          img.setAttribute('data-ruta', j.ruta);
          img.setAttribute('loading', 'lazy');
          img.setAttribute('decoding', 'async');
        }
        img.src = j.url;
        if (j.srcset) { img.setAttribute('srcset', j.srcset); img.setAttribute('sizes', window.EP_SIZES_FOTO || '330px'); }
        else { img.removeAttribute('srcset'); img.removeAttribute('sizes'); }
        if (j.url_full) img.setAttribute('data-full', j.url_full);
        img.setAttribute('data-ruta', j.ruta);
      }
      var pie = qs('.ep-foto__pie', card);
      if (pie) pie.textContent = j.ruta;
      var chipFoto = qs('[data-vista-foto]', card);
      if (chipFoto) {
        // Si la tarjeta ya decía "Con portada (N fotos)" se respeta ese texto;
        // solo se cambia cuando venía de "⬜ Sin imagen / Sin portada".
        var yaTenia = chipFoto.classList.contains('ep-chipmini--ok');
        chipFoto.className = 'ep-chipmini ep-chipmini--ok';
        if (!yaTenia) chipFoto.textContent = '🖼️ Con imagen';
      }
      if (j.anterior) ponerBotonDeshacer(card);
      pintarContadores(j.contadores);
      estado(card, j.mensaje, 'ok');
      aviso(j.mensaje);
    };
    xhr.onerror = function () {
      if (sello) sello.hidden = true;
      if (prog) prog.classList.remove('ep-progreso--on');
      estado(card, 'No se pudo publicar la imagen: se cortó la conexión.', 'error');
    };
    xhr.send(fd);
  }

  function publicarImagen(card, archivo) {
    if (!card || !archivo) return;
    if (!/^image\//.test(archivo.type || '')) {
      estado(card, 'Ese archivo no es una imagen (usa JPG, PNG o WebP).', 'error');
      return;
    }
    var marco = qs('[data-marco]', card);
    var img   = qs('img[data-ruta]', card);
    var sello = qs('[data-sello]', card);
    var prog  = qs('[data-progreso]', card);
    var urlPrevia = null;

    // Vista previa inmediata (así el jefe VE lo que va a publicar)
    var objUrl = URL.createObjectURL(archivo);
    if (!img) {
      var vacio = qs('[data-vacio]', card);
      if (vacio) vacio.parentNode.removeChild(vacio);
      img = document.createElement('img');
      img.id = 'epImg' + card.getAttribute('data-producto');
      img.setAttribute('data-ruta', '');
      img.className = 'cz-zoom';
      marco.insertBefore(img, marco.firstChild);
    } else {
      urlPrevia = img.getAttribute('src');
    }
    img.removeAttribute('srcset');
    img.removeAttribute('sizes');
    img.src = objUrl;
    if (sello) { sello.hidden = false; sello.textContent = '🆕 publicando…'; }
    if (prog) prog.classList.add('ep-progreso--on');
    estado(card, '⏳ Optimizando la imagen en tu PC…', 'curso');

    var listo = function (f) { subirImagen(card, f, urlPrevia); };
    if (window.CZImg && window.CZImg.optimizar) {
      window.CZImg.optimizar(archivo).then(listo, function () { listo(archivo); });
    } else {
      listo(archivo);
    }
  }

  function engancharDrop(card) {
    var drop = qs('.ep-drop', card);
    if (!drop || drop.__epDrop) return;
    drop.__epDrop = true;
    var prof = 0;

    drop.addEventListener('dragenter', function (e) {
      e.preventDefault(); e.stopPropagation();
      prof++;
      drop.classList.add('ep-drop--encima');
    });
    drop.addEventListener('dragover', function (e) {
      e.preventDefault(); e.stopPropagation();
      if (e.dataTransfer) { try { e.dataTransfer.dropEffect = 'copy'; } catch (err) {} }
      drop.classList.add('ep-drop--encima');
    });
    drop.addEventListener('dragleave', function (e) {
      e.preventDefault(); e.stopPropagation();
      prof--;
      if (prof <= 0) { prof = 0; drop.classList.remove('ep-drop--encima'); }
    });
    drop.addEventListener('drop', function (e) {
      e.preventDefault(); e.stopPropagation();
      prof = 0;
      drop.classList.remove('ep-drop--encima');
      var dt = e.dataTransfer;
      var archivo = null;
      if (dt) {
        if (dt.files && dt.files.length) archivo = dt.files[0];
        else if (dt.items && dt.items.length) {
          for (var i = 0; i < dt.items.length; i++) {
            if (dt.items[i].kind === 'file') { archivo = dt.items[i].getAsFile(); break; }
          }
        }
      }
      if (!archivo) { estado(card, 'No se detectó ninguna imagen en lo que soltaste.', 'error'); return; }
      // ⚡ SIN diálogo de confirmación (pedido del jefe: "al soltar la imagen
      // automáticamente se cambia"): se publica al instante y, si fue un error,
      // el botón ↩️ Deshacer lo devuelve tal como estaba.
      publicarImagen(card, archivo);
    });

    // Respaldo clásico: elegir el archivo a mano
    var input = qs('.ep-form-foto input[type="file"]', card);
    if (input && !input.__epFile) {
      input.__epFile = true;
      input.addEventListener('change', function () {
        if (input.files && input.files.length) publicarImagen(card, input.files[0]);
        input.value = '';
      });
    }
  }

  function deshacerImagen(card) {
    var pid = card.getAttribute('data-producto');
    estado(card, '⏳ Restaurando la imagen anterior…', 'curso');
    peticion({ accion: 'deshacer_imagen', producto_id: pid, _csrf: csrf() }, function (j) {
      if (!j || !j.ok) { estado(card, (j && j.mensaje) || 'No se pudo deshacer.', 'error'); return; }
      var img = qs('img[data-ruta]', card);
      if (img) {
        img.src = j.url;
        if (j.srcset) { img.setAttribute('srcset', j.srcset); img.setAttribute('sizes', window.EP_SIZES_FOTO || '330px'); }
        else { img.removeAttribute('srcset'); img.removeAttribute('sizes'); }
        img.setAttribute('data-full', j.url_full || j.url);
        img.setAttribute('data-ruta', j.ruta);
      }
      var pie = qs('.ep-foto__pie', card);
      if (pie) pie.textContent = j.ruta;
      var chipFoto = qs('[data-vista-foto]', card);
      if (chipFoto) {
        // Si la tarjeta ya decía "Con portada (N fotos)" se respeta ese texto;
        // solo se cambia cuando venía de "⬜ Sin imagen / Sin portada".
        var yaTenia = chipFoto.classList.contains('ep-chipmini--ok');
        chipFoto.className = 'ep-chipmini ep-chipmini--ok';
        if (!yaTenia) chipFoto.textContent = '🖼️ Con imagen';
      }
      var b = qs('[data-deshacer]', card);
      if (b) b.parentNode.removeChild(b);
      estado(card, j.mensaje, 'ok');
      aviso(j.mensaje);
    });
  }

  /* ==================================================== 4) PROMPT (sin recargar) */
  function asegurarRestaurar(card, pid) {
    if (qs('[data-restaurar-prompt]', card)) return;
    var cont = qs('.ep-botones', card);
    if (!cont) return;
    var b = document.createElement('button');
    b.type = 'submit';
    b.className = 'ep-btn ep-btn--suave';
    b.name = 'accion';
    b.value = 'restaurar_prompt';
    b.setAttribute('data-restaurar-prompt', pid);
    b.textContent = '↩️ Volver al del asistente';
    cont.insertBefore(b, cont.children[2] || null);
  }

  /** Manda el prompt al servidor y refresca la tarjeta. */
  function mandarPrompt(form, fd, esRestaurar) {
    var card = form.closest('.ep-card');
    if (!card) return;
    var pid = card.getAttribute('data-producto');
    var ta  = qs('textarea', form);
    estado(card, '⏳ Guardando el prompt…', 'curso');

    peticion(fd, function (j) {
      if (!j || !j.ok) { estado(card, (j && j.mensaje) || 'No se pudo guardar el prompt.', 'error'); return; }
      if (ta && typeof j.prompt === 'string' && j.prompt !== '') ta.value = j.prompt;
      if (ta) ta.defaultValue = ta.value;      // ya está guardado
      var chip = qs('[data-vista-prompt]', card);
      if (chip) {
        chip.className = 'ep-chipmini';
        chip.textContent = esRestaurar ? '✨ Prompt del asistente' : '✍️ Tu prompt';
      }
      if (esRestaurar) {
        var rb = qs('[data-restaurar-prompt]', card);
        if (rb) rb.parentNode.removeChild(rb);
      } else if (qs('textarea', form)) {
        asegurarRestaurar(card, pid);
      }
      estado(card, j.mensaje, 'ok');
    });
  }

  function engancharPrompt(form) {
    var card = form.closest('.ep-card');
    if (!card || form.__epPrompt) return;
    form.__epPrompt = true;

    // Envío del formulario (p. ej. el botón ↩️ Volver al del asistente)
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var fd  = new FormData(form);
      var sub = e.submitter;
      if (sub && sub.name) fd.set(sub.name, sub.value);
      mandarPrompt(form, fd, String(fd.get('accion')) === 'restaurar_prompt');
    });

    // ⚡ SIN botón «Guardar prompt» (pedido del jefe): al salir del cuadro, si el
    // texto cambió, se guarda solo.
    var ta = qs('textarea', form);
    if (ta) {
      ta.addEventListener('blur', function () {
        if (String(ta.value) === String(ta.defaultValue)) return;   // nada cambió
        var fd = new FormData(form);
        fd.set('accion', 'guardar_prompt');
        mandarPrompt(form, fd, false);
      });
    }
  }

  /* ==================================================== 5) COPIAR EL PROMPT */
  document.addEventListener('click', function (ev) {
    var b = ev.target.closest ? ev.target.closest('[data-copiar]') : null;
    if (!b) return;
    var ta = document.getElementById(b.getAttribute('data-copiar'));
    if (!ta) return;
    var listo = function () {
      var antes = b.textContent;
      b.textContent = '✅ ¡Copiado!';
      setTimeout(function () { b.textContent = antes; }, 1600);
    };
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(ta.value).then(listo, function () { ta.select(); document.execCommand('copy'); listo(); });
    } else {
      ta.select(); document.execCommand('copy'); listo();
    }
  });

  /* ============================================ 6) ARRANQUE Y COMPORTAMIENTO GLOBAL */
  var tarjetaActiva = null;

  function marcarActiva(card) {
    qsa('.ep-card--destino').forEach(function (c) { c.classList.remove('ep-card--destino'); });
    tarjetaActiva = card;
    if (card) card.classList.add('ep-card--destino');
  }

  function arrancar() {
    prepararFuse('defecto', LISTA_DEFECTO);   // aquí Fuse.js (defer) ya está cargado

    qsa('.ep-card').forEach(function (card) {
      // Unidad: select predictivo con fuzzy
      var ui = inputUnidad(card);
      if (ui) {
        engancharCombo(ui);
        ui.addEventListener('input', function () { refrescarSucio(card); });
        ui.addEventListener('change', function () { refrescarSucio(card); });
        // ⚡ Guardado automático: si escribió la unidad a mano y sale del campo,
        // se guarda sola (con una pequeña espera, para que si estaba eligiendo
        // de la lista se guarde lo ELEGIDO y no lo que iba tecleando).
        ui.addEventListener('blur', function () {
          setTimeout(function () { autoguardar(card); }, 220);
        });
      }
      // Precio
      var pi = qs('input[type="number"]', card);
      if (pi) {
        pi.addEventListener('input', function () { refrescarSucio(card); });
        // ⚡ Guardado automático al salir del campo (y con Enter)
        pi.addEventListener('blur', function () { autoguardar(card); });
        pi.addEventListener('keydown', function (e) {
          if (e.key === 'Enter') { e.preventDefault(); guardarDatos(card, false); }
        });
      }
      // Guardar (precio + unidad)
      var bg = qs('[data-guardar]', card);
      if (bg) bg.addEventListener('click', function () { guardarDatos(card); });

      // 🏷️ RUBROS de la tienda (hasta 4 bloques): select predictivo + guardado en vivo
      qsa('.ep-rubro', card).forEach(function (rin) {
        engancharCombo(rin);
        // Al salir del campo también se guarda (por si escribió el rubro a mano)
        rin.addEventListener('blur', function () {
          setTimeout(function () { guardarRubros(card); }, 260);
        });
      });
      var brg = qs('[data-guardar-rubros]', card);
      if (brg) brg.addEventListener('click', function () { guardarRubros(card); });

      // Botón ✕ de la píldora: vuelve a la unidad guardada
      card.addEventListener('click', function (e) {
        var x = e.target.closest ? e.target.closest('[data-reset-unidad]') : null;
        if (!x) return;
        var inp = inputUnidad(card);
        if (inp) {
          inp.value = String(card.getAttribute('data-unidad') || '');
          refrescarSucio(card);
        }
      });

      // Imagen: arrastrar y soltar + elegir archivo
      engancharDrop(card);

      // Deshacer (si ya venía de un cambio anterior)
      var bd = qs('[data-deshacer]', card);
      if (bd && !bd.__ep) { bd.__ep = true; bd.addEventListener('click', function () { deshacerImagen(card); }); }

      // Prompt
      var fp = qs('.ep-form-prompt', card);
      if (fp) engancharPrompt(fp);

      // Tarjeta "activa" (destino del Ctrl+V)
      card.addEventListener('mouseover', function () { marcarActiva(card); });
      card.addEventListener('focusin', function () { marcarActiva(card); });

      refrescarSucio(card);
    });

    // Ctrl+V: publica la imagen copiada (p. ej. desde Gemini) en la tarjeta señalada
    document.addEventListener('paste', function (e) {
      var dt = e.clipboardData;
      if (!dt || !dt.files || !dt.files.length) return;
      var archivo = null;
      for (var i = 0; i < dt.files.length; i++) {
        if (/^image\//.test(dt.files[i].type)) { archivo = dt.files[i]; break; }
      }
      if (!archivo) return;
      if (!tarjetaActiva) { aviso('Primero pasa el mouse por la tarjeta del producto y vuelve a pegar.'); return; }
      e.preventDefault();
      publicarImagen(tarjetaActiva, archivo);
    });

    // Que soltar una imagen FUERA de la zona no abra el archivo en el navegador
    ['dragover', 'drop'].forEach(function (ev) {
      window.addEventListener(ev, function (e) {
        var enZona = e.target && e.target.closest && e.target.closest('.ep-drop');
        if (!enZona) e.preventDefault();
      });
    });

    // Filtro instantáneo de la lista (mientras se escribe)
    var caja = qs('#epQ');
    if (caja) {
      caja.addEventListener('input', function () {
        var t = normaliza(caja.value.trim());
        qsa('.ep-card').forEach(function (card) {
          var texto = card.getAttribute('data-busca') || '';
          card.style.display = (t === '' || texto.indexOf(t) !== -1) ? '' : 'none';
        });
      });
    }

    // Aviso si se cierra la pestaña con precio o unidad sin guardar
    window.addEventListener('beforeunload', function (e) {
      if (qs('.ep-card--sucio')) { e.preventDefault(); e.returnValue = ''; return ''; }
    });
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', arrancar);
  else arrancar();

  window.EPEditor = { publicarImagen: publicarImagen, guardarDatos: guardarDatos, filas: filas };
})();
