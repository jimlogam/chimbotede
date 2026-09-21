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


# GUÍA DE 🛡️ «EDITAR LA TIENDA DESDE SU PROPIA FICHA» (el menú del súper admin) · dechimbote.com

> **Qué es:** cuando el **súper administrador** abre la ficha de **cualquier** tienda
> (`/neg/<slug>`), **dentro de esa ficha le aparece un menú para editarla ahí mismo**: el teléfono,
> la descripción, los productos y otras cosas más (rubro, zona, dirección, horario, redes, si se ve o
> se oculta, y las fotos). **En el área de la descripción y en la de cada producto hay un botón 🤖 IA**
> que **reescribe ESA área en particular con inteligencia artificial**.
>
> 🔒 **Solo lo ve el súper administrador.** El público (y los dueños) ven la ficha **exactamente como
> siempre**: ni un byte de este módulo llega a su HTML.
>
> **Pedido del jefe (2026-09-18, textual):** *«cuando estoy en modo súper administrador, bloqueado,
> dentro de cada tienda debe aparecerme un menú para editar esa tienda, como su número de teléfono, su
> descripción, sus productos y otras cosas más. En las áreas de editar sus productos o editar su
> descripción coloca un botón que diga IA: significa que se presiona ese botón, esa área en particular
> va a ser reescrita usando inteligencia artificial.»*

---

## §1. LO QUE HACE, EN UNA TABLA

El menú se pinta **debajo de los botones de WhatsApp/Llamar de la ficha** (el mismo sitio donde vive el
bloque 📨 de invitación), y abre una **hoja** con 4 pestañas:

| Pestaña | Qué se puede hacer | El botón 🤖 IA |
|---|---|---|
| 🏪 **Datos** | nombre · rubro · zona (distrito) · cómo atiende (local, ambulante, a domicilio, a todo el país, al por mayor) · dirección o referencia · horario · **WhatsApp** · **teléfono** · Facebook · Instagram · TikTok · **si la tienda se ve o se oculta** | — |
| 📝 **Descripción** | escribir el texto de la ficha (**HTML simple**: `h3 p ul ol li strong br`), **ver cómo queda** sin salir del panel y guardarlo | **🤖 IA: reescribir este texto** |
| 🛍️ **Productos** | lista con **buscador**, y por producto: **nombre, precio, unidad y texto**, ocultar/mostrar y **borrar**; y **➕ Producto nuevo** | **🤖 IA: reescribir** (uno por uno) y **🤖 IA: escribir el texto** (al crear) |
| 🖼️ **Fotos** | subir fotos (hasta 6 de una vez), **⭐ poner de portada** y **🗑️ borrar** | — |

* **La IA no es nueva ni distinta de la del sitio**: el texto de la tienda lo escribe el **mismo
  copywriting** que usa El maestro y `mi-tienda.php` (`tienda_ia_ia_descripcion()`: colores, títulos y
  botones de WhatsApp), y el de cada producto `tienda_ia_ia_descripcion_producto()`, que **mira la foto
  del producto** si la tiene y **respeta lo que el jefe ya escribió** (copywriting, no invención).
* **El texto que devuelve la IA NO se guarda solo:** se pone en su casilla, el jefe lo mira (o toca
  **👁️ Ver cómo queda**) y **él decide** con 💾 Guardar. Así nada se pierde por un clic de más.
* **El enlace de la tienda NUNCA cambia.** Se puede corregir el nombre y la dirección web sigue siendo
  la misma (el `slug` no se toca), como en el editor del dueño.
* Cada pulsación del botón 🤖 IA = **una llamada** a DeepSeek (~US$ 0,0002 · 2 a 15 segundos).

---

## §2. 🔒 LA PUERTA (solo el súper administrador)

| Comprobación | Qué pasa |
|---|---|
| `editar_ficha_puede()` (que es `es_admin()`) | Si no es admin, **`editar_ficha_html()` devuelve `''`**: no se pinta el menú, ni su CSS, ni su JS, ni el campo CSRF (comprobado: la ficha del público pesa **~70 KB menos**). |
| Cualquier POST `accion=edt_…` sin sesión de admin | **403** y la respuesta dice *«Solo el súper administrador puede editar tiendas»*. **La tienda no se toca** (lo comprueba la sonda: nombre y estado quedan intactos). |
| El token CSRF (`_csrf`) | Se comprueba en **cada** acción (`csrf_verificar()`). Sin token bueno: **419** y no se escribe nada. |
| **Contraseña** | ⚠️ **No se pide.** El candado de la contraseña del dueño (`mi-tienda.php`, orden del jefe del 2026-09-16) es **para el dueño**; esta es la herramienta del **súper administrador**, y su llave es su propia sesión de admin + el CSRF (el mismo patrón que el botón 📨 de invitación). |

> ⚠️ **Sin caché a propósito:** `editar_ficha_puede()` **no** guarda su resultado en memoria
> (`static`), para que una sonda pueda medir en la misma corrida la cara de admin y la de público.
> (El módulo hermano de la invitación **sí** cachea: por eso su comprobación vive en su propia sonda,
> `__inv_ficha_verificar.php`.)

---

## §3. CÓMO ESTÁ HECHO (arquitectura)

```
deploy/includes/editar_ficha.php     ← EL MOTOR + EL PANEL (lo único nuevo)
        │  editar_ficha_accion($negocio)   · atiende los POST (accion=edt_…) y responde JSON
        │  editar_ficha_html($negocio)     · el menú + la hoja + su CSS y su JS ('' si no es admin)
        │  editar_ficha_ia_descripcion()   · el texto de la tienda con la IA del sitio
        │  editar_ficha_ia_producto()      · el texto de UN producto con la IA del sitio
        ▼
deploy/negocio.php   ← LA FICHA: 1) `editar_ficha_accion($negocio)` al principio (como la invitación)
                                2) `$edt_en_A/B/C` pintado debajo del bloque 📨 en las 3 plantillas
        ▼
includes/tienda_ia.php  ← SE REUTILIZA: el copywriting de la tienda y de los productos (la misma IA)
includes/imagenes.php   ← SE REUTILIZA: subir fotos (WebP ≤1600 + versiones 800/300) y borrarlas
```

**Las 11 acciones** (`accion=…`, todas con la puerta del admin + CSRF):

| Acción | Qué hace |
|---|---|
| `edt_datos` | Guarda los datos de la pestaña 🏪 (solo los campos que llegan; los números se guardan **solo con dígitos**, y si vienen con `51` delante se les quita). |
| `edt_desc` | Guarda la descripción. **Antes de guardar se limpia**: los bloques `<script>`, `<style>`, `<iframe>`, `<object>` y `<embed>` **se van enteros** (con su contenido) y después `tienda_ia_copy_limpiar()` deja solo las etiquetas permitidas. |
| `edt_desc_ia` | **🤖 La IA reescribe el texto de la tienda** (devuelve el HTML, **no** lo guarda). |
| `edt_lista` | Devuelve los productos (hasta 300) con todo lo que se edita. |
| `edt_prod` | Guarda un producto: título, precio, unidad, texto, visible y destacado. |
| `edt_prod_ia` | **🤖 La IA reescribe el texto de ESE producto** (mira su foto si la tiene). |
| `edt_prod_nuevo` | Crea un producto (nace **visible**). |
| `edt_prod_borrar` | Borra el producto **y sus fotos** (el archivo y las versiones 800/300). |
| `edt_fotos` | Devuelve las fotos de la tienda (la 1.ª es la portada). |
| `edt_foto_subir` | Sube hasta 6 fotos (comprimidas a WebP por el motor del sitio). |
| `edt_foto_portada` / `edt_foto_borrar` | Pone una foto de portada (`orden 0`) o la borra. |

* 🧪 **Gancho de las sondas** (mismo estilo que `$GLOBALS['SUPREMO_DIAG']` del Supremo): con
  `$GLOBALS['EDT_MODO_PRUEBA'] = 1` la función **devuelve** el resultado en vez de responder y salir,
  así una sonda prueba las 11 acciones seguidas en una sola petición. Sin el gancho no cambia nada.
* **Sin JavaScript** no se pierde nada raro: el panel manda por `fetch` al **mismo URL de la ficha**
  (`accion=edt_…`), y si el navegador no corre el JS, el POST normal **redirige a la ficha** con el
  aviso (`flash()`), que es el patrón de siempre del sitio.

---

## §4. 🧪 CÓMO SE PRUEBA (sin abrir el navegador)

```powershell
# 1) EL CAMINO COMPLETO: las 11 acciones, la puerta, la IA de verdad y limpieza total (24 comprobaciones)
python D:\RELAX\__sonda_run.py __ef_prueba.php sNd4-editar-ficha-9kQ7 go

# 2) LAS VISTAS: la ficha COMO ADMIN (trae el menú) y COMO PÚBLICO (no trae ni una marca), + el 403
python D:\RELAX\__sonda_run.py __ef_ver.php sNd4-ef-ver-9kQ7 go

# 3) 📐 VERLA Y MEDIRLA (sin abrirle nada al jefe): deja el HTML con el panel abierto en una pestaña
python D:\RELAX\__sonda_run.py __ef_pantalla.php sNd4-ef-pantalla-9kQ7 go "&tab=datos"
python D:\RELAX\__sup_bajar.py __ef_pantalla.html      # lo baja y lo borra del hosting
#    y para verla:  chrome --headless=new --window-size=390,844 --screenshot=... __ef_pantalla.html
#    (la pestaña de la foto se elige con &tab=datos|desc|prod|fotos)
```

**Lo que comprueba `__ef_prueba.php`** (24 de 24 el 2026-09-18): arma una tienda de prueba con 2
productos, **entra como admin y como público** (el público recibe `''`), guarda datos (nombre,
WhatsApp con `51` delante, teléfono, dirección, horario, redes, estado), comprueba que **la descripción
se limpia** (`<script>` fuera), **llama a la IA de verdad** para la tienda y para un producto, lista,
guarda, crea y borra productos, **sube dos fotos**, cambia la portada y borra una, **rebota el POST del
público (403)**, y al final **borra todo** (tienda, productos, fotos, archivos y opiniones) y lo
comprueba.

**Lo que comprueba `__ef_ver.php`**: que la ficha **como admin** trae el menú con sus 4 pestañas, los
botones 🤖 IA, el formulario del CSRF y **los datos de ESA tienda**; que la ficha **como público** no
trae **ni una marca** (`edt-caja`, `edt-sheet`, `data-edt-tab`, …) y sí lo de siempre (el nombre y el
botón de WhatsApp); y que un POST del público **no cambia la tienda**.

⚠️ **Dato del censo (2026-09-18):** las **1 711 tiendas activas usan la plantilla A**, así que las
ramas de las plantillas **B** y **C** se comprueban por código (las tres llevan la misma línea
`<?= $edt_en_X ?>`) pero no con tiendas reales de ese tipo.

---

## §5. 🔴 LOS BICHOS QUE YA MORDIERON (no repetirlos)

1. **El nombre del campo CSRF.** El campo del sitio se llama **`_csrf`** (`CSRF_TOKEN_NAME`), no
   `csrf`: el primer intento de la sonda mandaba `csrf=…` y el servidor respondía **419**. En el panel
   el token **no se arma por JavaScript**: va en un `<form id="edt-form">` del HTML y el JS lo lee con
   `new FormData(form)` (así el token viaja igual aunque cambie el nombre del campo).
2. **`strip_tags()` deja el CONTENIDO.** Un `<script>alert(1)</script>` en la descripción quedaba como
   el texto visible `alert(1)`. Antes de limpiar hay que **borrar el bloque entero** con su contenido
   (`preg_replace('#<(script|style|iframe|object|embed)\b[^>]*>.*?</\1\s*>#is', '', $texto)`).
3. **`img_guardar_subida()` exige una subida de verdad** (`is_uploaded_file()`), así que una sonda con
   un archivo fabricado no puede probar la subida: en modo prueba se le pasa
   `['exigir_subida' => false]` (igual que hace el Supremo con `datos['prueba']`).
4. **Las pestañas no pueden esconderse con scroll horizontal.** Con `overflow-x:auto` la cuarta
   pestaña (🖼️ Fotos) quedaba fuera de la pantalla del celular y no se veía: ahora **se envuelven**
   (`flex-wrap`) y las 4 se ven en dos filas a 390 px.
5. **El `static` del módulo hermano.** `invitacion_ficha_puede()` guarda su respuesta en memoria: al
   pintar tres fichas en la misma petición de sonda, el segundo resultado es el del primero. Para este
   módulo **no** se cachea (y la invitación se mide en su propia sonda).
6. **La vista `vista_negocios_activos`, no la tabla.** `plantilla_codigo` **no** es una columna de
   `directorio_negocios`: la sirve la vista. Buscar la plantilla en la tabla devuelve vacío y parece
   que «no hay tiendas de ese tipo».

---

## §6. REGISTRO

| Fecha | Qué se hizo |
|---|---|
| **2026-09-18** | **Módulo creado, desplegado y probado.** Pedido del jefe en una sola frase: el **menú de edición dentro de cada ficha** (solo para el súper admin) con **teléfono, descripción, productos y otras cosas más**, y **un botón 🤖 IA en la descripción y en cada producto** que reescribe esa área con IA. Se hizo con el patrón del botón 📨 (acción al principio de `negocio.php` + bloque que devuelve `''` para el público), reutilizando la **IA de verdad del sitio** (`tienda_ia_ia_descripcion` y `tienda_ia_ia_descripcion_producto`) y el motor de imágenes (`img_guardar_subida`). **Archivos:** `deploy/includes/editar_ficha.php` (nuevo) y `deploy/negocio.php` (4 líneas: la acción y las 3 plantillas). **Probado: 24 de 24** en el camino real (con IA de verdad y limpieza total) + las **vistas** (admin ve el menú · público no ve nada · el POST del público rebota con 403 y no cambia nada) + **la captura de la pantalla en el celular** con la hoja abierta en las pestañas Datos y Descripción. **Ninguna tienda de prueba quedó publicada.** |
