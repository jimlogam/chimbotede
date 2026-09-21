/* ============================================================
 * buscador_fuzzy.js — Búsqueda predictiva tolerante a errores (Fuse.js)
 * Proyecto: DeChimbote.com
 * ------------------------------------------------------------
 * Qué hace:
 *   1. Descarga UNA vez /api/negocios_json.php (JSON cacheado 1 hora).
 *   2. Arma el índice Fuse.js en el propio celular: la búsqueda es instantánea
 *      y perdona errores de tipeo ("zapatiyas" → "Zapaterías").
 *   3. Busca por PALABRAS (no la frase entera): "polleria chimbot" encuentra
 *      las pollerías de Chimbote y no una ferretería que se llame "Chimbote".
 *   4. Muestra hasta 8 coincidencias con debounce de 200 ms.
 *   5. Si Fuse.js o el JSON fallan, cae al buscador del servidor
 *      (/api/sugerir.php): nunca se queda sin sugerencias.
 *
 * 🆕 2026-09-13 — LA LEY DEL ENTER (orden del jefe): al pulsar ENTER **no** se abre el primer
 * resultado: se envía el formulario y el visitante aterriza en `buscar.php?q=…`, la **📊 BÚSQUEDA
 * DETALLADA** del término (tiendas, productos, visitas, pedidos, soles, precios y demanda). Las
 * fichas concretas se eligen con las flechas ⬆️⬇️ (o con el dedo) y después ENTER.
 *
 * Campos atendidos: cualquier input con  id="buscador-fuzzy"  o  data-fuzzy.
 * El formulario envía a buscar.php?q=... (esto es también el respaldo si no hay JS).
 * ============================================================ */
(function () {
    'use strict';

    var MAX_RESULTADOS = 8;
    var ESPERA_MS = 200;            // debounce
    var MIN_LETRAS = 2;
    var TOPE_POR_PALABRA = 2000;    // candidatos por palabra: prácticamente todos
                                    // (si se recorta, se pierden coincidencias por distrito)
    var MAX_PALABRAS = 4;           // tope de palabras analizadas (velocidad en móvil):
                                    // con las 4 más largas ya sobra para acertar

    // Configuración OBLIGATORIA del índice de NEGOCIOS (Regla de Oro de UX predictiva)
    var OPCIONES_FUSE = {
        keys: [
            { name: 'n', weight: 0.60 },   // nombre del negocio
            { name: 'r', weight: 0.25 },   // rubro / categoría
            { name: 'd', weight: 0.15 }    // distrito
        ],
        threshold: 0.3,                // punto dulce para español
        minMatchCharLength: MIN_LETRAS,
        includeScore: true,
        includeMatches: true,
        ignoreLocation: true,          // que encuentre la palabra en cualquier parte
        ignoreAccents: true,
        ignoreFieldNorm: true          // que un nombre largo no salga peor por ser largo
    };

    // Índices: los negocios viven en el celular (JSON de ~167 KB). Los productos
    // NO se indexan aquí a propósito: son miles y serían 1,5 MB (ver abajo).
    var CAMPOS_NEGOCIOS = ['n', 'r', 'd'];

    // ------------------------------------------------------------ utilidades
    function esc(txt) {
        return String(txt == null ? '' : txt)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function normalizar(txt) {
        return String(txt || '').toLowerCase()
            .replace(/[áàäâã]/g, 'a').replace(/[éèëê]/g, 'e').replace(/[íìïî]/g, 'i')
            .replace(/[óòöô]/g, 'o').replace(/[úùüû]/g, 'u').replace(/ñ/g, 'n');
    }

    /** Trocea lo que escribió el usuario en palabras útiles. */
    function palabras(q) {
        var bruto = normalizar(q).split(/[^0-9a-z]+/).filter(function (t) {
            return t.length >= MIN_LETRAS;
        });
        // 🧹 2026-09-14: las palabras de MANDO se tiran («comprar clavos» busca «clavos»). El motor y el
        // diccionario los manda el servidor (buscador_limpieza.js + window.CHIMBOTE_LIMPIEZA); si no
        // estuviera cargado, se busca como siempre (nunca peor que antes).
        var limpio = palabrasLimpias(q);
        return limpio.length ? limpio : bruto;
    }

    /** Las palabras ya limpias (sin mandos ni conectores), normalizadas. [] si el motor no está. */
    function palabrasLimpias(q) {
        var L = (typeof window !== 'undefined') ? window.ChimboteLimpieza : null;
        if (!L || typeof L.tokens !== 'function') return [];
        try { return L.tokens(q) || []; } catch (e) { return []; }
    }

    /**
     * 🧹 El TÉRMINO de la búsqueda: «comprar clavos» → «clavos» (mando del jefe, 2026-09-14).
     * Es lo que se manda al servidor, lo que se escribe en la URL y lo que se le enseña al visitante
     * («Ver todos los resultados para «clavos»»). Si el motor no está, devuelve el texto tal cual.
     */
    function terminoDe(q) {
        var txt = String(q == null ? '' : q).trim();
        var L = (typeof window !== 'undefined') ? window.ChimboteLimpieza : null;
        if (!L || typeof L.limpiar !== 'function') return txt;
        try { return L.limpiar(txt) || txt; } catch (e) { return txt; }
    }

    /** Resalta en <em> los tramos que Fuse marcó como coincidencia. */
    function pintar(nombre, tramos) {
        if (!tramos || !tramos.length) return esc(nombre);
        var salida = '', pos = 0;
        tramos.slice().sort(function (a, b) { return a[0] - b[0]; }).forEach(function (t) {
            var ini = t[0], fin = t[1];
            if (ini < pos) return;
            salida += esc(nombre.slice(pos, ini)) + '<em>' + esc(nombre.slice(ini, fin + 1)) + '</em>';
            pos = fin + 1;
        });
        salida += esc(nombre.slice(pos));
        return salida;
    }

    function urlNegocio(slug) {
        var base = String((typeof window !== 'undefined' && window.SITE_URL) || '').replace(/\/+$/, '');
        return base + '/neg/' + encodeURIComponent(slug);
    }

    function urlBuscar(q) {
        var base = String((typeof window !== 'undefined' && window.SITE_URL) || '').replace(/\/+$/, '');
        return base + '/buscar.php?q=' + encodeURIComponent(q);
    }

    /**
     * 🧹 La URL de la búsqueda YA LIMPIA, con la memoria de lo que escribió el visitante:
     *   `buscar.php?q=clavos&qo=comprar%20clavos`
     * El `q` es lo que se busca (limpio, así la página, el panel y los récords hablan del término de
     * verdad) y el `qo` sirve para que `buscar.php` pueda explicarle en una línea lo que se entendió.
     */
    function urlBuscarLimpio(q) {
        var crudo = String(q == null ? '' : q).trim();
        var t = terminoDe(crudo);
        var url = urlBuscar(t);
        if (t !== crudo && crudo !== '') url += '&qo=' + encodeURIComponent(crudo);
        return url;
    }

    /**
     * 🆕 2026-09-13 — LA LEY DEL ENTER (orden del jefe):
     *   «cuando alguien busca "chancho" muestra los resultados y si alguien da enter NO debe mostrar el
     *    primer resultado… debe mostrar la búsqueda detallada y más fuerte de ese término».
     *
     * Por eso ENTER ya NO abre la primera coincidencia: envía el formulario, o sea que lleva a
     * **`buscar.php?q=…`**, donde vive la **📊 BÚSQUEDA DETALLADA** del término (cuántas tiendas,
     * cuántos productos, visitas, pedidos, soles, precios y cuánta gente busca lo mismo).
     * El que quiera una ficha concreta la elige con las flechas ⬆️⬇️ (o con el dedo).
     *
     * ⚠️ Antes había aquí un `formularioSimple()` que interceptaba el envío del formulario para abrir
     *    `items[0]`: se quitó A PROPÓSITO. No volver a ponerlo.
     */

    // ============================================================ EL ÍNDICE
    /**
     * Crea un índice fuzzy a partir del JSON de un endpoint.
     * @param items    array de negocios ("n","s","r","i","d","p") o de productos ("t","ns",…)
     * @param opciones qué campos pesan (por defecto, los de negocios)
     * @param campos   campos que mira el plan C de rescate
     */
    function crearIndice(items, opciones, campos) {
        return {
            items: items,
            campos: campos || CAMPOS_NEGOCIOS,
            principal: new Fuse(items, opciones || OPCIONES_FUSE)
        };
    }

    /** Clave única de un ítem (para cruzar resultados de las palabras). */
    function claveItem(it) {
        if (!it) return 'x:' + String(Math.random());
        if (it.s) return 'n:' + it.s;                       // negocio (slug)
        if (it.ns && it.t) return 'p:' + it.ns + '|' + it.t; // producto: tienda + título
        if (it.n) return 'n:' + it.n;
        return 'x:' + String(Math.random());
    }

    /** Distancia de edición (Levenshtein) entre dos palabras. */
    function distancia(a, b) {
        var la = a.length, lb = b.length;
        if (!la) return lb;
        if (!lb) return la;
        var prev = [], i, j;
        for (j = 0; j <= lb; j++) prev[j] = j;
        for (i = 1; i <= la; i++) {
            var cur = [i];
            for (j = 1; j <= lb; j++) {
                cur[j] = Math.min(
                    prev[j] + 1,
                    cur[j - 1] + 1,
                    prev[j - 1] + (a.charAt(i - 1) === b.charAt(j - 1) ? 0 : 1)
                );
            }
            prev = cur;
        }
        return prev[lb];
    }

    var RATIO_RESCATE = 0.40;   // "sapatiyas"→"zapatillas" (0.30) entra; "→opticas" (0.44) no

    /**
     * PLAN C — Rescate con distancia de edición real.
     * Solo se usa cuando el índice fuzzy no encontró NADA (errores de tipeo muy
     * grandes). Compara palabra por palabra contra el nombre, el rubro y el
     * distrito, así que no devuelve cosas raras (nada de ópticas por "sapatiyas").
     */
    function rescatar(indice, toks, limite) {
        var campos = indice.campos || CAMPOS_NEGOCIOS;
        var hallados = [];
        indice.items.forEach(function (it) {
            var palabrasCampo = [];
            campos.forEach(function (c) {
                var campo = it[c];
                if (!campo) return;
                normalizar(campo).split(/[^0-9a-z]+/).forEach(function (p) {
                    if (p.length >= 3) palabrasCampo.push(p);
                });
            });
            if (!palabrasCampo.length) return;

            var aciertos = 0, suma = 0;
            for (var t = 0; t < toks.length; t++) {
                var tok = toks[t], mejor = Infinity;
                for (var p = 0; p < palabrasCampo.length; p++) {
                    var pal = palabrasCampo[p];
                    if (Math.abs(pal.length - tok.length) > 3) continue;   // atajo
                    var d = distancia(tok, pal);
                    if (d < mejor) mejor = d;
                    if (mejor === 0) break;
                }
                if (mejor !== Infinity && (mejor / Math.max(tok.length, 3)) <= RATIO_RESCATE) {
                    aciertos++;
                    suma += mejor;
                }
            }
            if (aciertos) {
                hallados.push({ item: it, aciertos: aciertos, score: suma / (aciertos * Math.max(toks[0].length, 3)) });
            }
        });

        hallados.sort(function (a, b) {
            if (b.aciertos !== a.aciertos) return b.aciertos - a.aciertos;
            return a.score - b.score;
        });
        return hallados.slice(0, limite).map(function (h) {
            return { item: h.item, score: h.score, matches: [] };
        });
    }

    /**
     * Busca dentro de UN índice Fuse ya construido.
     * · Una palabra  → búsqueda directa (tolera errores de tipeo).
     * · Varias       → se exige que TODAS coincidan (AND), ordenando por la suma
     *                  de calidades; si eso no da nada (alguna palabra con mucho
     *                  error), se muestran las que coincidan con al menos una,
     *                  primero las que coincidan con más palabras.
     */
    function buscarEn(fuse, toks, limite) {
        if (toks.length === 1) {
            return fuse.search(toks[0], { limit: limite });
        }

        var acumulado = new Map();   // slug -> {item, score, matches}  (AND)
        var union = new Map();       // slug -> {item, mejor, aciertos, matches} (plan B)
        var primerToken = true;

        toks.forEach(function (tok) {
            var res = fuse.search(tok, { limit: TOPE_POR_PALABRA });
            var set = new Map();
            res.forEach(function (r) {
                var clave = claveItem(r.item);
                set.set(clave, r);

                var u = union.get(clave);
                if (!u) {
                    union.set(clave, { item: r.item, mejor: r.score, aciertos: 1, matches: (r.matches || []).slice() });
                } else {
                    u.aciertos += 1;
                    if (r.score < u.mejor) u.mejor = r.score;
                    u.matches = u.matches.concat(r.matches || []);
                }
            });

            if (primerToken) {
                primerToken = false;
                set.forEach(function (r, k) {
                    acumulado.set(k, { item: r.item, score: r.score, matches: (r.matches || []).slice() });
                });
            } else {
                var nuevo = new Map();
                acumulado.forEach(function (acc, k) {
                    if (!set.has(k)) return;               // AND: si falta una palabra, fuera
                    var r = set.get(k);
                    nuevo.set(k, {
                        item: acc.item,
                        score: acc.score + r.score,
                        matches: acc.matches.concat(r.matches || [])
                    });
                });
                acumulado = nuevo;
            }
        });

        if (acumulado.size) {
            var and = Array.from(acumulado.values());
            and.sort(function (a, b) { return a.score - b.score; });
            return and.slice(0, limite);
        }

        // Plan B: ninguna coincide con todas las palabras a la vez.
        var or = Array.from(union.values());
        or.sort(function (a, b) {
            if (b.aciertos !== a.aciertos) return b.aciertos - a.aciertos;
            return a.mejor - b.mejor;
        });
        return or.slice(0, limite).map(function (o) {
            return { item: o.item, score: o.mejor, matches: o.matches };
        });
    }

    /**
     * Busca en el índice: plan A (fuzzy normal) y, si no hay NADA,
     * plan C (rescate por distancia de edición, para typos muy grandes).
     */
    function buscar(indice, q, limite) {
        limite = limite || MAX_RESULTADOS;
        var toks = palabras(q);
        if (!toks.length) return [];
        if (toks.length > MAX_PALABRAS) {
            toks = toks.slice().sort(function (a, b) { return b.length - a.length; }).slice(0, MAX_PALABRAS);
        }
        var res = buscarEn(indice.principal, toks, limite);
        if (!res.length && indice.items) {
            res = rescatar(indice, toks, limite);
        }
        return res;
    }

    // Datos a la vista para pruebas y para otros módulos (buscadores futuros).
    if (typeof window !== 'undefined') {
        window.ChimboteFuzzy = {
            opciones: OPCIONES_FUSE,
            crearIndice: crearIndice,
            buscar: buscar,
            palabras: palabras,
            normalizar: normalizar,
            pintar: pintar,
            describeUnaTienda: describeUnaTienda,
            pistasDe: pistasDe,
            terminoDe: terminoDe,
            urlNegocio: urlNegocio,
            urlBuscar: urlBuscar,
            urlBuscarLimpio: urlBuscarLimpio
        };
    }

    // Sin navegador (pruebas en Node) no hay nada que pintar.
    if (typeof document === 'undefined' || typeof window === 'undefined') return;

    var negocios = null;    // datos del endpoint de negocios
    var fuse = null;        // índice fuzzy de negocios
    var cargando = false;
    var pendientes = [];    // campos que quieren datos cuando lleguen

    // ------------------------------------------------------- carga del índice
    function cargarDatos(cb) {
        if (fuse) { cb(true); return; }
        pendientes.push(cb);
        if (cargando) return;
        cargando = true;

        fetch((window.SITE_URL || '') + '/api/negocios_json.php', { credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (datos) {
                if (Array.isArray(datos) && datos.length && typeof Fuse === 'function') {
                    negocios = datos;
                    fuse = crearIndice(negocios);
                }
                terminar(!!fuse);
            })
            .catch(function () { terminar(false); });

        function terminar(ok) {
            cargando = false;
            var cola = pendientes; pendientes = [];
            cola.forEach(function (fn) { try { fn(ok); } catch (e) {} });
        }
    }

    // --------------------------------------- productos (los busca el servidor)
    /**
     * Los PRODUCTOS no se descargan al celular: son miles (hoy 9.400 = 1,5 MB,
     * demasiado para un móvil). Se buscan en el servidor, y el navegador le pasa
     * PISTAS que dedujo del texto —aunque el usuario escriba con errores—:
     * las tiendas que reconoció y su rubro. Así "zapatiyas" trae los productos
     * de las tiendas de Calzado.
     */
    function pistasDe(resN) {
        var tiendas = [], rubros = {}, rubro = '', mejor = 0, k;
        for (var i = 0; i < resN.length && i < 5; i++) {
            var it = resN[i].item || {};
            if (it.s && tiendas.indexOf(it.s) === -1) tiendas.push(it.s);
            if (it.r) rubros[it.r] = (rubros[it.r] || 0) + 1;
        }
        for (k in rubros) {
            if (rubros[k] > mejor) { mejor = rubros[k]; rubro = k; }
        }
        return { tiendas: tiendas.slice(0, 6).join(','), rubro: rubro };
    }

    function buscarProductosServidor(q, pistas, cb) {
        var url = (window.SITE_URL || '') + '/api/sugerir.php?q=' + encodeURIComponent(q);
        if (pistas && pistas.tiendas) url += '&tiendas=' + encodeURIComponent(pistas.tiendas);
        if (pistas && pistas.rubro)   url += '&rubro=' + encodeURIComponent(pistas.rubro);
        fetch(url, { credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            // 🆕 2026-09-18: además de los productos, se devuelve la respuesta COMPLETA (`datos`): de ahí
            // sale `rubro_clave` (a qué rubro apuntan las palabras) para el caso «el texto no es un nombre».
            .then(function (d) { cb((d && d.productos) || [], d || {}); })
            .catch(function () { cb([], {}); });
    }

    /** Arma el ítem visible de un negocio que devolvió el SERVIDOR (nombre, rubro, distrito). */
    function itemNegocioServidor(n) {
        return {
            url: urlNegocio(n.slug),
            nombre: esc(n.nombre),
            meta: [n.categoria_nombre, n.distrito_nombre ? '📍 ' + n.distrito_nombre : '']
        };
    }

    /**
     * ¿Lo que escribió el visitante DESCRIBE a una de las tiendas que salieron?
     *
     * 🏷️ Se pide que **TODAS** las palabras útiles (3 letras o más) estén dentro de la **MISMA** tienda
     *    (su nombre, su rubro o su distrito), que es la misma escalera que usa `buscar.php`:
     *    · «academia euclides» → «Academia Preuniversitaria Euclides» las tiene todas → **manda el nombre**.
     *    · «polleria chimbot» → una pollería de Chimbote («polleria» en el nombre, «chimbot» en el
     *      distrito) las tiene todas → **mandan las pollerías que salieron**, al instante.
     *    · «clases a domicilio» → ninguna tienda tiene «clases» Y «domicilio» → **manda el rubro**
     *      (Educación), que es lo que el visitante está pidiendo, y no 4 tiendas de otros rubros que
     *      dicen «a domicilio».
     *    · «clavos» (que no está en ningún nombre) → manda el rubro: las ferreterías.
     */
    function describeUnaTienda(res, toks) {
        var utiles = toks.filter(function (k) { return k.length >= 3; });
        if (!utiles.length) return false;
        for (var i = 0; i < res.length; i++) {
            var it = (res[i] || {}).item || {};
            var texto = normalizar([it.n, it.r, it.d].filter(Boolean).join(' '));
            if (!texto) continue;
            var todas = true;
            for (var j = 0; j < utiles.length; j++) {
                if (texto.indexOf(utiles[j]) === -1) { todas = false; break; }
            }
            if (todas) return true;
        }
        return false;
    }

    /** Arma el ítem visible de un producto que devolvió el servidor. */
    function itemProductoServidor(p) {
        var precio = Number(p.precio) > 0
            ? '📦 S/ ' + Number(p.precio).toFixed(2) + (p.unidad ? ' / ' + p.unidad : '')
            : '📦 Servicio';
        return {
            url: urlNegocio(p.negocio_slug),
            nombre: esc(p.titulo),
            meta: [precio,
                   (p.categoria_icono ? p.categoria_icono + ' ' : '') + (p.categoria_nombre || ''),
                   p.negocio_nombre || '',
                   p.distrito_nombre ? '📍 ' + p.distrito_nombre : '',
                   p.destacado ? '★' : '']
        };
    }

    // ------------------------------------------------- búsqueda en el servidor
    /** Respaldo: el endpoint de siempre (/api/sugerir.php) si Fuse no está listo. */
    function buscarServidor(q, cb) {
        fetch((window.SITE_URL || '') + '/api/sugerir.php?q=' + encodeURIComponent(q), { credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (d) {
                if (!d) { cb([], 0); return; }
                // 🆕 2026-09-18: si el texto no es el nombre de ninguna tienda pero SÍ una palabra del
                // rubro («clavos», «niños hiperactivos»), el servidor manda `rubro_clave` con sus tiendas:
                // el grupo se titula con el rubro y la palabra que lo trajo, como pidió el jefe.
                var rc = d.rubro_clave || null;
                var grupos = [
                    { titulo: '🏪 ' + (rc ? esc(rc.nombre) + ' por «' + esc(rc.clave) + '»' : 'Negocios'), items: [] },
                    { titulo: '📦 Productos', items: [] }
                ];
                (d.negocios || []).forEach(function (n) {
                    grupos[0].items.push(itemNegocioServidor(n));
                });
                (d.productos || []).forEach(function (p) {
                    grupos[1].items.push(itemProductoServidor(p));
                });
                // El total incluye las tiendas del rubro que NO se listan (así sale «Ver todos los resultados»).
                var total = grupos[0].items.length + grupos[1].items.length
                    + (rc ? Math.max(0, (rc.tiendas || 0) - grupos[0].items.length) : 0);
                cb(grupos, total);
            })
            .catch(function () { cb([], 0); });
    }

    // -------------------------------------------------------------- pintado
    /** Arma el ítem visible de un negocio (con la coincidencia resaltada). */
    function itemNegocio(r) {
        var it = r.item || {};
        var tramos = [];
        (r.matches || []).forEach(function (m) {
            if (m.key === 'n' && m.indices) tramos = tramos.concat(m.indices);
        });
        return {
            url: urlNegocio(it.s),
            nombre: pintar(it.n, tramos),
            meta: [(it.i ? it.i + ' ' : '') + (it.r || ''),
                   it.d ? '📍 ' + it.d : '',
                   it.p ? '★ TOP' : '']
        };
    }

    function pintarPanel(estado, q, grupos, total) {
        var panel = estado.panel;
        // 🧹 El término de verdad (sin «comprar», «dónde hay»…): es lo que se le enseña al visitante y
        // lo que llevan los enlaces a la búsqueda completa.
        var t = terminoDe(q);
        var urlVer = urlBuscarLimpio(q);
        var visibles = grupos.reduce(function (n, g) { return n + g.items.length; }, 0);
        var esperando = grupos.some(function (g) { return g.cargando; });
        // 🆕 2026-09-13: el panel se repinta DOS veces por búsqueda (1.º los negocios del celular, y
        // otra vez cuando llegan los productos del servidor). Antes ese segundo pintado borraba la
        // elección del que iba bajando con las flechas ⬆️⬇️, así que su ENTER abría otra cosa (o hacía
        // la búsqueda completa). Ahora, si es LA MISMA búsqueda, la fila elegida se conserva.
        var mismaBusqueda = (estado.qPintada === q && panel.style.display !== 'none');
        var selPrevio = mismaBusqueda ? estado.sel : -1;

        if (!visibles && !esperando) {
            panel.innerHTML = '<div class="pred-sug__grupo">🏪 Negocios</div>' +
                '<div class="pred-vacio">Nada para «' + esc(t) + '».' +
                ' <a href="' + urlVer + '" style="font-weight:700;color:var(--marca-granate)">Ver resultados →</a></div>';
            estado.items = [];
            estado.sel = -1;
            estado.qPintada = q;
            mostrar(estado, true);
            return;
        }

        var i = 0, html = '';
        grupos.forEach(function (g) {
            if (!g.items.length && !g.cargando) return;
            html += '<div class="pred-sug__grupo">' + g.titulo + '</div>';
            if (!g.items.length) {
                html += '<div class="pred-vacio">' + (g.texto || 'Buscando productos…') + '</div>';
                return;
            }
            g.items.forEach(function (it) {
                var meta = (it.meta || []).filter(Boolean).join(' · ');
                html += '<a class="pred-item dropdown-fuzzy__item" data-idx="' + (i++) + '" href="' + it.url + '">' +
                    '<span class="pre">' + it.nombre + '</span>' +
                    (meta ? '<span class="meta">' + meta + '</span>' : '') +
                    '</a>';
            });
        });
        if (total > visibles) {
            html += '<a class="dropdown-fuzzy__ver" href="' + urlVer + '">Ver todos los resultados para «' + esc(t) + '» →</a>';
        }
        panel.innerHTML = html;
        estado.items = panel.querySelectorAll('.dropdown-fuzzy__item');
        estado.sel = (selPrevio >= 0 && selPrevio < estado.items.length) ? selPrevio : -1;
        estado.qPintada = q;
        marcar(estado);
        mostrar(estado, true);
    }

    function mostrar(estado, ver) {
        estado.panel.style.display = ver ? 'block' : 'none';
        estado.input.setAttribute('aria-expanded', ver ? 'true' : 'false');
        if (!ver) { estado.sel = -1; marcar(estado); }
    }

    function marcar(estado) {
        for (var i = 0; i < estado.items.length; i++) {
            estado.items[i].classList.toggle('is-sel', i === estado.sel);
        }
    }

    // ------------------------------------------------------------- un campo
    function prepararInput(input) {
        // Envolver en .pred-wrap (posiciona el desplegable; ya existe en el CSS)
        var wrap = input.closest('.pred-wrap');
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.className = 'pred-wrap';
            input.parentNode.insertBefore(wrap, input);
            wrap.appendChild(input);
        }

        var estado = {
            input: input, panel: null, items: [], sel: -1,
            timer: null, ultimo: '', seq: 0, qPintada: '', enterElegido: null
        };

        input.setAttribute('autocomplete', 'off');
        input.setAttribute('aria-autocomplete', 'list');
        input.setAttribute('aria-expanded', 'false');

        var previo = wrap.querySelector('.dropdown-fuzzy');
        if (previo) previo.parentNode.removeChild(previo);

        var panel = document.createElement('div');
        panel.className = 'pred-sug dropdown-fuzzy';
        panel.setAttribute('role', 'listbox');
        panel.style.display = 'none';
        wrap.appendChild(panel);
        estado.panel = panel;

        function buscar_() {
            var q = input.value.trim();
            if (normalizar(q).length < MIN_LETRAS) { mostrar(estado, false); return; }
            if (q === estado.ultimo) return;
            estado.ultimo = q;
            var miSeq = ++estado.seq;
            // 🧹 El término de verdad: «comprar clavos» → «clavos» (mando del jefe, 2026-09-14). El
            // panel, los enlaces y el servidor trabajan con ESTE; el campo sigue mostrando lo escrito.
            var t = terminoDe(q);
            if (normalizar(t).length < MIN_LETRAS) t = q;   // red de seguridad (nunca vacío)

            if (fuse) {
                // 1) NEGOCIOS: al instante, con el índice que está en el celular.
                // 2) PRODUCTOS: los busca el servidor con las pistas que deducimos
                //    del texto (aunque esté mal escrito); aparecen en cuanto llegan.
                var resN = buscar(fuse, t, MAX_RESULTADOS * 3);
                var maxN = Math.min(resN.length, 5);
                var porNombre = describeUnaTienda(resN, palabras(t));

                /**
                 * 🏷️ EL RUBRO CUANDO LO ESCRITO NO DESCRIBE A NINGUNA TIENDA (2026-09-18 — guía
                 * `GUIA_BUSQUEDAS_POR_RUBRO_Y_SINONIMOS.md` §5).
                 *
                 * Antes: «niños hiperactivos», «clases a domicilio» o «fiesta de cachimbos» daban
                 * **«Nada para «…»»** —o basura del rescate por errores de tipeo— en el desplegable,
                 * aunque `buscar.php` SÍ resolvía esas palabras, porque las resuelve por las «frases de
                 * unión» del rubro (tabla `directorio_categoria_claves`) y el desplegable no las leía.
                 * Ahora, cuando lo escrito no describe a ninguna tienda, el servidor dice a qué rubro
                 * apuntan esas palabras y se enseñan SUS tiendas, diciendo de dónde salen:
                 * «🏪 Educación / Academias por «clases a domicilio»». Si las palabras no son de ningún
                 * rubro (un typo como «zapatiyas»), todo queda como estaba: el rescate por errores.
                 */
                if (porNombre) {
                    // El visitante nombró una tienda: eso manda (y los productos van al lado).
                    pintarPanel(estado, q, [
                        { titulo: '🏪 Negocios', items: resN.slice(0, maxN).map(itemNegocio) },
                        { titulo: '📦 Productos', items: [], cargando: true }
                    ], resN.length);
                } else {
                    // Mientras el servidor dice de qué rubro son esas palabras, no se enseña basura.
                    pintarPanel(estado, q, [
                        { titulo: '🏪 Negocios', items: [], cargando: true, texto: 'Buscando en los rubros…' }
                    ], 0);
                }

                buscarProductosServidor(t, pistasDe(resN), function (prods, datos) {
                    if (miSeq !== estado.seq) return;               // respuesta vieja
                    if (input.value.trim() !== q) return;            // el usuario siguió escribiendo

                    var rc = (datos && datos.rubro_clave) || null;
                    var suyos = (rc && rc.negocios) ? rc.negocios : [];

                    if (!porNombre && suyos.length) {
                        var items = suyos.slice(0, MAX_RESULTADOS - 2).map(itemNegocioServidor);
                        pintarPanel(estado, q, [
                            { titulo: '🏪 ' + esc(rc.nombre) + ' por «' + esc(rc.clave) + '»', items: items },
                            { titulo: '📦 Productos', items: prods.slice(0, Math.max(0, MAX_RESULTADOS - items.length)).map(itemProductoServidor) }
                        ], (rc.tiendas || items.length) + prods.length);
                        return;
                    }

                    // Como siempre: el nombre de la tienda, el rescate por typos o el texto sin rubro.
                    pintarPanel(estado, q, [
                        { titulo: '🏪 Negocios', items: resN.slice(0, maxN).map(itemNegocio) },
                        { titulo: '📦 Productos', items: prods.slice(0, MAX_RESULTADOS - maxN).map(itemProductoServidor) }
                    ], resN.length + prods.length);
                });
                return;
            }

            // Todavía no llega el JSON: responde el servidor; cuando llegue el
            // índice, la misma búsqueda se repite ya en modo fuzzy.
            buscarServidor(t, function (grupos, total) {
                if (miSeq !== estado.seq) return;               // respuesta vieja
                if (input.value.trim() !== q) return;            // el usuario siguió escribiendo
                pintarPanel(estado, q, grupos, total);
            });
            cargarDatos(function (ok) {
                if (ok && input === document.activeElement && input.value.trim() === q) {
                    estado.ultimo = '';
                    buscar_();
                }
            });
        }

        input.addEventListener('input', function () {
            estado.enterElegido = null;
            clearTimeout(estado.timer);
            estado.timer = setTimeout(buscar_, ESPERA_MS);
        });

        input.addEventListener('focus', function () {
            estado.enterElegido = null;
            if (input.value.trim().length >= MIN_LETRAS && estado.ultimo) mostrar(estado, true);
        });

        input.addEventListener('keydown', function (ev) {
            var abierto = panel.style.display !== 'none' && estado.items.length > 0;
            if (ev.key === 'Escape') { mostrar(estado, false); return; }
            if (ev.key !== 'Enter') {
                if (!abierto) return;
                if (ev.key === 'ArrowDown' || ev.key === 'ArrowUp') {
                    ev.preventDefault();
                    estado.sel += (ev.key === 'ArrowDown' ? 1 : -1);
                    if (estado.sel < 0) estado.sel = estado.items.length - 1;
                    if (estado.sel >= estado.items.length) estado.sel = 0;
                    marcar(estado);
                }
                return;
            }
            // ================= ENTER (orden del jefe, 2026-09-13) =================
            // · Si el visitante BAJÓ con las flechas y eligió una coincidencia (sel >= 0), se respeta
            //   su elección: se abre ESA ficha (fue un acto deliberado, no una sorpresa).
            // · Si no eligió nada, NO se abre el primer resultado: ENTER hace la BÚSQUEDA COMPLETA y
            //   aterriza en `buscar.php?q=…`, donde está la 📊 BÚSQUEDA DETALLADA del término
            //   (tiendas, productos, visitas, pedidos, soles, precios y demanda real de la palabra).
            if (abierto && estado.sel >= 0) {
                var elegido = estado.items[estado.sel];
                var destino = elegido && elegido.getAttribute('href');
                if (destino) {
                    ev.preventDefault();
                    // ⚠️ TRAMPA YA PISADA (guía §8.3): Chrome dispara el `keydown` **y además** el
                    // envío implícito del formulario, y ese envío PISA esta navegación. Por eso se
                    // guarda el destino: si el `submit` llega igual, el handler de abajo lo usa.
                    estado.enterElegido = destino;
                    window.location.href = destino;
                    return;
                }
            }
            // Sin elección: se cierra el desplegable y el formulario sigue su camino solo
            // (búsqueda completa). NO se llama a preventDefault.
            estado.enterElegido = null;
            mostrar(estado, false);
        });

        // Regla de oro UX: al tocar fuera del buscador, el desplegable se cierra.
        document.addEventListener('click', function (ev) {
            if (!wrap.contains(ev.target)) mostrar(estado, false);
        });
        document.addEventListener('keydown', function (ev) {
            if (ev.key === 'Escape') mostrar(estado, false);
        });

        var form = input.form;
        if (form) {
            // Chrome dispara keydown Y keypress: el envío implícito del formulario puede colarse por
            // caminos que `preventDefault` en `keydown` no tapa.
            //   · Si el visitante había BAJADO con las flechas y eligió una coincidencia, el envío se
            //     frena y se abre ESA ficha (su elección manda).
            //   · Si no eligió nada, NO se intercepta: la búsqueda sigue su camino →
            //     `buscar.php?q=…` = la 📊 BÚSQUEDA DETALLADA del término (la ley del ENTER del jefe).
            // 🧹 2026-09-14: y antes de dejarlo pasar, el campo se LIMPIA («comprar clavos» → «clavos»)
            //    y lo que escribió se guarda en `qo`, para que la página pueda explicarle lo que se
            //    entendió. Se hace AQUÍ (y no en el keydown) porque el navegador arma los datos del
            //    formulario DESPUÉS de este manejador: así lo que viaja ya es el término limpio.
            form.addEventListener('submit', function (ev) {
                if (estado.enterElegido) {
                    ev.preventDefault();
                    var destino = estado.enterElegido;
                    estado.enterElegido = null;
                    window.location.href = destino;
                    return;
                }
                limpiarCampo();
                mostrar(estado, false);
            });
        }

        /**
         * 🧹 Deja en el campo el término de verdad y guarda lo escrito en el campo oculto `qo`.
         * Si no cambia nada, no toca el formulario (comportamiento de siempre).
         */
        function limpiarCampo() {
            var crudo = input.value.trim();
            var t = terminoDe(crudo);
            if (!t || t === crudo) return;

            var oculto = form && form.querySelector('input[name="qo"]');
            if (!oculto && form) {
                oculto = document.createElement('input');
                oculto.type = 'hidden';
                oculto.name = 'qo';
                form.appendChild(oculto);
            }
            if (oculto) oculto.value = crudo;
            input.value = t;              // el visitante ve lo mismo que se va a buscar
        }
    }

    // ------------------------------- ¿Buscabas alguno de estos? (página vacía)
    /**
     * buscar.php devuelve "Sin resultados" cuando el LIKE de MySQL no encuentra
     * nada ("polleria chimbot" → 0). Aquí el índice ya está en el celular: si el
     * usuario cae en esa página vacía, le ofrecemos las coincidencias fuzzy.
     */
    function sugerirEnPaginaVacia(indice) {
        var vacio = document.querySelector('.empty-state');
        if (!vacio) return;
        var q = '';
        try { q = new URLSearchParams(window.location.search).get('q') || ''; } catch (e) { q = ''; }
        if (normalizar(q).length < MIN_LETRAS) return;
        if (document.querySelector('.dropdown-fuzzy__sugerencias')) return;   // no duplicar

        // 🧹 Igual que en el desplegable: se ofrecen parecidos para el término de verdad.
        var t = terminoDe(q);
        var resN = buscar(indice, t, 4);

        function pintar(prods) {
            var itemsN = resN.map(itemNegocio);
            var itemsP = prods.slice(0, 4).map(itemProductoServidor);
            var total = itemsN.length + itemsP.length;
            if (!total) return;

            var html = '';
            [['🏪 Negocios', itemsN], ['📦 Productos', itemsP]].forEach(function (par) {
                if (!par[1].length) return;
                html += '<div class="pred-sug__grupo">' + par[0] + '</div>';
                par[1].forEach(function (it) {
                    var meta = (it.meta || []).filter(Boolean).join(' · ');
                    html += '<a class="pred-item dropdown-fuzzy__item" href="' + it.url + '">' +
                        '<span class="pre">' + it.nombre + '</span>' +
                        (meta ? '<span class="meta">' + meta + '</span>' : '') + '</a>';
                });
            });

            var caja = document.createElement('div');
            caja.className = 'dropdown-fuzzy__sugerencias';
            caja.innerHTML =
                '<p class="dropdown-fuzzy__sug-titulo">🔎 No hubo coincidencias exactas para «' + esc(t) + '».' +
                ' ¿Buscabas alguno de estos?</p>' +
                '<div class="dropdown-fuzzy__lista">' + html + '</div>' +
                '<p class="dropdown-fuzzy__sug-pie"><a href="' + urlBuscarLimpio(q) + '">Ver la búsqueda completa →</a></p>';
            vacio.parentNode.insertBefore(caja, vacio);
        }

        // Los productos los trae el servidor con las pistas del texto mal escrito.
        buscarProductosServidor(t, pistasDe(resN), pintar);
    }

    // ------------------------------------------------------------- arranque
    function init() {
        var inputs = document.querySelectorAll('#buscador-fuzzy, [data-fuzzy]');
        if (!inputs.length) return;

        Array.prototype.forEach.call(inputs, function (input, i) {
            prepararInput(input);
            // El primer campo recibe el id del contenedor que pide la especificación.
            if (i === 0) {
                var p = input.closest('.pred-wrap').querySelector('.dropdown-fuzzy');
                if (p) p.id = 'resultados-fuzzy';
            }
        });

        cargarDatos(function (ok) {
            if (!ok) { console.warn('[fuzzy] Sin Fuse.js/JSON: se usa el buscador del servidor.'); return; }
            // Página sin resultados (buscar.php con 0): ofrecemos los parecidos.
            if (document.querySelector('.empty-state')) sugerirEnPaginaVacia(fuse);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
