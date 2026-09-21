<?php
/**
 * includes/chatbot_buscar.php — 🔎 EL BUSCADOR VIVO DEL CHAT (orden del jefe, 2026-09-13)
 * ======================================================================================
 * Pedido del jefe, textual: *«le pregunté por una pollería en Chimbote y pensé que iba a ser una
 * búsqueda y darme los resultados… si alguien me pregunta una pollería en Chimbote interpreto la
 * pregunta y procedo a hacer la búsqueda en mi base de datos y relaciono el término "pollería" con
 * el rubro pollería. Por ejemplo si me pregunta "cargadores para celular" muestro de frente los
 * cargadores para celular, si es posible con foto, y las fotos llevan a las tiendas. El chat es una
 * especie de guía inteligente para encontrar lo que estás buscando.»*
 *
 * Así funciona:
 *   1. `chatbot_busqueda_termino()` LEE la pregunta y saca el término («¿dónde hay una pollería en
 *      Chimbote?» → «polleria»). Si la pregunta no es una búsqueda (es un «cómo hago…»), devuelve null
 *      y el chat sigue su camino normal.
 *   2. `chatbot_buscar()` BUSCA EN LA BASE de verdad: productos (`directorio_servicios`), tiendas
 *      (`directorio_negocios`, por nombre, rubro principal o rubros extra) y el rubro que coincide.
 *   3. `chatbot_busqueda_texto()` arma una respuesta CORTA (4 o 5 resultados y nada más), con la foto
 *      del producto o de la tienda **enlazada a la ficha de la tienda** (orden del jefe: «las fotos
 *      llevan a las tiendas») y el enlace al buscador para ver todo.
 *
 * ⚠️ Todo local: no gasta tokens de DeepSeek, contesta al instante y los datos son los reales del
 *    sitio (nada inventado). Si no encuentra nada, devuelve vacío y el chat lo resuelve con la IA.
 * ⚠️ Los términos se buscan sin tildes: el MySQL del sitio usa una colación que no distingue
 *    acentos (utf8mb4 …_ci), así que «polleria» encuentra «Pollería».
 *
 * ======================================================================================
 * 🆕 2026-09-13 (noche) — EL FALLO DE «CLAVOS», ARREGLADO (reclamo del jefe)
 * ======================================================================================
 * El jefe preguntó *«puedes buscar quién vende clavos acá en Chimbote»* y el chat le contestó que no
 * había resultados. El diagnóstico contra la base real dio TRES fallos, y aquí están los tres
 * arreglados:
 *
 *   A) **La palabra basura anulaba la búsqueda.** «acá» no estaba en la lista de ruido, así que el
 *      término salía **«clavo aca»**; como la consulta exige que aparezcan TODAS las palabras, el
 *      resultado era **0**. Ahora «aca», «alla» y los rellenos de lugar están en el ruido **y**,
 *      si un término de varias palabras no encuentra nada, se reintenta palabra por palabra (una
 *      palabra estorbosa ya no puede dejar la respuesta en cero).
 *   B) **Las «frases de unión» del sitio no se leían.** La tabla `directorio_categoria_claves`
 *      (1 057 claves en los 121 rubros, la misma que usan El caminante y las noticias) ya tenía
 *      **«clavos» → Ferreterías**, pero el chat solo miraba el NOMBRE de la tienda, el nombre del
 *      rubro y el título del producto, así que «clavos» nunca podía llegar a las 56 ferreterías.
 *      Ahora `chatbot_busqueda_rubro()` lee esa tabla primero (`chatbot_busqueda_claves()`).
 *   C) **El rubro encontrado no se usaba como respuesta.** «alguien que parche llantas» encontraba
 *      el rubro «Reparación de llantas» y contestaba 0 igual. Ahora, cuando el rubro se conoce, sus
 *      tiendas se suman a la respuesta y sus productos son los que se muestran (así «clavos» ya no
 *      devuelve una **chicha morada con clavo de olor**, que era el único «acierto» que daba antes).
 *
 * 🔑 Regla que queda para el futuro: **las palabras que la gente escribe son las «frases de unión»
 *    del rubro** (`directorio_categoria_claves`). Si un rubro no encuentra algo que vende, casi
 *    siempre es porque a ese rubro le falta la frase, NO porque haya que escribir código nuevo.
 */

if (!function_exists('chatbot_busqueda_intencion')) {
    /** Las palabras/frases que delatan que el visitante quiere BUSCAR algo (no preguntar cómo se hace). */
    function chatbot_busqueda_intencion() {
        return [
            'busco', 'buscame', 'estoy buscando', 'quiero buscar', 'quiero comprar', 'quiero pedir',
            'necesito', 'me recomiendas', 'recomiendame', 'me puedes recomendar', 'me interesa',
            'donde hay', 'donde puedo encontrar', 'donde encuentro', 'donde compro', 'donde comprar',
            'donde venden', 'donde queda', 'quien vende', 'quien tiene', 'quienes venden',
            'hay algun', 'hay alguna', 'hay algunos', 'hay alguna', 'tienen', 'venden', 'vende',
            'precio de', 'precios de', 'cuanto cuesta', 'a cuanto esta', 'opciones de', 'opciones',
            'sabes de', 'conoces', 'dame', 'muestrame', 'ensename', 'ver ', 'buscar ',
        ];
    }
}

if (!function_exists('chatbot_busqueda_ruido')) {
    /**
     * Palabras que NO son parte del término buscado (se quitan para buscar lo importante).
     *
     * ⚠️⚠️ OJO CON LAS TILDES: el término se limpia de tildes ANTES de mirar esta lista, así que las
     * palabras con tilde van escritas SIN ELLA («acá» → `aca`). Si se escriben con tilde, NO se
     * quitan y se cuelan al término. Ese fue el fallo del 2026-09-13: «quién vende clavos **acá** en
     * Chimbote» buscaba «clavo aca» y, como la consulta exige todas las palabras, daba 0 resultados.
     * Al añadir palabras nuevas: SIEMPRE sin tildes, y si es un lugar/relleno, añadirlo también
     * suelto (no solo dentro de la frase).
     */
    function chatbot_busqueda_ruido() {
        return [
            // Sitio y lugar (el sitio ya es de Chimbote: no hace falta buscarlo)
            'chimbote', 'chimbotano', 'nuevo chimbote', 'ancash', 'peru', 'aqui', 'ahi', 'cerca',
            'cerca de mi', 'por aqui', 'en la zona', 'en el distrito',
            // ⚠️ ESTOS FALTABAN (el fallo de «clavos»): «acá», «allá» y los rellenos de lugar.
            //    OJO: «local» NO va aquí aunque suene a relleno: «busco un local» es una búsqueda
            //    de verdad (alquilar un local) y está en el mapa de sinónimos de 'fiesta'.
            'aca', 'alla', 'por aca', 'por alla', 'de aca', 'de alla', 'de aqui', 'hacia aca',
            'por estos lados', 'en estos lados', 'a la vuelta', 'ciudad', 'zona', 'distrito',
            'cercanos', 'cercanas', 'cerca mio', 'alrededor',
            // Personas/relleno de personas («alguien que parche llantas»)
            'alguien', 'alguno', 'alguna persona', 'cualquiera', 'gente',
            // Relleno
            'por favor', 'porfavor', 'porfa', 'gracias', 'hola', 'oye', 'disculpa', 'buenas',
            'un', 'una', 'unos', 'unas', 'el', 'la', 'los', 'las', 'lo', 'de', 'del', 'al', 'a',
            'en', 'con', 'por', 'para', 'que', 'y', 'o', 'u', 'mi', 'me', 'te', 'se', 'tu',
            'es', 'son', 'esta', 'estan', 'seria', 'buen', 'buena', 'mejor', 'algun', 'alguna',
            'algunos', 'algunas', 'otro', 'otra', 'mas',
        ];
    }
}

if (!function_exists('chatbot_busqueda_termino')) {
    /**
     * 🔎 Saca el término que hay que buscar en la pregunta. Devuelve '' si la pregunta no es una
     * búsqueda (entonces el chat contesta como siempre).
     * Ejemplos: «¿dónde hay una pollería en Chimbote?» → «polleria» ·
     *           «cargadores para celular» → «cargador celular» ·
     *           «cómo publico mi tienda» → '' (eso no es buscar, es un cómo se hace).
     */
    function chatbot_busqueda_termino($mensaje) {
        $crudo = chatbot_sin_tildes(mb_strtolower(trim((string)$mensaje), 'UTF-8'));
        if ($crudo === '') return '';

        // Sin signos: dejamos solo letras, números y espacios.
        $t = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $crudo);
        $t = trim(preg_replace('/\s+/u', ' ', (string)$t));

        // ¿Hay intención de buscar?
        $intencion = false;
        foreach (chatbot_busqueda_intencion() as $pista) {
            if (mb_strpos($t, $pista) !== false) { $intencion = true; break; }
        }

        // ⛔ Preguntas que NO son búsquedas (tienen su propio camino en el chat): «cómo publico mi
        // tienda», «dame 10 consejos», «qué he visto»… Se miran SIEMPRE, aunque traigan verbo de buscar
        // («dame», «busco»). Se comparan por PALABRA COMPLETA para no confundir «cómoda» con «cómo».
        $prohibidas = ['como', 'donde esta', 'quien es', 'cuantas', 'cuantos', 'cuando', 'por que',
                       'publico', 'publicar', 'publica', 'registro', 'registrar', 'borro', 'borrar',
                       'cambio', 'cambiar', 'pongo', 'poner', 'agrego', 'agregar', 'subo', 'subir',
                       'edito', 'editar', 'mi tienda', 'mi negocio', 'mis productos', 'panel',
                       'contrasena', 'premium', 'funciona', 'sirve',
                       'consejo', 'consejos', 'marketing', 'vender mas', 'mas clientes', 'publicidad',
                       'visto', 'vistos', 'historial', 'recuerdas', 'recuerdame', 'he hecho', 'he buscado',
                       'que has', 'quien eres', 'que sabes', 'que puedes', 'mi cuenta'];
        foreach ($prohibidas as $x) {
            if (preg_match('/\b' . preg_quote($x, '/') . '\b/u', $t)) return '';
        }

        // Sin intención explícita solo se busca si parece un NOMBRE DE COSA (1 a 5 palabras).
        $palabras = preg_split('/\s+/u', $t, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (!$intencion) {
            $verbos = ['hay', 'tiene', 'tienen', 'vende', 'venden', 'comprar', 'compro', 'quiero',
                       'necesito', 'busca', 'busco', 'recomienda', 'muestra'];
            foreach ($verbos as $v) {
                if (in_array($v, $palabras, true)) { $intencion = true; break; }
            }
            if (!$intencion && (count($palabras) < 1 || count($palabras) > 5)) return '';
            // Un saludo o una pregunta de charla no es una búsqueda.
            if (!$intencion && preg_match('/^(hola|buenas|hey|que tal|gracias|como estas|quien eres|que haces)\b/u', $t)) return '';
        }

        // Se quitan las frases de intención y el relleno (y TAMBIÉN las palabras sueltas de esas
        // frases: «quiero una ferretería» → «ferreteria», si no, «quiero» se buscaría como texto).
        $quitar = array_merge(chatbot_busqueda_intencion(), chatbot_busqueda_ruido());
        $limpio = ' ' . $t . ' ';
        foreach ($quitar as $q) {
            $limpio = str_replace(' ' . $q . ' ', ' ', $limpio);
        }
        $limpio = trim(preg_replace('/\s+/u', ' ', $limpio));

        $stop = $quitar;
        foreach ($quitar as $q) {
            foreach (explode(' ', $q) as $w) { $stop[] = $w; }
        }
        $stop = array_values(array_unique($stop));

        // Palabras sueltas de relleno que quedaron (por ejemplo «dime pollerias»).
        $palabras = [];
        foreach (preg_split('/\s+/u', $limpio, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $p) {
            if (mb_strlen($p) < 3) continue;
            if (in_array($p, $stop, true)) continue;
            // Raíz: «cargadores» → «cargador», «pollerias» → «polleria» (así encuentra el singular)
            if (mb_strlen($p) > 5 && mb_substr($p, -2) === 'es') $p = mb_substr($p, 0, -2);
            elseif (mb_strlen($p) > 4 && mb_substr($p, -1) === 's') $p = mb_substr($p, 0, -1);
            $palabras[] = $p;
        }
        $palabras = array_slice(array_values(array_unique($palabras)), 0, 3);

        return implode(' ', $palabras);
    }
}

if (!function_exists('chatbot_busqueda_sinonimos')) {
    /**
     * 🧠 LO QUE HACE «INTELIGENTE» AL BUSCADOR (orden del jefe: *«saber interpretar palabras»*):
     * el visitante escribe como habla («pollería», «botica», «ceviche», «grifo») y aquí se traduce a
     * las palabras con las que están escritas las fichas del sitio. Es un mapa corto y a mano: se
     * amplía cuando aparezca una búsqueda nueva.
     *
     * ⚠️ Esto es SOLO para el nombre de las fichas. La traducción «producto → rubro» NO se escribe
     * aquí: vive en la base (tabla `directorio_categoria_claves`, las «frases de unión» del rubro) y
     * la lee `chatbot_busqueda_rubro()`. No duplicar datos aquí.
     */
    function chatbot_busqueda_sinonimos() {
        return [
            'polleria'    => ['pollo', 'brasa', 'pollería'],
            'pollo'       => ['pollo', 'brasa', 'polleria'],
            'ceviche'     => ['cebicheria', 'cebichería', 'cevicheria', 'pescado', 'marisco'],
            'cebicheria'  => ['cebicheria', 'cebichería', 'ceviche', 'pescado', 'marisco'],
            'marisco'     => ['marisco', 'pescado', 'cebicheria', 'cevicheria'],
            'pescado'     => ['pescado', 'pescaderia', 'pescadería', 'marisco'],
            'farmacia'    => ['farmacia', 'botica'],
            'botica'      => ['botica', 'farmacia'],
            'bodega'      => ['bodega', 'minimarket', 'minimercado'],
            'minimarket'  => ['minimarket', 'bodega', 'minimercado'],
            'mercado'     => ['mercado', 'puesto'],
            'ferreteria'  => ['ferreteria', 'ferretería', 'fierro', 'herramienta'],
            'zapateria'   => ['zapateria', 'zapatería', 'calzado', 'zapatilla'],
            'zapatilla'   => ['zapatilla', 'calzado', 'zapateria'],
            'ropa'        => ['ropa', 'prenda', 'boutique', 'moda'],
            'panaderia'   => ['panaderia', 'panadería', 'pan', 'pasteleria', 'pastelería'],
            'pasteleria'  => ['pasteleria', 'pastelería', 'torta', 'pastel', 'panaderia'],
            'torta'       => ['torta', 'pastel', 'pasteleria'],
            'restaurante' => ['restaurante', 'menu', 'menú', 'comida'],
            'menu'        => ['menu', 'menú', 'restaurante', 'comida'],
            'chifa'       => ['chifa', 'china', 'arroz'],
            'gimnasio'    => ['gimnasio', 'gym'],
            'veterinaria' => ['veterinaria', 'mascota', 'veterinario'],
            'hotel'       => ['hotel', 'hostal', 'hospedaje'],
            'hostal'      => ['hostal', 'hotel', 'hospedaje'],
            'celular'     => ['celular', 'telefono', 'teléfono', 'smartphone', 'movistar', 'claro', 'entel'],
            'telefono'    => ['telefono', 'teléfono', 'celular', 'smartphone'],
            'cargador'    => ['cargador', 'cable', 'usb', 'carga'],
            'computadora' => ['computadora', 'laptop', 'pc', 'informatica', 'informática'],
            'laptop'      => ['laptop', 'computadora', 'pc'],
            'impresora'   => ['impresora', 'imprenta', 'toner', 'tinta'],
            'moto'        => ['moto', 'motocicleta', 'mototaxi', 'motolineal'],
            'mototaxi'    => ['mototaxi', 'moto', 'taxi'],
            'taxi'        => ['taxi', 'transporte', 'auto'],
            'auto'        => ['auto', 'vehiculo', 'vehículo', 'carro'],
            'grifo'       => ['grifo', 'combustible', 'gasolina', 'gas'],
            'gas'         => ['gas', 'balon', 'balón', 'grifo'],
            'lavanderia'  => ['lavanderia', 'lavandería', 'lavado'],
            'peluqueria'  => ['peluqueria', 'peluquería', 'salon', 'salón', 'belleza', 'corte'],
            'barberia'    => ['barberia', 'barbería', 'corte', 'barbero'],
            'spa'         => ['spa', 'masaje', 'belleza'],
            'joyeria'     => ['joyeria', 'joyería', 'joya', 'oro', 'plata', 'reloj'],
            'reloj'       => ['reloj', 'relojeria', 'joyeria'],
            'optica'      => ['optica', 'óptica', 'lentes', 'anteojos'],
            'libreria'    => ['libreria', 'librería', 'papeleria', 'papelería', 'utiles', 'útiles'],
            'juguete'     => ['juguete', 'jugueteria', 'juguetería'],
            'mueble'      => ['mueble', 'carpinteria', 'carpintería', 'madera'],
            'carpinteria' => ['carpinteria', 'carpintería', 'mueble', 'madera'],
            'pintura'     => ['pintura', 'pintor', 'color'],
            'electricista' => ['electricista', 'electricidad', 'electrico', 'eléctrico'],
            'gasfitero'   => ['gasfitero', 'gasfiteria', 'desague', 'desagüe', 'tuberia'],
            'colegio'     => ['colegio', 'academia', 'instituto', 'educacion', 'educación'],
            'academia'    => ['academia', 'colegio', 'instituto', 'clases'],
            'hospedaje'   => ['hospedaje', 'hotel', 'hostal'],
            'pollos'      => ['pollo', 'brasa', 'polleria'],
            'heladeria'   => ['heladeria', 'heladería', 'helado'],
            'helado'      => ['helado', 'heladeria'],
            'cafe'        => ['cafe', 'café', 'cafeteria', 'cafetería'],
            'postre'      => ['postre', 'pastel', 'torta', 'helado'],
            'floreria'    => ['floreria', 'florería', 'flor', 'flores'],
            'flores'      => ['flores', 'flor', 'floreria'],
            'regalo'      => ['regalo', 'detalle', 'floreria'],
            'abarrotes'   => ['abarrote', 'bodega', 'minimarket'],
            'licor'       => ['licor', 'licorería', 'licoreria', 'cerveza', 'tragos'],
            'fiesta'      => ['fiesta', 'evento', 'local', 'alquiler'],
            'alquiler'    => ['alquiler', 'alquila', 'renta'],
            'construccion' => ['construccion', 'construcción', 'materiales', 'cemento', 'ladrillo'],
            'cemento'     => ['cemento', 'materiales', 'construccion'],
            'seguridad'   => ['seguridad', 'camara', 'cámara', 'vigilancia', 'candado'],
            'contabilidad'=> ['contabilidad', 'contador', 'contable'],
            'abogado'     => ['abogado', 'legal', 'juridico', 'jurídico'],
            'dentista'    => ['dentista', 'dental', 'odontologia', 'odontología'],
            'medico'      => ['medico', 'médico', 'doctor', 'consultorio', 'clinica', 'clínica'],
            'encomienda'  => ['encomienda', 'envio', 'envío', 'courier', 'agencia'],
            'envio'       => ['envio', 'envío', 'delivery', 'reparto', 'motorizado'],
            'delivery'    => ['delivery', 'envio', 'envío', 'reparto'],
        ];
    }
}

if (!function_exists('chatbot_busqueda_variantes')) {
    /**
     * Las palabras con las que hay que buscar de verdad: la que escribió el visitante y sus parecidas
     * («polleria» → pollo, brasa…). Con eso encuentra tiendas cuyo NOMBRE no lleva la palabra
     * (el visitante dice «polleria» y la tienda se llama «Don Pollo»).
     * ⚠️ Las parecidas muy cortas (menos de 5 letras) se descartan: «gas» se metería dentro de
     * «entreGAS» y el buscador devolvería cualquier cosa. La palabra que escribió el visitante
     * siempre se respeta (y si es corta se busca por palabra completa, no por trozo).
     */
    function chatbot_busqueda_variantes($palabra) {
        $p    = mb_strtolower(trim((string)$palabra), 'UTF-8');
        $mapa = chatbot_busqueda_sinonimos();
        $out  = [$p];
        if (isset($mapa[$p])) {
            foreach ($mapa[$p] as $v) {
                if (mb_strlen($v) >= 5) $out[] = $v;   // las cortas solo valen si son lo que él escribió
            }
        }
        if (mb_substr($p, -1) === 's' && mb_strlen($p) > 4) $out[] = mb_substr($p, 0, -1);
        $out = array_values(array_unique(array_filter($out, function ($v) { return mb_strlen($v) >= 3; })));
        return array_slice($out, 0, 6);
    }
}

if (!function_exists('chatbot_busqueda_claves')) {
    /**
     * 🗂️ LAS «FRASES DE UNIÓN» DEL SITIO, en memoria: `categoria_id => [clave, clave, …]`.
     * Es la tabla `directorio_categoria_claves` (la misma que usan El caminante y las noticias):
     * las palabras de verdad que la gente escribe y que llevan a un rubro («clavos» → Ferreterías,
     * «paracetamol» → Farmacias). Todo sin tildes y en minúsculas, para poder comparar.
     * Se lee UNA vez por petición (static).
     */
    function chatbot_busqueda_claves() {
        static $mapa = null;
        if ($mapa !== null) return $mapa;
        $mapa = [];
        if (function_exists('obtener_claves_categorias')) {
            try {
                foreach (obtener_claves_categorias() as $cid => $lista) {
                    foreach ((array)$lista as $cl) {
                        $cl = chatbot_sin_tildes(mb_strtolower(trim((string)$cl), 'UTF-8'));
                        if ($cl !== '') $mapa[(int)$cid][] = $cl;
                    }
                }
            } catch (Throwable $e) { $mapa = []; }
        }
        return $mapa;
    }
}

if (!function_exists('chatbot_busqueda_rubro')) {
    /**
     * 🏷️ ¿A QUÉ RUBRO APUNTAN LAS PALABRAS DEL VISITANTE? Devuelve la fila del rubro
     * (`id`, `nombre`, `slug`, `icono`) o null. Es lo que convierte «clavos» en «Ferreterías».
     *
     * Cómo decide, en este orden de peso:
     *   1. **El nombre del rubro empieza una palabra** (`[[:<:]]pollo` → «Pollerías», `llanta` →
     *      «Reparación de llantas»): 4 puntos. Se exige PRINCIPIO DE PALABRA a propósito: con un
     *      `LIKE '%aca%'` a secas, «acá» encontraba «Ac**aca**demias» (el fallo original).
     *   2. **Una «frase de unión» del rubro** (`directorio_categoria_claves`): 3 puntos si coincide
     *      exacta, 2 si una contiene a la otra («clavo» ↔ «clavos»).
     * Empate: gana el rubro que TIENE tiendas activas (un rubro vacío no sirve de respuesta).
     */
    function chatbot_busqueda_rubro($pdo, array $tokens) {
        $puntos = [];

        // 1) El NOMBRE del rubro, por principio de palabra (solo palabras de 4 letras o más).
        foreach ($tokens as $tk) {
            $limpio = preg_replace('/[^a-z0-9]/', '', (string)$tk);
            if (mb_strlen($limpio) < 4) continue;
            try {
                $st = $pdo->prepare("SELECT id FROM directorio_categorias
                                      WHERE activo = 1 AND nombre REGEXP ?
                                      ORDER BY CHAR_LENGTH(nombre) ASC LIMIT 3");
                $st->execute(['[[:<:]]' . $limpio]);
                foreach ($st->fetchAll() as $f) {
                    $id = (int)$f['id'];
                    $puntos[$id] = ($puntos[$id] ?? 0) + 4;
                }
            } catch (Throwable $e) {}
        }

        // 2) Las «frases de unión» (la voz del visitante).
        $mapa = chatbot_busqueda_claves();
        foreach ($tokens as $tk) {
            foreach ($mapa as $cid => $lista) {
                foreach ($lista as $cl) {
                    if ($cl === $tk) {
                        $puntos[$cid] = ($puntos[$cid] ?? 0) + 3;
                    } elseif (mb_strlen($tk) >= 5 && mb_strpos($cl, $tk) !== false) {
                        $puntos[$cid] = ($puntos[$cid] ?? 0) + 2;   // «clavo» dentro de «clavos»
                    } elseif (mb_strlen($cl) >= 5 && mb_strpos($tk, $cl) !== false) {
                        $puntos[$cid] = ($puntos[$cid] ?? 0) + 2;   // «clavos» dentro de «clavosespeciales»
                    }
                }
            }
        }

        if (!$puntos) return null;
        arsort($puntos);
        $ids  = array_map('intval', array_keys($puntos));
        $cats = [];
        foreach ($ids as $id) {
            try {
                $st = $pdo->prepare("SELECT id, nombre, slug, icono FROM directorio_categorias WHERE id = ? LIMIT 1");
                $st->execute([$id]);
                $f = $st->fetch();
                if ($f) $cats[$id] = $f;
            } catch (Throwable $e) {}
        }
        if (!$cats) return null;

        // Cuántos negocios tiene cada candidato (una consulta por candidato, y suelen ser 1 a 3).
        // ⚠️ El DESEMPATE es por tiendas y no por el orden en que salieron: cuando una palabra está en
        //    dos rubros (las frases se encargan de que casi todas estén en uno solo, pero pasa), antes
        //    ganaba el que saliera primero de la consulta — o sea, la suerte. Ahora gana el rubro con
        //    MÁS negocios, que es la lectura más útil («tornillo» → Ferreterías y no Ópticas).
        foreach ($ids as $id) {
            if (!isset($cats[$id])) continue;
            try {
                [$cf, $cp] = function_exists('rubro_filtro_id')
                    ? rubro_filtro_id($id, 'n')
                    : ['n.categoria_id = ?', [$id]];
                $st = $pdo->prepare("SELECT COUNT(*) FROM directorio_negocios n WHERE n.estado = 'activo' AND $cf");
                $st->execute($cp);
                $cats[$id]['tiendas'] = (int)$st->fetchColumn();
            } catch (Throwable $e) {
                $cats[$id]['tiendas'] = 0;
            }
        }
        usort($ids, function ($a, $b) use ($puntos, $cats) {
            if (($puntos[$a] ?? 0) !== ($puntos[$b] ?? 0)) return ($puntos[$b] ?? 0) - ($puntos[$a] ?? 0);
            return (int)($cats[$b]['tiendas'] ?? 0) - (int)($cats[$a]['tiendas'] ?? 0);
        });

        // Se prefiere el rubro que TIENE tiendas activas; si ninguno tiene, el primero del puntaje.
        foreach ($ids as $id) {
            if (isset($cats[$id]) && $cats[$id]['tiendas'] > 0) return $cats[$id];
        }
        $primero = isset($ids[0]) && isset($cats[$ids[0]]) ? $cats[$ids[0]] : null;
        if (is_array($primero) && !isset($primero['tiendas'])) $primero['tiendas'] = 0;
        return $primero;
    }
}

if (!function_exists('chatbot_busqueda_es_clave_de')) {
    /**
     * ¿Esta palabra es una «frase de unión» de ESE rubro? (mismo criterio que usa el anfitrión).
     * Sirve para saber si el visitante nombró algo que el rubro TIENE («polleria» en Restaurantes,
     * «laptop» en Informática): así el copy puede decir «resultados de «pollería»» en vez del nombre
     * ancho del rubro.
     */
    function chatbot_busqueda_es_clave_de($palabra, $rubro_id) {
        $lista = chatbot_busqueda_claves()[(int)$rubro_id] ?? [];
        $p     = (string)$palabra;
        if ($p === '') return false;
        foreach ((array)$lista as $cl) {
            if ($cl === $p) return true;
            if (mb_strlen($p) >= 5 && mb_strpos($cl, $p) !== false) return true;
            if (mb_strlen($cl) >= 5 && mb_strpos($p, $cl) !== false) return true;
        }
        return false;
    }
}

if (!function_exists('chatbot_busqueda_servicios')) {
    /**
     * 🔧 LO QUE LA GENTE PIDE CUANDO QUIERE UN SERVICIO (orden del jefe, 2026-09-13, noche):
     * *«si alguien busca reparar su celular debe mostrarle a los técnicos de la categoría/rubro
     * celulares que tenemos en la base de datos»*.
     *
     * El visitante escribe el **VERBO** («reparar», «formatear», «arreglar») y las fichas del sitio
     * están escritas con el **SUSTANTIVO** («reparación», «formateo», «arreglo»). Aquí se traduce uno
     * en otro para poder (a) buscar los productos de servicio y (b) **poner delante a los técnicos**.
     *
     * ⚠️ Ojo: estas palabras NO se buscan como tienda (buscar «reparacion» suelto lleva a las
     * llanterías, porque su rubro se llama «Reparación de llantas»). Solo sirven para ORDENAR y para
     * buscar los productos del rubro que ya se identificó.
     */
    function chatbot_busqueda_servicios() {
        return [
            // verbo / lo que escribe la gente => sustantivo que usan las fichas
            'reparar' => 'reparacion', 'reparo' => 'reparacion', 'reparan' => 'reparacion',
            'repara'  => 'reparacion', 'reparacion' => 'reparacion',
            'arreglar' => 'arreglo', 'arreglan' => 'arreglo', 'arreglo' => 'arreglo',
            'formatear' => 'formateo', 'formateo' => 'formateo', 'formatean' => 'formateo',
            'mantener' => 'mantenimiento', 'mantenimiento' => 'mantenimiento',
            'instalar' => 'instalacion', 'instalacion' => 'instalacion', 'instalan' => 'instalacion',
            'revisar' => 'revision', 'revision' => 'revision', 'revisan' => 'revision',
            'limpiar' => 'limpieza', 'limpieza' => 'limpieza',
            'pintar' => 'pintura', 'pintura' => 'pintura',
            'soldar' => 'soldadura', 'soldadura' => 'soldadura',
            'lavar' => 'lavado', 'lavado' => 'lavado',
            'planchar' => 'planchado', 'planchado' => 'planchado',
            'coser' => 'costura', 'costura' => 'costura',
            'cortar' => 'corte', 'corte' => 'corte',
            'imprimir' => 'impresion', 'impresion' => 'impresion',
            'traducir' => 'traduccion', 'traduccion' => 'traduccion',
            'tapizar' => 'tapizado', 'tapizado' => 'tapizado',
            'afilar' => 'afilado', 'afilado' => 'afilado',
            'enderezar' => 'enderezado', 'alinear' => 'alineamiento',
            'balancear' => 'balanceo', 'enmarcar' => 'enmarcado',
            'duplicar' => 'duplicado', 'duplicado' => 'duplicado',
        ];
    }
}

if (!function_exists('chatbot_busqueda_servicio')) {
    /**
     * ¿La búsqueda es de un SERVICIO (reparar, formatear, mantenimiento, un técnico, un oficio…)?
     * Devuelve ['es' => bool, 'palabras' => [sustantivos], 'marcas' => [trozos para ordenar]].
     * Con eso, «reparar celular» pone **primero a los técnicos** del rubro de celulares.
     */
    function chatbot_busqueda_servicio(array $tokens) {
        $mapa    = chatbot_busqueda_servicios();
        $oficios = ['tecnico', 'tecnicos', 'tecnica', 'service', 'taller', 'mecanico', 'gasfitero',
                    'electricista', 'carpintero', 'soldador', 'llantero', 'vulcanizador', 'cerrajero',
                    'veterinario', 'dentista', 'medico', 'contador', 'abogado', 'profesor', 'albanil',
                    'maestro', 'arquitecto', 'ingeniero', 'enfermera', 'nutricionista', 'psicologo'];
        $palabras = [];
        $usados   = [];
        foreach ($tokens as $tk) {
            $tk = mb_strtolower((string)$tk, 'UTF-8');
            if ($tk === '') continue;
            if (isset($mapa[$tk]))              { $palabras[] = $mapa[$tk]; $usados[] = $tk; }
            if (in_array($tk, $oficios, true))  { $palabras[] = $tk;        $usados[] = $tk; }
            if (mb_substr($tk, -1) === 's') {                       // «reparaciones» → «reparacion»
                $sin = mb_substr($tk, 0, -1);
                if (isset($mapa[$sin]))             { $palabras[] = $mapa[$sin]; $usados[] = $tk; }
                if (in_array($sin, $oficios, true)) { $palabras[] = $sin;        $usados[] = $tk; }
            }
        }
        $palabras = array_values(array_unique(array_filter($palabras)));
        if (!$palabras) return ['es' => false, 'palabras' => [], 'marcas' => [], 'usados' => []];

        // Trozos que llevan los NOMBRES de los negocios que hacen servicios (y sus productos).
        $marcas = ['tecnic', 'service', 'servici', 'reparad', 'reparaci', 'arregl', 'mantenim',
                   'formate', 'instala', 'especiali', 'taller', 'soporte'];
        foreach ($palabras as $p) {
            if (mb_strlen($p) >= 5) $marcas[] = mb_substr($p, 0, 6);
        }
        $marcas = array_values(array_unique(array_map(function ($m) {
            return preg_replace('/[^a-z]/', '', (string)$m);      // solo letras: se pega en el SQL
        }, $marcas)));
        return ['es' => true, 'palabras' => $palabras, 'marcas' => array_values(array_filter($marcas)),
                'usados' => array_values(array_unique($usados))];
    }
}

if (!function_exists('chatbot_busqueda_ors')) {
    /**
     * Las condiciones SQL de UNA palabra en varias columnas. Si la palabra es corta (3 o 4 letras) se
     * exige que empiece una palabra (`REGEXP [[:<:]]gas`) para no encontrar «entreGAS»; si es larga,
     * basta con que aparezca dentro (`LIKE %polleria%`).
     */
    function chatbot_busqueda_ors($palabra, array $columnas, array &$par) {
        $ors = [];
        if (mb_strlen($palabra) < 5) {
            $rx = '[[:<:]]' . preg_replace('/[^a-z0-9]/', '', $palabra);
            if ($rx === '[[:<:]]') return $ors;
            foreach ($columnas as $col) { $ors[] = $col . ' REGEXP ?'; $par[] = $rx; }
        } else {
            foreach ($columnas as $col) { $ors[] = $col . ' LIKE ?'; $par[] = '%' . $palabra . '%'; }
        }
        return $ors;
    }
}

if (!function_exists('chatbot_buscar_tiendas')) {
    /**
     * 🏪 Las tiendas que coinciden con las palabras (nombre, rubro principal, rubros extra o
     * distrito) y, si se pasa `$rubro_id`, SOLO las de ese rubro.
     * Con coordenadas primero se ordena por DISTANCIA y, si con la ubicación no sale ninguna (casi
     * ninguna tienda tiene GPS guardado), se repite sin ubicación: nunca se queda sin respuesta.
     * Devuelve ['tiendas' => [...], 'geo_sin_datos' => bool].
     */
    function chatbot_buscar_tiendas($pdo, array $tokens, $rubro_id, $geo, $lat, $lng, $limite, array $marcas = [], $en_productos = false) {
        $out = ['tiendas' => [], 'geo_sin_datos' => false];

        $arma = function ($con_geo) use ($tokens, $rubro_id, $limite, $marcas, $en_productos) {
            $sql = "SELECT n.id, n.nombre, n.slug, n.destacado, n.vistas_count, n.rating,
                           c.nombre AS rubro, d.nombre AS distrito,
                           (SELECT f.ruta FROM directorio_fotos f
                             WHERE f.negocio_id = n.id ORDER BY f.orden ASC LIMIT 1) AS foto";
            $par = [];
            if ($con_geo) {
                // ⚠️ Estos dos «?» van PRIMERO en la consulta (están en el SELECT), así que sus
                // valores se ponen al principio de los parámetros: POINT(lng, lat).
                $sql .= ", ST_Distance_Sphere(POINT(n.lng, n.lat), POINT(?, ?)) AS distancia_m";
            }
            if (function_exists('rubros_multi_ok') && rubros_multi_ok()) {
                $sql .= ", (SELECT GROUP_CONCAT(rc.nombre SEPARATOR ' ')
                             FROM directorio_negocio_rubros rr
                             JOIN directorio_categorias rc ON rc.id = rr.categoria_id
                            WHERE rr.negocio_id = n.id) AS rubros_extra";
            }
            $sql .= " FROM directorio_negocios n
                      LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                      LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
                      WHERE n.estado = 'activo'";
            if ($con_geo) $sql .= ' AND n.lat IS NOT NULL AND n.lng IS NOT NULL';

            // 🏷️ Las tiendas de un RUBRO (cuando la frase de unión ya dijo de qué rubro se trata).
            if ((int)$rubro_id > 0 && function_exists('rubro_filtro_id')) {
                [$cf, $cp] = rubro_filtro_id((int)$rubro_id, 'n');
                $sql .= " AND " . $cf;
                $par  = array_merge($par, $cp);
            }

            // Cada palabra tiene que aparecer en el nombre, en su rubro (principal o extra) o en el distrito.
            foreach ($tokens as $tk) {
                $ors = [];
                foreach (chatbot_busqueda_variantes($tk) as $v) {
                    $ors = array_merge($ors, chatbot_busqueda_ors($v, ['n.nombre', 'c.nombre', 'd.nombre'], $par));
                    // 🔧 En las búsquedas de SERVICIO la palabra también vale si está en el TÍTULO de un
                    //    producto: así «reparar laptop» trae a los que reparan laptops (aunque su nombre
                    //    no diga «laptop») y «reparar celular» a los que reparan celulares.
                    if ($en_productos) {
                        $ors[] = "EXISTS (SELECT 1 FROM directorio_servicios sp
                                           WHERE sp.negocio_id = n.id AND sp.activo = 1 AND sp.titulo LIKE ?)";
                        $par[] = '%' . $v . '%';
                    }
                    if (mb_strlen($v) >= 5 && function_exists('rubros_multi_ok') && rubros_multi_ok()) {
                        $ors[] = 'EXISTS (SELECT 1 FROM directorio_negocio_rubros rr
                                           JOIN directorio_categorias rc ON rc.id = rr.categoria_id
                                          WHERE rr.negocio_id = n.id AND rc.nombre LIKE ?)';
                        $par[] = '%' . $v . '%';
                    }
                }
                if (!$ors) continue;
                $sql .= " AND (" . implode(' OR ', $ors) . ")";
            }

            // 🔧 ¿La búsqueda es de un SERVICIO («reparar celular», «un técnico»)? Entonces van PRIMERO
            // los negocios que hacen servicios, EN DOS NIVELES (orden del jefe, 2026-09-13):
            //   1.º los que lo dicen en su **NOMBRE** («Servicio técnico de celulares Julinho»,
            //       «Microtech Service», «Técnico Germán Computadoras») — esos son «los técnicos»;
            //   2.º los que tienen el **servicio publicado** («Reparación de pantalla», «Instalación y
            //       soporte técnico»);
            //   3.º y recién después el resto del rubro.
            // ⚠️ Los trozos vienen de una lista propia y se limpian a solo letras: así se pueden pegar
            //    en el SQL sin comillas (nunca entra nada escrito por el visitante).
            $boost = '';
            if ($marcas) {
                $ors_nombre = [];
                $ors_prod   = [];
                foreach ($marcas as $m) {
                    $m = preg_replace('/[^a-z]/', '', (string)$m);
                    if ($m === '' || mb_strlen($m) < 5) continue;      // marcas muy cortas = ruido
                    $ors_nombre[] = "n.nombre LIKE '%$m%'";
                    $ors_prod[]   = "sv.titulo LIKE '%$m%'";
                }
                if ($ors_nombre) {
                    $boost = '(' . implode(' OR ', $ors_nombre) . ') DESC, ' .
                             'EXISTS (SELECT 1 FROM directorio_servicios sv
                                       WHERE sv.negocio_id = n.id AND sv.activo = 1
                                         AND (' . implode(' OR ', $ors_prod) . ')) DESC, ';
                }
            }

            // Con ubicación manda la DISTANCIA; sin ubicación, lo de siempre (destacados y más vistos).
            $sql .= $con_geo
                ? " ORDER BY distancia_m ASC, " . $boost . "n.destacado DESC, n.vistas_count DESC LIMIT " . max(1, min(12, (int)$limite + 3))
                : " ORDER BY " . $boost . "n.destacado DESC, n.vistas_count DESC, n.nombre ASC LIMIT " . max(1, min(12, (int)$limite + 4));
            return [$sql, $par];
        };

        if ($geo) {
            [$sql, $par] = $arma(true);
            $st = $pdo->prepare($sql);
            $st->execute(array_merge([$lng, $lat], $par));   // POINT(lng, lat): primero los del SELECT
            $out['tiendas'] = $st->fetchAll() ?: [];
        }
        if (!$out['tiendas']) {
            [$sql, $par] = $arma(false);
            $st = $pdo->prepare($sql);
            $st->execute($par);
            $out['tiendas'] = $st->fetchAll() ?: [];
            if ($geo && $out['tiendas']) $out['geo_sin_datos'] = true;
        }
        return $out;
    }
}

if (!function_exists('chatbot_buscar_productos')) {
    /**
     * 🛍️ Los productos que coinciden con las palabras (título del producto, rubro o nombre de la
     * tienda). Si se pasa `$rubro_id`, SOLO los productos de tiendas de ese rubro: es lo que evita
     * que al pedir «clavos» salga una **chicha morada con clavo de olor** (el único «resultado» que
     * daba antes este buscador).
     */
    function chatbot_buscar_productos($pdo, array $tokens, $rubro_id, $limite) {
        $sql = "SELECT s.id, s.titulo, s.precio, s.unidad, s.destacado, s.imagen,
                       n.nombre AS tienda, n.slug AS tienda_slug,
                       c.nombre AS rubro, d.nombre AS distrito,
                       (SELECT pf.ruta FROM directorio_producto_fotos pf
                         WHERE pf.producto_id = s.id ORDER BY pf.orden ASC LIMIT 1) AS foto
                  FROM directorio_servicios s
                  JOIN directorio_negocios n ON n.id = s.negocio_id AND n.estado = 'activo'
                  LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                  LEFT JOIN directorio_distritos  d ON d.id = n.distrito_id
                 WHERE s.activo = 1 AND " . sql_producto_vigente('s');
        $par = [];
        if ((int)$rubro_id > 0 && function_exists('rubro_filtro_id')) {
            [$cf, $cp] = rubro_filtro_id((int)$rubro_id, 'n');
            $sql .= " AND " . $cf;
            $par  = array_merge($par, $cp);
        }
        foreach ($tokens as $tk) {
            $ors = [];
            foreach (chatbot_busqueda_variantes($tk) as $v) {
                $ors = array_merge($ors, chatbot_busqueda_ors($v, ['s.titulo', 'c.nombre', 'n.nombre'], $par));
            }
            if (!$ors) continue;
            $sql .= " AND (" . implode(' OR ', $ors) . ")";
        }
        $primero = isset($tokens[0]) ? (string)$tokens[0] : '';
        if ($primero !== '') {
            $sql .= " ORDER BY CASE WHEN s.titulo LIKE ? THEN 0 ELSE 1 END, s.destacado DESC, n.vistas_count DESC";
            $par[] = '%' . $primero . '%';
        } else {
            $sql .= " ORDER BY s.destacado DESC, n.vistas_count DESC";
        }
        $sql .= " LIMIT " . max(1, min(12, (int)$limite + 5));
        $st = $pdo->prepare($sql);
        $st->execute($par);
        return $st->fetchAll() ?: [];
    }
}

if (!function_exists('chatbot_buscar')) {
    /**
     * 🔎 BUSCA EN LA BASE DEL SITIO: el rubro al que apuntan las palabras (frases de unión + nombre
     * del rubro), las tiendas y los productos.
     * Devuelve ['termino', 'productos', 'tiendas', 'rubro', 'total', 'geo', 'geo_sin_datos',
     *          'por_rubro'].
     * Nunca lanza: si la BD falla, devuelve vacío (el chat sigue funcionando con la IA).
     *
     * 🔁 Y no se rinde con la primera consulta: si el término trae VARIAS palabras y juntas no
     * encuentran nada, se reintenta palabra por palabra. Así, si el visitante coló una palabra que
     * estorba («clavo **aca**»), la respuesta sale igual con la palabra buena.
     */
    function chatbot_buscar($termino, $limite = 5, $lat = null, $lng = null) {
        $out = ['termino' => (string)$termino, 'productos' => [], 'tiendas' => [], 'rubro' => null,
                'total' => 0, 'geo' => false, 'geo_sin_datos' => false, 'por_rubro' => false,
                'servicio' => false];
        $tokens = preg_split('/\s+/u', (string)$termino, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (!$tokens || !function_exists('db')) return $out;

        // 📍 ¿Vienen las coordenadas del celular? Entonces las tiendas salen ORDENADAS POR DISTANCIA
        // (es la orden del jefe, 2026-09-13: «¿te muestro las más cercanas a ti? necesitaré que actives
        // tu ubicación», y eso se resuelve DENTRO del chat, sin salir a otra página).
        $geo = ($lat !== null && $lng !== null && is_numeric($lat) && is_numeric($lng));
        $out['geo'] = $geo;

        try {
            $pdo = db();

            // ---- 1) 🏷️ ¿A QUÉ RUBRO APUNTAN LAS PALABRAS? (lo primero: «clavos» → Ferreterías) ----
            $rubro = chatbot_busqueda_rubro($pdo, $tokens);
            $out['rubro'] = $rubro;
            $rid = $rubro ? (int)$rubro['id'] : 0;

            // ---- 1-bis) 🔧 ¿Y es una búsqueda de SERVICIO? («reparar celular», «un técnico») ----
            // Sirve para dos cosas: poner delante a los negocios que hacen servicios y sacar los
            // servicios publicados («Reparación de laptops y computadoras»), que es lo que busca quien
            // quiere reparar algo. Ver `chatbot_busqueda_servicio()`.
            $servicio = chatbot_busqueda_servicio($tokens);
            $marcas   = $servicio['es'] ? $servicio['marcas'] : [];
            $out['servicio'] = !empty($servicio['es']);

            // ---- 2) Las palabras a probar: TODAS juntas (lo más preciso) y, si no hay nada, de a una ----
            $conjuntos = [$tokens];
            if (count($tokens) > 1) {
                foreach ($tokens as $tk) { $conjuntos[] = [$tk]; }
            }

            // ---- 3) 🏪 TIENDAS ----
            // El ORDEN es la clave de que la respuesta sea la que espera el visitante:
            //   1.º 🔧 Un SERVICIO con rubro conocido → los del rubro («reparar celular» → los técnicos
            //       de Informática / Celulares), buscando la palabra del producto también en los TÍTULOS
            //       de los servicios (así «reparar laptop» trae a los que reparan laptops).
            //   2.º TODAS las palabras juntas (lo más preciso).
            //   3.º El RUBRO de esas palabras (si se conoce).
            //   4.º Palabra por palabra (último recurso). Va DESPUÉS del rubro a propósito: si no,
            //       «protector solar» caía en un contador que se apellida «Del Solar».
            $tope_tiendas = max(1, min(12, (int)$limite + 4));

            if ($servicio['es'] && $rid > 0) {
                $tk_prod = array_values(array_diff($tokens, (array)$servicio['usados']));
                if (!$tk_prod) $tk_prod = $tokens;
                $t = chatbot_buscar_tiendas($pdo, $tk_prod, $rid, $geo, $lat, $lng, $limite, $marcas, true);
                if ($t['tiendas']) {
                    $out['tiendas']      = $t['tiendas'];
                    $out['geo_sin_datos'] = $t['geo_sin_datos'];
                    $out['por_rubro']    = true;
                }
            }
            if (!$out['tiendas']) {
                $t = chatbot_buscar_tiendas($pdo, $tokens, 0, $geo, $lat, $lng, $limite, $marcas);
                if ($t['tiendas']) {
                    $out['tiendas']      = $t['tiendas'];
                    $out['geo_sin_datos'] = $t['geo_sin_datos'];
                }
            }
            if (!$out['tiendas'] && $rid > 0) {
                $t = chatbot_buscar_tiendas($pdo, [], $rid, $geo, $lat, $lng, $limite, $marcas);
                if ($t['tiendas']) {
                    $out['tiendas']      = $t['tiendas'];
                    $out['geo_sin_datos'] = $t['geo_sin_datos'];
                    $out['por_rubro']    = true;
                }
            }
            if (!$out['tiendas'] && count($tokens) > 1) {
                foreach ($tokens as $tk) {
                    $t = chatbot_buscar_tiendas($pdo, [$tk], 0, $geo, $lat, $lng, $limite, $marcas);
                    if ($t['tiendas']) {
                        $out['tiendas']      = $t['tiendas'];
                        $out['geo_sin_datos'] = $t['geo_sin_datos'];
                        break;
                    }
                }
            }

            // 🏷️ Y SIEMPRE, si hay rubro, se completa la lista con sus negocios (sin repetir): es la
            //    respuesta a «quién vende clavos» (las ferreterías) y lo que llena de fotos la respuesta.
            if ($rid > 0 && count($out['tiendas']) < $tope_tiendas) {
                $t = chatbot_buscar_tiendas($pdo, [], $rid, $geo, $lat, $lng, $limite, $marcas);
                $vistos  = array_map('intval', array_column($out['tiendas'], 'id'));
                $sumadas = 0;
                foreach ($t['tiendas'] as $fila) {
                    if (in_array((int)$fila['id'], $vistos, true)) continue;
                    $out['tiendas'][] = $fila;
                    $vistos[] = (int)$fila['id'];
                    $sumadas++;
                    if (count($out['tiendas']) >= $tope_tiendas) break;
                }
                if ($sumadas > 0) {
                    $out['por_rubro'] = true;
                    if (!empty($t['geo_sin_datos'])) $out['geo_sin_datos'] = true;
                }
            }

            // ---- 4) 🛍️ PRODUCTOS (si hay rubro, solo los suyos: nunca una chicha morada por «clavo») ----
            foreach ($conjuntos as $tks) {
                $p = chatbot_buscar_productos($pdo, $tks, $rid, $limite);
                if ($p) { $out['productos'] = $p; break; }
            }
            if (!$out['productos'] && $rid > 0) {
                $out['productos'] = chatbot_buscar_productos($pdo, [], $rid, $limite);
            }

            // 🛠️ Y si la búsqueda es de un SERVICIO («reparar celular»), los servicios PUBLICADOS van
            //    primero: «Reparación de laptops y computadoras», «Formateo e instalación de Windows»…
            //    Es lo que de verdad busca quien quiere reparar algo (orden del jefe).
            if ($servicio['es']) {
                $extra = chatbot_buscar_productos($pdo, $servicio['palabras'], $rid, 3);
                if ($extra) {
                    $out['productos'] = array_slice($out['productos'], 0, 4);
                    $ids = [];
                    $tmp = [];
                    foreach (array_merge($extra, $out['productos']) as $p) {
                        if (isset($ids[(int)$p['id']])) continue;
                        $ids[(int)$p['id']] = true;
                        $tmp[] = $p;
                    }
                    $out['productos'] = array_slice($tmp, 0, 6);
                }
            }
        } catch (Throwable $e) {
            error_log('chatbot_buscar: ' . $e->getMessage());
        }

        $out['total'] = count($out['tiendas']) + count($out['productos']);
        return $out;
    }
}

if (!function_exists('chatbot_busqueda_foto')) {
    /** La foto (300 px) de un producto o de una tienda, o '' si no tiene. */
    function chatbot_busqueda_foto($fila) {
        $ruta = trim((string)($fila['foto'] ?? ''));
        if ($ruta === '') $ruta = trim((string)($fila['imagen'] ?? ''));
        if ($ruta === '') return '';
        if (function_exists('img_url')) return img_url($ruta, 300);
        return function_exists('url_imagen') ? url_imagen($ruta) : '';
    }
}

if (!function_exists('chatbot_busqueda_frase')) {
    /**
     * 🎨 ELIGE UNA FRASE AL AZAR de una lista, para que el chat NO repita siempre lo mismo
     * (orden del jefe, 2026-09-13: *«mejora el copywriting, hazlo más bonito, amigable»*).
     * Un amigo no dice la misma frasecita cada vez: aquí se sortea entre las que ya están aprobadas.
     */
    function chatbot_busqueda_frase(array $frases) {
        $frases = array_values(array_filter($frases, function ($f) { return trim((string)$f) !== ''; }));
        if (!$frases) return '';
        return (string)$frases[array_rand($frases)];
    }
}

if (!function_exists('chatbot_busqueda_texto')) {
    /**
     * Arma la respuesta de la búsqueda: **CORTA, con el resultado de frente** (orden del jefe,
     * 2026-09-13): *«la función del bot es informar de manera rápida y en pocas palabras, resultados
     * personalizados»*… **y con el tono de un amigo** (*«mejora el copywriting, hazlo más bonito,
     * amigable»*).
     *
     * Las frases están escritas por CASOS, y en cada caso se sortea una (`chatbot_busqueda_frase()`):
     *   1. **Servicio** («reparar celular»): «Estos son los que te lo dejan como nuevo 🔧».
     *   2. **Con ubicación**: «Estas te quedan cerquita 👇» + la distancia en cada línea.
     *   3. **Rubro con más tiendas de las que se muestran**: «Tengo 56 opciones de ferreterías 😃».
     *   4. **Lo de siempre**: «Sí, estos son los resultados de pollerías en la ciudad».
     *   5. **Solo productos**: «Mira lo que encontré 👇».
     *   6. **Sin GPS de todas** (`geo_sin_datos`): se dice con gracia, no como una disculpa técnica.
     *   7. **El rubro** («Tienes más en el rubro Ferreterías 👀») y **el buscador** («Y hay más en el
     *      buscador»), cada uno con su frasecita.
     *   8. **La despedida es UNA sola pregunta**: la de la ubicación (el ejemplo del jefe).
     *
     * ⚠️ El **total del rubro** que se nombra sale de `$res['rubro']['tiendas']` (lo cuenta
     *    `chatbot_busqueda_rubro()` sin gastar una consulta más) y solo se usa cuando las tiendas
     *    salieron DEL RUBRO (`por_rubro`): así el número siempre cuadra con lo que se muestra.
     */
    function chatbot_busqueda_texto($res) {
        $termino  = (string)($res['termino'] ?? '');
        $geo      = !empty($res['geo']);
        $servicio = !empty($res['servicio']);
        $rubro    = is_array($res['rubro'] ?? null) ? $res['rubro'] : null;
        $L = [];

        $linea = function ($titulo, $url, $foto, $cola) {
            $t = $foto !== '' ? '[![' . $titulo . '](' . $foto . ')](' . $url . ') ' : '';
            return '- ' . $t . '[**' . $titulo . '**](' . $url . ')' . ($cola !== '' ? ' — ' . $cola : '');
        };

        // 🏪 Tiendas (lo primero cuando se busca un rubro, que es lo que más se pregunta).
        $tiendas = array_slice((array)($res['tiendas'] ?? []), 0, 3);
        if ($tiendas) {
            // El nombre del rubro, en minúsculas y en medio de la frase: «Pastelerías y Tortas» →
            // «pastelerías y tortas» (así nunca queda una mayúscula rara en medio de la oración).
            $como = mb_strtolower(trim((string)($rubro['nombre'] ?? '')), 'UTF-8');
            if ($como === '') $como = $termino;
            $cuantas = (int)($rubro['tiendas'] ?? 0);

            // 🗣️ Y a veces se nombra MEJOR con lo que escribió el visitante: cuando su palabra es un
            // TIPO DE NEGOCIO («polleria», «cebicheria», «libreria»… terminan en «-ería») y el rubro es
            // más ancho que ella (el rubro se llama «Restaurantes»), se dice «pollería».
            // ⚠️ Con los PRODUCTOS no se hace: decir «aquí tienes «helado»» sería peor que decir el
            //    nombre del rubro («heladerías y juguerías»).
            $dicho = $como;
            if (empty($res['por_rubro']) && $rubro && $termino !== '' && function_exists('chatbot_busqueda_es_clave_de')) {
                $tk = explode(' ', $termino)[0];
                if ($tk !== '' && mb_strpos($como, $tk) === false
                    && mb_substr($tk, -4) === 'eria'
                    && chatbot_busqueda_es_clave_de($tk, (int)$rubro['id'])) {
                    // El término viene SIN tildes (el buscador las quita para comparar), así que se le
                    // devuelve la tilde al escribirlo: «polleria» → **«pollería»** (en español, las
                    // palabras de negocio terminadas en -ería la llevan siempre).
                    $dicho = '«' . mb_substr($tk, 0, -4) . 'ería»';
                }
            }

            if ($servicio) {
                // 🔧 El visitante quiere que le REPAREN algo: se le habla de los que lo hacen.
                $L[] = chatbot_busqueda_frase([
                    'Estos son los que te lo dejan como nuevo 🔧:',
                    'Aquí sí te lo reparan 👇:',
                    'Estos técnicos te pueden ayudar 🔧:',
                    'Para eso, estos te sacan del apuro 👇:',
                ]);
            } elseif ($geo && empty($res['geo_sin_datos'])) {
                // Ya dio la ubicación: solo se le dan las cercanas, sin volver a preguntar nada.
                $L[] = chatbot_busqueda_frase([
                    'Estas son las más cercanas a ti 📍:',
                    'Estas te quedan cerquita 👇:',
                    'Las que tienes más a la mano 📍:',
                ]);
            } elseif (!empty($res['por_rubro']) && $cuantas > count($tiendas) && $como !== '') {
                // Hay MÁS de las que se muestran: se dice el total (es el dato que da confianza).
                $L[] = chatbot_busqueda_frase([
                    'Tengo ' . $cuantas . ' opciones de ' . $como . ' 😃 mira estas:',
                    'Hay ' . $cuantas . ' opciones de ' . $como . ' en la ciudad — aquí van algunas 👇:',
                    'De ' . $como . ' tengo ' . $cuantas . ' 😃 te muestro las más movidas:',
                ]);
            } else {
                // «Sí, estos son los resultados de <lo que buscó> en la ciudad» (el ejemplo del jefe).
                $L[] = chatbot_busqueda_frase([
                    'Sí, estos son los resultados de ' . $dicho . ' en la ciudad:',
                    '¡Claro que sí! 🙌 Mira lo que tengo de ' . $dicho . ':',
                    'Uy, sí 👀 aquí tienes ' . $dicho . ':',
                    'Te dejo lo mejor de ' . $dicho . ' que tengo 👇:',
                ]);
            }
            foreach ($tiendas as $t) {
                $url  = 'https://dechimbote.com/neg/' . rawurlencode((string)$t['slug']);
                $cola = '';
                // 📍 La distancia, si la tenemos (es lo que hace la respuesta "personalizada").
                if (isset($t['distancia_m']) && function_exists('distancia_txt')) {
                    $cola = '📍 ' . distancia_txt((float)$t['distancia_m']);
                }
                if ($cola === '') {
                    // Sin distancia: el rubro y, si se sabe, el distrito (queda más útil y más bonito).
                    $cola = trim((string)($t['rubro'] ?? ''));
                    $dis  = trim((string)($t['distrito'] ?? ''));
                    if ($cola !== '' && $dis !== '' && mb_strtolower($dis) !== 'chimbote') $cola .= ' · ' . $dis;
                    if ($cola === '') $cola = $dis;
                } else {
                    $rub = trim((string)($t['rubro'] ?? ''));
                    if ($rub !== '') $cola .= ' · ' . $rub;
                }
                $L[] = $linea((string)$t['nombre'], $url, chatbot_busqueda_foto($t), $cola);
            }
            if ($geo && !empty($res['geo_sin_datos'])) {
                $L[] = '(Todavía no todas me han dicho dónde están, así que van sin distancia 😅)';
            }
        }

        // 🛍️ Productos (con su precio y el nombre de la tienda, todo enlazado a la tienda).
        // Se muestran 2 si ya hay tiendas y 4 si no hay ninguna: la respuesta tiene que ser CORTA.
        $productos = array_slice((array)($res['productos'] ?? []), 0, $tiendas ? 2 : 4);
        if ($productos) {
            if (!$tiendas) {
                $L[] = chatbot_busqueda_frase([
                    'Mira lo que encontré 👇:',
                    'Justo tengo esto 👀:',
                    'Esto te puede servir 👇:',
                    'Toma, mira esto 🔎:',
                ]);
            }
            foreach ($productos as $p) {
                $url  = 'https://dechimbote.com/neg/' . rawurlencode((string)$p['tienda_slug']);
                $cola = '';
                if ((float)($p['precio'] ?? 0) > 0) {
                    $cola = '**S/ ' . number_format((float)$p['precio'], 2) . '**' .
                            (trim((string)($p['unidad'] ?? '')) !== '' ? ' ' . trim((string)$p['unidad']) : '');
                    $cola .= ' · ';
                }
                $cola .= '[' . (string)$p['tienda'] . '](' . $url . ')';
                $L[] = $linea((string)$p['titulo'], $url, chatbot_busqueda_foto($p), $cola);
            }
        }

        if (!$L) return '';

        // El rubro, cuando la respuesta salió de él: es el "ver más" natural (todas sus tiendas).
        if (!empty($rubro['nombre']) && (!empty($res['por_rubro']) || (!$tiendas && !$productos))) {
            $url_rubro = 'https://dechimbote.com/categoria/' . rawurlencode((string)$rubro['slug']);
            $L[] = chatbot_busqueda_frase([
                'Tienes más en el rubro [' . $rubro['nombre'] . '](' . $url_rubro . ') 👀',
                'Y si quieres verlas todas: [' . $rubro['nombre'] . '](' . $url_rubro . ') 👈',
                'Hay más en [' . $rubro['nombre'] . '](' . $url_rubro . ') ✨',
            ]);
        }

        // 📍 UNA SOLA pregunta, y es la de la ubicación (el ejemplo del jefe). Si ya la dio, no se repite.
        if (!$geo && $tiendas) {
            $L[] = chatbot_busqueda_frase([
                '¿Te muestro las más cercanas a ti? Solo activa tu ubicación 📍',
                '¿Quieres que te las ordene por cercanía? Activa tu ubicación 📍',
                '¿Te digo cuáles te quedan más cerca? Activa tu ubicación 📍',
            ]);
        } elseif (!$tiendas) {
            $url_buscar = 'https://dechimbote.com/buscar.php?q=' . rawurlencode($termino);
            $L[] = chatbot_busqueda_frase([
                'Y hay más en [el buscador](' . $url_buscar . ') 👀',
                'Si quieres ver todo lo que hay: [el buscador](' . $url_buscar . ').',
            ]);
        }

        return implode("\n", $L);
    }
}
