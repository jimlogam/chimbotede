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


# GUÍA — APP CAMINANTE: CAPTURA DE NEGOCIOS EN CAMPO · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** antes de tocar la app Caminante (`dechimbote.com/caminante/`), sus botones de acceso o el alta de tiendas desde el celular.
> **Archivos que toca:** `deploy/caminante/index.html` · `deploy/caminante/subir.php` · `deploy/caminante/sesion.php` · `deploy/includes/helpers.php` · `deploy/panel.php` · `deploy/includes/header.php` · `deploy/index.php` · `deploy/registrar_negocio.php`
> **Estado:** EN PRODUCCIÓN

> 🔓 **El hosting es de uso exclusivo de la IA (orden del jefe, 2026-09-12):** el jefe nunca sube, edita ni renombra nada a mano, así que **no se hace respaldo del archivo vivo** y **no se compara el local con el hosting** (ni md5, ni tamaños). Despliegue = `php -l` → subir solo lo modificado → verificar por HTTP. Las menciones a md5/respaldos que queden en este archivo son **históricas** y no son pasos del flujo.

> Regla de Oro de UX: ver REGLAS_DE_ORO_PROYECTO.md

## 0) LO ESENCIAL EN 30 SEGUNDOS

- **Caminante es la forma más efectiva de agregar tiendas**: captura en la calle con el celular —fotos con la cámara, GPS, nota por voz y productos— en pasos guiados de una sola página. Enlace vivo: **https://dechimbote.com/caminante/**
- 🆕 **OCUPA TODO EL ANCHO, como una página más del sitio (2026-09-10, §14).** Mide el **100 % de la franja del sitio (1200 px → 1168 px útiles)** y en pantallas de +1000 px reparte los pasos en **2 columnas** (en celular sigue siendo 1). Antes medía **520 px fijos** y flotaba centrada, con ~340 px vacíos a cada lado.
- **Al guardar crea la tienda REAL en la base de datos.** (Antes solo subía archivos sueltos a `caminante/<carpeta>/`, sin BD.)
- **Logueado** → la tienda queda a su nombre (`dueno_id`). **Sin sesión** → queda `pendiente` sin dueño; al entrar, se la reclama sola (§8).
- 🆕 **Orden de captura actual (2026-09-10):** **1** nombre y distrito → **2** rubro → **3** la cara (fachada/logo) → **4** el local y su entorno → **5** ubicación y nota → **6** **lo que vendes (productos), al final y opcional**. El efecto IKEA se mantiene (§7).
- **El rubro es un buscador predictivo de verdad**: nombre + **824 palabras clave** + **27 subcategorías** (§5). **El distrito son chips** con los distritos visibles del sitio (hoy **4**). Nunca listas largas de opciones.

## 1) QUÉ CREA EN LA BASE DE DATOS AL GUARDAR

| Tabla | Qué se guarda |
|---|---|
| `directorio_negocios` | La tienda, con estado `pendiente` (la revisa el admin en `superadmin.php`) |
| `directorio_fotos` | Portada, logo y galería capturadas |
| `directorio_servicios` | Los productos capturados (título y precio) |
| `directorio_producto_fotos` | Las fotos de cada producto |

Además, las fotos se quedan en su carpeta `caminante/<carpeta>/` del hosting: sirve de respaldo físico y para inspección manual, y la BD apunta ahí.

> 🖼️ **Las fotos se suben OPTIMIZADAS (2026-09-10).** Antes entraban crudas y sin validar: la
> peor medida fue **4,59 MB** en una sola foto. Hoy: (1) el navegador las comprime al
> elegirlas (`assets/js/imagen_optimizar.js`, WebP máx 1600 px), (2) `caminante/subir.php`
> valida que sean imágenes reales y las vuelve a optimizar, y (3) el servidor crea las
> versiones de **300 px y 800 px** (`foto-300.webp`, `foto-800.webp`) al lado de cada foto.
> Los archivos ahora son **`.webp`** (no `.jpg`) y **solo los completos** se registran en
> `directorio_fotos` (las versiones son la misma foto y no se cuentan aparte).
> Detalle completo: **`GUIA_IMAGENES_Y_OPTIMIZACION.md`**.

**Regla del jefe:** todos pueden agregar tiendas, logueados o no; el logueado recibe la suya.

## 2) DÓNDE ESTÁN LOS BOTONES DE ACCESO A CAMINANTE

1. **Menú superior** de todo el sitio → botón "📸 Agregar tienda" (`includes/header.php`).
2. **Portada (hero)** → botón "📸 Agregar tienda" (`index.php`).
3. **Página "Registrar mi negocio"** (`registrar_negocio.php`) → tarjeta "¿Vas a capturar en la calle? Abrir Caminante".

Los tres abren `https://dechimbote.com/caminante/`.

## 3) SESIÓN: QUÉ VE EL USUARIO AL ABRIR LA APP

Al abrir, Caminante consulta `caminante/sesion.php` (misma cookie de sesión del sitio).

> 🆕 **LAS PÍLDORAS DE SESIÓN YA NO EXISTEN (2026-09-10, tercera tanda).** El jefe las quitó de la
> cabecera: *"¿por qué un botón que dice sin sesión iniciada?, ¿eso qué significa?, ¿para qué sirve?"*
> y *"cuenta Jimmy Logam tampoco debe ir, no me aporta nada"*. Antes la app mostraba ahí
> "✅ Cuenta: <nombre>", "🔓 Sin sesión" o "⚠ Sin conexión". Qué significaban (queda como referencia,
> porque el comportamiento NO cambió, solo dejó de mostrarse):

| Lo que decía la píldora | Qué significa de verdad |
|---|---|
| ✅ Cuenta: <nombre> | Logueado → la ficha queda **a tu nombre** desde el primer segundo (`dueno_id`) |
| 🔓 Sin sesión | Sin cuenta → la ficha nace **pendiente y sin dueño**; al entrar (mismo navegador) **se asigna sola** (§8) |
| ⚠ Sin conexión | Sin red → **no se crea la tienda**: solo se guardan las fotos |

- **Lo único que se conserva de aquello** es el aviso de **sin conexión**, ahora en una **barra**
  (`#barraConexion`) que **aparece solo cuando pasa de verdad**, no encendida siempre.
- En el paso 5 sigue habiendo una línea discreta (`#cuentaBar`) que, al guardar, dice si la ficha
  quedará a tu nombre o pendiente (es la única información de sesión que el jefe sí quiere).

`sesion.php` devuelve JSON con la sesión, el token CSRF, las **categorías con sus palabras clave**
(`claves`), las **subcategorías**, los distritos y las fichas por asignar (`asignadas`).

## 4) ORDEN DE PASOS (ACTUALIZADO 2026-09-10, CUARTA TANDA: PANTALLA LIMPIA)

> Historia corta de esta lista: **segunda tanda** → se borró el paso "1 · Empezar" (no servía) y en su
> lugar quedó una lista de fotos. **Tercera tanda** → la ubicación subió al paso 1 y el distrito pasó a
> calcularse solo. **Cuarta tanda (§17)** → el jefe revisó la pantalla y quitó lo que sobraba: la franja
> amarilla, los botones de distrito, el paso de "fachada y logo" y los textos explicativos; el paso de
> fotos quedó uno solo y **el botón de guardar se movió dentro de la página**.
>
> **Quedan 5 pasos (numerados 1 a 5):**

Orden real en `caminante/index.php`:

1. **📋 Instrucciones** (`section.guia`, tarjeta blanca) — una sola frase, **texto dictado por el jefe**:
   *"Toma **10 fotos de tu negocio**: desde la parte de afuera y también la parte de adentro. **No tomes
   fotos de tus productos**: toma fotos de tu negocio. Tus productos los listaremos después."*
   (Antes esto era una lista de 5 puntos con casillas ☐ y fondo crema: se quitó.)
2. **1 · Título y ubicación** (`#secNombre`) — el campo del **nombre** (`#inpNombreTienda`, el cursor
   arranca aquí) + **📍 ubicación** (`#btnUbicacion`: "Usar mi ubicación actual"). De las coordenadas sale
   el **distrito**, que se muestra en verde: "✔ Distrito: Santa (según tu ubicación)" (`#distDetectado`).
3. **2 · ¿A qué rubro pertenece?** (`#secRubro`) — buscador predictivo (§5). Sin textos: solo el título,
   el campo y el botón ▾. 🆕 **Al tocar el campo, la página sube sola** y deja el paso 2 pegado debajo de
   la cabecera (como un ancla), para que la lista de rubros se vea entera por encima del teclado (§18).
4. **3 · Cargar fotos** (`#secEntorno`) — **dos botones nada más**: `📷 Abrir cámara` (una foto por vez:
   abre, tomas, cierra, y se repite) y `🖼️ Abrir galería` (elige hasta **8** de golpe). Máximo **10 fotos**
   (`MAX_FOTOS_TIENDA = 10`). **La primera foto es la portada de la ficha** (ver §17).
   ⚠️ **Cada foto es solo la miniatura con su ✕**: debajo de cada foto **NO** van botones (§18).
   ⚠️ Los viejos pasos "3 · La cara de tu negocio" (fachada/logo) y "4 · Tu local y tu entorno" **ya no
   existen**: eran dos sitios pidiendo las mismas fotos.
5. **4 · Cuéntale a la gente** (`#secFinal`) — **botón de hablar estilo WhatsApp**: "🎙️ Mantén pulsado
   para hablar" (`#btnHablar`); mientras aprietas escribe lo que dices; al soltar, termina. Debajo, el
   campo `#nota` con el texto (se puede corregir a mano) + recordatorio del rubro (`#rubroFinalOk`) +
   la línea de cuenta (`#cuentaBar`).
6. **5 · Lo que vendes (opcional)** (`#secProductos`) — hasta **6 productos** (`MAX_PRODUCTOS`), 3 fotos
   cada uno (la primera es la portada). Botones dinámicos "＋ Agregar mi primer producto" / "＋ Agregar
   otro producto (n/6)" / "Ya está 👉".
7. **💾 Guardar tienda** (`.acciones-finales`, **dentro** de la página, justo debajo del paso 5) —
   antes era una barra fija pegada abajo; el jefe pidió que no se esconda nada. Al lado aparece
   "＋ Registrar otro" cuando ya guardaste.

**Validación al pulsar 💾 Guardar tienda** (en este orden, y lleva al paso que falta):

1. Sin **rubro** → aviso y salta al paso 2.
2. Sin **ubicación** (y por tanto sin distrito) → aviso ("Falta darnos tu ubicación (paso 1)…") y salta al paso 1.
3. Sin **ninguna foto** → aviso y salta al paso 3.

Detalles internos:

- `actualizarContexto()` refresca el recordatorio del rubro y los consejos del paso de productos.
- `limpiarCaptura(true)` deja todo como al principio: distrito y ubicación en blanco, `#ubiInfo`
  escondido, el botón otra vez en "📍 Usar mi ubicación actual" y el nombre vacío.

## 5) RUBRO PREDICTIVO (DE VERDAD) Y DISTRITO AUTOMÁTICO

- Un solo campo de texto (`#inpRubro`) en vez de decenas de chips de categorías.
- Al **tocar** el campo (o el botón ▾) se despliega la lista **completa**: primero **Rubros (103)** y luego
  **"Con más detalle" (27 subcategorías)**, en orden alfabético y sin tildes al comparar.
- Al **escribir** se filtra al instante. **Busca en tres sitios**: el nombre del rubro, sus **palabras
  clave** (tabla `directorio_categoria_claves`, **824 palabras**) y las **subcategorías**.
  Comprobado en vivo: **"pollo" → 🍗Pollerías · 🍽️Restaurantes · 🍽️Chifas · Comida criolla · Comida de
  noche** (10 resultados); "zapatilla" → Calzado; "ceviche" → Cevicherías; "playa" → Playas;
  "hidrandina" → Puntos de Pago Hidrandina.
- **Enter** elige la primera coincidencia; el **clic** marca la opción con una **píldora verde "✔ <rubro> ✕"**; la ✕ la quita.
- Al elegir una **subcategoría** se guarda su **rubro padre** en `categoria_id` y la subcategoría en
  `subcategoria_id` (columna que existe en `directorio_negocios` desde siempre: `int(10) unsigned`, nullable).
- 🆕 **El DISTRITO ya no se marca: se calcula con la ubicación** (tercera tanda §15; **cuarta tanda §17**:
  sin botones). Detalle:
  - Al capturar el GPS, la app llama a **`caminante/distrito.php?lat=..&lng=..`** y muestra
    "✔ Distrito: Nuevo Chimbote (según tu ubicación)".
  - **Ya NO hay chips de distrito ni botón "cambiar"** (el jefe los quitó: *"cuando comparto mi
    ubicación el mensaje verde dice 'Distrito Santa', entonces está bien… casi nunca falla el Android"*).
    `sesion.php` **sigue enviando** la lista de distritos porque el plan B la usa para reconocer el
    nombre del distrito dentro de la dirección de OpenStreetMap.
  - **Si no se puede calcular** (GPS apagado, permiso denegado, sin red): se muestra un aviso en el
    mismo hueco (`#distDetectado` con `.dist-detectado--aviso`) y **no deja guardar** hasta que haya
    ubicación. ⚠️ Sin botones no hay forma de elegir el distrito a mano: es una decisión del jefe.
  - Es **obligatorio** para guardar: sin ubicación no hay distrito y no se puede guardar.
- **Sin conexión** el campo de rubro se deshabilita y la captura se guarda solo como fotos (sin tienda en BD).

## 6) REGLA DE ORO DE UX PREDICTIVA — SU VERSIÓN IMPLEMENTADA (SOLO AQUÍ)

Este es el detalle técnico de la regla, tal como quedó implementado en Caminante:

1. **Campo de texto predictivo**, nunca una lista larga de opciones: el usuario escribe y las coincidencias aparecen solas.
2. **Todas las opciones visibles al tocar** el campo, **en orden alfabético** (A–Z, comparando sin tildes), sin perder lo escrito.
3. **Enter selecciona** la primera coincidencia.
4. **Píldora verde** "✔ <opción> ✕" para mostrar lo elegido y permitir quitarlo.
5. **Móvil primero**: una mano, espacio mínimo, pocos toques y **fuentes ≥16 px** (evita el zoom automático al enfocar un campo).

Aplica a Caminante, al alta de negocios y a cualquier formulario, filtro o `<select>` futuro del sitio. En el resto de las guías solo se referencia: `REGLAS_DE_ORO_PROYECTO.md`.

## 7) EFECTO IKEA EN EL ALTA Y PANTALLA DE ÉXITO

> Principio del jefe: **lo que cuesta (hacerlo tú mismo) lo valoras más.** En IKEA, armar el mueble hace que lo valores aunque sea de peor calidad que uno ya armado. Aplicado a crear una tienda: primero se "arma" con las manos (fotos → productos) y los datos se piden al final, cuando ya quiere terminarla; al terminar se le muestra el logro y que **podrá editarla él mismo después**.

En `caminante/index.php`:

- **Orden emocional:** las fotos se piden antes que los productos y el cierre (nombre/distrito subieron al
  principio porque sin ellos la ficha no existe).
- ⚠️ **La franja "obra en progreso" (`#estadoObra`) YA NO EXISTE** (cuarta tanda §17: el jefe la mandó
  borrar, *"está por las puras"*). `actualizarObra()` sigue definida pero **vacía**, porque se llamaba
  desde 7 sitios del código. Si algún día se quiere reponer el contador, hay que devolver el `<div>` y
  el cuerpo de la función.
- **Botón principal:** "💾 Guardar tienda", **dentro de la página**, al final del paso 5 (ya no hay barra
  fija abajo).
- **Pantalla de éxito** (`#cajaExito`): 🎉 "¡Tu tienda quedó creada!" y, según el caso:
  - dueño logueado → "👁 Ver mi tienda" + "✏️ Editar fotos y productos" (usa el `negocio_id` que devuelve `subir.php`);
  - sin sesión → botón "🔑 Entrar y llevarme mi tienda" (§8);
  - siempre → "📸 Crear otra tienda" + tip "💡 Tu tienda se queda contigo… podrás editarla tú mismo (fotos, precios, productos) con un botón ✏️ en tu panel".
- Tras guardar se ocultan los pasos de captura (se muestra solo el logro); "Crear otra tienda" reinicia el flujo.

## 8) RECLAMO AUTOMÁTICO: TIENDA CREADA SIN SESIÓN → SE ASIGNA AL ENTRAR

> Pedido del jefe: si alguien crea la tienda sin estar logueado y pulsa "Iniciar sesión / crear cuenta", esa tienda debe quedarle **asignada** y en su panel aparecer como "suya" ("tienes una tienda creada").

1. Al crear la tienda **sin sesión**, `caminante/subir.php` guarda su `negocio_id` en `$_SESSION['cam_claim']` (la sesión del navegador donde se creó).
2. Al **iniciar sesión o crear cuenta** (mismo navegador) y volver a Caminante, `caminante/sesion.php` llama a `caminante_autoreclamar()` —función en `includes/helpers.php`—: asigna `dueno_id` a las tiendas reclamables que sigan sin dueño y responde `asignadas`; la app muestra el aviso "✅ ¡Tu tienda quedó asignada a tu cuenta!" con enlaces (Ver tienda / Editar / Mi panel).
3. `panel.php` también llama a `caminante_autoreclamar()` al abrir el panel: aunque el usuario no vuelva a Caminante, la tienda ya aparece en "Mis negocios".
4. La pantalla de éxito de una creación sin sesión avisa de que al entrar la tienda se asignará sola.

**Seguridad:** la reclamación queda atada a la sesión del navegador donde se creó la tienda (lo razonable: solo esa persona pudo crearla desde ahí). Si el usuario pierde las cookies, el admin puede asignarla manualmente.

## 8.1) LA EXPERIENCIA DE CAPTURA, AHORA TAMBIÉN EN EL PANEL DEL DUEÑO (2026-09-10)

> Decisión del jefe: la captura rápida de Caminante (fotos con la cámara + nota por voz) debía llegar al
> **panel de todos los dueños**. Se implementó **sin tocar Caminante**: los mismos patrones, en
> `productos.php` y `panel.php`. Detalle completo: **`GUIA_PANEL_DUENO_Y_CAPTURA_RAPIDA.md`**.

| De Caminante se reutilizó | Dónde vive ahora |
|---|---|
| El input de cámara creado por JS (`accept=image/*` + `capture=environment`, y sin `capture` para elegir varias de la galería) | `assets/js/captura_rapida.js` → `abrirSelector()` |
| La rejilla de casillas cuadradas con la foto encima y ✕ para quitarla (`crearCeldaFoto()`) | `assets/js/captura_rapida.js` → casillas `.cz-cap__celda` |
| Optimizar **antes** de aceptar la foto (`CZImg.optimizarLista()`) | igual, con el mismo motor `assets/js/imagen_optimizar.js` |
| El contador de "obra en progreso" (efecto IKEA) | el contador `.cz-cap__contador` ("🏗️ Tu producto ya tiene N de 6 fotos") |

**Lo que sí cambió respecto a Caminante:**

1. **La nota de voz de Caminante usa el micrófono del teclado** (su `#nota` lo dice en el placeholder).
   En el panel, en cambio, el jefe pidió **reutilizar la Web Speech API**: se estrena
   `assets/js/dictado_voz.js` (píldora 🎙️ en cualquier campo con `data-dictado`), con la limpieza del
   dictado **invertida** respecto al buscador: **conserva los conectores**, porque una descripción es una
   frase. **Caminante sigue igual**: si algún día se le lleva el 🎙️, es añadir `data-dictado` al campo.
2. **En el panel no hay endpoint nuevo**: las fotos entran en el `input name="fotos[]"` de siempre
   rellenado por `DataTransfer` (Caminante sí postea por `fetch` a `caminante/subir.php`).
3. **El orden es otro**: en el panel el inventario son **productos de una tienda que ya existe**
   (`directorio_servicios`), no la creación de la tienda completa.

---

## 9) GUARDADO: ENDPOINTS, PARÁMETROS Y RESPUESTAS

- Al pulsar **Guardar**, la app envía todo + `crear=1` + `categoria_id` + `subcategoria_id` (opcional) + `distrito_id` + CSRF a `caminante/subir.php`.
- `subir.php` guarda SIEMPRE la carpeta de fotos. Solo si llegan `crear=1` + CSRF válido + rubro + distrito crea la tienda y los productos en la BD.
- Respuestas: JSON `{ok:true,…}` con `negocio_id` (tienda creada) o el clásico `OK guardado_en_caminante/<carpeta> fotos=N` (modo simple, sin red).
- Token inválido → `{"ok":false,"error":"token_invalido"}` y **no crea nada**.
- Rubro y distrito se validan contra la BD: `directorio_categorias.activo=1` y `directorio_distritos.visible=1`.
- 🆕 La **subcategoría** solo se guarda si pertenece al rubro elegido y está activa
  (`SELECT 1 FROM directorio_subcategorias WHERE id=? AND categoria_id=? AND activo=1`); si no, se guarda
  `NULL`. El rubro del negocio se sigue guardando **por categoría** en `categoria_id`.
- Un anónimo SÍ puede crear tienda: siempre queda `pendiente` y con `dueno_id = NULL` (la decide el admin).

## 10) IDENTIDAD VISUAL: CAMINANTE YA NO PARECE UNA APP APARTE

> Decisión del jefe: Caminante debe usar la identidad visual del sitio, no verse como una app aparte.

- Paleta del sitio en `:root`: granate vino `#6d071a` (primario, aplicado sobre la variable `--azul`), crema `#f7efe2` (fondo), naranja `#ea6a12` (CTA), marrón `#7a5230`, azul noche `#123c6b`, verde éxito `#16a34a`, texto `#171717`, borde `#e6dbc8` y sombras granate suaves.
- Fuente **Inter** (Google Fonts), la del sitio. Inputs en **16 px** para que el móvil no haga zoom.
- ⚠️ **La cabecera granate propia (`.cab`, "📍 DECHIMBOTE.COM / 📸 Caminante") YA NO EXISTE** (tercera
  tanda, §15): el jefe la quitó porque arriba ya está la cabecera del sitio. Tampoco hay fondo propio:
  usa el **fondo crema del sitio** y ahora **también el pie del sitio** (antes no lo cargaba).
- Las clases viejas (`.btn-azul`, …) se mantienen y apuntan a los colores del sitio: `.btn-azul` =
  granate (primario), `.btn-naranja` = CTA naranja, `.btn-verde` = éxito.

## 11) ARCHIVOS DEL MÓDULO Y SU RESPONSABILIDAD

| Archivo | Qué hace |
|---|---|
| `caminante/index.php` | La app completa (⚠️ ya **no** es `index.html`, ver §13): los **5 pasos** de §4 (sin píldoras de sesión desde la tercera tanda ni franja amarilla desde la cuarta), rubro predictivo, distrito automático, micrófono estilo WhatsApp y pantalla de éxito. Ocupa el ancho REAL del sitio (§14, §15 y §17) |
| `caminante/distrito.php` | 🆕 **A qué distrito pertenece una ubicación** (§15): mira los 30 negocios **activos** con coordenadas más cercanos y vota pesando por cercanía. Devuelve `{distrito_id, nombre, confianza, distancia_km, vecinos, dudoso}`. **Solo lectura** |
| `caminante/subir.php` | Guarda la carpeta de fotos SIEMPRE (validadas y optimizadas: WebP + versiones de 300/800 px, motor `includes/imagenes.php`); con `crear=1` + CSRF + rubro + distrito crea tienda y productos en la BD (acepta `subcategoria_id` opcional). Devuelve `negocio_id` y guarda `cam_claim` cuando no hay sesión. **Registra CADA subida** (§11.1) |
| `includes/caminante_registro.php` | **Registro (log) de cada subida** (§11.1): `caminante/_registro_subidas.log` (legible) + `.jsonl` (el mismo dato en JSON) + amplía el `datos.txt` de la carpeta. Avisa por Telegram si la subida llega incompleta |
| `caminante/.htaccess` | **Bloquea por web** `*.log`, `*.jsonl` y `*.txt` de Caminante (llevan IP, GPS y notas) — verificado: HTTP 403 |
| `caminante/sesion.php` | JSON con sesión, CSRF, categorías **con sus claves**, subcategorías y distritos. Llama a `caminante_autoreclamar()` |
| `includes/helpers.php` | `caminante_autoreclamar()`: asigna al usuario que entra las tiendas reclamables que siguen sin dueño. También `obtener_claves_categorias()` y `obtener_subcategorias()`, que alimentan el buscador de rubros |
| `panel.php` | Llama a `caminante_autoreclamar()` al abrir el panel |
| `includes/header.php`, `index.php`, `registrar_negocio.php` | Los 3 botones "📸 Agregar tienda" |

Aparte de esos archivos no se tocó nada del sitio: ni el chat, ni los banners.

- Los APK `app_acceso` y `app_agregartienda` abren `/caminante/index.html` (verificar).

## 11.1) 🆕 LAS FOTOS QUE SE PERDÍAN Y EL REGISTRO DE CADA SUBIDA (2026-09-10, TARDE)

> **Lo que preguntó el jefe:** *"cuando creé esa tienda tomé muchas fotos y las subí; dime si en el
> hosting están esas imágenes huérfanas o si simplemente no llegaron a subir… me imagino que cada
> subida del caminante debe dejar algún tipo de log, y si no lo deja, pues debe dejarlo"*.

### 15.1 El fallo: **cada captura guardaba UNA sola foto (la última)**

- **Qué se veía:** 7 carpetas en `caminante/` y **una sola foto en cada una**, con nombres
  `tienda_6.webp`, `tienda_5.webp`, `tienda_4.webp`… (el número es la POSICIÓN de la foto en la app:
  si el nombre es `tienda_6`, la app tenía 6 fotos del local y se guardó solo la 6.ª).
- **La causa (reproducida el 2026-09-10):** la app enviaba todas las fotos con el MISMO nombre de
  campo —`fd.append('foto', …)`— y **PHP, sin corchetes, se queda solo con la ÚLTIMA**: `$_FILES['foto']`
  llega con un único archivo. Probado con un POST de 3 fotos: el servidor guardó **1** (`fotos=1`).
  Por eso los `datos.txt` decían `total_fotos: 1` y `fotos_rechazadas: 0`: **no se rechazaba nada,
  simplemente nunca llegaba**.
- **El arreglo:** enviarlas como **`foto[]`** (`caminante/index.php`, 3 líneas). Probado después del
  arreglo: **4 enviadas → 4 guardadas** (y 3 → 3).
- ⚠️ **NO quitar los corchetes.** Si otra sesión vuelve a subir `caminante/index.php` sin `foto[]`,
  **vuelve el fallo** (y habría que reponer también el `seguir` que entrega listas y los `lista[0]`
  de portada/logo y productos, ver 15.3).
- **Lo que ya se perdió no se puede recuperar** (nunca llegó al servidor): hay que **volver a
  capturar** esas fichas. Las capturas hechas hasta el 2026-09-10 tienen **1 foto**:
  Essalud Coishco (992, 2 fotos), Anqari (1563), Restaurante el tamarindo (1566),
  Iglesia Monte Sinaí (1567), Los niños del futuro (1568) y Plaza de Armas de Santa (1569,
  que hoy tiene 6 porque se le unió la ficha duplicada).
- ⚠️ **Un celular con la página vieja en caché sigue perdiendo fotos**: la app es `caminante/index.php`
  (HTML+JS), así que **hay que recargar la página en el celular** para que use el código nuevo.
  Comprobado en la prueba: con el campo `foto` repetido (cliente viejo) **sigue llegando solo la última**.
- **No eran "huérfanas":** las 6 carpetas con foto están TODAS registradas en `directorio_fotos`
  (una fila por carpeta, la portada de su ficha). La única carpeta huérfana es
  `tienda_1788620386` (05/09/2026): **0 fotos**, `datos.txt` con todo vacío y **sin fila en la BD**
  (una subida que no llegó a mandar nada). No ocupa nada; se puede borrar por FTP cuando se quiera.

### 15.2 El registro (log) de cada subida — lo que pediste

**Archivo para leer:** `caminante/_registro_subidas.log` — **una línea por subida**, legible:

```text
2026-09-10 16:13:19 | carpeta=tienda_1789068212950 | CREAR-TIENDA | nombre="Plaza de Armas de Santa"
 | fotos: enviadas=6 recibidas=6 guardadas=6 rechazadas=0 | por-paso=portada:1 local:3 productos:2
 | 1.24 MB recibidos / 0.31 MB guardados | servidor=1.8s captura=95s | ubicacion=-8.9866595,-78.6133349 (±12m)
 | direccion="Jirón Amazonas, Santa…" | rubro=64 Plazas y Parques distrito=3 Santa | nota=154c productos=2
 | negocio=1569 slug=plaza-de-armas-de-santa | ip=190.108.93.134 | dispositivo="Android · Chrome" | resultado=ok
```

Qué queda registrado de **cada** intento (también cuando **falla**):

| Dato | Detalle |
|---|---|
| **Cuándo** | Fecha y hora **de Lima** (`date_default_timezone_set('America/Lima')` al inicio de `subir.php`) + `fecha_utc` en el JSON |
| **Dónde** | `lat`, `lng`, **precisión del GPS en metros** (`pos.coords.accuracy`, que la app ahora captura y muestra en pantalla) y la dirección (geocodificación inversa) |
| **Qué** | Nombre de la tienda, rubro y distrito (con su id), largo de la nota, cuántos productos |
| **Cuántas imágenes** | `fotos_cliente` (**las que dice el celular que manda**), `fotos_recibidas`, `fotos_ok`, `fotos_rechazadas` y el **detalle archivo por archivo** (peso, si se guardó, error, ruta final) |
| **Cómo** | Tamaño recibido/guardado, duración en el servidor, **segundos que duró la captura**, pantalla del móvil, IP, dispositivo y User-Agent |
| **Resultado** | `ok` · `solo-fotos` · `token_invalido` · `rubro_distrito_invalidos` · `no_se_pudo_crear`, con el id y el slug de la tienda creada |

- **Tres rastros, un solo motor** (`includes/caminante_registro.php`):
  1. `caminante/_registro_subidas.log` → legible, para leer a ojo.
  2. `caminante/_registro_subidas.jsonl` → el mismo dato en JSON, para medir/procesar.
  3. `caminante/<carpeta>/datos.txt` → se **amplía** con lo que realmente llegó (antes solo tenía
     `total_fotos`, que es lo que el servidor guardó, y por eso el fallo pasó desapercibido).
- Los dos archivos del registro **se rotan solos** al pasar de 3 MB (se guarda una copia `.1`).
- 🔒 **Privacidad:** `.log`, `.jsonl` y `.txt` de Caminante **no se sirven por web** (403), porque
  llevan IP, GPS y notas. Regla en `caminante/.htaccess` (verificado con `curl`: HTTP 403).
- 🔔 **Aviso nuevo de Telegram: `caminante_incompleto`** (encendido por defecto, se apaga en
  Súper Admin → 🔔 Avisos). Salta cuando el celular dice haber enviado N fotos y llegaron menos,
  cuando alguna fue rechazada o cuando no se guardó ninguna. Texto:
  `📵 CAMINANTE: SUBIDA INCOMPLETA` + carpeta, fotos enviadas/guardadas/rechazadas, ubicación y el motivo.

### 15.3 Segundo fallo arreglado (silencioso): cámara y galería en los pasos de una sola foto

`inputCapture()` entregaba **un archivo suelto** cuando el modo era `camara` y **una lista** cuando
era `galeria`. Los pasos de una sola foto (fachada, logo y fotos de producto) esperaban un archivo:

- **Cámara** → recibían un archivo pero el paso de las fotos del local hacía `files.slice(...)`
  → **error de JavaScript y la foto NO entraba** (nunca avisaba).
- **Galería** (en fachada/logo/productos) → recibían una lista y se guardaba **la lista entera** en
  lugar de la foto.

Ahora `inputCapture()` **siempre entrega una lista** y cada paso toma lo que necesita
(`const f = lista[0]`). Verificado con `node --check` sobre el JavaScript y en la app servida.

## 12) REVERTIR CAMINANTE AL MODO "SOLO FOTOS"

Para volver al comportamiento anterior (sin crear tienda en la BD) hay que reponer `caminante/subir.php` y `caminante/index.html` con las versiones previas al cambio de integración. 🔓 **No se respalda el archivo vivo antes de subir** (regla del 2026-09-12: el hosting es de uso exclusivo de la IA): el despliegue son **3 pasos** — **`php -l`** → subir **solo lo modificado** (`python __subir_uno.py <ruta relativa>`) → **verificar por HTTP**.

---

## 13) 🆕 CAMINANTE AHORA ES `index.php` Y USA LA CABECERA DEL SITIO (2026-09-10)

**Qué pidió el jefe:** que la cabecera nueva del sitio (marca `DeChimbote.com` + menú hamburguesa +
buscador + marquesina de rubros) esté en **TODAS** las páginas visibles, "en todas, todas, todas".

**El problema:** `caminante/index.html` era una **página suelta** (HTML puro, 47 KB) con su **propia**
cabecera (`.cab`), así que los cambios de `includes/header.php` **nunca le llegaban**.

**La solución:** se convirtió a **`caminante/index.php`**, que ahora hace:

```php
$titulo_pagina = 'Caminante · Captura de negocios';
$descripcion_pagina = '...';
require_once __DIR__ . '/../includes/header.php';
?>
<style> …su CSS propio, tal cual… </style>
…su contenido y su JavaScript, tal cual…
<?php require_once __DIR__ . '/../includes/footer.php'; // ← añadido en la tercera tanda (§15) ?>
```

- **Su diseño y su JavaScript NO se tocaron** (al convertir): se sacaron del HTML original y se pusieron tal cual.
- Su `<style>` va **después** del CSS del sitio, así que sus reglas siguen mandando donde coincidan.
- ⚠️ **OJO, DATO VIEJO CORREGIDO:** aquí decía que *"no se carga el pie del sitio ni sus scripts (chat,
  carrito, banners, zoom…) porque Caminante debe seguir ligera"*. **El jefe decidió lo contrario el
  2026-09-10 por la tarde**: quiere que Caminante sea **una página más**, así que **sí se carga el pie
  completo** (`includes/footer.php`), con sus enlaces, su chat flotante y sus scripts (§15).
  - `footer.php` **ya trae `</main>` y `</body></html>`**: en `index.php` **no** se cierran (si se
    cerraran otra vez, el HTML queda roto).
  - **No se duplica `buscador_voz.js`**: lo carga el pie. Antes se añadía a mano (línea que ya no existe).
- El servidor sirve `/caminante/` con **este** `index.php` (comprobado por HTTP: DirectoryIndex prefiere
  `index.php`).
- El `index.html` viejo se sustituyó por una **redirección de seguridad** (1250 bytes, `<meta refresh>`)
  para que, si algún día el servidor sirviera `index.html`, el visitante acabe igual en la página con
  cabecera y no en una copia vieja. El HTML original quedó respaldado en
  `_vivos_cabecera\caminante_index.html.<sello>` (carpeta **histórica**: hoy no se respalda el archivo
  vivo — regla del 2026-09-12, el hosting es de uso exclusivo de la IA).
- **Rehacer la conversión** (si algún día cambia el HTML original): `python __caminante_1_convertir.py`
  (lee el HTML, saca su `<style>` y su `<body>` y escribe el `index.php`, guardando respaldo).
- ⚠️ **Ojo con los APK** (`app_acceso`, `app_agregartienda`) que abren `/caminante/index.html` (nota de
  la §11): esa URL ahora es una redirección, así que siguen funcionando, pero conviene apuntarlos a
  `/caminante/`.
- Nota: la app **enfoca sola** el campo del rubro al cargar (`inpRubro`), así que en pantalla de celular
  la página baja un poco sola. Es comportamiento suyo de antes, no de la cabecera.

---

## 14) 🆕 ANCHO COMPLETO COMO UNA PÁGINA MÁS DEL SITIO (2026-09-10, TARDE)

**Qué dijo el jefe:** *"El caminante tiene algo que corregir: no ocupa todo el ancho de la pantalla…
necesito que ocupe todo el ancho de la pantalla, que sea como una página más."* Y eligió entre las
opciones: **ancho = como una página más del sitio (1200 px)**, **no** de borde a borde en monitores
grandes.

**El diagnóstico (lo que confundía):** la versión que arreglaba el ancho **ya estaba escrita en local
desde las 15:29** (`deploy/caminante/index.php`) pero **nunca se había subido**: el servidor seguía
sirviendo la de las 14:58. Medido en vivo (1366 px de pantalla): `.app` = **520 px** centrada dentro de
`<main>` = **1200 px** → **~340 px vacíos a cada lado**, y la barra de guardar también de 520 px.

**La solución (en producción y verificada):**

- `.app{width:100%;max-width:none;margin:0 auto;padding:14px}` → **1168 px** medidos en vivo.
- `@media (min-width:1000px)`: `.contenido` pasa a **2 columnas** (`1fr 1fr`, `gap:16px`); en celular
  **sigue a 1 columna, igual que antes**.
- La barra de guardar `.fijo` es **de borde a borde** (1351 px medidos) y su contenido se alinea a la
  franja del sitio con `.fijo__inner{max-width:var(--max-ancho);margin:0 auto}`.
- Se ajusta al `--max-ancho: 1200px` del sitio (`assets/css/base.css`): **la app nunca es más ancha que
  la portada** (antes se decía «la portada o el tablón»; 🗑️ el tablón comunitario se retiró el 2026-09-13),
  que es lo que pidió el jefe.

**⚠️ Lo que NO debe repetirse (la trampa que costó esta sesión):**

1. **Una versión nueva en `deploy/` NO es una versión viva.** Aquel día se comprobaba con un
   **md5 local ↔ hosting** (`python __reco_md5.py caminante/index.php caminante/sesion.php
   caminante/subir.php`, **~2 s**) y la solución llevaba horas escrita y sin subir.
   *(hoy ya no aplica: regla del 2026-09-12, el hosting es de uso exclusivo de la IA — no se compara el
   local con el hosting; lo que se hace es subir lo modificado y verificar por HTTP).*
2. **`php -l` se puede hacer en local**: `C:\xampp\php\php.exe -l deploy\caminante\index.php`
   (XAMPP 8.2.12). No hace falta ningún validador subido al hosting.
3. **Los tres archivos van juntos**: `index.php` necesita el `sesion.php` nuevo (claves + subcategorías)
   **y** el `subir.php` nuevo (`subcategoria_id`). Subir solo el `index.php` deja el buscador de rubros
   vacío ("pollo" no encontraría nada).

**Datos de producción comprobados con una sonda temporal** (patrón §3.1 de
`GUIA_DESPLIEGUE_Y_ENTORNO.md`, ya borrada: da **404**): `directorio_negocios.subcategoria_id` existe
(`int(10) unsigned`, nullable, indexada) · `directorio_subcategorias` = **27 activas** ·
`directorio_categoria_claves` = **824 palabras** · `obtener_claves_categorias()`,
`obtener_subcategorias()` y `caminante_autoreclamar()` **existen** en el `includes/helpers.php` vivo ·
1536 negocios (**1516 sin subcategoría**).

**Herramientas de esta tanda (en `D:\RELAX`):**

| Script | Qué hace |
|---|---|
| `__cam_4_desplegar.py` | **HISTÓRICO (no se usa).** Bajaba los vivos a `_vivos_caminante_<sello>\`, comparaba md5 y (con `--subir`) subía solo lo cambiado y **volvía a verificar el md5 del servidor**. Hoy el despliegue son 3 pasos: `php -l` → subir solo lo modificado → verificar por HTTP |
| `__cam_sonda.php` + `__cam_sonda_run.py` | **HISTÓRICO (no se usa)** en su parte de md5 de los vivos: era una sonda de solo lectura (columnas, tablas, funciones y md5 de los vivos) que subía, leía y **borraba** en la misma ejecución |
| `__cam_rev_subir.py` | Banco de pruebas: copia el trío a `/__cam_rev/` con `?api=__cam_rev` para probar **sin pisar el vivo** (⚠️ hay que entrar con `?api=__cam_rev` o la app llama a los endpoints vivos y miente) |

**Respaldo del vivo antes de subir (dato histórico):** `_vivos_caminante_20260910_160602\` (md5 vivos: `index.php`
`99a7d025…`, `sesion.php` `a9a42f03…`, `subir.php` `57f5a9d8…`). Quedó también
`_vivos_caminante_20260910_160556\` (la pasada de comprobación). Esas carpetas quedan **solo como archivo
histórico y nadie tiene que correrlas** *(hoy ya no aplica: regla del 2026-09-12, el hosting es de uso
exclusivo de la IA)*.

---

## 15) 🆕 SIN CABECERA PROPIA, ANCHO REAL, PIE DEL SITIO Y DISTRITO AUTOMÁTICO (2026-09-10, TARDE)

> **Estado: ✅ EN PRODUCCIÓN** (subido el 2026-09-10 a las 16:33, con autorización expresa del jefe:
> *"sube sube todo"*). Antes se había probado en un banco aparte (`/__cam_rev/`, ya borrado).
> **Verificado en vivo:** `/caminante/` responde **200**; en pantalla de 1366 px la app, la lista y el
> botón Guardar miden **1168 px @ x=92** (igual que la portada); sin cabecera propia ni píldoras; con el
> pie del sitio; y `distrito.php` acierta los **4 distritos** (Chimbote, Nuevo Chimbote, Santa y Coishco).
>
> **md5 subidos:** `caminante/index.php` = `17a206864cd0f817e05c7d197c03069b` (75533 B) ·
> **`caminante/distrito.php` = `dc4b0974eba936e3a8b0eef26e66c409` (5654 B, ARCHIVO NUEVO)**.
> Respaldo del vivo antes de subir (**dato histórico**: hoy ya no aplica, regla del 2026-09-12, el hosting
> es de uso exclusivo de la IA): `_vivos_caminante_20260910_163353\` (la comprobación previa quedó en
> `_vivos_caminante_20260910_163346\`).

### 15.1 Lo que pidió el jefe (sus palabras)

1. *"Me da la sensación de que el ancho es distinto, no me termina de convencer."*
2. *"¿Por qué Caminante tiene cabecera? No debe tener."*
3. *"Hay un botón que dice 'sin sesión iniciada', ¿qué significa eso?, ¿para qué sirve? Y cuenta
   Jimmy Logam tampoco debe ir, no me aporta nada. Borra todo ese bloque."*
4. *"Lo de las 5 fotos está muy bien, pero hazlo parecer una **lista de tareas pendientes**."*
5. *"Si ya estamos pidiendo la ubicación, ¿por qué tenemos que marcar el distrito? Se supone que eso lo
   podemos calcular automáticamente."* → Eligió: **automático + botón "cambiar"** por si el GPS falla.
6. **Pie del sitio: completo** ("como cualquier página"). Y **empezar ya en local, sin subir**.

### 15.2 Los cambios

| # | Cambio | Detalle |
|---|---|---|
| 1 | **Ancho REAL** | `.app{padding:0}`. Antes la app añadía **su propio relleno** dentro del que ya pone el sitio: sus tarjetas medían **1136 px** contra **1168 px** de cualquier otra página. Ahora `.app`, la lista, los pasos y el botón Guardar miden **1168 px y empiezan en x=92**, igual que la portada |
| 2 | **Fuera la cabecera propia** | Se borró el bloque granate `.cab` (168 px de alto) y sus reglas CSS. La marca ya la pone la cabecera del sitio |
| 3 | **Fuera las píldoras de sesión** | `#pillEstado` y `#pillCuenta` eliminadas. ⚠️ **Trampa evitada:** el código hacía `if(!pill || !bar) return;` en `cargarConfig()` → borrar la píldora sin tocar esa línea **dejaba la app sin rubros, sin subcategorías y sin distritos**. Esa línea ya no existe |
| 4 | **Sin fondo propio** | Se quitó el degradado del `body`: usa el crema del sitio |
| 5 | **Pie del sitio completo** | `require_once __DIR__ . '/../includes/footer.php'`. **No** se cierran `</main>`/`</body>` (los trae el pie) y **no** se duplica `buscador_voz.js` |
| 6 | **Lista de tareas pendientes** | Mismo texto de las 5 fotos, con **casillas vacías ☐** hechas en CSS (`.guia li::before`, cuadro blanco 20 px, radio 6). **Solo visual** (elección del jefe: no se marcan solas) |
| 7 | **Ubicación al paso 1** | El botón 📍 pasó de `#secFinal` a `#secNombre` ("Usar mi ubicación actual"); tras capturar cambia a "Volver a capturar" |
| 8 | **Distrito automático** | Nuevo `caminante/distrito.php` (§15.3). La app muestra "✔ Distrito: X (según tu ubicación) · cambiar"; los chips quedan **ocultos** detrás de "cambiar" |
| 9 | **Paso 5 renombrado** | "5 · Cuéntale a la gente" (solo la nota por voz); la ubicación ya no está ahí |
| 10 | **Barra de guardar alineada** | `.fijo{padding:12px 0}` + `.fijo__inner{padding:0 16px}`; `.fijo` con `z-index:1200` y el **chat del pie subido** (`.ch-fab{bottom:96px}`) para que no tape el botón Guardar — 🗑️ **hoy ese `.ch-fab` ya no existe** (el botón flotante «💬 Chat» se retiró el 2026-09-13); el único flotante es 🥷 El ninja |

### 15.3 `caminante/distrito.php` — cómo calcula el distrito

- Toma los **30 negocios ACTIVOS con coordenadas** más cercanos (haversine calculado por MySQL) y vota
  **pesando por cercanía**: `peso = 1/(distancia+0,05)²`.
- Devuelve `dudoso:true` si el negocio más cercano está a **más de 3 km** o si el voto está repartido
  (**confianza < 60 %**) → la app **propone** el distrito pero **abre los botones para confirmar**.
- **Medido el 2026-09-10** (banco de pruebas): Chimbote (Plaza, Mercado, 21 de Abril) → **Chimbote**;
  Nuevo Chimbote (Plaza, UNAS) → **Nuevo Chimbote**; Santa → **Santa**; Coishco → **Coishco**; todos con
  **≤ 0,2 km** al negocio más cercano y confianza **1,0**. Lima → **pide confirmar** (4,8 km).
- Datos reales de la ciudad que se usan: **1474 negocios activos con coordenadas** (Chimbote 752,
  Nuevo Chimbote 444, Santa 129, Coishco 107, y otros en distritos **no visibles**).

### 15.4 Errores y fricciones de esta tanda (y cómo se resolvieron)

| # | Error / fricción | Causa | Solución | Dónde quedó |
|---|---|---|---|---|
| 1 | **HTTP 500 en `distrito.php`**: `SQLSTATE[HY093]: Invalid parameter number` | El sitio usa PDO con **prepares nativos**: un parámetro con nombre **no se puede repetir** (`:la` dos veces) | Parámetros distintos: `:la1`, `:la2`, `:lo1` | comentado en `distrito.php` y en §15.3 |
| 2 | **Una captura en Lima devolvía "Nuevo Chimbote"** con confianza 1,0 (¡peligroso!) | Hay **3 negocios activos con coordenadas de Lima** registrados como Nuevo Chimbote (más 1 al norte como Santa): **datos sucios** | El detector usa **solo negocios ACTIVOS** y exige que el más cercano esté a **≤ 3 km**; si no, `dudoso` y confirmación humana | §15.3 · **pendiente:** corregir esos 4 negocios |
| 3 | **El botón 💬 del pie tapaba el botón Guardar** | El pie traía `.ch-fab` con `z-index:1000` y Caminante tiene su barra fija abajo | `.ch-fab{bottom:96px}` y `.fijo{z-index:1200}`. 🗑️ **Nota (2026-09-13):** el botón **💬 Chat** se retiró del sitio, así que **este choque ya no puede ocurrir** | §15.2 (punto 10) |
| 4 | **En celular el botón Guardar quedaba 14 px más adentro** que las tarjetas | Doble relleno: `.fijo` (14 px) + `.fijo__inner` (16 px) | `.fijo{padding:12px 0}` | §15.2 (punto 10) |
| 5 | **Editar el comentario PHP de cabecera rompió el archivo** (quedó texto suelto fuera del `/** … */`) | Edición por bloques: se reemplazó el encabezado y los puntos viejos quedaron colgando | `php -l` lo detectó al instante; se repuso la cabecera de la "segunda tanda" | este apartado |
| 6 | **Otra sesión trabajaba en el MISMO módulo** (`index.php`, `subir.php`, `.htaccess`, guía) | Dos sesiones en paralelo sobre `caminante/` | Se detectó porque **el md5 local no coincidía con el que yo mismo había subido**; se releyó su versión, se entendió su arreglo (fotos perdidas + registro) y **se construyó encima**, sin pisarlo | §16 |
| 7 | **Mi pestaña de pruebas se cerró sola** a mitad de la verificación | El navegador lo comparten las dos sesiones | Se volvió a abrir la pestaña (el banco ya estaba subido) | este apartado |

### 15.5 Pendientes de esta tanda

1. ✅ **HECHO: SUBIDO al hosting** (2026-09-10 16:33): `caminante/index.php` + **`caminante/distrito.php`**
   (archivo nuevo). Se comprobó entonces que el md5 del vivo siguiera siendo la base esperada (`713c5778…`)
   y **no** la había movido la otra sesión.
   ⚠️ **Aquello era de entonces:** ese día se comparaba el md5 antes de subir cualquier archivo de
   `caminante/` (`python __reco_md5.py caminante/index.php`) porque dos sesiones trabajaron el mismo
   módulo. **Hoy ya no aplica: regla del 2026-09-12, el hosting es de uso exclusivo de la IA** — no se
   compara el local con el hosting; el despliegue son 3 pasos: `php -l` → subir solo lo modificado
   (`python __subir_uno.py <ruta relativa>`) → verificar por HTTP.
2. **Probar el flujo completo en un celular real** (GPS + cámara + guardado), que es lo único que no se
   puede probar desde la computadora. **Ojo: probar el guardado crea una ficha de verdad en la base.**
3. **4 negocios con coordenadas equivocadas** (3 en Lima marcados como Nuevo Chimbote, 1 al norte como
   Santa): corregir sus coordenadas o su distrito desde el panel. Son los que obligaron a la regla de
   los 3 km (con esos datos, una captura en Lima "acertaba" Nuevo Chimbote con confianza 1,0).
4. **Distritos no visibles con negocios reales**: Samanco (21), Nepeña (12), Moro (6), Cáceres del Perú
   (2). Como los chips de Caminante (y `subir.php`) solo aceptan distritos `visible=1`, una captura en
   Samanco **no puede** guardarse en su distrito: el detector **pide confirmar** y queda en manos del
   usuario. Decidir con el jefe si se hacen visibles.
5. ✅ **HECHO: banco de pruebas `/__cam_rev/` BORRADO** del hosting (da **404**, comprobado). También se
   borró `caminante/__chk_cam.php`, un chequeo temporal del 2026-09-09 que otra sesión dejó olvidado y
   que leía negocios, fotos y dueños (su propio comentario decía *"se borra tras usarse"*).
   **Regla:** cualquier archivo temporal se borra al terminar y **se comprueba el 404**.

---

## 16) ⚠️ DOS SESIONES TRABAJANDO EN CAMINANTE A LA VEZ (2026-09-10)

El mismo 2026-09-10 por la tarde, **dos sesiones** tocaron este módulo sin saberlo:

| Sesión | Qué hizo | Cuándo |
|---|---|---|
| A | Ancho completo + guía puesta al día (§14) | 16:06 (desplegado) |
| B | **Fotos que se perdían** (`foto[]`) + registro de subidas + `.htaccess` (§11.1) | 16:11–16:13 (desplegado) |
| A | Sin cabecera, ancho real, pie del sitio, distrito automático (§15) | 16:25–16:30 (**sin desplegar**) |

**Cómo se detectó:** al ir a editar, el **md5 local no coincidía con el que la propia sesión A había
subido** minutos antes (`0DDA16DD…` → `713C5778…`).

**Regla que salió de esto (era la de entonces):**

1. **Antes de editar `caminante/`**, se comparaba el md5 y, si no coincidía con lo último que recordabas,
   se **releía el archivo** para saber qué
   cambió. *(hoy ya no aplica la parte del md5: regla del 2026-09-12, el hosting es de uso exclusivo de la
   IA — no se compara el local con el hosting; sigue valiendo **releer el archivo**).*
2. **No pisar el trabajo de la otra sesión**: se construye **encima** de su versión.
3. Lo bueno: su arreglo (fotos perdidas) **sigue intacto** en la versión nueva (se verificó por diff:
   los 44 cambios suyos están todos presentes).

---

## 17) 🆕 PANTALLA LIMPIA: LO QUE EL JEFE MANDÓ BORRAR (2026-09-10, CUARTA TANDA)

> **Estado: ✅ EN PRODUCCIÓN** (subido el 2026-09-10 a las **17:12**, con la orden del jefe
> *"haz todos esos cambios ahora mismo, de frente al hosting"*). **md5 subido:**
> `caminante/index.php` = `fda4cd07f15f12b5c46a0fd113ae76db` (**72677 B**).
> Respaldo del vivo antes de subir (**dato histórico**: hoy ya no aplica, regla del 2026-09-12, el hosting
> es de uso exclusivo de la IA): `_vivos_caminante_20260910_171159\`.

### 17.1 Lo que dijo el jefe (revisando la página, en orden)

| # | Sus palabras (resumidas) | Qué se hizo |
|---|---|---|
| 1 | *"Se va directo al punto 2, totalmente innecesario: debe cargar normal y quedarse en el punto 1"* | **Causa real:** al cargar, la app llamaba a `limpiarRubro()`, que **enfocaba el campo del rubro** → el navegador bajaba solo. Ahora `limpiarRubro(false)` y el cursor arranca en el **nombre** (`#inpNombreTienda`) |
| 2 | *"Una ventana amarilla media crema que no sé qué sirve… se debe borrar"* | Era la **franja de la obra** (`#estadoObra`, fondo naranja claro y borde de rayas). Borrada, junto con su CSS y el `MutationObserver` |
| 3 | *"Dice 'tus cinco fotos pendientes', eso no me gusta: debe decir **instrucciones**…"* | La tarjeta ahora se titula **📋 Instrucciones** y lleva **su texto literal** (10 fotos del negocio, de afuera y de adentro, **no** de los productos). Se fueron la lista de 5 puntos, las casillas ☐ y el fondo crema |
| 4 | *"Dice 'tu ficha se está armando, empieza ya'… eso es por las puras, borra"* | Misma franja del punto 2 |
| 5 | *"En lugar de 'cómo se llama y dónde está', solamente **título y ubicación**"* | Paso 1 ahora se llama **"1 · Título y ubicación"**, sin el texto explicativo. El campo dice "Nombre del negocio" con ejemplo **"Cevichería Joaquín"** |
| 6 | *"Ahí debe cargar el cursor… luego dice usar mi ubicación; después de usarla todavía siguen los botones de Chimbote, Nuevo Chimbote, Santa: bórralo… y el botón de cambiar también"* | **Se borraron los chips de distrito y el botón "cambiar"** (y el texto "si no es correcto pulsa cambiar"). Queda solo el mensaje verde "✔ Distrito: Santa (según tu ubicación)". Si no se puede calcular, avisa y no deja guardar |
| 7 | *"A qué rubro pertenece está bien… pero borra el texto de arriba y el de abajo"* | Paso 2 sin textos: título, campo y botón ▾ |
| 8 | *"El punto 3, la cara de tu negocio, bórralo, ya no sirve… solo debe decir 'cargar fotos' con dos botones: Abrir cámara y Abrir galería… hasta un máximo de 8… portada 1 fachada, portada 2 logo: basura, basura, basura"* | **Se borró el paso entero** (`#secCara` con sus dos casillas) y también los textos del paso de fotos. Queda **3 · Cargar fotos** con dos botones: cámara (una por vez) y galería (hasta 8 de golpe), máximo **10 fotos** |
| 9 | *"Cuéntale a la gente: pon un botón que diga grabar… mejor 'mantener pulsado para hablar', como WhatsApp… mientras machucan graba y cuando dejan de machucar deja de grabar"* | **Botón `🎙️ Mantén pulsado para hablar`** (`#btnHablar`) hecho aquí con la Web Speech API (ya no `dictado_voz.js`): `continuous` + reinicio automático, así los silencios **no** cortan |
| 10 | *"El botón de guardar debe aparecer abajo de lo que vendes; actualmente se está escondiendo"* | **Se borró la barra fija** (`.fijo`) y el botón **"💾 Guardar tienda"** vive ahora **dentro de la página**, justo debajo del paso 5 |

### 17.2 Cómo queda el orden (5 pasos)

**Instrucciones → 1 Título y ubicación → 2 Rubro → 3 Cargar fotos → 4 Cuéntale a la gente → 5 Lo que vendes → 💾 Guardar tienda.**

### 17.3 Detalles técnicos que conviene no romper

1. **La primera foto es la portada.** `subir.php` guarda las fotos en `directorio_fotos` con un `orden`,
   y la ficha usa la de `orden` 0 (`ORDER BY f.orden ASC LIMIT 1` en `includes/helpers.php`). Al quitar
   `portada_0`/`portada_1` **no se pierde la portada**: la da la primera foto del paso 3.
2. **Los nombres van con dos cifras** (`tienda_01.webp`, `tienda_02.webp`…) porque `subir.php` recorre la
   carpeta con `glob()` (orden **alfabético**): con `tienda_1`, `tienda_10`, `tienda_2` la galería saldría
   desordenada y hasta la portada podría bailar.
3. **Sigue el `foto[]` con corchetes** (§11.1). Es lo que evita que PHP se quede con una sola foto.
4. **El registro de subidas cambió de campos**: `resumen_fotos` ahora dice
   `negocio:N productos:M` (antes `portada:N local:N productos:M`). Los registros viejos tienen el formato
   antiguo: es normal.
5. **`actualizarObra()` quedó vacía** (se llamaba desde 7 sitios). No borrar la función sin quitar antes
   las llamadas, o la app se rompe con `ReferenceError`.
6. **La lista de distritos sigue viajando** en `sesion.php`: la usa el plan B del detector (reconocer el
   nombre del distrito dentro de la dirección de OpenStreetMap), aunque ya no haya botones.

### 17.4 Errores y fricciones de esta tanda

| # | Error / fricción | Causa | Solución |
|---|---|---|---|
| 1 | **El navegador bajaba solo al paso 2** | `limpiarCaptura()` → `limpiarRubro()` terminaba con `inpRubro.focus()` | `limpiarRubro(enfocar)`: al limpiar la captura se pasa `false`; el foco inicial va al **nombre** |
| 2 | **Al quitar la barra fija, los botones de abajo casi desaparecen juntos** | `#btnGuardar` y `#btnNuevo` viven en el **mismo** contenedor: si se ocultaba el contenedor, se ocultaban los dos | Se ocultan **botón por botón** (ocultar "Guardar tienda" y mostrar "Registrar otro"), no el contenedor |
| 3 | **Los textos "borrados" seguían apareciendo en mi comprobación por HTTP** | Las menciones estaban en **mis propios comentarios** del código (`<div id="estadoObra">`, "La cara de tu negocio") | Se comprobó con el **DOM real** en el navegador: `.obra` y `#secCara` **no existen**. (Lección: comprobar por elemento, no por texto suelto en el HTML) |
| 4 | **El paso de fotos pedía lo mismo dos veces** | Existían "La cara de tu negocio" (fachada/logo) **y** "Tu local y tu entorno" con botones parecidos | Se unificaron en un solo paso de fotos |
| 5 | (decisión con consecuencia) **Sin ubicación ya no hay forma de elegir distrito** | El jefe mandó borrar los botones: *"casi nunca falla el Android"* | Se avisa en pantalla y no deja guardar. **Es reversible**: si algún día falla seguido, se reponen los chips como plan B |

### 17.5 Pendientes de esta tanda

1. **Probar el 🔴 botón de hablar en un celular real** (necesita micrófono y permiso): en la computadora
   solo se comprobó que el botón existe, que la API está disponible y que el JS no falla.
2. **Decidir lo del AUDIO**: hoy la voz se convierte en **texto**. Mandar la grabación de verdad (para que
   el dueño se escuche en su ficha) necesita `MediaRecorder`, guardar el archivo y un reproductor: el jefe
   lo dejó como *"sería ideal"* — queda para otra tanda.
3. **Si algún día el GPS falla seguido**, reponer la elección manual de distrito (§17.4, punto 5).
4. Sigue pendiente: **probar el guardado completo desde el celular**, corregir los **4 negocios con
   coordenadas equivocadas**, decidir los **distritos ocultos** (Samanco, Nepeña, Moro, Cáceres) y borrar
   la basura vieja de la raíz del hosting (esperando el OK del jefe).

---

---

## 18) 🆕 ARREGLOS QUE SALIERON AL PROBARLO EN EL CELULAR (2026-09-10, QUINTA TANDA)

> **Estado: ✅ EN PRODUCCIÓN.** Subido el 2026-09-10 a las **17:27** (tres subidas seguidas: 17:26, 17:27
> y 17:27), con la orden del jefe *"haz las correcciones necesarias… a trabajar"*.
> **md5 final:** `caminante/index.php` = `d47f095382d7632841135f74953b6d88` (**77879 B**).
> Respaldos (**dato histórico**: hoy ya no aplica, regla del 2026-09-12, el hosting es de uso exclusivo de
> la IA): `_vivos_caminante_20260910_172611\`, `_172709\` y `_172739\`.

### 18.1 Lo que dijo el jefe (probando en su Android)

| # | Su queja / pedido | Qué se hizo |
|---|---|---|
| 1 | *"El campo de rubro parece un campo de seguridad o inteligente: cuando le doy clic aparecen las herramientas de usar contraseñas guardadas, tarjeta guardada, formas de pago, hasta el de ubicación… eso genera una capa más de espacio en el teclado y tapa las categorías"* | El campo pasó de `type="text"` a **`type="search"`** y se le pusieron los atributos que dicen "esto no es un usuario, ni una contraseña, ni una tarjeta": `name="q"`, `autocomplete="off"`, `autocorrect="off"`, `autocapitalize="none"`, `spellcheck="false"`, `inputmode="search"`, `enterkeyhint="search"`, **`data-form-type="other"`**. Además se oculta la ✕ nativa del campo de búsqueda (chocaba con el botón ▾) |
| 2 | *"Cuando le dé clic ahí, automáticamente lleve toda la página hasta arriba y me deje el área de texto arriba… normalmente se hacía con un ancla"* | Al **enfocar** el campo (o pulsar ▾) se llama a `subirPasoRubro()`: **salto instantáneo** que deja el paso 2 **7 px debajo de la cabecera fija** (se descuenta el alto de `.topbar`). Comprobado en vivo: `scrollY=310` y el paso 2 a 7 px de la cabecera |
| 3 | *"Debajo de cada foto ha vuelto a salir un botón que dice cámara, galería… solo debe salir abajo al final, donde dice 8 de 10 fotos"* | `crearCeldaFoto()` ya **no** crea los botones por foto: cada foto es **la miniatura + su ✕**. Se borraron el CSS `.foto-caja`, `.foto-acciones` y `.btn-mini`. Los dos botones están **una sola vez**, debajo de la rejilla |
| 4 | *"El botón de grabar repite mucho los textos: 'hola hola hola hola… esto es un hola esto es una prueba hola hola'"* | **Causa:** el texto se **acumulaba** (`finalTxt += …`) y el mismo resultado final llegaba en varios avisos del reconocedor, así que se sumaba una y otra vez. **Arreglo:** en cada aviso se **reconstruye** el texto desde cero recorriendo `ev.results`; lo confirmado se guarda por rondas (`acumulado` + `sesionTxt`) y `sumarSesion()` **no repite** lo que ya estaba. Además, si una ronda vuelve a oír la misma frase, **tampoco se muestra** mientras habla |

**Extra que se añadió al arreglar el punto 2:** la lista de rubros **ajusta su altura al espacio visible**
(`ajustarAltoListaRubros()` con `visualViewport`), así en el celular se ven más rubros por encima del
teclado en vez de quedarse con la altura fija del CSS. Se recalcula al enfocar y también cuando el teclado
termina de abrirse (180 ms y 450 ms después).

### 18.2 Errores y fricciones de esta tanda

| # | Error / fricción | Causa | Solución |
|---|---|---|---|
| 1 | **La lista se quedaba con la altura mínima (150 px)** | Se medía **a mitad del desplazamiento suave**: el campo todavía estaba abajo y el cálculo daba casi cero | El salto pasó a ser **instantáneo** (`behavior:'auto'`) y se recalcula también a los 180 y 450 ms (el teclado tarda en abrir) |
| 2 | **La prueba del salto "no funcionaba"** | El tab de prueba estaba **en segundo plano**: Chrome **no hace scroll** en pestañas ocultas (`document.hidden = true`, `scrollY` se quedaba en 0) | Se **activó la pestaña** y se repitió: `scrollY=310` ✔. **Lección: cualquier prueba de scroll o de teclado necesita la pestaña visible** |
| 3 | **El alto de la lista no se ajustaba al primer toque** | `ajustarAltoListaRubros()` se llamaba **antes** de que la lista se abriera (`sug.hidden` seguía en true y la función salía) | Se llama al final de `pintarRubros()` (los dos caminos: con resultados y sin resultados) |
| 4 | **Un número mal escrito en la guía** | Se anotó "76669 B" para el `index.php` de las 17:12; el tamaño real era **72677 B** | Corregido en §17 (los tamaños se copian de `Get-Item`, no de memoria) |

### 18.3 El texto repetido del micrófono: hizo falta un SEGUNDO arreglo (18:19)

> El jefe probó otra vez en su celular y avisó: **"el botón de grabar, téstelo: sale el texto repetido"**.
> El primer arreglo (18.1, punto 4) **no bastaba**. Aquí queda la causa real y el arreglo definitivo.

**Qué pasaba de verdad:** el micrófono **se reinicia solo** cada cierto tiempo (Chrome corta la sesión y
la app la vuelve a encender para que los silencios no la cierren). Al reiniciarse, **vuelve a oír el
último pedazo** de lo que se dijo y lo reconoce otra vez. Como no es la frase completa, la comparación
simple ("¿es igual a lo último?") **no lo detectaba** y se colaba igual:
`"…esto es una prueba" + "esto es una prueba"` → `"…esto es una prueba esto es una prueba"`.

**El arreglo (tres capas, todas en `caminante/index.php`):**

1. **`fusionarSinRepetir(previo, nuevo)`** — busca el **mayor pedazo de palabras** que coincide entre el
   FINAL de lo ya escrito y el PRINCIPIO de lo nuevo, y descarta ese pedazo. Es lo que caza el
   "re-escuchó un trozo".
   Ejemplos comprobados: `"hola esto es una prueba"` + `"esto es una prueba"` → `"hola esto es una prueba"` ·
   `"hola esto es una prueba"` + `"esto es una prueba de audio"` → `"hola esto es una prueba de audio"`.
2. **`quitarRepeticiones(txt)`** — quita repeticiones **seguidas** de 1 a 3 palabras, que a veces las
   devuelve el propio reconocedor dentro de una misma ronda: `"hola hola hola hola"` → `"hola"` ·
   `"Esto es una prueba de grabación de grabación"` → `"Esto es una prueba de grabación"`.
   (No toca texto normal: `"poco a poco voy creciendo"` queda igual.)
3. **Reinicio limpio del micrófono** — antes, al reiniciar, se volvía a llamar a `start()` sobre el
   **mismo** objeto y sus avisos viejos seguían llegando. Ahora se **desenganchan** los manejadores del
   anterior (`onresult/onend/onerror = null` + `abort()`), se crea **uno nuevo**, y la ronda siguiente
   espera **250 ms** (con un tope de 25 reinicios para no encadenar).

**Lo que se muestra en pantalla ya viene limpio**: `pintarNotaVoz()` aplica las dos limpiezas ANTES de
mostrar, así lo que el captador ve es exactamente lo que queda al soltar el dedo.

**Prueba hecha (simulando las rondas del reconocedor, sin micrófono):** con las rondas
`"hola" · "hola" · "esto es" · "esto es una prueba" · "esto es una prueba" · "una prueba de audio"` el
**texto final es `"hola esto es una prueba de audio"`** — una sola vez cada cosa. Comprobado también en
la página **en vivo**.

**Archivos:** `index.php` = `6c8f53cf558301c71ea02a294612d18a` (80507 B) · respaldo
`_vivos_caminante_20260910_181909\` (carpeta **histórica**: hoy no se respalda el archivo vivo — regla del
2026-09-12, el hosting es de uso exclusivo de la IA) · herramienta de prueba: `__cam_prueba_fusion.js`
(`node __cam_prueba_fusion.js`).

⚠️ **Lección para la próxima:** "arreglar" la voz no es solo no acumular texto. Hay que contar con que
**el micrófono se reinicia y vuelve a oír**. Las tres capas tienen que seguir ahí.

### 18.4 Pendientes de esta tanda

1. **Probar el botón de hablar con la voz de verdad en su celular** (aquí solo se pudo simular la lógica
   del texto; en su Android ya lo probó y salía repetido: eso es lo que se arregló).
2. **Decidir lo del AUDIO grabado** (en vez de texto): sigue pendiente, necesita `MediaRecorder` + guardar
   el archivo + reproductor en la ficha.
3. Si el GPS falla seguido, reponer la elección manual de distrito (§17.4).
4. ~~**El botón 💬 del chat** ("tablones"): el jefe dijo que cada vez le gusta menos, pero se **conserva**
   por ahora. Está en `includes/footer.php`, así que **se ve en todas las páginas**, no solo en Caminante.~~
   🗑️ **RESUELTO (2026-09-13, orden del jefe):** el botón **💬 Chat** (y el chat comunitario entero) **se
   quitó del sitio**. Hoy el **único botón flotante es 🥷 El ninja** (`includes/chatbot_widget.php`).

---

> **Guías base del proyecto:** `GUIA_MAESTRA_CHIMBOTE_XYZ.md` (qué es el sitio, mapa de archivos,
> estado real de cada módulo y qué hacer después) · `REGLAS_DE_ORO_PROYECTO.md` (reglas del jefe,
> canónicas) · `GUIA_DESPLIEGUE_Y_ENTORNO.md` (cómo editar, subir y verificar sin romper el sitio).


_Última revisión: 2026-09-10 (quinta tanda + **segundo arreglo de la voz**, 18:19: el micrófono repetía
palabras porque **al reiniciarse vuelve a oír el último pedazo**; se resolvió con **fusión sin repetir +
limpieza de repeticiones seguidas + reinicio limpio del micrófono**. md5 `6c8f53cf…`, **subido y
verificado en vivo**)._
_2026-09-10 (quinta tanda: **arreglos del celular** — campo de rubro sin el autocompletado de Android,
salto al tope al tocarlo y sin botones debajo de cada foto; subido a las 17:27)._
_2026-09-10 (cuarta tanda: **pantalla limpia** — sin franja amarilla, sin botones de distrito, un solo
paso de fotos, micrófono estilo WhatsApp y botón de guardar dentro de la página; subido a las 17:12)._
_2026-09-10 (tercera tanda: sin cabecera propia, ancho real medido, pie del sitio y **distrito
automático** con `caminante/distrito.php` — subido a las 16:33)._
_2026-09-10 (tarde): **§11.1 — arreglo de las fotos que se perdían** (se enviaban con el campo `foto`
repetido y PHP guardaba solo la última: ahora van como **`foto[]`**), **registro de cada subida**
(`caminante/_registro_subidas.log` + `.jsonl` + `datos.txt` ampliado, motor
`includes/caminante_registro.php`), **aviso nuevo `caminante_incompleto`** y **privacidad**
(`caminante/.htaccess` bloquea `.log`/`.jsonl`/`.txt`)._


