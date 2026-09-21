<?php
/**
 * includes/supremo.php — 👑 «EL SUPREMO»: EL MOTOR DE LA VENTA EN VIVO
 * =====================================================================
 * Qué hace: maneja la conversación de `/supremo`, la página **privada del Súper
 * Administrador** para crear tiendas **delante del cliente** (modo vendedor).
 *
 * 🔴 LAS TRES DIFERENCIAS CON 🛠️ EL MAESTRO (que es su hermano, no su copia):
 *   1. **Solo entra el administrador** (la puerta lo comprueba en `api/supremo.php`, y el
 *      motor vuelve a comprobarlo aquí: nunca se confía en el navegador).
 *   2. **Al terminar de armar la tienda le entrega LOS CÓDIGOS YA ESCRITOS** con los datos
 *      reales de esa tienda: la cabecera (para Gemini/Flow con imagen ingrediente), la
 *      música (para el generador de música de Flow) y las imágenes de los productos.
 *   3. **La IA inventa 3 productos** con el contexto de la tienda; se crean de verdad y el
 *      código pide sus fotos **con el ID impreso dentro de cada imagen**, que es lo que nos
 *      permite publicarlas solas cuando llegan.
 *
 * Lo que NO se duplica: la lectura de las fotos con visión, los rubros reales del directorio,
 * la publicación de la tienda (con su slug, su cuenta y su canción), el WebP y el copywriting
 * son **las funciones de `includes/tienda_ia.php`**, tal cual. Si el jefe cambia El maestro,
 * lo bueno también mejora aquí.
 *
 * Las sesiones viven en la MISMA tabla (`directorio_ia_tiendas`) con `modo = 'supremo'`:
 * así el consumo de la IA se mide con el mismo medidor (la pestaña 🛠️ del panel filtra
 * las del Supremo para que cada módulo tenga sus números limpios).
 *
 * Guía: GUIA_EL_SUPREMO.md
 */

require_once __DIR__ . '/config_supremo.php';
require_once __DIR__ . '/tienda_ia.php';

/* =====================================================================================
 * 1) LOS PASOS DEL SUPREMO (el orden lo manda el servidor, como en El maestro)
 * ===================================================================================== */

if (!function_exists('supremo_pasos')) {
    function supremo_pasos() {
        return [
            // --- 🏪 ARMAR LA TIENDA (los mismos datos que El maestro, más rápido) ---
            'arranque'      => ['n' => 0,  'titulo' => 'Empezar la venta'],
            'ayuda'         => ['n' => 0,  'titulo' => 'Cómo se usa'],
            // 🔴 PRIMERO LA UBICACIÓN (orden del jefe, 2026-09-18: *«lo primero que debe pedir es la
            //    ubicación, no importa en qué circunstancias, siempre se debe saber la ubicación desde
            //    donde se creó la tienda; después de la ubicación vienen las imágenes»*).
            'ubicacion'     => ['n' => 1,  'titulo' => 'La ubicación'],
            'fotos'         => ['n' => 2,  'titulo' => 'Las fotos del negocio'],
            'nombre_ok'     => ['n' => 3,  'titulo' => 'El nombre'],
            'rubro_ok'      => ['n' => 4,  'titulo' => 'El rubro'],
            'rubro_mas'     => ['n' => 5,  'titulo' => 'Otros rubros'],
            'tipo'          => ['n' => 6,  'titulo' => 'Cómo atiende'],
            'direccion'     => ['n' => 7,  'titulo' => 'La dirección'],
            'horario'       => ['n' => 8,  'titulo' => 'El horario'],
            'entregas'      => ['n' => 8,  'titulo' => 'Los distritos de entrega'],
            'distrito'      => ['n' => 8,  'titulo' => 'Cambiar la zona'],   // se puede tocar en cualquier momento
            'cliente'       => ['n' => 9,  'titulo' => 'El WhatsApp del cliente'],
            // --- 👑 LO QUE SOLO HACE EL SUPREMO ---
            'sup_portada'        => ['n' => 10, 'titulo' => 'La portada'],
            'sup_portada_fotos'  => ['n' => 10, 'titulo' => 'Elegir la portada'],
            'sup_productos'      => ['n' => 11, 'titulo' => 'Los 8 productos'],
            'sup_ia_elegir'      => ['n' => 11, 'titulo' => 'Las imágenes de los productos'],
            'sup_codigos'        => ['n' => 12, 'titulo' => 'Los códigos'],
            'sup_imagenes'       => ['n' => 13, 'titulo' => 'Las imágenes'],
            'sup_cancion'        => ['n' => 14, 'titulo' => 'La canción'],
            'sup_piensa'         => ['n' => 15, 'titulo' => 'Piensa mejor'],
            'sup_oferta'         => ['n' => 16, 'titulo' => 'La oferta'],
            'sup_fin'            => ['n' => 17, 'titulo' => 'Venta cerrada'],
        ];
    }
}

if (!function_exists('supremo_progreso')) {
    /** Cuánto lleva (0 a 1) para la barrita de arriba. */
    function supremo_progreso($paso, array $datos) {
        $pasos = supremo_pasos();
        $n = isset($pasos[$paso]) ? (int)$pasos[$paso]['n'] : 0;
        return max(0.04, min(1, $n / 17));
    }
}

/* =====================================================================================
 * 2) LOS DATOS DE LA SESIÓN
 * ===================================================================================== */

if (!function_exists('supremo_datos_vacios')) {
    /**
     * Los datos de una venta. Se apoya en `tienda_ia_datos_vacios()` (es un superconjunto: así
     * la publicación y el copywriting —que son los de El maestro— encuentran todo lo que buscan)
     * y le suma lo propio del Supremo, todo con el prefijo `sup_`.
     */
    function supremo_datos_vacios() {
        $d = tienda_ia_datos_vacios();
        $d['modo'] = 'supremo';
        // 🏪 Lo que el Supremo aprende en sus propios pasos
        $d['sup_inicio']    = 0;      // ⏱️ cuándo empezó la venta (para el cronómetro)
        $d['sup_fin']       = 0;      // ⏱️ cuándo se cerró
        $d['sup_portada']   = '';     // 'ia' | 'foto' | 'subida' | 'despues'
        $d['sup_elegidos']  = [];     // 🛍️ los productos que el vendedor SÍ quiso incluir
        $d['sup_inventados'] = [];    // 🧠 los 3 que inventó la IA: titulo, precio, frase, plano
        $d['sup_creados']   = [];     // los productos que ya quedaron en la tienda: id, titulo, con_foto
        $d['sup_con_ia']    = [];     // 🖼️ los ids de producto que llevan imagen hecha por la IA
        $d['sup_imagenes']  = [];     // 📥 lo que ya llegó a la sala de espera: id, que, archivo
        $d['sup_clave']     = '';     // 🔑 la clave del cliente (se muestra una sola vez)
        $d['sup_usuario']   = '';     // 👤 su usuario (su WhatsApp)
        $d['sup_negocio']   = null;   // ['id','slug','url','nombre'] de la tienda publicada
        $d['sup_veces']     = 0;      // cuántas veces pidió que la IA lo piense otra vez
        // 🔁 A dónde se vuelve cuando un dato se toca fuera del flujo (menú ⋯ y 🧠 «Piensa mejor»).
        $d['sup_volver']       = '';
        // 🧠 «Piensa mejor»: qué dijo la IA la última vez y qué quedó pendiente por poner.
        $d['sup_piensa_hecha'] = 0;
        $d['sup_piensa_nota']  = '';
        $d['sup_piensa_fix']   = [];   // ['descripcion'=>bool,'productos'=>[…]]
        $d['sup_piensa_volver'] = '';  // a qué paso vuelve «seguir» (lo pone el botón 🧠 de la cabecera)
        // ✅ EL NOMBRE, DE UNO EN UNO (orden del jefe, 2026-09-18): la IA propone un nombre y se
        //    pregunta con botones; si el cliente dice NO, se propone el siguiente de la cola.
        $d['sup_nombres']      = [];   // los nombres propuestos que todavía no se han mostrado
        $d['sup_nombre_prop']  = '';   // el que está propuesto AHORA
        $d['sup_nombre_visto'] = 0;    // cuántos se han propuesto (0 = todavía sin «Escribir»)
        return $d;
    }
}

if (!function_exists('supremo_actual')) {
    /**
     * La venta EN CURSO de este administrador (o una nueva). Usa el mismo motor de sesiones de
     * El maestro con `modo = 'supremo'`: así el avance, el chat y el consumo se guardan solos.
     */
    function supremo_actual($admin_id, $crear = true) {
        $s = tienda_ia_actual((int)$admin_id, 'supremo', '', $crear);
        if (is_array($s) && !empty($s['datos'])) {
            $s['datos'] = array_merge(supremo_datos_vacios(), (array)$s['datos']);
        }
        return $s;
    }
}

if (!function_exists('supremo_negocio_id')) {
    /** El id de la tienda que se publicó en ESTA venta (0 si todavía no). */
    function supremo_negocio_id(array $d) {
        return (int)($d['publicado']['negocio_id'] ?? $d['sup_negocio']['id'] ?? $d['negocio_id'] ?? 0);
    }
}

if (!function_exists('supremo_segundos')) {
    /** ⏱️ Cuántos segundos lleva la venta (0 si todavía no empezó). */
    function supremo_segundos(array $d) {
        $ini = (int)($d['sup_inicio'] ?? 0);
        if ($ini <= 0) return 0;
        $fin = (int)($d['sup_fin'] ?? 0);
        return max(0, ($fin > 0 ? $fin : time()) - $ini);
    }
}

/* =====================================================================================
 * 3) LEER LA TIENDA PUBLICADA (sus fotos y sus productos)
 * ===================================================================================== */

if (!function_exists('supremo_fotos_negocio')) {
    /** Las fotos de la tienda, en orden (la primera es la portada). */
    function supremo_fotos_negocio($negocio_id) {
        $out = [];
        try {
            $st = db()->prepare("SELECT id, ruta, descripcion, orden FROM directorio_fotos
                                  WHERE negocio_id = ? ORDER BY orden ASC, id ASC");
            $st->execute([(int)$negocio_id]);
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $f) {
                $out[] = ['id' => (int)$f['id'], 'ruta' => (string)$f['ruta'],
                          'url' => img_url((string)$f['ruta']), 'orden' => (int)$f['orden']];
            }
        } catch (Throwable $e) {}
        return $out;
    }
}

if (!function_exists('supremo_productos_negocio')) {
    /** Los productos de la tienda, con si tienen foto o no. */
    function supremo_productos_negocio($negocio_id) {
        $out = [];
        try {
            $st = db()->prepare("SELECT s.id, s.titulo, s.precio, s.imagen,
                                        (SELECT COUNT(*) FROM directorio_producto_fotos f WHERE f.producto_id = s.id) AS nfotos
                                   FROM directorio_servicios s
                                  WHERE s.negocio_id = ? AND s.activo = 1
                                  ORDER BY s.id ASC");
            $st->execute([(int)$negocio_id]);
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $p) {
                $out[] = ['id' => (int)$p['id'], 'titulo' => (string)$p['titulo'],
                          'precio' => (float)$p['precio'],
                          'imagen' => (string)$p['imagen'],
                          'foto' => ((string)$p['imagen'] !== '' ? img_url((string)$p['imagen']) : ''),
                          'nfotos' => (int)$p['nfotos']];
            }
        } catch (Throwable $e) {}
        return $out;
    }
}

if (!function_exists('supremo_armar_productos')) {
    /**
     * 🛍️🆕 LOS 8 PRODUCTOS, DE FRENTE Y SIN PREGUNTAR NADA.
     *
     * Orden del jefe (2026-09-18, textual): *«inventes productos. No preguntes qué productos: de
     * frente crea los productos de frente y pide las imágenes. Tengo que confiar también en lo que tú
     * haces. No me preguntes qué productos quiero, ni quiero elegir de las fotos: tú elige, tú elige
     * siempre, tú elige. La tienda siempre tendrá ocho productos cuando se crea con este sistema.»*
     *
     * Cómo se eligen (el vendedor no toca nada):
     *   1. **Primero los que la IA VIO en las fotos** (hasta 8): nacen con su foto, así que ya están
     *      publicados y no necesitan que nadie les haga una imagen.
     *   2. **Los que falten hasta 8 los inventa la IA** con el contexto de la tienda (rubro, zona, lo
     *      que se vio y lo que ya hay), en tandas, y quedan **marcados para pedir su imagen** en el
     *      código (`sup_con_ia`), con su ID impreso dentro de la foto.
     *
     * @return array ['ok'=>bool,'hechos'=>int,'vistos'=>int,'inventados'=>int,'total'=>int]
     */
    function supremo_armar_productos(array &$s, array &$d, $empujar = null) {
        $neg_id = supremo_negocio_id($d);
        if ($neg_id <= 0) return ['ok' => false, 'hechos' => 0, 'vistos' => 0, 'inventados' => 0, 'total' => 0];
        $tope = max(1, (int)SUPREMO_PRODUCTOS_TOTAL);

        // Lo que ya hay en la tienda (por si se vuelve a pasar por aquí: nunca se duplica).
        $ya = [];
        foreach (supremo_productos_negocio($neg_id) as $p) { $ya[mb_strtolower((string)$p['titulo'])] = (int)$p['id']; }

        // ---- 1) los que la IA vio en las fotos (con su foto) ----
        $vistos = 0;
        foreach (supremo_productos_vistos($d) as $v) {
            if (count($ya) >= $tope) break;
            $t = mb_strtolower((string)$v['titulo']);
            if (isset($ya[$t])) continue;
            $desc = 'Producto de ' . (string)$d['nombre']
                  . (trim((string)$d['rubro_nombre']) !== '' ? ' (' . (string)$d['rubro_nombre'] . ')' : '')
                  . '. Pídelo por WhatsApp y te lo llevamos o lo recoges en el local.';
            $pid = supremo_crear_producto($neg_id, (string)$v['titulo'], $desc, 0.0, (string)$v['foto']);
            if ($pid > 0) {
                $ya[$t] = $pid;
                $d['sup_creados'][] = ['id' => $pid, 'titulo' => (string)$v['titulo'], 'con_foto' => ((string)$v['foto'] !== '')];
                $vistos++;
            }
        }

        // ---- 2) los que falten: los inventa la IA (sin foto) y se les pedirá su imagen ----
        $inventados = 0;
        // 🔴 TRAMPA CAZADA POR LA SONDA (2026-09-18 — la MISMA familia del bicho `$cuantos`/`$cuantas`):
        //    dentro del `for` el título del producto se guardaba en `$t`, que es **la variable del
        //    propio bucle**. Al salir del `foreach`, `$t` quedaba con un texto («martillo de acero»),
        //    el `$t++` no hacía nada y la condición `$t < $tandas` (texto contra número, que en PHP 8
        //    se compara como texto) daba falso: **la tienda se quedaba con 6 productos en vez de 8**
        //    y parecía culpa de la IA. Aquí la clave va en `$clave` y los intentos se cuentan aparte.
        $tandas = max(3, (int)SUPREMO_PRODUCTOS_TANDAS);
        $fallos = 0;
        for ($tanda = 1; $tanda <= $tandas && count($ya) < $tope; $tanda++) {
            $faltan = $tope - count($ya);
            $pedir  = min(4, $faltan);
            // Se le pasan a la IA los títulos que YA hay para que no repita.
            $d['productos'] = [];
            foreach ($ya as $tit => $id) { $d['productos'][] = ['titulo' => $tit]; }
            $inv = supremo_ia_inventar($d, ['sesion' => (int)$s['id'], 'usuario' => (int)$s['usuario_id']], $pedir);
            if (!$inv) {
                // Si la IA no contestó, se le da otra oportunidad; con dos fallos seguidos, se para.
                $fallos++;
                if ($fallos >= 2) break;
                continue;
            }
            $nuevos = 0;
            foreach ($inv as $p) {
                if (count($ya) >= $tope) break;
                $clave = mb_strtolower((string)$p['titulo']);
                if (isset($ya[$clave])) continue;
                $desc = trim((string)($p['frase'] ?? ''));
                if ($desc === '') $desc = (string)$p['titulo'] . ' de ' . (string)$d['nombre'] . '.';
                $pid = supremo_crear_producto($neg_id, (string)$p['titulo'], $desc, (float)($p['precio'] ?? 0), '');
                if ($pid > 0) {
                    $ya[$clave] = $pid;
                    $nuevos++;
                    $inventados++;
                    $d['sup_inventados'][] = $p;      // se guarda el «plano» para pedir su imagen
                    $d['sup_creados'][]    = ['id' => $pid, 'titulo' => (string)$p['titulo'], 'con_foto' => false];
                    // 🖼️ Sin foto ⇒ lleva imagen de la IA (automático: el vendedor no elige nada).
                    $con_ia = array_map('intval', (array)($d['sup_con_ia'] ?? []));
                    if (!in_array($pid, $con_ia, true)) $con_ia[] = $pid;
                    $d['sup_con_ia'] = $con_ia;
                }
            }
            if ($nuevos === 0) break;   // la IA no dio nada nuevo: no se insiste más
        }

        $s['datos'] = $d;
        return ['ok' => true, 'hechos' => count($ya), 'vistos' => $vistos, 'inventados' => $inventados, 'total' => count($ya)];
    }
}

if (!function_exists('supremo_productos_sin_foto')) {
    /**
     * 🖼️ Los productos de la tienda que TODAVÍA no tienen imagen: son justo los que necesitan que
     * se las pida a la IA. El orden de esta lista es el que ve el vendedor en la rejilla, así que
     * los índices que manda el navegador se traducen con ELLA (nunca con la lista completa).
     */
    function supremo_productos_sin_foto($negocio_id) {
        $out = [];
        foreach (supremo_productos_negocio($negocio_id) as $p) {
            if ((int)$p['nfotos'] <= 0) {
                $out[] = ['indice' => count($out), 'id' => (int)$p['id'],
                          'titulo' => (string)$p['titulo'], 'precio' => (float)$p['precio'],
                          'foto' => (string)$p['foto'], 'nfotos' => 0];
            }
        }
        return $out;
    }
}

/* =====================================================================================
 * 4) LO QUE LA IA VIO EN LAS FOTOS → productos que ya tienen su foto
 * ===================================================================================== */

if (!function_exists('supremo_productos_vistos')) {
    /**
     * Convierte lo que la IA leyó en las fotos (`PRODUCTOS: clavos (3), pintura (5)`) en la lista
     * de productos que el vendedor puede incluir, **cada uno con la foto donde se vio**.
     * La foto es lo que hace que estos productos nazcan ya con imagen (sin gastar en dibujarlos).
     */
    function supremo_productos_vistos(array $d) {
        $fotos = array_values((array)($d['fotos'] ?? []));
        $out   = [];
        $vistos = (array)($d['productos_vistos'] ?? []);
        foreach ($vistos as $i => $pv) {
            // 🆕 Desde el 2026-09-15 los productos vistos pueden venir con la foto aparte.
            if (is_array($pv)) {
                $titulo = trim((string)($pv['titulo'] ?? ''));
                $ix     = (int)($pv['foto'] ?? -1);
            } else {
                $titulo = trim((string)$pv);
                $ix     = -1;
                // «clavos (3)» → el 3 es el NÚMERO DE LA FOTO donde se vio (así lo pide el prompt).
                if (preg_match('/^(.*?)\s*\((\d+)\)\s*$/u', $titulo, $m)) {
                    $titulo = trim((string)$m[1]);
                    $ix     = (int)$m[2] - 1;
                }
            }
            $titulo = tienda_ia_limpiar($titulo, 60);
            if ($titulo === '' || mb_strlen($titulo) < 3) continue;
            $rel = ($ix >= 0 && isset($fotos[$ix])) ? (string)$fotos[$ix]
                 : (string)($fotos[$i + 1] ?? ($fotos[0] ?? ''));
            if ($rel === '') continue;
            // ⚠️ Si no hay foto no se cae: se ofrece igual, y su imagen se pide con el código.
            $out[] = ['titulo' => $titulo, 'foto' => $rel, 'precio' => 0];
            if (count($out) >= (int)SUPREMO_PRODUCTOS_VISTOS_MAX) break;
        }
        return $out;
    }
}

/* =====================================================================================
 * 5) LA IA DEL SUPREMO
 * ===================================================================================== */

if (!function_exists('supremo_llamar')) {
    /**
     * La llamada a la IA del Supremo: es la de El maestro (misma clave, mismo modelo con visión
     * y el razonamiento apagado) pero con el **paso marcado `sup_`** para que el gasto se vea
     * separado en la pestaña del panel.
     */
    function supremo_llamar($mensaje, array $op = []) {
        if (empty($op['paso'])) $op['paso'] = 'sup_ia';
        return tienda_ia_llamar($mensaje, $op);
    }
}

if (!function_exists('supremo_descripcion_guardada')) {
    /**
     * La descripción que la IA le escribió a la tienda (la columna `descripcion`).
     * Se lee DESPUÉS de publicar por una razón concreta: el «Cuerpo» del prompt de música son
     * «~50 palabras de la descripción real de la tienda», y esa descripción la acaba de escribir
     * la IA en la publicación. Sin esto el Cuerpo salía con las dos líneas del relato y la canción
     * se quedaba sin materia prima.
     */
    function supremo_descripcion_guardada($negocio_id) {
        try {
            $st = db()->prepare("SELECT descripcion FROM directorio_negocios WHERE id = ? LIMIT 1");
            $st->execute([(int)$negocio_id]);
            return trim((string)$st->fetchColumn());
        } catch (Throwable $e) { return ''; }
    }
}

if (!function_exists('supremo_descripcion_plana')) {
    /** El texto de la descripción (que es HTML con botones) en palabras: se usa para la música. */
    function supremo_descripcion_plana(array $d, $palabras = 50) {
        $html = (string)($d['descripcion_ia'] ?? '');
        if ($html === '') $html = (string)($d['trato'] ?? '');
        $t = strip_tags(str_replace(['<br>', '<br/>', '</p>', '</li>', '</h3>'], ' ', $html));
        $t = html_entity_decode($t, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        // 🧹 Los emojis y los adornos del copy (los títulos llevan uno al principio) fuera: el
        // «Cuerpo» del prompt de música es texto que la IA de música tiene que leer limpio.
        $t = preg_replace('/[\x{1F000}-\x{1FAFF}\x{2190}-\x{21FF}\x{2300}-\x{27BF}\x{2B00}-\x{2BFF}\x{FE0F}\x{200D}]/u', '', $t);
        $t = trim(preg_replace('/\s+/u', ' ', (string)$t));
        if ($t === '') return '';
        $partes = preg_split('/\s+/u', $t);
        if (count($partes) <= $palabras) return $t;
        return implode(' ', array_slice($partes, 0, $palabras)) . '…';
    }
}

if (!function_exists('supremo_parse_inventados')) {
    /**
     * Lee las líneas que devolvió la IA al inventar productos (formato `producto | precio | frase | plano`).
     * Va aparte porque lo usan la primera respuesta Y la segunda (cuando el modelo se queda corto).
     */
    function supremo_parse_inventados($texto, $cuantas = 3, $evitar = []) {
        $texto = (string)$texto;
        // ⚠️ TRAMPA CAZADA POR LA SONDA (2026-09-18): el modelo a veces PARTE una línea larga a mitad
        //    de un campo (la frase o el plano), y al leer línea por línea esos productos se perdían
        //    (salían 1 en vez de 3). Aquí se vuelven a pegar: un salto de línea que NO empieza con
        //    «2 |» es continuación de lo anterior, no un producto nuevo.
        $texto = preg_replace('/\n(?!\s*\d+\s*[\|\.\)\-])/u', ' ', $texto);
        $out = [];
        foreach (tienda_ia_lineas($texto) as $l) {
            $l = trim(preg_replace('/^\s*\d+\s*[\.\)\-:]?\s*/u', '', $l));
            if ($l === '') continue;
            // Al quitar el «1.» de «1 | Cemento | 28 | …» queda una **barra suelta delante**: si no se
            // limpia, el primer campo sale VACÍO y el producto se pierde (la cazó la sonda).
            $l = trim($l, " \t|");
            $c = array_map(function ($x) { return trim((string)$x); }, explode('|', $l));
            while ($c && $c[0] === '') array_shift($c);
            while ($c && end($c) === '') array_pop($c);
            if (count($c) >= 5 && preg_match('/^\d+$/', $c[0]) === 1) array_shift($c);   // por si dejó el número
            if (count($c) < 4) continue;
            $titulo = tienda_ia_limpiar($c[0], 60);
            if (mb_strlen($titulo) < 3) continue;
            if ($evitar && in_array(mb_strtolower($titulo), $evitar, true)) continue;
            $precio = (float)preg_replace('/[^0-9.]/', '', str_replace(',', '.', $c[1]));
            if ($precio < 0) $precio = 0;
            $out[] = ['titulo' => $titulo, 'precio' => $precio,
                      'frase'  => tienda_ia_limpiar_largo($c[2], 220),
                      'plano'  => tienda_ia_limpiar_largo($c[3], 220)];
            if (count($out) >= $cuantas) break;
        }
        return $out;
    }
}

if (!function_exists('supremo_ia_inventar')) {
    /**
     * 🧠 LOS TRES PRODUCTOS QUE INVENTA LA IA (lo que pidió el jefe: *«código para generar tres
     * productos que invente en base al contexto que tenga la tienda»*).
     *
     * Lo que devuelve, por cada producto: el título, el precio (a criterio, en soles de Chimbote),
     * una frase de venta y **el plano de su foto** (la acción y los objetos que hay que dibujar):
     * ese último dato es el que hace que el código de imágenes salga con sentido y no genérico.
     *
     * Formato de respuesta: 3 líneas con 5 datos separados por « | » (nunca JSON: al modelo se le
     * va el turno razonando y devuelve vacío — trampa 5 de la guía de El maestro).
     */
    function supremo_ia_inventar(array $d, array $op = [], $cuantas_pedidas = 0) {
        $rubros = [];
        if (trim((string)$d['rubro_nombre']) !== '') $rubros[] = (string)$d['rubro_nombre'];
        foreach ((array)($d['rubros_extra'] ?? []) as $r) {
            $n = trim((string)($r['nombre'] ?? ''));
            if ($n !== '') $rubros[] = $n;
        }
        $nombres = [];
        foreach ((array)($d['productos'] ?? []) as $p) {
            $t = trim((string)($p['titulo'] ?? ''));
            if ($t !== '') $nombres[] = $t;
        }
        $visto = [];
        foreach ((array)($d['visto'] ?? []) as $v) { if (trim((string)$v) !== '') $visto[] = '- ' . trim((string)$v); }

        // 🛍️ Cuántos productos inventar: por defecto los de la config (3), pero el Supremo le pide
        //    los que le faltan hasta los 8 de la tienda (en tandas de 3-4 por llamada).
        $cuantos = (int)$cuantas_pedidas > 0 ? min(8, (int)$cuantas_pedidas) : (int)SUPREMO_PRODUCTOS_IA;
        $peticion = "La tienda se llama «" . (string)$d['nombre'] . "»"
                  . ($rubros ? ', es de ' . implode(' y ', $rubros) : '')
                  . (trim((string)($d['rubro_leido'] ?? '')) !== '' ? ' (' . trim((string)$d['rubro_leido']) . ')' : '')
                  . ', está en ' . (string)$d['distrito_nombre'] . ' (Chimbote, Perú)'
                  . " y queremos armarle {$cuantos} productos NUEVOS para su catálogo.\n"
                  . (trim((string)($d['direccion'] ?? '')) !== '' ? 'Dirección: ' . (string)$d['direccion'] . "\n" : '')
                  . ($visto ? "En las FOTOS de su local se vio:\n" . implode("\n", array_slice($visto, 0, 6)) . "\n" : '')
                  . ($nombres ? 'Ya tiene en su catálogo: ' . implode(', ', $nombres) . ". NO repitas ninguno.\n" : '')
                  . "\nInventa {$cuantos} productos CONCRETOS que ese negocio podría vender de verdad, de los que "
                  . "se venden todos los días en un negocio así en Chimbote (nada raro ni de lujo, nada que no pegue "
                  . "con su rubro). Los precios tienen que ser realistas en soles peruanos.\n\n"
                  . "Contesta SOLO con {$cuantos} líneas, cada una con estos 5 datos separados por « | »:\n"
                  . "N.º | producto (de 2 a 4 palabras) | precio solo en números (sin S/) | una frase de 12 a 20 palabras "
                  . "que diga qué es y para qué le sirve al cliente | la foto que hay que hacerle (qué acción se ve y "
                  . "qué 2 o 3 objetos aparecen, en una frase)\n\n"
                  . "Ejemplo del formato (no copies este contenido):\n"
                  . "1 | Saco de cemento | 28 | Cemento de uso general para vaciados y tarrajeo, rinde bien y fragua parejo | "
                  . "Las manos del maestro abriendo el saco sobre la carretilla, con la bolsa y la pala al costado\n"
                  . "Nada de títulos, nada de explicaciones, nada de texto fuera de las {$cuantos} líneas.";

        $r = supremo_llamar($peticion, array_merge(['paso' => 'sup_inventar', 'max_tokens' => 600], $op));
        if (empty($r['ok'])) return [];
        // 🧪 Gancho de las SONDAS (mismo estilo que `TIA_MODO_PRUEBA` de El maestro): con
        //    `$GLOBALS['SUPREMO_DIAG'] = 1` la sonda puede ver la respuesta CRUDA del modelo.
        //    Sin el gancho no se guarda nada y no cuesta nada.
        if (!empty($GLOBALS['SUPREMO_DIAG'])) $GLOBALS['SUPREMO_DIAG_RAW']['inventar'][] = (string)$r['texto'];

        // ⚠️🔴 TRAMPA CAZADA POR LA SONDA CON TRAZAS (2026-09-18): aquí la variable local se llama
        //    `$cuantos` y el parámetro del parser `$cuantas`. Al pasarle `$cuantas` (que no existía)
        //    llegaba **null**, y `count($out) >= null` se cumple siempre: el parser devolvía SIEMPRE
        //    **un solo producto** (con el modelo contestando los 3 perfectos) y la repetición tampoco
        //    entraba (`1 < null` es falso). El fallo era nuestro, no de la IA.
        $out = supremo_parse_inventados((string)$r['texto'], $cuantos);
        if (!empty($GLOBALS['SUPREMO_DIAG'])) $GLOBALS['SUPREMO_DIAG_RAW']['parse1'][] = ['cuantos' => $cuantos, 'parseados' => count($out)];

        // 🔁 SI SE QUEDÓ CORTO, SE LE PIDE LO QUE FALTA UNA SOLA VEZ. El jefe pidió **tres** productos:
        //    si el modelo devuelve uno o dos (le pasa cuando se pone a explicar de más), no se le deja
        //    así — se le pide lo que falta, sin repetir los que ya dio. Es una llamada corta y barata.
        if (count($out) > 0 && count($out) < $cuantos) {
            $faltan = $cuantos - count($out);
            $ya = [];
            foreach ($out as $x) { $ya[] = (string)$x['titulo']; }
            $peticion2 = "Para «" . (string)$d['nombre'] . "»"
                       . ($rubros ? ' (' . implode(' y ', $rubros) . ')' : '')
                       . " en " . (string)$d['distrito_nombre'] . " ya anotamos estos productos: " . implode(', ', $ya) . ".\n"
                       . "Da SOLO {$faltan} producto(s) MÁS, DIFERENTES a esos, que ese negocio venda de verdad "
                       . "(nada raro ni de lujo, precios realistas en soles peruanos).\n\n"
                       . "Contesta SOLO con {$faltan} línea(s), cada una con estos 4 datos separados por « | » "
                       . "(sin numerar, en UNA sola línea cada producto):\n"
                       . "producto (de 2 a 4 palabras) | precio solo en números | una frase de 12 a 20 palabras | "
                       . "la foto que hay que hacerle (qué acción se ve y qué 2 o 3 objetos aparecen)";
            $r2 = supremo_llamar($peticion2, array_merge(['paso' => 'sup_inventar', 'max_tokens' => 400], $op));
            if (!empty($GLOBALS['SUPREMO_DIAG'])) {
                $GLOBALS['SUPREMO_DIAG_RAW']['retry'][] = ['ok' => !empty($r2['ok']), 'error' => (string)($r2['error'] ?? ''),
                                                            'detalle' => (string)($r2['detalle'] ?? ''), 'texto' => (string)($r2['texto'] ?? '')];
            }
            if (!empty($r2['ok'])) {
                if (!empty($GLOBALS['SUPREMO_DIAG'])) $GLOBALS['SUPREMO_DIAG_RAW']['inventar2'][] = (string)$r2['texto'];
                $evitar = [];
                foreach ($ya as $t) { $evitar[] = mb_strtolower($t); }
                foreach (supremo_parse_inventados((string)$r2['texto'], $faltan, $evitar) as $x) { $out[] = $x; }
            }
        }
        if (!empty($GLOBALS['SUPREMO_DIAG'])) $GLOBALS['SUPREMO_DIAG_RAW']['final'][] = count($out);
        return $out;
    }
}

if (!function_exists('supremo_ia_nombres')) {
    /**
     * ✅ NOMBRES PARA LA TIENDA, DE UNO EN UNO (orden del jefe, 2026-09-18).
     *
     * *«Si ya lo subió, de frente debe decir qué nombre se le ha ocurrido y preguntar con dos botones
     *   sí o no: por ejemplo «la tienda se llama Los Juancitos, ¿sí o no?». Si el cliente dice no,
     *   propone el siguiente nombre… y preguntan con tres botones: sí, no, escribir.»*
     *
     * Aquí se piden **varias ideas de una sola vez** (una llamada) y se guardan en una **cola**: así
     * cada «No» del cliente muestra la siguiente **al instante**, sin esperar a la IA otra vez.
     * Los nombres salen del contexto real de la tienda (rubro, zona, lo que vende) y del letrero si
     * la IA lo leyó: nunca inventan un rubro que no es.
     *
     * @return array lista de nombres (puede venir vacía si la IA no contestó)
     */
    function supremo_ia_nombres(array $d, $cuantos = 4) {
        $cuantos = max(1, min(6, (int)$cuantos));
        $rubro   = trim((string)($d['rubro_leido'] ?? '')) !== '' ? (string)$d['rubro_leido'] : (string)($d['rubro_nombre'] ?? '');
        $zona    = (string)($d['distrito_nombre'] ?? '');
        $leido   = tienda_ia_nombre_de_letrero((string)($d['letrero'] ?? ''));
        $visto   = [];
        foreach ((array)($d['visto'] ?? []) as $v) { if (trim((string)$v) !== '') $visto[] = trim((string)$v); }

        $peticion = "Estás ayudando a bautizar la tienda de un negocio de Chimbote (Perú) que acaba de "
                  . "mandar sus fotos.\n\n"
                  . 'Rubro: ' . ($rubro !== '' ? $rubro : '(no se pudo leer)') . "\n"
                  . 'Zona: ' . ($zona !== '' ? $zona : 'Chimbote') . "\n"
                  . ($leido !== '' ? 'En su letrero se lee: «' . $leido . "»\n" : '')
                  . ($visto ? 'En las fotos se ve: ' . implode(', ', array_slice($visto, 0, 6)) . "\n" : '')
                  . "\nDame {$cuantos} NOMBRES de tienda, uno por línea, sin numerar y sin nada más.\n"
                  . "Cómo tienen que ser:\n"
                  . "- En español del Perú, cortos (2 a 4 palabras), fáciles de decir y de recordar.\n"
                  . "- Como se llaman de verdad los negocios de barrio: «Los Juanitos», «El Ascensor», "
                  . "«Bendición Divina», «Multi Tienda Todo Barato», «Doña Rosa», «El Buen Pastor»…\n"
                  . "- Que peguen con el RUBRO (si es ferretería, que suene a ferretería; si es librería, a librería).\n"
                  . "- NADA de mayúsculas raras, ni comillas, ni emojis, ni la palabra «tienda» en todos.\n"
                  . "- Sí puedes jugar con la zona («de Chimbote», «del Santa») si suena bien.\n"
                  . ($leido !== '' ? "- Uno de los nombres puede ser el del letrero tal cual (el primero).\n" : '')
                  . "- No repitas ninguno.";

        $r = supremo_llamar($peticion, ['paso' => 'sup_nombres', 'max_tokens' => 220]);
        if (empty($r['ok'])) return [];
        if (!empty($GLOBALS['SUPREMO_DIAG'])) $GLOBALS['SUPREMO_DIAG_RAW']['nombres'][] = (string)$r['texto'];

        $out = [];
        foreach (preg_split('/\r?\n/', (string)$r['texto']) as $l) {
            $l = trim(preg_replace('/^\s*[-*\d\.\)]+\s*/u', '', trim((string)$l)));
            $l = trim($l, " \t\"'«»");
            if ($l === '' || mb_strlen($l) < 3 || mb_strlen($l) > 60) continue;
            if (preg_match('/^(nombre|aquí|estos|lista)/iu', $l)) continue;
            $clave = mb_strtolower($l);
            if (in_array($clave, array_map('mb_strtolower', $out), true)) continue;
            $out[] = $l;
            if (count($out) >= $cuantos) break;
        }
        return $out;
    }
}

if (!function_exists('supremo_nombre_siguiente')) {
    /**
     * ✅ LA SIGUIENTE PROPUESTA DE NOMBRE (y la deja apuntada en `$d['sup_nombre_prop']`).
     * Si la cola está vacía, se le piden más ideas a la IA (una llamada). Devuelve '' si no hay nada.
     */
    function supremo_nombre_siguiente(array &$s, array &$d) {
        $cola = array_values(array_filter((array)($d['sup_nombres'] ?? []), function ($x) { return trim((string)$x) !== ''; }));
        // El del letrero va primero (si la IA lo leyó y todavía no se ha propuesto).
        if (!$cola && (int)($d['sup_nombre_visto'] ?? 0) === 0) {
            $leido = tienda_ia_nombre_de_letrero((string)($d['letrero'] ?? ''));
            if ($leido !== '') $cola[] = $leido;
        }
        if (!$cola) {
            $mas = supremo_ia_nombres($d, 4);
            foreach ($mas as $n) {
                if (mb_strtolower($n) !== mb_strtolower((string)($d['sup_nombre_prop'] ?? ''))) $cola[] = $n;
            }
        }
        if (!$cola) return '';
        $sig = (string)array_shift($cola);
        $d['sup_nombres']      = $cola;
        $d['sup_nombre_prop']  = $sig;
        $d['sup_nombre_visto'] = (int)($d['sup_nombre_visto'] ?? 0) + 1;
        $s['datos'] = $d;
        return $sig;
    }
}

if (!function_exists('supremo_ia_descripcion_tienda')) {
    /**
     * ✍️ LA DESCRIPCIÓN DEFINITIVA DE LA FICHA (una llamada), **cuando los 8 productos ya están dentro**.
     *
     * Antes esto pasaba en la publicación y el jefe lo notaba: *«ya llegué a la parte donde va a poner el
     * número, imagino que está creando la tienda, está tardando un poquito»* (orden del 2026-09-18).
     * Ahora la tienda se publica **al instante** (con el relato en texto plano) y el copy bueno se
     * escribe en el paso de los productos, que es donde el vendedor ya está esperando de todas formas.
     * Sale **mejor**: el copywriting nombra los productos de verdad, con sus precios.
     *
     * Nunca rompe la venta: si la IA no contesta, la ficha se queda con el relato.
     *
     * @return string el copy guardado ('' si no se pudo)
     */
    function supremo_ia_descripcion_tienda(array &$s, array &$d) {
        $neg_id = supremo_negocio_id($d);
        if ($neg_id <= 0) return '';
        $prods = supremo_productos_negocio($neg_id);
        if (!$prods) return '';
        if (!function_exists('tienda_ia_ia_descripcion')) require_once __DIR__ . '/tienda_ia.php';

        $copy = tienda_ia_ia_descripcion($d, [
            'sesion'    => (int)$s['id'],
            'usuario'   => (int)$s['usuario_id'],
            'productos' => $prods,
        ]);
        // 🔁 Si el copy no pasó el listón de la casa (≥300 caracteres, 3 bloques `<h3>`, los botones de
        //    WhatsApp y el cierre), `tienda_ia_ia_descripcion()` devuelve vacío. Antes eso dejaba la ficha
        //    con el relato pelado: aquí se le pide UNA vez más (el modelo es variable y a la segunda casi
        //    siempre sale con la estructura completa). Solo cuesta tiempo cuando falla la primera.
        if (!is_string($copy) || trim($copy) === '') {
            $copy = tienda_ia_ia_descripcion($d, [
                'sesion'      => (int)$s['id'],
                'usuario'     => (int)$s['usuario_id'],
                'productos'   => $prods,
                'max_tokens'  => 1800,
            ]);
        }
        if (!is_string($copy) || trim($copy) === '') return '';
        $copy = mb_substr(tienda_ia_copy_limpiar($copy), 0, 6000);
        if ($copy === '') return '';
        try {
            db()->prepare("UPDATE directorio_negocios SET descripcion = ?, actualizado_en = ? WHERE id = ?")
                ->execute([$copy, date('Y-m-d H:i:s'), $neg_id]);
            require_once __DIR__ . '/fuzzy_cache.php';
            fuzzy_olvidar_cache();
        } catch (Throwable $e) {
            error_log('supremo_ia_descripcion_tienda: ' . $e->getMessage());
            return '';
        }
        return $copy;
    }
}

if (!function_exists('supremo_ia_leer_ids')) {    /**
     * 📥 LEE EL NÚMERO QUE TRAE IMPRESO CADA IMAGEN QUE LLEGA.
     *
     * Es el truco que hace que el diseñador y el programador se entiendan sin hablar: los códigos
     * piden que **cada imagen lleve su ID escrito dentro, pequeño y en cifras simples**, y aquí se
     * lee con visión para saber a qué producto va cada archivo (los nombres de archivo no dicen nada).
     *
     * Devuelve: `[ ['imagen' => 0, 'id' => 12288, 'portada' => false], … ]` (una entrada por imagen).
     * Si la IA no puede, devuelve `[]` y quien llama decide (se reparte por orden).
     */
    function supremo_ia_leer_ids(array $rels, $ids_validos = [], $nombre_tienda = '', array $op = []) {
        $limpias = [];
        foreach ($rels as $rel) { if (is_string($rel) && $rel !== '') $limpias[] = $rel; }
        if (!$limpias) return [];
        $k = count($limpias);

        $lista = $ids_validos ? implode(', ', array_map('intval', $ids_validos)) : 'ninguno en especial';
        $peticion = "Te mando {$k} imagen(es). Son las que hizo el diseñador para UNA tienda.\n"
                  . "Cada imagen de PRODUCTO lleva escrito DENTRO un número pequeño (su ID). "
                  . "La imagen de la CABECERA (la portada) no lleva número: lleva escrito el NOMBRE del negocio, grande.\n"
                  . "Los IDs que existen son: {$lista}.\n"
                  . ($nombre_tienda !== '' ? "El negocio se llama «{$nombre_tienda}».\n" : '')
                  . "\nMira cada imagen y contesta SOLO con {$k} línea(s), una por imagen, en el mismo orden en que te llegaron, "
                  . "con estos datos separados por « | »:\n"
                  . "número de la imagen (empezando en 1) | el número que se lee dentro (solo dígitos) o NINGUNO | "
                  . "PORTADA si es la cabecera del negocio, PRODUCTO si es de un producto, DUDOSA si no sabes\n"
                  . "Sin títulos, sin explicaciones, sin nada más.";

        $r = supremo_llamar($peticion, array_merge([
            'paso'       => 'sup_imagenes_leer',
            'imagenes'   => $limpias,
            'max_tokens' => 400,
            'lado'       => 1200,
            'calidad'    => 80,
        ], $op));
        if (empty($r['ok'])) return [];

        $out = [];
        foreach (tienda_ia_lineas((string)$r['texto']) as $l) {
            $c = array_map(function ($x) { return trim((string)$x); }, explode('|', $l));
            if (count($c) < 3) continue;
            $ix = (int)preg_replace('/\D+/', '', $c[0]) - 1;
            if ($ix < 0 || $ix >= $k) continue;
            $id = (int)preg_replace('/\D+/', '', $c[1]);
            $que = mb_strtoupper($c[2]);
            $out[$ix] = [
                'imagen'  => $ix,
                'id'      => $id,
                'portada' => (mb_strpos($que, 'PORTADA') !== false),
                'dudosa'  => (mb_strpos($que, 'DUDOSA') !== false),
            ];
        }
        return array_values($out);
    }
}

/* =====================================================================================
 * 6) 👑 LOS CÓDIGOS (lo que hace único al Supremo)
 * ===================================================================================== */

if (!function_exists('supremo_fondo_palabras')) {
    /**
     * El «fondo» de la cabecera, en palabras («una ferretería», «un restaurante»): es lo que se le
     * pide a la IA de imágenes. Sale de lo que la IA LEYÓ en las fotos (que ya viene en singular y
     * en palabras: «ferretería») y, si no leyó nada, del rubro del directorio.
     */
    function supremo_fondo_palabras(array $d) {
        $leido = trim((string)($d['rubro_leido'] ?? ''));
        if ($leido === '') {
            // Del rubro del directorio («Ferreterías» → «ferretería»).
            $r = trim((string)($d['rubro_nombre'] ?? ''));
            if ($r !== '') {
                $r = preg_split('#[/,]#', $r)[0];
                $r = trim((string)$r);
                $r = mb_strtolower($r);
                if (mb_substr($r, -1) === 's') $r = mb_substr($r, 0, -1);
            }
            $leido = $r;
        }
        if ($leido === '') return 'un negocio';
        $leido = mb_strtolower(trim($leido));
        // El artículo: «a» final, «-ería», «-ción», «-dad» y «-eza» son femeninas en español.
        $fem = (bool)preg_match('/(a|ería|ción|dad|eza|umbre|triz)$/u', $leido);
        // Y si empieza por el rubro ya con artículo, se respeta.
        if (preg_match('/^(el|la|los|las|un|una)\s+/u', $leido)) return $leido;
        return ($fem ? 'una ' : 'un ') . $leido;
    }
}

if (!function_exists('supremo_genero_musica')) {
    /** El género de la canción, según el rubro (lo que pide el prompt de música de Flow). */
    function supremo_genero_musica(array $d) {
        $t = tienda_ia_sin_tildes(mb_strtolower(trim((string)($d['rubro_leido'] ?? '') . ' ' . (string)($d['rubro_nombre'] ?? ''))));
        $mapa = [
            'barber'      => 'urbano con base de hip-hop suave y batería marcada',
            'peluquer'    => 'pop latino alegre y moderno con base bailable',
            'salon'       => 'pop latino alegre y moderno con base bailable',
            'spa'         => 'chill out suave con toques de pop latino',
            'cevicher'    => 'cumbia peruana con salsa y metales alegres',
            'restaurant'  => 'cumbia alegre con guitarra criolla y base bailable',
            'poller'      => 'cumbia pegajosa con metales y coros alegres',
            'chifa'       => 'pop latino con un toque oriental y base bailable',
            'cafeter'     => 'acústico cálido con pop suave y palmas',
            'panader'     => 'pop latino dulce con base suave y campanillas',
            'pasteler'    => 'pop latino dulce con base suave y campanillas',
            'bodega'      => 'cumbia alegre y sencilla, con coros pegajosos',
            'minimarket'  => 'pop latino enérgico con base bailable',
            'mercado'     => 'cumbia alegre con coros de fiesta',
            'ferreter'    => 'rock pop enérgico con base firme y coros',
            'veterinar'   => 'pop tierno y alegre con ukelele',
            'botica'      => 'pop optimista con base suave y coros claros',
            'farmac'      => 'pop optimista con base suave y coros claros',
            'consultor'   => 'pop suave y confiable con piano',
            'dentist'     => 'pop optimista y luminoso con base suave',
            'abogad'      => 'pop elegante y sobrio con cuerdas',
            'taller'      => 'rock pop con base fuerte y guitarra',
            'mecanic'     => 'rock pop con base fuerte y guitarra',
            'vulcaniz'    => 'rock pop con base fuerte y guitarra',
            'moto'        => 'rock urbano enérgico con base marcada',
            'inmobiliar'  => 'pop elegante y optimista con piano',
            'gimnasio'    => 'electrónica enérgica con base potente',
            'gym'         => 'electrónica enérgica con base potente',
            'hotel'       => 'pop suave y acogedor con cuerdas',
            'hostal'      => 'pop suave y acogedor con cuerdas',
            'librer'      => 'pop tranquilo con piano y cuerdas suaves',
            'ropa'        => 'pop latino moderno y bailable',
            'zapater'     => 'pop latino moderno y bailable',
            'joyer'       => 'pop elegante con brillo y cuerdas',
            'viaje'       => 'pop optimista con base bailable y vientos',
            'escuela'     => 'pop alegre y juvenil con palmas',
            'colegio'     => 'pop alegre y juvenil con palmas',
            'natacion'    => 'pop veraniego alegre con base bailable',
            'carpinter'   => 'pop artesanal cálido con guitarra acústica',
            'imprent'     => 'pop moderno con base marcada',
            'comput'      => 'electrónica moderna con base marcada',
            'celular'     => 'electrónica moderna con base marcada',
            'seguridad'   => 'pop enérgico y confiable con base firme',
            'cerrajer'    => 'pop latino enérgico y moderno con un toque de suspenso que se resuelve alegre',
            'transporte'  => 'cumbia con base marcada y coros alegres',
            'eventos'     => 'pop de fiesta con base bailable',
        ];
        foreach ($mapa as $clave => $genero) {
            if ($clave !== '' && mb_strpos($t, $clave) !== false) return $genero;
        }
        return 'pop latino alegre y moderno con base bailable';
    }
}

if (!function_exists('supremo_codigo_cabecera')) {
    /** 🎨 CÓDIGO 1 — LA CABECERA: se pega en Gemini/Flow CON UNA IMAGEN INGREDIENTE adjunta. */
    function supremo_codigo_cabecera(array $d, $negocio_id = 0) {
        $negocio_id = (int)$negocio_id ?: supremo_negocio_id($d);
        $nombre = (string)$d['nombre'];
        $rubro  = trim((string)($d['rubro_leido'] ?? '')) !== '' ? (string)$d['rubro_leido'] : (string)$d['rubro_nombre'];
        $fondo  = supremo_fondo_palabras($d);
        // 🔴 LA PORTADA ES UN TEXTO ARTÍSTICO SOBRE UNA FOTO DEL RUBRO (orden del jefe, 2026-09-18,
        //    con un ejemplo a la vista: piñatas y estantes de una piñatería/librería de fondo y el
        //    nombre en letras grandes con contorno y adornos encima). No es la foto del local del
        //    cliente: es una imagen **genérica del rubro** que se entiende al primer golpe de vista.
        $l = [];
        $l[] = 'PEDIDO DE 1 IMAGEN — dechimbote.com (la CABECERA de la tienda)';
        $l[] = '';
        $l[] = 'Crea una imagen con un fondo de ' . $fondo . ' y un texto que diga «' . $nombre . '». '
             . 'Copia los estilos de diseño, color, forma y fuentes de la imagen ingrediente que te adjunto. '
             . 'Añade además, en texto plano y discreto, el número ' . $negocio_id . '.';
        $l[] = '';
        $l[] = '· EL FONDO es una imagen REAL y LLENA de ' . $fondo . ' (' . $rubro . '): que se vea el rubro de '
             . 'un solo golpe de vista (su mercadería, sus estantes, sus colores). Es una imagen GENÉRICA del '
             . 'rubro, no el local de un cliente en particular. Limpia, ordenada, luminosa, con colores '
             . 'comerciales vivos. Nada de dibujos, caricaturas, vectorial, 3D ni render.';
        $l[] = '· EL TEXTO PROTAGONISTA es el nombre: LETRAS GRANDES, ARTÍSTICAS y con volumen, que ocupen el '
             . 'centro de la imagen, con contorno grueso y colores que contrasten fuerte con el fondo '
             . '(blanco o claro con borde oscuro), como un rótulo pintado a mano. Puede llevar un adorno '
             . 'del rubro alrededor (confeti, brillos, trazos de pintura), pero SIN tapar las letras.';
        $l[] = '· El nombre de la tienda es el ÚNICO texto con diseño. Todo en español.';
        $l[] = '· El número ' . $negocio_id . ' va pequeño y en una esquina, en cifras simples y rectas, '
             . 'sin adornos, sin cursiva, sin cajas y sin sombras (así nadie confunde un 1 con una l).';
        $l[] = '· No copies ningún otro texto de la imagen ingrediente: ni teléfonos, ni precios, ni direcciones, '
             . 'ni fechas, ni lemas, ni nombres de otros negocios, ni el nombre del archivo.';
        $l[] = '· Formato cuadrado 1:1, alta resolución.';
        return implode("\n", $l);
    }
}

if (!function_exists('supremo_codigo_musica')) {
    /** 🎵 CÓDIGO 2 — LA MÚSICA: se pega en el generador de música de Google Flow. */
    function supremo_codigo_musica(array $d) {
        $nombre   = (string)$d['nombre'];
        $rubro    = trim((string)($d['rubro_leido'] ?? '')) !== '' ? (string)$d['rubro_leido'] : (string)$d['rubro_nombre'];
        $distrito = (string)$d['distrito_nombre'] !== '' ? (string)$d['distrito_nombre'] : 'Chimbote';
        $tipo     = ['fisica' => 'local con atención al público',
                     'ambulante' => 'negocio ambulante',
                     'domicilio' => 'servicio a domicilio',
                     'nacional' => 'venta a todo el país',
                     'mayorista' => 'venta al por mayor'][(string)$d['vendedor']] ?? 'negocio';
        $cuerpo   = supremo_descripcion_plana($d, 50);
        if ($cuerpo === '') $cuerpo = $nombre . ' es ' . ($rubro !== '' ? $rubro : 'un negocio') . ' en ' . $distrito . '.';
        // 🎙️ El telonero da un TEXTO DE BIENVENIDA (regla vigente del 2026-09-18: ya no dice el ID).
        $bienvenida = 'Bienvenidos a ' . $nombre . ', ' . ($rubro !== '' ? $rubro : 'tu negocio de confianza')
                    . ' en ' . $distrito . ': ' . mb_substr(rtrim($cuerpo, '.… '), 0, 120) . '.';
        $genero   = supremo_genero_musica($d);

        $l = [];
        $l[] = 'Activa tu modo de crear música y crea la siguiente canción:';
        $l[] = '';
        $l[] = 'Título: ' . $nombre . ' + ' . ($rubro !== '' ? $rubro : 'negocio') . ' + ' . $tipo . ' + ' . $distrito . ', ' . SUPREMO_PROVINCIA;
        $l[] = '';
        $l[] = 'Cuerpo: ' . $cuerpo;
        $l[] = '';
        $l[] = 'Regla: La primera locución es del telonero, voz de varón, no canta, habla con voz nítida y clara '
             . 'sobre la base musical, nunca sobre silencio. Da la bienvenida con este texto: "' . $bienvenida . '". '
             . 'No menciones ningún número ni código de identificación. Deja al menos 1 segundo de espacio antes de '
             . 'continuar. Luego dice el nombre de la canción: "' . $nombre . '". Después, la canción continúa con '
             . 'voz de mujer alegre y debe decir el nombre "' . $nombre . '" antes de los 3 segundos.';
        $l[] = '';
        $l[] = 'Indicación: Crea una canción pegajosa con un ritmo y género acorde al rubro ' . ($rubro !== '' ? $rubro : 'del negocio')
             . ': ' . $genero . '. Menciona varias veces con alegría "' . $nombre . '" y, si es posible, la dirección '
             . $distrito . ', ' . SUPREMO_PROVINCIA . '. Habla de lo que ofrece la tienda, con entusiasmo y optimismo, y '
             . 'procura repetir el nombre de la tienda. Duración: 1 minuto. La canción debe ser siempre en español. '
             . 'No escribas la letra aquí; tú, IA de música, encárgate de pensar y armar la letra en español.';
        return implode("\n", $l);
    }
}

if (!function_exists('supremo_codigo_producto_uno')) {
    /**
     * 🛍️📱 EL PROMPT DE **UN** PRODUCTO — de uno en uno.
     *
     * Pedido del jefe (2026-09-18, desde el celular: *«los prompts de imagen dámelos de uno en uno
     * cuando estoy en mobile»*): en el teléfono no se puede manejar una carta de 8 000 caracteres.
     * Aquí cada producto tiene **su propio prompt corto y autosuficiente** (10 líneas) para pegarlo
     * **en un mensaje aparte** en Flow, esperar su imagen y pasar al siguiente.
     *
     * Lo que NO se puede perder de cada prompt (si falta, la imagen no sirve):
     *   · la orden del número **al principio Y al cierre** (es lo que después dice a qué producto va),
     *   · el contexto del negocio y el rubro,
     *   · las órdenes de la casa condensadas (100 % español · orgánica · limpia y de impacto ·
     *     el único texto es el número).
     */
    function supremo_codigo_producto_uno(array $d, array $p, $n, $total) {
        $id      = (int)$p['id'];
        $titulo  = (string)$p['titulo'];
        $rubro   = trim((string)($d['rubro_leido'] ?? '')) !== '' ? (string)$d['rubro_leido'] : (string)$d['rubro_nombre'];
        $distrito = (string)$d['distrito_nombre'] !== '' ? (string)$d['distrito_nombre'] : 'Chimbote';
        $nombre  = (string)$d['nombre'];
        $plano   = trim((string)($p['plano'] ?? ''));
        $frase   = trim((string)($p['frase'] ?? ''));
        $precio  = (float)($p['precio'] ?? 0);

        $L = [];
        $L[] = 'PEDIDO DE 1 IMAGEN — dechimbote.com (producto ' . (int)$n . ' de ' . (int)$total . ')';
        $L[] = '';
        $L[] = '🔴 PRIMERO: escribe DENTRO de la foto, pequeño, discreto y en LETRA BLANCA, el número ' . $id
             . ' (cifras simples y rectas, en una esquina, sin adornos, sin cajas y sin sombras). '
             . 'Sin ese número la imagen no me sirve.';
        $L[] = '';
        $L[] = 'Crea una FOTO REAL de «' . $titulo . '» para la tienda «' . $nombre . '», '
             . ($rubro !== '' ? $rubro . ' ' : '') . 'en ' . $distrito . ', Áncash (Perú).';
        $L[] = 'QUÉ SE VE: ' . ($plano !== '' ? $plano
             : 'el producto en primer plano sobre el mostrador, con las manos de quien atiende mostrándolo o entregándolo.');
        if ($frase !== '') $L[] = 'QUÉ ES (para que salga bien): ' . $frase
             . ($precio > 0 ? ' Se vende a ' . SUPREMO_MONEDA . ' ' . number_format($precio, 2) . '.' : '');
        $L[] = '· Foto orgánica y real, en 1:1 (cuadrada), limpia, ordenada, elegante y con colores '
             . 'comerciales vivos. PROHIBIDO dibujo, caricatura, 3D o render.';
        $L[] = '· El ÚNICO texto de la imagen es el número ' . $id . '. Nada de teléfonos, precios, '
             . 'logotipos, carteles, marcas de agua ni el nombre del negocio. Todo en español.';
        $L[] = '· Si aparece una persona: trabajando y de perfil, sin rostro identificable.';
        $L[] = '';
        $L[] = '✅ RECUERDA ANTES DE ENTREGAR: esta imagen tiene que llevar escrito el número ' . $id . '.';
        return implode("\n", $L);
    }
}

if (!function_exists('supremo_codigo_productos')) {
    /**
     * 🛍️ CÓDIGO 3 — LAS IMÁGENES DE LOS PRODUCTOS: la carta para la IA de imágenes, con **el ID
     * escrito dentro de cada foto** (es lo único que después nos dice qué archivo es de qué producto).
     *
     * El molde es el que ya funciona en el proyecto (`CARTA_IA_PRODUCTOS_*`): la orden del número
     * al principio y al cierre de cada pedido, el contexto del rubro, y las 4 órdenes del jefe.
     */
    function supremo_codigo_productos(array $d, array $productos) {
        if (!$productos) return '';
        $negocio_id = supremo_negocio_id($d);
        $n    = count($productos);
        $nombre = (string)$d['nombre'];
        $rubro  = trim((string)($d['rubro_leido'] ?? '')) !== '' ? (string)$d['rubro_leido'] : (string)$d['rubro_nombre'];
        $distrito = (string)$d['distrito_nombre'] !== '' ? (string)$d['distrito_nombre'] : 'Chimbote';

        $l = [];
        $l[] = 'CARTA PARA LA IA DE IMÁGENES — GOOGLE FLOW (dechimbote.com · ' . $n . ' FOTO(S) DE PRODUCTO · '
             . mb_strtoupper($rubro !== '' ? $rubro : 'NEGOCIO') . ' · BLOQUE ÚNICO)';
        $l[] = 'De: asistente del proyecto dechimbote.com (IA a IA) · Para: IA de generación de imágenes';
        $l[] = 'Asunto: ' . $n . ' fotos de producto para las fichas del directorio. FOTOS REALES, LIMPIAS Y '
             . 'ELEGANTES, con el contexto correcto del rubro (' . $rubro . '), con EL NÚMERO DE ID ESCRITO DENTRO '
             . 'DE CADA IMAGEN en letra blanca, y con el MANIFIESTO al final.';
        $l[] = '';
        $l[] = '=====================================================================';
        $l[] = '0) 🔴 LO PRIMERO DE TODO — LA ORDEN OBLIGATORIA DEL NÚMERO (LÉELA ANTES DE EMPEZAR)';
        $l[] = '=====================================================================';
        $l[] = 'EN CADA IMAGEN TIENES QUE ESCRIBIR, DENTRO DE LA FOTO, EL NÚMERO DE ID DE SU PRODUCTO.';
        $l[] = 'Es una orden, no una sugerencia: es la ÚNICA manera que tenemos de saber qué foto va con qué';
        $l[] = 'producto, porque los archivos se guardan con nombres automáticos y esos nombres no nos dicen nada.';
        foreach ($productos as $i => $p) {
            $l[] = '  · IMAGEN N.º ' . ($i + 1) . ' → escribe el número ' . (int)$p['id'] . '   (' . (string)$p['titulo'] . ')';
        }
        $l[] = 'CÓMO SE ESCRIBE (así, exactamente así):';
        $l[] = '  · EN LETRA BLANCA, blanco puro, que se lea bien sobre la foto.';
        $l[] = '  · PEQUEÑO Y DISCRETO: chiquito, como un dato al pie de la imagen; que no tape el producto.';
        $l[] = '  · EN CIFRAS SIMPLES Y RECTAS: solo los dígitos. SIN adornos, SIN cursiva, SIN subrayado, SIN';
        $l[] = '    sombras raras, SIN cajas, SIN marcos, SIN círculos, SIN logos y SIN ningún otro texto alrededor.';
        $l[] = '  · UBICADO dentro de la imagen, en una esquina y con un margen corto (esquina inferior derecha).';
        $l[] = '  · ⛔ SI UNA IMAGEN SALE SIN SU NÚMERO, ESA IMAGEN NO SIRVE y hay que generarla otra vez.';
        $l[] = '';
        $l[] = '=====================================================================';
        $l[] = '1) QUÉ TE PIDO';
        $l[] = '=====================================================================';
        $l[] = 'Generar ' . $n . ' foto(s), UNA por cada producto del punto 4, y en cada una escribir dentro de la';
        $l[] = 'imagen el número de ID que le toca (punto 0). Son los ' . $n . ' producto(s) de UNA sola tienda:';
        $l[] = '«' . $nombre . '», ' . ($rubro !== '' ? $rubro : 'negocio') . ' de ' . $distrito . ', ' . SUPREMO_PROVINCIA . '.';
        $l[] = 'Cada pedido trae 3 datos obligatorios: QUÉ ACCIÓN se espera ver · QUÉ ELEMENTOS deben aparecer ·';
        $l[] = 'BAJO QUÉ CONTEXTO / RUBRO viene el producto.';
        $l[] = '';
        $l[] = '=====================================================================';
        $l[] = '2) LAS ÓRDENES QUE MÁS IMPORTA RESPETAR (son del jefe del proyecto)';
        $l[] = '=====================================================================';
        $l[] = 'A) ✅ 100 % EN ESPAÑOL. Nada de inglés: ni en las instrucciones, ni en los textos que aparezcan.';
        $l[] = 'B) ✅ 100 % ORGÁNICAS. Fotografía real. PROHIBIDO caricatura, dibujo, vectorial, 3D, render o banco de fotos.';
        $l[] = 'C) 🧼 LIMPIO, ELEGANTE Y DE IMPACTO. El local se ve ASEADO y ordenado, luz pareja y cálida, colores';
        $l[] = '   comerciales vivos. PROHIBIDO: suciedad, óxido, desgaste, objetos maltratados, desorden.';
        $l[] = 'D) 🔤 LOS TEXTOS: LA ÚNICA COSA ESCRITA QUE LLEVA LA IMAGEN ES EL NÚMERO DE ID DEL PRODUCTO (punto 0).';
        $l[] = '   Fuera de ese número, NO va ninguna palabra: nada del nombre del negocio, nada de carteles, nada de';
        $l[] = '   precios, nada de teléfonos, nada de marcas ni logos, nada de marcas de agua, nada del nombre del';
        $l[] = '   archivo. (Excepción: la etiqueta que YA VIENE IMPRESA en un envase real, corta y en español.)';
        $l[] = '';
        $l[] = '=====================================================================';
        $l[] = '3) FORMATO Y ENTREGA';
        $l[] = '=====================================================================';
        $l[] = '· Formato CUADRADO 1:1, alta resolución, para que la web no recorte nada.';
        $l[] = '· LA ACCIÓN ES LA PROTAGONISTA, EN PRIMER PLANO, con el MATERIAL y su detalle a la vista (textura,';
        $l[] = '  uniones, brillo, trama) y el ambiente ordenado.';
        $l[] = '· Si aparece una persona: natural y TRABAJANDO (a media acción, NO a la cámara), de espaldas o de';
        $l[] = '  perfil, SIN rostro identificable, ropa limpia y presentable.';
        $l[] = '· Al terminar, entrega el MANIFIESTO en el mismo orden, con estas columnas:';
        $l[] = '  N.º · producto (título + ID) · qué se ve en la imagen · ¿el número de ID se lee bien? · dudas.';
        $l[] = '';
        $l[] = '=====================================================================';
        $l[] = '4) LOS ' . $n . ' PEDIDOS';
        $l[] = '=====================================================================';
        foreach ($productos as $i => $p) {
            $id    = (int)$p['id'];
            $tit   = (string)$p['titulo'];
            $prec  = (float)($p['precio'] ?? 0);
            $plano = trim((string)($p['plano'] ?? ''));
            $frase = trim((string)($p['frase'] ?? ''));
            $l[] = '';
            $l[] = 'N.º ' . ($i + 1) . ' · PRODUCTO: ' . $tit . '  (id ' . $id . ')';
            $l[] = '🔴 ESCRIBE EN ESTA IMAGEN, PEQUEÑO, DISCRETO Y EN LETRA BLANCA, EL NÚMERO ' . $id . ' (cifras simples, sin';
            $l[] = 'adornos, en una esquina de la foto). Sin ese número la imagen no se puede publicar.';
            if ($prec > 0) $l[] = 'PRECIO DE LA FICHA: ' . SUPREMO_MONEDA . ' ' . number_format($prec, 2) . '.';
            $l[] = 'RUBRO Y CONTEXTO: ' . ($rubro !== '' ? $rubro : 'el negocio') . ' «' . $nombre . '» en ' . $distrito
                 . ', ' . SUPREMO_PROVINCIA . '.';
            $l[] = 'QUÉ ES: ' . ($frase !== '' ? $frase : $tit . ' que se vende en este negocio.');
            $l[] = 'ACCIÓN QUE QUIERO VER: ' . ($plano !== '' ? $plano : 'el producto en primer plano, en el mostrador del negocio, con las manos de quien atiende mostrándolo o entregándolo.');
            $l[] = 'DETALLES DE ESTRUCTURA Y AMBIENTE LIMPIO: primer plano del producto con su material, su textura y su';
            $l[] = 'acabado al detalle; el mostrador limpio, ordenado y con luz cálida.';
            $l[] = 'TEXTOS: SOLO el número ' . $id . ' (pequeño, discreto, letra blanca, cifras simples, en una esquina) y NADA MÁS.';
            $l[] = 'NO DEBE SALIR: ninguna otra palabra escrita, ni el nombre del negocio, ni precios, ni teléfonos,';
            $l[] = 'ni carteles, ni marcas, ni el nombre del archivo.';
            $l[] = '✅ RECUERDA ANTES DE ENTREGAR: esta imagen tiene que llevar escrito el número ' . $id . '.';
        }
        $l[] = '';
        $l[] = '=====================================================================';
        $l[] = '5) MANIFIESTO (entrégalo al final, en este orden)';
        $l[] = '=====================================================================';
        $l[] = 'N.º · producto (título + ID) · qué se ve en la imagen · ¿EL NÚMERO DE ID SE LEE BIEN? · dudas';
        $l[] = '';
        foreach ($productos as $i => $p) {
            $l[] = '  ' . ($i + 1) . ' · ' . (string)$p['titulo'] . ' (' . (int)$p['id'] . ') · ...';
        }
        return implode("\n", $l);
    }
}

if (!function_exists('supremo_codigo_cliente')) {
    /** 📲 CÓDIGO 4 — EL MENSAJE DEL CLIENTE: el WhatsApp con su enlace, su usuario y su clave. */
    function supremo_codigo_cliente(array $d) {
        $neg = (array)($d['sup_negocio'] ?? []);
        if (empty($neg['url'])) return '';
        $clave   = (string)($d['sup_clave'] ?? '');
        $usuario = (string)($d['sup_usuario'] ?? '');
        $l = [];
        $l[] = '¡Listo, ' . (string)$d['nombre'] . '! 🎉 Ya tienes tu tienda en DeChimbote.com y ya está en internet:';
        $l[] = '';
        $l[] = (string)$d['nombre'] . ':';
        $l[] = (string)$neg['url'];
        $l[] = '';
        // ⚠️ UN SOLO ENLACE EN EL MENSAJE (orden del jefe, 2026-09-18: *«en estos mensajes de WhatsApp
        //    no pueden haber dos links»*). Antes aquí iba también el enlace del panel: el enlace de la
        //    tienda es el único que se manda, y el panel se explica en palabras.
        $l[] = 'Para administrarla tú mismo (agregar productos, cambiar precios, ver tus visitas) entra a '
             . 'DeChimbote.com y toca «Mi panel», con tu usuario y tu contraseña de aquí abajo.';
        $l[] = '';
        if ($usuario !== '') $l[] = '👤 Tu usuario: ' . $usuario;
        if ($clave !== '')   $l[] = '🔑 Tu contraseña: ' . $clave;
        if ($clave !== '')   $l[] = '';
        if ($clave !== '')   $l[] = 'Guarda este mensaje: la contraseña no se vuelve a mostrar.';
        $l[] = 'Cualquier cosa me escribes. ¡Muchas ventas! 🚀';
        return implode("\n", $l);
    }
}

if (!function_exists('supremo_wa_cliente')) {
    /**
     * 📲 EL BOTÓN DE WHATSAPP PARA EL CLIENTE (orden del jefe, 2026-09-18: *«el mensaje para el
     * cliente debe ser un botón de WhatsApp para mandarle el mensaje»*).
     *
     * Devuelve el enlace `wa.me` con **el número del cliente** y **su mensaje ya escrito** (el
     * enlace de su tienda, su usuario y su clave): el vendedor solo toca el botón y **le da enviar**.
     * Se arma con la misma receta que usa el resto del sitio (Perú: 51 + los 9 dígitos).
     * Si no hay número, devuelve '' y el botón no se pinta (nunca un enlace roto).
     */
    function supremo_wa_cliente(array $d) {
        $txt = supremo_codigo_cliente($d);
        if (trim($txt) === '') return '';
        $tel = preg_replace('/\D+/', '', (string)($d['whatsapp'] ?? ''));
        if ($tel === '') $tel = preg_replace('/\D+/', '', (string)($d['sup_usuario'] ?? ''));
        if (strlen($tel) === 11 && substr($tel, 0, 2) === '51') $tel = substr($tel, 2);
        if (strlen($tel) < 6) return '';
        if (strlen($tel) === 9) $tel = '51' . $tel;   // Perú
        return 'https://wa.me/' . $tel . '?text=' . rawurlencode($txt);
    }
}

if (!function_exists('supremo_codigos')) {
    /**
     * 👑 LOS CÓDIGOS LISTOS. Devuelve las tarjetas del panel: cada una con su título, su emoji, su
     * texto (lo que se copia) y —cuando toca— su «cómo se usa» de una línea.
     * Solo salen las que ya se pueden armar: la de productos necesita que los productos existan.
     */
    function supremo_codigos(array $d) {
        $out = [];
        if (supremo_negocio_id($d) <= 0) return $out;

        $out[] = [
            'clave'  => 'cabecera',
            'emoji'  => '🎨',
            'titulo' => '1 · LA CABECERA (la portada)',
            'como'   => 'Pégalo en Gemini o en Flow de imágenes CON UNA IMAGEN DE REFERENCIA ADJUNTA (de la que se copia solo el estilo).',
            'texto'  => supremo_codigo_cabecera($d),
            // 📱 La primera imagen también es UN prompt (se copia y se pega solo, igual que los productos).
            'piezas' => [[
                'titulo' => 'La cabecera',
                'nota'   => 'Con tu imagen de referencia adjunta',
                'texto'  => supremo_codigo_cabecera($d),
            ]],
        ];
        $out[] = [
            'clave'  => 'musica',
            'emoji'  => '🎵',
            'titulo' => '2 · LA MÚSICA DE LA TIENDA',
            'como'   => 'Pégalo en el generador de música de Google Flow. La letra la piensa la IA de música.',
            'texto'  => supremo_codigo_musica($d),
        ];

        // 🛍️ La carta de imágenes: los productos que llevan imagen hecha por la IA.
        $con_ia = array_map('intval', (array)($d['sup_con_ia'] ?? []));
        $prods  = [];
        $neg_id = supremo_negocio_id($d);
        if ($con_ia && $neg_id > 0) {
            $inventados = [];
            foreach ((array)($d['sup_inventados'] ?? []) as $p) { $inventados[trim((string)($p['titulo'] ?? ''))] = $p; }
            foreach (supremo_productos_negocio($neg_id) as $p) {
                if (!in_array((int)$p['id'], $con_ia, true)) continue;
                $extra = $inventados[$p['titulo']] ?? [];
                $prods[] = [
                    'id'     => (int)$p['id'],
                    'titulo' => (string)$p['titulo'],
                    'precio' => (float)$p['precio'],
                    'frase'  => (string)($extra['frase'] ?? ''),
                    'plano'  => (string)($extra['plano'] ?? ''),
                ];
            }
        }
        $txt_prod = $prods ? supremo_codigo_productos($d, $prods) : '';
        // 📱📋 LAS PIEZAS: un prompt POR PRODUCTO (de uno en uno, como lo pidió el jefe desde el
        //    celular). Es lo que el navegador muestra con ‹ › y su botón de copiar cada uno.
        $piezas = [];
        foreach ($prods as $i => $p) {
            $piezas[] = [
                'titulo' => 'Producto ' . ($i + 1) . ' de ' . count($prods) . ' · ' . (string)$p['titulo'],
                'nota'   => 'ID ' . (int)$p['id'] . ((float)$p['precio'] > 0
                                ? ' · ' . SUPREMO_MONEDA . ' ' . number_format((float)$p['precio'], 2) : ''),
                'texto'  => supremo_codigo_producto_uno($d, $p, $i + 1, count($prods)),
            ];
        }
        $out[] = [
            'clave'  => 'productos',
            'emoji'  => '🛍️',
            'titulo' => $prods ? ('3 · LAS IMÁGENES DE ' . count($prods) . ' PRODUCTO(S)') : '3 · LAS IMÁGENES DE LOS PRODUCTOS',
            'como'   => $prods
                ? ('Van de UNO EN UNO: copia el prompt de un producto, pégalo en Flow, espera su imagen '
                   . 'y pasa al siguiente. Cada prompt lleva su ID impreso dentro de la foto, así después '
                   . 'sé a qué producto va cada archivo.')
                : 'Primero hay que crear los productos (paso anterior) y este código aparece solo.',
            'texto'  => $txt_prod,
            'piezas' => $piezas,
            // 🖥️ La carta completa sigue disponible para quien la quiera de una sola vez (escritorio).
            'todos'  => $txt_prod,
        ];

        $cliente = supremo_codigo_cliente($d);
        if ($cliente !== '') {
            $wa = supremo_wa_cliente($d);
            $out[] = [
                'clave'  => 'cliente',
                'emoji'  => '📲',
                'titulo' => '4 · EL MENSAJE PARA EL CLIENTE',
                'como'   => $wa !== ''
                    ? 'Toca el botón verde: se abre WhatsApp con EL MENSAJE YA ESCRITO y su número puesto — tú solo le das enviar.'
                    : 'Cópialo y mándaselo por WhatsApp al cliente (no hay número: escríbelo donde toque).',
                'texto'  => $cliente,
                // 📲 El botón: `wa` es el enlace listo (con su número y el texto dentro).
                'wa'     => $wa,
                'wa_texto' => 'Mandarle los datos por WhatsApp',
            ];
        }
        return $out;
    }
}

/* =====================================================================================
 * 7) PUBLICAR LA TIENDA (los mismos pasos que El maestro, con el dueño desde el principio)
 * ===================================================================================== */

if (!function_exists('supremo_publicar')) {
    /**
     * 🚀 PUBLICAR LA TIENDA EN VIVO (delante del cliente).
     *
     * Es la publicación de El maestro (`tienda_ia_publicar()`: slug, rubros, fotos, descripción
     * escrita por la IA, aviso al jefe, HubSpot y su canción) con dos decisiones propias del Supremo:
     *
     *  1. **La cuenta se crea ANTES y conocemos la clave**: el vendedor está con el cliente y le
     *     tiene que poder decir «tu usuario es tu número y esta es tu clave» ahí mismo. El maestro
     *     muestra la clave solo la primera vez y aquí también (nunca se guarda en claro).
     *  2. **Siempre es una tienda NUEVA**: aunque ese número ya tenga cuenta, no se le pisa ninguna
     *     tienda suya (el maestro, si encuentra una parecida, la ACTUALIZA; aquí el vendedor está
     *     abriendo un negocio a propósito, así que se crea uno nuevo).
     */
    function supremo_publicar(array &$s, array $d) {
        $tel = preg_replace('/\D+/', '', (string)($d['whatsapp'] ?? ''));
        if (mb_strlen($tel) < 6) return ['ok' => false, 'error' => 'Necesito el WhatsApp del cliente 📱'];

        // 🔑 La cuenta del cliente (usuario = su WhatsApp). Si ya la tenía, se usa la suya.
        $cuenta = tienda_ia_cuenta_crear($tel, (string)$d['nombre'], !empty($d['prueba']));
        if (!$cuenta || empty($cuenta['id'])) {
            return ['ok' => false, 'error' => 'No pude crear la cuenta del cliente con ese número 😅'];
        }
        unset($cuenta['tienda']);   // ⛔ aquí NO se busca tienda parecida: el Supremo siempre abre una nueva

        // 📝 El relato del negocio: es el contexto del copywriting (la descripción la escribe la IA).
        if (trim((string)$d['trato']) === '') {
            $d['trato'] = supremo_relato($d);
        }
        $s['datos'] = $d;

        // ⚠️ Se publica con el id del CLIENTE como dueño, y con `id = 0` para que el motor NO toque
        //    la fila de la conversación del Supremo (esa fila sigue siendo del administrador y su
        //    paso lo guardamos nosotros después). Es la única forma de que la tienda quede a nombre
        //    del cliente y la venta siga viva en la pantalla del vendedor.
        $para = $s;
        $para['usuario_id'] = (int)$cuenta['id'];
        $para['id']         = 0;
        $para['datos']      = $d;

        // 🚀 SIN COPYWRITING AQUÍ (orden del jefe, 2026-09-18: *«ya llegué a la parte donde va a poner
        //    el número… está tardando un poquito»*): la tienda se publica **sin esperar a la IA** (el
        //    relato va como descripción provisional) y el texto bueno se escribe enseguida, en el paso
        //    de los productos, cuando ya están los 8 dentro. Así el cliente ve la tienda en el acto.
        $pub = tienda_ia_publicar($para, false, false, false, true);
        if (empty($pub['ok'])) return $pub;

        // 🔑 La clave del cliente se queda en la conversación SOLO para mostrársela una vez.
        $pub['cuenta'] = $cuenta;
        return $pub;
    }
}

if (!function_exists('supremo_relato')) {
    /** El «relato» del negocio (lo que en El maestro cuenta el dueño): aquí lo arma la IA con las fotos. */
    function supremo_relato(array $d) {
        $p = [];
        $leido = trim((string)($d['rubro_leido'] ?? ''));
        $p[] = 'Es ' . ($leido !== '' ? 'una ' . $leido : 'un negocio') . ' en ' . (string)$d['distrito_nombre'] . '.';
        $visto = [];
        foreach ((array)($d['productos_vistos'] ?? []) as $v) {
            $t = is_array($v) ? trim((string)($v['titulo'] ?? '')) : trim((string)$v);
            $t = trim(preg_replace('/\s*\(\d+\)\s*$/', '', $t));
            if ($t !== '') $visto[] = $t;
        }
        if ($visto) $p[] = 'Vende ' . implode(', ', array_slice($visto, 0, 6)) . '.';
        // ⚠️ El `comentario` de la visión NO entra aquí: es un mensaje PARA EL VENDEDOR («vi tus fotos,
        //    ¿me mandas una mejor?») y se estaba guardando como texto de la ficha del cliente. La
        //    descripción provisional tiene que poder leerse en la tienda tal cual.
        return trim(implode(' ', $p));
    }
}

/* =====================================================================================
 * 8) LOS PRODUCTOS: crearlos y ponerles sus fotos
 * ===================================================================================== */

if (!function_exists('supremo_crear_producto')) {
    /**
     * Crea UN producto en la tienda que se acaba de publicar, con la descripción que YA tenemos
     * (la de la IA que lo inventó o la que se armó con lo que se vio en las fotos).
     *
     * ⚠️ A propósito NO usa `tienda_ia_crear_producto()`: esa función le pide a la IA que escriba
     * la descripción del producto mirando su foto (una llamada por producto). En una venta en vivo
     * con el cliente delante eso son 15 segundos por producto y aquí la descripción ya la tenemos.
     */
    function supremo_crear_producto($negocio_id, $titulo, $descripcion = '', $precio = 0.0, $foto = '') {
        $negocio_id = (int)$negocio_id;
        $titulo = trim((string)$titulo);
        if ($negocio_id <= 0 || $titulo === '') return 0;
        try {
            $pdo = db();
            // Idempotente por título: si ya está en esa tienda, no se duplica (el vendedor puede
            // volver atrás y tocar «incluir» otra vez sin miedo).
            $st = $pdo->prepare("SELECT id FROM directorio_servicios WHERE negocio_id = ? AND titulo = ? LIMIT 1");
            $st->execute([$negocio_id, $titulo]);
            $ya = (int)$st->fetchColumn();
            if ($ya > 0) return $ya;

            $pdo->prepare("INSERT INTO directorio_servicios
                (negocio_id, titulo, tipo_producto, descripcion, precio, unidad, imagen, destacado, activo)
                VALUES (?,?,?,?,?,?,?,0,1)")
                ->execute([$negocio_id, $titulo, 'fisico',
                           ($descripcion !== '' ? $descripcion : null),
                           (float)$precio, 'unidad', ($foto !== '' ? $foto : null)]);
            $pid = (int)$pdo->lastInsertId();
            if ($foto !== '' && $pid > 0) {
                $pdo->prepare("INSERT INTO directorio_producto_fotos (producto_id, ruta, orden) VALUES (?,?,0)")
                    ->execute([$pid, (string)$foto]);
            }
            try {
                require_once __DIR__ . '/fuzzy_cache.php';
                fuzzy_olvidar_cache();
            } catch (Throwable $e) {}
            return $pid;
        } catch (Throwable $e) {
            error_log('supremo_crear_producto: ' . $e->getMessage());
            return 0;
        }
    }
}

if (!function_exists('supremo_portada_poner')) {
    /**
     * 🖼️ Pone la portada: la foto elegida pasa a ser la PRIMERA de la galería (orden 0) y las demás
     * se corren. No borra nada: la portada vieja se queda como una foto más de la tienda.
     */
    function supremo_portada_poner($negocio_id, $rel) {
        $negocio_id = (int)$negocio_id;
        $rel = ltrim((string)$rel, '/');
        if ($negocio_id <= 0 || $rel === '') return false;
        try {
            $pdo = db();
            $st = $pdo->prepare("SELECT id FROM directorio_fotos WHERE negocio_id = ? AND ruta = ? LIMIT 1");
            $st->execute([$negocio_id, $rel]);
            $fid = (int)$st->fetchColumn();
            $pdo->prepare("UPDATE directorio_fotos SET orden = orden + 1 WHERE negocio_id = ?")->execute([$negocio_id]);
            if ($fid > 0) {
                $pdo->prepare("UPDATE directorio_fotos SET orden = 0, descripcion = 'Fachada' WHERE id = ?")->execute([$fid]);
            } else {
                $pdo->prepare("INSERT INTO directorio_fotos (negocio_id, ruta, descripcion, orden) VALUES (?,?,'Fachada',0)")
                    ->execute([$negocio_id, $rel]);
            }
            return true;
        } catch (Throwable $e) {
            error_log('supremo_portada_poner: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('supremo_foto_producto')) {
    /** 📷 Le pone la imagen a un producto (y la deja como su principal). */
    function supremo_foto_producto($producto_id, $rel) {
        $producto_id = (int)$producto_id;
        $rel = ltrim((string)$rel, '/');
        if ($producto_id <= 0 || $rel === '') return false;
        try {
            $pdo = db();
            $st = $pdo->prepare("SELECT COUNT(*) FROM directorio_producto_fotos WHERE producto_id = ? AND ruta = ?");
            $st->execute([$producto_id, $rel]);
            if ((int)$st->fetchColumn() === 0) {
                $o = $pdo->prepare("SELECT COALESCE(MAX(orden), -1) + 1 FROM directorio_producto_fotos WHERE producto_id = ?");
                $o->execute([$producto_id]);
                $orden = (int)$o->fetchColumn();
                $pdo->prepare("INSERT INTO directorio_producto_fotos (producto_id, ruta, orden) VALUES (?,?,?)")
                    ->execute([$producto_id, $rel, $orden]);
            }
            $pdo->prepare("UPDATE directorio_servicios SET imagen = ? WHERE id = ?")->execute([$rel, $producto_id]);
            return true;
        } catch (Throwable $e) {
            error_log('supremo_foto_producto: ' . $e->getMessage());
            return false;
        }
    }
}

if (!function_exists('supremo_imagenes_publicar')) {
    /**
     * 📥 LA SALA DE ESPERA: llegan las imágenes del diseñador y cada una se va a su sitio.
     *
     * Cómo se sabe qué es cada archivo: **se lee el número que trae impreso dentro** (los códigos lo
     * piden así). La que trae el nombre del negocio en grande es la **portada**. Si la IA no pudo
     * leer el número de alguna, esa imagen **no se publica**: se le dice al vendedor cuál falló para
     * que la vuelva a pedir (nunca se adivina: publicar la foto equivocada en un producto es peor
     * que no tenerla).
     *
     * @return array ['ok'=>bool,'puestas'=>[['que'=>..,'titulo'=>..]],'fallaron'=>[nombres]]
     */
    function supremo_imagenes_publicar(array &$s, array $rels, array $nombres = []) {
        $d      = $s['datos'];
        $neg_id = supremo_negocio_id($d);
        if ($neg_id <= 0) return ['ok' => false, 'error' => 'Todavía no hay tienda publicada.'];

        $prods = supremo_productos_negocio($neg_id);
        $por_id = [];
        foreach ($prods as $p) { $por_id[(int)$p['id']] = $p; }
        $ids = array_keys($por_id);

        // Lo que ya llegó antes (para no repetir y para saber qué falta).
        $ya = [];
        foreach ((array)($d['sup_imagenes'] ?? []) as $im) {
            $ya[] = (int)($im['id'] ?? 0) . '|' . (string)($im['que'] ?? '');
        }

        $leidos = supremo_ia_leer_ids($rels, $ids, (string)$d['nombre'],
            ['sesion' => (int)$s['id'], 'usuario' => (int)$s['usuario_id']]);

        $puestas = []; $fallaron = []; $portada_puesta = false;
        foreach ($rels as $i => $rel) {
            $nombre_arch = (string)($nombres[$i] ?? ('imagen ' . ($i + 1) . '.jpg'));
            $info = null;
            foreach ($leidos as $l) { if ((int)$l['imagen'] === $i) { $info = $l; break; } }

            // 🖼️ ¿Es la portada? Lo dice la IA (vio el nombre grande) o, si no, cuando el vendedor la
            // eligió como portada antes y esta es la única imagen de la tanda que no es de un producto.
            $es_portada = $info && (!empty($info['portada']) || (int)$info['id'] <= 0 && !empty($info['portada']));
            if (!$portada_puesta && $es_portada) {
                if (supremo_portada_poner($neg_id, $rel)) {
                    $puestas[] = ['que' => 'portada', 'titulo' => 'La portada de la tienda', 'archivo' => $rel];
                    $portada_puesta = true;
                    continue;
                }
            }
            $pid = $info ? (int)$info['id'] : 0;
            if ($pid > 0 && isset($por_id[$pid])) {
                if (supremo_foto_producto($pid, $rel)) {
                    $puestas[] = ['que' => 'producto', 'titulo' => (string)$por_id[$pid]['titulo'],
                                  'id' => $pid, 'archivo' => $rel];
                    continue;
                }
            }
            $fallaron[] = $nombre_arch;
        }
        return ['ok' => true, 'puestas' => $puestas, 'fallaron' => $fallaron];
    }
}

/* =====================================================================================
 * 8-bis) 💰 LA OFERTA: LA CUENTA QUE CIERRA LA VENTA
 * =====================================================================================
 * Lo que se le vende al dueño NO es «una página web»: es **resultado con riesgo cero**
 * (*«50 ventas en 30 días; el primer mes es gratis; si no las logro, no pagas; si funciona,
 * pagas S/ 20 al mes»*). Y lo que hace que lo entienda en 30 segundos es **ver los números
 * delante de él**: el precio de SU producto, multiplicado por 50, contra el 10 % que le
 * cobraría cualquiera.
 *
 * ⚠️ Aquí NO hay ni una llamada de IA: es aritmética. Por eso se puede recalcular cuantas
 *    veces haga falta con el cliente mirando, sin gastar y sin esperar.
 * ===================================================================================== */

if (!function_exists('supremo_calculo')) {
    /**
     * La cuenta de la oferta. Devuelve todos los números listos para pintar:
     * el ingreso, la comisión del 10 %, la tarifa, el ahorro, la **comisión efectiva** (%),
     * cuántas ventas cubren la tarifa (el punto de equilibrio) y cuántas veces la tarifa cabe
     * en lo que vendió.
     */
    function supremo_calculo($precio, $ventas = null) {
        $precio = round(max(0.0, (float)$precio), 2);
        $ventas = ($ventas === null) ? (int)SUPREMO_OFERTA_VENTAS : max(1, (int)$ventas);
        $tarifa = (float)SUPREMO_OFERTA_TARIFA;
        $pct    = (float)SUPREMO_OFERTA_COMISION;

        $ingreso  = $precio * $ventas;
        $comision = $ingreso * $pct / 100;
        $ahorro   = $comision - $tarifa;
        $efectiva = $ingreso > 0 ? ($tarifa / $ingreso * 100) : 0.0;
        // 📐 ¿Con cuántas ventas ya se paga sola mi tarifa? (se redondea hacia arriba: 2,5 → 3)
        $cubre    = ($precio > 0 && $pct > 0) ? (int)ceil($tarifa / ($precio * $pct / 100)) : 0;
        $veces    = $tarifa > 0 ? ($ingreso / $tarifa) : 0.0;

        return [
            'precio'   => $precio,
            'ventas'   => $ventas,
            'ingreso'  => $ingreso,
            'comision' => $comision,
            'tarifa'   => $tarifa,
            'pct'      => $pct,
            'ahorro'   => $ahorro,
            'efectiva' => $efectiva,
            'cubre'    => $cubre,
            'veces'    => $veces,
            'sirve'    => ($precio > 0 && $ahorro > 0),
            // 🎯 ¿La cuenta LUCE? Con un producto muy barato mi tarifa se come el porcentaje
            //    (S/ 8 con 50 ventas = 5 %) y delante del cliente eso no convence. Cuando pasa,
            //    se le dice al vendedor qué precio mínimo necesita.
            'ideal'    => (float)SUPREMO_OFERTA_EFECTIVA_IDEAL,
            'luce'     => ($ingreso > 0 && ($tarifa / $ingreso * 100) <= (float)SUPREMO_OFERTA_EFECTIVA_IDEAL),
            'precio_min' => (float)SUPREMO_OFERTA_EFECTIVA_IDEAL > 0
                ? ceil(($tarifa / ((float)SUPREMO_OFERTA_EFECTIVA_IDEAL / 100)) / max(1, $ventas))
                : 0,
            'moneda'   => SUPREMO_MONEDA,
            'dias'     => (int)SUPREMO_OFERTA_DIAS,
        ];
    }
}

if (!function_exists('supremo_precio_de')) {
    /**
     * Saca el precio de lo que el vendedor escribió («S/ 80», «80 soles», «1,200»).
     * En Perú la coma es de miles y el punto es decimal, así que la coma se quita.
     */
    function supremo_precio_de($texto) {
        $t = str_replace(',', '', (string)$texto);
        $t = preg_replace('/[^0-9.]/', '', $t);
        if ($t === '' || $t === '.') return 0.0;
        // Si quedaron dos puntos («80.5.2»), se toma solo el primero como decimal.
        $partes = explode('.', $t);
        if (count($partes) > 2) $t = $partes[0] . '.' . $partes[1];
        $n = (float)$t;
        // Un precio de más de 100 000 soles en una tienda de barrio es un error de tipeo: se ignora.
        return ($n > 0 && $n <= 100000) ? round($n, 2) : 0.0;
    }
}

if (!function_exists('supremo_soles')) {
    /** Un número en soles, como se escribe aquí: S/ 4,000.00 */
    function supremo_soles($n) {
        return SUPREMO_MONEDA . ' ' . number_format((float)$n, 2, '.', ',');
    }
}

if (!function_exists('supremo_frase_venta')) {
    /**
     * 🗣️ LA FRASE PARA DECIR EN VOZ ALTA, con los números del cliente dentro.
     * Es la que pidió el jefe en su guion: precio → 50 ventas → lo que genera → el 10 % que
     * cobrarían otros → los S/ 20 → y la garantía.
     */
    function supremo_frase_venta(array $c) {
        if (empty($c['sirve'])) {
            return 'Dime el precio de un producto y te hago la cuenta delante de ti.';
        }
        return 'Si este producto cuesta ' . supremo_soles($c['precio']) . ' y vendemos ' . (int)$c['ventas']
             . ', generas ' . supremo_soles($c['ingreso']) . '. Si me pagaras ' . rtrim(rtrim(number_format($c['pct'], 2, '.', ''), '0'), '.')
             . '% por venta, serían ' . supremo_soles($c['comision']) . '. Yo no te cobro eso: te cobro solo '
             . supremo_soles($c['tarifa']) . ' al mes, que es '
             . number_format($c['efectiva'], 2) . '% de lo que vendes. Y si no vendo ' . (int)$c['ventas']
             . ', no pagas nada.';
    }
}

if (!function_exists('supremo_oferta_texto')) {
    /**
     * 📄 LA OFERTA POR ESCRITO (lo que el jefe lee en la tienda o le manda por WhatsApp).
     * Sale con los datos y los números de ESA tienda dentro, y con las reglas que evitan que lo
     * quemen después (qué cuenta como venta, quién entrega, el stock, los precios).
     *
     * 🔴 Todo va en un solo texto para que se copie **de un clic** (regla inviolable n.º 1:
     *    al jefe no se le pide seleccionar nada).
     */
    function supremo_oferta_texto(array $d, array $c) {
        $neg   = (array)($d['sup_negocio'] ?? []);
        $tienda = trim((string)$d['nombre']) !== '' ? (string)$d['nombre'] : 'tu tienda';
        $prod  = trim((string)($d['sup_producto'] ?? ''));
        $L = [];
        $L[] = 'CONVIERTO ' . mb_strtoupper($tienda) . ' EN UNA TIENDA ONLINE ATENDIDA POR WHATSAPP';
        $L[] = '';
        $L[] = 'LO QUE TE DOY';
        $L[] = '· Tu tienda online con tus productos, tus precios y tus fotos, ya publicada en DeChimbote.com'
             . (!empty($neg['url']) ? ' (' . (string)$neg['url'] . ')' : '') . '.';
        $L[] = '· Yo recibo a los clientes y les contesto por WhatsApp, incluso cuando tu local está cerrado.';
        $L[] = '· Yo cierro la venta: pregunto, confirmo y te paso el pedido listo.';
        $L[] = '';
        $L[] = 'LA PROMESA';
        $L[] = (int)$c['ventas'] . ' ventas en ' . (int)$c['dias'] . ' días'
             . ($prod !== '' ? ' de ' . $prod : ' (del producto o productos que acordemos contigo)') . '.';
        $L[] = '';
        $L[] = 'LA GARANTÍA';
        $L[] = 'El primer mes es gratis. Si en ' . (int)$c['dias'] . ' días no llego a las ' . (int)$c['ventas']
             . ' ventas, no pagas nada.';
        $L[] = 'Si llego, pagas solo ' . supremo_soles($c['tarifa']) . ' al mes.';
        $L[] = '';
        $L[] = 'LO QUE NO TE CUESTA';
        if (!empty($c['sirve'])) {
            $L[] = 'Con ' . ($prod !== '' ? $prod : 'un producto') . ' de ' . supremo_soles($c['precio']) . ':';
            $L[] = '· ' . (int)$c['ventas'] . ' ventas = ' . supremo_soles($c['ingreso']) . ' para ti.';
            $L[] = '· Al ' . rtrim(rtrim(number_format($c['pct'], 2, '.', ''), '0'), '.') . ' % por venta serían '
                 . supremo_soles($c['comision']) . '.';
            $L[] = '· Conmigo: ' . supremo_soles($c['tarifa']) . ' al mes. Te ahorras ' . supremo_soles($c['ahorro']) . '.';
            $L[] = '· Mi comisión real: ' . number_format($c['efectiva'], 2) . ' % de lo que vendes'
                 . ($c['cubre'] > 0 ? ' (con ' . (int)$c['cubre'] . ' ventas al mes ya me pagas y el resto es tuyo)' : '') . '.';
        } else {
            $L[] = 'No es un porcentaje por venta: es ' . supremo_soles($c['tarifa']) . ' al mes, pase lo que pase.';
            $L[] = 'Si vendemos mucho, tú ganas más.';
        }
        $L[] = '';
        $L[] = 'QUÉ NECESITO DE TI';
        $L[] = '· Tu catálogo con precios y tu stock real (para no ofrecer lo que no tienes).';
        $L[] = '· Tu horario y quién entrega: tú, yo o un delivery.';
        $L[] = '· Avisarme si cambias un precio, para no ofrecer un precio viejo.';
        $L[] = '';
        $L[] = 'QUÉ CUENTA COMO VENTA';
        $L[] = 'Un pedido pagado, entregado y no devuelto, dentro de los ' . (int)$c['dias']
             . ' días y fuera del horario que tú ya atiendes. Si no hay stock, esa venta no cuenta.';
        $L[] = '';
        $L[] = 'EMPIEZA ASÍ';
        $L[] = 'Elegimos UN producto de tu local, miramos la cuenta y arrancamos gratis. '
             . 'Si funciona, seguimos. Si no, no pagas nada.';
        return implode("\n", $L);
    }
}

if (!function_exists('supremo_guion_venta')) {
    /**
     * 🗣️ EL GUION DE LOS 6 PASOS, con los números del cliente ya dentro.
     * Se lee tal cual, uno por paso, sin prisa.
     */
    function supremo_guion_venta(array $d, array $c) {
        $prod = trim((string)($d['sup_producto'] ?? ''));
        $prod = $prod !== '' ? $prod : 'este producto';
        $L = [];
        $L[] = 'GUION DE VENTA EN 6 PASOS (uno por paso, sin prisa)';
        $L[] = '';
        $L[] = '1. ELIJO UN PRODUCTO VISIBLE';
        $L[] = '"¿Cuánto cuesta ' . $prod . '?" (señalo uno concreto del local)';
        $L[] = '';
        $L[] = '2. PREGUNTO EL PRECIO Y LO REPITO';
        $L[] = '"O sea, ' . supremo_soles($c['precio']) . ' cada uno, ¿no?"';
        $L[] = '';
        $L[] = '3. CALCULO EN VOZ ALTA';
        $L[] = '"Si vendemos ' . (int)$c['ventas'] . ' de estos en un mes, son ' . (int)$c['ventas'] . ' por '
             . number_format($c['precio'], 2, '.', ',') . '… ' . supremo_soles($c['ingreso']) . ' para ti."';
        $L[] = '';
        $L[] = '4. COMPARO CON LA COMISIÓN DE SIEMPRE';
        $L[] = '"Si me pagaras como las otras páginas, ' . rtrim(rtrim(number_format($c['pct'], 2, '.', ''), '0'), '.')
             . '% por venta, serían ' . supremo_soles($c['comision']) . '.';
        $L[] = ' Yo no te cobro eso: te cobro ' . supremo_soles($c['tarifa']) . ' al mes. Es '
             . number_format($c['efectiva'], 2) . '%. Nada."';
        $L[] = '';
        $L[] = '5. PONGO LA GARANTÍA';
        $L[] = '"Y si no vendo las ' . (int)$c['ventas'] . ', no me pagas. Así de simple."';
        $L[] = '';
        $L[] = '6. CIERRO CON LA PRUEBA';
        $L[] = '"El primer mes es gratis. Probemos con ' . $prod . ' o con el que tú quieras.';
        $L[] = ' Si funciona, seguimos; si no, no pagas nada. ¿Qué producto elegimos?"';
        return implode("\n", $L);
    }
}

if (!function_exists('supremo_reglas_texto')) {
    /** 📋 LAS CONDICIONES POR ESCRITO (las que evitan que lo quemen después). */
    function supremo_reglas_texto(array $d, array $c) {
        $L = [];
        $L[] = 'CONDICIONES DE LA OFERTA (se acuerdan antes de empezar)';
        $L[] = '';
        $L[] = '1. VENTA VÁLIDA: pedido pagado, entregado y no devuelto.';
        $L[] = '2. PLAZO: ' . (int)$c['dias'] . ' días calendario desde el día que arrancamos.';
        $L[] = '3. HORARIO: cuentan las ventas fuera del horario en que el dueño ya atiende.';
        $L[] = '4. STOCK: si no hay stock, esa venta no cuenta.';
        $L[] = '5. PRECIO: el dueño no lo cambia sin avisar (si no, la promesa se recalcula).';
        $L[] = '6. ENTREGA: se define quién entrega (el dueño, yo o un delivery) antes de empezar.';
        $L[] = '7. WHATSAPP: yo contesto, pero necesito el catálogo, los precios y el stock al día.';
        $L[] = '8. PRIMER MES: gratis. Si llego a las ' . (int)$c['ventas'] . ' ventas, empieza a pagar desde el mes 2.';
        $L[] = '9. SI NO LLEGO: no paga nada y no sigue.';
        $L[] = '10. QUÉ SE ACUERDA POR ESCRITO: qué producto o productos entran y a qué precio.';
        return implode("\n", $L);
    }
}

/* =====================================================================================
 * 8-ter) 🎵 LA CANCIÓN: SUBIRLA DESDE EL SUPREMO (lo que pidió el jefe, 2026-09-18)
 * =====================================================================================
 * *«No hay modo de subir la canción descargada al Supremo.»*
 *
 * Hasta hoy la canción que salía de Google Flow (el mp3 que el jefe baja) la publicaba un AGENTE
 * con `__cancion_uno.py` (convertía a OGG y la subía). El jefe quiere **subirla él mismo**, ahí
 * mismo, sin depender de nadie: se sube el archivo y la tienda ya la tiene sonando.
 *
 * Cómo queda:
 *   · se reutiliza **el motor de canciones del sitio** (`cancion_guardar_audio()`: la deja en
 *     `assets/uploads/canciones/cancion-<slug>-<id>.<ext>` y **mide** su duración de verdad),
 *   · se guarda la fila en **`directorio_canciones`** con `estado = 'listo'` — que es lo que hace
 *     que el reproductor de la ficha (`cancion_de_negocio()`) la encuentre,
 *   · **NO se llama a Treblo**: no gasta ni un crédito (esta es la vía gratis),
 *   · el audio **se guarda tal cual llegó** (orden del jefe: *«no pierdas tiempo recortando ni
 *     reeditando»*), sin recortar ni recomprimir.
 * ===================================================================================== */

if (!function_exists('supremo_cancion_info')) {
    /** ¿Esta tienda ya tiene canción puesta? Devuelve la fila (ruta, duración, cuándo) o null. */
    function supremo_cancion_info($negocio_id) {
        $negocio_id = (int)$negocio_id;
        if ($negocio_id <= 0) return null;
        require_once __DIR__ . '/cancion.php';
        if (!function_exists('cancion_de_negocio')) return null;
        try {
            $c = cancion_de_negocio($negocio_id);
            if (!$c) return null;
            return [
                'ruta'     => (string)$c['ruta'],
                'url'      => img_url((string)$c['ruta']),
                'duracion' => (float)($c['duracion'] ?? 0),
                'creado'   => (string)($c['creado_en'] ?? ''),
            ];
        } catch (Throwable $e) { return null; }
    }
}

if (!function_exists('supremo_cancion_subir')) {
    /**
     * 🎵 Sube el audio que bajó el jefe y se lo pone a la tienda.
     *
     * @param array $s        la conversación del Supremo (para saber la tienda y su nombre)
     * @param array $archivo  la entrada de `$_FILES` del audio
     * @return array ['ok'=>bool,'error'=>?string,'ruta'=>?string,'duracion'=>?float,'bytes'=>?int,'peso_mb'=>?float]
     */
    function supremo_cancion_subir(array $s, $archivo) {
        $d      = $s['datos'];
        $neg_id = supremo_negocio_id($d);
        if ($neg_id <= 0) return ['ok' => false, 'error' => 'Todavía no hay tienda publicada: primero se publica y después le subes su canción.'];

        require_once __DIR__ . '/cancion.php';
        if (!function_exists('cancion_guardar_audio') || !cancion_tablas_ok()) {
            return ['ok' => false, 'error' => 'El módulo de canciones no está instalado en el sitio.'];
        }

        // ---- 1) ¿llegó un archivo sano? (nunca se confía en el navegador) ----
        if (!is_array($archivo) || !isset($archivo['tmp_name'])) {
            return ['ok' => false, 'error' => 'No llegó el audio. Toca el botón y elige la canción que bajaste.'];
        }
        if ((int)($archivo['error'] ?? 1) !== UPLOAD_ERR_OK) {
            $cod = (int)($archivo['error'] ?? -1);
            $motivo = ($cod === UPLOAD_ERR_INI_SIZE || $cod === UPLOAD_ERR_FORM_SIZE)
                ? 'Pesa más de lo que el hosting acepta (' . supremo_audio_max_mb() . ' MB). Prueba con una canción más corta o en mp3.'
                : 'No se pudo subir el audio (código ' . $cod . ').';
            return ['ok' => false, 'error' => $motivo];
        }
        $ext = strtolower((string)pathinfo((string)($archivo['name'] ?? ''), PATHINFO_EXTENSION));
        if (!in_array($ext, supremo_audio_exts(), true)) {
            return ['ok' => false, 'error' => 'Ese archivo no es un audio (' . ($ext !== '' ? '.' . $ext : 'sin extensión')
                   . '). Se aceptan: ' . implode(', ', supremo_audio_exts()) . '.'];
        }
        $peso = (int)($archivo['size'] ?? 0);
        if ($peso <= 0) return ['ok' => false, 'error' => 'El audio llegó vacío.'];
        if ($peso > supremo_audio_max_mb() * 1024 * 1024) {
            return ['ok' => false, 'error' => 'El audio pesa ' . round($peso / 1048576, 1) . ' MB y el tope es '
                   . supremo_audio_max_mb() . ' MB.'];
        }

        // ---- 2) al temporal de canciones (misma carpeta que usa el módulo) ----
        $carpeta = rtrim(CANCION_CARPETA, '/') . '/tmp';
        $abs     = dirname(__DIR__) . '/' . $carpeta;
        if (!is_dir($abs)) @mkdir($abs, 0755, true);
        if (!is_dir($abs) || !is_writable($abs)) {
            return ['ok' => false, 'error' => 'La carpeta de canciones no tiene permisos de escritura.'];
        }
        $tmp = $abs . '/subiendo-' . getmypid() . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
        $movido = is_uploaded_file((string)$archivo['tmp_name'])
            ? @move_uploaded_file((string)$archivo['tmp_name'], $tmp)
            : @copy((string)$archivo['tmp_name'], $tmp);   // las SONDAS usan un archivo del servidor
        if (!$movido) return ['ok' => false, 'error' => 'No se pudo guardar el audio en el servidor.'];

        // ---- 3) el motor de canciones la deja en su sitio (y MIDE su duración) ----
        $final = cancion_guardar_audio($neg_id, (string)$d['nombre'], $tmp);
        @unlink($tmp);
        if (empty($final['ruta'])) {
            return ['ok' => false, 'error' => 'No se pudo archivar el audio. Prueba otra vez (o mándalo en mp3).'];
        }
        // 🛡️ ¿Es audio de verdad? Si no se le pudo medir la duración, no es una canción.
        if ((float)($final['duracion'] ?? 0) <= 0.5) {
            try { img_borrar((string)$final['ruta']); } catch (Throwable $e) {}
            return ['ok' => false, 'error' => 'Ese archivo no suena (no se le pudo medir la duración). Mándame el mp3 o el ogg que bajaste.'];
        }

        // ---- 4) la fila: 'listo' = la ficha ya la reproduce ----
        try {
            $pdo = db();
            $st = $pdo->prepare("SELECT id, ruta FROM " . CANCION_TABLA . " WHERE negocio_id = ? ORDER BY id DESC LIMIT 1");
            $st->execute([$neg_id]);
            $previa = $st->fetch(PDO::FETCH_ASSOC) ?: null;
            $campos = [
                'estado'   => 'listo',
                'ruta'     => (string)$final['ruta'],
                'duracion' => (float)$final['duracion'],
                'bytes'    => (int)$final['bytes'],
                'error'    => null,
            ];
            if ($previa) {
                cancion_fila_guardar((int)$previa['id'], $campos);
                // 🧹 La canción anterior se borra del disco (si era otra): no se deja basura.
                $vieja = ltrim((string)($previa['ruta'] ?? ''), '/');
                if ($vieja !== '' && $vieja !== (string)$final['ruta']) {
                    try { img_borrar($vieja); } catch (Throwable $e) {}
                }
            } else {
                $pdo->prepare("INSERT INTO " . CANCION_TABLA . "
                    (negocio_id, nombre, rubro, distrito, estado, prompt, letra, ruta, duracion, bytes, creado_en, actualizado_en)
                    VALUES (?,?,?,?,'listo',?,?,?,?,?,NOW(),NOW())")
                    ->execute([
                        $neg_id,
                        mb_substr((string)$d['nombre'], 0, 160),
                        mb_substr((string)$d['rubro_nombre'], 0, 120),
                        mb_substr((string)$d['distrito_nombre'], 0, 80),
                        'Subida a mano desde El Supremo (la hizo el jefe en Flow).',
                        '',
                        (string)$final['ruta'],
                        (float)$final['duracion'],
                        (int)$final['bytes'],
                    ]);
            }
        } catch (Throwable $e) {
            error_log('supremo_cancion_subir: ' . $e->getMessage());
            return ['ok' => false, 'error' => 'El audio se guardó pero no pude apuntarlo en la tienda.'];
        }

        return ['ok' => true, 'ruta' => (string)$final['ruta'], 'duracion' => (float)$final['duracion'],
                'bytes' => (int)$final['bytes'], 'peso_mb' => round(((int)$final['bytes']) / 1048576, 2)];
    }
}

if (!function_exists('supremo_audio_exts')) {
    /** Los formatos de audio que se aceptan (los mismos que usa la herramienta del agente). */
    function supremo_audio_exts() {
        return ['mp3', 'm4a', 'aac', 'ogg', 'oga', 'opus', 'wav', 'flac', 'wma'];
    }
}

if (!function_exists('supremo_audio_max_mb')) {
    /**
     * El tope REAL de subida de este servidor, en MB: se mira lo que permite PHP y se deja un
     * margen (el `post_max_size` incluye el formulario, así que se toma el más pequeño menos 1 MB).
     */
    function supremo_audio_max_mb() {
        $a_mb = function ($v) {
            $v = trim((string)$v);
            if ($v === '') return 0.0;
            $n = (float)$v;
            $u = strtolower(substr($v, -1));
            if ($u === 'g') $n *= 1024;
            elseif ($u === 'm') $n *= 1;
            elseif ($u === 'k') $n /= 1024;
            return $n;
        };
        $up   = $a_mb(ini_get('upload_max_filesize'));
        $post = $a_mb(ini_get('post_max_size'));
        $tope = min(array_filter([$up, $post, 20.0]));   // 20 MB es el techo de la casa
        if ($tope <= 0) $tope = 8.0;
        return (int)max(1, floor($tope - 1));
    }
}

/* =====================================================================================
 * 8-bis) 🔁 LOS DATOS QUE SE TOCAN FUERA DEL FLUJO, LA UBICACIÓN Y «PIENSA MEJOR»
 * ===================================================================================== */

if (!function_exists('supremo_volver')) {
    /**
     * 🔁 ¿A DÓNDE SE VUELVE DESPUÉS DE TOCAR UN DATO SUELTO?
     *
     * Un dato se puede tocar **en cualquier momento** (el menú ⋯ y el botón 🧠 «Piensa mejor»):
     * el paso se abre, el dato se corrige y hay que **volver justo a donde estaba la venta**.
     * Al abrir ese paso suelto se guarda el paso de origen en `sup_volver`; aquí se lee y se
     * borra (para que no se quede pegado en la próxima vuelta).
     *
     * Sin nada guardado, el flujo de siempre: el WhatsApp del cliente.
     */
    function supremo_volver(array &$d, $por_defecto = 'cliente') {
        $v = (string)($d['sup_volver'] ?? '');
        $d['sup_volver'] = '';
        $pasos = supremo_pasos();
        return ($v !== '' && isset($pasos[$v])) ? $v : $por_defecto;
    }
}

if (!function_exists('supremo_ubicacion_ahora')) {
    /**
     * 📍 COMPARTIR LA UBICACIÓN EN CUALQUIER MOMENTO (orden del jefe, 2026-09-18: *«eso es todo, en el
     * menú de opciones poner ahí también el acceso directo a compartir ubicación, así se puede compartir
     * ubicación en cualquier momento»*).
     *
     * · Si la venta está en el paso de la ubicación (o en «cambiar la zona»), manda al motor: es el
     *   camino normal y sigue solo a las fotos.
     * · En cualquier otro paso **NO mueve la venta de sitio**: guarda el punto, saca el distrito del
     *   GPS y —si la tienda ya está publicada— lo corrige en la tienda de verdad.
     *
     * @return array los mensajes que hay que pintar en el chat
     */
    function supremo_ubicacion_ahora(array $s, $lat, $lng) {
        $lat = (float)$lat; $lng = (float)$lng;
        $paso = (string)$s['paso'];
        if ($paso === 'ubicacion' || $paso === 'distrito') {
            $r = supremo_recibir($s, 'gps', '', 'gps', $lat, $lng, []);
            return (array)($r['mensajes'] ?? []);
        }

        $d = $s['datos'];
        $d['lat'] = $lat; $d['lng'] = $lng;
        $msgs = [];
        $dd = tienda_ia_distrito_por_gps($lat, $lng);
        $antes = (string)($d['distrito_nombre'] ?? '');
        if ($dd) {
            $d['distrito_id']     = (int)$dd['distrito_id'];
            $d['distrito_nombre'] = (string)$dd['nombre'];
        }
        // 🏬 Si la tienda YA está publicada, el dato tiene que quedar escrito en su ficha (no solo
        //    en la conversación): es la ubicación con la que la ve el cliente final.
        $neg_id = supremo_negocio_id($d);
        $guardado = false;
        if ($neg_id > 0) {
            try {
                db()->prepare("UPDATE directorio_negocios SET lat = ?, lng = ?, distrito_id = COALESCE(?, distrito_id) WHERE id = ?")
                    ->execute([$lat, $lng, ($dd ? (int)$dd['distrito_id'] : null), $neg_id]);
                $guardado = true;
            } catch (Throwable $e) { error_log('supremo_ubicacion_ahora: ' . $e->getMessage()); }
        }
        $s['datos'] = $d;
        tienda_ia_guardar($s);

        if ($dd && $antes !== '' && $antes !== (string)$dd['nombre']) {
            $msgs[] = ['rol' => 'bot', 'texto' => "📍 **Ubicación corregida:** están en **" . (string)$dd['nombre']
                     . '** (antes decía ' . $antes . ').' . ($guardado ? ' Ya lo dejé escrito en su ficha 👌' : '')];
        } elseif ($dd) {
            $msgs[] = ['rol' => 'bot', 'texto' => "📍 **Ubicación guardada:** están en **" . (string)$dd['nombre'] . '**.'
                     . ($guardado ? ' Ya quedó anotada en la ficha de la tienda 👌' : '')];
        } else {
            $msgs[] = ['rol' => 'bot', 'texto' => '📍 Guardé el punto exacto 👌 El distrito no lo pude sacar del GPS: '
                     . 'si quieres lo tocas en el menú ⋯ → **📍 Compartir ubicación**.'];
        }
        return $msgs;
    }
}

if (!function_exists('supremo_zona_abrir')) {
    /**
     * 📍 ABRIR «CAMBIAR LA ZONA» SIN PERDER EL SITIO.
     * Deja la venta en el paso `distrito` (el botón de ubicación + los distritos en tarjetas) y
     * apunta de dónde vino para volver ahí mismo cuando elija.
     */
    function supremo_zona_abrir(array $s) {
        $d = $s['datos'];
        $paso = (string)$s['paso'];
        $pasos = supremo_pasos();
        // Si la venta todavía no ha salido del arranque, al volver se sigue por la ubicación.
        if ($paso === '' || in_array($paso, ['arranque', 'ayuda'], true) || $paso === 'distrito') {
            $d['sup_volver'] = $paso === 'distrito' ? 'cliente' : 'ubicacion';
        } else {
            $d['sup_volver'] = $paso;
        }
        $s['paso'] = 'distrito'; $s['datos'] = $d;
        tienda_ia_guardar($s);
        $g = supremo_guion('distrito', $d);
        return ['ok' => true, 'tipo' => (string)$g['tipo'], 'opciones' => (array)$g['opciones'],
                'mensajes' => [['rol' => 'bot', 'texto' => "📍 **Cambiar la zona.** Toca el botón de la ubicación "
                             . 'o el distrito, y te devuelvo a donde estábamos 👇']]];
    }
}

if (!function_exists('supremo_piensa_abrir')) {
    /**
     * 🧠 ABRIR «PIENSA MEJOR» DESDE CUALQUIER PUNTO DE LA VENTA (el botón 🧠 de la cabecera y el menú ⋯).
     *
     * Se guarda **de dónde vino** (`sup_piensa_volver`) para que al terminar de revisar la tienda la
     * venta siga exactamente donde estaba: el vendedor puede tocar 🧠 en cualquier momento sin perder
     * el hilo delante del cliente.
     */
    function supremo_piensa_abrir(array $s) {
        $d = $s['datos'];
        $paso = (string)$s['paso'];
        if ($paso !== 'sup_piensa') {
            $volver = (isset(supremo_pasos()[$paso]) && !in_array($paso, ['arranque', 'ayuda', 'sup_fin'], true)) ? $paso : '';
            $d['sup_piensa_volver'] = $volver;
        }
        $s['paso'] = 'sup_piensa'; $s['datos'] = $d;
        tienda_ia_guardar($s);
        return ['ok' => true];
    }
}


if (!function_exists('supremo_lo_que_falta')) {
    /**
     * 🔎 LO QUE LE FALTA A LA TIENDA, MEDIDO AQUÍ MISMO (sin gastar IA).
     * Es la lista que usa «Piensa mejor» para pedir los datos que quedaron flojos: son **hechos**
     * (campos vacíos, fotos que no llegaron, productos que faltan), no la opinión del modelo.
     *
     * @return array [clave => ['texto','paso','valor','nota']]
     */
    function supremo_lo_que_falta(array $d) {
        $f = [];
        $neg_id = supremo_negocio_id($d);

        if (trim((string)($d['distrito_nombre'] ?? '')) === '') {
            $f['zona'] = ['texto' => '📍 Poner la zona', 'paso' => 'distrito', 'valor' => '',
                          'nota' => 'es lo que usa «cerca de mí»'];
        }
        if ((int)count((array)($d['fotos'] ?? [])) < (int)SUPREMO_FOTOS_MIN) {
            $n = count((array)($d['fotos'] ?? []));
            $f['fotos'] = ['texto' => '📷 Sumar fotos del negocio', 'paso' => 'fotos', 'valor' => '',
                           'nota' => 'van ' . $n . ' de ' . (int)SUPREMO_FOTOS_MIN];
        }
        if (trim((string)($d['direccion'] ?? '')) === '') {
            $f['direccion'] = ['texto' => '📍 Poner la dirección', 'paso' => 'direccion', 'valor' => '',
                               'nota' => 'calle o referencia'];
        }
        if (trim((string)($d['horario'] ?? '')) === '') {
            $f['horario'] = ['texto' => '🕐 Poner el horario', 'paso' => 'horario', 'valor' => '',
                             'nota' => 'de un toque'];
        }
        if (trim((string)($d['whatsapp'] ?? '')) === '' && $neg_id <= 0) {
            $f['cliente'] = ['texto' => '📱 Poner el WhatsApp del cliente', 'paso' => 'cliente', 'valor' => '',
                             'nota' => 'sin número no se publica'];
        }
        if (trim((string)($d['rubro_nombre'] ?? '')) === '') {
            $f['rubro_ok'] = ['texto' => '🏷️ Poner el rubro', 'paso' => 'rubro_ok', 'valor' => '',
                              'nota' => 'una palabra basta'];
        }
        // 🛍️ Los 8 productos (orden del jefe: la tienda del Supremo nace con ocho).
        if ($neg_id > 0) {
            $hay = count(supremo_productos_negocio($neg_id));
            $tope = (int)SUPREMO_PRODUCTOS_TOTAL;
            if ($hay < $tope) {
                $f['productos'] = ['texto' => '🛍️ Completar los ' . $tope . ' productos', 'paso' => 'sup_productos',
                                   'valor' => '', 'nota' => 'hay ' . $hay . ' de ' . $tope];
            }
            $sin = supremo_productos_sin_foto($neg_id);
            if ($sin) {
                $f['imagenes'] = ['texto' => '🖼️ Pedir las imágenes que faltan', 'paso' => 'sup_imagenes',
                                  'valor' => '', 'nota' => count($sin) . ' producto(s) sin foto'];
            }
        }
        return $f;
    }
}

if (!function_exists('supremo_ia_piensa')) {
    /**
     * 🧠 LA IA MIRA LA TIENDA YA ARMADA Y LA REESTRUCTURA (una sola llamada).
     *
     * Orden del jefe (2026-09-18): *«debe existir un botón de piensa mejor que lo que hace es analizar
     * lo que el usuario publicó y volver a reestructurar los datos, y si es necesario pedir datos que
     * se haya faltado»*.
     *
     * Devuelve SIEMPRE algo (si la IA no contesta, se sigue con lo que ya hay):
     *   · `nota`        → una línea para el vendedor (qué le faltaba o qué se mejoró)
     *   · `descripcion` → la descripción de la tienda reescrita (mejor gancho, sin inventar datos)
     *   · `productos`   → [{indice, titulo, precio}] con los títulos/precios mejor escritos
     *   · `extra`       → un dato útil que faltaba (frase corta de la propia tienda), si se le ocurre
     */
    function supremo_ia_piensa(array $d, array $ctx = []) {
        $neg_id = supremo_negocio_id($d);
        $prod   = $neg_id > 0 ? supremo_productos_negocio($neg_id) : [];

        $lista = '';
        foreach ($prod as $i => $p) {
            $lista .= ($i + 1) . '. ' . (string)$p['titulo']
                    . ((float)$p['precio'] > 0 ? ' — ' . number_format((float)$p['precio'], 2) : ' — sin precio')
                    . ((string)$p['foto'] !== '' ? ' (con foto)' : ' (sin foto)') . "\n";
        }
        $faltan = supremo_lo_que_falta($d);
        $nombres_faltan = [];
        foreach ($faltan as $k => $v) { $nombres_faltan[] = $k . ' (' . (string)$v['nota'] . ')'; }

        $datos = "TIENDA: " . (string)($d['nombre'] ?? '') . "\n"
               . 'RUBRO: ' . (string)($d['rubro_nombre'] ?? '') . "\n"
               . 'ZONA: ' . (string)($d['distrito_nombre'] ?? '') . "\n"
               . 'DIRECCIÓN: ' . ((string)($d['direccion'] ?? '') !== '' ? (string)$d['direccion'] : '(falta)') . "\n"
               . 'HORARIO: ' . ((string)($d['horario'] ?? '') !== '' ? (string)$d['horario'] : '(falta)') . "\n"
               . 'CÓMO ATIENDE: ' . (string)($d['vendedor'] ?? '') . "\n"
               . 'DESCRIPCIÓN ACTUAL: ' . tienda_ia_limpiar_largo((string)($d['descripcion'] ?? ''), 700) . "\n"
               . "PRODUCTOS:\n" . ($lista !== '' ? $lista : "(todavía no hay)\n")
               . 'DATOS QUE FALTAN (según el sistema): ' . ($nombres_faltan ? implode(', ', $nombres_faltan) : 'ninguno');

        $pide = "Eres el mejor vendedor de tiendas de Chimbote. Te paso UNA TIENDA tal como quedó armada.\n\n"
              . $datos . "\n\n"
              . "Revisa y contesta SOLO con estas líneas (sin explicaciones, sin markdown):\n"
              . "NOTA: una sola frase en español, para el vendedor, diciendo lo más importante que le falta o lo que mejoraste (máximo 22 palabras).\n"
              . "DESCRIPCION: la descripción de la tienda reescrita, en 2 o 3 frases, en español, cálida y vendedora, con lo que SÍ sabemos (rubro, zona, productos). Si un dato no lo tienes, NO lo inventes.\n"
              . "PRODUCTOS: un producto por línea, así → numero|titulo mejor escrito|precio o 0. No cambies el sentido de ningún producto, solo escríbelo mejor y ponle un precio redondo y razonable en soles si no tiene (entre 5 y 60). No agregues ni quites productos.\n"
              . "Si algo no lo puedes mejorar, deja la línea como está.";

        $r = supremo_llamar($pide, array_merge(['paso' => 'sup_piensa', 'max_tokens' => 700], $ctx));
        if (empty($r['ok'])) return ['nota' => '', 'descripcion' => '', 'productos' => [], 'crudo' => ''];
        $txt = (string)($r['texto'] ?? '');
        if (!empty($GLOBALS['SUPREMO_DIAG'])) $GLOBALS['SUPREMO_DIAG_RAW']['piensa'][] = $txt;

        $out = ['nota' => '', 'descripcion' => '', 'productos' => [], 'crudo' => $txt];
        if (trim($txt) === '') return $out;

        foreach (preg_split('/\r?\n/', $txt) as $linea) {
            $linea = trim($linea, " \t|");
            if ($linea === '') continue;
            if (preg_match('/^NOTA\s*:\s*(.+)$/iu', $linea, $m)) { $out['nota'] = tienda_ia_limpiar($m[1], 220); continue; }
            if (preg_match('/^DESCRIPCI[OÓ]N\s*:\s*(.+)$/iu', $linea, $m)) {
                $out['descripcion'] = tienda_ia_limpiar_largo($m[1], 600); continue;
            }
            if (preg_match('/^PRODUCTOS?\s*:\s*$/iu', $linea)) continue;
            // `1|Título mejor|12.5` (y también sin número, por si el modelo se salta la columna)
            $linea = preg_replace('/^\s*\d+\s*[\.\)]\s*/u', '', $linea);
            $campos = array_values(array_filter(array_map('trim', explode('|', $linea)), function ($x) { return $x !== ''; }));
            if (count($campos) >= 2) {
                $ix = null;
                if (ctype_digit($campos[0])) {
                    // Puede ser el número de la lista o un precio suelto: se decide por la posición.
                    $ix = (int)$campos[0] - 1;
                    array_shift($campos);
                    if (!$campos) continue;
                } elseif (preg_match('/^(\d+)\s*[\.\)]\s*(.+)$/u', $campos[0], $m)) {
                    $ix = (int)$m[1] - 1; $campos[0] = trim($m[2]);
                }
                $titulo = trim((string)$campos[0]);
                if ($titulo === '') continue;
                // El precio (si lo trajo) es el ÚLTIMO campo y viene solo en números o con «S/».
                $precio = 0.0;
                $ult = trim((string)$campos[count($campos) - 1]);
                if (count($campos) > 2 && preg_match('/^(s\/\.?\s*)?([0-9]+(?:[.,][0-9]{1,2})?)$/iu', $ult, $mp)) {
                    $precio = (float)str_replace(',', '.', $mp[2]);
                }
                $out['productos'][] = ['indice' => ($ix === null ? count($out['productos']) : $ix),
                                       'titulo' => tienda_ia_limpiar($titulo, 90), 'precio' => $precio];
            } elseif (!empty($campos[0]) && mb_strlen((string)$campos[0]) > 8) {
                if ($out['descripcion'] === '') $out['descripcion'] = tienda_ia_limpiar_largo((string)$campos[0], 600);
            }
        }
        return $out;
    }
}

if (!function_exists('supremo_piensa_aplicar')) {
    /**
     * 🧠 APLICA lo que la IA pensó: la descripción y los títulos/precios de los productos que ya
     * existen. **Nunca crea ni borra productos** (los 8 los arma el sistema, no el modelo) y nunca
     * toca el nombre ni el enlace de la tienda.
     *
     * @return array ['descripcion'=>bool,'productos'=>[titulos cambiados]]
     */
    function supremo_piensa_aplicar(array &$s, array &$d, array $ia) {
        $r = ['descripcion' => false, 'productos' => []];
        $neg_id = supremo_negocio_id($d);

        $desc = trim((string)($ia['descripcion'] ?? ''));
        if ($desc !== '' && mb_strlen($desc) > 40) {
            $d['descripcion'] = $desc;
            $r['descripcion'] = true;
            if ($neg_id > 0) {
                try { db()->prepare("UPDATE directorio_negocios SET descripcion = ?, actualizado_en = ? WHERE id = ?")
                        ->execute([$desc, date('Y-m-d H:i:s'), $neg_id]); }
                catch (Throwable $e) { error_log('supremo_piensa_aplicar desc: ' . $e->getMessage()); }
            }
        }

        $prod = $neg_id > 0 ? supremo_productos_negocio($neg_id) : [];
        foreach ((array)($ia['productos'] ?? []) as $p) {
            $ix = (int)($p['indice'] ?? -1);
            if (!isset($prod[$ix])) continue;
            $mio = $prod[$ix];
            $titulo = trim((string)$p['titulo']);
            $precio = (float)($p['precio'] ?? 0);
            $campos = []; $par = [];
            if ($titulo !== '' && mb_strtolower($titulo) !== mb_strtolower((string)$mio['titulo'])) {
                $campos[] = 'titulo = ?'; $par[] = $titulo;
                $r['productos'][] = (string)$mio['titulo'] . ' → ' . $titulo;
            }
            if ($precio > 0 && abs($precio - (float)$mio['precio']) > 0.009) {
                $campos[] = 'precio = ?'; $par[] = round($precio, 2);
                if (!in_array($mio['titulo'] . ' → ' . $titulo, $r['productos'], true)) $r['productos'][] = (string)$mio['titulo'] . ' (precio)';
            }
            if ($campos) {
                $par[] = (int)$mio['id'];
                try { db()->prepare('UPDATE directorio_servicios SET ' . implode(', ', $campos) . ' WHERE id = ?')->execute($par); }
                catch (Throwable $e) { error_log('supremo_piensa_aplicar prod: ' . $e->getMessage()); }
            }
        }
        return $r;
    }
}

/* =====================================================================================
 * 9) EL GUION (lo que dice el Supremo en cada paso)
 * ===================================================================================== */

if (!function_exists('supremo_guion')) {
    /**
     * El texto del paso y sus botones. Todo tiene respaldo local: la conversación NUNCA se traba
     * aunque la IA no conteste (misma regla que El maestro).
     */
    function supremo_guion($paso, array $d, array $extra = []) {
        // 🏷️ Los rubros reales del directorio, **en GRILLA 2 × 2**: 3 rubros + «Ninguno de estos»
        //    (orden del jefe, 2026-09-18). Se rellena hasta 3 con los rubros que complementan al
        //    candidato y, si aún falta, con los más usados del sitio: la grilla SIEMPRE sale 2 × 2.
        $chips_rubro = function ($limite = 3) use ($d) {
            $limite = max(1, (int)$limite);
            $op = []; $ids = [];
            $meter = function ($id, $texto, $icono = '') use (&$op, &$ids) {
                $id = (int)$id;
                $texto = trim((string)preg_replace('/^[^\p{L}\p{N}]+/u', '', (string)$texto));
                if ($id <= 0 || $texto === '' || in_array($id, $ids, true)) return;
                $ids[] = $id;
                $op[] = ['texto' => ($icono !== '' ? $icono . ' ' : '') . $texto, 'valor' => (string)$id, 'rejilla' => 1];
            };
            foreach ((array)tienda_ia_chips_prototipo($d, $limite) as $o) {
                $meter((int)($o['valor'] ?? 0), (string)($o['texto'] ?? ''), '');
            }
            if (count($op) < $limite && $ids && function_exists('categorias_afinidad')) {
                try {
                    foreach ((array)categorias_afinidad((int)$ids[0]) as $af) {
                        $meter((int)($af['id'] ?? 0), (string)($af['nombre'] ?? ''), (string)($af['icono'] ?? ''));
                        if (count($op) >= $limite) break;
                    }
                } catch (Throwable $e) {}
            }
            if (count($op) < $limite) {
                foreach ((array)tienda_ia_rubros_chips('', 8) as $o) {
                    $meter((int)($o['valor'] ?? 0), (string)($o['texto'] ?? ''), '');
                    if (count($op) >= $limite) break;
                }
            }
            $op[] = ['texto' => '✏️ Ninguno de estos', 'valor' => 'otro', 'rejilla' => 1];
            return array_slice($op, 0, $limite + 1);
        };

        switch ($paso) {

            /* ------------------------------------------------ 🚪 ARRANQUE */
            case 'arranque':
                return [
                    'texto' => SUPREMO_EMOJI . " **" . SUPREMO_NOMBRE . "** — modo vendedor.\n\n"
                             . "Estoy contigo en la venta: en **4 o 5 minutos** dejamos la tienda del cliente publicada, "
                             . "con su portada, su música y sus productos. Tú hablas con el cliente; yo escribo.",
                    'tipo' => 'opciones',
                    'opciones' => [
                        ['texto' => '🚀 Empezar la venta', 'valor' => 'empezar', 'principal' => true],
                        ['texto' => '❓ Cómo se usa', 'valor' => 'ayuda'],
                    ],
                ];

            case 'ayuda':
                return [
                    'texto' => "👑 **Cómo se usa El Supremo**\n\n"
                             . "**1.** Le pides al cliente **3 fotos** de su negocio (el letrero, la fachada, lo que vende).\n"
                             . "**2.** De ahí saco el nombre, el rubro y el WhatsApp: yo te los propongo y tú solo tocas **Sí** o **No**.\n"
                             . "**3.** En cuanto me confirmes **el WhatsApp del cliente**, la tienda **se publica en vivo** y se la muestras.\n"
                             . "**4.** Te doy **los códigos**: la portada (texto artístico sobre una foto del rubro), la música y las imágenes de los productos.\n"
                             . "**5.** Los pegas en Flow con el cliente y te vas; los productos y la portada ya quedaron armados.\n\n"
                             . "⏱️ La meta son **" . (int)SUPREMO_MINUTOS_OBJETIVO . " minutos**. Puedes dejarlo a medias y seguir después.",
                    'tipo' => 'opciones',
                    'opciones' => [['texto' => '🚀 Empezar la venta', 'valor' => 'empezar', 'principal' => true]],
                ];

            /* ------------------------------------------------ 📍 LA UBICACIÓN (LO PRIMERO) */
            // 🔴 Orden del jefe (2026-09-18): *«lo primero que debe pedir es la ubicación, no importa
            //    en qué circunstancias, siempre se debe saber la ubicación desde donde se creó la
            //    tienda; después de la ubicación vienen las imágenes. Para pedir la ubicación solamente
            //    suficiente componer el botón de ubicación»*.
            case 'ubicacion':
                $op = [['texto' => '📍 Compartir mi ubicación', 'valor' => 'gps', 'principal' => true]];
                foreach (tienda_ia_distritos() as $dist) {
                    $op[] = ['texto' => $dist['texto'], 'valor' => $dist['valor'],
                             'tarjeta' => true, 'color' => tienda_ia_distrito_color((string)$dist['texto'])];
                }
                return [
                    'texto' => "📍 **Lo primero: la ubicación.**\n\n"
                             . "Toca el botón y me llega el punto exacto desde donde estás creando la tienda "
                             . "(sirve para que el cliente salga en «cerca de mí»).\n\n"
                             . "Si el GPS falla, toca el distrito 👇",
                    'tipo' => 'opciones',
                    'opciones' => $op,
                ];

            /* ------------------------------------------------ 📷 LAS FOTOS */
            case 'fotos':
                $min = (int)SUPREMO_FOTOS_MIN;
                $max = (int)SUPREMO_FOTOS_MAX;
                $n   = count((array)$d['fotos']);
                // 🔴 SIN BOTÓN DE CONFIRMACIÓN (orden del jefe, 2026-09-18): *«después de subir las
                //    fotos aparece un botón de confirmación «ya está, seguir»: es un paso innecesario; si
                //    ya lo subió, de frente debe decir qué nombre se le ha ocurrido»*. Con las fotos ya
                //    subidas y leídas, el motor **pasa solo** a la pregunta del nombre.
                return [
                    'texto' => $n > 0
                        ? "📷 Ya tengo **{$n}** " . ($n === 1 ? 'foto' : 'fotos') . '. Si quieres mandar más, mándalas ahora.'
                        : "📷 **Las fotos del negocio del cliente** ({$min} a {$max}): el letrero 🏪, la fachada, "
                          . "por dentro, sus productos 📦, sus máquinas 🧰.\n\n"
                          . 'De aquí saco el nombre, el rubro, el WhatsApp y lo que vende.',
                    'tipo' => 'foto',
                    'opciones' => [],
                ];

            /* ------------------------------------------------ ✅ EL NOMBRE (SÍ / NO / ESCRIBIR) */
            // 🔴 Orden del jefe (2026-09-18): *«que diga qué nombre se le ha ocurrido y pregunte con dos
            //    botones sí o no: «la tienda se llama Los Juancitos, ¿sí o no?». Si el cliente dice no,
            //    propone el siguiente nombre y preguntan con tres botones: sí, no, escribir.»*
            case 'nombre_ok':
                $prop = trim((string)($d['sup_nombre_prop'] ?? ''));
                if ($prop === '') {
                    return [
                        'texto' => "🏪 **¿Cómo se llama la tienda del cliente?** Escríbelo aquí 👇",
                        'tipo' => 'texto',
                        'opciones' => [],
                    ];
                }
                $op = [['texto' => '✅ Sí: «' . $prop . '»', 'valor' => 'si:' . $prop, 'principal' => true],
                       ['texto' => '❌ No, otro nombre', 'valor' => 'no']];
                // Desde la segunda propuesta también se puede **escribir** el nombre a mano.
                if ((int)($d['sup_nombre_visto'] ?? 0) >= 2) $op[] = ['texto' => '✏️ Escribir', 'valor' => 'escribir'];
                return [
                    'texto' => '🏪 **¿La tienda se llama «' . $prop . '»?** '
                             . ((int)($d['sup_nombre_visto'] ?? 0) >= 2
                                 ? 'Toca **Sí**, **No** o **Escribir** 👇'
                                 : 'Toca **Sí** o **No** 👇'),
                    'tipo' => 'opciones',
                    'opciones' => $op,
                ];

            /* ------------------------------------------------ 🏷️ EL RUBRO (SÍ / NO + GRILLA 2 × 2) */
            // 🔴 Orden del jefe (2026-09-18): *«me dice «por las fotos diría que es una ferretería»…
            //    debe preguntarme sí o no con botones para yo presionar sí o no… y cuando presenta las
            //    opciones de rubro debe ser una GRILLA de dos columnas y dos filas: en esos cuatro
            //    botones, tres rubros y el botón que diga «ninguno de estos».»*
            case 'rubro_ok':
                $leido   = trim((string)($d['rubro_leido'] ?? ''));
                $mostrar = ((string)($d['sup_rubro_opciones'] ?? '') === '1');
                if ($leido !== '' && !$mostrar) {
                    return [
                        'texto' => '🏷️ Por las fotos diría que es **' . $leido . "**.\n\n¿Le atino?",
                        'tipo' => 'opciones',
                        'opciones' => [
                            ['texto' => '✅ Sí, es ' . $leido, 'valor' => 'si', 'principal' => true],
                            ['texto' => '❌ No, es otro', 'valor' => 'otro'],
                        ],
                    ];
                }
                return [
                    'texto' => "🏷️ **¿De qué es el negocio?** Toca el suyo 👇",
                    'tipo' => 'opciones',
                    'opciones' => $chips_rubro(3),   // 3 rubros + «Ninguno de estos» = la grilla 2 × 2
                ];

            case 'rubro_mas':
                // 🔴 Grilla 2 × 2 y el botón de abajo dice **Continuar** (orden del jefe, 2026-09-18).
                $extra_op = [];
                foreach (array_slice(array_values((array)tienda_ia_rubros_para_sumar($d)), 0, 4) as $o) {
                    $o['rejilla'] = 1;
                    $extra_op[] = $o;
                }
                $extra_op[] = ['texto' => '✅ Continuar', 'valor' => 'seguir', 'principal' => true];
                return [
                    'texto' => "🏷️ Su rubro principal es **" . (string)$d['rubro_nombre'] . "**.\n\n"
                             . "¿Le sumas otros rubros o categorías? Puede tener **hasta 4** en total (así sale en más búsquedas).",
                    'tipo' => 'opciones',
                    'opciones' => $extra_op,
                ];

            /* ------------------------------------------------ 🏪 CÓMO ATIENDE (GRILLA 2 × 2) */
            case 'tipo':
                return [
                    'texto' => "🏪 **¿Cómo atiende a sus clientes?**",
                    'tipo' => 'opciones',
                    'opciones' => [
                        ['texto' => '🏪 Tiene su local', 'valor' => 'fisica', 'principal' => true, 'rejilla' => 1],
                        ['texto' => '🛵 Es ambulante', 'valor' => 'ambulante', 'rejilla' => 1],
                        ['texto' => '🌐 Vende por internet', 'valor' => 'domicilio', 'rejilla' => 1],
                        ['texto' => '🚚 Vende a todo el país', 'valor' => 'nacional', 'rejilla' => 1],
                    ],
                ];

            case 'direccion':
                $op = [];
                $leida = trim((string)($d['direccion_leida'] ?? ''));
                if ($leida !== '') $op[] = ['texto' => '📍 ' . mb_substr($leida, 0, 60), 'valor' => 'usar:' . $leida];
                return [
                    'texto' => "📍 **¿Cuál es la dirección?**\n\nLa calle y el número, o una referencia para llegar "
                             . "(«frente al mercado», «a dos cuadras de la plaza»).",
                    'tipo' => 'texto',
                    'opciones' => $op,
                ];

            case 'horario':
                // 🕐 CINCO OPCIONES, SIN HACERLE ESCRIBIR (orden del jefe, 2026-09-18: *«cuando pidas el
                //    horario ofrece opciones, no es necesario que el usuario escriba; tú ofrece cinco
                //    opciones de horarios»*).
                return [
                    'texto' => "🕐 **¿En qué horario trabaja?** Toca el suyo 👇",
                    'tipo' => 'opciones',
                    'opciones' => [
                        // 📱 EN DOS COLUMNAS (orden del jefe, 2026-09-18: *«nuevamente están apilados en
                        //    filas, divídela en dos columnas»*): los 5 horarios van en rejilla y el
                        //    «Después» ocupa su propia fila abajo.
                        ['texto' => '🌅 Solo en las mañanas', 'valor' => 'Solo en las mañanas', 'rejilla' => 1],
                        ['texto' => '🌇 Solo en las tardes', 'valor' => 'Solo en las tardes', 'rejilla' => 1],
                        ['texto' => '📅 De lunes a viernes', 'valor' => 'De lunes a viernes', 'rejilla' => 1],
                        ['texto' => '🕘 De 9 de la mañana a 9 de la noche', 'valor' => 'De 9 de la mañana a 9 de la noche', 'rejilla' => 1],
                        ['texto' => '🗓️ Solo los sábados y domingos', 'valor' => 'Solo los sábados y domingos', 'rejilla' => 1],
                        ['texto' => '⏭️ Después', 'valor' => 'despues'],
                    ],
                ];

            case 'entregas':
                $op = [];
                foreach (tienda_ia_distritos() as $dist) {
                    $marca = in_array((int)$dist['valor'], array_map('intval', (array)($d['entregas'] ?? [])), true);
                    $op[] = ['texto' => ($marca ? '✅ ' : '') . $dist['texto'], 'valor' => $dist['valor'],
                             'tarjeta' => true, 'color' => tienda_ia_distrito_color((string)$dist['texto'])];
                }
                $op[] = ['texto' => '🗺️ Todos los distritos', 'valor' => 'todos', 'azul' => true];
                $op[] = ['texto' => '✅ Continuar', 'valor' => 'seguir', 'principal' => true];
                return [
                    'texto' => "🌐 **¿En qué distritos entrega sus productos?**\n\nToca todos los que reparte 👇",
                    'tipo' => 'opciones',
                    'opciones' => $op,
                ];

            case 'distrito':
                $op = [['texto' => '📍 Compartir mi ubicación', 'valor' => 'gps', 'principal' => true]];
                foreach (tienda_ia_distritos() as $dist) {
                    $op[] = ['texto' => $dist['texto'], 'valor' => $dist['valor'],
                             'tarjeta' => true, 'color' => tienda_ia_distrito_color((string)$dist['texto'])];
                }
                return [
                    'texto' => "📍 **¿En qué zona está la tienda?**\n\nToca su distrito o comparte la ubicación 👇",
                    'tipo' => 'opciones',
                    'opciones' => $op,
                ];

            /* ------------------------------------------------ 📱 EL CLIENTE (SÍ / NO / ESCRIBIR) */
            // 🔴 Orden del jefe (2026-09-18): *«no aceptamos tiendas sin número de WhatsApp, simplemente
            //    no aceptamos… cuando pregunta el WhatsApp y ofrece un número extraído de las imágenes,
            //    debe decir «tu WhatsApp es el número», dos botones sí o no o escribir; si queda
            //    confirmado que es el número, no presenta otro; escribir: el cliente escribe el número»*.
            case 'cliente':
                $tel = preg_replace('/\D+/', '', (string)($d['telefono_leido'] ?? ''));
                if (mb_strlen($tel) === 11 && substr($tel, 0, 2) === '51') $tel = substr($tel, 2);
                if (mb_strlen($tel) >= 6 && empty($d['sup_wa_preguntado'])) {
                    return [
                        'texto' => "📱 **El WhatsApp del cliente.**\n\n"
                                 . 'De sus fotos saqué este número: **' . $tel . "**.\n\n¿Es su WhatsApp?",
                        'tipo' => 'opciones',
                        'opciones' => [
                            ['texto' => '✅ Sí, es ' . $tel, 'valor' => 'si|' . $tel, 'principal' => true],
                            ['texto' => '❌ No, es otro', 'valor' => 'otro'],
                            ['texto' => '✏️ Escribir el número', 'valor' => 'escribir'],
                        ],
                    ];
                }
                return [
                    'texto' => "📱 **El WhatsApp del cliente** (donde quiere recibir sus pedidos).\n\n"
                             . "Escríbelo aquí (9 dígitos, por ejemplo **943112233**): con ese número le creo su cuenta "
                             . '—usuario = su número— y le doy su clave. La tienda queda a su nombre.',
                    'tipo' => 'texto',
                    'opciones' => [],
                ];

            /* ------------------------------------------------ 👑 LA PORTADA */
            case 'sup_portada':
                return [
                    'texto' => "🖼️ **¿Cuál es la portada?** Es la primera foto que ve el cliente en su tienda.\n\n"
                             . "Tres caminos:",
                    'tipo' => 'opciones',
                    'opciones' => [
                        ['texto' => '🎨 Que la cree la IA (te doy el código)', 'valor' => 'crear', 'principal' => true],
                        ['texto' => '📷 Usar una de las fotos del cliente', 'valor' => 'fotos'],
                        ['texto' => '📤 Ya tengo la portada: la subo ahora', 'valor' => 'subir'],
                        ['texto' => '⏭️ Sin portada por ahora', 'valor' => 'despues'],
                    ],
                ];

            case 'sup_portada_fotos':
                return [
                    'texto' => "📷 **Toca la foto que quieres de portada** 👇",
                    'tipo' => 'opcion',
                    'opciones' => [['texto' => '⬅️ Volver', 'valor' => 'volver']],
                ];

            /* ------------------------------------------------ 🛍️ LOS 8 PRODUCTOS (los elige el sistema) */
            case 'sup_productos':
                $creados = (array)($d['sup_creados'] ?? []);
                if ($creados) {
                    $con_foto = 0;
                    foreach ($creados as $p) { if (!empty($p['con_foto'])) $con_foto++; }
                    $t = "🛍️ **Le armé sus " . count($creados) . " productos** (los elegí yo, no hay que tocar nada):\n\n";
                    foreach ($creados as $i => $p) {
                        $t .= '· ' . (string)$p['titulo'] . (empty($p['con_foto']) ? '  _(le pedí su imagen)_' : '  📷') . "\n";
                    }
                    $t .= "\n📷 " . $con_foto . ' ya tienen su foto'
                        . (count($creados) - $con_foto > 0
                            ? ' y a los otros **' . (count($creados) - $con_foto) . '** les pedí su imagen en el código (con su ID impreso, para ponerla sola cuando llegue).'
                            : '.');
                    return [
                        'texto' => $t,
                        'tipo' => 'opciones',
                        'opciones' => [
                            ['texto' => '👑 Ver los códigos', 'valor' => 'seguir', 'principal' => true],
                            ['texto' => '🔄 Que los piense otra vez', 'valor' => 'otra'],
                        ],
                    ];
                }
                return [
                    'texto' => "🛍️ **Le estoy armando sus " . (int)SUPREMO_PRODUCTOS_TOTAL . " productos**…",
                    'tipo' => 'opciones',
                    'opciones' => [['texto' => '👑 Ver los códigos', 'valor' => 'seguir', 'principal' => true]],
                ];

            /* ------------------------------------------------ 🧠 VIEJO: LOS PRODUCTOS VISTOS */
            // ⚠️ LEGADO (ya no está en el flujo): desde el 2026-09-18 el Supremo **no pregunta** qué
            //    productos quiere —los elige el sistema y siempre son 8— así que el paso `sup_productos`
            //    de arriba es el único que manda. Estas dos ramas se quedan solo para que **una venta
            //    vieja guardada en la base** (con el paso `sup_inventar` o `sup_ia_elegir`) no se rompa
            //    al reabrirla.
            case 'sup_inventar':
                $inv = (array)($d['sup_inventados'] ?? []);
                if ($inv) {
                    $t = "🧠 **Estos son los " . count($inv) . " productos que inventé** con el contexto de la tienda:\n\n";
                    foreach ($inv as $p) {
                        $t .= '· **' . (string)$p['titulo'] . '**'
                            . ((float)($p['precio'] ?? 0) > 0 ? ' — ' . SUPREMO_MONEDA . ' ' . number_format((float)$p['precio'], 2) : '')
                            . "\n  " . (string)($p['frase'] ?? '') . "\n";
                    }
                    $t .= "\n¿Los creo en su tienda y después me dices a cuáles les pedimos su imagen a la IA?";
                    return [
                        'texto' => $t,
                        'tipo' => 'opciones',
                        'opciones' => [
                            ['texto' => '✅ Crearlos', 'valor' => 'crear', 'principal' => true],
                            ['texto' => '🔄 Que los piense otra vez', 'valor' => 'otra'],
                            ['texto' => '⏭️ Sin productos inventados', 'valor' => 'nada'],
                        ],
                    ];
                }
                return [
                    'texto' => "🧠 **La IA va a inventar " . (int)SUPREMO_PRODUCTOS_IA . " productos** con el contexto "
                             . "de la tienda: su rubro, su zona y lo que se vio en las fotos. Después te pregunto "
                             . "**a cuáles les pedimos su imagen** y te doy los códigos.\n\n¿Los pienso?",
                    'tipo' => 'opciones',
                    'opciones' => [['texto' => '🧠 Sí, invéntalos', 'valor' => 'pensar', 'principal' => true]],
                ];

            /* ------------------------------------------------ 🖼️ A CUÁLES LES HACEMOS LA IMAGEN */
            case 'sup_ia_elegir':
                $sin_foto = (array)($extra['sin_foto'] ?? []);
                if (!$sin_foto) {
                    return [
                        'texto' => "🖼️ Todos los productos ya tienen su foto: no hay que pedirle imágenes a la IA.",
                        'tipo' => 'opciones',
                        'opciones' => [['texto' => '👑 Ver los códigos', 'valor' => 'nada', 'principal' => true]],
                    ];
                }
                return [
                    'texto' => "🖼️ **¿A cuáles les pedimos su imagen a la IA?**\n\nEstos productos están **sin foto** "
                             . "(son los que inventé). Toca los que quieras con su imagen lista —se la pide el "
                             . "código y después la subes aquí— y los demás quedan sin foto.\n\n"
                             . "⚠️ Cada imagen lleva su **ID impreso** dentro: así yo sé a qué producto va cada archivo.",
                    'tipo' => 'multi',
                    'items' => $sin_foto,
                    'opciones' => [
                        ['texto' => '✅ Ya está: pedir estas imágenes', 'valor' => 'seguir', 'principal' => true],
                        ['texto' => '🖼️ A todos', 'valor' => 'todos'],
                        ['texto' => '⏭️ A ninguno', 'valor' => 'nada'],
                    ],
                ];

            /* ------------------------------------------------ 👑 LOS CÓDIGOS */
            case 'sup_codigos':
                // 🔴 SIN TEXTO DE RELLENO (orden del jefe, 2026-09-18: *«los códigos ya están en la parte
                //    de arriba, así que no es necesario que diga «aquí están los códigos, copia cada uno
                //    con su botón y pégalo»: ese texto es innecesario»*). El panel se abre solo: aquí no
                //    se dice nada, solo se ofrecen los atajos. **Ver la tienda es un ENLACE de verdad**
                //    (antes era una opción que volvía a pintar el mismo panel: *«presiono ver la tienda y
                //    no me muestra la tienda, me muestra los códigos»*), y el WhatsApp al cliente también
                //    está aquí, sin tener que llegar al final de la venta.
                $neg_cod = (array)($d['sup_negocio'] ?? []);
                $op_cod = [];
                if (trim((string)($neg_cod['url'] ?? '')) !== '') {
                    $op_cod[] = ['texto' => '🏬 Ver la tienda', 'url' => (string)$neg_cod['url'], 'principal' => true];
                }
                $wa_cod = supremo_wa_cliente($d);
                if ($wa_cod !== '') $op_cod[] = ['texto' => '📲 Mandarle sus datos por WhatsApp', 'url' => $wa_cod];
                $op_cod[] = ['texto' => '📥 Subir las imágenes', 'valor' => 'ir_imagenes'];
                $op_cod[] = ['texto' => '🎵 Subir la canción', 'valor' => 'cancion_ir'];
                return ['texto' => '', 'tipo' => 'codigos', 'opciones' => $op_cod];

            /* ------------------------------------------------ 📥 LA SALA DE ESPERA */
            case 'sup_imagenes':
                $prods = supremo_negocio_id($d) > 0 ? supremo_productos_negocio(supremo_negocio_id($d)) : [];
                $sin_foto = [];
                foreach ($prods as $p) { if ($p['nfotos'] <= 0) $sin_foto[] = (string)$p['titulo']; }
                $t = "📥 **La sala de espera.**\n\n"
                   . "Cuando la IA te entregue las imágenes, súbelas aquí **todas de una vez** (o de a pocos). "
                   . "Yo leo el número que trae impreso cada una y la pongo en su producto; a la cabecera la "
                   . "reconozco por el nombre del negocio.";
                if ($sin_foto) $t .= "\n\n⏳ **Sin imagen todavía:** " . implode(', ', array_slice($sin_foto, 0, 8)) . ".";
                $puestas = (array)($d['sup_imagenes'] ?? []);
                if ($puestas) $t .= "\n\n✅ Ya entraron **" . count($puestas) . "** imagen(es).";
                return [
                    'texto' => $t,
                    'tipo' => 'foto',
                    'opciones' => [
                        ['texto' => '🎵 La canción', 'valor' => 'cancion', 'principal' => true],
                        ['texto' => '💰 La oferta', 'valor' => 'oferta'],
                        ['texto' => '👑 Volver a los códigos', 'valor' => 'codigos'],
                    ],
                ];

            /* ------------------------------------------------ 🎵 LA CANCIÓN (subirla) */
            case 'sup_cancion':
                $c = supremo_cancion_info(supremo_negocio_id($d));
                if ($c) {
                    return [
                        'texto' => "🎵 **La canción ya está puesta.** Suena sola en la ficha del cliente 👇\n\n"
                                 . '· ' . number_format((float)$c['duracion'], 0) . " segundos\n"
                                 . '· ' . $c['url'] . "\n\n"
                                 . "Si quieres cambiarla, mándame el otro audio y la reemplazo.",
                        'tipo' => 'opciones',
                        'opciones' => [
                            ['texto' => '🧠 Revisar la tienda (piensa mejor)', 'valor' => 'oferta', 'principal' => true],
                            ['texto' => '🔄 Cambiar la canción', 'valor' => 'cambiar'],
                            ['texto' => '🎉 Cerrar la venta', 'valor' => 'cerrar'],
                        ],
                    ];
                }
                return [
                    // 🔴 UN BOTÓN QUE SE ENTIENDE (orden del jefe, 2026-09-18: *«aquí lo que debería decir
                    //    es click aquí para cargar la canción»*): texto corto y su botón grande.
                    'texto' => "🎵 **Falta la canción.** Toca el botón y elige el audio que bajaste de Flow (hasta "
                             . supremo_audio_max_mb() . ' MB).',
                    'tipo' => 'audio',
                    'opciones' => [
                        ['texto' => '🎵 Clic aquí para cargar la canción', 'valor' => 'cargar_cancion', 'principal' => true],
                        ['texto' => '⏭️ Después', 'valor' => 'despues'],
                    ],
                ];

            /* ------------------------------------------------ 🧠 PIENSA MEJOR (lo reestructura) */
            // Orden del jefe (2026-09-18): *«debe existir un botón de piensa mejor que lo que hace es
            // analizar lo que el usuario publicó y volver a reestructurar los datos, y si es necesario
            // pedir datos que se haya faltado»*.
            case 'sup_piensa':
                $pedir = supremo_lo_que_falta($d);
                $op = [];
                foreach ($pedir as $k => $v) {
                    $op[] = ['texto' => (string)$v['texto'], 'valor' => 'ir:' . (string)$v['paso'],
                             'nota' => (string)$v['nota']];
                }
                $t = "🧠 **Piensa mejor.**\n\n"
                   . "Ya le di una repasada a la tienda del cliente: miré lo que quedó publicado y **volví a "
                   . "reestructurar los datos** (su descripción y sus productos). Lo que mejoré te lo dije arriba.\n\n";
                if ($op) {
                    $t .= "Todavía **falta" . (count($op) === 1 ? '' : 'n') . ' ' . count($op) . " cosa"
                        . (count($op) === 1 ? '' : 's') . "**. Toca lo que quieras poner (te devuelvo aquí mismo) 👇";
                } else {
                    $t .= "✅ **No falta nada**: tiene su zona, su dirección, su horario, su WhatsApp, sus fotos "
                        . 'y sus ' . (int)SUPREMO_PRODUCTOS_TOTAL . " productos. Está lista para mostrarla.";
                }
                $op[] = ['texto' => '🧠 Analizar otra vez', 'valor' => 'otra'];
                $op[] = ['texto' => '💰 Seguir con la oferta', 'valor' => 'seguir', 'principal' => true];
                return ['texto' => $t, 'tipo' => 'opciones', 'opciones' => $op];

            /* ------------------------------------------------ 💰 LA OFERTA (lo que se le vende) */
            case 'sup_oferta':
                $c = supremo_calculo((float)($d['sup_precio'] ?? 0));
                $prods = supremo_negocio_id($d) > 0 ? supremo_productos_negocio(supremo_negocio_id($d)) : [];

                // 💰 Sin precio todavía: se le pide (o se le ofrece uno de los productos de la tienda).
                if ((float)$d['sup_precio'] <= 0) {
                    $t = "💰 **Ahora la cuenta que cierra la venta.**\n\n"
                       . "Dime el precio de UN producto del local y lo calculo delante del cliente.";
                    $op = [];
                    $n = 0;
                    foreach ($prods as $p) {
                        if ((float)$p['precio'] <= 0 || $n >= 6) continue;
                        $op[] = ['texto' => '💰 ' . (string)$p['titulo'] . ' · ' . supremo_soles($p['precio']),
                                 'valor' => 'precio:' . (float)$p['precio'] . '|' . (string)$p['titulo']];
                        $n++;
                    }
                    $op[] = ['texto' => '⏭️ Sin la cuenta, cerrar la venta', 'valor' => 'saltar'];
                    return ['texto' => $t, 'tipo' => 'texto', 'opciones' => $op];
                }

                $t = "💰 **LA CUENTA, delante del cliente:**\n\n"
                   . '· Producto: **' . (trim((string)($d['sup_producto'] ?? '')) !== '' ? (string)$d['sup_producto'] : 'el que eligió') . "**\n"
                   . '· Precio: **' . supremo_soles($c['precio']) . "**\n"
                   . '· Ventas prometidas: **' . (int)$c['ventas'] . ' en ' . (int)$c['dias'] . " días**\n"
                   . '· Lo que gana la tienda: **' . supremo_soles($c['ingreso']) . "**\n"
                   . '· Si me pagara el ' . rtrim(rtrim(number_format($c['pct'], 2, '.', ''), '0'), '.') . ' %: ' . supremo_soles($c['comision']) . "\n"
                   . '· Mi tarifa: **' . supremo_soles($c['tarifa']) . " al mes**\n"
                   . '· Se ahorra: **' . supremo_soles($c['ahorro']) . "**\n"
                   . '· Mi comisión real: **' . number_format($c['efectiva'], 2) . ' %**'
                   . ($c['cubre'] > 0 ? ' (con **' . (int)$c['cubre'] . '** ventas al mes ya me paga)' : '') . "\n\n"
                   . ($c['luce'] ? '' :
                        '⚠️ **Con este precio la cuenta no luce:** mi tarifa sale al '
                        . number_format($c['efectiva'], 2) . ' % de lo vendido. Busca un producto de **'
                        . supremo_soles($c['precio_min']) . ' o más** y mi comisión baja del '
                        . rtrim(rtrim(number_format($c['ideal'], 2, '.', ''), '0'), '.') . " %.\n\n")
                   . "🗣️ **Dilo así:**\n«" . supremo_frase_venta($c) . "»\n\n"
                   . '📋 En el botón **💰 La oferta** de la cabecera tienes la oferta por escrito, el guion de 6 pasos y las condiciones para copiar.';
                $op = [['texto' => '✅ Cerrar la venta', 'valor' => 'cerrar', 'principal' => true],
                       ['texto' => '💰 Ver la oferta y el guion', 'valor' => 'panel'],
                       ['texto' => '✏️ Probar con otro precio', 'valor' => 'otro']];
                foreach ($prods as $p) {
                    if ((float)$p['precio'] <= 0 || (float)$p['precio'] === (float)$c['precio']) continue;
                    $op[] = ['texto' => '💰 ' . (string)$p['titulo'] . ' · ' . supremo_soles($p['precio']),
                             'valor' => 'precio:' . (float)$p['precio'] . '|' . (string)$p['titulo']];
                }
                return ['texto' => $t, 'tipo' => 'opciones', 'opciones' => $op];

            /* ------------------------------------------------ 🎉 EL CIERRE */
            case 'sup_fin':
                $neg = (array)($d['sup_negocio'] ?? []);
                $seg = supremo_segundos($d);
                $min = $seg > 0 ? round($seg / 60, 1) : 0;
                $prods = supremo_negocio_id($d) > 0 ? supremo_productos_negocio(supremo_negocio_id($d)) : [];
                $fotos = supremo_negocio_id($d) > 0 ? supremo_fotos_negocio(supremo_negocio_id($d)) : [];
                $t = "🎉 **Venta cerrada**" . ($min > 0 ? " en **" . number_format($min, 1) . " minutos**" : "") . ".\n\n"
                   . "🏪 **" . (string)$d['nombre'] . "**\n"
                   . '🔗 Su tienda ya está en internet: **' . (string)($neg['url'] ?? '') . "**\n"
                   . '👉 Toca el botón **🏬 Ver la tienda** para abrirla delante del cliente.\n'
                   . "🛍️ Productos: **" . count($prods) . "**\n"
                   . "🖼️ Fotos de la tienda: **" . count($fotos) . "**"
                   . (trim((string)($d['sup_usuario'] ?? '')) !== '' ? "\n👤 Usuario del cliente: **" . (string)$d['sup_usuario'] . "**" : "")
                   . ((float)($d['sup_precio'] ?? 0) > 0
                        ? "\n💰 La oferta quedó con **" . supremo_soles((float)$d['sup_precio']) . "** ("
                          . supremo_soles(supremo_calculo((float)$d['sup_precio'])['ingreso']) . " con "
                          . (int)SUPREMO_OFERTA_VENTAS . " ventas)"
                        : '');
                // 🏬 LA TIENDA, CON SU ENLACE CLIQUEABLE (orden del jefe, 2026-09-18: *«por fin aparece
                //    el botón que dice ver la tienda: ahí debería verse la URL y hacerlo clickeable… y
                //    abajo un botón de mandar WhatsApp a la tienda»*). El primer botón es un ENLACE de
                //    verdad (se abre en otra pestaña), y debajo va el WhatsApp al cliente.
                $opciones = [];
                if (trim((string)($neg['url'] ?? '')) !== '') {
                    $opciones[] = ['texto' => '🏬 Ver la tienda (abrir)', 'url' => (string)$neg['url'], 'principal' => true];
                }
                // 📲 El botón de WhatsApp AL CLIENTE: abre su chat con el mensaje ya escrito (su enlace,
                //    su usuario y su clave: UN SOLO enlace en el mensaje).
                $wa_fin = supremo_wa_cliente($d);
                if ($wa_fin !== '') {
                    $opciones[] = ['texto' => '📲 Mandarle sus datos por WhatsApp', 'url' => $wa_fin];
                }
                $opciones[] = ['texto' => '👑 Otra venta en vivo', 'valor' => 'otra_venta'];
                $opciones[] = ['texto' => '💰 Ver la oferta y el guion otra vez', 'valor' => 'oferta'];
                $opciones[] = ['texto' => '👑 Ver los códigos otra vez', 'valor' => 'codigos'];
                return ['texto' => $t, 'tipo' => 'opciones', 'opciones' => $opciones];
        }

        // Respaldo: cualquier paso desconocido vuelve al principio (nunca deja la pantalla muda).
        return ['texto' => "👑 Seguimos. ¿Qué hacemos?", 'tipo' => 'opciones',
                'opciones' => [['texto' => '🚀 Empezar la venta', 'valor' => 'empezar', 'principal' => true]]];
    }
}

/* =====================================================================================
 * 10) EL MOTOR: lo que llega del navegador, paso por paso
 * ===================================================================================== */

if (!function_exists('supremo_recibir')) {
    /**
     * Recibe lo que hizo el vendedor (una opción, un texto, unas fotos o su ubicación) y devuelve
     * lo que hay que pintar. Mismo contrato que `tienda_ia_recibir()`: `['ok','mensajes','tipo','opciones']`.
     *
     * 🔒 Aquí no se confía en nada del navegador: el paso lo manda el servidor desplazándose solo.
     */
    function supremo_recibir(array $s, $tipo, $texto = '', $valor = '', $lat = null, $lng = null, array $archivos = []) {
        $d      = $s['datos'];
        $msgs   = [];
        $empujar = function ($t) use (&$msgs) { if (trim((string)$t) !== '') $msgs[] = ['rol' => 'bot', 'texto' => (string)$t]; };
        $uid    = (int)$s['usuario_id'];
        $paso   = (string)$s['paso'];
        $valor  = trim((string)$valor);
        $texto  = trim((string)$texto);

        // ⏱️ El cronómetro de la venta: arranca con el primer movimiento y se para al cerrar.
        if (empty($d['sup_inicio'])) $d['sup_inicio'] = time();

        // 🎵 EL ATAJO DE LA CANCIÓN (orden del jefe, 2026-09-18: *«no hay modo de subir la canción
        //    descargada al Supremo»*): el audio se puede subir **en cualquier momento de la venta**
        //    desde el botón 🎵 de la cabecera, sin tener que llegar a su paso. Se atiende aquí.
        if ($tipo === 'audio' && $valor === 'cancion_subir') {
            $s['datos'] = $d;
            $r = supremo_cancion_subir($s, $archivos[0] ?? []);
            if (empty($r['ok'])) {
                $empujar('😅 ' . (string)($r['error'] ?? 'No pude guardar la canción.'));
                return ['ok' => true, 'tipo' => 'audio', 'opciones' => [],
                        'mensajes' => $msgs, 'cancion' => supremo_cancion_info(supremo_negocio_id($d)),
                        'cancion_error' => true];
            }
            $empujar("🎵 **¡Canción puesta!** Ya suena sola en la ficha del cliente 👇\n\n"
                   . '· ' . number_format((float)$r['duracion'], 0) . ' segundos · '
                   . number_format((float)$r['peso_mb'], 2) . ' MB');
            return ['ok' => true, 'tipo' => 'opciones', 'opciones' => (array)supremo_guion((string)$s['paso'], $d)['opciones'],
                    'mensajes' => $msgs, 'cancion' => supremo_cancion_info(supremo_negocio_id($d))];
        }

        $loguear = function ($nuevo_paso, $extra = []) use (&$s, &$d, $paso, $empujar, &$msgs) {
            $s['paso']  = $nuevo_paso;
            $s['datos'] = $d;
            tienda_ia_guardar($s);
            $g = supremo_guion($nuevo_paso, $d, $extra);
            if (trim((string)$g['texto']) !== '') $empujar($g['texto']);
            // 🔴 LOS MENSAJES VIAJAN SIEMPRE (bicho cazado el 2026-09-18): este `$loguear` devolvía
            //    `tipo` y `opciones` pero **se dejaba fuera `mensajes`**, así que todo lo que el motor
            //    había dicho en el camino («Le armé sus 8 productos…», «Portada puesta», «Según tu
            //    ubicación están en Chimbote…») nunca llegaba a la pantalla: el chat se quedaba con la
            //    pregunta del paso y nada más. Es la otra mitad del bicho «no se ven las respuestas».
            return ['ok' => true, 'tipo' => (string)$g['tipo'], 'opciones' => (array)($g['opciones'] ?? []),
                    'items' => (array)($g['items'] ?? []), 'mensajes' => $msgs];
        };

        // ✍️💬 EL CIERRE DEL ARMADO: cuando los 8 productos ya están dentro, se escribe la descripción
        //    DEFINITIVA de la ficha (una llamada, que además nombra los productos) y se siembran las
        //    primeras opiniones SIN gastar IA. Va aquí —y no en la publicación— para que el cliente vea
        //    su tienda en el acto (orden del jefe, 2026-09-18: *«está tardando un poquito… procuremos
        //    optimizar el resultado lo más rápido»*).
        $cerrar_armado = function () use (&$s, &$d, $empujar) {
            if (supremo_negocio_id($d) <= 0) return;
            if (empty($d['sup_copy_hecho'])) {
                $copy = supremo_ia_descripcion_tienda($s, $d);
                $d = $s['datos'];
                if ($copy !== '') { $d['descripcion_ia'] = $copy; $empujar('✍️ La ficha quedó con su descripción escrita (con sus productos dentro).'); }
                $d['sup_copy_hecho'] = 1;
                $s['datos'] = $d;
            }
            if (empty($d['sup_opiniones_hechas'])) {
                try {
                    require_once __DIR__ . '/opiniones.php';
                    if (function_exists('tienda_ia_sembrar_opiniones')) {
                        tienda_ia_sembrar_opiniones(supremo_negocio_id($d), $d,
                            supremo_productos_negocio(supremo_negocio_id($d)), (int)$s['id'], true);
                    }
                } catch (Throwable $e) {}
                $d['sup_opiniones_hechas'] = 1;
                $s['datos'] = $d;
            }
        };

        // 🛍️ A LOS 8 PRODUCTOS YA ARMADOS: es el camino que pidió el jefe (*«no preguntes qué
        //    productos: de frente crea los productos de frente y pide las imágenes… tú elige
        //    siempre»*). Se arman al entrar (una sola vez) y el paso se abre con la lista hecha:
        //    el vendedor no toca nada. Si ya estaban armados, no se gasta ni una llamada.
        $ir_productos = function () use (&$s, &$d, $empujar, $loguear, $cerrar_armado) {
            if (supremo_negocio_id($d) > 0 && empty($d['sup_creados'])) {
                $r = supremo_armar_productos($s, $d, $empujar);
                $d = $s['datos'];
                if (!empty($r['hechos'])) {
                    $empujar("🛍️ **" . (int)$r['hechos'] . ' productos listos** en su tienda: '
                           . (int)$r['vistos'] . ' de sus fotos'
                           . ((int)$r['inventados'] > 0 ? ' y ' . (int)$r['inventados'] . ' que inventé con el contexto' : '') . '.');
                } else {
                    $empujar('🛍️ No pude armarle los productos ahora mismo (la tienda ya está publicada igual).');
                }
                $cerrar_armado();
            }
            return $loguear('sup_productos');
        };

        switch ($paso) {

            /* ============================================================ 🚪 ARRANQUE */
            case 'arranque':
                if ($valor === 'ayuda') return $loguear('ayuda');
                // 🔴 LO PRIMERO ES LA UBICACIÓN (orden del jefe): después vienen las fotos.
                return $loguear('ubicacion');

            case 'ayuda':
                return $loguear('ubicacion');

            /* ============================================================ 📍 LA UBICACIÓN */
            case 'ubicacion':
                // Se comparte la ubicación (GPS) o se toca el distrito: las dos cosas llevan a las fotos.
                if ($valor === 'gps' && $lat !== null && $lng !== null) {
                    $dd = tienda_ia_distrito_por_gps($lat, $lng);
                    $d['lat'] = $lat; $d['lng'] = $lng;
                    if ($dd) {
                        $d['distrito_id']     = (int)$dd['distrito_id'];
                        $d['distrito_nombre'] = (string)$dd['nombre'];
                        $empujar('📍 Ubicación recibida: están en **' . (string)$dd['nombre'] . '**.');
                    } else {
                        $empujar('📍 Ya tengo el punto exacto (el distrito lo saco después).');
                    }
                    $s['datos'] = $d;
                    return $loguear('fotos');
                }
                if (ctype_digit($valor)) {
                    $d['distrito_id'] = (int)$valor;
                    foreach (tienda_ia_distritos() as $dist) {
                        if ((int)$dist['valor'] === (int)$valor) { $d['distrito_nombre'] = (string)$dist['texto']; break; }
                    }
                    $s['datos'] = $d;
                    return $loguear('fotos');
                }
                $empujar('📍 Toca el botón de la ubicación (o el distrito) y seguimos con las fotos 👇');
                $s['paso'] = 'ubicacion'; $s['datos'] = $d; tienda_ia_guardar($s);
                $g = supremo_guion('ubicacion', $d);
                return ['ok' => true, 'tipo' => 'opciones', 'opciones' => (array)$g['opciones']];

            /* ============================================================ 📷 LAS FOTOS */
            case 'fotos':
                if ($archivos) {
                    $nuevas = []; $fallos = 0;
                    $tope = (int)SUPREMO_FOTOS_MAX;
                    foreach ($archivos as $f) {
                        if (count($d['fotos']) >= $tope) break;
                        $g = tienda_ia_guardar_foto($f, $uid, !empty($d['prueba']));
                        if (!empty($g['ok'])) { $nuevas[] = (string)$g['rel']; $d['fotos'][] = (string)$g['rel']; }
                        else $fallos++;
                    }
                    if ($fallos) $empujar("⚠️ " . $fallos . " foto(s) no se " . ($fallos === 1 ? 'pudo' : 'pudieron') . " subir (puede ser el peso). Las demás sí entraron.");

                    // 👁️ UNA sola llamada de visión para todo el lote: saca el letrero, el rubro, el
                    // teléfono, la dirección y lo que vende (es la misma lectura de El maestro).
                    $ficha = tienda_ia_ia_arranque($d['fotos'],
                        ['sesion' => (int)$s['id'], 'usuario' => $uid]);
                    if (is_array($ficha)) {
                        $d['letrero']         = (string)($ficha['letrero'] ?? '');
                        $d['rubro_leido']     = (string)($ficha['rubro'] ?? '');
                        $d['telefono_leido']  = (string)($ficha['telefono'] ?? '');
                        $d['direccion_leida'] = (string)($ficha['direccion'] ?? '');
                        $d['productos_vistos'] = (array)($ficha['productos'] ?? []);
                        $d['comentario']      = (string)($ficha['comentario'] ?? '');
                        $d['local_leido']     = (string)($ficha['local'] ?? '');
                        // 📷 Lo que la IA vio, en palabras sueltas: es el contexto de los productos y del copy.
                        $visto = [];
                        foreach ($d['productos_vistos'] as $pv) {
                            $t = is_array($pv) ? trim((string)($pv['titulo'] ?? '')) : trim((string)$pv);
                            $t = trim(preg_replace('/\s*\(\d+\)\s*$/', '', $t));
                            if ($t !== '') $visto[] = $t;
                        }
                        if ($d['rubro_leido'] !== '') $visto[] = 'el local es una ' . $d['rubro_leido'];
                        $d['visto'] = array_slice($visto, 0, 8);
                        // 🔴 SIN TEXTO DE RELLENO (orden del jefe, 2026-09-18: *«la IA pierde tiempo
                        //    presentando un resumen: «se ve tu tienda bien surtida de rollos…»;
                        //    procuremos evitar textos de relleno y pasar siempre directo a la acción»*).
                        //    El comentario del modelo ya NO se dice: se va derecho a la pregunta del nombre.
                    } else {
                        $empujar("📷 Ya tengo tus fotos. La IA no las pudo mirar ahora mismo, así que te pregunto lo principal.");
                    }
                    $s['datos'] = $d;
                    if (count($d['fotos']) < 1) {
                        $s['paso'] = 'fotos';
                        tienda_ia_guardar($s);
                        return ['ok' => true, 'tipo' => 'foto', 'opciones' => []];
                    }
                    // 🔴 SIN BOTÓN DE CONFIRMACIÓN (orden del jefe, 2026-09-18): *«después de subir las
                    //    fotos aparece un botón de confirmación «ya está, seguir»: es un paso
                    //    innecesario; si ya lo subió, de frente debe decir qué nombre se le ha ocurrido»*.
                    //    Con las fotos ya leídas, el motor **pasa solo** a la pregunta del nombre: la IA
                    //    propone uno y se pregunta con Sí / No (y Escribir desde la segunda propuesta).
                    $d['sup_nombres']      = [];
                    $d['sup_nombre_prop']  = '';
                    $d['sup_nombre_visto'] = 0;
                    $prop = supremo_nombre_siguiente($s, $d);
                    $s['datos'] = $d;
                    return $loguear('nombre_ok');
                }
                return $loguear('fotos');

            /* ============================================================ ✅ EL NOMBRE */
            case 'nombre_ok':
                // 📷 Si mientras responde manda MÁS fotos, se suman (sin volver a leerlas con la IA).
                if ($archivos) {
                    $n = 0;
                    foreach ($archivos as $f) {
                        if (count($d['fotos']) >= (int)SUPREMO_FOTOS_MAX) break;
                        $g = tienda_ia_guardar_foto($f, $uid, !empty($d['prueba']));
                        if (!empty($g['ok'])) { $d['fotos'][] = (string)$g['rel']; $n++; }
                    }
                    if ($n) $empujar('📷 Anoté **' . $n . '** foto(s) más.');
                    $s['paso'] = 'nombre_ok'; $s['datos'] = $d; tienda_ia_guardar($s);
                    $g = supremo_guion('nombre_ok', $d);
                    return ['ok' => true, 'tipo' => 'foto', 'opciones' => (array)$g['opciones']];
                }
                if (mb_strpos($valor, 'si:') === 0) {
                    $d['nombre'] = tienda_ia_limpiar(mb_substr($valor, 3), 60);
                } elseif ($valor === 'no') {
                    // ❌ El cliente dijo que no: se propone el SIGUIENTE nombre (de la cola, al instante).
                    $sig = supremo_nombre_siguiente($s, $d);
                    if ($sig === '') {
                        $empujar("✏️ No se me ocurren más: escríbeme el nombre del negocio 👇");
                        $d['sup_nombre_prop'] = '';
                        $s['paso'] = 'nombre_ok'; $s['datos'] = $d; tienda_ia_guardar($s);
                        return ['ok' => true, 'tipo' => 'texto', 'opciones' => []];
                    }
                    $empujar('🏪 ¿Y **' . $sig . '**?');
                    $s['paso'] = 'nombre_ok'; $s['datos'] = $d; tienda_ia_guardar($s);
                    $g = supremo_guion('nombre_ok', $d);
                    return ['ok' => true, 'tipo' => 'opciones', 'opciones' => (array)$g['opciones']];
                } elseif ($valor === 'escribir' || $valor === 'otro') {
                    $empujar("✏️ Escribe el nombre del negocio aquí 👇");
                    $s['paso'] = 'nombre_ok'; $s['datos'] = $d; tienda_ia_guardar($s);
                    return ['ok' => true, 'tipo' => 'texto', 'opciones' => []];
                } elseif ($texto !== '') {
                    // ✅ SIEMPRE vale escribir: lo que llegue por el compositor es la respuesta.
                    $v = tienda_ia_nombre_validar($d, $texto, ['sesion' => (int)$s['id'], 'usuario' => $uid]);
                    if (empty($v['ok'])) {
                        $empujar((string)$v['frase']);
                        $s['paso'] = 'nombre_ok'; $s['datos'] = $d; tienda_ia_guardar($s);
                        return ['ok' => true, 'tipo' => 'texto', 'opciones' => []];
                    }
                    $d['nombre'] = (string)$v['nombre'];
                    if (trim((string)$v['frase']) !== '') $empujar((string)$v['frase']);
                }
                if (trim((string)$d['nombre']) === '') {
                    $s['paso'] = 'nombre_ok'; $s['datos'] = $d; tienda_ia_guardar($s);
                    return ['ok' => true, 'tipo' => 'texto', 'opciones' => []];
                }
                $empujar('🏪 Perfecto: **' . (string)$d['nombre'] . '**.');
                return $loguear('rubro_ok');

            /* ============================================================ 🏷️ EL RUBRO */
            case 'rubro_ok':
                // ✅ «Sí, es <rubro leído>»: se busca ese rubro REAL en el directorio (sin IA).
                if ($valor === 'si') {
                    $leido = trim((string)($d['rubro_leido'] ?? ''));
                    $cid = 0; $cnom = '';
                    foreach (tienda_ia_rubros_candidatos($leido, 1) as $c) { $cid = (int)$c['id']; $cnom = (string)$c['nombre']; break; }
                    if ($cid <= 0) {
                        // No existe tal cual en el directorio: se muestran las opciones para que elija.
                        $d['sup_rubro_opciones'] = '1';
                        $s['paso'] = 'rubro_ok'; $s['datos'] = $d; tienda_ia_guardar($s);
                        $g = supremo_guion('rubro_ok', $d);
                        return ['ok' => true, 'tipo' => 'opciones', 'opciones' => (array)$g['opciones']];
                    }
                    $d['rubro_id'] = $cid; $d['rubro_nombre'] = $cnom;
                    $empujar('🏷️ Rubro: **' . $cnom . '**.');
                    $s['datos'] = $d;
                    return $loguear('rubro_mas');
                }
                // ❌ «No, es otro» → la grilla 2 × 2 con tres rubros y «Ninguno de estos».
                if ($valor === 'otro' && $texto === '') {
                    $d['sup_rubro_opciones'] = '1';
                    $s['paso'] = 'rubro_ok'; $s['datos'] = $d; tienda_ia_guardar($s);
                    $g = supremo_guion('rubro_ok', $d);
                    return ['ok' => true, 'tipo' => 'opciones', 'opciones' => (array)$g['opciones']];
                }
                if ($valor !== '' && $valor !== 'otro' && ctype_digit($valor)) {
                    $d['rubro_id'] = (int)$valor;
                    foreach (tienda_ia_rubros() as $r) {
                        if ((int)$r['id'] === (int)$valor) { $d['rubro_nombre'] = (string)$r['nombre']; break; }
                    }
                    $d['sup_rubro_opciones'] = '';
                    return $loguear('rubro_mas');
                }
                // «Ninguno de estos»: se buscan los rubros reales por lo que la IA leyó o por lo escrito.
                $buscar = $texto !== '' ? $texto : (string)($d['rubro_leido'] ?? '');
                if (trim($buscar) !== '') {
                    $c = tienda_ia_rubros_candidatos($buscar, 4);
                    if ($c) {
                        $op = [];
                        foreach ($c as $x) {
                            $op[] = ['texto' => ($x['icono'] !== '' ? $x['icono'] . ' ' : '') . (string)$x['nombre'],
                                     'valor' => (string)$x['id'], 'rejilla' => 1];
                        }
                        $empujar("🏷️ De lo que me dijiste, estos rubros existen en el directorio. Toca el suyo 👇");
                        $s['paso'] = 'rubro_ok'; $s['datos'] = $d; tienda_ia_guardar($s);
                        return ['ok' => true, 'tipo' => 'opciones', 'opciones' => $op];
                    }
                    $empujar("🤔 No encontré ese rubro. Prueba con la palabra más simple (ferretería, pollería, botica…).");
                    $s['paso'] = 'rubro_ok'; $s['datos'] = $d; tienda_ia_guardar($s);
                    return ['ok' => true, 'tipo' => 'texto', 'opciones' => []];
                }
                $empujar("✏️ Escríbeme de qué es el negocio (una palabra: ferretería, pollería, botica…):");
                $s['paso'] = 'rubro_ok'; $s['datos'] = $d; tienda_ia_guardar($s);
                return ['ok' => true, 'tipo' => 'texto', 'opciones' => []];

            case 'rubro_mas':
                if ($valor === 'seguir' || $valor === '') {
                    $s['datos'] = $d;
                    return $loguear('tipo');
                }
                // Suma un rubro más (hasta 4 en total) y se queda en el mismo paso.
                $ya = array_map(function ($r) { return (int)$r['id']; }, (array)($d['rubros_extra'] ?? []));
                if (ctype_digit($valor) && !in_array((int)$valor, $ya, true)) {
                    $nom = '';
                    foreach (tienda_ia_rubros() as $r) { if ((int)$r['id'] === (int)$valor) { $nom = (string)$r['nombre']; break; } }
                    if ($nom !== '') {
                        $d['rubros_extra'][] = ['id' => (int)$valor, 'nombre' => $nom];
                        $empujar('🏷️ Le sumé **' . $nom . '**.');
                    }
                }
                $s['datos'] = $d;
                $g = supremo_guion('rubro_mas', $d);
                return ['ok' => true, 'tipo' => 'opciones', 'opciones' => (array)$g['opciones']];

            /* ============================================================ 🏪 CÓMO ATIENDE */
            // 🔴 YA NO SE PREGUNTA LA DIRECCIÓN (orden del jefe, 2026-09-18): *«luego pregunta la
            //    dirección; se supone que ya pusimos la ubicación, ya no debe pedir dirección; si ya
            //    tiene la ubicación ahí no es necesario que pida la dirección»*. Quien tiene su local
            //    pasa derecho al WhatsApp del cliente (su zona y su punto ya se guardaron al principio).
            case 'tipo':
                $v = in_array($valor, ['fisica', 'ambulante', 'domicilio', 'nacional'], true) ? $valor : 'fisica';
                $d['vendedor'] = $v;
                if ($v === 'domicilio') return $loguear('entregas');
                if ($v === 'ambulante') return $loguear('horario');
                return $loguear('cliente');

            case 'direccion':
                if (mb_strpos($valor, 'usar:') === 0) $d['direccion'] = mb_substr($valor, 5);
                elseif ($texto !== '')            $d['direccion'] = tienda_ia_limpiar($texto, 160);
                if (trim((string)$d['direccion']) === '') {
                    $empujar("📍 Escríbeme la dirección o una referencia para llegar:");
                    $s['paso'] = 'direccion'; $s['datos'] = $d; tienda_ia_guardar($s);
                    return ['ok' => true, 'tipo' => 'texto', 'opciones' => []];
                }
                // 🔴 LA ZONA YA SE PREGUNTÓ LO PRIMERO (orden del jefe, 2026-09-18: *«lo primero que
                //    debe pedir es la ubicación»*): aquí NO se vuelve a preguntar. Se sigue donde
                //    estábamos (`sup_volver`, cuando el dato se tocó desde el menú o desde «Piensa
                //    mejor») o se pasa al WhatsApp del cliente.
                return $loguear(supremo_volver($d));

            case 'horario':
                if ($valor === 'despues') { /* se queda vacío */ }
                elseif ($valor === 'escribir') {
                    $empujar("🕐 Escríbeme el horario (por ejemplo: «lunes a sábado de 8 a 8»):");
                    $s['paso'] = 'horario'; $s['datos'] = $d; tienda_ia_guardar($s);
                    return ['ok' => true, 'tipo' => 'texto', 'opciones' => []];
                } elseif ($texto !== '')  $d['horario'] = tienda_ia_limpiar($texto, 60);
                elseif ($valor !== '')    $d['horario'] = tienda_ia_limpiar($valor, 60);
                return $loguear(supremo_volver($d));

            case 'entregas':
                $lista = array_map('intval', (array)($d['entregas'] ?? []));
                if ($valor === 'todos') {
                    $d['distrito_todas'] = 1;
                    foreach (tienda_ia_distritos() as $dist) { $lista[] = (int)$dist['valor']; }
                    $d['entregas'] = array_values(array_unique($lista));
                    $empujar("🗺️ Listo: atiende en **toda la provincia del Santa**.");
                    $s['datos'] = $d; $s['paso'] = 'entregas'; tienda_ia_guardar($s);
                    $g = supremo_guion('entregas', $d);
                    return ['ok' => true, 'tipo' => 'opciones', 'opciones' => (array)$g['opciones']];
                }
                if ($valor === 'seguir') {
                    if (!$lista) {
                        $empujar("🌐 Marca al menos un distrito (o toca «🗺️ Todos los distritos»):");
                        $s['datos'] = $d; $s['paso'] = 'entregas'; tienda_ia_guardar($s);
                        $g = supremo_guion('entregas', $d);
                        return ['ok' => true, 'tipo' => 'opciones', 'opciones' => (array)$g['opciones']];
                    }
                    return $loguear(supremo_volver($d));
                }
                if (ctype_digit($valor)) {
                    $i = array_search((int)$valor, $lista, true);
                    if ($i === false) $lista[] = (int)$valor; else unset($lista[$i]);
                    $d['entregas'] = array_values(array_unique($lista));
                }
                $s['datos'] = $d; $s['paso'] = 'entregas'; tienda_ia_guardar($s);
                $g = supremo_guion('entregas', $d);
                return ['ok' => true, 'tipo' => 'opciones', 'opciones' => (array)$g['opciones']];

            case 'distrito':
                if ($valor === 'gps' && $lat !== null && $lng !== null) {
                    $dd = tienda_ia_distrito_por_gps($lat, $lng);
                    if ($dd) {
                        $d['distrito_id']     = (int)$dd['distrito_id'];
                        $d['distrito_nombre'] = (string)$dd['nombre'];
                        $d['lat'] = $lat; $d['lng'] = $lng;
                        $empujar('📍 Según tu ubicación, están en **' . (string)$dd['nombre'] . '**.');
                        return $loguear(supremo_volver($d));
                    }
                    $empujar("📍 No pude sacar el distrito de la ubicación. Toca el suyo 👇");
                    $s['datos'] = $d; $s['paso'] = 'distrito'; tienda_ia_guardar($s);
                    $g = supremo_guion('distrito', $d);
                    return ['ok' => true, 'tipo' => 'opciones', 'opciones' => (array)$g['opciones']];
                }
                if (ctype_digit($valor)) {
                    $d['distrito_id'] = (int)$valor;
                    foreach (tienda_ia_distritos() as $dist) {
                        if ((int)$dist['valor'] === (int)$valor) { $d['distrito_nombre'] = (string)$dist['texto']; break; }
                    }
                    if (empty($d['distrito_nombre'])) {
                        $d['distrito_nombre'] = (string)($d['distrito_leido'] ?? '');
                    }
                    return $loguear(supremo_volver($d));
                }
                if ($texto !== '') {
                    $dd = tienda_ia_distrito_leido($texto);
                    if ($dd) {
                        $d['distrito_id'] = (int)$dd['id'];
                        $d['distrito_nombre'] = (string)$dd['texto'];
                        return $loguear(supremo_volver($d));
                    }
                }
                $empujar("📍 Toca el distrito 👇");
                $s['datos'] = $d; $s['paso'] = 'distrito'; tienda_ia_guardar($s);
                $g = supremo_guion('distrito', $d);
                return ['ok' => true, 'tipo' => 'opciones', 'opciones' => (array)$g['opciones']];

            /* ============================================================ 📱 EL CLIENTE Y LA PUBLICACIÓN */
            // 🔴 EL WHATSAPP ES OBLIGATORIO (orden del jefe, 2026-09-18: *«no aceptamos tiendas sin
            //    número de WhatsApp, simplemente no aceptamos»*): se ofrece el número que la IA leyó en
            //    las fotos con Sí / No / Escribir, y si escribe, lo que escriba es la respuesta.
            case 'cliente':
                if ($valor === 'despues') {
                    $empujar('📱 Sin número no publico la tienda: el cliente se quedaría sin su cuenta. Escríbeme su WhatsApp y seguimos.');
                    $s['paso'] = 'cliente'; $s['datos'] = $d; tienda_ia_guardar($s);
                    return ['ok' => true, 'tipo' => 'texto', 'opciones' => []];
                }
                if ($valor === 'si' && $texto === '') {
                    // ✅ «Sí, es ese»: el número que la IA leyó en las fotos.
                    $tel = preg_replace('/\D+/', '', (string)($d['telefono_leido'] ?? ''));
                    if (mb_strlen($tel) === 11 && substr($tel, 0, 2) === '51') $tel = substr($tel, 2);
                    $valor = $tel;
                } elseif ($valor === 'otro' || $valor === 'escribir') {
                    $d['sup_wa_preguntado'] = 1;
                    $empujar('📱 Escríbeme su número de WhatsApp (9 dígitos, por ejemplo **943112233**) 👇');
                    $s['paso'] = 'cliente'; $s['datos'] = $d; tienda_ia_guardar($s);
                    return ['ok' => true, 'tipo' => 'texto', 'opciones' => []];
                } elseif (mb_strpos($valor, 'si|') === 0) {
                    $valor = mb_substr($valor, 3);
                }
                $tel = preg_replace('/\D+/', '', $valor !== '' ? $valor : $texto);
                if (mb_strlen($tel) === 11 && substr($tel, 0, 2) === '51') $tel = substr($tel, 2);
                if (mb_strlen($tel) < 6) {
                    $empujar('🤔 Ese número no me cuadra. Escríbelo de nuevo, solo los dígitos (9 dígitos):');
                    $d['sup_wa_preguntado'] = 1;
                    $s['paso'] = 'cliente'; $s['datos'] = $d; tienda_ia_guardar($s);
                    return ['ok' => true, 'tipo' => 'texto', 'opciones' => []];
                }
                $d['whatsapp'] = $tel;
                $s['datos'] = $d;

                // 🚀 LA PUBLICACIÓN EN VIVO
                $pub = supremo_publicar($s, $d);
                if (empty($pub['ok'])) {
                    $empujar('😅 ' . (string)($pub['error'] ?? 'No pude publicar la tienda.'));
                    $s['paso'] = 'cliente'; $s['datos'] = $d; tienda_ia_guardar($s);
                    return ['ok' => true, 'tipo' => 'texto', 'opciones' => []];
                }
                $d = $s['datos'];   // `supremo_publicar()` dejó aquí el relato que usó el copywriting
                $d['sup_negocio'] = ['id' => (int)$pub['negocio_id'], 'slug' => (string)$pub['slug'],
                                     'url' => (string)$pub['url'], 'nombre' => (string)$pub['nombre']];
                $d['publicado']   = ['negocio_id' => (int)$pub['negocio_id'], 'slug' => (string)$pub['slug'],
                                     'url' => (string)$pub['url']];
                $d['negocio_id']  = (int)$pub['negocio_id'];
                $d['sup_usuario'] = (string)($pub['cuenta']['usuario'] ?? $tel);
                $d['sup_clave']   = (string)($pub['cuenta']['clave'] ?? '');
                // 📝 La descripción que la IA acaba de escribir en la ficha: es lo que alimenta el
                //    «Cuerpo» del prompt de música (~50 palabras de la descripción REAL).
                $desc_real = supremo_descripcion_guardada((int)$pub['negocio_id']);
                if ($desc_real !== '') $d['descripcion_ia'] = $desc_real;

                $empujar("🚀 **¡Ya está en internet!** Mírala aquí 👇\n" . (string)$pub['url']);
                if ((string)($pub['cuenta']['clave'] ?? '') !== '') {
                    $empujar("🔑 Su cuenta quedó así:\n👤 usuario **" . (string)$d['sup_usuario'] . "**\n🔑 clave **" . (string)$d['sup_clave'] . "**");
                }
                // ⚠️ Aquí NO se gasta IA (orden del jefe, 2026-09-18: la venta va contra reloj). Las
                //    primeras opiniones y la descripción buena se hacen en el paso siguiente, que es
                //    donde el vendedor ya está esperando a que se armen los 8 productos.

                $s['datos'] = $d;
                // 🎨 LA PORTADA YA NO SE PREGUNTA (orden del jefe, 2026-09-18: *«la portada va a ser
                //    solamente un texto artístico encima de una foto que tenga que ver con el rubro…
                //    una foto genérica con un texto artístico encima»*): siempre se entrega el código
                //    de la cabecera (que ya pide justo eso) y la venta sigue derechito a los productos.
                $d['sup_portada'] = 'ia';
                $empujar('🎨 **La portada ya está lista en los códigos:** un texto artístico grande con el nombre '
                       . 'encima de una foto del rubro, tal como me lo pediste. En el paso de los códigos la copias.');
                $s['datos'] = $d;
                return $ir_productos();

            /* ============================================================ 👑 LA PORTADA */
            case 'sup_portada':
                if ($valor === 'fotos') {
                    return $loguear('sup_portada_fotos');
                }
                if ($valor === 'subir') {
                    $empujar("📤 Mándame la portada 📷");
                    $s['paso'] = 'sup_portada'; $s['datos'] = $d; tienda_ia_guardar($s);
                    return ['ok' => true, 'tipo' => 'foto', 'opciones' => []];
                }
                if ($archivos) {
                    $g = tienda_ia_guardar_foto($archivos[0], $uid, !empty($d['prueba']));
                    if (!empty($g['ok'])) {
                        $d['fotos'][] = (string)$g['rel'];
                        if (supremo_portada_poner(supremo_negocio_id($d), (string)$g['rel'])) {
                            $d['sup_portada'] = 'subida';
                            $empujar("🖼️ **Portada puesta.** Es la primera que ve el cliente.");
                            $s['datos'] = $d;
                            return $ir_productos();
                        }
                    }
                    $empujar("😅 No pude guardar esa imagen. Prueba otra vez.");
                    $s['paso'] = 'sup_portada'; $s['datos'] = $d; tienda_ia_guardar($s);
                    return ['ok' => true, 'tipo' => 'foto', 'opciones' => []];
                }
                if ($valor === 'crear') {
                    $d['sup_portada'] = 'ia';
                    $empujar("🎨 Perfecto: en el paso de los códigos te lo dejo listo para copiar y pegar en Flow, con una imagen de referencia adjunta.");
                    $s['datos'] = $d;
                    return $ir_productos();
                }
                $d['sup_portada'] = 'despues';
                $s['datos'] = $d;
                return $ir_productos();

            case 'sup_portada_fotos':
                if ($valor === 'volver' || $valor === '') return $loguear('sup_portada');
                if (preg_match('/^usar_foto:(\d+)$/', $valor, $m)) {
                    $fotos = array_values((array)$d['fotos']);
                    $i = (int)$m[1];
                    if (isset($fotos[$i])) {
                        if (supremo_portada_poner(supremo_negocio_id($d), (string)$fotos[$i])) {
                            $d['sup_portada'] = 'foto';
                            $empujar("🖼️ **Portada puesta** con la foto " . ($i + 1) . ".");
                            $s['datos'] = $d;
                            return $ir_productos();
                        }
                    }
                    $empujar("😅 Esa foto no se pudo poner de portada. Prueba con otra.");
                }
                $s['paso'] = 'sup_portada_fotos'; $s['datos'] = $d; tienda_ia_guardar($s);
                return ['ok' => true, 'tipo' => 'opcion', 'opciones' => [['texto' => '⬅️ Volver', 'valor' => 'volver']]];

            /* ============================================================ 🛍️ LOS 8 PRODUCTOS */
            // 🔴 SIN PREGUNTAR NADA (orden del jefe): el sistema crea los 8 y pide sus imágenes.
            case 'sup_productos':
                if ($valor === 'otra') {
                    // Se rehace: se borran los que había creado esta venta y se vuelve a armar.
                    foreach ((array)($d['sup_creados'] ?? []) as $p) {
                        try { db()->prepare("DELETE FROM directorio_producto_fotos WHERE producto_id = ?")->execute([(int)$p['id']]); } catch (Throwable $e) {}
                        try { db()->prepare("DELETE FROM directorio_servicios WHERE id = ?")->execute([(int)$p['id']]); } catch (Throwable $e) {}
                    }
                    $d['sup_creados'] = []; $d['sup_inventados'] = []; $d['sup_con_ia'] = [];
                    $s['datos'] = $d;
                }
                if (empty($d['sup_creados'])) {
                    $r = supremo_armar_productos($s, $d, $empujar);
                    $d = $s['datos'];
                    if (!empty($r['hechos'])) {
                        $empujar("🛍️ **" . (int)$r['hechos'] . ' productos listos** en su tienda: '
                               . (int)$r['vistos'] . ' de sus fotos' . ((int)$r['inventados'] > 0 ? ' y ' . (int)$r['inventados'] . ' que inventé con el contexto' : '') . '.');
                    } else {
                        $empujar("🛍️ No pude armarle los productos ahora mismo (la tienda ya está publicada igual).");
                    }
                    $cerrar_armado();
                    $s['datos'] = $d;
                    return $loguear('sup_productos');
                }
                $s['datos'] = $d;
                return $loguear('sup_codigos');

            /* ============================================================ 🧠 VIEJO: LOS PRODUCTOS VISTOS */
            // ⚠️ LEGADO: mismo motivo que en el guion (una venta vieja con este paso no se rompe).
            case 'sup_inventar':
                if ($valor === 'nada') return $loguear('sup_codigos');
                if ($valor === 'otra') {
                    $d['sup_inventados'] = [];
                    $d['sup_veces'] = (int)($d['sup_veces'] ?? 0) + 1;
                    $s['datos'] = $d;
                }
                if (empty($d['sup_inventados'])) {
                    $inv = supremo_ia_inventar($d, ['sesion' => (int)$s['id'], 'usuario' => $uid]);
                    if (!$inv) {
                        $empujar("🤔 La IA no me dio productos esta vez. Puedes seguir igual: la tienda ya está publicada "
                               . "y los productos se agregan cuando quieras.");
                        $s['datos'] = $d;
                        return $loguear('sup_codigos');
                    }
                    $d['sup_inventados'] = $inv;
                    $s['datos'] = $d;
                    return $loguear('sup_inventar');
                }
                // `crear` → se crean de verdad en la tienda y se marcan para llevar imagen de la IA.
                $neg_id = supremo_negocio_id($d);
                $hechos = [];
                foreach ((array)$d['sup_inventados'] as $p) {
                    $desc = trim((string)($p['frase'] ?? ''));
                    if ($desc === '') $desc = (string)$p['titulo'] . ' de ' . (string)$d['nombre'] . '.';
                    $pid = supremo_crear_producto($neg_id, (string)$p['titulo'], $desc, (float)($p['precio'] ?? 0), '');
                    if ($pid > 0) {
                        $hechos[] = (string)$p['titulo'];
                        $d['sup_creados'][] = ['id' => $pid, 'titulo' => (string)$p['titulo'], 'con_foto' => false];
                        $con_ia = array_map('intval', (array)($d['sup_con_ia'] ?? []));
                        if (!in_array($pid, $con_ia, true)) $con_ia[] = $pid;
                        $d['sup_con_ia'] = $con_ia;
                    }
                }
                if ($hechos) {
                    $empujar("🧠 **" . count($hechos) . " producto(s) creados** en su tienda: " . implode(', ', $hechos) . ".");
                }
                $s['datos'] = $d;
                // 🖼️ Antes de los códigos se le pregunta A CUÁLES les pedimos su imagen (lo pidió el jefe).
                return $loguear('sup_ia_elegir');

            /* ============================================================ 🖼️ A CUÁLES LES HACEMOS LA IMAGEN */
            case 'sup_ia_elegir':
                $neg_id = supremo_negocio_id($d);
                $sin_foto = supremo_productos_sin_foto($neg_id);
                if (!$sin_foto) {
                    $empujar("🖼️ Todos los productos ya tienen su foto: no hay que pedirle imágenes a la IA.");
                    $d['sup_con_ia'] = [];
                    $s['datos'] = $d;
                    return $loguear('sup_codigos');
                }
                if ($valor === 'nada') {
                    $d['sup_con_ia'] = [];
                    $empujar("👌 Sin imágenes de la IA: los productos quedan sin foto hasta que se las tomen.");
                    $s['datos'] = $d;
                    return $loguear('sup_codigos');
                }
                if ($valor === 'todos') {
                    $ids = [];
                    foreach ($sin_foto as $p) { $ids[] = (int)$p['id']; }
                    $d['sup_con_ia'] = $ids;
                } else {
                    // `incluir:0,2` → los índices de la rejilla de ESTE paso (el navegador manda sus posiciones).
                    $marcados = [];
                    if (preg_match('/^incluir:(.*)$/', $valor, $m)) {
                        foreach (explode(',', $m[1]) as $ix) { if (trim($ix) !== '') $marcados[] = (int)$ix; }
                    }
                    $ids = [];
                    foreach ($sin_foto as $p) {
                        if (in_array((int)$p['indice'], $marcados, true)) $ids[] = (int)$p['id'];
                    }
                    $d['sup_con_ia'] = $ids;
                }
                if ($d['sup_con_ia']) {
                    $empujar("🖼️ Listo: le pido la imagen a **" . count((array)$d['sup_con_ia']) . " producto(s)**. "
                           . "En el paso de los códigos está el pedido con el ID de cada uno.");
                } else {
                    $empujar("👌 Ninguno marcado: no se piden imágenes de productos.");
                }
                $s['datos'] = $d;
                return $loguear('sup_codigos');

            /* ============================================================ 👑 LOS CÓDIGOS */
            case 'sup_codigos':
                if ($valor === 'ir_imagenes') return $loguear('sup_imagenes');
                if ($valor === 'cancion_ir')  return $loguear('sup_cancion');
                $s['datos'] = $d;
                return ['ok' => true, 'tipo' => 'codigos', 'opciones' => (array)supremo_guion('sup_codigos', $d)['opciones']];

            /* ============================================================ 📥 LA SALA DE ESPERA */
            case 'sup_imagenes':
                if ($valor === 'codigos') return $loguear('sup_codigos');
                // 🎵 Antes de la oferta pasa por LA CANCIÓN (es donde se sube el audio que bajó el jefe).
                if ($valor === 'cancion') return $loguear('sup_cancion');
                // 🧠 Y antes de la oferta, por PIENSA MEJOR (revisa la tienda y completa lo que falte).
                if ($valor === 'cerrar' || $valor === 'oferta') return $loguear('sup_piensa');
                if ($archivos) {
                    $rels = []; $nombres = [];
                    foreach (array_slice($archivos, 0, (int)SUPREMO_IMAGENES_LOTE) as $f) {
                        $g = tienda_ia_guardar_foto($f, $uid, !empty($d['prueba']));
                        if (!empty($g['ok'])) { $rels[] = (string)$g['rel']; $nombres[] = (string)($f['name'] ?? ''); }
                    }
                    if (!$rels) {
                        $empujar("😅 No se pudo guardar ninguna imagen. Prueba otra vez (mira que no pesen demasiado).");
                        $s['paso'] = 'sup_imagenes'; $s['datos'] = $d; tienda_ia_guardar($s);
                        return ['ok' => true, 'tipo' => 'foto', 'opciones' => []];
                    }
                    $r = supremo_imagenes_publicar($s, $rels, $nombres);
                    $d = $s['datos'];
                    $puestas = (array)($r['puestas'] ?? []);
                    foreach ($puestas as $p) {
                        $d['sup_imagenes'][] = ['que' => (string)$p['que'], 'id' => (int)($p['id'] ?? 0),
                                                'titulo' => (string)$p['titulo'], 'archivo' => (string)$p['archivo']];
                    }
                    if ($puestas) {
                        $t = "✅ **" . count($puestas) . " imagen(es) publicada(s):**\n";
                        foreach ($puestas as $p) {
                            $t .= '· ' . (string)$p['titulo'] . ($p['que'] === 'portada' ? ' (la portada 🖼️)' : '') . "\n";
                        }
                        $empujar($t);
                    }
                    if (!empty($r['fallaron'])) {
                        $empujar("⚠️ **" . count($r['fallaron']) . " imagen(es) no se pudieron ubicar** porque no les leí el número de ID: "
                               . implode(', ', array_slice((array)$r['fallaron'], 0, 5)) . ".\n\n"
                               . "No las publiqué a la loca (podrían ir al producto equivocado). Pídele al diseñador que las rehaga con su número impreso y las subes otra vez.");
                    }
                    $s['datos'] = $d;
                    $g = supremo_guion('sup_imagenes', $d);
                    $empujar((string)$g['texto']);
                    $s['paso'] = 'sup_imagenes'; tienda_ia_guardar($s);
                    return ['ok' => true, 'tipo' => 'foto', 'opciones' => (array)$g['opciones']];
                }
                $s['paso'] = 'sup_imagenes'; $s['datos'] = $d; tienda_ia_guardar($s);
                return ['ok' => true, 'tipo' => 'foto', 'opciones' => []];

            /* ============================================================ 🎵 LA CANCIÓN */
            case 'sup_cancion':
                if ($valor === 'oferta')  return $loguear('sup_piensa');
                // 🎵 El botón «Clic aquí para cargar la canción»: se abre el selector de audio y se
                //    queda esperando el archivo (el navegador abre el explorador con ese valor).
                if ($valor === 'cargar_cancion') {
                    $s['paso'] = 'sup_cancion'; $s['datos'] = $d; tienda_ia_guardar($s);
                    return ['ok' => true, 'tipo' => 'audio', 'opciones' => []];
                }
                if ($valor === 'despues' || $valor === 'cerrar') {
                    if ($valor === 'cerrar') { $d['sup_fin'] = time(); $s['datos'] = $d; return $loguear('sup_fin'); }
                    return $loguear('sup_piensa');
                }
                if ($valor === 'cambiar') {
                    $empujar("🎵 Mándame el otro audio y lo reemplazo 👇");
                    $s['paso'] = 'sup_cancion'; $s['datos'] = $d; tienda_ia_guardar($s);
                    return ['ok' => true, 'tipo' => 'audio', 'opciones' => []];
                }
                if ($archivos) {
                    $r = supremo_cancion_subir($s, $archivos[0]);
                    if (empty($r['ok'])) {
                        $empujar('😅 ' . (string)($r['error'] ?? 'No pude guardar la canción.'));
                        $s['paso'] = 'sup_cancion'; $s['datos'] = $d; tienda_ia_guardar($s);
                        return ['ok' => true, 'tipo' => 'audio', 'opciones' => []];
                    }
                    $empujar("🎵 **¡Canción puesta!** Ya suena sola en la ficha del cliente.\n\n"
                           . '· ' . number_format((float)$r['duracion'], 0) . ' segundos · '
                           . number_format((float)$r['peso_mb'], 2) . " MB\n"
                           . '· ' . img_url((string)$r['ruta']));
                    $s['datos'] = $d;
                    return $loguear('sup_piensa');
                }
                $s['paso'] = 'sup_cancion'; $s['datos'] = $d; tienda_ia_guardar($s);
                return ['ok' => true, 'tipo' => 'audio', 'opciones' => []];

            /* ============================================================ 🧠 PIENSA MEJOR */
            // Orden del jefe (2026-09-18): *«debe existir un botón de piensa mejor que lo que hace es
            // analizar lo que el usuario publicó y volver a reestructurar los datos, y si es necesario
            // pedir datos que se haya faltado»*.
            //   1. Se analiza UNA vez (una llamada de IA) y se aplica lo que mejora: la descripción de
            //      la tienda y los títulos/precios de sus productos.
            //   2. Si falta algún dato, se ofrece su botón: se abre ese paso y **se vuelve aquí mismo**
            //      (el paso de origen va en `sup_volver`), así no se pierde el hilo de la venta.
            case 'sup_piensa':
                // 🔁 Volver de haber puesto un dato suelto: NO se vuelve a llamar a la IA (ya está).
                if ($valor === 'otra') {
                    $d['sup_piensa_hecha'] = 0;
                    $d['sup_piensa_nota']  = '';
                    $d['sup_piensa_fix']   = [];
                    $s['datos'] = $d;
                }
                if (empty($d['sup_piensa_hecha'])) {
                    $ia = supremo_ia_piensa($d, ['sesion' => (int)$s['id'], 'usuario' => $uid]);
                    $d['sup_piensa_hecha'] = 1;
                    $d['sup_piensa_nota']  = (string)($ia['nota'] ?? '');
                    $fix = supremo_piensa_aplicar($s, $d, $ia);
                    $d['sup_piensa_fix'] = $fix;
                    if (!empty($fix['descripcion'])) {
                        $empujar("🧠 **Le reescribí la descripción** a la tienda con lo que ya sabemos (sin inventar nada).");
                    }
                    if (!empty($fix['productos'])) {
                        $t = "🧠 **Le dejé mejor los productos:**\n";
                        foreach (array_slice((array)$fix['productos'], 0, 8) as $q) { $t .= '· ' . (string)$q . "\n"; }
                        $empujar($t);
                    }
                    if (trim((string)$d['sup_piensa_nota']) !== '') $empujar('🧠 ' . (string)$d['sup_piensa_nota']);
                    if (empty($fix['descripcion']) && empty($fix['productos']) && trim((string)$d['sup_piensa_nota']) === '') {
                        $empujar('🧠 Lo revisé todo y **está bien como está**: no encontré nada que reestructurar.');
                    }
                    $s['datos'] = $d;
                    return $loguear('sup_piensa');
                }
                // «ir:<paso>» → abre el paso del dato que falta y vuelve aquí al terminar.
                if (preg_match('/^ir:([a-z_]+)$/', $valor, $m) && isset(supremo_pasos()[$m[1]])) {
                    $d['sup_volver'] = 'sup_piensa';
                    $s['datos'] = $d;
                    return $loguear($m[1]);
                }
                if ($valor === 'ubicacion') return supremo_zona_abrir($s);
                // ✅ «Seguir»: si el vendedor abrió 🧠 a mitad de la venta, se vuelve a SU paso; si
                //    venimos del flujo normal (canción → piensa), se sigue con la oferta.
                $volver = (string)($d['sup_piensa_volver'] ?? '');
                $d['sup_piensa_volver'] = '';
                $s['datos'] = $d;
                if ($volver === '' || $volver === 'sup_piensa' || !isset(supremo_pasos()[$volver])) $volver = 'sup_oferta';
                return $loguear($volver);

            /* ============================================================ 💰 LA OFERTA */
            case 'sup_oferta':
                if ($valor === 'cerrar' || $valor === 'saltar') {
                    $d['sup_fin'] = time();
                    $s['datos'] = $d;
                    return $loguear('sup_fin');
                }
                // 🖥️ «Ver la oferta y el guion»: el navegador abre el panel con la calculadora en vivo.
                if ($valor === 'panel') {
                    $s['datos'] = $d;
                    $g = supremo_guion('sup_oferta', $d);
                    return ['ok' => true, 'tipo' => (string)$g['tipo'], 'opciones' => (array)$g['opciones'],
                            'mensajes' => [], 'oferta_panel' => true];
                }
                if ($valor === 'otro') {
                    $empujar("✏️ Dime el precio de un producto (solo el número, por ejemplo **80**):");
                    $s['paso'] = 'sup_oferta'; $s['datos'] = $d; tienda_ia_guardar($s);
                    return ['ok' => true, 'tipo' => 'texto', 'opciones' => []];
                }
                // 💰 `precio:80|Clavos de acero` (los chips) o el precio escrito a mano.
                $nuevo = 0.0; $nombre = '';
                if (preg_match('/^precio:([0-9.]+)\|?(.*)$/u', $valor, $m)) {
                    $nuevo  = (float)$m[1];
                    $nombre = trim((string)$m[2]);
                } elseif ($texto !== '') {
                    $nuevo = supremo_precio_de($texto);
                } elseif ($valor !== '') {
                    $nuevo = supremo_precio_de($valor);
                }
                if ($nuevo <= 0) {
                    $empujar("🤔 No entendí el precio. Escríbelo solo con números (por ejemplo **80**):");
                    $s['paso'] = 'sup_oferta'; $s['datos'] = $d; tienda_ia_guardar($s);
                    return ['ok' => true, 'tipo' => 'texto', 'opciones' => []];
                }
                $d['sup_precio'] = round($nuevo, 2);
                if ($nombre !== '') $d['sup_producto'] = tienda_ia_limpiar($nombre, 60);
                $c = supremo_calculo($d['sup_precio']);
                $empujar("💰 Con " . supremo_soles($c['precio']) . " × " . (int)$c['ventas'] . " ventas = **"
                       . supremo_soles($c['ingreso']) . "** para la tienda. Mi tarifa (" . supremo_soles($c['tarifa'])
                       . ") es el **" . number_format($c['efectiva'], 2) . " %** de eso: se ahorra "
                       . supremo_soles($c['ahorro']) . ".");
                $s['datos'] = $d;
                return $loguear('sup_oferta');

            /* ============================================================ 🎉 EL CIERRE */
            case 'sup_fin':
                if ($valor === 'codigos') return $loguear('sup_codigos');
                if ($valor === 'oferta')  return $loguear('sup_oferta');
                if ($valor === 'ver_tienda') {
                    $neg = (array)($d['sup_negocio'] ?? []);
                    if (!empty($neg['url'])) $empujar("🏬 " . (string)$neg['url']);
                    $s['datos'] = $d;
                    return ['ok' => true, 'tipo' => 'opciones', 'opciones' => (array)supremo_guion('sup_fin', $d)['opciones']];
                }
                if ($valor === 'wa_cliente') {
                    $t = supremo_codigo_cliente($d);
                    if ($t !== '') {
                        $empujar("📲 Aquí tienes su mensaje: cópialo con el botón y mándaselo por WhatsApp.");
                        $s['datos'] = $d;
                        return ['ok' => true, 'tipo' => 'codigos', 'opciones' => (array)supremo_guion('sup_fin', $d)['opciones']];
                    }
                }
                // 👑 Otra venta: se cierra esta y se abre una limpia (la tienda publicada NO se toca).
                $s['datos'] = $d;
                $s['estado'] = 'publicada';
                tienda_ia_guardar($s);
                $nueva = tienda_ia_actual($uid, 'supremo', '', true);
                if ($nueva && empty($nueva['limite'])) {
                    $nueva['paso'] = 'arranque';
                    $nueva['datos'] = supremo_datos_vacios();
                    tienda_ia_guardar($nueva);
                    return ['ok' => true, 'reiniciado' => true, 'nueva_venta' => true,
                            'tipo' => 'opciones', 'opciones' => (array)supremo_guion('arranque', $nueva['datos'])['opciones'],
                            'mensajes' => [['rol' => 'bot', 'texto' => "👑 **Vamos con otra venta.**"]], 'paso' => 'arranque'];
                }
                return ['ok' => true, 'tipo' => 'opciones', 'opciones' => [], 'mensajes' => $msgs];
        }

        // Paso desconocido: se devuelve al arranque sin romper nada.
        return $loguear('arranque');
    }
}

/* =====================================================================================
 * 11) EL PANEL: el consumo y las ventas del Supremo (para su pestaña del Súper Admin)
 * ===================================================================================== */

if (!function_exists('supremo_stats')) {
    /** Cuántas ventas se hicieron, cuánto costó la IA y cómo salieron (por rango de días). */
    function supremo_stats($dias = 30) {
        $out = ['tablas' => tienda_ia_tablas_ok(), 'ventas' => 0, 'publicadas' => 0, 'en_curso' => 0,
                'productos' => 0, 'imagenes' => 0, 'llamadas' => 0, 'costo_usd' => 0.0,
                'por_venta' => 0.0, 'minutos' => 0.0, 'ultimas' => [], 'hoy' => ['llamadas' => 0, 'costo' => 0.0]];
        if (!$out['tablas']) return $out;
        $desde = date('Y-m-d H:i:s', time() - max(1, (int)$dias) * 86400);
        try {
            $pdo = db();
            $q = $pdo->prepare("SELECT COUNT(*) n, SUM(estado='publicada') pub, SUM(estado='en_curso') cur
                                  FROM " . TIENDA_IA_TABLA . " WHERE modo = 'supremo' AND creado_en >= ?");
            $q->execute([$desde]);
            $r = $q->fetch(PDO::FETCH_ASSOC) ?: [];
            $out['ventas']     = (int)($r['n'] ?? 0);
            $out['publicadas'] = (int)($r['pub'] ?? 0);
            $out['en_curso']   = (int)($r['cur'] ?? 0);

            // ⚠️ TRAMPA CAZADA POR LA SONDA (2026-09-18): al juntar las dos tablas, `costo_usd` y
            //    `creado_en` existen en LAS DOS → MySQL responde «column is ambiguous», la consulta
            //    falla y —como va dentro del try— la lista de ventas salía VACÍA sin decir nada.
            //    Por eso aquí TODAS las columnas del registro van con su alias `l.`.
            $q = $pdo->prepare("SELECT COUNT(*) n, SUM(l.costo_usd) cost, SUM(l.tokens_in + l.tokens_out) tok
                                  FROM " . TIENDA_IA_TABLA_LOG . " l
                                  JOIN " . TIENDA_IA_TABLA . " t ON t.id = l.sesion_id
                                 WHERE t.modo = 'supremo' AND l.creado_en >= ?");
            $q->execute([$desde]);
            $x = $q->fetch(PDO::FETCH_ASSOC) ?: [];
            $out['llamadas']  = (int)($x['n'] ?? 0);
            $out['costo_usd'] = (float)($x['cost'] ?? 0);
            $out['por_venta'] = $out['publicadas'] > 0 ? $out['costo_usd'] / $out['publicadas'] : 0;

            $q = $pdo->prepare("SELECT COUNT(*) n FROM " . TIENDA_IA_TABLA_LOG . " l
                                 JOIN " . TIENDA_IA_TABLA . " t ON t.id = l.sesion_id
                                WHERE t.modo = 'supremo' AND l.creado_en >= ? AND l.con_imagen > 0");
            $q->execute([$desde]);
            $out['imagenes'] = (int)$q->fetchColumn();

            $q = $pdo->prepare("SELECT t.id, t.paso, t.estado, t.negocio_id, t.datos, t.llamadas, t.costo_usd,
                                       t.creado_en, t.publicado_en
                                  FROM " . TIENDA_IA_TABLA . " t
                                 WHERE t.modo = 'supremo' AND t.creado_en >= ?
                                 ORDER BY t.id DESC LIMIT 40");
            $q->execute([$desde]);
            foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $u) {
                $dd = json_decode((string)$u['datos'], true) ?: [];
                $seg = 0;
                if (!empty($dd['sup_inicio'])) {
                    $seg = max(0, ((int)($dd['sup_fin'] ?? 0) ?: strtotime((string)$u['creado_en'])) - (int)$dd['sup_inicio']);
                }
                $u['minutos']   = $seg > 0 ? round($seg / 60, 1) : 0;
                $u['nombre']    = (string)($dd['nombre'] ?? ('venta ' . (int)$u['id']));
                $u['productos'] = count((array)($dd['sup_creados'] ?? []));
                $u['fotos']     = count((array)($dd['sup_imagenes'] ?? []));
                $u['cliente']   = (string)($dd['sup_usuario'] ?? '');
                $out['productos'] += (int)$u['productos'];
                $out['minutos']   += (float)$u['minutos'];
                $out['ultimas'][] = $u;
            }
            $out['minutos'] = $out['ultimas'] ? round($out['minutos'] / count($out['ultimas']), 1) : 0.0;

            $q = $pdo->prepare("SELECT COUNT(*) n, SUM(l.costo_usd) cost FROM " . TIENDA_IA_TABLA_LOG . " l
                                  JOIN " . TIENDA_IA_TABLA . " t ON t.id = l.sesion_id
                                 WHERE t.modo = 'supremo' AND l.creado_en >= ?");
            $q->execute([date('Y-m-d') . ' 00:00:00']);
            $x = $q->fetch(PDO::FETCH_ASSOC) ?: [];
            $out['hoy'] = ['llamadas' => (int)($x['n'] ?? 0), 'costo' => (float)($x['cost'] ?? 0)];
        } catch (Throwable $e) {
            error_log('supremo_stats: ' . $e->getMessage());
        }
        return $out;
    }
}
