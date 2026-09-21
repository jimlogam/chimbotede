<?php
/**
 * subir.php — Recibe la captura de la app "Caminante".
 * ============================================================
 * 1) SIEMPRE guarda las fotos y datos en: public_html/caminante/<carpeta>/
 *    Las fotos ahora se validan y se guardan OPTIMIZADAS (WebP, máx 1600 px) con
 *    sus versiones de 300 px y 800 px — motor: includes/imagenes.php.
 *    Nombres: portada_N.webp | info_N.webp | tienda_N.webp | prod_P_N.webp
 *    (+ sus variantes -300.webp / -800.webp) y datos.txt con nota, ubicación y productos.
 *    ⚠️ Antes se guardaban como .jpg crudos, sin validar ni comprimir: una foto de
 *    10 MB entraba tal cual (medido: hasta 4,59 MB). Ya no.
 * 2) Si llega `crear=1` con `categoria_id` y `distrito_id` válidos (+ CSRF),
 *    además CREA la TIENDA en la base del sitio (dechimbote.com):
 *      - directorio_negocios  → estado 'pendiente' (para revisión del admin)
 *      - directorio_fotos     → las fotos de la captura
 *      - directorio_servicios + directorio_producto_fotos → los productos capturados
 *    Si quien guarda tiene SESIÓN iniciada en dechimbote.com, la tienda queda
 *    asignada a su cuenta (dueno_id). Si no, queda sin dueño (pendiente).
 * 🆕 2026-09-10: acepta `subcategoria_id` opcional (Pollerías, Cevicherías, Chifas…).
 *    El rubro se guarda por CATEGORÍA; la subcategoría viaja aparte en su columna de
 *    siempre. Antes se perdía: por eso los 207 restaurantes quedaron "sin subcategoría".
 * 🆕 2026-09-10 (tarde): REGISTRO de cada subida (pedido del jefe) en
 *    `caminante/_registro_subidas.log` (+ `.jsonl`), con ubicación, precisión del GPS, cuántas
 *    fotos mandó el celular y cuántas llegaron/guardaron, tamaño, tiempos, IP, dispositivo y
 *    resultado — se guarda SIEMPRE, también cuando la subida falla. Motor:
 *    `includes/caminante_registro.php`. Si algo no cuadra, avisa al jefe por Telegram.
 *    ⚠️ El celular debe mandar las fotos como **`foto[]`** (array). Con `foto` repetido, PHP
 *    **se queda solo con la ÚLTIMA** y se perdían todas las demás (era el caso hasta hoy).
 * Respuesta: JSON {ok:true,...} en modo crear · texto "OK ..." en modo simple.
 */
header('Content-Type: text/plain; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); die('Método no permitido'); }

/* ⚠️ La zona horaria se fija AQUÍ, antes de todo: el registro de la subida se escribe también
   cuando no se llega a cargar config.php (modo "solo fotos" o CSRF inválido) y, sin esta línea,
   la hora del archivo .log salía en UTC (el hosting va en UTC) en vez de hora de Lima. */
date_default_timezone_set('America/Lima');

// Motor de imágenes (valida, optimiza a WebP y genera las versiones de tamaño).
require_once __DIR__ . '/../includes/imagenes.php';
// Registro de cada subida: archivo .log legible + .jsonl + datos.txt ampliado.
require_once __DIR__ . '/../includes/caminante_registro.php';

$cam_t0 = microtime(true);

/* Contexto del que sube (para el registro). Se calcula aquí porque el registro puede
   escribirse ANTES de cargar config.php (p. ej. si el CSRF falla). */
/* ⚠️ 2026-09-16 (Cloudflare): la IP se saca con `ip_real()` —el motor de
   includes/ip_real.php, que se carga aquí aparte porque config.php todavía no está
   cargado—, NO con `HTTP_X_FORWARDED_FOR` a pelo: esa cabecera la puede inventar
   cualquiera que llame directo al servidor. La carga es defensiva: si el motor no
   estuviera, se sigue con la IP de la conexión (nada se cae). */
$__ip_real_motor = __DIR__ . '/../includes/ip_real.php';
if (is_file($__ip_real_motor)) { require_once $__ip_real_motor; }
unset($__ip_real_motor);
$cam_ip = function_exists('ip_real') ? ip_real() : trim((string)($_SERVER['REMOTE_ADDR'] ?? ''));
$cam_ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
$cam_captura_s = null;
if (isset($_POST['captura_t0']) && is_numeric($_POST['captura_t0'])) {
    $cam_captura_s = max(0, (int)round((microtime(true) * 1000 - (float)$_POST['captura_t0']) / 1000));
}
$cam_reg = [
    'fecha'          => date('Y-m-d H:i:s'),
    'fecha_utc'      => gmdate('Y-m-d H:i:s'),
    'modo'           => (($_POST['crear'] ?? '') === '1') ? 'crear' : 'simple',
    'nombre'         => trim((string)($_POST['nombre'] ?? '')),
    'carpeta'        => '',
    'lat'            => (string)($_POST['lat'] ?? ''),
    'lng'            => (string)($_POST['lng'] ?? ''),
    'precision'      => is_numeric($_POST['precision'] ?? null) ? (int)round((float)$_POST['precision']) : '',
    'direccion'      => (string)($_POST['direc'] ?? ''),
    'nota_largo'     => mb_strlen(trim((string)($_POST['nota'] ?? ''))),
    'fotos_cliente'  => (int)($_POST['fotos_cliente'] ?? 0),
    'resumen_fotos'  => (string)($_POST['resumen_fotos'] ?? ''),
    'pantalla'       => (string)($_POST['pantalla'] ?? ''),
    'captura_s'      => $cam_captura_s,
    'ip'             => $cam_ip,
    'dispositivo'    => cam_dispositivo($cam_ua),
    'ua'             => mb_substr($cam_ua, 0, 250),
    'resultado'      => 'iniciando',
];

$crear = (($_POST['crear'] ?? '') === '1');

/* ---------- 1) Guardar carpeta de fotos (siempre) ---------- */
$base = __DIR__;
$nombre = trim($_POST['nombre'] ?? 'sin-nombre');
$carpeta = trim($_POST['carpeta'] ?? ('tienda_' . time()));
$carpeta = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $carpeta);
if ($carpeta === '' || $carpeta === '_') $carpeta = 'tienda_' . time();
$destino = $base . '/' . $carpeta;
if (!is_dir($destino)) { mkdir($destino, 0775, true); }

$nFotos = 0;
$nRechazadas = 0;
$detalle_fotos = [];        // una entrada por archivo recibido (para el registro)
$fotos_recibidas = 0;
$bytes_recibidos = 0;
$bytes_guardados = 0;
if (!empty($_FILES['foto'])) {
    $fotos = $_FILES['foto'];
    $esLista    = is_array($fotos['name']);
    $nombres    = $esLista ? $fotos['name']     : [$fotos['name']];
    $temporales = $esLista ? $fotos['tmp_name'] : [$fotos['tmp_name']];
    $errores    = $esLista ? $fotos['error']    : [$fotos['error']];
    $pesos      = $esLista ? ($fotos['size'] ?? []) : [$fotos['size'] ?? 0];
    $tipos      = $esLista ? ($fotos['type'] ?? []) : [$fotos['type'] ?? ''];
    $fotos_recibidas = count($nombres);

    for ($i = 0; $i < count($nombres); $i++) {
        $fname = basename($nombres[$i]);
        $fname = preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $fname);
        $nombre_base = preg_replace('/\.[^.]+$/', '', $fname);   // p. ej. prod_1_2
        if ($nombre_base === '' || $nombre_base === null) $nombre_base = 'foto_' . ($i + 1);

        $bytes_recibidos += (int)($pesos[$i] ?? 0);

        $res = img_guardar_subida([
            'name'     => $fname,
            'type'     => $tipos[$i] ?? '',
            'tmp_name' => $temporales[$i],
            'error'    => $errores[$i] ?? UPLOAD_ERR_NO_FILE,
            'size'     => $pesos[$i] ?? 0,
        ], 'caminante/' . $carpeta, $nombre_base);

        if (!empty($res['ok'])) {
            $nFotos++;
            $bytes_guardados += (int)($res['peso'] ?? 0);
        } else {
            $nRechazadas++;
        }
        $detalle_fotos[] = [
            'archivo'  => $fname,
            'peso'     => (int)($pesos[$i] ?? 0),
            'ok'       => !empty($res['ok']),
            'error'    => $res['error'] ?? null,
            'ruta'     => $res['rel'] ?? null,
            'peso_final' => (int)($res['peso'] ?? 0),
            'ancho'    => (int)($res['ancho'] ?? 0),
            'alto'     => (int)($res['alto'] ?? 0),
        ];
    }
}

$productos_raw = json_decode($_POST['productos'] ?? '[]', true) ?: [];
$datos = [
    'nombre' => $nombre,
    'carpeta' => $carpeta,
    'fecha' => date('c'),
    'lat' => $_POST['lat'] ?? '',
    'lng' => $_POST['lng'] ?? '',
    'direccion' => $_POST['direc'] ?? '',
    'inicio' => $_POST['inicio'] ?? '',
    'nota' => $_POST['nota'] ?? '',
    'productos' => $productos_raw,
    'total_fotos' => $nFotos,
    'fotos_rechazadas' => $nRechazadas,
    'crear_tienda' => $crear,
];
file_put_contents($destino . '/datos.txt', json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

/* ===== REGISTRO de la subida (pedido del jefe): lo que llegó, de dónde, cuándo y con qué GPS.
   Se completa a lo largo del script y se escribe en cada salida (también cuando falla). ===== */
$cam_reg['carpeta']         = $carpeta;
$cam_reg['fotos_recibidas'] = $fotos_recibidas;
$cam_reg['fotos_ok']        = $nFotos;
$cam_reg['fotos_rechazadas'] = $nRechazadas;
$cam_reg['bytes_recibidos'] = $bytes_recibidos;
$cam_reg['bytes_guardados'] = $bytes_guardados;
$cam_reg['detalle_fotos']   = $detalle_fotos;
$cam_reg['productos']       = count($productos_raw);
$cam_reg['detalle_texto']   = implode(' ; ', array_map(function ($d) {
    return $d['archivo'] . ' ' . round($d['peso'] / 1024) . 'KB -> ' . ($d['ok'] ? 'GUARDADA' : ('FALLO: ' . $d['error']));
}, $detalle_fotos));

/** Cierra el registro y responde (se usa en cada salida del script). */
function cam_cerrar(array &$reg, float $t0, string $resultado, string $msg = ''): void
{
    $reg['resultado']  = $resultado;
    $reg['msg']        = $msg;
    $reg['duracion_s'] = round(microtime(true) - $t0, 2);
    cam_registro_guardar($reg);
}

/* ---------- 2) Crear la tienda en la base del sitio ---------- */
if (!$crear) {
    cam_cerrar($cam_reg, $cam_t0, 'solo-fotos', 'guardado sin crear tienda');
    echo 'OK guardado_en_caminante/' . $carpeta . ' fotos=' . $nFotos;
    exit;
}

require_once __DIR__ . '/../config.php'; // helpers del sitio: db(), usuario_actual(), iniciar_sesion(), slugify(), slug_unico(), url()...
iniciar_sesion();

if (!hash_equals($_SESSION['csrf'] ?? '', (string)($_POST['csrf'] ?? ''))) {
    cam_cerrar($cam_reg, $cam_t0, 'token_invalido', 'el CSRF no coincide');
    echo json_encode(['ok' => false, 'error' => 'token_invalido'], JSON_UNESCAPED_UNICODE);
    exit;
}

$categoria_id = (int)($_POST['categoria_id'] ?? 0);
$distrito_id  = (int)($_POST['distrito_id'] ?? 0);
$subcategoria_id = (int)($_POST['subcategoria_id'] ?? 0);

$pdo = db();
// Validar rubro y distrito reales
$okCat = $pdo->prepare('SELECT 1 FROM directorio_categorias WHERE id = ? AND activo = 1');
$okCat->execute([$categoria_id]);
$okDis = $pdo->prepare('SELECT 1 FROM directorio_distritos WHERE id = ? AND visible = 1');
$okDis->execute([$distrito_id]);

// Nombres legibles de rubro y distrito para el registro (aunque la validación falle).
$cam_reg['rubro']    = (string)$categoria_id;
$cam_reg['distrito'] = (string)$distrito_id;
try {
    $n = $pdo->prepare('SELECT CONCAT(id, " ", nombre) FROM directorio_categorias WHERE id = ? LIMIT 1');
    $n->execute([$categoria_id]);
    if ($v = $n->fetchColumn()) $cam_reg['rubro'] = (string)$v;
    $n = $pdo->prepare('SELECT CONCAT(id, " ", nombre) FROM directorio_distritos WHERE id = ? LIMIT 1');
    $n->execute([$distrito_id]);
    if ($v = $n->fetchColumn()) $cam_reg['distrito'] = (string)$v;
} catch (Throwable $e) { /* el registro no debe romper la subida */ }

if (!$okCat->fetchColumn() || !$okDis->fetchColumn()) {
    cam_cerrar($cam_reg, $cam_t0, 'rubro_distrito_invalidos',
        'rubro=' . $categoria_id . ' distrito=' . $distrito_id);
    echo json_encode(['ok' => false, 'error' => 'rubro_distrito_invalidos'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Subcategoría (opcional): solo vale si pertenece al rubro elegido y está activa.
if ($subcategoria_id) {
    $okSub = $pdo->prepare('SELECT 1 FROM directorio_subcategorias WHERE id = ? AND categoria_id = ? AND activo = 1');
    $okSub->execute([$subcategoria_id, $categoria_id]);
    if (!$okSub->fetchColumn()) $subcategoria_id = 0;
}

$usuario = usuario_actual();
$dueno_id = $usuario ? (int)$usuario['id'] : null;

$latIn = trim($_POST['lat'] ?? '');
$lngIn = trim($_POST['lng'] ?? '');
$lat = ($latIn !== '' && is_numeric($latIn) && (float)$latIn >= -90 && (float)$latIn <= 90) ? (float)$latIn : null;
$lng = ($lngIn !== '' && is_numeric($lngIn) && (float)$lngIn >= -180 && (float)$lngIn <= 180) ? (float)$lngIn : null;

$descripcion = trim($_POST['nota'] ?? '') ?: null;
$direccion = trim($_POST['direc'] ?? '');
if (mb_strlen($direccion) > 250) $direccion = mb_substr($direccion, 0, 250);

try {
    $nombreFinal = ($nombre !== '' && $nombre !== 'sin-nombre') ? $nombre : ('Tienda capturada ' . $carpeta);
    $slug = slug_unico(slugify($nombreFinal));

    $pdo->prepare("INSERT INTO directorio_negocios
        (nombre, slug, categoria_id, subcategoria_id, distrito_id, ubicacion_tipo, direccion, lat, lng,
         descripcion, dueno_id, estado, plantilla_id, paleta_id)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)")
        ->execute([
            $nombreFinal, $slug, $categoria_id, ($subcategoria_id ?: null), $distrito_id, 'fisica', $direccion ?: null, $lat, $lng,
            $descripcion, $dueno_id, 'pendiente', 1, 1,
        ]);
    $negocio_id = (int)$pdo->lastInsertId();

    // 🔎 Buscador fuzzy: la tienda capturada entra ya en las sugerencias (cuando
    // se apruebe) sin esperar la hora de caché.
    require_once __DIR__ . '/../includes/fuzzy_cache.php';
    fuzzy_olvidar_cache();

    // 🔔 Aviso al jefe: tienda capturada desde Caminante
    $cat_nombre = '';
    if ($categoria_id) {
        $sc = $pdo->prepare("SELECT nombre FROM directorio_categorias WHERE id = ? LIMIT 1");
        $sc->execute([$categoria_id]);
        $cat_nombre = (string)$sc->fetchColumn();
    }
    aviso('tienda_nueva', [
        'nombre'   => $nombreFinal,
        'slug'     => $slug,
        'rubro'    => $cat_nombre . ' (capturada por Caminante)',
        'estado'   => 'pendiente de aprobación',
        'clave'    => 'tienda:' . $negocio_id,
        'resumen'  => 'caminante: ' . $nombreFinal,
    ]);

    // === INTEGRACIÓN HUBSPOT: sincronizar al dueño con el CRM ===
    // Solo hace algo si la tienda nació con dueño (con sesión iniciada).
    try {
        require_once __DIR__ . '/../includes/helpers_hubspot.php';
        hubspot_sync_negocio_nuevo($negocio_id);
    } catch (Throwable $e) {
        error_log('HubSpot (caminante/subir): ' . $e->getMessage());
    }
    // === FIN INTEGRACIÓN HUBSPOT ===

    // Si creó SIN sesión: dejar "reclamable" en esta misma sesión del navegador,
    // para que al iniciar sesión / crear cuenta la tienda se asigne sola a esa persona.
    if (!$dueno_id) {
        iniciar_sesion();
        $prev = is_array($_SESSION['cam_claim'] ?? null) ? $_SESSION['cam_claim'] : [];
        if (!in_array($negocio_id, $prev, true)) $prev[] = $negocio_id;
        $_SESSION['cam_claim'] = array_values($prev);
    }

    /* Fotos de la tienda → directorio_fotos
     * ⚠️ Se registran SOLO los archivos completos: las versiones de tamaño
     * (nombre-160 / -300 / -480 / -800.webp, la escalera de `img_escalera()`) son
     * variantes de la misma foto y no deben entrar como fotos aparte. */
    $fotos_dir = glob($destino . '/*.webp');
    $fotos_dir = array_values(array_filter($fotos_dir ?: [], function ($a) {
        return !img_es_version(basename($a));
    }));
    $desc_foto = ['portada_0' => 'Fachada', 'portada_1' => 'Logo'];
    $orden = 0;
    $fotos_en_ficha = 0;
    $insertFoto = $pdo->prepare("INSERT INTO directorio_fotos (negocio_id, ruta, descripcion, orden) VALUES (?,?,?,?)");
    if ($fotos_dir) {
        foreach ($fotos_dir as $archivo) {
            $nombreFoto = basename($archivo);
            if (preg_match('/^prod_\d+_\d+\.webp$/', $nombreFoto)) continue; // las de producto van aparte
            $clave = preg_replace('/\.webp$/', '', $nombreFoto);
            $desc = $desc_foto[$clave] ?? (strpos($clave, 'info_') === 0 ? 'Informativa' : 'Galería');
            $insertFoto->execute([$negocio_id, 'caminante/' . $carpeta . '/' . $nombreFoto, $desc, $orden++]);
            $fotos_en_ficha++;
        }
    }
    $cam_reg['fotos_en_ficha'] = $fotos_en_ficha;
    $cam_reg['archivos_en_carpeta'] = count($fotos_dir);

    /* Productos → directorio_servicios + directorio_producto_fotos */
    $fotos_producto = [];
    if ($fotos_dir) {
        foreach ($fotos_dir as $archivo) {
            if (preg_match('/^prod_(\d+)_(\d+)\.webp$/', basename($archivo), $m)) {
                $fotos_producto[(int)$m[1]][] = basename($archivo);
            }
        }
    }
    if ($productos_raw) {
        $insertServ = $pdo->prepare("INSERT INTO directorio_servicios
            (negocio_id, titulo, tipo_producto, descripcion, precio, unidad, imagen, destacado, activo)
            VALUES (?,?,?,?,?,?,?,0,1)");
        $insertPf = $pdo->prepare("INSERT INTO directorio_producto_fotos (producto_id, ruta, orden) VALUES (?,?,?)");
        foreach ($productos_raw as $i => $prod) {
            if (!is_array($prod)) continue;
            $titulo = trim((string)($prod['titulo'] ?? ''));
            if ($titulo === '') continue;
            $precio = max(0, (float)($prod['precio'] ?? 0));
            $unidad = trim((string)($prod['unidad'] ?? ''));
            if ($unidad === '') $unidad = 'unidad';
            $descP = trim((string)($prod['desc'] ?? '')) ?: null;
            $fotosP = $fotos_producto[$i + 1] ?? [];
            $insertServ->execute([
                $negocio_id, $titulo, 'fisico', $descP, $precio, $unidad,
                $fotosP ? ('caminante/' . $carpeta . '/' . $fotosP[0]) : null,
            ]);
            $servicio_id = (int)$pdo->lastInsertId();
            $ord = 0;
            foreach ($fotosP as $rutaP) {
                $insertPf->execute([$servicio_id, 'caminante/' . $carpeta . '/' . $rutaP, $ord++]);
            }
        }
    }

    $cam_reg['negocio_id'] = $negocio_id;
    $cam_reg['slug']       = $slug;

    echo json_encode([
        'ok' => true,
        'nombre' => $nombreFinal,
        'slug' => $slug,
        'negocio_id' => $negocio_id,
        'dueno' => $dueno_id ? 1 : 0,
        'url' => $dueno_id ? url('neg/' . $slug) : '',
        'msg' => $dueno_id ? 'Tienda creada y asignada a tu cuenta.' : 'Tienda creada (pendiente de revisión).',
    ], JSON_UNESCAPED_UNICODE);
    cam_cerrar($cam_reg, $cam_t0, 'ok', $nombreFinal . ' creada con ' . ($cam_reg['fotos_en_ficha'] ?? 0) . ' foto(s)');
} catch (Throwable $e) {
    cam_cerrar($cam_reg, $cam_t0, 'no_se_pudo_crear', $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'no_se_pudo_crear', 'msg' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
