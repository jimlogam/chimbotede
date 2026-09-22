<?php
/**
 * precios.php — 💰 NUESTROS PRECIOS (URL: /precios)
 * =================================================
 * Landing del área Nosotros con los **5 planes** que dictó el jefe, la **oferta sin riesgo** de las 50
 * ventas y las preguntas de siempre. Es **una página propia** (no una sección con ancla dentro de
 * `/nosotros`): orden del jefe, 2026-09-21.
 *
 * ⚠️ Los planes viven en `includes/nosotros_area.php` (`nosotros_planes()`) y los números de la oferta
 *    en `includes/config_supremo.php` (`SUPREMO_OFERTA_*`): si el jefe cambia un precio, se cambia allá.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/nosotros_area.php';
require_once __DIR__ . '/includes/config_supremo.php';
iniciar_sesion();

$categorias = obtener_categorias();
$planes     = nosotros_planes();

// La oferta sin riesgo: los números salen del config (nunca escritos a mano aquí).
$of_tarifa  = (float)SUPREMO_OFERTA_TARIFA;
$of_ventas  = (int)SUPREMO_OFERTA_VENTAS;
$of_dias    = (int)SUPREMO_OFERTA_DIAS;
$of_com     = (float)SUPREMO_OFERTA_COMISION;

$titulo_pagina      = 'Precios · los 5 planes de DeChimbote.com';
$descripcion_pagina = 'Los cinco planes de DeChimbote.com con lo que incluye cada uno: Gratis (S/ 0), '
    . 'Emprende (S/ 20), Vende Más (S/ 50), Premium (S/ 96) y Aliados (S/ 150). Sin comisiones por venta '
    . 'y sin permanencia.';
$canonical_url      = url('precios');

$og_titulo      = '💰 Precios de DeChimbote.com';
$og_descripcion = '🎁 Plan Gratis · 📈 S/ 20 con 8 videos · 🤖 S/ 50 con tu robot de IA · '
    . '👑 S/ 96 Premium con tu app Android · 🤝 S/ 150 Aliados. Cero comisiones por venta.';

include __DIR__ . '/includes/header.php';
nosotros_area_css();
?>

<div class="ns-wrap">

  <div class="ns-pagehead">
    <p class="ns-pagehead__eyebrow"><?= e(SITE_NAME) ?> · Nosotros</p>
    <h1>💰 Nuestros precios</h1>
    <p>
      <b>Cinco planes</b> y todos con lo mismo de base: tu tienda publicada, tu WhatsApp a la vista y
      <b>cero comisiones por venta</b>. Empieza gratis y sube solo cuando tu negocio te lo pida.
    </p>
  </div>

  <?= nosotros_area_menu('precios') ?>

  <div class="ns-planes">
    <?php // 🎨 Cada plan con SU color: Gratis verde · Emprende azul · Vende Más violeta · Premium dorado · Aliados turquesa ?>
    <?php $tonos_plan = [3, 4, 5, 2, 6]; ?>
    <?php foreach ($planes as $i => $p): ?>
      <article class="ns-plan <?= e($p['tono']) ?>" style="<?= e(nosotros_tono($tonos_plan[$i] ?? $i)) ?>">
        <?php if ($p['sello'] !== ''): ?>
          <span class="ns-plan__sello"><?= e($p['sello']) ?></span>
        <?php endif; ?>
        <div class="ns-plan__head">
          <span class="ns-plan__emoji" aria-hidden="true"><?= $p['emoji'] ?></span>
          <span class="ns-plan__nombre"><?= e($p['nombre']) ?></span>
          <span class="ns-plan__precio">
            <b><?= e($p['precio']) ?></b>
            <span><?= e($p['pago']) ?></span>
          </span>
        </div>
        <p class="ns-plan__lema"><?= e($p['lema']) ?></p>
        <ul class="ns-plan__lista">
          <?php foreach ($p['incluye'] as $bien): ?>
            <li><?= e($bien) ?></li>
          <?php endforeach; ?>
        </ul>
        <?php if ($p['nota'] !== ''): ?>
          <p class="ns-plan__nota"><?= e($p['nota']) ?></p>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
  </div>

  <div class="ns-planes__pie">
    <?php $wa_planes = url_whatsapp_admin('¡Hola! 👋 Vi los planes en ' . url_actual() . ' y quiero activar uno para mi negocio.'); ?>
    <?php if ($wa_planes !== ''): ?>
      <a class="b3d b3d--wa" href="<?= e($wa_planes) ?>" target="_blank" rel="noopener">
        <?= wa_icono_svg() ?> <span>Quiero activar un plan</span>
      </a>
    <?php endif; ?>
    <a class="b3d b3d--granate" href="<?= e(url('crear-tienda')) ?>"><span>🛠️ Empezar gratis ahora</span></a>
  </div>

  <?php // 🎁 LA OFERTA SIN RIESGO: los números son los de includes/config_supremo.php
        //    (SUPREMO_OFERTA_TARIFA 20 · _VENTAS 50 · _DIAS 30 · _GRATIS 1). ?>
  <div class="ns-oferta">
    <h3>🎁 La promoción sin riesgo: <?= (int)$of_ventas ?> ventas en <?= (int)$of_dias ?> días</h3>
    <p>
      <b>El primer mes es gratis.</b> Armo tu tienda, la dejo lista con tus fotos y tus productos, y
      trabajo el mes completo contigo para que vendas.
    </p>
    <p>
      <b>Si no llego a las <?= (int)$of_ventas ?> ventas acordadas, no pagas nada.</b> Si funciona, sigues desde
      <b>S/ <?= (int)$of_tarifa ?> al mes</b> (el plan Emprende) y tú decides si quieres subir.
    </p>
    <p style="font-size:13.5px;color:#6b6b6b">
      Las <?= (int)$of_ventas ?> ventas son del producto o de los productos que acordemos al empezar. La cuenta es simple:
      con <?= (int)$of_ventas ?> ventas al mes, la tarifa de S/ <?= (int)$of_tarifa ?> es una comisión de menos del 1 % — cuando otros
      cobran <?= (int)$of_com ?> %.
    </p>
  </div>

  <div class="ns-faq">
    <h2 class="ns-h2" style="font-size:20px;margin-bottom:10px">❓ Las preguntas de siempre</h2>

    <details>
      <summary>¿Cobran comisión por lo que vendo?</summary>
      <p>No. Cero comisiones en los cinco planes. El cliente te escribe directo a tu WhatsApp y la venta
         es tuya: nosotros cobramos el plan, nada más.</p>
    </details>
    <details>
      <summary>¿Hay contrato o permanencia?</summary>
      <p>No. Se paga mes a mes. Si un mes no lo pagas, vuelves al plan Gratis sin perder tu tienda,
         tus fotos ni tus clientes.</p>
    </details>
    <details>
      <summary>¿Puedo cambiar de plan cuando quiera?</summary>
      <p>Sí, para arriba o para abajo, cuando quieras. El cambio se aplica desde el mes siguiente.</p>
    </details>
    <details>
      <summary>¿Necesito saber de computadoras?</summary>
      <p>No. Todo se hace desde el celular y con tu WhatsApp. La Inteligencia Artificial escribe los
         textos por ti: tú solo mandas las fotos y apruebas.</p>
    </details>
    <details>
      <summary>¿Y si mi negocio ya aparece publicado en el sitio?</summary>
      <p>Es tuyo: entra a <a href="<?= e(url('reclamar')) ?>">Reclamar mi negocio</a>, busca tu tienda y
         pásala a manejar tú, sin costo.</p>
    </details>
    <details>
      <summary>¿Los videos y la aplicación Android de qué planes son?</summary>
      <p>Los <b>videos (hasta 8)</b> vienen desde el plan de S/ 20. La <b>aplicación Android</b> para que
         tus clientes la descarguen viene con el plan Premium de S/ 96.</p>
    </details>
    <details>
      <summary>¿De quién es lo que publico y qué pasa si doy de baja mi sitio?</summary>
      <p>La titularidad exclusiva de los contenidos publicados corresponde al Prestador. La baja del
         establecimiento bajo el plan Gratuito extingue todo derecho a reclamo; en los planes de pago
         <b>es posible descargar la base de datos</b> y la información recopilada. El establecimiento
         puede ser modificado conforme a los criterios editoriales del servicio. El texto íntegro consta
         en las <a href="<?= e(url('privacidad')) ?>">Políticas de Privacidad y Condiciones Generales de
         Uso</a>.</p>
    </details>
  </div>

  <?php // 📚 Los tres documentos oficiales, para quien quiera el detalle formal de cada condición. ?>
  <?= nosotros_documentacion_html() ?>

  <div class="ns-cierre">
    <h3>¿Empezamos hoy?</h3>
    <p>Publica tu negocio gratis y en minutos: la Inteligencia Artificial arma la tienda por ti.</p>
    <a class="b3d b3d--crema" href="<?= e(url('crear-tienda')) ?>"><span>🛠️ Crear mi tienda gratis</span></a>
  </div>

</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
