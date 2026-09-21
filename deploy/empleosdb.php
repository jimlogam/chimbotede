<?php
/**
 * empleosdb.php — EMPLEOS Y ANUNCIOS  (URL pública: /empleos)
 * =======================================================================
 * Se sirve en `/empleos` por la regla `^empleos/?$` del .htaccess (el archivo se llama
 * `empleosdb.php` porque así lo pidió el jefe).
 *
 * QUÉ HACE ESTA PÁGINA (las 4 cosas, en el orden en que se ejecutan):
 *   1. MODERA Y RENUEVA con un toque: los enlaces con token que manda el Telegram
 *      (`?moderar=<token>&accion=aprobar|rechazar|pausar` y `?renovar=<token>`) resuelven
 *      todo aquí sin panel, sin login y sin que el jefe escriba nada.
 *   2. LISTA los avisos activos con filtros compartibles por URL, chips con número y
 *      paginación de 24.
 *   3. DEJA PUBLICAR al visitante (formulario público) — pero el aviso NACE PENDIENTE.
 *   4. RECUERDA lo que no se negocia: nadie paga por conseguir trabajo.
 *
 * POR QUÉ EXISTE (y no son fichas de tienda): un aviso de trabajo no tiene precio, no
 * tiene galería y CADUCA a los 30 días. Los avisos que hoy hay en el sitio están metidos
 * a la fuerza como negocios con el puesto cargado como producto a S/ 0.
 *
 * ⛔ SIN IMÁGENES (regla del jefe, 2026-09-12): el aviso de empleo es TEXTO. Aquí no hay
 *    ningún campo de archivo y nunca debe haberlo (tampoco una captura de pantalla).
 * ⛔ NADA DE SQL PROPIO: todo sale del bloque «EMPLEOS Y ANUNCIOS» de
 *    `includes/helpers.php` (tabla, vigencia, tarjetas, conteos, antispam, moderación).
 *    Y por eso mismo aquí NO se usa `NOW()` ni `CURDATE()`: la vigencia la calcula el
 *    helper con la fecha de LIMA (el MySQL del hosting va en UTC y vencería 5 horas antes).
 */

require_once __DIR__ . '/config.php';

// 🧠 El buscador predictivo de empleos se alimenta de `api/empleos_json.php`, que cachea su
//    JSON 1 hora. Al aprobar, pausar, rechazar o renovar un aviso hay que olvidar ESA caché
//    (y solo esa: la de negocios sigue viva) para que el aviso entre a las sugerencias ya.
//    El `is_file` es a propósito: la moderación del jefe es un solo clic y NO puede caerse
//    porque falte un archivo de caché (si falta, se sigue moderando y la caché caduca sola).
if (is_file(__DIR__ . '/includes/fuzzy_cache.php')) {
    require_once __DIR__ . '/includes/fuzzy_cache.php';
}

iniciar_sesion();

// La tabla nace por auto-instalación defensiva (spec §1) y se comprueba ANTES de leer nada:
// los `migrar_*.php` no sirven (el antivirus del hosting les devuelve 404), así que la crea
// el propio módulo la primera vez que un admin entra. Si todavía no existe, todas las
// funciones de empleos devuelven vacío y la página se ve igual (lista recién abierta).
empleos_instalar_tabla();

// ============================================================================
// A) MODERACIÓN Y RENOVACIÓN POR ENLACE (un clic desde Telegram / WhatsApp)
// ============================================================================
// El token de 32 caracteres ES la llave: quien tiene el enlace puede moderar, y el enlace
// solo lo recibe el jefe por Telegram. No hace falta cuenta ni panel — el jefe abre el
// mensaje, toca «✅ Aprobar» y el aviso queda publicado. Por eso estas dos ramas van ANTES
// de cualquier salida al navegador: terminan siempre en un `redirect()`.

/** ¿Tiene forma de token de moderación? (32 hex = lo que genera empleo_token()) */
function empleosdb_token_valido($t) {
    return (bool)preg_match('/^[a-f0-9]{32}$/i', (string)$t);
}

$token_mod = trim((string)($_GET['moderar'] ?? ''));
if ($token_mod !== '') {
    $accion = (string)($_GET['accion'] ?? '');
    // Los textos son los que verá el jefe al volver a la página: dicen QUÉ pasó con el aviso.
    $confirmaciones = [
        'aprobar'  => '✅ Aviso aprobado y publicado en la página de empleos. Estará 30 días.',
        'rechazar' => '🗑️ Aviso rechazado. No se publica en la página de empleos.',
        'pausar'   => '⏸️ Aviso pausado: deja de mostrarse en la página de empleos.',
    ];
    $estado_nuevo = empleosdb_token_valido($token_mod) ? empleo_marcar($token_mod, $accion) : '';
    if ($estado_nuevo === '') {
        flash('Ese enlace de moderación ya no sirve (el aviso pudo cambiar de estado). Entra a la página de empleos y revísalo.', 'error');
    } else {
        // Al cambiar el estado cambia lo que ve el buscador: se tira la caché de empleos.
        if (function_exists('fuzzy_olvidar_cache')) {
            fuzzy_olvidar_cache(['empleos.json', 'empleos.lock']);
        }
        $txt = $confirmaciones[$accion] ?? 'Aviso actualizado.';
        flash($txt, ($estado_nuevo === 'rechazado') ? 'warning' : 'exito');
    }
    redirect('empleos');
}

$token_ren = trim((string)($_GET['renovar'] ?? ''));
if ($token_ren !== '') {
    $ok = empleosdb_token_valido($token_ren) ? empleo_renovar($token_ren) : false;
    if (!$ok) {
        flash('No pudimos renovar ese aviso: el enlace ya no sirve o el aviso está rechazado.', 'error');
    } else {
        if (function_exists('fuzzy_olvidar_cache')) {
            fuzzy_olvidar_cache(['empleos.json', 'empleos.lock']);
        }
        // La fecha se calcula igual que el helper (fecha de Lima, +30 días): es lo que el
        // que publicó quiere leer — "sigue publicado hasta el 12/10/2026".
        flash('🔄 ¡Aviso renovado! Sigue publicado hasta el ' . date('d/m/Y', strtotime('+' . EMPLEO_DIAS . ' days')) . '.', 'exito');
    }
    redirect('empleos');
}

// ---------------------------------------------------------------------------
// Datos que necesita el resto de la página (una sola lectura por petición).
// ---------------------------------------------------------------------------
$oficios    = empleo_oficios();
$distritos  = obtener_distritos_visibles();   // los 9 visibles de la provincia del Santa
$categorias = obtener_categorias();           // rubros del directorio (para el filtro `cat`)
$tipos      = empleo_tipos();

$distritos_por_id = [];
foreach ($distritos as $d) $distritos_por_id[(int)$d['id']] = $d;

// 📋 LAS LISTAS DEL ASISTENTE (pedido del jefe, 2026-09-12): «el máximo posible que el usuario
//    solo dé clics». Todo lo que se puede elegir con el dedo sale de aquí, y sale del bloque
//    «EMPLEOS Y ANUNCIOS» de `helpers.php`: aquí no se inventa ni un nombre, ni un
//    rango de edad, ni un sueldo. Se leen UNA vez por petición y las usan DOS sitios: los chips
//    del asistente (más abajo) y la validación del POST (líneas de arriba, por eso van antes).
$jornadas_lista = empleo_jornadas();     // ⏰ las 5 jornadas exactas
$edades_lista   = empleo_edades();       // 🎂 5 rangos, con su min y su max
$niveles_lista  = empleo_niveles();      // 🎓 6 niveles formativos
$exper_lista    = empleo_experiencias(); // 💪 6 niveles de experiencia
$sueldos_lista  = empleo_sueldos();      // 💰 presets con el mínimo peruano S/ 1 250 y variantes

// 🔁 Cómo se lee cada periodo en la boca de una persona (lo usa el select de «otro monto»).
$periodos_txt = [
    'mensual'   => 'al mes',
    'quincenal' => 'por quincena',
    'semanal'   => 'por semana',
    'diario'    => 'por día',
    'hora'      => 'por hora',
];

// 🔎 LOS SINÓNIMOS DEL BUSCADOR DE OFICIOS (asistente, paso 2): la gente escribe lo que dice en
//    la calle («motorizado», «cajera», «gasfitero», «señora del aseo») y los 12 oficios del módulo
//    tienen nombre formal. Esto NO toca la lista ni lo que se guarda —el chip sigue siendo el
//    oficio oficial y el POST lo valida contra `empleo_oficios()`—: solo ENSANCHA lo que encuentra
//    el fuzzy mientras se escribe. Es texto que se busca, nunca texto que se publica.
$oficio_sinonimos = [
    'construccion'    => 'albañil, albanil, peón, gasfitero, pintor, maestro de obra, encofrador, fierrero',
    'cocina'          => 'cocinero, cocinera, ayudante de cocina, mozo, mesero, pollería, restaurante, chef, lavaplatos, cevichería',
    'atencion-ventas' => 'vendedor, vendedora, cajera, cajero, atención al cliente, tienda, mostrador, market, bodega',
    'transporte'      => 'motorizado, repartidor, chofer, conductor, delivery, cobrador, combi, colectivo',
    'limpieza'        => 'aseo, señora del aseo, lavandería, conserje, auxiliar de limpieza, hotel',
    'seguridad'       => 'vigilante, guardián, guardia, serenazgo, portería',
    'tecnicos'        => 'técnico, electricista, mecánico, soldador, carpintero, refrigeración, celulares, computadoras',
    'salud'           => 'enfermera, enfermero, técnico en enfermería, farmacia, botica, obstetriz, consultorio',
    'educacion'       => 'profesor, docente, auxiliar de educación, academia, inicial, primaria, secundaria',
    'administracion'  => 'administrador, secretaria, asistente, contabilidad, oficina, recepcionista, practicante',
    'campo'           => 'agricultor, peón agrícola, ganadería, cosecha, chacra, pesca, pescador',
    'otros'           => 'varios, cualquier trabajo, lo que sea',
];

// ============================================================================
// C) PUBLICAR UN AVISO GRATIS (formulario público) — nace PENDIENTE
// ============================================================================
// El jefe aprobó que publique el visitante, pero con revisión previa: una página de avisos
// sin moderación se llena de estafas el primer fin de semana. Todo lo que entra por aquí se
// guarda con estado 'pendiente' y modo 'visitante'; se publica cuando el jefe toca
// «✅ Aprobar» en el aviso de Telegram (el enlace trae el token, no hace falta panel).
$error_form = false;

// 💼 PUERTA B — EL DUEÑO DE UNA TIENDA (2026-09-12): si el que publica está logueado y manda el
// `negocio_id` de una tienda SUYA (el botón «💼 Ofrecer empleo» del panel), el aviso sale
// **ACTIVO** y colgado de su ficha, sin pasar por moderación: no tiene sentido que el jefe
// revise a un negocio que ya verificó y al que le dio el panel. El visitante anónimo sigue
// naciendo `pendiente` (puerta C), con su aviso al Telegram y los botones de aprobar/rechazar.
$negocio_mio  = null;
$negocio_post = max(0, (int)($_POST['negocio_id'] ?? $_GET['negocio'] ?? 0));
$usuario_prev = usuario_actual();
if ($negocio_post > 0 && !empty($usuario_prev['id'])) {
    try {
        // Solo su tienda y activa: el `dueno_id` se comprueba aquí, no se confía en el formulario.
        $stm = db()->prepare("SELECT id, nombre, slug, categoria_id, distrito_id, telefono, whatsapp
                                FROM directorio_negocios
                               WHERE id = ? AND dueno_id = ? AND estado = 'activo' LIMIT 1");
        $stm->execute([$negocio_post, (int)$usuario_prev['id']]);
        $negocio_mio = $stm->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (Throwable $e) {
        $negocio_mio = null;   // si algo falla, el aviso entra por la puerta C (con moderación)
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verificar();

    // Un POST se puede fabricar: si alguien manda `titulo[]=x` en vez de texto, el valor
    // llega como array y al pintarlo con e() reventaría la página. Se normaliza de una vez.
    foreach ($_POST as $k => $v) {
        if (!is_string($v)) $_POST[$k] = '';
    }

    $tipo_in       = (string)($_POST['tipo'] ?? '');
    $titulo        = trim((string)($_POST['titulo'] ?? ''));
    $entidad       = trim((string)($_POST['entidad'] ?? ''));
    $oficio_in     = (string)($_POST['oficio_slug'] ?? '');
    $distrito_in   = (int)($_POST['distrito_id'] ?? 0);
    $ciudad_in     = trim((string)($_POST['ciudad_txt'] ?? ''));
    $tel_in        = preg_replace('/\D+/', '', (string)($_POST['telefono'] ?? ''));
    $wa_in         = preg_replace('/\D+/', '', (string)($_POST['whatsapp'] ?? ''));
    $duracion_in   = trim((string)($_POST['duracion'] ?? ''));
    $requisitos_in = trim((string)($_POST['requisitos'] ?? ''));
    $desc_in       = trim((string)($_POST['descripcion'] ?? ''));
    $declaro       = !empty($_POST['declaro']);

    // ========================================================================
    // 📋 LO QUE EL ASISTENTE PREGUNTA CON CLICS (pedido del jefe, 2026-09-12)
    // ========================================================================
    // Todos estos datos llegan de un CHIP, así que un POST fabricado puede mandar cualquier
    // cosa: se validan SIEMPRE contra las listas del módulo (`empleo_jornadas()`,
    // `empleo_edades()`, `empleo_niveles()`, `empleo_experiencias()`, `empleo_sueldos()`).
    // Lo que no exista en la lista se IGNORA (se guarda vacío) en vez de reventar el INSERT:
    // un aviso con un dato raro es peor que un aviso sin ese dato.
    // Lo que NO se elige queda vacío a propósito y el aviso simplemente no muestra ese chip
    // (el jefe lo pidió así: nada de inventar edad, estudios ni experiencia).

    // ⏰ Jornada: el chip de las 5 manda. El texto de «otro horario» es para lo que las 5 no
    //    cubren («lunes a sábado 8am–5pm») y ese texto MANDA sobre el chip (es el detalle real).
    $jornada_in  = (string)($_POST['jornada'] ?? '');
    if (!isset($jornadas_lista[$jornada_in])) $jornada_in = '';
    $horario_in  = mb_substr(trim((string)($_POST['horario_txt'] ?? '')), 0, 80);

    // 🎂 Edad: el rango elegido se parte en sus DOS columnas (`edad_min` / `edad_max`).
    //    «Indistinto» viene con min y max en null → el aviso queda sin edad, que es lo correcto.
    $edad_min  = null;
    $edad_max  = null;
    $edad_slug = (string)($_POST['edad'] ?? '');
    if (isset($edades_lista[$edad_slug])) {
        $edad_min = $edades_lista[$edad_slug]['min'];
        $edad_max = $edades_lista[$edad_slug]['max'];
    }

    // 🎓 Nivel formativo y 💪 experiencia: tal cual el chip (incluido «indistinto», que el
    //    módulo sabe pintar como «no lo pide»: `crear_empleo()` y la tarjeta lo entienden).
    $nivel_in = (string)($_POST['nivel_formativo'] ?? '');
    if (!isset($niveles_lista[$nivel_in])) $nivel_in = '';

    $exper_in = (string)($_POST['experiencia'] ?? '');
    if (!isset($exper_lista[$exper_in])) $exper_in = '';

    // 💰 EL SUELDO, en orden de prioridad (regla del proyecto: NUNCA se inventa un sueldo):
    //    1) el TEXTO LIBRE manda siempre («S/ 30 el día + pasajes» no lo cubre ninguna lista);
    //    2) si no hay texto, el CHIP elegido de `empleo_sueldos()` (con el mínimo peruano
    //       S/ 1 250 y sus variantes por quincena, semana, día y hora);
    //    3) si no hay chip, el MONTO escrito a mano + su periodo.
    //    Se guarda `sueldo_periodo` + `sueldo_monto` (y `sueldo_txt` VACÍO) para que el sitio
    //    componga «S/ 1 250 al mes» con `empleo_sueldo_texto()`; con el texto libre se guarda
    //    `sueldo_txt` y el sitio lo respeta tal cual. Sin nada de esto → «A convenir».
    $sueldo_libre_in = trim((string)($_POST['sueldo_libre'] ?? ''));
    if ($sueldo_libre_in === '') {
        // El nombre viejo del campo (`sueldo_txt`): un formulario cacheado en el navegador de
        // alguien que estaba publicando justo cuando cambiamos el asistente sigue funcionando.
        $sueldo_libre_in = trim((string)($_POST['sueldo_txt'] ?? ''));
    }
    $sueldo_txt_in = '';   // lo que se guarda en la columna `sueldo_txt` (solo el texto libre)
    $sueldo_per_in = '';
    $sueldo_mon_in = 0.0;
    $preset_in     = (string)($_POST['sueldo_preset'] ?? '');
    if ($sueldo_libre_in !== '') {
        $sueldo_txt_in = mb_substr($sueldo_libre_in, 0, 80);
    } elseif (isset($sueldos_lista[$preset_in])) {
        $sueldo_per_in = (string)$sueldos_lista[$preset_in]['periodo'];
        $sueldo_mon_in = (float)($sueldos_lista[$preset_in]['monto'] ?? 0);
    } else {
        $per_post = (string)($_POST['sueldo_periodo'] ?? '');
        // El monto se limpia antes de convertirlo: si alguien escribe «1.250,50» (o el navegador
        // manda una coma), el punto de miles se quita y la coma pasa a ser el decimal.
        $mon_txt = preg_replace('/[^0-9.,]/', '', (string)($_POST['sueldo_monto'] ?? ''));
        if (strpos($mon_txt, ',') !== false) {
            $mon_txt = str_replace(',', '.', str_replace('.', '', $mon_txt));
        }
        $mon_post = (float)$mon_txt;
        if (isset($periodos_txt[$per_post]) && $mon_post > 0) {
            $sueldo_per_in = $per_post;
            $sueldo_mon_in = round(min(999999, $mon_post), 2);
        }
    }

    $errores = [];

    if (!isset($tipos[$tipo_in])) {
        $errores[] = 'Elige qué publicas: ofreces trabajo, buscas trabajo o es un anuncio.';
    }
    if (mb_strlen($titulo) < 6) {
        $errores[] = 'Escribe el puesto en el título (mínimo 6 letras). Ej. «Ayudante de cocina».';
    }
    if (mb_strlen($titulo) > 150) {
        $errores[] = 'El título es muy largo: resúmelo en 150 letras como máximo.';
    }
    if (!empleo_oficio_valido($oficio_in)) {
        $errores[] = 'Elige el oficio del aviso (construcción, cocina, ventas…).';
    }
    if (mb_strlen($desc_in) < 20) {
        $errores[] = 'Cuenta el trabajo con un poco más de detalle (mínimo 20 letras): qué se hace, horario y cómo postular.';
    }
    if (mb_strlen($desc_in) > 3000) {
        $errores[] = 'La descripción es muy larga (máximo 3000 letras).';
    }
    if (mb_strlen($requisitos_in) > 500) {
        $errores[] = 'Los requisitos son muy largos (máximo 500 letras).';
    }

    // Teléfono peruano: 9 dígitos que empiezan en 9 (celular). Vale teléfono O WhatsApp,
    // pero si el campo trae algo tiene que estar bien: un número mal escrito deja el aviso
    // inservible (nadie puede postular y el que publicó se queda esperando).
    $tel_ok = ($tel_in !== '' && preg_match('/^9\d{8}$/', $tel_in));
    $wa_ok  = ($wa_in  !== '' && preg_match('/^9\d{8}$/', $wa_in));
    if ($tel_in !== '' && !$tel_ok) $errores[] = 'El teléfono debe tener 9 dígitos y empezar en 9. Ej. 943111222.';
    if ($wa_in  !== '' && !$wa_ok)  $errores[] = 'El WhatsApp debe tener 9 dígitos y empezar en 9. Ej. 943111222.';
    if (!$tel_ok && !$wa_ok)        $errores[] = 'Deja un teléfono o un WhatsApp de 9 dígitos que empiece en 9: sin eso nadie puede postular a tu aviso.';

    // La casilla de la declaración es OBLIGATORIA (regla del jefe): el que publica firma
    // que no cobra al postulante. Es lo que frena al que quiere hacer negocio con la necesidad.
    if (!$declaro) {
        $errores[] = 'Marca la declaración: no cobras nada al postulante ni pides dinero por el trabajo.';
    }

    // El distrito llega del select, pero un POST se puede fabricar: si no está entre los
    // visibles se ignora (el aviso queda sin distrito y usa la ciudad escrita a mano).
    if ($distrito_in > 0 && !isset($distritos_por_id[$distrito_in])) $distrito_in = 0;

    // ---- Antispam (funciones del módulo; ninguna consulta propia) ----
    // Se corre SOLO si lo demás está bien: así el aviso de "ya publicaste hoy" no tapa los
    // errores que la persona tiene que corregir igual.
    $tel_aviso = $tel_ok ? $tel_in : $wa_in;   // el control se hace con el número que exista
    if (!$errores) {
        if (empleo_repetido($titulo, $tel_aviso, 7)) {
            $errores[] = 'Ya publicaste este mismo aviso hace poco. Si quieres corregirlo, escríbenos por WhatsApp en vez de publicarlo otra vez.';
        } elseif (!$negocio_mio && empleos_de_ip_hoy(empleo_ip_hash()) >= 1) {
            $errores[] = 'Ya publicaste un aviso hoy desde este aparato. Publicamos uno por día para que la lista sirva: vuelve mañana.';
        } elseif (!$negocio_mio && empleos_activos_de_telefono($tel_aviso) >= 2) {
            $errores[] = 'Ese teléfono ya tiene 2 avisos activos. Pausa o deja vencer uno antes de publicar otro.';
        }
    }

    if (!$errores) {
        // Con tienda propia (puerta B) el aviso sale ACTIVO y hereda de la tienda lo que el dueño
        // no volvió a escribir (entidad, rubro, distrito y teléfono): cero tecleo repetido.
        $nuevo = crear_empleo([
            'tipo'         => $tipo_in,
            'titulo'       => $titulo,
            'entidad'      => ($entidad !== '') ? $entidad : ($negocio_mio ? (string)$negocio_mio['nombre'] : 'Particular'),
            'oficio_slug'  => $oficio_in,
            'distrito_id'  => $distrito_in > 0 ? $distrito_in : (!empty($negocio_mio['distrito_id']) ? (int)$negocio_mio['distrito_id'] : null),
            'ciudad_txt'   => $ciudad_in,
            'telefono'     => ($tel_in !== '') ? $tel_in : (string)($negocio_mio['telefono'] ?? ''),
            'whatsapp'     => ($wa_in !== '')  ? $wa_in  : (string)($negocio_mio['whatsapp'] ?? ''),
            // 💰 Sueldo: el texto libre (si lo hay) o el monto con su periodo (chip o escrito a
            //    mano). `sueldo_txt` va VACÍO cuando hay periodo+monto: así el sitio compone
            //    «S/ 1 250 al mes» con `empleo_sueldo_texto()` y no se pisan los dos campos.
            'sueldo_txt'     => $sueldo_txt_in,
            'sueldo_periodo' => $sueldo_per_in,
            'sueldo_monto'   => $sueldo_mon_in,
            // ⏰ `jornada` guarda el nombre legible («Tiempo completo»): la ficha del aviso lo
            //    usa para el JSON-LD (`employmentType`: FULL_TIME / PART_TIME). `horario_txt`
            //    guarda el slug del chip —o el texto de «otro horario» si lo escribieron—, que
            //    es lo que pinta la tarjeta (`empleo_card_html()`).
            'jornada'      => ($jornada_in !== '') ? $jornadas_lista[$jornada_in]['nombre'] : $horario_in,
            'horario_txt'  => ($horario_in !== '') ? $horario_in : $jornada_in,
            // 📋 Los chips del asistente, ya validados contra las listas (o vacíos si no eligieron).
            'edad_min'        => $edad_min,
            'edad_max'        => $edad_max,
            'nivel_formativo' => $nivel_in,
            'experiencia'     => $exper_in,
            'duracion'     => $duracion_in,
            'requisitos'   => $requisitos_in,
            'descripcion'  => $desc_in,
            'negocio_id'   => $negocio_mio ? (int)$negocio_mio['id'] : null,
            'categoria_id' => $negocio_mio ? (int)$negocio_mio['categoria_id'] : null,
            'dueno_id'     => $negocio_mio ? (int)$usuario_prev['id'] : null,
            'estado'       => $negocio_mio ? 'activo' : 'pendiente',   // el dueño no pasa por moderación
            'modo'         => $negocio_mio ? 'dueno' : 'visitante',    // y el aviso al Telegram lo dice
            'ip_hash'      => $negocio_mio ? '' : empleo_ip_hash(),    // nunca se guarda la IP en claro
        ]);
        if (!$nuevo) {
            $errores[] = 'No pudimos guardar tu aviso en este momento. Intenta otra vez en unos minutos.';
        } else {
            // Con el visitante, `crear_empleo()` ya avisó al Telegram con los botones de moderación.
            flash($negocio_mio
                ? '¡Publicado! Tu aviso ya está visible en la página de empleos y en la ficha de ' . $negocio_mio['nombre'] . '.'
                : '¡Listo! Tu aviso queda en revisión: lo publicamos hoy mismo.');
            redirect('empleos');
        }
    }

    foreach ($errores as $err) flash($err, 'error');
    $error_form = true;
}

// ============================================================================
// B) FILTROS — todo vive en la URL, así el enlace se comparte tal cual
// ============================================================================
// Ojo: los filtros llegan de `$_GET` y se pintan en el HTML y en enlaces, así que se
// validan contra las listas del sitio ANTES de usarlos: lo que no existe se descarta
// (si no, un `?cat=loquesea` quedaría pegado en todos los enlaces de la página).

/** Los 8 filtros de empleos, todos vacíos. */
function empleosdb_filtros_vacios() {
    return ['tipo' => '', 'oficio' => '', 'zona' => '', 'ciudad' => '', 'cat' => '', 'q' => '', 'orden' => '', 'negocio_id' => 0];
}

$filtros = empleosdb_filtros_vacios();

$g_tipo = (string)($_GET['tipo'] ?? '');
if (isset($tipos[$g_tipo])) $filtros['tipo'] = $g_tipo;

$g_oficio = (string)($_GET['oficio'] ?? '');
if (empleo_oficio_valido($g_oficio)) $filtros['oficio'] = $g_oficio;

// La zona es el slug de un distrito (lo que reparten los chips), pero también se acepta el
// id numérico: `buscar_empleos()` entiende las dos formas.
$g_zona = trim((string)($_GET['zona'] ?? ''));
if ($g_zona !== '' && preg_match('/^[a-z0-9\-]{1,60}$/', $g_zona)) $filtros['zona'] = $g_zona;

// Ciudad escrita a mano (Casma, Huarmey…): esos no son distritos de la provincia del Santa.
$g_ciudad = mb_substr(trim((string)($_GET['ciudad'] ?? '')), 0, 80);
if ($g_ciudad !== '') $filtros['ciudad'] = $g_ciudad;

$g_cat = (string)($_GET['cat'] ?? '');
foreach ($categorias as $c) {
    if ((string)$c['slug'] === $g_cat && $g_cat !== '') { $filtros['cat'] = $g_cat; break; }
}

$filtros['q'] = mb_substr(trim((string)($_GET['q'] ?? '')), 0, 80);

$g_orden = (string)($_GET['orden'] ?? '');
if (in_array($g_orden, ['nuevo', 'vence'], true)) $filtros['orden'] = $g_orden;

// `?negocio=<id>`: los avisos de una sola tienda. Lo usa el bloque «este negocio busca
// personal» de la ficha (`empleos_negocio_bloque_html()`), así que esta página lo entiende.
$filtros['negocio_id'] = max(0, (int)($_GET['negocio'] ?? 0));

$pagina = max(1, (int)($_GET['p'] ?? 1));

// ¿Hay algún filtro puesto? Sirve para 3 cosas: el botón «Limpiar filtros», el canonical
// (una URL con filtros NO se declara canónica) y el texto del bloque vacío.
$hay_filtros = false;
foreach (['tipo', 'oficio', 'zona', 'ciudad', 'cat', 'q', 'orden', 'negocio_id'] as $k) {
    if (!empty($filtros[$k])) { $hay_filtros = true; break; }
}

// Una sola consulta para todo: filas de la página + total + nº de páginas.
$res     = buscar_empleos($filtros + ['pagina' => $pagina, 'limite' => EMPLEO_POR_PAGINA]);
$filas   = $res['filas'];
$total   = (int)$res['total'];
$paginas = (int)$res['paginas'];
$pagina  = (int)$res['pagina'];

// Contadores de los chips y del titular. `empleos_contar_activos()` es el número honesto
// de la página (avisos activos y sin vencer hoy, con la fecha de Lima).
$total_activos = empleos_contar_activos();
$por_oficio    = contar_empleos_por_oficio();
$por_zona      = contar_empleos_por_zona();

// ============================================================================
// UTILIDADES DE PINTADO (solo de esta página; no tocan los helpers del sitio)
// ============================================================================

/**
 * URL de la página de empleos con los filtros puestos, la ciudad/tienda y la página $p.
 * Se apoya en `empleo_url_filtros()` (el helper del contrato) y le añade lo que ese helper
 * no conoce —`ciudad`, `negocio` y `p`—: sin esto, pasar a la página 2 perdería la ciudad
 * y el visitante creería que la página "se olvidó" de su búsqueda.
 */
function empleosdb_url(array $f, $p = 0) {
    $url = empleo_url_filtros($f);
    $extra = [];
    if (!empty($f['ciudad']))     $extra['ciudad']  = (string)$f['ciudad'];
    if (!empty($f['negocio_id'])) $extra['negocio'] = (int)$f['negocio_id'];
    if ((int)$p > 1)              $extra['p']       = (int)$p;
    if (!$extra) return $url;
    return $url . (strpos($url, '?') === false ? '?' : '&') . http_build_query($extra);
}

/** El mismo estado de filtros pero con una clave cambiada (para los chips). */
function empleosdb_filtros_con(array $f, $clave, $valor) {
    $g = $f;
    $g[$clave] = $valor;
    return $g;
}

/**
 * Un chip de filtro. Es un ENLACE, no un checkbox: así el filtro se comparte por WhatsApp
 * tal cual, no hace falta JavaScript y el navegador puede guardar el enlace.
 * El chip puesto lleva `is-active` (la clase con la que el sitio marca lo encendido).
 */
function empleosdb_chip_html($etiqueta, $url, $activo, $title = '') {
    return '<a class="filtro-chip' . ($activo ? ' is-active' : '') . '" href="' . e($url) . '"'
        . ($activo ? ' aria-current="true"' : '')
        . ($title !== '' ? ' title="' . e($title) . '"' : '')
        . '><span>' . e($etiqueta) . '</span></a>';
}

/**
 * Un chip de RADIO del ASISTENTE de publicación: `label.pub-chip > input[type=radio] + span`.
 * Se diferencia del chip de los filtros (`empleosdb_chip_html`, que es un ENLACE) porque aquí
 * no se navega: se ELIGE un dato del aviso y el navegador lo manda con el formulario.
 *
 * POR QUÉ ASÍ Y NO UN <select>: el jefe lo pidió con estas palabras (2026-09-12): «el máximo
 * posible que el usuario solo dé clics». Un chip se toca con el dedo, se ve de un vistazo y no
 * abre ninguna lista. Además, SIN JavaScript el chip sigue funcionando igual (es un radio de
 * toda la vida), así que el asistente es una mejora, nunca un requisito.
 *
 * @param string $name    nombre del radio (`tipo`, `jornada`, `edad`…)
 * @param mixed  $valor   valor que viaja en el POST
 * @param string $texto   lo que se ve (con su emoji)
 * @param string $nota    explicación corta debajo, en letra pequeña
 * @param bool   $marcado ¿viene marcado? (se recalcula con $_POST al volver de un error)
 * @param array  $datos   atributos `data-*` extra (JS los lee: `busca`, `resumen`, `periodo`…)
 */
function empleosdb_chip_pub($name, $valor, $texto, $nota = '', $marcado = false, array $datos = []) {
    $h = '<label class="pub-chip"';
    foreach ($datos as $k => $v) {
        if ($v === '' || $v === null) continue;   // sin dato, sin atributo (el JS lo comprueba)
        $h .= ' data-' . e((string)$k) . '="' . e((string)$v) . '"';
    }
    $h .= '><input type="radio" name="' . e((string)$name) . '" value="' . e((string)$valor) . '"'
        . ($marcado ? ' checked' : '') . '><span>' . e($texto)
        . ($nota !== '' ? '<small>' . e($nota) . '</small>' : '')
        . '</span></label>';
    return $h;
}

/**
 * Paginador: una fila de botones grandes (44 px, se tocan con el dedo) + «Página X de Y»
 * en texto. Todos los enlaces conservan los filtros puestos.
 */
function empleosdb_paginador_html(array $f, $pagina, $paginas) {
    if ($paginas < 2) return '';

    $boton = function ($n, $texto = null) use ($f, $pagina) {
        $txt = ($texto === null) ? (string)$n : $texto;
        if ((int)$n === (int)$pagina) {
            return '<span class="paginador__btn paginador__btn--activo" aria-current="page">' . e($txt) . '</span>';
        }
        return '<a class="paginador__btn" href="' . e(empleosdb_url($f, $n)) . '">' . e($txt) . '</a>';
    };

    $h  = '<div class="paginador" role="navigation" aria-label="Páginas de avisos">';
    $h .= ($pagina > 1)
        ? '<a class="paginador__btn" rel="prev" href="' . e(empleosdb_url($f, $pagina - 1)) . '">‹ Anterior</a>'
        : '<span class="paginador__btn paginador__btn--off">‹ Anterior</span>';

    // La página actual con dos vecinas a cada lado; si hay muchas, puntos suspensivos.
    $ini = max(1, $pagina - 2);
    $fin = min($paginas, $pagina + 2);
    if ($ini > 1) {
        $h .= $boton(1);
        if ($ini > 2) $h .= '<span class="paginador__puntos">…</span>';
    }
    for ($i = $ini; $i <= $fin; $i++) $h .= $boton($i);
    if ($fin < $paginas) {
        if ($fin < $paginas - 1) $h .= '<span class="paginador__puntos">…</span>';
        $h .= $boton($paginas);
    }

    $h .= ($pagina < $paginas)
        ? '<a class="paginador__btn" rel="next" href="' . e(empleosdb_url($f, $pagina + 1)) . '">Siguiente ›</a>'
        : '<span class="paginador__btn paginador__btn--off">Siguiente ›</span>';

    $h .= '<div class="paginador__info">Página ' . (int)$pagina . ' de ' . (int)$paginas . '</div>';
    $h .= '</div>';
    return $h;
}

// ============================================================================
// E) SEO — el header del sitio pinta $titulo_pagina, $descripcion_pagina y $canonical_url
// ============================================================================
$titulo_pagina      = 'Empleos y anuncios en Chimbote y la provincia del Santa';
$descripcion_pagina = 'Empleos gratis en Chimbote: quién ofrece trabajo, quién busca trabajo y anuncios de la provincia del Santa. Publica tu aviso gratis y dura 30 días.';

// El título acompaña al filtro (le sirve al que busca y a Google), sin inventar nada.
if ($filtros['oficio'] !== '') {
    $titulo_pagina = $oficios[$filtros['oficio']]['nombre'] . ': empleos en Chimbote';
} elseif ($filtros['q'] !== '') {
    $titulo_pagina = 'Empleos de «' . $filtros['q'] . '» en Chimbote';
}

// 🔗 Canonical: solo la URL limpia de la página de empleos. Las páginas con filtros NO se declaran
// canónicas porque el contenido es distinto y no queremos pisar unas con otras.
if (!$hay_filtros) $canonical_url = url('empleos');

$h1_txt = '💼 Empleos y anuncios en Chimbote';
if ($filtros['oficio'] !== '') {
    $h1_txt = $oficios[$filtros['oficio']]['icono'] . ' ' . $oficios[$filtros['oficio']]['nombre'] . ' en Chimbote';
} elseif ($filtros['tipo'] !== '') {
    $h1_txt = $tipos[$filtros['tipo']]['icono'] . ' ' . $tipos[$filtros['tipo']]['etiqueta'] . ' en Chimbote';
}

$usuario = usuario_actual();

include __DIR__ . '/includes/header.php';
?>

<style>
/* ==========================================================================
   ESTILOS PROPIOS DE LA PÁGINA DE EMPLEOS (solo de esta página)
   - Los chips: la receta de `.filtro-chip` vive DENTRO de buscar.php (líneas 395-408 del
     archivo) y no se carga en esta página, así que aquí trae su propia versión en
     forma de ENLACE (`a.filtro-chip`, no `label > input`). components.css solo pone la
     altura táctil de 44 px, que aquí se respeta.
   - Los campos van a 16 px: `.form__input` del sitio mide 15 px y en iPhone eso hace que
     la pantalla se haga zoom sola al enfocar un campo (Regla de Oro: móvil primero).
   ========================================================================== */
.filtro-chip{position:relative;display:inline-flex;text-decoration:none;-webkit-tap-highlight-color:transparent}
.filtro-chip > span{position:relative;display:inline-flex;align-items:center;min-height:44px;padding:8px 13px;box-sizing:border-box;
    border:1.5px solid rgba(109,7,26,.25);background:#fff;border-radius:999px;
    font-size:14px;font-weight:700;color:var(--color-primario);white-space:nowrap}
/* Encendido: fondo granate y la palomita en la esquina (absoluta, para que el chip NO cambie de ancho). */
.filtro-chip.is-active > span{background:var(--color-primario);border-color:var(--color-primario);color:#fff;box-shadow:0 2px 6px rgba(109,7,26,.28)}
.filtro-chip.is-active > span::after{content:"✓";position:absolute;top:-7px;right:-3px;width:17px;height:17px;
    border-radius:50%;background:var(--marca-naranja);color:#fff;font-size:11px;font-weight:800;
    line-height:17px;text-align:center;box-shadow:0 1px 3px rgba(0,0,0,.3)}

/* Caja del aviso de seguridad: la misma receta de `.flash--warning` del sitio, con aire propio. */
.empleos-aviso{margin:0 0 14px;font-size:15px;line-height:1.5}
.empleos-aviso b{font-weight:800}

/* La barra de filtros: en celular cada control ocupa su fila (es lo que hace components.css);
   desde 900 px sobra sitio y el buscador crece mientras los selects se quedan estrechos.
   (El selector lleva las clases del padre a propósito: así gana al `flex:1 1 100%` de components.css.) */
@media (min-width:900px){
    .empleos-toolbar .empleos-toolbar__fila > input.empleos-toolbar__q{flex:2 1 300px;min-width:240px}
    .empleos-toolbar .empleos-toolbar__fila > input.empleos-toolbar__c{flex:1 1 170px;min-width:150px}
    .empleos-toolbar .empleos-toolbar__fila > select.empleos-toolbar__sel{flex:0 1 200px}
}

/* Filas de la barra: rótulo + chips. Va en dos líneas cuando no cabe (celular). */
.empleos-toolbar__bloque{margin-top:10px}
.empleos-toolbar__bloque:first-of-type{margin-top:12px}
.empleos-toolbar__chips .filtro-chip > span{font-size:13.5px}
.empleos-toolbar__acciones{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin-top:12px}
.empleos-toolbar__limpiar{font-size:14px;font-weight:700;color:var(--color-primario);text-decoration:underline}

/* ⚠️ Aquí vivían las reglas del formulario viejo («.empleo-radios» y los chips con `input`
   dentro de `.filtro-chip`): el asistente ya no las usa. Ahora cada opción es un `.pub-chip`
   (ver el bloque del ASISTENTE DE PUBLICACIÓN, más abajo), que trae su propio fondo y borde.
   Los chips de los FILTROS siguen siendo enlaces `.filtro-chip` (reglas de arriba). */

/* El formulario: campos a 16 px y la casilla de la declaración bien visible. */
.empleo-form .form__input,
.empleo-form .form__select,
.empleo-form .form__textarea{font-size:16px}
.empleo-form .form__textarea{min-height:96px}
.empleo-check{grid-column:1/-1;display:flex;align-items:flex-start;gap:10px;padding:12px 13px;
    border:1.5px solid var(--marca-naranja);border-radius:12px;background:#fffbeb;font-size:15px;line-height:1.45;font-weight:600;color:#b45309}
.empleo-check input{width:22px;height:22px;flex:0 0 auto;margin-top:1px;accent-color:var(--color-primario)}

/* Cajas de gancho (empresa / pie) y el título de la sección del formulario. */
.empleos-caja{background:var(--color-fondo-tarjeta);border:1px solid var(--color-borde);border-radius:14px;
    box-shadow:var(--sombra-tarjeta);padding:16px;margin:16px 0;text-align:center}
.empleos-caja__t{margin:0 0 6px;font-size:17px;font-weight:800;color:var(--marca-granate)}
.empleos-caja__s{margin:0 0 12px;font-size:14.5px;line-height:1.5;color:var(--color-texto-claro)}
/* ==========================================================================
   ✍️ EL ASISTENTE DE PUBLICACIÓN (pedido del jefe, 2026-09-12)
   Sus palabras: «el diseño de "publica un aviso" está muy plano, necesita sus fondos y
   bordes, además de ser inteligente para pedir datos como un chatbot, el máximo posible
   que el usuario solo dé clics».
   → Por eso cada paso es una TARJETA con su fondo, su borde, su radio y su sombra (los
     tokens del sitio, igual que `.card-empleo`), el encabezado lleva barra de progreso y
     todo lo que se puede elegir es un CHIP de 44 px. Móvil primero: fuente ≥16 px.
   ⛔ NADA SE ESCONDE SIN JAVASCRIPT: los pasos se apagan SOLO cuando el contenedor lleva
     `.pub--vivo` (lo pone el JS al arrancar). Sin JS se ven los 8 pasos y el formulario se
     envía igual; el asistente es una mejora, nunca un requisito (requisito del jefe).
   ========================================================================== */
.pub{margin-top:22px;scroll-margin-top:96px}
.pub [hidden]{display:none!important}
/* ⚠️ `.empleo-form` del sitio es una REJILLA de 2 columnas desde 900 px (components.css:1586,
   hecha para el formulario plano viejo, que se pintaba en dos columnas). El asistente va a UNA
   columna: aquí se apaga esa rejilla SOLO dentro del asistente, sin tocar components.css (que
   otros formularios del sitio sí usan). Sin esto, en PC el asistente sale a media pantalla. */
.pub .empleo-form{display:block}

/* --- Encabezado del asistente: fondo con degradado, borde y barra de progreso --- */
.pub-cab{background:linear-gradient(140deg,var(--marca-granate,#6d071a) 0%,#8c1026 55%,#b34700 165%);
    color:#fff;border:1px solid rgba(255,255,255,.22);border-radius:16px;box-shadow:var(--sombra-tarjeta);padding:16px}
.pub-cab__t{margin:0 0 5px;font-size:20px;font-weight:800;color:#fff}
.pub-cab__s{margin:0 0 12px;font-size:15px;line-height:1.5;color:rgba(255,255,255,.93)}
.pub-cab__s b{color:#fff}
.pub-cab__yo{margin:0 0 12px;padding:10px 12px;border-radius:12px;background:rgba(255,255,255,.16);
    border:1px solid rgba(255,255,255,.4);font-size:14.5px;line-height:1.45;font-weight:600}
.pub-prog{height:10px;border-radius:999px;background:rgba(0,0,0,.3);border:1px solid rgba(255,255,255,.3);overflow:hidden}
.pub-prog__barra{display:block;height:100%;width:12.5%;border-radius:999px;
    background:linear-gradient(90deg,#fbbf24,#f97316);transition:width .25s}
.pub-prog__txt{margin:7px 0 0;font-size:13.5px;font-weight:800;letter-spacing:.01em}
/* 🧠 RESUMEN VIVO: la caja blanca donde va creciendo el aviso mientras lo arman. */
.pub-resumen{margin:10px 0 0;padding:10px 12px;border-radius:12px;background:#fffdf7;
    border:1.5px solid rgba(255,255,255,.75);color:var(--color-texto);font-size:15px;font-weight:700;
    line-height:1.45;min-height:44px;display:flex;align-items:center}
.pub-resumen__vacio{font-weight:600;color:var(--color-texto-claro)}

/* --- Los pasos: cada uno es una tarjeta --- */
.pub-pasos{display:grid;gap:14px;margin-top:14px}
.pub-paso{background:var(--color-fondo-tarjeta);border:1px solid var(--color-borde);
    border-top:4px solid var(--color-primario);border-radius:16px;box-shadow:var(--sombra-tarjeta);
    padding:14px;scroll-margin-top:96px}
.pub--vivo .pub-paso{display:none}
.pub--vivo .pub-paso.is-actual{display:block}
.pub-paso__cab{display:flex;align-items:center;gap:9px;margin-bottom:6px}
.pub-paso__num{flex:0 0 auto;width:30px;height:30px;border-radius:50%;background:var(--color-primario);
    color:#fff;font-size:15px;font-weight:800;line-height:30px;text-align:center;box-shadow:0 2px 6px rgba(109,7,26,.3)}
.pub-paso__t{margin:0;font-size:17.5px;font-weight:800;color:var(--color-texto)}
.pub-paso__ayuda{margin:0 0 10px;font-size:14.5px;line-height:1.5;color:var(--color-texto-claro)}
.pub-paso__ayuda b{color:var(--color-texto)}

/* --- Chips: se tocan con el dedo (44 px), con fondo blanco y borde --- */
.pub-chips{display:flex;flex-wrap:wrap;gap:8px}
.pub-chip{position:relative;display:inline-flex;cursor:pointer;-webkit-tap-highlight-color:transparent}
.pub-chip input{position:absolute;opacity:0;pointer-events:none}
.pub-chip > span{display:inline-flex;flex-direction:column;justify-content:center;gap:1px;min-height:44px;
    box-sizing:border-box;padding:8px 14px;border:1.5px solid var(--color-borde);border-radius:12px;
    background:#fff;box-shadow:0 1px 3px rgba(0,0,0,.07);font-size:16px;font-weight:700;color:var(--color-texto)}
.pub-chip > span small{font-size:12.5px;font-weight:600;color:var(--color-texto-claro)}
.pub-chip input:checked + span{background:var(--color-primario);border-color:var(--color-primario);color:#fff;
    box-shadow:0 3px 9px rgba(109,7,26,.3)}
.pub-chip input:checked + span small{color:rgba(255,255,255,.88)}
.pub-chip input:focus-visible + span{outline:2px solid var(--marca-naranja);outline-offset:2px}
.pub-chip.is-oculto{display:none}
/* El primer clic del asistente (los 3 tipos) son botones GRANDES, uno por fila en celular. */
.pub-chips--tipo .pub-chip{flex:1 1 100%}
.pub-chips--tipo .pub-chip > span{align-items:flex-start;padding:12px 14px;font-size:17px}
@media(min-width:640px){.pub-chips--tipo .pub-chip{flex:1 1 30%}}

/* --- Campos escritos (los mínimos) y cajas de sugerencia del fuzzy --- */
.pub-campo{margin-top:12px}
.pub-campo__lbl{display:block;font-size:15px;font-weight:800;color:var(--color-texto);margin-bottom:5px}
.pub-campo__ayuda{margin:5px 0 0;font-size:13.5px;line-height:1.45;color:var(--color-texto-claro)}
.pub-campo.is-error .form__input,.pub-campo.is-error .form__textarea{border-color:#dc2626;background:#fff5f5}
.pub-campo.is-error .pub-campo__lbl{color:#b91c1c}
.pub-paso .form__input,.pub-paso .form__select,.pub-paso .form__textarea{font-size:16px}
.pub-fila{display:grid;gap:10px}
@media(min-width:640px){.pub-fila--2{grid-template-columns:1fr 1fr}}
.pub-sug{display:flex;flex-wrap:wrap;align-items:center;gap:6px;margin-top:6px}
.pub-sug:empty{display:none}
.pub-sug__lbl{font-size:13px;font-weight:700;color:var(--color-texto-claro)}
.pub-sug__item{min-height:44px;padding:8px 12px;border:1.5px dashed rgba(109,7,26,.38);border-radius:12px;
    background:#fff7f9;color:var(--color-primario);font-family:inherit;font-size:15px;font-weight:700;
    text-align:left;cursor:pointer}
.pub-extra{margin-top:12px;padding:12px;border:1.5px dashed var(--color-borde);border-radius:12px;background:#fafafa}
.pub--vivo .pub-extra{display:none}
.pub--vivo .pub-extra.is-visible{display:block}
.pub-mini{display:inline-flex;align-items:center;gap:5px;min-height:44px;margin-top:6px;padding:8px 13px;
    border:1.5px solid var(--color-borde);border-radius:999px;background:#fff;color:var(--color-primario);
    font-family:inherit;font-size:14px;font-weight:700;cursor:pointer}

/* --- Navegación del asistente: botones ≥48 px --- */
.pub-nav{display:flex;align-items:center;gap:10px;margin-top:14px}
.pub-btn{display:inline-flex;align-items:center;justify-content:center;gap:6px;min-height:48px;padding:12px 18px;
    border:1.5px solid var(--color-borde);border-radius:12px;background:#fff;color:var(--color-texto);
    font-family:inherit;font-size:16px;font-weight:800;cursor:pointer}
.pub-btn--sig{margin-left:auto;background:var(--color-primario);border-color:var(--color-primario);color:#fff;
    box-shadow:0 4px 12px rgba(109,7,26,.25)}
.pub-btn--enviar{flex:1 1 auto;min-height:54px;background:#15803d;border-color:#15803d;color:#fff;font-size:17px}
.pub-btn[disabled]{opacity:.45;cursor:not-allowed;box-shadow:none}

/* --- ⛔ LA ALERTA OBLIGATORIA (paso 8): imposible de no ver --- */
.pub-alerta{display:flex;gap:11px;align-items:flex-start;padding:14px;border:3px solid #dc2626;border-radius:14px;
    background:repeating-linear-gradient(135deg,#fff1f2 0 14px,#ffe4e6 14px 28px);
    box-shadow:0 6px 18px rgba(220,38,38,.2);color:#7f1d1d}
.pub-alerta__ico{font-size:28px;line-height:1}
.pub-alerta__t{margin:0 0 4px;font-size:17.5px;font-weight:800;color:#991b1b}
.pub-alerta__s{margin:0;font-size:16px;font-weight:700;line-height:1.5}
.pub-declaro{margin-top:12px}
.pub-noscript{margin:0 0 10px;padding:10px 12px;border-radius:12px;background:rgba(255,255,255,.18);
    border:1px solid rgba(255,255,255,.4);font-size:14px;font-weight:600}

.empleos-pie{margin:18px 0 4px;font-size:14px;line-height:1.5;color:var(--color-texto-claro);text-align:center}
</style>

<h1 class="seccion__titulo" style="margin-bottom:6px"><?= e($h1_txt) ?></h1>
<p style="color:var(--color-texto-claro);margin-bottom:12px">
    Publicar es <b>gratis</b> y el aviso dura <b>30 días</b>. Quien busca trabajo también puede publicar.
</p>

<!-- ⛔ LA CAJA QUE NO SE QUITA (regla del jefe): arriba y bien visible. -->
<div class="flash flash--warning empleos-aviso">
    ⚠️ <b>Nunca pagues por un trabajo.</b> Si te piden dinero para darte el empleo, es una estafa: repórtalo.
</div>

<!-- Contador honesto: cuántos avisos activos hay de verdad (fecha de Lima). -->
<p class="empleos-count">
    <b><?= (int)$total_activos ?></b> aviso<?= $total_activos === 1 ? '' : 's' ?> activo<?= $total_activos === 1 ? '' : 's' ?>
    en esta página
    <?php if ($hay_filtros): ?>
        · <b><?= (int)$total ?></b> coincide<?= $total === 1 ? '' : 'n' ?> con tus filtros
    <?php endif; ?>
</p>

<!-- ======================= FILTROS (estado en la URL) ======================= -->
<div class="empleos-toolbar">

    <form method="get" action="<?= e(url('empleos')) ?>" role="search">
        <?php // Los filtros que no tienen control propio viajan como campos ocultos: si no,
              // escribir una palabra borraría el oficio o la zona que la persona ya eligió. ?>
        <input type="hidden" name="tipo"   value="<?= e($filtros['tipo']) ?>">
        <input type="hidden" name="oficio" value="<?= e($filtros['oficio']) ?>">
        <input type="hidden" name="zona"   value="<?= e($filtros['zona']) ?>">
        <input type="hidden" name="cat"    value="<?= e($filtros['cat']) ?>">
        <?php if ((int)$filtros['negocio_id'] > 0): ?>
            <input type="hidden" name="negocio" value="<?= (int)$filtros['negocio_id'] ?>">
        <?php endif; ?>

        <div class="empleos-toolbar__fila">
            <?php /* `data-fuzzy` = el buscador predictivo del sitio (Fuse.js, ya cargado en el
                     header): tolera errores de tipeo. Su lista de datos la sirve
                     `api/empleos_json.php` desde el motor del sitio: aquí NO se escribe lógica
                     de Fuse propia, solo se marca el campo, igual que en la barra superior. */ ?>
            <input class="form__input empleos-toolbar__q" type="search" name="q" data-fuzzy
                   value="<?= e($filtros['q']) ?>" autocomplete="off"
                   placeholder="🔎 Puesto, negocio o palabra clave…" aria-label="Buscar en los avisos de empleo">
            <input class="form__input empleos-toolbar__c" type="search" name="ciudad"
                   value="<?= e($filtros['ciudad']) ?>" list="lista-ciudades" autocomplete="off"
                   placeholder="Casma, Huarmey…" aria-label="Ciudad o zona escrita a mano">
            <?php /* Autocompletado nativo del campo libre (Regla de Oro: nada de escribir de memoria).
                     Solo las ciudades que NO son distrito del Santa: los distritos se eligen con chips. */ ?>
            <datalist id="lista-ciudades">
                <option value="Casma"></option>
                <option value="Huarmey"></option>
            </datalist>
            <select class="form__select empleos-toolbar__sel" name="orden" aria-label="Orden de los avisos">
                <option value="nuevo" <?= $filtros['orden'] !== 'vence' ? 'selected' : '' ?>>🆕 Más nuevos primero</option>
                <option value="vence" <?= $filtros['orden'] === 'vence' ? 'selected' : '' ?>>⏳ Los que vencen antes</option>
            </select>
            <button type="submit" class="btn">Buscar</button>
        </div>
    </form>

    <?php // ---- Chips de TIPO (sin número: los helpers de empleos solo cuentan por oficio y zona) ---- ?>
    <div class="empleos-toolbar__bloque">
        <div class="empleos-toolbar__lbl">Qué buscas</div>
        <div class="empleos-toolbar__chips">
            <?= empleosdb_chip_html('📋 Todo (' . (int)$total_activos . ')', empleosdb_url(empleosdb_filtros_con($filtros, 'tipo', '')), $filtros['tipo'] === '', 'Ver los avisos de los 3 tipos') ?>
            <?php foreach ($tipos as $k => $t): ?>
                <?= empleosdb_chip_html($t['icono'] . ' ' . $t['etiqueta'], empleosdb_url(empleosdb_filtros_con($filtros, 'tipo', $k)), $filtros['tipo'] === $k, $t['nota']) ?>
            <?php endforeach; ?>
        </div>
    </div>

    <?php
    // ---- Chips de OFICIO con su número (contar_empleos_por_oficio) ----
    // Solo se ofrecen los oficios que HOY tienen avisos: un chip que no lleva a ninguna parte
    // es una promesa falsa. El oficio activo se pinta siempre, aunque se haya quedado en 0.
    $chips_oficio = '';
    foreach ($oficios as $slug => $o) {
        $n = (int)($por_oficio[$slug] ?? 0);
        if ($n === 0 && $filtros['oficio'] !== $slug) continue;
        $activo = ($filtros['oficio'] === $slug);
        $chips_oficio .= empleosdb_chip_html(
            $o['icono'] . ' ' . $o['nombre'] . ' (' . $n . ')',
            empleosdb_url(empleosdb_filtros_con($filtros, 'oficio', $activo ? '' : $slug)),
            $activo,
            $activo ? 'Quitar este filtro' : $n . ' aviso(s) de ' . $o['nombre']
        );
    }
    if ($chips_oficio !== ''): ?>
        <div class="empleos-toolbar__bloque">
            <div class="empleos-toolbar__lbl">Oficio</div>
            <div class="empleos-toolbar__chips"><?= $chips_oficio ?></div>
        </div>
    <?php endif; ?>

    <?php
    // ---- Chips de ZONA con su número (contar_empleos_por_zona, por id de distrito) ----
    $chips_zona = '';
    foreach ($distritos as $d) {
        $n = (int)($por_zona[(int)$d['id']] ?? 0);
        $slug_d = (string)$d['slug'];
        if ($n === 0 && $filtros['zona'] !== $slug_d) continue;
        $activo = ($filtros['zona'] === $slug_d);
        $chips_zona .= empleosdb_chip_html(
            '📍 ' . $d['nombre'] . ' (' . $n . ')',
            empleosdb_url(empleosdb_filtros_con($filtros, 'zona', $activo ? '' : $slug_d)),
            $activo,
            $activo ? 'Quitar este filtro' : $n . ' aviso(s) en ' . $d['nombre']
        );
    }
    if ($chips_zona !== ''): ?>
        <div class="empleos-toolbar__bloque">
            <div class="empleos-toolbar__lbl">Zona</div>
            <div class="empleos-toolbar__chips"><?= $chips_zona ?></div>
        </div>
    <?php endif; ?>

    <?php
    // ---- Filtros que llegaron por enlace (rubro / tienda): se ven y se pueden quitar ----
    // El rubro NO se ofrece como lista larga (Regla de Oro n.º 1): llega por enlace
    // (`?cat=<slug>`) desde las páginas del directorio y aquí se muestra puesto.
    $quitar = [];
    if ($filtros['cat'] !== '') {
        foreach ($categorias as $c) {
            if ((string)$c['slug'] === $filtros['cat']) {
                $quitar[] = ['🏷️ Rubro: ' . $c['nombre'], empleosdb_url(empleosdb_filtros_con($filtros, 'cat', ''))];
                break;
            }
        }
    }
    if ((int)$filtros['negocio_id'] > 0) {
        $quitar[] = ['🏪 Solo los avisos de una tienda', empleosdb_url(empleosdb_filtros_con($filtros, 'negocio_id', 0))];
    }
    if ($filtros['ciudad'] !== '') {
        $quitar[] = ['🌎 Ciudad: ' . $filtros['ciudad'], empleosdb_url(empleosdb_filtros_con($filtros, 'ciudad', ''))];
    }
    if ($quitar): ?>
        <div class="empleos-toolbar__bloque">
            <div class="empleos-toolbar__lbl">Filtros puestos</div>
            <div class="empleos-toolbar__chips">
                <?php foreach ($quitar as $q): ?>
                    <?= empleosdb_chip_html($q[0] . ' ✕', $q[1], true, 'Quitar este filtro') ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($hay_filtros): ?>
        <div class="empleos-toolbar__acciones">
            <a class="empleos-toolbar__limpiar" href="<?= e(url('empleos')) ?>">🧹 Limpiar filtros</a>
        </div>
    <?php endif; ?>
</div>

<!-- ======================= LOS AVISOS ======================= -->
<?php if (empty($filas)): ?>

    <?php if ($total > 0): ?>
        <?php // La página pedida no existe (p. ej. `?p=99`): no se deja al visitante en blanco. ?>
        <div class="empleo-bloque-vacio">
            <span class="empleo-bloque-vacio__ico">📄</span>
            <div class="empleo-bloque-vacio__t">Esa página no tiene avisos</div>
            <p>Esta búsqueda tiene <?= (int)$paginas ?> página<?= $paginas === 1 ? '' : 's' ?> con esos filtros.</p>
            <a class="empleo-bloque-vacio__btn" href="<?= e(empleosdb_url($filtros, 1)) ?>">Ver la primera página</a>
        </div>
    <?php else: ?>
        <div class="empleo-bloque-vacio">
            <span class="empleo-bloque-vacio__ico">🪧</span>
            <div class="empleo-bloque-vacio__t">
                <?= $hay_filtros ? 'No hay avisos con esos filtros' : 'Todavía no hay avisos: sé el primero' ?>
            </div>
            <p>
                <?= $hay_filtros
                    ? 'Prueba con otro oficio o con otra zona — o publica tu propio aviso: es gratis y sale hoy mismo.'
                    : 'Todavía no hay avisos publicados. Publica el tuyo (ofreces trabajo, buscas trabajo o es un anuncio) y aparecerá aquí mismo.' ?>
            </p>
            <a class="empleo-bloque-vacio__btn" href="#publicar">✍️ Publicar el primer aviso</a>
            <?php if ($hay_filtros): ?>
                <p style="margin-top:8px"><a class="empleos-toolbar__limpiar" href="<?= e(url('empleos')) ?>">🧹 Limpiar filtros</a></p>
            <?php endif; ?>
        </div>
    <?php endif; ?>

<?php else: ?>

    <?php // `grid-empleos--completa`: aquí se ven LOS 24 de la página (el recorte del
          // bloque del index esconde de la 9.ª tarjeta en celular y aquí no tiene sentido). ?>
    <div class="grid-empleos grid-empleos--completa">
        <?php foreach ($filas as $a) echo empleo_card_html($a); ?>
    </div>

    <?= empleosdb_paginador_html($filtros, $pagina, $paginas) ?>

    <p style="text-align:center;margin-top:10px">
        <a class="btn" href="#publicar">✍️ Publicar un aviso gratis</a>
    </p>

<?php endif; ?>

<!-- ======================= GANCHO: empresa / tienda ======================= -->
<div class="empleos-caja">
    <p class="empleos-caja__t">🏪 ¿Eres una empresa o una tienda?</p>
    <?php if ($usuario): ?>
        <p class="empleos-caja__s">
            Publica tu oferta desde tu panel: queda en tu ficha de tienda y la ves ahí mismo,
            junto a tus productos y tus avisos.
        </p>
        <a class="btn" href="<?= e(url('panel.php')) ?>">💼 Publicar desde mi panel</a>
    <?php else: ?>
        <p class="empleos-caja__s">
            Crea tu cuenta gratis y publica tus ofertas desde tu panel, sin esperar la revisión
            de los avisos de visitante. Además tendrás tu ficha de tienda en el directorio.
        </p>
        <a class="btn" href="<?= e(url('registro.php')) ?>">✍️ Crear mi cuenta gratis</a>
    <?php endif; ?>
</div>

<!-- ============ ✍️ PUBLICAR UN AVISO GRATIS — ASISTENTE POR PASOS (tipo chatbot) ============ -->
<?php // 💼 Si el que publica es el DUEÑO de una tienda (puerta B: botón «Ofrecer empleo» del panel),
      // el aviso sale ACTIVO y colgado de su ficha; si es un visitante, queda pendiente de revisión.
      // El `negocio_id` viaja oculto y el servidor vuelve a comprobar el `dueno_id`: no se confía
      // nunca en lo que manda el formulario.
      //
      // ✍️ EL ASISTENTE (pedido del jefe, 2026-09-12): «necesita sus fondos y bordes, además de ser
      //    inteligente para pedir datos como un chatbot, el máximo posible que el usuario solo dé
      //    clics». Son 8 pasos, uno detrás de otro, cada uno en su TARJETA; solo se teclean el
      //    teléfono (y lo que la persona quiera contar). Los pasos NO se esconden en el HTML: los
      //    apaga el JS al arrancar (`.pub--vivo`), así sin JavaScript se ven los 8 y el formulario
      //    se envía igual. La validación de verdad (longitudes, teléfono, antispam) la hace el
      //    servidor, como siempre. ?>
<section class="pub" id="publicar" data-pub<?= $error_form ? ' data-pub-error="1"' : '' ?>>
    <div class="pub-cab">
        <h2 class="pub-cab__t">✍️ Publica un aviso gratis</h2>
        <?php // 📝 EL TEXTO DE ENTRADA: corto y amable (orden del jefe, 2026-09-12: «sé amable pero no
              // cuentes todo; lo máximo que puedes poner es "Vamos a crear un anuncio paso a paso, tu
              // anuncio durará 30 días"»). Antes había aquí un párrafo de folleto explicando el chat,
              // el teléfono, el texto y las fotos: sobraba. Lo que hay que saber se ve en el camino. ?>
        <p class="pub-cab__s">
            Vamos a crear un anuncio paso a paso. Tu anuncio durará <b><?= (int)EMPLEO_DIAS ?> días</b>.
        </p>
        <?php if ($negocio_mio): ?>
            <p class="pub-cab__yo">
                🏪 Publicas como <b><?= e($negocio_mio['nombre']) ?></b>: sale al instante y también en tu ficha.
            </p>
        <?php endif; ?>

        <noscript>
            <p class="pub-noscript">
                👉 Rellena los 8 pasos y pulsa «Publicar mi aviso» al final.
            </p>
        </noscript>

        <?php /* La barra de progreso y el resumen los mueve el JS del asistente (más abajo). */ ?>
        <div class="pub-prog" id="pubProg" role="progressbar" aria-valuemin="1" aria-valuemax="8"
             aria-valuenow="1" aria-label="Avance del asistente">
            <span class="pub-prog__barra" id="pubBarra"></span>
        </div>
        <p class="pub-prog__txt" id="pubPasoTxt">Paso 1 de 8 · ¿Qué publicas?</p>
        <p class="pub-resumen" id="pubResumen" aria-live="polite">
            <span class="pub-resumen__vacio">Tu aviso se va armando aquí: lo que lleves, lo verás.</span>
        </p>
    </div>

    <?php if ($error_form): ?>
        <div class="flash flash--error" style="margin:12px 0">
            Falta corregir algo para poder publicar: te llevo al paso donde está lo que falta.
            (Los avisos de arriba dicen exactamente qué es.)
        </div>
    <?php endif; ?>

    <form method="post" action="<?= e(url('empleos')) ?>#publicar" class="empleo-form" id="pubForm">
        <?= csrf_campo() ?>
        <?php if ($negocio_mio): ?>
            <input type="hidden" name="negocio_id" value="<?= (int)$negocio_mio['id'] ?>">
        <?php endif; ?>

        <div class="pub-pasos" id="pubPasos">

        <?php // ================== PASO 1 · ¿QUÉ PUBLICAS? ==================
        // Los 3 tipos del módulo (`empleo_tipos()`) en botones grandes. Con ESTE clic el asistente
        // ya sabe si el paso 6 tiene que preguntar «¿cuánto pagas?» o «¿cuánto quieres cobrar?». ?>
        <div class="pub-paso" data-paso="1" data-titulo="¿Qué publicas?">
            <div class="pub-paso__cab">
                <span class="pub-paso__num" aria-hidden="true">1</span>
                <h3 class="pub-paso__t">¿Qué publicas?</h3>
            </div>
            <p class="pub-paso__ayuda">Elige una opción.</p>
            <div class="pub-chips pub-chips--tipo">
                <?php // 🎯 El tipo puede venir ya elegido desde fuera (`/empleos?tipo=busco#publicar`):
                // los 2 botones que van debajo de cada aviso en `empleo.php` mandan aquí con el tipo
                // puesto, así el que viene de «Busco trabajo» no tiene que volver a elegirlo.
                // ⚠️ El parámetro se llama `nuevo` A PROPÓSITO: `tipo` es el FILTRO del listado, y
                // usarlo dejaría la lista vacía al llegar desde «Busco trabajo» (todavía no hay
                // avisos de ese tipo). Con `nuevo` la lista se ve completa y el asistente arranca
                // con la opción ya marcada.
                $tipo_marca = (string)($_POST['tipo'] ?? $_GET['nuevo'] ?? $_GET['tipo'] ?? 'ofrezco');
                if (!isset($tipos[$tipo_marca])) $tipo_marca = 'ofrezco';
                foreach ($tipos as $k => $t): ?>
                    <?= empleosdb_chip_pub('tipo', $k, $t['icono'] . ' ' . $t['etiqueta'], $t['nota'],
                          ($tipo_marca === $k),
                          ['resumen' => $t['icono'] . ' ' . $t['etiqueta']]) ?>
                <?php endforeach; ?>
            </div>
            <div class="pub-nav">
                <button type="button" class="pub-btn pub-btn--atras" data-pub-atras hidden>‹ Atrás</button>
                <button type="button" class="pub-btn pub-btn--sig" data-pub-sig>Siguiente ➤</button>
            </div>
        </div>

        <?php // ================== PASO 2 · ¿DE QUÉ ES EL PUESTO? ==================
        // Los 12 oficios (`empleo_oficios()`) como chips + una caja de texto que FILTRA los chips
        // mientras se escribe (Fuse.js: perdona las faltas de ortografía). El campo no tiene
        // `name` a propósito: es un filtro, no un dato del aviso. ?>
        <div class="pub-paso" data-paso="2" data-titulo="¿De qué es el puesto?">
            <div class="pub-paso__cab">
                <span class="pub-paso__num" aria-hidden="true">2</span>
                <h3 class="pub-paso__t">¿De qué es el puesto?</h3>
            </div>
            <p class="pub-paso__ayuda">
                Toca el oficio. Si no te acuerdas del nombre, escríbelo como te salga: el buscador
                lo encuentra igual aunque lo escribas con faltas.
            </p>
            <div class="pub-campo">
                <label class="pub-campo__lbl" for="pubFiltroOficio">🔎 Busca tu oficio</label>
                <input class="form__input" type="search" id="pubFiltroOficio" autocomplete="off"
                       placeholder="cocina, albañil, vendedora, motorizado…"
                       aria-label="Filtrar los oficios mientras escribes">
            </div>
            <div class="pub-chips pub-chips--oficio">
                <?php foreach ($oficios as $slug => $o): ?>
                    <?php /* `data-busca` lleva el nombre oficial + los sinónimos de la calle: es lo
                             que mira el fuzzy. El chip que se ve (y el valor que se guarda) es el
                             oficio del módulo, sin inventar nada. */ ?>
                    <?= empleosdb_chip_pub('oficio_slug', $slug, $o['icono'] . ' ' . $o['nombre'], '',
                          (($_POST['oficio_slug'] ?? '') === $slug),
                          ['busca'    => $o['nombre'] . ' ' . ($oficio_sinonimos[$slug] ?? ''),
                           'resumen'  => $o['icono'] . ' ' . $o['nombre']]) ?>
                <?php endforeach; ?>
            </div>
            <p class="pub-campo__ayuda" id="pubOficioVacio" hidden>
                Ningún oficio se llama así: toca <b>📌 Otros</b> y cuéntalo en la descripción (paso 7).
            </p>
            <div class="pub-nav">
                <button type="button" class="pub-btn pub-btn--atras" data-pub-atras>‹ Atrás</button>
                <button type="button" class="pub-btn pub-btn--sig" data-pub-sig>Siguiente ➤</button>
            </div>
        </div>

        <?php // ================== PASO 3 · ¿DÓNDE? ==================
        // Los 9 distritos visibles de la provincia del Santa (los de la BD, `obtener_distritos_visibles()`)
        // son chips. Lo que NO es distrito (Casma, Huarmey, Lima…) se escribe en la ciudad, con
        // sugerencias fuzzy de la lista de ciudades de la zona que vive en el JS del asistente. ?>
        <div class="pub-paso" data-paso="3" data-titulo="¿Dónde es?">
            <div class="pub-paso__cab">
                <span class="pub-paso__num" aria-hidden="true">3</span>
                <h3 class="pub-paso__t">¿Dónde es?</h3>
            </div>
            <p class="pub-paso__ayuda">Toca el distrito. Si es fuera del Santa, escribe la ciudad abajo.</p>
            <div class="pub-chips">
                <?= empleosdb_chip_pub('distrito_id', 0, '📍 Toda la provincia', 'No importa el distrito',
                      ((int)($_POST['distrito_id'] ?? 0) === 0), ['resumen' => '📍 Toda la provincia']) ?>
                <?php foreach ($distritos as $d): ?>
                    <?= empleosdb_chip_pub('distrito_id', (int)$d['id'], '📍 ' . $d['nombre'], '',
                          ((int)($_POST['distrito_id'] ?? 0) === (int)$d['id']),
                          ['resumen' => '📍 ' . $d['nombre']]) ?>
                <?php endforeach; ?>
            </div>
            <div class="pub-campo">
                <label class="pub-campo__lbl" for="pubCiudad">🌎 ¿Otra ciudad o una referencia?</label>
                <input class="form__input" type="text" id="pubCiudad" name="ciudad_txt" maxlength="80"
                       autocomplete="off" value="<?= e($_POST['ciudad_txt'] ?? '') ?>"
                       placeholder="Casma, Huarmey, Lima…">
                <?php /* Aquí el JS pinta las sugerencias del fuzzy (Fuse.js sobre las ciudades de la zona). */ ?>
                <div class="pub-sug" data-pub-sug="ciudad"></div>
                <p class="pub-campo__ayuda">
                    Solo si es fuera del Santa (Casma, Huarmey…) o si quieres dar una referencia
                    («a 2 cuadras del mercado»).
                </p>
            </div>
            <div class="pub-nav">
                <button type="button" class="pub-btn pub-btn--atras" data-pub-atras>‹ Atrás</button>
                <button type="button" class="pub-btn pub-btn--sig" data-pub-sig>Siguiente ➤</button>
            </div>
        </div>

        <?php // ================== PASO 4 · ¿QUÉ JORNADA? ==================
        // LAS 5 JORNADAS de `empleo_jornadas()`: el jefe lo dijo claro («son clicables porque no son
        // más de 5»). El campo «otro horario» es el plan B para el detalle exacto, y si se escribe
        // MANDA sobre el chip (`horario_txt` en el POST). ?>
        <div class="pub-paso" data-paso="4" data-titulo="¿Qué jornada?">
            <div class="pub-paso__cab">
                <span class="pub-paso__num" aria-hidden="true">4</span>
                <h3 class="pub-paso__t">¿Qué jornada?</h3>
            </div>
            <p class="pub-paso__ayuda">Son 5 y se tocan. Si tu caso no es ninguno, escribe el horario abajo.</p>
            <div class="pub-chips">
                <?php foreach ($jornadas_lista as $slug => $j): ?>
                    <?= empleosdb_chip_pub('jornada', $slug, $j['icono'] . ' ' . $j['nombre'], $j['nota'],
                          (($_POST['jornada'] ?? '') === $slug),
                          ['resumen' => $j['icono'] . ' ' . $j['nombre']]) ?>
                <?php endforeach; ?>
            </div>
            <div class="pub-campo">
                <label class="pub-campo__lbl" for="pubHorario">🕗 Otro horario (si hace falta)</label>
                <input class="form__input" type="text" id="pubHorario" name="horario_txt" maxlength="80"
                       value="<?= e($_POST['horario_txt'] ?? '') ?>" placeholder="lunes a sábado 8am–5pm">
                <p class="pub-campo__ayuda">Si lo escribes, ese es el horario que sale en el aviso.</p>
            </div>
            <div class="pub-campo">
                <label class="pub-campo__lbl" for="pubDuracion">⏳ ¿Cuánto dura? (opcional)</label>
                <input class="form__input" type="text" id="pubDuracion" name="duracion" maxlength="60"
                       value="<?= e($_POST['duracion'] ?? '') ?>" placeholder="obra de 3 meses, indefinido…">
            </div>
            <div class="pub-nav">
                <button type="button" class="pub-btn pub-btn--atras" data-pub-atras>‹ Atrás</button>
                <button type="button" class="pub-btn pub-btn--sig" data-pub-sig>Siguiente ➤</button>
            </div>
        </div>

        <?php // ================== PASO 5 · ¿A QUIÉN BUSCAS? ==================
        // Edad, nivel formativo y experiencia: TODO OPCIONAL y todo con clics. Si no eligen nada
        // (o tocan «Indistinto / me da igual») el aviso queda SIN ese dato: nunca se inventa
        // (regla del proyecto). Cada fila lleva su botón de «Indistinto». ?>
        <div class="pub-paso" data-paso="5" data-titulo="¿A quién buscas?">
            <div class="pub-paso__cab">
                <span class="pub-paso__num" aria-hidden="true">5</span>
                <h3 class="pub-paso__t">¿A quién buscas?</h3>
            </div>
            <p class="pub-paso__ayuda">
                Todo esto es <b>opcional</b>: si no eliges nada, el aviso no pide edad, estudios ni experiencia.
            </p>
            <div class="pub-campo">
                <span class="pub-campo__lbl">🎂 Edad</span>
                <div class="pub-chips">
                    <?php foreach ($edades_lista as $slug => $ed): ?>
                        <?= empleosdb_chip_pub('edad', $slug,
                              $ed['icono'] . ' ' . ($slug === 'indistinto' ? 'Indistinto / me da igual' : $ed['nombre']),
                              $slug === 'indistinto' ? '' : 'años',
                              (($_POST['edad'] ?? '') === $slug),
                              ['resumen' => $ed['min'] ? ('🎂 ' . $ed['nombre']) : '']) ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="pub-campo">
                <span class="pub-campo__lbl">🎓 Nivel de estudios</span>
                <div class="pub-chips">
                    <?php foreach ($niveles_lista as $slug => $nv): ?>
                        <?= empleosdb_chip_pub('nivel_formativo', $slug,
                              $nv['icono'] . ' ' . ($slug === 'indistinto' ? 'Indistinto / me da igual' : $nv['nombre']), '',
                              (($_POST['nivel_formativo'] ?? '') === $slug),
                              ['resumen' => $slug === 'indistinto' ? '' : ($nv['icono'] . ' ' . $nv['nombre'])]) ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="pub-campo">
                <span class="pub-campo__lbl">💪 Experiencia</span>
                <div class="pub-chips">
                    <?php foreach ($exper_lista as $slug => $ex): ?>
                        <?= empleosdb_chip_pub('experiencia', $slug,
                              $ex['icono'] . ' ' . ($slug === 'indistinto' ? 'Indistinto / me da igual' : $ex['nombre']), '',
                              (($_POST['experiencia'] ?? '') === $slug),
                              ['resumen' => $slug === 'indistinto' ? '' : ($ex['icono'] . ' ' . $ex['nombre'])]) ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="pub-campo">
                <label class="pub-campo__lbl" for="pubRequisitos">📋 ¿Pides algo más? (opcional, se puede dictar)</label>
                <textarea class="form__textarea" id="pubRequisitos" name="requisitos" rows="2" maxlength="500"
                          data-dictado="texto" placeholder="Ej. DNI, movilidad propia, disponibilidad para viajar."><?= e($_POST['requisitos'] ?? '') ?></textarea>
            </div>
            <div class="pub-nav">
                <button type="button" class="pub-btn pub-btn--atras" data-pub-atras>‹ Atrás</button>
                <button type="button" class="pub-btn pub-btn--sig" data-pub-sig>Siguiente ➤</button>
            </div>
        </div>

        <?php // ================== PASO 6 · ¿CUÁNTO PAGA? ==================
        // 💰 `empleo_sueldos()`: el primer chip es el MÍNIMO LEGAL DEL PERÚ (S/ 1 250 al mes) y las
        // variantes por quincena, semana, día y hora se CALCULAN desde ahí (no son cifras inventadas).
        // El rótulo cambia a «¿Cuánto quieres cobrar?» si en el paso 1 eligieron «Busco trabajo».
        // «Otro monto» revela la caja con el número + el periodo + el texto libre; si escriben texto
        // libre, ESE texto manda (el servidor lo guarda en `sueldo_txt`). Nada elegido = «A convenir». ?>
        <div class="pub-paso" data-paso="6" data-titulo="¿Cuánto paga?">
            <div class="pub-paso__cab">
                <span class="pub-paso__num" aria-hidden="true">6</span>
                <h3 class="pub-paso__t" id="pubSueldoT">¿Cuánto paga?</h3>
            </div>
            <p class="pub-paso__ayuda" id="pubSueldoA">
                El mínimo legal del Perú es <b>S/ 1 250 al mes</b>: por eso esa es la primera opción
                (y las de la semana, la quincena, el día y la hora salen de ahí).
            </p>
            <div class="pub-chips">
                <?php foreach ($sueldos_lista as $k => $s): ?>
                    <?= empleosdb_chip_pub('sueldo_preset', $k, $s['nombre'], $s['nota'],
                          ((string)($_POST['sueldo_preset'] ?? '') === $k),
                          ['periodo' => $s['periodo'], 'resumen' => $s['nombre']]) ?>
                <?php endforeach; ?>
                <?php // Este chip es el que ABRE la caja de abajo (el JS le pone `is-visible`). ?>
                <?= empleosdb_chip_pub('sueldo_preset', '', '✍️ Otro monto o texto', 'Lo escribo yo',
                      $error_form && (string)($_POST['sueldo_preset'] ?? '') === '', ['otro' => '1']) ?>
            </div>
            <div class="pub-extra" id="pubOtroMonto">
                <div class="pub-fila pub-fila--2">
                    <div class="pub-campo" style="margin-top:0">
                        <label class="pub-campo__lbl" for="pubMonto">💵 Monto en soles</label>
                        <input class="form__input" type="number" id="pubMonto" name="sueldo_monto" min="0"
                               max="999999" step="10" inputmode="decimal" placeholder="1250"
                               value="<?= e($_POST['sueldo_monto'] ?? '') ?>">
                    </div>
                    <div class="pub-campo" style="margin-top:0">
                        <label class="pub-campo__lbl" for="pubPeriodo">🔁 Cada cuánto</label>
                        <select class="form__select" id="pubPeriodo" name="sueldo_periodo">
                            <option value="">Elige el periodo…</option>
                            <?php foreach ($periodos_txt as $p => $ptxt): ?>
                                <option value="<?= e($p) ?>" <?= ((string)($_POST['sueldo_periodo'] ?? '') === $p) ? 'selected' : '' ?>>
                                    <?= e('S/ … ' . $ptxt) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="pub-campo">
                    <label class="pub-campo__lbl" for="pubSueldoLibre">📝 O escríbelo como quieras</label>
                    <input class="form__input" type="text" id="pubSueldoLibre" name="sueldo_libre" maxlength="80"
                           value="<?= e($_POST['sueldo_libre'] ?? '') ?>" placeholder="S/ 30 el día + pasajes">
                    <p class="pub-campo__ayuda">
                        Si escribes algo aquí, <b>esto es lo que sale en el aviso</b> (manda sobre el
                        monto de arriba). Sirve para casos como «S/ 30 el día + pasajes».
                    </p>
                </div>
            </div>
            <p class="pub-campo__ayuda">
                Si no eliges nada, en el aviso sale <b>A convenir</b>: nunca inventamos un sueldo.
            </p>
            <div class="pub-nav">
                <button type="button" class="pub-btn pub-btn--atras" data-pub-atras>‹ Atrás</button>
                <button type="button" class="pub-btn pub-btn--sig" data-pub-sig>Siguiente ➤</button>
            </div>
        </div>

        <?php // ================== PASO 7 · CUÉNTALO Y DEJA TU CONTACTO ==================
        // Aquí está lo ÚNICO que hay que teclear. El título y la descripción se pueden DICTAR
        // (`data-dictado` de assets/js/dictado_voz.js, igual que en productos.php) y el título
        // además SUGIERE los puestos que ya existen (`api/empleos_json.php`, con Fuse). ?>
        <div class="pub-paso" data-paso="7" data-titulo="Cuéntalo y deja tu contacto">
            <div class="pub-paso__cab">
                <span class="pub-paso__num" aria-hidden="true">7</span>
                <h3 class="pub-paso__t">Cuéntalo y deja tu contacto</h3>
            </div>
            <p class="pub-paso__ayuda">
                Casi todo está hecho. Aquí solo escribes el puesto, qué se hace y tu teléfono:
                con el 🎙️ hasta se puede hablar en vez de teclear.
            </p>
            <div class="pub-campo">
                <label class="pub-campo__lbl" for="pubTitulo">📌 El puesto *</label>
                <input class="form__input" type="text" id="pubTitulo" name="titulo" maxlength="150"
                       required minlength="6" data-pub-quitar-nativo="1" data-dictado="titulo"
                       value="<?= e($_POST['titulo'] ?? '') ?>" placeholder="Ej. Ayudante de cocina">
                <?php /* Sugerencias del fuzzy con los títulos que YA están publicados (Fuse.js). */ ?>
                <div class="pub-sug" data-pub-sug="titulo"></div>
                <p class="pub-campo__ayuda">
                    Escribe 3 letras y te propongo puestos que ya están publicados: si uno te sirve,
                    tócalo y se rellena solo.
                </p>
            </div>
            <div class="pub-campo">
                <label class="pub-campo__lbl" for="pubEntidad">🏪 ¿Quién publica?</label>
                <input class="form__input" type="text" id="pubEntidad" name="entidad" maxlength="120"
                       value="<?= e($_POST['entidad'] ?? ($negocio_mio ? (string)$negocio_mio['nombre'] : '')) ?>"
                       placeholder="El nombre de tu negocio">
                <button type="button" class="pub-mini" data-pub-rellena="entidad" data-pub-valor="Particular">🙋 Soy particular</button>
            </div>
            <div class="pub-campo">
                <label class="pub-campo__lbl" for="pubDesc">📝 Cuenta el trabajo *</label>
                <textarea class="form__textarea" id="pubDesc" name="descripcion" rows="4" maxlength="3000"
                          required minlength="20" data-pub-quitar-nativo="1" data-dictado="descripcion"
                          placeholder="Qué se hace, en qué horario y cómo postular. Toca 🎙️ y dilo hablando."><?= e($_POST['descripcion'] ?? '') ?></textarea>
                <p class="pub-campo__ayuda">
                    Mínimo 20 letras. Lo lee quien busca trabajo: escribe claro, sin groserías y sin
                    pedir dinero (eso es una estafa).
                </p>
            </div>
            <div class="pub-fila pub-fila--2">
                <div class="pub-campo">
                    <label class="pub-campo__lbl" for="pubTel">📞 Teléfono *</label>
                    <input class="form__input" type="tel" id="pubTel" name="telefono" inputmode="numeric"
                           maxlength="15" value="<?= e($_POST['telefono'] ?? '') ?>" placeholder="943111222">
                    <p class="pub-campo__ayuda">9 dígitos que empiezan en 9.</p>
                </div>
                <div class="pub-campo">
                    <label class="pub-campo__lbl" for="pubWa">🟢 WhatsApp</label>
                    <input class="form__input" type="tel" id="pubWa" name="whatsapp" inputmode="numeric"
                           maxlength="15" value="<?= e($_POST['whatsapp'] ?? '') ?>" placeholder="943111222">
                    <button type="button" class="pub-mini" data-pub-rellena="whatsapp" data-pub-valor-de="telefono">📱 El mismo número</button>
                </div>
            </div>
            <p class="pub-campo__ayuda">
                Deja el teléfono <b>o</b> el WhatsApp: sin uno de los dos nadie puede postular a tu
                aviso. Si pones el WhatsApp, quien quiera postular te escribe con un solo toque.
            </p>
            <div class="pub-nav">
                <button type="button" class="pub-btn pub-btn--atras" data-pub-atras>‹ Atrás</button>
                <button type="button" class="pub-btn pub-btn--sig" data-pub-sig>Siguiente ➤</button>
            </div>
        </div>

        <?php // ================== PASO 8 · LA ALERTA OBLIGATORIA ==================
        // ⛔ El jefe lo pidió con estas palabras (2026-09-12): «el mensaje de no pedir dinero por dar
        //    trabajo hazlo como un alert que debe aceptar antes de publicar un anuncio». Por eso:
        //    la caja roja (imposible de no ver) + la casilla `declaro` que hay que marcar para que
        //    el botón de publicar se encienda. El servidor la sigue exigiendo igual que siempre,
        //    así que desmarcarla no sirve de nada aunque alguien toque el HTML. ?>
        <div class="pub-paso" data-paso="8" data-titulo="Antes de publicar">
            <div class="pub-paso__cab">
                <span class="pub-paso__num" aria-hidden="true">8</span>
                <h3 class="pub-paso__t">Antes de publicar</h3>
            </div>
            <div class="pub-alerta" role="alert">
                <span class="pub-alerta__ico" aria-hidden="true">⚠️</span>
                <div>
                    <p class="pub-alerta__t">Nunca pagues por un trabajo</p>
                    <p class="pub-alerta__s">
                        DeChimbote.com no cobra nada por publicar y ninguna empresa seria te pide
                        dinero para darte empleo. Si te piden pagar, es una estafa.
                    </p>
                </div>
            </div>
            <?php /* La casilla que frena las estafas: sin marcarla no se publica (orden del jefe). */ ?>
            <label class="empleo-check pub-declaro">
                <input type="checkbox" name="declaro" value="1" id="pubDeclaro" required
                       data-pub-quitar-nativo="1" <?= !empty($_POST['declaro']) ? 'checked' : '' ?>>
                <span>Entiendo y acepto: <b>NO cobro nada al postulante</b> ni pido dinero por el trabajo. *</span>
            </label>
            <div class="pub-nav">
                <button type="button" class="pub-btn pub-btn--atras" data-pub-atras>‹ Atrás</button>
                <button type="submit" class="pub-btn pub-btn--enviar" id="pubEnviar">✅ Publicar mi aviso</button>
            </div>
            <p class="pub-campo__ayuda" id="pubEnviarNota">Marca la casilla de arriba para poder publicar.</p>
            <p class="pub-campo__ayuda">
                <?php if ($negocio_mio): ?>
                    Publicas como <b><?= e($negocio_mio['nombre']) ?></b>: tu aviso sale al instante.
                <?php else: ?>
                    Un administrador lo revisa antes de publicarlo (para que no entren estafas).
                    Si te piden dinero por publicarte, no es de este sitio: repórtalo.
                <?php endif; ?>
            </p>
        </div>

        </div><!-- /.pub-pasos -->
    </form>

    <?php // 🎙️ El dictado por voz del sitio (el mismo de productos.php): pinta la píldora en los
          // campos con `data-dictado` (título, descripción y requisitos). Si el archivo no está o
          // el navegador no soporta la Web Speech API, la píldora no se pinta y se escribe normal.
          // Va ANTES del JS del asistente para que el campo ya exista cuando este arranca. ?>
    <?php if (is_file(__DIR__ . '/assets/js/dictado_voz.js')): ?>
        <script src="<?= url('assets/js/dictado_voz.js') ?>?v=1"></script>
    <?php endif; ?>

    <script>
    /* ==========================================================================
     * ✍️ EL ASISTENTE DE PUBLICACIÓN (empleosdb.php) — pedido del jefe, 2026-09-12
     * --------------------------------------------------------------------------
     * QUÉ HACE, EN ORDEN:
     *   1. Enciende el asistente: pone `.pub--vivo` (recién ahí se esconden los pasos que no
     *      tocan). SIN JavaScript no se ejecuta nada de esto y los 8 pasos quedan a la vista.
     *   2. Navega paso a paso («Siguiente ➤» / «‹ Atrás») con barra de progreso y «Paso X de 8».
     *      NO bloquea el avance: se puede saltar hacia adelante y volver (el jefe lo pidió así);
     *      la validación de verdad la hace el servidor.
     *   3. Pinta el RESUMEN VIVO («🍳 Cocina · 📍 Chimbote · 🕗 Tiempo completo · 🎂 26 a 35 ·
     *      💰 S/ 1 250 al mes») que crece con cada respuesta, para que vean su aviso antes de enviar.
     *   4. FUZZY (Fuse.js, ya cargado en el header del sitio): filtra los 12 chips de oficio,
     *      sugiere ciudades de la zona y sugiere TÍTULOS de los avisos que ya existen
     *      (`api/empleos_json.php`, clave `t`). Todo perdona faltas de ortografía.
     *   5. Detalles tontos que hacen la diferencia: el rótulo del sueldo («¿cuánto pagas?» /
     *      «¿cuánto quieres cobrar?»), «🙋 Soy particular», «📱 El mismo número» y el botón de
     *      publicar APAGADO hasta que marquen la casilla de la alerta.
     * --------------------------------------------------------------------------
     * ⚠️ TRAMPA YA PISADA (por qué se quitan los `required` nativos): un campo `required` dentro de
     *    un paso OCULTO (`display:none`) no se puede enfocar, y el navegador BLOQUEA el envío con un
     *    error que nadie ve («An invalid form control is not focusable»). Como el asistente esconde
     *    los pasos que no tocan, aquí se quitan esos atributos y el asistente avisa a su manera
     *    (llevando a la persona al paso que falta). Sin JS siguen puestos y los exige el navegador.
     * ========================================================================== */
    (function () {
        'use strict';

        var raiz  = document.getElementById('publicar');
        var form  = document.getElementById('pubForm');
        var cajas = document.getElementById('pubPasos');
        if (!raiz || !form || !cajas) return;

        var pasos = Array.prototype.slice.call(cajas.querySelectorAll('.pub-paso'));
        if (!pasos.length) return;

        // 🌎 Las ciudades de la zona que sugiere el paso 3: lo que NO es distrito del Santa
        //    (los distritos se eligen con chips, estos se escriben). Es la misma idea que el
        //    `datalist` de la barra de filtros, pero aquí con fuzzy (perdona las faltas).
        var CIUDADES = ['Casma', 'Huarmey', 'Santa', 'Coishco', 'Samanco', 'Nepeña', 'Moro',
                        'Cáceres', 'Nuevo Chimbote', 'Chimbote', 'Lima', 'Trujillo'];

        // Los mismos rótulos de periodo que pinta el PHP en el select de «otro monto».
        var PERIODOS = { mensual: 'al mes', quincenal: 'por quincena', semanal: 'por semana',
                         diario: 'por día', hora: 'por hora' };

        // ---------------------------------------------------------------- utilidades
        function esc(t) {
            return String(t == null ? '' : t)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }

        /** Minúsculas y sin tildes: para comparar, nunca para mostrar. */
        function sinTildes(t) {
            return String(t || '').toLowerCase()
                .replace(/[áàäâã]/g, 'a').replace(/[éèëê]/g, 'e').replace(/[íìïî]/g, 'i')
                .replace(/[óòöô]/g, 'o').replace(/[úùüû]/g, 'u').replace(/ñ/g, 'n');
        }

        /** Un campo escrito por su `name` (los radios y casillas tienen su propio helper). */
        function campo(nombre) {
            var lista = form.querySelectorAll('[name="' + nombre + '"]');
            for (var i = 0; i < lista.length; i++) {
                var t = String(lista[i].type || '').toLowerCase();
                if (t !== 'radio' && t !== 'checkbox') return lista[i];
            }
            return null;
        }
        function radio(nombre) { return form.querySelector('[name="' + nombre + '"]:checked'); }

        /** El texto del chip marcado (sin la nota pequeña) y su `data-resumen` si lo trae. */
        function chipDatos(nombre) {
            var c = radio(nombre);
            if (!c || !c.parentNode) return { texto: '', resumen: '' };
            var lab = c.parentNode;
            var sp  = lab.querySelector('span');
            var txt = '';
            if (sp) {
                txt = (sp.firstChild && sp.firstChild.nodeType === 3) ? sp.firstChild.nodeValue : sp.textContent;
            }
            return { texto: String(txt || '').trim(), resumen: String(lab.getAttribute('data-resumen') || '').trim() };
        }

        /** Índice Fuse sobre objetos {n: "texto"}: así funciona igual en cualquier versión de Fuse. */
        function indiceFuse(lista, umbral) {
            if (!window.Fuse || !lista || !lista.length) return null;
            try {
                return new window.Fuse(lista, {
                    keys: ['n'],
                    threshold: umbral || 0.38,
                    ignoreLocation: true,
                    ignoreAccents: true,
                    minMatchCharLength: 2,
                    includeScore: true
                });
            } catch (e) { return null; }
        }

        // ============================================================ EL RESUMEN VIVO
        /** Lo que la persona lleva armado, tal como lo verá en el aviso. */
        function resumen() {
            var partes = [];
            var t = chipDatos('tipo');
            if (t.resumen || t.texto) partes.push(t.resumen || t.texto);

            var o = chipDatos('oficio_slug');
            if (o.resumen || o.texto) partes.push(o.resumen || o.texto);

            // La zona: el distrito elegido o la ciudad escrita a mano (nunca las dos cosas).
            var ciu = campo('ciudad_txt');
            if (ciu && ciu.value.trim() !== '') {
                partes.push('🌎 ' + ciu.value.trim());
            } else {
                var d = chipDatos('distrito_id');
                if (d.resumen || d.texto) partes.push(d.resumen || d.texto);
            }

            // La jornada: el horario escrito manda sobre el chip (igual que en el servidor).
            var hor = campo('horario_txt');
            if (hor && hor.value.trim() !== '') {
                partes.push('🕗 ' + hor.value.trim());
            } else {
                var j = chipDatos('jornada');
                if (j.resumen || j.texto) partes.push(j.resumen || j.texto);
            }

            var e = chipDatos('edad');   if (e.resumen) partes.push(e.resumen);
            var n = chipDatos('nivel_formativo'); if (n.resumen) partes.push(n.resumen);
            var x = chipDatos('experiencia');     if (x.resumen) partes.push(x.resumen);

            // 💰 El sueldo: texto libre > chip > monto escrito. Sin nada: «A convenir».
            var s = '';
            var libre = campo('sueldo_libre');
            var pre   = radio('sueldo_preset');
            if (libre && libre.value.trim() !== '') {
                s = libre.value.trim();
            } else if (pre && pre.value !== '') {
                s = chipDatos('sueldo_preset').resumen;
            } else {
                var mon = campo('sueldo_monto'), per = campo('sueldo_periodo');
                if (mon && per && per.value && parseFloat(mon.value) > 0) {
                    s = 'S/ ' + mon.value + ' ' + (PERIODOS[per.value] || '');
                }
            }
            partes.push('💰 ' + (s !== '' ? s : 'A convenir'));

            return partes.length > 1 ? partes.join(' · ') : '';
        }

        /** Repinta el resumen y ajusta el rótulo del sueldo. Se llama con cada cambio. */
        function refrescar() {
            var caja = document.getElementById('pubResumen');
            if (caja) {
                var r = resumen();
                caja.innerHTML = r
                    ? esc(r)
                    : '<span class="pub-resumen__vacio">Tu aviso se va armando aquí: lo que lleves, lo verás.</span>';
            }
            rotuloSueldo();
        }

        // ============================================================ PASO A PASO
        var actual = 0;

        /** Escribe «Paso X de 8 · la pregunta» y mueve la barra de progreso. */
        function etiquetaPaso() {
            var txt = document.getElementById('pubPasoTxt');
            var t   = pasos[actual] ? String(pasos[actual].getAttribute('data-titulo') || '') : '';
            if (txt) txt.textContent = 'Paso ' + (actual + 1) + ' de ' + pasos.length + (t ? ' · ' + t : '');
            var barra = document.getElementById('pubBarra');
            if (barra) barra.style.width = Math.round(((actual + 1) / pasos.length) * 100) + '%';
            var prog = document.getElementById('pubProg');
            if (prog) {
                prog.setAttribute('aria-valuenow', String(actual + 1));
                prog.setAttribute('aria-valuetext', 'Paso ' + (actual + 1) + ' de ' + pasos.length);
            }
        }

        /** Los botones de cada paso: en el primero no hay «Atrás» y en el último manda el de publicar. */
        function botonesNav() {
            pasos.forEach(function (p, i) {
                var atras = p.querySelector('[data-pub-atras]');
                if (atras) atras.hidden = (i === 0);
                var sig = p.querySelector('[data-pub-sig]');
                if (sig) sig.hidden = (i === pasos.length - 1);
            });
        }

        function pintarPaso() {
            pasos.forEach(function (p, i) { p.classList.toggle('is-actual', i === actual); });
            botonesNav();
            etiquetaPaso();
            refrescar();
        }

        function ir(n) {
            if (n < 0) n = 0;
            if (n > pasos.length - 1) n = pasos.length - 1;
            actual = n;
            pintarPaso();
            // En celular el paso nuevo tiene que verse: se sube al principio del asistente.
            try { raiz.scrollIntoView({ behavior: 'smooth', block: 'start' }); } catch (e) { raiz.scrollIntoView(); }
            // Si el paso trae un campo escrito, el cursor va ahí (menos tecleo y menos toques).
            var primero = pasos[actual].querySelector('input[type="text"], input[type="search"], input[type="tel"], textarea');
            if (primero && pasos[actual].getAttribute('data-paso') === '7') {
                try { primero.focus({ preventScroll: true }); } catch (e2) { /* da igual */ }
            }
        }

        // ============================================================ EL SUELDO
        /**
         * El rótulo del paso 6 depende de lo que publican: si BUSCAN trabajo, la pregunta es
         * «¿cuánto quieres cobrar?». Es el detalle que pidió el jefe (el mismo formulario, dos casos).
         */
        function rotuloSueldo() {
            var t = radio('tipo');
            var busca = !!(t && t.value === 'busco');
            var h = document.getElementById('pubSueldoT');
            var a = document.getElementById('pubSueldoA');
            var titulo = busca ? '¿Cuánto quieres cobrar?' : '¿Cuánto paga?';
            if (h && h.textContent !== titulo) h.textContent = titulo;
            if (a) {
                a.innerHTML = busca
                    ? 'Pon lo que quieres ganar. Si no eliges nada, tu aviso dirá <b>A convenir</b>: nunca inventamos un monto.'
                    : 'El mínimo legal del Perú es <b>S/ 1 250 al mes</b>: por eso esa es la primera opción (y las de la semana, la quincena, el día y la hora salen de ahí).';
            }
            var p6 = null;
            pasos.forEach(function (p) { if (p.getAttribute('data-paso') === '6') p6 = p; });
            if (p6) p6.setAttribute('data-titulo', titulo);
            etiquetaPaso();
        }

        /** La caja de «Otro monto o texto» solo se ve cuando la eligen (o cuando ya hay un monto). */
        function pintarOtroMonto() {
            var caja = document.getElementById('pubOtroMonto');
            if (!caja) return;
            var chip = form.querySelector('.pub-chip[data-otro] input');
            var libre = campo('sueldo_libre'), mon = campo('sueldo_monto');
            var usado = (chip && chip.checked)
                || (libre && libre.value.trim() !== '')
                || (mon && parseFloat(mon.value) > 0);
            caja.classList.toggle('is-visible', !!usado);
        }

        // ============================================================ LA CASILLA Y EL BOTÓN
        /** Sin la casilla marcada el botón de publicar está APAGADO (el servidor la exige igual). */
        function actualizarEnviar() {
            var chk = form.querySelector('[name="declaro"][type="checkbox"]');
            var btn = document.getElementById('pubEnviar');
            var nota = document.getElementById('pubEnviarNota');
            if (!chk || !btn) return;
            btn.disabled = !chk.checked;
            btn.setAttribute('aria-disabled', chk.checked ? 'false' : 'true');
            if (nota) nota.hidden = !!chk.checked;
        }

        // ============================================================ FUZZY: OFICIOS
        var chipsOficio = Array.prototype.slice.call(cajas.querySelectorAll('.pub-chips--oficio .pub-chip'));
        // ⚠️ Los índices de Fuse se arman en `init()`, NO aquí: este script corre mientras el
        //    navegador todavía está leyendo la página y `fuse.min.js` va con `defer` (se ejecuta
        //    después). Si se armaran en este punto, `window.Fuse` no existiría y el fuzzy se
        //    quedaría con el plan B (coincidencia simple) para siempre.
        var idxOficios  = null;
        var idxCiudades = null;

        function construirIndices() {
            idxOficios  = indiceFuse(chipsOficio.map(function (l, i) {
                return { n: String(l.getAttribute('data-busca') || ''), i: i };
            }), 0.34);
            idxCiudades = indiceFuse(CIUDADES.map(function (c) { return { n: c }; }), 0.4);
        }

        /** Filtra los chips de oficio mientras se escribe (y nunca esconde el que ya está marcado). */
        function filtrarOficios(q) {
            var t = sinTildes(q).trim();
            var vistos = null;
            if (t.length >= 2) {
                if (idxOficios) {
                    vistos = {};
                    idxOficios.search(q, { limit: 12 }).forEach(function (r) { vistos[r.item.i] = 1; });
                } else {
                    // Plan B sin Fuse.js: coincidencia simple (mejor eso que nada).
                    vistos = {};
                    chipsOficio.forEach(function (l, i) {
                        if (sinTildes(l.getAttribute('data-busca') || '').indexOf(t) !== -1) vistos[i] = 1;
                    });
                }
            }
            chipsOficio.forEach(function (l, i) {
                var inp = l.querySelector('input');
                var marcado = !!(inp && inp.checked);
                var ocultar = (vistos !== null) && !vistos[i] && !marcado;
                l.classList.toggle('is-oculto', ocultar);
            });
            // El aviso de «ningún oficio se llama así» sale cuando la BÚSQUEDA no encontró nada
            // (no cuando lo único que queda a la vista es el chip que ya estaba marcado: ese se
            // respeta siempre, para no perder la elección que la persona ya había hecho).
            var aviso = document.getElementById('pubOficioVacio');
            if (aviso) aviso.hidden = !(vistos !== null && Object.keys(vistos).length === 0);
        }

        // ============================================================ FUZZY: CIUDADES
        function sugerirCiudades() {
            var inp  = campo('ciudad_txt');
            var caja = cajas.querySelector('[data-pub-sug="ciudad"]');
            if (!inp || !caja) return;
            var q = inp.value.trim();
            if (sinTildes(q).length < 2) { caja.innerHTML = ''; return; }
            var res = [];
            if (idxCiudades) {
                idxCiudades.search(q, { limit: 4 }).forEach(function (r) {
                    if (sinTildes(r.item.n) !== sinTildes(q)) res.push(r.item.n);
                });
            } else {
                CIUDADES.forEach(function (c) {
                    if (sinTildes(c).indexOf(sinTildes(q)) !== -1) res.push(c);
                });
            }
            var h = '';
            res.slice(0, 4).forEach(function (c) {
                h += '<button type="button" class="pub-sug__item" data-pub-sug-dest="ciudad_txt" data-pub-sug-val="'
                   + esc(c) + '">📍 ' + esc(c) + '</button>';
            });
            caja.innerHTML = h;
        }

        // ============================================================ FUZZY: TÍTULOS
        // Los títulos que YA existen salen de `api/empleos_json.php` (JSON cacheado 1 hora, clave
        // `t`): así quien publica «ayudante de cociina» ve «Ayudante de cocina» y lo toca en vez de
        // escribirlo. Se pide UNA vez, cuando de verdad hace falta (al escribir en el título).
        var idxTitulos = null, pidiendoTitulos = false;

        function cargarTitulos(cuandoListo) {
            if (idxTitulos) { cuandoListo(); return; }
            if (pidiendoTitulos) { return; }
            pidiendoTitulos = true;
            var endpoint = String(window.SITE_URL || '').replace(/\/+$/, '') + '/api/empleos_json.php';
            try {
                fetch(endpoint, { credentials: 'same-origin' })
                    .then(function (r) { return r.ok ? r.json() : []; })
                    .then(function (avisos) {
                        pidiendoTitulos = false;
                        if (!window.Fuse || !avisos || !avisos.length) return;
                        var lista = [], vistos = {};
                        avisos.forEach(function (a) {
                            var t = String(a && a.t ? a.t : '').trim();
                            if (t === '' || vistos[sinTildes(t)]) return;
                            vistos[sinTildes(t)] = 1;
                            lista.push({ n: t });
                        });
                        idxTitulos = indiceFuse(lista, 0.4);
                        cuandoListo();
                        // Si mientras bajaba el JSON la persona siguió escribiendo, se le vuelve a
                        // ofrecer: sin esto, esas letras se quedaban sin sugerencias para siempre.
                        sugerirTitulos();
                    })
                    .catch(function () { pidiendoTitulos = false; });   // sin JSON, se escribe y ya
            } catch (e) { pidiendoTitulos = false; }
        }

        function sugerirTitulos() {
            var inp  = campo('titulo');
            var caja = cajas.querySelector('[data-pub-sug="titulo"]');
            if (!inp || !caja) return;
            var q = inp.value.trim();
            if (sinTildes(q).length < 3) { caja.innerHTML = ''; return; }
            cargarTitulos(function () {
                if (!idxTitulos) return;
                var h = '';
                idxTitulos.search(q, { limit: 5 }).forEach(function (r) {
                    var t = r.item.n;
                    if (sinTildes(t) === sinTildes(q)) return;          // ya lo escribió igual
                    h += '<button type="button" class="pub-sug__item" data-pub-sug-dest="titulo" data-pub-sug-val="'
                       + esc(t) + '">📋 ' + esc(t) + '</button>';
                });
                caja.innerHTML = h ? ('<span class="pub-sug__lbl">Ya publicados:</span>' + h) : '';
            });
        }

        // ============================================================ VALIDACIÓN BLANDA
        // NO bloquea pasos hacia adelante (se puede saltar y volver). Solo se usa al ENVIAR, para
        // llevar a la persona al paso donde falta algo en vez de dejarla con un error del servidor.
        function telOk() {
            var t = campo('telefono'), w = campo('whatsapp');
            var re = /^9\d{8}$/;
            var tv = t ? String(t.value).replace(/\D+/g, '') : '';
            var wv = w ? String(w.value).replace(/\D+/g, '') : '';
            return re.test(tv) || re.test(wv);
        }

        function primerPasoIncompleto() {
            if (!radio('tipo')) return 0;
            if (!radio('oficio_slug')) return 1;
            var t = campo('titulo');
            if (!t || t.value.trim().length < 6) return 6;
            var d = campo('descripcion');
            if (!d || d.value.trim().length < 20) return 6;
            if (!telOk()) return 6;
            var chk = form.querySelector('[name="declaro"][type="checkbox"]');
            if (!chk || !chk.checked) return 7;
            return -1;
        }

        /** Marca en rojo la caja del campo que falta (y lo quita en cuanto se corrige). */
        function marcar(campoEl, mal) {
            if (!campoEl || !campoEl.closest) return;
            var caja = campoEl.closest('.pub-campo');
            if (caja) caja.classList.toggle('is-error', !!mal);
            campoEl.setAttribute('aria-invalid', mal ? 'true' : 'false');
        }

        /** Avance por CLIC en cualquier parte del asistente (delegado: vale para los 8 pasos). */
        form.addEventListener('click', function (ev) {
            var el = ev.target;
            if (!el || !el.closest) return;

            if (el.closest('[data-pub-sig]')) { ev.preventDefault(); ir(actual + 1); return; }
            if (el.closest('[data-pub-atras]')) { ev.preventDefault(); ir(actual - 1); return; }

            // Sugerencias del fuzzy (ciudad o título): se rellena el campo y se limpia la lista.
            var sug = el.closest('[data-pub-sug-dest]');
            if (sug) {
                ev.preventDefault();
                var destino = campo(sug.getAttribute('data-pub-sug-dest'));
                if (destino) {
                    destino.value = sug.getAttribute('data-pub-sug-val') || '';
                    marcar(destino, false);
                    refrescar();
                }
                var cajaSug = sug.closest('.pub-sug');
                if (cajaSug) cajaSug.innerHTML = '';
                return;
            }

            // Atajos: «🙋 Soy particular» y «📱 El mismo número» (cero tecleo).
            var rell = el.closest('[data-pub-rellena]');
            if (rell) {
                ev.preventDefault();
                var destino2 = campo(rell.getAttribute('data-pub-rellena'));
                var de       = rell.getAttribute('data-pub-valor-de');
                if (destino2) {
                    if (de) {
                        var origen = campo(de);
                        destino2.value = origen ? String(origen.value).replace(/\D+/g, '') : '';
                    } else {
                        destino2.value = rell.getAttribute('data-pub-valor') || '';
                    }
                    destino2.dispatchEvent(new Event('input', { bubbles: true }));
                    marcar(destino2, false);
                    refrescar();
                }
            }
        });

        // ----------------------------------------------------- cambios y escritura
        form.addEventListener('change', function (ev) {
            var el = ev.target || {};
            var n  = String(el.name || '');
            if (n === 'declaro') actualizarEnviar();
            if (n === 'sueldo_preset') {
                // Si eligen un chip de la lista, se limpia el «otro monto» (no pueden competir).
                var chip = el.closest ? el.closest('.pub-chip') : null;
                if (chip && !chip.getAttribute('data-otro')) {
                    var mon = campo('sueldo_monto'), per = campo('sueldo_periodo'), libre = campo('sueldo_libre');
                    if (mon) mon.value = '';
                    if (per) per.value = '';
                    if (libre) libre.value = '';
                }
                pintarOtroMonto();
            }
            if (el.closest) marcar(el, false);
            refrescar();
        });

        form.addEventListener('input', function (ev) {
            var el = ev.target || {};
            var n  = String(el.name || '');
            var id = String(el.id || '');

            if (id === 'pubFiltroOficio') filtrarOficios(el.value);
            if (n === 'ciudad_txt')       sugerirCiudades();
            if (n === 'titulo')           sugerirTitulos();
            if (n === 'sueldo_monto' || n === 'sueldo_libre') {
                // Escribir a mano apaga el chip elegido: el monto «de verdad» es el que escriben.
                var marcado = radio('sueldo_preset');
                if (marcado && marcado.value !== '') {
                    var otro = form.querySelector('.pub-chip[data-otro] input');
                    if (otro) otro.checked = true;
                }
                pintarOtroMonto();
            }
            marcar(el, false);
            refrescar();
        });

        // ------------------------------------------------------------- el envío
        /**
         * ⚠️ Con el botón de publicar APAGADO (hasta marcar la casilla), el navegador bloquea el
         * envío implícito: quien escriba el teléfono y pulse Enter no vería pasar NADA. Por eso,
         * en los pasos intermedios, Enter avanza al paso siguiente (que es lo que la persona
         * quiere). En el último paso no se toca: ahí Enter publica, como siempre.
         */
        form.addEventListener('keydown', function (ev) {
            if (ev.key !== 'Enter' || ev.shiftKey) return;
            var el = ev.target || {};
            var tipo = String(el.type || '').toLowerCase();
            if (tipo === 'textarea' || tipo === 'submit' || tipo === 'button') return;   // ahí Enter es suyo
            if (actual < pasos.length - 1) { ev.preventDefault(); ir(actual + 1); }
        });

        form.addEventListener('submit', function (ev) {
            // La validación de verdad es la del servidor (longitudes, teléfono, antispam, CSRF).
            // Aquí solo se evita mandar el aviso a medias y se lleva al paso que falta.
            var falta = primerPasoIncompleto();
            if (falta >= 0) {
                ev.preventDefault();
                ir(falta);
                var foco = null;
                if (falta === 6) {
                    var t = campo('titulo'), d = campo('descripcion');
                    if (!t || t.value.trim().length < 6) foco = t;
                    else if (!d || d.value.trim().length < 20) foco = d;
                    else foco = campo('telefono');
                }
                if (foco) { marcar(foco, true); try { foco.focus({ preventScroll: true }); } catch (e) { /* da igual */ } }
                var caja = document.getElementById('pubResumen');
                if (caja) {
                    caja.innerHTML = '<span class="pub-resumen__vacio">Falta un dato: te llevé al paso con lo que falta. 👆</span>';
                }
            }
        });

        // ============================================================ ARRANQUE
        function init() {
            // 1) Los `required`/`minlength` nativos se quitan porque los pasos ocultos los rompen
            //    (ver la TRAMPA explicada arriba). La casilla también: su sitio es el botón apagado.
            //    ⚠️ Se hace SIEMPRE, antes de cualquier `return`: si quedara un `required` dentro de
            //    un paso escondido, el navegador bloquearía el envío sin que nadie vea el motivo.
            Array.prototype.forEach.call(form.querySelectorAll('[data-pub-quitar-nativo]'), function (c) {
                c.removeAttribute('required');
                c.removeAttribute('minlength');
            });

            // 1b) Los índices del fuzzy (Fuse.js ya está cargado cuando esto corre: es `defer`).
            construirIndices();

            // 2) ¿Volvemos de un error del servidor? Entonces se busca el paso donde falta algo.
            var conError = raiz.getAttribute('data-pub-error') === '1';
            var falta    = conError ? primerPasoIncompleto() : -1;

            if (conError && falta < 0) {
                // El servidor rechazó el aviso pero NO falta ningún dato (p. ej. el antispam de «ya
                // publicaste hoy»). Aquí no se esconde NADA: se ven los 8 pasos con lo que la
                // persona escribió, para que lea el motivo (los avisos salen justo arriba).
                pasos.forEach(function (p) { p.classList.add('is-actual'); });
                botonesNav();
                var txt = document.getElementById('pubPasoTxt');
                if (txt) txt.textContent = 'Revisa tu aviso: aquí está todo lo que llevas';
                pintarOtroMonto();
                actualizarEnviar();
                refrescar();
                return;
            }

            // 3) El asistente está VIVO: recién AHORA se pueden esconder los pasos que no tocan
            //    (nunca antes: en el HTML no hay nada escondido, y sin JS se ven los 8 y se envía).
            raiz.classList.add('pub--vivo');

            actual = (falta > 0) ? falta : 0;
            pintarPaso();
            pintarOtroMonto();
            actualizarEnviar();
            refrescar();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
    </script>
</section>

<!-- ======================= PIE: el que buscaba otra cosa ======================= -->
<p class="empleos-pie">
    ¿Buscabas una tienda, un producto o un servicio? Está en el
    <a href="<?= e(url('buscar.php')) ?>">directorio de tiendas de Chimbote</a>.
</p>

<?php include __DIR__ . '/includes/footer.php'; ?>
