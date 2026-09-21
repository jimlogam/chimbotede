/* ============================================================
   carrito.js — CARRITO "ME INTERESA" POR TIENDA + MEMORIA LOCAL
   ============================================================
   Módulo: dechimbote.com · Guía: GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md §8
   CSS: assets/css/carrito.css · Se carga desde includes/footer.php (?v=1)

   QUÉ HACE
   1) CARRITO POR TIENDA ("Me interesa"): acumula foto + nombre + cantidad + precio de
      UNA tienda y al enviar arma UN SOLO mensaje de WhatsApp a esa tienda. Comprar en
      la tienda A y en la tienda B son procesos separados (cada tienda tiene sus
      horarios y políticas), por eso cada tienda guarda su propio carrito.
   2) MEMORIA DEL NAVEGADOR (localStorage): guarda los productos que el visitante miró
      ("Vistos recientemente") y sus pedidos a medio armar, SIN pedirle crear cuenta.
      Nada de esto viaja al servidor: vive en su navegador y él puede borrarlo.
   3) El envío NO lleva el teléfono en la página: se manda por api/lead.php, que ya
      conoce el WhatsApp de la tienda, así el pedido se registra como lead y avisa al
      jefe por Telegram (mismo motor de avisos que el botón 💬 WhatsApp).

   CONVENIO DE DATOS (los atributos los pinta negocio.php / index.php):
     <div data-cz-prod            ← tarjeta de producto (lleva los datos)
          data-id="13" data-titulo="Menú del día" data-precio="18" data-unidad="por plato"
          data-desc="..." data-img="url-300" data-img800="url-800"
          data-neg-id="634" data-neg-nombre="A'GUSTO" data-neg-slug="a-gusto">
       <button data-cz-add>❤️ Me interesa</button>   ← botón que inyecta el PHP/este script
     </div>
     <div data-cz-abrir>…</div>    ← además, al tocarla se abre la ficha rápida (solo ficha de tienda)

   REGLA DE ORO: si el navegador bloquea localStorage (modo privado), el carrito sigue
   funcionando durante la visita con memoria en RAM; nunca se rompe la página.
   ============================================================ */
(function () {
    'use strict';

    var SITE = window.SITE_URL || '';
    var K_CARRITO = 'cz_carrito_v1';   // pedidos por tienda
    var K_VISTOS = 'cz_vistos_v1';     // productos mirados
    var MAX_TIENDAS = 8;               // pedidos guardados a la vez
    var MAX_ITEMS = 20;                // productos distintos por pedido
    var MAX_QTY = 99;
    var MAX_VISTOS = 24;               // productos recordados
    var DIAS_VISTOS = 45;              // caducidad de la memoria
    var MAX_TEXTO = 1600;              // tope del mensaje de WhatsApp
    var VISIBLE_VISTOS = 10;           // tarjetas que se pintan en la fila

    // ============================================================
    // 1) MEMORIA (localStorage con respaldo en RAM)
    // ============================================================
    var mem = {};

    function lsGet(clave, porDefecto) {
        try {
            var s = localStorage.getItem(clave);
            if (s !== null) return JSON.parse(s);
        } catch (e) { /* bloqueado o JSON roto */ }
        return (clave in mem) ? mem[clave] : porDefecto;
    }

    function lsSet(clave, valor) {
        mem[clave] = valor;
        try { localStorage.setItem(clave, JSON.stringify(valor)); } catch (e) { /* modo privado */ }
    }

    // ============================================================
    // 2) UTILIDADES
    // ============================================================
    function esc(s) {
        return String(s === null || s === undefined ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    function fmt(n) {
        n = Math.round((parseFloat(n) || 0) * 100) / 100;
        return 'S/ ' + n.toFixed(2);
    }

    function entero(v, def) {
        var n = parseInt(v, 10);
        return isNaN(n) ? (def || 0) : n;
    }

    function recorta(s, n) {
        s = String(s || '');
        return s.length > n ? s.substring(0, n - 1) + '…' : s;
    }

    /* 📏 EL NOMBRE CORTO PARA EL SALUDO (2026-09-16, orden del jefe): las fichas llevan el
       nombre con su lema pegado («Sra. Cinthia — Zapatillas, Disfraces, Mochilas y Calendarios»)
       y el saludo se volvía larguísimo. Se usa solo lo que va antes de «—», «–», «-», «|» o «·»
       (espejo de wa_nombre_corto() en includes/helpers.php). */
    function nombreCorto(n) {
        n = String(n || '').trim();
        if (!n) return '';
        var partes = n.split(/\s+[—–|·]\s+|\s+-\s+/);
        var corto = String(partes[0] || '').trim();
        return (corto.length >= 3) ? corto : n;
    }

    function contexto() {
        var t = window.CZ_TIENDA;
        return (t && t.id) ? t : null;
    }

    // ============================================================
    // 3) ESTADO DEL CARRITO (por tienda)
    // ============================================================
    function carritos() {
        var o = lsGet(K_CARRITO, {});
        return (o && typeof o === 'object' && !o.length) ? o : {};
    }

    function guardarCarritos(o) { lsSet(K_CARRITO, o); }

    function carritoDe(negId) {
        if (!negId) return null;
        var c = carritos()[String(negId)];
        return (c && c.it) ? c : null;
    }

    function items(c) {
        if (!c || !c.it) return [];
        var out = [], k;
        for (k in c.it) { if (Object.prototype.hasOwnProperty.call(c.it, k)) out.push(c.it[k]); }
        out.sort(function (a, b) { return (a.o || 0) - (b.o || 0); });
        return out;
    }

    function nProductos(c) { return items(c).length; }

    function nUnidades(c) {
        var t = 0;
        items(c).forEach(function (i) { t += (i.q || 1); });
        return t;
    }

    function totalDe(c) {
        var t = 0;
        items(c).forEach(function (i) { t += (i.p || 0) * (i.q || 1); });
        return t;
    }

    function listaCarritos() {
        var o = carritos(), out = [], k;
        for (k in o) {
            if (Object.prototype.hasOwnProperty.call(o, k) && o[k] && o[k].it && items(o[k]).length) out.push(o[k]);
        }
        out.sort(function (a, b) { return (b.ts || 0) - (a.ts || 0); });
        return out;
    }

    function podar(o, proteger) {
        var claves = [], k;
        for (k in o) { if (Object.prototype.hasOwnProperty.call(o, k)) claves.push(k); }
        if (claves.length <= MAX_TIENDAS) return o;
        claves.sort(function (a, b) { return (o[a].ts || 0) - (o[b].ts || 0); });
        var sobran = claves.length - MAX_TIENDAS;
        for (var i = 0; i < claves.length && sobran > 0; i++) {
            if (String(claves[i]) === String(proteger || '')) continue;
            delete o[claves[i]];
            sobran--;
        }
        return o;
    }

    function datosDe(el) {
        if (!el) return null;
        var ctx = contexto();
        var id = entero(el.getAttribute('data-id'), 0);
        if (!id) return null;
        return {
            id: id,
            titulo: el.getAttribute('data-titulo') || '',
            precio: parseFloat(el.getAttribute('data-precio')) || 0,
            unidad: el.getAttribute('data-unidad') || '',
            desc: el.getAttribute('data-desc') || '',
            img: el.getAttribute('data-img') || '',
            img800: el.getAttribute('data-img800') || el.getAttribute('data-img') || '',
            negId: entero(el.getAttribute('data-neg-id'), ctx ? ctx.id : 0),
            negNombre: el.getAttribute('data-neg-nombre') || (ctx ? ctx.nombre : ''),
            negSlug: el.getAttribute('data-neg-slug') || (ctx ? ctx.slug : '')
        };
    }

    function alternar(el) {
        var d = datosDe(el);
        if (!d || !d.negId) return;
        var c = carritoDe(d.negId);
        if (c && c.it[String(d.id)]) {
            quitarProducto(d.negId, d.id);
            toast('Quitado de tu pedido');
            return;
        }
        if (agregarProducto(d)) toast('✅ ' + recorta(d.titulo, 34) + ' en tu pedido');
    }

    function agregarProducto(d) {
        var o = carritos();
        var c = o[String(d.negId)];
        if (!c) {
            c = { id: d.negId, n: d.negNombre || '', s: d.negSlug || '', ts: 0, seq: 0, it: {}, nota: '', env: 0 };
        }
        var it = c.it[String(d.id)];
        if (it) {
            it.q = Math.min(MAX_QTY, (it.q || 1) + 1);
        } else {
            if (items(c).length >= MAX_ITEMS) {
                toast('Tu pedido ya tiene ' + MAX_ITEMS + ' productos distintos');
                return false;
            }
            c.seq = (c.seq || 0) + 1;
            c.it[String(d.id)] = {
                id: d.id, t: d.titulo, p: d.precio, u: d.unidad,
                i: d.img || '', q: 1, o: c.seq
            };
        }
        c.ts = Date.now();
        c.n = c.n || d.negNombre || '';
        c.s = c.s || d.negSlug || '';
        o[String(d.negId)] = c;
        guardarCarritos(podar(o, d.negId));
        refrescar();
        latidoFab();
        return true;
    }

    function quitarProducto(negId, prodId) {
        var o = carritos(), c = o[String(negId)];
        if (!c || !c.it) return;
        delete c.it[String(prodId)];
        if (!items(c).length) { delete o[String(negId)]; } else { c.ts = Date.now(); o[String(negId)] = c; }
        guardarCarritos(o);
        refrescar();
    }

    function cambiarCantidad(negId, prodId, delta) {
        var o = carritos(), c = o[String(negId)];
        if (!c || !c.it || !c.it[String(prodId)]) return;
        var it = c.it[String(prodId)];
        it.q = (it.q || 1) + delta;
        if (it.q < 1) { quitarProducto(negId, prodId); return; }
        if (it.q > MAX_QTY) it.q = MAX_QTY;
        c.ts = Date.now();
        o[String(negId)] = c;
        guardarCarritos(o);
        refrescar();
    }

    function vaciarCarrito(negId) {
        var o = carritos();
        delete o[String(negId)];
        guardarCarritos(o);
        refrescar();
    }

    function ponerNota(negId, txt) {
        var o = carritos(), c = o[String(negId)];
        if (!c) return;
        c.nota = String(txt || '').substring(0, 240);
        o[String(negId)] = c;
        guardarCarritos(o);
    }

    function marcarEnviado(negId) {
        var o = carritos(), c = o[String(negId)];
        if (!c) return;
        c.env = Date.now();
        o[String(negId)] = c;
        guardarCarritos(o);
    }

    // ============================================================
    // 4) MEMORIA DE "VISTOS RECIENTEMENTE"
    // ============================================================
    function vistos() {
        var arr = lsGet(K_VISTOS, []);
        if (!arr || !arr.length) return [];
        var lim = Date.now() - (DIAS_VISTOS * 86400000), out = [];
        for (var i = 0; i < arr.length; i++) {
            var v = arr[i];
            if (v && v.id && v.ts && v.ts > lim) out.push(v);
        }
        return out;
    }

    function registrarVisto(d) {
        if (!d || !d.id) return;
        var arr = vistos(), out = [{
            id: d.id, t: d.titulo, p: d.precio, u: d.unidad, i: d.img || '',
            n: d.negId, nn: d.negNombre, ns: d.negSlug, ts: Date.now()
        }];
        for (var i = 0; i < arr.length && out.length < MAX_VISTOS; i++) {
            if (String(arr[i].id) === String(d.id)) continue;
            out.push(arr[i]);
        }
        lsSet(K_VISTOS, out);
    }

    function borrarVistos() {
        lsSet(K_VISTOS, []);
        pintarVistos();
        toast('Historial borrado de tu navegador');
    }

    // ============================================================
    // 5) EL MENSAJE DE WHATSAPP (un solo mensaje por tienda)
    // ============================================================
    function lineaItem(it) {
        var u = String(it.u || '').trim();
        var generica = /^(c\/u|cada uno|unidad|por unidad|servicio|por servicio|por\s|x\s)/i.test(u);
        var medida = u !== '' && !generica;
        var cant = it.q || 1;
        var cuerpo = medida ? (cant + ' ' + u + ' de ' + it.t) : (cant + ' × ' + it.t);
        var sub = (it.p || 0) * cant;
        return '• ' + cuerpo + (sub > 0 ? ' (' + fmt(sub) + ')' : '');
    }

    function textoPedido(c, nota) {
        var its = items(c), L = [], largo = 0, usados = 0;
        L.push('Hola *' + (nombreCorto(c.n) || 'la tienda') + '* 👋, me interesa comprarte:');
        L.push('');
        for (var i = 0; i < its.length; i++) {
            var linea = lineaItem(its[i]);
            if (largo + linea.length > MAX_TEXTO - 260) break;
            L.push(linea);
            largo += linea.length;
            usados++;
        }
        if (usados < its.length) {
            L.push('• …y ' + (its.length - usados) + ' producto(s) más de tu catálogo.');
        }
        L.push('');
        L.push('Total referencial: ' + fmt(totalDe(c)));
        if (nota) { L.push(''); L.push('📝 ' + nota); }
        L.push('');
        // 🧭 Contexto obligatorio (2026-09-11) en su forma CORTA (2026-09-16, orden del jefe:
        // «es demasiado texto»): el enlace de la página donde se armó el pedido va solo, en su
        // línea, sin la etiqueta «🔗 Página donde lo vi:» ni la despedida. La tienda ya conoce
        // su propio enlace, así que no se repite.
        L.push(location.href);
        return L.join('\n');
    }

    function urlEnvio(c, nota, unProducto) {
        var its = items(c);
        // 🖱️ `&c=1` = «esto lo mandó un clic de verdad»: `api/lead.php` lo exige desde el
        // 2026-09-16 para no contar a los robots que siguen el enlace del botón (ver allí).
        var url = SITE + '/api/lead.php?n=' + encodeURIComponent(c.id)
            + '&origen=carrito'
            + '&c=1'
            + '&t=' + encodeURIComponent(textoPedido(c, nota));
        if (unProducto && its.length === 1) url += '&p=' + encodeURIComponent(its[0].id);
        return url;
    }

    // ============================================================
    // 6) PIEZAS DE INTERFAZ (se crean una sola vez)
    // ============================================================
    var fab = null, cajon = null, ficha = null, toastEl = null, toastTimer = null;
    var verId = null;          // tienda que se está mirando en el cajón
    var notaTienda = null;     // tienda cuya nota está escrita en el textarea
    var scrollPrevio = '';

    function crearUI() {
        if (fab) return;

        fab = document.createElement('button');
        fab.type = 'button';
        fab.className = 'cz-fab';
        fab.id = 'czFab';
        fab.hidden = true;
        fab.setAttribute('aria-label', 'Ver mi pedido');
        fab.addEventListener('click', function () { abrirCajon(); });
        document.body.appendChild(fab);

        toastEl = document.createElement('div');
        toastEl.className = 'cz-toast';
        toastEl.setAttribute('role', 'status');
        document.body.appendChild(toastEl);

        // -------- cajón del pedido --------
        cajon = document.createElement('div');
        cajon.className = 'cz-drawer';
        cajon.id = 'czDrawer';
        cajon.hidden = true;
        cajon.innerHTML =
            '<div class="cz-drawer__fondo" data-cz-cerrar></div>' +
            '<div class="cz-drawer__caja" role="dialog" aria-modal="true" aria-labelledby="czDrawerTit">' +
              '<div class="cz-drawer__cab">' +
                '<div>' +
                  '<div class="cz-drawer__tit" id="czDrawerTit">🛒 Mi pedido</div>' +
                  '<div class="cz-drawer__sub" id="czDrawerSub"></div>' +
                '</div>' +
                '<button type="button" class="cz-x" data-cz-cerrar aria-label="Cerrar">✕</button>' +
              '</div>' +
              '<div class="cz-drawer__chips" id="czChips"></div>' +
              '<div id="czEnv" class="cz-drawer__env" hidden></div>' +
              '<div class="cz-drawer__lista" id="czLista"></div>' +
              '<div class="cz-drawer__pie">' +
                '<div class="cz-drawer__total"><span>Total referencial</span><b id="czTotal">S/ 0.00</b></div>' +
                '<label class="cz-drawer__lbl" for="czNota">¿Algo más para la tienda? (opcional)</label>' +
                '<textarea id="czNota" class="cz-drawer__nota" rows="2" maxlength="240" ' +
                  'placeholder="Ej.: para las 7 pm, sin cebolla, ¿hacen delivery?"></textarea>' +
                '<details class="cz-drawer__prev"><summary>👀 Ver el mensaje que se enviará</summary>' +
                  '<pre id="czPrev"></pre></details>' +
                '<button type="button" class="cz-btn cz-btn--wsp" id="czEnviar">' + (window.WA_ICONO_SVG || '💬') + ' Enviar pedido por WhatsApp</button>' +
                '<div class="cz-drawer__acciones">' +
                  '<button type="button" class="cz-btn cz-btn--ghost" id="czVaciar">🧹 Vaciar</button>' +
                  '<button type="button" class="cz-btn cz-btn--ghost" data-cz-cerrar>Seguir mirando</button>' +
                '</div>' +
                '<p class="cz-drawer__aviso">Se envía UN solo mensaje de WhatsApp a la tienda. ' +
                  'DeChimbote.com no cobra ni interviene en el pago: coordinas directo con la tienda. ' +
                  'Tu pedido se guarda solo en este navegador.</p>' +
              '</div>' +
            '</div>';
        document.body.appendChild(cajon);

        var notaEl = cajon.querySelector('#czNota');
        notaEl.addEventListener('input', function () {
            var c = carritoDelCajon();
            if (c) { ponerNota(c.id, notaEl.value); }
            pintarPreview();
        });
        cajon.querySelector('#czVaciar').addEventListener('click', function () {
            var c = carritoDelCajon();
            if (!c) return;
            if (window.confirm('¿Vaciar tu pedido de ' + (c.n || 'esta tienda') + '?')) vaciarCarrito(c.id);
        });
        cajon.querySelector('#czEnviar').addEventListener('click', enviarPedido);

        // -------- ficha rápida del producto --------
        ficha = document.createElement('div');
        ficha.className = 'cz-sheet';
        ficha.id = 'czSheet';
        ficha.hidden = true;
        ficha.innerHTML =
            '<div class="cz-sheet__caja" role="dialog" aria-modal="true" aria-labelledby="czSheetT">' +
              '<img class="cz-sheet__img" id="czSheetImg" alt="">' +
              '<div class="cz-sheet__cuerpo">' +
                '<div class="cz-sheet__cab">' +
                  '<div>' +
                    '<div class="cz-sheet__t" id="czSheetT"></div>' +
                    '<div class="cz-sheet__u" id="czSheetU"></div>' +
                  '</div>' +
                  '<button type="button" class="cz-x" data-cz-cerrar aria-label="Cerrar">✕</button>' +
                '</div>' +
                '<div class="cz-sheet__p" id="czSheetP"></div>' +
                '<div class="cz-sheet__d" id="czSheetD" hidden></div>' +
                '<div class="cz-sheet__tienda" id="czSheetTienda"></div>' +
                '<div class="cz-sheet__fila">' +
                  '<button type="button" class="cz-add" id="czSheetAdd" data-cz-add>❤️ Me interesa</button>' +
                '</div>' +
                // 🔴 EL BOTÓN DE CONSULTAR ES UNA IMAGEN (2026-09-16, orden del jefe): la imagen
                // del botón rojo «CONSULTAR PRODUCTO» (recortada, optimizada y en WebP de 15 KB:
                // assets/img/boton-consultar-producto.webp) sustituye al botón blanco de texto
                // «Preguntar por este producto». El id NO cambia: el clic se sigue enganchando aquí.
                '<button type="button" class="cz-btnimg" id="czSheetWsp" aria-label="Consultar producto">' +
                  '<img src="' + SITE + '/assets/img/boton-consultar-producto.webp" alt="Consultar producto" width="900" height="254" decoding="async">' +
                '</button>' +
                // 🔗 COMPARTIR EL PRODUCTO (2026-09-15, pedido del jefe: «el producto se comparte
                // con su enlace y su foto»). Manda el enlace de la PÁGINA DEL PRODUCTO
                // (`/producto/<id>`): al pegarlo en WhatsApp se ve su foto, su nombre y su precio.
                // El destino se rellena en abrirFicha(). Es un <a> normal: el manejador de clics
                // no lo intercepta (solo frena los `data-cz-add`, `data-cz-abrir` y `a[data-cz-prod]`).
                '<a class="cz-btn cz-btn--suave" id="czSheetCompartir" href="#" target="_blank" rel="noopener" style="text-decoration:none;display:flex;align-items:center;justify-content:center;gap:7px">🔗 Compartir este producto</a>' +
                // 👀 Y el enlace para ABRIR la página del producto (existe desde el 2026-09-15).
                // Es un <a> común: el manejador de clics no lo frena.
                '<a id="czSheetVer" href="#" style="display:block;margin-top:10px;text-align:center;font-size:14px;font-weight:700;color:var(--color-primario,#6d071a);text-decoration:none">👀 Ver la página del producto</a>' +
              '</div>' +
            '</div>';
        document.body.appendChild(ficha);
        ficha.addEventListener('click', function (ev) {
            if (ev.target === ficha) cerrarTodo();
        });
        ficha.querySelector('#czSheetWsp').addEventListener('click', function () {
            var d = datosDe(ficha);
            if (!d || !d.negId) return;
            // 🧭 Mensaje CORTO (2026-09-16, orden del jefe: «es demasiado texto»): saludo con el
            // nombre corto + **EL ENLACE EXACTO DEL PRODUCTO**, no el de la tienda. La página del
            // producto ya muestra su foto, su nombre y su precio, así que el mensaje no los repite
            // (antes repetía nombre + precio + unidad + «🔗 Página donde lo vi:» con la URL de la tienda).
            var msg = 'Hola *' + (nombreCorto(d.negNombre) || 'la tienda') + '* 👋, quiero consultar por este producto: '
                + SITE + '/producto/' + encodeURIComponent(d.id);
            window.open(SITE + '/api/lead.php?n=' + encodeURIComponent(d.negId)
                + '&p=' + encodeURIComponent(d.id)
                + '&c=1'
                + '&t=' + encodeURIComponent(msg), '_blank', 'noopener');
        });
    }

    function bloquearScroll(si) {
        if (si) {
            scrollPrevio = document.body.style.overflow || '';
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = scrollPrevio;
        }
    }

    function toast(msg) {
        crearUI();
        toastEl.textContent = msg;
        toastEl.className = 'cz-toast cz-toast--visible';
        if (toastTimer) window.clearTimeout(toastTimer);
        toastTimer = window.setTimeout(function () {
            toastEl.className = 'cz-toast';
        }, 2400);
    }

    function latidoFab() {
        if (!fab || fab.hidden) return;
        fab.className = 'cz-fab cz-fab--latido';
        window.setTimeout(function () { fab.className = 'cz-fab'; }, 1100);
    }

    // ============================================================
    // 7) PINTAR
    // ============================================================
    function carritoDelFab() {
        var ctx = contexto();
        if (ctx) return carritoDe(ctx.id);         // en una ficha: solo el pedido de ESA tienda
        var ls = listaCarritos();
        return ls.length ? ls[0] : null;           // fuera de la ficha: el pedido más reciente
    }

    function carritoDelCajon() {
        if (verId && carritoDe(verId)) return carritoDe(verId);
        var ctx = contexto();
        if (ctx && carritoDe(ctx.id)) return carritoDe(ctx.id);
        var ls = listaCarritos();
        return ls.length ? ls[0] : null;
    }

    function pintarFab() {
        if (!fab) return;
        var c = carritoDelFab();
        if (!c || !nProductos(c)) { fab.hidden = true; return; }
        var ctx = contexto();
        var enEsta = ctx && String(ctx.id) === String(c.id);
        fab.hidden = false;
        fab.innerHTML = '<span class="cz-fab__icon">🛒</span><span class="cz-fab__txt">'
            + (enEsta ? 'Mi pedido' : esc(recorta(c.n || 'una tienda', 14)))
            + ' <span class="cz-fab__n">' + nProductos(c) + '</span> ' + fmt(totalDe(c))
            + '</span>';
    }

    function pintarBotones() {
        var tarjetas = document.querySelectorAll('[data-cz-prod]');
        for (var i = 0; i < tarjetas.length; i++) {
            var card = tarjetas[i];
            var d = datosDe(card);
            if (!d || !d.negId) continue;
            var c = carritoDe(d.negId);
            var en = !!(c && c.it[String(d.id)]);
            var btn = card.querySelector('[data-cz-add]');
            if (btn) pintarBoton(btn, en);
            var marca = card.querySelector('[data-cz-hecho]');
            if (marca) marca.hidden = !en;
            if (card.className.indexOf('cz-prod') >= 0) {
                card.className = card.className.replace(/\s*cz-prod--en/g, '') + (en ? ' cz-prod--en' : '');
            }
        }
        // El aviso "cómo funciona" desaparece cuando el visitante ya tiene un pedido armado
        // (en una ficha: el de esa tienda; en la portada o el buscador: cualquiera).
        var ctx = contexto();
        var hayPedido = ctx ? !!carritoDe(ctx.id) : (listaCarritos().length > 0);
        var avisos = document.querySelectorAll('.cz-aviso-meinteresa');
        for (var j = 0; j < avisos.length; j++) avisos[j].hidden = hayPedido;
    }

    // Corazón del botón "me interesa" (mismo dibujo que lleva index.php escrito a mano).
    // Dos caminos: uno VACÍO (gris, apagado, esperando el clic) y uno LLENO (blanco sobre el
    // círculo rojo cuando ya está en el pedido). Los muestra/oculta carrito.css.
    var SVG_CORAZON = '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
        + '<path class="cz-add__vacio" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linejoin="round" '
        + 'd="M16.5 3.2c-1.74 0-3.4.8-4.5 2.08C10.9 4 9.24 3.2 7.5 3.2 4.5 3.2 2 5.6 2 8.6c0 3.75 3.4 6.82 8.55 11.5L12 21.4l1.45-1.3C18.6 15.42 22 12.35 22 8.6c0-3-2.5-5.4-5.5-5.4z"/>'
        + '<path class="cz-add__lleno" fill="currentColor" '
        + 'd="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>'
        + '</svg>';

    function pintarBoton(btn, en) {
        var flotante = btn.className.indexOf('cz-add--flotante') >= 0;
        var esFicha = btn.id === 'czSheetAdd';
        if (flotante) {
            // El corazón flotante NUNCA lleva emoji: dibujo propio, apagado o encendido
            btn.innerHTML = SVG_CORAZON;
            btn.title = en ? 'Quitar de mi pedido' : 'Me interesa';
        } else if (esFicha) {
            btn.textContent = en ? '✅ En mi pedido (quitar)' : '❤️ Me interesa';
        } else {
            btn.textContent = en ? '✅ En mi pedido' : '❤️ Me interesa';
        }
        btn.className = 'cz-add' + (flotante ? ' cz-add--flotante' : '') + (en ? ' cz-add--en' : '');
        btn.setAttribute('aria-pressed', en ? 'true' : 'false');
    }

    function filaItemHTML(it, negId) {
        var sub = (it.p || 0) * (it.q || 1);
        return '<div class="cz-item" data-id="' + entero(it.id, 0) + '">'
            + '<img class="cz-item__img" src="' + esc(it.i || (SITE + '/assets/img/sin-foto.svg')) + '" alt="" loading="lazy">'
            + '<div class="cz-item__info">'
              + '<div class="cz-item__t">' + esc(it.t) + '</div>'
              + '<div class="cz-item__u">' + esc(it.u || 'cada uno')
                + (it.p > 0 ? ' · ' + fmt(it.p) : '') + '</div>'
              + '<div class="cz-item__q">'
                + '<button type="button" class="cz-qbtn" data-cz-menos aria-label="Quitar uno">−</button>'
                + '<span class="cz-qn">' + (it.q || 1) + '</span>'
                + '<button type="button" class="cz-qbtn" data-cz-mas aria-label="Agregar uno">+</button>'
                + '<button type="button" class="cz-item__del" data-cz-del aria-label="Quitar del pedido">🗑 Quitar</button>'
              + '</div>'
            + '</div>'
            + '<div class="cz-item__sub">' + (sub > 0 ? fmt(sub) : '') + '</div>'
          + '</div>';
    }

    function pintarCajon() {
        if (!cajon) return;
        var c = carritoDelCajon();
        var lista = cajon.querySelector('#czLista');
        var chips = cajon.querySelector('#czChips');
        var sub = cajon.querySelector('#czDrawerSub');
        var env = cajon.querySelector('#czEnv');
        var todos = listaCarritos();

        if (!c) { cerrarCajon(); return; }

        cajon.querySelector('#czDrawerTit').innerHTML = '🛒 Mi pedido en ' + esc(recorta(c.n || 'la tienda', 26));
        sub.innerHTML = '<a href="' + esc(SITE + '/neg/' + c.s) + '">Ver la tienda</a> · '
            + nProductos(c) + ' producto(s) · ' + nUnidades(c) + ' unidad(es)';

        // Chips para saltar entre pedidos guardados (el carrito es POR TIENDA)
        if (todos.length > 1) {
            var h = '';
            for (var i = 0; i < todos.length; i++) {
                var t = todos[i];
                h += '<button type="button" class="cz-chip' + (String(t.id) === String(c.id) ? ' cz-chip--activo' : '')
                    + '" data-cz-chip="' + entero(t.id, 0) + '">🏪 ' + esc(recorta(t.n || 'Tienda', 18))
                    + ' (' + nProductos(t) + ')</button>';
            }
            chips.innerHTML = h;
            chips.hidden = false;
        } else {
            chips.innerHTML = '';
            chips.hidden = true;
        }

        // Lista de productos
        var its = items(c), html = '';
        for (var j = 0; j < its.length; j++) html += filaItemHTML(its[j], c.id);
        lista.innerHTML = html;

        // Aviso de "ya lo enviaste"
        if (c.env) {
            var f = new Date(c.env);
            env.textContent = '✅ Ya enviaste este pedido hoy a las '
                + ('0' + f.getHours()).slice(-2) + ':' + ('0' + f.getMinutes()).slice(-2)
                + '. Puedes seguir agregando productos y reenviarlo.';
            env.hidden = false;
        } else {
            env.hidden = true;
        }

        // Nota escrita: solo se reescribe al cambiar de tienda
        var notaEl = cajon.querySelector('#czNota');
        if (String(notaTienda) !== String(c.id)) {
            notaEl.value = c.nota || '';
            notaTienda = c.id;
        }

        cajon.querySelector('#czTotal').textContent = fmt(totalDe(c));
        pintarPreview();
    }

    function pintarPreview() {
        if (!cajon || cajon.hidden) return;
        var c = carritoDelCajon();
        if (!c) return;
        var nota = (cajon.querySelector('#czNota').value || '').trim();
        cajon.querySelector('#czPrev').textContent = textoPedido(c, nota);
    }

    // ============================================================
    // 7.bis) CONTADOR DE "OFERTAS DE ÚLTIMA HORA"
    // ============================================================
    // Pedido del jefe (2026-09-10): fuera los textos viejos ("Vistos recientemente", "lo que
    // miraste guardado en tu navegador"), el botón Borrar historial y la nota de privacidad. En su
    // lugar, un BLOQUE con borde y de un solo color: el título arriba y el contador abajo con
    // DÍAS, HORAS, MINUTOS Y SEGUNDOS, con los números girando cada vez que cambian.
    // El tiempo es ALEATORIO entre 1 y 3 días (constantes de aquí abajo). La hora de fin se guarda
    // en el navegador para que la cuenta BAJE de verdad al recargar la página: si se sorteara en
    // cada carga, el visitante vería el reloj reiniciarse y no se creería la oferta. Al llegar a
    // cero se sortea otra vez.
    var OFERTA_MIN_DIAS = 1, OFERTA_MAX_DIAS = 3;
    var K_OFERTA = 'cz_oferta_fin_v2';   // v2: el rango pasó de horas (5-11) a días (1-3)
    var relojOferta = null;

    function dosDig(n) { return (n < 10 ? '0' : '') + n; }

    function finDeOferta() {
        var t = parseInt(lsGet(K_OFERTA, 0), 10) || 0;
        var ahora = Date.now();
        if (t <= ahora) {
            var dias = OFERTA_MIN_DIAS + Math.random() * (OFERTA_MAX_DIAS - OFERTA_MIN_DIAS);
            t = ahora + Math.round(dias * 86400000);
            lsSet(K_OFERTA, t);
        }
        return t;
    }

    function arrancarRelojOferta() {
        var reloj = document.querySelector('[data-cz-reloj]');
        if (!reloj) return;
        clearInterval(relojOferta);

        var eD = reloj.querySelector('[data-cz-d]'), eH = reloj.querySelector('[data-cz-h]'),
            eM = reloj.querySelector('[data-cz-m]'), eS = reloj.querySelector('[data-cz-s]');
        var fin = finDeOferta();

        // Al cambiar un número, su cajita "gira" (la animación está en carrito.css)
        function pone(el, valor) {
            if (!el || el.textContent === valor) return;
            el.textContent = valor;
            var caja = el.parentNode;
            caja.classList.remove('cz-oferta__caja--gira');
            void caja.offsetWidth;                 // reinicia la animación
            caja.classList.add('cz-oferta__caja--gira');
        }

        function pinta() {
            var falta = fin - Date.now();
            if (falta <= 0) { fin = finDeOferta(); falta = fin - Date.now(); }
            var s = Math.floor(falta / 1000);
            pone(eD, String(Math.floor(s / 86400)));
            pone(eH, dosDig(Math.floor((s % 86400) / 3600)));
            pone(eM, dosDig(Math.floor((s % 3600) / 60)));
            pone(eS, dosDig(s % 60));
        }
        pinta();
        relojOferta = setInterval(pinta, 1000);
    }

    function pintarVistos() {
        var cont = document.getElementById('czVistos');
        if (!cont) return;
        var ctx = contexto();
        var arr = vistos(), out = [];
        for (var i = 0; i < arr.length && out.length < VISIBLE_VISTOS; i++) {
            // En la ficha de una tienda no se repiten sus propios productos (ya están a la vista)
            if (ctx && String(arr[i].n) === String(ctx.id)) continue;
            out.push(arr[i]);
        }
        // 🪟 EL BLOQUE YA VIENE PINTADO DEL SERVIDOR (`ofertas_bloque_html()`, 12 productos en 2 filas).
        //    Antes este JS lo pintaba entero con lo que el visitante ya había mirado: si había mirado 3,
        //    se veían 3 y a un visitante nuevo no le salía nada. Ahora el bloque está siempre y lo único
        //    que hace el JS es **poner delante** las tarjetas de lo que ya miró (ordenadas, sin
        //    duplicarlas y sin que el bloque pase de 12).
        if (!out.length) { cont.hidden = false; arrancarRelojOferta(); return; }

        // Las tarjetas de lo que el visitante ya miró: van a ir DELANTE de las 12 del servidor.
        var tarjetas = [];

        for (var j = 0; j < out.length; j++) {
            var v = out[j];
            var c = carritoDe(v.n);
            var en = !!(c && c.it[String(v.id)]);
            tarjetas.push('<a class="card-producto cz-vcard" href="' + esc(SITE + '/neg/' + (v.ns || '')) + '"'
                + ' data-cz-prod data-id="' + entero(v.id, 0) + '"'
                + ' data-titulo="' + esc(v.t) + '" data-precio="' + (parseFloat(v.p) || 0) + '"'
                + ' data-unidad="' + esc(v.u || '') + '" data-img="' + esc(v.i || '') + '"'
                + ' data-neg-id="' + entero(v.n, 0) + '" data-neg-nombre="' + esc(v.nn || '') + '"'
                + ' data-neg-slug="' + esc(v.ns || '') + '">'
                + '<div class="card-producto__img">'
                  + '<img src="' + esc(v.i || (SITE + '/assets/img/sin-foto.svg')) + '" alt="' + esc(v.t) + '" loading="lazy">'
                  + '<span class="cz-add cz-add--flotante' + (en ? ' cz-add--en' : '') + '" role="button" tabindex="0" data-cz-add'
                    + (en ? ' aria-pressed="true"' : '') + '>' + SVG_CORAZON + '</span>'
                  + '<span class="cz-vcard__hecho" data-cz-hecho' + (en ? '' : ' hidden') + '>En tu pedido</span>'
                + '</div>'
                // Jerarquía pedida por el jefe (2026-09-10): la TIENDA manda (nombre grande) y sin
                // precio. Decisión de diseño del agente: debajo va el nombre del producto en letra
                // chica, porque una tarjeta con solo el nombre de la tienda no dice qué oferta es.
                // Si el jefe lo quiere fuera del todo, se borra esta línea.
                + '<div class="card-producto__body">'
                  + '<div class="cz-vcard__tienda">🏪 ' + esc(recorta(v.nn || 'Tienda', 30)) + '</div>'
                  + '<div class="cz-vcard__prod">' + esc(recorta(v.t, 42)) + '</div>'
                + '</div>'
              + '</a>');
        }

        // Se recorta la lista a lo que puede ponerse delante: 6 fichas (y nunca más de 12).
        if (tarjetas.length > 6) tarjetas = tarjetas.slice(0, 6);

        // 🎠 LAS FILAS DE «NUEVOS INGRESOS» SON MARQUESINAS (2026-09-19): cada fila lleva DOS juegos de
        //    12 fichas (`.cz-mq__set`): el de verdad y su COPIA, que es la que cierra el bucle sin
        //    costura. Reglas que hay que respetar o la marquesina se rompe:
        //      · Cada juego tiene que quedarse con **DOCE** fichas: el `-50%` de la animación cae justo
        //        donde empieza la copia porque los dos juegos miden lo mismo.
        //      · Y el juego tiene que medir **más que la ventana** (12 fichas = 1 896 px contra los
        //        ~1 168 px del ancho de la página): si midiera menos, al final de cada vuelta se vería
        //        un hueco vacío. Por eso lo que se quita de una fila se repone (abajo, con `sobra[]`).
        //    Y lo de siempre: las fichas de lo que el visitante **ya miró** van DELANTE de la 1.ª fila.
        var reales = cont.querySelectorAll('.cz-mq__set:not(.cz-mq__set--copia)');
        if (reales.length) {
            // 🔴 OJO CON EL NOMBRE: esta tabla NO se puede llamar `vistos` — arriba, en esta misma
            //    función, hay `var arr = vistos()`, y una `var` se lleva al principio de la función
            //    (hoisting): llamarla `vistos` TAPA la función y revienta con «vistos is not a function»
            //    (pasó el 2026-09-19 y dejó el carrito sin refrescarse hasta que se corrigió).
            var yaVistos = {}, i, j;
            for (i = 0; i < out.length; i++) yaVistos[String(entero(out[i].id, 0))] = 1;

            // 1) fuera del 1.er juego las que van a ir delante (el mismo producto no puede salir dos veces)
            var f1 = [].slice.call(reales[0].querySelectorAll('.cz-vcard'));
            for (i = 0; i < f1.length; i++) {
                if (yaVistos[f1[i].getAttribute('data-id')] && f1[i].parentNode) f1[i].parentNode.removeChild(f1[i]);
            }
            // 2) las de «ya mirado», delante y en orden
            if (tarjetas.length) reales[0].insertAdjacentHTML('afterbegin', tarjetas.join(''));

            // 3) el 1.er juego se queda con DOCE. Lo que sobra del final son REPUESTOS: no se tiran,
            //    sirven para rellenar la 2.ª fila si allá se quitó alguna (así las dos quedan en 12).
            var sobra = [];
            f1 = [].slice.call(reales[0].querySelectorAll('.cz-vcard'));
            for (i = 12; i < f1.length; i++) {
                sobra.push(f1[i]);
                if (f1[i].parentNode) f1[i].parentNode.removeChild(f1[i]);
            }

            // 4) la 2.ª fila: fuera las que ya se pusieron delante, y en su hueco entran los repuestos
            if (reales[1]) {
                var f2 = [].slice.call(reales[1].querySelectorAll('.cz-vcard'));
                for (j = 0; j < f2.length; j++) {
                    if (!yaVistos[f2[j].getAttribute('data-id')]) continue;
                    if (f2[j].parentNode) f2[j].parentNode.removeChild(f2[j]);
                    if (sobra.length) reales[1].appendChild(sobra.shift());
                }
            }

            // 5) las copias, idénticas a su juego (y con sus enlaces fuera del recorrido del teclado:
            //    son los mismos 12 enlaces otra vez)
            //    ⚠️ El recorrido es `i < reales.length` (una copia por fila). Ojo: `reales` solo tiene los
            //    juegos DE VERDAD, así que con `i + 1 < reales.length` se quedaba sin rehacer la copia de
            //    la ÚLTIMA fila (y esa copia seguía mostrando las fichas viejas: pasó el 2026-09-19).
            for (i = 0; i < reales.length; i++) {
                var copia = reales[i].parentNode.querySelector('.cz-mq__set--copia');
                if (!copia) continue;
                copia.innerHTML = reales[i].innerHTML;
                var enlaces = copia.querySelectorAll('a');
                for (j = 0; j < enlaces.length; j++) enlaces[j].setAttribute('tabindex', '-1');
            }

            cont.hidden = false;
            arrancarRelojOferta();
            return;
        }

        // ── De aquí para abajo: el bloque de la FICHA DE LA TIENDA (`#czVistos` vacío, sin marquesina).
        var filas = cont.querySelectorAll('.carrusel-tiendas');
        var primera = filas[0];
        if (primera && tarjetas.length) {
            // 1) fuera las tarjetas repetidas (el mismo producto ya está en el bloque del servidor)
            for (var k = 0; k < out.length; k++) {
                var repe = cont.querySelector('.cz-vcard[data-id="' + entero(out[k].id, 0) + '"]');
                if (repe && repe.parentNode) repe.parentNode.removeChild(repe);
            }
            // 2) las de «ya mirado», delante y en orden
            primera.insertAdjacentHTML('afterbegin', tarjetas.join(''));
        }
        // 3) 🧮 REPARTO EN DOS FILAS DE SEIS (orden del jefe, 2026-09-16: *«cada scroll siempre debe
        //    tener seis productos para lograr un total de 12»*). Antes, al meter delante las tarjetas
        //    de lo ya mirado, la primera fila se quedaba con 10 y la segunda con 2: aquí se recortan a
        //    12 y se reparten 6 y 6.
        if (filas.length >= 2) {
            var todas = [].slice.call(cont.querySelectorAll('.cz-vcard'));
            for (var m = 12; m < todas.length; m++) {
                if (todas[m].parentNode) todas[m].parentNode.removeChild(todas[m]);
            }
            todas = [].slice.call(cont.querySelectorAll('.cz-vcard')).slice(0, 12);
            for (var t = 0; t < todas.length; t++) {
                filas[t < 6 ? 0 : 1].appendChild(todas[t]);
            }
        } else if (!primera) {
            // Sin bloque del servidor (por si algún día se quita): se deja como estaba antes.
            cont.innerHTML = '<section class="seccion cz-vistos-sec"><div class="carrusel-tiendas">'
                + tarjetas.join('') + '</div></section>';
        }
        cont.hidden = false;
        arrancarRelojOferta();
    }

    function refrescar() {
        crearUI();
        pintarBotones();
        pintarFab();
        if (cajon && !cajon.hidden) pintarCajon();
        pintarVistos();
    }

    // ============================================================
    // 8) ABRIR / CERRAR
    // ============================================================
    function abrirCajon() {
        crearUI();
        var c = carritoDelCajon();
        verId = c ? c.id : verId;
        cajon.hidden = false;
        bloquearScroll(true);
        pintarCajon();
    }

    function cerrarCajon() {
        if (cajon) cajon.hidden = true;
        if (ficha) ficha.hidden = true;
        bloquearScroll(false);
    }

    function cerrarTodo() { cerrarCajon(); }

    function abrirFicha(card) {
        crearUI();
        var d = datosDe(card);
        if (!d) return;
        registrarVisto(d);
        ficha.setAttribute('data-cz-prod', '');
        ficha.setAttribute('data-id', String(d.id));
        ficha.setAttribute('data-titulo', d.titulo);
        ficha.setAttribute('data-precio', String(d.precio));
        ficha.setAttribute('data-unidad', d.unidad);
        ficha.setAttribute('data-img', d.img);
        ficha.setAttribute('data-img800', d.img800);
        ficha.setAttribute('data-neg-id', String(d.negId));
        ficha.setAttribute('data-neg-nombre', d.negNombre);
        ficha.setAttribute('data-neg-slug', d.negSlug);

        var img = ficha.querySelector('#czSheetImg');
        img.src = d.img800 || d.img || (SITE + '/assets/img/sin-foto.svg');
        img.alt = d.titulo;
        ficha.querySelector('#czSheetT').textContent = d.titulo;
        ficha.querySelector('#czSheetU').textContent = d.unidad || 'Cada uno';
        ficha.querySelector('#czSheetP').textContent = fmt(d.precio);
        var desc = ficha.querySelector('#czSheetD');
        if (d.desc) { desc.textContent = d.desc; desc.hidden = false; } else { desc.textContent = ''; desc.hidden = true; }
        ficha.querySelector('#czSheetTienda').innerHTML = '🏪 Este producto es de <a href="'
            + esc(SITE + '/neg/' + d.negSlug) + '">' + esc(d.negNombre || 'la tienda') + '</a>. '
            + 'Arma tu pedido con ❤️ y lo envías en un solo mensaje de WhatsApp.';

        // 🔗 El enlace del producto para compartir (2026-09-15): WhatsApp abre con el nombre, el
        // precio y la dirección del producto ya escritos, así el que lo recibe ve su foto.
        // ⚠️ El precio se dice como en el resto del sitio: sin precio es «A consultar», no «S/ 0.00»
        // (esa regla es del 2026-09-14; `fmt()` de aquí sigue pintando S/ 0.00 en otras partes).
        var compartir = ficha.querySelector('#czSheetCompartir');
        if (compartir) {
            var precio_txt = (parseFloat(d.precio) > 0) ? fmt(d.precio) : 'A consultar';
            compartir.href = 'https://wa.me/?text=' + encodeURIComponent(
                '*' + d.titulo + '* — ' + precio_txt + '\n'
                + 'Míralo aquí: ' + SITE + '/producto/' + d.id);
        }
        var ver = ficha.querySelector('#czSheetVer');
        if (ver) ver.href = SITE + '/producto/' + d.id;

        ficha.hidden = false;
        bloquearScroll(true);
        pintarBotones();
        pintarVistos();
    }

    function enviarPedido() {
        var c = carritoDelCajon();
        if (!c || !nProductos(c)) { toast('Tu pedido está vacío'); return; }
        var nota = (cajon.querySelector('#czNota').value || '').trim();
        ponerNota(c.id, nota);
        var url = urlEnvio(c, nota, true);
        marcarEnviado(c.id);
        toast('Abriendo WhatsApp con tu pedido…');
        window.open(url, '_blank', 'noopener');
        pintarCajon();
    }

    // ============================================================
    // 9) EVENTOS (delegación: sirve para las tarjetas que pinta el PHP
    //    y para las que pinta este script, ahora y en el futuro)
    // ============================================================
    function montarEventos() {
        document.addEventListener('click', function (ev) {
            var t = ev.target;
            if (!t || !t.closest) return;

            // ❤️ / ✅  (dentro de cualquier tarjeta, también en la ficha rápida y en "Vistos")
            var add = t.closest('[data-cz-add]');
            if (add) {
                ev.preventDefault();
                ev.stopPropagation();
                if (add.id === 'czSheetAdd') { alternar(ficha); }
                else { alternar(add.closest('[data-cz-prod]')); }
                return;
            }

            // Tarjeta de la ficha de tienda: abre la ficha rápida y recuerda el producto
            var abrir = t.closest('[data-cz-abrir]');
            if (abrir) {
                ev.preventDefault();
                abrirFicha(abrir);
                return;
            }

            // Tarjeta-enlace de la portada: solo se memoriza (el enlace sigue funcionando)
            var enlace = t.closest('a[data-cz-prod]');
            if (enlace) { registrarVisto(datosDe(enlace)); return; }

            // Cerrar (fondo, ✕ o botones)
            if (t.closest('[data-cz-cerrar]')) { ev.preventDefault(); cerrarTodo(); return; }

            // Cantidades dentro del cajón
            var mas = t.closest('[data-cz-mas]'), menos = t.closest('[data-cz-menos]'), del = t.closest('[data-cz-del]');
            if (mas || menos || del) {
                var fila = (mas || menos || del).closest('.cz-item');
                var c = carritoDelCajon();
                if (fila && c) {
                    var pid = entero(fila.getAttribute('data-id'), 0);
                    if (del) quitarProducto(c.id, pid);
                    else cambiarCantidad(c.id, pid, mas ? 1 : -1);
                }
                return;
            }

            // Cambiar de pedido (chips)
            var chip = t.closest('[data-cz-chip]');
            if (chip) {
                verId = entero(chip.getAttribute('data-cz-chip'), 0);
                notaTienda = null;
                pintarCajon();
                return;
            }

            // Borrar la memoria de "Vistos recientemente"
            if (t.closest('[data-cz-borrar-vistos]')) { ev.preventDefault(); borrarVistos(); return; }
        });

        document.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape') {
                if (ficha && !ficha.hidden) { ficha.hidden = true; if (!cajon || cajon.hidden) bloquearScroll(false); return; }
                if (cajon && !cajon.hidden) cerrarTodo();
            }
        });

        // Volver a pintar al volver a la pestaña (otra pestaña pudo cambiar el carrito)
        document.addEventListener('visibilitychange', function () {
            if (document.visibilityState === 'visible') refrescar();
        });
        window.addEventListener('storage', function (e) {
            if (e.key === K_CARRITO || e.key === K_VISTOS) refrescar();
        });
    }

    // ============================================================
    // 9bis) LOS CARRUSELES ARRANCAN SIEMPRE POR LA PRIMERA FICHA
    // ============================================================
    /* 🎠 PEDIDO DEL JEFE (2026-09-16, textual): *«el scroll de “nuevos ingresos” que aparece en la parte
       de arriba lo has puesto al revés: cuando carga la página carga por defecto la última ficha y debe
       cargar la primera ficha y luego el usuario puede escrolear hacia la derecha, en este caso lo estás
       obligando a explorar hacia la izquierda»*.

       ¿Qué pasaba? **No lo hacía el sitio: lo hacía el navegador.** Chrome y Firefox **se acuerdan de
       dónde quedaron las tiras deslizables** y las dejan ahí al recargar (y al volver de la ficha de una
       tienda), así que el bloque aparecía donde el visitante lo había dejado —y lo normal es haberlo
       empujado hasta el final para ver las 12 fichas—. Aquí se pone a **cero** a propósito: la fila
       arranca en la **primera ficha** y el visitante desliza hacia la derecha.
       ⚠️ **Se respeta al visitante:** en cuanto toca, arrastra o rueda sobre una de esas tiras, ya no se
       vuelve a tocar (`carruselTocado`).
       ⚠️ Se pone a cero **varias veces** (al arrancar, a los 150 ms, a los 600 ms, al terminar de cargar
       la página y cuando el navegador la devuelve de su memoria): el navegador restaura SU posición
       DESPUÉS del `load`, así que una sola pasada no bastaba. */
    var SELECTOR_CARRUSELES = '.carrusel-tiendas, .carrusel-productos, .gz-grilla, .hz-tira';
    var carruselTocado = false;

    function carruselesAlInicio() {
        if (carruselTocado) return;
        var els = document.querySelectorAll(SELECTOR_CARRUSELES);
        for (var i = 0; i < els.length; i++) if (els[i].scrollLeft) els[i].scrollLeft = 0;
    }

    function vigilarCarruseles() {
        function marca(ev) {
            var t = ev.target;
            if (t && t.closest && t.closest(SELECTOR_CARRUSELES)) carruselTocado = true;
        }
        ['pointerdown', 'touchstart', 'wheel', 'keydown'].forEach(function (ev) {
            window.addEventListener(ev, marca, { passive: true, capture: true });
        });
        carruselesAlInicio();
        window.addEventListener('load', carruselesAlInicio);
        window.addEventListener('pageshow', carruselesAlInicio);
        setTimeout(carruselesAlInicio, 150);
        setTimeout(carruselesAlInicio, 600);
    }

    // ============================================================
    // 10) ARRANQUE
    // ============================================================
    function iniciar() {
        crearUI();
        montarEventos();
        refrescar();
        vigilarCarruseles();     // 🎠 y al final: las tiras, al principio (ver 9bis)
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }

    // API mínima para pruebas y para otras piezas del sitio
    window.CZ_CARRITO = {
        abrir: abrirCajon,
        cerrar: cerrarTodo,
        agregar: function (datos) { return agregarProducto(datos); },
        quitar: function (negId, prodId) { quitarProducto(negId, prodId); },
        recordar: function (datos) { registrarVisto(datos); },
        texto: function (negId, nota) { var c = carritoDe(negId); return c ? textoPedido(c, nota) : ''; },
        urlEnvio: function (negId, nota) { var c = carritoDe(negId); return c ? urlEnvio(c, nota, true) : ''; },
        carritos: listaCarritos,
        vistos: vistos,
        borrarVistos: borrarVistos
    };
})();
