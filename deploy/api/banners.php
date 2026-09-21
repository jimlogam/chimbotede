<?php
/**
 * api/banners.php — API pública del módulo de publicidad.
 * =======================================================
 * Acciones:
 *   ?action=clic&id=N          Registra 1 clic sobre el banner N.
 *   ?action=resultados&q=TXT   Cuenta (SOLO LECTURA) cuántos negocios devolvería la
 *                              búsqueda del sitio para ese término, con la MISMA
 *                              condición de buscar.php (nombre o descripción). Lo usa el
 *                              panel del banner para no guardar un término que dejaría al
 *                              visitante en una página sin resultados.
 *   ?action=negocios&tema=X[&distrito=slug][&lat=..&lng=..&radio=auto|2|5|10|20|30]
 *                              Devuelve los negocios que resuelven la necesidad del
 *                              banner (modal) + lista de distritos para el <select>.
 *                              Con lat/lng añade el modo "cerca de mí": MISMO criterio
 *                              del tema, filtrado y ORDENADO por distancia (escalera
 *                              2 → 5 → 10 km hasta juntar 20, igual que buscar.php).
 *                              El visitante nunca pierde el criterio del banner.
 * Respuesta siempre JSON. Sin sesión requerida (público).
 */
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'clic':
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) banner_registrar_clic($id);
        json_response(['ok' => true]);

    case 'resultados':
        /* 🔎 Comprobación de un término de BÚSQUEDA de banner (2026-09-11).
           No busca por su cuenta: usa EXACTAMENTE la condición de buscar.php
           (`nombre LIKE %t% OR descripcion LIKE %t%` sobre negocios activos), para que el
           panel diga la verdad de lo que verá el visitante. Devuelve solo un número y
           hasta 3 nombres de ejemplo. */
        $q = trim(preg_replace('/\s+/u', ' ', (string)($_GET['q'] ?? '')));
        if (mb_strlen($q) < 2) {
            json_response(['ok' => true, 'q' => $q, 'total' => 0, 'ejemplos' => [], 'aviso' => 'término muy corto']);
        }
        $q    = mb_substr($q, 0, 60);
        $like = '%' . $q . '%';
        try {
            $st = db()->prepare("SELECT n.nombre FROM directorio_negocios n
                                 WHERE n.estado='activo' AND n.ubicacion_tipo <> 'domicilio'
                                   AND (n.nombre LIKE ? OR n.descripcion LIKE ?)
                                 ORDER BY n.vistas_count DESC, n.nombre ASC LIMIT 50");
            $st->execute([$like, $like]);
            $nombres = array_map(function ($f) { return $f['nombre']; }, $st->fetchAll());

            $st2 = db()->prepare("SELECT COUNT(*) FROM directorio_negocios n
                                  WHERE n.estado='activo' AND n.ubicacion_tipo = 'domicilio'
                                    AND (n.nombre LIKE ? OR n.descripcion LIKE ?)");
            $st2->execute([$like, $like]);
            $domicilios = (int)$st2->fetchColumn();
        } catch (Throwable $e) {
            json_response(['ok' => false, 'error' => 'No se pudo comprobar el término.'], 500);
        }
        json_response([
            'ok'         => true,
            'q'          => $q,
            'total'      => count($nombres) + $domicilios,   // negocios que verá el visitante
            'negocios'   => count($nombres),
            'domicilios' => $domicilios,
            'ejemplos'   => array_slice($nombres, 0, 3),
        ]);

    case 'negocios':
        $tema     = trim($_GET['tema'] ?? '');
        $distrito = trim($_GET['distrito'] ?? '');
        // El JS manda distrito='' cuando pide cercanía; si llegara la palabra 'cerca'
        // (enlace viejo o alguien probando a mano), se ignora como distrito.
        if ($distrito === 'cerca') $distrito = '';

        if ($tema === '') {
            json_response(['ok' => false, 'error' => 'Falta el tema.'], 400);
        }

        // Modo "cerca de mí" (2026-09-10): la cercanía se aplica ENCIMA del criterio
        // del banner, nunca en su lugar. Validación igual que buscar.php.
        $lat = (isset($_GET['lat']) && is_numeric($_GET['lat'])) ? (float)$_GET['lat'] : null;
        $lng = (isset($_GET['lng']) && is_numeric($_GET['lng'])) ? (float)$_GET['lng'] : null;
        if ($lat !== null && ($lat < -90  || $lat > 90))  $lat = null;
        if ($lng !== null && ($lng < -180 || $lng > 180)) $lng = null;

        // radio: vacío / 'auto' = escalera 2 → 5 → 10 km. Un número = ese radio exacto.
        $radio_raw  = trim((string)($_GET['radio'] ?? ''));
        $radio_fijo = null;
        if ($radio_raw !== '' && $radio_raw !== 'auto' && is_numeric($radio_raw)) {
            $radio_fijo = max(0.0, min(30.0, (float)$radio_raw));
        }

        $cfg   = banner_tema_cfg($tema);
        $cerca = null;

        if ($lat !== null && $lng !== null) {
            $res   = banners_resultados_tema_cerca($tema, $lat, $lng, $distrito, $radio_fijo);
            $rows  = $res['data'];
            $cerca = [
                'activo'    => true,
                'radio_km'  => $res['radio_km'],
                'completo'  => (bool)$res['completo'],
                'tope'      => (int)$res['tope'],
                'automatico'=> ($radio_fijo === null),
            ];
        } else {
            $rows = banners_resultados_tema($tema, $distrito);
        }

        $distritoSel = null;
        $distritos   = obtener_distritos_visibles();
        if ($distrito !== '') {
            foreach ($distritos as $d) {
                if ($d['slug'] === $distrito) { $distritoSel = ['id' => (int)$d['id'], 'nombre' => $d['nombre'], 'slug' => $d['slug']]; break; }
            }
        }

        json_response([
            'ok'        => true,
            'tema'      => [
                'nombre'  => $cfg['nombre'] ?? 'negocios',
                'frase'   => $cfg['frase'] ?? '',
                'frase_res' => $cfg['frase_res'] ?? 'Estos son los mejores resultados',
            ],
            'distrito'  => $distritoSel,
            'distritos' => $distritos,
            'cerca'     => $cerca,
            'total'     => count($rows),
            'data'      => $rows,
        ]);

    default:
        json_response(['ok' => false, 'error' => 'Acción no válida.'], 404);
}
