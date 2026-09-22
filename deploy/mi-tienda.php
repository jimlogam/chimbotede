<?php
/**
 * mi-tienda.php — ✏️ EL DUEÑO EDITA SU TIENDA (control total, siempre con su contraseña)
 * ==========================================================================================
 * 🔴 ORDEN DEL JEFE (2026-09-16, textual): *«si el dueño tiene control total de su tienda para editar,
 * **siempre debe poner su contraseña**»*.
 *
 * Hasta hoy el dueño solo podía tocar sus **productos** (`productos.php`): el nombre, el rubro, la
 * zona, el WhatsApp, el horario, las redes, las fotos y el texto de la tienda **solo los cambiaba un
 * administrador** (`editatiendas.php`) o El maestro 🛠️ en la conversación donde la creó. Esta página le
 * da **control total** de su ficha, con tres reglas:
 *
 *   1. 🔐 **SIEMPRE la contraseña**: TODAS las acciones (guardar, subir o borrar fotos, poner portada,
 *      reescribir el texto con la IA, ocultar o mostrar la tienda) se mandan en el **mismo formulario**
 *      y ese formulario **exige la contraseña de su cuenta** — en el navegador (`required`) y, sobre
 *      todo, en el servidor (`contrasena_dueno_error()`, con `password_verify()`). Sin contraseña
 *      correcta **no se escribe nada**.
 *   2. 🏪 **Solo su tienda**: se comprueba en la base que la ficha es suya (`dueno_id` = la sesión).
 *      Un administrador puede entrar a cualquiera (es su trabajo); un dueño, solo a las suyas.
 *   3. 🔗 **El enlace NO cambia**: si corrige el nombre, se guarda el nombre nuevo pero se conserva el
 *      `slug`, para no romper la dirección que ya compartió con sus clientes.
 *
 * Qué se puede editar: nombre · rubro principal · cómo atiende · distrito · dirección · WhatsApp ·
 * teléfono · horario · Facebook/Instagram/TikTok · las fotos (subir, borrar, poner portada) · el texto
 * de la ficha (a mano o **reescrito por la IA**) · y ocultar/mostrar la tienda.
 * Los productos siguen en `productos.php?n=<id>` (con su enlace bien visible aquí arriba).
 *
 * URL: `/mi-tienda?n=<id>` (regla en el `.htaccess`) o `mi-tienda.php?n=<id>`.
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/contrasena_dueno.php';
require_once __DIR__ . '/includes/tienda_ia.php';      // el copywriting de la IA (tienda_ia_ia_descripcion)

requiere_login();
$usuario  = usuario_actual();
$uid      = (int)$usuario['id'];
$es_admin = es_admin();
$nid      = (int)($_GET['n'] ?? 0);

/** La tienda, con su rubro y su distrito (y solo si es suya). */
function mt_tienda($nid, $uid, $es_admin) {
    try {
        $st = db()->prepare("SELECT n.*, c.nombre AS rubro_nombre, d.nombre AS distrito_nombre
                               FROM directorio_negocios n
                               LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                               LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
                              WHERE n.id = ? LIMIT 1");
        $st->execute([(int)$nid]);
        $n = $st->fetch(PDO::FETCH_ASSOC);
    } catch (Throwable $e) { return null; }
    if (!$n) return null;
    if (!$es_admin && (int)$n['dueno_id'] !== (int)$uid) return null;
    return $n;
}

$negocio = mt_tienda($nid, $uid, $es_admin);
if (!$negocio) {
    flash('No encontré esa tienda entre las tuyas 🤔', 'error');
    redirect('panel.php#mis-negocios');
}

$fotos = [];
try {
    $st = db()->prepare("SELECT id, ruta FROM directorio_fotos WHERE negocio_id = ? ORDER BY orden ASC, id ASC");
    $st->execute([$nid]);
    $fotos = $st->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) { $fotos = []; }

/* ==================================================================================================
 * 2) LAS ACCIONES (todas con la MISMA puerta: la contraseña del dueño)
 * =============================================================================================== */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    csrf_verificar();

    $accion = (string)($_POST['accion'] ?? 'guardar_tienda');
    $pass   = (string)($_POST['password'] ?? '');
    $volver = 'mi-tienda.php?n=' . $nid;

    // 🔐 EL CANDADO ÚNICO: sin la contraseña correcta no se toca NADA (ni un campo, ni una foto).
    $que = [
        'guardar_tienda' => 'guardar los cambios de tu tienda',
        'foto_subir'     => 'subir fotos',
        'foto_borrar'    => 'borrar esa foto',
        'foto_portada'   => 'cambiar tu portada',
        'copy_ia'        => 'reescribir el texto con la IA',
    ][$accion] ?? 'cambiar tu tienda';
    $error = contrasena_dueno_error($pass, $uid, $que);
    if ($error !== '') {
        flash($error, 'error');
        redirect($volver);
    }

    /* ---------- a) Guardar los datos de la tienda ---------- */
    if ($accion === 'guardar_tienda') {
        $campos = [];
        $par    = [];

        $nombre = trim(strip_tags((string)($_POST['nombre'] ?? '')));
        if (mb_strlen($nombre) >= 2 && mb_strlen($nombre) <= 180) {
            $campos[] = 'nombre = ?';
            $par[]    = $nombre;                 // ⚠️ el `slug` NO se toca: su enlace sigue siendo el mismo
        }

        $rubro = (int)($_POST['categoria_id'] ?? 0);
        if ($rubro > 0) {
            try {
                $st = db()->prepare("SELECT COUNT(*) FROM directorio_categorias WHERE id = ? AND activo = 1");
                $st->execute([$rubro]);
                if ((int)$st->fetchColumn() > 0) { $campos[] = 'categoria_id = ?'; $par[] = $rubro; }
            } catch (Throwable $e) {}
        }

        // 🌎 TU ZONA: un distrito concreto **o «🌎 Todos los distritos»** (pedido del jefe, 2026-09-16).
        // «Todos» se guarda como `distrito_id = NULL` (toda la provincia) **y** como cobertura de todos
        // los distritos visibles marcada con `es_todos = 1` (ver `cobertura_todos_marcar`): así la tienda
        // sale en la búsqueda de CUALQUIER distrito y el dueño puede volver atrás cuando quiera.
        $zona_cob = null;                       // null = no tocar la cobertura · 'todos' · id del distrito
        $zona_post = $_POST['distrito_id'] ?? null;
        if ($zona_post !== null) {
            $zona_txt = trim((string)$zona_post);
            if ($zona_txt === 'todos') {
                $campos[] = 'distrito_id = ?';
                $par[]    = null;
                $zona_cob = 'todos';
            } elseif ((int)$zona_txt > 0) {
                $campos[] = 'distrito_id = ?';
                $par[]    = (int)$zona_txt;
                $zona_cob = (int)$zona_txt;
            }
        }

        $vende = (string)($_POST['ubicacion_tipo'] ?? '');
        if (in_array($vende, ['fisica', 'ambulante', 'domicilio', 'nacional', 'mayorista'], true)) {
            $campos[] = 'ubicacion_tipo = ?';
            $par[]    = $vende;
            // 🚚 «Lo llevo a su casa» = delivery; 🛵 ambulante = recojo. (Mismos campos que el alta.)
            $campos[] = 'delivery = ?'; $par[] = ($vende === 'domicilio' ? 1 : 0);
            $campos[] = 'recojo = ?';   $par[] = ($vende === 'ambulante' ? 1 : 0);
        }

        foreach ([['direccion', 255], ['horario', 255], ['web', 255],
                  ['facebook', 255], ['instagram', 255], ['tiktok', 255],
                  ['youtube', 255], ['twitter', 255], ['telegram', 255], ['linkedin', 255]] as $f) {
            if (!array_key_exists($f[0], $_POST)) continue;
            $v = trim(strip_tags((string)$_POST[$f[0]]));
            $campos[] = $f[0] . ' = ?';
            $par[]    = ($v === '' ? null : mb_substr($v, 0, $f[1]));
        }

        // 🪪 EL RUC Y EL CORREO DEL NEGOCIO (opcionales, pedido del jefe 2026-09-16 noche): dan
        // confianza y son la puerta de los contratos con empresas. El RUC se guarda solo con sus
        // 11 dígitos (si no son 11, no se guarda: mejor vacío que un RUC inventado).
        if (array_key_exists('ruc', $_POST)) {
            $campos[] = 'ruc = ?';
            $par[]    = (ruc_limpiar($_POST['ruc']) ?: null);
        }
        if (array_key_exists('email', $_POST)) {
            $correo = trim((string)$_POST['email']);
            $campos[] = 'email = ?';
            $par[]    = ($correo !== '' && filter_var($correo, FILTER_VALIDATE_EMAIL))
                        ? mb_substr($correo, 0, 120) : null;
        }

        // 📍 LAS COORDENADAS DEL GPS («📍 Tu ubicación», pedido del jefe 2026-09-16): con esto la ficha
        // pinta su mapa y la tienda entra en «cerca de mí». Solo se guardan si caen dentro del Perú
        // (una coordenada disparatada pondría el mapa en el mar y no se podría arreglar desde aquí).
        $lat_post = trim((string)($_POST['lat'] ?? ''));
        $lng_post = trim((string)($_POST['lng'] ?? ''));
        if ($lat_post !== '' && $lng_post !== '') {
            $lat_v = (float)str_replace(',', '.', $lat_post);
            $lng_v = (float)str_replace(',', '.', $lng_post);
            if ($lat_v <= 0 && $lat_v >= -18.5 && $lng_v <= -68.5 && $lng_v >= -81.5) {
                $campos[] = 'lat = ?';
                $par[]    = round($lat_v, 7);
                $campos[] = 'lng = ?';
                $par[]    = round($lng_v, 7);
            }
        }

        // 📞 TU NÚMERO PRINCIPAL: vive en `whatsapp` (lo usan el botón verde de la ficha, el carrito,
        // `api/lead.php`, el chatbot y el copy de la IA). No se cambia de sitio para no romper nada.
        if (array_key_exists('whatsapp', $_POST)) {
            $v = preg_replace('/\D+/', '', (string)$_POST['whatsapp']);
            if (mb_strlen($v) > 15) $v = mb_substr($v, 0, 15);
            $campos[] = 'whatsapp = ?';
            $par[]    = ($v === '' ? null : $v);
        }

        // 📞 Y HASTA 6 SECUNDARIOS, cada uno con su USO (pedido del jefe 2026-09-16). El campo viejo
        // `telefono` —el que usa el botón «📞 Llamar» de la ficha— se saca del **primero que reciba
        // llamadas**: así todo lo que ya lee `telefono` en el sitio sigue funcionando sin tocar nada más.
        $extras_guardar = null;   // null = el formulario no traía los campos (no se toca nada)
        if (array_key_exists('extra_numero', $_POST)) {
            $nums  = (array)$_POST['extra_numero'];
            $tipos = (array)($_POST['extra_tipo'] ?? []);
            $usos  = telefono_tipos_catalogo();          // ⚠️ lista blanca: el ENUM guarda '' si no está
            $principal = tel_normalizar((string)($_POST['whatsapp'] ?? ''));
            $extras_guardar = [];
            foreach ($nums as $i => $num) {
                $num = (string)preg_replace('/\D+/', '', (string)$num);
                if ($num === '' || mb_strlen($num) < 6) continue;
                if (mb_strlen($num) > 15) $num = mb_substr($num, 0, 15);
                if ($principal !== '' && tel_normalizar($num) === $principal) continue;   // el principal no se repite
                if (in_array(tel_normalizar($num), array_map('tel_normalizar', array_column($extras_guardar, 'numero')), true)) continue;
                $tipo = (string)($tipos[$i] ?? 'ambos');
                if (!isset($usos[$tipo])) $tipo = 'ambos';   // uso desconocido → el de siempre
                $extras_guardar[] = ['numero' => $num, 'tipo' => $tipo];
                if (count($extras_guardar) >= 6) break;      // tope del editor: principal + 6
            }
            $tel_llamadas = '';
            foreach ($extras_guardar as $x) {
                if (telefono_tipo_acciones($x['tipo'])['llamar']) { $tel_llamadas = $x['numero']; break; }
            }
            /* ⚠️ CUIDADO CON LAS TIENDAS DE ANTES (medido el 2026-09-16: **951** tiendas tienen el MISMO
               número en `telefono` y en `whatsapp**, solo que una con el +51 y la otra sin él). Esas
               tiendas ya tienen su botón «📞 Llamar» y no se puede perder por guardar: si el dueño no
               dejó ningún secundario que reciba llamadas y su `telefono` era el mismo número que su
               WhatsApp, el «Llamar» **sigue al número principal**. */
            if ($tel_llamadas === ''
                && tel_normalizar($negocio['telefono']) !== ''
                && tel_normalizar($negocio['telefono']) === tel_normalizar($negocio['whatsapp'])) {
                $tel_llamadas = (string)preg_replace('/\D+/', '', (string)($_POST['whatsapp'] ?? ''));
            }
            $campos[] = 'telefono = ?';
            $par[]    = ($tel_llamadas === '' ? null : $tel_llamadas);
        }

        // 📝 El texto de la ficha. 🆕 2026-09-16: el dueño NO ve el HTML del copy (sería un lío de
        // etiquetas): ve **cómo lo lee el cliente** (con sus botones) y, si quiere, escribe SU propio
        // texto en un campo aparte. Vacío = no se toca (así el copy de la IA se conserva entero).
        if (trim((string)($_POST['descripcion_nueva'] ?? '')) !== '') {
            $lineas = preg_split('/\r\n|\r|\n/', trim((string)$_POST['descripcion_nueva']));
            $parrafos = [];
            foreach ($lineas as $l) {
                $l = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$l)));
                if ($l !== '') $parrafos[] = '<p>' . htmlspecialchars($l, ENT_QUOTES, 'UTF-8') . '</p>';
            }
            if ($parrafos) {
                $campos[] = 'descripcion = ?';
                $par[]    = mb_substr(implode('', $parrafos), 0, 6000);
            }
        }

        // 👁️ Visibilidad: el dueño puede ocultar su tienda sin borrarla (y volver a mostrarla).
        $estado = (string)($_POST['estado'] ?? '');
        if (in_array($estado, ['activo', 'inactivo'], true)) { $campos[] = 'estado = ?'; $par[] = $estado; }

        if ($campos) {
            $campos[] = 'actualizado_en = ?';
            $par[]    = date('Y-m-d H:i:s');
            $par[]    = $nid;
            try {
                db()->prepare("UPDATE directorio_negocios SET " . implode(', ', $campos) . " WHERE id = ?")->execute($par);

                // 📞 Los teléfonos secundarios, en su tabla (se reemplazan los que había por los de ahora).
                if ($extras_guardar !== null) {
                    try {
                        db()->prepare("DELETE FROM directorio_negocio_telefonos WHERE negocio_id = ?")->execute([$nid]);
                        $ins = db()->prepare("INSERT INTO directorio_negocio_telefonos (negocio_id, numero, tipo, orden) VALUES (?,?,?,?)");
                        $o = 1;
                        foreach ($extras_guardar as $x) { $ins->execute([$nid, $x['numero'], $x['tipo'], $o++]); }
                    } catch (Throwable $e) {
                        error_log('mi-tienda telefonos: ' . $e->getMessage());
                        flash('😅 Tus teléfonos no se pudieron guardar ahora mismo.', 'error');
                    }
                }

                // 🌎 La zona: «todos los distritos» (cobertura marcada) o un distrito concreto (se borra
                // SOLO la marca de «todos»; la cobertura real de un servicio a domicilio no se toca).
                if ($zona_cob === 'todos') {
                    cobertura_todos_marcar($nid, array_column(obtener_distritos_visibles(), 'id'));
                } elseif (is_int($zona_cob)) {
                    cobertura_todos_quitar($nid);
                }

                if (function_exists('fuzzy_olvidar_cache')) {
                    require_once __DIR__ . '/includes/fuzzy_cache.php';
                    fuzzy_olvidar_cache();
                }
                @unlink(__DIR__ . '/cache/negocios.json');
                flash('✅ Guardado. Tu tienda ya quedó actualizada.', 'exito');
            } catch (Throwable $e) {
                error_log('mi-tienda guardar: ' . $e->getMessage());
                flash('😅 No pude guardar los cambios: ' . $e->getMessage(), 'error');
            }
        } else {
            flash('No había nada que guardar.', 'error');
        }
    }

    /* ---------- b) Subir fotos (varias de una vez) ---------- */
    if ($accion === 'foto_subir' && !empty($_FILES['fotos'])) {
        $f = $_FILES['fotos'];
        $nombres = is_array($f['name']) ? $f['name'] : [$f['name']];
        $total = min(count($nombres), 8);
        $orden = 0;
        try {
            $st = db()->prepare("SELECT COALESCE(MAX(orden), -1) FROM directorio_fotos WHERE negocio_id = ?");
            $st->execute([$nid]);
            $orden = (int)$st->fetchColumn() + 1;
        } catch (Throwable $e) { $orden = count($fotos); }
        $subidas = 0; $fallos = 0;
        for ($i = 0; $i < $total; $i++) {
            $uno = [
                'name'     => (string)($f['name'][$i] ?? ''),
                'type'     => (string)($f['type'][$i] ?? ''),
                'tmp_name' => (string)($f['tmp_name'][$i] ?? ''),
                'error'    => (int)($f['error'][$i] ?? UPLOAD_ERR_NO_FILE),
                'size'     => (int)($f['size'][$i] ?? 0),
            ];
            if ($uno['error'] !== UPLOAD_ERR_OK || $uno['tmp_name'] === '') continue;
            $r = img_guardar_subida($uno, 'assets/uploads/' . $uid, 'neg' . $nid . '_' . date('Ymd') . '_' . bin2hex(random_bytes(5)));
            if (empty($r['ok'])) { $fallos++; continue; }
            try {
                db()->prepare("INSERT INTO directorio_fotos (negocio_id, ruta, descripcion, orden) VALUES (?,?,?,?)")
                    ->execute([$nid, (string)$r['rel'], 'Galería', $orden++]);
                $subidas++;
            } catch (Throwable $e) { $fallos++; }
        }
        if ($subidas > 0) flash('📸 ' . $subidas . ' foto' . ($subidas === 1 ? '' : 's') . ' agregada' . ($subidas === 1 ? '' : 's') . ' a tu tienda.', 'exito');
        if ($fallos > 0)  flash('(' . $fallos . ' no se pudo subir: revisa que sean imágenes y que no sean muy pesadas.)', 'error');
        if ($subidas === 0 && $fallos === 0) flash('No elegiste ninguna foto.', 'error');
    }

    /* ---------- c) Borrar una foto ---------- */
    if ($accion === 'foto_borrar') {
        $fid = (int)($_POST['foto_borrar'] ?? 0);
        try {
            $st = db()->prepare("SELECT ruta FROM directorio_fotos WHERE id = ? AND negocio_id = ? LIMIT 1");
            $st->execute([$fid, $nid]);
            $ruta = (string)($st->fetchColumn() ?: '');
            if ($ruta !== '') {
                db()->prepare("DELETE FROM directorio_fotos WHERE id = ? AND negocio_id = ?")->execute([$fid, $nid]);
                img_borrar($ruta);
                flash('🗑️ Foto borrada de tu tienda.', 'exito');
            } else {
                flash('Esa foto no es de tu tienda.', 'error');
            }
        } catch (Throwable $e) { flash('😅 No pude borrar la foto.', 'error'); }
    }

    /* ---------- d) Poner una foto de portada (la primera de la galería) ---------- */
    if ($accion === 'foto_portada') {
        $fid = (int)($_POST['foto_portada'] ?? 0);
        try {
            $st = db()->prepare("SELECT COUNT(*) FROM directorio_fotos WHERE id = ? AND negocio_id = ?");
            $st->execute([$fid, $nid]);
            if ((int)$st->fetchColumn() > 0) {
                db()->prepare("UPDATE directorio_fotos SET orden = orden + 1 WHERE negocio_id = ?")->execute([$nid]);
                db()->prepare("UPDATE directorio_fotos SET orden = 0 WHERE id = ?")->execute([$fid]);
                flash('⭐ Listo: esa foto es ahora la portada de tu tienda.', 'exito');
            } else {
                flash('Esa foto no es de tu tienda.', 'error');
            }
        } catch (Throwable $e) { flash('😅 No pude cambiar la portada.', 'error'); }
    }

    /* ---------- e) Reescribir el texto con la IA ---------- */
    if ($accion === 'copy_ia') {
        $d = tienda_ia_datos_vacios();
        $d['nombre']          = (string)$negocio['nombre'];
        $d['rubro_nombre']    = (string)($negocio['rubro_nombre'] ?? '');
        $d['distrito_nombre'] = (string)($negocio['distrito_nombre'] ?? '');
        $d['vendedor']        = (string)$negocio['ubicacion_tipo'];
        $d['horario']         = (string)$negocio['horario'];
        $d['whatsapp']        = (string)$negocio['whatsapp'];
        $prods = tienda_ia_productos_publicados($nid);
        $copy  = tienda_ia_ia_descripcion($d, ['productos' => $prods]);
        if (is_string($copy) && trim($copy) !== '') {
            try {
                db()->prepare("UPDATE directorio_negocios SET descripcion = ?, actualizado_en = ? WHERE id = ?")
                    ->execute([mb_substr($copy, 0, 6000), date('Y-m-d H:i:s'), $nid]);
                flash('✨ Listo: el texto de tu tienda lo volvió a escribir la IA con lo que vendes.', 'exito');
            } catch (Throwable $e) { flash('😅 No pude guardar el texto nuevo.', 'error'); }
        } else {
            flash('😅 La IA no pudo escribir el texto ahora mismo. Prueba otra vez en un rato.', 'error');
        }
    }

    redirect($volver);   // siempre se vuelve a la página (patrón POST → redirección → GET)
}

/* ==================================================================================================
 * 3) LA PÁGINA
 * =============================================================================================== */
$titulo_pagina = 'Editar mi tienda · ' . (string)$negocio['nombre'];
include __DIR__ . '/includes/header.php';

$rubros    = obtener_categorias();
$distritos = obtener_distritos_visibles();
$vende     = ['fisica' => '🏪 Tengo un local y ahí atiendo',
              'ambulante' => '🛵 Soy vendedor ambulante (por las calles)',
              'domicilio' => '🏠 Lo llevo a la casa del cliente',
              'nacional' => '🚚 Vendo a todo el país',
              'mayorista' => '📦 Vendo al por mayor'];
$nproductos = 0;
try {
    $st = db()->prepare("SELECT COUNT(*) FROM directorio_servicios WHERE negocio_id = ?");
    $st->execute([$nid]);
    $nproductos = (int)$st->fetchColumn();
} catch (Throwable $e) {}
$desc_actual = (string)($negocio['descripcion'] ?? '');

// 🌎 ¿La zona está en «🌎 Todos los distritos»? (distrito_id NULL + la marca de la cobertura).
$zona_todos = ($negocio['distrito_id'] === null) || zona_todos_activa($nid);

// 📞 Los hasta 4 teléfonos: el principal ya está en `$negocio['whatsapp']` y aquí van los secundarios.
$tel_extra = negocio_telefonos_extra($nid, 3);
if (!$tel_extra && trim((string)$negocio['telefono']) !== ''
    && tel_normalizar($negocio['telefono']) !== tel_normalizar($negocio['whatsapp'])) {
    // Tienda de antes: su `telefono` viejo se muestra como primer secundario (solo llamadas). Si era
    // EL MISMO número que su WhatsApp (951 tiendas), no se muestra: repetirlo solo confundiría.
    $tel_extra = [['numero' => (string)$negocio['telefono'], 'tipo' => 'llamada']];
}
$tel_extra = array_pad(array_slice($tel_extra, 0, 6), 4, ['numero' => '', 'tipo' => 'ambos']);
$tipos_tel = telefono_tipos_catalogo();

// 🔗 Las redes que el dueño ya tiene puestas (para los chips de la tarjeta).
$mis_redes = negocio_redes_con_valor($negocio);

// 🕒 LAS OPCIONES DEL HORARIO (pedido del jefe, 2026-09-16 noche): se eligen con casillas en un modal.
// Lo que se guarda es el TEXTO del rótulo, porque el horario es una sola línea que se lee en la ficha.
$horario_opciones = [
    '24h'         => '🕛 24 horas, todos los días (¡no descanso nunca!)',
    '24h_finde'   => '🗓️ 24 horas, solo los fines de semana',
    'manana_todo' => '🌅 Todos los días en las mañanas',
    'tardes'      => '🌆 Solo en las tardes',
    'mananas'     => '☀️ Solo en las mañanas',
    'varios'      => '✅ Abro en varios de esos horarios',
    'noche'       => '🌙 De 9 de la noche a 2 de la mañana',
];
$horario_actual  = trim((string)$negocio['horario']);
$horario_marcado = [];       // qué casillas salen marcadas
$horario_otro    = '';       // lo que va en «otro» (si el horario guardado no es ninguna opción)
foreach ($horario_opciones as $k => $txt) {
    if ($horario_actual !== '' && mb_stripos($horario_actual, $txt) !== false) $horario_marcado[$k] = true;
}
if ($horario_actual !== '' && !$horario_marcado) $horario_otro = $horario_actual;

// 🗺️ ¿Ya tiene el punto en el mapa? (El dueño lo captura con «📍 Tu ubicación».)
$tiene_ubi = ((string)$negocio['lat'] !== '' && $negocio['lat'] !== null
           && (string)$negocio['lng'] !== '' && $negocio['lng'] !== null
           && (float)$negocio['lat'] !== 0.0 && (float)$negocio['lng'] !== 0.0);
?>
<style>
/* Estilos EN LÍNEA a propósito (como en `producto.php` y en el panel): así no hay que subir el `?v=`
   de ningún CSS y no se pelea con la caché de Cloudflare. */
/* 📐 A TODO EL ANCHO (orden del jefe, 2026-09-16: «ocupa todo el ancho de pantalla, no dejes espacio
   a la izquierda, así tendremos más espacio para editar en móvil»): esta página anula el relleno del
   `<main>` y se queda con TODO el ancho. Lo que da el aire son las tarjetas, que van de borde a borde
   con su propio relleno interno (menos margen = más sitio para escribir en el celular). */
.main{padding:0}
.mt{max-width:1020px;margin:0 auto;padding:10px 0 20px}
@media (min-width:760px){.main{padding:16px}.mt{padding:0}}
.mt__migas{font-size:13px;color:#6b7280;margin:2px 13px 12px}
.mt__migas a{color:#6b7280;text-decoration:none}
/* Los textos que NO van dentro de una tarjeta llevan su propio aire a los costados: las tarjetas sí
   van de borde a borde (su relleno interno ya da el espacio para escribir). */
.mt > h1,.mt > p{padding-left:13px;padding-right:13px}
.mt__aviso{background:#fff7ed;border:1px solid #fed7aa;border-radius:14px;padding:12px 13px;margin:0 13px 16px;
  font-size:14.5px;line-height:1.5;color:#7c2d12}
.mt__acciones{display:flex;gap:8px;flex-wrap:wrap;margin:0 13px 18px}
.mt__b{display:inline-flex;align-items:center;gap:7px;padding:10px 15px;border-radius:999px;font-weight:700;
  font-size:15px;text-decoration:none;border:1px solid var(--color-borde,#e5e7eb);background:#fff;color:inherit;
  cursor:pointer;font-family:inherit}
.mt__b--wsp{background:#25d366;border-color:#25d366;color:#fff}
.mt__b--listo{background:#6d071a;border-color:#6d071a;color:#fff;width:100%;justify-content:center;padding:13px 16px;font-size:16px}
.mt__card{background:#fff;border-radius:14px;padding:14px 13px;margin:0 0 12px;box-shadow:var(--sombra-tarjeta)}
.mt__card h2{font-size:17px;margin:0 0 12px;color:#6d071a}
.mt__grid{display:grid;grid-template-columns:1fr;gap:12px}
@media (min-width:760px){.mt__grid--dos{grid-template-columns:1fr 1fr}}
.mt label{display:block;font-size:14px;font-weight:700;margin:0 0 5px;color:#374151}
.mt input[type=text],.mt input[type=tel],.mt select,.mt textarea{width:100%;box-sizing:border-box;padding:12px 13px;
  border:1.5px solid #e2c9a6;border-radius:12px;font-size:16px;font-family:inherit;background:#fff;color:#111827}
.mt input:focus,.mt select:focus,.mt textarea:focus{outline:none;border-color:#c9a06a;box-shadow:0 0 0 3px rgba(234,106,18,.15)}
.mt__ayuda{font-size:12.5px;color:#6b7280;margin:5px 0 0}
/* 📞 LOS 4 TELÉFONOS: el principal con el texto en ROJO (orden del jefe), los otros apilados. */
.mt__tel{border:1px solid #ece3d6;border-radius:12px;padding:11px;margin:0 0 11px;background:#fffdf9}
.mt__tel--principal{border:1.5px solid #f4b8b8;background:#fff7f7}
.mt__tel--principal label{color:#b91c1c}
.mt__tel-num{display:grid;grid-template-columns:1fr;gap:8px}
@media (min-width:620px){.mt__tel-num{grid-template-columns:1fr 1.2fr;align-items:center}}
.mt__tel--principal input[type=tel]{color:#b91c1c;font-weight:700;border-color:#f0a5a5}
.mt__tel--principal input[type=tel]::placeholder{color:#e08c8c;font-weight:600}
.mt__tel-rol{font-size:14px}
/* Los chips con lo que el dueño ya tiene puesto (números y redes), y su resumen en vivo. */
.mt__lista-tel{display:flex;flex-wrap:wrap;gap:6px;margin:0 0 10px}
.mt__chip{display:inline-flex;align-items:center;gap:6px;background:#f9fafb;border:1px solid #e5e7eb;
  border-radius:999px;padding:6px 11px;font-size:13.5px;max-width:100%;overflow-wrap:anywhere}
.mt__chip-sub{color:#6b7280;font-size:12.5px}
/* El horario: se lee (no se teclea) y se elige en su modal con casillas. */
.mt__lectura{border:1px dashed #e2c9a6;border-radius:12px;padding:11px 12px;background:#fffdf9;
  font-size:15px;color:#374151;overflow-wrap:anywhere}
.mt__opciones{display:grid;grid-template-columns:1fr;gap:8px;margin:0 0 10px}
@media (min-width:620px){.mt__opciones{grid-template-columns:1fr 1fr}}
.mt__check{display:flex;align-items:center;gap:9px;border:1.5px solid #e2c9a6;border-radius:12px;
  padding:11px 12px;font-size:15px;background:#fff;cursor:pointer;margin:0;font-weight:600;color:#111827}
.mt__check input[type=checkbox]{width:20px;height:20px;flex:none;accent-color:#6d071a;margin:0}
.mt__fila-red{display:grid;grid-template-columns:1fr;gap:8px;border:1px solid #ece3d6;border-radius:12px;
  padding:11px;margin:0 0 11px;background:#fffdf9}
@media (min-width:620px){.mt__fila-red{grid-template-columns:170px 1fr;align-items:center}}
.mt__fila-red-etq{font-size:14.5px;font-weight:700;color:#374151}
.mt__consejo{display:flex;gap:8px;align-items:flex-start;background:#f0fdf4;border:1px solid #86efac;color:#14532d;
  border-radius:12px;padding:10px 12px;font-size:14px;line-height:1.45;margin:0 0 11px}
.mt__ubi{border:1px dashed #e2c9a6;border-radius:12px;padding:11px;margin:10px 0 0;background:#fffdf9}
.mt__ubi-caja{display:none;margin-top:10px;border-top:1px dashed #e2c9a6;padding-top:10px;font-size:14px;color:#374151}
.mt__ubi-caja.is-viendo{display:block}
.mt__ubi-dato{margin:0 0 6px;overflow-wrap:anywhere}
.mt__ubi-b{display:inline-flex;align-items:center;gap:6px;margin-top:6px;padding:9px 14px;border-radius:999px;
  border:0;background:#0f766e;color:#fff;font-size:14.5px;font-weight:700;font-family:inherit;cursor:pointer}
/* 📸 LAS FOTOS: cuadrícula de 3 columnas × 2 filas (la primera es la portada) y, al tocar, el visor. */
.mt__fotos{display:grid;grid-template-columns:repeat(3,1fr);gap:7px}
/* En la computadora la tarjeta es anchísima y 3 columnas dejarían fotos gigantes: se le pone tope y se
   centra (en el celular, que es donde el dueño edita, van de borde a borde). */
@media (min-width:760px){.mt__fotos{max-width:600px;margin-left:auto;margin-right:auto}}
.mt__foto{position:relative;border:1px solid var(--color-borde,#e5e7eb);border-radius:12px;overflow:hidden;
  background:#f9fafb;padding:0;margin:0;cursor:pointer;font-family:inherit;display:block;width:100%}
.mt__foto img{width:100%;aspect-ratio:1/1;object-fit:cover;display:block}
.mt__foto--portada{outline:3px solid #f59e0b;outline-offset:-3px}
.mt__sello{position:absolute;top:5px;left:5px;background:#f59e0b;color:#78350f;font-size:10.5px;font-weight:800;
  padding:2px 7px;border-radius:999px;letter-spacing:.3px}
.mt__mas{position:absolute;inset:0;background:rgba(17,24,39,.62);color:#fff;display:flex;align-items:center;
  justify-content:center;font-size:20px;font-weight:800}
.mt__foto-add{border:2px dashed #e2c9a6;background:#fffdf9;color:#8a5a1a;display:flex;flex-direction:column;
  align-items:center;justify-content:center;gap:4px;font-size:13px;font-weight:700;aspect-ratio:1/1}
.mt__foto-add span{font-size:22px;line-height:1}
/* Sin ninguna foto todavía, la casilla ocupa las 3 columnas (si no, quedaría un cuadradito chico). */
.mt__foto-add--ancho{grid-column:1/-1;aspect-ratio:auto;padding:26px 12px;font-size:15px}
/* El visor (modal) con TODAS las fotos: portada, borrar y agregar. */
.mt__modal{position:fixed;inset:0;z-index:9998;display:none}
.mt__modal.is-abierto{display:block}
.mt__modal-fondo{position:absolute;inset:0;background:rgba(17,24,39,.68)}
.mt__modal-caja{position:absolute;left:0;right:0;bottom:0;top:auto;max-height:92vh;overflow-y:auto;
  background:#fff;border-radius:18px 18px 0 0;padding:14px 13px 18px;box-shadow:0 -10px 40px rgba(0,0,0,.3);
  max-width:1020px;margin:0 auto}
@media (min-width:760px){.mt__modal-caja{top:5vh;bottom:auto;border-radius:18px;left:2vw;right:2vw;width:auto}}
.mt__modal-head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin:0 0 10px}
.mt__modal-head h3{margin:0;font-size:16.5px;color:#6d071a}
.mt__modal-x{border:0;background:#f3f4f6;color:#374151;width:36px;height:36px;border-radius:999px;font-size:17px;
  cursor:pointer;font-family:inherit;flex:none}
.mt__modal .mt__foto-acc{display:flex;gap:4px;padding:6px}
.mt__modal .mt__foto-acc button{flex:1;border:0;border-radius:8px;padding:8px 4px;font-size:12.5px;font-weight:700;
  cursor:pointer;font-family:inherit;background:#f3f4f6;color:#374151}
.mt__modal .mt__foto-acc button.mt__portada{background:#fef3c7;color:#92400e}
.mt__modal .mt__foto-acc button.mt__borrar{background:#fff1f2;color:#be123c}
.mt__modal-add{margin-top:14px;border-top:1px dashed #e2c9a6;padding-top:12px}
.mt__alerta{display:none;background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:12px;
  padding:10px 12px;font-size:14px;line-height:1.45;margin:0 0 10px}
.mt__alerta.is-viendo{display:block}
/* 📝 La vista del texto: se pinta con el motor de la ficha, así el dueño ve lo mismo que su cliente. */
.mt__copia{border:1px dashed #e2c9a6;border-radius:12px;padding:12px 14px;background:#fffdf9;font-size:15px;
  line-height:1.55;overflow-wrap:anywhere}
.mt__copia h3{margin:12px 0 6px}
.mt__copia p{margin:8px 0}
.mt__copia ul{margin:8px 0 8px 18px;padding:0}
/* 🔐 EL BLOQUE FINAL: la contraseña y el botón de guardar (todos los botones del formulario pasan por aquí).
   ⚠️ NO es `position:sticky`: el jefe ya se quejó una vez de que un bloque fijo le tapaba la pantalla
   del celular (2026-09-16). Va al final del formulario, como una tarjeta más. */
.mt__firma{background:#fffdf9;border:2px solid #f0c9a0;border-radius:16px;padding:14px;margin:12px 0 8px;
  box-shadow:0 -6px 18px rgba(43,33,24,.05)}
.mt__firma h3{margin:0 0 6px;font-size:16px;color:#6d071a}
.mt__firma p{margin:0 0 10px;font-size:13.5px;color:#6b7280;line-height:1.45}
.mt__firma p strong{color:#7c2d12}
.mt__firma-wsp{display:flex;gap:8px;align-items:flex-start;background:#f0fdf4;border:1px solid #86efac;
  color:#14532d;border-radius:12px;padding:10px 12px;font-size:13.5px;line-height:1.5;margin:0 0 11px}
.mt__guardar{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:15px 18px;border:0;
  border-radius:999px;background:linear-gradient(180deg,#7d0a1f,#6d071a);color:#fff;font-size:17px;font-weight:800;
  cursor:pointer;font-family:inherit;box-shadow:0 8px 20px rgba(109,7,26,.28)}
</style>

<div class="mt">
    <p class="mt__migas">
        <a href="<?= e(url('panel.php')) ?>">Mi panel</a> ›
        <?php // ⛔ SIN ANCLAS (orden del jefe, 2026-09-21): estos enlaces llevaban a `panel.php#mis-negocios`. ?>
        <a href="<?= e(url('panel.php')) ?>">Mis negocios</a> ›
        <span>Editar</span>
    </p>

    <h1 class="seccion__titulo" style="font-size:22px;margin-bottom:6px">✏️ Editar mi tienda</h1>
    <p style="font-size:15px;color:#6b7280;margin:0 0 14px"><strong><?= e($negocio['nombre']) ?></strong> · <?= e($negocio['estado']) ?></p>

    <div class="mt__acciones">
        <a class="mt__b mt__b--wsp" href="<?= e(url_negocio((string)$negocio['slug'])) ?>" target="_blank" rel="noopener">👁️ Ver mi tienda</a>
        <a class="mt__b" href="<?= e(url('productos.php?n=' . $nid)) ?>">🛒 Mis productos (<?= $nproductos ?>)</a>
        <a class="mt__b" href="<?= e(url('panel.php')) ?>">← Volver al panel</a>
    </div>

    <p class="mt__aviso">
        🔐 <strong>Para guardar cualquier cambio te voy a pedir tu contraseña</strong> (la misma con la que
        entras a DeChimbote.com). Es tu tienda y nadie más puede tocarla: ni aunque dejes el celular abierto.
    </p>

    <form method="post" class="mt__form" enctype="multipart/form-data" id="mtForm">
        <?= csrf_campo() ?>
        <input type="hidden" name="accion" value="guardar_tienda" id="mtAccion">

        <div class="mt__card">
            <h2>🏪 Los datos de tu tienda</h2>
            <div class="mt__grid">
                <div>
                    <label for="mtNombre">Nombre de tu tienda</label>
                    <input type="text" id="mtNombre" name="nombre" maxlength="180" required value="<?= e($negocio['nombre']) ?>">
                    <p class="mt__ayuda">Si lo corriges, <strong>tu enlace no cambia</strong>: el que ya compartiste sigue funcionando.</p>
                </div>
                <div class="mt__grid mt__grid--dos">
                    <div>
                        <label for="mtRubro">¿De qué es tu tienda? (tu rubro principal)</label>
                        <select id="mtRubro" name="categoria_id">
                            <?php foreach ($rubros as $c): ?>
                                <option value="<?= (int)$c['id'] ?>" <?= (int)$c['id'] === (int)$negocio['categoria_id'] ? 'selected' : '' ?>>
                                    <?= e(($c['icono'] ?? '') . ' ' . $c['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label for="mtVende">¿Cómo atiendes?</label>
                        <select id="mtVende" name="ubicacion_tipo">
                            <?php foreach ($vende as $k => $txt): ?>
                                <option value="<?= e($k) ?>" <?= (string)$negocio['ubicacion_tipo'] === $k ? 'selected' : '' ?>><?= e($txt) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="mt__grid mt__grid--dos">
                    <div>
                        <label for="mtDistrito">Tu zona</label>
                        <select id="mtDistrito" name="distrito_id">
                            <option value="0">— Elegir —</option>
                            <?php /* 🌎 LA OPCIÓN QUE FALTABA (pedido del jefe, 2026-09-16): el negocio que
                                     atiende en TODA la provincia elige «Todos los distritos» y sale en la
                                     búsqueda de cualquiera de ellos. */ ?>
                            <option value="todos" <?= $zona_todos ? 'selected' : '' ?>>🌎 Todos los distritos (toda la provincia)</option>
                            <?php foreach ($distritos as $d): ?>
                                <option value="<?= (int)$d['id'] ?>" <?= (!$zona_todos && (int)$d['id'] === (int)$negocio['distrito_id']) ? 'selected' : '' ?>>
                                    <?= e($d['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="mt__ayuda">Si atiendes en toda la provincia (a domicilio, mayorista, delivery),
                           elige <strong>🌎 Todos los distritos</strong>.</p>
                    </div>
                    <div>
                        <label for="mtDireccion">Tu dirección o referencia</label>
                        <input type="text" id="mtDireccion" name="direccion" maxlength="255" value="<?= e((string)$negocio['direccion']) ?>"
                               placeholder="Ej. Av. José Pardo 123, frente al mercado">
                    </div>
                </div>

                <?php /* 📍 TU UBICACIÓN (pedido del jefe, 2026-09-16): debajo de la dirección, el botón que
                         captura el punto del GPS y escribe la calle/dirección solita (dirección inversa de
                         OpenStreetMap, el mismo camino que ya usa Caminante). Lo capturado se guarda en
                         `lat`/`lng` al tocar «💾 Guardar los cambios» y con eso la ficha pinta su MAPA
                         (y el negocio sale en «cerca de mí»). */ ?>
                <div class="mt__ubi" id="mtUbi">
                    <div class="mt__consejo">
                        <span aria-hidden="true">📈</span>
                        <span><strong>Los negocios con ubicación reciben más clientes diariamente.</strong>
                              Toca el botón y tu celular te ubica: te lleno la dirección y le pongo el mapa a tu tienda.</span>
                    </div>
                    <button type="button" class="mt__b mt__b--ubi" id="mtUbiBtn"
                            style="background:#0f766e;border-color:#0f766e;color:#fff">📍 Tu ubicación</button>
                    <p class="mt__ayuda" id="mtUbiEstado">
                        <?= $tiene_ubi
                            ? '✅ Tu tienda ya tiene su punto en el mapa. Toca el botón para actualizarlo.'
                            : 'Toca el botón <strong>📍 Tu ubicación</strong> y te detecto la calle automáticamente.' ?>
                    </p>
                    <div class="mt__ubi-caja<?= $tiene_ubi ? ' is-viendo' : '' ?>" id="mtUbiCaja">
                        <p class="mt__ubi-dato">📌 <strong id="mtUbiCoord"><?= $tiene_ubi ? e(number_format((float)$negocio['lat'], 6, '.', '') . ', ' . number_format((float)$negocio['lng'], 6, '.', '')) : '' ?></strong></p>
                        <p class="mt__ubi-dato" id="mtUbiDirec"><?= $tiene_ubi ? 'Ubicación guardada en tu tienda.' : '' ?></p>
                        <button type="button" class="mt__ubi-b" id="mtUbiUsar" hidden>✅ Usar esta dirección</button>
                    </div>
                    <input type="hidden" name="lat" id="mtLat" value="<?= $tiene_ubi ? e((string)$negocio['lat']) : '' ?>">
                    <input type="hidden" name="lng" id="mtLng" value="<?= $tiene_ubi ? e((string)$negocio['lng']) : '' ?>">
                </div>
            </div>
        </div>

        <div class="mt__card">
            <h2>📞 Cómo te contactan tus clientes</h2>
            <p class="mt__ayuda" style="margin:0 0 12px">
                Este es tu <strong>número principal</strong> (en rojo): donde te escriben por WhatsApp. Si
                tienes más números, agrégalos abajo y dile a cada uno <strong>para qué es</strong>
                (solo ventas, solo atención, solo WhatsApp, para otras tiendas…). Todos salen en tu ficha.
            </p>

            <div class="mt__tel mt__tel--principal">
                <label for="mtWsp">Tu número principal (WhatsApp)</label>
                <input type="tel" id="mtWsp" name="whatsapp" inputmode="numeric" maxlength="15"
                       value="<?= e((string)$negocio['whatsapp']) ?>" placeholder="943112233">
                <p class="mt__ayuda">No pongas el +51 ni espacios. Los clientes te escriben aquí.</p>
            </div>

            <?php /* 📞 LOS DEMÁS NÚMEROS: la tarjeta solo enseña los que ya tiene; los campos se llenan
                     en el modal (pedido del jefe: «muestra el número actual en rojo y un botón de
                     agregar más números»). */ ?>
            <div class="mt__lista-tel" id="mtTelLista">
                <?php $hay_extras = false; foreach ($tel_extra as $tx): if (trim((string)$tx['numero']) === '') continue; $hay_extras = true; ?>
                    <span class="mt__chip">
                        <span aria-hidden="true"><?= telefono_tipo_icono((string)$tx['tipo']) ?></span>
                        <strong><?= e((string)$tx['numero']) ?></strong>
                        <span class="mt__chip-sub"><?= e(telefono_tipo_texto((string)$tx['tipo'])) ?></span>
                    </span>
                <?php endforeach; ?>
                <?php if (!$hay_extras): ?>
                    <p class="mt__ayuda" id="mtTelVacio" style="margin:0">Todavía no agregaste otros números.</p>
                <?php endif; ?>
            </div>
            <button type="button" class="mt__b" id="mtTelBtn" style="background:#f0fdf4;border-color:#86efac;color:#14532d">
                ➕ Agregar más números
            </button>

            <div style="margin-top:12px">
                <label for="mtHorario">Tu horario de atención</label>
                <div class="mt__lectura" id="mtHorarioVer"><?= $horario_actual !== '' ? e($horario_actual) : 'Todavía no pusiste tu horario.' ?></div>
                <input type="hidden" id="mtHorario" name="horario" value="<?= e($horario_actual) ?>">
                <button type="button" class="mt__b" id="mtHorarioBtn" style="margin-top:8px;background:#f0f9ff;border-color:#7dd3fc;color:#075985">
                    🕒 Elegir mi horario
                </button>
                <p class="mt__ayuda">Marca todas las opciones que te describan (puedes marcar varias).</p>
            </div>
        </div>

        <?php /* 🪪 EL RUC Y EL CORREO (pedido del jefe, 2026-09-16 noche): opcionales, pero el jefe lo
                 tiene claro: *«los negocios con RUC son los que se llevan los contratos más grandes»*. */ ?>
        <div class="mt__card">
            <h2>🪪 Tu RUC y tu correo (opcional, pero vende más)</h2>
            <div class="mt__consejo">
                <span aria-hidden="true">💼</span>
                <span><strong>Los negocios con RUC se llevan los contratos más grandes.</strong>
                      Sí funciona: las empresas y las instituciones buscan proveedores formales. Pon tu
                      <strong>RUC</strong> y tu <strong>correo</strong> —es opcional— y verás que te escriben
                      para compras y contratos, no solo para una venta suelta.</span>
            </div>
            <div class="mt__grid mt__grid--dos">
                <div>
                    <label for="mtRuc">Tu RUC</label>
                    <input type="text" id="mtRuc" name="ruc" inputmode="numeric" maxlength="20"
                           value="<?= e((string)$negocio['ruc']) ?>" placeholder="20601234567">
                    <p class="mt__ayuda">11 números. Aparece en tu ficha como <strong>RUC 20-12345678-9</strong>.</p>
                </div>
                <div>
                    <label for="mtMail">Tu correo electrónico</label>
                    <input type="text" id="mtMail" name="email" maxlength="120"
                           value="<?= e((string)$negocio['email']) ?>" placeholder="ventas@tutienda.com">
                    <p class="mt__ayuda">Aquí te escriben las empresas que quieren comprarte.</p>
                </div>
            </div>
        </div>

        <div class="mt__card">
            <h2>📘 Tus redes sociales (opcional)</h2>
            <p class="mt__ayuda" style="margin:0 0 11px">
                Muéstralas en tu ficha: el cliente que te sigue en una red te cree más y te compra más rápido.
            </p>
            <div class="mt__lista-tel">
                <?php if ($mis_redes): ?>
                    <?php foreach ($mis_redes as $campo => $r): ?>
                        <span class="mt__chip">
                            <span aria-hidden="true"><?= $r['icono'] ?></span>
                            <strong><?= e($r['etiqueta']) ?></strong>
                            <span class="mt__chip-sub"><?= e($r['valor']) ?></span>
                        </span>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="mt__ayuda" style="margin:0">Todavía no pusiste ninguna red.</p>
                <?php endif; ?>
            </div>
            <button type="button" class="mt__b" id="mtRedesBtn" style="background:#f5f3ff;border-color:#c4b5fd;color:#5b21b6">
                📘 Mostrar mis redes sociales
            </button>
        </div>

        <div class="mt__card">
            <h2>📸 Las fotos de tu tienda</h2>
            <p class="mt__ayuda" style="margin:0 0 11px">
                Tu <strong>primera foto es la portada</strong>. Aquí ves las 6 primeras: toca
                <strong>cualquier foto</strong> y se abre el visor con <strong>todas</strong> —ahí eliges la
                portada, borras o agregas nuevas.
            </p>
            <input type="hidden" name="foto_portada" value="">
            <input type="hidden" name="foto_borrar" value="">
            <?php if ($fotos): ?>
                <div class="mt__fotos">
                    <?php /* La cuadrícula es de 3 columnas × 2 filas (orden del jefe, 2026-09-16). */ ?>
                    <?php foreach (array_slice($fotos, 0, 6) as $i => $f): ?>
                        <button type="button" class="mt__foto<?= $i === 0 ? ' mt__foto--portada' : '' ?>"
                                data-mt-visor="1" aria-label="Ver todas mis fotos">
                            <?php if ($i === 0): ?><span class="mt__sello">PORTADA</span><?php endif; ?>
                            <?= img_tag((string)$f['ruta'], (string)$negocio['nombre'], ['sizes' => '160px']) ?>
                            <?php if ($i === 5 && count($fotos) > 6): ?>
                                <span class="mt__mas">+<?= count($fotos) - 6 ?></span>
                            <?php endif; ?>
                        </button>
                    <?php endforeach; ?>
                    <?php if (count($fotos) < 6): ?>
                        <button type="button" class="mt__foto mt__foto-add" data-mt-visor="1" aria-label="Agregar fotos">
                            <span aria-hidden="true">📷</span>Agregar
                        </button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="mt__fotos">
                    <button type="button" class="mt__foto mt__foto-add mt__foto-add--ancho" data-mt-visor="1" aria-label="Agregar mis primeras fotos">
                        <span aria-hidden="true">📷</span>Agregar mis primeras fotos
                    </button>
                </div>
                <p class="mt__ayuda">Todavía no tienes fotos. Sube las de tu local, tus productos y tu letrero: son lo que más vende.</p>
            <?php endif; ?>
        </div>

        <div class="mt__card">
            <h2>📝 El texto de tu tienda</h2>
            <p class="mt__ayuda" style="margin-bottom:8px">Así lo lee el cliente en tu ficha (los botones de
               WhatsApp los pone el sitio solo, con tu número):</p>
            <?php
            // 🖥️ Se pinta con el MISMO motor que la ficha: el dueño ve exactamente lo que ve su cliente.
            $render_desc = descripcion_negocio_html($desc_actual, $negocio);
            ?>
            <div class="mt__copia">
                <?php if (trim($render_desc) !== ''): ?>
                    <?= $render_desc ?>
                <?php else: ?>
                    <p class="mt__ayuda">Todavía no tiene texto. Toca <strong>✨ Que la IA lo escriba otra vez</strong> y te lo armo con lo que vendes.</p>
                <?php endif; ?>
            </div>
            <div style="margin-top:12px">
                <label for="mtDesc">¿Quieres escribir tu propio texto?</label>
                <textarea id="mtDesc" name="descripcion_nueva" rows="5"
                          placeholder="Déjalo vacío para no cambiar nada…"></textarea>
                <p class="mt__ayuda">
                    Si escribes aquí, tu texto reemplaza al que está arriba ⚠️ (se pierden los botones de
                    WhatsApp que puso la IA). El botón de abajo los vuelve a poner.
                </p>
            </div>
            <button type="submit" class="btn" name="accion" value="copy_ia" style="margin-top:10px"
                    title="La IA vuelve a escribir el texto de tu ficha con lo que vendes">✨ Que la IA lo escriba otra vez</button>
        </div>

        <div class="mt__card">
            <h2>👁️ ¿Tu tienda se ve?</h2>
            <div class="mt__grid mt__grid--dos">
                <div>
                    <label for="mtEstado">Visibilidad</label>
                    <select id="mtEstado" name="estado">
                        <option value="activo" <?= (string)$negocio['estado'] === 'activo' ? 'selected' : '' ?>>✅ Visible (la ve todo el mundo)</option>
                        <option value="inactivo" <?= (string)$negocio['estado'] === 'inactivo' ? 'selected' : '' ?>>🙈 Ocultarla por un tiempo</option>
                    </select>
                    <p class="mt__ayuda">Ocultarla no la borra: vuelve a mostrarla cuando quieras.</p>
                </div>
            </div>
        </div>

        <?php /* 📞 EL MODAL DE LOS OTROS NÚMEROS (pedido del jefe, 2026-09-16 noche): la tarjeta solo
                 muestra el principal en rojo y un botón; aquí están los campos (número + PARA QUÉ ES)
                 y el botón «➕ Agregar otro número». Va dentro del formulario igual que el visor de
                 fotos, así los números viajan con la contraseña. */ ?>
        <div class="mt__modal" id="mtTelModal" aria-hidden="true">
            <div class="mt__modal-fondo" data-mt-cerrar-tel="1"></div>
            <div class="mt__modal-caja" role="dialog" aria-modal="true" aria-label="Tus otros números">
                <div class="mt__modal-head">
                    <h3>📞 Tus otros números</h3>
                    <button type="button" class="mt__modal-x" data-mt-cerrar-tel="1" aria-label="Cerrar">✕</button>
                </div>
                <p class="mt__ayuda" style="margin:0 0 11px">
                    Escribe el número y elige <strong>para qué es</strong>: puedes tener uno solo para
                    ventas, otro solo para atención, otro solo para WhatsApp o uno para las tiendas que te
                    compran al por mayor. Los que dejes vacíos no se guardan.
                </p>
                <div id="mtTelFilas">
                    <?php foreach ($tel_extra as $i => $tx): ?>
                        <div class="mt__tel">
                            <label for="mtExtra<?= $i ?>">Número <?= $i + 2 ?></label>
                            <div class="mt__tel-num">
                                <select name="extra_tipo[<?= $i ?>]" class="mt__tel-rol" aria-label="¿Para qué es el número <?= $i + 2 ?>?">
                                    <?php foreach ($tipos_tel as $k => $f): ?>
                                        <option value="<?= e($k) ?>" <?= (string)$tx['tipo'] === $k ? 'selected' : '' ?>>
                                            <?= e($f['icono'] . ' ' . $f['texto']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <input type="tel" id="mtExtra<?= $i ?>" name="extra_numero[<?= $i ?>]" inputmode="numeric" maxlength="15"
                                       value="<?= e((string)$tx['numero']) ?>" placeholder="Ej. 943112233">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="button" class="mt__b" id="mtTelMas" style="background:#f0fdf4;border-color:#86efac;color:#14532d">
                    ➕ Agregar otro número
                </button>
                <p class="mt__ayuda" id="mtTelTope" style="display:none">Ya tienes el máximo (tu principal + 6 más).</p>
                <button type="button" class="mt__b mt__b--listo" data-mt-cerrar-tel="1" style="margin-top:12px">✅ Listo</button>
            </div>
        </div>

        <?php /* 🕒 EL MODAL DEL HORARIO: casillas (se pueden marcar varias) + «otro» para escribirlo. */ ?>
        <div class="mt__modal" id="mtHorarioModal" aria-hidden="true">
            <div class="mt__modal-fondo" data-mt-cerrar-hor="1"></div>
            <div class="mt__modal-caja" role="dialog" aria-modal="true" aria-label="Tu horario de atención">
                <div class="mt__modal-head">
                    <h3>🕒 ¿En qué horario atiendes?</h3>
                    <button type="button" class="mt__modal-x" data-mt-cerrar-hor="1" aria-label="Cerrar">✕</button>
                </div>
                <p class="mt__ayuda" style="margin:0 0 11px">Marca todo lo que te describa (puedes marcar varias).</p>
                <div class="mt__opciones">
                    <?php foreach ($horario_opciones as $k => $txt): ?>
                        <label class="mt__check">
                            <input type="checkbox" name="horario_marca[]" value="<?= e($k) ?>"
                                   data-mt-hor="<?= e($txt) ?>" <?= !empty($horario_marcado[$k]) ? 'checked' : '' ?>>
                            <span><?= e($txt) ?></span>
                        </label>
                    <?php endforeach; ?>
                    <label class="mt__check">
                        <input type="checkbox" id="mtHorOtra" value="otro" <?= $horario_otro !== '' ? 'checked' : '' ?>>
                        <span>✍️ Otro horario (lo escribo yo)</span>
                    </label>
                </div>
                <div id="mtHorCaja" style="<?= $horario_otro !== '' ? '' : 'display:none' ?>">
                    <label for="mtHorOtro">Escribe tu horario</label>
                    <input type="text" id="mtHorOtro" value="<?= e($horario_otro) ?>" maxlength="180"
                           placeholder="Ej. De lunes a sábado, de 8 de la mañana a 9 de la noche">
                </div>
                <div style="margin-top:12px">
                    <div class="mt__fila-red-etq">Así lo verá el cliente:</div>
                    <div class="mt__lectura" id="mtHorVer">—</div>
                </div>
                <button type="button" class="mt__b mt__b--listo" data-mt-cerrar-hor="1" style="margin-top:12px">✅ Listo</button>
            </div>
        </div>

        <?php /* 📘 EL MODAL DE LAS REDES: 8 casillas (el jefe pidió «al menos 6»). */ ?>
        <div class="mt__modal" id="mtRedesModal" aria-hidden="true">
            <div class="mt__modal-fondo" data-mt-cerrar-red="1"></div>
            <div class="mt__modal-caja" role="dialog" aria-modal="true" aria-label="Tus redes sociales">
                <div class="mt__modal-head">
                    <h3>📘 Tus redes sociales</h3>
                    <button type="button" class="mt__modal-x" data-mt-cerrar-red="1" aria-label="Cerrar">✕</button>
                </div>
                <p class="mt__ayuda" style="margin:0 0 11px">
                    Llena solo las que tengas: puedes escribir la dirección completa
                    (<em>facebook.com/tutienda</em>) o solo tu usuario (<em>@tutienda</em>).
                </p>
                <?php foreach (negocio_redes_catalogo() as $campo => $f): ?>
                    <div class="mt__fila-red">
                        <div class="mt__fila-red-etq"><?= $f['icono'] ?> <?= e($f['etiqueta']) ?></div>
                        <input type="text" id="mtRed_<?= e($campo) ?>" name="<?= e($campo) ?>" maxlength="255"
                               value="<?= e((string)$negocio[$campo]) ?>" placeholder="<?= e($f['placeholder']) ?>"
                               data-mt-red="<?= e($campo) ?>" data-mt-red-etq="<?= e($f['etiqueta']) ?>" data-mt-red-ico="<?= $f['icono'] ?>">
                    </div>
                <?php endforeach; ?>
                <button type="button" class="mt__b mt__b--listo" data-mt-cerrar-red="1">✅ Listo</button>
            </div>
        </div>

        <?php /* 📸 EL VISOR DE FOTOS (modal): se abre al tocar cualquier foto de la cuadrícula y ahí
                 están TODAS, con «⭐ Portada» y «🗑️ Borrar» en cada una, más el bloque para AGREGAR
                 nuevas (pedido del jefe, 2026-09-16). Va dentro del formulario a propósito: así los
                 botones mandan el formulario completo —con la contraseña— igual que todo lo demás. */ ?>
        <div class="mt__modal" id="mtVisor" aria-hidden="true">
            <div class="mt__modal-fondo" data-mt-cerrar="1"></div>
            <div class="mt__modal-caja" role="dialog" aria-modal="true" aria-label="Todas las fotos de tu tienda">
                <div class="mt__modal-head">
                    <h3>📸 Todas las fotos de tu tienda (<?= count($fotos) ?>)</h3>
                    <button type="button" class="mt__modal-x" data-mt-cerrar="1" aria-label="Cerrar">✕</button>
                </div>
                <p class="mt__alerta" id="mtVisorAlerta">
                    🔐 Falta tu contraseña. Escríbela abajo, en <strong>«Tu contraseña (obligatoria)»</strong>,
                    y vuelve a tocar el botón.
                </p>
                <div class="mt__modal-cuerpo">
                    <?php if ($fotos): ?>
                        <div class="mt__fotos">
                            <?php foreach ($fotos as $i => $f): ?>
                                <div class="mt__foto<?= $i === 0 ? ' mt__foto--portada' : '' ?>" style="cursor:default">
                                    <?php if ($i === 0): ?><span class="mt__sello">PORTADA</span><?php endif; ?>
                                    <?= img_tag((string)$f['ruta'], (string)$negocio['nombre'], ['sizes' => '200px']) ?>
                                    <div class="mt__foto-acc">
                                        <?php if ($i !== 0): ?>
                                            <button type="submit" class="mt__portada" name="accion" value="foto_portada"
                                                    data-foto="<?= (int)$f['id'] ?>">⭐ Portada</button>
                                        <?php else: ?>
                                            <button type="button" class="mt__portada" disabled
                                                    style="opacity:.75;cursor:default">⭐ Es tu portada</button>
                                        <?php endif; ?>
                                        <button type="submit" class="mt__borrar" name="accion" value="foto_borrar"
                                                data-foto="<?= (int)$f['id'] ?>">🗑️ Borrar</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="mt__ayuda">Todavía no tienes fotos. Elige las de tu local, tus productos y tu letrero.</p>
                    <?php endif; ?>

                    <div class="mt__modal-add">
                        <label for="mtFotos">📷 Agregar fotos nuevas</label>
                        <input type="file" id="mtFotos" name="fotos[]" accept="image/*" multiple>
                        <p class="mt__ayuda">Puedes elegir varias de una vez (hasta 8). Se guardan comprimidas
                           (WebP) y se ven nítidas en el celular.</p>
                        <button type="submit" class="btn" name="accion" value="foto_subir" style="margin-top:10px">📸 Subir estas fotos</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt__firma">
            <h3>🔐 Tu contraseña (obligatoria)</h3>
            <div class="mt__firma-wsp">
                <span aria-hidden="true">📲</span>
                <span><strong>Tu contraseña está en tu WhatsApp.</strong> Es la que te llegó (o la que te
                      guardaste) cuando creaste tu tienda, y es la misma con la que entras a DeChimbote.com.</span>
            </div>
            <p>🔑 <strong>Memorízala</strong>: te la voy a pedir <strong>siempre</strong> que quieras hacer
               un cambio en tu tienda (guardar datos, subir o borrar fotos, cambiar la portada). Así nadie
               más toca tu tienda, ni aunque dejes el celular abierto.</p>
            <p>¿No la recuerdas? Pide una nueva aquí:
               <a href="<?= e(url('recuperar')) ?>" target="_blank" rel="noopener">recuperar mi contraseña</a>
               (te llega por WhatsApp).
               <?php if (trim((string)($usuario['telefono'] ?? '')) !== ''): ?>
                   <br>Tu usuario para entrar es tu número: <strong><?= e((string)$usuario['telefono']) ?></strong>.
               <?php endif; ?>
            </p>
            <input type="password" name="password" id="mtPass" required autocomplete="current-password"
                   style="margin-bottom:12px" placeholder="Tu contraseña">
            <button type="submit" class="mt__guardar" name="accion" value="guardar_tienda">💾 Guardar los cambios</button>
        </div>
    </form>

    <p class="mt__ayuda" style="margin-top:16px">
        ¿Quieres borrar tu tienda? Se hace desde tu panel, con tu contraseña:
        <a href="<?= e(url('panel.php')) ?>">Mis negocios → 🗑️ Eliminar</a>.
    </p>
</div>

<script>
/* ==================================================================================================
 * 1) LAS FOTOS
 *    · Los botones del visor («⭐ Portada» / «🗑️ Borrar») son botones del MISMO formulario: al tocarlos
 *      se guarda en el campo escondido el id de ESA foto y el formulario entero —con la contraseña—
 *      viaja. Son botones `submit` de verdad (no `form.submit()`): así el navegador EXIGE la contraseña.
 *    · Si la contraseña está vacía, se avisa DENTRO del visor (el campo real está abajo del todo y el
 *      visor lo tapa: el aviso del navegador no se vería).
 * ================================================================================================ */
(function () {
    'use strict';
    var f = document.getElementById('mtForm');
    if (!f) return;
    var oAccion  = f.querySelector('input[name="accion"]');
    var pass     = document.getElementById('mtPass');
    var visor    = document.getElementById('mtVisor');
    var alerta   = document.getElementById('mtVisorAlerta');

    Array.prototype.forEach.call(f.querySelectorAll('button[name="accion"][data-foto]'), function (b) {
        b.addEventListener('click', function (ev) {
            if (pass && pass.value.trim() === '') {
                ev.preventDefault();
                if (alerta) alerta.classList.add('is-viendo');
                if (pass) { pass.scrollIntoView({ block: 'center' }); pass.focus({ preventScroll: true }); }
                return;
            }
            var v = b.getAttribute('value') || '';
            if (oAccion) oAccion.value = v;
            var campo = f.querySelector('input[name="' + v + '"]');
            if (campo) campo.value = b.getAttribute('data-foto') || '';
        });
    });

    /* El visor: se abre tocando cualquier foto y se cierra con la ✕, tocando el fondo o con Escape. */
    if (!visor) return;
    function abrirVisor() {
        visor.classList.add('is-abierto');
        visor.setAttribute('aria-hidden', 'false');
        document.documentElement.style.overflow = 'hidden';   // el fondo no se mueve
        var x = visor.querySelector('.mt__modal-x');
        if (x) x.focus({ preventScroll: true });
    }
    function cerrarVisor() {
        visor.classList.remove('is-abierto');
        visor.setAttribute('aria-hidden', 'true');
        document.documentElement.style.overflow = '';
        if (alerta) alerta.classList.remove('is-viendo');
    }
    Array.prototype.forEach.call(document.querySelectorAll('[data-mt-visor]'), function (b) {
        b.addEventListener('click', abrirVisor);
    });
    visor.addEventListener('click', function (ev) {
        var t = ev.target;
        while (t && t.nodeType === 1) {
            if (t.hasAttribute && t.hasAttribute('data-mt-cerrar')) { cerrarVisor(); return; }
            t = t.parentNode;
        }
    });
    document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && visor.classList.contains('is-abierto')) cerrarVisor();
    });

    /* ==============================================================================================
     * 2) 📞 EL MODAL DE LOS OTROS NÚMEROS
     *    El principal vive en la tarjeta (en rojo) y los demás, en el modal: cada fila es un número y
     *    su uso. «➕ Agregar otro número» añade filas hasta el tope (principal + 6). La tarjeta muestra
     *    los chips de lo que el dueño va escribiendo, para que vea lo que va a guardar.
     * ============================================================================================ */
    var telModal = document.getElementById('mtTelModal');
    var telFilas = document.getElementById('mtTelFilas');
    var telMas   = document.getElementById('mtTelMas');
    var telTope  = document.getElementById('mtTelTope');
    var telLista = document.getElementById('mtTelLista');
    var TEL_MAX  = 6;                 // números secundarios como máximo
    var telN     = telFilas ? telFilas.children.length : 0;   // siguiente índice libre

    function telContar() { return telFilas ? telFilas.querySelectorAll('input[name^="extra_numero"]').length : 0; }

    /* Los chips de la tarjeta se rehacen con lo que hay escrito en el modal. */
    function telPintarChips() {
        if (!telLista || !telFilas) return;
        var html = '';
        Array.prototype.forEach.call(telFilas.querySelectorAll('.mt__tel'), function (fila, i) {
            var num = fila.querySelector('input[name^="extra_numero"]');
            var sel = fila.querySelector('select[name^="extra_tipo"]');
            var v   = num ? (num.value || '').replace(/\D+/g, '') : '';
            if (v === '') return;
            var etq = sel && sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : '';
            html += '<span class="mt__chip"><span>' + etq.split(' ')[0] + '</span><strong>' + v +
                    '</strong><span class="mt__chip-sub">' + etq.replace(/^\S+\s*/, '') + '</span></span>';
        });
        telLista.innerHTML = html || '<p class="mt__ayuda" style="margin:0">Todavía no agregaste otros números.</p>';
    }

    /* Renumera los rótulos («Número 2, 3, 4…») según el orden de las filas. */
    function telRenumerar() {
        if (!telFilas) return;
        Array.prototype.forEach.call(telFilas.querySelectorAll('.mt__tel'), function (fila, i) {
            var lab = fila.querySelector('label');
            if (lab) lab.textContent = 'Número ' + (i + 2);
        });
        var llenas = telContar();
        if (telMas)  telMas.style.display = (llenas >= TEL_MAX) ? 'none' : '';
        if (telTope) telTope.style.display = (llenas >= TEL_MAX) ? '' : 'none';
    }

    function telEnganchar(fila) {
        Array.prototype.forEach.call(fila.querySelectorAll('input,select'), function (c) {
            c.addEventListener('input', function () { telPintarChips(); });
            c.addEventListener('change', function () { telPintarChips(); });
        });
    }
    if (telFilas) Array.prototype.forEach.call(telFilas.querySelectorAll('.mt__tel'), telEnganchar);
    telRenumerar();
    telPintarChips();

    if (telMas && telFilas) telMas.addEventListener('click', function () {
        if (telContar() >= TEL_MAX) return;
        var modelo = telFilas.querySelector('.mt__tel');
        if (!modelo) return;
        var fila = modelo.cloneNode(true);
        var num  = fila.querySelector('input[name^="extra_numero"]');
        var sel  = fila.querySelector('select[name^="extra_tipo"]');
        if (num) { num.name = 'extra_numero[' + (telN) + ']'; num.id = 'mtExtra' + telN; num.value = ''; }
        if (sel) { sel.name  = 'extra_tipo[' + (telN) + ']'; sel.selectedIndex = 0; }
        telN++;
        telFilas.appendChild(fila);
        telEnganchar(fila);
        telRenumerar();
        telPintarChips();
        if (num) num.focus({ preventScroll: true });
    });

    /* ==============================================================================================
     * 3) 🕒 EL MODAL DEL HORARIO
     *    Las casillas arman UNA línea de texto (la que se guarda y se lee en la ficha), más lo que el
     *    dueño escriba en «otro». El campo real (`#mtHorario`) va escondido para no cambiar el POST.
     * ============================================================================================ */
    var horModal = document.getElementById('mtHorarioModal');
    var horCampo = document.getElementById('mtHorario');
    var horVer   = document.getElementById('mtHorarioVer');   // la tarjeta (lo que se leerá en la ficha)
    var horVerM  = document.getElementById('mtHorVer');       // el «Así lo verá el cliente» del modal
    var horCaja  = document.getElementById('mtHorCaja');
    var horOtra  = document.getElementById('mtHorOtra');
    var horOtro  = document.getElementById('mtHorOtro');

    function horArmar() {
        if (!horCampo) return '';
        var partes = [];
        Array.prototype.forEach.call(document.querySelectorAll('input[data-mt-hor]'), function (c) {
            if (c.checked) partes.push(c.getAttribute('data-mt-hor'));
        });
        var libre = horOtro ? (horOtro.value || '').trim() : '';
        if (horOtra && horOtra.checked && libre !== '') partes.push(libre);   // sin prefijo: el texto se guarda tal cual
        var txt = partes.join(' · ').slice(0, 255);
        horCampo.value = txt;
        if (horVer)  horVer.textContent  = txt !== '' ? txt : 'Todavía no pusiste tu horario.';
        if (horVerM) horVerM.textContent = txt !== '' ? txt : '—';
        return txt;
    }
    Array.prototype.forEach.call(document.querySelectorAll('input[data-mt-hor]'), function (c) {
        c.addEventListener('change', horArmar);
    });
    if (horOtra) horOtra.addEventListener('change', function () {
        if (horCaja) horCaja.style.display = horOtra.checked ? '' : 'none';
        if (horOtra.checked && horOtro) horOtro.focus({ preventScroll: true });
        horArmar();
    });
    if (horOtro) horOtro.addEventListener('input', function () {
        if (horOtra && !horOtra.checked) { horOtra.checked = true; if (horCaja) horCaja.style.display = ''; }
        horArmar();
    });
    horArmar();

    /* ==============================================================================================
     * 4) 📘 EL MODAL DE LAS REDES: los chips de la tarjeta se rehacen mientras escribe.
     * ============================================================================================ */
    var redLista = null;
    (function () {
        var tarjeta = document.getElementById('mtRedesBtn');
        if (!tarjeta) return;
        redLista = tarjeta.parentNode.querySelector('.mt__lista-tel');
        var campos = document.querySelectorAll('input[data-mt-red]');
        function pintar() {
            if (!redLista) return;
            var html = '';
            Array.prototype.forEach.call(campos, function (c) {
                var v = (c.value || '').trim();
                if (v === '') return;
                html += '<span class="mt__chip"><span>' + c.getAttribute('data-mt-red-ico') + '</span><strong>' +
                        c.getAttribute('data-mt-red-etq') + '</strong><span class="mt__chip-sub">' + v + '</span></span>';
            });
            redLista.innerHTML = html || '<p class="mt__ayuda" style="margin:0">Todavía no pusiste ninguna red.</p>';
        }
        Array.prototype.forEach.call(campos, function (c) {
            c.addEventListener('input', pintar);
            c.addEventListener('change', pintar);
        });
    })();

    /* ==============================================================================================
     * 5) EL MOTOR COMÚN DE LOS MODALES: abrir con su botón y cerrar con la ✕, el fondo o Escape.
     * ============================================================================================ */
    var modales = [
        { caja: telModal, abre: 'mtTelBtn',       cierra: 'data-mt-cerrar-tel' },
        { caja: horModal, abre: 'mtHorarioBtn',   cierra: 'data-mt-cerrar-hor' },
        { caja: document.getElementById('mtRedesModal'), abre: 'mtRedesBtn', cierra: 'data-mt-cerrar-red' }
    ];
    function abrirModal(m) {
        if (!m.caja) return;
        m.caja.classList.add('is-abierto');
        m.caja.setAttribute('aria-hidden', 'false');
        document.documentElement.style.overflow = 'hidden';
        var x = m.caja.querySelector('.mt__modal-x');
        if (x) x.focus({ preventScroll: true });
    }
    function cerrarModal(m) {
        if (!m.caja) return;
        m.caja.classList.remove('is-abierto');
        m.caja.setAttribute('aria-hidden', 'true');
        document.documentElement.style.overflow = '';
    }
    modales.forEach(function (m) {
        var b = document.getElementById(m.abre);
        if (b) b.addEventListener('click', function () { abrirModal(m); });
        if (!m.caja) return;
        m.caja.addEventListener('click', function (ev) {
            var t = ev.target;
            while (t && t.nodeType === 1) {
                if (t.hasAttribute && t.hasAttribute(m.cierra)) { cerrarModal(m); telPintarChips(); horArmar(); return; }
                t = t.parentNode;
            }
        });
    });
    document.addEventListener('keydown', function (ev) {
        if (ev.key !== 'Escape') return;
        modales.forEach(function (m) { if (m.caja && m.caja.classList.contains('is-abierto')) cerrarModal(m); });
    });
    /* Al mandar el formulario, el horario y los chips quedan al día aunque no se haya cerrado el modal. */
    f.addEventListener('submit', function () { horArmar(); telPintarChips(); });

    /* ==============================================================================================
     * 6) 📍 «TU UBICACIÓN»: el GPS del celular + la calle detectada (dirección inversa de OpenStreetMap,
     *    el mismo camino que ya usa Caminante). Lo capturado se guarda al tocar «💾 Guardar los cambios»
     *    (viaja en los campos escondidos `lat`/`lng`) y con eso la ficha pinta su mapa.
     * ============================================================================================ */
    var btn    = document.getElementById('mtUbiBtn');
    var estado = document.getElementById('mtUbiEstado');
    var caja   = document.getElementById('mtUbiCaja');
    var coord  = document.getElementById('mtUbiCoord');
    var direc  = document.getElementById('mtUbiDirec');
    var usar   = document.getElementById('mtUbiUsar');
    var eLat   = document.getElementById('mtLat');
    var eLng   = document.getElementById('mtLng');
    var eDir   = document.getElementById('mtDireccion');
    var eZona  = document.getElementById('mtDistrito');
    if (!btn || !eLat || !eLng) return;

    var DISTRITOS = <?= json_encode(array_map(static function ($d) {
        return ['id' => (int)$d['id'], 'nombre' => (string)$d['nombre']];
    }, $distritos), JSON_UNESCAPED_UNICODE) ?>;
    var detectada = '';   // la dirección que devolvió el mapa (para el botón «Usar esta dirección»)

    function decir(txt, mal) {
        if (!estado) return;
        estado.innerHTML = txt;
        estado.style.color = mal ? '#b91c1c' : '';
    }
    /* Sin tildes y en minúsculas, para comparar «Nuevo Chimbote» con lo que diga el mapa. */
    function plano(t) {
        return (t || '').toString().toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '').replace(/\s+/g, ' ').trim();
    }

    if (btn) btn.addEventListener('click', function () {
        if (!navigator.geolocation) {
            decir('😅 Tu navegador no me deja ver la ubicación. Escribe tu dirección a mano, no hay problema.', true);
            return;
        }
        btn.disabled = true;
        decir('📍 Buscando tu ubicación… aprieta «Permitir» si te lo pregunta.');
        navigator.geolocation.getCurrentPosition(function (pos) {
            btn.disabled = false;
            var lat = pos.coords.latitude, lng = pos.coords.longitude;
            var acc = (pos.coords && typeof pos.coords.accuracy === 'number') ? Math.round(pos.coords.accuracy) : 0;
            eLat.value = lat.toFixed(7);
            eLng.value = lng.toFixed(7);
            if (coord) coord.textContent = lat.toFixed(6) + ', ' + lng.toFixed(6) + (acc ? '  (±' + acc + ' m)' : '');
            if (caja) caja.classList.add('is-viendo');
            decir('✅ Ubicación capturada. Se guarda cuando toques <strong>💾 Guardar los cambios</strong>.');
            btn.textContent = '📍 Volver a capturar mi ubicación';

            /* La calle: dirección inversa de OpenStreetMap (gratis y sin claves). */
            if (direc) direc.textContent = '🔎 Buscando tu calle…';
            fetch('https://nominatim.openstreetmap.org/reverse?format=json&lat=' + lat + '&lon=' + lng)
                .then(function (r) { return r.json(); })
                .then(function (d) {
                    var a = (d && d.address) || {};
                    var via = [a.road || a.pedestrian || a.footway || a.path || '', a.house_number || ''].join(' ').trim();
                    var zona = a.suburb || a.neighbourhood || a.city_district || a.village || a.town || a.county || '';
                    var corta = [via, zona].filter(Boolean).join(', ');
                    detectada = corta || (d && d.display_name) || '';
                    if (direc) {
                        direc.textContent = detectada
                            ? '📍 ' + detectada
                            : 'No pude leer la calle, pero tu punto ya quedó capturado.';
                    }
                    /* ¿La dirección dice el distrito? Se propone solo (el dueño lo puede cambiar). */
                    var texto = plano([a.road, a.suburb, a.neighbourhood, a.city_district, a.city, a.town,
                                       a.village, a.county, a.state_district, d && d.display_name].join(' '));
                    var orden = DISTRITOS.slice().sort(function (x, y) {
                        return y.nombre.length - x.nombre.length;   // «Nuevo Chimbote» antes que «Chimbote»
                    });
                    for (var i = 0; i < orden.length; i++) {
                        if (texto.indexOf(plano(orden[i].nombre)) !== -1) {
                            if (eZona && eZona.value !== 'todos') eZona.value = String(orden[i].id);
                            decir('✅ Ubicación capturada en <strong>' + orden[i].nombre + '</strong> y ya te puse tu zona. Se guarda con <strong>💾 Guardar los cambios</strong>.');
                            break;
                        }
                    }
                    if (usar && detectada) {
                        usar.hidden = false;
                        var actual = eDir ? (eDir.value || '').trim() : '';
                        if (actual === '') { eDir.value = detectada; usar.hidden = true; }
                    }
                })
                .catch(function () {
                    if (direc) direc.textContent = 'Tu punto quedó capturado, pero no pude leer la calle. Escríbela a mano.';
                });
        }, function (err) {
            btn.disabled = false;
            decir('😅 No me diste permiso para la ubicación' + (err && err.message ? ' (' + err.message + ')' : '')
                + '. Escribe tu dirección a mano y todo sigue igual.', true);
        }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 30000 });
    });

    if (usar) usar.addEventListener('click', function () {
        if (eDir && detectada) { eDir.value = detectada; usar.hidden = true; }
    });
})();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
