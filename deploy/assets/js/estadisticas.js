/* ============================================================
   estadisticas.js — Módulo de ESTADÍSTICAS de DeChimbote.com
   ============================================================
   El pageview lo registra el SERVIDOR (includes/header.php).
   Este script solo manda "latidos" para medir:
     - cuánto tiempo permanece la persona en el sitio, y
     - cuánta gente hay AHORA mismo (presencia en vivo).
   Envía un POST cada ~60 s (t=latido) mientras la pestaña está visible
   y uno al ocultar/cerrar (t=salida) con la duración acumulada.
   Es silencioso: si algo falla, nunca molesta al visitante.
   ============================================================ */
(function () {
    'use strict';

    if (navigator.webdriver) return;                 // pruebas automatizadas
    if (/bot|crawl|spider|preview|slurp|facebookexternalhit/i.test(navigator.userAgent)) return;

    // Páginas internas que no se miden (igual que en el servidor)
    var ruta = location.pathname || '';
    if (/(superadmin\.php|migrar_|cron_|google_|registrar_vista|logout\.php|\/api\/)/.test(ruta)) return;
    if (/^\/(assets|includes|img|video|audio|fotos)\//.test(ruta)) return;

    var BASE = window.SITE_URL || '';

    function getCookie(nombre) {
        var m = document.cookie.match(new RegExp('(?:^|; )' + nombre + '=([^;]*)'));
        return m ? decodeURIComponent(m[1]) : '';
    }

    // Sin cookie anónima del servidor no hay sesión que latir
    var ck = getCookie('cz_stats');
    if (!ck || !/^[a-f0-9]{32}$/.test(ck)) return;

    var ultimo = Date.now();
    var salidaEnviada = false;
    var enviando = false;

    function ping(tipo) {
        if (enviando) return;
        var ahora = Date.now();
        var dur = Math.round((ahora - ultimo) / 1000);
        ultimo = ahora;
        if (tipo === 'latido' && dur < 20) return;    // evitar spam de pestañas en segundo plano
        var pg = (location.pathname || '/').substring(0, 190);
        var cuerpo = 't=' + encodeURIComponent(tipo) + '&dur=' + Math.min(dur, 900) + '&pg=' + encodeURIComponent(pg);
        enviando = true;

        function listo() { enviando = false; }

        try {
            if (navigator.sendBeacon) {
                var blob = new Blob([cuerpo], { type: 'application/x-www-form-urlencoded' });
                navigator.sendBeacon(BASE + '/api/estadisticas.php', blob);
                listo();
            } else {
                fetch(BASE + '/api/estadisticas.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: cuerpo,
                    credentials: 'same-origin',
                    keepalive: true
                }).catch(function () {}).then(listo);
            }
        } catch (e) {
            listo();
        }
    }

    // Latido cada 60 s SOLO con la pestaña visible
    setInterval(function () {
        if (document.visibilityState === 'visible') ping('latido');
    }, 60000);

    // Al ocultar la pestaña se manda la salida; al volver se reinicia el reloj
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'hidden') {
            if (!salidaEnviada) { salidaEnviada = true; ping('salida'); }
        } else {
            salidaEnviada = false;
            ultimo = Date.now();
        }
    });

    // Al cerrar / navegar fuera de la página (respaldo)
    window.addEventListener('pagehide', function () {
        if (!salidaEnviada) { salidaEnviada = true; ping('salida'); }
    });
})();
