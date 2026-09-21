/* ============================================================
 * buscador_voz.js — BUSCAR POR VOZ (Web Speech API nativa, gratis)
 * Proyecto: DeChimbote.com
 * ------------------------------------------------------------
 * Qué hace:
 *   1. Pinta un micrófono 🎙️ al lado de cada buscador (los campos
 *      `#buscador-fuzzy` / `[data-fuzzy]`, los mismos del buscador fuzzy).
 *   2. Al tocarlo, escucha en español (es-PE) y va ESCRIBIENDO lo que oye
 *      en el propio campo: el usuario ve el texto antes de que se busque.
 *   3. Al terminar de hablar: limpia el dictado ("Pollería en Nuevo Chimbote"
 *      → "Pollería Nuevo Chimbote"), el buscador fuzzy muestra sus
 *      coincidencias y, si el usuario no toca nada, se busca solo
 *      (misma URL que el botón 🔍, conservando los filtros del formulario).
 *
 * Costo: $0. No hay servidor ni API pagada: usa la Web Speech API del
 * navegador (Chrome de Android y de escritorio). Si el navegador no la
 * soporta (Firefox, Safari), el micrófono NO se pinta: nunca un botón muerto.
 *
 * ⚠️ Requiere HTTPS (el sitio ya lo tiene) y permiso de micrófono.
 * ⚠️ Se apoya en el buscador fuzzy: buscador_fuzzy.js debe ir ANTES en el HTML.
 * ============================================================ */
(function () {
    'use strict';

    var Rec = (typeof window !== 'undefined')
        ? (window.SpeechRecognition || window.webkitSpeechRecognition)
        : null;

    var IDIOMAS = ['es-PE', 'es-ES', 'es-MX'];   // Perú primero; si el navegador no lo tiene, cae al siguiente
    var PAUSA_ANTES_DE_BUSCAR = 900;             // ms que se ve el texto reconocido antes de buscar solo
    var ESPERA_FINAL = 260;                      // ms para dejar llegar el último trozo del dictado
    var AVISO_MS = 7000;                         // lo que dura el aviso flotante

    // ---------------------------------------------------------------- diccionario
    // ⚠️ 2026-09-14 — EL DICCIONARIO YA NO VIVE AQUÍ (mando del jefe: «el buscador debe saber filtrar
    // palabras que simplemente acompañan una búsqueda»). Ahora es UNO SOLO para todo el sitio: el que
    // manda PHP (`includes/busqueda_limpieza.php` → `busqueda_diccionario_js()`, pintado en el pie como
    // `window.CHIMBOTE_LIMPIEZA`) y lo aplica `assets/js/buscador_limpieza.js`. Así lo que se escribe a
    // mano y lo que se dicta se limpian IGUAL, con la misma lista.
    // Las listas de abajo quedan solo como RESPALDO por si ese archivo no llegara a cargar.
    var MANDOS = [
        'buscar', 'busca', 'buscame', 'busco', 'buscando',
        'quiero', 'quisiera', 'necesito', 'dame', 'muestrame', 'muestra',
        'dime', 'ver', 'ensename', 'encuentra', 'encuentrame',
        'hay', 'donde hay', 'donde puedo encontrar', 'donde queda', 'donde estan',
        'como llegar a', 'informacion de'
    ];

    // Conectores y muletillas: se quitan porque el buscador exige que TODAS las
    // palabras coincidan (plan A). "polleria en nuevo chimbote" con el "en"
    // dentro obligaría a que cada resultado tuviera la palabra "en".
    var CONECTORES = [
        'en', 'de', 'del', 'la', 'el', 'los', 'las', 'un', 'una', 'unos', 'unas',
        'al', 'a', 'y', 'e', 'o', 'u', 'con', 'sin', 'para', 'por', 'mi', 'mis',
        'tu', 'tus', 'su', 'sus', 'que', 'es', 'son', 'esta', 'este', 'estos',
        'estas', 'hay', 'cerca', 'cerquita', 'favor', 'gracias', 'porfa', 'pues',
        'oe', 'oee', 'ahi', 'alli', 'aca', 'aqui'
    ];

    var MULETILLAS_FINALES = ['favor', 'gracias', 'porfa', 'pues', 'ya', 'oe', 'oee'];

    /** Minúsculas y sin tildes: para comparar palabras, no para mostrar. */
    function sinTildes(txt) {
        return String(txt || '').toLowerCase()
            .replace(/[áàäâã]/g, 'a').replace(/[éèëê]/g, 'e').replace(/[íìïî]/g, 'i')
            .replace(/[óòöô]/g, 'o').replace(/[úùüû]/g, 'u').replace(/ñ/g, 'n');
    }

    /**
     * Limpia lo que dictó el usuario antes de buscar.
     * "Oye, quiero buscar pollería en Nuevo Chimbote por favor"
     *   → "pollería Nuevo Chimbote"
     *
     * 🆕 2026-09-14: la limpieza la hace el motor común (`buscador_limpieza.js`, el mismo que usa el
     * buscador de escribir y el servidor): así el dictado y lo tecleado se limpian IGUAL. Si ese
     * archivo no estuviera cargado, se usa el respaldo de abajo (el comportamiento de siempre).
     * Nunca deja la búsqueda vacía: el motor común ya trae esa red de seguridad.
     */
    function limpiarDictado(txt) {
        var L = (typeof window !== 'undefined') ? window.ChimboteLimpieza : null;
        if (L && typeof L.limpiar === 'function') {
            try {
                var limpioComun = L.limpiar(txt);
                if (limpioComun) return limpioComun;
            } catch (e) { /* se sigue con el respaldo */ }
        }
        return limpiarDictadoRespaldo(txt);
    }

    /** Respaldo (lo que hacía este archivo antes del 2026-09-14), por si no cargó el motor común. */
    function limpiarDictadoRespaldo(txt) {
        var original = String(txt == null ? '' : txt).replace(/\s+/g, ' ').trim();
        if (!original) return '';

        // Signos de interrogación, comas y puntos que mete el reconocedor.
        var base = original.replace(/[¿?¡!.,;:"'()«»\-–—]/g, ' ').replace(/\s+/g, ' ').trim();
        var toks = base ? base.split(' ') : [];

        // 1) Mandos al principio ("quiero buscar…", "dónde hay…"): hasta 4 seguidos.
        var i = 0, vez, k, j, cambio;
        for (vez = 0; vez < 4; vez++) {
            cambio = false;
            for (k = 0; k < MANDOS.length; k++) {
                var m = MANDOS[k].split(' ');
                if (i + m.length > toks.length) continue;
                var igual = true;
                for (j = 0; j < m.length; j++) {
                    if (sinTildes(toks[i + j]) !== m[j]) { igual = false; break; }
                }
                if (igual) { i += m.length; cambio = true; break; }
            }
            if (!cambio) break;
        }

        // 2) Muletillas del final ("… por favor", "… gracias").
        var fin = toks.length;
        while (fin - 1 >= i && MULETILLAS_FINALES.indexOf(sinTildes(toks[fin - 1])) !== -1) fin--;

        // 3) Conectores fuera (dejando las palabras que de verdad buscan algo).
        var salida = [];
        for (; i < fin; i++) {
            if (CONECTORES.indexOf(sinTildes(toks[i])) !== -1) continue;
            salida.push(toks[i]);
        }
        var limpio = salida.join(' ').replace(/\s+/g, ' ').trim();

        // Si al limpiar no quedó nada útil, se busca lo que dijo (mejor eso que nada).
        if (limpio.replace(/[^0-9a-zA-ZáéíóúÁÉÍÓÚñÑ]/g, '').length < 3) {
            return base || original;
        }
        return limpio;
    }

    /** Primera letra en mayúscula (solo para que se vea bien en el campo). */
    function bonito(txt) {
        var t = String(txt || '').trim();
        return t ? t.charAt(0).toUpperCase() + t.slice(1) : t;
    }

    // Datos a la vista para pruebas y para otros módulos.
    if (typeof window !== 'undefined') {
        window.ChimboteVoz = {
            soportado: !!Rec,
            idiomas: IDIOMAS,
            limpiarDictado: limpiarDictado,
            bonito: bonito,
            sinTildes: sinTildes
        };
    }

    if (typeof window === 'undefined' || typeof document === 'undefined') return;

    // Sin soporte del navegador no se pinta nada: el buscador de escribir sigue igual.
    if (!Rec) return;

    // ------------------------------------------------------------- utilidades
    /** Dispara un `input` de verdad para que el buscador fuzzy reaccione. */
    function dispararInput(input) {
        var ev;
        try {
            ev = new Event('input', { bubbles: true });
        } catch (e) {
            ev = document.createEvent('Event');
            ev.initEvent('input', true, true);
        }
        input.dispatchEvent(ev);
    }

    /** URL de búsqueda conservando los filtros del formulario (rubro, distrito, radio). */
    function urlDeBusqueda(form, q) {
        var base = (window.SITE_URL || '') + '/buscar.php';
        if (form && form.getAttribute('action')) base = form.getAttribute('action');

        var cadena = '';
        try {
            var fd = new FormData(form);
            fd.set('q', q);
            var partes = [];
            fd.forEach(function (v, k) {
                partes.push(encodeURIComponent(k) + '=' + encodeURIComponent(v == null ? '' : v));
            });
            cadena = partes.join('&');
        } catch (e) {
            cadena = 'q=' + encodeURIComponent(q);
        }
        if (!cadena) return base;
        return base + (base.indexOf('?') === -1 ? '?' : '&') + cadena;
    }

    // ----------------------------------------------------------- un micrófono
    function prepararVoz(input) {
        var wrap = input.closest ? input.closest('.pred-wrap') : null;
        if (!wrap) {
            wrap = document.createElement('div');
            wrap.className = 'pred-wrap';
            input.parentNode.insertBefore(wrap, input);
            wrap.appendChild(input);
        }
        if (wrap.querySelector('.btn-voz')) return;   // ya tiene micrófono

        wrap.classList.add('pred-wrap--voz');

        // ---------------------------------------------------------- el botón
        var btn = document.createElement('button');
        btn.type = 'button';                     // NUNCA submit: no debe enviar solo
        btn.className = 'btn-voz';
        btn.setAttribute('aria-label', 'Buscar por voz');
        btn.setAttribute('title', 'Buscar por voz: toca y habla');
        btn.setAttribute('aria-pressed', 'false');
        // El dibujo del micrófono: el MISMO que usan Android, Google y Facebook
        // (micrófono relleno, reconocible al instante). Pedido del jefe 2026-09-10:
        // antes era el emoji 🎙️, que cada aparato pinta a su manera y en Windows
        // salía gris apagado sobre el círculo naranja: no invitaba a tocar.
        btn.innerHTML = '<span class="btn-voz__icono" aria-hidden="true">'
            + '<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true" focusable="false">'
            + '<path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>'
            + '<path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>'
            + '</svg></span>';
        wrap.appendChild(btn);

        // ------------------------------------------------------- el aviso
        var elAviso = null, timerAviso = null;

        function ocultarAviso() {
            clearTimeout(timerAviso);
            if (elAviso) elAviso.classList.remove('voz-aviso--visible');
            wrap.classList.remove('voz-con-aviso');
        }

        function avisar(texto, tipo) {
            if (!elAviso) {
                elAviso = document.createElement('div');
                elAviso.className = 'voz-aviso';
                elAviso.setAttribute('role', 'status');
                wrap.appendChild(elAviso);
            }
            elAviso.textContent = texto;
            elAviso.className = 'voz-aviso voz-aviso--' + (tipo || 'info') + ' voz-aviso--visible';
            wrap.classList.add('voz-con-aviso');   // mientras haya aviso, el desplegable no estorba
            clearTimeout(timerAviso);
            timerAviso = setTimeout(ocultarAviso, AVISO_MS);
        }

        // --------------------------------------------------- estado interno
        var escuchando = false, yaBuscado = false, huboError = false;
        var dicho = '', interino = '';
        var idioma = 0;
        var actual = null;            // reconocedor en uso (los anteriores se ignoran solos)
        var timerFinal = null, timerAuto = null;
        var placeholderOriginal = input.getAttribute('placeholder') || '¿Qué buscas hoy?';

        function cancelarAuto() {
            clearTimeout(timerAuto);
            document.removeEventListener('click', cancelarAuto, true);
            document.removeEventListener('keydown', cancelarAuto, true);
        }

        function buscarAhora() {
            cancelarAuto();
            var q = input.value.trim();
            if (!q) return;
            window.location.href = urlDeBusqueda(input.form, q);
        }

        function soltar() {
            escuchando = false;
            btn.classList.remove('is-escuchando');
            btn.setAttribute('aria-pressed', 'false');
            btn.setAttribute('aria-label', 'Buscar por voz');
            btn.setAttribute('title', 'Buscar por voz: toca y habla');
            input.setAttribute('placeholder', placeholderOriginal);
            input.classList.remove('voz-escuchando');
        }

        /** Escribe lo reconocido, avisa al buscador fuzzy y busca solo. */
        function terminar() {
            if (yaBuscado) return true;
            var bruto = (dicho || interino || input.value || '').trim();
            if (!bruto) return false;

            var limpio = bonito(limpiarDictado(bruto));
            input.value = limpio;
            dispararInput(input);          // el desplegable fuzzy se llena al instante

            yaBuscado = true;
            clearTimeout(timerAuto);
            // Si el usuario no toca nada, se busca solo (igual que con el botón 🔍).
            timerAuto = setTimeout(buscarAhora, PAUSA_ANTES_DE_BUSCAR);
            document.addEventListener('click', cancelarAuto, true);
            document.addEventListener('keydown', cancelarAuto, true);
            return true;
        }

        function empezar() {
            // Un reconocedor nuevo por dictado: no arrastra resultados viejos y
            // evita el estado pegado que a veces deja Chrome al reutilizarlo.
            var instancia;
            try {
                instancia = new Rec();
            } catch (e) {
                avisar('🎙️ No se pudo iniciar el micrófono en este navegador.', 'error');
                return;
            }

            instancia.lang = IDIOMAS[idioma] || 'es-PE';
            instancia.continuous = false;
            instancia.interimResults = true;
            instancia.maxAlternatives = 1;

            dicho = ''; interino = ''; yaBuscado = false; huboError = false;
            input.value = '';
            ocultarAviso();

            instancia.onstart = function () {
                if (instancia !== actual) return;
                escuchando = true;
                btn.classList.add('is-escuchando');
                btn.setAttribute('aria-pressed', 'true');
                btn.setAttribute('aria-label', 'Detener el micrófono');
                btn.setAttribute('title', 'Escuchando… toca para terminar');
                input.setAttribute('placeholder', '🎙️ Escuchando… habla ahora');
                input.classList.add('voz-escuchando');
            };

            instancia.onresult = function (ev) {
                if (instancia !== actual) return;
                var fin = '', inter = '';
                for (var i = ev.resultIndex; i < ev.results.length; i++) {
                    var r = ev.results[i];
                    var trozo = (r[0] && r[0].transcript) ? r[0].transcript : '';
                    if (r.isFinal) fin += trozo; else inter += trozo;
                }
                if (fin) dicho += fin;
                interino = inter;

                // Se va escribiendo lo que se oye (feedback inmediato, móvil-primero).
                input.value = bonito((dicho + ' ' + inter).replace(/\s+/g, ' ').trim());

                if (fin) {
                    clearTimeout(timerFinal);
                    timerFinal = setTimeout(function () {
                        if (instancia === actual) terminar();
                    }, ESPERA_FINAL);
                }
            };

            instancia.onerror = function (ev) {
                if (instancia !== actual) return;
                var err = ev && ev.error ? ev.error : 'desconocido';

                // El navegador puede no tener el español del Perú: se prueba el siguiente.
                if (err === 'language-not-supported' && idioma < IDIOMAS.length - 1) {
                    idioma++;
                    setTimeout(empezar, 80);   // se reintenta solo, sin molestar al usuario
                    return;
                }

                huboError = true;
                if (err === 'aborted') return;   // lo abortó el propio usuario: silencio

                var mensajes = {
                    'not-allowed': '🎙️ El micrófono está bloqueado. Toca el candado 🔒 de la barra de direcciones, permite el micrófono y vuelve a intentarlo.',
                    'service-not-allowed': '🎙️ El navegador no dejó usar el micrófono. Revisa los permisos del sitio.',
                    'audio-capture': '🎙️ No se encontró un micrófono en este dispositivo.',
                    'network': '🎙️ Sin conexión para reconocer la voz. Revisa tus datos o el wifi.',
                    'no-speech': '🎙️ No te escuché. Toca el micrófono y habla más cerca.',
                    'bad-grammar': '🎙️ No entendí bien. Prueba diciendo el rubro y el distrito, por ejemplo: «pollería Nuevo Chimbote».'
                };
                avisar(mensajes[err] || ('🎙️ No se pudo usar el micrófono (' + err + '). Prueba escribiendo.'), 'error');
            };

            instancia.onend = function () {
                if (instancia !== actual) return;
                var estaba = escuchando;
                soltar();
                if (yaBuscado || huboError) return;
                // El usuario dejó de hablar: se busca con lo que se haya entendido.
                if (!terminar() && estaba) {
                    avisar('🎙️ No te escuché. Toca el micrófono y habla más cerca.', 'info');
                }
            };

            actual = instancia;
            try {
                instancia.start();
            } catch (e) {
                soltar();
                avisar('🎙️ El micrófono ya estaba activo. Espera un segundo y vuelve a tocar.', 'error');
            }
        }

        function detener() {
            if (!actual) return;
            try { actual.stop(); } catch (e) { /* ya estaba parado */ }
        }

        btn.addEventListener('click', function (ev) {
            ev.preventDefault();
            ev.stopPropagation();
            cancelarAuto();          // si iba a buscar solo, el usuario cambió de idea
            if (escuchando) { detener(); return; }
            empezar();
        });

        // Si el usuario escribe o elige una sugerencia, se cancela la búsqueda automática.
        input.addEventListener('input', cancelarAuto);
    }

    // ------------------------------------------------------------- arranque
    function init() {
        var inputs = document.querySelectorAll('#buscador-fuzzy, [data-fuzzy]');
        if (!inputs.length) return;
        Array.prototype.forEach.call(inputs, prepararVoz);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
