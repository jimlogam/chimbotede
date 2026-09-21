<?php
/**
 * includes/busqueda_detalle.php — 📊 LA BÚSQUEDA DETALLADA (orden del jefe, 2026-09-13)
 * ==================================================================================
 * Pedido del jefe, textual: *«cuando alguien busca "chancho" muestra los resultados y si alguien da
 * enter no debe mostrar el primer resultado… debe mostrar la búsqueda detallada y más fuerte de ese
 * término "chancho"… lo máximo de detalle posible, como número de productos, ventas, visitas, todo lo
 * que puedas darle a esos resultados… así el cliente puede ver estadísticas de esa búsqueda de manera
 * rápida»*.
 *
 * Qué es: la FICHA COMPLETA de un término de búsqueda. Cuando el visitante escribe «chancho» y
 * pulsa ENTER, además de la lista de resultados recibe **todo lo que el sitio sabe de esa palabra**:
 *
 *   🏪 tiendas que lo ofrecen (+ cuántas con WhatsApp y cuántas a domicilio)
 *   📦 productos que lo ofrecen (+ cuántas tiendas los venden)
 *   👁️ visitas acumuladas de esas tiendas (vistas_count)
 *   🛒 pedidos que recibieron (botones de pedir: WhatsApp de la ficha, consulta y carrito 🛒)
 *   💰 soles que mueven esos pedidos
 *   💵 precios (el más barato, el promedio y el más caro)
 *   ⭐ calificación promedio y opiniones de esos negocios
 *   📍 en qué distritos está y 🏷️ en qué rubros cae
 *   📈 cuánta gente busca esa misma palabra (demanda real, de la tabla `directorio_busquedas`)
 *
 * DE DÓNDE SALE CADA NÚMERO (nada inventado: todo es de la base del sitio)
 *   · Tiendas / productos / precios / visitas / rating → `directorio_negocios`, `directorio_servicios`.
 *   · Pedidos y soles ................................ `directorio_pedidos` (TABLA NUEVA del módulo de
 *     récords; si todavía no existe, el bloque simplemente no se pinta: no rompe nada).
 *   · Demanda de la palabra .......................... `directorio_busquedas` (una fila por búsqueda).
 *
 * ⚠️ Reglas que respeta (Reglas de Oro del proyecto)
 *   1. **Nada puede romper ni frenar el buscador**: TODAS las consultas van por `stats_q()`
 *      (try/catch: si algo falla, devuelve 0 y la página sigue igual).
 *   3. **Móvil primero**: el panel es una rejilla de números que entra en 2 columnas del celular.
 *   4. **Sin JS obligatorio**: el bloque se despliega con `<details>` nativo (funciona sin JavaScript).
 *   5. **No se duplica la verdad**: las tablas nuevas se preguntan con `metricas_tabla_lista()`, el
 *      mismo guardián que usa la pestaña 🏆 Récords del Súper Admin.
 *
 * Lo usa `buscar.php`. Para pintarlo: `busqueda_detalle_html(busqueda_detalle_datos($termino))`.
 * Los estilos (.bz-*) viven en la página que lo pinta (buscar.php), junto a los del buscador.
 */

require_once __DIR__ . '/metricas.php';   // metricas_tabla_lista(), metricas_norm(), metricas_num()

if (!function_exists('busqueda_detalle_tokens')) {
    /**
     * Las palabras útiles del término (mínimo 2 letras, máximo 4: lo mismo que hace el buscador).
     * «chancho a la caja china» → ['chancho', 'caja', 'china'].
     */
    function busqueda_detalle_tokens($termino) {
        $t = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower((string)$termino, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $t = array_values(array_filter($t, function ($x) { return mb_strlen($x) >= 2; }));
        $t = array_slice($t, 0, 4);
        // Una sola letra («a», «x»): se busca el término completo para no contar medio mundo.
        if (!$t && trim((string)$termino) !== '') $t = [mb_strtolower(trim((string)$termino), 'UTF-8')];
        return $t;
    }
}

if (!function_exists('busqueda_detalle_datos')) {
    /**
     * 📊 TODO lo que el sitio sabe de un término de búsqueda.
     *
     * @param string $termino  lo que escribió el visitante (tal cual).
     * @param array  $extra    ['rubro' => fila de rubro ya resuelta (opcional), 'limite' => int]
     * @return array           estructura lista para pintar. Nunca lanza excepción.
     */
    function busqueda_detalle_datos($termino, array $extra = []) {
        $termino = trim((string)$termino);
        $tokens  = busqueda_detalle_tokens($termino);

        $d = [
            'termino'   => $termino,
            'tokens'    => $tokens,
            'rubro'     => $extra['rubro'] ?? null,
            'relajada'  => false,      // true = hubo que aflojar (no todas las palabras juntas)
            'tiendas'   => ['n' => 0, 'vistas' => 0, 'rating' => 0.0, 'con_wsp' => 0,
                            'a_domicilio' => 0, 'destacadas' => 0, 'ultima' => null],
            'productos' => ['n' => 0, 'tiendas' => 0, 'con_precio' => 0, 'con_foto' => 0,
                            'pmin' => 0.0, 'pmax' => 0.0, 'pmed' => 0.0, 'ultimo' => null],
            'pedidos'   => null,       // null = no hay tabla de pedidos (o no hay nada que medir)
            'opiniones' => null,
            'demanda'   => null,
            'distritos' => [],
            'rubros'    => [],
            'top_tiendas'   => [],
            'top_productos' => [],
            'rubro_sala'    => null,   // tiendas y productos del rubro al que apunta la palabra
            'ahora'     => date('d/m/Y H:i'),
        ];
        if (!$tokens) return $d;

        // ---------------------------------------------------------------- condiciones de texto
        // TODAS las palabras juntas (lo preciso). Se usa para el titular y para los desgloses.
        $cond_and = [];
        $par_and  = [];
        foreach ($tokens as $t) {
            $cond_and[] = '(n.nombre LIKE ? OR n.descripcion LIKE ?)';
            $par_and[]  = '%' . $t . '%';
            $par_and[]  = '%' . $t . '%';
        }
        $cond_tiendas = implode(' AND ', $cond_and);

        // ALGUNA palabra (para los pedidos y el desglose por rubro/distrito: así una palabra de más
        // no deja el bloque en cero, igual que hace el chat con «clavo aca»).
        $cond_or = [];
        $par_or  = [];
        foreach ($tokens as $t) {
            $cond_or[] = '(n.nombre LIKE ? OR n.descripcion LIKE ?)';
            $par_or[]  = '%' . $t . '%';
            $par_or[]  = '%' . $t . '%';
        }
        $cond_tiendas_or  = implode(' OR ', $cond_or);
        $cond_titulo_and  = implode(' AND ', array_map(function () { return 's.titulo LIKE ?'; }, $tokens));
        $par_titulo_and   = array_map(function ($t) { return '%' . $t . '%'; }, $tokens);
        $cond_titulo_or   = implode(' OR ', array_map(function () { return 's.titulo LIKE ?'; }, $tokens));

        // ---------------------------------------------------------------- 1) 🏪 TIENDAS
        $sql_t = "SELECT COUNT(*) AS tiendas,
                         COALESCE(SUM(n.vistas_count),0) AS vistas,
                         ROUND(AVG(NULLIF(n.rating,0)),1) AS rating,
                         SUM(CASE WHEN n.whatsapp IS NOT NULL AND n.whatsapp <> '' THEN 1 ELSE 0 END) AS con_wsp,
                         SUM(CASE WHEN n.ubicacion_tipo = 'domicilio' THEN 1 ELSE 0 END) AS a_domicilio,
                         SUM(CASE WHEN n.destacado = 1 THEN 1 ELSE 0 END) AS destacadas,
                         MAX(n.creado_en) AS ultima
                  FROM directorio_negocios n
                  WHERE n.estado = 'activo' AND ";
        $f = stats_q($sql_t . $cond_tiendas, $par_and);
        // Si con TODAS las palabras no hay nada, se afloja (cualquiera de ellas) y se avisa en pantalla.
        if (!$f || (int)($f[0]['tiendas'] ?? 0) === 0) {
            $f2 = stats_q($sql_t . $cond_tiendas_or, $par_or);
            if ($f2 && (int)($f2[0]['tiendas'] ?? 0) > 0) { $f = $f2; $d['relajada'] = true; }
        }
        if ($f) {
            $d['tiendas'] = [
                'n'           => (int)$f[0]['tiendas'],
                'vistas'      => (int)$f[0]['vistas'],
                'rating'      => (float)$f[0]['rating'],
                'con_wsp'     => (int)$f[0]['con_wsp'],
                'a_domicilio' => (int)$f[0]['a_domicilio'],
                'destacadas'  => (int)$f[0]['destacadas'],
                'ultima'      => $f[0]['ultima'],
            ];
        }
        // La condición que quedó viva (la usa el resto de los bloques).
        $cond_final = $d['relajada'] ? $cond_tiendas_or : $cond_tiendas;
        $par_final  = $d['relajada'] ? $par_or : $par_and;

        // ---------------------------------------------------------------- 2) 📦 PRODUCTOS
        // Se busca en el TÍTULO del producto (lo que el producto ES) y se desglosa por tienda.
        $sql_p = "SELECT COUNT(*) AS productos,
                         COUNT(DISTINCT s.negocio_id) AS tiendas,
                         SUM(CASE WHEN s.precio > 0 THEN 1 ELSE 0 END) AS con_precio,
                         SUM(CASE WHEN (s.imagen IS NOT NULL AND s.imagen <> '') OR pf.producto_id IS NOT NULL
                                  THEN 1 ELSE 0 END) AS con_foto,
                         MIN(CASE WHEN s.precio > 0 THEN s.precio END) AS pmin,
                         MAX(CASE WHEN s.precio > 0 THEN s.precio END) AS pmax,
                         ROUND(AVG(CASE WHEN s.precio > 0 THEN s.precio END),1) AS pmed,
                         MAX(s.creado_en) AS ultimo
                  FROM directorio_servicios s
                  JOIN directorio_negocios n ON n.id = s.negocio_id AND n.estado = 'activo'
                  LEFT JOIN (SELECT producto_id FROM directorio_producto_fotos GROUP BY producto_id) pf
                         ON pf.producto_id = s.id
                  WHERE s.activo = 1 AND " . sql_producto_vigente('s') . " AND (";
        $fp = stats_q($sql_p . $cond_titulo_and . ")", $par_titulo_and);
        if (!$fp || (int)($fp[0]['productos'] ?? 0) === 0) {
            // Con TODAS las palabras no hay nada: se prueba con ALGUNA (una palabra de más ya no borra el bloque).
            $fp2 = stats_q($sql_p . $cond_titulo_or . ")", $par_titulo_and);
            if ($fp2 && (int)($fp2[0]['productos'] ?? 0) > 0) $fp = $fp2;
        }
        if ($fp) {
            $d['productos'] = [
                'n'          => (int)$fp[0]['productos'],
                'tiendas'    => (int)$fp[0]['tiendas'],
                'con_precio' => (int)$fp[0]['con_precio'],
                'con_foto'   => (int)$fp[0]['con_foto'],
                'pmin'       => (float)$fp[0]['pmin'],
                'pmax'       => (float)$fp[0]['pmax'],
                'pmed'       => (float)$fp[0]['pmed'],
                'ultimo'     => $fp[0]['ultimo'],
            ];
        }

        // ---------------------------------------------------------------- 3) 📍 DISTRITOS
        $d['distritos'] = stats_q(
            "SELECT COALESCE(d.nombre,'Sin distrito') AS nombre, d.slug, COUNT(*) AS n
               FROM directorio_negocios n
               LEFT JOIN directorio_distritos d ON d.id = n.distrito_id
              WHERE n.estado = 'activo' AND ($cond_final)
              GROUP BY n.distrito_id, d.nombre, d.slug ORDER BY n DESC LIMIT 6", $par_final);

        // ---------------------------------------------------------------- 4) 🏷️ RUBROS
        $d['rubros'] = stats_q(
            "SELECT COALESCE(c.nombre,'Sin rubro') AS nombre, c.icono, c.slug, COUNT(*) AS n
               FROM directorio_negocios n
               LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
              WHERE n.estado = 'activo' AND ($cond_final)
              GROUP BY n.categoria_id, c.nombre, c.icono, c.slug ORDER BY n DESC LIMIT 6", $par_final);

        // ---------------------------------------------------------------- 5) 🛒 PEDIDOS (tabla nueva)
        if (metricas_tabla_lista(METRICAS_TABLA_PEDIDOS)) {
            $par_ped = [];
            $or_ped  = [];
            foreach ($tokens as $t) {                       // tienda por su nombre/descripción…
                $or_ped[] = '(n.nombre LIKE ? OR n.descripcion LIKE ? OR s.titulo LIKE ?)';
                $like = '%' . $t . '%';
                array_push($par_ped, $like, $like, $like);
            }
            $fped = stats_q(
                "SELECT COUNT(*) AS n,
                        SUM(CASE WHEN p.tipo='pedido'   THEN 1 ELSE 0 END) AS carritos,
                        SUM(CASE WHEN p.tipo='consulta' THEN 1 ELSE 0 END) AS consultas,
                        SUM(CASE WHEN p.tipo='clic'     THEN 1 ELSE 0 END) AS clics,
                        COALESCE(SUM(p.total),0) AS monto,
                        COUNT(DISTINCT p.negocio_id) AS tiendas,
                        MAX(p.fecha) AS ultimo
                   FROM directorio_pedidos p
                   JOIN directorio_negocios n ON n.id = p.negocio_id AND n.estado = 'activo'
                   LEFT JOIN directorio_servicios s ON s.id = p.producto_id
                  WHERE (" . implode(' OR ', $or_ped) . ")", $par_ped);
            if ($fped) {
                $d['pedidos'] = [
                    'n'         => (int)$fped[0]['n'],
                    'carritos'  => (int)$fped[0]['carritos'],
                    'consultas' => (int)$fped[0]['consultas'],
                    'clics'     => (int)$fped[0]['clics'],
                    'monto'     => (float)$fped[0]['monto'],
                    'tiendas'   => (int)$fped[0]['tiendas'],
                    'ultimo'    => $fped[0]['ultimo'],
                ];
            }
        }

        // ---------------------------------------------------------------- 6) ⭐ OPINIONES
        $fop = stats_q(
            "SELECT COUNT(*) AS n, ROUND(AVG(NULLIF(o.rating,0)),1) AS rating
               FROM directorio_opiniones o
               JOIN directorio_negocios n ON n.id = o.negocio_id AND n.estado = 'activo'
              WHERE ($cond_final)", $par_final);
        if ($fop && (int)$fop[0]['n'] > 0) {
            $d['opiniones'] = ['n' => (int)$fop[0]['n'], 'rating' => (float)$fop[0]['rating']];
        }

        // ---------------------------------------------------------------- 7) 📈 DEMANDA DE LA PALABRA
        // Cuánta gente escribe esto mismo: es el número que le dice al dueño «tus clientes te buscan».
        if (metricas_tabla_lista(METRICAS_TABLA_BUSQUEDAS)) {
            $norm = metricas_norm($termino);
            $hace30 = date('Y-m-d H:i:s', strtotime('-30 days'));
            $fb = stats_q(
                "SELECT COUNT(*) AS veces,
                        SUM(CASE WHEN resultados = 0 THEN 1 ELSE 0 END) AS vacias,
                        SUM(CASE WHEN fecha >= ? THEN 1 ELSE 0 END) AS d30,
                        SUM(CASE WHEN origen = 'chat' THEN 1 ELSE 0 END) AS chat,
                        ROUND(AVG(resultados),1) AS prom,
                        MAX(resultados) AS mejor,
                        MIN(fecha) AS primera,
                        MAX(fecha) AS ultima
                   FROM " . METRICAS_TABLA_BUSQUEDAS . " WHERE norm = ?", [$hace30, $norm]);
            if ($fb && (int)$fb[0]['veces'] > 0) {
                $veces = (int)$fb[0]['veces'];
                // 🏆 ¿En qué puesto va esta palabra entre las MÁS buscadas del sitio? (1.º = la que
                // más se busca). Se mira el top 30 y se busca la palabra ahí: es UNA consulta corta
                // (la tabla de búsquedas es pequeña) y evita SQL rebuscado.
                $puesto = null;
                foreach (stats_q("SELECT norm, COUNT(*) AS veces FROM " . METRICAS_TABLA_BUSQUEDAS . "
                                  GROUP BY norm ORDER BY veces DESC, MAX(fecha) DESC LIMIT 30") as $k => $fila) {
                    if ((string)$fila['norm'] === $norm) { $puesto = $k + 1; break; }
                }
                $d['demanda'] = [
                    'palabra'  => $norm,
                    'veces'    => $veces,
                    'vacias'   => (int)$fb[0]['vacias'],
                    'd30'      => (int)$fb[0]['d30'],
                    'chat'     => (int)$fb[0]['chat'],
                    'prom'     => (float)$fb[0]['prom'],
                    'mejor'    => (int)$fb[0]['mejor'],
                    'primera'  => $fb[0]['primera'],
                    'ultima'   => $fb[0]['ultima'],
                    'puesto'   => ($puesto !== null) ? (int)$puesto : null,
                ];
            }
        }

        // ---------------------------------------------------------------- 8) 🥇 LO MÁS FUERTE
        $sel_t = "SELECT n.nombre, n.slug, n.vistas_count, n.rating, n.whatsapp,
                         c.nombre AS rubro, c.icono AS rubro_icono, d.nombre AS distrito
                    FROM directorio_negocios n
                    LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                    LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
                   WHERE n.estado = 'activo' AND ($cond_final)
                   ORDER BY n.vistas_count DESC, n.rating DESC LIMIT 3";
        $d['top_tiendas'] = stats_q($sel_t, $par_final) ?: [];

        $sel_p = "SELECT s.titulo, s.precio, s.unidad, s.destacado, n.nombre AS tienda, n.slug,
                         d.nombre AS distrito
                    FROM directorio_servicios s
                    JOIN directorio_negocios n ON n.id = s.negocio_id AND n.estado = 'activo'
                    LEFT JOIN directorio_distritos d ON d.id = n.distrito_id
                   WHERE s.activo = 1 AND " . sql_producto_vigente('s') . "
                     AND (" . $cond_titulo_or . ")
                   ORDER BY s.destacado DESC, n.vistas_count DESC LIMIT 3";
        $d['top_productos'] = stats_q($sel_p, $par_titulo_and) ?: [];

        // ---------------------------------------------------------------- 9) 🏷️ EL RUBRO QUE LE TOCA
        // «chancho» puede no ser el nombre de ninguna tienda pero SÍ ser palabra de un rubro
        // (las «frases de unión»): aquí se cuenta ese rubro entero.
        if (!$d['rubro'] && function_exists('categoria_por_clave_texto')) {
            try { $d['rubro'] = categoria_por_clave_texto($termino); } catch (Throwable $e) { $d['rubro'] = null; }
        }
        if (!empty($d['rubro']['id']) && function_exists('rubro_filtro_id')) {
            try {
                [$cf, $cp] = rubro_filtro_id((int)$d['rubro']['id'], 'n');
                $d['rubro_sala'] = [
                    'nombre'    => (string)$d['rubro']['nombre'],
                    'slug'      => (string)($d['rubro']['slug'] ?? ''),
                    'tiendas'   => (int)stats_q1("SELECT COUNT(*) FROM directorio_negocios n
                                    WHERE n.estado = 'activo' AND $cf", $cp),
                    'productos' => (int)stats_q1("SELECT COUNT(*) FROM directorio_servicios s
                                    JOIN directorio_negocios n ON n.id = s.negocio_id AND n.estado = 'activo'
                                    WHERE s.activo = 1 AND " . sql_producto_vigente('s') . " AND $cf", $cp),
                ];
            } catch (Throwable $e) { $d['rubro_sala'] = null; }
        }

        return $d;
    }
}

if (!function_exists('busqueda_detalle_num')) {
    /** Número corto y legible (1 234 / 12,3 mil). */
    function busqueda_detalle_num($n) {
        return function_exists('metricas_num') ? metricas_num($n) : number_format((float)$n, 0, '.', ' ');
    }
}

if (!function_exists('busqueda_detalle_soles')) {
    /** Soles con el formato del sitio: S/ 1 250. */
    function busqueda_detalle_soles($n) {
        return 'S/ ' . number_format((float)$n, ((float)$n == (int)$n) ? 0 : 2, '.', ' ');
    }
}

if (!function_exists('busqueda_detalle_fecha')) {
    /** Una fecha de MySQL en corto y en hora de Lima: 13/09/2026 21:54. Devuelve '' si viene vacía. */
    function busqueda_detalle_fecha($f) {
        $f = trim((string)$f);
        if ($f === '' || strpos($f, '0000-00-00') === 0) return '';
        $ts = strtotime($f);
        return $ts ? date('d/m/Y H:i', $ts) : '';
    }
}

if (!function_exists('busqueda_detalle_html')) {
    /**
     * 📊 Pinta la ficha completa del término. Devuelve el HTML (string).
     * Si no hay NADA que contar, devuelve '' (la página se queda como estaba).
     */
    function busqueda_detalle_html(array $d) {
        $t   = (string)$d['termino'];
        $tie = $d['tiendas'];
        $pro = $d['productos'];
        // Solo se pinta si HAY algo que contar: tiendas, productos, un rubro al que apunta la palabra,
        // pedidos reales o una demanda que ya existe (si la palabra no da nada y nadie la busca, el
        // panel sería un cartel de ceros encima del «no encontré nada»: eso no ayuda a nadie).
        $hay = ((int)$tie['n'] > 0) || ((int)$pro['n'] > 0) || !empty($d['rubro_sala'])
             || ($d['pedidos'] !== null && (int)$d['pedidos']['n'] > 0)
             || ($d['demanda'] !== null && (int)$d['demanda']['veces'] > 1);
        if ($t === '' || !$hay) return '';

        $ico = '🔍';
        $frase = [];
        if ((int)$tie['n'] > 0)  $frase[] = '<b>' . busqueda_detalle_num($tie['n']) . '</b> tienda' . ($tie['n'] == 1 ? '' : 's');
        if ((int)$pro['n'] > 0)  $frase[] = '<b>' . busqueda_detalle_num($pro['n']) . '</b> producto' . ($pro['n'] == 1 ? '' : 's');
        if ((int)$tie['vistas'] > 0) $frase[] = '<b>' . busqueda_detalle_num($tie['vistas']) . '</b> visita' . ($tie['vistas'] == 1 ? '' : 's');
        if ($d['pedidos'] !== null && (int)$d['pedidos']['n'] > 0)
            $frase[] = '<b>' . busqueda_detalle_num($d['pedidos']['n']) . '</b> pedido' . ($d['pedidos']['n'] == 1 ? '' : 's');
        // Si la palabra no da tiendas ni productos por su nombre, pero SÍ es una «frase de unión» de un
        // rubro (el caso de «clavos» → Ferreterías), el titular lleva el rubro: es la respuesta útil.
        if (!$frase && !empty($d['rubro_sala'])) {
            $frase[] = '<b>' . busqueda_detalle_num($d['rubro_sala']['tiendas']) . '</b> tiendas y <b>'
                     . busqueda_detalle_num($d['rubro_sala']['productos']) . '</b> productos en '
                     . e((string)$d['rubro_sala']['nombre']);
        }

        ob_start();
        ?>
<section class="bz" aria-labelledby="bzTitulo">
    <div class="bz__cab">
        <h2 class="bz__t" id="bzTitulo"><span aria-hidden="true">📊</span> Todo sobre «<?= e($t) ?>»</h2>
        <p class="bz__s">
            Tu búsqueda, medida: <?= $frase ? implode(' · ', $frase) : 'todavía sin datos que medir' ?>.
            <span class="bz__mini">Datos reales del sitio al <?= e((string)$d['ahora']) ?>.</span>
        </p>
        <?php if (!empty($d['relajada'])): ?>
            <p class="bz__aviso">ℹ️ No había nada con <b>todas</b> las palabras juntas, así que estos números son de las tiendas que tienen <b>alguna</b> de ellas.</p>
        <?php endif; ?>
    </div>

    <!-- ===== LOS NÚMEROS GRANDES DE LA BÚSQUEDA ===== -->
    <div class="bz__kpis">
        <?php
        $kpis = [];
        // «0 tiendas» solo se dice cuando NO hay nada más que enseñar: si la palabra apunta a un rubro
        // (el caso de «clavos» → Ferreterías), el bloque del rubro ya lleva el número bueno.
        if ((int)$tie['n'] > 0 || ((int)$pro['n'] === 0 && empty($d['rubro_sala']))) {
            $kpis[] = ['🏪', busqueda_detalle_num($tie['n']),
                       'Tiendas que lo ofrecen',
                       ((int)$tie['con_wsp'] > 0 ? busqueda_detalle_num($tie['con_wsp']) . ' con WhatsApp' : 'en Chimbote y la provincia')];
        }
        if ((int)$pro['n'] > 0) {
            $kpis[] = ['📦', busqueda_detalle_num($pro['n']),
                       'Productos que lo ofrecen',
                       ((int)$pro['tiendas'] > 0 ? 'en ' . busqueda_detalle_num($pro['tiendas']) . ' tienda' . ($pro['tiendas'] == 1 ? '' : 's') : 'en el catálogo')];
        }
        if ((int)$tie['vistas'] > 0) {
            $kpis[] = ['👁️', busqueda_detalle_num($tie['vistas']),
                       'Visitas acumuladas',
                       'lo que ya miró la gente de estas tiendas'];
        }
        // 🛒 Pedidos: se muestra cuando hay algo que medir (tiendas o productos del término). Con 0 se
        // dice claro («todavía nadie pidió por aquí»): es el dato que le importa al dueño que mira su
        // rubro. Si la palabra no encontró nada, no se pinta un cero que no significa nada.
        if ($d['pedidos'] !== null && ((int)$tie['n'] > 0 || (int)$pro['n'] > 0)) {
            $kpis[] = ['🛒', busqueda_detalle_num($d['pedidos']['n']),
                       'Pedidos que movieron',
                       ((int)$d['pedidos']['n'] === 0
                            ? 'todavía nadie pidió por aquí'
                            : ((int)$d['pedidos']['tiendas'] > 0
                                ? busqueda_detalle_num($d['pedidos']['tiendas']) . ' tienda' . ($d['pedidos']['tiendas'] == 1 ? '' : 's') . ' con pedidos'
                                : 'botones de pedir que se tocaron'))];
            if ((float)$d['pedidos']['monto'] > 0) {
                $kpis[] = ['💰', busqueda_detalle_soles($d['pedidos']['monto']),
                           'Soles en pedidos', 'el valor que mueve este término'];
            }
        }
        if ((float)$pro['pmin'] > 0) {
            $kpis[] = ['💵', busqueda_detalle_soles($pro['pmin']),
                       'El más barato',
                       ((float)$pro['pmax'] > 0 ? 'el más caro: ' . busqueda_detalle_soles($pro['pmax']) : 'precio de referencia')];
        }
        if ((float)$tie['rating'] > 0) {
            $kpis[] = ['⭐', number_format((float)$tie['rating'], 1, ',', ''),
                       'Calificación promedio',
                       ((!empty($d['opiniones']['n'])) ? busqueda_detalle_num($d['opiniones']['n']) . ' opiniones de clientes' : 'de esas tiendas')];
        }
        if ($d['demanda'] !== null) {
            $kpis[] = ['🔎', busqueda_detalle_num($d['demanda']['veces']),
                       'Veces que se buscó',
                       ((int)$d['demanda']['d30'] > 0 ? busqueda_detalle_num($d['demanda']['d30']) . ' en los últimos 30 días' : 'demanda de esta palabra')];
        }
        foreach ($kpis as $k):
        ?>
            <div class="bz-kpi">
                <div class="bz-kpi__n"><span aria-hidden="true"><?= $k[0] ?></span> <?= $k[1] ?></div>
                <div class="bz-kpi__l"><?= e($k[2]) ?></div>
                <div class="bz-kpi__d"><?= e($k[3]) ?></div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- ===== EL DETALLE COMPLETO (se puede esconder: bloque nativo del navegador, sin JS) ===== -->
    <details class="bz__mas" open>
        <summary class="bz__mas-sum">📋 El detalle completo: distritos, rubros, precios<?= $d['pedidos'] !== null ? ', pedidos' : '' ?>, demanda y lo más fuerte</summary>
        <div class="bz__cuerpo">

            <?php if (!empty($d['rubro_sala'])): ?>
                <!-- 🏷️ La palabra es una «frase de unión» del rubro: aquí está el rubro entero -->
                <div class="bz-bloque bz-bloque--ancho">
                    <h3 class="bz-bloque__t">🏷️ El rubro que le toca</h3>
                    <p class="bz-bloque__p">
                        «<?= e($t) ?>» apunta al rubro
                        <?php if (!empty($d['rubro_sala']['slug'])): ?>
                            <a href="<?= e(url('categoria/' . urlencode((string)$d['rubro_sala']['slug']))) ?>"><b><?= e($d['rubro_sala']['nombre']) ?></b></a>
                        <?php else: ?>
                            <b><?= e($d['rubro_sala']['nombre']) ?></b>
                        <?php endif; ?>:
                        <b><?= busqueda_detalle_num($d['rubro_sala']['tiendas']) ?></b> tiendas y
                        <b><?= busqueda_detalle_num($d['rubro_sala']['productos']) ?></b> productos en total.
                    </p>
                </div>
            <?php endif; ?>

            <?php if ($d['distritos']): ?>
                <div class="bz-bloque">
                    <h3 class="bz-bloque__t">📍 Dónde está</h3>
                    <?php $mx = max(array_map(function ($x) { return (int)$x['n']; }, $d['distritos'])) ?: 1; ?>
                    <?php foreach ($d['distritos'] as $x): ?>
                        <div class="bz-fila">
                            <span class="bz-fila__n"><?= e((string)$x['nombre']) ?></span>
                            <span class="bz-fila__b"><i style="width:<?= max(3, (int)round((int)$x['n'] * 100 / $mx)) ?>%"></i></span>
                            <span class="bz-fila__v"><?= busqueda_detalle_num($x['n']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ($d['rubros']): ?>
                <div class="bz-bloque">
                    <h3 class="bz-bloque__t">🏷️ En qué rubros cae</h3>
                    <?php $mxr = max(array_map(function ($x) { return (int)$x['n']; }, $d['rubros'])) ?: 1; ?>
                    <?php foreach ($d['rubros'] as $x): ?>
                        <div class="bz-fila">
                            <span class="bz-fila__n"><?= e(trim((string)($x['icono'] ?? '') . ' ' . (string)$x['nombre'])) ?></span>
                            <span class="bz-fila__b"><i style="width:<?= max(3, (int)round((int)$x['n'] * 100 / $mxr)) ?>%"></i></span>
                            <span class="bz-fila__v"><?= busqueda_detalle_num($x['n']) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if ((int)$pro['n'] > 0 || (int)$tie['n'] > 0): ?>
                <div class="bz-bloque">
                    <h3 class="bz-bloque__t">💵 Precios y calidad</h3>
                    <?php if ((float)$pro['pmin'] > 0): ?>
                        <div class="bz-dato"><span>💵 Desde</span><b><?= busqueda_detalle_soles($pro['pmin']) ?></b></div>
                        <?php if ((float)$pro['pmed'] > 0): ?>
                            <div class="bz-dato"><span>📊 Precio promedio</span><b><?= busqueda_detalle_soles($pro['pmed']) ?></b></div>
                        <?php endif; ?>
                        <div class="bz-dato"><span>💎 Hasta</span><b><?= busqueda_detalle_soles($pro['pmax']) ?></b></div>
                        <div class="bz-dato"><span>🏷️ Con precio puesto</span><b><?= busqueda_detalle_num($pro['con_precio']) ?> de <?= busqueda_detalle_num($pro['n']) ?></b></div>
                    <?php endif; ?>
                    <?php if ((int)$pro['con_foto'] > 0): ?>
                        <div class="bz-dato"><span>🖼️ Con foto</span><b><?= busqueda_detalle_num($pro['con_foto']) ?> producto<?= $pro['con_foto'] == 1 ? '' : 's' ?></b></div>
                    <?php endif; ?>
                    <?php if ((int)$tie['con_wsp'] > 0): ?>
                        <div class="bz-dato"><span>💬 Se les escribe ya</span><b><?= busqueda_detalle_num($tie['con_wsp']) ?> tienda<?= $tie['con_wsp'] == 1 ? '' : 's' ?> con WhatsApp</b></div>
                    <?php endif; ?>
                    <?php if ((int)$tie['a_domicilio'] > 0): ?>
                        <div class="bz-dato"><span>🧰 Van a tu casa</span><b><?= busqueda_detalle_num($tie['a_domicilio']) ?> servicio<?= $tie['a_domicilio'] == 1 ? '' : 's' ?> a domicilio</b></div>
                    <?php endif; ?>
                    <?php if ((int)$tie['destacadas'] > 0): ?>
                        <div class="bz-dato"><span>⭐ Destacadas</span><b><?= busqueda_detalle_num($tie['destacadas']) ?></b></div>
                    <?php endif; ?>
                    <?php if (!empty($d['opiniones']['n'])): ?>
                        <div class="bz-dato"><span>💬 Opiniones de clientes</span><b><?= busqueda_detalle_num($d['opiniones']['n']) ?><?= ((float)$d['opiniones']['rating'] > 0) ? ' · ' . number_format((float)$d['opiniones']['rating'], 1, ',', '') . ' ⭐' : '' ?></b></div>
                    <?php endif; ?>
                    <?php
                    // 🆕 ¿Cuándo se sumó lo último de este término? (la tienda o el producto más nuevo)
                    $ult_nuevo = max(
                        (string)($pro['ultimo'] ?? ''),
                        (string)($tie['ultima'] ?? '')
                    );
                    $ult_txt = busqueda_detalle_fecha($ult_nuevo);
                    if ($ult_txt !== ''): ?>
                        <div class="bz-dato"><span>🆕 Lo último que se sumó</span><b><?= e($ult_txt) ?></b></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($d['pedidos'] !== null && (int)$d['pedidos']['n'] > 0): ?>
                <div class="bz-bloque">
                    <h3 class="bz-bloque__t">🛒 Lo que se pide</h3>
                    <div class="bz-dato"><span>💬 WhatsApp de la ficha</span><b><?= busqueda_detalle_num($d['pedidos']['clics']) ?></b></div>
                    <div class="bz-dato"><span>❓ Preguntas por un producto</span><b><?= busqueda_detalle_num($d['pedidos']['consultas']) ?></b></div>
                    <div class="bz-dato"><span>🛒 Pedidos del carrito</span><b><?= busqueda_detalle_num($d['pedidos']['carritos']) ?></b></div>
                    <?php if ((float)$d['pedidos']['monto'] > 0): ?>
                        <div class="bz-dato"><span>💰 Soles que mueven</span><b><?= busqueda_detalle_soles($d['pedidos']['monto']) ?></b></div>
                    <?php endif; ?>
                    <?php $fu = busqueda_detalle_fecha($d['pedidos']['ultimo']); if ($fu !== ''): ?>
                        <div class="bz-dato"><span>🕒 Último pedido</span><b><?= e($fu) ?></b></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($d['demanda'] !== null): ?>
                <div class="bz-bloque">
                    <h3 class="bz-bloque__t">📈 Cuánta gente busca esto</h3>
                    <div class="bz-dato"><span>🔎 Veces buscado</span><b><?= busqueda_detalle_num($d['demanda']['veces']) ?></b></div>
                    <?php if (!empty($d['demanda']['puesto'])): ?>
                        <div class="bz-dato"><span>🏆 Puesto entre lo más buscado</span><b>n.º <?= busqueda_detalle_num($d['demanda']['puesto']) ?> del sitio</b></div>
                    <?php endif; ?>
                    <?php if ((int)$d['demanda']['d30'] > 0): ?>
                        <div class="bz-dato"><span>📅 Últimos 30 días</span><b><?= busqueda_detalle_num($d['demanda']['d30']) ?></b></div>
                    <?php endif; ?>
                    <?php if ((int)$d['demanda']['chat'] > 0): ?>
                        <div class="bz-dato"><span>🥷 Preguntado al chat</span><b><?= busqueda_detalle_num($d['demanda']['chat']) ?></b></div>
                    <?php endif; ?>
                    <?php if ((float)$d['demanda']['prom'] > 0): ?>
                        <div class="bz-dato"><span>📊 Resultados que da</span><b><?= number_format((float)$d['demanda']['prom'], 1, ',', '') ?> en promedio</b></div>
                    <?php endif; ?>
                    <?php if ((int)$d['demanda']['vacias'] > 0): ?>
                        <div class="bz-dato"><span>⚠️ Veces sin nada</span><b><?= busqueda_detalle_num($d['demanda']['vacias']) ?> de <?= busqueda_detalle_num($d['demanda']['veces']) ?></b></div>
                    <?php endif; ?>
                    <?php $fp1 = busqueda_detalle_fecha($d['demanda']['ultima']); if ($fp1 !== ''): ?>
                        <div class="bz-dato"><span>🕒 Última vez buscado</span><b><?= e($fp1) ?></b></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($d['top_tiendas']): ?>
                <div class="bz-bloque bz-bloque--ancho">
                    <h3 class="bz-bloque__t">🥇 Lo más fuerte de «<?= e($t) ?>»</h3>
                    <div class="bz-top">
                        <?php foreach ($d['top_tiendas'] as $x): ?>
                            <a class="bz-top__item" href="<?= e(url_negocio((string)$x['slug'])) ?>">
                                <span class="bz-top__n"><?= e((string)$x['nombre']) ?></span>
                                <span class="bz-top__m">
                                    <?= e(trim((string)($x['rubro_icono'] ?? '') . ' ' . (string)($x['rubro'] ?? ''))) ?>
                                    <?php if (!empty($x['distrito'])): ?> · 📍 <?= e((string)$x['distrito']) ?><?php endif; ?>
                                    · 👁️ <?= busqueda_detalle_num($x['vistas_count']) ?>
                                    <?php if ((float)$x['rating'] > 0): ?> · ⭐ <?= number_format((float)$x['rating'], 1, ',', '') ?><?php endif; ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($d['top_productos']): ?>
                <div class="bz-bloque bz-bloque--ancho">
                    <h3 class="bz-bloque__t">📦 Lo que ofrecen (con precio)</h3>
                    <div class="bz-top">
                        <?php foreach ($d['top_productos'] as $x): ?>
                            <a class="bz-top__item" href="<?= e(url_negocio((string)$x['slug'])) ?>">
                                <span class="bz-top__n"><?= e((string)$x['titulo']) ?><?= !empty($x['destacado']) ? ' ⭐' : '' ?></span>
                                <span class="bz-top__m">
                                    <?= ((float)$x['precio'] > 0) ? '<b>' . busqueda_detalle_soles($x['precio']) . '</b>' . (!empty($x['unidad']) ? ' / ' . e((string)$x['unidad']) : '') . ' · ' : '' ?>
                                    <?= e((string)$x['tienda']) ?><?= !empty($x['distrito']) ? ' · 📍 ' . e((string)$x['distrito']) : '' ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </details>

    <p class="bz__pie">
        <?= $ico ?> Estos números son de <b>todo Chimbote y la provincia</b> para la palabra «<?= e($t) ?>»;
        los filtros de abajo solo cambian la lista de resultados.
    </p>
</section>
        <?php
        return (string)ob_get_clean();
    }
}
