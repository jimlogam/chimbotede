/* ============================================================================
 * imagen_optimizar.js — dechimbote.com
 * ============================================================================
 * QUÉ HACE: comprime la foto EN EL NAVEGADOR (en el celular del usuario) ANTES
 * de subirla al hosting. Así el usuario NO gasta datos subiendo 4 MB y la
 * subida es mucho más rápida en la calle.
 *
 * CÓMO: canvas → WebP (si el navegador lo soporta) o JPEG; lado mayor máximo
 * 1600 px; calidad 0.82. Respeta la orientación EXIF (fotos tomadas de lado).
 *
 * USO RÁPIDO (formularios clásicos con <input type="file">):
 *     CZImg.engancharInput(document.querySelector('input[name="foto"]'));
 *   → comprime solo al elegir la foto y reemplaza el archivo del input.
 *
 * USO MANUAL (cuando la app maneja los archivos en memoria, como Caminante):
 *     CZImg.optimizar(file).then(function(f){ ... f es el archivo ya liviano ... });
 *     CZImg.optimizarLista(files).then(function(lista){ ... });
 *
 * REGLA: si algo falla (navegador viejo, formato raro), devuelve el archivo
 * ORIGINAL. Nunca se pierde una foto por culpa de esta librería.
 * El servidor vuelve a optimizar igual (includes/imagenes.php), así que esto es
 * una mejora de velocidad, no la única defensa.
 * ==========================================================================*/
(function () {
  'use strict';

  var MAX_LADO   = 1600;        // lado mayor de la imagen que se sube
  var CALIDAD    = 0.82;        // calidad WebP/JPEG
  var SALTAR_PESO = 200 * 1024; // si ya pesa menos que esto y cabe, no se toca
  var TIPOS_OK   = /^image\/(jpeg|jpg|png|webp|bmp)$/i;

  var soporteWebp = null;

  function soportaWebpSalida() {
    if (soporteWebp !== null) return soporteWebp;
    try {
      var c = document.createElement('canvas');
      c.width = 1; c.height = 1;
      soporteWebp = (c.toDataURL('image/webp').indexOf('data:image/webp') === 0);
    } catch (e) {
      soporteWebp = false;
    }
    return soporteWebp;
  }

  function extensionDe(tipo) {
    if (tipo === 'image/webp') return 'webp';
    return 'jpg';
  }

  /** Carga la imagen respetando la orientación EXIF del celular. */
  function cargarImagen(file) {
    return new Promise(function (resolver) {
      if (window.createImageBitmap) {
        try {
          createImageBitmap(file, { imageOrientation: 'from-image' })
            .then(function (bmp) { resolver(bmp); })
            .catch(function () { resolver(cargarConImg(file)); });
          return;
        } catch (e) { /* sigue abajo */ }
      }
      resolver(cargarConImg(file));
    });
  }

  function cargarConImg(file) {
    return new Promise(function (resolver) {
      var url = URL.createObjectURL(file);
      var im = new Image();
      im.onload = function () { resolver(im); };
      im.onerror = function () { URL.revokeObjectURL(url); resolver(null); };
      im.src = url;
    });
  }

  function medidas(im) {
    if (!im) return [0, 0];
    return [im.width || im.naturalWidth || 0, im.height || im.naturalHeight || 0];
  }

  /**
   * Comprime UN archivo.
   * @returns Promise<File>  (el comprimido, o el original si no se pudo)
   */
  function optimizar(file, opciones) {
    var op = opciones || {};
    var maxLado = op.maxLado || MAX_LADO;
    var calidad = (typeof op.calidad === 'number') ? op.calidad : CALIDAD;

    return new Promise(function (resolver) {
      if (!file || !file.size) { resolver(file); return; }
      if (file.type && !TIPOS_OK.test(file.type)) { resolver(file); return; }

      cargarImagen(file).then(function (im) {
        if (!im) { resolver(file); return; }

        var m = medidas(im);
        var w = m[0], h = m[1];
        if (!w || !h) { resolver(file); return; }

        var ladoMayor = Math.max(w, h);

        // Ya es liviana y ya cabe: no se toca (evita perder calidad sin ganar nada)
        if (ladoMayor <= maxLado && file.size <= SALTAR_PESO) {
          if (im.close) { try { im.close(); } catch (e) {} }
          resolver(file);
          return;
        }

        var escala = Math.min(1, maxLado / ladoMayor);
        var nw = Math.max(1, Math.round(w * escala));
        var nh = Math.max(1, Math.round(h * escala));

        var cv;
        try {
          cv = document.createElement('canvas');
          cv.width = nw; cv.height = nh;
          var ctx = cv.getContext('2d');
          ctx.imageSmoothingEnabled = true;
          if ('imageSmoothingQuality' in ctx) ctx.imageSmoothingQuality = 'high';
          ctx.drawImage(im, 0, 0, nw, nh);
        } catch (e) { resolver(file); return; }

        if (im.close) { try { im.close(); } catch (e) {} }

        var tipoSalida = soportaWebpSalida() ? 'image/webp' : 'image/jpeg';

        var terminar = function (blob) {
          if (!blob || !blob.size || blob.size >= file.size) { resolver(file); return; }
          var nombre = (file.name || 'foto').replace(/\.[^.]+$/, '') + '.' + extensionDe(blob.type || tipoSalida);
          var salida;
          try {
            salida = new File([blob], nombre, { type: blob.type || tipoSalida, lastModified: Date.now() });
          } catch (e) {
            blob.name = nombre;
            salida = blob;
          }
          resolver(salida);
        };

        if (cv.toBlob) {
          cv.toBlob(function (blob) {
            // Si el navegador no sabe escribir WebP, devuelve PNG: reintentar en JPEG
            if (blob && tipoSalida === 'image/webp' && blob.type !== 'image/webp') {
              cv.toBlob(function (b2) { terminar(b2); }, 'image/jpeg', calidad);
              return;
            }
            terminar(blob);
          }, tipoSalida, calidad);
        } else {
          // Navegador muy viejo: dataURL
          try {
            var d = cv.toDataURL(tipoSalida, calidad);
            var bin = atob(d.split(',')[1]);
            var arr = new Uint8Array(bin.length);
            for (var i = 0; i < bin.length; i++) arr[i] = bin.charCodeAt(i);
            terminar(new Blob([arr], { type: tipoSalida }));
          } catch (e) { resolver(file); }
        }
      });
    });
  }

  /** Comprime una lista de archivos (en orden). */
  function optimizarLista(files, opciones) {
    var lista = Array.prototype.slice.call(files || []);
    if (!lista.length) return Promise.resolve([]);
    return Promise.all(lista.map(function (f) { return optimizar(f, opciones); }));
  }

  /** Muestra en el botón de guardar que se está optimizando. */
  function marcarOcupado(form, ocupado) {
    if (!form) return;
    form.dataset.czOcupado = ocupado ? '1' : '0';
    var botones = form.querySelectorAll('button[type="submit"], button:not([type]), input[type="submit"]');
    Array.prototype.forEach.call(botones, function (b) {
      if (ocupado) {
        if (b.dataset.czTexto === undefined) b.dataset.czTexto = b.textContent;
        b.disabled = true;
      } else {
        b.disabled = false;
        if (b.dataset.czTexto !== undefined && b.dataset.czTexto) b.textContent = b.dataset.czTexto;
      }
    });
  }

  /**
   * Engancha un <input type="file"> de un formulario clásico:
   * al elegir la foto, la comprime y REEMPLAZA el archivo del input.
   * Si el usuario pulsa Guardar mientras se comprime, el envío espera.
   */
  function engancharInput(input, opciones) {
    if (!input || input.dataset.czOptim === '1') return;
    input.dataset.czOptim = '1';

    var form = input.form || input.closest('form');

    // Guardia: si se intenta enviar mientras se optimiza, se espera.
    if (form && form.dataset.czGuardia !== '1') {
      form.dataset.czGuardia = '1';
      form.addEventListener('submit', function (ev) {
        if (form.dataset.czOcupado === '1' && form.__czPendiente) {
          ev.preventDefault();
          var pend = form.__czPendiente;
          if (window.CZImg && CZImg.aviso) CZImg.aviso('Optimizando la foto…');
          pend.then(function () {
            form.__czPendiente = null;
            if (typeof form.submit === 'function') form.submit();
          });
        }
      });
    }

    input.addEventListener('change', function () {
      var files = Array.prototype.slice.call(input.files || []);
      if (!files.length) return;

      marcarOcupado(form, true);
      var pend = optimizarLista(files, opciones).then(function (nuevos) {
        try {
          if (window.DataTransfer) {
            var dt = new DataTransfer();
            nuevos.forEach(function (f) { dt.items.add(f); });
            input.files = dt.files;
          } else if (typeof input.__czArchivos === 'object') {
            input.__czArchivos = nuevos; // respaldo para lectores manuales
          }
        } catch (e) { /* el servidor optimiza igual */ }
        marcarOcupado(form, false);
      });
      if (form) form.__czPendiente = pend;
    });
  }

  /** Engancha todos los input[type=file] que haya dentro de un contenedor. */
  function engancharTodos(raiz, opciones) {
    var cont = raiz || document;
    var inputs = cont.querySelectorAll('input[type="file"][accept*="image" i], input[type="file"][data-cz-optimizar]');
    Array.prototype.forEach.call(inputs, function (i) { engancharInput(i, opciones); });
  }

  /** Aviso breve y no bloqueante (para no usar alert). */
  function aviso(texto) {
    var id = 'czAvisoImg';
    var el = document.getElementById(id);
    if (!el) {
      el = document.createElement('div');
      el.id = id;
      el.style.cssText = 'position:fixed;left:50%;bottom:18px;transform:translateX(-50%);' +
        'background:#123c6b;color:#fff;padding:10px 16px;border-radius:999px;font:600 14px/1.2 system-ui,' +
        'Segoe UI,Roboto,sans-serif;z-index:99999;box-shadow:0 6px 18px rgba(0,0,0,.25);max-width:90vw;text-align:center';
      document.body.appendChild(el);
    }
    el.textContent = texto;
    el.style.display = 'block';
    clearTimeout(el.__t);
    el.__t = setTimeout(function () { el.style.display = 'none'; }, 2400);
  }

  window.CZImg = {
    optimizar: optimizar,
    optimizarLista: optimizarLista,
    engancharInput: engancharInput,
    engancharTodos: engancharTodos,
    aviso: aviso,
    soportaWebp: soportaWebpSalida,
    MAX_LADO: MAX_LADO,
    CALIDAD: CALIDAD
  };
})();
