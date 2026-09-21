<?php
/**
 * api/cerca_de_mi.php — Tiendas activas con ubicación, en JSON.
 * ============================================================
 * Endpoint SOLO LECTURA para la herramienta móvil "Tiendas cerca de mí"
 * y para la sección "Ver negocios cerca" de la portada.
 * Devuelve los negocios ACTIVOS que tienen lat/lng guardada, para que una
 * página (o el navegador) calcule la distancia con el GPS del celular.
 *
 * Uso:
 *   GET /api/cerca_de_mi.php                       -> TODAS las tiendas con coordenadas
 *   GET /api/cerca_de_mi.php?lat=-9.07&lng=-78.59  -> con distancia_m (ordenadas de cerca a lejos)
 *   GET /api/cerca_de_mi.php?lat=..&lng=..&radio=5 -> limita por radio en km (radio=0 o sin radio = sin límite)
 *   GET /api/cerca_de_mi.php?lat=..&lng=..&radio=2&neg=8&prod=8
 *        -> además de 8 tiendas, devuelve 8 PRODUCTOS de tiendas cercanas
 *           (clave "productos"), ordenados por la distancia de su tienda.
 *
 * Respuesta: { ok:true, total:N, negocios:[{...distancia_m...}], productos:[{...distancia_m...}] }
 * ("productos" solo aparece cuando se pide con prod=N; sin prod=N la respuesta
 *  es idéntica a la de siempre, para no romper tiendas-cerca-de-mi.html)
 *
 * Es cross-origin (CORS) a propósito: lo consume una página que puede abrirse
 * en el celular como archivo local. Sin APIs de terceros ni pago.
 */

require_once __DIR__ . '/../config.php';

// ---- CORS (para que la página local pueda leer el JSON) ----
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 86400');
header('Cache-Control: no-store, no-cache, must-revalidate');

// Preflight (opción simple cuando el navegador pregunta antes)
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$lat  = isset($_GET['lat'])  ? (float)$_GET['lat']  : null;
$lng  = isset($_GET['lng'])  ? (float)$_GET['lng']  : null;
$radio= isset($_GET['radio'])? (float)$_GET['radio']: 0.0;

// Opcionales (portada): cuántas tiendas y cuántos productos devolver.
$lim_neg  = isset($_GET['neg'])  ? (int)$_GET['neg']  : 2000;   // por defecto: todas (comportamiento de siempre)
$lim_prod = isset($_GET['prod']) ? (int)$_GET['prod'] : 0;      // 0 = no devolver productos
$lim_neg  = max(1, min(2000, $lim_neg));
$lim_prod = max(0, min(60, $lim_prod));

$tiene_ubicacion = ($lat !== null && $lng !== null
    && $lat >= -90 && $lat <= 90 && $lng >= -180 && $lng <= 180);

$radio_m = max(0.0, $radio) * 1000;

$pdo = db();

$sql = "SELECT n.id, n.nombre, n.slug, n.direccion, n.lat, n.lng, n.rating,
               c.nombre AS categoria_nombre, c.icono AS categoria_icono,
               d.nombre AS distrito_nombre,
               (SELECT f.ruta FROM directorio_fotos f
                 WHERE f.negocio_id = n.id ORDER BY f.orden ASC LIMIT 1) AS imagen_portada";
$params = [];

if ($tiene_ubicacion) {
    $sql .= ", ST_Distance_Sphere(POINT(n.lng, n.lat), POINT(?, ?)) AS distancia_m";
    $params = [$lng, $lat];
}

$sql .= " FROM directorio_negocios n
          LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
          LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
          WHERE n.estado = 'activo' AND n.lat IS NOT NULL AND n.lng IS NOT NULL";

if ($tiene_ubicacion) {
    if ($radio_m > 0) {
        $sql .= " AND ST_Distance_Sphere(POINT(n.lng, n.lat), POINT(?, ?)) <= ?";
        array_push($params, $lng, $lat, $radio_m);
    }
    $sql .= " ORDER BY distancia_m ASC";
} else {
    $sql .= " ORDER BY n.id ASC";
}
$sql .= " LIMIT " . $lim_neg;

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $filas = $stmt->fetchAll();

    // Redondear la distancia a metros (para no mandar float con muchos decimales).
    if ($tiene_ubicacion) {
        foreach ($filas as &$f) {
            $f['distancia_m'] = round((float)$f['distancia_m']);
        }
        unset($f);
    }

    // ---- Productos de las tiendas cercanas (opcional, pedido con prod=N) ----
    $productos = [];
    if ($lim_prod > 0) {
        try {
            $sqlp = "SELECT s.id, s.titulo, s.precio, s.unidad, s.imagen, s.destacado,
                            n.id AS negocio_id, n.nombre AS negocio_nombre, n.slug AS negocio_slug,
                            c.nombre AS categoria_nombre, c.icono AS categoria_icono,
                            d.nombre AS distrito_nombre,
                            (SELECT pf.ruta FROM directorio_producto_fotos pf
                              WHERE pf.producto_id = s.id ORDER BY pf.orden ASC LIMIT 1) AS foto";
            $pp = [];
            if ($tiene_ubicacion) {
                $sqlp .= ", ST_Distance_Sphere(POINT(n.lng, n.lat), POINT(?, ?)) AS distancia_m";
                $pp = [$lng, $lat];
            }
            $sqlp .= " FROM directorio_servicios s
                       JOIN directorio_negocios n ON n.id = s.negocio_id
                            AND n.estado = 'activo'
                            AND n.lat IS NOT NULL AND n.lng IS NOT NULL
                       LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                       LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
                       WHERE s.activo = 1 AND " . sql_producto_vigente('s');
            if ($tiene_ubicacion && $radio_m > 0) {
                $sqlp .= " AND ST_Distance_Sphere(POINT(n.lng, n.lat), POINT(?, ?)) <= ?";
                array_push($pp, $lng, $lat, $radio_m);
            }
            $sqlp .= $tiene_ubicacion
                ? " ORDER BY distancia_m ASC, s.destacado DESC, n.vistas_count DESC"
                : " ORDER BY s.destacado DESC, n.vistas_count DESC";
            // Se trae un poco más de la cuenta para poder VARIAR las tiendas
            // (que no salgan 8 productos de la misma tienda) y priorizar los que
            // tienen precio real (los "S/ 0.00" son servicios sin precio).
            $pool = max(24, min(60, $lim_prod * 5));
            $sqlp .= " LIMIT " . $pool;

            $stp = $pdo->prepare($sqlp);
            $stp->execute($pp);
            $candidatos = $stp->fetchAll();

            if ($tiene_ubicacion) {
                foreach ($candidatos as &$pr) {
                    $pr['distancia_m'] = round((float)$pr['distancia_m']);
                }
                unset($pr);
            }

            // 1) primero los productos con precio, 2) el resto (manteniendo el orden por distancia)
            $con_precio = [];
            $sin_precio = [];
            foreach ($candidatos as $pr) {
                if ((float)$pr['precio'] > 0) { $con_precio[] = $pr; }
                else                          { $sin_precio[] = $pr; }
            }

            // Máximo 2 productos por tienda, para que la fila se vea variada.
            $por_negocio = [];
            foreach (array_merge($con_precio, $sin_precio) as $pr) {
                if (count($productos) >= $lim_prod) break;
                $nid = (int)$pr['negocio_id'];
                $usados = $por_negocio[$nid] ?? 0;
                if ($usados >= 2) continue;
                $por_negocio[$nid] = $usados + 1;
                $productos[] = $pr;
            }
            // Si aún faltan (pocas tiendas), se completa sin la regla de variedad.
            if (count($productos) < $lim_prod) {
                foreach (array_merge($con_precio, $sin_precio) as $pr) {
                    if (count($productos) >= $lim_prod) break;
                    $ya = false;
                    foreach ($productos as $p2) { if ((int)$p2['id'] === (int)$pr['id']) { $ya = true; break; } }
                    if (!$ya) { $productos[] = $pr; }
                }
            }
        } catch (Exception $e) {
            // Si los productos fallan, igual devolvemos las tiendas (no romper la portada).
            $productos = [];
        }
    }

    // ---- Versiones de tamaño (srcset) para que el celular no baje la foto completa ----
    // El motor de imágenes (includes/imagenes.php) devuelve '' si la foto todavía
    // no tiene versiones de 300/800 px; en ese caso el JS usa el src normal.
    foreach ($filas as &$fn) {
        $fn['srcset'] = img_srcset($fn['imagen_portada'] ?? '');
        $fn['img300'] = img_url($fn['imagen_portada'] ?? '', IMG_ANCHO_MINI);
    }
    unset($fn);
    foreach ($productos as &$fp) {
        $ruta_p = ($fp['imagen'] ?? '') ?: ($fp['foto'] ?? '');
        $fp['srcset'] = img_srcset($ruta_p);
        $fp['img300'] = img_url($ruta_p, IMG_ANCHO_MINI);
    }
    unset($fp);

    // ---- 🧰 SERVICIOS A DOMICILIO (2026-09-10) ----
    // No tienen local: van a la casa del cliente. Van en su propia lista para que quien la use
    // los muestre con su etiqueta y las ZONAS donde atienden (no con una distancia engañosa).
    $domicilio = [];
    try {
        $domicilio = buscar_domicilio_en_zona($lat, $lng, $tiene_ubicacion ? max(1.0, $radio) : 0.0, '', '', '', 8);
        foreach ($domicilio as &$fd) {
            if ($fd['distancia_m'] !== null) { $fd['distancia_m'] = round((float)$fd['distancia_m']); }
            $fd['srcset'] = img_srcset($fd['imagen_portada'] ?? '');
            $fd['img300'] = img_url($fd['imagen_portada'] ?? '', IMG_ANCHO_MINI);
        }
        unset($fd);
    } catch (Throwable $e) {
        $domicilio = [];   // nunca romper la respuesta por esta lista extra
    }

    $salida = [
        'ok'      => true,
        'total'   => count($filas),
        'radio'   => $tiene_ubicacion ? $radio : null,
        'negocios'=> $filas,
        'total_domicilio' => count($domicilio),
        'domicilio' => $domicilio,
    ];
    if ($lim_prod > 0) {
        $salida['total_productos'] = count($productos);
        $salida['productos'] = $productos;
    }
    json_response($salida);
} catch (Exception $e) {
    // Silencioso (no exponer detalles); la página mostrará "sin datos".
    $err = [
        'ok' => false,
        'total' => 0,
        'negocios' => [],
        'error' => 'No se pudieron cargar las tiendas en este momento. Intenta más tarde.',
    ];
    if ($lim_prod > 0) { $err['productos'] = []; }
    json_response($err, 500);
}
