<?php
/**
 * pedidos_sin_vendedor.php — «ENCARGOS» (motor de la lista pública)
 * ==========================================================================
 * Pedido del jefe (2026-09-15). Su idea, textual:
 *
 *   *«una persona está buscando alcohol isopropílico o una botella de medio litro de gas… o algo
 *   que mi página web no ha encontrado resultados suficientes. Por defecto el sitio me manda una
 *   notificación. ¿Qué tal si en lugar de mandarme una notificación me mandara un mensaje a ese
 *   lista diciendo "se ha buscado kerosene, no se han encontrado resultados; si tienes el
 *   producto en tu stock publícalo ahora mismo"? Y cuando la persona le dé clic, se abre el creador
 *   de productos o de tiendas según esté logueado o no… Así el usuario tendrá más posibilidades de
 *   encontrar su producto y el vendedor también podrá ofrecer el suyo.»*
 *
 *   *«Al comprador yo le tengo que decir algo como: no hemos encontrado tu producto, pero nuestros
 *   vendedores se van a comunicar contigo, déjanos tu número de WhatsApp. Y automáticamente en el
 *   lista lo publicaremos diciendo "el usuario número tal está buscando quien le venda zapatillas
 *   importadas de la marca Nike número 41". Así el pedido será específico y todos podrán darle clic
 *   y enviarle sus propuestas.»*
 *
 * Nombre elegido por el jefe: **Encargos** (la palabra «lista» sigue retirada del
 * sitio por su propia orden del 2026-09-13: este módulo NO se llama así ni tiene chat).
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * LAS 3 REGLAS QUE HACEN QUE ESTO NO SE CAIGA SOLO (medidas antes de construirlo)
 * ─────────────────────────────────────────────────────────────────────────────
 *  1) **NO SE PUBLICA UNA MENTIRA.** Medido el 2026-09-15: la mitad de los «0 resultados» del
 *     buscador eran falsos (decía 1 resultado para «cerveza» con 18 tiendas y 19 productos que la
 *     venden; 0 para «cámaras», «ollas», «camisas», «jugo de piña»). El número que da `buscar.php`
 *     cuenta TIENDAS CON ESA PALABRA EN EL NOMBRE, no productos. Si publicáramos el aviso tal cual,
 *     el sitio diría «nadie tiene cerveza» con 18 cervecerías dentro. Por eso, ANTES de publicar
 *     nada, `pedido_oferta_real()` mira 3 niveles (productos → rubro por frases de unión → tiendas)
 *     y **si hay oferta, el pedido NO nace**: se muestran los productos que sí existen.
 *  2) **NO SE PUBLICA BASURA.** De los términos sin resultado, muchos eran pruebas nuestras
 *     («xyzzy», «zzzznoexiste»), teléfonos («931103286») y erratas. `pedido_termino_util()` los
 *     tira, y el mismo pedido que se repite **no se duplica: suma** («🔥 7 personas lo buscan»),
 *     que es justo lo que hace atractivo un pedido para el vendedor.
 *  3) **EL WHATSAPP DEL COMPRADOR NO SE PUBLICA EN EL LISTADO.** El jefe lo quería ver en la lista
 *     («el usuario número tal está buscando…»): se cumple, pero el número se guarda y sale
 *     **enmascarado** (931 ••• 286); el enlace a `wa.me` lo fabrica el servidor **solo** para un
 *     vendedor que deja sus datos y toca «Ofrecer» (así se cuenta la propuesta y el número no queda
 *     tirado en el HTML para que lo cosechen los robots). El número de un VENDEDOR sí es público:
 *     lo publica él, en su propuesta, para que el comprador lo llame.
 *
 * ⚠️ Regla de oro del proyecto: nada de esto puede romper ni frenar el sitio. Todo va en try/catch
 *    y devuelve []/null/false si algo falla.
 * ⚠️ Fechas: SIEMPRE hora de Lima (las genera PHP). El MySQL del hosting va en UTC.
 */

// =====================================================================================
// 1) TABLAS (auto-instalación defensiva: los `migrar_*.php` los bloquea el antivirus)
// =====================================================================================

if (!function_exists('pedidos_tabla_lista')) {
    /** ¿Existe la tabla? (una comprobación por petición) */
    function pedidos_tabla_lista($tabla) {
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
}

if (!function_exists('pedidos_tablas_ok')) {
    function pedidos_tablas_ok() {
        return pedidos_tabla_lista('directorio_pedidos_busqueda');
    }
}

if (!function_exists('pedidos_instalar')) {
    /**
     * Crea las dos tablas del módulo. Idempotente (IF NOT EXISTS).
     * Solo lo hace un admin (o el instalador temporal `PEDIDOS_INSTALAR`, que es una sonda de un
     * solo uso que se borra del hosting al terminar), igual que empleos y banners.
     *
     * @return array{ok:bool,msg:string}
     */
    function pedidos_instalar() {
        $puede = (function_exists('es_admin') && es_admin()) || defined('PEDIDOS_INSTALAR');
        if (!$puede) return ['ok' => false, 'msg' => 'Solo el administrador puede crear las tablas de pedidos.'];
        // Si las tablas YA están, no se vuelve a crear nada: solo se aseguran las columnas nuevas.
        if (pedidos_tabla_lista('directorio_pedidos_busqueda')) {
            pedidos_columnas_asegurar();
            return ['ok' => true, 'msg' => '✔ Las tablas de pedidos ya estaban listas.'];
        }
        try {
            db()->exec("CREATE TABLE IF NOT EXISTS directorio_pedidos_busqueda (
                id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
                slug            VARCHAR(160) NOT NULL,
                termino         VARCHAR(120) NOT NULL,
                norm            VARCHAR(120) NOT NULL,
                -- ⚠️ DOS CLASES DE PEDIDO (los dos casos que pidió el jefe):
                --   'sin_vendedor' → 0 resultados y comprobado: NADIE lo vende. Es el hueco puro.
                --   'pocos'        → el buscador encontró 1-3: hay demanda y poquísima oferta.
                tipo            ENUM('sin_vendedor','pocos') NOT NULL DEFAULT 'sin_vendedor',
                -- Lo que el buscador le mostró al visitante (0 = nada, 1-3 = poquísimo). Se guarda
                -- porque es lo que hace honesto el pedido: «solo 2 tiendas lo tienen» es un dato.
                resultados      TINYINT UNSIGNED NULL,
                distrito_id     INT UNSIGNED NULL,
                rubro_id        INT UNSIGNED NULL,
                buscado_n       INT UNSIGNED NOT NULL DEFAULT 1,
                propuestas_n    INT UNSIGNED NOT NULL DEFAULT 0,
                whatsapp        VARCHAR(20)  NULL,        -- del COMPRADOR: nunca se pinta entero
                aviso_comprador VARCHAR(160) NULL,        -- «para mi moto», «talla 41»…
                estado          ENUM('abierto','conseguido','oculto') NOT NULL DEFAULT 'abierto',
                token           CHAR(32)     NOT NULL,
                ip_hash         CHAR(40)     NULL,
                dispositivo     VARCHAR(12)  NOT NULL DEFAULT '',
                primera_vez     DATETIME     NOT NULL,
                ultima_vez      DATETIME     NOT NULL,
                conseguido_en   DATETIME     NULL,
                origen          VARCHAR(12)  NOT NULL DEFAULT 'buscador',
                creado_en       DATETIME     NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY uq_pedido_slug (slug),
                KEY idx_norm (norm, estado),
                KEY idx_estado (estado, ultima_vez),
                KEY idx_token (token)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            db()->exec("CREATE TABLE IF NOT EXISTS directorio_pedidos_propuestas (
                id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
                pedido_id    INT UNSIGNED NOT NULL,
                negocio_id   INT UNSIGNED NULL,
                usuario_id   BIGINT UNSIGNED NULL,
                nombre       VARCHAR(80)  NOT NULL,
                whatsapp     VARCHAR(20)  NOT NULL,
                mensaje      VARCHAR(500) NULL,
                precio_txt   VARCHAR(60)  NULL,
                estado       ENUM('visible','oculto') NOT NULL DEFAULT 'visible',
                ip           VARCHAR(45)  NULL,
                creado_en    DATETIME     NOT NULL,
                PRIMARY KEY (id),
                KEY idx_pedido (pedido_id, estado)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

            return ['ok' => true, 'msg' => '✔ Tablas de pedidos sin vendedor listas.'];
        } catch (Throwable $e) {
            error_log('pedidos_instalar: ' . $e->getMessage());
            return ['ok' => false, 'msg' => 'Error al crear las tablas: ' . $e->getMessage()];
        }
    }
}

if (!function_exists('pedidos_columnas_asegurar')) {
    /**
     * Añade las columnas que falten en una instalación vieja (defensivo, solo admin o instalador).
     * La tabla nació el 2026-09-15 con `tipo` ya dentro; esto existe para que, si algún día se
     * amplía el módulo, no haya que correr un `migrar_*.php` (el antivirus del hosting los bloquea).
     */
    function pedidos_columnas_asegurar() {
        static $hecho = null;
        if ($hecho !== null) return $hecho;
        $hecho = false;
        if ((!function_exists('es_admin') || !es_admin()) && !defined('PEDIDOS_INSTALAR')) return $hecho;
        $cols = [
            'tipo'       => "ADD COLUMN tipo ENUM('sin_vendedor','pocos') NOT NULL DEFAULT 'sin_vendedor' AFTER norm",
            'resultados' => "ADD COLUMN resultados TINYINT UNSIGNED NULL AFTER tipo",
        ];
        try {
            $st = db()->prepare("SELECT column_name FROM information_schema.columns
                                  WHERE table_schema = DATABASE() AND table_name = 'directorio_pedidos_busqueda'");
            $st->execute();
            $tiene = [];
            foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $c) $tiene[] = strtolower((string)$c['column_name']);
            foreach ($cols as $col => $sql) {
                if (!in_array(strtolower($col), $tiene, true)) {
                    db()->exec('ALTER TABLE directorio_pedidos_busqueda ' . $sql);
                }
            }
            $hecho = true;
        } catch (Throwable $e) {
            error_log('pedidos_columnas_asegurar: ' . $e->getMessage());
        }
        return $hecho;
    }
}

// =====================================================================================
// 2) DEL TEXTO A UN PEDIDO PUBLICABLE (lo que se tira y lo que se guarda)
// =====================================================================================

if (!function_exists('pedido_norm')) {
    /** Palabra normalizada para AGRUPAR: minúsculas, sin tildes, sin signos, espacios simples. */
    function pedido_norm($texto) {
        $t = mb_strtolower(trim((string)$texto), 'UTF-8');
        $t = strtr($t, [
            'á'=>'a','é'=>'e','í'=>'i','ó'=>'o','ú'=>'u','ü'=>'u','à'=>'a','è'=>'e','ì'=>'i',
            'ò'=>'o','ù'=>'u','ñ'=>'n','ç'=>'c','ý'=>'y',
        ]);
        $t = preg_replace('/[^\p{L}\p{N}\s\+\-\.]/u', ' ', (string)$t);
        $t = preg_replace('/\s+/u', ' ', (string)$t);
        return trim(mb_substr((string)$t, 0, 120));
    }
}

if (!function_exists('pedido_palabras')) {
    /** Las palabras que cuentan (2+ letras). Se saltan las muletillas sueltas del castellano. */
    function pedido_palabras($termino) {
        $vacias = ['de' => 1, 'del' => 1, 'la' => 1, 'el' => 1, 'los' => 1, 'las' => 1, 'un' => 1,
                   'una' => 1, 'y' => 1, 'o' => 1, 'para' => 1, 'con' => 1, 'sin' => 1, 'en' => 1,
                   'mi' => 1, 'tu' => 1, 'que' => 1, 'por' => 1];
        $out = [];
        foreach (preg_split('/\s+/u', pedido_norm($termino), -1, PREG_SPLIT_NO_EMPTY) ?: [] as $p) {
            if (mb_strlen($p) < 2) continue;
            if (isset($vacias[$p]) && count(explode(' ', trim(pedido_norm($termino)))) > 1) continue;
            $out[] = $p;
        }
        return $out;
    }
}

if (!function_exists('pedido_termino_util')) {
    /**
     * ¿Este término merece un pedido público? Devuelve el término LIMPIO o '' si es basura.
     *
     * Se tira (medido el 2026-09-15 con lo que la gente escribió de verdad en el sitio):
     *   · pruebas nuestras y de la IA: xyzzy, zzzznoexiste, zzzsinresultados, test, prueba, asdf…
     *   · teléfonos («931103286», «931 103 286»): son datos personales, no un producto.
     *   · una sola palabra de 2 letras, o texto sin ninguna letra, o más de 60 caracteres.
     *   · el nombre EXACTO de una tienda activa: eso no es «sin vendedor», es que el buscador y el
     *     nombre no coinciden de milagro (y el nivel 3 de la oferta ya lo caza igual).
     */
    function pedido_termino_util($termino) {
        $t = trim(preg_replace('/\s+/u', ' ', (string)$termino));
        if ($t === '' || mb_strlen($t) > 60) return '';
        if (!preg_match('/\p{L}/u', $t)) return '';                 // sin letras: no es un producto
        $digitos = preg_replace('/\D+/', '', $t);
        if (strlen($digitos) >= 7) return '';                        // teléfono / DNI / código
        if (preg_match('/https?:|www\.|@|[<>{}]/i', $t)) return '';
        $norm = pedido_norm($t);
        if ($norm === '') return '';
        $palabras = pedido_palabras($t);
        if (!$palabras) return '';
        if (count($palabras) === 1 && mb_strlen($palabras[0]) < 4) return '';

        // Palabras de prueba / ruido (en cualquier posición: «test kerosene» tampoco es un pedido).
        $ruido = ['xyzzy','xyz','zzz','zzzz','zzzznoexiste','zzzsinresultados','sinresultados','sinresultado',
                  'noexiste','test','prueba','probando','asdf','qwerty','aaaa','hola','nada','cualquiera',
                  'lorem','ipsum','ejemplo','demo','sdfsdf','asdasd'];
        foreach ($palabras as $p) {
            if (in_array($p, $ruido, true)) return '';
            if (preg_match('/^(.)\1{3,}$/u', $p)) return '';          // «aaaa», «ssss»
        }

        // ¿Es el nombre de una tienda que ya existe? Entonces no falta vendedor: falta coincidencia.
        try {
            $st = db()->prepare("SELECT COUNT(*) FROM directorio_negocios WHERE estado = 'activo' AND nombre = ?");
            $st->execute([$t]);
            if ((int)$st->fetchColumn() > 0) return '';
        } catch (Throwable $e) { /* si falla, se sigue: no es motivo para perder el pedido */ }

        return $t;
    }
}

// =====================================================================================
// 3) ⚖️ LA COMPROBACIÓN QUE EVITA PUBLICAR MENTIRAS (los 3 niveles)
// =====================================================================================

if (!function_exists('pedido_oferta_productos')) {
    /**
     * Productos ACTIVOS que llevan esa palabra en el título (con su tienda).
     * Es el nivel que hoy le falta al buscador: «alcohol isopropílico» da 0 tiendas porque ninguna
     * se llama así, pero una botica YA lo tiene publicado como producto.
     */
    function pedido_oferta_productos($palabra, $limite = 4) {
        $palabra = trim((string)$palabra);
        if ($palabra === '') return [];
        try {
            $st = db()->prepare("SELECT s.id, s.titulo, s.precio, s.unidad,
                    n.id AS negocio_id, n.nombre AS tienda, n.slug, n.whatsapp, n.telefono
                FROM directorio_servicios s
                INNER JOIN directorio_negocios n ON n.id = s.negocio_id AND n.estado = 'activo'
                WHERE s.titulo LIKE ?
                ORDER BY s.precio > 0 DESC, s.id DESC LIMIT " . max(1, (int)$limite));
            $st->execute(['%' . $palabra . '%']);
            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('pedido_oferta_tiendas')) {
    /** Tiendas activas con esa palabra en el nombre o la descripción (SIN filtros de distrito). */
    function pedido_oferta_tiendas($palabra, $limite = 4) {
        $palabra = trim((string)$palabra);
        if ($palabra === '') return [];
        try {
            $st = db()->prepare("SELECT n.id, n.nombre, n.slug, n.whatsapp, n.telefono,
                    c.nombre AS rubro, c.icono AS rubro_icono
                FROM directorio_negocios n
                LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                WHERE n.estado = 'activo' AND (n.nombre LIKE ? OR n.descripcion LIKE ?)
                ORDER BY n.vistas_count DESC, n.rating DESC LIMIT " . max(1, (int)$limite));
            $st->execute(['%' . $palabra . '%', '%' . $palabra . '%']);
            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('pedido_nombre_de_tienda')) {
    /**
     * 🏪 ¿LO QUE SE ESCRIBIÓ ES EL NOMBRE DE UNA TIENDA ACTIVA? (2026-09-19 — caso «mayciel»).
     *
     * Es la comprobación que faltaba: cuando el término está en el NOMBRE de una tienda, el visitante
     * está buscando ESA tienda, no un producto que nadie vende. Sin ella, una ficha recién creada
     * generaba un aviso PÚBLICO («nadie lo vende» o «pocos lo venden») con la tienda ya publicada:
     * pasó con «mayciel» (3 tiendas en el aire) y con «payasito crespin» (1 tienda en el aire).
     * ⚠️ Se busca SOLO en el nombre (no en la descripción): «cerveza» está en la descripción de media
     *    bodega y ahí el pedido SÍ tiene sentido.
     *
     * @return array Tiendas (id, nombre, slug, whatsapp, rubro) o [] si ninguna se llama así.
     */
    function pedido_nombre_de_tienda($palabra, $limite = 4) {
        $palabra = trim((string)$palabra);
        if ($palabra === '' || mb_strlen($palabra) < 4) return [];
        try {
            $st = db()->prepare("SELECT n.id, n.nombre, n.slug, n.whatsapp, n.telefono,
                    c.nombre AS rubro, c.icono AS rubro_icono
                FROM directorio_negocios n
                LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                WHERE n.estado = 'activo' AND n.nombre LIKE ?
                ORDER BY n.vistas_count DESC, n.rating DESC LIMIT " . max(1, (int)$limite));
            $st->execute(['%' . $palabra . '%']);
            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }
}

if (!function_exists('pedido_oferta_real')) {
    /**
     * ¿DE VERDAD nadie ofrece esto? Los 3 niveles, en orden de fuerza:
     *   1. PRODUCTOS …… el título de un producto activo (lo más concreto: «alcohol isopropílico»).
     *   2. RUBRO ……… la palabra es una «frase de unión» de un rubro que TIENE tiendas («cerveza» →
     *                   Bodegas: aunque el texto no encuentre ninguna, hay 185 bodegas que la venden).
     *   3. TIENDAS …… nombre o descripción de una tienda activa.
     *
     * Con el término COMPLETO primero («zapatillas nike») y, si no hay nada, con sus palabras
     * (solo cuando el término tiene 1 o 2 palabras: con 3 o más el visitante fue específico —«Nike
     * talla 41»— y ahí SÍ queremos el pedido, aunque «zapatillas» exista).
     *
     * @return array{hay:bool,nivel:string,n:int,rubro:?array,productos:array,tiendas:array}
     */
    function pedido_oferta_real($termino) {
        $vacio = ['hay' => false, 'nivel' => '', 'n' => 0, 'rubro' => null, 'productos' => [], 'tiendas' => []];

        /** Prueba UNA palabra en los 3 niveles y devuelve el hallazgo (o null). */
        $mirar = function ($palabra) {
            $prod = pedido_oferta_productos($palabra, 50);
            if ($prod) {
                return ['hay' => true, 'nivel' => 'producto', 'n' => count($prod),
                        'rubro' => null, 'productos' => array_slice($prod, 0, 4), 'tiendas' => []];
            }
            if (function_exists('categoria_por_clave_texto')) {
                $rub = categoria_por_clave_texto($palabra);
                if ($rub) {
                    // El rubro se cuenta por sus tiendas: un rubro con 180 negocios es MUCHA oferta.
                    $n = 0;
                    try {
                        [$cf, $cp] = rubro_filtro_id((int)$rub['id'], 'n');
                        $st = db()->prepare("SELECT COUNT(*) FROM directorio_negocios n
                            WHERE n.estado = 'activo' AND $cf");
                        $st->execute($cp);
                        $n = (int)$st->fetchColumn();
                    } catch (Throwable $e) { $n = 1; }
                    return ['hay' => true, 'nivel' => 'rubro', 'n' => $n, 'rubro' => $rub,
                            'productos' => [], 'tiendas' => []];
                }
            }
            $tie = pedido_oferta_tiendas($palabra, 50);
            if ($tie) {
                return ['hay' => true, 'nivel' => 'tienda', 'n' => count($tie),
                        'rubro' => null, 'productos' => [], 'tiendas' => array_slice($tie, 0, 4)];
            }
            return null;
        };

        try {
            // a) El término COMPLETO, tal como lo escribió.
            $frase = trim((string)$termino);
            if (mb_strlen($frase) >= 4) {
                $hit = $mirar($frase);
                if ($hit) return $hit;
            }
            // b) Sus palabras, solo si el término es corto (1 o 2 palabras significativas).
            $palabras = pedido_palabras($frase);
            if ($palabras && count($palabras) <= 2) {
                foreach ($palabras as $p) {
                    if (mb_strlen($p) < 4) continue;
                    $hit = $mirar($p);
                    if ($hit) return $hit;
                }
            }
        } catch (Throwable $e) {
            error_log('pedido_oferta_real: ' . $e->getMessage());
        }
        return $vacio;
    }
}

// =====================================================================================
// 4) PUBLICAR / SUMAR UN PEDIDO
// =====================================================================================

if (!function_exists('pedido_slug_unico')) {
    function pedido_slug_unico($termino) {
        $base = pedido_norm($termino);
        $base = preg_replace('/[^a-z0-9]+/', '-', (string)$base);
        $base = trim((string)$base, '-');
        $base = mb_substr($base, 0, 60);
        if ($base === '') $base = 'pedido';
        $slug = $base;
        $n    = 1;
        while (true) {
            try {
                $st = db()->prepare('SELECT COUNT(*) FROM directorio_pedidos_busqueda WHERE slug = ?');
                $st->execute([$slug]);
                if (!(int)$st->fetchColumn()) return $slug;
            } catch (Throwable $e) {
                return $slug;
            }
            $n++;
            $slug = mb_substr($base, 0, 55) . '-' . $n;
        }
    }
}

if (!function_exists('pedido_token')) {
    function pedido_token() {
        try { return bin2hex(random_bytes(16)); } catch (Throwable $e) { return md5(uniqid('pedido', true)); }
    }
}

if (!function_exists('pedido_url')) {
    function pedido_url($slug = '') {
        $slug = trim((string)$slug);
        return url('en-vivo' . ($slug !== '' ? '?p=' . urlencode($slug) : ''));
    }
}

if (!function_exists('pedido_publicar')) {
    /**
     * Publica el pedido de un término… **solo si de verdad nadie lo vende**.
     *
     * @param string $termino  lo que la persona escribió o pidió
     * @param array  $opciones origen (buscador|pagina|chat) · distrito_id · whatsapp · aviso
     * @return array{ok:bool,motivo:string,pedido:?array,oferta:array,nuevo:bool}
     *   motivo: 'publicado' · 'sumado' · 'hay_oferta' · 'basura' · 'sin_tablas' · 'error'
     */
    function pedido_publicar($termino, array $opciones = []) {
        if (!pedidos_tablas_ok()) return ['ok' => false, 'motivo' => 'sin_tablas', 'pedido' => null, 'oferta' => [], 'nuevo' => false];

        // Un robot que recorre enlaces no publica pedidos en la lista (el buscador es GET y los
        // crawlers siguen esos enlaces: sin esto, un paseo de Googlebot llenaría la lista).
        if (function_exists('stats_es_bot') && stats_es_bot() && empty($opciones['ignorar_robot'])) {
            return ['ok' => false, 'motivo' => 'no_validado', 'pedido' => null, 'oferta' => [], 'nuevo' => false];
        }

        $limpio = pedido_termino_util($termino);
        if ($limpio === '') return ['ok' => false, 'motivo' => 'basura', 'pedido' => null, 'oferta' => [], 'nuevo' => false];

        // 📉 ¿CUÁNTOS RESULTADOS LE DIO EL BUSCADOR AL VISITANTE? (0 = nadie; 1-3 = poquísimos).
        $resultados = isset($opciones['resultados']) ? max(0, (int)$opciones['resultados']) : 0;

        // 🏪 SI SE LLAMA ASÍ UNA TIENDA, NO SE PUBLICA PEDIDO (2026-09-19 — caso «mayciel»).
        // El visitante está buscando esa tienda: se le enseñan sus tiendas y no se ensucia la lista
        // pública con un encargo de algo que SÍ existe (y menos con su propio nombre de marca).
        $con_ese_nombre = pedido_nombre_de_tienda($limpio, 4);
        if ($con_ese_nombre) {
            return ['ok' => true, 'motivo' => 'hay_oferta', 'pedido' => null, 'nuevo' => false,
                    'oferta' => ['hay' => true, 'nivel' => 'nombre', 'n' => count($con_ese_nombre),
                                 'rubro' => null, 'productos' => [], 'tiendas' => $con_ese_nombre]];
        }

        // ⚖️ La comprobación que evita el ridículo (ver la regla 1 de la cabecera). Se hace SIEMPRE:
        //    con 0 resultados decide si se publica; con 1-3 decide si la oferta es de verdad escasa
        //    o si el buscador simplemente está mostrando poco de lo mucho que hay.
        $oferta = pedido_oferta_real($limpio);
        if (!empty($oferta['hay'])) {
            $mucha = ((int)($oferta['n'] ?? 0) >= 4);   // 4+ productos, tiendas o negocios del rubro
            if ($resultados === 0 || $mucha) {
                return ['ok' => true, 'motivo' => 'hay_oferta', 'pedido' => null, 'oferta' => $oferta, 'nuevo' => false];
            }
        }
        // El tipo del pedido sale de lo que el visitante vio: 0 resultados = nadie; 1-3 = pocos.
        $tipo = ($resultados === 0) ? 'sin_vendedor' : 'pocos';

        $norm = pedido_norm($limpio);
        $wa   = preg_replace('/\D+/', '', (string)($opciones['whatsapp'] ?? ''));
        if (strlen($wa) === 9) $wa = '51' . $wa;
        if (strlen($wa) > 5 && (strlen($wa) < 11 || strlen($wa) > 13)) $wa = '';
        $aviso = mb_substr(trim((string)($opciones['aviso'] ?? '')), 0, 160);
        $hoy   = date('Y-m-d H:i:s');

        try {
            // ¿Ya hay un pedido ABIERTO de lo mismo? Entonces no se duplica: SE SUMA (regla 2).
            $st = db()->prepare("SELECT * FROM directorio_pedidos_busqueda
                WHERE norm = ? AND estado = 'abierto' ORDER BY id DESC LIMIT 1");
            $st->execute([$norm]);
            $p = $st->fetch(PDO::FETCH_ASSOC);

            if ($p) {
                $sets = ['buscado_n = buscado_n + 1', 'ultima_vez = ?'];
                $vals = [$hoy];
                // Si ahora llega con 0 resultados y el pedido era de «pocos», se agrava a «sin vendedor».
                if ($tipo === 'sin_vendedor' && (string)$p['tipo'] !== 'sin_vendedor') {
                    $sets[] = 'tipo = ?';
                    $vals[] = 'sin_vendedor';
                }
                if ($wa !== '')    { $sets[] = 'whatsapp = ?';        $vals[] = $wa; }
                if ($aviso !== '') { $sets[] = 'aviso_comprador = ?'; $vals[] = $aviso; }
                if (array_key_exists('resultados', $opciones)) { $sets[] = 'resultados = ?'; $vals[] = max(0, (int)$opciones['resultados']); }
                if (!empty($opciones['distrito_id'])) { $sets[] = 'distrito_id = ?'; $vals[] = (int)$opciones['distrito_id']; }
                $vals[] = (int)$p['id'];
                db()->prepare('UPDATE directorio_pedidos_busqueda SET ' . implode(', ', $sets) . ' WHERE id = ?')
                    ->execute($vals);
                $p = pedido_por_id((int)$p['id']);
                // Se avisa al jefe SOLO cuando el pedido se hace notar (3.ª vez): si no, un término
                // repetido llenaría el Telegram de lo mismo (y el aviso de la 1.ª vez ya lo mandó).
                if ((int)$p['buscado_n'] === 3) pedidos_aviso($p, 'crecido');
                return ['ok' => true, 'motivo' => 'sumado', 'pedido' => $p, 'oferta' => [], 'nuevo' => false];
            }

            // Pedido nuevo.
            $slug  = pedido_slug_unico($limpio);
            $token = pedido_token();
            $ip    = (string)(ip_real());
            db()->prepare("INSERT INTO directorio_pedidos_busqueda
                (slug, termino, norm, tipo, resultados, distrito_id, rubro_id, buscado_n, propuestas_n, whatsapp,
                 aviso_comprador, estado, token, ip_hash, dispositivo, primera_vez, ultima_vez, origen, creado_en)
                VALUES (?,?,?,?,?,?,?,1,0,?,?,'abierto',?,?,?,?,?,?,?)")
                ->execute([
                    $slug,
                    mb_substr($limpio, 0, 120),
                    $norm,
                    $tipo,
                    array_key_exists('resultados', $opciones) ? max(0, (int)$opciones['resultados']) : null,
                    !empty($opciones['distrito_id']) ? (int)$opciones['distrito_id'] : null,
                    !empty($opciones['rubro_id']) ? (int)$opciones['rubro_id'] : null,
                    ($wa !== '' ? $wa : null),
                    ($aviso !== '' ? $aviso : null),
                    $token,
                    ($ip !== '' ? sha1($ip . '|pedidos') : null),
                    function_exists('stats_dispositivo') ? stats_dispositivo() : '',
                    $hoy, $hoy,
                    mb_substr((string)($opciones['origen'] ?? 'buscador'), 0, 12),
                    $hoy,
                ]);
            $p = pedido_por_id((int)db()->lastInsertId());
            if ($p) {
                pedidos_aviso($p, 'nuevo');
                // 📲 Y a los VENDEDORES suscritos (los que siguen ese rubro y esa zona): el envío se
                // hace al terminar la respuesta, para que el visitante no espere por Telegram.
                pedidos_avisar_suscritos($p);
            }
            return ['ok' => true, 'motivo' => 'publicado', 'pedido' => $p, 'oferta' => [], 'nuevo' => true];
        } catch (Throwable $e) {
            error_log('pedido_publicar: ' . $e->getMessage());
            return ['ok' => false, 'motivo' => 'error', 'pedido' => null, 'oferta' => [], 'nuevo' => false];
        }
    }
}

// =====================================================================================
// 5) LEER
// =====================================================================================

if (!function_exists('pedido_por_id')) {
    function pedido_por_id($id) {
        if (!pedidos_tablas_ok()) return null;
        try {
            $st = db()->prepare("SELECT p.*, d.nombre AS distrito_nombre, d.slug AS distrito_slug,
                    c.nombre AS rubro_nombre, c.icono AS rubro_icono
                FROM directorio_pedidos_busqueda p
                LEFT JOIN directorio_distritos  d ON d.id = p.distrito_id
                LEFT JOIN directorio_categorias c ON c.id = p.rubro_id
                WHERE p.id = ? LIMIT 1");
            $st->execute([(int)$id]);
            return $st->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) { return null; }
    }
}

if (!function_exists('pedido_por_slug')) {
    function pedido_por_slug($slug) {
        if (!pedidos_tablas_ok()) return null;
        try {
            $st = db()->prepare("SELECT p.*, d.nombre AS distrito_nombre, d.slug AS distrito_slug,
                    c.nombre AS rubro_nombre, c.icono AS rubro_icono
                FROM directorio_pedidos_busqueda p
                LEFT JOIN directorio_distritos  d ON d.id = p.distrito_id
                LEFT JOIN directorio_categorias c ON c.id = p.rubro_id
                WHERE p.slug = ? LIMIT 1");
            $st->execute([(string)$slug]);
            return $st->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) { return null; }
    }
}

if (!function_exists('pedido_por_token')) {
    function pedido_por_token($token) {
        if (!pedidos_tablas_ok()) return null;
        try {
            $st = db()->prepare('SELECT * FROM directorio_pedidos_busqueda WHERE token = ? LIMIT 1');
            $st->execute([(string)$token]);
            return $st->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) { return null; }
    }
}

if (!function_exists('pedidos_listar')) {
    /**
     * Los pedidos de la lista. `$estado`: 'abierto' (lo que falta vender) · 'conseguido' (prueba
     * social: alguien ya lo consiguió) · 'todos'.
     */
    function pedidos_listar($estado = 'abierto', $limite = 30, $desde = 0) {
        if (!pedidos_tablas_ok()) return [];
        $limite = max(1, min(60, (int)$limite));
        $desde  = max(0, (int)$desde);
        $w = ($estado === 'todos') ? "p.estado <> 'oculto'" : "p.estado = ?";
        try {
            $sql = "SELECT p.*, d.nombre AS distrito_nombre, d.slug AS distrito_slug,
                    c.nombre AS rubro_nombre, c.icono AS rubro_icono
                FROM directorio_pedidos_busqueda p
                LEFT JOIN directorio_distritos  d ON d.id = p.distrito_id
                LEFT JOIN directorio_categorias c ON c.id = p.rubro_id
                WHERE $w
                ORDER BY p.ultima_vez DESC, p.id DESC
                LIMIT $desde, $limite";
            $st = db()->prepare($sql);
            $st->execute(($estado === 'todos') ? [] : [$estado]);
            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) { return []; }
    }
}

if (!function_exists('pedidos_contar')) {
    function pedidos_contar($estado = 'abierto') {
        if (!pedidos_tablas_ok()) return 0;
        try {
            $w = ($estado === 'todos') ? "estado <> 'oculto'" : "estado = ?";
            $st = db()->prepare("SELECT COUNT(*) FROM directorio_pedidos_busqueda WHERE $w");
            $st->execute(($estado === 'todos') ? [] : [$estado]);
            return (int)$st->fetchColumn();
        } catch (Throwable $e) { return 0; }
    }
}

if (!function_exists('pedidos_propuestas')) {
    /** Las propuestas visibles de un pedido (los vendedores que dicen tenerlo). */
    function pedidos_propuestas($pedido_id) {
        if (!pedidos_tablas_ok()) return [];
        try {
            $st = db()->prepare("SELECT pr.*, n.nombre AS tienda, n.slug AS tienda_slug, n.rating
                FROM directorio_pedidos_propuestas pr
                LEFT JOIN directorio_negocios n ON n.id = pr.negocio_id
                WHERE pr.pedido_id = ? AND pr.estado = 'visible'
                ORDER BY pr.id ASC LIMIT 20");
            $st->execute([(int)$pedido_id]);
            return $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) { return []; }
    }
}

// =====================================================================================
// 6) ESCRIBIR: EL COMPRADOR DEJA SU WHATSAPP · EL VENDEDOR OFRECE
// =====================================================================================

if (!function_exists('pedido_whatsapp_normal')) {
    /** Deja un WhatsApp peruano en formato 51XXXXXXXXX, o '' si no sirve. */
    function pedido_whatsapp_normal($numero) {
        $n = preg_replace('/\D+/', '', (string)$numero);
        if (strlen($n) === 9 && $n[0] === '9') return '51' . $n;
        if (strlen($n) === 11 && substr($n, 0, 2) === '51') return $n;
        if (strlen($n) === 12 && substr($n, 0, 2) === '51') return '51' . substr($n, 2);   // 51 + 0 + 9 díg.
        return '';
    }
}

if (!function_exists('pedido_whatsapp_visible')) {
    /**
     * El número del COMPRADOR, enmascarado: «931 ••• 286».
     * (El listado público nunca lo enseña entero: es un dato personal y los robots del sitio lo
     * cosecharían. El enlace completo lo fabrica el servidor para el vendedor que ofrece.)
     */
    function pedido_whatsapp_visible($numero) {
        $n = preg_replace('/\D+/', '', (string)$numero);
        if (strlen($n) === 11 && substr($n, 0, 2) === '51') $n = substr($n, 2);
        if (strlen($n) !== 9) return '';
        return substr($n, 0, 3) . ' ••• ' . substr($n, 6, 3);
    }
}

if (!function_exists('pedido_dejar_whatsapp')) {
    /**
     * El comprador deja su número (para que un vendedor lo contacte) y, si quiere, una nota.
     * Devuelve el pedido actualizado o null.
     */
    function pedido_dejar_whatsapp($slug, $numero, $aviso = '') {
        if (!pedidos_tablas_ok()) return null;
        $p = pedido_por_slug($slug);
        if (!$p || (string)$p['estado'] === 'oculto') return null;
        $wa = pedido_whatsapp_normal($numero);
        if ($wa === '') return null;
        try {
            db()->prepare("UPDATE directorio_pedidos_busqueda
                SET whatsapp = ?, aviso_comprador = ?, ultima_vez = ? WHERE id = ?")
                ->execute([$wa, mb_substr(trim((string)$aviso), 0, 160), date('Y-m-d H:i:s'), (int)$p['id']]);
            if (function_exists('aviso')) {
                aviso('pedido_sin_vendedor', [
                    'termino'    => (string)$p['termino'],
                    'motivo'     => 'comprador',
                    'buscado_n'  => (int)$p['buscado_n'] + 1,
                    'propuestas' => (int)$p['propuestas_n'],
                    'url'        => pedido_url((string)$p['slug']),
                    'clave'      => 'pedido_wa:' . (int)$p['id'],
                    'dedupe_min' => 1,
                    'resumen'    => $p['termino'] . ' (dejó su WhatsApp)',
                ]);
            }
            return pedido_por_id((int)$p['id']);
        } catch (Throwable $e) {
            error_log('pedido_dejar_whatsapp: ' . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('pedido_propuesta_crear')) {
    /**
     * Un vendedor dice «yo lo tengo». Guarda su propuesta (nombre + WhatsApp + mensaje) y devuelve
     * los datos: la página le mostrará el WhatsApp del comprador (si lo dejó) para que le escriba.
     *
     * @return array{ok:bool,motivo:string,id:int,comprador:string,oferta:?array}
     */
    function pedido_propuesta_crear($slug, array $datos) {
        $no = ['ok' => false, 'motivo' => 'error', 'id' => 0, 'comprador' => ''];
        if (!pedidos_tablas_ok()) return $no;
        $p = pedido_por_slug($slug);
        if (!$p || (string)$p['estado'] === 'oculto') return $no;

        $nombre = mb_substr(trim((string)($datos['nombre'] ?? '')), 0, 80);
        $wa     = pedido_whatsapp_normal($datos['whatsapp'] ?? '');
        if ($nombre === '' || $wa === '') return ['ok' => false, 'motivo' => 'faltan_datos', 'id' => 0, 'comprador' => ''];
        if (function_exists('stats_es_bot') && stats_es_bot() && empty($datos['ignorar_robot'])) {
            return ['ok' => false, 'motivo' => 'no_validado', 'id' => 0, 'comprador' => ''];
        }

        // Anti-spam: 5 propuestas por IP y hora (y 25 por pedido en total).
        $ip = (string)(ip_real());
        try {
            $st = db()->prepare("SELECT COUNT(*) FROM directorio_pedidos_propuestas
                WHERE ip = ? AND creado_en > ?");
            $st->execute([mb_substr($ip, 0, 45), date('Y-m-d H:i:s', time() - 3600)]);
            if ((int)$st->fetchColumn() >= 5) return ['ok' => false, 'motivo' => 'tope', 'id' => 0, 'comprador' => ''];
            if ((int)$p['propuestas_n'] >= 25) return ['ok' => false, 'motivo' => 'lleno', 'id' => 0, 'comprador' => ''];
        } catch (Throwable $e) { /* sin tope si la tabla falla: mejor aceptar que perder la propuesta */ }

        try {
            $mensaje = mb_substr(trim((string)($datos['mensaje'] ?? '')), 0, 500);
            $precio  = mb_substr(trim((string)($datos['precio'] ?? '')), 0, 60);
            $u       = function_exists('usuario_actual') ? usuario_actual() : null;
            db()->prepare("INSERT INTO directorio_pedidos_propuestas
                (pedido_id, negocio_id, usuario_id, nombre, whatsapp, mensaje, precio_txt, estado, ip, creado_en)
                VALUES (?,?,?,?,?,?,?,'visible',?,?)")
                ->execute([
                    (int)$p['id'],
                    !empty($datos['negocio_id']) ? (int)$datos['negocio_id'] : null,
                    !empty($u['id']) ? (int)$u['id'] : null,
                    $nombre, $wa, ($mensaje !== '' ? $mensaje : null), ($precio !== '' ? $precio : null),
                    mb_substr($ip, 0, 45), date('Y-m-d H:i:s'),
                ]);
            $id = (int)db()->lastInsertId();
            db()->prepare("UPDATE directorio_pedidos_busqueda
                SET propuestas_n = propuestas_n + 1, estado = 'conseguido',
                    conseguido_en = IFNULL(conseguido_en, ?)
                WHERE id = ?")->execute([date('Y-m-d H:i:s'), (int)$p['id']]);
            pedidos_aviso($p, 'propuesta', ['vendedor' => $nombre, 'whatsapp' => $wa, 'mensaje' => $mensaje]);
            return [
                'ok' => true, 'motivo' => 'ok', 'id' => $id,
                'comprador' => (string)($p['whatsapp'] ?? ''),
                'oferta'    => $p,
            ];
        } catch (Throwable $e) {
            error_log('pedido_propuesta_crear: ' . $e->getMessage());
            return $no;
        }
    }
}

if (!function_exists('pedido_marcar')) {
    /**
     * Moderación con un toque desde el Telegram (sin panel y sin login, como los empleos):
     * `ocultar` (borra de la lista: basura o spam) · `conseguir` (marca que ya se resolvió).
     * Devuelve el estado nuevo o '' si el token no sirve.
     */
    function pedido_marcar($token, $accion) {
        if (!pedidos_tablas_ok()) return '';
        if (!preg_match('/^[a-f0-9]{32}$/i', (string)$token)) return '';
        $p = pedido_por_token($token);
        if (!$p) return '';
        $mapa = ['ocultar' => 'oculto', 'conseguir' => 'conseguido', 'reabrir' => 'abierto'];
        if (!isset($mapa[$accion])) return '';
        $nuevo = $mapa[$accion];
        try {
            db()->prepare('UPDATE directorio_pedidos_busqueda SET estado = ?, conseguido_en = ? WHERE id = ?')
                ->execute([$nuevo, ($nuevo === 'conseguido' ? date('Y-m-d H:i:s') : null), (int)$p['id']]);
            return $nuevo;
        } catch (Throwable $e) {
            error_log('pedido_marcar: ' . $e->getMessage());
            return '';
        }
    }
}

// =====================================================================================
// 7) 🔔 EL AVISO AL JEFE (el de siempre: Telegram)
// =====================================================================================

if (!function_exists('pedidos_aviso')) {
    /**
     * $motivo: 'nuevo' (nació el pedido) · 'crecido' (ya lo buscaron 3) · 'propuesta' (un vendedor
     * ofreció) · 'comprador' (el comprador dejó su WhatsApp, lo manda el otro sitio).
     */
    function pedidos_aviso($p, $motivo = 'nuevo', array $extra = []) {
        if (!$p || !function_exists('aviso')) return;
        $slug = (string)($p['slug'] ?? '');
        aviso('pedido_sin_vendedor', [
            'termino'    => (string)($p['termino'] ?? ''),
            'motivo'     => (string)$motivo,
            'buscado_n'  => (int)($p['buscado_n'] ?? 1),
            'propuestas' => (int)($p['propuestas_n'] ?? 0),
            'distrito'   => (string)($p['distrito_nombre'] ?? ''),
            'vendedor'   => (string)($extra['vendedor'] ?? ''),
            'whatsapp'   => (string)($extra['whatsapp'] ?? ''),
            'mensaje'    => (string)($extra['mensaje'] ?? ''),
            'url'        => pedido_url($slug),
            'ocultar'    => url('en-vivo?moderar=' . (string)($p['token'] ?? '') . '&accion=ocultar'),
            'conseguir'  => url('en-vivo?moderar=' . (string)($p['token'] ?? '') . '&accion=conseguir'),
            'clave'      => 'pedido:' . (int)($p['id'] ?? 0) . ':' . $motivo,
            'dedupe_min' => 1,
            'resumen'    => ($p['termino'] ?? '') . ' (' . (int)($p['buscado_n'] ?? 1) . ' búsq. · ' . (int)($p['propuestas_n'] ?? 0) . ' oferta/s)',
        ]);
    }
}

// =====================================================================================
// 8) LA TARJETA PÚBLICA (la usan la página de la lista y los bloques de otras páginas)
// =====================================================================================

if (!function_exists('pedido_tiempo_txt')) {
    /** «hace 5 minutos», «hoy», «ayer», «hace 3 días» (para que el pedido se sienta vivo). */
    function pedido_tiempo_txt($fecha) {
        $t = strtotime((string)$fecha);
        if (!$t) return '';
        $min = (int)floor((time() - $t) / 60);
        if ($min < 2)   return 'ahora mismo';
        if ($min < 60)  return 'hace ' . $min . ' minutos';
        $h = (int)floor($min / 60);
        if ($h < 24 && date('Y-m-d', $t) === date('Y-m-d')) return 'hace ' . $h . ($h === 1 ? ' hora' : ' horas');
        $dias = (int)floor((strtotime(date('Y-m-d')) - strtotime(date('Y-m-d', $t))) / 86400);
        if ($dias <= 0) return 'hoy';
        if ($dias === 1) return 'ayer';
        if ($dias < 30) return 'hace ' . $dias . ' días';
        return 'el ' . date('d/m/Y', $t);
    }
}

if (!function_exists('pedido_es_mio')) {
    /**
     * ¿Este pedido lo publicó (o lo completó) quien está navegando ahora?
     * Se mira la IP hasheada al crearlo: sirve para que SOLO el comprador pueda cambiar su propio
     * WhatsApp (si no, cualquier vendedor podría poner su número y quedarse con el pedido).
     */
    function pedido_es_mio($p) {
        $hash = (string)($p['ip_hash'] ?? '');
        if ($hash === '') return false;
        $ip = (string)(ip_real());
        if ($ip === '') return false;
        return hash_equals($hash, sha1($ip . '|pedidos'));
    }
}

if (!function_exists('pedido_card_html')) {
    /**
     * Una tarjeta de la lista. Todo lo que se pinta sale de la base; los textos van con `e()`.
     * El botón «🛠️ Yo lo vendo» abre el constructor de tiendas con el nombre YA PUESTO (la idea del
     * jefe: que el vendedor no tenga que pensar qué publicar).
     */
    function pedido_card_html($p, array $opciones = []) {
        $slug    = (string)($p['slug'] ?? '');
        $termino = (string)($p['termino'] ?? '');
        $n       = (int)($p['buscado_n'] ?? 1);
        $props   = (int)($p['propuestas_n'] ?? 0);
        $wa      = pedido_whatsapp_visible((string)($p['whatsapp'] ?? ''));
        $aviso   = trim((string)($p['aviso_comprador'] ?? ''));
        $dist    = (string)($p['distrito_nombre'] ?? '');
        $rubro   = (string)($p['rubro_nombre'] ?? '');
        $icono   = (string)($p['rubro_icono'] ?? '');
        $estado  = (string)($p['estado'] ?? 'abierto');
        $tipo    = ((string)($p['tipo'] ?? 'sin_vendedor') === 'pocos') ? 'pocos' : 'sin_vendedor';
        $vistos  = ($p['resultados'] === null) ? null : (int)$p['resultados'];

        $crear = url('crear-tienda?modo=producto&q=' . rawurlencode($termino));

        $h  = '<article class="psv-card' . ($estado === 'conseguido' ? ' psv-card--ok' : '') . '" id="p' . (int)$p['id'] . '">';
        $h .= '<div class="psv-card__cab">';
        $h .= '<span class="psv-card__ico">' . ($estado === 'conseguido' ? '✅' : '🔎') . '</span>';
        $h .= '<div class="psv-card__quien">';
        if ($estado === 'conseguido') {
            $h .= '<span class="psv-card__estado">Ya lo consiguió</span>';
        } elseif ($props > 0) {
            $h .= '<span class="psv-card__estado">¡' . $props . ' vendedor' . ($props === 1 ? '' : 'es') . ' respondió!</span>';
        } elseif ($tipo === 'pocos') {
            // Honestidad: no decimos «nadie lo tiene» cuando el buscador enseñó 1-3 tiendas.
            $h .= '<span class="psv-card__estado psv-card__estado--falta">'
                . ($vistos !== null && $vistos > 0
                    ? 'Solo ' . $vistos . ' tienda' . ($vistos === 1 ? '' : 's') . ' lo tiene' . ($vistos === 1 ? '' : 'n')
                    : 'Muy pocos lo tienen')
                . '</span>';
        } else {
            $h .= '<span class="psv-card__estado psv-card__estado--falta">Nadie lo vende todavía</span>';
        }
        if ($n > 1) $h .= '<span class="psv-card__fuego">🔥 ' . $n . ' personas lo buscan</span>';
        $h .= '<span class="psv-card__cuando">' . e(pedido_tiempo_txt((string)($p['ultima_vez'] ?? $p['creado_en'] ?? ''))) . '</span>';
        $h .= '</div></div>';

        $h .= '<p class="psv-card__pide">Buscan <strong>' . e($termino) . '</strong></p>';

        $meta = [];
        if ($dist !== '')  $meta[] = '📍 ' . $dist;
        if ($rubro !== '') $meta[] = ($icono !== '' ? $icono . ' ' : '🏷️ ') . $rubro;
        if ($wa !== '')    $meta[] = '💬 El comprador dejó su WhatsApp (' . $wa . ')';
        if ($meta) $h .= '<p class="psv-card__meta">' . e(implode(' · ', $meta)) . '</p>';
        if ($aviso !== '') $h .= '<p class="psv-card__nota">📝 «' . e($aviso) . '»</p>';

        // Las ofertas que ya llegaron: el número del VENDEDOR sí es público (lo publica él).
        $lista = pedidos_propuestas((int)($p['id'] ?? 0));
        if ($lista) {
            $h .= '<div class="psv-ofertas">';
            foreach ($lista as $o) {
                $h .= '<div class="psv-oferta">';
                $h .= '<span class="psv-oferta__n">🏪 ' . e((string)$o['nombre'])
                    . (!empty($o['tienda']) ? ' <small>(' . e((string)$o['tienda']) . ')</small>' : '') . '</span>';
                $txt = trim((string)($o['precio_txt'] ?? '') . ' ' . (string)($o['mensaje'] ?? ''));
                if ($txt !== '') $h .= '<span class="psv-oferta__t">' . e(mb_substr($txt, 0, 160)) . '</span>';
                $h .= '<a class="psv-btn psv-btn--wa" target="_blank" rel="noopener nofollow"'
                    . ' href="' . e(url_whatsapp((string)$o['whatsapp'],
                        'Hola ' . (string)$o['nombre'] . ', vi en dechimbote.com que tienes «' . $termino
                        . '» (pedido de un cliente). ¿Me das el precio y si hay stock?')) . '">'
                    . wa_icono_svg() . ' Escribirle</a>';
                $h .= '</div>';
            }
            $h .= '</div>';
        }

        // Los botones del vendedor.
        $h .= '<div class="psv-card__acciones">';
        if ($estado !== 'conseguido' || $props > 0) {
            $h .= '<a class="psv-btn psv-btn--principal" href="' . e($crear) . '">🛠️ Yo lo vendo</a>';
            $h .= '<a class="psv-btn" href="#ofrecer-' . (int)$p['id'] . '">💬 Ofrecer mi precio</a>';
        }
        $h .= '</div>';

        // ⬇️ LAS DOS PUERTAS, SIN JAVASCRIPT (se abren con <details>).
        //   ⚠️ El formulario del VENDEDOR va SIEMPRE: deja su nombre y su WhatsApp (los suyos, que él
        //      publica a propósito). El del COMPRADOR solo si el pedido no tiene número todavía, o si
        //      quien mira es quien lo publicó (si no, cualquiera podría cambiarle el número).
        $puede_dejar = ($estado !== 'oculto') && ($wa === '' || pedido_es_mio($p));
        $tiene_wa    = ($wa !== '');

        if ($estado !== 'oculto') {
            if ($puede_dejar) {
                $h .= '<details class="psv-det" id="pedirwa-' . (int)$p['id'] . '">';
                $h .= '<summary>' . ($tiene_wa ? '📲 Cambiar mi WhatsApp' : '📲 Que me escriban: dejo mi WhatsApp') . '</summary>';
                $h .= '<form class="psv-form" method="post" action="' . e(url('en-vivo')) . '">';
                $h .= csrf_campo();
                $h .= '<input type="hidden" name="accion" value="whatsapp">';
                $h .= '<input type="hidden" name="pedido" value="' . e($slug) . '">';
                $h .= '<input type="tel" name="whatsapp" maxlength="15" inputmode="numeric" required placeholder="Tu WhatsApp: 9XX XXX XXX">';
                $h .= '<textarea name="nota" maxlength="160" placeholder="Un detalle más (opcional): talla, color, para qué es…"></textarea>';
                $h .= '<button type="submit" class="psv-btn psv-btn--ancho">Guardar mi WhatsApp</button>';
                $h .= '<p class="psv-privacidad">🔒 No se publica: solo lo ve el vendedor que te ofrece algo.</p>';
                $h .= '</form></details>';
            } elseif ($tiene_wa) {
                $h .= '<p class="psv-privacidad">🔒 El comprador dejó su WhatsApp (' . e($wa) . '): lo ve el vendedor que ofrece algo, no el público.</p>';
            }

            $h .= '<details class="psv-det" id="ofrecer-' . (int)$p['id'] . '">';
            $h .= '<summary>💬 Ofrecer mi precio</summary>';
            $h .= '<form class="psv-form" method="post" action="' . e(url('en-vivo')) . '">';
            $h .= csrf_campo();
            $h .= '<input type="hidden" name="accion" value="ofrecer">';
            $h .= '<input type="hidden" name="pedido" value="' . e($slug) . '">';
            $h .= '<input type="text" name="nombre" maxlength="80" required placeholder="Tu nombre o el de tu negocio">';
            $h .= '<input type="tel" name="whatsapp" maxlength="15" inputmode="numeric" required placeholder="Tu WhatsApp: 9XX XXX XXX">';
            $h .= '<input type="text" name="precio" maxlength="60" placeholder="Precio (opcional): S/ 25, a tratar…">';
            $h .= '<textarea name="mensaje" maxlength="500" placeholder="Marca, medida, si hay stock, dónde estás…"></textarea>';
            $h .= '<button type="submit" class="psv-btn psv-btn--principal psv-btn--ancho">Enviar mi oferta</button>';
            $h .= '<p class="psv-privacidad">' . ($tiene_wa
                    ? '📲 El comprador dejó su WhatsApp: al enviar, se abre su chat con tu mensaje ya escrito.'
                    : 'El comprador no dejó su número: tu oferta queda publicada en este pedido (y la ve cualquiera que busque lo mismo).')
                . '</p>';
            $h .= '</form></details>';
        }

        $h .= '</article>';
        return $h;
    }
}

// =====================================================================================
// 9) LOS DOS BLOQUES QUE PINTA EL BUSCADOR CUANDO NO ENCUENTRA NADA
// =====================================================================================

if (!function_exists('pedidos_avisar_suscritos')) {
    /**
     * 📲 Avisa a los vendedores suscritos a los encargos de su rubro y su zona.
     * El envío se hace en el `shutdown` (cuando la respuesta ya salió al visitante), igual que los
     * avisos del jefe: la página nunca se frena por Telegram. Si el módulo de suscripciones no está
     * (o su tabla no existe), no pasa absolutamente nada.
     */
    function pedidos_avisar_suscritos($pedido) {
        if (!$pedido) return;
        $archivo = __DIR__ . '/telegram_subs.php';
        if (!is_file($archivo)) return;
        require_once $archivo;
        if (!function_exists('tg_subs_avisar_encargo')) return;
        register_shutdown_function(function () use ($pedido) {
            try { tg_subs_avisar_encargo($pedido); }
            catch (Throwable $e) { error_log('pedidos_avisar_suscritos: ' . $e->getMessage()); }
        });
    }
}

if (!function_exists('pedido_estilos_aviso')) {
    /** Los estilos de los bloques del buscador, UNA sola vez por página (buscar.php los pide). */
    function pedido_estilos_aviso() {
        static $hecho = false;
        if ($hecho) return '';
        $hecho = true;
        return '<style>
.psv-aviso{background:#fff8ed;border:1.5px solid var(--marca-naranja,#e07a1f);border-radius:14px;
    padding:14px;margin:14px 0;text-align:left}
.psv-aviso--ok{background:#f0fdf4;border-color:#16a34a}
.psv-aviso__t{margin:0 0 5px;font-size:16.5px;font-weight:800;color:var(--marca-granate,#6d071a);line-height:1.3}
.psv-aviso__s{margin:0 0 11px;font-size:14.5px;line-height:1.55;color:#4a4a4a}
.psv-aviso__acc{display:flex;flex-wrap:wrap;gap:8px}
.psv-aviso__btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:44px;
    padding:10px 15px;border-radius:11px;background:var(--marca-naranja,#e07a1f);color:#fff;
    font-size:15px;font-weight:800;text-decoration:none;text-align:center}
.psv-aviso__btn--suave{background:#fff;color:var(--marca-granate,#6d071a);border:1.5px solid var(--color-borde,#e8ddd0)}
.psv-aviso__btn--wa{background:#25d366;color:#063a1d}
.psv-aviso__lista{display:flex;flex-direction:column;gap:8px;margin:0 0 11px}
.psv-aviso__item{display:flex;flex-wrap:wrap;gap:8px;align-items:center;justify-content:space-between;
    background:#fff;border:1px solid var(--color-borde,#e8ddd0);border-radius:11px;padding:10px 11px}
.psv-aviso__item b{font-size:14.5px;color:#222}
.psv-aviso__item small{display:block;font-size:12.5px;color:#777;font-weight:600}
.psv-aviso__mini{display:inline-flex;align-items:center;gap:5px;min-height:38px;padding:7px 12px;border-radius:9px;
    background:#25d366;color:#063a1d;font-size:13.5px;font-weight:800;text-decoration:none}
.psv-aviso__mini--gris{background:#fff;color:var(--marca-granate,#6d071a);border:1.5px solid var(--color-borde,#e8ddd0)}
</style>';
    }
}

if (!function_exists('pedido_publicado_html')) {
    /**
     * «📢 Publiqué tu pedido»: lo que ve el visitante cuando su búsqueda no encontró nada Y de verdad
     * nadie lo vende. Es la respuesta a su frustración: no se va con las manos vacías.
     */
    function pedido_publicado_html($p) {
        if (!$p) return '';
        $n     = (int)($p['buscado_n'] ?? 1);
        $slug  = (string)($p['slug'] ?? '');
        $id    = (int)($p['id'] ?? 0);
        $tipo  = ((string)($p['tipo'] ?? 'sin_vendedor') === 'pocos') ? 'pocos' : 'sin_vendedor';
        $vistos = ($p['resultados'] === null) ? null : (int)$p['resultados'];
        $h  = pedido_estilos_aviso();
        $h .= '<div class="psv-aviso">';
        $h .= '<p class="psv-aviso__t">📢 Publiqué tu encargo: «' . e((string)$p['termino']) . '»</p>';
        if ($tipo === 'pocos') {
            $h .= '<p class="psv-aviso__s">'
                . ($vistos !== null && $vistos > 0
                    ? '<strong>Solo ' . $vistos . ' tienda' . ($vistos === 1 ? '' : 's') . '</strong> de Chimbote '
                      . ($vistos === 1 ? 'tiene' : 'tienen') . ' lo que buscas.'
                    : 'Muy pocas tiendas de Chimbote tienen lo que buscas.')
                . ' Tu pedido ya quedó publicado para que los demás vendedores lo vean y te escriban'
                . ($n > 1 ? ' — <strong>🔥 ' . $n . ' personas lo buscan</strong>' : '') . '.</p>';
        } else {
            $h .= '<p class="psv-aviso__s">Lo comprobamos en todo el directorio: <strong>todavía nadie lo vende
                   en Chimbote</strong>. Tu pedido ya quedó publicado para que los vendedores lo vean'
                   . ($n > 1 ? ' — <strong>🔥 ' . $n . ' personas lo buscan</strong>' : '')
                   . '. Si alguno lo tiene, te escribe.</p>';
        }
        $h .= '<div class="psv-aviso__acc">';
        $h .= '<a class="psv-aviso__btn" href="' . e(pedido_url($slug) . '#pedirwa-' . $id) . '">📲 Dejo mi WhatsApp para que me escriban</a>';
        $h .= '<a class="psv-aviso__btn psv-aviso__btn--suave" href="' . e(url('en-vivo')) . '">Ver los encargos</a>';
        $h .= '</div></div>';
        return $h;
    }
}

if (!function_exists('pedido_oferta_html')) {
    /**
     * «Sí hay quién lo venda»: el buscador dijo 0 resultados, pero el sitio SÍ tiene el producto (en
     * el título de un producto), el rubro (frases de unión) o tiendas. Se le enseña eso al visitante
     * y NO se publica ningún pedido (nadie va a decir «no hay cerveza» con 18 cervecerías dentro).
     */
    function pedido_oferta_html(array $oferta, $termino = '') {
        if (empty($oferta['hay'])) return '';
        $termino = trim((string)$termino);
        $h  = pedido_estilos_aviso();
        $h .= '<div class="psv-aviso psv-aviso--ok">';
        $h .= '<p class="psv-aviso__t">😉 Sí hay: mira quién vende «' . e($termino) . '»</p>';

        $nivel = (string)($oferta['nivel'] ?? '');
        if ($nivel === 'nombre') {
            // 🏪 2026-09-19: lo escrito es el NOMBRE de una tienda activa (caso «mayciel»).
            $h .= '<p class="psv-aviso__s">Estas tiendas se llaman así — escríbeles directo por WhatsApp:</p>';
        } else {
            $h .= '<p class="psv-aviso__s">Ninguna tienda se llama así, pero el directorio sí tiene lo que
                   buscas' . ($nivel === 'producto' ? ' publicado como producto' : '') . ':</p>';
        }

        if ($nivel === 'producto' && !empty($oferta['productos'])) {
            $h .= '<div class="psv-aviso__lista">';
            foreach ($oferta['productos'] as $pr) {
                $h .= '<div class="psv-aviso__item"><span><b>' . e((string)$pr['titulo']) . '</b>'
                    . '<small>🏪 ' . e((string)$pr['tienda'])
                    . ((float)($pr['precio'] ?? 0) > 0 ? ' · ' . e(formato_precio((float)$pr['precio'])) : '') . '</small></span>';
                if (!empty($pr['whatsapp'])) {
                    $h .= '<a class="psv-aviso__mini" target="_blank" rel="noopener nofollow" href="'
                        . e(url_whatsapp((string)$pr['whatsapp'], 'Hola, vi en dechimbote.com que vendes «'
                            . (string)$pr['titulo'] . '». ¿Está disponible?')) . '">'
                        . wa_icono_svg() . ' Preguntar</a>';
                }
                $h .= '<a class="psv-aviso__mini psv-aviso__mini--gris" href="' . e(url_negocio((string)$pr['slug'])) . '">Ver la tienda</a>';
                $h .= '</div>';
            }
            $h .= '</div>';
        } elseif ($nivel === 'rubro' && !empty($oferta['rubro'])) {
            $h .= '<p class="psv-aviso__acc"><a class="psv-aviso__btn" href="'
                . e(url_categoria((string)$oferta['rubro']['slug'])) . '">🏷️ Ver '
                . e((string)$oferta['rubro']['nombre']) . '</a></p>';
        } elseif (!empty($oferta['tiendas'])) {
            $h .= '<div class="psv-aviso__lista">';
            foreach ($oferta['tiendas'] as $t) {
                $h .= '<div class="psv-aviso__item"><span><b>' . e((string)$t['nombre']) . '</b>'
                    . (!empty($t['rubro']) ? '<small>' . e((string)$t['rubro']) . '</small>' : '') . '</span>';
                if (!empty($t['whatsapp'])) {
                    $h .= '<a class="psv-aviso__mini" target="_blank" rel="noopener nofollow" href="'
                        . e(url_whatsapp((string)$t['whatsapp'], 'Hola, en dechimbote.com te encontré como «'
                            . (string)$t['nombre'] . '». ¿Tienes ' . $termino . '?')) . '">'
                        . wa_icono_svg() . ' Preguntar</a>';
                }
                $h .= '<a class="psv-aviso__mini psv-aviso__mini--gris" href="' . e(url_negocio((string)$t['slug'])) . '">Ver ficha</a>';
                $h .= '</div>';
            }
            $h .= '</div>';
        }
        $h .= '</div>';
        return $h;
    }
}
