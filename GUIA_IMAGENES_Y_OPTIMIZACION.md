> 🔴 **REGLA INVIOLABLE N.º 0 — PREGUNTAR SIEMPRE, CADA COSA (orden del jefe, 2026-09-21, textual):**
> *«Tú nunca debes mandarte solo. Tú debes aburrirme así, haciéndome preguntas por cada cosa. Solo cuando yo te
> diga “tienes el control” no me hagas preguntas; caso contrario, SIEMPRE me preguntas cada cosa. No me gusta
> que hagas las cosas sin preguntarme.»*
>
> → **Por defecto: se pregunta ANTES de cada cosa, una por una** (no una vez por lote). No se decide, no se
> borra, no se sube, no se cambia nada sin su visto bueno.
> → **La única excepción:** cuando el jefe dice **«tienes el control»** — solo entonces se trabaja sin preguntar.
> → Preguntar **no gasta**: lo que gasta es equivocarse y rehacer. Y **no se hacen mensajes largos**: se pregunta
> corto, por cada cosa.


# GUÍA — IMÁGENES: SUBIRLAS OPTIMIZADAS Y SERVIRLAS EN EL TAMAÑO JUSTO · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** SIEMPRE que se toque **cualquier cosa que suba o muestre una
> imagen**: Caminante, `productos.php`, `crear_negocio.php`, banners, la ficha, el buscador,
> los carruseles o las tarjetas del index.
> **Motor:** `deploy/includes/imagenes.php` · **JS:** `assets/js/imagen_optimizar.js` (subir) y
> `assets/js/imagen_zoom.js` (abrir la galería) · **Lote:** `__img_lote2.php` + `__img_lote2_run.py` ·
> **Prueba del motor:** `C:\xampp\php\php.exe -d extension=gd __img_prueba_motor.php` (43 comprobaciones) ·
> **Estado:** ✅ EN PRODUCCIÓN · **Última revisión: 2026-09-16** (auditoría del peso en celular: **§11**)

---

## 0) LO ESENCIAL EN 30 SEGUNDOS

0. ⛔ **REGLA INVIOLABLE (jefe, 2026-09-12): UNA CAPTURA DE PANTALLA NUNCA SE SUBE AL SITIO.**
   Ni como foto de producto, ni como carátula, ni como "imagen temporal": la captura muestra
   **sus pestañas, marcadores y URLs** = información sensible. La captura **solo se mira para leer un
   dato** (nombre/teléfono del anuncio) y se borra. **Sin foto del negocio → la ficha no se publica**
   (se le pide una foto al jefe). Vale para capturas de PC (p. ej. 1366×599 con la barra del navegador),
   de **celular** (barra de estado: hora/batería/señal, o el visor de fotos), **historias de Instagram**
   (stickers de ubicación/mención) y **videos con la interfaz de TikTok** (contador 0:11 + usuario):
   todo eso es captura. **No** es captura una foto, un flyer, un afiche, un logo, ni una imagen con una
   marca de agua de TikTok ya incrustada. **32 capturas retiradas del sitio el 2026-09-12** (copia local
   en `D:\RELAX\__capturas_retiradas\`); detector: `__sonda_capturas.php` +
   `python __sonda_capturas.py ver|borrar <ids>`.
1. **El 98 % de las visitas es por celular.** Todo lo de este módulo existe por eso: que el
   móvil **suba poco** (datos del usuario) y **baje poco** (datos del visitante).
2. **Al subir**, la foto se comprime **en el navegador** (WebP, máx 1600 px) y después el
   **servidor la vuelve a validar y optimizar**. Nunca entra un archivo crudo de 10 MB.
3. **Al guardar**, el servidor crea **4 versiones** de cada foto (como WordPress):
   `foto-160` · `foto-300` · `foto-480` · `foto-800` + el **completo** (`foto.webp`, máx 1600 px).
4. **Al mostrar**, `img_tag()` pone `srcset` + `sizes`, el navegador **elige el tamaño** y el
   `srcset` lleva **el ancho REAL de cada archivo** (nunca un ancho inventado).
   En las galerías la foto se ve pequeña y **se amplía solo al tocarla** (`.cz-zoom`).
5. **Regla de oro:** el módulo **nunca** debe romper una subida. Si algo falla, se guarda el
   original y se sigue (mejor una foto pesada que una foto perdida).

---

## 1) LOS DOS MOMENTOS (y por qué hacen falta los dos)

| Momento | Problema que resuelve | Quién lo hace |
|---|---|---|
| **SUBIR** | El captador está en la calle con datos móviles. Mandar 4 MB por foto es lento y caro. | `assets/js/imagen_optimizar.js` (navegador) **+** `includes/imagenes.php` (servidor) |
| **MOSTRAR** | La tarjeta del buscador mide ~150 px: bajar una foto de 1600 px es tirar datos. | `img_tag()` / `img_srcset()` con `srcset` + `sizes` |

**Los dos son necesarios.** Comprimir al subir no basta: una foto de 1600 px sigue siendo
demasiado grande para una tarjeta de 150 px. Y tener versiones no basta: si el usuario sube
10 MB, la subida ya fue lenta y el hosting ya guardó un archivo enorme.

---

## 2) LOS TAMAÑOS (nombres y medidas)

| Clave | Archivo | **Ancho** máximo | Para qué sirve |
|---|---|---|---|
| `micro` | `nombre-160.webp` | **160 px** | Avatares (34-62 px), miniaturas del panel (44-76 px) |
| `mini` | `nombre-300.webp` | **300 px** | Rejillas de 3 columnas (galería), carrusel de la ficha, tira de historias |
| `chico` | `nombre-480.webp` | **480 px** | Tarjetas de producto y de tienda (2 columnas en celular) |
| `medio` | `nombre-800.webp` | **800 px** | Portada/hero de la ficha, tarjetas de 1 columna, visor de historias |
| `completo` | `nombre.webp` | **1600 px** | Ampliación al tocar la foto (zoom) |

- Constantes en `includes/imagenes.php`: `IMG_ANCHO_MICRO` (160), `IMG_ANCHO_MINI` (300),
  `IMG_ANCHO_CHICO` (480), `IMG_ANCHO_MEDIO` (800), `IMG_ANCHO_COMPLETO` (1600), `IMG_CALIDAD` (**75**
  desde el 2026-09-16; era 82 — ver §11).
  La escalera entera se lee con **`img_escalera()`** (una sola verdad para todo el módulo).
- **El sufijo se inserta antes de la extensión:** `fotos/x/photo_1.webp` → `photo_1-480.webp`.
  Así las versiones viven **al lado** de la original y la ruta de la BD **no cambia**.
- 🔴 **LAS VERSIONES SE MIDEN POR ANCHO, NO POR EL LADO MAYOR (regla del 2026-09-15).**
  El navegador compara el **ancho** del hueco (`sizes` × densidad de pantalla) con el ancho del
  archivo. Por eso la versión «de 800» tiene que medir **800 de ancho**: una foto vertical de
  1200×1600 da `800×1067`, **no** `600×800`. Con la regla vieja (lado mayor) el hueco de una
  tarjeta grande pedía 662 px, la versión de 800 medía 600 y el navegador **saltaba al original**
  (200-600 KB) en vez de usar una versión de 60 KB.
- Si la foto ya es más **angosta** que un tope, esa versión **no se crea** (no se duplica el
  archivo para nada) y `img_buscar_variante()` no la encuentra, que es lo correcto.
- **Una versión más angosta que su tope NO debe existir** (era el caso de las hechas con la regla
  vieja): es un estorbo que además **pesa más** que la versión de abajo, y como el navegador se
  queda con la más pequeña que alcanza el hueco, la elegía → **el doble de datos por el mismo
  tamaño en pantalla**. El paso `limpiar` del lote borró **2 479** de esas (116 MB liberados).

---

## 3) ARCHIVOS DEL MÓDULO

| Archivo | Qué hace |
|---|---|
| `includes/imagenes.php` | **El motor.** Valida (`getimagesize`), gira según EXIF, convierte a WebP, crea la escalera, elige el **gemelo `.webp`**, mide el **ancho real**, borra con sus versiones y **pinta** el `<img>` con `srcset` |
| `assets/js/imagen_optimizar.js` | **Comprime en el navegador** (canvas → WebP 0.82, máx 1600 px). Se engancha a los `<input type="file">` con `CZImg.engancharTodos()` |
| `assets/js/imagen_zoom.js` | **Abrir la galería al tocar.** Cualquier `img.cz-zoom` abre la versión completa a pantalla completa y **se pasa de foto deslizando ← →, con los botones ‹ › o con las flechas del teclado** (contador «3 / 8»; ✕, tocar el fondo, Esc o deslizar ↓ para cerrar) |
| `assets/css/components.css` | Estilos del visor (`.cz-zoom-*`). Su `?v=` va en **22** en `includes/header.php` |
| `includes/footer.php` | Carga `imagen_zoom.js` en todas las páginas |
| `includes/helpers.php` | Carga el motor (`require_once imagenes.php`) y usa `img_tag()` en el carrusel de tiendas |
| **`__img_lote2.php` + `__img_lote2_run.py`** | **El lote** (sonda temporal): crea las versiones que faltan, rehace las que quedaron angostas, recompone las fotos pesadas y borra las versiones viejas que sobran. **No se sube al hosting salvo para correrlo** y **se borra al terminar** (§7) |
| **`__img_prueba_motor.php`** | **Prueba del motor sin hosting ni navegador**: crea fotos de prueba, las pasa por todo el ciclo y comprueba **43 cosas** (escalera, anchos reales, elección por hueco, gemelo `.webp`, recompresión, borrado). Se corre con `-d extension=gd` |
| **`__img_audit.php`** | **Auditoría del hosting** (sonda temporal): cuántas fotos hay, cuánto pesan, qué versiones faltan, cuáles están mal comprimidas y qué pesa más de lo que debería |
| **`__img_comparar_pagina.py`** | Mide una página real y compara **lo que bajaba ayer** contra **lo que baja hoy** (el móvil simulado, sin repetir la misma URL) |
| **`__img_ver_eleccion.py`** | Muestra, imagen por imagen, el hueco, el `sizes` y **qué archivo elige el navegador** (para cazar un `sizes` mal puesto) |

---

## 4) CÓMO SE MUESTRA UNA IMAGEN (lo que hay que usar de ahora en adelante)

**Nunca** volver a escribir `<img src="<?= url_imagen($ruta) ?>">`. Usar:

```php
<?= img_tag($ruta, $alt, [
      'sizes' => '(max-width: 640px) 92vw, 300px',   // ¡obligatorio pensarlo para móvil!
      'zoom'  => true,                                // opcional: se amplía al tocar
  ]) ?>
```

`img_tag()` decide solo:
- **`src`**: la versión **chico (480)** si existe (o la mini, o la media). Es lo que baja un
  navegador sin `srcset`, que hoy no existe: por eso va una versión liviana y no la grande.
- **`srcset`**: solo con las versiones que **existen de verdad** en el disco, **cada una con su
  ancho real** (`img_ancho_real()`). Si la foto todavía no tiene versiones, **no pone `srcset`** y
  el sitio sigue funcionando igual.
- **`sizes`**: hay que pasarle el ancho real del hueco. Es lo que hace que el navegador
  elija bien (por eso está en cada llamada).
- **Sin foto** (`$ruta` vacía): pinta el marcador `assets/img/sin-foto.svg`.

**`sizes` que ya están en uso (para no inventar otros):**

| Dónde | `sizes` | Qué versión acaba bajando el móvil (360 px, DPR 2) |
|---|---|---|
| Tarjeta de negocio a **1 columna** (categoría, panel) | `(max-width: 640px) 92vw, 300px` | `-800` (necesita 662 px) |
| Tarjeta de negocio a **2 columnas** (buscador, portada) | `(max-width: 640px) 46vw, 300px` | `-480` (necesita 331 px) |
| Tarjeta de negocio de la portada, en PC (6 y 4 columnas) | `(max-width: 759px) 46vw, 190px` / `…, 280px` | `-480` en celular |
| Tarjeta de producto (portada, 5×2 y 2 columnas) | `(max-width: 560px) 42vw, (max-width: 900px) 31vw, 190px` | `-480` |
| Carrusel de tiendas de la ficha (hueco de 108 px) | `(max-width: 480px) 90px, 108px` | `-300` |
| **Tira de historias de la portada** (hueco 96 / 112 px) | `(min-width: 560px) 112px, 96px` | `-300` |
| Portrait de la ficha (hero) | `(max-width: 900px) 100vw, 900px` | `-800` |
| Galería A (**3 columnas** en celular / **6** en escritorio) | `(max-width: 899px) 33vw, 160px` | `-300` |
| Galería C (mosaico; la primera es doble) | `(max-width: 899px) 66vw, 320px` / `… 33vw, 160px` | `-480` / `-300` |
| Productos de la ficha | `(max-width: 640px) 46vw, 240px` | `-480` |
| Miniaturas del panel de productos | `76px` | `-160` |
| Miniatura del modal de banners (62 px) | *(no lleva: el PHP manda la de 160)* | `-160` |

⚠️ **Un `sizes` mal puesto se paga caro en las dos direcciones**: si dice MÁS de lo que mide el
hueco, el celular baja una foto del doble de tamaño (así estaba la portada: decía `92vw` en
rejillas de **dos** columnas); si dice MENOS, la foto se ve borrosa. **Antes de escribir un
número, míralo en el CSS** (`grid-negocios`, `grid-negocios--dos`, `fz-celda`, …), y compruébalo
con `python __img_ver_eleccion.py <url> 360 2`.

---

## 5) LOS 4 CAMINOS DE SUBIDA (qué hace cada uno)

| Camino | Comprime en el navegador | Optimiza y crea versiones en el servidor |
|---|---|---|
| **Caminante** (`caminante/index.html` → `caminante/subir.php`) | ✅ en `inputCapture()` con `CZImg.optimizarLista()` | ✅ motor |
| **Productos** (`productos.php`, `producto_guardar_foto()`) | ✅ `CZImg.engancharTodos(document)` | ✅ motor |
| **Asistente clásico** (`crear_negocio.php` → `api/subir_foto.php`) | ✅ `reducirImagen()` llama a `CZImg.optimizar()` (con respaldo canvas) | ✅ motor |
| **Banners** (panel admin → `api/subir_banner.php`) | ✅ `CZImg.optimizar()` antes del `fetch` | ✅ motor |
| **El maestro** (`crear_tienda_ia.php` → `includes/tienda_ia.php`) | ✅ | ✅ motor |

Detalles que importan:

- **Caminante guarda `.webp`** desde el 2026-09-10 (antes `.jpg` crudos). `caminante/subir.php`
  registra en `directorio_fotos` **solo los archivos completos**: filtra con **`img_es_version()`**
  (cualquier `-160/-300/-480/-800`) para que las versiones **no** entren como fotos aparte.
  ⚠️ Cualquier sitio que filtre nombres de versiones debe usar `img_es_version()`, **nunca** una
  lista de sufijos escrita a mano (así se quedaron fuera las versiones nuevas al añadirlas).
- **Borrar una foto borra sus versiones**: usar `img_borrar($ruta)` (no `unlink`), o quedarían
  archivos huérfanos ocupando disco.
- El motor **no re-comprime** una foto que ya viene en WebP, ≤1600 px y liviana
  (`IMG_WEBP_SIN_RECOMPRIMIR`, 400 KB): así no se pierde calidad dos veces.
- **GEMELO `.webp`:** si la ruta guardada en la base es `.jpg`/`.png` y al lado hay un
  `foto.webp`, **se sirve el gemelo** (`img_archivo_completo()`). Es la forma de dejar las fotos
  viejas en WebP **sin tocar la base de datos** y sin romper ningún enlace viejo (el `.jpg` se
  queda en el disco).

---

## 6) EL VISOR DE GALERÍA (foto pequeña → tocar → tamaño completo y pasar de foto)

> 🔁 **2026-09-14 — ES UNA GALERÍA, NO UNA FOTO SUELTA (arreglo pedido por el jefe):** textual,
> *«cuando haces clic en una imagen no puedes deslizar a la derecha o a la izquierda para ver la
> siguiente imagen; se supone que son un solo bloque de imágenes, una sola galería, y al hacer clic
> en una puedes mover a la derecha o a la izquierda para cambiar de imagen»*.
> Antes, al tocar una foto se abría **esa sola** y no había forma de pasar a la siguiente: había que
> cerrar y volver a tocar. Ahora, al tocar cualquier foto se abre **toda la galería del negocio** y
> se pasa de una a otra de cuatro maneras: **deslizando el dedo ← →** (lo normal en celular), con los
> **botones ‹ ›**, con las **flechas ← → del teclado** o con **Tab + Enter** sobre la miniatura.
> Arriba a la izquierda se ve el contador **«3 / 8»** y abajo la pista *«Desliza ← → para ver las
> demás · ✕ para cerrar»*. La **✕** cierra, igual que tocar el fondo, `Esc` o deslizar hacia abajo.

- Se activa con la opción `'zoom' => true` de `img_tag()`, que añade la clase **`cz-zoom`** y
  el atributo **`data-full`** (la URL de la versión completa).
- `assets/js/imagen_zoom.js` escucha los clics por delegación: funciona en cualquier página
  que cargue `includes/footer.php`.
- Pensado para móvil: botón ✕ de 44 px, cierre tocando el fondo, con `Esc` o **deslizando
  hacia abajo**; se permite el **pellizco** para acercar.

### 6.1) Qué fotos forman «una sola galería» (`data-galeria`)

Todas las fotos que llevan **el mismo valor de `data-galeria`** son **una sola galería**. En la ficha
lo pone `negocio.php` con el valor fijo **`data-galeria="tienda"`**, en los 4 sitios donde hay fotos:

| Plantilla | Dónde | Fotos que entran |
|---|---|---|
| **A** | la **portada** (`ficha-A__cabecera`) **+** la cuadrícula `ficha-A__galeria-grid` | **todas** (la portada es la foto 1) |
| **B** | los slides del carrusel (`ficha-B__hero-slide`) | todas |
| **C** | el mosaico de la pestaña 📷 Fotos (`ficha-C__galeria-mosaico`) | todas |

⚠️ **Lo que hay que saber antes de tocar esto:**

1. **La 1.ª foto de la plantilla A entró en la galería el 2026-09-14.** Antes la portada no se podía
   tocar: la banda oscura del título (`.ficha-A__cabecera-overlay`) la cubría y **se comía el toque**.
   Se le puso **`pointer-events: none`** (dentro solo hay texto, ningún enlace). Lo mismo se hizo con
   `.ficha-B__hero-info`, que tapaba la mitad de abajo del carrusel. **Si algún día se pone un botón
   o un enlace dentro de esas bandas, hay que sacarle el `pointer-events: none` o no se podrá tocar.**
2. **Las 3 plantillas viajan en el mismo HTML** (las otras dos quedan en `display: none`), así que la
   misma galería está pintada **3 veces**. Por eso el JS **descarta las fotos que no se ven**
   (`getClientRects().length > 0`) y **quita las repetidas** por URL: sin eso, el contador diría
   «3 / 15» y se pasaría de foto a una copia oculta.
3. En la plantilla **C** la galería vive en la pestaña 📷 Fotos: mientras esa pestaña está cerrada sus
   fotos se consideran ocultas (no se pueden tocar de todos modos).
4. Una foto **sin `data-galeria`** (los editores `editatiendas.php` / `editaproductos.php`) se abre
   **sola**, sin flechas: es el comportamiento viejo y **no se rompe**.
5. **Prueba sin navegador:** `node __zoom_prueba.js` monta un DOM mínimo y comprueba 24 cosas
   (agrupar, saltar las ocultas, quitar repetidas, deslizar ← →, botones, flechas, dar la vuelta,
   ✕, fondo, deslizar ↓). Se corre **desde `D:\RELAX`** y **no toca el navegador del jefe**.
6. **Versiones que hay que subir juntas si se toca esto:** `assets/js/imagen_zoom.js` (`?v=2` en
   `includes/footer.php`), `assets/css/components.css` (`?v=22`), `assets/css/plantilla-a.css`
   (`?v=3`), `assets/css/plantilla-b.css` (`?v=3`) y `negocio.php`.

- Dónde está puesto: **galería de la plantilla A**, **galería de la plantilla C** y **el carrusel de
  fotos de la plantilla B** (se añadió el 2026-09-14: en B las fotos no se podían tocar).
- 📐 **CUÁNTAS COLUMNAS TIENE LA GALERÍA (orden del jefe, 2026-09-14:** *«la galería se está presentando
  en una cuadrícula de dos columnas: cámbiala a 6 columnas en escritorio y a 3 en móvil, y hazlo
  clickeable para ver la imagen en tamaño original»***):**
  - **Plantilla A** (`plantilla-a.css` → `.ficha-A__galeria-grid`): **3 columnas** en celular (antes eran
    **2 fijas**). Celdas cuadradas (`aspect-ratio: 1`).
  - **Plantilla C** (`plantilla-c.css` → `.ficha-C__galeria-mosaico`): 3 columnas en celular
    (el primer mosaico sigue siendo doble).
  - ⚠️ Al cambiar las columnas hay que cambiar también el atributo **`sizes`** de `img_tag()` en
    `negocio.php` (§4), o el navegador baja fotos más grandes de las que necesita.
  - Si se toca el CSS de la plantilla, subir `includes/header.php` con el **`?v=`** incrementado.

- ✂️ **EL RECORTE DE LA GRILLA — 3 × 3 EN MÓVIL Y 5 × 5 EN PC (orden del jefe, 2026-09-18, textual):**
  *«cuando una tienda tenía más de nueve fotos en su galería, automáticamente la galería se recortaba a
  una grilla de 3 × 3 (tres columnas por tres filas) en formato móvil y a 5 × 5 en formato PC, y cuando
  alguien daba clic en cualquiera de las fotos de la galería se abría una ventana modal y ahí sí se veían
  todas las fotos que podía tener; la idea era para no llenar la página con muchas fotos».*
  **Cómo quedó (2026-09-18), y las 3 piezas que van juntas:**
  | Pieza | Qué hace |
  |---|---|
  | `negocio.php` | pinta **todas** las fotos de la galería, pero **desde la 10.ª** cada `<img>` lleva **`data-galeria-oculta="1"`** (plantilla **A** y mosaico de la **C**) |
  | `plantilla-a.css` / `plantilla-c.css` | **5 columnas** desde 900 px (antes 6) y el recorte: **`nth-child(n+10){display:none}`** en celular y **`nth-child(n+26){display:none}`** desde 900 px (más `nth-child(-n+25){display:block}` para que en PC se vean 25) → **9 celdas en móvil (3 × 3) y 25 en PC (5 × 5)** |
  | `assets/js/imagen_zoom.js` (`?v=3`) | al agrupar la galería **ya no salta** las fotos marcadas con `data-galeria-oculta` (las demás ocultas sí se siguen saltando): por eso al tocar cualquier foto el visor muestra **TODAS** las de la tienda, con su contador «1 / 130» |
  - **Las fotos recortadas NO se bajan**: siguen con `loading="lazy"` y, al estar en `display:none`, el
    navegador no las pide; el visor solo baja la `data-full` de la que el visitante abre.
  - **La plantilla B (carrusel) NO se recorta**: enseña una foto por vez, así que no llena la página.
  - **Prueba sin navegador:** `node __zoom_prueba.js` (**29 comprobaciones**, incluida la nueva del
    recorte: 9 visibles + 3 marcadas = «1 / 12» y la oculta sin marca no entra) y
    `python __gal_prueba.py` (mide por HTTP la ficha real: 129 celdas, 241 marcas, CSS con el recorte
    y los `?v=` correctos).
  - **Si se toca esto, se suben juntos:** `negocio.php`, `includes/header.php` (plantilla-a **`?v=4`**,
    plantilla-c **`?v=3`**), `assets/css/plantilla-a.css`, `assets/css/plantilla-c.css`,
    `assets/js/imagen_zoom.js` y `includes/footer.php` (visor **`?v=3`**).

---

## 7) EL LOTE: DEJAR TODAS LAS FOTOS CON SUS TAMAÑOS Y LIGERAS

El sitio tiene **6 841 fotos originales** (604 MB) y el lote las deja **todas** con su escalera.
Los archivos son **sondas temporales**: se suben, se corren por rondas y **se borran al terminar**.

```bash
# 1) subir la sonda a la raíz viva
python __img_lote2_run.py subir
# 2) mirar qué falta y cuánto pesa (no toca NADA)
python __img_lote2_run.py resumen
# 3) crear / rehacer las versiones que faltan (20 rondas de 800 fotos)
python __img_lote2_run.py variantes 20 800
# 4) recomprimir las fotos pesadas o mal comprimidas
python __img_lote2_run.py recomprimir 20 25
# 5) borrar las versiones viejas que sobran y encarecen la página
python __img_lote2_run.py limpiar
# 6) BORRAR la sonda del hosting (y comprobar el 404)
python __img_lote2_run.py borrar
```

**Es idempotente y reanudable**: cada ronda inventaría lo que hay, trabaja con presupuesto de
tiempo (`segundos`) y cuenta lo que queda. Si una ronda se corta, se vuelve a llamar y sigue.

⚠️ **Trampas del lote (vividas el 2026-09-15):**

1. **La conexión HTTP se puede cortar a los ~100-300 s y el trabajo igual se hace.** El script
   corre en el servidor aunque el navegador/el `urllib` se rindan: la ronda que parece «de 4
   segundos y 8 fotos» puede venir **después** de una tirada larga que sí terminó. Antes de repetir
   una tanda, **mira el `resumen`**, no el reloj.
2. **Una versión no se crea si no aporta** (la foto ya es más angosta que el tope) **pero una
   versión vieja y angosta SÍ hay que borrarla** (`paso=limpiar`), o el navegador la elegirá por
   ser la más pequeña que alcanza… y pesa más que la de abajo.
3. **Las fotos enormes (>35 MP) no se pueden leer con GD** en esa pasada: primero se recomprimen
   (`paso=recomprimir`) y después se les hacen las versiones.
4. **GD comprime PEOR que un codificador bueno.** En fotos ya bien comprimidas, `imagewebp(q82)`
   puede dar un archivo **más grande** que el original. Por eso `img_recomprimir()` prueba
   82 → 72 → 65 y **solo acepta si baja al menos un 10 %** (si no, `no_conviene` y se deja como está).
5. **Fotos que GD no puede leer:** los **WebP animados** (`VP8X` con la marca `ANIM`) no los
   decodifica GD → esas fotos se quedan sin versiones. Hoy quedan **2** (`fotos/producto_1532.webp`
   de 1 MB y `fotos/producto_2027.webp`): son fotos del proveedor viejo que se van a reemplazar con
   el flujo de `GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md`.

**Lo que hizo el lote el 2026-09-15** (primer pase completo):

| Trabajo | Números |
|---|---|
| Versiones nuevas creadas (160 y 480, que no existían) | **11 048** |
| Versiones rehechas por ancho (las que quedaron angostas) | **~8 300** |
| Versiones viejas borradas (estorban y pesan más) | **2 479 · 116,5 MB liberados** |
| Fotos recomprimidas o con gemelo WebP | **433** (125 → 91 MB) |
| Originales de más de 1600 px | **308 → 0** |
| Originales de más de 400 KB | **58 → 6** |

---

## 8) DATOS REALES MEDIDOS

**El hosting puede optimizar** (comprobado con una sonda, no supuesto):

| Dato | Valor |
|---|---|
| PHP | 8.3.33 · GD **2.3.3** · **WebP: SÍ** · EXIF: SÍ |
| Memoria / tiempo | `memory_limit` 512 MB · `max_execution_time` 300 s |
| Disco | **21,8 GB** de cuota · libre 9,6 GB (la biblioteca de imágenes ocupa ~1,07 GB) |

**La biblioteca de imágenes** (auditoría del 2026-09-15, antes → después):

| Medición | Al empezar | Ahora |
|---|---|---|
| Fotos originales | 6 837 (646 MB) | 6 841 (605 MB) |
| Versiones de tamaño | 10 558 (290 MB) | **21 613 (466 MB)** → **≈388 MB** tras la recompresión del 2026-09-16 (§11.4) |
| Versiones de 160 px | 0 | **6 805** |
| Versiones de 300 px | 6 778 | 6 756 |
| Versiones de 480 px | 0 | **6 453** |
| Versiones de 800 px | 3 780 | 1 599 (las demás no aportaban: borradas) |
| Fotos con TODAS las versiones que les tocan | 3 | **4 076** |
| Fotos sin ninguna versión | 26 | **2** (los 2 WebP animados) |

**Ahorro de datos por página, en celular (360 px, DPR 2)** — medido con
`python __img_comparar_pagina.py <url> 360 2`, que simula lo que baja el navegador **sin contar dos
veces la misma foto** (la ficha pinta 3 plantillas y la misma foto sale 9 veces; el móvil la baja una):

| Página | Antes | Ahora | Ahorro |
|---|---|---|---|
| **Buscador** (`buscar.php?q=pollo`) | 2 155 KB | **765 KB** | **65 %** |
| **Portada** (135 imágenes) | 4 536 KB | **2 955 KB** | **35 %** |
| **Ficha de tienda** (Sra. Doris, 15 fotos + 12 productos) | 4 523 KB | **3 088 KB** | **32 %** |
| **Ficha** (Cevichería El Ratoncito) | 416 KB | **381 KB** | 8 % |
| **Rubro** (`categoria/ferreterias`, fichas a 1 columna) | 961 KB | **961 KB** | ya estaba a su tamaño |

> ⚠️ Los `sizes` de la portada viven en **`includes/portada_ciclos.php`** (el 2026-09-15 otra sesión
> movió allí el pintado de la portada). **Corregidos el 2026-09-16** (ver §11): las fichas de la portada
> en celular son **`(max-width: 759px) 41vw, …`** (2 columnas; el hueco real es 159 px, no 46vw) y las
> tarjetas de producto **`(max-width: 560px) 39vw, …`** (el hueco real es 137,8 px = 42 % del carrusel,
> no 42vw). Con los números viejos el celular se bajaba la versión de **480** en vez de la de **300**.


**Los tres casos que explican el módulo entero:**

1. Una tarjeta de producto pedía **302 px** de ancho y la versión de 300 medía 300: el navegador
   **saltaba a la de 800** (hasta **178 KB** por tarjeta). Hoy hay versión de **480** → **26-55 KB**
   por tarjeta, con la misma nitidez.
2. Una foto de **526×789** se anunciaba como «**1600w**» (era el rótulo fijo del completo): el móvil
   se bajaba **141 KB** creyendo que era una foto enorme. Hoy el `srcset` dice `526w` y elige bien.
3. Una foto vertical de una tienda (fuente de **768 px**) tenía una versión «de 800» de **447×800**
   que **pesaba más que la de 480** (75 KB contra 37 KB): el navegador elegía la de 447 por ser la
   más pequeña que alcanzaba el hueco → **el doble de datos**. Esa versión sobraba y se borró.

> **Con ~20 imágenes por página, el ahorro para un visitante de celular pasó del orden de 1 MB
> por página.** Ese es el motivo de todo el módulo.

---

## 9) TRAMPAS (lo que NO debe repetirse)

1. **Las versiones se guardan SIEMPRE en `.webp`, aunque la foto original sea `.jpg` o
   `.png`.** Por eso la búsqueda de la versión tiene que probar **`.webp` primero** y, si no
   está, la extensión original (`img_buscar_variante()`). ⚠️ **Este fue el fallo más caro de la
   sesión del 2026-09-10**: `img_variantes()` buscaba `foto-300.<ext-original>` mientras el
   generador escribía `foto-300.webp`, así que **ninguna foto `.jpg`/`.png` emitía `srcset`** y el
   móvil seguía bajándose la foto completa (una de **4,2 MB** en el buscador).
2. **EL `srcset` LLEVA EL ANCHO REAL, NO EL TOPE DE LA ESCALERA** (fallo del 2026-09-15).
   Antes se anunciaba `…-300.webp 300w` y `foto.webp 1600w` **aunque el archivo midiera 200 y 526
   px**; el navegador creía que tenía una foto de 1600 px y se bajaba 141 KB donde bastaban 29 KB.
   Se arregló con **`img_ancho_real()`** (lee el ancho del disco, con caché por petición).
3. **Las versiones se miden por ANCHO** (no por el lado mayor): ver §2. El lado mayor sirve para
   el **"completo"** (el tope de 1600 px de la subida), no para las versiones.
4. **`img_generar_variantes()` no crea una versión que no aporte**: si la foto ya es más angosta
   que el tope, esa versión **no existe**. Cualquier herramienta que pregunte "¿ya está hecha?"
   tiene que pedir **el ancho** del archivo, no solo si existe (si no, las fotos quedan
   "pendientes" para siempre y se rehacen en cada ronda).
5. **Nunca** `@unlink()` una foto: hay que borrar sus versiones también → `img_borrar()` (que
   incluye el sufijo de toda la escalera y el gemelo `.webp`).
6. **Nunca** escribir `srcset` a mano: si la versión no existe, el navegador pide un 404 y la
   foto **desaparece**. Siempre `img_tag()` / `img_srcset()`, que comprueban el disco.
7. **`sizes` mal puesto = no sirve de nada el `srcset`.** Si no sabes el ancho del hueco,
   míralo en el CSS antes de escribir el número (y compruébalo con `__img_ver_eleccion.py`).
8. **`mover` ≠ `move_uploaded_file`**: el motor usa `img_mover()`, que copia cuando el origen
   **no** es una subida (herramientas y pruebas). Con `move_uploaded_file` a secas, esas rutas
   fallan en silencio.
9. **Al cambiar `components.css` hay que subir el `?v=`** en `includes/header.php` (ahora `22`).
   Al cambiar un JS nuevo, su `?v=` en la página que lo carga (ahora `1`).
10. **Caminante: la extensión importa.** `directorio_fotos.ruta` apunta a `.webp` desde el
    2026-09-10; las filas viejas siguen apuntando a `.jpg` y **deben seguir funcionando**
    (el motor no asume extensión, y si hay gemelo `.webp` lo prefiere).
11. **No volver a meter un `<canvas>` propio** en una página: está `imagen_optimizar.js`.
12. **El lote puede saturar el hosting.** En la primera tanda del 2026-09-10 el servidor respondió
    **503 Service Unavailable**. El generador es **reanudable**: se vuelve a llamar y sigue.
    Trabajar con `segundos` bajos (40-150 s) y no lanzar varias rondas a la vez.
13. **⚠️ EL CDN DE HOSTINGER NO MIRA SI EL ARCHIVO CAMBIÓ (trampa grande, 2026-09-16).** Las fotos
    se sirven con `Cache-Control: public, max-age=15552000` (**180 días**) y delante está **`hcdn`**
    (el CDN de Hostinger), que **sirve su copia por URL sin revalidar**. Se comprobó con una foto de
    verdad: se recomprimió `…/la-casa-de-las-llantas…-480.webp` (83 870 → 67 620 bytes) y **el CDN siguió
    entregando los 83 870 bytes con `x-hcdn-cache-status: HIT` y `Age: 3312`** (también por
    `www.dechimbote.com`: las dos direcciones pasan por `hcdn`, no hay camino directo al origen).
    → **Cambiar los BYTES de una foto que ya existe NO llega a ningún visitante hasta que caduque el
    caché (meses).** Solo hay dos formas de que llegue: **(a) una URL nueva** (es lo que hacen las
    versiones nuevas `-160`/`-480`: por eso la auditoría del 2026-09-15 sí se vio) o **(b) subir la
    versión en `url_imagen()`** (`?v=…`, el mismo truco que usa el CSS), que obliga al CDN a pedirlas
    otra vez — y de paso hace que **cada visitante las vuelva a bajar una vez**. El **HTML, el CSS y el
    JS no tienen este problema** (van con `?v=` o con `no-store`). Detalle y números: **§11**.
14. **Los scripts temporales con clave dentro** se borran del hosting al terminar (y se
    comprueba el 404). **El lote escribe y borra archivos: eso no puede quedar colgado en el sitio.**

---

## 10) PENDIENTES

- [ ] **2 fotos** son **WebP animados** y GD no los puede leer, así que se quedan sin versiones:
      `fotos/producto_1532.webp` (**1 MB**, 220×400) y `fotos/producto_2027.webp` (108 KB). Son fotos
      del proveedor viejo: se resolverán al reemplazarlas (flujo de
      `GUIA_PRODUCTOS_IMAGENES_Y_CONTEXTO.md`). **Mientras estén, son las dos únicas fotos que se
      sirven enteras.**
- [ ] **10 fotos** siguen pesando más de lo que les gustaría (4,3 MB en total: 4 imágenes
      `ia_20260915_*` de 462-568 KB, `producto_565`, `producto_751`, 2 fotos de restaurante y los 2
      animados). GD ya no puede bajarlas más **sin degradarlas**: no se tocan.
- [ ] **3 `.jpg` viejos de Caminante** (9,8 MB en total) siguen en el disco aunque ya **no se
      sirven** (tienen su gemelo `.webp` y el motor prefiere el gemelo). Se pueden borrar cuando
      nadie los enlace directamente por URL.
- [ ] Revisar si conviene una versión **de 1200** para las portadas en PC (hoy un escritorio de
      900 px pide el completo de 1600). Antes de hacerlo, medir cuántas fotos la usarían de verdad.
- [ ] **`assets/img/logo.png`** (353×353, **285 KB**) ya no lo usa nadie (la cabecera usa
      `logo-dechombote.webp`, 4 KB): se puede borrar del hosting.
- [ ] Medir el ahorro real de datos con las estadísticas del sitio (antes/después).
- [ ] Repasar cualquier `<img src=` nuevo que quede en el proyecto: hoy solo quedan los **logos de
      la cabecera** (4 KB, con `width`/`height`) y las imágenes que pinta el JS con una URL ya
      resuelta por el motor (`banners.js`, `carrito.js`, `chatbot.js`, `tienda_ia.js`).

---

## 11) LA AUDITORÍA DEL PESO EN CELULAR (2026-09-16)

> Pedido del jefe: *«revisa la versión móvil del sitio, qué imágenes está cargando y el tamaño, porque en
> móvil lo siento muy pesado, tardan las imágenes en cargar. ¿Podría ser un bucle de código o imágenes mal
> optimizadas? Mide cuánto pesa exactamente el sitio en modo móvil.»*

### 11.1 Lo que pesaba (medido, no supuesto)

Se midió **archivo por archivo por HTTP** (con un `User-Agent` de celular) y además **en el navegador
de verdad**, metiendo la portada en un marco de **360 px** para ver qué fotos pide el navegador de verdad:

| Medición | Antes |
|---|---|
| Al abrir la portada (360 px, pantalla 2x) | **2,19 MB · ~95 peticiones · 75 fotos** |
| De eso, fotos | 1 607 KB |
| HTML de la portada | 378,8 KB de marcado (64 KB por la red) · 141 `<img>` · 1 681 nodos |
| Bajando toda la portada | **23,2 MB · 822 peticiones · 802 fotos** |
| Tiempo estimado al abrir | 4,9 s en 4G normal · **10,4 s en 4G flojo** · 19,6 s en 3G |
| Miniaturas del sitio (`-160/-300/-480/-800`) | **18 029 archivos · 458 MB** |

**Qué cargaba cada bloque al abrir** (contado en el navegador real, no estimado): tira de historias
**33 de 36** fotos (512 KB), rejilla al azar 13, tarjetas de producto 19 (667 KB), banners 5 (216 KB),
«Negocios para ti» 0 (van más abajo).

### 11.2 Las tres causas

1. **La portada se repetía 6 veces** (`PORTADA_CICLOS_MAX`): cada vuelta son 133 fotos y 264 KB de HTML.
   Eso es lo que el jefe intuía como «bucle»: **no hay bucle infinito, es una repetición que multiplica
   todo por 6**.
2. **La tira de historias cargaba TODAS las tiendas** (`HISTORIAS_TIENDAS_MAX = 0`): 36 historias, y son
   lo primero que se ve debajo del hero → el navegador se bajaba **33 fotos nada más abrir**.
3. **El `sizes` mentía hacia arriba y el celular bajaba la foto del doble.** La tarjeta de producto mide
   `flex: 0 0 42%` **de 328 px = 137,8 px** (no 42vw = 151 px) y la ficha de negocio **159 px** (no
   46vw = 165,6 px). Con el dato inflado, a 2x el navegador necesitaba **302 px** y la escalera salta de
   300 a 480: **por 2 píxeles** se bajaba la de 480 (**35 KB**) en vez de la de 300 (**15,5 KB**).

### 11.3 Lo que se cambió

| Cambio | Archivo | Efecto |
|---|---|---|
| Ciclos de la portada **6 → 3** | `includes/portada_ciclos.php` (`PORTADA_CICLOS_MAX`) | visitas largas: 23,2 → **6,2 MB** |
| Historias **36 → 12** | `includes/historias.php` (`HISTORIAS_TIENDAS_MAX`) | la tira al abrir: 512 → **219 KB** |
| `sizes` con el ancho **verdadero** (`39vw` y `41vw`) | `includes/portada_ciclos.php` (3 sitios) | el navegador elige **300** en 104 de 113 fotos |
| Calidad WebP **82 → 75** | `includes/imagenes.php` (`IMG_CALIDAD`) | las fotos **nuevas** nacen 19-20 % más livianas |

**Resultado medido** (mismo método, después de subir los 4 cambios y de recompirmir las fotos):

| Medición | Antes | Ahora |
|---|---|---|
| **Al abrir la portada** | 2,19 MB · 75 fotos | **1,51 MB · 50 fotos** (**−31 %**) |
| Fotos al abrir | 1 607 KB | **979,5 KB** (**−39 %**) |
| HTML de la portada | 378,8 KB | **317,7 KB** |
| `<img>` en el HTML | 141 | 113 |
| Bajando toda la portada | 23,2 MB · 802 fotos · 822 peticiones | **6,65 MB · 331 fotos** (**−71 %**) |
| Fotos del ciclo 1 (completo) | 2 806 KB | **2 054 KB** |
| Tiempo estimado al abrir (4G flojo) | 10,4 s | **7,6 s** |

> La nitidez **no baja**: la versión de 300 px se pinta en un hueco de 138-159 px, o sea **1,9-2,2x**
> (igual o más que la pantalla de 2x). Lo único que cambió es que ya no se baja la de 480.
>
> ⚠️ **Cuidado con las mediciones intermedias:** a mitad de camino se midió «851 KB» y **no era verdad**:
> el CDN seguía sirviendo **copias viejas más pequeñas** de algunas fotos (el disco ya tenía las nuevas).
> Los números de esta tabla son los definitivos, medidos **después** de versionar las URL (§11.4), o sea
> con el navegador bajando **exactamente lo que hay en el disco**.

### 11.4 La recompresión de las fotos viejas (HECHA el 2026-09-16)

✅ **Recomprimidas 16 661 miniaturas** (`-160/-300/-480/-800`; el original del zoom **no se toca**):
**425,2 MB → 347,7 MB = 77,5 MB menos (−18,2 %)**. Se hizo con la sonda
`__sonda_recomprimir.php` + el driver **`__recomprimir_run.py`** (34 rondas de 500 archivos, **8,1 minutos**;
solo reemplaza si el nuevo pesa ≥8 % menos y se puede volver a leer). ⚠️ **La sonda se borró** del
hosting al terminar (comprobado: da **404**) — con el driver nuevo, que reintenta y borra al final,
porque el `__sonda_run.py` se queda colgado si la conexión FTP se corta a mitad de una tirada larga
(pasó: *«421 Idle timeout»* y la sonda quedó viva; hay que borrarla a mano).

🔴 **Y HUBO QUE ESTRENAR URL (`?v=75`), o el trabajo no se habría visto nunca.** Con las URL de siempre,
el CDN seguía entregando su copia vieja: se comparó **el archivo del disco (por FTP) contra lo que
servía el CDN (por HTTP)** y en **9 de 10 fotos el md5 era distinto** (y en varios casos el disco tenía
la foto *más pesada*, porque el CDN guardaba una generación anterior de la versión). Después de subir la
versión: **8 de 8 fotos idénticas al disco** (`x-hcdn-cache-status: MISS` → el CDN las pidió de nuevo).

**Cómo se sube la versión** (ya está hecho, queda escrito para la próxima vez): `IMG_URL_V` en
`includes/imagenes.php` (**hoy `75`**) y la función **`img_url_version()`**, que se aplica en `img_url()`,
`img_srcset()` y `img_tag()` (incluido el `data-full` del zoom). **`url_imagen()` se dejó SIN versión a
propósito**: la usan los endpoints de subida y los previews del panel, y la ruta que se guarda en la base
tiene que quedar limpia (los endpoints devuelven `rel` para guardar y `url` para previsualizar).

### 11.5 Lo que queda pendiente

- ⏳ **El botón «Ver tiendas cerca» trae 146 KB de JSON** (`api/cerca_de_mi.php`, **1 135 KB sin
  comprimir**: devuelve las tiendas cercanas con todas sus fotos). No entra al abrir la página (solo
  cuando el visitante toca el botón), pero es el JSON más pesado del sitio: conviene mirar si hace falta
  mandar todas las fotos o basta la portada de cada tienda.
- ⏳ **Los banners de publicidad** son ahora el bloque más pesado al abrir (**181,6 KB de los 979,5 KB**,
  5 fotos en su versión de 800 px, ~36 KB cada una). Es el único sitio donde una foto pesa más que un
  bloque entero de contenido: conviene mirar si los banners de verdad necesitan 800 px.
- ⏳ **Las fuentes de Google** (Inter en **5 pesos**): 12,5 KB de CSS + hasta 214 KB de woff2 en 7
  archivos y **2 conexiones a un dominio de terceros** antes de pintar el texto. Auto-hospedar 2 pesos
  (400 y 700) ahorraría ~100 KB y las dos conexiones.
- ⏳ **Las fotos de arriba son `loading="lazy"` y sin `fetchpriority`**, y la mayoría no lleva
  `width`/`height`: la primera imagen se pide tarde y la página salta mientras carga.
- ⏳ **Los originales (el «completo» del zoom)** siguen a calidad 82: son 6 841 archivos y solo se bajan
  al ampliar una foto. Se pueden recomprimir con el mismo driver cuando se quiera (cambiando el filtro
  de sufijos de la sonda).

**Herramientas de esta auditoría** (quedan en la raíz para repetirla cuando haga falta):
`__peso_medir.py` (pesa todo lo que baja la portada), `__peso_simula.py` (qué elige el navegador a 2x y
3x), `__peso_total2.py` (los 6 ciclos) y `__peso_alabrir2.py` (el primer pantallazo). Los informes, en
`__peso_*.txt`. Para la recompresión: `__recomprimir_run.py` (+ `__sonda_recomprimir.php`).
Para comprobar si el CDN miente: `__cdn_md5.py` (disco por FTP contra CDN por HTTP).

---

> **Guías base:** `GUIA_MAESTRA_CHIMBOTE_XYZ.md` (índice) · `REGLAS_DE_ORO_PROYECTO.md`
> (reglas del jefe) · `GUIA_DESPLIEGUE_Y_ENTORNO.md` (cómo editar, subir y verificar).
