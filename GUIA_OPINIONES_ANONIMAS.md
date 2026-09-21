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


# 💬 GUÍA DEL MÓDULO DE OPINIONES ANÓNIMAS Y SUS RECLAMOS — dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** cuando el jefe pida tocar **las opiniones de las tiendas** (el «chat de
> opiniones» de la ficha), **sembrar opiniones** en tiendas nuevas, o **el panel de reclamos**.
> **Qué resuelve:** el módulo completo — la pestaña de opiniones en la ficha, el formulario anónimo,
> el botón 🚩 Reportar, la pantalla de Súper Admin que decide entre 🗑️ borrar y 🙈 ignorar, y la
> siembra de las 3 opiniones con contexto de las últimas 100 tiendas.
> **Estado:** ✅ EN PRODUCCIÓN · **Última revisión: 2026-09-14**

---

## 0) LO QUE PIDIÓ EL JEFE (2026-09-14, textual y resumido)

1. **En la ficha, en ESCRITORIO:** la descripción y las opiniones en **dos pestañas**, una al costado
   de la otra. Al entrar se ve la descripción con sus **primeras 100 palabras** y el botón
   **«Ver más»**; en la segunda pestaña, un **chat anónimo** donde cualquiera deja su opinión.
2. **Debajo de cada opinión, un botón «Reportar».**
3. **Los reportes llegan al Súper Admin** en una sección de **«Reclamos de opiniones»**, con **dos
   botones** para decidir rápido: **🗑️ borrar** o **🙈 ignorar**.
4. **A las últimas 100 tiendas creadas** (las más recientes, en orden cronológico) escribirles
   **3 opiniones positivas CON CONTEXTO**, hablando de **lo que esa tienda vende de verdad**
   (pollería → el pollo y las papas; ferretería → el trato y el orden; carnicería → la limpieza y los
   cortes). *«Si es que sale todo perfecto ya luego pasamos a los siguientes negocios»*.

---

## 1) DÓNDE ESTÁ CADA COSA

| Archivo | Qué hace |
|---------|----------|
| `deploy/includes/opiniones.php` | **El motor**: instala las tablas, lee/crea opiniones, reporta, borra, recalcula el rating, corta la descripción en palabras y **pinta el bloque de pestañas** (HTML + CSS + JS propios). **No se pide por URL** (es un `include`). |
| `deploy/includes/opiniones_semilla.php` | **Los textos de la siembra**: 99 tiendas × 3 opiniones = **297 opiniones** con contexto. Devuelve un array de PHP. |
| `deploy/includes/vista_opiniones_admin.php` | La pantalla del Súper Admin **«🚩 Reclamos de opiniones»** (datos + vista). |
| `deploy/negocio.php` | Llama al bloque y lo pinta **en la plantilla activa** (A, B o C). Reemplazó a las viejas secciones «📝 Sobre nosotros». |
| `deploy/api/reportar.php` | El endpoint. Con `que=opinar` publica una opinión; con `que=reportar_opinion` la reporta. **Sin `que`** sigue siendo el reporte de contenido de siempre. |
| `deploy/superadmin.php` | La pestaña **🚩 Reclamos de opiniones** (con globito de pendientes) y las 4 acciones POST (`opiniones_instalar`, `opiniones_sembrar`, `opinion_reporte_borrar`, `opinion_reporte_ignorar`). |
| `__sonda_opiniones_seed.php` (+ `python __sonda_run.py __sonda_opiniones_seed.php op-seed-2026-9kQ7zz [go]`) | Sonda temporal: informa y **siembra** (sin `go` es simulacro). Se borra sola del hosting. |
| `__sonda_op_vista.php` | Sonda temporal: **pinta la pantalla de reclamos** y devuelve las marcas (sirve para comprobar que no revienta sin entrar como admin). |
| `__sonda_op_limpieza.php` | Sonda temporal: **borra la opinión de prueba** que deja `__prueba_op_api.py`, prueba los dos caminos del panel con datos desechables y vuelve a dejar el reporte de demostración. |
| `__prueba_op_api.py` | Prueba de punta a punta contra el sitio vivo: abre una ficha con cookies, saca el CSRF, **publica una opinión marcada** y **la reporta** (dos veces, para ver que el duplicado se rechaza). Se limpia con la sonda de arriba. |
| `__ver_ficha_opiniones.py <slug>` | Mira una ficha servida por HTTP y cuenta las pestañas, las opiniones pintadas y los botones 🚩 (sin abrir el navegador). |
| `__op100_cosechar.py [cuantas]` · `__op100_tiendas.json` | **La cosecha**: saca del sitio las **últimas tiendas creadas** (slug, rubro, distrito, **productos reales**) y las guarda. Es lo que se lee para escribir la semilla de la siguiente tanda. |
| `__semilla_check.py` | Comprueba que la semilla cubra **exactamente** las tiendas cosechadas, que los **slugs existan** y que **cada tienda tenga 3** opiniones. |

### 1.1 Por qué el motor es un `include` y las opiniones van dentro de `api/reportar.php`

No es capricho: **`api/reportar.php` ya es «el de los reportes»** — tiene CSRF, tope diario por IP y
aviso al jefe por Telegram. Las opiniones anónimas y sus reportes **cuelgan del mismo sitio** en vez de
abrir una puerta nueva que habría que blindar aparte. Si algún día se separan en `api/opinar.php`, hay
que llevarse **también** el CSRF, el tope por IP y el aviso.

---

## 2) LA BASE DE DATOS

- **`directorio_opiniones`** (la tabla de siempre, la comparten los módulos viejos). Columnas reales:
  `id`, `negocio_id`, `usuario_id`, `autor`, `rating`, `fecha`, `texto`, `respuesta`, **`fuente`**.
  - `fuente` distingue de dónde salió cada opinión: **`semilla`** (las que sembramos),
    **`visitante`** (las del chat anónimo), `web` y `google` (las históricas del módulo viejo).
    ⚠️ **Al escribir, el motor consulta primero `SHOW COLUMNS`** y solo manda las columnas que existen:
    así el módulo no se rompe si el esquema cambia.
- **`directorio_opiniones_reportes`** (nueva, la crea el propio módulo):
  `id`, `opinion_id`, `negocio_id`, `motivo`, `texto`, `ip`,
  `estado` (`pendiente` · `opinion_borrada` · `ignorado`), `creado_en`, `atendido_por`, `atendido_en`.
- **`directorio_negocios.rating`**: se **recalcula** con el promedio real de las opiniones
  (`opiniones_recalcular_rating()`), pero **solo si la tienda tiene al menos una opinión** (una ficha
  con el rating puesto a mano y sin opiniones detrás **no se toca**). Ningún otro código escribe ese
  campo, así que el recálculo no pisa nada.
- **No hay `migrar_*.php`**: las tablas se crean **solas** al primer uso y desde el botón
  **⚙️ Preparar tablas** del panel (patrón de auto-instalación defensiva del proyecto).

---

## 3) LA FICHA: LAS DOS PESTAÑAS

- Bloque `fop` (`ficha_descripcion_opiniones_html()`): **📝 Descripción** y **💬 Opiniones** (con el
  número). En **escritorio** son dos pestañas a todo lo ancho, una al costado de la
  otra; en **celular** se apilan igual (fuente ≥16 px y botones cómodos de tocar).
- 🎨 **LA PESTAÑA DE OPINIONES VA EN NARANJA (2026-09-16, pedido del jefe: «dale un color más notorio al
  tab de opiniones»).** Lleva la clase `fop__tab--opi`: en reposo **fondo naranja suave** (`#fff3e6`) con
  el texto en naranja oscuro y el contador en un globito blanco; **elegida**, se enciende con el
  **degradado de los CTA de la casa** (granate → rojo → naranja) y el texto en blanco. La de Descripción se
  queda en el granate discreto.
- 🆕 **LA DESCRIPCIÓN VA COMPLETA, DE FRENTE (2026-09-16, pedido del jefe: «elimina la descripción corta,
  muestra de frente la descripción completa»).** Antes se pintaban las **primeras 100 palabras** y un botón
  **«Ver más ▾»**: eso **ya no existe** (el recorte `ficha_primeras_palabras()` y el botón se retiraron del
  marcado; el JS viejo quedó por si alguna ficha en caché lo trae). Se pinta **siempre** la descripción
  entera con su formato: títulos, listas, colores `cz-*` y **botones de WhatsApp**, tal como los genera
  `descripcion_negocio_html()`. ⚠️ Si algún día se vuelve a recortar, recordar que el recorte cambia las
  etiquetas de **bloque** por un espacio antes de quitar el resto: si se quitan «a secas», las palabras se
  pegan («Los CipresesPROYECTARQ…»).
- **Opiniones**: la pestaña arranca **DIRECTA con las opiniones** (sin encabezado ni párrafo de
  explicación: el jefe los mandó borrar, ver abajo) — apodo, estrellas, fecha, el texto tal como lo
  escribió la persona y, debajo, **🚩 Reportar**. **El formulario ya no se ve ahí** (ver §3.1bis) y el
  **botón «Escribe tu opinión» va DEBAJO de las opiniones**.
- 🗑️ **FUERA EL ENCABEZADO Y EL PÁRRAFO DE ARRIBA (orden del jefe, 2026-09-16):** *«hay un texto que dice
  opiniones de clientes… esos textos totalmente bórralo, no sé por qué lo pones, no sirve de nada; borra
  eso y pon las opiniones inmediatamente»*. Se retiraron el `<h2>💬 Opiniones de clientes</h2>` y el
  `p.fop__intro` («Este es un chat de opiniones anónimo… no necesitas cuenta…»), y también su CSS.
- 🗑️ **SIN EL CÍRCULO CON LA INICIAL (misma fecha):** *«cuando se escribe la opinión no le pongas esos
  redonditos, porque el nombre de la persona que está comentando ya se encuentra dentro del comentario»*.
  Se quitó `div.fop__avatar` del marcado **y del JS** que arma la opinión recién publicada (si solo se
  quitara del marcado, la opinión nueva saldría distinta a las demás), y la burbuja pasó a tener las
  4 puntas iguales (`border-radius:14px`) porque ya no hay círculo al costado.
- 📱 **A TODO EL ANCHO EN EL CELULAR, SIN BORDES A LOS COSTADOS (misma fecha):** *«trata de ocupar todo el
  ancho de la pantalla del celular… algo más ancho, no le pongas borde a los costados»*. En
  `@media (max-width:700px)` el bloque se estira con **márgenes negativos** que compensan el padding de
  16 px del `main` (`margin-left:-16px; margin-right:-16px`) y se le quitan **el borde y las puntas
  redondeadas de los costados** (`border-left:0; border-right:0; border-radius:0`).
  ⚠️ **Se hace con márgenes negativos y NO con `100vw`: `100vw` incluye el ancho de la barra de scroll y
  mete scroll lateral.** Medido en celular: el bloque queda en `left=0` con 469 px de ancho y el documento
  en 477 px ≤ 484 del viewport ✔ (sin scroll lateral). En PC no cambia nada (84 px de margen, 1168 px).
  La medición se hace con **`__fop_ancho_medir.py`** (mide el bloque y sus contenedores en el navegador
  headless y lo deja en el `<title>`).
- **El bloque se pinta SOLO en la plantilla activa** (`data-plantilla`): las 3 plantillas viajan en el
  HTML y las otras dos están ocultas por CSS, así que pintarlo en las tres duplicaría el chat entero.
- Si la tienda **no tiene descripción** el bloque sale **sin pestañas** (solo opiniones), y si no tiene
  **ni descripción ni opiniones** el bloque **no se pinta**.
- El **CSS y el JS del componente viajan dentro del propio bloque** (la regla del proyecto: componente
  puntual → CSS inline; así **no hay que subir ningún `?v=`** y la caché de 7 días no se come el cambio).

### 3.1bis ✍️ EL BOTÓN «ESCRIBE TU OPINIÓN» Y SU VENTANA MODAL (2026-09-16, pedido del jefe)

> Textual del jefe: *«las opiniones deben ser eso, opiniones… ponle un botón de agregar opinión que abre un
> pop up para que puedan agregar su opinión; no muestres el bloque de agregar opinión, solo el botón que
> abre un pop up o ventana modal y ahí pueden opinar.»* Y después: *«escribe de frente las opiniones y
> abajo el botón que dice escribe tu opinión»* → **el botón va DEBAJO de la lista de opiniones**, no arriba.

- **Qué cambió:** el formulario (`<form data-fop-form>`) **salió de la pestaña** y vive **dentro de un
  modal** (`div.fop-modal[data-fop-modal]`, `hidden`). En la pestaña solo queda el botón.
- **El botón es una IMAGEN**: `assets/img/boton-escribe-tu-opinion.webp`, hecha a partir del archivo que
  mandó el jefe (`6833da24-…jpg` en Descargas). Se pinta con `img_tag()` dentro de
  `button.fop__btn-opinar[data-fop-abrir]` (ancho máximo 320 px, sombra suave, se levanta al pasar el
  mouse).
  - 🔧 **Cómo se recortó y se le bajó el peso (44 KB → 11.9 KB) — la receta está en `__boton_opinar.py`:**
    (1) se detecta el fondo gris por las esquinas y se recorta al botón; (2) el fondo se vuelve
    **transparente** con un alfa limpia (y a los píxeles transparentes se les pone un **color plano**, si
    no el ruido del JPEG se guarda igual); (3) ⚠️ **el alfa se cuantiza a 8 niveles DESPUÉS de
    redimensionar**: si se cuantiza antes, el reescalado vuelve a crear degradados y el archivo pasa de
    12 a 30 KB. Sin la cuantización el WebP pesa 35 KB (el RGB solo pesa 8 y el alfa 7, pero juntos
    explotan). Referencia de la casa: el botón «CONSULTAR PRODUCTO» pesa 15.2 KB.
- **El modal**: fondo oscuro con desenfoque, caja blanca de 460 px máximo, **✕**, estrellas, apodo,
  texto y «Publicar opinión». Se cierra con la ✕, **tocando el fondo**, con **Escape**, y **solo** 1.5 s
  después de publicar (así el visitante alcanza a ver el «✅ ¡Gracias!» y su opinión apareciendo arriba).
  Mientras está abierto, `body.fop-sin-scroll` bloquea el scroll de atrás.
- ⚠️ **TRAMPA: el modal se MUDA al `<body>` al arrancar el JS** (`document.body.appendChild(modal)`).
  Dentro de la ficha `position:fixed` queda **recortado** por antepasados con recorte/contexto (`.fop`
  lleva `overflow:hidden`) y la ventana no puede salir del bloque. **Si algún día se pinta otro modal
  dentro de un componente, hacer lo mismo.**
- ⚠️ **Cuidado al medir en headless:** con `--window-size=430,900` el render reporta un **viewport de
  484 px** (no 430) y la captura de 430 px **corta los últimos 54 px**: parece que el modal se sale de la
  pantalla cuando en realidad mide 456 + 14 de margen = **470 ≤ 484** ✔. Se comprueba **midiendo**
  (`__opinar_medir.py` escribe la geometría en el `<title>` y se lee con `--dump-dom`), no a ojo con la
  captura.

### 3.1 Publicar una opinión (visitante, sin cuenta)

- Formulario: **estrellas (1-5, por defecto 5)**, **nombre o apodo (opcional** — si se deja vacío sale
  **«Anónimo»**) y el texto (**10 a 800 caracteres**).
- **Nada recarga la página**: se publica por AJAX y la opinión **aparece arriba del chat al instante**
  (con su botón 🚩 Reportar y todo, porque el bloque se arma con el mismo código que usa el servidor).
- **Candados**: la tienda tiene que existir y estar **activa**; **5 opiniones por IP al día**;
  `strip_tags` + se **borran los enlaces** del texto (una opinión no es un cartel publicitario).

### 3.2 🚩 Reportar una opinión

- El botón abre, **debajo de la misma opinión**, un cuadro con el **motivo** (`opinion_motivos()`:
  lenguaje ofensivo, información falsa, spam/publicidad, no tiene que ver con la tienda, publica datos
  personales, otro) y un texto opcional.
- **Tope: 3 reportes por IP al día** y **un solo reporte pendiente por opinión e IP** (el segundo
  intento responde *«Ya reportaste esta opinión»*).
- Cada reporte **avisa al Telegram del jefe** (respeta el interruptor **🚩 Reportes de contenido** del
  panel 📱 Telegram) con el negocio, el motivo, lo que explicó quien reportó y las dos salidas.

---

## 4) EL PANEL: SÚPER ADMIN → 🚩 RECLAMOS DE OPINIONES

`superadmin.php?seccion=opiniones` (lleva **globito** con los reportes pendientes en el menú).

La pantalla tiene **dos partes**, y la primera se añadió el 2026-09-15 porque el jefe pidió ver **todo**:
*«yo debo poder ver todo lo referente a esta nueva categoría de opiniones: desde que dejaron una opinión
o reportaron una opinión, con su respectivo botón de borrar o aprobar»*.

### 4.1 💬 LA LISTA DE OPINIONES (todo lo que se escribe en las fichas)

- **4 números**: opiniones en el sitio · **de visitantes** · de la siembra · **🚩 reportes por decidir**.
- **Filtros** (chips, uno a la vez): **💬 Todas · 👤 De visitantes · 🚩 Reportadas · 🌱 De la siembra ·
  ⏳ Por aprobar** (este último trae su contador).
- **Buscador predictivo**: escribe un nombre de tienda, un apodo o una palabra de la opinión y **busca solo**
  a los 0,7 s (regla de oro n.º 2: nada de listas largas ni de apretar «Buscar»).
- **25 opiniones por página**, con paginación que **conserva el filtro y la búsqueda**.
- Cada tarjeta: **la tienda** (con **enlace a su ficha**), **quién la escribió**, **las estrellas**, la fecha,
  **de dónde salió** (🌱 «la escribimos nosotros» o 👤 «la dejó un visitante»), **su estado**
  (`✅ publicada` · `⏳ por aprobar` · `🙈 oculta`), **cuántos reportes tiene** y **el texto completo**.
- **Los tres botones de cada opinión** (con `confirm()`):
  - **✅ Aprobar / Aprobada** → la deja publicada en la ficha (y la vuelve a mostrar si estaba oculta).
  - **🗑️ Borrar** → se va de la ficha y **no se puede deshacer**.
  - **🙈 Ocultar** → deja de verse en la ficha **pero se conserva** en la base (por si se repiensa).
- La ficha pública muestra **solo las aprobadas** (`opiniones_sql_aprobadas()`); el `rating` de la tienda
  se recalcula al aprobar, ocultar o borrar.

### 4.2 🚩 LOS REPORTES (lo que hay que decidir de un toque)

- Cada reporte muestra **todo lo necesario para decidir sin abrir nada**: la tienda (con enlace a su
  ficha), el motivo, cuándo se reportó, **el texto exacto de la opinión** con su autor y sus estrellas,
  y lo que explicó quien reportó.
- **Los dos botones del jefe** (con `confirm()`):
  - **🗑️ Borrar la opinión** → se borra la opinión de la ficha y **se cierran TODOS sus reportes**
    (`opinion_borrada`). Es irreversible.
  - **🙈 Ignorar (dejarla)** → la opinión **se queda publicada** y el reporte pasa a `ignorado`.
- Abajo: el **historial** de lo ya decidido y la tarjeta **🌱 Sembrar opiniones** (con **⚙️ Preparar
  tablas** al lado).

### 4.3 🔔 EL AVISO AL TELEGRAM

Cuando alguien deja una opinión nueva, **llega un mensaje al Telegram del jefe** (aviso
**💬 Opinión nueva en una ficha**, encendido por defecto y apagable en **Súper Admin → 📱 Telegram**), y
cuando alguien toca 🚩 Reportar llega el aviso **🚩 Reportaron una opinión**. Comprobado en producción el
2026-09-15: el registro de avisos quedó en `opinion_nueva · enviado`. → **si el jefe no ve los avisos, el
interruptor está apagado en 📱 Telegram, no falta el código.**

⚠️ **Trampa encontrada (y arreglada) al construir la lista:** `opiniones_columnas()` guardaba las columnas
en un `static`, así que **después de un `ALTER` la misma petición seguía creyendo que la columna nueva no
existía** (y el filtro de aprobadas no se aplicaba hasta la recarga siguiente). Ahora la función acepta
`opiniones_columnas(true)` para **releer** y el instalador la llama así.

---

## 5) LA SIEMBRA: 3 OPINIONES CON CONTEXTO EN LAS ÚLTIMAS 100 TIENDAS

**La receta (repetible para las próximas tandas):**

1. **Cosechar** las tiendas y **lo que venden**:
   `python __op100_cosechar.py` → `__op100_tiendas.json` + `__op100_listado.txt`.
   Cómo saca «las últimas 100» **sin tocar la base**: el **sitemap** del sitio recorre las fichas con
   `ORDER BY id ASC`, así que **las últimas 100 entradas `/neg/<slug>` son exactamente las 100 tiendas
   creadas más recientemente**. De cada ficha se leen los productos por sus atributos
   `data-cz-prod` (`data-id` / `data-titulo`), que es la única parte del HTML que es **dato** y no
   presentación; el rubro y el distrito salen de `api/negocios_json.php`.
2. **Escribir la semilla** en `deploy/includes/opiniones_semilla.php`:
   `['slug' => '…', 'opiniones' => [ ['Nombre A.', 5, 'texto con contexto', días_atrás], … ]]`.
   - **3 por tienda**, **positivas** (5 casi siempre y algún 4, como en la vida real) y **con contexto**:
     hay que hablar de **los productos, servicios, medidas, precios y forma de atender de ESA tienda**.
   - El **4.º dato son los DÍAS ATRÁS** (número): el seeder calcula la fecha y le pone una hora de
     persona. Así el chat no aparece todo escrito el mismo día y **volver a sembrar no cambia fechas**.
   - Se buscan las tiendas **por `slug`**, nunca por id.
3. **Comprobar** antes de subir: `python __semilla_check.py` (tiene que decir *«FALTAN: (ninguno)»*,
   *«NO EXISTEN: (ninguno)»* y **3 opiniones por bloque**).
4. **Subir**: `python __subir_uno.py includes/opiniones_semilla.php`
   (y `includes/opiniones.php` si se tocó el motor).
5. **Sembrar**: `python __sonda_run.py __sonda_opiniones_seed.php op-seed-2026-9kQ7zz go`
   — o el botón **🌱 Sembrar ahora** del panel. **Es idempotente** (si el mismo texto ya está en esa
   tienda, no se repite), así que se puede tocar las veces que haga falta.
6. **Verificar**: la ficha de una tienda sembrada tiene que mostrar su pestaña **💬 Opiniones** con **3**
   opiniones, sus estrellas y sus botones 🚩.

**Lo sembrado el 2026-09-14 (primera tanda):**

| Dato | Valor |
|------|-------|
| Tiendas | **99** (de las 100 más recientes) |
| Opiniones escritas | **297** (3 por tienda) |
| Fechas | repartidas entre el 05/08/2026 y el 13/09/2026 |
| Autores | apodos de pila (Rosa C., Miguel Á., Katherine V.…) |
| Estrellas | 5 en su mayoría, con algunos 4 |
| `fuente` | `semilla` (auditable en el panel y por SQL) |
| Ratings | recalculados: las tiendas quedaron en **4.7** o **5.0** |

⚠️ **La tienda `administrador-de-sitio` NO lleva opiniones a propósito**: es el perfil del propio
administrador del directorio, no un negocio. Son **99 tiendas de 100**, y está anotado en el archivo.

### 5.1 REGISTRO DE LAS TANDAS (lo que ya está sembrado en producción)

| Tanda | Fecha | Tiendas | Opiniones | Herramientas (todas en `D:\RELAX`) |
|-------|-------|---------|-----------|-----------------------------------|
| 1 | 2026-09-14 (tarde) | 99 (las 100 últimas, menos `administrador-de-sitio`) | **297** | escritas a mano una por una |
| 2 | 2026-09-14 (noche) | 100 | **300** | `__op_tanda.py 2 100` → `__op_armar_encargos.py 2` → 4 subagentes → `__op_fusionar.py 2` |
| 3 | 2026-09-14 (noche) | 100 | **300** | igual con `3` |
| 4 | 2026-09-14 (noche) | 100 | **300** | igual con `4` |
| 5 | 2026-09-14 (noche) | 100 | **300** | igual con `5` |
| 6 | 2026-09-14 (noche) | 100 | **300** | igual con `6` |
| 7 | 2026-09-14 (noche) | 100 | **300** | igual con `7` |
| 8 | 2026-09-14 (noche) | 100 | **300** | igual con `8` |
| 9 | 2026-09-15 (madrugada) | **105** | **315** | las tiendas NUEVAS sin opinión (creadas desde el 01/09): `__sonda_op_nuevas.php` → `__op_desde_sonda.py` |
| **TOTAL** | | **904 tiendas** | **2 712 opiniones** | `fuente = semilla` (el sitio quedó con **5 273** opiniones contando las históricas) |

> ⏱️ Las 8 primeras tandas se hicieron la misma noche (de 20:10 a 20:51): **2 100 opiniones en 41 minutos**,
> con los 4 subagentes en paralelo por tanda. El cuello de botella es la cosecha (100 fichas por HTTP, ~2 min),
> no la redacción (300 opiniones en ~3 min).
>
> 🔎 **La tanda 9 (2026-09-15) fue distinta y es la receta para «las tiendas nuevas»:** el jefe pidió
> *«revisa todas las tiendas nuevas que se han creado que todavía no tienen opiniones»*. Se hace con la
> **sonda de censo** `__sonda_op_nuevas.php` (devuelve las tiendas activas **sin ninguna opinión**, con sus
> productos y el **desglose por día de creación**) y `__op_desde_sonda.py <json_sonda> <tanda> <desde_fecha>`,
> que convierte ese censo en una tanda normal (JSON + 4 encargos equilibrados). Después, lo de siempre:
> 4 subagentes → `__op_fusionar.py <tanda>` → subir → sembrar.

> ✍️ **REESCRIBIR LAS OPINIONES DE UNA TIENDA YA SEMBRADA (2026-09-17, ficha 1731).** Cuando el jefe manda
> **una** tienda para mejorarla (*«revisa y mejora su contenido y sus productos»*), no se toca la semilla de
> `includes/opiniones_semilla.php` ni se corre el seeder: se manda el payload al **escritor genérico**
> (`__sonda_tw.php` + `__tw_run.py`, receta completa en **`GUIA_PLANTILLAS_FICHA_Y_COLORES.md` §6.6**) con
> **`reemplazar_opiniones: true`** —eso borra **solo** las `fuente='semilla'` de esa tienda— y las 3 nuevas.
> Dos cosas que se aprendieron ahí: cada opinión puede llevar **`fecha`** propia (soporte añadido ese día a
> `__sonda_tw.php`; sin ella se usa «ahora» y las tres quedan con la misma marca de tiempo) y **nunca** se usa
> `borrar_todas_opiniones` para esto, porque se llevaría también las de **visitantes reales**.
> Resultado: **105 tiendas** (todas las creadas desde el 01/09) y quedaron **349 sin opinión, todas del
> 31/08/2026** (las viejas, por si el jefe quiere seguir con ellas).

**La receta que se usó (y que hay que repetir para la siguiente tanda):**

1. **Cosechar la tanda** — `python __op_tanda.py <N> 100` (saca las 100 tiendas de esa ventana del
   sitemap, con sus **productos reales**; la 1 son las últimas 100, la 2 las 100 anteriores, etc.).
2. **Armar los 4 encargos** — `python __op_armar_encargos.py <N>` (25 tiendas por encargo, con las
   instrucciones completas de estilo dentro).
3. **Lanzar 4 subagentes EN PARALELO** (uno por encargo): cada uno lee su `__op_encargo<N>_<k>.txt` y
   escribe `__op_resp<N>_<k>.json` con **3 opiniones por tienda**. En paralelo tardan ~3 minutos los 300.
4. **Fusionar y validar** — `python __op_fusionar.py <N>`: comprueba slugs, 3 por tienda, autores y días
   distintos, 6-45 palabras, y **añade** las entradas al final de `includes/opiniones_semilla.php`
   (las anteriores no se tocan y un slug repetido se salta).
5. **Subir y sembrar** — `python __subir_uno.py includes/opiniones_semilla.php` y
   `python __sonda_run.py __sonda_opiniones_seed.php op-seed-2026-9kQ7zz go` (idempotente).

⚠️ **El estilo que se le pide a los agentes** (es lo que hace que parezcan reales, pedido textual del
jefe: *«la opinión es que parezcan reales, humanas»*): un detalle **concreto y verificable** de esa
tienda (el producto, la medida, el plato, el precio, la rapidez, la limpieza, la zona), **12-28
palabras**, español peruano hablado, **nada de frases de publicidad**, y las **3 de la misma tienda
hablando de cosas distintas**. Con eso salen cosas como *«El cambio de aceite lo hacen delante de uno y
te muestran la varilla antes de cerrar el tapón»* o *«La chicha morada la dan en jarra bien helada»*.

---

## 6) LO QUE SE PROBÓ (evidencia real del 2026-09-14)

| Prueba | Resultado |
|--------|-----------|
| El bloque se pinta en la ficha | `data-fop` presente, 2 pestañas, 2 paneles, «Ver más» y 3 opiniones con sus 3 botones 🚩 (`/neg/proyectarq`, `/neg/polleria-el-rustico`, `/neg/khalid-impresiones`, `/neg/chancho-frito`, `/neg/sra-doris-santa`) |
| Publicar una opinión por AJAX | `200 {"ok":true,…}` con `id` nuevo, autor, estrellas, color y fecha |
| Reportar esa opinión | `200 {"ok":true,"reporte_id":1}` |
| Reportar dos veces desde la misma IP | `422 «Ya reportaste esta opinión»` ✔ |
| La consulta del panel de pendientes | devuelve el reporte **con** la tienda, el slug, el motivo, el texto de la opinión, el autor, las estrellas, la IP y la fecha |
| La pantalla del panel se pinta | 4 816 bytes de HTML con el título, los dos botones, el historial y la tarjeta de siembra |
| Camino **🗑️ Borrar** | el reporte quedó `opinion_borrada` **y** la opinión dejó de existir (`0`) |
| Camino **🙈 Ignorar** | el reporte quedó `ignorado` **y** la opinión **siguió publicada** (`1`) |
| La siembra | **297 insertadas, 0 saltadas, 0 tiendas sin encontrar** |
| Estado final de la base | 2 858 opiniones en total · **297 `semilla`** · 99 tiendas con sus 3 opiniones |

---

## 7) TRAMPAS (lo que costó tiempo, para no repetirlo)

1. 🔴 **El FTP aterriza en una carpeta que NO es la web.** La primera subida «funcionó» (bytes y md5
   idénticos) y **la web no cambió**: la cuenta FTP deja al usuario en **`/public_html`**, que es una
   **copia vieja anidada dentro de la raíz viva**. La raíz viva es **`/`**. Detalle y cómo se detecta:
   **`GUIA_DESPLIEGUE_Y_ENTORNO.md` §11.1**. `__subir_uno.py` y `__sonda_run.py` ya lo controlan.
2. **Los slugs de la semilla hay que sacarlos de la cosecha, no inventarlos.** Los primeros 33 estaban
   escritos «a mano» (`carsa-motos-chimbote` en vez de `carsa-motos`) y **ninguna tienda se habría
   encontrado**. Por eso existe `__semilla_check.py`: **se corre siempre antes de subir**.
3. **Nunca editar estos archivos con PowerShell** (`Get-Content -Raw | Set-Content -Encoding UTF8`):
   se leen en la codificación ANSI de Windows y **el archivo queda con doble codificación** («opinión» →
   «opiniÃ³n») **y con BOM** — que en un PHP además rompería las cabeceras. Se editan **con las
   herramientas de archivo del agente**. (Pasó y se reparó invirtiendo cp1252 → UTF-8.)
4. **`includes/opiniones.php` NO debe pedirse por URL**: si algún día se mueve a un archivo público, hay
   que comprobar que responda **200** y mudar también el CSRF, el tope por IP y el aviso de Telegram.
5. **La tabla `directorio_opiniones` ya existía** con **2 561 opiniones** de los módulos viejos
   (`fuente` = `web` y `google`). El módulo **no las toca**: solo lee y añade. Por eso se usa `fuente`
   como marca y **no** se añade una columna nueva.
6. **Otra sesión puede estar trabajando en la misma carpeta** (pasó: apareció la sección `maestro` en
   `superadmin.php` a mitad de la tarea). Antes de subir, **`python __comparar_vivos.py`** confirma que
   el local y el hosting coinciden y que no se pisó nada.

---

## 8) PENDIENTES

- ⏳ **Los siguientes negocios** (orden del jefe: *«si es que sale todo perfecto ya luego pasamos a los
  siguientes»*). La receta del §5 ya está lista: cosechar → escribir la semilla → comprobar → subir →
  sembrar. La semilla nueva puede **añadirse al mismo archivo** (el seeder salta lo ya puesto) o ir
  por tandas.
- ⏳ **El jefe tiene que decidir el reporte de demostración** que quedó en el panel el 2026-09-14
  (sobre una opinión sembrada de PROYECTARQ): sirve para que vea la pantalla con datos reales.
- ⏳ **Borrar la copia vieja anidada `/public_html/`** del hosting (es visible en
  `dechimbote.com/public_html/…` y confunde a cualquier sesión): **esperando el OK del jefe**.
- ⏳ **Opiniones de las tiendas viejas**: hoy solo las 99 últimas tienen opiniones sembradas. Decidir si
  se sigue con las siguientes (el jefe lo pedirá).
- ⏳ **Paginar el chat** si una tienda junta muchas opiniones (hoy se pintan hasta **50**, las más
  nuevas primero).
- ⏳ **Aviso de Telegram con interruptor propio**: hoy el reporte de una opinión usa el interruptor
  **🚩 Reportes de contenido**. Si el jefe quiere separarlos, se añade su tipo al catálogo de
  `includes/avisos.php` **con su formato** en `avisos_formato()`.
