<?php
/**
 * includes/busqueda_limpieza.php — 🧹 LA LIMPIEZA DE LA BÚSQUEDA (mando del jefe, 2026-09-14)
 * =========================================================================================
 * Pedido del jefe, textual: *«la palabra comprar debería ser filtrada de los resultados de búsqueda…
 * el buscador debe saber filtrar palabras que simplemente acompañan una búsqueda pero no son parte de
 * la búsqueda; por ejemplo "comprar" no se debe buscar, es una especie de indicación… también podría
 * ser el término "buscar" o "quién tiene"… si alguien quisiera ver resultados de tiendas que venden
 * cerveza podría usar la palabra "dónde hay cerveza" y el buscador debe entregar resultados de
 * cerveza y no "de dónde"… "quién hace cerveza" el término correcto es cerveza y no "quién hace"…
 * "sabes que quiero comprar cerveza", el término nuevamente sería cerveza»*.
 *
 * QUÉ ES ESTO
 * -----------
 * El visitante escribe como HABLA, no como está escrito el catálogo: pide «comprar clavos», «dónde hay
 * cerveza» o «quién tiene botica». El buscador del sitio (igual que los buscadores de antes de la IA)
 * tiene que saber que **«comprar», «dónde hay», «quién tiene», «buscar» o «sabes que quiero»** son
 * MANDOS (la forma de pedir) y que **lo que se busca es el sustantivo que va detrás**.
 *
 * Se hace con PROGRAMACIÓN, no con IA: es un diccionario corto y una rutina de limpieza. Es gratis,
 * instantáneo (0 ms, sin red) y predecible, y es exactamente lo que hacen los buscadores desde 1998
 * (las «stop words»). La IA no aporta nada aquí: no hay nada que «entender», hay palabras que sobran.
 *
 * QUÉ SE QUITA (las 3 familias, todas EN CUALQUIER PARTE del texto)
 * -----------------------------------------------------------------
 *   1. **MANDOS** ………………… el verbo o la pregunta con la que piden algo: «comprar clavos» → clavos ·
 *                              «quién tiene cerveza» → cerveza · «dónde hay cerveza» → cerveza ·
 *                              «sabes que quiero comprar cerveza» → cerveza ·
 *                              «hay alguna bodega que venda puchitos» → bodega puchitos.
 *                              Las frases de la lista («donde hay», «me puedes recomendar») valen
 *                              también por sus palabras sueltas, así que se quitan estén donde estén.
 *   2. **CONECTORES** ………… las palabras de enganche: de, la, en, para, unos…
 *                              «comprar jugo de piña» → jugo piña.
 *   3. **MULETILLAS** …………… lo que se dice al final: «… por favor», «… gracias», «… porfa».
 *
 * ⚠️ POR QUÉ QUITARLAS EN CUALQUIER PARTE Y NO SOLO AL PRINCIPIO
 * -------------------------------------------------------------
 * Porque la gente no pide solo con el mando delante: *«hay alguna bodega que venda puchitos»* lleva el
 * verbo en medio, y *«muéstrame los precios de los clavos»* lleva DOS («muéstrame» y «precios»). Se
 * puede quitar siempre porque la búsqueda del sitio es **por trozo** (`LIKE %palabra%`): aunque la
 * tienda se llame «Compro Oro», quien escribe «compro oro» sigue encontrándola buscando «oro».
 * Es lo mismo que hace el buscador del chat (`chatbot_buscar.php`).
 *
 * ⚠️ RED DE SEGURIDAD (lo más importante de todo el archivo)
 * ---------------------------------------------------------
 * Si al limpiar no queda NADA útil (por ejemplo alguien busca solo «comprar» o «dónde hay»), **se
 * busca lo que escribió TAL CUAL**: mejor una búsqueda rara que una búsqueda vacía. La limpieza nunca
 * puede dejar al visitante sin resultados.
 *
 * ⚠️ ESTE ARCHIVO ES LA ÚNICA VERDAD DEL DICCIONARIO
 * -------------------------------------------------
 * El diccionario se le manda al navegador (`busqueda_diccionario_js()` se pinta en
 * `includes/footer.php` como `window.CHIMBOTE_LIMPIEZA`) y lo usan los DOS motores: el de PHP
 * (`busqueda_limpiar()`, para `buscar.php` y `api/sugerir.php`) y el de JavaScript
 * (`assets/js/buscador_limpieza.js`, para el desplegable y lo que se envía del formulario).
 * **Si se añade una palabra aquí, se añade sola en los dos lados.**
 * El respaldo que vive dentro del JS se comprueba con `node D:\RELAX\__limpieza_prueba.js` (si se
 * desincroniza, la prueba lo dice).
 *
 * Guía del módulo: `GUIA_BUSCADOR_FUZZY.md` (§4.8) · diccionario hermano del chat:
 * `includes/chatbot_buscar.php` (`chatbot_busqueda_ruido()`, que además quita el lugar).
 */

if (!function_exists('busqueda_norm')) {
    /**
     * Minúsculas y sin tildes: para COMPARAR (nunca para mostrar).
     * Es la versión de este módulo para que el archivo se valga por sí solo (el chat tiene la suya:
     * `chatbot_sin_tildes()`, porque ese archivo se carga solo).
     */
    function busqueda_norm($texto) {
        $t = mb_strtolower(trim((string)$texto), 'UTF-8');
        return strtr($t, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u', 'ç' => 'c', 'ã' => 'a', 'õ' => 'o',
        ]);
    }
}

if (!function_exists('busqueda_limpieza_mandos')) {
    /**
     * 🗣️ LOS MANDOS: la forma de pedir. Se quitan de CUALQUIER parte del texto (ver la cabecera del
     * archivo), y las frases valen también por sus palabras sueltas: de «me puedes recomendar» se
     * quitan «me», «puedes» y «recomendar» estén donde estén.
     * Todas SIN TILDES y en minúsculas: se comparan normalizadas.
     */
    function busqueda_limpieza_mandos() {
        return [
            // —— Preguntas enteras con las que piden algo ——
            'sabes que', 'sabes donde', 'sabes si', 'sabes de', 'sabes',
            'no sabes donde', 'tu sabes donde', 'tu sabes',
            'quiero comprar', 'quiero ver', 'quiero buscar', 'quiero saber', 'quiero encontrar',
            'quisiera comprar', 'quisiera ver', 'quisiera saber', 'quisiera',
            'quiero', 'queremos', 'queria',
            'vamos a comprar', 'vamos a ver', 'vamos a buscar', 'vamos a', 'vamos',
            'necesito comprar', 'necesito ver', 'necesito encontrar', 'necesito saber',
            'necesito', 'necesitaba', 'estoy necesitando',
            'estoy buscando', 'estamos buscando', 'ando buscando',
            'me puedes recomendar', 'me puede recomendar', 'me recomiendas', 'recomiendame',
            'recomiendan', 'recomienda',
            'me interesa', 'me gustaria', 'me ayudas a encontrar', 'ayudame a encontrar',
            'ayudame a', 'conoces algun', 'conoces alguna', 'conoces',
            'en que lugar venden', 'en que tienda venden',
            // —— Verbos de buscar ——
            'buscar', 'busca', 'busco', 'buscame', 'buscando', 'busquenme', 'buscar por',
            // —— Verbos de comprar ——
            'donde puedo comprar', 'donde comprar', 'donde compro', 'comprar', 'compro', 'comprame',
            // —— Preguntas de lugar ——
            'donde puedo encontrar', 'donde hay', 'donde encuentro', 'donde venden', 'donde queda',
            'donde estan', 'donde esta', 'donde',
            // —— Preguntas de persona ——
            'quienes venden', 'quienes tienen', 'quien vende', 'quien tiene', 'quien hace',
            'quien', 'quienes',
            // —— «alguien que…» y el subjuntivo («busco quién venda cemento» → cemento) ——
            'alguien que venda', 'alguien que vende', 'alguien que tenga', 'alguien que haga',
            'alguien que', 'alguien',
            'venda', 'vendan', 'vendas', 'tenga', 'tengan', 'haga', 'hagan', 'alquile', 'alquilen',
            // —— Preguntas con «que» ——
            'que venden', 'que vende', 'que tienen', 'que tiene', 'que hay',
            // —— Mandos de mostrar / de precio / de información ——
            'muestrame', 'muestra', 'ensename', 'dime', 'dame', 'ver',
            'tienes', 'tienen', 'venden', 'vende',
            'precios de', 'precio de', 'cuanto cuesta', 'a cuanto esta', 'cuanto',
            'informacion sobre', 'informacion de', 'informacion',
            'como llegar a', 'como llego a', 'opciones para', 'opciones de', 'opciones',
            'hay algunos', 'hay algunas', 'hay algun', 'hay alguna', 'hay',
            // —— Saludos con los que empiezan a pedir (solo al principio) ——
            'buenos dias', 'buenas tardes', 'buenas noches', 'buenas', 'hola', 'oye', 'oiga',
            'disculpa', 'disculpe', 'amigo', 'amiga',
        ];
    }
}

if (!function_exists('busqueda_limpieza_conectores')) {
    /**
     * 🔗 LOS CONECTORES: las palabras de enganche. Se quitan en CUALQUIER posición porque la búsqueda
     * es por palabras y exige que TODAS coincidan: un «de» dentro de «jugo de piña» obligaría a que
     * cada tienda tuviera la palabra «de» en su nombre.
     */
    function busqueda_limpieza_conectores() {
        return [
            'en', 'de', 'del', 'la', 'el', 'los', 'las', 'lo', 'un', 'una', 'unos', 'unas',
            'al', 'a', 'y', 'e', 'o', 'u', 'con', 'sin', 'para', 'por', 'mi', 'mis',
            'tu', 'tus', 'su', 'sus', 'que', 'es', 'son', 'esta', 'este', 'estos', 'estas',
            'me', 'te', 'se', 'le', 'les', 'nos',
            'hay', 'cerca', 'cerquita', 'favor', 'gracias', 'porfa', 'porfavor', 'pues',
            'oe', 'oee', 'ahi', 'alli', 'aca', 'aqui',
            'algo', 'algun', 'alguna', 'algunos', 'algunas',
        ];
    }
}

if (!function_exists('busqueda_limpieza_muletillas')) {
    /** 🙏 MULETILLAS del final: «… por favor», «… gracias», «… porfa». */
    function busqueda_limpieza_muletillas() {
        return ['favor', 'porfavor', 'porfa', 'gracias', 'pues', 'ya', 'oe', 'oee', 'papa', 'pe'];
    }
}

if (!function_exists('busqueda_limpieza_jerga')) {
    /**
     * 🗣️ LA JERGA DE LA CALLE (sinónimos): cómo pide la gente de Chimbote una cosa y con qué palabra
     * está escrita en las fichas del sitio.
     *
     * ⚠️ Es un SEGUNDO INTENTO, no un reemplazo: primero se busca lo que escribió el visitante y, SOLO
     * si eso no encontró nada, se reintenta con la palabra del sitio («puchitos» → «cigarrillos»). Así,
     * si algún día una tienda se llama «Los Puchitos», esa búsqueda la sigue encontrando.
     *
     * ⚠️ Esto es SOLO para el nombre de las fichas. Lo que traduce «producto → rubro» NO se escribe
     * aquí: vive en la base (tabla `directorio_categoria_claves`, las «frases de unión» del rubro) y lo
     * lee `categoria_por_clave_texto()`. No duplicar datos ahí.
     */
    function busqueda_limpieza_jerga() {
        return [
            // Cigarrillos: en la calle se piden «puchitos»
            'puchitos' => 'cigarrillos', 'puchito' => 'cigarrillos',
            'puchos'   => 'cigarrillos', 'pucho'   => 'cigarrillos',
            // Cerveza: «chelas»
            'chelas'   => 'cerveza', 'chela' => 'cerveza',
            // Celular y computadora
            'celu'     => 'celular', 'celus' => 'celular',
            'compu'    => 'computadora', 'compus' => 'computadora',
            // Farmacia: «botica»
            'boticas'  => 'farmacia', 'botica' => 'farmacia',
            // Refrigeradora
            'refri'    => 'refrigeradora',
        ];
    }
}

if (!function_exists('busqueda_limpieza_vacias')) {
    /**
     * 🧺 EL SACO DE LAS PALABRAS VACÍAS: todo lo que NO es parte de la búsqueda, en un solo mapa
     * (palabra => true) para poder preguntarlo en O(1).
     *
     * Son los conectores, las muletillas y TODAS las palabras de los mandos — la frase entera y cada
     * una de sus palabras sueltas: de «me puedes recomendar» entran «me puedes recomendar», «me»,
     * «puedes» y «recomendar». Así «hay alguna bodega **que venda** puchitos» pierde el «venda» aunque
     * vaya en medio de la frase.
     */
    function busqueda_limpieza_vacias() {
        static $saco = null;
        if ($saco !== null) return $saco;

        $saco = [];
        foreach (array_merge(busqueda_limpieza_conectores(), busqueda_limpieza_muletillas()) as $p) {
            $saco[busqueda_norm($p)] = true;
        }
        foreach (busqueda_limpieza_mandos() as $m) {
            $saco[busqueda_norm($m)] = true;
            foreach (preg_split('/\s+/u', busqueda_norm($m), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $w) {
                $saco[$w] = true;
            }
        }
        return $saco;
    }
}

if (!function_exists('busqueda_limpiar_info')) {
    /**
     * 🧹 Limpia lo que escribió el visitante y cuenta lo que hizo.
     *
     * Es UNA sola pasada, a propósito: se tira la puntuación y después toda palabra que esté en el saco
     * de las palabras vacías. No hacen falta vueltas ni reglas de posición — y como no hay reglas de
     * posición, no hay forma de que una frase rara («que venda» en medio) se cuele.
     *
     * @param string|int|null $texto
     * @return array{crudo:string,base:string,limpio:string,quitaron:string[],cambio:bool,vacio:bool}
     *         · crudo    → tal cual llegó (con signos).
     *         · base     → sin signos ni espacios de más.
     *         · limpio   → el término de verdad (sin mandos, conectores ni muletillas).
     *         · quitaron → las palabras que se dejaron fuera (para poder explicárselo al visitante).
     *         · cambio   → true si `limpio` es distinto de `base`.
     *         · vacio    → true si la búsqueda SOLO traía palabras de mando («comprar», «dónde hay»):
     *                      se busca el texto tal cual (nunca una búsqueda vacía), pero el visitante no
     *                      nombró NINGÚN producto, así que no se le debe llevar a un rubro. Antes,
     *                      «comprar» a secas terminaba mostrando una casa de cambio: la palabra
     *                      «compra» es clave de ese rubro.
     */
    function busqueda_limpiar_info($texto) {
        $crudo = trim(preg_replace('/\s+/u', ' ', (string)$texto));
        // Fuera signos (¿? ¡! . , ; : " ' ( ) « » - …), como hace el buscador por voz.
        $base  = trim(preg_replace('/\s+/u', ' ', (string)preg_replace('/[^\p{L}\p{N}]+/u', ' ', $crudo)));

        $salida = ['crudo' => $crudo, 'base' => $base, 'limpio' => $base, 'quitaron' => [],
                   'cambio' => false, 'vacio' => false];
        if ($base === '') return $salida;

        $vacias = busqueda_limpieza_vacias();
        $toks   = preg_split('/\s+/u', $base, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $toks   = array_values(array_filter($toks, function ($t) use ($vacias) {
            return !isset($vacias[busqueda_norm($t)]);
        }));

        $limpio = implode(' ', $toks);

        // ⚠️ RED DE SEGURIDAD: sin nada útil, se busca lo que escribió (nunca una búsqueda vacía).
        //    Es el caso de quien busca SOLO «comprar» o «dónde hay»: esas palabras no se pueden quitar
        //    porque entonces no quedaría nada que buscar.
        if (mb_strlen(preg_replace('/[^\p{L}\p{N}]/u', '', $limpio)) < 2) {
            $limpio = $base;
            $salida['vacio'] = true;   // no sobrevivió ninguna palabra propia: solo había mandos
        }

        $salida['limpio'] = $limpio;
        $salida['cambio'] = ($limpio !== $base);
        if ($salida['cambio']) {
            $quedan = array_map('busqueda_norm', preg_split('/\s+/u', $limpio, -1, PREG_SPLIT_NO_EMPTY) ?: []);
            foreach (preg_split('/\s+/u', $base, -1, PREG_SPLIT_NO_EMPTY) ?: [] as $t) {
                if (!in_array(busqueda_norm($t), $quedan, true)) $salida['quitaron'][] = $t;
            }
        }
        return $salida;
    }
}

if (!function_exists('busqueda_limpiar')) {
    /** El término de verdad de una búsqueda: «comprar clavos» → «clavos». */
    function busqueda_limpiar($texto) {
        $info = busqueda_limpiar_info($texto);
        return (string)$info['limpio'];
    }
}

if (!function_exists('busqueda_tokens')) {
    /**
     * Las palabras con las que se busca de verdad (2 letras o más, máximo 4: lo mismo que el
     * buscador del navegador y el de `api/sugerir.php`).
     */
    function busqueda_tokens($texto, $max = 4, $min = 2) {
        $t = preg_split('/[^\p{L}\p{N}]+/u', mb_strtolower((string)$texto, 'UTF-8'), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $t = array_values(array_filter($t, function ($x) use ($min) { return mb_strlen($x) >= $min; }));
        return array_slice($t, 0, (int)$max);
    }
}

if (!function_exists('busqueda_jerga_aplicar')) {
    /**
     * 🗣️ Traduce la jerga local al idioma de las fichas: «puchitos» → «cigarrillos».
     * Devuelve null si no había nada que traducir (o si la traducción deja el término vacío).
     *
     * @return array{texto:string,de:string[],a:string[]}|null
     */
    function busqueda_jerga_aplicar($texto) {
        $jerga = busqueda_limpieza_jerga();
        $toks  = preg_split('/\s+/u', trim((string)$texto), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (!$toks) return null;

        $de = []; $a = [];
        foreach ($toks as $i => $t) {
            $n = busqueda_norm($t);
            if (!isset($jerga[$n])) continue;
            $de[] = $t;
            $a[]  = $jerga[$n];
            $toks[$i] = $jerga[$n];
        }
        if (!$de) return null;

        return ['texto' => implode(' ', $toks), 'de' => array_values(array_unique($de)), 'a' => array_values(array_unique($a))];
    }
}

if (!function_exists('busqueda_diccionario_js')) {
    /**
     * El diccionario que se le pinta al navegador (lo imprime `includes/footer.php` como
     * `window.CHIMBOTE_LIMPIEZA`). **Una sola verdad**: el navegador limpia con esto mismo.
     */
    function busqueda_diccionario_js() {
        return [
            'mandos'     => busqueda_limpieza_mandos(),
            'conectores' => busqueda_limpieza_conectores(),
            'muletillas' => busqueda_limpieza_muletillas(),
            'jerga'      => busqueda_limpieza_jerga(),
        ];
    }
}
