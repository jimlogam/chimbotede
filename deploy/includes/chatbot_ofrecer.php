<?php
/**
 * includes/chatbot_ofrecer.php — 🎩 EL ANFITRIÓN (orden del jefe, 2026-09-13, noche)
 * =====================================================================================
 * Pedido textual del jefe:
 *   *«el chatbot es como un anfitrión que llama a la gente a visitar los negocios, pero lo hace de
 *   manera inteligente e indirecta. Por ejemplo si alguien dice que hace mucho calor o el día está
 *   muy soleado, el chatbot le debe mostrar los proveedores de helados o tiendas de jugos, con frases
 *   como "es el momento perfecto para refrescarte con una bebida helada, mira estos negocios"»*.
 *   Y con las fotos: *«tomé una foto de un letrero de un negocio llamado COMPUME que decía reparación
 *   de laptops y formateos… el bot vio la imagen y dijo eso mismo, pero no ofreció ningún negocio
 *   relacionado a productos de informática o técnicos»*.
 *
 * Cómo funciona (TODO LOCAL: no gasta ni un token de DeepSeek, reutiliza el buscador del chat):
 *   1. `chatbot_ofrecer_momento($mensaje)` — ¿el visitante está contando un MOMENTO? (calor, frío,
 *      hambre, un cumpleaños, el carro que falla, la laptop que no prende, la mascota enferma…).
 *      Cada momento tiene **su frase de anfitrión** y **sus términos de búsqueda** (probados contra la
 *      base: están los que de verdad encuentran negocios).
 *   2. `chatbot_ofrecer_palabras_de_texto($texto)` — cuando el visitante **manda una foto**, la
 *      respuesta del modelo ya nombra lo que se ve («reparación de laptops y formateos»): de ahí se
 *      sacan las palabras que **son frases de unión del directorio** («laptop», «accesorios»,
 *      «computadora»), que valen más que el resto del texto.
 *   3. `chatbot_ofrecer_bloque(...)` junta los negocios (**hasta 3 términos, sin repetir, tope 3
 *      tiendas + 1 producto, con su foto enlazada a la ficha**) y `chatbot_ofrecer_aplicar()` pega el
 *      bloque al final de la respuesta que va a ver el visitante.
 *
 * ⛔ CUÁNDO NO SE OFRECE NADA (un buen anfitrión no es un pesado):
 *   · Si la respuesta **ya trae negocios** (una búsqueda: `fuente: busqueda`).
 *   · Si en **esta conversación** ya se ofrecieron negocios (el historial trae `/neg/`).
 *   · Si el chat está **bloqueado** (cuota, límites, avisos) o si la respuesta es un error.
 *   · Si el rubro que salió tiene **menos de 2 negocios** (no se ofrece una página casi vacía).
 *   · Si el momento no se detecta **y** no vino ninguna foto.
 *
 * 📝 PARA AÑADIR UN MOMENTO NUEVO: se añade una fila a `chatbot_ofrecer_momentos()` con sus palabras
 *    (⚠️ **SIN TILDES**: el mensaje se limpia antes) y sus términos de búsqueda, y ya está. Los
 *    términos se prueban con `__diag_anfitrion.php` / `__diag_tezminos.php` (imprimen cuántos negocios
 *    encuentra cada uno) para no dejar momentos que no muestren nada.
 */

if (!function_exists('chatbot_ofrecer_momentos')) {
    /**
     * LOS MOMENTOS DEL ANFITRIÓN: lo que la gente cuenta sin pedir nada, y lo que el sitio tiene para
     * ese momento.
     *   · `palabras` = lo que delata el momento (se buscan como **palabra completa**, sin tildes).
     *   · `busca`    = términos que se le pasan al buscador del chat (se juntan hasta 3 resultados).
     *   · `frase`    = cómo lo dice el anfitrión (la idea del jefe: indirecto, con chispa, sin vender).
     * ⚠️ Los términos están elegidos con la base delante (2026-09-13): 'jugo', 'raspadilla' o 'formateo'
     *    no devuelven nada y por eso NO están; 'jugueria', 'heladeria', 'hielo', 'laptop' y 'tecnico' sí.
     */
    function chatbot_ofrecer_momentos() {
        return [
            // ☀️ El ejemplo del jefe, palabra por palabra. («Heladerías y Juguerías» existe como rubro
            //    pero todavía no tiene tiendas propias: lo que sí hay son heladerías y juguerías con
            //    ficha —HELADERIA UCV, Juguería Anita— y un repartidor de hielo.)
            'calor' => [
                'palabras' => ['calor', 'caluroso', 'soleado', 'sol', 'verano', 'bochorno', 'sofocante',
                               'sudando', 'sudor', 'playa', 'piscina', 'quema', 'helado', 'helados',
                               'jugo', 'jugos', 'refrescar', 'refrescante'],
                'frase'    => 'Es el momento perfecto para refrescarte con algo bien helado 🥤 Mira estos negocios:',
                'busca'    => ['heladeria', 'jugueria', 'hielo', 'gaseosa'],
            ],
            // 🥶 El otro lado del clima.
            'frio' => [
                'palabras' => ['frio', 'fria', 'friaje', 'helada', 'llueve', 'llueve', 'lloviendo',
                               'llover', 'lluvia', 'llovizna', 'garua', 'invierno'],
                'frase'    => 'Con este frío, algo calientito cae de maravilla ☕ Mira por dónde:',
                'busca'    => ['cafe', 'pan', 'sopa'],
            ],
            // 🍽️ Hambre y antojos.
            'hambre' => [
                'palabras' => ['hambre', 'almuerzo', 'almorzar', 'cena', 'cenar', 'comer', 'desayuno',
                               'antojo', 'antoja', 'comida', 'pizza', 'parrilla'],
                'frase'    => 'Y ya que estamos, aquí se come rico 🍽️ Mira estos lugares:',
                'busca'    => ['pollo', 'menu', 'chifa', 'parrilla'],
            ],
            // 🎂 Festejos.
            'fiesta' => [
                'palabras' => ['cumpleanos', 'cumple', 'fiesta', 'celebracion', 'celebramos', 'bautizo',
                               'aniversario', 'promocion', 'matrimonio', 'boda', 'graduacion'],
                'frase'    => '¿Y si lo celebramos bien? Aquí tienes tortas, globos y flores 🎂',
                'busca'    => ['torta', 'globos', 'flores', 'catering'],
            ],
            // 🚗 El carro o la moto.
            'vehiculo' => [
                'palabras' => ['auto', 'carro', 'moto', 'mototaxi', 'llanta', 'pinchazo', 'taller',
                               'mecanico', 'aceite', 'motor', 'frenos', 'combustible'],
                'frase'    => 'Para eso, estos talleres y llanterías te sacan del apuro 🔧',
                'busca'    => ['mecanico', 'llanta', 'reparacion de auto'],
            ],
            // 💻 La compu o el celular (el caso de la foto de COMPUME).
            'tecnologia' => [
                'palabras' => ['laptop', 'laptops', 'computadora', 'computador', 'compu', 'pc', 'celular',
                               'formateo', 'formatear', 'impresora', 'teclado', 'pantalla', 'software',
                               'internet', 'wifi', 'virus', 'tecnico'],
                'frase'    => 'Justo en Chimbote hay negocios de informática que hacen eso 💻 Mira:',
                'busca'    => ['laptop', 'tecnico', 'computadora', 'celular'],
            ],
            // 🐶 La mascota.
            'mascota' => [
                'palabras' => ['perro', 'gato', 'mascota', 'cachorro', 'gata', 'gatito', 'veterinaria'],
                'frase'    => 'Por si te sirve, aquí cuidan bien a los engreídos 🐾',
                'busca'    => ['veterinaria', 'mascota'],
            ],
            // 💊 El malestar.
            'salud' => [
                'palabras' => ['enfermo', 'enferma', 'dolor', 'fiebre', 'gripe', 'tos', 'resfrio',
                               'malestar', 'mareo', 'duele', 'muela', 'diente'],
                'frase'    => 'Si andas con ese malestar, esto te queda a mano 💊',
                'busca'    => ['farmacia', 'medicamento'],
            ],
            // 🔑 La llave o la cerradura.
            'llave' => [
                'palabras' => ['llave', 'cerradura', 'candado', 'chapa', 'cerrajero', 'duplicado'],
                'frase'    => '¿Perdiste la llave o se trabó la chapa? Aquí te sacan del apuro 🔑',
                'busca'    => ['cerrajero'],
            ],
            // 🧹 La casa.
            'casa' => [
                'palabras' => ['limpieza', 'limpiar', 'fumigacion', 'fumigar', 'mudanza', 'mudar',
                               'cucarachas', 'ratones'],
                'frase'    => 'Para dejar la casa impecable, mira esto 🧹',
                'busca'    => ['limpieza', 'fumigacion'],
            ],
            // ✈️ El viaje.
            'viaje' => [
                'palabras' => ['viaje', 'viajar', 'vacaciones', 'hospedaje', 'hotel', 'hostal',
                               'alojamiento', 'alojarme', 'descansar'],
                'frase'    => 'Si andas de viaje, aquí puedes quedarte tranquilo 🛏️',
                'busca'    => ['hotel', 'hostal'],
            ],
            // 👓 La vista.
            'vista' => [
                'palabras' => ['lentes', 'anteojos', 'gafas', 'optica', 'vista'],
                'frase'    => 'Para la vista, aquí te hacen la medida 👓',
                'busca'    => ['lentes', 'anteojos', 'optica'],
            ],
        ];
    }
}

if (!function_exists('chatbot_ofrecer_momento')) {
    /**
     * ¿Qué MOMENTO está contando el visitante? Devuelve la fila del momento (con su clave) o null.
     * Se busca por **palabra completa** y sin tildes: «hace mucho calor» → calor; «el sol está fuerte»
     * → calor; «solo quería saber» **no** es calor («solo» ≠ «sol»).
     */
    function chatbot_ofrecer_momento($mensaje) {
        $t = chatbot_sin_tildes(mb_strtolower((string)$mensaje, 'UTF-8'));
        $t = ' ' . trim((string)preg_replace('/[^a-z0-9]+/', ' ', $t)) . ' ';
        if (trim($t) === '') return null;
        foreach (chatbot_ofrecer_momentos() as $clave => $m) {
            foreach ((array)$m['palabras'] as $p) {
                if (mb_strpos($t, ' ' . $p . ' ') !== false) return ['clave' => $clave] + $m;
            }
        }
        return null;
    }
}

if (!function_exists('chatbot_ofrecer_palabras_de_texto')) {
    /**
     * 🔤 Las palabras «que valen» de un texto libre (la respuesta del modelo a una FOTO).
     * Se queda con las que **son frases de unión del directorio** (`directorio_categoria_claves`), que
     * son justo las que llevan a un negocio: de «…reparación de laptops y formateos, venta de
     * accesorios y mantenimiento de computadoras…» salen **laptop, accesorios, computadora** (y no
     * «letrero», «negocio» ni «venta», que no llevan a ninguna parte).
     * Orden: primero las que coinciden EXACTAS con una frase de unión, después las más largas.
     */
    function chatbot_ofrecer_palabras_de_texto($texto) {
        if (!function_exists('chatbot_busqueda_claves')) return [];
        $t = chatbot_sin_tildes(mb_strtolower((string)$texto, 'UTF-8'));
        $t = (string)preg_replace('/[^a-z0-9\s]+/', ' ', $t);

        $claves = chatbot_busqueda_claves();           // categoria_id => [clave, ...]
        $todas  = [];
        foreach ($claves as $lista) { foreach ((array)$lista as $cl) $todas[$cl] = true; }

        $out = [];
        foreach (preg_split('/\s+/', trim($t), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $p) {
            if (mb_strlen($p) < 4) continue;
            if (in_array($p, chatbot_busqueda_ruido(), true)) continue;
            // Raíz: «laptops» → «laptop», «computadoras» → «computadora»
            if (mb_strlen($p) > 5 && mb_substr($p, -2) === 'es') $p = mb_substr($p, 0, -2);
            elseif (mb_strlen($p) > 4 && mb_substr($p, -1) === 's') $p = mb_substr($p, 0, -1);
            if (mb_strlen($p) < 4) continue;

            $puntos = 0;
            if (isset($todas[$p])) {
                $puntos = 3;                                       // frase de unión exacta
            } else {
                foreach ($todas as $cl => $x) {
                    if (mb_strlen($p) >= 5 && mb_strpos($cl, $p) !== false) { $puntos = 2; break; }
                }
            }
            if ($puntos > 0) $out[$p] = max($puntos, $out[$p] ?? 0);
        }
        // Orden: primero las exactas (3), después las contenidas (2); a igual puntaje, la más larga.
        $lista = array_keys($out);
        usort($lista, function ($a, $b) use ($out) {
            if ($out[$a] !== $out[$b]) return $out[$b] - $out[$a];
            return mb_strlen($b) - mb_strlen($a);
        });
        return array_slice($lista, 0, 6);
    }
}

if (!function_exists('chatbot_ofrecer_es_clave_del_rubro')) {
    /**
     * ¿Esta palabra es una «frase de unión» de ESE rubro? Sirve para quedarse solo con las palabras de
     * la foto que son del rubro que salió: en un letrero de laptops, «computadora», «laptop»,
     * «impresora» y «accesorio» son de Informática; «letrero» (que es de otro rubro) se queda fuera.
     */
    function chatbot_ofrecer_es_clave_del_rubro($palabra, $rubro_id) {
        // La comprobación vive en el buscador (`chatbot_busqueda_es_clave_de()`) para no tener dos
        // criterios distintos: aquí solo se reutiliza.
        if (function_exists('chatbot_busqueda_es_clave_de')) {
            return chatbot_busqueda_es_clave_de($palabra, $rubro_id);
        }
        if (!function_exists('chatbot_busqueda_claves')) return false;
        $lista = chatbot_busqueda_claves()[(int)$rubro_id] ?? [];
        $p = (string)$palabra;
        foreach ((array)$lista as $cl) {
            if ($cl === $p) return true;
            if (mb_strlen($p) >= 5 && mb_strpos($cl, $p) !== false) return true;
            if (mb_strlen($cl) >= 5 && mb_strpos($p, $cl) !== false) return true;
        }
        return false;
    }
}

if (!function_exists('chatbot_ofrecer_rubro_de_texto')) {
    /**
     * 🏷️ El rubro del directorio a partir de un texto libre (la respuesta del modelo a una FOTO).
     * Es lo que hace que una foto sirva para ofrecer negocios: «reparación de laptops y formateos» →
     * **Informática / Celulares**.
     */
    function chatbot_ofrecer_rubro_de_texto($texto) {
        if (!function_exists('chatbot_busqueda_rubro') || !function_exists('db')) return null;
        $palabras = chatbot_ofrecer_palabras_de_texto($texto);
        if (!$palabras) {
            // Sin frases de unión conocidas se prueba con todas las palabras «serias» del texto.
            $t = chatbot_sin_tildes(mb_strtolower((string)$texto, 'UTF-8'));
            $t = (string)preg_replace('/[^a-z0-9\s]+/', ' ', $t);
            foreach (preg_split('/\s+/', trim($t), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $p) {
                if (mb_strlen($p) < 5) continue;
                if (in_array($p, chatbot_busqueda_ruido(), true)) continue;
                $palabras[] = $p;
                if (count($palabras) >= 10) break;
            }
        }
        if (!$palabras) return null;
        try {
            return chatbot_busqueda_rubro(db(), $palabras) ?: null;
        } catch (Throwable $e) {
            return null;
        }
    }
}

if (!function_exists('chatbot_ofrecer_tiendas_rubro')) {
    /** Cuántos negocios activos tiene un rubro (para no ofrecer rubros casi vacíos). */
    function chatbot_ofrecer_tiendas_rubro($rubro_id) {
        try {
            if (!function_exists('rubro_filtro_id')) return 0;
            [$cf, $cp] = rubro_filtro_id((int)$rubro_id, 'n');
            $st = db()->prepare("SELECT COUNT(*) FROM directorio_negocios n WHERE n.estado = 'activo' AND $cf");
            $st->execute($cp);
            return (int)$st->fetchColumn();
        } catch (Throwable $e) { return 0; }
    }
}

if (!function_exists('chatbot_ofrecer_ya_ofrecio')) {
    /**
     * ¿Ya se le ofrecieron negocios en esta conversación? Se mira el historial (lo que manda el
     * navegador): si ya hay un enlace a una ficha (`/neg/`), no se vuelve a ofrecer. Es la regla que
     * evita que el anfitrión se vuelva pesado.
     */
    function chatbot_ofrecer_ya_ofrecio($historial) {
        foreach (chatbot_historial_limpiar($historial) as $m) {
            if (mb_strpos((string)$m['content'], '/neg/') !== false) return true;
        }
        return false;
    }
}

if (!function_exists('chatbot_ofrecer_bloque')) {
    /**
     * 🎩 Arma el bloque del anfitrión (o devuelve `texto` vacío si no toca ofrecer nada).
     * Junta hasta **3 términos** (para que la lista no salga con un solo negocio) sin repetir fichas,
     * con tope de **3 tiendas + 1 producto**.
     * @return array{texto:string, termino:string, motivo:string, negocios:int}
     */
    function chatbot_ofrecer_bloque($mensaje, $respuesta_ia, $historial = [], $con_imagen = false, $lat = null, $lng = null) {
        $out = ['texto' => '', 'termino' => '', 'motivo' => '', 'negocios' => 0];
        if (!function_exists('chatbot_buscar') || !function_exists('chatbot_buscar_tiendas')) return $out;

        // ⛔ Nunca dos veces en la misma conversación, ni encima de una respuesta que ya trae negocios.
        if (mb_strpos((string)$respuesta_ia, '/neg/') !== false) return $out;
        if (!empty($historial) && chatbot_ofrecer_ya_ofrecio($historial)) return $out;

        $geo = ($lat !== null && $lng !== null && is_numeric($lat) && is_numeric($lng));

        // ---- 1) ¿Hay MOMENTO? (calor, hambre, cumpleaños, la laptop, el carro…) ----
        $momento = chatbot_ofrecer_momento($mensaje);

        // ---- 2) ¿O es una FOTO cuyo texto nombra cosas del directorio? ----
        $rubro    = null;
        $terminos = [];
        if ($momento) {
            $terminos = (array)$momento['busca'];
        } elseif ($con_imagen) {
            $texto    = trim((string)$mensaje . ' ' . (string)$respuesta_ia);
            $terminos = chatbot_ofrecer_palabras_de_texto($texto);
            $rubro    = chatbot_ofrecer_rubro_de_texto($texto);
            if ($rubro && chatbot_ofrecer_tiendas_rubro($rubro['id']) < 2) $rubro = null;
            if (!$terminos && !$rubro) return $out;
        } else {
            return $out;
        }

        // ---- 3) Se juntan los negocios y se ORDENAN por lo que de verdad pega ----
        // · Se buscan los términos (2 fichas de cada uno) y, si hay rubro (caso de la foto), también
        //   sus negocios.
        // · Después se PUNTÚA cada ficha por las palabras que lleva su NOMBRE: así, con un letrero de
        //   «reparación de laptops», va delante **Técnico Germán Computadoras** y no una tienda de
        //   webcams que también es del rubro Informática.
        $candidatos = [];
        $productos  = [];
        $usados     = [];
        $primer     = '';
        try {
            $pdo = db();

            // En la FOTO se prueban más términos (5) que en un momento (3): una foto es rara (hay cupo
            // diario) y así entran «laptop», «accesorio», «impresora»… Todos los que son del rubro.
            if ($rubro && !$momento) {
                $del_rubro = [];
                foreach ($terminos as $tk) {
                    if (chatbot_ofrecer_es_clave_del_rubro($tk, (int)$rubro['id'])) $del_rubro[] = $tk;
                }
                if ($del_rubro) $terminos = $del_rubro;
            }
            $tope_terminos = $momento ? 3 : 5;

            foreach ($terminos as $term) {
                if (count($usados) >= $tope_terminos) break;
                $usados[] = (string)$term;
                $b = chatbot_buscar($term, 4, $lat, $lng);
                if (empty($b['tiendas']) && empty($b['productos'])) continue;
                if ($primer === '') $primer = (string)$term;
                $n = 0;
                foreach ((array)$b['tiendas'] as $t) {
                    $id = (int)$t['id'];
                    if (!isset($candidatos[$id])) $candidatos[$id] = $t;
                    if (++$n >= 2) break;                      // 2 por término: variedad sin llenar de uno solo
                }
                if (!$productos && !empty($b['productos'])) $productos[] = $b['productos'][0];
            }

            // 🛟 Y los negocios del rubro (es el caso de la foto: Informática / Celulares), por si los
            //    términos no alcanzaron.
            if ($rubro) {
                $t = chatbot_buscar_tiendas($pdo, [], (int)$rubro['id'], $geo, $lat, $lng, 4);
                $n = 0;
                foreach ((array)$t['tiendas'] as $fila) {
                    $id = (int)$fila['id'];
                    if (!isset($candidatos[$id])) $candidatos[$id] = $fila;
                    if (++$n >= 4) break;
                }
                if (!$productos && function_exists('chatbot_buscar_productos')) {
                    $pr = chatbot_buscar_productos($pdo, [], (int)$rubro['id'], 2);
                    if ($pr) $productos[] = $pr[0];
                }
            }
        } catch (Throwable $e) {
            return $out;
        }
        if (!$candidatos && !$productos) return $out;

        // Puntaje por NOMBRE (y orden de aparición como desempate: el buscador ya los trae por afinidad).
        $orden = 0;
        foreach ($candidatos as $id => $fila) {
            $nombre = function_exists('chatbot_sin_tildes')
                ? chatbot_sin_tildes(mb_strtolower((string)$fila['nombre'], 'UTF-8'))
                : mb_strtolower((string)$fila['nombre'], 'UTF-8');
            $puntos = 0;
            foreach ($usados as $tk) {
                if ($tk !== '' && mb_strpos($nombre, (string)$tk) !== false) $puntos++;
            }
            $candidatos[$id]['_puntos'] = $puntos;
            $candidatos[$id]['_orden']  = $orden++;
        }
        uasort($candidatos, function ($a, $b) {
            if ($a['_puntos'] !== $b['_puntos']) return $b['_puntos'] - $a['_puntos'];
            if ($a['_orden']  !== $b['_orden'])  return $a['_orden'] - $b['_orden'];
            return (int)($b['vistas_count'] ?? 0) - (int)($a['vistas_count'] ?? 0);
        });
        $tiendas = array_slice(array_values($candidatos), 0, 3);

        // ---- 4) La frase del anfitrión ----
        if ($momento) {
            $frase  = (string)$momento['frase'];
            $motivo = 'momento:' . $momento['clave'] . ($primer !== '' ? ':' . $primer : '');
            if ($primer !== '') $termino = $primer;
        } else {
            $nombre = $rubro ? mb_strtolower((string)$rubro['nombre'], 'UTF-8') : '';
            $frase  = $nombre !== ''
                ? 'De paso: en Chimbote tenemos negocios de ' . $nombre . ' — mira 👇'
                : 'De paso: mira estos negocios por si te sirven 👇';
            $motivo  = 'foto:' . ($rubro ? 'rubro-' . (int)$rubro['id'] : 'palabras');
            $termino = $primer !== '' ? $primer : ($rubro['nombre'] ?? '');
        }

        // ---- 5) El bloque corto: la frase + 3 negocios con su foto (enlazada a su ficha) ----
        $L = [$frase];
        foreach ($tiendas as $t) {
            $url  = 'https://dechimbote.com/neg/' . rawurlencode((string)$t['slug']);
            $foto = function_exists('chatbot_busqueda_foto') ? chatbot_busqueda_foto($t) : '';
            $cola = trim((string)($t['rubro'] ?? ''));
            if (isset($t['distancia_m']) && function_exists('distancia_txt')) {
                $cola = '📍 ' . distancia_txt((float)$t['distancia_m']) . ($cola !== '' ? ' · ' . $cola : '');
            }
            $img = $foto !== '' ? '[![' . $t['nombre'] . '](' . $foto . ')](' . $url . ') ' : '';
            $L[] = '- ' . $img . '[**' . $t['nombre'] . '**](' . $url . ')' . ($cola !== '' ? ' — ' . $cola : '');
        }
        foreach ($productos as $p) {
            $url  = 'https://dechimbote.com/neg/' . rawurlencode((string)$p['tienda_slug']);
            $foto = function_exists('chatbot_busqueda_foto') ? chatbot_busqueda_foto($p) : '';
            $img  = $foto !== '' ? '[![' . $p['titulo'] . '](' . $foto . ')](' . $url . ') ' : '';
            $cola = ((float)($p['precio'] ?? 0) > 0 ? '**S/ ' . number_format((float)$p['precio'], 2) . '** · ' : '') .
                    '[' . $p['tienda'] . '](' . $url . ')';
            $L[] = '- ' . $img . '[**' . $p['titulo'] . '**](' . $url . ') — ' . $cola;
        }

        $out['texto']    = implode("\n", $L);
        $out['termino']  = (string)$termino;
        $out['motivo']   = $motivo;
        $out['negocios'] = count($tiendas) + count($productos);
        return $out;
    }
}

if (!function_exists('chatbot_ofrecer_aplicar')) {
    /**
     * 🎩 Pega el bloque del anfitrión al final de la respuesta que ya se iba a mandar.
     * Lo llama `chatbot_responder()` (el envoltorio del final de `chatbot.php`), así que vale para
     * CUALQUIER camino de respuesta: la IA, el clima del día, el guion local… (menos la búsqueda, que
     * ya trae negocios, y los caminos bloqueados).
     */
    function chatbot_ofrecer_aplicar($r, $mensaje, $historial = [], $con_imagen = false, $lat = null, $lng = null) {
        if (!is_array($r) || empty($r['ok']) || empty($r['respuesta'])) return $r;
        if (in_array((string)($r['fuente'] ?? ''), ['busqueda', 'cuota', 'limite', 'aviso'], true)) return $r;
        if (!empty($r['bloqueado']) || !empty($r['requiere_login'])) return $r;

        try {
            $bloque = chatbot_ofrecer_bloque($mensaje, (string)$r['respuesta'], $historial, $con_imagen, $lat, $lng);
        } catch (Throwable $e) {
            return $r;   // el anfitrión NUNCA puede romper el chat
        }
        if ($bloque['texto'] === '') return $r;

        $r['respuesta'] = rtrim((string)$r['respuesta']) . "\n\n" . $bloque['texto'];
        $r['ofrecio']   = $bloque['motivo'];

        // 📍 La opción de la ubicación, con el término que se acaba de ofrecer (así el visitante puede
        // pedir «las más cercanas» sin escribir nada).
        if ($bloque['termino'] !== '' && function_exists('chatbot_accion_cerca')) {
            $r['sugerencias'] = chatbot_accion_cerca(
                $bloque['termino'],
                ['geo' => ($lat !== null && $lng !== null)],
                $r['sugerencias'] ?? []
            );
        }
        return $r;
    }
}
