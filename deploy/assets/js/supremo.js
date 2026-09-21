/* =====================================================================================
 * assets/js/supremo.js — 👑 LA VENTA EN VIVO DE «EL SUPREMO»
 * =====================================================================================
 * Qué hace: pinta la conversación de `/supremo` y habla con `api/supremo.php`.
 *   · El paso lo manda el SERVIDOR: aquí solo se pinta lo que llega (tipo, opciones…).
 *   · ⏱️ El CRONÓMETRO de la venta va en la cabecera (la meta son 4-5 minutos).
 *   · 👑 EL PANEL DE LOS CÓDIGOS: las tarjetas con su botón de COPIAR. Se abre solo al llegar al
 *     paso de los códigos y también con el botón dorado de la cabecera. Es lo que el jefe pega en
 *     Flow/Gemini delante del cliente: **un clic copia el texto entero** (regla inviolable n.º 1
 *     del proyecto: nunca se le pide seleccionar un pedazo de la conversación).
 *   · 🛍️ LA REJILLA DE PRODUCTOS: se marcan los que van (con la foto donde la IA los vio).
 *   · 📥 LA SALA DE ESPERA: se suben las imágenes del diseñador todas de una vez y el servidor las
 *     reparte leyendo el ID impreso dentro de cada foto.
 *   · 📷 Igual que El maestro: la GALERÍA manda varias fotos en un envío y se comprimen antes.
 *
 * Guía: GUIA_EL_SUPREMO.md
 * ===================================================================================== */
(function () {
  'use strict';

  var raiz = document.getElementById('sup');
  if (!raiz) return;

  var CFG = window.SUP_CFG || {};
  var elChat       = document.getElementById('supChat');
  var elOpciones   = document.getElementById('supOpciones');
  var elTexto      = document.getElementById('supTexto');
  var elPaso       = document.getElementById('supPaso');
  var elBarra      = document.getElementById('supBarra');
  var elAyuda      = document.getElementById('supAyuda');
  var elFotoBtns   = document.getElementById('supFotoBotones');
  var elCamara     = document.getElementById('supCamara');
  var elGaleria    = document.getElementById('supGaleria');
  var elArchivo    = document.getElementById('supArchivo');
  var elArchivoGal = document.getElementById('supArchivoGaleria');
  var elMulti      = document.getElementById('supMulti');
  var elLlegaron   = document.getElementById('supLlegaron');
  var elRelojT     = document.getElementById('supRelojT');
  var elReloj      = document.getElementById('supReloj');
  var elBtnCodigos = document.getElementById('supBtnCodigos');
  var elSheetCod   = document.getElementById('supSheetCodigos');
  var elCodLista   = document.getElementById('supCodigosLista');
  var elCodNota    = document.getElementById('supCodigosNota');
  // 💰 La oferta: el botón de la cabecera y su panel con la calculadora.
  var elBtnOferta  = document.getElementById('supBtnOferta');
  var elSheetOf    = document.getElementById('supSheetOferta');
  // 🎵 La canción: su botón, su panel, el reproductor y la entrada del audio.
  var elBtnCancion = document.getElementById('supBtnCancion');
  var elSheetCan   = document.getElementById('supSheetCancion');
  var elAudio      = document.getElementById('supAudio');
  var elMenu       = document.getElementById('supMenu');
  var elMenuBtn    = document.getElementById('supMenuBtn');
  // 🧠 «Piensa mejor»: su botón de la cabecera y su entrada del menú.
  var elBtnPiensa  = document.getElementById('supBtnPiensa');
  var elVerPiensa  = document.getElementById('supVerPiensa');
  // 📍 Compartir la ubicación: su entrada del menú (está SIEMPRE disponible, en cualquier paso).
  var elVerUbic    = document.getElementById('supVerUbicacion');

  var elMic      = document.getElementById('supEnviar');
  var elClip     = document.getElementById('supClip');
  var elCamBtn   = document.getElementById('supCam');
  var elEmojiBtn = document.getElementById('supEmoji');
  var elEmojiPnl = document.getElementById('supEmojiPanel');
  var micIco  = elMic ? elMic.querySelector('.tia-wa__mic-ico') : null;
  var sendIco = elMic ? elMic.querySelector('.tia-wa__mic-enviar') : null;

  var ocupado  = false;
  var tipEl    = null;
  var ultimo   = null;     // la última respuesta del servidor
  var marcados = {};       // 🛍️ los índices de productos marcados en la rejilla
  var segBase  = 0;        // ⏱️ los segundos que ya llevaba la venta según el servidor
  var segReloj = 0;
  var relojId  = null;
  var codigos  = [];
  var codFirma = '';   // la firma de lo último pintado (para no repintar y no perder el prompt delante)
  var dictando = false, dictRec = null, dictBase = '', dictTimer = null;

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

  function abajo(suave) {
    if (!elChat) return;
    try { elChat.scrollTo({ top: elChat.scrollHeight, behavior: suave === false ? 'auto' : 'smooth' }); }
    catch (e) { elChat.scrollTop = elChat.scrollHeight; }
  }

  function burbuja(rol, html, extra) {
    var d = document.createElement('div');
    d.className = 'tia-msg tia-msg--' + (rol === 'yo' ? 'yo' : 'bot') + (extra ? ' ' + extra : '');
    var av = (rol === 'yo') ? '' : '<span class="tia-msg__av" aria-hidden="true">' + (CFG.emoji || '👑') + '</span>';
    d.innerHTML = av + '<div class="tia-msg__burbuja">' + html + '</div>';
    elChat.appendChild(d);
    abajo();
    return d;
  }

  function pintarMensajes(lista) {
    (lista || []).forEach(function (m) {
      var rol = (m.rol === 'yo' || m.rol === 'user') ? 'yo' : 'bot';
      var d = burbuja(rol, formato(m.texto));
      // 🔴 Se marca la burbuja con su FIRMA. Así, cuando después se comprueba si la pregunta del
      //    paso ya está a la vista (`asegurarPregunta`), se sabe con certeza y **nunca se duplica**
      //    (comparar textos sería frágil: el markdown no es igual al texto ya pintado).
      if (d && d.setAttribute && rol === 'bot') d.setAttribute('data-pregunta', firma(m.texto));
    });
  }

  /** La firma de un texto: sin símbolos, sin espacios y en minúsculas, para comparar de verdad. */
  function firma(txt) {
    return String(txt == null ? '' : txt).toLowerCase()
      .replace(/[^0-9a-záéíóúüñ]+/g, '')
      .slice(0, 60);
  }

  /**
   * 🔴 LA PREGUNTA DEL PASO, SIEMPRE A LA VISTA (bicho del 2026-09-18).
   * Lo que dijo el jefe: *«parece que me hiciera preguntas y solo veo las opciones de respuesta
   * pero no las preguntas»*. El servidor manda los mensajes nuevos de cada respuesta, pero **hay
   * caminos del motor que devuelven solo las opciones** (sin texto). Si por eso el chat se quedaba
   * sin la pregunta, aquí se pinta la del paso —que el servidor manda siempre en `pregunta`—.
   * Va marcada con su firma, así que **no se repite** aunque el mensaje ya esté.
   */
  function asegurarPregunta(r) {
    var obj = firma(r.pregunta);
    if (obj === '' || !elChat) return false;
    var hijos = elChat.childNodes || [];
    for (var i = 0; i < hijos.length; i++) {
      var h = hijos[i];
      var marca = (h && h.getAttribute) ? String(h.getAttribute('data-pregunta') || '') : '';
      if (marca === '') continue;
      // Ya está a la vista: o es la misma, o una empieza como la otra (el servidor a veces manda la
      // versión corta del mismo texto). Se comparan los caracteres que ambos tienen en común, con un
      // mínimo de 12 para no confundir dos preguntas distintas (los pasos empiezan todos distinto).
      var n = Math.min(marca.length, obj.length, 40);
      if (n >= 12 && marca.slice(0, n) === obj.slice(0, n)) return false;
    }
    var d = burbuja('bot', formato(String(r.pregunta)));
    if (d && d.setAttribute) d.setAttribute('data-pregunta', obj);
    return true;
  }

  function ayuda(txt) { if (elAyuda) elAyuda.textContent = txt || ''; }

  function pensando(si, frase) {
    ocupado = !!si;
    if (elMic) elMic.disabled = !!si;
    if (elCamara)  elCamara.disabled  = !!si;
    if (elGaleria) elGaleria.disabled = !!si;
    if (!si) { if (tipEl) { tipEl.remove(); tipEl = null; } return; }
    if (!tipEl) {
      tipEl = document.createElement('div');
      tipEl.className = 'tia-msg tia-msg--bot';
      tipEl.innerHTML = '<span class="tia-msg__av" aria-hidden="true">' + (CFG.emoji || '👑') + '</span>'
                      + '<div class="tia-msg__burbuja tia-tip"><span></span><span></span><span></span></div>';
      elChat.appendChild(tipEl);
    }
    abajo();
    if (frase) ayuda(frase);
  }

  function errorAmable(txt) {
    burbuja('bot', '😅 ' + formato(txt));
  }

  /* ---------------------------------------------------------------- ⏱️ el cronómetro */
  function pintarReloj() {
    if (!elRelojT) return;
    var m = Math.floor(segReloj / 60), s = segReloj % 60;
    elRelojT.textContent = m + ':' + (s < 10 ? '0' : '') + s;
    if (!elReloj) return;
    var obj = Number(CFG.objetivo_seg || 300);
    elReloj.classList.toggle('is-ok', segReloj <= obj);
    elReloj.classList.toggle('is-aviso', segReloj > obj && segReloj <= obj * 1.5);
    elReloj.classList.toggle('is-pasado', segReloj > obj * 1.5);
  }

  function arrancarReloj() {
    if (relojId) return;
    relojId = setInterval(function () { segReloj++; pintarReloj(); }, 1000);
  }

  /* ---------------------------------------------------------------- 👑 EL PANEL DE LOS CÓDIGOS */
  function copiar(texto, boton) {
    var listo = function () {
      if (!boton) return;
      var antes = boton.textContent;
      boton.textContent = '✅ Copiado';
      boton.classList.add('is-ok');
      setTimeout(function () { boton.textContent = antes; boton.classList.remove('is-ok'); }, 1600);
    };
    // 1) La forma moderna (necesita https o localhost).
    if (navigator.clipboard && navigator.clipboard.writeText) {
      navigator.clipboard.writeText(texto).then(listo).catch(function () { copiarViejo(texto, listo); });
      return;
    }
    copiarViejo(texto, listo);
  }

  /** El respaldo de siempre: un campo escondido y `execCommand`. */
  function copiarViejo(texto, listo) {
    try {
      var ta = document.createElement('textarea');
      ta.value = texto;
      ta.setAttribute('readonly', 'readonly');
      ta.style.position = 'fixed';
      ta.style.top = '-1000px';
      document.body.appendChild(ta);
      ta.select();
      ta.setSelectionRange(0, ta.value.length);
      var ok = document.execCommand('copy');
      document.body.removeChild(ta);
      if (ok) listo(); else errorAmable('No pude copiar solo. Mantén tocado el texto y copia a mano.');
    } catch (e) {
      errorAmable('No pude copiar solo. Mantén tocado el texto y copia a mano.');
    }
  }

  /** Pinta las tarjetas de los códigos (una por código) dentro del panel. */
  function pintarCodigos(lista) {
    codigos = Array.isArray(lista) ? lista : [];
    if (elBtnCodigos) elBtnCodigos.hidden = codigos.length === 0;
    if (!elCodLista) return;

    // 🚫 Si los códigos son LOS MISMOS que ya están pintados, no se vuelve a pintar: si no, el panel
    //    se refrescaría mientras el jefe lo está leyendo y perdería el prompt que tenía delante.
    var firma = codigos.map(function (c) {
      return (c.clave || '') + ':' + String(c.texto || '').length + ':' + ((c.piezas || []).length);
    }).join('|');
    if (firma === codFirma && elCodLista.childNodes.length) return;
    codFirma = firma;

    elCodLista.innerHTML = '';
    if (!codigos.length) {
      elCodLista.innerHTML = '<p class="sup-codigos__vacio">Todavía no hay códigos: aparecen en cuanto la '
        + 'tienda queda publicada (el de las imágenes necesita que existan los productos).</p>';
      return;
    }
    codigos.forEach(function (c, i) {
      var util = (c.texto || '').trim() !== '';
      var piezas = (c.piezas || []).filter(function (x) { return x && String(x.texto || '').trim() !== ''; });
      var card = document.createElement('div');
      card.className = 'sup-cod' + (util ? '' : ' sup-cod--vacio');
      var html = '<div class="sup-cod__cabeza">'
               + '<span class="sup-cod__emoji" aria-hidden="true">' + esc(c.emoji || '👑') + '</span>'
               + '<span class="sup-cod__tit">' + esc(c.titulo || ('Código ' + (i + 1))) + '</span>'
               + (util ? '<button type="button" class="sup-cod__copiar" data-i="' + i + '" data-pieza="0">📋 Copiar</button>' : '')
               + '</div>';
      if (c.como) html += '<p class="sup-cod__como">' + esc(c.como) + '</p>';

      // 📲 EL BOTÓN DE WHATSAPP PARA EL CLIENTE (orden del jefe, 2026-09-18): abre SU chat con el
      //    mensaje ya escrito (su enlace, su usuario y su clave). Solo hay que darle enviar.
      //    Debajo queda el texto, por si prefiere copiarlo.
      if (String(c.clave) === 'cliente' && c.wa) {
        html += '<a class="sup-wa" href="' + esc(c.wa) + '" target="_blank" rel="noopener">'
              + '<span class="sup-wa__ico">' + (CFG.wa_icono || '💬') + '</span>'
              + '<span class="sup-wa__txt"><strong>' + esc(c.wa_texto || 'Mandarle los datos por WhatsApp') + '</strong>'
              + '<small>Se abre WhatsApp con el mensaje ya escrito: solo le das enviar</small></span></a>';
        html += '<p class="sup-cod__como sup-cod__como--o">O copia el mensaje y mándalo tú:</p>';
        html += '<pre class="sup-cod__texto">' + esc(c.texto) + '</pre>';
        card.innerHTML = html;
        elCodLista.appendChild(card);
        return;
      }

      if (piezas.length > 1) {
        // 📱📋 DE UNO EN UNO (lo que pidió el jefe desde el celular): navegador ‹ › y un prompt
        //    por pantalla, con sus puntos para ver cuáles ya se copiaron.
        html += '<div class="sup-pieza" data-cod="' + i + '">'
              +   '<div class="sup-pieza__nav">'
              +     '<button type="button" class="sup-pieza__flecha" data-mover="' + i + '|-1" aria-label="Prompt anterior">‹</button>'
              +     '<span class="sup-pieza__quien"><b class="sup-pieza__tit">' + esc(piezas[0].titulo || '') + '</b>'
              +       '<small class="sup-pieza__nota">' + esc(piezas[0].nota || '') + '</small></span>'
              +     '<button type="button" class="sup-pieza__flecha" data-mover="' + i + '|1" aria-label="Prompt siguiente">›</button>'
              +   '</div>'
              +   '<pre class="sup-cod__texto" data-pieza-texto="' + i + '">' + esc(piezas[0].texto) + '</pre>'
              +   '<div class="sup-pieza__abajo">'
              +     '<button type="button" class="sup-cod__copiar sup-cod__copiar--ancho" data-i="' + i + '" data-pieza="1">📋 Copiar este prompt</button>'
              +     '<button type="button" class="sup-pieza__sig" data-mover="' + i + '|1">Siguiente producto ›</button>'
              +   '</div>'
              +   '<div class="sup-pieza__puntos" data-puntos="' + i + '">'
              +     piezas.map(function (p, k) {
                      return '<button type="button" class="sup-punto" data-mover="' + i + '|' + k + '" '
                           + 'title="' + esc(p.titulo || ('Prompt ' + (k + 1))) + '">' + (k + 1) + '</button>';
                    }).join('')
              +   '</div>'
              + '</div>';
        if ((c.todos || '').trim() !== '') {
          html += '<button type="button" class="sup-pieza__todos" data-i="' + i + '" data-pieza="2">'
                + '📋 Copiar los ' + piezas.length + ' juntos (carta completa)</button>';
        }
      } else {
        html += util
          ? '<pre class="sup-cod__texto" data-i="' + i + '">' + esc(c.texto) + '</pre>'
          : '<p class="sup-cod__vacio">Todavía no se puede armar este código.</p>';
      }
      if (util && String(c.clave) === 'cabecera') {
        html += '<a class="sup-cod__extra" href="' + esc(CFG.gemini || '#') + '" target="_blank" rel="noopener">'
              + '🌀 Abrir Gemini (y adjúntale tu imagen de referencia)</a>';
      }
      card.innerHTML = html;
      elCodLista.appendChild(card);
    });

    // El estado de cada tarjeta (en qué prompt va y cuáles ya copió) se recupera de `codEstado`.
    codigos.forEach(function (c, i) {
      var piezas = (c.piezas || []).filter(function (x) { return x && String(x.texto || '').trim() !== ''; });
      if (piezas.length > 1) pintarPieza(i, piezas);
    });

    Array.prototype.forEach.call(elCodLista.querySelectorAll('.sup-cod__copiar'), function (b) {
      b.addEventListener('click', function () {
        var i = Number(b.getAttribute('data-i'));
        var cual = Number(b.getAttribute('data-pieza'));
        var c = codigos[i] || {};
        var piezas = (c.piezas || []).filter(function (x) { return x && String(x.texto || '').trim() !== ''; });
        if (cual === 2) {                       // 🖥️ la carta completa
          copiar(String(c.todos || c.texto || ''), b);
          return;
        }
        if (piezas.length > 1) {
          var k = estadoDe(i).i;
          // 📱 Se copia sin tocar el botón: el ✅ lo pinta `pintarPieza` junto con el punto verde.
          copiar(String(piezas[k].texto || ''), null);
          var e = estadoDe(i);
          if (e.copiadas.indexOf(k) < 0) e.copiadas.push(k);
          pintarPieza(i, piezas);
          return;
        }
        copiar(String(c.texto || ''), b);
      });
    });
    Array.prototype.forEach.call(elCodLista.querySelectorAll('[data-mover]'), function (b) {
      b.addEventListener('click', function () {
        var par = String(b.getAttribute('data-mover')).split('|');
        var i = Number(par[0]), salto = Number(par[1]);
        var c = codigos[i] || {};
        var piezas = (c.piezas || []).filter(function (x) { return x && String(x.texto || '').trim() !== ''; });
        if (!piezas.length) return;
        var e = estadoDe(i);
        e.i = (salto === -1) ? Math.max(0, e.i - 1)
            : (salto === 1)  ? Math.min(piezas.length - 1, e.i + 1)
            : Math.max(0, Math.min(piezas.length - 1, salto));
        pintarPieza(i, piezas);
      });
    });
  }

  /* 📱 El estado de cada tarjeta con piezas: en qué prompt va y cuáles ya copió. */
  var codEstado = {};
  function estadoDe(i) {
    if (!codEstado[i]) codEstado[i] = { i: 0, copiadas: [] };
    return codEstado[i];
  }

  /** Pinta el prompt que toca (y deja claro en qué número va y cuáles ya se copiaron). */
  function pintarPieza(i, piezas) {
    var e = estadoDe(i);
    if (e.i >= piezas.length) e.i = 0;
    var p = piezas[e.i];
    var bloque = elCodLista.querySelector('.sup-pieza[data-cod="' + i + '"]');
    if (!bloque) return;
    var t = bloque.querySelector('[data-pieza-texto]');
    if (t) t.textContent = String(p.texto || '');
    var tit = bloque.querySelector('.sup-pieza__tit');
    if (tit) tit.textContent = String(p.titulo || '');
    var nota = bloque.querySelector('.sup-pieza__nota');
    if (nota) nota.textContent = String(p.nota || '');
    var flechaAnt = bloque.querySelector('[data-mover$="|-1"]');
    var flechaSig = bloque.querySelector('[data-mover$="|1"]');
    if (flechaAnt) flechaAnt.disabled = (e.i === 0);
    if (flechaSig) flechaSig.disabled = (e.i === piezas.length - 1);
    var sig = bloque.querySelector('.sup-pieza__sig');
    if (sig) sig.textContent = (e.i === piezas.length - 1) ? 'Ya es el último ✓' : 'Siguiente producto ›';
    var boton = bloque.querySelector('.sup-cod__copiar[data-pieza="1"]');
    if (boton) {
      var ya = e.copiadas.indexOf(e.i) >= 0;
      boton.textContent = ya ? '✅ Copiado' : '📋 Copiar este prompt';
      boton.classList.toggle('is-ok', ya);
    }
    Array.prototype.forEach.call(bloque.querySelectorAll('.sup-punto'), function (pt, k) {
      pt.classList.toggle('is-actual', k === e.i);
      pt.classList.toggle('is-copiado', e.copiadas.indexOf(k) >= 0);
    });
  }

  function abrirCodigos() {
    if (elCodNota) {
      elCodNota.innerHTML = 'Copia cada uno con su botón y pégalo donde dice. El de la cabecera necesita que le '
        + 'adjuntes una <b>imagen de referencia</b> (de esa imagen se copia solo el estilo, nunca su contenido).';
    }
    if (elSheetCod) elSheetCod.hidden = false;
  }
  function cerrarCodigos() { if (elSheetCod) elSheetCod.hidden = true; }

  /* ---------------------------------------------------------------- 🛍️ LA REJILLA DE PRODUCTOS */
  function pintarMulti(items) {
    if (!elMulti) return;
    if (!items || !items.length) { elMulti.hidden = true; elMulti.innerHTML = ''; return; }
    elMulti.hidden = false;
    elMulti.innerHTML = '<p class="sup-multi__tit">🛍️ Toca los productos que <b>sí</b> van a la tienda '
                      + '<span class="sup-multi__n" id="supMultiN"></span></p><div class="sup-multi__rejilla"></div>';
    var rej = elMulti.querySelector('.sup-multi__rejilla');
    items.forEach(function (it) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'sup-item';
      b.setAttribute('data-i', String(it.indice));
      b.innerHTML = (it.foto ? '<img src="' + esc(it.foto) + '" alt="" loading="lazy">' : '<span class="sup-item__sin">📦</span>')
                  + '<span class="sup-item__t">' + esc(it.titulo) + '</span><span class="sup-item__chk" aria-hidden="true">＋</span>';
      b.addEventListener('click', function () {
        var k = String(it.indice);
        if (marcados[k]) { delete marcados[k]; b.classList.remove('is-ok'); b.querySelector('.sup-item__chk').textContent = '＋'; }
        else { marcados[k] = 1; b.classList.add('is-ok'); b.querySelector('.sup-item__chk').textContent = '✓'; }
        var n = document.getElementById('supMultiN');
        if (n) n.textContent = '(' + Object.keys(marcados).length + ' marcados)';
      });
      rej.appendChild(b);
    });
    var n0 = document.getElementById('supMultiN');
    if (n0) n0.textContent = '(' + Object.keys(marcados).length + ' marcados)';
  }

  /* ---------------------------------------------------------------- 📥 LO QUE YA LLEGÓ */
  function pintarLlegaron(puestas) {
    if (!elLlegaron) return;
    if (!puestas || !puestas.length) { elLlegaron.hidden = true; elLlegaron.innerHTML = ''; return; }
    elLlegaron.hidden = false;
    var html = '<p class="sup-llegaron__tit">✅ Ya entraron <b>' + puestas.length + '</b> imagen(es)</p><div class="sup-llegaron__tira">';
    puestas.forEach(function (p) {
      var u = String(p.archivo || '');
      u = (u.indexOf('http') === 0) ? u : ((CFG.sitio || '') + '/' + u.replace(/^\//, ''));
      html += '<span class="sup-llegaron__foto"><img src="' + esc(u) + '" alt="" loading="lazy">'
            + '<em>' + esc(p.que === 'portada' ? '🖼️ Portada' : p.titulo) + '</em></span>';
    });
    html += '</div>';
    elLlegaron.innerHTML = html;
  }

  /* ---------------------------------------------------------------- 📷 LA TIRA DE FOTOS */
  function pintarTiraFotos(fotos, paso) {
    if (!fotos || !fotos.length) return;
    var tit = (paso === 'sup_portada_fotos') ? 'Toca la foto que quieres de portada 👇'
                                             : 'Las fotos del negocio 👇';
    var html = '<div class="tia-slide"><p class="tia-slide__tit">' + tit + '</p><div class="tia-slide__tira">';
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

  /* ---------------------------------------------------------------- ⬇️ pintar el paso */
  function pintarPaso(r) {
    ultimo = r;
    // 🔴 PRIMERO DE TODO: que la pregunta del paso esté a la vista (nunca botones sin pregunta).
    asegurarPregunta(r);
    if (elPaso) elPaso.textContent = r.paso_titulo || 'Modo vendedor';
    if (elBarra) elBarra.style.width = Math.round((r.progreso || 0) * 100) + '%';

    // ⏱️ El cronómetro: el servidor manda los segundos reales de la venta.
    segBase  = Number(r.segundos || 0);
    if (segBase > 0) { segReloj = segBase; arrancarReloj(); pintarReloj(); }

    // 👑 Los códigos: se pintan siempre que lleguen (la cabecera tiene su botón).
    if (r.codigos) pintarCodigos(r.codigos);

    // 💰 La oferta: los textos, los chips del catálogo y el panel cuando se pide.
    if (r.oferta) {
      ofertaDatos = r.oferta;
      pintarOfertaTextos(r.oferta);
      pintarOfertaChips(r.oferta, r.productos || []);
      var campo = document.getElementById('supCalcPrecio');
      if (campo && !Number(campo.value) && Number(r.oferta.precio) > 0) { campo.value = r.oferta.precio; }
      pintarCalculo();
      if (r.oferta_panel) setTimeout(abrirOferta, 300);
    }

    // 🎵 La canción: si la tienda ya la tiene (o se acaba de subir), se pinta con su reproductor.
    if (r.cancion !== undefined) pintarCancion(r.cancion);
    if (elBtnCancion) elBtnCancion.hidden = !(ultimo && ultimo.publicado);

    // 🛍️ La rejilla de productos: la de los que se incluyen y la de los que llevan imagen de la IA.
    if (r.paso === 'sup_productos' || r.paso === 'sup_ia_elegir') {
      pintarMulti(r.items || []);
    } else {
      pintarMulti([]);
      marcados = {};
    }
    if (r.paso === 'sup_ia_elegir' && elMulti && !elMulti.hidden) {
      var t = elMulti.querySelector('.sup-multi__tit');
      if (t) t.innerHTML = '🖼️ Toca los productos que quieras <b>con imagen de la IA</b> '
                         + '<span class="sup-multi__n" id="supMultiN"></span>';
    }

    // 📥 Lo que ya llegó a la sala de espera.
    if (r.paso === 'sup_imagenes' || r.paso === 'sup_fin') pintarLlegaron(r.imagenes_puestas || []);
    else pintarLlegaron([]);

    // Los botones del paso
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
      b.className = 'tia-chip' + (esObj && o.principal ? ' tia-chip--principal' : '') + (esObj && o.azul ? ' tia-chip--azul' : '');
      b.innerHTML = esc(texto) + (nota ? ' <em>' + esc(nota) + '</em>' : '');
      if (esObj && o.rejilla && !esEnlace) {
        if (!grid) { grid = document.createElement('div'); grid.className = 'tia-opciones--rejilla'; elOpciones.appendChild(grid); }
        b.style.justifyContent = 'center';
        b.style.textAlign = 'center';
        grid.appendChild(b);
      } else {
        elOpciones.appendChild(b);
      }
      if (esEnlace) {
        b.addEventListener('click', function () { burbuja('yo', esc(texto)); });
      } else {
        b.addEventListener('click', function () {
          tocarOpcion({ texto: limpio, valor: esObj ? String(o.valor == null ? texto : o.valor) : texto });
        });
      }
    });

    // La barra de escribir y los botones de foto según el tipo de paso
    // (⚠️ `tipo` se lee PRIMERO: es el del servidor, y de él dependen los tres bloques de abajo)
    var tipo = r.tipo || 'texto';
    var pideFoto  = (tipo === 'foto');
    var pideAudio = (tipo === 'audio');
    if (elFotoBtns) elFotoBtns.hidden = !pideFoto;
    if (elClip)   elClip.hidden   = !pideFoto;
    if (elCamBtn) elCamBtn.hidden = !pideFoto;
    if (!pideFoto && elEmojiPnl) elEmojiPnl.hidden = true;
    ayuda('');
    // 🎵 El paso de la canción: aquí no se piden fotos, se pide el AUDIO que bajó de Flow.
    if (pideAudio) {
      ayuda('Toca el botón 🎵 de la cabecera (o el de abajo) y elige el audio que bajaste de Flow · hasta '
            + (CFG.audio_max_mb || 8) + ' MB');
      elTexto.placeholder = 'Escribe si quieres… (lo importante es el audio 🎵)';
    }
    if (pideFoto) {
      var n = Number(r.n_fotos || 0);
      elTexto.placeholder = 'Escribe si quieres… (lo importante son las fotos 📷)';
      if (r.paso === 'sup_imagenes') {
        ayuda('Toca «🖼️ Abrir galería» y elige TODAS las imágenes que te dio la IA (hasta ' + (CFG.lote_max || 12) + ' de una vez)');
      } else if (r.paso === 'fotos') {
        ayuda(n > 0 ? ('Llevas ' + n + ' de ' + (r.min_fotos || CFG.min_fotos || 3) + ' fotos que hacen falta')
                    : ('Toca «🖼️ Abrir galería» y elige las fotos del negocio de una vez'));
      } else {
        ayuda('Toca «' + (r.texto_galeria || '🖼️ Abrir galería') + '» para mandar la foto');
      }
    } else if (r.paso === 'sup_productos') {
      elTexto.placeholder = 'Marca arriba los que van, o escribe…';
    } else {
      elTexto.placeholder = 'Escribe o habla 🎙️';
    }

    // 📷 La tira con las fotos que ya subió (para elegir la portada).
    if (r.paso === 'sup_portada_fotos' && r.fotos && r.fotos.length) pintarTiraFotos(r.fotos, r.paso);

    // 👑 Al llegar al paso de los códigos, el panel se abre SOLO (es lo que hay que pegar en Flow).
    if (r.paso === 'sup_codigos') setTimeout(abrirCodigos, 450);

    setTimeout(function () { abajo(); }, 60);
  }

  /* ---------------------------------------------------------------- 🗣️ hablar con el servidor */
  function enviar(datos, lote) {
    if (ocupado) return;
    pensando(true, lote ? 'Subiendo las fotos…' : '');
    var opciones = { method: 'POST', credentials: 'same-origin' };
    var cuerpo;
    if (lote && lote.length) {
      cuerpo = new FormData();
      cuerpo.append('accion', 'responder');
      cuerpo.append('csrf', CFG.csrf);
      cuerpo.append('tipo', 'foto');
      lote.forEach(function (f) { cuerpo.append('foto[]', f, f.name || 'foto.jpg'); });
    } else {
      opciones.headers = { 'Content-Type': 'application/json' };
      datos.csrf = CFG.csrf;
      cuerpo = JSON.stringify(datos);
    }
    opciones.body = cuerpo;

    fetch(CFG.api, opciones)
      .then(function (r) { return r.json().then(function (j) { return { ok: r.ok, j: j }; }); })
      .then(function (res) {
        pensando(false);
        var j = res.j || {};
        if (j.error === 'no_admin') { window.location.href = j.login_url || (CFG.sitio + '/login.php'); return; }
        if (!j.ok) { errorAmable(j.mensaje || 'No pude seguir. Prueba otra vez.'); return; }
        if (j.reiniciado) { window.location.reload(); return; }
        if (j.nueva_venta) { window.location.reload(); return; }
        pintarMensajes(j.mensajes);
        if ((!j.mensajes || j.mensajes.length === 0) && j.mensaje) burbuja('bot', formato(j.mensaje));
        pintarPaso(j);
      })
      .catch(function () {
        pensando(false);
        errorAmable('Se cortó el internet. Revisa tu señal y toca el botón otra vez.');
      });
  }

  function tocarOpcion(o) {
    if (!o || ocupado) return;
    if (o.url) { burbuja('yo', esc(o.texto)); window.open(o.url, '_blank', 'noopener'); return; }
    if (o.valor === 'camara') { if (elArchivo) elArchivo.click(); return; }
    if (o.valor === 'galeria') { burbuja('yo', esc(o.texto)); if (elArchivoGal) elArchivoGal.click(); return; }
    if (o.valor === 'gps')    { burbuja('yo', esc(o.texto)); pedirUbicacion(); return; }
    // 🎵 «Clic aquí para cargar la canción»: se abre el selector de audio ahí mismo (orden del jefe).
    if (o.valor === 'cargar_cancion') {
      burbuja('yo', esc(o.texto));
      if (elAudio) elAudio.click(); else if (elBtnCancion) elBtnCancion.click();
      return;
    }

    // 🛍️🖼️ En las rejillas de productos, el botón de seguir viaja con los índices marcados.
    var valor = String(o.valor == null ? '' : o.valor);
    var enRejilla = ultimo && (ultimo.paso === 'sup_productos' || ultimo.paso === 'sup_ia_elegir');
    if (enRejilla && valor === 'seguir') {
      valor = 'incluir:' + Object.keys(marcados).join(',');
      burbuja('yo', 'Los ' + Object.keys(marcados).length + ' marcados');
    } else {
      burbuja('yo', esc(o.texto));
    }
    // ✏️ «Escribir»: el cursor se va SOLO al área de escribir (orden del jefe, 2026-09-18: *«si presiona
    //    escribir automáticamente debe escribir en el área de escribir texto»*).
    if (valor === 'escribir' && elTexto) { elTexto.focus(); }
    enviar({ accion: 'responder', tipo: 'opcion', valor: valor, texto: String(o.texto || '') });
  }

  function pedirUbicacion() {
    if (!navigator.geolocation) {
      errorAmable('Tu navegador no me deja ver la ubicación. Toca el distrito de la lista 👇');
      return;
    }
    pensando(true, 'Buscando la zona…');
    navigator.geolocation.getCurrentPosition(function (pos) {
      pensando(false);
      enviar({ accion: 'responder', tipo: 'gps', valor: 'gps', lat: pos.coords.latitude, lng: pos.coords.longitude });
    }, function () {
      pensando(false);
      errorAmable('No me diste permiso para la ubicación. Toca el distrito de la lista 👇');
    }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 60000 });
  }

  /* 📍 COMPARTIR LA UBICACIÓN EN CUALQUIER MOMENTO (menú ⋯).
     No mueve la venta de paso: solo pone (o corrige) dónde está la tienda. Si el GPS falla o el
     navegador no da permiso, se abre el paso de la zona con su botón y los distritos en tarjetas. */
  function compartirUbicacion() {
    if (ocupado) return;
    burbuja('yo', '📍 Compartir ubicación');
    if (!navigator.geolocation) { irAZona(); return; }
    pensando(true, 'Buscando la ubicación…');
    navigator.geolocation.getCurrentPosition(function (pos) {
      pensando(false);
      enviar({ accion: 'ubicacion', lat: pos.coords.latitude, lng: pos.coords.longitude });
    }, function () {
      pensando(false);
      errorAmable('No me diste permiso para la ubicación. Toca el distrito de la lista 👇');
      irAZona();
    }, { enableHighAccuracy: true, timeout: 12000, maximumAge: 30000 });
  }

  /* 📍 Abrir «cambiar la zona» a mano (el GPS no siempre da). Al elegir, la venta vuelve a su paso. */
  function irAZona() {
    if (ocupado) return;
    enviar({ accion: 'zona' });
  }

  /* 🧠 PIENSA MEJOR: se abre desde cualquier punto y, al terminar, la venta sigue donde estaba. */
  function abrirPiensa() {
    if (ocupado) return;
    burbuja('yo', '🧠 Piensa mejor');
    enviar({ accion: 'piensa' });
  }

  /* ---------------------------------------------------------------- escribir y enviar */
  function ajustarAlto() {
    elTexto.style.height = 'auto';
    elTexto.style.height = Math.min(elTexto.scrollHeight, 140) + 'px';
  }

  function hayTexto() { return (elTexto.value || '').trim() !== ''; }

  function pintarBotonWa() {
    if (!elMic) return;
    var enviar = hayTexto() && !dictando;
    if (micIco)  micIco.hidden  = enviar;
    if (sendIco) sendIco.hidden = !enviar;
    elMic.classList.toggle('is-dictando', dictando);
    elMic.setAttribute('aria-label', enviar ? 'Enviar' : (dictando ? 'Detener el dictado' : 'Grabar un mensaje de voz'));
    elMic.setAttribute('title', enviar ? 'Enviar' : (dictando ? 'Toca para terminar' : 'Toca para hablar'));
  }

  function mandarTexto() {
    var t = (elTexto.value || '').trim();
    if (t === '' || ocupado) return;
    if (dictando) dictarParar();
    burbuja('yo', esc(t));
    elTexto.value = '';
    ajustarAlto();
    pintarBotonWa();
    enviar({ accion: 'responder', tipo: 'texto', texto: t });
  }

  elTexto.addEventListener('keydown', function (ev) {
    if (ev.key === 'Enter' && !ev.shiftKey) { ev.preventDefault(); mandarTexto(); }
  });
  elTexto.addEventListener('input', function () { ajustarAlto(); pintarBotonWa(); });

  /* ---------------------------------------------------------------- 🎙️ el dictado */
  function dictarParar(aviso) {
    if (!dictando) return;
    dictando = false;
    if (dictTimer) { clearTimeout(dictTimer); dictTimer = null; }
    try { if (dictRec) dictRec.stop(); } catch (e) {}
    dictRec = null;
    pintarBotonWa();
    if (aviso) ayuda(aviso);
  }

  function dictarEmpezar() {
    var SR = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SR) { errorAmable('Este navegador no dicta. Escribe, por favor ✍️'); return; }
    try {
      dictRec = new SR();
      dictRec.lang = 'es-PE';
      dictRec.continuous = true;
      dictRec.interimResults = true;
      dictBase = (elTexto.value || '');
      dictando = true;
      pintarBotonWa();
      ayuda('Te escucho… habla normal 🎙️ (se corta solo a los 2 minutos)');
      dictRec.onresult = function (ev) {
        var t = '';
        for (var i = ev.resultIndex; i < ev.results.length; i++) t += ev.results[i][0].transcript;
        elTexto.value = (dictBase + ' ' + t).replace(/\s+/g, ' ').trim();
        ajustarAlto();
      };
      dictRec.onerror = function () { dictarParar('No pude escucharte. Escribe, por favor ✍️'); };
      dictRec.onend = function () { if (dictando) dictarParar(); pintarBotonWa(); };
      dictRec.start();
      dictTimer = setTimeout(function () { dictarParar('Corté el dictado a los 2 minutos ⏱️'); }, 120000);
    } catch (e) {
      dictarParar('No pude escucharte. Escribe, por favor ✍️');
    }
  }

  if (elMic) {
    elMic.addEventListener('click', function () {
      if (ocupado) return;
      if (dictando) { dictarParar(); return; }
      if (hayTexto()) { mandarTexto(); return; }
      dictarEmpezar();
    });
  }

  /* ---------------------------------------------------------------- 😊 emojis */
  var EMOJIS = ['😀','😃','😄','😁','😊','🙂','😉','😍','🤩','😎','🤝','👍','👏','🙌','💪','🙏','✨','⭐','🔥','💥',
                '🎉','🎊','🎁','💰','💵','🛒','🛍️','📦','🏪','🏬','🚀','📷','🖼️','🎨','🎵','🎶','👑','✅','📲','📱',
                '📍','🗺️','🕐','❤️','💚','🌟','🧠','⏳'];
  if (elEmojiBtn && elEmojiPnl) {
    elEmojiPnl.innerHTML = EMOJIS.map(function (x) { return '<button type="button">' + x + '</button>'; }).join('');
    elEmojiBtn.addEventListener('click', function () { elEmojiPnl.hidden = !elEmojiPnl.hidden; });
    elEmojiPnl.addEventListener('click', function (ev) {
      var b = ev.target.closest('button');
      if (!b) return;
      elTexto.value = (elTexto.value || '') + b.textContent;
      elTexto.focus();
      ajustarAlto();
      pintarBotonWa();
    });
  }

  /* ---------------------------------------------------------------- 📷 las fotos */
  /** El peso en palabras (para que el jefe VEA que la foto se comprime antes de subir). */
  function peso(bytes) {
    var b = Number(bytes) || 0;
    if (b >= 1048576) return (b / 1048576).toFixed(1).replace('.', ',') + ' MB';
    if (b >= 1024) return Math.round(b / 1024) + ' KB';
    return b + ' B';
  }

  function optimizarTodas(files) {
    var salida = [], i = 0;
    // 📷📉 SE COMPRIME EN EL CELULAR ANTES DE SUBIR (orden del jefe: máximo 800 px de lado mayor).
    //    Medido con una foto de 12 MP de 8,97 MB: a 1600 px subía 1,34 MB y a 800 px sube ~190 KB.
    var op = { maxLado: Number(CFG.foto_lado || 800), calidad: Number(CFG.foto_calidad || 0.82) };
    function paso() {
      if (i >= files.length) return Promise.resolve(salida);
      var f = files[i];
      var p = (window.CZImg && CZImg.optimizar) ? CZImg.optimizar(f, op) : Promise.resolve(f);
      return p.catch(function () { return f; }).then(function (opt) {
        salida.push(opt || f);
        i++;
        ayuda('Preparando las fotos… ' + i + ' de ' + files.length);
        return paso();
      });
    }
    return paso();
  }

  function burbujaFotos(files, nota) {
    var urls = files.map(function (f) { try { return URL.createObjectURL(f); } catch (e) { return ''; } });
    var html = '<strong>' + (files.length === 1 ? '📷 Mi foto' : '🖼️ Mis ' + files.length + ' fotos') + '</strong>'
             + '<div class="tia-fotos tia-fotos--' + Math.min(files.length, 6) + '">';
    urls.forEach(function (u) { html += u ? '<img src="' + u + '" alt="foto" onload="URL.revokeObjectURL(this.src)">' : ''; });
    html += '</div><span class="tia-fotos__nota">' + esc(nota) + '</span>';
    return burbuja('yo', html, 'tia-msg--ancha');
  }

  function mandarFotos(files) {
    if (ocupado) return;
    var lista = Array.prototype.slice.call(files || []).filter(function (f) {
      return f && (!f.type || f.type.indexOf('image/') === 0);
    });
    if (!lista.length) return;
    var max = Number(CFG.lote_max || 12);
    if (lista.length > max) { lista = lista.slice(0, max); ayuda('Mando las primeras ' + max + ' 👌'); }
    var peso_antes = lista.reduce(function (s, f) { return s + (f.size || 0); }, 0);
    pensando(true, 'Preparando ' + lista.length + (lista.length === 1 ? ' foto…' : ' fotos…'));
    optimizarTodas(lista).then(function (listos) {
      pensando(false);
      var peso_despues = listos.reduce(function (s, f) { return s + (f.size || 0); }, 0);
      // 📉 El jefe VE el antes y el después: es la prueba de que la foto se comprime ANTES de subir.
      var nota = (peso_despues > 0 && peso_antes > peso_despues)
        ? 'Comprimidas antes de subir: ' + peso(peso_antes) + ' → ' + peso(peso_despues)
        : 'Subiendo…';
      burbujaFotos(listos, nota);
      enviar({ accion: 'responder' }, listos);
    }).catch(function () {
      pensando(false);
      burbujaFotos(lista, 'Subiendo…');
      enviar({ accion: 'responder' }, lista);
    });
  }

  if (elCamara)  elCamara.addEventListener('click',  function () { if (elArchivo) elArchivo.click(); });
  if (elClip)    elClip.addEventListener('click',    function () { if (elArchivoGal) elArchivoGal.click(); });
  if (elGaleria) elGaleria.addEventListener('click', function () { if (elArchivoGal) elArchivoGal.click(); });

  elArchivo.addEventListener('change', function () {
    var f = elArchivo.files && elArchivo.files[0];
    elArchivo.value = '';
    mandarFotos(f ? [f] : []);
  });
  if (elArchivoGal) {
    elArchivoGal.addEventListener('change', function () {
      var fs = elArchivoGal.files ? Array.prototype.slice.call(elArchivoGal.files) : [];
      elArchivoGal.value = '';
      mandarFotos(fs);
    });
  }

  // 🖱️ Arrastrar fotos a la pantalla (en la computadora): el mismo camino que la galería.
  ['dragenter', 'dragover'].forEach(function (ev) {
    elChat.addEventListener(ev, function (e) { e.preventDefault(); elChat.classList.add('tia-soltando'); });
  });
  ['dragleave', 'drop'].forEach(function (ev) {
    elChat.addEventListener(ev, function (e) { e.preventDefault(); elChat.classList.remove('tia-soltando'); });
  });
  elChat.addEventListener('drop', function (e) {
    if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length) mandarFotos(e.dataTransfer.files);
  });

  /* ==================================================================================
   * 💰 EL PANEL DE LA OFERTA — la cuenta que cierra la venta.
   *   · La tabla se recalcula EN VIVO mientras se escribe el precio (sin ir al servidor:
   *     delante del cliente no se puede esperar, y así se toca cuantas veces quiera).
   *   · La FRASE para decir en voz alta también se arma aquí (es la misma cuenta).
   *   · Los 4 textos largos (la oferta, el guion, las condiciones) vienen del servidor ya
   *     armados con los datos de ESTA tienda, cada uno con su botón de COPIAR.
   *   ⚠️ Los números de la oferta (S/ 20 · 50 ventas · 10 %) NO se escriben aquí: se reciben
   *      en `CFG.oferta`, que sale de `config_supremo.php`. Cambiar el precio de la oferta es
   *      cambiar UNA línea del PHP.
   * ================================================================================== */
  var OF = CFG.oferta || { tarifa: 20, ventas: 50, comision: 10, dias: 30, moneda: 'S/' };
  var ofertaDatos = null;   // lo último que mandó el servidor (textos + productos)

  function soles(n) {
    var x = Number(n) || 0;
    return OF.moneda + ' ' + x.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }
  function pct(n) { return (Number(n) || 0).toFixed(2) + ' %'; }

  /** La cuenta completa. Es la MISMA fórmula del servidor (`supremo_calculo()`). */
  function calcular(precio) {
    var p = Math.max(0, Number(precio) || 0);
    var n = Number(OF.ventas) || 50;
    var ingreso  = p * n;
    var comision = ingreso * (Number(OF.comision) || 10) / 100;
    var ahorro   = comision - Number(OF.tarifa);
    var efectiva = ingreso > 0 ? (Number(OF.tarifa) / ingreso * 100) : 0;
    var cubre    = (p > 0 && Number(OF.comision) > 0) ? Math.ceil(Number(OF.tarifa) / (p * Number(OF.comision) / 100)) : 0;
    var ideal    = Number(OF.ideal || 1);
    var precioMin = (ideal > 0 && n > 0) ? Math.ceil((Number(OF.tarifa) / (ideal / 100)) / n) : 0;
    return { precio: p, ventas: n, ingreso: ingreso, comision: comision, tarifa: Number(OF.tarifa),
             ahorro: ahorro, efectiva: efectiva, cubre: cubre, sirve: (p > 0 && ahorro > 0),
             ideal: ideal, luce: (ingreso > 0 && efectiva <= ideal), precioMin: precioMin };
  }

  /** La frase para decir en voz alta (misma redacción que `supremo_frase_venta()` del servidor). */
  function fraseVenta(c) {
    if (!c.sirve) return 'Dime el precio de un producto y te hago la cuenta delante de ti.';
    return 'Si este producto cuesta ' + soles(c.precio) + ' y vendemos ' + c.ventas + ', generas ' + soles(c.ingreso)
         + '. Si me pagaras ' + (Number(OF.comision) % 1 === 0 ? Number(OF.comision) : Number(OF.comision).toFixed(2))
         + '% por venta, serían ' + soles(c.comision) + '. Yo no te cobro eso: te cobro solo ' + soles(c.tarifa)
         + ' al mes, que es ' + pct(c.efectiva) + ' de lo que vendes. Y si no vendo ' + c.ventas + ', no pagas nada.';
  }

  function pintarCalculo() {
    var campo = document.getElementById('supCalcPrecio');
    if (!campo) return;
    var c = calcular(campo.value);
    var set = function (id, txt) { var e = document.getElementById(id); if (e) e.textContent = txt; };
    set('supCalcPrecioV', c.precio > 0 ? soles(c.precio) : '—');
    set('supCalcIngreso',  c.precio > 0 ? soles(c.ingreso) : '—');
    set('supCalcComision', c.precio > 0 ? soles(c.comision) : '—');
    set('supCalcTarifa',   soles(c.tarifa));
    set('supCalcAhorro',   c.precio > 0 ? soles(c.ahorro) : '—');
    set('supCalcEfectiva', c.precio > 0 ? pct(c.efectiva) : '—');
    set('supCalcCubre',    c.cubre > 0 ? (c.cubre + ' ventas al mes (el resto es suyo)') : '—');
    var fr = document.getElementById('supCalcFrase');
    if (fr) {
      // ⚠️ Con un producto muy barato la cuenta NO luce delante del cliente: se le dice qué buscar.
      var aviso = (c.precio > 0 && !c.luce)
        ? '<br><br>⚠️ <b>Con este precio la cuenta no luce:</b> mi tarifa sale al ' + pct(c.efectiva)
          + ' de lo vendido. Busca un producto de <b>' + soles(c.precioMin) + ' o más</b> y baja del '
          + (c.ideal % 1 === 0 ? c.ideal : c.ideal.toFixed(2)) + ' %.'
        : '';
      fr.innerHTML = '🗣️ ' + esc(fraseVenta(c)) + aviso;
    }
  }

  /** Los 4 textos con su botón de copiar (lo que el jefe se lleva a la tienda o al WhatsApp). */
  function pintarOfertaTextos(o) {
    var caja = document.getElementById('supOfertaTextos');
    if (!caja || !o) return;
    var piezas = [
      { emoji: '💰', titulo: 'LA OFERTA (por escrito)', como: 'Léela en la tienda o mándasela por WhatsApp.', texto: o.texto },
      { emoji: '🗣️', titulo: 'EL GUION DE 6 PASOS', como: 'Uno por paso, sin prisa, delante del dueño.', texto: o.guion },
      { emoji: '📋', titulo: 'LAS CONDICIONES', como: 'Se acuerdan ANTES de empezar (es lo que evita que te quemen después).', texto: o.reglas }
    ];
    caja.innerHTML = '';
    piezas.forEach(function (p) {
      if (!p.texto) return;
      var card = document.createElement('div');
      card.className = 'sup-cod';
      card.innerHTML = '<div class="sup-cod__cabeza">'
        + '<span class="sup-cod__emoji">' + p.emoji + '</span>'
        + '<span class="sup-cod__tit">' + esc(p.titulo) + '</span>'
        + '<button type="button" class="sup-cod__copiar" data-of="1">📋 Copiar</button></div>'
        + '<p class="sup-cod__como">' + esc(p.como) + '</p>'
        + '<pre class="sup-cod__texto">' + esc(p.texto) + '</pre>';
      card.querySelector('.sup-cod__copiar').addEventListener('click', function (ev) {
        copiar(String(p.texto), ev.currentTarget);
      });
      caja.appendChild(card);
    });
  }

  /** Los chips de los productos REALES de la tienda (con su precio) para no teclear nada. */
  function pintarOfertaChips(o, productos) {
    var caja = document.getElementById('supCalcChips');
    if (!caja) return;
    caja.innerHTML = '';
    var con = (productos || []).filter(function (p) { return Number(p.precio) > 0; }).slice(0, 8);
    if (!con.length) return;
    var t = document.createElement('span');
    t.className = 'sup-calc__chips-et';
    t.textContent = 'De su catálogo:';
    caja.appendChild(t);
    con.forEach(function (p) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'sup-chip' + (Number(o && o.precio) === Number(p.precio) ? ' is-ok' : '');
      b.textContent = p.titulo + ' · ' + soles(p.precio);
      b.addEventListener('click', function () {
        var campo = document.getElementById('supCalcPrecio');
        if (campo) { campo.value = p.precio; pintarCalculo(); }
        Array.prototype.forEach.call(caja.querySelectorAll('.sup-chip'), function (x) { x.classList.remove('is-ok'); });
        b.classList.add('is-ok');
      });
      caja.appendChild(b);
    });
  }

  function abrirOferta() {
    var campo = document.getElementById('supCalcPrecio');
    if (campo && !campo.value && ofertaDatos && Number(ofertaDatos.precio) > 0) campo.value = ofertaDatos.precio;
    pintarCalculo();
    if (elSheetOf) elSheetOf.hidden = false;
  }
  function cerrarOferta() { if (elSheetOf) elSheetOf.hidden = true; }

  if (elBtnOferta) elBtnOferta.addEventListener('click', function () { cerrarMenu(); abrirOferta(); });
  var elVerOferta = document.getElementById('supVerOferta');
  if (elVerOferta) elVerOferta.addEventListener('click', function () { cerrarMenu(); abrirOferta(); });
  var elCalcPrecio = document.getElementById('supCalcPrecio');
  if (elCalcPrecio) elCalcPrecio.addEventListener('input', pintarCalculo);
  var elCalcUsar = document.getElementById('supCalcUsar');
  if (elCalcUsar) elCalcUsar.addEventListener('click', function () {
    var v = Number(elCalcPrecio && elCalcPrecio.value);
    if (!(v > 0)) { errorAmable('Escribe primero el precio del producto 💰'); return; }
    cerrarOferta();
    burbuja('yo', 'La cuenta con ' + soles(v));
    enviar({ accion: 'responder', tipo: 'opcion', valor: 'precio:' + v, texto: 'Precio ' + v });
  });
  if (elSheetOf) {
    elSheetOf.addEventListener('click', function (ev) {
      if (ev.target && ev.target.getAttribute && ev.target.getAttribute('data-cerrar')) cerrarOferta();
    });
  }

  /* ==================================================================================
   * 🎵 LA CANCIÓN — subir el audio que el jefe bajó de Google Flow.
   *   Orden del jefe (2026-09-18): *«no hay modo de subir la canción descargada al Supremo»*.
   *   El audio **se manda tal cual** (no se comprime como las fotos: es música) y se puede subir
   *   desde el botón 🎵 de la cabecera **en cualquier momento de la venta**.
   * ================================================================================== */
  var cancionDatos = null;

  function pintarCancion(c) {
    cancionDatos = c || null;
    var caja = document.getElementById('supCancion');
    var est  = document.getElementById('supCancionEstado');
    // La tira de la pantalla principal (cuando la tienda ya tiene su canción).
    if (caja) {
      if (!c || !c.url) { caja.hidden = true; caja.innerHTML = ''; }
      else {
        caja.hidden = false;
        caja.innerHTML = '<p class="sup-cancion__tit">🎵 Ya tiene su canción ('
          + Math.round(Number(c.duracion) || 0) + ' s)</p>'
          + '<audio controls preload="none" src="' + esc(c.url) + '"></audio>';
      }
    }
    // Y dentro del panel: el reproductor de la que ya está puesta.
    if (est) {
      est.innerHTML = (c && c.url)
        ? '<p class="sup-cancion__tit">Ya está puesta 👇 (toca ▶️ para oírla)</p>'
          + '<audio controls preload="none" src="' + esc(c.url) + '"></audio>'
        : '<p class="sup-cancion__vacio">Todavía no tiene canción.</p>';
    }
    var nota = document.getElementById('supCancionNota');
    if (nota) {
      nota.textContent = (c && c.url)
        ? ('Si subes otra, la reemplazo (hasta ' + (CFG.audio_max_mb || 8) + ' MB)')
        : ('mp3, ogg, m4a… · hasta ' + (CFG.audio_max_mb || 8) + ' MB');
    }
  }

  function abrirCancion() {
    pintarCancion(cancionDatos);
    if (elSheetCan) elSheetCan.hidden = false;
  }
  function cerrarCancion() { if (elSheetCan) elSheetCan.hidden = true; }

  /** Manda el audio al servidor (sin tocarlo: es música, no se recomprime). */
  function mandarAudio(file) {
    if (ocupado) return;
    if (!file) return;
    var maxMb = Number(CFG.audio_max_mb || 8);
    if (file.size > maxMb * 1048576) {
      errorAmable('Ese audio pesa ' + (file.size / 1048576).toFixed(1) + ' MB y el tope es ' + maxMb
        + ' MB. Prueba con una versión más corta o en mp3.');
      return;
    }
    cerrarCancion();
    pensando(true, 'Subiendo la canción…');
    var cuerpo = new FormData();
    cuerpo.append('accion', 'responder');
    cuerpo.append('csrf', CFG.csrf);
    cuerpo.append('tipo', 'audio');
    cuerpo.append('valor', 'cancion_subir');
    cuerpo.append('audio', file, file.name || 'cancion.mp3');

    fetch(CFG.api, { method: 'POST', credentials: 'same-origin', body: cuerpo })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        pensando(false);
        if (!j.ok) { errorAmable(j.mensaje || 'No pude subir la canción.'); return; }
        pintarMensajes(j.mensajes);
        if (j.cancion) pintarCancion(j.cancion);
        pintarPaso(j);
      })
      .catch(function () { pensando(false); errorAmable('Se cortó la subida. Prueba otra vez.'); });
  }

  if (elBtnCancion) elBtnCancion.addEventListener('click', function () { cerrarMenu(); abrirCancion(); });
  var elVerCancion = document.getElementById('supVerCancion');
  if (elVerCancion) elVerCancion.addEventListener('click', function () { cerrarMenu(); abrirCancion(); });
  var elCancionSubir = document.getElementById('supCancionSubir');
  if (elCancionSubir) elCancionSubir.addEventListener('click', function () { if (elAudio) elAudio.click(); });
  if (elAudio) elAudio.addEventListener('change', function () {
    var f = elAudio.files && elAudio.files[0];
    elAudio.value = '';
    mandarAudio(f);
  });
  if (elSheetCan) {
    elSheetCan.addEventListener('click', function (ev) {
      if (ev.target && ev.target.getAttribute && ev.target.getAttribute('data-cerrar')) cerrarCancion();
    });
  }

  /* ---------------------------------------------------------------- el menú y el panel */
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

  if (elBtnCodigos) elBtnCodigos.addEventListener('click', function () { cerrarMenu(); abrirCodigos(); });
  // 🧠 Piensa mejor (cabecera y menú): revisa la tienda publicada y completa lo que falte.
  if (elBtnPiensa) elBtnPiensa.addEventListener('click', function () { cerrarMenu(); abrirPiensa(); });
  if (elVerPiensa) elVerPiensa.addEventListener('click', function () { cerrarMenu(); abrirPiensa(); });
  // 📍 Compartir la ubicación: SIEMPRE disponible, en cualquier paso de la venta.
  if (elVerUbic) elVerUbic.addEventListener('click', function () { cerrarMenu(); compartirUbicacion(); });
  var elVerCodigos = document.getElementById('supVerCodigos');
  if (elVerCodigos) elVerCodigos.addEventListener('click', function () { cerrarMenu(); abrirCodigos(); });
  var elIrImagenes = document.getElementById('supIrImagenes');
  if (elIrImagenes) elIrImagenes.addEventListener('click', function () {
    cerrarMenu();
    if (ocupado) return;
    burbuja('yo', 'Ir a subir las imágenes');
    enviar({ accion: 'responder', tipo: 'opcion', valor: 'ir_imagenes', texto: 'Ir a subir las imágenes' });
  });
  var elVerTienda = document.getElementById('supVerTienda');
  if (elVerTienda) elVerTienda.addEventListener('click', function () {
    cerrarMenu();
    var u = ultimo && ultimo.publicado ? ultimo.publicado.url : '';
    if (u) window.open(u, '_blank', 'noopener');
    else errorAmable('Todavía no hay tienda publicada en esta venta.');
  });

  var elGuardar = document.getElementById('supGuardar');
  if (elGuardar) elGuardar.addEventListener('click', function () {
    cerrarMenu();
    if (ocupado) return;
    fetch(CFG.api, {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'salir', csrf: CFG.csrf })
    }).then(function (r) { return r.json(); }).then(function (j) {
      burbuja('bot', formato(j.mensaje || 'Listo, guardé la venta 👍'));
    }).catch(function () { errorAmable('No pude guardar ahora mismo, pero se guarda solo en cada paso.'); });
  });

  var elReiniciar = document.getElementById('supReiniciar');
  if (elReiniciar) elReiniciar.addEventListener('click', function () {
    cerrarMenu();
    if (ocupado) return;
    if (!window.confirm('¿Empezar otra venta desde el principio? Lo que ya se publicó (la tienda y sus productos) NO se borra; solo se deja esta conversación a un lado.')) return;
    fetch(CFG.api, {
      method: 'POST', credentials: 'same-origin',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ accion: 'reiniciar', csrf: CFG.csrf })
    }).then(function () { window.location.reload(); });
  });

  // Cerrar el panel de códigos con la ✕, tocando el fondo o con Escape.
  if (elSheetCod) {
    elSheetCod.addEventListener('click', function (ev) {
      if (ev.target && ev.target.getAttribute && ev.target.getAttribute('data-cerrar')) cerrarCodigos();
    });
  }
  document.addEventListener('keydown', function (ev) {
    if (ev.key === 'Escape') { cerrarCodigos(); cerrarOferta(); cerrarMenu(); }
  });

  /* ---------------------------------------------------------------- arranque */
  pintarBotonWa();
  ayuda('Cargando la venta…');
  fetch(CFG.api, { credentials: 'same-origin' })
    .then(function (r) { return r.json(); })
    .then(function (j) {
      ayuda('');
      if (!j.ok) { errorAmable(j.mensaje || 'No pude abrir la venta.'); return; }
      pintarMensajes(j.mensajes);
      pintarPaso(j);
    })
    .catch(function () { ayuda(''); errorAmable('No pude abrir la venta. Recarga la página.'); });
})();
