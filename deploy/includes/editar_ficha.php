<?php
/**
 * includes/editar_ficha.php — 🛡️ EL SÚPER ADMIN EDITA LA TIENDA DESDE SU PROPIA FICHA
 * ============================================================================
 * Pedido del jefe (2026-09-18, textual): *«cuando estoy en modo súper administrador, bloqueado,
 * dentro de cada tienda debe aparecerme un menú para editar esa tienda: como su número de teléfono,
 * su descripción, sus productos y otras cosas más. En las áreas de editar sus productos o editar su
 * descripción coloca un botón que diga IA: significa que al presionar ese botón esa área en particular
 * va a ser reescrita usando inteligencia artificial.»*
 *
 * 🔒 **SOLO EL SÚPER ADMINISTRADOR.** Si el que mira la ficha no es admin, `editar_ficha_html()`
 *    devuelve **una cadena vacía** (ni un byte) y cualquier POST se rechaza con **403**. El público
 *    —dueños y visitantes— sigue viendo la ficha exactamente igual que siempre.
 *
 * 🔑 **No se pide contraseña.** El candado del dueño (`mi-tienda.php`, orden del jefe del 2026-09-16)
 *    sigue en pie para el dueño; esto es la herramienta del **súper administrador**, y su llave es su
 *    propia sesión de admin + el token CSRF (el mismo patrón que el botón 📨 de invitación).
 *
 * Qué se puede editar (las 4 pestañas del menú):
 *   🏪 **Datos**      · nombre, rubro, cómo atiende, zona (distrito), dirección, horario, WhatsApp,
 *                       teléfono, Facebook/Instagram/TikTok y si la tienda se ve o se oculta.
 *   📝 **Descripción** · el texto de la ficha, a mano **o reescrito por la IA** (botón 🤖 IA).
 *   🛍️ **Productos**   · crear, editar (título, precio, unidad, texto, visible/destacado), **🤖 IA**
 *                       por producto, y borrar. Con buscador para no perderse entre muchos.
 *   🖼️ **Fotos**       · subir, poner de portada y borrar.
 *
 * ⚙️ **La IA es la del sitio, no una nueva**: el texto de la tienda lo escribe
 * `tienda_ia_ia_descripcion()` (el mismo copywriting con colores y botones que usa El maestro y
 * `mi-tienda.php`) y el de cada producto `tienda_ia_ia_descripcion_producto()` (que además **mira la
 * foto del producto** si la tiene). Se limpia con `tienda_ia_copy_limpiar()` (solo las etiquetas
 * permitidas) antes de guardar. Cada pulsación del botón IA = **una llamada** (~US$ 0,0002).
 *
 * Cómo viaja: el panel hace `fetch` al **mismo URL de la ficha** (`accion=edt_…`), igual que el botón
 * de invitación; `editar_ficha_accion()` se llama al principio de `negocio.php` y termina la petición
 * con el JSON. Sin JavaScript, los formularios siguen funcionando (POST normal → vuelve a la ficha).
 *
 * Guía: **`GUIA_EDITAR_TIENDA_DESDE_LA_FICHA.md`**
 * ============================================================================
 */

/**
 * ¿Quién puede ver y usar el menú? SOLO el súper administrador.
 * (Sin caché a propósito: `es_admin()` lee la sesión y ya está cacheada por petición; así la sonda
 *  puede probar las dos caras —admin y público— en la misma corrida.)
 */
function editar_ficha_puede(): bool {
    try { return (function_exists('es_admin') && es_admin()); }
    catch (Throwable $e) { return false; }
}

/** ¿La petición viene del panel (fetch)? */
function editar_ficha_es_ajax(): bool {
    if (strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest') return true;
    return (strpos(strtolower((string)($_SERVER['HTTP_ACCEPT'] ?? '')), 'application/json') !== false);
}

/** La tienda completa (con el nombre de su rubro y de su distrito). */
function editar_ficha_negocio($id) {
    try {
        $st = db()->prepare("SELECT n.*, c.nombre AS rubro_nombre, c.icono AS rubro_icono,
                                    d.nombre AS distrito_nombre
                               FROM directorio_negocios n
                               LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                               LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
                              WHERE n.id = ? LIMIT 1");
        $st->execute([(int)$id]);
        $n = $st->fetch(PDO::FETCH_ASSOC);
        return $n ?: null;
    } catch (Throwable $e) { return null; }
}

/* =====================================================================================
 * LA IA: el texto de la tienda y el de cada producto
 * ===================================================================================== */

/**
 * Arma el `$d` que esperan los escritores de la IA del sitio (`tienda_ia_ia_descripcion` y
 * `tienda_ia_ia_descripcion_producto`) a partir de la fila del negocio.
 */
function editar_ficha_datos_ia(array $neg): array {
    if (!function_exists('tienda_ia_datos_vacios')) require_once __DIR__ . '/tienda_ia.php';
    $d = tienda_ia_datos_vacios();
    $d['nombre']          = (string)($neg['nombre'] ?? '');
    $d['rubro_nombre']    = (string)($neg['rubro_nombre'] ?? '');
    $d['distrito_nombre'] = (string)($neg['distrito_nombre'] ?? '');
    $d['vendedor']        = (string)($neg['ubicacion_tipo'] ?? 'fisica');
    $d['horario']         = (string)($neg['horario'] ?? '');
    $d['whatsapp']        = (string)($neg['whatsapp'] ?? '');
    $d['direccion']       = (string)($neg['direccion'] ?? '');
    $d['negocio_id']      = (int)($neg['id'] ?? 0);
    $d['publicado']       = ['negocio_id' => (int)($neg['id'] ?? 0), 'slug' => (string)($neg['slug'] ?? '')];
    return $d;
}

/** Los productos de la tienda, tal como los quiere el copy de la IA (título + precio). */
function editar_ficha_productos_ia($negocio_id, $limite = 24) {
    if (!function_exists('tienda_ia_productos_publicados')) require_once __DIR__ . '/tienda_ia.php';
    return tienda_ia_productos_publicados((int)$negocio_id, (int)$limite);
}

/**
 * ✍️ EL TEXTO DE LA TIENDA, ESCRITO POR LA IA (una llamada).
 * Devuelve el HTML del copy (ya limpio con las etiquetas permitidas) o '' si la IA no pudo.
 */
function editar_ficha_ia_descripcion(array $neg) {
    if (!function_exists('tienda_ia_ia_descripcion')) require_once __DIR__ . '/tienda_ia.php';
    $d = editar_ficha_datos_ia($neg);
    $prods = editar_ficha_productos_ia((int)$neg['id']);
    $copy = tienda_ia_ia_descripcion($d, ['productos' => $prods]);
    if (!is_string($copy) || trim($copy) === '') return '';
    $limpio = trim(tienda_ia_copy_limpiar($copy));
    return mb_substr($limpio, 0, 6000);
}

/**
 * ✍️ EL TEXTO DE UN PRODUCTO, ESCRITO POR LA IA (una llamada).
 * Mira su foto si la tiene y **respeta lo que el jefe ya escribió** (copywriting, no invención).
 */
function editar_ficha_ia_producto(array $neg, $titulo, $texto_dueno = '', $fotos = []) {
    if (!function_exists('tienda_ia_ia_descripcion_producto')) require_once __DIR__ . '/tienda_ia.php';
    $d = editar_ficha_datos_ia($neg);
    // El contexto extra que hace que el texto salga con sentido para ESA tienda.
    $d['trato'] = trim((string)($neg['rubro_nombre'] ?? '')) !== ''
        ? 'Es una tienda de ' . (string)$neg['rubro_nombre'] . ' en ' . (string)($neg['distrito_nombre'] ?? 'Chimbote') . '.'
        : '';
    $rels = [];
    foreach ((array)$fotos as $f) { if (is_string($f) && trim($f) !== '') $rels[] = trim($f); }
    $txt = tienda_ia_ia_descripcion_producto($d, (string)$titulo, $rels, [], (string)$texto_dueno);
    return mb_substr(trim((string)$txt), 0, 500);
}

/** Las fotos de un producto (para que la IA las mire y para saber si tiene alguna). */
function editar_ficha_fotos_producto($producto_id) {
    try {
        $st = db()->prepare("SELECT ruta FROM directorio_producto_fotos WHERE producto_id = ? ORDER BY orden ASC, id ASC LIMIT 3");
        $st->execute([(int)$producto_id]);
        return array_values(array_filter(array_map('strval', $st->fetchAll(PDO::FETCH_COLUMN))));
    } catch (Throwable $e) { return []; }
}

/* =====================================================================================
 * LAS ACCIONES (POST del panel) — todas con la puerta del admin + CSRF
 * ===================================================================================== */

/** Responde el JSON del panel y termina (el `fetch` del navegador espera esto). */
function editar_ficha_json(array $r) {
    if (!headers_sent()) header('Content-Type: application/json; charset=utf-8');
    echo json_encode($r, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Atiende el POST del panel. **Termina la petición** si la acción es suya.
 *
 * 🧪 **Gancho de las SONDAS** (mismo estilo que `$GLOBALS['SUPREMO_DIAG']` del Supremo): con
 *    `$GLOBALS['EDT_MODO_PRUEBA'] = 1` la función **devuelve** el resultado en vez de responder y
 *    terminar, así una sonda puede probar las 11 acciones seguidas en una sola petición.
 *    Sin el gancho no cambia nada: la web responde el JSON y sale, como siempre.
 *
 * @return bool|array true si ya se respondió; con el gancho, el array del resultado.
 */
function editar_ficha_accion(array $negocio) {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') return false;
    $accion = (string)($_POST['accion'] ?? '');
    if (strpos($accion, 'edt_') !== 0) return false;

    $prueba  = !empty($GLOBALS['EDT_MODO_PRUEBA']);
    $id      = (int)($negocio['id'] ?? 0);
    $es_ajax = editar_ficha_es_ajax();

    // 🔒 La puerta: es una herramienta del Súper Admin, no del público.
    if (!editar_ficha_puede() || $id <= 0) {
        if ($prueba) return ['ok' => false, 'error' => '403'];
        http_response_code(403);
        if ($es_ajax) editar_ficha_json(['ok' => false, 'msg' => 'Solo el súper administrador puede editar tiendas.']);
        header('Content-Type: text/plain; charset=utf-8');
        echo 'Solo el súper administrador puede editar tiendas.';
        exit;
    }

    csrf_verificar();

    // La fila COMPLETA (la de `negocio.php` viene recortada para la ficha).
    $neg = editar_ficha_negocio($id) ?: $negocio;
    $volver = url_negocio((string)($neg['slug'] ?? ''));

    $ok  = true;
    $msg = '';
    $extra = [];

    /* ---------------------------------------------------------------- 🏪 LOS DATOS */
    if ($accion === 'edt_datos') {
        $campos = []; $par = [];

        $nombre = trim(strip_tags((string)($_POST['nombre'] ?? '')));
        if (mb_strlen($nombre) >= 2 && mb_strlen($nombre) <= 180) { $campos[] = 'nombre = ?'; $par[] = $nombre; }

        // 🏷️ El rubro: se resuelve por nombre (el panel manda el nombre que el jefe escriba o toque).
        $rubro_txt = trim((string)($_POST['rubro'] ?? ''));
        if ($rubro_txt !== '') {
            $cid = 0;
            try {
                $st = db()->prepare("SELECT id FROM directorio_categorias WHERE nombre = ? LIMIT 1");
                $st->execute([$rubro_txt]);
                $cid = (int)$st->fetchColumn();
                if ($cid <= 0) {
                    $st = db()->prepare("SELECT id FROM directorio_categorias WHERE nombre LIKE ? ORDER BY nombre ASC LIMIT 1");
                    $st->execute(['%' . $rubro_txt . '%']);
                    $cid = (int)$st->fetchColumn();
                }
            } catch (Throwable $e) {}
            if ($cid > 0) { $campos[] = 'categoria_id = ?'; $par[] = $cid; }
        }

        $dist = (int)($_POST['distrito_id'] ?? 0);
        if ($dist > 0) { $campos[] = 'distrito_id = ?'; $par[] = $dist; }

        $vende = (string)($_POST['vendedor'] ?? '');
        if (in_array($vende, ['fisica', 'ambulante', 'domicilio', 'nacional', 'mayorista'], true)) {
            $campos[] = 'ubicacion_tipo = ?'; $par[] = $vende;
        }

        $dir = trim(strip_tags((string)($_POST['direccion'] ?? '')));
        $campos[] = 'direccion = ?'; $par[] = ($dir !== '' ? mb_substr($dir, 0, 160) : null);

        $hor = trim(strip_tags((string)($_POST['horario'] ?? '')));
        $campos[] = 'horario = ?'; $par[] = ($hor !== '' ? mb_substr($hor, 0, 60) : null);

        // 📱 Los números: se guardan solo los dígitos (el sitio arma los enlaces wa.me/tel:).
        $wa  = preg_replace('/\D+/', '', (string)($_POST['whatsapp'] ?? ''));
        $tel = preg_replace('/\D+/', '', (string)($_POST['telefono'] ?? ''));
        if (mb_strlen($wa) === 11 && substr($wa, 0, 2) === '51') $wa = substr($wa, 2);
        $campos[] = 'whatsapp = ?'; $par[] = ($wa !== '' ? mb_substr($wa, 0, 15) : null);
        $campos[] = 'telefono = ?'; $par[] = ($tel !== '' ? mb_substr($tel, 0, 15) : null);

        // 🔗 Las redes (el enlace completo o el usuario; se guarda tal cual menos si viene vacío).
        foreach ([['facebook', 180], ['instagram', 180], ['tiktok', 180]] as $red) {
            $v = trim((string)($_POST[$red[0]] ?? ''));
            $campos[] = $red[0] . ' = ?';
            $par[] = ($v !== '' ? mb_substr($v, 0, (int)$red[1]) : null);
        }

        $estado = (string)($_POST['estado'] ?? '');
        if (in_array($estado, ['activo', 'inactivo'], true)) { $campos[] = 'estado = ?'; $par[] = $estado; }

        if (!$campos) {
            $ok = false; $msg = 'No había nada que guardar.';
        } else {
            $campos[] = 'actualizado_en = ?'; $par[] = date('Y-m-d H:i:s');
            $par[] = $id;
            try {
                db()->prepare('UPDATE directorio_negocios SET ' . implode(', ', $campos) . ' WHERE id = ?')->execute($par);
                $msg = '🏪 Datos guardados.';
                require_once __DIR__ . '/fuzzy_cache.php';
                fuzzy_olvidar_cache();
            } catch (Throwable $e) {
                error_log('edt_datos: ' . $e->getMessage());
                $ok = false; $msg = '😅 No pude guardar los datos.';
            }
        }
    }

    /* ---------------------------------------------------------------- 📝 LA DESCRIPCIÓN */
    if ($accion === 'edt_desc') {
        require_once __DIR__ . '/tienda_ia.php';   // `tienda_ia_copy_limpiar()`
        $texto = (string)($_POST['descripcion'] ?? '');
        // 🧼 Primero se van los bloques que NO deben quedar ni en texto (<script>, <style>, <iframe>):
        //    `strip_tags()` quita la etiqueta pero deja el contenido, y eso se vería como basura.
        $texto = (string)preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1\s*>#is', '', $texto);
        $texto = (string)preg_replace('#<(script|style|iframe|object|embed)\b[^>]*/?>#is', '', $texto);
        $limpio = mb_substr(tienda_ia_copy_limpiar($texto), 0, 6000);
        try {
            db()->prepare("UPDATE directorio_negocios SET descripcion = ?, actualizado_en = ? WHERE id = ?")
                ->execute([($limpio !== '' ? $limpio : null), date('Y-m-d H:i:s'), $id]);
            // El buscador del sitio guarda un texto ya limpio de cada tienda: se le avisa.
            require_once __DIR__ . '/fuzzy_cache.php';
            fuzzy_olvidar_cache();
            $msg = '📝 Descripción guardada.';
        } catch (Throwable $e) {
            error_log('edt_desc: ' . $e->getMessage());
            $ok = false; $msg = '😅 No pude guardar la descripción.';
        }
    }

    if ($accion === 'edt_desc_ia') {
        $nuevo = editar_ficha_ia_descripcion($neg);
        if ($nuevo === '') {
            $ok = false; $msg = '😅 La IA no pudo escribir el texto ahora mismo. Prueba otra vez en un rato.';
        } else {
            $msg = '🤖 La IA reescribió la descripción. **Mírala y toca 💾 Guardar** para dejarla así.';
            $extra['descripcion'] = $nuevo;
        }
    }

    /* ---------------------------------------------------------------- 🛍️ LOS PRODUCTOS */
    if ($accion === 'edt_lista') {
        $extra['productos'] = editar_ficha_lista($id);
        $msg = count($extra['productos']) . ' producto(s).';
    }

    if ($accion === 'edt_fotos') {
        $extra['fotos'] = editar_ficha_lista_fotos($id);
        $msg = count($extra['fotos']) . ' foto(s).';
    }

    if ($accion === 'edt_prod') {
        $pid = (int)($_POST['producto_id'] ?? 0);
        $titulo = trim(strip_tags((string)($_POST['titulo'] ?? '')));
        if ($pid <= 0 || mb_strlen($titulo) < 2) {
            $ok = false; $msg = 'Escribe el nombre del producto.';
        } else {
            try {
                $st = db()->prepare("SELECT id FROM directorio_servicios WHERE id = ? AND negocio_id = ? LIMIT 1");
                $st->execute([$pid, $id]);
                if (!$st->fetchColumn()) {
                    $ok = false; $msg = 'Ese producto no es de esta tienda.';
                } else {
                    $precio = max(0, (float)($_POST['precio'] ?? 0));
                    $unidad = trim(strip_tags((string)($_POST['unidad'] ?? '')));
                    if ($unidad === '') $unidad = 'unidad';
                    $desc = trim(strip_tags((string)($_POST['descripcion'] ?? '')));
                    db()->prepare("UPDATE directorio_servicios SET titulo = ?, precio = ?, unidad = ?, descripcion = ?,
                                          activo = ?, destacado = ? WHERE id = ? AND negocio_id = ?")
                        ->execute([mb_substr($titulo, 0, 120), $precio, mb_substr($unidad, 0, 30),
                                   ($desc !== '' ? mb_substr($desc, 0, 500) : null),
                                   (int)!empty($_POST['activo']), (int)!empty($_POST['destacado']), $pid, $id]);
                    $msg = '🛍️ Producto guardado.';
                }
            } catch (Throwable $e) {
                error_log('edt_prod: ' . $e->getMessage());
                $ok = false; $msg = '😅 No pude guardar el producto.';
            }
        }
    }

    if ($accion === 'edt_prod_ia') {
        $pid = (int)($_POST['producto_id'] ?? 0);
        $titulo = trim(strip_tags((string)($_POST['titulo'] ?? '')));
        $texto  = trim(strip_tags((string)($_POST['descripcion'] ?? '')));
        if ($titulo === '') { $ok = false; $msg = 'Primero escribe el nombre del producto.'; }
        else {
            $nuevo = editar_ficha_ia_producto($neg, $titulo, $texto, editar_ficha_fotos_producto($pid));
            if ($nuevo === '') {
                $ok = false; $msg = '😅 La IA no pudo escribir el texto de ese producto. Prueba otra vez.';
            } else {
                $msg = '🤖 La IA reescribió el producto. **Míralo y toca 💾 Guardar**.';
                $extra['descripcion'] = $nuevo;
            }
        }
    }

    if ($accion === 'edt_prod_nuevo') {
        $titulo = trim(strip_tags((string)($_POST['titulo'] ?? '')));
        if (mb_strlen($titulo) < 2) {
            $ok = false; $msg = 'Escribe el nombre del producto nuevo.';
        } else {
            try {
                $precio = max(0, (float)($_POST['precio'] ?? 0));
                $desc = trim(strip_tags((string)($_POST['descripcion'] ?? '')));
                $unidad = trim(strip_tags((string)($_POST['unidad'] ?? '')));
                if ($unidad === '') $unidad = 'unidad';
                db()->prepare("INSERT INTO directorio_servicios
                    (negocio_id, titulo, tipo_producto, descripcion, precio, unidad, imagen, destacado, activo)
                    VALUES (?,?,?,?,?,?,?,?,?)")
                    ->execute([$id, mb_substr($titulo, 0, 120), 'fisico',
                               ($desc !== '' ? mb_substr($desc, 0, 500) : null), $precio,
                               mb_substr($unidad, 0, 30), null, 0, 1]);
                $pid = (int)db()->lastInsertId();
                $msg = '🛍️ Producto creado.';
                $extra['producto_id'] = $pid;
            } catch (Throwable $e) {
                error_log('edt_prod_nuevo: ' . $e->getMessage());
                $ok = false; $msg = '😅 No pude crear el producto.';
            }
        }
    }

    if ($accion === 'edt_prod_borrar') {
        $pid = (int)($_POST['producto_id'] ?? 0);
        try {
            $st = db()->prepare("SELECT titulo FROM directorio_servicios WHERE id = ? AND negocio_id = ? LIMIT 1");
            $st->execute([$pid, $id]);
            $titulo = (string)($st->fetchColumn() ?: '');
            if ($titulo === '') {
                $ok = false; $msg = 'Ese producto no es de esta tienda.';
            } else {
                // Sus fotos también se van (el archivo y las versiones 800/300).
                $st = db()->prepare("SELECT ruta FROM directorio_producto_fotos WHERE producto_id = ?");
                $st->execute([$pid]);
                foreach ((array)$st->fetchAll(PDO::FETCH_COLUMN) as $rel) {
                    try { if (function_exists('img_borrar')) img_borrar((string)$rel); } catch (Throwable $e) {}
                }
                db()->prepare("DELETE FROM directorio_producto_fotos WHERE producto_id = ?")->execute([$pid]);
                db()->prepare("DELETE FROM directorio_servicios WHERE id = ? AND negocio_id = ?")->execute([$pid, $id]);
                $msg = '🗑️ Producto borrado: «' . $titulo . '».';
            }
        } catch (Throwable $e) {
            error_log('edt_prod_borrar: ' . $e->getMessage());
            $ok = false; $msg = '😅 No pude borrar el producto.';
        }
    }

    /* ---------------------------------------------------------------- 🖼️ LAS FOTOS */
    if ($accion === 'edt_foto_subir') {
        $n = 0;
        // 🧪 En modo prueba la sonda manda una imagen fabricada (no es una subida real del navegador).
        $op_foto = $prueba ? ['exigir_subida' => false] : [];
        if (!empty($_FILES['fotos']['name']) && is_array($_FILES['fotos']['name'])) {
            $total = min(count($_FILES['fotos']['name']), 6);
            for ($i = 0; $i < $total; $i++) {
                $f = [
                    'name' => $_FILES['fotos']['name'][$i], 'type' => $_FILES['fotos']['type'][$i] ?? '',
                    'tmp_name' => $_FILES['fotos']['tmp_name'][$i], 'error' => $_FILES['fotos']['error'][$i],
                    'size' => $_FILES['fotos']['size'][$i],
                ];
                $r = img_guardar_subida($f, 'assets/uploads/' . $id, date('Ymd') . '_' . bin2hex(random_bytes(6)), $op_foto);
                if (empty($r['ok'])) continue;
                try {
                    $st = db()->prepare("SELECT COALESCE(MAX(orden), 0) + 1 FROM directorio_fotos WHERE negocio_id = ?");
                    $st->execute([$id]);
                    $orden = (int)$st->fetchColumn();
                    db()->prepare("INSERT INTO directorio_fotos (negocio_id, ruta, descripcion, orden) VALUES (?,?,?,?)")
                        ->execute([$id, (string)$r['rel'], 'Galería', $orden]);
                    $n++;
                } catch (Throwable $e) { error_log('edt_foto_subir: ' . $e->getMessage()); }
            }
        }
        $msg = $n > 0 ? ('🖼️ ' . $n . ' foto(s) subida(s).') : '😅 No se pudo subir ninguna foto.';
        if ($n === 0) $ok = false;
        $extra['fotos'] = editar_ficha_lista_fotos($id);
    }

    if ($accion === 'edt_foto_portada') {
        $fid = (int)($_POST['foto_id'] ?? 0);
        try {
            $st = db()->prepare("SELECT COUNT(*) FROM directorio_fotos WHERE id = ? AND negocio_id = ?");
            $st->execute([$fid, $id]);
            if ((int)$st->fetchColumn() <= 0) {
                $ok = false; $msg = 'Esa foto no es de esta tienda.';
            } else {
                db()->prepare("UPDATE directorio_fotos SET orden = orden + 1 WHERE negocio_id = ?")->execute([$id]);
                db()->prepare("UPDATE directorio_fotos SET orden = 0 WHERE id = ?")->execute([$fid]);
                $msg = '⭐ Listo: esa es ahora la portada de la tienda.';
            }
        } catch (Throwable $e) {
            $ok = false; $msg = '😅 No pude cambiar la portada.';
        }
        $extra['fotos'] = editar_ficha_lista_fotos($id);
    }

    if ($accion === 'edt_foto_borrar') {
        $fid = (int)($_POST['foto_id'] ?? 0);
        try {
            $st = db()->prepare("SELECT ruta FROM directorio_fotos WHERE id = ? AND negocio_id = ? LIMIT 1");
            $st->execute([$fid, $id]);
            $rel = (string)($st->fetchColumn() ?: '');
            if ($rel === '') {
                $ok = false; $msg = 'Esa foto no es de esta tienda.';
            } else {
                db()->prepare("DELETE FROM directorio_fotos WHERE id = ? AND negocio_id = ?")->execute([$fid, $id]);
                try { img_borrar($rel); } catch (Throwable $e) {}
                $msg = '🗑️ Foto borrada.';
            }
        } catch (Throwable $e) {
            $ok = false; $msg = '😅 No pude borrar la foto.';
        }
        $extra['fotos'] = editar_ficha_lista_fotos($id);
    }

    $resultado = array_merge(['ok' => $ok, 'msg' => $msg], $extra);
    if ($prueba) return $resultado;          // 🧪 la sonda sigue en la misma petición
    if ($es_ajax) editar_ficha_json($resultado);

    // Sin JavaScript: se vuelve a la ficha con el aviso.
    flash($msg, $ok ? 'exito' : 'error');
    header('Location: ' . $volver);
    exit;
}

/** Los productos de la tienda, para el panel (todos, también los ocultos). */
function editar_ficha_lista($negocio_id, $limite = 300) {
    $out = [];
    try {
        $st = db()->prepare("SELECT s.id, s.titulo, s.precio, s.unidad, s.descripcion, s.activo, s.destacado,
                                    s.imagen,
                                    (SELECT COUNT(*) FROM directorio_producto_fotos f WHERE f.producto_id = s.id) AS nfotos
                               FROM directorio_servicios s
                              WHERE s.negocio_id = ?
                              ORDER BY s.id ASC LIMIT " . max(1, (int)$limite));
        $st->execute([(int)$negocio_id]);
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $p) {
            $out[] = [
                'id'          => (int)$p['id'],
                'titulo'      => (string)$p['titulo'],
                'precio'      => (float)$p['precio'],
                'unidad'      => (string)$p['unidad'],
                'descripcion' => (string)$p['descripcion'],
                'activo'      => (int)$p['activo'],
                'destacado'   => (int)$p['destacado'],
                'foto'        => ((string)$p['imagen'] !== '' ? img_url((string)$p['imagen'], 300) : ''),
                'nfotos'      => (int)$p['nfotos'],
            ];
        }
    } catch (Throwable $e) { error_log('editar_ficha_lista: ' . $e->getMessage()); }
    return $out;
}

/** Las fotos de la tienda (la 1.ª es la portada). */
function editar_ficha_lista_fotos($negocio_id) {
    $out = [];
    try {
        $st = db()->prepare("SELECT id, ruta FROM directorio_fotos WHERE negocio_id = ? ORDER BY orden ASC, id ASC");
        $st->execute([(int)$negocio_id]);
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $i => $f) {
            $out[] = ['id' => (int)$f['id'], 'url' => img_url((string)$f['ruta'], 300), 'portada' => ($i === 0)];
        }
    } catch (Throwable $e) {}
    return $out;
}

/* =====================================================================================
 * EL PANEL QUE SE PINTA EN LA FICHA ('' para todo el que no sea admin)
 * ===================================================================================== */

/**
 * 🛡️ EL MENÚ DEL SÚPER ADMIN EN LA FICHA.
 *
 * @param array $negocio la fila del negocio (se recarga entera aquí para tener el rubro y la zona)
 * @return string el HTML del menú y su hoja de edición, o **''** si el que mira no es admin
 */
function editar_ficha_html(array $negocio): string {
    if (!editar_ficha_puede()) return '';
    $id = (int)($negocio['id'] ?? 0);
    if ($id <= 0) return '';
    $neg = editar_ficha_negocio($id);
    if (!$neg) return '';

    // Los catálogos del formulario (rubros y zonas reales del directorio).
    $rubros = [];
    try { $rubros = obtener_categorias(); } catch (Throwable $e) { $rubros = []; }
    $distritos = [];
    try { $distritos = obtener_distritos_visibles(); } catch (Throwable $e) { $distritos = []; }

    $vende = ['fisica'     => '🏪 Tiene un local y ahí atiende',
              'ambulante'  => '🛵 Vendedor ambulante (por las calles)',
              'domicilio'  => '🏠 Lo lleva a la casa del cliente',
              'nacional'   => '🚚 Vende a todo el país',
              'mayorista'  => '📦 Vende al por mayor'];
    $horarios = ['24 horas, todos los días', 'Todos los días en las mañanas', 'Solo en las tardes',
                 'Solo en las mañanas', 'De 9 de la mañana a 9 de la noche', 'De lunes a viernes',
                 'Solo los sábados y domingos', 'Abro en varios de esos horarios'];

    $v = function ($k, $def = '') use ($neg) { return e((string)($neg[$k] ?? $def)); };
    $rubro_actual = (string)($neg['rubro_nombre'] ?? '');
    $dist_actual  = (int)($neg['distrito_id'] ?? 0);
    $estado       = (string)($neg['estado'] ?? 'activo');
    $nombre_actual = (string)($neg['nombre'] ?? '');
    $desc_actual  = (string)($neg['descripcion'] ?? '');

    ob_start(); ?>
<style>
/* 🛡️ EL MENÚ DEL SÚPER ADMIN EN LA FICHA (2026-09-18) — solo lo ve el administrador. */
.edt{margin:12px 0 4px;padding:12px 14px;background:#0b1220;border:1px solid #f0b429;border-radius:12px;color:#fff}
.edt__tit{font-size:14px;font-weight:800;color:#f7d774;margin-bottom:9px}
.edt__tit span{font-weight:600;color:#cbd5e1}
.edt__menu{display:flex;flex-wrap:wrap;gap:8px}
.edt__b{flex:1 1 auto;display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:44px;
  padding:9px 12px;border:1px solid rgba(255,255,255,.22);border-radius:10px;background:rgba(255,255,255,.08);
  color:#fff;font-size:15px;font-weight:700;cursor:pointer}
.edt__b:hover{background:rgba(255,255,255,.18)}
.edt__b--oro{background:linear-gradient(180deg,#f5cd5b,#d9a409);color:#241a00;border-color:#f0b429}

.edt-sheet{position:fixed;inset:0;z-index:120;display:flex;align-items:flex-end;justify-content:center;
  background:rgba(8,12,22,.62);backdrop-filter:blur(2px)}
.edt-sheet[hidden]{display:none}
.edt-sheet__caja{width:min(720px,100%);max-height:92vh;display:flex;flex-direction:column;
  background:#fff;border-radius:16px 16px 0 0;overflow:hidden;box-shadow:0 -10px 40px rgba(0,0,0,.35)}
.edt-sheet__cab{display:flex;align-items:center;gap:10px;padding:12px 14px;background:#0b1220;color:#fff}
.edt-sheet__cab strong{flex:1;font-size:16px}
.edt-x{border:0;background:rgba(255,255,255,.14);color:#fff;width:38px;height:38px;border-radius:999px;
  font-size:18px;cursor:pointer}
/* 📱 Las pestañas SE ENVUELVEN (en el celular las 4 tienen que verse: con scroll horizontal la de
   Fotos quedaba escondida y el jefe no la encontraba). */
.edt-tabs{display:flex;flex-wrap:wrap;gap:6px;padding:10px 12px 8px}
.edt-tabs button{flex:0 0 auto;min-height:42px;padding:8px 12px;border:1px solid #cbd5e1;
  border-radius:999px;background:#f1f5f9;color:#0f172a;font-size:15px;font-weight:700;cursor:pointer}
.edt-tabs button.is-on{background:#0b1220;color:#fff;border-color:#0b1220}
.edt-cuerpo{flex:1;overflow-y:auto;padding:14px;border-top:3px solid #0b1220}
.edt-p[hidden]{display:none}
.edt-aviso{padding:0 14px 14px;font-size:14.5px;color:#0f172a}
.edt-aviso b{color:#0b1220}
.edt-aviso.is-error{color:#b91c1c}
.edt-campo{margin-bottom:11px}
.edt-campo label{display:block;font-size:14px;font-weight:700;color:#334155;margin-bottom:4px}
.edt-inp{width:100%;box-sizing:border-box;min-height:46px;padding:10px 12px;border:1px solid #cbd5e1;
  border-radius:10px;font-size:16px;font-family:inherit;color:#0f172a;background:#fff}
textarea.edt-inp{min-height:130px;line-height:1.45;resize:vertical}
.edt-fila{display:flex;gap:9px;flex-wrap:wrap}
.edt-fila>*{flex:1 1 150px;min-width:0}
.edt-b{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:46px;padding:10px 14px;
  border:0;border-radius:10px;font-size:15.5px;font-weight:800;cursor:pointer;font-family:inherit}
.edt-b--ia{background:linear-gradient(180deg,#7fe3d4,#2fae9b);color:#06312b}
.edt-b--ok{background:linear-gradient(180deg,#34d399,#0f9d63);color:#04291b}
.edt-b--gris{background:#e2e8f0;color:#0f172a}
.edt-b--rojo{background:#fee2e2;color:#b91c1c}
.edt-b--azul{background:#dbeafe;color:#1e40af}
.edt-b:disabled{opacity:.5;cursor:default}
.edt-barra{display:flex;gap:8px;flex-wrap:wrap;margin-top:6px}
.edt-nota{font-size:13px;color:#64748b;margin:6px 0 12px}
.edt-aviso-ia{margin-top:6px;font-size:13.5px;font-weight:700;color:#0f766e}
.edt-prod{border:1px solid #e2e8f0;border-left:4px solid #0b1220;border-radius:12px;padding:11px 12px;margin-bottom:11px}
.edt-prod__cab{display:flex;align-items:center;gap:9px;margin-bottom:8px}
.edt-prod__cab img{width:44px;height:44px;object-fit:cover;border-radius:8px;background:#f1f5f9}
.edt-prod__cab b{flex:1;font-size:15px;color:#0f172a;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.edt-prod.is-off{opacity:.62}
.edt-chip{font-size:11.5px;font-weight:800;padding:3px 8px;border-radius:999px;background:#f1f5f9;color:#475569}
.edt-chip--on{background:#dcfce7;color:#166534}
.edt-chip--off{background:#fee2e2;color:#991b1b}
.edt-fotos{display:grid;grid-template-columns:repeat(auto-fill,minmax(104px,1fr));gap:9px}
.edt-foto{position:relative;border:1px solid #e2e8f0;border-radius:10px;overflow:hidden;background:#f8fafc}
.edt-foto img{display:block;width:100%;height:104px;object-fit:cover}
.edt-foto__p{position:absolute;top:5px;left:5px;background:#f0b429;color:#241a00;font-size:11px;font-weight:800;
  padding:2px 7px;border-radius:999px}
.edt-foto__b{display:flex;gap:5px;padding:5px}
.edt-foto__b button{flex:1;min-height:36px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;
  font-size:14px;cursor:pointer}
.edt-vacio{font-size:14.5px;color:#64748b;padding:8px 0}
@media (max-width:640px){
  .edt__b{font-size:14.5px;padding:9px 10px}
  .edt-cuerpo{padding:12px}
}
</style>

<div class="edt" id="edt-caja" data-neg="<?= (int)$id ?>">
    <div class="edt__tit">🛡️ Súper Admin · Editar esta tienda <span>(lo ves solo tú)</span></div>
    <div class="edt__menu">
        <button type="button" class="edt__b edt__b--oro" data-edt-tab="datos">🏪 Datos</button>
        <button type="button" class="edt__b" data-edt-tab="desc">📝 Descripción <b>🤖</b></button>
        <button type="button" class="edt__b" data-edt-tab="prod">🛍️ Productos <b>🤖</b></button>
        <button type="button" class="edt__b" data-edt-tab="fotos">🖼️ Fotos</button>
    </div>
</div>

<?php /* El formulario del CSRF: el panel manda TODAS sus acciones con este token (una `fetch` por
         acción). Va en el HTML —y no armado por JavaScript— para que el token viaje siempre igual. */ ?>
<form method="post" id="edt-form" style="display:none">
    <?= csrf_campo() ?>
    <input type="hidden" name="accion" id="edt-form-accion" value="">
</form>

<div class="edt-sheet" id="edt-sheet" hidden>
  <div class="edt-sheet__caja">
    <div class="edt-sheet__cab">
      <strong id="edt-titulo">✏️ <?= e($nombre_actual) ?></strong>
      <button type="button" class="edt-x" id="edt-cerrar" aria-label="Cerrar">✕</button>
    </div>
    <div class="edt-tabs">
      <button type="button" data-edt-panel="datos">🏪 Datos</button>
      <button type="button" data-edt-panel="desc">📝 Descripción</button>
      <button type="button" data-edt-panel="prod">🛍️ Productos</button>
      <button type="button" data-edt-panel="fotos">🖼️ Fotos</button>
    </div>

    <div class="edt-cuerpo">
      <!-- ============ 🏪 DATOS ============ -->
      <section class="edt-p" id="edt-p-datos">
        <div class="edt-campo"><label>Nombre de la tienda</label>
          <input class="edt-inp" id="edt-nombre" value="<?= e($nombre_actual) ?>" maxlength="180"></div>
        <div class="edt-fila">
          <div class="edt-campo"><label>Rubro</label>
            <input class="edt-inp" id="edt-rubro" list="edt-rubros" value="<?= e($rubro_actual) ?>" placeholder="ferretería, bodega…">
            <datalist id="edt-rubros">
              <?php foreach ($rubros as $r): ?><option value="<?= e((string)$r['nombre']) ?>"></option><?php endforeach; ?>
            </datalist></div>
          <div class="edt-campo"><label>Zona</label>
            <select class="edt-inp" id="edt-distrito">
              <option value="0">— sin cambiar —</option>
              <?php foreach ($distritos as $dd): ?>
                <option value="<?= (int)$dd['id'] ?>"<?= ((int)$dd['id'] === $dist_actual ? ' selected' : '') ?>><?= e((string)$dd['nombre']) ?></option>
              <?php endforeach; ?>
            </select></div>
        </div>
        <div class="edt-campo"><label>Cómo atiende</label>
          <select class="edt-inp" id="edt-vendedor">
            <?php foreach ($vende as $k => $txt): ?>
              <option value="<?= e($k) ?>"<?= ((string)($neg['ubicacion_tipo'] ?? 'fisica') === $k ? ' selected' : '') ?>><?= e($txt) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="edt-campo"><label>Dirección o referencia</label>
          <input class="edt-inp" id="edt-direccion" value="<?= $v('direccion') ?>" maxlength="160" placeholder="Av. Pardo 123, frente al mercado"></div>
        <div class="edt-campo"><label>Horario</label>
          <input class="edt-inp" id="edt-horario" list="edt-horarios" value="<?= $v('horario') ?>" maxlength="60">
          <datalist id="edt-horarios">
            <?php foreach ($horarios as $h): ?><option value="<?= e($h) ?>"></option><?php endforeach; ?>
          </datalist></div>
        <div class="edt-fila">
          <div class="edt-campo"><label>WhatsApp (pedidos)</label>
            <input class="edt-inp" id="edt-whatsapp" inputmode="numeric" value="<?= $v('whatsapp') ?>" placeholder="943112233"></div>
          <div class="edt-campo"><label>Teléfono (llamadas)</label>
            <input class="edt-inp" id="edt-telefono" inputmode="numeric" value="<?= $v('telefono') ?>" placeholder="043 123456"></div>
        </div>
        <div class="edt-campo"><label>Facebook</label>
          <input class="edt-inp" id="edt-facebook" value="<?= $v('facebook') ?>" placeholder="enlace o nombre de la página"></div>
        <div class="edt-campo"><label>Instagram</label>
          <input class="edt-inp" id="edt-instagram" value="<?= $v('instagram') ?>" placeholder="@usuario o enlace"></div>
        <div class="edt-campo"><label>TikTok</label>
          <input class="edt-inp" id="edt-tiktok" value="<?= $v('tiktok') ?>" placeholder="@usuario o enlace"></div>
        <div class="edt-campo"><label>¿La tienda se ve?</label>
          <select class="edt-inp" id="edt-estado">
            <option value="activo"<?= ($estado === 'activo' ? ' selected' : '') ?>>✅ Sí, se ve en el sitio</option>
            <option value="inactivo"<?= ($estado !== 'activo' ? ' selected' : '') ?>>🙈 No, está oculta</option>
          </select></div>
        <div class="edt-barra">
          <button type="button" class="edt-b edt-b--ok" id="edt-guardar-datos">💾 Guardar los datos</button>
          <button type="button" class="edt-b edt-b--gris" id="edt-recargar" hidden>🔄 Recargar la ficha para verlo</button>
        </div>
        <p class="edt-nota">El <b>enlace de la tienda no cambia</b> nunca: si corriges el nombre, la dirección web sigue siendo la misma.</p>
      </section>

      <!-- ============ 📝 DESCRIPCIÓN ============ -->
      <section class="edt-p" id="edt-p-desc" hidden>
        <div class="edt-campo"><label>El texto de la ficha</label>
          <textarea class="edt-inp" id="edt-desc" rows="10"><?= e($desc_actual) ?></textarea></div>
        <p class="nota edt-nota">Puedes escribir <b>HTML simple</b> (h3, p, ul, li, strong, br) o dejar que lo escriba la IA.</p>
        <div class="edt-barra">
          <button type="button" class="edt-b edt-b--ia" id="edt-desc-ia">🤖 IA: reescribir este texto</button>
          <button type="button" class="edt-b edt-b--ok" id="edt-desc-guardar">💾 Guardar</button>
          <button type="button" class="edt-b edt-b--gris" id="edt-desc-ver">👁️ Ver cómo queda</button>
        </div>
        <div id="edt-desc-preview" hidden
             style="margin-top:10px;padding:12px;border:1px dashed #94a3b8;border-radius:12px;background:#fff"></div>
        <div class="edt-aviso-ia" id="edt-desc-aviso"></div>
      </section>

      <!-- ============ 🛍️ PRODUCTOS ============ -->
      <section class="edt-p" id="edt-p-prod" hidden>
        <div class="edt-campo"><label>Buscar un producto</label>
          <input class="edt-inp" id="edt-prod-buscar" placeholder="Escribe parte del nombre…"></div>
        <div class="edt-barra" style="margin-bottom:10px">
          <button type="button" class="edt-b edt-b--azul" id="edt-prod-nuevo">➕ Producto nuevo</button>
          <button type="button" class="edt-b edt-b--gris" id="edt-prod-refrescar">🔄 Traer la lista</button>
        </div>
        <div id="edt-prod-lista"><p class="edt-vacio">Toca <b>🔄 Traer la lista</b> para ver los productos.</p></div>
      </section>

      <!-- ============ 🖼️ FOTOS ============ -->
      <section class="edt-p" id="edt-p-fotos" hidden>
        <div class="edt-barra" style="margin-bottom:10px">
          <button type="button" class="edt-b edt-b--azul" id="edt-foto-elegir">📤 Subir fotos</button>
          <button type="button" class="edt-b edt-b--gris" id="edt-foto-refrescar">🔄 Traer las fotos</button>
        </div>
        <input type="file" id="edt-foto-input" accept="image/*" multiple hidden>
        <div class="edt-fotos" id="edt-fotos"><p class="edt-vacio">Toca <b>🔄 Traer las fotos</b>.</p></div>
        <p class="edt-nota">La <b>primera foto</b> es la portada de la tienda (la que se ve al compartir el enlace). Las fotos se comprimen solas a WebP antes de subir.</p>
      </section>
    </div>

    <div class="edt-aviso" id="edt-aviso"></div>
  </div>
</div>

<script>
/* 🛡️ EL MENÚ DEL SÚPER ADMIN EN LA FICHA (2026-09-18).
   Todo viaja por `fetch` al MISMO URL de la ficha (`accion=edt_…`), como el botón 📨 de invitación:
   la puerta (admin + CSRF) la comprueba el servidor en cada acción. */
(function () {
  var caja = document.getElementById('edt-caja');
  if (!caja) return;
  var negId  = parseInt(caja.getAttribute('data-neg'), 10) || 0;
  var hoja   = document.getElementById('edt-sheet');
  var aviso  = document.getElementById('edt-aviso');
  var form   = document.getElementById('edt-form');       // el CSRF (y la acción) viajan aquí
  if (!form) return;
  var url    = window.location.pathname + window.location.search;

  function decir(txt, esError) {
    if (!aviso) return;
    aviso.className = 'edt-aviso' + (esError ? ' is-error' : '');
    aviso.innerHTML = txt || '';
  }

  function cuerpo(accion, datos) {
    var fd = new FormData(form);                          // trae el CSRF de una
    fd.set('accion', accion);
    if (datos) { Object.keys(datos).forEach(function (k) { fd.set(k, datos[k]); }); }
    return fd;
  }

  function pedir(accion, datos, cb) {
    decir('⏳ Trabajando…');
    fetch(url, { method: 'POST', body: cuerpo(accion, datos), credentials: 'same-origin',
                 headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        if (!d || !d.ok) { decir('⚠️ ' + ((d && d.msg) || 'No se pudo.'), true); if (cb) cb(null); return; }
        decir(d.msg || 'Listo.');
        if (cb) cb(d);
      })
      .catch(function () {
        decir('⚠️ Se cortó el internet. Revisa tu señal y toca otra vez.', true);
        if (cb) cb(null);
      });
  }

  function valor(id) { var e = document.getElementById(id); return e ? e.value : ''; }
  function poner(id, v) { var e = document.getElementById(id); if (e) e.value = v; }

  /* ---------------------------------------------------------------- abrir/cerrar y pestañas */
  function abrir(panel) {
    hoja.hidden = false;
    document.body.style.overflow = 'hidden';
    elegir(panel || 'datos');
  }
  function cerrar() { hoja.hidden = true; document.body.style.overflow = ''; }
  function elegir(panel) {
    ['datos', 'desc', 'prod', 'fotos'].forEach(function (p) {
      var sec = document.getElementById('edt-p-' + p);
      if (sec) sec.hidden = (p !== panel);
      var bt = document.querySelector('.edt-tabs [data-edt-panel=' + p + ']');
      if (bt) bt.className = (p === panel) ? 'is-on' : '';
    });
    if (panel === 'prod' && !document.querySelector('#edt-prod-lista .edt-prod')) traerProductos();
    if (panel === 'fotos' && !document.querySelector('#edt-fotos .edt-foto')) traerFotos();
  }

  Array.prototype.forEach.call(document.querySelectorAll('[data-edt-tab]'), function (b) {
    b.addEventListener('click', function () { abrir(b.getAttribute('data-edt-tab')); });
  });
  Array.prototype.forEach.call(document.querySelectorAll('[data-edt-panel]'), function (b) {
    b.addEventListener('click', function () { elegir(b.getAttribute('data-edt-panel')); });
  });
  document.getElementById('edt-cerrar').addEventListener('click', cerrar);
  hoja.addEventListener('click', function (ev) { if (ev.target === hoja) cerrar(); });
  document.addEventListener('keydown', function (ev) { if (ev.key === 'Escape' && !hoja.hidden) cerrar(); });

  /* ---------------------------------------------------------------- 🏪 DATOS */
  document.getElementById('edt-guardar-datos').addEventListener('click', function () {
    pedir('edt_datos', {
      nombre: valor('edt-nombre'), rubro: valor('edt-rubro'), distrito_id: valor('edt-distrito'),
      vendedor: valor('edt-vendedor'), direccion: valor('edt-direccion'), horario: valor('edt-horario'),
      whatsapp: valor('edt-whatsapp'), telefono: valor('edt-telefono'),
      facebook: valor('edt-facebook'), instagram: valor('edt-instagram'), tiktok: valor('edt-tiktok'),
      estado: valor('edt-estado')
    }, function () {
      var rec = document.getElementById('edt-recargar');
      if (rec) rec.hidden = false;
    });
  });
  document.getElementById('edt-recargar').addEventListener('click', function () { window.location.reload(); });

  /* ---------------------------------------------------------------- 📝 DESCRIPCIÓN (+ IA) */
  document.getElementById('edt-desc-ia').addEventListener('click', function () {
    var b = this;
    b.disabled = true;
    var av = document.getElementById('edt-desc-aviso');
    av.textContent = '🤖 La IA está escribiendo… (5 a 15 segundos)';
    pedir('edt_desc_ia', {}, function (d) {
      b.disabled = false;
      if (!d) { av.textContent = ''; return; }
      if (d.descripcion) { poner('edt-desc', d.descripcion); av.textContent = '✅ Reescrito: revísalo y toca 💾 Guardar.'; }
    });
  });
  document.getElementById('edt-desc-guardar').addEventListener('click', function () {
    pedir('edt_desc', { descripcion: valor('edt-desc') }, function (d) {
      if (d) document.getElementById('edt-desc-aviso').textContent = '✅ Guardado en la ficha.';
    });
  });
  // 👁️ Ver cómo queda el texto en la ficha (con sus colores y sus botones) sin salir del panel.
  document.getElementById('edt-desc-ver').addEventListener('click', function () {
    var cajaP = document.getElementById('edt-desc-preview');
    if (!cajaP.hidden) { cajaP.hidden = true; this.textContent = '👁️ Ver cómo queda'; return; }
    var html = valor('edt-desc')
      .replace(/<script[\s\S]*?<\/script>/gi, '')
      .replace(/\son\w+\s*=\s*("[^"]*"|'[^']*')/gi, '');
    cajaP.innerHTML = html || '<i>(vacío)</i>';
    cajaP.hidden = false;
    this.textContent = '✏️ Seguir editando';
  });

  /* ---------------------------------------------------------------- 🛍️ PRODUCTOS */
  function traerProductos() {
    pedir('edt_lista', {}, function (d) {
      if (!d || !d.productos) return;
      pintarProductos(d.productos);
    });
  }
  function esc(s) {
    return String(s == null ? '' : s).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
  }
  function filaProd(p) {
    var div = document.createElement('div');
    div.className = 'edt-prod' + (p.activo ? '' : ' is-off');
    div.setAttribute('data-id', p.id);
    div.setAttribute('data-nombre', String(p.titulo).toLowerCase());
    div.innerHTML =
      '<div class="edt-prod__cab">' +
        (p.foto ? '<img src="' + esc(p.foto) + '" alt="">' : '<span class="edt-chip">sin foto</span>') +
        '<b>' + esc(p.titulo) + '</b>' +
        '<span class="edt-chip ' + (p.activo ? 'edt-chip--on' : 'edt-chip--off') + '">' + (p.activo ? 'visible' : 'oculto') + '</span>' +
      '</div>' +
      '<div class="edt-fila">' +
        '<div class="edt-campo"><label>Nombre</label><input class="edt-inp" data-c="titulo" value="' + esc(p.titulo) + '"></div>' +
        '<div class="edt-campo"><label>Precio (S/)</label><input class="edt-inp" data-c="precio" inputmode="decimal" value="' + (Number(p.precio) || 0) + '"></div>' +
        '<div class="edt-campo"><label>Unidad</label><input class="edt-inp" data-c="unidad" value="' + esc(p.unidad || 'unidad') + '"></div>' +
      '</div>' +
      '<div class="edt-campo"><label>Texto del producto</label><textarea class="edt-inp" data-c="descripcion" rows="3">' + esc(p.descripcion || '') + '</textarea></div>' +
      '<div class="edt-barra">' +
        '<button type="button" class="edt-b edt-b--ia" data-h="ia">🤖 IA: reescribir</button>' +
        '<button type="button" class="edt-b edt-b--ok" data-h="guardar">💾 Guardar</button>' +
        '<button type="button" class="edt-b edt-b--gris" data-h="visible">' + (p.activo ? '🙈 Ocultar' : '👁️ Mostrar') + '</button>' +
        '<button type="button" class="edt-b edt-b--rojo" data-h="borrar">🗑️ Borrar</button>' +
      '</div>';
    Array.prototype.forEach.call(div.querySelectorAll('[data-h]'), function (b) {
      b.addEventListener('click', function () { accionProd(div, b.getAttribute('data-h')); });
    });
    return div;
  }
  function campo(div, c) { var e = div.querySelector('[data-c=' + c + ']'); return e ? e.value : ''; }
  function accionProd(div, que) {
    var pid = parseInt(div.getAttribute('data-id'), 10) || 0;
    var datos = { producto_id: pid, titulo: campo(div, 'titulo'), precio: campo(div, 'precio'),
                  unidad: campo(div, 'unidad'), descripcion: campo(div, 'descripcion') };
    if (que === 'ia') {
      var b = div.querySelector('[data-h=ia]');
      b.disabled = true; b.textContent = '🤖 escribiendo…';
      pedir('edt_prod_ia', datos, function (d) {
        b.disabled = false; b.textContent = '🤖 IA: reescribir';
        if (d && d.descripcion) {
          var t = div.querySelector('[data-c=descripcion]');
          t.value = d.descripcion;
          t.style.borderColor = '#2fae9b';
        }
      });
      return;
    }
    if (que === 'borrar') {
      if (!window.confirm('¿Borrar el producto «' + campo(div, 'titulo') + '»? No se puede deshacer.')) return;
      pedir('edt_prod_borrar', { producto_id: pid }, function (d) { if (d) div.remove(); });
      return;
    }
    if (que === 'visible') {
      var activo = div.className.indexOf('is-off') >= 0 ? 1 : 0;   // si está oculto, se muestra
      datos.activo = activo;
      pedir('edt_prod', datos, function (d) {
        if (!d) return;
        div.className = 'edt-prod' + (activo ? '' : ' is-off');
        var ch = div.querySelector('.edt-chip--on, .edt-chip--off');
        ch.className = 'edt-chip ' + (activo ? 'edt-chip--on' : 'edt-chip--off');
        ch.textContent = activo ? 'visible' : 'oculto';
        var bb = div.querySelector('[data-h=visible]');
        bb.textContent = activo ? '🙈 Ocultar' : '👁️ Mostrar';
      });
      return;
    }
    // guardar
    var conTodo = {};
    ['producto_id', 'titulo', 'precio', 'unidad', 'descripcion'].forEach(function (k) { conTodo[k] = datos[k]; });
    conTodo.activo = (div.className.indexOf('is-off') < 0) ? 1 : 0;
    conTodo.destacado = 0;
    pedir('edt_prod', conTodo, null);
  }
  function pintarProductos(lista) {
    var caja2 = document.getElementById('edt-prod-lista');
    caja2.innerHTML = '';
    if (!lista.length) { caja2.innerHTML = '<p class="edt-vacio">Esta tienda todavía no tiene productos.</p>'; return; }
    lista.forEach(function (p) { caja2.appendChild(filaProd(p)); });
  }
  document.getElementById('edt-prod-refrescar').addEventListener('click', traerProductos);
  document.getElementById('edt-prod-buscar').addEventListener('input', function () {
    var q = this.value.trim().toLowerCase();
    Array.prototype.forEach.call(document.querySelectorAll('#edt-prod-lista .edt-prod'), function (d) {
      var n = d.getAttribute('data-nombre') || '';
      d.style.display = (q === '' || n.indexOf(q) >= 0) ? '' : 'none';
    });
  });
  document.getElementById('edt-prod-nuevo').addEventListener('click', function () {
    var caja2 = document.getElementById('edt-prod-lista');
    if (caja2.querySelector('.edt-prod--nuevo')) { caja2.querySelector('.edt-prod--nuevo').scrollIntoView(); return; }
    var div = document.createElement('div');
    div.className = 'edt-prod edt-prod--nuevo';
    div.setAttribute('data-id', '0');
    div.innerHTML =
      '<div class="edt-prod__cab"><b>➕ Producto nuevo</b></div>' +
      '<div class="edt-fila">' +
        '<div class="edt-campo"><label>Nombre</label><input class="edt-inp" data-c="titulo" placeholder="Ej: Aceite 20W-50 1L"></div>' +
        '<div class="edt-campo"><label>Precio (S/)</label><input class="edt-inp" data-c="precio" inputmode="decimal" value="0"></div>' +
        '<div class="edt-campo"><label>Unidad</label><input class="edt-inp" data-c="unidad" value="unidad"></div>' +
      '</div>' +
      '<div class="edt-campo"><label>Texto del producto</label><textarea class="edt-inp" data-c="descripcion" rows="3"></textarea></div>' +
      '<div class="edt-barra">' +
        '<button type="button" class="edt-b edt-b--ia" data-h="ia">🤖 IA: escribir el texto</button>' +
        '<button type="button" class="edt-b edt-b--ok" data-h="crear">💾 Crear</button>' +
      '</div>';
    div.querySelector('[data-h=ia]').addEventListener('click', function () {
      var b = this;
      b.disabled = true; b.textContent = '🤖 escribiendo…';
      pedir('edt_prod_ia', { producto_id: 0, titulo: campo(div, 'titulo'), descripcion: campo(div, 'descripcion') }, function (d) {
        b.disabled = false; b.textContent = '🤖 IA: escribir el texto';
        if (d && d.descripcion) div.querySelector('[data-c=descripcion]').value = d.descripcion;
      });
    });
    div.querySelector('[data-h=crear]').addEventListener('click', function () {
      pedir('edt_prod_nuevo', { titulo: campo(div, 'titulo'), precio: campo(div, 'precio'),
                                unidad: campo(div, 'unidad'), descripcion: campo(div, 'descripcion') },
        function (d) { if (d) traerProductos(); });
    });
    caja2.insertBefore(div, caja2.firstChild);
    div.scrollIntoView();
  });

  /* ---------------------------------------------------------------- 🖼️ FOTOS */
  function traerFotos() {
    pedir('edt_fotos', {}, function (d) { if (d && d.fotos) pintarFotos(d.fotos); });
  }
  function pintarFotos(lista) {
    var caja2 = document.getElementById('edt-fotos');
    caja2.innerHTML = '';
    if (!lista.length) { caja2.innerHTML = '<p class="edt-vacio">Esta tienda no tiene fotos todavía.</p>'; return; }
    lista.forEach(function (f) {
      var div = document.createElement('div');
      div.className = 'edt-foto';
      div.innerHTML = (f.portada ? '<span class="edt-foto__p">PORTADA</span>' : '') +
        '<img src="' + esc(f.url) + '" alt="">' +
        '<div class="edt-foto__b">' +
          '<button type="button" data-h="portada" title="Poner de portada">⭐</button>' +
          '<button type="button" data-h="borrar" title="Borrar">🗑️</button>' +
        '</div>';
      div.querySelector('[data-h=portada]').addEventListener('click', function () {
        pedir('edt_foto_portada', { foto_id: f.id }, function (d) { if (d && d.fotos) pintarFotos(d.fotos); });
      });
      div.querySelector('[data-h=borrar]').addEventListener('click', function () {
        if (!window.confirm('¿Borrar esta foto de la tienda?')) return;
        pedir('edt_foto_borrar', { foto_id: f.id }, function (d) { if (d && d.fotos) pintarFotos(d.fotos); });
      });
      caja2.appendChild(div);
    });
  }
  document.getElementById('edt-foto-refrescar').addEventListener('click', traerFotos);
  document.getElementById('edt-foto-elegir').addEventListener('click', function () {
    document.getElementById('edt-foto-input').click();
  });
  document.getElementById('edt-foto-input').addEventListener('change', function () {
    if (!this.files || !this.files.length) return;
    var fd = cuerpo('edt_foto_subir');
    Array.prototype.forEach.call(this.files, function (f) { fd.append('fotos[]', f, f.name); });
    var self = this;
    decir('⏳ Subiendo las fotos…');
    fetch(url, { method: 'POST', body: fd, credentials: 'same-origin',
                 headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        decir((d && d.msg) || 'Listo.', !(d && d.ok));
        if (d && d.fotos) pintarFotos(d.fotos);
        self.value = '';
      })
      .catch(function () { decir('⚠️ No se pudieron subir las fotos.', true); self.value = ''; });
  });
})();
</script>
<?php
    return (string)ob_get_clean();
}
