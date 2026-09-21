<?php
/**
 * includes/invitacion_ficha.php — 📨 INVITAR AL NEGOCIO DESDE SU PROPIA FICHA
 * ============================================================================
 * Pedido del jefe (2026-09-17, textual): *«cuando estoy logueado como súper administrador
 * (jimmylopez…) ahí debe aparecer un botón de enviar invitación… así le mandaré un WhatsApp»*.
 *
 * Es el **MISMO motor** de la pestaña 🏬 Tiendas del Súper Admin
 * (`includes/invitaciones.php` · guía **`GUIA_INVITACIONES_A_NEGOCIOS.md`**), pero pintado en la
 * **FICHA del negocio** (`negocio.php`): así el jefe invita a la tienda que está mirando, sin
 * tener que buscarla en el panel.
 *
 * 🔒 **SOLO LO VE EL SÚPER ADMIN.** Si el que mira la ficha no es admin, `invitacion_ficha_html()`
 *    devuelve **una cadena vacía** (no se pinta ni un byte) y el POST se rechaza con **403**. El
 *    público —dueños y visitantes— sigue viendo la ficha exactamente como siempre.
 *
 * Qué se pinta (debajo de los botones de WhatsApp/Llamar de la tienda):
 *   · el **botón 📨 Invitar** con los 4 colores de siempre según cuántas veces se le mandó
 *     (⚫ 0 · 🟠 1 · 🟢 2 · 🔵 3 o más), con el contador en el globito;
 *   · el **↺** para corregir un clic de más;
 *   · una leyenda corta con lo que significa cada color;
 *   · el **formulario oculto** y el **JavaScript** que hace el `fetch` (idéntico al del panel:
 *     el enlace se abre **dentro del gesto** del clic la primera vez, cuando hay que crear la
 *     cuenta de la tienda).
 *
 * El mensaje que se abre es el mismo de siempre (la invitación + **usuario y contraseña**), o sea
 * que un clic desde la ficha vale exactamente lo mismo que un clic desde el panel: queda apuntado
 * en `directorio_invitaciones` con el id del admin que lo mandó.
 *
 * ⚠️ **Sin JavaScript no se pierde nada:** si el navegador no corre el `fetch`, el POST normal
 *    responde redirigiendo a WhatsApp y la invitación queda apuntada igual
 *    (`invitacion_ficha_accion()` lo resuelve).
 * ============================================================================
 */

require_once __DIR__ . '/invitaciones.php';   // el motor (invitacion_enviar / _deshacer / _url…)

/**
 * ¿Quién puede ver y tocar el botón? SOLO el súper administrador.
 * (Se guarda en memoria: `es_admin()` lee la sesión y en una misma petición no cambia.)
 */
function invitacion_ficha_puede(): bool {
    static $puede = null;
    if ($puede === null) {
        try { $puede = (function_exists('es_admin') && es_admin()); }
        catch (Throwable $e) { $puede = false; }
    }
    return $puede;
}

/**
 * Atiende el POST del botón 📨 de la ficha (y del ↺). **Termina la petición** si es suyo.
 *
 * Se llama al principio de `negocio.php`, antes de pintar nada: así el `fetch` recibe su JSON y
 * no se manda al visitante media ficha por delante.
 *
 * @return bool true si la petición era del botón (y ya se respondió); false si hay que seguir
 *              pintando la ficha normalmente.
 */
function invitacion_ficha_accion(array $negocio): bool {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') return false;
    $accion = (string)($_POST['accion'] ?? '');
    if ($accion !== 'tienda_invitar' && $accion !== 'tienda_invitar_deshacer') return false;

    $id      = (int)($negocio['id'] ?? 0);
    $es_ajax = invitacion_es_ajax();

    // 🔒 Puerta cerrada: esto es una herramienta del Súper Admin, no del público.
    if (!invitacion_ficha_puede() || $id <= 0) {
        http_response_code(403);
        if ($es_ajax) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'n' => 0, 'url' => '',
                              'msg' => 'Solo el súper administrador puede invitar tiendas.'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Solo el súper administrador puede invitar tiendas.';
        exit;
    }

    csrf_verificar();   // el formulario oculto lleva `csrf_campo()`

    $u = usuario_actual();
    $r = ($accion === 'tienda_invitar')
        ? invitacion_enviar($id, (int)($u['id'] ?? 0))
        : invitacion_deshacer($id);

    if ($es_ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($r, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Sin JavaScript: se manda al WhatsApp con el mensaje ya escrito (o se dice qué pasó).
    if (!empty($r['url'])) {
        header('Location: ' . $r['url']);
        exit;
    }
    header('Content-Type: text/plain; charset=utf-8');
    echo (string)($r['msg'] ?? 'Listo.');
    exit;
}

/**
 * El bloque de la invitación que se pinta en la ficha ('' si el que mira no es admin).
 *
 * @param array $t la fila del negocio (necesita id, nombre, slug, whatsapp, telefono, dueno_id)
 */
function invitacion_ficha_html(array $t): string {
    if (!invitacion_ficha_puede()) return '';
    $id = (int)($t['id'] ?? 0);
    if ($id <= 0) return '';

    $mapa = invitaciones_mapa([$id]);
    $n    = (int)($mapa[$id]['n'] ?? 0);
    $ults = (string)($mapa[$id]['ultima'] ?? '');

    $claves = invitaciones_claves_mapa([$id]);
    $cred   = $claves[$id] ?? ['usuario' => '', 'clave' => ''];
    $tel    = invitacion_contacto($t);

    // El `title` cuenta lo mismo que el del panel: cuántas veces, cuándo y con qué credenciales.
    $como = invitacion_etiqueta($n);
    if ($ults !== '') {
        $ts = strtotime($ults);
        // ⚠️ «a las» va ESCAPADO: en `date()` la `a` es am/pm, la `l` el día y la `s` los segundos
        // (sin las barras salía «17/09/2026 am Thursdayam26 09:07»).
        if ($ts) $como .= ' · último envío el ' . date('d/m/Y \a \l\a\s H:i', $ts);
    }
    $tiene_cred = (!empty($cred['usuario']) && !empty($cred['clave']));
    if ($tiene_cred) $como .= ' · usuario ' . (string)$cred['usuario'] . ' · contraseña ' . (string)$cred['clave'];

    // Si ya hay credenciales guardadas, el enlace viene armado desde el servidor (clic instantáneo).
    $listo = ($tiene_cred && $tel !== '');
    $href  = $listo ? invitacion_url($t, $cred) : '#';
    $nivel = invitacion_nivel($n);

    ob_start(); ?>
<style>
/* 📨 INVITACIÓN EN LA FICHA (2026-09-17) — SOLO la ve el súper administrador.
   Mismos 4 colores que el botón del panel (⚫🟠🟢🔵): si se cambian, hay que cambiarlos
   también en `includes/vista_tiendas_admin.php` (guía GUIA_INVITACIONES_A_NEGOCIOS.md §3). */
.invf{margin:12px 0 4px;padding:12px 14px;background:#fff;border:1px dashed #94a3b8;border-radius:12px}
.invf__tit{font-size:14px;font-weight:800;color:#0f172a;margin-bottom:8px}
.invf__tit span{font-weight:600;color:#64748b}
.invf__fila{display:flex;align-items:center;gap:8px}
.invf__btn{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:46px;
  padding:10px 14px;border-radius:10px;color:#fff;font-size:16px;font-weight:800;text-decoration:none;
  box-shadow:0 2px 6px rgba(15,23,42,.18)}
.invf__btn:hover{filter:brightness(1.12);color:#fff}
.invf__btn b{font-weight:800;background:rgba(255,255,255,.24);border-radius:999px;padding:2px 9px;font-size:13px}
.invf__btn--n0{background:#111827}
.invf__btn--n1{background:#ea580c}
.invf__btn--n2{background:#15803d}
.invf__btn--n3{background:#1d4ed8}
.invf__sin{flex:1;display:inline-flex;align-items:center;justify-content:center;gap:8px;min-height:46px;
  padding:10px 14px;border-radius:10px;background:#e5e7eb;color:#b91c1c;font-size:15px;font-weight:700}
.invf__undo{flex:0 0 auto;width:46px;min-height:46px;border:1px solid #cbd5e1;background:#f8fafc;color:#475569;
  border-radius:10px;font-size:18px;cursor:pointer}
.invf__undo:hover:not(:disabled){border-color:#2563eb;color:#2563eb}
.invf__undo:disabled{opacity:.35;cursor:default}
.invf__leyenda{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin-top:8px;font-size:12.5px;color:#475569}
.invf__leyenda i{display:inline-block;width:11px;height:11px;border-radius:999px;margin-right:4px;vertical-align:-1px}
.invf__leyenda .p0{background:#111827}
.invf__leyenda .p1{background:#ea580c}
.invf__leyenda .p2{background:#15803d}
.invf__leyenda .p3{background:#1d4ed8}
</style>

<div class="invf" id="invf-caja">
    <div class="invf__tit">🛡️ Súper Admin · Invitación por WhatsApp <span>(lo ves solo tú)</span></div>
    <div class="invf__fila">
        <?php if ($tel === ''): ?>
            <span class="invf__sin" id="invf-btn" title="Esta tienda no tiene WhatsApp ni teléfono: no se puede invitar">📨 Sin número</span>
        <?php else: ?>
            <a class="invf__btn invf__btn--n<?= (int)$nivel ?>" id="invf-btn" data-inv-id="<?= (int)$id ?>"
               data-listo="<?= $listo ? '1' : '0' ?>"<?= $tiene_cred ? ' data-creds="usuario ' . e((string)$cred['usuario']) . ' · contraseña ' . e((string)$cred['clave']) . '"' : '' ?>
               href="<?= e($href) ?>"<?= $listo ? ' target="_blank" rel="noopener"' : '' ?>
               title="Invitación por WhatsApp con el usuario y la contraseña · <?= e($como) ?>"
               onclick="return invFichaInvitar(this)">📨 Enviar invitación <b class="invf__n"><?= (int)$n ?></b></a>
            <button type="button" class="invf__undo" id="invf-undo" data-inv-id="<?= (int)$id ?>"
                    title="Corregir: quitar UNA invitación del conteo"<?= $n < 1 ? ' disabled' : '' ?>
                    onclick="return invFichaDeshacer(this)">↺</button>
        <?php endif; ?>
    </div>
    <div class="invf__leyenda">
        <span><i class="p0"></i>sin enviar</span>
        <span><i class="p1"></i>1 vez</span>
        <span><i class="p2"></i>2 veces</span>
        <span><i class="p3"></i>3 veces o más</span>
        <span style="margin-left:auto">Un clic abre WhatsApp con el mensaje escrito y queda apuntado · <b>↺</b> corrige si te equivocas</span>
    </div>
</div>

<form method="post" id="invf-form" style="display:none">
    <?= csrf_campo() ?>
    <input type="hidden" name="accion" value="tienda_invitar">
    <input type="hidden" name="id" value="<?= (int)$id ?>">
</form>

<script>
/* 📨 INVITACIÓN DESDE LA FICHA (2026-09-17) — mismo comportamiento que el botón del panel:
     · data-listo="1" → el enlace ya viene armado: se abre al instante y el envío se apunta de fondo.
     · data-listo="0" → primera vez: hay que crear la cuenta de la tienda, así que la pestaña se
       abre DENTRO del clic (si no, el navegador la bloquea) y se manda a WhatsApp al responder. */
(function () {
  var form = document.getElementById('invf-form');
  if (!form) return;
  var btn = document.getElementById('invf-btn');
  var id  = parseInt(btn ? btn.getAttribute('data-inv-id') : 0, 10) || 0;

  function pintar(n) {
    n = Math.max(0, parseInt(n, 10) || 0);
    var boton = document.getElementById('invf-btn');
    if (boton) {
      boton.className = 'invf__btn invf__btn--n' + Math.min(3, n);
      var num = boton.querySelector('.invf__n');
      if (num) num.textContent = n;
    }
    var undo = document.getElementById('invf-undo');
    if (undo) undo.disabled = (n < 1);
  }

  function apuntar(accion, ok, error) {
    form.querySelector('[name=accion]').value = accion;
    fetch(window.location.pathname + window.location.search, {
      method: 'POST',
      body: new FormData(form),
      credentials: 'same-origin',
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    }).then(function (r) { return r.json(); }).then(function (d) {
      if (d && d.ok) ok(d);
      else if (d && d.msg) { if (error) error(); alert(d.msg); }
    }).catch(function () {
      if (error) error();
      alert('⚠️ No pude preparar la invitación. Revisa tu conexión y recarga la página.');
    });
  }

  window.invFichaInvitar = function (a) {
    if (id <= 0) return false;

    if (a.getAttribute('data-listo') === '1') {
      apuntar('tienda_invitar', function (d) { pintar(d.n); if (d.url) a.href = d.url; }, null);
      return true;   // el navegador abre WhatsApp con el mensaje escrito
    }

    var w = window.open('about:blank', '_blank');
    apuntar('tienda_invitar', function (d) {
      pintar(d.n);
      if (d.url) {
        a.href = d.url;
        a.setAttribute('data-listo', '1');
        a.setAttribute('target', '_blank');
        a.setAttribute('rel', 'noopener');
        if (d.usuario && d.clave) a.setAttribute('data-creds', 'usuario ' + d.usuario + ' · contraseña ' + d.clave);
        if (w) w.location.href = d.url; else window.location.href = d.url;
      } else if (w) { w.close(); }
      if (d.aviso) alert('📨 ' + d.aviso);
    }, function () { if (w) w.close(); });
    return false;   // el clic ya está manejado
  };

  window.invFichaDeshacer = function (b) {
    if (id > 0 && confirm('¿Quitar UNA invitación del conteo de esta tienda?')) {
      apuntar('tienda_invitar_deshacer', function (d) { pintar(d.n); }, null);
    }
    return false;
  };
})();
</script>
<?php
    return (string)ob_get_clean();
}
