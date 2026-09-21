<?php
/**
 * productos.php — Gestión de productos de una tienda (solo el dueño o admin).
 * ============================================================
 * Cada producto vive en directorio_servicios (con precio y unidad) y su
 * galería de fotos en directorio_producto_fotos. La primera foto de la
 * galería se usa como portada; si el producto ya tiene 'imagen', esa gana.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/btn_cerca.php';   // botón reutilizable "📍 Ver tiendas cerca"

requiere_login();
$usuario = usuario_actual();

$negocio_id = (int)($_GET['n'] ?? 0);
$stmt = db()->prepare("SELECT id, dueno_id, slug, nombre, estado, whatsapp, telefono
                       FROM directorio_negocios WHERE id = ? LIMIT 1");
$stmt->execute([$negocio_id]);
$negocio = $stmt->fetch();

if (!$negocio) {
    redirect('panel.php');
}
$es_admin = es_admin();
if (!$es_admin && (int)$negocio['dueno_id'] !== (int)$usuario['id']) {
    http_response_code(403);
    die('Esta tienda no te pertenece.');
}

$nota = '';
$nota_tipo = 'ok'; // ok | error

/* ---------- Subida de imágenes ----------
 * Usa el MOTOR COMPARTIDO (includes/imagenes.php): valida de verdad, guarda en
 * WebP (máx 1600 px) y genera las versiones de 300 px y 800 px.
 * Devuelve ['ok'=>bool, 'rel'=>ruta, 'error'=>mensaje].
 */
function producto_guardar_foto($archivo, $uid) {
    return img_guardar_subida(
        $archivo,
        'assets/uploads/' . (int)$uid,
        date('Ymd') . '_' . bin2hex(random_bytes(8))
    );
}

function producto_fotos_de($pdo, $producto_id) {
    $s = $pdo->prepare("SELECT id, ruta FROM directorio_producto_fotos WHERE producto_id = ? ORDER BY orden ASC, id ASC");
    $s->execute([$producto_id]);
    return $s->fetchAll();
}

function producto_primera_foto($pdo, $producto_id, $ruta_excluida = '') {
    $s = $pdo->prepare("SELECT ruta FROM directorio_producto_fotos
                        WHERE producto_id = ? AND ruta <> ? ORDER BY orden ASC, id ASC LIMIT 1");
    $s->execute([$producto_id, $ruta_excluida]);
    $r = $s->fetchColumn();
    return $r ?: null;
}

/* ================================================================================================
 * 🔐 LA PUERTA DE LA CONTRASEÑA (orden del jefe, 2026-09-16, textual: *«si el dueño tiene control
 * total de su tienda para editar, **siempre debe poner su contraseña**»*).
 *
 * Esta página es donde el dueño carga y edita los PRODUCTOS de su tienda (y donde vive la captura
 * rápida con cámara y voz). Antes de tocar nada, **pide su contraseña** y la comprueba con
 * `password_verify()`. Una vez dentro, la sesión queda desbloqueada **30 minutos** para ESA tienda:
 * así la captura rápida sigue siendo lo que promete el panel —*«toma la foto, dicta y publica, nada de
 * teclear»*— en vez de pedir la clave en cada producto. Pasados los 30 minutos (o en otro navegador,
 * o si alguien coge el celular más tarde) la vuelve a pedir.
 *
 * ⚠️ Un administrador NO pasa por la puerta: administra decenas de tiendas y ya tiene su propio panel.
 * ⚠️ Y los POST no se atienden sin el desbloqueo: sin puerta, no hay escritura.
 * ============================================================================================== */
require_once __DIR__ . '/includes/contrasena_dueno.php';
$__clave_sesion  = 'prod_ok_' . $negocio_id;
$__desbloqueado  = $es_admin || (int)($_SESSION[$__clave_sesion] ?? 0) >= time() - 1800;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && ($_POST['accion'] ?? '') === 'desbloquear') {
    csrf_verificar();
    $__err = contrasena_dueno_error((string)($_POST['password'] ?? ''), (int)$usuario['id'],
                                    'gestionar los productos de «' . (string)$negocio['nombre'] . '»');
    if ($__err === '') {
        iniciar_sesion();
        $_SESSION[$__clave_sesion] = time();
        redirect('productos.php?n=' . $negocio_id);
    }
    flash($__err, 'error');
    redirect('productos.php?n=' . $negocio_id);
}

if (!$__desbloqueado) {
    $titulo_pagina = 'Productos de ' . (string)$negocio['nombre'];
    include __DIR__ . '/includes/header.php';
    ?>
    <div style="max-width:520px;margin:24px auto;background:#fff;border-radius:16px;padding:20px;box-shadow:var(--sombra-tarjeta)">
        <h1 class="seccion__titulo" style="font-size:20px;margin-bottom:8px">🔐 Escribe tu contraseña</h1>
        <p style="font-size:15px;color:#6b7280;line-height:1.5;margin:0 0 16px">
            Vas a gestionar los productos de <strong><?= e((string)$negocio['nombre']) ?></strong>.
            Es tu tienda: nadie más puede tocarla.
        </p>
        <form method="post">
            <?= csrf_campo() ?>
            <input type="hidden" name="accion" value="desbloquear">
            <div class="form__group">
                <label class="form__label" for="prPass">Tu contraseña de DeChimbote.com</label>
                <input class="form__input" type="password" id="prPass" name="password" required
                       autocomplete="current-password" autofocus>
            </div>
            <button class="btn btn--block" type="submit">Entrar a mis productos</button>
        </form>
        <p style="font-size:13px;color:#6b7280;margin:14px 0 0">
            <a href="<?= e(url('mi-tienda.php?n=' . $negocio_id)) ?>">← Volver a editar mi tienda</a>
            · <a href="<?= e(url('panel.php#mis-negocios')) ?>">Mi panel</a>
        </p>
    </div>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

/* ---------- Acciones POST (CSRF + dueño verificado arriba + contraseña ya pedida) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'crear') {
        $titulo = trim($_POST['titulo'] ?? '');
        if (mb_strlen($titulo) < 2) {
            $nota = 'Escribe el nombre del producto.'; $nota_tipo = 'error';
        } else {
            $tipo = ($_POST['tipo'] ?? '') === 'virtual' ? 'virtual' : 'fisico';
            $precio = max(0, (float)($_POST['precio'] ?? 0));
            $unidad = trim($_POST['unidad'] ?? '');
            if ($unidad === '') $unidad = $tipo === 'virtual' ? 'sesión' : 'unidad';
            $desc = trim($_POST['descripcion'] ?? '') ?: null;
            $activo = isset($_POST['activo']) ? 1 : 0;
            $destacado = isset($_POST['destacado']) ? 1 : 0;

            $pdo = db();
            $pdo->prepare("INSERT INTO directorio_servicios
                (negocio_id, titulo, tipo_producto, descripcion, precio, unidad, imagen, destacado, activo)
                VALUES (?,?,?,?,?,?,?,?,?)")
                ->execute([$negocio_id, $titulo, $tipo, $desc, $precio, $unidad, null, $destacado, $activo]);
            $producto_id = (int)$pdo->lastInsertId();

            $subidas = 0;
            $error_foto = '';
            $fotos = $_FILES['fotos'] ?? null;
            if ($fotos && isset($fotos['name']) && is_array($fotos['name'])) {
                $orden = 0;
                $total = min(count($fotos['name']), 6);
                for ($i = 0; $i < $total; $i++) {
                    $f = [
                        'name' => $fotos['name'][$i], 'type' => $fotos['type'][$i],
                        'tmp_name' => $fotos['tmp_name'][$i], 'error' => $fotos['error'][$i],
                        'size' => $fotos['size'][$i],
                    ];
                    $res_foto = producto_guardar_foto($f, $usuario['id']);
                    if (empty($res_foto['ok'])) { $error_foto = $res_foto['error'] ?? 'Foto inválida.'; continue; }
                    $pdo->prepare("INSERT INTO directorio_producto_fotos (producto_id, ruta, orden) VALUES (?,?,?)")
                        ->execute([$producto_id, $res_foto['rel'], $orden++]);
                    $subidas++;
                }
            }
            if ($subidas > 0) {
                $pdo->prepare("UPDATE directorio_servicios SET imagen = ? WHERE id = ?")
                    ->execute([producto_primera_foto($pdo, $producto_id), $producto_id]);
            }
            // Efecto IKEA: se celebra lo que armó y se le recuerda que podrá
            // editarlo él mismo cuando quiera (botón ✏️ Editar).
            $nota = $subidas > 0
                ? '🎉 ¡Producto publicado con ' . $subidas . ($subidas === 1 ? ' foto' : ' fotos') . '! Ya se ve en tu tienda. Puedes cambiarle el nombre, el precio o las fotos cuando quieras (✏️ Editar).'
                : '🎉 ¡Producto publicado! Ya se ve en tu tienda. ¿Le tomas una foto con la cámara? En la lista de abajo, toca la casilla 📷 del producto.';
            if ($subidas === 0 && $error_foto !== '') {
                $nota = 'Producto creado, pero la foto no se pudo guardar: ' . $error_foto;
                $nota_tipo = 'error';
            }

            // 🔔 Aviso al jefe: el negocio publicó un producto
            aviso('producto_nuevo', [
                'negocio_id'  => $negocio_id,
                'producto_id' => $producto_id,
                'titulo'      => $titulo,
                'precio'      => $precio,
                'accion'      => 'publicado',
                'activo'      => $activo,
                'fotos'       => $subidas,
                'clave'       => 'prod:' . $producto_id,
                'dedupe_min'  => 1,
                'resumen'     => 'producto nuevo: ' . $titulo,
            ]);
        }
    }

    if ($accion === 'editar') {
        $pid = (int)($_POST['producto_id'] ?? 0);
        $s = db()->prepare("SELECT * FROM directorio_servicios WHERE id = ? AND negocio_id = ?");
        $s->execute([$pid, $negocio_id]);
        if ($s->fetch()) {
            $titulo = trim($_POST['titulo'] ?? '');
            if (mb_strlen($titulo) < 2) {
                $nota = 'Escribe el nombre del producto.'; $nota_tipo = 'error';
            } else {
                $tipo = ($_POST['tipo'] ?? '') === 'virtual' ? 'virtual' : 'fisico';
                $precio = max(0, (float)($_POST['precio'] ?? 0));
                $unidad = trim($_POST['unidad'] ?? '');
                if ($unidad === '') $unidad = $tipo === 'virtual' ? 'sesión' : 'unidad';
                db()->prepare("UPDATE directorio_servicios SET titulo=?, tipo_producto=?, descripcion=?,
                               precio=?, unidad=?, destacado=?, activo=? WHERE id=? AND negocio_id=?")
                    ->execute([
                        $titulo, $tipo, trim($_POST['descripcion'] ?? '') ?: null, $precio, $unidad,
                        isset($_POST['destacado']) ? 1 : 0, isset($_POST['activo']) ? 1 : 0,
                        $pid, $negocio_id,
                    ]);
                $nota = 'Producto actualizado.';

                // 🔔 Aviso al jefe: el negocio editó un producto
                aviso('producto_nuevo', [
                    'negocio_id'  => $negocio_id,
                    'producto_id' => $pid,
                    'titulo'      => $titulo,
                    'precio'      => $precio,
                    'accion'      => 'editado',
                    'activo'      => isset($_POST['activo']) ? 1 : 0,
                    'clave'       => 'prod:' . $pid . ':' . date('YmdHi'),
                    'dedupe_min'  => 1,
                    'resumen'     => 'producto editado: ' . $titulo,
                ]);
            }
        } else {
            $nota = 'Producto no encontrado.'; $nota_tipo = 'error';
        }
    }

    if ($accion === 'borrar') {
        $pid = (int)($_POST['producto_id'] ?? 0);
        $s = db()->prepare("SELECT id, titulo FROM directorio_servicios WHERE id = ? AND negocio_id = ?");
        $s->execute([$pid, $negocio_id]);
        $prod_borrar = $s->fetch();
        if ($prod_borrar) {
            foreach (producto_fotos_de(db(), $pid) as $f) {
                img_borrar($f['ruta']);   // borra también las versiones de 300/800 px
            }
            db()->prepare("DELETE FROM directorio_producto_fotos WHERE producto_id = ?")->execute([$pid]);
            db()->prepare("DELETE FROM directorio_servicios WHERE id = ? AND negocio_id = ?")->execute([$pid, $negocio_id]);
            $nota = 'Producto eliminado.';

            // 🔔 Aviso al jefe: el negocio borró un producto
            aviso('producto_borrado', [
                'negocio_id'  => $negocio_id,
                'producto_id' => $pid,
                'titulo'      => (string)($prod_borrar['titulo'] ?? ''),
                'clave'       => 'prodbaja:' . $pid,
                'dedupe_min'  => 1,
                'resumen'     => 'producto borrado: ' . ($prod_borrar['titulo'] ?? ''),
            ]);
        }
    }

    if ($accion === 'foto_subir') {
        $pid = (int)($_POST['producto_id'] ?? 0);
        $s = db()->prepare("SELECT imagen FROM directorio_servicios WHERE id = ? AND negocio_id = ?");
        $s->execute([$pid, $negocio_id]);
        $sv = $s->fetch();
        if ($sv) {
            $fotos = producto_fotos_de(db(), $pid);
            if (count($fotos) >= 6) {
                $nota = 'Máximo 6 fotos por producto.'; $nota_tipo = 'error';
            } else {
                $res_foto = producto_guardar_foto($_FILES['foto'] ?? null, $usuario['id']);
                if (empty($res_foto['ok'])) {
                    $nota = $res_foto['error'] ?? 'Foto inválida (solo JPG/PNG/WebP, máx. 12 MB).'; $nota_tipo = 'error';
                } else {
                    $ruta = $res_foto['rel'];
                    $orden = count($fotos);
                    db()->prepare("INSERT INTO directorio_producto_fotos (producto_id, ruta, orden) VALUES (?,?,?)")
                        ->execute([$pid, $ruta, $orden]);

                    // 🔔 Aviso al jefe: foto nueva en un producto
                    aviso('foto_nueva', [
                        'negocio_id'  => $negocio_id,
                        'producto_id' => $pid,
                        'cantidad'    => 1,
                        'producto'    => (string)($_POST['titulo_prod'] ?? ''),
                        'clave'       => 'foto_prod:' . $pid . ':' . $ruta,
                        'dedupe_min'  => 1,
                        'resumen'     => 'foto nueva de producto',
                    ]);
                    if (empty($sv['imagen'])) {
                        db()->prepare("UPDATE directorio_servicios SET imagen = ? WHERE id = ?")->execute([$ruta, $pid]);
                    }
                    $nota = '📸 ¡Foto agregada! Ya se ve en la galería del producto.';
                }
            }
        }
    }

    if ($accion === 'foto_quitar') {
        $fid = (int)($_POST['foto_id'] ?? 0);
        $s = db()->prepare("SELECT pf.id, pf.producto_id, pf.ruta FROM directorio_producto_fotos pf
                            JOIN directorio_servicios s ON s.id = pf.producto_id
                            WHERE pf.id = ? AND s.negocio_id = ?");
        $s->execute([$fid, $negocio_id]);
        $f = $s->fetch();
        if ($f) {
            img_borrar($f['ruta']);   // borra también las versiones de 300/800 px
            db()->prepare("DELETE FROM directorio_producto_fotos WHERE id = ?")->execute([$fid]);
            $sv = db()->prepare("SELECT imagen FROM directorio_servicios WHERE id = ?");
            $sv->execute([$f['producto_id']]);
            $imagen = $sv->fetchColumn();
            if ($imagen === $f['ruta']) {
                $nueva = producto_primera_foto(db(), $f['producto_id'], $f['ruta']);
                db()->prepare("UPDATE directorio_servicios SET imagen = ? WHERE id = ?")->execute([$nueva, $f['producto_id']]);
            }
            $nota = 'Foto eliminada.';
        }
    }

    // 🔤 TÍTULOS PREDICTIVOS: si cambió un nombre de producto, el diccionario de
    // sugerencias (cache/terminos.json) se rehace en la siguiente consulta.
    if (in_array($accion, ['crear', 'editar', 'borrar'], true)) {
        require_once __DIR__ . '/includes/terminos_sugerir.php';
        terminos_olvidar_cache();
    }

    redirect('productos.php?n=' . $negocio_id . ($nota !== '' ? '&aviso=' . urlencode($nota) . '&tipo=' . $nota_tipo : ''));
}

/* Aviso tras redirigir */
if (isset($_GET['aviso'])) {
    $nota = $_GET['aviso'];
    $nota_tipo = ($_GET['tipo'] ?? 'ok') === 'error' ? 'error' : 'ok';
}

/* ---------- Datos de la página ---------- */
$productos = db()->prepare("SELECT s.*, (SELECT COUNT(*) FROM directorio_producto_fotos pf WHERE pf.producto_id = s.id) AS n_fotos
                            FROM directorio_servicios s WHERE s.negocio_id = ? ORDER BY s.destacado DESC, s.id DESC");
$productos->execute([$negocio_id]);
$productos = $productos->fetchAll();

$galerias = [];
if ($productos) {
    $ids = array_map('intval', array_column($productos, 'id'));
    $in = implode(',', $ids);
    foreach (db()->query("SELECT producto_id, id, ruta FROM directorio_producto_fotos WHERE producto_id IN ($in) ORDER BY producto_id, orden ASC, id ASC")->fetchAll() as $g) {
        $galerias[$g['producto_id']][] = $g;
    }
}

$titulo_pagina = 'Productos de ' . $negocio['nombre'];
include __DIR__ . '/includes/header.php';
?>
<style>
  .adm-box{background:#fff;border:1px solid var(--color-borde);border-radius:var(--radio-borde);box-shadow:var(--sombra-tarjeta);padding:16px;margin-bottom:16px}
  .adm-aviso{border-radius:10px;padding:12px 14px;margin-bottom:14px;font-weight:600}
  .adm-aviso--ok{background:#dcfce7;color:#15803d;border:1px solid #bbf7d0}
  .adm-aviso--error{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca}
  .adm-chip{display:inline-block;border-radius:999px;padding:3px 10px;font-size:12px;font-weight:800}
  .adm-chip--activo{background:#dcfce7;color:#15803d}
  .adm-chip--oculto{background:#f3f4f6;color:#6b7280}
  .adm-chip--destacado{background:#fef3c7;color:#92400e}
  .adm-prod{display:flex;flex-direction:column;gap:10px}
  .adm-fotos{display:flex;gap:8px;flex-wrap:wrap}
  .adm-foto{position:relative;width:76px;height:76px}
  .adm-foto img{width:76px;height:76px;object-fit:cover;border-radius:10px;background:#f3f4f6}
  .adm-foto--portada img{outline:3px solid var(--color-exito)}
  .adm-foto form{position:absolute;top:-6px;right:-6px}
  .adm-foto .adm-x{background:#dc2626;color:#fff;border:0;width:20px;height:20px;border-radius:50%;cursor:pointer;font-weight:800;line-height:1}
  .adm-foto .adm-portada{position:absolute;left:0;bottom:0;background:rgba(22,163,74,.9);color:#fff;font-size:10px;font-weight:800;padding:1px 6px;border-radius:0 8px 0 8px}
  .adm-tipo{margin-bottom:6px}
  .adm-tipo label{margin-right:14px;font-weight:600}
  .adm-fila{display:flex;flex-wrap:wrap;gap:12px}
  .adm-fila > label{flex:1 1 180px}

  /* ===== 📸 CAPTURA RÁPIDA (rejilla de fotos con cámara) y 🎙️ DICTADO =====
     Estilos del componente captura_rapida.js + dictado_voz.js. Van inline
     porque solo los usa esta página (regla: componente puntual → CSS inline;
     CSS compartido → subir el ?v= de components.css). */
  /* ⚠️ `input:not([type])` es imprescindible: los campos de esta página no llevan
     atributo type (el navegador los trata como texto, pero el selector [type=text]
     NO los alcanza) y se quedaban en 15 px → iOS hacía zoom al enfocarlos. */
  .cz-form input:not([type]), .cz-form input[type="text"], .cz-form input[type="number"],
  .cz-form input[type="file"]{font-size:16px}
  .cz-form textarea{font-size:16px;min-height:84px}
  .cz-cap{margin-top:6px}
  .cz-cap__grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
  @media (min-width:720px){ .cz-cap__grid{grid-template-columns:repeat(6,1fr);gap:10px} }
  .cz-cap__celda{position:relative;aspect-ratio:1/1;width:100%;border:2px dashed var(--color-borde);
    border-radius:14px;background:#fdfaf5;display:flex;flex-direction:column;align-items:center;
    justify-content:center;gap:2px;overflow:hidden;cursor:pointer;font-family:inherit;padding:4px;
    text-align:center;transition:border-color .15s,transform .1s,background .15s}
  .cz-cap__celda:active{transform:scale(.97)}
  .cz-cap__celda--vacia:hover,.cz-cap__celda--vacia:focus-visible{border-color:var(--color-acento);
    background:#fff7ee;outline:none}
  .cz-cap__celda--llena{border-style:solid;border-color:var(--color-exito);box-shadow:0 2px 8px rgba(0,0,0,.08)}
  .cz-cap__ico{font-size:24px;line-height:1}
  .cz-cap__etiq{font-size:11px;font-weight:700;color:var(--color-texto-claro);line-height:1.15}
  .cz-cap__img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;display:block}
  .cz-cap__num{position:absolute;left:0;bottom:0;background:rgba(22,163,74,.92);color:#fff;font-size:10px;
    font-weight:800;padding:2px 7px;border-radius:0 10px 0 12px;pointer-events:none}
  .cz-cap__celda--llena:not(.cz-cap__celda--portada) .cz-cap__num{background:rgba(18,60,107,.85)}
  .cz-cap__equis{position:absolute;top:4px;right:4px;width:26px;height:26px;border-radius:50%;border:0;
    background:#dc2626;color:#fff;font-weight:900;font-size:13px;line-height:1;cursor:pointer;z-index:3;
    box-shadow:0 2px 6px rgba(0,0,0,.3)}
  .cz-cap__equis:active{transform:scale(.9)}
  .cz-cap__acciones{display:flex;flex-wrap:wrap;gap:8px;margin-top:10px}
  .cz-cap__btn{flex:1 1 auto;min-height:44px;border-radius:999px;border:0;padding:10px 16px;
    font-family:inherit;font-size:14.5px;font-weight:800;cursor:pointer}
  .cz-cap__btn--cam{background:var(--color-primario);color:#fff}
  .cz-cap__btn--cam:hover{background:#8c0a22}
  .cz-cap__btn--gal{background:#fff;color:var(--color-primario);border:1px solid var(--color-borde)}
  .cz-cap__btn:active{transform:scale(.98)}
  .cz-cap__btn:disabled{opacity:.5;cursor:not-allowed}
  .cz-cap--mini .cz-cap__grid{grid-template-columns:92px}
  .cz-cap--mini .cz-cap__acciones{margin-top:8px}
  .cz-cap--mini .cz-cap__btn{flex:0 1 auto;font-size:13.5px;padding:9px 14px}
  .cz-cap__aviso{display:none;margin-top:8px;border-radius:10px;padding:9px 12px;font-size:13px;
    font-weight:600;border-left:4px solid var(--color-acento);background:#fff7ee;color:#7a3c00}
  .cz-cap__aviso--visible{display:block}
  .cz-cap__aviso--ok{border-left-color:var(--color-exito);background:#ecfdf3;color:#15803d}
  .cz-cap__aviso--error{border-left-color:#dc2626;background:#fef2f2;color:#b91c1c}
  .cz-cap__contador{display:inline-block;margin-top:10px;border-radius:999px;padding:6px 14px;font-size:13px;
    font-weight:800;background:#fef3c7;color:#92400e;border:1px solid #fde68a}
  .cz-dict{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin-top:6px}
  .cz-dict__btn{min-height:40px;border-radius:999px;border:1px solid var(--color-borde);background:#fff;
    color:var(--color-primario);font-family:inherit;font-size:13.5px;font-weight:800;padding:8px 16px;cursor:pointer}
  .cz-dict__btn:hover{background:#fdf6ec}
  .cz-dict__btn:active{transform:scale(.98)}
  .cz-dict__btn.is-escuchando{background:#c1121f;border-color:#c1121f;color:#fff;
    animation:czDictLatido 1.1s ease-in-out infinite}
  @keyframes czDictLatido{0%,100%{box-shadow:0 0 0 0 rgba(193,18,31,.55)}50%{box-shadow:0 0 0 8px rgba(193,18,31,0)}}
  .cz-dict-escuchando{border-color:#c1121f!important;box-shadow:0 0 0 3px rgba(193,18,31,.16)!important}
  .cz-dict__aviso{display:none;flex:1 1 100%;border-radius:10px;padding:9px 12px;font-size:12.5px;
    font-weight:600;border-left:4px solid var(--color-acento);background:#fff7ee;color:#7a3c00}
  .cz-dict__aviso--visible{display:block}
  .cz-dict__aviso--ok{border-left-color:var(--color-exito);background:#ecfdf3;color:#15803d}
  .cz-dict__aviso--error{border-left-color:#dc2626;background:#fef2f2;color:#b91c1c}
  @media (prefers-reduced-motion:reduce){
    .cz-dict__btn.is-escuchando{animation:none}
    .cz-cap__celda:active,.cz-cap__btn:active,.cz-dict__btn:active{transform:none}
  }
</style>

<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:16px">
    <div>
        <h1 class="seccion__titulo">🛒 Productos de <?= e($negocio['nombre']) ?></h1>
        <small style="color:var(--color-texto-claro)">
            Estado: <strong><?= e($negocio['estado']) ?></strong> ·
            <a href="<?= url_negocio($negocio['slug']) ?>" target="_blank">Ver tienda pública ↗</a>
        </small>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        <a class="btn" href="<?= url('panel.php') ?>">← Mi panel</a>
    </div>
</div>

<?php if ($nota !== ''): ?>
    <div class="adm-aviso adm-aviso--<?= $nota_tipo === 'error' ? 'error' : 'ok' ?>"><?= e($nota) ?></div>
<?php endif; ?>

<!-- 📍 BOTÓN "VER TIENDAS CERCA" — en las fichas de los productos, encima de la galería -->
<?= btn_tiendas_cerca_html() ?>

<!-- ===== Lista de productos ===== -->
<?php if ($productos): ?>
    <div class="adm-box">
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;margin-bottom:12px">
            <h2 class="seccion__titulo" style="margin:0;font-size:17px">Tus productos (<?= count($productos) ?>)</h2>
        </div>
        <div class="adm-prod">
            <?php foreach ($productos as $p): ?>
                <div style="border:1px solid var(--color-borde);border-radius:var(--radio-borde);padding:12px">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap">
                        <div>
                            <strong><?= e($p['titulo']) ?></strong>
                            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-top:6px">
                                <?php if ($p['destacado']): ?><span class="adm-chip adm-chip--destacado">★ Destacado</span><?php endif; ?>
                                <span class="adm-chip <?= $p['activo'] ? 'adm-chip--activo' : 'adm-chip--oculto' ?>"><?= $p['activo'] ? '🟢 Visible' : '⚪ Oculto' ?></span>
                                <span class="adm-chip adm-chip--oculto"><?= $p['tipo_producto'] === 'virtual' ? '💻 Virtual' : '🏬 Físico' ?></span>
                                <?php // ⚡ VIGENCIA (2026-09-10): productos de corta duración (lo que trajo hoy)
                                $dh = trim((string)($p['disponible_hasta'] ?? ''));
                                if ($dh !== ''):
                                    $vencido = ($dh < date('Y-m-d')); ?>
                                    <span class="adm-chip" style="background:<?= $vencido ? '#fee2e2;color:#991b1b' : '#fff7ed;color:#9a3412' ?>">
                                        ⚡ <?= $vencido ? 'Caducó el ' . e(date('d/m', strtotime($dh))) : 'Hasta el ' . e(date('d/m H:i', strtotime($dh . ' 23:59'))) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="text-align:right;font-size:15px">
                            <strong style="color:var(--color-exito)"><?= formato_precio($p['precio']) ?></strong>
                            <?php if ($p['unidad']): ?><small style="color:var(--color-texto-claro)"> / <?= e($p['unidad']) ?></small><?php endif; ?>
                        </div>
                    </div>
                    <?php if ($p['descripcion']): ?>
                        <p style="color:var(--color-texto-claro);font-size:13px;margin:8px 0 0"><?= e($p['descripcion']) ?></p>
                    <?php endif; ?>

                    <!-- Fotos del producto -->
                    <?php $fotosP = $galerias[$p['id']] ?? []; ?>
                    <?php if ($fotosP): ?>
                        <div class="adm-fotos" style="margin-top:10px">
                            <?php foreach ($fotosP as $i => $f): ?>
                                <div class="adm-foto <?= $i === 0 ? 'adm-foto--portada' : '' ?>">
                                    <?= img_tag($f['ruta'], '', ['sizes' => '76px']) ?>
                                    <?php if ($i === 0): ?><span class="adm-portada">PORTADA</span><?php endif; ?>
                                    <form method="post" onsubmit="return confirm('¿Quitar esta foto?')">
                                        <?= csrf_campo() ?>
                                        <input type="hidden" name="accion" value="foto_quitar">
                                        <input type="hidden" name="foto_id" value="<?= (int)$f['id'] ?>">
                                        <button class="adm-x" title="Quitar foto">✕</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Agregar foto (con la cámara, vista previa instantánea) + editar + eliminar -->
                    <div style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-start;margin-top:10px">
                        <form method="post" enctype="multipart/form-data" class="cz-form" style="flex:1 1 240px;min-width:220px">
                            <?= csrf_campo() ?>
                            <input type="hidden" name="accion" value="foto_subir">
                            <input type="hidden" name="producto_id" value="<?= (int)$p['id'] ?>">
                            <div class="cz-cap cz-cap--mini"
                                 data-cz-captura
                                 data-cz-max="1"
                                 data-cz-multiple="0"
                                 data-cz-destino="#czFotoP<?= (int)$p['id'] ?>"
                                 data-cz-requerido="#czBtnFotoP<?= (int)$p['id'] ?>"
                                 data-cz-etiqueta="Foto"
                                 data-cz-texto="🏗️ Foto lista para <?= e($p['titulo']) ?>"
                                 data-cz-texto-cero="📷 Toca para tomar la foto con la cámara"></div>
                            <input type="file" name="foto" accept="image/*" id="czFotoP<?= (int)$p['id'] ?>" data-cz-optim="1" hidden>
                            <button class="btn" type="submit" id="czBtnFotoP<?= (int)$p['id'] ?>" disabled
                                    style="font-size:13px;padding:8px 14px;margin-top:8px">Guardar foto</button>
                        </form>
                        <details style="margin-left:auto">
                            <summary class="btn" style="display:inline-block;cursor:pointer;font-size:13px;padding:6px 12px">✏️ Editar</summary>
                            <form method="post" class="cz-form" style="border-top:1px dashed var(--color-borde);margin-top:10px;padding-top:10px">
                                <?= csrf_campo() ?>
                                <input type="hidden" name="accion" value="editar">
                                <input type="hidden" name="producto_id" value="<?= (int)$p['id'] ?>">
                                <div class="adm-fila">
                                    <label>Nombre<br><input class="form__input" name="titulo" value="<?= e($p['titulo']) ?>" required data-dictado="titulo" data-terminos="1"></label>
                                    <label>Precio (S/)<br><input class="form__input" type="number" step="0.01" min="0" name="precio" value="<?= e($p['precio']) ?>"></label>
                                    <label>Unidad<br><input class="form__input" name="unidad" value="<?= e($p['unidad']) ?>" data-terminos="unidad"></label>
                                </div>
                                <div class="adm-tipo" style="margin-top:8px">
                                    <label><input type="radio" name="tipo" value="fisico" <?= $p['tipo_producto'] !== 'virtual' ? 'checked' : '' ?>> 🏬 Físico</label>
                                    <label><input type="radio" name="tipo" value="virtual" <?= $p['tipo_producto'] === 'virtual' ? 'checked' : '' ?>> 💻 Virtual</label>
                                    <label><input type="checkbox" name="destacado" value="1" <?= $p['destacado'] ? 'checked' : '' ?>> ★ Destacado</label>
                                    <label><input type="checkbox" name="activo" value="1" <?= $p['activo'] ? 'checked' : '' ?>> Visible</label>
                                </div>
                                <label style="display:block;margin-top:6px">Descripción (dicta o escribe)<br>
                                    <textarea class="form__input" name="descripcion" rows="2" style="width:100%" data-dictado="descripcion"><?= e($p['descripcion']) ?></textarea>
                                </label>
                                <div style="display:flex;gap:8px;margin-top:10px;flex-wrap:wrap">
                                    <button class="btn" type="submit">Guardar cambios</button>
                                    <button class="btn" type="submit" form="formBorrar-<?= (int)$p['id'] ?>" style="background:#dc2626;color:#fff"
                                            onclick="return confirm('¿Eliminar este producto y sus fotos?')">Eliminar producto</button>
                                </div>
                            </form>
                        </details>
                        <form id="formBorrar-<?= (int)$p['id'] ?>" method="post">
                            <?= csrf_campo() ?>
                            <input type="hidden" name="accion" value="borrar">
                            <input type="hidden" name="producto_id" value="<?= (int)$p['id'] ?>">
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php else: ?>
    <div class="empty-state">
        <div class="empty-state__icono">🛒</div>
        <div class="empty-state__titulo">Aún no tienes productos</div>
        <p>Baja a <b>📸 Captura rápida</b>: toma la foto con la cámara y dicta la descripción con el micrófono 🎙️.</p>
    </div>
<?php endif; ?>

<!-- ===== 📸 CAPTURA RÁPIDA (la experiencia de Caminante, ahora en tu panel) =====
     El dueño toma la foto con la cámara del celular, la ve AL INSTANTE en la
     casilla y dicta la descripción con el micrófono 🎙️ (Web Speech API).
     Sin teclear. Todo lo manejan assets/js/captura_rapida.js y dictado_voz.js. -->
<div class="adm-box" id="crear">
    <h2 class="seccion__titulo" style="margin:0 0 6px;font-size:17px">📸 Captura rápida: agrega un producto a <?= e($negocio['nombre']) ?></h2>
    <p style="margin:0 0 12px;font-size:13.5px;color:var(--color-texto-claro)">
        <b>Toca una casilla y toma la foto con la cámara</b> (la ves al instante) y <b>dicta la descripción</b>
        con el micrófono 🎙️ en vez de escribir. Podrás editarlo o borrarlo cuando quieras.
    </p>
    <form method="post" enctype="multipart/form-data" class="cz-form">
        <?= csrf_campo() ?>
        <input type="hidden" name="accion" value="crear">

        <div style="font-size:12px;font-weight:800;color:var(--color-texto-claro);text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px">1 · Las fotos</div>
        <div class="cz-cap"
             data-cz-captura
             data-cz-max="6"
             data-cz-destino="#czFotosProducto"
             data-cz-contador="#czContProducto"
             data-cz-etiqueta="Foto"
             data-cz-texto="🏗️ Tu producto ya tiene {n} de {max} fotos · la 1.ª será la portada"
             data-cz-texto-cero="📷 Toca una casilla para tomar la primera foto con la cámara"></div>
        <!-- Las fotos viajan en ESTE input (en orden). data-cz-optim="1" evita que
             imagen_optimizar.js lo reenganche: captura_rapida.js ya las optimizó. -->
        <input type="file" name="fotos[]" accept="image/*" multiple id="czFotosProducto" data-cz-optim="1" hidden>
        <span class="cz-cap__contador" id="czContProducto">📷 Toca una casilla para tomar la primera foto con la cámara</span>

        <div style="font-size:12px;font-weight:800;color:var(--color-texto-claro);text-transform:uppercase;letter-spacing:.05em;margin:16px 0 6px">2 · Lo que es y cuánto cuesta</div>
        <div class="adm-fila">
            <label>Nombre del producto *<br><input class="form__input" name="titulo" required placeholder="Ej: Aceite 20W-50 1L" data-dictado="titulo" data-terminos="1"></label>
            <label>Precio (S/) — 0 = a consultar<br><input class="form__input" type="number" step="0.01" min="0" name="precio" value="0"></label>
            <label>Unidad<br><input class="form__input" name="unidad" placeholder="por unidad / por kilo / por sesión" value="unidad" data-terminos="unidad"></label>
        </div>
        <div class="adm-tipo" style="margin-top:10px">
            <label><input type="radio" name="tipo" value="fisico" checked> 🏬 Producto físico</label>
            <label><input type="radio" name="tipo" value="virtual"> 💻 Servicio / virtual</label>
            <label><input type="checkbox" name="destacado" value="1"> ★ Destacado</label>
            <label><input type="checkbox" name="activo" value="1" checked> Visible</label>
        </div>
        <label style="display:block;margin-top:8px">Descripción (dicta o escribe)<br>
            <textarea class="form__input" name="descripcion" rows="3" style="width:100%" placeholder="Marca, tamaño, promoción…" data-dictado="descripcion"></textarea>
        </label>
        <button class="btn" type="submit" style="margin-top:12px">💾 Publicar producto</button>
    </form>
</div>

<!-- ===== 📸 CAPTURA RÁPIDA + 🎙️ DICTADO (orden obligatorio) ==================
     1) imagen_optimizar.js → comprime la foto EN EL CELULAR antes de subirla
        (WebP, máx 1600 px). Es el motor compartido con Caminante.
     2) dictado_voz.js → píldora 🎙️ en los campos con `data-dictado` (Web Speech
        API nativa, $0). Si el navegador no la soporta, la píldora no se pinta.
     3) captura_rapida.js → la rejilla de fotos con cámara y VISTA PREVIA
        instantánea (la experiencia de Caminante, ahora en el panel del dueño).
     captura_rapida.js usa CZImg: por eso 1 va antes que 3. -->
<script src="<?= url('assets/js/imagen_optimizar.js') ?>?v=1"></script>
<script src="<?= url('assets/js/dictado_voz.js') ?>?v=1"></script>
<script src="<?= url('assets/js/captura_rapida.js') ?>?v=1"></script>
<!-- 🔤 Títulos y unidades PREDICTIVOS: sugiere los que YA existen en el sitio (2026-09-10) -->
<script src="<?= url('assets/js/terminos_sugerir.js') ?>?v=1"></script>
<script>
(function(){
  // Los input[type=file] que maneja captura_rapida.js llevan data-cz-optim="1"
  // (sus archivos ya vienen comprimidos y EN ORDEN: el 1.º es la portada), así
  // que engancharTodos los salta solo. Queda para inputs de imagen futuros.
  if (window.CZImg) CZImg.engancharTodos(document);
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
