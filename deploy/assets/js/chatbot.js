/**
 * assets/js/chatbot.js — LA CONVERSACIÓN del chat de ayuda de dechimbote.com
 * =======================================================================
 * Qué hace: abre y cierra la ventana del chat, pinta los mensajes, manda la pregunta a
 * `api/chatbot.php` (que es quien habla con la API de DeepSeek) y recuerda la conversación
 * en el navegador del visitante para que no tenga que repetir el contexto.
 *
 * Lo pinta `includes/chatbot_widget.php` en TODAS las páginas. Los datos que necesita
 * llegan en `window.CHATBOT_CFG` (nunca la clave de DeepSeek: esa vive en el servidor).
 *
 * Reglas del proyecto que respeta:
 *  · Móvil-primero: fuente de 16 px (el iPhone no hace zoom al escribir), botones grandes.
 *  · Nada de librerías: JavaScript puro, sin dependencias.
 *  · Si no hay JavaScript, el sitio sigue igual (el widget simplemente no aparece).
 *  · El historial se guarda 12 h como máximo y se puede borrar desde el propio chat (🗑️).
 *
 * Guía: GUIA_CHATBOT_DEEPSEEK.md
 */
(function () {
    'use strict';

    var CFG = window.CHATBOT_CFG;
    if (!CFG || !CFG.api) return;
    if (window.__cbotListo) return;
    window.__cbotListo = true;

    var CLAVE_LS  = 'chimbote_chat_ayuda_v1';
    var HORAS_VIDA = 12;          // el historial del navegador caduca a las 12 h
    var MAX_GUARDA = 24;          // mensajes guardados como máximo

    var fab     = document.getElementById('cbotFab');
    var panel   = document.getElementById('cbotPanel');
    var fondo   = document.getElementById('cbotFondo');
    var cuerpo  = document.getElementById('cbotCuerpo');
    var chips   = document.getElementById('cbotChips');
    var form    = document.getElementById('cbotForm');
    var entrada = document.getElementById('cbotTexto');
    var enviar  = document.getElementById('cbotEnviar');
    // ⛔ AQUÍ VIVÍA EL MICRÓFONO (`#cbotMicro`, el botón de tres caras 🎤🛑➤) Y LA GRABADORA: se
    // BORRARON el 2026-09-20 por orden del jefe (*«quiero que borres todo lo que tenga que ver con la
    // voz dentro de la brújula… muéstralo simplemente como un chatbot normal»*). El guía escribe y
    // manda fotos; no dicta. Lo retirado está ARCHIVADO en `GUIA_CHATBOT_DEEPSEEK.md` (apartados 11-13
    // y trampas 36 y 37): si algún día se quisiera reponer, se lee ahí, no se reinventa.
    // ✕ CERRAR (orden del jefe, 2026-09-20): *«al ladito de la escoba agrégale una ✕ para poder cerrar
    // la guía»*. Es la ✕ que el jefe había pedido quitar el 2026-09-13 (entonces se cerraba solo
    // tocando fuera); ahora vuelve, fija en la cabecera, al lado de la 🧹. El fondo y Escape siguen
    // cerrando también.
    var bCerrar = document.getElementById('cbotCerrar');
    var bIdeas  = document.getElementById('cbotIdeas');
    var punto   = document.getElementById('cbotPunto');
    var reloj   = null;   // (el reloj ya no existe: no se pinta ningún contador)
    var relojT  = null;
    var estadoT = document.getElementById('cbotEstado');
    var estadoInicial = estadoT ? estadoT.textContent : '';   // para poder volver a él tras limpiar
    var cierre  = document.getElementById('cbotCierre');
    var cierreT = document.getElementById('cbotCierreTxt');
    var camBtn  = document.getElementById('cbotCam');
    // 📷 EL MENÚ DE LA CÁMARA, CHIQUITO Y PEGADO AL BOTÓN (orden del jefe, 2026-09-20): al tocar la 📷
    // sale **justo encima** un menú discreto —como el ☰ del sitio, pero con icono de cámara— con dos
    // renglones: **📷 Abrir cámara** y **🖼️ Abrir galería**. Antes esto era una ventana modal centrada
    // con su cajita y dos botones grandes, y el jefe la rechazó: *«se ve muy llamativo, como un banner;
    // debe ser discreto nomás»*. Cada opción tiene su propia entrada de archivo (la de la cámara lleva
    // `capture`, así el celular abre la cámara de frente).
    var camMenu   = document.getElementById('cbotCamMenu');
    var camFoto   = document.getElementById('cbotCamFoto');       // 📷 Abrir cámara
    var camGal    = document.getElementById('cbotCamGaleria');    // 🖼️ Abrir galería
    var archCam   = document.getElementById('cbotArchivoCam');    // input con capture (cámara)
    var archGal   = document.getElementById('cbotArchivoGal');    // input sin capture (galería)
    var adjunto = document.getElementById('cbotAdjunto');
    var adImg   = document.getElementById('cbotAdjuntoImg');
    var adTxt   = document.getElementById('cbotAdjuntoTxt');
    var adQuitar= document.getElementById('cbotAdjuntoQuitar');
    var cabecera = panel ? panel.querySelector('.cbot-head') : null;

    if (!fab || !panel || !cuerpo || !form || !entrada) return;

    var hist = [];         // [{rol:'user'|'bot', texto:'…', requiere_login?:bool}]
    var ocupado = false;   // hay una pregunta en la aire
    var abierto = false;
    var bloq    = false;   // 🔢 el chat está cerrado: se le acabaron las preguntas del día
    var vacio   = false;   // 🧹 el visitante tocó «Limpiar el chat»: la ventana quedó SIN NADA
    var avisoVisto = false;
    var imgPendiente = ''; // 👁️ imagen lista para mandar (data URL), o '' si no hay
    // 🔢 La cuota de preguntas la lleva SIEMPRE el servidor: aquí solo se guarda lo último que dijo
    // (plan y si ya se agotó). El navegador NO cuenta nada ni pinta ningún contador.
    var cuota = CFG.cuota || { activo: false, plan: 'visitante', sin_limite: false, iniciado: false, limite: 0, usadas: 0, restantes: 0, agotada: false };

    // 🧭 LA TIENDA DONDE ESTÁ EL VISITANTE (2026-09-17): el slug de la ficha viaja en CADA petición
    // (en el saludo y en cada pregunta) para que el servidor cargue los datos de ESA tienda. El
    // servidor NO se cree este dato: con el slug vuelve a leer la tienda de nuestra base.
    function apiUrl() {
        var u = CFG.api;
        if (CFG.ficha) u += (u.indexOf('?') === -1 ? '?' : '&') + 'negocio=' + encodeURIComponent(CFG.ficha);
        return u;
    }

    // 🎬 EL EFECTO DE ENTRADA (pedido del jefe, 2026-09-17): al cargar la ficha, el chat se abre
    // desde el botón y se vuelve a cerrar solo (para que se vea que ahí hay un chat), y después queda
    // el círculo con su halo y sus movimientos persuasivos. Se hace UNA vez por sesión del navegador
    // (si no, cada página sería un sobresalto) y se respeta «reducir movimiento».
    // 🧭 Y DESDE EL 2026-09-20 (orden del jefe) el efecto de entrada se queda, pero la brújula **sigue
    // moviéndose cada 6,5 s** para llamar la atención: ver `brujulaViva()`.
    var CLAVE_POP  = 'chimbote_guia_pop_v1';
    var popTimer   = null;
    var popEnCurso = false;

    // ==================== 1) Historial del navegador ====================
    function cargar() {
        try {
            var crudo = localStorage.getItem(CLAVE_LS);
            if (!crudo) return;
            var d = JSON.parse(crudo);
            if (!d || !d.t || !d.m) return;
            if (Date.now() - d.t > HORAS_VIDA * 3600 * 1000) { localStorage.removeItem(CLAVE_LS); return; }
            hist = d.m.filter(function (m) { return m && m.texto; });
        } catch (e) { hist = []; }
    }
    function guardar() {
        try {
            // ⚠️ Las IMÁGENES no se guardan en el navegador (pesan mucho y llenarían el almacén):
            // se guarda solo la marca de que hubo una foto.
            var limpio = hist.slice(-MAX_GUARDA).map(function (m) {
                var c = { rol: m.rol, texto: m.texto };
                if (m.requiere_login) c.requiere_login = 1;
                if (m.dia) c.dia = 1;
                if (m.actividad) c.actividad = 1;
                if (m.foto) c.conFoto = 1;
                return c;
            });
            localStorage.setItem(CLAVE_LS, JSON.stringify({ t: Date.now(), m: limpio }));
        } catch (e) { /* modo privado / sin espacio: se sigue sin memoria */ }
    }

    // ==================== 1.b) ❤️ La lista «Me interesa» del navegador ====================
    // Vive en localStorage (`cz_carrito_v1`, la misma que usa carrito.js). Se manda con cada
    // pregunta SOLO si hay sesión iniciada: así «El ninja» puede decir «vi que te interesa X».
    function leerCarrito() {
        try {
            var o = JSON.parse(localStorage.getItem('cz_carrito_v1') || '{}');
            var out = [], k;
            for (k in o) { if (Object.prototype.hasOwnProperty.call(o, k) && o[k] && o[k].it) out.push(o[k]); }
            return out.slice(0, 8);
        } catch (e) { return []; }
    }

    // ==================== 2) Pintar texto (markdown mínimo y seguro) ====================
    function esc(s) {
        return String(s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }

    // Deja los enlaces tocables. 🔗 NUNCA se muestra una dirección completa (orden del jefe,
    // 2026-09-13): si el enlace no trae nombre, se le pone uno. Y tampoco textos de relleno tipo
    // «ver tienda» (segunda orden del mismo día): **el nombre de la tienda, del aviso, del rubro o
    // del distrito ES el enlace**. Este mapa es el espejo del PHP `chatbot_etiqueta_enlace()`.
    var ETIQUETAS = {
        '/registro': 'crear tu cuenta gratis',
        '/registro.php': 'crear tu cuenta gratis',
        '/login.php': 'entrar a tu cuenta',
        '/perfil': 'donde administras tu tienda',
        '/panel.php': 'donde administras tu tienda',
        '/productos.php': 'tus productos',
        '/crear-tienda': 'donde El maestro te arma la tienda',
        '/empleos': 'la sección de empleos',
        '/noticias': 'las noticias de hoy',
        '/buscar': 'el buscador',
        '/buscar.php': 'el buscador',
        '/caminante': 'publicar sin cuenta',
        '/reclamar': 'reclamar tu negocio',
        '/trabaja_con_nosotros.php': 'trabaja con nosotros',
        '/terminos.php': 'los términos',
        '/privacidad.php': 'la privacidad'
    };
    var MEDIOS = {
        'andina.pe': 'Agencia Andina', 'rpp.pe': 'RPP Noticias', 'elcomercio.pe': 'El Comercio',
        'larepublica.pe': 'La República', 'gob.pe': 'el Estado peruano', 'youtube.com': 'YouTube',
        'facebook.com': 'Facebook', 'news.google': 'Google Noticias'
    };

    /** «bodega-don-jose» → «Bodega Don Jose» (el nombre propio que hace de enlace). */
    function nombreDeSlug(s) {
        s = decodeURIComponent(String(s || '')).replace(/[-_+]+/g, ' ').replace(/\s+/g, ' ').trim();
        if (!s) return '';
        return s.replace(/\S+/g, function (p) { return p.charAt(0).toUpperCase() + p.slice(1).toLowerCase(); });
    }

    /**
     * 📰 Un título de noticia NUNCA pasa de CUATRO PALABRAS (orden del jefe, 2026-09-14): así el chat
     * no muestra titulares largos ni llamativos. El servidor ya manda los títulos cortos; esto es solo
     * la red de seguridad (por si llega una dirección de noticia pelada y hay que nombrarla).
     */
    function breve4(t) {
        var w = String(t || '').trim().split(/\s+/).filter(function (x) { return x !== ''; });
        if (w.length > 4) w = w.slice(0, 4);
        return w.join(' ');
    }

    function etiquetaEnlace(url) {
        var m = String(url).match(/^https?:\/\/([^\/]+)(\/[^?#]*)?/i);
        var host = m ? m[1].toLowerCase() : '';
        var ruta = (m && m[2]) ? m[2] : '/';
        if (ruta.length > 1) ruta = ruta.replace(/\/+$/, '');   // sin barra final
        if (ruta === '/' || ruta === '') return 'DeChimbote.com';
        if (ETIQUETAS[ruta]) return ETIQUETAS[ruta];
        // 👇 El NOMBRE es el enlace (nada de «ver la tienda» ni «ver el anuncio»).
        if (ruta.indexOf('/neg/') === 0) return nombreDeSlug(ruta.slice(5)) || 'DeChimbote.com';
        if (ruta.indexOf('/empleo/') === 0) return nombreDeSlug(ruta.slice(8)) || 'la sección de empleos';
        // 📰 Nuestra noticia: el enlace se llama como el título CORTO (4 palabras como máximo, orden
        //    del jefe 2026-09-14); el servidor ya manda el título corto escrito en el mensaje, esto es
        //    solo la red de seguridad si llega la dirección pelada.
        if (ruta.indexOf('/noticia/') === 0) return breve4(nombreDeSlug(ruta.slice(9))) || 'las noticias de hoy';
        if (ruta.indexOf('/categoria/') === 0) return nombreDeSlug(ruta.slice(11)) || 'los rubros';
        if (ruta.indexOf('/distrito/') === 0) return nombreDeSlug(ruta.slice(10)) || 'los distritos';
        if (host.indexOf('dechimbote.com') === -1) {
            if (host.indexOf('wa.me') !== -1 || host.indexOf('whatsapp') !== -1) return 'WhatsApp';
            for (var h in MEDIOS) { if (MEDIOS.hasOwnProperty(h) && host.indexOf(h) !== -1) return MEDIOS[h]; }
            return nombreDeSlug(host.replace(/^(www|m|amp|es|pe)\./, '').split('.')[0]) || 'la fuente';
        }
        return 'DeChimbote.com';
    }

    function pareceUrl(t) {
        return /^(https?:\/\/|www\.|dechimbote\.com\/)/i.test(String(t).trim());
    }

    function enlazar(t) {
        // 🖼️ Las FOTOS del buscador (`[![titulo](foto)](tienda)`) se apartan mientras se etiquetan los
        // enlaces: si no, el `[titulo](foto)` de dentro se tomaría por un enlace. Vuelven después.
        var fotos = [];
        t = t.replace(/\[?!\[[^\]]*\]\(https?:\/\/[^\s)]+\)\]?\(https?:\/\/[^\s)]+\)/g, function (todo) {
            fotos.push(todo);
            return '\u0001' + (fotos.length - 1) + '\u0001';
        });

        var re = /\[([^\]]+)\]\((https?:\/\/[^\s)]+)\)|((?:https?:\/\/|www\.)[^\s<)]+)|(dechimbote\.com\/[^\s<).,;:!?]+)/g;
        t = t.replace(re, function (todo, txt, url, suelto, corto) {
            var destino = url || suelto || corto || '';
            if (!destino) return todo;
            var cola = '';
            if (!url) {   // en los enlaces sueltos, la puntuación final no es parte de la dirección
                var m = destino.match(/[.,;:!?]+$/);
                if (m) { cola = m[0]; destino = destino.slice(0, -m[0].length); }
            }
            var href = /^https?:\/\//i.test(destino) ? destino : 'https://' + destino;
            // El texto visible: el que traía, salvo que sea la propia dirección.
            var visible = (url && txt && !pareceUrl(txt)) ? txt : etiquetaEnlace(href);
            return '<a href="' + href + '" target="_blank" rel="noopener">' + visible + '</a>' + cola;
        });

        if (fotos.length) {
            t = t.replace(/\u0001(\d+)\u0001/g, function (todo, i) { return fotos[+i] || ''; });
        }
        return t;
    }

    /** 🖼️ Convierte el markdown de fotos en <img>: la foto del resultado ENLAZADA a la tienda. */
    function fotos(t) {
        t = t.replace(/\[!\[([^\]]*)\]\(([^)\s]+)\)\]\(([^)\s]+)\)/g, function (todo, alt, img, url) {
            return '<a class="cbot-foto__link" href="' + url + '" target="_blank" rel="noopener">' +
                   '<img class="cbot-foto" src="' + img + '" alt="' + alt + '" loading="lazy"></a>';
        });
        return t.replace(/!\[([^\]]*)\]\(([^)\s]+)\)/g, function (todo, alt, img) {
            return '<img class="cbot-foto" src="' + img + '" alt="' + alt + '" loading="lazy">';
        });
    }

    function formato(texto) {
        var t = fotos(enlazar(esc(texto)));
        t = t.replace(/\*\*([^*]+)\*\*/g, '<b>$1</b>');
        // Enlaces recién creados no deben volver a procesarse: se protegen del reemplazo de guiones.
        var lineas = t.split('\n');
        var html = '', lista = '', i = 0;

        // 📊 LAS TABLAS (orden del jefe, 2026-09-17: *«si vas a ofrecer por ejemplo tablas, crea esas
        // tablas y procura que las tablas ocupen todo el ancho de la ventana modal»*). Se escriben así:
        //     | Producto | Precio |
        //     |---|---|
        //     | Membresía mensual | S/ 90 |
        // y aquí se convierten en una tabla de verdad, a todo el ancho de la ventana.
        function celdas(l) {
            return l.replace(/^\s*\|/, '').replace(/\|\s*$/, '').split('|').map(function (c) { return c.trim(); });
        }
        function esSep(l) { return /^[\s:\-|]+$/.test(l) && l.indexOf('-') !== -1 && l.indexOf('|') !== -1; }

        for (; i < lineas.length; i++) {
            var l = lineas[i];
            if (/^\s*\|/.test(l) && (i + 1) < lineas.length && esSep(lineas[i + 1])) {
                if (lista) { html += '</' + lista + '>'; lista = ''; }
                var cab = celdas(l), filas = [], k;
                i += 2;
                while (i < lineas.length && /^\s*\|/.test(lineas[i])) { filas.push(celdas(lineas[i])); i++; }
                i--;   // el `for` vuelve a avanzar
                var th = '';
                for (k = 0; k < cab.length; k++) th += '<th>' + cab[k] + '</th>';
                var tb = '';
                for (var f = 0; f < filas.length; f++) {
                    var tr = '';
                    for (k = 0; k < cab.length; k++) tr += '<td>' + (filas[f][k] || '') + '</td>';
                    tb += '<tr>' + tr + '</tr>';
                }
                html += '<table class="cbot-tabla"><thead><tr>' + th + '</tr></thead><tbody>' + tb + '</tbody></table>';
                continue;
            }
            var ul = l.match(/^\s*[-•*]\s+(.*)$/);
            var ol = l.match(/^\s*\d+[.)]\s+(.*)$/);
            if (ul) {
                if (lista !== 'ul') { if (lista) html += '</' + lista + '>'; html += '<ul>'; lista = 'ul'; }
                html += '<li>' + ul[1] + '</li>';
            } else if (ol) {
                if (lista !== 'ol') { if (lista) html += '</' + lista + '>'; html += '<ol>'; lista = 'ol'; }
                html += '<li>' + ol[1] + '</li>';
            } else {
                if (lista) { html += '</' + lista + '>'; lista = ''; }
                if (l.trim() !== '') html += '<p>' + l.replace(/  $/, '') + '</p>';
            }
        }
        if (lista) html += '</' + lista + '>';
        return html;
    }

    function pintar(quien, texto, opciones) {
        opciones = opciones || {};
        var d = document.createElement('div');
        d.className = 'cbot-msg ' + (quien === 'user' ? 'cbot-msg--yo' : 'cbot-msg--bot');
        var html = '';
        // 👁️ Si el mensaje lleva una foto (la que mandó el visitante), se ve en su burbuja.
        if (opciones.foto) html += '<img class="cbot-msg__foto" src="' + opciones.foto + '" alt="imagen enviada">';
        html += formato(texto);

        // Cuando la respuesta pide cuenta (los 10 consejos) o se le acabaron las preguntas, se
        // pintan los botones de verdad (registro / login / Premium por WhatsApp).
        if (opciones.requiere_login) {
            html += botonesCta('registro');
        } else if (opciones.cta) {
            html += botonesCta(opciones.cta);
        }
        d.innerHTML = html;
        cuerpo.appendChild(d);

        // 📐 DÓNDE QUEDA EL SCROLL (orden del jefe, 2026-09-13):
        //   · Si la respuesta/pregunta CORTA cabe entera en la ventana → abajo, como siempre.
        //   · Si NO cabe (respuesta larga): la pregunta del visitante se deja pegada ARRIBA y el
        //     scroll NO se mueve mientras la respuesta va creciendo; así el foco se queda en la
        //     pregunta y él va leyendo hacia abajo. Es la falla de usabilidad que él describió:
        //     «si te pregunto cómo se prepara un ceviche… te quedas ahí con la pantalla quieta para
        //     que sea yo el que vaya haciendo scroll».
        if (opciones.anclar) {
            if (hayDesborde()) anclar(d); else bajar();
        } else if (anclaViva()) {
            anclar(anclaEl);            // 📌 la pregunta sigue arriba: la respuesta crece debajo
        } else if (!hayDesborde()) {
            bajar();
        }
        return d;
    }

    // ==================== 2.b) Los botones de la invitación (registro / Premium) ====================
    function botonesCta(tipo) {
        if (tipo === 'premium') {
            var wa = CFG.wa_premium || CFG.wa;
            return '<div class="cbot-botones">' +
                   '<a class="cbot-btn--wa" href="' + wa + '" target="_blank" rel="noopener">' +
                   (CFG.wa_icono || '') + ' Hacerme Premium (' + (CFG.precio_premium || 'S/ 30 al mes') + ')</a></div>';
        }
        if (tipo === 'registro') {
            return '<div class="cbot-botones">' +
                   '<a href="' + CFG.registro + '">✅ Crear mi cuenta gratis</a>' +
                   '<a class="cbot-btn--suave" href="' + CFG.login + '">Ya tengo cuenta</a></div>';
        }
        return '';
    }

    function bajar() { cuerpo.scrollTop = cuerpo.scrollHeight; }

    /** ¿El contenido no cabe en la ventana del chat? (entonces hay que decidir dónde dejar el scroll) */
    function hayDesborde() { return cuerpo.scrollHeight > cuerpo.clientHeight + 4; }

    // 📌 EL ANCLA: mientras el visitante lee la respuesta, su pregunta se queda ARRIBA de la ventana y
    // la respuesta va creciendo hacia abajo (orden del jefe: «el foco de atención se debe quedar en la
    // pregunta del usuario… el usuario podrá ir leyendo mientras tú lo vas escribiendo»).
    var anclaEl       = null;   // la burbuja de la pregunta que se queda fija arriba
    var anclaActiva   = false;  // ¿seguimos anclados? (se apaga si el visitante mueve la pantalla)
    var anclaObjetivo = -1;     // la posición exacta que pusimos nosotros (para no confundirla con un scroll suyo)

    cuerpo.addEventListener('scroll', function () {
        if (!anclaActiva) return;
        if (Math.abs(cuerpo.scrollTop - anclaObjetivo) > 24) anclaActiva = false;   // movió él: manda él
    }, { passive: true });

    /** Deja la pregunta pegada ARRIBA de la zona visible (y se queda ahí mientras llega la respuesta). */
    function anclar(el) {
        anclaEl = el || anclaEl;
        if (!anclaEl) return;
        anclaActiva = true;
        requestAnimationFrame(function () {
            if (!anclaActiva || !anclaEl) return;
            if (!hayDesborde()) { anclaObjetivo = cuerpo.scrollTop = cuerpo.scrollHeight; return; }
            var rc = anclaEl.getBoundingClientRect(), cc = cuerpo.getBoundingClientRect();
            cuerpo.scrollTop   = Math.max(0, (rc.top - cc.top) + cuerpo.scrollTop - 10);
            anclaObjetivo      = cuerpo.scrollTop;
        });
    }

    /** ¿Hay que mantener la pregunta arriba? (solo si el visitante no ha movido la pantalla él mismo) */
    function anclaViva() { return anclaActiva && anclaEl && hayDesborde(); }

    // ⏳ EL «PENSANDO…» (orden del jefe, 2026-09-14): *«no olvides siempre ganar al menos uno o dos
    // segundos en el periodo de respuesta con el texto "pensando"… luego puedes usar "consultando" y
    // una segunda frase comodín que puede ser "respondiendo": hasta eso ya ganaste dos segundos que es
    // tiempo valiosísimo»*. Las frases son **FIJAS y vienen del servidor** (`CFG.pensando`, se editan
    // en Súper Admin → 🥷 Ninja (chat)): es texto repetido, así que se escribe UNA vez en el código y no
    // se le pide al modelo (gastar tokens en dos palabras fijas sería absurdo). El navegador las rota
    // mientras espera y `CFG.espera_min` es el **piso**: la respuesta no se pinta antes de ese tiempo.
    var pensandoTimer  = null;
    var pensandoPaso   = 0;
    var pensandoInicio = 0;

    function frasesPensando() {
        return (CFG.pensando && CFG.pensando.length) ? CFG.pensando : ['Pensando'];
    }

    function pintaPensando() {
        var d = document.getElementById('cbotPensando');
        if (!d) {
            d = document.createElement('div');
            d.className = 'cbot-pensando';
            d.id = 'cbotPensando';
            cuerpo.appendChild(d);
        }
        var f  = frasesPensando();
        var tx = f[Math.min(pensandoPaso, f.length - 1)];
        d.innerHTML = '<i></i><i></i><i></i><span class="cbot-pensando__txt">' + esc(tx) + '…</span>';
    }

    function pensando(mostrar) {
        if (pensandoTimer) { clearTimeout(pensandoTimer); pensandoTimer = null; }
        var viejo = document.getElementById('cbotPensando');
        if (!mostrar) { if (viejo) viejo.remove(); return; }

        pensandoPaso   = 0;
        pensandoInicio = Date.now();
        pintaPensando();

        var ms = parseInt(CFG.pensando_ms, 10);
        if (isNaN(ms) || ms < 200) ms = 700;
        var avanza = function () {
            if (!document.getElementById('cbotPensando')) return;      // ya salió la respuesta
            if (pensandoPaso >= frasesPensando().length - 1) return;    // se queda en la última frase
            pensandoPaso++;
            pintaPensando();
            pensandoTimer = setTimeout(avanza, ms);
        };
        pensandoTimer = setTimeout(avanza, ms);
        // (Sin `bajar()`: el «pensando» sale justo debajo de la pregunta, que es la que manda.)
    }

    /** ⏳ Cuántos ms faltan para cumplir el piso de espera (así el «pensando» se ve entero). */
    function faltaEspera() {
        var min = parseInt(CFG.espera_min, 10);
        if (isNaN(min) || min <= 0) return 0;
        return Math.max(0, min - (Date.now() - pensandoInicio));
    }

    // ==================== 2.c) 🔢 La cuota de preguntas (sin contadores a la vista) ====================
    // Orden del jefe (2026-09-13): el chat YA NO se limita por tiempo, se limita por CANTIDAD DE
    // PREGUNTAS (20 el visitante, 50 el registrado, 300 el ⭐ Premium), y el número es INTERNO:
    // «no es necesario que avises que le quedan tantas preguntas». Así que:
    //   · NO se pinta ningún contador ni cuenta atrás.
    //   · Cuando se le acaban, el servidor manda el mensaje de cierre y el chat se bloquea.
    // Quien manda es el SERVIDOR (`cuota.agotada`): el navegador solo obedece.
    var cierreTimer = null;

    function aplicarCuota(t) {
        if (t) cuota = t;
    }

    function textoCierre() {
        // ⚠️ SIN NÚMEROS (orden del jefe): no se dice cuántas preguntas eran ni cuántas quedan.
        if (cuota.plan === 'visitante') return 'Por hoy ya no puedo contestarte más. Crea tu cuenta gratis y seamos amigos: con cuenta te contesto muchísimo más.';
        if (cuota.plan === 'premium')   return 'El chat está cerrado por hoy.';
        return 'Por hoy lo dejamos aquí. Con Premium (S/ 30 al mes) no tiene límite de preguntas.';
    }

    function marcarCerrado() {
        fab.classList.add('cbot-fab--cerrado');
        brujulaViva(false);      // 🧭 cerrado por cuota: no hay nada que invitar, así que no se mueve
        fab.title = 'El chat está cerrado por hoy: ' + textoCierre();
    }

    function programaCierre() {
        if (cierreTimer) return;
        cierreTimer = setTimeout(function () { if (abierto) cerrar(); marcarCerrado(); },
                                 (CFG.cierre_seg || 15) * 1000);
    }

    /** Cierra el chat (el jefe: «se cierra indicando que debe estar registrado»). */
    function bloquear(texto, cta) {
        if (bloq) return;
        bloq = true;
        ocupado = false;
        pensando(false);
        autoEnvioCancelar();                 // 🚀 chat cerrado: no hay envío automático pendiente
        vozDetener();                        // 🎤 si estaba dictando, se corta el micrófono
        if (texto) pintar('bot', texto, { cta: cta || 'registro' });

        entrada.disabled = true;
        enviar.disabled = true;
        if (vozBtn) vozBtn.hidden = true;    // 🎤 chat cerrado por hoy: sin micrófono (como en el buscador)
        if (camBtn) camBtn.disabled = true;   // cerrado: tampoco se mandan fotos
        vozOcultarAviso();
        cerrarMenu();
        entrada.placeholder = 'Chat cerrado por hoy';
        form.classList.add('cbot-pie--cerrado');
        if (cabecera) cabecera.classList.add('cbot-head--cerrado');
        if (estadoT) estadoT.textContent = 'Chat cerrado por hoy';
        if (cierre && cierreT) { cierreT.textContent = textoCierre(); cierre.hidden = false; }
        mostrarChips(false);
        marcarCerrado();
        if (abierto) programaCierre();
    }

    /** Pregunta al servidor cómo va la cuota del día (y si ya hay que cerrar). */
    function refrescar() {
        fetch(apiUrl(), { credentials: 'same-origin' })
            .then(function (r) { return r.json().catch(function () { return null; }); })
            .then(function (d) {
                if (!d || d.ok === false) return;
                aplicarCuota(d.cuota);
                // 🧹 SI EL VISITANTE ACABA DE TOCAR «LIMPIAR EL CHAT», LA VENTANA SE QUEDA VACÍA (orden
                // del jefe, 2026-09-14): no se pinta NADA —ni las opciones, ni el saludo del día, ni la
                // actividad, ni el aviso de cierre— hasta que él escriba algo. La cuota sí se refresca
                // (es interna y no se ve).
                if (vacio) return;
                if (!bloq && Array.isArray(d.sugerencias) && d.sugerencias.length) pintarChips(d.sugerencias);
                // 📅 El saludo del día (fecha, hora y clima de Chimbote) solo se añade una vez y
                // solo si el visitante todavía no ha escrito nada.
                if (d.dia && !hist.some(function (m) { return m.dia || m.rol === 'user'; })) {
                    hist.push({ rol: 'bot', texto: d.dia, dia: true });
                    pintar('bot', d.dia);
                    guardar();
                }
                // 👀 Y si tiene sesión y ya hizo cosas en el sitio, se lo recordamos con gracia.
                if (d.actividad && !hist.some(function (m) { return m.actividad || m.rol === 'user'; })) {
                    hist.push({ rol: 'bot', texto: d.actividad, actividad: true });
                    pintar('bot', d.actividad);
                    guardar();
                }
                if (d.bloqueado && !bloq) bloquear(d.respuesta, d.cta);
                // Al abrir, el saludo del día se ve entero (aquí sí se baja: todavía no hay pregunta).
                if (!hist.some(function (m) { return m.rol === 'user'; })) bajar();
            })
            .catch(function () { /* sin conexión: se sigue hablando con el guion */ });
    }

    // ==================== 3) Los botones de preguntas frecuentes ====================
    // Cada opción puede ser un TEXTO (se manda como pregunta) o un objeto
    // {texto, accion, url} — las de pura NAVEGACIÓN (orden del jefe, 2026-09-13) no preguntan nada:
    // cierran el chat y abren la página, «como cerrar el chatbot y abrir la URL».
    var chipsTimer = null;   // ⏳ las opciones apiladas se esconden solas

    /** Cierra el chat y abre la página tal cual. */
    function irA(url) {
        cerrar();
        if (url) location.href = url;
    }

    /** 📍 «Ver tiendas cerca de mí»: cierra el chat, pide la ubicación y abre el buscador por distancia. */
    function irACerca(base) {
        var url = base || '/buscar.php';
        cerrar();
        if (!navigator.geolocation) { location.href = url; return; }
        navigator.geolocation.getCurrentPosition(function (pos) {
            location.href = url + '?lat=' + pos.coords.latitude.toFixed(7)
                          + '&lng=' + pos.coords.longitude.toFixed(7) + '&radio=auto';
        }, function () {
            location.href = url;   // sin ubicación igual lo llevamos al buscador (nunca lo dejamos sin salida)
        }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 60000 });
    }

    /**
     * 📍 PEDIR LA UBICACIÓN DENTRO DEL CHAT (orden del jefe, 2026-09-13): *«¿te muestro las más
     * cercanas a ti? Necesitaré que actives tu ubicación»*. El visitante toca la opción, el celular
     * pide el permiso y la MISMA búsqueda se repite con las coordenadas: los resultados salen
     * ordenados por distancia **sin salir del chat** (nada de mandarlo a otra página).
     * Si dice que no o no hay GPS, se le dice con naturalidad y se sigue en el chat.
     */
    function pedirUbicacion(termino) {
        var q = String(termino || '').trim();
        if (!q || ocupado || bloq) return;
        if (!navigator.geolocation) { pintar('bot', 'Tu celular no me deja ver la ubicación 🤔, pero puedo buscarlo por nombre si quieres.'); return; }

        pintar('user', '📍 Ver las más cercanas a mí', { anclar: true });
        pensando(true);
        navigator.geolocation.getCurrentPosition(function (pos) {
            pensando(false);
            enviarPregunta(q, { lat: pos.coords.latitude.toFixed(7), lng: pos.coords.longitude.toFixed(7), visible: '📍 Ver las más cercanas a mí' });
        }, function () {
            pensando(false);
            pintar('bot', 'No pude ver tu ubicación. Actívala en el celular y toca otra vez 📍, o dime la zona y lo busco así.');
        }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 60000 });
    }

    /**
     * 🧹 LIMPIAR EL CHAT (orden del jefe, 2026-09-13; completada el 2026-09-14): *«el botón limpiar
     * debe limpiar todo el chat, no dejar nada, ni siquiera los mismos botones»*. Por eso ahora la
     * ventana queda **COMPLETAMENTE VACÍA**: ni la conversación, ni el saludo, ni las opciones
     * apiladas. El visitante escribe su pregunta y el chat vuelve a la vida como si recién abriera
     * (el saludo del día y las opciones no se repintan solos: `vacio` los frena hasta que él escriba;
     * las opciones vuelven si toca el 💡).
     */
    function limpiarChat() {
        hist = [];
        try { localStorage.removeItem(CLAVE_LS); } catch (e) { /* da igual */ }
        cuerpo.innerHTML = '';
        anclaEl = null; anclaActiva = false; anclaObjetivo = -1;
        pensando(false);
        ocupado = false;
        bloq = false;                       // se vuelve a empezar: el servidor dirá si sigue bloqueado
        vacio = true;                       // 🧹 ventana limpia: nada de saludo ni de botones
        entrada.disabled = false;
        enviar.disabled = false;
        vozPintar();                        // 🎤 el micrófono vuelve (si el navegador sabe dictar)
        if (camBtn) camBtn.disabled = false;
        quitarAdjunto();
        cerrarMenu();
        if (entrada) entrada.value = '';
        form.classList.remove('cbot-pie--cerrado');
        if (cabecera) cabecera.classList.remove('cbot-head--cerrado');
        if (cierre) { cierre.hidden = true; if (cierreT) cierreT.textContent = ''; }
        if (estadoT) estadoT.textContent = estadoInicial;
        entrada.placeholder = 'Escribe tu pregunta…';
        fab.classList.remove('cbot-fab--cerrado');
        fab.title = '';
        // 🧹 VOLVER A EMPEZAR (orden del jefe, 2026-09-17: *«siempre pon por ahí algún botón para limpiar
        // el chat y volver a empezar»*): el chat queda como recién abierto — el saludo de la tienda y
        // sus TRES opciones otra vez—, listo para empezar de cero. (Antes se dejaba la ventana vacía
        // del todo, que era la orden del 2026-09-14; esa quedó reemplazada por esta.)
        vacio = false;
        if (CFG.saludo) {
            hist.push({ rol: 'bot', texto: CFG.saludo });
            pintar('bot', CFG.saludo);
        }
        pintarChips(CFG.sugerencias || []);
        mostrarChips(true);
        guardar();
        refrescar();                        // cuota del día (interna: no pinta ningún número)
    }

    /**
     * Pinta las opciones. 🔘 SOLO TRES COMO MUCHO (orden del jefe, 2026-09-17: *«máximo deben verse
     * visibles tres botones, exagerando, con texto pequeño, lo más compacto posible»*) y chiquitas.
     * La lista COMPLETA (`CFG.sugerencias_todas`) es la que sale cuando el visitante toca el 💡.
     */
    function pintarChips(lista, todas) {
        if (!chips) return;
        chips.innerHTML = '';
        var tope = todas ? 99 : (parseInt(CFG.max_chips, 10) || 3);
        (lista || []).slice(0, tope).forEach(function (it) {
            var esObj  = it && typeof it === 'object';
            var txt    = esObj ? String(it.texto || '') : String(it || '');
            if (!txt) return;
            var accion = esObj ? String(it.accion || 'pregunta') : 'pregunta';
            var url    = esObj ? String(it.url || '') : '';
            var b = document.createElement('button');
            b.type = 'button';
            b.className = 'cbot-chip' + (accion === 'limpiar' ? ' cbot-chip--util' : '');
            b.textContent = txt;
            b.addEventListener('click', function () {
                if (accion === 'cerca')      { irACerca(url); return; }   // abre «tiendas cerca de mí»
                if (accion === 'geo')        { pedirUbicacion(esObj ? (it.q || '') : ''); return; }  // 📍 ubicación dentro del chat
                if (accion === 'url' && url) { irA(url); return; }        // abre la página y ya
                if (accion === 'limpiar')    { limpiarChat(); return; }   // 🧹 borra todo y empieza de cero
                var limpio = txt.replace(/🔒/g, '').trim();
                enviarPregunta(limpio);
            });
            chips.appendChild(b);
        });
    }

    function mostrarChips(si) {
        if (!chips) return;
        chips.hidden = !si;
        if (si) {
            bajar();
            // ⏳ Las opciones apiladas se van solas a los 20 segundos (orden del jefe: «haz
            // desaparecer los botones»). Vuelven cuando toque el 💡.
            if (chipsTimer) clearTimeout(chipsTimer);
            chipsTimer = setTimeout(function () { if (!bloq) chips.hidden = true; }, 20000);
        } else if (chipsTimer) {
            clearTimeout(chipsTimer);
            chipsTimer = null;
        }
    }

    // ==================== 3.b) 👁️ MIRAR: foto, captura o compartir pantalla ====================
    // Pedido del jefe: «"mira mi pantalla" → ocultará el chat y tomará una foto». El visitante
    // manda una imagen y el bot la mira (modelo con visión de DeepSeek). La imagen se manda y se
    // tira: NO se guarda en el servidor ni se publica.
    var MAX_LADO = 1280;      // la imagen se encoge a este lado máximo (para que pese poco)
    var CALIDAD  = 0.72;

    // 📷 EL MENÚ DE LA CÁMARA: chiquito, pegado al botón y discreto (ver la nota de arriba).
    function menuAbierto() { return camMenu && !camMenu.hidden; }
    function abrirMenu()  { if (camMenu) camMenu.hidden = false; }
    function cerrarMenu() { if (camMenu) camMenu.hidden = true; }

    function comprimir(fuente, ancho, alto) {
        var escala = Math.min(1, MAX_LADO / Math.max(ancho || 1, alto || 1));
        var w = Math.max(1, Math.round((ancho || 1) * escala));
        var h = Math.max(1, Math.round((alto || 1) * escala));
        var c = document.createElement('canvas');
        c.width = w; c.height = h;
        var cx = c.getContext('2d');
        cx.fillStyle = '#fff'; cx.fillRect(0, 0, w, h);      // fondo blanco (si venía con transparencia)
        cx.drawImage(fuente, 0, 0, w, h);
        var url = c.toDataURL('image/jpeg', CALIDAD);
        if (url.length > 1500000) url = c.toDataURL('image/jpeg', 0.5);   // muy pesada: se aprieta más
        return url;
    }

    function ponerAdjunto(url) {
        if (!url) return;
        imgPendiente = url;
        if (adImg) adImg.src = url;
        if (adTxt) adTxt.textContent = 'Imagen lista (' + Math.round(url.length * 0.75 / 1024) + ' KB). Escribe qué quieres que mire o toca ➤';
        if (adjunto) adjunto.hidden = false;
        bajar();
    }

    function quitarAdjunto() {
        imgPendiente = '';
        if (adjunto) adjunto.hidden = true;
        if (adImg) adImg.removeAttribute('src');
        // Las DOS entradas de la cámara se vacían: si no, elegir la MISMA foto otra vez no dispara
        // `change` y parecería que el botón no funciona.
        if (archCam) archCam.value = '';
        if (archGal) archGal.value = '';
    }

    function fotoDeArchivo(f) {
        if (!f) return;
        if (f.size > 12 * 1024 * 1024) { pintar('bot', 'Esa foto es muy pesada 😅 (máximo 12 MB). Prueba con otra.'); return; }
        if (window.createImageBitmap) {
            createImageBitmap(f).then(function (bmp) {
                ponerAdjunto(comprimir(bmp, bmp.width, bmp.height));
                if (bmp.close) bmp.close();
            }).catch(function () { fotoConImg(f); });
        } else {
            fotoConImg(f);
        }
    }

    function fotoConImg(f) {
        var url = URL.createObjectURL(f);
        var im = new Image();
        im.onload = function () { ponerAdjunto(comprimir(im, im.naturalWidth, im.naturalHeight)); URL.revokeObjectURL(url); };
        im.onerror = function () { URL.revokeObjectURL(url); pintar('bot', 'No pude leer esa imagen 😅. Prueba con otra foto (JPG o PNG).'); };
        im.src = url;
    }

    /** Burbuja con los dos botones para mirar algo (se usa cuando el visitante pide que miremos). */
    function ofrecerMirar() {
        var html = '<div class="cbot-botones">' +
                   '<button type="button" class="cbot-btn-foto" data-cbot-foto="camara">📷 Tomar foto con la cámara</button>' +
                   '<button type="button" class="cbot-btn-foto" data-cbot-foto="galeria">🖼️ Subir de la galería</button>' +
                   '</div>';
        var d = document.createElement('div');
        d.className = 'cbot-msg cbot-msg--bot';
        d.innerHTML = formato('¡Claro que sí! 👁️ Mándame una foto y te digo qué veo:') + html;
        cuerpo.appendChild(d);
        bajar();
    }

    // 📷 El botón de la cámara abre su menú chiquito (y lo cierra si ya estaba abierto).
    if (camBtn) camBtn.addEventListener('click', function (ev) {
        ev.stopPropagation();                    // (que el clic no lo cierre el cierre-de-fuera de abajo)
        autoEnvioCancelar();                     // 🚀 si había un envío automático en camino, se cancela
        menuAbierto() ? cerrarMenu() : abrirMenu();
    });
    if (camFoto) camFoto.addEventListener('click', function () { cerrarMenu(); if (archCam) archCam.click(); });
    if (camGal)  camGal.addEventListener('click',  function () { cerrarMenu(); if (archGal) archGal.click(); });
    // Tocando cualquier otra parte del guía (o del sitio) el menú se cierra: es un menú, no una ventana.
    document.addEventListener('click', function (ev) {
        if (!menuAbierto()) return;
        var t = ev.target;
        if (camMenu && t && camMenu.contains && camMenu.contains(t)) return;   // dentro del menú: sigue
        if (camBtn && t && (t === camBtn || (camBtn.contains && camBtn.contains(t)))) return;
        cerrarMenu();
    });
    if (archCam) archCam.addEventListener('change', function () { fotoDeArchivo(archCam.files && archCam.files[0]); });
    if (archGal) archGal.addEventListener('change', function () { fotoDeArchivo(archGal.files && archGal.files[0]); });
    if (adQuitar) adQuitar.addEventListener('click', quitarAdjunto);

    // Los botones «mírame» que van dentro de las burbujas abren la misma cámara o galería.
    cuerpo.addEventListener('click', function (ev) {
        var b = ev.target.closest ? ev.target.closest('[data-cbot-foto]') : null;
        if (!b) return;
        ev.preventDefault();
        if (b.getAttribute('data-cbot-foto') === 'galeria') { if (archGal) archGal.click(); }
        else if (archCam) archCam.click();
    });

    // ¿El visitante está pidiendo que MIREMOS algo? («mira mi pantalla», «no me sale el botón»…)
    function pideMirar(texto) {
        var t = (texto || '').toLowerCase();
        var gatillos = ['mira mi pantalla', 'mira mi foto', 'mira esta foto', 'mira esta imagen', 'mira la pantalla',
                        'te mando una foto', 'te envio una foto', 'te mando una captura', 'captura de pantalla',
                        'no me sale', 'no me aparece', 'no encuentro', 'no funciona', 'no puedo', 'me sale error',
                        'mira esto', 'mira aqui', 'mira aquí', 'revisa mi'];
        for (var i = 0; i < gatillos.length; i++) if (t.indexOf(gatillos[i]) !== -1) return true;
        return false;
    }

    // ==================== 4) Enviar la pregunta ====================
    // `geo` (opcional): {lat, lng, visible} cuando el visitante autorizó su ubicación. Se manda la
    // MISMA pregunta que ya había hecho, pero con las coordenadas: el servidor la contesta ordenada
    // por distancia y en el chat se ve «📍 Ver las más cercanas a mí» como lo que pidió el visitante.
    function enviarPregunta(texto, geo) {
        texto = (texto || '').trim();
        var foto = imgPendiente;
        var geoLat = geo && geo.lat ? String(geo.lat) : '';
        var geoLng = geo && geo.lng ? String(geo.lng) : '';
        var visible = (geo && geo.visible) ? String(geo.visible) : texto;
        if ((texto === '' && !foto) || ocupado || bloq) return;
        vacio = false;   // 🧹 escribió después de limpiar: el chat vuelve a la vida (saludo, opciones…)

        hist.push({ rol: 'user', texto: visible, foto: foto || undefined });
        // 👤 La pregunta se pinta ANCLADA ARRIBA (ver `anclar()`): la respuesta sale debajo y el
        // visitante la lee bajando, sin tener que subir.
        pintar('user', visible, { foto: foto || '', anclar: true });
        guardar();
        mostrarChips(false);
        quitarAdjunto();
        cerrarMenu();

        entrada.value = '';
        ocupado = true;
        enviar.disabled = true;
        pensando(true);

        // Solo los últimos turnos: abarata la llamada y evita conversaciones eternas.
        var paraEnviar = hist.slice(-8).map(function (m) {
            return { rol: m.rol === 'user' ? 'user' : 'assistant', texto: m.texto };
        });

        var control = (typeof AbortController !== 'undefined') ? new AbortController() : null;
        var reloj = setTimeout(function () { if (control) control.abort(); }, 90000);   // con imagen tarda más

        /** Lo que se hace con la respuesta, ya cumplido el piso de espera del «pensando…». */
        function respuesta(d) {
            pensando(false);
            ocupado = false;
            enviar.disabled = false;

            if (!d || d.ok === false) {
                var aviso = (d && d.error) ? d.error
                    : 'No pude responder ahora mismo 🤖💤. Inténtalo otra vez o escríbele al administrador por WhatsApp.';
                hist.push({ rol: 'bot', texto: aviso });
                pintar('bot', aviso);
                guardar();
                return;
            }

            // 🔢 El servidor manda cómo va la cuota del día (aquí no se cuenta nada).
            if (d.cuota) aplicarCuota(d.cuota);

            // 🔢 Se le acabaron las preguntas: se pinta el mensaje de cierre y el chat se bloquea.
            if (d.bloqueado) {
                bloquear(d.respuesta, d.cta);
                guardar();
                return;
            }

            hist.push({ rol: 'bot', texto: d.respuesta, requiere_login: !!d.requiere_login });
            pintar('bot', d.respuesta, { requiere_login: !!d.requiere_login, cta: d.cta || '' });
            guardar();

            // Si pidió que MIREMOS algo y no mandó imagen, se le ofrecen los dos botones del 📷.
            if (!foto && pideMirar(texto)) ofrecerMirar();

            if (Array.isArray(d.sugerencias) && d.sugerencias.length) pintarChips(d.sugerencias);

            // Si cerró el chat mientras pensaba, se le avisa con un punto verde en el botón.
            if (!abierto && punto) punto.hidden = false;
        }

        fetch(apiUrl(), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({
                mensaje: texto,
                historial: paraEnviar.slice(0, -1),
                imagen: foto || '',                                 // 👁️ foto/captura (data URL)
                carrito: CFG.logueado ? leerCarrito() : null,       // ❤️ lista de «Me interesa»
                negocio: CFG.ficha || '',                           // 🧭 la tienda donde está el visitante
                lat: geoLat,                                        // 📍 ubicación autorizada (o '')
                lng: geoLng
            }),
            signal: control ? control.signal : undefined
        })
        .then(function (r) { return r.json().catch(function () { return null; }); })
        .then(function (d) {
            clearTimeout(reloj);
            // ⏳ EL PISO DE ESPERA (orden del jefe, 2026-09-14): si el servidor contestó volando, la
            // respuesta se pinta cuando el «Pensando → Consultando → Respondiendo» ya se vio. Ese
            // segundo o dos de verdad son los que le dan aire al buscador para cotejar la base y al
            // visitante la sensación de que el bot trabajó (en vez de soltar lo primero que encontró).
            var falta = faltaEspera();
            if (falta > 0) setTimeout(function () { respuesta(d); }, falta);
            else respuesta(d);
        })
        .catch(function () {
            clearTimeout(reloj);
            pensando(false);
            ocupado = false;
            enviar.disabled = false;
            var txt = 'Se me fue la conexión 🤖💤. Revisa tu internet e inténtalo otra vez, ' +
                      'o escríbele al administrador por WhatsApp.';
            hist.push({ rol: 'bot', texto: txt });
            pintar('bot', txt);
            guardar();
        });
    }

    // ══════════════════════════════════════════════════════════════════════════════════════════════
    // 🎵 LA MÚSICA DE LA TIENDA SE PARA CUANDO EL VISITANTE ABRE EL GUÍA (orden del jefe, 2026-09-17):
    // *«cuando el usuario da clic en el guía, automáticamente la música debe parar, porque como el
    // usuario va a grabar, la música estorba»*.
    //   · `window.DCH_MUSICA_PARADA` es la bandera que respeta el reproductor de la ficha
    //     (`includes/cancion_player.php`): mientras el chat esté abierto **no arranca** (ni sola ni con
    //     el primer gesto del visitante).
    //   · Y aquí se pausa el audio que ya esté sonando (por si arrancó antes de abrir el chat).
    //   · Al cerrar el chat la música **no vuelve sola**: el visitante le da al ▶ si quiere seguir
    //     oyéndola (así no lo sorprende).
    // ══════════════════════════════════════════════════════════════════════════════════════════════
    function musicaParar() {
        window.DCH_MUSICA_PARADA = true;
        var audios = document.querySelectorAll('audio, video');
        for (var i = 0; i < audios.length; i++) {
            try { if (!audios[i].paused) audios[i].pause(); } catch (e) { /* nada */ }
        }
    }
    function musicaSoltar() {
        window.DCH_MUSICA_PARADA = false;
    }

    // ==================== 5) Abrir y cerrar ====================
    /**
     * 🧭 LA BRÚJULA NO SE QUEDA QUIETA (orden del jefe, 2026-09-20): *«actualmente se muestra en cada
     * ficha como una especie de brújula que hace un efecto de cargar al inicio y se cierra; mantenga esa
     * brújula en cierto movimiento cada x segundos para que le llame la atención al usuario»*.
     *   · El efecto de entrada (abrirse y cerrarse solo) ya existía y se queda igual.
     *   · Lo nuevo: mientras el guía esté CERRADO, el círculo late (`--vivo`, cada 4,6 s), el halo sale
     *     en anillos (`--halo`, cada 2,8 s) y **la aguja de la brújula busca el norte cada 6,5 s**
     *     (`--brujula`). Los tres movimientos son CSS: aquí solo se encienden y se apagan.
     *   · Se apaga al abrirlo (ya llamó su atención) y **vuelve al cerrarlo**, así el botón nunca deja
     *     de invitar. Si el chat está cerrado por cuota, no se mueve nada (no hay nada que invitar).
     */
    function brujulaViva(si) {
        if (!fab) return;
        if (si && !bloq && panel.hidden) {
            fab.classList.add('cbot-fab--vivo', 'cbot-fab--brujula');
        } else {
            fab.classList.remove('cbot-fab--vivo', 'cbot-fab--brujula');
        }
    }

    function abrir() {
        musicaParar();                 // 🎵 primero la música: el visitante viene a hablar, no a oír
        brujulaViva(false);            // 🧭 ya lo abrió: la brújula deja de llamar
        panel.hidden = false;
        if (fondo) fondo.hidden = false;
        abierto = true;
        fab.setAttribute('aria-expanded', 'true');
        if (punto) punto.hidden = true;
        if (hist.length === 0 && CFG.saludo && !bloq && !vacio) {
            pintar('bot', CFG.saludo);
            mostrarChips(true);
        } else {
            mostrarChips(false);   // (si la ventana quedó limpia con 🧹, no se pinta nada)
        }
        bajar();

        // 🔢 Se pregunta al servidor cómo va la cuota de hoy (y si ya hay que cerrar el chat).
        // En la misma respuesta viene el saludo del día: fecha, hora y clima de Chimbote.
        refrescar();
        if (bloq) programaCierre();

        setTimeout(function () {
            // En pantalla grande se enfoca el cuadro; en celular el teclado aparece solo al tocar.
            if (window.innerWidth >= 640 && !bloq) entrada.focus();
        }, 120);
    }

    function cerrar() {
        autoEnvioCancelar();            // 🚀 al cerrar el guía no queda ningún envío automático armado
        if (escuchando) vozDetener();   // 🎤 si estaba dictando, se corta el micrófono al cerrar el guía
        cerrarMenu();                // 📷 si estaba abierta la ventana de la cámara, se cierra también
        musicaSoltar();              // 🎵 la bandera se suelta (la música no vuelve sola: hay ▶)
        panel.hidden = true;
        if (fondo) fondo.hidden = true;
        abierto = false;
        fab.setAttribute('aria-expanded', 'false');
        brujulaViva(true);           // 🧭 volvió a estar cerrado: la brújula sigue llamando la atención
    }

    /**
     * 🎬 EL EFECTO DE ENTRADA DEL BOTÓN (pedido del jefe, 2026-09-17): *«cuando cargue la página va a
     * hacer un efecto de que el botón popa o modal, como que se hubiese abierto y luego se hubiese
     * cerrado, y permanecería ahí en un círculo futurista con algún halo… con pequeños movimientos
     * persuasivos buscando que le den clic»*.
     * Se hace UNA vez por sesión del navegador (si no, cada ficha sería un sobresalto) y se respeta
     * «reducir movimiento». No pinta nada dentro: no gasta preguntas ni llama a la API.
     */
    function efectoPop() {
        // 🧭 Pase lo que pase, la brújula se queda moviéndose: si el efecto de entrada no se hace
        // (ya se vio en esta sesión, o el visitante pidió «reducir movimiento»), el botón igual invita.
        if (!CFG.pop) { brujulaViva(true); return; }
        var yaVisto = false;
        try { yaVisto = sessionStorage.getItem(CLAVE_POP) === '1'; } catch (e) { yaVisto = false; }
        if (yaVisto) { brujulaViva(true); return; }

        var reducir = false;
        try { reducir = !!(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches); } catch (e2) {}
        try { sessionStorage.setItem(CLAVE_POP, '1'); } catch (e3) {}
        if (reducir) { brujulaViva(true); return; }

        var ms = Math.max(700, parseInt(CFG.pop_ms, 10) || 1400);
        popEnCurso = true;
        panel.style.animationDuration = ms + 'ms';
        panel.classList.add('cbot-panel--pop');
        panel.hidden = false;
        if (fondo) {
            fondo.style.animationDuration = ms + 'ms';
            fondo.classList.add('cbot-fondo--pop');
            fondo.hidden = false;
        }
        popTimer = setTimeout(function () {
            popTimer = null;
            popEnCurso = false;
            panel.classList.remove('cbot-panel--pop');
            panel.style.animationDuration = '';
            if (!abierto) panel.hidden = true;
            if (fondo) {
                fondo.classList.remove('cbot-fondo--pop');
                fondo.style.animationDuration = '';
                if (!abierto) fondo.hidden = true;
            }
            if (!abierto) fab.classList.add('cbot-fab--vivo');   // 🎯 ya queda invitando al clic
            brujulaViva(true);                                   // 🧭 y la aguja sigue buscando el norte
        }, ms);
    }

    /** Si el visitante toca el botón mientras el chat se está presentando, se corta y se abre de verdad. */
    function cortarPop() {
        if (!popEnCurso) return;
        if (popTimer) { clearTimeout(popTimer); popTimer = null; }
        popEnCurso = false;
        panel.classList.remove('cbot-panel--pop');
        panel.style.animationDuration = '';
        if (fondo) {
            fondo.classList.remove('cbot-fondo--pop');
            fondo.style.animationDuration = '';
        }
    }

    if (fondo) fondo.addEventListener('click', cerrar);

    fab.addEventListener('click', function () {
        cortarPop();                              // 🎬 se corta la presentación si estaba en curso
        brujulaViva(false);                       // 🧭 ya lo abrió: no hay nada que pedirle
        abierto ? cerrar() : abrir();
    });
    if (bCerrar) bCerrar.addEventListener('click', cerrar);
    document.addEventListener('keydown', function (ev) {
        if (ev.key !== 'Escape') return;
        // 📷 Si el menú de la cámara está abierto, `Escape` cierra ESE menú (no todo el guía): es lo que
        // espera cualquiera, y así no se pierde la conversación por cerrar un menú.
        if (menuAbierto()) { cerrarMenu(); return; }
        if (abierto) cerrar();
    });

    // 💡 «Ver más preguntas»: saca la lista COMPLETA (las tres de siempre y las demás), chiquitas.
    if (bIdeas) {
        bIdeas.addEventListener('click', function () {
            var listaTodas = (CFG.sugerencias_todas && CFG.sugerencias_todas.length) ? CFG.sugerencias_todas : (CFG.sugerencias || []);
            pintarChips(listaTodas, true);
            mostrarChips(true);
        });
    }

    // 🧹 LIMPIAR Y VOLVER A EMPEZAR (orden del jefe, 2026-09-17: *«siempre pon por ahí algún botón
    // para limpiar el chat y volver a empezar»*): un botón fijo en la cabecera, a mano siempre.
    var bLimpiar = document.getElementById('cbotLimpiar');
    if (bLimpiar) {
        bLimpiar.addEventListener('click', function () {
            if (entrada) entrada.value = '';
            limpiarChat();
        });
    }

    // 🗑️ La papelera también se quitó (orden del jefe): el chat recuerda la conversación 12 h y
    // se puede empezar de cero recargando la página. No hay botón de borrar.

    form.addEventListener('submit', function (ev) {
        ev.preventDefault();
        autoEnvioCancelar();      // 🚀 lo mandó él con el ➤: que no se mande otra vez solo
        enviarPregunta(entrada.value);
    });

    // ══════════════════════════════════════════════════════════════════════════════════════════════
    // 🎤 EL MICRÓFONO: COPIA LITERAL DEL MOTOR DEL BUSCADOR (orden del jefe, 2026-09-20)
    // ──────────────────────────────────────────────────────────────────────────────────────────────────
    // Textual del jefe: *«quiero que copies la tecnología que ya existe en el buscador para convertir
    // voz a texto y esa misma tecnología la pongas al costado del icono de cámara que aparece en… el
    // guía; eso estoy pidiendo: que lo copies tal cual, no estoy pidiendo que lo crees, lo modifiques ni
    // nada, que lo copies tal cual. Y cuando presione ese botón va a convertir mi voz en texto, y este
    // texto… lo va a pegar en el input que dice "escribe tu pregunta", que curiosamente es el input que
    // está al costadito de la cámara»*.
    //
    // POR QUÉ ESTE OTRO SÍ FUNCIONA (lo que hace que el del buscador «nunca se equivoque»): el motor es
    // el MISMO y se copia tal cual de `assets/js/buscador_voz.js`:
    //   · un reconocedor NUEVO en cada dictado (reutilizar la instancia deja estados pegados en Chrome),
    //   · el candado `instancia === actual` en cada manejador, para que el reconocedor viejo no pise al
    //     nuevo,
    //   · `es-PE` primero, con caída automática a `es-ES` y `es-MX` si el navegador no tiene el del Perú,
    //   · `interimResults`: lo que se oye se va ESCRIBIENDO en el campo mientras habla (feedback),
    //   · segundo toque = parar, y los mismos avisos (micrófono bloqueado, sin micrófono, sin conexión,
    //     no te escuché…), con la misma caja blanca del buscador.
    // ⚠️ LO ÚNICO QUE **NO** SE COPIA ES LA LIMPIEZA DEL BUSCADOR (`limpiarDictado()`), y es a propósito:
    // esa limpieza borra mandos Y CONECTORES porque el buscador busca por palabras. En una pregunta
    // hablada destrozaría la frase (medido con el motor común del sitio): «¿Cuánto cuesta la membresía
    // mensual?» quedaría en «membresía mensual», y «Hola, ¿venden leche?» en «leche». Aquí el texto se
    // escribe **tal como se dijo** (es lo mismo que hace el dictado del panel del dueño,
    // `assets/js/dictado_voz.js`). ⚠️ Tampoco hay búsqueda automática: el texto se queda en el cuadro y
    // el visitante lo manda con el ➤ (en el buscador sí busca solo a los 900 ms: eso es de buscar).
    // ⚠️ El buscador NO se toca: esto es una copia. `buscador_voz.js` y `components.css` quedan igual.
    // ══════════════════════════════════════════════════════════════════════════════════════════════
    var Rec = window.SpeechRecognition || window.webkitSpeechRecognition;
    var IDIOMAS_VOZ   = ['es-PE', 'es-ES', 'es-MX'];   // Perú primero; si no lo tiene, cae al siguiente
    var ESPERA_FINAL  = 260;                            // ms para dejar llegar el último trozo del dictado
    var AVISO_VOZ_MS  = 7000;                           // lo que dura el aviso (igual que en el buscador)
    var AUTO_ENVIO_MS = 1000;                           // 🚀 el segundo de gracia antes de mandar solo
    var PISTA_VOZ     = 'Escribe tu pregunta…';         // el texto de fondo del cuadro
    var MENSAJES_VOZ  = {                               // los MISMOS textos del buscador
        'not-allowed': '🎙️ El micrófono está bloqueado. Toca el candado 🔒 de la barra de direcciones, permite el micrófono y vuelve a intentarlo.',
        'service-not-allowed': '🎙️ El navegador no dejó usar el micrófono. Revisa los permisos del sitio.',
        'audio-capture': '🎙️ No se encontró un micrófono en este dispositivo.',
        'network': '🎙️ Sin conexión para reconocer la voz. Revisa tus datos o el wifi.',
        'no-speech': '🎙️ No te escuché. Toca el micrófono y habla más cerca.',
        'bad-grammar': '🎙️ No entendí bien. Prueba diciendo el rubro y el distrito, por ejemplo: «pollería Nuevo Chimbote».'
    };

    var vozBtn   = document.getElementById('cbotVoz');
    var vozAviso = document.getElementById('cbotVozAviso');
    var escuchando = false, huboErrorVoz = false;
    var dicho = '', interino = '', idiomaVoz = 0, vozActual = null, timerFinalVoz = null, timerAvisoVoz = null;
    // ✂️ El MISMO candado del buscador (`yaBuscado`), aquí llamado `yaDictado`: una vez que el texto ya
    // quedó escrito en el cuadro, no se vuelve a escribir. Sin esto, el temporizador de 260 ms podía
    // devolver la pregunta al cuadro justo después de que el visitante la mandara con el ➤.
    var yaDictado = false;
    // 🚀 EL ENVÍO AUTOMÁTICO (orden del jefe, 2026-09-20): *«después de grabar el mensaje con el
    // micrófono, y el micrófono se desactiva porque escucha silencio, debería dar un segundo y enviarse
    // el mensaje; de esa manera acostumbramos al usuario a dar respuestas cortas y también le ahorramos
    // dar clic en Enviar»*. Se manda **solo** cuando el motor se apaga **por el silencio** (nadie tocó
    // el botón): da **1 segundo de gracia** y, si en ese segundo él escribe, toca el micrófono otra vez,
    // abre la cámara o le da al ➤, el envío automático se cancela. ⚠️ El **botón ➤ se queda** donde está
    // (el jefe lo dijo: *«no estoy diciendo que borres el botón de enviar, déjalo, está muy bien ahí»*).
    var timerAutoEnvio = null;
    var vozParoManual  = false;        // true cuando el que apagó el micrófono fue su dedo

    /** Primera letra en mayúscula (igual que en el buscador). */
    function vozBonito(txt) {
        var t = String(txt || '').trim();
        return t ? t.charAt(0).toUpperCase() + t.slice(1) : t;
    }

    /** Dispara un `input` de verdad para que lo vean los demás scripts de la página. */
    function dispararInput(input) {
        var ev;
        try { ev = new Event('input', { bubbles: true }); }
        catch (e) { ev = document.createEvent('Event'); ev.initEvent('input', true, true); }
        input.dispatchEvent(ev);
    }

    function vozOcultarAviso() {
        clearTimeout(timerAvisoVoz);
        if (vozAviso) vozAviso.className = 'cbot-voz-aviso';
    }

    /** 🔔 La misma caja blanca del buscador, con sus mismos textos. */
    function vozAvisar(texto, tipo) {
        if (!vozAviso) return;
        vozAviso.textContent = texto;
        vozAviso.className = 'cbot-voz-aviso cbot-voz-aviso--' + (tipo || 'info') + ' cbot-voz-aviso--visible';
        clearTimeout(timerAvisoVoz);
        timerAvisoVoz = setTimeout(vozOcultarAviso, AVISO_VOZ_MS);
    }

    /** Suelta el botón (deja de escuchar) y devuelve el campo a su estado normal. */
    function vozSoltar() {
        escuchando = false;
        if (vozBtn) {
            vozBtn.classList.remove('is-escuchando');
            vozBtn.setAttribute('aria-pressed', 'false');
            vozBtn.setAttribute('title', 'Hablar: tu voz se escribe en el cuadro');
            vozBtn.setAttribute('aria-label', 'Hablar: tu voz se escribe en el cuadro');
        }
        if (entrada && !bloq) entrada.placeholder = PISTA_VOZ;
        if (entrada) entrada.classList.remove('voz-escuchando');
    }

    /** 🗣️ Escribe en el cuadro lo que se entendió (SIN limpiar: la pregunta va entera). */
    function vozTerminar() {
        if (yaDictado) return true;      // ya se escribió (o ya se mandó): no se repite ni se reescribe
        var bruto = (dicho || interino || (entrada ? entrada.value : '') || '').replace(/\s+/g, ' ').trim();
        if (!bruto) return false;
        yaDictado = true;
        if (entrada) {
            entrada.value = vozBonito(bruto).slice(0, 700);
            dispararInput(entrada);      // el cuadro queda como si lo hubiera escrito él
        }
        return true;
    }

    /** 🚀 Deja armado el envío automático (1 s). Cualquier gesto del visitante lo cancela. */
    function autoEnviarVoz() {
        autoEnvioCancelar();
        timerAutoEnvio = setTimeout(function () {
            timerAutoEnvio = null;
            if (bloq || ocupado) return;                       // chat cerrado o ya hay una pregunta en el aire
            var txt = (entrada && entrada.value.trim()) || '';
            if (txt) enviarPregunta(txt);
        }, AUTO_ENVIO_MS);
    }

    /** Cancela el envío automático (escribió, tocó el micrófono, abrió la cámara, o le dio al ➤). */
    function autoEnvioCancelar() {
        if (timerAutoEnvio) { clearTimeout(timerAutoEnvio); timerAutoEnvio = null; }
    }

    function vozEmpezar() {
        // Un reconocedor NUEVO por dictado (evita estados pegados de Chrome). Copiado del buscador.
        var instancia;
        try { instancia = new Rec(); }
        catch (e) {
            vozAvisar('🎙️ No se pudo iniciar el micrófono en este navegador.', 'error');
            return;
        }

        instancia.lang = IDIOMAS_VOZ[idiomaVoz] || 'es-PE';
        instancia.continuous = false;
        instancia.interimResults = true;
        instancia.maxAlternatives = 1;

        dicho = ''; interino = ''; huboErrorVoz = false; yaDictado = false;
        autoEnvioCancelar();             // 🚀 si quedaba un envío automático del dictado anterior, se cae
        if (entrada) entrada.value = '';
        vozOcultarAviso();
        musicaParar();                   // 🎵 con la música de la tienda sonando el micrófono se confunde

        instancia.onstart = function () {
            if (instancia !== vozActual) return;
            escuchando = true;
            if (vozBtn) {
                vozBtn.classList.add('is-escuchando');
                vozBtn.setAttribute('aria-pressed', 'true');
                vozBtn.setAttribute('title', 'Escuchando… toca para terminar');
                vozBtn.setAttribute('aria-label', 'Escuchando… toca para terminar');
            }
            if (entrada) {
                entrada.setAttribute('placeholder', '🎙️ Escuchando… habla ahora');
                entrada.classList.add('voz-escuchando');
            }
        };

        instancia.onresult = function (ev) {
            if (instancia !== vozActual) return;
            var fin = '', inter = '';
            for (var i = ev.resultIndex; i < ev.results.length; i++) {
                var r = ev.results[i];
                var trozo = (r[0] && r[0].transcript) ? r[0].transcript : '';
                if (r.isFinal) fin += trozo; else inter += trozo;
            }
            if (fin) dicho += fin;
            interino = inter;

            // 🗣️ Se va escribiendo lo que se oye (feedback inmediato, móvil-primero): es el cuadro de
            // al lado del micrófono. Aquí NO se limpia nada: la pregunta se escribe como se dijo.
            if (entrada) entrada.value = vozBonito((dicho + ' ' + inter).replace(/\s+/g, ' ').trim()).slice(0, 700);

            if (fin) {
                clearTimeout(timerFinalVoz);
                timerFinalVoz = setTimeout(function () {
                    if (instancia === vozActual) vozTerminar();
                }, ESPERA_FINAL);
            }
        };

        instancia.onerror = function (ev) {
            if (instancia !== vozActual) return;
            var err = ev && ev.error ? ev.error : 'desconocido';

            // El navegador puede no tener el español del Perú: se prueba el siguiente (sin molestar).
            if (err === 'language-not-supported' && idiomaVoz < IDIOMAS_VOZ.length - 1) {
                idiomaVoz++;
                setTimeout(vozEmpezar, 80);
                return;
            }

            huboErrorVoz = true;
            if (err === 'aborted') return;   // lo abortó el propio visitante: silencio
            vozAvisar(MENSAJES_VOZ[err] || ('🎙️ No se pudo usar el micrófono (' + err + '). Prueba escribiendo.'), 'error');
        };

        instancia.onend = function () {
            if (instancia !== vozActual) return;
            var estaba = escuchando;
            var porSilencio = estaba && !vozParoManual;   // 🚀 se apagó solo: nadie tocó el botón
            vozParoManual = false;
            vozSoltar();
            if (huboErrorVoz) return;
            // Dejó de hablar: el texto se queda escrito en el cuadro…
            if (vozTerminar()) {
                // 🚀 …y si se apagó porque escuchó silencio, se manda solo dentro de 1 segundo.
                if (porSilencio) autoEnviarVoz();
            } else if (estaba) {
                vozAvisar('🎙️ No te escuché. Toca el micrófono y habla más cerca.', 'info');
            }
        };

        vozActual = instancia;
        try { instancia.start(); }
        catch (e) {
            vozSoltar();
            vozAvisar('🎙️ El micrófono ya estaba activo. Espera un segundo y vuelve a tocar.', 'error');
        }
    }

    function vozDetener() {
        if (!vozActual) return;
        try { vozActual.stop(); } catch (e) { /* ya estaba parado */ }
    }

    // 🎤 El botón: sin soporte del navegador NO se pinta (nunca un botón muerto), igual que en el buscador.
    function vozPintar() {
        if (vozBtn) vozBtn.hidden = !(Rec && !bloq);
    }

    if (vozBtn && Rec) {
        vozBtn.addEventListener('click', function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            autoEnvioCancelar();                        // 🚀 si había un envío automático, se cancela
            if (escuchando) { vozParoManual = true; vozDetener(); return; }   // su dedo: se para y revisa
            vozEmpezar();
        });
    }
    // 🚀 Escribir cancela el envío automático (si no, mandaría lo que él está corrigiendo sin avisar).
    // Se escucha `keydown`, que solo llega cuando teclea una persona (no cuando el dictado escribe).
    if (entrada) entrada.addEventListener('keydown', autoEnvioCancelar);
    vozPintar();

    // 🗑️ La papelera también se quitó (orden del jefe): el chat recuerda la conversación 12 h y
    // ahora hay 🧹 en la cabecera para limpiar y volver a empezar.

    // ==================== 7) Arrancar ====================
    cargar();
    pintarChips(CFG.sugerencias || []);

    // 📷 El botón de la cámara y su ventana modal son las DOS opciones de siempre (tomar foto · galería):
    // no hay nada que esconder según el navegador. ⛔ Aquí vivía la línea que mostraba «Compartir mi
    // pantalla» cuando el navegador lo permitía: esa tercera opción se retiró el 2026-09-20 (el jefe
    // pidió un modal con DOS botones y nada más). El guía tampoco tiene voz: ver la nota ARCHIVADO.

    // Para que otras partes del sitio puedan ABRIR el chat (por ejemplo la tarjeta de
    // «El ninja» del panel): así se abre siempre, sin el efecto interruptor del botón.
    window.NINJA_ABRIR = function () { if (!abierto) abrir(); };

    // Si ya había conversación, se vuelve a pintar tal cual (mensajes del bot con su texto).
    if (hist.length) {
        hist.forEach(function (m) {
            var txt = m.texto || (m.conFoto ? '(imagen enviada)' : '');
            pintar(m.rol === 'user' ? 'user' : 'bot', txt, { requiere_login: !!m.requiere_login, conFoto: !!m.conFoto });
        });
        // Sin burbujas repetidas de saludo: la charla continúa donde quedó.
    }
    // 🔢 La cuota de preguntas la manda el servidor; si ya se agotó, el chat nace cerrado.
    aplicarCuota(cuota);
    if (CFG.bloqueado) bloquear(CFG.respuesta_bloqueo, CFG.cta);

    // 🎬 Y el efecto de entrada del botón (el chat se abre y se cierra solo, una vez por sesión).
    if (!bloq && !CFG.bloqueado) efectoPop();
})();
