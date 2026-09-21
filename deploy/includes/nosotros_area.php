<?php
/**
 * includes/nosotros_area.php — 🏪 EL ÁREA «NOSOTROS» (estilos, menú interno y datos)
 * =================================================================================
 * 🔴 ORDEN DEL JEFE (2026-09-21, 3.ª orden — la que manda hoy): **NADA DE ANCLAS.** *«No quiero que
 *    trabajes con anclas dentro del sitio… creo que sean páginas separadas, cada una con su área
 *    respectiva… por ejemplo Novedades es una página, Precios es una página, una landing page, y
 *    "Cómo se usa este sitio" también es una landing page»*. Y que el visitante pueda moverse entre
 *    esas secciones con un **menú interno** del área.
 *
 * Por eso el área quedó partida en CUATRO PÁGINAS, cada una con su URL propia (ninguna con `#`):
 *
 *   · `/nosotros`       → portada del área (presentación, números y accesos)   ← `nosotros.php`
 *   · `/como-se-usa`    → landing de la guía de uso                            ← `como-se-usa.php`
 *   · `/novedades`      → landing de lo que nos diferencia                     ← `novedades.php`
 *   · `/precios`        → landing de los 5 planes                              ← `precios.php`
 *
 * Este archivo concentra lo que las cuatro comparten: **los estilos**, el **menú interno del área**
 * (`nosotros_area_menu()`), los **datos** (planes, novedades, números del sitio) y el **bloque de
 * documentación oficial**. Así una página del área es un archivo corto que solo tiene su contenido.
 *
 * ⚠️ El menú interno marca la página actual con `is-actual`. Si se agrega una sección al área, se
 *    agrega en `nosotros_area_menu()` (una línea) y se le da su página y su regla en el `.htaccess`.
 */

if (!function_exists('nosotros_area_css')) {

    /** Los estilos del área (se imprimen una sola vez por página). */
    function nosotros_area_css() {
        static $impreso = false;
        if ($impreso) return;
        $impreso = true;
        ?>
<style>
/* ==========================================================================
   ÁREA NOSOTROS (portada + las tres landing pages) — móvil primero.
   🎨 ÓRDENES DEL JEFE (2026-09-21, 4.ª vuelta):
     · **A TODO EL ANCHO**: la sección es de lectura y no puede quedar como una columna
       angosta centrada con aire a los costados. `max-width: none` y un margen mínimo.
     · **BOTONES CON 3D**: los botones tienen que PARECER botones (volumen, brillo, sombra
       inferior y hundido al pulsar): la clase base es **`.b3d`** y sus variantes de color.
     · **TARJETAS SEPARADAS POR COLORES**: cada tarjeta lleva su color (el PHP le pone
       `--tk` = color fuerte y `--tkt` = fondo teñido con `nosotros_paleta()`).
   ========================================================================== */
.ns-wrap{width:100%;max-width:none;margin:0;padding:12px 10px 40px}

/* ===== BOTONES 3D (clase base que usan el área y la documentación) =====
   El truco del volumen: una sombra sólida debajo (el «costado» de la tecla), un brillo
   diagonal encima y las luces interiores (arriba claro, abajo oscuro). Al pulsar, la tecla
   baja: la sombra se acorta y el botón se hunde. */
.b3d{position:relative;overflow:hidden;display:inline-flex;align-items:center;justify-content:center;gap:9px;
  box-sizing:border-box;border:0;border-radius:12px;padding:13px 18px;font-family:inherit;
  font-size:16px;font-weight:800;line-height:1.2;text-decoration:none;text-align:center;color:#fff;
  cursor:pointer;
  background:linear-gradient(180deg,var(--b3a,#9c1030) 0%,var(--b3b,#6d071a) 100%);
  box-shadow:0 5px 0 0 var(--b3c,#3d040f),0 9px 18px rgba(0,0,0,.22),
    inset 0 2px 0 rgba(255,255,255,.42),inset 0 -3px 0 rgba(0,0,0,.16);
  transition:transform .1s ease,box-shadow .1s ease,filter .12s ease}
.b3d::after{content:'';position:absolute;inset:0;pointer-events:none;
  background:linear-gradient(112deg,rgba(255,255,255,.34) 0%,rgba(255,255,255,.10) 42%,rgba(255,255,255,0) 66%)}
.b3d>span,.b3d>svg{position:relative;z-index:1}
.b3d:hover{filter:brightness(1.07);transform:translateY(-1px);
  box-shadow:0 6px 0 0 var(--b3c,#3d040f),0 12px 22px rgba(0,0,0,.26),
    inset 0 2px 0 rgba(255,255,255,.45),inset 0 -3px 0 rgba(0,0,0,.16)}
.b3d:active{transform:translateY(4px);
  box-shadow:0 1px 0 0 var(--b3c,#3d040f),0 3px 8px rgba(0,0,0,.2),inset 0 2px 0 rgba(255,255,255,.3)}
.b3d:focus-visible{outline:3px solid #fbd7a4;outline-offset:2px}
/* Los colores de cada botón */
.b3d--granate{--b3a:#9c1030;--b3b:#6d071a;--b3c:#3d040f}
.b3d--naranja{--b3a:#f59e0b;--b3b:#c2410c;--b3c:#7c2d12}
.b3d--wa{--b3a:#5ce08a;--b3b:#25d366;--b3c:#128c4a}
.b3d--verde{--b3a:#22c55e;--b3b:#15803d;--b3c:#0b5227}
.b3d--azul{--b3a:#3b82f6;--b3b:#1d4ed8;--b3c:#122e7c}
.b3d--violeta{--b3a:#8b5cf6;--b3b:#6d28d9;--b3c:#42188c}
.b3d--rosa{--b3a:#ec4899;--b3b:#be185d;--b3c:#7c0b3c}
.b3d--crema{--b3a:#ffffff;--b3b:#f4ece6;--b3c:#cbb9ad;color:#6d071a}
.b3d--gris{--b3a:#ffffff;--b3b:#f4f4f5;--b3c:#d4d4d8;color:#3f3f46}
/* Tamaños: chico (los atajos) y ancho completo (los botones de acción) */
.b3d--sm{padding:10px 14px;font-size:14.5px;border-radius:10px;
  box-shadow:0 4px 0 0 var(--b3c,#d4d4d8),0 6px 12px rgba(0,0,0,.16),inset 0 1px 0 rgba(255,255,255,.42)}
.b3d--sm:hover{box-shadow:0 5px 0 0 var(--b3c,#d4d4d8),0 9px 16px rgba(0,0,0,.2),inset 0 1px 0 rgba(255,255,255,.45)}
.b3d--sm:active{transform:translateY(3px);box-shadow:0 1px 0 0 var(--b3c,#d4d4d8),0 2px 6px rgba(0,0,0,.16)}
.b3d--full{width:100%}

/* 🧭 EL MENÚ INTERNO DEL ÁREA: la barra que lleva de una sección a otra (sin anclas). */
.ns-menu{display:flex;flex-wrap:wrap;gap:10px;background:#fff;border:1px solid var(--color-borde,#e6ded9);
  border-radius:14px;padding:10px;margin:0 0 16px}
.ns-menu a{flex:0 0 auto}
@media (max-width:640px){
  .ns-menu{flex-wrap:nowrap;overflow-x:auto;-webkit-overflow-scrolling:touch;padding:8px}
  .ns-menu a{white-space:nowrap;padding:12px 15px;font-size:15px}
}

/* Encabezado de una landing del área */
.ns-pagehead{--tk:#6d071a;--tkt:#fff;background:var(--tkt);border:1px solid var(--color-borde,#e6ded9);
  border-left:6px solid var(--tk);border-radius:14px;padding:18px;margin:0 0 16px}
.ns-pagehead__eyebrow{margin:0 0 8px;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;
  color:var(--tk,#6d071a)}
.ns-pagehead h1{margin:0 0 8px;font-size:25px;line-height:1.22;font-weight:800}
.ns-pagehead p{margin:0;font-size:16px;line-height:1.6;color:#4b5563}

/* La cabecera de la portada del área */
.ns-hero{background:linear-gradient(135deg,var(--marca-granate,#6d071a) 0%,var(--marca-granate-osc,#4a0412) 100%);
  color:#fff;border-radius:16px;padding:22px 18px;margin-bottom:16px}
.ns-hero h1{margin:0 0 8px;font-size:26px;line-height:1.2;font-weight:800}
.ns-hero p{margin:0;font-size:16px;line-height:1.6;color:rgba(255,255,255,.94)}
.ns-hero__nums{display:flex;flex-wrap:wrap;gap:8px;margin-top:14px}
.ns-hero__num{background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.32);border-radius:10px;
  padding:7px 11px;font-size:13.5px;font-weight:700}
.ns-hero__num b{font-size:16px}

/* Las tarjetas de acceso a cada sección (cada una su color) */
.ns-indice{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:10px;margin:0 0 20px}
.ns-indice a{--tk:#6d071a;--tkt:#fff;display:flex;align-items:center;gap:12px;text-decoration:none;
  color:var(--color-texto,#222);background:var(--tkt);border:1px solid rgba(0,0,0,.07);
  border-left:6px solid var(--tk);border-radius:12px;padding:14px;font-size:16px;font-weight:700;
  box-shadow:0 2px 0 0 rgba(0,0,0,.06),0 6px 14px rgba(0,0,0,.06)}
.ns-indice a:hover{transform:translateY(-1px);box-shadow:0 3px 0 0 rgba(0,0,0,.08),0 10px 20px rgba(0,0,0,.09)}
.ns-indice a span.ns-indice__ico{font-size:22px;line-height:1}
.ns-indice a span.ns-indice__s{display:block;font-size:13.5px;font-weight:500;color:#5b5b62;margin-top:3px}
.ns-indice a strong{color:var(--tk,#6d071a)}

.ns-h2{margin:24px 0 6px;font-size:23px;line-height:1.25;font-weight:800;color:var(--marca-granate,#6d071a)}
.ns-sub{margin:0 0 14px;font-size:16px;line-height:1.65;color:#4b5563}

/* Las tarjetas generales: color propio por tarjeta (lo pone el PHP con --tk / --tkt) */
.ns-card{--tk:#6d071a;--tkt:#fff;background:var(--tkt);border:1px solid rgba(0,0,0,.07);
  border-left:6px solid var(--tk);border-radius:14px;padding:16px;
  box-shadow:0 2px 0 0 rgba(0,0,0,.05),0 8px 18px rgba(0,0,0,.05)}
.ns-card h3{margin:0 0 10px;font-size:18px;line-height:1.3;color:var(--tk,#6d071a)}

/* La guía: dos caminos (comprar / vender), a todo el ancho */
.ns-guia{display:grid;grid-template-columns:repeat(auto-fit,minmax(420px,1fr));gap:14px}
.ns-pasos{margin:0;padding:0;list-style:none;counter-reset:ns}
.ns-pasos li{position:relative;padding:0 0 12px 40px;font-size:15.5px;line-height:1.55;counter-increment:ns}
.ns-pasos li:last-child{padding-bottom:0}
.ns-pasos li::before{content:counter(ns);position:absolute;left:0;top:0;width:28px;height:28px;border-radius:50%;
  background:linear-gradient(180deg,var(--tk,#6d071a) 0%,rgba(0,0,0,.55) 100%);
  color:#fff;font-size:14px;font-weight:800;display:flex;align-items:center;justify-content:center;
  box-shadow:0 2px 0 0 rgba(0,0,0,.25),inset 0 1px 0 rgba(255,255,255,.4)}
.ns-pasos b{color:var(--tk,#6d071a)}
.ns-atajos{display:flex;flex-wrap:wrap;gap:9px;margin-top:14px}

/* Las novedades: una tarjeta por novedad, cada una su color, y llenan el ancho */
.ns-nov{display:grid;grid-template-columns:repeat(auto-fit,minmax(330px,1fr));gap:12px}
.ns-nov__i{--tk:#6d071a;--tkt:#fff;display:flex;gap:12px;background:var(--tkt);
  border:1px solid rgba(0,0,0,.07);border-left:6px solid var(--tk);border-radius:14px;padding:15px;
  box-shadow:0 2px 0 0 rgba(0,0,0,.05),0 8px 18px rgba(0,0,0,.05)}
.ns-nov__ico{flex:0 0 auto;width:38px;height:38px;border-radius:10px;display:flex;align-items:center;justify-content:center;
  font-size:20px;line-height:1;background:#fff;border:1px solid rgba(0,0,0,.08);
  box-shadow:0 2px 0 0 rgba(0,0,0,.07),inset 0 1px 0 rgba(255,255,255,.6)}
.ns-nov__t{margin:0 0 5px;font-size:16.5px;font-weight:800;line-height:1.3;color:var(--tk,#6d071a)}
.ns-nov__p{margin:0;font-size:15px;line-height:1.6;color:#444}
.ns-nov__a{display:inline-block;margin-top:9px;font-size:14.5px;font-weight:800;color:var(--tk,#6d071a)}

/* Las 5 fichas de precios: cada plan con su color, a todo el ancho */
.ns-planes{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:14px;margin-top:6px}
.ns-plan{--tk:#6d071a;--tkt:#fff;position:relative;background:var(--tkt);border:1px solid rgba(0,0,0,.07);
  border-left:6px solid var(--tk);border-radius:16px;padding:16px;
  box-shadow:0 2px 0 0 rgba(0,0,0,.05),0 10px 20px rgba(0,0,0,.06)}
.ns-plan--premium{box-shadow:0 0 0 2px var(--tk) inset,0 10px 22px rgba(0,0,0,.10)}
.ns-plan__sello{position:absolute;top:-11px;right:14px;background:var(--tk,#6d071a);color:#fff;
  font-size:11.5px;font-weight:800;letter-spacing:.02em;border-radius:999px;padding:4px 10px;
  box-shadow:0 2px 0 0 rgba(0,0,0,.25)}
.ns-plan__head{display:flex;align-items:baseline;gap:9px;flex-wrap:wrap}
.ns-plan__emoji{font-size:22px;line-height:1}
.ns-plan__nombre{font-size:20px;font-weight:800;color:var(--tk,#6d071a)}
.ns-plan__precio{margin-left:auto;text-align:right;line-height:1.1}
.ns-plan__precio b{display:block;font-size:24px;font-weight:800;color:var(--tk,#6d071a)}
.ns-plan__precio span{font-size:12.5px;color:#6b6b6b}
.ns-plan__lema{margin:8px 0 12px;font-size:15.5px;font-weight:600;color:#374151}
.ns-plan__lista{margin:0;padding:0;list-style:none}
.ns-plan__lista li{position:relative;padding:0 0 8px 24px;font-size:15px;line-height:1.55}
.ns-plan__lista li:last-child{padding-bottom:0}
.ns-plan__lista li::before{content:'✔';position:absolute;left:0;top:0;color:var(--tk,#15803d);font-weight:800}
.ns-plan__nota{margin:12px 0 0;font-size:13.5px;line-height:1.5;color:#6b6b6b;
  border-top:1px dashed rgba(0,0,0,.12);padding-top:10px}
.ns-planes__pie{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}
.ns-planes__pie .b3d{flex:1 1 260px}

/* La oferta sin riesgo */
.ns-oferta{margin-top:18px;background:#fff7ed;border:1px solid #f2d1a8;border-left:6px solid #c2410c;
  border-radius:16px;padding:16px;box-shadow:0 2px 0 0 rgba(0,0,0,.05),0 8px 18px rgba(0,0,0,.05)}
.ns-oferta h3{margin:0 0 8px;font-size:18px;line-height:1.3;color:#92400e}
.ns-oferta p{margin:0 0 8px;font-size:15.5px;line-height:1.6;color:#444}
.ns-oferta p:last-child{margin-bottom:0}
.ns-oferta b{color:#92400e}

/* Preguntas rápidas */
.ns-faq{margin-top:20px}
.ns-faq details{background:#fff;border:1px solid rgba(0,0,0,.08);border-left:6px solid var(--marca-granate,#6d071a);
  border-radius:12px;padding:12px 14px;margin-bottom:8px}
.ns-faq summary{font-size:16px;font-weight:700;cursor:pointer;line-height:1.45;color:#4a0412}
.ns-faq p{margin:9px 0 0;font-size:15px;line-height:1.6;color:#444}

.ns-cierre{margin-top:24px;background:linear-gradient(135deg,#a3123c 0%,#d0312a 48%,#f0861c 100%);
  color:#fff;border-radius:16px;padding:18px;text-align:center;
  box-shadow:0 6px 0 0 rgba(90,6,20,.35),0 14px 26px rgba(109,7,26,.25)}
.ns-cierre h3{margin:0 0 8px;font-size:19px;line-height:1.3}
.ns-cierre p{margin:0 0 12px;font-size:15.5px;line-height:1.6;color:rgba(255,255,255,.96)}
.ns-cierre .b3d{max-width:420px;margin:0 auto}

/* 📚 EL BLOQUE DE DOCUMENTACIÓN OFICIAL: tres tarjetas, cada una su color. */
.ns-docs{margin-top:18px;background:#fff;border:1px solid var(--color-borde,#e6ded9);border-radius:16px;
  padding:18px}
.ns-docs__eyebrow{margin:0 0 8px;font-size:11px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;
  color:var(--marca-granate,#6d071a)}
.ns-docs__t{margin:0 0 6px;font-size:19px;font-weight:800;line-height:1.3}
.ns-docs__s{margin:0 0 14px;font-size:15.5px;line-height:1.6;color:#4b5563}
.ns-docs__grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:12px}
.ns-doc{--tk:#6d071a;--tkt:#fdfbfa;display:flex;flex-direction:column;background:var(--tkt);
  border:1px solid rgba(0,0,0,.07);border-left:6px solid var(--tk);border-radius:12px;padding:15px;
  box-shadow:0 2px 0 0 rgba(0,0,0,.05),0 8px 16px rgba(0,0,0,.05)}
.ns-doc__k{margin:0 0 5px;font-size:11px;font-weight:800;letter-spacing:.1em;text-transform:uppercase;color:var(--tk,#8a6d5f)}
.ns-doc__t{margin:0 0 7px;font-size:16.5px;font-weight:800;line-height:1.3;color:#27272a}
.ns-doc__d{margin:0 0 14px;font-size:14.8px;line-height:1.55;color:#4b5563}
.ns-doc__acciones{display:flex;flex-direction:column;gap:9px;margin-top:auto}
.ns-manual{margin:14px 0 0;font-size:15px;line-height:1.65;color:#4b5563}

@media (min-width:700px){
  .ns-hero h1{font-size:31px}
  .ns-pagehead h1{font-size:30px}
}
</style>
        <?php
    }

    /**
     * 🎨 LA PALETA DE LAS TARJETAS (orden del jefe: *«las tarjetas sepáralas por colores»*).
     * Cada tono trae el color fuerte (`tk`), el fondo teñido de la tarjeta (`tkt`) y los tres del
     * botón 3D (`a` claro de arriba, `b` base, `c` el canto de abajo). Las listas rotan por estos
     * tonos, así dos tarjetas vecinas nunca se ven iguales.
     */
    function nosotros_paleta($i = 0) {
        $paleta = [
            ['tk' => '#6d071a', 'tkt' => '#fdf2f4', 'a' => '#9c1030', 'b' => '#6d071a', 'c' => '#3d040f'], // granate (la marca)
            ['tk' => '#c2410c', 'tkt' => '#fff5ed', 'a' => '#f59e0b', 'b' => '#c2410c', 'c' => '#7c2d12'], // naranja
            ['tk' => '#b45309', 'tkt' => '#fffaeb', 'a' => '#fbbf24', 'b' => '#b45309', 'c' => '#78350f'], // ámbar
            ['tk' => '#15803d', 'tkt' => '#f2fdf5', 'a' => '#22c55e', 'b' => '#15803d', 'c' => '#0b5227'], // verde
            ['tk' => '#1d4ed8', 'tkt' => '#f0f6ff', 'a' => '#3b82f6', 'b' => '#1d4ed8', 'c' => '#122e7c'], // azul
            ['tk' => '#6d28d9', 'tkt' => '#f6f3ff', 'a' => '#8b5cf6', 'b' => '#6d28d9', 'c' => '#42188c'], // violeta
            ['tk' => '#0f766e', 'tkt' => '#effcfa', 'a' => '#14b8a6', 'b' => '#0f766e', 'c' => '#084c47'], // turquesa
            ['tk' => '#be185d', 'tkt' => '#fdf2f8', 'a' => '#ec4899', 'b' => '#be185d', 'c' => '#7c0b3c'], // rosa
        ];
        $n = count($paleta);
        $i = (int)$i;
        if ($i < 0) $i = 0;
        return $paleta[$i % $n];
    }

    /** El estilo en línea que le da su color a una tarjeta. */
    function nosotros_tono($i = 0) {
        $t = nosotros_paleta($i);
        return '--tk:' . $t['tk'] . ';--tkt:' . $t['tkt'];
    }

    /** El estilo en línea que le da su color 3D a un botón. */
    function nosotros_tono_boton($i = 0) {
        $t = nosotros_paleta($i);
        return '--b3a:' . $t['a'] . ';--b3b:' . $t['b'] . ';--b3c:' . $t['c'];
    }

    /**
     * 🧭 EL MENÚ INTERNO DEL ÁREA: las cuatro secciones, cada una su página.
     * Los botones llevan el efecto 3D (`.b3d`) y la sección actual se ve pulsada (granate).
     * @param string $actual  'nosotros' | 'como-se-usa' | 'novedades' | 'precios'
     */
    function nosotros_area_menu($actual = '') {
        $secciones = [
            'nosotros'    => ['t' => '🏪 Nosotros',               'u' => url('nosotros')],
            'como-se-usa' => ['t' => '📖 Cómo se usa este sitio', 'u' => url('como-se-usa')],
            'novedades'   => ['t' => '✨ Novedades',              'u' => url('novedades')],
            'precios'     => ['t' => '💰 Precios',                'u' => url('precios')],
        ];
        $html = '<nav class="ns-menu" aria-label="Secciones de Nosotros">';
        foreach ($secciones as $clave => $s) {
            $es = ($clave === $actual);
            $html .= '<a class="b3d ' . ($es ? 'b3d--granate' : 'b3d--crema') . '" href="' . e($s['u']) . '"'
                   . ($es ? ' aria-current="page"' : '') . '><span>' . e($s['t']) . '</span></a>';
        }
        $html .= '</nav>';
        return $html;
    }

    /** Los números del sitio (reales, en try/catch: si una tabla falla, ese número no se pinta). */
    function nosotros_numeros() {
        static $nums = null;
        if ($nums !== null) return $nums;
        $nums = [];
        try { $nums['tiendas']   = (int)db()->query("SELECT COUNT(*) FROM directorio_negocios WHERE estado = 'activo'")->fetchColumn(); } catch (Throwable $e) { }
        try { $nums['productos'] = (int)db()->query("SELECT COUNT(*) FROM directorio_servicios s
                                                       JOIN directorio_negocios n ON n.id = s.negocio_id
                                                      WHERE n.estado = 'activo'")->fetchColumn(); } catch (Throwable $e) { }
        try { $nums['rubros']    = count(obtener_categorias()); } catch (Throwable $e) { }
        try { $nums['distritos'] = count(obtener_distritos_visibles()); } catch (Throwable $e) { }
        return $nums;
    }

    /** La banda de números (la usan la portada del área y las landing que la necesiten). */
    function nosotros_numeros_html() {
        $n = nosotros_numeros();
        if (!$n) return '';
        $html = '<div class="ns-hero__nums">';
        if (!empty($n['tiendas']))   $html .= '<span class="ns-hero__num"><b>' . number_format($n['tiendas']) . '</b> tiendas publicadas</span>';
        if (!empty($n['productos'])) $html .= '<span class="ns-hero__num"><b>' . number_format($n['productos']) . '</b> productos</span>';
        if (!empty($n['rubros']))    $html .= '<span class="ns-hero__num"><b>' . (int)$n['rubros'] . '</b> rubros</span>';
        if (!empty($n['distritos'])) $html .= '<span class="ns-hero__num"><b>' . (int)$n['distritos'] . '</b> distritos</span>';
        return $html . '</div>';
    }

    /** Los 5 planes (dictados por el jefe; los pinta la landing de precios). */
    function nosotros_planes() {
        return [
            [
                'emoji' => '🎁', 'nombre' => 'Gratis', 'precio' => 'S/ 0', 'pago' => 'para siempre',
                'lema'  => 'Recibe clientes a costo cero', 'tono' => '', 'sello' => '',
                'incluye' => [
                    'Tu tienda publicada con tus fotos, tus productos y tus precios',
                    'Tu WhatsApp a la vista: el cliente te escribe directo, sin intermediarios',
                    'Apareces en el buscador, en los rubros y en las noticias del sitio',
                    'Apareces en «📍 Ver negocios cerca de mí» (ubicación GPS de tu local)',
                    'Tus clientes arman su pedido con el carrito ❤️ «Me interesa»',
                    'Opiniones de tus clientes dentro de tu ficha',
                    'Cambias tus fotos, tus precios y tus horarios cuando quieras, desde tu panel',
                    'Estadísticas demostrables: visitas, clics de llamar, mensajes y pedidos reales de tu ficha',
                ],
                'nota' => 'Sin pagar nada y sin comisiones. Es el plan con el que empieza la mayoría.',
            ],
            [
                'emoji' => '📈', 'nombre' => 'Emprende', 'precio' => 'S/ 20', 'pago' => 'al mes',
                'lema'  => 'Hasta 8 videos y estadísticas más exactas', 'tono' => '', 'sello' => '',
                'incluye' => [
                    'Todo lo del plan Gratis',
                    'Hasta 8 videos dentro de tu ficha (tu local por dentro, cómo trabajas, tus promociones)',
                    'Estadísticas más exactas de tu sitio web: qué miran, cuánto se quedan y de dónde llegan',
                    'Mira las estadísticas de otras tiendas que venden lo mismo que tú: cómo se mueve tu rubro',
                ],
                'nota' => 'Es el plan que más se pide: con videos, la ficha vende sola.',
            ],
            [
                'emoji' => '🤖', 'nombre' => 'Vende Más', 'precio' => 'S/ 50', 'pago' => 'al mes',
                'lema'  => 'Tu robot con Inteligencia Artificial y tu sitio a tu gusto', 'tono' => '', 'sello' => '',
                'incluye' => [
                    'Todo lo del plan de S/ 20',
                    'Un robot personalizado que responde a tus clientes con Inteligencia Artificial, entrenado SOLO para ti y SOLO con tus productos',
                    'Tus productos aparecen más arriba en los resultados de búsqueda',
                    'Personalizas los colores de tu sitio web',
                    'Personalizas la forma cómo se muestra tu sitio web',
                ],
                'nota' => 'El robot contesta dudas de precios, stock y horarios a cualquier hora.',
            ],
            [
                'emoji' => '👑', 'nombre' => 'Premium', 'precio' => 'S/ 96', 'pago' => 'al mes',
                'lema'  => 'Tu aplicación Android y todo lo anterior, al máximo',
                'tono'  => 'ns-plan--premium', 'sello' => 'El más completo',
                'incluye' => [
                    'Todo lo de los planes anteriores',
                    'Tu aplicación Android para que tus clientes la descarguen en su celular',
                    'Estadísticas mucho más exactas y en tiempo real',
                    'Visitas de nuestro equipo a tu local para capacitar a tu personal',
                    'Clases de estrategias de marketing para ti y para tu equipo',
                    'Control del producto que vendes con más rotación en todo el distrito',
                    'Llevamos tus ventas fuera de Chimbote con nuestras páginas amigas que también posicionan muy bien en Google',
                ],
                'nota' => 'Pensado para el negocio que ya vende y quiere salir del distrito.',
            ],
            [
                'emoji' => '🤝', 'nombre' => 'Aliados', 'precio' => 'S/ 150', 'pago' => 'al mes',
                'lema'  => 'Para varias tiendas, marcas o cadenas',
                'tono'  => 'ns-plan--aliado', 'sello' => 'Para varios locales',
                'incluye' => [
                    'Todo lo del plan Premium, para hasta 5 locales, marcas o sucursales con un solo pago',
                    'Primera plana: tu banner en la portada del sitio y en tu rubro',
                    'Campañas por temporada armadas con Inteligencia Artificial (Día de la Madre, Navidad, aniversarios y ferias del distrito)',
                    'Presencia en nuestra red de páginas amigas de otras ciudades, además de Chimbote',
                    'Asesor de ventas dedicado por WhatsApp y una reunión de resultados al mes',
                    'Capacitación mensual a tu equipo: fotos, precios, atención y marketing',
                    'Carga masiva de tu catálogo e inventario (precios y stock por lote)',
                    'Estadísticas de toda la red: de dónde vienen las visitas y a dónde llegaron tus ventas',
                    'Informe mensual de lo que más se vendió en tu rubro',
                ],
                'nota' => 'Cero comisiones por venta, igual que todos los planes.',
            ],
        ];
    }

    /** Las novedades: las cuatro que nombró el jefe y las demás que el sitio ya tiene. */
    function nosotros_novedades() {
        return [
            ['🤖', 'Inteligencia Artificial de verdad, trabajando para ti',
             'Con 8 fotos, la IA te arma la tienda: escribe tu descripción, tus textos de venta y clasifica tus productos uno por uno. Y dentro de tu ficha, el 🧭 <b>guía</b> atiende a tus clientes con tus precios, tus horarios y tus productos.',
             'Crear mi tienda con IA', 'crear-tienda'],
            ['📍', 'Negocios con ubicación GPS',
             'Cada local va con su ubicación real. El visitante toca «📍 Ver negocios cerca de mí» y el sitio le muestra lo que tiene al lado: abre el radio de 2 a 5 y a 10 km hasta juntar 20 resultados.',
             '', ''],
            ['📸', 'Tú cambias tus fotos cuando quieras',
             'El dueño entra a su panel desde el celular y cambia su portada, sus fotos de producto y sus precios en el momento: no depende de nadie ni espera a nadie. Y cada foto nueva la IA la clasifica y le escribe su descripción.',
             'Editar mi tienda', 'panel.php'],
            ['📈', 'Estadísticas en tiempo real',
             'Visitas, clics de llamar, mensajes de WhatsApp, pedidos del carrito, búsquedas y soles: lo ves en el momento en que pasa, no a fin de mes.',
             'Ver la información en vivo', 'en-vivo'],
            ['🎵', 'La canción de tu tienda',
             'Cada ficha puede tener su propia canción: el visitante entra y suena. Es lo primero que hace que se acuerden de tu negocio.',
             '', ''],
            ['🛒', 'El carrito ❤️ «Me interesa»',
             'El cliente marca todo lo que quiere y a tu WhatsApp le llega UN solo mensaje con el pedido completo, con precios y cantidades. Se acabó el «¿me pasas el catálogo?».',
             '', ''],
            ['💬', 'Opiniones de tus clientes, con reporte',
             'Las opiniones se publican en tu ficha con su fecha y su nota. Si alguien falta el respeto o miente, se reporta y el equipo lo revisa.',
             '', ''],
            ['🎙️', 'Un buscador que te entiende',
             'Busca aunque escribas con errores de tipeo («cevicheria», «barberia», «gazfitero») y también se puede HABLAR: el visitante dicta lo que busca y aparece.',
             'Buscar negocios', 'buscar.php'],
            ['📰', 'Noticias del día y bolsa de empleos',
             'Las noticias de Chimbote, Nuevo Chimbote y Santa todas las mañanas, y los avisos de trabajo de la zona con su afiche, su sueldo y su WhatsApp.',
             'Ver noticias', 'noticias'],
            ['🏷️', 'Todo ordenado en 40 rubros',
             'Del buscador a la ficha en dos toques: todos los rubros del directorio en una sola página, con las tiendas que hay en cada uno y un campo que filtra al instante.',
             'Ver todos los rubros', 'rubros'],
            ['🔎', 'Hecho para que Google te encuentre',
             'Sitemap al día, datos estructurados de tu negocio y aviso automático a los buscadores cuando publicas: tu ficha es una puerta de entrada desde Google.',
             '', ''],
            ['🧾', 'Ficha completa, no un anuncio suelto',
             'Horario, distrito, referencia de cómo llegar, formas de pago, entrega a domicilio, teléfono, redes sociales y RUC: todo lo que el cliente pregunta antes de ir.',
             '', ''],
            ['🚫', 'Cero comisiones por venta',
             'No te cobramos un porcentaje de lo que vendes: el cliente te escribe directo y la venta es tuya. Nosotros cobramos el plan, nada más.',
             '', ''],
            ['📱', 'Tu aplicación Android (plan Premium)',
             'Con el plan Premium, tus clientes descargan tu aplicación en su celular y te llevan en el bolsillo.',
             'Ver los planes', 'precios'],
        ];
    }

    /** 📚 El bloque de documentación oficial (tres tarjetas, cada una su color, con botones 3D). */
    function nosotros_documentacion_html() {
        $documentos = [
            ['k' => 'Documento I',   't' => 'Políticas de Privacidad y Condiciones Generales de Uso',
             'd' => 'Titularidad exclusiva de los contenidos, régimen de baja del establecimiento, '
                  . 'extracción de la base de datos, criterios editoriales y contenidos prohibidos.',
             'u' => url('privacidad'), 'pdf' => url('documentos/privacidad.pdf')],
            ['k' => 'Documento II',  't' => 'Preguntas Frecuentes',
             'd' => 'Respuestas oficiales a las consultas de mayor recurrencia sobre el uso del directorio, '
                  . 'la publicación, los planes y el tratamiento de los datos personales.',
             'u' => url('preguntas-frecuentes'), 'pdf' => url('documentos/preguntas-frecuentes.pdf')],
            ['k' => 'Documento III', 't' => 'Manual de Uso de la Plataforma',
             'd' => 'Explicación funcional de cada característica: publicación, administración, material '
                  . 'gráfico, catálogo, atención automatizada, estadísticas y geolocalización.',
             'u' => url('manual-de-uso'), 'pdf' => url('documentos/manual.pdf')],
        ];
        ob_start(); ?>
  <div class="ns-docs">
    <p class="ns-docs__eyebrow"><?= e(SITE_NAME) ?> · Documentación oficial</p>
    <p class="ns-docs__t">Los documentos que rigen el servicio</p>
    <p class="ns-docs__s">
      Textos formales, estructurados por capítulos, disponibles para su lectura en línea y su descarga
      en formato PDF.
    </p>

    <div class="ns-docs__grid">
      <?php foreach ($documentos as $i => $d): ?>
        <article class="ns-doc" style="<?= e(nosotros_tono($i)) ?>">
          <p class="ns-doc__k"><?= e($d['k']) ?></p>
          <h3 class="ns-doc__t"><?= e($d['t']) ?></h3>
          <p class="ns-doc__d"><?= e($d['d']) ?></p>
          <div class="ns-doc__acciones">
            <a class="b3d" style="<?= e(nosotros_tono_boton($i)) ?>" href="<?= e($d['u']) ?>"><span>Leer en línea</span></a>
            <a class="b3d b3d--gris" href="<?= e($d['pdf']) ?>"><span>Descargar en PDF</span></a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>
  </div>
        <?php
        return ob_get_clean();
    }
}
