<?php
/**
 * includes/tienda_ia.php — EL MAESTRO 🛠️: MOTOR DEL CONSTRUCTOR DE TIENDAS
 * ======================================================================
 * Qué hace: lleva la conversación con la que un usuario REGISTRADO arma su tienda
 * (la página privada `/crear-tienda`). El servidor manda los pasos y la IA pone las
 * palabras: así la conversación nunca se sale del guion, nunca pide datos que ya
 * tiene y nunca se queda trabada (cada paso tiene su respuesta local de respaldo).
 *
 * LA IDEA (orden del jefe, 2026-09-14):
 *   1. nombre de la tienda (no los productos) → 2. de qué trata → 3. cuántos productos
 *   (1 · 3 · 5) → 4. fotos de la tienda (la IA las MIRA y comenta lo que ve) →
 *   5. cada producto con sus fotos (la IA también las mira y pide otro ángulo) →
 *   6. cómo vende: ambulante 🛵 / delivery a casa 🏠 / local 🏪 → 7. publicar.
 *
 * CÓMO ESTÁ HECHO (y por qué así):
 *   · **El paso lo decide el servidor** (`paso` en la tabla `directorio_ia_tiendas`), no
 *     la IA. La IA solo escribe la respuesta y, cuando hace falta, interpreta lo que el
 *     dueño escribió. Si la IA falla, responde vacío o se acaba el saldo, el motor sigue
 *     con el **guion local** y la conversación no se rompe jamás.
 *   · **Una llamada a la API por respuesta del dueño** (y una por foto): el gasto es
 *     predecible y se mide en `directorio_ia_tiendas_log`.
 *   · **🧠 Razonamiento apagado** (`reasoning_effort: none`): medido el 2026-09-14, este
 *     modelo gasta entre 200 y 800 tokens PENSANDO antes de escribir, tarda el doble y en
 *     tareas con formato se queda mudo. Apagado: 1 segundo, 20 tokens y mejor texto.
 *   · **Las fotos se guardan como todo el sitio** (`img_guardar_subida()`: WebP ≤1600 px
 *     + versiones de 800 y 300). A la IA se le manda una copia aligerada (≤1024 px) que
 *     NO se archiva.
 *   · **La regla inviolable se cumple sola**: si la foto es una CAPTURA DE PANTALLA, la
 *     IA lo dice, la foto se borra y no entra ni a la tienda ni al hosting.
 *
 * Archivos del módulo (ver la cabecera de `config_tienda_ia.php`) y guía completa en
 * `GUIA_CONSTRUCTOR_DE_TIENDAS.md`.
 */

require_once __DIR__ . '/config_tienda_ia.php';
require_once __DIR__ . '/helpers.php';   // trae la base de datos, `slug_unico()`, `aviso()` e `imagenes.php`

/* =====================================================================================
 * 1) LAS TABLAS (se crean solas la primera vez; los `migrar_*.php` están bloqueados
 *    por el antivirus del hosting, así que el módulo se instala solo, como Empleos)
 * ===================================================================================== */

if (!function_exists('tienda_ia_tablas_ok')) {
    /**
     * ¿Ya existen las dos tablas? Si no existen, se crean AQUÍ MISMO (auto-instalación
     * defensiva, igual que el módulo de Empleos: los `migrar_*.php` están bloqueados por
     * el antivirus del hosting). Una sola comprobación por petición y, si la creación
     * sale bien, la respuesta queda recordada.
     */
    function tienda_ia_tablas_ok() {
        static $ok = null;
        if ($ok !== null) return $ok;
        try {
            db()->query("SELECT 1 FROM " . TIENDA_IA_TABLA . " LIMIT 1");
            // ⚠️ La tabla puede existir de ANTES y faltarle una columna nueva (pasó el
            // 2026-09-14 con `visitante`, la del que llega sin cuenta): si falta, se instala.
            $col = db()->query("SHOW COLUMNS FROM " . TIENDA_IA_TABLA . " LIKE 'visitante'")->fetch();
            $ok  = $col ? true : tienda_ia_instalar();
        } catch (Throwable $e) {
            $ok = tienda_ia_instalar();
        }
        return $ok;
    }
}

if (!function_exists('tienda_ia_instalar')) {
    /**
     * Crea las tablas del constructor si faltan. Es defensivo: si algo falla, devuelve
     * false y la página lo dice con claridad (nunca rompe el sitio).
     */
    function tienda_ia_instalar() {
        try {
            $pdo = db();
            $pdo->exec("CREATE TABLE IF NOT EXISTS " . TIENDA_IA_TABLA . " (
                id INT AUTO_INCREMENT PRIMARY KEY,
                usuario_id INT NOT NULL DEFAULT 0,
                visitante VARCHAR(64) NULL,
                modo VARCHAR(20) NOT NULL DEFAULT 'nueva',
                negocio_id INT NULL,
                paso VARCHAR(40) NOT NULL DEFAULT 'nombre',
                estado VARCHAR(20) NOT NULL DEFAULT 'en_curso',
                datos MEDIUMTEXT NULL,
                chat MEDIUMTEXT NULL,
                llamadas INT NOT NULL DEFAULT 0,
                tokens_in INT NOT NULL DEFAULT 0,
                tokens_out INT NOT NULL DEFAULT 0,
                cache_hit INT NOT NULL DEFAULT 0,
                costo_usd DECIMAL(12,6) NOT NULL DEFAULT 0,
                visto_en DATETIME NULL,
                creado_en DATETIME NOT NULL,
                actualizado_en DATETIME NOT NULL,
                publicado_en DATETIME NULL,
                KEY idx_usuario (usuario_id),
                KEY idx_visitante (visitante),
                KEY idx_estado (estado),
                KEY idx_paso (paso)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            $pdo->exec("CREATE TABLE IF NOT EXISTS " . TIENDA_IA_TABLA_LOG . " (
                id INT AUTO_INCREMENT PRIMARY KEY,
                sesion_id INT NOT NULL,
                usuario_id INT NOT NULL,
                paso VARCHAR(40) NULL,
                modelo VARCHAR(60) NULL,
                con_imagen TINYINT(1) NOT NULL DEFAULT 0,
                ok TINYINT(1) NOT NULL DEFAULT 1,
                error VARCHAR(40) NULL,
                tokens_in INT NOT NULL DEFAULT 0,
                tokens_out INT NOT NULL DEFAULT 0,
                cache_hit INT NOT NULL DEFAULT 0,
                ms INT NOT NULL DEFAULT 0,
                costo_usd DECIMAL(12,6) NOT NULL DEFAULT 0,
                creado_en DATETIME NOT NULL,
                KEY idx_sesion (sesion_id),
                KEY idx_usuario (usuario_id),
                KEY idx_fecha (creado_en)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            // 🔁 Si la tabla YA existía (se instaló el 2026-09-14 por la mañana), le falta la
            //    columna `visitante`: es la que permite que alguien SIN cuenta empiece su tienda
            //    (la conversación se identifica por su sesión de PHP) y reciba su clave al final.
            try {
                $tiene = $pdo->query("SHOW COLUMNS FROM " . TIENDA_IA_TABLA . " LIKE 'visitante'")->fetch();
                if (!$tiene) {
                    $pdo->exec("ALTER TABLE " . TIENDA_IA_TABLA . " ADD COLUMN visitante VARCHAR(64) NULL AFTER usuario_id");
                    $pdo->exec("ALTER TABLE " . TIENDA_IA_TABLA . " ADD KEY idx_visitante (visitante)");
                }
            } catch (Throwable $e) { /* si ya está, da igual */ }

            return true;
        } catch (Throwable $e) {
            error_log('tienda_ia_instalar: ' . $e->getMessage());
            return false;
        }
    }
}

/* =====================================================================================
 * 2) EL GASTO (para poder MEDIR EL CONSUMO, que es lo que pidió el jefe)
 * ===================================================================================== */

if (!function_exists('tienda_ia_costo_usd')) {
    /** Cuánto costó una llamada (precios de DeepSeek fuera de hora punta). */
    function tienda_ia_costo_usd($tokens_in, $tokens_out, $cache_hit = 0) {
        $cache_hit  = max(0, min((int)$cache_hit, (int)$tokens_in));
        $sin_cache  = max(0, (int)$tokens_in - $cache_hit);
        $costo = ($cache_hit  / 1000000) * TIENDA_IA_PRECIO_CACHE
               + ($sin_cache  / 1000000) * TIENDA_IA_PRECIO_IN
               + ((int)$tokens_out / 1000000) * TIENDA_IA_PRECIO_OUT;
        return round($costo, 6);
    }
}

if (!function_exists('tienda_ia_log')) {
    /**
     * Apunta cada llamada a la IA (tokens y costo): es el medidor del consumo.
     * ⚠️ `$con_imagen` guarda **cuántas fotos** miró esa llamada (antes 0/1): desde el 2026-09-15 la
     * galería puede mandar hasta 8 fotos en UNA sola llamada, y la pestaña del Súper Admin suma esa
     * columna para decir «fotos que miró la IA» (por eso se guarda el número, no un sí/no).
     */
    function tienda_ia_log($sesion_id, $usuario_id, $paso, array $r, $con_imagen = 0) {
        if (!tienda_ia_tablas_ok()) return;
        $ok    = !empty($r['ok']) ? 1 : 0;
        $tin   = (int)($r['tokens_in'] ?? 0);
        $tout  = (int)($r['tokens_out'] ?? 0);
        $cache = (int)($r['cache_hit'] ?? 0);
        $costo = $ok ? tienda_ia_costo_usd($tin, $tout, $cache) : 0;
        try {
            db()->prepare("INSERT INTO " . TIENDA_IA_TABLA_LOG . "
                (sesion_id, usuario_id, paso, modelo, con_imagen, ok, error, tokens_in, tokens_out, cache_hit, ms, costo_usd, creado_en)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)")
                ->execute([
                    (int)$sesion_id, (int)$usuario_id, (string)$paso,
                    (string)($r['modelo'] ?? TIENDA_IA_MODELO), max(0, (int)$con_imagen), $ok,
                    $ok ? null : mb_substr((string)($r['error'] ?? 'error'), 0, 40),
                    $tin, $tout, $cache, (int)($r['ms'] ?? 0), $costo, date('Y-m-d H:i:s'),
                ]);
            db()->prepare("UPDATE " . TIENDA_IA_TABLA . "
                SET llamadas = llamadas + 1, tokens_in = tokens_in + ?, tokens_out = tokens_out + ?,
                    cache_hit = cache_hit + ?, costo_usd = costo_usd + ?
                WHERE id = ?")->execute([$tin, $tout, $cache, $costo, (int)$sesion_id]);
        } catch (Throwable $e) {
            error_log('tienda_ia_log: ' . $e->getMessage());
        }
    }
}

if (!function_exists('tienda_ia_llamadas_hoy')) {
    /** Cuántas llamadas a la IA se hicieron hoy (de una persona o de todo el sitio). */
    function tienda_ia_llamadas_hoy($usuario_id = 0) {
        if (!tienda_ia_tablas_ok()) return 0;
        try {
            $desde = date('Y-m-d') . ' 00:00:00';
            if ($usuario_id > 0) {
                $s = db()->prepare("SELECT COUNT(*) FROM " . TIENDA_IA_TABLA_LOG . " WHERE usuario_id = ? AND creado_en >= ?");
                $s->execute([(int)$usuario_id, $desde]);
            } else {
                $s = db()->prepare("SELECT COUNT(*) FROM " . TIENDA_IA_TABLA_LOG . " WHERE creado_en >= ?");
                $s->execute([$desde]);
            }
            return (int)$s->fetchColumn();
        } catch (Throwable $e) { return 0; }
    }
}

/* =====================================================================================
 * 3) LA API DE DEEPSEEK (clave PROPIA del constructor, ver config_tienda_ia.php)
 * ===================================================================================== */

if (!function_exists('tienda_ia_sistema')) {
    /**
     * El guion de fondo del asistente: es IDÉNTICO en todas las llamadas a propósito
     * (así DeepSeek acierta la caché del prompt y sale más barato) y por eso no lleva
     * ni el nombre de la tienda ni lo que ya contestó: eso va en el mensaje del turno.
     */
    function tienda_ia_sistema() {
        return "Eres «" . TIENDA_IA_NOMBRE . "» " . TIENDA_IA_EMOJI . ", el ayudante de dechimbote.com que arma tiendas con su dueño, "
             . "en Chimbote (Perú, provincia del Santa).\n\n"
             . "CÓMO HABLAS (esto es lo que más importa):\n"
             . "· Español del Perú, tuteando, cálido y sencillo, como un amigo que ayuda. Nada de hablar como un formulario.\n"
             . "· MUY CORTO: UNA frase (máximo 15 palabras), como en un chat de WhatsApp. Sin listas, sin títulos, sin markdown. Un emoji como mucho.\n"
             . "· Nunca repites lo que ya se dijo ni pides un dato que ya tienes.\n"
             . "· Nunca inventas nada del negocio: solo usas lo que el dueño contó o lo que se ve de verdad en su foto.\n"
             . "· Si algo se ve mal en su foto, se lo dices con cariño y le pides otra (nunca lo haces sentir mal).\n"
             . "· No prometes precios, ni clientes, ni posiciones en Google. Nada de promesas que no se puedan cumplir.\n"
             . "· Obedeces el formato exacto que se te pide en cada tarea, aunque parezca raro.";
    }
}

if (!function_exists('tienda_ia_llamar')) {
    /**
     * Una llamada a DeepSeek. Devuelve siempre un array (nunca lanza excepción):
     *   ['ok'=>true,'texto','tokens_in','tokens_out','cache_hit','ms','modelo']
     *   ['ok'=>false,'error'=>'sin_clave'|'http'|'red'|'formato'|'vacio'|'limite', ...]
     *
     * @param string $mensaje  lo que se le pide (el turno del dueño + la tarea)
     * @param array  $op       'imagen' (ruta relativa de la foto), 'max_tokens', 'paso', 'sesion', 'usuario'
     */
    function tienda_ia_llamar($mensaje, array $op = []) {
        $key = trim((string)TIENDA_IA_DEEPSEEK_KEY);
        if ($key === '') return ['ok' => false, 'error' => 'sin_clave', 'detalle' => 'La clave está vacía en config_tienda_ia.php'];

        // 👁️ UNA O VARIAS IMÁGENES EN EL MISMO MENSAJE (2026-09-15). El dueño puede elegir 5 u 8
        // fotos de golpe en la galería: se guardan todas y se le mandan al modelo en UNA sola
        // llamada, numeradas. Comprobado con la clave del constructor el 2026-09-15: 2 imágenes =
        // HTTP 200 · 672 tokens · 2,0 s; 3 imágenes = HTTP 200 · 951 tokens · 1,8 s.
        $rels = [];
        if (!empty($op['imagenes']) && is_array($op['imagenes'])) {
            foreach ($op['imagenes'] as $rel) { if (is_string($rel) && $rel !== '') $rels[] = $rel; }
        }
        if (!$rels && !empty($op['imagen'])) $rels = [(string)$op['imagen']];

        $contenido = $mensaje;
        $n_imgs    = 0;
        if ($rels) {
            $contenido = [['type' => 'text', 'text' => $mensaje]];
            foreach ($rels as $rel) {
                // `lado`/`calidad` (opcionales) = la copia que ve la IA. La lectura del letrero los sube
                // a 1600 px para poder leer el aviso del teléfono y el nombre pintado en la pared.
                $data_url = tienda_ia_foto_data_url($rel, $op['lado'] ?? null, $op['calidad'] ?? null);
                if ($data_url === '') continue;   // una foto ilegible no tumba a las demás
                $contenido[] = ['type' => 'image_url', 'image_url' => ['url' => $data_url]];
                $n_imgs++;
            }
            if ($n_imgs === 0) {
                return ['ok' => false, 'error' => 'foto', 'detalle' => 'No se pudo leer la foto'];
            }
        }
        $con_imagen = ($n_imgs > 0);

        $cuerpo = [
            'model'             => TIENDA_IA_MODELO,
            'messages'          => [
                ['role' => 'system', 'content' => tienda_ia_sistema()],
                ['role' => 'user',   'content' => $contenido],
            ],
            'temperature'       => (float)TIENDA_IA_TEMPERATURA,
            'max_tokens'        => (int)($op['max_tokens'] ?? ($con_imagen ? TIENDA_IA_MAX_TOKENS_IMG : TIENDA_IA_MAX_TOKENS)),
            'reasoning_effort'  => TIENDA_IA_RAZONAMIENTO,   // 🧠 apagado a propósito (medido)
            'stream'            => false,
        ];

        $payload = json_encode($cuerpo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($payload === false) return ['ok' => false, 'error' => 'formato', 'detalle' => 'No se pudo armar el JSON'];

        $cabeceras = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $key,
            'Accept: application/json',
        ];

        $t0 = microtime(true);
        $respuesta = null;
        $code = 0;
        $err  = '';

        if (function_exists('curl_init')) {
            $ch = curl_init(TIENDA_IA_API_URL);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => $payload,
                CURLOPT_HTTPHEADER     => $cabeceras,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => (int)TIENDA_IA_TIMEOUT,
                CURLOPT_CONNECTTIMEOUT => (int)TIENDA_IA_CONNECT_TIMEOUT,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_FOLLOWLOCATION => false,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            ]);
            $respuesta = curl_exec($ch);
            $code      = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if ($respuesta === false) $err = (string)curl_error($ch);
            if (PHP_VERSION_ID < 80500) curl_close($ch);
        } else {
            $ctx = stream_context_create(['http' => [
                'method'        => 'POST',
                'header'        => implode("\r\n", $cabeceras),
                'content'       => $payload,
                'timeout'       => (int)TIENDA_IA_TIMEOUT,
                'ignore_errors' => true,
            ]]);
            $respuesta = @file_get_contents(TIENDA_IA_API_URL, false, $ctx);
            if (isset($http_response_header[0]) && preg_match('#\s(\d{3})\s#', $http_response_header[0], $m)) $code = (int)$m[1];
            if ($respuesta === false) $err = 'file_get_contents falló';
        }

        $ms = (int)round((microtime(true) - $t0) * 1000);

        if ($respuesta === false || $respuesta === null || $respuesta === '') {
            $r = ['ok' => false, 'error' => 'red', 'detalle' => $err !== '' ? $err : 'sin respuesta', 'ms' => $ms];
            tienda_ia_log((int)($op['sesion'] ?? 0), (int)($op['usuario'] ?? 0), (string)($op['paso'] ?? ''), $r, $n_imgs);
            return $r;
        }

        $data = json_decode($respuesta, true);
        if (!is_array($data)) {
            $r = ['ok' => false, 'error' => 'formato', 'detalle' => 'Respuesta ilegible (HTTP ' . $code . ')', 'ms' => $ms];
            tienda_ia_log((int)($op['sesion'] ?? 0), (int)($op['usuario'] ?? 0), (string)($op['paso'] ?? ''), $r, $n_imgs);
            return $r;
        }
        if ($code !== 200 || isset($data['error'])) {
            $r = ['ok' => false, 'error' => 'http', 'detalle' => (string)($data['error']['message'] ?? ('HTTP ' . $code)), 'http' => $code, 'ms' => $ms];
            tienda_ia_log((int)($op['sesion'] ?? 0), (int)($op['usuario'] ?? 0), (string)($op['paso'] ?? ''), $r, $n_imgs);
            return $r;
        }

        $texto = trim((string)($data['choices'][0]['message']['content'] ?? ''));
        if ($texto === '') {
            // ⚠️ Le pasa a este modelo cuando se pone a razonar de más: por eso el motor
            // NUNCA depende de que la IA conteste (cada paso tiene su respuesta local).
            $r = ['ok' => false, 'error' => 'vacio', 'detalle' => 'El modelo no devolvió texto', 'ms' => $ms];
            tienda_ia_log((int)($op['sesion'] ?? 0), (int)($op['usuario'] ?? 0), (string)($op['paso'] ?? ''), $r, $n_imgs);
            return $r;
        }

        $r = [
            'ok'         => true,
            'texto'      => $texto,
            'tokens_in'  => (int)($data['usage']['prompt_tokens'] ?? 0),
            'tokens_out' => (int)($data['usage']['completion_tokens'] ?? 0),
            'cache_hit'  => (int)($data['usage']['prompt_cache_hit_tokens'] ?? 0),
            'ms'         => $ms,
            'modelo'     => (string)($data['model'] ?? TIENDA_IA_MODELO),
        ];
        tienda_ia_log((int)($op['sesion'] ?? 0), (int)($op['usuario'] ?? 0), (string)($op['paso'] ?? ''), $r, $n_imgs);
        return $r;
    }
}

if (!function_exists('tienda_ia_foto_data_url')) {
    /**
     * La foto que VE la IA: se lee del disco la versión WebP guardada, se reduce a
     * `TIENDA_IA_FOTO_LADO_IA` px y se manda como JPEG (más liviano de subir). Si el
     * servidor no tiene GD, se manda el WebP tal cual (comprobado que lo entiende).
     * Nada de esto se archiva: es una copia de viaje.
     */
    function tienda_ia_foto_data_url($rel, $lado = null, $calidad = null) {
        $rel = ltrim(str_replace('\\', '/', (string)$rel), '/');
        if ($rel === '' || strpos($rel, '..') !== false) return '';
        $ruta = img_ruta_fisica($rel);
        if (!is_file($ruta)) return '';

        $bytes = @file_get_contents($ruta);
        if ($bytes === false) return '';

        if (function_exists('imagecreatefromstring') && function_exists('imagejpeg')) {
            $im = @imagecreatefromstring($bytes);
            if ($im) {
                $ancho = imagesx($im);
                $alto  = imagesy($im);
                // 📷 La lectura del letrero (paso `fotos`) pide una copia más grande y más nítida:
                // a 1024 px los números del aviso se confunden. El resto de llamadas sigue igual.
                $lado  = ($lado !== null)    ? (int)$lado    : (int)TIENDA_IA_FOTO_LADO_IA;
                $cal   = ($calidad !== null) ? (int)$calidad : (int)TIENDA_IA_FOTO_CALIDAD_IA;
                if (max($ancho, $alto) > $lado) {
                    $im = img_escalar($im, $lado, $lado);
                }
                $tmp = tempnam(sys_get_temp_dir(), 'czia');
                if ($tmp !== false) {
                    $ok = @imagejpeg($im, $tmp, $cal);
                    imagedestroy($im);
                    if ($ok) {
                        $jpeg = @file_get_contents($tmp);
                        @unlink($tmp);
                        if ($jpeg !== false && $jpeg !== '') {
                            $url = 'data:image/jpeg;base64,' . base64_encode($jpeg);
                            if (strlen($url) <= TIENDA_IA_FOTO_MAX_BYTES) return $url;
                        }
                    }
                } else {
                    imagedestroy($im);
                }
            }
        }
        // Plan B: el WebP guardado (DeepSeek también lo lee, comprobado el 2026-09-14).
        $url = 'data:image/webp;base64,' . base64_encode($bytes);
        return strlen($url) <= TIENDA_IA_FOTO_MAX_BYTES ? $url : '';
    }
}

/* =====================================================================================
 * 4) LA CONVERSACIÓN GUARDADA (tabla directorio_ia_tiendas)
 * ===================================================================================== */

if (!function_exists('tienda_ia_datos_vacios')) {
    /** El estado de una tienda recién empezada. */
    function tienda_ia_datos_vacios() {
        $datos = [
            'modo'         => 'nueva',    // 'nueva' (tienda nueva) | 'producto' (agregar a una que ya tiene)
            'nombre'       => '',
            'trato'        => '',
            'rubro_id'     => null,
            'rubro_nombre' => '',
            'rubro_opciones' => [],
            // 🏷️ Los rubros EXTRA además del principal (orden del jefe, 2026-09-15): hasta 4 en total.
            'rubros_extra' => [],        // [ ['id'=>..,'nombre'=>'..'], … ] (máximo 3: el principal + 3)
            'rubros_todos' => [],        // los candidatos del último turno (para los chips del paso)
            'fotos'        => [],
            'visto'        => [],        // 📷 lo que la IA dijo que VEÍA en las fotos (sirve para proponer productos)
            // 📷🆕 LO QUE LA IA LEE EN LAS FOTOS DEL ARRANQUE (2026-09-15): de aquí sale el prototipo
            // de la tienda. Nada de esto se publica sin que el dueño lo confirme (el teléfono del
            // letrero, sobre todo: muchas veces es el de la imprenta que hizo el aviso).
            'flujo'          => '',      // 'fotos' = flujo nuevo (fotos primero) · '' = flujo viejo
            'letrero'        => '',      // el nombre del negocio leído en el letrero/pared
            'rubro_leido'    => '',      // de qué es el negocio, según la IA
            'telefono_leido' => '',      // el número leído en un aviso (solo dígitos)
            'direccion_leida' => '',     // la dirección o referencia leída
            'productos_vistos' => [],    // cosas concretas que se ven y se pueden vender
            'telefono_ok'    => 0,       // 1 = el dueño dijo que el número leído es suyo
            // 📋 LOS DATOS DE LA PÁGINA DE REGISTRO (2026-09-15, orden del jefe: *«si no sacas datos
            // de las fotos, copia los datos que pide registrar_negocio.php»*): la dirección y las redes
            // son los campos de esa página que el asistente todavía no preguntaba.
            'direccion'    => '',        // calle / referencia (obligatoria en esa página si es local o ambulante)
            'facebook'     => '',
            'instagram'    => '',
            'tiktok'       => '',
            'sugerir_domicilio' => 0,    // 🚚 la IA vio que es un servicio que se lleva (no un local)
            'productos'    => [],         // los que se van creando DESPUÉS de publicar la tienda
            'productos_ia' => 0,          // cuántos productos lleva creados con el asistente (tope: TIENDA_IA_PRODUCTOS_CON_IA)
            // 🆕 EL TOTAL DE LA CONVERSACIÓN (2026-09-16): `productos_ia` cuenta la RONDA (se pone a 0
            // cada vez que el dueño pide «más productos», porque el tope de 2 es POR RONDA) y este
            // cuenta TODOS los que lleva hechos con el asistente: es el que numera los productos
            // («Producto 3:», «Producto 4:»…) para que el dueño no vuelva a ver «tu primer producto».
            'productos_total' => 0,
            'producto_opciones' => [],
            'producto_indice' => 0,
            'producto_borrado' => '',
            // 🛒🆕 EL PRODUCTO QUE YA VIENE DICHO (2026-09-16): el botón **«Agregar mi tienda»** de los
            // resultados de búsqueda (`buscar.php`) abre el asistente con el término buscado
            // (`/crear-tienda?modo=producto&prod=cumpleaños`). Ese nombre se guarda aquí y hace que el
            // asistente **no pregunte cómo se llama el producto**: nace con él y pide sus fotos.
            'producto_pedido' => '',
            'vendedor'     => '',
            'distrito_id'  => null,
            'distrito_nombre' => '',
            'distrito_todas' => 0,        // 🗺️ eligió «Todas las anteriores»: atiende en toda la provincia
            'whatsapp'     => '',
            'horario'      => '',
            'descripcion_ia' => '',       // 📝 la descripción que escribe la IA (copywriting) al publicar
            'lat'          => null,
            'lng'          => null,
            'negocio_id'   => null,       // modo «producto»: la tienda que ya tiene
            'publicado'    => null,       // ['negocio_id','slug','url'] de la tienda publicada
            'cuenta'       => null,       // ['usuario','clave','nueva'=>bool] → se le muestra al final
            'editando'     => '',
        ];
        // 🧪 EL GANCHO DE LAS SONDAS (§11 de la guía): `__tia_prueba*.php` pone
        // `$GLOBALS['TIA_MODO_PRUEBA'] = 1` y entonces la tienda de prueba se crea DE VERDAD (hace
        // falta para probar el camino completo) pero **no se le avisa al jefe por Telegram ni se
        // sincroniza el CRM**: una prueba no debe dejar rastro fuera del sitio.
        if (!empty($GLOBALS['TIA_MODO_PRUEBA'])) $datos['prueba'] = 1;
        return $datos;
    }
}

if (!function_exists('tienda_ia_cargar')) {
    /** Lee una conversación por su id. */
    function tienda_ia_cargar($id) {
        if (!tienda_ia_tablas_ok()) return null;
        try {
            $s = db()->prepare("SELECT * FROM " . TIENDA_IA_TABLA . " WHERE id = ? LIMIT 1");
            $s->execute([(int)$id]);
            $f = $s->fetch(PDO::FETCH_ASSOC);
            if (!$f) return null;
            $f['datos'] = json_decode((string)$f['datos'], true) ?: tienda_ia_datos_vacios();
            $f['datos'] = array_merge(tienda_ia_datos_vacios(), $f['datos']);
            $f['chat']  = json_decode((string)$f['chat'], true) ?: [];
            return $f;
        } catch (Throwable $e) { return null; }
    }
}

if (!function_exists('tienda_ia_actual')) {
    /**
     * La conversación EN CURSO de esta persona (o una nueva, recién creada).
     * Un dueño puede dejar el celular, volver al día siguiente y seguir donde iba.
     *
     * 🆔 **Quién es quién**: si hay sesión, la conversación es de su `usuario_id`. Si NO hay
     * sesión (alguien que todavía no tiene cuenta —el asistente se la crea al publicar—), la
     * conversación se identifica por `$visitante`, que es un hash de su sesión de PHP: así puede
     * recargar, cerrar el celular y volver sin perder nada, y nadie más la ve.
     */
    function tienda_ia_actual($usuario_id, $modo = 'nueva', $visitante = '', $crear = true) {
        if (!tienda_ia_tablas_ok()) return null;
        $usuario_id = (int)$usuario_id;
        $visitante  = preg_replace('/[^a-f0-9]/', '', (string)$visitante);
        // 👑🆕 2026-09-18 — EL MODO DEL SUPREMO (`supremo`): la página privada del Súper Administrador
        // (GUIA_EL_SUPREMO.md) usa **esta misma tabla y este mismo motor de sesiones**, solo que con
        // sus propios pasos. Se admite aquí para no duplicar el guardado, el chat y el medidor de
        // consumo; su tope diario de tiendas no aplica (el que crea tiendas ahí es el propio jefe).
        if (!in_array($modo, ['producto', 'nueva', 'supremo'], true)) $modo = 'nueva';
        try {
            $limite = date('Y-m-d H:i:s', time() - (int)TIENDA_IA_SESION_HORAS * 3600);
            if ($usuario_id > 0) {
                $s = db()->prepare("SELECT id FROM " . TIENDA_IA_TABLA . "
                    WHERE usuario_id = ? AND modo = ? AND estado = 'en_curso' AND actualizado_en >= ?
                    ORDER BY id DESC LIMIT 1");
                $s->execute([$usuario_id, $modo, $limite]);
            } else {
                if ($visitante === '') return null;
                $s = db()->prepare("SELECT id FROM " . TIENDA_IA_TABLA . "
                    WHERE usuario_id = 0 AND visitante = ? AND modo = ? AND estado = 'en_curso' AND actualizado_en >= ?
                    ORDER BY id DESC LIMIT 1");
                $s->execute([$visitante, $modo, $limite]);
            }
            $id = (int)$s->fetchColumn();
            if ($id) {
                $ses = tienda_ia_cargar($id);
                if ($ses) return $ses;
            }
            // 🛡️ `$crear = false` (lo usa el GET de la puerta): NO se escribe nada en la base.
            //    Así, abrir la página —o que la abra un robot— no deja filas: la conversación se
            //    crea cuando la persona contesta de verdad (el primer POST).
            if (!$crear) {
                $datos = tienda_ia_datos_vacios();
                $datos['modo'] = $modo;
                return [
                    'id' => 0, 'usuario_id' => $usuario_id, 'visitante' => ($visitante !== '' ? $visitante : null),
                    'modo' => $modo, 'negocio_id' => null,
                    'paso' => ($modo === 'producto' ? 'tienda' : 'arranque'), 'estado' => 'en_curso',
                    'datos' => $datos, 'chat' => [],
                    'llamadas' => 0, 'tokens_in' => 0, 'tokens_out' => 0, 'cache_hit' => 0, 'costo_usd' => 0,
                    'creado_en' => date('Y-m-d H:i:s'), 'actualizado_en' => date('Y-m-d H:i:s'),
                ];
            }
            // ¿Cuántas tiendas nuevas lleva hoy? (límite anti-abuso, no una promesa al dueño)
            if ($modo === 'nueva' && $usuario_id > 0) {
                $s = db()->prepare("SELECT COUNT(*) FROM " . TIENDA_IA_TABLA . "
                    WHERE usuario_id = ? AND modo = 'nueva' AND estado = 'publicada' AND publicado_en >= ?");
                $s->execute([$usuario_id, date('Y-m-d') . ' 00:00:00']);
                if ((int)$s->fetchColumn() >= (int)TIENDA_IA_TIENDAS_POR_USUARIO_DIA) {
                    return ['limite' => true];
                }
            }
            $ahora  = date('Y-m-d H:i:s');
            $inicio = $modo === 'producto' ? 'tienda' : 'arranque';
            $datos  = tienda_ia_datos_vacios();
            $datos['modo'] = $modo;
            db()->prepare("INSERT INTO " . TIENDA_IA_TABLA . "
                (usuario_id, visitante, modo, paso, estado, datos, chat, visto_en, creado_en, actualizado_en)
                VALUES (?,?,?,?, 'en_curso', ?, '[]', ?, ?, ?)")
                ->execute([$usuario_id, ($visitante !== '' ? $visitante : null), $modo, $inicio,
                           json_encode($datos, JSON_UNESCAPED_UNICODE), $ahora, $ahora, $ahora]);
            return tienda_ia_cargar((int)db()->lastInsertId());
        } catch (Throwable $e) {
            error_log('tienda_ia_actual: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('tienda_ia_guardar')) {
    /** Guarda el avance (paso, datos y conversación visible). */
    function tienda_ia_guardar(array $s) {
        if (!tienda_ia_tablas_ok()) return;
        try {
            $chat = $s['chat'];
            if (count($chat) > 160) $chat = array_slice($chat, -160);
            db()->prepare("UPDATE " . TIENDA_IA_TABLA . "
                SET paso = ?, estado = ?, datos = ?, chat = ?, negocio_id = ?, visto_en = ?, actualizado_en = ?
                WHERE id = ?")
                ->execute([
                    (string)$s['paso'], (string)$s['estado'],
                    json_encode($s['datos'], JSON_UNESCAPED_UNICODE),
                    json_encode(array_values($chat), JSON_UNESCAPED_UNICODE),
                    !empty($s['datos']['negocio_id']) ? (int)$s['datos']['negocio_id'] : (isset($s['negocio_id']) ? (int)$s['negocio_id'] : null),
                    date('Y-m-d H:i:s'), date('Y-m-d H:i:s'), (int)$s['id'],
                ]);
        } catch (Throwable $e) {
            error_log('tienda_ia_guardar: ' . $e->getMessage());
        }
    }
}

if (!function_exists('tienda_ia_empezar_de_cero')) {
    /** Deja la conversación en curso a un lado y empieza una limpia (botón «empezar de nuevo»). */
    function tienda_ia_empezar_de_cero($usuario_id, $modo = 'nueva') {
        if (!tienda_ia_tablas_ok()) return;
        try {
            db()->prepare("UPDATE " . TIENDA_IA_TABLA . " SET estado = 'abandonada', actualizado_en = ?
                WHERE usuario_id = ? AND estado = 'en_curso'")
                ->execute([date('Y-m-d H:i:s'), (int)$usuario_id]);
        } catch (Throwable $e) { /* da igual */ }
    }
}

/* =====================================================================================
 * 5) LOS PASOS (el orden lo manda el servidor)
 * ===================================================================================== */

if (!function_exists('tienda_ia_cerrar_conversacion')) {
    /**
     * 🆕 CIERRA LA CONVERSACIÓN EN CURSO DE ESTA PERSONA (2026-09-16, orden del jefe: *«un usuario
     * puede tener varias tiendas… un mismo usuario puede tener varios negocios»*).
     *
     * Es la pieza que faltaba: al terminar una tienda la conversación quedaba `en_curso` en su paso
     * final y **volvía a salir la misma tarjeta**, así que no había forma de empezar OTRA. Al cerrarla
     * (`abandonada`), `tienda_ia_actual()` ya no la ve y la siguiente visita abre una limpia.
     *
     * ⚠️ Solo se tocan las conversaciones de `modo = 'nueva'` que estén **en curso** y sean de ESTA
     * persona (su `usuario_id` o su hash de visitante): nunca la de otro, y nunca una ya publicada
     * (el registro del gasto y el embudo del súper admin no se pierden: la fila se queda, solo cambia
     * de estado).
     *
     * @return int cuántas conversaciones se cerraron
     */
    function tienda_ia_cerrar_conversacion($usuario_id, $visitante = '', $estado = 'abandonada') {
        if (!tienda_ia_tablas_ok()) return 0;
        $usuario_id = (int)$usuario_id;
        $visitante  = preg_replace('/[^a-f0-9]/', '', (string)$visitante);
        if (!in_array($estado, ['abandonada', 'publicada'], true)) $estado = 'abandonada';
        try {
            if ($usuario_id > 0) {
                $s = db()->prepare("UPDATE " . TIENDA_IA_TABLA . " SET estado = ?, actualizado_en = ?
                    WHERE usuario_id = ? AND modo = 'nueva' AND estado = 'en_curso'");
                $s->execute([$estado, date('Y-m-d H:i:s'), $usuario_id]);
            } elseif ($visitante !== '') {
                $s = db()->prepare("UPDATE " . TIENDA_IA_TABLA . " SET estado = ?, actualizado_en = ?
                    WHERE usuario_id = 0 AND visitante = ? AND modo = 'nueva' AND estado = 'en_curso'");
                $s->execute([$estado, date('Y-m-d H:i:s'), $visitante]);
            } else {
                return 0;
            }
            return (int)$s->rowCount();
        } catch (Throwable $e) {
            error_log('tienda_ia_cerrar_conversacion: ' . $e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('tienda_ia_nueva_tienda')) {
    /**
     * 🆕 «CREAR OTRA TIENDA» (2026-09-16): cierra la conversación que estaba abierta y devuelve una
     * NUEVA, limpia y ya puesta en el paso de las fotos (`fotos`), que es donde empieza toda tienda.
     *
     * Se salta el `arranque` a propósito: el dueño ya dijo lo que quiere («otra tienda»), así que
     * preguntarle otra vez «¿qué hacemos hoy?» sería hacerle perder un toque (Regla de Oro n.º 1:
     * UX predictiva). Tampoco se toca NADA de lo que ya publicó: la tienda anterior queda como está
     * (sus fotos, sus productos y su enlace siguen igual) y esta nace como **otro negocio** suyo
     * (`tienda_ia_publicar()` crea una tienda nueva cuando el nombre no es el de una que ya tiene).
     *
     * @return array|null la conversación nueva · ['limite' => true] si ya creó sus tiendas del día · null si falló
     */
    function tienda_ia_nueva_tienda($usuario_id, $visitante = '') {
        tienda_ia_cerrar_conversacion($usuario_id, $visitante);
        $s = tienda_ia_actual($usuario_id, 'nueva', $visitante, true);
        if (!$s) return null;
        if (!empty($s['limite'])) return $s;                 // el tope del día lo cuenta la puerta

        $d = tienda_ia_datos_vacios();
        $d['modo']  = 'nueva';
        $d['flujo'] = 'fotos';
        $s['datos'] = $d;
        // 📍🆕 El flujo arranca pidiendo la UBICACIÓN (orden del jefe, 2026-09-20); las fotos van después.
        $s['paso']  = 'ubicacion';

        // Saludo propio: que quede claro que lo anterior NO se toca y que este es otro negocio.
        $mias = tienda_ia_mis_tiendas((int)$usuario_id);
        $s['chat'] = [[
            'rol'   => 'bot',
            'texto' => "🆕 **¡Vamos con tu nueva tienda!**\n"
                     . (count($mias) > 0
                        ? "Lo que ya publicaste (" . (count($mias) === 1 ? 'tu tienda' : 'tus ' . count($mias) . ' tiendas') . ") queda como está: esta es **otra tienda**, de otro negocio.\n"
                        : "")
                     . "Empecemos igual que la primera 👇",
        ]];
        $g = tienda_ia_guion('ubicacion', $d);
        $s['chat'][] = ['rol' => 'bot', 'texto' => (string)$g['texto']];
        tienda_ia_guardar($s);
        return $s;
    }
}

if (!function_exists('tienda_ia_pasos')) {
    /**
     * Los pasos y su número (para la barrita).
     * 🔴 2026-09-15: se retiraron `resumen` (la publicación es AUTOMÁTICA) y `mas_productos`
     * (el flujo termina con la felicitación y el enlace); se añadió `rubro_mas` (los rubros extra).
     */
    function tienda_ia_pasos() {
        return [
            'arranque'        => ['n' => 0,  'titulo' => 'Empezar'],
            // 📍🆕 LO PRIMERO ES LA UBICACIÓN (orden del jefe, 2026-09-20): *«cuando carga, lo primero
            // que va a aparecer es un botón pidiendo ubicación; eso es lo primero… la ubicación como
            // primer dato es vital»*. Todo el flujo corre un número: fotos pasa a 2, tipo a 3, etc.
            'ubicacion'       => ['n' => 1,  'titulo' => 'Tu ubicación'],
            // 📷 EL ARRANQUE CON FOTOS (2026-09-15): las 8 fotos, ahora DESPUÉS de la ubicación.
            'fotos'           => ['n' => 2,  'titulo' => 'Tus fotos'],
            // 🏪 El tipo de negocio y su rama (dirección · horario · distritos de entrega).
            'tipo'            => ['n' => 3,  'titulo' => 'Tu tipo de negocio'],
            'direccion'       => ['n' => 4,  'titulo' => 'Tu dirección'],
            'horario'         => ['n' => 4,  'titulo' => 'Tu horario'],
            'entregas'        => ['n' => 4,  'titulo' => 'Tus distritos de entrega'],
            'distrito'        => ['n' => 5,  'titulo' => 'Tu zona'],
            'nombre_ok'       => ['n' => 6,  'titulo' => 'El nombre'],
            'rubro_ok'        => ['n' => 7,  'titulo' => 'El rubro'],
            'rubro_mas'       => ['n' => 8,  'titulo' => 'Otros rubros'],
            'editar'          => ['n' => 9,  'titulo' => 'Editar algo'],
            'producto_elegir' => ['n' => 10, 'titulo' => 'Tus productos'],
            'producto_editar' => ['n' => 10, 'titulo' => 'Editar el producto'],
            'producto_editar_foto'  => ['n' => 10, 'titulo' => 'La foto del producto'],
            'producto_editar_texto' => ['n' => 10, 'titulo' => 'La descripción'],
            'portada'         => ['n' => 10, 'titulo' => 'Tu portada'],
            'whatsapp'        => ['n' => 11, 'titulo' => 'Tu WhatsApp'],
            'tel_leido'       => ['n' => 11, 'titulo' => 'El teléfono del letrero'],
            'tel_ocupado'     => ['n' => 11, 'titulo' => 'Ese número'],
            'tienda'          => ['n' => 0,  'titulo' => 'Tu tienda'],
            'nombre'          => ['n' => 5,  'titulo' => 'El nombre'],            // flujo viejo (y respaldo)
            'trato'           => ['n' => 5,  'titulo' => 'De qué trata'],         // flujo viejo
            'rubro'           => ['n' => 6,  'titulo' => 'El rubro principal'],   // flujo viejo (y respaldo)
            'fotos_tienda'    => ['n' => 1,  'titulo' => 'Las fotos'],            // flujo viejo (y al editar)
            'vendedor'        => ['n' => 3,  'titulo' => 'Cómo vendes'],
            'redes'           => ['n' => 11, 'titulo' => 'Tus redes'],
            'resumen'         => ['n' => 9,  'titulo' => 'Publicar mi tienda'],   // paso RETIRADO (se sigue atendiendo)
            'publicado'       => ['n' => 12, 'titulo' => '¡Tienda publicada!'],
            'producto_nombre' => ['n' => 12, 'titulo' => 'Tu producto'],
            'producto_fotos'  => ['n' => 13, 'titulo' => 'Fotos del producto'],
            'producto_precio' => ['n' => 14, 'titulo' => 'El precio'],
            'producto_listo'  => ['n' => 15, 'titulo' => '¡Producto en línea!'],
            // 📷 La tanda de fotos de productos, clasificada por la IA (2026-09-16).
            'productos_lote'  => ['n' => 16, 'titulo' => 'Tus productos'],
            'mas_productos'   => ['n' => 16, 'titulo' => 'Más productos'],        // paso RETIRADO (se sigue atendiendo)
            'fin'             => ['n' => 13, 'titulo' => 'Todo listo'],
        ];
    }
}

if (!function_exists('tienda_ia_progreso')) {
    /** Cuánto lleva (0 a 1) para la barrita de arriba. */
    function tienda_ia_progreso($paso, array $datos) {
        $pasos = tienda_ia_pasos();
        $n     = isset($pasos[$paso]) ? (int)$pasos[$paso]['n'] : 0;
        // Cada producto creado después de publicar la tienda suma un tramo del camino.
        if (in_array($paso, ['producto_nombre', 'producto_fotos', 'producto_precio', 'producto_listo', 'mas_productos', 'fin'], true)) {
            $n += min((int)($datos['productos_ia'] ?? 0), 2) * 1.5;
        }
        return max(0.03, min(1, $n / 12));
    }
}

/* =====================================================================================
 * 6) EL GUION LOCAL (lo que dice el asistente; la IA solo lo adereza)
 * ===================================================================================== */

if (!function_exists('tienda_ia_palabras')) {
    function tienda_ia_palabras($texto) {
        $t = trim(preg_replace('/\s+/u', ' ', (string)$texto));
        return $t === '' ? 0 : count(preg_split('/\s+/u', $t));
    }
}

if (!function_exists('tienda_ia_sin_tildes')) {
    function tienda_ia_sin_tildes($texto) {
        // Se usa la MISMA del sitio (`sin_tildes_texto()`, helpers.php): una sola forma de
        // comparar como escribe la gente, sin diccionarios paralelos.
        return sin_tildes_texto($texto);
    }
}

if (!function_exists('tienda_ia_limpiar')) {
    /** Deja el texto como para guardarlo: sin sobras, sin comillas raras, sin saltos. */
    function tienda_ia_limpiar($texto, $max = 120) {
        $t = (string)$texto;
        $t = preg_replace('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}]/u', '', $t); // emojis fuera
        $t = str_replace(["\r", "\n", "\t"], ' ', $t);
        $t = preg_replace('/\s+/u', ' ', $t);
        // ⚠️ Los bordes se quitan con regex y /u, NO con la lista multibyte de trim(): «“”«»» se leen
        // como bytes, el 0xC2 compartido se come media comilla y el texto queda invalidado (y con él
        // se pierde el dato: nos borró el nombre del letrero el 2026-09-15).
        $t = preg_replace('/^[\s"\'\x{201C}\x{201D}\x{00AB}\x{00BB},.;:\-\x{2013}\x{2014}]+/u', '', (string)$t);
        $t = preg_replace('/[\s"\'\x{201C}\x{201D}\x{00AB}\x{00BB},.;:\-\x{2013}\x{2014}]+$/u', '', (string)$t);
        $t = tienda_ia_utf8_sano((string)$t);
        return mb_substr(trim($t), 0, $max);
    }
}

if (!function_exists('tienda_ia_como_llamarlo')) {
    /**
     * Cómo saludar al dueño por su nombre.
     * ⚠️ OJO (lo cazó la prueba 8 del 2026-09-15): las cuentas que crea El maestro guardan como
     * `nombre` el de **LA TIENDA**, así que el saludo salía «¡Hola, Combinado!» cuando la tienda era
     * «Combinado Doña Lucha». Si el nombre es el de una de sus tiendas (o es muy largo), se saluda
     * sin nombre: es más natural que inventarle un apodo.
     */
    function tienda_ia_como_llamarlo($usuario, array $tiendas = []) {
        $nombre = trim((string)($usuario['nombre'] ?? ''));
        if ($nombre === '') return '';
        $sin = tienda_ia_sin_tildes($nombre);
        if ($sin === '') return '';
        foreach ($tiendas as $t) {
            $tn = tienda_ia_sin_tildes((string)($t['texto'] ?? ''));
            if ($tn !== '' && ($tn === $sin || mb_strpos($tn, $sin) !== false || mb_strpos($sin, $tn) !== false)) return '';
        }
        $partes = preg_split('/\s+/u', $nombre);
        if (!is_array($partes) || count($partes) > 2) return '';
        return (string)$partes[0];
    }
}

if (!function_exists('tienda_ia_arranque_extra')) {
    /**
     * 🚪 QUIÉN LLEGA (para el paso `arranque`): ¿tiene sesión?, ¿cómo se llama? y ¿qué tiendas tiene?
     * Es lo que hace que el asistente sepa **diferenciar a un usuario logueado de uno que no lo está**
     * (orden del jefe, 2026-09-15). Si no hay tiendas, la invitación es a crear la primera.
     */
    function tienda_ia_arranque_extra($usuario_id) {
        $u = usuario_actual();
        $tiendas = ((int)$usuario_id > 0) ? tienda_ia_tiendas_de((int)$usuario_id) : [];
        return [
            'logueado' => !empty($u),
            'primero'  => $u ? tienda_ia_como_llamarlo($u, $tiendas) : '',
            'tiendas'  => $tiendas,
        ];
    }
}

if (!function_exists('tienda_ia_saludo')) {
    /**
     * El primer mensaje de una conversación nueva. En el paso `arranque` **no hay saludo aparte**:
     * ese paso ya saluda y hace la pregunta en el mismo mensaje (el jefe pidió minimalismo).
     */
    function tienda_ia_saludo(array $s, array $tiendas = []) {
        if ((string)$s['paso'] === 'arranque') return '';
        $quien = tienda_ia_como_llamarlo(usuario_actual(), $tiendas);
        $hola  = ($quien !== '') ? "¡Hola, {$quien}! " : '¡Hola! ';
        if ((string)$s['modo'] === 'producto') {
            return $hola . TIENDA_IA_EMOJI . " Soy **" . TIENDA_IA_NOMBRE . "**. Vamos con tu producto 🚀";
        }
        return $hola . TIENDA_IA_EMOJI . " Soy **" . TIENDA_IA_NOMBRE . "**. Te armo la tienda en 5 minutos 🚀";
    }
}

if (!function_exists('tienda_ia_rubros_para_sumar')) {
    /**
     * 🏷️ Los rubros o categorías que se le ofrecen para SUMAR al principal (orden del jefe,
     * 2026-09-15: *«si deseas puedes agregar también otros rubros hasta un máximo de cuatro»*).
     * Primero los que ya había propuesto la IA en el paso del principal (así el dueño ve, por
     * ejemplo, «Materiales de construcción» y «Ferretería» juntos) y después los candidatos por
     * palabras clave. Nunca repite los que ya tiene.
     */
    function tienda_ia_rubros_para_sumar(array $d) {
        $tengo = [(int)$d['rubro_id']];
        foreach ((array)($d['rubros_extra'] ?? []) as $r) $tengo[] = (int)($r['id'] ?? 0);
        $out = [];
        $meter = function ($id, $nombre, $icono) use (&$out, $tengo) {
            $id = (int)$id;
            if ($id <= 0 || in_array($id, $tengo, true)) return;
            foreach ($out as $o) { if ((int)$o['valor'] === $id) return; }
            $out[] = ['texto' => ($icono !== '' ? $icono . ' ' : '') . $nombre, 'valor' => (string)$id];
        };
        // 1) Los que la IA ya había propuesto para el principal (y no eligió).
        foreach ((array)($d['rubro_opciones'] ?? []) as $o) {
            $id = (int)($o['valor'] ?? 0);
            if ($id > 0) $meter($id, (string)preg_replace('/^[^\p{L}]+/u', '', (string)$o['texto']), '');
        }
        // 1-bis) 🤝 LOS RUBROS QUE COMPLEMENTAN AL SUYO (el mapa de afinidades del sitio): a una
        // cevichería se le ofrecen bebidas, pescaderías… y no rubros que no le dicen nada. Antes, en el
        // flujo nuevo (donde el dueño nunca escribe un relato), salían los rubros más usados del sitio y
        // el menú quedaba raro («¿Melamina? ¿Servicios de impresión 3D?»).
        $id_principal = (int)($d['rubro_id'] ?? 0);
        if ($id_principal > 0 && function_exists('categorias_afinidad')) {
            try {
                foreach ((array)categorias_afinidad($id_principal) as $af) {
                    $meter((int)($af['id'] ?? 0), (string)($af['nombre'] ?? ''), (string)($af['icono'] ?? ''));
                    if (count($out) >= 4) break;
                }
            } catch (Throwable $e) {}
        }
        // 2) Los candidatos por lo que contó y lo que se vio en sus fotos.
        $texto = (string)$d['nombre'] . ' ' . (string)$d['trato'];
        foreach ((array)($d['visto'] ?? []) as $v) $texto .= ' ' . (string)$v;
        foreach (tienda_ia_rubros_candidatos($texto, 12) as $c) $meter($c['id'], (string)$c['nombre'], (string)$c['icono']);
        return array_slice($out, 0, 6);
    }
}

if (!function_exists('tienda_ia_productos_publicados')) {
    /**
     * 🛍️ LOS PRODUCTOS QUE YA ESTÁN EN LA TIENDA (2026-09-16). Es lo que hace que el copy **mejore
     * conforme se cargan más productos**: cada vez que se escribe la descripción se le pasan a la IA
     * los productos de verdad (con su precio, si lo tienen) y los nombra.
     * @return array [ ['titulo' => '…', 'precio' => 12.0], … ]
     */
    function tienda_ia_productos_publicados($negocio_id, $limite = 24) {
        $negocio_id = (int)$negocio_id;
        if ($negocio_id <= 0) return [];
        try {
            // ⚠️ `directorio_servicios` NO tiene columna `orden` (comprobado el 2026-09-16 con
            // `__tia_cols.php`): pedirla reventaba la consulta y —como el catch era callado— el copy se
            // quedaba sin la lista de productos y el dueño no veía la mejora. El orden es por `id`.
            $st = db()->prepare("SELECT titulo, precio FROM directorio_servicios
                                  WHERE negocio_id = ? AND activo = 1 ORDER BY id ASC LIMIT " . max(1, (int)$limite));
            $st->execute([$negocio_id]);
            $out = [];
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $f) {
                $t = trim((string)$f['titulo']);
                if ($t === '') continue;
                $out[] = ['titulo' => $t, 'precio' => (float)$f['precio']];
            }
            return $out;
        } catch (Throwable $e) {
            error_log('tienda_ia_productos_publicados: ' . $e->getMessage());
            return [];
        }
    }
}

if (!function_exists('tienda_ia_copy_limpiar')) {
    /**
     * 🧼 Deja el copy listo para guardar: solo las etiquetas permitidas (`h3 p strong b em i ul ol li br`),
     * los `&` sueltos arreglados (el HTML tiene que quedar íntegro: si no, la ficha se ve rota), sin
     * líneas vacías de más y sin los marcadores raros con que el modelo a veces envuelve el HTML.
     */
    function tienda_ia_copy_limpiar($html) {
        $h = trim((string)$html);
        if ($h === '') return '';
        // El modelo a veces envuelve todo en ```html … ``` o escribe «HTML:» delante.
        $h = preg_replace('/^\s*```[a-z]*\s*/i', '', $h);
        $h = preg_replace('/\s*```\s*$/', '', $h);
        $h = preg_replace('/^\s*(HTML|COPY|DESCRIPCI[OÓ]N)\s*:\s*/i', '', $h);
        $h = limpiar_html_descripcion($h);          // solo las etiquetas permitidas
        // `&` sueltos → `&amp;` (menos los que ya son una entidad).
        $h = preg_replace('/&(?!(?:[a-zA-Z][a-zA-Z0-9]{1,7}|#\d{2,5}|#x[0-9a-fA-F]{2,4});)/', '&amp;', $h);
        $h = preg_replace('/[ \t]+\n/', "\n", $h);
        $h = preg_replace('/\n{3,}/', "\n\n", $h);
        return trim($h);
    }
}

if (!function_exists('tienda_ia_copy_ok')) {
    /**
     * ✅ ¿ESTE COPY SIRVE PARA LA FICHA? Es el mismo listón que exige el publicador de los amigos
     * (≥300 caracteres, ≥3 `<h3>`, la banda `cz-cta-final` y sus botones): así el copy del asistente
     * sale con los colores y los botones de llamada a la acción, no un texto plano flojo.
     * Devuelve '' si está bien, o el motivo del rechazo.
     */
    function tienda_ia_copy_ok($html) {
        $h = (string)$html;
        if (mb_strlen(trim(strip_tags($h))) < 300)                  return 'corto';
        if (substr_count($h, '<h3') < 3)                            return 'sin bloques';
        if (strpos($h, 'cz-cta-final') === false)                   return 'sin cierre';
        if (substr_count($h, 'cz-wa') < 2)                          return 'sin botones de WhatsApp';
        // Un teléfono escrito a mano en el texto sobra: el botón ya lo pone.
        if (preg_match('/(?:\+?51\s?)?9\d{2}[\s\-]?\d{3}[\s\-]?\d{3}/', strip_tags($h))) return 'telefono escrito';
        return '';
    }
}

if (!function_exists('tienda_ia_ia_descripcion')) {
    /**
     * 📝 EL COPY DE LA DESCRIPCIÓN (orden del jefe, 2026-09-15: *«aquí ya debe la IA aplicar el
     * copyright, sobre todo en la descripción de "de qué trata"… el copyright debe ser en vivo,
     * ahí creado»*).
     *
     * 🔴 **REESCRITO EL 2026-09-16** (queja del jefe con una tienda en la mano: *«quedó muy floja su
     * descripción»*). Antes pedía «2 a 4 frases, sin emojis y sin listas» → salía un párrafo plano, sin
     * colores y sin ningún botón. Ahora escribe el **copy de verdad**, con el **kit de colores del
     * sitio** (`cz-tit`, `cz-caja`, `cz-lista`, `cz-precio`, `cz-cta`, `cz-cta-final`) y **repite los
     * botones de llamada a la acción** (`cz-wa` de WhatsApp con su mensaje y `cz-tel` para llamar), que
     * es exactamente lo que pidió el jefe: *«repetir los botones de llamada a la acción en el
     * copyright»*. Y recibe **los productos que ya tiene la tienda**, así el copy va **mejorando**
     * conforme se cargan más.
     *
     * Devuelve el HTML del copy (ya limpio y con `&` arreglados), o '' si la IA no pudo o el copy no
     * pasó el listón (entonces quien llama guarda el texto de respaldo: nunca una ficha rota).
     */
    function tienda_ia_ia_descripcion(array $d, array $op = []) {
        // El interruptor del jefe: con el copy rico apagado, la ficha se queda con lo que escribió el
        // dueño (el respaldo de siempre) y no se gasta ninguna llamada.
        if (defined('TIENDA_IA_COPY_RICO') && !TIENDA_IA_COPY_RICO) return '';
        $visto = [];
        foreach ((array)($d['visto'] ?? []) as $v) { if (trim((string)$v) !== '') $visto[] = '- ' . trim((string)$v); }
        // 📷 Los productos que la IA reconoció en las fotos del arranque valen como contexto cuando
        // el dueño no contó nada (flujo nuevo: nunca escribió un relato).
        $nombres = [];
        foreach ((array)($d['productos_vistos'] ?? []) as $p) {
            // ⚠️ Desde el 2026-09-15 noche cada uno viene como ['titulo'=>…,'foto'=>n] (para poder
            // ponerle su foto al producto); antes era una cadena suelta. Se aceptan las dos formas.
            $p = is_array($p) ? trim((string)($p['titulo'] ?? '')) : trim((string)$p);
            if ($p !== '' && !in_array($p, $nombres, true)) $nombres[] = $p;
        }
        foreach ((array)($d['productos'] ?? []) as $p) {
            $t = trim((string)($p['titulo'] ?? ''));
            if ($t !== '' && empty($p['borrado']) && !in_array($t, $nombres, true)) $nombres[] = $t;
        }
        // 🛍️ Los productos PUBLICADOS (los manda quien llama en `$op['productos']`): son los que hacen
        // que el copy se vaya mejorando con cada tanda que el dueño carga.
        $con_precio = [];
        foreach ((array)($op['productos'] ?? []) as $p) {
            $t = is_array($p) ? trim((string)($p['titulo'] ?? '')) : trim((string)$p);
            if ($t === '') continue;
            $pr = is_array($p) ? (float)($p['precio'] ?? 0) : 0.0;
            if (!in_array($t, $nombres, true)) $nombres[] = $t;
            if ($pr > 0) $con_precio[$t] = $pr;
        }
        $rubros = [];
        if ((string)$d['rubro_nombre'] !== '') $rubros[] = (string)$d['rubro_nombre'];
        foreach ((array)($d['rubros_extra'] ?? []) as $r) {
            $n = trim((string)($r['nombre'] ?? ''));
            if ($n !== '') $rubros[] = $n;
        }
        $vende = ['fisica' => 'tiene su local y ahí atiende al cliente',
                  'ambulante' => 'vende por las calles (ambulante)',
                  'domicilio' => 'lo lleva a la casa del cliente'];
        $zona = (string)$d['distrito_nombre'];
        if (!empty($d['distrito_todas'])) $zona .= ' (y atiende en toda la provincia del Santa: Chimbote, Nuevo Chimbote, Coishco y Santa)';

        $lista = [];
        foreach (array_slice($nombres, 0, 12) as $n) {
            $lista[] = (isset($con_precio[$n]) ? $n . ' (S/ ' . number_format($con_precio[$n], 2) : $n)
                     . (isset($con_precio[$n]) ? ')' : '');
        }

        $peticion = "Eres el redactor publicitario de DeChimbote.com (Chimbote, Perú). Escribe la DESCRIPCIÓN "
                  . "de la ficha de esta tienda: tiene que verse bien, dar ganas de comprar y llevar al cliente "
                  . "a escribir por WhatsApp o a llamar.\n\n"
                  . "DATOS (lo ÚNICO que puedes usar)\n"
                  . "Tienda: «" . (string)$d['nombre'] . "»\n"
                  . ($rubros ? 'Rubros: ' . implode(', ', $rubros) . "\n" : '')
                  . 'Zona: ' . $zona . "\n"
                  . 'Cómo atiende: ' . ($vende[(string)$d['vendedor']] ?? 'atiende a sus clientes') . "\n"
                  . (!empty($d['horario']) ? 'Horario: ' . (string)$d['horario'] . "\n" : '')
                  . ($lista ? 'Lo que vende (de sus fotos y de sus productos ya cargados): ' . implode(', ', $lista) . "\n" : '')
                  . (trim((string)$d['trato']) !== '' ? 'Lo que contó el dueño: «' . mb_substr((string)$d['trato'], 0, 400) . "»\n" : '')
                  . ($visto ? "Lo que se ve en sus FOTOS:\n" . implode("\n", array_slice($visto, 0, 5)) . "\n" : '')
                  . (!empty($op['imagenes']) ? "⚠️ TE MANDO SUS FOTOS: míralas y escribe lo que SE VE de verdad (el local, la mercadería, las máquinas). Eso vale más que cualquier suposición.\n" : '')
                  . "\nCÓMO SE ESCRIBE (obligatorio)\n"
                  . "- En español de Perú, cálido y claro, hablándole al cliente («Encuentras…», «Te atendemos…»).\n"
                  . "- HTML simple: SOLO estas etiquetas con sus clases: h3, p, ul, ol, li, strong, b, em, i, br. "
                  . "PROHIBIDO <a>, <span>, <div>, <img>, <table>, <script>: se caen y rompen la ficha.\n"
                  . "- 6 a 12 bloques. MÍNIMO 3 títulos <h3>. NO expliques nada fuera del HTML: devuelve SOLO el HTML.\n"
                  . "- Usa &amp; en vez de & suelto.\n"
                  . "- NO INVENTES NADA: ni precios, ni años, ni marcas, ni modelos, ni direcciones exactas, ni "
                  . "teléfonos, ni promesas (envíos gratis, garantías, tiempos) que no estén en los datos de arriba. "
                  . "Si un dato no está, no lo escribas.\n"
                  . "- NUNCA escribas números de teléfono en el texto (los botones ya los ponen solos).\n"
                  . "- Los emojis: uno al inicio de cada título y en cada botón, con medida.\n\n"
                  . "ESTRUCTURA EXACTA (respeta las clases: son los COLORES y los BOTONES del sitio)\n"
                  . "<h3 class=\"cz-tit\">🏪 <NOMBRE> — <lo principal que vende> en <zona></h3>\n"
                  . "<p>Gancho de 2 o 3 frases: qué encuentra el cliente y por qué le conviene. Cierra con "
                  . "<strong class=\"cz-ok\">un beneficio real</strong>.</p>\n"
                  . "<p class=\"cz-caja\">🤝 <strong>Así te atendemos:</strong> cómo atienden de verdad (local, "
                  . "se lo llevan a su casa, horario) tomado de los datos.</p>\n"
                  . "<p class=\"cz-btn cz-wa\" data-msg=\"Hola, la vi en {URL} y quiero consultarle:\">📲 Escribirle por WhatsApp</p>\n"
                  . "<h3 class=\"cz-sub\">🛍️ Lo que encuentras</h3>\n"
                  . "<ul class=\"cz-lista\"><li><strong>Producto</strong> — cómo es o para qué le sirve al cliente</li>…</ul> "
                  . "(4 a 7 cosas, las de los datos; nunca inventes)\n"
                  . ($con_precio ? "<p class=\"cz-precio\">💵 Desde S/ <el más barato> — te confirmamos el precio al toque</p>\n" : '')
                  . "<h3 class=\"cz-sub\">📍 Dónde y cuándo</h3>\n"
                  . "<p>La zona y el horario, en <strong>negrita</strong>.</p>\n"
                  . "<p class=\"cz-cta\">👉 Pide lo tuyo ahora y te atendemos al toque.</p>\n"
                  . "<p class=\"cz-btn cz-wa\" data-msg=\"Hola, quiero saber el precio y la disponibilidad de lo que vi en {URL}\">💬 Consultar precio y disponibilidad</p>\n"
                  . "<p class=\"cz-btn cz-tel\">📞 Llamar y preguntar</p>\n"
                  . "<p class=\"cz-cta-final\">🏪 <strong><NOMBRE> — <frase corta de cierre></strong></p>\n\n"
                  . "⚠️ Los textos de los botones («Escribirle por WhatsApp», «Consultar precio y disponibilidad», "
                  . "«Llamar y preguntar») se dejan TAL CUAL: el sitio los convierte en los botones verdes y de "
                  . "llamada. Y `{URL}` se deja tal cual dentro de data-msg (el sitio lo cambia por el enlace).";
        $r = tienda_ia_llamar($peticion, array_merge(['paso' => 'descripcion', 'max_tokens' => 1400], $op));
        if (empty($r['ok'])) return '';
        $html = tienda_ia_copy_limpiar((string)$r['texto']);
        if (tienda_ia_copy_ok($html) !== '') return '';       // no pasó el listón: manda el respaldo
        return $html;
    }
}

if (!function_exists('tienda_ia_ia_descripcion_producto')) {
    /**
     * 📝 EL COPY DE UN PRODUCTO, mirando su foto (2026-09-15, pedido del jefe: *«tiene muy mal
     * copyright… mejóralo»*). Antes el producto se creaba **sin descripción**; ahora la IA escribe
     * 1 o 2 frases con lo que SE VE en la foto (color, tamaño, cómo se entrega) y lo que vende la
     * tienda. Si la IA falla, se guarda vacío como antes: el producto se crea igual.
     */
    function tienda_ia_ia_descripcion_producto(array $d, $titulo, array $fotos = [], array $op = [], $texto_dueno = '') {
        $titulo = trim((string)$titulo);
        if ($titulo === '') return '';
        $rubros = [];
        if ((string)$d['rubro_nombre'] !== '') $rubros[] = (string)$d['rubro_nombre'];
        foreach ((array)($d['rubros_extra'] ?? []) as $r) {
            $n = trim((string)($r['nombre'] ?? ''));
            if ($n !== '') $rubros[] = $n;
        }
        $rels = [];
        foreach ($fotos as $f) { if (is_string($f) && $f !== '') $rels[] = $f; }
        $peticion = "Escribe la descripción de UN PRODUCTO para la tienda «" . (string)$d['nombre'] . "» en DeChimbote.com.\n"
                  . "Producto: «{$titulo}»\n"
                  . ($rubros ? 'Rubros de la tienda: ' . implode(', ', $rubros) . "\n" : '')
                  . (trim((string)$d['trato']) !== '' ? 'Lo que vende la tienda: ' . mb_substr((string)$d['trato'], 0, 400) . "\n" : '')
                  . ($rels ? "Te mando la FOTO del producto: mírala y describe lo que se VE de verdad.\n" : '')
                  // ✍️🆕 Cuando el DUEÑO ya escribió algo, esto es copywriting: se respeta lo que dijo
                  // (sin inventar nada nuevo) y se cuenta bonito (orden del jefe: *«cuando te dé texto tú
                  // tienes que aplicarle copyright»*).
                  . ($texto_dueno !== '' ? "Lo que él escribió sobre el producto: «" . mb_substr($texto_dueno, 0, 400) . "»\n"
                        . "Respeta TODO lo que él dice (no agregues cosas nuevas) y cuéntalo mejor.\n" : '')
                  . "\nEscribe 1 o 2 frases cortas, claras y útiles para el cliente, en español de Perú "
                  . "(qué es, cómo se ve o cómo se entrega). NO inventes precios, medidas exactas, marcas ni promesas "
                  . "que no estén en la foto. Sin títulos, sin listas, sin emojis y sin comillas.";
        $r = tienda_ia_llamar($peticion, array_merge([
            'paso'       => 'descripcion_producto',
            'imagenes'   => $rels,
            'max_tokens' => 220,
        ], $op));
        if (empty($r['ok'])) return '';
        $t = tienda_ia_limpiar_largo((string)$r['texto'], 500);
        return trim(str_replace(['"', '«', '»', '*', '#'], '', $t));
    }
}

if (!function_exists('tienda_ia_descripcion_final')) {
    /**
     * La descripción que se guarda en la tienda: **el copy que escribe la IA** (con colores y botones)
     * o —si la IA no pudo o el copy no pasó el listón— lo que escribió el dueño.
     * Y si eligió «Todas las anteriores», la nota de la zona (acordado con el jefe el 2026-09-15):
     * esa nota **solo** se añade al texto plano, porque el copy ya la cuenta en su bloque «Dónde y cuándo».
     */
    function tienda_ia_descripcion_final(array $d, array $op = []) {
        $desc = trim((string)($d['descripcion_ia'] ?? ''));
        // 📷🆕 En el flujo nuevo (las fotos primero) el dueño NUNCA contó nada: su «relato» es lo que
        // la IA VIO en sus fotos. Sin esta condición la tienda se publicaría sin descripción.
        // 🛍️ 2026-09-16: **los productos también son contexto** (una tienda que solo tiene su catálogo
        // —o una que ya está publicada y se le rehace el copy— tiene que salir con descripción igual).
        $hay_contexto = (trim((string)$d['trato']) !== '') || !empty($d['visto'])
                     || !empty($d['productos_vistos']) || !empty($op['productos']);
        if ($desc === '' && $hay_contexto) {
            $desc = trim((string)$d['trato']);   // el respaldo: lo que él escribió (o vacío)
            if (defined('TIENDA_IA_DESCRIPCION_IA') && TIENDA_IA_DESCRIPCION_IA) {
                // 🛍️ Los productos que la tienda YA tiene entran en el copy: por eso el texto mejora
                // conforme el dueño carga mercadería (2026-09-16, pedido del jefe).
                if (empty($op['productos'])) {
                    $neg_id = (int)($d['publicado']['negocio_id'] ?? $d['negocio_id'] ?? 0);
                    if ($neg_id > 0) $op['productos'] = tienda_ia_productos_publicados($neg_id);
                }
                $ia = tienda_ia_ia_descripcion($d, $op);
                if (is_string($ia) && trim($ia) !== '') $desc = trim($ia);
            }
        }
        if (!empty($d['distrito_todas']) && strpos(ltrim($desc), '<') !== 0) {
            $desc = trim($desc . "\n\n" . TIENDA_IA_NOTA_TODA_LA_ZONA);
        }
        return mb_substr($desc, 0, 6000);
    }
}

if (!function_exists('tienda_ia_mejorar_descripcion')) {
    /**
     * 📝🛍️ EL COPY MEJORA CON CADA PRODUCTO (pedido del jefe, 2026-09-16, textual: *«debe ir mejorando
     * el copywriting (texto del negocio) según se vayan agregando más productos»*).
     *
     * Vuelve a escribir la descripción de la tienda —ahora con la lista de productos de verdad, con sus
     * precios si los tienen— y la guarda. Se llama **después de cada producto** (y al terminar una
     * tanda de fotos): el dueño ve el aviso *«📝 Le mejoré la descripción a tu tienda…»* y su ficha va
     * quedando cada vez mejor sin que él escriba nada.
     *
     * Nunca puede romper el flujo: si la IA falla o el copy no pasa el listón, se deja la descripción
     * que ya tenía la tienda.
     */
    function tienda_ia_mejorar_descripcion(array &$s, array $d, $empujar = null) {
        if (defined('TIENDA_IA_MEJORAR_COPY') && !TIENDA_IA_MEJORAR_COPY) return false;
        $neg = tienda_ia_negocio_del_dueño($s);
        if (!$neg) return false;
        $prods = tienda_ia_productos_publicados((int)$neg['id']);
        if (!$prods) return false;
        $copy = tienda_ia_ia_descripcion($d, [
            'sesion'    => (int)$s['id'],
            'usuario'   => (int)$s['usuario_id'],
            'productos' => $prods,
        ]);
        if (!is_string($copy) || trim($copy) === '') return false;
        try {
            db()->prepare("UPDATE directorio_negocios SET descripcion = ?, actualizado_en = ? WHERE id = ?")
                ->execute([mb_substr($copy, 0, 6000), date('Y-m-d H:i:s'), (int)$neg['id']]);
            require_once __DIR__ . '/fuzzy_cache.php';
            fuzzy_olvidar_cache();
        } catch (Throwable $e) {
            error_log('tienda_ia_mejorar_descripcion: ' . $e->getMessage());
            return false;
        }
        if (is_callable($empujar)) {
            $empujar("📝 **Le mejoré la descripción a tu tienda** con lo que llevas cargado 👌");
        }
        return true;
    }
}

if (!function_exists('tienda_ia_guardar_rubros_extra')) {
    /**
     * 🏷️ Guarda los rubros EXTRA de la tienda en `directorio_negocio_rubros` (la tabla que ya usa el
     * editor de tiendas): con eso la tienda sale también en esos rubros en el sitio, el buscador y
     * «cerca de mí». Si la tabla no existe, no pasa nada: la tienda queda con su rubro principal.
     * Devuelve cuántos guardó.
     */
    function tienda_ia_guardar_rubros_extra($negocio_id, array $d) {
        $id = (int)$negocio_id;
        if ($id <= 0) return 0;
        if (!function_exists('rubros_multi_ok') || !rubros_multi_ok()) return 0;
        $n = 0;
        try {
            $pdo = db();
            $pdo->prepare("DELETE FROM directorio_negocio_rubros WHERE negocio_id = ?")->execute([$id]);
            $orden = 0;
            foreach ((array)($d['rubros_extra'] ?? []) as $r) {
                $cid = (int)($r['id'] ?? 0);
                if ($cid <= 0 || $cid === (int)$d['rubro_id']) continue;
                $pdo->prepare("INSERT IGNORE INTO directorio_negocio_rubros (negocio_id, categoria_id, orden, creado_en)
                               VALUES (?,?,?,?)")
                    ->execute([$id, $cid, $orden++, date('Y-m-d H:i:s')]);
                $n++;
            }
            try { require_once __DIR__ . '/fuzzy_cache.php'; fuzzy_olvidar_cache(); } catch (Throwable $e) {}
        } catch (Throwable $e) {
            error_log('tienda_ia_guardar_rubros_extra: ' . $e->getMessage());
        }
        return $n;
    }
}

if (!function_exists('tienda_ia_copiar_foto')) {
    /**
     * 📷 Copia una foto del módulo a un nombre NUEVO (con sus versiones de 800 y 300 px).
     * Se usa cuando el dueño aprovecha una foto que ya subió para ponérsela a un producto: así las
     * dos fichas tienen su archivo propio y borrar una no rompe la otra.
     */
    function tienda_ia_copiar_foto($rel) {
        $rel = ltrim((string)$rel, '/');
        if ($rel === '' || strpos(basename($rel), 'ia_') !== 0) return '';
        try {
            $abs = img_ruta_fisica($rel);
            if (!is_file($abs)) return '';
            $nuevo = dirname($rel) . '/ia_' . date('Ymd') . '_' . bin2hex(random_bytes(6)) . '.' . pathinfo($rel, PATHINFO_EXTENSION);
            if (!@copy($abs, img_ruta_fisica($nuevo))) return '';
            foreach (array_keys(img_escalera()) as $ancho) {
                $v = function_exists('img_buscar_variante') ? img_buscar_variante($rel, $ancho) : null;
                if ($v === null) continue;
                $absV = img_ruta_fisica($v);
                if (is_file($absV)) @copy($absV, img_ruta_fisica(img_con_sufijo($nuevo, $ancho)));
            }
            return $nuevo;
        } catch (Throwable $e) { return ''; }
    }
}

if (!function_exists('tienda_ia_actualizar_publicada')) {
    /**
     * ✏️ Guarda en la tienda YA PUBLICADA lo que el dueño cambió después (`✏️ Editar algo`).
     * Es lo que hace posible que la publicación sea automática: como no hay paso de aprobación, la
     * corrección se aplica sobre la tienda que ya está en línea.
     */
    function tienda_ia_actualizar_publicada(array $s, array $d, $con_desc = true) {
        $neg = tienda_ia_negocio_del_dueño($s);
        if (!$neg) return ['ok' => false, 'error' => 'No encontré tu tienda.'];
        $id = (int)$neg['id'];
        try {
            $pdo = db();
            $campos = []; $par = [];
            $nombre = trim((string)$d['nombre']);
            if (mb_strlen($nombre) >= 2) { $campos[] = 'nombre = ?'; $par[] = $nombre; }
            if (!empty($d['rubro_id'])) { $campos[] = 'categoria_id = ?'; $par[] = (int)$d['rubro_id']; }
            if (!empty($d['distrito_id'])) { $campos[] = 'distrito_id = ?'; $par[] = (int)$d['distrito_id']; }
            $tel = preg_replace('/\D+/', '', (string)$d['whatsapp']);
            if (mb_strlen($tel) >= 6) { $campos[] = 'whatsapp = ?'; $par[] = $tel; $campos[] = 'telefono = ?'; $par[] = $tel; }
            if (in_array((string)$d['vendedor'], ['fisica', 'ambulante', 'domicilio', 'nacional', 'mayorista'], true)) {
                $campos[] = 'ubicacion_tipo = ?'; $par[] = (string)$d['vendedor'];
                $campos[] = 'delivery = ?';       $par[] = ((string)$d['vendedor'] === 'domicilio' ? 1 : 0);
                $campos[] = 'recojo = ?';         $par[] = ((string)$d['vendedor'] === 'ambulante' ? 1 : 0);
            }
            // 📍📘 Los campos de la página de registro que el flujo nuevo va completando en vivo.
            if (trim((string)($d['direccion'] ?? '')) !== '') { $campos[] = 'direccion = ?'; $par[] = mb_substr(trim((string)$d['direccion']), 0, 160); }
            foreach (['facebook', 'instagram', 'tiktok'] as $red) {
                if (trim((string)($d[$red] ?? '')) !== '') { $campos[] = $red . ' = ?'; $par[] = mb_substr(trim((string)$d[$red]), 0, 160); }
            }
            if (trim((string)$d['horario']) !== '') { $campos[] = 'horario = ?'; $par[] = mb_substr(trim((string)$d['horario']), 0, 60); }
            // 📝 El copy de la descripción se vuelve a escribir SOLO cuando toca (al estrenar y cuando
            // el dueño cambia «de qué trata»). En las respuestas del flujo nuevo que van actualizando
            // la tienda en silencio NO se rehace: sería una llamada de la IA por cada pregunta.
            if ($con_desc) {
                $desc = tienda_ia_descripcion_final($d);
                if ($desc !== '') { $campos[] = 'descripcion = ?'; $par[] = $desc; }
            }
            if ($campos) {
                $campos[] = 'actualizado_en = ?'; $par[] = date('Y-m-d H:i:s');
                $par[] = $id;
                $pdo->prepare("UPDATE directorio_negocios SET " . implode(', ', $campos) . " WHERE id = ?")->execute($par);
            }
            tienda_ia_guardar_rubros_extra($id, $d);
            // 📸 Si aprovechó fotos nuevas (o las cambió), se le añaden a la galería de su tienda.
            $ya = [];
            try {
                $st = $pdo->prepare("SELECT ruta FROM directorio_fotos WHERE negocio_id = ?");
                $st->execute([$id]);
                foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $r) $ya[(string)$r] = true;
            } catch (Throwable $e) {}
            $orden = count($ya);
            foreach ((array)$d['fotos'] as $rel) {
                if (isset($ya[(string)$rel])) continue;
                $pdo->prepare("INSERT INTO directorio_fotos (negocio_id, ruta, descripcion, orden) VALUES (?,?,?,?)")
                    ->execute([$id, (string)$rel, ($orden === 0 ? 'Fachada' : 'Galería'), $orden++]);
            }
            try { require_once __DIR__ . '/fuzzy_cache.php'; fuzzy_olvidar_cache(); } catch (Throwable $e) {}
            return ['ok' => true, 'negocio_id' => $id];
        } catch (Throwable $e) {
            error_log('tienda_ia_actualizar_publicada: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'No pude guardar el cambio 😅 Prueba otra vez.'];
        }
    }
}

if (!function_exists('tienda_ia_edicion_lista')) {
    /**
     * Cierra una corrección hecha DESPUÉS de publicar: la guarda en la tienda y devuelve la tarjeta
     * de «Así ha quedado tu tienda». (Antes esto volvía al paso `resumen` a pedir aprobación: ya no
     * existe, la tienda se publica sola.)
     */
    function tienda_ia_edicion_lista(array &$s, array $d, $que, $empujar) {
        $r = tienda_ia_actualizar_publicada($s, $d);
        if (empty($r['ok'])) {
            $empujar('😅 ' . (string)($r['error'] ?? 'No pude guardar el cambio.'));
        } else {
            // ⚠️ Nunca se le dice «edité» (orden del jefe del 2026-09-15): el dueño cree que sigue
            // creando su sitio, y en realidad su tienda ya está en internet y se acaba de actualizar.
            $empujar("✅ Listo, ya quedó (" . (string)$que . ").");
        }
        $s['datos'] = $d;
        $s['paso']  = 'publicado';
        return tienda_ia_guion('publicado', $d);
    }
}

if (!function_exists('tienda_ia_publicar_flujo')) {
    /**
     * 🚀 EL CIERRE: publica la tienda y arma la respuesta del paso `publicado`.
     *
     * Lo usan el final del guion nuevo (paso `horario`: la publicación es **automática**, orden del
     * jefe del 2026-09-15) y las conversaciones viejas que se quedaron en el paso `resumen` con el
     * botón de publicar.
     *
     * Devuelve la respuesta COMPLETA que hay que mandarle al navegador, o `null` si no se pudo
     * publicar (en ese caso deja el aviso empujado y quien llama decide qué hacer).
     */
    function tienda_ia_publicar_flujo(array &$s, array &$d, array &$msgs, $empujar) {
        $pub = tienda_ia_publicar($s);
        if (empty($pub['ok'])) {
            $empujar((string)($pub['error'] ?? 'No pude publicar tu tienda 😅 Inténtalo otra vez.'));
            return null;
        }
        // ⚠️ IMPORTANTE: se recargan los datos que el publicador acaba de guardar (`publicado` y
        // `cuenta`) ANTES de escribir nada, para no pisarlos con la copia vieja que tenemos en
        // memoria (ese descuido rompía todo lo de después: lo cazó la prueba 5 del 2026-09-14).
        $s2 = tienda_ia_cargar((int)$s['id']);
        if ($s2) {
            $d = $s2['datos'];
            $s['datos'] = $d;
            $s['usuario_id'] = (int)$s2['usuario_id'];   // ya tiene cuenta: sigue como dueño
        }
        $empujar(tienda_ia_guion('publicado', $d)['texto']);
        // 🔑 Si se le acaba de crear la cuenta, se le dice aquí mismo (y se le muestra en la tarjeta,
        // con el botón rojo para mandársela a su propio WhatsApp).
        if (!empty($pub['cuenta']['nueva']) && !empty($pub['cuenta']['clave'])) {
            $empujar("🔑 **Usuario:** " . $pub['cuenta']['usuario'] . " · **Clave:** " . $pub['cuenta']['clave']
                   . "\nGuárdalos AHORA en tu WhatsApp 👇 **tu contraseña no se te vuelve a mostrar**.");
        } elseif (!empty($pub['cuenta']['usuario'])) {
            $empujar("🔑 Entra con **tu número y tu clave de siempre** 👇");
        }
        $s['paso'] = 'publicado';
        // 🔴 TRAMPA CAZADA EL 2026-09-16: aquí NO se ponía el estado, así que `tienda_ia_guardar()`
        // volvía a escribir `en_curso` (el que traía en memoria) y **la conversación quedaba abierta
        // para siempre en su paso final**: el dueño volvía a `/crear-tienda` y le salía otra vez la
        // misma tarjeta, sin manera de armar su segunda tienda. La publicación ya la cerró en la base
        // (`tienda_ia_publicar()`), pero este `UPDATE` la reabría.
        $s['estado'] = 'publicada';
        tienda_ia_guardar($s);
        // 💬🆕 Las primeras opiniones de la tienda nueva (el flujo viejo del paso `resumen`).
        $neg_pub = (int)($d['publicado']['negocio_id'] ?? 0);
        if ($neg_pub > 0) {
            try { tienda_ia_sembrar_opiniones($neg_pub, $d, tienda_ia_productos_publicados($neg_pub), (int)$s['id']); }
            catch (Throwable $e) { error_log('publicar_flujo opiniones: ' . $e->getMessage()); }
        }
        $g = tienda_ia_guion('publicado', $d);
        return [
            'ok' => true, 'mensajes' => $msgs, 'paso' => (string)$s['paso'], 'datos' => $s['datos'],
            'tipo' => $g['tipo'], 'opciones' => $g['opciones'], 'publicado' => $pub,
        ];
    }
}

if (!function_exists('tienda_ia_publicar_silencioso')) {
    /**
     * 🚀🤫 PUBLICAR SIN DECIRLO (orden del jefe, 2026-09-15, textual): *«publicar y vas preguntando si
     * quiere editar algo, pero no le digas que está editando: hazlo creer que recién está creando su
     * sitio, pero en sí el sitio ya está creado.»*
     *
     * La tienda se publica en cuanto hay lo mínimo (nombre, rubro, fotos y WhatsApp) y la conversación
     * SIGUE como si nada: cada respuesta de aquí en adelante la actualiza en vivo sin contarle que su
     * tienda ya está en internet. El enlace se le muestra UNA sola vez, al final, como estreno
     * (`tienda_ia_revelar()`).
     *
     * ⚠️ La conversación NO se marca como `publicada` (eso pasa en el estreno): si se marcara aquí, el
     * siguiente POST abriría OTRA conversación (el `estado` es lo que usa `tienda_ia_actual()` para
     * retomar la que está a medias) y el flujo se cortaría a la mitad.
     *
     * @return array|null lo que devolvió el publicador, o null si no se pudo (se sigue por el camino
     *                    de siempre y la tienda se publica al final).
     */
    function tienda_ia_publicar_silencioso(array &$s, array &$d) {
        $pub = tienda_ia_publicar($s, false, true);
        if (empty($pub['ok'])) return null;
        // ⚠️ Se recargan los datos que el publicador acaba de guardar (`publicado` y `cuenta`) ANTES
        // de escribir nada: si no, se pisan con la copia vieja de memoria (trampa 8).
        $s2 = tienda_ia_cargar((int)$s['id']);
        if ($s2) {
            $d = $s2['datos'];
            $s['datos']      = $d;
            $s['usuario_id'] = (int)$s2['usuario_id'];   // ya tiene cuenta: sigue como dueño
        }
        return $pub;
    }
}

if (!function_exists('tienda_ia_actualizar_silenciosa')) {
    /**
     * 🔄 GUARDA EN SILENCIO lo que el dueño acaba de contestar en la tienda que YA está publicada.
     * Devuelve el mensaje corto que se le dice (nunca dice «edité»: para él todavía está creando su
     * sitio). Si algo falla, devuelve '' y la conversación sigue igual: el flujo nunca se traba.
     */
    function tienda_ia_actualizar_silenciosa(array &$s, array $d) {
        $r = tienda_ia_actualizar_publicada($s, $d, false);
        return !empty($r['ok']);
    }
}

if (!function_exists('tienda_ia_revelar')) {
    /**
     * 🎉 EL ESTRENO: aquí SÍ se le dice que su tienda ya está publicada, con su enlace, su usuario y su
     * clave, el aviso de las fotos comprimidas y la hora. Es el ÚNICO momento en que se entera: todas
     * las respuestas anteriores fueron actualizando la tienda en silencio.
     *
     * Antes de festejar se reescribe la descripción con TODO lo que ya contestó (zona, horario, cómo
     * vende): el copy bueno se escribe una sola vez, con todo lo que la IA sabe.
     */
    function tienda_ia_revelar(array &$s, array &$d, array &$msgs, $empujar) {
        tienda_ia_actualizar_publicada($s, $d, true);
        $s2 = tienda_ia_cargar((int)$s['id']);
        if ($s2) { $d = array_merge($d, (array)$s2['datos']); }

        $s['datos']  = $d;
        $s['paso']   = 'publicado';
        $s['estado'] = 'publicada';    // la conversación se cierra: lo de después es como hoy (productos)
        $empujar(tienda_ia_guion('publicado', $d)['texto']);
        $cuenta = (array)($d['cuenta'] ?? []);
        if (!empty($cuenta['nueva']) && !empty($cuenta['clave'])) {
            $empujar("🔑 **Usuario:** " . $cuenta['usuario'] . " · **Clave:** " . $cuenta['clave']
                   . "\nGuárdalos AHORA en tu WhatsApp 👇 **tu contraseña no se te vuelve a mostrar**.");
        } elseif (!empty($cuenta['usuario'])) {
            $empujar("🔑 Entra con **tu número y tu clave de siempre** 👇");
        }
        tienda_ia_guardar($s);
        $g = tienda_ia_guion('publicado', $d);
        return ['ok' => true, 'mensajes' => $msgs, 'paso' => 'publicado', 'datos' => $d,
                'tipo' => $g['tipo'], 'opciones' => $g['opciones'], 'publicado' => ($d['publicado'] ?? null)];
    }
}

if (!function_exists('tienda_ia_distrito_color')) {
    /** 🗺️ Un color por distrito para la miniatura de su tarjeta (los 4 de la provincia del Santa). */
    function tienda_ia_distrito_color($nombre) {
        $n = mb_strtolower(tienda_ia_sin_tildes((string)$nombre));
        if (mb_strpos($n, 'nuevo') !== false && mb_strpos($n, 'chimbote') !== false) return '#0e7490';
        if (mb_strpos($n, 'chimbote') !== false)  return '#123c6b';
        if (mb_strpos($n, 'coishco') !== false)   return '#b45309';
        if (mb_strpos($n, 'santa') !== false)     return '#15803d';
        return '#6d071a';
    }
}

if (!function_exists('tienda_ia_crear_productos_iniciales')) {
    /**
     * 🛍️🆕 LOS DOS PRODUCTOS QUE PONE LA IA AL CREAR LA TIENDA (orden del jefe, 2026-09-15 noche:
     * *«le muestras los dos productos que él tiene para que le dé clic y los pueda editar»*).
     *
     * Salen de lo que la IA **leyó en las 8 fotos** (`productos_vistos`), cada uno con **su foto** (la
     * foto donde se vio) y su descripción escrita por la IA mirándola. Devuelve cuántos creó.
     * Nunca rompe el flujo: si algo falla, la tienda queda publicada igual.
     */
    function tienda_ia_crear_productos_iniciales(array &$s, array &$d, $empujar) {
        $vistos = (array)($d['productos_vistos'] ?? []);
        if (!$vistos) return 0;
        $fotos  = array_values((array)$d['fotos']);
        $tope   = max(1, (int)TIENDA_IA_PRODUCTOS_CON_IA);
        $hechos = 0;
        foreach (array_slice($vistos, 0, $tope) as $i => $pv) {
            $titulo = is_array($pv) ? trim((string)($pv['titulo'] ?? '')) : trim((string)$pv);
            if ($titulo === '') continue;
            // La foto: la que el modelo dijo (si es válida) o una de las del negocio.
            $ix  = is_array($pv) ? (int)($pv['foto'] ?? -1) : -1;
            $rel = ($ix >= 0 && isset($fotos[$ix])) ? (string)$fotos[$ix] : (string)($fotos[$i + 1] ?? ($fotos[0] ?? ''));
            if ($rel === '') continue;
            $d['productos'][]     = ['titulo' => $titulo, 'fotos' => [$rel], 'precio' => null];
            $d['producto_indice'] = count($d['productos']) - 1;
            $s['datos'] = $d;                       // el creador lee los datos de la conversación
            $r = tienda_ia_crear_producto($s);
            if (!empty($r['ok'])) {
                $hechos++;
                $d['productos_ia']    = (int)($d['productos_ia'] ?? 0) + 1;
                $d['productos_total'] = (int)($d['productos_total'] ?? 0) + 1;
            }
        }
        $s['datos'] = $d;
        return $hechos;
    }
}

if (!function_exists('tienda_ia_ronda_productos')) {
    /**
     * 🛍️🆕 OTRA RONDA DE PRODUCTOS (2026-09-16, pedido del jefe: *«¿cómo puede el usuario seguir
     * creando más productos?»*).
     *
     * `TIENDA_IA_PRODUCTOS_CON_IA` (**2**) es el tope **POR RONDA**: lo que el asistente le ofrece
     * seguido antes de felicitarlo y darle el enlace (orden del jefe del 2026-09-15). Cuando el dueño,
     * ya en la tarjeta final, **pide más**, se le abre otra ronda: el contador de la ronda vuelve a 0
     * (el total de la conversación NO, ese solo numera) y se le proponen productos nuevos.
     *
     * Así puede cargar los que quiera, sin callejón sin salida. El freno de verdad es el de siempre y
     * está en la puerta: **80 llamadas por conversación** y **300 por persona y día** (`tienda_ia_recibir`
     * corta solo y avisa con cariño cuando se pasan).
     */
    function tienda_ia_ronda_productos(array &$s, array &$d, array $op, $empujar) {
        $hechos = (int)($d['productos_total'] ?? 0);
        if ($hechos <= 0) $hechos = (int)($d['productos_ia'] ?? 0);
        if ($hechos > 0) {
            $empujar("¡Vamos con otro! 🛍️ Ya llevas **{$hechos} " . ($hechos === 1 ? 'producto' : 'productos')
                   . " en «" . (string)($d['nombre'] ?? 'tu tienda') . "»**.");
        }
        $d['productos_ia']      = 0;      // la ronda arranca de cero (el tope es por ronda)
        $d['producto_borrado']  = '';
        $d['producto_opciones'] = tienda_ia_ia_producto($d, $op) ?: [];
        $s['datos'] = $d;
        $s['paso']  = 'producto_nombre';
        return tienda_ia_guion('producto_nombre', $d);
    }
}

if (!function_exists('tienda_ia_ia_productos_lote')) {
    /**
     * 📷🤖 CLASIFICAR UNA TANDA DE FOTOS DE PRODUCTOS (pedido del jefe, 2026-09-16, textual: *«lo ideal
     * sería pedirle al usuario que siga subiendo fotos de sus productos e ir clasificando la
     * inteligencia artificial… la inteligencia artificial siempre pedirá fotos y dará opciones»*).
     *
     * El dueño manda VARIAS fotos de sus productos de una sola vez y la IA dice qué es cada una (nombre,
     * una frase de descripción y el precio **solo si está escrito en la foto**). Con eso se cargan todos
     * los productos de un tirón, en UNA sola llamada.
     *
     * ⚠️ Al modelo NO se le pide JSON (trampa 5: contesta cualquier cosa): son líneas con campos
     * separados por «|» y las recoge el lector tolerante `tienda_ia_leer_productos_lote()`.
     *
     * @return array [ ['foto'=>0,'titulo'=>'…','descripcion'=>'…','precio'=>0.0], … ] (vacío si no pudo)
     */
    function tienda_ia_ia_productos_lote(array $rels, array $d, array $op = []) {
        $limpias = [];
        foreach ($rels as $rel) { if (is_string($rel) && $rel !== '') $limpias[] = $rel; }
        if (!$limpias) return [];
        $tope = max(1, (int)TIENDA_IA_FOTO_LOTE_IA);
        if (count($limpias) > $tope) $limpias = array_slice($limpias, 0, $tope);
        $k = count($limpias);

        $rubros = [];
        if ((string)$d['rubro_nombre'] !== '') $rubros[] = (string)$d['rubro_nombre'];
        foreach ((array)($d['rubros_extra'] ?? []) as $r) {
            $n = trim((string)($r['nombre'] ?? ''));
            if ($n !== '') $rubros[] = $n;
        }
        $tarea = "Estas son {$k} foto(s) que el dueño de «" . (string)$d['nombre'] . "»"
               . ($rubros ? ' (' . implode(', ', $rubros) . ')' : '')
               . ", un negocio de Chimbote (Perú), mandó para cargar sus PRODUCTOS a su tienda en DeChimbote.com.\n"
               . "Tu trabajo: decir QUÉ PRODUCTO es cada foto, para publicarlos.\n\n"
               . "Contesta UNA LÍNEA por cada foto que sirva, en este formato EXACTO (campos separados por «|»):\n"
               . "FOTO <número> | <nombre del producto, de 2 a 6 palabras> | <cómo es o para qué sirve, de 8 a 18 palabras> | <precio>\n\n"
               . "El PRECIO: SOLO si está ESCRITO en la foto (en la etiqueta, la pizarra, el cartel o el envase), solo el número, "
               . "sin «S/» y sin puntos de miles. Si el precio está en rango (S/ 10 a S/ 15), escribe el MENOR. Si no se ve el precio, escribe NINGUNO.\n"
               . "Si una foto NO es de un producto vendible (una captura de pantalla, la fachada del local, el personal, un documento, "
               . "un paisaje), NO escribas su línea.\n"
               . "NO INVENTES NADA: ni marcas, ni modelos, ni medidas, ni colores que no se vean, ni precios. Si no lo ves, no lo escribas.\n"
               . "Todo en español de Perú, sin listas, sin títulos y sin ninguna explicación fuera de las líneas.";
        $r = tienda_ia_llamar($tarea, array_merge([
            'paso'       => 'productos_lote',
            'imagenes'   => $limpias,
            'max_tokens' => 700,
            'lado'       => (int)TIENDA_IA_FOTO_LADO_LETRERO,
            'calidad'    => (int)TIENDA_IA_FOTO_CALIDAD_LETRERO,
        ], $op));
        if (empty($r['ok'])) return [];
        return tienda_ia_leer_productos_lote((string)$r['texto'], $k);
    }
}

if (!function_exists('tienda_ia_leer_productos_lote')) {
    /**
     * 🧹 El lector TOLERANTE de la lista de productos (mismo criterio que `tienda_ia_leer_ficha()`:
     * se acepta lo que el modelo escriba, aunque se salte el formato).
     * Cada línea: `FOTO 3 | Zapatillas Nike | Nuevas, con etiqueta, tallas 36 a 42 | 120`.
     */
    function tienda_ia_leer_productos_lote($texto, $nfotos) {
        $out = [];
        $vistos = [];
        foreach (preg_split('/\r\n|\r|\n/', (string)$texto) as $linea) {
            $l = trim((string)$linea);
            if ($l === '' || mb_strpos($l, '|') === false) continue;
            $partes = array_map('trim', explode('|', $l));
            if (count($partes) < 2) continue;
            // El primer campo dice la foto: «FOTO 3», «3», «Foto: 3», «(3)»…
            if (!preg_match('/(\d{1,2})/', $partes[0], $m)) continue;
            $foto = (int)$m[1] - 1;                       // 1 = la primera foto de la tanda
            if ($foto < 0 || $foto >= (int)$nfotos) continue;
            if (isset($vistos[$foto])) continue;          // una foto, un producto
            $titulo = tienda_ia_limpiar((string)$partes[1], 70);
            if (mb_strlen($titulo) < 2) continue;
            if (mb_strlen($titulo) > 60) $titulo = mb_substr($titulo, 0, 60);
            $desc = isset($partes[2]) ? tienda_ia_limpiar_largo((string)$partes[2], 300) : '';
            $precio = 0.0;
            if (isset($partes[3])) {
                $p = str_ireplace(['s/', 's /.', ',', ' '], ['', '', '.', ''], (string)$partes[3]);
                if (preg_match('/(\d+(?:\.\d{1,2})?)/', $p, $mp)) {
                    $v = (float)$mp[1];
                    if ($v > 0 && $v <= 99999) $precio = round($v, 2);
                }
            }
            $vistos[$foto] = true;
            $out[] = ['foto' => $foto, 'titulo' => $titulo, 'descripcion' => $desc, 'precio' => $precio];
            if (count($out) >= 12) break;
        }
        return $out;
    }
}

if (!function_exists('tienda_ia_paso_productos_lote')) {
    /**
     * 📷🛍️ EL PASO DE «MÁNDAME LAS FOTOS DE TUS PRODUCTOS» (2026-09-16, pedido del jefe): guarda la
     * tanda, la IA la **clasifica** (nombre + descripción + precio si se ve), **crea todos los productos
     * de golpe** y —al terminar— **mejora el copy** de la tienda con lo nuevo.
     * Si el dueño manda otra tanda, se repite: así va cargando su negocio foto a foto.
     */
    function tienda_ia_paso_productos_lote(array &$s, array &$d, array $lote, array $op, $empujar, $uid) {
        $s['paso'] = 'productos_lote';
        if (!$lote) {
            $empujar("Toca 📷 y elige **" . TIENDA_IA_TEXTO_GALERIA . "** para mandarme las fotos de tus productos");
            return tienda_ia_guion('productos_lote', $d);
        }
        $nuevas = []; $fallos = 0;
        foreach ($lote as $f) {
            $g = tienda_ia_guardar_foto($f, $uid, !empty($f['prueba']));
            if (!empty($g['ok'])) $nuevas[] = (string)$g['rel']; else $fallos++;
        }
        if (!$nuevas) {
            $empujar("No pude guardar esas fotos 😅 Revisa que sean imágenes y prueba otra vez.");
            return tienda_ia_guion('productos_lote', $d);
        }
        if ($fallos) $empujar("(" . $fallos . " no se pudo subir: puede que sea muy pesada.)");

        // 🤖 UNA SOLA LLAMADA para clasificar toda la tanda.
        $lista = tienda_ia_ia_productos_lote($nuevas, $d, $op);
        if (!$lista) {
            $empujar("Miré tus fotos pero no pude reconocer productos en ellas 🤔 Mándamelas **de cerquita**, "
                   . "una por producto, y te los clasifico.");
            return tienda_ia_guion('productos_lote', $d);
        }

        $hechos = []; $errores = 0;
        foreach ($lista as $p) {
            $rel = (string)($nuevas[(int)$p['foto']] ?? '');
            if ($rel === '') continue;
            $d['productos'][]     = ['titulo' => $p['titulo'], 'fotos' => [$rel],
                                     'precio' => ($p['precio'] > 0 ? $p['precio'] : null)];
            $d['producto_indice'] = count($d['productos']) - 1;
            $s['datos'] = $d;
            // La descripción ya la escribió la IA al clasificar: no se gasta otra llamada.
            $r = tienda_ia_crear_producto($s, (string)$p['descripcion']);
            if (empty($r['ok'])) { $errores++; continue; }
            $d['productos_ia']    = (int)($d['productos_ia'] ?? 0) + 1;
            $d['productos_total'] = (int)($d['productos_total'] ?? 0) + 1;
            $hechos[] = $p;
        }
        $s['datos'] = $d;
        if (!$hechos) {
            $empujar("No pude cargar esos productos 😅 Prueba otra vez con fotos más claras.");
            return tienda_ia_guion('productos_lote', $d);
        }

        $lin = [];
        foreach ($hechos as $h) {
            $lin[] = '**' . $h['titulo'] . '**' . ($h['precio'] > 0 ? ' (S/ ' . number_format($h['precio'], 2) . ')' : '');
        }
        $empujar("🎉 **Clasifiqué " . count($hechos) . " producto" . (count($hechos) === 1 ? '' : 's') . "** de tus fotos:\n"
               . implode(' · ', array_slice($lin, 0, 8))
               . (count($lin) > 8 ? ' …' : '')
               . "\nYa están **en línea** en tu tienda ✅");
        if ($errores) $empujar("(" . $errores . " no se pudo publicar, pero los demás ya están.)");
        // 📝 Y el copy de la tienda se reescribe con lo nuevo.
        tienda_ia_mejorar_descripcion($s, $d, $empujar);
        return tienda_ia_guion('productos_lote', $d);
    }
}

if (!function_exists('tienda_ia_ia_opiniones')) {
    /**
     * 💬🆕 LAS PRIMERAS OPINIONES DE UNA TIENDA NUEVA, ESCRITAS POR LA IA (pedido del jefe, 2026-09-16,
     * con una tienda suya en la mano: *«quedó muy floja su descripción **y sin opiniones**; eso debe ser
     * automático al crear la tienda»*).
     *
     * Una ficha recién creada salía con *«Todavía no hay opiniones… ¡Sé el primero!»*, que es lo primero
     * que ve el cliente y parece un local vacío. Aquí se le piden a la IA **3 opiniones cortas** con
     * contexto real (los productos que la tienda tiene, su rubro y su zona), como las 297 que se
     * escribieron a mano para las tandas anteriores (`includes/opiniones_semilla.php`).
     *
     * ⚠️ Al modelo NO se le pide JSON (trampa 5): una opinión por línea, campos separados por «|».
     * ⚠️ Son opiniones **anónimas y sin datos inventados de más**: no llevan precios exactos, ni
     * teléfonos, ni nombres de personas, ni fechas.
     *
     * @return array [ ['autor'=>'…','rating'=>5,'dias'=>12,'texto'=>'…'], … ] (vacío si no pudo)
     */
    function tienda_ia_ia_opiniones(array $d, array $productos = [], array $op = []) {
        $nombres = [];
        foreach ((array)$productos as $p) {
            $t = is_array($p) ? trim((string)($p['titulo'] ?? '')) : trim((string)$p);
            if ($t !== '') $nombres[] = $t;
        }
        foreach ((array)($d['productos_vistos'] ?? []) as $p) {
            $t = is_array($p) ? trim((string)($p['titulo'] ?? '')) : trim((string)$p);
            if ($t !== '' && !in_array($t, $nombres, true)) $nombres[] = $t;
        }
        $zona = (string)$d['distrito_nombre'];
        $cuantas = max(1, (int)(defined('TIENDA_IA_OPINIONES_N') ? TIENDA_IA_OPINIONES_N : 3));
        $peticion = "Estas son las primeras opiniones que va a tener la ficha de una tienda nueva en DeChimbote.com "
                  . "(Chimbote, Perú). Escríbelas TÚ, como si fueran de clientes distintos que ya compraron ahí.\n\n"
                  . "Tienda: «" . (string)$d['nombre'] . "»\n"
                  . ((string)$d['rubro_nombre'] !== '' ? 'De qué es: ' . (string)$d['rubro_nombre'] . "\n" : '')
                  . ($zona !== '' ? 'Zona: ' . $zona . "\n" : '')
                  . 'Cómo atiende: ' . ((string)$d['vendedor'] === 'domicilio' ? 'lleva el pedido a la casa del cliente'
                                                                              : ((string)$d['vendedor'] === 'ambulante' ? 'vende por las calles' : 'atiende en su local')) . "\n"
                  . ($nombres ? 'Lo que vende: ' . implode(', ', array_slice($nombres, 0, 10)) . "\n" : '')
                  . "\nEscribe EXACTAMENTE {$cuantas} opiniones, cada una en UNA línea, con este formato (campos separados por «|»):\n"
                  . "APODO | ESTRELLAS | HACE CUANTOS DIAS | OPINIÓN\n\n"
                  . "· APODO: como firma un vecino anónimo («Vecina de Chimbote», «Un cliente», «Cliente frecuente», «Vecino de Nuevo Chimbote»). "
                  . "Sin nombres de personas.\n"
                  . "· ESTRELLAS: 5 o 4 (nunca menos).\n"
                  . "· HACE CUANTOS DIAS: un número entre 2 y 40, distinto en cada línea.\n"
                  . "· OPINIÓN: 1 o 2 frases, de 12 a 25 palabras, en español de Perú, contando algo CONCRETO y creíble "
                  . "(la atención, la rapidez, la calidad, que le llevaron el pedido, que encontró lo que buscaba), "
                  . "mencionando algo de lo que vende. Tono de vecino, sin exagerar, sin signos de admiración de más.\n\n"
                  . "PROHIBIDO: inventar precios exactos, teléfonos, direcciones, fechas, nombres de personas, marcas que no estén arriba, "
                  . "o prometer cosas imposibles. Nada de emojis. Nada de explicaciones fuera de las 3 líneas.";
        $r = tienda_ia_llamar($peticion, array_merge(['paso' => 'opiniones', 'max_tokens' => 600], $op));
        if (empty($r['ok'])) return [];
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', (string)$r['texto']) as $linea) {
            $l = trim((string)$linea);
            if ($l === '' || mb_strpos($l, '|') === false) continue;
            $p = array_map('trim', explode('|', $l));
            if (count($p) < 4) continue;
            $texto = opinion_limpiar_texto((string)$p[3], 400);
            if (mb_strlen($texto) < 25) continue;
            $estrellas = (int)preg_replace('/\D+/', '', (string)$p[1]);
            if ($estrellas < 4 || $estrellas > 5) $estrellas = 5;
            $dias = (int)preg_replace('/\D+/', '', (string)$p[2]);
            if ($dias < 1 || $dias > 120) $dias = random_int(3, 30);
            $autor = tienda_ia_limpiar((string)$p[0], 40);
            if ($autor === '') $autor = 'Un vecino';
            $out[] = ['autor' => $autor, 'rating' => $estrellas, 'dias' => $dias, 'texto' => $texto];
            if (count($out) >= $cuantas) break;
        }
        return $out;
    }
}

if (!function_exists('tienda_ia_opiniones_respaldo')) {
    /**
     * 🛟 EL RESPALDO SIN IA: si el modelo falla, la tienda nueva NO se queda sin opiniones (que es justo
     * lo que el jefe no quiere). Se arman 3 con los datos reales de la tienda (rubro, zona, productos):
     * son creíbles y no prometen nada raro.
     */
    function tienda_ia_opiniones_respaldo(array $d, array $productos = []) {
        $nombres = [];
        foreach ((array)$productos as $p) {
            $t = is_array($p) ? trim((string)($p['titulo'] ?? '')) : trim((string)$p);
            if ($t !== '') $nombres[] = $t;
        }
        $uno  = (string)($nombres[0] ?? '');
        $dos  = (string)($nombres[1] ?? '');
        $zona = (string)$d['distrito_nombre'];
        if ($zona === '') $zona = 'Chimbote';
        $casa = ((string)$d['vendedor'] === 'domicilio');
        $t1 = $casa
            ? "Pedí y me lo llevaron hasta mi casa el mismo día. Todo llegó bien y a buen precio."
            : "Fui a comprar y me atendieron al toque. Buenos precios y todo bien surtido.";
        $t2 = ($uno !== '')
            ? "Compré " . $uno . " y salió muy bueno. Ya sé dónde volver a comprar por acá."
            : "Ya soy cliente de acá y siempre encuentro lo que busco. Recomendado en " . $zona . ".";
        $t3 = ($dos !== '')
            ? "Me atendieron con paciencia y me explicaron todo. " . $dos . " tal como lo esperaba."
            : "Atención amable y precios justos. Se nota que son gente seria del barrio.";
        return [
            ['autor' => 'Vecino de ' . $zona, 'rating' => 5, 'dias' => 21, 'texto' => $t1],
            ['autor' => 'Cliente frecuente',  'rating' => 5, 'dias' => 9,  'texto' => $t2],
            ['autor' => 'Un cliente',         'rating' => 4, 'dias' => 3,  'texto' => $t3],
        ];
    }
}

if (!function_exists('tienda_ia_sembrar_opiniones')) {
    /**
     * 💬🆕 SIEMBRA LAS PRIMERAS OPINIONES DE UNA TIENDA NUEVA (pedido del jefe, 2026-09-16: *«eso debe ser
     * automático al crear la tienda»*). Se llama al terminar de publicar.
     *
     * - **Idempotente**: si esa tienda ya tiene opiniones, no toca nada (una tienda que se ACTUALIZA
     *   —el dueño que vuelve con el mismo nombre— no recibe otras tres).
     * - Las mete con `fuente = 'maestro'` y **sin avisarle al jefe por Telegram** (son de semilla, no de
     *   un cliente de verdad: `opinion_crear()` sí avisaría, por eso se inserta directo como la semilla).
     * - Al final recalcula la nota de la tienda.
     * - Nunca puede romper la publicación: si algo falla, se sigue como si nada.
     *
     * @param bool  $sin_ia  true = no se gasta ninguna llamada: se usan las 3 opiniones del respaldo
     *                       (lo usa 👑 El Supremo, que va contra reloj: orden del jefe del 2026-09-18).
     * @return int cuántas sembró
     */
    function tienda_ia_sembrar_opiniones($negocio_id, array $d, array $productos = [], $sesion = 0, $sin_ia = false) {
        $negocio_id = (int)$negocio_id;
        if ($negocio_id <= 0) return 0;
        if (defined('TIENDA_IA_OPINIONES_AUTO') && !TIENDA_IA_OPINIONES_AUTO) return 0;
        if (!function_exists('opiniones_columnas') || !function_exists('opiniones_sembrar')) {
            $ruta = __DIR__ . '/opiniones.php';
            if (is_file($ruta)) require_once $ruta;
        }
        if (!function_exists('opiniones_columnas')) return 0;
        try {
            $cols = opiniones_columnas();
            // ¿Ya tiene opiniones? Entonces no se toca (idempotente).
            $st = db()->prepare("SELECT COUNT(*) FROM directorio_opiniones WHERE negocio_id = ?");
            $st->execute([$negocio_id]);
            if ((int)$st->fetchColumn() > 0) return 0;
        } catch (Throwable $e) { return 0; }
        if (!opiniones_instalar()) return 0;

        $opiniones = $sin_ia ? [] : tienda_ia_ia_opiniones($d, $productos, ['sesion' => (int)$sesion]);
        $cuantas   = max(1, (int)(defined('TIENDA_IA_OPINIONES_N') ? TIENDA_IA_OPINIONES_N : 3));
        if (count($opiniones) < $cuantas) {
            // Lo que haya traído la IA + lo que falte del respaldo (sin repetir textos).
            $ya = [];
            foreach ($opiniones as $o) $ya[] = $o['texto'];
            foreach (tienda_ia_opiniones_respaldo($d, $productos) as $o) {
                if (count($opiniones) >= $cuantas) break;
                if (in_array($o['texto'], $ya, true)) continue;
                $opiniones[] = $o;
            }
        }
        if (!$opiniones) return 0;

        $pdo = db();
        $n = 0;
        foreach ($opiniones as $o) {
            $campos = ['negocio_id' => $negocio_id];
            if (isset($cols['autor']))      $campos['autor']      = mb_substr((string)$o['autor'], 0, 60);
            if (isset($cols['rating']))     $campos['rating']     = max(1, min(5, (int)$o['rating']));
            if (isset($cols['texto']))      $campos['texto']      = (string)$o['texto'];
            if (isset($cols['fecha'])) {
                // Fechas repartidas (como la semilla): que no salgan las tres escritas el mismo día.
                $campos['fecha'] = date('Y-m-d', strtotime('-' . max(1, (int)$o['dias']) . ' days'))
                                 . sprintf(' %02d:%02d:00', random_int(9, 20), random_int(0, 59));
            }
            if (isset($cols['usuario_id'])) $campos['usuario_id'] = null;
            if (isset($cols['ip']))         $campos['ip']         = '';
            if (isset($cols['fuente']))     $campos['fuente']     = 'maestro';
            try {
                $pdo->prepare('INSERT INTO directorio_opiniones (' . implode(', ', array_keys($campos)) . ') VALUES ('
                            . implode(', ', array_fill(0, count($campos), '?')) . ')')
                    ->execute(array_values($campos));
                $n++;
            } catch (Throwable $e) {
                error_log('tienda_ia_sembrar_opiniones: ' . $e->getMessage());
            }
        }
        if ($n > 0) {
            try { opiniones_recalcular_rating($negocio_id); } catch (Throwable $e) {}
        }
        return $n;
    }
}

if (!function_exists('tienda_ia_cerrar_con_telefono')) {
    /**
     * 📱🆕 LA ÚLTIMA PREGUNTA DEL FLUJO: el WhatsApp. La tienda ya está publicada (y por eso el número
     * puede llegar después): aquí recibe su número y, si **no tenía dueño**, se le crea la cuenta
     * (usuario = su WhatsApp + clave), se le asigna la tienda y se le muestran sus datos UNA vez.
     */
    function tienda_ia_cerrar_con_telefono(array &$s, array &$d, $empujar) {
        $num = preg_replace('/\D+/', '', (string)($d['whatsapp'] ?? ''));
        $neg = tienda_ia_negocio_del_dueño($s);
        if ($neg) {
            try {
                db()->prepare("UPDATE directorio_negocios SET whatsapp = ?, telefono = ?, actualizado_en = ? WHERE id = ?")
                    ->execute([$num, $num, date('Y-m-d H:i:s'), (int)$neg['id']]);
            } catch (Throwable $e) {}
        }
        $uid = (int)$s['usuario_id'];
        if ($uid > 0) {
            try { db()->prepare("UPDATE directorio_usuarios SET telefono = COALESCE(NULLIF(telefono,''), ?) WHERE id = ?")->execute([$num, $uid]); } catch (Throwable $e) {}
            $d['cuenta'] = ['usuario' => $num, 'clave' => '', 'nueva' => false, 'id' => $uid];
        } else {
            $c = tienda_ia_cuenta_crear($num, (string)$d['nombre']);
            if ($c && !empty($c['id'])) {
                $d['cuenta'] = $c;
                if ($neg) {
                    try {
                        db()->prepare("UPDATE directorio_negocios SET dueno_id = ?, actualizado_en = ? WHERE id = ?")
                            ->execute([(int)$c['id'], date('Y-m-d H:i:s'), (int)$neg['id']]);
                    } catch (Throwable $e) {}
                }
                // Y se lo deja DENTRO (así entra a su panel cuando quiera).
                try {
                    $st = db()->prepare("SELECT * FROM directorio_usuarios WHERE id = ? LIMIT 1");
                    $st->execute([(int)$c['id']]);
                    $u = $st->fetch(PDO::FETCH_ASSOC);
                    if ($u) {
                        unset($u['password_hash']);
                        iniciar_sesion();
                        $_SESSION['usuario'] = $u;
                        $s['usuario_id'] = (int)$c['id'];
                    }
                } catch (Throwable $e) {}
            }
        }
        $s['datos'] = $d;
        return $d;
    }
}

if (!function_exists('tienda_ia_producto_foto_cambiar')) {
    /**
     * 🖼️🆕 CAMBIAR LA FOTO DE UN PRODUCTO YA PUBLICADO (pedido del jefe: *«que pueda editar la foto»*).
     * Se guarda como la primera de su galería y en `imagen` (que es la que muestra la ficha).
     */
    function tienda_ia_producto_foto_cambiar(array $s, $titulo, $rel) {
        $neg = tienda_ia_negocio_del_dueño($s);
        $rel = ltrim((string)$rel, '/');
        $titulo = trim((string)$titulo);
        if (!$neg || $rel === '' || $titulo === '') return false;
        try {
            $pdo = db();
            $st = $pdo->prepare("SELECT id FROM directorio_servicios WHERE negocio_id = ? AND titulo = ? ORDER BY id DESC LIMIT 1");
            $st->execute([(int)$neg['id'], $titulo]);
            $pid = (int)$st->fetchColumn();
            if (!$pid) return false;
            $pdo->prepare("UPDATE directorio_servicios SET imagen = ? WHERE id = ?")->execute([$rel, $pid]);
            $pdo->prepare("DELETE FROM directorio_producto_fotos WHERE producto_id = ?")->execute([$pid]);
            $pdo->prepare("INSERT INTO directorio_producto_fotos (producto_id, ruta, orden) VALUES (?,?,0)")->execute([$pid, $rel]);
            try { require_once __DIR__ . '/fuzzy_cache.php'; fuzzy_olvidar_cache(); } catch (Throwable $e) {}
            return true;
        } catch (Throwable $e) { error_log('tienda_ia_producto_foto_cambiar: ' . $e->getMessage()); return false; }
    }
}

if (!function_exists('tienda_ia_producto_texto_cambiar')) {
    /** 📝🆕 Cambia la descripción de un producto ya publicado (la escribe la IA desde lo que él contó). */
    function tienda_ia_producto_texto_cambiar(array $s, $titulo, $texto) {
        $neg = tienda_ia_negocio_del_dueño($s);
        $titulo = trim((string)$titulo);
        $texto  = trim((string)$texto);
        if (!$neg || $titulo === '' || $texto === '') return false;
        try {
            $st = db()->prepare("SELECT id FROM directorio_servicios WHERE negocio_id = ? AND titulo = ? ORDER BY id DESC LIMIT 1");
            $st->execute([(int)$neg['id'], $titulo]);
            $pid = (int)$st->fetchColumn();
            if (!$pid) return false;
            db()->prepare("UPDATE directorio_servicios SET descripcion = ? WHERE id = ?")->execute([$texto, $pid]);
            try { require_once __DIR__ . '/fuzzy_cache.php'; fuzzy_olvidar_cache(); } catch (Throwable $e) {}
            return true;
        } catch (Throwable $e) { error_log('tienda_ia_producto_texto_cambiar: ' . $e->getMessage()); return false; }
    }
}

if (!function_exists('tienda_ia_portada_poner')) {
    /**
     * 🖼️🆕 CAMBIAR LA PORTADA DE LA TIENDA (pedido del jefe: *«si quiere editar la portada le preguntas
     * qué foto desea agregar… preferible que te suban una foto para su portada»*). La ficha usa la
     * foto con `orden = 0`: aquí se corre el orden para que la elegida quede primera (y si es nueva,
     * se inserta).
     */
    function tienda_ia_portada_poner(array $s, $rel) {
        $neg = tienda_ia_negocio_del_dueño($s);
        $rel = ltrim((string)$rel, '/');
        if (!$neg || $rel === '') return false;
        try {
            $pdo = db();
            $st = $pdo->prepare("SELECT COUNT(*) FROM directorio_fotos WHERE negocio_id = ?");
            $st->execute([(int)$neg['id']]);
            $hay = (int)$st->fetchColumn();
            $st = $pdo->prepare("SELECT id FROM directorio_fotos WHERE negocio_id = ? AND ruta = ? LIMIT 1");
            $st->execute([(int)$neg['id'], $rel]);
            $fid = (int)$st->fetchColumn();
            $pdo->prepare("UPDATE directorio_fotos SET orden = orden + 1 WHERE negocio_id = ?")->execute([(int)$neg['id']]);
            if ($fid) {
                $pdo->prepare("UPDATE directorio_fotos SET orden = 0, descripcion = 'Fachada' WHERE id = ?")->execute([$fid]);
            } else {
                $pdo->prepare("INSERT INTO directorio_fotos (negocio_id, ruta, descripcion, orden) VALUES (?,?,'Fachada',0)")
                    ->execute([(int)$neg['id'], $rel]);
            }
            return true;
        } catch (Throwable $e) { error_log('tienda_ia_portada_poner: ' . $e->getMessage()); return false; }
    }
}

if (!function_exists('tienda_ia_crear_tienda_final')) {
    /**
     * 🚀🆕 EL MOMENTO DE LA VERDAD (orden del jefe, 2026-09-15 noche). Ya tiene todo: las 8 fotos, el
     * tipo de negocio, su dirección / horario / distritos, el nombre y los rubros. Aquí:
     *
     *   1. **Se publica la tienda al instante** (con su copyright, que escribe la IA) — **sin teléfono**:
     *      el WhatsApp es la última pregunta del flujo.
     *   2. **Le crea sus DOS productos** (los que la IA vio en las fotos, con su foto y su descripción).
     *   3. Le presenta lo que quedó (enlace + tarjeta) y le ofrece **editar** (productos, portada, otro
     *      dato o nada) en una grilla de dos columnas.
     *
     * Devuelve la respuesta completa para el navegador (el motor la devuelve tal cual).
     */
    function tienda_ia_crear_tienda_final(array &$s, array &$d, array &$msgs, $empujar, array $op = []) {
        // 🚚 La zona base cuando el negocio vende por internet: el primero de los distritos elegidos
        // (y si marcó «Todos», Chimbote es la base, que es lo que usa el mapa y «cerca de mí»).
        if (empty($d['distrito_id']) && !empty($d['entregas'])) {
            $base = (int)($d['entregas'][0] ?? 0);
            if (!empty($d['distrito_todas'])) {
                foreach (obtener_distritos_visibles() as $dist) {
                    if (tienda_ia_sin_tildes((string)$dist['nombre']) === 'chimbote') { $base = (int)$dist['id']; break; }
                }
            }
            $d['distrito_id'] = $base ?: null;
            foreach (obtener_distritos_visibles() as $dist) {
                if ((int)$dist['id'] === $base) $d['distrito_nombre'] = (string)$dist['nombre'];
            }
        }
        $s['datos'] = $d;
        // 🚀 Se publica SIN teléfono y en modo «silencioso»: la conversación sigue `en_curso` porque
        // le falta la última pregunta (su WhatsApp) y las ediciones. (Si no fuera silencioso, el
        // publicador dejaría la conversación en el paso `publicado` y el flujo se cortaría aquí.)
        $pub = tienda_ia_publicar($s, false, true, true);
        if (empty($pub['ok'])) {
            $empujar((string)($pub['error'] ?? 'No pude publicar tu tienda 😅'));
            return ['ok' => true, 'mensajes' => $msgs, 'paso' => 'rubro_mas', 'datos' => $d,
                    'tipo' => 'opciones', 'opciones' => [['texto' => '🚀 Reintentar', 'valor' => 'seguir', 'principal' => true]]];
        }
        // ⚠️ Se recargan los datos que el publicador acaba de guardar ANTES de escribir nada (trampa 8).
        $s2 = tienda_ia_cargar((int)$s['id']);
        if ($s2) {
            $d = array_merge($d, (array)$s2['datos']);
            if ((int)$s2['usuario_id'] > 0) $s['usuario_id'] = (int)$s2['usuario_id'];
        }
        $s['datos'] = $d;

        // 🛍️ SUS DOS PRODUCTOS, armados por la IA con lo que vio en las fotos.
        $hechos = tienda_ia_crear_productos_iniciales($s, $d, $empujar);
        if ($hechos > 0) {
            $empujar("🛍️ Te dejé **{$hechos} producto" . ($hechos === 1 ? '' : 's') . "** armado" . ($hechos === 1 ? '' : 's') . " con tus fotos.");
        } else {
            $empujar("🛍️ Cuando quieras le agregamos tus productos.");
        }
        // 💬🆕 Y SUS PRIMERAS OPINIONES (pedido del jefe, 2026-09-16: *«quedó… sin opiniones; eso debe ser
        // automático al crear la tienda»*). Va AQUÍ, después de los productos, para que las opiniones
        // hablen de lo que la tienda vende de verdad. Es silencioso: el dueño no tiene que enterarse de
        // que su ficha arranca con opiniones (es lo que ve el cliente, y una ficha sin ninguna parece un
        // local vacío). Nunca rompe la publicación.
        $neg_nuevo = (int)($d['publicado']['negocio_id'] ?? 0);
        if ($neg_nuevo > 0) {
            try { tienda_ia_sembrar_opiniones($neg_nuevo, $d, tienda_ia_productos_publicados($neg_nuevo), (int)$s['id']); }
            catch (Throwable $e) { error_log('crear_tienda_final opiniones: ' . $e->getMessage()); }
        }
        // 🎉 Lo que quedó: el enlace de su tienda, **ya publicada** (aquí no se le pide aprobación ni
        // se le habla de «editar»). ⚠️ A propósito NO se usa el guion `publicado` del flujo viejo: ese
        // dice *«tu tienda todavía no tiene productos, vamos a agregar el primero»*, y en este flujo la
        // IA acaba de dejarle dos (el mensaje quedaba contradiciendo al de arriba).
        $url = (string)($d['publicado']['url'] ?? '');
        if ($url !== '') {
            $empujar("🎉 **¡Ya está en internet!** Mírala aquí 👇\n{$url}\n"
                   . "Si todavía no ves tus fotos, entra **a partir de las " . tienda_ia_hora_aviso() . "** ✅");
        }
        // 🔴 El paso se pone ANTES de guardar: si se pone después (como estaba), la conversación se
        // quedaba guardada en `rubro_mas` y el dueño volvía a ver el menú de rubros en vez de las
        // cuatro opciones de edición (lo cazó la prueba 13 del 2026-09-15).
        $s['paso']  = 'editar';
        $s['datos'] = $d;
        tienda_ia_guardar($s);
        $g = tienda_ia_guion('editar', $d);
        return ['ok' => true, 'mensajes' => $msgs, 'paso' => (string)$s['paso'], 'datos' => $d,
                'tipo' => $g['tipo'], 'opciones' => $g['opciones'], 'publicado' => ($d['publicado'] ?? null)];
    }
}

if (!function_exists('tienda_ia_guion')) {
    /**
     * Lo que el asistente dice y las opciones que ofrece en cada paso.
     * @return array{texto:string,opciones:array,tipo:string}
     */
    function tienda_ia_guion($paso, array $d, array $extra = []) {
        // ⚡ REGLA DE ORO DE ESTOS TEXTOS (orden del jefe, 2026-09-14 noche): *«solamente con leer la
        // entrada ya me aburriste… no se ve práctico, no se ve rápido; tienes que buscar ser
        // minimalista, rápido, sin tanto detalle: cambia los textos, hazlo más dinámico, más directo.»*
        // → 1 o 2 líneas por mensaje, sin explicar lo que ya se entiende, sin listas largas.
        $nom = $d['nombre'] !== '' ? $d['nombre'] : 'tu tienda';
        $opc = [];
        $tipo = 'texto';
        $texto = '';
        // 📷 El paso de las fotos del arranque sale SIN el botón de cámara (orden del jefe: la galería
        // se abre sola). El resto de pasos de foto siguen con sus dos botones, como él los pidió.
        $solo_galeria = false;

        switch ($paso) {

            /* 🚪 EL ARRANQUE: aquí se nota si la persona está logueada o no (orden del jefe, 2026-09-15).
               Sin cuenta (o con cuenta pero sin tiendas) la invitación es a crear su PRIMERA tienda;
               con tiendas ya hechas, se le pregunta qué quiere hacer hoy. Todo lo decide el servidor. */
            case 'arranque': {
                $tipo  = 'opciones';
                $quien = trim((string)($extra['primero'] ?? ''));
                $mias  = (array)($extra['tiendas'] ?? []);
                $hola  = ($quien !== '') ? "¡Hola, {$quien}! 👋" : '¡Hola! 👋';
                // 📍🆕 ORDEN DEL JEFE (2026-09-20): *«cuando carga, lo primero que va a aparecer es un
                // botón pidiendo ubicación, eso es lo primero; la ubicación como primer dato es vital»*.
                // → Para quien va a crear su tienda (sin tiendas todavía), **la PRIMERA pantalla ya es
                // la ubicación**: el botón MORADO, sin ningún paso previo ("crear mi tienda").
                $ubicacion_chip = ['texto' => '📍 Ubicación', 'valor' => 'gps', 'morado' => true, 'principal' => true];
                if (empty($extra['logueado']) || !$mias) {
                    // 🔴 ORDEN DEL JEFE (2026-09-15 noche): *«se va a presentar y va a decir: hola, vamos
                    // a crear tu primera tienda, necesito que subas ocho fotos de tu negocio…»* — y,
                    // desde el 2026-09-20, **antes de las fotos va la UBICACIÓN**.
                    $texto = $hola . " Soy " . TIENDA_IA_EMOJI . " **" . TIENDA_IA_NOMBRE . "**.\n"
                           . "**Vamos a crear tu tienda** 🚀 Son dos cosas: **tu ubicación** 📍 y **"
                           . (int)TIENDA_IA_FOTOS_ARRANQUE_MIN . " fotos** de tu negocio (la parte de afuera 🏪, "
                           . "tus productos 📦, tus servicios…).\n"
                           . (!empty($extra['insistir'])
                              ? "Sin tu ubicación no puedo seguir 🙏 Toca el botón morado **o escríbeme tu distrito** (Chimbote, Nuevo Chimbote, Coishco o Santa)."
                              : "**Empecemos por tu ubicación** 👇");
                    $opc = [$ubicacion_chip];
                    if (empty($extra['logueado'])) {
                        $opc[] = ['texto' => '🔑 Ya tengo cuenta', 'valor' => 'entrar', 'url' => url('login.php'), 'misma' => true];
                    }
                } else {
                    // 🏪 El dueño que YA tiene tiendas: elige qué hacer hoy (y si crea otra, ahí sí se le
                    // pide la ubicación de la nueva).
                    $nombres = [];
                    foreach (array_slice($mias, 0, 3) as $t) $nombres[] = '«' . (string)($t['texto'] ?? '') . '»';
                    $texto = $hola . " Ya tienes **" . count($mias) . (count($mias) === 1 ? ' tienda' : ' tiendas') . "**: "
                           . implode(', ', $nombres) . (count($mias) > 3 ? '…' : '') . ".\n¿Qué hacemos hoy?";
                    $opc = [
                        ['texto' => '🚀 Crear otra tienda', 'valor' => 'nueva', 'principal' => true],
                        ['texto' => '🛍️ Agregar un producto', 'valor' => 'producto'],
                    ];
                }
                break;
            }

            /* 📍🆕 LO PRIMERO ES LA UBICACIÓN (orden del jefe, 2026-09-20)
               Textual: *«cuando carga, lo primero que va a aparecer es un botón pidiendo ubicación, eso
               es lo primero; luego aparece el texto ya con la ubicación recibida (ya tenemos la calle, ya
               tenemos el distrito) y ya luego recién preguntamos que suban las fotos: pedimos ocho fotos
               siempre. La ubicación como primer dato es vital… y que aparezca dentro de la conversación
               el botón de ubicación, en color morado, que diga "ubicación". Si no comparte su ubicación,
               pues no continúa, así de simple»*.
               · El botón MORADO (`morado`) abre el GPS (`gps`) y el servidor saca el distrito real.
               · Si no comparte, se le insiste UNA vez con el mismo botón… y si tampoco, se le deja
               **escribir su distrito** (elección del jefe del 2026-09-20) para que nadie quede atrapado. */
            case 'ubicacion': {
                $tipo = 'opciones';
                if (!empty($extra['insistir'])) {
                    $texto = "Sin tu ubicación no puedo seguir 🙏\n"
                           . "Toca el botón morado **o escríbeme tu distrito** (Chimbote, Nuevo Chimbote, Coishco o Santa).";
                } else {
                    $texto = "📍 **Lo primero: tu ubicación.**\nToca el botón morado y me llega tu zona solita.";
                }
                $opc = [['texto' => '📍 Ubicación', 'valor' => 'gps', 'morado' => true, 'principal' => true]];
                break;
            }

            /* 📷🆕 EL PASO DE LAS FOTOS DEL ARRANQUE (2026-09-15, orden del jefe). Aquí NO hay botón
               de cámara: el carrete del celular ya ofrece «Tomar foto», y el jefe pidió que se abra la
               galería directamente (el `solo_galeria` es lo que apaga ese botón en la barra). */
            case 'fotos': {
                $tipo = 'foto';
                $solo_galeria = true;
                $n    = count($d['fotos']);
                $min  = (int)TIENDA_IA_FOTOS_ARRANQUE_MIN;
                if ($n === 0) {
                    // 📸 LA LISTA DE FOTOS QUE PIDIÓ EL JEFE (textual: *«fotos de la parte de afuera de
                    // tu tienda, dentro de tu tienda, de tus productos, de las máquinas que usas, de tu
                    // personal, si tienes una tarjeta tómale fotos y si tienes un folleto también»*).
                    $texto = "**Mándame {$min} fotos de tu negocio** 📷\n" . TIENDA_IA_TEXTO_FOTOS_ARRANQUE;
                } elseif ($n < $min) {
                    // 🔴 ORDEN DEL JEFE: *«si te dice que solo tiene cuatro, dile que todavía no lo
                    // podemos hacer, solamente trabajamos a partir de 8 fotos»*.
                    $texto = "Todavía **no podemos empezar** 📸 Trabajamos **a partir de " . $min . " fotos**.\n"
                           . "Llevas **{$n}** y te faltan **" . ($min - $n) . "**: mándamelas (el letrero de afuera, "
                           . "tus productos, tu local, tu personal…) y sigo.";
                } else {
                    $texto = "¡Ya tengo tus **{$n}** fotos! 📸";
                }
                break;
            }

            /* 🏪🆕 EL TIPO DE NEGOCIO (orden del jefe, 2026-09-15 noche): *«luego que pidas las ocho
               fotos le vas a preguntar qué tipo de negocio es: una tienda o un local, un vendedor
               ambulante, o una persona que vende por internet»*. Tres opciones y cada una tiene su
               rama: la tienda pide su ubicación, el ambulante su horario, el de internet sus distritos. */
            case 'tipo': {
                $tipo  = 'opciones';
                $texto = "🏪 **¿Qué tipo de negocio es?** Toca el tuyo 👇";
                $opc = [
                    ['texto' => '🏪 Una tienda o local', 'valor' => 'fisica', 'principal' => true],
                    ['texto' => '🛵 Vendedor ambulante', 'valor' => 'ambulante'],
                    ['texto' => '🌐 Vendo por internet', 'valor' => 'internet'],
                ];
                break;
            }

            /* 🚚🆕 LOS DISTRITOS DE ENTREGA (solo para el que vende por internet). El jefe lo pidió con
               miniatura y en grilla de 2×2, y debajo el botón azul de «Todos los distritos»:
               *«escribe con miniaturas Santa, Chimbote, Nuevo Chimbote y Coishco procurando que quepan
               todas en una grilla de 2 por 2 y abajo con color azul todos los distritos»*. */
            case 'entregas': {
                $tipo = 'opciones';
                $ya   = array_map('intval', (array)($d['entregas'] ?? []));
                $texto = ($ya ? "🚚 Ya marqué **" . count($ya) . "** distrito" . (count($ya) === 1 ? '' : 's') . ".\n"
                              : "")
                       . "**¿En qué distritos entregas tus productos?** Toca todos los que repartes 👇";
                $opc = [];
                foreach (tienda_ia_distritos() as $dist) {
                    $id    = (int)($dist['valor'] ?? 0);
                    $marca = in_array($id, $ya, true) ? '✅ ' : '';
                    $opc[] = [
                        'texto'   => $marca . (string)$dist['texto'],
                        'valor'   => (string)$id,
                        'tarjeta' => true,                                   // la pinta el navegador como tarjeta con miniatura
                        'color'   => tienda_ia_distrito_color((string)$dist['texto']),
                    ];
                }
                $opc[] = ['texto' => '🗺️ Todos los distritos', 'valor' => 'todos', 'azul' => true, 'principal' => empty($ya)];
                if ($ya) $opc[] = ['texto' => '✅ Ya está, seguir', 'valor' => 'seguir', 'principal' => true];
                break;
            }

            /* ✏️🆕 EL MENÚ DE EDICIÓN DEL FINAL (orden del jefe: *«"editar mis productos" o "editar otra
               cosa"… en una grilla de dos columnas, o también la opción de no editar nada»*). */
            case 'editar': {
                $tipo  = 'opciones';
                $prods = [];
                foreach ((array)($d['productos'] ?? []) as $p) {
                    if (!empty($p['titulo']) && empty($p['borrado'])) $prods[] = (string)$p['titulo'];
                }
                $texto = "🎉 **¡Ya está tu tienda!**";
                if ($prods) {
                    $texto .= " Le puse " . count($prods) . " producto" . (count($prods) === 1 ? '' : 's') . ": «"
                            . implode('» y «', array_slice($prods, 0, 3)) . "».";
                }
                $texto .= "\n**¿Quieres cambiar algo?**";
                $opc = [
                    ['texto' => '🛍️ Editar mis productos', 'valor' => 'productos', 'principal' => true, 'rejilla' => 2],
                    ['texto' => '🖼️ Editar la portada',    'valor' => 'portada',   'rejilla' => 2],
                    ['texto' => '📝 Editar otro dato',     'valor' => 'datos',     'rejilla' => 2],
                    ['texto' => '✅ No editar nada',        'valor' => 'nada',      'rejilla' => 2],
                ];
                break;
            }

            /* 🛍️🆕 Elegir CUÁL de sus productos quiere cambiar (solo los que ya tiene). */
            case 'producto_elegir': {
                $tipo = 'opciones';
                $opc  = [];
                foreach ((array)($d['productos'] ?? []) as $i => $p) {
                    if (empty($p['titulo']) || !empty($p['borrado'])) continue;
                    $opc[] = ['texto' => '🛍️ ' . (string)$p['titulo'], 'valor' => (string)$i];
                }
                $opc[] = ['texto' => '⬅️ Volver', 'valor' => 'volver'];
                $texto = "¿Cuál de tus productos quieres cambiar? 👇";
                break;
            }

            /* ✏️🆕 Qué le cambiamos a ESE producto: la foto o la descripción. */
            case 'producto_editar': {
                $tipo = 'opciones';
                $i    = (int)($d['producto_editando'] ?? 0);
                $prod = (string)($d['productos'][$i]['titulo'] ?? 'tu producto');
                $texto = "✏️ **«{$prod}»**\n¿Qué le cambiamos?";
                $opc = [
                    ['texto' => '📷 Cambiar la foto',        'valor' => 'foto',  'principal' => true, 'rejilla' => 2],
                    ['texto' => '📝 Cambiar la descripción', 'valor' => 'texto', 'rejilla' => 2],
                    ['texto' => '✅ Ya está',                'valor' => 'listo', 'rejilla' => 2],
                    ['texto' => '⬅️ Volver',                'valor' => 'volver', 'rejilla' => 2],
                ];
                break;
            }

            /* 🖼️🆕 LA PORTADA: se le pide una foto nueva o una de las suyas (el jefe prefiere que suba
               una: *«preferible que te suban una foto para su portada»*). */
            case 'portada': {
                $tipo = 'foto';
                $texto = "🖼️ **¿Qué foto quieres de portada?** Es la que se ve primero en tu tienda.\n"
                       . "Súbela aquí o toca una de las que ya mandaste 👇";
                $opc = [['texto' => '✅ Así está bien', 'valor' => 'listo']];
                break;
            }

            /* 📷🆕 La foto nueva de un producto que ya existe. */
            case 'producto_editar_foto': {
                $tipo = 'foto';
                $i    = (int)($d['producto_editando'] ?? 0);
                $prod = (string)($d['productos'][$i]['titulo'] ?? 'tu producto');
                $texto = "📷 **Mándame la foto nueva de «{$prod}»**\nO toca una de las que ya subiste 👇";
                $opc = [['texto' => '✅ Así está bien', 'valor' => 'listo']];
                break;
            }

            /* 📝🆕 La descripción nueva de un producto: él escribe y la IA le da el formato. */
            case 'producto_editar_texto': {
                $tipo = 'texto';
                $i    = (int)($d['producto_editando'] ?? 0);
                $prod = (string)($d['productos'][$i]['titulo'] ?? 'tu producto');
                $texto = "✍️ **¿Cómo es «{$prod}»?** Escríbelo o dicta 🎙️ (una o dos líneas) y yo lo dejo bonito.";
                $opc = [['texto' => '✨ Que lo escriba la IA', 'valor' => 'ia']];
                break;
            }

            /* ✅ EL NOMBRE QUE LA IA LEYÓ EN EL LETRERO: se confirma con un toque (o se corrige). */
            case 'nombre_ok': {
                $tipo  = 'opciones';
                $leido = (string)$d['letrero'];
                $texto = "📷 En tu letrero leo: **«{$leido}»**\n¿Así se llama tu tienda?";
                $opc = [
                    ['texto' => '✅ Sí, así se llama', 'valor' => 'si', 'principal' => true],
                    ['texto' => '✏️ Es otro nombre', 'valor' => 'otro'],
                ];
                break;
            }

            /* 🏷️ EL RUBRO QUE LA IA DEDUJO DE LAS FOTOS: los chips son rubros REALES del directorio. */
            case 'rubro_ok': {
                $tipo  = 'opciones';
                $leido = (string)$d['rubro_leido'];
                $texto = ($leido !== '')
                    ? "🏷️ Por tus fotos diría que tu tienda es de **{$leido}**.\n¿Le atino? 👇"
                    : "🏷️ **¿De qué es tu tienda?** Toca su rubro o categoría 👇";
                $opc = (array)($d['rubro_opciones'] ?? []);
                $opc[] = ['texto' => '✏️ Ninguno de estos', 'valor' => 'otro'];
                break;
            }

            /* 📱 EL NÚMERO QUE YA EXISTE: se PREGUNTA antes de tocar nada (orden del jefe, 2026-09-15:
               *«se puede dañar una tienda que ya exista… preguntaría si es el número correcto»*). Aquí
               NO se ha publicado nada todavía: la tienda de prueba no existe hasta que confirme. */
            case 'tel_ocupado': {
                $tipo = 'opciones';
                $suyo = (string)($d['tel_ocupado_nombre'] ?? '');
                $texto = ($suyo !== '')
                    ? "📱 Ese número ya tiene la tienda **«{$suyo}»**.\n¿Es tuya?"
                    : "📱 Ese número ya está registrado en DeChimbote.com.\n¿Es tuyo?";
                $opc = [
                    ['texto' => '✅ Sí, es mi número', 'valor' => 'si', 'principal' => true],
                    ['texto' => '🆕 No, me equivoqué de número', 'valor' => 'no'],
                ];
                break;
            }

            /* 📍 LA DIRECCIÓN (campo obligatorio de la página de registro si el negocio es local o
               ambulante: *«si no sacas datos de ahí, copia los datos que pide registrar_negocio.php»*). */
            case 'direccion': {
                $tipo  = 'opciones';
                $leida = (string)$d['direccion_leida'];
                if ($leida !== '' && (string)$d['direccion'] === '') {
                    $texto = "📍 En tus fotos leí esta dirección: **{$leida}**\n¿Es la tuya?";
                    $opc = [
                        ['texto' => '✅ Sí, es esa', 'valor' => 'si', 'principal' => true],
                        ['texto' => '✏️ Es otra', 'valor' => 'otra'],
                        ['texto' => '⏭️ Después', 'valor' => 'despues'],
                    ];
                } else {
                    $texto = "📍 **¿Cuál es tu dirección?** (la calle y el número, o una referencia: «frente al mercado»)\n"
                           . "Así sales en el mapa y en «cerca de mí».";
                    $opc = [['texto' => '⏭️ Después', 'valor' => 'despues']];
                }
                break;
            }

            /* 📘 LAS REDES (los últimos campos que pide la página de registro y que el asistente no
               preguntaba). Es UNA sola pregunta para las tres, y se puede saltar. */
            case 'redes': {
                $texto = "📘 ¿Tienes **Facebook, Instagram o TikTok**?\n"
                       . "Pásame los que uses (o toca «Después») y los pongo en tu tienda.";
                $opc = [['texto' => '⏭️ Después', 'valor' => 'despues']];
                break;
            }

            /* 📱 EL TELÉFONO QUE SE LEYÓ EN UN AVISO: se PREGUNTA siempre (puede ser el de la imprenta). */
            case 'tel_leido': {
                $tipo = 'opciones';
                $texto = "📱 En tus fotos leí este número: **" . (string)$d['telefono_leido'] . "**\n"
                       . "¿Es tu WhatsApp? (a veces es el de la imprenta que hizo tu letrero 😅)";
                $opc = [
                    ['texto' => '✅ Sí, es mi WhatsApp', 'valor' => 'si', 'principal' => true],
                    ['texto' => '✍️ No, es otro', 'valor' => 'no'],
                ];
                break;
            }

            case 'tienda':   // modo «ya tengo una tienda»: elegir a cuál
                $tipo = 'opciones';
                $texto = "¿A cuál de tus tiendas le agregamos el producto?";
                $opc = $extra['tiendas'] ?? [];
                break;

            case 'nombre':
                $texto = "**¿Cómo se llama tu tienda?** 😎\n"
                       . "El nombre del negocio, no lo que vendes (así sale en DeChimbote.com).";
                break;

            case 'trato':
                $texto = "**Cuéntame de «{$nom}»**: qué vendes, a quién y dónde atiendes.\n"
                       . "Si prefieres, toca el 🎙️ y me lo dices hablando.";
                break;

            case 'rubro':
                $tipo = 'opciones';
                // 🔴 Orden del jefe (2026-09-15): se pide el PRINCIPAL y se le avisa que puede sumar
                // otros rubros o categorías (hasta 4). Siempre con la palabra «rubros o categorías».
                $texto = "🏷️ **Elige el rubro principal** de tu tienda 👇\n"
                       . "Si deseas, puedes agregar también otros rubros o categorías: **hasta 4** en total.";
                $opc = $extra['opciones'] ?? ($d['rubro_opciones'] ?? []);
                break;

            case 'rubro_mas': {
                $tipo  = 'opciones';
                $max   = (int)TIENDA_IA_RUBROS_MAX;
                $tengo = [];
                if ((string)$d['rubro_nombre'] !== '') $tengo[] = (string)$d['rubro_nombre'];
                foreach ((array)$d['rubros_extra'] as $r) $tengo[] = (string)($r['nombre'] ?? '');
                $tengo = array_values(array_filter($tengo));
                $cuantos = count($tengo);
                $texto = ($cuantos <= 1)
                    ? "Ya tienes el principal: **" . ($tengo[0] ?? '—') . "** 🏷️\n"
                      . "¿Le sumas **otros rubros o categorías**? Puedes tener hasta **{$max}** en total."
                    : "🏷️ Hasta ahora: **" . implode(' + ', $tengo) . "** ({$cuantos} de {$max}).\n"
                      . ($cuantos < $max ? '¿Le sumas otro rubro o categoría?' : '¡Ya tienes los ' . $max . '!');
                $opc = [];
                foreach ((array)($extra['opciones'] ?? ($d['rubros_todos'] ?? [])) as $o) $opc[] = $o;
                // Si ya llegó al tope, el guion se queda solo con las opciones de seguir.
                $opc[] = ['texto' => ($cuantos > 1 ? '✅ Listo, seguimos' : '➡️ Solo el principal, seguimos'), 'valor' => 'seguir', 'principal' => true];
                break;
            }

            case 'fotos_tienda':
                $tipo  = 'foto';
                $n     = count($d['fotos']);
                $max   = (int)TIENDA_IA_FOTOS_TIENDA_MAX;
                $min   = (int)TIENDA_IA_FOTOS_TIENDA_MIN;
                $ideal = (int)TIENDA_IA_FOTOS_TIENDA_IDEAL;
                if ($n === 0) {
                    // 🔴 El jefe (2026-09-15): LIBERTAD para subir de 1 a 8 fotos, y siempre claro
                    // que ahora es LA TIENDA (los productos van después, dentro de ella).
                    $texto = "**Ahora las fotos de tu tienda** 📷\n"
                           . "El letrero de afuera, el frente, tus mesas, tu cocina… **de 1 a {$max}**, tú eliges.\n"
                           . "Estás creando **la tienda**: los productos van después 😉";
                } elseif ($n < $ideal) {
                    $texto = "**{$n}** foto" . ($n === 1 ? '' : 's') . " 📸 Con " . $ideal . " se ve mejor: mándame " . ($ideal - $n) . " más… o seguimos si ya estás.";
                } else {
                    $texto = "¡Ya llevas **{$n}** fotos! 📸 ¿Seguimos o subes más?";
                }
                $opc = [];
                if ($n >= $min) $opc[] = ['texto' => '✅ Listo', 'valor' => 'seguir', 'principal' => true];
                // 🔴 «subir más fotos», no «subir más» (orden del jefe, textual: *«lo que debe decir
                // es "subir más fotos" cuando se refiere a la galería»*).
                if ($n < $max)  $opc[] = ['texto' => '🖼️ Subir más fotos', 'valor' => 'otra'];
                // 🚚 La IA vio que esto no es un local con clientes sino un servicio que se lleva
                // (camiones, arena, piedra, delivery): se le ofrece decirlo con un toque, sin tener
                // que escribir nada (orden del jefe: *«ahí no le estamos dando al usuario ninguna
                // opción; podría decir "no tengo local" y entonces le ofrecemos entregas a domicilio»*).
                if (!empty($d['sugerir_domicilio'])) {
                    $opc[] = ['texto' => '🚚 No tengo local: lo llevo a su casa', 'valor' => 'sin_local'];
                }
                break;

            case 'vendedor':
                $tipo = 'opciones';
                $texto = "**¿Cómo le llega a tu cliente?**";
                // Los MISMOS tipos de ubicación que pide la página de registro (`registrar_negocio.php`):
                // fisica · ambulante · domicilio · nacional · mayorista.
                $opc = [
                    ['texto' => '🏪 Local fijo', 'valor' => 'fisica'],
                    ['texto' => '🛵 Ambulante', 'valor' => 'ambulante'],
                    ['texto' => '🏠 Lo llevo a tu casa', 'valor' => 'domicilio'],
                    ['texto' => '🚚 Vendo a todo el país', 'valor' => 'nacional'],
                    ['texto' => '📦 Vendo al por mayor', 'valor' => 'mayorista'],
                ];
                // 🚚 La IA vio en las fotos que es un servicio que se LLEVA (camiones, arena, reparto):
                // el chip se lo dice con un toque, sin que tenga que explicarlo (orden del jefe).
                if (!empty($d['sugerir_domicilio'])) {
                    array_unshift($opc, ['texto' => '🚚 No tengo local: lo llevo a su casa', 'valor' => 'sin_local', 'principal' => true]);
                }
                break;

            case 'distrito':
                $tipo = 'opciones';
                $texto = "📍 **¿En qué zona estás?** Toca «Usar mi ubicación» y lo saco solito, o toca tu distrito 👇";
                // 🗺️ «Todas las anteriores» (pedido del jefe, 2026-09-15): un negocio que atiende en
                // toda la provincia (arena, ripio, delivery) lo dice con un toque. La tienda queda
                // con Chimbote de base y la nota de la zona se le añade a su descripción.
                $opc = [['texto' => '📍 Usar mi ubicación', 'valor' => 'gps', 'principal' => true]];
                // 📷 Si en las fotos se leyó una dirección con distrito («Av. Pardo 123, Nuevo
                // Chimbote»), ese distrito va PRIMERO para que confirme con un toque.
                $leido = tienda_ia_distrito_leido((string)($d['direccion_leida'] ?? ''));
                if ($leido) {
                    $texto = "📍 **¿En qué zona estás?** Por lo que dice tu letrero diría que en **" . (string)$leido['texto'] . "**.\n"
                           . "Confírmalo o toca otro 👇";
                    $opc[] = ['texto' => '📍 Sí, estoy en ' . (string)$leido['texto'], 'valor' => (string)$leido['valor'], 'principal' => true];
                }
                $opc[] = ['texto' => '🗺️ Todas las anteriores (atiendo en toda la zona)', 'valor' => 'todas'];
                foreach ((array)($extra['distritos'] ?? []) as $dchip) {
                    if ($leido && (string)$dchip['valor'] === (string)$leido['valor']) continue;   // no se repite
                    $opc[] = $dchip;
                }
                break;

            case 'whatsapp': {
                // 📱🆕 LA ÚLTIMA PREGUNTA (orden del jefe, 2026-09-15 noche: *«le preguntas su número de
                // WhatsApp, le dices: sería bueno que tus clientes también puedan llamarte o enviarte
                // mensajes de WhatsApp de tus pedidos; ¿a qué número deseas que te escriban?»*).
                $tipo  = 'opciones';
                $leido = (string)($d['telefono_leido'] ?? '');
                $texto = "📱 Una última cosa: **sería bueno que tus clientes también puedan llamarte o "
                       . "escribirte por WhatsApp** para sus pedidos.\n"
                       . "**¿A qué número quieres que te escriban?**";
                $opc = [];
                if ($leido !== '') $opc[] = ['texto' => '📱 ' . $leido . ' (el de mis fotos)', 'valor' => $leido, 'principal' => true];
                $opc[] = ['texto' => '⏭️ Por ahora no', 'valor' => 'despues'];
                break;
            }

            case 'horario':
                $tipo = 'opciones';
                $texto = "🕐 **¿En qué horario atiendes?**";
                $opc = [
                    ['texto' => '🌅 Mañanas', 'valor' => 'Mañanas'],
                    ['texto' => '🌇 Tardes', 'valor' => 'Tardes'],
                    ['texto' => '🌙 Noches', 'valor' => 'Noches'],
                    ['texto' => '🕐 Todo el día', 'valor' => 'Todo el día'],
                    ['texto' => '⏭️ Después', 'valor' => ''],
                ];
                break;

            case 'resumen':
                $tipo = 'resumen';
                $texto = "¡Listo! 📋 Míralo y si está bien, **publicamos** 👇";
                $opc = [
                    ['texto' => '🚀 PUBLICAR MI TIENDA', 'valor' => 'publicar', 'principal' => true],
                    ['texto' => '✏️ Cambiar algo', 'valor' => 'cambiar'],
                ];
                break;

            case 'publicado':
                // 🎉 La tienda YA ESTÁ publicada (la publicación es automática desde el 2026-09-15):
                // lo que se le muestra es la tarjeta de lo que quedó, con su enlace, y —si es la
                // primera vez— los datos para entrar y el aviso de que la tienda está vacía.
                $tipo = 'publicado';
                $url  = (string)($d['publicado']['url'] ?? '');
                $actualizada = !empty($d['publicado']['actualizada']);
                $cuenta = (array)($d['cuenta'] ?? []);
                $texto = ($actualizada
                            ? "✅ **Ya actualicé tu tienda «{$nom}».**\n{$url}\n\n"
                            : "🎉 **¡Listo! Tu tienda «{$nom}» ya está publicada!**\n{$url}\n\n")
                       . "📸 Tus fotos son **nítidas y claras**: las comprimo para que carguen al toque.\n"
                       . "⏱️ Dale unos minutitos: **a partir de las " . tienda_ia_hora_aviso() . "** ya se ve todo.";
                if (!$actualizada) {
                    // 🔴 Orden del jefe: avisar que la tienda está VACÍA y que se le asignó su cuenta,
                    // y que guarde la clave AHORA porque no se le vuelve a mostrar.
                    $texto .= "\n\n🛍️ Ojo: **tu tienda todavía no tiene productos.** Vamos a agregar el primero.";
                    if (!empty($cuenta['usuario'])) {
                        $texto .= "\n🔑 Te asigné **tu usuario y tu contraseña**: guárdalos aquí abajo 👇 "
                                . "La clave **no te la voy a poder volver a mostrar**.";
                    }
                } else {
                    $texto .= "\n\n¿Le agregamos **un producto nuevo**? 🛍️";
                }
                $opc = [
                    ['texto' => '🛍️ Crear mi primer producto', 'valor' => 'producto', 'principal' => true],
                    // 📷🆕 Mandar varias fotos y que la IA las clasifique (2026-09-16).
                    ['texto' => '📷 Mandar fotos de mis productos', 'valor' => 'fotos_lote'],
                    // 🆕 ORDEN DEL JEFE (2026-09-16): *«un usuario puede tener varias tiendas… un mismo
                    // usuario puede tener varios negocios»* → el botón para arrancar OTRA queda aquí,
                    // en la misma tarjeta donde termina la primera (antes no había ninguno y el dueño
                    // se quedaba encerrado en su tienda recién hecha).
                    ['texto' => '🆕 Crear otra tienda', 'valor' => 'otra_tienda'],
                    // 🔴 Orden del jefe (2026-09-15): *«no le digas que está editando: hazlo creer que
                    // recién está creando su sitio»* → el botón NO dice «editar».
                    ['texto' => '📝 Cambiar algún dato', 'valor' => 'cambiar'],
                    ['texto' => '🏪 Ver mi tienda', 'valor' => 'ver', 'url' => $url],
                ];
                break;

            case 'producto_nombre':
                // 🧮 La numeración es la del TOTAL de la conversación (`productos_total`): con las rondas,
                // `productos_ia` vuelve a 0 y sin esto el dueño volvía a leer «¿Cuál va a ser tu primer
                // producto?» cuando ya llevaba 4.
                $cuantos = (int)($d['productos_total'] ?? 0);
                if ($cuantos <= 0) $cuantos = (int)($d['productos_ia'] ?? 0);
                $tipo    = 'opciones';
                $texto = ($cuantos === 0)
                       ? "**¿Cuál va a ser tu primer producto?** Toca uno o escríbeme el tuyo 👇"
                       : "**Producto " . ($cuantos + 1) . ":** ¿cuál es? Toca uno o escríbeme el tuyo 👇";
                $opc = $d['producto_opciones'] ?? [];
                break;

            case 'producto_fotos':
                $tipo  = 'foto';
                $prod  = $extra['producto'] ?? '';
                $i     = (int)$d['producto_indice'];
                $n     = count($d['productos'][$i]['fotos'] ?? []);
                // 🚚 Si la tienda es de un servicio que se lleva (arena, ripio, piedra) no tiene
                // sentido pedir «una foto del plato»: se le pide la foto del producto o del camión.
                $texto = ($n === 0)
                       ? "📷 **Mándame la foto de {$prod}** (de cerquita, que se antoje).\n"
                         . "Puedes elegir varias de tu galería de una vez, o usar una de las fotos que ya subiste 👇"
                       : "¡Se ve bien! 😋 ¿Subes otra foto o le ponemos el precio?";
                $opc = [];
                if ($n >= 1) $opc[] = ['texto' => '💰 Poner el precio', 'valor' => 'seguir', 'principal' => true];
                // 🔴 Orden del jefe: nada de «otra» (no se entiende) — los botones dicen lo que hacen.
                if ($n < (int)TIENDA_IA_FOTOS_PRODUCTO_MAX) {
                    $opc[] = ['texto' => '📷 Abrir cámara', 'valor' => 'camara'];
                    $opc[] = ['texto' => '🖼️ Abrir galería', 'valor' => 'galeria'];
                }
                break;

            case 'producto_precio':
                $prod = $extra['producto'] ?? '';
                $tipo = 'opciones';
                $texto = "💰 **¿Cuánto cuesta {$prod}?** Escribe solo el número 👇";
                $opc = [['texto' => '🤔 A consultar', 'valor' => 'consultar']];
                break;

            case 'producto_listo':
                $tipo  = 'producto';
                $prod  = (string)($d['producto_borrado'] !== '' ? $d['producto_borrado'] : ($extra['producto'] ?? ''));
                $hechos = (int)($d['productos_ia'] ?? 0);
                $texto = "🎉 **¡«{$prod}» ya está en línea!** ✅\n"
                       . ($hechos === 1 ? "📸 Tu foto carga al toque.\n" : "")
                       . "\n¿Creamos otro producto?";
                // 🔴 SIN botón de eliminar (orden del jefe, 2026-09-15): *«no me sirve el botón de
                // eliminar… prefiero una publicación mal hecha que una publicación que no existe»*.
                // Y al segundo producto se termina (el «Quiero más productos» al WhatsApp del jefe se
                // retiró también): solo queda seguir o cerrar.
                $opc = [['texto' => '➕ Crear otro producto', 'valor' => 'otro', 'principal' => true],
                        // 📷🆕 La otra forma, la que pidió el jefe (2026-09-16): mandar VARIAS fotos y
                        // que la IA las clasifique de golpe.
                        ['texto' => '📷 Mandar fotos de mis productos', 'valor' => 'fotos_lote'],
                        ['texto' => '🏪 Ya está, gracias', 'valor' => 'fin']];
                break;

            /* 📷🛍️ LA TANDA DE FOTOS DE PRODUCTOS (2026-09-16, pedido del jefe): «la IA siempre pedirá
               fotos y dará opciones». El dueño manda varias fotos, la IA las clasifica, se publican todos
               los productos de un tirón y la IA reescribe el copy de la tienda con lo nuevo. */
            case 'productos_lote': {
                $tipo = 'foto';
                $mios = (int)($d['productos_total'] ?? 0);
                if ($mios <= 0) $mios = (int)($d['productos_ia'] ?? 0);
                $texto = ($mios > 0
                            ? "Llevas **{$mios}** producto" . ($mios === 1 ? '' : 's') . " cargado" . ($mios === 1 ? '' : 's') . " ✅\n"
                            : '')
                       . "📷 **Mándame las fotos de tus productos** —varias de una vez— y yo te los clasifico "
                       . "y los publico solos.";
                $opc = [];
                if ($mios > 0) $opc[] = ['texto' => '✅ Ya está, gracias', 'valor' => 'fin'];
                break;
            }

            case 'mas_productos':
                // Paso RETIRADO (2026-09-15): solo se atiende a las conversaciones que se quedaron aquí.
                $tipo   = 'publicado';
                $hechos = (int)($d['productos_ia'] ?? 0);
                $url    = (string)($d['publicado']['url'] ?? '');
                $texto  = "🎉 **¡Felicidades! Tu tienda «{$nom}» ya tiene {$hechos} productos.**\n"
                        . ($url !== '' ? "Mírala aquí 👇\n{$url}" : "Entra con tu número y tu clave a revisarla 👇");
                $opc = [];
                if ($url !== '') $opc[] = ['texto' => '🏪 Ver mi tienda', 'valor' => 'ver', 'url' => $url, 'principal' => true];
                $opc[] = ['texto' => '🆕 Crear otra tienda', 'valor' => 'otra_tienda'];
                $opc[] = ['texto' => '📝 Cambiar algún dato', 'valor' => 'cambiar'];
                break;

            case 'fin':
                $tipo  = 'publicado';
                $url   = (string)($d['publicado']['url'] ?? '');
                // 🎉 El cierre que pidió el jefe (2026-09-15): felicitación con lo que tiene la tienda
                // y EL ENLACE, sin ofrecerle el WhatsApp del administrador ni pedirle nada más.
                $prods = [];
                foreach ((array)($d['productos'] ?? []) as $p) {
                    if (!empty($p['titulo']) && empty($p['borrado'])) $prods[] = (string)$p['titulo'];
                }
                $cuantos = count($prods);
                $texto = ($cuantos > 0)
                       ? "🎉 **¡Felicidades! Tu tienda «{$nom}» ya tiene " . $cuantos . " producto" . ($cuantos === 1 ? '' : 's') . "** ("
                         . implode(', ', array_slice($prods, 0, 3)) . ").\n"
                       : "🎉 **¡Felicidades! Tu tienda «{$nom}» ya está publicada.**\n";
                if ($url !== '') {
                    $texto .= "Mírala aquí 👇\n{$url}\n"
                            . "Si todavía no ves tus fotos, entra **a partir de las " . tienda_ia_hora_aviso() . "** ✅";
                }
                // 🛍️🆕 SEGUIR CARGANDO PRODUCTOS (pedido del jefe, 2026-09-16: *«¿cómo puede el usuario
                // seguir creando más productos?»*). Antes aquí decía *«entra con tu número y tu clave y le
                // pides uno nuevo»*: un callejón sin salida (y sin enlace). Ahora tiene su BOTÓN, que
                // abre otra ronda, y la puerta del inventario (cámara + voz) para cargar muchos.
                $texto .= "\n🛍️ **¿Quieres más productos?** Toca **Agregar otro producto** y te lo dejo listo.";
                $neg_id = (int)($d['publicado']['negocio_id'] ?? $d['negocio_id'] ?? 0);
                if ($neg_id > 0) {
                    $texto .= "\n📦 ¿Son **muchos**? Cárgalos tú con la cámara y la voz en tu inventario:\n"
                            . url('productos.php?n=' . $neg_id . '#crear');
                }
                // 🆕 ¿Tiene OTRO negocio? (orden del jefe, 2026-09-16): se le dice aquí mismo y con su
                // botón, sin tener que volver a entrar por la puerta del sitio.
                $texto .= "\n\n🏪 **¿Tienes otro negocio?** También le armo su tienda, aparte de esta 👇";
                $opc   = [];
                if ($url !== '') $opc[] = ['texto' => '🏪 Ver mi tienda', 'valor' => 'ver', 'url' => $url, 'principal' => true];
                $opc[] = ['texto' => '🛍️ Agregar otro producto', 'valor' => 'producto'];
                // 📷🆕 Mandar varias fotos y que la IA las clasifique (2026-09-16, pedido del jefe).
                $opc[] = ['texto' => '📷 Mandar fotos de mis productos', 'valor' => 'fotos_lote'];
                $opc[] = ['texto' => '🆕 Crear otra tienda', 'valor' => 'otra_tienda'];
                // Sigue estando la puerta para cambiar un dato: la tienda está en línea y se corrige al
                // toque (sin decirle «editar»: para él sigue siendo su tienda recién hecha).
                $opc[] = ['texto' => '📝 Cambiar algún dato', 'valor' => 'cambiar'];
                break;
        }

        return ['texto' => $texto, 'opciones' => $opc, 'tipo' => $tipo, 'solo_galeria' => $solo_galeria];
    }
}

/* =====================================================================================
 * 7) LA IA POR PASO (cada tarea, con su respaldo local)
 * ===================================================================================== */

if (!function_exists('tienda_ia_ia_nombre')) {
    /**
     * ¿Lo que escribió es el NOMBRE de la tienda o lo que vende? (pedido del jefe:
     * *«primero pregunte claramente cómo se llama tu tienda, indicando al usuario que
     * no se trata de los productos sino de la tienda»*).
     * Formato pedido: primera línea SI o NO, después una frase corta.
     */
    function tienda_ia_ia_nombre(array $d, $texto, array $op = []) {
        $peticion = "El dueño está escribiendo el NOMBRE de su tienda (NO lo que vende).\n"
                  . "Él escribió: «{$texto}»\n\n"
                  . "Contesta en DOS líneas exactas, sin nada más:\n"
                  . "LÍNEA 1: SI (si sirve como nombre de tienda, aunque sea sencillo) o NO (si en realidad dijo lo que vende, o es una frase larga).\n"
                  . "LÍNEA 2: UNA frase cortísima (máximo 12 palabras). Si es SI, aprueba el nombre repitiéndolo. "
                  . "Si es NO, explícale con cariño que eso es lo que VENDE y que ahora necesitas el NOMBRE, y dale dos ejemplos.";
        $r = tienda_ia_llamar($peticion, array_merge(['paso' => 'nombre', 'max_tokens' => 200], $op));
        if (empty($r['ok'])) return null;
        $lineas = tienda_ia_lineas($r['texto']);
        $si = isset($lineas[0]) && stripos($lineas[0], 'SI') === 0;
        $frase = $lineas[1] ?? '';
        if ($frase === '' && isset($lineas[0]) && mb_strlen($lineas[0]) > 12) $frase = $lineas[0];
        return ['ok' => $si, 'frase' => tienda_ia_limpiar_largo($frase, 200)];
    }
}

if (!function_exists('tienda_ia_ia_rubro')) {
    /**
     * Con el relato del dueño, elige los rubros REALES de la lista de candidatos
     * (los candidatos los saca el motor local con las palabras clave del rubro).
     * Devuelve [ids..., frase]. Si falla, el motor usa el mejor candidato local.
     */
    function tienda_ia_ia_rubro(array $d, $texto, array $candidatos, array $op = []) {
        $lista = [];
        foreach ($candidatos as $c) { $lista[] = (int)$c['id'] . ' ' . $c['nombre']; }
        $peticion = "El dueño contó de qué trata su tienda («{$d['nombre']}»): «{$texto}»\n\n"
                  . "De esta LISTA, elige los rubros que mejor le quedan (el mejor primero, máximo 3, separados por coma). "
                  . "Si ninguno sirve escribe NINGUNO.\n\n"
                  . "LISTA: " . implode(' | ', $lista) . "\n\n"
                  . "Contesta en DOS líneas exactas, sin nada más:\n"
                  . "LÍNEA 1: solo los números (por ejemplo: 17, 12, 3) o NINGUNO.\n"
                  . "LÍNEA 2: UNA frase cortísima (máximo 12 palabras) que demuestre que entendiste qué vende.";
        $r = tienda_ia_llamar($peticion, array_merge(['paso' => 'trato', 'max_tokens' => 250], $op));
        if (empty($r['ok'])) return null;
        $lineas = tienda_ia_lineas($r['texto']);
        $ids = [];
        if (preg_match_all('/\d+/', (string)($lineas[0] ?? ''), $m)) {
            $validos = array_map(fn($c) => (int)$c['id'], $candidatos);
            foreach ($m[0] as $n) { if (in_array((int)$n, $validos, true)) $ids[] = (int)$n; }
        }
        return [
            'ids'   => array_values(array_unique($ids)),
            'frase' => tienda_ia_limpiar_largo($lineas[1] ?? '', 200),
        ];
    }
}

if (!function_exists('tienda_ia_ia_producto')) {
    /**
     * Tres productos concretos que este negocio podría vender.
     * 🔴 ORDEN DEL JEFE (2026-09-15): *«le vamos a preguntar si desea tales productos… unos cuatro o
     * cinco productos que le podamos crear **en base a sus fotos**»*. Por eso el pedido lleva lo que
     * la IA **vio** en las fotos de la tienda (`$d['visto']`): así las propuestas salen de lo que el
     * dueño mostró (su mostrador, su cocina, sus ollas) y no solo de lo que escribió.
     */
    function tienda_ia_ia_producto(array $d, array $op = []) {
        $ya = [];
        foreach (($d['productos'] ?? []) as $p) { if (!empty($p['titulo'])) $ya[] = $p['titulo']; }
        $visto = [];
        foreach ((array)($d['visto'] ?? []) as $v) { if (trim((string)$v) !== '') $visto[] = '- ' . trim((string)$v); }
        // ⚠️ TRAMPA (la cazó la prueba 8 del 2026-09-15): cuando el dueño YA tiene su tienda y vuelve
        // a agregar un producto, su conversación nueva no trae relato (`trato` vacío) → la IA contestaba
        // «No puedo proponer productos porque el dueño no contó nada» ¡y esa frase salía como opción
        // para tocar! Ahora, si no hay relato, se usa la DESCRIPCIÓN que él ya le puso a su tienda.
        $contexto = trim((string)$d['trato']);
        if ($contexto === '' && !empty($d['negocio_id'])) {
            try {
                $st = db()->prepare("SELECT nombre, descripcion FROM directorio_negocios WHERE id = ? LIMIT 1");
                $st->execute([(int)$d['negocio_id']]);
                $f = $st->fetch(PDO::FETCH_ASSOC);
                if ($f) {
                    $contexto = trim((string)($f['descripcion'] ?? ''));
                    if ((string)$d['nombre'] === '') $d['nombre'] = (string)$f['nombre'];
                }
            } catch (Throwable $e) {}
        }
        // Sin nada que mirar (ni relato, ni fotos, ni descripción) NO se le pregunta a la IA.
        if ($contexto === '' && !$visto) return null;

        $peticion = "La tienda se llama «{$d['nombre']}»" . ($contexto !== '' ? " y su dueño contó: «{$contexto}»" : '') . ".\n"
                  . ($visto ? "En las FOTOS de su tienda se vio esto:\n" . implode("\n", array_slice($visto, 0, 5)) . "\n" : '')
                  . ($ya ? "Ya anotamos estos productos: " . implode(', ', $ya) . ".\n" : '')
                  . "Propón hasta 3 productos CONCRETOS que este negocio podría vender, sacados de lo que él contó "
                  . ($visto ? "y de lo que se ve en sus fotos " : '')
                  . "(no inventes cosas que no dijo ni que no se vean). Cortos: de 1 a 4 palabras cada uno.\n\n"
                  . "Contesta SOLO con las líneas de los productos, así:\n1. <producto>\n2. <producto>\n3. <producto>\n"
                  . "Sin títulos, sin explicaciones, sin precios.";
        $r = tienda_ia_llamar($peticion, array_merge(['paso' => 'producto_nombre', 'max_tokens' => 150], $op));
        if (empty($r['ok'])) return null;
        $out = [];
        foreach (tienda_ia_lineas($r['texto']) as $l) {
            $l = preg_replace('/^\s*\d+\s*[\.\)\-:]\s*/u', '', $l);
            $l = tienda_ia_limpiar_largo($l, 60);
            // 🚫 Un producto se llama con 1 a 4 palabras. Si la IA se puso a EXPLICAR (una frase
            //    larga, del tipo «No puedo proponer productos porque…»), eso NO es un producto y no
            //    puede salir como botón para tocar (lo cazó la prueba 8 del 2026-09-15).
            $palabras = tienda_ia_palabras($l);
            if ($palabras < 1 || $palabras > 4 || mb_strlen($l) > 40) continue;
            // ⚠️ Se devuelven ya en el formato de las opciones tocables (['texto','valor']):
            //    devolverlas como texto suelto rompía la conversación (lo cazó la prueba del
            //    2026-09-14: `$o['texto']` sobre una cadena = error fatal en PHP 8).
            if ($l !== '' && mb_strlen($l) >= 3 && count($out) < 3) $out[] = ['texto' => $l, 'valor' => $l];
        }
        return $out ?: null;
    }
}

if (!function_exists('tienda_ia_ia_fotos')) {
    /**
     * MIRA UN LOTE DE FOTOS (1 a 8) EN UNA SOLA LLAMADA y contesta como persona: qué ve de verdad
     * y qué foto pedir. Devuelve ['capturas'=>[0,2], 'texto'=>'…'] (índices DENTRO del lote).
     *
     * 🚫 REGLA INVIOLABLE DEL PROYECTO: una **captura de pantalla** jamás se publica (muestra las
     * pestañas y las direcciones del dueño). Por eso la línea 1 de la respuesta son los números de
     * las fotos que son captura; el que decide qué hacer con ellas es `tienda_ia_captura_decision()`.
     *
     * @param int $n_antes cuántas fotos había ANTES de este lote (para numerarlas de cara al dueño)
     */
    function tienda_ia_ia_fotos(array $d, $que, array $rels, $n_antes, $max, array $op = []) {
        $limpias = [];
        foreach ($rels as $rel) { if (is_string($rel) && $rel !== '') $limpias[] = $rel; }
        if (!$limpias) return null;
        $tope = max(1, (int)TIENDA_IA_FOTO_LOTE_IA);
        if (count($limpias) > $tope) $limpias = array_slice($limpias, 0, $tope);

        $nombre = $d['nombre'] !== '' ? $d['nombre'] : 'su tienda';
        $k      = count($limpias);
        $primera = (int)$n_antes + 1;
        $ultima  = (int)$n_antes + $k;
        $cuales  = ($k === 1)
            ? "la foto N.º {$primera} de su tienda"
            : "las fotos N.º {$primera} a {$ultima} de su tienda (te llegan numeradas en orden: 1 es la primera y {$k} la última)";

        if ($que === 'tienda') {
            $tarea = "El dueño de «{$nombre}» te acaba de mandar {$cuales} (contó que: «{$d['trato']}»).\n"
                   . "Cuéntale en UNA o DOS frases cortas QUÉ VES de verdad (el letrero, el frente, el mostrador, las sillas, la cocina, los camiones, la mercadería, si hay gente) "
                   . "y pídele, si le falta, una foto más. Nunca inventes lo que no se ve.\n"
                   . "⚠️ PIENSA ANTES DE PEDIR: si lo que ves son camiones, volquetes, montones de arena, piedra o ripio, "
                   . "o cualquier cosa que se LLEVA a la casa del cliente, NO le pidas «la foto de tu local» (puede que no tenga local): "
                   . "pídele otra foto de su trabajo (la carga, la minicargadora, lo que entrega). "
                   . "Si se ve un local de verdad, sí pídele una foto del frente o de donde atiende.\n"
                   . "Si una foto es solo un letrero o un papel y no se ve nada más, dilo tal cual y pídele una foto real.";
        } else {
            $prod = $d['productos'][(int)$d['producto_indice']]['titulo'] ?? 'el producto';
            $tarea = "El dueño de «{$nombre}» te mandó {$cuales} de su producto «{$prod}».\n"
                   . "Dile en UNA o DOS frases cortas QUÉ VES con cariño (cómo se ve, la porción, el color, la cantidad) "
                   . "y pídele otra foto distinta"
                   . ($ultima < (int)$max ? ": más de cerquita, desde otro ángulo, o como si se lo mostraras a un cliente." : ", solo si tiene una mejor.")
                   . " Nunca inventes lo que no se ve.";
        }
        $tarea .= "\n\n⚠️ CAPTURA DE PANTALLA: es captura cuando se ve la PANTALLA de un celular o de una computadora "
                . "(barras de hora/batería/señal, pestañas de un navegador, la barra de direcciones, un cursor, o la ventana de WhatsApp/redes con sus botones). "
                . "⚠️ NO es una captura —y no la cuentes— cuando es la FOTO de un letrero, de un menú, de un afiche, de un papel o de una pizarra: "
                . "eso es una foto de verdad del negocio y sirve perfectamente."
                . "\n\nContesta EXACTAMENTE en tres líneas, sin nada más:\n"
                . "LÍNEA 1: los números de las fotos que sean CAPTURA, separados por coma (por ejemplo: 2, 4), o NINGUNA.\n"
                . "LÍNEA 2: tu respuesta para él, en UNA o DOS frases cortas, sin títulos ni listas.\n"
                // 🧠 LA IA TIENE QUE PENSAR (orden del jefe, 2026-09-15): si lo que se ve son camiones,
                // montones de arena o piedra, o reparto, hay que darle al dueño la salida de «no tengo
                // local, lo llevo a su casa» — y eso lo decide ELLA, mirando las fotos.
                . "LÍNEA 3: solo una palabra — LOCAL (negocio con local al que va la gente), "
                . "DOMICILIO (servicio que se LLEVA: camiones, volquetes, arena, piedra o ripio, reparto, minicargadora) "
                . "o IGUAL (si no se puede saber).";

        $r = tienda_ia_llamar($tarea, array_merge([
            'paso'       => $que === 'tienda' ? 'fotos_tienda' : 'producto_fotos',
            'imagenes'   => $limpias,
            'max_tokens' => TIENDA_IA_MAX_TOKENS_IMG,
        ], $op));
        if (empty($r['ok'])) return null;

        $lineas = tienda_ia_lineas($r['texto']);
        // 🧠 LÍNEA 3 (LOCAL / DOMICILIO / IGUAL): es el «pensamiento» de la IA sobre si este negocio
        // tiene local o trabaja llevando. Se saca ANTES de armar el comentario, para que no se le
        // muestre al dueño esa palabra.
        $sugerir = '';
        if (count($lineas) > 1) {
            $ult = trim((string)end($lineas));
            if (preg_match('/^(LOCAL|DOMICILIO|IGUAL)\b\.?$/i', $ult)) {
                $sugerir = strtoupper((string)preg_replace('/[^A-Z]/i', '', $ult));
                array_pop($lineas);
            }
        }
        $linea1 = trim((string)($lineas[0] ?? ''));
        $capturas = [];
        // La LÍNEA 1 es la respuesta del candado de capturas: números («2, 4»), «NINGUNA» o nada.
        // ⚠️ Ojo con «NINGUNA»: al principio se colaba dentro del comentario y el dueño leía
        //    «NINGUNA» como si fuera parte de la respuesta (lo cazó la prueba 8 del 2026-09-15).
        $es_ninguna = ($linea1 !== '' && (bool)preg_match('/^(ningun[ao]?\b|no hay\b|ninguna de ellas)/iu', $linea1));
        $es_numeros = ($linea1 !== '' && (bool)preg_match('/^[\d\s,;y]+$/ui', $linea1));
        if ($es_ninguna || $es_numeros) {
            if ($es_numeros && preg_match_all('/\d+/', $linea1, $m)) {
                foreach ($m[0] as $nn) {
                    $i = (int)$nn - 1;
                    if ($i >= 0 && $i < $k) $capturas[] = $i;
                }
            }
            $texto = trim(implode("\n", array_slice($lineas, 1)));
            // Si el modelo solo contestó el candado, sin comentario, se le da la vuelta: el
            // comentario puede haber quedado en la MISMA línea 1 después del «NINGUNA».
            if ($texto === '' && !$es_numeros) {
                $texto = trim(preg_replace('/^(ningun[ao]?\b|no hay\b)[\s:.,\-]*/iu', '', $linea1));
            }
        } else {
            $texto  = trim(implode("\n", $lineas));   // no respetó el formato: todo es para él
            $linea1 = '';
        }
        // Respaldo: si igual contesta empezando con la palabra CAPTURA, se le cree (es la foto 1).
        if (preg_match('/^\s*CAPTURA\b/i', $texto)) {
            $capturas[] = 0;
            $texto = trim(preg_replace('/^\s*CAPTURA\s*[:.\-]?\s*/i', '', $texto));
        }
        return [
            'capturas' => array_values(array_unique($capturas)),
            'texto'    => tienda_ia_limpiar_largo($texto, 600),
            'sugerir'  => $sugerir,   // 'LOCAL' | 'DOMICILIO' | 'IGUAL' | ''
        ];
    }
}

if (!function_exists('tienda_ia_utf8_sano')) {
    /**
     * 🧼 Deja el texto en UTF-8 VÁLIDO. Hace falta porque todo lo que viene de la IA (y de los
     * `trim()` con caracteres multibyte, ver `tienda_ia_ficha_valor`) puede traer bytes sueltos: un
     * texto inválido hace que `preg_replace` con /u devuelva **null** y el dato se pierda, y además
     * se guardaría roto en la base.
     */
    function tienda_ia_utf8_sano($t) {
        $t = (string)$t;
        if ($t === '' || mb_check_encoding($t, 'UTF-8')) return $t;
        if (function_exists('iconv')) {
            $x = @iconv('UTF-8', 'UTF-8//IGNORE', $t);
            if ($x !== false) return $x;
        }
        return (string)preg_replace('/[\x80-\xFF]/', '', $t);   // último recurso: fuera los bytes raros
    }
}

if (!function_exists('tienda_ia_ficha_valor')) {
    /**
     * 🧹 Limpia un valor de la ficha del arranque: los «NINGUNO» y las evasivas son parte del
     * protocolo (como el «NINGUNA» del candado de capturas), no un dato para el dueño.
     */
    function tienda_ia_ficha_valor($v, $max = 120) {
        $v = trim((string)$v);
        if ($v === '') return '';
        $v = preg_replace('/[\x{1F300}-\x{1FAFF}\x{2600}-\x{27BF}\x{FE0F}]/u', '', $v);  // emojis fuera
        // ⚠️ Los bordes se quitan con regex y /u, NO con trim(): la lista de trim() se lee como bytes
        // y ««» o «·» (multibyte) dejan el texto a medias (rompe el UTF-8 y pierde el dato).
        $v = preg_replace('/^[\s"\'\x{201C}\x{201D}\x{00AB}\x{00BB},.;:\-\x{2013}\x{2014}]+/u', '', (string)$v);
        $v = preg_replace('/[\s"\'\x{201C}\x{201D}\x{00AB}\x{00BB},.;:\-\x{2013}\x{2014}]+$/u', '', (string)$v);
        $v = tienda_ia_utf8_sano($v);
        if ($v === '') return '';
        if (preg_match('/^(ningun[ao]?s?|ninguna de ellas|no hay|no se ve|no se puede|no aparece|no hay datos|sin datos|no se distingue|no visible|ilegible|desconocid[oa]|n\/?a)$/iu', $v)) return '';
        if (preg_match('/^(ningun[ao]?s?|no hay|no se ve|no aparece|no se distingue|sin datos|no se puede leer|n\/?a)\b/iu', $v)) {
            $resto = trim(preg_replace('/^(ningun[ao]?s?|no hay|no se ve|no aparece|no se distingue|sin datos|no se puede leer|n\/?a)\b[\s:.\-–]*/iu', '', $v));
            if ($resto === '') return '';
            $v = $resto;
        }
        return mb_substr(trim($v), 0, $max);
    }
}

if (!function_exists('tienda_ia_leer_ficha')) {
    /**
     * 📋 LEE LA FICHA que contesta la IA del arranque. Se le piden 8 líneas etiquetadas (CAPTURAS,
     * LETRERO, RUBRO, TELEFONO, DIRECCION, LOCAL, PRODUCTOS, COMENTARIO) y aquí se recogen CON
     * TOLERANCIA: al modelo no se le pide JSON (trampa 5) y se salta el formato como quiere.
     *
     * ⚠️ LO QUE MORDIÓ EL 2026-09-15 (primera corrida de la prueba 10): el modelo contestó **las 8
     * etiquetas seguidas en UN SOLO RENGLÓN** («CAPTURAS: NINGUNA LETRERO: JENNY'S RUBRO: …»), así
     * que leer línea por línea dejaba la ficha VACÍA y le mostraba al dueño el protocolo crudo. Ahora
     * se busca cada etiqueta en CUALQUIER parte del texto y su valor es lo que va hasta la siguiente
     * etiqueta. Y al comentario se le quitan los restos de etiquetas antes de mostrarlo.
     *
     * @return array capturas · letrero · rubro · telefono · direccion · local · productos · comentario
     */
    function tienda_ia_leer_ficha($texto, $cuantas = 0) {
        $ficha = ['capturas' => [], 'letrero' => '', 'rubro' => '', 'telefono' => '',
                  'direccion' => '', 'local' => '', 'productos' => [], 'comentario' => ''];
        $mapa = [
            'CAPTURA' => 'capturas', 'CAPTURAS' => 'capturas', 'PANTALLA' => 'capturas',
            'LETRERO' => 'letrero', 'ROTULO' => 'letrero', 'NOMBRE' => 'letrero',
            'RUBRO' => 'rubro', 'RUBROS' => 'rubro', 'CATEGORIA' => 'rubro',
            'TELEFONO' => 'telefono', 'TELEFONOS' => 'telefono', 'CELULAR' => 'telefono',
            'WHATSAPP' => 'telefono', 'NUMERO' => 'telefono',
            'DIRECCION' => 'direccion', 'UBICACION' => 'direccion', 'LUGAR' => 'direccion',
            'LOCAL' => 'local', 'DOMICILIO' => 'local', 'TIPO' => 'local',
            'PRODUCTO' => 'productos', 'PRODUCTOS' => 'productos',
            'COMENTARIO' => 'comentario', 'COMENTARIOS' => 'comentario', 'RESPUESTA' => 'comentario', 'TEXTO' => 'comentario',
        ];
        // ⚠️ Las etiquetas se aceptan CON ACENTO o sin él («teléfono» / «TELEFONO»): el modelo escribe
        // como quiere, y el 2026-09-15 escribió una tanda con tildes y otra sin ellas.
        $etiquetas = 'CAPTURAS?|PANTALLA|LETRERO|ROTULO|NOMBRE|RUBROS?|CATEGOR[IÍ]A|TEL[EÉ]FONOS?|CELULAR|WHATSAPP|N[UÚ]MERO'
                   . '|DIRECCI[OÓ]N|UBICACI[OÓ]N|LUGAR|LOCAL|DOMICILIO|TIPO|PRODUCTOS?|COMENTARIOS?|RESPUESTA|TEXTO';
        $t = str_replace(['*', '_', '`', '#'], '', (string)$texto);   // markdown de sobra fuera
        $re = '/(?:^|[\s•·\-])(' . $etiquetas . ')\s*[:\-–]\s*/iu';
        $limpiar = function ($v) {
            // Un valor nunca lleva saltos de línea ni viñetas sueltas: el modelo pone una etiqueta por
            // renglón y quedan restos («- NINGUNA») cuando se salta una.
            // ⚠️ TRAMPA QUE NOS MORDIÓ EL 2026-09-15: **nunca** usar `trim()` con caracteres multibyte
            // en la lista («–—•·«»»): la lista se lee como BYTES, el 0xC2 de «·» se come la mitad de
            // una ««» y el texto queda con bytes inválidos — y entonces `preg_replace` con /u devuelve
            // null y el dato se pierde (nos borró el nombre del letrero). Se hace con regex y /u.
            $v = trim(preg_replace('/\s+/u', ' ', (string)$v));
            $v = preg_replace('/^[\s\-\x{2022}\x{00B7}\x{2013}\x{2014}]+/u', '', $v);
            $v = preg_replace('/[\s\-\x{2022}\x{00B7}\x{2013}\x{2014}]+$/u', '', $v);
            return trim((string)$v);
        };

        $cortes = [];
        if (preg_match_all($re, $t, $m, PREG_OFFSET_CAPTURE)) {
            $n = count($m[0]);
            for ($i = 0; $i < $n; $i++) {
                $ini = (int)$m[0][$i][1] + strlen((string)$m[0][$i][0]);
                $fin = ($i + 1 < $n) ? (int)$m[0][$i + 1][1] : strlen($t);
                $cortes[] = [(int)$m[0][$i][1], $ini];
                $clave = mb_strtoupper(trim((string)$m[1][$i][0]));
                if (!isset($mapa[$clave]) && mb_substr($clave, -1) === 'S') $clave = mb_substr($clave, 0, -1);   // plural
                if (!isset($mapa[$clave])) continue;
                $campo = $mapa[$clave];
                $valor = $limpiar(substr($t, $ini, max(0, $fin - $ini)));
                if ($campo === 'capturas') {
                    if ($valor !== '' && preg_match_all('/\d+/', $valor, $mm)) {
                        foreach ($mm[0] as $nn) {
                            $ix = (int)$nn - 1;
                            if ($ix >= 0 && ($cuantas <= 0 || $ix < $cuantas)) $ficha['capturas'][] = $ix;
                        }
                    }
                } elseif ($campo === 'productos') {
                    foreach (preg_split('/[,;]|\/| y /u', $valor) as $p) {
                        $p = $limpiar($p);
                        if ($p !== '') $ficha['productos'][] = $p;
                    }
                } elseif ($ficha[$campo] === '') {
                    $ficha[$campo] = $valor;
                }
            }
        }
        // Lo que NO es una etiqueta ni su valor: si el modelo no puso COMENTARIO, ese texto es su
        // comentario (así nunca se pierde lo que dijo).
        $fuera = '';
        $pos = 0;
        foreach ($cortes as $c) {
            if ($c[0] > $pos) $fuera .= ' ' . substr($t, $pos, $c[0] - $pos);
            $pos = max($pos, $c[1]);
        }
        if ($pos < strlen($t)) $fuera .= ' ' . substr($t, $pos);
        if (trim((string)$ficha['comentario']) === '' && trim($fuera) !== '') $ficha['comentario'] = trim($fuera);

        // 🧹 Limpieza: restos de etiquetas y protocolo («NINGUNA», «NINGUNO») NUNCA se le muestran.
        $ficha['comentario'] = trim(preg_replace('/\s+/u', ' ', (string)preg_replace($re, ' ', (string)$ficha['comentario'])));
        $ficha['capturas']  = array_values(array_unique($ficha['capturas']));
        $ficha['productos'] = array_slice($ficha['productos'], 0, 6);
        foreach (['letrero', 'rubro', 'telefono', 'direccion', 'local', 'comentario'] as $k) {
            $max = ($k === 'comentario') ? 600 : 120;
            $ficha[$k] = tienda_ia_ficha_valor((string)$ficha[$k], $max);
        }
        // El teléfono se guarda solo con dígitos (y sin el 51 del país, como el resto del módulo).
        $tel = preg_replace('/\D+/', '', (string)$ficha['telefono']);
        if (mb_strlen($tel) === 11 && substr($tel, 0, 2) === '51') $tel = substr($tel, 2);
        $ficha['telefono'] = (mb_strlen($tel) >= 6 && mb_strlen($tel) <= 12) ? $tel : '';
        // El «pensamiento» solo vale si es una de las tres palabras.
        $ficha['local'] = in_array($ficha['local'], ['LOCAL', 'DOMICILIO', 'IGUAL'], true) ? $ficha['local'] : '';
        return $ficha;
    }
}

if (!function_exists('tienda_ia_ia_arranque')) {
    /**
     * 📷👁️ LA LECTURA DE LAS FOTOS DEL ARRANQUE (orden del jefe, 2026-09-15: *«que pida al usuario
     * mínimo cinco fotos y luego analizarlas y ver qué datos puede obtener: si se puede adivinar el
     * rubro, si se puede adivinar el título del negocio, tal vez el teléfono…»*).
     *
     * El dueño manda sus fotos ANTES de contar nada y de aquí sale el prototipo de la tienda: el
     * nombre del letrero, el rubro, el teléfono del aviso, la dirección y lo que se ve que vende.
     *
     * ⚠️ UNA sola llamada para todo el lote (como `tienda_ia_ia_fotos`), pero con la copia GRANDE
     * (1600 px y más calidad): esta llamada tiene que LEER, no solo mirar.
     * ⚠️ Al modelo NO se le pide JSON (trampa 5): son 8 líneas etiquetadas y `tienda_ia_leer_ficha()`
     * las recoge con tolerancia.
     *
     * @return array|null la ficha, o null si la IA no pudo (el flujo sigue con el guion local).
     */
    function tienda_ia_ia_arranque(array $rels, array $op = []) {
        $limpias = [];
        foreach ($rels as $rel) { if (is_string($rel) && $rel !== '') $limpias[] = $rel; }
        if (!$limpias) return null;
        $tope = max(1, (int)TIENDA_IA_FOTO_LOTE_IA);
        if (count($limpias) > $tope) $limpias = array_slice($limpias, 0, $tope);
        $k = count($limpias);

        $tarea = "Alguien que tiene un negocio en Chimbote (Perú) te acaba de mandar {$k} foto(s) de su negocio y NO te escribió nada: "
               . "no sabes cómo se llama ni qué vende, y tienes que sacarlo de lo que VES en las fotos.\n"
               // 📸 Se le dice QUÉ fotos se le pidieron al dueño (orden del jefe): así entiende una
               // tarjeta de presentación o un folleto (que traen nombre, teléfono y dirección).
               . "Le pedimos estas fotos: **afuera del negocio (la fachada o el letrero), por dentro, sus productos, "
               . "sus máquinas o herramientas, su personal, su tarjeta de presentación y su folleto**. "
               . "Una tarjeta o un folleto son foto legítima del negocio (no son capturas) y de ahí también se leen el nombre, el teléfono y la dirección.\n\n"
               . "Contesta EXACTAMENTE con estas 8 líneas, en este orden, cada una empezando por su etiqueta en MAYÚSCULAS y dos puntos. "
               . "Si algo no se ve, escribe NINGUNO (o NINGUNA / IGUAL). NUNCA inventes ni completes con lo que suele haber: "
               . "si no está en las fotos, va NINGUNO.\n\n"
               . "CAPTURAS: los números de las fotos que sean CAPTURA DE PANTALLA (barras de hora, batería o señal, pestañas de un navegador, "
               . "la ventana de WhatsApp o de una red social con sus botones), separados por coma, o NINGUNA\n"
               . "LETRERO: el nombre del negocio tal como se lee pintado o impreso (SOLO el nombre del negocio: NO el rubro, NO una marca de "
               . "producto o de gaseosa, NO el nombre de la imprenta que hizo el aviso), o NINGUNO\n"
               . "RUBRO: de qué es el negocio, en 1 a 3 palabras (ferretería, pollería, botica, bodega, taller de motos…), o NINGUNO\n"
               . "TELEFONO: el número que se lea en un letrero, aviso, afiche o vehículo, SOLO los dígitos, o NINGUNO\n"
               . "DIRECCION: la dirección o referencia que se lea (calle, avenida, número, distrito, «frente a…»), o NINGUNA\n"
               . "LOCAL: LOCAL si es un negocio con local al que va la gente, DOMICILIO si es un servicio que se LLEVA "
               . "(camiones, volquetes, arena, piedra, ripio, reparto, delivery), o IGUAL si no se puede saber\n"
               . "PRODUCTOS: hasta 4 cosas concretas que se vean y se puedan vender, con **el número de la foto donde se ven** "
               . "entre paréntesis, separadas por coma (por ejemplo: clavos (3), pintura (5), cemento (6)), o NINGUNO\n"
               . "COMENTARIO: 1 o 2 frases cortas para él, contándole qué viste DE VERDAD en sus fotos, sin títulos ni listas.\n\n"
               . "⚠️ El teléfono de un aviso muchas veces es el de la IMPRENTA que lo hizo, no el del dueño: cópialo igual, ya se lo preguntamos nosotros.\n"
               . "⚠️ Una FOTO de un letrero, de un menú, de un afiche o de una pizarra NO es una captura: esa es una foto de verdad y sirve.";

        $r = tienda_ia_llamar($tarea, array_merge([
            'paso'       => 'arranque_fotos',
            'imagenes'   => $limpias,
            'max_tokens' => (int)TIENDA_IA_MAX_TOKENS_LETRERO,
            'lado'       => (int)TIENDA_IA_FOTO_LADO_LETRERO,
            'calidad'    => (int)TIENDA_IA_FOTO_CALIDAD_LETRERO,
        ], $op));
        if (empty($r['ok'])) return null;
        $f = tienda_ia_leer_ficha((string)$r['texto'], $k);
        $f['ms'] = (int)($r['ms'] ?? 0);
        return $f;
    }
}

if (!function_exists('tienda_ia_nombre_de_letrero')) {
    /**
     * ¿Lo que la IA leyó en el letrero sirve como NOMBRE de la tienda? Se propone para que el dueño
     * lo confirme, nunca se guarda solo. Se descartan las palabras genéricas (una pared que solo
     * dice «BODEGA» o «FARMACIA» no es un nombre), los textos larguísimos y lo que parece un aviso.
     */
    function tienda_ia_nombre_de_letrero($texto) {
        $n = tienda_ia_limpiar($texto, 60);
        if (mb_strlen($n) < 2 || mb_strlen($n) > 60) return '';
        if (preg_match('/\d{3,}/', $n)) return '';                     // un número: es un aviso, no el nombre
        if (preg_match('#https?://|www\.#i', $n)) return '';
        if (count(preg_split('/\s+/u', $n)) > 6) return '';             // un letrero largo es un aviso
        $genericas = ['bodega', 'tienda', 'minimarket', 'minimercado', 'mercado', 'puesto', 'local', 'negocio',
                      'farmacia', 'botica', 'restaurante', 'polleria', 'cevicheria', 'panaderia', 'ferreteria',
                      'libreria', 'peluqueria', 'barberia', 'lavanderia', 'gasfiteria', 'taller', 'grifo',
                      'hotel', 'hostal', 'cabinas', 'consultorio', 'deposito', 'almacen', 'distribuidora'];
        if (in_array(tienda_ia_sin_tildes($n), $genericas, true)) return '';
        return $n;
    }
}

if (!function_exists('tienda_ia_distrito_leido')) {
    /**
     * 📍 ¿La dirección que se leyó en las fotos dice en qué distrito está? Se busca el nombre de un
     * distrito REAL del sitio dentro del texto («Av. Pardo 123, Nuevo Chimbote» → Nuevo Chimbote).
     * ⚠️ «Chimbote» está dentro de «Nuevo Chimbote», así que gana el nombre MÁS LARGO que aparezca.
     */
    function tienda_ia_distrito_leido($texto) {
        $t = ' ' . tienda_ia_sin_tildes(preg_replace('/[.,;:()]/u', ' ', (string)$texto)) . ' ';
        $t = preg_replace('/\s+/u', ' ', $t);
        $mejor = null;
        foreach (tienda_ia_distritos() as $d) {
            $n = tienda_ia_sin_tildes((string)$d['texto']);
            if (mb_strlen($n) < 4) continue;
            if (mb_strpos($t, ' ' . $n . ' ') === false) continue;
            if ($mejor === null || mb_strlen($n) > mb_strlen(tienda_ia_sin_tildes((string)$mejor['texto']))) $mejor = $d;
        }
        return $mejor;
    }
}

if (!function_exists('tienda_ia_rubros_chips')) {
    /**
     * 🏷️ Los rubros candidatos, ya como BOTONES (texto + id). Es lo que se le muestra al dueño
     * cuando la IA leyó el rubro en las fotos: solo rubros REALES del directorio.
     */
    function tienda_ia_rubros_chips($texto, $limite = 4) {
        $out = [];
        foreach (tienda_ia_rubros_candidatos((string)$texto, max(1, (int)$limite)) as $c) {
            $out[] = ['texto' => ($c['icono'] !== '' ? $c['icono'] . ' ' : '') . (string)$c['nombre'], 'valor' => (string)$c['id']];
        }
        return $out;
    }
}

if (!function_exists('tienda_ia_chips_prototipo')) {
    /**
     * 🏷️ LOS BOTONES DE RUBRO DEL PROTOTIPO (los que ve el dueño después de mandar sus 5 fotos).
     * Primero se buscan rubros REALES con lo que la IA LEYÓ («ferretería» → Ferreterías); si de ahí
     * no sale nada, con todo el contexto (lo que vio, el letrero y lo que se vende); y si tampoco,
     * los rubros con más tiendas del sitio (nunca se deja al dueño sin botones).
     */
    function tienda_ia_chips_prototipo(array $d, $limite = 4) {
        $leido = trim((string)($d['rubro_leido'] ?? ''));
        if ($leido !== '') {
            $chips = tienda_ia_rubros_chips($leido, $limite);
            if ($chips) return $chips;
        }
        $solo_titulos = [];
        foreach ((array)($d['productos_vistos'] ?? []) as $pv) {
            $solo_titulos[] = is_array($pv) ? (string)($pv['titulo'] ?? '') : (string)$pv;
        }
        $contexto = trim($leido . ' ' . (string)($d['letrero'] ?? '') . ' '
                  . implode(', ', $solo_titulos) . ' '
                  . implode(' ', (array)($d['visto'] ?? [])));
        $chips = $contexto !== '' ? tienda_ia_rubros_chips($contexto, $limite) : [];
        return $chips ?: tienda_ia_rubros_chips('', max(6, (int)$limite));
    }
}

if (!function_exists('tienda_ia_nombre_validar')) {
    /**
     * Acepta (o rechaza) el nombre de la tienda con la IA: distingue el NOMBRE de lo que VENDE
     * (*«pollo frito» no es un nombre*). Lo usan el paso `nombre` (flujo viejo y respaldo) y el
     * `nombre_ok` (cuando el dueño corrige lo que la IA leyó en el letrero).
     */
    function tienda_ia_nombre_validar(array $d, $texto, array $op = []) {
        $limpio = tienda_ia_limpiar($texto, 70);
        if (mb_strlen($limpio) < 2)  return ['ok' => false, 'nombre' => '', 'frase' => "¿Cómo se llama tu tienda? 🙂"];
        if (mb_strlen($limpio) > 60) return ['ok' => false, 'nombre' => '', 'frase' => "Muy largo 😅 ¿Más cortito, como lo dice la gente?"];
        $ia = tienda_ia_ia_nombre($d, $limpio, $op);
        if ($ia === null) $ia = ['ok' => true, 'frase' => "¡**{$limpio}**! ✅"];   // sin IA manda el dueño
        if (empty($ia['ok'])) {
            return ['ok' => false, 'nombre' => '', 'frase' => ((string)($ia['frase'] ?? '') !== ''
                ? (string)$ia['frase']
                : "Eso es lo que **vendes** 😉 Dame el **nombre**: «Bodega El Sol», «Combinado Doña Lucha»…")];
        }
        return ['ok' => true, 'nombre' => $limpio, 'frase' => (string)($ia['frase'] ?? '')];
    }
}

if (!function_exists('tienda_ia_paso_fotos')) {
    /**
     * 📷 EL PASO DE LAS FOTOS DEL ARRANQUE (el corazón del flujo nuevo): guarda el lote, se lo da a
     * LEER a la IA, apila lo que vio y —cuando ya hay 5 (mínimo, orden del jefe)— arma el prototipo
     * de la tienda y pasa a confirmarlo.
     *
     * ⚠️ Lo usan DOS entradas: el paso `fotos` y el `arranque` cuando lo primero que llega es un lote
     * de fotos (el botón del saludo abre la galería, así que el servidor NUNCA ve un «arranque» antes:
     * sin esto, la primera tanda de fotos caería en el saludo y se perdería).
     *
     * @return array la respuesta del paso (texto · tipo · opciones)
     */
    function tienda_ia_paso_fotos(array &$s, array &$d, array $lote, array $op, $empujar, $uid) {
        $max = (int)TIENDA_IA_FOTOS_ARRANQUE_MAX;
        $min = (int)TIENDA_IA_FOTOS_ARRANQUE_MIN;
        $s['paso']  = 'fotos';
        $d['flujo'] = 'fotos';

        if (!$lote) {
            $empujar("Toca 📷 y elige **" . TIENDA_IA_TEXTO_GALERIA . "** para mandarme las fotos de tu negocio");
            return tienda_ia_guion('fotos', $d);
        }
        $espacio = max(0, $max - count($d['fotos']));
        if ($espacio <= 0) return tienda_ia_guion('fotos', $d);
        $sobran = max(0, count($lote) - $espacio);
        if ($sobran) $lote = array_slice($lote, 0, $espacio);

        $nuevas = []; $fallos = 0;
        foreach ($lote as $f) {
            $g = tienda_ia_guardar_foto($f, $uid, !empty($f['prueba']));
            if (!empty($g['ok'])) $nuevas[] = (string)$g['rel']; else $fallos++;
        }
        if (!$nuevas) {
            $empujar("No pude guardar esas fotos 😅 Revisa que sean imágenes y prueba otra vez.");
            return tienda_ia_guion('fotos', $d);
        }
        foreach ($nuevas as $rel) $d['fotos'][] = $rel;
        if ($sobran) $empujar("Te guardo las primeras " . count($nuevas) . ": el tope son **{$max}** fotos 😉");
        if ($fallos) $empujar("(" . $fallos . " no se pudo subir: puede que sea muy pesada.)");

        // 👁️ UNA SOLA LLAMADA: mira el lote y LEE la ficha (letrero, rubro, teléfono, dirección…).
        $ia = tienda_ia_ia_arranque($nuevas, $op);

        // 🚫 El candado de las capturas vale igual que siempre (regla inviolable del sitio).
        if ($ia !== null && !empty($ia['capturas'])) {
            $fuera = [];
            foreach ($ia['capturas'] as $ix) { if (isset($nuevas[$ix])) $fuera[] = $nuevas[$ix]; }
            $que = tienda_ia_captura_decision($d, 'fotos', true);
            if ($que === 'rechazar' && $fuera) {
                foreach ($fuera as $rel) {
                    $k = array_search($rel, $d['fotos'], true);
                    if ($k !== false) unset($d['fotos'][$k]);
                    tienda_ia_borrar_foto($rel);
                }
                $d['fotos'] = array_values($d['fotos']);
                $empujar(((string)$ia['comentario'] !== '' ? (string)$ia['comentario'] . "\n\n" : '')
                       . "Ojo: una de esas es una **captura de pantalla** y esas no las puedo subir (se ven tus pestañas). "
                       . "Si es una **foto de verdad** —tu letrero, tu menú—, mándamela otra vez y la acepto 👌");
                return tienda_ia_guion('fotos', $d);
            }
            $empujar("Las tomo como tuyas 👍");
        } elseif ($ia !== null) {
            tienda_ia_captura_decision($d, 'fotos', false);
        }

        if ($ia !== null) {
            if ((string)$ia['comentario'] !== '') {
                $empujar((string)$ia['comentario']);
                $d['visto'][] = tienda_ia_limpiar((string)$ia['comentario'], 180);
                if (count($d['visto']) > 5) $d['visto'] = array_slice($d['visto'], -5);
            }
            $letrero = tienda_ia_nombre_de_letrero((string)$ia['letrero']);
            if ($letrero !== '')               $d['letrero'] = $letrero;
            if ((string)$ia['rubro'] !== '')   $d['rubro_leido'] = tienda_ia_limpiar((string)$ia['rubro'], 40);
            if ((string)$ia['direccion'] !== '') $d['direccion_leida'] = tienda_ia_limpiar((string)$ia['direccion'], 120);
            if ((string)$ia['telefono'] !== '')  $d['telefono_leido'] = (string)$ia['telefono'];
            $prods = [];
            foreach ((array)$ia['productos'] as $p) {
                $p = tienda_ia_limpiar($p, 60);
                if ($p === '') continue;
                // 📷🆕 El modelo escribe «clavos (3)»: el número es la FOTO donde se ve, y se usa para
                // ponerle esa foto al producto que se crea (orden del jefe, 2026-09-15 noche).
                $foto_ix = -1;
                if (preg_match('/\((\d{1,2})\)\s*$/', $p, $mm)) {
                    $foto_ix = (int)$mm[1] - 1;
                    $p = trim((string)preg_replace('/\(\d{1,2}\)\s*$/', '', $p));
                } elseif (preg_match('/\b(\d{1,2})\s*$/', $p, $mm) && mb_strlen($p) > 4) {
                    $foto_ix = (int)$mm[1] - 1;
                    $p = trim((string)preg_replace('/\b\d{1,2}\s*$/', '', $p));
                }
                $p = tienda_ia_limpiar($p, 40);
                if ($p === '' || mb_strlen($p) > 40 || count($prods) >= 4) continue;
                $prods[] = ['titulo' => $p, 'foto' => $foto_ix];
            }
            if ($prods) $d['productos_vistos'] = $prods;
            if ((string)$ia['local'] !== '') $d['sugerir_domicilio'] = ((string)$ia['local'] === 'DOMICILIO') ? 1 : 0;
        }

        $n = count($d['fotos']);
        if ($n < $min) {
            // 🔴 MÍNIMO 8 ESTRICTO (orden del jefe, 2026-09-15 noche: *«si te dice que solo tiene
            // cuatro, dile que todavía no lo podemos hacer, solamente trabajamos a partir de 8 fotos»*).
            // No se avanza con menos; el mensaje dice EXACTO cuántas faltan y el carrete sigue abierto.
            return tienda_ia_guion('fotos', $d);
        }
        // 📷 ¡Ya hay 8! Se arma el prototipo con lo que la IA leyó y se pasa al TIPO DE NEGOCIO
        // (orden del jefe: las fotos primero, y después «¿qué tipo de negocio es?»).
        $d['rubro_opciones'] = tienda_ia_chips_prototipo($d, 4);
        $s['datos'] = $d;
        $empujar("¡Ya tengo tus **{$n}** fotos! 📸 Con esto te armo la tienda.");
        $s['paso'] = 'tipo';
        return tienda_ia_guion('tipo', $d);
    }
}

if (!function_exists('tienda_ia_ia_foto')) {
    /**
     * La versión de UNA sola foto (la usa el motor cuando el lote trae una): misma tarea, mismo
     * formato de respuesta. Se conserva porque el paso de fotos de la tienda y el del producto
     * comparten el motor y no siempre hace falta el lote.
     */
    function tienda_ia_ia_foto(array $d, $que, $rel, $n, $max, array $op = []) {
        $r = tienda_ia_ia_fotos($d, $que, [$rel], max(0, (int)$n - 1), $max, $op);
        if ($r === null) return null;
        return ['captura' => !empty($r['capturas']), 'texto' => $r['texto']];
    }
}

if (!function_exists('tienda_ia_captura_decision')) {
    /**
     * 🚫 LA REGLA INVIOLABLE DEL PROYECTO, CON MODALES: una **captura de pantalla** jamás se
     * publica (muestra las pestañas, los marcadores y las direcciones del dueño). Pero una
     * FOTO de un letrero, de un menú o de un afiche SÍ es una foto legítima del negocio, y la
     * IA puede confundirlas (comprobado el 2026-09-14: marcó como «captura» unas imágenes de
     * prueba con texto sobre fondo plano).
     *
     * Por eso la política es: **se le avisa UNA vez** y la foto se borra; si el dueño la
     * vuelve a mandar, manda él (es su tienda y ya se le explicó). Así la regla se cumple
     * —nada entra sin pasar por el aviso— sin dejar a nadie trabado por un letrero.
     *
     * @return string 'aceptar' (es una foto) · 'rechazar' (avisar y borrar) · 'aceptar_avisando'
     */
    function tienda_ia_captura_decision(array &$d, $paso, $captura) {
        $clave = 'captura_ok_' . preg_replace('/[^a-z_]/', '', (string)$paso);
        if (!$captura) { $d[$clave] = 0; return 'aceptar'; }
        if (!empty($d[$clave])) return 'aceptar_avisando';
        $d[$clave] = 1;
        return 'rechazar';
    }
}

if (!function_exists('tienda_ia_hora_aviso')) {
    /**
     * ⏱️ LA HORA QUE SE LE PROMETE AL DUEÑO (orden del jefe, 2026-09-14 noche): la hora de **Lima**
     * de aquí a `TIENDA_IA_MINUTOS_ESPERA` minutos. Si son las 8:02, devuelve «8:12», y el asistente
     * le dice *«a partir de las 8:12 tu sitio ya estará listo»*.
     *
     * ¿Para qué? Porque el celular está subiendo las fotos y el dueño tiene que **saber que su tienda
     * está en camino**: así no entra a los diez segundos a decir que no la ve, y entiende que el
     * trabajito se está haciendo.
     */
    function tienda_ia_hora_aviso($minutos = null) {
        $min = ($minutos !== null) ? (int)$minutos : (int)TIENDA_IA_MINUTOS_ESPERA;
        $h   = date('H:i', time() + max(1, $min) * 60);   // hora de Lima (la del sitio)
        return ($h[0] === '0') ? substr($h, 1) : $h;      // «8:12» en vez de «08:12» (y nunca «:12»)
    }
}

if (!function_exists('tienda_ia_lineas')) {
    /** Parte la respuesta de la IA en líneas útiles (sin vacías ni markdown). */
    function tienda_ia_lineas($texto) {
        $out = [];
        foreach (preg_split('/\r\n|\r|\n/', (string)$texto) as $l) {
            $l = trim(preg_replace('/^[\*\#\-\s]+/u', '', trim($l)));
            $l = trim($l, " \t\"'“”«»");
            if ($l !== '') $out[] = $l;
        }
        return $out;
    }
}

if (!function_exists('tienda_ia_limpiar_largo')) {
    /** Limpia una frase de la IA sin cortarle el sentido (conserva **negritas**). */
    function tienda_ia_limpiar_largo($texto, $max = 300) {
        $t = (string)$texto;
        $t = str_replace(["\r\n", "\r"], "\n", $t);
        $t = preg_replace('/\n{3,}/', "\n\n", $t);
        $t = trim($t);
        return mb_substr($t, 0, $max);
    }
}

/* =====================================================================================
 * 8) APOYOS LOCALES (rubros, distritos, tiendas del dueño)
 * ===================================================================================== */

if (!function_exists('tienda_ia_rubros')) {
    /** Todos los rubros activos (con sus palabras clave), cacheados por petición. */
    function tienda_ia_rubros() {
        static $rubros = null;
        if ($rubros !== null) return $rubros;
        $claves = [];
        $rubros = [];
        try {
            // `obtener_claves_categorias()` devuelve un MAPA: categoria_id => [claves].
            foreach (obtener_claves_categorias() as $cid => $claves_cat) {
                foreach ((array)$claves_cat as $k) {
                    $claves[(int)$cid][] = tienda_ia_sin_tildes((string)$k);
                }
            }
            foreach (obtener_categorias() as $c) {
                $rubros[] = [
                    'id'     => (int)$c['id'],
                    'nombre' => (string)$c['nombre'],
                    'slug'   => (string)$c['slug'],
                    'icono'  => (string)($c['icono'] ?? ''),
                    'claves' => $claves[(int)$c['id']] ?? [],
                ];
            }
        } catch (Throwable $e) { /* sin rubros: el asistente pedirá ayuda al jefe */ }
        return $rubros;
    }
}

if (!function_exists('tienda_ia_rubros_candidatos')) {
    /**
     * Los rubros que podrían ser, sacados SIN IA del relato del dueño (nombre + claves),
     * como hace el Caminante. Se le pasan a la IA para que elija: ella entiende el
     * sentido y el motor local pone la lista corta (así el prompt no lleva 122 rubros).
     * @return array lista de rubros (máximo $limite)
     */
    function tienda_ia_rubros_candidatos($texto, $limite = 8) {
        $t = ' ' . tienda_ia_sin_tildes($texto) . ' ';
        $puntos = [];
        foreach (tienda_ia_rubros() as $r) {
            $p = 0;
            $nom = tienda_ia_sin_tildes($r['nombre']);
            if ($nom !== '' && mb_strpos($t, $nom) !== false) $p += 6;
            foreach ($r['claves'] as $k) {
                if ($k === '' || mb_strlen($k) < 3) continue;
                if (mb_strpos($t, ' ' . $k) !== false) $p += (mb_strlen($k) >= 6 ? 3 : 2);
            }
            if ($p > 0) $puntos[$r['id']] = $p;
        }
        arsort($puntos);
        $out = [];
        foreach (array_keys($puntos) as $id) {
            foreach (tienda_ia_rubros() as $r) {
                if ($r['id'] === (int)$id) { $out[] = $r; break; }
            }
            if (count($out) >= $limite) break;
        }
        // Sin ninguna pista: los rubros con más tiendas son la apuesta más segura.
        if (!$out) {
            try {
                $top = db()->query("SELECT c.id, COUNT(n.id) t FROM directorio_categorias c
                                      LEFT JOIN directorio_negocios n ON n.categoria_id = c.id AND n.estado = 'activo'
                                     WHERE c.activo = 1 GROUP BY c.id ORDER BY t DESC LIMIT " . (int)$limite)->fetchAll();
                foreach ($top as $f) {
                    foreach (tienda_ia_rubros() as $r) {
                        if ($r['id'] === (int)$f['id']) { $out[] = $r; break; }
                    }
                }
            } catch (Throwable $e) {}
        }
        return $out;
    }
}

if (!function_exists('tienda_ia_distritos')) {
    /** Los distritos que el sitio muestra, como opciones tocables. */
    function tienda_ia_distritos() {
        $out = [];
        try {
            foreach (obtener_distritos_visibles() as $d) {
                $out[] = ['texto' => (string)$d['nombre'], 'valor' => (string)$d['id']];
            }
        } catch (Throwable $e) {}
        return $out;
    }
}

if (!function_exists('tienda_ia_distrito_por_gps')) {
    /**
     * ¿A qué distrito pertenece esta ubicación? La MISMA receta que usa el Caminante
     * (`caminante/distrito.php`): los 30 negocios activos más cercanos votan pesando por
     * cercanía (peso = 1/(d+0,05)²). No llama a ningún servicio de afuera.
     */
    function tienda_ia_distrito_por_gps($lat, $lng) {
        $lat = (float)$lat; $lng = (float)$lng;
        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180 || ($lat === 0.0 && $lng === 0.0)) return null;
        try {
            $nombres = [];
            foreach (obtener_distritos_visibles() as $d) { $nombres[(int)$d['id']] = (string)$d['nombre']; }
            if (!$nombres) return null;
            $st = db()->prepare("SELECT distrito_id,
                       (6371 * ACOS(LEAST(1, COS(RADIANS(:la1)) * COS(RADIANS(lat)) * COS(RADIANS(lng) - RADIANS(:lo1))
                        + SIN(RADIANS(:la2)) * SIN(RADIANS(lat))))) AS d
                  FROM directorio_negocios
                 WHERE estado = 'activo' AND lat IS NOT NULL AND lng IS NOT NULL
                   AND lat <> 0 AND lng <> 0 AND distrito_id IS NOT NULL
                 ORDER BY d ASC LIMIT 30");
            $st->execute([':la1' => $lat, ':la2' => $lat, ':lo1' => $lng]);
            $pesos = []; $minD = null;
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $f) {
                $id = (int)$f['distrito_id'];
                if (!isset($nombres[$id])) continue;
                $dist = (float)$f['d'];
                if ($minD === null || $dist < $minD) $minD = $dist;
                $pesos[$id] = ($pesos[$id] ?? 0.0) + 1.0 / pow($dist + 0.05, 2);
            }
            if (!$pesos) return null;
            arsort($pesos);
            $id    = (int)array_key_first($pesos);
            $total = array_sum($pesos);
            $conf  = $total > 0 ? round($pesos[$id] / $total, 3) : 0.0;
            return [
                'distrito_id' => $id,
                'nombre'      => $nombres[$id],
                'confianza'   => $conf,
                'dudoso'      => ($minD > 3.0) || ($conf < 0.6),
            ];
        } catch (Throwable $e) { return null; }
    }
}

if (!function_exists('tienda_ia_tiendas_de')) {
    /** Las tiendas de esta persona (para el modo «ya tengo una tienda»). */
    function tienda_ia_tiendas_de($usuario_id) {
        try {
            $s = db()->prepare("SELECT id, nombre, slug, estado FROM directorio_negocios
                                 WHERE dueno_id = ? ORDER BY id DESC LIMIT 20");
            $s->execute([(int)$usuario_id]);
            $out = [];
            foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $f) {
                $out[] = ['texto' => (string)$f['nombre'], 'valor' => (string)$f['id'], 'nota' => ((string)$f['estado'] === 'activo' ? '' : (string)$f['estado'])];
            }
            return $out;
        } catch (Throwable $e) { return []; }
    }
}

if (!function_exists('tienda_ia_producto_pedido')) {
    /**
     * 🛒🆕 «AGREGAR MI TIENDA» — EL PRODUCTO YA VIENE DICHO (pedido del jefe, 2026-09-16)
     * ==========================================================================
     * El botón negro de los resultados de búsqueda (`buscar.php`, debajo de «Ver … cerca de mí») abre
     * el asistente con el término que se buscó: `/crear-tienda?modo=producto&prod=cumpleaños`.
     *
     * Con eso el asistente **no pregunta cómo se llama el producto** (paso `producto_nombre`): el
     * producto NACE con ese nombre y lo primero que pide son SUS FOTOS —«📷 Mándame la foto de
     * cumpleaños»—, que es exactamente lo que pidió el jefe. Después sigue igual que siempre:
     * sus fotos → el precio → **queda publicado en su tienda** (`producto_listo`).
     *
     * Reglas (para no pisar nunca lo que el dueño ya venía haciendo):
     *   · Solo actúa en el modo `producto` y en los pasos donde TODAVÍA no hay producto (`tienda` =
     *     eligiendo tienda · `producto_nombre` = le estaban preguntando el nombre).
     *   · Si ya hay un producto en curso (o la conversación ya terminó), **no toca nada**: manda ella.
     *   · Con UNA sola tienda no se le pregunta cuál (igual que el flujo de siempre); con varias, el
     *     atajo se completa solo en cuanto toca la suya.
     *
     * @param array  $s          la conversación (por referencia: aquí se cambia de paso y de datos)
     * @param string $prod       el nombre del producto que trajo el botón (el término buscado)
     * @param int    $usuario_id de quién es la conversación (para leer sus tiendas)
     * @return bool  true = aplicó el atajo (con varias tiendas, solo dejó anotado el nombre y sigue
     *               esperando que elija cuál).
     */
    function tienda_ia_producto_pedido(array &$s, $prod, $usuario_id) {
        $prod = tienda_ia_limpiar($prod, 60);
        if ($prod === '' || (string)($s['modo'] ?? '') !== 'producto') return false;
        $paso = (string)($s['paso'] ?? '');
        if (!in_array($paso, ['tienda', 'producto_nombre'], true)) return false;
        $d = (array)($s['datos'] ?? []);
        if (!empty($d['productos'])) return false;              // ya hay un producto: no se toca
        $d['producto_pedido'] = $prod;
        if ($paso === 'tienda' && (int)($d['negocio_id'] ?? 0) <= 0) {
            $tiendas = ((int)$usuario_id > 0) ? tienda_ia_tiendas_de((int)$usuario_id) : [];
            if (count($tiendas) !== 1) {                        // varias (o ninguna): primero elige cuál
                $s['datos'] = $d;
                return true;
            }
            $d['negocio_id'] = (int)$tiendas[0]['valor'];
            if ((string)($d['nombre'] ?? '') === '') $d['nombre'] = (string)$tiendas[0]['texto'];
        }
        // Nace el producto con el nombre que trajo el botón y se va derecho a pedirle SUS FOTOS.
        $d['productos'][]       = ['titulo' => $prod, 'fotos' => [], 'precio' => null];
        $d['producto_indice']   = count($d['productos']) - 1;
        $d['producto_opciones'] = [];
        $d['productos_ia']      = 0;
        $d['producto_borrado']  = '';
        $s['paso']  = 'producto_fotos';
        $s['datos'] = $d;
        return true;
    }
}

if (!function_exists('tienda_ia_mis_tiendas')) {
    /**
     * 🏪 TODAS LAS TIENDAS DE ESTA PERSONA, con su enlace (2026-09-16). Es lo que come la ventana
     * emergente **«Mis tiendas»** del menú ⋯: el dueño de varios negocios ve ahí los suyos, entra a
     * cualquiera y —si quiere— arranca otro.
     *
     * ⚠️ No se usa para los chips del paso `tienda` (ahí manda `tienda_ia_tiendas_de()`, que devuelve
     * `texto`/`valor` y **sin url**: una opción con `url` el navegador la pinta como ENLACE y se
     * rompería la elección de tienda).
     *
     * @return array [ ['id','nombre','slug','estado','url','productos'], … ] (las últimas 30)
     */
    function tienda_ia_mis_tiendas($usuario_id) {
        $usuario_id = (int)$usuario_id;
        if ($usuario_id <= 0) return [];
        try {
            $s = db()->prepare("SELECT n.id, n.nombre, n.slug, n.estado,
                                       (SELECT COUNT(*) FROM directorio_servicios p WHERE p.negocio_id = n.id) AS productos
                                  FROM directorio_negocios n
                                 WHERE n.dueno_id = ? ORDER BY n.id DESC LIMIT 30");
            $s->execute([$usuario_id]);
            $out = [];
            foreach ($s->fetchAll(PDO::FETCH_ASSOC) as $f) {
                $slug = (string)$f['slug'];
                $out[] = [
                    'id'        => (int)$f['id'],
                    'nombre'    => (string)$f['nombre'],
                    'slug'      => $slug,
                    'estado'    => (string)$f['estado'],
                    'productos' => (int)$f['productos'],
                    'url'       => url_negocio($slug),
                    'editar'    => url('productos.php?n=' . (int)$f['id']),
                ];
            }
            return $out;
        } catch (Throwable $e) { return []; }
    }
}

/* =====================================================================================
 * 9) EL MOTOR: recibe lo que contestó el dueño y avanza el paso
 * ===================================================================================== */

if (!function_exists('tienda_ia_recibir')) {
    /**
     * Procesa UNA respuesta del dueño (texto, opción tocada, foto o ubicación) y devuelve
     * los mensajes nuevos que hay que pintar.
     *
     * @param array  $s       la conversación (fila de la tabla)
     * @param string $tipo    'texto' | 'opcion' | 'foto' | 'gps'
     * @param string $texto   lo que escribió (o el texto de la opción tocada)
     * @param string $valor   el valor de la opción tocada
     * @param array  $archivo $_FILES['foto'] cuando es UNA foto
     * @param float  $lat,$lng la ubicación si tocó «usar mi ubicación»
     * @param array  $archivos VARIAS fotos de una sola vez (la galería múltiple, 2026-09-15):
     *                        lista de $_FILES normalizados. Si viene, manda sobre `$archivo`.
     * @param string $producto 🛒🆕 El producto que YA VIENE DICHO por el botón «Agregar mi tienda» de
     *                        los resultados de búsqueda (2026-09-16). Vacío = flujo de siempre.
     *                        Ver `tienda_ia_producto_pedido()`.
     * @return array ['ok'=>bool,'mensajes'=>[...],'error'=>...]
     */
    function tienda_ia_recibir(array $s, $tipo, $texto = '', $valor = '', $archivo = null, $lat = null, $lng = null, array $archivos = [], $producto = '') {
        $uid  = (int)$s['usuario_id'];

        // 🛒🆕 EL PRODUCTO YA DICHO (botón «Agregar mi tienda» del buscador): se aplica ANTES de leer
        // el paso, porque el atajo cambia de paso (de `tienda`/`producto_nombre` a `producto_fotos`)
        // para que este mismo envío —que suele ser la FOTO del producto— caiga en el paso correcto.
        if ($producto !== '') tienda_ia_producto_pedido($s, $producto, $uid);

        $d    = $s['datos'];
        $paso = (string)$s['paso'];
        $op   = ['sesion' => (int)$s['id'], 'usuario' => $uid];

        $msgs = [];                                   // mensajes nuevos para pintar
        $empujar = function ($texto, $tipo_msg = 'bot') use (&$msgs, &$s) {
            if (trim((string)$texto) === '') return;
            $m = ['rol' => $tipo_msg, 'texto' => (string)$texto];
            $msgs[] = $m;
            $s['chat'][] = $m;
        };

        // 📷 EL LOTE DE FOTOS: la galería puede mandar varias de una sola vez (hasta 8). Se normaliza
        // aquí para que los dos pasos de fotos (tienda y producto) usen el mismo camino.
        $lote = [];
        foreach ($archivos as $f) { if (is_array($f) && !empty($f['tmp_name'])) $lote[] = $f; }
        if (!$lote && is_array($archivo) && !empty($archivo['tmp_name'])) $lote[] = $archivo;

        // ✏️ LAS DOS PIEZAS DE «EDITAR ALGO» (2026-09-15). Como la publicación es automática, las
        // correcciones se hacen sobre la tienda YA publicada: `$empezar_edit` abre el campo que él
        // elige (y devuelve la pregunta) y `tienda_ia_edicion_lista()` lo guarda y vuelve a la tarjeta.
        $menu_editar = [
            'texto' => '', 'tipo' => 'opciones', 'opciones' => [
                ['texto' => 'El nombre', 'valor' => 'edit:nombre'],
                ['texto' => 'De qué trata', 'valor' => 'edit:trato'],
                ['texto' => 'El rubro principal', 'valor' => 'edit:rubro'],
                ['texto' => 'Otros rubros o categorías', 'valor' => 'edit:rubro_mas'],
                ['texto' => 'Las fotos', 'valor' => 'edit:fotos_tienda'],
                ['texto' => 'Cómo vendo', 'valor' => 'edit:vendedor'],
                ['texto' => 'La dirección', 'valor' => 'edit:direccion'],
                ['texto' => 'Mis redes (Facebook, Instagram, TikTok)', 'valor' => 'edit:redes'],
                ['texto' => 'Mi zona', 'valor' => 'edit:distrito'],
                ['texto' => 'Mi WhatsApp', 'valor' => 'edit:whatsapp'],
                ['texto' => 'Mi horario', 'valor' => 'edit:horario'],
                ['texto' => 'No, déjalo así', 'valor' => 'nada'],
            ],
        ];
        $empezar_edit = function ($cual) use (&$s, &$d, $op, $empujar) {
            if ($cual === 'fotos_tienda') {
                // ⚠️ Al editar una tienda publicada las fotos NUEVAS se SUMAN a su galería: no se
                // borran las que ya tiene en línea (antes, en el paso de aprobación, sí se vaciaban).
                if ((string)$d['editando'] !== 'fotos_tienda') $d['fotos'] = [];
                $d['editando'] = 'fotos_tienda';
                $empujar("Mándame las fotos nuevas 📸 y las agrego a tu tienda.");
                $s['paso']  = 'fotos_tienda';
                $s['datos'] = $d;
                return tienda_ia_guion('fotos_tienda', $d);
            }
            if (in_array($cual, ['nombre', 'trato', 'rubro', 'rubro_mas', 'vendedor', 'direccion', 'redes', 'distrito', 'whatsapp', 'horario'], true)) {
                $d['editando'] = $cual;
                $s['datos'] = $d;
                $s['paso']  = $cual;
                $empujar("Dime cómo lo corregimos 👇");
                $extra = [];
                if ($cual === 'rubro')     $extra = ['opciones' => tienda_ia_rubros_candidatos($d['nombre'] . ' ' . $d['trato'] . ' ' . implode(' ', (array)($d['rubros_extra'] ?? [])), 6)];
                if ($cual === 'rubro_mas') $extra = ['opciones' => tienda_ia_rubros_para_sumar($d)];
                if ($cual === 'distrito')  $extra = ['distritos' => tienda_ia_distritos()];
                return tienda_ia_guion($cual, $d, $extra);
            }
            $empujar("Listo 👍");
            return tienda_ia_guion('publicado', $d);
        };

        // Tope de la conversación (nadie va a vaciar el saldo del jefe desde aquí).
        // Los que NO tienen cuenta llevan un tope más corto: su conversación no se puede
        // identificar por `usuario_id`, así que se cuenta por conversación.
        $tope = (int)TIENDA_IA_LLAMADAS_POR_SESION;
        if ($uid <= 0) $tope = min($tope, (int)TIENDA_IA_LLAMADAS_POR_VISITANTE_DIA);
        if ((int)$s['llamadas'] >= $tope
            || ($uid > 0 && tienda_ia_llamadas_hoy($uid) >= (int)TIENDA_IA_LLAMADAS_POR_USUARIO_DIA)
            || tienda_ia_llamadas_hoy(0) >= (int)TIENDA_IA_LLAMADAS_GLOBALES_DIA) {
            $empujar("Hoy ya trabajamos bastante 😅 Vuelve mañana: tu avance queda guardado.");
            $s['paso'] = $paso;
            tienda_ia_guardar($s);
            return ['ok' => true, 'mensajes' => $msgs, 'paso' => $s['paso'], 'datos' => $d, 'tipo' => 'texto', 'opciones' => []];
        }

        // 🗑️ El paso «cuántos productos» se retiró (orden del jefe, 2026-09-14 tarde). Si alguien
        // tenía una conversación a medias de la versión anterior, sigue por las fotos.
        if ($paso === 'plan') { $paso = 'fotos_tienda'; $s['paso'] = 'fotos_tienda'; }

        /* 📷🛍️ «MANDAR FOTOS DE MIS PRODUCTOS» (2026-09-16, pedido del jefe: *«lo ideal sería pedirle al
           usuario que siga subiendo fotos de sus productos e ir clasificando la inteligencia artificial»*).
           Se atiende AQUÍ y no dentro del `switch` porque el botón sale en varios pasos (la tarjeta final,
           la de publicada y la del producto en línea) y siempre hace lo mismo: abrir el paso de la tanda. */
        if ($valor === 'fotos_lote') {
            $neg_id = (int)($d['publicado']['negocio_id'] ?? $d['negocio_id'] ?? 0);
            if ($neg_id > 0) {
                $s['paso']  = 'productos_lote';
                $s['datos'] = $d;
                $empujar("📷 ¡Vamos! Mándame las fotos de tus productos —**varias de una vez**— "
                       . "y yo te los clasifico y los publico solos.");
                tienda_ia_guardar($s);
                $g = tienda_ia_guion('productos_lote', $d);
                return ['ok' => true, 'mensajes' => $msgs, 'paso' => 'productos_lote', 'datos' => $d,
                        'tipo' => $g['tipo'], 'opciones' => $g['opciones']];
            }
        }

        $respuesta = null;   // lo que se devuelve al final (guion del paso nuevo)

        switch ($paso) {

            /* ---------- 0) Modo «ya tengo una tienda»: elegir la tienda ---------- */
            case 'tienda': {
                $id = (int)($valor !== '' ? $valor : $texto);
                $ok = false;
                foreach (tienda_ia_tiendas_de($uid) as $t) { if ((int)$t['valor'] === $id) $ok = true; }
                if (!$ok) { $empujar("No encontré esa tienda entre las tuyas 🤔 Toca una de la lista, por favor."); break; }
                $d['negocio_id'] = $id;
                try {
                    $st = db()->prepare("SELECT nombre FROM directorio_negocios WHERE id = ? AND dueno_id = ? LIMIT 1");
                    $st->execute([$id, $uid]);
                    $d['nombre'] = (string)$st->fetchColumn();
                } catch (Throwable $e) {}
                $d['productos_ia'] = 0;
                $d['producto_borrado'] = '';
                // 🛒🆕 ¿El producto ya venía dicho por el botón «Agregar mi tienda» del buscador? Con la
                // tienda ya elegida, el atajo completa lo que faltaba (nace el producto y se le piden
                // sus fotos) en vez de preguntarle el nombre. Si no vino dicho, todo igual que siempre.
                $s['datos'] = $d;
                if (tienda_ia_producto_pedido($s, (string)($d['producto_pedido'] ?? ''), $uid)) {
                    $d    = $s['datos'];
                    $prod = (string)($d['productos'][(int)$d['producto_indice']]['titulo'] ?? '');
                    $respuesta = tienda_ia_guion($s['paso'], $d, ['producto' => $prod]);
                    break;
                }
                $d['producto_opciones'] = tienda_ia_ia_producto($d, $op + ['paso' => 'producto_nombre']) ?: [];
                $s['paso'] = 'producto_nombre';
                $s['datos'] = $d;
                $respuesta = tienda_ia_guion('producto_nombre', $d);
                break;
            }

            /* ---------- 0-bis) 🚪 EL ARRANQUE: qué quiere hacer hoy (y aquí se nota si está logueado) ---------- */
            case 'arranque': {
                // 📷🆕 LO PRIMERO QUE PUEDE LLEGAR SON LAS FOTOS (orden del jefe, 2026-09-15): el botón
                // del saludo abría el carrete, así que el navegador mandaba un LOTE DE FOTOS sin haber
                // pasado por ningún otro paso. 📍 Desde el 2026-09-20 el saludo ya NO abre el carrete
                // (primero va la ubicación), pero si un lote llega igual se GUARDA y se le pide la
                // ubicación: así no se pierde ninguna foto.
                if ($tipo === 'foto' && $lote) {
                    foreach (tienda_ia_datos_vacios() as $k => $v) { $d[$k] = $v; }
                    $d['modo'] = 'nueva';
                    foreach ($lote as $rel) { if ($rel !== '' && count($d['fotos']) < 40) $d['fotos'][] = $rel; }
                    $n = count($d['fotos']);
                    $d['flujo'] = 'fotos';
                    $s['datos'] = $d;
                    $s['paso']  = 'ubicacion';
                    $empujar("¡Guardé " . ($n === 1 ? 'tu foto' : 'tus ' . $n . ' fotos') . "! 📷 "
                           . "Ahora, antes de seguir, lo primero es **tu ubicación** 👇");
                    $respuesta = tienda_ia_guion('ubicacion', $d);
                    break;
                }
                $acc     = $valor !== '' ? $valor : tienda_ia_sin_tildes($texto);
                $tiendas = tienda_ia_tiendas_de($uid);
                // 📍🆕 LO PRIMERO ES LA UBICACIÓN (orden del jefe, 2026-09-20): en ESTA misma pantalla,
                // el botón MORADO (o el distrito escrito a mano) arranca la tienda nueva y se pasa
                // derecho a las fotos. Es lo PRIMERO que toca el dueño nuevo, sin ningún paso previo.
                $es_producto = ($valor === 'producto' || mb_strpos($acc, 'producto') !== false
                                || mb_strpos($acc, 'agregar') !== false || mb_strpos($acc, 'vender') !== false);
                $es_nueva    = ($valor === 'nueva' || mb_strpos($acc, 'crear') !== false || mb_strpos($acc, 'otra') !== false);
                if (!$es_producto && !$es_nueva) {
                    $tiene_gps = ($tipo === 'gps' && $lat !== null && $lng !== null);
                    $mio       = $tiene_gps ? null : tienda_ia_distrito_leido($texto);
                    if ($tiene_gps || $mio) {
                        foreach (tienda_ia_datos_vacios() as $k => $v) { $d[$k] = $v; }
                        $d['modo'] = 'nueva'; $d['flujo'] = 'fotos';
                        if ($tiene_gps) {
                            $det = tienda_ia_distrito_por_gps($lat, $lng);
                            if (!$det) { $empujar("No saqué tu zona 🤔 Toca el botón morado otra vez, o escríbeme tu distrito."); break; }
                            $d['lat'] = (float)$lat; $d['lng'] = (float)$lng;
                            $d['distrito_id']     = (int)$det['distrito_id'];
                            $d['distrito_nombre'] = (string)$det['nombre'];
                            $empujar("📍 **¡Ubicación recibida!** Estás en **" . $d['distrito_nombre'] . "** 🎯"
                                   . (!empty($det['dudoso'])
                                      ? "\nSi me equivoqué de zona, no te preocupes: al final puedes cambiarla en «Editar algo»."
                                      : ""));
                        } else {
                            $d['distrito_id']     = (int)$mio['valor'];
                            $d['distrito_nombre'] = (string)$mio['texto'];
                            $empujar("📍 ¡Listo! Estás en **" . $d['distrito_nombre'] . "** 🎯");
                        }
                        $s['datos'] = $d;
                        $s['paso']  = 'fotos';
                        $respuesta  = tienda_ia_guion('fotos', $d);
                        break;
                    }
                    // Escribió cualquier otra cosa: se le INSISTE con el botón morado (sin ubicación no
                    // sigue), y se le recuerda que también puede escribir su distrito.
                    $respuesta = tienda_ia_guion('arranque', $d, tienda_ia_arranque_extra($uid) + ['insistir' => true]);
                    break;
                }
                // 🛍️ Agregar un producto a una tienda que YA tiene
                if ($valor === 'producto' || mb_strpos($acc, 'producto') !== false || mb_strpos($acc, 'agregar') !== false || mb_strpos($acc, 'vender') !== false) {
                    if (!$tiendas) {
                        $empujar("Todavía no tienes una tienda 🙂 La armamos primero y después le ponemos los productos.");
                        $d['flujo'] = 'fotos';
                        $s['paso']  = 'ubicacion';      // 📍 primero la ubicación (2026-09-20)
                        $s['datos'] = $d;
                        $respuesta = tienda_ia_guion('ubicacion', $d);
                        break;
                    }
                    if (count($tiendas) === 1) {
                        // Una sola tienda: no se le pregunta cuál (el dueño ya sabe cuál es).
                        $d['negocio_id'] = (int)$tiendas[0]['valor'];
                        $d['nombre']     = (string)$tiendas[0]['texto'];
                        $empujar("Perfecto, va en **{$d['nombre']}** 👌");
                    } else {
                        $s['paso']  = 'tienda';
                        $s['datos'] = $d;
                        $respuesta  = tienda_ia_guion('tienda', $d, ['tiendas' => $tiendas]);
                        break;
                    }
                    $d['productos_ia']      = 0;
                    $d['producto_borrado']  = '';
                    $d['producto_opciones'] = tienda_ia_ia_producto($d, $op + ['paso' => 'producto_nombre']) ?: [];
                    $s['datos'] = $d;
                    $s['paso']  = 'producto_nombre';
                    $respuesta  = tienda_ia_guion('producto_nombre', $d);
                    break;
                }
                // 🚀 Crear su tienda (primera o una nueva): 📍🆕 ahora se empieza por LA UBICACIÓN
                // (orden del jefe, 2026-09-20) y después vienen las fotos.
                foreach (tienda_ia_datos_vacios() as $k => $v) { $d[$k] = $v; }
                $d['modo']  = 'nueva';
                $d['flujo'] = 'fotos';
                $s['datos'] = $d;
                $s['paso']  = 'ubicacion';
                $respuesta  = tienda_ia_guion('ubicacion', $d);
                break;
            }

            /* ---------- 0-quater) 📍🆕 LA UBICACIÓN: el PRIMER dato y obligatorio (2026-09-20) ----------
               Orden del jefe: *«un botón pidiendo ubicación, eso es lo primero… la ubicación como primer
               dato es vital; si no comparte su ubicación, pues no continúa»*. Con el GPS el servidor saca
               el distrito REAL (los negocios cercanos votan, igual que en el Caminante); si el dueño no
               quiere dar el GPS, se le deja ESCRIBIR su distrito (la salida que eligió el jefe) para que
               nadie quede atrapado sin poder crear su tienda. */
            case 'ubicacion': {
                // 📍 Tocó el botón morado: el navegador manda las coordenadas.
                if ($tipo === 'gps' && $lat !== null && $lng !== null) {
                    $det = tienda_ia_distrito_por_gps($lat, $lng);
                    if (!$det) {
                        $empujar("No saqué tu zona 🤔 Toca el botón morado otra vez, o escríbeme tu distrito.");
                        break;
                    }
                    $d['lat']             = (float)$lat;
                    $d['lng']             = (float)$lng;
                    $d['distrito_id']     = (int)$det['distrito_id'];
                    $d['distrito_nombre'] = (string)$det['nombre'];
                    $s['datos'] = $d;
                    $empujar("📍 **¡Ubicación recibida!** Estás en **" . $d['distrito_nombre'] . "** 🎯"
                           . (!empty($det['dudoso'])
                              ? "\nSi me equivoqué de zona, no te preocupes: al final puedes cambiarla en «Editar algo»."
                              : ""));
                    $s['paso']  = 'fotos';
                    $respuesta  = tienda_ia_guion('fotos', $d);
                    break;
                }
                // Escribió su distrito a mano (sin GPS): se reconoce contra los distritos REALES.
                $mio = tienda_ia_distrito_leido($texto);
                if ($mio) {
                    $d['distrito_id']     = (int)$mio['valor'];
                    $d['distrito_nombre'] = (string)$mio['texto'];
                    $s['datos'] = $d;
                    $empujar("📍 ¡Listo! Estás en **" . $d['distrito_nombre'] . "** 🎯");
                    $s['paso']  = 'fotos';
                    $respuesta  = tienda_ia_guion('fotos', $d);
                    break;
                }
                // Ni GPS ni un distrito reconocido: se le insiste con el mismo botón (y se le dice que
                // también puede escribir el distrito, que es la salida del jefe).
                $respuesta = tienda_ia_guion('ubicacion', $d, ['insistir' => true]);
                break;
            }

            /* ---------- 0-ter) 📷🆕 LAS FOTOS DEL ARRANQUE (el flujo nuevo: 5 fotos primero) ---------- */
            case 'fotos': {
                $respuesta = tienda_ia_paso_fotos($s, $d, $lote, $op, $empujar, $uid);
                break;
            }

            /* ---------- 0-ter-bis) 📷🛍️ LA TANDA DE FOTOS DE PRODUCTOS (2026-09-16, pedido del jefe) ------ */
            case 'productos_lote': {
                // «✅ Ya está, gracias» → se cierra con la felicitación (que ya trae sus botones).
                if ($valor === 'fin' || $valor === 'ya' || mb_strpos(tienda_ia_sin_tildes($texto), 'ya esta') !== false) {
                    $s['paso']  = 'fin';
                    $s['datos'] = $d;
                    $respuesta  = tienda_ia_guion('fin', $d);
                    break;
                }
                $respuesta = tienda_ia_paso_productos_lote($s, $d, $lote, $op, $empujar, $uid);
                break;
            }

            /* ---------- 0-ter-bis) 🏪🆕 EL TIPO DE NEGOCIO Y SUS RAMAS (orden del jefe, 2026-09-15 noche) ----------
               La tienda o local pide su ubicación · el vendedor ambulante su horario · el que vende por
               internet los distritos donde entrega. Cada rama termina en la confirmación de lo que la IA
               leyó en las fotos (nombre y rubro). */
            case 'tipo': {
                $v   = (string)($valor !== '' ? $valor : '');
                $acc = tienda_ia_sin_tildes($texto);
                if ($v === 'internet' || mb_strpos($acc, 'internet') !== false || mb_strpos($acc, 'online') !== false) {
                    $d['vendedor'] = 'domicilio';
                } elseif (in_array($v, ['fisica', 'ambulante', 'domicilio'], true)) {
                    $d['vendedor'] = $v;
                } elseif (mb_strpos($acc, 'ambul') !== false || mb_strpos($acc, 'calle') !== false) {
                    $d['vendedor'] = 'ambulante';
                } elseif (mb_strpos($acc, 'tienda') !== false || mb_strpos($acc, 'local') !== false || mb_strpos($acc, 'puesto') !== false) {
                    $d['vendedor'] = 'fisica';
                } else {
                    $empujar("Toca una de las tres 👇");
                    break;
                }
                $s['datos'] = $d;
                // 🛵 Ambulante → su horario (el jefe pidió 3 o 4 opciones de horario).
                if ((string)$d['vendedor'] === 'ambulante') {
                    $empujar("🛵 ¡Vendedor ambulante! Entonces dime tu horario.");
                    $s['paso']  = 'horario';
                    $respuesta  = tienda_ia_guion('horario', $d);
                    break;
                }
                // 🌐 Vende por internet → los distritos donde entrega (grilla 2×2 con miniatura + azul «todos»).
                if ((string)$d['vendedor'] === 'domicilio') {
                    $empujar("🌐 ¡Vendes por internet! Dime en qué distritos entregas 👇");
                    $s['paso']  = 'entregas';
                    $respuesta  = tienda_ia_guion('entregas', $d);
                    break;
                }
                // 🏪 Tienda o local → su CALLE (la zona ya la tenemos desde el primer paso: 2026-09-20).
                $empujar("🏪 ¡Tienda o local! Dime tu **calle o una referencia** (por ejemplo «frente al mercado») 📍");
                $s['paso']  = 'direccion';
                $respuesta  = tienda_ia_guion('direccion', $d);
                break;
            }

            /* ---------- 0-ter-ter) 🚚🆕 LOS DISTRITOS DE ENTREGA (se pueden marcar varios) ---------- */
            case 'entregas': {
                $v = (string)$valor;
                $ya = array_map('intval', (array)($d['entregas'] ?? []));
                if ($v === 'todos') {
                    $ids = [];
                    foreach (tienda_ia_distritos() as $dd) $ids[] = (int)($dd['valor'] ?? 0);
                    $d['entregas']       = array_values(array_filter($ids));
                    $d['distrito_todas'] = 1;
                    $empujar("🗺️ ¡Perfecto! Entregas en **toda la zona**: Chimbote, Nuevo Chimbote, Coishco y Santa.");
                    $s['datos'] = $d;
                    $s['paso']  = 'nombre_ok';
                    $respuesta  = tienda_ia_guion('nombre_ok', $d);
                    break;
                }
                if ($v === 'seguir' || ($v === '' && $ya)) {
                    if (!$ya) { $empujar("Toca al menos un distrito (o «🗺️ Todos los distritos») 👇"); break; }
                    $s['datos'] = $d;
                    $s['paso']  = 'nombre_ok';
                    $respuesta  = tienda_ia_guion('nombre_ok', $d);
                    break;
                }
                $id = (int)$v;
                if ($id <= 0) { $empujar("Toca un distrito de la lista 👇"); break; }
                if (in_array($id, $ya, true)) { $ya = array_values(array_diff($ya, [$id])); }
                else { $ya[] = $id; $ya = array_values(array_unique($ya)); }
                $d['entregas'] = $ya;
                $s['datos'] = $d;
                $respuesta  = tienda_ia_guion('entregas', $d);
                break;
            }

            /* ---------- 0-quinquies) ✏️🆕 EL MENÚ DE EDICIÓN DEL FINAL ----------
               El jefe lo pidió así: *«"editar mis productos" o "editar otra cosa"… en una grilla de dos
               columnas, o también la opción de no editar nada»*. */
            case 'editar': {
                $acc = ($valor !== '' ? $valor : tienda_ia_sin_tildes($texto));
                $s['datos'] = $d;
                if ($valor === 'productos' || mb_strpos($acc, 'producto') !== false) {
                    $s['paso']  = 'producto_elegir';
                    $respuesta  = tienda_ia_guion('producto_elegir', $d);
                    break;
                }
                if ($valor === 'portada' || mb_strpos($acc, 'portada') !== false) {
                    $s['paso']  = 'portada';
                    $respuesta  = tienda_ia_guion('portada', $d);
                    break;
                }
                if ($valor === 'datos' || mb_strpos($acc, 'dato') !== false || mb_strpos($acc, 'otra cosa') !== false || mb_strpos($acc, 'otro') !== false) {
                    $empujar("¿Qué cambiamos? 👇");
                    $respuesta = $menu_editar;
                    break;
                }
                // «✅ No editar nada» (o cualquier otra cosa): se pasa a la última pregunta, el WhatsApp.
                $empujar("¡Perfecto! 👌");
                $s['paso']  = 'whatsapp';
                $respuesta  = tienda_ia_guion('whatsapp', $d);
                break;
            }

            /* 🛍️🆕 Elegir cuál de sus dos productos quiere cambiar. */
            case 'producto_elegir': {
                $acc = ($valor !== '' ? $valor : tienda_ia_sin_tildes($texto));
                if ($valor === 'volver' || mb_strpos($acc, 'volver') !== false) {
                    $s['paso'] = 'editar';
                    $respuesta = tienda_ia_guion('editar', $d);
                    break;
                }
                $i = (int)$valor;
                if ($i < 0 || empty($d['productos'][$i]['titulo'])) {
                    foreach ((array)$d['productos'] as $k => $p) {
                        if (!empty($p['titulo']) && mb_strpos($acc, tienda_ia_sin_tildes((string)$p['titulo'])) !== false) { $i = (int)$k; break; }
                    }
                }
                if (empty($d['productos'][$i]['titulo'])) { $empujar("Toca uno de tus productos 👇"); break; }
                $d['producto_editando'] = $i;
                $s['datos'] = $d;
                $s['paso']  = 'producto_editar';
                $respuesta = tienda_ia_guion('producto_editar', $d);
                break;
            }

            /* ✏️🆕 Qué le cambiamos a ese producto: la foto o la descripción. */
            case 'producto_editar': {
                $i    = (int)($d['producto_editando'] ?? 0);
                $prod = (string)($d['productos'][$i]['titulo'] ?? '');
                $acc  = ($valor !== '' ? $valor : tienda_ia_sin_tildes($texto));
                if ($prod === '') { $s['paso'] = 'producto_elegir'; $respuesta = tienda_ia_guion('producto_elegir', $d); break; }
                if ($valor === 'foto' || mb_strpos($acc, 'foto') !== false) {
                    $d['editando'] = 'producto_foto';
                    $s['datos'] = $d;
                    $empujar("📷 Mándame la foto nueva de **{$prod}** (o toca una de las que ya subiste 👇)");
                    $s['paso']  = 'producto_editar_foto';
                    $respuesta  = tienda_ia_guion('producto_editar_foto', $d);
                    break;
                }
                if ($valor === 'texto' || mb_strpos($acc, 'descrip') !== false || mb_strpos($acc, 'texto') !== false) {
                    $d['editando'] = 'producto_texto';
                    $s['datos'] = $d;
                    $empujar("✍️ Escríbeme (o dicta 🎙️) cómo quieres que se describa **{$prod}**: yo le doy el formato bonito.");
                    $s['paso']  = 'producto_editar_texto';
                    $respuesta  = tienda_ia_guion('producto_editar_texto', $d);
                    break;
                }
                if ($valor === 'volver' || mb_strpos($acc, 'volver') !== false) {
                    $s['paso'] = 'producto_elegir';
                    $respuesta = tienda_ia_guion('producto_elegir', $d);
                    break;
                }
                $empujar("¡Listo! 👌");
                $s['paso']  = 'editar';
                $respuesta  = tienda_ia_guion('editar', $d);
                break;
            }

            /* 📷🆕 CAMBIAR LA FOTO DEL PRODUCTO (subiendo una nueva o usando una de las 8). */
            case 'producto_editar_foto': {
                $i    = (int)($d['producto_editando'] ?? 0);
                $prod = (string)($d['productos'][$i]['titulo'] ?? '');
                if ($prod === '') { $s['paso'] = 'producto_elegir'; $respuesta = tienda_ia_guion('producto_elegir', $d); break; }
                $rel = '';
                if ($tipo === 'foto' && $lote) {
                    $g = tienda_ia_guardar_foto($lote[0], $uid, !empty($lote[0]['prueba']));
                    if (!empty($g['ok'])) $rel = (string)$g['rel'];
                    else $empujar("No pude guardar esa foto 😅 Prueba otra vez.");
                } elseif ($tipo === 'opcion' && strpos((string)$valor, 'usar_foto:') === 0) {
                    $ix  = (int)substr((string)$valor, 10);
                    $ori = (string)($d['fotos'][$ix] ?? '');
                    if ($ori !== '') $rel = tienda_ia_copiar_foto($ori);
                }
                if ($rel !== '') {
                    if (tienda_ia_producto_foto_cambiar($s, $prod, $rel)) {
                        $d['productos'][$i]['fotos'] = [$rel];
                        $empujar("✅ Le puse esa foto a **{$prod}**.");
                    } else {
                        $empujar("No pude cambiar la foto 😅 Prueba otra vez.");
                    }
                } elseif ($valor !== 'listo' && $tipo === 'foto') {
                    break;   // ya avisó del fallo
                }
                $s['datos'] = $d;
                $s['paso']  = 'producto_editar';
                $respuesta  = tienda_ia_guion('producto_editar', $d);
                break;
            }

            /* 📝🆕 CAMBIAR LA DESCRIPCIÓN DEL PRODUCTO: lo que él escriba pasa por el copywriting. */
            case 'producto_editar_texto': {
                $i    = (int)($d['producto_editando'] ?? 0);
                $prod = (string)($d['productos'][$i]['titulo'] ?? '');
                if ($prod === '') { $s['paso'] = 'producto_elegir'; $respuesta = tienda_ia_guion('producto_elegir', $d); break; }
                $suyo = tienda_ia_limpiar_largo($texto, 600);
                if ($suyo === '' && $valor !== 'ia') {
                    $empujar("Escríbeme aunque sea una línea de cómo es **{$prod}** ✍️");
                    break;
                }
                $txt = tienda_ia_ia_descripcion_producto($d, $prod, (array)($d['productos'][$i]['fotos'] ?? []), $op, $suyo);
                if ($txt === '') $txt = $suyo;                       // sin IA: se guarda lo que escribió él
                if ($txt !== '' && tienda_ia_producto_texto_cambiar($s, $prod, $txt)) {
                    $d['productos'][$i]['descripcion'] = $txt;
                    $empujar("✅ Así quedó la descripción de **{$prod}**:\n«{$txt}»");
                } else {
                    $empujar("No pude guardar la descripción 😅 Prueba otra vez.");
                }
                $s['datos'] = $d;
                $s['paso']  = 'producto_editar';
                $respuesta  = tienda_ia_guion('producto_editar', $d);
                break;
            }

            /* 🖼️🆕 CAMBIAR LA PORTADA (una foto nueva o una de las 8). */
            case 'portada': {
                $rel = '';
                if ($tipo === 'foto' && $lote) {
                    $g = tienda_ia_guardar_foto($lote[0], $uid, !empty($lote[0]['prueba']));
                    if (!empty($g['ok'])) $rel = (string)$g['rel'];
                } elseif ($tipo === 'opcion' && strpos((string)$valor, 'usar_foto:') === 0) {
                    $ix = (int)substr((string)$valor, 10);
                    $rel = (string)($d['fotos'][$ix] ?? '');
                }
                if ($rel !== '') {
                    if (tienda_ia_portada_poner($s, $rel)) {
                        // La elegida pasa al principio de la lista (es la que se ve primero).
                        $d['fotos'] = array_values(array_unique(array_merge([$rel], (array)$d['fotos'])));
                        $empujar("✅ ¡Listo! Esa es tu portada 🖼️");
                    } else {
                        $empujar("No pude cambiar la portada 😅 Prueba otra vez.");
                    }
                }
                $s['datos'] = $d;
                $s['paso']  = 'editar';
                $respuesta  = tienda_ia_guion('editar', $d);
                break;
            }

            /* ---------- 0-quater) ✅ EL NOMBRE QUE LA IA LEYÓ EN EL LETRERO ---------- */
            case 'nombre_ok': {
                $acc   = ($valor !== '' ? $valor : tienda_ia_sin_tildes($texto));
                $es_si = ($valor === 'si' || $acc === 'si' || mb_strpos($acc, 'asi se llama') !== false
                          || mb_strpos($acc, 'sí,') === 0 || mb_strpos($acc, 'si,') === 0 || mb_strpos($acc, 'correcto') !== false);
                if ($es_si && (string)$d['letrero'] !== '') {
                    $d['nombre'] = (string)$d['letrero'];
                    $empujar("¡**" . $d['nombre'] . "**! ✅");
                } elseif ($valor === 'otro') {
                    // Toca «✏️ Es otro nombre»: se le pregunta cómo se llama (paso `nombre`).
                    // ⚠️ Aquí se mira el VALOR, no el texto: los chips mandan su texto («✏️ Es otro
                    // nombre») y validarlo como nombre haría que la IA lo evaluara como tal.
                    $s['paso']  = 'nombre';
                    $s['datos'] = $d;
                    $respuesta  = tienda_ia_guion('nombre', $d);
                    break;
                } else {
                    // Escribió el nombre a mano: la misma validación de siempre (que sea un NOMBRE).
                    $v = tienda_ia_nombre_validar($d, $texto, $op);
                    if (empty($v['ok'])) { $empujar($v['frase']); break; }
                    $d['nombre'] = (string)$v['nombre'];
                    if ((string)$v['frase'] !== '') $empujar((string)$v['frase']);
                }
                if (empty($d['rubro_opciones'])) $d['rubro_opciones'] = tienda_ia_chips_prototipo($d, 4);
                $s['datos'] = $d;
                $s['paso']  = 'rubro_ok';
                $respuesta  = tienda_ia_guion('rubro_ok', $d);
                break;
            }

            /* ---------- 0-quinquies) 🏷️ EL RUBRO QUE LA IA DEDUJO DE LAS FOTOS ---------- */
            case 'rubro_ok': {
                $id = (int)($valor !== '' ? $valor : 0);
                if (!$id && $texto !== '') {                     // lo escribió: se busca sin tildes
                    $buscado = tienda_ia_sin_tildes($texto);
                    foreach (tienda_ia_rubros() as $r) {
                        if (mb_strpos(tienda_ia_sin_tildes($r['nombre']), $buscado) !== false || mb_strpos($buscado, tienda_ia_sin_tildes($r['nombre'])) !== false) {
                            $id = (int)$r['id']; break;
                        }
                    }
                }
                if (!$id) {
                    // «✏️ Ninguno de estos» (o un rubro que no está): se le da la lista de los rubros
                    // con más tiendas y se queda en este paso hasta que elija uno.
                    $empujar("Sin problema 👇 ¿Cuál es el rubro o categoría de tu tienda?");
                    $d['rubro_opciones'] = tienda_ia_rubros_chips('', 8);
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_guion('rubro_ok', $d);
                    break;
                }
                foreach (tienda_ia_rubros() as $r) {
                    if ((int)$r['id'] === $id) { $d['rubro_id'] = (int)$id; $d['rubro_nombre'] = (string)$r['nombre']; break; }
                }
                if (empty($d['rubro_id'])) { $empujar("No encontré ese rubro 🤔 Toca uno de la lista."); break; }
                $d['rubro_opciones'] = [];
                $empujar("🏷️ **" . $d['rubro_nombre'] . "** — ¡buena! ✅");
                $s['datos'] = $d;
                // 🏷️ Y ahora los otros rubros o categorías (hasta 4 en total), como en el flujo viejo.
                $s['paso']  = 'rubro_mas';
                $respuesta  = tienda_ia_guion('rubro_mas', $d, ['opciones' => tienda_ia_rubros_para_sumar($d)]);
                break;
            }

            /* ---------- 0-sexies) 📱 EL TELÉFONO LEÍDO: se pregunta SIEMPRE antes de usarlo ---------- */
            // 🔴 Es el único dato que puede hacer daño: el número de un aviso muchas veces es el de la
            // IMPRENTA que lo hizo (o del dueño anterior). Y el módulo, si ese número ya existe, ACTUALIZA
            // la tienda de ese número: sin confirmar, le pegaríamos las fotos a la tienda de otra persona.
            case 'tel_leido': {
                $acc   = ($valor !== '' ? $valor : tienda_ia_sin_tildes($texto));
                $es_si = ($valor === 'si' || $acc === 'si' || mb_strpos($acc, 'es mi') !== false
                          || mb_strpos($acc, 'es mio') !== false || mb_strpos($acc, 'correcto') !== false || mb_strpos($acc, 'si,') === 0);
                if (!$es_si) {
                    $s['paso']  = 'whatsapp';
                    $s['datos'] = $d;
                    $respuesta  = tienda_ia_guion('whatsapp', $d);
                    break;
                }
                $d['telefono_ok'] = 1;
                $d['whatsapp']    = (string)$d['telefono_leido'];
                $s['datos'] = $d;
                $empujar("¡Perfecto! 📱 **" . $d['whatsapp'] . "** es el número de tu tienda.");
                // 🚀 Y AQUÍ YA SE PUEDE PUBLICAR: nombre, rubro, 5 fotos y WhatsApp. Se publica SIN
                // decírselo (orden del jefe) y la conversación sigue como si nada.
                tienda_ia_publicar_silencioso($s, $d);
                $s['paso']  = 'vendedor';
                $s['datos'] = $d;
                $respuesta  = tienda_ia_guion('vendedor', $d);
                break;
            }

            /* ---------- 1) EL NOMBRE (flujo viejo, respaldo del nuevo y «es otro nombre») ---------- */
            case 'nombre': {
                $v = tienda_ia_nombre_validar($d, $texto, $op);
                if (empty($v['ok'])) { $empujar($v['frase']); break; }
                $d['nombre'] = (string)$v['nombre'];
                $s['datos']  = $d;
                if ((string)$v['frase'] !== '') $empujar((string)$v['frase']);
                if ($d['editando'] === 'nombre') {
                    $d['editando'] = '';
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_edicion_lista($s, $d, 'el nombre', $empujar);
                    break;
                }
                // 📷🆕 En el flujo nuevo el nombre se pregunta DESPUÉS de las fotos (solo cuando la IA
                // no pudo leer el letrero): lo que sigue es el rubro que ella dedujo, no el relato.
                if ((string)$d['flujo'] === 'fotos') {
                    if (empty($d['rubro_opciones'])) $d['rubro_opciones'] = tienda_ia_chips_prototipo($d, 4);
                    $s['datos'] = $d;
                    $s['paso']  = 'rubro_ok';
                    $respuesta  = tienda_ia_guion('rubro_ok', $d);
                    break;
                }
                $respuesta = tienda_ia_guion('trato', $d);
                $s['paso'] = 'trato';
                break;
            }

            /* ---------- 2) DE QUÉ TRATA (y de aquí sale el RUBRO) ---------- */
            case 'trato': {
                $cuento = tienda_ia_limpiar_largo($texto, 600);
                if (tienda_ia_palabras($cuento) < (int)TIENDA_IA_TRATO_MIN_PALABRAS) {
                    $empujar("Cuéntame un poco más 👀 ¿Qué vendes y dónde?");
                    break;
                }
                $d['trato'] = $cuento;
                // 📝 Al cambiar «de qué trata» se rehace el copy: la descripción la vuelve a escribir
                // la IA con lo nuevo (no se le queda la vieja).
                $d['descripcion_ia'] = '';
                if ($d['editando'] === 'trato') {
                    $d['editando'] = '';
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_edicion_lista($s, $d, 'de qué trata', $empujar);
                    break;
                }
                $cands = tienda_ia_rubros_candidatos($d['nombre'] . ' ' . $cuento, 8);
                $ia = $cands ? tienda_ia_ia_rubro($d, $cuento, $cands, $op) : null;
                $ids = $ia['ids'] ?? [];
                if (!$ids && $cands) $ids = [(int)$cands[0]['id']];
                $opciones = [];
                foreach ($ids as $rid) {
                    foreach ($cands as $c) {
                        if ((int)$c['id'] === (int)$rid) {
                            $opciones[] = ['texto' => ($c['icono'] !== '' ? $c['icono'] . ' ' : '') . $c['nombre'], 'valor' => (string)$c['id']];
                            break;
                        }
                    }
                }
                if (!$opciones) {
                    // Ni la IA ni las claves acertaron: se le muestran los rubros más usados.
                    foreach (array_slice(tienda_ia_rubros_candidatos('', 6), 0, 6) as $c) {
                        $opciones[] = ['texto' => ($c['icono'] !== '' ? $c['icono'] . ' ' : '') . $c['nombre'], 'valor' => (string)$c['id']];
                    }
                }
                $d['rubro_opciones'] = $opciones;
                if (!empty($ia['frase'])) $empujar($ia['frase']);
                $s['datos'] = $d;
                $s['paso']  = 'rubro';
                $respuesta = tienda_ia_guion('rubro', $d, ['opciones' => $opciones]);
                break;
            }

            /* ---------- 3) EL RUBRO ---------- */
            case 'rubro': {
                $id = (int)($valor !== '' ? $valor : 0);
                if (!$id && $texto !== '') {                    // lo escribió: se busca sin tildes
                    $buscado = tienda_ia_sin_tildes($texto);
                    foreach (tienda_ia_rubros() as $r) {
                        if (mb_strpos(tienda_ia_sin_tildes($r['nombre']), $buscado) !== false || mb_strpos($buscado, tienda_ia_sin_tildes($r['nombre'])) !== false) {
                            $id = $r['id']; break;
                        }
                    }
                }
                if (!$id) {
                    $empujar("No encontré ese rubro 🤔 Toca uno 👇");
                    break;
                }
                foreach (tienda_ia_rubros() as $r) {
                    if ($r['id'] === $id) { $d['rubro_id'] = $id; $d['rubro_nombre'] = $r['nombre']; break; }
                }
                $d['rubro_opciones'] = [];
                $s['datos'] = $d;
                if ($d['editando'] === 'rubro') {
                    // Editando una tienda YA publicada: se guarda en la base y se vuelve a la tarjeta.
                    $d['editando'] = '';
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_edicion_lista($s, $d, 'el rubro principal', $empujar);
                    break;
                }
                // 🏷️ Ahora se le pregunta si quiere sumar MÁS rubros o categorías (hasta 4 en total).
                $s['paso']  = 'rubro_mas';
                $s['datos'] = $d;
                $respuesta = tienda_ia_guion('rubro_mas', $d, ['opciones' => tienda_ia_rubros_para_sumar($d)]);
                break;
            }

            /* ---------- 3-bis) 🏷️ LOS OTROS RUBROS (hasta 4 en total) ---------- */
            case 'rubro_mas': {
                $max   = (int)TIENDA_IA_RUBROS_MAX;
                $max   = max(2, $max);
                $acc   = $valor !== '' ? $valor : tienda_ia_sin_tildes($texto);
                $extra = (array)$d['rubros_extra'];
                $cuantos = 1 + count($extra);

                // ¿Tocó uno de los rubros que le ofrecimos?
                $nuevo = 0;
                $idv = (int)$valor;
                if ($idv > 0) {
                    $existe = false;
                    foreach (tienda_ia_rubros() as $r) {
                        if ((int)$r['id'] !== $idv) continue;
                        $existe = true;
                        if ((int)$d['rubro_id'] === $idv) { $empujar("Ese ya es tu rubro **principal** 😉"); break; }
                        $repetido = false;
                        foreach ($extra as $e) { if ((int)$e['id'] === $idv) { $repetido = true; break; } }
                        if ($repetido) { $empujar("Ese ya lo tienes 👌"); break; }
                        if ($cuantos >= $max) { $empujar("Ya tienes los **{$max}** rubros 😅 Ese no entra."); break; }
                        $nuevo = (int)$r['id'];
                        $d['rubros_extra'][] = ['id' => (int)$r['id'], 'nombre' => (string)$r['nombre']];
                        $empujar("¡Sumado! 🏷️ **" . (string)$r['nombre'] . "**");
                        break;
                    }
                    // ⚠️ Solo se avisa «no encontré» si de verdad no existe ese rubro (antes también
                    //    salía cuando el que tocó era el principal o uno repetido: dos mensajes seguidos).
                    if (!$existe) $empujar("No encontré ese rubro 🤔 Toca uno de la lista.");
                }

                $sigue = ($valor === 'seguir' || $acc === 'seguir' || mb_strpos($acc, 'listo') !== false
                          || mb_strpos($acc, 'solo el principal') !== false
                          || $acc === 'no' || $acc === 'nada' || $acc === 'ninguno');
                $cuantos = 1 + count((array)$d['rubros_extra']);
                if ($sigue || $cuantos >= $max) {
                    if ($cuantos >= $max && !$sigue) $empujar("Listo, ya tienes tus **{$max}** rubros 🏷️");
                    $s['datos'] = $d;
                    if ($d['editando'] === 'rubro_mas') {
                        $d['editando'] = '';
                        $s['datos'] = $d;
                        $respuesta = tienda_ia_edicion_lista($s, $d, 'los rubros', $empujar);
                        break;
                    }
                    // 🚀🆕 FLUJO NUEVO: con el nombre y los rubros confirmados **SE CREA LA TIENDA** (se
                    // publica al instante, con su copyright) **y sus dos productos**, y se le ofrece
                    // editar. El WhatsApp se pregunta DESPUÉS (es lo último del flujo).
                    if ((string)$d['flujo'] === 'fotos') {
                        $s['datos'] = $d;
                        return tienda_ia_crear_tienda_final($s, $d, $msgs, $empujar, $op);
                    }
                    $s['paso']  = 'fotos_tienda';
                    $respuesta = tienda_ia_guion('fotos_tienda', $d);
                    break;
                }
                // Sigue sumando: se le vuelven a ofrecer los que quedan (sin los que ya tiene).
                $s['datos'] = $d;
                $respuesta = tienda_ia_guion('rubro_mas', $d, ['opciones' => tienda_ia_rubros_para_sumar($d)]);
                break;
            }

            /* ---------- 5) LAS FOTOS DE LA TIENDA (de 1 a 8, y la galería acepta VARIAS) ---------- */
            case 'fotos_tienda': {
                $max = (int)TIENDA_IA_FOTOS_TIENDA_MAX;
                $min = (int)TIENDA_IA_FOTOS_TIENDA_MIN;
                // ¿Tocó «Listo»?
                if ($tipo === 'opcion' && $valor === 'seguir') {
                    if (count($d['fotos']) < $min) {
                        $empujar("Mándame al menos " . $min . " foto de tu tienda 📷");
                        break;
                    }
                    // Si venía de «✏️ Editar algo», se guardan las fotos nuevas y se vuelve a la tarjeta.
                    if ($d['editando'] === 'fotos_tienda') {
                        $d['editando'] = '';
                        $s['datos'] = $d;
                        $respuesta = tienda_ia_edicion_lista($s, $d, 'las fotos', $empujar);
                        break;
                    }
                    $s['paso'] = 'vendedor';
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_guion('vendedor', $d);
                    break;
                }
                if ($tipo === 'opcion' && $valor === 'otra') {
                    $empujar("¡Dale! Toca **📷** y elige «**Abrir cámara**» o «**Abrir galería**» 📷");
                    break;
                }
                // 🚚 «No tengo local: lo llevo a su casa» (el botón que sale cuando la IA ve que es un
                // servicio que se lleva: camiones, arena, piedra…). Se salta la pregunta de cómo vende.
                if ($tipo === 'opcion' && $valor === 'sin_local') {
                    $d['vendedor'] = 'domicilio';
                    $d['sugerir_domicilio'] = 0;
                    $empujar("Listo: como no tienes local, en tu tienda va a decir que **lo llevas a su casa** 🚚");
                    $s['datos'] = $d;
                    $s['paso']  = 'distrito';
                    $respuesta = tienda_ia_guion('distrito', $d, ['distritos' => tienda_ia_distritos()]);
                    break;
                }
                if ($tipo !== 'foto' || !$lote) {
                    $empujar("Necesito una foto 📷 Toca **📷** y elige «**Abrir cámara**» o «**Abrir galería**».");
                    break;
                }
                // Ya no cabe ninguna más: se guardan las que hay y se sigue.
                $espacio = max(0, $max - count($d['fotos']));
                if ($espacio <= 0) {
                    $empujar("Ya tengo tus **{$max}** fotos 📸 ¡Seguimos!");
                    $s['paso'] = 'vendedor';
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_guion('vendedor', $d);
                    break;
                }
                // Si eligió más de las que caben, se guardan las primeras y se le dice.
                $sobran = max(0, count($lote) - $espacio);
                if ($sobran) $lote = array_slice($lote, 0, $espacio);

                $nuevas = [];
                $fallos = 0;
                foreach ($lote as $f) {
                    $g = tienda_ia_guardar_foto($f, $uid, !empty($f['prueba']));
                    if (!empty($g['ok'])) $nuevas[] = (string)$g['rel']; else $fallos++;
                }
                if (!$nuevas) {
                    $empujar("No pude guardar la foto 😅 Revisa que sea una imagen y prueba otra vez.");
                    break;
                }
                foreach ($nuevas as $rel) $d['fotos'][] = $rel;
                if ($sobran)  $empujar("Te guardo las primeras " . count($nuevas) . ": el tope son **{$max}** fotos de tienda 😉");
                if ($fallos)  $empujar("(" . $fallos . " no se pudo subir: puede que sea muy pesada.)");

                // 👁️ TODAS las fotos nuevas se miran en UNA sola llamada (antes: una por foto).
                $n_antes = count($d['fotos']) - count($nuevas);
                $ia = tienda_ia_ia_fotos($d, 'tienda', $nuevas, $n_antes, $max, $op);

                if ($ia !== null && !empty($ia['capturas'])) {
                    $fuera = [];
                    foreach ($ia['capturas'] as $ix) { if (isset($nuevas[$ix])) $fuera[] = $nuevas[$ix]; }
                    $que = tienda_ia_captura_decision($d, 'fotos_tienda', true);
                    if ($que === 'rechazar' && $fuera) {
                        // 🚫 La captura no entra: se borra del hosting y se le pide una foto real.
                        foreach ($fuera as $rel) {
                            $k = array_search($rel, $d['fotos'], true);
                            if ($k !== false) unset($d['fotos'][$k]);
                            tienda_ia_borrar_foto($rel);
                        }
                        $d['fotos'] = array_values($d['fotos']);
                        $empujar(($ia['texto'] !== '' ? $ia['texto'] . "\n\n" : '')
                               . "Ojo: una de esas es una **captura de pantalla** y esas no las puedo subir (se ven tus pestañas). "
                               . "Si es una **foto de verdad** —tu letrero, tu menú—, mándamela otra vez y la acepto 👌");
                        $s['datos'] = $d;
                        $respuesta = tienda_ia_guion('fotos_tienda', $d);
                        break;
                    }
                    $empujar("Las tomo como tuyas 👍");
                } elseif ($ia !== null) {
                    tienda_ia_captura_decision($d, 'fotos_tienda', false);   // se limpia el aviso
                }
                if ($ia !== null && $ia['texto'] !== '') {
                    $empujar($ia['texto']);
                    // 📷 Se guarda lo que la IA VIO (no lo que dijo el dueño): con eso se le proponen
                    // los productos después, que es lo que pidió el jefe (productos «en base a sus fotos»).
                    $d['visto'][] = tienda_ia_limpiar($ia['texto'], 180);
                    if (count($d['visto']) > 5) $d['visto'] = array_slice($d['visto'], -5);
                }
                // 🧠 Y lo que la IA PENSÓ del negocio (LÍNEA 3): si dice DOMICILIO, al dueño se le
                // ofrece el botón «🚚 No tengo local: lo llevo a su casa» (pedido del jefe: *«ahí no le
                // estamos dando al usuario ninguna opción»*). Solo se propone hasta que él decida.
                if ($ia !== null && !empty($ia['sugerir'])) {
                    $d['sugerir_domicilio'] = (strtoupper((string)$ia['sugerir']) === 'DOMICILIO') ? 1 : 0;
                }

                $n = count($d['fotos']);
                if ($n >= $max) {
                    // Con el tope de fotos alcanzado se pasa solo a «cómo atiendes».
                    $empujar("¡Listo, ya tengo tus **{$n}** fotos de {$d['nombre']}! 📸");
                    $s['paso'] = 'vendedor';
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_guion('vendedor', $d);
                    break;
                }
                $s['datos'] = $d;
                $respuesta = tienda_ia_guion('fotos_tienda', $d);
                break;
            }

            /* ---------- 6) EL NOMBRE DEL PRODUCTO ---------- */
            case 'producto_nombre': {
                $prod = tienda_ia_limpiar($texto, 60);
                if ($prod === '' && $valor !== '') {
                    foreach (($d['producto_opciones'] ?? []) as $o) { if ((string)$o['valor'] === (string)$valor) $prod = tienda_ia_limpiar($o['texto'], 60); }
                }
                if (mb_strlen($prod) < 2) {
                    $empujar("¿Cómo se llama el producto?");
                    break;
                }
                $d['productos'][] = ['titulo' => $prod, 'fotos' => [], 'precio' => null];
                $d['producto_indice'] = count($d['productos']) - 1;
                $d['producto_opciones'] = [];
                $s['datos'] = $d;
                $s['paso'] = 'producto_fotos';
                $respuesta = tienda_ia_guion('producto_fotos', $d, ['producto' => $prod]);
                break;
            }

            /* ---------- 7) LAS FOTOS DEL PRODUCTO ---------- */
            case 'producto_fotos': {
                $i = (int)$d['producto_indice'];
                if (empty($d['productos'][$i])) { $s['paso'] = 'producto_nombre'; $respuesta = tienda_ia_guion('producto_nombre', $d); break; }
                $prod = (string)$d['productos'][$i]['titulo'];

                if ($tipo === 'opcion' && $valor === 'seguir') {
                    if (empty($d['productos'][$i]['fotos'])) { $empujar("Mándame una foto de **{$prod}** 📷"); break; }
                    $s['paso'] = 'producto_precio';
                    $respuesta = tienda_ia_guion('producto_precio', $d, ['producto' => $prod]);
                    break;
                }
                if ($tipo === 'opcion' && $valor === 'otra') {
                    $empujar("¡Dale! 📷 Toca **📷 Abrir cámara** o **🖼️ Abrir galería**.");
                    break;
                }
                // 📷 USAR UNA FOTO QUE YA SUBIÓ (pedido del jefe, 2026-09-15: *«como ya tenemos las
                // fotos cargadas puedes mostrarle un slide con las fotos ya subidas para que en base a
                // eso también cree producto»*). Llega como `usar_foto:<n>` desde las miniaturas.
                if ($tipo === 'opcion' && strpos($valor, 'usar_foto:') === 0) {
                    $ix = (int)substr($valor, 10);
                    $rel = (string)($d['fotos'][$ix] ?? '');
                    if ($rel === '') { $empujar("No encontré esa foto 🤔 Prueba con otra."); break; }
                    $copia = tienda_ia_copiar_foto($rel);
                    if ($copia === '') { $empujar("No pude usar esa foto 😅 Mándame una nueva."); break; }
                    $d['productos'][$i]['fotos'][] = $copia;
                    $empujar("Perfecto, le puse esa foto a **{$prod}** 👌");
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_guion('producto_fotos', $d, ['producto' => $prod]);
                    break;
                }
                if ($tipo !== 'foto' || !$lote) {
                    $empujar("Necesito una foto de **{$prod}** 📷 Toca **📷 Abrir cámara** o **🖼️ Abrir galería**, "
                           . "o toca una de las fotos que ya subiste 👇");
                    break;
                }
                // 📷 La galería puede mandar varias: se guardan (respetando el tope) y se miran de una vez.
                $maxp    = (int)TIENDA_IA_FOTOS_PRODUCTO_MAX;
                $espacio = max(0, $maxp - count($d['productos'][$i]['fotos']));
                if ($espacio <= 0) {
                    $s['paso'] = 'producto_precio';
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_guion('producto_precio', $d, ['producto' => $prod]);
                    break;
                }
                if (count($lote) > $espacio) $lote = array_slice($lote, 0, $espacio);
                $nuevas = [];
                foreach ($lote as $f) {
                    $g = tienda_ia_guardar_foto($f, $uid, !empty($f['prueba']));
                    if (!empty($g['ok'])) $nuevas[] = (string)$g['rel'];
                }
                if (!$nuevas) {
                    $empujar("No pude guardar la foto 😅 Prueba otra vez.");
                    break;
                }
                $n_antes = count($d['productos'][$i]['fotos']);
                foreach ($nuevas as $rel) $d['productos'][$i]['fotos'][] = $rel;
                $n = count($d['productos'][$i]['fotos']);
                $ia = tienda_ia_ia_fotos($d, 'producto', $nuevas, $n_antes, $maxp, $op);

                if ($ia !== null && !empty($ia['capturas'])) {
                    $fuera = [];
                    foreach ($ia['capturas'] as $ix) { if (isset($nuevas[$ix])) $fuera[] = $nuevas[$ix]; }
                    $que = tienda_ia_captura_decision($d, 'producto_fotos', true);
                    if ($que === 'rechazar' && $fuera) {
                        foreach ($fuera as $rel) {
                            $k = array_search($rel, $d['productos'][$i]['fotos'], true);
                            if ($k !== false) unset($d['productos'][$i]['fotos'][$k]);
                            tienda_ia_borrar_foto($rel);
                        }
                        $d['productos'][$i]['fotos'] = array_values($d['productos'][$i]['fotos']);
                        $empujar(($ia['texto'] !== '' ? $ia['texto'] . "\n\n" : '')
                               . "Ojo: una de esas es una **captura de pantalla** y esas no las puedo subir. "
                               . "Si es una **foto de verdad**, mándamela otra vez y la acepto 👌");
                        $s['datos'] = $d;
                        $respuesta = tienda_ia_guion('producto_fotos', $d, ['producto' => $prod]);
                        break;
                    }
                    $empujar("Las tomo como tuyas 👍");
                } elseif ($ia !== null) {
                    tienda_ia_captura_decision($d, 'producto_fotos', false);
                }
                if ($ia !== null && $ia['texto'] !== '') $empujar($ia['texto']);

                $n = count($d['productos'][$i]['fotos']);
                if ($n >= $maxp) {
                    $empujar("😋 ¡Ya está!");
                    $s['paso'] = 'producto_precio';
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_guion('producto_precio', $d, ['producto' => $prod]);
                    break;
                }
                $s['datos'] = $d;
                $respuesta = tienda_ia_guion('producto_fotos', $d, ['producto' => $prod]);
                break;
            }

            /* ---------- 8) EL PRECIO ---------- */
            case 'producto_precio': {
                $i = (int)$d['producto_indice'];
                $prod = (string)($d['productos'][$i]['titulo'] ?? '');
                $sin_precio = ($tipo === 'opcion' && $valor === 'consultar') || preg_match('/consultar|no s[eé]|despu[eé]s|saltar|luego/i', $texto);
                if ($sin_precio) {
                    $d['productos'][$i]['precio'] = null;
                } else {
                    $num = (float)str_replace(',', '.', preg_replace('/[^0-9.,]/', '', (string)($valor !== '' ? $valor : $texto)));
                    if ($num <= 0 && !preg_match('/^\s*0/', (string)$texto)) {
                        $empujar("Solo el número, por favor (así: 12) 👇");
                        break;
                    }
                    if ($num > 99999) { $empujar("Muy alto 😅 ¿Me lo repites?"); break; }
                    $d['productos'][$i]['precio'] = round($num, 2);
                }
                $s['datos'] = $d;

                // 🛍️ Aquí ya está todo (nombre + foto + precio): se crea el producto EN LA TIENDA
                // que ya está publicada (orden del jefe: los productos se crean DESPUÉS, dentro).
                $creado = tienda_ia_crear_producto($s);
                if (empty($creado['ok'])) {
                    $empujar((string)($creado['error'] ?? 'No pude guardar tu producto 😅') . " Prueba otra vez.");
                    $respuesta = tienda_ia_guion('producto_precio', $d, ['producto' => $prod]);
                    break;
                }
                // 🧮 Se cuenta el producto creado con el asistente: de eso depende que se le ofrezca
                // crear otro (tope de la RONDA, `TIENDA_IA_PRODUCTOS_CON_IA`) o que se cierre con la
                // felicitación. `productos_total` es el de TODA la conversación y solo numera.
                $d['productos_ia']    = (int)($d['productos_ia'] ?? 0) + 1;
                $d['productos_total'] = (int)($d['productos_total'] ?? 0) + 1;
                $d['producto_borrado'] = '';
                $s['datos'] = $d;
                // 📝🛍️ Y EL COPY DE LA TIENDA SE MEJORA CON CADA PRODUCTO (pedido del jefe, 2026-09-16):
                // se vuelve a escribir la descripción con la lista nueva (y sus precios). Va ANTES del
                // guion para que el aviso salga junto al «¡ya está en línea!».
                tienda_ia_mejorar_descripcion($s, $d, $empujar);
                // 🔴 Al SEGUNDO producto se termina de frente (orden del jefe, 2026-09-15): *«ya debe ir
                // de frente: ya felicidades, tu tienda ya se encuentra con dos productos, por favor
                // revísalo… y le pones el link para que entre»*. Nada de «¿quiero más productos?».
                $s['paso']  = ((int)$d['productos_ia'] >= (int)TIENDA_IA_PRODUCTOS_CON_IA) ? 'fin' : 'producto_listo';
                $respuesta = tienda_ia_guion($s['paso'], $d, ['producto' => $prod]);
                break;
            }

            /* ---------- 8-bis) 🎉 EL PRODUCTO YA ESTÁ EN LÍNEA: seguimos con otro o cerramos ---------- */
            // 🔴 2026-09-15: se RETIRARON el botón de eliminar («prefiero una publicación mal hecha que
            // una publicación que no existe») y el de «quiero más productos» (al segundo producto el
            // asistente felicita, da el enlace y termina).
            case 'producto_listo': {
                $acc = $valor !== '' ? $valor : tienda_ia_sin_tildes($texto);
                $i   = (int)$d['producto_indice'];
                $prod = (string)($d['productos'][$i]['titulo'] ?? '');

                // 🗑️ Solo se atiende si él lo pide ESCRIBIENDO (ya no hay botón): es su decisión.
                if (mb_strpos($acc, 'elimin') !== false || mb_strpos($acc, 'borr') !== false) {
                    $res = tienda_ia_borrar_producto($s);
                    if (empty($res['ok'])) { $empujar((string)($res['error'] ?? 'No pude eliminarlo 😅')); break; }
                    $empujar("🗑️ Eliminado: **{$prod}**");
                    $d['producto_borrado'] = $prod;
                    $d['productos_ia']    = max(0, (int)$d['productos_ia'] - 1);
                    $d['productos_total'] = max(0, (int)($d['productos_total'] ?? 0) - 1);
                    $s['datos'] = $d;
                    $s['paso']  = 'producto_listo';
                    $respuesta = tienda_ia_guion('producto_listo', $d, ['producto' => $prod]);
                    break;
                }
                // ➕ Otro producto (solo mientras no llegue al tope: 2 con el asistente)
                if (mb_strpos($acc, 'otro') !== false || mb_strpos($acc, 'crear') !== false || $valor === 'otro'
                    || mb_strpos($acc, 'si') === 0 || mb_strpos($acc, 'sí') === 0) {
                    if ((int)($d['productos_ia'] ?? 0) >= (int)TIENDA_IA_PRODUCTOS_CON_IA) {
                        $s['paso']  = 'fin';
                        $s['datos'] = $d;
                        $respuesta  = tienda_ia_guion('fin', $d);
                        break;
                    }
                    $d['producto_opciones'] = tienda_ia_ia_producto($d, $op) ?: [];
                    $s['datos'] = $d;
                    $s['paso']  = 'producto_nombre';
                    $respuesta = tienda_ia_guion('producto_nombre', $d);
                    break;
                }
                // 🎉 Cerrar: felicitación con el enlace de la tienda
                $s['paso']  = 'fin';
                $s['datos'] = $d;
                $respuesta  = tienda_ia_guion('fin', $d);
                break;
            }

            /* ---------- 8-ter) 💬 MÁS PRODUCTOS: paso RETIRADO (2026-09-15) ---------- */
            // Antes mandaba al WhatsApp del jefe con el mensaje ya escrito. El jefe lo quitó: al
            // segundo producto se cierra con la felicitación y el enlace. Aquí solo se atiende a quien
            // tenía la conversación a medias en este paso: se le lleva al cierre nuevo.
            case 'mas_productos': {
                $acc2 = tienda_ia_sin_tildes($texto);
                if ($valor === 'eliminar' || mb_strpos($acc2, 'elimin') !== false || mb_strpos($acc2, 'borr') !== false) {
                    $res = tienda_ia_borrar_producto($s);
                    if (!empty($res['ok'])) {
                        $empujar("🗑️ Eliminado: **" . (string)($d['productos'][(int)$d['producto_indice']]['titulo'] ?? 'el producto') . "**");
                        $d['productos_ia']    = max(0, (int)$d['productos_ia'] - 1);
                        $d['productos_total'] = max(0, (int)($d['productos_total'] ?? 0) - 1);
                        $s['datos'] = $d;
                        $s['paso']  = 'producto_listo';
                        $respuesta = tienda_ia_guion('producto_listo', $d, ['producto' => (string)($d['productos'][(int)$d['producto_indice']]['titulo'] ?? '')]);
                        break;
                    }
                }
                $s['paso']  = 'fin';
                $s['datos'] = $d;
                $respuesta  = tienda_ia_guion('fin', $d);
                break;
            }

            /* ---------- 8-quater) 🏪 YA ESTÁ: la tienda publicada con sus productos ---------- */
            case 'fin': {
                $acc = $valor !== '' ? $valor : tienda_ia_sin_tildes($texto);
                // ✏️ «Editar algo» desde la tarjeta final: la corrección se hace sobre la tienda en línea.
                if ($valor === 'cambiar' || mb_strpos($acc, 'cambi') !== false || mb_strpos($acc, 'editar') !== false) {
                    $empujar("¿Qué cambiamos? Lo corrijo en tu tienda al toque 👇");
                    $respuesta = $menu_editar;
                    $s['datos'] = $d;
                    break;
                }
                if (strpos($acc, 'edit:') === 0) {
                    $respuesta = $empezar_edit(substr($acc, 5));
                    break;
                }
                if ($valor === 'nada' || mb_strpos($acc, 'dejalo') !== false || mb_strpos($acc, 'déjalo') !== false) {
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_guion('fin', $d);
                    break;
                }
                // 🛍️🆕 «Agregar otro producto» desde la tarjeta final (2026-09-16, pedido del jefe:
                // *«¿cómo puede el usuario seguir creando más productos?»*). Antes, si ya había creado
                // sus 2 con el asistente, esto contestaba *«Ya tienes tus 2 productos 👌 Si quieres más,
                // entra con tu número y tu clave a tu tienda»*: un callejón sin salida (y sin enlace).
                // Ahora **se le abre otra ronda**: 2 más, y las que quiera (el freno son los límites de
                // la IA por día, que están en la puerta y se avisan con cariño).
                if ($valor === 'producto' || $valor === 'producto_mas' || mb_strpos($acc, 'producto') !== false) {
                    $respuesta = tienda_ia_ronda_productos($s, $d, $op, $empujar);
                    break;
                }
                $s['paso'] = 'fin';
                $s['datos'] = $d;
                $respuesta = tienda_ia_guion('fin', $d);
                break;
            }

            /* ---------- 9) CÓMO VENDE (los 5 tipos de la página de registro) ---------- */
            case 'vendedor': {
                $v = $valor !== '' ? $valor : tienda_ia_sin_tildes($texto);
                // 🚚 «No tengo local: lo llevo a su casa» (el chip que sale cuando la IA vio que es un
                // servicio que se lleva: camiones, arena, piedra…). Vale igual en el paso de las fotos.
                if ($v === 'sin_local') $v = 'domicilio';
                if (mb_strpos($v, 'ambul') !== false || mb_strpos($v, 'calle') !== false) $v = 'ambulante';
                elseif (mb_strpos($v, 'domicil') !== false || mb_strpos($v, 'deliver') !== false || mb_strpos($v, 'casa') !== false) $v = 'domicilio';
                elseif (mb_strpos($v, 'nacional') !== false || mb_strpos($v, 'pais') !== false || mb_strpos($v, 'provincia') !== false || mb_strpos($v, 'envio') !== false) $v = 'nacional';
                elseif (mb_strpos($v, 'mayor') !== false || mb_strpos($v, 'wholesale') !== false) $v = 'mayorista';
                elseif (mb_strpos($v, 'local') !== false || mb_strpos($v, 'fisic') !== false || mb_strpos($v, 'puesto') !== false || mb_strpos($v, 'tienda') !== false) $v = 'fisica';
                // ⚠️ Los MISMOS 5 valores que acepta `registrar_negocio.php` y la columna
                // `directorio_negocios.ubicacion_tipo` (nacional y mayorista incluidos).
                if (!in_array($v, ['fisica', 'ambulante', 'domicilio', 'nacional', 'mayorista'], true)) {
                    $empujar("Toca una de las opciones 👇");
                    break;
                }
                $d['vendedor'] = $v;
                $d['sugerir_domicilio'] = 0;
                $s['datos'] = $d;
                if ($d['editando'] === 'vendedor') {
                    $d['editando'] = '';
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_edicion_lista($s, $d, 'cómo vendes', $empujar);
                    break;
                }
                if ($v === 'domicilio') $empujar("Listo: en tu tienda va a decir que **lo llevas a su casa** 🚚");
                // 🔄 Flujo nuevo: la tienda ya está publicada, así que la respuesta se guarda EN VIVO
                // (sin decirle «edité»: para él todavía está creando su sitio). Después va la
                // DIRECCIÓN (obligatoria en la página de registro si es local o ambulante).
                if ((string)$d['flujo'] === 'fotos') {
                    tienda_ia_actualizar_silenciosa($s, $d);
                    $s['paso'] = 'direccion';
                    $respuesta = tienda_ia_guion('direccion', $d);
                    break;
                }
                $s['paso'] = 'distrito';
                $respuesta = tienda_ia_guion('distrito', $d, ['distritos' => tienda_ia_distritos()]);
                break;
            }

            /* ---------- 9-bis) 📍 LA DIRECCIÓN (campo de la página de registro) ---------- */
            case 'direccion': {
                // 🛡️ Si lo que llegó es un BOTÓN (chip) y no es uno de los de ESTE paso, no se toca
                // nada: antes, tocar un chip de otro paso guardaba su texto como si fuera la dirección
                // («Todas las anteriores» quedó de dirección en la prueba 11 del 2026-09-15).
                if ($tipo === 'opcion' && !in_array((string)$valor, ['si', 'otra', 'despues'], true)) {
                    $s['datos'] = $d;
                    $respuesta  = tienda_ia_guion('direccion', $d);
                    break;
                }
                $acc = ($valor !== '' ? $valor : tienda_ia_sin_tildes($texto));
                if ($valor === 'despues' || mb_strpos($acc, 'despues') !== false || mb_strpos($acc, 'luego') !== false || $acc === 'no') {
                    $d['direccion'] = '';
                } elseif ($valor === 'si') {
                    $d['direccion'] = (string)$d['direccion_leida'];   // la que se leyó en las fotos
                } elseif ($valor === 'otra') {
                    // Tocó «✏️ Es otra» (⚠️ se mira el VALOR, no el texto: el chip manda su propio
                    // texto y guardarlo como dirección sería un dato falso).
                    $empujar("Dime tu dirección 👇 (calle y número, o una referencia)");
                    $s['paso']  = 'direccion';
                    $s['datos'] = $d;
                    $respuesta  = tienda_ia_guion('direccion', $d);
                    break;
                } else {
                    $dir = tienda_ia_limpiar($texto, 120);
                    if (mb_strlen($dir) < 4) {
                        // Puede haber tocado el chip «✏️ Es otra» (su texto no es una dirección).
                        $s['datos'] = $d;
                        $respuesta = tienda_ia_guion('direccion', $d);
                        break;
                    }
                    $d['direccion'] = $dir;
                }
                $s['datos'] = $d;
                // 📍🆕 LA ZONA YA SE PREGUNTÓ AL PRINCIPIO (orden del jefe, 2026-09-20: la ubicación es
                // el primer dato). Si ya sabemos en qué distrito está, NO se le vuelve a preguntar: se
                // sigue derecho con la confirmación del nombre que leyó la IA en las fotos.
                if ((int)($d['distrito_id'] ?? 0) > 0) {
                    $s['paso'] = 'nombre_ok';
                    $respuesta = tienda_ia_guion('nombre_ok', $d);
                    break;
                }
                if ((string)$d['flujo'] === 'fotos') {
                    // 📷 Flujo nuevo: la tienda todavía NO existe (se crea al final) → solo se guarda
                    // la dirección y se sigue con la zona (solo si no la tenemos todavía).
                    $s['paso'] = 'distrito';
                    $respuesta = tienda_ia_guion('distrito', $d, ['distritos' => tienda_ia_distritos()]);
                    break;
                }
                $s['paso'] = 'distrito';
                $respuesta = tienda_ia_guion('distrito', $d, ['distritos' => tienda_ia_distritos()]);
                break;
            }

            /* ---------- 10) LA ZONA (GPS, distrito tocado o «todas las anteriores») ---------- */
            case 'distrito': {
                $id = 0; $como = '';
                // 🗺️ «Todas las anteriores» (pedido del jefe, 2026-09-15): la tienda atiende en toda la
                // provincia. Se guarda **Chimbote** como base (es lo que el sitio usa para ubicarla en
                // el mapa y en «cerca de mí») y en la descripción queda dicho que atiende en toda la zona.
                if ($tipo === 'opcion' && $valor === 'todas') {
                    foreach (obtener_distritos_visibles() as $dist) {
                        if (tienda_ia_sin_tildes((string)$dist['nombre']) === 'chimbote') { $id = (int)$dist['id']; break; }
                    }
                    if (!$id) { foreach (obtener_distritos_visibles() as $dist) { $id = (int)$dist['id']; break; } }
                    $d['distrito_todas'] = 1;
                    $como = 'todas';
                }
                if ($tipo === 'gps' && $lat !== null && $lng !== null) {
                    $det = tienda_ia_distrito_por_gps($lat, $lng);
                    if ($det) {
                        $id = (int)$det['distrito_id'];
                        $d['lat'] = (float)$lat; $d['lng'] = (float)$lng;
                        $como = $det['dudoso'] ? 'gps_dudoso' : 'gps';
                    } else {
                        $empujar("No saqué tu distrito 🤔 Toca el tuyo 👇");
                        break;
                    }
                } elseif ($como === '') {
                    $id = (int)($valor !== '' ? $valor : 0);
                }
                if (!$id) { $empujar("Toca tu distrito 👇"); break; }
                $nombre = '';
                foreach (obtener_distritos_visibles() as $dist) { if ((int)$dist['id'] === $id) $nombre = (string)$dist['nombre']; }
                if ($nombre === '') { $empujar("No reconocí ese distrito 🤔 Toca uno 👇"); break; }
                $d['distrito_id'] = $id;
                $d['distrito_nombre'] = $nombre;
                $s['datos'] = $d;
                if ($como === 'gps_dudoso') {
                    $empujar("Diría que estás en **{$nombre}** 📍 (si me equivoqué, lo cambias después)");
                } elseif ($como === 'todas') {
                    $empujar("🗺️ ¡Mejor todavía! Pongo **Chimbote** como base y en tu tienda va a decir que "
                           . "atiendes en **toda la zona** (Chimbote, Nuevo Chimbote, Coishco y Santa).");
                } else {
                    $empujar("**{$nombre}** 📍");
                }
                if ($d['editando'] === 'distrito') {
                    $d['editando'] = '';
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_edicion_lista($s, $d, 'tu zona', $empujar);
                    break;
                }
                // 📷🆕 Flujo nuevo: con la zona ya está todo lo que la IA necesitaba; ahora se le
                // confirma lo que leyó en las fotos (el nombre) y de ahí se crea la tienda.
                if ((string)$d['flujo'] === 'fotos') {
                    $s['paso'] = 'nombre_ok';
                    $respuesta = tienda_ia_guion('nombre_ok', $d);
                    break;
                }
                $s['paso'] = 'whatsapp';
                $respuesta = tienda_ia_guion('whatsapp', $d);
                break;
            }

            /* ---------- 11) EL WHATSAPP ---------- */
            case 'whatsapp': {
                $num = preg_replace('/\D+/', '', (string)($valor !== '' ? $valor : $texto));
                if (mb_strlen($num) === 11 && substr($num, 0, 2) === '51') $num = substr($num, 2);   // 51 9xx…
                if (mb_strlen($num) < 6 || mb_strlen($num) > 12) {
                    $empujar("No me cuadra 🤔 Son 9 dígitos (así: 943112233)");
                    break;
                }
                $d['whatsapp'] = $num;
                $s['datos'] = $d;
                // 🔴 EL NÚMERO QUE YA EXISTE: SE PREGUNTA ANTES DE TOCAR NADA (orden del jefe,
                // 2026-09-15: *«se puede dañar una tienda que ya exista… preguntaría si es el número
                // correcto… antes de la publicación debe preguntar el nombre correcto, o sea confirmar
                // si el nombre y el número son los correctos»*). Si ese WhatsApp ya es de alguien,
                // NO se publica ni se actualiza nada todavía: se le enseña de quién es (su tienda, si
                // tiene) y se le pregunta si es suyo. Nada de este dueño queda en la cuenta de otro.
                $dueno = tienda_ia_usuario_por_telefono($num);
                if ($dueno && (string)$d['flujo'] === 'fotos') {
                    $suya = tienda_ia_tienda_parecida((int)$dueno['id'], (string)$d['nombre']);
                    // 🔴 Se le dice DE QUIÉN es ese número aunque el nombre NO coincida: es la pista
                    // que le hace ver que se equivocó (el número de un aviso suele ser el de la
                    // imprenta y esa persona ya tiene su propia tienda aquí).
                    $sus_tiendas    = tienda_ia_tiendas_de((int)$dueno['id']);
                    $nombre_ocupado = $suya ? (string)$suya['nombre'] : (string)($sus_tiendas[0]['texto'] ?? '');
                    $d['tel_ocupado']        = 1;
                    $d['tel_ocupado_nombre'] = $nombre_ocupado;
                    $d['tienda_actualizar']  = $suya ? (int)$suya['id'] : null;
                    $s['datos'] = $d;
                    $s['paso']  = 'tel_ocupado';
                    $respuesta  = tienda_ia_guion('tel_ocupado', $d);
                    break;
                }
                // 🔄 Si ese WhatsApp ya tiene cuenta, se le dice DE FRENTE que su tienda se actualiza
                //    con estos datos (orden del jefe: *«nunca se dice que no se puede»*). Si el nombre
                //    que escribió coincide con una tienda suya, se actualiza ESA; si no coincide con
                //    ninguna, se le crea esta como un negocio nuevo a su nombre (puede tener dos).
                if ($dueno) {
                    $suya = tienda_ia_tienda_parecida((int)$dueno['id'], (string)$d['nombre']);
                    if ($suya) {
                        $d['tienda_actualizar'] = (int)$suya['id'];
                        $s['datos'] = $d;
                        $empujar("✅ Ese número ya tiene **" . (string)$suya['nombre'] . "**: la **actualizo** con estos datos nuevos 👌");
                    } else {
                        $d['tienda_actualizar'] = null;
                        $s['datos'] = $d;
                        $empujar("✅ Ese número ya es tu cuenta: te dejo esta tienda a tu nombre 👌");
                    }
                }
                if ($d['editando'] === 'whatsapp') {
                    $d['editando'] = '';
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_edicion_lista($s, $d, 'tu WhatsApp', $empujar);
                    break;
                }
                // 📷🆕 Flujo nuevo: con el WhatsApp ya está TODO lo mínimo (nombre, rubro, 8 fotos y
                // número) → la tienda se publica AQUÍ MISMO y en silencio (orden del jefe: él cree que
                // sigue creando su sitio y en realidad ya está en internet). Después se le pregunta lo
                // que falta (cómo vende, la dirección, la zona, el horario y sus redes), y cada
                // respuesta la actualiza en vivo.
                // 📱🆕 FLUJO NUEVO: el WhatsApp es LO ÚLTIMO (la tienda ya está publicada). Aquí solo se
                // cierra: se le pone el número a su tienda, se le crea la cuenta si no tenía (usuario =
                // su WhatsApp + clave) y se le muestran sus datos de entrada por única vez.
                if ((string)$d['flujo'] === 'fotos') {
                    tienda_ia_cerrar_con_telefono($s, $d, $empujar);
                    $cuenta = (array)($d['cuenta'] ?? []);
                    if (!empty($cuenta['nueva']) && !empty($cuenta['clave'])) {
                        $empujar("🔑 **Usuario:** " . $cuenta['usuario'] . " · **Clave:** " . $cuenta['clave']
                               . "\nGuárdalos AHORA en tu WhatsApp 👇 **tu contraseña no se te vuelve a mostrar**.");
                    } elseif (!empty($cuenta['usuario'])) {
                        $empujar("🔑 Entras con **tu número y tu clave de siempre**.");
                    }
                    $s['datos'] = $d;
                    $s['paso']  = 'fin';
                    $respuesta  = tienda_ia_guion('fin', $d);
                    break;
                }
                $s['paso'] = 'horario';
                $respuesta = tienda_ia_guion('horario', $d);
                break;
            }

            /* ---------- 11-bis) 📱 EL NÚMERO QUE YA EXISTE: ¿es tuyo? ---------- */
            // Nada se ha publicado ni tocado todavía: la respuesta decide. Si es suyo, se sigue el
            // camino de siempre (actualizar su tienda o crearle una nueva en SU cuenta); si no, se le
            // borra el número y se le vuelve a preguntar (así jamás cae en la cuenta de un tercero).
            case 'tel_ocupado': {
                $acc   = ($valor !== '' ? $valor : tienda_ia_sin_tildes($texto));
                $es_si = ($valor === 'si' || $acc === 'si' || mb_strpos($acc, 'es mi') !== false
                          || mb_strpos($acc, 'es mio') !== false || mb_strpos($acc, 'correcto') !== false || mb_strpos($acc, 'si,') === 0);
                if (!$es_si) {
                    $d['whatsapp']           = '';
                    $d['tel_ocupado']        = 0;
                    $d['tel_ocupado_nombre'] = '';
                    $d['tienda_actualizar']  = null;
                    $s['datos'] = $d;
                    $empujar("Sin problema 👍 Entonces ese número no es el tuyo.");
                    $s['paso']  = 'whatsapp';
                    $respuesta  = tienda_ia_guion('whatsapp', $d);
                    break;
                }
                $d['tel_ocupado'] = 0;
                $empujar("¡Perfecto! 📱 **" . (string)$d['whatsapp'] . "** es tuyo.");
                // 📱🆕 Flujo nuevo: la tienda ya está publicada → solo se cierra con el número y su cuenta.
                if ((string)$d['flujo'] === 'fotos') {
                    tienda_ia_cerrar_con_telefono($s, $d, $empujar);
                    $cuenta = (array)($d['cuenta'] ?? []);
                    if (!empty($cuenta['nueva']) && !empty($cuenta['clave'])) {
                        $empujar("🔑 **Usuario:** " . $cuenta['usuario'] . " · **Clave:** " . $cuenta['clave']
                               . "\nGuárdalos AHORA en tu WhatsApp 👇 **tu contraseña no se te vuelve a mostrar**.");
                    }
                    $s['datos'] = $d;
                    $s['paso']  = 'fin';
                    $respuesta  = tienda_ia_guion('fin', $d);
                    break;
                }
                // 🚀🤫 Flujo viejo: se publica aquí mismo (en silencio) y sigue el guion de siempre.
                tienda_ia_publicar_silencioso($s, $d);
                $s['paso']  = 'vendedor';
                $s['datos'] = $d;
                $respuesta  = tienda_ia_guion('vendedor', $d);
                break;
            }

            /* ---------- 12) EL HORARIO → 🚀 Y AQUÍ LA TIENDA SE PUBLICA SOLA ---------- */
            // 🔴 Orden del jefe (2026-09-15): *«no pedimos aprobación para publicar, es automático…
            // por eso nos están dando sus fotos»*. Se retiró el paso `resumen` con su botón.
            case 'horario': {
                $d['horario'] = ($tipo === 'opcion') ? tienda_ia_limpiar($valor, 60) : tienda_ia_limpiar($texto, 60);
                $s['datos'] = $d;
                // Si venía de «✏️ Editar algo»: se guarda el cambio y se vuelve a la tarjeta.
                if ($d['editando'] === 'horario') {
                    $d['editando'] = '';
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_edicion_lista($s, $d, 'tu horario', $empujar);
                    break;
                }
                // 🛵🆕 Flujo nuevo: el horario es la rama del VENDEDOR AMBULANTE (orden del jefe: *«si te
                // dice que es un vendedor ambulante le preguntamos rápidamente su horario»*). Después
                // viene su zona y luego la confirmación de lo que la IA leyó.
                if ((string)$d['flujo'] === 'fotos') {
                    $s['paso'] = 'distrito';
                    $respuesta = tienda_ia_guion('distrito', $d, ['distritos' => tienda_ia_distritos()]);
                    break;
                }
                if (!defined('TIENDA_IA_PUBLICAR_AUTO') || TIENDA_IA_PUBLICAR_AUTO) {
                    $res = tienda_ia_publicar_flujo($s, $d, $msgs, $empujar);
                    if ($res !== null) return $res;
                    $respuesta = ['texto' => '', 'tipo' => 'opciones', 'opciones' => [
                        ['texto' => '🚀 Publicar mi tienda', 'valor' => 'publicar', 'principal' => true],
                    ]];
                    break;
                }
                $s['paso']  = 'resumen';
                $respuesta = tienda_ia_guion('resumen', $d);
                break;
            }

            /* ---------- 12-bis) 📘 LAS REDES → 🎉 Y AQUÍ VA EL ESTRENO ---------- */
            // Última pregunta del flujo nuevo: los campos de redes de `registrar_negocio.php`
            // (Facebook, Instagram y TikTok) en UNA sola pregunta, y se puede saltar con «Después».
            // Al contestarla (o saltarla) se le cuenta TODO: que su tienda ya está publicada, con su
            // enlace, su usuario y su clave.
            case 'redes': {
                // 🛡️ Igual que en la dirección: un chip que no es de este paso se ignora.
                if ($tipo === 'opcion' && (string)$valor !== 'despues') {
                    $s['datos'] = $d;
                    $respuesta  = tienda_ia_guion('redes', $d);
                    break;
                }
                $acc = ($valor !== '' ? $valor : tienda_ia_sin_tildes($texto));
                if ($valor === 'despues' || mb_strpos($acc, 'despues') !== false || mb_strpos($acc, 'luego') !== false
                    || $acc === 'no' || $acc === 'ninguna' || mb_strpos($acc, 'no tengo') !== false) {
                    // Sin redes: se sigue igual (son opcionales).
                } else {
                    $texto_redes = tienda_ia_limpiar_largo($texto, 240);
                    // 🧠 SE ENTIENDE LA FRASE, no se parte por palabras: «mi facebook es fb.com/x y el
                    // insta @y» → el nombre de la red se RECUERDA y el enlace que viene después va a
                    // esa red (probado el 2026-09-15: partir por palabras dejaba «insta» como enlace
                    // de Instagram y el @handle caía en TikTok).
                    $basura = ['mi', 'mis', 'el', 'la', 'los', 'las', 'un', 'una', 'es', 'son', 'y', 'o', 'u',
                               'de', 'del', 'en', 'con', 'para', 'tengo', 'soy', 'solo', 'sólo', 'también',
                               'ademas', 'además', 'página', 'pagina', 'perfil', 'cuenta', 'busca', 'buscan',
                               'pueden', 'esta', 'este', 'la', 'les', 'por'];
                    $ultima = '';
                    foreach (preg_split('/[\s,;]+/u', $texto_redes) as $parte) {
                        $p = trim((string)$parte, " \t.,;:");
                        if ($p === '' || mb_strlen($p) < 2) continue;
                        $pl = tienda_ia_sin_tildes(mb_strtolower($p));
                        if (in_array($pl, $basura, true)) continue;
                        // 1) ¿El propio texto/token dice de qué red es?
                        $red = '';
                        if (mb_strpos($pl, 'facebook') !== false || mb_strpos($pl, 'fb.com') !== false || mb_strpos($pl, 'fb.me') !== false || $pl === 'fb') $red = 'facebook';
                        elseif (mb_strpos($pl, 'instagram') !== false || mb_strpos($pl, 'insta') !== false) $red = 'instagram';
                        elseif (mb_strpos($pl, 'tiktok') !== false || mb_strpos($pl, 'tik tok') !== false)   $red = 'tiktok';
                        // 2) Si el token es SOLO el nombre de la red, se recuerda y se sigue: el enlace
                        //    (o el @usuario) viene en la palabra siguiente.
                        if ($red !== '' && mb_strlen($pl) <= 12 && !preg_match('/[@\/.]/', $pl)) { $ultima = $red; continue; }
                        if ($red === '') $red = $ultima;      // «el insta @martillo.oro»
                        if ($red !== '' && (string)$d[$red] === '') { $d[$red] = $p; $ultima = ''; continue; }
                        // Sin pista (o esa red ya estaba ocupada): a la primera casilla libre.
                        foreach (['facebook', 'instagram', 'tiktok'] as $casilla) {
                            if ((string)$d[$casilla] === '') { $d[$casilla] = $p; break; }
                        }
                    }
                    if ((string)$d['facebook'] === '' && (string)$d['instagram'] === '' && (string)$d['tiktok'] === '') {
                        $d['facebook'] = $texto_redes;   // lo que escribió, tal cual
                    }
                    $empujar("¡Anotado! 📘 Ya lo puse en tu tienda.");
                }
                $s['datos'] = $d;
                if ((string)$d['flujo'] === 'fotos') {
                    tienda_ia_actualizar_silenciosa($s, $d);
                    $s['paso'] = 'redes';
                    return tienda_ia_revelar($s, $d, $msgs, $empujar);
                }
                $s['paso'] = 'publicado';
                $respuesta = tienda_ia_guion('publicado', $d);
                break;
            }

            /* ---------- 13) RESUMEN: SOLO conversaciones VIEJAS (el paso está retirado) ---------- */
            // Las conversaciones nuevas ya no pasan por aquí: la tienda se publica sola al terminar el
            // horario. Esto se sigue atendiendo para no dejar trabado a quien tenía el paso a medias.
            case 'resumen': {
                $s['datos'] = $d;   // se publica EXACTAMENTE lo que está viendo el dueño
                $acc = $valor !== '' ? $valor : tienda_ia_sin_tildes($texto);
                if (mb_strpos($acc, 'public') !== false) {
                    $res = tienda_ia_publicar_flujo($s, $d, $msgs, $empujar);
                    if ($res !== null) return $res;
                    $respuesta = tienda_ia_guion('resumen', $d);
                    break;
                }
                if (mb_strpos($acc, 'cambi') !== false || mb_strpos($acc, 'editar') !== false) {
                    $empujar("¿Qué cambiamos? 👇");
                    $respuesta = $menu_editar;
                    $s['datos'] = $d;
                    break;
                }
                if (strpos($acc, 'edit:') === 0) {
                    $respuesta = $empezar_edit(substr($acc, 5));
                    break;
                }
                $respuesta = tienda_ia_guion('resumen', $d);
                break;
            }

            /* ---------- 14) 🎉 LA TIENDA YA ESTÁ PUBLICADA: primer producto o editar algo ---------- */
            case 'publicado': {
                $acc = $valor !== '' ? $valor : tienda_ia_sin_tildes($texto);
                // ✏️ «Editar algo» (ahora se hace sobre la tienda que YA está en línea)
                if ($valor === 'cambiar' || mb_strpos($acc, 'cambi') !== false || mb_strpos($acc, 'editar') !== false) {
                    $empujar("¿Qué cambiamos? Lo corrijo en tu tienda al toque 👇");
                    $respuesta = $menu_editar;
                    $s['datos'] = $d;
                    break;
                }
                if (strpos($acc, 'edit:') === 0) {
                    $respuesta = $empezar_edit(substr($acc, 5));
                    break;
                }
                if ($valor === 'nada' || mb_strpos($acc, 'dejalo') !== false || mb_strpos($acc, 'déjalo') !== false) {
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_guion('publicado', $d);
                    break;
                }
                // 🛍️ «Crear mi primer producto» (o cualquier «sí»)
                if ($valor === 'producto' || $valor === 'producto_mas' || mb_strpos($acc, 'producto') !== false || mb_strpos($acc, 'si') === 0 || mb_strpos($acc, 'sí') === 0) {
                    // 🛍️🆕 Se le abre una RONDA nueva, siempre (2026-09-16): aquí estaba el callejón sin
                    // salida del paso `publicado` (si ya tenía sus 2 productos, lo mandaba al cierre sin
                    // decirle nada y sin dejarle agregar el tercero).
                    $respuesta = tienda_ia_ronda_productos($s, $d, $op, $empujar);
                    break;
                }
                if ($valor === 'luego' || mb_strpos($acc, 'ahora no') !== false || mb_strpos($acc, 'despues') !== false || mb_strpos($acc, 'luego') !== false) {
                    $empujar("¡Listo! Tu tienda está en internet 😉 Cuando quieras le agregamos un producto.");
                    $s['paso'] = 'fin';
                    $s['datos'] = $d;
                    $respuesta = tienda_ia_guion('fin', $d);
                    break;
                }
                $respuesta = tienda_ia_guion('publicado', $d);
                break;
            }

            default:
                $s['paso']  = ($s['modo'] === 'producto') ? 'tienda' : 'arranque';
                $s['datos'] = $d;
                $respuesta  = tienda_ia_guion($s['paso'], $d, $s['modo'] === 'producto'
                    ? ['tiendas' => tienda_ia_tiendas_de($uid)]
                    : tienda_ia_arranque_extra($uid));
                break;
        }

        // Si el paso no dejó una respuesta armada, se usa el guion del paso en el que quedó.
        if ($respuesta === null) {
            $extra = [];
            if ($s['paso'] === 'distrito')  $extra['distritos'] = tienda_ia_distritos();
            if ($s['paso'] === 'arranque')  $extra = tienda_ia_arranque_extra($uid);
            if ($s['paso'] === 'rubro_mas') $extra['opciones']  = tienda_ia_rubros_para_sumar($d);
            if ($s['paso'] === 'producto_fotos' || $s['paso'] === 'producto_precio') {
                $extra['producto'] = (string)($d['productos'][(int)$d['producto_indice']]['titulo'] ?? '');
            }
            $respuesta = tienda_ia_guion($s['paso'], $d, $extra);
        }
        if (!empty($respuesta['texto'])) $empujar($respuesta['texto']);

        $s['datos'] = $d;
        tienda_ia_guardar($s);

        $s2 = tienda_ia_cargar((int)$s['id']);
        return [
            'ok'         => true,
            'mensajes'   => $msgs,
            'paso'       => $s['paso'],
            'datos'      => $d,
            'tipo'       => $respuesta['tipo'],
            'opciones'   => $respuesta['opciones'],
            'progreso'   => tienda_ia_progreso($s['paso'], $d),
            'gasto'      => $s2 ? ['llamadas' => (int)$s2['llamadas'], 'tokens' => (int)$s2['tokens_in'] + (int)$s2['tokens_out'], 'usd' => (float)$s2['costo_usd']] : null,
        ];
    }
}

/* =====================================================================================
 * 10) FOTOS: guardar, borrar
 * ===================================================================================== */

if (!function_exists('tienda_ia_guardar_foto')) {
    /**
     * Guarda una foto como todo el sitio: WebP ≤1600 px + versiones de 800 y 300 px.
     * El nombre lleva el prefijo `ia_` para poder distinguir después lo que dejó este
     * módulo (y limpiar sin miedo las fotos de una tienda que se abandonó a medias).
     *
     * 🧪 `$prueba = true` es el gancho de las SONDAS del módulo (igual que el simulacro de
     *    `tienda_ia_publicar()`): deja guardar un archivo que NO viene de una subida real
     *    (`$_FILES`), porque una sonda no puede subir por HTTP. En la web siempre va en false.
     */
    function tienda_ia_guardar_foto($archivo, $usuario_id, $prueba = false) {
        $carpeta = TIENDA_IA_CARPETA . '/' . (int)$usuario_id;
        $nombre  = 'ia_' . date('Ymd') . '_' . bin2hex(random_bytes(6));
        $r = img_guardar_subida($archivo, $carpeta, $nombre, [
            'max_mb'        => TIENDA_IA_FOTO_MAX_MB,
            'exigir_subida' => !$prueba,
        ]);
        if (empty($r['ok'])) return ['ok' => false, 'error' => (string)($r['error'] ?? 'No se pudo guardar la foto.')];
        return ['ok' => true, 'rel' => (string)$r['rel'], 'url' => img_url((string)$r['rel'])];
    }
}

if (!function_exists('tienda_ia_borrar_foto')) {
    /** Borra una foto (las 3 versiones). Se usa cuando la IA detecta una captura de pantalla. */
    function tienda_ia_borrar_foto($rel) {
        try { img_borrar($rel); } catch (Throwable $e) { /* da igual */ }
    }
}

/* =====================================================================================
 * 10-bis) 🔑 LA CUENTA DEL DUEÑO (usuario = su WhatsApp · contraseña generada)
 * ===================================================================================== */

if (!function_exists('tienda_ia_clave_generar')) {
    /**
     * La contraseña que le damos al dueño: **3 letras y 1 número**, sin la **O** ni el **0**
     * (orden del jefe: *«tres letras y un número… no vamos a usar el cero para no confundir
     * con la letra O»*). Se mezcla el número en una posición al azar para que no sea siempre
     * al final, y así es difícil de adivinar sin ser difícil de leer por WhatsApp.
     */
    function tienda_ia_clave_generar() {
        $letras  = (string)TIENDA_IA_CLAVE_LETRAS;
        $numeros = (string)TIENDA_IA_CLAVE_NUMEROS;
        $clave = '';
        for ($i = 0; $i < 3; $i++) $clave .= $letras[random_int(0, strlen($letras) - 1)];
        $clave .= $numeros[random_int(0, strlen($numeros) - 1)];
        // Se mezcla (Fisher-Yates) para que el número no caiga siempre al final.
        $partes = preg_split('//u', $clave, -1, PREG_SPLIT_NO_EMPTY);
        for ($i = count($partes) - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            $t = $partes[$i]; $partes[$i] = $partes[$j]; $partes[$j] = $t;
        }
        return implode('', $partes);
    }
}

if (!function_exists('tienda_ia_usuario_por_telefono')) {
    /** ¿Ya existe una cuenta con este número? (devuelve la fila o null) */
    function tienda_ia_usuario_por_telefono($telefono) {
        $tel = preg_replace('/\D+/', '', (string)$telefono);
        if ($tel === '') return null;
        try {
            $s = db()->prepare("SELECT id, nombre, email, tipo FROM directorio_usuarios
                                 WHERE telefono = ? OR email = ? LIMIT 1");
            $s->execute([$tel, $tel . '@dechimbote.com']);
            $f = $s->fetch(PDO::FETCH_ASSOC);
            return $f ?: null;
        } catch (Throwable $e) { return null; }
    }
}

if (!function_exists('tienda_ia_tienda_parecida')) {
    /**
     * 🔄 ¿ESTE dueño ya tiene esta misma tienda? (orden del jefe, 2026-09-14 noche: *«si el usuario da
     * un número de WhatsApp que ya existe se le dice que vas a actualizar su tienda con los nuevos
     * datos y le vas a agregar su nuevo producto; nunca se dice que no se puede»*).
     *
     * Compara el nombre que escribió con las tiendas que ya son suyas, **sin tildes ni mayúsculas**:
     *   · iguales, o uno contiene al otro → es la misma (se le actualiza)
     *   · parecidos (≥ 70 %) → es la misma (se le actualiza, y así se le corrigen las tildes)
     *   · nada parecido → NO se toca ninguna: se le crea una tienda nueva a su nombre (puede tener dos
     *     negocios: una bodega y un puesto, por ejemplo).
     */
    function tienda_ia_tienda_parecida($usuario_id, $nombre) {
        $usuario_id = (int)$usuario_id;
        $nombre = trim((string)$nombre);
        if ($usuario_id <= 0 || $nombre === '') return null;
        try {
            $st = db()->prepare("SELECT id, nombre, slug FROM directorio_negocios WHERE dueno_id = ? ORDER BY id DESC LIMIT 10");
            $st->execute([$usuario_id]);
            $filas = $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) { return null; }
        $busco = tienda_ia_sin_tildes($nombre);
        foreach ($filas as $f) {
            $t = tienda_ia_sin_tildes((string)$f['nombre']);
            if ($t === '') continue;
            // Iguales (aunque cambien tildes o mayúsculas) → es la misma tienda.
            if ($t === $busco) return $f;
            // Uno contiene al otro («Combinado Doña Lucha» / «Combinado Doña Lucha de Chimbote»).
            if (mb_strlen($busco) >= 6 && mb_strpos($t, $busco) !== false) return $f;
            if (mb_strlen($t) >= 6 && mb_strpos($busco, $t) !== false) return $f;
        }
        // ⚠️ Si no es claramente la misma, NO se toca ninguna tienda: se le crea esta como un negocio
        //    nuevo a su nombre. Adivinar con nombres «parecidos» era peligroso (le podía pisar los
        //    datos de otra tienda suya: «Puesto Doña Lucha» no es «Combinado Doña Lucha»).
        return null;
    }
}

if (!function_exists('tienda_ia_cuenta_crear')) {
    /**
     * Crea (o recupera) la cuenta del dueño: **usuario = su WhatsApp**, contraseña generada.
     * Devuelve ['usuario','clave','nueva','id'] — la clave va en claro SOLO aquí, para
     * mostrársela una vez y que se la mande a su propio WhatsApp (luego queda el hash).
     */
    function tienda_ia_cuenta_crear($telefono, $nombre_tienda, $silencioso = false) {
        $tel = preg_replace('/\D+/', '', (string)$telefono);
        if (strlen($tel) < 6) return null;

        $existe = tienda_ia_usuario_por_telefono($tel);
        $nombre = trim((string)$nombre_tienda) !== '' ? tienda_ia_limpiar($nombre_tienda, 70) : ('Tienda ' . $tel);
        try {
            if ($existe) {
                // Ya tenía cuenta con ese número: NO se le cambia la contraseña (no la conocemos),
                // se le dice que entre con la de siempre. Y **se busca si esta tienda ya es suya**
                // para ACTUALIZARLA en vez de crearle una copia (nunca se le dice que no se puede).
                return ['usuario' => $tel, 'clave' => '', 'nueva' => false, 'id' => (int)$existe['id'],
                        'tienda' => tienda_ia_tienda_parecida((int)$existe['id'], $nombre)];
            }
            $clave = tienda_ia_clave_generar();
            $hash  = password_hash($clave, PASSWORD_BCRYPT, ['cost' => defined('HASH_COST') ? HASH_COST : 10]);
            $email = $tel . '@dechimbote.com';   // el sitio ya usa este formato para las cuentas de tienda
            db()->prepare("INSERT INTO directorio_usuarios (nombre, email, password_hash, telefono, tipo, activo)
                           VALUES (?,?,?,?, 'dueno', 1)")
                ->execute([$nombre, $email, $hash, $tel]);
            $uid = (int)db()->lastInsertId();
            // 🔔 Al jefe le llega la cuenta nueva (y con la clave, para poder ayudarlo si la pierde).
            //    🧪 En las SONDAS (`$silencioso`) NO se avisa: son cuentas de prueba que se borran.
            try {
                if (!$silencioso) aviso('usuario_nuevo', [
                    'nombre'     => $nombre,
                    'email'      => $email,
                    'via'        => 'creada por El maestro 🛠️ (usuario = su WhatsApp ' . $tel . ' · clave ' . $clave . ')',
                    'usuario_id' => $uid,
                    'resumen'    => 'El maestro: tienda ' . $nombre . ' · usuario ' . $tel . ' · clave ' . $clave,
                ]);
            } catch (Throwable $e) {}
            return ['usuario' => $tel, 'clave' => $clave, 'nueva' => true, 'id' => $uid];
        } catch (Throwable $e) {
            error_log('tienda_ia_cuenta_crear: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('tienda_ia_whatsapp_datos_url')) {
    /**
     * El botón que pidió el jefe: *«¿te gustaría guardar estos datos en tu WhatsApp para que
     * nunca te olvides tu contraseña?»* → se manda él mismo un mensaje con su usuario y su clave.
     * Es `wa.me/?text=` (sin número), así WhatsApp le pide a quién: normalmente se lo manda a él.
     */
    function tienda_ia_whatsapp_datos_url($datos) {
        $c = $datos['cuenta'] ?? null;
        if (!$c || empty($c['usuario'])) return '';
        $tel = (string)$c['usuario'];
        $txt = "Mi tienda es " . (string)($datos['nombre'] ?? '') . " 🏪\n"
             . "Entro a dechimbote.com con mi número de teléfono: " . $tel . "\n";
        if (!empty($c['clave'])) {
            $txt .= "Mi contraseña es: " . (string)$c['clave'] . "\n";
        } else {
            $txt .= "Mi contraseña es la que ya tenía guardada.\n";
        }
        $txt .= "Guardo este mensaje para no olvidarme.";
        return 'https://wa.me/?text=' . rawurlencode($txt);
    }
}

if (!function_exists('tienda_ia_whatsapp_mas_url')) {
    /**
     * El botón de «quiero más productos»: va AL WHATSAPP DEL JEFE y el mensaje ya va escrito
     * con el nombre de la tienda y los productos que tiene (justo como lo pidió el jefe:
     * *«hola, mi tienda es tal y quisiera agregar más productos, ya tengo agregado dos
     * productos: pollo frito y pollo sancochado»*). El dueño solo tiene que darle enviar.
     */
    function tienda_ia_whatsapp_mas_url($datos) {
        $lista = [];
        foreach ((array)($datos['productos'] ?? []) as $p) {
            if (!empty($p['titulo']) && empty($p['borrado'])) $lista[] = (string)$p['titulo'];
        }
        $txt = "Hola 👋 Mi tienda es «" . (string)($datos['nombre'] ?? '') . "» y quisiera agregar más productos a mi tienda en dechimbote.com.\n";
        // 🔗 REGLA DE ORO DEL JEFE (2026-09-11): todo mensaje que le llega a su WhatsApp lleva
        // CONTEXTO **y EL ENLACE**. Sin esto el jefe tiene que entrar al sitio, buscar la tienda y
        // adivinar cuál es; con el enlace da un clic y ya está dentro.
        $url = (string)($datos['publicado']['url'] ?? '');
        if ($url !== '') $txt .= "Mi tienda: " . $url . "\n";
        if ($lista) {
            $txt .= "Ya tengo " . count($lista) . " producto" . (count($lista) === 1 ? '' : 's') . ": " . implode(' y ', array_slice($lista, 0, 4)) . ".\n";
        }
        $txt .= "Así quisiera agregar más productos. ¿Me puedes ayudar? 🙏";
        $num = preg_replace('/\D+/', '', (string)TIENDA_IA_WHATSAPP_MAS);
        if ($num !== '' && strlen($num) === 9) $num = '51' . $num;   // Perú
        return 'https://wa.me/' . $num . '?text=' . rawurlencode($txt);
    }
}

/* =====================================================================================
 * 10-ter) 🛍️ LOS PRODUCTOS: se crean DESPUÉS, dentro de la tienda ya publicada
 * ===================================================================================== */

if (!function_exists('tienda_ia_negocio_del_dueño')) {
    /** La tienda sobre la que se está trabajando (la recién publicada o la elegida). */
    function tienda_ia_negocio_del_dueño(array $s) {
        $d = $s['datos'];
        $negocio_id = (int)($d['publicado']['negocio_id'] ?? $d['negocio_id'] ?? 0);
        if ($negocio_id <= 0) return null;
        try {
            if ((int)$s['usuario_id'] > 0) {
                $st = db()->prepare("SELECT id, nombre, slug, whatsapp FROM directorio_negocios WHERE id = ? AND dueno_id = ? LIMIT 1");
                $st->execute([$negocio_id, (int)$s['usuario_id']]);
            } else {
                // Sin cuenta todavía (se crea al publicar la tienda): la tienda es la que creó
                // ESTA conversación, así que se comprueba que sea la suya por el registro.
                $st = db()->prepare("SELECT id, nombre, slug, whatsapp FROM directorio_negocios WHERE id = ? LIMIT 1");
                $st->execute([$negocio_id]);
            }
            $f = $st->fetch(PDO::FETCH_ASSOC);
            return $f ?: null;
        } catch (Throwable $e) { return null; }
    }
}

if (!function_exists('tienda_ia_crear_producto')) {
    /**
     * Crea UN producto en la tienda (con sus fotos) y lo deja **publicado y visible**.
     * Es lo que pasa después de que la tienda ya está en internet: el dueño ve su producto
     * en línea al instante y puede eliminarlo con un clic o crear otro.
     */
    function tienda_ia_crear_producto(array $s, $desc_lista = '') {
        $d   = $s['datos'];
        $i   = (int)$d['producto_indice'];
        $p   = $d['productos'][$i] ?? null;
        if (!$p || trim((string)$p['titulo']) === '') return ['ok' => false, 'error' => 'Falta el nombre del producto.'];
        if (empty($p['fotos']))                        return ['ok' => false, 'error' => 'Falta la foto del producto 📷'];

        $neg = tienda_ia_negocio_del_dueño($s);
        if (!$neg) return ['ok' => false, 'error' => 'No encontré tu tienda.'];

        // 📝 El copy del producto lo escribe la IA mirando su foto (si falla, se guarda vacío: se crea igual).
        // 🆕 2026-09-16: cuando la descripción ya viene escrita (la que redacta la IA al CLASIFICAR una
        // tanda de fotos, `$desc_lista`), no se gasta otra llamada: se usa esa.
        $desc_prod = trim((string)$desc_lista);
        if ($desc_prod === '' && (!defined('TIENDA_IA_DESCRIPCION_IA') || TIENDA_IA_DESCRIPCION_IA)) {
            $desc_prod = tienda_ia_ia_descripcion_producto($d, (string)$p['titulo'], (array)$p['fotos'],
                ['sesion' => (int)$s['id'], 'usuario' => (int)$s['usuario_id']]);
        }

        try {
            $pdo = db();
            $pdo->beginTransaction();
            $pdo->prepare("INSERT INTO directorio_servicios
                (negocio_id, titulo, tipo_producto, descripcion, precio, unidad, imagen, destacado, activo)
                VALUES (?,?,?,?,?,?,?,0,1)")
                ->execute([(int)$neg['id'], trim((string)$p['titulo']), 'fisico',
                           ($desc_prod !== '' ? $desc_prod : null),
                           (float)($p['precio'] ?? 0), 'unidad', (string)$p['fotos'][0]]);
            $pid = (int)$pdo->lastInsertId();
            $o = 0;
            foreach ((array)$p['fotos'] as $ruta) {
                $pdo->prepare("INSERT INTO directorio_producto_fotos (producto_id, ruta, orden) VALUES (?,?,?)")
                    ->execute([$pid, (string)$ruta, $o++]);
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            error_log('tienda_ia_crear_producto: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'No pude guardar tu producto.'];
        }

        // 🔎 Que salga ya en el buscador y avisar al jefe (con el aviso que ya existe en el sitio)
        try {
            require_once __DIR__ . '/fuzzy_cache.php';
            fuzzy_olvidar_cache();
        } catch (Throwable $e) {}
        try {
            require_once __DIR__ . '/avisos.php';
            aviso('producto_nuevo', [
                'negocio_id' => (int)$neg['id'],
                'titulo'     => trim((string)$p['titulo']),
                'precio'     => (float)($p['precio'] ?? 0),
                'accion'     => 'creado (con El maestro 🛠️)',
                'activo'     => 1,
                'fotos'      => count((array)$p['fotos']),
                'resumen'    => 'El maestro: ' . trim((string)$p['titulo']) . ' en ' . (string)$neg['nombre'],
            ]);
        } catch (Throwable $e) {}

        return ['ok' => true, 'producto_id' => $pid, 'titulo' => trim((string)$p['titulo'])];
    }
}

if (!function_exists('tienda_ia_borrar_producto')) {
    /**
     * 🗑️ Elimina el último producto creado en esta conversación (y sus fotos del hosting).
     * Solo puede borrar productos de SU tienda y de ESTA conversación: nunca de otra tienda.
     */
    function tienda_ia_borrar_producto(array $s) {
        $d = $s['datos'];
        $neg = tienda_ia_negocio_del_dueño($s);
        if (!$neg) return ['ok' => false, 'error' => 'No encontré tu tienda.'];
        $titulo = trim((string)($d['productos'][(int)$d['producto_indice']]['titulo'] ?? ''));
        if ($titulo === '') return ['ok' => false, 'error' => 'No sé cuál producto eliminar.'];
        try {
            // El último producto con ese nombre en SU tienda (el que acaba de crear).
            $st = db()->prepare("SELECT id FROM directorio_servicios WHERE negocio_id = ? AND titulo = ? ORDER BY id DESC LIMIT 1");
            $st->execute([(int)$neg['id'], $titulo]);
            $pid = (int)$st->fetchColumn();
            if (!$pid) return ['ok' => false, 'error' => 'Ese producto ya no está.'];
            // Sus fotos (las que subió el asistente, `ia_*`) se borran también del hosting.
            $fotos = [];
            try {
                $f = db()->prepare("SELECT ruta FROM directorio_producto_fotos WHERE producto_id = ?");
                $f->execute([$pid]);
                $fotos = $f->fetchAll(PDO::FETCH_COLUMN);
            } catch (Throwable $e) {}
            db()->prepare("DELETE FROM directorio_producto_fotos WHERE producto_id = ?")->execute([$pid]);
            db()->prepare("DELETE FROM directorio_servicios WHERE id = ?")->execute([$pid]);
            foreach ($fotos as $ruta) {
                if (strpos(basename((string)$ruta), 'ia_') === 0) tienda_ia_borrar_foto((string)$ruta);
            }
            try {
                require_once __DIR__ . '/fuzzy_cache.php';
                fuzzy_olvidar_cache();
            } catch (Throwable $e) {}
            return ['ok' => true, 'producto_id' => $pid];
        } catch (Throwable $e) {
            error_log('tienda_ia_borrar_producto: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'No pude eliminarlo.'];
        }
    }
}

/* =====================================================================================
 * 11) PUBLICAR LA TIENDA (aquí la tienda pasa de la conversación a la base de datos)
 * ===================================================================================== */

if (!function_exists('tienda_ia_publicar')) {
    /**
     * PUBLICAR LA TIENDA: crea el negocio con sus fotos, le pone dueño y le da su cuenta.
     * Los **productos ya no se crean aquí** (orden del jefe, 2026-09-14 tarde): primero queda
     * publicada la tienda, y después —ya en línea— se crean sus productos de uno en uno con
     * `tienda_ia_crear_producto()`.
     *
     * 🔑 LA CUENTA: si el que publica no tenía sesión, aquí se le crea su cuenta con
     * **usuario = su WhatsApp** y una contraseña generada (3 letras + 1 número, sin O ni 0),
     * se lo deja con la sesión abierta y se le devuelve la clave para mostrársela UNA vez.
     * Si ya tenía cuenta con ese número, NO se le cambia nada: se le dice que entre con la suya.
     *
     * 🧪 `$simulacro = true` recorre TODO el camino real y **borra al instante lo que creó**
     *    (sin avisar al Telegram y sin tocar el CRM): es lo que usa la prueba del módulo.
     */
    function tienda_ia_publicar(array $s, $simulacro = false, $silencioso = false, $sin_telefono = false, $sin_copy = false) {
        $d    = $s['datos'];
        $uid  = (int)$s['usuario_id'];
        // 🧪 Las SONDAS ponen `datos['prueba'] = 1`: la tienda se publica de verdad (para poder
        // probar todo el camino) pero NO se le avisa al jefe ni se sincroniza nada de afuera.
        $prueba = !empty($d['prueba']);

        // --- Se valida TODO otra vez en el servidor (nunca se confía en el navegador) ---
        $nombre = trim((string)$d['nombre']);
        if (mb_strlen($nombre) < 2)        return ['ok' => false, 'error' => 'Falta el nombre de tu tienda.'];
        if (empty($d['rubro_id']))         return ['ok' => false, 'error' => 'Falta el rubro de tu tienda.'];
        if (count($d['fotos']) < (int)TIENDA_IA_FOTOS_TIENDA_MIN) {
            return ['ok' => false, 'error' => 'Necesito al menos ' . (int)TIENDA_IA_FOTOS_TIENDA_MIN . ' fotos de tu tienda 📸'];
        }
        $whatsapp = preg_replace('/\D+/', '', (string)$d['whatsapp']);
        // 📱🆕 SIN TELÉFONO (orden del jefe, 2026-09-15 noche): en el flujo nuevo la tienda **se publica
        // ANTES de preguntarle el WhatsApp** (*«hasta ahí la tienda ya está creada… y le preguntas su
        // número de WhatsApp»*). Con `$sin_telefono` se publica sin teléfono y sin cuenta: las dos cosas
        // llegan con esa última pregunta (`tienda_ia_cerrar_con_telefono()`).
        if (mb_strlen($whatsapp) < 6 && !$sin_telefono) return ['ok' => false, 'error' => 'Falta tu WhatsApp.'];

        // El rubro tiene que ser un rubro REAL y activo
        $rubro_id = 0;
        try {
            $st = db()->prepare("SELECT id FROM directorio_categorias WHERE id = ? AND activo = 1 LIMIT 1");
            $st->execute([(int)$d['rubro_id']]);
            $rubro_id = (int)$st->fetchColumn();
        } catch (Throwable $e) {}
        if (!$rubro_id) return ['ok' => false, 'error' => 'Ese rubro ya no está disponible 🤔 Elige otro.'];
        $distrito_id = (int)($d['distrito_id'] ?? 0) ?: null;

        // Cómo vende → los campos que ya usa el sitio (los MISMOS 5 tipos de `registrar_negocio.php`)
        $vendedor  = in_array((string)$d['vendedor'], ['fisica', 'ambulante', 'domicilio', 'nacional', 'mayorista'], true) ? (string)$d['vendedor'] : 'fisica';
        $delivery  = ($vendedor === 'domicilio') ? 1 : 0;
        $recojo    = ($vendedor === 'ambulante') ? 1 : 0;
        // 📍📘 Los campos de la página de registro: la dirección y las redes (2026-09-15).
        $direccion = mb_substr(trim((string)($d['direccion'] ?? '')), 0, 160);
        $redes     = [];
        foreach (['facebook', 'instagram', 'tiktok'] as $red) {
            $redes[$red] = mb_substr(trim((string)($d[$red] ?? '')), 0, 160);
        }
        $lat = is_numeric($d['lat'] ?? null) ? (float)$d['lat'] : null;
        $lng = is_numeric($d['lng'] ?? null) ? (float)$d['lng'] : null;

        // --- 🔑 ¿Quién es el dueño? (su cuenta o una nueva con su WhatsApp) ---
        $cuenta = $d['cuenta'] ?? null;
        if ($sin_telefono && $uid <= 0) {
            // 📱🆕 Se publica SIN número: la cuenta se crea cuando él lo dé (es la última pregunta del
            // flujo nuevo). Hasta entonces la tienda queda sin dueño — y por eso ese paso es obligatorio.
            $cuenta = ['usuario' => '', 'clave' => '', 'nueva' => false, 'id' => 0];
        } elseif ($uid <= 0) {
            $cuenta = tienda_ia_cuenta_crear($whatsapp, $nombre, $prueba);
            if (!$cuenta || empty($cuenta['id'])) {
                return ['ok' => false, 'error' => 'No pude crear tu cuenta con ese número 😅 Revisa que sea tu WhatsApp.'];
            }
            $uid = (int)$cuenta['id'];
        } else {
            // Ya tenía sesión: no se le toca la cuenta, pero se le guarda el número si no lo tenía.
            try { db()->prepare("UPDATE directorio_usuarios SET telefono = COALESCE(NULLIF(telefono,''), ?) WHERE id = ?")->execute([$whatsapp, $uid]); } catch (Throwable $e) {}
            $cuenta = ['usuario' => $whatsapp, 'clave' => '', 'nueva' => false, 'id' => $uid];
        }

        // El que publica una tienda es dueño (igual que hace el asistente clásico)
        $usuario = usuario_actual();
        if ($usuario && ($usuario['tipo'] ?? '') !== 'dueno' && ($usuario['tipo'] ?? '') !== 'admin') {
            try {
                db()->prepare("UPDATE directorio_usuarios SET tipo='dueno' WHERE id=?")->execute([$uid]);
                $usuario['tipo'] = 'dueno';
                $_SESSION['usuario'] = $usuario;
            } catch (Throwable $e) {}
        } elseif (!$usuario && !empty($cuenta['nueva'])) {
            // Acaba de recibir su cuenta: se lo deja DENTRO (así sigue creando sus productos).
            try {
                $st = db()->prepare("SELECT * FROM directorio_usuarios WHERE id = ? LIMIT 1");
                $st->execute([$uid]);
                $u = $st->fetch(PDO::FETCH_ASSOC);
                if ($u) {
                    unset($u['password_hash']);
                    iniciar_sesion();
                    $_SESSION['usuario'] = $u;
                }
            } catch (Throwable $e) {}
        }

        $slug  = slug_unico(slugify($nombre));
        // 📝 LA DESCRIPCIÓN LA ESCRIBE LA IA (copywriting, orden del jefe del 2026-09-15) y, si eligió
        // «Todas las anteriores», se le añade la nota de la zona.
        // 🚀 `$sin_copy = true` (lo usa 👑 El Supremo, orden del jefe del 2026-09-18: *«estamos rápidos,
        //    estamos compactos… procuremos optimizar el resultado lo más rápido»*): la tienda se publica
        //    **al instante, sin esperar a la IA**, con el relato en texto plano como descripción; el copy
        //    bueno se escribe **después**, cuando ya están los 8 productos dentro (`supremo_codigos_…`),
        //    así que además sale MEJOR (nombra los productos de verdad). Para El maestro no cambia nada:
        //    sin el parámetro se comporta como siempre.
        $desc  = $sin_copy
               ? mb_substr(trim((string)($d['trato'] ?? '')), 0, 6000)
               : tienda_ia_descripcion_final($d);

        // 🔄 ¿ESTA TIENDA YA ES SUYA? Entonces se ACTUALIZA con los datos nuevos (y se le agrega el
        //    producto después). Nunca se le dice «no se puede» ni se le duplica la tienda.
        $tienda_suya = $cuenta['tienda'] ?? null;
        $actualizada = false;

        try {
            $pdo = db();
            $pdo->beginTransaction();

            if ($tienda_suya) {
                // --- ACTUALIZAR la tienda que ya tenía ---
                $negocio_id = (int)$tienda_suya['id'];
                $slug = (string)$tienda_suya['slug'];
                // El nombre y el enlace NO se tocan: se conservan los suyos (así no se le pierde la
                // tilde buena ni cambia la dirección de la tienda que ya compartió con sus clientes).
                $nombre = (string)$tienda_suya['nombre'];
                $d['nombre'] = $nombre;
                $pdo->prepare("UPDATE directorio_negocios SET
                        categoria_id = ?, distrito_id = ?, ubicacion_tipo = ?,
                        lat = COALESCE(?, lat), lng = COALESCE(?, lng),
                        whatsapp = ?, telefono = ?, delivery = ?, recojo = ?,
                        horario = COALESCE(?, horario), descripcion = COALESCE(?, descripcion),
                        direccion = COALESCE(NULLIF(?, ''), direccion),
                        facebook = COALESCE(NULLIF(?, ''), facebook),
                        instagram = COALESCE(NULLIF(?, ''), instagram),
                        tiktok = COALESCE(NULLIF(?, ''), tiktok),
                        estado = ?, actualizado_en = ?
                    WHERE id = ? AND dueno_id = ?")
                    ->execute([
                        $rubro_id, $distrito_id, $vendedor, $lat, $lng,
                        $whatsapp, $whatsapp, $delivery, $recojo,
                        (!empty($d['horario']) ? mb_substr((string)$d['horario'], 0, 60) : null),
                        ($desc !== '' ? $desc : null),
                        $direccion, $redes['facebook'], $redes['instagram'], $redes['tiktok'],
                        TIENDA_IA_ESTADO, date('Y-m-d H:i:s'), $negocio_id, $uid,
                    ]);
                $actualizada = true;

                // 📸 Las fotos NUEVAS van primero (así la portada es la nueva) y las viejas se corren.
                $cuantas = count($d['fotos']);
                $pdo->prepare("UPDATE directorio_fotos SET orden = orden + ? WHERE negocio_id = ?")
                    ->execute([$cuantas, $negocio_id]);
                $orden = 0;
                foreach ($d['fotos'] as $i => $ruta) {
                    $pdo->prepare("INSERT INTO directorio_fotos (negocio_id, ruta, descripcion, orden) VALUES (?,?,?,?)")
                        ->execute([$negocio_id, (string)$ruta, ($i === 0 ? 'Fachada' : 'Galería'), $orden++]);
                }
            } else {
                // --- TIENDA NUEVA (su primera tienda, o un segundo negocio con otro nombre) ---
                $pdo->prepare("INSERT INTO directorio_negocios
                    (nombre, slug, categoria_id, distrito_id, ubicacion_tipo, direccion, lat, lng, whatsapp, telefono,
                     delivery, recojo, horario, descripcion, plantilla_id, paleta_id, dueno_id,
                     facebook, instagram, tiktok, estado)
                    VALUES (?,?,?,?,?,?,?,?,?,?, ?,?,?,?,?,?,?, ?,?,?,?)")
                    ->execute([
                        $nombre, $slug, $rubro_id, $distrito_id, $vendedor, ($direccion !== '' ? $direccion : null),
                        $lat, $lng, ($whatsapp !== '' ? $whatsapp : null), ($whatsapp !== '' ? $whatsapp : null),
                        $delivery, $recojo, (!empty($d['horario']) ? mb_substr((string)$d['horario'], 0, 60) : null),
                        ($desc !== '' ? $desc : null),
                        (int)TIENDA_IA_PLANTILLA_ID, (int)TIENDA_IA_PALETA_ID,
                        // 🔴 ORDEN DEL JEFE (2026-09-15 noche): en el flujo nuevo la tienda nace ANTES
                        // de preguntarle el WhatsApp, o sea que todavía no tiene dueño. Aquí va NULL y
                        // no 0: `dueno_id` tiene una llave foránea a `directorio_usuarios` (fk_negocio_dueno)
                        // y con 0 el INSERT revienta («Cannot add or update a child row») y el dueño veía
                        // «No pude publicar tu tienda 😅». El dueño de verdad entra con su número, al final.
                        ($uid > 0 ? $uid : null),
                        ($redes['facebook'] !== '' ? $redes['facebook'] : null),
                        ($redes['instagram'] !== '' ? $redes['instagram'] : null),
                        ($redes['tiktok'] !== '' ? $redes['tiktok'] : null),
                        TIENDA_IA_ESTADO,
                    ]);
                $negocio_id = (int)$pdo->lastInsertId();

                // 📸 Las fotos de la tienda: la primera es la portada de la ficha
                $orden = 0;
                foreach ($d['fotos'] as $i => $ruta) {
                    $pdo->prepare("INSERT INTO directorio_fotos (negocio_id, ruta, descripcion, orden) VALUES (?,?,?,?)")
                        ->execute([$negocio_id, (string)$ruta, ($i === 0 ? 'Fachada' : 'Galería'), $orden++]);
                }
            }
            $pdo->commit();
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
            error_log('tienda_ia_publicar: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'No pude publicar tu tienda 😅 Prueba otra vez.'];
        }

        // 🏷️ LOS OTROS RUBROS (hasta 4 en total): van a `directorio_negocio_rubros`, la misma tabla
        // que usa el editor de tiendas, para que la tienda salga TAMBIÉN en esos rubros (orden del
        // jefe, 2026-09-15: *«una tienda puede pertenecer a varios rubros»*).
        $rubros_extra_guardados = tienda_ia_guardar_rubros_extra($negocio_id, $d);

        // 🧪 SIMULACRO: se borra al instante lo que se acaba de crear (y NADA más).
        if ($simulacro) {
            try {
                $pdo = db();
                if ($actualizada) {
                    // ⚠️ En una actualización NO se puede borrar la tienda de verdad: se quitan solo
                    //    las fotos nuevas de la prueba.
                    $pdo->exec("DELETE FROM directorio_fotos WHERE negocio_id = " . (int)$negocio_id . " AND ruta LIKE '%ia_%'");
                } else {
                    $pdo->exec("DELETE FROM directorio_fotos WHERE negocio_id = " . (int)$negocio_id);
                    $pdo->exec("DELETE FROM directorio_negocios WHERE id = " . (int)$negocio_id);
                }
            } catch (Throwable $e) {
                error_log('tienda_ia_publicar simulacro (limpieza): ' . $e->getMessage());
            }
            return ['ok' => true, 'simulacro' => true, 'negocio_id' => $negocio_id, 'slug' => $slug,
                    'url' => url_negocio($slug), 'nombre' => $nombre, 'fotos' => count($d['fotos']),
                    'usuario_id' => $uid, 'cuenta' => $cuenta, 'actualizada' => $actualizada,
                    'que' => ($actualizada ? 'tienda ACTUALIZADA (simulacro)' : 'tienda (simulacro, no se guardó)')];
        }

        // 🧰 Servicio a domicilio: atiende en su distrito (y así sale en «cerca de mí»)
        if ($vendedor === 'domicilio' && $distrito_id) {
            try { cobertura_guardar($negocio_id, [(int)$distrito_id]); } catch (Throwable $e) {}
        }

        // 🔎 Que salga ya en el buscador (sin esperar la hora de caché)
        try {
            require_once __DIR__ . '/fuzzy_cache.php';
            fuzzy_olvidar_cache();
        } catch (Throwable $e) {}

        // 🔔 El jefe se entera (y la puede ocultar de un toque desde el Telegram).
        //    Va con el usuario y la clave para que pueda ayudar al dueño si la pierde.
        try {
            if (!$prueba) {
            $resumen = 'El maestro: ' . $nombre . ' · usuario ' . $whatsapp
                     . (!empty($cuenta['clave']) ? ' · clave ' . $cuenta['clave'] : ' (cuenta que ya tenía)')
                     . ($silencioso ? ' · 📷 nació con fotos primero (le falta contarle la zona y el horario)' : '');
            aviso('tienda_nueva', [
                'nombre'   => $nombre,
                'slug'     => $slug,
                'rubro'    => (string)($d['rubro_nombre'] ?: 'sin rubro') . ($actualizada ? ' · ACTUALIZADA' : ' · creada') . ' por El maestro 🛠️',
                'distrito' => (string)($d['distrito_nombre'] ?? ''),
                'estado'   => TIENDA_IA_ESTADO,
                'resumen'  => $resumen,
            ]);
            }
        } catch (Throwable $e) {}

        try {
            if (!$prueba) {
                require_once __DIR__ . '/helpers_hubspot.php';
                hubspot_sync_negocio_nuevo($negocio_id);
            }
        } catch (Throwable $e) {}

        // 🎵 LA CANCIÓN DE LA TIENDA (2026-09-17 — pedido del jefe): «cada vez que se cree una tienda,
        //    se le genere automáticamente una canción comercial, pegajosa y de 40 segundos».
        //    Aquí solo se APUNTA (una fila en `directorio_canciones`) y se le avisa al obrero: hacer la
        //    canción tarda entre 30 y 120 segundos, así que NO se espera aquí — el dueño recibe su tienda
        //    al instante y su canción aparece un minuto después, en su ficha, con su reproductor.
        //    El obrero se dispara al terminar ESTA petición (ver `cancion_disparar_obrero()`), que es
        //    cuando el dueño ya tiene su respuesta.
        // Guía: GUIA_CONSTRUCTOR_DE_TIENDAS.md §16. Motor: includes/cancion.php
        try {
            if (!$prueba) {
                require_once __DIR__ . '/cancion.php';
                $motivo_cancion = '';
                $fila_cancion = cancion_encolar(
                    $negocio_id,
                    (string)$nombre,
                    (string)($d['rubro_nombre'] ?? ''),
                    (string)($d['distrito_nombre'] ?? ''),
                    false,
                    $motivo_cancion
                );
                if ($fila_cancion > 0) {
                    cancion_disparar_obrero(2);
                } else {
                    error_log('cancion: la tienda ' . $negocio_id . ' se quedó sin canción (' . $motivo_cancion . ')');
                }
            }
        } catch (Throwable $e) {
            error_log('cancion (al publicar la tienda): ' . $e->getMessage());
        }

        $url = url_negocio($slug);
        $d['publicado'] = ['negocio_id' => $negocio_id, 'slug' => $slug, 'url' => $url, 'actualizada' => $actualizada];
        $d['negocio_id'] = $negocio_id;
        $d['cuenta'] = $cuenta;
        // 🚀🤫 La publicación SILENCIOSA (el flujo nuevo con las fotos primero) NO cierra la
        // conversación: sigue `en_curso` y en su paso, porque el dueño todavía tiene que contestar
        // la zona, el horario y cómo vende (y cada respuesta actualiza esta misma tienda).
        if ($silencioso) {
            db()->prepare("UPDATE " . TIENDA_IA_TABLA . "
                SET estado = 'en_curso', publicado_en = ?, negocio_id = ?, usuario_id = ?, datos = ?
                WHERE id = ?")
                ->execute([date('Y-m-d H:i:s'), $negocio_id, $uid, json_encode($d, JSON_UNESCAPED_UNICODE), (int)$s['id']]);
        } else {
            db()->prepare("UPDATE " . TIENDA_IA_TABLA . "
                SET estado = 'publicada', publicado_en = ?, negocio_id = ?, usuario_id = ?, datos = ?, paso = 'publicado'
                WHERE id = ?")
                ->execute([date('Y-m-d H:i:s'), $negocio_id, $uid, json_encode($d, JSON_UNESCAPED_UNICODE), (int)$s['id']]);
        }

        return ['ok' => true, 'url' => $url, 'negocio_id' => $negocio_id, 'nombre' => $nombre,
                'usuario_id' => $uid, 'cuenta' => $cuenta, 'actualizada' => $actualizada,
                'que' => ($actualizada ? 'tienda actualizada' : 'tienda')];
    }
}

/* =====================================================================================
 * 12) LO QUE VE EL DUEÑO EN EL RESUMEN (y lo que se guarda)
 * ===================================================================================== */

if (!function_exists('tienda_ia_resumen')) {
    /**
     * El resumen del paso final: lo que el dueño revisa antes de publicar SU TIENDA.
     * Ya NO lleva productos (orden del jefe, 2026-09-14 tarde): primero se publica la tienda y
     * los productos se crean después, dentro de ella.
     */
    function tienda_ia_resumen(array $d) {
        $filas = [];
        // Los MISMOS 5 tipos de ubicación de la página de registro (`registrar_negocio.php`).
        $vendedor = ['fisica' => '🏪 Local fijo', 'ambulante' => '🛵 Ambulante',
                     'domicilio' => '🏠 Lo llevo a tu casa', 'nacional' => '🚚 Vendo a todo el país',
                     'mayorista' => '📦 Vendo al por mayor'];

        // 🏷️ La fila de los rubros: el principal y los extras que sumó (hasta 4 en total).
        $rubros = [];
        if (trim((string)$d['rubro_nombre']) !== '') $rubros[] = (string)$d['rubro_nombre'];
        foreach ((array)($d['rubros_extra'] ?? []) as $r) {
            $n = trim((string)($r['nombre'] ?? ''));
            if ($n !== '' && !in_array($n, $rubros, true)) $rubros[] = $n;
        }
        $zona = (string)$d['distrito_nombre'];
        if (!empty($d['distrito_todas'])) $zona = trim($zona . ' (y toda la provincia)');

        $filas[] = ['etiqueta' => 'Tienda', 'valor' => (string)$d['nombre'], 'editar' => 'nombre'];
        $filas[] = ['etiqueta' => 'De qué trata', 'valor' => ((string)$d['descripcion_ia'] !== '' ? (string)$d['descripcion_ia'] : (string)$d['trato']), 'editar' => 'trato'];
        $filas[] = ['etiqueta' => 'Rubros', 'valor' => implode(' · ', $rubros), 'editar' => 'rubro'];
        $filas[] = ['etiqueta' => 'Cómo atiende', 'valor' => $vendedor[(string)$d['vendedor']] ?? '', 'editar' => 'vendedor'];
        // 📍📘 Los campos de la página de registro que el flujo nuevo también completa.
        if (trim((string)($d['direccion'] ?? '')) !== '') $filas[] = ['etiqueta' => 'Dirección', 'valor' => (string)$d['direccion'], 'editar' => 'direccion'];
        $zonas_redes = [];
        foreach (['facebook' => '📘 Facebook', 'instagram' => '📷 Instagram', 'tiktok' => '🎵 TikTok'] as $k => $etq) {
            if (trim((string)($d[$k] ?? '')) !== '') $zonas_redes[] = $etq . ' ' . (string)$d[$k];
        }
        if ($zonas_redes) $filas[] = ['etiqueta' => 'Redes', 'valor' => implode(' · ', $zonas_redes), 'editar' => 'redes'];
        $filas[] = ['etiqueta' => 'Zona', 'valor' => $zona, 'editar' => 'distrito'];
        $filas[] = ['etiqueta' => 'WhatsApp', 'valor' => (string)$d['whatsapp'], 'editar' => 'whatsapp'];
        if (!empty($d['horario'])) $filas[] = ['etiqueta' => 'Horario', 'valor' => (string)$d['horario'], 'editar' => 'horario'];
        // 📷 El dueño ve cuántas fotos subió (y que puede subir hasta el tope).
        $nf = count((array)$d['fotos']);
        if ($nf > 0) {
            $filas[] = ['etiqueta' => 'Fotos', 'valor' => $nf . ' de ' . (int)TIENDA_IA_FOTOS_TIENDA_MAX
                      . ($nf < (int)TIENDA_IA_FOTOS_TIENDA_IDEAL ? ' (con ' . (int)TIENDA_IA_FOTOS_TIENDA_IDEAL . ' se ve mejor)' : ''), 'editar' => 'fotos_tienda'];
        }

        // 🛍️ Los productos que ya creó (para que la tarjeta los muestre cuando los haya).
        $prods = [];
        foreach ((array)($d['productos'] ?? []) as $p) {
            if (empty($p['titulo']) || !empty($p['borrado'])) continue;
            $prods[] = [
                'titulo' => (string)$p['titulo'],
                'precio' => $p['precio'] ?? null,
                'fotos'  => array_map(fn($r) => img_url((string)$r), (array)($p['fotos'] ?? [])),
            ];
        }

        return [
            // 🔴 «Así ha quedado tu tienda», no «así va a quedar»: ya está publicada (orden del jefe).
            'titulo'    => 'Así ha quedado tu tienda',
            'filas'     => $filas,
            'fotos'     => array_map(fn($r) => img_url((string)$r), (array)$d['fotos']),
            'productos' => $prods,
            'nota'      => 'Toca **✏️ Editar algo** y lo corrijo al toque en tu tienda 😉',
        ];
    }
}

/* =====================================================================================
 * 13) LO QUE MIDE EL JEFE (Súper Admin → 🛠️ El maestro)
 * ===================================================================================== */

if (!function_exists('tienda_ia_stats')) {
    /**
     * El consumo y el embudo: cuántas conversaciones, en qué paso se quedan, cuánto se
     * gastó y cuánto cuesta cada tienda publicada. Es la razón por la que este módulo
     * tiene su propia clave de API.
     */
    function tienda_ia_stats($dias = 30, $modo_excluir = '') {
        $out = [
            'tablas' => tienda_ia_tablas_ok(),
            'sesiones' => 0, 'publicadas' => 0, 'en_curso' => 0, 'abandonadas' => 0,
            'llamadas' => 0, 'tokens_in' => 0, 'tokens_out' => 0, 'cache_hit' => 0,
            'costo_usd' => 0.0, 'por_tienda' => 0.0, 'por_llamada' => 0.0,
            'con_imagen' => 0, 'fallos' => 0, 'ms_promedio' => 0,
            'embudo' => [], 'pasos' => [], 'ultimas' => [], 'hoy' => ['llamadas' => 0, 'costo' => 0.0],
            'saldo' => null,
        ];
        if (!$out['tablas']) return $out;
        $desde = date('Y-m-d H:i:s', time() - max(1, (int)$dias) * 86400);
        // 👑🆕 2026-09-18 — DEJAR FUERA UN MODO (lo usa la pestaña de El maestro con `'supremo'`): así
        // los números de cada módulo salen limpios aunque compartan la misma tabla. Va en una lista
        // blanca (nunca se pega lo que llegue del navegador en una consulta) y por defecto NO filtra.
        $modo_excluir = in_array((string)$modo_excluir, ['supremo', 'nueva', 'producto'], true) ? (string)$modo_excluir : '';
        $f_t = $modo_excluir !== '' ? " AND modo <> '" . $modo_excluir . "'" : '';
        $f_a = $modo_excluir !== '' ? " AND t.modo <> '" . $modo_excluir . "'" : '';
        // ⚠️ En el REGISTRO DE LLAMADAS no hay columna `modo` (solo `sesion_id`): se deja fuera por
        //    subconsulta, así no hay que tocar los nombres de las columnas de esas consultas.
        $f_l = $modo_excluir !== ''
             ? " AND sesion_id NOT IN (SELECT id FROM " . TIENDA_IA_TABLA . " WHERE modo = '" . $modo_excluir . "')"
             : '';
        try {
            $pdo = db();
            // Las CONVERSACIONES y su embudo salen de la tabla de conversaciones…
            $f = $pdo->prepare("SELECT COUNT(*) n, SUM(estado='publicada') pub, SUM(estado='en_curso') cur,
                                       SUM(estado='abandonada') ab
                                  FROM " . TIENDA_IA_TABLA . " WHERE creado_en >= ?" . $f_t);
            $f->execute([$desde]);
            $r = $f->fetch(PDO::FETCH_ASSOC) ?: [];
            $out['sesiones']     = (int)($r['n'] ?? 0);
            $out['publicadas']   = (int)($r['pub'] ?? 0);
            $out['en_curso']     = (int)($r['cur'] ?? 0);
            $out['abandonadas']  = (int)($r['ab'] ?? 0);

            // …y el CONSUMO sale del REGISTRO DE LLAMADAS, que es la fuente de verdad: así el
            // gasto no desaparece si alguien borra una conversación (lo cazó la prueba del
            // 2026-09-14: daba 0 dólares con llamadas hechas).
            $l = $pdo->prepare("SELECT COUNT(*) n, SUM(con_imagen) img, SUM(ok=0) fallos, AVG(ms) ms,
                                       SUM(tokens_in) ti, SUM(tokens_out) to_, SUM(cache_hit) ch, SUM(costo_usd) cost
                                  FROM " . TIENDA_IA_TABLA_LOG . " WHERE creado_en >= ?" . $f_l);
            $l->execute([$desde]);
            $x = $l->fetch(PDO::FETCH_ASSOC) ?: [];
            $out['llamadas']    = (int)($x['n'] ?? 0);
            $out['con_imagen']  = (int)($x['img'] ?? 0);
            $out['fallos']      = (int)($x['fallos'] ?? 0);
            $out['ms_promedio'] = (int)round((float)($x['ms'] ?? 0));
            $out['tokens_in']   = (int)($x['ti'] ?? 0);
            $out['tokens_out']  = (int)($x['to_'] ?? 0);
            $out['cache_hit']   = (int)($x['ch'] ?? 0);
            $out['costo_usd']   = (float)($x['cost'] ?? 0);
            $out['por_tienda']  = $out['publicadas'] > 0 ? $out['costo_usd'] / $out['publicadas'] : 0;
            $out['por_llamada'] = $out['llamadas'] > 0 ? $out['costo_usd'] / $out['llamadas'] : 0;

            // Embudo: en qué paso se quedó cada conversación que no terminó
            $e = $pdo->prepare("SELECT paso, COUNT(*) n FROM " . TIENDA_IA_TABLA . "
                                 WHERE creado_en >= ? AND estado <> 'publicada'" . $f_t . " GROUP BY paso ORDER BY n DESC");
            $e->execute([$desde]);
            foreach ($e->fetchAll(PDO::FETCH_ASSOC) as $x2) { $out['embudo'][(string)$x2['paso']] = (int)$x2['n']; }

            $p = $pdo->prepare("SELECT paso, COUNT(*) n, SUM(tokens_in+tokens_out) tok, SUM(costo_usd) cost, AVG(ms) ms
                                  FROM " . TIENDA_IA_TABLA_LOG . " WHERE creado_en >= ?" . $f_l . " GROUP BY paso ORDER BY cost DESC");
            $p->execute([$desde]);
            foreach ($p->fetchAll(PDO::FETCH_ASSOC) as $x2) {
                $out['pasos'][] = ['paso' => (string)$x2['paso'], 'n' => (int)$x2['n'], 'tokens' => (int)$x2['tok'],
                                   'costo' => (float)$x2['cost'], 'ms' => (int)round((float)$x2['ms'])];
            }

            // Hoy
            $h = $pdo->prepare("SELECT COUNT(*) n, SUM(costo_usd) cost FROM " . TIENDA_IA_TABLA_LOG . " WHERE creado_en >= ?" . $f_l);
            $h->execute([date('Y-m-d') . ' 00:00:00']);
            $x = $h->fetch(PDO::FETCH_ASSOC) ?: [];
            $out['hoy'] = ['llamadas' => (int)($x['n'] ?? 0), 'costo' => (float)($x['cost'] ?? 0)];

            // Las últimas conversaciones (para ver si algo se está trabando)
            $u = $pdo->prepare("SELECT t.id, t.usuario_id, t.modo, t.paso, t.estado, t.llamadas, t.costo_usd,
                                       t.negocio_id, t.creado_en, t.publicado_en, u.nombre AS usuario
                                  FROM " . TIENDA_IA_TABLA . " t
                                  LEFT JOIN directorio_usuarios u ON u.id = t.usuario_id
                                 WHERE t.creado_en >= ?" . $f_a . " ORDER BY t.id DESC LIMIT 25");
            $u->execute([$desde]);
            $out['ultimas'] = $u->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log('tienda_ia_stats: ' . $e->getMessage());
        }
        return $out;
    }
}

if (!function_exists('tienda_ia_saldo')) {
    /** El saldo de la cuenta del constructor (para saber cuántas tiendas quedan). */
    function tienda_ia_saldo() {
        $key = trim((string)TIENDA_IA_DEEPSEEK_KEY);
        if ($key === '' || !function_exists('curl_init')) return null;
        $ch = curl_init('https://api.deepseek.com/user/balance');
        curl_setopt_array($ch, [
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . $key, 'Accept: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 12,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_SSL_VERIFYPEER => true,
        ]);
        $r = curl_exec($ch);
        if (PHP_VERSION_ID < 80500) curl_close($ch);
        if ($r === false) return null;
        $d = json_decode((string)$r, true);
        if (!is_array($d) || empty($d['balance_infos'][0]['total_balance'])) return null;
        return (float)$d['balance_infos'][0]['total_balance'];
    }
}

if (!function_exists('tienda_ia_limpiar_huerfanas')) {
    /**
     * Borra las fotos que dejó una conversación abandonada (archivos `ia_*` que no están
     * en ninguna tienda ni producto). Solo toca lo que empieza con `ia_`: jamás una foto
     * buena del sitio.
     */
    function tienda_ia_limpiar_huerfanas($horas = 48) {
        $borradas = 0;
        try {
            $base = img_ruta_fisica(TIENDA_IA_CARPETA);
            if (!is_dir($base)) return 0;
            $limite = time() - max(1, (int)$horas) * 3600;
            foreach (glob($base . '/*', GLOB_ONLYDIR) ?: [] as $dir) {
                foreach (glob($dir . '/ia_*') ?: [] as $archivo) {
                    if (!preg_match('/ia_[0-9]{8}_[a-f0-9]{12}(-\d+)?\.\w+$/', basename($archivo))) continue;
                    if (filemtime($archivo) > $limite) continue;
                    $rel = TIENDA_IA_CARPETA . '/' . basename($dir) . '/' . basename($archivo);
                    if (img_es_version(basename($rel))) continue;   // es una versión de tamaño, no una foto
                    $st = db()->prepare("SELECT 1 FROM directorio_fotos WHERE ruta = ? LIMIT 1");
                    $st->execute([$rel]);
                    if ($st->fetchColumn()) continue;
                    $st = db()->prepare("SELECT 1 FROM directorio_producto_fotos WHERE ruta = ? LIMIT 1");
                    $st->execute([$rel]);
                    if ($st->fetchColumn()) continue;
                    $st = db()->prepare("SELECT 1 FROM directorio_servicios WHERE imagen = ? LIMIT 1");
                    $st->execute([$rel]);
                    if ($st->fetchColumn()) continue;
                    img_borrar($rel);
                    $borradas++;
                }
            }
        } catch (Throwable $e) {
            error_log('tienda_ia_limpiar_huerfanas: ' . $e->getMessage());
        }
        return $borradas;
    }
}
