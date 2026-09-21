<?php
/**
 * trabaja_con_nosotros.php — "Trabaja con nosotros"
 * Reclutamiento: buscamos personas que quieran generar ingresos visitando negocios
 * y creadores de contenido con cuentas llamativas en redes sociales.
 * Formulario con 4 niveles de sueldo y preguntas. Guarda en directorio_postulantes.
 */
require_once __DIR__ . '/config.php';
iniciar_sesion();

$enviado_ok = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $d = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
        'redes' => trim($_POST['redes'] ?? ''),
        'info' => trim($_POST['info'] ?? ''),
        'sueldo' => trim($_POST['sueldo'] ?? ''),
        'jornada' => trim($_POST['jornada'] ?? ''),
        'movilidad' => trim($_POST['movilidad'] ?? ''),
        'idioma' => trim($_POST['idioma'] ?? ''),
        'formacion' => trim($_POST['formacion'] ?? ''),
    ];
    $errores = [];
    if (mb_strlen($d['nombre']) < 2) $errores[] = 'Escribe tu nombre real.';
    if (empty($d['email']) || !filter_var($d['email'], FILTER_VALIDATE_EMAIL)) $errores[] = 'Escribe un email válido.';
    $sueldos_validos = ['S/ 1,300','S/ 1,700','S/ 2,000','S/ 2,400'];
    if (!in_array($d['sueldo'], $sueldos_validos)) $errores[] = 'Elige uno de los 4 niveles de sueldo.';
    if (!in_array($d['jornada'], ['Día','Noche','Día y noche','Flexible'])) $errores[] = 'Indica tu disponibilidad de jornada.';

    if (empty($errores)) {
        crear_postulante($d);
        $enviado_ok = true;
    } else {
        foreach ($errores as $err) flash($err, 'error');
    }
}

$categorias = obtener_categorias();
$titulo_pagina = 'Trabaja con nosotros';
include __DIR__ . '/includes/header.php';
?>

<style>
.tcw-wrap { max-width: 560px; margin: 0 auto; }
.tcw-hero {
    background: linear-gradient(135deg,#075e54,#128c7e); color:#fff; border-radius:16px;
    padding:24px 20px; margin-bottom:18px; text-align:center;
}
.tcw-hero h1 { font-size:24px; font-weight:800; margin-bottom:6px; }
.tcw-hero p { font-size:14px; opacity:.95; }
.tcw-hero .sueldos { margin-top:14px; display:flex; justify-content:center; gap:8px; flex-wrap:wrap; }
.tcw-hero .sueldos span {
    background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.4);
    padding:6px 10px; border-radius:8px; font-weight:700; font-size:13px;
}
.tcw-card { background:#fff; border-radius:14px; padding:22px; box-shadow:var(--sombra-tarjeta); border:1px solid var(--color-borde); }
.tcw-card h2 { font-size:17px; font-weight:700; margin-bottom:12px; }
.form-tcw .form__group { margin-bottom:14px; }
.form-tcw label.form__label { font-size:13px; font-weight:600; }
.form-tcw .hint { font-size:11px; color:var(--color-texto-claro); }
.opciones-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:4px; }
.opcion {
    display:flex; align-items:center; gap:8px; border:1.5px solid var(--color-borde);
    border-radius:10px; padding:12px 10px; cursor:pointer; font-size:14px;
    transition:all .1s; background:#fff;
}
.opcion:active { border-color:var(--color-acento); background:#eff6ff; }
.opcion input { accent-color: var(--color-acento); }
.opcion input[type=radio]{ width:16px;height:16px; }
.opcion.sueldo { flex-direction:column; text-align:center; }
.opcion.sueldo b { font-size:17px; }
.opcion.sueldo small { font-size:11px; color:var(--color-texto-claro); }
.tcw-exito { text-align:center; padding:20px 10px; }
.tcw-exito .ic { font-size:44px; }
.btn-prim { display:flex; align-items:center; justify-content:center; width:100%;
  padding:15px; font-size:16px; font-weight:700; border:0; border-radius:12px;
  background:var(--color-acento); color:#fff; cursor:pointer; }
@media (max-width:480px){ .opciones-grid{ grid-template-columns:1fr 1fr; } }
</style>

<div class="tcw-wrap">
  <div class="tcw-hero">
    <h1>💼 Trabaja con nosotros</h1>
    <p>Buscamos personas con energía y ganas de <b>generar ingresos</b> visitando negocios de Chimbote y el Santa. ¡También son bienvenidos los creadores de contenido con cuentas llamativas en redes sociales!</p>
    <div class="sueldos"><span>S/1,300</span><span>S/1,700</span><span>S/2,000</span><span>S/2,400</span></div>
    <p style="font-size:11px;margin-top:8px;opacity:.85">Trabajo de 8 horas diarias · Lunes a Viernes</p>
  </div>

  <?php if ($enviado_ok): ?>
    <div class="tcw-card tcw-exito">
      <div class="ic">🎉</div>
      <h2 style="margin-bottom:8px">¡Gracias por postular!</h2>
      <p style="color:var(--color-texto-claro);font-size:14px">Recibimos tu información. Nuestro equipo de reclutamiento revisará tu perfil y te contactaremos pronto.</p>
      <a href="<?= url('') ?>" class="btn" style="margin-top:14px">Volver al inicio</a>
    </div>
    <?php include __DIR__ . '/includes/footer.php'; return; ?>
  <?php endif; ?>

  <div class="tcw-card">
    <h2>📋 Completa tu postulación</h2>
    <form method="post" action="<?= url('trabaja_con_nosotros.php') ?>" class="form-tcw">
      <?= csrf_campo() ?>
      <div class="form__group">
        <label class="form__label">Nombre real *</label>
        <input type="text" name="nombre" class="form__input" required value="<?= e($_POST['nombre'] ?? '') ?>" placeholder="Tu nombre completo">
      </div>
      <div class="form__group">
        <label class="form__label">Email *</label>
        <input type="email" name="email" class="form__input" required value="<?= e($_POST['email'] ?? '') ?>">
      </div>
      <div class="form__group">
        <label class="form__label">Teléfono / WhatsApp</label>
        <input type="text" name="telefono" class="form__input" value="<?= e($_POST['telefono'] ?? '') ?>">
      </div>
      <div class="form__group">
        <label class="form__label">Redes sociales</label>
        <input type="text" name="redes" class="form__input" value="<?= e($_POST['redes'] ?? '') ?>" placeholder="Ej. @tuinstagram, TikTok…">
        <div class="hint">Si eres creador de contenido, indícalo aquí.</div>
      </div>
      <div class="form__group">
        <label class="form__label">Cuéntanos qué puedes ofrecer</label>
        <textarea name="info" class="form__textarea" rows="3" placeholder="Experiencia, habilidades, lo que desees compartir…"><?= e($_POST['info'] ?? '') ?></textarea>
      </div>

      <div class="form__group">
        <label class="form__label">Nivel de sueldo esperado * <span class="hint">(8h · Lun–Vie)</span></label>
        <div class="opciones-grid">
          <?php
          $sueldos = [['v'=>'S/ 1,300','d'=>'base'],['v'=>'S/ 1,700','d'=>''],['v'=>'S/ 2,000','d'=>''],['v'=>'S/ 2,400','d'=>'meta']];
          foreach ($sueldos as $i=>$s): ?>
          <label class="opcion sueldo">
            <input type="radio" name="sueldo" value="<?= e($s['v']) ?>" required <?= (($_POST['sueldo'] ?? '')===$s['v'])?'checked':'' ?>>
            <b><?= e($s['v']) ?></b>
            <small><?= $s['d']==='base'?'esperado':($s['d']==='meta'?'objetivo':'') ?></small>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form__group">
        <label class="form__label">¿Disponibilidad de jornada? *</label>
        <div class="opciones-grid">
          <?php foreach (['Día','Noche','Día y noche','Flexible'] as $j): ?>
          <label class="opcion">
            <input type="radio" name="jornada" value="<?= e($j) ?>" required <?= (($_POST['jornada'] ?? '')===$j)?'checked':'' ?>>
            <span><?= $j==='Día'?'🌞':'🌙' ?></span> <?= e($j) ?>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form__group">
        <label class="form__label">¿Cuentas con movilidad propia? *</label>
        <div class="opciones-grid">
          <?php foreach ([['si','🛵 Sí, tengo movilidad'],['no','🚶 No, no tengo']] as $m): ?>
          <label class="opcion">
            <input type="radio" name="movilidad" value="<?= e($m[0]) ?>" required <?= (($_POST['movilidad'] ?? '')===$m[0])?'checked':'' ?>>
            <span><?= $m[1] ?></span>
          </label>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="form__group">
        <label class="form__label">¿Hablas algún segundo idioma además del español?</label>
        <input type="text" name="idioma" class="form__input" value="<?= e($_POST['idioma'] ?? '') ?>" placeholder="Ej. inglés, quechua… (o 'no')">
      </div>

      <div class="form__group">
        <label class="form__label">¿Cuál es tu nivel de formación / estudios?</label>
        <select name="formacion" class="form__select">
          <option value="">Selecciona…</option>
          <?php foreach (['Secundaria','Técnico','Universitario (en curso)','Universitario (completo)','Estudios de posgrado','Otro'] as $f): ?>
          <option value="<?= e($f) ?>" <?= (($_POST['formacion'] ?? '')===$f)?'selected':'' ?>><?= e($f) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <button type="submit" class="btn-prim">🚀 Enviar mi postulación</button>
    </form>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
