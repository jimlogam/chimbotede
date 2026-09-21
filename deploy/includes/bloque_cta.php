<?php
/**
 * includes/bloque_cta.php — 📣 EL BLOQUE DE BOTONES DE LA PORTADA, PARA PONERLO EN CUALQUIER PÁGINA
 * =================================================================================================
 * Qué es: **los tres botones que están arriba en la portada**, juntos en una banda, listos para
 * repetirlos en otra página con una sola línea:
 *
 *     <?php require_once __DIR__ . '/includes/bloque_cta.php';   // (dentro de includes/)
 *     <?= bloque_cta_html() ?>
 *
 *   · 🟠 **Crear tienda**            → `/crear-tienda` (El maestro 🛠️)
 *   · 🟠 **📍 Ver tiendas cerca**    → pide la ubicación y abre el buscador por distancia
 *     (con la línea chica «comparte tu ubicación», como en la portada)
 *   · 🔵 **f Descubrir Nuevas Tiendas** → `/explorer.php` (el muro), ancho y delgado
 *
 * 🔴 ORDEN DEL JEFE (2026-09-16, textual): *«en el index tenemos este bloque, trata de meterlo en la
 * ficha de cada producto, **no arriba, si no después de la galería de productos**»* → así quedó en
 * `producto.php` (después de «🛍️ Mira otros productos de esta tienda»).
 *
 * 🎨 **Copia el diseño EXACTO de la portada** (ancho, colores, altos y sombras): banda granate con
 * esquinas redondeadas, «Crear tienda» naranja, el botón de la ubicación con el degradado de la marca
 * y su pin, y el botón azul `#0866ff` de Facebook con la «f» oficial. Las clases van con prefijo
 * propio (`bcta`) para que **no choquen con las de la portada** (`.hero*`) ni con las del botón
 * reutilizable de «Ver tiendas cerca» (`includes/btn_cerca.php`, que tiene su propio estilo y su
 * propio JS).
 *
 * ⚠️ El CSS y el JS se imprimen **UNA sola vez por página**, aunque se llame la función varias veces.
 * ⚠️ El botón de la ubicación NO es un enlace muerto: si el visitante niega el permiso o el celular no
 * puede ubicarlo, **avisa en la misma página** y el enlace sigue llevando al buscador (misma política
 * que `btn_cerca.php`: nunca se deja al visitante sin salida).
 *
 * Guía: GUIA_DISENO_DEL_INDEX.md (la banda del hero) · GUIA_CERCA_DE_MI_Y_GEOBUSQUEDA.md §6.4.
 */

if (!function_exists('bloque_cta_html')) {

    /**
     * Devuelve el HTML del bloque de botones (banda + estilos + script).
     *
     * @param array $op  · 'radio'  (string) km que se le mandan al buscador ('auto' por defecto: la
     *                            escalera 2 → 5 → 10 km del buscador).
     *                   · 'texto_cerca' / 'sub_cerca' (string) para cambiar el rótulo del botón.
     *                   · 'clase'  (string) clase extra para la banda (por ejemplo, un margen).
     *                   · 'explorer' (bool) false para no mostrar el botón azul (por defecto sí).
     */
    function bloque_cta_html(array $op = []) {
        static $estilos = false;
        static $script  = false;

        // El pin blanco del botón de ubicación vive en `includes/btn_cerca.php` (el botón reutilizable
        // «Ver tiendas cerca» que ya usan las fichas y el buscador). Ese archivo solo DEFINE cosas: no
        // imprime nada hasta que alguien llama a su función, así que pedirlo aquí es gratis.
        if (!function_exists('cerca_pin_svg')) {
            $ruta = __DIR__ . '/btn_cerca.php';
            if (is_file($ruta)) require_once $ruta;
        }

        $radio = (string)($op['radio'] ?? 'auto');
        $radio_txt = is_numeric($radio)
            ? ((float)$radio == (int)$radio ? (string)(int)$radio : rtrim(rtrim(number_format((float)$radio, 1, '.', ''), '0'), '.'))
            : 'auto';
        $texto_cerca = (string)($op['texto_cerca'] ?? 'Ver tiendas cerca');
        $sub_cerca   = (string)($op['sub_cerca'] ?? 'comparte tu ubicación');
        $clase       = trim((string)($op['clase'] ?? ''));
        $con_explorer = !isset($op['explorer']) || !empty($op['explorer']);
        $buscar   = url('buscar.php');

        $html = '';

        /* ---------- 1) Los estilos (una sola vez por página) ---------- */
        if (!$estilos) {
            $estilos = true;
            $html .= <<<CSS
<style>
/* ===== Bloque de botones de la portada (includes/bloque_cta.php) =====
   Mismo diseño que el hero de la portada: banda granate con esquinas redondeadas, «Crear tienda»
   naranja, el botón de la ubicación con el degradado de la marca y el botón azul del Explorer. */
.bcta{background:linear-gradient(135deg,var(--marca-granate,#6d071a) 0%,var(--marca-granate-osc,#520512) 100%);
  color:var(--marca-crema,#f7efe2);border:1px solid rgba(247,239,226,.25);border-radius:16px;
  padding:16px;margin:26px 0 8px;text-align:center;box-sizing:border-box}
.bcta__ctas{display:flex;flex-wrap:wrap;gap:10px;justify-content:center;align-items:stretch}
.bcta__btn{display:inline-flex;align-items:center;justify-content:center;text-align:center;
  min-height:58px;padding:10px 28px;font-size:17px;font-weight:700;border-radius:12px;
  background:var(--marca-naranja,#ea6a12);color:#fff;text-decoration:none;border:0;cursor:pointer;
  font-family:inherit;line-height:1.2}
.bcta__btn:hover{filter:brightness(1.07)}
.bcta__btn:active{transform:translateY(1px) scale(.995)}
/* 📍 El de la ubicación: el MISMO degradado, anillo ámbar y resplandor que en la portada (2026-09-14,
   el verde se confundía con los botones de WhatsApp). */
.bcta__btn--geo{position:relative;overflow:hidden;display:inline-flex;flex-direction:column;
  align-items:center;justify-content:center;gap:1px;min-height:58px;padding:10px 24px;border-radius:12px;
  background:linear-gradient(105deg,#a3123c 0%,#d0312a 48%,#f0861c 100%);color:#fff;cursor:pointer;
  box-shadow:0 0 0 3px #fbd7a4,0 0 18px rgba(240,134,28,.45),0 7px 16px rgba(109,7,26,.28),
    inset 0 2px 0 rgba(255,255,255,.42),inset 0 -3px 0 rgba(90,6,20,.30);
  transition:transform .12s ease,filter .12s ease,box-shadow .12s ease}
.bcta__btn--geo::after{content:'';position:absolute;inset:0;pointer-events:none;
  background:linear-gradient(112deg,rgba(255,255,255,.30) 0%,rgba(255,255,255,.08) 40%,rgba(255,255,255,0) 64%)}
.bcta__btn--geo:hover{filter:brightness(1.07);transform:translateY(-1px);
  box-shadow:0 0 0 3px #fde0b4,0 0 26px rgba(240,134,28,.60),0 9px 20px rgba(109,7,26,.32),
    inset 0 2px 0 rgba(255,255,255,.5),inset 0 -3px 0 rgba(90,6,20,.30)}
.bcta__btn--geo.is-busy{opacity:.6;cursor:wait;pointer-events:none}
.bcta__btn--geo>span{position:relative;z-index:1}
.bcta__t{display:inline-flex;align-items:center;gap:7px;font-size:17px;font-weight:800;color:#fff;
  text-shadow:0 2px 3px rgba(90,6,20,.45)}
.bcta__s{font-size:12.5px;font-weight:500;color:rgba(255,255,255,.95)}
.bcta__pin{flex:0 0 auto;width:19px;height:19px;color:#fff;filter:drop-shadow(0 1px 2px rgba(90,6,20,.5))}
/* 🔵 El botón del Explorer: azul de Facebook, ancho y delgado (44 px contra los 58 de los otros dos). */
.bcta__explorer{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;
  margin-top:10px;min-height:44px;padding:8px 14px;border-radius:12px;box-sizing:border-box;
  background:linear-gradient(180deg,#1a7cff 0%,#0866ff 55%,#0757d4 100%);color:#fff;
  font-size:16px;font-weight:700;text-decoration:none;line-height:1.15;letter-spacing:.01em;
  box-shadow:0 3px 10px rgba(3,20,60,.35),inset 0 1px 0 rgba(255,255,255,.35);
  transition:filter .12s ease,transform .12s ease}
.bcta__explorer:hover{filter:brightness(1.07)}
.bcta__explorer:active{transform:translateY(1px) scale(.997)}
.bcta__explorer-f{flex:0 0 auto;width:21px;height:21px}
.bcta__explorer-f svg{display:block;width:100%;height:100%}
.bcta__explorer,.bcta__explorer-t{white-space:nowrap}
.bcta__estado{margin:10px 0 0;font-size:13px;line-height:1.45;color:#ffd9c9}
.bcta__estado.is-error{color:#ffc9c9;font-weight:600}
/* 📱 En el celular, igual que en la portada: «Crear tienda» mide justo su texto y el de la ubicación
   se lleva el resto del ancho (así su texto entra en UNA línea). Fuentes ≥16 px (Regla de Oro n.º 1). */
@media (max-width:640px){
  .bcta{border-radius:14px;padding:14px 12px}
  .bcta__ctas{flex-wrap:nowrap;gap:8px}
  .bcta__btn{flex:0 1 auto;min-width:0;padding:10px 9px;font-size:16px}
  .bcta__btn--geo{flex:1 1 auto;padding:10px 8px}
  .bcta__t{font-size:16px;gap:6px;line-height:1.15}
  .bcta__pin{width:19px;height:19px}
  .bcta__s{font-size:12.5px}
}
</style>
CSS;
        }

        /* ---------- 2) La banda ---------- */
        $html .= '<div class="bcta' . ($clase !== '' ? ' ' . e($clase) : '') . '">';
        $html .= '<div class="bcta__ctas">';
        $html .= '<a class="bcta__btn" href="' . e(url('crear-tienda')) . '"'
               . ' title="Crear mi tienda con El maestro (Inteligencia Artificial): sube 8 fotos y la arma">Crear tienda</a>';
        $html .= '<a class="bcta__btn bcta__btn--geo" href="' . e($buscar) . '"'
               . ' data-bcta-url="' . e($buscar) . '" data-bcta-radio="' . e($radio_txt) . '"'
               . ' title="Ver las tiendas que están cerca de ti">'
               . '<span class="bcta__t">' . cerca_pin_svg('bcta__pin') . '<span>' . e($texto_cerca) . '</span></span>';
        if ($sub_cerca !== '') $html .= '<span class="bcta__s">' . e($sub_cerca) . '</span>';
        $html .= '</a>';
        $html .= '</div>';

        if ($con_explorer) {
            $html .= '<a href="' . e(url('explorer.php')) . '" class="bcta__explorer"'
                   . ' title="Descubrir nuevas tiendas en el Explorer: el muro de las tiendas de Chimbote">'
                   . '<span class="bcta__explorer-f" aria-hidden="true"><svg viewBox="0 0 320 512" focusable="false">'
                   . '<path fill="currentColor" d="M80 299.3V512H196V299.3h86.5l18-97.8H196V166.9c0-51.7 20.3-71.5 72.7-71.5 16.3 0 29.4.4 37 1.2V7.9C291.4 4 256.4 0 236.2 0 129.3 0 80 50.5 80 154.2v47.3H16v97.8h64z"/></svg></span>'
                   . '<span class="bcta__explorer-t">Descubrir Nuevas Tiendas</span></a>';
        }
        $html .= '<p class="bcta__estado" hidden></p>';
        $html .= '</div>';

        /* ---------- 3) El script de la ubicación (una sola vez, delegado) ---------- */
        if (!$script) {
            $script = true;
            $html .= <<<JS
<script>
(function () {
    'use strict';
    if (window.__bctaListo) return;
    window.__bctaListo = true;

    function boton(el) {
        while (el && el.nodeType === 1) {
            if (el.classList && el.classList.contains('bcta__btn--geo')) return el;
            el = el.parentNode;
        }
        return null;
    }

    document.addEventListener('click', function (ev) {
        var a = boton(ev.target);
        if (!a) return;
        ev.preventDefault();

        var caja   = a.parentNode ? a.parentNode.parentNode : null;
        var estado = caja ? caja.querySelector('.bcta__estado') : null;
        var base   = a.getAttribute('data-bcta-url') || (window.SITE_URL + '/buscar.php');
        var radio  = a.getAttribute('data-bcta-radio') || 'auto';

        function decir(t, tipo) {
            if (!estado) return;
            estado.hidden = false;
            estado.className = 'bcta__estado' + (tipo ? ' is-' + tipo : '');
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
