/* ============================================================================
   explorer.js — 🧭 Las interacciones del área Explorer
   ============================================================================
   Todo sin librerías (igual que el resto del sitio) y con **delegación de clics**: las
   publicaciones que llegan después (el scroll infinito las pide a `api/explorer_feed.php`)
   funcionan solas, sin volver a enganchar nada.

   QUÉ HACE
     1. La cabecera: los paneles de herramientas, avisos y cuenta, y la lupa del celular.
     2. Los cajones: el menú del celular, «todos los rubros» (con su buscador) y «publicar».
     3. Cada publicación: ⋯ (menú), «Ver más» del texto, 👍 **Me gusta de verdad** (se guarda en la
        base: `api/explorer_social.php?que=like`), 💬 **comentarios de la publicación** (se escriben y
        se ven, sin estrellas), ➦ Compartir y «Ver los N comentarios».
     4. **Scroll infinito**: al llegar al final del muro se pide la tanda siguiente y se pega debajo
        (ya no hay botón «Ver más publicaciones», como pidió el jefe).
     5. Cuenta los clics de la publicidad (`a[data-banner]`), como hace `banners.js` en la portada.

   ⚠️ AQUÍ NO HAY CARRITO (orden del jefe, 2026-09-16): el ❤️ «Me interesa» es del carrito de compras
   del sitio y se queda en la portada y en las fichas. El Explorer es para **explorar**: su botón es
   un **me gusta** normal, que no agrega nada al pedido (solo dice «esto me gusta»).

   LO QUE SE GUARDA EN EL NAVEGADOR (localStorage, nunca en el servidor):
     · `exp_guardadas` → las tiendas que guardaste con «Guardar».
     · `exp_ocultas`   → las publicaciones que mandaste a «No me interesa».
   Los me gusta y los comentarios viven en la BASE (para que el jefe pueda ver qué le gusta a la
   gente); el carrito sigue viviendo en el sitio, con su propia memoria (`CZ_CARRITO`).
   ============================================================================ */
(function () {
    'use strict';

    var SITE   = (window.SITE_URL || '').replace(/\/+$/, '');
    var CSRF   = window.CSRF_TOKEN || '';
    var $  = function (s, c) { return (c || document).querySelector(s); };
    var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };

    /* ------------------------------------------------------------------ */
    /* La memoria del navegador (con red de seguridad si está bloqueada)  */
    /* ------------------------------------------------------------------ */
    function leer(clave, porDefecto) {
        try {
            var v = window.localStorage.getItem(clave);
            return v ? JSON.parse(v) : porDefecto;
        } catch (e) { return porDefecto; }
    }
    function escribir(clave, valor) {
        try { window.localStorage.setItem(clave, JSON.stringify(valor)); } catch (e) {}
    }

    var guardadas = leer('exp_guardadas', {});

    /* ------------------------------------------------------------------ */
    /* Un avisito abajo (para decir «listo» o el error del servidor)      */
    /* ------------------------------------------------------------------ */
    var avisoNodo = null, avisoReloj = null;
    function avisar(texto, malo) {
        if (!avisoNodo) {
            avisoNodo = document.createElement('div');
            avisoNodo.className = 'exp-toast';
            document.body.appendChild(avisoNodo);
        }
        avisoNodo.textContent = texto;
        avisoNodo.className = 'exp-toast is-visible' + (malo ? ' exp-toast--malo' : '');
        if (avisoReloj) window.clearTimeout(avisoReloj);
        avisoReloj = window.setTimeout(function () {
            avisoNodo.className = 'exp-toast';
        }, 4200);
    }

    /* ------------------------------------------------------------------ */
    /* 1) LA CABECERA                                                     */
    /* ------------------------------------------------------------------ */
    function cerrarPaneles(menos) {
        $$('[data-ex-caja]').forEach(function (caja) {
            var p = $('.exh__panel', caja);
            if (p && p !== menos) p.hidden = true;
        });
    }

    document.addEventListener('click', function (ev) {
        var t = ev.target;

        // --- los paneles de la cabecera (herramientas, avisos, cuenta)
        var btnPanel = t.closest ? t.closest('[data-ex-caja-btn]') : null;
        if (btnPanel) {
            var panel = $('.exh__panel', btnPanel.closest('[data-ex-caja]'));
            var abrir = panel && panel.hidden;
            cerrarPaneles(abrir ? panel : null);
            if (panel) panel.hidden = !abrir;
            return;
        }
        if (!t.closest || !t.closest('.exh__panel')) cerrarPaneles(null);

        // --- la lupa del celular
        var lupa = t.closest ? t.closest('[data-ex-buscar]') : null;
        if (lupa) {
            var cab = $('#expCabecera');
            var abierto = cab.classList.toggle('is-buscando');
            if (abierto) {
                var inp = $('#buscador-fuzzy');
                if (inp) window.setTimeout(function () { inp.focus(); }, 60);
            }
            return;
        }

        // --- las herramientas que abren algo de casa
        var accion = t.closest ? t.closest('[data-ex-accion]') : null;
        if (accion) {
            var que = accion.getAttribute('data-ex-accion');
            if (que === 'chat') {
                ev.preventDefault();
                var fab = document.getElementById('cbotFab');
                if (fab) fab.click();
                else window.location.href = SITE + '/buscar.php';
            } else if (que === 'carrito') {
                ev.preventDefault();
                if (window.CZ_CARRITO) window.CZ_CARRITO.abrir();
                else window.location.href = SITE + '/buscar.php';
            }
            return;
        }

        // --- 📣 la publicidad: cuenta el clic igual que en la portada
        var banner = t.closest ? t.closest('a[data-banner]') : null;
        if (banner) {
            var id = banner.getAttribute('data-banner');
            if (id) {
                try {
                    fetch(SITE + '/api/banners.php?action=clic&id=' + encodeURIComponent(id), { keepalive: true });
                } catch (e) {}
            }
        }
    });

    document.addEventListener('keydown', function (ev) {
        if (ev.key !== 'Escape') return;
        cerrarPaneles(null);
        cerrarCajones();
        cerrarMenu();
    });

    /* ------------------------------------------------------------------ */
    /* 2) LOS CAJONES (rubros · publicar) Y EL MENÚ DEL CELULAR           */
    /* ------------------------------------------------------------------ */
    var velo = document.getElementById('expVelo');

    /**
     * ¿Hay alguna capa abierta (el menú del celular o un cajón)? Se apunta en el `<body>` porque el
     * CSS necesita saberlo para esconder el botón de publicar y la barra del pedido del sitio
     * (si no, flotan por encima del cajón y estorban).
     */
    function actualizarCapa() {
        var menu = document.getElementById('expIzq');
        var cajon = $$('.exp-cajon').some(function (c) { return !c.hidden; });
        var abierta = cajon || (menu && menu.classList.contains('is-abierto'));
        document.body.classList.toggle('exp-capa-abierta', !!abierta);
        document.documentElement.style.overflow = abierta ? 'hidden' : '';
        return !!abierta;
    }

    function abrirCajon(id) {
        var c = document.getElementById(id);
        if (!c) return;
        cerrarMenu();
        $$('.exp-cajon').forEach(function (x) { x.hidden = true; });
        c.hidden = false;
        if (velo) velo.hidden = false;
        actualizarCapa();
        var foco = c.querySelector('input, a, button');
        if (foco && c.id === 'expRubros') window.setTimeout(function () { foco.focus(); }, 80);
    }
    function cerrarCajones() {
        $$('.exp-cajon').forEach(function (x) { x.hidden = true; });
        var menu = document.getElementById('expIzq');
        var menuAbierto = menu && menu.classList.contains('is-abierto');
        if (velo) velo.hidden = menuAbierto;
        actualizarCapa();
    }
    function abrirMenu() {
        var izq = document.getElementById('expIzq');
        if (!izq) return;
        $$('.exp-cajon').forEach(function (x) { x.hidden = true; });
        izq.classList.add('is-abierto');
        if (velo) velo.hidden = false;
        actualizarCapa();
    }
    function cerrarMenu() {
        var izq = document.getElementById('expIzq');
        if (!izq) return;
        izq.classList.remove('is-abierto');
        if (velo) velo.hidden = true;
        actualizarCapa();
    }

    document.addEventListener('click', function (ev) {
        var t = ev.target;
        if (!t.closest) return;

        if (t.closest('[data-ex-abrir-rubros]')) { ev.preventDefault(); abrirCajon('expRubros'); return; }
        if (t.closest('[data-ex-publicar]'))     { ev.preventDefault(); abrirCajon('expPublicar'); return; }
        if (t.closest('[data-ex-menu]'))         { ev.preventDefault(); abrirMenu(); return; }
        if (t.closest('[data-ex-cerrar]'))       { ev.preventDefault(); cerrarCajones(); cerrarMenu(); return; }

        // 👆 TOCAR FUERA DEL CAJÓN LO CIERRA (queja del jefe, 2026-09-16: *«cuando le das en el botón
        //    de qué estás pensando te abre unas opciones… pero no hay manera de cerrarlo dando clic en
        //    otra parte»*). El cajón ocupa toda la pantalla, así que el «fondo» es él mismo: si el clic
        //    cae en el cajón (y no dentro de su caja blanca), se cierra. También cierra la ✕ y el Escape.
        var cajonAbierto = t.closest('.exp-cajon');
        if (cajonAbierto && !t.closest('.exp-cajon__caja')) {
            ev.preventDefault();
            cerrarCajones();
            return;
        }

        // Tocar un rubro del cajón cierra el cajón y deja navegar (es un enlace normal)
        if (t.closest('#expRubros a')) { cerrarCajones(); return; }

        // Tocar un enlace del menú del celular lo cierra
        var enMenu = t.closest('#expIzq a');
        if (enMenu && document.getElementById('expIzq').classList.contains('is-abierto')) cerrarMenu();
    });

    if (velo) {
        velo.addEventListener('click', function () { cerrarCajones(); cerrarMenu(); });
    }

    // 🗂️ El buscador de rubros del cajón (filtra mientras se escribe, sin listas largas)
    var buscaRubros = document.getElementById('expRubrosBusca');
    if (buscaRubros) {
        buscaRubros.addEventListener('input', function () {
            var q = buscaRubros.value.trim().toLowerCase();
            $$('#expRubros [data-ex-rubro]').forEach(function (a) {
                var txt = a.getAttribute('data-ex-rubro') || '';
                a.hidden = q !== '' && txt.indexOf(q) === -1;
            });
        });
    }

    /* ------------------------------------------------------------------ */
    /* 3) CADA PUBLICACIÓN                                                */
    /* ------------------------------------------------------------------ */
    document.addEventListener('click', function (ev) {
        var t = ev.target;
        if (!t.closest) return;

        /* --- ⋯ el menú de la publicación --- */
        var btnMenu = t.closest('[data-ex-menu-btn]');
        if (btnMenu) {
            var caja = $('.exp-menu__caja', btnMenu.closest('[data-ex-menu]'));
            var abrir = caja && caja.hidden;
            $$('.exp-menu__caja').forEach(function (c) { c.hidden = true; });
            if (caja) caja.hidden = !abrir;
            return;
        }
        if (!t.closest('.exp-menu__caja')) {
            $$('.exp-menu__caja').forEach(function (c) { c.hidden = true; });
        }

        /* --- «Ver más» del texto --- */
        var mas = t.closest('[data-ex-mas]');
        if (mas) {
            var p = $('[data-ex-texto]', mas.closest('.exp-post__cuerpo'));
            if (p) p.textContent = mas.getAttribute('data-ex-completo') || p.textContent;
            mas.remove();
            return;
        }

        /* --- 👍 ME GUSTA (de verdad: se guarda en la base para saber qué le gusta a la gente) --- */
        var like = t.closest('[data-ex-like]');
        if (like) {
            if (like.getAttribute('data-ex-ocupado') === '1') return;
            like.setAttribute('data-ex-ocupado', '1');
            var clave = like.getAttribute('data-ex-like');
            apiSocial({ que: 'like', post: clave }).then(function (j) {
                like.removeAttribute('data-ex-ocupado');
                if (!j || !j.ok) {
                    avisar((j && j.error) ? j.error : 'No se pudo guardar tu me gusta.', true);
                    return;
                }
                pintarLike(like, j.n, j.mio);
            }).catch(function () {
                like.removeAttribute('data-ex-ocupado');
                avisar('No se pudo guardar tu me gusta. Revisa tu conexión.', true);
            });
            return;
        }

        /* --- 💬 COMENTAR: abre el bloque de comentarios de ESA publicación --- */
        var abrirC = t.closest('[data-ex-comentar]');
        if (abrirC) {
            var postC = abrirC.closest('.exp-post');
            var cajaC = $('.exp-coment', postC);
            if (cajaC) {
                var estabaOculta = cajaC.hidden;
                cajaC.hidden = !estabaOculta;
                if (!cajaC.hidden) {
                    var campo = $('.exp-coment__campo', cajaC);
                    if (campo) window.setTimeout(function () { campo.focus(); }, 60);
                }
            }
            return;
        }

        /* --- «Ver los N comentarios»: pide TODOS los de esa publicación --- */
        var verC = t.closest('[data-ex-ver-coment]');
        if (verC) {
            if (verC.getAttribute('data-ex-ocupado') === '1') return;
            verC.setAttribute('data-ex-ocupado', '1');
            var claveV = verC.getAttribute('data-ex-ver-coment');
            var cajaV = verC.closest('.exp-coment');
            var listaV = cajaV ? $('[data-ex-coment-lista]', cajaV) : null;
            verC.textContent = 'Cargando comentarios…';
            apiComentarios(claveV).then(function (j) {
                verC.removeAttribute('data-ex-ocupado');
                if (!j || !j.ok) {
                    verC.textContent = 'No se pudieron cargar los comentarios. Toca para reintentar.';
                    return;
                }
                if (listaV) {
                    listaV.innerHTML = (j.html || '');
                }
                verC.remove();   // ya están todos a la vista
            }).catch(function () {
                verC.removeAttribute('data-ex-ocupado');
                verC.textContent = 'No se pudieron cargar los comentarios. Toca para reintentar.';
            });
            return;
        }

        /* --- ➦ Compartir --- */
        var comp = t.closest('[data-ex-compartir]');
        if (comp) {
            var url = comp.getAttribute('data-ex-compartir');
            var tit = comp.getAttribute('data-ex-titulo') || 'DeChimbote.com';
            if (navigator.share) {
                navigator.share({ title: tit, text: tit + ' · DeChimbote.com', url: url }).catch(function () {});
            } else {
                window.open('https://wa.me/?text=' + encodeURIComponent(tit + ' 👉 ' + url), '_blank', 'noopener');
            }
            return;
        }

        /* --- 🔖 Guardar / 🚫 No me interesa --- */
        var guardar = t.closest('[data-ex-guardar]');
        if (guardar) {
            var gid = guardar.getAttribute('data-ex-guardar');
            guardadas[gid] = !guardadas[gid];
            escribir('exp_guardadas', guardadas);
            avisar(guardadas[gid] ? 'Guardada en tu lista 🔖' : 'Quitada de tu lista');
            return;
        }
        var ocultar = t.closest('[data-ex-ocultar]');
        if (ocultar) {
            var artX = ocultar.closest('.exp-post');
            if (artX) {
                artX.style.display = 'none';
                var clave = artX.getAttribute('data-ex-post');
                if (clave) {
                    var ocultas = leer('exp_ocultas', {});
                    ocultas[clave] = 1;
                    escribir('exp_ocultas', ocultas);
                }
                avisar('Listo, no te volveremos a mostrar esa publicación');
            }
            return;
        }
        var seguir = t.closest('[data-ex-seguir]');
        if (seguir) {
            var sid = seguir.getAttribute('data-ex-seguir');
            guardadas[sid] = true;
            escribir('exp_guardadas', guardadas);
            seguir.textContent = 'Guardada ✓';
            avisar('Guardada en tu lista 🔖');
        }
    });

    /* ------------------------------------------------------------------ */
    /* El 👍 ME GUSTA: pintar el botón y el resumen de arriba             */
    /* ------------------------------------------------------------------ */
    /* El número que se enseña es **de personas** (una por visitante: la clave única de la tabla lo
       garantiza), y dice «Tú y N personas más» cuando el mío está puesto, igual que el muro. */
    function pintarLike(btn, n, mio, animar) {
        n = parseInt(n, 10) || 0;
        btn.classList.toggle('is-on', !!mio);
        btn.setAttribute('aria-pressed', mio ? 'true' : 'false');
        btn.title = mio ? 'Quitar mi me gusta' : 'Me gusta';

        var txt = $('[data-ex-like-txt]', btn);
        if (txt) txt.textContent = mio ? 'Te gusta' : 'Me gusta';
        var num = $('[data-ex-like-n]', btn);
        if (num) {
            num.textContent = n > 0 ? String(n) : '';
            num.hidden = n <= 0;
        }

        var post = btn.closest('.exp-post');
        var resumen = post ? $('[data-ex-resumen]', post) : null;
        if (resumen) {
            if (n === 0)          resumen.textContent = 'Sé el primero en dar me gusta';
            else if (mio && n === 1) resumen.textContent = 'Te gusta a ti';
            else if (mio)         resumen.textContent = 'Tú y ' + (n - 1) + (n - 1 === 1 ? ' persona más' : ' personas más');
            else                  resumen.textContent = n + (n === 1 ? ' persona' : ' personas') + ' les gusta';
        }
        if (animar) {
            var globo = post ? $('.exp-post__globito', post) : null;
            if (globo) {
                globo.style.transform = 'scale(1.35)';
                window.setTimeout(function () { globo.style.transform = ''; }, 180);
            }
        }
    }

    /* ------------------------------------------------------------------ */
    /* 3.bis) HABLAR CON EL SERVIDOR (me gusta y comentarios)             */
    /* ------------------------------------------------------------------ */
    /* Las dos cosas pasan por `api/explorer_social.php`, que es quien guarda en la base:
         · `que=like`       → prende o apaga MI me gusta y devuelve el total
         · `que=comentar`   → publica un comentario y devuelve el comentario y el total
         · `que=comentarios`→ devuelve TODOS los comentarios de una publicación
       Todas las escrituras llevan el token CSRF (el mismo del resto del sitio). */
    function apiSocial(datos) {
        var cuerpo = new FormData();
        cuerpo.append('_csrf', CSRF);
        Object.keys(datos).forEach(function (k) { cuerpo.append(k, datos[k]); });
        return fetch(SITE + '/api/explorer_social.php', {
            method: 'POST',
            body: cuerpo,
            credentials: 'same-origin',
            headers: { 'X-Requested-With': 'fetch' },
        }).then(function (r) {
            return r.json().catch(function () { return { ok: false, error: 'Respuesta inesperada del servidor.' }; });
        });
    }

    function apiComentarios(clave) {
        var partes = String(clave).split('-');
        if (partes.length !== 2) return Promise.resolve({ ok: false });
        return fetch(SITE + '/api/explorer_social.php?que=comentarios&tipo=' + encodeURIComponent(partes[0])
            + '&id=' + encodeURIComponent(partes[1]), { credentials: 'same-origin' })
            .then(function (r) { return r.json().catch(function () { return { ok: false }; }); });
    }

    /* ------------------------------------------------------------------ */
    /* 3.ter) ESCRIBIR UN COMENTARIO (💬)                                 */
    /* ------------------------------------------------------------------ */
    /* Sin estrellas: esto es un comentario de la publicación, no una opinión de la ficha. El servidor
       decide el apodo (el nombre de quien tiene sesión o un apodo amable) y devuelve el comentario. */
    document.addEventListener('submit', function (ev) {
        var form = ev.target;
        if (!form || !form.hasAttribute || !form.hasAttribute('data-ex-comentar-form')) return;
        ev.preventDefault();

        var clave = form.getAttribute('data-post');
        var campo = $('.exp-coment__campo', form);
        var boton = $('.exp-coment__enviar', form);
        var caja  = form.closest('.exp-coment');
        var texto = campo ? campo.value.trim() : '';

        if (texto.length < 2) {
            avisar('Escribe tu comentario, por favor.', true);
            if (campo) campo.focus();
            return;
        }

        if (boton) boton.disabled = true;
        var post = form.closest('.exp-post');
        apiSocial({
            que: 'comentar',
            post: clave,
            texto: texto,
            neg: post ? (post.getAttribute('data-neg') || 0) : 0,
            prod: post ? (post.getAttribute('data-prod') || 0) : 0,
        })
        .then(function (j) {
            if (boton) boton.disabled = false;
            if (!j || !j.ok) {
                avisar((j && j.error) ? j.error : 'No se pudo publicar tu comentario.', true);
                return;
            }
            var o = j.comentario || {};
            var lista = $('[data-ex-coment-lista]', caja);
            if (lista) {
                var fila = document.createElement('div');
                fila.className = 'exp-coment__fila';
                fila.innerHTML = '<span class="exp-av__txt exp-av__txt--chico" style="background:'
                    + escapar(o.color || '#0866ff') + '">' + escapar(o.inicial || 'V') + '</span>'
                    + '<div class="exp-coment__globo">'
                    + '<p class="exp-coment__autor">' + escapar(o.autor || 'Visitante') + '</p>'
                    + '<p class="exp-coment__texto">' + escapar(o.texto || texto) + '</p>'
                    + '<p class="exp-coment__fecha">Ahora mismo</p>'
                    + '</div>';
                lista.appendChild(fila);
            }
            if (campo) campo.value = '';
            // El contador de la fila de acciones y el resumen de arriba suben al instante
            var n = parseInt(j.n, 10) || 0;
            if (post) {
                var btn = $('[data-ex-comentar]', post);
                var num = btn ? $('[data-ex-com-n]', btn) : null;
                if (num) { num.textContent = n > 0 ? String(n) : ''; num.hidden = n <= 0; }
                var enlace = $('.exp-post__enlace', post);
                if (enlace) {
                    enlace.textContent = '💬 ' + n + (n === 1 ? ' comentario' : ' comentarios');
                }
            }
            avisar('¡Publicado! 💬');
        })
        .catch(function () {
            if (boton) boton.disabled = false;
            avisar('No se pudo enviar tu comentario. Revisa tu conexión e inténtalo otra vez.', true);
        });
    });

    function escapar(t) {
        return String(t == null ? '' : t)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    /* ------------------------------------------------------------------ */
    /* 4.bis) EL BOTÓN DISCRETO «REGRESAR A LA PORTADA»                   */
    /* ------------------------------------------------------------------ */
    /* Pedido del jefe (2026-09-16): *«incluye algún botón así de manera extemporánea, cuando el usuario
       va dando scroll, que diga "regresar a la portada", así discreto nada más, cada cierta cantidad
       que el usuario hace scroll»*.
       Se enseña cuando el visitante ya bajó **dos pantallas** (no antes: así no estorba al que recién
       llega) y se esconde al volver arriba. Va con `requestAnimationFrame` para no castigar el scroll. */
    function vigilarPortada() {
        var btn = document.getElementById('expPortada');
        if (!btn) return;
        var pendiente = false, visible = false;

        function revisar() {
            pendiente = false;
            var y = window.pageYOffset || document.documentElement.scrollTop || 0;
            var alto = window.innerHeight || 700;
            var debe = y > (alto * 2);
            if (debe === visible) return;
            visible = debe;
            if (debe) {
                btn.hidden = false;
                // el navegador necesita un cuadro con el botón ya pintado para poder animar la entrada
                requestAnimationFrame(function () { btn.classList.add('is-visible'); });
            } else {
                btn.classList.remove('is-visible');
                window.setTimeout(function () { if (!visible) btn.hidden = true; }, 220);
            }
        }
        window.addEventListener('scroll', function () {
            if (pendiente) return;
            pendiente = true;
            requestAnimationFrame(revisar);
        }, { passive: true });
        revisar();
    }

    /* ------------------------------------------------------------------ */
    /* 4) SCROLL INFINITO (el muro se sigue solo, como Facebook)          */
    /* ------------------------------------------------------------------ */
    /* El jefe lo pidió así: *«el botón de ver más publicaciones que aparece en la parte de abajo
       también desaparece, lo debe ser un scroll infinito así como tiene Facebook»*. Cuando el
       visitante llega al centinela del final, se pide la tanda siguiente a `api/explorer_feed.php`
       —con la MISMA semilla del muro para no repetir ni saltarse nada— y se pega debajo. */
    var cargando = false;
    var paginaActual = parseInt(document.body.getAttribute('data-pagina'), 10) || 1;

    function cargarMas() {
        if (cargando) return;
        var centinela = $('[data-ex-centinela]');
        if (!centinela) return;

        cargando = true;
        var aviso = $('[data-ex-cargando]');
        if (aviso) aviso.hidden = false;

        var q = new URLSearchParams(window.location.search);
        q.set('p', String(paginaActual + 1));
        q.set('s', String(window.EXP_SEMILLA || 0));
        q.delete('post');

        fetch(SITE + '/api/explorer_feed.php?' + q.toString(), { credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (j) {
                cargando = false;
                if (aviso) aviso.hidden = true;
                if (!j || !j.ok) {
                    // No se pudo: se deja el centinela y se reintenta cuando el visitante vuelva a bajar
                    avisar('No pudimos cargar más publicaciones. Sigue bajando para reintentar.', true);
                    return;
                }
                var cont = document.getElementById('expPosts');
                if (cont && j.html) cont.insertAdjacentHTML('beforeend', j.html);
                aplicarOcultas();
                paginaActual = parseInt(j.pagina, 10) || (paginaActual + 1);
                document.body.setAttribute('data-pagina', String(paginaActual));

                if (!j.hay_mas) {
                    var caja = $('.exp-mas');
                    if (caja) {
                        caja.innerHTML = '<p class="exp-mas__fin">Ya viste todo lo de este filtro 🎉 '
                            + '<a href="' + SITE + '/buscar.php">Busca algo concreto</a></p>';
                    }
                    if (observador) observador.disconnect();
                }
            })
            .catch(function () {
                cargando = false;
                if (aviso) aviso.hidden = true;
                avisar('No pudimos cargar más publicaciones. Sigue bajando para reintentar.', true);
            });
    }

    // El observador: en cuanto el centinela asoma en pantalla (600 px antes), se pide la tanda.
    var observador = null;
    function vigilarCentinela() {
        var centinela = $('[data-ex-centinela]');
        if (!centinela) return;
        if (!('IntersectionObserver' in window)) {
            // Navegador viejo: con bajar hasta el final basta (se mira el scroll)
            window.addEventListener('scroll', function () {
                if ((window.innerHeight + window.scrollY) > (document.body.offsetHeight - 800)) cargarMas();
            }, { passive: true });
            return;
        }
        observador = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (e) { if (e.isIntersecting) cargarMas(); });
        }, { rootMargin: '600px 0px' });
        observador.observe(centinela);
    }

    /* ------------------------------------------------------------------ */
    /* 5) AL ABRIR LA PÁGINA                                              */
    /* ------------------------------------------------------------------ */
    function aplicarOcultas() {
        var ocultas = leer('exp_ocultas', {});
        Object.keys(ocultas).forEach(function (clave) {
            var art = document.querySelector('[data-ex-post="' + clave + '"]');
            if (art) art.style.display = 'none';
        });
    }

    function arrancar() {
        aplicarOcultas();
        // 📜 El scroll infinito: se pone a vigilar el centinela del final del muro.
        vigilarCentinela();
        // ⬆️ El botón discreto de «Regresar a la portada» (aparece al bajar dos pantallas).
        vigilarPortada();
        // 🗂️ Si la dirección pide el cajón de rubros (`?panel=rubros`), se abre solo.
        if (document.body.getAttribute('data-panel') === 'rubros') abrirCajon('expRubros');
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', arrancar);
    } else {
        arrancar();
    }
})();
