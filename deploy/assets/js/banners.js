/* banners.js — Módulo de publicidad: clic en banner → modal de negocios que resuelven
 * la necesidad, con <select> de zona (📍 Cerca de mí · Todas las zonas · Chimbote ·
 * Nuevo Chimbote · Santa).
 * 🔎 Si el banner trae un TÉRMINO DE BÚSQUEDA (data-busqueda / href a buscar.php?q=…),
 * el clic navega a los resultados de búsqueda: aquí no hay que hacer nada especial (el
 * href manda), solo contar el clic con keepalive para que la estadística no lo pierda.
 * La imagen del banner NO cambia sola: la elección ocurre al cargar la página (PHP).
 * Cada clic sobre un banner registra 1 clic (estadística) en api/banners.php.
 * En la lista de resultados, TODA la celda del negocio es clicable (foto, textos y
 * zona blanca) y abre la tienda; los botones WhatsApp / Llamar siguen por separado.
 *
 * 🧭 CERCA DE MÍ (2026-09-10, pedido del jefe Jimmy)
 * -----------------------------------------------------------------------------
 * REGLA MADRE: el criterio del banner NUNCA se pierde. "Cerca de mí" no reemplaza al
 * tema: lo MISMO que ya se muestra se filtra por radio y se ordena del más cercano al
 * más lejano, con los metros en cada resultado. Quien entró por "pollo a la brasa" ve
 * pollo a la brasa cercano, no ferreterías cercanas.
 *
 * Una sola fuente de verdad: `est.zona` ('' = todas las zonas | 'cerca' | slug de distrito).
 * La opción del <select> y el botón "📍 Ver los que están más cerca de mí" escriben ESA
 * misma variable: por eso nunca pueden mostrar dos listas distintas.
 *
 * Lista partida (pedido textual del jefe): 3 primeros resultados → botón de cercanía →
 * el resto. Cuando la cercanía YA está activa, el orden de por sí es el mensaje: la
 * lista va seguida y en su lugar aparece una barrita con "Ver todas las zonas".
 *
 * La ubicación se pide SOLO cuando el visitante toca el botón o elige la opción (nunca
 * al abrir el modal). Si la niega o no hay GPS, el modal lo dice y el <select> sigue
 * funcionando: el visitante nunca queda sin salida.
 * Los radios (escalera 2 → 5 → 10 km hasta juntar 20) los decide el SERVIDOR, en
 * includes/banners.php, igual que el buscador.
 */
(function () {
  'use strict';

  var modal = document.getElementById('bnModal');
  if (!modal) return;
  var cuerpo = document.getElementById('bnCuerpo');
  var filtro = document.getElementById('bnFiltro');
  var BASE = window.SITE_URL || '';

  // OJO: no basta con 'geolocation' in navigator — hay navegadores y WebViews que
  // exponen la propiedad VACÍA (undefined/null, por privacidad o por una extensión).
  // Si solo se mira "in", el botón se pinta y al tocarlo revienta. Se comprueba el valor.
  var HAY_GPS   = !!(navigator.geolocation && typeof navigator.geolocation.getCurrentPosition === 'function');
  var ZONA_CERCA = 'cerca';          // valor de la opción "📍 Cerca de mí"
  var TEXTO_CERCA = 'a menos de ';   // (se usa en los mensajes de estado)

  /* ---------- Estado del modal (una sola fuente de verdad) ---------- */
  var est = {
    tema: '',        // tema del banner (el criterio: no se pierde NUNCA)
    nombre: '',      // nombre del rubro, para los mensajes
    zona: '',        // '' = todas las zonas | 'cerca' | slug de distrito
    pos: null,       // {lat, lng} ya obtenidas (no se vuelve a preguntar en la sesión)
    cerca: null,     // bloque "cerca" de la última respuesta (radio, completo…)
    aviso: '',       // mensaje que se pinta encima de la lista (ej. ubicación negada)
    pidiendo: false  // hay una petición de ubicación en curso
  };

  function esc(s) {
    var d = document.createElement('div');
    d.textContent = (s == null ? '' : String(s));
    return d.innerHTML;
  }

  function urlImagen(ruta) {
    if (!ruta) return '';
    if (/^(https?:)?\/\//.test(ruta)) return ruta;
    return BASE + '/' + ruta;
  }

  /* ---------- Modal open/close ---------- */
  function abrirModal() { modal.hidden = false; document.body.style.overflow = 'hidden'; }
  function cerrarModal() { modal.hidden = true; document.body.style.overflow = ''; }

  modal.querySelectorAll('[data-cierre]').forEach(function (el) {
    el.addEventListener('click', cerrarModal);
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !modal.hidden) cerrarModal(); });

  /* ---------- Pedir la ubicación (solo cuando el visitante la pide) ---------- */
  /* cb(ok, motivo) — motivo: 'negado' | 'error' | 'sin-gps' */
  function pedirUbicacion(cb) {
    if (est.pos) { cb(true); return; }
    if (!HAY_GPS) { cb(false, 'sin-gps'); return; }
    try {
      navigator.geolocation.getCurrentPosition(function (p) {
        est.pos = { lat: p.coords.latitude.toFixed(7), lng: p.coords.longitude.toFixed(7) };
        cb(true);
      }, function (err) {
        cb(false, (err && err.code === 1) ? 'negado' : 'error');
      }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 60000 });
    } catch (e) {
      cb(false, 'error');
    }
  }

  /* ---------- Carga de resultados (tema + zona) ---------- */
  function cargar() {
    var aviso = est.aviso || '';
    est.aviso = '';
    if (filtro) filtro.hidden = true;
    cuerpo.innerHTML = '<div class="bn-modal__msg"><span class="bn-spinner"></span>Buscando negocios que resuelven tu necesidad…</div>';

    var url = BASE + '/api/banners.php?action=negocios'
            + '&tema=' + encodeURIComponent(est.tema || '')
            + '&distrito=' + encodeURIComponent(est.zona === ZONA_CERCA ? '' : (est.zona || ''));

    // Cercanía: se manda la ubicación y el SERVIDOR aplica el criterio del tema + la
    // escalera de radios. El radio no viaja: lo decide la escalera (2 → 5 → 10 km).
    if (est.zona === ZONA_CERCA && est.pos) {
      url += '&lat=' + encodeURIComponent(est.pos.lat) + '&lng=' + encodeURIComponent(est.pos.lng);
    }

    fetch(url)
      .then(function (r) { return r.json(); })
      .then(function (j) { pintar(j, aviso); })
      .catch(function () {
        cuerpo.innerHTML = '<div class="bn-modal__vacio">Ups, no pudimos cargar los resultados. Inténtalo de nuevo.</div>';
        if (filtro) filtro.hidden = true;
      });
  }

  /* ---------- Cambiar de zona (un solo camino para el <select> y los botones) ---------- */
  function cambiarZona(zona) {
    if (zona === ZONA_CERCA) { irACerca(); return; }
    est.zona = zona || '';
    cargar();
  }

  /* Activa la cercanía: pide la ubicación y, si la niegan, vuelve donde estaba. */
  function irACerca() {
    if (est.zona === ZONA_CERCA || est.pidiendo) return;
    var zonaPrevia = est.zona;
    est.zona = ZONA_CERCA;
    est.pidiendo = true;

    // Feedback inmediato: el botón pasa a "Obteniendo tu ubicación…".
    var boton = cuerpo.querySelector('.bn-cerca-btn');
    if (boton) {
      boton.classList.add('is-busy');
      boton.innerHTML = '<span class="bn-cerca-btn__t">Obteniendo tu ubicación…</span>'
                      + '<span class="bn-cerca-btn__s">tu navegador te va a preguntar</span>';
    }

    pedirUbicacion(function (ok, motivo) {
      est.pidiendo = false;
      if (ok) { cargar(); return; }

      // No hay ubicación: se vuelve a la zona anterior y se avisa DENTRO del modal.
      est.zona  = zonaPrevia;
      est.aviso = (motivo === 'negado')
        ? 'No compartiste tu ubicación. Mientras tanto puedes elegir una zona o un distrito aquí arriba 👇'
        : (motivo === 'sin-gps')
          ? 'Tu navegador no puede ubicarte. Puedes elegir una zona o un distrito aquí arriba 👇'
          : 'No pudimos leer tu ubicación. Inténtalo otra vez o elige una zona o un distrito aquí arriba 👇';
      cargar();
    });
  }

  /* ---------- Pintado ---------- */
  function fila(n, conDistancia) {
    // 🖼️ `foto_chica` ya viene resuelta por el motor de PHP (la versión de 160 px):
    // es la que corresponde a la miniatura de 62 px del modal.
    var img = n.foto_chica || urlImagen(n.foto);
    var botones = '';
    /* 🧭 Contexto obligatorio (2026-09-11): el 💬 NUNCA abre el chat en blanco. El
       mensaje lleva el nombre de la tienda, el anuncio del que salió y el enlace de la
       página, DENTRO de la frase (2026-09-16: el jefe dijo que la línea aparte
       «🔗 Página donde lo vi: …» era demasiado texto). */
    if (n.whatsapp_url) {
      var txtWa = 'Hola *' + (n.nombre || '') + '* 👋, vi su negocio en el anuncio de "'
                + (est.nombre || 'DeChimbote.com') + '" de ' + location.href + ' y me interesa.';
      botones += '<a class="bn-btn bn-btn--wa" target="_blank" rel="noopener" href="'
               + esc(n.whatsapp_url + '?text=' + encodeURIComponent(txtWa)) + '">' + (window.WA_ICONO_SVG || '💬') + ' WhatsApp</a>';
    }
    if (n.tel_url)      botones += '<a class="bn-btn bn-btn--tel" href="' + esc(n.tel_url) + '">📞 Llamar</a>';
    botones += '<a class="bn-btn bn-btn--ver" href="' + esc(n.url) + '">Ver tienda →</a>';
    var destino = esc(n.url);
    /* TODA la celda es clicable: una capa <a> transparente cubre la celda completa
       (foto, textos y zona blanca) y abre la tienda. Los botones de .bn-item__acciones
       quedan por encima (z-index: 2) y siguen funcionando por separado. */
    return '<div class="bn-item">'
      + '<a class="bn-item__link" href="' + destino + '" aria-hidden="true" tabindex="-1"></a>'
      + (img ? '<img class="bn-item__img" src="' + esc(img) + '" alt="" loading="lazy" onerror="this.style.visibility=\'hidden\'">' : '')
      + '<div class="bn-item__info">'
      + '<a class="bn-item__nombre" href="' + destino + '">' + esc(n.nombre) + '</a>'
      + '<div class="bn-item__meta">' + (n.categoria ? esc(n.categoria) : '')
      + (n.distrito ? ' · 📍 ' + esc(n.distrito) : '') + '</div>'
      + (conDistancia && n.distancia_txt ? '<span class="bn-item__dist">⚡ ' + esc(n.distancia_txt) + '</span>' : '')
      + (botones ? '<div class="bn-item__acciones">' + botones + '</div>' : '')
      + '</div></div>';
  }

  /* Botón que va DESPUÉS del 3.er resultado: ordena lo mismo por cercanía. */
  function botonCerca() {
    if (!HAY_GPS) return '';   // sin GPS no se ofrece lo que no funciona
    return '<button type="button" class="bn-cerca-btn">'
      + '<span class="bn-cerca-btn__t">📍 Ver los que están más cerca de mí</span>'
      + '<span class="bn-cerca-btn__s">de este mismo rubro</span>'
      + '</button>';
  }

  function botonVerTodas() {
    return '<button type="button" class="bn-volver" data-bn-zona="">Ver todas las zonas</button>';
  }

  function pintar(j, aviso) {
    if (!j || !j.ok) {
      cuerpo.innerHTML = '<div class="bn-modal__vacio">Ups, no pudimos cargar los resultados.</div>';
      if (filtro) filtro.hidden = true;
      return;
    }
    var data = j.data || [];
    var distritos = j.distritos || [];
    var t = j.tema || {};
    var nombre = t.nombre || est.nombre || 'negocios';
    var distSel = (j.distrito && j.distrito.nombre) || '';
    est.cerca = j.cerca || null;
    var enCerca = !!(est.cerca && est.cerca.activo);
    var radio   = est.cerca ? est.cerca.radio_km : 0;

    var zonaTxt = enCerca
      ? 'cerca de ti'
      : (distSel ? 'en <b>' + esc(distSel) + '</b>' : 'en <b>todas las zonas</b>');

    var h = '';
    if (aviso) h += '<div class="bn-modal__msg bn-modal__msg--aviso">' + esc(aviso) + '</div>';
    h += '<div class="bn-modal__msg">' + esc(t.frase_res || 'Estos negocios te pueden ayudar 👇')
       + ' ' + zonaTxt + ':</div>';

    /* Selector de zona: el distrito y "Cerca de mí" son UNA sola elección. */
    if (filtro) {
      filtro.hidden = false;
      var sel = filtro.querySelector('#bnSelect');
      if (!sel) {
        sel = document.createElement('select');
        sel.id = 'bnSelect';
        sel.className = 'bn-modal__select';
        filtro.appendChild(sel);
        sel.addEventListener('change', function () { cambiarZona(sel.value); });
      }
      var zonaActual = est.zona;
      var opts = '';
      if (HAY_GPS) {
        opts += '<option value="' + ZONA_CERCA + '"' + (zonaActual === ZONA_CERCA ? ' selected' : '') + '>📍 Cerca de mí</option>';
      }
      opts += '<option value=""' + (zonaActual === '' ? ' selected' : '') + '>Todas las zonas</option>';
      distritos.forEach(function (d) {
        opts += '<option value="' + esc(d.slug) + '"' + (d.slug === zonaActual ? ' selected' : '') + '>' + esc(d.nombre) + '</option>';
      });
      sel.innerHTML = opts;
    }

    if (!data.length) {
      if (enCerca) {
        h += '<div class="bn-modal__vacio">No hay ' + esc(nombre) + ' a menos de '
           + esc(radio) + ' km de ti 😊.<br>Mira todas las zonas o elige un distrito aquí arriba, '
           + 'o vuelve a intentarlo cuando estés en otra zona.</div>';
        h += botonVerTodas();
      } else {
        h += '<div class="bn-modal__vacio">Por ahora no hay negocios de este rubro'
          + (distSel ? ' en ' + esc(distSel) : ' en tu zona')
          + ' 😊. Prueba con <b>Todas las zonas</b> o elige otro distrito arriba.</div>';
        h += botonCerca();
      }
      cuerpo.innerHTML = h;
      return;
    }

    if (enCerca) {
      /* Cercanía activa: la lista va seguida; el orden (y los metros) ya lo explican todo. */
      h += '<div class="bn-cerca-barra"><span>📍 Ordenados por cercanía'
         + (radio ? ' (' + TEXTO_CERCA + esc(radio) + ' km)' : '') + '</span>'
         + botonVerTodas() + '</div>';
      h += '<div class="bn-lista">';
      data.forEach(function (n) { h += fila(n, true); });
      h += '</div>';
      h += '<p class="bn-nota">Los negocios que no tienen ubicación en el mapa no aparecen en esta lista.</p>';
    } else {
      /* 3 primeros → botón de cercanía → el resto (pedido textual del jefe). */
      var primeros = data.slice(0, 3);
      var resto    = data.slice(3);
      h += '<div class="bn-lista">';
      primeros.forEach(function (n) { h += fila(n, false); });
      h += '</div>';
      h += botonCerca();
      if (resto.length) {
        h += '<div class="bn-lista">';
        resto.forEach(function (n) { h += fila(n, false); });
        h += '</div>';
      }
    }
    cuerpo.innerHTML = h;
  }

  /* ---------- Clics dentro del modal (delegado: el cuerpo se repinta entero) ---------- */
  cuerpo.addEventListener('click', function (ev) {
    var t = ev.target;
    if (!t || !t.closest) return;
    if (t.closest('.bn-cerca-btn')) { ev.preventDefault(); irACerca(); return; }
    var volver = t.closest('[data-bn-zona]');
    if (volver) { ev.preventDefault(); cambiarZona(volver.getAttribute('data-bn-zona')); }
  });

  /* ---------- Delegación de clics en banners ---------- */
  document.addEventListener('click', function (ev) {
    var a = ev.target && ev.target.closest ? ev.target.closest('a[data-banner]') : null;
    if (!a) return;
    var id = a.getAttribute('data-banner');
    // keepalive: el clic se cuenta aunque el banner NAVEGUE (búsqueda del sitio o enlace
    // externo) y la página se descargue de inmediato; sin esto el navegador podía cancelar
    // la petición y la estadística perdía clics.
    if (id) fetch(BASE + '/api/banners.php?action=clic&id=' + encodeURIComponent(id), { keepalive: true });

    var href = a.getAttribute('href') || '';
    var tema = a.getAttribute('data-tema') || '';
    // Banner con 🔎 búsqueda del sitio o 🔗 enlace externo: navega normal (el clic ya se contó).
    if (href && href !== '#') return;

    // Banner de necesidad: abre el modal.
    if (tema) {
      ev.preventDefault();
      var nombre = a.getAttribute('data-nombre') || '';
      abrirModal();
      // Al abrir, siempre se arranca en "todas las zonas" (la ubicación se pide
      // solo si el visitante toca el botón de cercanía).
      est.tema = tema; est.nombre = nombre; est.zona = ''; est.cerca = null; est.aviso = '';
      cargar();
    }
  });
})();
