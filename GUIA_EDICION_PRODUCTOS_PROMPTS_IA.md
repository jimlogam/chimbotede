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


# GUÍA — EDITOR DE PRODUCTOS CON PROMPTS DE IMAGEN (IA) · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** cuando el jefe pida **trabajar las imágenes de los productos** (generarlas con
> una IA, cambiarlas, ponerles foto a los que no tienen), **corregir precios o unidades** en masa, o cuando
> haya que **cargar el siguiente lote de prompts**.
> **Página:** `https://dechimbote.com/editaproductos.php` (**solo admin** · herramienta de **escritorio**) ·
> **JS:** `assets/js/editor_productos.js` (`?v=2`) · **Motor de imágenes:** `includes/imagenes.php`
> (`GUIA_IMAGENES_Y_OPTIMIZACION.md`) · **Estado:** ✅ **EN PRODUCCIÓN** · **Lote 1 cargado y verificado** ·
> **v2 (editor masivo AJAX) verificada en vivo el 2026-09-11.**

---

## 0) LO ESENCIAL EN 30 SEGUNDOS

1. El jefe abre **Súper Admin → 🎨 Editar productos (IA)** (o `editaproductos.php`).
2. Ve **todos los productos por orden de llegada** (últimos creados primero), **20 por página** = **1 lote**.
3. Cada tarjeta trae, en una sola pantalla:
   - **la imagen actual** dentro de una **zona donde se ARRASTRA la imagen nueva** (se publica sola),
   - **📷/💾 Precio (input) + Unidad (select predictivo con fuzzy) + botón Guardar**,
   - **el prompt de generación** editable y copiable.
4. **NADA RECARGA LA PÁGINA**: guardar se hace por detrás (AJAX). Puedes trabajar el producto 22 o 27
   **sin perder el scroll** (era el pedido textual del jefe).
5. Los prompts los escribe el asistente y viven en **`directorio_producto_prompts`**; se cargan solos desde
   **`deploy/cache/prompts/lote_NN.json`**. **Lo que el jefe edite es suyo** (`origen='jefe'`): ningún lote
   lo pisa.
6. **↩️ Deshacer** devuelve la imagen anterior (se guarda 24 h) por si el arrastre fue al producto equivocado.

**Hoy (2026-09-11):** 9 579 productos · **929 con imagen** · **20 con prompt (lote 1)** · **9 559 pendientes**
(≈ 478 lotes de 20).

---

## 1) QUÉ PIDIÓ EL JEFE (con sus palabras)

**1.ª parte (2026-09-11):**

> *"Vas a crear un nuevo módulo llamado **edición de productos**… muéstrame en una página, se puede llamar
> `editaproductos.php`, **todos los productos por orden de llegada**, empezando por los últimos creados hasta
> los primeros. Muéstrame **la imagen actual** que tenga y **abajo un botón que diga cambiar imagen**, y
> **al costado** vas a crear un **prompt** (tú lo vas a crear)… **Los prompts tienen que estar basados en el
> contexto de cada imagen: a qué tienda pertenecen y en qué rubro está esa tienda**… **empecemos por unos 20**
> para no consumir muchos tokens y luego vamos por el siguiente lote de 20."*

**2.ª parte (2026-09-11, la versión actual):**

> *"Agrega la opción de **editar el precio** directamente mostrándolo como un **input**, y la imagen cámbialo
> con una opción de **arrastrar una imagen encima**… **cuando pongo una imagen la página me recarga** y como
> ya estoy por la imagen 22 o 27 tengo que volver a hacer scroll después de cada cambio: **no es necesario
> recargar la página cada vez que hago un cambio**, eso es muy importante… si **arrastro una imagen encima
> automáticamente se cambia**… el precio se guarda con **un botón Guardar por producto**… y a la **unidad**,
> me gustaría que sea para elegir en un **SELECT**… ponme todo el tipo de medidas posibles: kilo, pesos,
> metros, kilómetros, porciones, turnos, mililitros… **en el select usa FUZZY para predecir resultados**.
> …esta herramienta **solamente la voy a usar de escritorio**… es una herramienta de edición masiva, versión
> trabajo pesado en computadora."*

**El rubro manda en los prompts.** El ejemplo del jefe («collares hermosos» cambia por completo si la tienda
es de **veterinarias**, de **ropa de dama** o de **herramientas**) es el corazón del módulo: **todo prompt
dice explícitamente tienda + rubro + distrito**.

---

## 2) ARCHIVOS DEL MÓDULO

| Archivo | Qué hace |
|---|---|
| **`deploy/editaproductos.php`** | **La página + su API.** Listado por `s.id DESC` (orden de llegada), 20 por página, filtros, buscador, imagen actual con **zona de arrastre**, **precio (input) + unidad (combo fuzzy) + 💾 Guardar**, prompt editable/copiable, contadores y navegación por lotes. Las acciones contestan **JSON** cuando vienen del editor (`ajax=1`) y siguen funcionando como formulario clásico con recarga si no (respaldo). |
| **`deploy/assets/js/editor_productos.js`** | **El motor del navegador**: AJAX de todas las acciones (sin recargas), **arrastrar y soltar** + **Ctrl+V**, compresión previa con `CZImg.optimizar`, **combo de unidades con Fuse.js**, estado "cambios sin guardar", contadores en vivo y aviso al cerrar con cambios pendientes. ⚠️ **Al cambiarlo hay que subir el `?v=` de la página** (ahora **2**). |
| **`deploy/cache/prompts/lote_NN.json`** | **Los prompts del asistente**, un archivo por lote. Es lo único que se sube para "cargar trabajo nuevo". |
| `deploy/superadmin.php` | Una línea en el menú: **🎨 Editar productos (IA)**. |
| `includes/imagenes.php` | El motor de imágenes (WebP ≤1600 px + versiones de 800 y 300). Se usa tal cual. |
| `assets/js/imagen_optimizar.js` | `CZImg.optimizar()`: comprime la imagen **en la PC** antes de subirla. Sin cambios. |
| `directorio_producto_prompts` (BD) | **Los prompts.** La crea la propia página la primera vez que entra un admin. |
| `directorio_producto_imagenes_ant` (BD) | **El "deshacer"**: una fila por producto con la imagen anterior (se limpia sola a las 24 h). |

### 2.1 Las tablas

**`directorio_producto_prompts`**

| Columna | Para qué |
|---|---|
| `producto_id` (PK) | El producto de `directorio_servicios` |
| `prompt` (TEXT) | El prompt de generación de imagen |
| `origen` (`asistente` \| `jefe`) | **Quién lo escribió.** `jefe` = intocable para los lotes |
| `lote` | De qué lote vino (0 = escrito a mano por el jefe) |
| `rubro`, `negocio` | Contexto con el que se escribió (auditoría) |
| `creado_en`, `actualizado_en` | Fechas (desde PHP, zona `America/Lima`) |

**`directorio_producto_imagenes_ant`** (el deshacer)

| Columna | Para qué |
|---|---|
| `producto_id` (PK) | El producto cuya imagen se cambió |
| `ruta` | **La imagen anterior** (su archivo se conserva) |
| `ruta_nueva` | La que se publicó (se borra al deshacer) |
| `creado_en` | Cuándo. Pasadas **24 h** se limpia: la fila se borra y el archivo se elimina **si nadie más lo usa** |

---

## 3) CÓMO SE USA (flujo del jefe · escritorio)

### 3.1 La imagen: **arrastrar y soltar encima** (se publica sola)
1. Trae la imagen generada (o cópiala en Gemini) y **suéltala sobre la foto** de la tarjeta.
2. El área se ilumina (*"Suelta la imagen aquí"*), se ve **vista previa inmediata** y se publica
   **automáticamente** — **sin diálogo de confirmación y sin recargar**.
3. Barra de progreso → *"🔄 Imagen cambiada … KB en WebP (900×900 px)"* y el botón **↩️ Deshacer** aparece.
4. **Ctrl+V** publica la imagen del portapapeles en la tarjeta **señalada con el mouse** (borde azul).
5. Respaldo: el enlace **"elige el archivo"** abre el selector clásico.

### 3.2 Precio y unidad (botón **💾 Guardar** por producto)
- **Precio**: input numérico. **0 = "a consultar"** (como lo entiende todo el sitio).
- **Unidad**: **select predictivo con fuzzy** (Fuse.js). Al tocarlo se ven **todas** las opciones
  **agrupadas** (Conteo · Peso · Volumen · Largo y área · Distancia y carga · Tiempo y servicio ·
  Personas y comida · Del sitio · Las que ya usa el sitio) y **alfabéticas** dentro de cada grupo; al
  escribir filtra al instante y **tolera errores y tildes** (`kil` → *por kilo*, *por kilómetro*;
  `porci` → *por porción*) con el tramo coincidente resaltado. **↓ ↑** navegan, **Enter** elige,
  **Esc** cierra. Se puede escribir una unidad que no esté en la lista y se guarda igual.
- El **💾 Guardar** guarda **precio + unidad** de ese producto. Si hay cambios sin guardar, el botón se pone
  **ámbar** ("💾 Guardar · cambios sin guardar") y la tarjeta se marca; si intentas cerrar la pestaña, el
  navegador avisa. **Soltar una imagen NO guarda el precio** (son dos cosas independientes: el precio solo
  se guarda con su botón).
- La píldora verde **"🏷️ Guardado como: …"** muestra la unidad guardada y su **✕** devuelve el campo a esa
  unidad (Regla de Oro n.º 2).

### 3.3 Prompt
**📋 Copiar prompt** (un clic) · **💾 Guardar prompt** (queda `origen='jefe'`, sin recargar) ·
**↩️ Volver al del asistente** (re-aplica el texto del lote en el acto).

---

## 4) QUÉ HACE EXACTAMENTE "CAMBIAR IMAGEN"

Al soltar el archivo, el servidor:

1. **Valida y guarda** con `img_guardar_subida()` en **`fotos/<slug-de-la-tienda>/ia_<fecha>_<aleatorio>.webp`**
   (WebP ≤1600 px + versiones de 800 y 300 px). Antes, el navegador ya la comprimió con `CZImg.optimizar()`.
2. **Reemplaza la 1.ª foto de la galería** del producto (`directorio_producto_fotos`, la de menor `orden`);
   si no tenía galería, **la crea**.
3. **Actualiza la portada** (`directorio_servicios.imagen`), que es la que manda en todo el sitio.
4. **Guarda la anterior para deshacer** en `directorio_producto_imagenes_ant` y **NO borra su archivo**
   (hasta 24 h).
5. **Opcional**: si la imagen la comparten otros productos **de la misma tienda**, la casilla
   *"Esta imagen la comparten N productos…"* (desmarcada por defecto) la cambia también en ellos; **cada uno
   recibe su propio "deshacer"**.
6. **Al deshacer**: se restaura la ruta anterior (portada + galería), se borra la imagen que se acaba de
   quitar **solo si ya nadie la usa** y se elimina la fila del deshacer.
7. **Limpieza automática**: al abrir la página se podan los "deshacer" de más de 24 h (archivo incluido si
   ya no lo usa nadie). Así no se acumula basura en el disco.

> ⚠️ **Lo más delicado del módulo**: en una pollería **4 productos comparten la misma foto**. Ningún borrado
> se hace sin contar referencias (`ep_referencias_imagen()`, que **también cuenta los "deshacer"**; si no, al
> deshacer la foto ya no estaría en el disco). Verificado en vivo: se cambió la imagen del producto **9583**
> y los otros 3 siguieron mostrando la suya.

---

## 5) LOS PROMPTS: FORMATO DEL LOTE Y REGLAS DE CARGA

### 5.1 Formato de `cache/prompts/lote_NN.json`

```json
{
  "lote": 2,
  "creado": "2026-09-11",
  "nota": "por qué estos 20 y de dónde salieron",
  "prompts": [
    {
      "producto_id": 9563,
      "titulo": "Supervisión y Ejecución de Proyectos Civiles",
      "negocio": "R&L Construcciones y Servicios",
      "rubro": "Construcción y Remodelaciones",
      "prompt": "Imagen publicitaria para el catálogo de la tienda «…» (rubro: …, Chimbote, Perú).\n\nProducto: «…» — …\n\nEscena: … Formato cuadrado 1:1, alta resolución. Sin texto, sin marcas de agua, sin logotipos y sin rostros identificables."
    }
  ]
}
```

### 5.2 Reglas de la sincronización (automática, en cada carga de la página)

| Caso | Qué hace |
|---|---|
| El producto **no tiene** fila | **INSERT** con `origen='asistente'`, `lote=N` |
| Tiene fila **del asistente** y `lote_guardado <= lote_nuevo` y el texto cambió | **UPDATE** (permite **corregir** un lote ya cargado) |
| Tiene fila **del asistente** con `lote` **mayor** | No se toca (un lote viejo no pisa a uno nuevo) |
| Tiene fila con `origen='jefe'` | **Nunca se toca** |

- Es **idempotente**: recargar la página no duplica nada (verificado: 20 filas tras varias cargas).
- El botón **↩️ Volver al del asistente** devuelve el prompt a `origen='asistente'` y **re-aplica el texto
  del lote en el acto**, sin recargar.

### 5.3 El **prompt base automático**

Si el producto todavía no está en ningún lote, la página muestra un prompt base armado en PHP
(`ep_prompt_base()`) con **título + tienda + rubro + distrito** y la instrucción explícita *"interpreta el
producto dentro del rubro «X» (no en otro rubro)"*, marcado con **🧩 Prompt base (el asistente lo mejorará)**.

### 5.4 CÓMO SE CARGA EL SIGUIENTE LOTE DE 20 (procedimiento exacto)

```powershell
# 1) Leer el contexto real de los siguientes 20 SIN prompt (sonda temporal: se sube, se lee y se borra)
python __ep_run.py __ep_sonda_contexto.php __ep_sonda_contexto_resultado.json

# 2) Escribir los prompts en deploy\cache\prompts\lote_02.json  (formato de §5.1)

# 3) Subir SOLO el JSON
python __subir_uno.py cache/prompts/lote_02.json

# 4) Entrar como admin a la página: el lote se carga solo (INSERT idempotente)
```

- **`__ep_run.py`** sube cualquier `.php` de `D:\RELAX`, lo llama con la clave, guarda el JSON de respuesta
  y **borra el script del servidor** en el mismo paso (patrón de §3.1 de `GUIA_DESPLIEGUE_Y_ENTORNO.md`).
- **`__ep_sonda_contexto.php`** devuelve el **siguiente lote** (con descripción, precio, unidad, tienda,
  rubro, distrito, imagen y n.º de fotos), los **lotes ya cargados** y los **últimos 20 sin filtrar** con la
  marca `tiene_prompt`.
- **Regla de oro del lote: los 20 son correlativos por `id`** (orden de llegada). El lote 1 fueron los ids
  **9583 → 9564**; el siguiente sin prompt son **9563 → 9544**.

### 5.5 CÓMO SE ESCRIBE CADA PROMPT (criterio del asistente, no improvisar)

1. **Contexto del negocio**: *"Imagen publicitaria para el catálogo de la tienda «X» (rubro: Y, distrito,
   Perú)."* → **nunca se omite**: es lo que evita que la IA se equivoque de rubro.
2. **Producto exacto**: el título entre «» + los atributos reales de la **descripción**.
3. **Aclaración cuando el título engaña**: si es un **servicio** (curso, mensualidad, inscripción, masaje,
   alquiler, envío, préstamo, empleo) se dice **explícitamente** *"OJO: no es un objeto, es un SERVICIO"*.
4. **Escena**: dónde, con qué, ángulo, luz.
5. **Cierre técnico fijo**: *"Formato cuadrado 1:1, alta resolución, fotografía realista. Sin texto, sin
   marcas de agua, sin logotipos y sin rostros identificables."*
6. **Marcas registradas**: nada de escudos, logos ni interfaces (Netflix, clubes): se describen de forma
   genérica. Además de evitar problemas legales, es lo que hace que la IA **no rechace** el prompt.
7. **Fotos de comida**: apetitosas y realistas. **Personas**: solo si el rubro lo pide y **sin rostros**.

---

## 6) VERIFICACIÓN REAL (medido en producción)

### 6.1 Lote 1 y página base (2026-09-11)

| Qué | Resultado |
|---|---|
| `editaproductos.php` sin sesión | **HTTP 302** → `login.php?redirect=%2Feditaproductos.php` |
| Página con sesión de admin | **20 tarjetas** y contadores reales |
| Carga automática del lote 1 | 20 filas (`origen='asistente'`, `lote=1`, ids **9564–9583**), **0 duplicados** |
| 💾 Guardar prompt / ↩️ restaurar | Guardado, marcado como del jefe y devuelto al del lote (probado y revertido) |

### 6.2 Editor masivo v2 (verificado el 2026-09-11, todo revertido)

| Qué | Resultado (evidencia) |
|---|---|
| **Ninguna acción recarga la página** | Se dejó una marca en `window` antes de cada acción y **siguió viva después** en las 4 pruebas (guardar precio/unidad, soltar imagen, deshacer, prompt) → la página **no se recargó** |
| **Precio + unidad sin recargar** | `15.00 por promoción` → `15.50 por porción` → chip, `data-precio`, píldora y botón actualizados en el acto; mensaje *"💾 Precio y unidad guardados."* |
| **Estado "cambios sin guardar"** | El botón pasa a **ámbar** y la tarjeta se marca al editar; vuelve a "💾 Guardar" al guardar |
| **Combo de unidades con fuzzy** | `kil` → *por kilo*, *por kilómetro*, *por millar*, *por kit*, *por cilindro*, *por mililitro*, *por milímetro*, *por perfil*; `porci` → *por porción*, *por cien*, *por ciento*…; al abrir sin escribir: **110 opciones en 10 grupos alfabéticos** |
| **Arrastrar y soltar** | `dragenter/dragover` encienden el velo *"Suelta la imagen aquí"*; al soltar, vista previa inmediata, *"🔄 Imagen cambiada … 3 KB en WebP (900×900 px)"*, chip 🖼️, contador de imágenes actualizado, botón ↩️ Deshacer, **sin diálogo de confirmación** |
| **↩️ Deshacer** | Devolvió la imagen anterior exacta (la que el jefe había publicado), borró la de prueba (HTTP **404**) y quitó el botón. **Salvó una imagen real del jefe durante las pruebas** |
| **Imágenes compartidas** | La imagen vieja **`brasas_01.webp` sobrevivió** (165 464 bytes) y los otros 3 productos siguieron mostrando la suya |
| **Prompt sin recargar** | Guardado → chip *"✍️ Tu prompt"* y botón de restaurar creado al vuelo; restaurar → volvió el texto del lote y el chip *"✨ Prompt del asistente"* |
| **Cero residuo** | `directorio_producto_imagenes_ant` **vacía**, prompts **20 filas** `origen='asistente'`, precio/unidad del producto de prueba en su valor original y **las 3 imágenes que publicó el jefe intactas** |
| `php -l` / `node --check` | Página y JS **sin errores** |

---

## 7) TRAMPAS Y ERRORES (no repetirlos)

1. **Al cambiar `editor_productos.js` hay que subir el `?v=` en `editaproductos.php`** y volver a subir la
   página: el hosting manda `Cache-Control: max-age=604800` (7 días) a los JS. Hoy va en **`?v=2`**.
2. **Las herramientas del navegador actúan sobre la pestaña ACTIVA** y el jefe cambia de pestaña: **pasar
   siempre `tabId`** (un `evaluate` acabó corriendo en `superadmin.php?seccion=banners`).
3. **El evaluador del navegador corta las promesas a ~100 ms**: nada de `await` largos; para fabricar un
   archivo de prueba, generar la imagen **en síncrono** (`canvas.toDataURL()` → `atob` → `File`).
4. **`form.submit()` NO dispara el evento `submit`** (se saltaría la compresión): usar **`requestSubmit()`**.
5. **Sin `display_errors` una sonda temporal esconde el error real**: usar el patrón de `__ep_diag.php`
   (pasos con `try/catch` y errores visibles) y comprobar el estado real antes de dar algo por hecho.
6. **`-800.webp` no siempre existe y está bien**: el motor **no crea una versión que no aporte** (una imagen
   de 800 px no genera su propia versión de 800). `img_srcset()` solo anuncia lo que existe.
7. **`cache/prompts/` no se sirve por web** (el hosting responde **403**): correcto, PHP lo lee del disco.
8. **El jefe trabaja en paralelo**: durante las pruebas del 2026-09-11 publicó **3 imágenes nuevas** en los
   mismos productos de la página 1. Antes de probar, **leer el estado real** (y usar el "deshacer" si algo
   se pisa): una prueba mía reemplazó su imagen y **el ↩️ Deshacer la devolvió tal cual**.
9. **Sondas temporales**: se borran del servidor en el mismo paso (comprobado: **404**). Los scripts de
   prueba viven en `D:\RELAX`, **nunca** dentro de `deploy/`.

---

## 8) PENDIENTES (con el siguiente paso concreto)

- [ ] **Lotes 2…479**: `python __ep_run.py __ep_sonda_contexto.php` (devuelve los ids **9563 → 9544**) y
      escribir `deploy/cache/prompts/lote_02.json`. **De 20 en 20** (orden del jefe).
- [ ] **Probar la casilla "aplicar también a los otros N"** con el nuevo flujo de arrastre: hoy **no se ha
      probado en vivo** (cambia varios productos de golpe; cada uno recibe su propio "deshacer", pero
      conviene verificarlo con el jefe delante).
- [ ] **Preguntar al jefe si quiere un filtro "sin imagen primero"** para atacar antes los ~8 650 productos
      sin foto (hoy el orden es estrictamente por llegada).
- [ ] Decidir si el módulo se abre también a los **dueños de tienda** (hoy es solo admin).
- [ ] Decidir si se quiere **aviso a Telegram** cuando se cambia una imagen (hoy no avisa: en una tanda de 20
      cambios sería el jefe avisándose a sí mismo).
- [ ] Medir el efecto de las imágenes nuevas en los clics (`lead_score` / estadísticas).

---

## 9) HISTORIAL DE LOTES

| Lote | Fecha | Productos (ids) | Contenido |
|---|---|---|---|
| **1** | 2026-09-11 | **9583 → 9564** (20) | Los 20 productos más recientes por orden de llegada: pollería Brasas & Leña (5), polera de ropa deportiva, cursos de globos/repostería, transporte de pasajeros y encomiendas, masajes, streaming, habitaciones de alquiler, vehículos (2), construcción y vidriería, turrón, empleo minero, caritas pintadas, accesorios, desayunos y flores. |

---

## 10) HISTORIAL DE VERSIONES DE LA PÁGINA

| Versión | Fecha | Qué agregó |
|---|---|---|
| v1 | 2026-09-11 | Página con listado por orden de llegada (20/página), imagen actual + **📷 Cambiar imagen**, prompt editable/copiable, filtros, tabla de prompts y carga de lotes desde JSON. |
| **v2** | 2026-09-11 | **Editor masivo**: **nada recarga la página** (AJAX en todo), **arrastrar y soltar** la imagen encima (publicación automática) + **Ctrl+V**, **precio como input**, **unidad en select predictivo con fuzzy** (Fuse.js, 110 opciones en 10 grupos), **💾 Guardar por producto**, **↩️ Deshacer** (tabla `directorio_producto_imagenes_ant`, 24 h), aviso de cambios sin guardar y contadores en vivo. |

---

## 11) RE-EDITAR LOS PRODUCTOS DE UNA TIENDA (título, descripción, precio, unidad, tipo, imagen)

> **Cuándo:** el jefe manda el enlace de una ficha y pide *«revisa sus productos y vuelve a editarlos»*.
> Es el hermano de escritura del editor de arriba: los mismos campos que guarda **`productos.php?n=<ID>`**
> (`titulo · tipo_producto · descripcion · precio · unidad · destacado · activo`), pero **sin navegador**.

**Cuándo pasa de verdad (caso real, 2026-09-17 · ficha 1751 `miru-snack-bar-chimbote`):** cuando la ficha
nació de una **fusión por teléfono** (7 avisos del mismo negocio), el motor solo salta productos con el
**mismo título**, así que entran **29 productos que dicen casi lo mismo** con otras palabras (9 «carrito de
snacks», 4 «pop corn», 4 «manzanas», 4 «panchos»…) y **fotos repetidas** en la galería. La revisión es:
**un producto por servicio REAL** (nada inventado), títulos que no se pisen, descripciones propias, unidad
del catálogo (`ep_unidades_catalogo()`: `por servicio`, `por evento`, `por paquete`, `por combo`, `por
unidad`, `por docena`…), `tipo_producto` correcto (`fisico` = producto, `virtual` = servicio) y **una foto
distinta para cada producto visible**.

| Paso | Qué |
|---|---|
| 1 | **Leer el estado real** con una sonda de solo lectura (`id`, título, descripción, precio, unidad, tipo, `activo`, `destacado`, `imagen`, fotos de cada producto + fotos de la galería). |
| 2 | **Escribir el payload** (`__miru_payload.json`): `productos[]` (con `id`), `desactivar_productos[]`, `borrar_fotos[]`. |
| 3 | **Simulacro**: `python __miru_run.py sim` (valida que cada `id` sea de esa tienda, que exista el archivo de la imagen nueva y que **la foto a borrar esté repetida**; si hay un error **no escribe nada**). |
| 4 | **Escribir**: `python __miru_run.py go` (sube la sonda a `/`, manda el payload por POST y **borra la sonda** al terminar). El parte devuelve el estado **después**. |
| 5 | **Comprobar por HTTP** la ficha (los títulos salen **3 veces**: son las 3 plantillas A/B/C, no duplicados). |

- **Repetidos: se APAGAN (`activo = 0`), no se borran** — el sitio ya oculta los inactivos
  (`obtener_productos_negocio()` exige `activo = 1`) y así el jefe puede recuperar cualquiera. Borrarlos de
  verdad es decisión suya.
- **⚠️ Nunca borrar archivos al limpiar la galería:** las **mismas rutas** viven en `directorio_fotos`
  (galería de la tienda) **y** en `directorio_producto_fotos` (galería del producto). El borrado de filas
  repetidas de `directorio_fotos` lo hace **solo la fila**, y únicamente si **otra fila de la misma tienda usa
  la misma ruta** (lo comprueba la sonda). El botón 🗑️ de `productos.php` **sí borra el archivo**: para la
  portada compartida sería un tiro en el pie.
- **Herramientas (plantilla reutilizable):** `__miru_editar.php` (la sonda escritora, clave propia y
  `&go=1`), `__miru_run.py` (sube, postea el payload por base64 y borra la sonda), `__miru_sonda.php`
  (lectura del estado). En el mismo payload van las **fotos repetidas** de la galería.
- **Resultado medido:** ficha **1751** → 38 productos → **17 activos** (4 destacados: carrito, estación con
  atención, promoción S/ 450 y algodón en domo), **21 apagados**, galería **40 → 34 fotos**, y **0 productos
  compartiendo foto**.

---

## 12) ✍️ EL COPY DE LOS PRODUCTOS EN LOTE (título + descripción) — 2026-09-20

> **Encargo del jefe:** *«busca los productos de las tiendas que se han agregado, de las últimas 10 al
> menos o 15 tiendas, y a sus productos agrégale mejor copy y que tengan también posibilidad de aparecer
> en el buscador»* (el caso que lo disparó: una cevichería con «Chicharrón de pescado» publicado que no
> salía al buscar).

**Qué se encontró en las 15 últimas tiendas (1948 … 1962 · 212 productos):** descripciones de **hasta
2.654 caracteres** (promedio 1.274), 89 fichas empezando con **grito de volante** (`🎈 ¡TRANSFORMA TU
SALÓN…!`), **62 con el precio escrito dentro del texto** («a S/ 200 soles»), 55 títulos **sin tildes**
(«Decoracion», «iluminacion»), 58 títulos de más de 70 caracteres, teléfonos y URLs metidos en la
descripción, y **5 productos de FestJim con el mismo título y la misma descripción** (cada uno con 3 fotos
distintas: son 5 decoraciones diferentes).

**Las reglas del copy nuevo (sirven para cualquier lote):**

| Campo | Regla |
|---|---|
| **Título** | 28-68 caracteres · empieza por el nombre común con el que la gente busca («Chicharrón de pescado», «Alquiler de sillas blancas») · zona local **una sola vez** y solo si el original la trae · **prohibido**: precios, teléfonos/WhatsApp, emojis, MAYÚSCULAS de grito, signos de admiración, el nombre de la tienda y palabras de volante (*oferta, barato, económico, promoción, a solo*) |
| **Descripción** | 150-320 caracteres · 1-3 frases · qué es, qué incluye y para qué sirve, **con los datos reales del original** (colores, medidas, materiales, marca, zona) · mayúscula inicial y punto final · prohibido precios, teléfonos, emojis y promesas exageradas · **nada que no esté en el original** |
| **Intocable** | `precio`, `unidad`, `activo`, `destacado`, `tipo_producto`, `imagen` y las fotos. La sonda escribe **solo** `titulo` y `descripcion` |
| **Palabras locales** | se conservan tal cual (quino, pellejón, combinado, pinta caritas, tacho par LED, cilindro, grass) — son palabras que la gente escribe |

**Cómo se hizo (receta reutilizable, en lotes de ~30-50 productos):**

```powershell
# 1) Los últimos N negocios con TODOS sus productos (sonda de solo lectura, se borra sola)
python __sonda_run.py __ep_ult15_productos.php ult15-prod-2026-9kQ7 go
# 2) Partir en lotes + escribir el copy (aquí: 6 subagentes en paralelo, uno por lote)
python __copy_lotes.py                    # -> __copy_lote<N>_entrada.json
#    (cada lote devuelve __copy_lote<N>_salida.json: [{"id","titulo","descripcion"}, …])
# 3) VALIDAR antes de tocar la base (ids, largos, precios, teléfonos, emojis, tildes, nombre de tienda)
python __copy_check.py
# 4) Armar la sonda escritora con el copy embebido y aplicarla con SIMULACRO primero
python __copy_gen_php.py                  # -> __ep_copy_aplicar.php
python __sonda_run.py __ep_copy_aplicar.php copy15-prod-2026-9kQ7 x      # simulacro
python __sonda_run.py __ep_copy_aplicar.php copy15-prod-2026-9kQ7 go     # aplicar
```

- La sonda **solo toca ids de esas tiendas** (`AND negocio_id IN (…)`), informa cuántos encontró, cuáles
  están de baja y cuáles no existen, y **relee 3 filas** después de escribir (el «parte» que se lee).
- **Resultado medido (2026-09-20):** **212 de 212** filas actualizadas (descripciones de ~1.274 → ~250
  caracteres), y los 5 gemelos de FestJim con copy propio, **mirando sus fotos** para no inventar
  diferencias (19 años con neón turquesa · lila/azul/dorado en terraza · verde agua con rosas · fucsia,
  blanco y turquesa · azul marino y plata sobre grass). Herramienta de los gemelos: `__copy5_gen.py`.
- El copy se ve en **tres sitios**: la página del producto (`/producto/<id>`), su `meta description` y las
  tarjetas del buscador. Verificado por HTTP en `producto/12933`, `producto/12899` y `producto/12911`.

⚠️ **Lo que le faltaba al buscador para que estos productos salgan** (no era solo el copy): está en
`GUIA_BUSCADOR_FUZZY.md` **§4.11** — el escalón que busca por título de producto, la tolerancia de plural
y género, la escalera relajada y el orden «gana el que tiene más palabras en común».

---

_Última revisión: 2026-09-11 (v2 verificada en producción) + **§11 del 2026-09-17** (re-edición de los
productos de una tienda fusionada, probada en la ficha 1751) + **§12 del 2026-09-20** (copy en lote de las
15 últimas tiendas: 212 productos). Reglas base: `REGLAS_DE_ORO_PROYECTO.md` ·
`GUIA_DESPLIEGUE_Y_ENTORNO.md` · `GUIA_IMAGENES_Y_OPTIMIZACION.md`._
