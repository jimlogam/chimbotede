<?php
/**
 * explorer.php — 🧭 EL EXPLORER: el área del sitio con la forma del muro de Facebook
 * =================================================================================
 * Pedido del jefe (2026-09-16): *«clona esta página de Facebook y llámala a explorar… usa mis
 * tiendas y mis productos para rellenar contenido por mis categorías, todos mis rubros, pon mis
 * enlaces a mis herramientas… colores, formas, buscador y también en formato móvil.»*
 *
 * · El motor (consultas + pintado de cada publicación) vive en `includes/explorer.php`.
 * · Los estilos, en `assets/css/explorer.css` (medidas y colores del muro).
 * · Las interacciones, en `assets/js/explorer.js` (me gusta, comentar, comentarios, cajones…).
 * · El «cargar más» del muro, en `api/explorer_feed.php`.
 * · La dirección amable es **`/explorer`** (regla en `.htaccess`).
 *
 * Guía del módulo: `GUIA_EXPLORER.md`.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/explorer.php';

iniciar_sesion();

/* ============================================================================
   🌐 ES UNA PÁGINA PÚBLICA (aclaración del jefe, 2026-09-16: «ojo, no es una página privada, no es:
   es una página normal a la que se puede acceder… si el usuario ya viene logueado va a seguir
   logueado, y si no está logueado pues tiene arriba su botón para que se pueda loguear»).
   Aquí NO hay pase, ni contraseña, ni `noindex`: entra cualquiera. Lo único que se respeta es que
   **todavía no está en ninguna botonera del sitio** (el jefe dirá cómo se entra).
   ============================================================================ */

// 📈 El Explorer también cuenta en las estadísticas propias del sitio (cookie anónima + latidos).
if (is_file(__DIR__ . '/includes/estadisticas.php')) {
    require_once __DIR__ . '/includes/estadisticas.php';
    try { stats_registrar_visita(); } catch (Throwable $e) { /* nunca romper la página */ }
}

$usuario = usuario_actual();
$yo      = explorer_yo();

// 📌 ¿Es el enlace de UNA publicación? (`/explorer?post=p-1234`, el que se comparte por WhatsApp)
$post_suelto = null;
if (!empty($_GET['post']) && preg_match('/^([tp])-(\d+)$/', (string)$_GET['post'], $m)) {
    $post_suelto = explorer_post_uno($m[1] === 'p' ? 'producto' : 'tienda', (int)$m[2]);
}

$pagina = max(1, (int)($_GET['p'] ?? 1));
$filtro = (string)($_GET['filtro'] ?? 'todo');
if (!in_array($filtro, ['todo', 'tienda', 'producto'], true)) $filtro = 'todo';
$cat    = (string)($_GET['cat'] ?? '');
$zona   = (string)($_GET['zona'] ?? '');

// 🎲 LA SEMILLA DEL MURO (pedido del jefe: *«cambia el orden para que no se vea siempre de la misma
//    tienda»*). El orden sale al azar, pero **estable dentro de la visita**: la semilla se sortea una
//    vez y el «scroll infinito» se la pasa a la API para seguir pidiendo las siguientes sin repetir
//    ni saltarse ninguna. Va en una variable de JavaScript (no en la dirección) para que los enlaces
//    que se comparten sigan siendo limpios.
$semilla = (int)($_GET['s'] ?? 0);
if ($semilla <= 0) {
    try { $semilla = random_int(1, 2000000000); } catch (Throwable $e) { $semilla = 20260916; }
}

$feed = $post_suelto
      ? ['posts' => [$post_suelto], 'hay_mas' => false]
      : explorer_feed(['pagina' => $pagina, 'filtro' => $filtro, 'cat' => $cat, 'zona' => $zona,
                       'semilla' => $semilla]);

// 🗂️ El rubro que se está mirando (para el chip y el título).
$cat_datos = null;
if ($cat !== '') {
    try {
        $st = db()->prepare('SELECT nombre, slug, icono FROM directorio_categorias WHERE slug = ? LIMIT 1');
        $st->execute([$cat]);
        $cat_datos = $st->fetch() ?: null;
    } catch (Throwable $e) { $cat_datos = null; }
}
$zona_datos = null;
if ($zona !== '') {
    try {
        $st = db()->prepare('SELECT nombre, slug FROM directorio_distritos WHERE slug = ? LIMIT 1');
        $st->execute([$zona]);
        $zona_datos = $st->fetch() ?: null;
    } catch (Throwable $e) { $zona_datos = null; }
}

$avisos_n  = explorer_avisos_n();
$resumen   = explorer_resumen();
$distritos = function_exists('obtener_distritos_visibles') ? obtener_distritos_visibles() : [];
$herramientas = explorer_herramientas();
[$herr_visibles, $herr_resto] = explorer_herramientas_visibles(7);
$rubros_directos = explorer_rubros_directos(10);

$titulo_pagina      = 'Explorer — el muro de las tiendas de Chimbote';
$descripcion_pagina = 'Explora Chimbote como en un muro: las tiendas, sus productos, sus precios y su '
                    . 'WhatsApp directo, por rubros y distritos. Publica lo tuyo gratis en DeChimbote.com.';
$og_titulo          = '🧭 Explorer · el muro de las tiendas de DeChimbote.com';
$og_descripcion     = '🏪 ' . number_format($resumen['tiendas']) . ' tiendas y '
                    . number_format($resumen['productos']) . ' productos de Chimbote y la provincia del Santa, '
                    . 'en un solo muro: mira, pregunta y pide por WhatsApp 👉 entra y explora 🛍️';
$canonical_url      = url('explorer.php');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0">
<title><?= e($titulo_pagina) ?> · <?= e(SITE_NAME) ?></title>
<meta name="description" content="<?= e($descripcion_pagina) ?>">
<meta name="theme-color" content="#0866ff">

<meta property="og:title" content="<?= e($og_titulo) ?>">
<meta property="og:description" content="<?= e($og_descripcion) ?>">
<meta property="og:type" content="website">
<meta property="og:site_name" content="<?= e(SITE_NAME) ?>">
<meta property="og:locale" content="es_PE">
<meta property="og:image" content="<?= e(url('assets/img/og-dechimbote.jpg') . '?v=2') ?>">
<meta property="og:url" content="<?= e($canonical_url) ?>">
<meta name="twitter:card" content="summary_large_image">
<link rel="canonical" href="<?= e($canonical_url) ?>">

<!-- Fuente: la misma familia del muro (Helvetica/Arial en el sistema, sin bajar nada extra) -->
<link rel="stylesheet" href="<?= url('assets/css/base.css') ?>?v=6">
<link rel="stylesheet" href="<?= url('assets/css/carrito.css') ?>?v=6">
<link rel="stylesheet" href="<?= url('assets/css/explorer.css') ?>?v=14">
<link rel="icon" href="<?= url('assets/img/favicon.ico') ?>?v=2" sizes="any">
<link rel="icon" type="image/png" sizes="512x512" href="<?= url('assets/img/favicon.png') ?>?v=2">
<link rel="apple-touch-icon" href="<?= url('assets/img/apple-touch-icon.png') ?>?v=2">

<script src="<?= url('assets/js/fuse.min.js') ?>?v=7" defer></script>
<script>
    window.SITE_URL   = <?= json_encode(SITE_URL) ?>;
    window.CSRF_TOKEN = '<?= csrf_token() ?>';
    // 🎲 La semilla del muro: la usa el «scroll infinito» para pedir las siguientes publicaciones
    //    conservando el MISMO orden (si cambiara en cada petición, se repetirían y se saltarían).
    window.EXP_SEMILLA = <?= (int)$semilla ?>;
</script>
</head>
<body class="exp<?= $usuario ? '' : ' exp-visitante' ?>" data-panel="<?= e((string)($_GET['panel'] ?? '')) ?>" data-pagina="<?= (int)$pagina ?>">

<!-- ==========================================================================
     LA CABECERA (como la barra de arriba: logo, buscador, iconos y la cuenta)
     ========================================================================== -->
<header class="exh" id="expCabecera">
    <div class="exh__izq">
        <a class="exh__logo" href="<?= url('') ?>" title="Inicio de DeChimbote.com" aria-label="Inicio de DeChimbote.com">
            <span class="exh__logo-circulo"><?= explorer_icono('buscar') ?></span>
        </a>
        <form class="exh__buscador" action="<?= url('buscar.php') ?>" method="get" role="search">
            <span class="exh__buscador-ico" aria-hidden="true"><?= explorer_icono('buscar') ?></span>
            <input type="search" id="buscador-fuzzy" data-fuzzy name="q" autocomplete="off"
                   placeholder="Busca en DeChimbote: tiendas, productos, rubros…"
                   value="<?= e($_GET['q'] ?? '') ?>" aria-label="Buscar en DeChimbote">
            <button type="submit" class="sr-solo" tabindex="-1" aria-label="Buscar">Buscar</button>
        </form>
    </div>

    <nav class="exh__centro" aria-label="Secciones del Explorer">
        <a class="exh__tab<?= $filtro === 'todo' && !$post_suelto ? ' is-on' : '' ?>"
           href="<?= e(explorer_url(['filtro' => null, 'post' => null])) ?>" title="Explorer: el muro de las tiendas"><?= explorer_icono('inicio') ?><span>Explorer</span></a>
        <a class="exh__tab" href="<?= url('buscar.php') ?>" title="Tiendas"><?= explorer_icono('tiendas') ?><span>Tiendas</span></a>
        <a class="exh__tab" href="<?= e(explorer_url(['filtro' => null, 'cat' => null, 'zona' => null, 'post' => null])) ?>" data-ex-abrir-rubros title="Rubros"><?= explorer_icono('rubros') ?><span>Rubros</span></a>
        <a class="exh__tab<?= $filtro === 'producto' ? ' is-on' : '' ?>" href="<?= e(explorer_url(['filtro' => 'producto'])) ?>" title="Productos"><?= explorer_icono('productos') ?><span>Productos</span></a>
        <?php // ⛔ SIN ANCLAS (orden del jefe, 2026-09-21): esta pestaña llevaba al final `#historias`. ?>
        <a class="exh__tab" href="<?= e(explorer_url(['filtro' => null, 'cat' => null, 'zona' => null, 'post' => null])) ?>" title="Historias"><?= explorer_icono('historias') ?><span>Historias</span></a>
    </nav>

    <div class="exh__der">
        <!-- 🔎 La lupa del celular: despliega el buscador debajo de la cabecera -->
        <button type="button" class="exh__ico exh__ico--solo-movil" data-ex-buscar aria-label="Buscar" title="Buscar">
            <?= explorer_icono('buscar') ?>
        </button>

        <!-- ⠿ Todas mis herramientas (el cuadrito de nueve puntos) -->
        <div class="exh__caja" data-ex-caja>
            <button type="button" class="exh__ico" data-ex-caja-btn aria-label="Tus herramientas" title="Tus herramientas">
                <?= explorer_icono('rejilla') ?>
            </button>
            <div class="exh__panel exh__panel--ancho" hidden>
                <p class="exh__panel-titulo">Tus herramientas en DeChimbote</p>
                <div class="exh__herramientas">
                    <?php foreach ($herramientas as $t): ?>
                        <a class="exp-rail__it" href="<?= e($t['url']) ?>"<?= !empty($t['accion']) ? ' data-ex-accion="' . e($t['accion']) . '"' : '' ?>>
                            <span class="exp-cuadro" style="background:<?= e($t['color']) ?>"><?= explorer_icono($t['ico']) ?></span>
                            <span class="exp-rail__ittxt"><b><?= e($t['txt']) ?></b><i><?= e($t['sub']) ?></i></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php // 🗑️ 2026-09-17: aquí iba el botón 💬 de «El ninja» (hacía de Messenger). El chat de
              // ayuda dejó de vivir en todo el sitio: ahora es **🧭 El guía**, el anfitrión de cada
              // tienda, y solo se pinta en las fichas (`/neg/<slug>`). Un botón sin widget detrás no
              // hace nada, así que se retiró. ?>

        <!-- 🔔 La actividad del sitio (números REALES) -->
        <div class="exh__caja" data-ex-caja>
            <button type="button" class="exh__ico" data-ex-caja-btn aria-label="Avisos" title="Avisos">
                <?= explorer_icono('campana') ?>
                <?php if ($avisos_n > 0): ?><span class="exh__globo"><?= $avisos_n > 20 ? '20+' : (int)$avisos_n ?></span><?php endif; ?>
            </button>
            <div class="exh__panel" hidden>
                <p class="exh__panel-titulo">Lo que hay hoy en el sitio</p>
                <a class="exp-rail__it" href="<?= url('empleos') ?>">
                    <span class="exp-cuadro" style="background:#6d071a"><?= explorer_icono('empleo') ?></span>
                    <span class="exp-rail__ittxt"><b><?= number_format($avisos_n) ?> empleos y anuncios vigentes</b><i>Mira quién está contratando</i></span>
                </a>
                <a class="exp-rail__it" href="<?= url('buscar.php') ?>">
                    <span class="exp-cuadro" style="background:#1877f2"><?= explorer_icono('tiendas') ?></span>
                    <span class="exp-rail__ittxt"><b><?= number_format($resumen['tiendas']) ?> tiendas publicadas</b><i><?= number_format($resumen['rubros']) ?> rubros en el directorio</i></span>
                </a>
                <a class="exp-rail__it" href="<?= url('en-vivo') ?>">
                    <span class="exp-cuadro" style="background:#e41e3f"><?= explorer_icono('vivo') ?></span>
                    <span class="exp-rail__ittxt"><b>Información en vivo</b><i>Lo que se busca y nadie vende, ahora</i></span>
                </a>
                <a class="exp-rail__it" href="<?= url('noticias') ?>">
                    <span class="exp-cuadro" style="background:#0f766e"><?= explorer_icono('noticias') ?></span>
                    <span class="exp-rail__ittxt"><b>Noticias de Chimbote</b><i>Lo último de la zona</i></span>
                </a>
            </div>
        </div>

        <!-- 👤 La cuenta: si no hay sesión, un botón azul «Entrar» bien visible (el jefe lo pidió:
             «si no está logueado pues tiene arriba su botón para que se pueda loguear») -->
        <?php if (!$usuario): ?>
            <a class="exh__entrar" href="<?= e(url('login.php') . '?redirect=' . rawurlencode('explorer.php')) ?>">Entrar</a>
        <?php endif; ?>
        <div class="exh__caja" data-ex-caja>
            <button type="button" class="exh__cuenta" data-ex-caja-btn aria-label="Tu cuenta" title="<?= e($yo !== '' ? $yo : 'Tu cuenta') ?>">
                <?php if ($yo !== ''): ?>
                    <?= explorer_avatar_html('', $yo) ?>
                <?php else: ?>
                    <span class="exh__cuenta-anon"><?= explorer_icono('panel') ?></span>
                <?php endif; ?>
            </button>
            <div class="exh__panel" hidden>
                <?php if ($usuario): ?>
                    <a class="exp-rail__it" href="<?= url('panel.php') ?>">
                        <span class="exp-cuadro" style="background:#123c6b"><?= explorer_icono('panel') ?></span>
                        <span class="exp-rail__ittxt"><b>Mi panel, <?= e(explorer_yo_corto()) ?></b><i>Tus avisos, pedidos y búsquedas</i></span>
                    </a>
                    <a class="exp-rail__it" href="<?= url('logout.php') ?>">
                        <span class="exp-cuadro" style="background:#6b7280"><?= explorer_icono('cerrar') ?></span>
                        <span class="exp-rail__ittxt"><b>Salir</b><i>Cerrar mi sesión en este aparato</i></span>
                    </a>
                <?php else: ?>
                    <a class="exp-rail__it" href="<?= url('registro.php') ?>">
                        <span class="exp-cuadro" style="background:#1877f2"><?= explorer_icono('panel') ?></span>
                        <span class="exp-rail__ittxt"><b>Crear mi cuenta gratis</b><i>Guarda tus favoritos y lo que buscas</i></span>
                    </a>
                    <a class="exp-rail__it" href="<?= url('login.php') ?>">
                        <span class="exp-cuadro" style="background:#123c6b"><?= explorer_icono('panel') ?></span>
                        <span class="exp-rail__ittxt"><b>Ingresar</b><i>Ya tengo cuenta en DeChimbote.com</i></span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<!-- ==========================================================================
     EL CUERPO: barra izquierda · el muro · columna derecha
     ========================================================================== -->
<div class="exp-cuerpo">

    <!-- ---------------- BARRA IZQUIERDA (en el celular es el cajón del menú) ---------------- -->
    <aside class="exp-izq" id="expIzq" aria-label="Tus accesos directos">
        <a class="exp-izq__yo" href="<?= e($usuario ? url('panel.php') : url('login.php')) ?>">
            <span class="exp-av"><?= explorer_mi_avatar_html() ?></span>
            <span class="exp-izq__yotxt">
                <b><?= e($yo !== '' ? $yo : 'Entra a tu cuenta') ?></b>
                <i><?= e($yo !== '' ? 'Mira tu panel y tus tiendas' : 'Es gratis y en un minuto') ?></i>
            </span>
        </a>

        <nav class="exp-izq__lista">
            <a class="exp-izq__it" href="<?= url('') ?>" title="La portada de DeChimbote.com">
                <span class="exp-cuadro" style="background:#6d071a"><?= explorer_icono('inicio') ?></span>
                <span class="exp-izq__txt">Inicio · la portada</span>
            </a>
            <?php foreach ($herr_visibles as $t): ?>
                <a class="exp-izq__it" href="<?= e($t['url']) ?>"<?= !empty($t['accion']) ? ' data-ex-accion="' . e($t['accion']) . '"' : '' ?>
                   title="<?= e($t['txt']) ?>">
                    <span class="exp-cuadro" style="background:<?= e($t['color']) ?>"><?= explorer_icono($t['ico']) ?></span>
                    <span class="exp-izq__txt"><?= e($t['txt']) ?></span>
                    <?php if ($t['k'] === 'empleos' && $avisos_n > 0): ?><span class="exp-izq__globo"><?= (int)$avisos_n ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
            <div class="exp-izq__resto" id="expIzqResto" hidden>
                <?php foreach ($herr_resto as $t): ?>
                    <a class="exp-izq__it" href="<?= e($t['url']) ?>"<?= !empty($t['accion']) ? ' data-ex-accion="' . e($t['accion']) . '"' : '' ?>>
                        <span class="exp-cuadro" style="background:<?= e($t['color']) ?>"><?= explorer_icono($t['ico']) ?></span>
                        <span class="exp-izq__txt"><?= e($t['txt']) ?></span>
                    </a>
                <?php endforeach; ?>
                <button type="button" class="exp-izq__it" data-ex-abrir-rubros>
                    <span class="exp-cuadro" style="background:#45bd62"><?= explorer_icono('rubros') ?></span>
                    <span class="exp-izq__txt">Todos los rubros</span>
                </button>
            </div>
            <button type="button" class="exp-izq__it exp-izq__it--mas" data-ex-ver-mas aria-expanded="false" aria-controls="expIzqResto">
                <span class="exp-cuadro exp-cuadro--gris"><?= explorer_icono('flecha-mas') ?></span>
                <span class="exp-izq__txt">Ver más</span>
            </button>
        </nav>

        <p class="exp-izq__titulo">Tus accesos directos</p>
        <nav class="exp-izq__lista">
            <?php foreach ($rubros_directos as $r): ?>
                <a class="exp-izq__it" href="<?= e(explorer_url(['cat' => $r['slug'], 'post' => null])) ?>" title="<?= e($r['nombre']) ?>">
                    <span class="exp-cuadro exp-cuadro--letra" style="background:<?= e(['#1877f2','#f3425f','#45bd62','#f7b928','#8b5cf6','#0f766e','#ea6a12','#123c6b'][abs(crc32((string)$r['slug'])) % 8]) ?>">
                        <?= e(mb_strtoupper(mb_substr((string)$r['nombre'], 0, 1))) ?>
                    </span>
                    <span class="exp-izq__txt"><?= e($r['icono']) ?> <?= e($r['nombre']) ?></span>
                    <?php if ((int)$r['n'] > 0): ?><span class="exp-izq__n"><?= (int)$r['n'] ?></span><?php endif; ?>
                </a>
            <?php endforeach; ?>
            <a class="exp-izq__it" href="<?= url('buscar.php') ?>">
                <span class="exp-cuadro exp-cuadro--gris"><?= explorer_icono('buscar') ?></span>
                <span class="exp-izq__txt">Ver todas las tiendas</span>
            </a>
        </nav>

        <p class="exp-izq__legal">
            <a href="<?= url('terminos.php') ?>">Términos</a> ·
            <a href="<?= url('privacidad.php') ?>">Privacidad</a> ·
            <a href="<?= e(explorer_url(['filtro' => null, 'cat' => null, 'zona' => null, 'post' => null])) ?>">Explorer</a> ·
            <a href="<?= url('') ?>">Portada</a> ·
            <a href="<?= url('reclamar') ?>">Reclama tu negocio</a><br>
            © <?= date('Y') ?> <?= e(SITE_NAME) ?> · Hecho con 💚 en Chimbote, Perú
        </p>
    </aside>

    <!-- ---------------- EL MURO (la columna del centro) ---------------- -->
    <main class="exp-muro" id="expMuro">

        <?php if ($post_suelto): ?>
            <a class="exp-volver" href="<?= e(explorer_url(['post' => null])) ?>">‹ Volver al Explorer</a>
        <?php endif; ?>

        <?php // ---------- Los filtros del muro (como las pestañas de arriba del muro) ---------- ?>
        <?php if (!$post_suelto): ?>
        <div class="exp-chips" role="tablist" aria-label="Filtros del muro">
            <a class="exp-chip<?= ($filtro === 'todo' && $cat === '' && $zona === '') ? ' is-on' : '' ?>"
               href="<?= e(explorer_url(['filtro' => null, 'cat' => null, 'zona' => null, 'p' => null])) ?>">🧭 Para ti</a>
            <a class="exp-chip<?= $filtro === 'tienda' ? ' is-on' : '' ?>"
               href="<?= e(explorer_url(['filtro' => 'tienda', 'p' => null])) ?>"><?= explorer_icono('tiendas') ?> Tiendas</a>
            <a class="exp-chip<?= $filtro === 'producto' ? ' is-on' : '' ?>"
               href="<?= e(explorer_url(['filtro' => 'producto', 'p' => null])) ?>"><?= explorer_icono('productos') ?> Productos</a>
            <button type="button" class="exp-chip exp-chip--btn" data-ex-abrir-rubros>
                <?= explorer_icono('rubros') ?> <?= $cat_datos ? e($cat_datos['icono'] . ' ' . $cat_datos['nombre']) : 'Rubros' ?> ▾
            </button>
            <?php if ($cat_datos): ?>
                <a class="exp-chip exp-chip--x" href="<?= e(explorer_url(['cat' => null])) ?>" title="Quitar el filtro de rubro">✕</a>
            <?php endif; ?>
            <?php if (!empty($distritos)): ?>
                <form class="exp-zona" method="get" action="<?= url('explorer') ?>">
                    <input type="hidden" name="filtro" value="<?= e($filtro) ?>">
                    <?php if ($cat !== ''): ?><input type="hidden" name="cat" value="<?= e($cat) ?>"><?php endif; ?>
                    <?php // ⚠️ AQUÍ HABÍA un `<label class="sr-solo" for="expZona">` y ERA EL CULPABLE de
                          // que en el celular la pantalla se deslizara al costado: ese rótulo es
                          // `position: absolute`, y al no tener un contenedor posicionado su bloque era
                          // la PÁGINA: como el carril de filtros se desliza, el rótulo quedaba en x≈479 y
                          // estiraba el documento a 481 px en una pantalla de 390 (medido con
                          // `node __exp_lado.js`). El nombre del campo va ahora en el `aria-label`. ?>
                    <select id="expZona" name="zona" aria-label="Zona o distrito" onchange="this.form.submit()">
                        <option value="">📍 Toda la provincia</option>
                        <?php foreach ($distritos as $d): ?>
                            <option value="<?= e($d['slug']) ?>"<?= $zona === $d['slug'] ? ' selected' : '' ?>>
                                <?= e($d['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php // ---------- La tira de historias (los flyers de las tiendas) ---------- ?>
        <?php if (!$post_suelto): ?>
            <?= explorer_stories_html() ?>
        <?php endif; ?>

        <?php // ---------- El compositor ---------- ?>
        <?php if (!$post_suelto): ?>
        <section class="exp-compositor" aria-label="Publica en DeChimbote">
            <div class="exp-comp__fila">
                <span class="exp-av"><?= explorer_mi_avatar_html() ?></span>
                <button type="button" class="exp-comp__campo" data-ex-publicar>
                    ¿Qué estás pensando<?= $yo !== '' ? ', ' . e(explorer_yo_corto()) : '' ?>? Publica tu tienda o un producto…
                </button>
            </div>
            <div class="exp-comp__linea"></div>
            <div class="exp-comp__botones">
                <a class="exp-comp__btn" href="<?= url('en-vivo') ?>">
                    <span class="exp-comp__ico exp-comp__ico--rojo"><?= explorer_icono('vivo') ?></span> Video en vivo
                </a>
                <a class="exp-comp__btn" href="<?= url('caminante/') ?>">
                    <span class="exp-comp__ico exp-comp__ico--verde"><?= explorer_icono('camara') ?></span> Foto/video
                </a>
                <a class="exp-comp__btn" href="<?= url('crear-tienda') ?>">
                    <span class="exp-comp__ico exp-comp__ico--amarillo"><?= explorer_icono('sonrisa') ?></span> Vender algo
                </a>
            </div>
        </section>
        <?php endif; ?>

        <?php // ---------- Las publicaciones (con sus intercalados: publicidad cada 3 y luego cada 5,
              // y las historias repetidas cada 10 — lo hace el motor en `explorer_posts_html()`) ---------- ?>
        <div class="exp-posts" id="expPosts">
            <?php if (empty($feed['posts'])): ?>
                <div class="exp-vacio">
                    <p class="exp-vacio__ico">🧭</p>
                    <p class="exp-vacio__t">Aquí todavía no hay nada con este filtro</p>
                    <p class="exp-vacio__s">Prueba con otro rubro, otra zona o mira todas las tiendas.</p>
                    <a class="exp-btn exp-btn--azul" href="<?= e(explorer_url(['filtro' => null, 'cat' => null, 'zona' => null, 'post' => null])) ?>">Ver todo el Explorer</a>
                </div>
            <?php else: ?>
                <?= explorer_posts_html($feed['posts'], ($pagina - 1) * (int)EXPLORER_POR_PAGINA) ?>
            <?php endif; ?>
        </div>

        <?php // ---------- El muro se sigue solo: SCROLL INFINITO (pedido del jefe: «el botón de ver más
              // publicaciones… desaparece, lo debe ser un scroll infinito así como tiene Facebook»).
              // Abajo del muro queda un centinela invisible: cuando el visitante llega ahí, el JS pide
              // la tanda siguiente a api/explorer_feed.php y la pega al final. Sin botón. ?>
        <?php if (!$post_suelto && !empty($feed['posts']) && !empty($feed['hay_mas'])): ?>
            <div class="exp-mas" id="expMas">
                <div class="exp-mas__centinela" data-ex-centinela aria-hidden="true"></div>
                <p class="exp-mas__cargando" data-ex-cargando hidden>
                    <span class="exp-mas__giro" aria-hidden="true"></span> Cargando más publicaciones…
                </p>
            </div>
        <?php elseif (!$post_suelto && !empty($feed['posts'])): ?>
            <p class="exp-mas__fin">Ya viste todo lo de este filtro 🎉
                <a href="<?= url('buscar.php') ?>">Busca algo concreto</a></p>
        <?php endif; ?>

        <p class="exp-pie">
            <?= e(SITE_NAME) ?> · <?= number_format($resumen['tiendas']) ?> tiendas ·
            <?= number_format($resumen['productos']) ?> productos · <?= number_format($resumen['rubros']) ?> rubros<br>
            <a href="<?= url('crear-tienda') ?>">Publica tu tienda gratis</a> ·
            <a href="<?= url('trabaja_con_nosotros.php') ?>">Trabaja con nosotros</a> ·
            <a href="<?= url('terminos.php') ?>">Términos</a> ·
            <a href="<?= url('privacidad.php') ?>">Privacidad</a>
        </p>
    </main>

    <!-- ---------------- COLUMNA DERECHA ---------------- -->
    <aside class="exp-der" aria-label="Publicidad y sugerencias">
        <?= explorer_publicidad_html(2) ?>
        <?= explorer_sugerencias_html(4) ?>
        <?= explorer_rail_herramientas_html() ?>
    </aside>
</div>

<!-- ==========================================================================
     EL CELULAR: la barra de abajo y el botón de publicar
     ========================================================================== -->
<nav class="exp-barra" aria-label="Navegación del celular">
    <a class="exp-barra__it" href="<?= url('') ?>" title="Inicio"><?= explorer_icono('inicio') ?></a>
    <a class="exp-barra__it is-on" href="<?= e(explorer_url(['post' => null])) ?>" title="Explorer"><?= explorer_icono('historias') ?></a>
    <a class="exp-barra__it" href="<?= e(explorer_url(['filtro' => 'producto', 'post' => null])) ?>" title="Productos"><?= explorer_icono('productos') ?></a>
    <button type="button" class="exp-barra__it" data-ex-abrir-rubros title="Rubros"><?= explorer_icono('rubros') ?></button>
    <a class="exp-barra__it" href="<?= url('empleos') ?>" title="Empleos y avisos">
        <?= explorer_icono('campana') ?>
        <?php if ($avisos_n > 0): ?><span class="exh__globo"><?= $avisos_n > 20 ? '20+' : (int)$avisos_n ?></span><?php endif; ?>
    </a>
    <button type="button" class="exp-barra__it" data-ex-menu title="Menú"><?= explorer_icono('menu') ?></button>
</nav>

<button type="button" class="exp-fab" data-ex-publicar aria-label="Publicar en DeChimbote" title="Publica tu tienda o un producto">
    <?= explorer_icono('mas') ?>
</button>

<?php // ---------- El botón DISCRETO de «Regresar a la portada» (pedido del jefe, 2026-09-16: *«incluye
      // algún botón así de manera extemporánea, cuando el usuario va dando scroll, que diga "regresar a
      // la portada", así discreto nada más, cada cierta cantidad que el usuario hace scroll»*).
      // Aparece solo cuando el visitante ya bajó bastante (dos pantallas) y se esconde al volver arriba:
      // lo enciende y lo apaga `assets/js/explorer.js`. ?>
<a class="exp-portada" id="expPortada" href="<?= url('') ?>" hidden>
    <span class="exp-portada__ico" aria-hidden="true">↑</span>
    <span class="exp-portada__txt">Regresar a la portada</span>
</a>

<!-- ==========================================================================
     LOS CAJONES: el menú del celular, los rubros y el de publicar
     ========================================================================== -->
<div class="exp-velo" id="expVelo" hidden></div>

<!-- 🗂️ Todos los rubros (con buscador, porque son muchos) -->
<div class="exp-cajon" id="expRubros" hidden>
    <div class="exp-cajon__caja" role="dialog" aria-modal="true" aria-label="Todos los rubros">
        <header class="exp-cajon__cabeza">
            <p class="exp-cajon__titulo">Todos los rubros</p>
            <button type="button" class="exp-cajon__x" data-ex-cerrar aria-label="Cerrar"><?= explorer_icono('cerrar') ?></button>
        </header>
        <div class="exp-cajon__buscador">
            <input type="search" id="expRubrosBusca" placeholder="Escribe el rubro que buscas…" autocomplete="off">
        </div>
        <div class="exp-cajon__cuerpo" data-ex-rubros-lista>
            <?php foreach (explorer_rubros_todos() as $r): ?>
                <a class="exp-izq__it" href="<?= e(explorer_url(['cat' => $r['slug'], 'p' => null, 'post' => null])) ?>" data-ex-rubro="<?= e(mb_strtolower((string)$r['nombre'])) ?>">
                    <span class="exp-cuadro exp-cuadro--letra" style="background:<?= e(['#1877f2','#f3425f','#45bd62','#f7b928','#8b5cf6','#0f766e','#ea6a12','#123c6b'][abs(crc32((string)$r['slug'])) % 8]) ?>">
                        <?= e(mb_strtoupper(mb_substr((string)$r['nombre'], 0, 1))) ?>
                    </span>
                    <span class="exp-izq__txt"><?= e($r['icono']) ?> <?= e($r['nombre']) ?></span>
                </a>
            <?php endforeach; ?>
            <?php // 🏷️ La puerta a la PÁGINA de rubros (2026-09-19): este cajón es una ventana dentro del
                  // muro (no se puede enlazar ni compartir), y el jefe pidió «una página donde se vean
                  // todos los rubros». `rubros.php` = /rubros. ?>
            <a class="exp-izq__it" href="<?= url('rubros') ?>">
                <span class="exp-cuadro exp-cuadro--letra" style="background:#6d071a"><?= explorer_icono('rubros') ?></span>
                <span class="exp-izq__txt">Ver la lista completa, en una página</span>
            </a>
        </div>
    </div>
</div>

<!-- 🛠️ Publicar (el «¿Qué estás pensando?» lleva a las herramientas de verdad) -->
<div class="exp-cajon" id="expPublicar" hidden>
    <div class="exp-cajon__caja" role="dialog" aria-modal="true" aria-label="Publica en DeChimbote">
        <header class="exp-cajon__cabeza">
            <p class="exp-cajon__titulo">Publica en DeChimbote</p>
            <button type="button" class="exp-cajon__x" data-ex-cerrar aria-label="Cerrar"><?= explorer_icono('cerrar') ?></button>
        </header>
        <div class="exp-cajon__cuerpo">
            <p class="exp-cajon__nota">Elige lo que quieres publicar. Todo es <b>gratis</b> y sale en el muro, en
               el buscador y en los rubros del sitio.</p>
            <?php foreach ($herramientas as $t): ?>
                <?php if (!in_array($t['k'], ['maestro', 'caminante', 'empleos', 'productos', 'vivo', 'reclamo'], true)) continue; ?>
                <a class="exp-rail__it" href="<?= e($t['url']) ?>">
                    <span class="exp-cuadro exp-cuadro--grande" style="background:<?= e($t['color']) ?>"><?= explorer_icono($t['ico']) ?></span>
                    <span class="exp-rail__ittxt"><b><?= e($t['txt']) ?></b><i><?= e($t['sub']) ?></i></span>
                </a>
            <?php endforeach; ?>
            <a class="exp-rail__it" href="<?= url('reclamar') ?>">
                <span class="exp-cuadro exp-cuadro--grande" style="background:#7a5230"><?= explorer_icono('tiendas') ?></span>
                <span class="exp-rail__ittxt"><b>Reclamar mi negocio</b><i>Ya está publicado y es mío</i></span>
            </a>
        </div>
    </div>
</div>

<?php // ---------- 🥷 El ninja: el chat de ayuda del sitio, que aquí hace de Messenger ---------- ?>
<script>window.WA_ICONO_SVG = <?= json_encode(wa_icono_svg()) ?>;</script>
<script>window.CHIMBOTE_LIMPIEZA = <?= json_encode(busqueda_diccionario_js(), JSON_UNESCAPED_UNICODE) ?>;</script>
<script src="<?= url('assets/js/buscador_limpieza.js') ?>?v=1"></script>
<script src="<?= url('assets/js/buscador_fuzzy.js') ?>?v=9"></script>
<script src="<?= url('assets/js/buscador_voz.js') ?>?v=3"></script>
<?php // 🛒 El carrito del sitio: el ❤️ Me interesa del muro es EL MISMO motor (pedido por WhatsApp). ?>
<script src="<?= url('assets/js/carrito.js') ?>?v=9"></script>
<?php // 📞 Cuenta los clics reales de llamar/WhatsApp (no envenena los récords con toques). ?>
<script src="<?= url('assets/js/llamadas.js') ?>?v=2"></script>
<script src="<?= url('assets/js/explorer.js') ?>?v=5"></script>
<?php
require_once __DIR__ . '/includes/chatbot_widget.php';
echo chatbot_widget_html();
?>
</body>
</html>
