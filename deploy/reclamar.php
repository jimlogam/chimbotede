<?php
/**
 * reclamar.php — "Reclama tu negocio"
 * ============================================================
 * Cómo se llega:
 *   /reclamar                       → elige tu rubro o escribe tu nombre
 *   /reclamar?rubro=bodegas         → ya filtrado por rubro
 *   /reclamar?q=Nombre&rubro=bodegas → resultados buscados (formato de los enlaces viejos)
 *
 * ⚠️ ESTA PÁGINA SUSTITUYE A LA VIEJA reclamar.php. La anterior era de la arquitectura
 * antigua (require de includes/funciones.php + includes/db.php) y en el hosting devolvía
 * ERROR 500; la URL /reclamar (sin .php) no tenía regla y devolvía 404. Los enlaces
 * históricos "/reclamar?q=…&rubro=…" que la gente sigue abriendo (Google, WhatsApp
 * guardado) caían todos en 404: 96 visitas HUMANAS en un solo día (10/09/2026).
 * Ahora caen aquí y funcionan. Reglas en .htaccess (§ URLs amigables).
 *
 * El formulario de reclamo como tal vive en reclamar_negocio.php?slug=<slug>.
 * Guía del módulo: el apartado de reclamos de la guía del proyecto (§19).
 */
require_once __DIR__ . '/config.php';
iniciar_sesion();

$rubro = trim((string)($_GET['rubro'] ?? ''));
$q     = trim((string)($_GET['q'] ?? ''));

$rubros      = contar_negocios_por_categoria();
$mapa_rubros = [];
foreach ($rubros as $r) { $mapa_rubros[(string)$r['slug']] = $r; }

// Un rubro que ya no existe (enlace viejo) no puede romper la página: se ignora.
$rubro_actual = ($rubro !== '' && isset($mapa_rubros[$rubro])) ? $mapa_rubros[$rubro] : null;
$rubro_valido = $rubro_actual ? (string)$rubro_actual['slug'] : '';

$busco      = ($q !== '' || $rubro_valido !== '');
$resultados = $busco ? buscar_negocios_para_reclamar($q, $rubro_valido, 60) : [];

$wa_admin  = url_admin_reclamo([
    'buscado' => $q !== '' ? $q : ($rubro_actual['nombre'] ?? ''),
    'detalle' => 'No encuentro mi negocio en la lista y quiero reclamarlo.',
]);
$wa_altura = url_admin_reclamo(['detalle' => 'Quiero reclamar mi tienda y necesito ayuda.']);

$titulo_pagina      = 'Reclama tu negocio';
$descripcion_pagina = '¿Tu negocio ya está publicado en DeChimbote.com? Encuéntralo, reclámalo gratis y actualiza sus datos, fotos y productos.';
include __DIR__ . '/includes/header.php';
?>

<style>
.rec-wrap { max-width: 900px; margin: 0 auto; padding: 4px 0 30px; }
.rec-hero {
    background: #fff; border: 1px solid var(--color-borde); border-radius: 16px;
    padding: 20px 18px; box-shadow: var(--sombra-tarjeta); text-align: center; margin-bottom: 16px;
}
.rec-hero__ico { font-size: 38px; line-height: 1; }
.rec-hero__t { font-size: 22px; font-weight: 800; color: var(--color-primario); margin: 6px 0 6px; }
.rec-hero__s { font-size: 15px; color: var(--color-texto-claro); line-height: 1.55; max-width: 620px; margin: 0 auto; }

/* Buscador predictivo: input grande (16px = sin zoom en el celular) */
.rec-busca { position: relative; max-width: 620px; margin: 16px auto 0; }
.rec-busca__caja { display: flex; gap: 8px; }
.rec-busca__input {
    flex: 1; min-width: 0; font-size: 17px; padding: 14px 14px; border-radius: 12px;
    border: 2px solid var(--color-borde); background: #fff; font-family: inherit;
}
.rec-busca__input:focus { outline: none; border-color: var(--color-acento); }
.rec-busca__btn {
    background: var(--color-acento); color: #fff; border: 0; border-radius: 12px;
    padding: 0 18px; font-size: 16px; font-weight: 800; cursor: pointer; font-family: inherit;
}
.rec-sug {
    position: absolute; left: 0; right: 0; top: calc(100% + 4px); background: #fff; z-index: 40;
    border: 1px solid var(--color-borde); border-radius: 12px; box-shadow: var(--sombra-hover);
    overflow: hidden; text-align: left; max-height: 340px; overflow-y: auto;
}
.rec-sug a { display: block; padding: 11px 13px; border-bottom: 1px solid #f1ece2; font-size: 16px; }
.rec-sug a:last-child { border-bottom: 0; }
.rec-sug a:hover, .rec-sug a:focus { background: #fdf7ee; }
.rec-sug small { display: block; color: var(--color-texto-claro); font-size: 13px; margin-top: 2px; }
.rec-sug__vacio { padding: 12px 13px; font-size: 15px; color: var(--color-texto-claro); }

.rec-paso { font-size: 15px; font-weight: 800; color: var(--color-texto); margin: 22px 0 10px; }
.rec-rubros { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
.rec-rubro {
    display: flex; align-items: center; gap: 9px; background: #fff; border: 1px solid var(--color-borde);
    border-radius: 12px; padding: 12px 13px; font-size: 15px; font-weight: 600; min-height: 56px;
}
.rec-rubro:hover { border-color: var(--color-acento); box-shadow: var(--sombra-hover); }
.rec-rubro__n { margin-left: auto; font-size: 13px; color: var(--color-texto-claro); font-weight: 700; }

.rec-chip {
    display: inline-flex; align-items: center; gap: 8px; background: #fff; border: 1px solid var(--color-borde);
    border-radius: 999px; padding: 7px 14px; font-size: 15px; font-weight: 700; margin-bottom: 12px;
}
.rec-lista { display: grid; gap: 10px; }
.rec-item {
    display: flex; align-items: center; gap: 12px; background: #fff; border: 1px solid var(--color-borde);
    border-radius: 14px; padding: 14px; box-shadow: var(--sombra-tarjeta);
}
.rec-item:hover { border-color: var(--color-acento); }
.rec-item__ico { font-size: 26px; line-height: 1; }
.rec-item__txt { flex: 1; min-width: 0; }
.rec-item__nom { font-size: 16.5px; font-weight: 800; }
.rec-item__sub { font-size: 13.5px; color: var(--color-texto-claro); margin-top: 2px; }
.rec-item__cta {
    background: var(--color-primario); color: #fff; border-radius: 10px; padding: 10px 12px;
    font-size: 14px; font-weight: 800; white-space: nowrap;
}
@media (max-width: 520px) {
    .rec-item { flex-wrap: wrap; }
    .rec-item__cta { width: 100%; text-align: center; }
    .rec-busca__caja { flex-wrap: wrap; }
    .rec-busca__btn { width: 100%; padding: 13px; }
}

.rec-caja {
    background: #fff; border: 1px solid var(--color-borde); border-radius: 14px; padding: 18px;
    box-shadow: var(--sombra-tarjeta); margin-top: 16px; font-size: 15px; line-height: 1.55;
}
.rec-wa {
    display: inline-flex; align-items: center; justify-content: center; gap: 9px;
    background: var(--color-wsp, #25d366); color: #fff; font-size: 16.5px; font-weight: 800;
    border-radius: 14px; padding: 15px 20px; width: 100%; max-width: 460px; margin-top: 12px;
    box-shadow: 0 6px 18px rgba(37,211,102,.28);
}
.rec-nota { font-size: 13.5px; color: var(--color-texto-claro); margin-top: 10px; }
</style>

<div class="rec-wrap">

    <div class="rec-hero">
        <div class="rec-hero__ico" aria-hidden="true">🗝️</div>
        <h1 class="rec-hero__titulo rec-hero__t">¿Este negocio es tuyo?</h1>
        <p class="rec-hero__s">
            Encuéntralo abajo, tócalo y envíanos tu reclamo: un administrador te da el control de la
            ficha para que corrijas el nombre, la dirección, el WhatsApp, las fotos y tus productos.
            <b>Es gratis</b> y no necesitas crear una cuenta para pedirlo.
        </p>

        <!-- Buscador predictivo: sugiere mientras se escribe (Regla de Oro de UX predictiva).
             Sin JavaScript el formulario sigue funcionando (envía a /reclamar). -->
        <form class="rec-busca" action="<?= url('reclamar') ?>" method="get" role="search" autocomplete="off">
            <?php if ($rubro_valido !== ''): ?>
                <input type="hidden" name="rubro" value="<?= e($rubro_valido) ?>">
            <?php endif; ?>
            <div class="rec-busca__caja">
                <input class="rec-busca__input" type="search" id="recBuscar" name="q" value="<?= e($q) ?>"
                       placeholder="Escribe el nombre de tu negocio… ej: Bodega Marina"
                       aria-label="Buscar mi negocio" autocomplete="off">
                <button class="rec-busca__btn" type="submit">Buscar</button>
            </div>
            <div class="rec-sug" id="recSug" hidden></div>
        </form>
    </div>

    <?php if ($rubro_actual): ?>
        <a class="rec-chip" href="<?= url('reclamar') ?>">
            <?= e($rubro_actual['icono']) ?> <?= e($rubro_actual['nombre']) ?>
            <span style="font-weight:600;color:var(--color-texto-claro)">· cambiar de rubro ↩</span>
        </a>
    <?php endif; ?>

    <?php if ($busco): ?>
        <!-- PASO 2: resultados -->
        <?php if ($resultados): ?>
            <p class="rec-paso">
                <?= count($resultados) ?> negocio(s)
                <?php if ($q !== ''): ?> que coinciden con «<?= e($q) ?>»<?php endif; ?>
                <?php if ($rubro_actual): ?> en <?= e($rubro_actual['nombre']) ?><?php endif; ?>
                · toca el tuyo 👇
            </p>
            <div class="rec-lista">
                <?php foreach ($resultados as $n): ?>
                    <a class="rec-item" href="<?= url('reclamar_negocio.php?slug=' . urlencode((string)$n['slug'])) ?>">
                        <span class="rec-item__ico" aria-hidden="true"><?= e($n['icono'] ?: '🏪') ?></span>
                        <span class="rec-item__txt">
                            <span class="rec-item__nom"><?= e($n['nombre']) ?></span>
                            <span class="rec-item__sub">
                                <?= e($n['rubro'] ?? '') ?>
                                <?= !empty($n['distrito']) ? ' · 📍 ' . e($n['distrito']) : '' ?>
                                <?= !empty($n['direccion']) ? ' · ' . e($n['direccion']) : '' ?>
                            </span>
                        </span>
                        <span class="rec-item__cta">Este es mi negocio →</span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="rec-caja">
                <b>No encontramos ningún negocio<?= $q !== '' ? ' con «' . e($q) . '»' : '' ?><?= $rubro_actual ? ' en ' . e($rubro_actual['nombre']) : '' ?>.</b>
                <p class="rec-nota">Puede estar escrito de otra forma, o quizá todavía no está publicado.</p>
                <ul style="margin:12px 0 0 18px;list-style:disc">
                    <li>Prueba con <b>una sola palabra</b> (ej. «Marina» en vez del nombre completo).</li>
                    <li>¿Tu negocio no está en la lista? <a href="<?= url('crear-tienda') ?>" style="color:var(--color-primario);font-weight:700;text-decoration:underline">Créalo con El maestro 🛠️ en 2 minutos</a> y queda a tu nombre.</li>
                </ul>
                <?php if ($wa_admin !== ''): ?>
                    <a class="rec-wa" href="<?= e($wa_admin) ?>" target="_blank" rel="noopener">
                        <?= wa_icono_svg() ?> Escribir al administrador por WhatsApp
                    </a>
                    <p class="rec-nota">Le llega tu mensaje con el nombre que buscaste y el enlace; él te responde y corrige lo que haga falta.</p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- PASO 1: elegir rubro -->
        <p class="rec-paso">1 · ¿De qué rubro es tu negocio?</p>
        <div class="rec-rubros">
            <?php foreach ($rubros as $c): ?>
                <?php if ((int)$c['n'] <= 0) continue; ?>
                <a class="rec-rubro" href="<?= url('reclamar?rubro=' . urlencode((string)$c['slug'])) ?>">
                    <span aria-hidden="true"><?= e($c['icono']) ?></span>
                    <span><?= e($c['nombre']) ?></span>
                    <span class="rec-rubro__n"><?= (int)$c['n'] ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <p class="rec-nota">
            ¿No encuentras tu rubro? <a href="<?= url('crear-tienda') ?>" style="color:var(--color-primario);font-weight:700;text-decoration:underline">Crea tu tienda con El maestro 🛠️</a> desde cero.
        </p>
    <?php endif; ?>

    <div class="rec-caja" style="text-align:center">
        <b>¿Tu tienda no aparece o te sale un error?</b>
        <p class="rec-nota">Escríbenos y lo arreglamos nosotros: dinos el nombre de tu negocio y en qué distrito está.</p>
        <?php if ($wa_altura !== ''): ?>
            <a class="rec-wa" href="<?= e($wa_altura) ?>" target="_blank" rel="noopener">
                💬 Hablar con el administrador
            </a>
        <?php endif; ?>
        <p class="rec-nota">
            También puedes <a href="<?= url('crear-tienda') ?>" style="color:var(--color-primario);font-weight:700;text-decoration:underline">crear tu tienda con El maestro 🛠️</a>
            o <a href="<?= url('buscar.php') ?>" style="color:var(--color-primario);font-weight:700;text-decoration:underline">buscar en todo el directorio</a>.
        </p>
    </div>
</div>

<script>
/* Sugerencias mientras se escribe (mismo espíritu que el buscador fuzzy del sitio).
   Aquí NO se usa el buscador global de la cabecera a propósito: ese lleva a /neg/<slug>,
   y en esta página tocar un resultado debe llevar al RECLAMO (reclamar_negocio.php?slug=). */
(function () {
    'use strict';
    var input = document.getElementById('recBuscar');
    var caja  = document.getElementById('recSug');
    if (!input || !caja) return;

    var base = <?= json_encode(SITE_URL) ?>;
    var datos = null, fuse = null, timer = null, cargando = false;

    function normalizar(t) {
        return String(t || '').toLowerCase()
            .replace(/[áàäâã]/g, 'a').replace(/[éèëê]/g, 'e').replace(/[íìïî]/g, 'i')
            .replace(/[óòöô]/g, 'o').replace(/[úùüû]/g, 'u').replace(/ñ/g, 'n');
    }

    function cargar() {
        if (datos || cargando) return;
        cargando = true;
        fetch(base + '/api/negocios_json.php')
            .then(function (r) { return r.json(); })
            .then(function (j) {
                datos = Array.isArray(j) ? j : [];
                if (window.Fuse) {
                    fuse = new window.Fuse(datos, {
                        keys: [{ name: 'n', weight: 0.75 }, { name: 'r', weight: 0.15 }, { name: 'd', weight: 0.10 }],
                        threshold: 0.3, ignoreLocation: true, ignoreAccents: true, minMatchCharLength: 2
                    });
                }
            })
            .catch(function () { datos = []; });
    }

    function buscar(texto) {
        var t = normalizar(texto).trim();
        if (t.length < 2 || !datos) return [];
        if (fuse) return fuse.search(texto, { limit: 6 }).map(function (r) { return r.item; });
        var out = [];
        for (var i = 0; i < datos.length && out.length < 6; i++) {
            if (normalizar(datos[i].n).indexOf(t) !== -1) out.push(datos[i]);
        }
        return out;
    }

    function pintar(items, texto) {
        if (!items.length) {
            caja.innerHTML = '<div class="rec-sug__vacio">No encontramos «' + esc(texto) +
                '». Prueba con una palabra o <a href="' + base + '/reclamar" style="font-weight:700;text-decoration:underline">mira los rubros</a>.</div>';
        } else {
            var html = '';
            items.forEach(function (it) {
                html += '<a href="' + base + '/reclamar_negocio.php?slug=' + encodeURIComponent(it.s) + '">' +
                        '<b>' + esc(it.n) + '</b>' +
                        '<small>' + esc(it.r || '') + (it.d ? ' · 📍 ' + esc(it.d) : '') + ' · toca para reclamarlo</small>' +
                        '</a>';
            });
            caja.innerHTML = html;
        }
        caja.hidden = false;
    }

    function esc(t) {
        return String(t == null ? '' : t).replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    input.addEventListener('input', function () {
        cargar();
        clearTimeout(timer);
        var texto = input.value;
        timer = setTimeout(function () {
            if (normalizar(texto).trim().length < 2) { caja.hidden = true; caja.innerHTML = ''; return; }
            if (!datos) { setTimeout(function () { pintar(buscar(texto), texto); }, 350); return; }
            pintar(buscar(texto), texto);
        }, 180);
    });

    input.addEventListener('blur', function () { setTimeout(function () { caja.hidden = true; }, 220); });
    input.addEventListener('focus', function () { if (caja.innerHTML) caja.hidden = false; });
    document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape') caja.hidden = true; });
    cargar();
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
