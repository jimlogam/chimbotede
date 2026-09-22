# GUÍA 2 — SUBIR LAS IMÁGENES AL HOSTING Y DEJARLAS ASIGNADAS A SU PRODUCTO
### dechimbote.com · para la IA nueva que recibe guías · 2026-09-22

> **Lee esta guía después de la GUÍA 1** (la de CREAR las imágenes). Aquí está **el acceso al hosting, la
> trampa del FTP, cómo se publica una foto de producto y cómo se limpia**.
>
> **Tu encargo en esta parte:** cada imagen que creaste (ya designada con el número de su producto) tiene que
> **quedar puesta en el sitio**, en su producto, con sus versiones y con el «deshacer» de 24 horas. Y después
> **limpiar** lo que se usó.

---

## 1) ACCESO AL HOSTING (FTP)

> 🔑 **Estos datos son del dueño del sitio y son para que tú subas los archivos.** No los compartas ni los
> publiques en ningún sitio.

| Dato | Valor |
|---|---|
| **Host** | `ftp.dechimbote.com` |
| **Puerto** | `21` |
| **Usuario** | `u196269909.dechimboteftp` |
| **Contraseña** | `PON_AQUI_LA_CLAVE_DEL_FTP` |
| **Protocolo** | FTP |
| **Carpeta inicial** | `public_html` ⚠️ **NO ES LA WEB** |
| **RAÍZ VIVA (donde SÍ se escribe)** | **`/`** |
| **Web** | `https://dechimbote.com` |

---

## 2) 🔴 LA TRAMPA DEL FTP (esto hace perder horas si no se sabe)

Al conectarte, el FTP **te deja dentro de `/public_html`**, y esa carpeta **NO es la web**: es una **copia
vieja y completa del sitio, anidada dentro de la raíz viva**. Si subes ahí, **no da ningún error** (los bytes
y el md5 quedan idénticos) pero **la web nunca cambia**.

✅ **Antes de escribir nada, siempre:**

1. **`cwd('/')`** → ponerse en la **raíz viva**.
2. Comprobar que estás en el sitio correcto: **debe existir el archivo `assets/css/carrito.css`**.
   Si no existe, **no estás en la raíz viva: no subas nada**.
3. Recién entonces se escribe.

⛔ **Nunca se sube nada a `/public_html`.** Las sondas PHP también van a **`/`**.

---

## 3) QUÉ PASA «POR DENTRO» CUANDO SE PONE LA FOTO DE UN PRODUCTO

Una foto de producto **no es un solo archivo**: en el sitio se guarda en **dos sitios** que hay que dejar
coherentes, y el motor del sitio es el que hace todo (el mismo motor que usa el editor cuando el dueño
arrastra una foto). **Por eso no se toca la base de datos a mano.**

| Pieza | Dónde vive |
|---|---|
| **Los productos** | tabla **`directorio_servicios`** (`id`, `negocio_id`, `titulo`, `descripcion`, `precio`, `unidad`, `imagen`, `activo`) |
| **Las tiendas** | tabla **`directorio_negocios`** (`id`, `slug`, `nombre`, `categoria_id`) |
| **Los rubros** | tabla **`directorio_categorias`** (`id`, `nombre`) |
| **La galería del producto** | tabla **`directorio_producto_fotos`** (`producto_id`, `ruta`, `orden`) |
| **La portada del producto** | columna **`directorio_servicios.imagen`** (es la que manda en todo el sitio) |
| **El «deshacer» de 24 h** | tabla **`directorio_producto_imagenes_ant`** |
| **Los archivos de imagen** | **`fotos/<slug-de-la-tienda>/ia_<AAAAMMDD_HHMMSS>_<aleatorio>.webp`** |
| **La conversión** | el motor convierte a **WebP** (máx 1600 px) **y genera las versiones** (800, 300 y las menores que apliquen) para el móvil y el escritorio |

**Qué hace el motor, en orden:** guarda la imagen con su nombre nuevo → **reemplaza la 1.ª fila** de la
galería del producto (o la crea si no había) → **actualiza la portada** (`directorio_servicios.imagen`) →
**guarda la foto anterior** para el deshacer de 24 h (su archivo no se borra todavía).

⚠️ **No hay que crear archivos de versión a mano**: los crea el motor. Y **no hay que borrar la foto
anterior**: de eso se encarga el motor cuando ya nadie la usa.

---

## 4) LOS DOS CAMINOS PARA PUBLICAR

### 🅰️ CAMINO A — DEJAR LAS IMÁGENES PARA QUE LAS PUBLIQUE EL AGENTE DEL SITIO (el más seguro)

Tú dejas las imágenes **creadas y designadas** (GUÍA 1 §5) en la zona de trabajo del proyecto —
**`C:\Users\Usuario\Downloads` (Descargas)** — con su nombre `producto-<titulo-corto>-<ID>.png`, y avisas.
**El agente del sitio** las publica con su publicador (que hace WebP + versiones + galería + portada +
deshacer) con estos comandos:

```powershell
python __pub_productos.py listar     # solo lista: qué llegó y qué falta
python __pub_productos.py probar     # 1 sola imagen, para validar el camino
python __pub_productos.py p1a        # publica el bloque de 10 de ese modo
python __pub_productos.py limpiar    # borra de Descargas SOLO lo publicado (nunca por comodín)
```

**Ventaja:** cero riesgo para el sitio. **Cuándo se usa:** siempre, salvo que el dueño te pida subir tú misma.

### 🅱️ CAMINO B — TÚ SUBES POR FTP Y PUBLICAS CON LA SONDA (tú ya tienes el FTP)

Una **sonda** es un **archivo PHP temporal** que se sube a la **raíz viva**, se usa **y se borra en el mismo
paso**. Son **4 pasos**.

#### PASO 1 — Subir la sonda `__pp_ia.php` a la RAÍZ VIVA (`/`)

Su contenido es **exactamente** este (no se le cambia nada):

```php
<?php
/**
 * __pp_ia.php — SONDA TEMPORAL: se sube a la RAÍZ VIVA '/', se usa y se BORRA del servidor en el mismo paso.
 * Publica la IMAGEN de varios PRODUCTOS haciendo lo mismo que el editor de productos del sitio
 * (editaproductos.php), pero sin sesión de admin y sin CSRF, con clave.
 *
 * Uso: POST multipart a https://dechimbote.com/__pp_ia.php?key=PON_AQUI_LA_CLAVE_DE_LAS_SONDAS
 *      archivo_0 = <imagen>   id_0 = 205
 *      archivo_1 = <imagen>   id_1 = 13   … (hasta 20 pares por llamada)
 * Devuelve JSON: { ok, cuantas, publicadas, resultados:[{producto_id, ok, aviso|error, ruta, anterior,
 * peso_kb, url}] }
 *
 * Requisitos en el servidor (ya existen, no hay que crear nada): config.php y las funciones del motor
 * (img_guardar_subida, img_borrar, img_url).
 */
ini_set('display_errors', '0');
header('Content-Type: application/json; charset=utf-8');

define('CLAVE_SONDA', 'PON_AQUI_LA_CLAVE_DE_LAS_SONDAS');
if (($_GET['key'] ?? '') !== CLAVE_SONDA) { http_response_code(403); echo '{"ok":false,"error":"clave"}'; exit; }
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { echo '{"ok":false,"error":"solo POST"}'; exit; }

require_once __DIR__ . '/config.php';
$pdo = db();
$TABLA_ANT = 'directorio_producto_imagenes_ant';

/* ¿cuántos registros MÁS usan esta imagen? */
function ia_referencias(PDO $pdo, string $ruta, int $excluir_producto = 0): int {
    global $TABLA_ANT;
    $ruta = trim($ruta);
    if ($ruta === '') return 0;
    $n = 0;
    $consultas = [
        ["SELECT COUNT(*) FROM directorio_servicios WHERE imagen = ? AND id <> ?", [$ruta, $excluir_producto]],
        ["SELECT COUNT(*) FROM directorio_producto_fotos WHERE ruta = ?", [$ruta]],
        ["SELECT COUNT(*) FROM directorio_fotos WHERE ruta = ?", [$ruta]],
        ["SELECT COUNT(*) FROM directorio_banners WHERE imagen = ?", [$ruta]],
        ["SELECT COUNT(*) FROM {$TABLA_ANT} WHERE ruta = ? AND producto_id <> ?", [$ruta, $excluir_producto]],
    ];
    foreach ($consultas as $c) {
        try { $s = $pdo->prepare($c[0]); $s->execute($c[1]); $n += (int)$s->fetchColumn(); }
        catch (Throwable $e) { /* si esa tabla no existe, no se cuenta */ }
    }
    return $n;
}

/* guarda la anterior para el deshacer de 24 h */
function ia_guardar_undo(PDO $pdo, int $pid, string $ruta_vieja, string $ruta_nueva): void {
    global $TABLA_ANT;
    if ($ruta_vieja === '' || $ruta_vieja === $ruta_nueva) return;
    $previa = '';
    try {
        $s = $pdo->prepare("SELECT ruta FROM {$TABLA_ANT} WHERE producto_id = ?");
        $s->execute([$pid]);
        $previa = (string)$s->fetchColumn();
    } catch (Throwable $e) { return; }
    $pdo->prepare("INSERT INTO {$TABLA_ANT} (producto_id, ruta, ruta_nueva, creado_en)
                   VALUES (?, ?, ?, NOW())
                   ON DUPLICATE KEY UPDATE ruta = VALUES(ruta), ruta_nueva = VALUES(ruta_nueva), creado_en = NOW()")
        ->execute([$pid, $ruta_vieja, $ruta_nueva]);
    if ($previa !== '' && $previa !== $ruta_vieja && ia_referencias($pdo, $previa) === 0) {
        @img_borrar($previa);
    }
}

/* publicar la imagen de un producto */
function ia_publicar(PDO $pdo, int $pid, array $archivo): array {
    $s = $pdo->prepare("SELECT s.id, s.titulo, s.imagen, s.negocio_id,
                               n.slug AS negocio_slug, n.nombre AS negocio, c.nombre AS rubro
                          FROM directorio_servicios s
                          LEFT JOIN directorio_negocios n ON n.id = s.negocio_id
                          LEFT JOIN directorio_categorias c ON c.id = n.categoria_id
                         WHERE s.id = ? LIMIT 1");
    $s->execute([$pid]);
    $prod = $s->fetch();
    if (!$prod) return ['ok' => false, 'error' => 'Ese producto ya no existe.'];
    if (empty($archivo['name'])) return ['ok' => false, 'error' => 'No llegó ninguna imagen.'];

    $slug    = preg_replace('/[^a-z0-9_\-]/i', '', (string)($prod['negocio_slug'] ?? ''));
    $carpeta = 'fotos/' . ($slug !== '' ? $slug : 'ia');
    $nombre  = 'ia_' . date('Ymd_His') . '_' . bin2hex(random_bytes(3));

    $res = img_guardar_subida($archivo, $carpeta, $nombre);
    if (empty($res['ok'])) {
        return ['ok' => false, 'error' => 'No se pudo guardar la imagen: ' . ($res['error'] ?? 'error desconocido')];
    }
    $nueva = (string)$res['rel'];
    $vieja = (string)($prod['imagen'] ?? '');

    // a) galería del producto: se reemplaza la 1.ª foto (o se crea)
    $g = $pdo->prepare("SELECT id FROM directorio_producto_fotos WHERE producto_id = ?
                         ORDER BY orden ASC, id ASC LIMIT 1");
    $g->execute([$pid]);
    $foto_id = (int)$g->fetchColumn();
    if ($foto_id > 0) {
        $pdo->prepare("UPDATE directorio_producto_fotos SET ruta = ? WHERE id = ?")->execute([$nueva, $foto_id]);
    } else {
        $pdo->prepare("INSERT INTO directorio_producto_fotos (producto_id, ruta, orden) VALUES (?, ?, 0)")
            ->execute([$pid, $nueva]);
    }

    // b) portada (es la que manda en todo el sitio)
    $pdo->prepare("UPDATE directorio_servicios SET imagen = ? WHERE id = ?")->execute([$nueva, $pid]);

    // c) deshacer: se guarda la anterior (su archivo NO se borra todavía)
    if ($vieja !== '' && $vieja !== $nueva) ia_guardar_undo($pdo, $pid, $vieja, $nueva);

    $ancho = (int)($res['ancho'] ?? 0);
    $alto  = (int)($res['alto'] ?? 0);
    $peso  = (int)($res['peso'] ?? 0);

    return [
        'ok'          => true,
        'producto_id' => $pid,
        'titulo'      => (string)$prod['titulo'],
        'tienda'      => (string)$prod['negocio'],
        'negocio_id'  => (int)$prod['negocio_id'],
        'rubro'       => (string)($prod['rubro'] ?? ''),
        'ruta'        => $nueva,
        'anterior'    => $vieja,
        'peso_kb'     => (int)round($peso / 1024),
        'ancho'       => $ancho,
        'alto'        => $alto,
        'variantes'   => array_values((array)($res['variantes'] ?? [])),
        'url'         => function_exists('img_url') ? img_url($nueva) : null,
        'aviso'       => ($vieja === '' ? 'Imagen publicada' : 'Imagen cambiada')
                         . ' para «' . (string)$prod['titulo'] . '»'
                         . ' · ' . (int)round($peso / 1024) . ' KB en WebP'
                         . ($ancho > 0 ? ' (' . $ancho . '×' . $alto . ' px)' : ''),
    ];
}

/* recorrer los pares archivo_N / id_N */
$resultados = [];
$log = [];
for ($i = 0; $i < 20; $i++) {
    if (!isset($_FILES['archivo_' . $i])) continue;
    $pid = (int)($_POST['id_' . $i] ?? 0);
    if ($pid <= 0) { $resultados[] = ['ok' => false, 'error' => 'id inválido en el par ' . $i]; continue; }
    try {
        $r = ia_publicar($pdo, $pid, $_FILES['archivo_' . $i]);
    } catch (Throwable $e) {
        $r = ['ok' => false, 'producto_id' => $pid, 'error' => get_class($e) . ': ' . $e->getMessage()];
    }
    $resultados[] = $r;
    $log[] = date('c') . "\t" . $pid . "\t" . (($r['ok'] ?? false)
        ? ('OK ' . ($r['ruta'] ?? '') . ' anterior=' . ($r['anterior'] ?? ''))
        : ('ERROR ' . ($r['error'] ?? '')));
}

@file_put_contents(__DIR__ . '/__pp_ia.log', implode("\n", $log) . "\n", FILE_APPEND);

echo json_encode([
    'ok' => true,
    'cuantas' => count($resultados),
    'publicadas' => count(array_filter($resultados, fn($r) => !empty($r['ok']))),
    'resultados' => $resultados,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
```

#### PASO 2 — La PRUEBA con UN solo producto (siempre se prueba antes)

```bash
curl -X POST "https://dechimbote.com/__pp_ia.php?key=PON_AQUI_LA_CLAVE_DE_LAS_SONDAS" \
  -F "archivo_0=@producto-paquete-de-productos-de-panaderia-12-piezas-205.png" \
  -F "id_0=205"
```

**Cómo se lee la respuesta:** si trae `"publicadas": 1` y una `"ruta": "fotos/<slug>/ia_….webp"` → **quedó
puesta**. Si trae `"ok": false`, el mensaje dice qué pasó (`Ese producto ya no existe.`, `No llegó ninguna
imagen.`…).

#### PASO 3 — Los bloques

**Máximo 20 pares por llamada**: `archivo_0` … `archivo_19` con su **`id_0` … `id_19`**. El `id` es el número
del producto (el mismo que va impreso en la imagen y al final del nombre del archivo).

#### PASO 4 — LIMPIAR (obligatorio, en el mismo paso)

1. **Borrar la sonda y su log** del servidor: **`__pp_ia.php`** y **`__pp_ia.log`**, y **comprobar que la URL
   ya da 404**.
2. **Borrar de la zona de trabajo las imágenes ya publicadas** (las del manifiesto). ⛔ **Nunca con comodín**
   (`producto-*`): se borra **solo lo que está en el manifiesto**. Si hay dudas, se listan primero.

> ✅ **El «deshacer» de 24 horas ya lo deja el motor**: si algo sale mal, la foto anterior se puede recuperar
> durante ese día. No hay que hacer nada para eso.

---

## 5) PROHIBIDO SIEMPRE

1. **No tocar la base de datos a mano** (nada de phpMyAdmin ni de SQL suelto): se publica con el motor.
2. **No entrar como administrador** al sitio (eso saca al dueño de su sesión).
3. **No subir nada a `/public_html`** (es la copia vieja: la web no cambia).
4. **No borrar ni renombrar** archivos que no sean tuyos, ni abrir carpetas ajenas de la zona de trabajo.
5. **No verificar** el trabajo ya publicado ajeno: publicar **cierra** el asunto.
6. **No inventar** nombres ni códigos: el ID sale del censo, no de la imaginación.
7. **No dejar la sonda en el servidor**: se borra en el mismo paso, con su log.

---

## 6) LO QUE DEVUELVES AL CERRAR CADA BLOQUE

En **mensajes cortos**, sin dar vueltas:

1. **Cuántas publicaste**: «bloque 1: 10 de 10».
2. **El manifiesto**: `N.º · archivo · producto (título + id) · qué se ve · dudas`.
3. **La ruta publicada** de cada una (`fotos/<slug-de-la-tienda>/ia_….webp`) y su **URL** si la respuesta la
   trae.
4. **Los enlaces de las fichas** de las tiendas: `https://dechimbote.com/negocio/<slug>`.
5. **Lo que quedó pendiente o dudoso**, una línea por caso.
6. **La confirmación de la limpieza**: sonda borrada (404 comprobado) y sin imágenes usadas en la zona de
   trabajo.

---

## 7) CHECKLIST DE BOLSILLO (para no saltarse nada)

```text
[ ] 1. cwd('/')  y compruebo que EXISTE el archivo assets/css/carrito.css  → si no existe, no estoy en la raíz viva: no subo nada
[ ] 2. La imagen tiene impreso el NÚMERO del producto (blanco, chico, cifras simples, en una esquina)
[ ] 3. El archivo se llama producto-<titulo-corto>-<ID>.png   (el ID manda)
[ ] 4. Es 1:1 (1024x1024 o más) y el objeto se ve de cerca, limpio y elegante
[ ] 5. Prueba con UN producto → leo el JSON → "publicadas": 1
[ ] 6. El bloque completo (máx 20 pares por llamada)
[ ] 7. BORRO la sonda __pp_ia.php y su log, y compruebo el 404
[ ] 8. BORRO de la zona de trabajo solo lo publicado (nunca con comodín)
[ ] 9. Reporto el manifiesto y lo pendiente
```

---

## 8) RESUMEN EN 6 LÍNEAS

1. **FTP:** `ftp.dechimbote.com` · puerto `21` · `u196269909.dechimboteftp` · `PON_AQUI_LA_CLAVE_DEL_FTP`.
2. **La raíz viva es `/`** (nunca `public_html`, que es una copia vieja anidada): se comprueba con
   `assets/css/carrito.css`.
3. **No se toca la base de datos a mano** y **no se entra como admin**: publica **el motor** del sitio.
4. **Camino A:** las imágenes se dejan designadas en la zona de trabajo y las publica el agente del sitio.
5. **Camino B:** subo la sonda **`__pp_ia.php`** a **`/`**, publico por HTTP con `archivo_N`/`id_N`
   (máx 20), y **borro la sonda y su log** en el mismo paso.
6. Se cierra con **el manifiesto** y la **limpieza confirmada**.

*Fin de la GUÍA 2.*
