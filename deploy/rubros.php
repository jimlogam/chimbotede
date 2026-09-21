<?php
/**
 * rubros.php — LA PÁGINA DE TODOS LOS RUBROS DEL DIRECTORIO (URL pública: /rubros)
 * ==============================================================================
 * Pedido del jefe (2026-09-19, textual): *«¿existe alguna página donde yo pueda ver los rubros?…
 * una página que me muestre el listado de los rubros así de simple… una página donde se vean TODOS
 * los rubros. Cuando digo rubros me refiero a los NOMBRES DE LAS CATEGORÍAS, no a las tiendas ni a
 * los productos que viven adentro.»*
 *
 * QUÉ ES: **la lista pelada y completa de los rubros** (los nombres de las categorías del
 * directorio), en orden alfabético, cada uno con las tiendas que tiene y su enlace a su página.
 * No es un buscador ni un muro: es el índice del directorio.
 *
 * ⚠️ QUÉ HABÍA ANTES (y por qué esto es nuevo): la lista completa de rubros **no tenía página**.
 *    Existía en tres sitios, ninguno servía para esto:
 *      · la **marquesina** de la cabecera (`includes/header.php`) — pasa rodando y no se puede leer;
 *      · los **18 chips** del menú ☰ — es un pedazo, no la lista;
 *      · el **cajón «Todos los rubros» del Explorer** (`explorer.php`) — está dentro de otra página,
 *        se abre con un toque, y no se puede enlazar ni compartir.
 *    De ahí que el jefe no encontrara «una página donde se vean todos los rubros». Ahora sí: /rubros.
 *
 * 🎯 CÓMO SE USA: se escribe en el campo y la lista se filtra AL INSTANTE (Regla de Oro n.º 1 del
 *    proyecto: todo buscador autocompleta mientras se escribe). Además de por el nombre del rubro se
 *    puede buscar **por lo que la gente pide de verdad** («pollo» → Pollerías, «clavos» →
 *    Ferreterías): las palabras son las mismas del buscador del sitio
 *    (`directorio_categoria_claves`, vía `obtener_claves_categorias()`), y se enseñan bajo el nombre
 *    **solo mientras se está buscando** (así el listado queda limpio cuando no se busca nada).
 *
 * 🔢 EL NÚMERO DE TIENDAS: es el de tiendas **activas** del rubro, contando también los **rubros
 *    extra** de cada tienda (`directorio_negocio_rubros`, hasta 4 rubros por tienda: ver
 *    `rubro_filtro_id()` en `includes/helpers.php`). Es el mismo criterio que la página del rubro
 *    (`categoria.php`), así que los números cuadran con lo que se ve al entrar.
 *
 * 🔗 URL: se sirve en `/rubros` (y también en `/categorias`) por las reglas `^rubros/?$` y
 *    `^categorias/?$` del `.htaccess`. El archivo también responde a pelo (`/rubros.php`), como todo
 *    el sitio. Guía del módulo de rubros: `GUIA_TIENDAS_PORTADAS_Y_RUBROS.md`.
 */

require_once __DIR__ . '/config.php';

// ============================================================================
// 1) LOS RUBROS (los nombres de las categorías activas)
// ============================================================================
$rubros = [];
try {
    $rubros = obtener_categorias();   // orden de la base (orden ASC, nombre ASC)
} catch (Throwable $e) {
    $rubros = [];
}

// ============================================================================
// 2) CUÁNTAS TIENDAS TIENE CADA RUBRO
//    Se cuentan las ACTIVAS y se mira el rubro principal Y los rubros extra, sin contar dos veces a
//    la misma tienda (una tienda que vive en dos rubros cuenta 1 en cada uno, no 2 en el mismo).
//    Si algo de esto falla, la página se sigue viendo: solo salen los rubros sin número.
// ============================================================================
$conteo = [];
try {
    $vistos = [];
    $rs = db()->query("SELECT id, categoria_id FROM directorio_negocios WHERE estado = 'activo'");
    foreach ($rs as $f) {
        $cat = (int)($f['categoria_id'] ?? 0);
        if ($cat > 0) $vistos[$cat][(int)$f['id']] = 1;
    }
    if (rubros_multi_ok()) {
        $rs = db()->query("SELECT rr.categoria_id, rr.negocio_id
                             FROM directorio_negocio_rubros rr
                             JOIN directorio_negocios n ON n.id = rr.negocio_id
                            WHERE n.estado = 'activo'");
        foreach ($rs as $f) {
            $cat = (int)($f['categoria_id'] ?? 0);
            if ($cat > 0) $vistos[$cat][(int)$f['negocio_id']] = 1;
        }
    }
    foreach ($vistos as $cat => $ids) $conteo[(int)$cat] = count($ids);
} catch (Throwable $e) {
    $conteo = [];
}

// ============================================================================
// 3) LAS PALABRAS QUE LLEVAN A CADA RUBRO (para el buscador de la página)
// ============================================================================
$claves = [];
try {
    if (function_exists('obtener_claves_categorias')) $claves = obtener_claves_categorias();
} catch (Throwable $e) {
    $claves = [];
}

// ============================================================================
// 4) ORDEN ALFABÉTICO Y AGRUPADO POR LETRA
//    Alfabético «de verdad»: se compara SIN tildes (si no, «Ópticas» caería después de «Zapatería»
//    por el código de la letra acentuada). Los rubros que no empiezan con letra van al grupo «#».
// ============================================================================
usort($rubros, function ($a, $b) {
    $na = function_exists('sin_tildes_texto') ? sin_tildes_texto((string)$a['nombre']) : mb_strtolower((string)$a['nombre']);
    $nb = function_exists('sin_tildes_texto') ? sin_tildes_texto((string)$b['nombre']) : mb_strtolower((string)$b['nombre']);
    return strcmp($na, $nb);
});

$grupos = [];
foreach ($rubros as $r) {
    $nombre = trim((string)($r['nombre'] ?? ''));
    if ($nombre === '') continue;                       // un rubro sin nombre no se puede listar
    $primera = mb_strtoupper(mb_substr($nombre, 0, 1, 'UTF-8'), 'UTF-8');
    if (!preg_match('/^[A-ZÁÉÍÓÚÑ]$/u', $primera)) $primera = '#';
    // La «Ñ» se junta con la «N» (como en cualquier índice en español).
    if ($primera === 'Ñ') $primera = 'N';
    $grupos[$primera][] = $r;
}
ksort($grupos, SORT_STRING);

$total_rubros = 0;
foreach ($grupos as $g) $total_rubros += count($g);

// ============================================================================
// 5) SEO
// ============================================================================
$titulo_pagina      = 'Todos los rubros del directorio';
$descripcion_pagina = 'La lista completa de los rubros de DeChimbote.com: ' . $total_rubros
    . ' categorías (restaurantes, bodegas, ferreterías, farmacias, barberías…) con las tiendas que '
    . 'hay en cada una. Entra al rubro que buscas y mira sus negocios.';
$canonical_url      = url('rubros');

$og_titulo      = '🏷️ Todos los rubros de Chimbote';
$og_descripcion = '🏪 Los ' . $total_rubros . ' rubros del directorio en una sola lista: '
                . 'toca el que buscas y mira sus tiendas, con precios y WhatsApp directo 🛍️';

include __DIR__ . '/includes/header.php';
?>

<h1 class="rub-h1">🏷️ Todos los rubros</h1>
<p class="rub-sub">
    Los <strong><?= number_format($total_rubros) ?></strong> rubros del directorio, en orden
    alfabético. <strong>Toca uno</strong> y ves sus tiendas.
</p>

<!-- 🔎 EL CAMPO: filtra la lista AL INSTANTE (no recarga la página, no manda nada al servidor) -->
<div class="rub-busca">
    <label class="sr-solo" for="rubBusca">Buscar un rubro</label>
    <input type="search" id="rubBusca" autocomplete="off" enterkeyhint="search"
           placeholder="Escribe el rubro que buscas… (o lo que quieres comprar)"
           aria-describedby="rubCuenta">
    <p class="rub-cuenta" id="rubCuenta" aria-live="polite"></p>
</div>

<?php // ⚠️ Los rubros se pintan TODOS de una vez (la lista completa es lo que pidió el jefe: no se
      // pagina ni se esconde nada). El filtro del campo es del navegador: nada de consultas. ?>
<div id="rubLista">
    <?php foreach ($grupos as $letra => $lista): ?>
        <section class="rub-grupo" data-grupo="<?= e((string)$letra) ?>">
            <h2 class="rub-letra" id="letra-<?= e((string)$letra) ?>"><?= e((string)$letra) ?></h2>
            <div class="rub-items">
                <?php foreach ($lista as $r):
                    $rid    = (int)$r['id'];
                    $nombre = (string)$r['nombre'];
                    // Sin filas en el conteo = rubro SIN tiendas todavía (0). Se dice tal cual: es un
                    // rubro del directorio igual, y el jefe pidió verlos TODOS.
                    $n      = (int)($conteo[$rid] ?? 0);
                    // Lo que se busca: el nombre, su dirección y las palabras del rubro (sin tildes).
                    $pals   = $claves[$rid] ?? [];
                    $busca  = sin_tildes_texto($nombre . ' ' . (string)($r['slug'] ?? '') . ' ' . implode(' ', $pals));
                    $pista  = implode(' · ', array_slice($pals, 0, 4));
                ?>
                <a class="rub-item" href="<?= e(url_categoria((string)$r['slug'])) ?>"
                   data-busca="<?= e($busca) ?>" title="<?= e($nombre) ?>">
                    <span class="rub-ico" aria-hidden="true"><?= e((string)($r['icono'] ?? '🏪')) ?></span>
                    <span class="rub-txt">
                        <span class="rub-nombre"><?= e($nombre) ?></span>
                        <span class="rub-claves" hidden><?= e($pista) ?></span>
                    </span>
                    <span class="rub-n<?= $n === 0 ? ' rub-n--cero' : '' ?>"><?php
                        if ($n === 1)      echo '1 tienda';
                        elseif ($n === 0)  echo 'sin tiendas';
                        else               echo number_format($n) . ' tiendas';
                    ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endforeach; ?>
</div>

<!-- Nada encontrado (solo se ve cuando el filtro no deja ningún rubro) -->
<div class="rub-vacio" id="rubVacio" hidden>
    <p class="rub-vacio__t">Ningún rubro coincide con lo que escribiste.</p>
    <p class="rub-vacio__s">
        Prueba con una palabra más corta, o
        <a href="<?= e(url('buscar.php')) ?>">búscalo como producto o tienda</a>.
    </p>
</div>

<?php if ($total_rubros === 0): ?>
    <div class="empty-state">
        <div class="empty-state__icono">🏷️</div>
        <div class="empty-state__titulo">Todavía no hay rubros cargados</div>
        <p>¿Tienes un negocio? <a href="<?= e(url('crear-tienda')) ?>">Créalo con El maestro 🛠️</a>.</p>
    </div>
<?php endif; ?>

<p class="rub-pie">
    ¿No encuentras tu rubro? <a href="<?= e(url('buscar.php')) ?>">Busca por producto o tienda</a>
    o <a href="<?= e(url('crear-tienda')) ?>">crea tu tienda gratis 🛠️</a>.
</p>

<style>
/* ============================================================================
   LOS RUBROS (2026-09-19) — la lista completa del directorio.
   Móvil primero: en el celular una columna (se lee de corrido) y en pantalla
   grande hasta tres, que es cuando la lista ya es larga.
   ============================================================================ */
.rub-h1{font-size:24px;line-height:1.25;margin-bottom:6px}
.rub-sub{color:var(--color-texto-claro);font-size:16px;margin-bottom:14px}
.rub-sub strong{color:var(--marca-granate)}

/* El campo de búsqueda: grande, cómodo en el celular (16 px o más: si no, iOS hace zoom) */
.rub-busca{margin-bottom:16px}
.rub-busca input{
    width:100%;font-size:17px;padding:14px 16px;border:2px solid var(--color-borde);
    border-radius:var(--radio-borde);background:#fff;color:var(--texto-negro);
    box-shadow:var(--sombra-tarjeta);
}
.rub-busca input:focus{outline:none;border-color:var(--marca-naranja);
    box-shadow:0 0 0 3px rgba(234,106,18,.18)}
.rub-cuenta{margin-top:8px;font-size:15px;color:var(--color-texto-claro)}

/* La letra del grupo: separador del índice */
.rub-grupo{margin-bottom:18px}
.rub-letra{
    font-size:18px;font-weight:800;color:var(--marca-granate);
    border-bottom:2px solid var(--color-borde);padding-bottom:4px;margin-bottom:10px;
}

/* Los rubros */
.rub-items{display:grid;grid-template-columns:1fr;gap:8px}
.rub-item{
    display:flex;align-items:center;gap:10px;padding:12px 14px;background:#fff;
    border:1px solid var(--color-borde);border-radius:var(--radio-borde);
    box-shadow:0 2px 6px rgba(109,7,26,.05);font-size:17px;color:var(--texto-negro);
}
.rub-item[hidden]{display:none}          /* el filtro del navegador lo esconde así */
.rub-item:hover{border-color:var(--marca-naranja);box-shadow:var(--sombra-hover);transform:translateY(-1px)}
.rub-item:active{transform:none}
.rub-ico{font-size:20px;line-height:1;flex:0 0 auto}
.rub-txt{flex:1 1 auto;min-width:0;display:flex;flex-direction:column;gap:2px}
.rub-nombre{font-weight:700}
.rub-claves{font-size:13.5px;color:#8a8a8a;font-weight:500}
.rub-claves[hidden]{display:none}
.rub-n{
    flex:0 0 auto;font-size:13.5px;font-weight:700;color:var(--marca-granate);
    background:var(--marca-crema);border-radius:999px;padding:4px 10px;white-space:nowrap;
}
/* Rubro sin tiendas todavía: se dice, pero sin llamar la atención */
.rub-n--cero{color:#8a8a8a;background:#f2f2f2;font-weight:600}

.rub-vacio{background:#fff;border:1px dashed var(--color-borde);border-radius:var(--radio-borde);
    padding:18px;text-align:center}
.rub-vacio__t{font-size:17px;font-weight:700;margin-bottom:4px}
.rub-vacio__s{font-size:15.5px;color:var(--color-texto-claro)}
.rub-vacio a{color:var(--marca-link);text-decoration:underline}

.rub-pie{margin:22px 0 8px;font-size:15.5px;color:var(--color-texto-claro)}
.rub-pie a{color:var(--marca-link);text-decoration:underline}

/* Pantalla grande: dos o tres columnas y el nombre más grande (es un índice) */
@media (min-width:600px){
    .rub-items{grid-template-columns:1fr 1fr}
    .rub-h1{font-size:28px}
}
@media (min-width:980px){
    .rub-items{grid-template-columns:1fr 1fr 1fr}
}
</style>

<script>
/* ============================================================================
   EL FILTRO DE LOS RUBROS (2026-09-19)
   ----------------------------------------------------------------------------
   · Filtra AL INSTANTE, sin recargar y sin preguntarle nada al servidor: los
     rubros ya están todos en la página (son pocos y así la lista se abre al
     toque, aunque la conexión del celular sea lenta).
   · Se busca por el nombre del rubro Y por sus palabras («pollo» → Pollerías,
     «clavos» → Ferreterías). Las palabras se enseñan bajo el nombre **solo
     mientras se busca**: al limpiar el campo el listado queda limpio.
   · Escribe en cualquier orden («salon belleza» encuentra «Salones de belleza»):
     se piden TODAS las palabras escritas, no la frase entera.
   ============================================================================ */
(function () {
    'use strict';
    var inp    = document.getElementById('rubBusca');
    var cuenta = document.getElementById('rubCuenta');
    var vacio  = document.getElementById('rubVacio');
    var items  = Array.prototype.slice.call(document.querySelectorAll('.rub-item'));
    var grupos = Array.prototype.slice.call(document.querySelectorAll('.rub-grupo'));
    if (!inp || !items.length) return;

    /* Sin tildes y en minúsculas: la misma comparación que hace el sitio en PHP
       (sin_tildes_texto). Así «optica» encuentra «Ópticas» y «panaderia» → «Panaderías». */
    function norm(t) {
        return (t || '').toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/\s+/g, ' ').trim();
    }

    /* Preparado una sola vez: lo que se compara por rubro (nombre + dirección + palabras). */
    var datos = items.map(function (a) {
        return {
            el: a,
            claves: a.querySelector('.rub-claves'),
            texto: norm(a.getAttribute('data-busca') || '')
        };
    });

    function filtrar() {
        var palabras = norm(inp.value).split(' ').filter(function (p) { return p !== ''; });
        var buscando = palabras.length > 0;
        var visibles = 0;

        datos.forEach(function (d) {
            var ok = true;
            for (var i = 0; i < palabras.length; i++) {
                if (d.texto.indexOf(palabras[i]) === -1) { ok = false; break; }
            }
            d.el.hidden = !ok;
            /* Las palabras solo se enseñan mientras se busca (y solo en los que quedaron). */
            if (d.claves) d.claves.hidden = !(buscando && ok);
            if (ok) visibles++;
        });

        /* La letra de un grupo se va si no le queda ningún rubro a la vista. */
        grupos.forEach(function (g) {
            var hay = g.querySelector('.rub-item:not([hidden])');
            g.hidden = !hay;
        });

        vacio.hidden = visibles !== 0;
        if (!buscando) {
            cuenta.textContent = 'Se ven los ' + items.length + ' rubros.';
        } else if (visibles === 0) {
            cuenta.textContent = 'Ningún rubro coincide.';
        } else {
            cuenta.textContent = visibles + (visibles === 1 ? ' rubro encontrado.' : ' rubros encontrados.');
        }
    }

    inp.addEventListener('input', filtrar);
    inp.addEventListener('search', filtrar);   // la ✕ del campo en algunos navegadores
    filtrar();                                  // por si el navegador recuerda lo escrito
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
