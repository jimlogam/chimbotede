<?php
/**
 * como-se-usa.php — 📖 CÓMO SE USA ESTE SITIO (URL: /como-se-usa)
 * ===============================================================
 * Landing del área Nosotros: la guía de uso completa, en dos caminos (el que busca o compra y el que
 * publica y vende). Es **una página propia**, no una sección con ancla dentro de `/nosotros` (orden del
 * jefe, 2026-09-21).
 *
 * Los estilos, el menú interno del área y los datos viven en `includes/nosotros_area.php`.
 * El detalle funcional de cada característica está en el Manual de Uso (`/manual-de-uso`).
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/nosotros_area.php';
iniciar_sesion();

$categorias = obtener_categorias();

$titulo_pagina      = 'Cómo se usa este sitio · la guía en dos caminos';
$descripcion_pagina = 'La guía de DeChimbote.com: cómo buscar y pedir un producto paso a paso, y cómo '
    . 'publicar y administrar tu negocio con la Inteligencia Artificial del sitio.';
$canonical_url      = url('como-se-usa');

$og_titulo      = '📖 Cómo se usa DeChimbote.com';
$og_descripcion = '🗺️ Dos caminos y ninguno tiene misterio: si vienes a buscar o comprar y si tienes '
    . 'un negocio y quieres vender. El paso a paso, en una sola página.';

include __DIR__ . '/includes/header.php';
nosotros_area_css();
?>

<div class="ns-wrap">

  <div class="ns-pagehead">
    <p class="ns-pagehead__eyebrow"><?= e(SITE_NAME) ?> · Nosotros</p>
    <h1>📖 Cómo se usa este sitio</h1>
    <p>
      Dos caminos y ninguno tiene misterio: uno es <b>si vienes a buscar o a comprar</b> y el otro es
      <b>si tienes un negocio y quieres vender</b>. Toca el que necesites.
    </p>
  </div>

  <?= nosotros_area_menu('como-se-usa') ?>

  <div class="ns-guia">
    <div class="ns-card">
      <h3>🛍️ Si vienes a buscar o a comprar</h3>
      <ol class="ns-pasos">
        <li><b>Escribe lo que buscas</b> en el buscador de arriba («ceviche», «zapatillas», «gasfitero»).
            Escribe tranquilo: el buscador perdona los errores de tipeo. Si prefieres, toca el
            🎙️ micrófono y dilo en voz alta.</li>
        <li><b>¿Quieres lo más cercano?</b> Toca el botón naranja <b>«📍 Ver negocios cerca de mí»</b> y
            comparte tu ubicación: el sitio te muestra primero lo que tienes al lado.</li>
        <li><b>Entra a la tienda que te guste.</b> Ahí están sus fotos, sus productos con precios, su
            horario, su dirección y cómo llegar. Dentro de la ficha, el 🧭 <b>guía</b> responde
            cualquier duda (si atienden ahora, si tienen stock, dónde queda exactamente).</li>
        <li><b>Arma tu pedido:</b> toca el corazón ❤️ <b>«Me interesa»</b> en cada producto que quieras.</li>
        <li><b>Manda el pedido:</b> con todo marcado, sale <b>un solo mensaje de WhatsApp</b> a la tienda,
            con los productos y el total. Se lo mandas y ya estás en contacto con el vendedor.</li>
        <li><b>Deja tu opinión:</b> después de comprar, cuéntale a los demás cómo te fue. Es anónima.</li>
        <li><b>Y no solo hay tiendas:</b> también están las 📰 noticias del día y los 💼 avisos de trabajo
            de Chimbote, Nuevo Chimbote y Santa.</li>
      </ol>
      <div class="ns-atajos">
        <a href="<?= e(url('buscar.php')) ?>">🔎 Buscar negocios</a>
        <a href="<?= e(url('rubros')) ?>">🏷️ Ver todos los rubros</a>
        <a href="<?= e(url('noticias')) ?>">📰 Noticias</a>
        <a href="<?= e(url('empleos')) ?>">💼 Empleos</a>
      </div>
    </div>

    <div class="ns-card">
      <h3>🏪 Si tienes un negocio y quieres vender</h3>
      <ol class="ns-pasos">
        <li><b>Publica tu tienda gratis.</b> Toca <b>«🛠️ Crear mi tienda»</b> y sube unas fotos de tu
            local y de tus productos. La <b>Inteligencia Artificial</b> arma la tienda, escribe tu
            descripción y deja tus productos listos con su foto, su nombre y su precio.</li>
        <li><b>Revisa tu WhatsApp.</b> Ahí te llega tu enlace, tu usuario y tu clave, el mismo día.</li>
        <li><b>Deja todo a tu gusto:</b> entra a tu 👤 panel y cambia tus fotos, tus precios, tu horario,
            tu dirección, tus formas de pago y tu entrega <b>cuando quieras</b>, sin pedirle permiso a nadie.</li>
        <li><b>Comparte tu enlace</b> por WhatsApp, Facebook o TikTok. También tienes tu
            <b>📸 caminante</b>: se agrega la tienda con la cámara en 2 minutos, sin complicarte.</li>
        <li><b>Mira tus números:</b> visitas, clics de llamar, mensajes y pedidos de tu ficha, en tiempo real.</li>
        <li><b>¿Tu negocio ya está publicado y es tuyo?</b> Toca <b>«Reclamar mi negocio»</b> y pasa a
            manejarlo tú.</li>
      </ol>
      <div class="ns-atajos">
        <a href="<?= e(url('crear-tienda')) ?>">🛠️ Crear mi tienda con IA</a>
        <a href="<?= e(url('caminante/')) ?>">📸 Agregar con la cámara</a>
        <a href="<?= e(url('reclamar')) ?>">🙋 Reclamar mi negocio</a>
        <a href="<?= e(url('panel.php')) ?>">👤 Mi panel</a>
      </div>
    </div>
  </div>

  <p class="ns-manual">
    El detalle funcional de cada característica —publicación del establecimiento, catálogo,
    material gráfico, atención automatizada, estadísticas y geolocalización— consta en el
    <a href="<?= e(url('manual-de-uso')) ?>">Manual de Uso de la Plataforma</a>, disponible también
    para su descarga en formato PDF.
  </p>

  <?php // 📍 El botón de GPS de verdad (includes/btn_cerca.php): el mismo del menú y de la portada. ?>
  <?php require_once __DIR__ . '/includes/btn_cerca.php'; ?>
  <?= btn_tiendas_cerca_html('Ver negocios cerca de mí', 'toca y comparte tu ubicación') ?>

  <div class="ns-cierre">
    <h3>¿Y si quieres vender?</h3>
    <p>Publica tu negocio gratis y en minutos: la Inteligencia Artificial arma la tienda por ti.</p>
    <a class="ns-btn ns-btn--claro" href="<?= e(url('crear-tienda')) ?>">🛠️ Crear mi tienda gratis</a>
  </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
