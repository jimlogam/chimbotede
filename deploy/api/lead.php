<?php
/**
 * lead.php — Cuenta los clics en "WhatsApp" de una ficha y avisa al jefe
 * ============================================================
 * Por aquí pasan los botones 💬 WhatsApp de las fichas: el visitante va igual
 * a WhatsApp (no cambia nada para él), pero antes se registra el aviso.
 *
 *   https://dechimbote.com/api/lead.php?n=<negocio_id>[&p=<producto_id>][&origen=...]
 *
 * 🛒 CARRITO "ME INTERESA" (por tienda) — desde el 2026-09-10:
 *   El carrito de una tienda también envía por aquí, con el pedido ya armado:
 *
 *   https://dechimbote.com/api/lead.php?n=<negocio_id>&p=<id si es 1 solo>&origen=carrito&t=<mensaje>
 *
 *   - `t` es el mensaje que el navegador del visitante armó (productos + cantidades +
 *     total). Aquí se limpia, se limita y se pega al enlace de WhatsApp de la tienda
 *     (`wa.me/<número>?text=...`), así el pedido llega a la tienda EN UN SOLO MENSAJE.
 *   - El teléfono de la tienda NO viaja en la página: se busca aquí en la BD, así el
 *     carrito funciona también desde la portada (donde no hay ficha abierta).
 *   - Se registra como lead igual que un clic normal y el aviso de Telegram muestra el
 *     resumen del pedido (`carrito_txt`).
 *   - Si la tienda no tiene WhatsApp, el visitante vuelve a su ficha: nunca se pierde.
 *
 * 🖱️ EXIGE PRUEBA DE CLIC (2026-09-16) — EL ARREGLO DE LOS FALSOS POSITIVOS DEL TELEGRAM:
 *   El botón 💬 WhatsApp de la ficha es un ENLACE a este archivo, así que la URL queda escrita
 *   en el HTML de la ficha. Y por eso llegaba sola: los robots (Googlebot, ClaudeBot, MJ12bot,
 *   `curl`…) y la GRANJA de IPs seguían el enlace —o lo pedían a pelo— y CADA petición disparaba
 *   el aviso «🔥 PIDIERON PRECIO», aunque no hubiera nadie tocando nada. Medido en producción el
 *   2026-09-16: en 7 días llegaron 371 avisos de lead, y **306 venían de IPs que aparecen UNA SOLA
 *   VEZ en todo el registro** (ninguna visita a la ficha, ninguna sesión, modelos de móvil falsos
 *   como «Pixel 9» o «iPhone 18_4»): puro ruido del rastreador.
 *   Ahora un pedido solo cuenta si hay PRUEBA de que alguien hizo clic:
 *     · `&c=1` — lo pega `assets/js/llamadas.js` en el enlace EN EL MOMENTO DEL CLIC (un robot
 *       que lee el HTML no lo lleva, y un prefetch del navegador tampoco: se pide la URL vieja).
 *     · `Sec-Fetch-User: ?1` — la cabecera que mandan los navegadores cuando la navegación la
 *       activó el USUARIO. Es el respaldo para quien navega sin JavaScript.
 *   Sin ninguna de las dos, el visitante va a WhatsApp igual (a él no le cambia nada), pero el
 *   hecho queda registrado como 🚫 «robot» en `directorio_avisos_log` y NO avisa al jefe ni
 *   ensucia los récords. La guía: GUIA_TELEGRAM_BOT_AVISOS_Y_MONITOREO.md §19.
 *
 * 🧭 CONTEXTO OBLIGATORIO (2026-09-11, pedido del jefe): NINGÚN chat de WhatsApp del
 * sitio se abre en blanco. Todo mensaje lleva su contexto y el ENLACE de la página exacta
 * dentro de la frase (2026-09-16: antes iba una línea aparte con la etiqueta
 * «🔗 Página donde lo vi:», y el jefe dijo que era demasiado texto). El origen se resuelve
 * con wa_origen_url() (helpers.php): parámetro &u= del botón -> referer del navegador ->
 * ficha de la tienda. Y el `t` ya se respeta TAMBIÉN sin `origen=carrito` (antes la consulta
 * "💬 Preguntar por este producto" de la ficha rápida llegaba aquí y se DESCARTABA:
 * otro motivo de chats en blanco). Guía: GUIA_RECOMENDACIONES_Y_FICHA_PRODUCTO.md §8.2
 */
require_once __DIR__ . '/../config.php';

$negocio_id  = (int)($_GET['n'] ?? 0);
$producto_id = (int)($_GET['p'] ?? 0);
// `origen=carrito` lo manda el carrito; sirve para distinguirlo en los registros y en
// los ensayos. Del contexto del aviso se sigue usando el origen real (Google, WhatsApp…).
$es_carrito  = (($_GET['origen'] ?? '') === 'carrito');
$mensaje     = (string)($_GET['t'] ?? '');
// 🧭 URL de la página desde donde se tocó el botón (la pasan los botones que la
// conocen con &u=; si no viene, se intenta con el referer del navegador).
$origen_u    = (string)($_GET['u'] ?? '');

// 🖱️ ¿HUBO UN CLIC DE VERDAD? (2026-09-16 — el arreglo de los falsos positivos del Telegram)
//   · `c=1` → lo pega `assets/js/llamadas.js` al enlace en el momento del clic. Un robot que lee
//     el HTML no lo lleva, y un prefetch del navegador pide la URL vieja (sin marcador).
//   · `Sec-Fetch-User: ?1` → cabecera que mandan los navegadores cuando la navegación la activó
//     el usuario: es el respaldo para quien navega sin JavaScript.
//   Sin ninguna de las dos, el visitante VA IGUAL a WhatsApp (abajo se le redirige), pero el
//   pedido NO se cuenta ni avisa: solo queda anotado como robot en `directorio_avisos_log`.
$clic_js      = (($_GET['c'] ?? '') === '1');
$clic_usuario = (($_SERVER['HTTP_SEC_FETCH_USER'] ?? '') === '?1');
$clic_real    = ($clic_js || $clic_usuario);

// 👀 Usuario con sesión (si la hay). Se lee AQUÍ, antes de mandar la redirección, y se usa más
// abajo para que «El ninja» recuerde «le pediste precio a X» (ver el bloque del final).
$chatbot_uid = 0;
if (function_exists('usuario_actual')) {
    $__u = usuario_actual();
    if ($__u && !empty($__u['id'])) $chatbot_uid = (int)$__u['id'];
}

// --- Limpieza del mensaje del carrito (viene del navegador: nunca se confía en él) ---
$pedido = ($es_carrito && $mensaje !== '');
if ($mensaje !== '') {
    // Sin caracteres de control (deja \n y \t) y con tope de largo.
    // ⚠️ El tope son 1700 (no 1800): así queda sitio para la línea del 🎁 descuento que se
    // añade más abajo SIN que un mensaje enorme la corte (límite efectivo: ~1800).
    $mensaje = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', ' ', $mensaje);
    $mensaje = trim(mb_substr((string)$mensaje, 0, 1700));
    if ($mensaje === '') $pedido = false;
}

$destino = SITE_URL . '/';

try {
    if ($negocio_id > 0) {
        $st = db()->prepare("SELECT id, nombre, slug, whatsapp, telefono, estado, descuento FROM directorio_negocios WHERE id = ? LIMIT 1");
        $st->execute([$negocio_id]);
        $neg = $st->fetch();

        if ($neg) {
            // El producto solo se acepta si de verdad es de esta tienda (y de paso se
            // leen sus datos: entran al mensaje de contexto si es un clic directo).
            $prod = null;
            if ($producto_id > 0) {
                $sp = db()->prepare("SELECT titulo, precio, unidad FROM directorio_servicios WHERE id = ? AND negocio_id = ? LIMIT 1");
                $sp->execute([$producto_id, $negocio_id]);
                $prod = $sp->fetch() ?: null;
                if (!$prod) $producto_id = 0;
            }

            // 🎁 DESCUENTO POR DECHIMBOTE.COM (2026-09-10): el negocio lo DECLARÓ en su ficha
            // ("🎁 10 % de descuento por DeChimbote.com"). Aquí se añade al pedido del carrito,
            // justo debajo del total, para que el mensaje que le llega a la tienda lo diga:
            // así el cliente llega diciendo "vengo de la página" y el trato queda por escrito.
            $desc_pct = (int)($neg['descuento'] ?? 0);
            $desc_txt = '';
            if ($pedido && $desc_pct > 0 && $desc_pct <= 50) {
                $desc_txt = '🎁 Descuento por DeChimbote.com: ' . $desc_pct . ' %';
                if (preg_match('/Total referencial:\s*S\/\s*([0-9]+(?:[.,][0-9]+)?)/u', $mensaje, $mt)) {
                    $base = (float)str_replace(',', '.', $mt[1]);
                    if ($base > 0) {
                        $desc_txt .= ' · Total con descuento: S/ ' . number_format($base * (1 - $desc_pct / 100), 2);
                    }
                }
                // Se inserta debajo de la línea del total (o al final si no hubiera total).
                $lineas_msg = explode("\n", $mensaje);
                $nuevas = []; $insertado = false;
                foreach ($lineas_msg as $ln) {
                    $nuevas[] = $ln;
                    if (!$insertado && stripos(ltrim($ln), 'Total referencial:') === 0) {
                        $nuevas[] = $desc_txt;
                        $insertado = true;
                    }
                }
                if (!$insertado) { $nuevas[] = ''; $nuevas[] = $desc_txt; }
                $mensaje = implode("\n", $nuevas);
            }

            // Resumen del pedido del carrito para el aviso del jefe
            $carrito_txt = '';
            if ($pedido) {
                $lineas = [];
                if (preg_match_all('/^•\s*(.+)$/mu', $mensaje, $m)) $lineas = $m[1];
                $total_txt = '';
                if (preg_match('/^Total referencial:\s*(.+)$/mu', $mensaje, $mt)) $total_txt = trim($mt[1]);
                $detalle = implode(' · ', array_slice($lineas, 0, 3));
                $carrito_txt = trim(count($lineas) . ' producto(s)'
                    . ($total_txt !== '' ? ' · ' . $total_txt : '')
                    . ($detalle !== '' ? ' · ' . $detalle : ''));
                if ($desc_txt !== '') $carrito_txt .= ' · ' . mb_substr($desc_txt, 0, 40);
                $carrito_txt = mb_substr($carrito_txt, 0, 170);
            }

            // 🏆 Números del pedido para los RÉCORDS del Súper Admin (pedido del jefe, 2026-09-13):
            // cuántos productos traía y el «Total referencial» del mensaje. El pedido se guarda
            // en la base DESPUÉS de la redirección (más abajo), para no hacer esperar al visitante.
            $ped_n = 0;
            $ped_total = null;
            if ($pedido) {
                if (preg_match_all('/^•\s*(.+)$/mu', $mensaje, $m_ped)) $ped_n = count($m_ped[1]);
                if (preg_match('/Total referencial:\s*S\/\s*([0-9]+(?:[.,][0-9]+)?)/u', $mensaje, $m_tot)) {
                    $ped_total = (float)str_replace(',', '.', $m_tot[1]);
                }
            }

            $destino = !empty($neg['whatsapp'])
                ? url_whatsapp($neg['whatsapp'])
                : url_negocio((string)$neg['slug']);

            // 🧭 CONTEXTO OBLIGATORIO: el chat NUNCA se abre en blanco.
            //  - Si el navegador mandó un mensaje (`t`, del carrito o de la ficha rápida)
            //    se respeta tal cual; solo se le pone EL ENLACE si no lo trae.
            //  - Si no mandó nada (clic directo en 💬 WhatsApp de la ficha) el servidor
            //    arma el mensaje CORTO (2026-09-16, orden del jefe: «es demasiado texto»):
            //    saludo + el enlace DENTRO de la frase, y para un producto **el enlace EXACTO
            //    DEL PRODUCTO** (`/producto/<id>`, que ya trae foto, nombre y precio), nunca
            //    el de la tienda ni el nombre/precio repetidos en el texto.
            if ($mensaje !== '') {
                if (stripos($mensaje, 'http') === false) {
                    $origen = wa_origen_url($origen_u) ?: url_negocio((string)$neg['slug']);
                    $mensaje = wa_mensaje_con_enlace($mensaje, $origen);
                }
            } else {
                $origen = wa_origen_url($origen_u) ?: url_negocio((string)$neg['slug']);
                $hola   = 'Hola *' . (wa_nombre_corto($neg['nombre']) ?: 'la tienda') . '* 👋, ';
                if ($prod) {
                    $mensaje = $hola . 'quiero consultar por este producto: ' . url_producto($producto_id);
                } else {
                    $mensaje = $hola . 'la vi en ' . $origen . ' y quiero consultarle:';
                }
            }

            // Todo mensaje viaja en el enlace: pedido, consulta de producto o clic directo
            if (!empty($neg['whatsapp']) && $mensaje !== '') {
                $destino .= '?text=' . rawurlencode($mensaje);
            }

            // 🔔 Aviso al jefe: pidieron precio (lead caliente) — SOLO CON UN CLIC DE VERDAD
            if ($clic_real) {
                aviso('lead_precio', [
                    'negocio_id'       => $negocio_id,
                    'producto_id'      => $producto_id ?: null,
                    'telefono_negocio' => (string)($neg['whatsapp'] ?: $neg['telefono']),
                    'carrito_txt'      => $carrito_txt,     // '' = clic normal, no carrito
                    'clave'            => 'lead:' . (ip_real()) . ':' . $negocio_id
                                          . ($pedido ? ':carrito' : ''),
                    'dedupe_min'       => 10,
                    'resumen'          => ($pedido
                                            ? 'envió su pedido del carrito a ' . $neg['nombre']
                                            : 'pidió precio a ' . $neg['nombre']),
                ]);
            } else {
                // 🚫 NADIE HIZO CLIC: es un robot (o la granja de IPs) siguiendo el enlace del
                // botón, que está escrito en el HTML de la ficha. Se anota como robot —así se ve
                // en Súper Admin → 📱 Telegram y en los conteos— pero NO se avisa al jefe ni
                // ensucia los récords. El visitante, mientras tanto, ya salió redirigido.
                aviso('lead_precio', [
                    'negocio_id'       => $negocio_id,
                    'producto_id'      => $producto_id ?: null,
                    'telefono_negocio' => '',
                    'carrito_txt'      => '',
                    'es_bot'           => 1,
                    'bot'              => 'Enlace seguido sin clic (robot)',
                    'resumen'          => 'siguió el enlace del botón SIN clic real: ' . $neg['nombre'],
                ]);
            }
        }
    }
} catch (Throwable $e) {
    error_log('api/lead: ' . $e->getMessage());
}

header('Location: ' . $destino, true, 302);
echo 'Redirigiendo…';

// El visitante ya tiene su redirección enviada: lo que sigue NO le hace esperar.
if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
}

// === 🏆 RÉCORDS DEL SITIO: EL PEDIDO QUEDA GUARDADO EN LA BASE (2026-09-13, pedido del jefe) ===
// Hasta hoy, cada toque de un botón de pedir (WhatsApp de la ficha, consulta de un producto,
// pedido armado del carrito 🛒) solo se AVISABA al Telegram: no quedaba historial en la base, así
// que el Súper Admin no podía decir qué tienda recibe más pedidos ni cuánto dinero mueve el sitio.
// Ahora sí. Va DESPUÉS de la redirección: el visitante no espera nada por esto.
// 🖱️ 2026-09-16: solo si hubo CLIC DE VERDAD (`$clic_real`). Antes entraban aquí los rastreadores
// (Googlebot, ClaudeBot…) y la granja de IPs, y las tablas de récords salían infladas con clics
// que nadie hizo.
try {
    if ($negocio_id > 0 && $clic_real) {
        require_once __DIR__ . '/../includes/metricas.php';
        metrica_pedido([
            'negocio_id'    => $negocio_id,
            'producto_id'   => $producto_id ?: null,
            // 'pedido' = armó el carrito · 'consulta' = preguntó por un producto · 'clic' = WhatsApp a secas
            'tipo'          => $pedido ? 'pedido' : ($producto_id ? 'consulta' : 'clic'),
            'origen'        => $es_carrito ? 'carrito' : 'ficha',
            'productos_n'   => $ped_n ?? 0,
            'total'         => $ped_total ?? null,
            'descuento_pct' => $desc_pct ?? 0,
            'detalle'       => $carrito_txt ?? '',
        ]);
    }
} catch (Throwable $e) {
    error_log('api/lead (récords): ' . $e->getMessage());
}

// === 👀 EL CHAT DE AYUDA SE ACUERDA DE ESTO (pedido del jefe, 2026-09-13) ===
// Si quien pidió el precio tiene sesión iniciada, se anota en `cache/chatbot/actividad/u<ID>.jsonl`
// para que «El ninja» pueda recordárselo luego con gracia («me acuerdo que le pediste precio a X»).
// Se hace DESPUÉS de la redirección: el visitante no espera nada por esto. El sitio NO guardaba
// esta acción en la base de datos (solo avisaba al Telegram), así que el registro es del chat.
try {
    if ($chatbot_uid > 0 && $clic_real) {
        require_once __DIR__ . '/../includes/chatbot_actividad.php';
        if (function_exists('chatbot_leads_registrar')) {
            chatbot_leads_registrar($chatbot_uid, [
                'negocio'  => (string)($neg['nombre'] ?? ''),
                'slug'     => (string)($neg['slug'] ?? ''),
                'producto' => (string)($prod['titulo'] ?? ($carrito_txt !== '' ? $carrito_txt : '')),
                'precio'   => (float)($prod['precio'] ?? 0),
                'pedido'   => $pedido,
            ]);
        }
    }
} catch (Throwable $e) {
    error_log('api/lead (chatbot actividad): ' . $e->getMessage());
}

// === INTEGRACIÓN HUBSPOT: sumar 1 al lead score del dueño ===
// Al cruzar 3 pedidos de precio por primera vez, avisa al jefe por Telegram.
// 🖱️ 2026-09-16: solo con clic de verdad (si no, el rastreador inflaba el lead score).
try {
    if ($negocio_id > 0 && $clic_real) {
        require_once __DIR__ . '/../includes/helpers_hubspot.php';
        hubspot_sync_clic_precio($negocio_id);
    }
} catch (Throwable $e) {
    error_log('api/lead (HubSpot): ' . $e->getMessage());
}
// === FIN INTEGRACIÓN HUBSPOT ===

exit;
