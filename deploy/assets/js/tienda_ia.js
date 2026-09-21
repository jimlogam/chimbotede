/* =====================================================================================
 * assets/js/tienda_ia.js — LA CONVERSACIÓN DE «EL MAESTRO» 🛠️ (constructor de tiendas)
 * =====================================================================================
 * Qué hace: pinta la conversación de `/crear-tienda` y habla con `api/tienda_ia.php`.
 *   · El paso lo manda el SERVIDOR: aquí solo se pinta lo que llega (tipo, opciones…).
 *   · 📷 La CÁMARA abre la cámara del celular y manda UNA foto.
 *   · 🖼️ La GALERÍA deja marcar VARIAS fotos de una sola vez (hasta el tope del servidor, 8):
 *     se comprimen una por una con `CZImg.optimizar()` (1600 px, igual que el resto del sitio)
 *     y se mandan **en un solo envío** (`foto[]`), así el servidor las guarda todas juntas y la
 *     IA las mira en UNA sola llamada en vez de una por foto.
 *   · 🎙️ Dictado: lo pinta `dictado_voz.js` sobre el `[data-dictado]` (Web Speech API, gratis).
 *   · 📱 La página es de pantalla completa y el que scrollea es el chat, nunca la página:
 *     así la barra de escribir queda siempre abajo y **no hay pie del sitio** dentro del chat.
 *   · Nunca se muestra la clave de la API: eso pasa en el servidor.
 *
 * Guía: GUIA_CONSTRUCTOR_DE_TIENDAS.md
 * ===================================================================================== */
(function () {
  'use strict';

  var raiz = document.getElementById('tia');
  if (!raiz) return;

  var CFG = window.TIA_CFG || {};
  var elChat      = document.getElementById('tiaChat');
  var elOpciones  = document.getElementById('tiaOpciones');
  var elPie       = document.getElementById('tiaPie');
  var elTexto     = document.getElementById('tiaTexto');
  var elEnviar    = document.getElementById('tiaEnviar');
  /* 🎯🎨 EL COMPOSITOR DE LA GUÍA, TAL CUAL (orden del jefe, 2026-09-20): 📷 · 🎤 · cuadro · ➤.
     · `elCam` abre el **menú chiquito** (`elCamMenu`) con «Abrir cámara» / «Abrir galería».
     · `elVoz` es el **micrófono del BUSCADOR copiado tal cual** (`buscador_voz.js`) y su aviso.
     ⛔ Se fueron el compositor verde de WhatsApp (😊 📎 🎤 verde), los dos botones grandes de foto
     (`#tiaCamara` / `#tiaGaleria`), el cajón de emojis y la ventana modal de la foto. */
  var elCam       = document.getElementById('tiaCam');
  var elCamMenu   = document.getElementById('tiaCamMenu');
  var elCamFoto   = document.getElementById('tiaCamFoto');
  var elCamGal    = document.getElementById('tiaCamGaleria');
  var elVoz       = document.getElementById('tiaVoz');
  var elVozAviso  = document.getElementById('tiaVozAviso');
  var elArchivo   = document.getElementById('tiaArchivo');
  var elArchivoGal= document.getElementById('tiaArchivoGaleria');
  var elPaso      = document.getElementById('tiaPaso');
  var elBarra     = document.getElementById('tiaBarra');
  var elAyuda     = document.getElementById('tiaAyuda');
  var elGuardar   = document.getElementById('tiaGuardar');
  var elReiniciar = document.getElementById('tiaReiniciar');
  var elMenuBtn   = document.getElementById('tiaMenuBtn');
  var elMenu      = document.getElementById('tiaMenu');

  /* 🆕 2026-09-16 — LO DE LAS VARIAS TIENDAS Y LAS VENTANAS EMERGENTES (orden del jefe: *«un usuario
     puede tener varias tiendas… un mismo usuario puede tener varios negocios»* + *«pon submenús en
     popup escondidos, ejemplo al abrir la cámara puede ser un modal que carga las 2 opciones»*). */
  var elMisTiendas   = document.getElementById('tiaMisTiendas');
  var elOtraTienda   = document.getElementById('tiaOtraTienda');
  var elSheetTiendas = document.getElementById('tiaSheetTiendas');
  var elTiendasLista = document.getElementById('tiaSheetTiendasLista');
  var elTiendasOtra  = document.getElementById('tiaSheetTiendasOtra');
  var elSheetOtra    = document.getElementById('tiaSheetOtra');
  var elOtraTxt      = document.getElementById('tiaSheetOtraTxt');
  var elOtraSi       = document.getElementById('tiaSheetOtraSi');
  var misTiendas     = [];      // 🏪 las tiendas que ya tiene (las manda el servidor)
  var ultimo         = null;    // la última respuesta del servidor (para saber si ya publicó)

  var ocupado  = false;
  var tipEl    = null;      // la burbuja de «escribiendo…»
  var fotosTienda = 0;      // cuántas fotos de la tienda lleva (para avisar sin molestar)

  /* ---------------------------------------------------------------- utilidades */
  function esc(s) {
    return String(s == null ? '' : s)
      .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }

  /** Formato mínimo: **negrita**, saltos de línea y direcciones convertidas en enlaces. */
  function formato(txt) {
    var t = esc(txt);
    t = t.replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>');
    t = t.replace(/(https?:\/\/[^\s<]+)/g, function (u) {
      var limpia = u.replace(/[.,;:)]+$/, '');
      var cola = u.slice(limpia.length);
      return '<a href="' + limpia + '" target="_blank" rel="noopener">' + limpia + '</a>' + cola;
    });
    return t.replace(/\n/g, '<br>');
  }

  /** Baja el scroll DEL CHAT (nunca el de la página: la página no scrollea). */
  function abajo(suave) {
    if (!elChat) return;
    try {
      elChat.scrollTo({ top: elChat.scrollHeight, behavior: suave === false ? 'auto' : 'smooth' });
    } catch (e) { elChat.scrollTop = elChat.scrollHeight; }
  }

  function burbuja(rol, html, extra) {
    var d = document.createElement('div');
    d.className = 'tia-msg tia-msg--' + (rol === 'yo' ? 'yo' : 'bot') + (extra ? ' ' + extra : '');
    var av = (rol === 'yo') ? '' : '<span class="tia-msg__av" aria-hidden="true">' + (CFG.emoji || '🛠️') + '</span>';
    d.innerHTML = av + '<div class="tia-msg__burbuja">' + html + '</div>';
    elChat.appendChild(d);
    abajo();
    return d;
  }

  function pintarMensajes(lista) {
    (lista || []).forEach(function (m) {
      var rol = (m.rol === 'yo' || m.rol === 'user') ? 'yo' : 'bot';
      burbuja(rol, formato(m.texto));
    });
  }

  function ayuda(txt) { if (elAyuda) elAyuda.textContent = txt || ''; }

  /** La burbuja de «escribiendo…» (mientras el servidor piensa o mira las fotos). */
  function pensando(si, frase) {
    ocupado = !!si;
    elEnviar.disabled = !!si;
    if (elCam) elCam.disabled = !!si;
    if (elVoz) elVoz.disabled = !!si;
    if (!si) {
      if (tipEl) { tipEl.remove(); tipEl = null; }
      // 🎯 La flecha se apaga sola si el cuadro quedó vacío (el ➤ no dicta: eso es del 🎤).
      pintarBotonEnviar();
      return;
    }
    if (!tipEl) {
      tipEl = document.createElement('div');
      tipEl.className = 'tia-msg tia-msg--bot';
      tipEl.innerHTML = '<span class="tia-msg__av" aria-hidden="true">' + (CFG.emoji || '🛠️') + '</span>'
                      + '<div class="tia-msg__burbuja tia-tip"><span></span><span></span><span></span></div>';
      elChat.appendChild(tipEl);
    }
    abajo();
    if (frase) ayuda(frase);
  }

  function errorAmable(txt, reintentar) {
    var d = burbuja('bot', '😅 ' + formato(txt));
    if (reintentar) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'tia-chip';
      b.textContent = '🔄 Probar otra vez';
      b.addEventListener('click', function () { b.remove(); enviar({ accion: 'responder', tipo: 'texto', texto: reintentar }); });
      var caja = d.querySelector('.tia-msg__burbuja');
      caja.appendChild(document.createElement('br'));
      caja.appendChild(b);
    }
  }

  /* ---------------------------------------------------------------- el estado que manda el servidor */
  function pintarPaso(r) {
    ultimo = r;   // 🆕 lo último que dijo el servidor (lo usa el modal de «crear otra tienda»)
    if (r.mis_tiendas) pintarMisTiendas(r.mis_tiendas);
    if (elPaso) {
      var t = r.paso_titulo || '';
      if (r.modo === 'producto' && t !== '') t += ' · producto';
      elPaso.textContent = (r.tipo === 'publicado' && r.paso === 'publicado') ? '¡Tienda publicada!' : (t || 'Paso a paso');
    }
    if (elBarra) elBarra.style.width = Math.round((r.progreso || 0) * 100) + '%';

    // 📊 Lo que el servidor ya sabe (aviso suave de cuántas fotos lleva)
    if (r.datos && r.datos.fotos) fotosTienda = r.datos.fotos.length;

    // Opciones (chips). Se aceptan tal cual lleguen: el servidor las manda bien formadas.
    // 📐 Tres formas de pintarlas (lo decide el servidor con sus banderas):
    //    · `tarjeta` (los distritos) y `rejilla` (el menú de edición) → en una grilla de 2 columnas;
    //    · `azul` (el botón «Todos los distritos») → ancho completo;
    //    · el resto → chips sueltos, como siempre.
    elOpciones.innerHTML = '';
    var grid = null;
    (r.opciones || []).forEach(function (o) {
      var esObj = (o && typeof o === 'object');
      var texto = esObj ? String(o.texto || '') : String(o == null ? '' : o);
      var nota  = esObj ? (o.nota || '') : '';
      if (texto === '') return;
      var limpio = texto.replace(/^\s*✅\s*/, '');

      // 🗺️ La tarjeta de distrito: miniatura con el color de su zona + el nombre.
      if (esObj && o.tarjeta) {
        if (!grid) { grid = document.createElement('div'); grid.className = 'tia-opciones--rejilla'; elOpciones.appendChild(grid); }
        var t = document.createElement('button');
        t.type = 'button';
        t.className = 'tia-dist' + (/^\s*✅/.test(texto) ? ' is-ok' : '');
        t.innerHTML = '<span class="tia-dist__mini" style="background:' + esc(o.color || '#123c6b') + '">'
                    + '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.2a7.3 7.3 0 0 0-7.3 7.3c0 5.5 7.3 12.3 7.3 12.3s7.3-6.8 7.3-12.3A7.3 7.3 0 0 0 12 2.2zm0 9.9a2.6 2.6 0 1 1 0-5.2 2.6 2.6 0 0 1 0 5.2z"/></svg>'
                    + '</span><span>' + esc(limpio) + '</span>';
        t.addEventListener('click', function () {
          tocarOpcion({ texto: limpio, valor: String(o.valor == null ? limpio : o.valor) });
        });
        grid.appendChild(t);
        return;
      }

      var esEnlace = esObj && !!o.url;
      var b = document.createElement(esEnlace ? 'a' : 'button');
      if (esEnlace) { b.href = o.url; if (!o.misma) { b.target = '_blank'; b.rel = 'noopener'; } }
      else b.type = 'button';
      // 📍🆕 El botón MORADO de la ubicación (orden del jefe, 2026-09-20): el paso `ubicacion` manda
      // `morado: true` y así se pinta, morado, dentro de la conversación.
      var clase = 'tia-chip' + (esObj && o.principal ? ' tia-chip--principal' : '')
                + (esObj && o.azul ? ' tia-chip--azul' : '')
                + (esObj && o.morado ? ' tia-chip--morado' : '');
      b.className = clase;
      b.innerHTML = esc(texto) + (nota ? ' <em>' + esc(nota) + '</em>' : '');
      // El menú de edición (4 opciones con `rejilla`) va en dos columnas, como lo pidió el jefe.
      if (esObj && o.rejilla && !esEnlace) {
        if (!grid) { grid = document.createElement('div'); grid.className = 'tia-opciones--rejilla'; elOpciones.appendChild(grid); }
        b.style.justifyContent = 'center';
        b.style.textAlign = 'center';
        grid.appendChild(b);
      } else if (esEnlace) {
        elOpciones.appendChild(b);
      } else {
        elOpciones.appendChild(b);
      }
      if (esEnlace) {
        b.addEventListener('click', function () { burbuja('yo', esc(texto)); });
      } else {
        b.addEventListener('click', function () {
          tocarOpcion({ texto: limpio, valor: esObj ? String(o.valor == null ? texto : o.valor) : texto, url: esObj ? o.url : '' });
        });
      }
    });

    // La barra de escribir según el tipo de paso
    var tipo = r.tipo || 'texto';
    var pideFoto = (tipo === 'foto');
    // 🎯 2026-09-20: el compositor es el de la guía y está SIEMPRE a la vista (📷 · 🎤 · cuadro · ➤);
    // las fotos se mandan con el 📷 en cualquier momento (su menú tiene «Abrir cámara» y «Abrir
    // galería»). Ya no hay botones grandes que aparezcan y desaparezcan.
    ayuda('');
    if (pideFoto) {
      elTexto.placeholder = 'Escribe si quieres… (lo importante son las fotos 📷)';
      var n = fotosTienda, max = Number(r.max_fotos_tienda || CFG.max_fotos || 8);
      if (r.paso === 'productos_lote') {
        // 📷🛍️ La tanda de fotos de PRODUCTOS (2026-09-16): aquí el aviso no es de las fotos de la
        // tienda, sino de las fotos que va a clasificar la IA.
        ayuda('Toca 📷 y elige «Abrir galería»: marca las fotos de tus productos y yo te los clasifico 📷');
      } else if (r.solo_galeria) {
        var min = Number(r.min_fotos_arranque || CFG.min_fotos_arranque || 5);
        ayuda(n > 0 ? ('Llevas ' + n + ' de ' + min + ' fotos · te faltan ' + Math.max(0, min - n)
                      + ' · toca 📷 y elige «Abrir galería»')
                    : ('Toca 📷 y elige «Abrir galería» para mandarme tus fotos de una vez'));
      } else {
        ayuda(n > 0 ? ('Llevas ' + n + ' de ' + max + ' fotos · toca 📷 para mandar más')
                    : 'Toca 📷 y elige «Abrir cámara» o «Abrir galería»');
      }
    } else if (tipo === 'resumen') {
      elTexto.placeholder = '¿Algo que cambiar? Toca «Cambiar algo» 👆';
    } else if (tipo === 'publicado' || tipo === 'mas') {
      elTexto.placeholder = 'Escribe o habla 🎙️';
    } else {
      elTexto.placeholder = 'Escribe o habla 🎙️';
    }

    // Cuadros especiales
    // 📋 La tarjeta «Así ha quedado tu tienda»: se pinta cuando el servidor la manda (al publicar con
    // el menú de edición, en el paso publicado y en el resumen viejo).
    if (r.resumen && (tipo === 'resumen' || tipo === 'publicado' || tipo === 'opciones')) pintarResumen(r.resumen);
    if (tipo === 'publicado' && r.publicado) pintarPublicado(r.publicado, r.datos, r);
    if (tipo === 'producto') pintarProductoListo(r);
    if (tipo === 'mas') pintarMasProductos(r);
    if (tipo === 'aviso' && r.mensaje) burbuja('bot', formato(r.mensaje));
    (r.enlaces || []).forEach(function (e) { chipEnlace(e.texto, e.url, e.principal); });
    // 📷 La tira con LAS FOTOS QUE YA SUBIÓ: se le muestra cuando tiene que elegir una — para el
    // producto (*«como ya tenemos las fotos cargadas puedes mostrarle un slide…»*), para su portada y
    // para cambiarle la foto a un producto.
    if (pideFoto && r.datos && (r.datos.fotos || []).length
        && ['producto_fotos', 'portada', 'producto_editar_foto'].indexOf(r.paso) >= 0) {
      pintarSlideFotos(r.datos.fotos, r.paso);
    }

    setTimeout(function () { abajo(); }, 60);
  }

  /** 📷 La tira de fotos que ya subió el dueño: toca una y se usa (producto, portada…). */
  function pintarSlideFotos(fotos, paso) {
    var tit = (paso === 'portada') ? 'Toca la foto que quieres de portada 👇'
            : (paso === 'producto_editar_foto' ? 'Toca la foto nueva de tu producto 👇'
                                               : 'Tus fotos: toca una para usarla en el producto 👇');
    var html = '<div class="tia-slide">'
             + '<p class="tia-slide__tit">' + tit + '</p>'
             + '<div class="tia-slide__tira">';
    fotos.forEach(function (f, i) {
      var u = String(f).indexOf('http') === 0 ? f : ((CFG.sitio || '') + '/' + String(f).replace(/^\//, ''));
      html += '<button type="button" class="tia-slide__foto" data-i="' + i + '" aria-label="Usar la foto ' + (i + 1) + '">'
            + '<img src="' + esc(u) + '" alt="Foto ' + (i + 1) + '" loading="lazy"></button>';
    });
    html += '</div></div>';
    var d = burbuja('bot', '', 'tia-msg--ancha');
    d.querySelector('.tia-msg__burbuja').innerHTML = html;
    Array.prototype.forEach.call(d.querySelectorAll('.tia-slide__foto'), function (b) {
      b.addEventListener('click', function () {
        if (ocupado) return;
        burbuja('yo', 'Usar esa foto');
        enviar({ accion: 'responder', tipo: 'opcion', valor: 'usar_foto:' + b.getAttribute('data-i'), texto: 'Usar esa foto' });
      });
    });
  }

  /** Cuadro con el botón de WhatsApp (ícono oficial del sitio cuando está disponible). */
  function botonWa(texto, url, clase) {
    var ico = CFG.wa_icono ? '<span class="tia-wa-ico">' + CFG.wa_icono + '</span>' : '';
    return '<a class="tia-btn ' + (clase || 'tia-btn--wa') + '" target="_blank" rel="noopener" href="' + esc(url) + '">'
         + ico + esc(texto) + '</a>';
  }

  function chipEnlace(texto, url, principal) {
    var b = document.createElement('a');
    b.className = 'tia-chip' + (principal === false ? '' : ' tia-chip--principal');
    b.href = url;
    b.target = '_blank';
    b.rel = 'noopener';
    b.textContent = texto;
    elOpciones.appendChild(b);
  }

  function pintarResumen(res) {
    var html = '<div class="tia-resumen">';
    html += '<h3 class="tia-resumen__tit">' + esc(res.titulo || 'Así va a quedar tu tienda') + '</h3>';
    (res.filas || []).forEach(function (f) {
      if (!f.valor) return;
      html += '<div class="tia-resumen__fila"><span class="tia-resumen__et">' + esc(f.etiqueta) + '</span>'
            + '<span class="tia-resumen__va">' + formato(f.valor) + '</span></div>';
    });
    if ((res.fotos || []).length) {
      html += '<div class="tia-resumen__fotos">';
      res.fotos.forEach(function (u) { html += '<img src="' + esc(u) + '" alt="Foto de tu tienda" loading="lazy">'; });
      html += '</div>';
    }
    (res.productos || []).forEach(function (p) {
      html += '<div class="tia-resumen__prod">';
      if ((p.fotos || []).length) html += '<img src="' + esc(p.fotos[0]) + '" alt="' + esc(p.titulo) + '" loading="lazy">';
      html += '<div><strong>' + esc(p.titulo) + '</strong><br><small>'
            + (p.precio ? 'S/ ' + Number(p.precio).toFixed(2) : 'A consultar') + '</small></div>';
      html += '</div>';
    });
    html += '<p class="tia-resumen__nota">'
         + (res.nota ? formato(res.nota)
                     : 'Cuando toques <strong>PUBLICAR MI TIENDA</strong>, aparece en ' + esc(CFG.sitio_nombre || 'DeChimbote.com') + ' y te doy el enlace para compartirla.')
         + '</p>';
    html += '</div>';
    var d = burbuja('bot', '', 'tia-msg--ancha');
    d.querySelector('.tia-msg__burbuja').innerHTML = html;
  }

  function pintarPublicado(pub, datos, r) {
    var nombre = (datos && datos.nombre) || 'Tu tienda';
    var cuenta = r && r.cuenta ? r.cuenta : null;
    var html = '<div class="tia-festejo">'
      + '<div class="tia-festejo__emoji">🎉</div>'
      + '<h3>¡' + esc(nombre) + ' ya está en línea!</h3>'
      + '<p>Compártela con tus clientes 👇</p>'
      + '<p class="tia-festejo__url"><a href="' + esc(pub.url) + '" target="_blank" rel="noopener">' + esc(pub.url) + '</a></p>'
      + '<div class="tia-festejo__botones">'
      + botonWa('Compartir mi tienda', (CFG.wa_share || 'https://wa.me/?text=') + encodeURIComponent('Mira mi tienda: ' + nombre + ' — ' + pub.url))
      + '</div></div>';

    // 🔑 Los datos para entrar: se muestran UNA vez, con el botón ROJO para guardarlos en su WhatsApp
    // (orden del jefe: *«el botón que dice guardar en mi WhatsApp… debe ser llamativo, color rojo; es
    // muy importante que el usuario guarde su usuario y su contraseña»*).
    if (cuenta && cuenta.usuario) {
      html += '<div class="tia-cuenta">'
        + '<h4>🔑 Tus datos para entrar</h4>'
        + '<p class="tia-cuenta__fila"><span>Usuario</span><strong>' + esc(cuenta.usuario) + '</strong></p>';
      if (cuenta.clave) {
        html += '<p class="tia-cuenta__fila"><span>Contraseña</span><strong>' + esc(cuenta.clave) + '</strong></p>';
      }
      html += '<p class="tia-cuenta__nota">Tu usuario es tu número de WhatsApp. '
            + '<strong>Guárdalos AHORA</strong>: la contraseña se muestra una sola vez y después no te la podré volver a mostrar.</p>';
      if (r.wa_datos) html += '<div class="tia-festejo__botones">' + botonWa('💾 Guardar mis datos en mi WhatsApp', r.wa_datos, 'tia-btn--rojo') + '</div>';
      html += '</div>';
    }

    var d = burbuja('bot', '', 'tia-msg--ancha');
    d.querySelector('.tia-msg__burbuja').innerHTML = html;
  }

  /** 🎉 El producto quedó en línea: foto + la tarjeta (eliminarlo o crear otro va en los botones). */
  function pintarProductoListo(r) {
    var d = r.datos || {};
    var i = Number(d.producto_indice || 0);
    var p = (d.productos && d.productos[i]) ? d.productos[i] : null;
    if (!p) return;
    var foto = (p.fotos && p.fotos[0]) ? p.fotos[0] : '';
    var html = '<div class="tia-prod">'
      + (foto ? '<img src="' + esc(foto) + '" alt="' + esc(p.titulo) + '" loading="lazy">' : '')
      + '<div><strong>' + esc(p.titulo) + '</strong><br><small>'
      + (p.precio ? 'S/ ' + Number(p.precio).toFixed(2) : 'A consultar') + ' · en línea ✅</small></div>'
      + '</div>';
    var b = burbuja('bot', '', 'tia-msg--ancha');
    b.querySelector('.tia-msg__burbuja').innerHTML = html;
  }

  /** 💬 «Quiero más productos»: se va al WhatsApp del jefe con el mensaje ya escrito. */
  function pintarMasProductos(r) {
    var html = '<div class="tia-festejo">'
      + '<div class="tia-festejo__emoji">💬</div>'
      + '<h3>Más productos 💬</h3>'
      + '<p>El mensaje ya va escrito. Solo dale enviar.</p>'
      + '<div class="tia-festejo__botones">'
      + botonWa('Escribirle por WhatsApp', r.wa_mas)
      + '</div>'
      + '<p class="tia-cuenta__nota">Entra cuando quieras con tu número y tu clave.</p>'
      + '</div>';
    var d = burbuja('bot', '', 'tia-msg--ancha');
    d.querySelector('.tia-msg__burbuja').innerHTML = html;
  }

  /* ==================================================================================
   * 🪟 LAS VENTANAS EMERGENTES (2026-09-16)
   * ==================================================================================
   * Orden del jefe: *«pon submenús en popup escondidos, ejemplo al abrir la cámara puede ser un modal
   * que carga las 2 opciones y asimismo mete otros modales donde lo creas conveniente»*.
   *
   * Las tres nacen escondidas en la página (`hidden`) y aquí solo se abren y se cierran:
   *   · 📷 `#tiaSheetFoto`    → las DOS formas de mandar una foto (cámara o galería).
   *   · 🏪 `#tiaSheetTiendas` → TODAS las tiendas del dueño (un mismo usuario puede tener varios
   *                             negocios) y, desde ahí, arrancar otra.
   *   · 🆕 `#tiaSheetOtra`    → la confirmación de «crear otra tienda».
   * Se cierran con «Cancelar/Cerrar», tocando el fondo (el velo) o con la tecla Escape.
   * ================================================================================== */
  function abrirSheet(el) {
    if (!el) return;
    cerrarCamMenu();
    cerrarMenu();
    el.hidden = false;
    document.body.classList.add('tia-con-sheet');
  }

  function cerrarSheet(el) {
    if (!el) return;
    el.hidden = true;
    var alguno = [elSheetTiendas, elSheetOtra].some(function (s) { return s && !s.hidden; });
    if (!alguno) document.body.classList.remove('tia-con-sheet');
  }

  function cerrarSheets() { [elSheetTiendas, elSheetOtra].forEach(cerrarSheet); }

  /** 🏪 El modal de «Mis tiendas»: la lista de sus negocios (con su enlace) y el botón de otra tienda. */
  function pintarMisTiendas(lista) {
    if (Array.isArray(lista)) misTiendas = lista;
    if (elMisTiendas) elMisTiendas.textContent = '🏪 Mis tiendas' + (misTiendas.length ? ' (' + misTiendas.length + ')' : '');
    if (!elTiendasLista) return;
    if (!misTiendas.length) {
      elTiendasLista.innerHTML = '<p class="tia-sheet__vacio">Todavía no tienes ninguna tienda publicada 🏪<br>'
                               + 'La primera la armamos aquí abajo 👇</p>';
      return;
    }
    var h = '';
    misTiendas.forEach(function (t) {
      var n = Number(t.productos || 0);
      // 🛍️ Cada tienda trae DOS puertas (2026-09-16, pedido del jefe: *«¿cómo puede el usuario seguir
      // creando más productos?»*): ver su ficha y —la nueva— **cargarle productos** en su inventario
      // (la pantalla de cámara + voz del panel, sin tope y sin pasar por el asistente).
      h += '<div class="tia-tienda">'
         + '<span class="tia-tienda__ico">🏪</span>'
         + '<span class="tia-tienda__txt"><strong>' + esc(t.nombre) + '</strong><small>'
         + (n > 0 ? n + (n === 1 ? ' producto' : ' productos') : 'todavía sin productos')
         + (t.estado && t.estado !== 'activo' ? ' · ' + esc(t.estado) : '')
         + '</small></span>'
         + '<span class="tia-tienda__acc">'
         + (t.editar ? '<a class="tia-tienda__mas" href="' + esc(t.editar) + '" target="_blank" rel="noopener" title="Agregar un producto">➕ <span>Producto</span></a>' : '')
         + '<a class="tia-tienda__ver" href="' + esc(t.url) + '" target="_blank" rel="noopener">Ver ›</a>'
         + '</span></div>';
    });
    elTiendasLista.innerHTML = h;
  }

  function abrirTiendasSheet() { pintarMisTiendas(); abrirSheet(elSheetTiendas); }

  /** 🆕 El modal de «crear otra tienda»: la confirmación, con el aviso según lo que lleve hecho. */
  function abrirOtraSheet() {
    if (elOtraTxt) {
      var d = (ultimo && ultimo.datos) ? ultimo.datos : {};
      var publicada = !!(d.publicado && d.publicado.url);
      var nombre = d.nombre || 'la que estabas armando';
      var nfotos = (d.fotos && d.fotos.length) ? d.fotos.length : 0;
      var que = '';
      if (publicada) {
        que = 'Tu tienda «' + nombre + '» ya está publicada y **no se toca**: sus fotos, sus productos y su enlace siguen igual.';
      } else if (nfotos > 0) {
        que = 'Ojo: la tienda que estabas armando todavía no se publica, así que lo que llevas (tus ' + nfotos + ' fotos) se queda a medias.';
      } else {
        que = 'Vas a empezar una tienda nueva, aparte de las que ya tengas publicadas.';
      }
      elOtraTxt.innerHTML = formato(que + '\n\nVamos con **las fotos del otro negocio** 📷');
    }
    abrirSheet(elSheetOtra);
  }

  /** 🆕 Le pide al servidor la conversación nueva (él cierra la que estaba abierta y abre otra limpia). */
  function crearOtraTienda() {
    cerrarSheets();
    if (ocupado) return;
    burbuja('yo', '🆕 Crear otra tienda');
    enviar({ accion: 'responder', tipo: 'opcion', valor: 'otra_tienda', texto: '🆕 Crear otra tienda' });
  }

  /* ---------------------------------------------------------------- hablar con el servidor */
  function enviar(datos, lote) {
    if (ocupado) return;
    pensando(true, lote ? 'Subiendo tus fotos…' : '');
    var opciones = { method: 'POST', credentials: 'same-origin' };
    var cuerpo;
    if (lote && lote.length) {
      cuerpo = new FormData();
      cuerpo.append('accion', 'responder');
      cuerpo.append('csrf', CFG.csrf);
      cuerpo.append('modo', CFG.modo || 'nueva');
      // 🛒🆕 El producto ya dicho (botón «Agregar mi tienda» del buscador): viaja en cada envío porque
      // el motor lo necesita para no preguntar el nombre y pedir de una vez las fotos de ese producto.
      if (CFG.producto) cuerpo.append('prod', CFG.producto);
      cuerpo.append('tipo', 'foto');
      // 📷 VARIAS fotos en un solo envío: el servidor las guarda todas y las mira de una vez.
      lote.forEach(function (f) { cuerpo.append('foto[]', f, f.name || 'foto.jpg'); });
    } else {
      opciones.headers = { 'Content-Type': 'application/json' };
      datos.csrf = CFG.csrf;
      datos.modo = CFG.modo || 'nueva';
      if (CFG.producto) datos.prod = CFG.producto;   // 🛒🆕 el producto ya dicho (ver arriba)
      cuerpo = JSON.stringify(datos);
    }
    opciones.body = cuerpo;

    fetch(CFG.api, opciones)
      .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
      .then(function (res) {
        pensando(false);
        var j = res.j || {};
        if (j.error === 'sin_sesion') { window.location.href = j.login_url || (CFG.sitio + '/login.php'); return; }
        if (!j.ok) { errorAmable(j.mensaje || 'No pude seguir. Prueba otra vez.'); return; }
        if (j.reiniciado) { window.location.reload(); return; }
        // 🆕 El dueño pidió OTRA tienda: el servidor ya cerró la conversación anterior y devolvió una
        // limpia. Se borra la pantalla (la tienda que ya publicó no se toca: sigue en su enlace) y se
        // pinta la nueva conversación, que arranca pidiendo las fotos del otro negocio.
        // ⚠️ Si veníamos del modo «agregar un producto» (`?modo=producto`), esa conversación es OTRA
        // (el modo es una columna de la tabla): se recarga en la página limpia, que ya retoma la
        // conversación nueva que el servidor acaba de abrir.
        if (j.nueva_tienda) {
          if (CFG.modo && CFG.modo !== 'nueva') { window.location.href = CFG.nueva || CFG.casa; return; }
          elChat.innerHTML = '';
          tipEl = null;
        }
        pintarMensajes(j.mensajes);
        if ((!j.mensajes || j.mensajes.length === 0) && j.mensaje) burbuja('bot', formato(j.mensaje));
        pintarPaso(j);
      })
      .catch(function () {
        pensando(false);
        errorAmable('Se cortó el internet. Revisa tu señal y toca «Probar otra vez».');
      });
  }

  function tocarOpcion(o) {
    if (!o || ocupado) return;
    if (o.url) { burbuja('yo', esc(o.texto)); window.open(o.url, '_blank', 'noopener'); return; }
    // 🆕 «Crear otra tienda» (el botón de la tarjeta final y el del modal «Mis tiendas»): antes de
    // empezar se le confirma en una ventana, con el aviso de que lo ya publicado NO se toca.
    if (o.valor === 'otra_tienda' || o.valor === 'nueva_tienda') { abrirOtraSheet(); return; }
    // 📷 Los botones de foto del paso del producto («📷 Abrir cámara» / «🖼️ Abrir galería») no van
    // al servidor: abren la cámara o el carrete aquí mismo (orden del jefe: *«ese botón de "otra"
    // debería ser "abrir galería" o "abrir cámara", o sea ser más específico»*).
    // 🎯 2026-09-20: la cámara ya no abre una ventana modal: abre su **menú chiquito** (📷 · 🖼️), el
    // mismo de la guía. El de la galería sigue abriendo el carrete directo (el botón dice lo que hace).
    if (o.valor === 'camara') { abrirCamMenu(); return; }
    if (o.valor === 'galeria') {
      burbuja('yo', esc(o.texto));
      if (elArchivoGal) elArchivoGal.click();
      return;
    }
    burbuja('yo', esc(o.texto));
    if (o.valor === 'gps') { pedirUbicacion(o); return; }
    enviar({ accion: 'responder', tipo: 'opcion', valor: String(o.valor == null ? '' : o.valor), texto: String(o.texto || '') });
  }

  /** 📍 La ubicación del celular: el servidor saca el distrito con los datos del sitio. */
  function pedirUbicacion(o) {
    if (!navigator.geolocation) {
      errorAmable('Tu navegador no me deja ver la ubicación. Escríbeme tu distrito (Chimbote, Nuevo Chimbote, Coishco o Santa) y sigo 👇');
      return;
    }
    pensando(true, 'Buscando tu zona…');
    navigator.geolocation.getCurrentPosition(function (pos) {
      pensando(false);
      enviar({ accion: 'responder', tipo: 'gps', valor: 'gps', lat: pos.coords.latitude, lng: pos.coords.longitude });
    }, function () {
      pensando(false);
      // 📍 ORDEN DEL JEFE (2026-09-20): la ubicación es obligatoria, pero si no la comparte se le deja
      // ESCRIBIR su distrito (así nadie queda atrapado sin poder crear su tienda).
      errorAmable('No me diste permiso para la ubicación. Escríbeme tu distrito (Chimbote, Nuevo Chimbote, Coishco o Santa) y sigo 👇');
    }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 });
  }

  /* ---------------------------------------------------------------- escribir y enviar */
  function ajustarAlto() {
    elTexto.style.height = 'auto';
    elTexto.style.height = Math.min(elTexto.scrollHeight, 140) + 'px';
  }

  function mandarTexto() {
    var t = (elTexto.value || '').trim();
    if (t === '' || ocupado) return;
    autoEnvioCancelar();                 // 🚀 lo manda él: que no se mande otra vez solo
    if (dictando) vozDetener();
    burbuja('yo', esc(t));
    elTexto.value = '';
    ajustarAlto();
    enviar({ accion: 'responder', tipo: 'texto', texto: t });
  }

  elTexto.addEventListener('keydown', function (ev) {
    if (ev.key === 'Enter' && !ev.shiftKey) { ev.preventDefault(); mandarTexto(); return; }
    // 🚀 Escribir cancela el envío automático del dictado (si no, mandaría lo que está corrigiendo).
    autoEnvioCancelar();
  });
  elTexto.addEventListener('input', function () { ajustarAlto(); pintarBotonEnviar(); });

  /* ══════════════════════════════════════════════════════════════════════════════════════════════
   * 🎯 EL COMPOSITOR DE LA GUÍA, TAL CUAL (orden del jefe, 2026-09-20)
   * ──────────────────────────────────────────────────────────────────────────────────────────────
   * Textual: *«quiero llevar este mismo estilo de los botones —el botón de cámara (el que abre el modal
   * de "Abrir cámara" o "Abrir galería"), el botón de grabar, el input y el botón de enviar— a la página
   * del maestro, tal cual como lo tenemos ahorita, tal cual»*.
   *   · 📷 `#tiaCam` abre un **MENÚ CHIQUITO encima del botón** (como el ☰ del sitio, con icono de
   *     cámara): **📷 Abrir cámara** (input con `capture`) y **🖼️ Abrir galería** (input `multiple`).
   *   · 🎤 `#tiaVoz` es **el MISMO motor del buscador** que ya usa la guía (`buscador_voz.js` copiado):
   *     reconocedor NUEVO por dictado, el candado `instancia === vozActual`, `es-PE` → `es-ES` → `es-MX`,
   *     `interimResults` (lo que se oye se escribe en el cuadro) y sus avisos en la misma caja blanca.
   *     Lo dictado cae en el cuadro y **no se limpia** (una frase del dueño va entera).
   *   · 🚀 **ENVÍO AUTOMÁTICO**: si el micrófono se apaga **porque escuchó silencio**, el mensaje se
   *     manda **al segundo** (igual que en la guía). Se cancela si él escribe, toca el micrófono, abre
   *     la cámara o le da al ➤. Si el que apaga el micrófono es **su dedo**, no se manda solo.
   *   · ➤ `#tiaEnviar` **solo envía** (el círculo naranja de la guía).
   *   ⛔ Se fueron: el 😊 de emojis, el 📎 de adjuntar, el micrófono verde de WhatsApp con su corte a
   *     los 2 minutos, los dos botones grandes de foto y la ventana modal de la cámara.
   * ══════════════════════════════════════════════════════════════════════════════════════════════ */
  var Rec = window.SpeechRecognition || window.webkitSpeechRecognition;
  var IDIOMAS_VOZ   = ['es-PE', 'es-ES', 'es-MX'];   // Perú primero; si no lo tiene, cae al siguiente
  var ESPERA_FINAL  = 260;                            // ms para dejar llegar el último trozo del dictado
  var AVISO_VOZ_MS  = 7000;                           // lo que dura el aviso (igual que en el buscador)
  var AUTO_ENVIO_MS = 1000;                           // 🚀 el segundo de gracia antes de mandar solo
  var PISTA         = 'Escribe tu respuesta…';
  var MENSAJES_VOZ  = {                               // los MISMOS textos del buscador
    'not-allowed': '🎙️ El micrófono está bloqueado. Toca el candado 🔒 de la barra de direcciones, permite el micrófono y vuelve a intentarlo.',
    'service-not-allowed': '🎙️ El navegador no dejó usar el micrófono. Revisa los permisos del sitio.',
    'audio-capture': '🎙️ No se encontró un micrófono en este dispositivo.',
    'network': '🎙️ Sin conexión para reconocer la voz. Revisa tus datos o el wifi.',
    'no-speech': '🎙️ No te escuché. Toca el micrófono y habla más cerca.',
    'bad-grammar': '🎙️ No entendí bien. Prueba diciéndolo más despacio y en frases cortas.'
  };
  var dictando = false, vozActual = null, dicho = '', interino = '';
  var idiomaVoz = 0, huboErrorVoz = false, yaDictado = false, vozParoManual = false;
  var timerFinalVoz = null, timerAvisoVoz = null, timerAutoEnvio = null;

  /** La flecha de enviar solo se apaga si no hay nada que mandar (el ➤ no dicta: eso es del 🎤). */
  function pintarBotonEnviar() {
    if (!elEnviar) return;
    var hay = (elTexto.value || '').trim() !== '';
    elEnviar.disabled = !hay || ocupado;
  }

  function vozOcultarAviso() {
    clearTimeout(timerAvisoVoz);
    if (elVozAviso) elVozAviso.className = 'tia-voz-aviso';
  }

  /** 🔔 La misma caja blanca del buscador, con sus mismos textos. */
  function vozAvisar(texto, tipo) {
    if (!elVozAviso) return;
    elVozAviso.textContent = texto;
    elVozAviso.className = 'tia-voz-aviso tia-voz-aviso--' + (tipo || 'info') + ' tia-voz-aviso--visible';
    clearTimeout(timerAvisoVoz);
    timerAvisoVoz = setTimeout(vozOcultarAviso, AVISO_VOZ_MS);
  }

  function vozSoltar() {
    dictando = false;
    if (elVoz) {
      elVoz.classList.remove('is-escuchando');
      elVoz.setAttribute('aria-pressed', 'false');
      elVoz.setAttribute('title', 'Hablar: tu voz se escribe en el cuadro');
      elVoz.setAttribute('aria-label', 'Hablar: tu voz se escribe en el cuadro');
    }
    elTexto.placeholder = PISTA;
    elTexto.classList.remove('voz-escuchando');
  }

  /** 🗣️ Escribe en el cuadro lo que se entendió (SIN limpiar: la frase va entera). */
  function vozTerminar() {
    if (yaDictado) return true;
    var bruto = (dicho || interino || (elTexto.value || '')).replace(/\s+/g, ' ').trim();
    if (!bruto) return false;
    yaDictado = true;
    elTexto.value = bruto.charAt(0).toUpperCase() + bruto.slice(1);
    ajustarAlto();
    pintarBotonEnviar();
    return true;
  }

  /** 🚀 Deja armado el envío automático (1 s). Cualquier gesto del dueño lo cancela. */
  function autoEnviarVoz() {
    autoEnvioCancelar();
    timerAutoEnvio = setTimeout(function () {
      timerAutoEnvio = null;
      if (ocupado) return;
      var txt = (elTexto.value || '').trim();
      if (txt) mandarTexto();
    }, AUTO_ENVIO_MS);
  }

  function autoEnvioCancelar() {
    if (timerAutoEnvio) { clearTimeout(timerAutoEnvio); timerAutoEnvio = null; }
  }

  function vozEmpezar() {
    var instancia;
    try { instancia = new Rec(); }
    catch (e) { vozAvisar('🎙️ No se pudo iniciar el micrófono en este navegador.', 'error'); return; }

    instancia.lang = IDIOMAS_VOZ[idiomaVoz] || 'es-PE';
    instancia.continuous = false;      // igual que el buscador: para cuando deja de hablar
    instancia.interimResults = true;
    instancia.maxAlternatives = 1;

    dicho = ''; interino = ''; huboErrorVoz = false; yaDictado = false;
    autoEnvioCancelar();
    elTexto.value = '';
    ajustarAlto();
    pintarBotonEnviar();
    vozOcultarAviso();

    instancia.onstart = function () {
      if (instancia !== vozActual) return;
      dictando = true;
      if (elVoz) {
        elVoz.classList.add('is-escuchando');
        elVoz.setAttribute('aria-pressed', 'true');
        elVoz.setAttribute('title', 'Escuchando… toca para terminar');
        elVoz.setAttribute('aria-label', 'Escuchando… toca para terminar');
      }
      elTexto.setAttribute('placeholder', '🎙️ Escuchando… habla ahora');
      elTexto.classList.add('voz-escuchando');
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
      var vivo = (dicho + ' ' + interino).replace(/\s+/g, ' ').trim();
      elTexto.value = vivo ? (vivo.charAt(0).toUpperCase() + vivo.slice(1)) : '';
      ajustarAlto();
      pintarBotonEnviar();
      if (fin) {
        clearTimeout(timerFinalVoz);
        timerFinalVoz = setTimeout(function () { if (instancia === vozActual) vozTerminar(); }, ESPERA_FINAL);
      }
    };

    instancia.onerror = function (ev) {
      if (instancia !== vozActual) return;
      var err = ev && ev.error ? ev.error : 'desconocido';
      if (err === 'language-not-supported' && idiomaVoz < IDIOMAS_VOZ.length - 1) {
        idiomaVoz++;
        setTimeout(vozEmpezar, 80);
        return;
      }
      huboErrorVoz = true;
      if (err === 'aborted') return;
      vozAvisar(MENSAJES_VOZ[err] || ('🎙️ No se pudo usar el micrófono (' + err + '). Escribe tu respuesta.'), 'error');
    };

    instancia.onend = function () {
      if (instancia !== vozActual) return;
      var estaba = dictando;
      var porSilencio = estaba && !vozParoManual;
      vozParoManual = false;
      vozSoltar();
      if (huboErrorVoz) return;
      if (vozTerminar()) {
        if (porSilencio) autoEnviarVoz();      // 🚀 se apagó solo: se manda al segundo
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

  /** 🎤 El botón: sin soporte del navegador NO se pinta (nunca un botón muerto), igual que el buscador. */
  function pintarVoz() { if (elVoz) elVoz.hidden = !Rec; }

  if (elVoz) {
    elVoz.addEventListener('click', function (ev) {
      ev.preventDefault();
      ev.stopPropagation();
      autoEnvioCancelar();
      if (ocupado) return;
      if (dictando) { vozParoManual = true; vozDetener(); return; }   // su dedo: para y revisa
      vozEmpezar();
    });
  }
  pintarVoz();

  /* 📷 EL MENÚ CHIQUITO DE LA CÁMARA (el mismo de la guía). */
  function camMenuAbierto() { return elCamMenu && !elCamMenu.hidden; }
  function abrirCamMenu()   { if (elCamMenu) elCamMenu.hidden = false; }
  function cerrarCamMenu()  { if (elCamMenu) elCamMenu.hidden = true; }

  if (elCam) {
    elCam.addEventListener('click', function (ev) {
      ev.stopPropagation();
      autoEnvioCancelar();
      if (ocupado) return;
      if (camMenuAbierto()) cerrarCamMenu(); else abrirCamMenu();
    });
  }
  if (elCamFoto) elCamFoto.addEventListener('click', function () { cerrarCamMenu(); if (elArchivo) elArchivo.click(); });
  if (elCamGal)  elCamGal.addEventListener('click',  function () { cerrarCamMenu(); if (elArchivoGal) elArchivoGal.click(); });
  // Tocando cualquier otra parte se cierra (es un menú, no una ventana).
  document.addEventListener('click', function (ev) {
    if (!camMenuAbierto()) return;
    var t = ev.target;
    if (elCamMenu && t && elCamMenu.contains && elCamMenu.contains(t)) return;
    if (elCam && t && (t === elCam || (elCam.contains && elCam.contains(t)))) return;
    cerrarCamMenu();
  });
  if (elEnviar) elEnviar.addEventListener('click', function () { mandarTexto(); });
  pintarBotonEnviar();

  /** Comprime las fotos UNA POR UNA (en el celular, 8 de golpe en paralelo se atragantan). */
  function optimizarTodas(files) {
    var salida = [], i = 0;
    function paso() {
      if (i >= files.length) return Promise.resolve(salida);
      var f = files[i];
      var p = (window.CZImg && CZImg.optimizar) ? CZImg.optimizar(f, { maxLado: 1600, calidad: 0.82 }) : Promise.resolve(f);
      return p.catch(function () { return f; }).then(function (opt) {
        salida.push(opt || f);
        i++;
        ayuda('Preparando tus fotos… ' + i + ' de ' + files.length);
        return paso();
      });
    }
    return paso();
  }

  /** La burbuja del dueño con las miniaturas y la nota de lo que está pasando. */
  function burbujaFotos(files, nota) {
    var urls = files.map(function (f) {
      try { return URL.createObjectURL(f); } catch (e) { return ''; }
    });
    var html = '<strong>' + (files.length === 1 ? '📷 Tu foto' : '🖼️ Tus ' + files.length + ' fotos') + '</strong>'
             + '<div class="tia-fotos tia-fotos--' + Math.min(files.length, 6) + '">';
    urls.forEach(function (u) {
      html += u ? '<img src="' + u + '" alt="Tu foto" onload="URL.revokeObjectURL(this.src)">' : '';
    });
    html += '</div><span class="tia-fotos__nota">' + esc(nota) + '</span>';
    var d = burbuja('yo', html, 'tia-msg--ancha');
    return d;
  }

  function mandarFotos(files, deDonde) {
    if (ocupado) return;
    var lista = Array.prototype.slice.call(files || []).filter(function (f) {
      return f && (!f.type || f.type.indexOf('image/') === 0);
    });
    if (!lista.length) return;
    // El servidor manda el tope real; aquí solo se evita mandar de más en un envío.
    var max = Number(CFG.max_fotos || 8);
    if (lista.length > max) { lista = lista.slice(0, max); ayuda('Te mando las primeras ' + max + ' 👌'); }

    pensando(true, 'Preparando ' + lista.length + (lista.length === 1 ? ' foto…' : ' fotos…'));
    optimizarTodas(lista).then(function (listos) {
      pensando(false);
      burbujaFotos(listos, deDonde === 'galeria' ? 'Subiendo a tu tienda…' : 'Subiendo…');
      enviar({ accion: 'responder' }, listos);
    }).catch(function () {
      pensando(false);
      burbujaFotos(lista, 'Subiendo…');
      enviar({ accion: 'responder' }, lista);
    });
  }

  elArchivo.addEventListener('change', function () {
    var f = elArchivo.files && elArchivo.files[0];
    elArchivo.value = '';
    mandarFotos(f ? [f] : [], 'camara');
  });
  if (elArchivoGal) {
    elArchivoGal.addEventListener('change', function () {
      var fs = elArchivoGal.files ? Array.prototype.slice.call(elArchivoGal.files) : [];
      elArchivoGal.value = '';
      mandarFotos(fs, 'galeria');
    });
  }

  // 🖱️ Arrastrar fotos al chat (en la computadora): el mismo camino que la galería.
  ['dragenter', 'dragover'].forEach(function (ev) {
    elChat.addEventListener(ev, function (e) { e.preventDefault(); elChat.classList.add('tia-soltando'); });
  });
  ['dragleave', 'drop'].forEach(function (ev) {
    elChat.addEventListener(ev, function (e) { e.preventDefault(); elChat.classList.remove('tia-soltando'); });
  });
  elChat.addEventListener('drop', function (e) {
    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) mandarFotos(e.dataTransfer.files, 'galeria');
  });

  /* ---------------------------------------------------------------- el menú, guardar y reiniciar */
  function cerrarMenu() { if (elMenu) elMenu.hidden = true; if (elMenuBtn) elMenuBtn.setAttribute('aria-expanded', 'false'); }
  if (elMenuBtn) {
    elMenuBtn.addEventListener('click', function (ev) {
      ev.stopPropagation();
      var abierto = elMenu && !elMenu.hidden;
      if (abierto) cerrarMenu();
      else { elMenu.hidden = false; elMenuBtn.setAttribute('aria-expanded', 'true'); }
    });
    document.addEventListener('click', function () { cerrarMenu(); });
    if (elMenu) elMenu.addEventListener('click', function (ev) { ev.stopPropagation(); });
  }

  /* 🆕 Los dos submenús en ventana emergente del menú ⋯ (2026-09-16). */
  if (elMisTiendas) elMisTiendas.addEventListener('click', function () { abrirTiendasSheet(); });
  if (elOtraTienda) elOtraTienda.addEventListener('click', function () { cerrarMenu(); abrirOtraSheet(); });

  elGuardar.addEventListener('click', function () {
    cerrarMenu();
    if (ocupado) return;
    fetch(CFG.api, {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'salir', csrf: CFG.csrf, modo: CFG.modo || 'nueva' })
    }).then(function (r) { return r.json(); }).then(function (j) {
      burbuja('bot', formato(j.mensaje || 'Listo, guardé tu avance 👍'));
    }).catch(function () { errorAmable('No pude guardar ahora mismo, pero tu avance se guarda solo en cada paso.'); });
  });

  elReiniciar.addEventListener('click', function () {
    cerrarMenu();
    if (ocupado) return;
    if (!window.confirm('¿Empezar de nuevo desde el principio? Lo que llevas de esta tienda se borra (nada se publicó todavía).')) return;
    fetch(CFG.api, {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'reiniciar', csrf: CFG.csrf, modo: CFG.modo || 'nueva' })
    }).then(function () { window.location.reload(); });
  });

  /* ---------------------------------------------------------------- arranque */
  // 🛒🆕 `prod` = el producto que ya viene dicho por el botón «Agregar mi tienda» del buscador (puede
  // no venir: entonces el asistente pregunta el nombre del producto, como siempre).
  var url = CFG.api + '?modo=' + encodeURIComponent(CFG.modo || 'nueva')
          + (CFG.producto ? '&prod=' + encodeURIComponent(CFG.producto) : '')
          + '&_=' + Date.now();
  pensando(true, 'Abriendo tu conversación…');
  fetch(url, { credentials: 'same-origin' })
    .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
    .then(function (res) {
      pensando(false);
      var j = res.j || {};
      if (j.error === 'sin_sesion') { window.location.href = j.login_url || (CFG.sitio + '/login.php'); return; }
      if (!j.ok) { errorAmable(j.mensaje || 'No pude abrir la conversación. Recarga la página.'); return; }
      pintarMensajes(j.mensajes);
      pintarPaso(j);
      // El micrófono aparece en cuanto el campo existe (dictado_voz.js lo pinta solo)
      if (window.ChimboteDictado && ChimboteDictado.engancharTodos) ChimboteDictado.engancharTodos(raiz);
      abajo(false);
    })
    .catch(function () { pensando(false); errorAmable('No pude abrir la conversación. Recarga la página, por favor.'); });
})();
