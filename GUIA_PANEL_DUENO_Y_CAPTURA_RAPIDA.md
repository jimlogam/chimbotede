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


# GUÍA — PANEL DEL DUEÑO: CAPTURA RÁPIDA DE INVENTARIO (cámara + dictado) · dechimbote.com

> 🌐 **DOMINIO ACTUAL: `dechimbote.com`** — la dirección oficial es **sin www** (`www.dechimbote.com`
> redirige con **301** al de sin www) · marca visible **DeChimbote.com** · el dominio viejo **ya no
> existe** (mudanza del 2026-09-15: no queda ninguna referencia suya ni en el sitio, ni en la base de
> datos, ni en las guías).
> 🔑 **Datos de acceso FTP (cuenta nueva del dominio nuevo):** están en **`DATOS_DE_ACCESO_Y_DOMINIO.md`**
> (raíz de `D:\RELAX`) y en `C:\Users\Usuario\Documents\apk\seguridad\ftp_config.json` — host
> `ftp.dechimbote.com`, usuario `u196269909.dechimboteftp`. ⚠️ **La raíz viva es `/`** (el FTP *entra* en
> `/public_html`, que es una copia vieja): el detalle, en `GUIA_DESPLIEGUE_Y_ENTORNO.md` §2 y §11.1.

> **Cuándo leer esta guía:** antes de tocar **el panel del dueño** (`panel.php`), la **gestión de
> productos** (`productos.php`) o los dos motores nuevos: la **rejilla de fotos con cámara**
> (`assets/js/captura_rapida.js`) y el **dictado en campos** (`assets/js/dictado_voz.js`).
> **Archivos que toca:** `deploy/productos.php` · `deploy/panel.php` ·
> `deploy/assets/js/captura_rapida.js` · `deploy/assets/js/dictado_voz.js`
> · (motor compartido de fotos: `deploy/assets/js/imagen_optimizar.js` + `deploy/includes/imagenes.php`)
> **Estado:** ✅ **EN PRODUCCIÓN** (2026-09-10) · **Última revisión: 2026-09-10**
> **Guías hermanas:** `GUIA_CAMINANTE_WEB.md` (de dónde viene la experiencia) ·
> `GUIA_BUSCADOR_VOZ.md` (el otro micrófono, el del buscador) · `GUIA_IMAGENES_Y_OPTIMIZACION.md`
> (el motor de fotos) · `REGLAS_DE_ORO_PROYECTO.md` (reglas canónicas).

> 🔓 **El hosting es de uso exclusivo de la IA (orden del jefe, 2026-09-12):** el jefe nunca sube, edita ni renombra nada a mano, así que **no se hace respaldo del archivo vivo** y **no se compara el local con el hosting** (ni md5, ni tamaños). Despliegue = `php -l` → subir solo lo modificado → verificar por HTTP. Las menciones a md5/respaldos que queden en este archivo son **históricas** y no son pasos del flujo.

---

## 0) LO ESENCIAL EN 30 SEGUNDOS

1. **El dueño ya no teclea su inventario.** En su panel hay una **rejilla de casillas 📷**: toca una,
   se abre la **cámara del celular** y la foto aparece **al instante** en la casilla (vista previa,
   sin subir nada todavía). Puede volver a tomarla (tocando la foto) o quitarla con ✕.
2. **La descripción se dicta.** Los campos de **nombre** y **descripción** llevan una píldora
   **🎙️ Dictar** (Web Speech API nativa, **$0**): el texto se escribe solo mientras se habla.
3. **No se inventó ningún endpoint.** Las fotos viajan en el **mismo `input name="fotos[]"`** de
   siempre (se rellenan por `DataTransfer`), así que el POST, el CSRF, los avisos de Telegram y el
   motor de imágenes **no cambiaron ni una línea**.
4. **De dónde viene:** es la experiencia de **Caminante** (`GUIA_CAMINANTE_WEB.md` §4) llevada al
   panel del dueño. Allí el captador está en la calle; aquí el dueño está en su tienda, con la misma
   necesidad: **pocos toques, cero teclado**.
5. **Móvil primero:** casillas cuadradas, botones de ≥44 px, campos de **16 px** (evita el zoom de iOS)
   y contador de obra ("🏗️ Tu producto ya tiene N de 6 fotos").

---

## 1) QUÉ SE AÑADIÓ, EN UNA TABLA

| Dónde | Qué ve el dueño | Archivo |
|---|---|---|
| `panel.php` | Tarjeta granate **"📸 Carga tu inventario con la cámara y la voz"** con un botón por tienda → `productos.php?n=<id>#crear` | `panel.php` |
| `productos.php` → **Captura rápida** | Rejilla de **6 casillas** + botones `📷 Tomar foto` / `🖼️ Elegir de mi galería` + contador + píldoras 🎙️ en nombre y descripción | `productos.php` |
| `productos.php` → cada producto | Casilla **mini** (1 foto) para agregar una foto a un producto existente con la cámara (el botón "Guardar foto" está apagado hasta que hay foto) | `productos.php` |
| `productos.php` → ✏️ Editar | Píldoras 🎙️ en **nombre** y **descripción** | `productos.php` |

El mensaje de éxito también cambió (efecto IKEA): *"🎉 ¡Producto publicado con N fotos! … Puedes
cambiarle el nombre, el precio o las fotos cuando quieras (✏️ Editar)."*

---

## 2) LOS DOS MOTORES (archivos y responsabilidad)

| Archivo | Qué hace | Tamaño |
|---|---|---|
| `assets/js/captura_rapida.js` | **La rejilla**: crea las casillas, abre la cámara (`capture=environment`) o la galería, optimiza, pinta la **vista previa instantánea**, deja las fotos **en orden** dentro del input real del formulario, lleva el contador y los avisos. | 17 488 B |
| `assets/js/dictado_voz.js` | **La píldora 🎙️**: reconocimiento en `es-PE`, escribe en vivo, limpia el dictado al terminar y avisa. Si el navegador no soporta la API, **no pinta nada**. | 17 121 B |
| `assets/js/imagen_optimizar.js` | (ya existía) comprime en el celular: **WebP, máx 1600 px**. `captura_rapida.js` lo usa. | — |

⚠️ **`dictado_voz.js` NO reemplaza a `buscador_voz.js` ni lo toca.** Son dos micrófonos con dos
limpiezas distintas (§5). `buscador_voz.js` sigue siendo el del buscador (con su desplegable fuzzy y
su búsqueda automática); `dictado_voz.js` es el de **cualquier campo de un formulario**.

---

## 3) EL CONTRATO DEL HTML (cómo se usa, sin escribir JS)

### 3.1 La rejilla de fotos

```html
<div class="cz-cap"
     data-cz-captura
     data-cz-max="6"
     data-cz-destino="#czFotosProducto"
     data-cz-contador="#czContProducto"
     data-cz-etiqueta="Foto"
     data-cz-texto="🏗️ Tu producto ya tiene {n} de {max} fotos · la 1.ª será la portada"
     data-cz-texto-cero="📷 Toca una casilla para tomar la primera foto con la cámara"></div>
<input type="file" name="fotos[]" accept="image/*" multiple id="czFotosProducto"
       data-cz-optim="1" hidden>
<span class="cz-cap__contador" id="czContProducto">📷 Toca una casilla…</span>
```

| Atributo (en el contenedor) | Para qué |
|---|---|
| `data-cz-captura` | Marca el contenedor (obligatorio) |
| `data-cz-max` | Nº de casillas (por defecto 6) |
| `data-cz-destino` | Selector del `input[type=file]` que recibirá las fotos **en orden** |
| `data-cz-contador` · `data-cz-texto` · `data-cz-texto-cero` | El contador tipo "obra en progreso" (`{n}` y `{max}` se sustituyen solos) |
| `data-cz-etiqueta` | Texto de cada casilla vacía ("Foto 1", "Foto 2"…) |
| `data-cz-multiple="0"` | Quita el botón "Elegir de mi galería" (solo cámara) |
| `data-cz-requerido` | Selector del botón que debe estar **desactivado hasta que haya foto** |

Variante mini (una sola foto, al lado de un producto que ya existe): `class="cz-cap cz-cap--mini"`
con `data-cz-max="1"` y `data-cz-multiple="0"`.

### 3.2 El dictado

```html
<input    class="form__input" name="titulo"      data-dictado="titulo">
<textarea class="form__input" name="descripcion" data-dictado="descripcion"></textarea>
```

| Atributo | Valores | Efecto |
|---|---|---|
| `data-dictado` | `titulo` · `descripcion` · `texto` | Modo de limpieza (§5) |
| `data-dictado-reemplazar="1"` | — | Reemplaza el contenido en vez de **añadirse** al final |

La píldora **se inserta después del campo** (no se envuelve el campo: cero cambios de estructura).

---

## 4) EL FLUJO DEL DUEÑO, PASO A PASO

1. Entra a **Mi panel** → tarjeta **📸 Carga tu inventario con la cámara y la voz** → botón de su tienda.
2. Cae en **📸 Captura rápida** (`productos.php#crear`).
3. **1 · Las fotos:** toca una casilla → se abre la cámara (foto única) → *"📷 Optimizando la foto…"* →
   la foto aparece en la casilla con la etiqueta **"1 · portada"** y la primera casilla se marca en
   verde. Toca la foto para **volver a tomarla**; ✕ para quitarla. Con `🖼️ Elegir de mi galería` puede
   meter varias de golpe (se cortan al máximo).
4. **2 · Lo que es y cuánto cuesta:** nombre (🎙️ dictable), precio, unidad, tipo, destacado/visible.
5. **3 · Descripción:** píldora **🎙️ Dictar descripción** → habla → el texto aparece en vivo → al
   terminar se limpia y se avisa *"✅ Listo. Revisa lo que escribí…"*.
6. **💾 Publicar producto** → el servidor hace lo de siempre (WebP + versiones 300/800 px, fila en
   `directorio_servicios`, fotos en `directorio_producto_fotos`, aviso a Telegram) → vuelve con el
   mensaje de celebración.

**Para agregar una foto a un producto que ya existe** (lista "Tus productos"): casilla mini 📷 →
`Guardar foto` (se habilita al tener foto).

---

## 4bis) 🏪 «MIS NEGOCIOS»: EDITAR, ELIMINAR CON CONTRASEÑA Y EL ANCLA (2026-09-16)

> 🔴 **Orden del jefe (textual):** *«debe aparecer las tiendas creadas y debajo de cada uno un botón de
> editar y otro de eliminar, **pedir contraseña para eliminar**, y en la parte donde está el número de
> tiendas arriba debe ser un **enlace ancla** al área donde están tus negocios.»*

**Qué quedó en `panel.php`:**

| Pieza | Cómo funciona |
|---|---|
| 🔗 **El ancla** | La tarjeta **«Mis negocios»** del marcador (arriba) es un **`<a href="#mis-negocios">`** (se lee *«MIS NEGOCIOS ↓»*) y el área de abajo es **`<section id="mis-negocios">`** con `scroll-margin-top:96px` (sin eso el título quedaba debajo de la cabecera pegajosa del sitio). Solo se pinta como enlace si es dueño |
| 🏪 **La lista** | Las tiendas del dueño, con su foto, su rubro, su estado y sus vistas, **y ahora también el contador** (*«2 tiendas»*) al lado del título |
| ✏️ **Editar** | Un botón por tienda → **`/mi-tienda?n=<ID>`**: el **editor completo de esa tienda** (§4ter). Ahí se cambia el nombre, el rubro, la zona, el WhatsApp, el horario, las redes, las fotos, el texto y la visibilidad — **siempre con la contraseña del dueño** |
| 🗑️ **Eliminar** | Abre una **ventana** que pide **la contraseña del dueño** y avisa en rojo de que se borra **todo** y no se puede deshacer |
| 🔒 **Los tres candados del borrado** | 1) la **sesión** (`requiere_login()`); 2) **es suya**: se comprueba en la base que `dueno_id` = el usuario de la sesión (jamás se confía en el `negocio_id` que llega del formulario); 3) **su contraseña** con `password_verify()` — el hash se lee de la base porque `login()` **borra** `password_hash` de la sesión. Más el **token CSRF** de siempre. Si algo falla: **no se borra nada** y se avisa con un mensaje claro |
| 🗑️ **Qué borra** | **`includes/negocio_borrar.php` → `negocio_borrar_completo()`**: las filas de sus productos y fotos, **todas las tablas que apunten a la tienda por `negocio_id`** (se le preguntan a la base con `INFORMATION_SCHEMA`, así una tabla nueva no se queda huérfana), la tienda, **los archivos de las imágenes** del hosting y las cachés. Lo usan el panel y el editor del Súper Admin |

**Verificado (2026-09-16) con `__panel_prueba.php` → 31 ✅ · 0 ❌ · 0 restos**, entrando de verdad con
login y cookie y mandando el formulario: las dos tiendas salen con sus dos botones · el ancla está en las
dos puntas · **con la contraseña mal y sin contraseña NO se borra nada** y sale el aviso · **no se puede
borrar la tienda de otra persona** aunque se mande su id a mano · **con su contraseña sí se borra** la
tienda con sus productos y opiniones **y la otra tienda queda intacta**. Y se miró con capturas
(`python __panel_ver.py` → `__panel_vista_arriba_movil.png`, `negocios_movil`, `negocios_pc`,
`modal_borrar`, `despues_borrar`).

---

## 4ter) ✏️ EL EDITOR DEL DUEÑO: `/mi-tienda?n=<id>` — CONTROL TOTAL, SIEMPRE CON SU CONTRASEÑA (2026-09-16)

> 🔴 **Orden del jefe (textual):** *«si el dueño tiene control total de su tienda para editar, **siempre
> debe poner su contraseña**»*.

Antes de esta página el dueño **solo podía tocar sus productos** (`productos.php`): el nombre, el rubro,
la zona, el WhatsApp, el horario, las redes, las fotos y el texto **solo los cambiaba un administrador**
(`editatiendas.php`) o El maestro 🛠️ en la conversación donde creó la tienda. Ya no: **`mi-tienda.php`**
(URL amigable **`/mi-tienda?n=<id>`**, regla en el `.htaccess`) le da la ficha entera.

| Regla | Cómo está hecho |
|---|---|
| 🔐 **SIEMPRE la contraseña** | **Todas** las acciones viajan en **el mismo formulario** y ese formulario exige la contraseña **en el navegador** (`required`, así no se manda nada sin ella) **y en el servidor** (`contrasena_dueno_error()` → `password_verify()` contra el hash de `directorio_usuarios`). Sin contraseña correcta **no se escribe ni una fila** y sale el aviso por `flash` |
| 🏪 **Solo su tienda** | `mt_tienda()` comprueba en la base que el `dueno_id` es el de la sesión (un admin puede entrar a cualquiera: es su trabajo). Con la tienda de otro, la página **ni se abre** y un POST a mano **no cambia nada** |
| 🔗 **El enlace no cambia** | Si corrige el nombre, se guarda el nombre nuevo pero **se conserva el `slug`**: la dirección que ya compartió con sus clientes sigue funcionando |
| 🧩 **Todo en un formulario** | Los botones de cada foto (`⭐ Portada`, `🗑️ Borrar`) y los de acción (`📸 Subir estas fotos`, `✨ Que la IA lo escriba otra vez`, `💾 Guardar los cambios`) son **botones `submit` del mismo formulario**: al tocarlos, un script chico pone en un campo escondido el id de la foto y el `accion` que toca. Se usan `submit` de verdad (no `form.submit()`) para que el navegador **exija** la contraseña |
| 📝 **El texto, como lo lee el cliente** | La descripción **no se muestra en HTML crudo** (sería un lío de etiquetas): se pinta con el **mismo motor de la ficha** (`descripcion_negocio_html()`, con sus botones de WhatsApp). Debajo hay un campo **opcional**: si el dueño escribe su propio texto, reemplaza al de la IA (en párrafos limpios) y avisa de que se pierden esos botones; el botón **✨ Que la IA lo escriba otra vez** los devuelve (usa `tienda_ia_ia_descripcion()`, §2sexies de la guía del constructor) |
| 📸 **Fotos** | Subir varias de una vez (`img_guardar_subida` → WebP + versiones de 800/300), **poner de portada** (la elegida pasa a `orden 0`) y **borrar** (la fila **y el archivo** del hosting, con `img_borrar()`) |
| 👁️ **Visibilidad** | El dueño puede **ocultar** su tienda sin borrarla (`estado = inactivo`) y volver a mostrarla |
| 🗑️ **Borrar la tienda** | Sigue en el panel (`#mis-negocios` → 🗑️ Eliminar) y **también pide la contraseña** (§4bis). Al pie del editor hay el enlace |
| 🛒 **Los productos** | No se mezclan aquí: un botón bien visible lleva a `productos.php?n=<ID>` (*«Mis productos (N)»*). **Y esa página también pide la contraseña** (2026-09-16): sin desbloquear, solo se ve la puerta (`accion=desbloquear`) y **los POST no se atienden** (un `crear` a mano no crea nada). Al verificarla, la sesión queda desbloqueada **30 minutos para ESA tienda**: así la **captura rápida con cámara y voz** sigue siendo lo que promete el panel (*«toma la foto, dicta y publica, nada de teclear»*) en vez de pedir la clave en cada producto. Los administradores no pasan por la puerta (administran decenas de tiendas) |

**Verificado (2026-09-16) con `__mitienda_prueba.php` → 44 ✅ · 0 ❌ · 0 restos**, por el camino real
(login con cookie → página → POST del formulario): la página abre con todos los campos y el texto
renderizado · **sin contraseña y con una incorrecta NO cambia nada** (ni el horario, ni el nombre, ni el
WhatsApp, ni una foto) · **con la suya sí** (y el `slug` sigue igual) · **la tienda de otra persona ni se
abre ni se cambia** · las fotos se suben, se pone la portada y se borra (fila + archivo) · **sin la
contraseña correcta la foto NO se borra** · el campo del texto vacío **no toca** el texto de la IA y con
texto propio se guardan párrafos limpios · **✨ la IA reescribe el texto** pasando el listón del copy · y
**la puerta de los productos** (sin desbloquear pide la clave y no atiende POST; con la suya entra y ya
puede crear su producto).
Capturas (Chrome aparte, sin tocar el navegador del jefe): `python __mt_ver.py` →
`__mt_vista_arriba.png`, `__mt_vista_fotos.png`, `__mt_vista_firma.png`, `__mt_vista_pc.png`.

### 4ter.1) 🆕 SEGUNDA TANDA DEL EDITOR (2026-09-16, noche — pedido del jefe)

> **Lo que pidió, textual:** *«falta la opción de "todos", refiriéndose a todos los distritos · abajo de
> "tu dirección" falta "tu ubicación" y de ahí detectar la calle o dirección, ponle el consejo "los
> negocios con ubicación reciben más clientes diariamente" · en "cómo te contactan tus clientes" debes
> indicarle al cliente que puede tener hasta 4 teléfonos, los inputs apilados en móvil, el primero es el
> principal y el texto del input va en rojo, los otros son secundarios (uno solo para llamadas y otro
> solo para WhatsApp) · ocupa todo el ancho de pantalla, sin márgenes a la izquierda, así tendremos más
> espacio para editar en móvil · la sección fotos va en cuadrícula de 3 columnas × 2 filas y al darle clic
> carga un modal con todas las fotos, la opción de elegir portada, borrar o agregar · y en "tu
> contraseña" recuérdale que su contraseña está en su WhatsApp y que debe memorizarla porque siempre se
> le pedirá para hacer cambios · y revisa que el sitio no se deslice a los costados.»*

| 🆕 | Cómo quedó |
|---|---|
| 🌎 **TU ZONA → «Todos los distritos»** | Nueva opción (`value="todos"`) además de los 4 distritos visibles. Se guarda como **`distrito_id = NULL`** (toda la provincia) **y** como **cobertura de todos los distritos visibles** en `directorio_negocio_cobertura` con **`es_todos = 1`** (`cobertura_todos_marcar()`): así el buscador —que ya cruza esa tabla— la encuentra **en el distrito que sea**, y al elegir después un distrito concreto el editor borra **solo esas filas** (`cobertura_todos_quitar()`, la cobertura real de un 🧰 servicio a domicilio no se toca). La ficha lo dice: **«🌎 Toda la provincia (todos los distritos)»** |
| 📍 **«Tu ubicación» (debajo de la dirección)** | Botón **📍 Tu ubicación** → `navigator.geolocation` + **dirección inversa de OpenStreetMap** (`nominatim.openstreetmap.org/reverse`, el mismo camino que ya usa Caminante, sin claves ni costo): llena la **calle** en el campo de dirección (solo si estaba vacía; si no, aparece el botón **✅ Usar esta dirección**), **detecta el distrito** y lo pone en «Tu zona» (comparando sin tildes y **de nombre más largo a más corto**, para que «Nuevo Chimbote» gane a «Chimbote»), y guarda **`lat`/`lng`** al tocar 💾 Guardar. Con esas coordenadas la ficha **pinta su mapa** y la tienda entra en «cerca de mí». ⚠️ Las coordenadas **fuera del Perú se ignoran** (no se puede romper el mapa desde aquí). El consejo pedido va arriba del botón: **«Los negocios con ubicación reciben más clientes diariamente»** |
| 📞 **Hasta 4 teléfonos** | El **principal** sigue siendo `directorio_negocios.whatsapp` (no se movió nada: botón verde de la ficha, `api/lead.php`, el copy de la IA, el chatbot y el carrito lo leen de ahí) y va en **rojo** con el rótulo *«1) Tu número principal (WhatsApp)»*. Los **3 secundarios** viven en la tabla NUEVA **`directorio_negocio_telefonos`** (`numero`, `tipo` ∈ `ambos`/`llamada`/`whatsapp`, `orden`), **apilados uno encima de otro** (en móvil el número arriba y su papel debajo). El campo viejo **`telefono`** —el que usa el botón **📞 Llamar** de la ficha— se recalcula solo: **el primer secundario que reciba llamadas**. 🔴 **La trampa que se midió antes de tocar nada: 951 tiendas tienen el MISMO número en `telefono` y en `whatsapp`** (una con el +51 y la otra sin él) → si el dueño guarda cualquier cosa, esas tiendas **no pueden perder su «Llamar»**: cuando no hay secundarios de llamadas y su `telefono` era su WhatsApp, **`telefono` sigue al número principal** (`tel_normalizar()` compara ignorando el 51; el número repetido **no** se guarda como secundario) |
| 📐 **A todo el ancho** | `.main{padding:0}` y `.mt{max-width:1020px;padding:10px 0 20px}` inyectados por la propia página: **las tarjetas van de borde a borde** (su relleno interno es el que da el aire) y los textos sueltos llevan 13 px a los costados. En ≥760 px vuelve el relleno del `main` (16 px). **Medido: 0 px de desborde horizontal a 320 / 360 / 390 / 414 / 768 / 1366 px** |
| 📸 **Fotos 3 × 2 + visor (modal)** | La tarjeta muestra **6 fotos en 3 columnas** (la 1.ª con el sello **PORTADA**; si hay más, la 6.ª lleva el **«+N»**). **Al tocar cualquier foto se abre el visor** (`#mtVisor`, `position:fixed`, se cierra con ✕, tocando el fondo o con Escape) con **TODAS** las fotos, cada una con **⭐ Portada** / **🗑️ Borrar**, más el bloque **📷 Agregar fotos nuevas** (un solo `input[type=file]`, dentro del visor, para no duplicar el campo). El visor va **dentro del formulario**: los botones son `submit` de verdad y el formulario —con la contraseña— viaja igual. ⚠️ Si falta la contraseña, el aviso sale **dentro del visor** (el campo real está al final de la página y el visor lo tapa): el script lo detecta, enfoca el campo y lo dice. En pantalla grande la cuadrícula se limita a **600 px** y se centra (si no, cada foto saldría de 440 px) |
| 🔐 **La contraseña, recordada** | El bloque final dice, con una caja verde: **«Tu contraseña está en tu WhatsApp»** (la que le llegó/se guardó al crear la tienda, la misma con la que entra) y **«🔑 Memorízala: te la voy a pedir SIEMPRE que quieras hacer un cambio»**, con el enlace a `/recuperar` y, si la cuenta tiene teléfono, **su usuario para entrar es su número** |

**Piezas nuevas de esta tanda:** `deploy/migrar_tiendatelefonos.php` (migrador **idempotente** que creó
la tabla `directorio_negocio_telefonos` y la columna `es_todos`; **se autodestruye al correr** — ya
corrido y comprobado en vivo el 2026-09-16: pruebas dentro de una transacción con ROLLBACK y 0 restos) ·
`helpers.php` (`negocio_telefonos_extra`, `telefono_tipo_texto`, `telefono_extra_html`,
`telefono_para_marcar`, `tel_normalizar`, `zona_todos_activa`, `cobertura_todos_marcar`,
`cobertura_todos_quitar`) · `negocio.php` (los secundarios en las 3 plantillas: dato con su papel y sus
botones `tel:` / `wa.me`, y el rótulo «Toda la provincia»).

**Verificado (2026-09-16, noche) con `__mitienda_prueba.php` → 70 ✅ · 0 ❌ · 0 restos.** Lo nuevo que se
comprueba de verdad, por el camino real: la opción **«Todos los distritos»** (queda `distrito_id` NULL +
4 filas `es_todos=1`, **el buscador la encuentra en los 4 distritos**, la ficha lo dice y al volver a un
distrito la marca se borra) · los **4 teléfonos** (el repetido del principal se descarta, `telefono`
queda con el que recibe llamadas, **la ficha los muestra con su papel y sus botones**, y la tienda que
tenía el mismo número para todo **no pierde su «Llamar»**) · la **ubicación** (se guarda y **la ficha ya
pinta su mapa**; una coordenada fuera del Perú se ignora) · y el HTML con la cuadrícula de 3 columnas,
el visor, el principal en rojo y el recordatorio de la contraseña. Desborde horizontal medido en el
navegador (iframe de 320 a 414 px) con el visor abierto: **0 px** (y 0 elementos fuera del ancho).

### 4ter.2) 🆕 TERCERA TANDA DEL EDITOR (2026-09-16, noche — pedido del jefe)

> **Lo que pidió, textual:** *«en "cómo te contactan tus clientes" muestra el número actual en rojo y un
> botón de "Agregar más números"; esto abre un modal con 4 campos para más teléfonos y darles un valor
> (ejemplo solo ventas, solo atención, solo WhatsApp, o para otras tiendas que pueda tener)… en el modal
> aparece el input del número y un select con varias opciones de cómo usar ese número, y un botón de
> agregar más números · sería bueno también la opción de "Agregar mi RUC" y dar el consejo de que los
> negocios con RUC son los que se llevan los contratos más grandes, "sí funciona", dejando claro que es
> buena estrategia poner el RUC y el correo electrónico… es opcional pero mejora mucho las ventas · en
> "Tu horario de atención" abre un modal y ofrece opciones con check: 24 h, 24 h solo fines de semana,
> todos los días en las mañanas, solo en las tardes, solo en las mañanas, todas las anteriores, de 9 pm a
> 2 am y "otro: especificar" · y "Tus redes sociales" ponlo como un botón que diga "Mostrar mis redes
> sociales" y que abra un modal con al menos 6 redes.»*

| 🆕 | Cómo quedó |
|---|---|
| 📞 **La tarjeta de contacto, limpia** | El **número principal va en ROJO** (como pidió) y **los demás ya no ocupan la pantalla**: debajo hay **chips** con lo que el dueño tiene puesto (número + para qué es) y el botón **➕ Agregar más números**, que abre su **modal** |
| 📞 **El modal de los otros números** | **4 campos** al abrir (número + **SELECT de uso** en cada uno) y el botón **➕ Agregar otro número**, que añade filas **hasta 6** (ahí el botón se esconde y avisa del tope). El **select** tiene los **8 usos**: 📱 Llamadas y WhatsApp · 💬 Solo WhatsApp · 📞 Solo llamadas · 🛒 **Solo ventas** · 🧑💼 **Solo atención al cliente** · 🏪 **Para otras tiendas / mayoristas** · 📦 Solo pedidos y delivery · ✳️ Otros usos. Los **chips de la tarjeta se rehacen en vivo** mientras escribe, para que vea lo que va a guardar. Números vacíos o repetidos del principal **no se guardan** |
| 🪪 **RUC + correo (opcional, "pero vende más")** | Tarjeta propia con el consejo en verde: *«Los negocios con RUC se llevan los contratos más grandes. Sí funciona: las empresas y las instituciones buscan proveedores formales.»* Dos campos: **RUC** (11 dígitos; se pinta en la ficha como **20-12345678-9**) y **correo del negocio**. ⚠️ **Un RUC que no tenga 11 dígitos y un correo mal escrito NO se guardan** (mejor vacío que un dato falso). En la ficha salen como datos 📇 del bloque de contacto y en el bloque «Contacto» de la plantilla C |
| 🕒 **El horario se elige, no se teclea** | La tarjeta **lee** el horario y el botón **🕒 Elegir mi horario** abre el modal con **8 casillas** (24 h todos los días · 24 h solo fines de semana · todos los días en las mañanas · solo en las tardes · solo en las mañanas · abro en varios de esos horarios · de 9 de la noche a 2 de la mañana · **otro: especificar**). **Se pueden marcar varias** y el texto se arma solo con « · » (máx. 255, que es lo que aguanta la columna) y se ve en el momento en **«Así lo verá el cliente»**. Un horario viejo que no sea ninguna opción entra en **«otro»** tal cual (así no se pierde ni se ensucia con prefijos) |
| 📘 **Las redes, en su modal** | La tarjeta muestra **chips** de las que ya tiene y el botón **📘 Mostrar mis redes sociales** abre el modal con **8 redes** (el jefe pidió «al menos 6»): **Facebook · Instagram · TikTok · YouTube · X (Twitter) · Telegram · LinkedIn · Página web**. Acepta la dirección completa (`facebook.com/tutienda`) **o solo el usuario** (`@tutienda`) y el enlace se arma solo. En la ficha salen como botones en **«🔗 Síguenos»** (plantilla A) y en **«🔗 Nuestras redes»** (C), y entran al `sameAs` del JSON-LD de Google ⚠️ **antes NO se mostraban en ninguna parte** (se guardaban y nadie los veía) |

**Piezas nuevas:** `deploy/migrar_tienda_ruc_redes.php` (migrador **idempotente**, ya corrido y
autodestruido: creó **6 columnas** en `directorio_negocios` — `ruc`, `email`, `youtube`, `twitter`,
`telegram`, `linkedin` — y amplió el ENUM de `directorio_negocio_telefonos.tipo` a los **8 usos**) ·
`helpers.php` (`telefono_tipos_catalogo`, `telefono_tipo_icono`, `telefono_tipo_acciones`,
`negocio_redes_catalogo`, `red_enlace`, `negocio_redes_con_valor`, `ruc_limpiar`, `ruc_bonito`;
`negocio_telefonos_extra` sube a **6**).

**🔴 LAS TRES TRAMPAS QUE COSTARON TIEMPO EN ESTA TANDA (leer antes de tocar esto):**

1. **El ENUM guarda VACÍO un valor que no está en la lista, sin dar error** (la misma trampa de
   `tipo_producto`, §11). Por eso el editor **valida contra `telefono_tipos_catalogo()`** y un uso
   inventado cae en `ambos`. La prueba lo comprueba (`un uso inventado NO queda vacío`).
2. **Cloudflare OFUSCA los correos al vuelo**: la ficha sale con `<a href="/cdn-cgi/l/email-protection#…">`
   en vez de `mailto:`, así que **la dirección NO se lee en el HTML que baja por HTTP** y una prueba que
   busque el correo «falla» sin que haya nada roto (el navegador lo muestra bien, y de paso protege al
   dueño del spam). Se verifica por la fila **«Correo»** + la marca `cdn-cgi/l/email-protection`.
3. **El dominio se duplicaba en los enlaces de las redes** (`https://facebook.com/facebook.com/tutienda`):
   los `placeholder` del editor invitan a escribir el dominio sin `http`, y `red_enlace()` le ponía la
   base encima. Ahora, **si el valor ya trae un dominio (`.` en el primer trozo) solo se le pone el
   `https://`**. La prueba tiene un check propio para que no vuelva a pasar.

**Verificado (2026-09-16, noche) con `__mitienda_prueba.php` → 89 ✅ · 0 ❌ · 0 restos**: las 8 redes se
guardan en sus columnas y salen en la ficha **con su enlace de verdad** (`@usuario` → la red, y **sin
dominios duplicados**) · el **RUC** se guarda sin espacios y se pinta **20-60123456-7**, el **correo** sale
en la ficha (ofuscado por Cloudflare) y **un RUC/correo inválido no se guarda** · los **4 números con sus
usos** se guardan, `telefono` queda con el que **recibe llamadas** y la ficha los muestra con sus botones ·
un **uso inventado** cae en «Llamadas y WhatsApp» (no queda vacío) · y el HTML trae el botón y el modal de
los números, el bloque del RUC con su consejo, las 8 casillas del horario y las 8 redes.
En el navegador (pestaña de pruebas, sin tocar la del jefe) se comprobó el JavaScript: el modal abre y
cierra, **«➕ Agregar otro número» añade filas hasta 6**, los **chips se actualizan en vivo**, las
**casillas del horario arman el texto** (y la vista previa se refresca en la tarjeta **y** dentro del
modal) y el **modal de redes** muestra las 8 con sus chips. **Desborde horizontal con los 3 modales
abiertos: 0 px a 360, 390 y 414 px.**

---

## 5) LA LIMPIEZA DEL DICTADO — Y POR QUÉ ES AL REVÉS QUE EN EL BUSCADOR

> Esta es **la decisión más importante del módulo**. Confundirlas rompe una de las dos cosas.

| | `buscador_voz.js` (buscar) | `dictado_voz.js` (describir) |
|---|---|---|
| Qué limpia | Mandos, muletillas **y CONECTORES** (`en`, `de`, `la`…) | Mandos del principio y muletillas del final |
| Por qué | El buscador fuzzy exige que **todas** las palabras coincidan: un "en" obligaría a que cada resultado tuviera "en" | Una descripción es **una frase**: "Ideal para motos de trabajo, resistente al agua" **sin** "para/de" no significa nada |
| Ejemplo | `Pollería en Nuevo Chimbote` → `Pollería Nuevo Chimbote` | `somos una cevichería en Nuevo Chimbote con delivery` → `Somos una cevichería en Nuevo Chimbote con delivery.` |
| Extra | Red de seguridad: si queda <3 letras, busca el dictado tal cual | Red de seguridad igual + **punto final** si la frase pasa de 24 caracteres + `…punto` oral → `.` |

⚠️ **Los MANDOS no incluyen "producto", "servicio", "texto" ni "nota" sueltos.** Son palabras con las
que **empiezan negocios reales de Chimbote** ("Servicio de instalación…", "Productos de limpieza",
"Textos escolares"): al quitarlas el nombre quedaba mutilado (*"De instalación…"*). El caso lo cazó la
prueba `__cap_2_prueba.js` (§7). Las frases completas ("el producto es…", "nombre del producto…") sí
se quitan.

**El dictado se AÑADE a lo ya escrito** (se puede dictar en dos tandas: primero "Ideal para motos",
después "resistente al agua"); si el dueño quiere reemplazar, el campo lleva
`data-dictado-reemplazar="1"`.

---

## 6) TRAMPAS YA PISADAS (no repetir)

1. **`data-cz-optim="1"` en el input destino es OBLIGATORIO.** `imagen_optimizar.js` se engancha a
   todo `input[type=file][accept*=image]`; si lo enganchara, **reordenaría/reemplazaría** los archivos
   y **se perdería la portada**. El guard (`input.dataset.czOptim === '1'` → salir) es lo que lo evita.
2. **Nunca poner `required` en el input de archivos oculto.** Un control inválido **no enfocable**
   bloquea el envío con *"An invalid form control … is not focusable"* y el formulario **no se envía
   nunca**. Para eso está `data-cz-requerido`: el botón arranca desactivado y se enciende con la foto.
3. **`input:not([type])` es imprescindible en el CSS.** Los campos de `productos.php` **no llevan
   atributo `type`**: el navegador los trata como texto, pero el selector `input[type="text"]` **no los
   alcanza** y se quedaban en **15 px** → iOS hace zoom al enfocar. Medido: 15 px → **16 px**.
4. **La API pública `CZCaptura.agregar()` también comprime.** Si un script (o una prueba) mete archivos
   por la API, tienen que pasar por el mismo `CZImg.optimizarLista()` que el flujo de la cámara. Al
   principio no lo hacía y las pruebas mostraban **PNG de 62 KB** en vez de **WebP de 4 KB**.
5. **`insertar()` (lo interno) NO comprime**: úsalo solo si los archivos ya vienen optimizados.
6. **El orden de los `<script>` importa:** `imagen_optimizar.js` → `dictado_voz.js` →
   `captura_rapida.js` (la rejilla usa `CZImg`).
7. **El evaluador del navegador corta las promesas a ~100 ms.** Lanzar la prueba y **leer el resultado
   en llamadas aparte**; si se deja una promesa larga "colgada" de un intento anterior, la prueba
   vuelve a correr sobre una página ya sucia (pasó: la segunda pasada encontró 5 fotos donde esperaba
   0). **Recargar la página antes de cada pasada.**
8. **Otra sesión puede estar desplegando a la vez.** El 2026-09-10 el deploy de **otra** sesión
   (panel del vendedor) subió **mi** `panel.php` antes de que yo lo subiera: el md5 del vivo cambió sin
   que yo tocara el FTP. Entonces el script de despliegue comparaba el **vivo** contra el md5 esperado y
   **abortaba** si difería; había que re-verificar y seguir. *(hoy ya no aplica: regla del 2026-09-12, el
   hosting es de uso exclusivo de la IA — no se compara el local con el hosting; si algo se ve raro, se
   relee el archivo y se sigue).*
9. **Los temporales con clave se borran** (`__cap_probe.php`): se comprueba que dan **404** y se borra
   también la copia local, para que un despliegue futuro no lo resucite.
10. 🔴 **`[hidden]` NO GANA CONTRA UN `display` NUESTRO (2026-09-16 — es la trampa 37 de la guía del
    constructor de tiendas, y volvió a morder aquí).** La ventana de eliminar del panel nace con
    `hidden`, pero su CSS es `display:flex`: el atributo es una regla **floja** (`[hidden]{display:none}`)
    y **el navegador la pisa**. Resultado: la ventana **salía ABIERTA encima del panel** en cuanto se
    entraba. **Lo cazó la captura** (`__panel_vista_negocios_movil.png`), no el código. Arreglo:
    **`.pn-modal[hidden]{display:none!important}`**. Regla general: si algo se enciende y se apaga con
    `.hidden` (o el atributo `hidden`), su CSS **no puede** traer un `display` sin esa línea.

---

## 7) CÓMO PROBARLO (comandos reales, en orden)

```powershell
# 1) Sintaxis
node --check D:\RELAX\deploy\assets\js\captura_rapida.js
node --check D:\RELAX\deploy\assets\js\dictado_voz.js
C:\xampp\php\php.exe -l D:\RELAX\deploy\productos.php
C:\xampp\php\php.exe -l D:\RELAX\deploy\panel.php
# 2) Limpieza del dictado, sin navegador (17 casos: títulos y descripciones)
node D:\RELAX\__cap_2_prueba.js          # debe terminar en "TODO OK"

# 3) Banco de pruebas LOCAL (26 comprobaciones, cámara y micrófono simulados)
#    Abrir en una PESTAÑA NUEVA:  file:///D:/RELAX/__cap_3_banco_pruebas.html
#    y lanzar  window.__runTests()  (por pasos: ver trampa 7)

# 4) Sonda EN VIVO dentro del sitio real (header + footer + CSS global)
#    python D:\RELAX\__subir_uno.py __cap_probe.php
#    https://dechimbote.com/__cap_probe.php?k=<CLAVE>   (14 comprobaciones)
#    y AL TERMINAR:  python D:\RELAX\__cap_7_borrar_sonda.py   (borra y comprueba 404)

# 5) Desplegar = 3 pasos: php -l → subir solo lo modificado → verificar por HTTP
python D:\RELAX\__subir_uno.py productos.php   # y cada archivo modificado, uno por uno
#    Sin respaldo del vivo y sin comparar local ↔ hosting (regla del 2026-09-12).
#    ⚠️ __cap_4_desplegar.py queda como HISTÓRICO (no se usa): respaldaba el vivo y comparaba md5.

# 6) 🆕 EL PANEL: tiendas + editar + eliminar con contraseña + el ancla (§4bis)
python D:\RELAX\__sonda_run.py __panel_prueba.php panel-2026-09-16-borrar
#    Crea un dueño de prueba con 2 tiendas (y una tercera de otra persona), ENTRA con login de verdad,
#    mira el HTML del panel y prueba el borrado: sin contraseña, con la mala, con la de otro y con la
#    suya. Devuelve el HTML del panel entre ===HTML=== (para las capturas) y limpia todo al final.
python D:\RELAX\__panel_ver.py
#    Saca las fotos del panel (Chrome aparte, sin tocar el navegador del jefe):
#    __panel_vista_arriba_movil.png · negocios_movil · negocios_pc · modal_borrar · despues_borrar

# 7) 🆕 EL EDITOR DEL DUEÑO (/mi-tienda), siempre con su contraseña (§4ter y §4ter.1)
python D:\RELAX\__sonda_run.py __mitienda_prueba.php mitienda-2026-09-16-clave go
#    Crea un dueño de prueba con su tienda (y otra de otra persona), ENTRA con login de verdad y prueba:
#    sin contraseña / con la mala / la tienda ajena (NO debe cambiar nada) · con la suya (SÍ) ·
#    subir fotos, portada y borrar · «✨ Que la IA lo escriba otra vez» · 🌎 «Todos los distritos»
#    (queda NULL + cobertura `es_todos=1` y el buscador la encuentra en los 4 distritos) ·
#    📞 los 4 teléfonos (el repetido se descarta, `telefono` = el que recibe llamadas, la ficha los
#    muestra con sus botones y la tienda del mismo número para todo no pierde su «Llamar») ·
#    📍 la ubicación (se guarda, la ficha pinta su mapa y una coordenada fuera del Perú se ignora) ·
#    🪪 RUC + correo (se guardan, salen en la ficha y un dato inválido NO se guarda) ·
#    📘 las 8 redes (enlaces sin dominio duplicado) · 📞 los 4 usos del teléfono (y que un uso
#    inventado no quede vacío) · y limpia todo al final.  ⚠️ SIN `go` hace simulacro.
#    Resultado de referencia (2026-09-16, noche): 89 ✅ · 0 ❌ · 0 restos → __mitienda_prueba_go_resultado.json
python D:\RELAX\__mt_ver.py
#    Fotos del editor: __mt_vista_arriba.png · __mt_vista_fotos.png · __mt_vista_firma.png · __mt_vista_pc.png
```

**Medir el desborde horizontal sin tocar la ventana del jefe** (2026-09-16): en una pestaña de pruebas
—o mejor **sin abrir ninguna**, con `browser_evaluate` sobre una pestaña ya abierta— se crean
**iframes del ancho del celular** apuntando a la misma URL y se comparan
`documentElement.scrollWidth` con `clientWidth` (y se buscan los elementos con `rect.right` fuera del
ancho). Con los 5 anchos (320/360/390/414/768) el editor da **0 px de desborde**. ⚠️ El scroll de un
iframe con `scroll-behavior:smooth` **no se mide en el mismo instante**: leerlo en una llamada aparte.

El banco de pruebas y la sonda usan la **misma** técnica que el buscador por voz: se **inyecta una
`SpeechRecognition` falsa** (definida **antes** de `dictado_voz.js`, que lee la API al arrancar) y las
fotos se crean con un `<canvas>`. Así se prueba el camino real **sin hablar y sin abrir la cámara**.

---

## 8) CÓMO SE DESPLIEGA

- 🔓 **Despliegue = 3 pasos, sin respaldos:** **`php -l`** → subir **solo lo modificado**
  (`python __subir_uno.py <ruta relativa>`) → **verificar por HTTP**. No se respalda el archivo vivo antes
  de subir y **no se compara nada entre local y hosting** (ni md5, ni tamaños): el hosting es de uso
  exclusivo de la IA y `D:\RELAX\deploy` **es la verdad** (regla del 2026-09-12).
- El script viejo **`python __cap_4_desplegar.py`** (respaldaba el vivo en
  `__backup_captura_rapida_<fecha>\`, comparaba el md5 del vivo con el esperado y abortaba si difería,
  y después verificaba md5 local = hosting) queda como **HISTÓRICO (no se usa)**, igual que esa carpeta.
- **`?v=`**: los dos JS son **archivos nuevos**, así que su `?v=1` no pisa ninguna caché; el CSS va
  **inline en `productos.php`** (regla de `GUIA_DESPLIEGUE_Y_ENTORNO.md` §6: componente puntual → CSS
  inline), así que **no se toca `components.css` ni `includes/header.php`**.
- **Verificación esperada** (2026-09-10): `panel.php` **302**, `productos.php?n=1` **302** (piden
  login), `assets/js/captura_rapida.js` **200 · 17 488 B**, `assets/js/dictado_voz.js` **200 · 17 121 B**,
  `index.php` **200** (el sitio sano).

---

## 9) DATOS MEDIDOS (2026-09-10)

| Medición | Resultado |
|---|---|
| Limpieza del dictado (Node, 17 casos) | **TODO OK** |
| Banco de pruebas local (cámara + voz simuladas) | **26 comprobaciones · 0 fallos** |
| Sonda en vivo (dentro del sitio real) | **14 comprobaciones · 0 fallos** |
| Compresión real de una foto en la sonda | **62 KB (PNG) → 4 KB (WebP)** |
| Fotos serializadas en el POST | **6 de 6** en `fotos[]` (comprobado con `new FormData(form)`) |
| Campos del formulario en móvil | **16 px** (antes 15 px → zoom en iOS) |
| Peso de los archivos nuevos | `captura_rapida.js` **17 488 B** · `dictado_voz.js` **17 121 B** |

---

## 10) COMPATIBILIDAD, PERMISOS Y AVISOS

| Navegador | Cámara | Micrófono 🎙️ |
|---|---|---|
| **Chrome Android** (el de la calle) | ✅ abre la cámara del celular | ✅ |
| Chrome / Edge escritorio | ✅ (elige archivo o webcam según el sistema) | ✅ |
| **Firefox** | ✅ (archivo/galería) | ❌ no soporta la API → **la píldora no se pinta** |
| **Safari (iPhone)** | ✅ abre la cámara | ❌ la píldora no se pinta (el iPhone tiene su 🎙️ en el teclado) |

- **HTTPS obligatorio** (el sitio lo tiene) y **permiso de micrófono** la primera vez.
- La píldora avisa (en español, sin tecnicismos) si el micrófono está bloqueado, si no hay micrófono,
  si no hay conexión o si no se entendió. **Nunca deja el campo en blanco**: si la limpieza no deja
  nada útil, escribe el dictado tal cual.
- El idioma se pide `es-PE`; si el navegador no lo tiene se reintenta solo con `es-ES` y `es-MX`.

---

## 11) CÓMO REVERTIR

1. Reponer `productos.php` y `panel.php` desde la carpeta **histórica**
   `D:\RELAX\__backup_captura_rapida_<fecha>\` (el del **12:38:49** es el original) y subirlos con
   `python __subir_uno.py <ruta relativa>`. ⚠️ Esa carpeta ya **no se genera**: hoy no se respalda el
   archivo vivo (regla del 2026-09-12, el hosting es de uso exclusivo de la IA).
2. Borrar del hosting `assets/js/captura_rapida.js` y `assets/js/dictado_voz.js` (o dejarlos: son
   inertes si ninguna página los carga, porque **solo** actúan donde hay `data-cz-captura` /
   `data-dictado`).
3. Verificar por HTTP y **revertir también el local**.

---

## 12) 🔤 TÍTULOS Y UNIDADES PREDICTIVOS (2026-09-10) — "escribe y te sugiero"

> **Pedido del jefe:** *"así como en los rubros el campo va sugiriendo mientras escribes, en los TÍTULOS de
> producto también"* — y que la lista salga de **lo que el sitio ya tiene**, no de términos inventados.
> **Estado:** ✅ **EN PRODUCCIÓN (Etapa 1)** · verificado en vivo con datos reales.

**Qué hace:** mientras el dueño escribe el **nombre del producto**, aparece un desplegable con títulos que
**ya existen en el sitio** (y al escribir la **unidad**, las unidades reales ya usadas). Si ignora las
sugerencias y sigue escribiendo lo suyo, **su texto se respeta** (Regla de Oro n.º 2: la ayuda nunca obliga).

| Pieza | Archivo | Qué hace |
|---|---|---|
| Diccionario | `includes/terminos_sugerir.php` | Construye el diccionario con los **títulos reales** de productos activos (hoy **2 862 términos**) + los **subrubros**, lo guarda en `cache/terminos.json` (1 h) y **compara** con `terminos_sugerir($q, 8)` |
| Endpoint | `api/terminos.php` | `?q=canci` → sugerencias · `?campo=unidad&q=por t` → unidades reales. Solo lectura |
| Desplegable | `assets/js/terminos_sugerir.js` | Se activa **con un atributo**: `data-terminos="1"` (títulos) o `data-terminos="unidad"`. Teclado ↓↑/Enter/Esc, táctil de 44 px, fuente 16 px |
| En el panel | `productos.php` | Atributos en los 2 formularios (crear y editar) + `terminos_olvidar_cache()` al crear/editar/borrar |
| En el asistente | `crear_negocio.php` | Atributos en `#inTituloP`, `#inTituloV` y `#inUnidadP` (los pasos se pintan por JS: el desplegable se engancha solo con un `MutationObserver`) |

**Cómo se ordenan las sugerencias (y por qué así):**

1. **Empieza igual** ("zapati" → *Zapatillas*) → 2) **empieza una palabra** ("flor" → *Ramo de flores*) →
   y dentro de cada grupo, por **popularidad** (en cuántos productos se usa) y luego el más corto.
2. ⚠️ **NO se usa la coincidencia "por dentro"** de la palabra. Probado el 2026-09-10: así salían
   *"udio" → **Est**udio**s de Tatuajes*** y *"udio" → …(au**dio**)*. Es la misma lección del buscador de
   rubros (*Fuse.js: "sap" → Transporte*) y del `LIKE '%…%'` sin límite de palabra.
3. **Se ignoran mayúsculas y tildes** (`terminos_normalizar()`): "cancion" encuentra *Canción…*.
4. **Nunca se sugiere el término exacto que ya escribió** (no tiene sentido repetirlo).

**Cómo verificarlo (reproducible):**

```text
C:\xampp\php\php.exe __term_1_prueba.php     # 12 casos SIN BD ni navegador (incluye la trampa "udio")
python __subir_uno.py includes/terminos_sugerir.php   # desplegar = solo lo modificado
python __term_4_verificar.py                 # caché escrito, cache/ 403 y el JS servido
```

⚠️ El despliegue es **`php -l` → subir solo lo modificado → verificar por HTTP** (sin respaldo del vivo y
sin comparar local ↔ hosting: regla del 2026-09-12). **`__term_3_deploy.py`** (guardaba md5 y respaldo del
vivo) queda como **HISTÓRICO (no se usa)**.

Medido en vivo el 2026-09-10: `zapati` → 8 sugerencias · `torta` → *Tortas · Torta por porción · Torta
decorada · Torta personalizada…* · `cemento` → *Cemento (bolsa) · Cemento Portland tipo I…* · `udio` → **0**
· unidad `por t` → *por torneo · por torta · por taza…*.

**Trampas / decisiones que hay que respetar:**

- ⚠️ **`fuzzy_olvidar_cache()` borra TODOS los `.json` de `cache/`**, así que también refresca este
  diccionario cuando se guarda un negocio. Por eso `terminos_olvidar_cache()` solo hace falta donde **no**
  se llama al del buscador (hoy: `productos.php`).
- ⚠️ **El diccionario NO se descarga al celular** (292 KB hoy; mañana más): se consulta por el endpoint.
  Es la misma razón por la que los productos no se bajan al teléfono (9 400 = 1,5 MB).
- ⚠️ Al cambiar `assets/js/terminos_sugerir.js` hay que **subir el `?v=`** en `productos.php` y en
  `crear_negocio.php` (hoy `?v=1`), o el jefe verá la versión vieja hasta 7 días.
- **Un término que el sitio nunca vendió no aparece**: con datos reales, `canci` devuelve **1** sugerencia
  (*Canción dedicada a domicilio con violín y guitarra*). Eso es **esperado**: el diccionario refleja lo
  que existe. Para que salgan *"canciones criollas"* o *"canción con serenata"* hace falta la **Etapa 2**
  (semilla curada por rubro) — ver §12.1.

### 12.1 ETAPAS 2 Y 3 (pendientes, ya diseñadas)

| Etapa | Qué falta | Cómo se haría |
|---|---|---|
| **2** | **Semilla curada por rubro** (ferretería, bodega, botica, ropa, limpieza, eventos…): unos **300-600 términos genéricos por rubro grande** | Un migrador que inserta en la misma estructura del diccionario (o una tabla `directorio_terminos_producto`) con `origen='semilla'`. **Es data: no hace falta tocar el motor** |
| **3** | **Aprender de lo que escriben los usuarios** + informe en el Súper Admin | Tabla `directorio_terminos_producto (texto, veces, origen, aprobado)`: normalizar, **contador de usos**, mostrar solo lo usado **3 veces** (o lo aprobado por el jefe) y un informe de *"términos más escritos"* y *"términos escritos que NO existen en el catálogo"* (mina para saber qué falta en Chimbote) |
| **Bonus** | Que el término escrito **sugiera el rubro** ("serenata" → Música / Shows) | El mismo comparador, contra `directorio_categorias` |

## 13) PENDIENTES / IDEAS

- [ ] **Decidir si el RUC y el correo se muestran también en un bloque propio de la ficha** (hoy salen como
      dato en el bloque de contacto, que es donde el jefe los pidió: *«opcional pero mejora mucho las
      ventas»*). *Siguiente paso:* ver si al jefe le gusta ahí o los quiere más arriba, con los botones.
- [ ] **Medir si las tiendas que ponen RUC/correo reciben más consultas**: el sitio ya cuenta vistas y
      clics (`data-negocio` en los botones). *Siguiente paso:* comparar el informe del Súper Admin entre
      tiendas con RUC y sin RUC cuando haya unas cuantas cargadas.
- [ ] **Probar los modales nuevos en el celular del jefe** (los 3: números, horario y redes): en el
      navegador de escritorio ya se probaron (abren, cierran con ✕/fondo/Escape, agregan filas, arman el
      horario y actualizan los chips) y miden **0 px de desborde** a 360/390/414 px.
- [ ] **Probar la 📍 ubicación REAL en el celular del dueño** (Chrome Android, permiso de GPS y Nominatim
      respondiendo): la prueba automática comprueba que el mapa y los campos se guardan, pero
      **no** el diálogo de permiso ni la calle que devuelve el mapa en Chimbote. *Siguiente paso:* que el
      jefe abra su editor en el celular, toque **📍 Tu ubicación** y confirme que le llenó bien la calle.
- [ ] **Probarlo con la cámara y la voz REALES en el celular del dueño** (Chrome Android). Es la única
      prueba que falta: el banco y la sonda usan cámara y micrófono **simulados**. (Mismo pendiente que
      `GUIA_BUSCADOR_VOZ.md` §8.)
- [ ] **Vista previa en vivo dentro de la página** (`getUserMedia`, sin abrir la app de cámara): hoy la
      vista previa es la **miniatura instantánea** en la casilla, que es el camino probado de Caminante
      y el que funciona igual en iPhone y Android. Si se añade, hay que pedir permiso de cámara en la
      página y dejar el input nativo como respaldo.
- [ ] **Llevar la píldora 🎙️ al resto de formularios** (Caminante: nombre y productos; alta de negocio).
      Es solo añadir `data-dictado` al campo y cargar `dictado_voz.js`. 🗑️ El **tablón comunitario** ya no
      es un destino: se retiró del sitio el 2026-09-13 (orden del jefe).
- [ ] **Medir** con las estadísticas del sitio cuántos productos se publican usando cámara/dictado
      (el componente ya emite el evento `cz-captura-cambio` con el nº de fotos).
- [ ] **Aviso al jefe de "producto con foto"**: hoy `aviso('producto_nuevo', …)` ya cuenta las fotos;
      se podría resaltar cuando el producto viene de la captura rápida.
- [ ] **🔤 Etapas 2 y 3 de los títulos predictivos** (semilla por rubro + aprendizaje con aprobación y
      informe): diseñadas en **§12.1**. *Siguiente paso:* decidir si se siembra primero ferretería/bodega/
      botica (los rubros con más productos) o se espera a ver qué escriben los dueños.

---

> **Guías base:** `GUIA_MAESTRA_CHIMBOTE_XYZ.md` (índice y estado real) · `REGLAS_DE_ORO_PROYECTO.md`
> (reglas del jefe) · `GUIA_DESPLIEGUE_Y_ENTORNO.md` (editar, subir y verificar).

_Última revisión: 2026-09-10 (creación del módulo: rejilla de fotos con cámara y vista previa
instantánea + dictado por Web Speech API en el panel del dueño)._
_2026-09-16: **§4bis** (el panel: editar, eliminar con contraseña y el ancla) y **§4ter** (el editor del
dueño `/mi-tienda?n=<id>`, control total siempre con su contraseña)._
_**2026-09-16 (noche): §4ter.1 — segunda tanda del editor**: 🌎 «Todos los distritos» (con la cobertura
marcada `es_todos`), 📍 «Tu ubicación» (GPS + calle por OpenStreetMap, `lat`/`lng` → mapa de la ficha),
📞 **hasta 4 teléfonos** (principal en rojo + `directorio_negocio_telefonos`, sin perder el «Llamar» de
las 951 tiendas que tenían el mismo número para todo), 📸 **fotos en 3 × 2 con visor modal** (portada,
borrar, agregar), 📐 **a todo el ancho sin margen a la izquierda** (0 px de desborde de 320 a 1366 px) y
🔐 el recordatorio de que **la contraseña está en su WhatsApp y hay que memorizarla**. Prueba:
**70 ✅ · 0 ❌ · 0 restos**._
_**2026-09-16 (noche): §4ter.2 — tercera tanda del editor**: 📞 los otros números en un **modal con
select de uso** (8 usos: ventas, atención, mayoristas, pedidos…) y «➕ Agregar otro número» hasta 6 ·
🪪 **RUC y correo** (opcionales, con el consejo de los contratos grandes; un dato inválido no se guarda) ·
🕒 el **horario con 8 casillas** en un modal que arma el texto solo · 📘 **8 redes en un modal** con el
botón «Mostrar mis redes sociales» (y ahora **sí se ven en la ficha**, que antes se guardaban y nadie las
mostraba). Tres trampas nuevas documentadas ahí mismo (el ENUM que guarda vacío, **Cloudflare ofuscando
los correos** y el dominio duplicado en los enlaces de las redes). Prueba: **89 ✅ · 0 ❌ · 0 restos**._
_2026-09-10: añadido **§12 — títulos y unidades predictivos** (`includes/terminos_sugerir.php` +
`api/terminos.php` + `assets/js/terminos_sugerir.js`, atributo `data-terminos`), con las etapas 2 y 3
diseñadas en §12.1._
