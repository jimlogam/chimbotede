<?php
/**
 * editatiendas.php — EDITOR DE PORTADAS DE TIENDAS (flyer del rubro + prompt de IA)
 * ============================================================================
 * QUÉ ES (pedido del jefe, 2026-09-12):
 *   *"Algo similar necesito pero esta vez con tiendas, procurando meter en el
 *    prompt el nombre de la tienda como texto. Las imágenes de referencia al tipo
 *    de tienda o tipo de rubro: ejemplo, si se llama «Virgen del Carmen» y es una
 *    ferretería, la imagen debe ser una ferretería con productos de ferretería y
 *    solo un texto que diga «virgen del carmen» y un logo. Porque el nombre del
 *    local NO se debe usar como representación del negocio, porque siempre dará
 *    errores: ejemplo peluquería «Mi Corazón» — no queremos una portada de partes
 *    del corazón o tipos de corazones, queremos un flyer DE PELUQUERÍA pero con el
 *    texto artístico «MI CORAZÓN» y elementos de peluquería. La descripción de la
 *    tienda también te ayudará a armar el prompt."*
 *
 *   ⇒ Las 2 reglas de oro de estos prompts:
 *      1) EL RUBRO MANDA: la imagen es un **flyer publicitario del rubro** (con
 *         sus productos y ambiente), NUNCA una ilustración del nombre.
 *      2) EL NOMBRE ES TEXTO: va como **único texto**, en letras grandes y
 *         artísticas (lettering), más un **logo/emblema simple del rubro**.
 *
 * MECÁNICA (idéntica al editor de productos, para reaprovechar su JS):
 *   · Tiendas por **orden de llegada** (`n.id DESC`), **20 por página = 1 lote**.
 *   · La portada de una tienda es **la 1.ª foto de `directorio_fotos`** (la que
 *     usa el buscador, la portada y el hero de la ficha: `$fotos[0]`).
 *   · **Arrastrar y soltar** una imagen encima de la portada la publica al
 *     instante, **sin recargar la página** (+ Ctrl+V), con **↩️ Deshacer**.
 *   · El **prompt** (lo escribe el asistente) se guarda y **se copia con un botón**;
 *     lo que el jefe edita queda `origen='jefe'` y ningún lote lo pisa.
 *     🔇 **OJO (pedido del jefe, 2026-09-12):** el prompt **YA NO SE MUESTRA**: vive
 *     **oculto** dentro del formulario (`.ep-prompt__oculto`) solo para que el botón
 *     **📋 Copiar prompt** lo copie. En su lugar se ve la **DESCRIPCIÓN de la tienda**
 *     (**primeras 50 palabras**, `.ep-desc`), **solo para leer: no se edita**.
 *   · Los prompts viven en `directorio_negocio_prompts` y se cargan solos desde
 *     `cache/prompts_tiendas/lote_NN.json`.
 *   · **Acciones por tienda (pedido del jefe, 2026-09-12):** **✏️ Editar** (abre la
 *     gestión de esa tienda, `productos.php?n=<ID>`, en pestaña nueva), **👁️ Ver ficha**
 *     y **🗑️ Eliminar tienda** (`accion=eliminar_negocio`: borra la tienda con sus
 *     productos, fotos, opiniones, rubros, prompts, avisos de empleo y **sus archivos**;
 *     pide confirmación y no se puede deshacer).
 *
 * Se apoya en `assets/js/editor_productos.js` (mismo contrato de HTML: `.ep-card`,
 * `.ep-drop`, `.ep-form-foto`, `.ep-form-prompt`, `[data-estado]`, contadores…).
 */

require_once __DIR__ . '/config.php';

requiere_login();
if (!es_admin()) { http_response_code(403); die('Acceso denegado. Requiere ser administrador.'); }

$pdo   = db();
$admin = usuario_actual();

$TABLA      = 'directorio_negocio_prompts';
$TABLA_ANT  = 'directorio_negocio_imagenes_ant';
$POR_PAGINA = 20;
$DIR_LOTES  = __DIR__ . '/cache/prompts_tiendas';
$UNDO_HORAS = 24;

/* ============================================================================
 * 1) TABLAS (se crean solas al entrar un admin)
 * ========================================================================== */
function et_asegurar_tablas(PDO $pdo) {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS directorio_negocio_prompts (
            negocio_id INT(10) UNSIGNED NOT NULL,
            prompt TEXT NOT NULL,
            origen ENUM('asistente','jefe') NOT NULL DEFAULT 'asistente',
            lote SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
            rubro VARCHAR(120) NULL,
            negocio VARCHAR(180) NULL,
            creado_en DATETIME NOT NULL,
            actualizado_en DATETIME NULL,
            PRIMARY KEY (negocio_id),
            KEY idx_origen (origen),
            KEY idx_lote (lote)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        $pdo->exec("CREATE TABLE IF NOT EXISTS directorio_negocio_imagenes_ant (
            negocio_id INT(10) UNSIGNED NOT NULL,
            ruta VARCHAR(255) NOT NULL,
            ruta_nueva VARCHAR(255) NULL,
            creado_en DATETIME NOT NULL,
            PRIMARY KEY (negocio_id),
            KEY idx_creado (creado_en)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // 🏷️ RUBROS MÚLTIPLES (pedido del jefe, 2026-09-12): una tienda puede
        // vivir en hasta 4 rubros. `directorio_negocios.categoria_id` es el
        // PRINCIPAL (slot 1) y los otros viven aquí (slots 2, 3 y 4).
        $pdo->exec("CREATE TABLE IF NOT EXISTS directorio_negocio_rubros (
            negocio_id INT(10) UNSIGNED NOT NULL,
            categoria_id INT(10) UNSIGNED NOT NULL,
            orden TINYINT(3) UNSIGNED NOT NULL DEFAULT 0,
            creado_en DATETIME NOT NULL,
            PRIMARY KEY (negocio_id, categoria_id),
            KEY idx_cat (categoria_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $ok = true;
    } catch (Throwable $e) {
        error_log('editatiendas: no se pudieron crear las tablas: ' . $e->getMessage());
        $ok = false;
    }
    return $ok;
}

/* ============================================================================
 * 2) PROMPT BASE AUTOMÁTICO (para las tiendas que aún no tienen el del asistente)
 *    Mismas 2 reglas: flyer del rubro + el nombre SOLO como texto artístico.
 * ========================================================================== */
function et_prompt_base(array $t) {
    $nombre  = trim((string)($t['nombre'] ?? ''));
    $rubro   = trim((string)($t['rubro'] ?? '')) ?: 'negocio';
    $sub     = trim((string)($t['subrubro'] ?? ''));
    $distrito = trim((string)($t['distrito'] ?? '')) ?: 'Chimbote';

    return 'Portada publicitaria (flyer) para el negocio «' . $nombre . '» — es ' . $rubro
        . ($sub !== '' ? ' (' . $sub . ')' : '') . ' en ' . $distrito . ', Perú.' . "\n\n"
        . 'Diseño: flyer publicitario moderno y llamativo de ' . $rubro
        . ', con sus productos, herramientas y ambiente típicos en primer plano.' . "\n\n"
        . '⚠️ NO ilustres el nombre del negocio: la imagen representa el RUBRO, no lo que dice el nombre.' . "\n\n"
        . 'Texto: ÚNICAMENTE el nombre «' . $nombre . '» en letras grandes y artísticas '
        . '(lettering) bien legibles, más un logo o emblema simple del rubro. Ningún otro texto: nada en inglés ni en otro idioma.' . "\n\n"
        // Google Flow (la IA de imágenes) TRADUCE los textos: hay que repetirlo.
        . '🚫 IDIOMA OBLIGATORIO: TODO el texto de la imagen va SIEMPRE EN ESPAÑOL y EXACTAMENTE '
        . 'como te lo escribo aquí, letra por letra. NO traduzcas ni cambies de idioma: el nombre se escribe «'
        . $nombre . '» y así debe quedar en la imagen (por ejemplo: «Hola Perú» se escribe «Hola Perú», '
        . 'NUNCA «Hello Peru»).' . "\n\n"
        . 'Composición centrada (que ningún recorte corte el nombre). Formato cuadrado 1:1, alta resolución, '
        . 'colores vivos y profesionales. Sin marcas registradas, sin texto adicional, sin marcas de agua y sin rostros identificables. '
        . '🔁 RECUERDA: el texto va en ESPAÑOL y tal cual está escrito («' . $nombre . '»), sin traducir, y sin ningún otro texto.';
}

/* ============================================================================
 * 2bis) LA DESCRIPCIÓN, PARA VER (pedido del jefe, 2026-09-12)
 *    «en lugar del cuadro de prompt muéstrame las primeras 50 palabras de la
 *     descripción … (solo ver no editar)»
 *    Devuelve las PRIMERAS 50 PALABRAS de la descripción de la tienda, en un
 *    solo párrafo. Si la descripción es más larga, termina en «…». No se
 *    guarda nada: es solo para leerla mientras se arma la portada.
 *    ⚠️ Muchas descripciones del directorio traen **HTML** (`<p>`, `<strong>`,
 *    `<h3>`…): primero se pasa a TEXTO PLANO (los cierres de bloque se cambian
 *    por un espacio para que no se peguen las palabras) y se decodifican las
 *    entidades (`&aacute;` → á). Así se lee limpio y las 50 palabras son de
 *    verdad palabras.
 * ========================================================================== */
function et_primeras_palabras($texto, $max = 50) {
    $texto = (string)$texto;
    // 1) Los cierres de bloque y los <br> valen como un espacio.
    $texto = preg_replace('#<(br|/p|/div|/li|/h[1-6]|/tr|/td)\s*/?>#i', ' ', $texto);
    // 2) Fuera el resto de etiquetas (y los comentarios) y las entidades HTML.
    $texto = strip_tags($texto);
    $texto = html_entity_decode($texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    // 3) Espacios de sobra fuera (incluidos los saltos de línea y los &nbsp;).
    $texto = trim(preg_replace('/\s+/u', ' ', $texto));
    if ($texto === '') return '';
    $palabras = preg_split('/ /u', $texto, -1, PREG_SPLIT_NO_EMPTY) ?: [];
    if (count($palabras) <= $max) return $texto;
    return implode(' ', array_slice($palabras, 0, $max)) . '…';
}

/* ============================================================================
 * 3) CARGA DE LOTES: cache/prompts_tiendas/lote_NN.json
 * ========================================================================== */
function et_sincronizar_lotes(PDO $pdo, string $dir) {
    $res = ['archivos' => [], 'nuevos' => 0, 'actualizados' => 0, 'leidos' => 0];
    if (!is_dir($dir)) return $res;
    $archivos = glob($dir . '/lote_*.json') ?: [];
    sort($archivos, SORT_STRING);
    if (!$archivos) return $res;

    $actuales = [];
    try {
        foreach ($pdo->query("SELECT negocio_id, lote, origen, prompt FROM directorio_negocio_prompts") as $f) {
            $actuales[(int)$f['negocio_id']] = [
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
            $nid = (int)($it['negocio_id'] ?? 0);
            $txt = trim((string)($it['prompt'] ?? ''));
            if ($nid <= 0 || $txt === '') continue;
            $res['leidos']++;
            $rubro = mb_substr(trim((string)($it['rubro'] ?? '')), 0, 120);
            $neg   = mb_substr(trim((string)($it['negocio'] ?? '')), 0, 180);
            $act   = $actuales[$nid] ?? null;

            if ($act === null) {
                $pdo->prepare("INSERT INTO directorio_negocio_prompts
                        (negocio_id, prompt, origen, lote, rubro, negocio, creado_en)
                        VALUES (?, ?, 'asistente', ?, ?, ?, NOW())")
                    ->execute([$nid, $txt, $lote, ($rubro !== '' ? $rubro : null), ($neg !== '' ? $neg : null)]);
                $actuales[$nid] = ['lote' => $lote, 'origen' => 'asistente', 'prompt' => $txt];
                $res['nuevos']++;
            } elseif ($act['origen'] === 'asistente' && $act['lote'] <= $lote && $act['prompt'] !== $txt) {
                $pdo->prepare("UPDATE directorio_negocio_prompts
                                  SET prompt = ?, lote = ?, rubro = ?, negocio = ?, actualizado_en = NOW()
                                WHERE negocio_id = ?")
                    ->execute([$txt, $lote, ($rubro !== '' ? $rubro : null), ($neg !== '' ? $neg : null), $nid]);
                $actuales[$nid]['prompt'] = $txt;
                $actuales[$nid]['lote']   = $lote;
                $res['actualizados']++;
            }
        }
    }
    return $res;
}

/* ============================================================================
 * 4) LA PORTADA DE UNA TIENDA (1.ª foto de directorio_fotos) + deshacer
 * ========================================================================== */
function et_referencias(PDO $pdo, string $ruta, int $excluir = 0) {
    global $TABLA_ANT;
    $ruta = trim($ruta);
    if ($ruta === '') return 0;
    $n = 0;
    $consultas = [
        ["SELECT COUNT(*) FROM directorio_fotos WHERE ruta = ? AND negocio_id <> ?", [$ruta, $excluir]],
        ["SELECT COUNT(*) FROM {$TABLA_ANT} WHERE ruta = ? AND negocio_id <> ?", [$ruta, $excluir]],
        ["SELECT COUNT(*) FROM directorio_servicios WHERE imagen = ?", [$ruta]],
        ["SELECT COUNT(*) FROM directorio_producto_fotos WHERE ruta = ?", [$ruta]],
        ["SELECT COUNT(*) FROM directorio_banners WHERE imagen = ?", [$ruta]],
    ];
    foreach ($consultas as $c) {
        try { $s = $pdo->prepare($c[0]); $s->execute($c[1]); $n += (int)$s->fetchColumn(); }
        catch (Throwable $e) { /* tabla o columna inexistente: no se cuenta */ }
    }
    return $n;
}

function et_guardar_undo(PDO $pdo, int $nid, string $vieja, string $nueva) {
    global $TABLA_ANT;
    if ($vieja === '' || $vieja === $nueva) return;
    $previa = '';
    try {
        $s = $pdo->prepare("SELECT ruta FROM {$TABLA_ANT} WHERE negocio_id = ?");
        $s->execute([$nid]);
        $previa = (string)$s->fetchColumn();
    } catch (Throwable $e) { return; }

    $pdo->prepare("INSERT INTO {$TABLA_ANT} (negocio_id, ruta, ruta_nueva, creado_en)
                   VALUES (?, ?, ?, NOW())
                   ON DUPLICATE KEY UPDATE ruta = VALUES(ruta), ruta_nueva = VALUES(ruta_nueva), creado_en = NOW()")
        ->execute([$nid, $vieja, $nueva]);

    if ($previa !== '' && $previa !== $vieja && et_referencias($pdo, $previa) === 0) {
        img_borrar($previa);
    }
}

function et_limpiar_antiguas(PDO $pdo, int $horas = 24) {
    global $TABLA_ANT;
    $borradas = 0;
    try {
        $s = $pdo->prepare("SELECT negocio_id, ruta FROM {$TABLA_ANT}
                             WHERE creado_en < DATE_SUB(NOW(), INTERVAL ? HOUR) LIMIT 40");
        $s->bindValue(1, $horas, PDO::PARAM_INT);
        $s->execute();
        foreach ($s->fetchAll() as $f) {
            $pdo->prepare("DELETE FROM {$TABLA_ANT} WHERE negocio_id = ?")->execute([(int)$f['negocio_id']]);
            if (et_referencias($pdo, (string)$f['ruta']) === 0) { img_borrar((string)$f['ruta']); $borradas++; }
        }
    } catch (Throwable $e) { /* la página funciona igual */ }
    return $borradas;
}

/** Publicar (o cambiar) la PORTADA de una tienda = 1.ª foto de su galería. */
function et_publicar_portada(PDO $pdo, int $nid, $archivo) {
    $s = $pdo->prepare("SELECT n.id, n.nombre, n.slug FROM directorio_negocios n WHERE n.id = ? LIMIT 1");
    $s->execute([$nid]);
    $neg = $s->fetch();
    if (!$neg) return ['ok' => false, 'error' => 'Esa tienda ya no existe.'];
    if (empty($archivo['name'])) return ['ok' => false, 'error' => 'No llegó ninguna imagen.'];

    $slug = preg_replace('/[^a-z0-9_\-]/i', '', (string)($neg['slug'] ?? ''));
    $carpeta = 'fotos/' . ($slug !== '' ? $slug : 'neg' . $nid);
    $nombre  = 'portada_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));

    $res = img_guardar_subida($archivo, $carpeta, $nombre);
    if (empty($res['ok'])) {
        return ['ok' => false, 'error' => 'No se pudo guardar la imagen: ' . ($res['error'] ?? 'error desconocido')];
    }
    $nueva = (string)$res['rel'];

    $g = $pdo->prepare("SELECT id, ruta FROM directorio_fotos WHERE negocio_id = ?
                         ORDER BY orden ASC, id ASC LIMIT 1");
    $g->execute([$nid]);
    $fila = $g->fetch();
    $vieja = $fila ? (string)$fila['ruta'] : '';

    if ($fila) {
        $pdo->prepare("UPDATE directorio_fotos SET ruta = ? WHERE id = ?")->execute([$nueva, (int)$fila['id']]);
        if ($vieja !== '' && $vieja !== $nueva) et_guardar_undo($pdo, $nid, $vieja, $nueva);
    } else {
        $pdo->prepare("INSERT INTO directorio_fotos (negocio_id, ruta, orden) VALUES (?, ?, 0)")
            ->execute([$nid, $nueva]);
    }

    $ancho = (int)($res['ancho'] ?? 0);
    $alto  = (int)($res['alto'] ?? 0);
    $peso  = (int)($res['peso'] ?? 0);
    $completa = img_buscar_variante($nueva, IMG_ANCHO_COMPLETO) ?: $nueva;

    return [
        'ok'        => true,
        'producto_id' => $nid,           // (el JS usa este campo para identificar la tarjeta)
        'titulo'    => (string)$neg['nombre'],
        'ruta'      => $nueva,
        'anterior'  => $vieja,
        'peso_kb'   => (int)round($peso / 1024),
        'ancho'     => $ancho,
        'alto'      => $alto,
        'url'       => img_url($nueva),
        'srcset'    => img_srcset($nueva),
        'url_full'  => url_imagen($completa),
        'aviso'     => ($vieja === '' ? '🖼️ Portada publicada' : '🔄 Portada cambiada')
                     . ' para «' . (string)$neg['nombre'] . '» · ' . (int)round($peso / 1024) . ' KB en WebP'
                     . ($ancho > 0 ? ' (' . $ancho . '×' . $alto . ' px)' : ''),
    ];
}

/* ============================================================================
 * 5) ARRANQUE
 * ========================================================================== */
$tablas_ok = et_asegurar_tablas($pdo);
$sync = $tablas_ok ? et_sincronizar_lotes($pdo, $DIR_LOTES)
                   : ['archivos' => [], 'nuevos' => 0, 'actualizados' => 0, 'leidos' => 0];
if ($tablas_ok) et_limpiar_antiguas($pdo, $UNDO_HORAS);

/* ============================================================================
 * 6) ACCIONES (POST) — JSON si vienen del editor (ajax=1)
 * ========================================================================== */
$nota = '';
$nota_tipo = 'ok';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tablas_ok) {
    csrf_verificar();
    $accion  = (string)($_POST['accion'] ?? '');
    $nid     = (int)($_POST['producto_id'] ?? 0);   // el JS manda producto_id: aquí es el negocio
    $es_ajax = (($_POST['ajax'] ?? '') === '1')
            || (strtolower((string)($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')) === 'xmlhttprequest');

    $volver = 'editatiendas.php?p=' . max(1, (int)($_POST['volver_p'] ?? 1));
    if (trim((string)($_POST['volver_f'] ?? '')) !== '') $volver .= '&f=' . urlencode((string)$_POST['volver_f']);
    if (trim((string)($_POST['volver_q'] ?? '')) !== '') $volver .= '&q=' . urlencode((string)$_POST['volver_q']);

    $json = ['ok' => false, 'mensaje' => ''];

    /* ---- Guardar el prompt (editado por el jefe) ---- */
    if ($accion === 'guardar_prompt' && $nid > 0) {
        $texto = trim((string)($_POST['prompt'] ?? ''));
        $s = $pdo->prepare("SELECT n.nombre, c.nombre AS rubro FROM directorio_negocios n
                             LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                            WHERE n.id = ? LIMIT 1");
        $s->execute([$nid]);
        $ctx = $s->fetch();
        if (!$ctx) {
            $json['mensaje'] = 'Esa tienda ya no existe.';
        } elseif ($texto === '') {
            $json['mensaje'] = 'El prompt está vacío: escribe algo antes de guardar.';
        } else {
            $pdo->prepare("INSERT INTO directorio_negocio_prompts
                    (negocio_id, prompt, origen, lote, rubro, negocio, creado_en, actualizado_en)
                    VALUES (?, ?, 'jefe', 0, ?, ?, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE prompt = VALUES(prompt), origen = 'jefe', actualizado_en = NOW()")
                ->execute([
                    $nid, $texto,
                    mb_substr((string)($ctx['rubro'] ?? ''), 0, 120) ?: null,
                    mb_substr((string)($ctx['nombre'] ?? ''), 0, 180) ?: null,
                ]);
            $json = ['ok' => true, 'producto_id' => $nid, 'origen' => 'jefe',
                     'mensaje' => '💾 Prompt guardado: ya es tuyo, ningún lote lo va a pisar.'];
        }
    }

    /* ---- Volver al prompt del asistente ---- */
    if ($accion === 'restaurar_prompt' && $nid > 0) {
        $pdo->prepare("UPDATE directorio_negocio_prompts SET origen = 'asistente', actualizado_en = NOW()
                        WHERE negocio_id = ?")->execute([$nid]);
        et_sincronizar_lotes($pdo, $DIR_LOTES);
        $s = $pdo->prepare("SELECT prompt, origen FROM directorio_negocio_prompts WHERE negocio_id = ?");
        $s->execute([$nid]);
        $fila = $s->fetch();
        $json = ['ok' => true, 'producto_id' => $nid, 'origen' => $fila ? (string)$fila['origen'] : 'asistente',
                 'prompt' => $fila ? (string)$fila['prompt'] : '',
                 'mensaje' => '↩️ Prompt devuelto al del asistente.'];
    }

    /* ---- 🏷️ Guardar los RUBROS (hasta 4, en vivo) ---- */
    if ($accion === 'guardar_rubros' && $nid > 0) {
        $nombres = [];
        for ($i = 1; $i <= 4; $i++) $nombres[$i] = trim((string)($_POST['rubro' . $i] ?? ''));

        $ids = [];            // slot => categoria_id
        $no_encontrados = [];
        foreach ($nombres as $slot => $nombre) {
            if ($nombre === '') continue;
            $res_id = $pdo->prepare("SELECT id FROM directorio_categorias WHERE nombre = ? AND activo = 1 LIMIT 1");
            $res_id->execute([$nombre]);
            $id = (int)$res_id->fetchColumn();
            if ($id > 0) $ids[$slot] = $id;
            else $no_encontrados[] = $nombre;
        }
        // Sin repetidos: el rubro repetido se queda en el primer bloque donde aparece.
        $vistos = [];
        foreach ($ids as $slot => $id) {
            if (isset($vistos[$id])) unset($ids[$slot]);
            else $vistos[$id] = $slot;
        }

        if (empty($ids[1])) {
            $json['mensaje'] = 'El bloque 1 es el RUBRO PRINCIPAL: escribe uno que exista en el directorio.';
        } else {
            $pdo->prepare("UPDATE directorio_negocios SET categoria_id = ? WHERE id = ?")
                ->execute([$ids[1], $nid]);

            $pdo->prepare("DELETE FROM directorio_negocio_rubros WHERE negocio_id = ?")->execute([$nid]);
            $orden = 0;
            foreach ([2, 3, 4] as $slot) {
                if (empty($ids[$slot])) continue;
                $pdo->prepare("INSERT INTO directorio_negocio_rubros (negocio_id, categoria_id, orden, creado_en)
                               VALUES (?, ?, ?, NOW())")
                    ->execute([$nid, $ids[$slot], $orden++]);
            }

            // El buscador fuzzy guarda su lista en caché: se invalida para que el
            // rubro nuevo se encuentre al instante.
            @unlink(__DIR__ . '/cache/negocios.json');

            $rubros = rubros_de_negocio($nid);
            $txt = implode(' · ', array_map(function ($r) { return $r['nombre']; }, $rubros));
            $extra_msg = $no_encontrados ? ' (no encontré: ' . implode(', ', $no_encontrados) . ')' : '';
            $json = [
                'ok' => true, 'producto_id' => $nid,
                'rubros' => $rubros, 'rubros_txt' => $txt,
                'mensaje' => '🏷️ Rubros guardados: ' . $txt . $extra_msg,
            ];
        }
    }

    /* ---- Publicar / cambiar la PORTADA ---- */
    if ($accion === 'cambiar_imagen' && $nid > 0) {
        $r = et_publicar_portada($pdo, $nid, $_FILES['foto'] ?? null);
        if (empty($r['ok'])) {
            $json['mensaje'] = (string)($r['error'] ?? 'No se pudo publicar la portada.');
            $nota = (string)$r['error']; $nota_tipo = 'error';
        } else {
            $json = $r;
            $json['mensaje'] = (string)$r['aviso'];
            $nota = (string)$r['aviso'];
        }
    }

    /* ---- Deshacer el último cambio de portada ---- */
    if ($accion === 'deshacer_imagen' && $nid > 0) {
        global $TABLA_ANT;
        $s = $pdo->prepare("SELECT ruta, ruta_nueva FROM {$TABLA_ANT} WHERE negocio_id = ?");
        $s->execute([$nid]);
        $ant = $s->fetch();
        if (!$ant || trim((string)$ant['ruta']) === '') {
            $json['mensaje'] = 'Ya no hay nada que deshacer en esta tienda.';
        } else {
            $ruta_ant = (string)$ant['ruta'];
            $ruta_act = (string)$ant['ruta_nueva'];
            $pdo->prepare("DELETE FROM {$TABLA_ANT} WHERE negocio_id = ?")->execute([$nid]);

            $g = $pdo->prepare("SELECT id FROM directorio_fotos WHERE negocio_id = ?
                                 ORDER BY orden ASC, id ASC LIMIT 1");
            $g->execute([$nid]);
            $foto_id = (int)$g->fetchColumn();
            if ($foto_id > 0) {
                $pdo->prepare("UPDATE directorio_fotos SET ruta = ? WHERE id = ?")->execute([$ruta_ant, $foto_id]);
            } else {
                $pdo->prepare("INSERT INTO directorio_fotos (negocio_id, ruta, orden) VALUES (?, ?, 0)")
                    ->execute([$nid, $ruta_ant]);
            }
            if ($ruta_act !== '' && $ruta_act !== $ruta_ant && et_referencias($pdo, $ruta_act, $nid) === 0) {
                img_borrar($ruta_act);
            }
            $completa = img_buscar_variante($ruta_ant, IMG_ANCHO_COMPLETO) ?: $ruta_ant;
            $json = [
                'ok' => true, 'producto_id' => $nid,
                'ruta' => $ruta_ant, 'url' => img_url($ruta_ant), 'srcset' => img_srcset($ruta_ant),
                'url_full' => url_imagen($completa),
                'mensaje' => '↩️ Portada anterior restaurada.',
            ];
        }
    }

    /* ---- 🗑️ ELIMINAR LA TIENDA (pedido del jefe, 2026-09-12) ------------------
     * Hace lo mismo que el 🗑 del Súper Admin (`eliminar_negocio_completo()`), más
     * lo que nació con este editor (rubros extra, prompts y el «↩️ Deshacer» de la
     * portada) y los avisos de empleo colgados de la ficha.
     * ⚠️ Es IRREVERSIBLE: el formulario de la tarjeta pide confirmación antes de
     * enviarlo. Además borra los ARCHIVOS de las imágenes (WebP + versiones de 300
     * y 800 px) para no dejar fotos huérfanas ocupando el hosting.               */
    if ($accion === 'eliminar_negocio' && $nid > 0) {
        $s = $pdo->prepare("SELECT nombre FROM directorio_negocios WHERE id = ? LIMIT 1");
        $s->execute([$nid]);
        $nombre_del = trim((string)($s->fetchColumn() ?: ''));

        if ($nombre_del === '') {
            $json['mensaje'] = 'Esa tienda ya no existe.';
        } else {
            // 1) Las rutas de TODAS sus imágenes, ANTES de borrar las filas.
            $rutas = [];
            $s = $pdo->prepare("SELECT ruta FROM directorio_fotos WHERE negocio_id = ?");
            $s->execute([$nid]);
            foreach ($s->fetchAll() as $f) $rutas[] = (string)$f['ruta'];
            try {
                $s = $pdo->prepare("SELECT pf.ruta FROM directorio_producto_fotos pf
                                      INNER JOIN directorio_servicios sv ON sv.id = pf.producto_id
                                     WHERE sv.negocio_id = ?");
                $s->execute([$nid]);
                foreach ($s->fetchAll() as $f) $rutas[] = (string)$f['ruta'];
            } catch (Throwable $e) {}
            try {
                $s = $pdo->prepare("SELECT ruta FROM {$TABLA_ANT} WHERE negocio_id = ?");
                $s->execute([$nid]);
                foreach ($s->fetchAll() as $f) $rutas[] = (string)$f['ruta'];
            } catch (Throwable $e) {}

            // 2) Las filas: mismo orden y mismo alcance que el Súper Admin.
            $pdo->prepare("DELETE FROM directorio_producto_fotos WHERE producto_id IN
                             (SELECT id FROM directorio_servicios WHERE negocio_id = ?)")->execute([$nid]);
            $pdo->prepare("DELETE FROM directorio_vistas WHERE producto_id IN
                             (SELECT id FROM directorio_servicios WHERE negocio_id = ?)")->execute([$nid]);
            $pdo->prepare("DELETE FROM directorio_servicios WHERE negocio_id = ?")->execute([$nid]);
            $pdo->prepare("DELETE FROM directorio_fotos WHERE negocio_id = ?")->execute([$nid]);
            $pdo->prepare("DELETE FROM directorio_opiniones WHERE negocio_id = ?")->execute([$nid]);
            $pdo->prepare("DELETE FROM directorio_vistas WHERE negocio_id = ?")->execute([$nid]);
            $pdo->prepare("DELETE FROM directorio_mensajes WHERE negocio_id = ?")->execute([$nid]);
            $pdo->prepare("DELETE FROM directorio_negocio_pagos WHERE negocio_id = ?")->execute([$nid]);
            $pdo->prepare("DELETE FROM directorio_reclamos WHERE negocio_id = ?")->execute([$nid]);
            // 🗑️ 2026-09-13: aquí se limpiaba la mensajería entre tiendas (B2B) del negocio.
            // Ese módulo se retiró del sitio, así que ya no hay nada que limpiar por ese lado.
            // Lo que nació con este editor y los avisos de empleo de la ficha.
            foreach (['directorio_negocio_rubros', $TABLA_ANT, $TABLA, 'directorio_empleos'] as $tabla_dep) {
                try { $pdo->prepare("DELETE FROM {$tabla_dep} WHERE negocio_id = ?")->execute([$nid]); } catch (Throwable $e) {}
            }
            $pdo->prepare("DELETE FROM directorio_negocios WHERE id = ?")->execute([$nid]);
            // La caché del buscador fuzzy tenía esa tienda dentro.
            @unlink(__DIR__ . '/cache/negocios.json');

            // 3) Los archivos, ya sin filas que los usen.
            $archivos_borrados = 0;
            foreach (array_unique(array_filter($rutas, 'strlen')) as $r) {
                try { $archivos_borrados += (int)img_borrar($r); } catch (Throwable $e) {}
            }

            $json = ['ok' => true, 'producto_id' => $nid,
                     'mensaje' => '🗑️ Tienda «' . $nombre_del . '» eliminada con todo lo suyo'
                                  . ($archivos_borrados > 0 ? ' (' . $archivos_borrados . ' archivo(s) de imagen)' : '') . '.'];
            $nota      = (string)$json['mensaje'];
            $nota_tipo = 'ok';
        }
    }

    if ($es_ajax) {
        try {
            $json['contadores'] = [
                'con_prompt' => (int)$pdo->query("SELECT COUNT(*) FROM {$TABLA} pr
                        INNER JOIN directorio_negocios n ON n.id = pr.negocio_id")->fetchColumn(),
                'con_imagen' => (int)$pdo->query("SELECT COUNT(DISTINCT negocio_id) FROM directorio_fotos")->fetchColumn(),
            ];
        } catch (Throwable $e) {}
        json_response($json);
    }

    redirect($volver . ($nota !== '' ? '&aviso=' . urlencode($nota) . '&tipo=' . $nota_tipo : ''));
}

if (isset($_GET['aviso'])) {
    $nota = (string)$_GET['aviso'];
    $nota_tipo = (($_GET['tipo'] ?? 'ok') === 'error') ? 'error' : 'ok';
}

/* ============================================================================
 * 7) LISTADO: tiendas por orden de llegada, 20 por página
 * ========================================================================== */
$filtro = (string)($_GET['f'] ?? 'todos');
if (!in_array($filtro, ['todos', 'pendientes', 'sinfoto', 'confoto'], true)) $filtro = 'todos';
$q      = trim((string)($_GET['q'] ?? ''));
$pagina = max(1, (int)($_GET['p'] ?? 1));

$where = [];
$par   = [];
if ($q !== '') {
    $where[] = "(n.nombre LIKE ? OR c.nombre LIKE ?)";
    $par[] = '%' . $q . '%';
    $par[] = '%' . $q . '%';
}
if ($filtro === 'pendientes') $where[] = "pr.negocio_id IS NULL";
if ($filtro === 'sinfoto')    $where[] = "NOT EXISTS (SELECT 1 FROM directorio_fotos fx WHERE fx.negocio_id = n.id)";
if ($filtro === 'confoto')    $where[] = "EXISTS (SELECT 1 FROM directorio_fotos fx WHERE fx.negocio_id = n.id)";
$wsql = $where ? (' WHERE ' . implode(' AND ', $where)) : '';

$desde = "FROM directorio_negocios n
          LEFT JOIN directorio_categorias c   ON c.id = n.categoria_id
          LEFT JOIN directorio_subcategorias s ON s.id = n.subcategoria_id
          LEFT JOIN directorio_distritos d    ON d.id = n.distrito_id
          LEFT JOIN {$TABLA} pr               ON pr.negocio_id = n.id
          LEFT JOIN {$TABLA_ANT} ua           ON ua.negocio_id = n.id" . $wsql;

$st = $pdo->prepare("SELECT COUNT(*) " . $desde);
$st->execute($par);
$total = (int)$st->fetchColumn();
$paginas = max(1, (int)ceil($total / $POR_PAGINA));
if ($pagina > $paginas) $pagina = $paginas;
$offset = ($pagina - 1) * $POR_PAGINA;

$sql = "SELECT n.id, n.nombre, n.slug, n.estado, n.ubicacion_tipo, n.direccion, n.telefono, n.whatsapp,
               n.descripcion, n.creado_en, c.nombre AS rubro, c.icono AS rubro_icono, s.nombre AS subrubro, d.nombre AS distrito,
               pr.prompt, pr.origen, pr.lote, ua.ruta AS ruta_anterior,
               (SELECT GROUP_CONCAT(rc.nombre ORDER BY rr.orden ASC SEPARATOR '|')
                  FROM directorio_negocio_rubros rr
                  JOIN directorio_categorias rc ON rc.id = rr.categoria_id
                 WHERE rr.negocio_id = n.id) AS rubros_extra,
               (SELECT f.ruta FROM directorio_fotos f WHERE f.negocio_id = n.id
                 ORDER BY f.orden ASC, f.id ASC LIMIT 1) AS portada,
               (SELECT COUNT(*) FROM directorio_fotos f WHERE f.negocio_id = n.id) AS n_fotos,
               (SELECT COUNT(*) FROM directorio_servicios sv WHERE sv.negocio_id = n.id) AS n_productos
        " . $desde . " ORDER BY n.id DESC LIMIT ? OFFSET ?";
$st = $pdo->prepare($sql);
$i = 1;
foreach ($par as $v) { $st->bindValue($i++, $v, PDO::PARAM_STR); }
$st->bindValue($i++, $POR_PAGINA, PDO::PARAM_INT);
$st->bindValue($i, $offset, PDO::PARAM_INT);
$st->execute();
$tiendas = $st->fetchAll();

$stats = ['total' => 0, 'con_prompt' => 0, 'con_imagen' => 0];
try {
    $stats['total']      = (int)$pdo->query("SELECT COUNT(*) FROM directorio_negocios")->fetchColumn();
    $stats['con_imagen'] = (int)$pdo->query("SELECT COUNT(DISTINCT negocio_id) FROM directorio_fotos")->fetchColumn();
    if ($tablas_ok) {
        $stats['con_prompt'] = (int)$pdo->query("SELECT COUNT(*) FROM {$TABLA} pr
            INNER JOIN directorio_negocios n ON n.id = pr.negocio_id")->fetchColumn();
    }
} catch (Throwable $e) {}

$lotes_guardados = [];
if ($tablas_ok) {
    try {
        foreach ($pdo->query("SELECT lote, COUNT(*) n FROM {$TABLA} WHERE origen = 'asistente'
                              GROUP BY lote ORDER BY lote") as $f) {
            $lotes_guardados[(int)$f['lote']] = (int)$f['n'];
        }
    } catch (Throwable $e) {}
}

$titulo_pagina = 'Portadas de tiendas (flyer + prompt IA)';
include __DIR__ . '/includes/header.php';
?>
<style>
/* ===== 🏪 EDITOR DE PORTADAS DE TIENDAS (mismo look que el editor de productos) */
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
.ep-card__head{display:flex;flex-wrap:wrap;gap:6px 10px;align-items:baseline;margin-bottom:4px}
.ep-card__id{font-size:13px;font-weight:800;color:var(--color-texto-claro)}
.ep-card__titulo{font-size:18px;font-weight:800;margin:0;line-height:1.25}
.ep-chips{display:flex;flex-wrap:wrap;gap:6px;margin:6px 0 10px}
.ep-chipmini{font-size:12.5px;font-weight:700;border-radius:999px;padding:3px 10px;background:#f3f4f6;color:#374151;border:1px solid #e5e7eb}
.ep-chipmini--rubro{background:#eef2ff;color:#3730a3;border-color:#c7d2fe}
.ep-chipmini--ok{background:#dcfce7;color:#15803d;border-color:#bbf7d0}
.ep-chipmini--falta{background:#fff7ed;color:#9a3412;border-color:#fed7aa}
.ep-cuerpo{display:grid;gap:16px;grid-template-columns:165px 1fr;align-items:start}
@media(max-width:900px){.ep-cuerpo{grid-template-columns:1fr}}
.ep-foto__marco{position:relative;width:100%;aspect-ratio:1/1;border-radius:12px;overflow:hidden;background:#f9fafb;border:2px dashed #d6d3d1;display:flex;align-items:center;justify-content:center;transition:border-color .12s,background .12s}
.ep-foto__marco img{width:100%;height:100%;object-fit:cover;display:block;background:#fff}
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
.ep-btn{background:#fff;border:1px solid var(--color-borde);border-radius:10px;padding:11px 16px;font-size:15px;font-weight:800;cursor:pointer;color:inherit;text-decoration:none;display:inline-block;font-family:inherit}
.ep-btn--principal{background:#123c6b;color:#fff;border-color:#123c6b}
.ep-btn--ok{background:#15803d;color:#fff;border-color:#15803d}
.ep-btn--suave{background:#f3f4f6}
.ep-btn--mini{padding:8px 12px;font-size:13.5px}
.ep-prompt__eti{display:flex;flex-wrap:wrap;gap:6px;align-items:center;justify-content:space-between;margin-bottom:6px}
.ep-prompt__txt{font-size:15px;font-weight:800}
.ep-prompt__sub{font-weight:600;font-size:12.5px;color:var(--color-texto-claro)}
/* 🔇 El prompt YA NO SE MUESTRA (pedido del jefe, 2026-09-12): queda OCULTO dentro
   del formulario solo para que el botón 📋 Copiar prompt lo copie. Se deja FUERA de
   la pantalla pero enfocable (no `display:none`) para que el copiado de respaldo
   (`ta.select()` + `execCommand`) siga funcionando en navegadores viejos. */
.ep-prompt__oculto{position:absolute;left:-9999px;top:0;width:1px;height:1px;padding:0;border:0;overflow:hidden}
/* 📝 En su lugar se ve la DESCRIPCIÓN de la tienda (primeras 50 palabras): SOLO VER. */
.ep-desc{background:#fffdf8;border:1px solid var(--color-borde);border-radius:10px;padding:10px 12px;
         font-size:15px;line-height:1.5;color:#1c1917;min-height:92px;max-height:150px;overflow:auto;
         white-space:pre-wrap;word-break:break-word}
.ep-desc__vacia{color:var(--color-texto-claro);font-style:italic}
.ep-form-eliminar{margin-top:8px}
.ep-btn--peligro{background:#b91c1c;color:#fff;border-color:#b91c1c}
.ep-botones .ep-nota{align-self:center;max-width:340px}
.ep-botones{display:flex;flex-wrap:wrap;gap:8px;margin-top:8px}
.ep-pag{display:flex;flex-wrap:wrap;gap:8px;align-items:center;justify-content:center;margin:18px 0}
.ep-pag a,.ep-pag span{border:1px solid var(--color-borde);background:#fff;border-radius:10px;padding:10px 14px;font-size:15px;font-weight:700;text-decoration:none;color:inherit}
.ep-pag .off{opacity:.45}
.ep-pag .act{background:var(--color-primario,#6d071a);color:#fff;border-color:var(--color-primario,#6d071a)}
.ep-vacio{background:#fff;border:1px dashed var(--color-borde);border-radius:12px;padding:26px 16px;text-align:center;color:var(--color-texto-claro);font-size:15px}
.ep-nota{font-size:12.5px;color:var(--color-texto-claro);line-height:1.5;margin-top:6px}
.et-regla{background:#eef2ff;border:1px solid #c7d2fe;border-radius:12px;padding:10px 12px;font-size:13.5px;line-height:1.5;color:#312e81}

/* ---- 🏷️ RUBROS (4 bloques con select predictivo) ---- */
.ep-datos{display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;background:#fdfaf5;border:1px solid var(--color-borde);border-radius:12px;padding:10px 12px}
.ep-campo{display:flex;flex-direction:column;gap:6px}
.ep-campo__eti{font-size:12px;font-weight:800;color:var(--color-texto-claro);text-transform:uppercase;letter-spacing:.04em}
/* 🏷️ Los 4 bloques de rubro van SIEMPRE en grilla 2×2 (orden del jefe: "nunca
   apilados en fila, tampoco en móvil"). Aquí había una regla que a menos de
   1000 px los ponía en una sola columna: ELIMINADA. */
.ep-rubros{display:grid;gap:8px;grid-template-columns:1fr 1fr}
.ep-combo{position:relative}
.ep-combo--rubro input{padding-left:12px}
.ep-combo input{width:100%;font-size:15px;padding:10px 30px 10px 12px;border:1px solid var(--color-borde);border-radius:10px;background:#fff;font-family:inherit}
/* 🎨 COLORES POR BLOQUE (pedido del jefe): así se entiende de un vistazo cuál es
   cuál. Bloque 1 = rojo oscuro (el RUBRO PRINCIPAL, alfa) · 2 = verde ·
   3 = naranja claro · 4 = azul oscuro. Van DESPUÉS de `.ep-combo input` para
   que el color gane. */
.ep-combo--rojo input{border:2px solid #6d071a;background:#fdf2f4;color:#6d071a;font-weight:700}
.ep-combo--verde input{border:2px solid #15803d;background:#f0fdf4;color:#14532d}
.ep-combo--naranja input{border:2px solid #f59e0b;background:#fff7ed;color:#9a3412}
.ep-combo--azul input{border:2px solid #123c6b;background:#f2f7ff;color:#123c6b}
.ep-combo--rojo input:focus,.ep-combo--verde input:focus,.ep-combo--naranja input:focus,.ep-combo--azul input:focus{outline:3px solid rgba(109,7,26,.18)}
/* 📱 En pantallas muy angostas se achica un poco la letra para que sigan entrando
   los DOS bloques por fila (nunca se apilan). ⚠️ Va al FINAL a propósito: tiene la
   misma especificidad que `.ep-combo input`, así que gana por ir después. */
@media(max-width:560px){
  .ep-rubros{gap:6px}
  .ep-combo--rubro input{font-size:13.5px;padding:9px 22px 9px 9px}
}
.ep-combo__flecha{position:absolute;right:8px;top:50%;transform:translateY(-50%);pointer-events:none;color:#78716c;font-size:12px}
.ep-combo__lista{position:absolute;z-index:80;left:0;right:0;top:calc(100% + 4px);max-height:290px;overflow:auto;background:#fff;border:1px solid #d6d3d1;border-radius:12px;box-shadow:0 12px 28px rgba(0,0,0,.16);padding:4px}
.ep-combo__lista[hidden]{display:none}
.ep-combo__grupo{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:#78716c;padding:8px 10px 4px}
.ep-combo__item{display:block;width:100%;text-align:left;border:0;background:none;padding:9px 10px;border-radius:9px;font-size:15px;font-family:inherit;color:#1c1917;cursor:pointer}
.ep-combo__item:hover,.ep-combo__item--sel{background:#eef2ff}
.ep-combo__item em{font-style:normal;font-weight:800;background:#fef08a}
.ep-combo__vacio{padding:10px;font-size:13.5px;color:#78716c}
.ep-pill{display:inline-flex;align-items:center;gap:6px;background:#dcfce7;color:#15803d;border:1px solid #bbf7d0;border-radius:999px;padding:4px 10px;font-size:13px;font-weight:800;margin-top:8px}
.ep-pill[hidden]{display:none}
</style>

<div class="ep-wrap">

  <h1 class="ep-h1">🏪 Portadas de tiendas · flyer del rubro + prompt con IA</h1>
  <p class="ep-sub">
    Tiendas <b>por orden de llegada</b> (las últimas creadas primero), <b>20 por página</b> = 1 lote.
    La <b>portada</b> de una tienda es su <b>primera foto</b> (la que sale en el buscador y en la ficha).
    <b>Nada recarga la página</b>: arrastras la imagen encima y queda publicada.
  </p>

  <div class="ep-caja">
    <div class="et-regla">
      🎯 <b>Las 2 reglas de estos prompts:</b><br>
      1) <b>EL RUBRO MANDA</b> — la imagen es un <b>flyer publicitario del rubro</b> (una ferretería con
      ferretería, una peluquería con peluquería), <b>jamás</b> una ilustración del nombre: «Mi Corazón»
      <b>no</b> son corazones, «Virgen del Carmen» <b>no</b> es una imagen religiosa.<br>
      2) <b>EL NOMBRE ES TEXTO</b> — el nombre va como <b>único texto</b>, en letras grandes y artísticas,
      más un <b>logo/emblema simple del rubro</b>.
    </div>
    <div class="ep-pasos" style="margin-top:12px">
      <div class="ep-paso"><b>1 · Copia el prompt</b>Toca 📋 Copiar prompt (el prompt va oculto; en su lugar se ve la <b>descripción de la tienda</b>) y pégalo en tu IA de imágenes (Gemini).</div>
      <div class="ep-paso"><b>2 · Genera el flyer</b>La IA devuelve la portada del rubro con el nombre artístico.</div>
      <div class="ep-paso"><b>3 · Suéltala encima</b>Arrastra la imagen sobre la foto (o <b>Ctrl+V</b>) y se publica al instante.</div>
    </div>
    <div class="ep-botones">
      <a class="ep-btn ep-btn--principal" href="https://gemini.google.com/app" target="_blank" rel="noopener">🪄 Abrir generador de imágenes (Gemini)</a>
      <a class="ep-btn" href="<?= url('editaproductos.php') ?>">🎨 Editor de productos</a>
      <a class="ep-btn" href="<?= url('superadmin.php?seccion=tiendas') ?>">🏬 Tiendas en Súper Admin</a>
    </div>
  </div>

  <?php if ($nota !== ''): ?>
    <div class="ep-aviso ep-aviso--<?= $nota_tipo === 'error' ? 'error' : 'ok' ?>"><?= e($nota) ?></div>
  <?php endif; ?>

  <?php if (!$tablas_ok): ?>
    <div class="ep-aviso ep-aviso--error">No se pudieron preparar las tablas del editor en la base de datos.</div>
  <?php endif; ?>

  <div class="ep-contadores">
    <span class="ep-cont">🏪 <b data-cont="total"><?= number_format($stats['total']) ?></b> tiendas</span>
    <span class="ep-cont">🎨 <b data-cont="con_prompt"><?= number_format($stats['con_prompt']) ?></b> con prompt</span>
    <span class="ep-cont">⏳ <b data-cont="sin_prompt"><?= number_format(max(0, $stats['total'] - $stats['con_prompt'])) ?></b> sin prompt</span>
    <span class="ep-cont">🖼️ <b data-cont="con_imagen"><?= number_format($stats['con_imagen']) ?></b> con portada</span>
    <?php foreach ($lotes_guardados as $n_lote => $n_neg): ?>
      <span class="ep-cont"><?= $n_lote > 0 ? '✅ Lote ' . (int)$n_lote . ' (' . number_format($n_neg) . ')' : '✍️ Tuyos (' . number_format($n_neg) . ')' ?></span>
    <?php endforeach; ?>
  </div>

  <form method="get" class="ep-filtros">
    <input type="hidden" name="f" value="<?= e($filtro) ?>">
    <div class="ep-busca">
      <label for="epQ" style="font-size:13px;font-weight:700;display:block;margin-bottom:4px">Buscar tienda o rubro (filtra la lista al instante)</label>
      <input type="text" id="epQ" name="q" value="<?= e($q) ?>" placeholder="Ej: ferretería, peluquería, agua…" autocomplete="off">
    </div>
    <button class="ep-btn ep-btn--principal" type="submit" style="margin-top:20px">🔎 Buscar en todas las tiendas</button>
  </form>

  <div class="ep-filtros">
    <?php
      $chips = ['todos' => '🗂️ Todas', 'pendientes' => '⏳ Sin prompt', 'sinfoto' => '⬜ Sin portada', 'confoto' => '🖼️ Con portada'];
      foreach ($chips as $clave => $etiqueta):
        $u = 'editatiendas.php?f=' . $clave . '&p=1' . ($q !== '' ? '&q=' . urlencode($q) : '');
    ?>
      <a class="ep-chip <?= $filtro === $clave ? 'ep-chip--on' : '' ?>" href="<?= e($u) ?>"><?= $etiqueta ?></a>
    <?php endforeach; ?>
    <span class="ep-cont" style="margin-left:auto"><?= number_format($total) ?> resultado(s) · página <?= $pagina ?> de <?= $paginas ?></span>
  </div>

  <?php if ($sync['nuevos'] > 0 || $sync['actualizados'] > 0): ?>
    <p class="ep-nota">🎒 Lotes leídos: <?= e(implode(' · ', $sync['archivos']) ?: 'ninguno') ?>
      → <?= (int)$sync['nuevos'] ?> prompt(s) nuevos, <?= (int)$sync['actualizados'] ?> corregido(s).</p>
  <?php endif; ?>

  <?php if (!$tiendas): ?>
    <div class="ep-vacio">No hay tiendas con este filtro.</div>
  <?php endif; ?>

  <?php foreach ($tiendas as $t): ?>
    <?php
      $nid       = (int)$t['id'];
      $portada   = trim((string)($t['portada'] ?? ''));
      $tiene     = $portada !== '';
      $prompt_txt = trim((string)($t['prompt'] ?? ''));
      $es_asist  = ($prompt_txt !== '' && ($t['origen'] ?? '') === 'asistente');
      $es_jefe   = ($prompt_txt !== '' && ($t['origen'] ?? '') === 'jefe');
      $mostrado  = $prompt_txt !== '' ? $prompt_txt : et_prompt_base($t);
      $desc_50   = et_primeras_palabras((string)($t['descripcion'] ?? ''), 50);   // 📝 lo que se ve en la tarjeta
      $fid       = 'etPrompt' . $nid;
      $rubro_txt = trim((string)($t['rubro'] ?? '')) ?: 'sin rubro';

      // 🏷️ Hasta 4 rubros: slot 1 = principal (categoria_id) y 2-4 = extras.
      $slots = [1 => trim((string)($t['rubro'] ?? ''))];
      $extras = array_filter(array_map('trim', explode('|', (string)($t['rubros_extra'] ?? ''))), 'strlen');
      $i = 2;
      foreach ($extras as $ex) { if ($i > 4) break; $slots[$i++] = $ex; }
      for ($k = 1; $k <= 4; $k++) if (!isset($slots[$k])) $slots[$k] = '';
      $rubros_txt = implode(' · ', array_filter($slots, 'strlen'));
      $rubro_icono = trim((string)($t['rubro_icono'] ?? ''));
    ?>
    <div class="ep-card" id="etCard<?= $nid ?>"
         data-producto="<?= $nid ?>"
         data-precio="0.00"
         data-unidad=""
         data-rubros-firma="<?= e(implode('|', array_values($slots))) ?>"
         data-busca="<?= e(mb_strtolower($t['nombre'] . ' ' . $rubro_txt . ' ' . (string)$t['distrito'] . ' ' . $nid)) ?>">
      <div class="ep-card__head">
        <span class="ep-card__id">#<?= $nid ?></span>
        <h2 class="ep-card__titulo"><?= e($t['nombre']) ?></h2>
      </div>
      <div class="ep-chips">
        <span class="ep-chipmini ep-chipmini--rubro" data-vista-rubro>🏷️ <?= e($rubro_icono !== '' ? $rubro_icono . ' ' : '') ?><?= e($rubros_txt !== '' ? $rubros_txt : 'sin rubro') ?></span>
        <span class="ep-chipmini">📍 <?= e((string)($t['distrito'] ?: 'Chimbote')) ?></span>
        <?php if ((int)$t['n_fotos'] > 0): ?>
          <span class="ep-chipmini ep-chipmini--ok" data-vista-foto>🖼️ Con portada (<?= (int)$t['n_fotos'] ?> foto<?= (int)$t['n_fotos'] === 1 ? '' : 's' ?>)</span>
        <?php else: ?>
          <span class="ep-chipmini ep-chipmini--falta" data-vista-foto>⬜ Sin portada</span>
        <?php endif; ?>
        <span class="ep-chipmini">📦 <?= (int)$t['n_productos'] ?> producto(s)</span>
        <?php if ($es_jefe): ?>
          <span class="ep-chipmini" data-vista-prompt>✍️ Tu prompt</span>
        <?php elseif ($es_asist): ?>
          <span class="ep-chipmini" data-vista-prompt>✨ Prompt del asistente<?= (int)($t['lote'] ?? 0) > 0 ? ' · lote ' . (int)$t['lote'] : '' ?></span>
        <?php else: ?>
          <span class="ep-chipmini ep-chipmini--falta" data-vista-prompt>🧩 Prompt base (el asistente lo mejorará)</span>
        <?php endif; ?>
      </div>

      <div class="ep-cuerpo">
        <!-- ===== IZQUIERDA: la portada actual (zona para arrastrar y soltar) ===== -->
        <div class="ep-foto">
          <div class="ep-drop" data-producto="<?= $nid ?>">
            <div class="ep-foto__marco" data-marco>
              <?php if ($tiene): ?>
                <?= img_tag($portada, (string)$t['nombre'], [
                      'sizes' => '(max-width: 900px) 45vw, 165px',
                      'zoom'  => true,
                      'extra' => ['id' => 'epImg' . $nid, 'data-ruta' => $portada],
                    ]) ?>
              <?php else: ?>
                <div class="ep-foto__vacia" data-vacio>⬜<br>Esta tienda no tiene portada.<br>Genera el flyer y suéltalo aquí.</div>
              <?php endif; ?>
              <div class="ep-drop__velo"><b>Suelta la portada aquí</b><span>se publica al instante (sin recargar)</span></div>
              <div class="ep-drop__sello" data-sello hidden>🆕 publicando…</div>
            </div>
            <div class="ep-progreso" data-progreso><i></i></div>
          </div>
          <p class="ep-foto__ayuda">
            <b>Arrastra el flyer y suéltalo encima</b> (o <b>Ctrl+V</b> si lo copiaste en Gemini · o
            <label for="epFile<?= $nid ?>" style="cursor:pointer;text-decoration:underline">elige el archivo</label>).
          </p>
          <div class="ep-estado" data-estado hidden></div>
          <?php if (trim((string)($t['ruta_anterior'] ?? '')) !== ''): ?>
            <button type="button" class="ep-btn ep-btn--suave ep-btn--mini" data-deshacer="<?= $nid ?>"
                    style="margin-top:8px">↩️ Deshacer el último cambio de portada</button>
          <?php endif; ?>
          <p class="ep-foto__pie"><?= e($tiene ? $portada : 'sin archivo') ?></p>

          <form method="post" enctype="multipart/form-data" class="ep-form-foto">
            <?= csrf_campo() ?>
            <input type="hidden" name="accion" value="cambiar_imagen">
            <input type="hidden" name="producto_id" value="<?= $nid ?>">
            <input type="hidden" name="volver_p" value="<?= $pagina ?>">
            <input type="hidden" name="volver_f" value="<?= e($filtro) ?>">
            <input type="hidden" name="volver_q" value="<?= e($q) ?>">
            <input type="file" id="epFile<?= $nid ?>" name="foto" accept="image/*" hidden>
          </form>
        </div>

        <!-- ===== DERECHA: rubros + prompt ===== -->
        <div>
          <!-- 🏷️ RUBROS: hasta 4, cada uno con SU COLOR. El 1.º (rojo oscuro) es el
               rubro PRINCIPAL (alfa); los otros TAMBIÉN son rubros de la tienda
               (no son subrubros). Se guardan SOLOS: no hay botón de guardar. -->
          <div class="ep-datos">
            <div class="ep-campo" style="flex:1 1 100%">
              <div class="ep-rubros">
                <?php
                  $colores = [1 => 'rojo', 2 => 'verde', 3 => 'naranja', 4 => 'azul'];
                  for ($k = 1; $k <= 4; $k++):
                ?>
                  <div class="ep-combo ep-combo--rubro ep-combo--<?= $colores[$k] ?>" data-campo="rubro<?= $k ?>">
                    <input type="text" class="ep-rubro" data-ep-lista="rubros" data-campo="rubro" data-slot="<?= $k ?>"
                           value="<?= e($slots[$k]) ?>" autocomplete="off" spellcheck="false"
                           placeholder="<?= $k === 1 ? 'Rubro principal (alfa)…' : 'Otro rubro de la tienda…' ?>">
                    <span class="ep-combo__flecha">▾</span>
                    <div class="ep-combo__lista" hidden></div>
                  </div>
                <?php endfor; ?>
              </div>
            </div>
          </div>
          <div class="ep-pill" data-pill-rubros<?= $rubros_txt === '' ? ' hidden' : '' ?>>🏷️ <?= e($rubros_txt) ?></div>

          <div class="ep-prompt" style="margin-top:12px">
          <div class="ep-prompt__eti">
            <span class="ep-prompt__txt">📝 Descripción de la tienda
              <span class="ep-prompt__sub">(primeras 50 palabras · solo lectura)</span></span>
            <span class="ep-chipmini"><?= $es_jefe ? 'prompt tuyo' : ($es_asist ? 'prompt del asistente' : 'prompt base automático') ?></span>
          </div>
          <?php /* 📝 En lugar del cuadro del prompt va la DESCRIPCIÓN (pedido del jefe:
                   «muéstrame las primeras 50 palabras … solo ver no editar»). */ ?>
          <div class="ep-desc" data-descripcion><?php
            if ($desc_50 !== '') { echo e($desc_50); }
            else { echo '<span class="ep-desc__vacia">Esta tienda no tiene descripción cargada.</span>'; }
          ?></div>
          <form method="post" class="ep-form-prompt">
            <?= csrf_campo() ?>
            <input type="hidden" name="accion" value="guardar_prompt">
            <input type="hidden" name="producto_id" value="<?= $nid ?>">
            <input type="hidden" name="volver_p" value="<?= $pagina ?>">
            <input type="hidden" name="volver_f" value="<?= e($filtro) ?>">
            <input type="hidden" name="volver_q" value="<?= e($q) ?>">
            <?php /* 🔇 El prompt NO se muestra (pedido del jefe): va oculto aquí dentro
                     solo para que el botón 📋 Copiar prompt lo copie al portapapeles. */ ?>
            <textarea id="<?= $fid ?>" class="ep-prompt__oculto" name="prompt" spellcheck="false"
                      tabindex="-1" aria-hidden="true"><?= e($mostrado) ?></textarea>
            <div class="ep-botones">
              <button type="button" class="ep-btn ep-btn--ok" data-copiar="<?= $fid ?>">📋 Copiar prompt</button>
              <?php if ($es_jefe): ?>
                <button type="submit" class="ep-btn ep-btn--suave" name="accion" value="restaurar_prompt"
                        data-restaurar-prompt="<?= $nid ?>">↩️ Volver al del asistente</button>
              <?php endif; ?>
              <a class="ep-btn" href="<?= url('productos.php?n=' . $nid) ?>" target="_blank" rel="noopener">✏️ Editar</a>
              <a class="ep-btn" href="<?= url('negocio.php?slug=' . urlencode((string)$t['slug'])) ?>" target="_blank" rel="noopener">👁️ Ver ficha</a>
            </div>
          </form>
          <?php /* 🗑️ ELIMINAR: borra la tienda con todo lo suyo. Va en su PROPIO
                   formulario (no se pueden anidar) y pide confirmación. */ ?>
          <form method="post" class="ep-form-eliminar"
                onsubmit="<?= e('return confirm(' . json_encode('⚠️ ¿ELIMINAR «' . (string)$t['nombre'] . '» y TODO lo suyo (productos, fotos, opiniones, rubros y avisos)? Esta acción NO se puede deshacer.', JSON_UNESCAPED_UNICODE) . ');') ?>">
            <?= csrf_campo() ?>
            <input type="hidden" name="accion" value="eliminar_negocio">
            <input type="hidden" name="producto_id" value="<?= $nid ?>">
            <input type="hidden" name="volver_p" value="<?= $pagina ?>">
            <input type="hidden" name="volver_f" value="<?= e($filtro) ?>">
            <input type="hidden" name="volver_q" value="<?= e($q) ?>">
            <div class="ep-botones">
              <button type="submit" class="ep-btn ep-btn--peligro">🗑️ Eliminar tienda</button>
              <span class="ep-nota">Borra la tienda con sus productos, fotos y avisos. No se puede deshacer.</span>
            </div>
          </form>
          </div><!-- /ep-prompt -->
          </div><!-- /columna derecha -->
        </div><!-- /ep-cuerpo -->
      </div><!-- /ep-card -->
  <?php endforeach; ?>

  <?php if ($paginas > 1): ?>
    <div class="ep-pag">
      <?php
        $base = 'editatiendas.php?f=' . urlencode($filtro) . ($q !== '' ? '&q=' . urlencode($q) : '') . '&p=';
        $ant  = max(1, $pagina - 1);
        $sig  = min($paginas, $pagina + 1);
      ?>
      <a class="<?= $pagina <= 1 ? 'off' : '' ?>" href="<?= e($base . $ant) ?>">‹ Lote anterior</a>
      <span class="act">Lote <?= $pagina ?> de <?= $paginas ?></span>
      <a class="<?= $pagina >= $paginas ? 'off' : '' ?>" href="<?= e($base . $sig) ?>">Lote siguiente ›</a>
    </div>
  <?php endif; ?>

</div>

<script>
window.EP_URL_ACCION = <?= json_encode('editatiendas.php') ?>;
window.EP_SIZES_FOTO = <?= json_encode('(max-width: 900px) 45vw, 165px') ?>;   // el bloque de la foto es angosto (la mitad)
<?php
// 🏷️ Lista de RUBROS para el select predictivo (agrupada por letra inicial).
$norm_r = function ($t) {
    $t = mb_strtolower(trim((string)$t), 'UTF-8');
    return str_replace(['á','à','ä','â','ã','é','è','ë','ê','í','ì','ï','î','ó','ò','ö','ô','ú','ù','ü','û','ñ'],
                       ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','u','u','u','u','n'], $t);
};
$lista_rubros = [];
foreach (obtener_categorias() as $cat) {
    $n = trim((string)$cat['nombre']);
    if ($n === '') continue;
    $lista_rubros[] = ['u' => $n, 'g' => mb_strtoupper(mb_substr($n, 0, 1, 'UTF-8'), 'UTF-8'), 'n' => $norm_r($n)];
}
echo 'window.EP_LISTAS = ' . json_encode(['rubros' => $lista_rubros], JSON_UNESCAPED_UNICODE) . ";\n";
?>
</script>
<script src="<?= url('assets/js/imagen_optimizar.js') ?>?v=1"></script>
<script src="<?= url('assets/js/editor_productos.js') ?>?v=6"></script>

<?php include __DIR__ . '/includes/footer.php'; ?>
