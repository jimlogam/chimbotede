<?php
/**
 * avisos.php — Motor de avisos del sitio por Telegram
 * ============================================================
 * Todo lo que pasa en dechimbote.com se avisa al Telegram del jefe con este motor.
 *
 * Cómo se usa (desde cualquier parte del sitio):
 *     aviso('busqueda', ['termino' => 'zapatillas', 'resultados' => 12]);
 *
 * El motor se encarga de:
 *   1) saber si ese aviso está encendido (tabla directorio_avisos_config, panel 🔔 Avisos),
 *   2) no repetir el mismo evento (dedupe por IP/tienda y por término),
 *   3) no saturar (topes por hora; lo que se pasa se agrupa en el resumen de la hora),
 *   4) separar personas de robots (un resumen por hora para los robots),
 *   5) dar formato bonito y ENVIARLO DESPUÉS de responder al visitante
 *      (litespeed_finish_request): la web nunca se frena por Telegram,
 *   6) dejar registro en directorio_avisos_log para el panel y los resúmenes.
 *
 * Regla de oro del proyecto: el aviso NUNCA puede romper la web. Todo va en
 * try/catch y si algo falla se registra en el log de errores y sigue la vida.
 */

require_once __DIR__ . '/config_avisos.php';

// ============================================================
// CATÁLOGO DE AVISOS (lo que el jefe puede encender/apagar)
// ============================================================

/** Nombre del día de la semana (0 = domingo), para los rótulos del panel. */
function avisos_dia_semana_nombre(int $dia): string {
    $dias = ['domingo', 'lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
    return $dias[max(0, min(6, $dia))];
}

function avisos_catalogo(): array {
    return [
        // ---- Visitas y búsquedas ----
        'busqueda'        => ['grupo' => 'Visitas y búsquedas', 'titulo' => '🔍 Búsquedas (con término)',        'defecto' => 1, 'nota' => 'Cada búsqueda que hace una persona o un anónimo.'],
        'busqueda_vacia'  => ['grupo' => 'Visitas y búsquedas', 'titulo' => '🕳️ Búsquedas sin resultados',       'defecto' => 1, 'nota' => 'Lo que busca la gente y no encuentra: oportunidad de captar negocios.'],
        // 🛒 ENCARGOS (módulo del 2026-09-15, pedido del jefe): la búsqueda que no encuentra nada ya no
        // se queda en un aviso al jefe — el encargo se PUBLICA en /encargos para que lo vean los
        // vendedores. El nombre lo eligió el jefe; el tipo interno sigue siendo `pedido_sin_vendedor`.
        'pedido_sin_vendedor' => ['grupo' => 'Visitas y búsquedas', 'titulo' => '🛒 Encargos (lo que buscan y nadie vende)', 'defecto' => 1, 'nota' => 'Alguien buscó algo que el sitio NO tiene: el encargo queda publicado en /en-vivo. Avisa cuando nace, cuando ya lo buscan varios, cuando un vendedor ofrece y cuando el comprador deja su WhatsApp. El mensaje trae los enlaces para quitarlo o marcarlo como conseguido (un toque, sin panel).'],
        'visita_tienda'   => ['grupo' => 'Visitas y búsquedas', 'titulo' => '👁️ Visitas a una tienda (solo personas)', 'defecto' => 1, 'nota' => 'Avisa solo si hay señales de persona real (sesión, llega de Google/redes o ya había entrado antes).'],
        'visita_producto' => ['grupo' => 'Visitas y búsquedas', 'titulo' => '📦 Visitas a un producto (solo personas)', 'defecto' => 1, 'nota' => 'Igual que las visitas a tienda, con el producto concreto.'],
        'visita_robot'    => ['grupo' => 'Visitas y búsquedas', 'titulo' => '🤖 Resumen de robots (por hora)',   'defecto' => 1, 'nota' => 'Googlebot, AhrefsBot, scrapers… en un solo mensaje por hora.'],

        // ---- Tiendas y productos ----
        'tienda_nueva'    => ['grupo' => 'Tiendas y productos', 'titulo' => '🏪 Tienda nueva creada',            'defecto' => 1, 'nota' => 'Negocios creados desde el formulario, el asistente o Caminante.'],
        'producto_nuevo'  => ['grupo' => 'Tiendas y productos', 'titulo' => '📦 Producto nuevo o editado',       'defecto' => 1, 'nota' => 'Cuando un negocio agrega o cambia un producto de su ficha.'],
        'producto_borrado'=> ['grupo' => 'Tiendas y productos', 'titulo' => '🗑️ Producto eliminado',             'defecto' => 1, 'nota' => 'Cuando un negocio borra un producto.'],
        'foto_nueva'      => ['grupo' => 'Tiendas y productos', 'titulo' => '📷 Fotos nuevas en una ficha',      'defecto' => 1, 'nota' => 'Fotos que sube un negocio a su ficha o a un producto.'],
        'caminante_incompleto' => ['grupo' => 'Tiendas y productos', 'titulo' => '📵 Caminante: subida incompleta', 'defecto' => 1, 'nota' => 'Cuando una captura en la calle llega con menos fotos de las que el celular envió, o con fotos rechazadas. Revisa el registro en caminante/_registro_subidas.log.'],

        // ---- Personas ----
        'usuario_nuevo'   => ['grupo' => 'Personas', 'titulo' => '👤 Usuario nuevo registrado',       'defecto' => 1, 'nota' => 'Altas con correo o con Google.'],
        // 🔑 OLVIDÓ SU CONTRASEÑA (módulo del 2026-09-16): el dueño que no puede entrar pide ayuda
        // desde `/recuperar` (usuario = su WhatsApp, y la clave solo se le mostró una vez cuando
        // El maestro 🛠️ le creó la cuenta). El mensaje trae su usuario, su teléfono y el enlace al
        // panel, donde el botón «🔑 Restablecer contraseña» le da una nueva en un toque.
        'clave_olvidada'  => ['grupo' => 'Personas', 'titulo' => '🔑 Olvidó su contraseña (pide ayuda)', 'defecto' => 1, 'nota' => 'Un dueño pidió recuperar su contraseña desde /recuperar. Se le da una clave nueva en Súper Admin → 👥 Usuarios (botón 🔑).'],
        'lead_precio'     => ['grupo' => 'Personas', 'titulo' => '🔥 Pidieron precio (WhatsApp)',     'defecto' => 1, 'nota' => 'Lead caliente: alguien hizo clic para pedir precio.'],
        'llamada_tienda'  => ['grupo' => 'Personas', 'titulo' => '📞 Tocaron «Llamar» en una ficha',   'defecto' => 1, 'nota' => 'Lead caliente igual que el de WhatsApp: el visitante marcó el teléfono de la tienda.'],
        'opinion_nueva'   => ['grupo' => 'Personas', 'titulo' => '💬 Opinión nueva en una ficha',      'defecto' => 1, 'nota' => 'Alguien dejó su opinión anónima en una tienda (con sus estrellas).'],
        // 🧭 EL MURO DEL EXPLORER (2026-09-16): los comentarios de las publicaciones del Explorer
        // («/explorer.php»). NO son opiniones de la ficha: estos no llevan estrellas y no cambian el
        // ⭐ de la tienda; son la conversación del muro.
        'explorer_comentario' => ['grupo' => 'Personas', 'titulo' => '🧭 Comentario en el muro (Explorer)', 'defecto' => 1, 'nota' => 'Alguien comentó una publicación del muro del Explorer (sin estrellas: eso es de las opiniones de la ficha).'],
        'reclamo'         => ['grupo' => 'Personas', 'titulo' => '🙋 Reclamo de negocio',             'defecto' => 1, 'nota' => 'Alguien dice "este negocio es mío".'],
        'reporte_contenido' => ['grupo' => 'Personas', 'titulo' => '🚩 Reportes de contenido',         'defecto' => 1, 'nota' => 'Alerta inmediata cuando un usuario reporta un negocio o un producto (fraude, contenido inapropiado…).'],
        'postulante'      => ['grupo' => 'Personas', 'titulo' => '💼 Postulación (Trabaja con nosotros)', 'defecto' => 1, 'nota' => 'Nuevos candidatos.'],
        'empleo'          => ['grupo' => 'Personas', 'titulo' => '💼 Aviso de empleo nuevo', 'defecto' => 1, 'nota' => 'Aviso nuevo, o uno enviado por un visitante que espera tu aprobación (el mensaje trae los enlaces ✅ Aprobar y 🗑️ Rechazar: un toque y queda publicado o descartado).'],
        // 🗑️ 2026-09-13: aquí estaba el aviso 'chat_comunidad' (cada mensaje del chat público).
        // Se retiró con el chat comunitario completo: su API ya no existe, así que ese aviso
        // nunca más se va a disparar y no tiene sentido ofrecerlo en Súper Admin → 📱 Telegram.

        // ---- Publicidad ----
        'banner_clic'     => ['grupo' => 'Publicidad', 'titulo' => '🖱️ Clic en un banner',              'defecto' => 0, 'nota' => 'Cada clic en la publicidad. Apagado: los banners rotan mucho.'],

        // ---- Sistema y errores ----
        'pagina_404'      => ['grupo' => 'Sistema y errores', 'titulo' => '🔗 Enlaces rotos (404)',            'defecto' => 1, 'nota' => 'Páginas que no existen: enlaces malos o contenido borrado.'],
        'error_sitio'     => ['grupo' => 'Sistema y errores', 'titulo' => '💥 Errores del sitio',              'defecto' => 1, 'nota' => 'Fallos de PHP y caídas de la base de datos. Se avisa una vez por error distinto.'],
        'resumen_hora'    => ['grupo' => 'Sistema y errores', 'titulo' => '📊 Resumen de la última hora',      'defecto' => 1, 'nota' => 'Personas que entraron, clics de pedir, búsquedas y robots: todo cruzado en un mensaje por hora.'],
        'resumen_dia'     => ['grupo' => 'Sistema y errores', 'titulo' => '📅 Resumen del día (' . AVISOS_RESUMEN_DIA_HORA . ':00)',        'defecto' => 0, 'nota' => 'Los números del día en un mensaje (el detalle fino va en el 🧠 Informe del día).'],
        // ⚠️ La HORA que se escribe en el título sale de la constante de verdad
        // (AVISOS_INFORME_HORA): antes el rótulo decía «21:00» y el envío era a las 22:00, y el
        // jefe se quedaba esperando a la hora equivocada (desfase encontrado el 2026-09-15).
        'informe_dia'     => ['grupo' => 'Sistema y errores', 'titulo' => '🧠 Informe inteligente del día (' . AVISOS_INFORME_HORA . ':00)', 'defecto' => 1, 'nota' => 'El parte del cierre del día: personas de verdad, la tienda que más destaca, quién pidió llamada o WhatsApp, lo que buscan y no encuentran y lo que espera tu decisión.'],
        'informe_semana'  => ['grupo' => 'Sistema y errores', 'titulo' => '🏆 Informe de la semana (' . avisos_dia_semana_nombre(AVISOS_INFORME_SEMANA_DIA) . ' ' . AVISOS_INFORME_HORA . ':00)', 'defecto' => 1, 'nota' => 'Igual que el del día, pero con los 7 días y comparado con la semana anterior: la tienda que está destacando más que las demás.'],
        'alerta_sistema'  => ['grupo' => 'Sistema y errores', 'titulo' => '⚠️ Alertas del servidor (disco/CPU/memoria)', 'defecto' => 1, 'nota' => 'Avisa si el hosting se acerca a sus límites. Necesita el Cron Job corregido para funcionar cada hora.'],
        'noticias_dia'    => ['grupo' => 'Sistema y errores', 'titulo' => '📰 Noticias del día publicadas', 'defecto' => 1, 'nota' => 'El robot de noticias (06:00) ya publicó las noticias locales del día en /noticias. Un mensaje al día con los titulares.'],
    ];
}

// ============================================================
// TABLAS Y CONFIGURACIÓN
// ============================================================
function avisos_tablas_ok(): bool {
    static $ok = null;
    if ($ok !== null) return $ok;
    try {
        db()->query('SELECT 1 FROM ' . AVISOS_TABLA_LOG . ' LIMIT 1');
        $ok = true;
    } catch (Throwable $e) {
        $ok = false;   // sin migración: el motor sigue funcionando, pero sin dedupe ni registro
    }
    return $ok;
}

function avisos_config(): array {
    static $cfg = null;
    if ($cfg !== null) return $cfg;
    $cfg = [];
    foreach (avisos_catalogo() as $tipo => $info) $cfg[$tipo] = (int)$info['defecto'];
    try {
        foreach (db()->query('SELECT tipo, activo FROM ' . AVISOS_TABLA_CONFIG) as $r) {
            if (isset($cfg[$r['tipo']])) $cfg[$r['tipo']] = (int)$r['activo'];
        }
    } catch (Throwable $e) {
        // sin tabla: se usan los valores por defecto
    }
    return $cfg;
}

function aviso_activo(string $tipo): bool {
    $cfg = avisos_config();
    return !empty($cfg[$tipo]);
}

function avisos_config_guardar(string $tipo, int $activo): bool {
    if (!isset(avisos_catalogo()[$tipo])) return false;
    try {
        db()->prepare('INSERT INTO ' . AVISOS_TABLA_CONFIG . ' (tipo, activo, actualizado_en) VALUES (?,?,?)
                       ON DUPLICATE KEY UPDATE activo = VALUES(activo), actualizado_en = VALUES(actualizado_en)')
            ->execute([$tipo, $activo ? 1 : 0, date('Y-m-d H:i:s')]);
        return true;
    } catch (Throwable $e) {
        error_log('avisos_config_guardar: ' . $e->getMessage());
        return false;
    }
}

// ============================================================
// CONTEXTO: quién es el visitante
// ============================================================
/** ¿Es un robot? Devuelve el nombre del robot o '' si parece una persona. */
function avisos_robot(string $ua): string {
    static $patrones = [
        'Googlebot' => 'googlebot', 'Google Images' => 'googlebot-image', 'Bingbot' => 'bingbot',
        'AhrefsBot' => 'ahrefsbot', 'SemrushBot' => 'semrushbot', 'MJ12bot' => 'mj12bot',
        'DotBot' => 'dotbot', 'PetalBot' => 'petalbot', 'Bytespider' => 'bytespider',
        'GPTBot (OpenAI)' => 'gptbot', 'ChatGPT-User' => 'chatgpt-user', 'OAI-SearchBot' => 'oai-searchbot',
        'ClaudeBot (Anthropic)' => 'claudebot', 'PerplexityBot' => 'perplexitybot',
        'Applebot' => 'applebot', 'YandexBot' => 'yandexbot', 'Baiduspider' => 'baiduspider',
        'DuckDuckBot' => 'duckduckbot', 'Amazonbot' => 'amazonbot', 'CCBot' => 'ccbot',
        'DataForSeo' => 'dataforseo', 'SeznamBot' => 'seznambot', 'Sogou' => 'sogou',
        'Facebook' => 'facebookexternalhit', 'WhatsApp' => 'whatsapp', 'Telegram' => 'telegrambot',
        'Twitter/X' => 'twitterbot', 'LinkedIn' => 'linkedinbot', 'Slack' => 'slackbot',
        'Discord' => 'discordbot', 'UptimeRobot' => 'uptimerobot', 'Pingdom' => 'pingdom',
        'Navegador headless' => 'headlesschrome', 'Puppeteer' => 'puppeteer', 'Playwright' => 'playwright',
        'PhantomJS' => 'phantomjs', 'Guion (python)' => 'python', 'Guion (curl)' => 'curl',
        'Guion (wget)' => 'wget', 'Go-http-client' => 'go-http-client', 'Java' => 'java/',
        'Scrapy' => 'scrapy', 'Axios' => 'axios', 'Node fetch' => 'node-fetch', 'Undici' => 'undici',
        'Escáner (zgrab)' => 'zgrab', 'Escáner (nmap)' => 'nmap', 'Escáner (nuclei)' => 'nuclei',
        'Escáner (nikto)' => 'nikto', 'Censys' => 'censys',
    ];
    $ua_l = strtolower($ua);
    foreach ($patrones as $nombre => $patron) {
        if (strpos($ua_l, $patron) !== false) return $nombre;
    }
    if (trim($ua) === '' || $ua === '-') return 'Sin user-agent';
    if (preg_match('/\b(bot|crawler|spider|scraper|monitor|fetcher)\b/i', $ua)) return 'Robot genérico';
    return '';
}

function avisos_dispositivo(string $ua): string {
    if (preg_match('/iPad|Tablet/i', $ua)) return '📟 Tablet';
    if (preg_match('/Mobile|Android|iPhone|iPod|Opera Mini/i', $ua)) return '📱 Móvil';
    return '🖥️ Computadora';
}

function avisos_origen(string $referer): string {
    if ($referer === '') return 'directo (sin referencia)';
    $host = parse_url($referer, PHP_URL_HOST) ?: '';
    $h = strtolower($host);
    if ($h === '') return 'directo';
    if (strpos($h, 'google.') !== false) return 'desde Google';
    if (strpos($h, 'bing.') !== false) return 'desde Bing';
    if (strpos($h, 'l.instagram') !== false || strpos($h, 'instagram') !== false) return 'desde Instagram';
    if (strpos($h, 'facebook') !== false) return 'desde Facebook';
    if (strpos($h, 'wa.me') !== false || strpos($h, 'whatsapp') !== false) return 'desde WhatsApp';
    if (strpos($h, 'tiktok') !== false) return 'desde TikTok';
    if (strpos($h, 'dechimbote.com') !== false) return 'navegando en el sitio';
    return 'desde ' . $h;
}

function avisos_quien(): string {
    $u = usuario_actual();
    if ($u) {
        $n = trim((string)($u['nombre'] ?? ''));
        return $n !== '' ? ($n . ' (con sesión)') : 'Usuario con sesión';
    }
    return 'Anónimo';
}

function avisos_contexto(): array {
    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    $bot = avisos_robot($ua);
    return [
        'ip'         => (string)(ip_real()),
        'ua'         => $ua,
        'bot'        => $bot,
        'es_bot'     => $bot !== '' ? 1 : 0,
        'dispositivo'=> avisos_dispositivo($ua),
        'origen'     => avisos_origen((string)($_SERVER['HTTP_REFERER'] ?? '')),
        'quien'      => avisos_quien(),
        'hora'       => date('d/m H:i'),
        'ruta'       => (string)($_SERVER['REQUEST_URI'] ?? ''),
        // 👤 La huella anónima del dispositivo (cookie `cz_stats` del módulo de estadísticas).
        // Es la prueba más limpia de que detrás hay un navegador de verdad: un robot con una IP
        // distinta cada vez casi nunca trae esta cookie, y una persona sí (es su mismo celular).
        'cookie'     => (string)($_COOKIE['cz_stats'] ?? ''),
    ];
}

// ============================================================
// ENVÍO DIFERIDO (la web responde primero, Telegram después)
// ============================================================
function avisos_encolar(string $texto): void {
    static $cola = [];
    static $registrado = false;
    $cola[] = $texto;

    if ($registrado) return;
    $registrado = true;

    register_shutdown_function(function () use (&$cola) {
        // 1) Se cierra la respuesta al visitante (LiteSpeed) para no hacerle esperar
        if (PHP_SAPI !== 'cli' && function_exists('litespeed_finish_request')) {
            @litespeed_finish_request();
        }
        // 2) Ahora sí, se manda a Telegram
        foreach ($cola as $t) {
            try {
                telegram_enviar(TELEGRAM_CHAT_JEFE, $t);
            } catch (Throwable $e) {
                error_log('avisos (envío): ' . $e->getMessage());
            }
        }
    });
}

// ============================================================
// REGISTRO Y CONTROL DE SATURACIÓN
// ============================================================
function avisos_log(string $tipo, string $estado, array $datos = []): void {
    if (!avisos_tablas_ok()) return;
    try {
        db()->prepare('INSERT INTO ' . AVISOS_TABLA_LOG . '
            (tipo, estado, clave, resumen, ip, user_agent, es_bot, bot, negocio_id, usuario_id, creado_en)
            VALUES (?,?,?,?,?,?,?,?,?,?,?)')
            ->execute([
                $tipo,
                $estado,
                isset($datos['clave']) ? mb_substr((string)$datos['clave'], 0, 120) : null,
                isset($datos['resumen']) ? mb_substr((string)$datos['resumen'], 0, 240) : null,
                isset($datos['ip']) ? mb_substr((string)$datos['ip'], 0, 45) : null,
                isset($datos['ua']) ? mb_substr((string)$datos['ua'], 0, 255) : null,
                (int)($datos['es_bot'] ?? 0),
                isset($datos['bot']) ? mb_substr((string)$datos['bot'], 0, 40) : null,
                isset($datos['negocio_id']) ? (int)$datos['negocio_id'] : null,
                isset($datos['usuario_id']) ? (int)$datos['usuario_id'] : null,
                date('Y-m-d H:i:s'),
            ]);
    } catch (Throwable $e) {
        error_log('avisos_log: ' . $e->getMessage());
    }
}

/**
 * Fecha/hora de hace N minutos en HORA DE PERÚ.
 * OJO: el MySQL del hosting va en UTC, así que NUNCA se compara contra NOW()
 * (las fechas del sitio se guardan con date() de PHP, en hora de Perú).
 */
function avisos_desde(int $minutos): string {
    return date('Y-m-d H:i:s', time() - max(0, $minutos) * 60);
}

/** ¿Ya se avisó este evento recientemente? (dedupe) */
function avisos_duplicado(string $clave, int $minutos): bool {
    if (!avisos_tablas_ok() || $clave === '' || $minutos <= 0) return false;
    try {
        $st = db()->prepare('SELECT COUNT(*) FROM ' . AVISOS_TABLA_LOG . "
            WHERE clave = ? AND estado = 'enviado' AND creado_en > ?");
        $st->execute([$clave, avisos_desde($minutos)]);
        return (int)$st->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

/** ¿Se pasó el tope de mensajes por hora para este tipo (o en total)? */
function avisos_tope_alcanzado(string $tipo, string $filtro = 'tipo'): bool {
    if (!avisos_tablas_ok()) return false;
    try {
        $tope_tipo = [
            'visita_tienda'   => AVISOS_TOPE_HORA_VISTAS,
            'visita_producto' => AVISOS_TOPE_HORA_VISTAS,
            'busqueda'        => AVISOS_TOPE_HORA_BUSQUEDAS,
            'busqueda_vacia'  => AVISOS_TOPE_HORA_BUSQUEDAS,
            'pagina_404'      => AVISOS_TOPE_HORA_404,
            'error_sitio'     => AVISOS_TOPE_HORA_ERRORES,
        ];
        $limite = $tope_tipo[$tipo] ?? AVISOS_TOPE_HORA_TOTAL;
        $desde  = avisos_desde(60);

        $st = db()->prepare('SELECT COUNT(*) FROM ' . AVISOS_TABLA_LOG . "
            WHERE tipo IN ('visita_tienda','visita_producto','busqueda','busqueda_vacia','pagina_404','error_sitio')
              AND estado = 'enviado' AND creado_en > ?");
        $st->execute([$desde]);
        if ((int)$st->fetchColumn() >= AVISOS_TOPE_HORA_TOTAL) return true;

        $st = db()->prepare('SELECT COUNT(*) FROM ' . AVISOS_TABLA_LOG . "
            WHERE tipo = ? AND estado = 'enviado' AND creado_en > ?");
        $st->execute([$tipo, $desde]);
        return (int)$st->fetchColumn() >= $limite;
    } catch (Throwable $e) {
        return false;
    }
}

function avisos_en_silencio(): bool {
    if (!AVISOS_SILENCIO_ACTIVO) return false;
    $h = (int)date('G');
    $d = (int)AVISOS_SILENCIO_DESDE;
    $a = (int)AVISOS_SILENCIO_HASTA;
    return $d <= $a ? ($h >= $d && $h < $a) : ($h >= $d || $h < $a);
}

/**
 * LAS SEÑALES DE PERSONA (rehecho el 2026-09-15 — queja del jefe: *«los mensajes de Telegram
 * siempre dicen que son robots, nunca dice que son personas»*).
 *
 * Antes solo había TRES señales (sesión, venir de Google/redes, o que esa IP ya hubiera pasado
 * por el sitio) y la más común —una persona que escribe la dirección, o llega de un enlace que
 * no manda referencia— se caía del lado de los robots. Por eso el jefe veía «robots» a todas
 * horas y ni una persona. Ahora se miran también las señales que deja el propio módulo de
 * estadísticas, que son las más difíciles de falsificar por una granja de IPs:
 *
 *   · 🧠 EL DISPOSITIVO YA CONOCIDO (`cookie cz_stats` del visitante): si su cookie tiene
 *     historial en `directorio_stats_sesiones` (ya entró otro día) o si en esta misma visita
 *     abrió más de una página, o si mandó latidos de tiempo (eso solo lo hace un navegador con
 *     JavaScript, y la granja no ejecuta JS: 28 de 2 350 sesiones del 14/09 tenían tiempo real),
 *     entonces ES una persona. Y da igual de dónde venga: no hace falta referer.
 *   · 🔁 LA MISMA IP YA HABÍA PASADO HOY por el sitio.
 *   · 🔑 TIENE SESIÓN iniciada.
 *   · 🌐 LLEGA DE Google/Bing/Instagram/Facebook/WhatsApp/TikTok (llegada intencionada).
 *
 * Devuelve la lista de señales encontradas (vacía = no hay ninguna prueba de persona).
 * Todo va en try/catch: si el módulo de estadísticas no está, esto NUNCA rompe la web.
 */
function avisos_senales_persona(array $d): array {
    $s = [];

    if (!empty($d['usuario_id'])) $s[] = 'tiene sesión iniciada';

    $origen = (string)($d['origen'] ?? '');
    if (preg_match('/desde (Google|Bing|Instagram|Facebook|WhatsApp|TikTok)/i', $origen)) {
        $s[] = 'llegó ' . $origen;
    }

    $ip = (string)($d['ip'] ?? '');
    if ($ip !== '' && avisos_tablas_ok()) {
        try {
            $st = db()->prepare('SELECT COUNT(*) FROM ' . AVISOS_TABLA_LOG . ' WHERE ip = ? AND creado_en > ?');
            $st->execute([$ip, avisos_desde(1440)]);
            if ((int)$st->fetchColumn() >= 1) $s[] = 'esa IP ya había entrado hoy';
        } catch (Throwable $e) { /* sin registro: se decide con lo demás */ }
    }

    // 🧠 La huella del navegador (lo más fuerte que hay aquí).
    $ck = (string)($d['cookie'] ?? '');
    if ($ck !== '' && preg_match('/^[a-f0-9]{32}$/', $ck)) {
        try {
            $st = db()->prepare('SELECT COUNT(*) sesiones, MAX(paginas) paginas, SUM(segundos) segundos,
                                        MAX(inicio) ultima
                                   FROM directorio_stats_sesiones WHERE cookie = ?');
            $st->execute([$ck]);
            $r = $st->fetch(PDO::FETCH_ASSOC) ?: [];
            $sesiones = (int)($r['sesiones'] ?? 0);
            $paginas  = (int)($r['paginas'] ?? 0);
            $segundos = (int)($r['segundos'] ?? 0);
            $ultima   = (string)($r['ultima'] ?? '');

            if ($sesiones > 1) {
                $s[] = 'es un dispositivo que ya había entrado antes';
            } elseif ($paginas >= 2) {
                $s[] = 'abrió más de una página en esta visita';
            } elseif ($segundos > 0) {
                $s[] = 'estuvo leyendo la ficha (' . $segundos . ' s)';
            } elseif ($sesiones === 1 && $ultima !== '' && $ultima < date('Y-m-d H:i:s', time() - 1800)) {
                $s[] = 'ya había pasado por el sitio hoy';
            }
        } catch (Throwable $e) { /* sin tabla de estadísticas: se decide con lo demás */ }
    }

    return $s;
}

/**
 * ¿Esta visita a una tienda/producto es de una PERSONA de verdad? (sí/no)
 *
 * Es la versión CORTA de `avisos_senales_persona()`: el motor de avisos (`aviso()`) usa la
 * versión larga porque además necesita saber POR QUÉ (para escribir «✅ Persona real: …» en el
 * mensaje), pero esta queda como la pregunta directa para cualquier otro módulo.
 *
 * En este hosting hay una granja de robots que entra a una ficha distinta con una IP distinta
 * cada ~25 segundos, con modelos de móvil falsos ("Pixel 8 Pro", "iPhone 18_2"), así que avisar
 * de "todas" las visitas serían ~500 mensajes al día de puro ruido (medido: 2 914 visitas
 * marcadas como automáticas en 7 días, con 2 509 IPs distintas).
 * Con AVISOS_VISITAS_MODO = 'solo_humanas' solo se avisa si hay ALGUNA señal de persona.
 */
function avisos_visita_confiable(array $d): bool {
    if (AVISOS_VISITAS_MODO !== 'solo_humanas') return true;
    return avisos_senales_persona($d) !== [];
}

/**
 * 👤 Cuántas visitas a tienda del registro SON de personas (y cuántas tiendas distintas).
 *
 * ⚠️ Cuenta TODO estado menos 'robot': el filtro de persona escribe 'robot' cuando dice que NO
 * es persona, así que cualquier otro estado ('enviado', 'duplicado', 'agrupado' —el tope por
 * hora—, 'silencio' o 'apagado') significa que SÍ pasó el filtro. Contar solo 'enviado' dejaba
 * fuera las visitas de personas que no generaron mensaje (p. ej. la misma IP entrando dos veces
 * a la misma ficha en 12 h), y el informe del jefe salía más pobre de lo que es la realidad.
 */
function avisos_personas(int $minutos = 60): array {
    if (!avisos_tablas_ok()) return ['n' => 0, 'tiendas' => 0, 'ips' => 0];
    try {
        $st = db()->prepare('SELECT COUNT(*) n, COUNT(DISTINCT negocio_id) tiendas, COUNT(DISTINCT ip) ips
                               FROM ' . AVISOS_TABLA_LOG . "
                              WHERE tipo IN ('visita_tienda','visita_producto')
                                AND estado <> 'robot' AND creado_en > ?");
        $st->execute([avisos_desde($minutos)]);
        $r = $st->fetch(PDO::FETCH_ASSOC) ?: [];
        return ['n' => (int)($r['n'] ?? 0), 'tiendas' => (int)($r['tiendas'] ?? 0), 'ips' => (int)($r['ips'] ?? 0)];
    } catch (Throwable $e) {
        return ['n' => 0, 'tiendas' => 0, 'ips' => 0];
    }
}

// ============================================================
// EL CORAZÓN: aviso()
// ============================================================
function aviso(string $tipo, array $datos = []): bool {
    try {
        if (!isset(avisos_catalogo()[$tipo])) return false;

        $ctx = avisos_contexto();
        $datos = array_merge($ctx, $datos);

        // Los robots solo entran en su propio resumen (salvo que se pida lo contrario)
        $es_robot = !empty($datos['es_bot']) && empty($datos['usuario_id']);
        if ($es_robot && !AVISOS_INCLUIR_ROBOTS_SUELTOS && $tipo !== 'visita_robot') {
            avisos_log($tipo, 'robot', $datos);
            return false;
        }

        // Visitas: si no hay señales de persona de verdad, se cuentan como robots y no se avisa
        $es_visita = in_array($tipo, ['visita_tienda', 'visita_producto'], true);
        if ($es_visita) {
            $senales = avisos_senales_persona($datos);
            if (!$senales && AVISOS_VISITAS_MODO === 'solo_humanas') {
                avisos_log($tipo, 'robot', array_merge($datos, [
                    'es_bot'  => 1,
                    'bot'     => 'Visita automática (sin señales de persona)',
                    'resumen' => trim('visita automática ' . (string)($datos['resumen'] ?? '')),
                ]));
                return false;
            }
            // Se guarda POR QUÉ se consideró persona (se ve en Súper Admin → 📱 Telegram).
            $datos['senales_persona'] = $senales;
            if ($senales) {
                $datos['resumen'] = trim((string)($datos['resumen'] ?? '')
                    . ' · persona porque ' . implode(', ', array_slice($senales, 0, 2)));
            }
        }

        // ¿Apagado desde el panel?
        if (!aviso_activo($tipo)) {
            avisos_log($tipo, 'apagado', $datos);
            return false;
        }

        // Dedupe
        $clave     = (string)($datos['clave'] ?? '');
        $dedupe    = (int)($datos['dedupe_min'] ?? 0);
        if ($dedupe > 0 && avisos_duplicado($clave, $dedupe)) {
            avisos_log($tipo, 'duplicado', $datos);
            return false;
        }

        // Horario de silencio
        if (avisos_en_silencio() && empty($datos['ignorar_silencio'])) {
            avisos_log($tipo, 'silencio', $datos);
            return false;
        }

        // Tope por hora
        if (avisos_tope_alcanzado($tipo)) {
            avisos_log($tipo, 'agrupado', $datos);
            return false;
        }

        // Formato + envío
        $texto = avisos_formato($tipo, $datos);
        if ($texto === '') return false;

        avisos_encolar($texto);
        avisos_log($tipo, 'enviado', $datos);
        return true;
    } catch (Throwable $e) {
        error_log('aviso(' . $tipo . '): ' . $e->getMessage());
        return false;
    }
}

// ============================================================
// FORMATOS DE LOS MENSAJES
// ============================================================
function avisos_linea_quien(array $d): string {
    $partes = [$d['quien'] ?? 'Anónimo'];
    if (!empty($d['dispositivo'])) $partes[] = $d['dispositivo'];
    $linea = '👤 ' . implode(' · ', $partes);
    // 👤 Por qué se sabe que es una persona (ver avisos_senales_persona): que el jefe lo lea.
    if (!empty($d['senales_persona']) && is_array($d['senales_persona'])) {
        $linea .= "\n✅ Persona real: " . implode(' · ', array_slice($d['senales_persona'], 0, 3));
    }
    return $linea;
}

function avisos_negocio_txt($negocio_id): array {
    static $cache = [];
    $id = (int)$negocio_id;
    if (isset($cache[$id])) return $cache[$id];
    try {
        $st = db()->prepare('SELECT nombre, slug FROM directorio_negocios WHERE id = ? LIMIT 1');
        $st->execute([$id]);
        $r = $st->fetch() ?: [];
    } catch (Throwable $e) {
        $r = [];
    }
    return $cache[$id] = [(string)($r['nombre'] ?? ('Negocio #' . $id)), (string)($r['slug'] ?? '')];
}

function avisos_producto_txt($producto_id): array {
    if (!$producto_id) return ['', ''];
    try {
        $st = db()->prepare('SELECT titulo, precio FROM directorio_servicios WHERE id = ? LIMIT 1');
        $st->execute([(int)$producto_id]);
        $r = $st->fetch() ?: [];
        return [(string)($r['titulo'] ?? ''), isset($r['precio']) ? (float)$r['precio'] : 0.0];
    } catch (Throwable $e) {
        return ['', 0.0];
    }
}

function avisos_banner_txt($banner_id): string {
    try {
        $st = db()->prepare('SELECT titulo FROM directorio_banners WHERE id = ? LIMIT 1');
        $st->execute([(int)$banner_id]);
        return (string)($st->fetchColumn() ?: '');
    } catch (Throwable $e) {
        return '';
    }
}

function avisos_formato(string $tipo, array $d): string {
    $L = [];
    switch ($tipo) {

        case 'noticias_dia':
            // 📰 El robot de noticias publica solo cada mañana (order del jefe: automático + aviso).
            $n = (int)($d['n'] ?? 0);
            $L[] = '📰 NOTICIAS DEL DÍA PUBLICADAS';
            $L[] = '';
            $L[] = '✅ ' . $n . ' noticia(s) local(es) nueva(s) en la web';
            if (!empty($d['distritos']) && is_array($d['distritos'])) {
                $partes = [];
                foreach ($d['distritos'] as $k => $v) $partes[] = $k . ' (' . (int)$v . ')';
                if ($partes) $L[] = '📍 ' . implode(' · ', $partes);
            }
            foreach (array_slice((array)($d['titulos'] ?? []), 0, 6) as $i => $t) {
                $L[] = ($i + 1) . '. ' . $t;
            }
            if (!empty($d['prueba'])) $L[] = '🧪 (aviso de PRUEBA: no se publicó nada)';
            if (!empty($d['url'])) $L[] = '🔗 ' . $d['url'];
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'busqueda':
            $L[] = '🔍 BÚSQUEDA NUEVA';
            $L[] = '';
            $L[] = '"' . ($d['termino'] ?? '') . '"';
            $L[] = avisos_linea_quien($d);
            $L[] = '🌐 ' . ($d['ip'] ?? '') . ' · ' . ($d['origen'] ?? '');
            $L[] = '📄 ' . (int)($d['resultados'] ?? 0) . ' resultado(s)';
            if (!empty($d['filtros'])) $L[] = '🎛️ Filtros: ' . $d['filtros'];
            $L[] = '🔗 ' . ($d['url'] ?? '');
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'busqueda_vacia':
            $L[] = '🕳️ BÚSQUEDA SIN RESULTADOS';
            $L[] = '';
            $L[] = '"' . ($d['termino'] ?? '') . '"';
            $L[] = avisos_linea_quien($d) . ' · 🌐 ' . ($d['ip'] ?? '');
            $L[] = '💡 Nadie ofrece eso en el directorio: oportunidad para captar ese negocio.';
            $L[] = '🔗 ' . ($d['url'] ?? '');
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        // 🛒 ENCARGO SIN VENDEDOR (módulo del 2026-09-15, pedido del jefe). El aviso de siempre
        // (busqueda_vacia) le decía al jefe «nadie ofrece eso»; este cuenta lo que pasa DESPUÉS:
        // el encargo ya está publicado en /en-vivo, con quién lo busca y quién ofrece. Los dos
        // enlaces de moderación funcionan con UN toque desde el Telegram, como los empleos.
        case 'pedido_sin_vendedor':
            $motivo = (string)($d['motivo'] ?? 'nuevo');
            $L[] = [
                'nuevo'     => '🛒 NADIE LO VENDE: encargo publicado',
                'crecido'   => '🔥 ESE ENCARGO YA LO BUSCAN VARIOS',
                'propuesta' => '✅ ¡ALGUIEN OFRECIÓ LO QUE PEDÍAN!',
                'comprador' => '📲 UN COMPRADOR DEJÓ SU WHATSAPP',
            ][$motivo] ?? '🛒 ENCARGO SIN VENDEDOR';
            $L[] = '';
            $L[] = '🔎 «' . ($d['termino'] ?? '') . '»';
            if (!empty($d['distrito'])) $L[] = '📍 ' . $d['distrito'];
            $L[] = '👥 Lo buscaron ' . (int)($d['buscado_n'] ?? 1) . ' vez(ces) · 🏪 ' . (int)($d['propuestas'] ?? 0) . ' oferta(s)';
            if ($motivo === 'propuesta') {
                $L[] = '';
                $L[] = '🏪 Ofrece: ' . ($d['vendedor'] ?? '');
                if (!empty($d['whatsapp'])) $L[] = '📞 ' . $d['whatsapp'];
                if (!empty($d['mensaje']))  $L[] = '💬 ' . mb_substr((string)$d['mensaje'], 0, 160);
            }
            if ($motivo === 'comprador') {
                $L[] = '👉 Dejó su número para que le escriban (el número NO se publica: solo lo ve el vendedor que ofrece algo).';
            }
            if (!empty($d['url'])) $L[] = '🔗 ' . $d['url'];
            $L[] = '';
            $L[] = '🗑️ QUITARLO:        ' . ($d['ocultar'] ?? '');
            $L[] = '✅ YA LO CONSEGUÍ:  ' . ($d['conseguir'] ?? '');
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'visita_tienda':
            [$nombre, $slug] = avisos_negocio_txt($d['negocio_id'] ?? 0);
            $L[] = '👁️ VISITA A UNA TIENDA';
            $L[] = '';
            $L[] = '🏪 ' . $nombre;
            $L[] = avisos_linea_quien($d);
            $L[] = '🌐 ' . ($d['ip'] ?? '') . ' · ' . ($d['origen'] ?? '');
            if ($slug !== '') $L[] = '🔗 ' . url_negocio($slug);
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'visita_producto':
            [$nombre, $slug] = avisos_negocio_txt($d['negocio_id'] ?? 0);
            [$titulo, $precio] = avisos_producto_txt($d['producto_id'] ?? 0);
            $L[] = '📦 VISITA A UN PRODUCTO';
            $L[] = '';
            $L[] = '📦 ' . ($titulo !== '' ? '"' . $titulo . '"' : ('Producto #' . (int)($d['producto_id'] ?? 0)))
                 . ($precio > 0 ? ' · ' . formato_precio($precio) : '');
            $L[] = '🏪 ' . $nombre;
            $L[] = avisos_linea_quien($d) . ' · 🌐 ' . ($d['ip'] ?? '');
            if ($slug !== '') $L[] = '🔗 ' . url_negocio($slug);
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'lead_precio':
            [$nombre, $slug] = avisos_negocio_txt($d['negocio_id'] ?? 0);
            [$titulo, $precio] = avisos_producto_txt($d['producto_id'] ?? 0);
            $L[] = '🔥 PIDIERON PRECIO POR WHATSAPP';
            $L[] = '';
            if ($titulo !== '') $L[] = '📦 "' . $titulo . '"' . ($precio > 0 ? ' · ' . formato_precio($precio) : '');
            $L[] = '🏪 ' . $nombre;
            if (!empty($d['telefono_negocio'])) $L[] = '📞 ' . $d['telefono_negocio'];
            // 🛒 El pedido vino del carrito "Me interesa" (varios productos en un solo mensaje)
            if (!empty($d['carrito_txt'])) {
                $L[] = '🛒 Carrito «Me interesa»: ' . mb_substr(strip_tags((string)$d['carrito_txt']), 0, 180);
            }
            $L[] = avisos_linea_quien($d);
            $L[] = '🌐 ' . ($d['ip'] ?? '') . ' · ' . ($d['origen'] ?? '');
            $L[] = '';
            $L[] = '💡 Lead caliente: te lo acaban de pedir.';
            if ($slug !== '') $L[] = '🔗 ' . url_negocio($slug);
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        // 📞 TOCARON «LLAMAR» (2026-09-15): el botón 📞 Llamar de la ficha no se medía de
        // ninguna forma (era un <a href="tel:"> puro). Ahora el navegador avisa con un beacon
        // (assets/js/llamadas.js → api/llamada.php) y aquí sale el aviso, igual de caliente
        // que el de WhatsApp: el visitante acaba de marcar el número de la tienda.
        case 'llamada_tienda':
            [$nombre, $slug] = avisos_negocio_txt($d['negocio_id'] ?? 0);
            $L[] = '📞 TOCARON «LLAMAR» EN UNA FICHA';
            $L[] = '';
            $L[] = '🏪 ' . $nombre;
            if (!empty($d['telefono_negocio'])) $L[] = '📞 ' . $d['telefono_negocio'];
            if (!empty($d['producto_id'])) {
                [$titulo] = avisos_producto_txt($d['producto_id']);
                if ($titulo !== '') $L[] = '📦 Desde el producto "' . $titulo . '"';
            }
            $L[] = avisos_linea_quien($d);
            $L[] = '🌐 ' . ($d['ip'] ?? '') . ' · ' . ($d['origen'] ?? '');
            $L[] = '';
            $L[] = '💡 Lead caliente: van a llamar (o acaban de llamar) a la tienda.';
            if ($slug !== '') $L[] = '🔗 ' . url_negocio($slug);
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        // 💼 EMPLEOS (2026-09-12): si el aviso nace PENDIENTE (lo mandó un
        // visitante), el mensaje lleva los dos enlaces de moderación: el jefe aprueba o
        // rechaza con UN toque desde el Telegram, sin entrar a ningún panel.
        case 'empleo':
            $td     = function_exists('empleo_tipo_datos') ? empleo_tipo_datos($d['tipo'] ?? 'ofrezco') : ['etiqueta' => 'Aviso', 'icono' => '💼'];
            $estado = (string)($d['estado'] ?? 'activo');
            $L[] = ($estado === 'pendiente')
                ? '🆕 AVISO DE EMPLEO POR APROBAR'
                : ($td['icono'] . ' ' . mb_strtoupper($td['etiqueta']) . ' EN EMPLEOS');
            $L[] = '';
            $L[] = '💼 ' . ($d['titulo'] ?? '');
            if (!empty($d['entidad']))  $L[] = '🏢 ' . $d['entidad'];
            if (!empty($d['oficio']))   $L[] = '🔧 ' . $d['oficio'];
            if (!empty($d['zona']))     $L[] = '📍 ' . $d['zona'];
            if (!empty($d['telefono'])) $L[] = '📞 ' . $d['telefono'];
            if (!empty($d['sueldo']))   $L[] = '💰 ' . $d['sueldo'];
            $res = trim(preg_replace('/\s+/u', ' ', (string)($d['resumen'] ?? '')));
            if ($res !== '') $L[] = '📝 ' . mb_substr($res, 0, 180);
            $modo = (string)($d['modo'] ?? 'manual');
            $L[] = '👤 Lo publicó: ' . ($modo === 'dueno' ? 'el dueño de una tienda'
                                    : ($modo === 'visitante' ? 'un visitante (sin cuenta)' : 'el administrador'));
            if (!empty($d['url'])) $L[] = '🔗 ' . $d['url'];
            if (!empty($d['aprobar'])) {
                $L[] = '';
                $L[] = '✅ APROBAR:  ' . $d['aprobar'];
                $L[] = '🗑️ RECHAZAR: ' . $d['rechazar'];
                $L[] = '⏳ Mientras no lo apruebes, el aviso NO se ve en el sitio.';
            }
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'clave_olvidada':
            // 🔑 PIDE AYUDA PORQUE NO PUEDE ENTRAR (2026-09-16). El aviso se resuelve en el panel:
            // el jefe toca «🔑 Restablecer contraseña» y le manda la clave nueva por WhatsApp.
            $L[] = '🔑 OLVIDÓ SU CONTRASEÑA';
            $L[] = '';
            if (!empty($d['encontrado'])) {
                $L[] = '👤 ' . ($d['nombre'] ?? '');
                if (!empty($d['usuario'])) $L[] = '📱 Su usuario: ' . $d['usuario'];
                if (!empty($d['tienda']))  $L[] = '🏪 ' . $d['tienda'];
                $L[] = '';
                $L[] = '👉 Dale una clave nueva: ' . ($d['panel'] ?? '');
                $L[] = '   (👥 Usuarios → 🔑 Restablecer contraseña: se la mandas por WhatsApp con un toque.)';
            } else {
                $L[] = '⚠️ Con ese dato NO encontré ninguna cuenta:';
                $L[] = '📱 ' . ($d['usuario'] ?? '');
                $L[] = '   Puede que haya escrito mal su número. Búscalo en 👥 Usuarios: ' . ($d['panel'] ?? '');
            }
            $L[] = '🌐 ' . ($d['ip'] ?? '');
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'usuario_nuevo':
            $L[] = '👤 USUARIO NUEVO';
            $L[] = '';
            $L[] = '👤 ' . ($d['nombre'] ?? '');
            if (!empty($d['email']))    $L[] = '📧 ' . $d['email'];
            if (!empty($d['telefono'])) $L[] = '📞 ' . $d['telefono'];
            $L[] = '🔑 ' . ($d['via'] ?? 'con correo');
            if (isset($d['total'])) $L[] = '👥 Ya son ' . (int)$d['total'] . ' usuarios registrados';
            $L[] = '🌐 ' . ($d['ip'] ?? '');
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        // 💬 OPINIÓN NUEVA (2026-09-15): el módulo de opiniones anónimas se estrenó el
        // 2026-09-14 y NO avisaba al Telegram: el jefe se enteraba solo si entraba al panel.
        case 'opinion_nueva':
            [$neg_o, $slug_o] = avisos_negocio_txt($d['negocio_id'] ?? 0);
            $est = (int)($d['rating'] ?? 0);
            $L[] = '💬 OPINIÓN NUEVA EN UNA FICHA';
            $L[] = '';
            $L[] = '🏪 ' . $neg_o;
            $L[] = '👤 ' . ($d['autor'] ?? 'Anónimo') . ($est > 0 ? ' · ' . str_repeat('⭐', max(1, min(5, $est))) : '');
            $txt = trim(preg_replace('/\s+/u', ' ', (string)($d['texto'] ?? '')));
            if ($txt !== '') { $L[] = ''; $L[] = '📝 «' . mb_substr($txt, 0, 240) . '»'; }
            $L[] = '';
            if ($est > 0 && $est <= 3) {
                $L[] = '⚠️ Puntuación baja: si es un ataque o un falso, se decide en Súper Admin → 🚩 Reclamos de opiniones.';
            } else {
                $L[] = '👍 Se publicó al instante (las opiniones son anónimas).';
            }
            if ($slug_o !== '') $L[] = '🔗 ' . url_negocio($slug_o);
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'tienda_nueva':
            $L[] = '🏪 TIENDA NUEVA CREADA';
            $L[] = '';
            $L[] = '🏪 ' . ($d['nombre'] ?? '');
            if (!empty($d['rubro']))    $L[] = '📂 ' . $d['rubro'];
            if (!empty($d['distrito'])) $L[] = '📍 ' . $d['distrito'];
            $L[] = avisos_linea_quien($d);
            if (!empty($d['estado'])) $L[] = '⏳ Estado: ' . $d['estado'];
            if (!empty($d['slug']))   $L[] = '🔗 ' . url_negocio($d['slug']);
            $L[] = '🛠️ Revisar: ' . url('superadmin.php?seccion=tiendas');
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'reclamo':
            [$nombre] = avisos_negocio_txt($d['negocio_id'] ?? 0);
            $L[] = '🙋 RECLAMO DE NEGOCIO';
            $L[] = '';
            $L[] = '🏪 ' . $nombre;
            $L[] = '👤 ' . ($d['nombre_reclama'] ?? '');
            if (!empty($d['email']))    $L[] = '📧 ' . $d['email'];
            if (!empty($d['telefono'])) $L[] = '📞 ' . $d['telefono'];
            if (!empty($d['explicacion'])) { $L[] = ''; $L[] = '📝 ' . $d['explicacion']; }
            $L[] = '🛠️ Revisar: ' . url('superadmin.php?seccion=reclamos');
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        // 🚩 REPORTE DE CONTENIDO (2026-09-15): hoy lo mandan `api/reportar.php` y
        // `includes/opiniones.php` con `notificar_jefe()` y texto propio (por eso el interruptor
        // `reporte_contenido` funcionaba), pero el tipo está en el catálogo y SIN este `case`
        // cualquier módulo que lo mandara por `aviso()` se perdía en silencio.
        case 'reporte_contenido':
            [$neg_rc, $slug_rc] = avisos_negocio_txt($d['negocio_id'] ?? 0);
            $L[] = '🚩 REPORTE DE CONTENIDO';
            $L[] = '';
            if ($neg_rc !== '') $L[] = '🏪 ' . $neg_rc;
            if (!empty($d['motivo']))      $L[] = '❗ Motivo: ' . $d['motivo'];
            if (!empty($d['descripcion'])) $L[] = '📝 ' . mb_substr((string)$d['descripcion'], 0, 220);
            if (!empty($d['producto']))    $L[] = '📦 Producto: ' . $d['producto'];
            $L[] = '👤 Lo reportó: ' . ($d['quien'] ?? 'Anónimo');
            $L[] = '';
            $L[] = '🛠️ Decidir en Súper Admin → 🚩 Reportes (o Reclamos de opiniones).';
            if ($slug_rc !== '') $L[] = '🔗 ' . url_negocio($slug_rc);
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'postulante':
            $L[] = '💼 POSTULACIÓN NUEVA';
            $L[] = '';
            $L[] = '👤 ' . ($d['nombre'] ?? '');
            if (!empty($d['email']))    $L[] = '📧 ' . $d['email'];
            if (!empty($d['telefono'])) $L[] = '📞 ' . $d['telefono'];
            if (!empty($d['sueldo']))   $L[] = '💰 Sueldo esperado: ' . $d['sueldo'];
            if (!empty($d['jornada']))  $L[] = '🕒 Jornada: ' . $d['jornada'];
            $L[] = '🛠️ Revisar: ' . url('superadmin.php?seccion=postulantes');
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'pagina_404':
            $L[] = '🔗 ENLACE ROTO (404)';
            $L[] = '';
            $L[] = '🚫 ' . ($d['ruta'] ?? '');
            $L[] = '↩️ ' . ($d['origen'] ?? '');
            $L[] = '🌐 ' . ($d['ip'] ?? '') . ' · ' . ($d['dispositivo'] ?? '');
            if (!empty($d['es_bot'])) $L[] = '🤖 robot: ' . $d['bot'];
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'error_sitio':
            $L[] = '💥 ERROR EN EL SITIO';
            $L[] = '';
            $L[] = '💥 ' . ($d['mensaje'] ?? '');
            if (!empty($d['archivo'])) $L[] = '📄 ' . $d['archivo'] . (isset($d['linea']) ? ':' . $d['linea'] : '');
            if (!empty($d['ruta']))    $L[] = '🌐 ' . $d['ruta'];
            $L[] = '';
            $L[] = '🛠️ Este aviso se manda una sola vez por error distinto.';
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'producto_nuevo':
            [$neg_p, $slug_p] = avisos_negocio_txt($d['negocio_id'] ?? 0);
            $L[] = '📦 PRODUCTO NUEVO O EDITADO';
            $L[] = '';
            $L[] = '📦 "' . ($d['titulo'] ?? '') . '"' . (!empty($d['precio']) ? ' · ' . formato_precio((float)$d['precio']) : '');
            $L[] = '🏪 ' . $neg_p;
            $L[] = '✏️ ' . ($d['accion'] ?? 'creado')
                 . (isset($d['activo']) ? ($d['activo'] ? ' · visible en la web' : ' · guardado como oculto') : '');
            if (!empty($d['fotos'])) $L[] = '📸 Con ' . (int)$d['fotos'] . ' foto(s).';
            $L[] = avisos_linea_quien($d);
            if ($slug_p !== '') $L[] = '🔗 ' . url_negocio($slug_p);
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'producto_borrado':
            [$neg_b, $slug_b] = avisos_negocio_txt($d['negocio_id'] ?? 0);
            $L[] = '🗑️ PRODUCTO ELIMINADO';
            $L[] = '';
            $L[] = '📦 "' . ($d['titulo'] ?? ('Producto #' . (int)($d['producto_id'] ?? 0))) . '"';
            $L[] = '🏪 ' . $neg_b;
            $L[] = avisos_linea_quien($d);
            if ($slug_b !== '') $L[] = '🔗 ' . url_negocio($slug_b);
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'foto_nueva':
            [$neg_f, $slug_f] = avisos_negocio_txt($d['negocio_id'] ?? 0);
            $L[] = '📷 FOTOS NUEVAS EN UNA FICHA';
            $L[] = '';
            $L[] = '🏪 ' . $neg_f;
            $L[] = '📸 ' . (int)($d['cantidad'] ?? 1) . ' foto(s)'
                 . (!empty($d['producto']) ? ' del producto "' . $d['producto'] . '"' : ' de la ficha');
            $L[] = avisos_linea_quien($d);
            if ($slug_f !== '') $L[] = '🔗 ' . url_negocio($slug_f);
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'caminante_incompleto':
            $L[] = '📵 CAMINANTE: SUBIDA INCOMPLETA';
            $L[] = '';
            if (($d['nombre'] ?? '') !== '') $L[] = '🏪 ' . $d['nombre'];
            $L[] = '📁 carpeta: ' . ($d['carpeta'] ?? '?');
            $L[] = '📷 El celular envió ' . (int)($d['enviadas'] ?? 0) . ' foto(s) · guardadas: '
                 . (int)($d['ok'] ?? 0) . ' · rechazadas: ' . (int)($d['rechazadas'] ?? 0);
            if (($d['ubicacion'] ?? '') !== '') $L[] = '📍 ' . $d['ubicacion'];
            if (($d['detalle'] ?? '') !== '') $L[] = '🔍 ' . $d['detalle'];
            $L[] = '⚠️ ' . ($d['problema'] ?? 'Revisar la subida.');
            $L[] = avisos_linea_quien($d) . ' · 🌐 ' . ($d['ip'] ?? '');
            $L[] = '🗂️ Registro: caminante/_registro_subidas.log';
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'banner_clic':
            $titulo_b = avisos_banner_txt($d['banner_id'] ?? 0);
            $L[] = '🖱️ CLIC EN UN BANNER';
            $L[] = '';
            $L[] = '📢 ' . ($titulo_b !== '' ? $titulo_b : ('Banner #' . (int)($d['banner_id'] ?? 0)));
            $L[] = avisos_linea_quien($d) . ' · 🌐 ' . ($d['ip'] ?? '');
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        // 🤖 EL RESUMEN DE ROBOTS, REHECHO (2026-09-15). Antes decía solo «41 visita(s) de
        // robots» y el jefe entendía que NADIE había entrado al sitio. Ahora separa los dos
        // tipos de tráfico automático y —lo importante— dice cuántas PERSONAS hubo en la
        // misma hora justo debajo, para que no quede la duda.
        case 'visita_robot':
            $personas = (int)($d['personas'] ?? 0);
            $auto     = (int)($d['automaticas'] ?? 0);
            $conocidos = (int)($d['conocidos'] ?? 0);

            $L[] = '🤖 TRÁFICO AUTOMÁTICO (última hora)';
            $L[] = '';
            $L[] = '  · ' . (int)($d['total'] ?? 0) . ' visita(s) de robots — NO son personas.';
            if (!empty($d['top'])) {
                foreach ($d['top'] as $nombre => $n) $L[] = '    • ' . $nombre . ': ' . $n;
            }
            $L[] = '';
            if ($conocidos > 0 && $auto > 0) {
                $L[] = '🔎 ' . $conocidos . ' con nombre de robot (Googlebot, Applebot, IA…): esos tratan el sitio como contenido.';
                $L[] = '👥 ' . $auto . ' automáticas sin señales de persona (la granja de IPs que raspa el sitio).';
            }
            $L[] = '';
            $L[] = $personas > 0
                ? '👤 Y en esa misma hora entraron ' . $personas . ' PERSONA(S) de verdad a las tiendas (van en el resumen de movimiento).'
                : '👤 En esa misma hora no entró ninguna persona a una tienda (la granja tapa el sitio, pero no es gente).';
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'resumen_hora':
            $L[] = '📊 MOVIMIENTO DE LA ÚLTIMA HORA';
            $L[] = '';
            foreach ((array)($d['filas'] ?? []) as $f) $L[] = $f;
            if (!empty($d['agrupados'])) {
                $L[] = '';
                $L[] = '🗂️ ' . (int)$d['agrupados'] . ' aviso(s) más se agruparon aquí para no saturar el chat.';
            }
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        case 'resumen_dia':
            $L[] = '📅 RESUMEN DEL DÍA — ' . date('d/m/Y');
            $L[] = '';
            foreach ((array)($d['filas'] ?? []) as $f) $L[] = $f;
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        // 🧠 EL INFORME INTELIGENTE (2026-09-15): las líneas las arma
        // includes/informe_inteligente.php y aquí solo se les pone la cabecera.
        case 'informe_dia':
        case 'informe_semana':
            $L[] = (string)($d['titulo'] ?? ($tipo === 'informe_semana' ? '🏆 INFORME DE LA SEMANA' : '🧠 INFORME DEL DÍA'));
            if (!empty($d['rango_txt'])) $L[] = '📅 ' . $d['rango_txt'];
            $L[] = '';
            foreach ((array)($d['filas'] ?? []) as $f) $L[] = $f;
            $L[] = '';
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;

        // 🛡️ RED DE SEGURIDAD (2026-09-15). Antes, un tipo que estuviera en el catálogo SIN su
        // `case` aquí hacía que `avisos_formato()` devolviera '' y el aviso se perdiera **en
        // silencio**: no llegaba al Telegram y tampoco quedaba registrado en el panel, así que
        // el jefe no tenía forma de saber que existía. Es exactamente el tipo de fallo que él
        // denunció («hay notificaciones que todavía no estoy recibiendo»). Con este `default`,
        // cualquier tipo que se añada al catálogo en el futuro sale al menos con su título y los
        // datos que traiga el aviso: feo, pero NUNCA perdido.
        default:
            $info_cat = avisos_catalogo()[$tipo] ?? [];
            $L[] = (string)($d['titulo'] ?? ($info_cat['titulo'] ?? mb_strtoupper($tipo)));
            $L[] = '';
            foreach ((array)($d['filas'] ?? []) as $f) $L[] = $f;
            if (!empty($d['mensaje'])) $L[] = (string)$d['mensaje'];
            if (!empty($d['resumen'])) $L[] = (string)$d['resumen'];
            if (empty($d['filas']) && empty($d['mensaje']) && empty($d['resumen'])) {
                $L[] = '⚠️ Este aviso no tiene un formato propio todavía: se muestra tal cual llegó.';
            }
            if (!empty($d['url'])) $L[] = '🔗 ' . $d['url'];
            if (!empty($d['ruta'])) $L[] = '🌐 ' . $d['ruta'];
            $L[] = '🕒 ' . ($d['hora'] ?? '');
            break;
    }
    return implode("\n", $L);
}

// ============================================================
// CONSULTAS PARA LOS RESÚMENES Y EL PANEL
// ============================================================
/** Cuenta eventos por tipo en los últimos N minutos. */
function avisos_contar(int $minutos = 1440, string $estado = 'enviado'): array {
    if (!avisos_tablas_ok()) return [];
    try {
        $st = db()->prepare('SELECT tipo, COUNT(*) n FROM ' . AVISOS_TABLA_LOG . '
            WHERE estado = ? AND creado_en > ? GROUP BY tipo');
        $st->execute([$estado, avisos_desde($minutos)]);
        $out = [];
        foreach ($st->fetchAll() as $r) $out[$r['tipo']] = (int)$r['n'];
        return $out;
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Top de tiendas visitadas en los últimos N minutos.
 * ⚠️ 2026-09-15: se limita a los tipos de VISITA. Antes entraba cualquier fila con
 * `negocio_id` (los avisos de pedir precio también lo traen) y el «🏆 Más visitadas» del
 * resumen contaba clics de WhatsApp como si fueran visitas.
 */
function avisos_top_negocios(int $minutos = 60, int $limite = 5): array {
    if (!avisos_tablas_ok()) return [];
    try {
        $st = db()->prepare('SELECT negocio_id, COUNT(*) n FROM ' . AVISOS_TABLA_LOG . "
            WHERE estado <> 'robot' AND negocio_id IS NOT NULL
              AND tipo IN ('visita_tienda','visita_producto') AND creado_en > ?
            GROUP BY negocio_id ORDER BY n DESC LIMIT ?");
        $st->bindValue(1, avisos_desde($minutos));
        $st->bindValue(2, $limite, PDO::PARAM_INT);
        $st->execute();
        return $st->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Robots contados en los últimos N minutos, SEPARADOS EN DOS (2026-09-15):
 *   · `conocidos`  = buscadores e IA con nombre (Googlebot, ClaudeBot, Applebot…): son buenos.
 *   · `automaticas`= visitas sin ninguna señal de persona (la granja de IPs): puro ruido.
 * Devuelve también `personas` (las visitas a tienda que sí eran de gente, en el mismo rato) para
 * que el mensaje pueda decir las dos cosas juntas y no dejar la duda de «¿y no entró nadie?».
 */
function avisos_robots(int $minutos = 60): array {
    $vacio = ['total' => 0, 'top' => [], 'conocidos' => 0, 'automaticas' => 0, 'personas' => 0];
    if (!avisos_tablas_ok()) return $vacio;
    try {
        $st = db()->prepare('SELECT bot, COUNT(*) n FROM ' . AVISOS_TABLA_LOG . '
            WHERE es_bot = 1 AND creado_en > ? GROUP BY bot ORDER BY n DESC LIMIT 8');
        $st->bindValue(1, avisos_desde($minutos));
        $st->execute();
        $filas = $st->fetchAll();
        $top = [];
        $total = 0;
        $auto = 0;
        $conocidos = 0;
        foreach ($filas as $f) {
            $bruto = (string)($f['bot'] ?: 'Robot');
            // El nombre de la granja se guarda truncado (la columna es VARCHAR(40)): se le pone
            // un nombre corto y legible para el mensaje del jefe.
            $nombre = (strpos($bruto, 'Visita automática') === 0) ? 'Automáticas (granja de IPs)' : $bruto;
            $top[$nombre] = (int)$f['n'];
            $total += (int)$f['n'];
            if (strpos($bruto, 'Visita automática') === 0) $auto += (int)$f['n'];
            else $conocidos += (int)$f['n'];
        }
        // ⚠️ El total se cuenta APARTE y no sumando los grupos: la lista de arriba es un TOP con
        // LIMIT 8, así que si algún día hay más de 8 robots distintos el total saldría corto
        // (encontrado el 2026-09-15 al revisar el módulo).
        $total = 0;
        try {
            $st2 = db()->prepare('SELECT COUNT(*) FROM ' . AVISOS_TABLA_LOG . '
                WHERE es_bot = 1 AND creado_en > ?');
            $st2->bindValue(1, avisos_desde($minutos));
            $st2->execute();
            $total = (int)$st2->fetchColumn();
        } catch (Throwable $e) {
            $total = array_sum($top);
        }

        $per = avisos_personas($minutos);
        return [
            'total' => $total, 'top' => $top,
            'conocidos' => $conocidos, 'automaticas' => $auto,
            'personas' => (int)$per['n'],
        ];
    } catch (Throwable $e) {
        return $vacio;
    }
}

/**
 * 📞 Los clics de PEDIR/LLAMAR de los últimos N minutos (cruzando `directorio_pedidos`).
 * Devuelve ['n' => total, 'llamadas' =>…, 'clics' =>…, 'carritos' =>…, 'tops' => [[nombre, n], …]].
 */
function avisos_pedidos(int $minutos = 60, int $top_limite = 3): array {
    $vacio = ['n' => 0, 'llamadas' => 0, 'clics' => 0, 'carritos' => 0, 'consultas' => 0, 'tops' => []];
    try {
        db()->query('SELECT 1 FROM directorio_pedidos LIMIT 1');
    } catch (Throwable $e) {
        return $vacio;   // la tabla de récords todavía no se ha creado
    }
    try {
        $desde = avisos_desde($minutos);
        $st = db()->prepare("SELECT COUNT(*) n, SUM(tipo = 'llamada') llamadas, SUM(tipo = 'clic') clics,
                                    SUM(tipo = 'pedido') carritos, SUM(tipo = 'consulta') consultas
                               FROM directorio_pedidos WHERE fecha > ?");
        $st->execute([$desde]);
        $r = $st->fetch(PDO::FETCH_ASSOC) ?: [];

        $tops = [];
        $st = db()->prepare('SELECT negocio_id, COUNT(*) n FROM directorio_pedidos
                              WHERE fecha > ? GROUP BY negocio_id ORDER BY n DESC LIMIT ' . max(1, (int)$top_limite));
        $st->execute([$desde]);
        foreach ($st->fetchAll() as $f) {
            [$nombre] = avisos_negocio_txt($f['negocio_id']);
            $tops[] = [$nombre, (int)$f['n']];
        }

        return [
            'n'         => (int)($r['n'] ?? 0),
            'llamadas'  => (int)($r['llamadas'] ?? 0),
            'clics'     => (int)($r['clics'] ?? 0),
            'carritos'  => (int)($r['carritos'] ?? 0),
            'consultas' => (int)($r['consultas'] ?? 0),
            'tops'      => $tops,
        ];
    } catch (Throwable $e) {
        return $vacio;
    }
}

/**
 * Resumen de la última hora: devuelve el texto o '' si no hay nada que contar.
 *
 * 🧠 REHECHO EL 2026-09-15 (queja del jefe: *«necesito datos más inteligentes»*). Antes solo
 * contaba avisos sueltos («Visitas a tiendas: 2») y el jefe no sabía si el sitio se movía.
 * Ahora CRUZA las tablas y abre con lo que de verdad importa:
 *   1) 👤 PERSONAS que entraron (visitas a tienda con señales de persona) y a qué tiendas.
 *   2) 📞/💬 PEDIDOS: clics de llamar y de WhatsApp (de `directorio_pedidos`).
 *   3) 🔍 BÚSQUEDAS de personas, con las palabras que escribieron (`directorio_busquedas`).
 *   4) 🚩 REPORTES y opiniones nuevas (lo que espera su decisión).
 *   5) 🤖 El tráfico automático, al final y en una línea.
 */
function avisos_resumen_hora(int $minutos = 60, bool $forzar = false): string {
    $hechos   = avisos_contar($minutos, 'enviado');
    $agrupado = avisos_contar($minutos, 'agrupado');
    $robots   = avisos_robots($minutos);
    $personas = avisos_personas($minutos);
    $pedidos  = avisos_pedidos($minutos, 3);

    $filas = [];

    // ---------- 1) 👤 LAS PERSONAS (primero, es lo que el jefe quiere saber) ----------
    if ($personas['n'] > 0) {
        $filas[] = '👤 PERSONAS: ' . $personas['n'] . ' visita(s) a ' . $personas['tiendas'] . ' tienda(s)';
        foreach (avisos_top_negocios($minutos, 3) as $t) {
            [$nombre] = avisos_negocio_txt($t['negocio_id']);
            $filas[] = '  🏆 ' . $nombre . ' (' . (int)$t['n'] . ')';
        }
    } else {
        $filas[] = '👤 PERSONAS: ninguna visita a tienda con señales de persona en esta hora.';
    }

    // ---------- 2) 📞/💬 LO QUE PIDIERON (dinero en la puerta) ----------
    if ($pedidos['n'] > 0) {
        $partes = [];
        if ($pedidos['llamadas'] > 0)  $partes[] = $pedidos['llamadas'] . ' 📞 llamada';
        if ($pedidos['clics'] > 0)     $partes[] = $pedidos['clics'] . ' 💬 WhatsApp';
        if ($pedidos['consultas'] > 0) $partes[] = $pedidos['consultas'] . ' 📦 consulta';
        if ($pedidos['carritos'] > 0)  $partes[] = $pedidos['carritos'] . ' 🛒 carrito';
        $filas[] = '';
        $filas[] = '📞 PIDIERON: ' . $pedidos['n'] . ' (' . implode(' · ', $partes) . ')';
        foreach ($pedidos['tops'] as $t) $filas[] = '  · ' . $t[0] . ' (' . $t[1] . ')';
    }

    // ---------- 3) 🔍 LO QUE BUSCARON ----------
    $busq_n = 0; $busq_top = [];
    try {
        $st = db()->prepare('SELECT COUNT(*) n FROM directorio_busquedas WHERE fecha > ?');
        $st->execute([avisos_desde($minutos)]);
        $busq_n = (int)$st->fetchColumn();
        if ($busq_n > 0) {
            $st = db()->prepare('SELECT norm, COUNT(*) n FROM directorio_busquedas
                                  WHERE fecha > ? GROUP BY norm ORDER BY n DESC LIMIT 4');
            $st->execute([avisos_desde($minutos)]);
            foreach ($st->fetchAll() as $f) $busq_top[] = $f['norm'] . ' (' . (int)$f['n'] . ')';
        }
    } catch (Throwable $e) { /* sin tabla de búsquedas */ }
    if ($busq_n > 0) {
        $filas[] = '';
        $filas[] = '🔍 BUSCARON: ' . implode(' · ', $busq_top);
    }

    // ---------- 4) 🚩 LO QUE ESPERA SU DECISIÓN Y LO NUEVO DEL SITIO ----------
    $otros = [];
    $pend = 0;
    try {
        $st = db()->prepare("SELECT COUNT(*) FROM directorio_reportes WHERE estado = 'pendiente'");
        $st->execute();
        $pend += (int)$st->fetchColumn();
    } catch (Throwable $e) { }
    try {
        $st = db()->prepare("SELECT COUNT(*) FROM directorio_opiniones_reportes WHERE estado = 'pendiente'");
        $st->execute();
        $pend += (int)$st->fetchColumn();
    } catch (Throwable $e) { }
    if ($pend > 0) $otros[] = '🚩 ' . $pend . ' reporte(s) por revisar';

    $etiquetas = [
        'opinion_nueva'   => '💬 Opiniones nuevas',
        'opinion_reportada'=> '🚩 Opiniones reportadas',
        'usuario_nuevo'   => '👤 Usuarios nuevos',
        'tienda_nueva'    => '🏪 Tiendas nuevas',
        'producto_nuevo'  => '📦 Productos nuevos',
        'empleo'          => '💼 Avisos de empleo',
        'reclamo'         => '🙋 Reclamos de tienda',
        'postulante'      => '💼 Postulaciones',
        'noticias_dia'    => '📰 Noticias publicadas',
    ];
    foreach ($etiquetas as $tipo => $etq) {
        $n = (int)($hechos[$tipo] ?? 0) + (int)($agrupado[$tipo] ?? 0);
        if ($n > 0) $otros[] = $etq . ': ' . $n;
    }
    if ($otros) {
        $filas[] = '';
        foreach ($otros as $o) $filas[] = $o;
    }

    // ---------- 5) 🤖 EL TRÁFICO AUTOMÁTICO (una línea, al final) ----------
    if ($robots['total'] > 0) {
        $filas[] = '';
        $filas[] = '🤖 Robots: ' . $robots['total'] . ' (' . $robots['conocidos'] . ' buscadores · '
                 . $robots['automaticas'] . ' automáticas sin señales de persona)';
    }

    $agrupados_total = array_sum($agrupado);
    $nada = ($personas['n'] === 0 && $pedidos['n'] === 0 && $busq_n === 0 && !$otros && $robots['total'] === 0);
    if ($nada && !$forzar) return '';

    // Lo que quedó agrupado por los topes (visitas/búsquedas que no se avisaron una a una)
    // ya no se cuenta aquí: ahora sale sumado en su propia línea (personas, pedidos, búsquedas).

    return avisos_formato('resumen_hora', [
        'filas'     => $filas,
        'agrupados' => 0,
        'hora'      => date('d/m H:i'),
    ]);
}

// ============================================================
// ERRORES FATALES DEL SITIO
// ============================================================
function avisos_vigilar_errores(): void {
    static $activo = false;
    if ($activo) return;
    $activo = true;
    register_shutdown_function(function () {
        try {
            $e = error_get_last();
            if (!$e || !in_array($e['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) return;
            $clave = md5(($e['message'] ?? '') . ($e['file'] ?? '') . ($e['line'] ?? ''));
            $ruta  = (string)($_SERVER['REQUEST_URI'] ?? '');
            $arch  = str_replace(dirname(__DIR__), '', (string)($e['file'] ?? ''));
            aviso('error_sitio', [
                'mensaje'    => mb_substr((string)($e['message'] ?? 'Error'), 0, 180),
                'archivo'    => $arch,
                'linea'      => (int)($e['line'] ?? 0),
                'ruta'       => $ruta,
                'clave'      => 'err:' . $clave,
                'dedupe_min' => AVISOS_DEDUPE_ERROR_MIN,
                // 🆕 2026-09-15: ANTES esta fila se guardaba sin resumen, así que en el panel
                // (📱 Telegram → Últimos avisos) solo se veía «💥 Error en el sitio» sin saber
                // CUÁL error ni en qué página: imposible de perseguir. Ahora queda escrito.
                'resumen'    => mb_substr(trim(($e['message'] ?? 'Error') . ' — ' . $arch
                                . ':' . (int)($e['line'] ?? 0) . ' — ' . $ruta), 0, 240),
            ]);
        } catch (Throwable $x) {
            // nada: nunca romper por un aviso
        }
    });
}

// Arranca la vigilancia de errores en cuanto se carga el motor (web y CLI)
avisos_vigilar_errores();
