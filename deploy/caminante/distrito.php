<?php
/**
 * caminante/distrito.php — ¿A qué distrito pertenece esta ubicación?
 * =====================================================================================
 * Pedido del jefe (2026-09-10, tarde):
 *   *"Si ya estamos pidiendo la ubicación, ¿por qué tenemos que marcar distrito? Se supone que
 *   eso lo podemos calcular automáticamente… solo con poner la ubicación ya deberíamos saber a
 *   qué distrito agregarlo."*
 *
 * CÓMO LO CALCULA (sin depender de nadie de fuera):
 *   Usa NUESTROS propios datos. En la base hay **1.536 negocios con coordenadas y su distrito**;
 *   se toman los **30 más cercanos** a la ubicación capturada (distancia real, fórmula de
 *   haversine calculada por la propia base) y se vota por distrito **pesando por cercanía**
 *   (peso = 1 / (distancia + 0,05)²): el negocio que está a 40 m cuenta muchísimo más que el que
 *   está a 2 km. Gana el distrito con más peso.
 *
 * POR QUÉ ASÍ:
 *   - No se llama a OpenStreetMap: evita depender de un servicio externo (límites de uso, sin
 *     internet no responde) y usa información REAL de la ciudad, que es más fiable para
 *     distinguir Chimbote de Nuevo Chimbote (los separa el río).
 *   - Si la ubicación está lejos de todo (más de 6 km del negocio más cercano) o el voto sale
 *     muy repartido (menos del 35 % del peso), se marca `dudoso:true` y la app **enseña los
 *     botones** para que la persona elija: el sistema propone, la persona confirma.
 *
 * RESPUESTA (JSON):
 *   {ok, distrito_id, nombre, confianza (0-1), distancia_km, vecinos, dudoso}
 *   {ok:false, error:'sin_coords'|'sin_datos'|'server_error'}
 *
 * NO escribe nada en la base: es de solo lectura y se puede llamar todas las veces que haga falta.
 */
require_once __DIR__ . '/../config.php'; // config + helpers del sitio

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$lat = isset($_GET['lat']) ? (float)$_GET['lat'] : 0.0;
$lng = isset($_GET['lng']) ? (float)$_GET['lng'] : 0.0;

if ($lat === 0.0 || $lng === 0.0 || $lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
    echo json_encode(['ok' => false, 'error' => 'sin_coords'], JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $pdo = db();

    // Distritos que el sitio muestra (los mismos que ofrece sesion.php).
    $distritos = [];
    foreach (obtener_distritos_visibles() as $d) {
        $distritos[(int)$d['id']] = (string)$d['nombre'];
    }
    if (!$distritos) {
        echo json_encode(['ok' => false, 'error' => 'sin_datos'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /* Los 30 negocios ACTIVOS con coordenadas más cercanos. La distancia se calcula en la misma
       consulta (haversine, en km). Son ~1.470 filas: es instantáneo, no hace falta ningún índice.
       ⚠️ Solo negocios ACTIVOS: los que están "pendiente" pueden traer coordenadas mal capturadas
       (comprobado el 2026-09-10: hay 3 negocios con coordenadas de LIMA registrados como
       "Nuevo Chimbote"; con datos sucios, el detector diría "Nuevo Chimbote" para una captura
       hecha en Lima).
       ⚠️ OJO CON LOS PARÁMETROS: el sitio usa PDO con prepares NATIVOS, y ahí un mismo
       parámetro con nombre NO se puede repetir (`:la` dos veces → "Invalid parameter number",
       error que salió al probar esto el 2026-09-10). Por eso van `:la1`, `:la2` y `:lo1`. */
    $sql = "SELECT distrito_id,
                   (6371 * ACOS(LEAST(1,
                       COS(RADIANS(:la1)) * COS(RADIANS(lat)) * COS(RADIANS(lng) - RADIANS(:lo1))
                       + SIN(RADIANS(:la2)) * SIN(RADIANS(lat))
                   ))) AS d
            FROM directorio_negocios
            WHERE estado = 'activo'
              AND lat IS NOT NULL AND lng IS NOT NULL
              AND lat <> 0 AND lng <> 0
              AND distrito_id IS NOT NULL
            ORDER BY d ASC
            LIMIT 30";
    $st = $pdo->prepare($sql);
    $st->execute([':la1' => $lat, ':la2' => $lat, ':lo1' => $lng]);
    $filas = $st->fetchAll(PDO::FETCH_ASSOC);

    if (!$filas) {
        echo json_encode(['ok' => false, 'error' => 'sin_datos'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Voto pesado por cercanía.
    $pesos = [];
    $minD  = null;
    $n     = 0;
    foreach ($filas as $f) {
        $id = (int)$f['distrito_id'];
        if (!isset($distritos[$id])) { continue; }   // distrito oculto: no se propone
        $d = (float)$f['d'];
        if ($minD === null || $d < $minD) { $minD = $d; }
        $pesos[$id] = ($pesos[$id] ?? 0.0) + 1.0 / pow($d + 0.05, 2);
        $n++;
    }
    if (!$pesos) {
        echo json_encode(['ok' => false, 'error' => 'sin_datos'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    arsort($pesos);
    $total   = array_sum($pesos);
    $ganador = (int)array_key_first($pesos);
    $conf    = $total > 0 ? ($pesos[$ganador] / $total) : 0.0;

    // ¿Es de fiar? Si el negocio conocido más cercano está a más de 3 km, o el voto sale muy
    // repartido, NO se da por seguro: la app propone el distrito pero pide confirmarlo.
    $dudoso = ($minD > 3.0) || ($conf < 0.6);

    echo json_encode([
        'ok'           => true,
        'distrito_id'  => $ganador,
        'nombre'       => $distritos[$ganador],
        'confianza'    => round($conf, 3),
        'distancia_km' => round((float)$minD, 3),
        'vecinos'      => $n,
        'dudoso'       => $dudoso,
    ], JSON_UNESCAPED_UNICODE);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'server_error', 'msg' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
