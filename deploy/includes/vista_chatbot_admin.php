<?php
/**
 * includes/vista_chatbot_admin.php — PANEL DE CONFIGURACIÓN DE «NINJA» (el chat de ayuda)
 * ======================================================================================
 * Pedido del jefe (2026-09-13): «pon un panel de configuración enlazado a mi panel de Superadmin».
 *
 * Lo pinta `superadmin.php` en la sección **`chatbot`** (menú: 🥷 Ninja (chat)) y guarda todo en la
 * tabla `directorio_chatbot_ajustes` (ver `includes/chatbot_ajustes.php`).
 *
 * Se puede cambiar SIN TOCAR ARCHIVOS: encender/apagar el bot, el título, el nombre, el emoji, las
 * **preguntas por persona y día** (visitante/registrado/premium), el precio de Premium, las fotos,
 * el tope diario de mensajes, el modelo y la clave de DeepSeek.
 * Y se ve de un vistazo: estado, uso de hoy y las últimas preguntas que hizo la gente.
 *
 * Guía: GUIA_CHATBOT_DEEPSEEK.md §2septies.
 */

require_once __DIR__ . '/chatbot.php';   // trae los ajustes aplicados + la cuota + el día + la actividad

/** Las últimas preguntas registradas (de hoy y de ayer), de la más nueva a la más vieja. */
function chatbot_vista_ultimas_preguntas($n = 12) {
    $lineas = [];
    foreach (array_reverse((array)@glob(chatbot_dir('log') . '/*.jsonl')) as $f) {
        $l = @file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!$l) continue;
        $lineas = array_merge($lineas, array_reverse(array_slice($l, -$n)));
        if (count($lineas) >= $n) break;
    }
    $out = [];
    foreach (array_slice($lineas, 0, $n) as $l) {
        $d = json_decode($l, true);
        if (is_array($d)) $out[] = $d;
    }
    return $out;
}

/** Cuánto se gastó hoy (estimado con los precios oficiales de DeepSeek, fuera de punta). */
function chatbot_vista_gasto_hoy() {
    $r = ['preguntas' => 0, 'ia' => 0, 'img' => 0, 'tok_in' => 0, 'tok_out' => 0, 'tok_cache' => 0, 'usd' => 0.0];
    $f = chatbot_dir('log') . '/' . date('Y-m-d') . '.jsonl';
    if (!is_file($f)) return $r;
    foreach ((array)@file($f, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $l) {
        $d = json_decode($l, true);
        if (!is_array($d)) continue;
        $r['preguntas']++;
        if (!empty($d['img'])) $r['img']++;
        if (($d['fuente'] ?? '') !== 'ia' || empty($d['tok_in'])) continue;
        $r['ia']++;
        $in   = (int)$d['tok_in'];
        $out  = (int)($d['tok_out'] ?? 0);
        $hit  = (int)($d['cache_hit'] ?? 0);
        $r['tok_in'] += $in; $r['tok_out'] += $out; $r['tok_cache'] += $hit;
        $r['usd'] += $hit / 1e6 * 0.007 + max(0, $in - $hit) / 1e6 * 0.22 + $out / 1e6 * 0.66;
    }
    return $r;
}

$ajustes_listos = chatbot_ajustes_instalar();
$catalogo = chatbot_ajustes_defecto();
$valores  = [];
foreach ($catalogo as $k => $info) $valores[$k] = chatbot_ajustes_valor($k);

$gasto   = chatbot_vista_gasto_hoy();
$log_dir = chatbot_dir('log');
$cuota_estado = chatbot_cuota_estado(['logueado' => false, 'premium' => false, 'usuario_id' => 0,
                                      'ip_hash' => 'panel', 'es_dueno' => false, 'nombre' => '', 'tipo' => '', 'negocios' => []]);

/** «50» → «50 preguntas»; «0» → «sin límite». En el panel del jefe SÍ se pueden ver los números. */
$cuota_txt = function ($n) {
    $n = (int)$n;
    return $n <= 0 ? 'sin límite' : $n . ' preguntas al día';
};
?>

<h2 style="font-size:18px;margin-bottom:4px"><?= e(CHATBOT_EMOJI) ?> <?= e(CHATBOT_TITULO) ?> — el chat de ayuda del sitio</h2>
<p style="font-size:13px;color:var(--color-texto-claro);margin-bottom:14px">
  Aquí se configura <b>todo lo del bot</b> sin tocar archivos. Lo que guardes aquí manda sobre los
  valores de fábrica (la tabla <code>directorio_chatbot_ajustes</code>).
  <?php if (!$ajustes_listos): ?>
    <br>⚠️ <b>La tabla de ajustes todavía no existe</b> (se crea sola al abrir esta página; si no,
    seguramente el usuario no es administrador).
  <?php endif; ?>
</p>

<!-- ===== Lo que está pasando ahora ===== -->
<div class="sa-grid" style="margin-bottom:16px">
  <div class="sa-stat"><div class="num"><?= CHATBOT_ACTIVO ? 'SÍ' : 'NO' ?></div><div class="lbl">Chat encendido</div></div>
  <div class="sa-stat"><div class="num"><?= $gasto['preguntas'] ?></div><div class="lbl">Preguntas hoy</div></div>
  <div class="sa-stat"><div class="num"><?= $gasto['ia'] ?></div><div class="lbl">Contestadas por la IA</div></div>
  <div class="sa-stat"><div class="num"><?= $gasto['img'] ?></div><div class="lbl">Fotos miradas hoy</div></div>
  <div class="sa-stat"><div class="num">US$ <?= number_format($gasto['usd'], 4) ?></div><div class="lbl">Gastado hoy (estimado)</div></div>
</div>

<div class="sa-card" style="margin-bottom:14px">
  <h3>Resumen de cómo está funcionando</h3>
  <p class="mini">
    🔢 <b>Cuota de preguntas por persona y día:</b><br>
    👤 <b>Visitante sin cuenta:</b> <?= e($cuota_txt($valores['cuota_visitante'])) ?> ·
    cuando se le acaban, el bot lo invita a <b>registrarse gratis</b>.<br>
    🏪 <b>Usuario registrado:</b> <?= e($cuota_txt($valores['cuota_registrado'])) ?> ·
    cuando se le acaban, el bot le ofrece ⭐ <b>Premium</b>.<br>
    ⭐ <b>Premium:</b> <?= e($cuota_txt($valores['cuota_premium'])) ?>.<br>
    🔒 <b>Recordatorio del jefe:</b> al visitante <b>NUNCA se le dicen los números</b> (ni los que
    tiene ni los que le quedan): solo se le avisa cuando ya no hay más. Nada de relojes ni contadores.<br>
    ✂️ <b>Respuestas cortas:</b> tope de <?= (int)CHATBOT_MAX_TOKENS ?> tokens y la regla de
    <b>no pasar de 200 palabras</b> (así el saldo rinde).<br>
    💰 <b>Gasto medido:</b> ~US$ 0,0014 por pregunta · ~US$ 0,0071 por sesión de 5 preguntas
    (una foto suma ~US$ 0,0001). Con US$ 5 salen ~3.500 preguntas.
    <?php if ($gasto['tok_in']): ?>
      <br>🔢 Hoy: <?= number_format($gasto['tok_in']) ?> tokens de entrada
      (<?= number_format($gasto['tok_cache']) ?> en caché), <?= number_format($gasto['tok_out']) ?> de salida.
    <?php endif; ?>
    <br>🔑 <b>Clave de DeepSeek:</b>
    <?= CHATBOT_DEEPSEEK_KEY !== '' ? '✅ puesta (termina en …' . e(substr(CHATBOT_DEEPSEEK_KEY, -4)) . ')'
                                    : '❌ VACÍA (el bot contesta solo el guion local)' ?>.
    <br>🧠 <b>Modelos:</b> texto <code><?= e(CHATBOT_MODELO) ?></code> ·
    fotos <code><?= e(CHATBOT_MODELO_VISION) ?></code>.
    <br>📒 <b>Registro de preguntas:</b> se guarda <?= (int)CHATBOT_LOG_DIAS ?> días en
    <code>cache/chatbot/log/</code> (también se puede leer desde la PC con <code>python __ver_chat_log.py bajar</code>).
  </p>
</div>

<!-- ===== FORMULARIO DE CONFIGURACIÓN ===== -->
<form method="post">
  <?= csrf_campo() ?>
  <input type="hidden" name="accion" value="chatbot_guardar">
  <input type="hidden" name="seccion" value="chatbot">

  <div class="sa-card" style="margin-bottom:14px">
    <h3>🥷 El bot</h3>
    <?php foreach (['activo','titulo','nombre','emoji'] as $k): $info = $catalogo[$k]; ?>
      <div style="margin-bottom:10px">
        <?php if ($info['tipo'] === 'onoff'): ?>
          <label style="display:flex;align-items:center;gap:8px;font-size:14px">
            <input type="checkbox" name="chatbot[<?= e($k) ?>]" value="1" <?= $valores[$k] === '1' ? 'checked' : '' ?>>
            <b><?= e($info['label']) ?></b>
          </label>
        <?php else: ?>
          <label style="display:block;font-size:14px;font-weight:700;margin-bottom:3px"><?= e($info['label']) ?></label>
          <input type="text" name="chatbot[<?= e($k) ?>]" value="<?= e($valores[$k]) ?>"
                 style="width:100%;max-width:380px;padding:9px;border:1px solid var(--color-borde);border-radius:8px;font-size:15px">
        <?php endif; ?>
        <div class="mini" style="margin-top:2px"><?= $info['ayuda'] ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="sa-card" style="margin-bottom:14px">
    <h3>🔢 Cuota de preguntas (por persona y día)</h3>
    <?php foreach (['cuota_visitante','cuota_registrado','cuota_premium','cierre_seg'] as $k): $info = $catalogo[$k]; ?>
      <div style="margin-bottom:10px">
        <label style="display:block;font-size:14px;font-weight:700;margin-bottom:3px">
          <?= e($info['label']) ?>
          <span class="mini" style="font-weight:400">
            (ahora: <?= e($k === 'cierre_seg' ? (int)$valores[$k] . ' segundos' : $cuota_txt($valores[$k])) ?><?= $k === 'cuota_premium' && $valores[$k] === '0' ? ' ⭐' : '' ?>)
          </span>
        </label>
        <input type="number" name="chatbot[<?= e($k) ?>]" value="<?= e($valores[$k]) ?>" min="0" max="5000"
               style="width:130px;padding:9px;border:1px solid var(--color-borde);border-radius:8px;font-size:15px">
        <div class="mini" style="margin-top:2px"><?= $info['ayuda'] ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="sa-card" style="margin-bottom:14px">
    <h3>⏳ Cómo se siente la espera</h3>
    <?php foreach (['pensando_frases','pensando_ms','espera_min_ms'] as $k): $info = $catalogo[$k]; ?>
      <div style="margin-bottom:10px">
        <label style="display:block;font-size:14px;font-weight:700;margin-bottom:3px"><?= e($info['label']) ?></label>
        <input type="<?= $info['tipo'] === 'numero' ? 'number' : 'text' ?>" name="chatbot[<?= e($k) ?>]" value="<?= e($valores[$k]) ?>"
               min="0" max="5000"
               style="width:<?= $info['tipo'] === 'numero' ? '130px' : '100%;max-width:420px' ?>;padding:9px;border:1px solid var(--color-borde);border-radius:8px;font-size:15px">
        <div class="mini" style="margin-top:2px"><?= $info['ayuda'] ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="sa-card" style="margin-bottom:14px">
    <h3>💸 Dinero, fotos y límites</h3>
    <?php foreach (['precio_premium','imagenes','img_dia','limite_global_dia','log_dias'] as $k): $info = $catalogo[$k]; ?>
      <div style="margin-bottom:10px">
        <?php if ($info['tipo'] === 'onoff'): ?>
          <label style="display:flex;align-items:center;gap:8px;font-size:14px">
            <input type="checkbox" name="chatbot[<?= e($k) ?>]" value="1" <?= $valores[$k] === '1' ? 'checked' : '' ?>>
            <b><?= e($info['label']) ?></b>
          </label>
        <?php else: ?>
          <label style="display:block;font-size:14px;font-weight:700;margin-bottom:3px"><?= e($info['label']) ?></label>
          <input type="<?= $info['tipo'] === 'numero' ? 'number' : 'text' ?>" name="chatbot[<?= e($k) ?>]"
                 value="<?= e($valores[$k]) ?>"
                 style="width:<?= $info['tipo'] === 'numero' ? '130px' : '100%;max-width:380px' ?>;padding:9px;border:1px solid var(--color-borde);border-radius:8px;font-size:15px">
        <?php endif; ?>
        <div class="mini" style="margin-top:2px"><?= $info['ayuda'] ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="sa-card" style="margin-bottom:14px">
    <h3>🔧 Avanzado</h3>
    <?php foreach (['modelo'] as $k): $info = $catalogo[$k]; ?>
      <div style="margin-bottom:10px">
        <label style="display:block;font-size:14px;font-weight:700;margin-bottom:3px"><?= e($info['label']) ?></label>
        <input type="text" name="chatbot[<?= e($k) ?>]" value="<?= e($valores[$k]) ?>"
               style="width:100%;max-width:380px;padding:9px;border:1px solid var(--color-borde);border-radius:8px;font-size:15px">
        <div class="mini" style="margin-top:2px"><?= $info['ayuda'] ?></div>
      </div>
    <?php endforeach; ?>
    <div style="margin-bottom:10px">
      <label style="display:block;font-size:14px;font-weight:700;margin-bottom:3px"><?= e($catalogo['clave']['label']) ?></label>
      <input type="text" name="chatbot[clave]" value="" placeholder="<?= CHATBOT_DEEPSEEK_KEY !== '' ? 'puesta (…' . e(substr(CHATBOT_DEEPSEEK_KEY, -4)) . ') — escribe otra solo si quieres cambiarla' : 'sk-…' ?>"
             autocomplete="off"
             style="width:100%;max-width:520px;padding:9px;border:1px solid var(--color-borde);border-radius:8px;font-size:15px">
      <div class="mini" style="margin-top:2px"><?= $catalogo['clave']['ayuda'] ?></div>
    </div>
  </div>

  <button type="submit" style="background:var(--color-primario);color:#fff;font-weight:800;padding:12px 22px;border:0;border-radius:999px;font-size:15px;cursor:pointer">
    💾 Guardar la configuración
  </button>
</form>

<!-- ===== Acciones rápidas ===== -->
<div class="sa-card" style="margin:16px 0 14px">
  <h3>🧹 Acciones rápidas</h3>
  <div style="display:flex;gap:8px;flex-wrap:wrap">
    <form method="post" style="display:inline">
      <?= csrf_campo() ?>
      <input type="hidden" name="accion" value="chatbot_diario_refrescar">
      <input type="hidden" name="seccion" value="chatbot">
      <button type="submit" class="btn-mini b-ghost">🌤️ Volver a pedir el clima y las noticias</button>
    </form>
    <form method="post" style="display:inline"
          onsubmit="return confirm('¿Devolverle las preguntas de hoy a TODO el mundo? (los que ya se quedaron sin chat podrán volver a escribir)')">
      <?= csrf_campo() ?>
      <input type="hidden" name="accion" value="chatbot_cuota_limpiar">
      <input type="hidden" name="seccion" value="chatbot">
      <button type="submit" class="btn-mini b-ghost">🔢 Devolver las preguntas de hoy a todos</button>
    </form>
  </div>
  <p class="mini" style="margin-top:8px">
    El clima y las noticias se piden <b>una vez al día</b> y se guardan 24 h; con el primer botón se
    vuelven a pedir ahora mismo. La cuota de preguntas se reinicia sola cada día (por la fecha de Lima).
  </p>
</div>

<!-- ===== Últimas preguntas ===== -->
<div class="sa-card" style="margin-bottom:14px">
  <h3>📒 Las últimas preguntas que le hicieron</h3>
  <?php $ultimas = chatbot_vista_ultimas_preguntas(12); ?>
  <?php if (!$ultimas): ?>
    <p class="mini">Todavía no hay preguntas registradas.</p>
  <?php else: ?>
    <div class="sa-table-wrap"><table class="sa-table">
      <thead><tr><th>Hora</th><th>Quién</th><th>Pregunta</th><th>Cómo se contestó</th></tr></thead>
      <tbody>
      <?php foreach ($ultimas as $u): ?>
        <tr>
          <td class="mini"><?= e(substr((string)($u['t'] ?? ''), 11, 8)) ?></td>
          <td class="mini"><?= !empty($u['logueado']) ? '🏪 registrado' : '👤 visitante' ?><?= !empty($u['img']) ? ' · 📷 ' . (int)$u['img'] . ' KB' : '' ?></td>
          <td><?= e(mb_substr((string)($u['q'] ?? ''), 0, 90)) ?></td>
          <td class="mini">
            <?php
            $fuentes = ['ia' => '🧠 IA (DeepSeek)', 'local' => '📜 guion del sitio', 'dia' => '🌤️ datos del día',
                        'cuota' => '🔢 se le acabaron las preguntas', 'limite' => '🚦 tope anti-abuso',
                        'actividad' => '👀 su historial', 'aviso' => '⚠️ sin respuesta'];
            echo e($fuentes[$u['fuente'] ?? ''] ?? ($u['fuente'] ?? '?'));
            if (!empty($u['tok_in'])) echo ' · ' . number_format((int)$u['tok_in']) . ' tok';
            ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>
    <?php if (count($ultimas) >= 12): ?>
      <p class="mini" style="margin-top:8px"><a href="<?= url('superadmin.php?seccion=chatbot') ?>">🔄 Volver a cargar</a> para ver las más nuevas.</p>
    <?php endif; ?>
  <?php endif; ?>
</div>
