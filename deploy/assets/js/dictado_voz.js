/* ============================================================
 * dictado_voz.js — DICTAR EN CUALQUIER CAMPO (Web Speech API nativa, $0)
 * Proyecto: DeChimbote.com
 * ------------------------------------------------------------
 * QUÉ HACE
 *   Pinta una píldora 🎙️ "Dictar" debajo de cada campo marcado con
 *   `data-dictado`. Al tocarla, el usuario HABLA y el texto se va
 *   escribiendo en el campo (se ve mientras habla), y al terminar se
 *   limpia y se deja listo para guardar. Nada de teclear en el celular.
 *
 * DIFERENCIA CLAVE CON EL BUSCADOR POR VOZ (leer antes de tocar)
 *   `buscador_voz.js` limpia el dictado quitando CONECTORES ("en", "de",
 *   "la"…) porque el buscador fuzzy exige que TODAS las palabras
 *   coincidan. Aquí NO: una descripción de producto es una frase
 *   ("Ideal para motos de trabajo, resistente al agua") y quitarle las
 *   palabras la dejaría sin sentido. Este módulo solo quita MANDOS del
 *   principio ("descripción", "escribe", "ponle"…), muletillas del final
 *   ("por favor", "gracias") y ordena los espacios y la puntuación.
 *
 * CÓMO SE USA (declarativo, sin escribir JS en la página)
 *   <textarea data-dictado="descripcion"></textarea>
 *   <input    data-dictado="titulo">
 *   Modos: "titulo" (nombre corto) · "descripcion" (frase larga) · "texto" (neutro).
 *
 *   - El dictado se AÑADE a lo que ya había escrito (se puede dictar en
 *     dos tandas). Con `data-dictado-reemplazar="1"` reemplaza todo.
 *   - Si el navegador no soporta la API (Firefox, Safari), la píldora NO
 *     se pinta: nunca un botón muerto. El campo se escribe como siempre.
 *
 * ⚠️ Requiere HTTPS (el sitio ya lo tiene) y permiso de micrófono.
 * ============================================================ */
(function () {
    'use strict';

    var Rec = (typeof window !== 'undefined')
        ? (window.SpeechRecognition || window.webkitSpeechRecognition)
        : null;

    var IDIOMAS = ['es-PE', 'es-ES', 'es-MX'];   // Perú primero; si el navegador no lo tiene, cae al siguiente
    var ESPERA_FINAL = 300;                      // ms para dejar llegar el último trozo del dictado
    var AVISO_MS = 7000;                         // lo que dura el aviso

    // ------------------------------------------------------------- diccionario
    /** Minúsculas y sin tildes: para comparar palabras, no para mostrar. */
    function sinTildes(txt) {
        return String(txt || '').toLowerCase()
            .replace(/[áàäâã]/g, 'a').replace(/[éèëê]/g, 'e').replace(/[íìïî]/g, 'i')
            .replace(/[óòöô]/g, 'o').replace(/[úùüû]/g, 'u').replace(/ñ/g, 'n');
    }

    // Mandos: lo que la gente dice ANTES del dato. Se quitan del principio.
    // ⚠️ TRAMPA YA PISADA: aquí NO van "producto", "servicio", "texto" ni "nota"
    // sueltos. Son palabras que empiezan nombres REALES de negocios de Chimbote
    // ("Servicio de instalación…", "Productos de limpieza", "Textos escolares"):
    // al quitarlas, el nombre quedaba mutilado ("De instalación…"). Las frases
    // completas ("el producto es…", "nombre del producto…") sí se quitan.
    var MANDOS = [
        'nombre del producto', 'nombre del negocio', 'nombre', 'titulo',
        'descripcion del producto', 'descripcion', 'describe',
        'detalles del producto', 'detalles', 'detalle',
        'escribe', 'ponle', 'pon', 'agregale', 'anota',
        'se llama', 'el producto es', 'la descripcion es', 'el titulo es'
    ];

    // Muletillas del final: se quitan (no aportan nada al dato). "por" entra
    // porque casi siempre es el resto del "por favor" ya recortado.
    var MULETILLAS_FINALES = ['favor', 'por', 'gracias', 'porfa', 'porfavor', 'pues', 'ya', 'oe', 'oee'];

    function soloEspacios(txt) {
        return String(txt == null ? '' : txt).replace(/\s+/g, ' ').trim();
    }

    /** Quita los mandos del principio (hasta 4 seguidos). */
    function quitarMandos(toks) {
        var i = 0, vez, k, j, cambio;
        for (vez = 0; vez < 4; vez++) {
            cambio = false;
            for (k = 0; k < MANDOS.length; k++) {
                var m = MANDOS[k].split(' ');
                if (i + m.length > toks.length) continue;
                var igual = true;
                for (j = 0; j < m.length; j++) {
                    if (sinTildes(toks[i + j]) !== sinTildes(m[j])) { igual = false; break; }
                }
                if (igual) { i += m.length; cambio = true; break; }
            }
            if (!cambio) break;
        }
        return i;
    }

    /** Quita las muletillas del final. */
    function quitarMuletillas(toks) {
        var fin = toks.length;
        while (fin - 1 >= 0 && MULETILLAS_FINALES.indexOf(sinTildes(toks[fin - 1])) !== -1) fin--;
        return fin;
    }

    /** Primera letra en mayúscula. */
    function bonito(txt) {
        var t = String(txt || '').trim();
        return t ? t.charAt(0).toUpperCase() + t.slice(1) : t;
    }

    /**
     * Nombre corto de producto/servicio: "el producto es aceite 20W50 por favor"
     *   → "Aceite 20W50".  NUNCA devuelve vacío: si la limpieza no deja nada,
     * devuelve el dictado tal cual llegó (mejor eso que un campo en blanco).
     */
    function limpiarTitulo(txt) {
        var original = soloEspacios(txt);
        if (!original) return '';
        var base = original.replace(/[¿?¡!.,;:"'()«»]/g, ' ').replace(/\s+/g, ' ').trim();
        var toks = base ? base.split(' ') : [];
        var i = quitarMandos(toks);
        var fin = quitarMuletillas(toks);
        var limpio = (fin > i ? toks.slice(i, fin).join(' ') : '').replace(/\s+/g, ' ').trim();
        if (limpio.replace(/[^0-9a-zA-ZáéíóúÁÉÍÓÚñÑ]/g, '').length < 2) return bonito(base || original);
        return bonito(limpio);
    }

    /**
     * Descripción (frase): se conservan TODAS las palabras (los conectores
     * también, al revés que en el buscador). Solo se limpian mandos del
     * principio, muletillas del final y la puntuación.
     */
    function limpiarTexto(txt) {
        var original = soloEspacios(txt);
        if (!original) return '';
        var s = original.replace(/\s+([,.;:!?])/g, '$1');       // " ," → ","
        s = s.replace(/([,.;:!?])(?=[^\s\d])/g, '$1 ');            // "frío,resistente" → "frío, resistente"
        var toks = s.split(' ');
        var i = quitarMandos(toks);
        var fin = quitarMuletillas(toks);
        var limpio = (fin > i ? toks.slice(i, fin).join(' ') : '').replace(/\s+/g, ' ').trim();
        limpio = limpio.replace(/\s+([,.;:!?])/g, '$1');
        if (limpio.replace(/[^0-9a-zA-ZáéíóúÁÉÍÓÚñÑ]/g, '').length < 2) limpio = s;   // red de seguridad
        limpio = bonito(limpio);
        // "…punto" al final es la forma oral de cerrar la frase.
        limpio = limpio.replace(/\s+punto$/i, '.');
        // Frase larga sin cierre: se le pone el punto final (queda presentable).
        if (limpio.length > 24 && !/[.!?]$/.test(limpio)) limpio += '.';
        return limpio;
    }

    /** Limpia según el modo declarado en el campo. */
    function limpiar(txt, modo) {
        if (modo === 'titulo') return limpiarTitulo(txt);
        if (modo === 'descripcion') return limpiarTexto(txt);
        // "texto" (neutro): como la descripción, pero sin exigir punto final.
        return limpiarTexto(txt).replace(/\.$/, '');
    }

    // Datos a la vista para pruebas y para otros módulos.
    if (typeof window !== 'undefined') {
        window.ChimboteDictado = {
            soportado: !!Rec,
            idiomas: IDIOMAS,
            limpiar: limpiar,
            limpiarTitulo: limpiarTitulo,
            limpiarTexto: limpiarTexto,
            bonito: bonito,
            sinTildes: sinTildes
        };
    }

    if (typeof window === 'undefined' || typeof document === 'undefined') return;

    // Sin soporte del navegador no se pinta nada: el campo se escribe igual.
    if (!Rec) return;

    // ------------------------------------------------------------- utilidades
    /** Dispara un `input` de verdad: así lo ven los demás scripts de la página. */
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

    // ------------------------------------------------------- una píldora 🎙️
    function prepararCampo(input) {
        if (!input || input.dataset.czDict === '1') return;
        input.dataset.czDict = '1';

        var modo = (input.getAttribute('data-dictado') || 'texto').toLowerCase();
        var reemplazar = input.getAttribute('data-dictado-reemplazar') === '1';
        var etiquetas = {
            titulo: ['🎙️ Dictar nombre', '🎙️ Dictar'],
            descripcion: ['🎙️ Dictar descripción', '🎙️ Dictar'],
            texto: ['🎙️ Dictar', '🎙️ Dictar']
        };
        var textos = etiquetas[modo] || etiquetas.texto;
        var etiqueta = textos[0];
        var etiquetaEscucha = '🔴 Escuchando…';
        // 🎙️ MODO SOLO ÍCONO (pedido del jefe, 2026-09-15): *«el botón de dictar está repetido dos
        // veces… no es necesario que diga "dictar"; trata de poner ahí un ícono que represente eso,
        // así como lo hace WhatsApp»*. Con `data-dictado-icono="1"` el botón queda con el micrófono
        // (el mismo que la gente tiene asociado a grabar) y sin texto; el nombre va en `aria-label`
        // y en el `title` para quien use lector de pantalla. Los demás campos del sitio NO cambian.
        var soloIcono = input.getAttribute('data-dictado-icono') === '1';
        var iconoMic = '<svg viewBox="0 0 24 24" width="22" height="22" fill="currentColor" aria-hidden="true">'
                     + '<path d="M12 15a3.2 3.2 0 0 0 3.2-3.2V6.2a3.2 3.2 0 1 0-6.4 0v5.6A3.2 3.2 0 0 0 12 15z"/>'
                     + '<path d="M18.6 11.6a6.6 6.6 0 0 1-13.2 0H3.6a8.4 8.4 0 0 0 7.4 8.3V22h2v-2.1a8.4 8.4 0 0 0 7.4-8.3h-1.8z"/></svg>';

        /** Pinta la etiqueta del botón respetando el modo solo ícono. */
        function rotular(texto) {
            if (soloIcono) { btn.innerHTML = (texto === etiquetaEscucha ? '🔴' : iconoMic); return; }
            btn.textContent = texto;
        }

        // Píldora + hueco de avisos: van DESPUÉS del campo (no se envuelve el
        // campo para no cambiar la estructura del formulario).
        var caja = document.createElement('div');
        caja.className = 'cz-dict';

        var btn = document.createElement('button');
        btn.type = 'button';                     // NUNCA submit: no debe enviar el formulario
        btn.className = 'cz-dict__btn' + (soloIcono ? ' cz-dict__btn--icono' : '');
        btn.setAttribute('aria-label', etiqueta);
        btn.setAttribute('title', soloIcono ? etiqueta : 'Dicta y el texto se escribe solo');
        btn.setAttribute('aria-pressed', 'false');
        rotular(etiqueta);
        caja.appendChild(btn);

        var elAviso = document.createElement('div');
        elAviso.className = 'cz-dict__aviso';
        elAviso.setAttribute('role', 'status');
        caja.appendChild(elAviso);

        if (input.parentNode) input.parentNode.insertBefore(caja, input.nextSibling);

        var timerAviso = null;
        function avisar(texto, tipo) {
            elAviso.textContent = texto;
            elAviso.className = 'cz-dict__aviso cz-dict__aviso--' +
                (tipo || 'info') + ' cz-dict__aviso--visible';
            clearTimeout(timerAviso);
            timerAviso = setTimeout(function () {
                elAviso.className = 'cz-dict__aviso';
            }, AVISO_MS);
        }

        // --------------------------------------------------- estado interno
        var escuchando = false, huboError = false;
        var dicho = '', interino = '', base = '';
        var idioma = 0, actual = null, timerFinal = null;

        function soltar() {
            escuchando = false;
            btn.classList.remove('is-escuchando');
            btn.setAttribute('aria-pressed', 'false');
            btn.setAttribute('aria-label', etiqueta);
            rotular(etiqueta);
            input.classList.remove('cz-dict-escuchando');
        }

        /** Escribe en el campo lo que ya se reconoció (limpio) y avisa. */
        function terminar() {
            var bruto = soloEspacios(dicho || interino || '');
            if (!bruto) return false;
            var limpio = limpiar(bruto, modo);
            input.value = (base && !reemplazar) ? (base.replace(/\s+$/, '') + ' ' + limpio) : limpio;
            dispararInput(input);
            try { input.focus(); } catch (e) { /* da igual */ }
            avisar('✅ Listo. Revisa lo que escribí y corrige lo que quieras.', 'ok');
            return true;
        }

        function empezar() {
            // Un reconocedor nuevo por dictado (evita estados pegados de Chrome).
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

            dicho = ''; interino = ''; huboError = false;
            base = reemplazar ? '' : String(input.value || '').replace(/\s+$/, '');
            elAviso.className = 'cz-dict__aviso';

            instancia.onstart = function () {
                if (instancia !== actual) return;
                escuchando = true;
                btn.classList.add('is-escuchando');
                btn.setAttribute('aria-pressed', 'true');
                btn.setAttribute('aria-label', 'Detener el micrófono');
                rotular(etiquetaEscucha);
                input.classList.add('cz-dict-escuchando');
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

                // Se va escribiendo lo que se oye: el usuario ve el texto al instante.
                var vivo = soloEspacios(dicho + ' ' + inter);
                input.value = (base && !reemplazar) ? (base + ' ' + vivo) : vivo;

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
                    setTimeout(empezar, 80);
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
                    'bad-grammar': '🎙️ No entendí bien. Prueba diciéndolo más despacio y en frases cortas.'
                };
                avisar(mensajes[err] || ('🎙️ No se pudo usar el micrófono (' + err + '). Puedes escribirlo a mano.'), 'error');
            };

            instancia.onend = function () {
                if (instancia !== actual) return;
                var estaba = escuchando;
                soltar();
                if (huboError) return;
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
            if (escuchando) { detener(); return; }
            empezar();
        });
    }

    // ------------------------------------------------------------- arranque
    function init(raiz) {
        var cont = raiz || document;
        var campos = cont.querySelectorAll('[data-dictado]');
        Array.prototype.forEach.call(campos, prepararCampo);
    }

    // Otros módulos (o campos creados después) pueden pedir el micrófono.
    window.ChimboteDictado.engancharTodos = init;
    window.ChimboteDictado.campos = function () {
        return document.querySelectorAll('[data-dictado]').length;
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(); });
    } else {
        init();
    }
})();
