<?php
/**
 * editaproductos.php — EDITOR MASIVO DE PRODUCTOS (precio · unidad · imagen · prompt)
 * ============================================================================
 * QUÉ ES:
 *   La herramienta de trabajo del jefe para dejar el catálogo presentable, en
 *   ESCRITORIO y a ritmo de 20 productos por página (1 lote).
 *
 *   Pedido del jefe (2026-09-11, 1.ª parte):
 *     *"Muéstrame todos los productos por orden de llegada, la imagen actual y
 *      abajo un botón que diga cambiar imagen, y al costado un prompt que lo
 *      crees tú, basado en el contexto: a qué tienda pertenece y en qué rubro
 *      está esa tienda… empecemos por unos 20 y luego el siguiente lote."*
 *
 *   Pedido del jefe (2026-09-11, 2.ª parte — ESTA VERSIÓN):
 *     *"Agrega la opción de editar el precio como un input… la imagen cámbiala
 *      con una opción de arrastrar una imagen encima… cuando pongo una imagen la
 *      página me recarga y como ya estoy por la imagen 22 o 27 tengo que volver
 *      a hacer scroll: NO es necesario recargar la página cada vez que hago un
 *      cambio… si arrastro una imagen encima automáticamente se cambia… el
 *      precio se guarda con un botón Guardar por producto… y en la unidad un
 *      SELECT, con fuzzy para predecir resultados."*
 *
 *   ✅ **NADA RECARGA LA PÁGINA**: todo se guarda por detrás (fetch + JSON) y el
 *      scroll se queda donde está.
 *   ✅ **Arrastrar y soltar** una imagen encima de la foto la publica AL INSTANTE
 *      (y también se puede pegar con Ctrl+V).
 *   ✅ **Precio** (input) y **unidad** (select predictivo con Fuse.js) se guardan
 *      con el botón **💾 Guardar** de ese producto.
 *   ✅ **↩️ Deshacer** por si el arrastre fue a la imagen equivocada.
 *
 * ORDEN DE LLEGADA: `ORDER BY s.id DESC` (el id es el orden real de creación).
 * 20 productos por página = 1 lote de trabajo.
 *
 * LOS PROMPTS no se escriben aquí: viven en `directorio_producto_prompts` y se
 * cargan solos desde `cache/prompts/lote_NN.json`. Lo que el jefe edita queda
 * `origen='jefe'` y ningún lote lo pisa.
 *
 * IMÁGENES: motor compartido (`includes/imagenes.php`): WebP ≤1600 px + versiones
 * de 800 y 300 px. La foto reemplazada NO se borra en el acto: se guarda como
 * "deshacer" y se limpia sola a las 24 h (o al reemplazarla otra vez).
 *
 * Guía del módulo: GUIA_EDICION_PRODUCTOS_PROMPTS_IA.md
 */

require_once __DIR__ . '/config.php';

requiere_login();
if (!es_admin()) { http_response_code(403); die('Acceso denegado. Requiere ser administrador.'); }

$pdo   = db();
$admin = usuario_actual();

$TABLA      = 'directorio_producto_prompts';
$TABLA_ANT  = 'directorio_producto_imagenes_ant';
$POR_PAGINA = 20;
$DIR_LOTES  = __DIR__ . '/cache/prompts';
$UNDO_HORAS = 24;   // cuánto vive la imagen anterior para poder deshacer

/* ============================================================================
 * 1) LAS TABLAS (se crean solas, sin migrador que subir)
 * ========================================================================== */
function ep_asegurar_tablas(PDO $pdo) {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS directorio_producto_prompts (
            producto_id INT(10) UNSIGNED NOT NULL,
            prompt TEXT NOT NULL,
            origen ENUM('asistente','jefe') NOT NULL DEFAULT 'asistente',
            lote SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
            rubro VARCHAR(120) NULL,
            negocio VARCHAR(180) NULL,
            creado_en DATETIME NOT NULL,
            actualizado_en DATETIME NULL,
            PRIMARY KEY (producto_id),
            KEY idx_origen (origen),
            KEY idx_lote (lote)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // Imagen anterior de cada producto = el "deshacer" del arrastre.
        $pdo->exec("CREATE TABLE IF NOT EXISTS directorio_producto_imagenes_ant (
            producto_id INT(10) UNSIGNED NOT NULL,
            ruta VARCHAR(255) NOT NULL,
            ruta_nueva VARCHAR(255) NULL,
            creado_en DATETIME NOT NULL,
            PRIMARY KEY (producto_id),
            KEY idx_creado (creado_en)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $ok = true;
    } catch (Throwable $e) {
        error_log('editaproductos: no se pudieron crear las tablas: ' . $e->getMessage());
        $ok = false;
    }
    return $ok;
}

/* ============================================================================
 * 2) LAS UNIDADES (el catálogo del select predictivo)
 *    · Van AGRUPADAS y en orden alfabético dentro de cada grupo (Regla de Oro
 *      n.º 2: nada de listas largas revueltas).
 *    · Se suman las unidades que YA usan los productos del sitio y, siempre,
 *      la que ese producto tiene guardada (nunca se pierde una unidad rara).
 * ========================================================================== */
function ep_unidades_catalogo() {
    return [
        'Conteo' => ['por unidad','por pieza','por par','por juego','por kit','por docena','por ciento','por millar','por caja','por cajón','por paquete','por bolsa','por saco','por balde','por pack','por lote','por display','por bandeja','por rollo','por cilindro','por galonera','por bidón'],
        'Peso' => ['por kilo','por gramo','por libra','por arroba','por quintal','por tonelada'],
        'Volumen' => ['por litro','por mililitro','por galón','por balón','por botella','por metro cúbico'],
        'Largo y área' => ['por metro','por metro lineal','por metro cuadrado','por centímetro','por milímetro','por pulgada','por pie'],
        'Distancia y carga' => ['por kilómetro','por viaje','por flete','por ruta','por encomienda','por hora de viaje'],
        'Tiempo y servicio' => ['por hora','por media hora','por día','por noche','por semana','por quincena','por mes','por temporada','por turno','por jornada','por sesión','por clase','por consulta','por visita','por atención','por evento','por servicio','por trabajo','por proyecto','por hora de máquina'],
        'Personas y comida' => ['por persona','por porción','por plato','por combo','por menú','por invitado','por niño','por grupo','por pareja'],
        'Del sitio' => ['por habitación','por cama','por asiento','por vehículo','por pantalla','por perfil','por cuenta','por plan','por suscripción','por vacante','por contrato','por página','por catálogo','por m² de construcción','a medida','a cotizar'],
    ];
}

/** Unidades reales que ya usan los productos (las 40 más frecuentes). */
function ep_unidades_del_sitio(PDO $pdo) {
    static $cache = null;
    if ($cache !== null) return $cache;
    $cache = [];
    try {
        foreach ($pdo->query("SELECT unidad, COUNT(*) n FROM directorio_servicios
                               WHERE unidad IS NOT NULL AND unidad <> ''
                               GROUP BY unidad ORDER BY n DESC LIMIT 40") as $f) {
            $cache[] = (string)$f['unidad'];
        }
    } catch (Throwable $e) { $cache = []; }
    return $cache;
}

/**
 * Lista plana de opciones para el select predictivo:
 *   [ ['u' => 'por kilo', 'g' => 'Peso', 'n' => 'por kilo'], … ]
 * `n` es la forma normalizada (sin tildes) que indexa Fuse.js en el navegador.
 */
function ep_opciones_unidad(PDO $pdo, array $extras = []) {
    $vistas = [];
    $salida = [];
    $norm = function ($t) {
        $t = mb_strtolower(trim((string)$t), 'UTF-8');
        return str_replace(['á','à','ä','â','ã','é','è','ë','ê','í','ì','ï','î','ó','ò','ö','ô','ú','ù','ü','û','ñ'],
                           ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','u','u','u','u','n'], $t);
    };
    $meter = function ($u, $g) use (&$vistas, &$salida, $norm) {
        $u = trim((string)$u);
        if ($u === '') return;
        $clave = $norm($u);
        if (isset($vistas[$clave])) return;
        $vistas[$clave] = true;
        $salida[] = ['u' => $u, 'g' => $g, 'n' => $clave];
    };

    // 1) Lo que ya usan los productos del sitio (contexto real del catálogo)
    foreach (ep_unidades_del_sitio($pdo) as $u) $meter($u, 'Las que ya usa el sitio');
    // 2) El catálogo completo, agrupado
    foreach (ep_unidades_catalogo() as $grupo => $lista) {
        foreach ($lista as $u) $meter($u, $grupo);
    }
    // 3) Las unidades que traen los productos visibles (por si alguna es única)
    foreach ($extras as $u) $meter($u, 'De estos productos');
    return $salida;
}

/* ============================================================================
 * 3) PROMPT BASE AUTOMÁTICO (para los productos que aún no tienen el del asistente)
 *    El contexto manda: SIEMPRE dice a qué tienda y a qué rubro pertenece.
 * ========================================================================== */
function ep_prompt_base(array $p) {
    $titulo   = trim((string)($p['titulo'] ?? ''));
    $negocio  = trim((string)($p['negocio'] ?? ''));
    $rubro    = trim((string)($p['rubro'] ?? '')) ?: 'sin rubro definido';
    $distrito = trim((string)($p['distrito'] ?? '')) ?: 'Chimbote';
    $es_serv = (($p['tipo_producto'] ?? '') === 'virtual')
            || (stripos($titulo, 'servicio') !== false)
            || (stripos($titulo, 'curso') !== false)
            || (stripos($titulo, 'alquiler') !== false)
            || (stripos($titulo, 'mensualidad') !== false)
            || (stripos($titulo, 'inscripci') !== false);

    return 'Imagen publicitaria para el catálogo de la tienda «' . $negocio . '» de ' . $distrito
       . ', Perú — rubro: ' . $rubro . ".\n\n"
       . 'Producto: «' . $titulo . '». '
       . ($es_serv
            ? 'Es un SERVICIO (no un objeto): muestra la actividad, el lugar de trabajo y el resultado de forma clara y creíble.'
            : 'Muestra el producto exacto, completo, limpio y apetecible/atractivo, en primer plano.')
       . "\n\n"
       . 'Contexto obligatorio: interpreta el producto dentro del rubro «' . $rubro . '»'
       . ' (no en otro rubro), con aspecto real de un negocio peruano de barrio.'
       . "\n\n"
       . 'Estilo: fotografía publicitaria realista, luz natural suave, fondo limpio y despejado, '
       . 'encuadre centrado, formato cuadrado 1:1, alta resolución. '
       . 'Sin texto, sin marcas de agua, sin logotipos y sin rostros identificables.';
}

/* ============================================================================
 * 4) CARGA DE LOTES: cache/prompts/lote_NN.json  →  tabla de prompts
 * ========================================================================== */
function ep_sincronizar_lotes(PDO $pdo, string $dir) {
    $res = ['archivos' => [], 'nuevos' => 0, 'actualizados' => 0, 'leidos' => 0];
    if (!is_dir($dir)) return $res;
    $archivos = glob($dir . '/lote_*.json') ?: [];
    sort($archivos, SORT_STRING);
    if (!$archivos) return $res;

    $actuales = [];
    try {
        foreach ($pdo->query("SELECT producto_id, lote, origen, prompt FROM directorio_producto_prompts") as $f) {
            $actuales[(int)$f['producto_id']] = [
                'lote' => (int)$f['lote'], 'origen' => (string)$f['origen'], 'prompt' => (string)$f['prompt'],
            ];
        }
    } catch (Throwable $e) { return $res; }

    foreach ($archivos as $ruta) {
        $txt_json = @file_get_contents($ruta);
        if ($txt_json === false) continue;
        $j = json_decode($txt_json, true);
        if (!is_array($j) || empty($j['prompts']) || !is_array($j['prompts'])) continue;
        $lote = (int)($j['lote'] ?? 0);
        $res['archivos'][] = basename($ruta) . ' (lote ' . $lote . ', ' . count($j['prompts']) . ' prompts)';

        foreach ($j['prompts'] as $it) {
            $pid = (int)($it['producto_id'] ?? 0);
            $txt = trim((string)($it['prompt'] ?? ''));
            if ($pid <= 0 || $txt === '') continue;
            $res['leidos']++;
            $rubro = mb_substr(trim((string)($it['rubro'] ?? '')), 0, 120);
            $neg   = mb_substr(trim((string)($it['negocio'] ?? '')), 0, 180);
            $act   = $actuales[$pid] ?? null;

            if ($act === null) {
                $pdo->prepare("INSERT INTO directorio_producto_prompts
                        (producto_id, prompt, origen, lote, rubro, negocio, creado_en)
                        VALUES (?, ?, 'asistente', ?, ?, ?, NOW())")
                    ->execute([$pid, $txt, $lote, ($rubro !== '' ? $rubro : null), ($neg !== '' ? $neg : null)]);
                $actuales[$pid] = ['lote' => $lote, 'origen' => 'asistente', 'prompt' => $txt];
                $res['nuevos']++;
            } elseif ($act['origen'] === 'asistente' && $act['lote'] <= $lote && $act['prompt'] !== $txt) {
                $pdo->prepare("UPDATE directorio_producto_prompts
                                  SET prompt = ?, lote = ?, rubro = ?, negocio = ?, actualizado_en = NOW()
                                WHERE producto_id = ?")
                    ->execute([$txt, $lote, ($rubro !== '' ? $rubro : null), ($neg !== '' ? $neg : null), $pid]);
                $actuales[$pid]['prompt'] = $txt;
                $actuales[$pid]['lote']   = $lote;
                $res['actualizados']++;
            }
        }
    }
    return $res;
}

/** Prompt que le tocaría a un producto según los lotes cargados (o null). */
function ep_prompt_del_lote(PDO $pdo, string $dir, int $pid) {
    foreach (glob($dir . '/lote_*.json') ?: [] as $ruta) {
        $j = json_decode((string)@file_get_contents($ruta), true);
        if (!is_array($j) || empty($j['prompts'])) continue;
        foreach ($j['prompts'] as $it) {
            if ((int)($it['producto_id'] ?? 0) === $pid) return trim((string)($it['prompt'] ?? ''));
        }
    }
    return null;
}

/* ============================================================================
 * 5) IMÁGENES: referencias, deshacer, limpieza
 * ========================================================================== */
/**
 * ¿Cuántos registros MÁS usan esta imagen? (incluye las imágenes guardadas para
 * deshacer: si no, al deshacer la foto ya no estaría en el disco).
 */
function ep_referencias_imagen(PDO $pdo, string $ruta, int $excluir_producto = 0) {
    global $TABLA_ANT;
    $ruta = trim($ruta);
    if ($ruta === '') return 0;
    $n = 0;
    $consultas = [
        ["SELECT COUNT(*) FROM directorio_servicios WHERE imagen = ? AND id <> ?", [$ruta, $excluir_producto]],
        ["SELECT COUNT(*) FROM directorio_producto_fotos WHERE ruta = ?", [$ruta]],
        ["SELECT COUNT(*) FROM directorio_fotos WHERE ruta = ?", [$ruta]],
        ["SELECT COUNT(*) FROM directorio_banners WHERE imagen = ?", [$ruta]],
        ["SELECT COUNT(*) FROM {$TABLA_ANT} WHERE ruta = ? AND producto_id <> ?", [$ruta, $excluir_producto]],
    ];
    foreach ($consultas as $c) {
        try { $s = $pdo->prepare($c[0]); $s->execute($c[1]); $n += (int)$s->fetchColumn(); }
        catch (Throwable $e) { /* si esa tabla o columna no existe, no se cuenta */ }
    }
    return $n;
}

/** Guarda (o reemplaza) la imagen anterior de un producto para poder deshacer. */
function ep_guardar_undo(PDO $pdo, int $pid, string $ruta_vieja, string $ruta_nueva) {
    global $TABLA_ANT;
    if ($ruta_vieja === '' || $ruta_vieja === $ruta_nueva) return;
    $anterior_previa = '';
    try {
        $s = $pdo->prepare("SELECT ruta FROM {$TABLA_ANT} WHERE producto_id = ?");
        $s->execute([$pid]);
        $anterior_previa = (string)$s->fetchColumn();
    } catch (Throwable $e) { return; }

    $pdo->prepare("INSERT INTO {$TABLA_ANT} (producto_id, ruta, ruta_nueva, creado_en)
                   VALUES (?, ?, ?, NOW())
                   ON DUPLICATE KEY UPDATE ruta = VALUES(ruta), ruta_nueva = VALUES(ruta_nueva), creado_en = NOW()")
        ->execute([$pid, $ruta_vieja, $ruta_nueva]);

    // La imagen anterior-anterior ya no sirve para deshacer: se borra si nadie la usa.
    if ($anterior_previa !== '' && $anterior_previa !== $ruta_vieja) {
        if (ep_referencias_imagen($pdo, $anterior_previa) === 0) img_borrar($anterior_previa);
    }
}

/** Poda el "deshacer" caducado: la imagen vuelve a borrarse si ya nadie la usa. */
function ep_limpiar_antiguas(PDO $pdo, int $horas = 24) {
    global $TABLA_ANT;
    $borradas = 0;
    try {
        $s = $pdo->prepare("SELECT producto_id, ruta FROM {$TABLA_ANT}
                             WHERE creado_en < DATE_SUB(NOW(), INTERVAL ? HOUR) LIMIT 40");
        $s->bindValue(1, $horas, PDO::PARAM_INT);
        $s->execute();
        $filas = $s->fetchAll();
        foreach ($filas as $f) {
            $pdo->prepare("DELETE FROM {$TABLA_ANT} WHERE producto_id = ?")->execute([(int)$f['producto_id']]);
            if (ep_referencias_imagen($pdo, (string)$f['ruta']) === 0) { img_borrar((string)$f['ruta']); $borradas++; }
        }
    } catch (Throwable $e) { /* la página funciona igual */ }
    return $borradas;
}

/**
 * PUBLICAR UNA IMAGEN (arrastrar y soltar, Ctrl+V o el formulario clásico).
 * Devuelve ['ok'=>bool, 'error'=>…, ...datos para el navegador].
 */
function ep_publicar_imagen(PDO $pdo, int $pid, $archivo, bool $aplicar_todos) {
    $s = $pdo->prepare("SELECT s.id, s.titulo, s.imagen, s.negocio_id,
                               n.slug AS negocio_slug, n.nombre AS negocio, c.nombre AS rubro
                          FROM directorio_servicios s
                          LEFT JOIN directorio_negocios n ON n.id = s.negocio_id
                          LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                         WHERE s.id = ? LIMIT 1");
    $s->execute([$pid]);
    $prod = $s->fetch();
    if (!$prod) return ['ok' => false, 'error' => 'Ese producto ya no existe.'];
    if (empty($archivo['name'])) return ['ok' => false, 'error' => 'No llegó ninguna imagen.'];

    $slug = preg_replace('/[^a-z0-9_\-]/i', '', (string)($prod['negocio_slug'] ?? ''));
    $carpeta = 'fotos/' . ($slug !== '' ? $slug : 'ia');
    $nombre  = 'ia_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));

    $res = img_guardar_subida($archivo, $carpeta, $nombre);
    if (empty($res['ok'])) return ['ok' => false, 'error' => 'No se pudo guardar la imagen: ' . ($res['error'] ?? 'error desconocido')];

    $nueva = (string)$res['rel'];
    $vieja = (string)($prod['imagen'] ?? '');

    // a) Galería del producto: se reemplaza la 1.ª foto (o se crea)
    $g = $pdo->prepare("SELECT id FROM directorio_producto_fotos WHERE producto_id = ?
                         ORDER BY orden ASC, id ASC LIMIT 1");
    $g->execute([$pid]);
    $foto_id = (int)$g->fetchColumn();
    if ($foto_id > 0) {
        $pdo->prepare("UPDATE directorio_producto_fotos SET ruta = ? WHERE id = ?")->execute([$nueva, $foto_id]);
    } else {
        $pdo->prepare("INSERT INTO directorio_producto_fotos (producto_id, ruta, orden) VALUES (?, ?, 0)")
            ->execute([$pid, $nueva]);
    }

    // b) Portada (es la que manda en todo el sitio)
    $pdo->prepare("UPDATE directorio_servicios SET imagen = ? WHERE id = ?")->execute([$nueva, $pid]);

    // c) Deshacer: la anterior se guarda (y su archivo NO se borra todavía)
    if ($vieja !== '' && $vieja !== $nueva) ep_guardar_undo($pdo, $pid, $vieja, $nueva);

    // d) Opcional: los demás productos de la tienda que usaban la misma imagen
    $aplicados = 0;
    $ids_extra = [];
    if ($aplicar_todos && $vieja !== '') {
        $u = $pdo->prepare("SELECT id FROM directorio_servicios WHERE imagen = ? AND negocio_id = ? AND id <> ?");
        $u->execute([$vieja, (int)$prod['negocio_id'], $pid]);
        $ids_extra = array_map('intval', array_column($u->fetchAll(), 'id'));
    }
    if ($ids_extra) {
        $in = implode(',', $ids_extra);
        $pdo->exec("UPDATE directorio_servicios SET imagen = " . $pdo->quote($nueva) . " WHERE id IN ($in)");
        $pdo->exec("UPDATE directorio_producto_fotos SET ruta = " . $pdo->quote($nueva) . " WHERE producto_id IN ($in)");
        foreach ($ids_extra as $otro) ep_guardar_undo($pdo, $otro, $vieja, $nueva);
        $aplicados = count($ids_extra);
    }

    $ancho = (int)($res['ancho'] ?? 0);
    $alto  = (int)($res['alto'] ?? 0);
    $peso  = (int)($res['peso'] ?? 0);
    $completa = img_buscar_variante($nueva, IMG_ANCHO_COMPLETO) ?: $nueva;

    return [
        'ok'        => true,
        'producto_id' => $pid,
        'titulo'    => (string)$prod['titulo'],
        'ruta'      => $nueva,
        'anterior'  => $vieja,
        'aplicados' => $aplicados,
        'ids_extra' => $ids_extra,
        'peso_kb'   => (int)round($peso / 1024),
        'ancho'     => $ancho,
        'alto'      => $alto,
        'url'       => img_url($nueva),
        'srcset'    => img_srcset($nueva),
        'url_full'  => url_imagen($completa),
        'aviso'     => ($vieja === '' ? '📷 Imagen publicada' : '🔄 Imagen cambiada')
                     . ' para «' . (string)$prod['titulo'] . '»'
                     . ($aplicados > 0 ? ' y ' . $aplicados . ' producto(s) más' : '')
                     . ' · ' . (int)round($peso / 1024) . ' KB en WebP'
                     . ($ancho > 0 ? ' (' . $ancho . '×' . $alto . ' px)' : ''),
    ];
}

/* ============================================================================
 * 6) ARRANQUE: tablas + lotes + limpieza del "deshacer" caducado
 * ========================================================================== */
$tablas_ok = ep_asegurar_tablas($pdo);
$sync = $tablas_ok ? ep_sincronizar_lotes($pdo, $DIR_LOTES)
                   : ['archivos' => [], 'nuevos' => 0, 'actualizados' => 0, 'leidos' => 0];
if ($tablas_ok) ep_limpiar_antiguas($pdo, $UNDO_HORAS);

/* ============================================================================
 * 7) ACCIONES (POST) — contestan JSON si vienen del editor (ajax=1) y, si no,
 *    siguen funcionando como formulario clásico con recarga (respaldo).
 * ========================================================================== */
$nota = '';
$nota_tipo = 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tablas_ok) {
    csrf_verificar();
    $accion  = (string)($_POST['accion'] ?? '');
    $pid     = (int)($_POST['producto_id'] ?? 0);
    $es_ajax = (($_POST['ajax'] ?? '') === '1')
            || (strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest');

    $volver = 'editaproductos.php?p=' . max(1, (int)($_POST['volver_p'] ?? 1));
    if (trim((string)($_POST['volver_f'] ?? '')) !== '') $volver .= '&f=' . urlencode((string)$_POST['volver_f']);
    if (trim((string)($_POST['volver_q'] ?? '')) !== '') $volver .= '&q=' . urlencode((string)$_POST['volver_q']);

    $json = ['ok' => false, 'mensaje' => ''];

    /* ---------- 7.1 Guardar PRECIO + UNIDAD (botón 💾 de cada producto) ---------- */
    if ($accion === 'guardar_datos' && $pid > 0) {
        $precio_txt = str_replace(',', '.', trim((string)($_POST['precio'] ?? '0')));
        $precio = ($precio_txt === '' || !is_numeric($precio_txt)) ? 0.0 : (float)$precio_txt;
        $precio = max(0, round($precio, 2));

        $unidad = trim((string)($_POST['unidad'] ?? ''));
        $unidad = preg_replace('/\s+/u', ' ', $unidad);
        if (mb_strlen($unidad, 'UTF-8') > 50) $unidad = mb_substr($unidad, 0, 50, 'UTF-8');

        $s = $pdo->prepare("SELECT titulo FROM directorio_servicios WHERE id = ? LIMIT 1");
        $s->execute([$pid]);
        $titulo = (string)$s->fetchColumn();

        if ($titulo === '') {
            $json['mensaje'] = 'Ese producto ya no existe.';
        } else {
            $pdo->prepare("UPDATE directorio_servicios SET precio = ?, unidad = ? WHERE id = ?")
                ->execute([$precio, ($unidad !== '' ? $unidad : null), $pid]);
            $json = [
                'ok'         => true,
                'producto_id'=> $pid,
                'precio'     => $precio,
                'precio_vista' => number_format($precio, 2, '.', ''),
                'unidad'     => $unidad,
                'mensaje'    => '💾 Precio y unidad guardados' . ($precio <= 0 ? ' (S/ 0 = a consultar)' : '') . '.',
            ];
        }
    }

    /* ---------- 7.2 Guardar el PROMPT (el que escribió el jefe) ---------- */
    if ($accion === 'guardar_prompt' && $pid > 0) {
        $texto = trim((string)($_POST['prompt'] ?? ''));
        $s = $pdo->prepare("SELECT s.titulo, n.nombre AS negocio, c.nombre AS rubro
                              FROM directorio_servicios s
                              LEFT JOIN directorio_negocios n ON n.id = s.negocio_id
                              LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                             WHERE s.id = ? LIMIT 1");
        $s->execute([$pid]);
        $ctx = $s->fetch();
        if (!$ctx) {
            $json['mensaje'] = 'Ese producto ya no existe.';
        } elseif ($texto === '') {
            $json['mensaje'] = 'El prompt está vacío: escribe algo antes de guardar.';
        } else {
            $pdo->prepare("INSERT INTO directorio_producto_prompts
                    (producto_id, prompt, origen, lote, rubro, negocio, creado_en, actualizado_en)
                    VALUES (?, ?, 'jefe', 0, ?, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE prompt = VALUES(prompt), origen = 'jefe', actualizado_en = NOW()")
                ->execute([
                    $pid, $texto,
                    mb_substr((string)($ctx['rubro'] ?? ''), 0, 120) ?: null,
                    mb_substr((string)($ctx['negocio'] ?? ''), 0, 180) ?: null,
                ]);
            $json = ['ok' => true, 'producto_id' => $pid, 'origen' => 'jefe',
                     'mensaje' => '💾 Prompt guardado: ya es tuyo, ningún lote lo va a pisar.'];
        }
    }

    /* ---------- 7.3 Volver al prompt del asistente ---------- */
    if ($accion === 'restaurar_prompt' && $pid > 0) {
        $pdo->prepare("UPDATE directorio_producto_prompts SET origen = 'asistente', actualizado_en = NOW()
                        WHERE producto_id = ?")->execute([$pid]);
        // Se re-aplica el texto del lote en el acto (sin recargar la página)
        ep_sincronizar_lotes($pdo, $DIR_LOTES);
        $s = $pdo->prepare("SELECT prompt, origen FROM directorio_producto_prompts WHERE producto_id = ?");
        $s->execute([$pid]);
        $fila = $s->fetch();
        $texto = $fila ? (string)$fila['prompt'] : '';
        $json = ['ok' => true, 'producto_id' => $pid, 'origen' => $fila ? (string)$fila['origen'] : 'asistente',
                 'prompt' => $texto, 'mensaje' => '↩️ Prompt devuelto al del asistente.'];
    }

    /* ---------- 7.4 Publicar / cambiar la IMAGEN (arrastrar, pegar o formulario) ---------- */
    if ($accion === 'cambiar_imagen' && $pid > 0) {
        $r = ep_publicar_imagen($pdo, $pid, $_FILES['foto'] ?? null, !empty($_POST['aplicar_todos']));
        if (empty($r['ok'])) {
            $json['mensaje'] = (string)($r['error'] ?? 'No se pudo publicar la imagen.');
            $nota = (string)$r['error']; $nota_tipo = 'error';
        } else {
            $json = $r;
            $json['mensaje'] = (string)$r['aviso'];
            $nota = (string)$r['aviso'];
        }
    }

    /* ---------- 7.5 Deshacer el último cambio de imagen ---------- */
    if ($accion === 'deshacer_imagen' && $pid > 0) {
        global $TABLA_ANT;
        $s = $pdo->prepare("SELECT ruta, ruta_nueva FROM {$TABLA_ANT} WHERE producto_id = ?");
        $s->execute([$pid]);
        $ant = $s->fetch();
        if (!$ant || trim((string)$ant['ruta']) === '') {
            $json['mensaje'] = 'Ya no hay nada que deshacer en este producto.';
        } else {
            $ruta_ant = (string)$ant['ruta'];
            $ruta_act = (string)$ant['ruta_nueva'];
            $pdo->prepare("DELETE FROM {$TABLA_ANT} WHERE producto_id = ?")->execute([$pid]);
            $pdo->prepare("UPDATE directorio_servicios SET imagen = ? WHERE id = ?")->execute([$ruta_ant, $pid]);
            $g = $pdo->prepare("SELECT id FROM directorio_producto_fotos WHERE producto_id = ?
                                 ORDER BY orden ASC, id ASC LIMIT 1");
            $g->execute([$pid]);
            $foto_id = (int)$g->fetchColumn();
            if ($foto_id > 0) {
                $pdo->prepare("UPDATE directorio_producto_fotos SET ruta = ? WHERE id = ?")->execute([$ruta_ant, $foto_id]);
            } else {
                $pdo->prepare("INSERT INTO directorio_producto_fotos (producto_id, ruta, orden) VALUES (?, ?, 0)")
                    ->execute([$pid, $ruta_ant]);
            }
            // La imagen que se acaba de quitar se borra solo si nadie más la usa
            if ($ruta_act !== '' && $ruta_act !== $ruta_ant && ep_referencias_imagen($pdo, $ruta_act, $pid) === 0) {
                img_borrar($ruta_act);
            }
            $completa = img_buscar_variante($ruta_ant, IMG_ANCHO_COMPLETO) ?: $ruta_ant;
            $json = [
                'ok' => true, 'producto_id' => $pid,
                'ruta' => $ruta_ant, 'url' => img_url($ruta_ant), 'srcset' => img_srcset($ruta_ant),
                'url_full' => url_imagen($completa),
                'mensaje' => '↩️ Imagen anterior restaurada.',
            ];
        }
    }

    if ($es_ajax) {
        // Contadores frescos: la página los repinta sin recargar
        try {
            $json['contadores'] = [
                'con_prompt' => (int)$pdo->query("SELECT COUNT(*) FROM {$TABLA} pr
                        INNER JOIN directorio_servicios s ON s.id = pr.producto_id")->fetchColumn(),
                'con_imagen' => (int)$pdo->query("SELECT COUNT(*) FROM directorio_servicios
                        WHERE imagen IS NOT NULL AND imagen <> ''")->fetchColumn(),
            ];
        } catch (Throwable $e) { /* sin contadores, todo lo demás funciona */ }
        json_response($json);
    }

    redirect($volver . ($nota !== '' ? '&aviso=' . urlencode($nota) . '&tipo=' . $nota_tipo : ''));
}

if (isset($_GET['aviso'])) {
    $nota = (string)$_GET['aviso'];
    $nota_tipo = (($_GET['tipo'] ?? 'ok') === 'error') ? 'error' : 'ok';
}

/* ============================================================================
 * 8) LISTADO: orden de llegada (últimos creados primero), 20 por página
 * ========================================================================== */
$filtro = (string)($_GET['f'] ?? 'todos');
if (!in_array($filtro, ['todos', 'pendientes', 'sinfoto', 'confoto'], true)) $filtro = 'todos';
$q      = trim((string)($_GET['q'] ?? ''));
$pagina = max(1, (int)($_GET['p'] ?? 1));

$where = [];
$par   = [];
if ($q !== '') {
    $where[] = "(s.titulo LIKE ? OR n.nombre LIKE ?)";
    $par[] = '%' . $q . '%';
    $par[] = '%' . $q . '%';
}
if ($filtro === 'pendientes') $where[] = "pr.producto_id IS NULL";
if ($filtro === 'sinfoto')    $where[] = "(s.imagen IS NULL OR s.imagen = '')";
if ($filtro === 'confoto')    $where[] = "(s.imagen IS NOT NULL AND s.imagen <> '')";
$wsql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

$desde = "FROM directorio_servicios s
          LEFT JOIN directorio_negocios n   ON n.id = s.negocio_id
          LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
          LEFT JOIN directorio_distritos d  ON d.id = n.distrito_id
          LEFT JOIN {$TABLA} pr             ON pr.producto_id = s.id
          LEFT JOIN {$TABLA_ANT} ua         ON ua.producto_id = s.id" . $wsql;

$st = $pdo->prepare("SELECT COUNT(*) " . $desde);
$st->execute($par);
$total = (int)$st->fetchColumn();
$paginas = max(1, (int)ceil($total / $POR_PAGINA));
if ($pagina > $paginas) $pagina = $paginas;
$offset = ($pagina - 1) * $POR_PAGINA;

$sql = "SELECT s.id, s.titulo, s.descripcion, s.precio, s.unidad, s.tipo_producto, s.imagen,
               s.creado_en, s.negocio_id, s.disponible_hasta,
               n.nombre AS negocio, n.slug AS negocio_slug,
               c.nombre AS rubro, d.nombre AS distrito,
               pr.prompt, pr.origen, pr.lote,
               ua.ruta AS ruta_anterior,
               (SELECT pf.ruta FROM directorio_producto_fotos pf
                 WHERE pf.producto_id = s.id ORDER BY pf.orden ASC, pf.id ASC LIMIT 1) AS primera_foto,
               (SELECT COUNT(*) FROM directorio_servicios x
                 WHERE x.imagen = s.imagen AND x.negocio_id = s.negocio_id
                   AND s.imagen IS NOT NULL AND s.imagen <> '') AS comparten
        " . $desde . " ORDER BY s.id DESC LIMIT ? OFFSET ?";
$st = $pdo->prepare($sql);
$i = 1;
foreach ($par as $v) { $st->bindValue($i++, $v, PDO::PARAM_STR); }
$st->bindValue($i++, $POR_PAGINA, PDO::PARAM_INT);
$st->bindValue($i, $offset, PDO::PARAM_INT);
$st->execute();
$productos = $st->fetchAll();

/* Contadores de arriba */
$stats = ['total' => 0, 'con_prompt' => 0, 'con_imagen' => 0];
try {
    $stats['total']      = (int)$pdo->query("SELECT COUNT(*) FROM directorio_servicios")->fetchColumn();
    $stats['con_imagen'] = (int)$pdo->query("SELECT COUNT(*) FROM directorio_servicios WHERE imagen IS NOT NULL AND imagen <> ''")->fetchColumn();
    if ($tablas_ok) {
        $stats['con_prompt'] = (int)$pdo->query("SELECT COUNT(*) FROM {$TABLA} pr
            INNER JOIN directorio_servicios s ON s.id = pr.producto_id")->fetchColumn();
    }
} catch (Throwable $e) { /* la página se muestra igual */ }

$lotes_guardados = [];
if ($tablas_ok) {
    try {
        foreach ($pdo->query("SELECT lote, COUNT(*) n FROM {$TABLA} WHERE origen = 'asistente'
                              GROUP BY lote ORDER BY lote") as $f) {
            $lotes_guardados[(int)$f['lote']] = (int)$f['n'];
        }
    } catch (Throwable $e) {}
}

/* Opciones del select de unidades (catálogo + las que ya usa el sitio + las de esta página) */
$unidades_propios = [];
foreach ($productos as $p) { $u = trim((string)$p['unidad']); if ($u !== '') $unidades_propios[] = $u; }
$opciones_unidad = ep_opciones_unidad($pdo, $unidades_propios);

$titulo_pagina = 'Editor de productos (precio · unidad · imagen · prompt)';
include __DIR__ . '/includes/header.php';
?>
<style>
/* ===== 🎨 EDITOR MASIVO DE PRODUCTOS — estilos del módulo (componente puntual:
   el CSS compartido va en components.css con su ?v=) ======================= */
.ep-wrap{max-width:1240px;margin:0 auto;padding:14px 16px 90px}
.ep-h1{font-size:22px;font-weight:800;margin:0 0 4px}
.ep-sub{font-size:14px;color:var(--color-texto-claro);margin:0 0 14px;line-height:1.5}
.ep-caja{background:#fff;border:1px solid var(--color-borde);border-radius:var(--radio-borde);box-shadow:var(--sombra-tarjeta);padding:14px;margin-bottom:14px}
.ep-pasos{display:grid;gap:8px;grid-template-columns:repeat(3,1fr)}
@media(max-width:860px){.ep-pasos{grid-template-columns:1fr}}
.ep-paso{background:#fdfaf5;border:1px dashed var(--color-borde);border-radius:12px;padding:10px 12px;font-size:13.5px;line-height:1.45}
.ep-paso b{display:block;font-size:14.5px}
.ep-aviso{border-radius:10px;padding:12px 14px;margin-bottom:14px;font-weight:600;font-size:15px;line-height:1.4}
.ep-aviso--ok{background:#dcfce7;color:#15803d;border:1px solid #bbf7d0}
.ep-aviso--error{background:#fee2e2;color:#b91c1c;border:1px solid #fecaca}
.ep-contadores{display:flex;flex-wrap:wrap;gap:8px;margin:0 0 12px}
.ep-cont{background:#fff;border:1px solid var(--color-borde);border-radius:999px;padding:6px 12px;font-size:13.5px;font-weight:700}
.ep-filtros{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-bottom:12px}
.ep-chip{border:1px solid var(--color-borde);background:#fff;border-radius:999px;padding:8px 14px;font-size:14px;font-weight:700;text-decoration:none;color:inherit}
.ep-chip--on{background:var(--color-primario,#6d071a);color:#fff;border-color:var(--color-primario,#6d071a)}
.ep-busca{flex:1 1 260px;min-width:220px}
.ep-busca input{width:100%;font-size:15px;padding:10px 12px;border:1px solid var(--color-borde);border-radius:10px}
.ep-card{background:#fff;border:1px solid var(--color-borde);border-radius:var(--radio-borde);box-shadow:var(--sombra-tarjeta);padding:14px;margin-bottom:16px}
.ep-card--sucio{border-color:#f59e0b;box-shadow:0 0 0 3px #fef3c7}
.ep-card--destino{border-color:#123c6b;box-shadow:0 0 0 3px #dbeafe}
.ep-card--destino.ep-card--sucio{border-color:#f59e0b;box-shadow:0 0 0 3px #fef3c7,0 0 0 6px #dbeafe}
.ep-card__head{display:flex;flex-wrap:wrap;gap:6px 10px;align-items:baseline;margin-bottom:4px}
.ep-card__id{font-size:13px;font-weight:800;color:var(--color-texto-claro)}
.ep-card__titulo{font-size:18px;font-weight:800;margin:0;line-height:1.25}
.ep-chips{display:flex;flex-wrap:wrap;gap:6px;margin:6px 0 10px}
.ep-chipmini{font-size:12.5px;font-weight:700;border-radius:999px;padding:3px 10px;background:#f3f4f6;color:#374151;border:1px solid #e5e7eb}
.ep-chipmini--rubro{background:#eef2ff;color:#3730a3;border-color:#c7d2fe}
.ep-chipmini--ok{background:#dcfce7;color:#15803d;border-color:#bbf7d0}
.ep-chipmini--falta{background:#fff7ed;color:#9a3412;border-color:#fed7aa}
.ep-cuerpo{display:grid;gap:16px;grid-template-columns:330px 1fr;align-items:start}
@media(max-width:900px){.ep-cuerpo{grid-template-columns:1fr}}

/* ---- Zona de la imagen: se arrastra la foto ENCIMA ---- */
.ep-foto__marco{position:relative;width:100%;aspect-ratio:1/1;border-radius:12px;overflow:hidden;background:#f9fafb;border:2px dashed #d6d3d1;display:flex;align-items:center;justify-content:center;transition:border-color .12s,background .12s}
.ep-foto__marco img{width:100%;height:100%;object-fit:contain;display:block;background:#fff}
.ep-foto__vacia{font-size:13px;font-weight:700;color:var(--color-texto-claro);text-align:center;padding:12px;line-height:1.45}
.ep-drop{cursor:copy}
.ep-drop__velo{position:absolute;inset:0;display:none;flex-direction:column;align-items:center;justify-content:center;gap:4px;background:rgba(18,60,107,.86);color:#fff;text-align:center;padding:12px;pointer-events:none}
.ep-drop__velo b{font-size:17px;font-weight:800}
.ep-drop__velo span{font-size:12.5px;opacity:.9}
.ep-drop--encima .ep-drop__velo{display:flex}
.ep-drop--encima .ep-foto__marco{border-color:#123c6b;background:#eef2ff}
.ep-drop__sello{position:absolute;left:8px;top:8px;background:#f59e0b;color:#fff;font-size:12px;font-weight:800;border-radius:999px;padding:4px 10px}
.ep-drop__sello[hidden]{display:none}
.ep-foto__pie{font-size:12px;color:var(--color-texto-claro);margin-top:8px;line-height:1.45;word-break:break-all}
.ep-foto__ayuda{font-size:12.5px;color:#374151;margin-top:6px;line-height:1.45}
.ep-foto__ayuda b{color:#123c6b}
.ep-estado{margin-top:8px;font-size:13px;font-weight:700;line-height:1.4}
.ep-estado--ok{color:#15803d}
.ep-estado--error{color:#b91c1c}
.ep-estado--curso{color:#123c6b}
.ep-progreso{margin-top:6px;height:6px;border-radius:99px;background:#e5e7eb;overflow:hidden;display:none}
.ep-progreso--on{display:block}
.ep-progreso i{display:block;height:100%;width:0;background:#123c6b;transition:width .2s}

/* ---- Datos: precio + unidad + Guardar ---- */
.ep-datos{display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;background:#fdfaf5;border:1px solid var(--color-borde);border-radius:12px;padding:10px 12px}
.ep-campo{display:flex;flex-direction:column;gap:4px}
.ep-campo__eti{font-size:12px;font-weight:800;color:var(--color-texto-claro);text-transform:uppercase;letter-spacing:.04em}
.ep-precio-caja{display:flex;align-items:center;gap:6px;border:1px solid var(--color-borde);border-radius:10px;background:#fff;padding:0 10px}
.ep-precio-caja span{font-weight:800;color:#374151}
.ep-precio-caja input{border:0;outline:none;font-size:16px;padding:10px 0;width:110px;font-family:inherit}
.ep-combo{position:relative;min-width:230px}
.ep-combo input{width:100%;font-size:15px;padding:10px 30px 10px 12px;border:1px solid var(--color-borde);border-radius:10px;background:#fff;font-family:inherit}
.ep-combo__flecha{position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#78716c;font-size:12px}
.ep-combo__lista{position:absolute;z-index:80;left:0;right:0;top:calc(100% + 4px);max-height:290px;overflow:auto;background:#fff;border:1px solid #d6d3d1;border-radius:12px;box-shadow:0 12px 28px rgba(0,0,0,.16);padding:4px}
.ep-combo__lista[hidden]{display:none}
.ep-combo__grupo{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:#78716c;padding:8px 10px 4px}
.ep-combo__item{display:block;width:100%;text-align:left;border:0;background:none;padding:9px 10px;border-radius:9px;font-size:15px;font-family:inherit;color:#1c1917;cursor:pointer}
.ep-combo__item:hover,.ep-combo__item--sel{background:#eef2ff}
.ep-combo__item em{font-style:normal;font-weight:800;background:#fef08a}
.ep-combo__vacio{padding:10px;font-size:13.5px;color:#78716c}
.ep-btn{background:#fff;border:1px solid var(--color-borde);border-radius:10px;padding:11px 16px;font-size:15px;font-weight:800;cursor:pointer;color:inherit;text-decoration:none;display:inline-block;font-family:inherit}
.ep-btn--principal{background:#123c6b;color:#fff;border-color:#123c6b}
.ep-btn--ok{background:#15803d;color:#fff;border-color:#15803d}
.ep-btn--suave{background:#f3f4f6}
.ep-btn--pide{background:#f59e0b;color:#fff;border-color:#f59e0b}
.ep-btn--mini{padding:8px 12px;font-size:13.5px}
.ep-btn:disabled{opacity:.6;cursor:progress}
.ep-pill{display:inline-flex;align-items:center;gap:6px;background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;border-radius:999px;padding:4px 10px;font-size:13px;font-weight:800;margin-top:8px}
.ep-pill button{border:0;background:none;color:#15803d;font-weight:800;cursor:pointer;font-size:14px;line-height:1}
.ep-pill[hidden]{display:none}

/* ---- Prompt ---- */
.ep-prompt__eti{display:flex;flex-wrap:wrap;gap:6px;align-items:center;justify-content:space-between;margin-bottom:6px}
.ep-prompt__txt{font-size:15px;font-weight:800}
/* El prompt es INFORMATIVO (se copia con el botón 📋): 3 filas de alto y, si se
   quiere leer o editar completo, se estira con el tirador de abajo a la derecha
   o se lee en el propio cuadro con la barra de desplazamiento. */
.ep-prompt textarea{width:100%;height:92px;min-height:92px;font-size:15px;line-height:1.5;padding:10px 12px;border:1px solid var(--color-borde);border-radius:10px;font-family:inherit;resize:vertical;background:#fffdf8;overflow:auto}
.ep-botones{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
.ep-pag{display:flex;flex-wrap:wrap;gap:8px;align-items:center;justify-content:center;margin:18px 0}
.ep-pag a,.ep-pag span{border:1px solid var(--color-borde);background:#fff;border-radius:10px;padding:10px 14px;font-size:15px;font-weight:700;text-decoration:none;color:inherit}
.ep-pag .off{opacity:.45}
.ep-pag .act{background:var(--color-primario,#6d071a);color:#fff;border-color:var(--color-primaria,#6d071a)}
.ep-vacio{background:#fff;border:1px dashed var(--color-borde);border-radius:12px;padding:26px 16px;text-align:center;color:var(--color-texto-claro);font-size:15px}
.ep-nota{font-size:12.5px;color:var(--color-texto-claro);line-height:1.5;margin-top:6px}
</style>

<div class="ep-wrap">

  <h1 class="ep-h1">🎨 Editor de productos · precio, unidad, imagen y prompt</h1>
  <p class="ep-sub">
    Todos los productos <b>por orden de llegada</b> (los últimos creados primero), <b>20 por página</b> = 1 lote.
    <b>Nada recarga la página</b>: puedes trabajar el producto 22 o 27 sin perder el scroll.
  </p>

  <div class="ep-caja">
    <div class="ep-pasos">
      <div class="ep-paso"><b>1 · La imagen: arrástrala encima</b>Trae la imagen generada y <b>suéltala sobre la foto</b>
        (o cópiala en Gemini y pégala con <b>Ctrl+V</b>). Se publica <b>al instante</b>, sin tocar nada más.</div>
      <div class="ep-paso"><b>2 · Precio y unidad (se guardan solos)</b>Escribe el precio y elige la unidad en el
        select <b>predictivo</b> (escribe "kil" y te encuentra <i>por kilo</i> / <i>por kilómetro</i>).
        <b>Se guardan automáticamente</b> al salir del campo o al elegir la unidad.</div>
      <div class="ep-paso"><b>3 · Guardar (si quieres forzarlo)</b>El botón <b>💾 Guardar</b> de <b>ese producto</b>
        guarda al instante. Solo se pone <b>ámbar</b> si algo quedó sin guardar (por ejemplo, si falló la conexión).</div>
    </div>
    <div class="ep-botones">
      <a class="ep-btn ep-btn--principal" href="https://gemini.google.com/app" target="_blank" rel="noopener">🪄 Abrir generador de imágenes (Gemini)</a>
      <a class="ep-btn" href="<?= url('superadmin.php?seccion=productos') ?>">📦 Ver productos en Súper Admin</a>
    </div>
    <p class="ep-nota">💡 <b>Ctrl+V</b> publica la imagen que tengas copiada en la tarjeta que tengas señalada (pasa el mouse por encima un segundo o haz clic en ella).</p>
  </div>

  <?php if ($nota !== ''): ?>
    <div class="ep-aviso ep-aviso--<?= $nota_tipo === 'error' ? 'error' : 'ok' ?>"><?= e($nota) ?></div>
  <?php endif; ?>

  <?php if (!$tablas_ok): ?>
    <div class="ep-aviso ep-aviso--error">No se pudieron preparar las tablas del editor en la base de datos. Avísale al asistente con este dato.</div>
  <?php endif; ?>

  <div class="ep-contadores">
    <span class="ep-cont">📦 <b data-cont="total"><?= number_format($stats['total']) ?></b> productos</span>
    <span class="ep-cont">🎨 <b data-cont="con_prompt"><?= number_format($stats['con_prompt']) ?></b> con prompt</span>
    <span class="ep-cont">⏳ <b data-cont="sin_prompt"><?= number_format(max(0, $stats['total'] - $stats['con_prompt'])) ?></b> sin prompt</span>
    <span class="ep-cont">🖼️ <b data-cont="con_imagen"><?= number_format($stats['con_imagen']) ?></b> con imagen</span>
    <?php foreach ($lotes_guardados as $n_lote => $n_prods): ?>
      <span class="ep-cont"><?= $n_lote > 0 ? '✅ Lote ' . (int)$n_lote . ' (' . number_format($n_prods) . ')' : '✍️ Tuyos (' . number_format($n_prods) . ')' ?></span>
    <?php endforeach; ?>
  </div>

  <form method="get" class="ep-filtros" id="epFiltros">
    <input type="hidden" name="f" value="<?= e($filtro) ?>">
    <div class="ep-busca">
      <label for="epQ" style="font-size:13px;font-weight:700;display:block;margin-bottom:4px">Buscar producto o tienda (filtra la lista al instante)</label>
      <input type="text" id="epQ" name="q" value="<?= e($q) ?>" placeholder="Ej: pollo, collar, Toyota…" autocomplete="off">
    </div>
    <button class="ep-btn ep-btn--principal" type="submit" style="margin-top:20px">🔎 Buscar en todo el catálogo</button>
  </form>

  <div class="ep-filtros">
    <?php
      $chips = ['todos' => '🗂️ Todos', 'pendientes' => '⏳ Sin prompt', 'sinfoto' => '⬜ Sin imagen', 'confoto' => '🖼️ Con imagen'];
      foreach ($chips as $clave => $etiqueta):
        $u = 'editaproductos.php?f=' . $clave . '&p=1' . ($q !== '' ? '&q=' . urlencode($q) : '');
    ?>
      <a class="ep-chip <?= $filtro === $clave ? 'ep-chip--on' : '' ?>" href="<?= e($u) ?>"><?= $etiqueta ?></a>
    <?php endforeach; ?>
    <span class="ep-cont" style="margin-left:auto"><?= number_format($total) ?> resultado(s) · página <?= $pagina ?> de <?= $paginas ?></span>
  </div>

  <?php if ($sync['nuevos'] > 0 || $sync['actualizados'] > 0): ?>
    <p class="ep-nota">🎒 Lotes leídos del servidor: <?= e(implode(' · ', $sync['archivos']) ?: 'ninguno') ?>
      → <?= (int)$sync['nuevos'] ?> prompt(s) nuevos, <?= (int)$sync['actualizados'] ?> corregido(s).</p>
  <?php endif; ?>

  <?php if (!$productos): ?>
    <div class="ep-vacio">No hay productos con este filtro.</div>
  <?php endif; ?>

  <?php foreach ($productos as $p): ?>
    <?php
      $pid        = (int)$p['id'];
      $ruta_foto  = trim((string)$p['imagen']) !== '' ? (string)$p['imagen'] : (string)$p['primera_foto'];
      $tiene_foto = trim($ruta_foto) !== '';
      $prompt_txt = trim((string)($p['prompt'] ?? ''));
      $es_asist   = ($prompt_txt !== '' && ($p['origen'] ?? '') === 'asistente');
      $es_jefe    = ($prompt_txt !== '' && ($p['origen'] ?? '') === 'jefe');
      $mostrado   = $prompt_txt !== '' ? $prompt_txt : ep_prompt_base($p);
      $comparten  = (int)($p['comparten'] ?? 0);
      $precio     = (float)$p['precio'];
      $unidad     = trim((string)($p['unidad'] ?? ''));
      $fid        = 'epPrompt' . $pid;
    ?>
    <div class="ep-card" id="epCard<?= $pid ?>"
         data-producto="<?= $pid ?>"
         data-precio="<?= e(number_format($precio, 2, '.', '')) ?>"
         data-unidad="<?= e($unidad) ?>"
         data-busca="<?= e(mb_strtolower($p['titulo'] . ' ' . (string)$p['negocio'] . ' ' . (string)$p['rubro'] . ' ' . (string)$p['distrito'] . ' ' . $pid)) ?>">
      <div class="ep-card__head">
        <span class="ep-card__id">#<?= $pid ?></span>
        <h2 class="ep-card__titulo"><?= e($p['titulo']) ?></h2>
      </div>
      <div class="ep-chips">
        <span class="ep-chipmini">🏪 <?= e((string)$p['negocio']) ?></span>
        <span class="ep-chipmini ep-chipmini--rubro">🏷️ Rubro: <?= e((string)($p['rubro'] ?: 'sin rubro')) ?></span>
        <span class="ep-chipmini">📍 <?= e((string)($p['distrito'] ?: 'Chimbote')) ?></span>
        <span class="ep-chipmini" data-vista-precio><?= e(formato_precio($precio) . ($unidad !== '' ? ' ' . $unidad : '')) ?></span>
        <?php if ($tiene_foto): ?>
          <span class="ep-chipmini ep-chipmini--ok" data-vista-foto>🖼️ Con imagen</span>
        <?php else: ?>
          <span class="ep-chipmini ep-chipmini--falta" data-vista-foto>⬜ Sin imagen</span>
        <?php endif; ?>
        <?php if ($es_jefe): ?>
          <span class="ep-chipmini" data-vista-prompt>✍️ Tu prompt</span>
        <?php elseif ($es_asist): ?>
          <span class="ep-chipmini" data-vista-prompt>✨ Prompt del asistente<?= (int)($p['lote'] ?? 0) > 0 ? ' · lote ' . (int)$p['lote'] : '' ?></span>
        <?php else: ?>
          <span class="ep-chipmini ep-chipmini--falta" data-vista-prompt>🧩 Prompt base (el asistente lo mejorará)</span>
        <?php endif; ?>
      </div>

      <div class="ep-cuerpo">
        <!-- ===== IZQUIERDA: la imagen (zona para arrastrar y soltar) ===== -->
        <div class="ep-foto">
          <div class="ep-drop" data-producto="<?= $pid ?>">
            <div class="ep-foto__marco" data-marco>
              <?php if ($tiene_foto): ?>
                <?= img_tag($ruta_foto, (string)$p['titulo'], [
                      'sizes' => '330px',
                      'zoom'  => true,
                      'extra' => ['id' => 'epImg' . $pid, 'data-ruta' => $ruta_foto],
                    ]) ?>
              <?php else: ?>
                <div class="ep-foto__vacia" data-vacio>⬜<br>Sin imagen todavía.<br>Arrastra una aquí encima.</div>
              <?php endif; ?>
              <div class="ep-drop__velo"><b>Suelta la imagen aquí</b><span>se publica al instante (sin recargar)</span></div>
              <div class="ep-drop__sello" data-sello hidden>🆕 publicando…</div>
            </div>
            <div class="ep-progreso" data-progreso><i></i></div>
          </div>
          <p class="ep-foto__ayuda">
            <b>Arrastra una imagen y suéltala encima</b> (o <b>Ctrl+V</b> si la copiaste en Gemini · o
            <label for="epFile<?= $pid ?>" style="cursor:pointer;text-decoration:underline">elige el archivo</label>).
          </p>
          <div class="ep-estado" data-estado hidden></div>
          <?php if (trim((string)($p['ruta_anterior'] ?? '')) !== ''): ?>
            <button type="button" class="ep-btn ep-btn--suave ep-btn--mini" data-deshacer="<?= $pid ?>"
                    style="margin-top:8px">↩️ Deshacer el último cambio de imagen</button>
          <?php endif; ?>
          <p class="ep-foto__pie"><?= e($ruta_foto !== '' ? $ruta_foto : 'sin archivo') ?></p>

          <!-- Formulario clásico: respaldo si algún día fallara el guardado silencioso -->
          <form method="post" enctype="multipart/form-data" class="ep-form-foto">
            <?= csrf_campo() ?>
            <input type="hidden" name="accion" value="cambiar_imagen">
            <input type="hidden" name="producto_id" value="<?= $pid ?>">
            <input type="hidden" name="volver_p" value="<?= $pagina ?>">
            <input type="hidden" name="volver_f" value="<?= e($filtro) ?>">
            <input type="hidden" name="volver_q" value="<?= e($q) ?>">
            <input type="file" id="epFile<?= $pid ?>" name="foto" accept="image/*" hidden>
            <?php if ($comparten > 1): ?>
              <label class="ep-chk" style="display:flex;gap:8px;align-items:flex-start;margin-top:8px;font-size:12.5px;line-height:1.35;color:#374151;cursor:pointer">
                <input type="checkbox" name="aplicar_todos" value="1" style="margin-top:2px;width:16px;height:16px">
                <span>Esta imagen la comparten <b><?= $comparten ?> productos</b> de la misma tienda: márcalo para
                cambiarla también en los otros <?= $comparten - 1 ?> al soltar la nueva.</span>
              </label>
            <?php endif; ?>
          </form>
        </div>

        <!-- ===== DERECHA: precio · unidad · prompt ===== -->
        <div>
          <div class="ep-datos">
            <label class="ep-campo">
              <span class="ep-campo__eti">Precio (0 = a consultar)</span>
              <span class="ep-precio-caja">
                <span>S/</span>
                <input type="number" step="0.01" min="0" inputmode="decimal"
                       id="epPrecio<?= $pid ?>" value="<?= e(number_format($precio, 2, '.', '')) ?>">
              </span>
            </label>
            <div class="ep-campo" style="flex:1 1 240px">
              <span class="ep-campo__eti">Unidad (escribe y te predice)</span>
              <div class="ep-combo" data-campo="unidad">
                <input type="text" id="epUnidad<?= $pid ?>" value="<?= e($unidad) ?>"
                       autocomplete="off" spellcheck="false" placeholder="por unidad, por kilo, por mes…">
                <span class="ep-combo__flecha">▾</span>
                <div class="ep-combo__lista" hidden></div>
              </div>
            </div>
            <button type="button" class="ep-btn ep-btn--principal" data-guardar="<?= $pid ?>">💾 Guardar</button>
            <span class="ep-nota" style="flex:1 1 100%">
              ⚡ El <b>precio</b> y la <b>unidad</b> se <b>guardan solos</b> al salir del campo o al elegir la unidad
              (el botón 💾 sirve para forzarlo). Al soltar una imagen también se publica sola.
            </span>
          </div>
          <div class="ep-pill" data-pill hidden></div>

          <div class="ep-prompt" style="margin-top:12px">
            <div class="ep-prompt__eti">
              <span class="ep-prompt__txt">🎨 Prompt para generar la imagen</span>
              <span class="ep-chipmini"><?= $es_jefe ? 'tuyo' : ($es_asist ? 'del asistente' : 'base automático') ?></span>
            </div>
            <form method="post" class="ep-form-prompt">
              <?= csrf_campo() ?>
              <input type="hidden" name="accion" value="guardar_prompt">
              <input type="hidden" name="producto_id" value="<?= $pid ?>">
              <input type="hidden" name="volver_p" value="<?= $pagina ?>">
              <input type="hidden" name="volver_f" value="<?= e($filtro) ?>">
              <input type="hidden" name="volver_q" value="<?= e($q) ?>">
              <textarea id="<?= $fid ?>" name="prompt" rows="3" spellcheck="false"><?= e($mostrado) ?></textarea>
              <div class="ep-botones">
                <button type="button" class="ep-btn ep-btn--ok" data-copiar="<?= $fid ?>">📋 Copiar prompt</button>
                <button type="submit" class="ep-btn ep-btn--principal" data-guardar-prompt="<?= $pid ?>">💾 Guardar prompt</button>
                <?php if ($es_jefe): ?>
                  <button type="submit" class="ep-btn ep-btn--suave" name="accion" value="restaurar_prompt"
                          data-restaurar-prompt="<?= $pid ?>">↩️ Volver al del asistente</button>
                <?php endif; ?>
                <a class="ep-btn" href="<?= url('negocio.php?slug=' . urlencode((string)$p['negocio_slug'])) ?>" target="_blank" rel="noopener">👁️ Ver ficha</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if ($paginas > 1): ?>
    <div class="ep-pag">
      <?php
        $base = 'editaproductos.php?f=' . urlencode($filtro) . ($q !== '' ? '&q=' . urlencode($q) : '') . '&p=';
        $ant  = max(1, $pagina - 1);
        $sig  = min($paginas, $pagina + 1);
      ?>
      <a class="<?= $pagina <= 1 ? 'off' : '' ?>" href="<?= e($base . $ant) ?>">‹ Lote anterior</a>
      <span class="act">Lote <?= $pagina ?> de <?= $paginas ?></span>
      <a class="<?= $pagina >= $paginas ? 'off' : '' ?>" href="<?= e($base . $sig) ?>">Lote siguiente ›</a>
    </div>
    <p class="ep-nota" style="text-align:center">Cada página son 20 productos: es un lote de trabajo.</p>
  <?php endif; ?>

</div>

<script>
/* Opciones del select de unidades (catálogo + las que ya usa el sitio) */
window.EP_UNIDADES = <?= json_encode($opciones_unidad, JSON_UNESCAPED_UNICODE) ?>;
window.EP_URL_ACCION = <?= json_encode('editaproductos.php') ?>;
</script>
<script src="<?= url('assets/js/imagen_optimizar.js') ?>?v=1"></script>
<script src="<?= url('assets/js/editor_productos.js') ?>?v=6"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
