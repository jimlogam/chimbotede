<?php
/**
 * includes/vista_tienda_ia_admin.php — 🛠️ EL MAESTRO EN EL SÚPER ADMIN
 * ==================================================================
 * Qué muestra: **el consumo y el embudo** del constructor de tiendas. Es la razón por la
 * que el módulo tiene su PROPIA clave de API (orden del jefe, 2026-09-14: *«por fuerza
 * tiene que usarse la API… para que de esa manera se pueda medir el consumo»*).
 *
 *   · Cuántas conversaciones se abrieron, cuántas terminaron en tienda publicada y
 *     cuántas se quedaron a medias (y EN QUÉ PASO se quedaron: eso dice qué preguntar mejor).
 *   · Cuántas llamadas a DeepSeek, con cuántos tokens, cuánto costaron y cuánto cuesta
 *     cada tienda publicada.
 *   · El saldo de la cuenta del constructor y cuántas tiendas quedan con ese saldo.
 *   · Un botón para borrar las fotos que dejó una conversación abandonada (solo `ia_*`).
 *
 * Usa las clases del panel (`.sa-grid`, `.sa-stat`, `.sa-card`, `.sa-table`): no trae CSS
 * propio, igual que los récords dentro de Estadísticas.
 * Se abre desde `superadmin.php?seccion=maestro`. Guía: GUIA_CONSTRUCTOR_DE_TIENDAS.md
 */

require_once __DIR__ . '/tienda_ia.php';

$ia_dias  = isset($_GET['ia_dias']) ? max(1, min(365, (int)$_GET['ia_dias'])) : 30;
$ia_hacer = (string)($_GET['ia_hacer'] ?? '');

$ia_mensaje = '';
if ($ia_hacer === 'limpiar' && es_admin()) {
    // 🧹 Solo borra archivos `ia_*` de más de 48 h que NO estén en ninguna ficha:
    //    es imposible que se lleve una foto buena de una tienda publicada.
    $n = tienda_ia_limpiar_huerfanas(48);
    $ia_mensaje = '🧹 Fotos de tiendas que se dejaron a medias, borradas: ' . (int)$n . '. (Solo se tocan las de más de 48 horas y que no están en ninguna ficha.)';
}

$t     = tienda_ia_stats($ia_dias, 'supremo');   // 👑 las ventas de El Supremo van en su propia pestaña
$saldo = tienda_ia_saldo();
$pasos = tienda_ia_pasos();

$por_tienda = $t['por_tienda'] > 0 ? $t['por_tienda'] : 0.004;   // 0,4 centavos si todavía no hay ninguna publicada
$quedan     = ($saldo !== null) ? (int)floor($saldo / $por_tienda) : null;

$ia_usd = function ($v) { return 'US$ ' . number_format((float)$v, 4, '.', ','); };
$ia_n   = function ($v) { return number_format((int)$v, 0, '.', ','); };
$ia_nota_paso = [
    'arranque'        => 'Se fue en la invitación de la entrada (antes de contar nada): aquí se ve si el gancho funciona.',
    'nombre'          => 'Dio su nombre y no siguió, o no le gustó la pregunta.',
    'trato'           => 'No quiso contar de qué trata su tienda.',
    'rubro'           => 'No encontró su rubro entre los que le propusimos.',
    'plan'            => 'Paso retirado (era la pregunta de cuántos productos).',
    'fotos_tienda'    => 'Se quedó en las fotos de la tienda: con 1 ya podía publicar, así que si se cae aquí es que no las tenía a mano.',
    'vendedor'        => 'No se reconoció en ninguno de los tres tipos de vendedor.',
    'distrito'        => 'No dio la ubicación ni eligió distrito.',
    'whatsapp'        => 'No quiso dar su WhatsApp.',
    'horario'         => 'Se quedó en el horario (que es opcional).',
    'resumen'         => 'Llegó al final y no se animó a publicar.',
    'publicado'       => 'Publicó su tienda y no siguió con el primer producto.',
    'producto_nombre' => 'No supo qué producto poner primero (las sugerencias no le sirvieron).',
    'producto_fotos'  => 'No tenía la foto del producto a mano.',
    'producto_precio' => 'Se trabó en el precio.',
    'producto_listo'  => 'Creó un producto y ahí se quedó.',
    'mas_productos'   => 'Le ofrecimos el WhatsApp del jefe para más productos.',
    'fin'             => 'Terminó todo. 👌',
];
?>

<h2 style="font-size:18px;margin-bottom:6px"><?= TIENDA_IA_EMOJI ?> <?= e(TIENDA_IA_NOMBRE) ?> — el constructor de tiendas</h2>
<p class="mini" style="margin-bottom:14px">
  Es la página <a href="<?= e(url('crear-tienda')) ?>" target="_blank" rel="noopener">dechimbote.com/crear-tienda</a>:
  un usuario <b>registrado</b> arma su tienda conversando (nombre, de qué trata, cuántos productos, fotos que la IA mira,
  cómo vende y su WhatsApp). Tiene <b>su propia clave</b> de la API de DeepSeek para que el gasto se mida aquí, aparte del chat de ayuda 🥷.
</p>

<?php if ($ia_mensaje !== ''): ?>
  <div class="sa-card" style="border-left:4px solid #16a34a"><?= e($ia_mensaje) ?></div>
<?php endif; ?>

<div class="sa-toolbar">
  <span class="mini" style="align-self:center"><b>Rango:</b></span>
  <?php foreach ([7 => '7 días', 30 => '30 días', 90 => '90 días', 365 => '1 año'] as $d => $lbl): ?>
    <a class="btn-mini <?= $ia_dias === $d ? 'b-ok' : 'b-ghost' ?>"
       href="<?= e(url('superadmin.php?seccion=maestro&ia_dias=' . $d)) ?>"><?= $lbl ?></a>
  <?php endforeach; ?>
  <a class="btn-mini b-ghost"
     href="<?= e(url('superadmin.php?seccion=maestro&ia_dias=' . $ia_dias . '&ia_hacer=limpiar')) ?>"
     onclick="return confirm('¿Borrar las fotos de las tiendas que se dejaron a medias (más de 48 h y que no están en ninguna ficha)?')">
     🧹 Borrar fotos de tiendas a medias
  </a>
</div>

<?php if (!$t['tablas']): ?>
  <div class="sa-card" style="border-left:4px solid #dc2626">
    Faltan las tablas del módulo. Se crean solas al abrir <a href="<?= e(url('crear-tienda')) ?>">/crear-tienda</a> con una sesión,
    o a mano: <code>directorio_ia_tiendas</code> y <code>directorio_ia_tiendas_log</code>.
  </div>
<?php else: ?>

  <div class="sa-grid">
    <div class="sa-stat"><div class="num"><?= $ia_n($t['sesiones']) ?></div><div class="lbl">Conversaciones</div><div class="mini"><?= $ia_n($t['en_curso']) ?> a medias · <?= $ia_n($t['abandonadas']) ?> dejadas</div></div>
    <div class="sa-stat"><div class="num"><?= $ia_n($t['publicadas']) ?></div><div class="lbl">Tiendas publicadas</div><div class="mini"><?= $t['sesiones'] > 0 ? round($t['publicadas'] * 100 / max(1, $t['sesiones'])) : 0 ?> % de las conversaciones</div></div>
    <div class="sa-stat"><div class="num"><?= $ia_usd($t['costo_usd']) ?></div><div class="lbl">Gasto de la IA</div><div class="mini"><?= $ia_n($t['llamadas']) ?> llamadas · <?= $ia_usd($t['por_llamada']) ?> cada una</div></div>
    <div class="sa-stat"><div class="num"><?= $ia_usd($t['por_tienda']) ?></div><div class="lbl">Costo por tienda</div><div class="mini">promedio real del rango</div></div>
    <div class="sa-stat"><div class="num"><?= $saldo !== null ? 'US$ ' . number_format($saldo, 2, '.', ',') : '—' ?></div><div class="lbl">Saldo de la cuenta</div><div class="mini"><?= $quedan !== null ? 'alcanza para ~' . $ia_n($quedan) . ' tiendas más' : 'no se pudo consultar' ?></div></div>
    <div class="sa-stat"><div class="num"><?= $ia_n($t['con_imagen']) ?></div><div class="lbl">Fotos que miró la IA</div><div class="mini"><?= $ia_n($t['fallos']) ?> llamadas fallaron · <?= $ia_n($t['ms_promedio']) ?> ms promedio</div></div>
  </div>

  <h3 style="font-size:16px;margin:20px 0 8px">🚦 Dónde se quedan las conversaciones que no terminan</h3>
  <?php if (!$t['embudo']): ?>
    <div class="empty">Ninguna conversación quedó a medias en este rango. 👌</div>
  <?php else: ?>
    <div class="sa-table-wrap">
      <table class="sa-table">
        <thead><tr><th>Se quedaron en…</th><th>Cuántas</th><th>Qué significa</th></tr></thead>
        <tbody>
          <?php foreach ($t['embudo'] as $paso => $n): ?>
            <tr>
              <td><b><?= e($pasos[$paso]['titulo'] ?? (string)$paso) ?></b> <span class="mini">(<?= e((string)$paso) ?>)</span></td>
              <td><?= $ia_n($n) ?></td>
              <td class="mini"><?= e($ia_nota_paso[$paso] ?? 'Paso sin nota.') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <h3 style="font-size:16px;margin:20px 0 8px">💸 Qué cuesta cada paso (tokens y dólares)</h3>
  <?php if (!$t['pasos']): ?>
    <div class="empty">Todavía no hay llamadas a la IA en este rango.</div>
  <?php else: ?>
    <div class="sa-table-wrap">
      <table class="sa-table">
        <thead><tr><th>Paso</th><th>Llamadas</th><th>Tokens</th><th>Gasto</th><th>Respuesta promedio</th></tr></thead>
        <tbody>
          <?php foreach ($t['pasos'] as $p): ?>
            <tr>
              <td><?= e($pasos[$p['paso']]['titulo'] ?? (string)$p['paso']) ?> <span class="mini">(<?= e((string)$p['paso']) ?>)</span></td>
              <td><?= $ia_n($p['n']) ?></td>
              <td><?= $ia_n($p['tokens']) ?></td>
              <td><?= $ia_usd($p['costo']) ?></td>
              <td class="mini"><?= $ia_n($p['ms']) ?> ms</td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="mini" style="margin-top:8px">
      Hoy: <b><?= $ia_n($t['hoy']['llamadas']) ?></b> llamadas y <b><?= $ia_usd($t['hoy']['costo']) ?></b>.
      ⚠️ Los precios son los de <b>fuera de hora punta</b>; en punta (20:00-23:00 y 01:00-05:00 de Lima, de lunes a viernes) cuesta el doble.
    </p>
  <?php endif; ?>

  <h3 style="font-size:16px;margin:20px 0 8px">🕐 Las últimas conversaciones</h3>
  <?php if (!$t['ultimas']): ?>
    <div class="empty">Todavía no hay ninguna.</div>
  <?php else: ?>
    <div class="sa-table-wrap">
      <table class="sa-table">
        <thead><tr><th>#</th><th>Persona</th><th>Modo</th><th>Paso</th><th>Estado</th><th>Llamadas</th><th>Gasto</th><th>Cuándo</th></tr></thead>
        <tbody>
          <?php foreach ($t['ultimas'] as $u): ?>
            <tr>
              <td><?= (int)$u['id'] ?></td>
              <td><?= e((string)($u['usuario'] ?? ('usuario ' . (int)$u['usuario_id']))) ?></td>
              <td class="mini"><?= e((string)$u['modo']) ?></td>
              <td class="mini"><?= e($pasos[(string)$u['paso']]['titulo'] ?? (string)$u['paso']) ?></td>
              <td class="mini"><?= e((string)$u['estado']) ?></td>
              <td><?= $ia_n($u['llamadas']) ?></td>
              <td class="mini"><?= $ia_usd($u['costo_usd']) ?></td>
              <td class="mini"><?= e(date('d/m H:i', strtotime((string)$u['creado_en']))) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

  <p class="mini" style="margin-top:16px">
    🔒 La clave de esta cuenta vive en <code>includes/config_tienda_ia.php</code> (el <code>.htaccess</code> bloquea
    <code>/includes/</code>, así que nunca se puede leer desde internet). El modelo es
    <code><?= e(TIENDA_IA_MODELO) ?></code> y va con el razonamiento apagado
    (<code>reasoning_effort: <?= e(TIENDA_IA_RAZONAMIENTO) ?></code>): medido el 2026-09-14, así responde en 1 segundo y gasta
    20 tokens en vez de 254 —y deja de quedarse mudo, que es lo que hacía al pensar de más.
  </p>
<?php endif; ?>
