<?php
/**
 * includes/btn_cerca.php — Botón reutilizable "📍 Ver tiendas cerca"
 * =================================================================
 * Se pone en CUALQUIER página con una sola línea:
 *
 *     <?php require_once __DIR__ . '/includes/btn_cerca.php'; ?>
 *     <?= btn_tiendas_cerca_html() ?>
 *
 * (Dentro de `includes/`, la ruta del require es __DIR__ . '/btn_cerca.php'.)
 *
 * Qué hace: el visitante toca el botón → el navegador pide permiso de ubicación
 * (gratis, sin APIs pagadas) → lo manda a buscar.php?lat=..&lng=..&radio=auto, que
 * lista las tiendas ordenadas por distancia.
 *
 * Reglas del proyecto que cumple:
 *  - Móvil-primero: una sola columna, botón ancho completo, fuentes ≥16 px.
 *  - Sin dependencias ni CSS externo (el <style> va inline con las variables del tema,
 *    así no pelea con la caché de base.css/components.css).
 *  - Si el navegador no tiene ubicación o el visitante la niega, avisa en la misma
 *    página (nunca deja al usuario sin salida: el enlace sigue llevando al buscador).
 *
 * Parámetros de btn_tiendas_cerca_html():
 *  - $texto  (string)  Texto grande del botón. Por defecto 'Ver tiendas cerca de mí' (2026-09-14).
 *  - $sub    (string)  Línea chica de abajo. Por defecto 'comparte tu ubicación'.
 *                      Pasar '' para no mostrarla.
 *  - $radio  (string)  Radio en km que se manda al buscador. Por defecto 'auto': el buscador
 *                      arranca en 2 km y amplía SOLO a 5 y a 10 km hasta juntar 20 resultados
 *                      (pedido del jefe, 2026-09-10). También acepta 2 / 5 / 10 / 20 / 30 para
 *                      fijar un radio exacto.
 *
 * Sesión 2026-09-10 (pedido del jefe Jimmy): repetir el botón de la portada
 * ("📍 Ver negocios cerca") en las fichas de tienda (encima de la galería),
 * en las páginas de rubro/categoría y en las fichas de productos.
 *
 * 🎨 2026-09-14 (pedido del jefe): el botón pasó de VERDE a NARANJA. El verde #16a34a se
 * confundía con los botones de WhatsApp (que son verdes a propósito, #25d366) cuando quedaban
 * uno cerca del otro en la misma pantalla. Ahora lleva el degradado de la marca
 * (granate #a3123c → rojo #d0312a → naranja #f0861c), anillo ámbar, resplandor exterior,
 * brillo diagonal y volumen de tecla: se ve claramente distinto de WhatsApp.
 * El verde de WhatsApp NO se toca. Detalle: GUIA_CERCA_DE_MI_Y_GEOBUSQUEDA.md §6.4.
 */

if (!function_exists('cerca_pin_svg')) {

    /**
     * El pin de ubicación BLANCO del botón naranja (reemplaza al emoji 📍, que sobre el
     * degradado granate/naranja quedaba sin contraste). Se usa en btn_cerca.php, en la
     * portada (hero) y en el botón del buscador.
     */
    function cerca_pin_svg($clase = 'btc-cerca__pin') {
        return '<svg class="' . e($clase) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">'
             . '<path fill="currentColor" d="M12 2.2c-3.9 0-7 3.1-7 7 0 5.1 6.3 12.1 6.6 12.4a.6.6 0 0 0 .9 0c.3-.3 6.5-7.3 6.5-12.4 0-3.9-3.1-7-7-7Zm0 9.6a2.6 2.6 0 1 1 0-5.2 2.6 2.6 0 0 1 0 5.2Z"/></svg>';
    }
}

if (!function_exists('btn_tiendas_cerca_html')) {

    /**
     * Devuelve el HTML del botón "Ver tiendas cerca".
     * El <style> y el <script> se imprimen UNA sola vez por página,
     * aunque se llame la función varias veces.
     */
    function btn_tiendas_cerca_html($texto = 'Ver tiendas cerca de mí', $sub = 'comparte tu ubicación', $radio = 'auto') {
        static $estilos_impresos = false;
        static $script_impreso  = false;

        // 'auto' = escalera del buscador (2 → 5 → 10 km hasta juntar 20 resultados).
        // Un número fija ese radio exacto. El número viaja tal cual en data-btc-radio.
        $radio_txt = is_numeric($radio)
            ? ((float)$radio == (int)$radio ? (string)(int)$radio : rtrim(rtrim(number_format((float)$radio, 1, '.', ''), '0'), '.'))
            : 'auto';
        $buscar    = url('buscar.php');

        $html = '';

        // ---------- 1) Estilos (una sola vez) ----------
        if (!$estilos_impresos) {
            $estilos_impresos = true;
            $html .= <<<CSS
<style>
/* ===== Botón "Ver tiendas cerca" (includes/btn_cerca.php) =====
   🎨 NARANJA desde el 2026-09-14 (pedido del jefe): el verde se confundía con los botones de
   WhatsApp. Degradado de la marca + anillo ámbar + resplandor + brillo diagonal + volumen. */
.btc-cerca{margin:16px auto;max-width:560px}
.btc-cerca__btn{position:relative;overflow:hidden;display:flex;flex-direction:column;align-items:center;
  justify-content:center;gap:2px;width:100%;box-sizing:border-box;color:#fff;text-decoration:none;border:0;
  border-radius:16px;padding:14px 18px;cursor:pointer;font-family:inherit;text-align:center;
  background:linear-gradient(105deg,#a3123c 0%,#d0312a 48%,#f0861c 100%);
  box-shadow:0 0 0 3px #fbd7a4,0 0 18px rgba(240,134,28,.45),0 8px 18px rgba(109,7,26,.28),
    inset 0 2px 0 rgba(255,255,255,.42),inset 0 -3px 0 rgba(90,6,20,.30);
  transition:transform .12s ease,filter .12s ease,box-shadow .12s ease}
/* El brillo diagonal (efecto cristal) va encima del fondo y debajo del texto */
.btc-cerca__btn::after{content:'';position:absolute;inset:0;pointer-events:none;
  background:linear-gradient(112deg,rgba(255,255,255,.32) 0%,rgba(255,255,255,.09) 40%,rgba(255,255,255,0) 64%)}
.btc-cerca__btn>span{position:relative;z-index:1}
.btc-cerca__btn:hover{filter:brightness(1.06);transform:translateY(-1px);
  box-shadow:0 0 0 3px #fde0b4,0 0 26px rgba(240,134,28,.60),0 10px 22px rgba(109,7,26,.32),
    inset 0 2px 0 rgba(255,255,255,.5),inset 0 -3px 0 rgba(90,6,20,.30)}
.btc-cerca__btn:active{transform:translateY(1px) scale(.995)}
.btc-cerca__btn.is-busy{opacity:.65;pointer-events:none}
.btc-cerca__t{display:flex;align-items:center;justify-content:center;gap:9px;font-size:17px;font-weight:800;
  line-height:1.2;text-shadow:0 2px 3px rgba(90,6,20,.45)}
.btc-cerca__tx{display:block;text-wrap:balance}
.btc-cerca__pin{flex:0 0 auto;width:22px;height:22px;color:#fff;filter:drop-shadow(0 1px 2px rgba(90,6,20,.5))}
.btc-cerca__s{font-size:12.5px;font-weight:500;color:rgba(255,255,255,.95);text-shadow:0 1px 2px rgba(90,6,20,.35)}
.btc-cerca__estado{margin:8px 0 0;font-size:13px;line-height:1.45;color:var(--color-texto-claro,#555);text-align:center}
.btc-cerca__estado.is-error{color:#b91c1c;font-weight:600}
.btc-cerca__estado.is-ok{color:#b45309;font-weight:600}
</style>
CSS;
        }

        // ---------- 2) El botón ----------
        $html .= '<div class="btc-cerca">';
        $html .= '<a class="btc-cerca__btn" href="' . e($buscar) . '"'
               . ' data-btc-url="' . e($buscar) . '"'
               . ' data-btc-radio="' . e($radio_txt) . '"'
               . ' title="Ver las tiendas que están cerca de ti">';
        $html .= '<span class="btc-cerca__t">' . cerca_pin_svg() . '<span class="btc-cerca__tx">' . e($texto) . '</span></span>';
        if ($sub !== '' && $sub !== null) {
            $html .= '<span class="btc-cerca__s">' . e($sub) . '</span>';
        }
        $html .= '</a>';
        $html .= '<p class="btc-cerca__estado" hidden></p>';
        $html .= '</div>';

        // ---------- 3) JavaScript (una sola vez, delegado: sirve para todos los botones) ----------
        if (!$script_impreso) {
            $script_impreso = true;
            $html .= <<<JS
<script>
(function () {
    'use strict';
    if (window.__btcCercaListo) return;
    window.__btcCercaListo = true;

    function buscar(el) {
        while (el && el.nodeType === 1) {
            if (el.classList && el.classList.contains('btc-cerca__btn')) return el;
            el = el.parentNode;
        }
        return null;
    }

    document.addEventListener('click', function (ev) {
        var a = buscar(ev.target);
        if (!a) return;
        ev.preventDefault();

        var caja   = a.parentNode;
        var estado = caja ? caja.querySelector('.btc-cerca__estado') : null;
        var base   = a.getAttribute('data-btc-url') || (window.SITE_URL + '/buscar.php');
        var radio  = a.getAttribute('data-btc-radio') || 'auto';

        function decir(t, tipo) {
            if (!estado) return;
            estado.hidden = false;
            estado.className = 'btc-cerca__estado' + (tipo ? ' is-' + tipo : '');
            estado.textContent = t;
        }

        if (!('geolocation' in navigator)) {
            decir('Tu navegador no puede ubicarte. Usa el buscador por texto o elige un rubro.', 'error');
            return;
        }

        decir('Obteniendo tu ubicación…', '');
        a.classList.add('is-busy');

        navigator.geolocation.getCurrentPosition(function (pos) {
            location.href = base
                + '?lat=' + pos.coords.latitude.toFixed(7)
                + '&lng=' + pos.coords.longitude.toFixed(7)
                + '&radio=' + encodeURIComponent(radio);
        }, function (err) {
            a.classList.remove('is-busy');
            if (err && err.code === 1) {
                decir('No compartiste tu ubicación. Puedes buscar por texto o elegir un rubro.', 'error');
            } else if (err && err.code === 2) {
                decir('No se pudo leer el GPS. Activa la ubicación del celular e inténtalo otra vez.', 'error');
            } else {
                decir('No pudimos obtener tu ubicación. Inténtalo de nuevo.', 'error');
            }
        }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 60000 });
    }, false);
})();
</script>
JS;
        }

        return $html;
    }
}
