/* ============================================================
 * captura_rapida.js — CAPTURA RÁPIDA CON LA CÁMARA DEL CELULAR
 * Proyecto: DeChimbote.com   (experiencia de Caminante, ahora en el panel)
 * ------------------------------------------------------------
 * QUÉ HACE
 *   Convierte un hueco de la página en una REJILLA DE FOTOS como la de
 *   Caminante: casillas cuadradas que abren la cámara del celular al
 *   tocarlas y muestran la VISTA PREVIA INSTANTÁNEA de la foto (sin
 *   esperar a guardar nada). Cada foto se puede volver a tomar (tocando
 *   la casilla) o quitar con ✕, y el contador anima al dueño a seguir
 *   ("🏗️ tu producto ya tiene 3 fotos").
 *
 *   Las fotos se comprimen EN EL CELULAR antes de entrar al flujo con el
 *   motor compartido `imagen_optimizar.js` (WebP, máx 1600 px): en la
 *   calle, con datos móviles, eso es la diferencia entre 4 MB y 200 KB.
 *
 * CÓMO SE USA (declarativo, sin escribir JS en la página)
 *   <div class="cz-cap"
 *        data-cz-captura
 *        data-cz-max="6"
 *        data-cz-destino="#miInputFotos"
 *        data-cz-contador="#miContador"
 *        data-cz-texto="🏗️ Tu producto ya tiene {n} fotos · la 1.ª es la portada"
 *        data-cz-texto-cero="📷 Toca una casilla y toma la primera foto"
 *        data-cz-etiqueta="Foto"></div>
 *   <input type="file" name="fotos[]" multiple accept="image/*"
 *          id="miInputFotos" data-cz-optim="1" hidden>
 *
 *   ⚠️ `data-cz-optim="1"` en el input es OBLIGATORIO: le dice a
 *   `imagen_optimizar.js` que NO se enganche a ese input (este módulo ya
 *   optimizó los archivos y los ordenó; si lo enganchara, los
 *   reemplazaría y se perdería el orden de la portada).
 *
 *   ⚠️ El input destino lleva las fotos por `DataTransfer`. Si el
 *   navegador no lo soporta, se avisa al usuario (el resto del panel
 *   sigue funcionando igual).
 *
 * API para pruebas y para otros scripts (se comporta como el flujo real):
 *   CZCaptura.agregar(contenedor, [File…])  → Promise<n>  (comprime y agrega)
 *   CZCaptura.insertar(contenedor, [File…]) → n           (ya optimizados)
 *   CZCaptura.cantidad(c) · .archivos(c) · .limpiar(c)
 *   Evento: 'cz-captura-cambio' (detail = {n: fotos listas})
 * ============================================================ */
(function () {
    'use strict';

    var AVISO_MS = 4000;

    function porSelector(sel, raiz) {
        if (!sel) return null;
        try { return (raiz || document).querySelector(sel); } catch (e) { return null; }
    }

    function soloEnteros(v, porDefecto) {
        var n = parseInt(v, 10);
        return isNaN(n) || n < 1 ? porDefecto : n;
    }

    /** Crea el input de cámara/galería y lo dispara (patrón probado en Caminante). */
    function abrirSelector(opciones, alElegir) {
        var inp = document.createElement('input');
        inp.type = 'file';
        inp.accept = 'image/*';
        if (opciones.multiple) {
            inp.multiple = true;
            inp.removeAttribute('capture');   // galería → varias fotos a la vez
        } else {
            inp.capture = 'environment';      // cámara trasera → 1 foto
        }
        inp.style.position = 'fixed';
        inp.style.left = '-9999px';
        inp.setAttribute('tabindex', '-1');
        inp.setAttribute('aria-hidden', 'true');
        document.body.appendChild(inp);

        var usado = false;
        inp.onchange = function () {
            usado = true;
            var files = inp.files ? Array.prototype.slice.call(inp.files) : [];
            if (inp.parentNode) inp.parentNode.removeChild(inp);
            if (files.length) alElegir(files);
        };

        // Si el usuario cancela, en algunos navegadores no llega `change`:
        // al volver el foco a la ventana se retira el input que quedó colgado.
        var limpiarAlVolver = function () {
            setTimeout(function () {
                if (!usado && inp.parentNode) inp.parentNode.removeChild(inp);
                window.removeEventListener('focus', limpiarAlVolver);
            }, 1200);
        };
        window.addEventListener('focus', limpiarAlVolver);

        setTimeout(function () {   // red de seguridad final
            if (!usado && inp.parentNode) inp.parentNode.removeChild(inp);
        }, 120000);

        inp.click();
    }

    /** Comprime los archivos en el celular antes de aceptarlos. */
    function optimizar(files, cont) {
        if (!window.CZImg || !CZImg.optimizarLista) return Promise.resolve(files);
        marcarAviso(cont, files.length === 1 ? '📷 Optimizando la foto…' : ('📷 Optimizando ' + files.length + ' fotos…'), 'info');
        return CZImg.optimizarLista(files).then(function (lista) {
            return (lista && lista.length) ? lista : files;
        }).catch(function () { return files; });   // nunca se pierde una foto
    }

    function marcarAviso(cont, texto, tipo) {
        var el = cont.__aviso;
        if (!el) return;
        el.textContent = texto || '';
        el.className = 'cz-cap__aviso' + (texto ? ' cz-cap__aviso--visible' : '') +
            (tipo ? ' cz-cap__aviso--' + tipo : '');
        clearTimeout(cont.__avisoT);
        if (texto) {
            cont.__avisoT = setTimeout(function () {
                el.className = 'cz-cap__aviso';
            }, AVISO_MS);
        }
    }

    function iniciar(cont) {
        if (!cont || cont.__czCap) return;
        cont.__czCap = true;

        var max = soloEnteros(cont.getAttribute('data-cz-max'), 6);
        var etiqueta = cont.getAttribute('data-cz-etiqueta') || 'Foto';
        var conGaleria = cont.getAttribute('data-cz-multiple') !== '0';
        var destino = porSelector(cont.getAttribute('data-cz-destino'));
        var contador = porSelector(cont.getAttribute('data-cz-contador'));
        // Botón que solo tiene sentido con foto (ej. "Guardar foto"): se
        // deshabilita hasta que haya al menos una.
        var btnReq = porSelector(cont.getAttribute('data-cz-requerido'));
        var txtCon = cont.getAttribute('data-cz-texto') || '🏗️ Ya tienes {n} foto(s)';
        var txtCero = cont.getAttribute('data-cz-texto-cero') || '📷 Toca una casilla y toma la primera foto';

        var fotos = [];      // File (en orden: la primera es la portada)
        var urls = [];       // objectURL de cada vista previa
        var ocupado = false;  // evita dos selectores abiertos a la vez

        // ---------------------------------------------------------- estructura
        var grid = document.createElement('div');
        grid.className = 'cz-cap__grid';

        var acciones = document.createElement('div');
        acciones.className = 'cz-cap__acciones';

        var btnCam = document.createElement('button');
        btnCam.type = 'button';
        btnCam.className = 'cz-cap__btn cz-cap__btn--cam';
        btnCam.textContent = '📷 Tomar foto';

        var btnGal = document.createElement('button');
        btnGal.type = 'button';
        btnGal.className = 'cz-cap__btn cz-cap__btn--gal';
        btnGal.textContent = '🖼️ Elegir de mi galería';

        acciones.appendChild(btnCam);
        if (conGaleria) acciones.appendChild(btnGal);

        var aviso = document.createElement('div');
        aviso.className = 'cz-cap__aviso';
        aviso.setAttribute('role', 'status');

        // Los botones de la página (los que ya existían) se ocultan: la rejilla
        // los reemplaza. Se conservan por si el JS no corre.
        Array.prototype.forEach.call(cont.querySelectorAll('[data-cz-boton-viejo]'), function (b) {
            b.hidden = true;
        });

        cont.appendChild(grid);
        cont.appendChild(acciones);
        cont.appendChild(aviso);
        cont.__aviso = aviso;

        // ---------------------------------------------------------- pintado
        function revocar(idx) {
            if (urls[idx]) { try { URL.revokeObjectURL(urls[idx]); } catch (e) {} urls[idx] = null; }
        }

        function pintar() {
            grid.innerHTML = '';
            var total = Math.max(max, fotos.length);
            for (var i = 0; i < total; i++) {
                if (fotos[i]) {
                    var celda = document.createElement('div');
                    celda.className = 'cz-cap__celda cz-cap__celda--llena';
                    if (i === 0) celda.classList.add('cz-cap__celda--portada');

                    var img = document.createElement('img');
                    img.className = 'cz-cap__img';
                    img.alt = 'Vista previa de la foto ' + (i + 1);
                    if (!urls[i]) urls[i] = URL.createObjectURL(fotos[i]);
                    img.src = urls[i];
                    celda.appendChild(img);

                    var num = document.createElement('span');
                    num.className = 'cz-cap__num';
                    num.textContent = i === 0 ? '1 · portada' : String(i + 1);
                    celda.appendChild(num);

                    // Tocar la foto = volver a tomarla (mismo hueco).
                    celda.title = 'Toca para volver a tomar esta foto';
                    celda.addEventListener('click', function (idx) {
                        return function () { tomar(idx); };
                    }(i));

                    var equis = document.createElement('button');
                    equis.type = 'button';
                    equis.className = 'cz-cap__equis';
                    equis.title = 'Quitar esta foto';
                    equis.setAttribute('aria-label', 'Quitar la foto ' + (i + 1));
                    equis.textContent = '✕';
                    equis.addEventListener('click', function (idx) {
                        return function (ev) {
                            ev.stopPropagation();
                            revocar(idx);
                            fotos.splice(idx, 1);
                            pintar();
                            sincronizar();
                        };
                    }(i));
                    celda.appendChild(equis);

                    grid.appendChild(celda);
                } else {
                    var vacia = document.createElement('button');
                    vacia.type = 'button';
                    vacia.className = 'cz-cap__celda cz-cap__celda--vacia';
                    vacia.setAttribute('aria-label', 'Tomar la ' + etiqueta.toLowerCase() + ' ' + (i + 1) + ' con la cámara');
                    vacia.innerHTML = '<span class="cz-cap__ico" aria-hidden="true">📷</span>' +
                        '<span class="cz-cap__etiq"></span>';
                    vacia.querySelector('.cz-cap__etiq').textContent = etiqueta + ' ' + (i + 1);
                    vacia.addEventListener('click', function (idx) {
                        return function () { tomar(idx); };
                    }(i));
                    grid.appendChild(vacia);
                }
            }

            if (fotos.length >= max) {
                btnCam.hidden = true;
                if (btnGal) btnGal.hidden = true;
            } else {
                btnCam.hidden = false;
                if (btnGal) btnGal.hidden = false;
                btnCam.textContent = fotos.length === 0 ? '📷 Tomar foto' : '📷 Tomar otra foto';
            }

            if (contador) {
                contador.textContent = fotos.length === 0
                    ? txtCero
                    : txtCon.replace('{n}', String(fotos.length)).replace('{max}', String(max));
            }

            if (btnReq) {
                btnReq.disabled = fotos.length === 0;
                if (fotos.length === 0) {
                    btnReq.title = 'Primero toma o elige una foto';
                } else {
                    btnReq.removeAttribute('title');
                }
            }
        }

        /** Deja las fotos dentro del input real del formulario, en orden. */
        function sincronizar() {
            if (!destino) return true;
            var ok = true;
            try {
                if (window.DataTransfer) {
                    var dt = new DataTransfer();
                    fotos.forEach(function (f) { dt.items.add(f); });
                    destino.files = dt.files;
                } else {
                    ok = false;
                }
            } catch (e) {
                ok = false;
            }
            if (!ok) {
                marcarAviso(cont, '⚠️ Este navegador no deja adjuntar las fotos así. Actualízalo o usa el celular.', 'error');
            }
            try {
                cont.dispatchEvent(new CustomEvent('cz-captura-cambio', { bubbles: true, detail: { n: fotos.length } }));
            } catch (e) { /* navegador viejo: da igual */ }
            return ok;
        }

        // ---------------------------------------------------------- acciones
        function tomar(idx) {
            if (ocupado) return;
            ocupado = true;
            abrirSelector({ multiple: false }, function (files) {
                ocupado = false;
                optimizar(files, cont).then(function (lista) {
                    var f = lista[0];
                    if (!f) return;
                    revocar(idx);
                    fotos[idx] = f;
                    pintar();
                    sincronizar();
                    marcarAviso(cont, '📸 Foto lista. Se ve abajo: revísala antes de guardar.', 'ok');
                });
            });
            setTimeout(function () { ocupado = false; }, 3000);   // si el usuario cancela
        }

        function elegirVarias() {
            if (ocupado) return;
            var libres = max - fotos.length;
            if (libres <= 0) { marcarAviso(cont, 'Ya tienes el máximo de ' + max + ' fotos.', 'info'); return; }
            ocupado = true;
            abrirSelector({ multiple: true }, function (files) {
                ocupado = false;
                optimizar(files, cont).then(function (lista) {
                    var nuevos = lista.slice(0, libres);
                    insertar(nuevos);
                    marcarAviso(cont,
                        '📸 +' + nuevos.length + (nuevos.length === 1 ? ' foto' : ' fotos') +
                        (lista.length > nuevos.length ? ' (el máximo es ' + max + ')' : ' y ya se ven abajo.'),
                        'ok');
                });
            });
            setTimeout(function () { ocupado = false; }, 3000);
        }

        btnCam.addEventListener('click', function (ev) {
            ev.preventDefault();
            var libre = -1;
            for (var i = 0; i < max; i++) { if (!fotos[i]) { libre = i; break; } }
            if (libre === -1) { marcarAviso(cont, 'Ya tienes el máximo de ' + max + ' fotos. Quita una con ✕ si quieres cambiarla.', 'info'); return; }
            tomar(libre);
        });
        if (btnGal) btnGal.addEventListener('click', function (ev) { ev.preventDefault(); elegirVarias(); });

        // ------------------------------------------- API (pruebas/otros scripts)
        /** Inserta fotos YA optimizadas (sincrónico, respeta el máximo). */
        function insertar(nuevos) {
            var libres = max - fotos.length, usados = 0;
            (nuevos || []).forEach(function (f) {
                if (!f || usados >= libres) return;
                fotos.push(f); usados++;
            });
            pintar();
            sincronizar();
            marcarAviso(cont, usados
                ? ('📸 +' + usados + (usados === 1 ? ' foto' : ' fotos') + ' y ya se ven abajo.')
                : 'No se agregó ninguna foto (el máximo ya está completo).',
                usados ? 'ok' : 'info');
            return usados;
        }

        cont.__czApi = {
            insertar: insertar,
            cantidad: function () { return fotos.length; },
            limpiar: function () {
                for (var i = 0; i < urls.length; i++) revocar(i);
                fotos = []; pintar(); sincronizar();
            },
            archivos: function () { return fotos.slice(); }
        };

        pintar();
        sincronizar();
    }

    // ------------------------------------------------------------- arranque
    function init(raiz) {
        var cont = raiz || document;
        var cajas = cont.querySelectorAll('[data-cz-captura]');
        Array.prototype.forEach.call(cajas, iniciar);
    }

    window.CZCaptura = {
        engancharTodos: init,
        /** Igual que el flujo de la cámara: COMPRIME antes de aceptar las fotos. */
        agregar: function (cont, files) {
            if (!cont || !cont.__czApi) return Promise.resolve(0);
            return optimizar(files, cont).then(function (lista) {
                return cont.__czApi.insertar(lista);
            });
        },
        /** Inserta sin comprimir (solo si los archivos ya vienen optimizados). */
        insertar: function (cont, files) { return cont && cont.__czApi ? cont.__czApi.insertar(files) : 0; },
        cantidad: function (cont) { return cont && cont.__czApi ? cont.__czApi.cantidad() : 0; },
        archivos: function (cont) { return cont && cont.__czApi ? cont.__czApi.archivos() : []; },
        limpiar: function (cont) { if (cont && cont.__czApi) cont.__czApi.limpiar(); }
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { init(); });
    } else {
        init();
    }
})();
