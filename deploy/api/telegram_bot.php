<?php
/**
 * telegram_bot.php — Comandos del bot de Telegram de DeChimbote.com
 * ============================================================
 * Recibe los webhooks de @Jimmychimbote_bot y responde SOLO al jefe.
 *
 * Comandos:
 *   /buscar <texto>  -> los 3 negocios que más coinciden, con su enlace
 *   /ayuda, /start   -> ayuda corta
 *
 * INSTALACIÓN (una sola vez, ver GUIA TELEGRAM .md):
 *   https://api.telegram.org/bot<TOKEN>/setWebhook?url=https://dechimbote.com/api/telegram_bot.php&secret_token=ChimboteBot2026_Xk9pLm4Qz7
 *   El secret_token es obligatorio: Telegram lo manda en la cabecera
 *   X-Telegram-Bot-Api-Secret-Token y así verificamos que la petición es real.
 *
 * Seguridad:
 *   - Solo POST (el GET solo sirve para comprobar que el archivo está arriba).
 *   - Cabecera secreta de Telegram (hash_equals, sin filtrar tiempos).
 *   - Solo se responde al Chat ID del jefe (8333560284).
 *   - Consultas con prepared statements (el término NUNCA se concatena al SQL).
 */

require_once __DIR__ . '/../config.php';

header('Content-Type: text/plain; charset=utf-8');

// ====== 1) Diagnóstico rápido: abrir la URL en el navegador ======
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    echo "Bot de DeChimbote.com activo.\n";
    echo "Webhook listo para recibir mensajes de Telegram por POST.\n";
    exit;
}

// ====== 2) ¿La petición viene de Telegram? ======
$secreto_esperado = TELEGRAM_WEBHOOK_SECRET;
$secreto_recibido = (string)($_SERVER['HTTP_X_TELEGRAM_BOT_API_SECRET_TOKEN'] ?? '');
if ($secreto_esperado === '' || !hash_equals($secreto_esperado, $secreto_recibido)) {
    error_log('telegram_bot: petición rechazada (cabecera secreta inválida) desde ' . (ip_real()));
    http_response_code(403);
    echo 'Acceso denegado.';
    exit;
}

// Telegram publica sus rangos: 149.154.160.0/20 y 91.108.4.0/22.
// La cabecera secreta ya nos protege; esto solo deja rastro por si algo raro llega.
$ip = (string)(ip_real());
if (!preg_match('/^(149\.154\.(1[6-9][0-9]|2[0-9][0-9])\.|91\.108\.[4-7]\.)/', $ip)) {
    error_log('telegram_bot: aviso, IP fuera de los rangos de Telegram: ' . $ip);
}

// ====== 3) Leer el mensaje de Telegram ======
$cuerpo = file_get_contents('php://input');
$update = json_decode((string)$cuerpo, true);

if (!is_array($update) || empty($update['message'])) {
    // Otros eventos (ediciones, canales, stickers sin texto…): no hay nada que hacer.
    echo 'ok';
    exit;
}

$mensaje = $update['message'];
$chat_id = isset($mensaje['chat']['id']) ? (string)$mensaje['chat']['id'] : '';
$texto   = isset($mensaje['text']) ? trim((string)$mensaje['text']) : '';
$de      = trim((string)($mensaje['from']['first_name'] ?? '') . ' ' . (string)($mensaje['from']['last_name'] ?? ''));

// ====== 4) EL JEFE manda el bot; cualquier OTRA persona puede SUSCRIBIRSE ======
// (Módulo «Encargos», 2026-09-15.) Antes, todo el que no fuera el jefe quedaba ignorado. Ahora los
// dueños de tienda (y cualquiera que quiera) pueden engancharse para recibir los ENCARGOS de su
// rubro y su zona: la caja de `/encargos` les da un código, el botón abre el bot con ese código y
// al tocar START se guarda su chat_id. `/stop` los da de baja.
if ($chat_id === '') {
    echo 'ok';
    exit;
}

if ($chat_id !== (string)TELEGRAM_CHAT_JEFE) {
    require_once __DIR__ . '/../includes/telegram_subs.php';

    $limpio_s  = preg_replace('/@[A-Za-z0-9_]+/', '', $texto);          // «/start@Jimmychimbote_bot»
    $partes_s  = (array)preg_split('/\s+/u', trim((string)$limpio_s), 2);
    $comando_s = mb_strtolower((string)($partes_s[0] ?? ''));
    $arg_s     = trim((string)($partes_s[1] ?? ''));

    // /start <código> → engancha su Telegram a los encargos de su rubro y su zona.
    if ($comando_s === '/start' && $arg_s !== '') {
        $fila = tg_subs_activar($arg_s, $chat_id, $de);
        telegram_enviar($chat_id, $fila
            ? implode("\n", [
                '✅ ¡Listo' . ($de !== '' ? ', ' . $de : '') . '! Ya te aviso aquí.',
                '',
                'Te voy a escribir cuando alguien busque algo de ' . tg_subs_interes_txt($fila) . ' y nadie lo venda.',
                'Así te enteras antes que los demás y puedes ofrecer tu producto.',
                '',
                '🔗 Ver los encargos de ahora: ' . url('encargos'),
                '',
                'Si algún día no quieres recibirlos, escribe /stop',
              ])
            : "Ese enlace ya no sirve (o pasaron más de 7 días).\n\nEntra a " . url('encargos')
              . " y toca otra vez «Recibir en mi Telegram».");
        echo 'ok';
        exit;
    }

    if ($comando_s === '/stop' || $comando_s === '/baja') {
        $habia = tg_subs_baja($chat_id);
        telegram_enviar($chat_id, $habia
            ? "👋 Listo, no te aviso más.\n\nSi quieres volver, entra a " . url('encargos') . '.'
            : "No tenías avisos activados. Entra a " . url('encargos') . ' si quieres recibirlos.');
        echo 'ok';
        exit;
    }

    // Cualquier otro mensaje de alguien que no es el jefe: se le explica en dos líneas.
    telegram_enviar($chat_id, implode("\n", [
        '🤖 Soy el bot de DeChimbote.com.',
        '',
        'Te aviso cuando alguien busca algo que nadie vende en Chimbote (encargos).',
        'Para activarlo entra aquí y toca «📲 Recibir en mi Telegram»:',
        '👉 ' . url('encargos'),
    ]));
    echo 'ok';
    exit;
}

if ($texto === '') {
    echo 'ok';
    exit;
}

// ====== 5) Comandos ======
// Se admite también "/buscar@Jimmychimbote_bot ..." (forma que usa Telegram en grupos).
$limpio = preg_replace('/@[A-Za-z0-9_]+/', '', $texto);
$partes = (array)preg_split('/\s+/u', trim((string)$limpio), 2);
$comando = mb_strtolower((string)($partes[0] ?? ''));
$argumento = trim((string)($partes[1] ?? ''));

if ($comando === '/buscar') {
    if ($argumento === '') {
        telegram_enviar($chat_id, "🔍 ¿Qué buscas?\n\nEscribe por ejemplo:\n/buscar zapatillas");
        echo 'ok';
        exit;
    }
    $termino = bot_limpiar_termino($argumento);
    $negocios = bot_buscar_negocios($termino, 3);
    telegram_enviar($chat_id, $negocios
        ? bot_formato_resultados($termino, $negocios)
        : "❌ No encontré negocios con '{$termino}'. Intenta con otro término.");
    echo 'ok';
    exit;
}

if ($comando === '/start' || $comando === '/ayuda' || $comando === '/help') {
    telegram_enviar($chat_id, implode("\n", [
        '🤖 Bot de DeChimbote.com',
        '',
        'Comandos disponibles:',
        '🔍 /buscar <texto> — los 3 negocios que más coinciden con su enlace.',
        '   Ejemplo: /buscar pollo',
        '',
        'También te aviso aquí de: reportes de contenido, alertas del servidor',
        '(disco, CPU, memoria) y avisos de ventas.',
    ]));
    echo 'ok';
    exit;
}

telegram_enviar($chat_id, "🤖 No conozco ese comando.\n\nPrueba con:\n🔍 /buscar <texto>   (ejemplo: /buscar pollo)");
echo 'ok';
exit;

// ============================================================
// Funciones del bot
// ============================================================

/**
 * Limpia el término de búsqueda: sin etiquetas, sin caracteres de control,
 * sin espacios repetidos y con un máximo razonable de largo.
 */
function bot_limpiar_termino($texto) {
    $t = strip_tags((string)$texto);
    $t = preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $t);
    $t = preg_replace('/\s+/u', ' ', (string)$t);
    $t = trim((string)$t, " \t\n\r\0\x0B\"'");
    if (mb_strlen($t) > 60) $t = mb_substr($t, 0, 60);
    return trim((string)$t);
}

/**
 * Devuelve hasta $limite negocios ACTIVOS que coincidan con el término,
 * ordenados por relevancia: primero los que coinciden en el nombre,
 * luego en el rubro (categoría), luego en la descripción y por último
 * los que tienen un producto con ese texto. Dentro de cada grupo, los
 * más vistos.
 *
 * El término viaja SIEMPRE como parámetro del prepared statement:
 * no hay forma de que se cuele SQL (por eso no se usa e() aquí: e() es
 * para HTML, no para SQL).
 */
function bot_buscar_negocios($termino, $limite = 3) {
    $termino = trim((string)$termino);
    if ($termino === '') return [];

    $limite = max(1, min(10, (int)$limite));
    $vistos = [];

    // Se prueba el término tal cual y, si no hay nada, su variante
    // singular/plural ("zapatillas" -> "zapatilla") porque en español
    // la gente escribe indistintamente.
    foreach (bot_variantes_termino($termino) as $variante) {
        $filas = bot_consulta_negocios($variante, $limite);
        foreach ($filas as $f) {
            $id = (int)$f['id'];
            if (isset($vistos[$id])) continue;
            $vistos[$id] = $f;
            if (count($vistos) >= $limite) break 2;
        }
    }

    return array_values($vistos);
}

/** Variantes simples: el término y su singular/plural. */
function bot_variantes_termino($termino) {
    $variantes = [$termino];
    $largo = mb_strlen($termino);
    if ($largo > 3 && mb_substr($termino, -1) === 's') {
        $variantes[] = mb_substr($termino, 0, -1);        // zapatillas -> zapatilla
    } elseif ($largo > 2) {
        $variantes[] = $termino . 's';                    // zapato -> zapatos
    }
    return array_values(array_unique($variantes));
}

/** Consulta SQL de coincidencias (prepared statement). */
function bot_consulta_negocios($termino, $limite) {
    $like = '%' . $termino . '%';

    $sql = "SELECT n.id, n.nombre, n.slug, n.vistas_count, n.rating,
                   c.nombre AS categoria_nombre, c.icono AS categoria_icono,
                   d.nombre AS distrito_nombre,
                   (n.nombre    LIKE :l1) AS m_nombre,
                   (c.nombre    LIKE :l2) AS m_rubro,
                   (n.descripcion LIKE :l3) AS m_desc,
                   (prod.negocio_id IS NOT NULL) AS m_producto
            FROM directorio_negocios n
            LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
            LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
            LEFT JOIN (
                SELECT s.negocio_id
                FROM directorio_servicios s
                WHERE s.titulo LIKE :l4 AND s.activo = 1 AND " . sql_producto_vigente('s') . "
                GROUP BY s.negocio_id
            ) prod ON prod.negocio_id = n.id
            WHERE n.estado = 'activo'
              AND (n.nombre LIKE :l5 OR c.nombre LIKE :l6 OR n.descripcion LIKE :l7 OR prod.negocio_id IS NOT NULL)
            ORDER BY m_nombre DESC, m_rubro DESC, m_desc DESC, m_producto DESC,
                     n.vistas_count DESC, n.rating DESC
            LIMIT " . (int)$limite;

    try {
        $st = db()->prepare($sql);
        $st->execute([
            ':l1' => $like, ':l2' => $like, ':l3' => $like, ':l4' => $like,
            ':l5' => $like, ':l6' => $like, ':l7' => $like,
        ]);
        return $st->fetchAll();
    } catch (Throwable $e) {
        error_log('telegram_bot (consulta): ' . $e->getMessage());
        return [];
    }
}

/** Formato de la respuesta que recibe el jefe. */
function bot_formato_resultados($termino, array $negocios) {
    $numeros = ['1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣', '🔟'];

    $lineas = [];
    $lineas[] = '🔍 RESULTADOS PARA "' . $termino . '":';
    $lineas[] = '';

    foreach ($negocios as $i => $n) {
        $icono = trim((string)($n['categoria_icono'] ?? '')) ?: '🏪';
        $rubro = trim((string)($n['categoria_nombre'] ?? '')) ?: 'Negocio';
        $distrito = trim((string)($n['distrito_nombre'] ?? '')) ?: 'Chimbote';

        $lineas[] = ($numeros[$i] ?? ('▪️ ' . ($i + 1) . '.')) . ' ' . (string)$n['nombre'];
        $lineas[] = $icono . ' ' . $rubro . ' · ' . $distrito;
        $lineas[] = '🔗 ' . url_negocio((string)$n['slug']);
        $lineas[] = '';
    }

    return rtrim(implode("\n", $lineas));
}
