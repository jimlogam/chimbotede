<?php
require_once __DIR__ . '/config.php';
// 🥷 Ajustes del chat de ayuda («El ninja»): nombre, emoji, cuota de preguntas. Se cargan AQUÍ ARRIBA
// porque las tarjetas de abajo los usan: si se cargara más tarde, PHP cortaría la página
// a la mitad al llegar a la primera constante sin definir (pasó el 2026-09-13 al instalar
// la tarjeta de El ninja en el panel: la página se quedaba en las 4 tarjetas de arriba).
require_once __DIR__ . '/includes/config_chatbot.php';
require_once __DIR__ . '/includes/negocio_borrar.php';   // 🗑️ borrar una tienda con todo lo suyo
require_once __DIR__ . '/includes/contrasena_dueno.php'; // 🔐 «escribe tu contraseña» (helpers compartidos)
requiere_login();

$usuario = usuario_actual();

/* ============================================================================================
 * 🆕 2026-09-16 — «✏️ EDITAR» Y «🗑️ ELIMINAR» EN CADA TIENDA DEL PANEL (orden del jefe, textual):
 * *«debe aparecer las tiendas creadas y debajo de cada uno un botón de editar y otro de eliminar,
 * pedir contraseña para eliminar»*.
 *
 * 🔒 CÓMO SE PROTEGE EL BORRADO (son tres candados, no uno):
 *   1. **Sesión**: `requiere_login()` (arriba).
 *   2. **Es suya**: se comprueba en la base que la tienda tiene `dueno_id` = el usuario de la sesión
 *      (nunca se confía en el `negocio_id` que llega del formulario).
 *   3. **Su contraseña**: se pide y se verifica con `password_verify()` contra su hash (el de la
 *      sesión NO sirve: `login()` lo borra al entrar, así que se lee de la base).
 * Además el token CSRF del sitio. Si algo falla, NO se borra nada y se avisa con un mensaje claro.
 * ========================================================================================== */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['accion'] ?? '') === 'eliminar_tienda') {
    csrf_verificar();
    $nid  = (int)($_POST['negocio_id'] ?? 0);
    $pass = (string)($_POST['password'] ?? '');

    $suya = null;
    if ($nid > 0) {
        $st = db()->prepare("SELECT id, nombre FROM directorio_negocios WHERE id = ? AND dueno_id = ? LIMIT 1");
        $st->execute([$nid, (int)$usuario['id']]);
        $suya = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    if (!$suya) {
        flash('No encontré esa tienda entre las tuyas 🤔 No se borró nada.', 'error');
    } else {
        // 🔐 El candado compartido con el editor (`includes/contrasena_dueno.php`).
        $error = contrasena_dueno_error($pass, (int)$usuario['id'], 'eliminar la tienda «' . (string)$suya['nombre'] . '»');
        if ($error !== '') {
            // ⚠️ Se avisa y NO se borra nada: ni con la contraseña vacía ni con una equivocada.
            flash($error . ' La tienda «' . (string)$suya['nombre'] . '» NO se borró.', 'error');
        } else {
            $r = negocio_borrar_completo((int)$suya['id']);
            if (!empty($r['ok'])) {
                flash('🗑️ La tienda «' . (string)$r['nombre'] . '» quedó eliminada con todos sus productos, fotos y opiniones'
                    . ((int)($r['archivos'] ?? 0) > 0 ? ' (' . (int)$r['archivos'] . ' archivo(s) de imagen)' : '') . '.', 'exito');
            } else {
                flash('😅 ' . (string)($r['error'] ?? 'No se pudo borrar la tienda.'), 'error');
            }
        }
    }
    // Se vuelve a la lista de negocios (el ancla del pedido del jefe).
    redirect('panel.php#mis-negocios');
}


// Caminante: si creó tiendas sin sesión, al entrar le quedan asignadas automáticamente
caminante_autoreclamar();

// Negocios del usuario (si es dueño)
$mis_negocios = [];
if (es_dueno()) {
    $stmt = db()->prepare("SELECT n.id, n.nombre, n.slug, n.estado, n.rating, n.vistas_count, n.creado_en,
                                 c.icono AS categoria_icono, c.nombre AS categoria_nombre,
                                 (SELECT f.ruta FROM directorio_fotos f WHERE f.negocio_id = n.id ORDER BY f.orden ASC LIMIT 1) AS imagen_portada
                          FROM directorio_negocios n
                          LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                          WHERE n.dueno_id = ?
                          ORDER BY n.creado_en DESC");
    $stmt->execute([$usuario['id']]);
    $mis_negocios = $stmt->fetchAll();
}

// Stats generales
$stats = [
    'total_negocios' => count($mis_negocios),
    'total_vistas' => array_sum(array_column($mis_negocios, 'vistas_count')),
    'mensajes_no_leidos' => 0,
];

foreach ($mis_negocios as $n) {
    $stats['mensajes_no_leidos'] += mensajes_no_leidos_negocio($n['id']);
}

// ⭐ El PLAN del usuario (gratis / premium) para la etiqueta del panel. Se lee con
// reglas_plan_usuario() porque es la única fuente del plan premium que hay hoy
// (directorio_usuarios.plan, el que cambia el Súper Admin).
$reglas_plan = reglas_plan_usuario($usuario['id']);

// 🗑️ 2026-09-13 (orden del jefe): el panel ya NO tiene nada de mensajería entre tiendas (B2B):
// ese módulo se retiró del sitio entero. En su hueco está «El ninja», el chat de ayuda, que es
// lo único que se consulta aquí.

$categorias = obtener_categorias();
$titulo_pagina = 'Mi panel';
include __DIR__ . '/includes/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px">
    <div>
        <h1 class="seccion__titulo">Hola, <?= e(explode(' ', $usuario['nombre'])[0]) ?> 👋</h1>
        <small style="color:var(--color-texto-claro)">Tipo de cuenta: <?= e($usuario['tipo']) ?><?php if (es_dueno()): ?> · Plan: <?= $reglas_plan['es_premium'] ? '⭐ Premium' : 'gratis' ?><?php endif; ?></small>
    </div>
    <?php if (es_dueno()): ?>
        <a href="<?= url('crear-tienda') ?>" class="btn">+ Crear otra tienda con El maestro 🛠️</a>
    <?php endif; ?>
</div>

<!-- Stats -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-bottom:24px">
    <?php /* 🆕 2026-09-16 (pedido del jefe): la tarjeta «Mis negocios» del marcador es un ENLACE ANCLA
             al área donde están sus tiendas (`#mis-negocios`, aquí abajo). Se ve igual que las otras
             tarjetas, pero al tocar el número el navegador baja solito a su lista de negocios. */ ?>
    <?php if (es_dueno()): ?>
    <a href="#mis-negocios" title="Ir a mis negocios"
       style="background:#fff;padding:16px;border-radius:12px;box-shadow:var(--sombra-tarjeta);border-top:4px solid var(--color-acento);text-decoration:none;display:block;color:inherit">
        <div style="font-size:24px;font-weight:700;color:var(--color-primario)"><?= $stats['total_negocios'] ?></div>
        <div style="font-size:12px;color:var(--color-texto-claro);text-transform:uppercase;letter-spacing:0.05em">Mis negocios ↓</div>
    </a>
    <?php else: ?>
    <div style="background:#fff;padding:16px;border-radius:12px;box-shadow:var(--sombra-tarjeta);border-top:4px solid var(--color-acento)">
        <div style="font-size:24px;font-weight:700;color:var(--color-primario)"><?= $stats['total_negocios'] ?></div>
        <div style="font-size:12px;color:var(--color-texto-claro);text-transform:uppercase;letter-spacing:0.05em">Mis negocios</div>
    </div>
    <?php endif; ?>
    <div style="background:#fff;padding:16px;border-radius:12px;box-shadow:var(--sombra-tarjeta);border-top:4px solid #22c55e">
        <div style="font-size:24px;font-weight:700;color:#15803d"><?= number_format($stats['total_vistas']) ?></div>
        <div style="font-size:12px;color:var(--color-texto-claro);text-transform:uppercase;letter-spacing:0.05em">Vistas totales</div>
    </div>
    <div style="background:#fff;padding:16px;border-radius:12px;box-shadow:var(--sombra-tarjeta);border-top:4px solid #f59e0b">
        <div style="font-size:24px;font-weight:700;color:#b45309"><?= $stats['mensajes_no_leidos'] ?></div>
        <div style="font-size:12px;color:var(--color-texto-claro);text-transform:uppercase;letter-spacing:0.05em">Mensajes nuevos</div>
    </div>
    <!-- 🗑️ 2026-09-17: aquí iba la tarjeta «🥷 El ninja · Pregúntame aquí →». El chat de ayuda dejó
         de vivir en todo el sitio (orden del jefe): ahora es **🧭 El guía**, el anfitrión de cada
         tienda, y solo se pinta DENTRO de las fichas (`/neg/<slug>`). Como en el panel ya no hay
         widget que abrir, la tarjeta se retiró para no dejar un botón que no hace nada. -->
    <?php // 📰 EL BOTÓN DE NOTICIAS (pedido del jefe, 2026-09-13): en el panel de TODOS los usuarios
          // con sesión, al lado del Ninja. Lleva al módulo de noticias locales (blanco y rojo oscuro,
          // como el resto del panel). ?>
    <a href="<?= url('noticias') ?>"
       style="background:#fff;padding:16px;border-radius:12px;box-shadow:var(--sombra-tarjeta);border-top:4px solid #6d071a;text-decoration:none;display:block">
        <div style="font-size:24px;font-weight:700;color:#6d071a">📰</div>
        <div style="font-size:12px;color:var(--color-texto-claro);text-transform:uppercase;letter-spacing:0.05em">Noticias de Chimbote</div>
        <div style="font-size:11px;color:#6d071a;margin-top:4px">Lo último de la zona →</div>
    </a>
</div>

<?php if (es_dueno() && $mis_negocios): ?>
    <!-- 📸 CAPTURA RÁPIDA DEL INVENTARIO — la experiencia de Caminante, ahora en
         el panel: cámara del celular con vista previa instantánea + dictado de la
         descripción con la Web Speech API (🎙️). La píldora del micrófono aparece
         solo si el navegador soporta la API (Chrome/Edge); nunca un botón muerto. -->
    <div style="background:linear-gradient(135deg,#6d071a,#8c0a22);border-radius:14px;padding:16px 18px;margin-bottom:22px;color:#fff;box-shadow:var(--sombra-tarjeta)">
        <div style="font-weight:800;font-size:17px">📸 Carga tu inventario con la cámara y la voz</div>
        <div style="font-size:13px;opacity:.94;margin-top:3px">
            Toma la foto → <b>la ves al instante</b> → dicta la descripción con el micrófono 🎙️ → publica.
            Nada de teclear en el celular. Y si te equivocas, lo editas o lo borras tú mismo cuando quieras.
        </div>
        <!-- Los negocios pueden ser muchos (una cuenta admin tiene decenas): la fila
             se desliza en horizontal en vez de apilarse en 12 renglones de botones. -->
        <div style="display:flex;gap:8px;margin-top:12px;overflow-x:auto;padding-bottom:4px;-webkit-overflow-scrolling:touch">
            <?php foreach ($mis_negocios as $n): ?>
                <a href="<?= url('productos.php?n=' . (int)$n['id']) ?>#crear"
                   style="display:inline-flex;align-items:center;gap:6px;background:#fff;color:#6d071a;font-weight:800;padding:11px 18px;border-radius:999px;text-decoration:none;font-size:14px;white-space:nowrap;flex:0 0 auto">
                    📸 Agregar producto a <?= e($n['nombre']) ?>
                </a>
            <?php endforeach; ?>
        </div>
        <?php if (count($mis_negocios) > 3): ?>
            <div style="font-size:12px;opacity:.9;margin-top:7px">Desliza la fila para ver tus <?= count($mis_negocios) ?> negocios →</div>
        <?php endif; ?>

        <!-- 💼 OFRECER EMPLEO (2026-09-12): el dueño publica su solicitud de
             personal sin salir del panel. El aviso queda LIGADO a su tienda, así que también
             aparece en su ficha (ver `empleos_negocio_bloque_html()` en negocio.php) y no pasa
             por moderación: es su propio negocio el que lo publica. -->
        <div style="display:flex;gap:8px;margin-top:10px;overflow-x:auto;padding-bottom:4px;-webkit-overflow-scrolling:touch">
            <?php foreach ($mis_negocios as $n): ?>
                <a href="<?= url('empleos?negocio=' . (int)$n['id']) ?>#publicar"
                   style="display:inline-flex;align-items:center;gap:6px;background:#0f766e;color:#fff;font-weight:800;padding:11px 18px;border-radius:999px;text-decoration:none;font-size:14px;white-space:nowrap;flex:0 0 auto">
                    💼 Ofrecer empleo en <?= e($n['nombre']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- 🛠️ EL MAESTRO (módulo nuevo, 2026-09-14 — pedido del jefe): el asistente que arma la
             tienda conversando (pregunta por pregunta, con las fotos que ÉL mira y le comenta) y que
             también agrega productos a una tienda que ya existe. Va justo aquí, al lado del
             inventario, que es el momento en que el dueño está cargando cosas. Si todavía no tiene
             ninguna tienda, el botón es para armarla. -->
        <div style="display:flex;gap:8px;margin-top:10px;overflow-x:auto;padding-bottom:4px;-webkit-overflow-scrolling:touch">
            <a href="<?= url($mis_negocios ? 'crear-tienda?modo=producto' : 'crear-tienda') ?>"
               style="display:inline-flex;align-items:center;gap:6px;background:#ea6a12;color:#fff;font-weight:800;padding:11px 18px;border-radius:999px;text-decoration:none;font-size:14px;white-space:nowrap;flex:0 0 auto">
                🛠️ <?= $mis_negocios ? 'Agregar un producto hablando con El maestro' : 'Armar mi tienda con El maestro' ?>
            </a>
        </div>
    </div>
<?php endif; ?>

<!-- Tiendas cerca de mí -->
<!-- 🎨 Naranja (2026-09-14, pedido del jefe): la tarjeta era verde y se confundía con WhatsApp.
     Misma paleta que el botón de todo el sitio (guía §6.4). -->
<div style="background:linear-gradient(105deg,#a3123c 0%,#d0312a 48%,#f0861c 100%);border-radius:14px;padding:16px 18px;margin-bottom:22px;color:#fff;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;box-shadow:var(--sombra-tarjeta)">
    <div>
        <div style="font-weight:800;font-size:17px">📍 Tiendas cerca de mí</div>
        <div style="font-size:13px;opacity:.92">Encuentra las tiendas más cercanas a tu ubicación. Ideal para usar en el celular (Android/iPhone).</div>
    </div>
    <a href="<?= url('tiendas-cerca-de-mi.html') ?>" target="_blank" rel="noopener" style="background:#fff;color:#9a3412;font-weight:800;padding:11px 18px;border-radius:999px;text-decoration:none;font-size:15px;white-space:nowrap">Abrir →</a>
</div>

<?php
// 🤝 Proveedores y alianzas recomendadas — el mapa de afinidades mirado desde el vendedor.
// Módulo aparte (includes/panel_reco.php) para no engordar este archivo: si faltara,
// el panel sigue funcionando y solo desaparece la sección.
// Detalle: GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md §4.
$__reco_panel = __DIR__ . '/includes/panel_reco.php';
if (is_file($__reco_panel)) include_once $__reco_panel;
if (function_exists('panel_reco_html')) echo panel_reco_html();
?>

<!-- Mis negocios -->
<?php /* 🆕 El ancla del pedido del jefe: la tarjeta «Mis negocios» de arriba lleva aquí.
         `scroll-margin-top` deja aire para que el título no quede debajo de la cabecera pegajosa. */ ?>
<section id="mis-negocios" style="scroll-margin-top:96px">
<h2 class="seccion__titulo" style="font-size:18px;margin-bottom:12px">🏪 Mis negocios
    <span style="font-size:13px;font-weight:600;color:var(--color-texto-claro)"><?= count($mis_negocios) ?> tienda<?= count($mis_negocios) === 1 ? '' : 's' ?></span>
</h2>

<?php if (empty($mis_negocios)): ?>
    <div class="empty-state">
        <div class="empty-state__icono">🏪</div>
        <div class="empty-state__titulo">Aún no tienes negocios registrados</div>
        <?php if (es_dueno()): ?>
            <p>Créala ahora con El maestro: sube 8 fotos de tu negocio y él la arma.</p>
            <a href="<?= url('crear-tienda') ?>" class="btn" style="margin-top:12px">🛠️ Crear mi tienda</a>
        <?php else: ?>
            <p>Si eres dueño de un negocio, actualiza tu tipo de cuenta a "dueño".</p>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="grid-negocios">
        <?php foreach ($mis_negocios as $n): ?>
            <?php
            // 🆕 La tarjeta DEJÓ de ser un `<a>` que envolvía todo (2026-09-16): dentro van DOS BOTONES
            // (Editar y Eliminar) y un enlace no puede llevar botones dentro — el toque se iba al
            // enlace de la ficha en vez de al botón. Ahora el enlace es la foto y el nombre, y los
            // botones van debajo, en su propia fila (que es como lo pidió el jefe).
            $__id     = (int)$n['id'];
            $__url    = url_negocio($n['slug']);
            // 🆕 2026-09-16: «Editar» abre el EDITOR DE LA TIENDA (`mi-tienda.php?n=ID`: nombre, rubro,
            // zona, WhatsApp, horario, redes, fotos, texto y visibilidad) — y ahí TODO se guarda con la
            // contraseña del dueño. Los productos siguen en su propia pantalla (enlace dentro del editor).
            $__editar = url('mi-tienda.php?n=' . $__id);
            ?>
            <div class="card-negocio">
                <a href="<?= e($__url) ?>" style="text-decoration:none;color:inherit;display:block;flex:1">
                    <div class="card-negocio__imagen">
                        <?= img_tag($n['imagen_portada'] ?? '', $n['nombre'], ['sizes' => '(max-width: 640px) 92vw, 300px', 'onerror' => "this.src='" . url('assets/img/sin-foto.svg') . "'"]) ?>
                        <span class="card-negocio__badge"><?= e($n['categoria_icono'] ?? '🏪') ?></span>
                    </div>
                    <div class="card-negocio__body">
                        <h3 class="card-negocio__titulo"><?= e($n['nombre']) ?></h3>
                        <div class="card-negocio__categoria"><?= e($n['categoria_nombre'] ?? 'Sin categoría') ?></div>
                        <div class="card-negocio__meta">
                            <span style="font-size:11px;padding:2px 8px;border-radius:999px;font-weight:600;
                                background:<?= $n['estado'] === 'activo' ? '#dcfce7' : '#fef3c7' ?>;
                                color:<?= $n['estado'] === 'activo' ? '#15803d' : '#b45309' ?>">
                                <?= e($n['estado']) ?>
                            </span>
                            <span>👁 <?= number_format($n['vistas_count']) ?></span>
                        </div>
                    </div>
                </a>
                <?php /* 🆕 «un botón de editar y otro de eliminar» debajo de cada tienda (orden del jefe,
                         2026-09-16). **Editar** abre el editor COMPLETO de esa tienda
                         (`mi-tienda.php?n=ID`): nombre, rubro, zona, WhatsApp, horario, redes, fotos,
                         texto y visibilidad — y **todo se guarda con la contraseña del dueño**
                         (orden del jefe: *«si el dueño tiene control total de su tienda para editar,
                         siempre debe poner su contraseña»*). **Eliminar** abre la ventana que pide esa
                         misma contraseña (el formulario de verdad está en el modal, al final). */ ?>
                <div style="display:flex;gap:8px;padding:0 12px 12px">
                    <a href="<?= e($__editar) ?>"
                       style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;
                              padding:11px 10px;border-radius:10px;background:#eef4ff;border:1px solid #c7d7f5;
                              color:#123c6b;font-weight:700;font-size:15px;text-decoration:none">
                        ✏️ Editar
                    </a>
                    <button type="button" class="pn-borrar"
                            data-id="<?= $__id ?>" data-nombre="<?= e($n['nombre']) ?>"
                            style="flex:1;display:inline-flex;align-items:center;justify-content:center;gap:6px;
                                   padding:11px 10px;border-radius:10px;background:#fff1f2;border:1px solid #fecdd3;
                                   color:#be123c;font-weight:700;font-size:15px;cursor:pointer;font-family:inherit">
                        🗑️ Eliminar
                    </button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
</section>

<?php
/* 🗑️ 2026-09-17: AQUÍ VIVÍA LA SECCIÓN «🥷 EL NINJA, TU ASISTENTE».
 * Se retiró con el cambio de papel del bot (orden del jefe): el chat ya no es el asistente de todo
 * el sitio, es **🧭 El guía**, el anfitrión de cada tienda, y solo se pinta en las fichas
 * (`/neg/<slug>`, constante CHATBOT_SOLO_FICHAS). En el panel no hay ningún widget que abrir. */
?>

<?php
/* ================================================================================================
 * 🗑️ LA VENTANA QUE PIDE LA CONTRASEÑA PARA ELIMINAR (orden del jefe, 2026-09-16, textual:
 * *«pedir contraseña para eliminar»*). Nace ESCONDIDA y la abre el botón «🗑️ Eliminar» de cada
 * tienda. El formulario de verdad (el que borra) es este: POST a `panel.php` con el token CSRF, el
 * id de la tienda y LA CONTRASEÑA DEL DUEÑO — que el servidor verifica con `password_verify()`.
 * Se cierra con «Cancelar», tocando el fondo o con la tecla Escape.
 *
 * El CSS va EN LÍNEA a propósito (como el resto del panel): así no hay que subir el `?v=` de ningún
 * CSS y no se pelea con la caché de Cloudflare (trampa 39 de la guía del constructor de tiendas).
 * ============================================================================================== */
if (es_dueno() && $mis_negocios):
?>
<style>
/* 🔴 `[hidden]` TIENE QUE GANAR (trampa 37, la misma que mordió el 2026-09-16 con el compositor):
   el navegador esconde lo que lleva el atributo `hidden` con una regla FLOJA (`display:none`), y
   cualquier `display` nuestro la pisa. Como esta ventana es `display:flex`, nacía **ABIERTA** encima
   del panel (lo cazó la captura del panel del 2026-09-16). Con esta línea el atributo manda. */
.pn-modal[hidden]{display:none!important}
.pn-modal{position:fixed;inset:0;z-index:9999;display:flex;align-items:center;justify-content:center;padding:16px}
.pn-modal__velo{position:absolute;inset:0;background:rgba(11,20,26,.55)}
.pn-modal__caja{position:relative;width:100%;max-width:440px;background:#fff;border-radius:18px;padding:18px 16px 14px;
  box-shadow:0 24px 60px rgba(11,20,26,.35);max-height:90vh;overflow-y:auto;box-sizing:border-box}
.pn-modal__tit{margin:0 0 8px;font-size:19px;font-weight:800;color:#6d071a}
.pn-modal__txt{margin:0 0 12px;font-size:15.5px;line-height:1.5;color:#374151}
.pn-modal__lab{display:block;font-size:14px;font-weight:700;color:#374151;margin:0 0 6px}
.pn-modal__input{width:100%;box-sizing:border-box;padding:13px 14px;border:1.5px solid #e2c9a6;border-radius:12px;
  font-size:16px;font-family:inherit;background:#fff}
.pn-modal__input:focus{outline:none;border-color:#c9a06a;box-shadow:0 0 0 3px rgba(234,106,18,.15)}
.pn-modal__aviso{margin:12px 0;padding:10px 12px;border-radius:12px;background:#fff1f2;border:1px solid #fecdd3;
  color:#9f1239;font-size:13.5px;line-height:1.5}
.pn-modal__rojo{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:14px 16px;border:0;
  border-radius:999px;background:linear-gradient(180deg,#e11d48,#be123c);color:#fff;font-size:16px;font-weight:800;
  cursor:pointer;font-family:inherit;box-shadow:0 8px 20px rgba(190,18,60,.3)}
.pn-modal__cancelar{display:block;width:100%;margin-top:8px;padding:11px;border:0;border-radius:12px;background:transparent;
  color:#6b7280;font-size:15.5px;font-weight:600;cursor:pointer;font-family:inherit}
.pn-modal__cancelar:hover{background:#f3f4f6}
</style>

<div class="pn-modal" id="pnModal" hidden>
    <div class="pn-modal__velo" data-cerrar="1"></div>
    <div class="pn-modal__caja" role="dialog" aria-modal="true" aria-labelledby="pnModalTit">
        <h3 class="pn-modal__tit" id="pnModalTit">🗑️ Eliminar esta tienda</h3>
        <p class="pn-modal__txt">
            Vas a eliminar <strong id="pnNombre">tu tienda</strong> de DeChimbote.com.
            <strong>Escribe tu contraseña</strong> para confirmarlo.
        </p>
        <form method="post" action="<?= e(url('panel.php')) ?>">
            <?= csrf_campo() ?>
            <input type="hidden" name="accion" value="eliminar_tienda">
            <input type="hidden" name="negocio_id" id="pnId" value="">
            <label class="pn-modal__lab" for="pnPass">Tu contraseña</label>
            <input class="pn-modal__input" type="password" id="pnPass" name="password"
                   required autocomplete="current-password" placeholder="Tu contraseña de DeChimbote.com">
            <p class="pn-modal__aviso">
                ⚠️ Se borra la tienda <strong>con todos sus productos, fotos y opiniones</strong>.
                Esta acción <strong>no se puede deshacer</strong>.
            </p>
            <button type="submit" class="pn-modal__rojo">🗑️ Sí, eliminar definitivamente</button>
            <button type="button" class="pn-modal__cancelar" data-cerrar="1">Cancelar</button>
        </form>
    </div>
</div>

<script>
(function () {
    'use strict';
    var m = document.getElementById('pnModal');
    if (!m) return;
    var elId = document.getElementById('pnId');
    var elNom = document.getElementById('pnNombre');
    var elPass = document.getElementById('pnPass');

    function abrir(btn) {
        elId.value = btn.getAttribute('data-id') || '';
        elNom.textContent = btn.getAttribute('data-nombre') || 'tu tienda';
        elPass.value = '';
        m.hidden = false;
        document.body.style.overflow = 'hidden';
        setTimeout(function () { try { elPass.focus(); } catch (e) {} }, 60);
    }
    function cerrar() {
        m.hidden = true;
        document.body.style.overflow = '';
    }
    Array.prototype.forEach.call(document.querySelectorAll('.pn-borrar'), function (b) {
        b.addEventListener('click', function () { abrir(b); });
    });
    Array.prototype.forEach.call(m.querySelectorAll('[data-cerrar]'), function (c) {
        c.addEventListener('click', cerrar);
    });
    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && !m.hidden) cerrar();
    });
})();
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
