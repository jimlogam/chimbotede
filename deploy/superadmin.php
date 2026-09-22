<?php
/**
 * superadmin.php — Panel del Súper Administrador
 * ============================================================
 * Acceso exclusivo para cuentas con rol 'admin'.
 * Secciones:
 *   reclamos    -> solicitudes "quiero reclamar mi negocio" (aprobar/rechazar/eliminar)
 *   postulantes -> candidatos de "Trabaja con nosotros"
 *   tiendas     -> listar y eliminar negocios que violen políticas
 *   productos   -> listar y eliminar productos/servicios
 *   usuarios    -> crear, listar, suspender o eliminar cuentas
 * Optimizado para móvil.
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/fuzzy_cache.php';   // refresco instantáneo del buscador
require_once __DIR__ . '/includes/opiniones.php';     // 💬 opiniones anónimas + 🚩 sus reportes
require_once __DIR__ . '/includes/clave_recuperar.php'; // 🔑 «olvidé mi contraseña» (pedidos + clave nueva)
require_once __DIR__ . '/includes/invitaciones.php';  // 📨 invitaciones a las tiendas (botón de la pestaña 🏬)
requiere_login();
if (!es_admin()) { http_response_code(403); die('Acceso denegado. Requiere ser administrador.'); }

$admin = usuario_actual();
$seccion = $_GET['seccion'] ?? 'resumen';
$secciones_validas = ['resumen','records','estadisticas','avisos','reclamos','reportes','opiniones','postulantes','tiendas','productos','usuarios','banners','cerca','chatbot','maestro','supremo','canciones'];
if (!in_array($seccion, $secciones_validas)) $seccion = 'resumen';

// ====== Acciones POST ======
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();
    $accion = $_POST['accion'] ?? '';
    $id = (int)($_POST['id'] ?? 0);
    $pdo = db();

    if ($accion === 'reclamo_aprobar') {
        $r = reclamo_por_id($id);
        if ($r) {
            // Transferir el negocio al usuario que reclama (si tiene cuenta) y marcar aprobado
            if ($r['usuario_id']) {
                $pdo->prepare("UPDATE directorio_negocios SET dueno_id=? WHERE id=?")->execute([$r['usuario_id'], $r['negocio_id']]);
            }
            $pdo->prepare("UPDATE directorio_reclamos SET estado='aprobado', atendido_por=?, atendido_en=NOW() WHERE id=?")->execute([$admin['id'], $id]);
            flash('Solicitud aprobada. Negocio transferido al reclamante.', 'exito');
        }
    } elseif ($accion === 'reclamo_rechazar') {
        $pdo->prepare("UPDATE directorio_reclamos SET estado='rechazado', atendido_por=?, atendido_en=NOW() WHERE id=?")->execute([$admin['id'], $id]);
        flash('Solicitud rechazada.', 'warning');
    } elseif ($accion === 'reclamo_borrar') {
        $pdo->prepare("DELETE FROM directorio_reclamos WHERE id=?")->execute([$id]);
        flash('Solicitud de reclamo eliminada.', 'info');
    } elseif ($accion === 'postulante_estado') {
        $est = trim($_POST['estado'] ?? '');
        if (in_array($est, ['nuevo','en_revision','contactado','descartado'])) {
            $pdo->prepare("UPDATE directorio_postulantes SET estado=? WHERE id=?")->execute([$est, $id]);
            flash('Estado del postulante actualizado.', 'exito');
        }
    } elseif ($accion === 'tienda_eliminar') {
        eliminar_negocio_completo($pdo, $id);
        flash('Tienda y todos sus datos eliminados.', 'exito');
    } elseif ($accion === 'tienda_estado') {
        $est = trim($_POST['estado'] ?? '');
        if (in_array($est, ['pendiente', 'activo', 'inactivo', 'rechazado'], true)) {
            $pdo->prepare("UPDATE directorio_negocios SET estado=? WHERE id=?")->execute([$est, $id]);
            $etiqueta = ['pendiente' => '⏳ Pendiente', 'activo' => '🟢 Aprobado / público', 'inactivo' => '🙈 Oculto', 'rechazado' => '⛔ Rechazado'][$est];
            flash('Estado de la tienda actualizado a ' . $etiqueta . '.', 'exito');
        } else {
            flash('Estado inválido.', 'warning');
        }
    } elseif ($accion === 'tienda_invitar' || $accion === 'tienda_invitar_deshacer') {
        // 📨 INVITACIÓN A LA TIENDA (pedido del jefe, 2026-09-17): el botón que va debajo de
        // cada tarjeta abre WhatsApp con el mensaje ya escrito y este POST deja apuntado el
        // envío (es lo que decide el COLOR del botón: ⚫0 · 🟠1 · 🟢2 · 🔵3+). El ↺ deshace el
        // último envío por si se tocó de más.
        // ⚠️ La llamada llega por `fetch` desde la tarjeta: se responde JSON y se sale ANTES
        // del `flash()`/redirect, para no dejar un aviso raro en la siguiente pantalla.
        $r = ($accion === 'tienda_invitar')
            ? invitacion_enviar($id, (int)($admin['id'] ?? 0))
            : invitacion_deshacer($id);
        if (invitacion_es_ajax()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode($r);
            exit;
        }
        flash((string)($r['msg'] ?? ''), !empty($r['ok']) ? 'exito' : 'warning');
    } elseif ($accion === 'tienda_revisar' || $accion === 'tienda_revisar_quitar') {
        // ✅ REVISADOS (pedido del jefe, 2026-09-18): marca la tienda como YA REVISADA (o lo deshace).
        // Es lo que alimenta el filtro «✅ Revisados» de la pestaña 🏬 Tiendas: con 1 708 tiendas, el jefe
        // necesita ver de un golpe cuáles ya pasaron por la revisión. La marca es la FECHA (`revisado_en`).
        $hay_columna = false;
        try { $pdo->query('SELECT revisado_en FROM directorio_negocios LIMIT 1'); $hay_columna = true; }
        catch (Throwable $e) { $hay_columna = false; }
        if (!$hay_columna) {
            flash('Falta la columna `revisado_en`: corre la migración de «Revisados».', 'warning');
        } elseif ($accion === 'tienda_revisar') {
            $pdo->prepare("UPDATE directorio_negocios SET revisado_en = NOW() WHERE id = ?")->execute([$id]);
            flash('✅ Tienda marcada como REVISADA.', 'exito');
        } else {
            $pdo->prepare("UPDATE directorio_negocios SET revisado_en = NULL WHERE id = ?")->execute([$id]);
            flash('↩️ Se quitó la marca de REVISADA.', 'info');
        }
    } elseif ($accion === 'producto_eliminar') {
        $pdo->prepare("DELETE FROM directorio_producto_fotos WHERE producto_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM directorio_vistas WHERE producto_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM directorio_servicios WHERE id=?")->execute([$id]);
        flash('Producto eliminado.', 'exito');
    } elseif ($accion === 'usuario_suspender') {
        $pdo->prepare("UPDATE directorio_usuarios SET activo=0 WHERE id=?")->execute([$id]);
        flash('Usuario suspendido (no puede iniciar sesión).', 'exito');
    } elseif ($accion === 'usuario_activar') {
        $pdo->prepare("UPDATE directorio_usuarios SET activo=1 WHERE id=?")->execute([$id]);
        flash('Usuario reactivado.', 'exito');
    } elseif ($accion === 'usuario_eliminar') {
        // Elimina cuenta y libera sus negocios (no borra negocios reales por seguridad)
        $pdo->prepare("UPDATE directorio_negocios SET dueno_id=NULL WHERE dueno_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM directorio_sesiones WHERE usuario_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM directorio_historial_busqueda WHERE usuario_id=?")->execute([$id]);
        $pdo->prepare("DELETE FROM directorio_usuarios WHERE id=?")->execute([$id]);
        flash('Cuenta eliminada. Sus negocios quedaron sin dueño.', 'exito');
    } elseif ($accion === 'usuario_crear') {
        // ➕ CREAR USUARIO A MANO (2026-09-21, orden del jefe: «crea usuarios»).
        // Hasta hoy las cuentas SOLO nacían solas (registro, Google, la invitación de tienda o
        // El maestro 🛠️) y el jefe no tenía ninguna puerta para crear una: la pestaña 👥 Usuarios
        // únicamente administraba las que ya existían (premium, suspender, clave, eliminar).
        // Se entra **con el correo o con el número de WhatsApp** (igual que `login()`): si se deja
        // el correo en blanco y hay teléfono, el correo interno es `<numero>@dechimbote.com`, que es
        // el formato que ya usa todo el sitio para las cuentas de tienda.
        // La clave: si el jefe la escribe se usa esa; si no, se genera (3 letras + 1 número, como
        // El maestro) y se muestra **UNA sola vez** abajo, con el mensaje listo para WhatsApp.
        $u_nombre = trim((string)($_POST['u_nombre'] ?? ''));
        $u_email  = trim((string)($_POST['u_email'] ?? ''));
        $u_tel    = (string)preg_replace('/\D+/', '', (string)($_POST['u_telefono'] ?? ''));
        $u_tipo   = (string)($_POST['u_tipo'] ?? 'cliente');
        $u_clave  = trim((string)($_POST['u_clave'] ?? ''));
        if (!in_array($u_tipo, ['cliente', 'dueno', 'admin'], true)) $u_tipo = 'cliente';

        $errores = [];
        if (mb_strlen($u_nombre) < 2) $errores[] = 'Escribe el nombre de la cuenta (al menos 2 letras).';
        if ($u_email === '' && $u_tel === '') $errores[] = 'Ponle un correo o un número de WhatsApp: es con lo que va a entrar.';
        if ($u_email !== '' && !filter_var($u_email, FILTER_VALIDATE_EMAIL)) $errores[] = 'Ese correo no es válido.';
        if ($u_tel !== '' && strlen($u_tel) < 6) $errores[] = 'Ese número de WhatsApp no parece válido.';
        if ($u_clave !== '' && strlen($u_clave) < 6) $errores[] = 'La contraseña debe tener al menos 6 caracteres (o déjala en blanco y la genero yo).';

        if (empty($errores) && $u_email === '') $u_email = $u_tel . '@dechimbote.com';

        if (empty($errores)) {
            // ⚠️ LA TRAMPA QUE COSTÓ UN ERROR 500 (2026-09-21, comprobado con sonda): NO se compara
            // un parámetro contra un literal en el SQL (`? <> ''`). MariaDB contesta
            // «Illegal mix of collations (utf8mb4_general_ci,COERCIBLE) and (utf8mb4_unicode_ci,COERCIBLE)
            // for operation '<>'» y la página se cae con 500. La decisión se toma en PHP (lo mismo que
            // ya se aprendió en includes/telegram_subs.php), y de paso va en try/catch: una consulta
            // de más NUNCA puede tumbar el alta.
            try {
                $existe = false;
                if ($u_tel !== '') {
                    $ya = $pdo->prepare("SELECT id, nombre FROM directorio_usuarios WHERE telefono = ? LIMIT 1");
                    $ya->execute([$u_tel]);
                    $existe = $ya->fetch();
                }
                if (!$existe) {
                    $ya = $pdo->prepare("SELECT id, nombre FROM directorio_usuarios WHERE email = ? LIMIT 1");
                    $ya->execute([$u_email]);
                    $existe = $ya->fetch();
                }
                if ($existe) {
                    $errores[] = 'Ya existe una cuenta con ese correo o ese número: «' . (string)$existe['nombre'] . '» (#' . (int)$existe['id'] . ').';
                }
            } catch (Throwable $e) {
                error_log('usuario_crear (duplicado): ' . $e->getMessage());   // no se frena el alta
            }
        }

        if (!empty($errores)) {
            foreach ($errores as $err) flash($err, 'error');
        } else {
            $clave  = $u_clave !== '' ? $u_clave : clave_generar();
            $hash   = password_hash($clave, PASSWORD_BCRYPT, ['cost' => defined('HASH_COST') ? HASH_COST : 10]);
            $activo = isset($_POST['u_activo']) ? 1 : 0;   // marcado = ya puede entrar
            try {
                $pdo->prepare("INSERT INTO directorio_usuarios (nombre, email, password_hash, telefono, tipo, activo)
                               VALUES (?,?,?,?,?,?)")
                    ->execute([$u_nombre, $u_email, $hash, ($u_tel !== '' ? $u_tel : null), $u_tipo, $activo]);
                $nuevo_id = (int)$pdo->lastInsertId();

                // 🔔 Aviso al jefe (el mismo canal que usan las otras cuentas nuevas)
                aviso('usuario_nuevo', [
                    'nombre'     => $u_nombre,
                    'email'      => $u_email,
                    'via'        => 'creada a mano en Súper Admin (' . $u_tipo . ($u_tel !== '' ? ' · WhatsApp ' . $u_tel : '') . ')',
                    'usuario_id' => $nuevo_id,
                    'total'      => (int)$pdo->query('SELECT COUNT(*) FROM directorio_usuarios')->fetchColumn(),
                    'clave'      => 'user:' . $u_email,
                    'resumen'    => 'alta manual: ' . $u_email,
                ]);

                // 🔑 La clave se muestra UNA sola vez (mismo cartel que «Restablecer contraseña»),
                //    con su mensaje listo para copiar y el botón verde de WhatsApp.
                $usuario_visible = ($u_tel !== '' ? $u_tel : $u_email);
                $mensaje = implode("\n", [
                    'Hola 👋 Te escribo de dechimbote.com.',
                    'Ya creé tu cuenta' . ($u_tipo === 'dueno' ? ' de dueño de tienda 🏪' : ($u_tipo === 'admin' ? ' de administrador 🔐' : ' 🎉')) . '.',
                    '🔑 Tu usuario es ' . ($u_tel !== '' ? 'tu número de WhatsApp' : 'tu correo') . ': ' . $usuario_visible,
                    '🔒 Tu contraseña es: ' . $clave,
                    '👉 Entra aquí: ' . url('login.php'),
                    '⚠️ Guárdala AHORA en este chat: no se vuelve a mostrar.',
                ]);
                $_SESSION['clave_nueva'] = [
                    'ok'       => true,
                    'nuevo'    => true,          // el cartel cambia de texto (es cuenta nueva, no cambio de clave)
                    'clave'    => $clave,
                    'nombre'   => $u_nombre,
                    'usuario'  => $usuario_visible,
                    'telefono' => $u_tel,
                    'email'    => $u_email,
                    'tienda'   => '',
                    'mensaje'  => $mensaje,
                    'wa'       => clave_whatsapp_url(['usuario' => $usuario_visible, 'telefono' => $u_tel, 'clave' => $clave, 'tienda' => '']),
                ];
                flash('➕ Cuenta creada: ' . $u_nombre . ' (' . $u_tipo . '). La clave está abajo, para copiarla o mandársela por WhatsApp.', 'exito');
            } catch (Throwable $e) {
                error_log('usuario_crear: ' . $e->getMessage());
                flash('No pude crear la cuenta: ' . $e->getMessage(), 'error');
            }
        }
        redirect('superadmin.php?seccion=usuarios');
    } elseif ($accion === 'usuario_rol') {
        // 🎭 CAMBIAR EL ROL DE UNA CUENTA (2026-09-21, orden del jefe: «cambiale el rol a admin»).
        // Antes el rol SOLO se podía poner al crear la cuenta: no había ninguna forma de cambiarlo
        // después. Ahora cada fila trae su selector (cliente · dueño · admin) con este botón.
        $tipo_nuevo = (string)($_POST['tipo'] ?? '');
        $st = $pdo->prepare("SELECT id, nombre, tipo FROM directorio_usuarios WHERE id = ? LIMIT 1");
        $st->execute([$id]);
        $u_rol = $st->fetch();
        if (!$u_rol) {
            flash('No encontré esa cuenta.', 'error');
        } elseif (!in_array($tipo_nuevo, ['cliente', 'dueno', 'admin'], true)) {
            flash('Ese rol no existe (son cliente, dueño y admin).', 'error');
        } else {
            // 🛡️ Red de seguridad: el sitio NUNCA se queda sin administrador activo (si no, nadie
            // podría volver a entrar al Súper Admin). Para dejar sin rol al último admin hay que
            // crear antes otro.
            $n_admin = (int)$pdo->query("SELECT COUNT(*) FROM directorio_usuarios WHERE tipo = 'admin' AND activo = 1")->fetchColumn();
            if ((string)$u_rol['tipo'] === 'admin' && $tipo_nuevo !== 'admin' && $n_admin <= 1) {
                flash('«' . (string)$u_rol['nombre'] . '» es el ÚNICO administrador activo: si le quitas el rol, nadie podría volver a entrar al Súper Admin. Crea otro admin primero.', 'error');
            } else {
                $pdo->prepare("UPDATE directorio_usuarios SET tipo = ? WHERE id = ?")->execute([$tipo_nuevo, $id]);
                flash('🎭 «' . (string)$u_rol['nombre'] . '» ahora es ' . ($tipo_nuevo === 'dueno' ? 'dueño de tienda' : $tipo_nuevo) . '.', 'exito');
            }
        }
        redirect('superadmin.php?seccion=usuarios');
    } elseif ($accion === 'usuario_plan') {
        $plan = (($_POST['plan'] ?? '') === PLAN_PREMIUM) ? PLAN_PREMIUM : PLAN_GRATIS;
        $dias = max(0, (int)($_POST['dias'] ?? 0));
        if ($plan === PLAN_PREMIUM) {
            if ($dias > 0) {
                $stmt = $pdo->prepare("UPDATE directorio_usuarios SET plan = ?, plan_desde = IFNULL(plan_desde, NOW()), plan_hasta = DATE_ADD(NOW(), INTERVAL ? DAY) WHERE id = ?");
                $stmt->execute([PLAN_PREMIUM, $dias, $id]);
                flash('Usuario marcado como PREMIUM por ' . $dias . ' días.', 'exito');
            } else {
                $pdo->prepare("UPDATE directorio_usuarios SET plan = ?, plan_desde = IFNULL(plan_desde, NOW()), plan_hasta = NULL WHERE id = ?")->execute([PLAN_PREMIUM, $id]);
                flash('Usuario marcado como PREMIUM (sin vencimiento).', 'exito');
            }
        } else {
            $pdo->prepare("UPDATE directorio_usuarios SET plan = ?, plan_hasta = NULL WHERE id = ?")->execute([PLAN_GRATIS, $id]);
            flash('Plan del usuario restablecido a gratis.', 'info');
        }
    } elseif ($accion === 'usuario_clave_nueva') {
        // 🔑 LA CLAVE NUEVA (módulo del 2026-09-16): el dueño que olvidó su contraseña pide desde
        // `/recuperar` y aquí se le resuelve. La clave en claro se guarda EN LA SESIÓN y se muestra
        // UNA sola vez en la pantalla (luego solo queda el hash, como siempre).
        $r = clave_restablecer($id);
        if (empty($r['ok'])) {
            flash((string)($r['error'] ?? 'No pude cambiar la contraseña.'), 'warning');
        } else {
            $r['mensaje'] = clave_mensaje($r);
            $r['wa']      = clave_whatsapp_url($r);
            $_SESSION['clave_nueva'] = $r;              // se lee y se borra al pintar 👥 Usuarios
            $cerrados = claves_atender_usuario($id, (int)$admin['id']);
            flash('🔑 Contraseña nueva para ' . $r['nombre'] . '. Está abajo, para copiarla o mandársela por WhatsApp.'
                  . ($cerrados > 0 ? ' (Pedido atendido ✅)' : ''), 'exito');
        }
        redirect('superadmin.php?seccion=usuarios');
    } elseif ($accion === 'clave_pedido_descartar') {
        // 🙈 Un pedido que no hay que atender (número equivocado, broma): sale de la lista.
        clave_pedido_descartar($id, (int)$admin['id']);
        flash('Pedido descartado.', 'info');
        redirect('superadmin.php?seccion=usuarios');
    } elseif ($accion === 'banner_crear' || $accion === 'banner_editar') {
        $datos = [
            'id'            => $accion === 'banner_editar' ? $id : 0,
            'titulo'        => trim($_POST['titulo'] ?? ''),
            'texto'         => trim($_POST['texto'] ?? ''),
            'imagen'        => trim($_POST['imagen'] ?? ''),
            'tema'          => trim($_POST['tema'] ?? ''),
            // 🔎 Término de búsqueda del sitio: si se llena, el clic del banner lleva a
            // buscar.php?q=<término> (manda sobre el enlace externo y sobre el modal).
            'busqueda'      => trim($_POST['busqueda'] ?? ''),
            'enlace'        => trim($_POST['enlace'] ?? ''),
            'fecha_inicio'  => trim($_POST['fecha_inicio'] ?? ''),
            'fecha_fin'     => trim($_POST['fecha_fin'] ?? ''),
            'franjas'       => (array)($_POST['franjas'] ?? []),
            'rubros'        => (array)($_POST['rubros'] ?? []),
            'activo'        => !empty($_POST['activo']),
            'orden'         => (int)($_POST['orden'] ?? 0),
        ];
        if ($datos['titulo'] === '' || $datos['imagen'] === '') {
            flash('Faltan el título o la imagen del banner.', 'warning');
        } else {
            banners_asegurar_busqueda();   // la columna `busqueda` se crea sola si falta
            banner_guardar($datos);
            flash($accion === 'banner_crear' ? '✅ Banner creado y programado.' : '✅ Banner actualizado.', 'exito');
        }
    } elseif ($accion === 'banner_toggle') {
        $b = banner_por_id($id);
        if ($b) {
            $nuevo = $b['activo'] ? 0 : 1;
            $pdo->prepare("UPDATE directorio_banners SET activo=? WHERE id=?")->execute([$nuevo, $id]);
            flash($nuevo ? 'Banner activado (respetará vigencia y franjas).' : 'Banner oculto (deja de rotar).', 'exito');
        }
    } elseif ($accion === 'banner_eliminar') {
        banner_eliminar($id);
        flash('Banner y sus estadísticas eliminados.', 'exito');
    } elseif ($accion === 'avisos_guardar') {
        // 📱 Telegram: se guardan TODAS las casillas de una vez.
        // Lo que viene marcado = activado; lo que no viene marcado = desactivado.
        $marcados = array_map('strval', (array)($_POST['avisos'] ?? []));
        $encendidos = 0;
        $apagados   = 0;
        foreach (avisos_catalogo() as $tipo_cat => $info_cat) {
            $activo = in_array($tipo_cat, $marcados, true) ? 1 : 0;
            if (avisos_config_guardar($tipo_cat, $activo)) {
                $activo ? $encendidos++ : $apagados++;
            }
        }
        flash('✅ Guardado: ' . $encendidos . ' notificación(es) activada(s) y ' . $apagados . ' desactivada(s).', 'exito');
    } elseif ($accion === 'aviso_toggle') {
        // Encender / apagar un aviso del sistema 🔔 (sin tocar código)
        $tipo_aviso = trim($_POST['tipo'] ?? '');
        $activo     = (int)($_POST['activo'] ?? 0);
        if (avisos_config_guardar($tipo_aviso, $activo)) {
            $titulo = avisos_catalogo()[$tipo_aviso]['titulo'] ?? $tipo_aviso;
            flash(($activo ? '🔔 Encendido: ' : '🔕 Apagado: ') . $titulo, $activo ? 'exito' : 'info');
        } else {
            flash('No se pudo cambiar ese aviso.', 'warning');
        }
    } elseif ($accion === 'reporte_estado') {
        $est = trim($_POST['estado'] ?? '');
        $etiquetas = ['revisado' => 'Revisado', 'resuelto' => 'Resuelto', 'ignorado' => 'Ignorado', 'pendiente' => 'Pendiente'];
        if (reportes_cambiar_estado($id, $est, (int)$admin['id'])) {
            flash('Reporte marcado como ' . ($etiquetas[$est] ?? $est) . '.', 'exito');
        } else {
            flash('No se pudo actualizar el reporte.', 'warning');
        }
    } elseif ($accion === 'reporte_suspender') {
        // Suspende el negocio reportado y deja el reporte como resuelto.
        $neg_id = (int)($_POST['negocio_id'] ?? 0);
        if ($neg_id > 0) {
            $pdo->prepare("UPDATE directorio_negocios SET estado='inactivo' WHERE id=?")->execute([$neg_id]);
            reportes_cambiar_estado($id, 'resuelto', (int)$admin['id']);
            flash('Negocio suspendido (deja de mostrarse) y reporte marcado como resuelto.', 'exito');
        } else {
            flash('Falta el negocio del reporte.', 'warning');
        }
    } elseif ($accion === 'chatbot_guardar') {
        // 🥷 Ninja (el chat de ayuda): se guarda su configuración desde el panel (pedido del jefe,
        // 2026-09-13). Los valores van a `directorio_chatbot_ajustes` y mandan sobre los de fábrica.
        require_once __DIR__ . '/includes/chatbot_ajustes.php';
        $n = chatbot_ajustes_guardar((array)($_POST['chatbot'] ?? []));
        flash($n > 0 ? '✅ Configuración del chat guardada (' . $n . ' ajuste(s)).' : '⚠️ No se guardó nada: revisa los valores.',
              $n > 0 ? 'exito' : 'warning');
    } elseif ($accion === 'chatbot_diario_refrescar') {
        // 🌤️ Vuelve a pedir el clima y las noticias AHORA (en vez de esperar al día siguiente).
        require_once __DIR__ . '/includes/chatbot.php';   // trae chatbot_dir(), el día y los ajustes
        chatbot_diario_borrar();
        $nuevo = chatbot_diario(true);
        $ok = !empty($nuevo['clima']) || !empty($nuevo['noticias']);
        flash($ok ? '✅ Clima y noticias actualizados' . (!empty($nuevo['fuente_noticias']) ? ' (fuente: ' . $nuevo['fuente_noticias'] . ')' : '') . '.'
                  : '⚠️ No se pudo consultar el clima/noticias ahora mismo.',
              $ok ? 'exito' : 'warning');
    } elseif ($accion === 'chatbot_cuota_limpiar') {
        // 🔢 Devolverle las preguntas de hoy a todo el mundo (se borran los contadores del día).
        require_once __DIR__ . '/includes/chatbot.php';
        $borrados = 0;
        foreach ((array)@glob(chatbot_dir('cuenta') . '/*.txt') as $f) { if (@unlink($f)) $borrados++; }
        flash('🔢 Listo: se devolvieron las preguntas de hoy a ' . $borrados . ' persona(s).', 'exito');
    } elseif ($accion === 'stats_instalar') {
        // 📈 Estadísticas: crea las 2 tablas desde el propio panel (sin subir archivos)
        require_once __DIR__ . '/includes/estadisticas.php';
        $r = stats_instalar();
        flash($r['ok'] ? '✅ ' . $r['msg'] : '⚠️ ' . $r['msg'], $r['ok'] ? 'exito' : 'warning');
    } elseif ($accion === 'stats_limpiar') {
        // 🧹 Borra eventos y sesiones viejos (retención)
        require_once __DIR__ . '/includes/estadisticas.php';
        [$ev, $se] = stats_limpiar();
        flash('🧹 Limpieza hecha: ' . (int)$ev . ' evento(s) y ' . (int)$se . ' sesión(es) eliminados.', 'exito');
    } elseif ($accion === 'metricas_instalar') {
        // 🏆 RÉCORDS (2026-09-13): crea las 2 tablas nuevas (búsquedas y pedidos) desde el panel,
        // sin subir un migrador (el antivirus del hosting devuelve 404 a los `migrar_*.php`).
        require_once __DIR__ . '/includes/metricas.php';
        $r = metricas_instalar();
        flash(($r['ok'] ? '✅ ' : '⚠️ ') . $r['msg'], $r['ok'] ? 'exito' : 'warning');
    } elseif ($accion === 'metricas_sembrar') {
        // 🧺 RÉCORDS: trae el histórico del registro de avisos del Telegram (lo que el sitio ya
        // medía pero no guardaba en la base: cada búsqueda y cada pedido con su fecha).
        require_once __DIR__ . '/includes/metricas.php';
        $r = metricas_sembrar(180);
        flash(($r['ok'] ? '✅ ' : '⚠️ ') . $r['msg'], $r['ok'] ? 'exito' : 'warning');
    } elseif ($accion === 'opiniones_instalar') {
        // 💬 OPINIONES (2026-09-14): crea la tabla de reportes de opiniones sin subir un migrador
        // (el antivirus del hosting devuelve 404 a los `migrar_*.php`). Idempotente.
        $ok = opiniones_instalar();
        flash($ok ? '✅ Tablas de opiniones listas.' : '⚠️ No se pudieron preparar las tablas de opiniones.',
              $ok ? 'exito' : 'warning');
    } elseif ($accion === 'opiniones_sembrar') {
        // 🌱 Siembra las 3 opiniones con contexto de las últimas 100 tiendas (idempotente:
        // si una opinión ya está puesta, no se repite).
        $r = opiniones_sembrar();
        flash(($r['ok'] ? '✅ ' : '⚠️ ') . $r['msg'], $r['ok'] ? 'exito' : 'warning');
    } elseif ($accion === 'opinion_aprobar') {
        // ✅ El botón APROBAR de la lista de opiniones (pedido del jefe): la deja publicada.
        if (opinion_aprobar($id)) {
            flash('✅ Opinión aprobada: se ve en la ficha.', 'exito');
        } else {
            flash('⚠️ No se pudo aprobar esa opinión.', 'warning');
        }
    } elseif ($accion === 'opinion_ocultar') {
        // 🙈 Ocultar sin borrar: deja de verse en la ficha, pero se conserva en la base.
        if (opinion_ocultar($id)) {
            flash('🙈 Opinión ocultada (ya no se ve en la ficha, pero sigue guardada).', 'info');
        } else {
            flash('⚠️ No se pudo ocultar esa opinión.', 'warning');
        }
    } elseif ($accion === 'opinion_borrar') {
        // 🗑️ El botón BORRAR de la lista de opiniones (sin reporte de por medio).
        $nid = 0;
        try {
            $st = $pdo->prepare('SELECT negocio_id FROM directorio_opiniones WHERE id = ? LIMIT 1');
            $st->execute([$id]);
            $nid = (int)($st->fetchColumn() ?: 0);
        } catch (Throwable $e) { $nid = 0; }
        if (opinion_borrar($id)) {
            opiniones_recalcular_rating($nid);
            flash('🗑️ Opinión borrada.', 'exito');
        } else {
            flash('⚠️ No se pudo borrar esa opinión.', 'warning');
        }
    } elseif ($accion === 'opinion_reporte_borrar' || $accion === 'opinion_reporte_ignorar') {
        // 🚩 RECLAMOS DE OPINIONES (pedido del jefe, 2026-09-14): los dos botones del panel.
        //   · Borrar  → se va la opinión y TODOS sus reportes quedan cerrados.
        //   · Ignorar → la opinión se queda y el reporte se cierra.
        $rep_id = $id;
        $opinion_id = 0;
        try {
            $st = $pdo->prepare('SELECT opinion_id FROM directorio_opiniones_reportes WHERE id = ? LIMIT 1');
            $st->execute([$rep_id]);
            $opinion_id = (int)($st->fetchColumn() ?: 0);
        } catch (Throwable $e) { $opinion_id = 0; }

        if ($accion === 'opinion_reporte_borrar') {
            if ($opinion_id > 0 && opinion_borrar($opinion_id)) {
                try {
                    $pdo->prepare("UPDATE directorio_opiniones_reportes
                                      SET estado='opinion_borrada', atendido_por=?, atendido_en=?
                                    WHERE opinion_id = ? AND estado = 'pendiente'")
                        ->execute([(int)$admin['id'], date('Y-m-d H:i:s'), $opinion_id]);
                } catch (Throwable $e) { /* el reporte queda pendiente: no rompe el borrado */ }
                flash('🗑️ Opinión borrada y sus reportes cerrados.', 'exito');
            } else {
                flash('⚠️ No se pudo borrar esa opinión.', 'warning');
            }
        } else {
            if (opinion_reporte_marcar($rep_id, 'ignorado', (int)$admin['id'])) {
                flash('🙈 Reporte ignorado: la opinión se queda publicada.', 'info');
            } else {
                flash('⚠️ No se pudo marcar el reporte.', 'warning');
            }
        }
    }
    // 🔎 Buscador fuzzy: lo que se acaba de cambiar (aprobar, ocultar, destacar,
    // borrar…) tiene que salir YA en las sugerencias, sin esperar la hora de caché.
    fuzzy_olvidar_cache();

    // Redirigir para evitar reenvío.
    // 🏬 Se conservan los FILTROS de la URL (vista previa de tiendas, 2026-09-12) para
    // que al aprobar/ocultar/eliminar el jefe no pierda dónde estaba mirando.
    // ⚠️ 2026-09-18: la lista tiene que llevar TODOS los filtros de la vista de tiendas. Faltaban
    //    `mus` (🎵 música) y `rev` (✅ Revisados), y por eso al marcar una tienda como revisada el
    //    filtro «Sin revisar» se caía y la lista volvía a las 1 708 tiendas.
    // 🆕 2026-09-20: se suman `dist` (📍 distrito), `vent` (🛵 cómo atiende) y `rub` (🏷️ rubro): si no
    //    viajaran aquí, al aprobar/ocultar una tienda el jefe perdería el filtro que estaba mirando.
    $qs = ['seccion' => $seccion];
    foreach (['q', 'estado', 'foto', 'prod', 'rev', 'wa', 'mus', 'tel', 'ubi', 'dueno', 'redes', 'dest', 'dist', 'vent', 'rub', 'orden', 'vista', 'p', 'per'] as $clave_get) {
        $valor_get = $_GET[$clave_get] ?? '';
        if (is_scalar($valor_get) && trim((string)$valor_get) !== '') $qs[$clave_get] = (string)$valor_get;
    }
    header('Location: ' . url('superadmin.php?' . http_build_query($qs)));
    exit;
}

// Helper: reclamo por id
function reclamo_por_id($id) {
    $stmt = db()->prepare("SELECT r.*, n.nombre AS negocio_nombre, n.slug AS negocio_slug FROM directorio_reclamos r LEFT JOIN directorio_negocios n ON n.id=r.negocio_id WHERE r.id=? LIMIT 1");
    $stmt->execute([$id]);
    return $stmt->fetch() ?: null;
}

// Helper: eliminar un negocio y todas sus dependencias
function eliminar_negocio_completo($pdo, $id) {
    $id = (int)$id;
    // productos del negocio
    foreach ($pdo->query("SELECT id FROM directorio_servicios WHERE negocio_id=$id") as $pr) {
        $pdo->prepare("DELETE FROM directorio_producto_fotos WHERE producto_id=?")->execute([$pr['id']]);
        $pdo->prepare("DELETE FROM directorio_vistas WHERE producto_id=?")->execute([$pr['id']]);
    }
    $pdo->prepare("DELETE FROM directorio_servicios WHERE negocio_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM directorio_fotos WHERE negocio_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM directorio_opiniones WHERE negocio_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM directorio_vistas WHERE negocio_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM directorio_mensajes WHERE negocio_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM directorio_negocio_pagos WHERE negocio_id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM directorio_reclamos WHERE negocio_id=?")->execute([$id]);
    // 📨 INVITACIONES (2026-09-17): se van con la tienda para no dejar filas huérfanas.
    // Va con try/catch: la tabla se crea sola la primera vez que se usa (puede no existir aún).
    try { $pdo->prepare("DELETE FROM " . invitaciones_tabla() . " WHERE negocio_id=?")->execute([$id]); }
    catch (Throwable $e) { /* la tabla aún no existe: no hay nada que borrar */ }
    // 🗑️ 2026-09-13: la limpieza de la mensajería entre tiendas (B2B) al borrar un negocio
    // se retiró junto con ese módulo. Las tablas de esa mensajería ya no existen en producción.
    $pdo->prepare("DELETE FROM directorio_negocios WHERE id=?")->execute([$id]);
}

// ====== Datos por sección ======
$reclamos_pend = [];
$reclamos_proc = [];
$postulantes = [];
$productos = [];
$usuarios = [];
$reportes_pend = [];
$reportes_proc = [];
$stats = ['negocios'=>0,'productos'=>0,'reclamos_pend'=>0,'postulantes_nuevos'=>0,'premium'=>0];

$pdo = db();
// Reportes de contenido pendientes (cuenta para el globito del menú).
// reportes_contar() devuelve 0 si la tabla aún no se ha creado.
$reportes_pend_n = reportes_contar('pendiente');
// 🚩 Reclamos de opiniones pendientes (lo que el jefe tiene que decidir): borrar o ignorar.
$opiniones_pend_n = opiniones_reportes_contar();
// 🔑 Dueños que olvidaron su contraseña y están esperando una nueva (globito del menú).
// Devuelve 0 si la tabla aún no existe: la crea sola el primer pedido.
$claves_pend_n = claves_pendientes_n();
if ($seccion === 'resumen') {
    $stats['reportes_pend'] = $reportes_pend_n;
    $stats['negocios'] = (int)$pdo->query("SELECT COUNT(*) FROM directorio_negocios")->fetchColumn();
    $stats['productos'] = (int)$pdo->query("SELECT COUNT(*) FROM directorio_servicios")->fetchColumn();
    $stats['reclamos_pend'] = (int)$pdo->query("SELECT COUNT(*) FROM directorio_reclamos WHERE estado='pendiente'")->fetchColumn();
    $stats['postulantes_nuevos'] = (int)$pdo->query("SELECT COUNT(*) FROM directorio_postulantes WHERE estado='nuevo'")->fetchColumn();
    try { $stats['premium'] = (int)$pdo->query("SELECT COUNT(*) FROM directorio_usuarios WHERE plan='premium'")->fetchColumn(); } catch (Exception $e) {}
} elseif ($seccion === 'reclamos') {
    $reclamos_pend = obtener_reclamos('pendiente');
    $reclamos_proc = array_merge(obtener_reclamos('aprobado'), obtener_reclamos('rechazado'));
} elseif ($seccion === 'postulantes') {
    $postulantes = obtener_postulantes(null, 300);
} elseif ($seccion === 'tiendas') {
    // 🏬 TIENDAS (2026-09-12): la vista previa, los filtros y el listado viven en
    // includes/vista_tiendas_admin.php (igual que Banners y Estadísticas).
    // Ese archivo carga sus propios datos (filtros GET: q, estado, foto, prod, wa, mus, rev,
    // tel, ubi, dueno, redes, dest, dist, vent, rub, orden, vista, p) y los pinta.
} elseif ($seccion === 'productos') {
    $filtro = trim($_GET['q'] ?? '');
    if ($filtro) {
        $stmt = $pdo->prepare("SELECT s.id, s.titulo, s.precio, s.unidad, s.tipo_producto, s.negocio_id, s.activo,
            n.nombre AS negocio_nombre FROM directorio_servicios s
            LEFT JOIN directorio_negocios n ON n.id=s.negocio_id
            WHERE s.titulo LIKE ? ORDER BY s.id DESC LIMIT 80");
        $stmt->execute(["%$filtro%"]);
        $productos = $stmt->fetchAll();
    } else {
        $productos = $pdo->query("SELECT s.id, s.titulo, s.precio, s.unidad, s.tipo_producto, s.negocio_id, s.activo,
            n.nombre AS negocio_nombre FROM directorio_servicios s
            LEFT JOIN directorio_negocios n ON n.id=s.negocio_id
            ORDER BY s.id DESC LIMIT 80")->fetchAll();
    }
} elseif ($seccion === 'usuarios') {
    try {
        $usuarios = $pdo->query("SELECT id, nombre, email, tipo, activo, ultimo_login, creado_en, plan, plan_hasta FROM directorio_usuarios ORDER BY id")->fetchAll();
    } catch (Exception $e) {
        // Esquema sin migrar todavía: fallback sin columnas de plan
        $usuarios = $pdo->query("SELECT id, nombre, email, tipo, activo, ultimo_login, creado_en FROM directorio_usuarios ORDER BY id")->fetchAll();
        foreach ($usuarios as &$u) { $u['plan'] = PLAN_GRATIS; $u['plan_hasta'] = null; } unset($u);
    }
    // 🛡️ Cuántos administradores ACTIVOS hay: es lo que protege al sitio de quedarse sin ninguno
    // cuando se cambia un rol (ver la acción `usuario_rol`).
    $admins_activos = 0;
    foreach ($usuarios as $uu) { if (($uu['tipo'] ?? '') === 'admin' && !empty($uu['activo'])) $admins_activos++; }
    // 🔑 LOS PEDIDOS DE «OLVIDÉ MI CONTRASEÑA» (2026-09-16): salen arriba de la lista, con su botón.
    $claves_pend = claves_pendientes(60);
    // 🔑 Y la clave recién generada: se lee de la sesión y se BORRA (se muestra una sola vez).
    $clave_nueva = $_SESSION['clave_nueva'] ?? null;
    unset($_SESSION['clave_nueva']);
} elseif ($seccion === 'reportes') {
    $reportes_pend = reportes_listar('pendiente');
    $reportes_proc = array_merge(
        reportes_listar('revisado', 100),
        reportes_listar('resuelto', 100),
        reportes_listar('ignorado', 100)
    );
} elseif ($seccion === 'opiniones') {
    // 💬 OPINIONES ANÓNIMAS: la pantalla entera (datos + vista) vive en
    // includes/vista_opiniones_admin.php, igual que Tiendas y Banners.
} elseif ($seccion === 'avisos') {
    $avisos_cfg     = avisos_config();
    $avisos_24h     = avisos_contar(1440, 'enviado');
    $avisos_semana  = avisos_contar(10080, 'enviado');
    $avisos_robots  = avisos_robots(1440);
    $avisos_agrup   = avisos_contar(1440, 'agrupado');
    $avisos_duplic  = avisos_contar(1440, 'duplicado');
    $avisos_ultimos = [];
    if (avisos_tablas_ok()) {
        try {
            $avisos_ultimos = db()->query('SELECT tipo, estado, resumen, ip, es_bot, bot, creado_en
                FROM ' . AVISOS_TABLA_LOG . " WHERE estado = 'enviado'
                ORDER BY id DESC LIMIT 25")->fetchAll();
        } catch (Throwable $e) { $avisos_ultimos = []; }
    }
} elseif ($seccion === 'banners') {
    // 🔎 La columna `busqueda` (término que buscará el banner) se crea sola al abrir la
    // pestaña: sin subir un migrador que el escáner del hosting pueda bloquear. Es
    // idempotente (primero comprueba si existe).
    banners_asegurar_busqueda();
    $banners_admin      = banners_listar(trim($_GET['f'] ?? ''));
    $banners_stats_dias = banners_stats_dias(7);
    $banners_categorias = obtener_categorias();
    $banners_temas_opts = banners_temas();
    $banner_edit        = isset($_GET['editar']) ? banner_por_id((int)$_GET['editar']) : null;
}

$titulo_pagina = 'Súper Admin';
include __DIR__ . '/includes/header.php';
?>
<style>
.sa-wrap{max-width:100%;margin:0 auto}
.sa-top{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:16px}
.sa-top h1{font-size:22px;font-weight:800}
.sa-top .badge-admin{background:#7c3aed;color:#fff;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:700}
/* Menú del Súper Admin: cuadrícula que usa todo el ancho y se apila sola (crece solo si se agregan más botones) */
.sa-nav{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px;margin-bottom:18px}
.sa-nav a{display:flex;align-items:center;gap:6px;min-height:46px;padding:10px 12px;border-radius:12px;background:#fff;border:1px solid var(--color-borde);box-shadow:var(--sombra-tarjeta);font-size:14px;font-weight:600;color:var(--color-texto);line-height:1.15;text-decoration:none;white-space:normal;overflow-wrap:break-word}
.sa-nav a:hover{border-color:var(--color-primario);color:var(--color-primario)}
.sa-nav a.activo{background:var(--color-primario);color:#fff;border-color:transparent}
.sa-nav a .n{margin-left:auto;background:#fbbf24;color:#78350f;border-radius:999px;padding:0 6px;font-size:11px;font-weight:700}
.sa-nav a.activo .n{background:#fff;color:var(--color-primario)}
@media(max-width:640px){.sa-nav{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.sa-nav a{font-size:13px;padding:9px 10px;min-height:44px}}
.sa-table-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
.sa-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:12px}
.sa-stat{background:#fff;border-radius:12px;padding:16px;box-shadow:var(--sombra-tarjeta);border:1px solid var(--color-borde)}
.sa-stat .num{font-size:26px;font-weight:800;color:var(--color-primario)}
.sa-stat .lbl{font-size:12px;color:var(--color-texto-claro)}
.sa-toolbar{display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap}
.sa-toolbar input{flex:1;min-width:180px;padding:10px 12px;border:1px solid var(--color-borde);border-radius:8px;font-size:14px}
.sa-table{width:100%;border-collapse:collapse;background:#fff;border-radius:12px;overflow:hidden;box-shadow:var(--sombra-tarjeta);font-size:13px}
.sa-table th,.sa-table td{padding:10px 12px;text-align:left;border-bottom:1px solid var(--color-borde);vertical-align:top}
.sa-table th{background:#f8fafc;font-size:11px;text-transform:uppercase;letter-spacing:.03em;color:var(--color-texto-claro)}
.sa-table .mini{font-size:11px;color:var(--color-texto-claro)}
.sa-card{background:#fff;border-radius:12px;padding:14px;box-shadow:var(--sombra-tarjeta);border:1px solid var(--color-borde);margin-bottom:10px}
.sa-card h3{font-size:15px;font-weight:700;margin-bottom:4px}
.badge-est{display:inline-block;padding:2px 8px;border-radius:999px;font-size:11px;font-weight:700}
.b-pendiente{background:#fef3c7;color:#b45309}
.b-aprobado{background:#dcfce7;color:#15803d}
.b-rechazado{background:#fee2e2;color:#b91c1c}
.b-oculto{background:#e5e7eb;color:#374151}
.b-nuevo{background:#eff6ff;color:#1e40af}
.btn-mini{padding:6px 10px;border-radius:8px;font-size:12px;font-weight:600;cursor:pointer;border:0}
.b-ok{background:#16a34a;color:#fff}
.b-no{background:#dc2626;color:#fff}
.b-ghost{background:#f3f4f6;color:#374151;border:1px solid var(--color-borde)}
.sa-acciones{display:flex;gap:6px;flex-wrap:wrap;margin-top:8px}
.empty{text-align:center;color:var(--color-texto-claro);padding:30px}
.danger{color:#dc2626}
@media(max-width:640px){.sa-table{font-size:12px}.sa-table th:nth-child(4),.sa-table td:nth-child(4){display:none}}
</style>

<div class="sa-wrap">
  <div class="sa-top">
    <h1>🛡️ Súper Admin</h1>
    <span class="badge-admin">Bienvenido, <?= e(explode(' ',$admin['nombre'])[0]) ?></span>
  </div>

  <nav class="sa-nav">
    <a href="<?= url('superadmin.php?seccion=resumen') ?>" class="<?= $seccion==='resumen'?'activo':'' ?>">📊 Resumen</a>
    <?php // 📈 ESTADÍSTICAS + 🏆 RÉCORDS = UNA SOLA PESTAÑA (fusión pedida por el jefe, 2026-09-13 noche):
          // *«puedes fusionar de manera inteligente records y estadísticas conservando el diseño de
          // estadísticas pero agregando también los datos de record de manera útil y fluida para tener
          // una mejor perspectiva del sitio»*. El diseño es el de Estadísticas (st-*): arriba el 🟢 en
          // vivo, después 💼 el negocio (los récords), luego 📈 el tráfico, 👥 las personas y 📣 el
          // resumen copiable. `seccion=records` sigue siendo válido: abre ESTA misma página, así los
          // enlaces y marcadores que ya existían no se rompen. ?>
    <a href="<?= url('superadmin.php?seccion=estadisticas') ?>" class="<?= in_array($seccion, ['estadisticas','records'], true) ? 'activo' : '' ?>">📈 Estadísticas y récords</a>
    <a href="<?= url('superadmin.php?seccion=reclamos') ?>" class="<?= $seccion==='reclamos'?'activo':'' ?>">🏪 Reclamos<?php if($stats['reclamos_pend']>0):?><span class="n"><?= $stats['reclamos_pend'] ?></span><?php endif; ?></a>
    <a href="<?= url('superadmin.php?seccion=reportes') ?>" class="<?= $seccion==='reportes'?'activo':'' ?>">🚩 Reportes<?php if($reportes_pend_n>0):?><span class="n"><?= $reportes_pend_n ?></span><?php endif; ?></a>
    <?php // 💬 RECLAMOS DE OPINIONES (pedido del jefe, 2026-09-14): cada 🚩 Reportar que un visitante
          // toca debajo de una opinión de la ficha llega aquí, y el jefe decide de dos toques:
          // 🗑️ Borrar la opinión o 🙈 Ignorarla (la opinión se queda). ?>
    <a href="<?= url('superadmin.php?seccion=opiniones') ?>" class="<?= $seccion==='opiniones'?'activo':'' ?>">🚩 Reclamos de opiniones<?php if($opiniones_pend_n>0):?><span class="n"><?= $opiniones_pend_n ?></span><?php endif; ?></a>
    <a href="<?= url('superadmin.php?seccion=avisos') ?>" class="<?= $seccion==='avisos'?'activo':'' ?>">📱 Telegram</a>
    <a href="<?= url('superadmin.php?seccion=postulantes') ?>" class="<?= $seccion==='postulantes'?'activo':'' ?>">💼 Postulantes<?php if($stats['postulantes_nuevos']>0):?><span class="n"><?= $stats['postulantes_nuevos'] ?></span><?php endif; ?></a>
    <a href="<?= url('superadmin.php?seccion=tiendas') ?>" class="<?= $seccion==='tiendas'?'activo':'' ?>">🏬 Tiendas</a>
    <a href="<?= url('superadmin.php?seccion=productos') ?>" class="<?= $seccion==='productos'?'activo':'' ?>">📦 Productos</a>
    <?php // 🎨 EDICIÓN DE PRODUCTOS CON IA (pedido del jefe, 2026-09-11): listado por orden de
          // llegada con la imagen actual, el prompt de generación (según tienda + rubro) y el
          // botón «Cambiar imagen». Guía: GUIA_EDICION_PRODUCTOS_PROMPTS_IA.md ?>
    <a href="<?= url('editaproductos.php') ?>">🎨 Editar productos (IA)</a>
    <?php // 🏪 EDICIÓN DE TIENDAS CON IA (pedido del jefe, 2026-09-12): portada por arrastrar y soltar
          // (flyer del rubro) + prompt de IA + hasta 4 RUBROS por tienda, todo en vivo sin recargar.
          // Guía: GUIA_TIENDAS_PORTADAS_Y_RUBROS.md · PARTE B (antes no estaba enlazado y se perdía el link) ?>
    <a href="<?= url('editatiendas.php') ?>">🖼️ Editar tiendas (portadas + rubros)</a>
    <a href="<?= url('superadmin.php?seccion=usuarios') ?>" class="<?= $seccion==='usuarios'?'activo':'' ?>">👥 Usuarios<?php if($claves_pend_n>0):?><span class="n"><?= (int)$claves_pend_n ?></span><?php endif; ?></a>
    <?php // 🗑️ 2026-09-13: aquí estaba la pestaña de mensajería privada entre tiendas (B2B).
          // El jefe ordenó retirarla del sitio, así que ya no se enlaza. NO volver a ponerla. ?>
    <?php // 🧭 EL GUÍA (2026-09-17): el chat de ayuda dejó de llamarse «El ninja» y dejó de vivir en
          // todo el sitio: ahora es el anfitrión de cada tienda y solo se pinta en las fichas. ?>
    <a href="<?= url('superadmin.php?seccion=chatbot') ?>" class="<?= $seccion==='chatbot'?'activo':'' ?>">🧭 El guía (chat)</a>
    <?php // 🛠️ EL CONSTRUCTOR DE TIENDAS (2026-09-14): la página privada donde un usuario
          // registrado arma su tienda conversando. Aquí se ve su CONSUMO (tokens, dólares y
          // en qué paso se quedan), que es para lo que tiene clave propia. ?>
    <a href="<?= url('superadmin.php?seccion=maestro') ?>" class="<?= $seccion==='maestro'?'activo':'' ?>">🛠️ El maestro (tiendas IA)</a>
    <?php // 👑 EL SUPREMO (2026-09-18, pedido del jefe): la página PRIVADA del Súper Administrador para
          // crear tiendas EN VIVO delante del cliente (el «modo vendedor»). Es el ÚNICO enlace que
          // existe a `/supremo`: nadie más que el administrador puede entrar (la página y su puerta
          // comprueban `es_admin()` en el servidor). Desde ahí salen los códigos listos para pegar en
          // Flow (la cabecera, la música y las imágenes de los productos). ?>
    <a href="<?= url('superadmin.php?seccion=supremo') ?>" class="<?= $seccion==='supremo'?'activo':'' ?>"
       style="background:#111;color:#e0b016;border-color:#111">👑 El Supremo (venta en vivo)</a>
    <?php // 🎵 LAS CANCIONES DE LAS TIENDAS (2026-09-17, pedido del jefe): el jingle de 40 segundos que
          // se le hace a cada tienda nueva, con el saldo y el consumo de las 4 cuentas de Treblo. ?>
    <a href="<?= url('superadmin.php?seccion=canciones') ?>" class="<?= $seccion==='canciones'?'activo':'' ?>">🎵 Canciones (Treblo)</a>
    <a href="<?= url('superadmin.php?seccion=banners') ?>" class="<?= $seccion==='banners'?'activo':'' ?>">📢 Banners</a>
    <a href="<?= url('superadmin.php?seccion=cerca') ?>" class="<?= $seccion==='cerca'?'activo':'' ?>">📍 Cerca de mí</a>
    <?php // 📰 EL BOTÓN DE NOTICIAS (pedido del jefe, 2026-09-13): abre en una pestaña nueva la
          // sección de noticias del sitio, para ver lo que el robot publicó hoy sin salir del panel. ?>
    <a href="<?= url('noticias') ?>" target="_blank" rel="noopener"
       style="background:var(--color-primario);color:#fff;border-color:transparent">📰 Noticias (web) ↗</a>
  </nav>

  <?php if ($seccion === 'resumen'): ?>
    <div class="sa-grid">
      <div class="sa-stat"><div class="num"><?= number_format($stats['negocios']) ?></div><div class="lbl">Negocios / tiendas</div></div>
      <div class="sa-stat"><div class="num"><?= number_format($stats['productos']) ?></div><div class="lbl">Productos</div></div>
      <div class="sa-stat"><div class="num"><?= $stats['reclamos_pend'] ?></div><div class="lbl">Reclamos pendientes</div></div>
      <div class="sa-stat"><div class="num"><?= $reportes_pend_n ?></div><div class="lbl">Reportes pendientes</div></div>
      <div class="sa-stat"><div class="num"><?= $stats['postulantes_nuevos'] ?></div><div class="lbl">Postulantes nuevos</div></div>
      <div class="sa-stat"><div class="num"><?= $stats['premium'] ?></div><div class="lbl">Usuarios Premium</div></div>
    </div>
    <div class="sa-card" style="margin-top:16px">
      <p style="font-size:14px;color:var(--color-texto-claro)">Desde aquí puedes moderar el contenido: revisar solicitudes de <b>reclamo de negocios</b>, contactar <b>postulantes</b>, y eliminar <b>tiendas, productos o cuentas</b> que no cumplan las políticas de la plataforma (p. ej. contenido prohibido).</p>
    </div>

  <?php elseif ($seccion === 'reclamos'): ?>
    <h2 style="font-size:17px;margin-bottom:10px">🕒 Solicitudes pendientes (<?= count($reclamos_pend) ?>)</h2>
    <?php if (!$reclamos_pend): ?><div class="empty">No hay solicitudes pendientes.</div><?php endif; ?>
    <?php foreach ($reclamos_pend as $r): ?>
      <div class="sa-card">
        <h3><?= e($r['negocio_nombre'] ?? 'Negocio #'.$r['negocio_id']) ?></h3>
        <span class="badge-est b-pendiente">pendiente</span>
        <p style="margin-top:8px;font-size:13px"><b>Reclamante:</b> <?= e($r['nombre']) ?> (<?= e($r['email']) ?>)</p>
        <?php if ($r['telefono']): ?><p class="mini">📞 <?= e($r['telefono']) ?></p><?php endif; ?>
        <p class="mini">📝 <?= e($r['explicacion']) ?></p>
        <div class="sa-acciones">
          <form method="post" style="display:inline">
            <?= csrf_campo() ?><input type="hidden" name="accion" value="reclamo_aprobar"><input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button class="btn-mini b-ok" onclick="return confirm('¿Aprobar y transferir este negocio al reclamante?')">✓ Aprobar</button>
          </form>
          <form method="post" style="display:inline">
            <?= csrf_campo() ?><input type="hidden" name="accion" value="reclamo_rechazar"><input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button class="btn-mini b-no">✕ Rechazar</button>
          </form>
          <form method="post" style="display:inline">
            <?= csrf_campo() ?><input type="hidden" name="accion" value="reclamo_borrar"><input type="hidden" name="id" value="<?= $r['id'] ?>">
            <button class="btn-mini b-ghost" onclick="return confirm('¿Eliminar esta solicitud?')">🗑 Eliminar</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>

    <h2 style="font-size:16px;margin:20px 0 10px">📜 Historial (<?= count($reclamos_proc) ?>)</h2>
    <?php if (!$reclamos_proc): ?><div class="empty">Sin historial.</div><?php endif; ?>
    <?php foreach ($reclamos_proc as $r): ?>
      <div class="sa-card" style="opacity:.75">
        <h3><?= e($r['negocio_nombre'] ?? 'Negocio #'.$r['negocio_id']) ?> <span class="badge-est <?= $r['estado']==='aprobado'?'b-aprobado':'b-rechazado' ?>"><?= e($r['estado']) ?></span></h3>
        <p class="mini"><?= e($r['nombre']) ?> · <?= e($r['email']) ?></p>
      </div>
    <?php endforeach; ?>

  <?php elseif ($seccion === 'postulantes'): ?>
    <?php if (!$postulantes): ?><div class="empty">Aún no hay postulaciones a "Trabaja con nosotros".</div><?php endif; ?>
    <?php foreach ($postulantes as $p): ?>
      <div class="sa-card">
        <h3><?= e($p['nombre_real']) ?></h3>
        <span class="badge-est <?= $p['estado']==='nuevo'?'b-nuevo':($p['estado']==='descartado'?'b-rechazado':'b-aprobado') ?>"><?= e($p['estado']) ?></span>
        <p class="mini">📧 <?= e($p['email']) ?><?= $p['telefono'] ? ' · 📞 '.e($p['telefono']) : '' ?></p>
        <?php if ($p['redes']): ?><p class="mini">📱 Redes: <?= e($p['redes']) ?></p><?php endif; ?>
        <p class="mini">💰 Sueldo esperado: <b><?= e($p['sueldo_solicitado']) ?></b> · Jornada: <?= e($p['jornada']) ?></p>
        <p class="mini">🛵 Movilidad: <?= $p['movilidad']==='si'?'Sí':'No' ?> · 🌐 Idioma: <?= e($p['segundo_idioma'] ?: '—') ?> · 🎓 Formación: <?= e($p['formacion'] ?: '—') ?></p>
        <?php if ($p['info']): ?><p class="mini" style="margin-top:4px">💬 <?= e($p['info']) ?></p><?php endif; ?>
        <div class="sa-acciones">
          <?php foreach (['en_revision'=>'En revisión','contactado'=>'Contactado','descartado'=>'Descartado'] as $val=>$lbl): ?>
            <form method="post" style="display:inline">
              <?= csrf_campo() ?><input type="hidden" name="accion" value="postulante_estado"><input type="hidden" name="id" value="<?= $p['id'] ?>"><input type="hidden" name="estado" value="<?= $val ?>">
              <button class="btn-mini <?= $p['estado']===$val?'b-ok':'b-ghost' ?>"><?= $lbl ?></button>
            </form>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>

  <?php elseif ($seccion === 'tiendas'): ?>
    <?php // 🏬 VISTA PREVIA de tiendas + filtros + orden (includes/vista_tiendas_admin.php) ?>
    <?php require __DIR__ . '/includes/vista_tiendas_admin.php'; ?>

  <?php elseif ($seccion === 'productos'): ?>
    <form method="get" class="sa-toolbar">
      <input type="hidden" name="seccion" value="<?= e($seccion) ?>">
      <input type="text" name="q" placeholder="Buscar por nombre de producto…" value="<?= e($_GET['q'] ?? '') ?>">
      <button class="btn-mini b-ok" style="padding:10px 18px">Buscar</button>
    </form>

    <?php if (!$productos): ?><div class="empty">No se encontraron productos.</div><?php endif; ?>
      <div class="sa-table-wrap"><table class="sa-table">
        <thead><tr><th>Producto</th><th>Precio</th><th>Tienda</th><th>Tipo</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($productos as $pr): ?>
          <tr>
            <td><b><?= e($pr['titulo']) ?></b></td>
            <td>S/ <?= number_format($pr['precio'],2) ?><?= $pr['unidad']?' / '.e($pr['unidad']):'' ?></td>
            <td><?= e($pr['negocio_nombre'] ?? 'sin tienda') ?></td>
            <td><?= e($pr['tipo_producto'] ?? 'fisico') ?></td>
            <td>
              <form method="post" style="display:inline">
                <?= csrf_campo() ?><input type="hidden" name="accion" value="producto_eliminar"><input type="hidden" name="id" value="<?= $pr['id'] ?>">
                <button class="btn-mini b-no" onclick="return confirm('⚠️ ¿Eliminar este producto?')">🗑 Eliminar</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table></div>

  <?php elseif ($seccion === 'usuarios'): ?>
    <?php // ================= 🔑 LA CLAVE NUEVA (módulo del 2026-09-16) =================
          // Cuando el jefe toca «🔑 Restablecer contraseña», la clave se guarda en la sesión y
          // sale AQUÍ, grande y copiable. Se muestra UNA sola vez (después solo queda el hash) y
          // con el botón verde para mandársela al dueño por WhatsApp con el mensaje ya escrito. ?>
    <?php if (!empty($clave_nueva) && !empty($clave_nueva['clave'])): ?>
      <div class="sa-card" style="border-left:5px solid #16a34a;margin-bottom:16px">
        <?php $cn_nueva = !empty($clave_nueva['nuevo']); // ➕ cuenta recién creada a mano: mismo cartel, otro texto ?>
        <h3 style="font-size:16px"><?= $cn_nueva ? '➕ Cuenta creada: ' . e((string)$clave_nueva['nombre']) : '🔑 Contraseña nueva para ' . e((string)$clave_nueva['nombre']) ?></h3>
        <p class="mini" style="margin:4px 0 10px">
          ⚠️ <b>Se muestra UNA sola vez.</b> Mándasela ahora (o cópiala): después ya no se puede leer.
          <?php if (!empty($clave_nueva['tienda'])): ?>· 🏪 <?= e((string)$clave_nueva['tienda']) ?><?php endif; ?>
          <?php if ($cn_nueva): ?>
            · 🆕 Cuenta nueva: ya puede entrar con esa clave.
          <?php else: ?>
            · 🔒 Se cerraron sus sesiones abiertas, así que tiene que entrar de nuevo.
          <?php endif; ?>
        </p>

        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:10px">
          <div style="background:#0f172a;color:#fff;border-radius:12px;padding:12px 18px;font-size:26px;font-weight:800;letter-spacing:3px;font-family:ui-monospace,Menlo,Consolas,monospace"><?= e((string)$clave_nueva['clave']) ?></div>
          <div class="mini">
            <div>👤 <b><?= e((string)$clave_nueva['nombre']) ?></b></div>
            <div>🔑 Usuario: <b><?= e((string)$clave_nueva['usuario']) ?></b></div>
          </div>
        </div>

        <label class="mini" style="display:block;margin-bottom:4px">📋 El mensaje, listo para mandárselo (tócalo para copiarlo):</label>
        <textarea readonly rows="6" onclick="this.select()"
                  style="width:100%;font-size:13px;padding:10px;border:1px solid var(--color-borde);border-radius:10px;background:#f8fafc;line-height:1.5"><?= e((string)$clave_nueva['mensaje']) ?></textarea>

        <?php if (!empty($clave_nueva['wa'])): ?>
          <a class="btn-mini b-ok" style="display:inline-flex;align-items:center;gap:6px;margin-top:10px;background:#25D366;text-decoration:none;padding:10px 14px;font-size:14px"
             href="<?= e((string)$clave_nueva['wa']) ?>" target="_blank" rel="noopener"><?= wa_icono_svg() ?> Mandársela por WhatsApp a <?= e((string)$clave_nueva['usuario']) ?></a>
        <?php else: ?>
          <p class="mini" style="margin-top:10px">📧 Esta cuenta no tiene teléfono (se registró con correo): mándale el mensaje por donde puedas.</p>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php // ================= 🔑 LOS PEDIDOS DE «OLVIDÉ MI CONTRASEÑA» =================
          // Llegan desde `/recuperar` (y al Telegram del jefe). Un toque y queda resuelto. ?>
    <?php if (!empty($claves_pend)): ?>
      <div class="sa-card" style="border-left:5px solid #ea6a12;margin-bottom:16px">
        <h3 style="font-size:16px">🔑 Olvidaron su contraseña (<?= count($claves_pend) ?>)</h3>
        <p class="mini" style="margin:4px 0 10px">Pidieron desde <b>/recuperar</b>. Toca el botón y le generas una clave nueva (sale arriba, lista para copiar o mandar por WhatsApp).</p>
        <div class="sa-table-wrap">
          <table class="sa-table">
            <thead><tr><th>Cuándo</th><th>Quién pide</th><th>Tienda</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($claves_pend as $cp): ?>
              <tr>
                <td class="mini"><?= e(date('d/m H:i', strtotime((string)$cp['creado_en']))) ?></td>
                <td>
                  <?php if (!empty($cp['usuario_id']) && !empty($cp['cuenta'])): ?>
                    <b><?= e((string)$cp['cuenta']) ?></b>
                    <div class="mini">🔑 <?= e((string)($cp['telefono'] ?: ($cp['cuenta_email'] ?: $cp['entrada']))) ?>
                      <?php if (empty($cp['cuenta_activa'])): ?>· <span class="danger">cuenta suspendida</span><?php endif; ?></div>
                  <?php else: ?>
                    <span class="mini">Escribió <b><?= e((string)$cp['entrada']) ?></b></span>
                    <div class="mini danger">No encontré ninguna cuenta con ese dato</div>
                  <?php endif; ?>
                  <?php if (!empty($cp['ip'])): ?><div class="mini">🌐 <?= e((string)$cp['ip']) ?></div><?php endif; ?>
                </td>
                <td class="mini"><?= $cp['tienda'] ? e((string)$cp['tienda']) : '<span class="mini">—</span>' ?></td>
                <td>
                  <div style="display:flex;gap:4px;flex-wrap:wrap">
                    <?php if (!empty($cp['usuario_id']) && !empty($cp['cuenta']) && ($cp['cuenta_tipo'] ?? '') !== 'admin'): ?>
                      <form method="post" style="display:inline">
                        <?= csrf_campo() ?><input type="hidden" name="accion" value="usuario_clave_nueva">
                        <input type="hidden" name="id" value="<?= (int)$cp['usuario_id'] ?>">
                        <button class="btn-mini b-ok" onclick="return confirm('¿Darle una contraseña nueva a esta cuenta? Se cerrarán sus sesiones abiertas.')">🔑 Darle clave nueva</button>
                      </form>
                    <?php elseif (($cp['cuenta_tipo'] ?? '') === 'admin'): ?>
                      <?php // ⛔ A un administrador no se le cambia la clave desde aquí (una sesión robada
                            // no puede dejar al jefe fuera de su propio sitio): no se ofrece el botón. ?>
                      <span class="mini">Cuenta de administrador: su clave no se cambia desde aquí.</span>
                    <?php endif; ?>
                    <form method="post" style="display:inline">
                      <?= csrf_campo() ?><input type="hidden" name="accion" value="clave_pedido_descartar">
                      <input type="hidden" name="id" value="<?= (int)$cp['id'] ?>">
                      <button class="btn-mini b-ghost">🙈 Descartar</button>
                    </form>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <?php // ➕ CREAR USUARIO (2026-09-21): la pestaña solo administraba las cuentas que ya existían
          // (nacían solas por registro, Google, la invitación de tienda o El maestro 🛠️), así que
          // esta es la puerta para crearlas a mano. Se entra con el correo O con el WhatsApp. ?>
    <div class="sa-card" style="margin-bottom:16px">
      <h3 style="font-size:16px">➕ Crear usuario</h3>
      <p class="mini" style="margin:4px 0 10px">
        La cuenta entra <b>con el correo o con el número de WhatsApp</b> (el que le pongas). Si dejas
        la contraseña en blanco se genera una (3 letras y 1 número) y sale aquí abajo,
        <b>una sola vez</b>, con el mensaje listo para mandárselo.
      </p>
      <form method="post" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:10px;align-items:end">
        <?= csrf_campo() ?>
        <input type="hidden" name="accion" value="usuario_crear">
        <label class="mini">Nombre (de la persona o de la tienda)
          <input type="text" name="u_nombre" required maxlength="80" style="width:100%;font-size:16px;padding:9px;border:1px solid var(--color-borde);border-radius:8px">
        </label>
        <label class="mini">Correo (si va a entrar con correo)
          <input type="email" name="u_email" maxlength="120" placeholder="nombre@correo.com" style="width:100%;font-size:16px;padding:9px;border:1px solid var(--color-borde);border-radius:8px">
        </label>
        <label class="mini">WhatsApp (si va a entrar con su número)
          <input type="tel" name="u_telefono" maxlength="15" placeholder="943810204" style="width:100%;font-size:16px;padding:9px;border:1px solid var(--color-borde);border-radius:8px">
        </label>
        <label class="mini">Rol
          <select name="u_tipo" style="width:100%;font-size:16px;padding:9px;border:1px solid var(--color-borde);border-radius:8px">
            <option value="cliente">cliente (busca y compra)</option>
            <option value="dueno">dueño de tienda</option>
            <option value="admin">admin (acceso total)</option>
          </select>
        </label>
        <label class="mini">Contraseña (en blanco = la genero yo)
          <input type="text" name="u_clave" maxlength="40" placeholder="3 letras y 1 número" style="width:100%;font-size:16px;padding:9px;border:1px solid var(--color-borde);border-radius:8px">
        </label>
        <label class="mini" style="display:flex;align-items:center;gap:8px;min-height:40px">
          <input type="checkbox" name="u_activo" value="1" checked style="width:18px;height:18px">
          Puede entrar ya (activo)
        </label>
        <button class="btn-mini b-ok" style="padding:12px 16px;font-size:15px">➕ Crear usuario</button>
      </form>
    </div>

    <div class="sa-table-wrap"><table class="sa-table">
      <thead><tr><th>ID</th><th>Usuario</th><th>Rol</th><th>Plan</th><th>Estado</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($usuarios as $u): ?>
        <?php $es_p = (($u['plan'] ?? 'gratis') === 'premium'); ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><b><?= e($u['nombre']) ?></b><div class="mini"><?= e($u['email']) ?></div></td>
          <td>
            <span class="badge-est <?= $u['tipo']==='admin'?'b-aprobado':($u['tipo']==='dueno'?'b-nuevo':'b-pendiente') ?>"><?= e($u['tipo']) ?></span>
            <?php // 🎭 EL ROL SE PUEDE CAMBIAR EN CUALQUIER MOMENTO (2026-09-21). Solo se esconde en el
                  // último administrador activo: dejar el sitio sin admin no tendría vuelta atrás. ?>
            <?php if ($u['tipo'] !== 'admin' || $admins_activos > 1): ?>
              <form method="post" style="display:flex;gap:4px;align-items:center;margin-top:6px">
                <?= csrf_campo() ?><input type="hidden" name="accion" value="usuario_rol"><input type="hidden" name="id" value="<?= $u['id'] ?>">
                <select name="tipo" style="padding:5px;border:1px solid var(--color-borde);border-radius:6px;font-size:12px">
                  <option value="cliente" <?= $u['tipo']==='cliente'?'selected':'' ?>>cliente</option>
                  <option value="dueno" <?= $u['tipo']==='dueno'?'selected':'' ?>>dueño</option>
                  <option value="admin" <?= $u['tipo']==='admin'?'selected':'' ?>>admin</option>
                </select>
                <button class="btn-mini b-ghost" title="Cambiar el rol de esta cuenta">🎭 Cambiar rol</button>
              </form>
            <?php else: ?>
              <div class="mini" style="margin-top:4px">🔒 único admin activo</div>
            <?php endif; ?>
          </td>
          <td>
            <span class="badge-est <?= $es_p ? 'b-aprobado' : 'b-pendiente' ?>"><?= $es_p ? '⭐ premium' : 'gratis' ?></span>
            <?php if ($es_p && !empty($u['plan_hasta'])): ?><div class="mini">hasta <?= date('d/m/Y', strtotime($u['plan_hasta'])) ?></div><?php endif; ?>
          </td>
          <td><span class="badge-est <?= $u['activo']?'b-aprobado':'b-rechazado' ?>"><?= $u['activo']?'activo':'suspendido' ?></span></td>
          <td>
            <div style="display:flex;gap:4px;flex-wrap:wrap">
              <?php if ($u['tipo'] !== 'admin'): ?>
                <form method="post" style="display:inline">
                  <?= csrf_campo() ?><input type="hidden" name="accion" value="usuario_plan">
                  <input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <input type="hidden" name="plan" value="<?= $es_p ? PLAN_GRATIS : PLAN_PREMIUM ?>">
                  <?php if (!$es_p): ?>
                    <select name="dias" style="padding:5px;border:1px solid var(--color-borde);border-radius:6px;font-size:12px">
                      <option value="0">Indefinido</option>
                      <option value="30">30 días</option>
                      <option value="90">90 días</option>
                      <option value="365">1 año</option>
                    </select>
                  <?php endif; ?>
                  <button class="btn-mini <?= $es_p ? 'b-no' : 'b-ok' ?>"><?= $es_p ? 'Quitar premium' : '⭐ Hacer Premium' ?></button>
                </form>
                <form method="post" style="display:inline">
                  <?= csrf_campo() ?><input type="hidden" name="accion" value="<?= $u['activo']?'usuario_suspender':'usuario_activar' ?>"><input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button class="btn-mini b-ghost"><?= $u['activo']?'⏸ Suspender':'▶ Activar' ?></button>
                </form>
                <form method="post" style="display:inline">
                  <?= csrf_campo() ?><input type="hidden" name="accion" value="usuario_clave_nueva"><input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button class="btn-mini b-ghost" title="Le genera una contraseña nueva (se muestra una sola vez, lista para mandársela por WhatsApp)"
                          onclick="return confirm('¿Darle una contraseña nueva a esta cuenta? Se cerrarán sus sesiones abiertas y la clave se mostrará UNA sola vez.')">🔑 Restablecer contraseña</button>
                </form>
                <form method="post" style="display:inline">
                  <?= csrf_campo() ?><input type="hidden" name="accion" value="usuario_eliminar"><input type="hidden" name="id" value="<?= $u['id'] ?>">
                  <button class="btn-mini b-no" onclick="return confirm('⚠️ ¿Eliminar esta cuenta? Sus negocios quedarán sin dueño.')">🗑 Eliminar</button>
                </form>
              <?php else: ?>
                <span class="mini">—</span>
              <?php endif; ?>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table></div>

  <?php // 🗑️ 2026-09-13 (orden del jefe): aquí estaba la pestaña con la lista de conversaciones
        // privadas entre tiendas (B2B). Se retiró del sitio ese módulo completo. ?>
  <?php elseif ($seccion === 'avisos'): ?>
    <h2 style="font-size:18px;margin-bottom:4px">📱 Notificaciones de Telegram</h2>
    <p style="font-size:13px;color:var(--color-texto-claro);margin-bottom:14px">
      Esta es la lista completa de lo que te puede avisar el bot. <b>Con la casilla marcada = lo recibes</b>;
      si la desmarcás y guardás, dejás de recibirlo. Los robots nunca se avisan uno por uno: van en un
      resumen por hora, para que el chat siga siendo útil.
    </p>

    <div class="sa-grid" style="margin-bottom:16px">
      <div class="sa-stat"><div class="num"><?= (int)array_sum($avisos_cfg) ?></div><div class="lbl">Notificaciones activadas</div></div>
      <div class="sa-stat"><div class="num"><?= count($avisos_cfg) - (int)array_sum($avisos_cfg) ?></div><div class="lbl">Desactivadas</div></div>
      <div class="sa-stat"><div class="num"><?= array_sum($avisos_24h) ?></div><div class="lbl">Enviadas (24 h)</div></div>
      <div class="sa-stat"><div class="num"><?= (int)($avisos_robots['total'] ?? 0) ?></div><div class="lbl">Robots (24 h)</div></div>
    </div>

    <?php
    // 👤 PERSONAS FRENTE A ROBOTS (2026-09-15). El jefe preguntó «¿acaso no he recibido ninguna
    // visita de persona?» porque el único número que veía era el de robots. Aquí tiene los dos
    // juntos y, debajo, el enlace para pedir el informe inteligente cuando quiera.
    // ⚠️ `MONITOREO_CLAVE_WEB` vive en includes/config_monitoreo.php, que hasta hoy solo cargaba
    // el cron: sin este require, el panel del jefe moría con «Undefined constant» al abrir esta
    // pestaña (el aviso de errores del sitio lo habría mandado al Telegram como 💥 ERROR).
    if (!defined('MONITOREO_CLAVE_WEB') && is_file(__DIR__ . '/includes/config_monitoreo.php')) {
        require_once __DIR__ . '/includes/config_monitoreo.php';
    }
    $av_per   = function_exists('avisos_personas') ? avisos_personas(1440) : ['n' => 0, 'tiendas' => 0, 'ips' => 0];
    $av_ped   = function_exists('avisos_pedidos')  ? avisos_pedidos(1440, 5) : ['n' => 0, 'llamadas' => 0];
    $av_robot = (int)($avisos_robots['total'] ?? 0);
    ?>
    <div class="sa-card" style="margin-bottom:14px">
      <h3>👤 Personas frente a 🤖 robots (24 h)</h3>
      <p class="mini" style="margin:6px 0 0">
        👤 <b><?= (int)$av_per['n'] ?></b> visita(s) de PERSONAS a tiendas
        (<?= (int)$av_per['tiendas'] ?> tienda(s) distinta(s) · <?= (int)$av_per['ips'] ?> visitante(s))<br>
        🤖 <b><?= $av_robot ?></b> visita(s) de robots
        (<?= (int)($avisos_robots['conocidos'] ?? 0) ?> buscadores e IA · <?= (int)($avisos_robots['automaticas'] ?? 0) ?> automáticas sin señales)
        <?php if ($av_robot > 0 && (int)$av_per['n'] === 0): ?>
          <br>ℹ️ La granja de robots tapa el sitio, pero <b>no son personas</b>: cuando entra alguien de verdad, se avisa.
        <?php endif; ?>
        <br>📞/💬 <b><?= (int)$av_ped['n'] ?></b> clic(s) de pedir
        (<?= (int)($av_ped['llamadas'] ?? 0) ?> 📞 llamada · <?= (int)($av_ped['clics'] ?? 0) ?> 💬 WhatsApp ·
        <?= (int)($av_ped['consultas'] ?? 0) ?> 📦 consulta · <?= (int)($av_ped['carritos'] ?? 0) ?> 🛒 carrito; en 24 h)
      </p>
      <p class="mini" style="margin-top:8px">
        🧠 <b>El informe inteligente</b> (la tienda que más destaca, quién pide llamada o WhatsApp, lo que
        buscan y no encuentran y lo que espera tu decisión) llega solo todos los días a las
        <?= (int)AVISOS_INFORME_HORA ?>:00 y los <?= e(avisos_dia_semana_nombre((int)AVISOS_INFORME_SEMANA_DIA)) ?>s
        con el acumulado de la semana.
        <?php // Los enlaces NO envían nada (llevan solo-mostrar=1): son para verlo en pantalla cuando quieras. ?>
        <br>👀 Verlo ahora sin que se envíe:
        <a href="<?= e(url('cron/monitoreo_sistema.php?k=' . urlencode(MONITOREO_CLAVE_WEB) . '&solo-mostrar=1&informe=dia')) ?>" target="_blank" rel="noopener">🧠 el del día</a>
        ·
        <a href="<?= e(url('cron/monitoreo_sistema.php?k=' . urlencode(MONITOREO_CLAVE_WEB) . '&solo-mostrar=1&informe=semana')) ?>" target="_blank" rel="noopener">🏆 el de la semana</a>
      </p>
    </div>

    <?php if (!empty($avisos_robots['top'])): ?>
      <div class="sa-card" style="margin-bottom:14px">
        <h3>🤖 Robots más activos (24 h)</h3>
        <p class="mini">
          <?php $trobb = []; foreach ($avisos_robots['top'] as $k => $v) $trobb[] = e($k) . ': <b>' . (int)$v . '</b>'; ?>
          <?= implode(' · ', $trobb) ?>
        </p>
      </div>
    <?php endif; ?>

    <form method="post" id="formAvisos">
      <?= csrf_campo() ?>
      <input type="hidden" name="accion" value="avisos_guardar">

      <?php
      // Agrupar el catálogo por tema para que la lista se lea fácil
      $por_grupo = [];
      foreach (avisos_catalogo() as $tipo => $info) {
          $por_grupo[$info['grupo'] ?? 'Otros'][$tipo] = $info;
      }
      ?>

      <?php foreach ($por_grupo as $grupo => $items): ?>
        <h3 style="font-size:14px;margin:18px 0 8px;color:var(--color-texto-claro);text-transform:uppercase;letter-spacing:.04em">
          <?= e($grupo) ?>
        </h3>

        <?php foreach ($items as $tipo => $info): $on = !empty($avisos_cfg[$tipo]); $n24 = (int)($avisos_24h[$tipo] ?? 0); $n7 = (int)($avisos_semana[$tipo] ?? 0); ?>
          <label for="av_<?= e($tipo) ?>" style="display:flex;gap:11px;align-items:flex-start;background:#fff;border:1px solid <?= $on ? '#16a34a' : 'var(--color-borde)' ?>;border-radius:12px;padding:12px 14px;margin-bottom:8px;box-shadow:var(--sombra-tarjeta);cursor:pointer">
            <input type="checkbox" id="av_<?= e($tipo) ?>" name="avisos[]" value="<?= e($tipo) ?>" <?= $on ? 'checked' : '' ?>
                   style="width:22px;height:22px;flex-shrink:0;margin-top:2px;accent-color:#16a34a;cursor:pointer">
            <span style="flex:1">
              <span style="display:block;font-size:15px;font-weight:700;color:var(--color-texto)"><?= e($info['titulo']) ?></span>
              <span style="display:block;font-size:12.5px;color:var(--color-texto-claro);margin-top:3px;line-height:1.45"><?= e($info['nota']) ?></span>
              <span style="display:block;margin-top:6px">
                <span class="badge-est <?= $on ? 'b-aprobado' : 'b-rechazado' ?>">
                  <?= $on ? '✅ Notificaciones activadas' : '🔕 Notificaciones desactivadas' ?>
                </span>
                <span class="mini" style="margin-left:6px">
                  <?= $n24 > 0 ? $n24 . ' en 24 h' : 'ninguna en 24 h' ?><?= $n7 > 0 ? ' · ' . $n7 . ' en 7 días' : '' ?>
                </span>
              </span>
            </span>
          </label>
        <?php endforeach; ?>
      <?php endforeach; ?>

      <div style="position:sticky;bottom:0;background:linear-gradient(180deg,rgba(255,255,255,0),#fff 40%);padding:14px 0 6px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
        <button type="submit" class="btn" style="padding:13px 22px;font-size:15px">💾 Guardar cambios</button>
        <button type="button" class="btn-mini b-ghost" onclick="document.querySelectorAll('#formAvisos input[type=checkbox]').forEach(function(c){c.checked=true;});">Marcar todas</button>
        <button type="button" class="btn-mini b-ghost" onclick="document.querySelectorAll('#formAvisos input[type=checkbox]').forEach(function(c){c.checked=false;});">Desmarcar todas</button>
        <span class="mini">Los cambios se aplican al instante.</span>
      </div>
    </form>

    <h2 style="font-size:16px;margin:22px 0 10px">📜 Últimos avisos enviados</h2>
    <?php if (!$avisos_ultimos): ?><div class="empty">Todavía no se ha enviado ningún aviso.</div><?php endif; ?>
    <?php foreach ($avisos_ultimos as $a): ?>
      <div class="sa-card" style="padding:10px 12px">
        <div style="font-size:13px">
          <b><?= e(avisos_catalogo()[$a['tipo']]['titulo'] ?? $a['tipo']) ?></b>
          <span class="mini">· <?= e(date('d/m H:i', strtotime((string)$a['creado_en']))) ?>
          <?= $a['es_bot'] ? '· 🤖 ' . e((string)$a['bot']) : '' ?>
          <?= !empty($a['ip']) ? '· IP ' . e((string)$a['ip']) : '' ?></span>
        </div>
        <?php if (!empty($a['resumen'])): ?><div class="mini"><?= e((string)$a['resumen']) ?></div><?php endif; ?>
      </div>
    <?php endforeach; ?>

  <?php elseif ($seccion === 'reportes'): ?>
    <?php
    // Etiquetas y colores de cada estado de reporte
    $rep_badge = [
        'pendiente' => 'b-pendiente',
        'revisado'  => 'b-nuevo',
        'resuelto'  => 'b-aprobado',
        'ignorado'  => 'b-rechazado',
    ];
    $rep_motivo_ico = [
        'Fraude'                 => '💸',
        'Contenido inapropiado'  => '🔞',
        'Información falsa'      => '🤥',
        'Otro'                   => '❓',
    ];
    ?>
    <h2 style="font-size:17px;margin-bottom:10px">🚩 Reportes pendientes (<?= count($reportes_pend) ?>)</h2>
    <?php if (!$reportes_pend): ?>
      <div class="empty">No hay reportes pendientes. 🎉</div>
    <?php endif; ?>
    <?php foreach ($reportes_pend as $r): ?>
      <div class="sa-card">
        <h3>
          <?= $rep_motivo_ico[$r['motivo']] ?? '🚩' ?> <?= e($r['motivo']) ?>
          <span class="badge-est <?= $rep_badge[$r['estado']] ?? 'b-pendiente' ?>"><?= e($r['estado']) ?></span>
        </h3>
        <p style="margin-top:6px;font-size:13px">
          🏪 <b><?= e($r['negocio_nombre'] ?? ('Negocio #' . (int)$r['negocio_id'])) ?></b>
          <?php if (!empty($r['negocio_slug'])): ?>
            · <a class="mini" href="<?= e(url_negocio($r['negocio_slug'])) ?>" target="_blank" rel="noopener">ver ficha</a>
          <?php endif; ?>
          <?php if (($r['negocio_estado'] ?? '') !== 'activo'): ?>
            <span class="mini">(estado actual: <?= e($r['negocio_estado'] ?? '—') ?>)</span>
          <?php endif; ?>
        </p>
        <?php if (!empty($r['producto_titulo'])): ?>
          <p class="mini">📦 Producto: <?= e($r['producto_titulo']) ?> (#<?= (int)$r['producto_id'] ?>)</p>
        <?php endif; ?>
        <p class="mini">
          👤 Reportado por:
          <b><?= $r['usuario_id'] ? e($r['usuario_nombre'] ?? 'Usuario #' . (int)$r['usuario_id']) : 'Anónimo' ?></b>
          <?php if (!empty($r['usuario_email'])): ?>(<?= e($r['usuario_email']) ?>)<?php endif; ?>
          <?php if (!empty($r['ip'])): ?> · IP <?= e($r['ip']) ?><?php endif; ?>
        </p>
        <p class="mini">🗓️ <?= e(date('d/m/Y H:i', strtotime((string)$r['fecha']))) ?></p>
        <?php if (!empty($r['descripcion'])): ?>
          <p style="margin-top:6px;font-size:13px;background:#f8fafc;border:1px solid var(--color-borde);border-radius:8px;padding:8px 10px">
            <?= nl2br(e($r['descripcion'])) ?>
          </p>
        <?php endif; ?>
        <div class="sa-acciones">
          <form method="post" style="display:inline">
            <?= csrf_campo() ?>
            <input type="hidden" name="accion" value="reporte_estado">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <input type="hidden" name="estado" value="revisado">
            <button class="btn-mini b-ok">✓ Marcar como revisado</button>
          </form>
          <form method="post" style="display:inline">
            <?= csrf_campo() ?>
            <input type="hidden" name="accion" value="reporte_suspender">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <input type="hidden" name="negocio_id" value="<?= (int)$r['negocio_id'] ?>">
            <button class="btn-mini b-no" onclick="return confirm('⚠️ ¿Suspender este negocio? Dejará de mostrarse en el directorio y el reporte quedará resuelto.')">🚫 Suspender negocio</button>
          </form>
          <form method="post" style="display:inline">
            <?= csrf_campo() ?>
            <input type="hidden" name="accion" value="reporte_estado">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <input type="hidden" name="estado" value="ignorado">
            <button class="btn-mini b-ghost" onclick="return confirm('¿Ignorar este reporte?')">🙈 Ignorar</button>
          </form>
        </div>
      </div>
    <?php endforeach; ?>

    <h2 style="font-size:16px;margin:20px 0 10px">📜 Reportes ya atendidos (<?= count($reportes_proc) ?>)</h2>
    <?php if (!$reportes_proc): ?><div class="empty">Todavía no has atendido ningún reporte.</div><?php endif; ?>
    <?php foreach ($reportes_proc as $r): ?>
      <div class="sa-card" style="opacity:.75">
        <h3>
          <?= e($r['motivo']) ?>
          <span class="badge-est <?= $rep_badge[$r['estado']] ?? 'b-ghost' ?>"><?= e($r['estado']) ?></span>
        </h3>
        <p class="mini">
          🏪 <?= e($r['negocio_nombre'] ?? ('Negocio #' . (int)$r['negocio_id'])) ?>
          · <?= e(date('d/m/Y H:i', strtotime((string)$r['fecha']))) ?>
          <?php if (!empty($r['atendido_en'])): ?> · atendido el <?= e(date('d/m/Y H:i', strtotime((string)$r['atendido_en']))) ?><?php endif; ?>
        </p>
      </div>
    <?php endforeach; ?>

  <?php elseif ($seccion === 'opiniones'): ?>
    <?php // 🚩 RECLAMOS DE OPINIONES (pedido del jefe, 2026-09-14): la pantalla
          // entera —los reportes por decidir, los dos botones 🗑️ Borrar / 🙈 Ignorar,
          // el historial y el botón de sembrar— vive en includes/vista_opiniones_admin.php,
          // igual que Tiendas y Banners. Ahí mismo carga sus datos. ?>
    <?php require __DIR__ . '/includes/vista_opiniones_admin.php'; ?>

  <?php elseif ($seccion === 'banners'): ?>
    <?php include __DIR__ . '/includes/vista_banners_admin.php'; ?>
  <?php elseif ($seccion === 'estadisticas' || $seccion === 'records'): ?>
    <?php // 📈 Estadísticas + 🏆 Récords FUSIONADAS en una sola página (2026-09-13):
          // `vista_estadisticas_admin.php` es la dueña de la página (diseño st-*) y dentro incrusta
          // `vista_records_admin.php` (los bloques del negocio: más vistos, más buscados, más pedidos…).
          // `seccion=records` entra por aquí a propósito: es un alias, no una página aparte. ?>
    <?php require_once __DIR__ . '/includes/estadisticas.php'; ?>
    <?php require __DIR__ . '/includes/vista_estadisticas_admin.php'; ?>

  <?php elseif ($seccion === 'chatbot'): ?>
    <?php require_once __DIR__ . '/includes/vista_chatbot_admin.php'; ?>
  <?php elseif ($seccion === 'maestro'): ?>
    <?php // 🛠️ EL CONSTRUCTOR DE TIENDAS (2026-09-14): consumo de su clave propia y el embudo
          // de la conversación (incluye/vista_tienda_ia_admin.php). ?>
    <?php require __DIR__ . '/includes/vista_tienda_ia_admin.php'; ?>
  <?php elseif ($seccion === 'supremo'): ?>
    <?php // 👑 EL SUPREMO (2026-09-18): las ventas en vivo y el consumo del módulo del jefe, más el
          // botón que abre la página (`/supremo`), que es la ÚNICA puerta que existe a ese módulo. ?>
    <?php require __DIR__ . '/includes/vista_supremo_admin.php'; ?>
  <?php elseif ($seccion === 'canciones'): ?>
    <?php // 🎵 LAS CANCIONES DE LAS TIENDAS (2026-09-17, pedido del jefe): el jingle de 40 segundos
          // que se le hace a cada tienda nueva, el saldo de las 4 cuentas de Treblo y el consumo de
          // cada una (incluye/vista_canciones_admin.php). ?>
    <?php require __DIR__ . '/includes/vista_canciones_admin.php'; ?>
  <?php elseif ($seccion === 'cerca'): ?>
    <div style="background:#fff;border:1px solid var(--color-borde);border-radius:14px;padding:22px;box-shadow:var(--sombra-tarjeta);text-align:center;max-width:540px;margin:0 auto">
        <div style="font-size:44px">📍</div>
        <h2 style="margin:6px 0 4px;color:var(--color-texto);font-size:20px">Encontrar tiendas cerca de mí</h2>
        <p style="color:var(--color-texto-claro);font-size:14px;margin:0 0 18px">Presiona el botón y la herramienta usará el GPS del celular para mostrarte las tiendas más cercanas a tu ubicación, ordenadas por distancia.</p>
        <a href="<?= url('tiendas-cerca-de-mi.html') ?>" target="_blank" rel="noopener" style="display:inline-block;background:var(--color-primario);color:#fff;font-weight:800;padding:13px 24px;border-radius:999px;text-decoration:none;font-size:16px">📍 Abrir herramienta</a>
        <p style="margin-top:18px;font-size:13px;background:#fff7ed;border:1px solid #fdba74;color:#9a3412;border-radius:10px;padding:10px 12px">📱 <b>Disponible para celulares.</b> En una PC la ubicación suele ser imprecisa o no funcionar.</p>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
