<?php
/**
 * vista_banners_admin.php — Sección "📢 Banners" del superadmin.
 * Necesita (definidos en superadmin.php): $banners_admin, $banners_stats_dias,
 * $banners_categorias, $banners_temas_opts, $banner_edit.
 * Todos los banners usan EL MISMO tamaño de imagen (slot uniforme).
 */
if (!isset($banners_admin) || !is_array($banners_admin)) $banners_admin = [];

/* ---- Cálculos rápidos para las tarjetas ---- */
$nActivos = 0; $nOcultos = 0; $sumImp = 0; $sumClic = 0; $sumImpHoy = 0; $sumClicHoy = 0;
$hoy = date('Y-m-d');
foreach ($banners_admin as $b) {
    if ((int)$b['activo']) $nActivos++; else $nOcultos++;
    $sumImp  += (int)($b['impresiones'] ?? 0);
    $sumClic += (int)($b['clics'] ?? 0);
}
foreach (banners_stats_dias(1) as $d) {
    $sumImpHoy  += (int)($d['impresiones'] ?? 0);
    $sumClicHoy += (int)($d['clics'] ?? 0);
}

/* ---- Mapa id categoria -> nombre para mostrar rubros ---- */
$rubrosNombre = [];
foreach ($banners_categorias as $c) { $rubrosNombre[(int)$c['id']] = $c['nombre']; }

/* ---- Valores para el formulario (edición) ---- */
$fv = [
    'id' => 0, 'titulo' => '', 'texto' => '', 'imagen' => '', 'tema' => '', 'enlace' => '',
    'busqueda' => '',
    'fecha_inicio' => '', 'fecha_fin' => '', 'franjas' => ['manana','tarde','noche'], 'rubros' => [],
    'activo' => 1, 'orden' => 0,
];
if ($banner_edit) {
    $fv['id']           = (int)$banner_edit['id'];
    $fv['titulo']       = $banner_edit['titulo'] ?? '';
    $fv['texto']        = $banner_edit['texto'] ?? '';
    $fv['imagen']       = $banner_edit['imagen'] ?? '';
    $fv['tema']         = $banner_edit['tema'] ?? '';
    $fv['enlace']       = $banner_edit['enlace'] ?? '';
    $fv['busqueda']     = $banner_edit['busqueda'] ?? '';
    $fv['fecha_inicio'] = $banner_edit['fecha_inicio'] ?? '';
    $fv['fecha_fin']    = $banner_edit['fecha_fin'] ?? '';
    $fv['franjas']      = array_values(array_filter(explode(',', (string)($banner_edit['franjas'] ?? '24'))));
    $fv['rubros']       = array_values(array_filter(array_map('intval', explode(',', (string)($banner_edit['rubros'] ?? '')))));
    $fv['activo']       = (int)($banner_edit['activo'] ?? 1);
    $fv['orden']        = (int)($banner_edit['orden'] ?? 0);
}

/* 🔎 La columna `busqueda` se crea sola al abrir esta pestaña (idempotente).
   Si el hosting no dejara crearla, se avisa en pantalla en vez de fallar en silencio. */
$bn_busq_ok = banners_asegurar_busqueda();
?>
<style>
.bn-adm{font-size:13px}
.bn-adm h2{font-size:17px;margin:0 0 10px}
.bn-adm .sa-grid{grid-template-columns:repeat(auto-fit,minmax(130px,1fr))}
.bn-tool{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin:12px 0}
.bn-tool a{white-space:nowrap}
.bn-form{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:10px}
.bn-form .bn-full{grid-column:1/-1}
.bn-form label{display:block;font-size:11.5px;font-weight:700;color:var(--color-texto-claro);margin-bottom:3px}
.bn-form input[type=text],.bn-form input[type=date],.bn-form input[type=number]{width:100%;padding:8px 10px;border:1px solid var(--color-borde);border-radius:8px;font:inherit;font-size:13px;background:#fffdf8}
.bn-form input:focus{outline:none;border-color:var(--color-primario)}
.bn-checks{display:flex;gap:14px;flex-wrap:wrap;align-items:center}
.bn-checks label{font-weight:600;color:var(--color-texto);display:flex;align-items:center;gap:5px;font-size:13px;margin:0}
.bn-rubros{max-height:150px;overflow-y:auto;border:1px solid var(--color-borde);border-radius:10px;padding:8px 10px;background:#fff;display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:4px 12px}
.bn-rubros label{font-size:12.5px;font-weight:500;color:var(--color-texto);display:flex;gap:6px;align-items:center;margin:0}
.bn-prev{max-width:280px;border-radius:10px;border:1px solid var(--color-borde);display:block;margin-top:6px}
.bn-lista img{width:92px;height:52px;object-fit:cover;border-radius:8px;background:#f1e9da}
.bn-chip{display:inline-block;background:#f1f5f9;border-radius:999px;padding:1px 8px;font-size:11px;margin-right:4px}
.bn-chip--busq{background:#e0f2fe;color:#075985;font-weight:700}
.bn-busq-aviso{margin-top:4px;font-size:12px;line-height:1.45}
.bn-vigencia{white-space:nowrap}
</style>
<div class="bn-adm">
  <h2>📢 Publicidad — banners rotativos programados</h2>

  <!-- ===== Tarjetas de resumen ===== -->
  <div class="sa-grid" style="margin-bottom:12px">
    <div class="sa-stat"><div class="num"><?= $nActivos ?></div><div class="lbl">Banners activos ahora</div></div>
    <div class="sa-stat"><div class="num"><?= $nOcultos ?></div><div class="lbl">En pausa</div></div>
    <div class="sa-stat"><div class="num"><?= number_format($sumImp) ?></div><div class="lbl">Impresiones totales</div></div>
    <div class="sa-stat"><div class="num"><?= number_format($sumClic) ?></div><div class="lbl">Clics totales</div></div>
    <div class="sa-stat"><div class="num"><?= number_format($sumImpHoy) ?></div><div class="lbl">Impresiones hoy</div></div>
    <div class="sa-stat"><div class="num"><?= number_format($sumClicHoy) ?></div><div class="lbl">Clics hoy</div></div>
  </div>

  <?php if ($banners_stats_dias): ?>
    <div class="sa-card">
      <h3>📈 Últimos 7 días</h3>
      <div style="display:flex;gap:16px;flex-wrap:wrap">
        <?php foreach ($banners_stats_dias as $d): ?>
          <span style="font-size:12px">
            <b><?= date('d/m', strtotime($d['fecha'])) ?></b> ·
            👁 <?= number_format((int)$d['impresiones']) ?> ·
            🖱 <?= number_format((int)$d['clics']) ?>
          </span>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>

  <!-- ===== Filtros ===== -->
  <div class="bn-tool">
    <a class="btn-mini <?= (($_GET['f'] ?? '')===''?'b-ok':'b-ghost') ?>" href="<?= url('superadmin.php?seccion=banners') ?>">Todos</a>
    <a class="btn-mini <?= (($_GET['f'] ?? '')==='activos'?'b-ok':'b-ghost') ?>" href="<?= url('superadmin.php?seccion=banners&f=activos') ?>">Solo activos</a>
    <a class="btn-mini <?= (($_GET['f'] ?? '')==='ocultos'?'b-ok':'b-ghost') ?>" href="<?= url('superadmin.php?seccion=banners&f=ocultos') ?>">Solo en pausa</a>
    <a class="btn-mini b-ghost" href="<?= url('superadmin.php?seccion=banners') ?>">＋ Nuevo banner</a>
  </div>

  <!-- ===== Formulario crear / editar ===== -->
  <?php if (!$banner_edit): ?>
  <div class="sa-card">
    <h3>＋ Crear banner</h3>
  <?php else: ?>
  <div class="sa-card" style="border-color:#6d071a">
    <h3>✏️ Editando banner #<?= (int)$banner_edit['id'] ?> — <?= e($banner_edit['titulo']) ?>
      <a class="btn-mini b-ghost" style="margin-left:8px" href="<?= url('superadmin.php?seccion=banners') ?>">✕ cancelar</a>
    </h3>
  <?php endif; ?>
    <form method="post" class="bn-form">
      <?= csrf_campo() ?>
      <input type="hidden" name="accion" value="<?= $fv['id'] ? 'banner_editar' : 'banner_crear' ?>">
      <?php if ($fv['id']): ?><input type="hidden" name="id" value="<?= (int)$fv['id'] ?>"><?php endif; ?>

      <div>
        <label>Título *</label>
        <input type="text" name="titulo" required maxlength="190" placeholder="¿Se te acabó el gas?" value="<?= e($fv['titulo']) ?>">
      </div>
      <div>
        <label>Tema (necesidad → negocios del modal)</label>
        <input type="text" name="tema" list="bn_temas" placeholder="gas, farmacias, mecanicos…" value="<?= e($fv['tema']) ?>">
        <datalist id="bn_temas">
          <?php foreach (array_keys($banners_temas_opts) as $tk): ?><option value="<?= e($tk) ?>"><?php endforeach; ?>
        </datalist>
        <div class="mini">Vacío = el banner solo enlaza (enlace externo).</div>
      </div>
      <div>
        <label>Enlace externo (opcional)</label>
        <input type="text" name="enlace" placeholder="https://…" value="<?= e($fv['enlace']) ?>">
        <div class="mini">Si se llena, el clic abre este enlace (sin modal).</div>
      </div>

      <?php /* ===== 🔎 BÚSQUEDA DEL SITIO (2026-09-11, pedido del jefe) =====
         El banner lleva a los RESULTADOS DE BÚSQUEDA del sitio con el término que se
         escriba aquí (ej. «Desayunos» → dechimbote.com/buscar.php?q=Desayunos).
         Predictivo (Regla de Oro n.º 2): mientras escribe se le sugieren términos que YA
         existen en el sitio y, en vivo, se comprueba si ese término tiene resultados. */ ?>
      <div class="bn-full">
        <label>🔎 Búsqueda del sitio — el clic lleva a los resultados de este término</label>
        <?php if (!$bn_busq_ok): ?>
          <div class="mini" style="color:#b45309;font-weight:700">
            ⚠️ No se pudo preparar la columna de búsqueda en la base de datos: este campo no se guardará.
            Avisa para revisarlo.
          </div>
        <?php endif; ?>
        <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
          <div style="flex:1;min-width:230px">
            <input type="text" name="busqueda" id="bnBusqueda" maxlength="190" data-terminos="1"
                   autocomplete="off" placeholder="Escribe y te sugiero términos que ya existen (Desayunos, Polos, Perros…)"
                   value="<?= e($fv['busqueda']) ?>">
          </div>
          <a class="btn-mini b-ghost" id="bnBusqProbar" target="_blank" rel="noopener"
             style="<?= $fv['busqueda'] !== '' ? '' : 'display:none' ?>"
             href="<?= e(banners_busqueda_url($fv['busqueda']) ?: '#') ?>">👁 Probar esta búsqueda</a>
        </div>
        <div class="mini" id="bnBusqInfo">
          <?= $fv['busqueda'] !== ''
              ? 'Al hacer clic, el visitante verá los resultados de «<b>' . e($fv['busqueda']) . '</b>» en buscar.php.'
              : 'Vacío = el clic no busca (usa el tema del modal o el enlace externo).' ?>
        </div>
        <div class="mini">
          Si escribes un término, <b>manda sobre el enlace externo y sobre el tema</b>: el banner lleva a
          <code>buscar.php?q=…</code> en la misma pestaña. Al escribir se comprueba en vivo si ese término
          tiene resultados en el sitio (así no mandas al visitante a una página vacía).
        </div>
      </div>

      <div class="bn-full">
        <label>Imagen del banner * (todos los banners usan el mismo tamaño)</label>
        <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
          <input type="file" id="bnArchivo" accept="image/jpeg,image/png,image/webp" style="flex:1;min-width:200px">
          <span class="mini" id="bnEstadoImg"></span>
        </div>
        <input type="hidden" name="imagen" id="bnImagenRel" value="<?= e($fv['imagen']) ?>">
        <?php if ($fv['imagen']): ?>
          <img class="bn-prev" id="bnPreview" src="<?= e(img_url($fv['imagen'], IMG_ANCHO_CHICO)) ?>" alt="preview">
        <?php else: ?>
          <img class="bn-prev" id="bnPreview" alt="preview" style="display:none">
        <?php endif; ?>
      </div>

      <div>
        <label>Vigencia — inicia</label>
        <input type="date" name="fecha_inicio" value="<?= e($fv['fecha_inicio']) ?>">
      </div>
      <div>
        <label>Vigencia — termina (vacío = infinito)</label>
        <input type="date" name="fecha_fin" value="<?= e($fv['fecha_fin']) ?>">
      </div>
      <div>
        <label>Franjas horarias</label>
        <div class="bn-checks">
          <?php foreach (banners_franjas_def() as $f): ?>
            <label><input type="checkbox" name="franjas[]" value="<?= $f['k'] ?>" <?= in_array($f['k'], $fv['franjas'], true) || $fv['franjas'] === ['24'] ? 'checked' : '' ?>> <?= $f['icono'] ?> <?= $f['label'] ?></label>
          <?php endforeach; ?>
          <button type="button" class="btn-mini b-ghost" id="bnTodoDia">Todo el día (24 h)</button>
        </div>
        <div class="mini">Fuera de su franja el banner no se muestra.</div>
      </div>
      <div>
        <label>Orden (menor = primero)</label>
        <input type="number" name="orden" min="0" value="<?= (int)$fv['orden'] ?>">
      </div>
      <div>
        <label>Estado</label>
        <div class="bn-checks">
          <label><input type="checkbox" name="activo" value="1" <?= $fv['activo'] ? 'checked' : '' ?>> Publicado (rota)</label>
        </div>
        <div class="mini">Al pasar la fecha fin se desactiva solo.</div>
      </div>

      <div class="bn-full">
        <label>¿En qué rubros puede publicarse? <span class="mini">(ninguno marcado = libre en TODO el sitio)</span></label>
        <div style="margin-bottom:6px">
          <button type="button" class="btn-mini b-ghost" id="bnRubrosTodo">Marcar todos</button>
          <button type="button" class="btn-mini b-ghost" id="bnRubrosNada">Ninguno (libre)</button>
        </div>
        <div class="bn-rubros">
          <?php foreach ($banners_categorias as $c): ?>
            <label><input type="checkbox" name="rubros[]" value="<?= (int)$c['id'] ?>" <?= in_array((int)$c['id'], $fv['rubros'], true) ? 'checked' : '' ?>> <?= e($c['icono'] ?? '') ?> <?= e($c['nombre']) ?></label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="bn-full">
        <button class="btn-mini b-ok" style="padding:10px 22px;font-size:14px"><?= $fv['id'] ? '💾 Guardar cambios' : '📢 Crear banner' ?></button>
      </div>
    </form>
  </div>

  <!-- ===== Listado ===== -->
  <?php if (!$banners_admin): ?>
    <div class="empty">Todavía no hay banners. Crea el primero arriba 👆</div>
  <?php else: ?>
  <div class="sa-table-wrap"><table class="sa-table">
    <thead><tr><th>Banner</th><th>Tema</th><th>Franjas</th><th>Vigencia</th><th>Rubros</th><th>👁/🖱</th><th>Estado</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($banners_admin as $b): ?>
      <?php
        $imp   = (int)($b['impresiones'] ?? 0);
        $clic  = (int)($b['clics'] ?? 0);
        $rub   = array_filter(array_map('intval', explode(',', (string)($b['rubros'] ?? ''))));
        $nombresRubros = [];
        foreach ($rub as $rid) { if (isset($rubrosNombre[$rid])) $nombresRubros[] = $rubrosNombre[$rid]; }
      ?>
      <tr>
        <td>
          <div style="display:flex;gap:8px;align-items:center">
            <?php // 🖼️ La miniatura del listado (≈90 px) va por el MOTOR: baja la versión de 160 px, no el banner entero. ?>
            <?php if (!empty($b['imagen'])): ?><?= img_tag($b['imagen'], $b['titulo'] ?? '', ['sizes' => '90px']) ?><?php endif; ?>
            <div><b><?= e($b['titulo']) ?></b><div class="mini"><?= $b['texto'] ? e($b['texto']) : '' ?></div></div>
          </div>
        </td>
        <td><?= $b['tema'] ? '<span class="bn-chip">' . e($b['tema']) . '</span>' : '<span class="mini">—</span>' ?>
          <?php if (trim((string)($b['busqueda'] ?? '')) !== ''): ?>
            <div><span class="bn-chip bn-chip--busq">🔎 <?= e($b['busqueda']) ?></span></div>
          <?php endif; ?>
        </td>
        <td class="mini"><?= e(banners_franjas_etiquetas($b['franjas'] ?? '')) ?></td>
        <td class="mini bn-vigencia">
          <?= $b['fecha_inicio'] ? date('d/m/y', strtotime($b['fecha_inicio'])) : '—' ?>
          →
          <?= $b['fecha_fin'] ? date('d/m/y', strtotime($b['fecha_fin'])) : '<b>∞ infinito</b>' ?>
        </td>
        <td class="mini">
          <?php if (!$nombresRubros): ?>Libre (todo)<?php else: ?>
            <?= count($nombresRubros) ?> rubro(s)<div class="mini" title="<?= e(implode(', ', $nombresRubros)) ?>"><?= e(implode(', ', array_slice($nombresRubros, 0, 2))) ?><?= count($nombresRubros) > 2 ? '…' : '' ?></div>
          <?php endif; ?>
        </td>
        <td class="mini">👁 <?= number_format($imp) ?><br>🖱 <?= number_format($clic) ?></td>
        <td><span class="badge-est <?= $b['activo'] ? 'b-aprobado' : 'b-rechazado' ?>"><?= $b['activo'] ? 'activo' : 'pausa' ?></span></td>
        <td>
          <div style="display:flex;gap:4px;flex-wrap:wrap">
            <a class="btn-mini b-ghost" href="<?= url('superadmin.php?seccion=banners&editar=' . (int)$b['id'] . '&f=' . urlencode($_GET['f'] ?? '')) ?>">✏️</a>
            <form method="post" style="display:inline">
              <?= csrf_campo() ?><input type="hidden" name="accion" value="banner_toggle"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
              <button class="btn-mini <?= $b['activo'] ? 'b-no' : 'b-ok' ?>" title="<?= $b['activo'] ? 'Ocultar' : 'Activar' ?>"><?= $b['activo'] ? '⏸' : '▶' ?></button>
            </form>
            <form method="post" style="display:inline">
              <?= csrf_campo() ?><input type="hidden" name="accion" value="banner_eliminar"><input type="hidden" name="id" value="<?= (int)$b['id'] ?>">
              <button class="btn-mini b-no" onclick="return confirm('⚠️ ¿Eliminar este banner y sus estadísticas?')">🗑</button>
            </form>
          </div>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>

  <div class="sa-card" style="margin-top:14px">
    <h3>💡 Cómo funciona</h3>
    <p style="font-size:13px;color:var(--color-texto-claro);margin:6px 0 0;line-height:1.7">
      · Cada carga de página elige banners <b>aleatorios</b> entre los <b>elegibles ahora</b> (activo + dentro de su vigencia + dentro de su franja horaria).<br>
      · Un banner <b>nunca se repite</b> dentro de la misma página.<br>
      · Al cumplirse su <b>fecha fin</b> se desactiva automáticamente (deja de rotar sin intervención).<br>
      · Al hacer clic en un banner de necesidad se abre un <b>modal con los negocios que la resuelven</b>, filtrables por distrito.<br>
      · Si el banner tiene un <b>🔎 término de búsqueda</b>, el clic NO abre el modal: lleva a los <b>resultados de búsqueda</b> de ese término (<code>buscar.php?q=…</code>, misma pestaña) y <b>manda</b> sobre el enlace externo y sobre el tema.<br>
      · Los banners con <b>rubros</b> marcados solo salen en páginas de esas categorías; sin rubros salen en todo el sitio.
    </p>
  </div>
</div>

<!-- Compresión en el navegador (motor compartido): la imagen del banner se
     optimiza antes de subirla. assets/js/imagen_optimizar.js -->
<script src="<?= url('assets/js/imagen_optimizar.js') ?>?v=1"></script>
<?php // 🔎 Términos predictivos para el campo de búsqueda del banner: reusa el motor del
      // alta de productos (assets/js/terminos_sugerir.js → api/terminos.php), que sugiere
      // términos que YA existen en el sitio. Se activa con data-terminos="1". ?>
<script src="<?= url('assets/js/terminos_sugerir.js') ?>?v=1"></script>
<script>
(function () {
  var CSRF = window.CSRF_TOKEN || '';
  var archivo = document.getElementById('bnArchivo');
  var estadoImg = document.getElementById('bnEstadoImg');

  /* Subir imagen del banner — se OPTIMIZA en el navegador antes de subir
     (WebP, máx 1600 px) para no mandar PNG de varios MB al hosting. */
  if (archivo) {
    archivo.addEventListener('change', function () {
      if (!archivo.files || !archivo.files[0]) return;
      var original = archivo.files[0];
      if (estadoImg) { estadoImg.textContent = 'Optimizando…'; estadoImg.style.color = ''; }

      var tarea = (window.CZImg) ? CZImg.optimizar(original) : Promise.resolve(original);
      tarea.then(function (listo) {
        var fd = new FormData();
        fd.append('foto', listo);
        fd.append('_csrf', CSRF);
        if (estadoImg) estadoImg.textContent = 'Subiendo…';
        return fetch(window.SITE_URL + '/api/subir_banner.php', { method: 'POST', body: fd, credentials: 'same-origin' })
          .then(function (r) { return r.json(); })
          .then(function (j) {
            if (!j.ok) { if (estadoImg) { estadoImg.textContent = '❌ ' + (j.error || 'Error'); estadoImg.style.color = '#dc2626'; } return; }
            document.getElementById('bnImagenRel').value = j.rel;
            var p = document.getElementById('bnPreview');
            p.src = j.url; p.style.display = 'block';
            if (estadoImg) { estadoImg.textContent = '✅ ' + j.name; estadoImg.style.color = '#15803d'; }
          });
      }).catch(function () { if (estadoImg) estadoImg.textContent = '❌ Error de red.'; });
    });
  }

  /* Todo el día: marca las 3 franjas */
  var todoDia = document.getElementById('bnTodoDia');
  if (todoDia) {
    todoDia.addEventListener('click', function () {
      document.querySelectorAll('input[name="franjas[]"]').forEach(function (c) { c.checked = true; });
    });
  }

  /* Rubros: marcar todos / ninguno */
  var rbTodo = document.getElementById('bnRubrosTodo');
  var rbNada = document.getElementById('bnRubrosNada');
  var rubChecks = document.querySelectorAll('.bn-rubros input[type=checkbox]');
  if (rbTodo) rbTodo.addEventListener('click', function () { rubChecks.forEach(function (c) { c.checked = true; }); });
  if (rbNada) rbNada.addEventListener('click', function () { rubChecks.forEach(function (c) { c.checked = false; }); });

  /* ===== 🔎 BÚSQUEDA DEL SITIO (2026-09-11, pedido del jefe) =====
     Mientras escribe el término se comprueba EN VIVO contra el propio buscador del sitio
     (api/sugerir.php, el mismo que usa la barra de arriba): así el jefe no guarda un
     término que dejaría al visitante en una página de "sin resultados".
     Regla de Oro n.º 2: predictivo, sin listas largas y con confirmación inmediata. */
  var busq   = document.getElementById('bnBusqueda');
  var busqIn = document.getElementById('bnBusqInfo');
  var probar = document.getElementById('bnBusqProbar');
  var BASE   = window.SITE_URL || '';
  var tBusq  = null, ultBusq = '';

  function busqEsc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  function busqPinta(html, color, esAviso) {
    if (!busqIn) return;
    busqIn.innerHTML = html;
    busqIn.style.color = color || '';
    busqIn.className = esAviso ? 'mini bn-busq-aviso' : 'mini';
  }

  function busqRevisar() {
    if (!busq) return;
    var q = busq.value.trim().replace(/\s+/g, ' ');
    ultBusq = q;

    if (probar) {
      if (q) { probar.href = BASE + '/buscar.php?q=' + encodeURIComponent(q); probar.style.display = ''; }
      else   { probar.style.display = 'none'; }
    }
    if (q.length < 2) {
      busqPinta('Vacío = el clic no busca (usa el tema del modal o el enlace externo).');
      return;
    }

    busqPinta('🔎 Comprobando «<b>' + busqEsc(q) + '</b>» en el buscador del sitio…');
    /* Se pregunta al servidor con la MISMA condición de buscar.php (nombre o descripción),
       no con las sugerencias del buscador fuzzy: así el dato es el que verá el visitante. */
    fetch(BASE + '/api/banners.php?action=resultados&q=' + encodeURIComponent(q), { credentials: 'same-origin' })
      .then(function (r) { return r.json(); })
      .then(function (j) {
        if (ultBusq !== q) return;                       // llegó tarde: ya escribió otra cosa
        var total = (j && j.total) ? j.total : 0;
        var ejs   = (j && j.ejemplos) ? j.ejemplos : [];
        if (!j || !j.ok) {
          busqPinta('No pudimos comprobar el término ahora mismo (igual puedes guardar el banner).', '#b45309', true);
          return;
        }
        if (!total) {
          busqPinta('⚠️ Con «<b>' + busqEsc(q) + '</b>» el visitante vería una página <b>sin resultados</b>: '
                  + 'ningún negocio tiene esa palabra en su nombre ni en su descripción. Prueba con otra palabra '
                  + '(la que use la tienda que quieres mostrar).', '#b45309', true);
          return;
        }
        busqPinta('✅ Al hacer clic, el visitante verá <b>' + total + '</b> negocio(s) para «<b>' + busqEsc(q) + '</b>»'
                + (ejs.length ? ' — ' + busqEsc(ejs.join(' · ')) + (total > ejs.length ? '…' : '') : '') + '.', '#15803d');
      })
      .catch(function () {
        if (ultBusq !== q) return;
        busqPinta('No pudimos comprobar el término ahora mismo (igual puedes guardar el banner).', '#b45309', true);
      });
  }

  if (busq) {
    busq.addEventListener('input', function () {
      if (tBusq) clearTimeout(tBusq);
      tBusq = setTimeout(busqRevisar, 320);
    });
    busq.addEventListener('change', busqRevisar);
    if (busq.value.trim()) busqRevisar();   // al abrir un banner ya guardado, se comprueba solo
  }
})();
</script>
