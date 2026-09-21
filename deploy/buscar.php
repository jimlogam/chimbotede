<?php
/**
 * buscar.php — RESULTADOS DE BÚSQUEDA (móvil primero)
 * ===================================================
 * Pedido del jefe (2026-09-10): la página cargaba con demasiado relleno en el celular
 * (título grande + widget de ubicación de 233 px + formulario de filtros + línea de
 * "N negocios encontrados" antes del PRIMER resultado). Ahora:
 *
 *   1. Los 4 resultados más notorios salen ARRIBA, junto al título compacto.
 *   2. DESPUÉS de esos 4 va el bloque "ver negocios cerca".
 *   3. El rubro se elige en un desplegable que dice "Categorías" y los distritos se ven
 *      todos como botones (son solo 4); el botón "Buscar" sobraba (se aplica al tocar).
 *   4. Tarjetas de 2 columnas en el celular (antes 1).
 *   5. El micrófono 🎙️ vive SOLO en el buscador de la barra superior (el del formulario
 *      de filtros se quitó junto con el campo de texto duplicado).
 *   6. "Ver negocios cerca" arranca en 2 km y sube solo 2 → 5 → 10 km hasta juntar 20
 *      resultados; con 10 km se detiene y el usuario decide con "Ampliar búsqueda".
 *   7. 📊 Las ESTADÍSTICAS de la búsqueda (la ficha del término, 2026-09-13) van AL FINAL,
 *      cuando ya se vieron todos los resultados (orden del jefe, 2026-09-14); arriba queda solo
 *      una línea-teaser que baja hasta ellas con el ancla #estadisticas.
 *
 * Todo lo demás (fuzzy, voz, avisos al jefe, historial de búsquedas) sigue igual.
 */
require_once __DIR__ . '/config.php';

// 🎨 El botón "Ver … cerca de mí" (naranja desde el 2026-09-14) se arma ANTES de la cabecera
// (el bloque se guarda con ob_start/ob_get_clean, líneas ~459-507), así que hay que cargar
// includes/btn_cerca.php aquí: si no, `cerca_pin_svg()` no existiría todavía (error fatal).
// Ese archivo solo define funciones: no imprime nada. Detalle: guía §6.4.
require_once __DIR__ . '/includes/btn_cerca.php';

// ====== 🧹 LA LIMPIEZA DE LA BÚSQUEDA (mando del jefe, 2026-09-14) ======
// Textual: *«la palabra comprar debería ser filtrada de los resultados de búsqueda… el buscador debe
// saber filtrar palabras que simplemente acompañan una búsqueda pero no son parte de la búsqueda…
// "dónde hay cerveza" debe entregar resultados de cerveza y no de "dónde hay"»*.
//
// «comprar», «dónde hay», «quién tiene», «buscar», «sabes que quiero»… son MANDOS (la forma de pedir),
// no parte de la búsqueda. Se quitan ANTES de buscar: `comprar clavos` busca **clavos** y `dónde hay
// cerveza` busca **cerveza**. Motor y diccionario: `includes/busqueda_limpieza.php` (el mismo que usa
// el navegador, así el desplegable y esta página limpian igual).
$q_crudo  = trim((string)($_GET['q'] ?? ''));
$limpieza = busqueda_limpiar_info($q_crudo);
$termino  = (string)$limpieza['limpio'];

// Lo que el visitante ESCRIBIÓ: viene en `qo` cuando el navegador ya limpió el campo, o se deduce de
// `q`. Solo sirve para explicarle, en una línea, lo que se entendió.
$q_escrito = trim((string)($_GET['qo'] ?? ''));
if ($q_escrito === '' && $limpieza['cambio']) $q_escrito = (string)$limpieza['base'];

// 🚪 LA PUERTA DE ATRÁS: con `&literal=1` se busca la frase TAL CUAL (sin limpiar). Es el enlace
// «buscar «comprar clavos» tal cual» de la nota: el visitante siempre puede mandar él.
$literal = !empty($_GET['literal']);
if ($literal) { $termino = $q_crudo; $q_escrito = ''; }

$categoria_slug = $_GET['cat'] ?? '';

// ====== DISTRITOS: botones de filtro que se combinan (2026-09-10, pedido del jefe) ======
// Cada botón se ENCIENDE y se APAGA. Sin ninguno encendido se ven TODOS los distritos (por eso ya no
// existe el botón "Todos"). Con dos encendidos (ej. Chimbote + Coishco) salen los negocios de ambos.
// Se acepta `dist[]=chimbote&dist[]=coishco` y también el `dist=chimbote` de los enlaces viejos.
$distritos_sel = [];
$dist_raw = $_GET['dist'] ?? [];
foreach ((is_array($dist_raw) ? $dist_raw : [$dist_raw]) as $d) {
    $d = trim((string)$d);
    if ($d !== '' && preg_match('/^[a-z0-9\-]+$/', $d)) $distritos_sel[] = $d;
}
$distritos_sel = array_values(array_unique($distritos_sel));

// ====== Modo "cerca de mí": lat/lng llega del navegador (gratis) ======
$lat = (isset($_GET['lat']) && is_numeric($_GET['lat'])) ? (float)$_GET['lat'] : null;
$lng = (isset($_GET['lng']) && is_numeric($_GET['lng'])) ? (float)$_GET['lng'] : null;
if ($lat !== null && ($lat < -90 || $lat > 90))   $lat = null;
if ($lng !== null && ($lng < -180 || $lng > 180)) $lng = null;
$modo_cerca = ($lat !== null && $lng !== null);

// ====== ESCALERA AUTOMÁTICA del botón "Ver negocios cerca" (pedido del jefe) ======
// Arranca en 2 km; si no junta 20 resultados sube solo a 5 km y, en el peor caso, a 10 km.
// Si con 2 km ya hay 20 resultados, NO sube. De 10 km no pasa solo: para eso está
// "Ampliar búsqueda", que muestra todos los radios (2/5/10/20/30 km) y ahí manda el usuario.
$CERCA_ESCALERA = [2, 5, 10];           // radios que prueba él solo, en este orden
$CERCA_TOPE     = 20;                   // resultados que intenta juntar
$CERCA_RADIOS   = [2, 5, 10, 20, 30];   // los que se ofrecen en "Ampliar búsqueda"

$radio_raw  = trim((string)($_GET['radio'] ?? ''));
// Sin radio (o "auto", o el viejo 0 = "sin límite") → escalera automática. Un número → ese radio exacto.
$radio_auto = ($modo_cerca && ($radio_raw === '' || $radio_raw === 'auto' || $radio_raw === '0'));
$radio      = ($radio_raw !== '' && is_numeric($radio_raw)) ? max(0.0, min(30.0, (float)$radio_raw)) : 5.0;

// Resolver IDs a partir de slugs
$categoria_id = 0;
if ($categoria_slug) {
    $stmt = db()->prepare("SELECT id FROM directorio_categorias WHERE slug = ? LIMIT 1");
    $stmt->execute([$categoria_slug]);
    $categoria_id = (int)($stmt->fetchColumn() ?: 0);
    // Slug que ya no existe (enlace viejo): se descarta, para no arrastrarlo en los filtros.
    if (!$categoria_id) $categoria_slug = '';
}

// IDs de los distritos encendidos (validados como slug y pasados por prepared statement)
$distrito_ids = [];
if ($distritos_sel) {
    $marcas = implode(',', array_fill(0, count($distritos_sel), '?'));
    $stmt = db()->prepare("SELECT id FROM directorio_distritos WHERE slug IN ($marcas)");
    $stmt->execute($distritos_sel);
    $distrito_ids = array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
}

// Guardar en historial si hay usuario logueado
guardar_busqueda_usuario($termino, $categoria_id ?: null, $distrito_ids[0] ?? null);

// ====== Resultados ======
$cerca_completo = false;   // ¿la escalera juntó los 20 resultados?

// Marcas de lo que hubo que hacer para encontrar algo: las usan las notas de la página y la 📊 búsqueda
// detallada. Se declaran aquí arriba para que existan SIEMPRE (también en el modo «cerca de mí»).
$busqueda_por_rubro = null;   // la palabra resultó ser una «frase de unión» de un rubro
$busqueda_jerga     = null;   // hubo que traducir la jerga local («puchitos» → «cigarrillos»)
$rubro_acompana     = null;   // el texto dio poquitas tiendas: se ofrece además su rubro (5️⃣)
$busqueda_telefono  = null;   // lo que se escribió era un NÚMERO de teléfono (🔢, 2026-09-19)
$busqueda_producto  = [];     // lo escrito coincidió con el TÍTULO de productos (📦, 2026-09-20)
$ids_por_producto   = [];     // las tiendas que entraron a los resultados por sus productos (2026-09-20)
$ids_nombre_completo = [];    // tiendas cuyo NOMBRE trae todas las palabras escritas (2026-09-20)
$busqueda_domicilio = false;  // el texto solo coincidió con tiendas «a domicilio» (2️⃣bis, 2026-09-19)
$total_clasico      = 0;      // 🔢 cuántas tiendas encontró la búsqueda DE VERDAD (la lista corta en 24)

if ($modo_cerca && $radio_auto) {
    // UNA sola consulta al radio mayor de la escalera: los 20 más cercanos dentro de 10 km
    // son EXACTAMENTE los mismos que saldrían probando 2, luego 5 y luego 10 km
    // (misma verdad, un solo viaje a la base de datos).
    $resultados = buscar_cerca_de($lat, $lng, (float)end($CERCA_ESCALERA), $termino, $categoria_slug, $distritos_sel, $CERCA_TOPE);

    $dist_max = 0.0;
    foreach ($resultados as $r) {
        $dist_max = max($dist_max, (float)($r['distancia_m'] ?? 0));
    }
    $cerca_completo = (count($resultados) >= $CERCA_TOPE);
    $radio = (float)end($CERCA_ESCALERA);       // si no juntó 20, la búsqueda llegó hasta el tope
    if ($cerca_completo) {
        // El primer escalón que explica TODOS los resultados: eso es lo que se anuncia.
        foreach ($CERCA_ESCALERA as $rk) {
            if ($dist_max <= ($rk * 1000) + 1) { $radio = (float)$rk; break; }
        }
    }
} elseif ($modo_cerca) {
    // Radio elegido a mano (chips de la portada, "Ampliar búsqueda", enlaces viejos).
    $resultados     = buscar_cerca_de($lat, $lng, $radio, $termino, $categoria_slug, $distritos_sel, $CERCA_TOPE);
    $cerca_completo = (count($resultados) >= $CERCA_TOPE);
} else {
    // Búsqueda clásica por texto / rubro / distrito.
    // Se arma dentro de una función para poder repetirla con OTRA condición (ver el 🏷️ de abajo).
    //   · $rubro_extra  → condición extra de rubro (la usan las «frases de unión»).
    //   · $texto        → con qué se busca (por defecto, el término ya LIMPIO). '' = sin texto.
    //   · $por_palabras → true = exige que aparezcan TODAS las palabras y no la frase entera. Es el
    //                     2.º intento: «la casa del pollo» (limpiado «casa pollo») no está escrita
    //                     así en ninguna ficha, pero «casa» + «pollo» sí.
    //   · $con_domicilio → 🧰 true = **también** los servicios a domicilio. Solo lo usa el BLOQUE DEL
    //                      RUBRO (4️⃣, 4️⃣bis y 5️⃣): hay rubros cuyas tiendas son TODAS a domicilio
    //                      (Animación Infantil tiene 2, ambas `domicilio`), y al excluirlas el bloque
    //                      salía VACÍO y la búsqueda terminaba en blanco igual. Medido el 2026-09-19 con
    //                      «pintacaritas»: la clave existía, pero no había a quién mostrar.
    //   · $contar → 🆕 2026-09-19: corre la MISMA consulta pero devolviendo **CUÁNTAS tiendas hay** (una
    //                cifra, sin orden ni tope) en vez de las filas. Lo usa el aviso «hay N más» de abajo:
    //                la lista corta en `POR_PAGINA_BUSCADOR` (24) y hasta hoy no lo decía en ninguna parte.
    // 📋 Las columnas de una TIENDA, en un solo sitio: las usan la búsqueda de tiendas y la nueva
    // búsqueda por productos (2️⃣ter), para que la tarjeta salga igual por donde salga.
    $cols_negocio = "n.id, n.nombre, n.slug, n.direccion, n.rating, n.vistas_count,
                       n.lat, n.lng, n.ubicacion_tipo,
                       c.nombre AS categoria_nombre, c.icono AS categoria_icono, c.slug AS categoria_slug,
                       d.nombre AS distrito_nombre,
                       (SELECT f.ruta FROM directorio_fotos f WHERE f.negocio_id = n.id ORDER BY f.orden ASC LIMIT 1) AS imagen_portada";
    $buscar_clasica = function (array $rubro_extra = [], $texto = null, $por_palabras = false, $con_domicilio = false, $contar = false) use ($termino, $categoria_id, $distrito_ids, $cols_negocio) {
        $t = ($texto === null) ? $termino : (string)$texto;
        // `c.slug` se pide desde el 2026-09-19 para poder decir «mira todas las de <rubro>».
        $sql = $contar
            ? "SELECT COUNT(*)"
            : "SELECT $cols_negocio";
        $sql .= "
                FROM directorio_negocios n
                LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                LEFT JOIN directorio_distritos d ON d.id = n.distrito_id
                WHERE n.estado = 'activo'";
        if (!$con_domicilio) {
            $sql .= " AND n.ubicacion_tipo <> 'domicilio'";   // 🧰 los servicios a domicilio van en su propio bloque (abajo)
        }
        $params = [];

        if ($t !== '') {
            if ($por_palabras) {
                $toks = busqueda_tokens($t);
                if (!$toks) $toks = [$t];
                foreach ($toks as $tok) {
                    $sql .= " AND (n.nombre LIKE ? OR n.descripcion LIKE ?)";
                    $params[] = '%' . $tok . '%';
                    $params[] = '%' . $tok . '%';
                }
            } else {
                $sql .= " AND (n.nombre LIKE ? OR n.descripcion LIKE ?)";
                $params[] = "%$t%";
                $params[] = "%$t%";
            }
        }
        if ($categoria_id) {
            // 🏷️ Rubros múltiples: la tienda también sale en sus rubros extra (2026-09-12).
            [$cf, $cp] = rubro_filtro_id($categoria_id, 'n');
            $sql .= " AND " . $cf;
            foreach ($cp as $p) $params[] = $p;
        }
        if ($distrito_ids) {
            $ids = implode(',', $distrito_ids);
            // 🧰 Un servicio a domicilio también entra si ATIENDE en ese distrito (cobertura),
            // aunque su base esté en otro (tabla directorio_negocio_cobertura, 2026-09-10).
            $sql .= " AND (n.distrito_id IN ($ids)
                           OR EXISTS (SELECT 1 FROM directorio_negocio_cobertura cc
                                       WHERE cc.negocio_id = n.id AND cc.distrito_id IN ($ids)))";
        }
        if ($rubro_extra) {
            $sql .= " AND " . $rubro_extra[0];
            foreach ((array)($rubro_extra[1] ?? []) as $p) $params[] = $p;
        }
        // 🔢 Si solo se quiere el NÚMERO: se corta aquí, sin orden ni tope.
        if ($contar) {
            $stmt = db()->prepare($sql);
            $stmt->execute($params);
            return (int)$stmt->fetchColumn();
        }

        $sql .= " ORDER BY n.vistas_count DESC, n.rating DESC LIMIT " . POR_PAGINA_BUSCADOR;

        $stmt = db()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    };

    $resultados = $buscar_clasica();

    // 🔢 CUÁNTAS HAY CON ESTA BÚSQUEDA, DE VERDAD (2026-09-19). La lista muestra como máximo
    // `POR_PAGINA_BUSCADOR` (24) y la página no lo decía: el visitante que buscaba «bodega» veía 24
    // tarjetas y daba por hecho que eso era todo (el jefe cazó lo mismo en los rubros: decía «12
    // negocios» cuando Bodegas tiene 208). Abajo, con este número, se avisa y se ofrece verlas todas.
    $total_clasico = (int)$buscar_clasica([], null, false, false, true);

    // 🧰 LAS TIENDAS DE UN RUBRO, CON SUS SERVICIOS A DOMICILIO (2026-09-19).
    // Lo usan los tres bloques de rubro (4️⃣, 4️⃣bis y 5️⃣). Primero se pide lo de siempre (tiendas con
    // local); **si el rubro se queda vacío** —porque todas sus fichas son a domicilio, como Animación
    // Infantil— se vuelve a pedir incluyéndolas: así el bloque NUNCA sale vacío y la búsqueda no termina
    // en blanco. Medido con «pintacaritas»: antes daba 0 fichas y ahora muestra su rubro.
    $tiendas_del_rubro = function ($rubro_id) use ($buscar_clasica) {
        $filtro = rubro_filtro_id((int)$rubro_id, 'n');
        $lista  = $buscar_clasica($filtro, '');
        if (!$lista) $lista = $buscar_clasica($filtro, '', false, true);
        return $lista;
    };

    // ============================================================================
    // 📦 LA BÚSQUEDA POR PRODUCTOS (2026-09-20 — el caso «alquiler de sillas»)
    // ============================================================================
    // Qué pasaba (medido el 2026-09-20): los negocios que alquilan sillas tienen sus productos
    // publicados («Alquiler de sillas, mesas y mantelería para eventos»), y esos productos SÍ salían en
    // el desplegable del buscador… pero **la página de resultados nunca los miraba**: su consulta solo
    // comparaba lo escrito con el NOMBRE y la DESCRIPCIÓN de la tienda. Resultado: «alquiler de sillas»
    // mostraba 17 fichas flojas (mariachis, fotos, decoraciones) y **ni una sola** de las que alquilan
    // sillas, porque en su ficha esas palabras viven en el **título del producto**, no en su nombre.
    //
    // Esto es el escalón que faltaba: se busca lo escrito en el **título de los productos**
    // (`directorio_servicios.titulo`, la misma tabla que usa el desplegable) y las tiendas que los
    // venden ENTRAN a los resultados, con sus productos a la vista (el bloque 📦 de más abajo).
    //
    // ⚠️ Se exige que aparezcan TODAS las palabras (igual que el 2️⃣ de las tiendas): «alquiler sillas»
    //    pide las dos en el mismo título, así no entra cualquier cosa que solo diga «alquiler».
    // ⚠️ Respeta el filtro de DISTRITO encendido (con la misma regla de cobertura que las tiendas).
    //   `$estricto = true` → el título tiene que traer TODAS las palabras (lo normal).
    //   `$estricto = false` → **escalera relajada** (2026-09-20): trae los títulos que traigan AL MENOS
    //   UNA palabra y los ordena por CUÁNTAS traen («gana el producto porque tiene más palabras en
    //   común»). Se usa SOLO como 2.º intento, cuando el estricto no encontró nada: es el caso de
    //   «dulces y tortas», donde ningún título lleva las dos palabras juntas (la dulcería tiene
    //   «Torta de chocolate» y «Tofis caseros», y antes la búsqueda se quedaba sin productos).
    $buscar_productos = function ($texto, $limite = 15, $estricto = true) use ($distrito_ids) {
        $t = trim((string)$texto);
        if ($t === '') return [];
        $toks = busqueda_tokens($t);
        if (!$toks) $toks = [$t];
        // 🌱 LA RAÍZ DE CADA PALABRA (2026-09-20). Medido el mismo día: «barandas metálicas» NO
        // encontraba «Barandas y pasamanos metálicos» (el título dice «metálicos», no «metálicas») y
        // «dulces y tortas» no encontraba «Torta de chocolate». Se busca por la RAÍZ —se quita el plural
        // y la vocal final— y la comparación acepta las terminaciones a/o/as/os/s/es: así «tortas» trae
        // «torta» y «metálicas» trae «metálicos», sin que «casa» traiga «casaca» (las palabras de 3
        // letras o menos no se recortan, para no volverlas locas).
        $raices = [];
        foreach ($toks as $tk) {
            $n = sin_tildes_texto($tk);
            $r = preg_replace('/(es|s)$/', '', $n);
            $r2 = preg_replace('/[aeiou]$/', '', $r);
            $raices[] = (mb_strlen($r2) >= 4) ? $r2 : $n;
        }
        try {
            $sql = "SELECT s.id, s.titulo, s.precio, s.unidad, s.imagen, s.destacado, s.disponible_hasta,
                           n.id AS negocio_id, n.nombre AS negocio_nombre, n.slug AS negocio_slug,
                           c.icono AS categoria_icono,
                           (SELECT pf.ruta FROM directorio_producto_fotos pf
                             WHERE pf.producto_id = s.id ORDER BY pf.orden ASC, pf.id ASC LIMIT 1) AS foto
                      FROM directorio_servicios s
                      INNER JOIN directorio_negocios n ON n.id = s.negocio_id AND n.estado = 'activo'
                      LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                     WHERE s.activo = 1 AND " . sql_producto_vigente('s');
            $par = [];
            if ($estricto) {
                foreach ($raices as $rx) {
                    $sql .= " AND s.titulo LIKE ?";
                    $par[] = '%' . $rx . '%';
                }
            } else {
                // Relajado: cualquiera de las palabras basta (el orden lo decide cuántas trae).
                $sql .= " AND (" . implode(' OR ', array_fill(0, count($raices), 's.titulo LIKE ?')) . ")";
                foreach ($raices as $rx) { $par[] = '%' . $rx . '%'; }
            }
            if ($distrito_ids) {
                $ids = implode(',', $distrito_ids);
                $sql .= " AND (n.distrito_id IN ($ids)
                               OR EXISTS (SELECT 1 FROM directorio_negocio_cobertura cc
                                           WHERE cc.negocio_id = n.id AND cc.distrito_id IN ($ids)))";
            }
            // Lo mismo que ordena el desplegable: primero lo destacado y lo que tiene precio. Se traen
            // MÁS filas de las que se van a mostrar (60) porque después se reordenan por relevancia:
            // si se cortara aquí por visitas, una tienda nueva con el producto exacto se quedaba fuera
            // de las 15 (medido el 2026-09-20 con «corte de cabello» y JC Studio).
            $sql .= " ORDER BY s.destacado DESC, (s.precio > 0) DESC, n.vistas_count DESC, s.id DESC
                      LIMIT " . max(60, (int)$limite * 4);
            $st = db()->prepare($sql);
            $st->execute($par);
            $filas = $st->fetchAll();

            // 🎯 La palabra tiene que estar COMPLETA en el título: «casa» no debe traer «casaca».
            //    Se aceptan plural y género («silla»/«sillas», «metálica»/«metálicos»).
            //    Se cuentan las palabras que trae cada título (`palabras`), y ese número ordena después:
            //    «gana el producto porque tiene más palabras en común» (regla del jefe, 2026-09-20).
            //    ⚠️ Si el filtro dejara la lista vacía, se devuelve lo que dio el LIKE: la búsqueda
            //       nunca sale peor que antes.
            $filtradas = [];
            foreach ($filas as $f) {
                $tit = sin_tildes_texto((string)($f['titulo'] ?? ''));
                $n_pal = 0;
                foreach ($raices as $rx) {
                    $re = '/(^|[^a-z0-9])' . preg_quote($rx, '/') . '(a|o|as|os|s|es)?([^a-z0-9]|$)/u';
                    if (preg_match($re, $tit)) { $n_pal++; }
                }
                $f['palabras'] = $n_pal;
                if ($estricto ? ($n_pal === count($raices)) : ($n_pal > 0)) $filtradas[] = $f;
            }
            if ($filtradas) {
                // Más palabras en común primero; a igualdad, lo destacado, lo que tiene precio y, al
                // final, el orden que ya traía la consulta (visitas) para que sea siempre el mismo.
                foreach ($filtradas as $i => $f) { $filtradas[$i]['_orden'] = $i; }
                usort($filtradas, static function ($a, $b) {
                    if ($a['palabras'] !== $b['palabras']) { return $b['palabras'] <=> $a['palabras']; }
                    if ((int)$a['destacado'] !== (int)$b['destacado']) { return (int)$b['destacado'] <=> (int)$a['destacado']; }
                    if (((float)$a['precio'] > 0) !== ((float)$b['precio'] > 0)) { return ((float)$b['precio'] > 0) <=> ((float)$a['precio'] > 0); }
                    return $a['_orden'] <=> $b['_orden'];
                });
                $filtradas = array_slice($filtradas, 0, (int)$limite);
            }
            return $filtradas ?: array_slice($filas, 0, (int)$limite);
        } catch (Throwable $e) {
            return [];   // si algo falla, la búsqueda sale igual que siempre
        }
    };

    /** Las TIENDAS de una lista de ids, con las mismas columnas de la tarjeta (y sin repetir). */
    $traer_tiendas_por_id = function (array $ids) use ($cols_negocio) {
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
        if (!$ids) return [];
        try {
            $sql = "SELECT $cols_negocio
                      FROM directorio_negocios n
                      LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                      LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
                     WHERE n.estado = 'activo' AND n.id IN (" . implode(',', $ids) . ")
                     ORDER BY n.vistas_count DESC, n.rating DESC";
            return db()->query($sql)->fetchAll();
        } catch (Throwable $e) {
            return [];
        }
    };

    // 2️⃣ TODAS LAS PALABRAS: la frase entera no está escrita en ninguna ficha, pero sus palabras sí
    // («la casa del pollo» → «casa» + «pollo»). Antes esto daba 0 y el visitante caía en la página
    // vacía. Se exige que estén TODAS (como el plan A del buscador del navegador).
    if (!$resultados && $termino !== '' && count(busqueda_tokens($termino)) > 1) {
        $resultados = $buscar_clasica([], null, true);
        // El número honesto tiene que ser el del intento que SÍ encontró algo.
        if ($resultados) $total_clasico = (int)$buscar_clasica([], null, true, false, true);
    }

    // 2️⃣bis 🧰 LA TIENDA QUE COINCIDE PERO ES «A DOMICILIO» (2026-09-19 — el caso «mayciel»).
    // ⚠️ La lista de resultados descarta a propósito los servicios a domicilio (`ubicacion_tipo =
    //    'domicilio'`), que van en su propio bloque de abajo (ver el 🧰 de la línea 168). El problema
    //    aparece cuando lo que el visitante escribió es el NOMBRE de esa tienda («mayciel», «payasito
    //    crespín»): como la marca no apunta a ningún rubro, NO había nada que la subiera a la lista,
    //    el contador quedaba en 0 y la página remataba con «No hubo coincidencias exactas… ¿Buscabas
    //    alguno de estos?» **con la tienda ya publicada**, además de disparar el aviso público de
    //    «nadie lo vende» (medido el 2026-09-19: `mayciel` daba 0 en la lista y 3 con domicilio).
    //    Si el texto no encontró NADA, se repite incluyéndolas: lo que escribió ES el nombre de su tienda.
    if (!$resultados && $termino !== '') {
        $resultados = $buscar_clasica([], null, false, true);
        if (!$resultados && count(busqueda_tokens($termino)) > 1) {
            $resultados = $buscar_clasica([], null, true, true);
        }
        if ($resultados) $busqueda_domicilio = true;
    }

    // 2️⃣ter 📦 LO QUE ESCRIBISTE ES UN PRODUCTO (2026-09-20 — el caso «alquiler de sillas»).
    // ================================================================================================
    // El escalón que faltaba. Medido el 2026-09-20: los negocios que alquilan sillas tienen sus
    // productos publicados y **visibles**, pero al buscar «alquiler de sillas» no salían: la consulta de
    // resultados solo compara lo escrito con el **nombre** y la **descripción** de la tienda, y en esas
    // fichas las palabras viven en el **título del producto** («Alquiler de sillas, mesas y mantelería
    // para eventos»). Ahora se busca también ahí y **las tiendas que los venden entran a los resultados**
    // (arriba: son la respuesta exacta) con sus productos a la vista en el bloque 📦.
    //
    // ⚠️ Cuándo NO corre:
    //    · Si lo escrito solo eran palabras de mando («comprar» a secas): ahí no se nombró ningún
    //      producto y buscar por título daría sorpresas (misma regla que los escalones 3️⃣, 4️⃣ y 5️⃣).
    //    · Si el visitante ya eligió un RUBRO en el filtro: ahí manda su filtro, no la adivinanza.
    //    · Si alguna tienda de las que ya salieron lleva lo escrito **en su nombre**: ese nombre manda
    //      (es lo que el visitante escribió) y no hay nada que arreglar.
    $busqueda_producto = [];   // productos que coinciden con lo escrito (los pinta el bloque 📦)
    $ids_por_producto  = [];   // tiendas que entraron por sus productos (se ordenan primero)
    if ($termino !== '' && !$categoria_id && empty($limpieza['vacio'])) {
        $en_nombre = false;
        $t_norm = sin_tildes_texto($termino);
        // Las palabras de lo escrito, sin tildes (para comparar nombres y títulos como escribe la gente).
        $toks_norm = [];
        foreach (busqueda_tokens($termino) as $tk) { $toks_norm[] = sin_tildes_texto($tk); }
        if (!$toks_norm) $toks_norm = [$t_norm];

        foreach ($resultados as $r) {
            $nom = sin_tildes_texto((string)($r['nombre'] ?? ''));
            if (mb_strpos($nom, $t_norm) !== false) {
                $en_nombre = true;
                break;
            }
            // 🔝 Una tienda cuyo NOMBRE trae TODAS las palabras (aunque no pegadas: «Pollo a la Brasa
            // Don José» con la búsqueda «pollo brasa») es la respuesta directa: se apunta para que el
            // orden final la ponga por encima de las que solo llegan por sus productos.
            $todas = true;
            foreach ($toks_norm as $tk) {
                if (mb_strpos($nom, $tk) === false) { $todas = false; break; }
            }
            if ($todas) $ids_nombre_completo[(int)$r['id']] = true;
        }
        if (!$en_nombre) {
            $prods = $buscar_productos($termino);
            // 2.º intento RELAJADO (2026-09-20): si el título de ningún producto trae TODAS las
            // palabras, se buscan los que traigan AL MENOS UNA y se ordenan por cuántas traen. Es el
            // caso «dulces y tortas» (ningún producto se llama así, pero la dulcería tiene «Torta de
            // chocolate» y «Tofis caseros»). Solo corre cuando el estricto no encontró NADA.
            // ⚠️ Se piden 40 (no 15) a propósito: el bloque enseña 15, pero las tiendas se marcan con
            //    TODOS los productos que coinciden — si se cortara antes, una tienda cuyo producto quedó
            //    en el puesto 16 se quedaba sin su lugar en los resultados.
            $amplio = false;
            if (!$prods && count($toks_norm) > 1) {
                $prods  = $buscar_productos($termino, 40, false);
                $amplio = true;
            }
            if ($prods) {
                $busqueda_producto = $amplio ? array_slice($prods, 0, 15) : $prods;
                $ya = [];
                foreach ($resultados as $r) { $ya[(int)$r['id']] = true; }
                // 🏆 Cuántas palabras trae la MEJOR coincidencia de cada tienda y CUÁNTOS de sus
                // productos coinciden: eso es lo que ordena después («gana el producto porque tiene
                // más palabras en común», y a igualdad gana el catálogo que más ofrece).
                $marca = [];
                foreach ($prods as $p) {
                    $pid = (int)$p['negocio_id'];
                    $pal = (int)($p['palabras'] ?? 0);
                    if (!isset($marca[$pid])) $marca[$pid] = ['palabras' => $pal, 'n' => 0];
                    $marca[$pid]['palabras'] = max($marca[$pid]['palabras'], $pal);
                    $marca[$pid]['n']++;
                }
                $tiendas_prod = [];
                foreach ($traer_tiendas_por_id(array_keys($marca)) as $r) { $tiendas_prod[(int)$r['id']] = $r; }
                $nuevas = [];
                foreach ($prods as $p) {
                    $pid = (int)$p['negocio_id'];
                    if (!isset($tiendas_prod[$pid]) || isset($ids_por_producto[$pid])) continue;
                    // 🔝 TODAS las tiendas de esos productos quedan marcadas como «vende lo escrito»,
                    // estén o no ya en la lista: la que salió por su descripción y ADEMÁS tiene el
                    // producto es la respuesta más fuerte que hay (regla del jefe: «gana el producto
                    // porque tiene más palabras en común»). Medido el 2026-09-20 con «chicharrón de
                    // pescado»: El Oso Bejar, El Cevichón y Obregón ya salían por su descripción y se
                    // quedaban detrás de las tiendas del rubro, con el producto publicado.
                    $ids_por_producto[$pid] = $marca[$pid];
                    if (empty($ya[$pid])) {
                        $nuevas[] = $tiendas_prod[$pid];
                        $ya[$pid] = true;
                    }
                }
                if ($nuevas) {
                    // Van ARRIBA de todo: quien busca un producto quiere a quien lo vende. La lista sigue
                    // cortándose en POR_PAGINA_BUSCADOR, como siempre.
                    $resultados = array_slice(array_merge($nuevas, $resultados), 0, POR_PAGINA_BUSCADOR);
                }
            }
        }
    }

    // 2️⃣quater 🔝 LA TIENDA QUE SE LLAMA COMO LO ESCRITO, AUNQUE SEA «A DOMICILIO» (2026-09-20).
    // ================================================================================================
    // Medido con «bazar y novedades»: la tienda que se llama **BAZAR Y NOVEDADES EINER** es
    // `ubicacion_tipo = 'domicilio'` y la lista principal las excluye a propósito (van a su propio
    // bloque), así que la ÚNICA tienda que se llama con las dos palabras escritas **no salía en ninguna
    // parte**: su nombre es la respuesta más directa que existe.
    // Aquí se repite la búsqueda por palabras **incluyendo las de domicilio** y se suben al primer
    // puesto las que traen TODAS las palabras en su nombre (se marcan en `$ids_nombre_completo`, que es
    // la primera clave del orden final). Si alguna es «a domicilio», se avisa con la nota 🧰 de siempre.
    if ($termino !== '' && !$categoria_id && empty($limpieza['vacio']) && $toks_norm) {
        $ya_nom = [];
        foreach ($resultados as $r) { $ya_nom[(int)$r['id']] = true; }
        foreach ($buscar_clasica([], null, true, true) as $r) {
            $nom   = sin_tildes_texto((string)($r['nombre'] ?? ''));
            $todas = true;
            foreach ($toks_norm as $tk) {
                if (mb_strpos($nom, $tk) === false) { $todas = false; break; }
            }
            if (!$todas) continue;
            $ids_nombre_completo[(int)$r['id']] = true;
            if (empty($ya_nom[(int)$r['id']])) {
                $resultados[] = $r;
                $ya_nom[(int)$r['id']] = true;
                if (($r['ubicacion_tipo'] ?? '') === 'domicilio') $busqueda_domicilio = true;
            }
        }
    }

    // 3️⃣ LA JERGA LOCAL: ya no hay nada con lo que escribió, así que se prueba con la palabra del
    // sitio («puchitos» → «cigarrillos», «chelas» → «cerveza»). Solo aquí, nunca antes: si algún día
    // una tienda se llama «Los Puchitos», esa búsqueda la encuentra el intento 1.
    // ⚠️ Los intentos 3️⃣, 4️⃣ y 5️⃣ se saltan cuando la búsqueda SOLO traía mandos («comprar» a secas):
    //    ahí el visitante no nombró ningún producto, y llevarlo a un rubro daba sorpresas (la palabra
    //    «compra» es clave de «Casas de Cambio Digital»).
    $puede_relajar = ($termino !== '' && !$categoria_id && empty($limpieza['vacio']));
    if (!$resultados && $puede_relajar) {
        $jerga = busqueda_jerga_aplicar($termino);
        if ($jerga) {
            $resultados = $buscar_clasica([], (string)$jerga['texto'], true);
            if (!$resultados) $resultados = $buscar_clasica([], (string)$jerga['texto'], false);
            if ($resultados) $busqueda_jerga = $jerga;
        }
    }

    // 4️⃣ LAS «FRASES DE UNIÓN» DEL RUBRO (2026-09-13, pedido del jefe): «clavos» no está en el
    // nombre ni en la descripción de ninguna tienda, pero SÍ es una palabra del rubro Ferreterías
    // (tabla `directorio_categoria_claves`, la misma que usa el chat). Si el texto no encontró NADA,
    // se muestran las tiendas de ese rubro: es la respuesta que espera el visitante que pregunta
    // «quién vende clavos», y nunca se queda con la página vacía.
    if (!$resultados && $puede_relajar) {
        $rubro_clave = categoria_por_clave_texto($termino);
        if (!$rubro_clave && $busqueda_jerga) {
            $rubro_clave = categoria_por_clave_texto((string)$busqueda_jerga['texto']);
        }
        if ($rubro_clave) {
            $resultados = $tiendas_del_rubro((int)$rubro_clave['id']);
            if ($resultados) $busqueda_por_rubro = $rubro_clave;
        }
    }

    // 5️⃣ EL RUBRO QUE ACOMPAÑA (pedido del jefe, 2026-09-14): *«si alguien dice "comprar jugo de piña"
    // es posible que la programación no sea capaz de ofrecer a los principales vendedores de piñas y
    // termine mostrando solo a los que venden jugo de piña»*.
    //
    // Cuando el texto encuentra POQUITO (menos de 4) y la palabra además es de un rubro, no se deja al
    // visitante con esas pocas: se le ofrece, en su PROPIO bloque (sin mezclarse con los resultados
    // exactos), las tiendas de ese rubro. Es el caso de «cerveza»: el texto solo da 1 tienda, pero el
    // rubro Bodegas/Minimarkets tiene 185. Y siempre por las «frases de unión» (datos), no por
    // adivinar: si el rubro no tiene la palabra, lo que falta es la clave en la tabla, no código.
    // ⚠️ Solo con búsquedas de UNA palabra (un producto: «cerveza», «paracetamol»). Con dos o más
    //    («alquiler de vestidos») el visitante ya fue específico y el rubro solo metería ruido: el
    //    rubro se saca de UNA palabra y la otra se perdería (pasó en las pruebas: «alquiler vestidos»
    //    salía acompañado de «Alquiler de Habitaciones»).
    $rubro_acompana = null;
    // 🆕 2026-09-18: con UNA palabra el bloque ya no depende solo del NÚMERO de fichas. También cuando
    // NINGUNA ficha tiene la palabra en su **NOMBRE**: «anchoveta» daba 12 fichas que la mencionan de
    // pasada (un centro cívico, un hotel, una veterinaria) y los **mercados que la venden no salían**.
    // Doce fichas que la nombran en su descripción no son la respuesta de quien quiere comprarla.
    // ⚠️ El bloque va SIEMPRE debajo de los resultados: al visitante no se le quita nada.
    // ⚠️ No se toca el caso «pocas fichas» (< 4), que es el de «cerveza» y «gasfitero» (decisión del jefe).
    $palabra_en_nombre = false;
    foreach ($resultados as $r) {
        if (mb_strpos(sin_tildes_texto((string)($r['nombre'] ?? '')), sin_tildes_texto($termino)) !== false) {
            $palabra_en_nombre = true;
            break;
        }
    }
    if ($resultados && $puede_relajar && count(busqueda_tokens($termino)) === 1
        && (count($resultados) < 4 || !$palabra_en_nombre)) {
        $rubro_clave = categoria_por_clave_texto($termino);
        if ($rubro_clave) {
            $ya = [];
            foreach ($resultados as $r) $ya[(int)$r['id']] = true;
            $del_rubro = $tiendas_del_rubro((int)$rubro_clave['id']);
            $del_rubro = array_values(array_filter($del_rubro, function ($r) use ($ya) {
                return empty($ya[(int)$r['id']]);
            }));
            if ($del_rubro) {
                $rubro_acompana = ['rubro' => $rubro_clave, 'tiendas' => array_slice($del_rubro, 0, 12)];
            }
        }
    }

    // 4️⃣bis 🏷️ LA FRASE QUE NO DESCRIBE A NINGUNA FICHA (2026-09-18 — guía
    // `GUIA_BUSQUEDAS_POR_RUBRO_Y_SINONIMOS.md` §7.0 y `GUIA_BUSCADOR_FUZZY.md` §4.9).
    //
    // «reforzamiento escolar», «clases de inglés para 7 años», «niños hiperactivos»: el texto SÍ encuentra
    // unas pocas fichas —la palabra «escolar» en un nombre, «clases» en otro—, pero NINGUNA es lo que el
    // visitante escribió. Como el intento 4️⃣ solo corre cuando NO se encontró NADA, el rubro (que SÍ tiene
    // la frase en sus «frases de unión») nunca aparecía y el visitante se quedaba con 4 fichas flojas.
    //
    // El criterio es el MISMO que el del desplegable (`describeUnaTienda()`): si **ninguna** ficha describe
    // lo escrito (TODAS las palabras, de 3 letras o más, dentro de su nombre + rubro + distrito), se
    // resuelve el rubro por las claves y se ofrece **en su propio bloque**, sin quitarle al visitante ni
    // una de las fichas que ya tenía.
    //
    // ⚠️ NO se toca el 5️⃣ (una sola palabra: «cerveza» da 1 tienda y su rubro tiene 185 — decisión del
    //    jefe) ni la escalera de arriba. Esto es SOLO para frases de dos o más palabras y SOLO cuando
    //    ninguna ficha las describe. Tope de fichas: menos de 8 (con más, el visitante ya tiene dónde
    //    elegir y el bloque sería ruido).
    if ($resultados && !$rubro_acompana && $puede_relajar
        && count($resultados) < 8 && count(busqueda_tokens($termino)) > 1) {

        $toks_frase = array_values(array_filter(busqueda_tokens($termino),
            static function ($t) { return mb_strlen($t) >= 3; }));

        $describe = false;
        if ($toks_frase) {
            foreach ($resultados as $r) {
                $texto_ficha = sin_tildes_texto(
                    (string)($r['nombre'] ?? '') . ' ' .
                    (string)($r['categoria_nombre'] ?? '') . ' ' .
                    (string)($r['distrito_nombre'] ?? '')
                );
                $todas = true;
                foreach ($toks_frase as $tk) {
                    if (mb_strpos($texto_ficha, sin_tildes_texto($tk)) === false) { $todas = false; break; }
                }
                if ($todas) { $describe = true; break; }
            }
        }

        if (!$describe) {
            // La frase entera primero; si no da, palabra por palabra (la más larga primero).
            $rubro_frase = categoria_por_clave_texto($termino);
            if (!$rubro_frase) {
                $largos = $toks_frase;
                usort($largos, static function ($a, $b) { return mb_strlen($b) <=> mb_strlen($a); });
                foreach ($largos as $tk) {
                    $rubro_frase = categoria_por_clave_texto($tk);
                    if ($rubro_frase) break;
                }
            }
            if ($rubro_frase) {
                $ya = [];
                foreach ($resultados as $r) $ya[(int)$r['id']] = true;
                $del_rubro = $tiendas_del_rubro((int)$rubro_frase['id']);
                $del_rubro = array_values(array_filter($del_rubro, static function ($r) use ($ya) {
                    return empty($ya[(int)$r['id']]);
                }));
                if ($del_rubro) {
                    $rubro_acompana = ['rubro' => $rubro_frase,
                                       'tiendas' => array_slice($del_rubro, 0, 12),
                                       'por_frase' => true];
                }
            }
        }
    }
}

// ====== 🔢 EL NÚMERO DE TELÉFONO (2026-09-19) ======
// En las búsquedas REALES de `directorio_busquedas` apareció gente escribiendo un número —«931103286»
// (2 veces) y «976940121»— y las dos veces salió la página vacía: el buscador solo miraba el nombre y
// la descripción de la ficha. En un directorio, escribir un número significa «quiero la tienda de este
// número», así que se buscan las tiendas por su WhatsApp, su teléfono o sus teléfonos secundarios
// (tabla `directorio_negocio_telefonos`). Antes de esto solo se intenta si lo escrito son CASI PURO
// DÍGITOS (6 o más) y TODAVÍA no hay resultados: no le quita nada a ninguna búsqueda de texto.
if (!$resultados) {
    $tel_digitos = preg_replace('/\D+/', '', (string)$termino);
    $largo_escrito = mb_strlen(trim((string)$termino));
    if ($largo_escrito > 0 && mb_strlen($tel_digitos) >= 6
        && mb_strlen($tel_digitos) >= ($largo_escrito * 0.6)) {

        $sel_tel = "SELECT n.id, n.nombre, n.slug, n.direccion, n.rating, n.vistas_count, n.lat, n.lng,
                           n.telefono, n.whatsapp,
                           c.nombre AS categoria_nombre, c.icono AS categoria_icono,
                           d.nombre AS distrito_nombre,
                           (SELECT f.ruta FROM directorio_fotos f WHERE f.negocio_id = n.id
                             ORDER BY f.orden ASC LIMIT 1) AS imagen_portada
                      FROM directorio_negocios n
                      LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                      LEFT JOIN directorio_distritos d ON d.id = n.distrito_id
                     WHERE n.estado = 'activo'
                       AND (REPLACE(REPLACE(REPLACE(COALESCE(n.whatsapp,''),' ',''),'-',''),'+','') LIKE ?
                         OR REPLACE(REPLACE(REPLACE(COALESCE(n.telefono,''),' ',''),'-',''),'+','') LIKE ?
                         %s)
                     ORDER BY n.vistas_count DESC, n.rating DESC LIMIT " . POR_PAGINA_BUSCADOR;
        // Los teléfonos secundarios viven en su propia tabla: si todavía no existe en este hosting,
        // se busca igual con los dos campos de siempre (y la búsqueda nunca se cae por eso).
        $sub_tel  = "OR EXISTS (SELECT 1 FROM directorio_negocio_telefonos t
                                  WHERE t.negocio_id = n.id
                                    AND REPLACE(REPLACE(REPLACE(COALESCE(t.numero,''),' ',''),'-',''),'+','') LIKE ?)";
        $par_tel  = ['%' . $tel_digitos . '%', '%' . $tel_digitos . '%'];
        try {
            $st_tel = db()->prepare(sprintf($sel_tel, $sub_tel));
            $st_tel->execute(array_merge($par_tel, ['%' . $tel_digitos . '%']));
            $por_telefono = $st_tel->fetchAll();
        } catch (Throwable $e) {
            $st_tel = db()->prepare(sprintf($sel_tel, ''));
            $st_tel->execute($par_tel);
            $por_telefono = $st_tel->fetchAll();
        }
        if ($por_telefono) {
            $resultados         = $por_telefono;
            $busqueda_telefono  = $tel_digitos;
        }
    }
}

// ====== ⭐ EL RUBRO DE LA FRASE VA PRIMERO (orden del jefe, 2026-09-19) ======
// Qué pidió, textual: *«cada tienda que se crea debe heredar todas las frases de su rubro y aparte
// agregar las frases personalizadas que se ha dado. Por ejemplo la palabra payasito: deben aparecer
// todos los payasitos, pero si uno de ellos pone "payasito marrón", entonces si alguien escribiera
// "payasito", "marrón" o "marrón payasito" esa tienda aparecería en los primeros resultados, PERO NO
// LA ÚNICA, porque los demás que están compitiendo por el término payasito también deberían aparecer,
// dando más auge a los que tienen más vistas o consiguen más visitas. Aquí premiamos al que tiene
// buenos resultados: si aparece, que aparezca mucho más.»*
//
// CÓMO QUEDA EL ORDEN (y por qué así):
//   1.º **Las tiendas del RUBRO al que apunta lo escrito** — o sea la herencia de las frases del rubro,
//       que es lo que hace que «payaso» traiga a TODOS los payasitos. Dentro de ellas, **la que lleva
//       MÁS palabras de lo escrito va arriba** («payasito marrón» le gana a «payasito»).
//   2.º **A su costadito, las demás** que compiten por el mismo término — y ahí manda **el que tiene
//       más vistas y mejor calificación**: premiamos al que tiene buenos resultados.
// Si lo escrito NO es frase de ningún rubro, **todo sigue exactamente igual que antes**.
// ⚠️ Antes de esto el orden era SOLO `vistas_count DESC, rating DESC`: buscando «payaso» salía primero
//    un bazar («Novedades Jar», más visitas) y el Payasito Crespín tercero.
if ($termino !== '' && $resultados) {
    try {
        // 🔎 ¿A qué rubro apunta lo escrito? Se pregunta **PALABRA POR PALABRA con la MISMA función del
        //    sitio** (`categoria_por_clave_texto`) y **gana el rubro que se lleva MÁS palabras**. La frase
        //    entera, sola, empataba mal («payaso para cumpleaños» caía en Florerías y Regalos por el id
        //    más bajo) y el payasito no subía. Con el voto por palabras, «payaso» manda a Animación
        //    Infantil aunque la frase traiga además «cumpleaños».
        //    ⚠️ NO se inventa ninguna comparación de texto propia: se usa la del sitio, que ya tiene su
        //    escala medida (GUIA_BUSQUEDAS_POR_RUBRO_Y_SINONIMOS.md §7.1 b). Intentarlo «a mano» el
        //    2026-09-19 eligió rubros equivocados (mariachis para «payaso») y se descartó.
        $tokens_busq = [];
        foreach (busqueda_tokens($termino) as $t) {
            $t = sin_tildes_texto($t);
            if (mb_strlen($t) >= 3) { $tokens_busq[] = $t; }
        }
        // 1️⃣ **La frase entera primero**, con la función del sitio (`categoria_por_clave_texto`), que ya
        //    desempata por «el rubro que TIENE tiendas». Medido el 2026-09-19 (después de mover las frases
        //    duplicadas a su único dueño): «show infantil» y «payaso para cumpleaños» resuelven los DOS a
        //    Animación Infantil. Antes de mover las frases, «payaso para cumpleaños» caía en Florerías.
        $rf_orden = categoria_por_clave_texto($termino, 2);
        // 2️⃣ Si la frase no es de ningún rubro, se vota **palabra por palabra** y gana la que se lleve más
        //    (así una búsqueda de dos palabras sin frase propia igual encuentra su rubro).
        //    ⚠️ Se probaron y se descartaron el 2026-09-19: comparar el texto a mano (eligió mariachis para
        //    «payaso») y buscar la clave exacta en el mapa crudo (sin el desempate por tiendas, ganaba el
        //    id más bajo y también salía mal: «show» manda a Música, y «show infantil» es Animación).
        if (!$rf_orden && $tokens_busq) {
            $votos = [];
            foreach ($tokens_busq as $t) {
                $r = categoria_por_clave_texto($t, 2);
                if ($r) {
                    $cid_v = (int)$r['id'];
                    $votos[$cid_v] = ['n' => ($votos[$cid_v]['n'] ?? 0) + 1, 'fila' => $r];
                }
            }
            if ($votos) {
                uasort($votos, static function ($a, $b) { return $b['n'] <=> $a['n']; });
                $primero  = reset($votos);
                $rf_orden = $primero['fila'];
            }
        }
        if ($rf_orden || $ids_por_producto || $ids_nombre_completo) {
            $rid_orden = $rf_orden ? (int)$rf_orden['id'] : 0;
            $por_id = [];
            foreach ($resultados as $r) { $por_id[(int)$r['id']] = $r; }
            // 🧬 LA HERENCIA: entran TAMBIÉN las tiendas del rubro que el texto no había encontrado.
            if ($rf_orden) {
                foreach ($tiendas_del_rubro($rid_orden) as $r) {
                    if (!isset($por_id[(int)$r['id']])) { $por_id[(int)$r['id']] = $r; }
                }
            }
            $pal_orden = [];
            foreach (busqueda_tokens($termino) as $t) {
                $t = sin_tildes_texto($t);
                if (mb_strlen($t) >= 3) { $pal_orden[] = $t; }
            }
            $puntos = [];
            foreach ($por_id as $id => $r) {
                $txt = sin_tildes_texto(($r['nombre'] ?? '') . ' ' . ($r['categoria_nombre'] ?? '') . ' '
                                      . ($r['distrito_nombre'] ?? '') . ' ' . ($r['direccion'] ?? ''));
                $n = 0;
                foreach ($pal_orden as $t) { if (mb_strpos($txt, $t) !== false) { $n++; } }
                $puntos[$id] = [
                                // 🔝 La tienda se LLAMA como lo que se escribió (todas las palabras en su
                                // nombre): es la respuesta directa de la búsqueda.
                                'nombre' => isset($ids_nombre_completo[$id]) ? 1 : 0,
                                // 📦 2026-09-20: la tienda entró porque VENDE lo que se escribió. Se
                                // ordena por CUÁNTAS palabras de lo escrito trae su mejor producto
                                // («gana el producto porque tiene más palabras en común») y, a igualdad,
                                // por cuántos productos suyos coinciden.
                                'producto' => isset($ids_por_producto[$id]) ? (int)$ids_por_producto[$id]['palabras'] : 0,
                                'productos_n' => isset($ids_por_producto[$id]) ? (int)$ids_por_producto[$id]['n'] : 0,
                                'rubro' => ((int)($r['categoria_id'] ?? 0) === $rid_orden) ? 1 : 0,
                                'palabras' => $n,
                                'vistas' => (int)($r['vistas_count'] ?? 0),
                                'rating' => (float)($r['rating'] ?? 0)];
            }
            $lista_orden = array_values($por_id);
            usort($lista_orden, static function ($a, $b) use ($puntos) {
                $pa = $puntos[(int)$a['id']];
                $pb = $puntos[(int)$b['id']];
                if ($pa['nombre'] !== $pb['nombre'])     { return $pb['nombre'] <=> $pa['nombre']; }
                if ($pa['producto'] !== $pb['producto']) { return $pb['producto'] <=> $pa['producto']; }
                if ($pa['productos_n'] !== $pb['productos_n']) { return $pb['productos_n'] <=> $pa['productos_n']; }
                if ($pa['rubro'] !== $pb['rubro'])       { return $pb['rubro'] <=> $pa['rubro']; }
                if ($pa['palabras'] !== $pb['palabras']) { return $pb['palabras'] <=> $pa['palabras']; }
                if ($pa['vistas'] !== $pb['vistas'])     { return $pb['vistas'] <=> $pa['vistas']; }
                return $pb['rating'] <=> $pa['rating'];
            });
            $resultados = array_slice($lista_orden, 0, POR_PAGINA_BUSCADOR);
            // El bloque «el rubro te acompaña» deja de hacer falta: esas tiendas ahora van ARRIBA.
            if (!empty($rubro_acompana['por_frase'])) { $rubro_acompana = null; }
        }
    } catch (Throwable $e) {
        // Nunca se rompe una búsqueda por este orden: si algo falla, queda como estaba.
    }
}

// ============================================================================
// 🧹 EL BLOQUE DEL RUBRO NO REPITE LO QUE YA ESTÁ ARRIBA (2026-09-20)
// ============================================================================
// Los bloques de rubro (4️⃣bis y 5️⃣) se arman ANTES del orden final, así que no saben qué tiendas va a
// terminar mostrando la lista. El orden final hereda las tiendas del rubro (su «🧬 herencia»), y entonces
// la misma tarjeta salía DOS veces en la misma página: en los resultados y en «mira también estas».
// ⚠️ Medido el 2026-09-20 con «festjim»: el bloque repetía las 12 que ya estaban arriba. Si tras limpiar
//    el bloque se queda sin tiendas, no se pinta nada (mejor nada que una repetición).
if ($rubro_acompana && !empty($rubro_acompana['tiendas'])) {
    $ya_rubro = [];
    foreach ($resultados as $r) { $ya_rubro[(int)$r['id']] = true; }
    $solo_nuevas = array_values(array_filter($rubro_acompana['tiendas'],
        static function ($r) use ($ya_rubro) { return empty($ya_rubro[(int)$r['id']]); }));
    $rubro_acompana = $solo_nuevas ? array_merge($rubro_acompana, ['tiendas' => $solo_nuevas]) : null;
}

// ============================================================================
// 🏷️ 3️⃣ter EL RUBRO QUE ACOMPAÑA CUANDO LA BÚSQUEDA LLEGÓ POR LOS PRODUCTOS (2026-09-20)
// ============================================================================
// Pieza 3 del arreglo del buscador de productos. «alquiler de sillas» es cosa de **Fiestas y Eventos**
// (es una de sus «frases de unión»), pero como la búsqueda SÍ encontró fichas (las que lo venden), los
// bloques de rubro que ya existían no se disparaban: esos solo salen cuando el texto no encuentra NADA
// (4️⃣) o cuando describe mal a todas las fichas (4️⃣bis). Así el visitante se quedaba con las pocas
// tiendas que alquilan sillas y **nunca veía** los toldos, carpas, decoraciones o mariachis, que son su
// mismo rubro — justo lo que pidió el jefe: *«no solo los que venden jugo de piña, también los
// principales vendedores de piñas»*.
// ⚠️ Va como bloque APARTE (nunca mezclado con los resultados) y solo se ofrece si hay tiendas del rubro
//    que todavía no estén en la lista: la misma tarjeta dos veces es ruido.
if ($busqueda_producto && !$rubro_acompana && $termino !== '' && !$categoria_id && empty($limpieza['vacio'])) {
    try {
        $rubro_prod = categoria_por_clave_texto($termino, 2);
        if (!$rubro_prod) {
            // Palabra por palabra, la más larga primero (es la que más dice): «alquiler de sillas».
            $largos = busqueda_tokens($termino);
            usort($largos, static function ($a, $b) { return mb_strlen($b) <=> mb_strlen($a); });
            foreach ($largos as $tk) {
                if (mb_strlen($tk) < 4) continue;
                $rubro_prod = categoria_por_clave_texto($tk, 2);
                if ($rubro_prod) break;
            }
        }
        if ($rubro_prod) {
            $ya = [];
            foreach ($resultados as $r) { $ya[(int)$r['id']] = true; }
            $del_rubro = array_values(array_filter($tiendas_del_rubro((int)$rubro_prod['id']),
                static function ($r) use ($ya) { return empty($ya[(int)$r['id']]); }));
            if ($del_rubro) {
                $rubro_acompana = ['rubro' => $rubro_prod, 'tiendas' => array_slice($del_rubro, 0, 12)];
            }
        }
    } catch (Throwable $e) {
        // El bloque es un extra: si algo falla, la búsqueda sale igual que siempre.
    }
}

$total = count($resultados);

// ============================================================================
// 🔢 «HAY N MÁS» (2026-09-19) — el MISMO aviso que se puso en los rubros.
// La lista corta en 24 y no lo decía: se cuenta lo que encontró la búsqueda de verdad
// (`$total_clasico`) y se busca el rubro que MÁS aparece entre lo que se está mostrando, para ofrecer
// «míralas todas» apuntando a su página de rubro (que desde hoy tiene paginador y las muestra todas).
// ============================================================================
$mostradas = count($resultados);
$rubro_mas = null;
// ⚠️ Solo cuando el visitante BUSCÓ algo (texto, rubro o distrito). En una búsqueda vacía (solo está
// navegando) el aviso no aporta: «hay 1.664 tiendas» y el rubro que más sale entre las 24 primeras
// salía cualquiera (Turismo), que despista en vez de ayudar.
$hay_busqueda = ($termino !== '' || $categoria_slug !== '' || !empty($distritos_sel));
if (!$modo_cerca && $hay_busqueda && $total_clasico > $mostradas) {
    $cuenta_rubros = [];
    foreach ($resultados as $r) {
        $s = (string)($r['categoria_slug'] ?? '');
        if ($s === '') continue;
        if (!isset($cuenta_rubros[$s])) {
            $cuenta_rubros[$s] = ['slug' => $s, 'nombre' => (string)($r['categoria_nombre'] ?? ''), 'n' => 0];
        }
        $cuenta_rubros[$s]['n']++;
    }
    if ($cuenta_rubros) {
        uasort($cuenta_rubros, static function ($a, $b) { return $b['n'] <=> $a['n']; });
        $rubro_mas = reset($cuenta_rubros);
    }
}

// ====== 🛒 ENCARGOS (pedido del jefe, 2026-09-15) ======
// El visitante buscó y no hay nada. Hasta hoy eso terminaba en un aviso al Telegram del jefe (que no
// puede hacer nada con él) y en una página que decía «prueba con otra palabra». Ahora, **si de verdad
// NADIE lo vende**, el pedido nace publicado en `/encargos` (la lista pública) para que los vendedores
// de Chimbote lo vean, y al visitante se le ofrece dejar su WhatsApp para que le escriban.
//
// ⚠️ LO IMPORTANTE: antes de publicar se comprueba la OFERTA REAL en 3 niveles (título de producto →
//    rubro por frases de unión → nombre/descripción de tienda). El número que da esta página cuenta
//    TIENDAS CON ESA PALABRA EN EL NOMBRE, y medido el 2026-09-15 resultó que la mitad de los
//    «0 resultados» eran falsos: «cerveza» daba 1 resultado con 18 tiendas y 19 productos que la
//    venden, «cámaras» y «ollas» daban 0 con 6 y 13 tiendas dentro. Si hay oferta, NO se publica
//    pedido: se le enseñan los productos que existen, que es justo lo que venía a buscar.
$psv_pedido = null;   // el pedido que se acaba de publicar (o sumar)
$psv_oferta = null;   // «no se publicó pedido porque SÍ hay quién lo venda»
// Se mira con 0 resultados (nadie lo tiene) Y con 1-3 (poquísimos lo tienen): los dos casos que
// pidió el jefe — *«no ha encontrado resultados suficientes o solamente ha encontrado dos o tres»*.
// Con más de 3 resultados el visitante ya tiene dónde elegir y no se publica nada: una lista lleno
// de pedidos de cosas que sí existen no sirve a nadie.
if ($termino !== '' && $total <= 3) {
    require_once __DIR__ . '/includes/pedidos_sin_vendedor.php';
    if (!pedidos_tablas_ok()) pedidos_instalar();   // defensivo: sin permisos no hace nada
    $psv_res = pedido_publicar($termino, [
        'origen'      => 'buscador',
        'resultados'  => $total,
        'distrito_id' => $distrito_ids[0] ?? null,
        'rubro_id'    => $categoria_id ?: null,
    ]);
    $psv_pedido = $psv_res['pedido'] ?? null;
    if (($psv_res['motivo'] ?? '') === 'hay_oferta') $psv_oferta = $psv_res['oferta'];
}

// Los 4 más notorios van ARRIBA (en "cerca de mí" son los 4 más cercanos).
$primeros = array_slice($resultados, 0, 4);
$resto    = array_slice($resultados, 4);

$radio_etiqueta = $radio > 0
    ? ($radio == (int)$radio ? (string)(int)$radio : rtrim(rtrim(number_format($radio, 1, '.', ''), '0'), '.'))
    : '';

$radio_form = $radio_auto ? 'auto' : (string)$radio_etiqueta;   // se conserva al cambiar de filtro

// ====== 🧰 SERVICIOS A DOMICILIO (2026-09-10) ======
// Van en su PROPIO bloque, debajo de las tiendas: para un servicio a domicilio la distancia a
// su base no significa lo mismo (el cliente no va allí: el técnico va a la casa). Su tarjeta
// muestra las ZONAS donde atiende. Ver GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md §5.1.
// ⚠️ En modo automático se usa el TOPE de la escalera (10 km), NO el radio anunciado: la
// escalera puede haber bajado a 2 km porque en el centro hay 20 tiendas, y eso recortaba
// a los servicios (su base suele estar más lejos que la tienda de la esquina).
$radio_dom = 0.0;
if ($modo_cerca) {
    $radio_dom = $radio_auto ? (float)end($CERCA_ESCALERA) : (($radio > 0) ? (float)$radio : (float)end($CERCA_ESCALERA));
}
$domicilios = buscar_domicilio_en_zona($lat, $lng, $radio_dom, $termino, $categoria_slug, $distritos_sel, 6);

// 🧰 2️⃣bis: las tiendas a domicilio que YA salieron arriba (en la lista de resultados) no se repiten
// en este bloque: la misma tarjeta dos veces en la misma página es ruido.
if ($domicilios && $resultados) {
    $ya_en_lista = [];
    foreach ($resultados as $r) { $ya_en_lista[(int)$r['id']] = true; }
    $domicilios = array_values(array_filter($domicilios, function ($d) use ($ya_en_lista) {
        return empty($ya_en_lista[(int)$d['id']]);
    }));
}

// ====== 📦 PRODUCTOS DE LAS TIENDAS QUE SALIERON (pedido del jefe, 2026-09-19) ======
// Textual: *«si solamente aparecen tres resultados, ¿por qué no llenar también con sus productos? …
// esta regla aplica cuando se muestran menos de 15 resultados: si se mostraran más de 15 tiendas ya no
// habría necesidad, pero como no, se rellena con productos de las tiendas mencionadas o de las tiendas
// que aparezcan»*.
//
// Se toman los productos de **todas las tiendas que la página enseña** (la lista de resultados, el rubro
// que acompaña y los servicios a domicilio) y se ordenan por lo más parecido a lo escrito, lo destacado
// y el precio. Van en la misma tarjeta de producto del resto del sitio (`.card-producto`, 5 por fila) y
// enlazan a la ficha de su tienda, igual que la portada y el desplegable.
$productos_tiendas = [];
// ⚠️ 2026-09-20: si la búsqueda llegó POR LOS PRODUCTOS (`$busqueda_producto`), ese bloque ya enseña lo
// que el visitante vino a buscar y este relleno sobraría (serían dos bloques de productos seguidos).
if ($total > 0 && !$busqueda_producto) {
    // Las tiendas que la página ENSEÑA de verdad: la lista + el rubro que acompaña + los de domicilio.
    $ids_tiendas = [];
    foreach (array_merge($resultados, $domicilios, (array)($rubro_acompana['tiendas'] ?? [])) as $t) {
        $tid = (int)($t['id'] ?? 0);
        if ($tid > 0) { $ids_tiendas[$tid] = true; }
    }
    // La regla del jefe son 15: con menos de 15 tiendas en pantalla se rellena con sus productos.
    if ($ids_tiendas && count($ids_tiendas) < 15) {
        $ids_sql = implode(',', array_map('intval', array_keys($ids_tiendas)));
        try {
            // `sql_producto_vigente()` deja fuera lo que ya caducó (pescaderías: «lo que trajo hoy»).
            $st_prod = db()->prepare(
                "SELECT s.id, s.titulo, s.precio, s.unidad, s.imagen, s.destacado, s.disponible_hasta,
                        n.id AS negocio_id, n.nombre AS negocio_nombre, n.slug AS negocio_slug,
                        c.icono AS categoria_icono,
                        (SELECT pf.ruta FROM directorio_producto_fotos pf
                          WHERE pf.producto_id = s.id ORDER BY pf.orden ASC, pf.id ASC LIMIT 1) AS foto
                   FROM directorio_servicios s
                   INNER JOIN directorio_negocios n ON n.id = s.negocio_id AND n.estado = 'activo'
                   LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                  WHERE s.negocio_id IN ($ids_sql) AND s.activo = 1
                    AND " . sql_producto_vigente('s') . "
                  ORDER BY (s.titulo LIKE ?) DESC, s.destacado DESC, (s.precio > 0) DESC,
                           n.vistas_count DESC, s.id DESC
                  LIMIT 15");
            $st_prod->execute(['%' . $termino . '%']);
            $productos_tiendas = $st_prod->fetchAll();
        } catch (Throwable $e) {
            $productos_tiendas = [];   // si algo falla, la búsqueda sale igual que siempre
        }
    }
}

// 🔔 Aviso al jefe: alguien buscó algo (con o sin resultados)
if ($termino !== '') {
    $filtros = [];
    if ($categoria_slug) $filtros[] = 'rubro: ' . $categoria_slug;
    if ($distritos_sel)  $filtros[] = 'distrito: ' . implode('+', $distritos_sel);
    if ($modo_cerca) {
        $filtros[] = $radio_auto
            ? 'cerca de mí (automático, hasta ' . $radio_etiqueta . ' km)'
            : 'cerca de mí (' . $radio_etiqueta . ' km)';
    }

    aviso(count($resultados) ? 'busqueda' : 'busqueda_vacia', [
        'termino'    => $termino,
        'resultados' => count($resultados),
        'url'        => url('buscar.php?q=' . rawurlencode($termino)),
        'filtros'    => implode(' · ', $filtros),
        'clave'      => 'busq:' . mb_strtolower($termino),
        'dedupe_min' => AVISOS_DEDUPE_BUSQUEDA_MIN,   // nombre REAL de la constante (includes/config_avisos.php)
        'resumen'    => $termino . ' (' . count($resultados) . ' res.)',
    ]);

    // 🏆 RÉCORDS DEL SITIO (pedido del jefe, 2026-09-13): además de avisarte por Telegram, la
    // búsqueda queda GUARDADA en la base para la pestaña «🏆 Récords del sitio» del Súper Admin.
    // ⚠️ Antes solo se guardaban las búsquedas de usuarios CON SESIÓN
    // (`directorio_historial_busqueda`, ver `guardar_busqueda_usuario()`), así que las de los
    // visitantes anónimos —la inmensa mayoría— se perdían y no había forma de saber qué pide la
    // gente. Aquí se guarda el término TAL CUAL (y su versión sin tildes para agrupar), cuántos
    // resultados salieron (0 = oportunidad) y el rubro al que apunta la palabra.
    require_once __DIR__ . '/includes/metricas.php';
    metrica_busqueda($termino, count($resultados), [
        'origen'       => 'web',
        'categoria_id' => !empty($busqueda_por_rubro['id'])
                            ? (int)$busqueda_por_rubro['id']
                            : ($categoria_id ?: null),
    ]);
}

// ====== 📊 LA BÚSQUEDA DETALLADA (orden del jefe, 2026-09-13) ======
// Textual: «cuando alguien busca "chancho" muestra los resultados y si alguien da ENTER no debe
// mostrar el primer resultado… debe mostrar la búsqueda detallada y más fuerte de ese término…
// lo máximo de detalle posible como número de productos, ventas, visitas… así el cliente puede ver
// estadísticas de esa búsqueda de manera rápida».
//
// Eso es lo que hace este bloque: con la palabra que escribió el visitante se calcula la FICHA
// COMPLETA del término (tiendas, productos, visitas, pedidos, soles, precios, distritos, rubros y
// cuánta gente busca lo mismo). El motor está en `includes/busqueda_detalle.php` (todo en try/catch:
// si algo falla, la página sale igual).
//
// 📍 DÓNDE SE PINTA (orden del jefe, 2026-09-14): AL FINAL de la página, cuando el visitante ya vio
//    TODAS las opciones de búsqueda (los resultados, «cerca de mí», el rubro que acompaña y los
//    servicios a domicilio) y recién ahí se pone a leer las estadísticas. Textual: *«lo primero que
//    debe mostrar son los resultados… esta información me agrada, me sirve, pero debe aparecer abajo…
//    tenemos que darle al usuario lo que está buscando»*. Antes iba arriba (2026-09-13) para que el
//    ENTER no dejara al visitante en la ficha del primer resultado; eso ya no hace falta porque el
//    ENTER tampoco abre ya ninguna ficha. Arriba queda solo la LÍNEA-TEASER (`$detalle_teaser`), que
//    avisa que los números están abajo y baja hasta ellos con el ancla `#estadisticas`.
//
// ⚠️ Se calcula DESPUÉS de `metrica_busqueda()` A PROPÓSITO: así el contador de «veces buscado»
//    incluye esta búsqueda y el visitante ve que él también acaba de sumar.
$detalle_html  = '';
$detalle_datos = null;
if ($termino !== '') {
    require_once __DIR__ . '/includes/busqueda_detalle.php';
    $detalle_datos = busqueda_detalle_datos($termino, [
        'rubro' => $busqueda_por_rubro ?? null,   // el rubro de las «frases de unión», si lo hubo
    ]);
    $detalle_html = busqueda_detalle_html($detalle_datos);
}

// 📊 LA LÍNEA-TEASER (orden del jefe, 2026-09-14): una sola línea arriba de los resultados avisa que
// los números de la búsqueda están AL FINAL y baja hasta el panel. Solo se pinta si el panel existe
// de verdad: si no hay nada que contar, no se anuncia nada.
$detalle_teaser = '';
if ($detalle_html !== '' && is_array($detalle_datos)) {
    $dz    = is_array($detalle_datos['demanda'] ?? null) ? $detalle_datos['demanda'] : null;
    $veces = $dz ? (int)$dz['veces'] : 0;
    $n_t   = (int)($detalle_datos['tiendas']['n'] ?? 0);
    $n_p   = (int)($detalle_datos['productos']['n'] ?? 0);
    if ($veces > 1) {
        $detalle_teaser = '📊 A «' . e($termino) . '» lo buscaron ' . busqueda_detalle_num($veces)
                        . ' veces aquí: mira las estadísticas';
    } elseif ($n_t > 0 || $n_p > 0) {
        $detalle_teaser = '📊 Los números de «' . e($termino) . '»: ' . busqueda_detalle_num($n_t)
                        . ' tienda' . ($n_t === 1 ? '' : 's') . ' y ' . busqueda_detalle_num($n_p)
                        . ' producto' . ($n_p === 1 ? '' : 's');
    } elseif (!empty($detalle_datos['rubro_sala']['nombre'])) {
        $detalle_teaser = '📊 «' . e($termino) . '» apunta a '
                        . e((string)$detalle_datos['rubro_sala']['nombre']) . ': mira las estadísticas';
    } else {
        $detalle_teaser = '📊 Mira las estadísticas de «' . e($termino) . '»';
    }
}

$categorias = obtener_categorias();
$distritos  = obtener_distritos_visibles();

// Nombre del rubro ya elegido (para que el campo lo muestre al cargar) y lista para el buscador
// predictivo de rubros (se pinta como JSON al final de la página y la lee el JS con Fuse.js).
$categoria_actual_nombre = '';
$rubros_js = [];
foreach ($categorias as $c) {
    $rubros_js[] = ['n' => $c['nombre'], 's' => $c['slug'], 'i' => $c['icono']];
    if ($categoria_slug !== '' && $c['slug'] === $categoria_slug) $categoria_actual_nombre = $c['nombre'];
}

// Los 4 botones de distrito, en una sola fila del celular (pedido del jefe 2026-09-10):
// "Nuevo Chimbote" se muestra como "Nvo Chim" en el celular (el nombre completo, en PC).
$dist_corto = ['nuevo-chimbote' => 'Nvo Chim'];

// Nombre de los distritos encendidos, para el título: "… en Chimbote y Coishco".
$dist_txt = '';
$nombres_dist = [];
foreach ($distritos as $d) {
    if (in_array($d['slug'], $distritos_sel, true)) $nombres_dist[] = $d['nombre'];
}
if ($nombres_dist) {
    $ultimo_dist = array_pop($nombres_dist);
    $dist_txt = $nombres_dist ? implode(', ', $nombres_dist) . ' y ' . $ultimo_dist : $ultimo_dist;
}

// Título de la página: el jefe pidió (2026-09-10) que al lado de la lupa NO se vea el
// "21 negocios para autos". El dato no se pierde: va como texto SOLO para lectores de pantalla y
// buscadores (clase .sr-solo), y el título de la pestaña del navegador ya dice "Buscar: autos".
if ($modo_cerca) {
    $h1_oculto = $total
        ? $total . ' negocio' . ($total === 1 ? '' : 's') . ' cerca de ti' . ($dist_txt !== '' ? ' (' . $dist_txt . ')' : '')
        : 'Negocios cerca de ti';
} else {
    $h1_oculto = $total
        ? $total . ' negocio' . ($total === 1 ? '' : 's')
          . ($termino !== '' ? ' para «' . $termino . '»' : ' encontrados')
          . ($dist_txt !== '' ? ' en ' . $dist_txt : '')
        : 'Resultados de búsqueda';
}

$titulo_pagina = $modo_cerca ? 'Negocios cerca de ti' : ($termino ? "Buscar: $termino" : 'Buscar negocios');
$descripcion_pagina = 'Encuentra negocios cerca de tu ubicación o busca por rubro y distrito en Chimbote y la provincia del Santa.';

// 🔗 CANONICAL (2026-09-16, SEO): esta página se duplica sola con cada filtro (`q`, `cat`, `dist`,
// la ubicación del visitante…), y todas se verían iguales ante Google. SOLO la versión limpia (sin
// filtros y sin ubicación) se declara a sí misma; con filtros no se declara nada y decide Google,
// que es lo prudente cuando el contenido sí cambia. La versión limpia es la que va en el sitemap.
if (!$modo_cerca && $termino === '' && $categoria_slug === '' && !$distritos_sel) {
    $canonical_url = url('buscar.php');
}

// Misma URL pero sin la ubicación (para "Quitar ubicación").
$qs = [];
if ($termino !== '')        $qs['q']    = $termino;
if ($categoria_slug !== '') $qs['cat']  = $categoria_slug;
if ($distritos_sel)         $qs['dist'] = $distritos_sel;
$url_sin_geo = url('buscar.php') . ($qs ? '?' . http_build_query($qs) : '');

// ====== Tarjeta de negocio (se pinta en los 2 bloques: los 4 primeros y el resto) ======
$tarjeta = function (array $n) use ($modo_cerca) {
    ?>
    <a href="<?= url_negocio($n['slug']) ?>" class="card-negocio">
        <div class="card-negocio__imagen">
            <?= img_tag($n['imagen_portada'] ?? '', $n['nombre'], ['sizes' => '(max-width: 640px) 46vw, 300px', 'onerror' => "this.src='" . url('assets/img/sin-foto.svg') . "'"]) ?>
            <?php if (!empty($n['categoria_icono'])): ?>
                <span class="card-negocio__badge"><?= e($n['categoria_icono']) ?> <?= e($n['categoria_nombre']) ?></span>
            <?php endif; ?>
        </div>
        <div class="card-negocio__body">
            <h3 class="card-negocio__titulo"><?= e($n['nombre']) ?></h3>
            <?php // 🧰 2️⃣bis (2026-09-19): la tienda «a domicilio» que entró a la lista se anuncia como
                  // tal —no tiene local— y su distrito se sigue viendo al lado. ?>
            <?php $es_domicilio = (($n['ubicacion_tipo'] ?? '') === 'domicilio'); ?>
            <?php if ($es_domicilio || !empty($n['distrito_nombre'])): ?>
                <div class="card-negocio__categoria"><?php
                    if ($es_domicilio) echo '🧰 A domicilio';
                    if ($es_domicilio && !empty($n['distrito_nombre'])) echo ' · ';
                    if (!empty($n['distrito_nombre'])) echo '📍 ' . e($n['distrito_nombre']);
                ?></div>
            <?php endif; ?>
            <?php if ($modo_cerca): ?>
                <span class="card-negocio__dist">⚡ <?= e(distancia_txt($n['distancia_m'] ?? 0)) ?></span>
            <?php endif; ?>
        </div>
    </a>
    <?php
};

// ====== 🧰 Tarjeta de SERVICIO A DOMICILIO (su propio bloque, debajo de las tiendas) ======
$tarjeta_domicilio = function (array $n) {
    $zonas = trim((string)($n['zonas_txt'] ?? ''));
    if ($zonas === '') $zonas = (string)($n['distrito_nombre'] ?? '');
    ?>
    <a href="<?= url_negocio($n['slug']) ?>" class="card-negocio">
        <div class="card-negocio__imagen">
            <?= img_tag($n['imagen_portada'] ?? '', $n['nombre'], ['sizes' => '(max-width: 640px) 46vw, 300px', 'onerror' => "this.src='" . url('assets/img/sin-foto.svg') . "'"]) ?>
            <span class="card-negocio__badge">🧰 A domicilio</span>
        </div>
        <div class="card-negocio__body">
            <h3 class="card-negocio__titulo"><?= e($n['nombre']) ?></h3>
            <?php if ($zonas !== ''): ?>
                <div class="card-negocio__categoria">📍 <?= e($zonas) ?></div>
            <?php endif; ?>
            <?php if ($n['distancia_m'] !== null): ?>
                <span class="card-negocio__dist">🧰 base a <?= e(distancia_txt((float)$n['distancia_m'])) ?></span>
            <?php else: ?>
                <span class="card-negocio__dist">🧰 va a tu casa</span>
            <?php endif; ?>
        </div>
    </a>
    <?php
};

// ====== 📦 Tarjeta de PRODUCTO (la usan el bloque de la búsqueda y el de relleno) ======
// 🆕 2026-09-20: se saca a una sola definición para que las dos salgan idénticas (antes el bloque de
// relleno llevaba el marcado pegado a mano y el nuevo bloque lo habría duplicado).
$tarjeta_producto = function (array $p) {
    ?>
    <a href="<?= e(url_negocio((string)$p['negocio_slug'])) ?>" class="card-producto">
        <div class="card-producto__img">
            <?= img_tag(imagen_producto($p), (string)$p['titulo'], [
                'sizes'   => '(max-width: 560px) 39vw, (max-width: 900px) 31vw, 190px',
                'onerror' => "this.src='" . url('assets/img/sin-foto.svg') . "'",
            ]) ?>
        </div>
        <div class="card-producto__body">
            <div class="card-producto__titulo"><?= e((string)$p['titulo']) ?></div>
            <div class="card-producto__negocio"><?= e((string)($p['categoria_icono'] ?? '')) ?> <?= e((string)$p['negocio_nombre']) ?></div>
            <div class="card-producto__precio"><?= formato_precio((float)$p['precio']) ?><?php
                if (!empty($p['unidad'])): ?> <span class="uni">/ <?= e((string)$p['unidad']) ?></span><?php endif; ?></div>
            <?= vigencia_chip_html($p) ?>
        </div>
    </a>
    <?php
};

// Texto del botón de ubicación, PERSONALIZADO con lo que el usuario busca (pedido del jefe 2026-09-10):
// buscando "autos" dice "Ver autos cerca de mí"; buscando "pollo a la brasa", "Ver pollo a la brasa
// cerca de mí". Sin término, el texto genérico.
$cerca_btn_txt = ($termino !== '')
    ? 'Ver ' . mb_substr($termino, 0, 30) . ' cerca de mí'
    : 'Ver negocios cerca de mí';

// ====== 🛒🆕 «AGREGAR MI TIENDA» (pedido del jefe, 2026-09-16) ======
// Textual: *«cuando muestre los resultados, abajo del botón "ver cumpleaños cerca de mí" pon un botón
// negro con texto rosado que diga "agregar mi tienda". Lo que hará este botón es: si no está logueado,
// loguearlo; y si ya está logueado, abrir el maestro crear tienda y le dirá "dame las imágenes para el
// producto" —en este caso cumpleaños— y lo agregará el producto a su tienda».*
//
// Cómo se cumple: el término que se buscó viaja al maestro en `prod`. El asistente (🛠️ El maestro, en
// su modo «agregar un producto», `/crear-tienda?modo=producto&prod=cumpleaños`) **ya no pregunta cómo
// se llama el producto**: nace con ese nombre y lo primero que pide son SUS FOTOS («📷 Mándame la foto
// de cumpleaños»); con la foto y el precio lo deja publicado en la tienda del dueño.
// Si el visitante no tiene sesión, el botón lo manda primero a entrar y `login.php` lo devuelve solo
// al maestro con el producto ya dicho (el `redirect` conserva `modo` y `prod`).
$prod_maestro = ($termino !== '') ? mb_substr($termino, 0, 40) : '';
// El enlace DIRECTO lleva el término ya codificado; el del LOGIN lleva la ruta sin codificar (la
// codifica `rawurlencode` entera: si se codificara dos veces, el `%` viajaría como `%25`).
$url_maestro  = 'crear-tienda?modo=producto' . ($prod_maestro !== '' ? '&prod=' . rawurlencode($prod_maestro) : '');
$url_agregar_tienda = usuario_actual()
    ? url($url_maestro)
    : url('login.php?redirect=' . rawurlencode('crear-tienda?modo=producto' . ($prod_maestro !== '' ? '&prod=' . $prod_maestro : '')));

// ====== El bloque de ubicación, que va DESPUÉS de los 4 primeros resultados ======
$panel_abierto = ($modo_cerca && $total === 0);   // sin resultados, las opciones ya se ven
ob_start();
?>
<div class="cerca-bloque">
<?php if (!$modo_cerca): ?>
    <button type="button" class="cerca-btn" id="btnCerca">
        <span class="cerca-btn__t"><?= cerca_pin_svg('cerca-btn__pin') ?><span class="cerca-btn__tx"><?= e($cerca_btn_txt) ?></span></span>
    </button>
    <p class="cerca-estado" id="estadoCerca">Toca el botón y comparte tu ubicación.</p>
<?php else: ?>
    <div class="cerca-bloque__cab">
        <span class="cerca-bloque__ico" aria-hidden="true">📍</span>
        <p class="cerca-bloque__txt">
            <?php if ($total === 0): ?>
                No encontramos nada a menos de <?= e($radio_etiqueta) ?> km<?= $radio_auto ? ' (la búsqueda automática llegó hasta los 10 km)' : '' ?>. Prueba ampliar los kilómetros aquí abajo, o cambia el rubro y el distrito.
            <?php elseif ($radio_auto && $cerca_completo && $radio <= 2): ?>
                <strong><?= $total ?> negocios a menos de 2 km.</strong> No hizo falta ampliar la búsqueda.
            <?php elseif ($radio_auto && $cerca_completo): ?>
                Ampliamos la búsqueda automáticamente de 2 km a <strong><?= e($radio_etiqueta) ?> km</strong> para juntar los <?= $total ?> negocios.
            <?php elseif ($radio_auto): ?>
                Buscamos hasta 10 km y solo hay <strong><?= $total ?> negocio<?= $total === 1 ? '' : 's' ?></strong>. Puedes ampliar a 20 o 30 km.
            <?php else: ?>
                <strong><?= $total ?> negocio<?= $total === 1 ? '' : 's' ?> a menos de <?= e($radio_etiqueta) ?> km</strong> de tu ubicación.
            <?php endif; ?>
        </p>
    </div>
    <div class="cerca-bloque__acciones">
        <button type="button" class="cerca-btn2" id="btnAmpliar" aria-expanded="<?= $panel_abierto ? 'true' : 'false' ?>" aria-controls="panelAmpliar">🔎 Ampliar búsqueda</button>
        <a class="cerca-quitar" href="<?= e($url_sin_geo) ?>">✕ Quitar ubicación</a>
    </div>
    <form method="get" action="<?= url('buscar.php') ?>" class="cerca-panel" id="panelAmpliar"<?= $panel_abierto ? '' : ' hidden' ?>>
        <?php if ($termino !== ''): ?><input type="hidden" name="q" value="<?= e($termino) ?>"><?php endif; ?>
        <?php if ($categoria_slug !== ''): ?><input type="hidden" name="cat" value="<?= e($categoria_slug) ?>"><?php endif; ?>
        <?php foreach ($distritos_sel as $ds): ?><input type="hidden" name="dist[]" value="<?= e($ds) ?>"><?php endforeach; ?>
        <input type="hidden" name="lat" value="<?= e((string)$lat) ?>">
        <input type="hidden" name="lng" value="<?= e((string)$lng) ?>">
        <p class="cerca-panel__lbl">Buscar a:</p>
        <div class="cerca-panel__chips">
            <?php foreach ($CERCA_RADIOS as $rk): ?>
                <label class="filtro-chip">
                    <input type="radio" name="radio" value="<?= (int)$rk ?>" <?= (!$radio_auto && (float)$radio === (float)$rk) ? 'checked' : '' ?>>
                    <span><?= (int)$rk ?> km</span>
                </label>
            <?php endforeach; ?>
        </div>
    </form>
<?php endif; ?>

    <!-- 🛒🆕 AGREGAR MI TIENDA (pedido del jefe, 2026-09-16): va SIEMPRE al final del bloque de
         ubicación, o sea DEBAJO del botón «Ver … cerca de mí». Es la otra cara de la búsqueda: el que
         busca su rubro suele ser el que lo vende. Botón negro con letra rosada (§CSS .agrega-tienda). -->
    <a class="agrega-tienda" href="<?= e($url_agregar_tienda) ?>">Agregar mi tienda</a>
</div>
<?php
$bloque_cerca = ob_get_clean();

// La clase "pg-buscar" va en el envoltorio del contenido (no en <body>, que vive en
// includes/header.php: esa cabecera la comparten TODAS las páginas y no se toca desde aquí).
// Las reglas que afectan a la cabecera usan `body:has(.pg-buscar)` → solo aplican en esta página.
include __DIR__ . '/includes/header.php';
?>
<style>
/* ============================================================
   PÁGINA DE BÚSQUEDA (buscar.php) — móvil primero
   El objetivo: que en el celular el PRIMER resultado se vea sin
   tener que bajar. Por eso el título es una sola línea, la cabecera
   va compacta y el bloque de ubicación vive DESPUÉS de los 4 primeros.
   ============================================================ */
.buscar-titulo{font-size:19px;line-height:1.25;font-weight:800;color:var(--color-texto);margin:0 0 10px}
.buscar-titulo__ico{font-size:23px;line-height:1}

/* ---- Rubro predictivo: se escribe y salen los rubros parecidos (no más lista de 61) ---- */
.rubro-wrap{position:relative;display:flex;align-items:center}
.rubro-wrap .rubro-input{width:100%;font-size:16px;padding:10px 40px 10px 12px}
.rubro-x{position:absolute;right:6px;top:50%;transform:translateY(-50%);width:28px;height:28px;border:0;
  border-radius:50%;background:#f1e7dc;color:var(--marca-granate);font:800 13px/1 inherit;cursor:pointer}
.rubro-x:hover{background:#e6d7c5}
.rubro-sug{position:absolute;top:calc(100% + 4px);left:0;right:0;z-index:320;background:#fff;
  border:1px solid var(--color-borde);border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,.22);
  max-height:56vh;overflow-y:auto}
.rubro-sug__item{display:block;width:100%;text-align:left;background:#fff;border:0;
  border-bottom:1px solid var(--color-borde);padding:11px 12px;font:600 15px/1.3 inherit;
  color:var(--color-texto);cursor:pointer}
.rubro-sug__item:last-child{border-bottom:0}
.rubro-sug__item:hover,.rubro-sug__item.is-sel{background:var(--marca-crema)}
.rubro-sug__vacio{padding:11px 12px;font-size:13.5px;color:var(--color-texto-claro)}

/* ---- Filtros: "Categorías" en desplegable + distritos en botones (son 4) ---- */
.buscar-filtros{background:#fff;border:1px solid var(--color-borde);border-radius:14px;
  box-shadow:var(--sombra-tarjeta);padding:10px 12px;margin:0 0 12px;display:grid;gap:8px}
.buscar-filtros__fila{display:flex;align-items:center;gap:10px}
.buscar-filtros__fila--col{flex-direction:column;align-items:stretch;gap:6px}
.buscar-filtros__lbl{font-size:11.5px;font-weight:800;letter-spacing:.05em;text-transform:uppercase;
  color:var(--color-texto-claro);white-space:nowrap}
.buscar-filtros__pista{font-weight:600;letter-spacing:0;text-transform:none;color:#9a8f80}
.buscar-filtros select{flex:1;min-width:0;font-size:15px;padding:9px 10px}

/* Distritos: 4 botones que se ENCIENDEN y se APAGAN (ya no hay "Todos": sin ninguno se ven todos).
   Se combinan (Chimbote + Coishco = los de ambos) y en el celular entran los 4 en UNA fila. */
.buscar-filtros__chips{display:flex;flex-wrap:wrap;gap:6px}
.filtro-chip{position:relative;display:inline-flex;cursor:pointer;-webkit-tap-highlight-color:transparent}
.filtro-chip input{position:absolute;opacity:0;pointer-events:none}
.filtro-chip > span{position:relative;display:inline-flex;align-items:center;min-height:34px;padding:6px 11px;box-sizing:border-box;
  border:1.5px solid rgba(109,7,26,.25);background:#fff;border-radius:999px;
  font-size:13.5px;font-weight:700;color:var(--color-primario);white-space:nowrap}
/* Encendido: fondo granate + palomita en la esquina (la palomita va ABSOLUTA para que el botón
   NO se haga más ancho al marcarlo y los 4 sigan entrando en una fila). */
.filtro-chip input:checked + span{background:var(--color-primario);border-color:var(--color-primario);color:#fff;
  box-shadow:0 2px 6px rgba(109,7,26,.28)}
.filtro-chip input:checked + span::after{content:"✓";position:absolute;top:-7px;right:-3px;width:17px;height:17px;
  border-radius:50%;background:var(--marca-naranja);color:#fff;font-size:11px;font-weight:800;
  line-height:17px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.3)}
.filtro-chip input:focus-visible + span{outline:2px solid var(--marca-naranja);outline-offset:2px}
.filtro-chip__corto{display:none}

/* ====== "Agrega tu negocio aquí" (va al FINAL de los resultados) ======
   El que busca su rubro suele ser el que lo vende: al terminar la lista se le invita a publicar. */
.invita-negocio{margin:18px 0 6px;padding:14px;border:1.5px dashed rgba(109,7,26,.35);
  border-radius:14px;background:var(--marca-crema);text-align:center}
.invita-negocio__t{margin:0 0 6px;font-size:16px;font-weight:800;color:var(--marca-granate)}
.invita-negocio__s{margin:0 0 12px;font-size:13.5px;line-height:1.45;color:var(--color-texto)}
.invita-negocio__btn{display:block;background:var(--marca-granate);color:#fff;text-decoration:none;
  font-size:16.5px;font-weight:800;padding:13px 16px;border-radius:12px;
  box-shadow:0 4px 14px rgba(109,7,26,.28)}
.invita-negocio__btn:hover{background:var(--marca-granate-osc)}
.invita-negocio__cam{display:inline-block;margin-top:9px;font-size:13px;font-weight:700;color:var(--color-primario)}

/* ---- Bloque "ver negocios cerca" (entre los 4 primeros resultados y el resto) ---- */
.cerca-bloque{background:#fff;border:1px solid var(--color-borde);border-radius:14px;
  box-shadow:var(--sombra-tarjeta);padding:12px;margin:14px 0}
/* 🎨 NARANJA (2026-09-14, pedido del jefe): el verde se confundía con los botones de WhatsApp.
   Mismo degradado, anillo y efectos que includes/btn_cerca.php (guía §6.4). */
.cerca-btn{position:relative;overflow:hidden;display:flex;flex-direction:column;align-items:center;
  justify-content:center;gap:2px;width:100%;box-sizing:border-box;color:#fff;border:0;border-radius:14px;
  background:linear-gradient(105deg,#a3123c 0%,#d0312a 48%,#f0861c 100%);
  box-shadow:0 0 0 3px #fbd7a4,0 0 18px rgba(240,134,28,.42),0 6px 14px rgba(109,7,26,.25),
    inset 0 2px 0 rgba(255,255,255,.42),inset 0 -3px 0 rgba(90,6,20,.30);
  padding:13px 16px;cursor:pointer;font-family:inherit;text-align:center}
.cerca-btn::after{content:'';position:absolute;inset:0;pointer-events:none;
  background:linear-gradient(112deg,rgba(255,255,255,.30) 0%,rgba(255,255,255,.08) 40%,rgba(255,255,255,0) 64%)}
.cerca-btn>span{position:relative;z-index:1}
.cerca-btn:disabled{opacity:.65;cursor:wait}
/* Texto GRANDE y en varias líneas si hace falta (pedido del jefe: "no importa que ocupe dos filas") */
.cerca-btn__t{font-size:19px;font-weight:800;line-height:1.22;display:flex;align-items:center;
  justify-content:center;gap:9px;width:100%;text-shadow:0 2px 3px rgba(90,6,20,.45)}
.cerca-btn__tx{display:block;text-wrap:balance}
.cerca-btn__pin{flex:0 0 auto;width:23px;height:23px;color:#fff;filter:drop-shadow(0 1px 2px rgba(90,6,20,.5))}
.cerca-estado{margin:8px 0 0;font-size:13px;line-height:1.4;color:var(--color-texto-claro);text-align:center}
.cerca-bloque__cab{display:flex;align-items:flex-start;gap:9px}
.cerca-bloque__ico{font-size:19px;line-height:1.2}
.cerca-bloque__txt{margin:0;font-size:14px;line-height:1.4;color:var(--color-texto)}
.cerca-bloque__acciones{display:flex;flex-wrap:wrap;align-items:center;gap:10px;margin-top:11px}
.cerca-btn2{background:var(--marca-crema);color:var(--marca-granate);border:1.5px solid var(--color-borde);
  border-radius:999px;padding:9px 15px;font:700 14px/1 inherit;cursor:pointer}
.cerca-btn2:hover{background:#f2e4d0}
.cerca-quitar{color:var(--color-primario);font-size:13px;font-weight:700;text-decoration:none}
.cerca-panel{margin:11px 0 0;padding-top:11px;border-top:1px dashed var(--color-borde)}
.cerca-panel__lbl{margin:0 0 7px;font-size:11.5px;font-weight:800;letter-spacing:.05em;
  text-transform:uppercase;color:var(--color-texto-claro)}
.cerca-panel__chips{display:flex;flex-wrap:wrap;gap:7px}

/* ---- 🛒🆕 «AGREGAR MI TIENDA» (pedido del jefe, 2026-09-16) ----
   Botón NEGRO con letra ROSADA, DEBAJO del botón de ubicación («Ver … cerca de mí»). Lleva al
   maestro (🛠️ El maestro) con el producto ya dicho: sin sesión pasa primero por el login y vuelve
   solo aquí. Móvil primero: ancho completo, 16 px (nunca menos, para que no haga zoom el celular). */
.agrega-tienda{display:block;width:100%;margin-top:10px;padding:13px 14px;border-radius:12px;
  background:#111;color:#ff5ea8;font-size:16px;line-height:1.2;font-weight:800;letter-spacing:.2px;
  text-align:center;text-decoration:none;-webkit-tap-highlight-color:transparent;
  box-shadow:0 4px 14px rgba(0,0,0,.25)}
.agrega-tienda:hover{background:#000;color:#ff86bd}
.agrega-tienda:focus-visible{outline:3px solid #ff5ea8;outline-offset:2px}
.agrega-tienda:active{transform:translateY(1px)}

/* ---- 📊 LA BÚSQUEDA DETALLADA (.bz-*) — orden del jefe, 2026-09-13 ----
   La que ve el visitante que escribe y pulsa ENTER: los números de su término (tiendas, productos,
   visitas, pedidos, soles, precios y demanda). 📍 Desde el 2026-09-14 va AL FINAL de la página
   (después de todos los resultados y antes de «Agrega tu negocio aquí»): primero lo que el cliente
   busca, y el detalle cuando ya vio todas las opciones. Móvil primero: los números grandes entran de
   a DOS por fila y el detalle se despliega con <details> (sin JS). */
.bz{background:#fff;border:1px solid var(--color-borde);border-radius:14px;
  box-shadow:var(--sombra-tarjeta);padding:12px;margin:0 0 12px}
.bz__cab{margin:0 0 10px}
.bz__t{margin:0 0 4px;font-size:17px;line-height:1.25;font-weight:800;color:var(--marca-granate)}
.bz__s{margin:0;font-size:13.5px;line-height:1.5;color:var(--color-texto)}
.bz__mini{display:block;margin-top:2px;font-size:11.5px;color:var(--color-texto-claro)}
.bz__aviso{margin:8px 0 0;padding:8px 10px;border-radius:10px;background:#fff7ed;
  border:1px solid #fdba74;color:#9a3412;font-size:12.5px;line-height:1.4}
.bz__kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:8px}
.bz-kpi{background:var(--marca-crema);border:1px solid var(--color-borde);border-radius:12px;padding:8px 10px}
.bz-kpi__n{font-size:20px;line-height:1.1;font-weight:800;color:var(--marca-granate)}
.bz-kpi__l{margin-top:2px;font-size:12.5px;font-weight:700;color:var(--color-texto)}
.bz-kpi__d{margin-top:1px;font-size:11.5px;line-height:1.3;color:var(--color-texto-claro)}
.bz__mas{margin-top:10px;padding-top:8px;border-top:1px dashed var(--color-borde)}
.bz__mas-sum{display:block;cursor:pointer;font-size:13.5px;font-weight:800;color:var(--color-primario);
  list-style:none}
.bz__mas-sum::-webkit-details-marker{display:none}
.bz__mas-sum::before{content:"▸ "}
.bz__mas[open] .bz__mas-sum::before{content:"▾ "}
.bz__cuerpo{display:grid;grid-template-columns:repeat(auto-fit,minmax(230px,1fr));gap:10px;margin-top:9px}
.bz-bloque{background:var(--marca-crema);border:1px solid var(--color-borde);border-radius:12px;padding:9px 11px}
.bz-bloque--ancho{grid-column:1/-1}
.bz-bloque__t{margin:0 0 6px;font-size:11.5px;font-weight:800;letter-spacing:.05em;
  text-transform:uppercase;color:var(--color-texto-claro)}
.bz-bloque__p{margin:0;font-size:13.5px;line-height:1.45}
.bz-dato{display:flex;justify-content:space-between;gap:10px;padding:3px 0;font-size:13px;line-height:1.4;
  border-bottom:1px solid rgba(0,0,0,.06)}
.bz-dato:last-child{border-bottom:0}
.bz-dato > span{color:var(--color-texto-claro)}
.bz-dato > b{text-align:right;color:var(--color-texto)}
.bz-fila{display:grid;grid-template-columns:1fr 52px 34px;align-items:center;gap:7px;padding:2px 0;font-size:13px}
.bz-fila__n{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.bz-fila__b{height:7px;border-radius:999px;background:rgba(109,7,26,.12);overflow:hidden}
.bz-fila__b > i{display:block;height:100%;border-radius:999px;background:var(--marca-granate)}
.bz-fila__v{text-align:right;font-weight:800;color:var(--marca-granate)}
.bz-top{display:grid;gap:6px}
.bz-top__item{display:block;padding:7px 9px;background:#fff;border:1px solid var(--color-borde);
  border-radius:10px;text-decoration:none}
.bz-top__n{display:block;font-size:13.5px;font-weight:700;color:var(--color-texto)}
.bz-top__m{display:block;margin-top:1px;font-size:11.5px;color:var(--color-texto-claro)}
.bz__pie{margin:9px 0 0;font-size:11.5px;line-height:1.4;color:var(--color-texto-claro)}

/* ---- Separador antes del resto de resultados ---- */
.buscar-mas{margin:16px 0 10px;font-size:12.5px;font-weight:700;color:var(--color-texto-claro);
  display:flex;align-items:center;gap:9px}
.buscar-mas::after{content:"";flex:1;height:1px;background:var(--color-borde)}

/* ---- Aviso de las «frases de unión» del rubro (2026-09-13): cuando la búsqueda por texto no
   encontraba nada y la respuesta salen las tiendas del rubro («clavos» → Ferreterías). ---- */
.buscar-nota{margin:14px 0 4px;padding:11px 13px;border-radius:12px;background:var(--color-fondo-suave,#f4f6f8);
  border:1px solid var(--color-borde);font-size:16px;line-height:1.45;color:var(--color-texto)}
.buscar-nota a{color:var(--color-primario);font-weight:700}

/* ---- 🧹 La nota de la LIMPIEZA (2026-09-14): en una línea se le dice al visitante qué se entendió
   de su búsqueda («escribiste "comprar clavos" y busco "clavos"»). Va pegada al título, antes de los
   resultados, y en letra cómoda de celular. ---- */
.buscar-nota--limpia{margin:10px 0 0;padding:9px 12px;background:var(--marca-crema);
  border-color:rgba(109,7,26,.18);font-size:14.5px;line-height:1.4}
.buscar-nota--limpia + .buscar-nota--limpia{margin-top:6px}

/* ---- 📊 LA LÍNEA-TEASER del panel de estadísticas (orden del jefe, 2026-09-14): avisa en una línea
   que los números de la búsqueda están AL FINAL y baja hasta ellos (ancla #estadisticas). Es un
   enlace, no un bloque de números: arriba mandan los resultados. ---- */
.buscar-teaser{display:flex;align-items:center;gap:9px;margin:10px 0 0;padding:10px 12px;
  border-radius:12px;background:var(--marca-crema);border:1px dashed rgba(109,7,26,.32);
  font-size:14.5px;line-height:1.35;font-weight:700;color:var(--marca-granate);text-decoration:none}
.buscar-teaser:hover{background:#f2e4d0}
.buscar-teaser__tx{flex:1;min-width:0}
.buscar-teaser__ir{flex:0 0 auto;font-size:16px;line-height:1}
/* El salto al ancla no debe dejar el panel debajo de la cabecera pegajosa. */
#estadisticas{scroll-margin-top:96px}

/* ---- CELULAR: 2 tarjetas por fila ----
   ⚠️ LA CABECERA NO SE TOCA AQUÍ (2026-09-10, orden del jefe: "la cabecera olvídate de la cabecera",
   la está programando otra sesión para TODO el sitio). Aquí solo se ajusta el contenido de la página. */
@media (max-width:640px){
  .buscar-titulo{font-size:17px}
  /* 📊 La búsqueda detallada en el celular: números de a DOS por fila y el detalle en una columna
     (con la letra del panel, que se lee sin zoom). */
  .bz{padding:11px}
  .bz__t{font-size:16px}
  .bz__kpis{grid-template-columns:repeat(2,minmax(0,1fr));gap:7px}
  .bz-kpi{padding:7px 9px}
  .bz-kpi__n{font-size:18px}
  .bz-kpi__l{font-size:12px}
  .bz__cuerpo{grid-template-columns:1fr}
  /* Los 4 distritos en UNA sola fila: 4 columnas exactas y "Nuevo Chimbote" → "Nvo Chim" */
  .buscar-filtros__chips{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:5px}
  .buscar-filtros__chips .filtro-chip{width:100%}
  .buscar-filtros__chips .filtro-chip > span{width:100%;justify-content:center;font-size:12.5px;padding:6px 4px;min-height:32px}
  .filtro-chip__largo{display:none}
  .filtro-chip__corto{display:inline}
  .pg-buscar .grid-negocios{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
  .pg-buscar .card-negocio__body{padding:9px 9px 10px}
  .pg-buscar .card-negocio__titulo{font-size:14px;line-height:1.22;margin-bottom:3px}
  .pg-buscar .card-negocio__categoria{font-size:11px;margin-bottom:6px}
  .pg-buscar .card-negocio__badge{font-size:10px;padding:2px 7px;max-width:calc(100% - 16px);
    overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
  .pg-buscar .card-negocio__meta{font-size:11px;gap:6px}
  .pg-buscar .card-negocio__dist{font-size:11px;padding:2px 8px;margin-top:4px}
}
</style>

<!-- Envoltorio de esta página: da el ámbito a las reglas .pg-buscar (tarjetas de 2 columnas,
     cabecera compacta del celular…). Las demás páginas del sitio no se enteran. -->
<div class="pg-buscar">

<h1 class="buscar-titulo"><span class="buscar-titulo__ico" aria-hidden="true"><?= $modo_cerca ? '📍' : '🔍' ?></span><span class="sr-solo"><?= e($h1_oculto) ?></span></h1>

<!-- ====== 🧹 LO QUE SE ENTENDIÓ DE LA BÚSQUEDA (mando del jefe, 2026-09-14) ======
     Si el visitante escribió «comprar clavos», «dónde hay cerveza» o «sabes que quiero comprar
     cerveza», aquí se le dice en UNA línea que se está buscando «clavos» / «cerveza»: las palabras de
     mando solo acompañan. Siempre se le da la puerta de atrás (buscar la frase tal cual). -->
<?php if ($q_escrito !== '' && $q_escrito !== $termino): ?>
    <p class="buscar-nota buscar-nota--limpia">
        🧹 Escribiste <strong>«<?= e($q_escrito) ?>»</strong> y estoy buscando
        <strong>«<?= e($termino) ?>»</strong>:
        <?php if (!empty($limpieza['quitaron'])): ?>
            «<?= e(implode('», «', array_slice($limpieza['quitaron'], 0, 4))) ?>»
            <?= count($limpieza['quitaron']) === 1 ? 'es una palabra que solo acompaña' : 'son palabras que solo acompañan' ?>
            la búsqueda, no parte de ella.
        <?php else: ?>
            las palabras de mando (comprar, dónde hay, quién tiene…) solo acompañan la búsqueda, no son
            parte de ella.
        <?php endif; ?>
        <a href="<?= e(url('buscar.php?q=' . rawurlencode($q_escrito) . '&literal=1')) ?>">Buscar «<?= e(mb_substr($q_escrito, 0, 40)) ?>» tal cual →</a>
    </p>
<?php endif; ?>
<?php if ($busqueda_jerga): ?>
    <p class="buscar-nota buscar-nota--limpia">
        🗣️ Por acá a <strong>«<?= e(implode('», «', $busqueda_jerga['de'])) ?>»</strong> le decimos
        <strong>«<?= e(implode('», «', $busqueda_jerga['a'])) ?>»</strong>, así que te muestro eso.
    </p>
<?php endif; ?>
<?php if ($busqueda_telefono): ?>
    <p class="buscar-nota buscar-nota--limpia">
        🔢 Lo que escribiste es un <strong>número de teléfono</strong>: te muestro
        <?= $total === 1 ? 'la tienda que lo tiene' : 'las tiendas que lo tienen' ?>.
    </p>
<?php endif; ?>

<!-- ====== 📊 LA LÍNEA-TEASER DE LAS ESTADÍSTICAS (orden del jefe, 2026-09-14) ======
     UNA línea, no el panel: le dice al visitante cuánta gente busca lo mismo (o cuántas tiendas y
     productos hay) y lo baja hasta el panel del final. El panel completo ya NO va aquí: primero lo que
     el cliente está buscando (los resultados de abajo) y las estadísticas al terminar de verlos. -->
<?php if ($detalle_teaser !== ''): ?>
    <a class="buscar-teaser" href="#estadisticas">
        <span class="buscar-teaser__tx"><?= $detalle_teaser ?></span>
        <span class="buscar-teaser__ir" aria-hidden="true">👇</span>
    </a>
<?php endif; ?>

<!-- Filtros: rubro en desplegable y distritos como botones. Sin botón "Buscar": se aplica al tocar. -->
<form method="get" action="<?= url('buscar.php') ?>" class="buscar-filtros" id="filtrosBuscar">
    <?php if ($termino !== ''): ?><input type="hidden" name="q" value="<?= e($termino) ?>"><?php endif; ?>
    <?php if ($modo_cerca): ?>
        <input type="hidden" name="lat" value="<?= e((string)$lat) ?>">
        <input type="hidden" name="lng" value="<?= e((string)$lng) ?>">
        <input type="hidden" name="radio" value="<?= e($radio_form) ?>">
    <?php endif; ?>

    <div class="buscar-filtros__fila buscar-filtros__fila--col">
        <span class="buscar-filtros__lbl">Categoría</span>
        <!-- Rubro PREDICTIVO (pedido del jefe 2026-09-10): antes era un desplegable con los 61 rubros
             y era una lista interminable. Ahora se escribe y van saliendo los parecidos (Fuse.js,
             el mismo motor del buscador de arriba): "sap" → Zapaterías, Zapatillas… -->
        <div class="rubro-wrap" id="rubroWrap">
            <input type="text" id="rubroInput" class="form__input rubro-input" autocomplete="off"
                   role="combobox" aria-expanded="false" aria-controls="rubroSug" aria-autocomplete="list"
                   placeholder="Escribe el rubro: zapatillas, pollería, autos…"
                   value="<?= e($categoria_actual_nombre) ?>">
            <input type="hidden" name="cat" id="catValor" value="<?= e($categoria_slug) ?>">
            <button type="button" class="rubro-x" id="rubroX" <?= $categoria_slug === '' ? 'hidden' : '' ?> aria-label="Quitar el rubro">✕</button>
            <div class="rubro-sug" id="rubroSug" role="listbox" hidden></div>
        </div>
    </div>

    <div class="buscar-filtros__fila buscar-filtros__fila--col">
        <span class="buscar-filtros__lbl">Distrito <span class="buscar-filtros__pista">(toca los que quieras)</span></span>
        <div class="buscar-filtros__chips">
            <?php foreach ($distritos as $d): ?>
                <?php $corto = $dist_corto[$d['slug']] ?? ''; ?>
                <label class="filtro-chip">
                    <input type="checkbox" name="dist[]" value="<?= e($d['slug']) ?>"
                           aria-label="Filtrar por <?= e($d['nombre']) ?>"
                           <?= in_array($d['slug'], $distritos_sel, true) ? 'checked' : '' ?>>
                    <span><?php if ($corto !== ''): ?><span class="filtro-chip__largo"><?= e($d['nombre']) ?></span><span class="filtro-chip__corto"><?= e($corto) ?></span><?php else: ?><?= e($d['nombre']) ?><?php endif; ?></span>
                </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Solo para que ENTER siga enviando el formulario: no se ve (el botón "Buscar" sobraba). -->
    <button type="submit" class="sr-solo" aria-label="Aplicar los filtros" tabindex="-1">Aplicar</button>
</form>

<?php if ($total === 0): ?>
    <!-- 🎨 Copy amigable (orden del jefe, 2026-09-13): nada de «Sin resultados» seco; se le habla
         como a un cliente y se le da una salida (el chat y las palabras vecinas). -->
    <div class="empty-state">
        <div class="empty-state__icono"><?= $modo_cerca ? '📍' : '🔍' ?></div>
        <div class="empty-state__titulo"><?= $modo_cerca ? 'No hay nada tan cerquita' : 'Uy, todavía no tengo nada de eso' ?></div>
        <?php if ($modo_cerca): ?>
            <p>Vuelve a compartir tu ubicación (a veces el GPS se pierde) o amplía la búsqueda aquí abajo.</p>
        <?php else: ?>
            <?php // ✏️ 2026-09-17: este texto mandaba a preguntarle a «El ninja» (el chat de la
                  // esquina). El chat ya no vive en todo el sitio: es 🧭 El guía, el anfitrión de cada
                  // tienda, y abre DENTRO de la ficha de un negocio. Se dice la verdad. ?>
            <p>Prueba con otra palabra, otro rubro u otro distrito: abriendo cualquier tienda,
               su <strong>🧭 guía</strong> te dice al instante qué vende, a cuánto y dónde queda.</p>
        <?php endif; ?>
    </div>
    <?= $bloque_cerca ?>

    <?php
    // 🛒 ENCARGOS (2026-09-15): las dos caras del «no encontré nada».
    //   · $psv_pedido → de verdad nadie lo vende: su pedido ya quedó publicado en la lista.
    //   · $psv_oferta → el buscador no encontró tiendas con ese nombre, pero el directorio SÍ tiene
    //                   el producto (o su rubro): se le enseña eso y no se publica ningún pedido.
    // ⚠️ El motor se carga SOLO cuando hubo término y 0 resultados (arriba), así que aquí se comprueba
    //    que exista: una búsqueda vacía SIN término (solo con filtros) no debe romper la página.
    if (function_exists('pedido_publicado_html')) {
        echo pedido_publicado_html($psv_pedido);
        echo pedido_oferta_html($psv_oferta ?: [], $termino);
    }
    ?>
<?php else: ?>
    <?php if ($busqueda_por_rubro): ?>
        <!-- 🏷️ La palabra no estaba en ninguna ficha, pero SÍ es una «frase de unión» del rubro -->
        <p class="buscar-nota">😉 No vi ninguna tienda con «<?= e($termino) ?>» en el nombre, pero en
            <a href="<?= url('categoria/' . urlencode((string)$busqueda_por_rubro['slug'])) ?>"><?= e($busqueda_por_rubro['nombre']) ?></a>
            sí lo tienen — mira las que tengo:</p>
    <?php endif; ?>
    <?php if ($busqueda_domicilio): ?>
        <!-- 🧰 2️⃣bis (2026-09-19): lo escrito coincidió con tiendas «a domicilio». Se dice, porque su
             tarjeta no lleva dirección (no hay local) y el visitante tiene que saber por dónde va. -->
        <p class="buscar-nota">🧰 Estas tiendas <strong>van a tu casa</strong>: no tienen local abierto,
            así que se las atiende por WhatsApp o por teléfono.</p>
    <?php endif; ?>
    <?php if ($busqueda_producto): ?>
        <!-- 📦 2️⃣ter (2026-09-20): lo escrito no está en el NOMBRE de ninguna tienda, pero SÍ en el
             título de sus productos. Se dice en una línea, porque si no el visitante no entiende por qué
             estas tiendas salen cuando su ficha no dice «alquiler de sillas» en ninguna parte. -->
        <p class="buscar-nota">📦 Estas tiendas no llevan «<?= e(mb_substr($termino, 0, 40)) ?>» en su
            nombre, pero <strong>lo venden</strong>: las encontré por sus productos 👇</p>
    <?php endif; ?>
    <!-- 🛒 ENCARGOS (2026-09-15): el visitante encontró MUY POCO (1-3). Si de verdad la
         oferta es escasa, su pedido quedó publicado y se le dice aquí, ANTES de los resultados (los
         pocos resultados que sí hay van debajo: no se le quita nada de lo que ya tenía). -->
    <?php
    if (function_exists('pedido_publicado_html')) {
        echo pedido_publicado_html($psv_pedido);
        // 🏪 `nombre` (2026-09-19): lo escrito es el nombre de una tienda y esas tiendas YA están en la
        // lista de arriba — repetirlas aquí sería la misma tarjeta dos veces. Solo se pinta cuando no
        // hubo resultados (bloque `$total === 0`), donde sí aporta.
        if (($psv_oferta['nivel'] ?? '') !== 'nombre') {
            echo pedido_oferta_html($psv_oferta ?: [], $termino);
        }
    }
    ?>
    <div class="grid-negocios">
        <?php foreach ($primeros as $n) { $tarjeta($n); } ?>
    </div>

    <?= $bloque_cerca ?>

    <?php if ($resto): ?>
        <p class="buscar-mas">Y hay <?= count($resto) ?> más<?= $termino !== '' ? ' de «' . e($termino) . '»' : '' ?> 👇</p>
        <div class="grid-negocios">
            <?php foreach ($resto as $n) { $tarjeta($n); } ?>
        </div>
    <?php endif; ?>

    <?php // 📦 LOS PRODUCTOS QUE COINCIDEN CON LO ESCRITO (2️⃣ter, 2026-09-20). Es lo que el visitante
          // vino a buscar cuando escribió un producto («alquiler de sillas»): van justo debajo de las
          // tiendas que los venden y NO dependen del número de tiendas (antes solo salían con menos de
          // 15 y por eso casi nunca se veían). ?>
    <?php if ($busqueda_producto): ?>
        <p class="buscar-mas">📦 Productos que coinciden con «<?= e(mb_substr($termino, 0, 40)) ?>»
            (<?= count($busqueda_producto) ?>) 👇</p>
        <div class="grid-productos">
            <?php foreach ($busqueda_producto as $p) { $tarjeta_producto($p); } ?>
        </div>
    <?php endif; ?>

    <?php // 🔢 HAY MUCHAS MÁS (2026-09-19): la lista se corta en 24 y hasta hoy no lo decía en ninguna
          // parte. Se dice el número real y se lleva a la página del rubro, que las muestra TODAS con
          // paginador (Bodegas: 208). ?>
    <?php if (!$modo_cerca && $hay_busqueda && $total_clasico > $mostradas): ?>
        <p class="buscar-mas">
            🔢 Hay <strong><?= number_format($total_clasico) ?></strong> tiendas con lo que buscas y aquí van
            las <?= (int)$mostradas ?> más vistas.
            <?php if ($rubro_mas): ?>
                Mira <a href="<?= e(url_categoria((string)$rubro_mas['slug'])) ?>"
                        style="font-weight:800;color:var(--marca-granate)">todas las de <?= e($rubro_mas['nombre']) ?></a> 👉
            <?php else: ?>
                <a href="<?= e(url('rubros')) ?>"
                   style="font-weight:800;color:var(--marca-granate)">Míralas por rubro</a> 👉
            <?php endif; ?>
        </p>
    <?php endif; ?>

    <?php if ($rubro_acompana): ?>
        <!-- 🏷️ EL RUBRO QUE ACOMPAÑA (2026-09-14): el texto dio pocas tiendas y la palabra es de un
             rubro, así que se ofrecen las del rubro en su PROPIO bloque (pedido del jefe: «no solo los
             que venden jugo de piña, también los principales vendedores»).
             🆕 2026-09-18: y cuando lo escrito es una FRASE que no describe a ninguna de esas fichas
             («reforzamiento escolar», «clases de inglés para 7 años»), el bloque se anuncia así: la frase
             es cosa del rubro. -->
        <?php if (!empty($rubro_acompana['por_frase'])): ?>
            <p class="buscar-mas">😉 Ninguna ficha se llama «<?= e($termino) ?>», pero es cosa de
                <a href="<?= e(url('categoria/' . urlencode((string)$rubro_acompana['rubro']['slug']))) ?>" style="font-weight:800;color:var(--marca-granate)"><?= e($rubro_acompana['rubro']['nombre']) ?></a>: mira también estas 👇</p>
        <?php else: ?>
            <p class="buscar-mas">🏷️ «<?= e($termino) ?>» es palabra de
                <a href="<?= e(url('categoria/' . urlencode((string)$rubro_acompana['rubro']['slug']))) ?>" style="font-weight:800;color:var(--marca-granate)"><?= e($rubro_acompana['rubro']['nombre']) ?></a>: mira también estas 👇</p>
        <?php endif; ?>
        <div class="grid-negocios">
            <?php foreach ($rubro_acompana['tiendas'] as $n) { $tarjeta($n); } ?>
        </div>
    <?php endif; ?>
<?php endif; ?>

<?php if ($domicilios): ?>
    <!-- 🧰 SERVICIOS A DOMICILIO: no tienen local, van a la casa del cliente (2026-09-10) -->
    <p class="buscar-mas">🧰 Servicios que van a tu zona</p>
    <div class="grid-negocios">
        <?php foreach ($domicilios as $d) { $tarjeta_domicilio($d); } ?>
    </div>
<?php endif; ?>

<?php if ($productos_tiendas): ?>
    <!-- 📦 PRODUCTOS DE LAS TIENDAS QUE SALIERON (pedido del jefe, 2026-09-19): con menos de 15 tiendas
         la página se rellena con lo que venden — así el visitante que buscó «mayciel» ve sus tiendas Y
         su catálogo, no una página a medias. Con 15 o más tiendas ya hay dónde elegir y no se pinta. -->
    <p class="buscar-mas">📦 Lo que venden estas tiendas<?= $termino !== ''
        ? ' (' . count($productos_tiendas) . ' de «' . e(mb_substr($termino, 0, 30)) . '»)' : ''
        ?> 👇</p>
    <div class="grid-productos">
        <?php foreach ($productos_tiendas as $p) { $tarjeta_producto($p); } ?>
    </div>
<?php endif; ?>

<!-- ====== 📊 LA BÚSQUEDA DETALLADA DEL TÉRMINO (orden del jefe, 2026-09-13; AL FINAL desde el 2026-09-14) ======
     Aquí termina la búsqueda: el visitante ya vio el rubro que le tocaba, las tiendas, el bloque de
     «cerca de mí», el rubro que acompaña y los servicios a domicilio. Recién ahora se le da la ficha
     completa de su palabra (tiendas, productos, visitas, pedidos, soles, precios, distritos, rubros y
     cuánta gente busca lo mismo): es lo que pidió el jefe — *«lo primero que debe mostrar son los
     resultados… la información estadística debe aparecer abajo, cuando el usuario ya terminó de ver
     todas las opciones de búsqueda»*.
     El ancla `#estadisticas` es la que usa la línea-teaser de arriba; el motor va por `stats_q()`
     (try/catch) y si no hay nada que contar, `$detalle_html` viene vacío y no se pinta nada. -->
<?php if ($detalle_html !== ''): ?>
    <p class="buscar-mas">📊 Todo lo que hay detrás de «<?= e(mb_substr($termino, 0, 40)) ?>»</p>
    <div id="estadisticas"><?= $detalle_html ?></div>
<?php endif; ?>

<!-- ====== AL FINAL DE LOS RESULTADOS: "Agrega tu negocio aquí" (pedido del jefe 2026-09-10) ======
     El que busca su rubro suele ser el que lo vende: si buscó "zapatos", es probable que venda zapatos.
     Por eso, cuando se acaban los resultados, se le invita a publicar su negocio (registro clásico) o a
     hacerlo con la cámara (Caminante). Sale también cuando la búsqueda no encontró nada. -->
<div class="invita-negocio">
    <p class="invita-negocio__t">➕ Agrega tu negocio aquí</p>
    <p class="invita-negocio__s">
        <?php if ($termino !== ''): ?>
            Si vendes <strong>«<?= e(mb_substr($termino, 0, 40)) ?>»</strong>, tus clientes ya te están buscando.
        <?php else: ?>
            Tus clientes ya están buscando lo que vendes.
        <?php endif; ?>
        Es gratis: apareces en el buscador, en tu rubro y en el mapa.
    </p>
    <a class="invita-negocio__btn" href="<?= url('crear-tienda') ?>">🛠️ Crear mi tienda con El maestro</a>
    <a class="invita-negocio__cam" href="<?= url('caminante/') ?>">📸 O agrégalo con la cámara en 2 minutos</a>
</div>

</div><!-- /.pg-buscar -->

<!-- Lista de rubros para el buscador predictivo (la lee el JS con Fuse.js). JSON_HEX_TAG por seguridad. -->
<script type="application/json" id="rubrosJson"><?= json_encode($rubros_js, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) ?></script>

<script>
(function () {
  'use strict';

  // ============ RUBRO PREDICTIVO (pedido del jefe 2026-09-10) ============
  // Antes: desplegable con los 61 rubros (lista interminable). Ahora: se escribe ("sap") y van saliendo
  // los parecidos ("Zapaterías y Arreglo de Calzado", "Zapatillas"…). Al tocar uno, se busca.
  // ⚠️ Se probó Fuse.js (el motor del buscador de arriba) sobre esta lista y NO sirve: con "sap"
  //    devolvía "Transporte", "Vape Shops" o "Spa para Mascotas". Por eso el comparador de abajo:
  //    1) prefijo y "contiene" del nombre, y 2) distancia de Levenshtein tratando como IGUALES las
  //    letras que en el Perú suenan igual (s=z, b=v, c=s, y=i, g=j): así "sap" → "Zapaterías".
  var rubroWrap = document.getElementById('rubroWrap');
  var rubroInput = document.getElementById('rubroInput');
  var catValor   = document.getElementById('catValor');
  var rubroSug   = document.getElementById('rubroSug');
  var rubroX     = document.getElementById('rubroX');
  var formFiltros = document.getElementById('filtrosBuscar');
  var rubros = [];

  function sinTildes(t) {
    return String(t || '').toLowerCase()
      .replace(/[áàäâã]/g, 'a').replace(/[éèëê]/g, 'e').replace(/[íìïî]/g, 'i')
      .replace(/[óòöô]/g, 'o').replace(/[úùüû]/g, 'u').replace(/ñ/g, 'n');
  }

  // Letras que suenan igual en el Perú (y erratas típicas del teclado): cuentan como la misma.
  var SUENAN_IGUAL = { s: 'z', z: 's', b: 'v', v: 'b', c: 's', k: 'c', y: 'i', g: 'j', j: 'g' };
  function mismaLetra(a, b) { return a === b || SUENAN_IGUAL[a] === b; }

  /** Distancia de Levenshtein (cuántos cambios hacen falta para convertir una palabra en otra). */
  function distancia(a, b) {
    var m = a.length, n = b.length, i, j;
    if (!m) return n;
    if (!n) return m;
    var prev = [], cur = [];
    for (j = 0; j <= n; j++) prev[j] = j;
    for (i = 1; i <= m; i++) {
      cur[0] = i;
      for (j = 1; j <= n; j++) {
        cur[j] = Math.min(prev[j] + 1, cur[j - 1] + 1, prev[j - 1] + (mismaLetra(a.charAt(i - 1), b.charAt(j - 1)) ? 0 : 1));
      }
      for (j = 0; j <= n; j++) prev[j] = cur[j];
    }
    return prev[n];
  }

  /** Rubros parecidos a lo que se escribió, del más parecido al menos. */
  function buscarRubros(txt) {
    var t = sinTildes(txt).trim();
    if (t.length < 2) return [];
    // Cuántos errores se toleran según lo que se escribió: pocas letras = casi exacto.
    var permitido = t.length <= 3 ? 1 : (t.length <= 5 ? 2 : 3);
    var hallados = [];

    rubros.forEach(function (r) {
      var nombre = sinTildes(r.n);
      var pos = nombre.indexOf(t);
      if (pos === 0) { hallados.push({ r: r, d: 0 }); return; }      // el nombre empieza igual
      if (pos > 0)   { hallados.push({ r: r, d: 0.5 }); return; }    // lo tiene dentro

      var palabras = nombre.split(/[^a-z0-9]+/), mejor = 99;
      palabras.forEach(function (p) {
        if (!p) return;
        // La PRIMERA letra manda: si cambia (y no suena igual), es otro rubro. Así "tortas" no
        // se confunde con "Hospitales y Postas" y "sap" SÍ encuentra "Zapaterías".
        if (!mismaLetra(t.charAt(0), p.charAt(0))) return;
        // Se compara contra el inicio de la palabra con el largo justo, uno menos y hasta dos más:
        // así "polllo" (letra de más) encuentra "Pollerías" y "piza" encuentra "Pizzerías".
        for (var k = -1; k <= 2; k++) {
          var largo = t.length + k;
          if (largo < 2 || largo > p.length) continue;
          var d = distancia(t, p.substr(0, largo));
          if (d < mejor) mejor = d;
        }
      });
      if (mejor <= permitido) hallados.push({ r: r, d: 1 + mejor });
    });

    hallados.sort(function (a, b) { return a.d - b.d || a.r.n.localeCompare(b.r.n); });
    return hallados.slice(0, 8).map(function (x) { return x.r; });
  }

  // A la vista para pruebas sin navegador (__busq_16_prueba_rubros.js) y por si otro módulo lo usa.
  window.ChimboteRubros = { buscar: buscarRubros, normalizar: sinTildes, distancia: distancia, rubros: function () { return rubros; } };

  try { rubros = JSON.parse(document.getElementById('rubrosJson').textContent) || []; } catch (e) { rubros = []; }

  if (rubroWrap) {
    function cerrarSug() {
      rubroSug.hidden = true;
      rubroSug.innerHTML = '';
      rubroInput.setAttribute('aria-expanded', 'false');
    }

    function pintar(lista) {
      rubroSug.innerHTML = '';
      if (!lista.length) {
        var p = document.createElement('p');
        p.className = 'rubro-sug__vacio';
        p.textContent = rubroInput.value.trim().length < 2
          ? 'Escribe dos letras o más para buscar el rubro.'
          : 'Ningún rubro se parece a «' + rubroInput.value.trim() + '».';
        rubroSug.appendChild(p);
        rubroSug.hidden = false;
        rubroInput.setAttribute('aria-expanded', 'true');
        return;
      }
      lista.forEach(function (r, k) {
        var b = document.createElement('button');
        b.type = 'button';
        b.className = 'rubro-sug__item' + (k === 0 ? ' is-sel' : '');
        b.setAttribute('role', 'option');
        b.textContent = (r.i ? r.i + ' ' : '') + r.n;
        b.addEventListener('click', function () { elegir(r, true); });
        rubroSug.appendChild(b);
      });
      rubroSug.hidden = false;
      rubroInput.setAttribute('aria-expanded', 'true');
    }

    function elegir(r, buscarYa) {
      rubroInput.value = r.n;
      catValor.value = r.s;
      if (rubroX) rubroX.hidden = false;
      cerrarSug();
      if (buscarYa && formFiltros) formFiltros.submit();
    }

    /** Deja el campo y el valor oculto coherentes antes de enviar el formulario. */
    function resolverRubro() {
      var txt = rubroInput.value.trim();
      if (txt === '') { catValor.value = ''; if (rubroX) rubroX.hidden = true; return ''; }
      if (catValor.value) {
        // ¿sigue siendo el mismo rubro? (si escribió otra cosa, se recalcula)
        for (var i = 0; i < rubros.length; i++) {
          if (rubros[i].s === catValor.value && rubros[i].n === txt) return catValor.value;
        }
      }
      var mejor = buscarRubros(txt)[0] || null;
      catValor.value = mejor ? mejor.s : '';
      if (mejor) rubroInput.value = mejor.n;
      if (rubroX) rubroX.hidden = (catValor.value === '');
      return catValor.value;
    }
    window.__czResolverRubro = resolverRubro;   // la usan los botones de distrito antes de enviar

    rubroInput.addEventListener('input', function () {
      if (catValor.value) {
        // Escribió encima del rubro elegido: el filtro se recalcula al enviar.
        var esElMismo = false;
        for (var i = 0; i < rubros.length; i++) {
          if (rubros[i].s === catValor.value && rubros[i].n === rubroInput.value.trim()) esElMismo = true;
        }
        if (!esElMismo) { catValor.value = ''; if (rubroX) rubroX.hidden = true; }
      }
      pintar(buscarRubros(rubroInput.value));
    });

    rubroInput.addEventListener('keydown', function (ev) {
      if (ev.key === 'Enter') {
        ev.preventDefault();
        resolverRubro();
        if (formFiltros) formFiltros.submit();
      } else if (ev.key === 'Escape') {
        cerrarSug();
      }
    });

    rubroInput.addEventListener('focus', function () {
      if (rubroInput.value.trim().length >= 2) pintar(buscarRubros(rubroInput.value));
    });

    if (rubroX) {
      rubroX.addEventListener('click', function () {
        rubroInput.value = '';
        catValor.value = '';
        rubroX.hidden = true;
        cerrarSug();
        if (formFiltros) formFiltros.submit();
      });
    }

    document.addEventListener('click', function (ev) {
      if (!rubroWrap.contains(ev.target)) cerrarSug();
    });
  }

  // ---- Los filtros se aplican solos al tocar (el botón "Buscar" sobraba) ----
  // Los distritos son casillas que se encienden y se apagan: marcar Y desmarcar vuelven a buscar.
  document.querySelectorAll('.buscar-filtros, .cerca-panel').forEach(function (caja) {
    caja.addEventListener('change', function (ev) {
      var t = ev.target;
      if (!t) return;
      if (t.tagName === 'SELECT' || t.type === 'radio' || t.type === 'checkbox') {
        if (window.__czResolverRubro) window.__czResolverRubro();
        caja.submit();
      }
    });
  });

  // ---- "Ampliar búsqueda": muestra los radios (2/5/10/20/30 km) y ahí manda el usuario ----
  var btnAmpliar = document.getElementById('btnAmpliar');
  var panel = document.getElementById('panelAmpliar');
  if (btnAmpliar && panel) {
    btnAmpliar.addEventListener('click', function () {
      panel.hidden = !panel.hidden;
      btnAmpliar.setAttribute('aria-expanded', panel.hidden ? 'false' : 'true');
      if (!panel.hidden) {
        var foco = panel.querySelector('input[type="radio"]');
        if (foco) foco.focus({ preventScroll: true });
      }
    });
  }

  // ---- "Ver negocios cerca": pide la ubicación y busca con la escalera automática (radio=auto) ----
  var btn = document.getElementById('btnCerca');
  if (!btn) return;
  var estado = document.getElementById('estadoCerca');

  function decir(txt) { if (estado) estado.textContent = txt; }

  btn.addEventListener('click', function () {
    if (!('geolocation' in navigator)) {
      decir('Tu navegador no tiene ubicación. Escribe lo que buscas o elige un distrito.');
      return;
    }
    decir('Obteniendo tu ubicación…');
    btn.disabled = true;
    navigator.geolocation.getCurrentPosition(function (pos) {
      var p = new URLSearchParams(location.search);
      p.set('lat', pos.coords.latitude.toFixed(7));
      p.set('lng', pos.coords.longitude.toFixed(7));
      p.set('radio', 'auto');            // escalera 2 → 5 → 10 km hasta juntar 20 resultados
      location.href = location.pathname + '?' + p.toString();
    }, function (err) {
      btn.disabled = false;
      if (err && err.code === 1) {
        decir('No compartiste tu ubicación. Puedes escribir lo que buscas o elegir un distrito.');
      } else if (err && err.code === 2) {
        decir('No se pudo ubicar el GPS. Revisa que la ubicación esté activada en tu celular.');
      } else {
        decir('No pudimos obtener tu ubicación. Inténtalo de nuevo.');
      }
    }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 60000 });
  });
})();
</script>

<?php
// 📰 FICHAS RÁPIDAS DE NOTICIAS (al azar) — pedido del jefe (2026-09-13). Va al FINAL de la página
// (parte no principal: primero está lo que el visitante vino a buscar) y ofrece las noticias de la
// zona como una opción más. Si no hay noticias publicadas, el bloque no pinta nada.
require_once __DIR__ . '/includes/noticias_slide.php';
echo noticias_slide_html();
?>

<?php include __DIR__ . '/includes/footer.php'; ?>
