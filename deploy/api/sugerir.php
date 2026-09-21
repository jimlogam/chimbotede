<?php
/**
 * api/sugerir.php — Buscador del servidor (negocios + productos)
 * ============================================================
 * Lo usa el buscador fuzzy del navegador (assets/js/buscador_fuzzy.js) para:
 *   1. Dar sugerencias cuando el índice del celular todavía no está listo.
 *   2. Buscar PRODUCTOS: hay miles (9.400+), así que NO se descargan al celular;
 *      se buscan aquí y el navegador le pasa PISTAS que dedujo del texto aunque
 *      el usuario escriba con errores.
 *
 *   GET /api/sugerir.php?q=zapatiyas                 → búsqueda normal
 *   GET /api/sugerir.php?q=zapatiyas&tiendas=a,b,c   → + productos de esas tiendas
 *                                                      (slugs que el buscador fuzzy
 *                                                       del navegador reconoció)
 *   GET /api/sugerir.php?q=zapatiyas&rubro=Calzado   → + productos de ese rubro
 *   GET /api/sugerir.php?q=zapatillas&distrito=...   → + productos de ese distrito
 *
 * Respuesta JSON: {q, negocios:[…], productos:[…], pistas:{…}, rubro_clave:null|{…}}
 * Compatible con lo de antes: sin las pistas nuevas, responde como siempre.
 * 🆕 `rubro_clave` (2026-09-18): cuando el texto no nombra a ninguna tienda pero SÍ es una palabra del
 *    rubro («clavos», «niños hiperactivos», «clases a domicilio»), trae el rubro al que apunta, la palabra
 *    que lo trajo, cuántas tiendas tiene y **hasta 6 de ellas** en `negocios` — para que la barra de
 *    escribir nunca diga «nada» si la página de resultados sí sabe responder.
 */

require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$q = trim((string)($_GET['q'] ?? ''));

// 🧹 LA LIMPIEZA DE LA BÚSQUEDA (mando del jefe, 2026-09-14): «comprar clavos» → «clavos».
// El motor viene de config.php → includes/helpers.php → includes/busqueda_limpieza.php. Si por lo que
// fuera no estuviera, se busca como siempre (nunca peor que antes).
if ($q !== '' && function_exists('busqueda_limpiar')) {
    $q_limpio = busqueda_limpiar($q);
    if ($q_limpio !== '') $q = $q_limpio;
}

// Pistas que manda el buscador fuzzy del navegador (texto ya "entendido")
$pista_tiendas = trim((string)($_GET['tiendas'] ?? ''));
$pista_rubro   = mb_substr(trim((string)($_GET['rubro'] ?? '')), 0, 60);
$pista_dist    = mb_substr(trim((string)($_GET['distrito'] ?? '')), 0, 60);

if (mb_strlen($q) < 2) {
    json_response(['q' => $q, 'negocios' => [], 'productos' => [],
                   'pistas' => ['tiendas' => '', 'rubro' => '', 'distrito' => '']]);
}

$pdo = db();
$salida = [
    'q' => $q,
    'negocios' => [],
    'productos' => [],
    'pistas' => ['tiendas' => $pista_tiendas, 'rubro' => $pista_rubro, 'distrito' => $pista_dist],
];

// El buscador del servidor también piensa por PALABRAS ("polleria chimbot").
$tokens = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower($q), -1, PREG_SPLIT_NO_EMPTY) ?: [];
$tokens = array_values(array_filter($tokens, function ($t) { return mb_strlen($t) >= 2; }));
$tokens = array_slice($tokens, 0, 4);

// Slugs de tienda que el navegador reconoció (máx. 8, saneados)
$slugs = [];
foreach (explode(',', $pista_tiendas) as $s) {
    $s = trim($s);
    if ($s !== '' && preg_match('/^[a-z0-9_\-]{1,80}$/i', $s)) {
        $slugs[] = $s;
    }
}
$slugs = array_slice(array_unique($slugs), 0, 8);

try {
    // ------------------------------------------------------------- NEGOCIOS
    // Igual que siempre (nombre, rubro o distrito) pero por palabras.
    $sql = "SELECT n.id, n.nombre, n.slug,
                   c.nombre AS categoria_nombre, d.nombre AS distrito_nombre
            FROM directorio_negocios n
            LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
            LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
            WHERE n.estado = 'activo'";
    $par = [];
    foreach ($tokens as $t) {
        $sql .= " AND (n.nombre LIKE ? OR c.nombre LIKE ? OR d.nombre LIKE ?)";
        $like = '%' . $t . '%';
        array_push($par, $like, $like, $like);
    }
    if (!$tokens) {
        $sql .= " AND (n.nombre LIKE ? OR c.nombre LIKE ? OR d.nombre LIKE ?)";
        $like = '%' . $q . '%';
        array_push($par, $like, $like, $like);
    }
    $sql .= " ORDER BY n.vistas_count DESC LIMIT 6";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($par);
    $salida['negocios'] = $stmt->fetchAll();
} catch (Exception $e) {
    // silencioso: el buscador del navegador ya tiene sus propios negocios
}

// 🔢 EL NÚMERO DE TELÉFONO (2026-09-19) — el mismo caso que en `buscar.php`.
// ================================================================================================
// En las búsquedas reales (`directorio_busquedas`) hay gente que escribe un número —«931103286»,
// «976940121»— porque quiere **la tienda de ese número**. El buscador solo miraba nombre, rubro y
// distrito, así que la barra no ofrecía nada. Aquí se buscan las tiendas por su WhatsApp, su teléfono
// o sus teléfonos secundarios (`directorio_negocio_telefonos`) y se devuelven como negocios.
// ⚠️ Solo cuando lo escrito son CASI PURO DÍGITOS (6 o más) y el LIKE no encontró tiendas: no cambia
//    ninguna búsqueda de texto.
$tel_digitos = preg_replace('/\D+/', '', (string)$q);
if (count($salida['negocios']) < 2 && mb_strlen($tel_digitos) >= 6
    && mb_strlen($tel_digitos) >= (mb_strlen((string)$q) * 0.6)) {
    try {
        $sub_tel = "OR EXISTS (SELECT 1 FROM directorio_negocio_telefonos t
                                 WHERE t.negocio_id = n.id
                                   AND REPLACE(REPLACE(REPLACE(COALESCE(t.numero,''),' ',''),'-',''),'+','') LIKE ?)";
        $sql_tel = "SELECT n.id, n.nombre, n.slug,
                           c.nombre AS categoria_nombre, d.nombre AS distrito_nombre
                      FROM directorio_negocios n
                      LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                      LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
                     WHERE n.estado = 'activo'
                       AND (REPLACE(REPLACE(REPLACE(COALESCE(n.whatsapp,''),' ',''),'-',''),'+','') LIKE ?
                         OR REPLACE(REPLACE(REPLACE(COALESCE(n.telefono,''),' ',''),'-',''),'+','') LIKE ?
                         %s)
                     ORDER BY n.vistas_count DESC LIMIT 6";
        $par_tel = ['%' . $tel_digitos . '%', '%' . $tel_digitos . '%'];
        try {
            $st = $pdo->prepare(sprintf($sql_tel, $sub_tel));
            $st->execute(array_merge($par_tel, ['%' . $tel_digitos . '%']));
            $por_tel = $st->fetchAll();
        } catch (Throwable $e) {
            $st = $pdo->prepare(sprintf($sql_tel, ''));
            $st->execute($par_tel);
            $por_tel = $st->fetchAll();
        }
        if ($por_tel) {
            $salida['negocios'] = $por_tel;
            $salida['telefono'] = $tel_digitos;   // la barra puede decir que lo entendió como número
        }
    } catch (Throwable $e) {
        // silencioso: sin teléfono, el desplegable se comporta como antes
    }
}

/**
 * 🏷️ EL RUBRO POR LAS «FRASES DE UNIÓN» (2026-09-18 — guía `GUIA_BUSQUEDAS_POR_RUBRO_Y_SINONIMOS.md` §5).
 * ================================================================================================
 * «niños hiperactivos», «clases a domicilio», «fiesta de cachimbos» o «clavos» NO están escritos en el
 * nombre, el rubro ni el distrito de ninguna tienda, así que el LIKE de arriba devuelve 0 y la barra de
 * escribir decía «nada»… mientras `buscar.php` SÍ resolvía esas palabras, porque lee la tabla
 * `directorio_categoria_claves` (las «frases de unión» de cada rubro). Aquí se hace lo mismo:
 *
 *   1. Se pregunta a qué rubro apuntan las palabras (`categoria_por_clave_texto()`, la MISMA función que
 *      usa la página de resultados y el chat: una sola verdad, sin listas paralelas).
 *   2. Se devuelven **hasta 6 tiendas de ese rubro** ya listas en `rubro_clave.negocios`, marcadas con el
 *      rubro y la palabra que las trajo, para que el desplegable pueda decir «Educación / Academias por
 *      «clases a domicilio»» y el clic lleve al rubro completo.
 *   3. Si el texto no encontró NINGUNA tienda, esas mismas tiendas van también en `negocios`: así el
 *      desplegable viejo (o una página con el JS en caché) ya se comporta bien sin tocar nada más.
 *
 * ⚠️ Es la misma decisión que en `buscar.php`: la escalera manda (nombre → descripción → rubro) y el rubro
 *    es el ÚLTIMO recurso. Por eso solo se calcula cuando el LIKE no encontró tiendas (0 o 1: una sola
 *    coincidencia floja tampoco es respuesta) — nunca por delante del nombre.
 * ⚠️ Coste: leer las claves (4 232 filas) en cada tecla. Por eso se salta en el caso normal (el texto SÍ
 *    encuentra tiendas), que es el 99 % de lo que se escribe en la barra.
 */
$salida['rubro_clave'] = null;
if (count($salida['negocios']) < 2 && $q !== '' && function_exists('categoria_por_clave_texto')) {
    try {
        $palabra_rubro = $q;
        $rubro_clave   = categoria_por_clave_texto($q);
        if (!$rubro_clave) {
            // Palabra por palabra, la más larga primero (es la que más dice): «reforzamiento escolar».
            $largos = $tokens;
            usort($largos, function ($a, $b) { return mb_strlen($b) <=> mb_strlen($a); });
            foreach ($largos as $t) {
                if (mb_strlen($t) < 4) continue;
                $rubro_clave = categoria_por_clave_texto($t);
                if ($rubro_clave) { $palabra_rubro = $t; break; }
            }
        }
        if ($rubro_clave) {
            [$cf, $cp] = rubro_filtro_id((int)$rubro_clave['id'], 'n');
            $stmt = $pdo->prepare("SELECT n.id, n.nombre, n.slug,
                                          c.nombre AS categoria_nombre, d.nombre AS distrito_nombre
                                     FROM directorio_negocios n
                                     LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                                     LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
                                    WHERE n.estado = 'activo' AND $cf
                                    ORDER BY n.vistas_count DESC LIMIT 6");
            $stmt->execute($cp);
            $del_rubro = $stmt->fetchAll();

            if ($del_rubro) {
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM directorio_negocios n WHERE n.estado = 'activo' AND $cf");
                $stmt->execute($cp);
                $salida['rubro_clave'] = [
                    'id'       => (int)$rubro_clave['id'],
                    'nombre'   => (string)$rubro_clave['nombre'],
                    'slug'     => (string)$rubro_clave['slug'],
                    'clave'    => $palabra_rubro,   // la palabra que llevó al rubro (se muestra al visitante)
                    'tiendas'  => (int)$stmt->fetchColumn(),
                    'negocios' => $del_rubro,
                ];
                if (!$salida['negocios']) $salida['negocios'] = $del_rubro;
                // 🏷️ El visitante escribió una palabra DEL RUBRO, no un nombre: las pistas que mandó el
                // navegador salieron de coincidencias flojas («clases» → cualquier cosa con «clase»), así
                // que se dejan de lado y los productos se buscan en el rubro que esas palabras nombran.
                $slugs = [];
                $pista_rubro = (string)$rubro_clave['nombre'];
            }
        }
    } catch (Throwable $e) {
        // silencioso: sin rubro, el desplegable se comporta como antes (nunca peor)
    }
}

try {
    // ------------------------------------------------------------- PRODUCTOS
    // Se busca por el título del producto, por el rubro de su tienda y por el
    // nombre de la tienda; y además con las PISTAS (tiendas/rubro/distrito) que
    // el navegador dedujo aunque el texto esté mal escrito.
    $cond = [];
    $par  = [];

    if ($tokens) {
        $grupo = [];
        foreach ($tokens as $t) {
            $grupo[] = "(s.titulo LIKE ? OR c.nombre LIKE ? OR n.nombre LIKE ?)";
            $like = '%' . $t . '%';
            array_push($par, $like, $like, $like);
        }
        $cond[] = '(' . implode(' AND ', $grupo) . ')';
    }

    if ($slugs) {
        $cond[] = 'n.slug IN (' . implode(',', array_fill(0, count($slugs), '?')) . ')';
        foreach ($slugs as $s) {
            $par[] = $s;
        }
    }
    if ($pista_rubro !== '') {
        $cond[] = 'c.nombre = ?';
        $par[] = $pista_rubro;
    }
    if ($pista_dist !== '') {
        $cond[] = 'd.nombre = ?';
        $par[] = $pista_dist;
    }

    if ($cond) {
        // Orden: primero lo que coincide con lo escrito en el TÍTULO, después lo
        // que viene de una tienda reconocida, y al final lo más visto.
        $sqlp = "SELECT s.id, s.titulo, s.precio, s.unidad, s.destacado,
                        n.nombre AS negocio_nombre, n.slug AS negocio_slug,
                        c.nombre AS categoria_nombre, c.icono AS categoria_icono,
                        d.nombre AS distrito_nombre
                 FROM directorio_servicios s
                 JOIN directorio_negocios n ON n.id = s.negocio_id AND n.estado = 'activo'
                 LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                 LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
                 WHERE s.activo = 1 AND " . sql_producto_vigente('s') . " AND (" . implode(' OR ', $cond) . ")
                 ORDER BY CASE WHEN s.titulo LIKE ? THEN 0
                               WHEN s.titulo LIKE ? THEN 1
                               ELSE 2 END,
                          s.destacado DESC, n.vistas_count DESC
                 LIMIT 8";
        $par[] = '%' . $q . '%';
        $par[] = '%' . ($tokens ? $tokens[0] : $q) . '%';

        $stmtp = $pdo->prepare($sqlp);
        $stmtp->execute($par);
        $salida['productos'] = $stmtp->fetchAll();
    }
} catch (Exception $e) {
    // silencioso: si falla, el buscador sigue mostrando negocios
}

json_response($salida);
