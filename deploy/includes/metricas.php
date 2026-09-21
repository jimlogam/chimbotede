<?php
/**
 * metricas.php — LOS RÉCORDS DEL SITIO (motor de datos del Súper Admin)
 * =====================================================================
 * Pedido del jefe (2026-09-13): *«en mi panel de super admin muéstrame los records de
 * más vistos, más buscados, más botones de pedir pedidos y otras cosas que consideres
 * interesantes para mi crecimiento y encontrar personas dispuestas a invertir en mi sitio»*.
 *
 * Este archivo es el MOTOR: mide y devuelve números. La vista que los pinta es
 * `includes/vista_records_admin.php` (pestaña 🏆 Récords de `superadmin.php`).
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * DE DÓNDE SALE CADA COSA (lo importante: no todo se podía medir antes)
 * ─────────────────────────────────────────────────────────────────────────────
 *  · 👁️ MÁS VISTOS ......... `directorio_stats_eventos` (pageviews puros, tipo_pagina='tienda').
 *                            Se usa ESTA y no `directorio_vistas` porque las vistas por
 *                            `api/registrar_vista.php` casi no se llaman y `vistas_count` se
 *                            resetea cada 10 días (`cron_reset_vistas.php`).
 *  · 🔍 MÁS BUSCADOS ....... `directorio_busquedas` (TABLA NUEVA): cada término que alguien
 *                            escribe, con cuántos resultados le salieron. Antes solo se
 *                            guardaban las búsquedas de usuarios CON SESIÓN
 *                            (`directorio_historial_busqueda`), así que el 99 % no se medía.
 *  · 🔥 MÁS PEDIDOS ........ `directorio_pedidos` (TABLA NUEVA): cada toque de un botón de
 *                            pedido (WhatsApp de la ficha, consulta de un producto, pedido
 *                            del carrito 🛒). Antes NO se guardaba en la base: solo se avisaba
 *                            al Telegram (`directorio_avisos_log`), así que no había historial.
 *  · 💰 DINERO ............. la suma de los pedidos del carrito (`total`, el «Total
 *                            referencial» del mensaje): es el valor que el sitio mueve.
 *
 * Las dos tablas nuevas se crean SOLO desde el panel (botón ⚙️, patrón de `empleos` y
 * `banners`): los `migrar_*.php` los bloquea el antivirus del hosting. Y como el sitio
 * llevaba meses midiendo sin guardar, hay un **🧺 «Traer el histórico»** que SIEMBRA las
 * tablas nuevas con lo que ya estaba en el registro de avisos (`directorio_avisos_log`),
 * para que el jefe vea récords reales desde el primer minuto.
 *
 * ⚠️ Regla de oro del proyecto: NADA de esto puede romper ni frenar el sitio.
 *    Todas las funciones van en try/catch y devuelven [] / false si algo falla.
 * ⚠️ Fechas: SIEMPRE hora de Lima (las genera PHP). El MySQL del hosting va en UTC.
 */

require_once __DIR__ . '/estadisticas.php';   // stats_es_bot(), stats_dispositivo(), stats_q()

if (!defined('METRICAS_TABLA_BUSQUEDAS')) define('METRICAS_TABLA_BUSQUEDAS', 'directorio_busquedas');
if (!defined('METRICAS_TABLA_PEDIDOS'))   define('METRICAS_TABLA_PEDIDOS', 'directorio_pedidos');
if (!defined('METRICAS_DIAS_GUARDAR'))    define('METRICAS_DIAS_GUARDAR', 365);  // retención

// ============================================================
// 1) INSTALACIÓN DE LAS TABLAS (defensiva, solo desde el panel)
// ============================================================

/**
 * ¿Existe la tabla? Se comprueba UNA vez por petición (cache estático) y nunca lanza error.
 * Es lo que permite que el sitio siga midiendo en cuanto el admin abre la pestaña 🏆 Récords.
 */
function metricas_tabla_lista($tabla) {
    static $cache = [];
    $tabla = (string)$tabla;
    if (array_key_exists($tabla, $cache)) return $cache[$tabla];
    try {
        db()->query('SELECT 1 FROM ' . $tabla . ' LIMIT 1');
        $cache[$tabla] = true;
    } catch (Throwable $e) {
        $cache[$tabla] = false;
    }
    return $cache[$tabla];
}

/** ¿Están las dos tablas de récords? */
function metricas_listas() {
    return metricas_tabla_lista(METRICAS_TABLA_BUSQUEDAS) && metricas_tabla_lista(METRICAS_TABLA_PEDIDOS);
}

/**
 * ¿La columna `tipo` de `directorio_pedidos` ya acepta 'llamada'? (2026-09-15)
 * El ENUM original era ('clic','consulta','pedido'). El botón 📞 Llamar añadió 'llamada', pero
 * una base que no haya pasado por el ALTER guardaría '' SIN dar error (trampa ya conocida del
 * proyecto con `tipo_producto`). Aquí se comprueba y, si no está, el clic se guarda como 'clic'
 * en vez de perderse. Se comprueba UNA vez por petición.
 */
function metricas_tipo_llamada_ok() {
    static $ok = null;
    if ($ok !== null) return $ok;
    $ok = false;
    try {
        $st = db()->query("SHOW COLUMNS FROM " . METRICAS_TABLA_PEDIDOS . " LIKE 'tipo'");
        $c = $st->fetch(PDO::FETCH_ASSOC);
        if ($c && stripos((string)($c['Type'] ?? ''), "'llamada'") !== false) $ok = true;
    } catch (Throwable $e) {
        $ok = false;
    }
    return $ok;
}

/**
 * Crea las 2 tablas de récords. Idempotente (IF NOT EXISTS).
 * Solo se llama desde el panel del admin (o desde una sonda con METRICAS_INSTALAR).
 * Devuelve ['ok'=>bool,'msg'=>string].
 */
function metricas_instalar() {
    if ((!function_exists('es_admin') || !es_admin()) && !defined('METRICAS_INSTALAR')) {
        return ['ok' => false, 'msg' => 'Solo el administrador puede crear las tablas de récords.'];
    }
    $pdo = db();
    $msg = [];
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS " . METRICAS_TABLA_BUSQUEDAS . " (
            id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            termino       VARCHAR(120) NOT NULL,
            norm          VARCHAR(120) NOT NULL,
            origen        VARCHAR(12)  NOT NULL DEFAULT 'web',
            resultados    SMALLINT UNSIGNED NULL,
            categoria_id  INT UNSIGNED NULL,
            usuario_id    BIGINT UNSIGNED NULL,
            dispositivo   VARCHAR(12)  NOT NULL DEFAULT '',
            ip            VARCHAR(45)  NULL,
            fecha         DATETIME     NOT NULL,
            PRIMARY KEY (id),
            KEY idx_mb_fecha (fecha),
            KEY idx_mb_norm (norm, fecha),
            KEY idx_mb_vacias (resultados, fecha),
            KEY idx_mb_cat (categoria_id, fecha)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $msg[] = '✔ Tabla de búsquedas lista.';

        $pdo->exec("CREATE TABLE IF NOT EXISTS " . METRICAS_TABLA_PEDIDOS . " (
            id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            negocio_id    INT UNSIGNED NOT NULL,
            producto_id   INT UNSIGNED NULL,
            tipo          ENUM('clic','consulta','pedido','llamada') NOT NULL DEFAULT 'clic',
            origen        VARCHAR(12)  NOT NULL DEFAULT 'ficha',
            productos_n   TINYINT UNSIGNED NOT NULL DEFAULT 0,
            total         DECIMAL(10,2) NULL,
            descuento_pct TINYINT UNSIGNED NULL,
            detalle       VARCHAR(255) NULL,
            usuario_id    BIGINT UNSIGNED NULL,
            dispositivo   VARCHAR(12)  NOT NULL DEFAULT '',
            ip            VARCHAR(45)  NULL,
            fecha         DATETIME     NOT NULL,
            PRIMARY KEY (id),
            KEY idx_mp_fecha (fecha),
            KEY idx_mp_neg (negocio_id, fecha),
            KEY idx_mp_prod (producto_id, fecha),
            KEY idx_mp_tipo (tipo, fecha)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        $msg[] = '✔ Tabla de pedidos lista.';
        return ['ok' => true, 'msg' => implode(' ', $msg)];
    } catch (Throwable $e) {
        error_log('metricas_instalar: ' . $e->getMessage());
        return ['ok' => false, 'msg' => 'Error al crear las tablas: ' . $e->getMessage()];
    }
}

// ============================================================
// 2) REGISTRO (lo que se llama desde el sitio)
// ============================================================

/** ¿Es un robot? (se reutiliza el detector del módulo de estadísticas). */
function metricas_es_robot() {
    if (function_exists('stats_es_bot')) return stats_es_bot();
    return false;
}

/**
 * ¿Es el propio administrador? Sus pruebas y sus navegaciones NO deben inflar los récords
 * (el módulo de Estadísticas ya lo hace así con `STATS_EXCLUIR_ADMIN`, y aquí se respeta la
 * misma constante: si algún día se quiere contar al jefe, se cambia UNA vez en estadisticas.php).
 */
function metricas_es_admin() {
    if (!defined('STATS_EXCLUIR_ADMIN') || !STATS_EXCLUIR_ADMIN) return false;
    if (!function_exists('es_admin')) return false;
    try { return (bool)es_admin(); } catch (Throwable $e) { return false; }
}

/**
 * Palabra normalizada para AGRUPAR: minúsculas, sin tildes, espacios simples.
 * «Zapatillas  Nike» y «zapatillas nike» son la MISMA búsqueda.
 */
function metricas_norm($texto) {
    $t = mb_strtolower(trim((string)$texto), 'UTF-8');
    $t = strtr($t, [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'à' => 'a',
        'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u', 'ñ' => 'n', 'ç' => 'c', 'ý' => 'y',
    ]);
    $t = preg_replace('/[^\p{L}\p{N}\s\+\-\.]/u', ' ', $t);
    $t = preg_replace('/\s+/u', ' ', (string)$t);
    return trim(mb_substr((string)$t, 0, 120));
}

/** La IP del visitante (máx. 45: cabe IPv6). */
function metricas_ip() {
    return mb_substr((string)(ip_real()), 0, 45);
}

/**
 * 🔍 REGISTRA UNA BÚSQUEDA. Se llama desde `buscar.php` (web) y desde el chat 🥷.
 * $opciones: resultados · origen (web|chat|banner) · categoria_id · usuario_id
 * Devuelve true si se guardó.
 */
function metrica_busqueda($termino, $resultados = null, array $opciones = []) {
    $termino = trim((string)$termino);
    $norm    = metricas_norm($termino);
    if ($norm === '' || mb_strlen($norm) < 2) return false;   // ruido de 1 letra
    if (mb_strlen($norm) > 120) $norm = mb_substr($norm, 0, 120);
    if (metricas_es_robot()) return false;
    if (metricas_es_admin()) return false;
    if (!metricas_tabla_lista(METRICAS_TABLA_BUSQUEDAS)) return false;
    try {
        $u = function_exists('usuario_actual') ? usuario_actual() : null;
        db()->prepare('INSERT INTO ' . METRICAS_TABLA_BUSQUEDAS . '
            (termino, norm, origen, resultados, categoria_id, usuario_id, dispositivo, ip, fecha)
            VALUES (?,?,?,?,?,?,?,?,?)')
            ->execute([
                mb_substr($termino, 0, 120),
                $norm,
                mb_substr((string)($opciones['origen'] ?? 'web'), 0, 12),
                ($resultados === null ? null : max(0, (int)$resultados)),
                !empty($opciones['categoria_id']) ? (int)$opciones['categoria_id'] : null,
                !empty($opciones['usuario_id']) ? (int)$opciones['usuario_id'] : ($u['id'] ?? null),
                function_exists('stats_dispositivo') ? stats_dispositivo() : '',
                metricas_ip(),
                date('Y-m-d H:i:s'),
            ]);
        return true;
    } catch (Throwable $e) {
        error_log('metrica_busqueda: ' . $e->getMessage());
        return false;
    }
}

/**
 * 🔥 REGISTRA UN PEDIDO (cualquier botón que lleve a pedir: WhatsApp de la ficha,
 * consulta de un producto, el pedido armado del carrito 🛒 o el botón 📞 Llamar).
 * Se llama desde `api/lead.php` y `api/llamada.php`.
 * $datos: negocio_id · producto_id · tipo (clic|consulta|pedido|llamada) · origen · productos_n ·
 *         total · descuento_pct · detalle
 */
function metrica_pedido(array $datos) {
    $neg = (int)($datos['negocio_id'] ?? 0);
    if ($neg <= 0) return false;
    // `ignorar_filtro_robot`: lo usa `api/llamada.php`, que recibe un «beacon» de JavaScript.
    // Ese beacon SOLO lo puede mandar un navegador ejecutando la página (un robot que lee el
    // HTML no lo dispara) y, en cambio, el filtro por user-agent se lleva por delante a gente
    // de verdad: los navegadores DENTRO de WhatsApp o Telegram llevan su nombre en el UA y
    // `stats_es_bot()` los marca como robot (el mismo motivo por el que sus visitas tampoco se
    // cuentan). Sin esta excepción, los clics más calientes (los que llegan de un chat) se
    // perderían justo en el informe que el jefe pidió.
    if (metricas_es_robot() && empty($datos['ignorar_filtro_robot'])) return false;
    if (metricas_es_admin()) return false;
    if (!metricas_tabla_lista(METRICAS_TABLA_PEDIDOS)) return false;
    // 'llamada' se añadió el 2026-09-15 (botón 📞 Llamar de la ficha): si la base todavía no
    // tuviera el ENUM ampliado, MySQL guardaría '' y el dato se perdería; por eso se comprueba.
    $tipos = ['clic', 'consulta', 'pedido', 'llamada'];
    $tipo  = in_array((string)($datos['tipo'] ?? ''), $tipos, true) ? (string)$datos['tipo'] : 'clic';
    if ($tipo === 'llamada' && !metricas_tipo_llamada_ok()) $tipo = 'clic';
    try {
        $u = function_exists('usuario_actual') ? usuario_actual() : null;
        db()->prepare('INSERT INTO ' . METRICAS_TABLA_PEDIDOS . '
            (negocio_id, producto_id, tipo, origen, productos_n, total, descuento_pct, detalle, usuario_id, dispositivo, ip, fecha)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([
                $neg,
                !empty($datos['producto_id']) ? (int)$datos['producto_id'] : null,
                $tipo,
                mb_substr((string)($datos['origen'] ?? 'ficha'), 0, 12),
                max(0, min(255, (int)($datos['productos_n'] ?? 0))),
                (isset($datos['total']) && (float)$datos['total'] > 0) ? round((float)$datos['total'], 2) : null,
                (isset($datos['descuento_pct']) && (int)$datos['descuento_pct'] > 0) ? min(255, (int)$datos['descuento_pct']) : null,
                isset($datos['detalle']) ? mb_substr((string)$datos['detalle'], 0, 255) : null,
                !empty($datos['usuario_id']) ? (int)$datos['usuario_id'] : ($u['id'] ?? null),
                function_exists('stats_dispositivo') ? stats_dispositivo() : '',
                metricas_ip(),
                date('Y-m-d H:i:s'),
            ]);
        return true;
    } catch (Throwable $e) {
        error_log('metrica_pedido: ' . $e->getMessage());
        return false;
    }
}

// ============================================================
// 3) PERIODOS Y RANGOS (hora de Lima, nunca NOW() de MySQL)
// ============================================================

/** Catálogo de periodos del selector de la pestaña. */
function metricas_periodos() {
    return [
        'hoy'    => ['dias' => 1,  'titulo' => 'Hoy'],
        '7'      => ['dias' => 7,  'titulo' => '7 días'],
        '30'     => ['dias' => 30, 'titulo' => '30 días'],
        '90'     => ['dias' => 90, 'titulo' => '90 días'],
        'todo'   => ['dias' => 0,  'titulo' => 'Todo'],
    ];
}

/** Convierte el periodo de la URL en días (0 = todo el histórico). */
function metricas_dias($clave) {
    $p = metricas_periodos();
    $clave = (string)$clave;
    return isset($p[$clave]) ? (int)$p[$clave]['dias'] : 30;
}

/**
 * Rango [desde, hasta] en formato MySQL, en hora de Lima.
 * $offset = cuántos periodos hacia atrás (1 = el periodo anterior, para comparar crecimiento).
 * Con días = 0 (todo) devuelve ['', ''] y las consultas NO filtran por fecha.
 */
function metricas_rango($dias, $offset = 0) {
    $dias = (int)$dias;
    if ($dias <= 0) return ['', ''];
    $offset = max(0, (int)$offset);
    $fin = strtotime('-' . ($offset * $dias) . ' days');
    $ini = strtotime('-' . (($offset * $dias) + $dias - 1) . ' days');
    return [date('Y-m-d 00:00:00', $ini), date('Y-m-d 23:59:59', $fin)];
}

/** Trozo de SQL + parámetros para filtrar por una columna de fecha. */
function metricas_sql_rango($columna, array $rango) {
    if ($rango[0] === '') return ['', []];
    return [' AND ' . $columna . ' >= ? AND ' . $columna . ' <= ?', [$rango[0], $rango[1]]];
}

/** Etiqueta humana del rango (para el encabezado y el texto copiable). */
function metricas_rango_txt($dias) {
    $r = metricas_rango($dias);
    if ($r[0] === '') return 'todo el histórico';
    if ((int)$dias === 1) return 'hoy (' . date('d/m/Y') . ')';
    return 'del ' . date('d/m/Y', strtotime($r[0])) . ' al ' . date('d/m/Y', strtotime($r[1]));
}

// ============================================================
// 4) CONSULTAS DEL TABLERO
// ============================================================

/** Los números grandes del periodo (+ los del periodo anterior, para el % de crecimiento). */
function metricas_kpis($dias, $offset = 0) {
    $r = metricas_rango($dias, $offset);
    [$w, $p] = metricas_sql_rango('inicio', $r);
    $out = [
        'visitas'    => stats_q1("SELECT COUNT(*) FROM directorio_stats_sesiones WHERE 1=1 $w", $p),
        'visitantes' => stats_q1("SELECT COUNT(DISTINCT cookie) FROM directorio_stats_sesiones WHERE 1=1 $w", $p),
        'segundos'   => stats_q1("SELECT COALESCE(SUM(segundos),0) FROM directorio_stats_sesiones WHERE 1=1 $w", $p),
    ];
    [$w, $p] = metricas_sql_rango('fecha', $r);
    $out['paginas'] = stats_q1("SELECT COUNT(*) FROM directorio_stats_eventos WHERE tipo='pv' $w", $p);
    $out['fichas']  = stats_q1("SELECT COUNT(*) FROM directorio_stats_eventos WHERE tipo_pagina='tienda' $w", $p);
    $out['buscadas_paginas'] = stats_q1("SELECT COUNT(*) FROM directorio_stats_eventos WHERE tipo_pagina='buscar' $w", $p);

    // Tablas nuevas (pueden no existir todavía: stats_q devuelve [] y aquí sale 0)
    if (metricas_tabla_lista(METRICAS_TABLA_BUSQUEDAS)) {
        $f = stats_q("SELECT COUNT(*) AS n, COUNT(DISTINCT norm) AS distintas,
                SUM(CASE WHEN resultados = 0 THEN 1 ELSE 0 END) AS vacias
            FROM " . METRICAS_TABLA_BUSQUEDAS . " WHERE 1=1 $w", $p);
        $out['busquedas']         = (int)($f[0]['n'] ?? 0);
        $out['busquedas_distintas'] = (int)($f[0]['distintas'] ?? 0);
        $out['busquedas_vacias']  = (int)($f[0]['vacias'] ?? 0);
    } else {
        $out['busquedas'] = $out['busquedas_distintas'] = $out['busquedas_vacias'] = 0;
    }
    if (metricas_tabla_lista(METRICAS_TABLA_PEDIDOS)) {
        $f = stats_q("SELECT COUNT(*) AS n,
                SUM(CASE WHEN tipo='pedido' THEN 1 ELSE 0 END) AS carritos,
                SUM(CASE WHEN tipo='consulta' THEN 1 ELSE 0 END) AS consultas,
                SUM(CASE WHEN tipo='clic' THEN 1 ELSE 0 END) AS clics,
                COALESCE(SUM(total),0) AS monto
            FROM " . METRICAS_TABLA_PEDIDOS . " WHERE 1=1 $w", $p);
        $out['pedidos']       = (int)($f[0]['n'] ?? 0);
        $out['pedidos_carrito'] = (int)($f[0]['carritos'] ?? 0);
        $out['pedidos_consulta'] = (int)($f[0]['consultas'] ?? 0);
        $out['pedidos_clic']  = (int)($f[0]['clics'] ?? 0);
        $out['pedidos_monto'] = (float)($f[0]['monto'] ?? 0);
        $out['tiendas_con_pedido'] = stats_q1("SELECT COUNT(DISTINCT negocio_id) FROM " . METRICAS_TABLA_PEDIDOS . " WHERE 1=1 $w", $p);
    } else {
        $out['pedidos'] = $out['pedidos_carrito'] = $out['pedidos_consulta'] = $out['pedidos_clic'] = 0;
        $out['pedidos_monto'] = 0.0;
        $out['tiendas_con_pedido'] = 0;
    }

    // Crecimiento del sitio (lo que mira un inversionista).
    // ⚠️ OJO: el trozo de SQL trae DENTRO el nombre de la columna, así que aquí hay que pedir
    // el rango por `creado_en` (no se puede reutilizar el de `fecha`).
    [$wc, $pc] = metricas_sql_rango('creado_en', $r);
    $out['tiendas_nuevas']  = stats_q1("SELECT COUNT(*) FROM directorio_negocios WHERE 1=1 $wc", $pc);
    $out['usuarios_nuevos'] = stats_q1("SELECT COUNT(*) FROM directorio_usuarios WHERE 1=1 $wc", $pc);

    // Y el tamaño total del sitio (no depende del periodo)
    $out['tiendas_total']    = stats_q1("SELECT COUNT(*) FROM directorio_negocios");
    $out['tiendas_activas']  = stats_q1("SELECT COUNT(*) FROM directorio_negocios WHERE estado='activo'");
    $out['productos_total']  = stats_q1("SELECT COUNT(*) FROM directorio_servicios");
    $out['usuarios_total']   = stats_q1("SELECT COUNT(*) FROM directorio_usuarios");
    $out['con_whatsapp']     = stats_q1("SELECT COUNT(*) FROM directorio_negocios WHERE estado='activo' AND whatsapp IS NOT NULL AND whatsapp <> ''");
    return $out;
}

/** 👁️ Tiendas MÁS VISTAS del periodo (pageviews reales de su ficha). */
function metricas_top_tiendas($dias, $top = 15) {
    $r = metricas_rango($dias);
    [$w, $p] = metricas_sql_rango('fecha', $r);
    $p[] = (int)$top;
    return stats_q("SELECT n.id, n.nombre, n.slug, n.whatsapp, n.telefono,
            c.nombre AS rubro, c.icono AS rubro_icono, d.nombre AS distrito, t.n AS vistas
        FROM (SELECT pagina, COUNT(*) AS n FROM directorio_stats_eventos
              WHERE tipo_pagina='tienda' $w
              GROUP BY pagina ORDER BY n DESC LIMIT ?) t
        JOIN directorio_negocios n ON n.slug = SUBSTRING(t.pagina, 5)
        LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
        LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
        ORDER BY t.n DESC", $p);
}

/** 🔍 LOS MÁS BUSCADOS (agrupados por término normalizado). */
function metricas_top_busquedas($dias, $top = 20, $solo_vacias = false) {
    if (!metricas_tabla_lista(METRICAS_TABLA_BUSQUEDAS)) return [];
    $r = metricas_rango($dias);
    [$w, $p] = metricas_sql_rango('fecha', $r);
    $extra = $solo_vacias ? ' AND resultados = 0' : '';
    $p[] = (int)$top;
    return stats_q("SELECT norm, MIN(termino) AS termino, COUNT(*) AS veces,
            ROUND(AVG(resultados),1) AS prom, MAX(resultados) AS mejor,
            MAX(fecha) AS ultima,
            SUM(CASE WHEN resultados = 0 THEN 1 ELSE 0 END) AS vacias,
            MAX(categoria_id) AS categoria_id
        FROM " . METRICAS_TABLA_BUSQUEDAS . "
        WHERE 1=1 $w $extra
        GROUP BY norm ORDER BY veces DESC, ultima DESC LIMIT ?", $p);
}

/** Cuántas búsquedas llegan por cada puerta (web, chat, banner…). */
function metricas_busquedas_origen($dias) {
    if (!metricas_tabla_lista(METRICAS_TABLA_BUSQUEDAS)) return [];
    $r = metricas_rango($dias);
    [$w, $p] = metricas_sql_rango('fecha', $r);
    return stats_q("SELECT origen, COUNT(*) AS n,
            SUM(CASE WHEN resultados = 0 THEN 1 ELSE 0 END) AS vacias
        FROM " . METRICAS_TABLA_BUSQUEDAS . " WHERE 1=1 $w
        GROUP BY origen ORDER BY n DESC", $p);
}

/** 🔥 TIENDAS CON MÁS PEDIDOS (todos los botones juntos). */
function metricas_top_pedidos($dias, $top = 15) {
    if (!metricas_tabla_lista(METRICAS_TABLA_PEDIDOS)) return [];
    $r = metricas_rango($dias);
    [$w, $p] = metricas_sql_rango('p.fecha', $r);
    $p[] = (int)$top;
    return stats_q("SELECT p.negocio_id, neg.nombre, neg.slug, neg.whatsapp, neg.telefono,
            c.nombre AS rubro, d.nombre AS distrito,
            COUNT(*) AS pedidos,
            SUM(CASE WHEN p.tipo='pedido'   THEN 1 ELSE 0 END) AS carritos,
            SUM(CASE WHEN p.tipo='consulta' THEN 1 ELSE 0 END) AS consultas,
            SUM(CASE WHEN p.tipo='clic'     THEN 1 ELSE 0 END) AS clics,
            SUM(CASE WHEN p.tipo='llamada'  THEN 1 ELSE 0 END) AS llamadas,
            COALESCE(SUM(p.total),0) AS monto,
            MAX(p.fecha) AS ultimo
        FROM " . METRICAS_TABLA_PEDIDOS . " p
        JOIN directorio_negocios neg ON neg.id = p.negocio_id
        LEFT JOIN directorio_categorias c ON c.id = neg.categoria_id
        LEFT JOIN directorio_distritos  d ON d.id = neg.distrito_id
        WHERE 1=1 $w
        GROUP BY p.negocio_id
        ORDER BY pedidos DESC, monto DESC LIMIT ?", $p);
}

/** 📦 PRODUCTOS MÁS PEDIDOS (los que la gente de verdad quiere comprar). */
function metricas_top_productos($dias, $top = 15) {
    if (!metricas_tabla_lista(METRICAS_TABLA_PEDIDOS)) return [];
    $r = metricas_rango($dias);
    [$w, $p] = metricas_sql_rango('p.fecha', $r);
    $p[] = (int)$top;
    return stats_q("SELECT p.producto_id, s.titulo, s.precio, s.unidad,
            neg.nombre AS tienda, neg.slug, neg.whatsapp,
            COUNT(*) AS pedidos, COALESCE(SUM(p.total),0) AS monto
        FROM " . METRICAS_TABLA_PEDIDOS . " p
        JOIN directorio_servicios s ON s.id = p.producto_id
        JOIN directorio_negocios neg ON neg.id = p.negocio_id
        WHERE p.producto_id IS NOT NULL $w
        GROUP BY p.producto_id
        ORDER BY pedidos DESC, monto DESC LIMIT ?", $p);
}

/** 📅 El DÍA RÉCORD del periodo (el que más páginas vistas tuvo). */
function metricas_dia_record($dias) {
    $r = metricas_rango($dias);
    [$w, $p] = metricas_sql_rango('fecha', $r);
    $f = stats_q("SELECT DATE(fecha) AS d, COUNT(*) AS n FROM directorio_stats_eventos
        WHERE tipo='pv' $w GROUP BY DATE(fecha) ORDER BY n DESC LIMIT 1", $p);
    return $f ? $f[0] : null;
}

/** 🕐 La HORA PUNTA y el DÍA DE LA SEMANA más movido. */
function metricas_hora_punta($dias) {
    $r = metricas_rango($dias);
    [$w, $p] = metricas_sql_rango('fecha', $r);
    $f = stats_q("SELECT HOUR(fecha) AS h, COUNT(*) AS n FROM directorio_stats_eventos
        WHERE tipo='pv' $w GROUP BY HOUR(fecha) ORDER BY n DESC LIMIT 1", $p);
    return $f ? $f[0] : null;
}

function metricas_dia_semana($dias) {
    $r = metricas_rango($dias);
    [$w, $p] = metricas_sql_rango('fecha', $r);
    // DAYOFWEEK: 1 = domingo … 7 = sábado
    $f = stats_q("SELECT DAYOFWEEK(fecha) AS dw, COUNT(*) AS n FROM directorio_stats_eventos
        WHERE tipo='pv' $w GROUP BY DAYOFWEEK(fecha) ORDER BY n DESC LIMIT 1", $p);
    return $f ? $f[0] : null;
}

/** 🗺️ DEMANDA POR RUBRO: las vistas de las tiendas más vistas, agrupadas por rubro. */
function metricas_rubros($dias, $top = 15) {
    $r = metricas_rango($dias);
    [$w, $p] = metricas_sql_rango('fecha', $r);
    $p[] = 500;   // se mira el top 500 de fichas vistas (acotado: no pesa)
    $filas = stats_q("SELECT c.id, c.nombre, c.icono, SUM(t.n) AS vistas, COUNT(*) AS tiendas_vistas
        FROM (SELECT pagina, COUNT(*) AS n FROM directorio_stats_eventos
              WHERE tipo_pagina='tienda' $w
              GROUP BY pagina ORDER BY n DESC LIMIT ?) t
        JOIN directorio_negocios n ON n.slug = SUBSTRING(t.pagina, 5)
        JOIN directorio_categorias c ON c.id = n.categoria_id
        GROUP BY c.id ORDER BY vistas DESC", $p);
    if (!$filas) return [];
    $filas = array_slice($filas, 0, (int)$top);

    // La OFERTA de cada uno de esos rubros (cuántas tiendas y productos tiene el sitio)
    $ids = array_map('intval', array_column($filas, 'id'));
    $oferta = metricas_oferta_rubros($ids);
    foreach ($filas as &$f) {
        $id = (int)$f['id'];
        $f['tiendas']   = (int)($oferta[$id]['tiendas'] ?? 0);
        $f['productos'] = (int)($oferta[$id]['productos'] ?? 0);
        $f['vistas']    = (int)$f['vistas'];
        // Vistas por tienda: si es alto con pocas tiendas, ese rubro PIDE más negocios.
        $f['por_tienda'] = $f['tiendas'] > 0 ? round($f['vistas'] / $f['tiendas'], 1) : 0;
    }
    unset($f);
    return $filas;
}

/** Cuántas tiendas y productos tiene cada rubro (oferta del directorio). */
function metricas_oferta_rubros(array $ids) {
    $ids = array_values(array_filter(array_map('intval', $ids)));
    if (!$ids) return [];
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $out = [];
    foreach (stats_q("SELECT categoria_id, COUNT(*) AS n FROM directorio_negocios
            WHERE estado='activo' AND categoria_id IN ($ph) GROUP BY categoria_id", $ids) as $f) {
        $out[(int)$f['categoria_id']]['tiendas'] = (int)$f['n'];
    }
    // Los productos se cuentan por la tienda a la que pertenecen
    foreach (stats_q("SELECT n.categoria_id, COUNT(s.id) AS n FROM directorio_negocios n
            INNER JOIN directorio_servicios s ON s.negocio_id = n.id
            WHERE n.estado='activo' AND n.categoria_id IN ($ph) GROUP BY n.categoria_id", $ids) as $f) {
        $out[(int)$f['categoria_id']]['productos'] = (int)$f['n'];
    }
    return $out;
}

/** 📍 Los DISTRITOS que más se miran. */
function metricas_distritos($dias, $top = 8) {
    $r = metricas_rango($dias);
    [$w, $p] = metricas_sql_rango('fecha', $r);
    $p[] = 500;
    $filas = stats_q("SELECT d.id, d.nombre, SUM(t.n) AS vistas, COUNT(*) AS tiendas_vistas
        FROM (SELECT pagina, COUNT(*) AS n FROM directorio_stats_eventos
              WHERE tipo_pagina='tienda' $w
              GROUP BY pagina ORDER BY n DESC LIMIT ?) t
        JOIN directorio_negocios n ON n.slug = SUBSTRING(t.pagina, 5)
        JOIN directorio_distritos d ON d.id = n.distrito_id
        GROUP BY d.id ORDER BY vistas DESC LIMIT ?", array_merge($p, [(int)$top]));
    return $filas ?: [];
}

/** 💤 Cuántas tiendas activas NO recibieron ni una visita en el periodo (= trabajo comercial). */
function metricas_tiendas_sin_visitas($dias) {
    $r = metricas_rango($dias);
    // ⚠️ El rango va con la columna SIN alias (`fecha`): el filtro se aplica DENTRO de la
    // subconsulta, donde la tabla todavía no se llama `e` (aquí se tropezó la primera versión:
    // «Unknown column 'e.fecha'» y el conteo salía 0 en silencio).
    [$w, $p] = metricas_sql_rango('fecha', $r);
    $con = stats_q("SELECT COUNT(*) AS n FROM directorio_negocios neg
        INNER JOIN (SELECT DISTINCT pagina FROM directorio_stats_eventos
                    WHERE tipo_pagina='tienda' $w) e
          ON e.pagina = CONCAT('neg/', neg.slug)
        WHERE neg.estado='activo'", $p);
    $con_n = (int)($con[0]['n'] ?? 0);
    $activas = stats_q1("SELECT COUNT(*) FROM directorio_negocios WHERE estado='activo'");
    return ['activas' => $activas, 'con_vistas' => $con_n, 'sin_vistas' => max(0, $activas - $con_n)];
}

/** 🔗 CRUZA vistas y pedidos: la CONVERSIÓN por tienda (pedidos por cada 100 vistas). */
function metricas_conversion($dias, $min_vistas = 3, $top = 12) {
    $vistas  = [];
    foreach (metricas_top_tiendas($dias, 200) as $f) $vistas[(int)$f['id']] = (int)$f['vistas'];
    $pedidos = [];
    foreach (metricas_top_pedidos($dias, 200) as $f) $pedidos[(int)$f['negocio_id']] = $f;
    $out = [];
    foreach ($pedidos as $id => $f) {
        $v = (int)($vistas[$id] ?? 0);
        if ($v < (int)$min_vistas) continue;      // con 1 vista, el 100 % no dice nada
        $out[] = [
            'nombre'   => (string)$f['nombre'],
            'slug'     => (string)$f['slug'],
            'rubro'    => (string)($f['rubro'] ?? ''),
            'vistas'   => $v,
            'pedidos'  => (int)$f['pedidos'],
            'tasa'     => round((int)$f['pedidos'] * 100 / $v, 1),
        ];
    }
    usort($out, function ($a, $b) {
        if ($a['tasa'] === $b['tasa']) return $b['pedidos'] <=> $a['pedidos'];
        return $b['tasa'] <=> $a['tasa'];
    });
    return array_slice($out, 0, (int)$top);
}

/** Los nombres de los rubros de una lista de ids (para las tablas de búsquedas). */
function metricas_rubros_nombres(array $ids) {
    $ids = array_values(array_unique(array_filter(array_map('intval', $ids))));
    if (!$ids) return [];
    $ph = implode(',', array_fill(0, count($ids), '?'));
    $out = [];
    foreach (stats_q("SELECT id, nombre, icono FROM directorio_categorias WHERE id IN ($ph)", $ids) as $f) {
        $out[(int)$f['id']] = $f;
    }
    return $out;
}

/** 📈 Cuánto creció (o cayó) algo respecto al periodo anterior. Null si no se puede calcular. */
function metricas_crecimiento($actual, $anterior) {
    $actual = (float)$actual;
    $anterior = (float)$anterior;
    if ($anterior <= 0) return $actual > 0 ? null : 0.0;   // sin base no hay % (se pinta «nuevo»)
    return round(($actual - $anterior) * 100 / $anterior, 1);
}

// ============================================================
// 5) 🧺 TRAER EL HISTÓRICO (sembrar las tablas nuevas con lo ya medido)
// ============================================================

/**
 * Las tablas de récords nacen HOY, así que al principio estarían vacías. Pero el sitio
 * llevaba meses avisando al Telegram de cada búsqueda y de cada pedido, y ese registro
 * (`directorio_avisos_log`) SÍ tiene la fecha, el término y la tienda. Esta función lo
 * vuelca a las tablas nuevas para que el jefe vea récords reales desde el primer momento.
 *
 * Es idempotente por ORIGEN: solo siembra si todavía no hay filas con origen='aviso'.
 * ⚠️ Solo trae lo que el aviso estaba ENCENDIDO y dentro del tope por hora (lo agrupado
 *    también entra, porque es una búsqueda real; lo «duplicado» NO, porque es la repetición
 *    de la misma persona en 5 minutos).
 */
function metricas_sembrar($dias = 180) {
    $res = ['busquedas' => 0, 'pedidos' => 0, 'ok' => false, 'msg' => ''];
    if (!metricas_listas()) {
        $ins = metricas_instalar();
        if (!$ins['ok']) { $res['msg'] = $ins['msg']; return $res; }
    }
    $hay_log = metricas_tabla_lista('directorio_avisos_log');
    if (!$hay_log) { $res['msg'] = 'No hay registro de avisos del que traer datos.'; return $res; }
    $desde = date('Y-m-d H:i:s', time() - max(1, (int)$dias) * 86400);
    try {
        // ---------- 1) BÚSQUEDAS ----------
        $ya = (int)db()->query("SELECT COUNT(*) FROM " . METRICAS_TABLA_BUSQUEDAS . " WHERE origen='aviso'")->fetchColumn();
        if ($ya === 0) {
            // El resumen del aviso se escribe como:  «zapatillas (12 res.)»
            $st = db()->prepare("SELECT resumen, usuario_id, creado_en FROM directorio_avisos_log
                WHERE tipo IN ('busqueda','busqueda_vacia') AND estado IN ('enviado','agrupado')
                  AND es_bot = 0 AND resumen IS NOT NULL AND creado_en >= ?
                ORDER BY id ASC LIMIT 5000");
            $st->execute([$desde]);
            $ins = db()->prepare('INSERT INTO ' . METRICAS_TABLA_BUSQUEDAS . '
                (termino, norm, origen, resultados, categoria_id, usuario_id, dispositivo, ip, fecha)
                VALUES (?,?,?,?,?,?,?,?,?)');
            foreach ($st->fetchAll() as $f) {
                if (!preg_match('/^(.*) \((\d+) res\.\)$/u', (string)$f['resumen'], $m)) continue;
                $term = trim($m[1]);
                $norm = metricas_norm($term);
                if ($norm === '') continue;
                $ins->execute([mb_substr($term, 0, 120), $norm, 'aviso', (int)$m[2], null,
                    !empty($f['usuario_id']) ? (int)$f['usuario_id'] : null, '', null, $f['creado_en']]);
                $res['busquedas']++;
            }
        }

        // ---------- 2) PEDIDOS ----------
        $ya = (int)db()->query("SELECT COUNT(*) FROM " . METRICAS_TABLA_PEDIDOS . " WHERE origen='aviso'")->fetchColumn();
        if ($ya === 0) {
            $st = db()->prepare("SELECT negocio_id, resumen, usuario_id, ip, creado_en FROM directorio_avisos_log
                WHERE tipo = 'lead_precio' AND estado = 'enviado'
                  AND negocio_id IS NOT NULL AND creado_en >= ?
                ORDER BY id ASC LIMIT 5000");
            $st->execute([$desde]);
            $ins = db()->prepare('INSERT INTO ' . METRICAS_TABLA_PEDIDOS . '
                (negocio_id, producto_id, tipo, origen, productos_n, total, descuento_pct, detalle, usuario_id, dispositivo, ip, fecha)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            foreach ($st->fetchAll() as $f) {
                $txt = (string)$f['resumen'];
                $es_carrito = (stripos($txt, 'carrito') !== false);
                $n = 0; $total = null;
                if (preg_match('/(\d+)\s+producto\(s\)/u', $txt, $m2)) $n = (int)$m2[1];
                if (preg_match('/Total referencial:\s*S\/\s*([0-9]+(?:[.,][0-9]+)?)/u', $txt, $m3)) {
                    $total = (float)str_replace(',', '.', $m3[1]);
                }
                $ins->execute([(int)$f['negocio_id'], null, $es_carrito ? 'pedido' : 'clic', 'aviso',
                    $n, $total, null, mb_substr($txt, 0, 255),
                    !empty($f['usuario_id']) ? (int)$f['usuario_id'] : null, '',
                    (string)($f['ip'] ?? ''), $f['creado_en']]);
                $res['pedidos']++;
            }
        }
        $res['ok'] = true;
        $res['msg'] = 'Histórico traído: ' . $res['busquedas'] . ' búsqueda(s) y ' . $res['pedidos'] . ' pedido(s).';
    } catch (Throwable $e) {
        error_log('metricas_sembrar: ' . $e->getMessage());
        $res['msg'] = 'No se pudo traer el histórico: ' . $e->getMessage();
    }
    return $res;
}

/** 🧹 Borra lo viejo de las tablas de récords (retención). Devuelve cuántas filas borró. */
function metricas_limpiar($dias = METRICAS_DIAS_GUARDAR) {
    $limite = date('Y-m-d H:i:s', time() - max(30, (int)$dias) * 86400);
    $n = 0;
    foreach ([METRICAS_TABLA_BUSQUEDAS, METRICAS_TABLA_PEDIDOS] as $tabla) {
        if (!metricas_tabla_lista($tabla)) continue;
        try {
            $st = db()->prepare('DELETE FROM ' . $tabla . ' WHERE fecha < ?');
            $st->execute([$limite]);
            $n += $st->rowCount();
        } catch (Throwable $e) {
            error_log('metricas_limpiar: ' . $e->getMessage());
        }
    }
    return $n;
}

// ============================================================
// 6) AYUDAS DE PRESENTACIÓN (las usa la vista)
// ============================================================

/** Formato corto de un número grande: 1 234 / 12,3 mil. */
function metricas_num($n) {
    $n = (float)$n;
    if ($n >= 1000000) return number_format($n / 1000000, 1, ',', '') . ' M';
    return number_format($n, 0, '.', ' ');
}

/** Nombre del día de la semana a partir del DAYOFWEEK de MySQL (1 = domingo). */
function metricas_dia_nombre($dw) {
    $dias = [1 => 'domingo', 2 => 'lunes', 3 => 'martes', 4 => 'miércoles',
             5 => 'jueves', 6 => 'viernes', 7 => 'sábado'];
    return $dias[(int)$dw] ?? '';
}

/** Etiqueta del origen de una búsqueda. */
function metricas_origen_txt($origen) {
    $map = ['web' => '🔍 Buscador', 'chat' => '🥷 Chat (El ninja)', 'banner' => '📢 Banner',
            'aviso' => '📼 Histórico (Telegram)'];
    return $map[(string)$origen] ?? ucfirst((string)$origen);
}

/** Etiqueta del tipo de pedido. */
function metricas_pedido_tipo_txt($tipo) {
    $map = ['clic' => '💬 WhatsApp de la ficha', 'consulta' => '❓ Preguntó por un producto',
            'pedido' => '🛒 Pedido del carrito', 'llamada' => '📞 Tocó «Llamar»'];
    return $map[(string)$tipo] ?? (string)$tipo;
}

/**
 * 📣 EL RESUMEN PARA ENSEÑAR (el texto que el jefe copia con un botón y le muestra a un socio,
 * a un inversionista o a un banco). Vive AQUÍ, en el motor, y no en la vista, por dos razones:
 *   1) el panel lo pinta con un clic (el jefe NO selecciona texto: Regla inviolable n.º 1), y
 *   2) el futuro aviso semanal por Telegram (`cron_reporte_ventas.php`, pendiente de la guía de
 *      avisos §17) puede mandar ESTE MISMO texto, sin que haya dos verdades.
 *
 * Se le puede pasar lo ya calculado para no repetir consultas:
 *   metricas_resumen_inversionista(30, ['kpis'=>$K, 'anterior'=>$Ka, 'busquedas'=>$bq,
 *                                       'vacias'=>$vac, 'pedidos'=>$ped, 'vistas'=>$vis, 'sin'=>$sin]);
 * Devuelve el texto en varias líneas (string).
 */
function metricas_resumen_inversionista($dias, array $datos = []) {
    $K   = $datos['kpis']     ?? metricas_kpis($dias);
    $Ka  = $datos['anterior'] ?? (($dias > 0) ? metricas_kpis($dias, 1) : $K);
    $bq  = $datos['busquedas'] ?? metricas_top_busquedas($dias, 5);
    $vac = $datos['vacias']    ?? metricas_top_busquedas($dias, 5, true);
    $ped = $datos['pedidos']   ?? metricas_top_pedidos($dias, 5);
    $vis = $datos['vistas']    ?? metricas_top_tiendas($dias, 5);
    $sin = $datos['sin']       ?? metricas_tiendas_sin_visitas($dias);

    $L = [];
    $L[] = 'DECHIMBOTE.COM — NÚMEROS DEL SITIO';
    $L[] = 'Periodo: ' . metricas_rango_txt($dias) . ' · informe del ' . date('d/m/Y H:i');
    $L[] = '';
    $L[] = 'EL DIRECTORIO';
    $L[] = '· ' . metricas_num($K['tiendas_total']) . ' negocios captados (' . metricas_num($K['tiendas_activas']) . ' publicados)';
    $L[] = '· ' . metricas_num($K['productos_total']) . ' productos publicados';
    $L[] = '· ' . metricas_num($K['con_whatsapp']) . ' negocios con WhatsApp directo para recibir pedidos';
    $L[] = '· ' . metricas_num($K['usuarios_total']) . ' usuarios registrados';
    if ($dias > 0) $L[] = '· ' . metricas_num($K['tiendas_nuevas']) . ' negocios nuevos en el periodo';
    $L[] = '';
    $L[] = 'EL MOVIMIENTO DEL PERIODO';
    $L[] = '· ' . metricas_num($K['visitas']) . ' visitas de ' . metricas_num($K['visitantes']) . ' personas distintas';
    $L[] = '· ' . metricas_num($K['paginas']) . ' páginas vistas (' . metricas_num($K['fichas']) . ' aperturas de fichas de negocio)';
    $L[] = '· ' . metricas_num($K['busquedas']) . ' búsquedas hechas por la gente (' . metricas_num($K['busquedas_distintas']) . ' términos distintos)';
    $L[] = '· ' . metricas_num($K['pedidos']) . ' pedidos generados a los negocios';
    $L[] = '   — ' . (int)$K['pedidos_carrito'] . ' pedidos armados con el carrito, por S/ ' . number_format((float)$K['pedidos_monto'], 2);
    $L[] = '   — ' . (int)$K['pedidos_consulta'] . ' consultas por un producto y ' . (int)$K['pedidos_clic'] . ' clics directos a WhatsApp';
    $L[] = '· ' . metricas_num($K['tiendas_con_pedido']) . ' negocios distintos recibieron pedidos';
    $L[] = '';
    if ($dias > 0) {
        $L[] = 'CRECIMIENTO (contra el periodo anterior igual de largo)';
        foreach ([['Visitas', 'visitas'], ['Páginas vistas', 'paginas'], ['Fichas abiertas', 'fichas'],
                  ['Búsquedas', 'busquedas'], ['Pedidos', 'pedidos']] as $cm) {
            $gr = metricas_crecimiento($K[$cm[1]], $Ka[$cm[1]]);
            $L[] = '· ' . $cm[0] . ': ' . metricas_num($K[$cm[1]]) . ' (' .
                ($gr === null ? 'sin base de comparación' : ($gr >= 0 ? '+' : '') . number_format($gr, 1, ',', '') . ' %') . ')';
        }
        $L[] = '';
    }
    if ($K['busquedas'] > 0) {
        $L[] = 'QUÉ PIDE LA GENTE';
        $L[] = '· ' . round($K['busquedas_vacias'] * 100 / max(1, $K['busquedas']), 1) . ' % de las búsquedas no encontró nada: demanda que hoy no está cubierta';
        if ($bq) {
            $t = [];
            foreach ($bq as $b) $t[] = $b['termino'] . ' (' . (int)$b['veces'] . ')';
            $L[] = '· Lo más buscado: ' . implode(', ', $t);
        }
        if ($vac) {
            $t = [];
            foreach ($vac as $v) $t[] = $v['termino'];
            $L[] = '· Buscado y NO disponible: ' . implode(', ', $t) . '  ← negocios por captar';
        }
        $L[] = '';
    }
    if ($ped) {
        $L[] = 'LOS NEGOCIOS QUE MÁS VENDEN POR EL SITIO';
        foreach ($ped as $p) $L[] = '· ' . $p['nombre'] . ' (' . ($p['rubro'] ?: 'sin rubro') . '): ' . (int)$p['pedidos'] . ' pedido(s)';
        $L[] = '';
    }
    if ($vis) {
        $L[] = 'LAS FICHAS MÁS VISTAS';
        foreach ($vis as $t) $L[] = '· ' . $t['nombre'] . ': ' . (int)$t['vistas'] . ' visita(s)';
        $L[] = '';
    }
    $L[] = '· ' . metricas_num($sin['sin_vistas']) . ' de las ' . metricas_num($sin['activas']) . ' tiendas activas aún no reciben visitas: ahí está el trabajo comercial pendiente.';
    return implode("\n", $L);
}
