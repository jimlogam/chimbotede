<?php
/**
 * includes/chatbot_ajustes.php — LOS AJUSTES DE «NINJA» EDITABLES DESDE EL SÚPER ADMIN
 * ==================================================================================
 * Pedido del jefe (2026-09-13): *«pon un panel de configuración enlazado a mi panel de Superadmin»*.
 *
 * Cómo funciona:
 *   1. Los valores viven en la tabla **`directorio_chatbot_ajustes`** (clave → valor), que se crea
 *      sola (auto-instalación defensiva, como `empleos_instalar_tabla`) cuando un admin entra a la
 *      sección del panel.
 *   2. **Este archivo se carga ANTES que `config_chatbot.php`**: primero define las constantes con
 *      lo que el jefe haya guardado y después `config_chatbot.php` rellena lo que falte con sus
 *      valores por defecto (todos sus `define` están protegidos con `if (!defined(...))`).
 *      ⚠️ Por eso TODOS los archivos del chat incluyen **este** y no `config_chatbot.php` a secas.
 *   3. Si la tabla no existe o la BD falla, todo sigue funcionando con los valores por defecto.
 *
 * Guía: GUIA_CHATBOT_DEEPSEEK.md §2septies. Panel: Súper Admin → 🥷 Ninja (chat).
 *
 * Orden que NO se puede cambiar: (1) leer la tabla, (2) definir las constantes, (3) recién ahí
 * cargar `config_chatbot.php` para que ponga los valores por defecto de lo que falte.
 */

if (!function_exists('chatbot_ajustes_tabla')) {
    function chatbot_ajustes_tabla() { return 'directorio_chatbot_ajustes'; }
}

if (!function_exists('chatbot_ajustes_defecto')) {
    /**
     * EL CATÁLOGO: es la única fuente de verdad del panel y de los valores por defecto.
     * Cada fila: constante que se define · etiqueta · tipo · ayuda · opciones.
     *   tipos: 'onoff' | 'texto' | 'numero' | 'clave'
     */
    function chatbot_ajustes_defecto() {
        return [
            'activo' => [
                'const' => 'CHATBOT_ACTIVO', 'label' => '🥷 El chat está encendido', 'tipo' => 'onoff',
                'defecto' => '1', 'ayuda' => 'Si lo apagas, el botón desaparece de todo el sitio (no se borra nada).',
            ],
            'titulo' => [
                'const' => 'CHATBOT_TITULO', 'label' => 'Título de la ventana', 'tipo' => 'texto',
                'defecto' => 'El guía', 'ayuda' => 'Lo que se lee arriba en la cabecera del chat (corto, sin adornos).',
            ],
            'nombre' => [
                'const' => 'CHATBOT_NOMBRE', 'label' => 'Nombre del bot', 'tipo' => 'texto',
                'defecto' => 'El ninja', 'ayuda' => 'Cómo se llama a sí mismo al hablar (el título de arriba puede ser más corto).',
            ],
            'emoji' => [
                'const' => 'CHATBOT_EMOJI', 'label' => 'Emoji del bot', 'tipo' => 'texto',
                'defecto' => '🥷', 'ayuda' => 'Sale en el botón flotante, en el avatar y en sus mensajes.',
            ],
            'cuota_visitante' => [
                'const' => 'CHATBOT_PREGUNTAS_VISITANTE', 'label' => 'Preguntas al día: visitante sin cuenta', 'tipo' => 'numero',
                'defecto' => '20', 'ayuda' => 'Cada pregunta contestada gasta una. Al agotarlas se le invita a registrarse. (El número NUNCA se le dice.)',
            ],
            'cuota_registrado' => [
                'const' => 'CHATBOT_PREGUNTAS_REGISTRADO', 'label' => 'Preguntas al día: usuario registrado', 'tipo' => 'numero',
                'defecto' => '50', 'ayuda' => 'Al agotarlas se le invita a Premium. (Tampoco se le dice el número.)',
            ],
            'cuota_premium' => [
                'const' => 'CHATBOT_PREGUNTAS_PREMIUM', 'label' => 'Preguntas al día: Premium ⭐', 'tipo' => 'numero',
                'defecto' => '300', 'ayuda' => '0 = SIN LÍMITE. Recomendado 300 (en la práctica nadie llega).',
            ],
            'cierre_seg' => [
                'const' => 'CHATBOT_CIERRE_SEG', 'label' => 'Cierre automático de la ventana (segundos)', 'tipo' => 'numero',
                'defecto' => '15', 'ayuda' => 'Cuánto espera antes de cerrar la ventana cuando ya se le acabaron las preguntas.',
            ],
            // ⏳ EL «PENSANDO…» (orden del jefe, 2026-09-14): frases FIJAS que el navegador va rotando
            // mientras espera la respuesta. No se le piden al modelo: no gastan tokens (son programación).
            'pensando_frases' => [
                'const' => 'CHATBOT_PENSANDO_FRASES', 'label' => '⏳ Frases del «pensando» (separadas por ·)',
                'tipo' => 'texto', 'defecto' => 'Pensando · Consultando · Respondiendo',
                'ayuda' => 'Lo que se lee mientras el bot trabaja (texto pequeño). Se rotan en orden y se queda en la última. Máximo 4 frases.',
            ],
            'pensando_ms' => [
                'const' => 'CHATBOT_PENSANDO_MS', 'label' => '⏳ Lo que dura cada frase (milisegundos)', 'tipo' => 'numero',
                'defecto' => '700', 'ayuda' => 'Cada cuánto cambia de frase. 700 = «Pensando» (0,7 s) → «Consultando» (1,4 s) → «Respondiendo».',
            ],
            'espera_min_ms' => [
                'const' => 'CHATBOT_ESPERA_MIN_MS', 'label' => '⏳ Espera mínima antes de responder (milisegundos)', 'tipo' => 'numero',
                'defecto' => '1500', 'ayuda' => 'Piso: la respuesta no se pinta antes de este tiempo, así el «pensando» se ve y el buscador gana su segundo. 0 = sin piso (responde en cuanto llega).',
            ],
            'precio_premium' => [
                'const' => 'CHATBOT_PREMIUM_PRECIO', 'label' => 'Precio de Premium', 'tipo' => 'texto',
                'defecto' => 'S/ 96 al mes', 'ayuda' => 'Como se escribe en el chat y en sus respuestas. Es el plan Premium del sitio (página /nosotros#precios).',
            ],
            'imagenes' => [
                'const' => 'CHATBOT_IMAGENES_ACTIVO', 'label' => '👁️ Puede mirar fotos', 'tipo' => 'onoff',
                'defecto' => '1', 'ayuda' => 'Botón 📷: el visitante manda una foto o comparte su pantalla y el bot la mira. No se guardan.',
            ],
            'img_dia' => [
                'const' => 'CHATBOT_IMG_DIA', 'label' => 'Fotos por persona y día', 'tipo' => 'numero',
                'defecto' => '20', 'ayuda' => 'Tope diario de imágenes por persona (cada una cuesta ~0,01 céntimo).',
            ],
            'limite_global_dia' => [
                'const' => 'CHATBOT_LIMITE_GLOBAL_DIA', 'label' => 'Tope de mensajes al día (todo el sitio)', 'tipo' => 'numero',
                'defecto' => '600', 'ayuda' => 'Freno de mano por si alguien quiere vaciar el saldo de DeepSeek.',
            ],
            'modelo' => [
                'const' => 'CHATBOT_MODELO', 'label' => 'Modelo de texto', 'tipo' => 'texto',
                'defecto' => 'deepseek-chat', 'ayuda' => 'Déjalo así: se resuelve en `deepseek-flash` (el barato).',
            ],
            'log_dias' => [
                'const' => 'CHATBOT_LOG_DIAS', 'label' => 'Días que se guardan las preguntas', 'tipo' => 'numero',
                'defecto' => '90', 'ayuda' => 'El registro de lo que pregunta la gente (sirve para mejorar el bot).',
            ],
            'clave' => [
                'const' => 'CHATBOT_DEEPSEEK_KEY', 'label' => '🔑 Clave de DeepSeek', 'tipo' => 'clave',
                'defecto' => '', 'ayuda' => 'Escríbela solo si quieres cambiarla. Se guarda en el servidor y NUNCA se muestra completa.',
            ],
        ];
    }
}

if (!function_exists('chatbot_ajustes_instalar')) {
    /**
     * Crea la tabla de ajustes. Solo un admin (o el instalador) puede crearla, igual que el resto
     * de auto-instalaciones del proyecto. Devuelve true si la tabla está lista.
     */
    function chatbot_ajustes_instalar() {
        static $ok = null;
        if ($ok !== null) return $ok;
        $tabla = chatbot_ajustes_tabla();
        try {
            db()->query('SELECT 1 FROM ' . $tabla . ' LIMIT 1');
            return $ok = true;
        } catch (Throwable $e) {
            $ok = false;
        }
        if (!function_exists('es_admin') || !es_admin()) return $ok;
        try {
            db()->exec("CREATE TABLE IF NOT EXISTS " . $tabla . " (
                clave      VARCHAR(40)  NOT NULL,
                valor      TEXT         NULL,
                actualizado_en DATETIME NULL,
                PRIMARY KEY (clave)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
            return $ok = true;
        } catch (Throwable $ex) {
            error_log('chatbot_ajustes_instalar: ' . $ex->getMessage());
            return $ok = false;
        }
    }
}

if (!function_exists('chatbot_ajustes_leer')) {
    /** Lo guardado en la tabla (clave => valor). Se lee UNA vez por petición. */
    function chatbot_ajustes_leer($forzar = false) {
        static $cache = null;
        if ($cache !== null && !$forzar) return $cache;
        $cache = [];
        if (!function_exists('db')) return $cache;
        try {
            $st = db()->query('SELECT clave, valor FROM ' . chatbot_ajustes_tabla());
            foreach ($st as $f) $cache[(string)$f['clave']] = (string)$f['valor'];
        } catch (Throwable $e) {
            $cache = [];   // todavía no hay tabla: se usan los valores por defecto
        }
        return $cache;
    }
}

if (!function_exists('chatbot_ajustes_aplicar')) {
    /**
     * Define las constantes del chat con lo que el jefe guardó en el panel.
     * ⚠️ Se llama ANTES de que `config_chatbot.php` ponga sus valores por defecto.
     */
    function chatbot_ajustes_aplicar() {
        $guardado = chatbot_ajustes_leer();
        if (!$guardado) return;
        foreach (chatbot_ajustes_defecto() as $clave => $info) {
            if (!array_key_exists($clave, $guardado)) continue;
            $valor = (string)$guardado[$clave];
            if ($info['tipo'] === 'onoff') {
                $valor = ($valor === '1' || $valor === 'true' || $valor === 'on') ? '1' : '0';
                if (defined($info['const'])) continue;
                define($info['const'], $valor === '1');
                continue;
            }
            if ($info['tipo'] === 'numero') {
                if (!is_numeric($valor)) continue;
                $valor = (string)max(0, (int)$valor);
                if (defined($info['const'])) continue;
                define($info['const'], (int)$valor);
                continue;
            }
            // texto y clave: se recortan para que no entren cosas raras
            $valor = trim($valor);
            if ($valor === '') continue;
            if ($info['tipo'] === 'texto') $valor = mb_substr($valor, 0, 60);
            if (defined($info['const'])) continue;
            define($info['const'], $valor);
        }
    }
}

// 👇 1.º se aplica lo que el jefe guardó en el panel y 2.º se cargan los valores por defecto.
//    `config_chatbot.php` tiene TODOS sus define con `if (!defined(...))`, así que lo guardado manda.
chatbot_ajustes_aplicar();
require_once __DIR__ . '/config_chatbot.php';

if (!function_exists('chatbot_ajustes_valor')) {
    /** El valor que se está usando ahora mismo (BD si hay, si no el de defecto). */
    function chatbot_ajustes_valor($clave) {
        $cat = chatbot_ajustes_defecto();
        if (!isset($cat[$clave])) return '';
        if (defined($cat[$clave]['const'])) {
            $v = constant($cat[$clave]['const']);
            return is_bool($v) ? ($v ? '1' : '0') : (string)$v;
        }
        return (string)$cat[$clave]['defecto'];
    }
}

if (!function_exists('chatbot_ajustes_guardar')) {
    /**
     * Guarda los ajustes que llegan del panel. Devuelve cuántos se guardaron.
     * Solo toca las claves del catálogo y solo con valores válidos (nunca se confía en el POST).
     */
    function chatbot_ajustes_guardar(array $datos) {
        if (!chatbot_ajustes_instalar()) return 0;
        $cat = chatbot_ajustes_defecto();
        $guardados = 0;
        $pdo = db();
        $st = $pdo->prepare("INSERT INTO " . chatbot_ajustes_tabla() . " (clave, valor, actualizado_en)
                             VALUES (?, ?, NOW())
                             ON DUPLICATE KEY UPDATE valor = VALUES(valor), actualizado_en = NOW()");
        foreach ($cat as $clave => $info) {
            if (!array_key_exists($clave, $datos)) {
                // Las casillas de encendido/apagado que NO vienen en el POST están desmarcadas.
                if ($info['tipo'] !== 'onoff') continue;
                $valor = '0';
            } else {
                $valor = (string)$datos[$clave];
            }
            if ($info['tipo'] === 'onoff') {
                $valor = ($valor === '1' || $valor === 'on') ? '1' : '0';
            } elseif ($info['tipo'] === 'numero') {
                $valor = (string)max(0, (int)$valor);
                if ($clave === 'cierre_seg') $valor = (string)max(3, min(120, (int)$valor));
                if ($clave === 'img_dia')    $valor = (string)max(0, min(200, (int)$valor));
                if ($clave === 'cuota_visitante' || $clave === 'cuota_registrado') $valor = (string)max(1, min(1000, (int)$valor));
                if ($clave === 'cuota_premium') $valor = (string)max(0, min(5000, (int)$valor));   // 0 = sin límite
                // ⏳ El «pensando…»: el piso de espera no pasa de 5 s (nadie espera más) ni la frase de 3 s.
                if ($clave === 'espera_min_ms') $valor = (string)max(0, min(5000, (int)$valor));
                if ($clave === 'pensando_ms')   $valor = (string)max(200, min(3000, (int)$valor));
            } elseif ($info['tipo'] === 'clave') {
                $valor = trim($valor);
                // 🔑 La clave SOLO se guarda si escribieron una nueva (nunca se borra por dejar el campo vacío).
                if ($valor === '' || mb_strlen($valor) < 12) continue;
                $valor = mb_substr($valor, 0, 120);
            } else {
                $valor = mb_substr(trim($valor), 0, 60);
                if ($valor === '') continue;   // no se guarda vacío: se queda el valor por defecto
            }
            $st->execute([$clave, $valor]);
            $guardados++;
        }
        return $guardados;
    }
}
