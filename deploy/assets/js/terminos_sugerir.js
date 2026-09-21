/* ============================================================================
 * assets/js/terminos_sugerir.js — TÍTULOS DE PRODUCTO PREDICTIVOS (2026-09-10)
 * ----------------------------------------------------------------------------
 * Qué hace: mientras el dueño escribe el NOMBRE de un producto, le sugiere los
 * títulos que YA existen en el sitio (y las unidades reales, si el campo es de
 * unidad). Las sugerencias son una AYUDA: si el dueño las ignora y sigue
 * escribiendo, su texto se respeta tal cual (Regla de Oro n.º 2).
 *
 * Cómo se activa (atributo, nada más):
 *     <input data-terminos="1">          → sugiere TÍTULOS
 *     <input data-terminos="unidad">     → sugiere UNIDADES ("por torta", "por kg")
 *
 * Notas de implementación:
 *  · El diccionario NO se descarga: se consulta `api/terminos.php` (debounce 140 ms).
 *  · Funciona con entradas que aparecen DESPUÉS (el asistente pinta sus pasos por JS):
 *    hay un MutationObserver que las engancha solas.
 *  · Teclado: ↓ ↑ para elegir, Enter acepta, Esc cierra. Si NO hay nada elegido,
 *    Enter sigue enviando el formulario como siempre.
 *  · Móvil primero: 16 px (evita el zoom de iOS) y filas de 44 px para el dedo.
 * ========================================================================== */
(function () {
  'use strict';
  if (window.CZTerminos) return;

  var MIN = 2;          // desde 2 letras ya sugiere
  var DEBOUNCE = 140;   // ms
  var cache = {};       // q|campo -> sugerencias (por sesión)

  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  // Un solo <style> por página (el jefe odia los duplicados)
  function estilos() {
    if (document.getElementById('czTerminosCss')) return;
    var st = document.createElement('style');
    st.id = 'czTerminosCss';
    st.textContent =
      '.cz-term{position:relative}' +
      '.cz-term__lista{position:absolute;z-index:60;left:0;right:0;top:calc(100% + 4px);' +
        'background:#fff;border:1px solid #d6d3d1;border-radius:12px;box-shadow:0 10px 24px rgba(0,0,0,.14);' +
        'max-height:264px;overflow:auto;padding:4px;margin:0;list-style:none}' +
      '.cz-term__lista[hidden]{display:none}' +
      '.cz-term__item{display:flex;align-items:center;gap:8px;min-height:44px;padding:8px 10px;' +
        'border-radius:9px;font-size:16px;color:#1c1917;cursor:pointer;line-height:1.25}' +
      '.cz-term__item b{font-weight:600}' +
      '.cz-term__item--sel,.cz-term__item:hover{background:#f0f9ff}' +
      '.cz-term__n{margin-left:auto;font-size:12px;color:#78716c;white-space:nowrap}' +
      '.cz-term__pie{padding:6px 10px 4px;font-size:12px;color:#78716c}';
    document.head.appendChild(st);
  }

  function caja(input) {
    if (!input.__czTerm) {
      var cont = document.createElement('div');
      cont.className = 'cz-term';
      input.parentNode.insertBefore(cont, input);
      cont.appendChild(input);
      var ul = document.createElement('ul');
      ul.className = 'cz-term__lista';
      ul.hidden = true;
      ul.setAttribute('role', 'listbox');
      cont.appendChild(ul);
      input.__czTerm = { ul: ul, cont: cont, sel: -1, items: [], timer: null, ultimo: '' };
    }
    return input.__czTerm;
  }

  function cerrar(input) {
    var c = caja(input);
    c.ul.hidden = true;
    c.sel = -1;
  }

  function pintar(input, lista) {
    var c = caja(input);
    c.items = lista || [];
    c.sel = -1;
    if (!c.items.length) { cerrar(input); return; }
    var html = '';
    for (var i = 0; i < c.items.length; i++) {
      html += '<li class="cz-term__item" role="option" data-i="' + i + '">' +
                '<span>' + esc(c.items[i].t) + '</span>' +
                (c.items[i].n > 1 ? '<span class="cz-term__n">' + c.items[i].n + ' tiendas</span>' : '') +
              '</li>';
    }
    html += '<li class="cz-term__pie">Toca una sugerencia o sigue escribiendo lo tuyo.</li>';
    c.ul.innerHTML = html;
    c.ul.hidden = false;
  }

  function elegir(input, i) {
    var c = caja(input);
    if (!c.items[i]) return;
    input.value = c.items[i].t;
    input.dispatchEvent(new Event('input', { bubbles: true }));
    input.dispatchEvent(new Event('change', { bubbles: true }));
    cerrar(input);
    input.focus();
  }

  function mover(input, paso) {
    var c = caja(input);
    if (c.ul.hidden || !c.items.length) return false;
    var n = c.items.length;
    c.sel = (c.sel + paso + n + 1) % (n + 1);        // n = "ninguna" (vuelve al texto libre)
    var nodos = c.ul.querySelectorAll('.cz-term__item');
    for (var i = 0; i < nodos.length; i++) nodos[i].classList.toggle('cz-term__item--sel', i === c.sel);
    return true;
  }

  function pedir(input, q) {
    var campo = (input.getAttribute('data-terminos') === 'unidad') ? 'unidad' : 'titulo';
    var clave = campo + '|' + q.toLowerCase();
    if (cache[clave]) { pintar(input, cache[clave]); return; }
    var url = (window.CZ_SITE || '') + '/api/terminos.php?campo=' + campo + '&q=' + encodeURIComponent(q);
    fetch(url, { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        var lista = (j && j.terminos) ? j.terminos : [];
        cache[clave] = lista;
        if (input.value.trim().toLowerCase().indexOf(q.toLowerCase().slice(0, MIN)) === 0 ||
            input.value.trim().length >= MIN) {
          pintar(input, lista);
        }
      })
      .catch(function () { cerrar(input); });      // sin conexión: se escribe igual
  }

  function enganchar(input) {
    if (!input || input.__czTermEng) return;
    input.__czTermEng = true;
    input.setAttribute('autocomplete', 'off');
    estilos();
    caja(input);

    input.addEventListener('input', function () {
      var c = caja(input);
      var q = input.value.trim();
      c.ultimo = q;
      if (c.timer) clearTimeout(c.timer);
      if (q.length < MIN) { cerrar(input); return; }
      c.timer = setTimeout(function () {
        if (c.ultimo === q) pedir(input, q);
      }, DEBOUNCE);
    });

    input.addEventListener('keydown', function (e) {
      var c = caja(input);
      if (e.key === 'ArrowDown') { if (mover(input, 1)) e.preventDefault(); }
      else if (e.key === 'ArrowUp') { if (mover(input, -1)) e.preventDefault(); }
      else if (e.key === 'Escape') { cerrar(input); }
      else if (e.key === 'Enter') {
        if (!c.ul.hidden && c.sel >= 0) { e.preventDefault(); elegir(input, c.sel); }
        else { cerrar(input); }     // sin elegir nada: Enter envía el formulario
      } else if (e.key === 'Tab') { cerrar(input); }
    });

    input.addEventListener('blur', function () { setTimeout(function () { cerrar(input); }, 180); });

    input.__czTerm.ul.addEventListener('mousedown', function (e) {
      var li = e.target.closest ? e.target.closest('.cz-term__item') : null;
      if (!li) return;
      e.preventDefault();
      elegir(input, parseInt(li.getAttribute('data-i'), 10));
    });
    input.__czTerm.ul.addEventListener('touchstart', function (e) {
      var li = e.target.closest ? e.target.closest('.cz-term__item') : null;
      if (li) { e.preventDefault(); elegir(input, parseInt(li.getAttribute('data-i'), 10)); }
    }, { passive: false });
  }

  function barrer(raiz) {
    var nodos = (raiz || document).querySelectorAll('input[data-terminos]');
    for (var i = 0; i < nodos.length; i++) enganchar(nodos[i]);
  }

  function arrancar() {
    barrer(document);
    // El asistente pinta sus pasos por JS: engancha las entradas que vayan apareciendo.
    try {
      new MutationObserver(function () { barrer(document); })
        .observe(document.body, { childList: true, subtree: true });
    } catch (e) { /* navegador viejo: solo funciona el barrido inicial */ }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', arrancar);
  } else {
    arrancar();
  }

  window.CZTerminos = { enganchar: enganchar, barrer: barrer, sugerir: pedir };
})();
