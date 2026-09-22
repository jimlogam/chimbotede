<?php
/**
 * panel_reco.php — "🤝 Proveedores y alianzas recomendadas" del PANEL DEL VENDEDOR
 * ==============================================================================
 * El carrusel 🤝 de la ficha mira desde el COMPRADOR. Aquí se invierte la mirada y se
 * le muestra al DUEÑO su cadena de suministro, con la MISMA data (directorio_afinidades):
 *
 *   · 🚚 Proveedores que pueden surtirte  → rubros complementarios, catálogo primero
 *   · 🏭 Tiendas que podrían comprarte    → (mayorista/proveedor) la INVERSA del mapa
 *   · 🤝 Alianzas para crecer juntos      → el motor M2, sin repetir tarjetas
 *
 * ── v2 (2026-09-12, pedido del jefe) ─────────────────────────────────────────────
 * El problema: la sección pintaba los bloques UNA VEZ POR NEGOCIO. La cuenta del jefe
 * tiene 48 negocios → la página medía 58 207 px de alto, de los cuales 52 205 px eran
 * esta sección: 94 bloques, 468 tarjetas y los títulos "Proveedores que pueden surtirte"
 * y "Alianzas para crecer juntos" repetidos 47 veces cada uno.
 *
 * La regla nueva: **un negocio por vez**. Se elige con los chips de arriba (?reco=<id>)
 * y solo se pintan sus 3 bloques, cada tipo con SU COLOR (azul = te surte, morado = te
 * compra, ámbar = aliado). Nunca dos bloques del mismo tipo en la página.
 *
 * Motor: includes/helpers.php → categorias_afinidad(), obtener_negocios_panel(),
 * render_cards_panel(). Se llama desde panel.php con una sola línea:
 *     <?= function_exists('panel_reco_html') ? panel_reco_html() : '' ?>
 *
 * Guía del módulo: GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md §4.
 */

if (!function_exists('panel_reco_html')) {

    /** Negocios del dueño con lo que necesita el módulo (rubro, distrito y tipo de vendedor). */
    function panel_reco_negocios($usuario_id) {
        $usuario_id = (int)$usuario_id;
        if ($usuario_id <= 0) return [];
        $sql = "SELECT n.id, n.nombre, n.slug, n.estado, n.categoria_id, n.distrito_id, n.ubicacion_tipo,
                       c.nombre AS categoria_nombre, c.icono AS categoria_icono, c.slug AS categoria_slug,
                       d.nombre AS distrito_nombre
                FROM directorio_negocios n
                LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                LEFT JOIN directorio_distritos d ON d.id = n.distrito_id
                WHERE n.dueno_id = ?
                ORDER BY n.creado_en DESC";
        try {
            $st = db()->prepare($sql);
            $st->execute([$usuario_id]);
            return $st->fetchAll();
        } catch (Throwable $e) {
            error_log('panel_reco_negocios: ' . $e->getMessage());
            return [];
        }
    }

    /** CSS + JS del módulo (inline: es un componente puntual, no hay que subir ?v=). */
    function panel_reco_estilos() {
        static $impreso = false;
        if ($impreso) return '';
        $impreso = true;
        return <<<'HTML'
<style>
/* ⚠️ Los colores de los bloques son SEMÁNTICOS (azul = te surte, morado = te compra,
   ámbar = aliado): no salen de la paleta del dueño, igual que las tarjetas de arriba. */
.pv-reco{background:#fff;border:1px solid var(--color-borde);border-radius:16px;padding:16px;margin-bottom:24px;box-shadow:var(--sombra-tarjeta);scroll-margin-top:122px}
.pv-reco__h{font-size:19px;margin:0 0 6px;line-height:1.25}
.pv-reco__intro{font-size:13.5px;color:var(--color-texto-claro);margin:0;line-height:1.55;max-width:78ch}
.pv-reco__intro b{color:var(--color-texto)}
/* ── Elegir negocio ── */
.pv-pick{margin-top:14px;border-top:1px dashed var(--color-borde);padding-top:12px}
.pv-pick__l{display:block;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:var(--color-texto-claro);margin-bottom:8px}
.pv-pick__caja{display:flex;flex-wrap:wrap;gap:7px;max-height:138px;overflow-y:auto;padding:2px}
.pv-pick__chip{display:inline-flex;align-items:center;gap:6px;background:#faf7f2;border:1px solid var(--color-borde);border-radius:999px;padding:7px 12px;font-size:13.5px;font-weight:600;text-decoration:none;color:var(--color-texto);max-width:100%}
.pv-pick__chip:hover{border-color:var(--color-acento)}
.pv-pick__chip--on{background:var(--color-primario);border-color:var(--color-primario);color:#fff}
.pv-pick__nom{display:block;max-width:210px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
/* ── Negocio elegido ── */
.pv-ctx{display:flex;align-items:center;gap:11px;margin-top:14px;background:#faf7f2;border:1px solid var(--color-borde);border-radius:13px;padding:10px 12px}
.pv-ctx__ico{width:44px;height:44px;border-radius:12px;background:#fff;border:1px solid var(--color-borde);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0}
.pv-ctx__nom{font-weight:800;font-size:16px;line-height:1.2}
.pv-ctx__nom a{color:inherit;text-decoration:none}
.pv-ctx__meta{font-size:12.5px;color:var(--color-texto-claro);margin-top:3px;line-height:1.4}
/* ── Leyenda de colores ── */
.pv-leyenda{display:flex;flex-wrap:wrap;gap:12px;margin:12px 0 0;font-size:12px;color:var(--color-texto-claro)}
.pv-leyenda span{display:inline-flex;align-items:center;gap:5px}
.pv-leyenda i{width:11px;height:11px;border-radius:3px;display:block;flex-shrink:0}
/* ── Filtro ── */
.pv-filtro{width:100%;font-size:16px;padding:10px 12px;border:1px solid var(--color-borde);border-radius:10px;margin:14px 0 0;box-sizing:border-box}
.pv-filtro:focus{outline:2px solid var(--color-acento);outline-offset:0}
/* ── Bloques de color ── */
.pv-bloques{display:flex;flex-direction:column;gap:14px;margin-top:14px}
.pv-bloque{border:1px solid var(--color-borde);border-left:5px solid #94a3b8;border-radius:13px;padding:12px;background:#fff}
.pv-bloque__h{display:flex;align-items:center;gap:8px;font-weight:800;font-size:15.5px;line-height:1.25;flex-wrap:wrap}
.pv-bloque__ico{width:27px;height:27px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0;background:#eef2ff}
.pv-bloque__n{border-radius:999px;padding:1px 9px;font-size:12px;font-weight:800;background:#eef2ff;color:#3730a3}
.pv-bloque__sub{font-size:12.5px;color:var(--color-texto-claro);line-height:1.45;margin:6px 0 10px;max-width:78ch}
.pv-bloque--surte{border-left-color:#2563eb;background:linear-gradient(180deg,#f5f9ff,#fff 64px)}
.pv-bloque--surte .pv-bloque__h{color:#1d4ed8}
.pv-bloque--surte .pv-bloque__ico,.pv-bloque--surte .pv-bloque__n{background:#dbeafe;color:#1e40af}
.pv-bloque--compra{border-left-color:#7c3aed;background:linear-gradient(180deg,#f8f6ff,#fff 64px)}
.pv-bloque--compra .pv-bloque__h{color:#6d28d9}
.pv-bloque--compra .pv-bloque__ico,.pv-bloque--compra .pv-bloque__n{background:#ede9fe;color:#5b21b6}
.pv-bloque--aliado{border-left-color:#d97706;background:linear-gradient(180deg,#fffaf1,#fff 64px)}
.pv-bloque--aliado .pv-bloque__h{color:#b45309}
.pv-bloque--aliado .pv-bloque__ico,.pv-bloque--aliado .pv-bloque__n{background:#fef3c7;color:#92400e}
/* ── Tarjetas ── */
.pv-lista{display:flex;flex-direction:column;gap:8px}
.pv-card{display:flex;align-items:center;gap:10px;background:#fff;border:1px solid var(--color-borde);border-radius:12px;padding:8px 10px}
.pv-card__foto{position:relative;width:54px;height:54px;border-radius:10px;overflow:hidden;flex-shrink:0;background:#f3f4f6;display:block}
.pv-card__foto img{width:100%;height:100%;object-fit:cover;display:block}
.pv-card__badge{position:absolute;left:3px;bottom:3px;font-size:12px;background:rgba(0,0,0,.55);border-radius:6px;padding:0 3px;line-height:1.5}
.pv-card__info{flex:1;min-width:0}
.pv-card__nom{display:block;font-weight:700;font-size:14.5px;color:var(--color-texto);text-decoration:none;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pv-card__meta{font-size:12px;color:var(--color-texto-claro);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.pv-card__meta2{font-size:11.5px;color:var(--color-texto-claro);display:flex;gap:8px;flex-wrap:wrap;margin-top:3px;align-items:center}
.pv-card__cerca{background:#dcfce7;color:#15803d;border-radius:999px;padding:1px 7px;font-weight:700}
.pv-card__wsp{flex-shrink:0;width:40px;height:40px;border-radius:50%;background:#dcfce7;color:#15803d;display:flex;align-items:center;justify-content:center;font-size:19px;text-decoration:none;border:1px solid #86efac}
.pv-vacio{font-size:13px;color:var(--color-texto-claro);background:#faf7f2;border:1px dashed var(--color-borde);border-radius:10px;padding:12px;line-height:1.5}
.pv-sinres{font-size:13px;color:var(--color-texto-claro);padding:12px 2px}
.pv-pie{margin-top:12px;border-top:1px dashed var(--color-borde);padding-top:10px}
.pv-pie a{font-size:13.5px;font-weight:700;color:var(--color-primario);text-decoration:none}
.pv-cta{display:flex;flex-wrap:wrap;gap:8px;margin-top:12px}
.pv-cta a{display:inline-flex;align-items:center;gap:6px;background:#fef3c7;color:#92400e;border:1px solid #fde68a;border-radius:999px;padding:9px 15px;font-weight:700;font-size:13.5px;text-decoration:none}
@media (max-width:480px){
  .pv-reco{padding:13px}
  .pv-bloque{padding:11px}
  .pv-card__nom{font-size:14px}
}
</style>
<script>
function pvNorm(s){return (s||'').toString().toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g,'');}
function pvFiltrar(input){
  var caja = input.closest ? input.closest('.pv-reco') : null; if(!caja) return;
  var q = pvNorm(input.value.trim()); var visibles = 0;
  var tarjetas = caja.querySelectorAll('.pv-card');
  for (var i=0;i<tarjetas.length;i++){
    var c = tarjetas[i];
    var ok = !q || pvNorm(c.getAttribute('data-busca')||'').indexOf(q) >= 0;
    c.style.display = ok ? '' : 'none';
    if (ok) visibles++;
  }
  var bloques = caja.querySelectorAll('.pv-bloque');
  for (var j=0;j<bloques.length;j++){
    var b = bloques[j]; var n = 0;
    var tc = b.querySelectorAll('.pv-card');
    for (var k=0;k<tc.length;k++){ if (tc[k].style.display !== 'none') n++; }
    b.style.display = n ? '' : 'none';
  }
  var sin = caja.querySelector('.pv-sinres'); if(sin) sin.hidden = visibles > 0;
}
/* Al tocar un negocio (?reco=…#recomendados) el navegador salta ANTES de que carguen las
   fotos y la sección se queda fuera de sitio: se recoloca al terminar la carga.
   'instant' a propósito: el <html> del sitio usa scroll-behavior:smooth y aquí no queremos
   una animación larga desde arriba, sino caer ya en la sección. */
(function(){
  if (location.hash !== '#recomendados') return;
  var ir = function(){ var s = document.getElementById('recomendados'); if (s) s.scrollIntoView({block:'start', behavior:'instant'}); };
  if (document.readyState === 'complete') ir(); else window.addEventListener('load', ir);
})();
</script>
HTML;
    }

    /**
     * Sección completa. Devuelve '' si no hay sesión; un aviso amable si el usuario no tiene
     * negocios o si su negocio aún no tiene rubro (sin romper nunca el panel).
     * $usuario_id permite previsualizar la sección de otro dueño (pruebas de solo lectura).
     * ?reco=<id> elige el negocio (los chips de arriba); si falta o no es suyo, se usa el
     * negocio más reciente que tenga rubro.
     */
    function panel_reco_html($usuario_id = null) {
        $u   = usuario_actual();
        $uid = $usuario_id ? (int)$usuario_id : (int)($u['id'] ?? 0);
        if ($uid <= 0) return '';

        $negocios = panel_reco_negocios($uid);
        $html  = panel_reco_estilos();
        $html .= '<section class="pv-reco" id="recomendados">';
        $html .= '<h2 class="pv-reco__h">🤝 Proveedores y alianzas recomendadas</h2>';

        if (!$negocios) {
            $html .= '<p class="pv-reco__intro">Cuando registres tu negocio verás aquí <b>quién puede surtirte</b>, <b>quién puede comprarte</b> y <b>con quién te conviene aliarte</b> para vender más.</p>';
            $html .= '<div class="pv-cta"><a href="' . e(url('crear-tienda')) . '">🛠️ Crear mi tienda</a>'
                   . '<a href="' . e(url('buscar.php')) . '">🔍 Buscar tiendas</a></div>';
            $html .= '</section>';
            return $html;
        }

        $cuantos = count($negocios);

        // ── ¿De qué negocio hablamos? (uno por vez: nunca los bloques repetidos) ──
        $sel_id = isset($_GET['reco']) ? (int)$_GET['reco'] : 0;
        $sel = null;
        if ($sel_id) {
            foreach ($negocios as $n) {
                if ((int)$n['id'] === $sel_id) { $sel = $n; break; }
            }
        }
        if (!$sel) {
            foreach ($negocios as $n) {
                if (!empty($n['categoria_id'])) { $sel = $n; break; }
            }
        }
        if (!$sel) $sel = $negocios[0];

        if ($cuantos === 1) {
            $html .= '<p class="pv-reco__intro">Estas son las tiendas que complementan tu negocio: <b>quién puede surtirte</b>, <b>quién puede comprarte</b> y <b>con quién te conviene aliarte</b>. El botón de WhatsApp de cada tarjeta abre el chat con el mensaje ya escrito.</p>';
        } else {
            $html .= '<p class="pv-reco__intro">Las recomendaciones se calculan según el rubro de cada negocio, así que se revisan <b>de a uno</b>: elige el que quieras ver y aparecerán sus proveedores, sus compradores y sus aliados, cada grupo en su bloque de color. El botón de WhatsApp de cada tarjeta abre el chat con el mensaje ya escrito.</p>';
        }

        // ── Chips para cambiar de negocio ──
        if ($cuantos > 1) {
            $html .= '<div class="pv-pick"><span class="pv-pick__l">Elige el negocio (' . $cuantos . ')</span><div class="pv-pick__caja">';
            foreach ($negocios as $n) {
                $activo = ((int)$n['id'] === (int)$sel['id']) ? ' pv-pick__chip--on' : '';
                $titulo = $n['nombre'] . (!empty($n['categoria_nombre']) ? ' · ' . $n['categoria_nombre'] : '');
                // ⛔ SIN ANCLAS (orden del jefe, 2026-09-21): llevaba a `panel.php?reco=ID#recomendados`.
                //    El parámetro `reco` ya dice qué proveedor mostrar: el ancla sobraba.
                $html .= '<a class="pv-pick__chip' . $activo . '" href="' . e(url('panel.php?reco=' . (int)$n['id'])) . '" title="' . e($titulo) . '">'
                       . '<span aria-hidden="true">' . e($n['categoria_icono'] ?: '🏪') . '</span>'
                       . '<span class="pv-pick__nom">' . e($n['nombre']) . '</span></a>';
            }
            $html .= '</div></div>';
        }

        // ── Negocio elegido ──
        $es_mayorista = (($sel['ubicacion_tipo'] ?? '') === 'mayorista');
        $afines_n = 0;
        if (!empty($sel['categoria_id'])) {
            $afines_n = count(categorias_afinidad((int)$sel['categoria_id'],
                            $es_mayorista ? 'inversa' : 'directa',
                            (string)($sel['categoria_nombre'] ?? '')));
        }
        $html .= '<div class="pv-ctx">'
               . '<span class="pv-ctx__ico" aria-hidden="true">' . e($sel['categoria_icono'] ?: '🏪') . '</span>'
               . '<div style="min-width:0">'
               . '<div class="pv-ctx__nom"><a href="' . e(url_negocio($sel['slug'])) . '">' . e($sel['nombre']) . '</a></div>'
               . '<div class="pv-ctx__meta">' . e($sel['categoria_nombre'] ?: 'Sin rubro')
               . (!empty($sel['distrito_nombre']) ? ' · 📍 ' . e($sel['distrito_nombre']) : '')
               . ($es_mayorista ? ' · 🏭 Mayorista / Proveedor' : '')
               . ($afines_n ? ' · 🔗 ' . $afines_n . ' rubro' . ($afines_n === 1 ? '' : 's') . ' afine' . ($afines_n === 1 ? '' : 's') : '')
               . '</div></div></div>';

        // ── Las tarjetas de este negocio ──
        $principal = [];
        $aliados   = [];
        if (!empty($sel['categoria_id'])) {
            $principal = obtener_negocios_panel((int)$sel['categoria_id'], (int)$sel['id'], $uid, $es_mayorista ? 'compradores' : 'proveedores', 6);
            $ids = [];
            foreach ($principal as $r) { $ids[] = (int)$r['id']; }
            $aliados = obtener_negocios_panel((int)$sel['categoria_id'], (int)$sel['id'], $uid, 'alianzas', 4, $ids);
        }

        // Marca las que están en el mismo distrito (el motor ya las pone primero: esto lo explica).
        // Solo se marca si DISCRIMINA: si las 10 son de tu distrito, la etiqueta repetida sobra.
        $mi_distrito = (string)($sel['distrito_nombre'] ?? '');
        $marcar_cerca = function (array $filas) use ($mi_distrito) {
            if ($mi_distrito === '' || !$filas) return $filas;
            $dentro = 0;
            foreach ($filas as $f) {
                if ((string)($f['distrito_nombre'] ?? '') === $mi_distrito) $dentro++;
            }
            if ($dentro === 0 || $dentro === count($filas)) return $filas;
            foreach ($filas as &$f) {
                $f['en_mi_distrito'] = ((string)($f['distrito_nombre'] ?? '') === $mi_distrito);
            }
            unset($f);
            return $filas;
        };
        $principal = $marcar_cerca($principal);
        $aliados   = $marcar_cerca($aliados);

        $total = count($principal) + count($aliados);

        if (empty($sel['categoria_id'])) {
            $html .= '<div class="pv-vacio" style="margin-top:12px">A <b>' . e($sel['nombre']) . '</b> le falta el <b>rubro</b> asignado. Las recomendaciones se calculan por rubro: escríbenos y lo completamos para que aparezcan sus proveedores y sus aliados.</div>';
            $html .= '<div class="pv-pie"><a href="' . e(url('buscar.php')) . '">🔍 Mientras tanto, busca tiendas en el directorio →</a></div>';
            $html .= '</section>';
            return $html;
        }
        if (!$total) {
            $html .= '<div class="pv-vacio" style="margin-top:12px">Todavía no hay tiendas con rubros complementarios al de <b>' . e($sel['categoria_nombre'] ?? '') . '</b>. La red de aliados crece cada semana: vuelve a revisarlo en unos días o busca directamente en el directorio.</div>';
            $html .= '<div class="pv-pie"><a href="' . e(url('buscar.php')) . '">🔍 Buscar tiendas en el directorio →</a></div>';
            $html .= '</section>';
            return $html;
        }

        // Leyenda de colores (solo si hay más de un bloque: con uno solo sobra).
        $tipos = [];
        if ($principal) $tipos[] = $es_mayorista ? ['#7c3aed', 'Comprador'] : ['#2563eb', 'Proveedor'];
        if ($aliados)   $tipos[] = ['#d97706', 'Aliado'];
        if (count($tipos) > 1) {
            $html .= '<div class="pv-leyenda">';
            foreach ($tipos as $t) {
                $html .= '<span><i style="background:' . $t[0] . '"></i>' . e($t[1]) . '</span>';
            }
            $html .= '</div>';
        }

        if ($total > 4) {
            $html .= '<input class="pv-filtro" type="search" inputmode="search" autocomplete="off" aria-label="Filtrar recomendaciones" placeholder="🔎 Escribe rubro, tienda o distrito…" oninput="pvFiltrar(this)">';
        }

        $html .= '<div class="pv-bloques">';

        if ($principal) {
            if ($es_mayorista) {
                $clase = 'pv-bloque--compra';
                $ico   = '🏭';
                $tit   = 'Tiendas que podrían comprarte';
                $sub   = 'Por el rubro que vendes, estos comercios son tus compradoras naturales. Ofréceles precio por volumen y muéstrales tu catálogo completo.';
            } else {
                $clase = 'pv-bloque--surte';
                $ico   = '🚚';
                $tit   = 'Proveedores que pueden surtirte';
                $sub   = 'Venden lo que se complementa con tu rubro. Van primero los que tienen más catálogo publicado —son los que mejor te pueden abastecer— y los de tu distrito.';
            }
            $html .= '<div class="pv-bloque ' . $clase . '">'
                   . '<div class="pv-bloque__h"><span class="pv-bloque__ico" aria-hidden="true">' . $ico . '</span>'
                   . e($tit) . ' <span class="pv-bloque__n">' . count($principal) . '</span></div>'
                   . '<div class="pv-bloque__sub">' . e($sub) . '</div>'
                   . render_cards_panel($principal)
                   . '</div>';
        }

        if ($aliados) {
            $sub_al = $es_mayorista
                ? 'Comercios que ni te compran ni te surten, pero caminan con lo tuyo: los clientes de uno necesitan al otro. Menciónense y armen combos.'
                : 'Rubros que combinan con el tuyo y que no salieron arriba. Los clientes de una tienda necesitan a la otra: menciónense, compartan promos y armen combos.';
            $html .= '<div class="pv-bloque pv-bloque--aliado">'
                   . '<div class="pv-bloque__h"><span class="pv-bloque__ico" aria-hidden="true">🤝</span>'
                   . 'Alianzas para crecer juntos <span class="pv-bloque__n">' . count($aliados) . '</span></div>'
                   . '<div class="pv-bloque__sub">' . e($sub_al) . '</div>'
                   . render_cards_panel($aliados)
                   . '</div>';
        }

        $html .= '</div>'; // .pv-bloques

        $html .= '<div class="pv-sinres" hidden>Ninguna tienda coincide con lo que escribiste. Borra el filtro y vuelven a salir todas.</div>';
        $html .= '<div class="pv-pie"><a href="' . e(url('buscar.php')) . '">🔍 Buscar más tiendas en el directorio →</a></div>';
        $html .= '</section>';
        return $html;
    }
}
